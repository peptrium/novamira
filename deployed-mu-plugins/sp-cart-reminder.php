<?php
/**
 * Plugin Name: SP Warenkorb-Erinnerung
 * Description: Eigene Warenkorb-Abbrecher-Erinnerung ohne externen Dienst (2026-10-09, ersetzt Omnisend).
 *
 * Rechtlich bewusst nur MIT Einwilligung (Werbe-Mail nach UWG/DSGVO): an der
 * Kasse gibt es unter der E-Mail-Adresse eine freiwillige, nicht
 * vorangehakte Checkbox. Nur wer sie anhakt, kann EINE Erinnerung bekommen.
 *
 * Ablauf:
 *  1. Kunde hakt an -> E-Mail + Warenkorb werden gespeichert (Tabelle
 *     wp_sp_cart_reminders, mit Zeitpunkt der Einwilligung als Nachweis).
 *  2. Bestellt er innerhalb von 2 Std. -> nichts passiert.
 *  3. Sonst: stuendlicher Cron schickt genau EINE Mail mit Warenkorb-Inhalt
 *     und Button "Zurueck zum Warenkorb" (stellt den Warenkorb wieder her).
 *  4. Bestellt er danach -> als "zurueckgeholt" gezaehlt.
 * Abmelde-Link in jeder Mail. Admin-Uebersicht: Peptrium Dashboard ->
 * Warenkorb-Erinnerungen.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_CR_DELAY_HOURS', 2);
define('SP_CR_MAX_AGE_DAYS', 3);
define('SP_CR_LABEL', 'Erinnere mich per E-Mail, falls ich meine Bestellung nicht abschließe (einmalig, jederzeit abbestellbar).');

function sp_cr_table() {
    global $wpdb;
    return $wpdb->prefix . 'sp_cart_reminders';
}

add_action('init', function () {
    if (get_option('sp_cr_table_version') === '1') {
        return;
    }
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta("CREATE TABLE " . sp_cr_table() . " (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        cart LONGTEXT NOT NULL,
        cart_total DECIMAL(10,2) NOT NULL DEFAULT 0,
        token CHAR(32) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'open',
        consent_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        sent_at DATETIME NULL DEFAULT NULL,
        order_id BIGINT UNSIGNED NULL DEFAULT NULL,
        PRIMARY KEY (id),
        KEY email (email),
        KEY status_updated (status, updated_at),
        KEY token (token)
    ) " . $wpdb->get_charset_collate() . ";");
    update_option('sp_cr_table_version', '1');
});

/* ---------------------------------------------------------------------
 * 1) Einwilligungs-Checkbox an der Kasse (Block-Checkout, Kontakt-Bereich)
 * ------------------------------------------------------------------- */

add_action('woocommerce_init', function () {
    if (function_exists('woocommerce_register_additional_checkout_field')) {
        woocommerce_register_additional_checkout_field(array(
            'id' => 'sp/cart-reminder',
            'label' => SP_CR_LABEL,
            'location' => 'contact',
            'type' => 'checkbox',
            'required' => false,
        ));
    }
});

/** Warenkorb-Inhalt als speicherbare Liste (ohne Gratis-Geschenke und Guthaben-Aufladungen). */
function sp_cr_cart_snapshot() {
    $items = array();
    $total = 0.0;
    if (!WC()->cart) {
        return array($items, $total);
    }
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    foreach (WC()->cart->get_cart() as $ci) {
        if (!empty($ci['sp_is_gift']) || (int) $ci['product_id'] === $topup || empty($ci['data'])) {
            continue;
        }
        $items[] = array(
            'product_id' => (int) $ci['product_id'],
            'variation_id' => (int) $ci['variation_id'],
            'quantity' => (int) $ci['quantity'],
            'abo' => !empty($ci['sp_abo_interval_days']) ? (int) $ci['sp_abo_interval_days'] : 0,
            'name' => $ci['data']->get_name(),
            'price' => (float) $ci['line_total'] + (float) $ci['line_tax'],
        );
        $total += (float) $ci['line_total'] + (float) $ci['line_tax'];
    }
    return array($items, round($total, 2));
}

