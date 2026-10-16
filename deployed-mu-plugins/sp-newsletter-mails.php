<?php
/**
 * Plugin Name: SP Newsletter-Mails
 * Description: Automatische Newsletter-Mails (Willkommen, Nachkauf, Rueckgewinnung) - 2026-10-10.
 *
 * Nur an bestaetigte Newsletter-Abonnenten (FluentCRM-Liste "newsletter", Status
 * subscribed) - Werbung ohne Einwilligung ist in DE nicht erlaubt. Versand mit
 * Absender @news.peptrium.com -> laeuft ueber Mailgun (sp-mailgun.php), nie ueber
 * SMTP2GO.
 *
 * AUS bis Mailgun freigeschaltet ist: Option sp_nlm_enabled = 1 schaltet den
 * automatischen Versand ein (taeglicher WP-Cron sp_nlm_daily).
 *
 * 1. Willkommen  - direkt nach der Bestaetigung, mit dem persoenlichen 10-%-Code.
 * 2. Nachkauf    - 28 Tage nach einer bezahlten Peptid-Bestellung, wenn seitdem
 *                  nichts mehr bestellt wurde und kein Abo laeuft. Mit Link, der
 *                  dieselben Artikel wieder in den Warenkorb legt (?sp_nachkauf=).
 * 3. Rueckgewinnung - 50 Tage nach der letzten Bestellung, einmalig, neuer Code.
 * 4. Code-Erinnerung - 5 Tage (HALLO) bzw. 3 Tage (COMEBACK) vor Ablauf, nur wenn
 *                  der Code noch nicht benutzt wurde.
 * 5. Nachkauf 2    - 14 Tage nach der ersten Nachkauf-Mail, wenn immer noch nichts
 *                  bestellt wurde; kurz, ohne Rabatt.
 * Zustellbarkeit: Betreff/Vorschauzeile ohne "Gutschein", "%" und Wirkstoffnamen;
 * hoechstens SP_NLM_DAILY_CAP automatische Mails pro Tag (neue Domain langsam
 * aufwaermen, Mailgun-Free erlaubt 100/Tag).
 * Links tragen utm_source=newsletter (Analyse-Tab zeigt "newsletter" als Quelle).
 *
 * OMNISEND (2026-10-15): Mailgun ist gesperrt. Ist Option sp_nlm_omnisend = 1 (und ein Omnisend-API-Schluessel in
 * sp-omnisend-sync.php hinterlegt), entscheidet dieser Cron weiter WER WANN welche Mail bekommt, verschickt aber
 * nichts selbst: er setzt die noetigen Werte als Omnisend-Kontakt-Eigenschaften (Code, Ablauf, Artikel, Link) und
 * loest ein eigenes Omnisend-Ereignis aus (sp_code_erinnerung, sp_nachkauf, sp_nachkauf_2, sp_rueckgewinnung).
 * Die passende Omnisend-Automation verschickt dann die Mail im Peptrium-Design. Die Willkommensmail kommt
 * direkt aus Omnisend (Automation "Welcome Email"), hier bleibt sie aus.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_NLM_FROM', 'Peptrium <newsletter@news.peptrium.com>');
define('SP_NLM_REORDER_DAYS', 28);
define('SP_NLM_WINBACK_DAYS', 50);
define('SP_NLM_WINBACK_PERCENT', 10);
define('SP_NLM_WINBACK_COUPON_DAYS', 14);
define('SP_NLM_REORDER2_AFTER', 14);
define('SP_NLM_DAILY_CAP', 60);

function sp_nlm_enabled() {
    return (bool) get_option('sp_nlm_enabled', 0);
}

function sp_nlm_url($path, $campaign, $extra = []) {
    return add_query_arg(array_merge(['utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => $campaign], $extra), home_url($path));
}

function sp_nlm_unsub_url($subscriber) {
    if (!$subscriber || !function_exists('fluentCrmGetContactManagedHash')) {
        return home_url('/');
    }
    return add_query_arg([
        'fluentcrm' => 1,
        'route' => 'unsubscribe',
        'secure_hash' => fluentCrmGetContactManagedHash($subscriber->id),
    ], site_url('/'));
}

/* Produktkarte (Bild, Name, Preis "ab", Link). */
function sp_nlm_product_card($product_id, $campaign) {
    $p = wc_get_product($product_id);
    if (!$p) {
        return '';
    }
    $img = wp_get_attachment_image_url($p->get_image_id(), 'woocommerce_thumbnail') ?: wc_placeholder_img_src();
    $price = $p->is_type('variable') ? $p->get_variation_price('min', true) : wc_get_price_to_display($p);
    $name = preg_replace('/\s*\(.*?\)\s*$/', '', $p->get_name());
    $href = sp_nlm_url(wp_make_link_relative(get_permalink($product_id)), $campaign);
    return '<td width="50%" valign="top" style="padding:6px;"><a href="' . esc_url($href) . '" style="text-decoration:none;color:' . SP_EM_INK . ';display:block;border:1px solid ' . SP_EM_LINE . ';border-radius:16px;overflow:hidden;">'
        . '<div style="background:' . SP_EM_SOFT . ';text-align:center;padding:14px 0;"><img src="' . esc_url($img) . '" alt="" height="110" style="height:110px;width:auto;max-width:100%;border:0;display:inline-block;margin:0;"></div>'
        . '<div style="padding:12px 14px 14px;"><div style="font-size:15px;font-weight:700;">' . esc_html($name) . '</div>'
        . '<div style="font-size:13px;color:' . SP_EM_MUTED . ';margin-top:2px;">' . ($p->is_type('variable') ? 'ab ' : '') . wp_kses_post(wc_price($price)) . '</div>'
        . '<div style="margin-top:10px;font-size:13px;font-weight:700;">Ansehen &rarr;</div></div></a></td>';
}

