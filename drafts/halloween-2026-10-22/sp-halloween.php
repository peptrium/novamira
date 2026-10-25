<?php
/**
 * Peptrium – Halloween-Modus (2026-10-22)
 *
 * Ein Schalter fuer alles: Peptrium Dashboard -> Halloween (Option sp_hw).
 *  - Angebot "Jede 3. Flasche gratis – die guenstigste": vorraetige Peptid-Flaschen
 *    + Vorbestellungen (seit 2026-10-24, User; behalten ihren -15-%-Vorbestellpreis als Basis)
 *    (keine Pens, kein Zubehoer, keine Abo-/Geschenk-Zeilen),
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
        'wenig' => 1,
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
    if (sp_hw_is_preview()) return $s = (($_COOKIE['sp_hw_pvs'] ?? '') === 'teaser' ? 'teaser' : 'live');
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

/* Automatisch einschalten (Option sp_hw['auto_on'] = Zeitstempel): beim ersten nicht gecachten
 * Aufruf danach wird 'on' gesetzt – update_option leert dabei auch den Seiten-Cache. */
add_action('init', function () {
    $o = get_option('sp_hw', []);
    if (!is_array($o) || empty($o['auto_on']) || !empty($o['on']) || time() < (int) $o['auto_on']) return;
    $o['on'] = 1;
    $o['auto_on'] = 0;
    update_option('sp_hw', $o, false);
    delete_transient('sp_hw_ids_v1');
    $log = (array) get_option('sp_hw_log', []);
    $log[] = [time(), 0, 'an (automatisch)'];
    update_option('sp_hw_log', array_slice($log, -50), false);
}, 1);

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
    if ($ok && preg_match('/\bpen\b/i', (string) get_the_title($pid))) $ok = false; // auch neue Peptrium-Pens
    if ($ok && get_post_status($pid) !== 'publish') $ok = false;
    return $cache[$pid] = $ok;
}

function sp_hw_product_eligible($product) {
    if (!$product) return false;
    $pid = $product->get_parent_id() ?: $product->get_id();
    if (!sp_hw_parent_eligible($pid)) return false;
    // Sale-Preise nur bei Vorbestellungen (-15 %); andere reduzierte Produkte zaehlen nicht
    if ($product->is_on_sale('edit') && !sp_hw_is_preorder($pid)) return false;
    return (float) $product->get_regular_price('edit') > 0;
}

function sp_hw_is_preorder($pid) {
    return function_exists('sp_preorder_product_ids') && in_array((int) $pid, array_map('intval', (array) sp_preorder_product_ids()), true);
}

/** Basispreis einer Aktionsflasche: regulaer, bei Vorbestellungen der Vorbestellpreis (nie teurer als angezeigt). */
function sp_hw_base_price($product) {
    $reg = (float) $product->get_regular_price('edit');
    if ($product->is_on_sale('edit')) {
        $sale = (float) $product->get_sale_price('edit');
        if ($sale > 0 && $sale < $reg) return $sale;
    }
    return $reg;
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
        $reg = sp_hw_base_price($p);
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
        $reg = sp_hw_base_price($item['data']);
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
 * Codes & Empfehlungs-Rabatt waehrend der Aktion (User 2026-10-22):
 * gelten NICHT auf Aktionsflaschen (dort gibt es die Gratis-Flasche), aber weiter
 * auf Pens, Zubehoer usw. Partner-Codes bleiben immer gueltig (Zuordnung).
 * ------------------------------------------------------------------- */
function sp_hw_is_partner_coupon($coupon) {
    $id = is_object($coupon) ? $coupon->get_id() : wc_get_coupon_id_by_code((string) $coupon);
    return $id && get_post_meta($id, '_sp_affiliate_id', true);
}

/** Cart-Zeile zaehlt zur Aktion (keine Geschenk-/Abo-Zeile, Produkt geeignet). */
function sp_hw_item_in_action($item) {
    if (defined('SP_GIFT_ITEM_META') && !empty($item[SP_GIFT_ITEM_META])) return false;
    if (!empty($item['sp_abo_interval_days'])) return false;
    return !empty($item['data']) && sp_hw_product_eligible($item['data']);
}

/** Summe der Positionen, auf die Codes/Partnerrabatt weiter wirken. */
function sp_hw_other_subtotal($cart) {
    $sum = 0.0;
    foreach ($cart->get_cart() as $item) {
        if (defined('SP_GIFT_ITEM_META') && !empty($item[SP_GIFT_ITEM_META])) continue;
        if (sp_hw_item_in_action($item)) continue;
        $sum += (float) $item['data']->get_price() * (int) $item['quantity'];
    }
    return $sum;
}

add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    if (!$valid || !sp_hw_active() || sp_hw_is_partner_coupon($coupon) || !WC()->cart) return $valid;
    return sp_hw_other_subtotal(WC()->cart) > 0 ? $valid : false;
}, 50, 2);

add_filter('woocommerce_coupon_error', function ($err, $err_code, $coupon) {
    if ((int) $err_code === WC_Coupon::E_WC_COUPON_INVALID_FILTERED && sp_hw_active() && $coupon && !sp_hw_is_partner_coupon($coupon)) {
        return 'Auf die Halloween-Flaschen gibt es schon die Gratis-Flasche – Rabattcodes gelten in der Zeit nur für Pens und Zubehör. 🎃';
    }
    return $err;
}, 50, 3);

add_filter('woocommerce_coupon_get_discount_amount', function ($amount, $discounting, $cart_item, $single, $coupon) {
    return (sp_hw_active() && is_array($cart_item) && sp_hw_item_in_action($cart_item)) ? 0 : $amount;
}, 50, 5);

add_filter('woocommerce_coupon_message', function ($msg, $code, $coupon) {
    if ($code === WC_Coupon::WC_COUPON_SUCCESS && sp_hw_active() && $coupon && sp_hw_is_partner_coupon($coupon)) {
        return 'Partner-Code erkannt ✓ – auf die Halloween-Flaschen gilt die Gratis-Flasche, auf Pens & Zubehör dein Rabatt.';
    }
    return $msg;
}, 50, 3);

