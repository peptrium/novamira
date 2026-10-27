<?php
/**
 * Peptrium Seiten-Cache (2026-10-23).
 *
 * Fertiges HTML für ANONYME Besucher in Redis (Objekt-Cache) speichern und
 * beim nächsten Aufruf direkt ausliefern, bevor Plugins/Theme laden
 * (vorher ~1,2–2,5 s TTFB, ~1,1 s davon PHP).
 *
 * Vorsichtig gebaut:
 *  - nur GET/HEAD ohne Query-String, nur Pfade aus der Freigabe (Option sp_pc['allow'])
 *  - nie mit Login-/Warenkorb-/Vorschau-/Aktions-Cookies (nur harmlose Cookies erlaubt)
 *  - gespeichert wird nur HTML mit Status 200, ohne Set-Cookie, ohne no-cache-Header,
 *    nicht für Warenkorb/Kasse/Konto/404/Suche
 *  - Lebensdauer 10 min, kürzer vor einem Halloween-Phasenwechsel
 *  - wird bei Produkt-/Seiten-/Lager-/Einstellungsänderungen komplett geleert
 *  - Wartungsmodus an → nichts aus dem Cache
 * Aus: Peptrium Dashboard → Seiten-Cache, oder diese Datei löschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

const SP_PC_TTL = 600;

function sp_pc_opts() {
    $o = get_option('sp_pc');
    return wp_parse_args(is_array($o) ? $o : [], ['on' => 0, 'allow' => '^/$']);
}

/** Request ist grundsätzlich cachebar (ohne WP-Funktionen, läuft vor allem anderen). */
function sp_pc_request_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    $ok = false;
    if (PHP_SAPI === 'cli' || (defined('WP_CLI') && WP_CLI)) return false;
    if (defined('DOING_AJAX') || defined('DOING_CRON') || defined('REST_REQUEST') || defined('XMLRPC_REQUEST')) return false;
    $m = $_SERVER['REQUEST_METHOD'] ?? '';
    if ($m !== 'GET' && $m !== 'HEAD') return false;
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri === '' || strpos($uri, '?') !== false) return false;
    if (!preg_match('~^/[a-z0-9/_\-]*$~', $uri)) return false;
    if (preg_match('~^/(wp-|warenkorb|kasse|mein-konto|start/|bestellstatus|partnerkonto|affiliate|passwort|l/|go/|feed|sitemap|robots|abo-stack|mein-abo|guthaben|wp-sitemap)~', $uri)) return false;
    if (preg_match('~/feed/$~', $uri)) return false;
    foreach (array_keys($_COOKIE) as $c) {
        if (!preg_match('/^(sp_age_gate|sp_nlp_closed|real_cookie_banner.*|_ga.*|_gid|_gcl_.*|_clck|_clsk|_fbp|_ttp|omnisend.*|soundest.*|sbjs_.*|_cfuvid|__cf.*|cf_.*|slicewp_aff|slicewp_visit|wordpress_test_cookie|sp_clarity_seen|CLID|MUID|MR|SM|SRM_B|ANONCHK|_uet.*)$/', $c)) return false;
    }
    $o = sp_pc_opts();
    if (empty($o['on'])) return false;
    if (!@preg_match('~' . $o['allow'] . '~', $uri)) return false;
    $w = get_option('sp_wartung');
    if (is_array($w) && !empty($w['on']) && (empty($w['until']) || (int) $w['until'] > time())) return false;
    return $ok = true;
}

/** Gleiche Regel wie wp_is_mobile() – Astra setzt danach andere Body-Klassen, also eigene Handy-Version. */
function sp_pc_device() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera Mini|Opera Mobi/', $ua) ? 'm' : 'd';
}

/** Bots bekommen eine Seite ohne Statistik-Skripte (sp_an_should_track) → nie speichern, nur ausliefern. */
function sp_pc_is_bot() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|headless|lighthouse|pagespeed|preview|monitor|uptime|curl|wget|python|scrapy|facebookexternalhit|embedly|whatsapp\/|telegrambot/i', $ua);
}

function sp_pc_key() {
    $host = strtolower(preg_replace('/[^a-z0-9.\-]/i', '', $_SERVER['HTTP_HOST'] ?? ''));
    return (int) get_option('sp_pc_gen', 1) . ':' . sp_pc_device() . ':' . md5($host . ($_SERVER['REQUEST_URI'] ?? ''));
}

function sp_pc_stat($k) {
    if (wp_cache_incr($k, 1, 'sp_pc_stat') === false) wp_cache_set($k, 1, 'sp_pc_stat', 2 * DAY_IN_SECONDS);
}

