<?php
/**
 * Plugin Name: SP Analyse
 * Description: Cookiefreie Besucherstatistik, Kauf-Trichter, Umsatz nach Herkunft, Tracking-Links (/l/...), Google/Bing-Bestaetigung und Health-Check fuer UptimeRobot (2026-10-09).
 *
 * Besucherzaehlung: ein kleines Skript meldet jeden Seitenaufruf per
 * sendBeacon an admin-ajax (sp_an_hit). Es wird KEIN Cookie gesetzt und keine
 * IP gespeichert: IP + Browser-Kennung + taeglich wechselnder Zufallswert
 * werden zu einem anonymen 16-Zeichen-Code gehasht (wie Plausible) - der Code
 * ist nur innerhalb eines Tages gleich ("Besucher" = eindeutig pro Tag).
 * Deshalb zaehlt die Statistik ALLE Besucher, nicht nur die ~80 %, die im
 * Cookie-Banner zustimmen (Clarity/InsightPress sehen nur diese).
 * Admins (manage_woocommerce) und Bots werden nicht gezaehlt.
 *
 * Herkunft: utm_source > ?ref= (Partner) > Referrer-Domain > In-App-Browser
 * (TikTok/Instagram/Facebook-App senden oft keinen Referrer, sind aber am
 * User-Agent erkennbar) > "direkt". Pro Besucher und Tag zaehlt die erste
 * echte Quelle (eine externe Quelle schlaegt "direkt").
 * Bestellungen bekommen die Quelle beim Absenden als Meta _sp_an_src /
 * _sp_an_campaign; aeltere Bestellungen fallen auf WooCommerces eigene
 * Order-Attribution (_wc_order_attribution_*) zurueck.
 *
 * Tracking-Links: peptrium.com/l/<slug> -> Zielseite mit utm-Parametern
 * (Option sp_an_links), verwaltet im Tab "Analyse" des Peptrium Dashboards.
 * Health-Check: peptrium.com/?sp_health=1 -> "PEPTRIUM-OK" oder HTTP 503.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_AN_DB_VERSION', '1');
define('SP_AN_RETENTION_DAYS', 425);
define('SP_AN_TOPUP_PRODUCT_ID', 729);

function sp_an_table() {
    global $wpdb;
    return $wpdb->prefix . 'sp_stats';
}

function sp_an_install() {
    if (get_option('sp_an_db_version') === SP_AN_DB_VERSION) {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = sp_an_table();
    $charset = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        ts DATETIME NOT NULL,
        day DATE NOT NULL,
        vh CHAR(16) NOT NULL,
        type VARCHAR(10) NOT NULL,
        ptype VARCHAR(10) NOT NULL DEFAULT '',
        path VARCHAR(191) NOT NULL DEFAULT '',
        product_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        src VARCHAR(60) NOT NULL DEFAULT '',
        campaign VARCHAR(80) NOT NULL DEFAULT '',
        device VARCHAR(8) NOT NULL DEFAULT '',
        country CHAR(2) NOT NULL DEFAULT '',
        order_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        KEY day_type (day,type),
        KEY vh_day (vh,day)
    ) {$charset};");
    update_option('sp_an_db_version', SP_AN_DB_VERSION);
    if (!get_option('sp_an_since')) {
        update_option('sp_an_since', current_time('Y-m-d'));
    }
    if (get_option('sp_an_links') === false) {
        update_option('sp_an_links', array(
            sp_an_random_slug(array()) => array('label' => 'TikTok Bio-Link', 'channel' => 'tiktok', 'target' => '/'),
            sp_an_random_slug(array()) . 'x' => array('label' => 'Instagram Bio-Link', 'channel' => 'instagram', 'target' => '/'),
        ), false);
    }
}
add_action('admin_init', 'sp_an_install');

/* -----------------------------------------------------------------------
 * Besucher-Erkennung (anonym, ohne Cookie)
 * ---------------------------------------------------------------------*/

function sp_an_is_bot($ua) {
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|headless|lighthouse|pagespeed|preview|monitor|uptime|curl|wget|python|scrapy|facebookexternalhit|embedly|whatsapp\/|telegrambot/i', $ua);
}

function sp_an_visitor_hash() {
    $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP']) ? $_SERVER['HTTP_CF_CONNECTING_IP'] : (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $today = current_time('Y-m-d');
    $salt = get_option('sp_an_salt');
    if (!is_array($salt) || ($salt['d'] ?? '') !== $today) {
        // Neuer Tag = neuer Zufallswert; der alte wird verworfen, damit sich
        // Codes weder tagesuebergreifend verknuepfen noch zurueckrechnen lassen.
        $salt = array('d' => $today, 's' => wp_generate_password(32, false));
        update_option('sp_an_salt', $salt, true);
    }
    return substr(hash('sha256', $salt['s'] . '|' . $ip . '|' . $ua), 0, 16);
}

function sp_an_device($ua) {
    if (preg_match('/iPad|Tablet/i', $ua)) {
        return 'tablet';
    }
    return preg_match('/Mobi|Android|iPhone/i', $ua) ? 'mobil' : 'desktop';
}

