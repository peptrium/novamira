<?php
/**
 * Plugin Name: SP Newsletter – weitere Automatik-Mails
 * Description: Willkommen 2 + 3, Abo-Angebot fuer Wiederkaeufer, "Wieder da" fuer Vorbestell-Produkte, "Passt dazu" und
 * Bewertungsanfrage – jeweils als Omnisend-Ereignis (Mails/Design liegen in Omnisend, Generator
 * drafts/newsletter-mails-C-2026-10-16/more.py).
 *
 * (2026-10-16, Nutzer: "passt, jetzt die anderen Mail-Entwuerfe einbauen") Welche Mails laufen, steht in der Option
 * sp_nlx_on (Array mit den Schluesseln w2, w3, abo, wd, qv, bw = 1). Alles laeuft im taeglichen Cron sp_nlm_daily (prio 30),
 * "Wieder da" stuendlich. Gestartet wird nur, was nach dem Einschalten faellig wird (Option sp_nlx_since).
 * Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_nlx_on($k) {
    $o = get_option('sp_nlx_on', []);
    return is_array($o) && !empty($o[$k]) && function_exists('sp_os_event');
}

function sp_nlx_since() {
    return (int) get_option('sp_nlx_since', time());
}

function sp_nlx_url($path, $campaign) {
    return add_query_arg(['utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => $campaign], home_url($path));
}

function sp_nlx_money($v) {
    return html_entity_decode(wp_strip_all_tags(wc_price($v)), ENT_QUOTES, 'UTF-8');
}

function sp_nlx_img($p, $size = 'woocommerce_single') {
    if (is_numeric($p)) {
        $p = wc_get_product($p);
    }
    $iid = $p ? ($p->get_image_id() ?: (($pp = wc_get_product($p->get_parent_id())) ? $pp->get_image_id() : 0)) : 0;
    return $iid ? (string) wp_get_attachment_image_url($iid, $size) : wc_placeholder_img_src();
}

function sp_nlx_sub($email) {
    if (!class_exists('\FluentCrm\App\Models\Subscriber')) {
        return null;
    }
    return \FluentCrm\App\Models\Subscriber::where('email', strtolower(trim($email)))->first();
}

function sp_nlx_has_order($email) {
    return (bool) wc_get_orders(['billing_email' => $email, 'status' => ['on-hold', 'processing', 'completed'], 'limit' => 1, 'return' => 'ids']);
}

function sp_nlx_paid_items($order) {
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 729;
    $out = [];
    foreach ($order->get_items() as $it) {
        if ($it->get_meta('Geschenk') || (int) $it->get_product_id() === $topup || (float) $it->get_total() <= 0) {
            continue;
        }
        $out[] = $it;
    }
    return $out;
}

/* Hauptartikel fuer die Bewertung: erster Nicht-Zubehoer-Artikel, sonst der erste. */
function sp_nlx_main_pid($order) {
    $items = sp_nlx_paid_items($order);
    foreach ($items as $it) {
        if (!has_term(20, 'product_cat', (int) $it->get_product_id())) {
            return (int) $it->get_product_id();
        }
    }
    return $items ? (int) $items[0]->get_product_id() : 0;
}

function sp_nlx_test_order($o) {
    return $o->get_meta('_sp_is_test') || in_array((int) $o->get_customer_id(), [17, 41, 90], true);
}

