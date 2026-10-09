<?php
/**
 * Plugin Name: SP Buchhaltung
 * Description: Interne Rechnungen (nur fuer die eigene Buchhaltung) + Monatsuebersicht (2026-10-09).
 *
 * Nutzt das Plugin "PDF Invoices & Packing Slips for WooCommerce" (WP Overnight)
 * nur als PDF-/Nummern-Werkzeug. Dessen Einstellungen sind so gesetzt, dass
 * Kunden die Rechnung NIE bekommen: attach_to_email_ids leer, my_account_buttons
 * = never, include_email_link aus.
 *
 *  - Jede bezahlte Bestellung (In Bearbeitung / Abgeschlossen) bekommt sofort
 *    eine fortlaufende Rechnungsnummer (RE-00001 ...), Rechnungsdatum =
 *    Zahlungsdatum. Testbestellungen (_sp_is_test) bekommen keine.
 *  - Admin "Buchhaltung" (Peptrium Dashboard + WooCommerce-Menue): Monat
 *    waehlen -> alle Rechnungen als ein PDF, Excel-Uebersicht, Summen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_buch_available() {
    return function_exists('wcpdf_get_invoice') && function_exists('wcpdf_get_document');
}

/** Rechnung fuer eine bezahlte Bestellung anlegen (idempotent). */
function sp_buch_ensure_invoice($order) {
    if (!sp_buch_available() || !$order || $order->get_meta('_sp_is_test')) {
        return null;
    }
    if (!in_array($order->get_status(), array('processing', 'completed'), true)) {
        return null;
    }
    $invoice = wcpdf_get_invoice($order, false);
    if ($invoice && $invoice->exists()) {
        return $invoice;
    }
    $invoice = wcpdf_get_invoice($order, true);
    if (!$invoice) {
        return null;
    }
    $paid = $order->get_date_paid() ?: $order->get_date_created();
    if ($paid && is_callable(array($invoice, 'set_date'))) {
        $invoice->set_date($paid);
    }
    $invoice->save();
    return $invoice;
}

/* Rechnungs-PDF: deutsches Datumsformat, und Emojis (z.B. beim Gratis-Geschenk)
 * weglassen - die PDF-Schrift kann sie nicht darstellen (leere Kaestchen). */
add_filter('wpo_wcpdf_date_format', function () {
    return 'd.m.Y';
});
add_action('wpo_wcpdf_before_html', function () {
    $GLOBALS['sp_buch_rendering_pdf'] = true;
});
add_action('wpo_wcpdf_after_html', function () {
    $GLOBALS['sp_buch_rendering_pdf'] = false;
});
add_filter('woocommerce_order_item_get_formatted_meta_data', function ($meta) {
    if (empty($GLOBALS['sp_buch_rendering_pdf'])) {
        return $meta;
    }
    foreach ($meta as $m) {
        $m->display_value = trim(preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', '', (string) $m->display_value));
    }
    return $meta;
}, 99);

add_action('woocommerce_order_status_processing', function ($order_id) {
    sp_buch_ensure_invoice(wc_get_order($order_id));
}, 40);
add_action('woocommerce_order_status_completed', function ($order_id) {
    sp_buch_ensure_invoice(wc_get_order($order_id));
}, 40);

/** Art der Bestellung fuer die Buchhaltung. */
function sp_buch_order_type($order) {
    $topup = 0;
    $goods = 0;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_wallet_amount')) {
            $topup++;
        } else {
            $goods++;
        }
    }
    if ($topup && !$goods) {
        return 'Guthaben-Aufladung';
    }
    if ($order->get_payment_method() === 'sp_wallet') {
        return $order->get_meta('_sp_abo_subscription_id') ? 'Abo-Lieferung (mit Guthaben bezahlt)' : 'Ware (mit Guthaben bezahlt)';
    }
    return $topup ? 'Ware + Guthaben-Aufladung' : 'Ware';
}