function sp_an_country() {
    $c = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? strtoupper(substr(sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_IPCOUNTRY'])), 0, 2)) : '';
    return preg_match('/^[A-Z]{2}$/', $c) && $c !== 'XX' && $c !== 'T1' ? $c : '';
}

/** Ordnet utm_source-Werte und Referrer-Domains einer Quelle zu. '' = interne Navigation. */
function sp_an_classify_name($value) {
    $v = strtolower(trim($value));
    $v = preg_replace('/^www\./', '', $v);
    if ($v === '') {
        return '';
    }
    $map = array(
        'email'     => '/(^|\.)mail\.|newsletter|^e-?mail|warenkorb|freescout|outlook\.|gmx\.|web\.de/',
        'tiktok'    => '/tiktok|musical|^tt$/',
        'instagram' => '/instagram|^ig$|^insta/',
        'facebook'  => '/facebook|^fb$|fb\.com|fb\.me/',
        'youtube'   => '/youtube|youtu\.be|^yt$/',
        'telegram'  => '/telegram|^t\.me$|^tg$/',
        'whatsapp'  => '/whatsapp|^wa$|wa\.me/',
        'google'    => '/(^|\.)google\.|^google$/',
        'bing'      => '/(^|\.)bing\.|^bing$/',
        'ki'        => '/chatgpt|openai|perplexity|copilot|gemini\.google|claude\.ai/',
        'reddit'    => '/reddit/',
    );
    // KI vor Google pruefen (gemini.google.com)
    if (preg_match($map['ki'], $v)) {
        return 'ki';
    }
    foreach ($map as $key => $re) {
        if (preg_match($re, $v)) {
            return $key;
        }
    }
    return 'web:' . substr(preg_replace('/[^a-z0-9.\-_]/', '', $v), 0, 55);
}

/** Plattform aus Referrer bzw. In-App-Browser. '' = interne Navigation / Rueckkehr vom Zahlungsdienst. */
function sp_an_platform($referrer, $ua) {
    $host = $referrer !== '' ? (string) wp_parse_url($referrer, PHP_URL_HOST) : '';
    $own = (string) wp_parse_url(home_url(), PHP_URL_HOST);
    if ($host !== '') {
        $h = strtolower(preg_replace('/^www\./', '', $host));
        if ($h === strtolower(preg_replace('/^www\./', '', $own))) {
            return '';
        }
        if (preg_match('/nowpayments|paypal|stripe|klarna|sofort|giropay/', $h)) {
            return '';
        }
        return sp_an_classify_name($h);
    }
    // Kein Referrer: In-App-Browser erkennen.
    if (preg_match('/BytedanceWebview|musical_ly|TikTok|trill_/i', $ua)) {
        return 'tiktok';
    }
    if (preg_match('/Instagram/i', $ua)) {
        return 'instagram';
    }
    if (preg_match('/FBAN|FBAV|FB_IAB/i', $ua)) {
        return 'facebook';
    }
    return 'direkt';
}

/**
 * Quelle eines Seitenaufrufs. Reihenfolge:
 * 1. eigener Tracking-Link (/l/<slug>)       -> "eigen-<kanal>", Kampagne = slug
 * 2. Partner-Link (?ref=ID)                    -> "partner", Kampagne "aff-ID/<plattform>"
 * 3. andere utm-Links
 * 4. Rueckkehrer mit SliceWP-Partner-Cookie    -> "partner" (Kunde dieses Partners)
 * 5. Referrer / In-App-Browser / direkt
 */
function sp_an_source_from_request($query, $referrer, $ua) {
    parse_str(ltrim($query, '?'), $q);
    $campaign = '';
    if (!empty($q['utm_campaign'])) {
        $campaign = substr(sanitize_title((string) $q['utm_campaign']), 0, 50);
        if (!empty($q['utm_content'])) {
            $campaign .= '/' . substr(sanitize_title((string) $q['utm_content']), 0, 28);
        }
    }
    $platform = sp_an_platform($referrer, $ua);
    $links = sp_an_links();
    $slug = !empty($q['utm_campaign']) ? sanitize_title((string) $q['utm_campaign']) : '';
    if ($slug !== '' && isset($links[$slug])) {
        return array('eigen-' . $links[$slug]['channel'], $slug);
    }
    $aff = !empty($q['ref']) ? absint($q['ref']) : 0;
    if ($aff) {
        return array('partner', 'aff-' . $aff . ($platform !== '' && $platform !== 'direkt' ? '/' . $platform : ''));
    }
    if (!empty($q['utm_source'])) {
        return array(sp_an_classify_name((string) $q['utm_source']) ?: 'direkt', $campaign);
    }
    if ($platform === '') {
        return array('', '');
    }
    $cookie_aff = !empty($_COOKIE['slicewp_aff']) ? absint($_COOKIE['slicewp_aff']) : 0;
    if ($cookie_aff) {
        return array('partner', 'aff-' . $cookie_aff . ($platform !== 'direkt' ? '/' . $platform : ''));
    }
    return array($platform, $campaign);
}

function sp_an_should_track() {
    if (is_user_logged_in() && current_user_can('manage_woocommerce')) {
        return false;
    }
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    return !sp_an_is_bot($ua);
}

function sp_an_insert($row) {
    global $wpdb;
    if (get_option('sp_an_db_version') !== SP_AN_DB_VERSION) {
        sp_an_install();
    }
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $row = array_merge(array(
        'ts' => current_time('mysql'),
        'day' => current_time('Y-m-d'),
        'vh' => sp_an_visitor_hash(),
        'device' => sp_an_device($ua),
        'country' => sp_an_country(),
    ), $row);
    $wpdb->insert(sp_an_table(), $row);
    if (mt_rand(1, 300) === 1) {
        $wpdb->query($wpdb->prepare('DELETE FROM ' . sp_an_table() . ' WHERE day < %s', date('Y-m-d', strtotime('-' . SP_AN_RETENTION_DAYS . ' days', current_time('timestamp')))));
    }
}

/* Seitenaufruf-Beacon */
add_action('wp_footer', function () {
    if (is_admin() || !sp_an_should_track()) {
        return;
    }
    $ptype = 'other';
    $pid = 0;
    if (is_front_page()) {
        $ptype = 'home';
    } elseif (function_exists('is_product') && is_product()) {
        $ptype = 'product';
        $pid = (int) get_queried_object_id();
    } elseif (function_exists('is_order_received_page') && is_order_received_page()) {
        $ptype = 'thanks';
    } elseif (function_exists('is_checkout') && is_checkout()) {
        $ptype = 'checkout';
    } elseif (function_exists('is_cart') && is_cart()) {
        $ptype = 'cart';
    } elseif (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $ptype = 'shop';
    } elseif (function_exists('is_account_page') && is_account_page()) {
        $ptype = 'account';
    } elseif (is_page()) {
        $ptype = 'page';
    }
    ?>
    <script>
    (function(){try{
      var u=<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,d=new FormData();
      d.append('action','sp_an_hit');d.append('t',<?php echo wp_json_encode($ptype); ?>);d.append('pid','<?php echo (int) $pid; ?>');
      d.append('p',location.pathname.slice(0,190));d.append('q',location.search.slice(0,400));d.append('r',(document.referrer||'').slice(0,400));
      if(!(navigator.sendBeacon&&navigator.sendBeacon(u,d))){fetch(u,{method:'POST',body:d,keepalive:true,credentials:'same-origin'});}
    }catch(e){}})();
    </script>
    <?php
}, 99);

function sp_an_ajax_hit() {
    if (!sp_an_should_track()) {
        wp_die('', '', array('response' => 204));
    }
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $ptype = isset($_POST['t']) ? sanitize_key(wp_unslash($_POST['t'])) : 'other';
    if (!in_array($ptype, array('home', 'product', 'thanks', 'checkout', 'cart', 'shop', 'account', 'page', 'other'), true)) {
        $ptype = 'other';
    }
    $query = isset($_POST['q']) ? (string) wp_unslash($_POST['q']) : '';
    $ref = isset($_POST['r']) ? esc_url_raw(wp_unslash($_POST['r'])) : '';
    list($src, $campaign) = sp_an_source_from_request($query, $ref, $ua);
    sp_an_insert(array(
        'type' => 'pv',
        'ptype' => $ptype,
        'path' => substr(sanitize_text_field(isset($_POST['p']) ? wp_unslash($_POST['p']) : ''), 0, 190),
        'product_id' => isset($_POST['pid']) ? absint($_POST['pid']) : 0,
        'src' => $src,
        'campaign' => $campaign,
    ));
    wp_die('', '', array('response' => 204));
}
add_action('wp_ajax_sp_an_hit', 'sp_an_ajax_hit');
add_action('wp_ajax_nopriv_sp_an_hit', 'sp_an_ajax_hit');

/* In den Warenkorb gelegt (Geschenke, Aufladungen und Warenkorb-Wiederherstellung zaehlen nicht) */
add_action('woocommerce_add_to_cart', function ($key, $product_id, $qty, $variation_id, $variation, $data) {
    if (!sp_an_should_track() || isset($_GET['sp_warenkorb']) || (defined('DOING_CRON') && DOING_CRON)) {
        return;
    }
    if ((defined('SP_GIFT_ITEM_META') && !empty($data[SP_GIFT_ITEM_META])) || (int) $product_id === SP_AN_TOPUP_PRODUCT_ID) {
        return;
    }
    sp_an_insert(array('type' => 'cart', 'product_id' => (int) $product_id));
}, 50, 6);

/* Bestellung abgeschickt: Herkunft des Besuchers an der Bestellung merken */
function sp_an_on_order($order) {
    if (!$order instanceof WC_Order || $order->get_meta('_sp_an_src') !== '') {
        return;
    }
    global $wpdb;
    $vh = sp_an_visitor_hash();
    $rows = $wpdb->get_results($wpdb->prepare('SELECT src, campaign FROM ' . sp_an_table() . " WHERE vh = %s AND day = %s AND type IN ('pv','link') AND src <> '' ORDER BY ts ASC", $vh, current_time('Y-m-d')));
    $src = '';
    $campaign = '';
    foreach ($rows as $r) {
        if ($src === '' || ($src === 'direkt' && $r->src !== 'direkt')) {
            $src = $r->src;
            $campaign = $r->campaign;
        }
    }
    $order->update_meta_data('_sp_an_src', $src !== '' ? $src : 'unbekannt');
    if ($campaign !== '') {
        $order->update_meta_data('_sp_an_campaign', $campaign);
    }
    $order->save_meta_data();
    if (sp_an_should_track()) {
        sp_an_insert(array('type' => 'order', 'order_id' => $order->get_id(), 'src' => $src, 'campaign' => $campaign));
    }
}
add_action('woocommerce_store_api_checkout_order_processed', 'sp_an_on_order', 50);
add_action('woocommerce_checkout_order_processed', function ($order_id, $posted = null, $order = null) {
    sp_an_on_order($order instanceof WC_Order ? $order : wc_get_order($order_id));
}, 50, 3);

/* -----------------------------------------------------------------------
 * Tracking-Links /l/<slug>
 * ---------------------------------------------------------------------*/

function sp_an_channels() {
    return array(
        'tiktok' => 'TikTok', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp', 'facebook' => 'Facebook', 'email' => 'E-Mail', 'sonstiges' => 'Sonstiges',
    );
}

function sp_an_links() {
    $links = get_option('sp_an_links');
    return is_array($links) ? $links : array();
}

function sp_an_random_slug($links) {
    do {
        $slug = strtolower(wp_generate_password(4, false, false));
    } while (isset($links[$slug]) || !preg_match('/^[a-z0-9]{4}$/', $slug));
    return $slug;
}

function sp_an_link_url($slug) {
    return home_url('/l/' . $slug);
}

add_action('init', function () {
    $path = isset($_SERVER['REQUEST_URI']) ? (string) wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
    if (!preg_match('#^/l/([a-z0-9\-]+)/?$#', $path, $m)) {
        return;
    }
    $links = sp_an_links();
    $link = isset($links[$m[1]]) ? $links[$m[1]] : null;
    nocache_headers();
    if (!$link) {
        wp_safe_redirect(home_url('/'), 302);
        exit;
    }
    // Klick serverseitig merken (gleicher anonymer Tagescode wie der folgende
    // Seitenaufruf) und auf die SAUBERE Zieladresse weiterleiten - ohne
    // sichtbare utm-Parameter. Link-Vorschau-Bots werden nicht gezaehlt.
    if (sp_an_should_track()) {
        sp_an_insert(array('type' => 'link', 'src' => 'eigen-' . $link['channel'], 'campaign' => $m[1]));
    }
    wp_safe_redirect(home_url('/' . ltrim($link['target'], '/')), 302);
    exit;
}, 1);

/* -----------------------------------------------------------------------
 * Google / Bing Bestaetigung, Sitemap, Health-Check
 * ---------------------------------------------------------------------*/

add_action('wp_head', function () {
    $g = (string) get_option('sp_an_google_verify', '');
    $b = (string) get_option('sp_an_bing_verify', '');
    if ($g !== '') {
        echo '<meta name="google-site-verification" content="' . esc_attr($g) . '" />' . "\n";
    }
    if ($b !== '') {
        echo '<meta name="msvalidate.01" content="' . esc_attr($b) . '" />' . "\n";
    }
}, 1);

/* Die Benutzer-Sitemap verraet Login-Namen (inkl. Admin) - fuer Google wertlos. */
add_filter('wp_sitemaps_add_provider', function ($provider, $name) {
    return $name === 'users' ? false : $provider;
}, 10, 2);

add_action('wp_loaded', function () {
    if (!isset($_GET['sp_health'])) {
        return;
    }
    global $wpdb;
    nocache_headers();
    header('Content-Type: text/plain; charset=utf-8');
    $errors = array();
    if ((int) $wpdb->get_var('SELECT 1') !== 1) {
        $errors[] = 'Datenbank';
    }
    if (!function_exists('WC')) {
        $errors[] = 'WooCommerce';
    } else {
        $checkout = wc_get_page_id('checkout');
        if ($checkout <= 0 || get_post_status($checkout) !== 'publish') {
            $errors[] = 'Kasse';
        }
        $enabled = 0;
        foreach (WC()->payment_gateways()->payment_gateways() as $gw) {
            if ($gw->enabled === 'yes') {
                $enabled++;
            }
        }
        if ($enabled === 0) {
            $errors[] = 'Zahlungsarten';
        }
        $counts = wp_count_posts('product');
        if (empty($counts->publish)) {
            $errors[] = 'Produkte';
        }
    }
    $probe = (string) mt_rand();
    wp_cache_set('sp_health_probe', $probe, 'sp', 30);
    if (wp_cache_get('sp_health_probe', 'sp') !== $probe) {
        $errors[] = 'Cache';
    }
    if ($errors) {
        status_header(503);
        echo 'PEPTRIUM-FEHLER: ' . implode(', ', $errors);
    } else {
        status_header(200);
        echo 'PEPTRIUM-OK';
    }
    exit;
});

/* Datenschutzerklaerung (Seite 410) um die Besucherstatistik ergaenzen */
function sp_an_privacy_filter($html) {
    if (!is_page(410) || strpos($html, '<h2>Daten von Kindern</h2>') === false || strpos($html, 'sp-an-privacy') !== false) {
        return $html;
    }
    $section = '<h2 class="sp-an-privacy">Besucherstatistik ohne Cookies</h2>'
        . '<p>Um unseren Shop zu verbessern, zählen wir Seitenaufrufe mit einer eigenen, auf unserem Server laufenden Statistik. Dabei werden <strong>keine Cookies</strong> gesetzt und keine Informationen auf deinem Gerät gespeichert oder ausgelesen. Aus deiner IP-Adresse, der Kennung deines Browsers und einem täglich neu erzeugten Zufallswert wird ein anonymer Code gebildet; die IP-Adresse selbst wird nicht gespeichert, und der Code lässt sich weder auf dich zurückführen noch tagesübergreifend verknüpfen.</p>'
        . '<p>Gespeichert werden: aufgerufene Seite bzw. angesehenes Produkt, Zeitpunkt, die Website oder Kampagne, über die du zu uns gekommen bist (z. B. TikTok, Instagram, Suchmaschine), Gerätetyp (Handy/Computer), Land sowie ob ein Artikel in den Warenkorb gelegt oder eine Bestellung abgeschickt wurde. Bei einer Bestellung speichern wir zusätzlich die Herkunftsquelle an der Bestellung.</p>'
        . '<p>Rechtsgrundlage ist unser berechtigtes Interesse an der Analyse und Verbesserung unseres Angebots (Art. 6 Abs. 1 lit. f DSGVO). Die Daten werden nicht an Dritte weitergegeben und nach spätestens 14 Monaten gelöscht.</p>';
    return str_replace('<h2>Daten von Kindern</h2>', $section . '<h2>Daten von Kindern</h2>', $html);
}
add_filter('elementor/frontend/the_content', 'sp_an_privacy_filter', 98);
add_filter('the_content', 'sp_an_privacy_filter', 98);

/* -----------------------------------------------------------------------
 * Auswertung
 * ---------------------------------------------------------------------*/

function sp_an_source_label($src) {
    $labels = array(
        'tiktok' => 'TikTok (ohne Link)', 'instagram' => 'Instagram (ohne Link)', 'facebook' => 'Facebook', 'youtube' => 'YouTube',
        'telegram' => 'Telegram', 'whatsapp' => 'WhatsApp', 'google' => 'Google', 'bing' => 'Bing',
        'ki' => 'KI-Suche (ChatGPT & Co.)', 'reddit' => 'Reddit', 'email' => 'E-Mail', 'partner' => 'Partner (Affiliates)',
        'link' => 'Tracking-Link', 'direkt' => 'Direkt / unbekannt', 'unbekannt' => 'Direkt / unbekannt',
    );
    if (isset($labels[$src])) {
        return $labels[$src];
    }
    if (strpos($src, 'eigen-') === 0) {
        $ch = substr($src, 6);
        return 'Eigener ' . (sp_an_channels()[$ch] ?? $ch) . '-Account (Link)';
    }
    if (strpos($src, 'web:') === 0) {
        return substr($src, 4);
    }
    return $src;
}

/** SliceWP-Provisionen je Bestellung (nicht abgelehnt): order_id => [affiliate_id, Provision]. */
function sp_an_commission_map() {
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    global $wpdb;
    $map = array();
    $table = $wpdb->prefix . 'slicewp_commissions';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        return $map;
    }
    $rows = $wpdb->get_results("SELECT reference, affiliate_id, CAST(amount AS DECIMAL(15,2)) AS amount FROM {$table} WHERE origin = 'woo' AND status <> 'rejected'");
    foreach ($rows as $r) {
        $map[(int) $r->reference] = array((int) $r->affiliate_id, (float) $r->amount);
    }
    return $map;
}

function sp_an_affiliate_name($affiliate_id) {
    return function_exists('sp_aff_display_name') ? sp_aff_display_name($affiliate_id) : 'Partner #' . $affiliate_id;
}

/**
 * Herkunft einer Bestellung. Hat SliceWP eine Provision gebucht, ist es
 * IMMER eine Partner-Bestellung (Kampagne "aff-ID/<plattform>") - auch wenn
 * der Kunde ueber TikTok/Instagram kam, denn das war dann das Video des
 * Partners, nicht dein eigener Account.
 */
function sp_an_order_source($order) {
    list($src, $campaign) = sp_an_order_source_raw($order);
    $map = sp_an_commission_map();
    if (isset($map[$order->get_id()])) {
        $aff = $map[$order->get_id()][0];
        if ($src === 'partner') {
            $platform = strpos($campaign, '/') !== false ? substr($campaign, strpos($campaign, '/') + 1) : '';
        } else {
            $platform = ($src !== 'direkt' && strpos($src, 'eigen-') !== 0) ? $src : '';
        }
        return array('partner', 'aff-' . $aff . ($platform !== '' ? '/' . $platform : ''));
    }
    return array($src, $campaign);
}

/** Herkunft ohne Partner-Abgleich: eigene Erfassung, sonst WooCommerce-Order-Attribution. */
function sp_an_order_source_raw($order) {
    $src = (string) $order->get_meta('_sp_an_src');
    $campaign = (string) $order->get_meta('_sp_an_campaign');
    if ($src === '' || $src === 'direkt' || $src === 'unbekannt') {
        $type = (string) $order->get_meta('_wc_order_attribution_source_type');
        $utm = (string) $order->get_meta('_wc_order_attribution_utm_source');
        if ($type !== '' && $type !== 'typein' && $type !== 'admin' && $utm !== '' && $utm !== '(direct)') {
            $src = sp_an_classify_name($utm) ?: 'direkt';
            if ($campaign === '') {
                $c = (string) $order->get_meta('_wc_order_attribution_utm_campaign');
                $campaign = ($c !== '' && $c !== '(none)') ? sanitize_title($c) : '';
            }
        }
    }
    if ($src === '' || $src === 'unbekannt') {
        $src = 'direkt';
    }
    return array($src, $campaign);
}

/** Bestellungen (ohne Tests, Abo-Abbuchungen aus Guthaben und reine Aufladungen) mit Warenwert. */
function sp_an_get_orders($start, $end) {
    $orders = wc_get_orders(array(
        'limit' => -1,
        'status' => array('on-hold', 'processing', 'completed'),
        'date_created' => $start . '...' . $end . ' 23:59:59',
        'return' => 'objects',
    ));
    $out = array();
    foreach ($orders as $order) {
        if ($order->get_meta('_sp_is_test') || $order->get_payment_method() === 'sp_wallet') {
            continue;
        }
        $topup = function_exists('sp_dashboard_order_topup_total') ? sp_dashboard_order_topup_total($order) : 0;
        $net = function_exists('sp_dashboard_order_net_total') ? sp_dashboard_order_net_total($order) : (float) $order->get_total();
        $goods = max(0.0, $net - $topup);
        if ($goods <= 0) {
            continue;
        }
        list($src, $campaign) = sp_an_order_source($order);
        $products = array();
        foreach ($order->get_items() as $item) {
            if ((float) $item->get_total() <= 0 || $item->get_meta('_sp_wallet_amount')) {
                continue;
            }
            $products[(int) $item->get_product_id()] = true;
        }
        $out[] = array(
            'paid' => $order->is_paid(),
            'goods' => $goods,
            'src' => $src,
            'campaign' => $campaign,
            'commission' => isset(sp_an_commission_map()[$order->get_id()]) ? sp_an_commission_map()[$order->get_id()][1] : 0.0,
            'day' => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : $start,
            'products' => array_keys($products),
        );
    }
    return $out;
}

/** Besucher-Tage mit Quelle und Trichter-Stufen. */
function sp_an_get_visits($start, $end) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        'SELECT vh, day, type, ptype, product_id, src, campaign, device, country FROM ' . sp_an_table() . ' WHERE day BETWEEN %s AND %s ORDER BY ts ASC',
        $start, $end
    ));
    $visits = array();
    foreach ($rows as $r) {
        $k = $r->vh . '|' . $r->day;
        if (!isset($visits[$k])) {
            $visits[$k] = array('day' => $r->day, 'src' => '', 'campaign' => '', 'device' => $r->device, 'country' => $r->country,
                'pv' => false, 'product' => false, 'cart' => false, 'checkout' => false, 'order' => false,
                'viewed' => array(), 'carted' => array());
        }
        $v =& $visits[$k];
        if ($r->type === 'link') {
            // Klick auf einen eigenen Tracking-Link schlaegt die (danach erkannte) App-Herkunft.
            if ($v['src'] !== 'partner') {
                $v['src'] = $r->src;
                $v['campaign'] = $r->campaign;
            }
        } elseif ($r->type === 'pv') {
            $v['pv'] = true;
            if ($r->src !== '' && ($v['src'] === '' || ($v['src'] === 'direkt' && $r->src !== 'direkt') || ($r->src === 'partner' && $v['src'] !== 'partner'))) {
                $v['src'] = $r->src;
                $v['campaign'] = $r->campaign;
            }
            if ($r->ptype === 'product') {
                $v['product'] = true;
                if ($r->product_id) {
                    $v['viewed'][(int) $r->product_id] = true;
                }
            } elseif ($r->ptype === 'checkout') {
                $v['checkout'] = true;
            }
        } elseif ($r->type === 'cart') {
            $v['cart'] = true;
            $v['carted'][(int) $r->product_id] = true;
        } elseif ($r->type === 'order') {
            $v['order'] = true;
        }
        unset($v);
    }
    // Nur echte Besuche (mit Seitenaufruf); Besucher ohne erkannte Quelle = direkt.
    $visits = array_filter($visits, function ($v) {
        return $v['pv'];
    });
    foreach ($visits as &$v) {
        if ($v['src'] === '') {
            $v['src'] = 'direkt';
        }
    }
    unset($v);
    return $visits;
}