add_action('wp_ajax_sp_cr_save', 'sp_cr_ajax_save');
add_action('wp_ajax_nopriv_sp_cr_save', 'sp_cr_ajax_save');
function sp_cr_ajax_save() {
    check_ajax_referer('sp_cr_save', 'nonce');
    global $wpdb;
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $consent = !empty($_POST['consent']);
    if (!is_email($email)) {
        wp_send_json_error('email');
    }
    $email = strtolower($email);
    $table = sp_cr_table();
    if (!$consent) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE email = %s AND status = 'open'", $email));
        wp_send_json_success('removed');
    }
    if ($wpdb->get_var($wpdb->prepare("SELECT 1 FROM {$table} WHERE email = %s AND status = 'optout' LIMIT 1", $email))) {
        wp_send_json_success('optout');
    }
    list($items, $total) = sp_cr_cart_snapshot();
    if (!$items) {
        wp_send_json_success('empty');
    }
    $now = current_time('mysql');
    $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE email = %s AND status = 'open' ORDER BY id DESC LIMIT 1", $email));
    if ($existing) {
        $wpdb->update($table, array('cart' => wp_json_encode($items), 'cart_total' => $total, 'updated_at' => $now), array('id' => $existing));
    } else {
        $wpdb->insert($table, array(
            'email' => $email,
            'user_id' => get_current_user_id(),
            'cart' => wp_json_encode($items),
            'cart_total' => $total,
            'token' => wp_generate_password(32, false, false),
            'status' => 'open',
            'consent_at' => $now,
            'updated_at' => $now,
        ));
    }
    wp_send_json_success('saved');
}

/** Checkbox-Aenderung + E-Mail an den Server melden (das Kassen-Formular selbst bleibt unangetastet). */
add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url('order-received')) {
        return;
    }
    ?>
    <script>
    (function(){
      var LABEL = <?php echo wp_json_encode(mb_substr(SP_CR_LABEL, 0, 40)); ?>;
      var url = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>, nonce = <?php echo wp_json_encode(wp_create_nonce('sp_cr_save')); ?>;
      var last = '';
      function box(){ var ls = document.querySelectorAll('label'); for (var i = 0; i < ls.length; i++) { if (ls[i].textContent.indexOf(LABEL) !== -1) { return ls[i].querySelector('input[type=checkbox]') || document.getElementById(ls[i].getAttribute('for')); } } return null; }
      function email(){ var e = document.getElementById('email') || document.querySelector('input[type=email]'); return e ? e.value.trim() : ''; }
      function send(){
        var b = box(); if (!b) { return; }
        var mail = email(); if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(mail)) { return; }
        var key = mail + '|' + (b.checked ? 1 : 0); if (key === last) { return; } last = key;
        var fd = new FormData(); fd.append('action', 'sp_cr_save'); fd.append('nonce', nonce); fd.append('email', mail); fd.append('consent', b.checked ? '1' : '');
        fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
      }
      document.addEventListener('change', function(e){ var b = box(); if (b && (e.target === b || e.target.type === 'email')) { setTimeout(send, 50); } }, true);
      document.addEventListener('focusout', function(e){ if (e.target && e.target.type === 'email') { setTimeout(send, 50); } }, true);
    })();
    </script>
    <?php
}, 60);

/* ---------------------------------------------------------------------
 * 2) Bestellung eingegangen -> keine Erinnerung bzw. als "zurueckgeholt" zaehlen
 * ------------------------------------------------------------------- */

