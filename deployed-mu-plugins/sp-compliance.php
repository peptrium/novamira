<?php
/**
 * Plugin Name: SP Compliance-Texte
 * Description: Entschaerft beim Ausliefern Aussagen, die nicht zum Forschungszweck passen oder (noch) nicht stimmen – ohne gespeicherte Elementor-Daten anzufassen.
 *
 * (2026-10-11, Nutzer: "ja mach")
 *  1. Retatrutide: Block "Wirkung & Forschung" mit "Abnehm-Peptide"/"24 % Koerpergewichtsreduktion" entfernt.
 *  2. Peptrium-Pens: Dosiertabelle (Einheiten → mg), "Gaengige Einstellungen", Zeilen "Maximale Dosen" und
 *     "Verabreichung: subkutan" entfernt; Titel "Technische Referenz – Dosiszaehler" → "Technische Daten".
 *  3. Analysezertifikate (COA): gibt es noch nicht → Aussagen neutral auf "HPLC-/LC-MS-geprueft", PDF-Kaesten
 *     (.rx-coa-box, Button war ohnehin deaktiviert) weg, FAQ "Was ist ein Analysezertifikat?" weg, Labor-Name weg.
 *     Sobald echte COAs vorliegen: Option sp_cmp_coa = 'show' (dann bleiben alle COA-Texte unveraendert).
 *  4. Menue: Gruppe "Nach Ziel suchen" (alte Zielseiten mit "Gewichtsmanagement", "Fettverbrennung", "Anti-Aging" …)
 *     schon serverseitig als "Forschungsbereiche" mit den Kategorien – vorher tauschte nur das JS in sp-ds.php sie aus,
 *     Google sah die alten Begriffe.
 *  5. E-Mails: dieselben COA-Saetze in Bestell-/Newsletter-Mails neutral.
 * Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_cmp_coa_hidden() {
    return get_option('sp_cmp_coa', 'hide') !== 'show';
}

/** Ersetzungen (rohes HTML, inkl. Entities). Laengste Treffer zuerst (strtr). */
function sp_cmp_coa_map() {
    return [
        // Produktseiten
        '<span>COA</span>' => '<span>Geprüft</span>',
        'Chargenspezifisches Analysezertifikat (COA)' => 'Chargenweise HPLC-geprüft',
        'mit chargenspezifischem Analysezertifikat (COA)' => 'chargenweise geprüft',
        'Analyse per HPLC &amp; LC-MS &middot; Chargenspezifisches COA' => 'Analyse per HPLC &amp; LC-MS &middot; jede Charge',
        ' und mit chargenspezifischem, öffentlich einsehbarem Analysezertifikat (COA) ausgeliefert' => '',
        'unabhängig per HPLC' => 'per HPLC',
        'Unabhängig laborgeprüft' => 'Laborgeprüft',
        'Zertifizierte Qualit&auml;t' => 'Geprüfte Qualit&auml;t',
        'Zertifizierte Qualität' => 'Geprüfte Qualität',
        '. Jede Charge wird steril filtriert und mit einsehbarem Analysezertifikat (COA) ausgeliefert.' => '. Jede Charge wird steril filtriert.',
        ', dokumentiert in einem chargengenauen Analysezertifikat (COA),' => '',
        // Startseite
        ' und mit einem Analysezertifikat (COA) bereitgestellt' => '',
        'Gute Qualität, Analysezertifikate waren vorhanden und einsehbar.' => 'Gute Qualität, sauber und neutral verpackt.',
        '„Hier ist das COA. Prüf es selbst.“' => '„Geprüft. Charge für Charge.“',
        'Öffentliches Analysezertifikat je Fläschchen' => 'HPLC- &amp; LC-MS-Prüfung jeder Charge',
        'mit vollständigen Spezifikationen, öffentlichen Analysezertifikaten und Chargenberichten.' => 'mit vollständigen Spezifikationen und chargenweiser Laborprüfung.',
        'Jede Charge HPLC-geprüft, mit Analysezertifikat –' => 'Jede Charge HPLC-geprüft –',
        'Analysezertifikat zu jeder Charge' => 'HPLC-Prüfung jeder Charge',
        'den Zertifikaten und dem schnellen Versand' => 'der Verpackung und dem schnellen Versand',
        // Retatrutide-Rechner
        '; geprüfte Ware mit chargenspezifischem Analysezertifikat (COA) gibt es auf der' => '; geprüfte Ware gibt es auf der',
        'Jede Charge mit Analysezertifikat (COA), HPLC- &amp; LC-MS-geprüft.' => 'Jede Charge HPLC- &amp; LC-MS-geprüft.',
        // Über uns
        '— jede Charge mit unabhängigem Analysezertifikat, HPLC- und LC-MS-geprüft.' => '— jede Charge HPLC- und LC-MS-geprüft.',
        'COA pro Charge' => 'Prüfung jeder Charge',
        '— unabhängiges Labor (Janoshik)' => '— per HPLC &amp; LC-MS',
        'chargengeprüft (COA)' => 'chargengeprüft',
        'Chargenspezifisches COA' => 'Chargenprüfung',
        'von einem unabhängigen Labor (Janoshik) — liegt jeder Lieferung bei.' => 'per HPLC und LC-MS — für jede Charge.',
        'Chargenprüfung (COA)' => 'Chargenprüfung',
        'Jede Charge mit COA, HPLC- und LC-MS-Daten — einsehbar statt versteckt.' => 'Jede Charge per HPLC und LC-MS geprüft.',
        'Wir sind ein Anbieter mit vollständigem Impressum, erreichbarem Support und unabhängig geprüfter Qualität. Jede Charge wird per HPLC und LC-MS analysiert und mit einem Analysezertifikat (COA) eines unabhängigen Labors dokumentiert.' => 'Wir sind ein Anbieter mit erreichbarem Support und geprüfter Qualität. Jede Charge wird per HPLC und LC-MS analysiert.',
        ' Das chargenspezifische COA liegt jeder Lieferung bei und belegt Reinheit und Identität.' => '',
        'Entdecke über 10 geprüfte Verbindungen — jede mit Analysezertifikat.' => 'Entdecke über 10 geprüfte Verbindungen.',
        // Forschungsnutzung / Partnerprogramm
        ' und über ein unabhängiges Analysezertifikat (COA) verifiziert. Details dazu findest du auf der jeweiligen Produktseite.' => '.',
        'HPLC-gepr&uuml;ft mit chargenspezifischem COA &ndash;' => 'HPLC-gepr&uuml;ft &ndash;',
    ];
}

