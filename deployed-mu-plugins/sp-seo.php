<?php
/**
 * Plugin Name: SP SEO
 * Description: Titel, Meta-Beschreibungen, Open-Graph-Vorschaubild, Canonical fuer Kategorien und strukturierte Daten (Organisation, WebSite, Produkt, Breadcrumbs). Kein SEO-Plugin auf der Seite – alles hier.
 *
 * - Website-Icon (Favicon fuer Google/Browser): Option site_icon = Anhang 919 (peptrium-icon-512.png).
 * - Standard-Vorschaubild (WhatsApp/Telegram/Facebook): Option sp_seo_og_default = Anhang 920 (peptrium-og.jpg).
 * - Produkte: Product-JSON-LD mit Preis/Verfuegbarkeit, bewusst OHNE Bewertungen (die Sterne im Shop sind feste Werte,
 *   als strukturierte Daten waeren sie ein Verstoss gegen die Google-Richtlinien).
 * Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

const SP_SEO_SITE = 'Peptrium';

/** Handgeschriebene Titel/Beschreibungen fuer Seiten (Slug => [Titel, Beschreibung]). */
function sp_seo_pages() {
    return [
        'alle-produkte' => ['Peptide kaufen – alle Forschungspeptide', 'Alle Forschungspeptide von Peptrium: Retatrutide, GHK-Cu, Mots-C, BPC-157 und mehr – HPLC-geprüft, ab 3 Stück −10 %, Versand aus Deutschland in 2 Werktagen.'],
        'shop' => ['Shop – Forschungspeptide kaufen', 'Forschungspeptide in Laborqualität: HPLC-geprüft, diskret verpackt, Versand aus Deutschland in 2 Werktagen, gratis ab 100 €.'],
        'alle-peptrium-pens' => ['Peptrium-Pens – vorgefüllte Peptid-Pens', 'Peptrium-Pens mit Retatrutide, GHK-Cu oder Mots-C: fertig gemischt, exakte Dosierung per Klick, kein Anmischen. Versand aus Deutschland.'],
        'zubehoer' => ['Zubehör – Bac Water, Spritzen & Pen-Nadeln', 'Laborzubehör für Peptide: bakteriostatisches Wasser, Insulinspritzen und Pen-Nadeln – steril, günstig, Versand aus Deutschland in 2 Werktagen.'],
        'peptid-rechner' => ['Peptid-Rechner – Rekonstitution & Dosierung berechnen', 'Kostenloser Peptid-Rechner: Menge Bac Water, Konzentration und Einheiten auf der Spritze in Sekunden berechnen.'],
        'retatrutide-dosierung-rechner' => ['Retatrutide-Rechner – Dosierung berechnen', 'Retatrutide-Rechner für die Forschung: Konzentration nach dem Anmischen und Einheiten pro Dosis einfach berechnen.'],
        'lagerungs-guide' => ['Lagerungs-Guide – Peptide richtig lagern', 'So lagerst du Peptide richtig: Temperatur, Haltbarkeit vor und nach dem Anmischen – übersichtlich für jedes Peptid.'],
        'peptid-lexikon' => ['Peptid-Lexikon – Forschungspeptide erklärt', 'Das Peptid-Lexikon von Peptrium: Herkunft, Wirkmechanismus und Forschungsstand der wichtigsten Peptide verständlich erklärt.'],
        'ueber-uns' => ['Über uns', 'Peptrium liefert Forschungspeptide in geprüfter Qualität – mit transparenter Analytik, diskretem Versand und persönlichem Support.'],
        'kontakt' => ['Kontakt', 'Fragen zu Bestellung, Versand oder Produkten? Schreib uns per E-Mail oder Telegram – wir antworten schnell.'],
        'versand-lieferzeit' => ['Versand & Lieferzeit', 'Versand mit DHL aus Deutschland in 2 Werktagen, neutral verpackt, mit Sendungsnummer. Gratisversand ab 100 €.'],
        'abo-modell' => ['Abo-Modell – 15 % sparen', 'Mit dem Peptrium-Abo sparst du ab der 2. Lieferung dauerhaft 15 %. Intervall frei wählbar, jederzeit pausieren oder kündigen.'],
        'abo-stack' => ['Abo-Stack zusammenstellen', 'Stell dir dein eigenes Peptid-Abo zusammen: Produkte wählen, Intervall festlegen und ab der 2. Lieferung 15 % sparen.'],
        'bestellstatus' => ['Bestellstatus prüfen', 'Bestellnummer und E-Mail eingeben und sofort sehen, wo deine Peptrium-Bestellung gerade ist.'],
        'forschungsnutzung' => ['Forschungsnutzung', 'Alle Produkte von Peptrium sind ausschließlich für Laborforschung bestimmt – nicht für den menschlichen oder tierischen Gebrauch.'],
        'affiliate-programm' => ['Partnerprogramm', 'Werde Peptrium-Partner: eigener Link, eigener Rabattcode und Provision für jede Bestellung über deine Empfehlung.'],
        'agb' => ['AGB', 'Allgemeine Geschäftsbedingungen von Peptrium.'],
        'datenschutz' => ['Datenschutzerklärung', 'Datenschutzerklärung von Peptrium.'],
        'rueckgabe-widerruf' => ['Rückgabe & Widerruf', 'Informationen zu Rückgabe und Widerruf bei Peptrium.'],
        'cookie-richtlinie' => ['Cookie-Richtlinie', 'Welche Cookies Peptrium verwendet und wie du deine Einwilligung änderst.'],
    ];
}

