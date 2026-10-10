<?php
/**
 * Plugin Name: SP Email Design
 * Description: Einheitliches, modernes Design fuer alle Shop-Mails (2026-10-10).
 *
 * Dunkler Kopfbereich (Chrom-Logo + Schriftzug PEPTRIUM, Bestellnummer,
 * Ueberschrift, Fortschritt Bestellt -> Bezahlt -> Versendet -> Zugestellt),
 * heller Inhalt mit Karten, dunkler Footer mit E-Mail + Telegram.
 *
 * Wirkt auf ALLE Mails, die ueber WooCommerce laufen: die Bestell-Mails
 * (Vorlagen in sp-email-templates/emails/), WooCommerce-Standardmails
 * (Konto, Passwort, Erstattung ...) und alle eigenen Mails, die ueber
 * WC()->mailer()->wrap_message() gebaut werden (sp_abo_send_branded_email():
 * Abo, Guthaben, Warenkorb-Erinnerung, Zahlungserinnerung).
 *
 * Bausteine fuer die Vorlagen: sp_em_*(). Kopfbereich je Mail ueber
 * sp_em_hero([...]) VOR do_action('woocommerce_email_header') setzen;
 * ohne Angabe leitet email-header.php einen passenden Standard ab.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_EM_INK', '#0D0F12');
define('SP_EM_MUTED', '#5B6169');
define('SP_EM_LINE', '#E4E6E9');
define('SP_EM_SOFT', '#F5F6F7');
define('SP_EM_GRAD', 'background-color:#0D0F12;background-image:linear-gradient(135deg,#0D0F12 0%,#23272C 55%,#3A3F45 100%);');
define('SP_EM_FONT', "-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif");
define('SP_EM_MONO', "'SFMono-Regular',Consolas,Menlo,monospace");
define('SP_EM_TELEGRAM', 'https://t.me/peptrium');
/* Abo-Akzent (wie auf der Website): Orange-Rot-Verlauf fuer Buttons in Abo-/Guthaben-Mails. */
define('SP_EM_ORANGE', 'background-color:#FF6B35;background-image:linear-gradient(135deg,#FF6B35 0%,#E5342B 100%);');

