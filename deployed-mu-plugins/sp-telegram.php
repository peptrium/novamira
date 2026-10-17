<?php
/**
 * Plugin Name: SP Telegram
 * Description: Eigener Peptrium-Telegram-Bot (@Peptriumhilfebot) für die Gruppe "Peptrium Community"
 * (Supergruppe mit Themen) und den Kanal "Peptrium". Webhook auf diese Seite.
 * - Einrichtung (sp_tg_setup()): Beschreibung, Bild, Rechte, Themen, Regeln/Begrüßung angepinnt, Einladungslinks.
 * - Beitritt nur per Anfrage: Bot schickt die Regeln privat, Button "Regeln gelesen – beitreten" nimmt auf.
 * - Moderation: fremde Shops/Telegram-Links → sofort Bann; fremde Links, Weiterleitungen aus Kanälen,
 *   Dosierungs-/Anwendungsfragen → löschen + Verwarnung (3. = Bann); neue Mitglieder 24 h ohne Links/Medien;
 *   in "Start & Regeln" und "News" schreiben nur Admins.
 * Token liegt in Option sp_tg_token (autoload aus) und wird nie ausgegeben.
 */

if (!defined('ABSPATH')) exit;

define('SP_TG_SUPPORT', 'peptrium'); // Support-Account @peptrium

function sp_tg_token() { return (string) get_option('sp_tg_token', ''); }

/** Bot-API-Aufruf. Liefert das decodierte Ergebnis oder ['ok'=>false,'description'=>…]. */
function sp_tg_api($method, $params = []) {
    $t = sp_tg_token();
    if ($t === '') return ['ok' => false, 'description' => 'kein Token'];
    $r = wp_remote_post('https://api.telegram.org/bot' . $t . '/' . $method, [
        'timeout' => 15,
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => wp_json_encode($params),
    ]);
    if (is_wp_error($r)) return ['ok' => false, 'description' => $r->get_error_message()];
    $j = json_decode(wp_remote_retrieve_body($r), true);
    return is_array($j) ? $j : ['ok' => false, 'description' => 'HTTP ' . wp_remote_retrieve_response_code($r)];
}

/** Bot-API-Aufruf mit Datei-Upload (multipart). */
function sp_tg_api_file($method, $params, $field, $path) {
    $t = sp_tg_token();
    if ($t === '' || !file_exists($path) || !function_exists('curl_init')) return ['ok' => false, 'description' => 'kein Token/Datei'];
    $params[$field] = new CURLFile($path);
    $ch = curl_init('https://api.telegram.org/bot' . $t . '/' . $method);
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $params, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30]);
    $b = curl_exec($ch);
    curl_close($ch);
    $j = json_decode((string) $b, true);
    return is_array($j) ? $j : ['ok' => false, 'description' => 'Upload fehlgeschlagen'];
}

function sp_tg_log($entry) {
    $log = (array) get_option('sp_tg_log', []);
    array_unshift($log, ['t' => time()] + $entry);
    update_option('sp_tg_log', array_slice($log, 0, 30), false);
}

/** Moderations-Protokoll (fürs Dashboard). */
function sp_tg_modlog($action, $user, $why, $text = '') {
    $log = (array) get_option('sp_tg_modlog', []);
    array_unshift($log, ['t' => time(), 'a' => $action, 'u' => $user, 'why' => $why, 'txt' => mb_substr((string) $text, 0, 140)]);
    update_option('sp_tg_modlog', array_slice($log, 0, 100), false);
}

/** IDs von Gruppe/Kanal (aus den my_chat_member-Updates). */
function sp_tg_group_id() {
    foreach ((array) get_option('sp_tg_chats', []) as $id => $c) if ($c['type'] === 'supergroup') return (int) $id;
    return 0;
}
function sp_tg_channel_id() {
    foreach ((array) get_option('sp_tg_chats', []) as $id => $c) if ($c['type'] === 'channel') return (int) $id;
    return 0;
}

/** Link auf ein Thema in der (privaten) Gruppe. */
function sp_tg_topic_link($key) {
    $topics = (array) get_option('sp_tg_topics', []);
    $gid = sp_tg_group_id();
    if (!$gid || empty($topics[$key])) return '';
    return 'https://t.me/c/' . substr((string) $gid, 4) . '/' . (int) $topics[$key];
}

/* ---------- Texte ---------- */

function sp_tg_topic_defs() {
    // key => [Name, Icon-Emoji-ID]; 'allgemein' = umbenanntes General-Thema
    return [
        'start'    => ['Start & Regeln', '5379748062124056162'],
        'news'     => ['News', '5309984423003823246'],
        'wissen'   => ['Peptid-Wissen', '5350481781306958339'],
        'lagerung' => ['Lagerung & Handling', '5377580546748588396'],
        'wunsch'   => ['Wunschliste', '5411138633765757782'],
        'bestell'  => ['Bestellung & Versand', '5431492767249342908'],
        'feedback' => ['Erfahrungen & Feedback', '5235579393115438657'],
    ];
}

function sp_tg_rules_html() {
    return "<b>📌 Regeln der Peptrium Community</b>\n\n"
        . "Willkommen! Hier tauschen sich Forschende rund um Peptide aus. Damit das so bleibt:\n\n"
        . "<b>🚫 Sofortiger Bann – ohne Verwarnung</b>\n"
        . "• Werbung oder Empfehlung für andere Shops, Händler oder Quellen (Namen, Links, Codes, „schreib mir privat“)\n"
        . "• Mitglieder privat anschreiben, um etwas zu verkaufen\n"
        . "• Sich als Peptrium-Team ausgeben\n"
        . "• Spam, Bots, Betrug\n\n"
        . "<b>⚠️ Löschen + Verwarnung (beim 3. Mal Bann)</b>\n"
        . "• Fragen oder Tipps zu Dosierung oder Anwendung am Menschen, Einnahme-Erfahrungen, Gesundheitsversprechen – unsere Produkte sind <b>ausschließlich für Laborforschungszwecke</b>\n"
        . "• Beleidigungen, Streit, Politik\n"
        . "• Fremde Links und weitergeleitete Beiträge anderer Kanäle\n"
        . "• Bestelldaten öffentlich posten (Bestellnummer, Adresse, Zahlung) – dafür bitte privat an @" . SP_TG_SUPPORT . "\n\n"
        . "<b>🛡 Wichtig</b>\n"
        . "Wir schreiben dich <b>niemals zuerst privat an</b> und fragen nie per Telegram nach Zahlungen. Wer das tut, ist ein Betrüger – bitte melde ihn einem Admin.";
}

function sp_tg_welcome_html() {
    $l = function ($k, $label) { $u = sp_tg_topic_link($k); return $u ? '<a href="' . esc_url($u) . '">' . $label . '</a>' : $label; };
    $site = 'https://peptrium.com';
    $utm = '?utm_source=telegram&utm_medium=community&utm_campaign=gruppe';
    return "<b>Willkommen bei Peptrium 👋</b>\n\n"
        . "So ist die Gruppe aufgebaut:\n\n"
        . "💬 " . $l('allgemein', '<b>Allgemein</b>') . " – Austausch &amp; Vorstellen\n"
        . "📚 " . $l('wissen', '<b>Peptid-Wissen</b>') . " – Fragen zu Peptiden &amp; Forschung\n"
        . "🔬 " . $l('lagerung', '<b>Lagerung &amp; Handling</b>') . " – Lagerung, Rekonstitution im Labor\n"
        . "🧪 " . $l('wunsch', '<b>Wunschliste</b>') . " – welche Peptide sollen wir aufnehmen?\n"
        . "🛒 " . $l('bestell', '<b>Bestellung &amp; Versand</b>') . " – allgemeine Fragen\n"
        . "⭐️ " . $l('feedback', '<b>Erfahrungen &amp; Feedback</b>') . " – Lob, Kritik, Ideen\n"
        . "📣 " . $l('news', '<b>News</b>') . " – Neuheiten &amp; Aktionen\n\n"
        . "<b>Nützliche Links</b>\n"
        . "🛍 <a href=\"$site/alle-produkte/$utm\">Shop</a> · 📖 <a href=\"$site/peptid-lexikon/$utm\">Peptid-Lexikon</a> · 🧮 <a href=\"$site/peptid-rechner/$utm\">Peptid-Rechner</a>\n"
        . "❄️ <a href=\"$site/lagerungs-guide/$utm\">Lagerungs-Guide</a> · 📝 <a href=\"$site/ratgeber/$utm\">Ratgeber</a>\n\n"
        . "Fragen zu deiner Bestellung? Schreib direkt an @" . SP_TG_SUPPORT . ".";
}

