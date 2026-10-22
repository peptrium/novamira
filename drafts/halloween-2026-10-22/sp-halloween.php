<?php
/**
 * Peptrium – Halloween-Modus (2026-10-22)
 *
 * Ein Schalter fuer alles: Peptrium Dashboard -> Halloween (Option sp_hw).
 *  - Angebot "Jede 3. Flasche gratis – die guenstigste": vorraetige Peptid-Flaschen
 *    (keine Pens, kein Zubehoer, keine Vorbestellungen, keine Abo-/Geschenk-Zeilen),
 *    auch gemischt. Pro 3 Flaschen wird die guenstigste Einheit kostenlos
 *    (Zeilenpreis per set_price, Prio 25 – nach dem Mengenrabatt (20), vor den
 *    Geschenkstufen (30); der Mengenrabatt wird fuer diese Flaschen zurueckgesetzt).
 *  - Waehrend der Aktion: keine Rabattcodes (HALLO/COMEBACK/TIKTOK10 …) und kein
 *    10-%-Empfehlungs-Rabatt; Partner-Codes werden angenommen (0 € Rabatt), damit die
 *    Bestellung dem Partner zugeordnet bleibt. Partner-Provision +N Prozentpunkte.
 *  - Zeitplan: Early Access (Link aus der Newsletter-Mail setzt Cookie sp_hw_ea),
 *    Start fuer alle, Ende – alles automatisch, solange der Hauptschalter an ist.
 *  - Halloween-Look (Schalter "deko"): Top-Banner mit Countdown, Fledermaeuse,
 *    Startseiten-Streifen, Badges auf Produktkarten, Buybox-Staffeln "1 gratis",
 *    Hinweis im Warenkorb.
 * Vorschau fuer Admins/Tests: /?sp_hw=vorschau&k=<pv> (Cookie 24 h, ?sp_hw=aus beendet).
 */
if (!defined('ABSPATH')) exit;

function sp_hw_tz() { return new DateTimeZone('Europe/Berlin'); }

function sp_hw_opt() {
    $o = get_option('sp_hw', []);
    if (!is_array($o)) $o = [];
    $tz = sp_hw_tz();
    $def = [
        'on'    => 0,
        'deko'  => 1,
        'early' => (new DateTime('2026-10-29 18:00:00', $tz))->getTimestamp(),
        'start' => (new DateTime('2026-10-30 00:00:00', $tz))->getTimestamp(),
        'end'   => (new DateTime('2026-11-01 23:59:59', $tz))->getTimestamp(),
        'bonus' => 5,
        'ea'    => '',
        'pv'    => '',
    ];
    $o = array_merge($def, $o);
    if ($o['ea'] === '' || $o['pv'] === '') {
        if ($o['ea'] === '') $o['ea'] = strtolower(wp_generate_password(10, false, false));
        if ($o['pv'] === '') $o['pv'] = strtolower(wp_generate_password(10, false, false));
        update_option('sp_hw', $o, false);
    }
    return $o;
}

function sp_hw_cookie_val($salt) {
    $o = sp_hw_opt();
    return substr(hash_hmac('sha256', $salt . '|' . $o[$salt === 'ea' ? 'ea' : 'pv'], wp_salt('auth')), 0, 24);
}

function sp_hw_is_preview() {
    return !empty($_COOKIE['sp_hw_pv']) && hash_equals(sp_hw_cookie_val('pv'), (string) $_COOKIE['sp_hw_pv']);
}

function sp_hw_has_ea() {
    return !empty($_COOKIE['sp_hw_ea']) && hash_equals(sp_hw_cookie_val('ea'), (string) $_COOKIE['sp_hw_ea']);
}

/** off | teaser | early | live | ended – Sicht des aktuellen Besuchers. */
function sp_hw_state() {
    static $s = null;
    if ($s !== null) return $s;
    $o = sp_hw_opt();
    if (sp_hw_is_preview()) return $s = 'live';
    if (empty($o['on'])) return $s = 'off';
    $now = time();
    if ($now >= $o['end']) return $s = 'ended';
    if ($now >= $o['start']) return $s = 'live';
    if ($now >= $o['early'] && sp_hw_has_ea()) return $s = 'early';
    return $s = 'teaser';
}

/** Angebot greift (Preise, Codes, Provision). */
function sp_hw_active() {
    $st = sp_hw_state();
    return $st === 'live' || $st === 'early';
}

function sp_hw_deko() {
    $o = sp_hw_opt();
    $st = sp_hw_state();
    return !empty($o['deko']) && in_array($st, ['teaser', 'early', 'live'], true);
}

/* ---------------------------------------------------------------------
 * Welche Flaschen zaehlen
 * ------------------------------------------------------------------- */
function sp_hw_excluded_ids() {
    // Guthaben, Willkommenspaket, Injektionskit, Pen Nadeln, Bac Water, Spritzen, Pens
    return [729, 817, 745, 908, 74, 80, 393, 395, 396];
}

function sp_hw_parent_eligible($pid) {
    static $cache = [];
    $pid = (int) $pid;
    if (isset($cache[$pid])) return $cache[$pid];
    $ok = true;
    if (in_array($pid, sp_hw_excluded_ids(), true)) $ok = false;
    if ($ok && has_term(20, 'product_cat', $pid)) $ok = false; // Zubehoer
    if ($ok && function_exists('sp_preorder_product_ids') && in_array($pid, array_map('intval', (array) sp_preorder_product_ids()), true)) $ok = false;
    if ($ok && get_post_status($pid) !== 'publish') $ok = false;
    return $cache[$pid] = $ok;
}

function sp_hw_product_eligible($product) {
    if (!$product) return false;
    $pid = $product->get_parent_id() ?: $product->get_id();
    if (!sp_hw_parent_eligible($pid)) return false;
    if ($product->is_on_sale('edit')) return false;
    return (float) $product->get_regular_price('edit') > 0;
}