/** Alle Bestellungen mit Rechnung im Monat YYYY-MM, nach Rechnungsnummer sortiert. */
function sp_buch_month_orders($month) {
    $start = strtotime($month . '-01 00:00:00');
    $end = strtotime('+1 month', $start) - 1;
    $orders = wc_get_orders(array(
        'limit' => -1,
        'status' => array_keys(wc_get_order_statuses()),
        'meta_query' => array(array('key' => '_wcpdf_invoice_date', 'value' => array($start, $end), 'compare' => 'BETWEEN', 'type' => 'NUMERIC')),
    ));
    $rows = array();
    foreach ($orders as $order) {
        if ($order->get_meta('_sp_is_test')) {
            continue;
        }
        $inv = wcpdf_get_invoice($order, false);
        if (!$inv || !$inv->exists()) {
            continue;
        }
        $num = $inv->get_number();
        $date = $inv->get_date();
        $rows[] = array(
            'order' => $order,
            'number' => $num ? $num->get_formatted() : '',
            'plain' => $num ? (int) $num->get_plain() : 0,
            'date' => $date ? $date->date_i18n('d.m.Y') : '',
            'customer' => trim($order->get_formatted_billing_full_name()) ?: $order->get_billing_email(),
            'payment' => $order->get_payment_method_title(),
            'type' => sp_buch_order_type($order),
            'total' => (float) $order->get_total(),
            'refunded' => (float) $order->get_total_refunded(),
            'status' => wc_get_order_status_name($order->get_status()),
        );
    }
    usort($rows, function ($a, $b) { return $a['plain'] <=> $b['plain']; });
    return $rows;
}

function sp_buch_bulk_pdf_url($order_ids) {
    return add_query_arg(array(
        'action' => 'generate_wpo_wcpdf',
        'document_type' => 'invoice',
        'order_ids' => implode('x', $order_ids),
        'bulk' => 1,
        '_wpnonce' => wp_create_nonce('generate_wpo_wcpdf'),
    ), admin_url('admin-ajax.php'));
}

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Buchhaltung', 'Buchhaltung', 'manage_woocommerce', 'sp-buchhaltung', 'sp_buch_render_page');
    add_submenu_page('woocommerce', 'Buchhaltung', '🧾 Buchhaltung', 'manage_woocommerce', 'admin.php?page=sp-buchhaltung');
}, 21);

/* Excel-Export */
add_action('admin_init', function () {
    if (($_GET['page'] ?? '') !== 'sp-buchhaltung' || empty($_GET['sp_buch_xlsx']) || !current_user_can('manage_woocommerce')) {
        return;
    }
    check_admin_referer('sp_buch_xlsx');
    $month = preg_match('/^\d{4}-\d{2}$/', $_GET['m'] ?? '') ? $_GET['m'] : current_time('Y-m');
    $rows = array();
    foreach (sp_buch_month_orders($month) as $r) {
        $rows[] = array($r['number'], $r['date'], $r['order']->get_order_number(), $r['customer'], $r['type'], $r['payment'], str_replace('.', ',', number_format($r['total'], 2, '.', '')), $r['refunded'] ? str_replace('.', ',', number_format($r['refunded'], 2, '.', '')) : '', $r['status']);
    }
    $bytes = sp_vorkasse_build_xlsx(array('Rechnungsnr', 'Rechnungsdatum', 'Bestellnr', 'Kunde', 'Art', 'Zahlart', 'Betrag EUR', 'Erstattet EUR', 'Status'), $rows);
    nocache_headers();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="rechnungen-' . $month . '.xlsx"');
    header('Content-Length: ' . strlen($bytes));
    echo $bytes;
    exit;
});

