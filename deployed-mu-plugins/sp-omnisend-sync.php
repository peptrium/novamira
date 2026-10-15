<?php
/**
 * Plugin Name: SP Omnisend – Newsletter-Abonnenten synchronisieren
 * Description: Uebertraegt bestaetigte Newsletter-Abonnenten (FluentCRM, Double-Opt-in) mit ihrem HALLO-Code an Omnisend.
 *
 * (2026-10-13) Versand der Newsletter laeuft ueber Omnisend (Mailgun-Konto gesperrt). Anmeldung, Double-Opt-in und
 * Gutschein bleiben in WordPress (sp-newsletter.php). Nach der Bestaetigung wird der Kontakt per Omnisend-API angelegt:
 * Status subscribed, Tags "newsletter" + "quelle-<footer|kasse|startseite>", Custom Properties hallo_code und
 * hallo_gueltig_bis (die Omnisend-Willkommensmail zeigt sie mit [[contact.custom_properties.hallo_code]]).
 * Abmeldungen in FluentCRM werden ebenfalls uebertragen.
 * API-Schluessel: Option sp_os_key (autoload aus), Eingabe unter Peptrium Dashboard → Omnisend.
 * Ohne Schluessel passiert nichts. Fehlgeschlagene Uebertragungen landen in Option sp_os_queue und werden stuendlich erneut versucht.
 * Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_os_key() {
    return trim((string) get_option('sp_os_key', ''));
}

/** Kontakt-Daten fuer Omnisend aus einem FluentCRM-Abonnenten. */
function sp_os_payload($sub, $status) {
    $when = $sub->updated_at ? gmdate('Y-m-d\TH:i:s\Z', strtotime(get_gmt_from_date((string) $sub->updated_at))) : gmdate('Y-m-d\TH:i:s\Z');
    $ident = [
        'type' => 'email',
        'id' => strtolower($sub->email),
        'channels' => ['email' => ['status' => $status, 'statusChangedAt' => $when]],
    ];
    if ($status === 'subscribed') {
        $ident['consent'] = array_filter([
            'source' => 'peptrium.com Newsletter (Double-Opt-in)',
            'createdAt' => $when,
            'ip' => (string) $sub->ip,
        ]);
    }
    $data = ['identifiers' => [$ident]];
    if ($status === 'subscribed') {
        $src = 'footer';
        foreach ($sub->tags as $t) {
            if (stripos($t->title, 'Kasse') !== false) {
                $src = 'kasse';
            } elseif (stripos($t->title, 'Startseite') !== false) {
                $src = 'startseite';
            } elseif (stripos($t->title, 'Bestellmail') !== false) {
                $src = 'bestellmail';
            }
        }
        $data['tags'] = ['newsletter', 'quelle-' . $src];
        $code = function_exists('sp_nl_ensure_coupon') ? sp_nl_ensure_coupon($sub) : '';
        if ($code) {
            $c = new WC_Coupon($code);
            $exp = $c->get_date_expires();
            $data['customProperties'] = [
                'hallo_code' => $code,
                'hallo_gueltig_bis' => $exp ? $exp->date_i18n('d.m.Y') : '',
            ];
        }
        if (trim((string) $sub->first_name) !== '') {
            $data['firstName'] = trim($sub->first_name);
        }
        if (trim((string) $sub->last_name) !== '') {
            $data['lastName'] = trim($sub->last_name);
        }
    }
    return $data;
}