function sp_an_kpis($start, $end, $since) {
    $orders = sp_an_get_orders($start, $end);
    $tracked = $end >= $since;
    $visitors = $tracked ? count(sp_an_get_visits(max($start, $since), $end)) : null;
    $revenue = 0.0;
    $paid = 0;
    foreach ($orders as $o) {
        if ($o['paid']) {
            $revenue += $o['goods'];
            $paid++;
        }
    }
    return array(
        'visitors' => $visitors,
        'orders' => count($orders),
        'revenue' => $revenue,
        'aov' => $paid ? $revenue / $paid : 0,
        'cr' => ($visitors && $start >= $since) ? count($orders) / $visitors * 100 : null,
    );
}

function sp_an_pct($a, $b) {
    return $b > 0 ? $a / $b * 100 : 0;
}

function sp_an_fmt_pct($v) {
    return number_format_i18n($v, 1) . ' %';
}

function sp_an_delta($cur, $prev) {
    if ($cur === null || $prev === null || $prev == 0) {
        return null;
    }
    $d = ($cur - $prev) / $prev * 100;
    return ($d >= 0 ? '+' : '−') . number_format_i18n(abs($d), 0) . ' % ggü. Vorzeitraum';
}

/* -----------------------------------------------------------------------
 * Admin-Aktionen (Tracking-Links, Bestaetigungscodes)
 * ---------------------------------------------------------------------*/

