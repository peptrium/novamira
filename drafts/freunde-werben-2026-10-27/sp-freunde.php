<?php
/**
 * Plugin Name: SP Freunde werben
 * Description: Kunden werben Kunden (2026-10-27, Nutzer). Jeder zahlende Kunde bekommt einen eigenen Code FREUND-<NAME><2>:
 *              Freunde sparen damit 10 % (bei jeder Bestellung, nicht kombinierbar, nicht zusammen mit einem Partner-Link).
 *              Der Werber bekommt fuer jede NEUE Person, deren Bestellung bezahlt ist, einen eigenen 20-%-Code (DANKE-…, 1×, 60 Tage,
 *              an seine E-Mail gebunden, keine Obergrenze). Eigener Code ist gesperrt (Abgleich E-Mail inkl. +/Punkte, Konto, Liefer-/
 *              Rechnungsadresse, Telefon, Nachname+PLZ). Ab der 3. Empfehlung enthaelt die Belohnungsmail die Partner-Einladung.
 *              Mails ueber Omnisend: sp_freunde_start (14 Tage nach der Bezahlung + einmal an alle Bestandskunden beim Start),
 *              sp_freund_belohnung. Schalter/Uebersicht: Peptrium Dashboard → Freunde werben. Aus, bis Option sp_fw['on'] gesetzt ist
 *              (automatisch zum Zeitpunkt sp_fw['auto_on']).
 * Zuruecksetzen: Schalter aus (Codes werden dann abgelehnt); Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_fw_cfg() {
    return wp_parse_args((array) get_option('sp_fw', []), [
        'on' => 0, 'auto_on' => 0, 'since' => 0, 'friend' => 10, 'reward' => 20, 'reward_days' => 60, 'mail_days' => 14,
        'auto' => [], 'mailcard' => 0,
    ]);
}
function sp_fw_live() {
    return !empty(sp_fw_cfg()['on']);
}
function sp_fw_log($msg) {
    $log = (array) get_option('sp_fw_log', []);
    array_unshift($log, current_time('mysql') . ' – ' . $msg);
    update_option('sp_fw_log', array_slice($log, 0, 200), false);
}

/* -------------------------------------------------------------------
 * Identitaet (Missbrauchsschutz)
 * ------------------------------------------------------------------- */
function sp_fw_email_norm($e) {
    $e = strtolower(trim((string) $e));
    if (strpos($e, '@') === false) {
        return $e;
    }
    [$u, $d] = explode('@', $e, 2);
    $u = preg_replace('/\+.*$/', '', $u);
    if (in_array($d, ['gmail.com', 'googlemail.com'], true)) {
        $u = str_replace('.', '', $u);
        $d = 'gmail.com';
    }
    return $u . '@' . $d;
}
function sp_fw_norm($s) {
    $s = strtolower(remove_accents(trim((string) $s)));
    $s = str_replace(['strasse', 'straße', 'str.'], 'str', $s);
    return preg_replace('/[^a-z0-9]/', '', $s);
}
/** $d: email, phone, last, pc, pc2, street, street2 -> Liste von Merkmal-Hashes */
function sp_fw_ids(array $d) {
    $ids = [];
    if (!empty($d['email'])) {
        $ids[] = 'e' . md5(sp_fw_email_norm($d['email']));
    }
    $ph = preg_replace('/\D/', '', (string) ($d['phone'] ?? ''));
    if (strlen($ph) >= 7) {
        $ids[] = 'p' . md5(substr($ph, -8));
    }
    foreach ([['street', 'pc'], ['street2', 'pc2']] as [$s, $p]) {
        $st = sp_fw_norm($d[$s] ?? '');
        $pc = sp_fw_norm($d[$p] ?? '');
        if ($st !== '' && $pc !== '') {
            $ids[] = 'a' . md5($st . '|' . $pc);
        }
        $last = sp_fw_norm($d['last'] ?? '');
        if ($last !== '' && $pc !== '') {
            $ids[] = 'n' . md5($last . '|' . $pc);
        }
    }
    return array_values(array_unique($ids));
}
function sp_fw_order_ids($o) {
    return sp_fw_ids([
        'email' => $o->get_billing_email(), 'phone' => $o->get_billing_phone(), 'last' => $o->get_billing_last_name(),
        'street' => $o->get_billing_address_1(), 'pc' => $o->get_billing_postcode(),
        'street2' => $o->get_shipping_address_1(), 'pc2' => $o->get_shipping_postcode(),
    ]);
}
function sp_fw_customer_ids() {
    $ids = [];
    if (function_exists('WC') && WC()->customer) {
        $c = WC()->customer;
        $ids = sp_fw_ids([
            'email' => $c->get_billing_email(), 'phone' => $c->get_billing_phone(), 'last' => $c->get_billing_last_name() ?: $c->get_shipping_last_name(),
            'street' => $c->get_billing_address_1(), 'pc' => $c->get_billing_postcode(),
            'street2' => $c->get_shipping_address_1(), 'pc2' => $c->get_shipping_postcode(),
        ]);
    }
    if (is_user_logged_in()) {
        $ids = array_merge($ids, sp_fw_ids(['email' => wp_get_current_user()->user_email]));
    }
    return array_values(array_unique($ids));
}

