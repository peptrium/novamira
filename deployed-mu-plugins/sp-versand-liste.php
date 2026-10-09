<?php
/**
 * Plugin Name: SP Versand vorbereiten
 * Description: Pickliste + Packzettel fuer alle bezahlten, noch nicht versendeten Bestellungen (2026-10-09).
 *
 * Admin: Peptrium Dashboard -> "Versand vorbereiten". Grundlage sind alle
 * Bestellungen im Status "In Bearbeitung" (bezahlt) ohne Sendungsnummer -
 * dieselbe Definition wie "Bezahlt, noch nicht versendet" im Dashboard.
 * Ausgenommen: reine Guthaben-Aufladungen (nichts zu versenden) und
 * Testbestellungen (_sp_is_test, sp-test-orders.php).
 *
 *  - Pickliste: was insgesamt aus dem Lager geholt werden muss (summiert pro
 *    Produkt/Variante, Gratis-Geschenke inklusive).
 *  - Packzettel: pro Bestellung Adresse + Inhalt, druckfertig (eine Seite je
 *    Bestellung).
 *  - Excel: Pickliste und Packliste als .xlsx (nutzt sp_vorkasse_build_xlsx()
 *    aus sp-vorkasse-overview.php).
 *
 * Nur lesend - aendert keine Bestellung. Sendungsnummern danach wie bisher
 * einzeln oder per Excel-Import (sp-order-tracking-bulk.php) eintragen.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Versand vorbereiten', 'Versand vorbereiten', 'manage_woocommerce', 'sp-versand', 'sp_versand_render_page');
}, 20);

/** Bezahlte, unversendete, echte Bestellungen - aelteste Zahlung zuerst. */
function sp_versand_get_orders() {
    $orders = wc_get_orders(array('limit' => -1, 'status' => 'processing', 'orderby' => 'date', 'order' => 'ASC'));
    $out = array();
    foreach ($orders as $order) {
        if ($order->get_meta('_sp_is_test')) {
            continue;
        }
        $tracking = defined('SP_TRACKING_META_KEY') ? $order->get_meta(SP_TRACKING_META_KEY) : $order->get_meta('_sp_tracking_number');
        if (!empty($tracking)) {
            continue;
        }
        if (!sp_versand_order_items($order)) {
            continue; // nur Guthaben-Aufladung
        }
        $out[] = $order;
    }
    usort($out, function ($a, $b) {
        $da = $a->get_date_paid() ?: $a->get_date_created();
        $db = $b->get_date_paid() ?: $b->get_date_created();
        return $da->getTimestamp() <=> $db->getTimestamp();
    });
    return $out;
}

/** Zu packende Positionen einer Bestellung (ohne Guthaben-Aufladung). */
function sp_versand_order_items($order) {
    $items = array();
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_wallet_amount')) {
            continue;
        }
        $items[] = array(
            'key' => $item->get_product_id() . ':' . $item->get_variation_id(),
            'name' => $item->get_name(),
            'qty' => (int) $item->get_quantity(),
            'gift' => ((float) $item->get_total() <= 0),
            'abo' => (bool) $item->get_meta('_sp_abo_interval_days') || (bool) $order->get_meta('_sp_abo_subscription_id'),
        );
    }
    return $items;
}

function sp_versand_pick_list($orders) {
    $pick = array();
    foreach ($orders as $order) {
        foreach (sp_versand_order_items($order) as $it) {
            if (!isset($pick[$it['key']])) {
                $pick[$it['key']] = array('name' => $it['name'], 'qty' => 0, 'orders' => 0, 'ids' => array());
            }
            $pick[$it['key']]['qty'] += $it['qty'];
            $pick[$it['key']]['ids'][$order->get_id()] = true;
        }
    }
    foreach ($pick as $k => $p) {
        $pick[$k]['orders'] = count($p['ids']);
    }
    uasort($pick, function ($a, $b) { return strcasecmp($a['name'], $b['name']); });
    return $pick;
}

