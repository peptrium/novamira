<?php
defined('ABSPATH') || exit;

echo "= " . $email_heading . " =\n\n";

echo "Hallo " . $order->get_billing_first_name() . ",\n\n";
echo "Deine Bestellung ist eingegangen und wird nun bearbeitet.\n\n";

$total_plain = html_entity_decode(wp_strip_all_tags(wc_price($order->get_total(), ['currency' => $order->get_currency()])), ENT_QUOTES, 'UTF-8');

echo "SO GEHT ES JETZT WEITER\n";
echo "1. Überweisen - Betrag " . $total_plain . " auf die unten genannten Bankdaten, Bestellnummer " . $order->get_order_number() . " als Verwendungszweck\n";
echo "2. Zahlungseingang - wir prüfen die Eingänge mehrmals täglich, kein Zahlungsnachweis nötig. Sobald dein Geld da ist, bekommst du eine kurze Bestätigung.\n";
echo "3. Versand - diskret per DHL, am selben oder nächsten Werktag nach Zahlungseingang\n";
echo "4. Sendungsnummer - bekommst du von uns per E-Mail, sobald dein Paket unterwegs ist.\n\n";

$accounts = get_option('woocommerce_bacs_accounts');
if (!empty($accounts) && is_array($accounts)) {
    $acc = $accounts[0];
    echo "UNSERE BANKVERBINDUNG\n";
    echo "Kontoinhaber: " . ($acc['account_name'] ?? '') . "\n";
    echo "Verwendungszweck: " . $order->get_order_number() . "\n";
    echo "IBAN: " . ($acc['iban'] ?? '') . "\n";
    echo "BIC: " . ($acc['bic'] ?? '') . "\n";
    echo "Bank: " . ($acc['bank_name'] ?? '') . "\n";
    echo "Betrag: " . $total_plain . "\n\n";
}

$bacs_gateway = WC()->payment_gateways()->payment_gateways()['bacs'] ?? null;
if ($bacs_gateway) {
    remove_action('woocommerce_email_before_order_table', [$bacs_gateway, 'email_instructions'], 10);
}

echo "----------\n\n";
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
echo "----------\n\n";

if (sp_order_has_preorder_item($order)) {
    echo "HINWEIS ZU DEINER VORBESTELLUNG\nEin oder mehrere Artikel sind aktuell vorbestellt und werden versendet, sobald sie wieder verfügbar sind.\n\n";
}

echo "Der oben genannte Kontoinhaber ist korrekt und gehört zu Peptrium - bitte überweise genau an diese Daten. Einen Zahlungsnachweis brauchst du uns nicht zu schicken.\n";
echo "Sobald deine Zahlung eingegangen ist, bekommst du eine kurze Bestätigung - danach folgt die Versandmail mit Sendungsnummer.\n\n";

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

echo "Fragen? Antworte einfach auf diese E-Mail, schreib an info@peptrium.com oder auf Telegram: https://t.me/peptrium\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