/* Empfehlungs-Rabatt (sp-affiliate-program.php, Prio 20) nur noch auf die Nicht-Aktions-Positionen. */
add_action('woocommerce_cart_calculate_fees', function ($cart) {
    if (!sp_hw_active()) return;
    $fees = $cart->fees_api()->get_fees();
    $changed = false;
    foreach ($fees as $k => $fee) {
        if ((float) $fee->amount >= 0 || strpos((string) $fee->name, 'Empfehlungs-Rabatt') !== 0) continue;
        $pct = defined('SP_AFF_REFERRAL_DISCOUNT_PCT') ? (float) SP_AFF_REFERRAL_DISCOUNT_PCT : 10;
        $new = round(sp_hw_other_subtotal($cart) * $pct / 100, 2);
        if ($new <= 0) { unset($fees[$k]); } else { $fees[$k]->amount = -$new; $fees[$k]->total = -$new; }
        $changed = true;
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
        setcookie('sp_hw_pvs', (isset($_GET['s']) && $_GET['s'] === 'teaser') ? 'teaser' : 'live', ['expires' => time() + DAY_IN_SECONDS, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
        wp_safe_redirect(home_url('/'));
        exit;
    }
    if ($a === 'aus') {
        setcookie('sp_hw_pv', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => true, 'samesite' => 'Lax']);
        wp_safe_redirect(home_url('/'));
        exit;
    }
}, 1);

/* Newsletter bestaetigt (Danke-Seite) in der Teaser-/Early-Phase -> Early Access auf diesem Geraet. */
add_action('init', function () {
    if (($_GET['sp_nl'] ?? '') !== 'danke' || sp_hw_has_ea()) return;
    $o = sp_hw_opt();
    if (empty($o['on']) || time() >= $o['start']) return;
    setcookie('sp_hw_ea', sp_hw_cookie_val('ea'), ['expires' => (int) $o['end'] + 86400, 'path' => '/', 'secure' => true, 'httponly' => false, 'samesite' => 'Lax']);
}, 2);

/* ---------------------------------------------------------------------
 * "Nur noch wenige verfügbar" in der Kaufbox – auf allen Produktseiten,
 * solange die Aktion laeuft (User 2026-10-22: ohne Lagerabgleich, Schalter 'wenig').
 * ------------------------------------------------------------------- */
add_action('wp_footer', function () {
    if (!sp_hw_active() || !function_exists('is_product') || !is_product()) return;
    $o = sp_hw_opt();
    if (empty($o['wenig'])) return;
    if (in_array((int) get_queried_object_id(), [729, 745, 817], true)) return;
    if (sp_hw_is_preorder(get_queried_object_id())) return; // Vorbestellung: nicht "wenige verfügbar"
    ?>
<style>.rx-stock.sp-hw-low{color:#B45309!important;font-weight:700!important}.rx-stock.sp-hw-low .rx-stock-dot{background:#F59E0B!important;box-shadow:0 0 0 3px rgba(245,158,11,.25)!important}</style>
<script>(function(){var st=document.querySelector('#rx-buybox .rx-stock');if(!st)return;var dot=st.querySelector('.rx-stock-dot');st.classList.add('sp-hw-low');st.innerHTML=(dot?dot.outerHTML:'')+' Nur noch wenige verfügbar';})();</script>
    <?php
}, 125);

/* ---------------------------------------------------------------------
 * Kasse: Zeile "Du sparst X € mit dem Halloween-Angebot"
 * ------------------------------------------------------------------- */
add_action('wp_footer', function () {
    if (!sp_hw_active() || !function_exists('is_checkout') || !is_checkout() || is_order_received_page()) return;
    ?>
<style>#sp-hw-co{display:flex;justify-content:space-between;align-items:center;gap:10px;margin:8px 16px;padding:11px 14px;border-radius:12px;background:linear-gradient(135deg,#140A02,#2A1306);border:1px solid rgba(255,122,26,.45);color:#FFE7CC;font:600 14px/1.35 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif}#sp-hw-co b{color:#FFB547;font-size:15px;white-space:nowrap}#sp-hw-co .cdl{font-size:12px;color:#E8C9A8;margin-top:3px;font-variant-numeric:tabular-nums}#sp-hw-co .cdl span{color:#fff;font-weight:700}</style>
<script>(function(){var t=0;function run(){if(t)return;t=setTimeout(function(){t=0;if(!window.wp||!wp.data)return;var st=wp.data.select('wc/store/cart');if(!st)return;var c=st.getCartData();if(!c||!c.items)return;
 var save=0,free=0;c.items.forEach(function(it){var hw=(it.item_data||[]).filter(function(d){return /Halloween/.test(d.name||d.key||'')})[0];if(!hw)return;var tt=it.totals||{};save+=(parseInt(tt.line_subtotal,10)-parseInt(tt.line_total,10))||0;free+=parseInt(String(hw.value||'').replace(/[^0-9]/g,''),10)||0;});
 var minor=(c.totals&&c.totals.currency_minor_unit)||2,box=document.getElementById('sp-hw-co');
 var anchor=document.querySelector('.wc-block-components-totals-footer-item');
 if(save<=0||!anchor){if(box)box.remove();return;}
 if(!box){box=document.createElement('div');box.id='sp-hw-co';}
 if(box.nextElementSibling!==anchor)anchor.parentNode.insertBefore(box,anchor);
 var v=(save/Math.pow(10,minor)).toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2})+'\u00a0€';
 box.innerHTML='<div><span>🎃 Halloween: '+free+' Flasche'+(free>1?'n':'')+' gratis</span><div class="cdl">⏳ Endet in <span id="sp-hw-co-cd">'+cdt()+'</span></div></div><b>−'+v+'</b>';},300);}
var E=<?php echo (int) sp_hw_opt()['end'] * 1000; ?>;function p2(n){return (n<10?'0':'')+n}
function cdt(){var s=Math.max(0,Math.floor((E-Date.now())/1000)),d=Math.floor(s/86400);return (d?d+' T · ':'')+p2(Math.floor(s%86400/3600))+':'+p2(Math.floor(s%3600/60))+':'+p2(s%60);}
setInterval(function(){var x=document.getElementById('sp-hw-co-cd');if(x)x.textContent=cdt();},1000);
var iv=setInterval(function(){if(window.wp&&wp.data){clearInterval(iv);wp.data.subscribe(run);run();}},300);})();</script>
    <?php
}, 130);

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
        return $html;
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
#sp-top-banner.sp-hw{background:linear-gradient(90deg,#1A0D04,#2A1306 50%,#1A0D04)!important;color:#FFD9B0!important;overflow:hidden}
#sp-top-banner img.emoji,[class*="sp-hw"] img.emoji,[id^="sp-hw"] img.emoji{position:static!important;display:inline-block!important;width:1.15em!important;height:1.15em!important;margin:0 .1em 0 0!important;vertical-align:-.2em!important;top:auto!important;left:auto!important;transform:none!important}
#sp-top-banner.sp-hw .sp-tb-mask{display:none!important}
#sp-hw-tb{display:flex;align-items:center;justify-content:center;gap:8px;height:100%;min-height:inherit;font:700 12.5px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;letter-spacing:.02em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none;color:#FFD9B0!important;padding:0 12px;position:relative;z-index:2}
#sp-hw-tb b{color:var(--hw-or2)}
#sp-hw-tb .cd{font-variant-numeric:tabular-nums;background:rgba(255,122,26,.16);border:1px solid rgba(255,122,26,.35);border-radius:6px;padding:2px 6px;color:#FFE7CC}
.sp-hw-bat{position:absolute;top:0;width:22px;height:11px;fill:#FFB547;opacity:.22;z-index:1;animation:sphwfly 14s linear infinite}
.sp-hw-bat.b2{width:16px;height:8px;animation-duration:19s;animation-delay:-7s;top:14px}
.sp-hw-bat.b3{width:12px;height:6px;animation-duration:23s;animation-delay:-13s;top:4px}
@keyframes sphwfly{0%{left:-30px;transform:translateY(6px)}25%{transform:translateY(0)}50%{transform:translateY(8px)}75%{transform:translateY(2px)}100%{left:calc(100% + 30px);transform:translateY(6px)}}
@media (max-width:420px){#sp-hw-tb{font-size:11.5px;gap:6px}#sp-hw-tb .lg{display:none}}
@media (max-width:359px){#sp-hw-tb{font-size:10.5px;letter-spacing:0}}
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
@media (max-width:600px){.sp-hw-strip .pk,.sp-hw-strip.mini .pk{font-size:38px!important;top:18px!important;bottom:auto!important;right:10px!important;opacity:.9!important}.sp-hw-strip .ey{margin-right:50px}}
/* Karten-Badge */
.sp-pc{position:relative}
@media (max-width:600px){.sp-hw-badge .t{display:none}.sp-hw-badge{padding:5px 6px!important;font-size:13px!important}}
.sp-hw-badge{position:absolute;top:10px;right:10px;z-index:3;background:#140A02;color:var(--hw-or2);border:1px solid rgba(255,122,26,.5);font:800 11px/1 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;border-radius:99px;padding:6px 9px;letter-spacing:.02em;pointer-events:none}
/* Buybox */
#sp-hw-bb{background:linear-gradient(135deg,#140A02,#2A1306);color:#FFE7CC;border:1px solid rgba(255,122,26,.45);border-radius:14px;padding:12px 14px;margin:0 0 14px;font:14px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif}
#sp-hw-bb b{color:var(--hw-or2);display:block;font-size:13px;letter-spacing:.08em;margin-bottom:2px}
#rx-tiers .rx-tier[data-q="3"] .rx-tier__disc,#rx-tiers .rx-tier[data-q="5"] .rx-tier__disc,#rx-tiers .rx-tier[data-q="10"] .rx-tier__disc{color:#B94A00!important}
/* Warenkorb */
#sp-hw-bb>b{font-size:14.5px!important;letter-spacing:.01em!important;color:#fff!important;margin:0!important}#sp-hw-bb .ex{margin-top:4px!important}
#sp-hw-bt{display:flex;gap:10px;align-items:center;background:linear-gradient(135deg,#140A02,#2A1306);border:1px solid rgba(255,122,26,.45);border-radius:14px;padding:11px 14px;margin:0 0 14px;color:#FFE7CC!important;text-decoration:none!important;font:600 14px/1.35 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif}#sp-hw-bt>span{flex:1}#sp-hw-bt b{color:var(--hw-or2)}#sp-hw-bt small{font-size:12.5px;color:#E8C9A8}
.sp-hw-btn.sm{padding:10px 14px;font-size:13.5px;margin-top:2px}
#sp-hw-bb .ex{margin-top:8px;font-size:15px;color:#fff}#sp-hw-bb .ex s{color:#A89888;margin-right:4px}#sp-hw-bb .ex strong{color:var(--hw-or2);font-size:17px}
#sp-hw-bb .cdl,#sp-hw-sum .cdl{margin-top:6px;font-size:12.5px;color:#E8C9A8;font-variant-numeric:tabular-nums}#sp-hw-bb .cdl span,#sp-hw-sum .cdl span{color:#fff;font-weight:700}
#sp-hw-bb .t3{margin-top:10px;width:100%;border:0;border-radius:10px;padding:11px 12px;background:linear-gradient(90deg,var(--hw-or),var(--hw-or2))!important;color:#140A02!important;font:800 14.5px/1 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;cursor:pointer;box-shadow:none!important}
#sp-hw-cd .add{display:block;margin-top:8px;width:100%;border:0;border-radius:9px;padding:9px 10px;background:linear-gradient(90deg,var(--hw-or),var(--hw-or2))!important;color:#140A02!important;font:800 13.5px/1.2 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;cursor:pointer;box-shadow:none!important;text-align:center}
#sp-hw-cd .add[disabled]{opacity:.6}
#sp-hw-sum{margin:6px 0 10px;background:linear-gradient(135deg,#140A02,#2A1306);color:#FFE7CC;border:1px solid rgba(255,122,26,.45);border-radius:12px;padding:10px 12px;font:600 13.5px/1.35 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif}
#sp-hw-sum .row{display:flex;justify-content:space-between;gap:8px;align-items:center}#sp-hw-sum .row b{color:var(--hw-or2);font-size:16px;white-space:nowrap}
#sp-hw-cd{margin:10px 14px 4px;background:#140A02;color:#FFE7CC;border:1px solid rgba(255,122,26,.45);border-radius:12px;padding:10px 12px;font:600 13.5px/1.4 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;display:flex;gap:10px;align-items:center}
#sp-hw-cd .bar{flex:1;height:6px;border-radius:6px;background:rgba(255,255,255,.12);overflow:hidden;margin-top:6px}
#sp-hw-cd .bar i{display:block;height:100%;background:linear-gradient(90deg,var(--hw-or),var(--hw-or2));border-radius:6px;transition:width .3s}
@media (prefers-reduced-motion:reduce){.sp-hw-bat{display:none}}
#sp-hw-party{position:absolute;inset:0;z-index:99;display:flex;align-items:center;justify-content:center;background:rgba(10,6,3,.55);-webkit-backdrop-filter:blur(5px);backdrop-filter:blur(5px);animation:sphwbg .3s ease both;cursor:pointer}
#sp-hw-party.out{animation:sphwout .35s ease forwards}
@keyframes sphwbg{from{opacity:0}to{opacity:1}}@keyframes sphwout{to{opacity:0}}
#sp-hw-party .cd{position:relative;width:min(300px,84%);padding:26px 20px 22px;border-radius:22px;text-align:center;color:#fff;background:radial-gradient(90% 60% at 50% 18%,rgba(255,122,26,.35),transparent 70%),linear-gradient(160deg,#1E0F05,#0C0704);border:1px solid rgba(255,140,40,.45);box-shadow:0 30px 80px rgba(0,0,0,.55);animation:sphwcd .55s cubic-bezier(.2,1.4,.4,1) both}
@keyframes sphwcd{from{opacity:0;transform:translateY(24px) scale(.85)}to{opacity:1;transform:none}}
#sp-hw-party .vs{display:flex;justify-content:center;align-items:flex-end;gap:8px;height:150px;margin-bottom:14px}
#sp-hw-party .v{position:relative;width:40px;display:block;opacity:0}
#sp-hw-party .v svg{width:40px;height:72px;display:block;overflow:visible}
#sp-hw-party .cp{fill:#C9CDD2}#sp-hw-party .nk{fill:#8B9097}#sp-hw-party .bd{fill:rgba(255,255,255,.14);stroke:rgba(255,255,255,.45);stroke-width:1.5}#sp-hw-party .lb{fill:#E9ECEF}
#sp-hw-party .v1{animation:sphwv .35s .15s cubic-bezier(.2,1.4,.4,1) forwards}
#sp-hw-party .v2{animation:sphwv .35s .32s cubic-bezier(.2,1.4,.4,1) forwards}
#sp-hw-party .v3{width:50px;animation:sphwv3 .6s .6s cubic-bezier(.3,1.5,.5,1) forwards}
#sp-hw-party .v3 svg,#sp-hw-party .v3 img{width:50px;height:90px;animation:sphwgl 1.6s .95s ease-out forwards}
#sp-hw-party .v img{height:100px;width:auto;display:block;margin:0 auto;-webkit-user-drag:none}
#sp-hw-party .v1 img,#sp-hw-party .v2 img{filter:brightness(.74) saturate(.85)}
#sp-hw-party .v3 img{width:auto;height:146px;position:relative;z-index:1}
#sp-hw-party .v3 .gl{position:absolute;left:50%;bottom:0;width:150px;height:150px;transform:translateX(-50%) scale(.3);border-radius:50%;background:radial-gradient(circle,rgba(255,140,40,.75) 0%,rgba(255,110,20,.25) 45%,transparent 70%);opacity:0;animation:sphwglo 1.4s .9s ease-out forwards;z-index:0}
@keyframes sphwglo{0%{opacity:0;transform:translateX(-50%) scale(.3)}35%{opacity:1;transform:translateX(-50%) scale(1.15)}100%{opacity:.8;transform:translateX(-50%) scale(1)}}
#sp-hw-party .v1,#sp-hw-party .v2{width:auto}
#sp-hw-party .v3{width:auto}
#sp-hw-party .v3 .lb{fill:#FF8A2A}#sp-hw-party .v3 .bd{stroke:#FFB547;fill:rgba(255,140,40,.22)}#sp-hw-party .v3 .cp{fill:#FFB547}
@keyframes sphwv{from{opacity:0;transform:translateY(10px) scale(.7)}to{opacity:.75;transform:none}}
@keyframes sphwv3{0%{opacity:0;transform:translateY(-70px) rotate(-14deg)}60%{opacity:1;transform:translateY(4px) rotate(3deg)}80%{transform:translateY(-3px) rotate(-1deg)}100%{opacity:1;transform:none}}
@keyframes sphwgl{0%{filter:drop-shadow(0 0 0 rgba(255,122,26,0))}30%{filter:drop-shadow(0 0 18px rgba(255,122,26,.95))}100%{filter:drop-shadow(0 0 10px rgba(255,122,26,.6))}}
#sp-hw-party .v3 em{position:absolute;z-index:2;left:88%;top:16%;transform:translate(-50%,-50%) rotate(14deg) scale(2.4);opacity:0;font:900 10.5px/1 Sora,-apple-system,sans-serif;font-style:normal;letter-spacing:.06em;color:#140A02;background:#fff;border-radius:5px;padding:4px 5px;box-shadow:0 4px 12px rgba(0,0,0,.4);animation:sphwstp .35s 1.05s cubic-bezier(.3,1.6,.5,1) forwards}
@keyframes sphwstp{to{opacity:1;transform:translate(-50%,-50%) rotate(14deg) scale(1)}}
#sp-hw-party .sk{position:absolute;z-index:2;left:50%;top:40%;width:0;height:0}
#sp-hw-party .sk i{position:absolute;left:-3px;top:-3px;width:6px;height:6px;border-radius:50%;background:#FFB547;box-shadow:0 0 8px 2px rgba(255,122,26,.9);opacity:0;animation:sphwsp .7s ease-out forwards}
#sp-hw-party .sk i:nth-child(3n){background:#fff;width:4px;height:4px}
@keyframes sphwsp{0%{opacity:1;transform:rotate(var(--a)) translateX(0) scale(1)}100%{opacity:0;transform:rotate(var(--a)) translateX(var(--d)) scale(.3)}}
#sp-hw-party .ey{font:800 10.5px/1 Sora,-apple-system,sans-serif;letter-spacing:.2em;color:#FFB547;opacity:0;animation:sphwtx .4s 1.1s ease forwards}
#sp-hw-party .h{margin-top:8px;font:800 23px/1.15 Sora,-apple-system,sans-serif;letter-spacing:-.01em;opacity:0;animation:sphwtx .45s 1.18s ease forwards}
#sp-hw-party .sv{margin-top:12px;display:inline-block;font:600 14px/1 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;color:#FFE7CC;background:rgba(255,122,26,.14);border:1px solid rgba(255,140,40,.4);border-radius:99px;padding:9px 14px;opacity:0;animation:sphwtx .45s 1.25s ease forwards}
#sp-hw-party .sv strong{color:#FFB547;font-weight:800;font-variant-numeric:tabular-nums}
@keyframes sphwtx{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
#sp-hw-party .bt{position:absolute;top:40%;left:50%;width:30px;fill:#0A0604;stroke:rgba(255,160,60,.55);stroke-width:.4;opacity:0;animation:sphwbt 1.6s 1s ease-out forwards}
#sp-hw-party .bt.b1{--x:-170px;--y:-150px}#sp-hw-party .bt.b2{--x:160px;--y:-130px;width:24px;animation-delay:1.1s}#sp-hw-party .bt.b3{--x:150px;--y:120px;width:20px;animation-delay:1.2s}
@keyframes sphwbt{0%{opacity:0;transform:translate(-50%,0) scale(.4)}15%{opacity:1}100%{opacity:0;transform:translate(calc(-50% + var(--x)),var(--y)) scale(1.1) rotate(10deg)}}
@media (prefers-reduced-motion:reduce){#sp-hw-party,#sp-hw-party *{animation-duration:.01s!important;animation-delay:0s!important}#sp-hw-party .sk,#sp-hw-party .bt{display:none}}
#sp-cat .hero.sp-hw-hero{position:relative;overflow:hidden;background:radial-gradient(70% 90% at 85% 100%,rgba(255,110,20,.28),transparent 65%),radial-gradient(60% 70% at 0% 0%,rgba(110,30,150,.28),transparent 70%),linear-gradient(160deg,#0D0F12 0%,#1E1612 100%)!important}
#sp-cat .hero.sp-hw-hero>*{position:relative;z-index:1}
#sp-cat .hero .sp-hw-web{position:absolute!important;z-index:0!important;right:0;top:0;width:120px;height:120px;opacity:.2;transform:scaleX(-1);pointer-events:none}
#sp-cat .hero .sp-hw-hb{position:absolute!important;z-index:0!important;fill:#0A0604;opacity:.9;filter:drop-shadow(0 0 6px rgba(255,140,40,.45));animation:sphwhb 18s linear infinite;pointer-events:none}
#sp-cat .hero .sp-hw-hb.a{width:34px;top:18%}#sp-cat .hero .sp-hw-hb.b{width:22px;top:55%;animation-duration:24s;animation-delay:-10s}
.sp-hw-stamp{position:absolute;z-index:3;bottom:14px;right:14px;width:74px;height:74px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#FFB547,#FF7A1A 60%,#C2410C);color:#140A02;display:flex;flex-direction:column;align-items:center;justify-content:center;transform:rotate(12deg);box-shadow:0 8px 24px rgba(255,122,26,.45),inset 0 0 0 3px rgba(255,255,255,.35);pointer-events:none;animation:sphwst 3.5s ease-in-out infinite}
.sp-hw-stamp span{font-size:20px;line-height:1}.sp-hw-stamp b{font:800 13px/1.1 Sora,-apple-system,sans-serif;margin-top:2px;white-space:nowrap}
@keyframes sphwst{0%,100%{transform:rotate(12deg) scale(1)}50%{transform:rotate(8deg) scale(1.05)}}
@media (prefers-reduced-motion:reduce){.sp-hw-stamp,#sp-cat .hero .sp-hw-hb{animation:none}}
#sp-hw-ty{margin:0 0 18px;background:linear-gradient(135deg,#140A02,#2A1306);border:1px solid rgba(255,122,26,.5);border-radius:16px;padding:16px 18px;color:#FFE7CC;font:15px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,sans-serif;display:flex;gap:14px;align-items:center}
#sp-hw-ty .i{font-size:34px;line-height:1}#sp-hw-ty b{color:#fff}#sp-hw-ty strong{color:#FFB547;font-size:17px;white-space:nowrap}#sp-hw-ty small{display:block;color:#C9A98A;font-size:13px;margin-top:2px}
/* Hero-Uebernahme Startseite */
.sp-hw-ey{display:inline-flex;align-items:center;gap:7px;font:800 10.5px/1 Sora,-apple-system,sans-serif;letter-spacing:.18em;color:#FFB547;border:1px solid rgba(255,122,26,.55);background:rgba(20,10,2,.55);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);border-radius:99px;padding:7px 12px;margin:0 0 12px}
.sp-hw-g{background:linear-gradient(90deg,#FF7A1A,#FFB547)!important;-webkit-background-clip:text!important;background-clip:text!important;color:transparent!important;-webkit-text-fill-color:transparent!important}
.sp-hero2-cta.sp-hw-cta,#sp-hero-banner .sp-hb-cta.sp-hw-cta{background:linear-gradient(90deg,#FF7A1A,#FFB547)!important;color:#140A02!important;font-weight:800!important;box-shadow:0 10px 30px rgba(255,122,26,.45)!important}
.sp-hero2-cta-wrap.sp-hw-cw{flex-direction:column;align-items:center;gap:8px;bottom:64px!important}
.sp-hw-hcd{display:flex;gap:6px;justify-content:center}
.sp-hw-hcd span{min-width:46px;text-align:center;background:rgba(20,10,2,.6);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);border:1px solid rgba(255,122,26,.4);border-radius:10px;padding:6px 4px;font:800 16px/1 Sora,-apple-system,sans-serif;color:#fff;font-variant-numeric:tabular-nums}
.sp-hw-hcd small{display:block;font-size:8.5px;letter-spacing:.12em;color:#C9A98A;margin-top:4px;font-weight:700}
.sp-hw-vr{display:inline-flex;align-items:center;gap:6px;margin-top:10px;font:700 12px/1 Sora,-apple-system,sans-serif;color:#FFD9B0;background:rgba(20,10,2,.88);border:1px dashed rgba(255,181,71,.55);border-radius:99px;padding:7px 11px}
.sp-hw-cw .sp-hw-vr{margin:-2px 0 2px;background:none;border:0;padding:0;font-size:11.5px;color:#E8C9A8;text-shadow:0 1px 6px rgba(0,0,0,.9)}.sp-hw-cw .sp-hw-vr i{width:6px;height:6px}.sp-hw-dt{font:700 14px/1.2 Sora,-apple-system,sans-serif;color:#fff;text-align:center;text-shadow:0 1px 8px rgba(0,0,0,.95);margin-bottom:-2px}
.sp-hw-vr i{width:7px;height:7px;border-radius:50%;background:#FF7A1A;box-shadow:0 0 0 0 rgba(255,122,26,.7);animation:sphwvr 1.6s infinite}
@keyframes sphwvr{0%{box-shadow:0 0 0 0 rgba(255,122,26,.7)}70%{box-shadow:0 0 0 8px rgba(255,122,26,0)}100%{box-shadow:0 0 0 0 rgba(255,122,26,0)}}
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
.sp-hero2.sp-hw-m .sp-hero2-img-wrap>img{margin-top:44px!important}
.sp-hero2.sp-hw-m .sp-hero2-img-wrap{background:#000!important}
.sp-hero2.sp-hw-m .sp-hero2-sub{text-shadow:0 1px 8px rgba(0,0,0,.9)}
.sp-hero2-text.sp-hw-t .sp-hero2-sub{text-shadow:0 2px 10px rgba(0,0,0,.9);color:rgba(255,255,255,.88)}
.sp-hero2-img-wrap .sp-hw-hov .pk{display:none}
#sp-hero-banner .sp-hw-hov{z-index:0}#sp-hero-banner .sp-hw-hov .pk{bottom:40px}#sp-hero-banner .sp-hw-hov .pk.l{left:auto;right:42%}
@media (prefers-reduced-motion:reduce){.sp-hw-hov .hb,.sp-hw-hov i{display:none}}

</style>
    <?php
}, 50);

/* Echte Flaschenfotos fuer die Gratis-Animation im Warenkorb (freigestellt, uploads/sp-hw/v-<key>.webp). */
function sp_hw_vial_map($ids) {
    $m = get_transient('sp_hw_vimg_v1');
    if (is_array($m)) return $m;
    $up = wp_upload_dir();
    $over = [556 => 'r20', 1012 => 'g100'];
    $m = [];
    foreach ($ids as $id) {
        $key = $over[$id] ?? 0;
        if (!$key) {
            $p = wc_get_product($id);
            if (!$p) continue;
            $key = $p->get_image_id();
            if (!$key && $p->get_parent_id()) { $pp = wc_get_product($p->get_parent_id()); $key = $pp ? $pp->get_image_id() : 0; }
        }
        if ($key && file_exists($up['basedir'] . '/sp-hw/v-' . $key . '.webp')) $m[$id] = $up['baseurl'] . '/sp-hw/v-' . $key . '.webp';
    }
    set_transient('sp_hw_vimg_v1', $m, 10 * MINUTE_IN_SECONDS);
    return $m;
}

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
        'vimg'   => sp_hw_vial_map($ids),
    ];
    ?>
<script id="sp-hw-js">
(function(){var C=<?php echo wp_json_encode($cfg); ?>;var live=C.state==='live'||C.state==='early';
var DE=['So','Mo','Di','Mi','Do','Fr','Sa'];
function pad(n){return (n<10?'0':'')+n}
function cdt(){var l=left(C.end);return (l.d?l.d+(l.d>1?' Tagen ':' Tag ')+'· ':'')+pad(l.h)+':'+pad(l.m)+':'+pad(l.s);}
function left(t){var s=Math.max(0,Math.floor((t-Date.now())/1000));return {d:Math.floor(s/86400),h:Math.floor(s%86400/3600),m:Math.floor(s%3600/60),s:s%60,t:s}}
var bat='<svg viewBox="0 0 24 12" class="sp-hw-bat XX"><path d="M0 4C3 1 6 1 8 3.5 9 1.5 10.5 1 12 2.6 13.5 1 15 1.5 16 3.5 18 1 21 1 24 4 21.5 4 19.6 5 18.6 7 16.8 6 14.6 6.4 12 9 9.4 6.4 7.2 6 5.4 7 4.4 5 2.5 4 0 4Z"/></svg>';
/* Top-Banner */
var tb=document.getElementById('sp-top-banner');
if(tb){tb.classList.add('sp-hw');var a=document.createElement('a');a.id='sp-hw-tb';a.href='/alle-produkte/';
 tb.insertAdjacentHTML('beforeend',bat.replace('XX','b1')+bat.replace('XX','b2')+bat.replace('XX','b3'));tb.appendChild(a);
 if(live){a.innerHTML='🎃 <b>Halloween:</b> Jede 3. Flasche gratis<span class="lg"> · solange der Vorrat reicht</span>';}
 else{a.href='/#sp-nlh';a.addEventListener('click',function(e){var n=document.getElementById('sp-nlh');if(n){e.preventDefault();n.scrollIntoView({behavior:'smooth'});}});a.innerHTML='🎃 <b>Halloween</b> ab '+C.startT+' · Newsletter früher dabei';}}
/* Streifen Startseite / Kategorie */
function strip(mini){var s=document.createElement('section');s.className='sp-hw-strip'+(mini?' mini':'');
 var web='<svg class="web" viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".8"><path d="M0 0L100 100M0 0L50 100M0 0L100 50M0 0L0 100M0 0L100 0"/><path d="M0 20Q14 14 20 0M0 40Q28 28 40 0M0 62Q44 44 62 0M0 84Q60 60 84 0"/></svg>';
 if(live){s.innerHTML=web+'<div class="in"><span class="ey">🎃 HALLOWEEN-SPECIAL'+(C.state==='early'?' · EARLY ACCESS':'')+'</span><h2>Jede 3. Flasche <span>gratis.</span></h2>'
  +(mini?'<p>Die günstigste zahlst du nicht. Bis '+C.endT+' Uhr.</p>':'<p>Die günstigste zahlst du nicht – auch gemischt. Bis '+C.endT+' Uhr.</p><div class="sp-hw-cd" id="sp-hw-cd2"></div><a class="sp-hw-btn" href="/alle-produkte/">Flaschen wählen →</a>')+'</div><div class="pk">🎃</div>';}
 else{s.innerHTML=web+'<div class="in"><span class="ey">🎃 BALD: HALLOWEEN-SPECIAL</span><h2>Jede 3. Flasche <span>gratis.</span></h2><p>Ab '+C.startT+' · Insider schon '+C.earlyT+'.</p>'
  +'<a class="sp-hw-btn'+(mini?' sm':'')+'" href="#sp-nlh">Früher dabei sein →</a>'+'</div><div class="pk">🎃</div>';}
 return s;}
if(C.home){var ht=0,hi=setInterval(function(){
 var m=document.querySelector('.sp-hero2'),mob=m&&m.offsetParent!==null;
 if(!mob&&window.matchMedia('(max-width:767px)').matches&&ht<40){ht++;return;}
 var title=mob?m.querySelector('.sp-hero2-title'):document.querySelector('#sp-hero-banner .sp-hb-hero-title');
 if(!title){if(++ht>40){clearInterval(hi);var hero=document.querySelector('.elementor-211>[data-id="shbn001"]');if(hero){var s0=strip(0);s0.style.order='1';hero.insertAdjacentElement('afterend',s0);}}return;}
 clearInterval(hi);
 var sub=mob?m.querySelector('.sp-hero2-sub'):document.querySelector('#sp-hero-banner .sp-hb-hero-subtext');
 var cta=mob?m.querySelector('.sp-hero2-cta'):document.querySelector('#sp-hero-banner .sp-hb-cta');
 var wrap=mob?m.querySelector('.sp-hero2-img-wrap'):document.querySelector('#sp-hero-banner .sp-hb-image-wrap');
 title.insertAdjacentHTML('beforebegin','<div class="sp-hw-ey">🎃 HALLOWEEN-SPECIAL'+(C.state==='early'?' · EARLY ACCESS':'')+'</div>');
 title.innerHTML='Jede 3. Flasche<br><span class="sp-hw-g">gratis.</span>';
 if(mob){m.classList.add('sp-hw-m');var tx=m.querySelector('.sp-hero2-text');if(tx)tx.classList.add('sp-hw-t');}
 if(sub&&mob)sub.style.display='none';
 if(sub)sub.innerHTML=live?(mob?'Die günstigste zahlst du nicht.':'Leg 3 Flaschen in den Warenkorb –<br>die günstigste zahlst du nicht.'):(mob?'Ab '+C.startT+' · Insider schon '+C.earlyT:'Ab '+C.startT+' für alle.<br>Newsletter-Abonnenten schon '+C.earlyT+'.');
 if(cta){cta.classList.add('sp-hw-cta');if(live){cta.firstChild.nodeValue='3 für 2 sichern ';}else{cta.setAttribute('href','#sp-nlh');cta.firstChild.nodeValue='Eintragen & früher dabei ';}
  var cw=cta.parentElement;if(mob&&cw)cw.classList.add('sp-hw-cw');
  var vr='<div class="sp-hw-vr"><i></i>'+(live?'Nur solange der Vorrat reicht':'Begrenzter Vorrat – Insider kaufen zuerst')+'</div>';
  if(live){var hc=document.createElement('div');hc.className='sp-hw-hcd';cta.insertAdjacentElement('beforebegin',hc);
   var t3=function(){var l=left(C.end);hc.innerHTML='<span>'+l.d+'<small>TAGE</small></span><span>'+pad(l.h)+'<small>STD</small></span><span>'+pad(l.m)+'<small>MIN</small></span><span>'+pad(l.s)+'<small>SEK</small></span>';};t3();setInterval(t3,1000);}
  if(mob&&cw){if(live&&hc)hc.insertAdjacentHTML('afterend',vr);else cw.insertAdjacentHTML('afterbegin','<div class="sp-hw-dt">Ab '+C.startT+' · Insider schon '+C.earlyT+'</div>'+vr);}else if(sub)sub.insertAdjacentHTML('afterend',vr);}
 if(wrap){var ov=document.createElement('div');ov.className='sp-hw-hov';var em='';for(var k=0;k<14;k++){em+='<i style="left:'+(5+Math.random()*90).toFixed(1)+'%;animation-delay:-'+(Math.random()*7).toFixed(2)+'s;animation-duration:'+(5+Math.random()*4).toFixed(2)+'s"></i>';}
  ov.innerHTML='<svg class="web" viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".8"><path d="M0 0L100 100M0 0L50 100M0 0L100 50M0 0L0 100M0 0L100 0"/><path d="M0 20Q14 14 20 0M0 40Q28 28 40 0M0 62Q44 44 62 0M0 84Q60 60 84 0"/></svg>'
   +['h1','h2','h3'].map(function(c){return bat.replace('sp-hw-bat XX','hb '+c);}).join('')+em+'<span class="pk l">🎃</span><span class="pk r">🎃</span>';
  wrap.appendChild(ov);}
},120);}
if(C.cat){var tries=0,ci=setInterval(function(){var sc=document.getElementById('sp-cat');if(sc&&sc.firstElementChild){clearInterval(ci);sc.firstElementChild.insertAdjacentElement('afterend',strip(1));}else if(++tries>40){clearInterval(ci);}},150);}
var cd2=document.getElementById('sp-hw-cd2');if(cd2){var t2=function(){var l=left(C.end);cd2.innerHTML='<div><b>'+l.d+'</b><small>TAGE</small></div><div><b>'+pad(l.h)+'</b><small>STD</small></div><div><b>'+pad(l.m)+'</b><small>MIN</small></div><div><b>'+pad(l.s)+'</b><small>SEK</small></div>';};t2();setInterval(t2,1000);}
/* Newsletter-Popup: Text passend zur Phase */
(function(){var n=0,pi=setInterval(function(){var pp=document.getElementById('sp-nlp');if(!pp){if(++n>40)clearInterval(pi);return;}clearInterval(pi);
 var h=pp.querySelectorAll('.st[data-s="1"] h2,.st[data-s="2"] h2'),p1=pp.querySelector('.st[data-s="1"] p');
 if(live){h.forEach(function(e){e.innerHTML='Sichere dir <em>10 % Rabatt</em><br>für deine nächste Bestellung';});if(p1)p1.textContent='Dein Code gilt nach Halloween noch 30 Tage.';}
 else{h.forEach(function(e){e.innerHTML='🎃 Halloween <em>früher</em><br>für Insider';});if(p1)p1.textContent='Jede 3. Flasche gratis – als Insider schon '+C.earlyT+', alle anderen ab '+C.startT+' Plus dein 10-%-Code.';
 var b1=pp.querySelector('.st[data-s="1"] button');if(b1&&!/nein/i.test(b1.textContent))b1.textContent='Ja, früher dabei sein';}
},150);})();
if(!live){
 var nn=0,ni=setInterval(function(){var nl=document.getElementById('sp-nlh');if(!nl){if(++nn>40)clearInterval(ni);return;}clearInterval(ni);
  var ey=nl.querySelector('.ey'),h2=nl.querySelector('h2'),ld=nl.querySelector('.lead');
  if(ey)ey.innerHTML='<i></i>🎃 Halloween-Early-Access';if(h2)h2.innerHTML='Halloween <span>früher</span>.';
  if(ld)ld.textContent='Jede 3. Flasche gratis – als Insider schon '+C.earlyT+'. Plus dein 10-%-Code.';},150);
 if(C.pp){var tb2=document.getElementById('rx-buybox');if(tb2)tb2.insertAdjacentHTML('afterbegin','<a id="sp-hw-bt" href="#sp-nlh">🎃 <span>Ab '+C.startT+': <b>Jede 3. Flasche gratis</b><br><small>Insider schon '+C.earlyT+' – jetzt eintragen →</small></span></a>');}
 return;}
/* Karten-Badges */
var ids={};C.ids.forEach(function(i){ids[i]=1});
function badges(){document.querySelectorAll('.sp-pc[data-id]').forEach(function(c){if(!ids[c.getAttribute('data-id')])return;
 if(!c.querySelector('.sp-hw-badge'))c.insertAdjacentHTML('beforeend','<span class="sp-hw-badge">🎃<span class="t"> 3. gratis</span></span>');
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
if(C.pp){var bb=document.getElementById('rx-buybox');if(bb&&!document.getElementById('sp-hw-bb')){bb.insertAdjacentHTML('afterbegin','<div id="sp-hw-bb"><b>🎃 Jede 3. Flasche gratis</b><div class="ex"></div><div class="cdl">⏳ Noch <span></span></div><button type="button" class="t3">3 Flaschen wählen – 1 gratis</button></div>');
 var hb=document.getElementById('sp-hw-bb'),hx=hb.querySelector('.ex'),hc=hb.querySelector('.cdl span');
 var upd=function(){var pr=document.getElementById('rx-price');var u=pr?num(pr.textContent):0;hx.innerHTML=u>0?'3 Flaschen: <s>'+fm(u*3)+'</s> <strong>'+fm(u*2)+'</strong>':'';hc.textContent=cdt();};
 upd();setInterval(upd,1000);
 hb.querySelector('.t3').addEventListener('click',function(){var t=document.querySelector('#rx-tiers .rx-tier[data-q="3"],.rx-tier[data-q="3"]');if(t)t.click();var a=document.querySelector('.single_add_to_cart_button');if(a){setTimeout(function(){a.scrollIntoView({behavior:'smooth',block:'center'});},150);}});}}
/* Warenkorb-Hinweis */
var addT=0;
function party(sv,f,vs){vs=vs||[];var dr=document.getElementById('sp-cart-drawer')||document.body;var o=dr.querySelector('#sp-hw-party');if(o)o.remove();
 var V='<svg viewBox="0 0 40 72"><rect x="11" y="2" width="18" height="9" rx="2" class="cp"/><rect x="14" y="11" width="12" height="6" class="nk"/><rect x="5" y="17" width="30" height="52" rx="7" class="bd"/><rect x="5" y="34" width="30" height="20" class="lb"/></svg>',sp='';
 for(var k=0;k<18;k++){sp+='<i style="--a:'+(k*20)+'deg;--d:'+(38+Math.random()*34).toFixed(0)+'px;animation-delay:'+(.95+Math.random()*.12).toFixed(2)+'s"></i>';}
 var d=document.createElement('div');d.id='sp-hw-party';d.setAttribute('role','status');
 var vi=function(k){var u=C.vimg&&C.vimg[vs[k]];return u?'<img src="'+u+'" alt="" draggable="false">':V;};
 d.innerHTML='<div class="cd"><div class="vs"><span class="v v1">'+vi(0)+'</span><span class="v v2">'+vi(1)+'</span><span class="v v3"><i class="gl"></i>'+vi(2)+'<em>GRATIS</em><b class="sk">'+sp+'</b></span></div>'
  +'<div class="ey">🎃 HALLOWEEN'+(f>1?' · '+f+'× GRATIS':'')+'</div><div class="h">'+(f>1?'Noch eine Flasche':'Die 3. Flasche')+'<br>geht aufs Haus.</div>'+(sv>0?'<div class="sv">Du sparst <strong>0,00 €</strong></div>':'')
  +bat.replace('sp-hw-bat XX','bt b1')+bat.replace('sp-hw-bat XX','bt b2')+bat.replace('sp-hw-bat XX','bt b3')+'</div>';
 dr.appendChild(d);try{if(navigator.vibrate)navigator.vibrate([20,60,30]);}catch(x){}
 var st=d.querySelector('.sv strong');if(st){var t0=0;setTimeout(function(){var go=function(ts){if(!t0)t0=ts;var q=Math.min(1,(ts-t0)/700);q=1-Math.pow(1-q,3);st.textContent=fm(sv*q);if(q<1)requestAnimationFrame(go);};requestAnimationFrame(go);},1150);}
 var close=function(){if(!d.parentNode)return;d.classList.add('out');setTimeout(function(){d.remove();},350);};d.addEventListener('click',close);setTimeout(close,4200);}
var busy=0;function cart(){if(busy)return;busy=setTimeout(function(){busy=0;fetch('/wp-json/wc/store/v1/cart',{credentials:'same-origin'}).then(function(r){return r.json()}).then(function(c){
 var n=0;(c.items||[]).forEach(function(it){if(ids[it.id]&&parseInt(it.prices&&it.prices.regular_price||0,10)>0&&!(it.item_data||[]).some(function(d){return /Geschenk/i.test(d.name||d.key||'')}))n+=it.quantity;});
 var box=document.getElementById('sp-hw-cd');var sc=document.getElementById('sp-cd-scroll')||document.getElementById('sp-cd-items');if(!sc)return;
 if(!box){box=document.createElement('div');box.id='sp-hw-cd';sc.insertAdjacentElement('afterbegin',box);}
 var r=n%3,f=Math.floor(n/3),need=3-r;var pf=0;try{pf=parseInt(sessionStorage.getItem('sp_hw_f')||'0',10)||0;sessionStorage.setItem('sp_hw_f',f);}catch(x){}var doParty=f>pf&&Date.now()-addT<8000;
 var txt=n===0?'Jede 3. Flasche ist gratis – leg 3 in den Warenkorb.':(r===0?'✓ '+f+' Flasche'+(f>1?'n':'')+' gratis! Noch 3 für die nächste.':'Noch '+need+' Flasche'+(need>1?'n':'')+', dann ist eine gratis.'+(f?' ('+f+' schon gratis)':''));
 var ch=null;(c.items||[]).forEach(function(it){if(!ids[it.id]||(it.item_data||[]).some(function(d){return /Geschenk/i.test(d.name||d.key||'')}))return;var rp=parseInt(it.prices&&it.prices.regular_price||0,10);if(rp>0&&(!ch||rp<ch.rp))ch={id:it.id,rp:rp,name:it.name+((it.variation||[]).length?' '+it.variation.map(function(v){return v.value}).join(' '):'')};});
 var btn=(r===2&&ch)?'<button type="button" class="add" data-id="'+ch.id+'">+ 1× '+ch.name+' dazu – gratis 🎃</button>':'';
 box.innerHTML='<span style="font-size:22px">🎃</span><div style="flex:1">'+txt+'<div class="bar"><i style="width:'+(r/3*100)+'%"></i></div>'+btn+'</div>';
 var save=0;(c.items||[]).forEach(function(it){if(!(it.item_data||[]).some(function(d){return /Halloween/.test(d.name||d.key||'')}))return;var tt=it.totals||{};save+=(parseInt(tt.line_subtotal,10)-parseInt(tt.line_total,10))||0;});
 var minor=(c.totals&&c.totals.currency_minor_unit)||2;if(doParty){var un=[];(c.items||[]).forEach(function(it){if(!ids[it.id]||(it.item_data||[]).some(function(d){return /Geschenk/i.test(d.name||d.key||'')}))return;var rp=parseInt(it.prices&&it.prices.regular_price||0,10);for(var q=0;q<it.quantity;q++)un.push({id:it.id,rp:rp});});un.sort(function(x,y){return y.rp-x.rp;});var fr=ch?ch.id:(un.length?un[un.length-1].id:0);party(save/Math.pow(10,minor),f,[un[0]?un[0].id:fr,un[1]?un[1].id:fr,fr]);}var anc=document.getElementById('sp-cd-dhl'),sm=document.getElementById('sp-hw-sum');
 if(anc&&n>0){if(!sm){sm=document.createElement('div');sm.id='sp-hw-sum';}if(sm.nextElementSibling!==anc)anc.parentNode.insertBefore(sm,anc);
  sm.innerHTML=(save>0?'<div class="row"><span>🎃 Halloween-Angebot: du sparst</span><b>−'+fm(save/Math.pow(10,minor))+'</b></div>':'<div class="row"><span>🎃 Noch '+need+' Flasche'+(need>1?'n':'')+' bis zur Gratis-Flasche</span></div>')+'<div class="cdl">⏳ Angebot endet in <span>'+cdt()+'</span></div>';}
 else if(sm){sm.remove();}
 var sr=document.getElementById('sp-cd-total-savings-row'),sv=document.getElementById('sp-cd-total-savings-val');if(sr&&sv){sr.style.visibility=(save>0&&Math.abs(num(sv.textContent)-save/Math.pow(10,minor))<0.02)?'hidden':'';if(sr.style.visibility==='hidden')sr.style.height='0',sr.style.margin='0',sr.style.overflow='hidden';else sr.style.height=sr.style.margin='';}
 });},400);}
setInterval(function(){var s=document.querySelector('#sp-hw-sum .cdl span');if(s)s.textContent=cdt();},1000);
document.addEventListener('click',function(e){var b=e.target.closest('#sp-hw-cd .add');if(!b)return;addT=Date.now();b.disabled=true;b.textContent='Wird hinzugefügt …';
 var fd=new FormData();fd.append('product_id',b.getAttribute('data-id'));fd.append('quantity','1');
 fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){if(window.jQuery&&d&&d.fragments)jQuery(document.body).trigger('added_to_cart',[d.fragments,d.cart_hash]);else if(window.jQuery)jQuery(document.body).trigger('added_to_cart');}).catch(function(){b.disabled=false;});});
var items=document.getElementById('sp-cd-items');if(items){new MutationObserver(cart).observe(items,{childList:true,subtree:true});}
if(window.jQuery){jQuery(document.body).on('added_to_cart',function(){addT=Date.now();});jQuery(document.body).on('added_to_cart removed_from_cart',cart);}
document.addEventListener('click',function(e){if(e.target.closest('#sp-cart-fab'))cart();});
/* Tab-Titel, wenn der Besucher weggeht */
if(!document.body.classList.contains('woocommerce-order-received')){var otl=document.title;document.addEventListener('visibilitychange',function(){if(document.hidden){otl=document.title;document.title='🎃 Deine 3. Flasche wartet …';}else if(/^🎃 Deine 3/.test(document.title)){document.title=otl;}});}
/* Deko: Kategorie-Kopf + Stempel aufs Produktbild */
var webS='<svg class="sp-hw-web" viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".8"><path d="M0 0L100 100M0 0L50 100M0 0L100 50M0 0L0 100M0 0L100 0"/><path d="M0 20Q14 14 20 0M0 40Q28 28 40 0M0 62Q44 44 62 0M0 84Q60 60 84 0"/></svg>';
if(C.cat){var dt=0,di=setInterval(function(){var h=document.querySelector('#sp-cat .hero');if(!h){if(++dt>40)clearInterval(di);return;}clearInterval(di);if(h.querySelector('.sp-hw-web'))return;h.classList.add('sp-hw-hero');h.insertAdjacentHTML('afterbegin',webS+bat.replace('sp-hw-bat XX','sp-hw-hb a')+bat.replace('sp-hw-bat XX','sp-hw-hb b'));},150);}
if(C.pp){var st=0,si=setInterval(function(){var bd=document.querySelector('.sp-img-bd');if(!bd){if(++st>40)clearInterval(si);return;}clearInterval(si);var im=bd.parentElement;if(im&&!im.querySelector('.sp-hw-stamp'))im.insertAdjacentHTML('beforeend','<div class="sp-hw-stamp"><span>🎃</span><b>3 für 2</b></div>');},150);}
})();
</script>
    <?php
}, 120);

/* ---------------------------------------------------------------------
 * Danke-Seite + Bestellmails: zeigen, was die Gratis-Flasche gespart hat
 * (auch nach Aktionsende, solange die Bestellung im Aktionszeitraum lag).
 * ------------------------------------------------------------------- */
function sp_hw_order_saving($order) {
    if (!$order || (int) $order->get_meta('_sp_hw') < 1) return null;
    $free = 0; $save = 0.0;
    foreach ($order->get_items() as $it) {
        $m = $it->get_meta('Halloween-Angebot');
        if (!$m) continue;
        $free += (int) $m;
        $save += (float) $it->get_subtotal() - (float) $it->get_total();
    }
    if ($free < 1 || $save <= 0) return null;
    return ['free' => $free, 'save' => round($save, 2)];
}

add_action('woocommerce_thankyou', function ($order_id) {
    $sv = sp_hw_order_saving(wc_get_order($order_id));
    if (!$sv) return;
    $n = $sv['free'] . ' Flasche' . ($sv['free'] > 1 ? 'n' : '');
    echo '<div id="sp-hw-ty"><span class="i">🎃</span><div><b>Halloween-Angebot: ' . esc_html($n) . ' gratis</b><br>Du sparst <strong>' . wp_kses_post(wc_price($sv['save'])) . '</strong><small>Danke, dass du dabei bist!</small></div></div>';
    echo "<script>(function(){var b=document.getElementById('sp-hw-ty'),t=0,i=setInterval(function(){var h=document.getElementById('sp-ty-hero');if(h){clearInterval(i);var bank=h.nextElementSibling&&/bacs|bank|ueberweisung/i.test(h.nextElementSibling.className)?h.nextElementSibling:h;bank.insertAdjacentElement('afterend',b);}else if(++t>30){clearInterval(i);var w=document.querySelector('.woocommerce-order');if(w)w.insertAdjacentElement('afterbegin',b);}},150);})();</script>";
}, 1);

add_action('woocommerce_email_before_order_table', function ($order, $sent_to_admin, $plain, $email = null) {
    if ($sent_to_admin || $plain || !$email) return;
    if (!in_array($email->id, ['customer_on_hold_order', 'customer_processing_order'], true)) return;
    $sv = sp_hw_order_saving($order);
    if (!$sv) return;
    $n = $sv['free'] . ' Flasche' . ($sv['free'] > 1 ? 'n' : '');
    echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;border-collapse:separate"><tr><td style="background:#1C0E04;border:1px solid #7A3A0E;border-radius:14px;padding:16px 18px;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Inter,Arial,sans-serif;color:#FFE7CC;font-size:15px;line-height:1.45">'
        . '<span style="font-size:13px;letter-spacing:.14em;font-weight:800;color:#FFB547">🎃 HALLOWEEN-ANGEBOT</span><br>'
        . '<b style="color:#ffffff;font-size:17px">' . esc_html($n) . ' gratis – du sparst ' . wp_kses_post(wc_price($sv['save'])) . '</b>'
        . '</td></tr></table>';
}, 5, 4);

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
        if ($o['on']) $o['auto_on'] = 0;
        $o['deko'] = empty($_POST['deko']) ? 0 : 1;
        foreach (['early', 'start', 'end'] as $k) {
            $v = sanitize_text_field(wp_unslash($_POST[$k] ?? ''));
            try { if ($v !== '') $o[$k] = (new DateTime(str_replace('T', ' ', $v), $tz))->getTimestamp(); } catch (Exception $e) {}
        }
        $o['bonus'] = max(0, min(20, (float) str_replace(',', '.', (string) ($_POST['bonus'] ?? 5))));
        $o['wenig'] = empty($_POST['wenig']) ? 0 : 1;
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
    if (empty($o['on']) && !empty($o['auto_on'])) { $phase .= ' <b>Schaltet sich automatisch ein am ' . esc_html(wp_date('d.m.Y \\u\\m H:i', (int) $o['auto_on'], $tz)) . ' Uhr.</b>'; }
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
    echo '<tr><th>„Nur noch wenige verfügbar“</th><td><label><input type="checkbox" name="wenig" value="1" ' . checked(!empty($o['wenig']), true, false) . '> auf allen Produktseiten zeigen, solange die Aktion läuft</label></td></tr>';
    echo '<tr><th>Partner-Bonus</th><td><input type="text" name="bonus" value="' . esc_attr($o['bonus']) . '" style="width:60px"> Prozentpunkte zusätzlich (z. B. 20 % → 25 %)</td></tr></table>';
    echo '<p><button class="button button-primary button-hero" name="sp_hw_save" value="1">Speichern</button></p></form>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px;margin:16px 0"><h2 style="margin-top:0">Links</h2>';
    echo '<p><b>Early-Access-Link für die Newsletter-Mail:</b><br><code style="user-select:all">' . esc_html($ea) . '</code></p>';
    echo '<p><b>Vorschau (nur für dich, 24 h, zeigt alles wie live – auch wenn der Schalter aus ist):</b><br><a href="' . esc_url($pv) . '" target="_blank">' . esc_html($pv) . '</a><br><a href="' . esc_url(home_url('/?sp_hw=aus')) . '" target="_blank">Vorschau beenden</a></p></div>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px;margin:16px 0"><h2 style="margin-top:0">Was zählt („jede 3. gratis“)</h2><p>' . ($names ? implode(' · ', $names) : '– keine vorrätigen Flaschen –') . '</p><p style="color:#646970">Nur vorrätige Peptid-Flaschen. Nicht: Pens, Zubehör, Vorbestellungen, Abo, Geschenke. Auf diese Flaschen während der Aktion: kein Rabattcode, kein 10-%-Partnerlink-Rabatt, kein Mengenrabatt – auf Pens/Zubehör gelten Codes und Partnerrabatt weiter. Partner-Codes ordnen immer zu. Early Access: nur Newsletter (Link aus der Mail oder Anmeldung in der Teaser-Zeit).</p></div>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px 20px;margin:16px 0"><h2 style="margin-top:0">Ergebnis</h2><p>' . (int) $cnt . ' Bestellungen · ' . wc_price($sum) . ' · ' . (int) $free . ' Flaschen gratis</p></div></div>';
}
