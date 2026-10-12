<?php
/**
 * Plugin Name: SP Gutschein – E-Mail-Bindung erst in der Kasse pruefen
 * Description: Newsletter-Codes (HALLO-…, COMEBACK-…) sind an die E-Mail des Abonnenten gebunden. Im Warenkorb-Drawer
 *              kennt WooCommerce bei Gaesten noch keine E-Mail → Code wurde mit "Bitte gib an der Kasse eine gueltige
 *              E-Mail-Adresse ein …" abgelehnt (Wochenbericht 10.10., Kundin hat danach nicht bestellt).
 *
 * Loesung: Solange keine E-Mail bekannt ist (Gast im Drawer), gilt die E-Mail-Beschraenkung dieser Codes als leer –
 * der Code wird angenommen. Sobald eine E-Mail da ist (Kasse, eingeloggt, Bestellung), greift die echte Beschraenkung
 * wieder; passt die E-Mail nicht, wird der Code mit einer verstaendlichen Meldung entfernt.
 * Nur der Getter im 'view'-Kontext wird gefiltert – im Admin (Kontext 'edit') bleibt die Beschraenkung sichtbar/gespeichert.
 * Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_ce_is_nl_code($coupon) {
    return $coupon instanceof WC_Coupon && preg_match('/^(hallo|comeback)-/i', (string) $coupon->get_code());
}

function sp_ce_known_email() {
    if (is_user_logged_in()) {
        $u = wp_get_current_user();
        if ($u && $u->user_email) {
            return $u->user_email;
        }
    }
    if (function_exists('WC') && WC()->customer) {
        return (string) WC()->customer->get_billing_email();
    }
    return '';
}

add_filter('woocommerce_coupon_get_email_restrictions', function ($emails, $coupon) {
    if (empty($emails) || !sp_ce_is_nl_code($coupon)) {
        return $emails;
    }
    return sp_ce_known_email() === '' ? [] : $emails;
}, 10, 2);

add_filter('woocommerce_coupon_error', function ($err, $code, $coupon) {
    if ((int) $code === WC_Coupon::E_WC_COUPON_NOT_YOURS_REMOVED && sp_ce_is_nl_code($coupon)) {
        return sprintf('Der Code „%s“ gilt nur mit der E-Mail-Adresse, mit der du dich für den Newsletter angemeldet hast. Bitte gib in der Kasse diese E-Mail-Adresse an.', esc_html(strtoupper($coupon->get_code())));
    }
    return $err;
}, 10, 3);