function sp_nlm_product_grid($ids, $campaign) {
    $cells = array_values(array_filter(array_map(function ($id) use ($campaign) {
        return sp_nlm_product_card($id, $campaign);
    }, $ids)));
    $rows = '';
    for ($i = 0; $i < count($cells); $i += 2) {
        $rows .= '<tr>' . $cells[$i] . ($cells[$i + 1] ?? '<td width="50%"></td>') . '</tr>';
    }
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;">' . $rows . '</table>';
}

function sp_nlm_coupon_card($code, $line, $btn_url) {
    return '<div style="border:2px dashed ' . SP_EM_INK . ';border-radius:18px;padding:24px 18px;text-align:center;margin:0 0 24px;">'
        . '<div style="font-size:12px;font-weight:700;letter-spacing:.14em;color:' . SP_EM_MUTED . ';text-transform:uppercase;">Dein Gutscheincode</div>'
        . '<div style="font-size:30px;font-weight:900;letter-spacing:.06em;margin:8px 0 4px;color:' . SP_EM_INK . ';font-family:' . SP_EM_MONO . ';">' . esc_html($code) . '</div>'
        . '<div style="font-size:13px;color:' . SP_EM_MUTED . ';margin-bottom:18px;">' . $line . '</div>'
        . sp_em_button('Jetzt einlösen &rarr;', $btn_url) . '</div>';
}

function sp_nlm_usps() {
    $rows = '';
    foreach ([['🧪', 'LC-MS geprüft', 'jede Charge mit Analysezertifikat'], ['📦', 'Diskreter Versand', 'neutral verpackt per DHL'], ['🎁', 'Gratis-Geschenke', 'ab 200 € Bestellwert'], ['🚚', 'Gratisversand', 'ab 150 € innerhalb Deutschlands']] as $u) {
        $rows .= '<table role="presentation" width="100%" style="margin-bottom:10px;"><tr><td width="30" valign="top" style="font-size:17px;">' . $u[0] . '</td><td style="font-size:14px;line-height:1.5;color:' . SP_EM_INK . ';"><strong>' . $u[1] . '</strong> <span style="color:' . SP_EM_MUTED . ';">– ' . $u[2] . '</span></td></tr></table>';
    }
    return sp_em_card(sp_em_label('Warum Peptrium') . $rows);
}

/* Rahmen + Abmelde-Hinweis um den Inhalt. */
function sp_nlm_wrap($heading, $hero, $body, $subscriber) {
    WC()->mailer();
    sp_em_hero($hero);
    $unsub = '<p style="margin:6px 0 20px;font-size:12px;line-height:1.6;color:' . SP_EM_MUTED . ';text-align:center;">Du bekommst diese E-Mail, weil du dich für den Peptrium-Newsletter angemeldet hast.<br><a href="' . esc_url(sp_nlm_unsub_url($subscriber)) . '" style="color:' . SP_EM_MUTED . ';">Vom Newsletter abmelden</a></p>';
    $html = wc_get_template_html('emails/email-header.php', ['email_heading' => $heading]) . $body . $unsub . wc_get_template_html('emails/email-footer.php', ['email' => null]);
    return (new WC_Email())->style_inline($html);
}

