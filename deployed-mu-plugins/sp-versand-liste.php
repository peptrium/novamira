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
 *  - Karten-Ansicht pro Bestellung (Empfaenger, Kontakt, Inhalt) - zum Lesen
 *    am Bildschirm, als eigene Seite, und per "Liste per E-Mail senden"
 *    (gleiche Ansicht im Mailtext + Excel im Anhang; letzte Adresse wird gemerkt).
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
    // Zweiter Einstieg direkt im WooCommerce-Menue (gleiche Seite).
    add_submenu_page('woocommerce', 'Versand vorbereiten', '📦 Versand vorbereiten', 'manage_woocommerce', 'admin.php?page=sp-versand');
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

/**
 * Lieferadresse in die Felder des DHL-Formulars (Online-Frankierung) zerlegen.
 * Strasse und Hausnummer werden getrennt ("Olvenstedter Chaussee22" ->
 * "Olvenstedter Chaussee" + "22", "Hauptstrasse 26/2/4" -> "Hauptstrasse" + "26/2/4").
 */
function sp_versand_dhl_fields($order) {
    $use_shipping = $order->has_shipping_address();
    $g = function ($field) use ($order, $use_shipping) {
        $m = ($use_shipping ? 'get_shipping_' : 'get_billing_') . $field;
        return trim((string) $order->$m());
    };
    $street = $g('address_1');
    $number = '';
    if (preg_match('/^(.*?)[\s,]*(\d+\s*[a-zA-Z]?(?:\s*[-\/]\s*\d+\s*[a-zA-Z]?)*)$/u', $street, $m) && trim($m[1]) !== '') {
        $street = trim($m[1]);
        $number = preg_replace('/\s+/', '', $m[2]);
    }
    $extra = trim($g('company') . ' ' . $g('address_2'));
    $country = $g('country');
    $fields = array(
        'Name' => trim($g('first_name') . ' ' . $g('last_name')),
        'Adresszusatz' => $extra,
        'Straße' => $street,
        'Hausnummer' => $number,
        'PLZ' => $g('postcode'),
        'Ort' => $g('city'),
        'Land' => $country && $country !== 'DE' ? (WC()->countries->get_countries()[$country] ?? $country) : '',
    );
    return array_filter($fields, function ($v) { return $v !== ''; });
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

/* ---------------------------------------------------------------------
 * Karten-Ansicht (Bildschirm, eigenstaendige Ansicht und E-Mail).
 * Nur Inline-Styles, damit sie in E-Mail-Programmen genauso aussieht.
 * ------------------------------------------------------------------- */

function sp_versand_tag($label, $bg, $color) {
    return '<span style="display:inline-block;background:' . $bg . ';color:' . $color . ';border-radius:999px;padding:1px 8px;font-size:11px;font-weight:700;letter-spacing:.02em;margin-left:6px;vertical-align:middle;">' . esc_html($label) . '</span>';
}

function sp_versand_cards_html($orders, $mode = 'admin') {
    $with_links = ($mode === 'admin');
    $font = "font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;";
    $units = 0;
    foreach ($orders as $o) {
        foreach (sp_versand_order_items($o) as $it) {
            $units += $it['qty'];
        }
    }
    ob_start();
    ?>
    <div style="<?php echo $font; ?>color:#0D0F12;max-width:760px;">
      <div style="background:#0D0F12;color:#fff;border-radius:14px;padding:16px 20px;margin:0 0 16px;">
        <div style="font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:#A8B0B9;font-weight:700;">Versandliste &middot; <?php echo esc_html(current_time('d.m.Y, H:i')); ?> Uhr</div>
        <div style="font-size:22px;font-weight:800;margin-top:4px;"><?php echo count($orders); ?> Paket<?php echo count($orders) === 1 ? '' : 'e'; ?> &middot; <?php echo (int) $units; ?> Artikel</div>
        <div style="font-size:12.5px;color:#C7CCD1;margin-top:4px;">Bezahlt, noch nicht versendet &ndash; älteste Zahlung zuerst.</div>
      </div>
      <?php foreach ($orders as $i => $order) :
          $addr = sp_versand_address_lines($order);
          $items = sp_versand_order_items($order);
          $is_abo = (bool) array_filter($items, function ($it) { return $it['abo']; });
      ?>
      <div style="background:#fff;border:1px solid #DCDEE0;border-radius:14px;margin:0 0 14px;overflow:hidden;">
        <div style="background:#F2F3F4;padding:10px 16px;border-bottom:1px solid #DCDEE0;">
          <span style="display:inline-block;background:#0D0F12;color:#fff;border-radius:8px;padding:2px 9px;font-weight:800;font-size:13px;"><?php echo (int) ($i + 1); ?></span>
          <?php if ($with_links) : ?><a href="<?php echo esc_url($order->get_edit_order_url()); ?>" style="color:#0D0F12;font-weight:800;font-size:16px;text-decoration:none;margin-left:8px;">Bestellung #<?php echo esc_html($order->get_order_number()); ?></a>
          <?php else : ?><span style="font-weight:800;font-size:16px;margin-left:8px;">Bestellung #<?php echo esc_html($order->get_order_number()); ?></span><?php endif; ?>
          <span style="color:#4B5157;font-size:12.5px;margin-left:8px;">bezahlt <?php echo esc_html(sp_versand_paid_date($order)); ?></span>
          <?php if ($is_abo) { echo sp_versand_tag('ABO', '#FFF1EA', '#E5342B'); } ?>
        </div>
        <div style="padding:14px 16px;">
          <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#8A9099;font-weight:700;margin-bottom:4px;">Empfänger</div>
          <div style="font-size:16px;font-weight:800;line-height:1.35;"><?php echo esc_html($addr[0] ?? ''); ?></div>
          <div style="font-size:14.5px;line-height:1.5;color:#1d2327;"><?php echo implode('<br>', array_map('esc_html', array_slice($addr, 1))); ?></div>
          <div style="font-size:12.5px;color:#4B5157;margin-top:6px;">✉ <?php echo esc_html($order->get_billing_email()); ?><?php if ($order->get_billing_phone()) : ?> &nbsp;·&nbsp; ☎ <?php echo esc_html($order->get_billing_phone()); ?><?php endif; ?></div>
          <?php $dhl = sp_versand_dhl_fields($order); ?>
            <div class="sp-dhl" style="margin-top:10px;background:#FFFBEA;border:1px solid #F5DFA0;border-radius:10px;padding:8px 10px;">
              <div style="font-size:11px;letter-spacing:.05em;text-transform:uppercase;color:#996800;font-weight:700;margin-bottom:6px;"><?php echo $mode === 'email' ? 'Für DHL – Wert lange drücken zum Kopieren' : 'Für DHL – Feld anklicken zum Kopieren'; ?></div>
              <?php if ($mode === 'email') : ?>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;background:#fff;border:1px solid #F0E3B5;border-radius:8px;">
                <?php foreach ($dhl as $label => $value) : ?>
                  <tr>
                    <td style="padding:6px 10px;border-bottom:1px solid #F7EFD2;font-size:11px;color:#8A9099;font-weight:700;width:110px;white-space:nowrap;"><?php echo esc_html($label); ?></td>
                    <td style="padding:6px 10px;border-bottom:1px solid #F7EFD2;font-size:14px;color:#0D0F12;font-weight:600;-webkit-user-select:all;user-select:all;"><?php echo esc_html($value); ?></td>
                  </tr>
                <?php endforeach; ?>
                </table>
              <?php else : ?>
              <div style="display:flex;flex-wrap:wrap;gap:6px;">
                <?php foreach ($dhl as $label => $value) : ?>
                  <button type="button" class="sp-copy" data-copy="<?php echo esc_attr($value); ?>" style="display:inline-flex;flex-direction:column;align-items:flex-start;background:#fff;border:1px solid #DCDEE0;border-radius:8px;padding:4px 9px;cursor:pointer;text-align:left;">
                    <span style="font-size:10px;color:#8A9099;font-weight:700;"><?php echo esc_html($label); ?></span>
                    <span style="font-size:13.5px;color:#0D0F12;font-weight:600;"><?php echo esc_html($value); ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>

          <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#8A9099;font-weight:700;margin:14px 0 6px;">Inhalt</div>
          <?php foreach ($items as $it) : ?>
            <div style="padding:7px 0;border-top:1px solid #F2F3F4;font-size:15px;">
              <span style="display:inline-block;min-width:34px;text-align:center;background:#0D0F12;color:#fff;border-radius:6px;padding:1px 6px;font-weight:800;margin-right:8px;"><?php echo (int) $it['qty']; ?>×</span><?php echo esc_html($it['name']); ?><?php if ($it['gift']) { echo sp_versand_tag('GRATIS', '#E7F6EC', '#1F7A4D'); } ?>
            </div>
          <?php endforeach; ?>
          <?php if ($order->get_customer_note()) : ?>
            <div style="margin-top:10px;background:#FFF8E5;border:1px solid #F5DFA0;border-radius:10px;padding:9px 12px;font-size:13.5px;"><strong>Hinweis vom Kunden:</strong> <?php echo esc_html($order->get_customer_note()); ?></div>
          <?php endif; ?>
          <?php if ($mode === 'admin') : ?>
            <form method="post" onsubmit="return confirm('Sendungsnummer ' + this.sp_versand_tracking.value + ' speichern?\n\nDie Bestellung wird abgeschlossen und der Kunde bekommt die Versandmail mit DHL-Link.');" style="margin:14px 0 0;padding-top:12px;border-top:1px solid #F2F3F4;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
              <?php wp_nonce_field('sp_versand_track'); ?>
              <input type="hidden" name="sp_versand_track_order" value="<?php echo (int) $order->get_id(); ?>">
              <input type="text" name="sp_versand_tracking" required placeholder="DHL-Sendungsnummer eintragen" autocomplete="off" style="flex:1;min-width:210px;font-size:14px;padding:6px 10px;border:1px solid #DCDEE0;border-radius:8px;">
              <button type="submit" class="button button-primary">✓ Versendet</button>
            </form>
            <div style="font-size:11.5px;color:#8A9099;margin-top:5px;">Speichert die Nummer, schließt die Bestellung ab und schickt dem Kunden die Versandmail mit DHL-Link.</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

function sp_versand_copy_script() {
    ?>
    <script>
    (function(){
      function done(btn){ var o = btn.style.borderColor, b = btn.style.background; btn.style.borderColor = '#1F7A4D'; btn.style.background = '#E7F6EC'; setTimeout(function(){ btn.style.borderColor = o; btn.style.background = b; }, 900); }
      document.addEventListener('click', function(e){
        var btn = e.target.closest('.sp-copy'); if (!btn) { return; }
        var text = btn.getAttribute('data-copy');
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(function(){ done(btn); }); }
        else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); done(btn); }
      });
    })();
    </script>
    <?php
}

