<?php
/**
 * Plugin Name: SP Preise & Versandgrenze in Elementor-Texten
 * Description: Ersetzt beim Ausliefern fest eingetragene Bac-Water-Preise und die alte Gratisversand-Grenze in Elementor-Seiten/-Vorlagen – ohne gespeicherte Elementor-Daten anzufassen.
 *
 * (2026-10-16, Nutzer: Gratisversand 100 → 150 €, Bac Water 6,90 → 9,90 €)
 * Die Werte kommen live aus WooCommerce (Preis von Produkt 74, Mindestbestellwert der Versandzone DE),
 * spaetere Aenderungen im Shop-Backend greifen also automatisch auch hier.
 * Laeuft nur auf Produkt-/Inhaltsseiten – nie auf Warenkorb, Kasse, Konto oder Bestellseiten
 * (alte Bestellungen behalten ihren damaligen Preis). Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

// Werte werden in template_redirect VOR dem Puffern gelesen: im ob-Callback darf nichts ob_start() aufrufen
// (WooCommerce-Versandeinstellungen tun das → Fatal "Cannot use output buffering in output buffering display handlers").
$GLOBALS['sp_pt_vals'] = null;

function sp_pt_bac_price() {
    $p = function_exists('wc_get_product') ? wc_get_product(74) : null;
    return $p ? (float) $p->get_price() : 0.0;
}

function sp_pt_threshold() {
    if (function_exists('sp_fsh_threshold')) {
        $t = (float) sp_fsh_threshold();
        if ($t > 0) {
            return $t;
        }
    }
    return 150.0;
}

/** Ersetzt $from durch $to, sofern es hoechstens $max Zeichen hinter jedem $anchor vorkommt (ohne Regex). */
function sp_pt_replace_after($html, $anchor, $from, $to, $max) {
    $off = 0;
    while (($a = strpos($html, $anchor, $off)) !== false) {
        $p = strpos($html, $from, $a);
        if ($p !== false && $p - $a <= $max) {
            $html = substr_replace($html, $to, $p, strlen($from));
        }
        $off = $a + strlen($anchor);
    }
    return $html;
}

function sp_pt_filter_html($html) {
    if (!is_string($html) || stripos($html, '<html') === false) {
        return $html;
    }
    $orig = $html;
    try {
        $out = sp_pt_apply($html);
    } catch (\Throwable $e) {
        return $orig;
    }
    // Sicherheitsnetz: nie eine leere/kaputte Seite ausliefern
    return (is_string($out) && strlen($out) > strlen($orig) * 0.9) ? $out : $orig;
}

function sp_pt_apply($html) {
    $v = $GLOBALS['sp_pt_vals'];
    if (!is_array($v)) {
        return $html;
    }
    $t = $v['t'];
    $ti = (string) (int) round($t);
    // Gratisversand-Grenze (alte Texte "ab 100 €" in Elementor-Inhalten)
    if ($t != 100.0) {
        $html = str_replace(
            ['Kostenloser Versand ab 100€', 'Versandkostenfrei ab 100€', 'Gratisversand ab 100€', 'ab 100 € Bestellwert', 'var freeShipThreshold = 100;'],
            ['Kostenloser Versand ab ' . $ti . '€', 'Versandkostenfrei ab ' . $ti . '€', 'Gratisversand ab ' . $ti . '€', 'ab ' . $ti . ' € Bestellwert', 'var freeShipThreshold = ' . $ti . ';'],
            $html
        );
    }
    // Bac Water (Produkt 74)
    $bp = $v['bp'];
    if ($bp > 0 && abs($bp - 6.90) > 0.001) {
        $de = number_format($bp, 2, ',', '.');
        $en = number_format($bp, 2, '.', '');
        // Zubehoer-Zeile in der Kaufbox
        $html = str_replace('data-field="sp_addon_bacwater" data-price="6.90"', 'data-field="sp_addon_bacwater" data-price="' . $en . '"', $html);
        $html = sp_pt_replace_after($html, 'data-field="sp_addon_bacwater"', 'rx-addon-price">+6,90', 'rx-addon-price">+' . $de, 2500);
        // "Passt dazu"-Karte mit Link zu Bac Water: nur innerhalb des jeweiligen <a>…</a>
        $off = 0;
        while (($p = strpos($html, '/produkt/bac-water/"', $off)) !== false) {
            $e = strpos($html, '</a>', $p);
            if ($e === false || $e - $p > 3000) {
                $off = $p + 10;
                continue;
            }
            $seg = substr($html, $p, $e - $p);
            $new = str_replace(['>6,90&nbsp;&euro;', '>ab 6,90&nbsp;&euro;', '>6,90 €', '>6,90&nbsp;€'], ['>' . $de . '&nbsp;&euro;', '>ab ' . $de . '&nbsp;&euro;', '>' . $de . ' €', '>' . $de . '&nbsp;€'], $seg);
            if ($new !== $seg) {
                $html = substr_replace($html, $new, $p, strlen($seg));
            }
            $off = $p + strlen($new) + 4;
        }
        $html = str_replace('Bac Water 6,90', 'Bac Water ' . $de, $html);
        // Bac-Water-Produktseite selbst
        if ($v['bacpage']) {
            $html = str_replace(['id="rx-price">6,90&nbsp;&euro;', 'var unitPrice = 6.90;'], ['id="rx-price">' . $de . '&nbsp;&euro;', 'var unitPrice = ' . $en . ';'], $html);
        }
        // Lexikon Bac Water, /zubehoer/ (529), /alle-produkte/ (325)
        $html = str_replace('lexd-hero-price">ab 6,90&nbsp;&euro;', 'lexd-hero-price">ab ' . $de . '&nbsp;&euro;', $html);
        if ($v['coll']) {
            $html = str_replace(['elementor-size-default">6,90 &euro;<', 'elementor-size-default">6,90 €<'], ['elementor-size-default">' . $de . ' &euro;<', 'elementor-size-default">' . $de . ' €<'], $html);
        }
    }
    return $html;
}

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || isset($_GET['elementor-preview']) || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout()) || (function_exists('is_account_page') && is_account_page())
        || (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url())) {
        return;
    }
    $GLOBALS['sp_pt_vals'] = [
        't' => sp_pt_threshold(),
        'bp' => sp_pt_bac_price(),
        'bacpage' => function_exists('is_product') && is_product() && get_queried_object_id() === 74,
        'coll' => is_page([529, 325]),
    ];
    ob_start('sp_pt_filter_html');
}, 4);