/** Alle zaehlenden Produkt- und Variations-IDs (fuer Karten/Warenkorb-JS). */
function sp_hw_eligible_ids() {
    $ids = [];
    $q = wc_get_products(['status' => 'publish', 'limit' => -1, 'return' => 'ids', 'type' => ['simple', 'variable']]);
    foreach ($q as $pid) {
        if (!sp_hw_parent_eligible($pid)) continue;
        $p = wc_get_product($pid);
        if (!$p) continue;
        if ($p->is_type('variable')) {
            $any = false;
            foreach ($p->get_children() as $vid) {
                $v = wc_get_product($vid);
                if ($v && $v->get_status() === 'publish' && $v->is_in_stock() && sp_hw_product_eligible($v)) { $ids[] = (int) $vid; $any = true; }
            }
            if ($any) $ids[] = (int) $pid;
        } elseif ($p->is_in_stock() && sp_hw_product_eligible($p)) {
            $ids[] = (int) $pid;
        }
    }
    return $ids;
}

/* ---------------------------------------------------------------------
 * Preise: jede 3. Flasche gratis (die guenstigste)
 * ------------------------------------------------------------------- */
add_action('woocommerce_before_calculate_totals', function ($cart) {
    static $running = false;
    if ($running || !sp_hw_active()) return;
    $running = true;
    $units = [];
    $regs = [];
    foreach ($cart->get_cart() as $key => $item) {
        $cart->cart_contents[$key]['sp_hw_free'] = 0;
        if (defined('SP_GIFT_ITEM_META') && !empty($item[SP_GIFT_ITEM_META])) continue;
        if (!empty($item['sp_abo_interval_days'])) continue;
        $p = $item['data'];
        if (!sp_hw_product_eligible($p)) continue;
        $reg = (float) $p->get_regular_price('edit');
        $p->set_price($reg); // Mengenrabatt fuer Aktionsflaschen zuruecksetzen
        $regs[$key] = $reg;
        for ($i = 0; $i < (int) $item['quantity']; $i++) $units[] = [$reg, $key];
    }
    $free = intdiv(count($units), 3);
    if ($free > 0) {
        usort($units, function ($a, $b) { return $a[0] <=> $b[0] ?: strcmp($a[1], $b[1]); });
        $per = [];
        for ($i = 0; $i < $free; $i++) $per[$units[$i][1]] = ($per[$units[$i][1]] ?? 0) + 1;
        foreach ($per as $key => $f) {
            $qty = (int) $cart->cart_contents[$key]['quantity'];
            $cart->cart_contents[$key]['sp_hw_free'] = $f;
            $cart->cart_contents[$key]['data']->set_price($regs[$key] * ($qty - $f) / $qty);
        }
    }
    $running = false;
}, 25);

/* Streichpreis im Warenkorb: line_subtotal = regulaer (wie sp-quantity-discount.php). */
add_action('woocommerce_after_calculate_totals', function ($cart) {
    if (!sp_hw_active()) return;
    foreach ($cart->get_cart() as $key => $item) {
        if (empty($item['sp_hw_free'])) continue;
        $reg = (float) $item['data']->get_regular_price('edit');
        $cart->cart_contents[$key]['line_subtotal'] = round($reg * (int) $item['quantity'], 2);
    }
}, 30);

add_filter('woocommerce_get_item_data', function ($data, $item) {
    if (!empty($item['sp_hw_free']) && sp_hw_active()) {
        $data[] = ['key' => 'Halloween', 'value' => (int) $item['sp_hw_free'] . '× gratis 🎃'];
    }
    return $data;
}, 20, 2);

add_action('woocommerce_checkout_create_order_line_item', function ($li, $key, $values) {
    if (!empty($values['sp_hw_free']) && sp_hw_active()) {
        $li->add_meta_data('Halloween-Angebot', (int) $values['sp_hw_free'] . '× gratis', true);
    }
}, 20, 3);

add_action('woocommerce_checkout_create_order', function ($order) {
    if (!sp_hw_active() || !WC()->cart) return;
    $free = 0;
    foreach (WC()->cart->get_cart() as $item) $free += (int) ($item['sp_hw_free'] ?? 0);
    $order->update_meta_data('_sp_hw', $free);
    $order->update_meta_data('_sp_hw_phase', sp_hw_state());
}, 20);

/* ---------------------------------------------------------------------
 * Codes & Empfehlungs-Rabatt waehrend der Aktion
 * ------------------------------------------------------------------- */
function sp_hw_is_partner_coupon($coupon) {
    $id = is_object($coupon) ? $coupon->get_id() : wc_get_coupon_id_by_code((string) $coupon);
    return $id && get_post_meta($id, '_sp_affiliate_id', true);
}

add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    if (!$valid || !sp_hw_active() || sp_hw_is_partner_coupon($coupon)) return $valid;
    return false;
}, 50, 2);

add_filter('woocommerce_coupon_error', function ($err, $err_code, $coupon) {
    if ((int) $err_code === WC_Coupon::E_WC_COUPON_INVALID_FILTERED && sp_hw_active() && $coupon && !sp_hw_is_partner_coupon($coupon)) {
        return 'Während des Halloween-Angebots gelten keine Rabattcodes – jede 3. Flasche ist schon gratis. 🎃';
    }
    return $err;
}, 50, 3);

add_filter('woocommerce_coupon_get_discount_amount', function ($amount, $discounting, $cart_item, $single, $coupon) {
    return (sp_hw_active() && sp_hw_is_partner_coupon($coupon)) ? 0 : $amount;
}, 50, 5);

add_filter('woocommerce_coupon_message', function ($msg, $code, $coupon) {
    if ($code === WC_Coupon::WC_COUPON_SUCCESS && sp_hw_active() && sp_hw_is_partner_coupon($coupon)) {
        return 'Partner-Code erkannt ✓ – das Halloween-Angebot gilt bereits, der Code gibt keinen zusätzlichen Rabatt.';
    }
    return $msg;
}, 50, 3);