/* -------------------------------------------------------------------
 * Codes
 * ------------------------------------------------------------------- */
function sp_fw_is_code($coupon) {
    return $coupon instanceof WC_Coupon && $coupon->get_id() && $coupon->get_meta('_sp_fw_owner') !== '';
}
function sp_fw_is_test_order($o) {
    return !$o || $o->get_meta('_sp_is_test') || in_array((int) $o->get_customer_id(), [17, 41, 90], true);
}
function sp_fw_code_id($email) {
    $map = (array) get_option('sp_fw_codes', []);
    $h = md5(sp_fw_email_norm($email));
    return (!empty($map[$h]) && get_post_status($map[$h]) === 'publish') ? (int) $map[$h] : 0;
}
function sp_fw_code($email) {
    $id = sp_fw_code_id($email);
    return $id ? strtoupper(get_the_title($id)) : '';
}
/** Code anlegen bzw. Merkmale des Werbers ergaenzen. Gibt den Code (GROSS) zurueck. */
function sp_fw_ensure_code($order) {
    $email = strtolower(trim((string) $order->get_billing_email()));
    if (!is_email($email)) {
        return '';
    }
    $ids = sp_fw_order_ids($order);
    $id = sp_fw_code_id($email);
    if ($id) {
        $old = (array) get_post_meta($id, '_sp_fw_ids', true);
        $new = array_values(array_unique(array_merge($old, $ids)));
        if ($new !== $old) {
            update_post_meta($id, '_sp_fw_ids', $new);
        }
        if ($order->get_customer_id() && !get_post_meta($id, '_sp_fw_uid', true)) {
            update_post_meta($id, '_sp_fw_uid', (int) $order->get_customer_id());
        }
        return strtoupper(get_the_title($id));
    }
    $name = strtoupper(preg_replace('/[^A-Za-z]/', '', remove_accents((string) $order->get_billing_first_name())));
    $name = substr($name !== '' ? $name : 'PEP', 0, 8);
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for ($i = 0; $i < 30; $i++) {
        $code = 'FREUND-' . $name . $chars[random_int(0, 31)] . $chars[random_int(0, 31)];
        if (!wc_get_coupon_id_by_code($code)) {
            break;
        }
    }
    $cfg = sp_fw_cfg();
    $c = new WC_Coupon();
    $c->set_code($code);
    $c->set_discount_type('percent');
    $c->set_amount($cfg['friend']);
    $c->set_individual_use(true);
    $c->set_excluded_product_ids([function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 729]);
    $c->set_description('Empfehlungscode von ' . $email . ' (sp-freunde.php)');
    $c->update_meta_data('_sp_fw_owner', $email);
    $c->update_meta_data('_sp_fw_ids', $ids);
    if ($order->get_customer_id()) {
        $c->update_meta_data('_sp_fw_uid', (int) $order->get_customer_id());
    }
    $id = $c->save();
    $map = (array) get_option('sp_fw_codes', []);
    $map[md5(sp_fw_email_norm($email))] = $id;
    update_option('sp_fw_codes', $map, false);
    return $code;
}

/* Jeder bezahlte Kunde bekommt seinen Code (still, auch solange das Programm aus ist). */
add_action('woocommerce_order_status_changed', function ($oid, $from, $to, $order) {
    if (!in_array($to, ['processing', 'completed'], true) || sp_fw_is_test_order($order)) {
        return;
    }
    if (function_exists('sp_em_order_kind') && sp_em_order_kind($order) === 'topup') {
        return;
    }
    sp_fw_ensure_code($order);
    sp_fw_reward($order);
}, 30, 4);

function sp_fw_is_self($coupon, array $ids) {
    $own = (array) $coupon->get_meta('_sp_fw_ids');
    $uid = (int) $coupon->get_meta('_sp_fw_uid');
    if ($uid && is_user_logged_in() && get_current_user_id() === $uid) {
        return true;
    }
    return (bool) array_intersect($own, $ids);
}

