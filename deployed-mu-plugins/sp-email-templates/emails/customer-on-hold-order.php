<?php
/**
 * Bestellbestaetigung Vorkasse (on-hold) - Peptrium-Design (2026-10-10).
 * Ablauf, der hier beschrieben wird (so laeuft es wirklich):
 * Bestellung -> Ueberweisung -> du markierst sie in der Vorkasse-Uebersicht
 * als bezahlt -> Kunde bekommt "Zahlung erhalten" (processing) -> Versand,
 * Sendungsnummer kommt per Versendet-Button als "Paket unterwegs"-Mail.
 * @see sp-order-emails.php / sp-email-design.php
 */
defined('ABSPATH') || exit;

$kind = sp_em_order_kind($order);
sp_em_hero([
    'eyebrow' => 'Bestellung #' . $order->get_order_number(),
    'sub' => $kind === 'topup'
        ? 'Deine Aufladung ist eingegangen. Jetzt nur noch überweisen – das Guthaben schreiben wir dir nach Zahlungseingang gut.'
        : 'Deine Bestellung ist eingegangen. Jetzt nur noch überweisen – wir versenden nach Zahlungseingang.',
    'step' => $kind === 'topup' ? null : 0,
    'preheader' => 'So überweist du in 2 Minuten – alle Bankdaten in dieser E-Mail.',
]);
do_action('woocommerce_email_header', $email_heading, $email);

echo sp_em_pay_box($order); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ($kind === 'topup') {
    echo sp_em_steps('So geht es weiter', [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ['Zahlungseingang', 'Wir prüfen die Eingänge mehrmals täglich – ein Zahlungsnachweis ist nicht nötig.'],
        ['Gutschrift', 'Sobald dein Geld da ist, schreiben wir dir das Guthaben gut und schicken dir eine Bestätigung per E-Mail.'],
    ]);
} else {
    $steps = [
        ['Zahlungseingang', 'Wir prüfen die Eingänge mehrmals täglich – ein Zahlungsnachweis ist nicht nötig. Sobald dein Geld da ist, bekommst du eine kurze Bestätigung.'],
        ['Diskreter Versand', 'Neutral verpackt per DHL, am selben oder nächsten Werktag nach Zahlungseingang.'],
        ['Sendungsnummer', 'Bekommst du von uns per E-Mail, sobald dein Paket unterwegs ist.'],
    ];
    if ($kind === 'mixed') {
        $steps[0][1] .= ' Dein Guthaben schreiben wir dir dann gleich mit gut.';
    }
    echo sp_em_steps('So geht es weiter', $steps); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/* Eigene Bankdaten-Karte oben - die BACS-Standardanweisungen sonst doppelt. */
$bacs_gateway = WC()->payment_gateways()->payment_gateways()['bacs'] ?? null;
if ($bacs_gateway) {
    remove_action('woocommerce_email_before_order_table', [$bacs_gateway, 'email_instructions'], 10);
}
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

if (sp_order_has_preorder_item($order)) {
    echo sp_em_note('📦 <strong>Vorbestellung:</strong> Mindestens ein Artikel ist vorbestellt. Wir versenden deine Bestellung, sobald alles da ist – du bekommst dann wie gewohnt die Versandmail mit Sendungsnummer.', 'warn'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

$faq = [['Muss ich einen Zahlungsnachweis schicken?', 'Nein. Wir ordnen deine Zahlung über die Bestellnummer im Verwendungszweck zu.']];
if ($kind !== 'topup') {
    $faq[] = ['Adresse ändern oder stornieren?', 'Solange noch nicht versendet wurde: Antworte einfach auf diese E-Mail mit deiner Bestellnummer.'];
    $faq[] = ['Sind die Produkte geprüft?', 'Ja – jede Charge ist LC-MS-geprüft, ein chargenspezifisches Analysezertifikat liegt vor.'];
}
echo sp_em_faq($faq); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

do_action('woocommerce_email_footer', $email);