function sp_seo_front() {
    return ['Peptide kaufen in Forschungsqualität', 'Peptrium – Forschungspeptide wie Retatrutide, GHK-Cu und Mots-C: HPLC-geprüft, diskret verpackt, Versand aus Deutschland in 2 Werktagen, gratis ab 100 €.'];
}

function sp_seo_price_from($p) {
    $price = $p->is_type('variable') ? $p->get_variation_price('min', true) : wc_get_price_to_display($p);
    return $price ? number_format((float) $price, 2, ',', '.') : '';
}

/** Zubehoer = Kategorie 20 */
function sp_seo_is_accessory($p) {
    return in_array(20, $p->get_category_ids(), true);
}

/** [Titel, Beschreibung] fuer die aktuelle Seite oder null. */
function sp_seo_current() {
    static $cur = false;
    if ($cur !== false) {
        return $cur;
    }
    $cur = null;
    if (is_front_page()) {
        $cur = sp_seo_front();
    } elseif (function_exists('is_product') && is_product()) {
        $p = wc_get_product(get_queried_object_id());
        if ($p) {
            $n = $p->get_name();
            $pr = sp_seo_price_from($p);
            $ab = $p->is_type('variable') ? 'ab ' : '';
            if (sp_seo_is_accessory($p)) {
                $cur = [$n . ' kaufen', $n . ' für die Laborpraxis – steril und sofort lieferbar. ' . ($pr ? $ab . $pr . ' €, ' : '') . 'Versand aus Deutschland in 2 Werktagen, gratis ab 100 €.'];
            } elseif (stripos($n, 'Pen') === 0 || strpos($p->get_slug(), 'peptrium-pen') === 0) {
                $cur = [$n . ' kaufen – vorgefüllter Peptid-Pen', $n . ': fertig gemischt, exakte Dosierung per Klick, kein Anmischen. ' . ($pr ? ucfirst($ab) . $pr . ' €. ' : '') . 'Versand aus Deutschland in 2 Werktagen. Nur für Laborforschung.'];
            } else {
                $pre = function_exists('sp_preorder_product_ids') && in_array($p->get_id(), sp_preorder_product_ids(), true);
                $cur = [$n . ' kaufen – Forschungspeptid', $n . ' in Forschungsqualität kaufen: HPLC-geprüft, ≥ 99 % Reinheit' . ($pr ? ', ' . $ab . $pr . ' €' : '') . '. ' . ($pre ? 'Jetzt vorbestellen – nur für Laborforschung.' : 'Versand aus Deutschland in 2 Werktagen, gratis ab 100 €.')];
            }
        }
    } elseif (function_exists('is_product_category') && is_product_category()) {
        $t = get_queried_object();
        $name = html_entity_decode($t->name);
        $d = function_exists('sp_cpv_desc') ? wp_strip_all_tags(sp_cpv_desc($t->slug)) : '';
        if ($t->slug === 'zubehoer') {
            $cur = ['Zubehör – Bac Water, Spritzen & Pen-Nadeln', sp_seo_pages()['zubehoer'][1]];
        } else {
            $cur = [$name . ' – Forschungspeptide kaufen', trim(($d ? $d . ' ' : '') . 'HPLC-geprüft, Versand aus Deutschland in 2 Werktagen.')];
        }
    } elseif (is_page()) {
        $post = get_queried_object();
        $map = sp_seo_pages();
        if (isset($map[$post->post_name]) && !$post->post_parent) {
            $cur = $map[$post->post_name];
        } elseif ($post->post_parent && get_post_field('post_name', $post->post_parent) === 'peptid-lexikon') {
            $n = $post->post_title;
            $cur = [$n . ' – Peptid-Lexikon', $n . ' im Peptid-Lexikon: Herkunft, Wirkmechanismus und aktueller Forschungsstand verständlich zusammengefasst.'];
        }
    }
    if ($cur && mb_strlen($cur[1]) > 160) {
        $cut = mb_substr($cur[1], 0, 158);
        $cur[1] = rtrim(mb_substr($cut, 0, (int) mb_strrpos($cut, ' ')), " ,.–:") . ' …';
    }
    return $cur;
}