/* ---------------- 1) Willkommen ---------------- */
function sp_nlm_welcome_html($subscriber, $code) {
    $first = trim((string) ($subscriber->first_name ?? ''));
    $body = sp_em_p(($first ? 'Hallo ' . esc_html($first) . ',' : 'Hallo,') . '<br>schön, dass du dabei bist! Ab jetzt bekommst du Neuheiten, Aktionen und Forschungs-Updates direkt in dein Postfach. Als Dankeschön ist hier dein Willkommensgeschenk:')
        . sp_nlm_coupon_card($code, SP_NL_COUPON_PERCENT . ' % auf deine nächste Bestellung · ' . SP_NL_COUPON_DAYS . ' Tage gültig', sp_nlm_url('/alle-produkte/', 'willkommen'))
        . sp_em_card('<p style="margin:0 0 8px;font-size:15px;font-weight:700;color:' . SP_EM_INK . ';">Wer hinter Peptrium steht</p><p style="margin:0;font-size:14px;line-height:1.6;color:' . SP_EM_MUTED . ';">Hinter Peptrium steht ein kleines Team mit einem Anspruch: Forschungspeptide in geprüfter Qualität, ehrlich beschrieben und schnell bei dir. Jede Charge wird LC-MS-geprüft, bevor sie in den Versand geht. Wenn du Fragen hast, antworte einfach auf diese E-Mail – wir lesen jede Nachricht selbst.<br><br>– Dein Peptrium-Team</p>', '#FFFFFF')
        . sp_em_label('Beliebt bei unseren Kunden')
        . sp_nlm_product_grid([65, 68, 393, 71], 'willkommen')
        . sp_nlm_usps();
    return sp_nlm_wrap('Schön, dass du da bist.', ['eyebrow' => 'Willkommen bei Peptrium', 'sub' => 'Dein Willkommensgeschenk für die nächste Bestellung ist da.', 'preheader' => 'Schön, dass du dabei bist – dein Willkommensgeschenk wartet.'], $body, $subscriber);
}

/* ---------------- 2) Nachkauf ---------------- */
function sp_nlm_reorder_token($order) {
    $t = $order->get_meta('_sp_nlm_reorder');
    if (!$t) {
        $t = wp_generate_password(24, false, false);
        $order->update_meta_data('_sp_nlm_reorder', $t);
        $order->save();
    }
    return $t;
}

function sp_nlm_reorder_html($subscriber, $order) {
    $first = trim((string) ($subscriber->first_name ?? '')) ?: $order->get_billing_first_name();
    $rows = '';
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('Geschenk') || (int) $item->get_product_id() === $topup || (float) $item->get_total() <= 0) {
            continue;
        }
        $p = $item->get_product();
        $iid = $p ? ($p->get_image_id() ?: (($pp = wc_get_product($p->get_parent_id())) ? $pp->get_image_id() : 0)) : 0;
        $img = $iid ? wp_get_attachment_image_url($iid, 'woocommerce_thumbnail') : wc_placeholder_img_src();
        $rows .= '<tr><td width="68" style="width:68px;padding:10px 0;border-bottom:1px solid ' . SP_EM_LINE . ';"><div style="width:56px;height:56px;border-radius:12px;background:#FFFFFF;overflow:hidden;text-align:center;"><img src="' . esc_url($img) . '" alt="" height="56" style="height:56px;width:auto;max-width:56px;border:0;margin:0;"></div></td>'
            . '<td style="padding:10px 8px;border-bottom:1px solid ' . SP_EM_LINE . ';font-size:14px;font-weight:700;color:' . SP_EM_INK . ';">' . esc_html($item->get_name()) . '<div style="font-size:12px;font-weight:500;color:' . SP_EM_MUTED . ';margin-top:3px;">Menge: ' . (int) $item->get_quantity() . '</div></td></tr>';
    }
    $again = sp_nlm_url('/', 'nachkauf', ['sp_nachkauf' => sp_nlm_reorder_token($order)]);
    $body = sp_em_p(($first ? 'Hallo ' . esc_html($first) . ',' : 'Hallo,') . '<br>deine letzte Bestellung ist jetzt rund vier Wochen her. Falls deine Forschung weiterläuft: Mit einem Klick liegt alles wieder im Warenkorb.')
        . sp_em_card(sp_em_label('Deine letzte Bestellung') . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">' . $rows . '</table>'
            . '<div style="text-align:center;margin-top:18px;">' . sp_em_button('Gleiche Bestellung nochmal &rarr;', $again) . '</div>')
        . sp_em_card('<p style="margin:0 0 4px;font-size:15px;font-weight:700;color:' . SP_EM_INK . ';">Tipp: Mehrere auf einmal sparen</p><p style="margin:0;font-size:14px;line-height:1.55;color:' . SP_EM_MUTED . ';">Bei vielen Peptiden gilt: ab 3 Stück −10 %, ab 5 Stück −15 %, ab 10 Stück −20 % (nicht auf bereits reduzierte Artikel).</p>', '#FFFFFF')
        . sp_em_card('<p style="margin:0 0 4px;font-size:15px;font-weight:700;color:' . SP_EM_INK . ';">Nie wieder nachbestellen</p><p style="margin:0 0 16px;font-size:14px;line-height:1.55;color:' . SP_EM_MUTED . ';">Mit dem Abo kommt deine Lieferung automatisch – ab der 2. Lieferung 15 % günstiger, jederzeit pausierbar.</p>' . sp_em_button('Abo-Modell ansehen', sp_nlm_url('/abo-modell/', 'nachkauf'), 'abo'));
    return sp_nlm_wrap('Zeit für Nachschub?', ['eyebrow' => 'Peptrium', 'sub' => 'Deine letzte Bestellung ist rund vier Wochen her.', 'preheader' => 'Mit einem Klick liegt deine letzte Bestellung wieder im Warenkorb.'], $body, $subscriber);
}

