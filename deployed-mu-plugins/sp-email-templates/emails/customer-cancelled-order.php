<?php
/**
 * Bestellung storniert (cancelled) - Peptrium-Design (2026-10-10).
 * Stornos passieren nur von Hand (Vorkasse-Uebersicht ab 14 Tagen oder auf
 * Kundenwunsch) - daher der Hinweis fuer den Fall, dass doch schon ueberwiesen wurde.
 * @see sp-order-emails.php / sp-email-design.php
 */
defined('ABSPATH') || exit;

sp_em_hero([
    'eyebrow' => 'Bestellung #' . $order->get_order_number(),
    'sub' => 'Deine Bestellung wurde storniert.',
]);
do_action('woocommerce_email_header', $email_heading, $email);

$first = sp_em_first_name($order);
echo sp_em_p(($first ? 'Hallo ' . esc_html($first) . ',' : 'Hallo,') . '<br>deine Bestellung <strong>#' . esc_html($order->get_order_number()) . '</strong> wurde storniert. Du musst nichts weiter tun.'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo sp_em_note('💶 <strong>Schon überwiesen?</strong> Dann antworte kurz auf diese E-Mail – wir klären das sofort für dich.', 'warn'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

echo '<div style="text-align:center;margin:4px 0 26px;">' . sp_em_button('Neu bestellen', home_url('/alle-produkte/')) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

do_action('woocommerce_email_footer', $email);
