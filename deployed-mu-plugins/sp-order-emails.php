<?php
/**
 * Custom order confirmation emails (Chrome Mono design) replacing the default
 * WooCommerce "on-hold" (Vorkasse) and "processing" (Krypto/instant) customer
 * emails, with dynamic bank-transfer instructions, a step-by-step roadmap,
 * an FAQ block and a preorder/backorder note.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_ORDER_EMAILS_DIR', __DIR__ . '/sp-email-templates');

add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    $overridable = [
        'emails/customer-on-hold-order.php',
        'emails/customer-processing-order.php',
        'emails/plain/customer-on-hold-order.php',
        'emails/plain/customer-processing-order.php',
        'emails/customer-cancelled-order.php',
        'emails/customer-completed-order.php',
        'emails/email-header.php',
    ];
    if (in_array($template_name, $overridable, true)) {
        $custom = SP_ORDER_EMAILS_DIR . '/' . $template_name;
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 20, 3);

add_filter('woocommerce_email_heading_customer_on_hold_order', function ($heading, $order) {
    return $order ? 'Bestätigung deiner Bestellung ' . $order->get_order_number() : $heading;
}, 10, 2);

add_filter('woocommerce_email_subject_customer_on_hold_order', function ($subject, $order) {
    return $order ? 'Bestätigung deiner Bestellung ' . $order->get_order_number() : $subject;
}, 10, 2);

add_filter('woocommerce_email_heading_customer_processing_order', function ($heading, $order) {
    return $order ? 'Zahlung bestätigt – Bestellung ' . $order->get_order_number() : $heading;
}, 10, 2);

add_filter('woocommerce_email_subject_customer_processing_order', function ($subject, $order) {
    return $order ? 'Zahlung bestätigt – Bestellung ' . $order->get_order_number() : $subject;
}, 10, 2);

add_filter('woocommerce_email_heading_customer_cancelled_order', function ($heading, $order) {
    return $order ? 'Bestellung storniert' : $heading;
}, 10, 2);

add_filter('woocommerce_email_subject_customer_cancelled_order', function ($subject, $order) {
    return $order ? 'Bestellung storniert – Bestellung ' . $order->get_order_number() : $subject;
}, 10, 2);

add_filter('woocommerce_email_heading_customer_completed_order', function ($heading, $order) {
    if (!$order) {
        return $heading;
    }
    $has_tracking = defined('SP_TRACKING_META_KEY') && $order->get_meta(SP_TRACKING_META_KEY);
    return $has_tracking ? 'Deine Bestellung ist unterwegs' : 'Deine Bestellung ist abgeschlossen';
}, 10, 2);

add_filter('woocommerce_email_subject_customer_completed_order', function ($subject, $order) {
    if (!$order) {
        return $subject;
    }
    $has_tracking = defined('SP_TRACKING_META_KEY') && $order->get_meta(SP_TRACKING_META_KEY);
    $label = $has_tracking ? 'Deine Bestellung ist unterwegs' : 'Bestellung abgeschlossen';
    return $label . ' – Bestellung ' . $order->get_order_number();
}, 10, 2);

/**
 * True if any line item's product is currently on backorder - the same
 * definition sp-preorder.php already uses for the "Vorbestellen" button text,
 * kept in sync here rather than introducing a second definition of "preorder".
 */
function sp_order_has_preorder_item($order) {
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->get_stock_status() === 'onbackorder') {
            return true;
        }
    }
    return false;
}

function sp_email_box_style($bg = '#F5F6F7', $border = '#E4E6E9') {
    return "background:{$bg};border:1px solid {$border};border-radius:16px;padding:20px;margin:0 0 22px;";
}