/* ---------- Willkommen 2 (Tag 2) und 3 (Tag 6) – nur, wer noch nicht bestellt hat ---------- */
function sp_nlx_welcome_run() {
    if ((!sp_nlx_on('w2') && !sp_nlx_on('w3')) || !class_exists('\FluentCrm\App\Models\Subscriber') || !function_exists('sp_nlm_omni_event')) {
        return;
    }
    $subs = \FluentCrm\App\Models\Subscriber::where('status', 'subscribed')->get();
    foreach ($subs as $sub) {
        $code = (string) $sub->getMeta('sp_nl_coupon', 'peptrium');
        $cid = $code ? wc_get_coupon_id_by_code($code) : 0;
        if (!$cid) {
            continue;
        }
        $c = new WC_Coupon($cid);
        $t = strtotime(get_post_field('post_date_gmt', $cid) . ' UTC');
        if (!$t || $t < sp_nlx_since() - 9 * DAY_IN_SECONDS) {
            continue;
        }
        $days = (int) floor((time() - $t) / DAY_IN_SECONDS);
        if ($c->get_usage_count() > 0 || sp_nlx_has_order($sub->email)) {
            continue;
        }
        $props = ['hallo_code' => $code, 'hallo_gueltig_bis' => $c->get_date_expires() ? $c->get_date_expires()->date_i18n('d.m.Y') : ''];
        foreach ([['w2', 2, 4, 'sp_willkommen_2'], ['w3', 6, 9, 'sp_willkommen_3']] as [$k, $from, $to, $ev]) {
            if (sp_nlx_on($k) && $days >= $from && $days <= $to && !$sub->getMeta('sp_nlx_' . $k, 'peptrium')) {
                if (sp_nlm_omni_event($sub, $ev, $props)) {
                    $sub->updateMeta('sp_nlx_' . $k, current_time('mysql'), 'peptrium');
                }
                break; // hoechstens eine Mail pro Tag
            }
        }
    }
}

/* ---------- Abo-Angebot: 3 Tage nach einem Wiederholungskauf ---------- */
function sp_nlx_abo_props($order) {
    $prev = wc_get_orders(['billing_email' => $order->get_billing_email(), 'status' => ['processing', 'completed'], 'limit' => 20, 'exclude' => [$order->get_id()], 'date_created' => '<' . $order->get_date_created()->getTimestamp()]);
    $before = [];
    foreach ($prev as $po) {
        foreach (sp_nlx_paid_items($po) as $it) {
            $before[(int) $it->get_product_id()] = true;
        }
    }
    foreach (sp_nlx_paid_items($order) as $it) {
        $pid = (int) $it->get_product_id();
        if (empty($before[$pid]) || $it->get_meta('_sp_abo_interval_days')) {
            continue;
        }
        if (function_exists('sp_abo_is_subscribable_product') && !sp_abo_is_subscribable_product($pid)) {
            continue;
        }
        $p = $it->get_product();
        if (!$p || !$p->is_purchasable()) {
            continue;
        }
        $price = (float) $p->get_price();
        if ($price <= 0) {
            continue;
        }
        $abo = round($price * 0.85, 2);
        return [
            'abo_name' => get_the_title($pid),
            'abo_bild' => sp_nlx_img($p),
            'abo_preis' => sp_nlx_money($price),
            'abo_preis_abo' => sp_nlx_money($abo),
            'abo_ersparnis' => sp_nlx_money($price - $abo),
        ];
    }
    return null;
}

function sp_nlx_has_abo($order) {
    global $wpdb;
    $uid = (int) $order->get_customer_id();
    if (!$uid) {
        return false;
    }
    return (bool) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}sp_subscriptions WHERE user_id = %d AND status IN ('active','paused') LIMIT 1", $uid));
}

function sp_nlx_abo_run() {
    if (!sp_nlx_on('abo') || !function_exists('sp_nlm_omni_event')) {
        return;
    }
    $from = max(sp_nlx_since(), time() - 7 * DAY_IN_SECONDS);
    $orders = wc_get_orders(['status' => ['processing', 'completed'], 'limit' => 100, 'type' => 'shop_order', 'date_paid' => $from . '...' . (time() - 3 * DAY_IN_SECONDS)]);
    $sent = get_option('sp_nlx_abo_sent', []);
    foreach ($orders as $o) {
        $email = strtolower((string) $o->get_billing_email());
        $h = md5($email);
        if (sp_nlx_test_order($o) || $o->get_meta('_sp_nlx_abo') || !empty($sent[$h])) {
            continue;
        }
        $sub = sp_nlx_sub($email);
        if (!$sub || $sub->status !== 'subscribed' || sp_nlx_has_abo($o)) {
            continue;
        }
        $props = sp_nlx_abo_props($o);
        if (!$props) {
            $o->update_meta_data('_sp_nlx_abo', 'kein-wiederholungskauf');
            $o->save();
            continue;
        }
        if (sp_nlm_omni_event($sub, 'sp_abo_angebot', $props)) {
            $sent[$h] = current_time('mysql');
            update_option('sp_nlx_abo_sent', $sent, false);
            $o->update_meta_data('_sp_nlx_abo', current_time('mysql'));
            $o->save();
        }
    }
}

