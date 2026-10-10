<?php
/**
 * Plugin Name: SP Versand-Texte
 * Description: Lieferzeit auf der Seite "Versand & Lieferzeit" (409) korrigiert: 2 Werktage statt 2-5 (2026-10-10).
 *
 * Seite ist in Elementor gebaut (Daten nicht direkt beschreiben) -> Ausgabefilter.
 * Ergaenzt ausserdem DHL, Sendungsnummer per E-Mail und Gratisversand ab 100 EUR.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_vt_filter($html) {
    if (!is_page(409) || strpos($html, 'sp-vt-done') !== false) {
        return $html;
    }
    $html = preg_replace('/2\s*(?:–|&#8211;|&ndash;|-)\s*5\s+Werktagen/u', '2 Werktagen', $html);
    $add = '<p class="sp-vt-done">Wir versenden mit <strong>DHL</strong>. Sobald dein Paket unterwegs ist, bekommst du die <strong>Sendungsnummer per E-Mail</strong>. Ab <strong>100 € Bestellwert</strong> ist der Versand innerhalb Deutschlands kostenlos.</p>';
    $html = preg_replace('/(Versandbestätigung\.)(\s*<\/p>)/u', '$1$2' . $add, $html, 1, $n);
    if (!$n) {
        $html .= '<span class="sp-vt-done" hidden></span>';
    }
    return $html;
}
add_filter('elementor/frontend/the_content', 'sp_vt_filter', 98);
add_filter('the_content', 'sp_vt_filter', 98);