/* ?sp_nachkauf=<token>: Artikel der Bestellung in den Warenkorb, dann zur Kasse. */
add_action('template_redirect', function () {
    if (empty($_GET['sp_nachkauf']) || !function_exists('WC')) {
        return;
    }
    $token = preg_replace('/[^A-Za-z0-9]/', '', (string) $_GET['sp_nachkauf']);
    $ids = strlen($token) === 24 ? wc_get_orders(['limit' => 1, 'return' => 'ids', 'meta_key' => '_sp_nlm_reorder', 'meta_value' => $token]) : [];
    $order = $ids ? wc_get_order($ids[0]) : null;
    if ($order && WC()->cart) {
        $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
        foreach ($order->get_items() as $item) {
            if ($item->get_meta('Geschenk') || (int) $item->get_product_id() === $topup || (float) $item->get_total() <= 0) {
                continue;
            }
            $p = $item->get_product();
            if ($p && $p->is_purchasable() && $p->is_in_stock()) {
                WC()->cart->add_to_cart($item->get_product_id(), $item->get_quantity(), $item->get_variation_id(), $item->get_variation_id() ? $p->get_variation_attributes() : []);
            }
        }
    }
    wp_safe_redirect(wc_get_checkout_url());
    exit;
}, 5);

/* ---------------- 3) Rueckgewinnung ---------------- */
function sp_nlm_winback_coupon($subscriber) {
    $code = (string) $subscriber->getMeta('sp_nlm_winback', 'peptrium');
    if ($code && wc_get_coupon_id_by_code($code)) {
        return $code;
    }
    do {
        $code = 'COMEBACK-' . strtoupper(wp_generate_password(5, false, false));
    } while (wc_get_coupon_id_by_code($code));
    $c = new WC_Coupon();
    $c->set_code($code);
    $c->set_discount_type('percent');
    $c->set_amount(SP_NLM_WINBACK_PERCENT);
    $c->set_individual_use(true);
    $c->set_usage_limit(1);
    $c->set_usage_limit_per_user(1);
    $c->set_email_restrictions([$subscriber->email]);
    $c->set_date_expires(time() + SP_NLM_WINBACK_COUPON_DAYS * DAY_IN_SECONDS);
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    if ($topup) {
        $c->set_excluded_product_ids([$topup]);
    }
    $c->set_description('Newsletter-Rückgewinnung (' . $subscriber->email . ')');
    $c->save();
    $subscriber->updateMeta('sp_nlm_winback', $code, 'peptrium');
    return $code;
}

function sp_nlm_winback_html($subscriber, $code) {
    $first = trim((string) ($subscriber->first_name ?? ''));
    $body = sp_em_p(($first ? 'Hallo ' . esc_html($first) . ',' : 'Hallo,') . '<br>wir haben dich eine Weile nicht gesehen. Damit dir der Wiedereinstieg leichter fällt, haben wir einen persönlichen Gutschein für dich:')
        . sp_nlm_coupon_card($code, SP_NLM_WINBACK_PERCENT . ' % auf deine nächste Bestellung · nur ' . SP_NLM_WINBACK_COUPON_DAYS . ' Tage gültig', sp_nlm_url('/alle-produkte/', 'rueckgewinnung'))
        . sp_em_label('Das ist neu und beliebt')
        . sp_nlm_product_grid([65, 68, 393, 908], 'rueckgewinnung')
        . sp_nlm_usps();
    return sp_nlm_wrap('Wir vermissen dich', ['eyebrow' => 'Peptrium', 'sub' => 'Wir haben etwas für dich – nur für kurze Zeit.', 'preheader' => 'Wir haben etwas für dich – nur für kurze Zeit.'], $body, $subscriber);
}

/* ---------------- 4) Code-Erinnerung ---------------- */
function sp_nlm_expiry_html($subscriber, $code, $days_left, $percent) {
    $first = trim((string) ($subscriber->first_name ?? ''));
    $body = sp_em_p(($first ? 'Hallo ' . esc_html($first) . ',' : 'Hallo,') . '<br>kurze Erinnerung: Dein persönlicher Code läuft in <strong>' . (int) $days_left . ' ' . ($days_left === 1 ? 'Tag' : 'Tagen') . '</strong> ab und ist noch nicht eingelöst.')
        . sp_nlm_coupon_card($code, $percent . ' % auf deine Bestellung · gilt nur noch ' . (int) $days_left . ' ' . ($days_left === 1 ? 'Tag' : 'Tage'), sp_nlm_url('/alle-produkte/', 'code-erinnerung'))
        . sp_em_p('Gib den Code im Warenkorb unter „Rabattcode eingeben“ ein. Er gilt mit deiner E-Mail-Adresse.', 'font-size:13px;color:' . SP_EM_MUTED . ';text-align:center;');
    return sp_nlm_wrap('Dein Code läuft bald ab', ['eyebrow' => 'Peptrium', 'sub' => 'Nur noch ' . (int) $days_left . ' ' . ($days_left === 1 ? 'Tag' : 'Tage') . ' – danach verfällt er.', 'preheader' => 'Kurze Erinnerung, bevor es zu spät ist.'], $body, $subscriber);
}

