<?php
/**
 * Plugin Name: SP Analyse – Kasse, Zahlung, Kundenbindung
 * Description: Erweitert den Analyse-Tab um Kassen-Fehler-Protokoll, Zahlungsarten/Vorkasse-Zahlquote und Kundenbindung/Abo-Kennzahlen (2026-10-09).
 *
 * 1. Kassen-Fehler: ein kleines Skript protokolliert, was Kunden an Fehlern
 *    SEHEN - fehlgeschlagene Store-API-Antworten (Kasse, Gutschein, Warenkorb,
 *    auch im Warenkorb-Drawer), rote Hinweis-Banner, beim Klick auf
 *    "Bestellen" noch offene Pflichtfeld-Meldungen und JavaScript-Fehler auf
 *    Kasse/Warenkorb. Tabelle wp_sp_errors, gleicher anonymer Tagescode wie
 *    die Besucherstatistik (sp-analyse.php), keine Cookies, keine
 *    Formularinhalte - nur die Fehlermeldung selbst. Admins werden nicht
 *    erfasst. Loeschung nach 180 Tagen.
 * 2. Zahlungsarten: rein aus den Bestellungen - pro Zahlungsart bestellt /
 *    bezahlt / offen / storniert, Zahlquote, Tage bis Zahlung.
 * 3. Kundenbindung & Abos: Wiederkaufrate, Kundenwert (auch nach Herkunft
 *    des Erstkaufs, abzueglich Partner-Provision) und Abo-Bestand.
 *
 * Testkunden (SP_AN_TEST_USER_IDS) und Testbestellungen (_sp_is_test) sind
 * ueberall ausgenommen. Die Karten erscheinen ueber den Hook
 * sp_an_render_insights in sp_an_render_tab().
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_AN_ERR_DB_VERSION', '1');
define('SP_AN_TEST_USER_IDS', '17,41,90');

function sp_an_err_table() {
    global $wpdb;
    return $wpdb->prefix . 'sp_errors';
}

function sp_an_err_install() {
    if (get_option('sp_an_err_db_version') === SP_AN_ERR_DB_VERSION) {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta('CREATE TABLE ' . sp_an_err_table() . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        ts DATETIME NOT NULL,
        day DATE NOT NULL,
        vh CHAR(16) NOT NULL,
        area VARCHAR(12) NOT NULL,
        code VARCHAR(80) NOT NULL DEFAULT '',
        msg VARCHAR(255) NOT NULL DEFAULT '',
        page VARCHAR(100) NOT NULL DEFAULT '',
        device VARCHAR(8) NOT NULL DEFAULT '',
        app VARCHAR(10) NOT NULL DEFAULT '',
        PRIMARY KEY  (id),
        KEY day_area (day,area),
        KEY vh_day (vh,day)
    ) " . $wpdb->get_charset_collate() . ';');
    update_option('sp_an_err_db_version', SP_AN_ERR_DB_VERSION);
}
add_action('admin_init', 'sp_an_err_install');

function sp_an_test_user_ids() {
    return array_map('intval', explode(',', SP_AN_TEST_USER_IDS));
}

function sp_an_is_test_order($order) {
    return $order->get_meta('_sp_is_test') || in_array((int) $order->get_customer_id(), sp_an_test_user_ids(), true);
}

/* -----------------------------------------------------------------------
 * 1. Kassen-Fehler erfassen
 * ---------------------------------------------------------------------*/