function sp_tg_topic_intro($key) {
    $site = 'https://peptrium.com';
    $utm = '?utm_source=telegram&utm_medium=community&utm_campaign=gruppe';
    switch ($key) {
        case 'wissen':
            return "<b>📚 Peptid-Wissen</b>\n\nHier geht es um Peptide und Forschung: Was ist was, was wird untersucht, wo liegen Unterschiede?\n\n"
                . "Viele Antworten findest du auch im <a href=\"$site/peptid-lexikon/$utm\">Peptid-Lexikon</a> und im <a href=\"$site/ratgeber/$utm\">Ratgeber</a>.\n\n"
                . "<i>Hinweis: Fragen zu Dosierung oder Anwendung am Menschen werden gelöscht – unsere Produkte sind ausschließlich für Laborforschungszwecke.</i>";
        case 'lagerung':
            return "<b>🔬 Lagerung &amp; Handling</b>\n\nLagerung, Haltbarkeit und Rekonstitution im Labor.\n\n"
                . "❄️ <a href=\"$site/lagerungs-guide/$utm\">Lagerungs-Guide</a>\n🧮 <a href=\"$site/peptid-rechner/$utm\">Peptid-Rechner</a>\n📝 <a href=\"$site/ratgeber/peptide-rekonstituieren/$utm\">Peptide rekonstituieren (Ratgeber)</a>";
        case 'wunsch':
            return "<b>🧪 Wunschliste</b>\n\nWelches Peptid fehlt dir bei uns? Schreib es hier rein – gern mit kurzem Grund.\n\nWir schauen regelmäßig rein und machen ab und zu eine Abstimmung.";
        case 'bestell':
            return "<b>🛒 Bestellung &amp; Versand</b>\n\nAllgemeine Fragen zu Bestellung, Zahlung und Versand.\n\n"
                . "📦 <a href=\"$site/versand-lieferzeit/$utm\">Versand &amp; Lieferzeit</a>\n🔎 <a href=\"$site/bestellstatus/$utm\">Bestellstatus prüfen</a>\n\n"
                . "⚠️ Bitte <b>keine Bestellnummer, Adresse oder Zahlungsdaten</b> hier posten – dafür schreib privat an @" . SP_TG_SUPPORT . ".";
        case 'feedback':
            return "<b>⭐️ Erfahrungen &amp; Feedback</b>\n\nWie war deine Bestellung? Was können wir besser machen? Lob, Kritik und Ideen – alles gern hier.\n\n<i>Bitte keine Erfahrungen zur Einnahme oder Anwendung am Menschen.</i>";
        case 'news':
            return "<b>📣 News</b>\n\nHier erscheinen Neuheiten, „wieder da“ und Aktionen. Nur Admins posten hier – stell die Glocke an, um nichts zu verpassen 🔔";
        case 'allgemein':
            return "<b>💬 Allgemein</b>\n\nHier ist Platz für alles rund um Peptrium und Peptid-Forschung. Stell dich gern kurz vor 👋";
    }
    return '';
}

/* ---------- Einrichtung ---------- */

/**
 * Richtet Gruppe + Kanal ein. Idempotent: bereits angelegte Themen/Posts werden nicht doppelt erstellt.
 * Rückgabe: Protokoll-Zeilen.
 */