/* ---------------- 5) Nachkauf 2 (kurzer Stups, ohne Rabatt) ---------------- */
function sp_nlm_reorder2_html($subscriber, $order) {
    $first = trim((string) ($subscriber->first_name ?? '')) ?: $order->get_billing_first_name();
    $again = sp_nlm_url('/', 'nachkauf-2', ['sp_nachkauf' => sp_nlm_reorder_token($order)]);
    $body = sp_em_p(($first ? 'Hallo ' . esc_html($first) . ',' : 'Hallo,') . '<br>nur ein kurzer Hinweis: Deine letzte Bestellung liegt mit einem Klick wieder im Warenkorb – Menge und Artikel kannst du an der Kasse noch anpassen.')
        . '<div style="text-align:center;margin:8px 0 26px;">' . sp_em_button('Gleiche Bestellung nochmal &rarr;', $again) . '</div>'
        . sp_em_p('Lieber automatisch? Mit dem <a href="' . esc_url(sp_nlm_url('/abo-modell/', 'nachkauf-2')) . '" style="color:' . SP_EM_INK . ';font-weight:700;">Abo</a> kommt deine Lieferung von selbst – ab der 2. Lieferung 15 % günstiger.', 'font-size:14px;color:' . SP_EM_MUTED . ';');
    return sp_nlm_wrap('Noch alles da?', ['eyebrow' => 'Peptrium', 'sub' => 'Deine letzte Bestellung – mit einem Klick wieder im Warenkorb.', 'preheader' => 'Mit einem Klick wieder im Warenkorb.'], $body, $subscriber);
}

/* Unbenutzter Code des Abonnenten mit Restlaufzeit (Tage) oder null. */
function sp_nlm_open_coupon($subscriber, $meta_key) {
    $code = (string) $subscriber->getMeta($meta_key, 'peptrium');
    $id = $code ? wc_get_coupon_id_by_code($code) : 0;
    if (!$id) {
        return null;
    }
    $c = new WC_Coupon($id);
    $exp = $c->get_date_expires();
    if (!$exp || $c->get_usage_count() > 0) {
        return null;
    }
    $left = (int) ceil(($exp->getTimestamp() - time()) / DAY_IN_SECONDS);
    return $left > 0 ? [$code, $left, (int) $c->get_amount()] : null;
}

/* ---------------- Omnisend-Versand ---------------- */
function sp_nlm_omni() {
    return get_option('sp_nlm_omnisend', 0) && function_exists('sp_os_key') && sp_os_key() !== '' && function_exists('sp_os_send');
}

/* Kontakt-Eigenschaften setzen, dann eigenes Ereignis senden. true bei Erfolg. */
function sp_nlm_omni_event($subscriber, $event, array $props) {
    if (!$subscriber || $subscriber->status !== 'subscribed') {
        return false;
    }
    $day = 'sp_nlm_sent_' . current_time('Ymd');
    $count = (int) get_transient($day);
    if ($count >= SP_NLM_DAILY_CAP) {
        return false; // Rest kommt am naechsten Tag dran
    }
    $email = strtolower($subscriber->email);
    /* Fehlt der Kontakt in Omnisend (z. B. Sync fehlgeschlagen), erst vollstaendig mit Code anlegen. */
    if (function_exists('sp_os_contact_status') && sp_os_contact_status($email) === '' && function_exists('sp_os_payload')) {
        sp_os_send(sp_os_payload($subscriber, 'subscribed'));
    }
    $r = function_exists('sp_os_event') ? sp_os_event($email, $event, $props, 'subscribed') : 'sp_os_event fehlt';
    if ($r !== true) {
        update_option('sp_os_last_error', current_time('mysql') . ' – ' . $email . ' (' . $event . '): ' . $r, false);
        return false;
    }
    set_transient($day, $count + 1, 2 * DAY_IN_SECONDS);
    update_option('sp_os_last_ok', current_time('mysql') . ' – ' . $email . ' (' . $event . ')', false);
    return true;
}

/* Bestellte Artikel als kurzer Text, z. B. "GHK-Cu (2×), Bac Water (1×)". */
function sp_nlm_items_text($order) {
    $out = [];
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('Geschenk') || (int) $item->get_product_id() === $topup || (float) $item->get_total() <= 0) {
            continue;
        }
        $out[] = $item->get_name() . ' (' . (int) $item->get_quantity() . '×)';
    }
    return mb_substr(implode(', ', $out), 0, 1500);
}