add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    if (!$valid || !sp_fw_is_code($coupon)) {
        return $valid;
    }
    if (!sp_fw_live() || $coupon->get_meta('_sp_fw_blocked')) {
        throw new Exception('Dieser Empfehlungscode ist gerade nicht aktiv.');
    }
    if (function_exists('slicewp_get_referrer_affiliate_id') && slicewp_get_referrer_affiliate_id()) {
        throw new Exception('Du bestellst schon über einen Partner-Link und bekommst dort 10 % – beides lässt sich nicht kombinieren.');
    }
    if (sp_fw_is_self($coupon, sp_fw_customer_ids())) {
        throw new Exception('Deinen eigenen Empfehlungscode kannst du nicht selbst einlösen – teil ihn mit Freunden. 🙂');
    }
    return $valid;
}, 20, 2);

/* -------------------------------------------------------------------
 * Belohnung, sobald die Bestellung des Freundes bezahlt ist
 * ------------------------------------------------------------------- */
function sp_fw_order_coupon($order) {
    foreach ($order->get_coupon_codes() as $code) {
        $c = new WC_Coupon($code);
        if (sp_fw_is_code($c)) {
            return $c;
        }
    }
    return null;
}
function sp_fw_is_affiliate($email) {
    global $wpdb;
    return (bool) $wpdb->get_var($wpdb->prepare("SELECT a.id FROM {$wpdb->prefix}slicewp_affiliates a JOIN {$wpdb->users} u ON u.ID = a.user_id WHERE u.user_email = %s LIMIT 1", $email));
}
function sp_fw_reward($order) {
    if ($order->get_meta('_sp_fw_done')) {
        return;
    }
    $c = sp_fw_order_coupon($order);
    if (!$c) {
        return;
    }
    $cid = $c->get_id();
    $owner = (string) $c->get_meta('_sp_fw_owner');
    $fids = sp_fw_order_ids($order);
    if (array_intersect((array) $c->get_meta('_sp_fw_ids'), $fids) || ((int) $c->get_meta('_sp_fw_uid') && (int) $c->get_meta('_sp_fw_uid') === (int) $order->get_customer_id())) {
        $order->update_meta_data('_sp_fw_done', 'eigener-code');
        $order->add_order_note('Freunde werben: eigener Code ' . strtoupper($c->get_code()) . ' – keine Belohnung.');
        $order->save();
        sp_fw_log('Eigener Code erkannt: ' . strtoupper($c->get_code()) . ' in #' . $order->get_order_number());
        return;
    }
    $refs = (array) get_option('sp_fw_refs', []);
    $list = $refs[$cid] ?? [];
    foreach ($list as $r) {
        if (array_intersect((array) ($r['ids'] ?? []), $fids)) {
            $order->update_meta_data('_sp_fw_done', 'schon-geworben');
            $order->save();
            return;
        }
    }
    if (sp_fw_is_test_order($order)) {
        return;
    }
    $cfg = sp_fw_cfg();
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $rc = 'DANKE-';
        for ($i = 0; $i < 6; $i++) {
            $rc .= $chars[random_int(0, 31)];
        }
    } while (wc_get_coupon_id_by_code($rc));
    $until = time() + (int) $cfg['reward_days'] * DAY_IN_SECONDS;
    $rw = new WC_Coupon();
    $rw->set_code($rc);
    $rw->set_discount_type('percent');
    $rw->set_amount($cfg['reward']);
    $rw->set_usage_limit(1);
    $rw->set_individual_use(true);
    $rw->set_email_restrictions([$owner]);
    $rw->set_date_expires($until);
    $rw->set_excluded_product_ids([function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 729]);
    $rw->set_description('Belohnung Freunde werben für ' . $owner . ' (Bestellung #' . $order->get_order_number() . ')');
    $rw->update_meta_data('_sp_fw_reward', $cid);
    $rid = $rw->save();
    $items = 0.0;
    foreach ($order->get_items() as $it) {
        $items += (float) $it->get_total();
    }
    $list[] = ['o' => $order->get_id(), 't' => time(), 'ids' => $fids, 'sum' => round($items, 2), 'r' => $rid, 'sent' => 0];
    $refs[$cid] = $list;
    update_option('sp_fw_refs', $refs, false);
    $order->update_meta_data('_sp_fw_done', 'belohnt:' . $rc);
    $order->add_order_note('Freunde werben: Code ' . strtoupper($c->get_code()) . ' von ' . $owner . ' – Belohnung ' . $rc . ' (' . $cfg['reward'] . ' %) erstellt.');
    $order->save();
    sp_fw_log('Belohnung ' . $rc . ' für ' . $owner . ' (Empfehlung Nr. ' . count($list) . ', #' . $order->get_order_number() . ')');
    sp_fw_send_rewards();
}
/** Belohnungsmails verschicken (auch Wiederholung, falls Omnisend nicht erreichbar war). */
function sp_fw_send_rewards() {
    if (!function_exists('sp_os_event')) {
        return;
    }
    $refs = (array) get_option('sp_fw_refs', []);
    $changed = false;
    foreach ($refs as $cid => $list) {
        $owner = (string) get_post_meta($cid, '_sp_fw_owner', true);
        foreach ($list as $i => $r) {
            if (!empty($r['sent']) || empty($r['r']) || $r['t'] < time() - 7 * DAY_IN_SECONDS) {
                continue;
            }
            $n = $i + 1;
            $rw = new WC_Coupon((int) $r['r']);
            $sum = 0.0;
            foreach (array_slice($list, 0, min($n, 3)) as $x) {
                $sum += (float) ($x['sum'] ?? 0);
            }
            $props = [
                'freund_code' => strtoupper(get_the_title($cid)),
                'freund_anzahl' => (string) $n,
                'belohnung_code' => strtoupper($rw->get_code()),
                'belohnung_bis' => $rw->get_date_expires() ? $rw->get_date_expires()->date_i18n('d.m.Y') : '',
                'partner_show' => ($n === 3 && !sp_fw_is_affiliate($owner)) ? 'block' : 'none',
                'partner_beispiel' => number_format_i18n(round($sum * 0.15)) . ' €',
            ];
            $res = sp_os_event($owner, 'sp_freund_belohnung', $props, 'nonSubscribed');
            if ($res === true || $res === 'unsubscribed') {
                $refs[$cid][$i]['sent'] = $res === true ? time() : 'abgemeldet';
                $changed = true;
            } else {
                update_option('sp_os_last_error', current_time('mysql') . ' – ' . $owner . ' (sp_freund_belohnung): ' . $res, false);
            }
        }
    }
    if ($changed) {
        update_option('sp_fw_refs', $refs, false);
    }
}