function sp_buch_render_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Keine Berechtigung.');
    }
    if (!sp_buch_available()) {
        echo '<div class="wrap"><h1>Buchhaltung</h1><div class="notice notice-error"><p>Das Plugin „PDF Invoices &amp; Packing Slips for WooCommerce“ ist nicht aktiv.</p></div></div>';
        return;
    }
    $month = preg_match('/^\d{4}-\d{2}$/', $_GET['m'] ?? '') ? $_GET['m'] : current_time('Y-m');
    $rows = sp_buch_month_orders($month);
    $sum = array_sum(array_column($rows, 'total'));
    $ref = array_sum(array_column($rows, 'refunded'));
    $by_type = array();
    foreach ($rows as $r) {
        $by_type[$r['type']] = ($by_type[$r['type']] ?? 0) + $r['total'];
    }
    $months = array();
    for ($i = 0; $i < 12; $i++) {
        $ts = strtotime("-$i months", strtotime(current_time('Y-m') . '-01'));
        $months[date('Y-m', $ts)] = date_i18n('F Y', $ts);
    }
    ?>
    <div class="wrap">
      <h1>Buchhaltung &ndash; Rechnungen</h1>
      <p style="max-width:820px;color:#50575e;">Interne Rechnungen für deine Buchhaltung. Jede bezahlte Bestellung bekommt automatisch eine fortlaufende Rechnungsnummer (Rechnungsdatum = Zahlungsdatum). <strong>Kunden bekommen diese Rechnungen nicht</strong> &ndash; weder per Mail noch im Kundenkonto. Testbestellungen sind ausgenommen.</p>

      <form method="get" style="margin:14px 0;">
        <input type="hidden" name="page" value="sp-buchhaltung">
        <label><strong>Monat:</strong>
          <select name="m" onchange="this.form.submit()">
            <?php foreach ($months as $k => $label) : ?><option value="<?php echo esc_attr($k); ?>" <?php selected($k, $month); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?>
          </select>
        </label>
      </form>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 16px;"><div style="font-size:12px;color:#50575e;">Rechnungen</div><div style="font-size:22px;font-weight:700;"><?php echo count($rows); ?></div></div>
        <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 16px;"><div style="font-size:12px;color:#50575e;">Summe Rechnungsbeträge</div><div style="font-size:22px;font-weight:700;"><?php echo wp_kses_post(wc_price($sum)); ?></div><?php if ($ref) : ?><div style="font-size:12px;color:#B32D2E;">davon erstattet <?php echo wp_kses_post(wc_price($ref)); ?></div><?php endif; ?></div>
        <?php foreach ($by_type as $t => $v) : ?>
          <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 16px;"><div style="font-size:12px;color:#50575e;"><?php echo esc_html($t); ?></div><div style="font-size:16px;font-weight:700;"><?php echo wp_kses_post(wc_price($v)); ?></div></div>
        <?php endforeach; ?>
      </div>

      <?php if ($rows) : ?>
        <p>
          <a class="button button-primary" target="_blank" href="<?php echo esc_url(sp_buch_bulk_pdf_url(array_map(function ($r) { return $r['order']->get_id(); }, $rows))); ?>">📄 Alle Rechnungen <?php echo esc_html($months[$month] ?? $month); ?> als PDF</a>
          <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=sp-buchhaltung&sp_buch_xlsx=1&m=' . $month), 'sp_buch_xlsx')); ?>">📥 Übersicht als Excel</a>
        </p>
        <table class="widefat striped" style="max-width:1150px;">
          <thead><tr><th>Rechnungsnr.</th><th>Datum</th><th>Bestellung</th><th>Kunde</th><th>Art</th><th>Zahlart</th><th style="text-align:right;">Betrag</th><th>PDF</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r) : ?>
            <tr>
              <td><strong><?php echo esc_html($r['number']); ?></strong></td>
              <td><?php echo esc_html($r['date']); ?></td>
              <td><a href="<?php echo esc_url($r['order']->get_edit_order_url()); ?>">#<?php echo esc_html($r['order']->get_order_number()); ?></a> <span style="color:#50575e;font-size:12px;"><?php echo esc_html($r['status']); ?></span></td>
              <td><?php echo esc_html($r['customer']); ?></td>
              <td><?php echo esc_html($r['type']); ?></td>
              <td><?php echo esc_html($r['payment']); ?></td>
              <td style="text-align:right;"><?php echo wp_kses_post(wc_price($r['total'])); ?><?php if ($r['refunded']) : ?><br><span style="color:#B32D2E;font-size:12px;">−<?php echo wp_kses_post(wc_price($r['refunded'])); ?> erstattet</span><?php endif; ?></td>
              <td><a class="button button-small" target="_blank" href="<?php echo esc_url(sp_buch_bulk_pdf_url(array($r['order']->get_id()))); ?>">PDF</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php else : ?>
        <p>Keine Rechnungen in diesem Monat.</p>
      <?php endif; ?>
    </div>
    <?php
}