function sp_versand_address_lines($order) {
    $use_shipping = $order->has_shipping_address();
    $g = function ($field) use ($order, $use_shipping) {
        $m = ($use_shipping ? 'get_shipping_' : 'get_billing_') . $field;
        return trim((string) $order->$m());
    };
    $lines = array_filter(array(
        trim($g('first_name') . ' ' . $g('last_name')),
        $g('company'),
        $g('address_1'),
        $g('address_2'),
        trim($g('postcode') . ' ' . $g('city')),
        $g('country') && $g('country') !== 'DE' ? (WC()->countries->get_countries()[$g('country')] ?? $g('country')) : '',
    ));
    return array_values($lines);
}

function sp_versand_paid_date($order) {
    $d = $order->get_date_paid() ?: $order->get_date_created();
    return $d ? $d->date_i18n('d.m.Y') : '';
}

/* ---------------------------------------------------------------------
 * Excel-Export + Druckansichten (ohne WP-Admin-Rahmen)
 * ------------------------------------------------------------------- */

add_action('admin_init', function () {
    if (($_GET['page'] ?? '') !== 'sp-versand' || empty($_GET['sp_versand']) || !current_user_can('manage_woocommerce')) {
        return;
    }
    check_admin_referer('sp_versand_out');
    $mode = sanitize_key($_GET['sp_versand']);
    $orders = sp_versand_get_orders();
    if (!empty($_GET['ids'])) {
        $ids = array_map('intval', explode(',', $_GET['ids']));
        $orders = array_values(array_filter($orders, function ($o) use ($ids) { return in_array($o->get_id(), $ids, true); }));
    }

    if ($mode === 'xlsx_pick' || $mode === 'xlsx_pack') {
        if (!function_exists('sp_vorkasse_build_xlsx')) {
            wp_die('Excel-Export nicht verfügbar.');
        }
        if ($mode === 'xlsx_pick') {
            $header = array('Produkt', 'Menge gesamt', 'In Bestellungen', 'Gepickt');
            $rows = array();
            foreach (sp_versand_pick_list($orders) as $p) {
                $rows[] = array($p['name'], $p['qty'], $p['orders'], '');
            }
            $file = 'pickliste';
        } else {
            $header = array('Bestellnummer', 'Bezahlt am', 'Name', 'Adresse', 'Produkt', 'Menge', 'Hinweis', 'E-Mail', 'Telefon');
            $rows = array();
            foreach ($orders as $order) {
                $addr = sp_versand_address_lines($order);
                foreach (sp_versand_order_items($order) as $it) {
                    $rows[] = array(
                        $order->get_order_number(), sp_versand_paid_date($order), $addr[0] ?? '',
                        implode(', ', array_slice($addr, 1)), $it['name'], $it['qty'],
                        trim(($it['gift'] ? 'Gratis-Geschenk ' : '') . ($it['abo'] ? 'Abo ' : '') . $order->get_customer_note()),
                        $order->get_billing_email(), $order->get_billing_phone(),
                    );
                }
            }
            $file = 'packliste';
        }
        $bytes = sp_vorkasse_build_xlsx($header, $rows);
        nocache_headers();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $file . '-' . current_time('Y-m-d') . '.xlsx"');
        header('Content-Length: ' . strlen($bytes));
        echo $bytes;
        exit;
    }

    if ($mode === 'print_pick' || $mode === 'print_pack') {
        sp_versand_render_print($mode, $orders);
        exit;
    }
});