/* Zwei Bild-Plaetze fuer die Nachkauf-Mail: die ersten bestellten Artikel, freie Plaetze mit passendem Zubehoer. */
function sp_nlm_slots($order) {
    $img = function ($p) {
        $iid = $p ? ($p->get_image_id() ?: (($pp = wc_get_product($p->get_parent_id())) ? $pp->get_image_id() : 0)) : 0;
        return $iid ? (string) wp_get_attachment_image_url($iid, 'woocommerce_thumbnail') : wc_placeholder_img_src();
    };
    $slots = [];
    $have = [];
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    foreach ($order->get_items() as $item) {
        if (count($slots) >= 2 || $item->get_meta('Geschenk') || (int) $item->get_product_id() === $topup || (float) $item->get_total() <= 0) {
            continue;
        }
        $have[] = (int) $item->get_product_id();
        $slots[] = [$img($item->get_product()), $item->get_name(), 'Menge: ' . (int) $item->get_quantity()];
    }
    foreach ([74, 80, 68] as $sid) {
        if (count($slots) >= 2) {
            break;
        }
        $p = wc_get_product($sid);
        if ($p && !in_array($sid, $have, true) && $p->is_purchasable()) {
            $slots[] = [$img($p), $p->get_name(), 'Passt dazu · ' . html_entity_decode(wp_strip_all_tags(wc_price($p->get_price())), ENT_QUOTES, 'UTF-8')];
        }
    }
    $out = [];
    foreach ($slots as $i => $sl) {
        $out['nachkauf_bild_' . ($i + 1)] = $sl[0];
        $out['nachkauf_name_' . ($i + 1)] = $sl[1];
        $out['nachkauf_info_' . ($i + 1)] = $sl[2];
    }
    return $out;
}

/* Omnisend (wenn aktiv) oder alter wp_mail-Weg. $html_fn wird nur im alten Weg gerendert. */
function sp_nlm_dispatch($subscriber, $event, array $props, $subject, $html_fn) {
    if (sp_nlm_omni()) {
        return sp_nlm_omni_event($subscriber, $event, $props);
    }
    return sp_nlm_send($subscriber, $subject, $html_fn());
}

/* ---------------- Versand ---------------- */
function sp_nlm_send($subscriber, $subject, $html) {
    if (!sp_nlm_enabled() || !$subscriber || $subscriber->status !== 'subscribed') {
        return false;
    }
    $day = 'sp_nlm_sent_' . current_time('Ymd');
    $count = (int) get_transient($day);
    if ($count >= SP_NLM_DAILY_CAP) {
        return false; // Rest kommt am naechsten Tag dran (Flags werden erst nach Erfolg gesetzt)
    }
    set_transient($day, $count + 1, 2 * DAY_IN_SECONDS);
    return wp_mail($subscriber->email, $subject, $html, [
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . SP_NLM_FROM,
        'Reply-To: info@peptrium.com',
        'List-Unsubscribe: <' . sp_nlm_unsub_url($subscriber) . '>',
        'List-Unsubscribe-Post: List-Unsubscribe=One-Click',
    ]);
}

/* Willkommen direkt nach der Bestaetigung (nach dem Gutschein aus sp-newsletter.php, Prio 20). */
add_action('fluentcrm_subscriber_status_to_subscribed', function ($subscriber) {
    if (!sp_nlm_enabled() || !function_exists('sp_nl_ensure_coupon') || $subscriber->getMeta('sp_nlm_welcome_sent', 'peptrium')) {
        return;
    }
    if (!$subscriber->lists()->where('slug', SP_NL_LIST_SLUG)->exists()) {
        return;
    }
    $code = sp_nl_ensure_coupon($subscriber);
    if ($code && sp_nlm_send($subscriber, 'Willkommen bei Peptrium 👋', sp_nlm_welcome_html($subscriber, $code))) {
        $subscriber->updateMeta('sp_nlm_welcome_sent', current_time('mysql'), 'peptrium');
    }
}, 30, 1);

/* Taeglich: Nachkauf + Rueckgewinnung. */
add_action('init', function () {
    if (!wp_next_scheduled('sp_nlm_daily')) {
        wp_schedule_event(strtotime('tomorrow 10:00', current_time('timestamp')) - (int) (get_option('gmt_offset') * HOUR_IN_SECONDS), 'daily', 'sp_nlm_daily');
    }
});