/** Eigenstaendige Ansicht (ohne WP-Admin-Rahmen) mit Kopier-Buttons - auch fuer den Link aus der Mail. */
function sp_versand_render_print($mode, $orders) {
    nocache_headers();
    header('X-Robots-Tag: noindex, nofollow');
    ?><!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Versandliste <?php echo esc_html(current_time('d.m.Y')); ?></title></head>
<body style="margin:0;padding:18px 14px;background:#F7F8F9;">
<?php echo sp_versand_cards_html($orders, 'web'); ?>
<?php sp_versand_copy_script(); ?>
</body></html>
    <?php
}

/* ---------------------------------------------------------------------
 * Geheimer Link aus der Mail: Web-Ansicht mit Kopier-Buttons ohne Login.
 * Zufaelliger Token, 7 Tage gueltig, zeigt genau die Bestellungen, die beim
 * Senden der Mail offen waren (bereits versendete werden ausgeblendet).
 * ------------------------------------------------------------------- */

define('SP_VERSAND_LINK_DAYS', 7);

function sp_versand_create_share_link($orders) {
    $token = wp_generate_password(32, false, false);
    set_transient('sp_versand_share_' . $token, array_map(function ($o) { return $o->get_id(); }, $orders), SP_VERSAND_LINK_DAYS * DAY_IN_SECONDS);
    return add_query_arg('sp_versandliste', $token, home_url('/'));
}