add_filter('document_title_parts', function ($parts) {
    if (is_admin() || is_feed()) {
        return $parts;
    }
    $c = sp_seo_current();
    if ($c) {
        $parts = ['title' => $c[0], 'site' => SP_SEO_SITE];
    }
    return $parts;
}, 20);

add_filter('document_title_separator', function () {
    return '|';
});

/** Astra-Seitentitel "Startseite" (versteckte zweite H1) auf der Startseite nicht ausgeben. */
add_filter('astra_the_title_enabled', function ($on) {
    return is_front_page() ? false : $on;
});

function sp_seo_image() {
    if (function_exists('is_product') && is_product()) {
        $id = get_post_thumbnail_id(get_queried_object_id());
        if ($id) {
            $src = wp_get_attachment_image_src($id, 'large');
            if ($src) {
                return [$src[0], (int) $src[1], (int) $src[2]];
            }
        }
    }
    $id = (int) get_option('sp_seo_og_default');
    $src = $id ? wp_get_attachment_image_src($id, 'full') : false;
    return $src ? [$src[0], (int) $src[1], (int) $src[2]] : null;
}

function sp_seo_url() {
    if (is_front_page()) {
        return home_url('/');
    }
    if (is_singular()) {
        return get_permalink(get_queried_object_id());
    }
    if (is_tax() || is_category() || is_tag()) {
        $l = get_term_link(get_queried_object());
        return is_wp_error($l) ? '' : $l;
    }
    return '';
}