add_action('wp_footer', function () {
    if (is_admin() || !function_exists('sp_an_should_track') || !sp_an_should_track()) {
        return;
    }
    $watch_js = (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) || (function_exists('is_cart') && is_cart());
    $area = (function_exists('is_checkout') && is_checkout()) ? 'kasse' : 'warenkorb';
    ?>
    <script>
    (function(){
      var U=<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,AREA=<?php echo wp_json_encode($area); ?>,sent={},n=0;
      function clean(s){return String(s||'').replace(/<[^>]*>/g,' ').replace(/&[a-z#0-9]+;/gi,' ').replace(/\s+/g,' ').trim().slice(0,250);}
      function send(a,code,msg){
        msg=clean(msg);code=String(code||'').slice(0,80);
        if(!msg&&!code)return;
        var k=a+'|'+msg;if(sent[k]||n>=15)return;sent[k]=1;n++;
        try{var d=new FormData();d.append('action','sp_an_err');d.append('a',a);d.append('c',code);d.append('m',msg);d.append('p',location.pathname.slice(0,100));
        if(!(navigator.sendBeacon&&navigator.sendBeacon(U,d))){fetch(U,{method:'POST',body:d,keepalive:true,credentials:'same-origin'});}}catch(e){}
      }
      function areaOf(u){return /apply-coupon|coupon/.test(u)?'gutschein':(/checkout/.test(u)?'kasse':'warenkorb');}
      /* Fehlgeschlagene Store-API-Antworten (Kasse, Gutschein, Warenkorb/Drawer) */
      if(window.fetch){
        var of=window.fetch;
        window.fetch=function(input){
          var url=typeof input==='string'?input:((input&&input.url)||'');
          var p=of.apply(this,arguments);
          if(url.indexOf('/wc/store/')!==-1){
            p.then(function(r){
              if(/\/batch/.test(url)&&r.ok){
                r.clone().json().then(function(j){(j&&j.responses||[]).forEach(function(x){if(x&&x.status>=400&&x.body){send(areaOf(url+(x.body.code||'')),x.body.code,x.body.message);}});}).catch(function(){});
              }else if(!r.ok&&r.status!==401){
                r.clone().json().then(function(j){send(areaOf(url),(j&&j.code)||('http_'+r.status),j&&j.message);}).catch(function(){send(areaOf(url),'http_'+r.status,'Serverantwort '+r.status);});
              }
            }).catch(function(){});
          }
          return p;
        };
      }
      /* Rote Hinweis-Banner, die der Kunde sieht - nur wenn sie mind. 1,5 s
         stehen bleiben (beim Laden der Kasse blitzt z. B. kurz "keine
         Zahlungsarten verfuegbar" auf, bis die Zahlungsarten geladen sind). */
      var SEL='.wc-block-components-notice-banner.is-error,.woocommerce-error';
      function watch(el){setTimeout(function(){if(el.isConnected&&el.offsetParent!==null){send(AREA,'hinweis',el.textContent);}},1500);}
      function scan(root){(root.querySelectorAll?root:document).querySelectorAll(SEL).forEach(watch);}
      new MutationObserver(function(ms){ms.forEach(function(m){m.addedNodes.forEach(function(nd){if(nd.nodeType===1){if(nd.matches&&nd.matches(SEL)){watch(nd);}else{scan(nd);}}});});}).observe(document.documentElement,{childList:true,subtree:true});
      /* Pflichtfelder, die beim Klick auf "Bestellen" noch rot sind */
      document.addEventListener('click',function(e){
        if(!e.target.closest||!e.target.closest('.wc-block-components-checkout-place-order-button'))return;
        setTimeout(function(){
          document.querySelectorAll('.wc-block-components-validation-error').forEach(function(el){
            var w=el.closest('.has-error')||el.parentElement,l=w&&w.querySelector('label'),lt=l?clean(l.textContent):'';
            // Bei Haekchen (AGB, Alter) ist das Label ein ganzer Satz - dann nur die Meldung.
            var mt=clean(el.textContent);send('kasse','pflichtfeld',(lt&&lt.length<=40&&!el.closest('.wc-block-components-checkbox')&&mt.indexOf(lt)===-1?lt+': ':'')+mt);
          });
        },800);
      },true);
      <?php if ($watch_js): ?>
      /* Technische Fehler auf Kasse/Warenkorb (verhindern evtl. das Bestellen) */
      window.addEventListener('error',function(e){
        var f=String(e.filename||'');
        if(!e.message||/extension:|Script error|ResizeObserver/i.test(e.message+f))return;
        send('js','js',e.message+' @ '+f.split('/').pop().split('?')[0]+':'+(e.lineno||0));
      });
      <?php endif; ?>
    })();
    </script>
    <?php
}, 98);

function sp_an_ajax_err() {
    if (!function_exists('sp_an_should_track') || !sp_an_should_track()) {
        wp_die('', '', array('response' => 204));
    }
    global $wpdb;
    sp_an_err_install();
    $vh = sp_an_visitor_hash();
    $day = current_time('Y-m-d');
    $count = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . sp_an_err_table() . ' WHERE vh = %s AND day = %s', $vh, $day));
    if ($count >= 40) {
        wp_die('', '', array('response' => 204));
    }
    $area = isset($_POST['a']) ? sanitize_key(wp_unslash($_POST['a'])) : '';
    if (!in_array($area, array('kasse', 'warenkorb', 'gutschein', 'js'), true)) {
        wp_die('', '', array('response' => 204));
    }
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $app = '';
    if (preg_match('/BytedanceWebview|musical_ly|TikTok|trill_/i', $ua)) {
        $app = 'tiktok';
    } elseif (preg_match('/Instagram/i', $ua)) {
        $app = 'instagram';
    } elseif (preg_match('/FBAN|FBAV|FB_IAB/i', $ua)) {
        $app = 'facebook';
    }
    $wpdb->insert(sp_an_err_table(), array(
        'ts' => current_time('mysql'),
        'day' => $day,
        'vh' => $vh,
        'area' => $area,
        'code' => substr(sanitize_text_field(wp_unslash($_POST['c'] ?? '')), 0, 80),
        'msg' => substr(sanitize_text_field(wp_unslash($_POST['m'] ?? '')), 0, 255),
        'page' => substr(sanitize_text_field(wp_unslash($_POST['p'] ?? '')), 0, 100),
        'device' => sp_an_device($ua),
        'app' => $app,
    ));
    if (mt_rand(1, 200) === 1) {
        $wpdb->query($wpdb->prepare('DELETE FROM ' . sp_an_err_table() . ' WHERE day < %s', date('Y-m-d', strtotime('-180 days', current_time('timestamp')))));
    }
    wp_die('', '', array('response' => 204));
}
add_action('wp_ajax_sp_an_err', 'sp_an_ajax_err');
add_action('wp_ajax_nopriv_sp_an_err', 'sp_an_ajax_err');

/* -----------------------------------------------------------------------
 * Auswertungen
 * ---------------------------------------------------------------------*/

function sp_an_err_area_label($area) {
    $labels = array('kasse' => 'Kasse', 'warenkorb' => 'Warenkorb', 'gutschein' => 'Gutschein', 'js' => 'Technischer Fehler');
    return $labels[$area] ?? $area;
}

function sp_an_payment_label($method, $order = null) {
    $labels = array('bacs' => 'Vorkasse (Überweisung)', 'nowpayments' => 'Krypto', 'sp_wallet' => 'Guthaben', 'cod' => 'Nachnahme');
    if (isset($labels[$method])) {
        return $labels[$method];
    }
    if (strpos($method, 'nowpayments') !== false) {
        return 'Krypto';
    }
    return ($order && $order->get_payment_method_title()) ? $order->get_payment_method_title() : ($method ?: 'unbekannt');
}

function sp_an_median($values) {
    if (!$values) {
        return null;
    }
    sort($values);
    $n = count($values);
    return $n % 2 ? $values[intdiv($n, 2)] : ($values[$n / 2 - 1] + $values[$n / 2]) / 2;
}

/** Vom Kunden selbst aufgegebene Bestellungen im Zeitraum (keine automatischen Abo-Abbuchungen, keine Tests). */
function sp_an_customer_orders($start, $end) {
    $orders = wc_get_orders(array(
        'limit' => -1,
        'type' => 'shop_order',
        'status' => array('pending', 'on-hold', 'processing', 'completed', 'cancelled', 'failed', 'refunded'),
        'date_created' => $start . '...' . $end . ' 23:59:59',
        'return' => 'objects',
    ));
    return array_values(array_filter($orders, function ($o) {
        if (sp_an_is_test_order($o)) {
            return false;
        }
        return !($o->get_payment_method() === 'sp_wallet' && !in_array($o->get_created_via(), array('store-api', 'checkout'), true));
    }));
}

function sp_an_payment_stats($start, $end) {
    $now = current_time('timestamp');
    $rows = array();
    foreach (sp_an_customer_orders($start, $end) as $o) {
        $m = $o->get_payment_method() ?: 'unbekannt';
        if (!isset($rows[$m])) {
            $rows[$m] = array('label' => sp_an_payment_label($m, $o), 'orders' => 0, 'paid' => 0, 'paid_sum' => 0.0, 'open' => 0, 'open_sum' => 0.0, 'open_old' => 0, 'lost' => 0, 'lost_sum' => 0.0, 'days' => array());
        }
        $r =& $rows[$m];
        $r['orders']++;
        $total = (float) $o->get_total();
        $created = $o->get_date_created() ? $o->get_date_created()->getTimestamp() : $now;
        if ($o->get_date_paid()) {
            $r['paid']++;
            $r['paid_sum'] += $total;
            $r['days'][] = max(0, ($o->get_date_paid()->getTimestamp() - $created) / DAY_IN_SECONDS);
        } elseif (in_array($o->get_status(), array('cancelled', 'failed'), true)) {
            $r['lost']++;
            $r['lost_sum'] += $total;
        } else {
            $r['open']++;
            $r['open_sum'] += $total;
            if ($now - $created > 7 * DAY_IN_SECONDS) {
                $r['open_old']++;
            }
        }
        unset($r);
    }
    uasort($rows, function ($a, $b) {
        return $b['orders'] <=> $a['orders'];
    });
    return $rows;
}

/** Kundenbindung seit Shop-Start. */
function sp_an_retention_stats() {
    $orders = wc_get_orders(array(
        'limit' => -1,
        'type' => 'shop_order',
        'status' => array('processing', 'completed', 'refunded'),
        'orderby' => 'date',
        'order' => 'ASC',
        'return' => 'objects',
    ));
    $commissions = function_exists('sp_an_commission_map') ? sp_an_commission_map() : array();
    $customers = array();
    foreach ($orders as $o) {
        if (sp_an_is_test_order($o) || !$o->get_date_paid()) {
            continue;
        }
        $id = function_exists('sp_dashboard_customer_identity') ? sp_dashboard_customer_identity($o) : ($o->get_customer_id() ? 'u' . $o->get_customer_id() : 'e' . strtolower($o->get_billing_email()));
        if (!$id) {
            continue;
        }
        $topup = function_exists('sp_dashboard_order_topup_total') ? sp_dashboard_order_topup_total($o) : 0;
        $net = max(0.0, (float) $o->get_total() - (float) $o->get_total_refunded());
        $is_purchase = ($net - $topup) > 0 || $o->get_payment_method() === 'sp_wallet';
        if (!isset($customers[$id])) {
            if (!$is_purchase) {
                continue; // erste "Bestellung" nur eine Aufladung - Kunde zaehlt ab dem ersten Warenkauf
            }
            list($src) = function_exists('sp_an_order_source') ? sp_an_order_source($o) : array('direkt');
            $customers[$id] = array('first' => $o->get_date_created()->getTimestamp(), 'dates' => array(), 'value' => 0.0, 'commission' => 0.0, 'src' => $src);
        }
        $c =& $customers[$id];
        if ($is_purchase) {
            $c['dates'][] = $o->get_date_created()->getTimestamp();
        }
        // Geld, das tatsaechlich vom Kunden kam: Guthaben-Abbuchungen nicht doppelt zaehlen.
        if ($o->get_payment_method() !== 'sp_wallet') {
            $c['value'] += $net;
        }
        if (isset($commissions[$o->get_id()])) {
            $c['commission'] += $commissions[$o->get_id()][1];
        }
        unset($c);
    }
    $now = current_time('timestamp');
    $tot = array('customers' => 0, 'repeat' => 0, 'repeat3' => 0, 'mature' => 0, 'mature_repeat' => 0, 'value' => 0.0, 'purchases' => 0, 'gaps' => array());
    $by_src = array();
    foreach ($customers as $c) {
        $n = count($c['dates']);
        $tot['customers']++;
        $tot['purchases'] += $n;
        $tot['value'] += $c['value'];
        if ($n >= 2) {
            $tot['repeat']++;
            $tot['gaps'][] = ($c['dates'][1] - $c['dates'][0]) / DAY_IN_SECONDS;
        }
        if ($n >= 3) {
            $tot['repeat3']++;
        }
        if ($now - $c['first'] > 30 * DAY_IN_SECONDS) {
            $tot['mature']++;
            if ($n >= 2) {
                $tot['mature_repeat']++;
            }
        }
        $g = $c['src'] === 'partner' ? 'partner' : (strpos($c['src'], 'eigen-') === 0 ? 'eigen' : ($c['src'] === 'direkt' ? 'direkt' : 'sonstige'));
        if (!isset($by_src[$g])) {
            $by_src[$g] = array('customers' => 0, 'repeat' => 0, 'value' => 0.0, 'commission' => 0.0);
        }
        $by_src[$g]['customers']++;
        $by_src[$g]['value'] += $c['value'];
        $by_src[$g]['commission'] += $c['commission'];
        if ($n >= 2) {
            $by_src[$g]['repeat']++;
        }
    }
    uasort($by_src, function ($a, $b) {
        return $b['customers'] <=> $a['customers'];
    });
    return array('tot' => $tot, 'by_src' => $by_src);
}

function sp_an_abo_stats($start, $end) {
    global $wpdb;
    $table = $wpdb->prefix . 'sp_subscriptions';
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        return null;
    }
    $test = implode(',', sp_an_test_user_ids());
    $subs = $wpdb->get_results("SELECT * FROM {$table} WHERE user_id NOT IN ({$test})");
    $s = array('active' => 0, 'active_customers' => array(), 'paused' => 0, 'paused_funds' => 0, 'cancelled' => 0, 'new' => 0, 'cancelled_range' => 0, 'mrr' => 0.0, 'due7' => 0.0, 'due7_n' => 0);
    $today = current_time('Y-m-d');
    $in7 = date('Y-m-d', strtotime('+7 days', current_time('timestamp')));
    foreach ($subs as $sub) {
        $created = substr($sub->created_at, 0, 10);
        if ($created >= $start && $created <= $end) {
            $s['new']++;
        }
        if ($sub->status === 'active') {
            $s['active']++;
            $s['active_customers'][(int) $sub->user_id] = true;
            $product = wc_get_product((int) $sub->variation_id ?: (int) $sub->product_id);
            if ($product && function_exists('sp_abo_calculate_price')) {
                $price = sp_abo_calculate_price($sub, $product);
                $s['mrr'] += $price * 30 / max(1, (int) $sub->interval_days);
                if ($sub->next_payment_date >= $today && $sub->next_payment_date <= $in7) {
                    $s['due7'] += $price;
                    $s['due7_n']++;
                }
            }
        } elseif ($sub->status === 'paused') {
            $s['paused']++;
            if ($sub->paused_reason === 'insufficient_funds') {
                $s['paused_funds']++;
            }
        } elseif ($sub->status === 'cancelled') {
            $s['cancelled']++;
            $upd = substr($sub->updated_at, 0, 10);
            if ($upd >= $start && $upd <= $end) {
                $s['cancelled_range']++;
            }
        }
    }
    $s['active_customers'] = count($s['active_customers']);
    return $s;
}