function sp_email_render_preorder_note() {
    ob_start();
    ?>
    <div style="<?php echo esc_attr(sp_email_box_style('#F2F3F4', '#DCDEE0')); ?>">
      <p style="margin:0;font-size:14px;line-height:1.6;color:#0D0F12;">
        📦 <strong>Hinweis zu deiner Vorbestellung:</strong> Ein oder mehrere Artikel in deiner
        Bestellung sind aktuell vorbestellt und werden versendet, sobald sie wieder verfügbar
        sind. Wir informieren dich nicht extra darüber &ndash; die nächste E-Mail ist wie gewohnt
        die Versand-/Sendungsbestätigung.
      </p>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * @param array $steps List of [title, text] pairs.
 * @param string|null $hint Optional light hint line shown under the steps.
 */
function sp_email_render_roadmap($steps, $hint = null) {
    ob_start();
    ?>
    <div style="<?php echo esc_attr(sp_email_box_style('#F9FAFA', '#DCDEE0')); ?>">
      <p style="margin:0 0 16px;font-size:13px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:#4B5157;">
        So geht es jetzt weiter &ndash; dein Fahrplan
      </p>
      <?php foreach ($steps as $i => $step): ?>
      <div style="display:flex;gap:12px;align-items:flex-start;<?php echo $i < count($steps) - 1 ? 'margin-bottom:14px;' : ''; ?>">
        <div style="flex-shrink:0;width:24px;height:24px;border-radius:50%;background:#0D0F12;color:#FFFFFF;font-size:12px;font-weight:700;line-height:24px;text-align:center;"><?php echo (int) ($i + 1); ?></div>
        <p style="margin:0;font-size:14px;line-height:1.6;color:#0D0F12;"><strong><?php echo esc_html($step[0]); ?></strong> &ndash; <?php echo wp_kses_post($step[1]); ?></p>
      </div>
      <?php endforeach; ?>
      <?php if ($hint): ?>
      <div style="margin-top:16px;background:#FFFFFF;border:1px solid #DCDEE0;border-radius:8px;padding:10px 14px;">
        <p style="margin:0;font-size:13px;color:#4B5157;"><?php echo wp_kses_post($hint); ?></p>
      </div>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function sp_email_render_bank_box($order) {
    $accounts = get_option('woocommerce_bacs_accounts');
    if (empty($accounts) || !is_array($accounts)) {
        return '';
    }
    $acc = $accounts[0];
    ob_start();
    ?>
    <div style="margin:0 0 22px;border:1px solid #DCDEE0;border-radius:12px;overflow:hidden;">
      <div style="background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);padding:14px 22px;">
        <p style="margin:0;color:#FFFFFF;font-size:15px;font-weight:700;">Unsere Bankverbindung</p>
      </div>
      <div style="padding:18px 22px;">
        <?php
        // IBAN in 4er-Bloecken (leichter lesbar/manuell zu uebertragen) - reine Anzeige,
        // die eigentliche IBAN in $acc['iban'] bleibt unveraendert.
        $iban_raw = esc_html($acc['iban'] ?? '');
        $iban_grouped = trim(chunk_split(str_replace(' ', '', $iban_raw), 4, ' '));

        $rows = [
            ['KONTOINHABER', esc_html($acc['account_name'] ?? ''), 'bitte genau so im Banking-Feld „Empfänger“ eintragen', false, false],
            ['VERWENDUNGSZWECK', esc_html($order->get_order_number()), 'Wichtig: muss angegeben werden – sonst können wir deine Bestellung nicht zuordnen.', true, true],
            ['IBAN', $iban_grouped, null, true, false],
            ['BIC', esc_html($acc['bic'] ?? ''), null, true, false],
            ['BANK', esc_html($acc['bank_name'] ?? ''), null, false, false],
            ['BETRAG', wp_kses_post(wc_price($order->get_total(), ['currency' => $order->get_currency()])), null, false, false],
        ];
        foreach ($rows as $idx => $row):
            list($label, $value, $note, $copyable, $note_important) = $row;
            $value_style = 'margin:0;font-size:17px;font-weight:700;color:#0D0F12;';
            if ($copyable) {
                $value_style .= 'font-family:"SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace;letter-spacing:.03em;';
            }
            $note_style = $note_important
                ? 'margin:3px 0 0;font-size:12px;font-weight:700;color:#B5450C;'
                : 'margin:3px 0 0;font-size:12px;color:#4B5157;';
        ?>
        <div style="<?php echo $idx < count($rows) - 1 ? 'border-bottom:1px solid #F2F3F4;padding-bottom:12px;margin-bottom:12px;' : ''; ?>">
          <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;"><?php echo esc_html($label); ?></p>
          <p style="<?php echo esc_attr($value_style); ?>"><?php echo $value; ?></p>
          <?php if ($note): ?><p style="<?php echo esc_attr($note_style); ?>"><?php echo esc_html($note); ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <p style="margin:14px 0 0;font-size:12px;color:#4B5157;">Tipp: Tippe/klicke auf IBAN, Verwendungszweck oder BIC oben und halte kurz gedrückt, um den Wert direkt zu markieren und zu kopieren.</p>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

function sp_email_render_hold_notice() {
    ob_start();
    ?>
    <div style="<?php echo esc_attr(sp_email_box_style('#F2F3F4', '#DCDEE0')); ?>">
      <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#0D0F12;">
        🔒 Der oben genannte Kontoinhaber ist korrekt und gehört zu Peptrium &ndash; bitte
        überweise genau an diese Daten. Einen Zahlungsnachweis brauchst du uns nicht zu
        schicken &ndash; wir prüfen die Zahlungseingänge mehrmals täglich.
      </p>
      <p style="margin:0;font-size:14px;line-height:1.6;color:#0D0F12;">
        ✉️ <strong>Wichtig:</strong> Du bekommst keine separate Zahlungsbestätigung. Sobald
        deine Zahlung bei uns eingegangen ist, versenden wir am selben oder nächsten Werktag
        &ndash; die nächste E-Mail ist die Sendungsnummer von DHL.
      </p>
    </div>
    <?php
    return ob_get_clean();
}

function sp_email_render_cancel_notice() {
    ob_start();
    ?>
    <div style="<?php echo esc_attr(sp_email_box_style('#F2F3F4', '#DCDEE0')); ?>">
      <p style="margin:0;font-size:14px;line-height:1.6;color:#0D0F12;">
        📮 Musste keine Zahlung von dir eingehen (z. B. bei nicht rechtzeitig eingegangener
        Vorkasse-Überweisung), ist das kein Problem &ndash; du musst nichts weiter tun. Hast du
        bereits eine Zahlung veranlasst, melde dich bitte kurz bei uns, damit wir das für dich
        klären können.
      </p>
    </div>
    <?php
    return ob_get_clean();
}

function sp_email_render_completed_tracking_box($tracking_number, $tracking_url) {
    ob_start();
    ?>
    <div style="margin:0 0 22px;border:1px solid #DCDEE0;border-radius:12px;overflow:hidden;">
      <div style="background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);padding:14px 22px;">
        <p style="margin:0;color:#FFFFFF;font-size:15px;font-weight:700;">Sendungsnummer</p>
      </div>
      <div style="padding:18px 22px;">
        <p style="margin:0 0 16px;font-size:19px;font-weight:700;color:#0D0F12;letter-spacing:.01em;"><?php echo esc_html($tracking_number); ?></p>
        <a href="<?php echo esc_url($tracking_url); ?>" style="display:inline-block;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:9px;">Sendung bei DHL verfolgen &rarr;</a>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * @param bool $include_payment_faq Whether to include the "already confirmed"
 *   framing vs. the "how do I know it arrived" framing for the first FAQ item.
 */
function sp_email_render_faq($include_payment_faq = true) {
    ob_start();
    ?>
    <div style="border:1px solid #DCDEE0;border-radius:12px;overflow:hidden;">
      <div style="background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);padding:14px 22px;">
        <p style="margin:0;color:#FFFFFF;font-size:15px;font-weight:700;">Häufige Fragen</p>
      </div>
      <div style="padding:18px 22px;font-size:14px;line-height:1.7;color:#0D0F12;">
        <?php if ($include_payment_faq): ?>
        <p style="margin:0 0 12px;"><strong>Ist meine Bestellung angekommen?</strong> Ja &ndash; diese E-Mail bestätigt deine Bestellung.</p>
        <?php else: ?>
        <p style="margin:0 0 12px;"><strong>Ist meine Zahlung bestätigt?</strong> Ja &ndash; deine Zahlung ist eingegangen, deine Bestellung wird jetzt vorbereitet.</p>
        <?php endif; ?>
        <p style="margin:0 0 12px;"><strong>Wo ist meine Sendungsnummer?</strong> Die schickt dir DHL separat per E-Mail, sobald dein Paket unterwegs ist (bitte auch Spam-Ordner prüfen).</p>
        <p style="margin:0 0 12px;"><strong>Lieferadresse ändern oder stornieren?</strong> Solange noch nicht versendet wurde: antworte auf diese E-Mail mit deiner Bestellnummer.</p>
        <p style="margin:0 0 12px;"><strong>Sind die Produkte geprüft?</strong> Jede Charge ist LC-MS-geprüft, ein chargenspezifisches COA liegt vor.</p>
        <p style="margin:0;"><strong>Kontakt:</strong> info@peptrium.com &ndash; Antwort i.d.R. innerhalb von 24 h.</p>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