/* Vorlagen, die diese Datei zusaetzlich zu sp-order-emails.php ersetzt. */
add_filter('woocommerce_locate_template', function ($template, $template_name) {
    $mine = [
        'emails/email-footer.php',
        'emails/email-order-details.php',
        'emails/email-order-items.php',
        'emails/email-addresses.php',
        'emails/customer-new-account.php',
        'emails/customer-reset-password.php',
    ];
    if (in_array($template_name, $mine, true)) {
        $custom = WPMU_PLUGIN_DIR . '/sp-email-templates/' . $template_name;
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 21, 2);

/* Kopfbereich der naechsten Mail setzen (Aufruf ohne Argument = abholen und leeren). */
function sp_em_hero($args = null) {
    static $hero = null;
    if ($args !== null) {
        $hero = $args;
        return null;
    }
    $out = $hero;
    $hero = null;
    return $out;
}

/* Standard-Kopfbereich fuer Mails ohne eigene Angabe (WooCommerce-Standardmails). */
function sp_em_default_hero($email) {
    $order = ($email && isset($email->object) && $email->object instanceof WC_Order) ? $email->object : null;
    if (!$order) {
        return [];
    }
    $hero = ['eyebrow' => 'Bestellung #' . $order->get_order_number()];
    switch ($email->id) {
        case 'customer_refunded_order':
            $hero['sub'] = 'Wir haben eine Erstattung für deine Bestellung veranlasst.';
            break;
        case 'customer_failed_order':
            $hero['sub'] = 'Bei deiner Bestellung ist leider etwas schiefgelaufen.';
            break;
        case 'customer_note':
            $hero['sub'] = 'Wir haben eine Nachricht zu deiner Bestellung für dich.';
            break;
    }
    return $hero;
}

function sp_em_first_name($order) {
    $n = $order ? trim($order->get_billing_first_name()) : '';
    return $n;
}

/* Fortschritt: 0 Bestellt, 1 Bezahlt, 2 Versendet, 3 Zugestellt. Linie als eigene Zellen (funktioniert in allen Mail-Programmen). */
function sp_em_tracker($active) {
    $steps = ['Bestellt', 'Bezahlt', 'Versendet', 'Zugestellt'];
    $n = count($steps);
    $line = function ($c) {
        return '<td style="padding:14px 0 0;"><div style="height:2px;line-height:2px;font-size:0;background:' . $c . ';">&nbsp;</div></td>';
    };
    $cells = '';
    foreach ($steps as $i => $label) {
        $done = $i <= $active;
        $lc = $i === 0 ? 'transparent' : ($i <= $active ? '#FFFFFF' : '#4A4F56');
        $rc = $i === $n - 1 ? 'transparent' : ($i < $active ? '#FFFFFF' : '#4A4F56');
        $mark = $i < $active ? '&#10003;' : (string) ($i + 1);
        $cells .= '<td valign="top" style="width:25%;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>' . $line($lc)
            . '<td width="30" style="width:30px;"><div style="width:30px;height:30px;line-height:27px;border-radius:50%;border:1.5px solid ' . ($done ? '#FFFFFF' : '#5B6169') . ';background:' . ($done ? '#FFFFFF' : '#1A1D21') . ';color:' . ($done ? SP_EM_INK : '#8A9099') . ';font-size:13px;font-weight:700;text-align:center;box-sizing:border-box;">' . $mark . '</div></td>'
            . $line($rc) . '</tr></table>'
            . '<div style="font-size:11px;font-weight:600;letter-spacing:.02em;color:' . ($done ? '#FFFFFF' : '#8A9099') . ';text-align:center;margin-top:8px;">' . $label . '</div></td>';
    }
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;"><tr>' . $cells . '</tr></table>';
}

function sp_em_label($text) {
    return '<p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:' . SP_EM_MUTED . ';">' . $text . '</p>';
}

function sp_em_card($inner, $bg = SP_EM_SOFT, $border = SP_EM_LINE, $extra = '') {
    return '<div style="background:' . $bg . ';border:1px solid ' . $border . ';border-radius:16px;padding:20px;margin:0 0 22px;' . $extra . '">' . $inner . '</div>';
}

/* $dark: true = dunkel, false = weiss mit Rand, 'abo' = Orange (Abo-Akzent). */
function sp_em_button($text, $url, $dark = true) {
    if ($dark === 'abo') {
        $style = SP_EM_ORANGE . 'color:#FFFFFF;border:1.5px solid #E5342B;';
    } elseif ($dark) {
        $style = SP_EM_GRAD . 'color:#FFFFFF;border:1.5px solid #0D0F12;';
    } else {
        $style = 'background:#FFFFFF;color:' . SP_EM_INK . ';border:1.5px solid ' . SP_EM_INK . ';';
    }
    return '<a href="' . esc_url($url) . '" style="' . $style . 'display:inline-block;text-decoration:none;font-weight:700;font-size:15px;line-height:1.2;padding:14px 26px;border-radius:12px;">' . $text . '</a>';
}

function sp_em_p($html, $extra = '') {
    return '<p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:' . SP_EM_INK . ';' . $extra . '">' . $html . '</p>';
}

/* Nummerierte Schritte in einer Karte. $steps = [[Titel, Text], ...] */
function sp_em_steps($title, $steps) {
    $html = sp_em_label($title);
    $last = count($steps) - 1;
    foreach ($steps as $i => $s) {
        $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:' . ($i < $last ? '14' : '0') . 'px;"><tr>'
            . '<td width="36" valign="top" style="width:36px;"><div style="width:26px;height:26px;line-height:26px;border-radius:50%;background:' . SP_EM_INK . ';color:#FFFFFF;font-size:12px;font-weight:700;text-align:center;">' . ($i + 1) . '</div></td>'
            . '<td style="font-size:14px;line-height:1.55;color:' . SP_EM_INK . ';"><strong>' . $s[0] . '</strong><br><span style="color:' . SP_EM_MUTED . ';">' . $s[1] . '</span></td></tr></table>';
    }
    return sp_em_card($html);
}

/* Kurze Fragen & Antworten. $items = [[Frage, Antwort], ...] */
function sp_em_faq($items) {
    $html = sp_em_label('Häufige Fragen');
    $last = count($items) - 1;
    foreach ($items as $i => $it) {
        $html .= '<p style="margin:0 0 ' . ($i < $last ? '12' : '0') . 'px;font-size:14px;line-height:1.55;color:' . SP_EM_INK . ';"><strong>' . $it[0] . '</strong><br><span style="color:' . SP_EM_MUTED . ';">' . $it[1] . '</span></p>';
    }
    return sp_em_card($html, '#FFFFFF');
}

/* Hinweis-Box: 'soft' (grau) oder 'warn' (orange). */
function sp_em_note($html, $tone = 'soft') {
    if ($tone === 'warn') {
        return sp_em_card('<p style="margin:0;font-size:14px;line-height:1.6;color:' . SP_EM_INK . ';">' . $html . '</p>', '#FFF4E5', '#F5C77E');
    }
    return sp_em_card('<p style="margin:0;font-size:14px;line-height:1.6;color:' . SP_EM_INK . ';">' . $html . '</p>');
}

/* Ueberweisungs-Karte (Vorkasse). */
function sp_em_pay_box($order) {
    $accounts = get_option('woocommerce_bacs_accounts');
    if (empty($accounts) || !is_array($accounts)) {
        return '';
    }
    $acc = $accounts[0];
    $total = wp_kses_post(wc_price($order->get_total(), ['currency' => $order->get_currency()]));
    $iban = trim(chunk_split(str_replace(' ', '', (string) ($acc['iban'] ?? '')), 4, ' '));
    $rows = [
        ['Empfänger', esc_html($acc['account_name'] ?? ''), false],
        ['IBAN', esc_html($iban), true],
        ['BIC', esc_html($acc['bic'] ?? ''), true],
        ['Bank', esc_html($acc['bank_name'] ?? ''), false],
        ['Betrag', $total, false],
    ];
    $table = '';
    foreach ($rows as $r) {
        $mono = $r[2] ? 'font-family:' . SP_EM_MONO . ';letter-spacing:.02em;' : '';
        $table .= '<tr><td style="padding:9px 0;border-bottom:1px solid ' . SP_EM_LINE . ';font-size:12px;color:' . SP_EM_MUTED . ';width:30%;vertical-align:top;">' . $r[0] . '</td>'
            . '<td style="padding:9px 0;border-bottom:1px solid ' . SP_EM_LINE . ';font-size:15px;font-weight:700;color:' . SP_EM_INK . ';' . $mono . '">' . $r[1] . '</td></tr>';
    }
    return '<div style="border-radius:16px;overflow:hidden;border:1.5px solid ' . SP_EM_INK . ';margin:0 0 22px;">'
        . '<div style="' . SP_EM_GRAD . 'padding:16px 20px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
        . '<td style="font-size:12px;font-weight:700;letter-spacing:.12em;color:#B9BEC5;text-transform:uppercase;">Zu überweisen</td>'
        . '<td align="right" style="font-size:24px;font-weight:800;color:#FFFFFF;">' . $total . '</td></tr></table></div>'
        . '<div style="padding:16px 20px 18px;background:#FFFFFF;">'
        . '<div style="background:#FFF4E5;border:1px solid #F5C77E;border-radius:12px;padding:12px 14px;margin-bottom:12px;">'
        . '<div style="font-size:11px;font-weight:700;letter-spacing:.1em;color:#9A5B00;text-transform:uppercase;">Verwendungszweck – bitte unbedingt angeben</div>'
        . '<div style="font-size:26px;font-weight:800;color:' . SP_EM_INK . ';font-family:' . SP_EM_MONO . ';margin-top:4px;">' . esc_html($order->get_order_number()) . '</div>'
        . '<div style="font-size:12px;color:#9A5B00;margin-top:2px;">Ohne Verwendungszweck können wir deine Zahlung nicht zuordnen.</div></div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $table . '</table>'
        . '<p style="margin:12px 0 0;font-size:12px;line-height:1.55;color:' . SP_EM_MUTED . ';">🔒 Der Empfänger ist korrekt und gehört zu Peptrium – bitte überweise genau an diese Daten.<br>💡 Lange auf IBAN oder Verwendungszweck tippen zum Kopieren. Mit Echtzeitüberweisung ist dein Geld in Minuten da.</p>'
        . '</div></div>';
}

/* Sendungsnummer-Karte. */
function sp_em_tracking_box($number, $url) {
    return '<div style="border-radius:16px;border:1px solid ' . SP_EM_LINE . ';padding:22px 20px 24px;text-align:center;background:' . SP_EM_SOFT . ';margin:0 0 22px;">'
        . sp_em_label('DHL-Sendungsnummer')
        . '<div style="font-size:22px;font-weight:800;color:' . SP_EM_INK . ';font-family:' . SP_EM_MONO . ';letter-spacing:.02em;margin:-2px 0 18px;word-break:break-all;">' . esc_html($number) . '</div>'
        . sp_em_button('Sendung verfolgen &rarr;', $url)
        . '</div>';
}

/* Drei kleine Vertrauenspunkte (Versandmail). */
function sp_em_trust_row() {
    $items = [
        ['🔒', 'Diskret verpackt', 'Neutraler Karton ohne Hinweis auf den Inhalt'],
        ['🧪', 'LC-MS geprüft', 'Jede Charge mit Analysezertifikat'],
        ['❄️', 'Richtig lagern', 'Kühl, trocken und lichtgeschützt'],
    ];
    $cells = '';
    foreach ($items as $it) {
        $cells .= '<td align="center" valign="top" width="33%" style="padding:0 4px;"><div style="font-size:22px;line-height:1;">' . $it[0] . '</div>'
            . '<div style="font-size:12px;font-weight:700;color:' . SP_EM_INK . ';margin-top:8px;">' . $it[1] . '</div>'
            . '<div style="font-size:11px;color:' . SP_EM_MUTED . ';margin-top:3px;line-height:1.4;">' . $it[2] . '</div></td>';
    }
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 26px;"><tr>' . $cells . '</tr></table>';
}

/* Abo-Hinweis fuer Kunden ohne Abo (Versandmail). */
function sp_em_abo_teaser() {
    return sp_em_card(
        '<p style="margin:0 0 4px;font-size:15px;font-weight:700;color:' . SP_EM_INK . ';">Nie wieder nachbestellen vergessen</p>'
        . '<p style="margin:0 0 16px;font-size:14px;line-height:1.55;color:' . SP_EM_MUTED . ';">Mit dem Abo kommt deine Lieferung automatisch – ab der 2. Lieferung 15&nbsp;% günstiger. Jederzeit pausierbar.</p>'
        . sp_em_button('Abo-Modell ansehen', home_url('/abo-modell/'), false)
    );
}

function sp_em_order_has_abo($order) {
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_abo_interval_days')) {
            return true;
        }
    }
    return false;
}