/* -----------------------------------------------------------------------
 * Karten im Analyse-Tab
 * ---------------------------------------------------------------------*/

add_action('sp_an_render_insights', function ($start, $end, $since) {
    global $wpdb;
    sp_an_err_install();

    /* 1. Kassen-Fehler */
    $errs = $wpdb->get_results($wpdb->prepare('SELECT vh, day, area, code, msg, device, app, ts FROM ' . sp_an_err_table() . ' WHERE day BETWEEN %s AND %s ORDER BY ts DESC', $start, $end));
    $ordered = array();
    foreach ($wpdb->get_results($wpdb->prepare('SELECT DISTINCT vh, day FROM ' . sp_an_table() . " WHERE type = 'order' AND day BETWEEN %s AND %s", $start, $end)) as $r) {
        $ordered[$r->vh . '|' . $r->day] = true;
    }
    $groups = array();
    $affected = array();
    foreach ($errs as $e) {
        $k = $e->area . '|' . $e->msg;
        $vk = $e->vh . '|' . $e->day;
        $affected[$vk] = true;
        if (!isset($groups[$k])) {
            $groups[$k] = array('area' => $e->area, 'code' => $e->code, 'msg' => $e->msg, 'n' => 0, 'visitors' => array(), 'apps' => array(), 'last' => $e->ts);
        }
        $groups[$k]['n']++;
        $groups[$k]['visitors'][$vk] = true;
        if ($e->app !== '') {
            $groups[$k]['apps'][$e->app] = true;
        }
    }
    uasort($groups, function ($a, $b) {
        return count($b['visitors']) <=> count($a['visitors']);
    });
    $affected_ordered = count(array_intersect_key($affected, $ordered));
    ?>
    <div class="sp-dash-card">
      <h2>Kassen-Fehler – was sehen Kunden, bevor sie abbrechen?</h2>
      <?php if (!$groups): ?>
        <p class="sp-dash-empty">Im Zeitraum keine Fehler protokolliert<?php echo $start <= $since ? ' (Protokoll läuft seit ' . esc_html(date_i18n('d.m.Y', strtotime($since))) . ')' : ''; ?>.</p>
      <?php else: ?>
        <p><strong><?php echo esc_html(count($affected)); ?> Besucher</strong> haben mindestens eine Fehlermeldung gesehen – davon haben <strong><?php echo esc_html($affected_ordered); ?></strong> trotzdem bestellt, <strong><?php echo esc_html(count($affected) - $affected_ordered); ?></strong> nicht.</p>
        <table class="sp-dash-table">
          <thead><tr><th>Bereich</th><th>Meldung</th><th>Besucher</th><th>Anzahl</th><th>App</th><th>Zuletzt</th></tr></thead>
          <tbody>
          <?php foreach (array_slice($groups, 0, 30) as $g):
              $no_order = count(array_diff_key($g['visitors'], $ordered)); ?>
            <tr>
              <td><?php echo esc_html(sp_an_err_area_label($g['area'])); ?></td>
              <td><?php echo esc_html($g['msg'] !== '' ? $g['msg'] : $g['code']); ?><?php if ($g['code'] !== '' && !in_array($g['code'], array('hinweis', 'pflichtfeld', 'js'), true)): ?><br><span class="sp-an-muted"><?php echo esc_html($g['code']); ?></span><?php endif; ?></td>
              <td><?php echo esc_html(count($g['visitors'])); ?><?php if ($no_order): ?> <span class="sp-an-muted">(<?php echo esc_html($no_order); ?> ohne Bestellung)</span><?php endif; ?></td>
              <td><?php echo esc_html($g['n']); ?></td>
              <td class="sp-an-muted"><?php echo esc_html($g['apps'] ? implode(', ', array_map('ucfirst', array_keys($g['apps']))) : '–'); ?></td>
              <td class="sp-an-muted"><?php echo esc_html(date_i18n('d.m. H:i', strtotime($g['last']))); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      <p class="sp-an-muted">Erfasst werden nur die Fehlermeldungen selbst (keine Eingaben der Kunden): Fehler beim Bestellen, Gutschein- und Warenkorb-Fehler, rote Hinweise, beim Klick auf „Bestellen“ noch offene Pflichtfelder und technische Fehler auf Kasse/Warenkorb. „App“ = Kunde war im TikTok-/Instagram-App-Browser.</p>
    </div>
    <?php

    /* 2. Zahlungsarten */
    $pay = sp_an_payment_stats($start, $end);
    ?>
    <div class="sp-dash-card">
      <h2>Zahlungsarten – wie viele Bestellungen werden wirklich bezahlt?</h2>
      <?php if (!$pay): ?>
        <p class="sp-dash-empty">Keine Bestellungen im Zeitraum.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Zahlungsart</th><th>Bestellungen</th><th>Bezahlt</th><th>Noch offen</th><th>Storniert / fehlgeschlagen</th><th>Zahlquote</th><th>Ø Tage bis Zahlung</th></tr></thead>
          <tbody>
          <?php foreach ($pay as $m => $r):
              $decided = $r['paid'] + $r['lost'];
              $med = sp_an_median($r['days']); ?>
            <tr>
              <td><strong><?php echo esc_html($r['label']); ?></strong></td>
              <td><?php echo esc_html($r['orders']); ?></td>
              <td><?php echo esc_html($r['paid']); ?> <span class="sp-an-muted">(<?php echo sp_dashboard_money($r['paid_sum']); ?>)</span></td>
              <td><?php echo esc_html($r['open']); ?><?php if ($r['open']): ?> <span class="sp-an-muted">(<?php echo sp_dashboard_money($r['open_sum']); ?><?php echo $r['open_old'] ? ', ' . esc_html($r['open_old']) . ' älter als 7 Tage' : ''; ?>)</span><?php endif; ?></td>
              <td><?php echo esc_html($r['lost']); ?><?php if ($r['lost']): ?> <span class="sp-an-muted">(<?php echo sp_dashboard_money($r['lost_sum']); ?>)</span><?php endif; ?></td>
              <td><?php echo $decided ? esc_html(sp_an_fmt_pct($r['paid'] / $decided * 100)) : '–'; ?></td>
              <td><?php echo $med === null ? '–' : esc_html(number_format_i18n($med, 1)); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p class="sp-an-muted">Zahlquote = bezahlt ÷ (bezahlt + storniert/fehlgeschlagen); noch offene Bestellungen zählen erst mit, wenn sie entschieden sind. Ø Tage = Median von Bestellung bis Zahlungseingang. Automatische Abo-Abbuchungen aus Guthaben und Testbestellungen sind nicht enthalten.</p>
      <?php endif; ?>
    </div>
    <?php

    /* 3. Kundenbindung & Abos */
    $ret = sp_an_retention_stats();
    $t = $ret['tot'];
    $abo = sp_an_abo_stats($start, $end);
    $src_labels = array('partner' => 'Partner (Affiliates)', 'eigen' => 'Eigene Links (TikTok/Instagram)', 'direkt' => 'Direkt / unbekannt', 'sonstige' => 'Sonstige (Suche, App ohne Link …)');
    ?>
    <div class="sp-dash-card">
      <h2>Kundenbindung – kommen Kunden wieder? <span class="sp-an-muted">(seit Shop-Start, unabhängig vom Zeitraum)</span></h2>
      <?php if (!$t['customers']): ?>
        <p class="sp-dash-empty">Noch keine bezahlten Bestellungen.</p>
      <?php else:
          $gap = sp_an_median($t['gaps']); ?>
        <div class="sp-dash-status-grid" style="margin-bottom:16px">
          <div class="sp-dash-status-tile"><div class="l">Kunden</div><div class="n"><?php echo esc_html($t['customers']); ?></div><div class="v">mit mind. 1 bezahltem Kauf</div></div>
          <div class="sp-dash-status-tile"><div class="l">Wiederkäufer</div><div class="n"><?php echo esc_html(sp_an_fmt_pct(sp_an_pct($t['repeat'], $t['customers']))); ?></div><div class="v"><?php echo esc_html($t['repeat']); ?> Kunden mit 2+ Käufen, <?php echo esc_html($t['repeat3']); ?> mit 3+</div></div>
          <div class="sp-dash-status-tile"><div class="l">Wiederkauf (Kunden &gt; 30 Tage)</div><div class="n"><?php echo $t['mature'] ? esc_html(sp_an_fmt_pct(sp_an_pct($t['mature_repeat'], $t['mature']))) : '–'; ?></div><div class="v">fairer Wert: nur Kunden, die schon Zeit zum Nachkaufen hatten (<?php echo esc_html($t['mature']); ?>)</div></div>
          <div class="sp-dash-status-tile"><div class="l">Zeit bis 2. Kauf</div><div class="n"><?php echo $gap === null ? '–' : esc_html(number_format_i18n($gap, 0)) . ' Tage'; ?></div><div class="v">Median</div></div>
          <div class="sp-dash-status-tile"><div class="l">Ø Kundenwert</div><div class="n"><?php echo sp_dashboard_money($t['value'] / $t['customers']); ?></div><div class="v">eingenommen pro Kunde bisher</div></div>
        </div>
        <table class="sp-dash-table">
          <thead><tr><th>Erstkauf über</th><th>Kunden</th><th>Wiederkäufer</th><th>Ø Kundenwert</th><th>Ø Provision</th><th>Ø Wert nach Provision</th></tr></thead>
          <tbody>
          <?php foreach ($ret['by_src'] as $g => $x): ?>
            <tr>
              <td><strong><?php echo esc_html($src_labels[$g] ?? $g); ?></strong></td>
              <td><?php echo esc_html($x['customers']); ?></td>
              <td><?php echo esc_html(sp_an_fmt_pct(sp_an_pct($x['repeat'], $x['customers']))); ?></td>
              <td><?php echo sp_dashboard_money($x['value'] / $x['customers']); ?></td>
              <td><?php echo sp_dashboard_money($x['commission'] / $x['customers']); ?></td>
              <td><strong><?php echo sp_dashboard_money(($x['value'] - $x['commission']) / $x['customers']); ?></strong></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p class="sp-an-muted">Kunde = Kundenkonto bzw. E-Mail bei Gastbestellungen. Kundenwert = tatsächlich eingenommenes Geld (inkl. Guthaben-Aufladungen, Abo-Lieferungen aus Guthaben nicht doppelt). Provision = SliceWP-Provisionen auf alle Bestellungen dieses Kunden. Solange der Shop jung ist, sind Wiederkauf-Werte noch wenig aussagekräftig – die Spalte „Kunden &gt; 30 Tage“ zählt nur Kunden, die schon Zeit zum Nachkaufen hatten.</p>
      <?php endif; ?>

      <?php if ($abo !== null): ?>
        <h2 style="margin-top:22px">Abos</h2>
        <div class="sp-dash-status-grid">
          <div class="sp-dash-status-tile"><div class="l">Aktive Abo-Positionen</div><div class="n"><?php echo esc_html($abo['active']); ?></div><div class="v">bei <?php echo esc_html($abo['active_customers']); ?> Kunden</div></div>
          <div class="sp-dash-status-tile"><div class="l">Abo-Umsatz pro Monat</div><div class="n"><?php echo sp_dashboard_money($abo['mrr']); ?></div><div class="v">hochgerechnet aus aktiven Abos</div></div>
          <div class="sp-dash-status-tile"><div class="l">Fällig nächste 7 Tage</div><div class="n"><?php echo sp_dashboard_money($abo['due7']); ?></div><div class="v"><?php echo esc_html($abo['due7_n']); ?> Abbuchung(en)</div></div>
          <div class="sp-dash-status-tile"><div class="l">Pausiert</div><div class="n"><?php echo esc_html($abo['paused']); ?></div><div class="v"><?php echo esc_html($abo['paused_funds']); ?> davon wegen fehlendem Guthaben</div></div>
          <div class="sp-dash-status-tile"><div class="l">Neu / gekündigt im Zeitraum</div><div class="n"><?php echo esc_html($abo['new']); ?> / <?php echo esc_html($abo['cancelled_range']); ?></div><div class="v"><?php echo esc_html($abo['cancelled']); ?> gekündigt insgesamt</div></div>
        </div>
        <p class="sp-an-muted">Ohne Testkunden. Details zu einzelnen Abos: Peptrium Dashboard → Abo-Verwaltung.</p>
      <?php endif; ?>
    </div>
    <?php
}, 10, 3);