add_action('template_redirect', function () {
    if (empty($_GET['sp_versandliste'])) {
        return;
    }
    $token = preg_replace('/[^A-Za-z0-9]/', '', (string) $_GET['sp_versandliste']);
    $ids = strlen($token) === 32 ? get_transient('sp_versand_share_' . $token) : false;
    if (!$ids) {
        status_header(404);
        nocache_headers();
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><p style="font-family:sans-serif;padding:24px">Dieser Link ist abgelaufen oder ungültig. Bitte eine neue Versandliste anfordern.</p>';
        exit;
    }
    $open = sp_versand_get_orders();
    $orders = array_values(array_filter($open, function ($o) use ($ids) { return in_array($o->get_id(), $ids, true); }));
    // Eigenstaendige Seite, Ausgabe vor dem Theme - Altersabfrage/Cookie-Banner (wp_head/wp_footer) greifen hier nicht.
    sp_versand_render_print('web', $orders);
    exit;
}, 1);

/** Versandliste per Mail: Karten-Ansicht im Mailtext + Excel-Datei im Anhang. */
function sp_versand_send_mail($to, $orders) {
    $subject = 'Versandliste ' . current_time('d.m.Y') . ' – ' . count($orders) . ' Paket' . (count($orders) === 1 ? '' : 'e');
    $mailer = WC()->mailer();
    $link = sp_versand_create_share_link($orders);
    $button = '<p style="margin:0 0 18px;"><a href="' . esc_url($link) . '" style="display:inline-block;background:#0D0F12;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 20px;border-radius:9px;">Ansicht mit Kopier-Buttons öffnen &rarr;</a><br><span style="font-size:12px;color:#4B5157;">Im Browser: Feld antippen = kopiert. Link gilt ' . (int) SP_VERSAND_LINK_DAYS . ' Tage.</span></p>';
    $message = $mailer->wrap_message('Versandliste', $button . sp_versand_cards_html($orders, 'email'));
    $attachments = array();
    if (function_exists('sp_vorkasse_build_xlsx')) {
        $header = array('Nr', 'Bestellnummer', 'Bezahlt am', 'Name', 'Adresse', 'Produkt', 'Menge', 'Hinweis', 'E-Mail', 'Telefon');
        $rows = array();
        foreach ($orders as $i => $order) {
            $addr = sp_versand_address_lines($order);
            foreach (sp_versand_order_items($order) as $it) {
                $rows[] = array($i + 1, $order->get_order_number(), sp_versand_paid_date($order), $addr[0] ?? '', implode(', ', array_slice($addr, 1)), $it['name'], $it['qty'], trim(($it['gift'] ? 'Gratis ' : '') . ($it['abo'] ? 'Abo ' : '') . $order->get_customer_note()), $order->get_billing_email(), $order->get_billing_phone());
            }
        }
        $file = trailingslashit(get_temp_dir()) . 'versandliste-' . current_time('Y-m-d') . '.xlsx';
        file_put_contents($file, sp_vorkasse_build_xlsx($header, $rows));
        $attachments[] = $file;
    }
    $ok = $mailer->send($to, $subject, $message, "Content-Type: text/html\r\n", $attachments);
    foreach ($attachments as $f) {
        @unlink($f);
    }
    return $ok;
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
    $notice = '';
    // Sendungsnummer direkt aus der Karte: gleiche Logik wie das Feld in der Bestellung
    // (sp_tracking_set_number(): speichern + "Abgeschlossen" + Versandmail mit DHL-Link).
    if (!empty($_POST['sp_versand_track_order']) && check_admin_referer('sp_versand_track')) {
        $t_order = wc_get_order(absint($_POST['sp_versand_track_order']));
        $tn = preg_replace('/\s+/', '', sanitize_text_field(wp_unslash($_POST['sp_versand_tracking'] ?? '')));
        if (!$t_order || !function_exists('sp_tracking_set_number')) {
            $notice = '<div class="notice notice-error"><p>Bestellung nicht gefunden.</p></div>';
        } elseif (!preg_match('/^[A-Za-z0-9]{8,40}$/', $tn)) {
            $notice = '<div class="notice notice-error"><p>Die Sendungsnummer sieht nicht gültig aus (8–40 Buchstaben/Ziffern, ohne Sonderzeichen). Nichts gespeichert.</p></div>';
        } else {
            $res = sp_tracking_set_number($t_order, $tn);
            $link = '<a href="' . esc_url($t_order->get_edit_order_url()) . '">Bestellung #' . esc_html($t_order->get_order_number()) . '</a>';
            $notice = $res === 'set'
                ? '<div class="notice notice-success"><p>✓ ' . $link . ' als versendet markiert (Sendungsnummer ' . esc_html($tn) . '). Der Kunde hat die Versandmail mit DHL-Link bekommen. Tippfehler? In der Bestellung korrigieren.</p></div>'
                : '<div class="notice notice-info"><p>' . $link . ': Diese Sendungsnummer war schon hinterlegt – nichts geändert.</p></div>';
        }
    }
    $orders = sp_versand_get_orders();
    if (!empty($_POST['sp_versand_send']) && check_admin_referer('sp_versand_send')) {
        $to = sanitize_email(wp_unslash($_POST['sp_versand_to'] ?? ''));
        if (!is_email($to)) {
            $notice = '<div class="notice notice-error"><p>Bitte eine gültige E-Mail-Adresse eingeben.</p></div>';
        } elseif (!$orders) {
            $notice = '<div class="notice notice-warning"><p>Keine offenen Bestellungen &ndash; es wurde nichts gesendet.</p></div>';
        } elseif (sp_versand_send_mail($to, $orders)) {
            update_option('sp_versand_last_email', $to, false);
            $notice = '<div class="notice notice-success"><p>Versandliste mit ' . count($orders) . ' Bestellung(en) an <strong>' . esc_html($to) . '</strong> gesendet (inkl. Excel-Datei).</p></div>';
        } else {
            $notice = '<div class="notice notice-error"><p>Senden fehlgeschlagen &ndash; bitte später erneut versuchen.</p></div>';
        }
    }
    $last_to = get_option('sp_versand_last_email', '');
    $pick = sp_versand_pick_list($orders);
    ?>
    <div class="wrap">
      <h1>Versand vorbereiten</h1>
      <?php echo $notice; ?>
      <p style="max-width:760px;color:#50575e;">Alle bezahlten Bestellungen ohne Sendungsnummer &ndash; bei jedem Öffnen aktuell. Neue Bestellungen erscheinen automatisch, versendete (mit Sendungsnummer) verschwinden. Guthaben-Aufladungen und Testbestellungen sind ausgenommen. Sendungsnummer einfach unten in der jeweiligen Karte eintragen und „Versendet“ klicken &ndash; oder viele auf einmal per <a href="<?php echo esc_url(admin_url('admin.php?page=sp-tracking-import')); ?>">Excel-Import</a>.</p>

      <?php if (!$orders) : ?>
        <div class="notice notice-success inline"><p><strong>Alles versendet</strong> &ndash; aktuell nichts zu packen.</p></div>
      <?php else : ?>
        <div style="max-width:760px;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:14px 16px;margin:14px 0 18px;">
          <form method="post" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:0;">
            <?php wp_nonce_field('sp_versand_send'); ?>
            <strong style="margin-right:4px;">✉ Liste per E-Mail senden an</strong>
            <input type="email" name="sp_versand_to" value="<?php echo esc_attr($last_to); ?>" placeholder="name@beispiel.de" required style="min-width:240px;">
            <button type="submit" name="sp_versand_send" value="1" class="button button-primary">Senden</button>
          </form>
          <div style="font-size:12px;color:#50575e;margin-top:6px;">Die Mail enthält genau diese Ansicht plus die Liste als Excel-Datei im Anhang. Absender: Peptrium &lt;info@peptrium.com&gt;.</div>
          <div style="margin-top:10px;">
            <a class="button" target="_blank" href="<?php echo esc_url(sp_versand_out_url('print_pack')); ?>">↗ Als eigene Seite öffnen</a>
            <a class="button" href="<?php echo esc_url(sp_versand_out_url('xlsx_pack')); ?>">📥 Excel herunterladen</a>
          </div>
        </div>

        <?php echo sp_versand_cards_html($orders, 'admin'); ?>
        <?php sp_versand_copy_script(); ?>

        <details style="margin-top:24px;max-width:760px;">
          <summary style="cursor:pointer;font-weight:600;">Optional: Gesamtmengen aller offenen Bestellungen</summary>
          <table class="widefat striped" style="margin-top:10px;">
            <thead><tr><th>Produkt</th><th style="width:90px;">Menge</th><th style="width:120px;">in Bestellungen</th></tr></thead>
            <tbody>
            <?php foreach ($pick as $p) : ?>
              <tr><td><?php echo esc_html($p['name']); ?></td><td><strong><?php echo (int) $p['qty']; ?></strong></td><td><?php echo (int) $p['orders']; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </details>
      <?php endif; ?>
    </div>
    <?php
}