add_action('woocommerce_cart_calculate_fees', function ($cart) {
    if (!sp_hw_active()) return;
    $fees = $cart->fees_api()->get_fees();
    $changed = false;
    foreach ($fees as $k => $fee) {
        if ((float) $fee->amount < 0 && strpos((string) $fee->name, 'Empfehlungs-Rabatt') === 0) { unset($fees[$k]); $changed = true; }
    }
    if ($changed) $cart->fees_api()->set_fees($fees);
}, 98);

/* Partner-Provision + Bonus-Prozentpunkte auf den bezahlten Positionsbetrag. */
add_filter('slicewp_calculate_commission_amount', function ($commission, $amount, $args) {
    if (!sp_hw_active() || empty($args['affiliate_id']) || ($args['type'] ?? '') !== 'sale') return $commission;
    $o = sp_hw_opt();
    return round((float) $commission + (float) $amount * (float) $o['bonus'] / 100, 2);
}, 30, 3);

/* ---------------------------------------------------------------------
 * Links: Early Access / Vorschau
 * ------------------------------------------------------------------- */
add_action('init', function () {
    if (empty($_GET['sp_hw'])) return;
    $o = sp_hw_opt();
    $a = sanitize_key($_GET['sp_hw']);
    $k = isset($_GET['k']) ? sanitize_key($_GET['k']) : '';
    $keep = [];
    foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'] as $u) if (!empty($_GET[$u])) $keep[$u] = sanitize_text_field(wp_unslash($_GET[$u]));
    if ($a === 'frueh' && $k !== '' && hash_equals($o['ea'], $k)) {
        setcookie('sp_hw_ea', sp_hw_cookie_val('ea'), ['expires' => (int) $o['end'] + 86400, 'path' => '/', 'secure' => true, 'httponly' => false, 'samesite' => 'Lax']);
        wp_safe_redirect(add_query_arg($keep, home_url('/alle-produkte/')));
        exit;
    }
    if ($a === 'vorschau' && $k !== '' && hash_equals($o['pv'], $k)) {
        setcookie('sp_hw_pv', sp_hw_cookie_val('pv'), ['expires' => time() + DAY_IN_SECONDS, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
        wp_safe_redirect(home_url('/'));
        exit;
    }
    if ($a === 'aus') {
        setcookie('sp_hw_pv', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
        wp_safe_redirect(home_url('/'));
        exit;
    }
}, 1);

/* ---------------------------------------------------------------------
 * Buybox: Mengen-Staffeln zeigen "1 gratis" statt −10/−15/−20 %
 * (Gesamtpreis der Buybox rechnet mit data-disc → stimmt mit dem Warenkorb)
 * ------------------------------------------------------------------- */
add_action('template_redirect', function () {
    if (!sp_hw_active() || !is_product()) return;
    $p = wc_get_product(get_queried_object_id());
    if (!$p || !sp_hw_parent_eligible($p->get_id())) return;
    if ($p->is_type('simple') && !sp_hw_product_eligible($p)) return;
    ob_start(function ($html) {
        // data-disc 0: Die Buybox rechnet nur ganze Prozent und nur die groesste Staffel –
        // den richtigen Gesamtpreis (jede 3. gratis) setzt das Halloween-JS.
        $map = ['3' => ['0', '1 gratis'], '5' => ['0', '1 gratis'], '10' => ['0', '3 gratis']];
        $html = preg_replace_callback('/(data-q="(3|5|10)" data-disc=")[\d.]+(">\s*<span class="rx-tier__disc">)[^<]*(<\/span>)/', function ($m) use ($map) {
            return $m[1] . $map[$m[2]][0] . $m[3] . $map[$m[2]][1] . $m[4];
        }, $html);
        return str_replace('rx-tier__flag-text">Beliebt<', 'rx-tier__flag-text">🎃 Halloween<', $html);
    });
}, 7);

/* ---------------------------------------------------------------------
 * Look & Hinweise
 * ------------------------------------------------------------------- */
function sp_hw_fmt_date($ts, $f) { return wp_date($f, (int) $ts, sp_hw_tz()); }

add_action('wp_head', function () {
    if (is_admin() || !sp_hw_deko()) return;
    ?>
<style id="sp-hw-css">
:root{--hw-or:#FF7A1A;--hw-or2:#FFB547;--hw-bg:#0B0806}
#sp-top-banner.sp-hw{background:linear-gradient(90deg,#1A0D04,#2A1306 50%,#1A0D04)!important;color:#FFD9B0!important;position:relative;overflow:hidden}
#sp-top-banner.sp-hw .sp-tb-mask{display:none!important}
#sp-hw-tb{display:flex;align-items:center;justify-content:center;gap:8px;height:100%;min-height:inherit;font:700 12.5px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;letter-spacing:.02em;white-space:nowrap;text-decoration:none;color:#FFD9B0!important;padding:0 10px;position:relative;z-index:2}
#sp-hw-tb b{color:var(--hw-or2)}
#sp-hw-tb .cd{font-variant-numeric:tabular-nums;background:rgba(255,122,26,.16);border:1px solid rgba(255,122,26,.35);border-radius:6px;padding:2px 6px;color:#FFE7CC}
.sp-hw-bat{position:absolute;top:0;width:22px;height:11px;fill:#FFB547;opacity:.35;z-index:1;animation:sphwfly 14s linear infinite}
.sp-hw-bat.b2{width:16px;height:8px;animation-duration:19s;animation-delay:-7s;top:14px}
.sp-hw-bat.b3{width:12px;height:6px;animation-duration:23s;animation-delay:-13s;top:4px}
@keyframes sphwfly{0%{left:-30px;transform:translateY(6px)}25%{transform:translateY(0)}50%{transform:translateY(8px)}75%{transform:translateY(2px)}100%{left:calc(100% + 30px);transform:translateY(6px)}}
@media (max-width:420px){#sp-hw-tb{font-size:11.5px;gap:6px}#sp-hw-tb .lg{display:none}}
/* Startseite / Kategorie-Streifen */
.sp-hw-strip{position:relative;overflow:hidden;background:radial-gradient(120% 140% at 85% 0%,rgba(255,122,26,.30) 0%,rgba(255,122,26,0) 55%),radial-gradient(80% 120% at 0% 100%,rgba(120,40,160,.25) 0%,rgba(120,40,160,0) 60%),var(--hw-bg);color:#F4ECE4;padding:34px 20px 30px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif}
.sp-hw-strip .in{max-width:1100px;margin:0 auto;position:relative;z-index:2}
.sp-hw-strip .ey{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:800;letter-spacing:.2em;color:var(--hw-or2);border:1px solid rgba(255,122,26,.4);border-radius:99px;padding:6px 12px}
.sp-hw-strip h2{font-size:clamp(30px,7vw,48px);line-height:1.05;font-weight:800;letter-spacing:-.02em;margin:14px 0 10px;color:#fff}
.sp-hw-strip h2 span{background:linear-gradient(90deg,var(--hw-or),var(--hw-or2));-webkit-background-clip:text;background-clip:text;color:transparent}
.sp-hw-strip p{color:#CDBFB2;font-size:15px;line-height:1.55;margin:0 0 18px;max-width:560px}
.sp-hw-cd{display:flex;gap:8px;margin:0 0 18px}
.sp-hw-cd div{min-width:58px;text-align:center;background:rgba(255,255,255,.05);border:1px solid rgba(255,122,26,.28);border-radius:12px;padding:8px 6px}
.sp-hw-cd b{display:block;font-size:22px;font-weight:800;color:#fff;font-variant-numeric:tabular-nums}
.sp-hw-cd small{font-size:10px;letter-spacing:.12em;color:#A89888}
.sp-hw-btn{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(90deg,var(--hw-or),var(--hw-or2));color:#140A02!important;font-weight:800;font-size:15px;text-decoration:none;border-radius:12px;padding:14px 20px}
.sp-hw-strip .pk{position:absolute;right:-6px;bottom:-18px;font-size:120px;line-height:1;opacity:.9;z-index:1;filter:drop-shadow(0 0 30px rgba(255,122,26,.45));transform:rotate(-8deg)}
.sp-hw-strip .web{position:absolute;left:0;top:0;width:110px;height:110px;opacity:.18;z-index:1}
@media (max-width:600px){.sp-hw-strip .pk{font-size:84px;right:-10px;bottom:-14px;opacity:.55}}
.sp-hw-strip.mini{padding:16px 18px}
.sp-hw-strip.mini h2{font-size:22px;margin:8px 0 4px}
.sp-hw-strip.mini p{font-size:13.5px;margin:0}
.sp-hw-strip.mini .pk{font-size:64px;bottom:-10px}
/* Karten-Badge */
.sp-pc{position:relative}
.sp-hw-badge{position:absolute;top:10px;right:10px;z-index:3;background:#140A02;color:var(--hw-or2);border:1px solid rgba(255,122,26,.5);font:800 11px/1 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;border-radius:99px;padding:6px 9px;letter-spacing:.02em;pointer-events:none}
/* Buybox */
#sp-hw-bb{background:linear-gradient(135deg,#140A02,#2A1306);color:#FFE7CC;border:1px solid rgba(255,122,26,.45);border-radius:14px;padding:12px 14px;margin:0 0 14px;font:14px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif}
#sp-hw-bb b{color:var(--hw-or2);display:block;font-size:13px;letter-spacing:.08em;margin-bottom:2px}
#rx-tiers .rx-tier[data-q="3"] .rx-tier__disc,#rx-tiers .rx-tier[data-q="5"] .rx-tier__disc,#rx-tiers .rx-tier[data-q="10"] .rx-tier__disc{color:#B94A00!important}
/* Warenkorb */
#sp-hw-cd{margin:10px 14px 4px;background:#140A02;color:#FFE7CC;border:1px solid rgba(255,122,26,.45);border-radius:12px;padding:10px 12px;font:600 13.5px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;display:flex;gap:10px;align-items:center}
#sp-hw-cd .bar{flex:1;height:6px;border-radius:6px;background:rgba(255,255,255,.12);overflow:hidden;margin-top:6px}
#sp-hw-cd .bar i{display:block;height:100%;background:linear-gradient(90deg,var(--hw-or),var(--hw-or2));border-radius:6px;transition:width .3s}
@media (prefers-reduced-motion:reduce){.sp-hw-bat{display:none}}
/* Hero-Uebernahme Startseite */
.sp-hw-ey{display:inline-flex;align-items:center;gap:7px;font:800 10.5px/1 Sora,-apple-system,sans-serif;letter-spacing:.18em;color:#FFB547;border:1px solid rgba(255,122,26,.55);background:rgba(20,10,2,.55);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);border-radius:99px;padding:7px 12px;margin:0 0 12px}
.sp-hw-g{background:linear-gradient(90deg,#FF7A1A,#FFB547)!important;-webkit-background-clip:text!important;background-clip:text!important;color:transparent!important;-webkit-text-fill-color:transparent!important}
.sp-hero2-cta.sp-hw-cta,#sp-hero-banner .sp-hb-cta.sp-hw-cta{background:linear-gradient(90deg,#FF7A1A,#FFB547)!important;color:#140A02!important;font-weight:800!important;box-shadow:0 10px 30px rgba(255,122,26,.45)!important}
.sp-hero2-cta-wrap.sp-hw-cw{flex-direction:column;align-items:center;gap:10px}
.sp-hw-hcd{display:flex;gap:6px;justify-content:center}
.sp-hw-hcd span{min-width:46px;text-align:center;background:rgba(20,10,2,.6);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);border:1px solid rgba(255,122,26,.4);border-radius:10px;padding:6px 4px;font:800 16px/1 Sora,-apple-system,sans-serif;color:#fff;font-variant-numeric:tabular-nums}
.sp-hw-hcd small{display:block;font-size:8.5px;letter-spacing:.12em;color:#C9A98A;margin-top:4px;font-weight:700}
.sp-hw-hov{position:absolute;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.sp-hw-hov:before{content:'';position:absolute;inset:0;background:radial-gradient(70% 45% at 50% 78%,rgba(255,110,20,.38) 0%,rgba(255,110,20,0) 70%),radial-gradient(60% 40% at 15% 0%,rgba(110,30,150,.30) 0%,rgba(110,30,150,0) 70%);mix-blend-mode:screen}
.sp-hw-hov .web{position:absolute;right:0;top:0;width:150px;height:150px;opacity:.22;transform:scaleX(-1)}
.sp-hw-hov .hb{position:absolute;fill:#0A0604;opacity:.85;filter:drop-shadow(0 0 6px rgba(255,140,40,.35));animation:sphwhb 16s linear infinite}
.sp-hw-hov .hb.h1{width:44px;top:22%;animation-duration:15s}
.sp-hw-hov .hb.h2{width:30px;top:12%;animation-duration:21s;animation-delay:-8s}
.sp-hw-hov .hb.h3{width:22px;top:34%;animation-duration:26s;animation-delay:-15s}
@keyframes sphwhb{0%{left:-60px;transform:translateY(0) rotate(-4deg)}20%{transform:translateY(-14px) rotate(3deg)}40%{transform:translateY(6px) rotate(-3deg)}60%{transform:translateY(-10px) rotate(4deg)}80%{transform:translateY(4px) rotate(-2deg)}100%{left:calc(100% + 60px);transform:translateY(0) rotate(0)}}
.sp-hw-hov i{position:absolute;bottom:-10px;width:4px;height:4px;border-radius:50%;background:#FFB547;box-shadow:0 0 8px 2px rgba(255,122,26,.8);opacity:0;animation:sphwem 7s linear infinite}
@keyframes sphwem{0%{transform:translateY(0) translateX(0);opacity:0}10%{opacity:.9}100%{transform:translateY(-480px) translateX(30px);opacity:0}}
.sp-hw-hov .pk{position:absolute;bottom:92px;font-size:44px;line-height:1;filter:drop-shadow(0 0 18px rgba(255,122,26,.75));animation:sphwpk 3s ease-in-out infinite}
.sp-hw-hov .pk.l{left:14px;transform:rotate(-10deg)}
.sp-hw-hov .pk.r{right:14px;font-size:34px;animation-delay:-1.5s}
@keyframes sphwpk{0%,100%{filter:drop-shadow(0 0 10px rgba(255,122,26,.5))}50%{filter:drop-shadow(0 0 24px rgba(255,122,26,.95))}}
.sp-hero2-text,.sp-hero2-cta-wrap{z-index:2!important}
.sp-hero2-text.sp-hw-t .sp-hw-ey{margin-bottom:10px}
.sp-hero2-text.sp-hw-t .sp-hero2-sub{text-shadow:0 2px 10px rgba(0,0,0,.9);color:rgba(255,255,255,.88)}
.sp-hero2-img-wrap .sp-hw-hov .pk{display:none}
#sp-hero-banner .sp-hw-hov{z-index:0}#sp-hero-banner .sp-hw-hov .pk{bottom:40px}#sp-hero-banner .sp-hw-hov .pk.l{left:auto;right:42%}
@media (prefers-reduced-motion:reduce){.sp-hw-hov .hb,.sp-hw-hov i{display:none}}

</style>
    <?php
}, 50);

add_action('wp_footer', function () {
    if (is_admin() || !sp_hw_deko()) return;
    $o = sp_hw_opt();
    $st = sp_hw_state();
    $ids = get_transient('sp_hw_ids_v1');
    if (!is_array($ids)) { $ids = sp_hw_eligible_ids(); set_transient('sp_hw_ids_v1', $ids, 10 * MINUTE_IN_SECONDS); }
    $pp = 0;
    if (is_product()) {
        $p = wc_get_product(get_queried_object_id());
        if ($p && in_array((int) $p->get_id(), $ids, true)) $pp = 1;
    }
    $cfg = [
        'state'  => $st,
        'end'    => (int) $o['end'] * 1000,
        'start'  => (int) $o['start'] * 1000,
        'startT' => sp_hw_fmt_date($o['start'], 'D j.n.'),
        'earlyT' => sp_hw_fmt_date($o['early'], 'D') . ' ' . sp_hw_fmt_date($o['early'], 'G') . ' Uhr',
        'endT'   => sp_hw_fmt_date($o['end'], 'D H:i'),
        'ea'     => sp_hw_has_ea() ? 1 : 0,
        'ids'    => $ids,
        'pp'     => $pp,
        'home'   => is_front_page() ? 1 : 0,
        'cat'    => (is_product_category() || is_shop() || is_page(325)) ? 1 : 0,
    ];
    ?>
<script id="sp-hw-js">
(function(){var C=<?php echo wp_json_encode($cfg); ?>;var live=C.state==='live'||C.state==='early';
var DE=['So','Mo','Di','Mi','Do','Fr','Sa'];
function pad(n){return (n<10?'0':'')+n}
function left(t){var s=Math.max(0,Math.floor((t-Date.now())/1000));return {d:Math.floor(s/86400),h:Math.floor(s%86400/3600),m:Math.floor(s%3600/60),s:s%60,t:s}}
var bat='<svg viewBox="0 0 24 12" class="sp-hw-bat XX"><path d="M0 4C3 1 6 1 8 3.5 9 1.5 10.5 1 12 2.6 13.5 1 15 1.5 16 3.5 18 1 21 1 24 4 21.5 4 19.6 5 18.6 7 16.8 6 14.6 6.4 12 9 9.4 6.4 7.2 6 5.4 7 4.4 5 2.5 4 0 4Z"/></svg>';
/* Top-Banner */
var tb=document.getElementById('sp-top-banner');
if(tb){tb.classList.add('sp-hw');var a=document.createElement('a');a.id='sp-hw-tb';a.href='/alle-produkte/';
 tb.insertAdjacentHTML('beforeend',bat.replace('XX','b1')+bat.replace('XX','b2')+bat.replace('XX','b3'));tb.appendChild(a);
 var tick=function(){if(live){var l=left(C.end);a.innerHTML='🎃 <b>Halloween:</b> Jede 3. Flasche gratis <span class="lg">· endet in</span> <span class="cd">'+(l.d?l.d+'T ':'')+pad(l.h)+':'+pad(l.m)+':'+pad(l.s)+'</span>';}
  else{a.innerHTML='🎃 <b>Halloween-Special</b> ab '+C.startT+' <span class="lg">· Newsletter: schon '+C.earlyT+'</span>';}};
 tick();setInterval(tick,1000);}
/* Streifen Startseite / Kategorie */
function strip(mini){var s=document.createElement('section');s.className='sp-hw-strip'+(mini?' mini':'');
 var web='<svg class="web" viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".8"><path d="M0 0L100 100M0 0L50 100M0 0L100 50M0 0L0 100M0 0L100 0"/><path d="M0 20Q14 14 20 0M0 40Q28 28 40 0M0 62Q44 44 62 0M0 84Q60 60 84 0"/></svg>';
 if(live){s.innerHTML=web+'<div class="in"><span class="ey">🎃 HALLOWEEN-SPECIAL'+(C.state==='early'?' · EARLY ACCESS':'')+'</span><h2>Jede 3. Flasche <span>gratis.</span></h2>'
  +(mini?'<p>Leg 3 Flaschen in den Warenkorb – die günstigste zahlst du nicht. Endet '+C.endT+' Uhr.</p>':'<p>Leg 3 Flaschen in den Warenkorb – die günstigste zahlst du nicht. Gilt für alle vorrätigen Peptid-Flaschen, auch gemischt. Nur bis '+C.endT+' Uhr.</p><div class="sp-hw-cd" id="sp-hw-cd2"></div><a class="sp-hw-btn" href="/alle-produkte/">Flaschen wählen →</a>')+'</div><div class="pk">🎃</div>';}
 else{s.innerHTML=web+'<div class="in"><span class="ey">🎃 BALD: HALLOWEEN-SPECIAL</span><h2>Jede 3. Flasche <span>gratis.</span></h2><p>Ab '+C.startT+' für alle. Newsletter-Abonnenten bekommen es schon '+C.earlyT+'.</p>'
  +(mini?'':'<a class="sp-hw-btn" href="#sp-nlh">Newsletter – früher dabei sein →</a>')+'</div><div class="pk">🎃</div>';}
 return s;}
if(C.home){var ht=0,hi=setInterval(function(){
 var m=document.querySelector('.sp-hero2'),mob=m&&m.offsetParent!==null;
 var title=mob?m.querySelector('.sp-hero2-title'):document.querySelector('#sp-hero-banner .sp-hb-hero-title');
 if(!title){if(++ht>40){clearInterval(hi);var hero=document.querySelector('.elementor-211>[data-id="shbn001"]');if(hero){var s0=strip(0);s0.style.order='1';hero.insertAdjacentElement('afterend',s0);}}return;}
 clearInterval(hi);
 var sub=mob?m.querySelector('.sp-hero2-sub'):document.querySelector('#sp-hero-banner .sp-hb-hero-subtext');
 var cta=mob?m.querySelector('.sp-hero2-cta'):document.querySelector('#sp-hero-banner .sp-hb-cta');
 var wrap=mob?m.querySelector('.sp-hero2-img-wrap'):document.querySelector('#sp-hero-banner .sp-hb-image-wrap');
 title.insertAdjacentHTML('beforebegin','<div class="sp-hw-ey">🎃 HALLOWEEN-SPECIAL'+(C.state==='early'?' · EARLY ACCESS':'')+'</div>');
 title.innerHTML='Jede 3. Flasche<br><span class="sp-hw-g">gratis.</span>';
 if(mob){var tx=m.querySelector('.sp-hero2-text');if(tx)tx.classList.add('sp-hw-t');}
 if(sub)sub.innerHTML=live?(mob?'Die günstigste zahlst du nicht.':'Leg 3 Flaschen in den Warenkorb –<br>die günstigste zahlst du nicht.'):'Ab '+C.startT+' für alle.<br>Newsletter-Abonnenten schon '+C.earlyT+'.';
 if(cta){cta.classList.add('sp-hw-cta');if(live){cta.firstChild.nodeValue='3 für 2 sichern ';}else{cta.setAttribute('href','#sp-nlh');cta.firstChild.nodeValue='Früher dabei sein ';}
  var cw=cta.parentElement;if(mob&&cw)cw.classList.add('sp-hw-cw');
  if(live){var hc=document.createElement('div');hc.className='sp-hw-hcd';cta.insertAdjacentElement('beforebegin',hc);
   var t3=function(){var l=left(C.end);hc.innerHTML='<span>'+l.d+'<small>TAGE</small></span><span>'+pad(l.h)+'<small>STD</small></span><span>'+pad(l.m)+'<small>MIN</small></span><span>'+pad(l.s)+'<small>SEK</small></span>';};t3();setInterval(t3,1000);}}
 if(wrap){var ov=document.createElement('div');ov.className='sp-hw-hov';var em='';for(var k=0;k<14;k++){em+='<i style="left:'+(5+Math.random()*90).toFixed(1)+'%;animation-delay:-'+(Math.random()*7).toFixed(2)+'s;animation-duration:'+(5+Math.random()*4).toFixed(2)+'s"></i>';}
  ov.innerHTML='<svg class="web" viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".8"><path d="M0 0L100 100M0 0L50 100M0 0L100 50M0 0L0 100M0 0L100 0"/><path d="M0 20Q14 14 20 0M0 40Q28 28 40 0M0 62Q44 44 62 0M0 84Q60 60 84 0"/></svg>'
   +['h1','h2','h3'].map(function(c){return bat.replace('sp-hw-bat XX','hb '+c);}).join('')+em+'<span class="pk l">🎃</span><span class="pk r">🎃</span>';
  wrap.appendChild(ov);}
},120);}
if(C.cat){var tries=0,ci=setInterval(function(){var sc=document.getElementById('sp-cat');if(sc&&sc.firstElementChild){clearInterval(ci);sc.firstElementChild.insertAdjacentElement('afterend',strip(1));}else if(++tries>40){clearInterval(ci);}},150);}
var cd2=document.getElementById('sp-hw-cd2');if(cd2){var t2=function(){var l=left(C.end);cd2.innerHTML='<div><b>'+l.d+'</b><small>TAGE</small></div><div><b>'+pad(l.h)+'</b><small>STD</small></div><div><b>'+pad(l.m)+'</b><small>MIN</small></div><div><b>'+pad(l.s)+'</b><small>SEK</small></div>';};t2();setInterval(t2,1000);}
if(!live)return;
/* Karten-Badges */
var ids={};C.ids.forEach(function(i){ids[i]=1});
function badges(){document.querySelectorAll('.sp-pc[data-id]').forEach(function(c){if(!ids[c.getAttribute('data-id')])return;
 if(!c.querySelector('.sp-hw-badge'))c.insertAdjacentHTML('beforeend','<span class="sp-hw-badge">🎃 3 für 2</span>');
 c.querySelectorAll('*').forEach(function(e){if(e.children.length===0&&/^ab 3 St(ü|u)ck/.test(e.textContent.trim())&&!e.getAttribute('data-hw')){e.setAttribute('data-hw',1);e.textContent='🎃 Jede 3. Flasche gratis';e.style.color='#C2410C';}});});
 var cd=document.getElementById('sp-cd-items');if(cd)cd.querySelectorAll('*').forEach(function(e){if(e.children.length===0&&/^Bundle\s/.test(e.textContent.trim()))e.textContent='🎃 Halloween';});}
badges();new MutationObserver(function(){badges();}).observe(document.body,{childList:true,subtree:true});
/* Buybox-Gesamtpreis: jede 3. Flasche gratis */
var tot=document.getElementById('rx-total'),sav=document.getElementById('rx-save'),qf=document.getElementById('rx-qtyfield'),pe=document.getElementById('rx-price');
function num(t){return parseFloat(String(t).replace(/[^0-9,.-]/g,'').replace(/\./g,'').replace(',','.'))||0}
function fm(n){return n.toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2})+'\u00a0€'}
var own=0;function fix(){if(own||!tot||!qf||!pe)return;var q=parseInt(qf.value,10)||1,u=num(pe.textContent),shown=num(tot.textContent),add=Math.max(0,Math.round((shown-u*q)*100)/100),fr=Math.floor(q/3);
 own=1;tot.textContent=fm(u*(q-fr)+add);if(sav){if(fr>0){sav.style.display='';sav.textContent='🎃 '+fr+'× gratis – du sparst '+fm(u*fr);}else{sav.style.display='none';}}setTimeout(function(){own=0},0);}
if(C.pp&&tot){new MutationObserver(fix).observe(tot,{childList:true,characterData:true,subtree:true});fix();}
/* Buybox-Hinweis */
if(C.pp){var bb=document.getElementById('rx-buybox');if(bb&&!document.getElementById('sp-hw-bb')){bb.insertAdjacentHTML('afterbegin','<div id="sp-hw-bb"><b>🎃 HALLOWEEN-SPECIAL</b>Jede 3. Flasche gratis – leg 3 Flaschen in den Warenkorb, die günstigste zahlst du nicht. Auch gemischt mit anderen Peptiden.</div>');}}
/* Warenkorb-Hinweis */
var busy=0;function cart(){if(busy)return;busy=setTimeout(function(){busy=0;fetch('/wp-json/wc/store/v1/cart',{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(c){
 var n=0;(c.items||[]).forEach(function(it){if(ids[it.id]&&parseInt(it.prices&&it.prices.regular_price||0,10)>0&&!(it.item_data||[]).some(function(d){return /Geschenk/i.test(d.name||d.key||'')}))n+=it.quantity;});
 var box=document.getElementById('sp-hw-cd');var sc=document.getElementById('sp-cd-scroll')||document.getElementById('sp-cd-items');if(!sc)return;
 if(!box){box=document.createElement('div');box.id='sp-hw-cd';sc.insertAdjacentElement('afterbegin',box);}
 var r=n%3,f=Math.floor(n/3),need=3-r;
 var txt=n===0?'Jede 3. Flasche ist gratis – leg 3 in den Warenkorb.':(r===0?'✓ '+f+' Flasche'+(f>1?'n':'')+' gratis! Noch 3 für die nächste.':'Noch '+need+' Flasche'+(need>1?'n':'')+', dann ist eine gratis.'+(f?' ('+f+' schon gratis)':''));
 box.innerHTML='<span style="font-size:22px">🎃</span><div style="flex:1">'+txt+'<div class="bar"><i style="width:'+(r/3*100)+'%"></i></div></div>';
 });},400);}
var items=document.getElementById('sp-cd-items');if(items){new MutationObserver(cart).observe(items,{childList:true,subtree:true});}
if(window.jQuery)jQuery(document.body).on('added_to_cart removed_from_cart',cart);
document.addEventListener('click',function(e){if(e.target.closest('#sp-cart-fab'))cart();});
})();
</script>
    <?php
}, 120);

/* ---------------------------------------------------------------------
 * Admin
 * ------------------------------------------------------------------- */
add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Halloween', '🎃 Halloween', 'manage_woocommerce', 'sp-halloween', 'sp_hw_render_page');
}, 60);

function sp_hw_render_page() {
    if (!current_user_can('manage_woocommerce')) return;
    $tz = sp_hw_tz();
    if (!empty($_POST['sp_hw_save']) && check_admin_referer('sp_hw')) {
        $o = sp_hw_opt();
        $o['on'] = empty($_POST['on']) ? 0 : 1;
        $o['deko'] = empty($_POST['deko']) ? 0 : 1;
        foreach (['early', 'start', 'end'] as $k) {
            $v = sanitize_text_field(wp_unslash($_POST[$k] ?? ''));
            try { if ($v !== '') $o[$k] = (new DateTime(str_replace('T', ' ', $v), $tz))->getTimestamp(); } catch (Exception $e) {}
        }
        $o['bonus'] = max(0, min(20, (float) str_replace(',', '.', (string) ($_POST['bonus'] ?? 5))));
        update_option('sp_hw', $o, false);
        delete_transient('sp_hw_ids_v1');
        $log = (array) get_option('sp_hw_log', []);
        $log[] = [time(), get_current_user_id(), $o['on'] ? 'an' : 'aus'];
        update_option('sp_hw_log', array_slice($log, -50), false);
        echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
    }
    $o = sp_hw_opt();
    $now = time();
    if (empty($o['on'])) { $phase = '<span style="background:#757575;color:#fff;padding:3px 10px;border-radius:99px">AUS</span> Niemand sieht etwas, die Preise sind normal.'; }
    elseif ($now >= $o['end']) { $phase = '<span style="background:#757575;color:#fff;padding:3px 10px;border-radius:99px">BEENDET</span> Die Aktion ist vorbei, der Shop läuft normal.'; }
    elseif ($now >= $o['start']) { $phase = '<span style="background:#E65100;color:#fff;padding:3px 10px;border-radius:99px">LÄUFT</span> Für alle aktiv.'; }
    elseif ($now >= $o['early']) { $phase = '<span style="background:#6A1B9A;color:#fff;padding:3px 10px;border-radius:99px">EARLY ACCESS</span> Aktiv für alle, die über den Newsletter-Link kommen. Alle anderen sehen den Teaser.'; }
    else { $phase = '<span style="background:#1565C0;color:#fff;padding:3px 10px;border-radius:99px">TEASER</span> Banner kündigt die Aktion an, Preise noch normal.'; }
    $f = function ($ts) use ($tz) { return wp_date('Y-m-d\TH:i', (int) $ts, $tz); };
    $ea = add_query_arg(['sp_hw' => 'frueh', 'k' => $o['ea']], home_url('/'));
    $pv = add_query_arg(['sp_hw' => 'vorschau', 'k' => $o['pv']], home_url('/'));
    global $wpdb;
    $ids = sp_hw_eligible_ids();
    $names = [];
    foreach ($ids as $id) { $p = wc_get_product($id); if ($p && !$p->is_type('variable')) $names[] = esc_html($p->get_name()); }
    $orders = wc_get_orders(['limit' => -1, 'meta_key' => '_sp_hw', 'meta_compare' => 'EXISTS', 'status' => ['processing', 'completed', 'on-hold']]);
    $cnt = 0; $sum = 0; $free = 0;
    foreach ($orders as $ord) { if ($ord->get_meta('_sp_is_test')) continue; $cnt++; $sum += (float) $ord->get_total(); $free += (int) $ord->get_meta('_sp_hw'); }
    echo '<div class="wrap" style="max-width:860px"><h1>🎃 Halloween-Modus</h1>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px;margin:16px 0"><p style="font-size:15px;margin:0">' . $phase . '</p></div>';
    echo '<form method="post" style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px">';
    wp_nonce_field('sp_hw');
    echo '<p><label style="font-size:15px"><input type="checkbox" name="on" value="1" ' . checked($o['on'], 1, false) . '> <b>Halloween-Modus an</b> (Hauptschalter – aus = alles sofort normal)</label></p>';
    echo '<p><label><input type="checkbox" name="deko" value="1" ' . checked($o['deko'], 1, false) . '> Halloween-Look im Shop (Banner, Fledermäuse, Streifen, Badges)</label></p>';
    echo '<table class="form-table"><tr><th>Early Access (Newsletter)</th><td><input type="datetime-local" name="early" value="' . esc_attr($f($o['early'])) . '"></td></tr>';
    echo '<tr><th>Start für alle</th><td><input type="datetime-local" name="start" value="' . esc_attr($f($o['start'])) . '"></td></tr>';
    echo '<tr><th>Ende</th><td><input type="datetime-local" name="end" value="' . esc_attr($f($o['end'])) . '"></td></tr>';
    echo '<tr><th>Partner-Bonus</th><td><input type="text" name="bonus" value="' . esc_attr($o['bonus']) . '" style="width:60px"> Prozentpunkte zusätzlich (z. B. 20 % → 25 %)</td></tr></table>';
    echo '<p><button class="button button-primary button-hero" name="sp_hw_save" value="1">Speichern</button></p></form>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px;margin:16px 0"><h2 style="margin-top:0">Links</h2>';
    echo '<p><b>Early-Access-Link für die Newsletter-Mail:</b><br><code style="user-select:all">' . esc_html($ea) . '</code></p>';
    echo '<p><b>Vorschau (nur für dich, 24 h, zeigt alles wie live – auch wenn der Schalter aus ist):</b><br><a href="' . esc_url($pv) . '" target="_blank">' . esc_html($pv) . '</a><br><a href="' . esc_url(home_url('/?sp_hw=aus')) . '" target="_blank">Vorschau beenden</a></p></div>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px;margin:16px 0"><h2 style="margin-top:0">Was zählt („jede 3. gratis“)</h2><p>' . ($names ? implode(' · ', $names) : '– keine vorrätigen Flaschen –') . '</p><p style="color:#646970">Nur vorrätige Peptid-Flaschen. Nicht: Pens, Zubehör, Vorbestellungen, Abo, Geschenke. Während der Aktion: keine Rabattcodes, kein 10-%-Partnerlink-Rabatt, kein Mengenrabatt auf diese Flaschen. Partner-Codes ordnen weiter zu (0 € Rabatt).</p></div>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px;margin:16px 0"><h2 style="margin-top:0">Ergebnis</h2><p>' . (int) $cnt . ' Bestellungen · ' . wc_price($sum) . ' · ' . (int) $free . ' Flaschen gratis</p></div></div>';
}
