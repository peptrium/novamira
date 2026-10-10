<?php
/**
 * Zahlung erhalten (processing) - Peptrium-Design (2026-10-10).
 * Kommt bei Vorkasse, sobald die Zahlung als eingegangen markiert wird, und
 * bei Krypto/Guthaben direkt nach der Bestellung.
 * @see sp-order-emails.php / sp-email-design.php
 */
defined('ABSPATH') || exit;

$first = sp_em_first_name($order);
$kind = sp_em_order_kind($order);
$from_wallet = $order->get_payment_method() === 'sp_wallet';
if ($kind === 'topup') {
    $sub = 'Danke – deine Zahlung ist da. Dein Guthaben ist gutgeschrieben, die Bestätigung mit dem neuen Kontostand bekommst du separat.';
} elseif ($from_wallet) {
    $sub = 'Deine Lieferung wurde automatisch aus deinem Guthaben bezahlt. Wir bereiten sie jetzt für den Versand vor.';
} else {
    $sub = ($first ? 'Danke, ' . esc_html($first) . ' – deine' : 'Danke – deine') . ' Zahlung ist da. Wir bereiten deine Bestellung jetzt für den Versand vor.';
}
sp_em_hero([
    'eyebrow' => 'Bestellung #' . $order->get_order_number(),
    'sub' => $sub,
    'step' => $kind === 'topup' ? null : 1,
    'preheader' => $kind === 'topup' ? 'Deine Zahlung ist eingegangen – dein Guthaben ist gutgeschrieben.' : 'Deine Zahlung ist eingegangen – als Nächstes kommt der Versand.',
]);
do_action('woocommerce_email_header', $email_heading, $email);

if ($kind !== 'topup') {
    echo sp_em_steps('So geht es weiter', [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ['Diskreter Versand', 'Neutral verpackt per DHL, am selben oder nächsten Werktag.'],
        ['Sendungsnummer', 'Bekommst du von uns per E-Mail, sobald dein Paket unterwegs ist.'],
    ]);
}

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

if (sp_order_has_preorder_item($order)) {
    echo sp_em_note('📦 <strong>Vorbestellung:</strong> Mindestens ein Artikel ist vorbestellt. Wir versenden deine Bestellung, sobald alles da ist – du bekommst dann wie gewohnt die Versandmail mit Sendungsnummer.', 'warn'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

if ($kind !== 'topup') echo sp_em_faq([ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ['Adresse ändern?', 'Solange noch nicht versendet wurde: Antworte einfach auf diese E-Mail mit deiner Bestellnummer.'],
    ['Sind die Produkte geprüft?', 'Ja – jede Charge ist LC-MS-geprüft, ein chargenspezifisches Analysezertifikat liegt vor.'],
]);

do_action('woocommerce_email_footer', $email);