add_action('sp_nlm_daily', function () {
    if ((!sp_nlm_enabled() && !sp_nlm_omni()) || !class_exists('\FluentCrm\App\Models\Subscriber')) {
        return;
    }
    $subs = \FluentCrm\App\Models\Subscriber::where('status', 'subscribed')->whereHas('lists', function ($q) {
        $q->where('slug', SP_NL_LIST_SLUG);
    })->get();
    $now = time();
    foreach ($subs as $sub) {
        /* Code-Erinnerungen: HALLO 5 Tage, COMEBACK 3 Tage vor Ablauf, je einmal. */
        foreach ([['sp_nl_coupon', 5, 'sp_nlm_exp1_sent'], ['sp_nlm_winback', 3, 'sp_nlm_exp2_sent']] as $cfg) {
            $open = sp_nlm_open_coupon($sub, $cfg[0]);
            if ($open && $open[1] <= $cfg[1] && $sub->getMeta($cfg[2], 'peptrium') !== $open[0]) {
                if (sp_nlm_dispatch($sub, 'sp_code_erinnerung', ['erinnerung_code' => $open[0], 'erinnerung_tage' => $open[1] . ' ' . ($open[1] === 1 ? 'Tag' : 'Tage'), 'erinnerung_prozent' => $open[2] . ' %', 'erinnerung_zahl' => (string) $open[1], 'erinnerung_label' => $open[1] === 1 ? 'TAG ÜBRIG' : 'TAGE ÜBRIG', 'erinnerung_bis' => sp_nlm_coupon_until($open[0])], 'Dein Code läuft bald ab', function () use ($sub, $open) { return sp_nlm_expiry_html($sub, $open[0], $open[1], $open[2]); })) {
                    $sub->updateMeta($cfg[2], $open[0], 'peptrium');
                }
            }
        }
        $orders = wc_get_orders(['billing_email' => $sub->email, 'status' => ['processing', 'completed'], 'limit' => 1, 'orderby' => 'date', 'order' => 'DESC']);
        $last = $orders ? $orders[0] : null;
        if (!$last || $last->get_meta('_sp_is_test')) {
            continue;
        }
        $paid = $last->get_date_paid() ?: $last->get_date_created();
        $days = (int) floor(($now - $paid->getTimestamp()) / DAY_IN_SECONDS);
        $has_abo = function_exists('sp_em_order_has_abo') && sp_em_order_has_abo($last);
        $kind = function_exists('sp_em_order_kind') ? sp_em_order_kind($last) : 'normal';
        if ($kind === 'topup' || $has_abo) {
            continue;
        }
        $first_sent = $last->get_meta('_sp_nlm_reorder_sent');
        $reorder_days = sp_nlm_reorder_days($last);
        $winback_days = sp_nlm_winback_days($last);
        if ($first_sent && !$last->get_meta('_sp_nlm_reorder2_sent') && $days < $winback_days
            && (time() - strtotime($first_sent)) >= SP_NLM_REORDER2_AFTER * DAY_IN_SECONDS) {
            if (sp_nlm_dispatch($sub, 'sp_nachkauf_2', ['nachkauf_link' => sp_nlm_url('/', 'nachkauf-2', ['sp_nachkauf' => sp_nlm_reorder_token($last)]), 'nachkauf_artikel' => sp_nlm_items_text($last)] + sp_nlm_slots($last), 'Noch alles da?', function () use ($sub, $last) { return sp_nlm_reorder2_html($sub, $last); })) {
                $last->update_meta_data('_sp_nlm_reorder2_sent', current_time('mysql'));
                $last->save();
            }
            continue;
        }
        if ($reorder_days > 0 && $days >= $reorder_days && $days < $winback_days && !$first_sent) {
            if (sp_nlm_dispatch($sub, 'sp_nachkauf', ['nachkauf_link' => sp_nlm_url('/', 'nachkauf', ['sp_nachkauf' => sp_nlm_reorder_token($last)]), 'nachkauf_artikel' => sp_nlm_items_text($last)] + sp_nlm_slots($last), 'Zeit für Nachschub?', function () use ($sub, $last) { return sp_nlm_reorder_html($sub, $last); })) {
                $last->update_meta_data('_sp_nlm_reorder_sent', current_time('mysql'));
                $last->save();
            }
        } elseif ($days >= $winback_days && !$last->get_meta('_sp_nlm_winback_sent')) {
            $wb = sp_nlm_winback_coupon($sub);
            $wbc = new WC_Coupon($wb);
            if (sp_nlm_dispatch($sub, 'sp_rueckgewinnung', ['comeback_code' => $wb, 'comeback_bis' => $wbc->get_date_expires() ? $wbc->get_date_expires()->date_i18n('d.m.Y') : '', 'comeback_prozent' => SP_NLM_WINBACK_PERCENT . ' %'], 'Wir vermissen dich', function () use ($sub, $wb) { return sp_nlm_winback_html($sub, $wb); })) {
                $last->update_meta_data('_sp_nlm_winback_sent', current_time('mysql'));
                $last->save();
            }
        }
    }
});

/* Nachkauf-Zeitpunkt nach Verbrauch (2026-10-16): 1 Vial ~21 Tage, jede weitere Einheit +14, Pens ~28 Tage (+21 je weiterer),
 * Maximum 70 Tage; reine Zubehoer-Bestellungen (Kategorie 20) bekommen keine Nachkauf-Mail (0). Aktiv mit Option sp_nlm_verbrauch = 1. */
function sp_nlm_reorder_days($order) {
    if (!get_option('sp_nlm_verbrauch')) {
        return SP_NLM_REORDER_DAYS; // alte feste Regel, bis der Nutzer die neue freigibt
    }
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 729;
    $qty = [];
    foreach ($order->get_items() as $item) {
        $pid = (int) $item->get_product_id();
        if ($item->get_meta('Geschenk') || $pid === $topup || (float) $item->get_total() <= 0 || has_term(20, 'product_cat', $pid)) {
            continue;
        }
        $qty[$pid] = ($qty[$pid] ?? 0) + max(1, (int) $item->get_quantity()); // gleiche Produkte in mehreren Zeilen zusammenzaehlen
    }
    $days = 0;
    foreach ($qty as $pid => $n) {
        $days = max($days, in_array($pid, [393, 395, 396], true) ? 28 + 21 * ($n - 1) : 21 + 14 * ($n - 1));
    }
    return min(70, $days);
}

