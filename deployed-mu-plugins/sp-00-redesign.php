<?php
/**
 * Plugin Name: SP Redesign-Schalter
 * Description: Zentraler Schalter fuer das Shop-Redesign 2026-10 (laedt als erste sp-Datei).
 *
 * Jede Redesign-Datei fragt sp_redesign_on('<bereich>'): aktiv, wenn der Vorschau-Link/-Cookie
 * gesetzt ist (sp_hpv_token_ok() in sp-home-preview.php) ODER der Bereich live geschaltet ist.
 * Bereiche (Option sp_redesign_live = ['basis'=>1,'katalog'=>1,'kauf'=>1]):
 *   basis   - Menue/Footer/404, Inhalts- und Abo-Seiten, Kundenkonto, Partnerbereich, Danke-Seite,
 *             Weiterleitungen der alten Ziel-Seiten (live: 301, Vorschau: 302)
 *   katalog - Startseite, Kategorien/Alle Produkte, Produktseiten
 *   kauf    - Warenkorb-Drawer, Kasse
 * Schalten: Peptrium Dashboard -> Redesign (oder Option direkt). Zurueck = Haken raus.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_redesign_areas() {
    return [
        'basis' => 'Basis & Inhalte – Menü, Footer, 404, Inhalts- und Abo-Seiten, Kundenkonto, Partnerbereich, Danke-Seite, Weiterleitungen',
        'katalog' => 'Katalog – Startseite, Kategorien, Alle Produkte, Produktseiten',
        'kauf' => 'Kauf – Warenkorb, Kasse',
    ];
}

function sp_redesign_live($area) {
    $o = get_option('sp_redesign_live', []);
    return is_array($o) && !empty($o[$area]);
}

/** Reiner Vorschau-Modus (geheimer Link/Cookie). */
function sp_redesign_preview() {
    return function_exists('sp_hpv_token_ok') && sp_hpv_token_ok();
}

function sp_redesign_on($area) {
    return sp_redesign_preview() || sp_redesign_live($area);
}

/** Nur Entwurf (Vorschau, Bereich noch nicht live): ENTWURF-Pille + noindex. */
function sp_redesign_is_draft($area) {
    return sp_redesign_preview() && !sp_redesign_live($area);
}

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Redesign', 'Redesign', 'manage_woocommerce', 'sp-redesign', 'sp_redesign_admin_page');
}, 99);

function sp_redesign_admin_page() {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }
    if (isset($_POST['sp_redesign_save']) && check_admin_referer('sp_redesign_save')) {
        $new = [];
        foreach (array_keys(sp_redesign_areas()) as $a) {
            if (!empty($_POST['sp_rd'][$a])) {
                $new[$a] = 1;
            }
        }
        $old = get_option('sp_redesign_live', []);
        update_option('sp_redesign_live', $new, true);
        $log = get_option('sp_redesign_log', []);
        $log[] = ['t' => current_time('mysql'), 'from' => $old, 'to' => $new, 'user' => get_current_user_id()];
        update_option('sp_redesign_log', array_slice($log, -30), false);
        echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
    }
    echo '<div class="wrap"><h1>Redesign live schalten</h1><p>Haken = für alle Besucher sichtbar. Haken raus = sofort wieder das alte Design. Die Vorschau über den geheimen Link funktioniert immer.</p><form method="post">';
    wp_nonce_field('sp_redesign_save');
    echo '<table class="form-table">';
    foreach (sp_redesign_areas() as $a => $label) {
        printf('<tr><th>%s</th><td><label><input type="checkbox" name="sp_rd[%s]" value="1" %s> live</label></td></tr>', esc_html($label), esc_attr($a), checked(sp_redesign_live($a), true, false));
    }
    echo '</table><p><button class="button button-primary" name="sp_redesign_save" value="1">Speichern</button></p></form></div>';
}
