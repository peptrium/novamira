<?php
/**
 *
 * SliceWP Email Template Name: Peptrium
 *
 * (2026-10-16) Partner-Mails im selben Design wie die Shop-Mails (sp-email-design.php: dunkler Kopf mit Logo,
 * helle Karten, dunkler Fuss mit E-Mail/Telegram). Die Inhalte in den SliceWP-Einstellungen bestehen nur aus
 * einem Marker <!--sp_aff:TYP§§feld§§feld…--> mit den Merge-Tags (gesetzt in sp-affiliate-emails.php);
 * hier wird daraus die eigentliche Mail gebaut (sp_aff_mail_build()). Ohne Marker (z. B. ein in wp-admin
 * frei geschriebener Text) wird der Text einfach im neuen Rahmen ausgegeben.
 *
 * Variablen aus slicewp_wp_email(): $email_content (bereits wpautop'd), $email_subject, $email_recipient.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('sp_aff_mail_build')) {
    function sp_aff_mail_build($type, array $f, $subject) {
        $I = defined('SP_EM_INK') ? SP_EM_INK : '#0D0F12';
        $M = defined('SP_EM_MUTED') ? SP_EM_MUTED : '#5B6169';
        $L = defined('SP_EM_LINE') ? SP_EM_LINE : '#E4E6E9';
        $S = defined('SP_EM_SOFT') ? SP_EM_SOFT : '#F5F6F7';
        $MONO = defined('SP_EM_MONO') ? SP_EM_MONO : 'monospace';
        $g = function ($i) use ($f) { return isset($f[$i]) ? trim(wp_strip_all_tags($f[$i])) : ''; };
        $name = $g(0) !== '' ? esc_html($g(0)) : '';
        $hi = $name !== '' ? 'Hey ' . $name . ',' : 'Hey,';
        $p = function ($t, $extra = '') use ($I) { return '<p style="margin:0 0 14px;font-size:15px;line-height:1.6;color:' . $I . ';' . $extra . '">' . $t . '</p>'; };
        $lbl = function ($t) use ($M) { return '<p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:' . $M . ';">' . $t . '</p>'; };
        $card = function ($inner, $bg = null, $bd = null) use ($S, $L) { return '<div style="background:' . ($bg ?: $S) . ';border:1px solid ' . ($bd ?: $L) . ';border-radius:16px;padding:16px;margin:0 0 14px;">' . $inner . '</div>'; };
        $btn = function ($t, $u, $dark = true) use ($I) {
            $s = $dark ? (defined('SP_EM_GRAD') ? SP_EM_GRAD : 'background:' . $I . ';') . 'color:#FFFFFF;border:1.5px solid ' . $I . ';' : 'background:#FFFFFF;color:' . $I . ';border:1.5px solid ' . $I . ';';
            return '<a href="' . esc_url($u) . '" style="' . $s . 'display:block;text-align:center;text-decoration:none;font-weight:700;font-size:15px;line-height:1.2;padding:15px 12px;border-radius:12px;margin:0 0 10px;">' . $t . '</a>';
        };
        $steps = function ($rows) use ($I, $M, $L) {
            $h = '';
            $n = count($rows);
            foreach ($rows as $i => $r) {
                $done = !empty($r[2]);
                $dot = $done ? '<div style="width:26px;height:26px;line-height:26px;border-radius:50%;background:' . $I . ';color:#FFFFFF;font-size:13px;font-weight:700;text-align:center;">✓</div>'
                    : '<div style="width:24px;height:24px;line-height:24px;border-radius:50%;border:1px solid ' . $I . ';color:' . $I . ';font-size:12px;font-weight:700;text-align:center;">' . ($i + 1) . '</div>';
                $bd = $i < $n - 1 ? 'border-bottom:1px solid ' . $L . ';' : '';
                $h .= '<tr><td width="38" valign="top" style="width:38px;padding:12px 0;' . $bd . '">' . $dot . '</td><td style="padding:12px 0;' . $bd . '"><div style="font-size:15px;font-weight:700;color:' . $I . ';">' . $r[0] . '</div><div style="font-size:13px;line-height:1.5;color:' . $M . ';margin-top:2px;">' . $r[1] . '</div></td></tr>';
            }
            return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 18px;">' . $h . '</table>';
        };
        $big = function ($label, $value, $sub = '') use ($MONO) {
            return '<div style="' . (defined('SP_EM_GRAD') ? SP_EM_GRAD : 'background:#0D0F12;') . 'border-radius:18px;padding:22px 16px;text-align:center;margin:0 0 16px;">'
                . '<div style="font-size:11px;font-weight:700;letter-spacing:.16em;color:#B9BEC5;text-transform:uppercase;">' . $label . '</div>'
                . '<div style="font-size:40px;line-height:1.1;font-weight:800;color:#FFFFFF;margin-top:6px;">' . $value . '</div>'
                . ($sub !== '' ? '<div style="font-size:13px;color:#C9CDD2;margin-top:6px;">' . $sub . '</div>' : '') . '</div>';
        };
        $acct = home_url('/partnerkonto/');
        $terms = home_url('/affiliate-bedingungen/');
        $tg = defined('SP_EM_TELEGRAM') ? SP_EM_TELEGRAM : 'https://t.me/peptrium';
        $hero = ['eyebrow' => 'Partnerprogramm'];
        $head = $subject;
        $b = '';
        switch ($type) {
            case 'registration':
                $head = 'Bewerbung erhalten ✓';
                $hero += ['sub' => 'Danke für dein Interesse an unserem Partnerprogramm.', 'preheader' => 'Wir prüfen deine Bewerbung und melden uns bei dir.'];
                $b .= $p($hi) . $p('wir haben deine Bewerbung erhalten. So geht es jetzt weiter:');
                $b .= $steps([
                    ['Bewerbung eingegangen', 'Deine Angaben sind bei uns angekommen.', true],
                    ['Wir schauen uns deinen Kanal an', 'Wir prüfen, ob unser Programm zu deinem Content passt – in der Regel innerhalb weniger Tage.'],
                    ['Freischaltung per E-Mail', 'Du bekommst deinen persönlichen Partner-Link, deinen Rabattcode und den Zugang zu deinem Partnerkonto.'],
                ]);
                $b .= $card($lbl('Schon mal reinschauen') . '<p style="margin:0 0 12px;font-size:14px;line-height:1.55;color:' . $I . ';">In unseren Partner-Bedingungen steht, wie Provisionen, Auszahlungen und Inhalte bei uns ablaufen.</p>' . $btn('Partner-Bedingungen lesen', $terms, false));
                $b .= $p('Bis bald,<br>Dein Peptrium-Team', 'margin-top:8px;');
                break;

            case 'approved':
                $head = 'Willkommen im Team 🎉';
                $hero += ['sub' => 'Deine Bewerbung wurde angenommen.', 'preheader' => 'Hier sind dein Partner-Link und dein Rabattcode.'];
                $url = $g(1);
                $code = $g(2);
                $b .= $p($hi) . $p('schön, dass du dabei bist! Hier sind deine beiden persönlichen Werkzeuge – beide bringen dir dieselbe Provision.');
                $b .= $card($lbl('🔗 Dein Partner-Link') . '<div style="font-size:15px;font-weight:700;color:' . $I . ';word-break:break-all;font-family:' . $MONO . ';">' . esc_html($url) . '</div><div style="font-size:12px;line-height:1.5;color:' . $M . ';margin-top:8px;">Zum Beispiel in deine Instagram- oder TikTok-Bio oder direkt in deine Posts.</div>');
                if ($code !== '') {
                    $b .= '<div style="border:2px dashed ' . $I . ';border-radius:16px;padding:16px;margin:0 0 14px;text-align:center;">' . $lbl('🏷️ Dein Rabattcode · 10 % ab 25 €')
                        . '<div style="font-size:26px;font-weight:800;color:' . $I . ';font-family:' . $MONO . ';letter-spacing:.04em;word-break:break-all;">' . esc_html($code) . '</div><div style="font-size:12px;line-height:1.5;color:' . $M . ';margin-top:6px;">Nenne ihn in deinem Content – deine Follower geben ihn an der Kasse ein.</div></div>';
                }
                $b .= '<p style="margin:18px 0 6px;font-size:17px;font-weight:800;color:' . $I . ';">Dein Start: das erste Partner-Video</p>';
                $b .= $steps([
                    ['Produkt aussuchen', 'Such dir ein Produkt aus unserem Sortiment aus (außer den Pens) und schreib deinem Creator Manager, welches du haben möchtest – das schenken wir dir.'],
                    ['Kostenlos geliefert', 'Es ist in der Regel innerhalb von 2 Werktagen bei dir.'],
                    ['Erstes Video hochladen', 'Teste in Ruhe und lade dann dein erstes Video mit deinem Code hoch (bitte Punkt 4 der Partner-Bedingungen beachten).'],
                ]);
                $b .= $p('Schreib deinem Creator Manager, welches Produkt du dir aussuchst:', 'font-size:14px;');
                $b .= $btn('Creator Manager per Telegram schreiben', $tg) . $btn('Per E-Mail an info@peptrium.com', 'mailto:info@peptrium.com?subject=' . rawurlencode('Mein Produkt für mein erstes Partner-Video'), false);
                $b .= $card($lbl('💬 Dein Creator Manager') . '<p style="margin:0;font-size:14px;line-height:1.6;color:' . $I . ';">Du hast Fragen zu deinem Content – wie du deine Videos aufbaust, welche Formate gut ankommen oder wie du am effizientesten arbeitest? Tausch dich jederzeit gerne mit deinem Creator Manager aus. Gemeinsam holen wir das Maximum aus deinem Content heraus.</p>');
                $b .= '<div style="height:6px;"></div>' . $btn('Zum Partnerkonto', $g(3) !== '' ? $g(3) : $acct, false);
                $b .= $card('<p style="margin:0;font-size:13.5px;line-height:1.6;color:' . $I . ';"><strong>⚠️ Wichtig:</strong> Bitte lies vor deinem ersten Post unsere <a href="' . esc_url($terms) . '" style="color:' . $I . ';font-weight:700;">Partner-Bedingungen</a> einmal komplett durch.</p>', '#FFF6E5', '#FAB219');
                $b .= $p('Willkommen im Team!<br>Dein Peptrium-Team', 'margin-top:8px;');
                break;

            case 'rejected':
                $head = 'Update zu deiner Bewerbung';
                $hero += ['sub' => 'Danke für dein Interesse an unserem Partnerprogramm.', 'preheader' => 'Eine Rückmeldung zu deiner Bewerbung.'];
                $b .= $p($hi) . $p('vielen Dank für deine Bewerbung. Leider können wir sie im Moment nicht annehmen.');
                if ($g(1) !== '') {
                    $b .= $card($lbl('Begründung') . '<p style="margin:0;font-size:14px;line-height:1.6;color:' . $I . ';">' . nl2br(esc_html($g(1))) . '</p>');
                }
                $b .= $p('Das ist keine Entscheidung für immer – wenn sich dein Kanal weiterentwickelt, kannst du dich gerne später noch einmal bewerben. Bei Fragen erreichst du uns jederzeit per Telegram oder unter info@peptrium.com.');
                $b .= $p('Viele Grüße,<br>Dein Peptrium-Team', 'margin-top:8px;');
                break;

            case 'commission_ok':
                $head = 'Neue Provision ✓';
                $hero += ['sub' => 'Für eine Bestellung über deinen Link oder Code.', 'preheader' => 'Eine neue Provision wurde dir gutgeschrieben.'];
                $b .= $p($hi) . $p('über deinen Partner-Link oder deinen Code wurde bestellt – dafür bekommst du:');
                $b .= $big('Deine Provision', esc_html($g(1)), 'genehmigt');
                if (!empty($f[2]) && trim(wp_strip_all_tags($f[2])) !== '') {
                    $b .= $card(wp_kses_post($f[2]));
                }
                $b .= $btn('Provisionen im Partnerkonto ansehen', $acct);
                $b .= $p('Weiter so!<br>Dein Peptrium-Team', 'margin-top:8px;');
                break;

            case 'commission_no':
                $head = 'Provision abgelehnt';
                $hero += ['sub' => 'Eine Provision konnte nicht gutgeschrieben werden.', 'preheader' => 'Eine Provision wurde abgelehnt.'];
                $b .= $p($hi) . $p('die Provision <strong>#' . esc_html($g(1)) . '</strong> über <strong>' . esc_html($g(2)) . '</strong> wurde leider abgelehnt – zum Beispiel, weil die Bestellung storniert, nicht bezahlt oder zurückgegeben wurde.');
                $b .= $p('Wenn du dazu Fragen hast, schreib uns einfach per Telegram oder an info@peptrium.com.');
                $b .= $btn('Zum Partnerkonto', $acct, false);
                $b .= $p('Viele Grüße,<br>Dein Peptrium-Team', 'margin-top:8px;');
                break;

            case 'payout':
                $head = 'Auszahlung unterwegs 💸';
                $hero += ['sub' => 'Wir haben deine Provisionen ausgezahlt.', 'preheader' => 'Deine Provisionen wurden ausgezahlt.'];
                $b .= $p($hi) . $p('wir haben deine Provisionen ausgezahlt:');
                $b .= $big('Ausgezahlt', esc_html($g(1)));
                $b .= $card('<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="font-size:13px;color:' . $M . ';padding:4px 0;">Zahlungsweg</td><td align="right" style="font-size:14px;font-weight:700;color:' . $I . ';padding:4px 0;">' . esc_html($g(2)) . '</td></tr><tr><td style="font-size:13px;color:' . $M . ';padding:4px 0;border-top:1px solid ' . $L . ';">Auszahlungs-Nr.</td><td align="right" style="font-size:14px;font-weight:700;color:' . $I . ';padding:4px 0;border-top:1px solid ' . $L . ';">#' . esc_html($g(3)) . '</td></tr></table>');
                $b .= $btn('Zum Partnerkonto', $acct, false);
                $b .= $p('Danke für die tolle Zusammenarbeit!<br>Dein Peptrium-Team', 'margin-top:8px;');
                break;

            case 'admin_reg':
                $head = 'Neue Partnerbewerbung';
                $hero = ['eyebrow' => 'Intern', 'sub' => 'Jemand hat sich für das Partnerprogramm beworben.', 'preheader' => 'Neue Bewerbung prüfen.'];
                $b .= $card('<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="font-size:13px;color:' . $M . ';padding:4px 0;">Benutzername</td><td align="right" style="font-size:14px;font-weight:700;color:' . $I . ';padding:4px 0;">' . esc_html($g(1)) . '</td></tr><tr><td style="font-size:13px;color:' . $M . ';padding:4px 0;border-top:1px solid ' . $L . ';">E-Mail</td><td align="right" style="font-size:14px;font-weight:700;color:' . $I . ';padding:4px 0;border-top:1px solid ' . $L . ';word-break:break-all;"><!--email_off-->' . esc_html($g(2)) . '<!--/email_off--></td></tr></table>');
                $b .= $btn('Bewerbung prüfen', admin_url('admin.php?page=slicewp-affiliates'));
                break;

            case 'admin_comm':
                $head = 'Neue Provision';
                $hero = ['eyebrow' => 'Intern', 'sub' => 'Für einen Partner wurde eine Provision erzeugt.', 'preheader' => 'Neue Provision registriert.'];
                $b .= $card('<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="font-size:13px;color:' . $M . ';padding:4px 0;">Partner</td><td align="right" style="font-size:14px;font-weight:700;color:' . $I . ';padding:4px 0;">' . esc_html(trim($g(0) . ' ' . $g(1))) . '</td></tr><tr><td style="font-size:13px;color:' . $M . ';padding:4px 0;border-top:1px solid ' . $L . ';">E-Mail</td><td align="right" style="font-size:14px;font-weight:700;color:' . $I . ';padding:4px 0;border-top:1px solid ' . $L . ';word-break:break-all;"><!--email_off-->' . esc_html($g(2)) . '<!--/email_off--></td></tr></table>');
                $b .= $btn('Provisionen ansehen', admin_url('admin.php?page=slicewp-commissions'));
                break;
        }
        return [$head, $hero, $b];
    }
}

$sp_type = '';
$sp_fields = [];
if (preg_match('/<!--sp_aff:([a-z_]+)(.*?)-->/s', (string) $email_content, $m)) {
    $sp_type = $m[1];
    $sp_fields = explode('§§', ltrim($m[2], '§'));
}

$sp_ok = function_exists('sp_em_hero') && function_exists('wc_get_template_html') && function_exists('WC');
if ($sp_ok) {
    WC()->mailer();
    if ($sp_type !== '') {
        list($sp_head, $sp_hero, $sp_body) = sp_aff_mail_build($sp_type, $sp_fields, $email_subject);
    } else {
        $sp_head = $email_subject;
        $sp_hero = ['eyebrow' => 'Partnerprogramm'];
        $sp_body = '<div style="font-size:15px;line-height:1.6;color:#0D0F12;">' . $email_content . '</div>';
    }
    sp_em_hero($sp_hero);
    $sp_html = wc_get_template_html('emails/email-header.php', ['email_heading' => $sp_head]) . $sp_body . wc_get_template_html('emails/email-footer.php', ['email' => null]);
    $sp_mailer = new WC_Email();
    echo $sp_mailer->style_inline($sp_html);
    return;
}
// Fallback ohne WooCommerce-Design: schlichter Rahmen
?><!doctype html><html><head><meta name="viewport" content="width=device-width"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>
<body style="margin:0;background:#F2F3F4;font-family:Arial,sans-serif;color:#0D0F12;"><div style="max-width:580px;margin:0 auto;padding:24px 12px;"><div style="background:#FFFFFF;border:1px solid #DCDEE0;border-radius:14px;padding:24px;font-size:15px;line-height:1.6;"><?php echo $email_content; ?></div>
<p style="text-align:center;color:#8A9099;font-size:12px;">Peptrium · Nur für Laborforschungszwecke · <a href="https://peptrium.com" style="color:#8A9099;">peptrium.com</a></p></div></body></html>