function sp_cr_mark_ordered($order) {
    if (!$order) {
        return;
    }
    global $wpdb;
    $email = strtolower((string) $order->get_billing_email());
    if (!$email) {
        return;
    }
    $table = sp_cr_table();
    $wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 'ordered', order_id = %d WHERE email = %s AND status = 'open'", $order->get_id(), $email));
    $wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 'recovered', order_id = %d WHERE email = %s AND status = 'sent'", $order->get_id(), $email));
}
add_action('woocommerce_store_api_checkout_order_processed', 'sp_cr_mark_ordered');
add_action('woocommerce_checkout_order_processed', function ($order_id) {
    sp_cr_mark_ordered(wc_get_order($order_id));
});

/* ---------------------------------------------------------------------
 * 3) Stuendlicher Versand
 * ------------------------------------------------------------------- */

add_action('init', function () {
    if (!wp_next_scheduled('sp_cr_cron')) {
        wp_schedule_event(time() + 300, 'hourly', 'sp_cr_cron');
    }
});

add_action('sp_cr_cron', 'sp_cr_send_due');
function sp_cr_send_due() {
    global $wpdb;
    $table = sp_cr_table();
    $now = current_time('timestamp');
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE status = 'open' AND updated_at <= %s AND updated_at >= %s ORDER BY id ASC LIMIT 50",
        date('Y-m-d H:i:s', $now - SP_CR_DELAY_HOURS * HOUR_IN_SECONDS),
        date('Y-m-d H:i:s', $now - SP_CR_MAX_AGE_DAYS * DAY_IN_SECONDS)
    ));
    foreach ($rows as $row) {
        if ($wpdb->get_var($wpdb->prepare("SELECT 1 FROM {$table} WHERE email = %s AND status = 'optout' LIMIT 1", $row->email))) {
            $wpdb->update($table, array('status' => 'optout'), array('id' => $row->id));
            continue;
        }
        // Inzwischen (auch auf anderem Weg) bestellt? Dann keine Erinnerung.
        $recent = wc_get_orders(array('billing_email' => $row->email, 'date_created' => '>' . strtotime(get_gmt_from_date($row->consent_at)), 'limit' => 1, 'return' => 'ids', 'status' => array('pending', 'on-hold', 'processing', 'completed')));
        if ($recent) {
            $wpdb->update($table, array('status' => 'ordered', 'order_id' => (int) $recent[0]), array('id' => $row->id));
            continue;
        }
        // Erst als gesendet markieren (verhindert doppelte Mail bei ueberlappenden Laeufen), dann senden.
        $claimed = $wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 'sent', sent_at = %s WHERE id = %d AND status = 'open'", current_time('mysql'), $row->id));
        if ($claimed === 1) {
            sp_cr_send_mail($row);
        }
    }
}

function sp_cr_send_mail($row) {
    if (!function_exists('sp_abo_send_branded_email')) {
        return;
    }
    $items = json_decode($row->cart, true) ?: array();
    $restore = add_query_arg('sp_warenkorb', $row->token, home_url('/'));
    $unsub = add_query_arg('sp_warenkorb_abmelden', $row->token, home_url('/'));
    $box = function_exists('sp_email_box_style') ? sp_email_box_style() : 'background:#F9FAFA;border:1px solid #DCDEE0;border-radius:12px;padding:20px 22px;margin:0 0 22px;';
    ob_start();
    ?>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#0D0F12;">Du hast deine Bestellung noch nicht abgeschlossen &ndash; dein Warenkorb ist für dich gespeichert.</p>
    <div style="<?php echo esc_attr($box); ?>">
      <?php foreach ($items as $i => $it) : ?>
        <div style="<?php echo $i < count($items) - 1 ? 'border-bottom:1px solid #F2F3F4;padding-bottom:9px;margin-bottom:9px;' : ''; ?>">
          <p style="margin:0;font-size:15px;font-weight:700;color:#0D0F12;"><?php echo (int) $it['quantity']; ?>&times; <?php echo esc_html($it['name']); ?><?php echo !empty($it['abo']) ? ' <span style="color:#E5342B;font-size:12px;">(Abo)</span>' : ''; ?></p>
        </div>
      <?php endforeach; ?>
      <p style="margin:12px 0 0;font-size:14px;color:#4B5157;">Summe: <strong style="color:#0D0F12;"><?php echo wp_kses_post(wc_price($row->cart_total)); ?></strong></p>
    </div>
    <a href="<?php echo esc_url($restore); ?>" style="display:inline-block;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:9px;">Zurück zum Warenkorb &rarr;</a>
    <p style="margin:16px 0 0;font-size:13px;line-height:1.6;color:#4B5157;">Fragen zu Produkt, Versand oder Bezahlung? Antworte einfach auf diese Mail oder schreib uns auf <a href="https://t.me/peptrium" style="color:#4B5157;">Telegram</a>.</p>
    <p style="margin:14px 0 0;font-size:11px;line-height:1.5;color:#8A9099;">Du bekommst diese einmalige Erinnerung, weil du an der Kasse zugestimmt hast. <a href="<?php echo esc_url($unsub); ?>" style="color:#8A9099;">Keine Erinnerungen mehr erhalten</a></p>
    <?php
    sp_abo_send_branded_email($row->email, 'Dein Warenkorb wartet noch auf dich', 'Noch etwas vergessen?', ob_get_clean());
}