/* -------------------------------------------------------------------
 * Freunde-Mail: einmal pro Kunde, 14 Tage nach der Bezahlung; beim Start an alle Bestandskunden
 * ------------------------------------------------------------------- */
function sp_fw_start_mail($order) {
    $email = strtolower(trim((string) $order->get_billing_email()));
    $sent = (array) get_option('sp_fw_mailed', []);
    $h = md5(sp_fw_email_norm($email));
    if (!is_email($email) || !empty($sent[$h]) || !function_exists('sp_os_event')) {
        return false;
    }
    $code = sp_fw_ensure_code($order);
    if ($code === '') {
        return false;
    }
    $res = sp_os_event($email, 'sp_freunde_start', ['freund_code' => $code], 'nonSubscribed');
    if ($res === true || $res === 'unsubscribed') {
        $sent[$h] = $res === true ? time() : 'abgemeldet';
        update_option('sp_fw_mailed', $sent, false);
        return true;
    }
    update_option('sp_os_last_error', current_time('mysql') . ' – ' . $email . ' (sp_freunde_start): ' . $res, false);
    return false;
}
/** Letzte bezahlte Bestellung je Kunde (ohne Tests/Aufladungen) */
function sp_fw_customers() {
    $by = [];
    foreach (wc_get_orders(['status' => ['processing', 'completed'], 'limit' => -1, 'type' => 'shop_order', 'orderby' => 'date', 'order' => 'ASC']) as $o) {
        if (sp_fw_is_test_order($o) || (function_exists('sp_em_order_kind') && sp_em_order_kind($o) === 'topup')) {
            continue;
        }
        $e = strtolower(trim((string) $o->get_billing_email()));
        if (is_email($e)) {
            $by[md5(sp_fw_email_norm($e))] = $o;
        }
    }
    return $by;
}
function sp_fw_backfill($limit = 20) {
    $sent = (array) get_option('sp_fw_mailed', []);
    $done = 0;
    $left = 0;
    foreach (sp_fw_customers() as $h => $o) {
        if (!empty($sent[$h])) {
            continue;
        }
        if ($done >= $limit) {
            $left++;
            continue;
        }
        sp_fw_start_mail($o);
        $done++;
    }
    if ($left > 0) {
        wp_schedule_single_event(time() + 120, 'sp_fw_backfill');
    }
    sp_fw_log('Bestandskunden-Mail: ' . $done . ' verarbeitet, ' . $left . ' offen');
}
add_action('sp_fw_backfill', function () {
    if (sp_fw_live()) {
        sp_fw_backfill();
    }
});

