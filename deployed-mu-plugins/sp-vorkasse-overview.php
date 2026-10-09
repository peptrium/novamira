<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * "Offene Vorkasse-Zahlungen" admin overview: a single list of every bacs
 * (Vorkasse) order still on-hold, oldest first, with a one-click "als
 * bezahlt markieren" action - so working through the day's bank statement
 * means going down one page top-to-bottom instead of hunting through the
 * full Orders list (where they're mixed in with crypto/cheque/cod orders
 * in every other status). Orders open more than 2 days are flagged, same
 * threshold sp-order-tracking.php already uses for its "missing tracking
 * number" admin notice, so the two warnings read as one consistent system
 * rather than two different cutoffs.
 *
 * "Als bezahlt markieren" (single-row button, or via the CSV import below)
 * moves the order to "processing" - the same status a confirmed crypto
 * payment lands on (see sp-order-emails.php's roadmap wording, "Zahlung
 * bestätigt" only ever refers to processing) - not "completed", which
 * stays reserved for once a tracking number has actually been entered
 * (sp_tracking_mark_completed() in sp-order-tracking.php). This keeps
 * "paid" and "shipped" as two distinct, separately visible steps instead
 * of collapsing them into one click.
 *
 * CSV export/import for bulk bank-statement reconciliation: export gives
 * an "Bezahlt?" column left blank; the admin fills it in Excel with
 * anything at all (an "x", a checkmark, a word - any non-empty cell
 * counts) for the rows that got paid, leaves the rest blank, and
 * re-uploads. Same two-step upload -> preview -> confirm flow as
 * sp-order-tracking-bulk.php's tracking-number import, and reuses that
 * file's sp_tracking_import_find_order() to resolve the "Bestellnummer"
 * column against either the new sequential order numbers (3201+) or an
 * older order's raw internal ID - both already loaded as mu-plugins by
 * the time either page is opened.
 */

add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Offene Vorkasse-Zahlungen',
        'Vorkasse-Zahlungen',
        'manage_woocommerce',
        'sp-vorkasse-overview',
        'sp_vorkasse_render_page'
    );
});

/**
 * Escapes a value for use as literal text inside an XLSX sheet XML cell.
 */
function sp_xlsx_escape($value) {
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

/**
 * Builds a genuine, minimal .xlsx file (one sheet, plain data, bold header
 * row) using only PHP's built-in ZipArchive - an .xlsx is just a zip of a
 * few small XML files, so no external library (e.g. PhpSpreadsheet) is
 * needed for a table this simple. Every cell is written as inline text
 * (t="inlineStr"), which sidesteps a shared-strings table entirely and
 * means every value (including numbers like "76,42" as a German-format
 * string) round-trips exactly as given.
 *
 * @param string[] $header Column headings for row 1.
 * @param array<int, array<int, string>> $rows Data rows, same column count as $header.
 * @return string Raw .xlsx file bytes.
 */
function sp_vorkasse_build_xlsx($header, $rows) {
    $tmp_path = wp_tempnam('sp-vorkasse-export.xlsx');

    $zip = new ZipArchive();
    $zip->open($tmp_path, ZipArchive::OVERWRITE);

    $zip->addFromString('[Content_Types].xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
        '<Default Extension="xml" ContentType="application/xml"/>' .
        '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
        '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
        '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
        '</Types>'
    );

    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
        '</Relationships>'
    );

    $zip->addFromString('xl/workbook.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
        '<sheets><sheet name="Vorkasse offen" sheetId="1" r:id="rId1"/></sheets>' .
        '</workbook>'
    );

    $zip->addFromString('xl/_rels/workbook.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
        '</Relationships>'
    );

    // Two cell formats: 0 = normal, 1 = bold (used for the header row).
    $zip->addFromString('xl/styles.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
        '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><sz val="11"/><name val="Calibri"/><b/></font></fonts>' .
        '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>' .
        '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>' .
        '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
        '<cellXfs count="2">' .
        '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
        '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>' .
        '</cellXfs>' .
        '</styleSheet>'
    );

    $col_letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
    $col_count = count($header);

    $cols_xml = '<cols>';
    $default_widths = [14, 24, 16, 10, 10, 12];
    for ($i = 0; $i < $col_count; $i++) {
        $width = $default_widths[$i] ?? 14;
        $cols_xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1"/>';
    }
    $cols_xml .= '</cols>';

    $sheet_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
        $cols_xml .
        '<sheetData>';

    $row_num = 1;
    $sheet_xml .= '<row r="' . $row_num . '">';
    foreach ($header as $i => $val) {
        $cell_ref = $col_letters[$i] . $row_num;
        $sheet_xml .= '<c r="' . $cell_ref . '" t="inlineStr" s="1"><is><t xml:space="preserve">' . sp_xlsx_escape($val) . '</t></is></c>';
    }
    $sheet_xml .= '</row>';

    foreach ($rows as $row) {
        $row_num++;
        $sheet_xml .= '<row r="' . $row_num . '">';
        foreach ($row as $i => $val) {
            $cell_ref = $col_letters[$i] . $row_num;
            $sheet_xml .= '<c r="' . $cell_ref . '" t="inlineStr"><is><t xml:space="preserve">' . sp_xlsx_escape($val) . '</t></is></c>';
        }
        $sheet_xml .= '</row>';
    }

    $sheet_xml .= '</sheetData></worksheet>';

    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet_xml);
    $zip->close();

    $bytes = file_get_contents($tmp_path);
    unlink($tmp_path);

    return $bytes;
}

