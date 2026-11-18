<?php
/**
 * Plugin Name: SP Black Week (Preise)
 * Description: (2026-11-17) Black Week −30 % / Black Friday −35 % auf alles (Peptide, Pens, Zubehör), zeitgesteuert.
 *   Option sp_bw: start/insider/bf/end (Unix), bonus (Partner-Prozentpunkte), ea (Insider-Token), off (Notschalter).
 *   Phasen: vor start = aus · start..bf = 30 % · insider..bf mit Insider-Cookie = 35 % · bf..end = 35 % · danach aus.
 *   Vorschau-Cookie sp_bw_pv (sp-blackweek-vorschau.php) schaltet die Preise für diesen Browser auch vorher ein.
 *   Katalogpreise bleiben unverändert (Anzeige rechnet sp-blackweek-vorschau.php), Rabatt wird im Warenkorb gesetzt.
 *   2026-11-18: Rabatt NUR noch mit Code BLACK30 (bis Do) / BLACK35 (ab Insider-Zeit Do 18 Uhr bzw. Fr) – echte WC-Gutscheine
 *   (Prozent, Betrag je Phase per Filter; BLACK30 gibt ab Freitag automatisch 35 %). Ohne Code gelten die normalen Shop-Regeln.
 *   Mit Code: Mengenrabatt + Empfehlungs-Rabatt aus, andere Codes 0 € (außer Gutschriften), Partner +bonus %.
 *   Auto-Einlösen: Cookie sp_bw_code (gesetzt per ?bw=1 / Mail-Links utm_campaign=blackweek-* / Pop-up), angewendet sobald etwas im Warenkorb liegt.
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
function sp_bw_codes() { return ['black30', 'black35']; }
function sp_bw_is_code($c) { $c = is_object($c) ? $c->get_code() : (string) $c; return in_array(wc_strtolower($c), sp_bw_codes(), true); }
/** Code, den die Aktion gerade bewirbt / automatisch einlöst. */
function sp_bw_current_code() { return sp_bw_phase() === 'bf' ? 'BLACK35' : 'BLACK30'; }
/** Prozent eines BW-Codes jetzt (0 = gerade nicht gültig). */
function sp_bw_code_pct($code) {
    $code = wc_strtolower((string) $code);
    $ph = sp_bw_phase();
    if (!$ph) return 0;
    if ($code === 'black35') {
        $o = sp_bw_opt();
        return ($ph === 'bf' || time() >= (int) $o['insider']) ? 35 : 0;
    }
    if ($code === 'black30') return $ph === 'bf' ? 35 : 30;
    return 0;
}
/** Prozent des BW-Codes im Warenkorb (0 = kein Code drin). */
function sp_bw_cart_pct($cart = null) {
    if (!$cart && function_exists('WC') && WC()->cart) $cart = WC()->cart;
    if (!$cart) return 0;
    $best = 0;
    foreach ($cart->get_applied_coupons() as $c) if (sp_bw_is_code($c)) $best = max($best, sp_bw_code_pct($c));
    return $best;
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
    $pct = sp_bw_cart_pct($cart);
    if (!$pct) return;
    $run = true;
    // Mit Code: Normalpreis (Mengenrabatt aus), der Gutschein zieht dann die % ab.
    foreach ($cart->get_cart() as $key => $item) {
        if (!sp_bw_item_ok($item)) continue;
        $cart->cart_contents[$key]['data']->set_price(sp_bw_regular($item['data']));
        $cart->cart_contents[$key]['sp_bw'] = $pct;
        $cart->cart_contents[$key]['sp_bw_reg'] = sp_bw_regular($item['data']);
    }
    $run = false;
}, 26);

