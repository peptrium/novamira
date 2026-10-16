<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Branded SliceWP affiliate emails (application received / approved /
 * rejected) - SliceWP ships these as plain, unstyled, English-only text by
 * default (no logo, no template matching the rest of the site's mail). This
 * swaps in the same dark logo-banner look used on the WooCommerce order
 * emails (sp-order-emails.php) and rewrites the copy in German. Only
 * overrides the template's HTML shell - SliceWP still owns subject/content
 * merge-tag replacement and the actual sending logic.
 */
add_filter('slicewp_register_email_templates', function ($email_templates) {
    if (isset($email_templates['default'])) {
        $email_templates['default']['path'] = __DIR__ . '/slicewp-email-templates/template-peptrium.php';
        $email_templates['default']['name'] = 'Peptrium';
    }
    return $email_templates;
});

/**
 * German copy for all eight SliceWP email notifications (admin-facing and
 * partner-facing). Written directly to the option (not through SliceWP's
 * settings form) so it can't drift from a manual edit in wp-admin
 * overwriting the whole array - merges onto whatever's already stored
 * (subject/content only) instead of replacing it outright, so each
 * notification's own enabled/toggle state in wp-admin stays untouched.
 */
add_action('init', function () {
    $settings = get_option('slicewp_settings', []);
    if (!is_array($settings)) {
        return;
    }

    $notifications = $settings['email_notifications'] ?? [];

    // (2026-10-16) Inhalte sind nur noch Marker mit den Merge-Tags; die eigentliche Mail baut
    // slicewp-email-templates/template-peptrium.php im Design der Shop-Mails (sp_aff_mail_build()).
    // 'enabled' => '1': alle Partner-Mails sind aktiv (Nutzer: "die Mails sollen aktiv sein").
    $mk = function ($type, array $tags) {
        return '<!--sp_aff:' . $type . '§§' . implode('§§', $tags) . '-->';
    };
    $german = [
        // Admin-facing.
        'admin_new_affiliate_registration' => [
            'enabled' => '1',
            'subject' => 'Neue Partnerbewerbung eingegangen',
            'content' => $mk('admin_reg', ['{{affiliate_first_name}}', '{{affiliate_username}}', '{{affiliate_email}}']),
        ],
        'admin_new_commission_registered' => [
            'enabled' => '1',
            'subject' => 'Neue Provision registriert',
            'content' => $mk('admin_comm', ['{{affiliate_first_name}}', '{{affiliate_last_name}}', '{{affiliate_email}}']),
        ],
        // Partner-facing.
        'affiliate_account_registration' => [
            'enabled' => '1',
            'subject' => 'Bewerbung erhalten – Peptrium Partnerprogramm',
            'content' => $mk('registration', ['{{affiliate_first_name}}']),
        ],
        'affiliate_account_approved' => [
            'enabled' => '1',
            'subject' => 'Bewerbung angenommen – Willkommen im Partnerprogramm',
            'content' => $mk('approved', ['{{affiliate_first_name}}', '{{affiliate_referral_url}}', '{{affiliate_coupon_code}}', '{{page_affiliate_account}}']),
        ],
        'affiliate_account_rejected' => [
            'enabled' => '1',
            'subject' => 'Update zu deiner Partnerprogramm-Bewerbung',
            'content' => $mk('rejected', ['{{affiliate_first_name}}', '{{affiliate_rejection_reason}}']),
        ],
        'affiliate_commission_approved' => [
            'enabled' => '1',
            'subject' => 'Neue Provision für dich ✓',
            'content' => $mk('commission_ok', ['{{affiliate_first_name}}', '{{commission_amount}}', '{{affiliate_tier_status}}']),
        ],
        'affiliate_commission_rejected' => [
            'enabled' => '1',
            'subject' => 'Provision abgelehnt',
            'content' => $mk('commission_no', ['{{affiliate_first_name}}', '{{commission_id}}', '{{commission_amount}}']),
        ],
        'affiliate_payment_paid' => [
            'enabled' => '1',
            'subject' => 'Auszahlung unterwegs 💸',
            'content' => $mk('payout', ['{{affiliate_first_name}}', '{{payment_amount}}', '{{payment_payout_method}}', '{{payment_id}}']),
        ],
    ];

    foreach ($german as $slug => $copy) {
        $notifications[$slug] = array_merge($notifications[$slug] ?? [], $copy);
    }

    if (($settings['email_notifications'] ?? null) !== $notifications) {
        $settings['email_notifications'] = $notifications;
        update_option('slicewp_settings', $settings);
    }
}, 5);