/* -----------------------------------------------------------------------
 * 4. Geschenkstufen, 5. Zubehoer/Abo/Gutscheine (2026-10-09)
 * ---------------------------------------------------------------------*/

/** Bezahlte, vom Kunden aufgegebene Bestellungen im Zeitraum mit Warenwert wie bei den Geschenkstufen. */
function sp_an_paid_order_details($start, $end) {
    $out = array();
    foreach (sp_an_customer_orders($start, $end) as $o) {
        if (!$o->get_date_paid()) {
            continue;
        }
        $basis = 0.0;
        $items = array();
        $gifts = array();
        $abo = false;
        foreach ($o->get_items() as $item) {
            if ($item->get_meta('_sp_wallet_amount')) {
                continue;
            }
            $pid = (int) $item->get_product_id();
            if ($item->get_meta('Geschenk')) {
                $gifts[] = $item;
                continue;
            }
            // Gleiche Basis wie sp_gift_non_gift_subtotal(): Artikelpreis vor Gutscheinen, ohne Geschenke/Aufladungen.
            $basis += (float) $item->get_subtotal() + (float) $item->get_subtotal_tax();
            $items[$pid] = ($items[$pid] ?? 0.0) + (float) $item->get_total() + (float) $item->get_total_tax();
            if ($item->get_meta('_sp_abo_interval_days')) {
                $abo = true;
            }
        }
        if (!$items) {
            continue; // reine Aufladung
        }
        $out[] = array('order' => $o, 'basis' => $basis, 'items' => $items, 'gifts' => $gifts, 'abo' => $abo);
    }
    return $out;
}