add_action('sp_nlm_daily', function () {
    if (!sp_fw_live()) {
        return;
    }
    $cfg = sp_fw_cfg();
    $to = time() - (int) $cfg['mail_days'] * DAY_IN_SECONDS;
    $from = $to - 7 * DAY_IN_SECONDS;
    foreach (wc_get_orders(['status' => ['processing', 'completed'], 'limit' => 200, 'type' => 'shop_order', 'date_paid' => $from . '...' . $to]) as $o) {
        if (!sp_fw_is_test_order($o) && !(function_exists('sp_em_order_kind') && sp_em_order_kind($o) === 'topup')) {
            sp_fw_start_mail($o);
        }
    }
    sp_fw_send_rewards();
    sp_fw_watch();
}, 40);

/* Automatisch einschalten (z. B. 04.11. 10:00): Automationen in Omnisend aktivieren, Bestandskunden anschreiben. */
function sp_fw_switch($on) {
    $cfg = sp_fw_cfg();
    $cfg['on'] = $on ? 1 : 0;
    if ($on) {
        $cfg['since'] = time();
        $cfg['auto_on'] = 0;
    }
    update_option('sp_fw', $cfg);
    if (function_exists('sp_os_key') && sp_os_key()) {
        foreach ((array) $cfg['auto'] as $k => $aid) {
            $r = wp_remote_post('https://api.omnisend.com/api/automations/' . $aid . '/' . ($on ? 'enable' : 'disable'), [
                'timeout' => 20,
                'headers' => ['Authorization' => 'Omnisend-API-Key ' . sp_os_key(), 'Omnisend-Version' => '2026-03-15', 'Content-Type' => 'application/json'],
                'body' => $on ? '{}' : wp_json_encode(['contactsInWorkflow' => 'keep']),
            ]);
            sp_fw_log('Omnisend ' . $k . ' ' . ($on ? 'an' : 'aus') . ': HTTP ' . (is_wp_error($r) ? $r->get_error_message() : wp_remote_retrieve_response_code($r)));
        }
    }
    if ($on) {
        wp_schedule_single_event(time() + 60, 'sp_fw_backfill');
    }
    sp_fw_log($on ? 'eingeschaltet' : 'ausgeschaltet');
}
add_action('init', function () {
    $cfg = sp_fw_cfg();
    if (empty($cfg['on']) && !empty($cfg['auto_on']) && time() >= (int) $cfg['auto_on'] && !get_transient('sp_fw_switching')) {
        set_transient('sp_fw_switching', 1, 300);
        sp_fw_switch(true);
        sp_fw_log('automatisch eingeschaltet');
    }
}, 20);

/* Warnung: ein Code bringt mehr als 10 Bestellungen pro Woche (z. B. auf Gutscheinseiten gelandet). */
function sp_fw_week_uses($code) {
    global $wpdb;
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT i.order_id) FROM {$wpdb->prefix}woocommerce_order_items i JOIN {$wpdb->prefix}wc_orders o ON o.id = i.order_id
         WHERE i.order_item_type = 'coupon' AND i.order_item_name = %s AND o.date_created_gmt > %s",
        strtolower($code), gmdate('Y-m-d H:i:s', time() - 7 * DAY_IN_SECONDS)
    ));
}
function sp_fw_watch() {
    $warned = (array) get_option('sp_fw_warned', []);
    foreach ((array) get_option('sp_fw_codes', []) as $cid) {
        $code = get_the_title($cid);
        $n = $code ? sp_fw_week_uses($code) : 0;
        if ($n > 10 && (empty($warned[$cid]) || $warned[$cid] < time() - 7 * DAY_IN_SECONDS)) {
            $warned[$cid] = time();
            wp_mail(get_option('admin_email'), 'Freunde werben: Code ' . strtoupper($code) . ' auffällig oft genutzt',
                'Der Empfehlungscode ' . strtoupper($code) . ' wurde in den letzten 7 Tagen in ' . $n . " Bestellungen genutzt.\nVielleicht ist er auf einer Gutscheinseite gelandet. Sperren: " . admin_url('admin.php?page=sp-freunde'));
            sp_fw_log('Warnung: ' . strtoupper($code) . ' ' . $n . '× in 7 Tagen');
        }
    }
    update_option('sp_fw_warned', $warned, false);
}

