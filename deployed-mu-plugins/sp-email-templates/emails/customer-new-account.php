<?php
/**
 * Konto erstellt - Peptrium-Design (sp-email-design.php, 2026-10-10).
 */
defined('ABSPATH') || exit;

sp_em_hero([
    'sub' => 'Dein Kundenkonto ist bereit.',
    'preheader' => 'Dein Peptrium-Konto ist bereit.',
]);
do_action('woocommerce_email_header', $email_heading, $email);

echo sp_em_p('Hallo ' . esc_html($user_display_name) . ',<br>schön, dass du da bist! Dein Konto ist eingerichtet.'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

$inner = sp_em_label('Deine Zugangsdaten')
    . '<p style="margin:0;font-size:14px;color:' . SP_EM_MUTED . ';">Benutzername / E-Mail</p>'
    . '<p style="margin:2px 0 0;font-size:16px;font-weight:700;color:' . SP_EM_INK . ';word-break:break-all;">' . esc_html($user_login) . '</p>';
if (!empty($password_generated) && !empty($set_password_url)) {
    $inner .= '<p style="margin:16px 0 0;">' . sp_em_button('Passwort festlegen', $set_password_url) . '</p>';
}
echo sp_em_card($inner); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo sp_em_steps('In deinem Konto kannst du', [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    ['Bestellungen verfolgen', 'Status und Sendungsnummer jederzeit im Blick.'],
    ['Abo verwalten', 'Lieferungen anpassen, pausieren oder fortsetzen.'],
    ['Guthaben nutzen', 'Aufladen und automatisch für Abo-Lieferungen verwenden.'],
]);

echo '<div style="text-align:center;margin:4px 0 26px;">' . sp_em_button('Zu meinem Konto', wc_get_page_permalink('myaccount')) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

do_action('woocommerce_email_footer', $email);