/**
 * XLSX export - a separate admin-post handler (not mixed into the normal
 * page render) so the response can be a clean file download instead of an
 * HTML admin page.
 */
add_action('admin_post_sp_vorkasse_export', function () {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Keine Berechtigung.');
    }
    check_admin_referer('sp_vorkasse_export');

    $orders = wc_get_orders([
        'limit'          => -1,
        'status'         => 'on-hold',
        'payment_method' => 'bacs',
        'orderby'        => 'date',
        'order'          => 'ASC',
    ]);

    $now = time();
    $data_rows = [];
    foreach ($orders as $order) {
        $days_open = floor(($now - $order->get_date_created()->getTimestamp()) / DAY_IN_SECONDS);
        $data_rows[] = [
            $order->get_order_number(),
            trim($order->get_formatted_billing_full_name()) ?: $order->get_billing_email(),
            $order->get_date_created()->date('d.m.Y H:i'),
            str_replace('.', ',', $order->get_total()), // deutsches Zahlenformat, leichter mit dem Kontoauszug abzugleichen
            $days_open,
            '',
        ];
    }

    $xlsx_bytes = sp_vorkasse_build_xlsx(
        ['Bestellnummer', 'Kunde', 'Datum', 'Betrag', 'Tage offen', 'Bezahlt?'],
        $data_rows
    );

    nocache_headers();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="vorkasse-offen-' . gmdate('Y-m-d') . '.xlsx"');
    header('Content-Length: ' . strlen($xlsx_bytes));
    echo $xlsx_bytes;
    exit;
});

/**
 * Parses the uploaded "Bezahlt?" CSV into preview rows without saving
 * anything - mirrors sp_tracking_import_parse_upload()'s shape/behavior
 * exactly, just matched against a "mark as paid" column instead of a
 * tracking-number column.
 */
function sp_vorkasse_import_parse_upload() {
    if (empty($_FILES['sp_vorkasse_csv']['tmp_name']) || !is_uploaded_file($_FILES['sp_vorkasse_csv']['tmp_name'])) {
        return ['error' => 'Keine Datei hochgeladen.'];
    }

    $handle = fopen($_FILES['sp_vorkasse_csv']['tmp_name'], 'r');
    if (!$handle) {
        return ['error' => 'Datei konnte nicht gelesen werden.'];
    }

    $first_line = fgets($handle);
    rewind($handle);
    $delimiter = (substr_count($first_line, ';') > substr_count($first_line, ',')) ? ';' : ',';

    $header = fgetcsv($handle, 0, $delimiter);
    if (!$header) {
        fclose($handle);
        return ['error' => 'Datei ist leer oder ungültig.'];
    }

    $order_col = null;
    $paid_col = null;
    foreach ($header as $i => $col) {
        $col_norm = strtolower(trim((string) $col));
        if ($order_col === null && strpos($col_norm, 'bestellnummer') !== false) {
            $order_col = $i;
        }
        if ($paid_col === null && strpos($col_norm, 'bezahlt') !== false) {
            $paid_col = $i;
        }
    }

    if ($order_col === null || $paid_col === null) {
        fclose($handle);
        return ['error' => 'Spalten "Bestellnummer" und/oder "Bezahlt?" wurden in der Kopfzeile nicht gefunden. Bitte die heruntergeladene Vorlage verwenden und nur die "Bezahlt?"-Spalte ausfüllen.'];
    }

    $rows = [];
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $order_number_raw = isset($row[$order_col]) ? trim((string) $row[$order_col]) : '';
        $paid_marker = isset($row[$paid_col]) ? trim((string) $row[$paid_col]) : '';

        if ($order_number_raw === '' || $paid_marker === '') {
            continue; // blank "Bezahlt?" - nothing to do for this row
        }

        $order = function_exists('sp_tracking_import_find_order') ? sp_tracking_import_find_order($order_number_raw) : null;

        if (!$order) {
            $rows[] = [
                'order_id'         => null,
                'order_number_raw' => $order_number_raw,
                'status'           => 'not_found',
            ];
            continue;
        }

        $rows[] = [
            'order_id'         => $order->get_id(),
            'order_number_raw' => $order_number_raw,
            'status'           => ($order->get_status() === 'on-hold') ? 'will_mark_paid' : 'already_' . $order->get_status(),
        ];
    }

    fclose($handle);
    return ['rows' => $rows];
}