/* ---------- Ausliefern (sofort beim Laden dieser Datei) ---------- */
if (sp_pc_request_ok()) {
    $sp_pc_hit = wp_cache_get(sp_pc_key(), 'sp_pc');
    if (is_array($sp_pc_hit) && !empty($sp_pc_hit['h']) && ($html = @gzuncompress($sp_pc_hit['h'])) !== false) {
        sp_pc_stat('hit_' . gmdate('Ymd'));
        header('Content-Type: text/html; charset=UTF-8');
        header('X-SP-Cache: HIT ' . max(0, time() - (int) $sp_pc_hit['t']) . 's');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD') echo $html;
        exit;
    }
    sp_pc_stat('miss_' . gmdate('Ymd'));
    $GLOBALS['sp_pc_store'] = null; // wird in template_redirect entschieden
    $GLOBALS['sp_pc_k'] = sp_pc_key();
    header('X-SP-Cache: MISS');
    ob_start('sp_pc_ob');
}

/** Ob die Seite gespeichert werden darf – vor dem Ausgeben entscheiden (im ob-Callback keine WP/WC-Funktionen). */
add_action('template_redirect', function () {
    if (!array_key_exists('sp_pc_store', $GLOBALS)) return;
    $ttl = SP_PC_TTL;
    $no = sp_pc_is_bot() || is_user_logged_in() || is_404() || is_search() || is_feed() || is_preview() || is_robots()
        || (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page()))
        || (function_exists('WC') && WC()->session && WC()->session->has_session());
    // Halloween: nie über einen Phasenwechsel hinweg cachen
    $hw = get_option('sp_hw');
    if (is_array($hw) && !empty($hw['on'])) {
        foreach (['early', 'start', 'end'] as $k) {
            $t = (int) ($hw[$k] ?? 0);
            if ($t > time()) $ttl = min($ttl, $t - time());
        }
    }
    if ($ttl < 30) $no = true;
    $GLOBALS['sp_pc_store'] = $no ? false : $ttl;
}, 9999);

function sp_pc_ob($html, $phase = 0) {
    if (!($phase & PHP_OUTPUT_HANDLER_FINAL) && !($phase & PHP_OUTPUT_HANDLER_END)) return $html;
    $ttl = $GLOBALS['sp_pc_store'] ?? null;
    if (!$ttl || defined('DONOTCACHEPAGE') || http_response_code() !== 200) return $html;
    $len = strlen($html);
    if ($len < 5000 || $len > 3000000 || stripos($html, '</html>') === false || strpos($html, 'id="wpadminbar"') !== false) return $html;
    foreach (headers_list() as $h) {
        $l = strtolower($h);
        if (strpos($l, 'set-cookie:') === 0) return $html;
        if (strpos($l, 'cache-control:') === 0 && preg_match('/no-cache|no-store|private/', $l)) return $html;
        if (strpos($l, 'content-type:') === 0 && strpos($l, 'text/html') === false) return $html;
        if (strpos($l, 'location:') === 0) return $html;
    }
    wp_cache_set($GLOBALS['sp_pc_k'], ['t' => time(), 'h' => gzcompress($html, 6)], 'sp_pc', (int) $ttl);
    return $html;
}