/* ---------------------------------------------------------------------
 * 4) Warenkorb wiederherstellen + Abmelden
 * ------------------------------------------------------------------- */

add_action('template_redirect', function () {
    global $wpdb;
    $table = sp_cr_table();
    if (!empty($_GET['sp_warenkorb_abmelden'])) {
        $token = preg_replace('/[^A-Za-z0-9]/', '', (string) $_GET['sp_warenkorb_abmelden']);
        $email = strlen($token) === 32 ? $wpdb->get_var($wpdb->prepare("SELECT email FROM {$table} WHERE token = %s", $token)) : null;
        if ($email) {
            $wpdb->query($wpdb->prepare("UPDATE {$table} SET status = 'optout' WHERE email = %s AND status IN ('open','sent')", $email));
            if (!$wpdb->get_var($wpdb->prepare("SELECT 1 FROM {$table} WHERE email = %s AND status = 'optout' LIMIT 1", $email))) {
                $now = current_time('mysql');
                $wpdb->insert($table, array('email' => $email, 'cart' => '[]', 'token' => wp_generate_password(32, false, false), 'status' => 'optout', 'consent_at' => $now, 'updated_at' => $now));
            }
        }
        nocache_headers();
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><div style="font-family:sans-serif;max-width:480px;margin:60px auto;padding:0 20px;text-align:center"><h2>Abgemeldet</h2><p>Du bekommst keine Warenkorb-Erinnerungen mehr.</p><p><a href="' . esc_url(home_url('/')) . '">Zum Shop</a></p></div>';
        exit;
    }
    if (empty($_GET['sp_warenkorb']) || !WC()->cart) {
        return;
    }
    $token = preg_replace('/[^A-Za-z0-9]/', '', (string) $_GET['sp_warenkorb']);
    $row = strlen($token) === 32 ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE token = %s", $token)) : null;
    if ($row) {
        WC()->cart->empty_cart();
        foreach (json_decode($row->cart, true) ?: array() as $it) {
            $data = !empty($it['abo']) ? array('sp_abo_interval_days' => (int) $it['abo']) : array();
            WC()->cart->add_to_cart((int) $it['product_id'], max(1, (int) $it['quantity']), (int) $it['variation_id'], array(), $data);
        }
        if (WC()->customer && !WC()->customer->get_billing_email()) {
            WC()->customer->set_billing_email($row->email);
            WC()->customer->save();
        }
    }
    wp_safe_redirect(wc_get_checkout_url());
    exit;
}, 5);

/* ---------------------------------------------------------------------
 * 5) Admin-Uebersicht
 * ------------------------------------------------------------------- */

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Warenkorb-Erinnerungen', 'Warenkorb-Erinnerungen', 'manage_woocommerce', 'sp-cart-reminders', 'sp_cr_render_admin');
}, 22);

