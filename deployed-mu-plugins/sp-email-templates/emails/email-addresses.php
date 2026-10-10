<?php
/**
 * Addresses - Peptrium-Design (sp-email-design.php, 2026-10-10).
 * Eine Karte "Lieferadresse"; nur wenn die Rechnungsadresse abweicht, beide
 * nebeneinander. Telefon/E-Mail nur in Admin-Mails.
 */
defined('ABSPATH') || exit;

$billing = $order->get_formatted_billing_address();
$shipping = (!wc_ship_to_billing_address_only() && $order->needs_shipping_address()) ? $order->get_formatted_shipping_address() : '';
$same = !$shipping || $shipping === $billing;

$card = function ($title, $body) {
    return '<div style="background:' . SP_EM_SOFT . ';border:1px solid ' . SP_EM_LINE . ';border-radius:16px;padding:16px 16px;">'
        . sp_em_label($title)
        . '<div style="font-size:14px;line-height:1.55;color:' . SP_EM_INK . ';">' . $body . '</div></div>';
};

ob_start();
echo wp_kses_post($billing ?: 'k. A.');
if ($sent_to_admin) {
    if ($order->get_billing_phone()) {
        echo '<br>' . wc_make_phone_clickable($order->get_billing_phone()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    if ($order->get_billing_email()) {
        echo '<br>' . esc_html($order->get_billing_email());
    }
}
do_action('woocommerce_email_customer_address_section', 'billing', $order, $sent_to_admin, false);
$billing_html = ob_get_clean();

$left = $card($order->needs_shipping_address() ? 'Lieferadresse' : 'Rechnungsadresse', $same ? $billing_html : wp_kses_post($shipping));
if ($same) {
    echo '<div style="margin:0 0 22px;">' . $left . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}
$right = $card('Rechnungsadresse', $billing_html);
?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">
<tr>
<td valign="top" width="50%" style="width:50%;padding-right:6px;"><?php echo $left; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
<td valign="top" width="50%" style="width:50%;padding-left:6px;"><?php echo $right; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
</tr>
</table>