/* ---------- Leeren ---------- */
function sp_pc_purge($why = '') {
    static $done = false;
    if ($done) return;
    $done = true;
    $log = wp_cache_get('log', 'sp_pc_stat');
    $log = is_array($log) ? array_slice($log, -29) : [];
    $log[] = gmdate('d.m. H:i:s') . ' ' . $why . ' ' . substr($_SERVER['REQUEST_URI'] ?? '', 0, 60);
    wp_cache_set('log', $log, 'sp_pc_stat', 7 * DAY_IN_SECONDS);
    update_option('sp_pc_gen', ((int) get_option('sp_pc_gen', 1)) + 1, true);
}
foreach (['save_post', 'deleted_post', 'trashed_post', 'edited_term', 'created_term', 'delete_term', 'wp_update_nav_menu',
    'woocommerce_product_set_stock_status', 'woocommerce_variation_set_stock_status', 'woocommerce_product_set_stock',
    'woocommerce_variation_set_stock', 'woocommerce_update_product', 'woocommerce_update_product_variation',
    'elementor/core/files/clear_cache', 'switch_theme', 'upgrader_process_complete', 'activated_plugin', 'deactivated_plugin',
    'wp_set_comment_status', 'comment_post'] as $h) {
    add_action($h, function () use ($h) {
        if ($h === 'save_post' || $h === 'deleted_post' || $h === 'trashed_post') {
            $p = get_post(func_get_arg(0));
            if ($p && in_array($p->post_type, ['shop_order', 'shop_order_placehold', 'revision', 'customize_changeset', 'oembed_cache', 'wp_global_styles'], true)) return;
        }
        sp_pc_purge($h);
    });
}
add_action('updated_option', function ($name) {
    if ($name === 'sp_pc_gen' || $name === 'sp_pc') return;
    // Elementor/RCB schreiben diese Optionen beim normalen Seitenaufruf – kein Inhaltswechsel (sonst wird der Cache dauernd geleert, 2026-10-27)
    if (preg_match('/^(elementor_atomic_cache_validity|elementor_.*_cache|rcb-notice-states)/', $name)) return;
    if (preg_match('/^(sp_hw|sp_redesign|sp_wartung|sp_nlp|sp_cmp|sp_seo|sp_rd_webp_map|sp_bbv|sp_imp|sp_an_google|sp_an_bing|site_icon|blogname|blogdescription|woocommerce_(price|currency|default)|woocommerce_free_shipping|rcb-|elementor_)/', $name)) sp_pc_purge('opt:' . $name);
});
add_action('added_option', function ($name) {
    if (preg_match('/^(sp_hw|sp_redesign|sp_wartung|sp_nlp)/', $name)) sp_pc_purge('add:' . $name);
});
add_action('update_option_sp_pc', function () { sp_pc_purge('einstellung'); });

/* ---------- Admin ---------- */
add_action('admin_menu', function () {
    $parent = 'sp-peptrium-dashboard';
    global $admin_page_hooks;
    if (empty($admin_page_hooks[$parent])) $parent = 'woocommerce';
    add_submenu_page($parent, 'Seiten-Cache', 'Seiten-Cache', 'manage_woocommerce', 'sp-seiten-cache', 'sp_pc_admin');
}, 99);

function sp_pc_admin() {
    if (!current_user_can('manage_woocommerce')) return;
    if (!empty($_POST['sp_pc_nonce']) && wp_verify_nonce($_POST['sp_pc_nonce'], 'sp_pc')) {
        if (isset($_POST['purge'])) {
            sp_pc_purge('manuell');
            echo '<div class="notice notice-success"><p>Cache geleert.</p></div>';
        } else {
            $o = sp_pc_opts();
            $o['on'] = empty($_POST['on']) ? 0 : 1;
            $a = trim(wp_unslash($_POST['allow'] ?? ''));
            if ($a !== '' && @preg_match('~' . $a . '~', '/') !== false) $o['allow'] = $a;
            update_option('sp_pc', $o, true);
            echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
        }
    }
    $o = sp_pc_opts();
    $d = gmdate('Ymd');
    $hit = (int) wp_cache_get('hit_' . $d, 'sp_pc_stat');
    $miss = (int) wp_cache_get('miss_' . $d, 'sp_pc_stat');
    ?>
    <div class="wrap"><h1>Seiten-Cache</h1>
    <p>Fertige Seiten werden für Besucher ohne Login und ohne Warenkorb 10 Minuten zwischengespeichert. Warenkorb, Kasse, Konto, Vorschau-Links und Aktions-Cookies laufen immer live. Bei Änderungen an Produkten, Seiten, Lager oder Aktions-Schaltern wird der Cache automatisch geleert.</p>
    <form method="post"><?php wp_nonce_field('sp_pc', 'sp_pc_nonce'); ?>
    <p><label><input type="checkbox" name="on" value="1" <?php checked($o['on'], 1); ?>> Seiten-Cache aktiv</label></p>
    <p><label>Freigegebene Pfade (Regex):<br><input type="text" name="allow" value="<?php echo esc_attr($o['allow']); ?>" style="width:520px;font-family:monospace"></label></p>
    <p><button class="button button-primary">Speichern</button> <button class="button" name="purge" value="1">Cache jetzt leeren</button></p>
    </form>
    <p>Heute (UTC): <strong><?php echo $hit; ?></strong> aus dem Cache, <strong><?php echo $miss; ?></strong> neu erzeugt.</p>
    <?php $log = wp_cache_get('log', 'sp_pc_stat'); if (is_array($log) && $log): ?>
    <h2>Zuletzt geleert</h2><pre style="background:#fff;padding:10px;max-width:760px;white-space:pre-wrap"><?php echo esc_html(implode("\n", array_reverse($log))); ?></pre>
    <?php endif; ?>
    </div>
    <?php
}