add_action('wp_head', function () {
    if (is_admin() || is_feed() || is_404() || is_search()) {
        return;
    }
    $c = sp_seo_current();
    $url = sp_seo_url();
    $title = $c ? $c[0] . ' | ' . SP_SEO_SITE : wp_get_document_title();
    $desc = $c ? $c[1] : '';
    $out = "\n<!-- sp-seo -->\n";
    if ($desc) {
        $out .= '<meta name="description" content="' . esc_attr($desc) . '">' . "\n";
    }
    // WP gibt canonical nur fuer Einzelseiten aus -> Kategorien hier (ohne Seiten-/Filterparameter)
    if ((function_exists('is_product_category') && is_product_category()) && $url) {
        $paged = (int) get_query_var('paged');
        $out .= '<link rel="canonical" href="' . esc_url($paged > 1 ? trailingslashit($url) . 'page/' . $paged . '/' : $url) . '">' . "\n";
    }
    $is_prod = function_exists('is_product') && is_product();
    $og = [
        'og:locale' => 'de_DE',
        'og:site_name' => SP_SEO_SITE,
        'og:type' => $is_prod ? 'product' : 'website',
        'og:title' => $title,
        'og:description' => $desc,
        'og:url' => $url,
    ];
    $img = sp_seo_image();
    if ($img) {
        $og['og:image'] = $img[0];
        $og['og:image:width'] = $img[1];
        $og['og:image:height'] = $img[2];
    }
    foreach ($og as $k => $v) {
        if ($v !== '' && $v !== null) {
            $out .= '<meta property="' . $k . '" content="' . esc_attr($v) . '">' . "\n";
        }
    }
    if ($is_prod && ($p = wc_get_product(get_queried_object_id()))) {
        $price = $p->is_type('variable') ? $p->get_variation_price('min', true) : wc_get_price_to_display($p);
        if ($price) {
            $out .= '<meta property="product:price:amount" content="' . esc_attr(number_format((float) $price, 2, '.', '')) . '">' . "\n";
            $out .= '<meta property="product:price:currency" content="EUR">' . "\n";
        }
    }
    $out .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo $out;

    foreach (sp_seo_jsonld() as $ld) {
        echo '<script type="application/ld+json">' . wp_json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }
}, 2);

function sp_seo_jsonld() {
    $home = home_url('/');
    $logo_id = (int) get_option('site_icon');
    $logo = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
    $org = ['@type' => 'Organization', '@id' => $home . '#organization', 'name' => SP_SEO_SITE, 'url' => $home, 'email' => 'info@peptrium.com', 'sameAs' => ['https://t.me/peptrium']];
    if ($logo) {
        $org['logo'] = ['@type' => 'ImageObject', 'url' => $logo, 'width' => 512, 'height' => 512];
    }
    $out = [];
    if (is_front_page()) {
        $out[] = ['@context' => 'https://schema.org', '@graph' => [
            $org,
            ['@type' => 'WebSite', '@id' => $home . '#website', 'name' => SP_SEO_SITE, 'url' => $home, 'inLanguage' => 'de-DE', 'publisher' => ['@id' => $home . '#organization']],
        ]];
        return $out;
    }
    if (!(function_exists('is_product') && is_product())) {
        return $out;
    }
    $p = wc_get_product(get_queried_object_id());
    if (!$p || $p->get_catalog_visibility() === 'hidden') {
        return $out;
    }
    $c = sp_seo_current();
    $url = get_permalink($p->get_id());
    $img = sp_seo_image();
    $pre = function_exists('sp_preorder_product_ids') && in_array($p->get_id(), sp_preorder_product_ids(), true);
    $avail = $pre ? 'https://schema.org/PreOrder' : ($p->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock');
    $ship = [
        '@type' => 'OfferShippingDetails',
        'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => 4.90, 'currency' => 'EUR'],
        'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'DE'],
        'deliveryTime' => [
            '@type' => 'ShippingDeliveryTime',
            'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'],
            'transitTime' => ['@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 2, 'unitCode' => 'DAY'],
        ],
    ];
    if ($p->is_type('variable')) {
        $prices = $p->get_variation_prices(true);
        $vals = array_map('floatval', array_values($prices['price'] ?? []));
        if (!$vals) {
            return $out;
        }
        $offers = ['@type' => 'AggregateOffer', 'priceCurrency' => 'EUR', 'lowPrice' => number_format(min($vals), 2, '.', ''), 'highPrice' => number_format(max($vals), 2, '.', ''), 'offerCount' => count($vals), 'availability' => $avail, 'url' => $url, 'seller' => ['@id' => $home . '#organization']];
    } else {
        $offers = ['@type' => 'Offer', 'price' => number_format((float) wc_get_price_to_display($p), 2, '.', ''), 'priceCurrency' => 'EUR', 'availability' => $avail, 'itemCondition' => 'https://schema.org/NewCondition', 'url' => $url, 'seller' => ['@id' => $home . '#organization'], 'shippingDetails' => $ship];
    }
    $prod = ['@type' => 'Product', '@id' => $url . '#product', 'name' => $p->get_name(), 'url' => $url, 'description' => $c ? $c[1] : wp_strip_all_tags($p->get_short_description()), 'brand' => ['@type' => 'Brand', 'name' => SP_SEO_SITE], 'offers' => $offers];
    if ($img) {
        $prod['image'] = $img[0];
    }
    if ($p->get_sku()) {
        $prod['sku'] = $p->get_sku();
    }
    $crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Startseite', 'item' => $home]];
    $cats = wc_get_product_terms($p->get_id(), 'product_cat', ['orderby' => 'term_order']);
    if ($cats) {
        $l = get_term_link($cats[0]);
        if (!is_wp_error($l)) {
            $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => html_entity_decode($cats[0]->name), 'item' => $l];
        }
    }
    $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $p->get_name(), 'item' => $url];
    $out[] = ['@context' => 'https://schema.org', '@graph' => [$prod, ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs]]];
    return $out;
}