function sp_versand_render_print($mode, $orders) {
    nocache_headers();
    ?><!doctype html>
<html lang="de"><head><meta charset="utf-8"><title><?php echo $mode === 'print_pick' ? 'Pickliste' : 'Packzettel'; ?> <?php echo esc_html(current_time('d.m.Y')); ?></title>
<style>
  body{font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111;margin:24px}
  h1{font-size:20px;margin:0 0 4px} .sub{color:#555;font-size:12px;margin:0 0 16px}
  table{border-collapse:collapse;width:100%} th,td{border:1px solid #bbb;padding:8px 10px;text-align:left;font-size:14px;vertical-align:top}
  th{background:#f2f2f2} td.num{text-align:center;font-weight:700;font-size:16px;width:70px} td.box{width:60px}
  .slip{page-break-after:always;padding-bottom:12px} .slip:last-child{page-break-after:auto}
  .slip-head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #111;padding-bottom:10px;margin-bottom:12px}
  .addr{font-size:16px;line-height:1.45} .meta{text-align:right;font-size:13px;color:#333}
  .tag{display:inline-block;border:1px solid #111;border-radius:4px;padding:0 6px;font-size:11px;font-weight:700;margin-left:6px}
  .note{margin-top:10px;padding:8px 10px;border:1px dashed #888;font-size:13px}
  .noprint{margin-bottom:16px} @media print{.noprint{display:none} body{margin:10mm}}
</style></head><body>
<div class="noprint"><button onclick="window.print()" style="font-size:15px;padding:8px 16px;cursor:pointer">🖨 Drucken</button></div>
<?php if ($mode === 'print_pick') : $pick = sp_versand_pick_list($orders); ?>
  <h1>Pickliste</h1>
  <p class="sub"><?php echo count($orders); ?> Bestellung(en) · erstellt <?php echo esc_html(current_time('d.m.Y H:i')); ?></p>
  <table><thead><tr><th>Produkt</th><th>Menge</th><th>Bestellungen</th><th>✓</th></tr></thead><tbody>
  <?php foreach ($pick as $p) : ?>
    <tr><td><?php echo esc_html($p['name']); ?></td><td class="num"><?php echo (int) $p['qty']; ?></td><td><?php echo (int) $p['orders']; ?></td><td class="box"></td></tr>
  <?php endforeach; ?>
  </tbody></table>
<?php else : foreach ($orders as $order) : $addr = sp_versand_address_lines($order); ?>
  <div class="slip">
    <div class="slip-head">
      <div class="addr"><?php echo implode('<br>', array_map('esc_html', $addr)); ?></div>
      <div class="meta"><strong style="font-size:18px">#<?php echo esc_html($order->get_order_number()); ?></strong><br>bezahlt <?php echo esc_html(sp_versand_paid_date($order)); ?><br><?php echo esc_html($order->get_billing_phone()); ?></div>
    </div>
    <table><thead><tr><th>Produkt</th><th>Menge</th><th>✓</th></tr></thead><tbody>
    <?php foreach (sp_versand_order_items($order) as $it) : ?>
      <tr><td><?php echo esc_html($it['name']); ?><?php if ($it['gift']) : ?><span class="tag">GRATIS</span><?php endif; ?><?php if ($it['abo']) : ?><span class="tag">ABO</span><?php endif; ?></td><td class="num"><?php echo (int) $it['qty']; ?></td><td class="box"></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php if ($order->get_customer_note()) : ?><div class="note"><strong>Kundenhinweis:</strong> <?php echo esc_html($order->get_customer_note()); ?></div><?php endif; ?>
  </div>
<?php endforeach; endif; ?>
</body></html>
    <?php
}

/* ---------------------------------------------------------------------
 * Admin-Seite
 * ------------------------------------------------------------------- */

function sp_versand_out_url($mode, $ids = null) {
    $args = array('page' => 'sp-versand', 'sp_versand' => $mode);
    if ($ids) {
        $args['ids'] = implode(',', $ids);
    }
    return wp_nonce_url(add_query_arg($args, admin_url('admin.php')), 'sp_versand_out');
}

function sp_versand_render_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Keine Berechtigung.');
    }
    $orders = sp_versand_get_orders();
    $pick = sp_versand_pick_list($orders);
    $units = array_sum(array_column($pick, 'qty'));
    ?>
    <div class="wrap">
      <h1>Versand vorbereiten</h1>
      <p style="max-width:900px;color:#50575e;">Alle bezahlten Bestellungen ohne Sendungsnummer, älteste Zahlung zuerst. Guthaben-Aufladungen und Testbestellungen sind ausgenommen. Diese Seite ändert nichts an den Bestellungen &ndash; Sendungsnummern danach wie gewohnt eintragen (einzeln oder per <a href="<?php echo esc_url(admin_url('admin.php?page=sp-tracking-import')); ?>">Excel-Import</a>).</p>

      <?php if (!$orders) : ?>
        <div class="notice notice-success inline"><p><strong>Alles versendet</strong> &ndash; aktuell nichts zu packen.</p></div>
      <?php else : ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin:14px 0;">
          <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 16px;"><div style="font-size:12px;color:#50575e;">Zu versenden</div><div style="font-size:22px;font-weight:700;"><?php echo count($orders); ?> Bestellung<?php echo count($orders) === 1 ? '' : 'en'; ?></div></div>
          <div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 16px;"><div style="font-size:12px;color:#50575e;">Artikel gesamt</div><div style="font-size:22px;font-weight:700;"><?php echo (int) $units; ?> Stück</div></div>
        </div>

        <p>
          <a class="button button-primary" target="_blank" href="<?php echo esc_url(sp_versand_out_url('print_pick')); ?>">🖨 Pickliste drucken</a>
          <a class="button button-primary" target="_blank" href="<?php echo esc_url(sp_versand_out_url('print_pack')); ?>">🖨 Packzettel drucken (1 Seite je Bestellung)</a>
          <a class="button" href="<?php echo esc_url(sp_versand_out_url('xlsx_pick')); ?>">📥 Pickliste (Excel)</a>
          <a class="button" href="<?php echo esc_url(sp_versand_out_url('xlsx_pack')); ?>">📥 Packliste (Excel)</a>
        </p>

        <h2>Pickliste &ndash; das musst du insgesamt holen</h2>
        <table class="widefat striped" style="max-width:700px;">
          <thead><tr><th>Produkt</th><th style="width:90px;">Menge</th><th style="width:120px;">in Bestellungen</th></tr></thead>
          <tbody>
          <?php foreach ($pick as $p) : ?>
            <tr><td><?php echo esc_html($p['name']); ?></td><td><strong><?php echo (int) $p['qty']; ?></strong></td><td><?php echo (int) $p['orders']; ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <h2 style="margin-top:28px;">Packliste &ndash; pro Bestellung</h2>
        <table class="widefat striped" style="max-width:1100px;">
          <thead><tr><th style="width:90px;">Bestellung</th><th style="width:90px;">Bezahlt</th><th>Lieferadresse</th><th>Inhalt</th><th style="width:110px;">Einzeln</th></tr></thead>
          <tbody>
          <?php foreach ($orders as $order) : $addr = sp_versand_address_lines($order); ?>
            <tr>
              <td><a href="<?php echo esc_url($order->get_edit_order_url()); ?>">#<?php echo esc_html($order->get_order_number()); ?></a></td>
              <td><?php echo esc_html(sp_versand_paid_date($order)); ?></td>
              <td><?php echo implode('<br>', array_map('esc_html', $addr)); ?></td>
              <td>
                <?php foreach (sp_versand_order_items($order) as $it) : ?>
                  <div><strong><?php echo (int) $it['qty']; ?>×</strong> <?php echo esc_html($it['name']); ?>
                    <?php if ($it['gift']) : ?><span style="background:#E7F6EC;color:#1F7A4D;border-radius:999px;padding:0 7px;font-size:11px;font-weight:700;">Gratis</span><?php endif; ?>
                    <?php if ($it['abo']) : ?><span style="background:#FFF1EA;color:#E5342B;border-radius:999px;padding:0 7px;font-size:11px;font-weight:700;">Abo</span><?php endif; ?>
                  </div>
                <?php endforeach; ?>
                <?php if ($order->get_customer_note()) : ?><div style="margin-top:4px;color:#996800;">💬 <?php echo esc_html($order->get_customer_note()); ?></div><?php endif; ?>
              </td>
              <td><a class="button button-small" target="_blank" href="<?php echo esc_url(sp_versand_out_url('print_pack', array($order->get_id()))); ?>">Packzettel</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <?php
}