/* ---------- Passt dazu: 16 Tage nach dem Versand ---------- */
function sp_nlx_qv_props($order) {
    $map = function_exists('sp_crosssell_map') ? sp_crosssell_map() : [];
    $pre = function_exists('sp_preorder_product_ids') ? (array) sp_preorder_product_ids() : [];
    $have = [];
    foreach (sp_nlx_paid_items($order) as $it) {
        $have[] = (int) $it->get_product_id();
    }
    foreach ($have as $pid) {
        $rec = (int) ($map[$pid] ?? 0);
        if (!$rec || in_array($rec, $have, true) || in_array($rec, $pre, true)) {
            continue;
        }
        $p = wc_get_product($rec);
        if (!$p || !$p->is_purchasable() || !$p->is_visible()) {
            continue;
        }
        $info = '';
        if ($p->is_type('variable')) {
            $sizes = [];
            foreach ($p->get_available_variations() as $v) {
                $sizes[] = implode(' ', array_map('strval', array_values($v['attributes'])));
            }
            $info = implode(' · ', array_slice(array_unique(array_filter($sizes)), 0, 3));
            $price = 'ab ' . sp_nlx_money($p->get_variation_price('min'));
        } else {
            $price = sp_nlx_money($p->get_price());
        }
        $base = get_the_title($pid);
        return [
            'qv_basis' => 'deinem ' . $base,
            'qv_grund' => 'Wird oft zusammen mit ' . $base . ' erforscht – und passt gut in deine nächste Bestellung.',
            'qv_name' => $p->get_name(),
            'qv_info' => $info !== '' ? $info : 'lyophilisiert · HPLC-geprüft',
            'qv_preis' => $price,
            'qv_bild' => sp_nlx_img($p),
            'qv_link' => sp_nlx_url(wp_parse_url(get_permalink($rec), PHP_URL_PATH), 'passt-dazu'),
        ];
    }
    return null;
}

/* ---------- Bewertung: 9 Tage nach dem Versand ---------- */
function sp_nlx_bw_sig($oid) {
    return substr(hash_hmac('sha256', 'sp_bewertung|' . (int) $oid, wp_salt('auth')), 0, 20);
}

function sp_nlx_bw_props($order) {
    $items = sp_nlx_paid_items($order);
    $first = $items ? get_the_title(sp_nlx_main_pid($order)) : '';
    return [
        'bw_link' => add_query_arg(['sp_bewertung' => $order->get_id(), 'k' => sp_nlx_bw_sig($order->get_id())], home_url('/')),
        'bw_nr' => (string) $order->get_order_number(),
        'bw_produkt' => count($items) === 1 && $first ? 'deinem ' . $first : 'deiner Bestellung',
    ];
}

/* Versendete Bestellungen im Zeitfenster [$min_days, $max_days] nach "Versendet" durchgehen. */
function sp_nlx_shipped_run($key, $min_days, $max_days, $event, $props_fn) {
    if (!sp_nlx_on($key) || !function_exists('sp_nlm_omni_event')) {
        return;
    }
    $from = max(sp_nlx_since(), time() - $max_days * DAY_IN_SECONDS);
    $orders = wc_get_orders(['status' => 'completed', 'limit' => 100, 'type' => 'shop_order', 'date_completed' => $from . '...' . (time() - $min_days * DAY_IN_SECONDS)]);
    foreach ($orders as $o) {
        if (sp_nlx_test_order($o) || $o->get_meta('_sp_nlx_' . $key) || !sp_nlx_paid_items($o)) {
            continue;
        }
        $email = strtolower((string) $o->get_billing_email());
        $newer = wc_get_orders(['billing_email' => $email, 'status' => ['on-hold', 'processing', 'completed'], 'limit' => 1, 'return' => 'ids', 'date_created' => '>' . $o->get_date_created()->getTimestamp()]);
        $sub = sp_nlx_sub($email);
        if (!$sub || $sub->status !== 'subscribed' || ($key === 'qv' && $newer)) {
            $o->update_meta_data('_sp_nlx_' . $key, 'skip');
            $o->save();
            continue;
        }
        $props = $props_fn($o);
        if (!$props) {
            $o->update_meta_data('_sp_nlx_' . $key, 'skip-keine-empfehlung');
            $o->save();
            continue;
        }
        if (sp_nlm_omni_event($sub, $event, $props)) {
            $o->update_meta_data('_sp_nlx_' . $key, current_time('mysql'));
            $o->save();
        }
    }
}