function sp_an_handle_post() {
    if (empty($_POST['sp_an_action']) || !current_user_can('manage_woocommerce') || !check_admin_referer('sp_an_admin')) {
        return '';
    }
    $action = sanitize_key($_POST['sp_an_action']);
    $links = sp_an_links();
    if ($action === 'add_link') {
        $label = sanitize_text_field(wp_unslash($_POST['label'] ?? ''));
        $channel = sanitize_key($_POST['channel'] ?? '');
        $target = (string) wp_unslash($_POST['target'] ?? '/');
        if ($target === 'custom') {
            $target = (string) wp_unslash($_POST['target_custom'] ?? '/');
        }
        $target = '/' . ltrim((string) wp_make_link_relative(esc_url_raw($target)), '/');
        if ($label === '' || !isset(sp_an_channels()[$channel])) {
            return 'Bitte Name und Kanal angeben.';
        }
        // Ohne eigene Kennung: neutraler Zufallscode, damit im Link weder Kanal noch Inhalt steht.
        $slug = substr(preg_replace('/[^a-z0-9\-]/', '', sanitize_title(wp_unslash($_POST['slug'] ?? ''))), 0, 40);
        if ($slug === '') {
            $slug = sp_an_random_slug($links);
        }
        if ($slug === '' || isset($links[$slug])) {
            return 'Diese Link-Kennung gibt es schon – bitte einen anderen Namen wählen.';
        }
        $links[$slug] = array('label' => $label, 'channel' => $channel, 'target' => $target);
        update_option('sp_an_links', $links, false);
        return 'Link angelegt: ' . sp_an_link_url($slug);
    }
    if ($action === 'delete_link') {
        $slug = sanitize_title(wp_unslash($_POST['slug'] ?? ''));
        unset($links[$slug]);
        update_option('sp_an_links', $links, false);
        return 'Link gelöscht.';
    }
    if ($action === 'verify') {
        foreach (array('google' => 'sp_an_google_verify', 'bing' => 'sp_an_bing_verify') as $field => $opt) {
            $raw = trim((string) wp_unslash($_POST[$field] ?? ''));
            // Ganzes <meta>-Tag oder nur den Code akzeptieren.
            if (preg_match('/content=["\']([^"\']+)["\']/', $raw, $m)) {
                $raw = $m[1];
            }
            update_option($opt, preg_replace('/[^A-Za-z0-9_\-]/', '', $raw), true);
        }
        return 'Bestätigungscodes gespeichert.';
    }
    return '';
}

