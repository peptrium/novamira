<?php
/**
 * Plugin Name: SP Redesign – Ladezeit (Bilder)
 * Description: Laesst ausgeblendete alte Elementor-Abschnitte weg und liefert grosse PNG/JPG-Uploads als WebP aus.
 *
 * Nur aktiv, wenn sp_redesign_on('katalog') (sp-00-redesign.php). Greift nicht in gespeicherte Daten ein:
 *  A) elementor/frontend/builder_content_data: alte, vom Redesign per CSS versteckte Abschnitte werden beim
 *     Rendern gar nicht erst ausgegeben (ihre Bilder wurden sonst trotzdem geladen, Startseite ~20 MB).
 *  B) Ausgabepuffer: jede Upload-URL (.png/.jpg) > 100 KB im HTML (auch JSON-escaped in Inline-Skripten) wird
 *     durch eine einmalig erzeugte WebP-Kopie ersetzt (uploads/sp-redesign/webp/, max. 1200 px breit).
 *     Zuordnung in Option sp_rd_webp_map (rel. Pfad => webp-Dateiname oder '' = nicht umwandeln).
 * Zuruecksetzen: Datei loeschen (WebP-Ordner + Option koennen bleiben).
 */
if (!defined('ABSPATH')) {
    exit;
}

/** Top-Level-Abschnitte pro Elementor-Dokument, die das Redesign ohnehin ausblendet. */
function sp_rd_drop_sections() {
    return [
        211 => ['a9d4d1b', 'midcta01', '3ca703b', '5c363d8', '045a884', 'a4ab146'], // Startseite (d60a05b + spsocial1 bleiben: JS liest die Texte)
        325 => ['59e11b5'], // Alle Produkte: Inhalt wird komplett von sp-category-preview.php gebaut
    ];
}

add_filter('elementor/frontend/builder_content_data', function ($data, $post_id) {
    if (is_admin() || !is_array($data) || !function_exists('sp_redesign_on') || !sp_redesign_on('katalog')) {
        return $data;
    }
    $map = sp_rd_drop_sections();
    if (empty($map[(int) $post_id])) {
        return $data;
    }
    if (isset($_GET['elementor-preview'])) {
        return $data;
    }
    $drop = $map[(int) $post_id];
    return array_values(array_filter($data, function ($el) use ($drop) {
        return !(is_array($el) && isset($el['id']) && in_array($el['id'], $drop, true));
    }));
}, 10, 2);

function sp_rd_webp_dir() {
    $u = wp_get_upload_dir();
    return ['dir' => $u['basedir'] . '/sp-redesign/webp', 'url' => $u['baseurl'] . '/sp-redesign/webp', 'basedir' => $u['basedir'], 'baseurl' => $u['baseurl']];
}

/**
 * WebP fuer einen Upload-Pfad (relativ zu uploads). Gibt Dateinamen, '' (nicht umwandeln) oder null (noch offen) zurueck.
 * $allow_gen: darf jetzt erzeugt werden.
 */
function sp_rd_webp_for($rel, &$map, $allow_gen) {
    if (array_key_exists($rel, $map)) {
        return $map[$rel];
    }
    if (!$allow_gen) {
        return null;
    }
    $d = sp_rd_webp_dir();
    $src = $d['basedir'] . '/' . $rel;
    if (strpos($rel, '..') !== false || !is_file($src) || filesize($src) < 100 * 1024) {
        $map[$rel] = '';
        return '';
    }
    $name = md5($rel) . '.webp';
    $dst = $d['dir'] . '/' . $name;
    if (!is_file($dst)) {
        wp_mkdir_p($d['dir']);
        $ed = wp_get_image_editor($src);
        if (is_wp_error($ed)) {
            $map[$rel] = '';
            return '';
        }
        $sz = $ed->get_size();
        if (!empty($sz['width']) && $sz['width'] > 1200) {
            $ed->resize(1200, null, false);
        }
        $ed->set_quality(80);
        $res = $ed->save($dst, 'image/webp');
        if (is_wp_error($res) || !is_file($dst) || filesize($dst) >= filesize($src)) {
            if (is_file($dst)) {
                @unlink($dst);
            }
            $map[$rel] = '';
            return '';
        }
    }
    $map[$rel] = $name;
    return $name;
}

function sp_rd_rewrite_html($html) {
    if (!is_string($html) || strlen($html) < 200 || stripos($html, '<html') === false) {
        return $html;
    }
    $d = sp_rd_webp_dir();
    $base = $d['baseurl'];
    $base_esc = str_replace('/', '\/', $base);
    $re = '~(' . preg_quote($base, '~') . '|' . preg_quote($base_esc, '~') . ')((?:\\\\?/[A-Za-z0-9._%-]+)+\.(?:png|jpe?g))(?![A-Za-z0-9])~i';
    if (!preg_match_all($re, $html, $m, PREG_SET_ORDER)) {
        return $html;
    }
    $map = get_option('sp_rd_webp_map', []);
    if (!is_array($map)) {
        $map = [];
    }
    $before = count($map);
    $gen = 0;
    $repl = [];
    foreach ($m as $hit) {
        if (isset($repl[$hit[0]])) {
            continue;
        }
        $rel = ltrim(str_replace('\/', '/', $hit[2]), '/');
        if (strpos($rel, 'sp-redesign/webp/') === 0) {
            continue;
        }
        $known = array_key_exists($rel, $map);
        $name = sp_rd_webp_for($rel, $map, $gen < 3);
        if (!$known && $name !== null) {
            $gen++;
        }
        if ($name) {
            $esc = strpos($hit[1], '\/') !== false;
            $url = $d['url'] . '/' . $name;
            $repl[$hit[0]] = $esc ? str_replace('/', '\/', $url) : $url;
        }
    }
    if (count($map) !== $before) {
        update_option('sp_rd_webp_map', $map, false);
    }
    return $repl ? strtr($html, $repl) : $html;
}

add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || (defined('REST_REQUEST') && REST_REQUEST) || isset($_GET['elementor-preview'])) {
        return;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET' || !function_exists('sp_redesign_on') || !sp_redesign_on('katalog')) {
        return;
    }
    ob_start('sp_rd_rewrite_html');
}, 1);
