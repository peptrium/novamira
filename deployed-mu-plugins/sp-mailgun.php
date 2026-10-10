<?php
/**
 * Plugin Name: SP Mailgun (Newsletter-Versand)
 * Description: Schickt alle Mails mit Absender @news.peptrium.com ueber die Mailgun-HTTP-API (2026-10-10).
 *
 * Warum: Shop-Mails laufen ueber WP Mail SMTP -> SMTP2GO, dort sind Peptide laut AGB
 * verboten -> Newsletter/Marketing duerfen da nie durch. FluentCRM schickt mit
 * Absender newsletter@news.peptrium.com; dieser Filter faengt genau diese Mails in
 * pre_wp_mail ab und schickt sie per Mailgun-API (HTTP - die Server-IP taucht so
 * nicht als Absender-Station in den Kopfzeilen auf). Fehlt der API-Schluessel, wird
 * die Mail NICHT verschickt (kein Ausweichen auf SMTP2GO).
 *
 * Einstellungen: Peptrium Dashboard -> Newsletter-Versand (Schluessel, Testmail).
 * Domain news.peptrium.com, Region US (api.mailgun.net) - EU ging beim Anlegen nicht.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_MG_DOMAIN', 'news.peptrium.com');
define('SP_MG_API', 'https://api.mailgun.net/v3/');
define('SP_MG_FROM', 'Peptrium <newsletter@news.peptrium.com>');
define('SP_MG_REPLY_TO', 'info@peptrium.com');

function sp_mg_key() {
    return (string) get_option('sp_mg_key', '');
}

/** Header-Liste (String oder Array) in [name => wert] zerlegen. */
function sp_mg_parse_headers($headers) {
    if (!is_array($headers)) {
        $headers = preg_split('/\r\n|\n/', (string) $headers);
    }
    $out = [];
    foreach ($headers as $k => $h) {
        if (!is_int($k)) {
            $out[strtolower($k)] = ['name' => $k, 'value' => $h];
            continue;
        }
        if (strpos((string) $h, ':') === false) {
            continue;
        }
        list($name, $value) = array_map('trim', explode(':', $h, 2));
        $out[strtolower($name)] = ['name' => $name, 'value' => $value];
    }
    return $out;
}

/** Eine Mail ueber die Mailgun-API senden. Rueckgabe true oder Fehlertext. */
function sp_mg_send($to, $subject, $html, $headers = []) {
    $key = sp_mg_key();
    if ($key === '') {
        return 'Kein Mailgun-API-Schlüssel hinterlegt.';
    }
    $h = sp_mg_parse_headers($headers);
    $body = [
        'from' => isset($h['from']) && stripos($h['from']['value'], '@' . SP_MG_DOMAIN) !== false ? $h['from']['value'] : SP_MG_FROM,
        'to' => is_array($to) ? implode(',', $to) : $to,
        'subject' => $subject,
        'html' => $html,
        'text' => trim(html_entity_decode(wp_strip_all_tags(preg_replace('/<(style|script)[^>]*>.*?<\/\1>/is', '', $html)), ENT_QUOTES, 'UTF-8')),
        'h:Reply-To' => isset($h['reply-to']) ? $h['reply-to']['value'] : SP_MG_REPLY_TO,
        'o:tracking' => 'no', // Oeffnungen/Klicks misst FluentCRM selbst
    ];
    foreach ($h as $lk => $hh) {
        if (in_array($lk, ['from', 'reply-to', 'content-type', 'mime-version', 'cc', 'bcc'], true)) {
            continue;
        }
        $body['h:' . $hh['name']] = $hh['value']; // z. B. List-Unsubscribe von FluentCRM
    }
    $res = wp_remote_post(SP_MG_API . SP_MG_DOMAIN . '/messages', [
        'timeout' => 20,
        'headers' => ['Authorization' => 'Basic ' . base64_encode('api:' . $key)],
        'body' => $body,
    ]);
    if (is_wp_error($res)) {
        return $res->get_error_message();
    }
    $code = wp_remote_retrieve_response_code($res);
    if ($code !== 200) {
        return 'Mailgun HTTP ' . $code . ': ' . substr(wp_remote_retrieve_body($res), 0, 200);
    }
    return true;
}