/** Versteckte Produkte (Guthaben 729, Injektionskit 745, Willkommenspaket 817) nicht in Google. */
add_filter('wp_robots', function ($robots) {
    if (function_exists('is_product') && is_product()) {
        $p = wc_get_product(get_queried_object_id());
        if ($p && $p->get_catalog_visibility() === 'hidden') {
            $robots['noindex'] = true;
            $robots['follow'] = true;
            unset($robots['max-image-preview']);
        }
    }
    return $robots;
});

/** Sitemap: versteckte Produkte (noindex) und /shop/ (301 -> /alle-produkte/) nicht melden. */
add_filter('wp_sitemaps_posts_query_args', function ($args, $post_type) {
    if ($post_type === 'product') {
        $hidden = get_terms(['taxonomy' => 'product_visibility', 'slug' => ['exclude-from-catalog', 'exclude-from-search'], 'fields' => 'ids', 'hide_empty' => false]);
        if ($hidden && !is_wp_error($hidden)) {
            $args['tax_query'] = [['taxonomy' => 'product_visibility', 'field' => 'term_id', 'terms' => $hidden, 'operator' => 'NOT IN']];
        }
    }
    if ($post_type === 'page' && function_exists('wc_get_page_id')) {
        $args['post__not_in'] = array_merge($args['post__not_in'] ?? [], [wc_get_page_id('shop')]);
    }
    return $args;
}, 20, 2);

/**
 * Menue/Startseite verlinken im HTML noch die alten Ziel-Seiten (301 -> Kategorie, sp_ds_goal_map() in sp-ds.php);
 * das Menue-JS tauscht sie erst im Browser. Fuer Google die Links schon serverseitig direkt auf die Kategorien
 * zeigen lassen (wichtig fuer Sitelinks/interne Verlinkung). Das JS erkennt die Gruppe am Text, nicht am Link.
 */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || !function_exists('sp_ds_goal_map') || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    ob_start(function ($html) {
        if (!is_string($html) || stripos($html, '<html') === false) {
            return $html;
        }
        static $repl = null;
        if ($repl === null) {
            $repl = [];
            $home = untrailingslashit(home_url());
            foreach (sp_ds_goal_map() as $goal => $cat) {
                $t = get_term_by('slug', $cat, 'product_cat');
                $l = $t ? get_term_link($t) : '';
                if ($l && !is_wp_error($l)) {
                    $repl['href="' . $home . '/' . $goal . '/"'] = 'href="' . esc_url($l) . '"';
                    $repl['href="/' . $goal . '/"'] = 'href="' . esc_url($l) . '"';
                }
            }
        }
        return strtr($html, $repl);
    });
}, 2);