function sp_an_gift_thresholds() {
    $t = array(100 => 'Gratisversand');
    if (function_exists('sp_gift_tiers')) {
        foreach (sp_gift_tiers() as $tier) {
            $th = (int) $tier['threshold'];
            $t[$th] = isset($t[$th]) ? $t[$th] . ' + ' . $tier['label'] : $tier['label'];
        }
    }
    ksort($t);
    return $t;
}

function sp_an_affiliate_coupon_codes() {
    $map = array();
    if (!function_exists('slicewp_get_affiliates') || !function_exists('slicewp_get_affiliate_meta')) {
        return $map;
    }
    foreach (slicewp_get_affiliates(array('number' => -1)) as $aff) {
        $code = strtolower((string) slicewp_get_affiliate_meta($aff->get('id'), 'sp_coupon_code', true));
        if ($code !== '') {
            $map[$code] = (int) $aff->get('id');
        }
    }
    return $map;
}

add_action('sp_an_render_insights', function ($start, $end, $since) {
    $orders = sp_an_paid_order_details($start, $end);

    /* 4. Geschenkstufen */
    $thresholds = sp_an_gift_thresholds();
    $step = 25;
    $buckets = array();
    $max_bucket = 600;
    foreach ($orders as $d) {
        $b = (int) min($max_bucket, floor($d['basis'] / $step) * $step);
        $buckets[$b] = ($buckets[$b] ?? 0) + 1;
    }
    $near = array();
    foreach ($thresholds as $t => $label) {
        $near[$t] = array('below' => 0, 'above' => 0);
        foreach ($orders as $d) {
            if ($d['basis'] >= $t - 30 && $d['basis'] < $t) {
                $near[$t]['below']++;
            } elseif ($d['basis'] >= $t && $d['basis'] < $t + 30) {
                $near[$t]['above']++;
            }
        }
    }
    $gift_sum = array();
    foreach ($orders as $d) {
        foreach ($d['gifts'] as $g) {
            $p = $g->get_product();
            $name = $g->get_name();
            if (!isset($gift_sum[$name])) {
                $gift_sum[$name] = array('n' => 0, 'value' => 0.0);
            }
            $gift_sum[$name]['n'] += $g->get_quantity();
            $gift_sum[$name]['value'] += $p ? (float) $p->get_regular_price() * $g->get_quantity() : 0.0;
        }
    }
    $maxn = $buckets ? max($buckets) : 1;
    ?>
    <div class="sp-dash-card">
      <h2>Geschenkstufen – legen Kunden nach, um das Geschenk zu bekommen?</h2>
      <?php if (!$orders): ?>
        <p class="sp-dash-empty">Keine bezahlten Bestellungen im Zeitraum.</p>
      <?php else: ?>
        <div class="sp-an-small">
          <div>
            <p class="sp-an-muted" style="margin-top:0">Bestellwerte (Warenwert ohne Geschenke/Aufladungen, vor Gutscheinen – wie bei den Geschenkstufen), in 25-€-Schritten:</p>
            <?php for ($b = 0; $b <= $max_bucket; $b += $step):
                $n = $buckets[$b] ?? 0;
                $mark = '';
                foreach ($thresholds as $t => $label) {
                    if ($t >= $b && $t < $b + $step) {
                        $mark = '🎁 ' . $t . ' €: ' . $label;
                    }
                }
                if (!$n && !$mark) {
                    continue;
                } ?>
              <div class="sp-an-funnel-row" style="grid-template-columns:90px 1fr 40px;margin-bottom:4px">
                <span class="sp-an-muted"><?php echo esc_html($b >= $max_bucket ? 'ab ' . $max_bucket . ' €' : $b . '–' . ($b + $step - 1) . ' €'); ?></span>
                <div class="sp-an-funnel-bar" style="height:16px"><span style="width:<?php echo esc_attr($n ? max(3, round($n / $maxn * 100)) : 0); ?>%"></span></div>
                <span><?php echo esc_html($n); ?></span>
              </div>
              <?php if ($mark): ?><div class="sp-an-muted" style="margin:-2px 0 6px 102px;color:#B45309;font-weight:600"><?php echo esc_html($mark); ?></div><?php endif; ?>
            <?php endfor; ?>
          </div>
          <div>
            <table class="sp-dash-table">
              <thead><tr><th>Stufe</th><th>knapp darunter<br><span class="sp-an-muted">(bis 30 € fehlen)</span></th><th>knapp erreicht<br><span class="sp-an-muted">(bis 30 € drüber)</span></th></tr></thead>
              <tbody>
              <?php foreach ($thresholds as $t => $label): ?>
                <tr><td><strong><?php echo esc_html($t); ?> €</strong><br><span class="sp-an-muted"><?php echo esc_html($label); ?></span></td><td><?php echo esc_html($near[$t]['below']); ?></td><td><?php echo esc_html($near[$t]['above']); ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <p class="sp-an-muted">Viele „knapp erreicht“ = Kunden legen für die Stufe nach (sie wirkt). Viele „knapp darunter“ = Kunden scheitern knapp – ein Hinweis „Nur noch X € bis …“ oder eine niedrigere Stufe könnte helfen.</p>
            <?php if ($gift_sum): ?>
              <table class="sp-dash-table" style="margin-top:12px">
                <thead><tr><th>Verschenkt im Zeitraum</th><th>Stück</th><th>Ladenpreis</th></tr></thead>
                <tbody>
                <?php foreach ($gift_sum as $name => $x): ?>
                  <tr><td><?php echo esc_html($name); ?></td><td><?php echo esc_html($x['n']); ?></td><td><?php echo sp_dashboard_money($x['value']); ?></td></tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <?php

    /* 5. Zubehoer, Abo, Gutscheine */
    $acc_ids = array();
    $peptide_orders = 0;
    $attach = array();
    $abo_orders = 0;
    $coupons = array();
    $aff_codes = sp_an_affiliate_coupon_codes();
    foreach ($orders as $d) {
        $has_peptide = false;
        $acc_in_order = array();
        foreach ($d['items'] as $pid => $value) {
            if (!isset($acc_ids[$pid])) {
                $acc_ids[$pid] = has_term(20, 'product_cat', $pid);
            }
            if ($acc_ids[$pid]) {
                $acc_in_order[$pid] = $value;
            } else {
                $has_peptide = true;
            }
        }
        if ($has_peptide) {
            $peptide_orders++;
            foreach ($acc_in_order as $pid => $value) {
                if (!isset($attach[$pid])) {
                    $attach[$pid] = array('n' => 0, 'value' => 0.0);
                }
                $attach[$pid]['n']++;
                $attach[$pid]['value'] += $value;
            }
        }
        if ($d['abo']) {
            $abo_orders++;
        }
        foreach ($d['order']->get_items('coupon') as $c) {
            $code = strtolower($c->get_code());
            if (!isset($coupons[$code])) {
                $coupons[$code] = array('n' => 0, 'discount' => 0.0);
            }
            $coupons[$code]['n']++;
            $coupons[$code]['discount'] += (float) $c->get_discount() + (float) $c->get_discount_tax();
        }
    }
    uasort($attach, function ($a, $b) {
        return $b['n'] <=> $a['n'];
    });
    uasort($coupons, function ($a, $b) {
        return $b['n'] <=> $a['n'];
    });
    // Zubehoer, das nie mitgekauft wurde, trotzdem zeigen - ohne reine Geschenk-/Aufladeprodukte.
    $skip = array(SP_AN_TOPUP_PRODUCT_ID => true, 745 => true, 817 => true);
    $acc_term = get_term(20, 'product_cat');
    foreach ($acc_term && !is_wp_error($acc_term) ? wc_get_products(array('status' => 'publish', 'limit' => -1, 'category' => array($acc_term->slug), 'return' => 'ids')) : array() as $pid) {
        if (!isset($attach[$pid]) && !isset($skip[$pid])) {
            $attach[$pid] = array('n' => 0, 'value' => 0.0);
        }
    }
    ?>
    <div class="sp-dash-card">
      <h2>Zubehör, Abo & Gutscheine – was wird mitgekauft?</h2>
      <?php if (!$orders): ?>
        <p class="sp-dash-empty">Keine bezahlten Bestellungen im Zeitraum.</p>
      <?php else: ?>
        <div class="sp-dash-status-grid" style="margin-bottom:16px">
          <div class="sp-dash-status-tile"><div class="l">Bestellungen mit Peptid</div><div class="n"><?php echo esc_html($peptide_orders); ?></div><div class="v">von <?php echo esc_html(count($orders)); ?> bezahlten</div></div>
          <div class="sp-dash-status-tile"><div class="l">Mit Abo-Artikel</div><div class="n"><?php echo esc_html(sp_an_fmt_pct(sp_an_pct($abo_orders, count($orders)))); ?></div><div class="v"><?php echo esc_html($abo_orders); ?> Bestellung(en) mit „Im Abo“</div></div>
          <div class="sp-dash-status-tile"><div class="l">Mit Gutschein</div><div class="n"><?php echo esc_html(sp_an_fmt_pct(sp_an_pct(array_sum(array_column($coupons, 'n')), count($orders)))); ?></div><div class="v"><?php echo sp_dashboard_money(array_sum(array_column($coupons, 'discount'))); ?> Rabatt gesamt</div></div>
        </div>
        <div class="sp-an-small">
          <div>
            <table class="sp-dash-table">
              <thead><tr><th>Zubehör</th><th>in Peptid-Bestellungen</th><th>Umsatz</th></tr></thead>
              <tbody>
              <?php foreach ($attach as $pid => $x):
                  $p = wc_get_product($pid);
                  if (!$p) {
                      continue;
                  } ?>
                <tr><td><?php echo esc_html($p->get_name()); ?></td><td><?php echo esc_html($x['n']); ?> <span class="sp-an-muted">(<?php echo esc_html(sp_an_fmt_pct(sp_an_pct($x['n'], $peptide_orders))); ?>)</span></td><td><?php echo sp_dashboard_money($x['value']); ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <p class="sp-an-muted">Anteil der Bestellungen mit mindestens einem Peptid, in denen das Zubehör mitgekauft wurde (über Zubehör-Schalter, Warenkorb-Vorschlag oder direkt). Gratis-Geschenke zählen nicht.</p>
          </div>
          <div>
            <table class="sp-dash-table">
              <thead><tr><th>Gutschein</th><th>Bestellungen</th><th>Rabatt</th></tr></thead>
              <tbody>
              <?php if (!$coupons): ?>
                <tr><td colspan="3" class="sp-an-muted">Keine Gutscheine im Zeitraum.</td></tr>
              <?php endif; ?>
              <?php foreach ($coupons as $code => $x): ?>
                <tr><td><strong><?php echo esc_html(strtoupper($code)); ?></strong><?php if (isset($aff_codes[$code])): ?><br><span class="sp-an-muted">Partner: <?php echo esc_html(sp_an_affiliate_name($aff_codes[$code])); ?></span><?php endif; ?></td><td><?php echo esc_html($x['n']); ?></td><td><?php echo sp_dashboard_money($x['discount']); ?></td></tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <?php
}, 20, 3);