function sp_cr_render_admin() {
    global $wpdb;
    $table = sp_cr_table();
    $since = date('Y-m-d H:i:s', current_time('timestamp') - 30 * DAY_IN_SECONDS);
    $count = function ($where) use ($wpdb, $table, $since) {
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE consent_at >= %s AND {$where}", $since));
    };
    $consents = $count("cart <> '[]'");
    $sent = $count("sent_at IS NOT NULL");
    $recovered = $count("status = 'recovered'");
    $ordered = $count("status = 'ordered'");
    $rec_total = 0.0;
    foreach ($wpdb->get_col($wpdb->prepare("SELECT order_id FROM {$table} WHERE status = 'recovered' AND consent_at >= %s", $since)) as $oid) {
        $o = wc_get_order($oid);
        if ($o) {
            $rec_total += (float) $o->get_total();
        }
    }
    $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC LIMIT 50");
    $labels = array('open' => array('Wartet', '#50575e'), 'ordered' => array('Selbst bestellt', '#1F7A4D'), 'sent' => array('Erinnert', '#996800'), 'recovered' => array('Nach Erinnerung bestellt', '#1F7A4D'), 'optout' => array('Abgemeldet', '#B32D2E'));
    ?>
    <div class="wrap">
      <h1>Warenkorb-Erinnerungen</h1>
      <p style="max-width:820px;color:#50575e;">Nur Kunden, die an der Kasse die Checkbox „Erinnere mich per E-Mail …“ angehakt haben, bekommen <?php echo (int) SP_CR_DELAY_HOURS; ?> Stunden nach dem Abbruch <strong>eine</strong> Erinnerung mit ihrem Warenkorb und einem Link, der ihn wiederherstellt. Wer vorher bestellt, bekommt nichts.</p>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin:14px 0;">
        <?php foreach (array('Einwilligungen (30 Tage)' => $consents, 'Selbst bestellt' => $ordered, 'Erinnerung gesendet' => $sent, 'Danach bestellt' => $recovered) as $l => $v) : ?>
          <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 16px;"><div style="font-size:12px;color:#50575e;"><?php echo esc_html($l); ?></div><div style="font-size:22px;font-weight:700;"><?php echo (int) $v; ?></div></div>
        <?php endforeach; ?>
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 16px;"><div style="font-size:12px;color:#50575e;">Zurückgeholter Umsatz</div><div style="font-size:22px;font-weight:700;color:#1F7A4D;"><?php echo wp_kses_post(wc_price($rec_total)); ?></div></div>
      </div>
      <?php if (!$rows) : ?>
        <p>Noch keine Einträge.</p>
      <?php else : ?>
        <table class="widefat striped" style="max-width:1100px;">
          <thead><tr><th>E-Mail</th><th>Warenkorb</th><th>Wert</th><th>Einwilligung</th><th>Erinnert</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r) : $items = json_decode($r->cart, true) ?: array(); $st = $labels[$r->status] ?? array($r->status, '#50575e'); ?>
            <tr>
              <td><?php echo esc_html($r->email); ?></td>
              <td style="font-size:12.5px;"><?php echo esc_html(implode(', ', array_map(function ($i) { return $i['quantity'] . '× ' . $i['name']; }, $items))); ?></td>
              <td><?php echo wp_kses_post(wc_price($r->cart_total)); ?></td>
              <td><?php echo esc_html(mysql2date('d.m. H:i', $r->consent_at)); ?></td>
              <td><?php echo $r->sent_at ? esc_html(mysql2date('d.m. H:i', $r->sent_at)) : '–'; ?></td>
              <td><span style="font-weight:700;color:<?php echo esc_attr($st[1]); ?>;"><?php echo esc_html($st[0]); ?></span><?php if ($r->order_id && ($o = wc_get_order($r->order_id))) : ?> · <a href="<?php echo esc_url($o->get_edit_order_url()); ?>">#<?php echo esc_html($o->get_order_number()); ?></a><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <?php
}