/* -------------------------------------------------------------------
 * Kundenkonto: Karte mit Code (Startseite "Mein Konto")
 * ------------------------------------------------------------------- */
add_action('woocommerce_account_dashboard', function () {
    if (!sp_fw_live()) {
        return;
    }
    $u = wp_get_current_user();
    $id = sp_fw_code_id($u->user_email);
    if (!$id) {
        return;
    }
    $code = strtoupper(get_the_title($id));
    $n = count(((array) get_option('sp_fw_refs', []))[$id] ?? []);
    $txt = 'Hey, ich hol meine Peps bei Peptrium – kommt schnell und diskret. Mit meinem Code ' . $code . ' sparst du 10 % 👉 https://peptrium.com';
    $open = [];
    foreach (((array) get_option('sp_fw_refs', []))[$id] ?? [] as $r) {
        $rw = new WC_Coupon((int) ($r['r'] ?? 0));
        if ($rw->get_id() && $rw->get_usage_count() < 1 && (!$rw->get_date_expires() || $rw->get_date_expires()->getTimestamp() > time())) {
            $open[] = strtoupper($rw->get_code()) . ' (bis ' . $rw->get_date_expires()->date_i18n('d.m.Y') . ')';
        }
    }
    ?>
    <div id="sp-fw-acc" style="background:#0D0F12;border-radius:20px;padding:22px 18px;margin:0 0 22px;color:#fff;font-family:inherit;">
      <div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:#C08A2E;">FREUNDE WERBEN</div>
      <div style="font-size:20px;font-weight:800;margin:6px 0 4px;line-height:1.25;">10 % für deine Freunde, 20 % für dich.</div>
      <div style="font-size:14px;color:#C9CDD2;line-height:1.5;">Für jeden neuen Freund, der mit deinem Code bestellt, bekommst du einen 20-%-Code.</div>
      <div style="border:2px dashed #C08A2E;border-radius:14px;padding:12px;text-align:center;margin:14px 0 12px;font-size:24px;font-weight:800;letter-spacing:.04em;font-family:Menlo,Consolas,monospace;"><?php echo esc_html($code); ?></div>
      <div style="display:flex;gap:8px;">
        <a href="<?php echo esc_url('https://wa.me/?text=' . rawurlencode($txt)); ?>" target="_blank" rel="noopener" style="flex:1;background:#1FA855;color:#fff;text-align:center;text-decoration:none;font-weight:800;font-size:14px;padding:12px 6px;border-radius:12px;">WhatsApp</a>
        <a href="<?php echo esc_url('https://t.me/share/url?url=' . rawurlencode('https://peptrium.com') . '&text=' . rawurlencode($txt)); ?>" target="_blank" rel="noopener" style="flex:1;background:#229ED9;color:#fff;text-align:center;text-decoration:none;font-weight:800;font-size:14px;padding:12px 6px;border-radius:12px;">Telegram</a>
      </div>
      <div style="font-size:13px;color:#C9CDD2;margin-top:12px;">Geworbene Freunde: <b style="color:#fff;"><?php echo (int) $n; ?></b><?php if ($open) { echo ' · Deine offenen 20-%-Codes: <b style="color:#fff;">' . esc_html(implode(', ', $open)) . '</b>'; } ?></div>
    </div>
    <?php
}, 5);

/* -------------------------------------------------------------------
 * Kasse: Hinweis nach § 7 Abs. 3 UWG (Bestandskunden-Werbung) – immer sichtbar
 * ------------------------------------------------------------------- */
function sp_fw_uwg_text() {
    return 'Als Kunde informieren wir dich per E-Mail über eigene ähnliche Angebote (z. B. Nachbestellung oder unser Empfehlungsprogramm). Du kannst dem jederzeit widersprechen – z. B. über den Abmeldelink in jeder Mail –, ohne dass dafür andere als die Übermittlungskosten nach den Basistarifen entstehen.';
}
add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }
    ?>
    <script id="sp-fw-uwg">(function(){var T=<?php echo wp_json_encode(sp_fw_uwg_text()); ?>;var t=null;
    function put(){var a=document.querySelector('.wc-block-checkout__actions');if(!a||document.getElementById('sp-fw-uwg-n'))return;var d=document.createElement('div');d.id='sp-fw-uwg-n';d.textContent=T;d.style.cssText='font-size:12px;line-height:1.5;color:#6B7280;margin:10px 0 0;';a.parentNode.insertBefore(d,a);}
    new MutationObserver(function(){if(t)return;t=setTimeout(function(){t=null;put();},400);}).observe(document.body,{childList:true,subtree:true});put();})();</script>
    <?php
}, 60);