/* -----------------------------------------------------------------------
 * 6. Clarity: Herkunft als Tag, damit Aufnahmen danach filterbar sind
 * ---------------------------------------------------------------------*/

/** Herkunft des aktuellen Besuchers heute (gleiche Regel wie im Analyse-Tab). */
function sp_an_visitor_source_today() {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare('SELECT src, campaign FROM ' . sp_an_table() . " WHERE vh = %s AND day = %s AND type IN ('pv','link') AND src <> '' ORDER BY ts ASC", sp_an_visitor_hash(), current_time('Y-m-d')));
    $src = '';
    $campaign = '';
    // Partner > eigener Link > erste echte Quelle (wie sp_an_get_visits()).
    foreach ($rows as $r) {
        if ($src === 'partner') {
            break;
        }
        if ($r->src === 'partner' || (strpos($r->src, 'eigen-') === 0 && strpos($src, 'eigen-') !== 0) || $src === '' || ($src === 'direkt' && $r->src !== 'direkt')) {
            $src = $r->src;
            $campaign = $r->campaign;
        }
    }
    if ($src === '') {
        list($src, $campaign) = sp_an_source_from_request(
            isset($_SERVER['QUERY_STRING']) ? (string) $_SERVER['QUERY_STRING'] : '',
            isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '',
            isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : ''
        );
    }
    return array($src !== '' ? $src : 'direkt', $campaign);
}

