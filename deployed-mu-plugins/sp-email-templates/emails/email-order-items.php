<?php
/**
 * Order items - Peptrium-Design (sp-email-design.php, 2026-10-10).
 * Eine Zeile pro Artikel: Bild auf hellem Grund, Name, Eigenschaften
 * (Variante, Abo, Geschenk ...), Menge, Preis.
 */
defined('ABSPATH') || exit;

foreach ($items as $item_id => $item) :
    if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
        continue;
    }
    $product = $item->get_product();
    $sku = '';
    $purchase_note = '';
    $img_url = '';
    if (is_object($product)) {
        $sku = $product->get_sku();
        $purchase_note = $product->get_purchase_note();
        $image_id = $product->get_image_id();
        if (!$image_id && $product->get_parent_id()) {
            $parent = wc_get_product($product->get_parent_id());
            $image_id = $parent ? $parent->get_image_id() : 0;
        }
        $img_url = $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : '';
    }
    if (!$img_url) {
        $img_url = wc_placeholder_img_src('woocommerce_thumbnail');
    }

    $name = apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false);
    $meta = wc_display_item_meta($item, [
        'before' => '',
        'after' => '',
        'separator' => '<br>',
        'echo' => false,
        'label_before' => '<span>',
        'label_after' => ':</span> ',
    ]);

    $qty = $item->get_quantity();
    $refunded_qty = $order->get_qty_refunded_for_item($item_id);
    $qty_display = $refunded_qty ? '<del>' . esc_html($qty) . '</del> ' . esc_html($qty - ($refunded_qty * -1)) : esc_html($qty);
    $qty_display = apply_filters('woocommerce_email_order_item_quantity', $qty_display, $item);
    $line = 'border-bottom:1px solid ' . SP_EM_LINE . ';';
    ?>
<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
<td width="68" valign="middle" style="width:68px;padding:12px 0;<?php echo $line; ?>">
<div style="width:56px;height:56px;border-radius:12px;background:<?php echo SP_EM_SOFT; ?>;overflow:hidden;text-align:center;"><img src="<?php echo esc_url($img_url); ?>" alt="" height="56" style="height:56px;width:auto;max-width:56px;display:inline-block;border:0;margin:0;object-fit:cover;" /></div>
</td>
<td valign="middle" style="padding:12px 10px;<?php echo $line; ?>font-size:14px;font-weight:600;line-height:1.35;color:<?php echo SP_EM_INK; ?>;">
<?php echo wp_kses_post($name); ?><?php if ($show_sku && $sku) { echo ' <span style="font-weight:400;color:' . SP_EM_MUTED . ';">(#' . esc_html($sku) . ')</span>'; } ?>
<?php do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text); ?>
<?php if ($meta) : ?>
<div style="font-size:12px;font-weight:500;color:<?php echo SP_EM_MUTED; ?>;margin-top:3px;line-height:1.45;"><?php echo wp_kses($meta, ['br' => [], 'span' => [], 'a' => ['href' => true, 'target' => true, 'rel' => true]]); ?></div>
<?php endif; ?>
<?php do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text); ?>
<?php if ($qty_display !== '') : ?>
<div style="font-size:12px;font-weight:500;color:<?php echo SP_EM_MUTED; ?>;margin-top:3px;">Menge: <?php echo wp_kses_post($qty_display); ?></div>
<?php endif; ?>
</td>
<td align="right" valign="middle" style="padding:12px 0;<?php echo $line; ?>font-size:14px;font-weight:700;white-space:nowrap;color:<?php echo SP_EM_INK; ?>;"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
</tr>
<?php if ($show_purchase_note && $purchase_note) : ?>
<tr><td colspan="3" style="padding:8px 0 12px;font-size:13px;color:<?php echo SP_EM_MUTED; ?>;<?php echo $line; ?>"><?php echo wp_kses_post(wpautop(do_shortcode($purchase_note))); ?></td></tr>
<?php endif; ?>
<?php endforeach; ?>