/* Datenschutzerklaerung (Seite 410): Abschnitt vor "Daten von Kindern" */
function sp_fw_privacy($html) {
    if (!is_page(410) || strpos($html, 'sp-fw-privacy') !== false || ($p = strpos($html, 'Daten von Kindern')) === false) {
        return $html;
    }
    $h = strrpos(substr($html, 0, $p), '<h');
    if ($h === false) {
        return $html;
    }
    $sec = '<h3 id="sp-fw-privacy">Werbung an Bestandskunden und Empfehlungsprogramm</h3>'
        . '<p>Wenn du bei uns bestellt hast, nutzen wir deine E-Mail-Adresse, um dich über eigene ähnliche Angebote zu informieren – z. B. Nachbestellung oder unser Empfehlungsprogramm „Freunde werben“ (Rechtsgrundlage: § 7 Abs. 3 UWG, Art. 6 Abs. 1 lit. f DSGVO). Du kannst dem jederzeit widersprechen, z. B. über den Abmeldelink in jeder Mail oder per Nachricht an info@peptrium.com. Versendet werden diese Mails über Omnisend (UAB Omnisend, Litauen).</p>'
        . '<p>Für das Empfehlungsprogramm erhältst du einen persönlichen Code. Löst jemand deinen Code ein, gleichen wir zur Vermeidung von Missbrauch Merkmale der Bestellung (E-Mail, Adresse, Telefonnummer) mit denen des Code-Inhabers ab; gespeichert werden dafür nur verschlüsselte Prüfwerte (Hashes). Den Namen oder die Bestellung des Freundes erfährt der Code-Inhaber nicht.</p>';
    return substr($html, 0, $h) . $sec . substr($html, $h);
}
add_filter('elementor/frontend/the_content', 'sp_fw_privacy', 97);
add_filter('the_content', 'sp_fw_privacy', 97);

/* -------------------------------------------------------------------
 * Admin: Peptrium Dashboard → Freunde werben
 * ------------------------------------------------------------------- */
add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Freunde werben', 'Freunde werben', 'manage_woocommerce', 'sp-freunde', 'sp_fw_admin');
}, 61);