function sp_vorkasse_import_apply_confirmed() {
    $result = ['marked' => 0, 'skipped' => 0];

    $raw = isset($_POST['sp_vorkasse_import_rows']) ? wp_unslash($_POST['sp_vorkasse_import_rows']) : '[]';
    $rows = json_decode($raw, true);
    if (!is_array($rows)) {
        return $result;
    }

    foreach ($rows as $row) {
        $order_id = isset($row['order_id']) ? (int) $row['order_id'] : 0;
        $order = $order_id ? wc_get_order($order_id) : null;

        if (!$order || $order->get_status() !== 'on-hold') {
            $result['skipped']++;
            continue;
        }

        $order->update_status('processing', 'Als bezahlt markiert (Vorkasse-CSV-Import).');
        $result['marked']++;
    }

    return $result;
}

define('SP_VORKASSE_REMIND_AFTER_DAYS', 3);
define('SP_VORKASSE_CANCEL_AFTER_DAYS', 14);

function sp_vorkasse_days_open($order) {
    return (int) floor((time() - $order->get_date_created()->getTimestamp()) / DAY_IN_SECONDS);
}

/** Alters-Stufe einer offenen Vorkasse-Bestellung (Farbe + kurzer Hinweis fuer die Liste). */
function sp_vorkasse_stage($days) {
    if ($days >= SP_VORKASSE_CANCEL_AFTER_DAYS) {
        return array('bg' => '#FCE8E8', 'color' => '#B32D2E', 'hint' => 'Stornieren, falls kein Geld da');
    }
    if ($days >= 7) {
        return array('bg' => '#FDEEE4', 'color' => '#B5450C', 'hint' => '2. Erinnerung sinnvoll');
    }
    if ($days >= SP_VORKASSE_REMIND_AFTER_DAYS) {
        return array('bg' => '#FFF6DC', 'color' => '#996800', 'hint' => 'Erinnerung sinnvoll');
    }
    return array('bg' => '#F0F0F1', 'color' => '#50575e', 'hint' => 'Überweisung dauert 1–2 Werktage');
}

