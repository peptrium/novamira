<?php
/**
 * Plugin Name: SP Black Week (Preise)
 * Description: (2026-11-17) Black Week −30 % / Black Friday −35 % auf alles (Peptide, Pens, Zubehör), zeitgesteuert.
 *   Option sp_bw: start/insider/bf/end (Unix), bonus (Partner-Prozentpunkte), ea (Insider-Token), off (Notschalter).
 *   Phasen: vor start = aus · start..bf = 30 % · insider..bf mit Insider-Cookie = 35 % · bf..end = 35 % · danach aus.
 *   Vorschau-Cookie sp_bw_pv (sp-blackweek-vorschau.php) schaltet die Preise für diesen Browser auch vorher ein.
 *   Katalogpreise bleiben unverändert (Anzeige rechnet sp-blackweek-vorschau.php), Rabatt wird im Warenkorb gesetzt.
 *   Codes: werden angenommen (Zuordnung), geben aber 0 € – außer Gutschrift-Codes (_sp_gutschrift). Mengenrabatt + Empfehlungs-Rabatt aus.
 */
if (!defined('ABSPATH')) exit;

function sp_bw_opt() {
    $o = get_option('sp_bw');
    if (!is_array($o)) $o = [];
    return $o + [
        'start'   => 1794942000, // Di 17.11.2026 20:00 Berlin
        'insider' => 1795712400, // Do 26.11. 18:00
        'bf'      => 1795734000, // Fr 27.11. 00:00
        'end'     => 1796079599, // Mo 30.11. 23:59:59
        'bonus'   => 5,
        'ea'      => '',
        'off'     => 0,
    ];
}
function sp_bw_skip_ids() { return [729, 745, 817]; }

/** Aktuelle Phase für diesen Besucher: '' | 'bw' | 'bf'. */
function sp_bw_phase() {
    static $ph = null;
    if ($ph !== null) return $ph;
    $o = sp_bw_opt();
    $pv = isset($_COOKIE['sp_bw_pv']) ? (string) $_COOKIE['sp_bw_pv'] : '';
    if ($pv === 'bw' || $pv === 'bf') return $ph = $pv;
    if (!empty($o['off'])) return $ph = '';
    $t = time();
    if ($t < (int) $o['start'] || $t > (int) $o['end']) return $ph = '';
    if ($t >= (int) $o['bf']) return $ph = 'bf';
    $ea = isset($_COOKIE['sp_bw_ea']) ? (string) $_COOKIE['sp_bw_ea'] : '';
    if ($t >= (int) $o['insider'] && $o['ea'] !== '' && hash_equals(substr(hash_hmac('sha256', 'ea', $o['ea']), 0, 16), $ea)) return $ph = 'bf';
    return $ph = 'bw';
}
function sp_bw_pct() { $p = sp_bw_phase(); return $p === 'bf' ? 35 : ($p === 'bw' ? 30 : 0); }
function sp_bw_active() { return sp_bw_pct() > 0; }

function sp_bw_product_ok($p) {
    if (!$p) return false;
    $pid = $p->get_parent_id() ?: $p->get_id();
    return !in_array((int) $pid, sp_bw_skip_ids(), true) && !in_array((int) $p->get_id(), sp_bw_skip_ids(), true);
}
function sp_bw_item_ok($item) {
    if (defined('SP_GIFT_ITEM_META') && !empty($item[SP_GIFT_ITEM_META])) return false;
    if (!empty($item['sp_abo_interval_days'])) return false;
    return !empty($item['data']) && sp_bw_product_ok($item['data']);
}
function sp_bw_regular($p) {
    $r = (float) $p->get_regular_price('edit');
    return $r > 0 ? $r : (float) $p->get_price('edit');
}

/* Preise im Warenkorb (nach Mengenrabatt Prio 20 → überschreibt ihn) */
add_action('woocommerce_before_calculate_totals', function ($cart) {
    static $run = false;
    if ($run || !sp_bw_active()) return;
    $run = true;
    $f = 1 - sp_bw_pct() / 100;
    foreach ($cart->get_cart() as $key => $item) {
        if (!sp_bw_item_ok($item)) continue;
        $cart->cart_contents[$key]['data']->set_price(round(sp_bw_regular($item['data']) * $f, 2));
        $cart->cart_contents[$key]['sp_bw'] = sp_bw_pct();
    }
    $run = false;
}, 26);