function sp_fw_admin() {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }
    $msg = '';
    if (!empty($_POST['sp_fw_nonce']) && wp_verify_nonce($_POST['sp_fw_nonce'], 'sp_fw')) {
        $act = sanitize_key($_POST['act'] ?? '');
        if ($act === 'on' || $act === 'off') {
            sp_fw_switch($act === 'on');
            $msg = $act === 'on' ? 'Eingeschaltet – Bestandskunden werden in Paketen angeschrieben.' : 'Ausgeschaltet.';
        } elseif ($act === 'auto') {
            $cfg = sp_fw_cfg();
            $t = trim((string) ($_POST['auto_on'] ?? ''));
            $cfg['auto_on'] = $t === '' ? 0 : (int) (new DateTime($t, wp_timezone()))->getTimestamp();
            update_option('sp_fw', $cfg);
            $msg = 'Startzeit gespeichert.';
        } elseif ($act === 'block' || $act === 'unblock') {
            $cid = (int) ($_POST['cid'] ?? 0);
            if ($cid && get_post_meta($cid, '_sp_fw_owner', true)) {
                $act === 'block' ? update_post_meta($cid, '_sp_fw_blocked', time()) : delete_post_meta($cid, '_sp_fw_blocked');
                sp_fw_log(($act === 'block' ? 'gesperrt: ' : 'entsperrt: ') . strtoupper(get_the_title($cid)));
                $msg = 'Gespeichert.';
            }
        }
    }
    $cfg = sp_fw_cfg();
    $refs = (array) get_option('sp_fw_refs', []);
    $codes = (array) get_option('sp_fw_codes', []);
    $mailed = (array) get_option('sp_fw_mailed', []);
    $cust = count(sp_fw_customers());
    echo '<div class="wrap"><h1>Freunde werben</h1>';
    if ($msg) {
        echo '<div class="notice notice-success"><p>' . esc_html($msg) . '</p></div>';
    }
    echo '<p>Freund spart <b>' . (int) $cfg['friend'] . ' %</b>, Werber bekommt pro neuem Freund (bezahlt) <b>' . (int) $cfg['reward'] . ' %</b> (einmal, ' . (int) $cfg['reward_days'] . ' Tage). Freunde-Mail ' . (int) $cfg['mail_days'] . ' Tage nach der Bezahlung, einmal pro Kunde.</p>';
    echo '<form method="post" style="background:#fff;border:1px solid #ddd;padding:14px 16px;border-radius:8px;max-width:760px;">';
    wp_nonce_field('sp_fw', 'sp_fw_nonce');
    echo '<p style="font-size:15px;">Status: ' . (sp_fw_live() ? '<b style="color:#16a34a;">AN</b> seit ' . esc_html(wp_date('d.m.Y H:i', (int) $cfg['since'])) : '<b style="color:#b91c1c;">AUS</b>') . '</p>';
    if (!sp_fw_live()) {
        echo '<p>Schaltet sich automatisch ein am: <input type="datetime-local" name="auto_on" value="' . esc_attr($cfg['auto_on'] ? wp_date('Y-m-d\TH:i', (int) $cfg['auto_on']) : '') . '"> <button class="button" name="act" value="auto">Speichern</button></p>';
        echo '<p><button class="button button-primary" name="act" value="on" onclick="return confirm(\'Jetzt einschalten? Alle ' . (int) $cust . ' Bestandskunden bekommen die Freunde-Mail.\')">Jetzt einschalten</button></p>';
    } else {
        echo '<p><button class="button" name="act" value="off" onclick="return confirm(\'Ausschalten? Codes werden dann abgelehnt.\')">Ausschalten</button></p>';
    }
    echo '<p>Kunden mit Code: <b>' . count($codes) . '</b> · Kunden gesamt: <b>' . (int) $cust . '</b> · Freunde-Mail verschickt: <b>' . count($mailed) . '</b></p></form>';

    echo '<h2 style="margin-top:24px;">Werber</h2><table class="widefat striped" style="max-width:1100px;"><thead><tr><th>Code</th><th>Werber</th><th>Genutzt</th><th>Neue Freunde</th><th>Umsatz Freunde</th><th>Belohnungen (eingelöst)</th><th>7 Tage</th><th></th></tr></thead><tbody>';
    $rows = [];
    foreach ($codes as $cid) {
        $c = new WC_Coupon((int) $cid);
        if (!$c->get_id()) {
            continue;
        }
        $list = $refs[$cid] ?? [];
        $used = 0;
        $sum = 0;
        foreach ($list as $r) {
            $sum += (float) ($r['sum'] ?? 0);
            $rw = new WC_Coupon((int) ($r['r'] ?? 0));
            $used += $rw->get_id() && $rw->get_usage_count() > 0 ? 1 : 0;
        }
        $rows[] = [$c, count($list), $sum, $used, $c->get_usage_count()];
    }
    usort($rows, function ($a, $b) {
        return [$b[1], $b[4]] <=> [$a[1], $a[4]];
    });
    $shown = 0;
    foreach ($rows as [$c, $n, $sum, $used, $uses]) {
        if ($uses < 1 && $n < 1 && $shown >= 15) {
            continue;
        }
        $shown++;
        $w = $uses > 0 ? sp_fw_week_uses($c->get_code()) : 0;
        $bl = $c->get_meta('_sp_fw_blocked');
        echo '<tr' . ($w > 10 ? ' style="background:#fee2e2;"' : '') . '><td><code>' . esc_html(strtoupper($c->get_code())) . '</code>' . ($bl ? ' <b style="color:#b91c1c;">gesperrt</b>' : '') . '</td><td>' . esc_html($c->get_meta('_sp_fw_owner')) . '</td><td>' . (int) $uses . '×</td><td>' . (int) $n . '</td><td>' . wc_price($sum) . '</td><td>' . (int) $n . ' (' . (int) $used . ')</td><td>' . ($w > 10 ? '<b style="color:#b91c1c;">' . $w . '× – auffällig</b>' : (int) $w) . '</td><td>';
        echo '<form method="post" style="margin:0">';
        wp_nonce_field('sp_fw', 'sp_fw_nonce');
        echo '<input type="hidden" name="cid" value="' . (int) $c->get_id() . '"><button class="button button-small" name="act" value="' . ($bl ? 'unblock' : 'block') . '">' . ($bl ? 'Entsperren' : 'Sperren') . '</button></form></td></tr>';
    }
    if (!$rows) {
        echo '<tr><td colspan="8">Noch keine Codes.</td></tr>';
    }
    echo '</tbody></table><p class="description">Ohne Nutzung werden nur die ersten 15 Codes angezeigt.</p>';
    echo '<h2>Protokoll</h2><pre style="background:#fff;border:1px solid #ddd;padding:10px;max-width:1100px;max-height:260px;overflow:auto;">' . esc_html(implode("\n", array_slice((array) get_option('sp_fw_log', []), 0, 60))) . '</pre></div>';
}