/** Zahlungserinnerung mit Bankdaten an den Kunden (gleiches Mail-Design wie die Abo-Mails). */
function sp_vorkasse_send_payment_reminder($order) {
    $to = $order->get_billing_email();
    if (!$to || !function_exists('sp_abo_send_branded_email')) {
        return false;
    }
    $accounts = (array) get_option('woocommerce_bacs_accounts', array());
    $acc = $accounts ? reset($accounts) : array();
    $rows = array(
        'BETRAG' => wc_price($order->get_total(), array('currency' => $order->get_currency())),
        'VERWENDUNGSZWECK' => esc_html($order->get_order_number()),
    );
    if (!empty($acc['account_name'])) { $rows['KONTOINHABER'] = esc_html($acc['account_name']); }
    if (!empty($acc['iban'])) { $rows['IBAN'] = esc_html($acc['iban']); }
    if (!empty($acc['bic'])) { $rows['BIC'] = esc_html($acc['bic']); }
    if (!empty($acc['bank_name'])) { $rows['BANK'] = esc_html($acc['bank_name']); }
    $box = function_exists('sp_email_box_style') ? sp_email_box_style() : 'background:#F9FAFA;border:1px solid #DCDEE0;border-radius:12px;padding:20px 22px;margin:0 0 22px;';
    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;">Für deine Bestellung <strong>#<?php echo esc_html($order->get_order_number()); ?></strong> vom <?php echo esc_html($order->get_date_created()->date_i18n('d.m.Y')); ?> ist bei uns noch keine Zahlung eingegangen. Sobald die Überweisung da ist, verschicken wir deine Bestellung sofort.</p>
    <div style="<?php echo esc_attr($box); ?>">
      <?php $n = 0; foreach ($rows as $label => $value) : $n++; ?>
        <div style="<?php echo $n < count($rows) ? 'border-bottom:1px solid #F2F3F4;padding-bottom:9px;margin-bottom:9px;' : ''; ?>">
          <p style="margin:0 0 2px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;"><?php echo esc_html($label); ?></p>
          <p style="margin:0;font-size:15px;font-weight:700;color:#0D0F12;"><?php echo wp_kses_post($value); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#4B5157;">Bitte gib als Verwendungszweck nur die Bestellnummer an. Du hast schon überwiesen? Dann ist alles gut &ndash; die Zahlung ist vermutlich noch unterwegs, du kannst diese Mail ignorieren.</p>
    <p style="margin:0 0 12px;font-size:13px;line-height:1.6;color:#4B5157;">Ohne Zahlungseingang wird die Bestellung nach <?php echo (int) SP_VORKASSE_CANCEL_AFTER_DAYS; ?> Tagen storniert.</p>
    <?php if (function_exists('sp_abo_email_support_line')) { echo sp_abo_email_support_line(); } ?>
    <?php
    sp_abo_send_branded_email($to, 'Zahlungserinnerung zu deiner Bestellung #' . $order->get_order_number(), 'Deine Zahlung fehlt noch', ob_get_clean());
    $sent = (array) $order->get_meta('_sp_vorkasse_reminders');
    $sent[] = time();
    $order->update_meta_data('_sp_vorkasse_reminders', $sent);
    $order->add_order_note('Zahlungserinnerung per Mail gesendet (Vorkasse-Übersicht).');
    $order->save();
    return true;
}