add_action('woocommerce_checkout_create_order_line_item', function ($li, $key, $values) {
    if (!empty($values['sp_bw']) && sp_bw_active()) $li->add_meta_data('Black-Week-Rabatt', '−' . (int) $values['sp_bw'] . ' %', true);
}, 20, 3);
function sp_bw_set_order_meta($order) {
    if (!sp_bw_active()) return;
    $order->update_meta_data('_sp_bw', sp_bw_pct());
    $order->update_meta_data('_sp_bw_phase', sp_bw_phase());
}
add_action('woocommerce_checkout_create_order', 'sp_bw_set_order_meta', 20);
add_action('woocommerce_store_api_checkout_update_order_meta', 'sp_bw_set_order_meta', 20);

/* Codes: gelten (Zuordnung), aber 0 € – außer Gutschriften */
function sp_bw_is_credit($coupon) {
    $id = is_object($coupon) ? $coupon->get_id() : wc_get_coupon_id_by_code((string) $coupon);
    return $id && get_post_meta($id, '_sp_gutschrift', true);
}
add_filter('woocommerce_coupon_get_discount_amount', function ($amount, $discounting, $cart_item, $single, $coupon) {
    return (sp_bw_active() && is_array($cart_item) && sp_bw_item_ok($cart_item) && !sp_bw_is_credit($coupon)) ? 0 : $amount;
}, 60, 5);
add_filter('woocommerce_coupon_message', function ($msg, $code, $coupon) {
    if ($code === WC_Coupon::WC_COUPON_SUCCESS && sp_bw_active() && $coupon && !sp_bw_is_credit($coupon)) {
        return 'Code gespeichert ✓ – in der ' . (sp_bw_phase() === 'bf' ? 'Black-Friday-Aktion' : 'Black Week') . ' gilt stattdessen −' . sp_bw_pct() . ' % auf alles.';
    }
    return $msg;
}, 60, 3);

/* Empfehlungs-Rabatt (Partner-Link) entfällt – die Aktion ist besser */
add_action('woocommerce_cart_calculate_fees', function ($cart) {
    if (!sp_bw_active()) return;
    $fees = $cart->fees_api()->get_fees();
    $ch = false;
    foreach ($fees as $k => $fee) {
        if ((float) $fee->amount < 0 && strpos((string) $fee->name, 'Empfehlungs-Rabatt') === 0) { unset($fees[$k]); $ch = true; }
    }
    if ($ch) $cart->fees_api()->set_fees($fees);
}, 99);

/* Partner: Bonus-Prozentpunkte */
add_filter('slicewp_calculate_commission_amount', function ($commission, $amount, $args) {
    if (!sp_bw_active() || empty($args['affiliate_id']) || ($args['type'] ?? '') !== 'sale') return $commission;
    $o = sp_bw_opt();
    return round((float) $commission + (float) $amount * (float) $o['bonus'] / 100, 2);
}, 31, 3);

/* Insider-Link /?sp_bw=frueh&k=<ea> */
add_action('init', function () {
    if (empty($_GET['sp_bw']) || $_GET['sp_bw'] !== 'frueh') return;
    $o = sp_bw_opt();
    $k = isset($_GET['k']) ? sanitize_key($_GET['k']) : '';
    if ($o['ea'] === '' || $k === '' || !hash_equals($o['ea'], $k)) return;
    $v = substr(hash_hmac('sha256', 'ea', $o['ea']), 0, 16);
    setcookie('sp_bw_ea', $v, ['expires' => (int) $o['end'] + 86400, 'path' => '/', 'secure' => true, 'httponly' => false, 'samesite' => 'Lax']);
    $keep = [];
    foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'] as $u) if (!empty($_GET[$u])) $keep[$u] = sanitize_text_field(wp_unslash($_GET[$u]));
    nocache_headers();
    wp_safe_redirect(add_query_arg($keep, home_url('/alle-produkte/')));
    exit;
}, 2);

/* Seiten-Cache an den Phasengrenzen leeren */
add_action('init', function () {
    $o = sp_bw_opt();
    foreach (['start', 'bf', 'end'] as $k) {
        $t = (int) $o[$k] + ($k === 'end' ? 2 : 1);
        if ($t > time() && !wp_next_scheduled('sp_bw_purge', [$k])) wp_schedule_single_event($t, 'sp_bw_purge', [$k]);
    }
}, 20);
add_action('sp_bw_purge', function ($k = '') {
    update_option('sp_pc_gen', (int) get_option('sp_pc_gen') + 1);
    $log = (array) get_option('sp_bw_log', []);
    $log[] = [time(), 'cache geleert: ' . $k];
    update_option('sp_bw_log', array_slice($log, -50), false);
});
