<?php
/**
 * Order details - Peptrium-Design (sp-email-design.php, 2026-10-10).
 * Produktliste mit Bildern + Summen. Die WooCommerce-Hooks vor/nach der
 * Tabelle bleiben erhalten (BACS-Anweisungen, Guthaben-Hinweis aus
 * sp-wallet-gateway.php usw.).
 */
defined('ABSPATH') || exit;

add_filter('woocommerce_order_shipping_to_display_shipped_via', '__return_false');

do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email);

$title = 'Deine Bestellung';
if ($sent_to_admin) {
    $title = '<a href="' . esc_url($order->get_edit_order_url()) . '" style="color:' . SP_EM_MUTED . ';">Bestellung #' . esc_html($order->get_order_number()) . '</a> · ' . esc_html(wc_format_datetime($order->get_date_created()));
} elseif ($email && $email->id === 'customer_completed_order' && defined('SP_TRACKING_META_KEY') && $order->get_meta(SP_TRACKING_META_KEY)) {
    $title = 'Im Paket';
}
?>
<div style="margin:6px 0 22px;">
<?php echo sp_em_label($title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<?php
echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    $order,
    [
        'show_sku' => $sent_to_admin,
        'show_image' => true,
        'image_size' => [112, 112],
        'plain_text' => $plain_text,
        'sent_to_admin' => $sent_to_admin,
    ]
);
?>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:6px;">
<?php
$totals = $order->get_order_item_totals();
$count = count($totals);
$i = 0;
foreach ($totals as $key => $total) {
    ++$i;
    $is_total = ($key === 'order_total');
    $label = rtrim(trim(wp_strip_all_tags($total['label'])), ':');
    if ($is_total) {
        $st = 'padding:12px 0 6px;font-size:17px;font-weight:800;color:' . SP_EM_INK . ';border-top:1px solid ' . SP_EM_LINE . ';';
    } else {
        $st = 'padding:7px 0 0;font-size:14px;color:' . SP_EM_MUTED . ';';
    }
    echo '<tr><td style="' . $st . '">' . esc_html($label) . '</td><td align="right" style="' . $st . ($is_total ? '' : 'color:' . SP_EM_INK . ';') . '">' . wp_kses_post($total['value']) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
?>
</table>
<?php if ($order->get_customer_note()) : ?>
<div style="background:<?php echo SP_EM_SOFT; ?>;border:1px solid <?php echo SP_EM_LINE; ?>;border-radius:12px;padding:12px 14px;margin-top:14px;font-size:14px;line-height:1.55;">
<strong>Anmerkung zur Bestellung</strong><br><?php echo wp_kses(nl2br(wc_wptexturize_order_note($order->get_customer_note())), ['br' => []]); ?>
</div>
<?php endif; ?>
</div>
<?php
remove_filter('woocommerce_order_shipping_to_display_shipped_via', '__return_false');

do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email);