/* Kundenmails: Block "Zusaetzliche Informationen" (AGB/18 Jahre: Ja) weglassen - nur Admin-Mails behalten ihn. */
add_action('woocommerce_email', function ($emails) {
    if (!is_callable([$emails, 'additional_checkout_fields'])) {
        return;
    }
    remove_action('woocommerce_email_customer_details', [$emails, 'additional_checkout_fields'], 30);
    add_action('woocommerce_email_customer_details', function ($order, $sent_to_admin = false, $plain_text = false) use ($emails) {
        if ($sent_to_admin) {
            $emails->additional_checkout_fields($order, $sent_to_admin, $plain_text);
        }
    }, 30, 3);
});

/* Ueberschriften der Bestellmails (Betreff bleibt wie in sp-order-emails.php). */
add_filter('woocommerce_email_heading_customer_on_hold_order', function ($heading, $order) {
    if (!$order) {
        return $heading;
    }
    $n = sp_em_first_name($order);
    return $n ? 'Danke, ' . $n . '! 🙌' : 'Danke für deine Bestellung! 🙌';
}, 30, 2);

add_filter('woocommerce_email_heading_customer_processing_order', function ($heading, $order) {
    if (!$order) {
        return $heading;
    }
    if ($order->get_payment_method() === 'sp_wallet') {
        return 'Abo-Lieferung bezahlt ✓';
    }
    return 'Zahlung erhalten ✓';
}, 30, 2);

