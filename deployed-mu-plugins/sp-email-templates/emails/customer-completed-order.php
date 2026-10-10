<?php
/**
 * Versandbestaetigung / Bestellung abgeschlossen (completed) - Peptrium-Design (2026-10-10).
 * Mit Sendungsnummer (Versendet-Button, sp-order-tracking.php): "Paket unterwegs".
 * Ohne Sendungsnummer: schlichte Abschluss-Mail.
 * @see sp-order-emails.php / sp-email-design.php
 */
defined('ABSPATH') || exit;

$tracking_number = defined('SP_TRACKING_META_KEY') ? $order->get_meta(SP_TRACKING_META_KEY) : '';
$first = sp_em_first_name($order);

if ($tracking_number) {
    sp_em_hero([
        'eyebrow' => 'Bestellung #' . $order->get_order_number(),
        'sub' => ($first ? 'Gute Nachrichten, ' . esc_html($first) . ': Wir' : 'Gute Nachrichten: Wir') . ' haben deine Bestellung an DHL übergeben.',
        'step' => 2,
        'preheader' => 'Dein Paket ist unterwegs – hier ist deine DHL-Sendungsnummer.',
    ]);
} else {
    sp_em_hero([
        'eyebrow' => 'Bestellung #' . $order->get_order_number(),
        'sub' => 'Vielen Dank für dein Vertrauen in Peptrium!',
    ]);
}
do_action('woocommerce_email_header', $email_heading, $email);

if ($tracking_number && function_exists('sp_tracking_dhl_url')) {
    echo sp_em_tracking_box($tracking_number, sp_tracking_dhl_url($tracking_number)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo sp_em_trust_row(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
    echo sp_em_p(($first ? 'Hallo ' . esc_html($first) . ',' : 'Hallo,') . '<br>deine Bestellung <strong>#' . esc_html($order->get_order_number()) . '</strong> ist abgeschlossen.'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

if ($tracking_number && !sp_em_order_has_abo($order)) {
    echo sp_em_abo_teaser(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo sp_em_p('Deinen Bestellstatus siehst du jederzeit unter <a href="' . esc_url(home_url('/bestellstatus/')) . '" style="color:' . SP_EM_INK . ';font-weight:700;">peptrium.com/bestellstatus</a>.', 'font-size:13px;color:' . SP_EM_MUTED . ';'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_footer', $email);
