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
        $cart->cart_contents[$key]['sp_bw_reg'] = sp_bw_regular($item['data']);
    }
    $run = false;
}, 26);

add_action('woocommerce_checkout_create_order_line_item', function ($li, $key, $values) {
    if (!empty($values['sp_bw']) && sp_bw_active()) {
        $li->add_meta_data('Black-Week-Rabatt', '−' . (int) $values['sp_bw'] . ' %', true);
        if (!empty($values['sp_bw_reg'])) $li->add_meta_data('_sp_bw_reg', round((float) $values['sp_bw_reg'] * (int) $values['quantity'], 2), true);
    }
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

/* ---------------------------------------------------------------------
 * Ersparnis auf Danke-Seite + in Bestellmails (2026-11-17)
 * ------------------------------------------------------------------- */
function sp_bw_order_saving($order) {
    if (!$order || !(int) $order->get_meta('_sp_bw')) return null;
    $save = 0.0;
    foreach ($order->get_items() as $it) {
        $reg = (float) $it->get_meta('_sp_bw_reg');
        if ($reg > 0) $save += $reg - (float) $it->get_total();
    }
    if ($save <= 0.005) return null;
    $bf = $order->get_meta('_sp_bw_phase') === 'bf';
    return ['save' => round($save, 2), 'pct' => (int) $order->get_meta('_sp_bw'), 'name' => $bf ? 'Black Friday' : 'Black Week'];
}
add_action('woocommerce_thankyou', function ($order_id) {
    $sv = sp_bw_order_saving(wc_get_order($order_id));
    if (!$sv) return;
    echo '<div id="sp-bw-ty"><div class="e">' . esc_html(strtoupper($sv['name'])) . ' · −' . (int) $sv['pct'] . ' %</div><div class="r"><span>Du hast gespart</span><strong>' . wp_kses_post(wc_price($sv['save'])) . '</strong></div></div>';
    echo '<style>#sp-bw-ty{margin:0 0 18px;background:radial-gradient(90% 140% at 50% 0%,#1E2024,#050506 72%);border-radius:16px;padding:16px 18px;color:#fff;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}#sp-bw-ty .e{font-size:10.5px;font-weight:600;letter-spacing:.38em;color:#9AA0A8}#sp-bw-ty .r{display:flex;justify-content:space-between;align-items:flex-end;gap:10px;margin-top:8px}#sp-bw-ty .r span{font-size:15px;font-weight:600}#sp-bw-ty strong{font-size:26px;font-weight:800;letter-spacing:-.03em;background:linear-gradient(180deg,#fff 0%,#F1F3F5 26%,#A3A9B1 48%,#5F656D 53%,#D9DDE1 68%,#fff 82%,#9AA0A8 100%);-webkit-background-clip:text;background-clip:text;color:transparent;white-space:nowrap}#sp-bw-ty strong *{color:inherit!important;-webkit-text-fill-color:transparent}</style>';
    echo "<script>(function(){var b=document.getElementById('sp-bw-ty'),t=0,i=setInterval(function(){var h=document.getElementById('sp-ty-hero');if(h){clearInterval(i);var bank=h.nextElementSibling&&/bacs|bank|ueberweisung/i.test(h.nextElementSibling.className)?h.nextElementSibling:h;bank.insertAdjacentElement('afterend',b);}else if(++t>30){clearInterval(i);var w=document.querySelector('.woocommerce-order');if(w)w.insertAdjacentElement('afterbegin',b);}},150);})();</script>";
}, 1);
add_action('woocommerce_email_before_order_table', function ($order, $sent_to_admin, $plain, $email = null) {
    if ($sent_to_admin || $plain || !$email) return;
    if (!in_array($email->id, ['customer_on_hold_order', 'customer_processing_order'], true)) return;
    $sv = sp_bw_order_saving($order);
    if (!$sv) return;
    echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;border-collapse:separate"><tr><td style="background:#0B0C0E;border-radius:14px;padding:16px 18px;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Inter,Arial,sans-serif;color:#FFFFFF;font-size:15px;line-height:1.45">'
        . '<span style="font-size:11px;letter-spacing:.3em;font-weight:700;color:#9AA0A8">' . esc_html(strtoupper($sv['name'])) . ' · −' . (int) $sv['pct'] . ' %</span><br>'
        . '<b style="color:#FFFFFF;font-size:18px">Du hast ' . wp_kses_post(wc_price($sv['save'])) . ' gespart</b>'
        . '</td></tr></table>';
}, 5, 4);

/* ---------------------------------------------------------------------
 * Texte für Omnisend-Mails (Warenkorb-Erinnerung, Willkommen) – leer außerhalb der Aktion
 * ------------------------------------------------------------------- */
function sp_bw_mail_props() {
    $o = sp_bw_opt();
    $t = time();
    $on = empty($o['off']) && $t >= (int) $o['start'] && $t <= (int) $o['end'];
    if (!$on) return ['bw_show' => 'none', 'bw_text' => '', 'bw_code_text' => ''];
    $bf = $t >= (int) $o['bf'];
    return [
        'bw_show' => 'block',
        'bw_text' => $bf ? '⚡ Black Friday: −35 % auf alles – nur bis Mo 30.11., 23:59' : '🖤 Black Week: −30 % auf alles – nur noch bis Do 26.11.',
        'bw_code_text' => 'Tipp: Bis 30.11. läuft unsere Black Week mit bis zu −35 % auf alles – ganz ohne Code. Deinen Code nutzt du einfach danach.',
    ];
}