add_filter('woocommerce_email_heading_customer_completed_order', function ($heading, $order) {
    if (!$order) {
        return $heading;
    }
    $has_tracking = defined('SP_TRACKING_META_KEY') && $order->get_meta(SP_TRACKING_META_KEY);
    return $has_tracking ? 'Dein Paket ist unterwegs 📦' : 'Bestellung abgeschlossen';
}, 30, 2);

add_filter('woocommerce_email_heading_customer_new_account', function () {
    return 'Willkommen bei Peptrium';
}, 30);

add_filter('woocommerce_email_heading_customer_reset_password', function () {
    return 'Neues Passwort festlegen';
}, 30);

/* CSS-Ergaenzungen (werden von WooCommerce inline gesetzt). Ueberschreibt die generischen WC-Regeln fuer unseren Rahmen. */
add_filter('woocommerce_email_styles', function ($css) {
    $css .= "\n#sp-em-hero h1{color:#FFFFFF !important;font-size:27px !important;line-height:1.2 !important;font-weight:800 !important;letter-spacing:-.01em !important;margin:10px 0 8px !important;text-align:center !important;font-family:" . SP_EM_FONT . " !important;}"
        . "\n#sp-em-hero img,#sp-em-body img,#sp-em-foot img{margin:0 !important;}"
        . "\n#sp-em-body{font-family:" . SP_EM_FONT . ";font-size:15px;line-height:1.6;color:" . SP_EM_INK . ";text-align:left;}"
        . "\n#sp-em-body p{margin:0 0 16px;}"
        . "\n#sp-em-body h2{font-size:18px;font-weight:800;color:" . SP_EM_INK . ";margin:0 0 12px;}"
        . "\n#sp-em-body h3{font-size:15px;font-weight:700;color:" . SP_EM_INK . ";margin:0 0 6px;}"
        . "\n#sp-em-body a{color:" . SP_EM_INK . ";}"
        . "\n#sp-em-body ul.wc-item-meta{margin:0;padding:0;list-style:none;}";
    return $css;
}, 99);