add_action('wp_footer', function () {
    if (is_admin() || !function_exists('sp_an_should_track') || !sp_an_should_track()) {
        return;
    }
    list($src, $campaign) = sp_an_visitor_source_today();
    $group = $src === 'partner' ? 'Partner' : (strpos($src, 'eigen-') === 0 ? 'Eigener Link' : sp_an_source_label($src));
    $tags = array('quelle' => $group, 'quelle_detail' => $src);
    if ($campaign !== '') {
        $tags['kampagne'] = $campaign;
    }
    ?>
    <script>
    (function(){var t=<?php echo wp_json_encode($tags); ?>,tries=0;
      (function go(){if(typeof clarity==='function'){for(var k in t){clarity('set',k,t[k]);}return;}if(++tries<20){setTimeout(go,1000);}})();
    })();
    </script>
    <?php
}, 101);

/* -----------------------------------------------------------------------
 * 7. Gratisversand-Hinweis in der Kasse: gesehen / "+" getippt / bestellt
 * ---------------------------------------------------------------------*/

function sp_an_fsh_stats($start, $end) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare('SELECT vh, day, type, product_id, campaign FROM ' . sp_an_table() . " WHERE day BETWEEN %s AND %s AND type IN ('fshview','fshclick','order')", $start, $end));
    $views = array();
    $clickers = array();
    $ordered = array();
    $clicks = 0;
    $value = 0.0;
    $prod = array();
    foreach ($rows as $r) {
        $k = $r->vh . '|' . $r->day;
        if ($r->type === 'fshview') {
            $views[$k] = true;
        } elseif ($r->type === 'fshclick') {
            $clickers[$k] = true;
            $clicks++;
            $parts = explode(':', $r->campaign);
            $value += (float) ($parts[1] ?? 0);
            $prod[(int) $r->product_id] = ($prod[(int) $r->product_id] ?? 0) + 1;
        } else {
            $ordered[$k] = true;
        }
    }
    arsort($prod);
    return array(
        'viewers' => count($views),
        'viewers_ordered' => count(array_intersect_key($views, $ordered)),
        'clickers' => count($clickers),
        'clickers_ordered' => count(array_intersect_key($clickers, $ordered)),
        'clicks' => $clicks,
        'value' => $value,
        'products' => $prod,
    );
}

