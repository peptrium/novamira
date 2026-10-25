<?php
/**
 * Plugin Name: SP Partner-Zuordnung über die E-Mail
 * Description: (2026-10-25) Wer sich für den Newsletter anmeldet, während ein Partner-Cookie (slicewp_aff) gesetzt ist,
 *   bleibt diesem Partner 90 Tage lang über seine E-Mail-Adresse zugeordnet. Bestellt die Adresse später ohne
 *   Partner-Link/-Code (z. B. über den Mail-Link auf einem anderen Gerät), bekommt der Partner trotzdem seine Provision –
 *   über SliceWPs eigenen Filter 'slicewp_referrer_affiliate_id_woo', d. h. normale Provision mit richtigem Satz,
 *   Halloween-Bonus und Partner-Mail. Ein vorhandener Link/Code hat immer Vorrang.
 *   Speicher: Option sp_aff_email [sha256(e-mail) => ['a' => Partner-ID, 't' => Zeit]] – keine Klartext-Adressen.
 */
if (!defined('ABSPATH')) exit;

define('SP_AFE_DAYS', 90);

function sp_afe_key($email) { return hash('sha256', strtolower(trim((string) $email))); }

/** Partner-ID aus dem SliceWP-Cookie (nur gültige, aktive Partner). */
function sp_afe_cookie_affiliate() {
    $c = 'slicewp_aff';
    $a = isset($_COOKIE[$c]) ? absint($_COOKIE[$c]) : 0;
    if (!$a || !function_exists('slicewp_is_affiliate_valid') || !slicewp_is_affiliate_valid($a)) return 0;
    return $a;
}

/** Zuordnung merken (bestehende, noch gültige Zuordnung bleibt – erster Partner zählt). */
function sp_afe_remember($email, $aff, $src = '') {
    if (!is_email($email) || !$aff) return;
    $m = get_option('sp_aff_email', []);
    if (!is_array($m)) $m = [];
    $k = sp_afe_key($email);
    if (isset($m[$k]) && time() - (int) $m[$k]['t'] < SP_AFE_DAYS * DAY_IN_SECONDS) return;
    $m[$k] = ['a' => (int) $aff, 't' => time(), 's' => $src];
    foreach ($m as $kk => $v) { // Aufräumen
        if (time() - (int) ($v['t'] ?? 0) > SP_AFE_DAYS * DAY_IN_SECONDS) unset($m[$kk]);
    }
    update_option('sp_aff_email', $m, false);
}

/** Newsletter-Anmeldung (Footer, Startseite, Popup) – läuft vor sp-newsletter.php. */
function sp_afe_on_nl_ajax() {
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (!empty($_POST['website'])) return; // Honeypot
    $aff = sp_afe_cookie_affiliate();
    if ($aff && $email) sp_afe_remember($email, $aff, sanitize_key($_POST['src'] ?? 'footer'));
}
add_action('wp_ajax_sp_nl_subscribe', 'sp_afe_on_nl_ajax', 1);
add_action('wp_ajax_nopriv_sp_nl_subscribe', 'sp_afe_on_nl_ajax', 1);

/** Partner für eine Bestellung über die E-Mail nachschlagen. */
function sp_afe_lookup($email) {
    $m = get_option('sp_aff_email', []);
    $k = sp_afe_key($email);
    if (empty($m[$k])) return 0;
    if (time() - (int) $m[$k]['t'] > SP_AFE_DAYS * DAY_IN_SECONDS) return 0;
    return (int) $m[$k]['a'];
}

/** Nach sp-affiliate-coupons (Prio 10): nur wenn weder Cookie noch Partner-Code einen Partner liefern. */
add_filter('slicewp_referrer_affiliate_id_woo', function ($affiliate_id, $order_id) {
    if (!empty($affiliate_id)) return $affiliate_id;
    $order = wc_get_order($order_id);
    if (!$order || $order->get_meta('_sp_is_test')) return $affiliate_id;
    $email = $order->get_billing_email();
    $aff = sp_afe_lookup($email);
    if (!$aff || !function_exists('slicewp_is_affiliate_valid') || !slicewp_is_affiliate_valid($aff)) return $affiliate_id;
    // Keine Eigen-Provision
    if (function_exists('slicewp_get_affiliate')) {
        $a = slicewp_get_affiliate($aff);
        $u = $a ? get_userdata((int) $a->get('user_id')) : null;
        if ($u && strtolower($u->user_email) === strtolower($email)) return $affiliate_id;
    }
    $name = function_exists('sp_afp_affiliate_name') ? sp_afp_affiliate_name($aff) : ('#' . $aff);
    $order->add_order_note('Partner ' . $name . ' über die Newsletter-Anmeldung zugeordnet (gleiche E-Mail, kein Partner-Link/-Code in dieser Bestellung).');
    $order->update_meta_data('_sp_afe', $aff);
    $order->save();
    return $aff;
}, 11, 2);