function sp_tg_setup() {
    $out = [];
    $gid = sp_tg_group_id();
    $cid = sp_tg_channel_id();
    if (!$gid) return ['Keine Gruppe gefunden'];
    $avatar = WP_CONTENT_DIR . '/uploads/sp-email/tg-avatar.jpg';

    $base = !get_option('sp_tg_base_done'); // Beschreibung/Bild/Rechte nur beim ersten Lauf (Flood-Limit)
    // Gruppe: Beschreibung, Bild, Rechte
    if ($base) {
    $r = sp_tg_api('setChatDescription', ['chat_id' => $gid, 'description' => "Die Community von Peptrium – Austausch & Wissen rund um Forschungspeptide.\nRegeln: Thema „Start & Regeln“ · Shop: peptrium.com\nNur für Laborforschungszwecke."]);
    $out[] = 'Gruppe Beschreibung: ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    if (file_exists($avatar)) {
        $r = sp_tg_api_file('setChatPhoto', ['chat_id' => $gid], 'photo', $avatar);
        $out[] = 'Gruppe Bild: ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    }
    $r = sp_tg_api('setChatPermissions', ['chat_id' => $gid, 'use_independent_chat_permissions' => true, 'permissions' => [
        'can_send_messages' => true, 'can_send_photos' => true, 'can_send_videos' => true, 'can_send_video_notes' => false,
        'can_send_audios' => false, 'can_send_voice_notes' => false, 'can_send_documents' => false, 'can_send_polls' => false,
        'can_send_other_messages' => true, 'can_add_web_page_previews' => false, 'can_change_info' => false,
        'can_invite_users' => false, 'can_pin_messages' => false, 'can_manage_topics' => false,
    ]]);
    $out[] = 'Gruppe Rechte: ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    }

    // Themen
    $topics = (array) get_option('sp_tg_topics', []);
    $r = sp_tg_api('editGeneralForumTopic', ['chat_id' => $gid, 'name' => 'Allgemein']);
    $topics['allgemein'] = 1; // General-Thema hat immer die ID 1
    $out[] = 'Thema Allgemein: ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    // Reihenfolge: zuletzt aktive Themen stehen oben → "Start & Regeln" zuletzt befüllen
    $order = ['feedback', 'bestell', 'wunsch', 'lagerung', 'wissen', 'news', 'start'];
    $defs = sp_tg_topic_defs();
    foreach ($order as $k) {
        if (!empty($topics[$k])) continue;
        $r = sp_tg_api('createForumTopic', ['chat_id' => $gid, 'name' => $defs[$k][0], 'icon_custom_emoji_id' => $defs[$k][1]]);
        if (!empty($r['ok'])) { $topics[$k] = (int) $r['result']['message_thread_id']; $out[] = 'Thema ' . $defs[$k][0] . ': angelegt'; }
        else $out[] = 'Thema ' . $defs[$k][0] . ': ' . ($r['description'] ?? '?');
    }
    update_option('sp_tg_topics', $topics, false);

    // Intro-Posts je Thema (einmalig), zuletzt Begrüßung + Regeln in "Start & Regeln" (angepinnt)
    $posted = (array) get_option('sp_tg_posted', []);
    foreach (['allgemein', 'feedback', 'bestell', 'wunsch', 'lagerung', 'wissen', 'news'] as $k) {
        if (!empty($posted[$k]) || empty($topics[$k])) continue;
        $p = ['chat_id' => $gid, 'text' => sp_tg_topic_intro($k), 'parse_mode' => 'HTML', 'link_preview_options' => ['is_disabled' => true]];
        if ($k !== 'allgemein') $p['message_thread_id'] = $topics[$k];
        $r = sp_tg_api('sendMessage', $p);
        if (!empty($r['ok'])) {
            $posted[$k] = $r['result']['message_id'];
            sp_tg_api('pinChatMessage', ['chat_id' => $gid, 'message_id' => $posted[$k], 'disable_notification' => true]);
        }
        $out[] = 'Intro ' . $k . ': ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    }
    foreach (['welcome' => sp_tg_welcome_html(), 'rules' => sp_tg_rules_html()] as $k => $txt) {
        if (!empty($posted[$k]) || empty($topics['start'])) continue;
        $r = sp_tg_api('sendMessage', ['chat_id' => $gid, 'message_thread_id' => $topics['start'], 'text' => $txt, 'parse_mode' => 'HTML', 'link_preview_options' => ['is_disabled' => true]]);
        if (!empty($r['ok'])) {
            $posted[$k] = $r['result']['message_id'];
            sp_tg_api('pinChatMessage', ['chat_id' => $gid, 'message_id' => $posted[$k], 'disable_notification' => true]);
        }
        $out[] = 'Post ' . $k . ': ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    }
    update_option('sp_tg_posted', $posted, false);

    // Kanal: Beschreibung, Bild
    if ($cid && $base) {
        $r = sp_tg_api('setChatDescription', ['chat_id' => $cid, 'description' => "Neuheiten, Wieder-da & Aktionen von Peptrium.\nShop: peptrium.com · Nur für Laborforschungszwecke."]);
        $out[] = 'Kanal Beschreibung: ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
        if (file_exists($avatar)) {
            $r = sp_tg_api_file('setChatPhoto', ['chat_id' => $cid], 'photo', $avatar);
            $out[] = 'Kanal Bild: ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
        }
    }

    // Einladungslinks: Gruppe nur per Beitrittsanfrage (Bot prüft), je Quelle ein Link zum Mitzählen
    $links = (array) get_option('sp_tg_links', []);
    foreach (['website' => 'Website', 'tiktok' => 'TikTok', 'mail' => 'E-Mails', 'paket' => 'Paketkarte', 'direkt' => 'Direkt'] as $k => $name) {
        if (!empty($links['g_' . $k])) continue;
        $r = sp_tg_api('createChatInviteLink', ['chat_id' => $gid, 'name' => $name, 'creates_join_request' => true]);
        if (!empty($r['ok'])) $links['g_' . $k] = $r['result']['invite_link'];
        $out[] = 'Link Gruppe ' . $name . ': ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    }
    if ($cid && empty($links['c_main'])) {
        $r = sp_tg_api('createChatInviteLink', ['chat_id' => $cid, 'name' => 'Peptrium Kanal']);
        if (!empty($r['ok'])) $links['c_main'] = $r['result']['invite_link'];
        $out[] = 'Link Kanal: ' . (!empty($r['ok']) ? 'ok' : ($r['description'] ?? '?'));
    }
    update_option('sp_tg_links', $links, false);
    update_option('sp_tg_base_done', 1, false);

    // Bot-Befehle + Kurzbeschreibung im Bot-Profil
    sp_tg_api('setMyCommands', ['commands' => [
        ['command' => 'start', 'description' => 'Info & Links'],
        ['command' => 'regeln', 'description' => 'Regeln der Community'],
        ['command' => 'support', 'description' => 'Hilfe zu deiner Bestellung'],
    ]]);
    sp_tg_api('setMyShortDescription', ['short_description' => 'Assistent der Peptrium Community – Infos, Links & Hilfe. Nur für Laborforschungszwecke.']);
    return $out;
}

/* ---------- Webhook ---------- */
add_action('rest_api_init', function () {
    register_rest_route('sp-tg/v1', '/hook', [
        'methods'             => 'POST',
        'permission_callback' => function (WP_REST_Request $req) {
            $s = (string) get_option('sp_tg_secret', '');
            return $s !== '' && hash_equals($s, (string) $req->get_header('x_telegram_bot_api_secret_token'));
        },
        'callback'            => function (WP_REST_Request $req) {
            $u = $req->get_json_params();
            if (is_array($u)) {
                try { sp_tg_handle_update($u); } catch (Throwable $e) { sp_tg_log(['ev' => 'error', 'msg' => $e->getMessage()]); }
            }
            return ['ok' => true];
        },
    ]);
});

function sp_tg_handle_update($u) {
    // Bot wurde in eine Gruppe/einen Kanal aufgenommen oder seine Rechte haben sich geändert
    if (!empty($u['my_chat_member'])) {
        $m = $u['my_chat_member'];
        $chat = $m['chat'];
        $st = $m['new_chat_member']['status'] ?? '';
        $chats = (array) get_option('sp_tg_chats', []);
        if (in_array($st, ['administrator', 'member'], true)) {
            $chats[(string) $chat['id']] = [
                'type'  => $chat['type'],
                'title' => $chat['title'] ?? '',
                'forum' => !empty($chat['is_forum']),
                'admin' => $st === 'administrator',
                'by'    => (int) ($m['from']['id'] ?? 0),
                't'     => time(),
            ];
        } else {
            unset($chats[(string) $chat['id']]);
        }
        update_option('sp_tg_chats', $chats, false);
        sp_tg_log(['ev' => 'chat', 'chat' => $chat['title'] ?? $chat['id'], 'status' => $st]);
        return;
    }
    if (!empty($u['chat_join_request'])) { sp_tg_on_join_request($u['chat_join_request']); return; }
    if (!empty($u['callback_query']))    { sp_tg_on_callback($u['callback_query']); return; }
    if (!empty($u['chat_member']))       { sp_tg_on_chat_member($u['chat_member']); return; }

    $msg = $u['message'] ?? $u['edited_message'] ?? null;
    if ($msg) {
        $type = $msg['chat']['type'] ?? '';
        if ($type === 'private') { sp_tg_on_private($msg); return; }
        if ((int) $msg['chat']['id'] === sp_tg_group_id()) { sp_tg_moderate($msg); return; }
    }
    do_action('sp_tg_update', $u);
}

/* ---------- Beitritt ---------- */

function sp_tg_on_join_request($jr) {
    $gid = sp_tg_group_id();
    if ((int) $jr['chat']['id'] !== $gid) return;
    $uid = (int) $jr['from']['id'];
    $src = $jr['invite_link']['name'] ?? 'Link';
    $req = (array) get_option('sp_tg_joinreq', []);
    $req[$uid] = ['t' => time(), 'src' => $src];
    update_option('sp_tg_joinreq', array_slice($req, -500, null, true), false);
    $name = esc_html($jr['from']['first_name'] ?? '');
    $txt = "Hey $name 👋 schön, dass du der <b>Peptrium Community</b> beitreten willst!\n\n"
        . "Kurz das Wichtigste:\n"
        . "🚫 Keine Werbung für andere Shops – sofortiger Bann\n"
        . "⚠️ Keine Fragen zu Dosierung oder Anwendung am Menschen – unsere Produkte sind nur für Laborforschungszwecke\n"
        . "🔒 Bestelldaten nur privat an @" . SP_TG_SUPPORT . "\n"
        . "🛡 Wir schreiben dich nie zuerst privat an\n\n"
        . "Wenn das für dich passt, tippe unten auf den Button.";
    $r = sp_tg_api('sendMessage', [
        'chat_id' => (int) ($jr['user_chat_id'] ?? $uid), 'text' => $txt, 'parse_mode' => 'HTML',
        'reply_markup' => ['inline_keyboard' => [[['text' => '✅ Regeln gelesen – beitreten', 'callback_data' => 'join']]]],
    ]);
    sp_tg_log(['ev' => 'joinreq', 'src' => $src, 'dm' => !empty($r['ok']) ? 'ok' : ($r['description'] ?? '?')]);
}

function sp_tg_on_callback($cq) {
    $uid = (int) $cq['from']['id'];
    if (($cq['data'] ?? '') === 'join') {
        $gid = sp_tg_group_id();
        $r = sp_tg_api('approveChatJoinRequest', ['chat_id' => $gid, 'user_id' => $uid]);
        $ok = !empty($r['ok']) || stripos((string) ($r['description'] ?? ''), 'USER_ALREADY_PARTICIPANT') !== false;
        sp_tg_api('answerCallbackQuery', ['callback_query_id' => $cq['id'], 'text' => $ok ? 'Willkommen! 🎉' : 'Anfrage nicht mehr gültig – bitte über den Link neu beitreten.']);
        if ($ok) {
            sp_tg_mark_joined($uid);
            $start = sp_tg_topic_link('start');
            sp_tg_api('editMessageText', [
                'chat_id' => $cq['message']['chat']['id'], 'message_id' => $cq['message']['message_id'], 'parse_mode' => 'HTML',
                'text' => "Du bist drin 🎉\n\nSchau zuerst ins Thema <b>Start &amp; Regeln</b>" . ($start ? " (<a href=\"" . esc_url($start) . "\">öffnen</a>)" : '') . " – dort findest du alle Themen und Links.\n\nFragen zu deiner Bestellung? Schreib an @" . SP_TG_SUPPORT . ".",
                'link_preview_options' => ['is_disabled' => true],
            ]);
            $req = (array) get_option('sp_tg_joinreq', []);
            $src = $req[$uid]['src'] ?? 'Link';
            $stats = (array) get_option('sp_tg_joinstats', []);
            $stats[$src] = (int) ($stats[$src] ?? 0) + 1;
            update_option('sp_tg_joinstats', $stats, false);
        }
        return;
    }
    sp_tg_api('answerCallbackQuery', ['callback_query_id' => $cq['id']]);
}

function sp_tg_mark_joined($uid) {
    $j = (array) get_option('sp_tg_joined', []);
    $j[(int) $uid] = time();
    // nur die letzten 7 Tage behalten (für die 24-h-Regel reicht das)
    $j = array_filter($j, function ($t) { return $t > time() - 7 * DAY_IN_SECONDS; });
    update_option('sp_tg_joined', $j, false);
}

function sp_tg_on_chat_member($cm) {
    if ((int) $cm['chat']['id'] !== sp_tg_group_id()) return;
    $old = $cm['old_chat_member']['status'] ?? '';
    $new = $cm['new_chat_member']['status'] ?? '';
    if (in_array($old, ['left', 'kicked'], true) && $new === 'member') sp_tg_mark_joined($cm['new_chat_member']['user']['id']);
}

/* ---------- Private Nachrichten an den Bot ---------- */

function sp_tg_on_private($msg) {
    $chat = (int) $msg['chat']['id'];
    $t = trim((string) ($msg['text'] ?? ''));
    $links = (array) get_option('sp_tg_links', []);
    if (stripos($t, '/regeln') === 0) {
        sp_tg_api('sendMessage', ['chat_id' => $chat, 'text' => sp_tg_rules_html(), 'parse_mode' => 'HTML']);
        return;
    }
    if (stripos($t, '/support') === 0) {
        sp_tg_api('sendMessage', ['chat_id' => $chat, 'parse_mode' => 'HTML', 'text' => "Für Fragen zu deiner Bestellung schreib bitte direkt an @" . SP_TG_SUPPORT . " – mit deiner Bestellnummer.\n\nDen Status kannst du auch selbst prüfen: https://peptrium.com/bestellstatus/", 'link_preview_options' => ['is_disabled' => true]]);
        return;
    }
    if ($t !== '' && stripos($t, '/start') !== 0) {
        $key = sp_tg_faq_find($t);
        if ($key) { sp_tg_faq_send($chat, $key); return; }
        sp_tg_api('sendMessage', ['chat_id' => $chat, 'parse_mode' => 'HTML', 'text' => sp_tg_faq_nomatch_html()]);
        return;
    }
    $kb = [[['text' => '🛍 Zum Shop', 'url' => 'https://peptrium.com/?utm_source=telegram&utm_medium=bot&utm_campaign=start']]];
    if (!empty($links['g_direkt'])) $kb[] = [['text' => '💬 Community beitreten', 'url' => $links['g_direkt']]];
    if (!empty($links['c_main']))   $kb[] = [['text' => '📣 Kanal: Neuheiten & Aktionen', 'url' => $links['c_main']]];
    $kb[] = [['text' => '🙋 Support: @' . SP_TG_SUPPORT, 'url' => 'https://t.me/' . SP_TG_SUPPORT]];
    sp_tg_api('sendMessage', ['chat_id' => $chat, 'parse_mode' => 'HTML', 'reply_markup' => ['inline_keyboard' => $kb],
        'text' => "Hi 👋 ich bin der <b>Peptrium Assistent</b>.\n\nHier findest du den Shop, unsere Community und den Kanal mit Neuheiten. Für Fragen zu deiner Bestellung schreib an @" . SP_TG_SUPPORT . ".\n\n<i>Alle Produkte ausschließlich für Laborforschungszwecke.</i>"]);
}

/* ---------- Moderation ---------- */

function sp_tg_is_admin($gid, $uid) {
    $key = 'sp_tg_admins_' . md5((string) $gid);
    $ids = get_transient($key);
    if (!is_array($ids)) {
        $r = sp_tg_api('getChatAdministrators', ['chat_id' => $gid]);
        $ids = [];
        foreach (($r['result'] ?? []) as $a) $ids[] = (int) $a['user']['id'];
        set_transient($key, $ids, 10 * MINUTE_IN_SECONDS);
    }
    return in_array((int) $uid, $ids, true);
}

/** Domains, die in der Gruppe erlaubt sind (eigene + Fachquellen). */
function sp_tg_allowed_domains() {
    return ['peptrium.com', 'pubmed.ncbi.nlm.nih.gov', 'ncbi.nlm.nih.gov', 'wikipedia.org', 'doi.org', 'sciencedirect.com', 'nature.com', 'nih.gov', 'clinicaltrials.gov'];
}

/** Zusätzliche Bann-Wörter (Namen anderer Shops), im Dashboard pflegbar. */
function sp_tg_ban_words() {
    $w = (string) get_option('sp_tg_ban_words', '');
    return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', mb_strtolower($w)))));
}

function sp_tg_extract_urls($msg) {
    $text = (string) ($msg['text'] ?? $msg['caption'] ?? '');
    $ents = $msg['entities'] ?? $msg['caption_entities'] ?? [];
    $urls = [];
    foreach ($ents as $e) {
        if ($e['type'] === 'text_link' && !empty($e['url'])) $urls[] = $e['url'];
        if ($e['type'] === 'url') $urls[] = mb_substr($text, $e['offset'], $e['length']); // UTF-16-Offsets: für ASCII-URLs ausreichend
    }
    if (preg_match_all('~(?:https?://|www\.|t\.me/|telegram\.me/)\S+~i', $text, $m)) $urls = array_merge($urls, $m[0]);
    return array_unique($urls);
}

function sp_tg_host($url) {
    if (!preg_match('~^[a-z]+://~i', $url)) $url = 'http://' . $url;
    return strtolower((string) parse_url($url, PHP_URL_HOST));
}

/** Prüft eine Nachricht. Rückgabe: ['ban'|'warn'|'delete'|'', Grund]. */
function sp_tg_check($msg, $is_new) {
    $text = (string) ($msg['text'] ?? $msg['caption'] ?? '');
    $lt = mb_strtolower($text);
    $urls = sp_tg_extract_urls($msg);

    // Fremde Telegram-/Messenger-Links, Shop-Namen → sofort Bann
    foreach ($urls as $u) {
        $h = sp_tg_host($u);
        if (preg_match('~(^|\.)(t\.me|telegram\.me|telegram\.dog|wa\.me|chat\.whatsapp\.com|signal\.me|discord\.gg|discord\.com)$~', $h)) {
            if (!preg_match('~t\.me/(' . preg_quote(SP_TG_SUPPORT, '~') . '|peptriumhilfebot)\b~i', $u) && !preg_match('~t\.me/c/~i', $u)) return ['ban', 'Fremder Telegram-/Messenger-Link'];
        }
        if (strpos($h, 'pept') !== false && !preg_match('~(^|\.)peptrium\.com$~', $h)) return ['ban', 'Link zu anderem Peptid-Shop'];
    }
    foreach (sp_tg_ban_words() as $w) {
        if ($w !== '' && mb_strpos($lt, $w) !== false) return ['ban', 'Anderer Shop genannt (' . $w . ')'];
    }
    if (preg_match('~(schreib|schreibt|meld|melde)\w*\s+(mir|mich)\s+(privat|per\s*dm|pn)|\bdm\s+(me|mir)\b|\bpn\s+an\s+mich\b|bei\s+mir\s+(günstiger|billiger)|ich\s+(verkaufe|verkauf)\b~u', $lt)) {
        return ['ban', 'Privater Verkauf / Abwerben'];
    }
    if (preg_match('~@\w*(shop|store|pept(?!rium))\w*~i', $text)) return ['ban', 'Fremder Shop-Account erwähnt'];

    // Weiterleitungen aus fremden Kanälen
    if (!empty($msg['forward_origin']) && in_array($msg['forward_origin']['type'] ?? '', ['channel', 'chat'], true)) return ['warn', 'Weitergeleiteter Beitrag'];

    // Fremde Links
    foreach ($urls as $u) {
        $h = sp_tg_host($u);
        $ok = (bool) preg_match('~t\.me/(' . preg_quote(SP_TG_SUPPORT, '~') . '|peptriumhilfebot|c/)~i', $u); // eigene Telegram-Links

        foreach (sp_tg_allowed_domains() as $d) if ($h === $d || substr($h, -strlen('.' . $d)) === '.' . $d) $ok = true;
        if (!$ok) return ['warn', 'Fremder Link'];
    }

    // Dosierung / Anwendung am Menschen
    if (preg_match('~dosier|dosis|dosen\b|injizier|injekt|subkutan|\bsc\b\s*spritz|spritz(e|en)?\s+(ich|du|man|mir|dir|sich)|(ich|du|man)\s+spritz|wie\s*viel\w*\s+(nehm|spritz|einheit)|wieviel\w*\s+(nehm|spritz|einheit)|\bnehme\s+(ich|seit|gerade)\b|einnahme|\d+\s?(mg|mcg|µg|ie|einheiten|units)\s*(pro|am|/|die|jede|jeden|in\s+der)\s*(tag|woche|dosis|morgen|abend)~u', $lt)) {
        return ['warn', 'Dosierung/Anwendung'];
    }

    // Neue Mitglieder: 24 h ohne Medien
    if ($is_new && (isset($msg['photo']) || isset($msg['video']) || isset($msg['document']) || isset($msg['animation']) || isset($msg['sticker']))) return ['delete', 'Neues Mitglied: noch keine Medien'];
    return ['', ''];
}

function sp_tg_moderate($msg) {
    $gid = (int) $msg['chat']['id'];
    // Service-Nachrichten (beigetreten/verlassen) aufräumen
    if (!empty($msg['new_chat_members']) || !empty($msg['left_chat_member'])) {
        foreach ((array) ($msg['new_chat_members'] ?? []) as $nm) sp_tg_mark_joined($nm['id']);
        sp_tg_api('deleteMessage', ['chat_id' => $gid, 'message_id' => $msg['message_id']]);
        return;
    }
    // Admins, anonyme Admins (sender_chat = Gruppe), automatische Weiterleitungen aus dem Kanal
    if (!empty($msg['is_automatic_forward'])) return;
    if (!empty($msg['sender_chat']) && (int) $msg['sender_chat']['id'] === $gid) { sp_tg_maybe_faq($msg); return; } // anonymer Admin
    if (!empty($msg['sender_chat']) && (int) $msg['sender_chat']['id'] === sp_tg_channel_id()) return;
    $uid = (int) ($msg['from']['id'] ?? 0);
    if (!$uid || !empty($msg['from']['is_bot']) && $uid === (int) (get_option('sp_tg_bot')['id'] ?? 0)) return;
    if (sp_tg_is_admin($gid, $uid)) { sp_tg_maybe_faq($msg); return; }
    if (!empty($msg['sender_chat'])) { // als fremder Kanal geschrieben
        sp_tg_api('deleteMessage', ['chat_id' => $gid, 'message_id' => $msg['message_id']]);
        sp_tg_api('banChatSenderChat', ['chat_id' => $gid, 'sender_chat_id' => $msg['sender_chat']['id']]);
        sp_tg_modlog('ban', $msg['sender_chat']['title'] ?? 'Kanal', 'Als Kanal geschrieben');
        return;
    }

    $name = trim(($msg['from']['first_name'] ?? '') . ' ' . ($msg['from']['last_name'] ?? '')) . (!empty($msg['from']['username']) ? ' @' . $msg['from']['username'] : '');
    $text = (string) ($msg['text'] ?? $msg['caption'] ?? '');
    $thread = (int) ($msg['message_thread_id'] ?? 0);
    $topics = (array) get_option('sp_tg_topics', []);

    // In "Start & Regeln" und "News" schreiben nur Admins
    if ($thread && in_array($thread, [(int) ($topics['start'] ?? 0), (int) ($topics['news'] ?? 0)], true)) {
        sp_tg_api('deleteMessage', ['chat_id' => $gid, 'message_id' => $msg['message_id']]);
        return;
    }

    $joined = (array) get_option('sp_tg_joined', []);
    $is_new = isset($joined[$uid]) && $joined[$uid] > time() - DAY_IN_SECONDS;
    list($act, $why) = sp_tg_check($msg, $is_new);
    if ($act === '') { sp_tg_maybe_faq($msg); return; }

    sp_tg_api('deleteMessage', ['chat_id' => $gid, 'message_id' => $msg['message_id']]);
    $mention = '<a href="tg://user?id=' . $uid . '">' . esc_html($msg['from']['first_name'] ?? 'Mitglied') . '</a>';
    $reply = ['chat_id' => $gid, 'parse_mode' => 'HTML'];
    if ($thread) $reply['message_thread_id'] = $thread;

    if ($act === 'delete') {
        // Link von neuem Mitglied ohne Verstoß zählt nicht als Verwarnung
        sp_tg_modlog('delete', $name, $why, $text);
        return;
    }
    if ($act === 'warn') {
        $warns = (array) get_option('sp_tg_warns', []);
        $n = (int) ($warns[$uid] ?? 0) + 1;
        $warns[$uid] = $n;
        update_option('sp_tg_warns', $warns, false);
        if ($n >= 3) { $act = 'ban'; $why .= ' (3. Verwarnung)'; }
        else {
            $hint = $why === 'Dosierung/Anwendung'
                ? 'Zu Dosierung oder Anwendung am Menschen geben wir keine Auskunft – unsere Produkte sind ausschließlich für Laborforschungszwecke.'
                : ($why === 'Fremder Link' ? 'Fremde Links sind hier nicht erlaubt.' : 'Weitergeleitete Beiträge anderer Kanäle sind hier nicht erlaubt.');
            $r = sp_tg_api('sendMessage', $reply + ['text' => "⚠️ $mention, deine Nachricht wurde entfernt. $hint\nVerwarnung $n/3."]);
            if (!empty($r['ok'])) wp_schedule_single_event(time() + 90, 'sp_tg_delete_later', [$gid, (int) $r['result']['message_id']]);
            sp_tg_modlog('warn', $name, $why . " ($n/3)", $text);
            return;
        }
    }
    // Bann (inkl. aller Nachrichten dieses Mitglieds)
    sp_tg_api('banChatMember', ['chat_id' => $gid, 'user_id' => $uid, 'revoke_messages' => true]);
    $r = sp_tg_api('sendMessage', $reply + ['text' => "🚫 Ein Mitglied wurde gesperrt: $why. Werbung für andere Shops ist hier nicht erlaubt."]);
    if (!empty($r['ok'])) wp_schedule_single_event(time() + 90, 'sp_tg_delete_later', [$gid, (int) $r['result']['message_id']]);
    sp_tg_modlog('ban', $name, $why, $text);
}

add_action('sp_tg_delete_later', function ($chat, $mid) {
    sp_tg_api('deleteMessage', ['chat_id' => (int) $chat, 'message_id' => (int) $mid]);
}, 10, 2);

/* ---------- FAQ: Peptid-Name eingeben → Bot antwortet mit der Erklärung ---------- */

/** Einträge: key => [title, aliases[], text (Telegram-HTML), url] */
function sp_tg_faq_entries() { return (array) get_option('sp_tg_faq', []); }

function sp_tg_norm($s) {
    $s = mb_strtolower((string) $s);
    $s = strtr($s, ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']);
    return preg_replace('/[^a-z0-9]+/', '', $s);
}

/** Findet den passenden Eintrag (exakter Name/Stichwort zuerst, sonst das längste enthaltene). */
function sp_tg_faq_find($text, $exact = false) {
    $text = preg_replace('~^/(peptid|faq|info)(@\w+)?\s*~i', '', trim((string) $text));
    if ($exact) { // nur wenn die Nachricht praktisch NUR der Name ist ("GHK-Cu", "Was ist Reta?")
        $text = preg_replace('~^(was\s+(ist|sind)(\s+(das|der|die|eigentlich))?|info(s)?\s+(zu|über)|erklär(e|ung)?\s+(mir\s+)?)\s*~iu', '', $text);
        $text = trim($text, " \t\n?!.:");
        if (mb_strlen($text) > 40) return '';
    }
    $n = sp_tg_norm($text);
    if ($n === '' || mb_strlen($text) > 120) return '';
    $best = ''; $bestLen = 0;
    foreach (sp_tg_faq_entries() as $key => $e) {
        if (trim((string) ($e['text'] ?? '')) === '') continue;
        foreach (array_merge([$e['title']], (array) ($e['aliases'] ?? [])) as $a) {
            $an = sp_tg_norm($a);
            if ($an === '') continue;
            if ($an === $n) return $key;
            if ($exact) continue;
            if (strlen($an) >= 3 && strpos($n, $an) !== false && strlen($an) > $bestLen) { $best = $key; $bestLen = strlen($an); }
        }
    }
    return $best;
}

function sp_tg_faq_titles() {
    $t = [];
    foreach (sp_tg_faq_entries() as $e) if (trim((string) ($e['text'] ?? '')) !== '' && ($e['cat'] ?? 'peptide') === 'peptide') $t[] = $e['title'];
    natcasesort($t);
    return array_values($t);
}

function sp_tg_faq_nomatch_html() {
    $t = sp_tg_faq_titles();
    return "🔎 Dazu habe ich leider keinen Eintrag.\n\n" . ($t ? "Schreib einfach einen Peptid-Namen, z. B. <b>" . esc_html(implode('</b>, <b>', array_slice($t, 0, 6))) . "</b>.\n\nAuch möglich: <b>Lagerung</b>, <b>Bac Water</b>, <b>Spritzen</b>, <b>Pen Nadeln</b>, <b>Transport</b>." : 'Die Einträge folgen in Kürze.');
}

/** Sendet einen Eintrag (Texte > 4000 Zeichen werden an Absätzen geteilt). */
function sp_tg_faq_send($chat, $key, $thread = 0, $reply_to = 0) {
    $e = sp_tg_faq_entries()[$key] ?? null;
    if (!$e) return;
    $parts = []; $cur = '';
    foreach (preg_split("/\n{2,}/", trim($e['text'])) as $p) {
        if ($cur !== '' && mb_strlen($cur . "\n\n" . $p) > 3900) { $parts[] = $cur; $cur = $p; }
        else $cur = $cur === '' ? $p : $cur . "\n\n" . $p;
    }
    if ($cur !== '') $parts[] = $cur;
    foreach ($parts as $i => $p) {
        $m = ['chat_id' => $chat, 'text' => $p, 'parse_mode' => 'HTML', 'link_preview_options' => ['is_disabled' => true]];
        if ($thread) $m['message_thread_id'] = $thread;
        if ($reply_to && $i === 0) $m['reply_parameters'] = ['message_id' => $reply_to, 'allow_sending_without_reply' => true];
        if ($i === count($parts) - 1 && !empty($e['url'])) {
            $m['reply_markup'] = ['inline_keyboard' => [[['text' => '🛍 ' . (!empty($e['btn']) ? $e['btn'] : $e['title']) . ' im Shop', 'url' => add_query_arg(['utm_source' => 'telegram', 'utm_medium' => 'faq', 'utm_campaign' => sanitize_title($e['title'])], $e['url'])]]]];
        }
        $r = sp_tg_api('sendMessage', $m);
        if (empty($r['ok']) && stripos((string) ($r['description'] ?? ''), 'parse') !== false) { // HTML-Fehler → als reiner Text
            $m['text'] = wp_strip_all_tags($p);
            unset($m['parse_mode']);
            sp_tg_api('sendMessage', $m);
        }
    }
    $st = (array) get_option('sp_tg_faq_stats', []);
    $st[$key] = (int) ($st[$key] ?? 0) + 1;
    update_option('sp_tg_faq_stats', $st, false);
}

/** In der Gruppe: im Thema FAQ auf jeden Namen/jedes Stichwort antworten, in allen anderen Themen nur,
    wenn die Nachricht praktisch nur aus dem Namen besteht ("GHK-Cu", "Was ist Reta?"). */
function sp_tg_maybe_faq($msg) {
    $topics = (array) get_option('sp_tg_topics', []);
    $thread = (int) ($msg['message_thread_id'] ?? 0);
    if (empty($msg['text']) || !empty($msg['edit_date']) || strpos($msg['text'], '/') === 0 && stripos($msg['text'], '/peptid') !== 0) return;
    $in_faq = !empty($topics['faq']) && $thread === (int) $topics['faq'];
    if (!$in_faq) {
        if ($thread && in_array($thread, [(int) ($topics['start'] ?? 0), (int) ($topics['news'] ?? 0)], true)) return;
        $key = sp_tg_faq_find($msg['text'], true);
        if ($key) sp_tg_faq_send((int) $msg['chat']['id'], $key, $thread, (int) $msg['message_id']);
        return;
    }
    $key = sp_tg_faq_find($msg['text']);
    if ($key) { sp_tg_faq_send((int) $msg['chat']['id'], $key, $thread, (int) $msg['message_id']); return; }
    sp_tg_api('sendMessage', ['chat_id' => (int) $msg['chat']['id'], 'message_thread_id' => $thread, 'parse_mode' => 'HTML', 'text' => sp_tg_faq_nomatch_html(),
        'reply_parameters' => ['message_id' => (int) $msg['message_id'], 'allow_sending_without_reply' => true]]);
}

function sp_tg_faq_intro_html() {
    return "<b>❓ So funktioniert das FAQ</b>\n\n"
        . "Schreib einfach den <b>Namen eines Peptids</b> als Nachricht – z. B. <i>GHK-Cu</i>, <i>Reta</i> oder <i>Was ist Semax?</i>. Der Bot antwortet automatisch mit der Erklärung.\n\n"
        . "Das klappt hier im Thema und in jedem anderen Thema der Gruppe, wenn die Nachricht nur den Namen enthält.\n\n"
        . "Auch Fragen zu <b>Lagerung &amp; Material</b> gehen per Stichwort, z. B. <i>Lagerung</i>, <i>Haltbarkeit</i>, <i>Einfrieren</i>, <i>Transport</i>, <i>Bac Water</i>, <i>Spritzen</i>, <i>Pen Nadeln</i> oder <i>Rekonstitution</i>.\n\n"
        . "Kennt der Bot einen Namen nicht, sagt er Bescheid – schreib dann gern ins Thema 🧪 Wunschliste.\n\n"
        . "<i>Der Bot ist keine KI – die Antworten sind feste, von uns geprüfte Texte. Alle Produkte ausschließlich für Laborforschungszwecke.</i>";
}

/** Thema FAQ anlegen (oder das alte "Peptid-Wissen" umbenennen) und den angepinnten Hinweis aktualisieren. */
function sp_tg_faq_setup() {
    $gid = sp_tg_group_id();
    if (!$gid) return 'keine Gruppe';
    $topics = (array) get_option('sp_tg_topics', []);
    $posted = (array) get_option('sp_tg_posted', []);
    $icon = '5377316857231450742'; // ❓
    if (empty($topics['faq'])) {
        if (!empty($topics['wissen'])) {
            $r = sp_tg_api('editForumTopic', ['chat_id' => $gid, 'message_thread_id' => $topics['wissen'], 'name' => 'FAQ', 'icon_custom_emoji_id' => $icon]);
            $topics['faq'] = $topics['wissen'];
            unset($topics['wissen']);
            if (!empty($posted['wissen'])) { $posted['faq'] = $posted['wissen']; unset($posted['wissen']); }
        } else {
            $r = sp_tg_api('createForumTopic', ['chat_id' => $gid, 'name' => 'FAQ', 'icon_custom_emoji_id' => $icon]);
            if (!empty($r['ok'])) $topics['faq'] = (int) $r['result']['message_thread_id'];
        }
        update_option('sp_tg_topics', $topics, false);
    }
    if (empty($topics['faq'])) return 'Thema fehlt';
    if (!empty($posted['faq'])) {
        $r = sp_tg_api('editMessageText', ['chat_id' => $gid, 'message_id' => $posted['faq'], 'text' => sp_tg_faq_intro_html(), 'parse_mode' => 'HTML', 'link_preview_options' => ['is_disabled' => true]]);
        if (empty($r['ok']) && stripos((string) ($r['description'] ?? ''), 'not modified') === false) unset($posted['faq']);
    }
    if (empty($posted['faq'])) {
        $r = sp_tg_api('sendMessage', ['chat_id' => $gid, 'message_thread_id' => $topics['faq'], 'text' => sp_tg_faq_intro_html(), 'parse_mode' => 'HTML', 'link_preview_options' => ['is_disabled' => true]]);
        if (!empty($r['ok'])) {
            $posted['faq'] = $r['result']['message_id'];
            sp_tg_api('pinChatMessage', ['chat_id' => $gid, 'message_id' => $posted['faq'], 'disable_notification' => true]);
        }
    }
    update_option('sp_tg_posted', $posted, false);
    return 'ok';
}

function sp_tg_faq_kses($html) {
    $html = str_replace(["\r\n", "\r"], "\n", (string) $html);
    return wp_kses($html, ['b' => [], 'strong' => [], 'i' => [], 'em' => [], 'u' => [], 's' => [], 'code' => [], 'pre' => [], 'blockquote' => [], 'tg-spoiler' => [], 'a' => ['href' => true]]);
}

/* ---------- Admin ---------- */
add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Telegram', 'Telegram', 'manage_options', 'sp-telegram', 'sp_tg_render_admin');
}, 30);

function sp_tg_set_webhook() {
    $secret = wp_generate_password(40, false);
    update_option('sp_tg_secret', $secret, false);
    return sp_tg_api('setWebhook', [
        'url'             => rest_url('sp-tg/v1/hook'),
        'secret_token'    => $secret,
        'allowed_updates' => ['message', 'edited_message', 'channel_post', 'my_chat_member', 'chat_member', 'chat_join_request', 'callback_query'],
        'drop_pending_updates' => true,
    ]);
}

function sp_tg_render_admin() {
    if (!current_user_can('manage_options')) return;
    $note = '';
    if (!empty($_POST['sp_tg_save']) && check_admin_referer('sp_tg')) {
        $tok = trim(wp_unslash($_POST['sp_tg_token'] ?? ''));
        if ($tok !== '' && !preg_match('/^\d{6,12}:[A-Za-z0-9_-]{30,}$/', $tok)) {
            $note = '<div class="notice notice-error"><p>Das sieht nicht wie ein Bot-Token aus (Format 123456789:ABC…).</p></div>';
        } elseif ($tok !== '') {
            update_option('sp_tg_token', $tok, false);
            $me = sp_tg_api('getMe');
            if (!empty($me['ok'])) {
                update_option('sp_tg_bot', ['id' => $me['result']['id'], 'username' => $me['result']['username'], 'name' => $me['result']['first_name']], false);
                $wh = sp_tg_set_webhook();
                $note = !empty($wh['ok'])
                    ? '<div class="notice notice-success"><p>Bot @' . esc_html($me['result']['username']) . ' verbunden ✓</p></div>'
                    : '<div class="notice notice-error"><p>Webhook-Fehler: ' . esc_html($wh['description'] ?? '?') . '</p></div>';
            } else {
                delete_option('sp_tg_token');
                $note = '<div class="notice notice-error"><p>Token ungültig: ' . esc_html($me['description'] ?? '?') . '</p></div>';
            }
        }
        if (isset($_POST['sp_tg_ban_words'])) update_option('sp_tg_ban_words', sanitize_textarea_field(wp_unslash($_POST['sp_tg_ban_words'])), false);
    }
    if (!empty($_POST['sp_tg_unwarn']) && check_admin_referer('sp_tg')) {
        delete_option('sp_tg_warns');
        $note = '<div class="notice notice-success"><p>Alle Verwarnungen zurückgesetzt.</p></div>';
    }
    if (!empty($_POST['sp_tg_faq_save']) && check_admin_referer('sp_tg_faq')) {
        $faq = sp_tg_faq_entries();
        $title = sanitize_text_field(wp_unslash($_POST['faq_title'] ?? ''));
        $key = sanitize_key(wp_unslash($_POST['faq_key'] ?? '')) ?: sanitize_title($title);
        if (!empty($_POST['faq_delete'])) { unset($faq[$key]); }
        elseif ($title !== '') {
            $faq[$key] = [
                'title'   => $title,
                'aliases' => array_values(array_filter(array_map('trim', explode(',', sanitize_text_field(wp_unslash($_POST['faq_aliases'] ?? '')))))),
                'url'     => esc_url_raw(wp_unslash($_POST['faq_url'] ?? '')),
                'text'    => sp_tg_faq_kses(wp_unslash($_POST['faq_text'] ?? '')),
            ];
        }
        update_option('sp_tg_faq', $faq, false);
        $res = sp_tg_faq_setup();
        $note = '<div class="notice notice-success"><p>FAQ gespeichert ✓' . ($res !== 'ok' ? ' (Telegram: ' . esc_html($res) . ')' : '') . '</p></div>';
    }
    $bot = get_option('sp_tg_bot');
    $chats = (array) get_option('sp_tg_chats', []);
    $links = (array) get_option('sp_tg_links', []);
    $stats = (array) get_option('sp_tg_joinstats', []);
    echo '<div class="wrap"><h1>Telegram</h1>' . $note;
    echo '<form method="post">';
    wp_nonce_field('sp_tg');
    echo '<table class="form-table"><tr><th>Bot-Token</th><td><input type="password" name="sp_tg_token" class="regular-text" autocomplete="off" placeholder="' . (sp_tg_token() ? 'gespeichert – nur zum Ändern neu eingeben' : '123456789:ABC…') . '">'
        . '<p class="description">Von @BotFather. Wird nur hier gespeichert und nie angezeigt.</p></td></tr>'
        . '<tr><th>Andere Shops (Bann-Wörter)</th><td><textarea name="sp_tg_ban_words" rows="4" class="large-text" placeholder="ein Name oder eine Domain pro Zeile">' . esc_textarea((string) get_option('sp_tg_ban_words', '')) . '</textarea>'
        . '<p class="description">Wer einen dieser Begriffe in der Gruppe schreibt, wird sofort gesperrt. Fremde Telegram-Links und Domains mit „pept“ (außer peptrium.com) werden immer gesperrt.</p></td></tr></table>';
    echo '<p><button class="button button-primary" name="sp_tg_save" value="1">Speichern</button> <button class="button" name="sp_tg_unwarn" value="1" onclick="return confirm(\'Alle Verwarnungen zurücksetzen?\')">Verwarnungen zurücksetzen</button></p></form>';

    echo '<h2>Status</h2><ul>';
    echo '<li>Bot: ' . ($bot ? '@' . esc_html($bot['username']) . ' ✓' : '— noch nicht verbunden') . '</li>';
    if (sp_tg_token()) {
        $wi = sp_tg_api('getWebhookInfo');
        $r = $wi['result'] ?? [];
        echo '<li>Webhook: ' . (!empty($r['url']) ? 'aktiv' : 'fehlt') . (!empty($r['last_error_message']) ? ' – letzter Fehler: ' . esc_html($r['last_error_message']) : '') . '</li>';
    }
    foreach ($chats as $id => $c) {
        echo '<li>' . esc_html($c['type'] === 'channel' ? 'Kanal' : 'Gruppe') . ': <b>' . esc_html($c['title']) . '</b>' . ($c['admin'] ? ' · Bot ist Admin ✓' : ' · Bot ist KEIN Admin') . '</li>';
    }
    echo '</ul>';
    if ($links) {
        $names = ['g_website' => 'Website', 'g_tiktok' => 'TikTok', 'g_mail' => 'E-Mails', 'g_paket' => 'Paketkarte', 'g_direkt' => 'Direkt', 'c_main' => 'Kanal'];
        echo '<h2>Einladungslinks</h2><table class="widefat striped" style="max-width:760px"><thead><tr><th>Quelle</th><th>Link</th><th>Beigetreten</th></tr></thead><tbody>';
        foreach ($names as $k => $n) {
            if (empty($links[$k])) continue;
            echo '<tr><td>' . esc_html($n) . '</td><td><code>' . esc_html($links[$k]) . '</code></td><td>' . ($k === 'c_main' ? '–' : (int) ($stats[$n] ?? 0)) . '</td></tr>';
        }
        echo '</tbody></table><p class="description">Gruppen-Links funktionieren nur per Beitrittsanfrage: der Bot schickt die Regeln privat, nach „Regeln gelesen“ ist man drin.</p>';
    }
    // FAQ-Einträge
    $faq = sp_tg_faq_entries();
    $fst = (array) get_option('sp_tg_faq_stats', []);
    $edit = isset($_GET['faq']) ? sanitize_key($_GET['faq']) : '';
    $e = $faq[$edit] ?? ['title' => '', 'aliases' => [], 'url' => '', 'text' => ''];
    echo '<h2 id="faq">FAQ-Einträge (Thema „FAQ“ + Privatchat)</h2>';
    if ($faq) {
        uasort($faq, function ($a, $b) { return strnatcasecmp($a['title'], $b['title']); });
        echo '<table class="widefat striped" style="max-width:960px"><thead><tr><th>Peptid</th><th>Stichwörter</th><th>Text</th><th>Abrufe</th><th></th></tr></thead><tbody>';
        foreach ($faq as $k => $x) echo '<tr><td><b>' . esc_html($x['title']) . '</b></td><td>' . esc_html(implode(', ', $x['aliases'])) . '</td><td>' . (trim($x['text']) !== '' ? mb_strlen(wp_strip_all_tags($x['text'])) . ' Zeichen' : '<em>leer – wird nicht angezeigt</em>') . '</td><td>' . (int) ($fst[$k] ?? 0) . '</td><td><a href="' . esc_url(admin_url('admin.php?page=sp-telegram&faq=' . $k . '#faq')) . '">Bearbeiten</a></td></tr>';
        echo '</tbody></table>';
    }
    echo '<form method="post" style="max-width:960px;margin-top:14px">';
    wp_nonce_field('sp_tg_faq');
    echo '<input type="hidden" name="faq_key" value="' . esc_attr($edit) . '">';
    echo '<h3>' . ($edit ? 'Eintrag bearbeiten: ' . esc_html($e['title']) : 'Neuer Eintrag') . '</h3><table class="form-table">'
        . '<tr><th>Peptid-Name</th><td><input name="faq_title" class="regular-text" value="' . esc_attr($e['title']) . '" placeholder="GHK-Cu"></td></tr>'
        . '<tr><th>Weitere Stichwörter</th><td><input name="faq_aliases" class="large-text" value="' . esc_attr(implode(', ', $e['aliases'])) . '" placeholder="ghk, kupferpeptid"><p class="description">Komma-getrennt. Groß/Klein, Leerzeichen und Bindestriche egal.</p></td></tr>'
        . '<tr><th>Shop-Link (optional)</th><td><input name="faq_url" class="large-text" value="' . esc_attr($e['url']) . '" placeholder="https://peptrium.com/produkt/ghk-cu/"></td></tr>'
        . '<tr><th>Erklärung</th><td><textarea name="faq_text" rows="14" class="large-text code">' . esc_textarea($e['text']) . '</textarea><p class="description">Erlaubt: &lt;b&gt;fett&lt;/b&gt;, &lt;i&gt;kursiv&lt;/i&gt;, &lt;u&gt;, &lt;a href="…"&gt;Link&lt;/a&gt;, Emojis. Leerzeile = neuer Absatz. Keine Dosierungsangaben.</p></td></tr></table>'
        . '<p><button class="button button-primary" name="sp_tg_faq_save" value="1">Speichern</button>'
        . ($edit ? ' <button class="button" name="faq_delete" value="1" onclick="return confirm(\'Eintrag löschen?\')">Löschen</button> <a class="button-link" href="' . esc_url(admin_url('admin.php?page=sp-telegram#faq')) . '">Neu anlegen</a>' : '') . '</p></form>';

    $mod = (array) get_option('sp_tg_modlog', []);
    echo '<h2>Moderation (letzte 100)</h2>';
    if (!$mod) echo '<p>Noch nichts.</p>';
    else {
        echo '<table class="widefat striped" style="max-width:960px"><thead><tr><th>Zeit</th><th>Aktion</th><th>Mitglied</th><th>Grund</th><th>Text</th></tr></thead><tbody>';
        foreach ($mod as $m) echo '<tr><td>' . esc_html(wp_date('d.m. H:i', $m['t'])) . '</td><td>' . esc_html(['ban' => '🚫 Bann', 'warn' => '⚠️ Verwarnung', 'delete' => '🗑 gelöscht'][$m['a']] ?? $m['a']) . '</td><td>' . esc_html($m['u']) . '</td><td>' . esc_html($m['why']) . '</td><td>' . esc_html($m['txt']) . '</td></tr>';
        echo '</tbody></table><p class="description">Zu Unrecht gesperrt? In Telegram: Gruppe → Mitglieder → Entfernte Nutzer → entsperren.</p>';
    }
    echo '</div>';
}