function sp_vorkasse_render_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Keine Berechtigung.');
    }

    if (!empty($_POST['sp_vorkasse_mark_paid']) && check_admin_referer('sp_vorkasse_mark_paid')) {
        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $order = $order_id ? wc_get_order($order_id) : null;
        if ($order && $order->get_status() === 'on-hold') {
            $order->update_status('processing', 'Manuell als bezahlt markiert (Vorkasse-Übersicht).');
            echo '<div class="notice notice-success is-dismissible"><p>Bestellung #' . esc_html($order->get_order_number()) . ' als bezahlt markiert.</p></div>';
        }
    }

    if (!empty($_POST['sp_vorkasse_remind']) && check_admin_referer('sp_vorkasse_remind')) {
        $order = wc_get_order(absint($_POST['order_id'] ?? 0));
        if ($order && $order->get_status() === 'on-hold' && sp_vorkasse_send_payment_reminder($order)) {
            echo '<div class="notice notice-success is-dismissible"><p>Zahlungserinnerung für #' . esc_html($order->get_order_number()) . ' an ' . esc_html($order->get_billing_email()) . ' gesendet.</p></div>';
        }
    }
    if (!empty($_POST['sp_vorkasse_cancel']) && check_admin_referer('sp_vorkasse_cancel')) {
        $order = wc_get_order(absint($_POST['order_id'] ?? 0));
        if ($order && $order->get_status() === 'on-hold' && sp_vorkasse_days_open($order) >= SP_VORKASSE_CANCEL_AFTER_DAYS) {
            $order->update_status('cancelled', 'Storniert: keine Zahlung nach ' . sp_vorkasse_days_open($order) . ' Tagen (Vorkasse-Übersicht).');
            echo '<div class="notice notice-success is-dismissible"><p>Bestellung #' . esc_html($order->get_order_number()) . ' storniert. Der Kunde wurde per Mail informiert.</p></div>';
        }
    }

    $import_preview = null;
    $import_error = null;
    $import_results = null;

    if (!empty($_POST['sp_vorkasse_import_upload']) && check_admin_referer('sp_vorkasse_import_upload', 'sp_vorkasse_import_nonce')) {
        $parsed = sp_vorkasse_import_parse_upload();
        if (isset($parsed['error'])) {
            $import_error = $parsed['error'];
        } else {
            $import_preview = $parsed['rows'];
        }
    } elseif (!empty($_POST['sp_vorkasse_import_confirm']) && check_admin_referer('sp_vorkasse_import_confirm', 'sp_vorkasse_import_confirm_nonce')) {
        $import_results = sp_vorkasse_import_apply_confirmed();
    }

    $orders = wc_get_orders([
        'limit'          => -1,
        'status'         => 'on-hold',
        'payment_method' => 'bacs',
        'orderby'        => 'date',
        'order'          => 'ASC',
    ]);

    $now = time();
    $overdue_count = 0;
    ?>
    <div class="wrap">
      <h1>Offene Vorkasse-Zahlungen</h1>
      <p>Alle Bestellungen, bei denen noch auf den Zahlungseingang per Überweisung gewartet wird &ndash; sortiert nach Alter, älteste zuerst. Beim Abgleich mit dem Kontoauszug einfach von oben nach unten durchgehen.</p>

      <p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
          <input type="hidden" name="action" value="sp_vorkasse_export">
          <?php wp_nonce_field('sp_vorkasse_export'); ?>
          <button type="submit" class="button">📥 Als Excel-Datei (.xlsx) herunterladen</button>
        </form>
      </p>

      <?php if (empty($orders)): ?>
        <p><strong>Aktuell keine offenen Vorkasse-Zahlungen.</strong></p>
      <?php else: ?>
        <?php
        $sp_vk_tiles = array(
            'Offen gesamt' => array(0, null, '#1d2327'),
            '0–2 Tage (normal)' => array(0, array(0, 2), '#50575e'),
            '3–6 Tage (erinnern)' => array(0, array(3, 6), '#996800'),
            '7–13 Tage (2. Erinnerung)' => array(0, array(7, 13), '#B5450C'),
            '14+ Tage (stornieren?)' => array(0, array(14, PHP_INT_MAX), '#B32D2E'),
        );
        $sp_vk_sums = array_fill_keys(array_keys($sp_vk_tiles), 0.0);
        foreach ($orders as $o) {
            $d = sp_vorkasse_days_open($o);
            foreach ($sp_vk_tiles as $label => $tile) {
                if ($tile[1] === null || ($d >= $tile[1][0] && $d <= $tile[1][1])) {
                    $sp_vk_tiles[$label][0]++;
                    $sp_vk_sums[$label] += (float) $o->get_total();
                }
            }
        }
        ?>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin:14px 0 18px;max-width:1000px;">
          <?php foreach ($sp_vk_tiles as $label => $tile) : ?>
            <div style="flex:1 1 150px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px 14px;">
              <div style="font-size:11.5px;color:#50575e;font-weight:600;"><?php echo esc_html($label); ?></div>
              <div style="font-size:20px;font-weight:700;color:<?php echo esc_attr($tile[2]); ?>;"><?php echo (int) $tile[0]; ?></div>
              <div style="font-size:12px;color:#50575e;"><?php echo wp_kses_post(wc_price($sp_vk_sums[$label])); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
        <p style="max-width:1000px;color:#50575e;">Ab 3 Tagen erscheint „Zahlungserinnerung senden“, ab 14 Tagen zusätzlich „Stornieren“. Es passiert nichts automatisch &ndash; du entscheidest bei jeder Bestellung selbst.</p>
        <table class="wp-list-table widefat fixed striped" style="max-width:1000px;">
          <thead>
            <tr>
              <th>Bestellung</th>
              <th>Kunde</th>
              <th>Datum</th>
              <th>Betrag</th>
              <th style="width:170px;">Offen seit</th>
              <th style="width:230px;">Aktion</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order):
                $days_open = sp_vorkasse_days_open($order);
                $is_overdue = $days_open >= 2;
                if ($is_overdue) {
                    $overdue_count++;
                }
                $stage = sp_vorkasse_stage($days_open);
                $reminders = (array) $order->get_meta('_sp_vorkasse_reminders');
                $is_topup = function_exists('sp_wallet_order_contains_topup_product') && sp_wallet_order_contains_topup_product($order);
            ?>
              <tr>
                <td>
                  <a href="<?php echo esc_url($order->get_edit_order_url()); ?>">#<?php echo esc_html($order->get_order_number()); ?></a>
                </td>
                <td><?php echo esc_html(trim($order->get_formatted_billing_full_name()) ?: $order->get_billing_email()); ?>
                  <?php if ($is_topup): ?><br><span style="display:inline-block;margin-top:3px;background:#EEF4FF;color:#1D4ED8;border-radius:999px;padding:1px 8px;font-size:11px;font-weight:700;">Guthaben-Aufladung</span><?php endif; ?>
                </td>
                <td><?php echo esc_html($order->get_date_created()->date('d.m.Y H:i')); ?></td>
                <td><?php echo wp_kses_post(wc_price($order->get_total(), ['currency' => $order->get_currency()])); ?></td>
                <td>
                  <span style="display:inline-block;background:<?php echo esc_attr($stage['bg']); ?>;color:<?php echo esc_attr($stage['color']); ?>;border-radius:999px;padding:2px 9px;font-weight:700;font-size:12px;"><?php echo (int) $days_open; ?> Tag<?php echo $days_open === 1 ? '' : 'e'; ?></span>
                  <div style="font-size:11.5px;color:#50575e;margin-top:3px;"><?php echo esc_html($stage['hint']); ?></div>
                  <?php if ($reminders) : ?><div style="font-size:11.5px;color:#50575e;">Erinnert: <?php echo esc_html(implode(', ', array_map(function ($t) { return date_i18n('d.m.', (int) $t); }, $reminders))); ?></div><?php endif; ?>
                </td>
                <td>
                  <form method="post" style="margin:0 0 4px;">
                    <?php wp_nonce_field('sp_vorkasse_mark_paid'); ?>
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order->get_id()); ?>">
                    <button type="submit" name="sp_vorkasse_mark_paid" value="1" class="button button-primary button-small">Als bezahlt markieren</button>
                  </form>
                  <?php if ($days_open >= SP_VORKASSE_REMIND_AFTER_DAYS) : ?>
                  <form method="post" style="margin:0 0 4px;" onsubmit="return confirm('Zahlungserinnerung an <?php echo esc_js($order->get_billing_email()); ?> senden?');">
                    <?php wp_nonce_field('sp_vorkasse_remind'); ?>
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order->get_id()); ?>">
                    <button type="submit" name="sp_vorkasse_remind" value="1" class="button button-small"><?php echo $reminders ? 'Erneut erinnern' : 'Zahlungserinnerung senden'; ?></button>
                  </form>
                  <?php endif; ?>
                  <?php if ($days_open >= SP_VORKASSE_CANCEL_AFTER_DAYS) : ?>
                  <form method="post" style="margin:0;" onsubmit="return confirm('Bestellung #<?php echo esc_js($order->get_order_number()); ?> wirklich stornieren? Der Kunde bekommt eine Storno-Mail. Nur machen, wenn sicher kein Geld eingegangen ist.');">
                    <?php wp_nonce_field('sp_vorkasse_cancel'); ?>
                    <input type="hidden" name="order_id" value="<?php echo esc_attr($order->get_id()); ?>">
                    <button type="submit" name="sp_vorkasse_cancel" value="1" class="button button-small" style="color:#B32D2E;border-color:#B32D2E;">Stornieren</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <p style="margin-top:14px;">
          <strong><?php echo count($orders); ?></strong> offene Vorkasse-Zahlung<?php echo count($orders) === 1 ? '' : 'en'; ?> insgesamt<?php if ($overdue_count > 0): ?>,
          davon <strong style="color:#B5450C;"><?php echo (int) $overdue_count; ?></strong> seit 2 Tagen oder länger offen<?php endif; ?>.
        </p>
      <?php endif; ?>

      <hr style="margin:32px 0;">

      <h2>Per CSV/Excel als bezahlt markieren</h2>

      <?php if ($import_results): ?>
        <div class="notice notice-success" style="padding:14px 18px;">
          <p style="font-size:14px;">
            <strong><?php echo (int) $import_results['marked']; ?></strong> Bestellung(en) als bezahlt markiert,
            <strong><?php echo (int) $import_results['skipped']; ?></strong> übersprungen (nicht mehr offen).
          </p>
        </div>
        <p><a href="<?php echo esc_url(menu_page_url('sp-vorkasse-overview', false)); ?>" class="button">Weitere Datei importieren</a></p>

      <?php elseif ($import_preview !== null): ?>
        <p>Bitte prüfen, bevor du bestätigst &ndash; <strong>erst nach dem Klick auf "Bestätigen &amp; markieren" werden Bestellungen umgestellt.</strong></p>

        <?php
        $will_mark = array_filter($import_preview, fn($r) => $r['status'] === 'will_mark_paid');
        $not_found = array_filter($import_preview, fn($r) => $r['status'] === 'not_found');
        $already = array_filter($import_preview, fn($r) => strpos((string) $r['status'], 'already_') === 0);
        ?>

        <?php if (count($will_mark) > 1 && count($will_mark) === count($orders)): ?>
          <div class="notice notice-warning" style="padding:14px 18px;">
            <p style="font-size:14px;">
              ⚠️ <strong>Achtung:</strong> Wirklich <strong>alle <?php echo count($will_mark); ?> aktuell offenen Bestellungen</strong> sind in der Datei als bezahlt markiert.
              Das kann stimmen &ndash; ist aber auch ein typischer Excel-Fehler (z. B. das "x" versehentlich über die ganze Spalte nach unten gezogen).
              Bitte einmal kurz gegenprüfen, bevor du bestätigst.
            </p>
          </div>
        <?php endif; ?>

        <p>
          <strong><?php echo count($will_mark); ?></strong> Bestellung(en) werden als bezahlt markiert,
          <strong><?php echo count($already); ?></strong> bereits in einem anderen Status (übersprungen),
          <strong><?php echo count($not_found); ?></strong> nicht gefunden.
        </p>

        <table class="widefat striped" style="max-width:700px;margin-bottom:20px;">
          <thead>
            <tr>
              <th>Bestellnummer</th>
              <th>Aktion</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($import_preview as $row): ?>
              <tr>
                <td>#<?php echo esc_html($row['order_number_raw']); ?></td>
                <td>
                  <?php if ($row['status'] === 'will_mark_paid'): ?>
                    <span style="color:#1F7A4D;font-weight:600;">✓ wird als bezahlt markiert</span>
                  <?php elseif ($row['status'] === 'not_found'): ?>
                    <span style="color:#b32d2e;font-weight:600;">✕ nicht gefunden &ndash; wird übersprungen</span>
                  <?php else: ?>
                    <span style="color:#787c81;">bereits "<?php echo esc_html(str_replace('already_', '', $row['status'])); ?>" &ndash; übersprungen</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <form method="post">
          <?php wp_nonce_field('sp_vorkasse_import_confirm', 'sp_vorkasse_import_confirm_nonce'); ?>
          <input type="hidden" name="sp_vorkasse_import_rows" value="<?php echo esc_attr(wp_json_encode(array_values($will_mark))); ?>" />
          <p class="submit">
            <button type="submit" name="sp_vorkasse_import_confirm" value="1" class="button button-primary" <?php echo empty($will_mark) ? 'disabled' : ''; ?>>Bestätigen &amp; markieren (<?php echo count($will_mark); ?>)</button>
            <a href="<?php echo esc_url(menu_page_url('sp-vorkasse-overview', false)); ?>" class="button">Abbrechen</a>
          </p>
        </form>

      <?php else: ?>
        <p>Öffne die heruntergeladene Excel-Datei, trage in der Spalte <strong>"Bezahlt?"</strong> bei den bezahlten Bestellungen z. B. ein <strong>"x"</strong> ein (alles außer leer zählt), und speichere sie dann als <strong>CSV</strong> (Excel: Datei &rarr; Speichern unter &rarr; Dateityp "CSV"). Diese CSV-Datei hier hochladen. Zeilen, die leer bleiben, werden nicht angefasst.</p>

        <?php if ($import_error): ?>
          <div class="notice notice-error" style="padding:14px 18px;"><p><?php echo esc_html($import_error); ?></p></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
          <?php wp_nonce_field('sp_vorkasse_import_upload', 'sp_vorkasse_import_nonce'); ?>
          <table class="form-table">
            <tr>
              <th scope="row"><label for="sp_vorkasse_csv">CSV-Datei</label></th>
              <td><input type="file" name="sp_vorkasse_csv" id="sp_vorkasse_csv" accept=".csv,text/csv" required /></td>
            </tr>
          </table>
          <p class="submit">
            <button type="submit" name="sp_vorkasse_import_upload" value="1" class="button button-primary">Hochladen &amp; Vorschau anzeigen</button>
          </p>
        </form>
      <?php endif; ?>
    </div>
    <?php
}