/* Alle Mails mit Absender @news.peptrium.com ueber Mailgun statt ueber WP Mail SMTP/SMTP2GO. */
add_filter('pre_wp_mail', function ($return, $atts) {
    if ($return !== null) {
        return $return;
    }
    $h = sp_mg_parse_headers($atts['headers'] ?? []);
    if (!isset($h['from']) || stripos($h['from']['value'], '@' . SP_MG_DOMAIN) === false) {
        return null; // normale Shop-Mail -> unveraendert ueber SMTP2GO
    }
    $r = sp_mg_send($atts['to'], $atts['subject'], $atts['message'], $atts['headers'] ?? []);
    if ($r !== true) {
        update_option('sp_mg_last_error', current_time('mysql') . ' – ' . $r, false);
        return false;
    }
    return true;
}, 5, 2);

/* ---------------------------------------------------------------------
 * Admin: Peptrium Dashboard -> Newsletter-Versand
 * ------------------------------------------------------------------- */
add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Newsletter-Versand', 'Newsletter-Versand', 'manage_options', 'sp-mailgun', 'sp_mg_render_page');
}, 99);

function sp_mg_render_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $msg = '';
    if (!empty($_POST['sp_mg_save']) && check_admin_referer('sp_mg_settings')) {
        $new = trim((string) wp_unslash($_POST['sp_mg_key'] ?? ''));
        if ($new !== '') {
            update_option('sp_mg_key', $new, false);
            $msg = 'API-Schlüssel gespeichert.';
        }
    }
    if (!empty($_POST['sp_mg_test']) && check_admin_referer('sp_mg_settings')) {
        $to = sanitize_email(wp_unslash($_POST['sp_mg_to'] ?? ''));
        $html = function_exists('sp_em_hero') && function_exists('wc_get_template_html')
            ? (function () {
                WC()->mailer();
                sp_em_hero(['sub' => 'Diese Testmail kam über Mailgun.']);
                $h = wc_get_template_html('emails/email-header.php', ['email_heading' => 'Newsletter-Versand funktioniert ✓'])
                    . '<p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Wenn du das liest, ist Mailgun richtig angebunden. Öffne in deinem Mailprogramm die Kopfzeilen („Header anzeigen“) und schick die <strong>Received:</strong>-Zeilen an Claude für den Test, ob deine Server-IP auftaucht.</p>'
                    . wc_get_template_html('emails/email-footer.php', ['email' => null]);
                return (new WC_Email())->style_inline($h);
            })()
            : '<p>Testmail über Mailgun.</p>';
        $r = $to ? sp_mg_send($to, 'Testmail: Newsletter-Versand über Mailgun', $html, ['From: ' . SP_MG_FROM]) : 'Bitte eine Empfänger-Adresse angeben.';
        $msg = $r === true ? 'Testmail an ' . esc_html($to) . ' verschickt.' : 'Fehler: ' . esc_html($r);
    }
    $key = sp_mg_key();
    $err = get_option('sp_mg_last_error', '');
    ?>
    <div class="wrap">
      <h1>Newsletter-Versand (Mailgun)</h1>
      <?php if ($msg) : ?><div class="notice notice-info"><p><?php echo esc_html($msg); ?></p></div><?php endif; ?>
      <p>Newsletter-Mails (FluentCRM, Absender <code>newsletter@news.peptrium.com</code>) laufen über Mailgun. Shop-Mails laufen unverändert über SMTP2GO.</p>
      <form method="post">
        <?php wp_nonce_field('sp_mg_settings'); ?>
        <table class="form-table">
          <tr><th>Status</th><td><?php echo $key ? '✅ API-Schlüssel hinterlegt (endet auf …' . esc_html(substr($key, -4)) . ')' : '❌ Noch kein API-Schlüssel'; ?><br><small>Domain: <?php echo esc_html(SP_MG_DOMAIN); ?> · Region US</small></td></tr>
          <tr><th><label for="sp_mg_key">Mailgun API-Schlüssel</label></th><td><input type="password" id="sp_mg_key" name="sp_mg_key" class="regular-text" autocomplete="off" placeholder="<?php echo $key ? 'neuen Schlüssel einfügen, um ihn zu ersetzen' : 'hier einfügen'; ?>"> <button class="button button-primary" name="sp_mg_save" value="1">Speichern</button></td></tr>
          <tr><th><label for="sp_mg_to">Testmail an</label></th><td><input type="email" id="sp_mg_to" name="sp_mg_to" class="regular-text" value="<?php echo esc_attr(get_option('admin_email')); ?>"> <button class="button" name="sp_mg_test" value="1">Testmail senden</button></td></tr>
          <?php if ($err) : ?><tr><th>Letzter Fehler</th><td><code><?php echo esc_html($err); ?></code></td></tr><?php endif; ?>
        </table>
      </form>
    </div>
    <?php
}
