<?php
defined('ABSPATH') || exit;

echo "= " . $email_heading . " =\n\n";

echo "Hallo " . $order->get_billing_first_name() . ",\n\n";
echo "Deine Zahlung ist bei uns eingegangen - deine Bestellung wird jetzt vorbereitet.\n\n";

echo "SO GEHT ES JETZT WEITER\n";
echo "1. Versand - diskret per DHL, am selben oder nächsten Werktag\n";
echo "2. Sendungsnummer - bekommst du von uns per E-Mail, sobald dein Paket unterwegs ist.\n\n";

echo "----------\n\n";
do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
echo "----------\n\n";

if (sp_order_has_preorder_item($order)) {
    echo "HINWEIS ZU DEINER VORBESTELLUNG\nEin oder mehrere Artikel sind aktuell vorbestellt und werden versendet, sobald sie wieder verfügbar sind.\n\n";
}

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

echo "Fragen? Antworte einfach auf diese E-Mail, schreib an info@peptrium.com oder auf Telegram: https://t.me/peptrium\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