add_action('sp_an_render_insights', function ($start, $end, $since) {
    $x = sp_an_fsh_stats($start, $end);
    ?>
    <div class="sp-dash-card">
      <h2>Gratisversand-Hinweis in der Kasse – bringt er etwas?</h2>
      <?php if (!$x['viewers']): ?>
        <p class="sp-dash-empty">Im Zeitraum hat noch niemand Vorschläge gesehen (Messung läuft seit 09.10.2026; Vorschläge erscheinen nur unter 100 € bei Lieferung nach Deutschland).</p>
      <?php else: ?>
        <div class="sp-dash-status-grid">
          <div class="sp-dash-status-tile"><div class="l">Vorschläge gesehen</div><div class="n"><?php echo esc_html($x['viewers']); ?></div><div class="v">Besucher · davon bestellt: <?php echo esc_html($x['viewers_ordered']); ?></div></div>
          <div class="sp-dash-status-tile"><div class="l">Auf „+“ getippt</div><div class="n"><?php echo esc_html($x['clickers']); ?></div><div class="v"><?php echo esc_html(sp_an_fmt_pct(sp_an_pct($x['clickers'], $x['viewers']))); ?> der Besucher · <?php echo esc_html($x['clicks']); ?> Klicks</div></div>
          <div class="sp-dash-status-tile"><div class="l">Danach bestellt</div><div class="n"><?php echo esc_html($x['clickers_ordered']); ?></div><div class="v">von <?php echo esc_html($x['clickers']); ?> Besuchern mit Klick</div></div>
          <div class="sp-dash-status-tile"><div class="l">Hinzugefügter Warenwert</div><div class="n"><?php echo sp_dashboard_money($x['value']); ?></div><div class="v">Summe der per „+“ hinzugefügten Artikel</div></div>
        </div>
        <?php if ($x['products']): ?>
          <p class="sp-an-muted" style="margin-top:12px">Am häufigsten hinzugefügt:
            <?php $names = array();
            foreach (array_slice($x['products'], 0, 5, true) as $pid => $n) {
                $p = wc_get_product($pid);
                $names[] = ($p ? $p->get_name() : '#' . $pid) . ' (' . $n . '×)';
            }
            echo esc_html(implode(', ', $names)); ?></p>
        <?php endif; ?>
        <p class="sp-an-muted">So liest du es: Bestellen Besucher mit Klick ähnlich oft oder öfter als alle, die die Vorschläge gesehen haben, schadet der Hinweis nicht und bringt Zusatzumsatz. Tippt fast niemand, kann er weg. Gezählt wird pro Besucher und Tag, ohne Cookies.</p>
      <?php endif; ?>
    </div>
    <?php
}, 15, 3);