add_action('woocommerce_checkout_create_order_line_item', function ($li, $key, $values) {
    if (!empty($values['sp_bw']) && sp_bw_cart_pct()) {
        $li->add_meta_data('Black-Week-Rabatt', '−' . (int) $values['sp_bw'] . ' %', true);
        if (!empty($values['sp_bw_reg'])) $li->add_meta_data('_sp_bw_reg', round((float) $values['sp_bw_reg'] * (int) $values['quantity'], 2), true);
    }
}, 20, 3);
function sp_bw_set_order_meta($order) {
    $pct = sp_bw_cart_pct();
    if (!$pct) return;
    $order->update_meta_data('_sp_bw', $pct);
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
    if (!is_array($cart_item)) return $amount;
    if (sp_bw_is_code($coupon)) return sp_bw_item_ok($cart_item) ? $amount : 0;   // BW-Code nicht auf Geschenke/Abo
    return (sp_bw_cart_pct() && sp_bw_item_ok($cart_item) && !sp_bw_is_credit($coupon)) ? 0 : $amount;
}, 60, 5);
/* BW-Codes: Prozent je Phase, Gültigkeit */
add_filter('woocommerce_coupon_get_amount', function ($amount, $coupon) {
    if (!sp_bw_is_code($coupon)) return $amount;
    $p = sp_bw_code_pct($coupon->get_code());
    return $p ?: $amount;
}, 20, 2);
add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    if (!$valid || !sp_bw_is_code($coupon)) return $valid;
    if (!sp_bw_phase()) throw new Exception('Die Black-Week-Codes gelten nur vom 17. bis 30. November.');
    if (!sp_bw_code_pct($coupon->get_code())) throw new Exception('Dieser Code ist noch nicht aktiv. Aktuell gilt BLACK30.');
    return $valid;
}, 20, 2);
/* BW-Code mit Partner-/Einzel-Codes kombinierbar (Zuordnung bleibt erhalten) */
add_filter('woocommerce_apply_individual_use_coupon', function ($keep) {
    if (!sp_bw_active() || !WC()->cart) return $keep;
    foreach (WC()->cart->get_applied_coupons() as $c) if (sp_bw_is_code($c)) $keep[] = $c;
    return $keep;
}, 20);
add_filter('woocommerce_apply_with_individual_use_coupon', function ($allow, $the_coupon) {
    return (sp_bw_active() && sp_bw_is_code($the_coupon)) ? true : $allow;
}, 20, 2);
/* Nur ein BW-Code gleichzeitig; Entfernen merken (dann nicht automatisch wieder einlösen) */
add_action('woocommerce_applied_coupon', function ($code) {
    if (!sp_bw_is_code($code) || !WC()->cart) return;
    if (WC()->session) WC()->session->set('sp_bw_rm', 0);
    foreach (WC()->cart->get_applied_coupons() as $c) {
        if ($c !== wc_strtolower($code) && sp_bw_is_code($c)) { $GLOBALS['sp_bw_swap'] = 1; WC()->cart->remove_coupon($c); unset($GLOBALS['sp_bw_swap']); }
    }
});
add_action('woocommerce_removed_coupon', function ($code) {
    if (sp_bw_is_code($code) && empty($GLOBALS['sp_bw_swap']) && WC()->session) WC()->session->set('sp_bw_rm', 1);
});
add_filter('woocommerce_coupon_message', function ($msg, $code, $coupon) {
    if ($code !== WC_Coupon::WC_COUPON_SUCCESS || !$coupon) return $msg;
    if (!empty($GLOBALS['sp_bw_quiet'])) return '';
    if (sp_bw_is_code($coupon)) return strtoupper($coupon->get_code()) . ' aktiv ✓ – −' . sp_bw_code_pct($coupon->get_code()) . ' % auf alles im Warenkorb.';
    if (sp_bw_cart_pct() && !sp_bw_is_credit($coupon)) return 'Code gespeichert ✓ – mit dem Black-Week-Code gilt schon −' . sp_bw_cart_pct() . ' %, andere Codes zählen nicht zusätzlich.';
    return $msg;
}, 60, 3);

/* Auto-Einlösen: Cookie sp_bw_code */
function sp_bw_set_code_cookie($code) {
    $o = sp_bw_opt();
    setcookie('sp_bw_code', $code, ['expires' => (int) $o['end'] + 86400, 'path' => '/', 'secure' => true, 'httponly' => false, 'samesite' => 'Lax']);
    $_COOKIE['sp_bw_code'] = $code;
}
add_action('init', function () {
    if (!sp_bw_active() || is_admin()) return;
    $g = $_GET;
    $want = !empty($g['bw']) || (!empty($g['utm_campaign']) && (strpos((string) $g['utm_campaign'], 'blackweek') === 0 || $g['utm_campaign'] === 'wieder-da-cagri'));
    if ($want && empty($_COOKIE['sp_bw_code'])) sp_bw_set_code_cookie(sp_bw_current_code());
}, 3);
function sp_bw_maybe_apply() {
    if (!sp_bw_active() || empty($_COOKIE['sp_bw_code']) || !function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) return;
    if (WC()->session && WC()->session->get('sp_bw_rm')) return;
    if (sp_bw_cart_pct()) return;
    $GLOBALS['sp_bw_quiet'] = 1;
    WC()->cart->apply_coupon(sp_bw_current_code());
    unset($GLOBALS['sp_bw_quiet']);
}
add_action('woocommerce_add_to_cart', function () { sp_bw_maybe_apply(); }, 30);
add_action('wp_loaded', function () { if (!is_admin() || wp_doing_ajax()) sp_bw_maybe_apply(); }, 30);

/* Empfehlungs-Rabatt (Partner-Link) entfällt – die Aktion ist besser */
add_action('woocommerce_cart_calculate_fees', function ($cart) {
    if (!sp_bw_cart_pct($cart)) return;
    $fees = $cart->fees_api()->get_fees();
    $ch = false;
    foreach ($fees as $k => $fee) {
        if ((float) $fee->amount < 0 && strpos((string) $fee->name, 'Empfehlungs-Rabatt') === 0) { unset($fees[$k]); $ch = true; }
    }
    if ($ch) $cart->fees_api()->set_fees($fees);
}, 99);

/* Partner: Bonus-Prozentpunkte */
add_filter('slicewp_calculate_commission_amount', function ($commission, $amount, $args) {
    if (!sp_bw_cart_pct() || empty($args['affiliate_id']) || ($args['type'] ?? '') !== 'sale') return $commission;
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
    sp_bw_set_code_cookie('BLACK35');
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
        'bw_text' => $bf ? '⚡ Black Friday: −35 % auf alles mit Code BLACK35 – nur bis Mo 30.11., 23:59' : '🖤 Black Week: −30 % auf alles mit Code BLACK30 – nur bis Do 26.11.',
        'bw_code_text' => $bf ? 'Tipp: Bis 30.11. bekommst du mit Code BLACK35 sogar −35 % auf alles. Deinen eigenen Code nutzt du einfach danach.' : 'Tipp: Bis Do 26.11. bekommst du mit Code BLACK30 sogar −30 % auf alles. Deinen eigenen Code nutzt du einfach danach.',
    ];
}