function sp_cmp_mail_map() {
    return [
        'Jede Charge mit Analysezertifikat' => 'Jede Charge HPLC-geprüft',
        'jede Charge mit Analysezertifikat' => 'jede Charge HPLC-geprüft',
        ', ein chargenspezifisches COA liegt vor.' => '.',
        ', ein chargenspezifisches Analysezertifikat liegt vor.' => '.',
    ];
}

/** Entfernt ein Element (div/details/table/p) samt Inhalt ab Position $start (Beginn des Start-Tags). */
function sp_cmp_cut($html, $start, $tag) {
    $depth = 0;
    $re = '~<(/?)' . $tag . '\b[^>]*>~i';
    if (!preg_match_all($re, $html, $m, PREG_OFFSET_CAPTURE, $start)) {
        return $html;
    }
    foreach ($m[0] as $i => $hit) {
        $depth += $m[1][$i][0] === '/' ? -1 : 1;
        if ($depth === 0) {
            $end = $hit[1] + strlen($hit[0]);
            return substr($html, 0, $start) . substr($html, $end);
        }
    }
    return $html;
}

/** Alle Elemente entfernen, deren Start-Tag $open ist und (optional) deren Inhalt $needle enthaelt. */
function sp_cmp_remove_all($html, $open, $tag, $needle = null) {
    $from = 0;
    for ($guard = 0; $guard < 60; $guard++) {
        $p = strpos($html, $open, $from);
        if ($p === false) {
            break;
        }
        if ($needle !== null) {
            $probe = sp_cmp_cut($html, $p, $tag);
            $removed = substr($html, $p, strlen($html) - strlen($probe));
            if (strpos($removed, $needle) === false) {
                $from = $p + strlen($open);
                continue;
            }
            $html = $probe;
        } else {
            $html = sp_cmp_cut($html, $p, $tag);
        }
        $from = $p;
    }
    return $html;
}