/** Sendet an Omnisend. true bei Erfolg, sonst Fehlertext. */
function sp_os_send(array $data) {
    $key = sp_os_key();
    if ($key === '') {
        return 'kein API-Schluessel';
    }
    $res = wp_remote_post('https://api.omnisend.com/api/contacts', [
        'timeout' => 8,
        'headers' => [
            'Authorization' => 'Omnisend-API-Key ' . $key,
            'Omnisend-Version' => '2026-03-15',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'body' => wp_json_encode($data),
    ]);
    if (is_wp_error($res)) {
        return $res->get_error_message();
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    if ($code >= 200 && $code < 300) {
        return true;
    }
    return 'HTTP ' . $code . ': ' . substr((string) wp_remote_retrieve_body($res), 0, 300);
}

function sp_os_sync_subscriber($sub, $status) {
    if (!$sub || !$sub->email || sp_os_key() === '') {
        return;
    }
    if (!$sub->lists()->where('slug', defined('SP_NL_LIST_SLUG') ? SP_NL_LIST_SLUG : 'newsletter')->exists()) {
        return;
    }
    $r = sp_os_send(sp_os_payload($sub, $status));
    if ($r !== true) {
        $q = (array) get_option('sp_os_queue', []);
        $q[(int) $sub->id] = $status;
        update_option('sp_os_queue', $q, false);
        update_option('sp_os_last_error', current_time('mysql') . ' – ' . $sub->email . ': ' . $r, false);
    } else {
        update_option('sp_os_last_ok', current_time('mysql') . ' – ' . $sub->email . ' (' . $status . ')', false);
    }
}

/* Nach der Bestaetigung (prio 25: der Gutschein wird in sp-newsletter.php bei prio 20 erzeugt). */
add_action('fluentcrm_subscriber_status_to_subscribed', function ($sub) {
    sp_os_sync_subscriber($sub, 'subscribed');
}, 25, 1);
add_action('fluentcrm_subscriber_status_to_unsubscribed', function ($sub) {
    sp_os_sync_subscriber($sub, 'unsubscribed');
}, 25, 1);

/* Erneute Versuche fuer fehlgeschlagene Uebertragungen. */
add_action('sp_os_retry', function () {
    $q = (array) get_option('sp_os_queue', []);
    if (!$q || sp_os_key() === '' || !class_exists('\FluentCrm\App\Models\Subscriber')) {
        return;
    }
    update_option('sp_os_queue', [], false);
    foreach ($q as $id => $status) {
        $sub = \FluentCrm\App\Models\Subscriber::find((int) $id);
        if ($sub) {
            sp_os_sync_subscriber($sub, $sub->status === 'subscribed' ? 'subscribed' : 'unsubscribed');
        }
    }
});
add_action('init', function () {
    if (!wp_next_scheduled('sp_os_retry')) {
        wp_schedule_event(time() + 600, 'hourly', 'sp_os_retry');
    }
});

/**
 * Aktueller E-Mail-Status eines Kontakts in Omnisend ('subscribed', 'nonSubscribed', 'unsubscribed'),
 * '' wenn es den Kontakt nicht gibt, null bei Fehler.
 */
function sp_os_contact_status($email) {
    $key = sp_os_key();
    if ($key === '') {
        return null;
    }
    $res = wp_remote_get('https://api.omnisend.com/api/contacts?email=' . rawurlencode(strtolower($email)), [
        'timeout' => 8,
        'headers' => ['Authorization' => 'Omnisend-API-Key ' . $key, 'Omnisend-Version' => '2026-03-15', 'Accept' => 'application/json'],
    ]);
    if (is_wp_error($res) || (int) wp_remote_retrieve_response_code($res) !== 200) {
        return null;
    }
    $b = json_decode(wp_remote_retrieve_body($res), true);
    foreach ($b['contacts'][0]['identifiers'] ?? [] as $i) {
        if (($i['type'] ?? '') === 'email') {
            return (string) ($i['channels']['email']['status'] ?? 'nonSubscribed');
        }
    }
    return '';
}

/**
 * Custom Properties setzen und danach ein Event ausloesen. Der Anmeldestatus wird NIE geaendert:
 * bestehende Kontakte behalten ihren Status (sonst wuerde z.B. die Willkommensmail starten),
 * neue werden mit $new_status angelegt. Abgemeldete bekommen nichts (Rueckgabe 'unsubscribed').
 * Rueckgabe true oder Fehlertext.
 */
function sp_os_event($email, $event, array $props, $new_status = 'nonSubscribed') {
    $email = strtolower(trim($email));
    $st = sp_os_contact_status($email);
    if ($st === null) {
        return 'Status nicht abrufbar';
    }
    if ($st === 'unsubscribed') {
        return 'unsubscribed';
    }
    $r = sp_os_send(['identifiers' => [['type' => 'email', 'id' => $email, 'channels' => ['email' => ['status' => $st !== '' ? $st : $new_status]]]], 'customProperties' => $props]);
    if ($r !== true) {
        return $r;
    }
    $res = wp_remote_post('https://api.omnisend.com/api/events', [
        'timeout' => 8,
        'headers' => ['Authorization' => 'Omnisend-API-Key ' . sp_os_key(), 'Omnisend-Version' => '2026-03-15', 'Content-Type' => 'application/json'],
        'body' => wp_json_encode(['eventName' => $event, 'origin' => 'api', 'eventID' => wp_generate_uuid4(), 'eventTime' => gmdate('Y-m-d\TH:i:s\Z'), 'contact' => ['email' => $email], 'properties' => (object) []]),
    ]);
    if (is_wp_error($res)) {
        return $res->get_error_message();
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    return ($code >= 200 && $code < 300) ? true : 'Event HTTP ' . $code . ': ' . substr((string) wp_remote_retrieve_body($res), 0, 200);
}

/* Admin: Peptrium Dashboard → Omnisend */
add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Omnisend', 'Omnisend', 'manage_woocommerce', 'sp-omnisend', 'sp_os_render_admin');
}, 60);

function sp_os_render_admin() {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }
    $msg = '';
    if (!empty($_POST['sp_os_nonce']) && wp_verify_nonce($_POST['sp_os_nonce'], 'sp_os')) {
        if (isset($_POST['sp_os_key'])) {
            $k = sanitize_text_field(wp_unslash($_POST['sp_os_key']));
            if ($k !== '' && strpos($k, '•') === false) {
                update_option('sp_os_key', $k, false);
                $msg = 'API-Schlüssel gespeichert.';
            }
        }
        if (!empty($_POST['sp_os_sync_all']) && class_exists('\FluentCrm\App\Models\Subscriber')) {
            $n = 0;
            $subs = \FluentCrm\App\Models\Subscriber::where('status', 'subscribed')->get();
            foreach ($subs as $s) {
                sp_os_sync_subscriber($s, 'subscribed');
                $n++;
            }
            $msg = $n . ' Abonnenten an Omnisend übertragen (bestehende Kontakte werden nur aktualisiert, die Willkommensmail geht dabei nicht erneut raus).';
        }
    }
    $key = sp_os_key();
    echo '<div class="wrap"><h1>Omnisend</h1>';
    if ($msg) {
        echo '<div class="notice notice-success"><p>' . esc_html($msg) . '</p></div>';
    }
    echo '<p>Bestätigte Newsletter-Abonnenten werden automatisch mit ihrem Willkommens-Code an Omnisend übertragen. Omnisend verschickt dann die Willkommensmail.</p>';
    echo '<form method="post">' . wp_nonce_field('sp_os', 'sp_os_nonce', true, false);
    echo '<p><label><strong>API-Schlüssel</strong> (Omnisend → Store settings → Integrations &amp; API → API keys)<br>';
    echo '<input type="password" name="sp_os_key" style="width:420px" placeholder="' . ($key ? '•••••••• gespeichert' : 'API-Schlüssel einfügen') . '"></label></p>';
    echo '<p><button class="button button-primary">Speichern</button> ';
    echo '<button class="button" name="sp_os_sync_all" value="1">Alle bestätigten Abonnenten jetzt übertragen</button></p></form>';
    echo '<p>Status: ' . ($key ? 'Schlüssel hinterlegt' : '<strong>kein Schlüssel – es wird nichts übertragen</strong>') . '</p>';
    echo '<p>Letzte erfolgreiche Übertragung: ' . esc_html((string) get_option('sp_os_last_ok', '–')) . '<br>';
    echo 'Letzter Fehler: ' . esc_html((string) get_option('sp_os_last_error', '–')) . '<br>';
    echo 'Warteschlange: ' . count((array) get_option('sp_os_queue', [])) . '</p></div>';
}
