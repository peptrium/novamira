<?php
/**
 * Passwort zuruecksetzen - Peptrium-Design (sp-email-design.php, 2026-10-10).
 */
defined('ABSPATH') || exit;

$reset_url = add_query_arg(
    ['key' => $reset_key, 'id' => $user_id, 'login' => rawurlencode($user_login)],
    wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount'))
);

sp_em_hero([
    'sub' => 'Für dein Konto wurde ein neues Passwort angefordert.',
    'preheader' => 'Lege jetzt ein neues Passwort für dein Peptrium-Konto fest.',
]);
do_action('woocommerce_email_header', $email_heading, $email);

echo sp_em_p('Hallo ' . esc_html($user_display_name) . ',<br>für dein Konto <strong>' . esc_html($user_login) . '</strong> wurde ein neues Passwort angefordert. Über den Button legst du es fest:'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo '<div style="text-align:center;margin:8px 0 24px;">' . sp_em_button('Neues Passwort festlegen', $reset_url) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo sp_em_note('🔒 Du hast das nicht angefordert? Dann ignoriere diese E-Mail einfach – dein Passwort bleibt unverändert.'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

do_action('woocommerce_email_footer', $email);