/* Rueckgewinnung fruehestens 3 Wochen nach dem erwarteten Nachkauf, mindestens SP_NLM_WINBACK_DAYS. */
function sp_nlm_winback_days($order) {
    if (!get_option('sp_nlm_verbrauch')) {
        return SP_NLM_WINBACK_DAYS;
    }
    return max(SP_NLM_WINBACK_DAYS, sp_nlm_reorder_days($order) + 21);
}

/* Ablaufdatum eines Gutscheins als d.m.Y. */
function sp_nlm_coupon_until($code) {
    $id = wc_get_coupon_id_by_code($code);
    $exp = $id ? (new WC_Coupon($id))->get_date_expires() : null;
    return $exp ? $exp->date_i18n('d.m.Y') : '';
}

/* ---------------------------------------------------------------------
 * (2026-10-16) Mail "Ist alles gut angekommen?" 5 Tage nach "Versendet"
 * an ALLE Kaeufer (Nutzer-Entscheidung), ueber Omnisend-Event sp_nach_lieferung.
 * Aus solange Option sp_nlm_delivered leer ist; nur Bestellungen, die nach dem
 * Einschalten (Option sp_nlm_delivered_since) versendet wurden.
 * ------------------------------------------------------------------- */
function sp_nlm_delivered_props($order) {
    $img = function ($p) {
        $iid = $p ? ($p->get_image_id() ?: (($pp = wc_get_product($p->get_parent_id())) ? $pp->get_image_id() : 0)) : 0;
        return $iid ? (string) wp_get_attachment_image_url($iid, 'woocommerce_thumbnail') : wc_placeholder_img_src();
    };
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    $slots = [];
    foreach ($order->get_items() as $item) {
        if (count($slots) >= 2 || $item->get_meta('Geschenk') || (int) $item->get_product_id() === $topup || (float) $item->get_total() <= 0) {
            continue;
        }
        $slots[] = [$img($item->get_product()), $item->get_name(), 'Menge: ' . (int) $item->get_quantity()];
    }
    $p = [
        'nl_nr' => (string) $order->get_order_number(),
        'nl_link' => sp_nlm_url('/', 'nach-lieferung', ['sp_nachkauf' => sp_nlm_reorder_token($order)]),
        'nl_show_2' => count($slots) > 1 ? 'table-cell' : 'none',
    ];
    for ($i = 0; $i < 2; $i++) {
        $p['nl_bild_' . ($i + 1)] = $slots[$i][0] ?? $slots[0][0] ?? '';
        $p['nl_name_' . ($i + 1)] = $slots[$i][1] ?? '';
        $p['nl_info_' . ($i + 1)] = $slots[$i][2] ?? '';
    }
    return $p;
}

add_action('sp_nlm_daily', function () {
    if (!get_option('sp_nlm_delivered') || !function_exists('sp_os_event')) {
        return;
    }
    $since = max(strtotime((string) get_option('sp_nlm_delivered_since', 'now')), time() - 10 * DAY_IN_SECONDS);
    $orders = wc_get_orders(['status' => 'completed', 'limit' => 200, 'date_completed' => $since . '...' . (time() - 5 * DAY_IN_SECONDS), 'type' => 'shop_order']);
    $n = 0;
    foreach ($orders as $o) {
        if ($n >= 60 || $o->get_meta('_sp_nlm_delivered_sent') || $o->get_meta('_sp_is_test')) {
            continue;
        }
        if (function_exists('sp_em_order_kind') && sp_em_order_kind($o) === 'topup') {
            continue;
        }
        $email = strtolower((string) $o->get_billing_email());
        if (!is_email($email)) {
            continue;
        }
        if (class_exists('\FluentCrm\App\Models\Subscriber')) {
            $fs = \FluentCrm\App\Models\Subscriber::where('email', $email)->first();
            if ($fs && in_array($fs->status, ['unsubscribed', 'bounced', 'complained'], true)) {
                $o->update_meta_data('_sp_nlm_delivered_sent', 'skip-abgemeldet');
                $o->save();
                continue;
            }
        }
        $r = sp_os_event($email, 'sp_nach_lieferung', sp_nlm_delivered_props($o), 'nonSubscribed');
        if ($r === true || $r === 'unsubscribed') {
            $o->update_meta_data('_sp_nlm_delivered_sent', $r === true ? current_time('mysql') : 'skip-abgemeldet');
            $o->save();
            $n++;
        } else {
            update_option('sp_os_last_error', current_time('mysql') . ' – ' . $email . ' (sp_nach_lieferung): ' . $r, false);
        }
    }
}, 20);
