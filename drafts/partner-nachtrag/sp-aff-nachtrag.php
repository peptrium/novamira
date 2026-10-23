<?php
/**
 * Nachgetragene Partner-Provision (2026-10-23): fuer Bestellungen, bei denen der Partner-Code
 * nachtraeglich angewendet wurde (SliceWP legt Provisionen nur beim Bestellen an).
 * Option `sp_aff_nachtrag` = [order_id => ['aff' => affiliate_id, 'rate' => 0.20]].
 * Sobald die Bestellung bezahlt ist (processing/completed), wird EINE Provision (unpaid) angelegt –
 * Satz × bezahlter Warenwert (ohne Versand/Geschenke) – und der Eintrag entfernt.
 */
if (!defined('ABSPATH')) exit;

function sp_aff_nachtrag_run($order_id) {
    $list = get_option('sp_aff_nachtrag', []);
    if (empty($list[$order_id]) || !function_exists('slicewp_insert_commission')) return;
    global $wpdb;
    $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}slicewp_commissions WHERE reference=%s AND status<>'rejected'", (string) $order_id));
    $order = wc_get_order($order_id);
    if (!$order) return;
    if (!$exists) {
        $goods = 0.0;
        foreach ($order->get_items() as $it) $goods += (float) $it->get_total();
        $aff = (int) $list[$order_id]['aff'];
        $rate = (float) $list[$order_id]['rate'];
        $amount = round($goods * $rate, 2);
        $now = current_time('mysql', true);
        $cid = slicewp_insert_commission([
            'affiliate_id' => $aff, 'visit_id' => 0, 'date_created' => $now, 'date_modified' => $now,
            'type' => 'sale', 'status' => 'unpaid', 'reference' => $order_id, 'reference_amount' => $order->get_total(),
            'customer_id' => $order->get_customer_id(), 'origin' => 'woo', 'amount' => $amount, 'currency' => $order->get_currency(),
        ]);
        if ($cid) {
            if (function_exists('slicewp_add_commission_meta')) slicewp_add_commission_meta($cid, '_sp_attribution_channel', 'nachtrag');
            $order->add_order_note(sprintf('Nachgetragene Partner-Provision #%d angelegt: %s (%d %% von %s) für Partner %d.', $cid, wc_price($amount), round($rate * 100), wc_price($goods), $aff));
        }
    }
    unset($list[$order_id]);
    update_option('sp_aff_nachtrag', $list, false);
}
add_action('woocommerce_order_status_processing', 'sp_aff_nachtrag_run', 30);
add_action('woocommerce_order_status_completed', 'sp_aff_nachtrag_run', 30);