/* Aeltere Mail-Bausteine (Abo/Guthaben/Zahlung, inline gestylt in den jeweiligen
   Plugins) an das neue Design angleichen: groessere Buttons, sichtbare Trennlinien
   auf dem helleren Kartengrund. Greift auf den fertigen Mail-Inhalt - der ist da
   schon von WooCommerce "inlined" (Leerzeichen nach ":"), daher Regex. */
add_filter('woocommerce_mail_content', function ($html) {
    return preg_replace(
        ['/font-size:\s*14px;\s*padding:\s*12px 22px;\s*border-radius:\s*9px;/', '/border-bottom:\s*1px solid #F2F3F4;/i'],
        ['font-size: 15px; padding: 14px 26px; border-radius: 12px;', 'border-bottom: 1px solid #E4E6E9;'],
        $html
    );
}, 20);

/* Art der Bestellung fuer passende Texte: 'topup' = nur Guthaben-Aufladung,
   'mixed' = Produkte + Aufladung, 'normal' = nur Produkte. */
function sp_em_order_kind($order) {
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    $has_topup = false;
    $has_other = false;
    foreach ($order->get_items() as $item) {
        if ($topup && (int) $item->get_product_id() === $topup) {
            $has_topup = true;
        } else {
            $has_other = true;
        }
    }
    if ($has_topup && !$has_other) {
        return 'topup';
    }
    return $has_topup ? 'mixed' : 'normal';
}

/* Abo-Mails: dunkle Buttons auf Orange umstellen (neue und aeltere Button-Stile,
   mit oder ohne Leerzeichen nach ":"). */
function sp_em_abo_accent($html) {
    return preg_replace(
        [
            '/background-color:\s*#0D0F12;\s*background-image:\s*linear-gradient\(135deg,\s*#0D0F12 0%,\s*#23272C 55%,\s*#3A3F45 100%\);\s*color:\s*#FFFFFF;\s*border:\s*1\.5px solid #0D0F12;/i',
            '/background:\s*linear-gradient\(135deg,\s*#0D0F12 0%,\s*#2A2E33 100%\);\s*color:\s*#FFFFFF;/i',
        ],
        [
            SP_EM_ORANGE . 'color:#FFFFFF;border:1.5px solid #E5342B;',
            SP_EM_ORANGE . 'color:#FFFFFF;',
        ],
        $html
    );
}