function sp_an_target_options() {
    $opts = array('/' => 'Startseite');
    foreach (array('shop' => 'Shop') as $page => $label) {
        $id = wc_get_page_id($page);
        if ($id > 0) {
            $opts[wp_make_link_relative(get_permalink($id))] = $label;
        }
    }
    foreach (array('abo-modell' => 'Abo-Modell', 'abo-stack' => 'Abo-Stack', 'zubehoer' => 'Zubehör') as $slug => $label) {
        $p = get_page_by_path($slug);
        if ($p) {
            $opts[wp_make_link_relative(get_permalink($p))] = $label;
        }
    }
    $products = wc_get_products(array('status' => 'publish', 'limit' => -1, 'orderby' => 'title', 'order' => 'ASC'));
    foreach ($products as $p) {
        $opts[wp_make_link_relative($p->get_permalink())] = 'Produkt: ' . $p->get_name();
    }
    return $opts;
}

/* -----------------------------------------------------------------------
 * Tab "Analyse" im Peptrium Dashboard
 * ---------------------------------------------------------------------*/

function sp_an_render_tab($range) {
    sp_an_install();
    $notice = sp_an_handle_post();
    $since = (string) get_option('sp_an_since', current_time('Y-m-d'));
    $start = $range['start'];
    $end = $range['end'];
    $days = (int) round((strtotime($end) - strtotime($start)) / DAY_IN_SECONDS) + 1;
    $prev_end = date('Y-m-d', strtotime($start . ' -1 day'));
    $prev_start = date('Y-m-d', strtotime($prev_end . ' -' . ($days - 1) . ' days'));

    $visits = sp_an_get_visits($start, $end);
    $orders = sp_an_get_orders($start, $end);
    $cur = sp_an_kpis($start, $end, $since);
    $prev = sp_an_kpis($prev_start, $prev_end, $since);

    /* Trichter */
    $f = array('pv' => 0, 'product' => 0, 'cart' => 0, 'checkout' => 0, 'order' => 0);
    foreach ($visits as $v) {
        foreach ($f as $k => $n) {
            if ($v[$k]) {
                $f[$k]++;
            }
        }
    }

    /* Herkunft */
    $src = array();
    $blank = array('visitors' => 0, 'cart' => 0, 'orders' => 0, 'revenue' => 0.0);
    foreach ($visits as $v) {
        $s = $v['src'];
        $src[$s] = isset($src[$s]) ? $src[$s] : $blank;
        $src[$s]['visitors']++;
        if ($v['cart']) {
            $src[$s]['cart']++;
        }
    }
    foreach ($orders as $o) {
        $s = $o['src'];
        $src[$s] = isset($src[$s]) ? $src[$s] : $blank;
        $src[$s]['orders']++;
        if ($o['paid']) {
            $src[$s]['revenue'] += $o['goods'];
        }
    }
    uasort($src, function ($a, $b) {
        return array($b['revenue'], $b['orders'], $b['visitors']) <=> array($a['revenue'], $a['orders'], $a['visitors']);
    });

    /* Kampagnen / Tracking-Links */
    $camp = array();
    foreach ($visits as $v) {
        if ($v['campaign'] !== '') {
            $c = strtok($v['campaign'], '/');
            $camp[$c] = isset($camp[$c]) ? $camp[$c] : $blank;
            $camp[$c]['visitors']++;
        }
    }
    foreach ($orders as $o) {
        if ($o['campaign'] !== '') {
            $c = strtok($o['campaign'], '/');
            $camp[$c] = isset($camp[$c]) ? $camp[$c] : $blank;
            $camp[$c]['orders']++;
            if ($o['paid']) {
                $camp[$c]['revenue'] += $o['goods'];
            }
        }
    }

    /* Partner (Affiliates) */
    $partners = array();
    $pb = array('visitors' => 0, 'orders' => 0, 'revenue' => 0.0, 'commission' => 0.0, 'via' => array());
    $add_partner = function ($campaign) use (&$partners, $pb) {
        if (strpos($campaign, 'aff-') !== 0) {
            return null;
        }
        $parts = explode('/', substr($campaign, 4), 2);
        $id = (int) $parts[0];
        if (!isset($partners[$id])) {
            $partners[$id] = $pb;
        }
        return array($id, isset($parts[1]) ? $parts[1] : '');
    };
    foreach ($visits as $v) {
        if ($v['src'] === 'partner' && ($r = $add_partner($v['campaign']))) {
            $partners[$r[0]]['visitors']++;
        }
    }
    foreach ($orders as $o) {
        if ($o['src'] === 'partner' && ($r = $add_partner($o['campaign']))) {
            $partners[$r[0]]['orders']++;
            if ($o['paid']) {
                $partners[$r[0]]['revenue'] += $o['goods'];
            }
            $partners[$r[0]]['commission'] += $o['commission'];
            $via = $r[1] !== '' ? $r[1] : 'unbekannt';
            $partners[$r[0]]['via'][$via] = ($partners[$r[0]]['via'][$via] ?? 0) + 1;
        }
    }
    uasort($partners, function ($a, $b) {
        return array($b['revenue'], $b['orders'], $b['visitors']) <=> array($a['revenue'], $a['orders'], $a['visitors']);
    });

    /* Produkte */
    $prod = array();
    $pblank = array('views' => 0, 'cart' => 0, 'buys' => 0);
    foreach ($visits as $v) {
        foreach (array_keys($v['viewed']) as $pid) {
            $prod[$pid] = isset($prod[$pid]) ? $prod[$pid] : $pblank;
            $prod[$pid]['views']++;
        }
        foreach (array_keys($v['carted']) as $pid) {
            $prod[$pid] = isset($prod[$pid]) ? $prod[$pid] : $pblank;
            $prod[$pid]['cart']++;
        }
    }
    foreach ($orders as $o) {
        foreach ($o['products'] as $pid) {
            $prod[$pid] = isset($prod[$pid]) ? $prod[$pid] : $pblank;
            $prod[$pid]['buys']++;
        }
    }
    // Vor dem Zaehlstart gibt es keine Aufrufe - dann nach Kaeufen sortieren.
    $by_views = $start >= $since;
    uasort($prod, function ($a, $b) use ($by_views) {
        return $by_views
            ? array($b['views'], $b['buys']) <=> array($a['views'], $a['buys'])
            : array($b['buys'], $b['views']) <=> array($a['buys'], $a['views']);
    });
    $tot_views = array_sum(array_column($prod, 'views'));
    $tot_buys = 0;
    foreach ($prod as $p) {
        if ($p['views'] > 0) {
            $tot_buys += min($p['buys'], $p['views']);
        }
    }
    $avg_rate = $tot_views ? $tot_buys / $tot_views : 0;

    /* Tage, Geraete, Laender */
    $daily = array();
    for ($t = strtotime($start); $t <= strtotime($end); $t += DAY_IN_SECONDS) {
        $daily[date('Y-m-d', $t)] = array('v' => 0, 'o' => 0);
    }
    $devices = array();
    $countries = array();
    foreach ($visits as $v) {
        if (isset($daily[$v['day']])) {
            $daily[$v['day']]['v']++;
        }
        $devices[$v['device'] ?: '?'] = ($devices[$v['device'] ?: '?'] ?? 0) + 1;
        $countries[$v['country'] ?: '?'] = ($countries[$v['country'] ?: '?'] ?? 0) + 1;
    }
    foreach ($orders as $o) {
        if (isset($daily[$o['day']])) {
            $daily[$o['day']]['o']++;
        }
    }
    arsort($devices);
    arsort($countries);
    $countries = array_slice($countries, 0, 8, true);

    $base = admin_url('admin.php?page=peptrium-dashboard&tab=analyse');
    $today = current_time('Y-m-d');
    $quick = array(
        '7 Tage' => array(date('Y-m-d', strtotime('-6 days', current_time('timestamp'))), $today),
        '30 Tage' => array(date('Y-m-d', strtotime('-29 days', current_time('timestamp'))), $today),
        '90 Tage' => array(date('Y-m-d', strtotime('-89 days', current_time('timestamp'))), $today),
        'Dieser Monat' => array(current_time('Y-m-01'), $today),
        'Letzter Monat' => array(date('Y-m-01', strtotime('first day of last month', current_time('timestamp'))), date('Y-m-t', strtotime('last day of last month', current_time('timestamp')))),
    );
    ?>
    <style>
      .sp-an-quick{display:flex;gap:8px;flex-wrap:wrap;margin:-8px 0 18px}
      .sp-an-quick a{font-size:12px;font-weight:600;padding:6px 12px;border-radius:999px;background:#F2F3F4;color:#0D0F12;text-decoration:none}
      .sp-an-quick a.on{background:#0D0F12;color:#fff}
      .sp-an-note{background:#FFF8E6;border:1px solid #F5D78E;border-radius:12px;padding:10px 14px;font-size:13px;margin-bottom:18px}
      .sp-an-ok{background:#E8F7F0;border-color:#9ED9BE}
      .sp-an-funnel-row{display:grid;grid-template-columns:180px 1fr 110px;gap:12px;align-items:center;margin-bottom:10px;font-size:14px}
      .sp-an-funnel-bar{height:26px;border-radius:7px;background:#F2F3F4;overflow:hidden}
      .sp-an-funnel-bar span{display:block;height:100%;background:linear-gradient(90deg,#3B82F6,#1D4ED8);border-radius:7px}
      .sp-an-drop{font-size:12px;color:#B91C1C;margin:-4px 0 10px 192px}
      .sp-an-muted{color:#8A9099;font-size:12px}
      .sp-an-warn{display:inline-block;background:#FEE2E2;color:#991B1B;border-radius:999px;padding:2px 8px;font-size:11px;font-weight:700}
      .sp-an-copy{cursor:pointer;border:1px solid #DCDEE0;background:#fff;border-radius:7px;padding:3px 9px;font-size:12px}
      .sp-an-url{font-family:monospace;font-size:13px}
      .sp-an-form{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;margin-top:14px}
      .sp-an-form label{display:flex;flex-direction:column;font-size:12px;color:#4B5157;gap:4px}
      .sp-an-form input,.sp-an-form select{min-width:160px;max-width:320px}
      .sp-an-chart{display:flex;align-items:flex-end;gap:3px;height:160px}
      .sp-an-chart div{flex:1;background:linear-gradient(180deg,#3B82F6,#1D4ED8);border-radius:4px 4px 0 0;min-height:2px;position:relative}
      .sp-an-chart div.has-o{background:linear-gradient(180deg,#0E9F6E,#057A55)}
      .sp-an-small{display:grid;grid-template-columns:1fr 1fr;gap:20px}
      @media (max-width:900px){.sp-an-small{grid-template-columns:1fr}.sp-an-funnel-row{grid-template-columns:110px 1fr 80px}.sp-an-drop{margin-left:122px}}
    </style>

    <div class="sp-an-quick">
      <?php foreach ($quick as $label => $q): ?>
        <a class="<?php echo ($q[0] === $start && $q[1] === $end) ? 'on' : ''; ?>" href="<?php echo esc_url(add_query_arg(array('sp_start' => $q[0], 'sp_end' => $q[1]), $base)); ?>"><?php echo esc_html($label); ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($notice): ?><div class="sp-an-note sp-an-ok"><?php echo esc_html($notice); ?></div><?php endif; ?>
    <?php if ($start < $since): ?>
      <div class="sp-an-note">Die Besucherzählung läuft seit <strong><?php echo esc_html(date_i18n('d.m.Y', strtotime($since))); ?></strong>. Für die Zeit davor siehst du nur Bestellungen und deren Herkunft (aus WooCommerce), noch keine Besucher-, Trichter- oder Produktaufruf-Zahlen.</div>
    <?php endif; ?>

    <div class="sp-dash-kpis">
      <?php
      sp_dashboard_kpi('Besucher', $cur['visitors'] === null ? '–' : esc_html(number_format_i18n($cur['visitors'])), 'blue', sp_an_delta($cur['visitors'], $prev['visitors']) ?: 'eindeutig pro Tag, ohne Cookies');
      sp_dashboard_kpi('Bestellungen', esc_html(number_format_i18n($cur['orders'])), 'purple', sp_an_delta($cur['orders'], $prev['orders']) ?: 'inkl. noch offener Vorkasse');
      sp_dashboard_kpi('Umsatz (bezahlt)', sp_dashboard_money($cur['revenue']), 'green', sp_an_delta($cur['revenue'], $prev['revenue']) ?: 'Warenwert, ohne Aufladungen');
      sp_dashboard_kpi('Kaufquote', $cur['cr'] === null ? '–' : esc_html(sp_an_fmt_pct($cur['cr'])), 'amber', 'Bestellungen pro 100 Besucher');
      sp_dashboard_kpi('Ø Bestellwert', sp_dashboard_money($cur['aov']), 'dark', 'bezahlte Bestellungen');
      ?>
    </div>

    <div class="sp-dash-card">
      <h2>Besucher pro Tag <span class="sp-an-muted">(grün = Tag mit Bestellung)</span></h2>
      <?php $max = max(1, max(array_column($daily, 'v'))); ?>
      <div class="sp-an-chart">
        <?php foreach ($daily as $d => $x): ?>
          <div class="<?php echo $x['o'] ? 'has-o' : ''; ?>" style="height:<?php echo esc_attr(max(1, round($x['v'] / $max * 100))); ?>%" title="<?php echo esc_attr(date_i18n('D d.m.', strtotime($d)) . ': ' . $x['v'] . ' Besucher, ' . $x['o'] . ' Bestellung(en)'); ?>"></div>
        <?php endforeach; ?>
      </div>
      <div class="sp-dash-chart-labels"><span><?php echo esc_html(date_i18n('d.m.', strtotime($start))); ?></span><span><?php echo esc_html(date_i18n('d.m.', strtotime($end))); ?></span></div>
    </div>

    <div class="sp-dash-card">
      <h2>Kauf-Trichter – wo steigen Besucher aus?</h2>
      <?php if ($f['pv'] === 0): ?>
        <p class="sp-dash-empty">Noch keine Besucherdaten im Zeitraum.</p>
      <?php else:
          $steps = array('pv' => 'Besucher', 'product' => 'Produkt angesehen', 'cart' => 'In den Warenkorb', 'checkout' => 'Kasse geöffnet', 'order' => 'Bestellt');
          $prev_n = null;
          foreach ($steps as $k => $label):
              $n = $f[$k];
              if ($prev_n !== null && $prev_n > 0):
                  $lost = 100 - sp_an_pct($n, $prev_n); ?>
                  <div class="sp-an-drop">↓ <?php echo esc_html(sp_an_fmt_pct(max(0, $lost))); ?> steigen hier aus</div>
              <?php endif; ?>
              <div class="sp-an-funnel-row">
                <strong><?php echo esc_html($label); ?></strong>
                <div class="sp-an-funnel-bar"><span style="width:<?php echo esc_attr(max(1, round(sp_an_pct($n, $f['pv'])))); ?>%"></span></div>
                <span><?php echo esc_html(number_format_i18n($n)); ?> <span class="sp-an-muted">(<?php echo esc_html(sp_an_fmt_pct(sp_an_pct($n, $f['pv']))); ?>)</span></span>
              </div>
          <?php $prev_n = $n; endforeach; ?>
        <p class="sp-an-muted">Gezählt werden Besucher pro Tag. Wer an einem Tag schaut und am nächsten Tag kauft, erscheint als zwei Besuche – die Zahl „Bestellt“ hier kann deshalb kleiner sein als die Bestellungen oben.</p>
      <?php endif; ?>
    </div>

    <div class="sp-dash-card">
      <h2>Herkunft – welcher Kanal bringt Umsatz?</h2>
      <?php if (empty($src)): ?>
        <p class="sp-dash-empty">Noch keine Daten.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Quelle</th><th>Besucher</th><th>Warenkorb</th><th>Bestellungen</th><th>Umsatz (bezahlt)</th><th>Kaufquote</th></tr></thead>
          <tbody>
          <?php foreach ($src as $s => $x): ?>
            <tr>
              <td><strong><?php echo esc_html(sp_an_source_label($s)); ?></strong></td>
              <td><?php echo esc_html(number_format_i18n($x['visitors'])); ?></td>
              <td><?php echo esc_html(number_format_i18n($x['cart'])); ?></td>
              <td><?php echo esc_html(number_format_i18n($x['orders'])); ?></td>
              <td><?php echo sp_dashboard_money($x['revenue']); ?></td>
              <td><?php echo $x['visitors'] && $start >= $since ? esc_html(sp_an_fmt_pct(sp_an_pct($x['orders'], $x['visitors']))) : '–'; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p class="sp-an-muted"><strong>Partner</strong> = über einen Partner-Link oder mit Partner-Provision (egal ob der Partner auf TikTok oder Instagram postet). <strong>Eigener …-Account (Link)</strong> = über deine eigenen Tracking-Links. <strong>TikTok/Instagram (ohne Link)</strong> = aus der App gekommen, aber ohne Partner- oder eigenen Link – z. B. dein Account, bevor der neue Bio-Link drin ist. Für ältere Bestellungen stammt die Herkunft aus WooCommerces eigener Erfassung plus den SliceWP-Provisionen.</p>
      <?php endif; ?>
    </div>

    <div class="sp-dash-card">
      <h2>Partner (Affiliates) – wer bringt Umsatz?</h2>
      <?php if (empty($partners)): ?>
        <p class="sp-dash-empty">Im Zeitraum keine Partner-Besucher oder -Bestellungen.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Partner</th><th>Besucher</th><th>Bestellungen</th><th>Umsatz (bezahlt)</th><th>Provision</th><th>Bestellungen kamen über</th></tr></thead>
          <tbody>
          <?php foreach ($partners as $id => $x):
              arsort($x['via']);
              $via = array();
              foreach ($x['via'] as $pl => $n) {
                  $via[] = ($pl === 'unbekannt' ? 'nicht erkennbar' : preg_replace('/ \(ohne Link\)$/', '', sp_an_source_label($pl))) . ' ' . $n;
              } ?>
            <tr>
              <td><strong><?php echo esc_html(sp_an_affiliate_name($id)); ?></strong></td>
              <td><?php echo $start >= $since ? esc_html(number_format_i18n($x['visitors'])) : '–'; ?></td>
              <td><?php echo esc_html(number_format_i18n($x['orders'])); ?></td>
              <td><?php echo sp_dashboard_money($x['revenue']); ?></td>
              <td><?php echo sp_dashboard_money($x['commission']); ?></td>
              <td class="sp-an-muted"><?php echo esc_html($via ? implode(', ', $via) : '–'); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p class="sp-an-muted">Partner-Bestellungen = Bestellungen, für die SliceWP eine Provision gebucht hat (auch wenn der Kunde über TikTok/Instagram des Partners kam). „Bestellungen kamen über“ = Plattform, von der der Kunde zuletzt in den Shop kam (z. B. das TikTok-Video des Partners).</p>
      <?php endif; ?>
    </div>

    <div class="sp-dash-card">
      <h2>Tracking-Links – welcher Post / welches Video verkauft?</h2>
      <p class="sp-an-muted">Nimm für jeden Kanal (und gern für einzelne Videos) einen eigenen Link. Die Links sind neutral (z. B. /l/k7m2), leiten ohne sichtbare Zusätze auf die Zielseite weiter und merken sich serverseitig, woher der Besucher kam.</p>
      <table class="sp-dash-table">
        <thead><tr><th>Name</th><th>Link</th><th>Ziel</th><th>Besucher</th><th>Bestellungen</th><th>Umsatz</th><th></th></tr></thead>
        <tbody>
        <?php foreach (sp_an_links() as $slug => $l):
            $x = isset($camp[$slug]) ? $camp[$slug] : $blank; ?>
          <tr>
            <td><strong><?php echo esc_html($l['label']); ?></strong><br><span class="sp-an-muted"><?php echo esc_html(sp_an_channels()[$l['channel']] ?? $l['channel']); ?></span></td>
            <td><span class="sp-an-url"><?php echo esc_html(sp_an_link_url($slug)); ?></span> <button type="button" class="sp-an-copy" data-copy="<?php echo esc_attr(sp_an_link_url($slug)); ?>">Kopieren</button></td>
            <td class="sp-an-muted"><?php echo esc_html($l['target']); ?></td>
            <td><?php echo esc_html(number_format_i18n($x['visitors'])); ?></td>
            <td><?php echo esc_html(number_format_i18n($x['orders'])); ?></td>
            <td><?php echo sp_dashboard_money($x['revenue']); ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Link wirklich löschen? Wer ihn danach anklickt, landet auf der Startseite.');">
                <?php wp_nonce_field('sp_an_admin'); ?>
                <input type="hidden" name="sp_an_action" value="delete_link"><input type="hidden" name="slug" value="<?php echo esc_attr($slug); ?>">
                <button class="button-link" style="color:#B91C1C">Löschen</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php
      $other_camps = array_filter(array_diff_key($camp, sp_an_links()), function ($k) {
          return strpos($k, 'aff-') !== 0;
      }, ARRAY_FILTER_USE_KEY);
      if ($other_camps): ?>
        <p class="sp-an-muted" style="margin-top:12px">Weitere Kampagnen (utm_campaign aus anderen Links):
          <?php foreach ($other_camps as $c => $x) {
              echo esc_html($c . ': ' . $x['visitors'] . ' Besucher, ' . $x['orders'] . ' Bestellungen') . ' · ';
          } ?>
        </p>
      <?php endif; ?>
      <form method="post" class="sp-an-form">
        <?php wp_nonce_field('sp_an_admin'); ?>
        <input type="hidden" name="sp_an_action" value="add_link">
        <label>Name <input type="text" name="label" placeholder="z. B. TikTok Video Retatrutide" required></label>
        <label>Kanal <select name="channel"><?php foreach (sp_an_channels() as $k => $l) { echo '<option value="' . esc_attr($k) . '">' . esc_html($l) . '</option>'; } ?></select></label>
        <label>Zielseite <select name="target">
          <?php foreach (sp_an_target_options() as $path => $l) { echo '<option value="' . esc_attr($path) . '">' . esc_html($l) . '</option>'; } ?>
          <option value="custom">Andere Adresse…</option>
        </select></label>
        <label style="display:none" id="sp-an-custom-wrap">&nbsp;<input type="text" name="target_custom" placeholder="/seite/" style="display:none"></label>
        <label>Kennung (optional) <input type="text" name="slug" placeholder="leer = Zufallscode"></label>
        <button class="button button-primary">Link anlegen</button>
      </form>
    </div>

    <div class="sp-dash-card">
      <h2>Produkte – angesehen vs. gekauft</h2>
      <?php if (empty($prod)): ?>
        <p class="sp-dash-empty">Noch keine Daten.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Produkt</th><th>Aufrufe</th><th>Warenkorb</th><th>Käufe</th><th>Kaufquote</th><th></th></tr></thead>
          <tbody>
          <?php foreach (array_slice($prod, 0, 25, true) as $pid => $x):
              $p = wc_get_product($pid);
              if (!$p) {
                  continue;
              }
              $rate = $x['views'] ? $x['buys'] / $x['views'] : null;
              $flag = $x['views'] >= 15 && $rate !== null && $rate < $avg_rate * 0.5; ?>
            <tr>
              <td><a href="<?php echo esc_url(get_permalink($pid)); ?>" target="_blank"><?php echo esc_html($p->get_name()); ?></a></td>
              <td><?php echo $start >= $since ? esc_html(number_format_i18n($x['views'])) : '–'; ?></td>
              <td><?php echo $start >= $since ? esc_html(number_format_i18n($x['cart'])) : '–'; ?></td>
              <td><?php echo esc_html(number_format_i18n($x['buys'])); ?></td>
              <td><?php echo $rate !== null && $start >= $since ? esc_html(sp_an_fmt_pct($rate * 100)) : '–'; ?></td>
              <td><?php if ($flag): ?><span class="sp-an-warn" title="Wird oft angesehen, aber deutlich seltener gekauft als der Durchschnitt – Preis, Bilder oder Text prüfen.">viel angesehen, wenig gekauft</span><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="sp-an-small">
      <div class="sp-dash-card">
        <h2>Geräte</h2>
        <table class="sp-dash-table"><tbody>
          <?php foreach ($devices as $d => $n): ?>
            <tr><td><?php echo esc_html(array('mobil' => 'Handy', 'desktop' => 'Computer', 'tablet' => 'Tablet')[$d] ?? $d); ?></td><td><?php echo esc_html(number_format_i18n($n)); ?> <span class="sp-an-muted">(<?php echo esc_html(sp_an_fmt_pct(sp_an_pct($n, count($visits)))); ?>)</span></td></tr>
          <?php endforeach; ?>
        </tbody></table>
      </div>
      <div class="sp-dash-card">
        <h2>Länder</h2>
        <table class="sp-dash-table"><tbody>
          <?php foreach ($countries as $c => $n): ?>
            <tr><td><?php echo esc_html($c === '?' ? 'unbekannt' : (WC()->countries->countries[$c] ?? $c)); ?></td><td><?php echo esc_html(number_format_i18n($n)); ?></td></tr>
          <?php endforeach; ?>
        </tbody></table>
      </div>
    </div>

    <div class="sp-dash-card">
      <h2>Einrichtung: Google, Bing, Ausfall-Überwachung</h2>
      <form method="post" class="sp-an-form" style="margin-top:0">
        <?php wp_nonce_field('sp_an_admin'); ?>
        <input type="hidden" name="sp_an_action" value="verify">
        <label>Google Search Console – Code (HTML-Tag) <input type="text" name="google" value="<?php echo esc_attr(get_option('sp_an_google_verify', '')); ?>" placeholder='&lt;meta name="google-site-verification" ...&gt;' style="min-width:320px"></label>
        <label>Bing Webmaster – Code <input type="text" name="bing" value="<?php echo esc_attr(get_option('sp_an_bing_verify', '')); ?>" placeholder='&lt;meta name="msvalidate.01" ...&gt;' style="min-width:260px"></label>
        <button class="button button-primary">Speichern</button>
      </form>
      <p class="sp-an-muted">Einfach das ganze Tag oder nur den Code einfügen. Sitemap für Google/Bing: <span class="sp-an-url"><?php echo esc_html(home_url('/wp-sitemap.xml')); ?></span> <button type="button" class="sp-an-copy" data-copy="<?php echo esc_attr(home_url('/wp-sitemap.xml')); ?>">Kopieren</button></p>
      <p class="sp-an-muted">UptimeRobot: Monitor-Typ <strong>„Keyword“</strong>, Adresse <span class="sp-an-url"><?php echo esc_html(home_url('/?sp_health=1')); ?></span> <button type="button" class="sp-an-copy" data-copy="<?php echo esc_attr(home_url('/?sp_health=1')); ?>">Kopieren</button>, Keyword <strong>PEPTRIUM-OK</strong>, Alarm wenn das Keyword <strong>fehlt</strong>. Prüft Datenbank, WooCommerce, Kasse, Zahlungsarten und Cache.</p>
      <p class="sp-an-muted">Aufzeichnungen & Heatmaps: <a href="https://clarity.microsoft.com/projects/view/ylbjsiarei/dashboard" target="_blank" rel="noopener">Microsoft Clarity öffnen</a> (sieht nur Besucher, die im Cookie-Banner zustimmen).</p>
    </div>

    <script>
    document.querySelectorAll('.sp-an-copy').forEach(function(b){b.addEventListener('click',function(){
      var t=b.getAttribute('data-copy'),done=function(){var o=b.textContent;b.textContent='Kopiert ✓';setTimeout(function(){b.textContent=o;},1500);};
      if(navigator.clipboard){navigator.clipboard.writeText(t).then(done);}else{var i=document.createElement('input');i.value=t;document.body.appendChild(i);i.select();document.execCommand('copy');i.remove();done();}
    });});
    var sel=document.querySelector('.sp-an-form select[name=target]');
    if(sel){sel.addEventListener('change',function(){var w=document.getElementById('sp-an-custom-wrap'),i=w.querySelector('input');w.style.display=i.style.display=sel.value==='custom'?'':'none';});}
    </script>
    <?php
}