add_action('sp_nlm_daily', function () {
    sp_nlx_welcome_run();
    sp_nlx_abo_run();
    sp_nlx_shipped_run('bw', 9, 13, 'sp_bewertung', 'sp_nlx_bw_props');
    sp_nlx_shipped_run('qv', 16, 21, 'sp_passt_dazu', 'sp_nlx_qv_props');
}, 30);

/* ---------- Bewertungsseite ?sp_bewertung=<id>&k=<sig>&r=<1-5> ---------- */
add_action('template_redirect', function () {
    if (empty($_GET['sp_bewertung'])) {
        return;
    }
    $oid = (int) $_GET['sp_bewertung'];
    $order = ($oid && hash_equals(sp_nlx_bw_sig($oid), (string) ($_GET['k'] ?? ''))) ? wc_get_order($oid) : null;
    nocache_headers();
    header('X-Robots-Tag: noindex', true);
    $r = max(0, min(5, (int) ($_REQUEST['r'] ?? 0)));
    $state = $order ? 'ask' : 'invalid';
    if ($order && $order->get_meta('_sp_nlx_review_done')) {
        $state = 'done';
    }
    if ($order && $state === 'ask' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $r >= 1) {
        $pid = sp_nlx_main_pid($order);
        $text = trim(wp_strip_all_tags(wp_unslash($_POST['text'] ?? '')));
        if ($pid) {
            $cid = wp_insert_comment([
                'comment_post_ID' => $pid,
                'comment_author' => trim($order->get_billing_first_name() . ' ' . mb_substr($order->get_billing_last_name(), 0, 1) . '.'),
                'comment_author_email' => $order->get_billing_email(),
                'comment_content' => $text !== '' ? mb_substr($text, 0, 2000) : '(nur Sterne)',
                'comment_type' => 'review',
                'comment_approved' => 0,
                'user_id' => (int) $order->get_customer_id(),
                'comment_meta' => ['rating' => $r, 'verified' => 1, 'sp_order_id' => $oid],
            ]);
            if ($cid) {
                $order->update_meta_data('_sp_nlx_review_done', $r);
                $order->save();
                $order->add_order_note('Kunde hat eine Bewertung abgegeben (' . $r . ' Sterne) – wartet auf Freigabe unter Produkte → Bewertungen.');
                $state = 'thanks';
            }
        }
    }
    $logo = get_option('woocommerce_email_header_image');
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= '<label class="st"><input type="radio" name="r" value="' . $i . '"' . ($i === $r ? ' checked' : '') . '><span>★</span><b>' . $i . '</b></label>';
    }
    $texts = [
        'ask' => ['Wie zufrieden bist du?', 'Bestellung #' . ($order ? esc_html($order->get_order_number()) : '') . ' – deine ehrliche Meinung hilft uns. Ein kurzer Text ist freiwillig.'],
        'thanks' => ['Danke für deine Bewertung! 🙏', 'Wir lesen jede Rückmeldung. Wenn etwas nicht gepasst hat, melde dich gerne direkt bei uns.'],
        'done' => ['Schon erledigt ✓', 'Für diese Bestellung hast du bereits eine Bewertung abgegeben – danke!'],
        'invalid' => ['Link ungültig', 'Dieser Link funktioniert leider nicht.'],
    ];
    [$h, $p] = $texts[$state];
    ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Bewertung – Peptrium</title>
<style>
body{margin:0;background:#0D0F12;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;color:#fff}
.w{max-width:520px;margin:0 auto;padding:40px 18px 50px;text-align:center}
.logo{width:88px;height:auto}.wm{font-size:17px;font-weight:800;letter-spacing:.32em;color:#E6E8EB;margin:10px 0 26px;padding-left:.32em}
h1{font-size:27px;line-height:1.2;margin:0 0 12px}p{color:#C9CDD2;font-size:15px;line-height:1.6;margin:0 0 22px}
.stars{display:flex;gap:8px;justify-content:center;margin:0 0 18px}.st{flex:1;max-width:72px;cursor:pointer}.st input{position:absolute;opacity:0}
.st span{display:block;font-size:30px;color:#3A3F46;border:1px solid #2C3138;border-radius:14px;padding:10px 0 4px;background:#12151A}.st b{display:block;font-size:13px;color:#9AA0A8;margin-top:4px}
.st input:checked+span,.st:has(input:checked) span{color:#F5A524;border-color:#F5A524}
textarea{width:100%;box-sizing:border-box;min-height:120px;border-radius:12px;border:1px solid #2C3138;background:#12151A;color:#fff;padding:12px;font:15px/1.5 inherit;margin:0 0 14px}
.bt{display:block;width:100%;box-sizing:border-box;border:0;border-radius:12px;padding:16px;font-weight:800;font-size:16px;text-decoration:none;cursor:pointer;font-family:inherit;background:#fff;color:#0D0F12}
.b2{background:transparent;color:#fff;border:1.5px solid #fff;margin-top:10px}.s{font-size:12px;color:#7D848D;margin-top:16px;line-height:1.5}
</style></head><body><div class="w">
<?php if ($logo) : ?><img class="logo" src="<?php echo esc_url($logo); ?>" alt="Peptrium"><?php endif; ?>
<div class="wm">PEPTRIUM</div>
<h1><?php echo esc_html($h); ?></h1><p><?php echo esc_html($p); ?></p>
<?php if ($state === 'ask') : ?>
<form method="post"><div class="stars"><?php echo $stars; // fest aufgebaut ?></div>
<textarea name="text" maxlength="2000" placeholder="Was hat dir gefallen, was können wir besser machen? (freiwillig)"></textarea>
<button class="bt" type="submit">Bewertung abschicken</button></form>
<div class="s">Deine Bewertung kann mit Vorname und abgekürztem Nachnamen auf peptrium.com erscheinen. Nichts ist öffentlich, bevor wir es freigeben.</div>
<?php else : ?>
<a class="bt" href="https://t.me/peptrium">Per Telegram schreiben</a><a class="bt b2" href="<?php echo esc_url(home_url('/alle-produkte/')); ?>">Zum Shop &rarr;</a>
<?php endif; ?>
</div></body></html><?php
    exit;
});

/* ---------- Wieder da: Benachrichtigung auf Vorbestell-Produktseiten ---------- */
/* Formular "Wieder da" sichtbar: live (sp_nlx_on wd) oder im Vorschau-Modus (Cookie/Link sp_vorschau). */
function sp_nlx_wd_visible() {
    return sp_nlx_on('wd') || (function_exists('sp_hpv_token_ok') && sp_hpv_token_ok());
}

function sp_nlx_preorder_ids() {
    return function_exists('sp_preorder_product_ids') ? array_map('intval', (array) sp_preorder_product_ids()) : [];
}

add_action('wp_footer', function () {
    if (!sp_nlx_wd_visible() || !function_exists('is_product') || !is_product()) {
        return;
    }
    $pid = (int) get_queried_object_id();
    if (!in_array($pid, sp_nlx_preorder_ids(), true)) {
        return;
    }
    ?>
<style>
#sp-wd{margin:14px 0 4px;border:1px solid #E4E6E9;background:#F5F6F7;border-radius:16px;padding:14px;text-align:left;font-family:inherit}
#sp-wd .t{font-size:14px;font-weight:800;color:#0D0F12;margin:0 0 4px}#sp-wd .d{font-size:13px;line-height:1.5;color:#5B6169;margin:0 0 10px}
#sp-wd .row{display:flex;gap:8px}#sp-wd input[type=email]{flex:1;min-width:0;height:44px;border:1px solid #D5D9DD;border-radius:10px;padding:0 12px;font-size:15px;background:#fff;color:#0D0F12}
#sp-wd button{height:44px;border:0;border-radius:10px;padding:0 14px;background:#0D0F12!important;color:#fff!important;font-weight:800;font-size:14px;white-space:nowrap;box-shadow:none!important}
#sp-wd .s{font-size:11px;color:#7D848D;margin-top:8px}#sp-wd .ok{font-size:14px;font-weight:700;color:#1E7A4C}
#sp-wd .hp{position:absolute;left:-9999px}
</style>
<script>
(function(){
  function mk(){
    if(document.getElementById('sp-wd'))return;
    var anchor=document.querySelector('.rx-buycta')||document.querySelector('form.cart');
    if(!anchor)return;
    var d=document.createElement('div');d.id='sp-wd';
    d.innerHTML='<p class="t">🔔 Lieber auf sofort lieferbar warten?</p><p class="d">Wir schreiben dir einmalig, sobald es wieder auf Lager ist.</p>'
      +'<form class="row"><input type="email" required placeholder="Deine E-Mail-Adresse" autocomplete="email"><input class="hp" name="website" tabindex="-1" autocomplete="off"><button type="submit">Benachrichtigen</button></form>'
      +'<p class="s">Nur diese eine E-Mail, kein Newsletter. Mehr in der <a href="/datenschutz/" style="color:inherit">Datenschutzerklärung</a>.</p>';
    anchor.parentNode.insertBefore(d,anchor.nextSibling);
    d.querySelector('form').addEventListener('submit',function(e){
      e.preventDefault();var f=e.target,b=f.querySelector('button');b.disabled=true;
      var fd=new FormData();fd.append('action','sp_wd_save');fd.append('email',f.querySelector('input[type=email]').value);fd.append('website',f.querySelector('.hp').value);fd.append('pid','<?php echo (int) $pid; ?>');
      fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){
        if(j&&j.success){d.innerHTML='<p class="ok">✓ Eingetragen – wir melden uns, sobald es sofort lieferbar ist.</p>';}else{b.disabled=false;alert((j&&j.data)||'Das hat nicht geklappt.');}
      }).catch(function(){b.disabled=false;});
    });
  }
  if(document.readyState!=='loading')mk();else document.addEventListener('DOMContentLoaded',mk);
  setTimeout(mk,1500);
})();
</script>
    <?php
}, 60);

function sp_nlx_wd_save() {
    if (!sp_nlx_wd_visible() || !empty($_POST['website'])) {
        wp_send_json_error('Das hat nicht geklappt.');
    }
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $pid = (int) ($_POST['pid'] ?? 0);
    if (!is_email($email)) {
        wp_send_json_error('Bitte gib eine gültige E-Mail-Adresse ein.');
    }
    if (!in_array($pid, sp_nlx_preorder_ids(), true)) {
        wp_send_json_error('Dieses Produkt ist bereits lieferbar.');
    }
    $ip = md5((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? ''));
    $tk = 'sp_wd_rl_' . $ip;
    $n = (int) get_transient($tk);
    if ($n >= 6) {
        wp_send_json_error('Zu viele Versuche – bitte später noch einmal.');
    }
    set_transient($tk, $n + 1, HOUR_IN_SECONDS);
    $list = get_option('sp_wd_list', []);
    $key = md5(strtolower($email) . '|' . $pid);
    $list[$key] = ['e' => strtolower($email), 'p' => $pid, 't' => time()];
    update_option('sp_wd_list', $list, false);
    wp_send_json_success();
}
add_action('wp_ajax_sp_wd_save', 'sp_nlx_wd_save');
add_action('wp_ajax_nopriv_sp_wd_save', 'sp_nlx_wd_save');

function sp_nlx_wd_props($pid) {
    $p = wc_get_product($pid);
    if (!$p) {
        return null;
    }
    $price = $p->is_type('variable') ? 'ab ' . sp_nlx_money($p->get_variation_price('min')) : sp_nlx_money($p->get_price());
    return [
        'wd_name' => $p->get_name(),
        'wd_bild' => sp_nlx_img($p),
        'wd_preis' => $price,
        'wd_link' => sp_nlx_url(wp_parse_url(get_permalink($pid), PHP_URL_PATH), 'wieder-da'),
    ];
}

add_action('init', function () {
    if (!wp_next_scheduled('sp_wd_check')) {
        wp_schedule_event(time() + 300, 'hourly', 'sp_wd_check');
    }
});

add_action('sp_wd_check', function () {
    $now = sp_nlx_preorder_ids();
    $prev = get_option('sp_wd_prev', null);
    update_option('sp_wd_prev', $now, false);
    $list = get_option('sp_wd_list', []);
    if (!$list) {
        return;
    }
    $changed = false;
    foreach ($list as $k => $row) {
        if ($row['t'] < time() - 180 * DAY_IN_SECONDS) {
            unset($list[$k]);
            $changed = true;
            continue;
        }
        if (!is_array($prev) || !sp_nlx_on('wd') || in_array((int) $row['p'], $now, true)) {
            continue; // noch Vorbestellung
        }
        $p = wc_get_product((int) $row['p']);
        if (!$p || !$p->is_in_stock() || !$p->is_purchasable()) {
            continue;
        }
        $props = sp_nlx_wd_props((int) $row['p']);
        $r = $props ? sp_os_event($row['e'], 'sp_wieder_da', $props, 'nonSubscribed') : 'kein Produkt';
        if ($r === true || $r === 'unsubscribed') {
            unset($list[$k]);
            $changed = true;
        } else {
            update_option('sp_os_last_error', current_time('mysql') . ' – ' . $row['e'] . ' (sp_wieder_da): ' . $r, false);
        }
    }
    if ($changed) {
        update_option('sp_wd_list', $list, false);
    }
});

/* ---------- Datenschutz: Abschnitt fuer die neuen Mails ---------- */
function sp_nlx_privacy($html) {
    if ((!sp_nlx_on('wd') && !sp_nlx_on('bw')) || !is_string($html) || strpos($html, '<h2>Daten von Kindern</h2>') === false || strpos($html, 'sp-nlx-privacy') !== false || !is_page(410)) {
        return $html;
    }
    $s = '<h2 class="sp-nlx-privacy">Benachrichtigung „Wieder lieferbar“ und Bewertungen</h2>'
        . '<p>Auf Produktseiten von Vorbestell-Artikeln kannst du deine E-Mail-Adresse hinterlassen, damit wir dir einmalig Bescheid geben, sobald der Artikel sofort lieferbar ist. Dafür speichern wir deine E-Mail-Adresse und den Artikel bis zum Versand der Benachrichtigung, höchstens 180 Tage, und übermitteln sie für den Versand an Omnisend. Rechtsgrundlage ist deine Anfrage (Art. 6 Abs. 1 lit. b DSGVO). Du erhältst darüber keinen Newsletter.</p>'
        . '<p>Wenn du eine Bestellung über den Link in unserer E-Mail bewertest, speichern wir Sternebewertung, Text, Vorname, den ersten Buchstaben deines Nachnamens, deine E-Mail-Adresse und die Bestellnummer. Die Bewertung kann nach unserer Freigabe mit Vorname und abgekürztem Nachnamen auf peptrium.com erscheinen. Rechtsgrundlage ist deine Einwilligung durch das Absenden (Art. 6 Abs. 1 lit. a DSGVO); du kannst die Löschung jederzeit unter info@peptrium.com verlangen.</p>';
    return str_replace('<h2>Daten von Kindern</h2>', $s . '<h2>Daten von Kindern</h2>', $html);
}
add_filter('elementor/frontend/the_content', 'sp_nlx_privacy', 99);
add_filter('the_content', 'sp_nlx_privacy', 99);