/** Menue-Gruppe "Nach Ziel suchen" serverseitig wie das sp-ds-JS umbauen. */
function sp_cmp_menu($html) {
    $label = 'Nach Ziel suchen</span>';
    if (strpos($html, $label) === false || !function_exists('sp_ds_menu_cats')) {
        return $html;
    }
    $cats = sp_ds_menu_cats();
    if (!$cats) {
        return $html;
    }
    $links = '';
    foreach ($cats as $c) {
        $links .= '<a class="sp-ds-cat" href="' . esc_url($c['u']) . '"><i></i><span><b>' . esc_html($c['n']) . '</b><small>' . esc_html($c['e']) . '</small></span></a>';
    }
    $links .= '<a class="sp-ds-cat all" href="' . esc_url(home_url('/alle-produkte/')) . '"><i></i><span><b>Alle Produkte</b><small>Das komplette Sortiment</small></span></a>';
    $from = 0;
    for ($guard = 0; $guard < 4; $guard++) {
        $l = strpos($html, $label, $from);
        if ($l === false) {
            break;
        }
        $html = substr_replace($html, 'Forschungsbereiche</span>', $l, strlen($label));
        $s = strpos($html, '<div class="sp-mm-sub', $l);
        if ($s === false) {
            break;
        }
        $gt = strpos($html, '>', $s) + 1;
        $cut = sp_cmp_cut($html, $s, 'div');
        $len = strlen($html) - strlen($cut);
        $openTag = substr($html, $s, $gt - $s);
        $html = substr($html, 0, $s) . $openTag . $links . '</div>' . substr($html, $s + $len);
        $from = $s + 10;
    }
    return $html;
}

function sp_cmp_filter_html($html) {
    if (!is_string($html) || stripos($html, '<html') === false) {
        return $html;
    }
    // 4. Menue
    $html = sp_cmp_menu($html);

    if (function_exists('is_product') && is_product()) {
        // 1. Retatrutide "Abnehm-Peptide"-Block
        $html = sp_cmp_remove_all($html, '<div class="sp-reta-desc">', 'div', 'Abnehm-Peptide');
        // 2. Pens: Dosiertabelle
        if (strpos($html, 'sp-dose') !== false) {
            $html = sp_cmp_remove_all($html, '<p class="sp-dose-sub">', 'p');
            $html = sp_cmp_remove_all($html, '<table class="sp-dose-table">', 'table');
            $html = sp_cmp_remove_all($html, '<p class="sp-dose-common">', 'p');
            $html = str_replace('Technische Referenz &ndash; Dosisz&auml;hler', 'Technische Daten', $html);
        }
        $html = preg_replace('~<tr>\s*<td>(?:Maximale Dosen|Verabreichung)</td>.*?</tr>~s', '', $html);
    }

    // 3. COA
    if (sp_cmp_coa_hidden()) {
        $html = sp_cmp_remove_all($html, '<div class="rx-coa-box">', 'div');
        $html = sp_cmp_remove_all($html, '<details', 'details', 'Was ist ein Analysezertifikat');
        $html = strtr($html, sp_cmp_coa_map());
    }
    return $html;
}

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || isset($_GET['elementor-preview']) || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        return;
    }
    ob_start('sp_cmp_filter_html');
}, 3);

add_filter('wp_mail', function ($args) {
    if (sp_cmp_coa_hidden() && !empty($args['message']) && is_string($args['message'])) {
        $args['message'] = strtr($args['message'], sp_cmp_mail_map());
    }
    return $args;
}, 5);
