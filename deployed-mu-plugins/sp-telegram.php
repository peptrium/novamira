<?php
/**
 * Plugin Name: SP Telegram
 * Description: Eigener Peptrium-Telegram-Bot (Community-Gruppe + Kanal). Stufe 1: Token im Dashboard
 * hinterlegen, Webhook auf diese Seite, Bot merkt sich Gruppe/Kanal, sobald er dort Admin wird.
 * Token liegt in Option sp_tg_token (autoload aus) und wird nie ausgegeben.
 */

if (!defined('ABSPATH')) exit;

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

function sp_tg_log($entry) {
    $log = (array) get_option('sp_tg_log', []);
    array_unshift($log, ['t' => time()] + $entry);
    update_option('sp_tg_log', array_slice($log, 0, 30), false);
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
            if (is_array($u)) sp_tg_handle_update($u);
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
    $msg = $u['message'] ?? $u['channel_post'] ?? null;
    if ($msg) {
        sp_tg_log(['ev' => 'msg', 'chat' => $msg['chat']['title'] ?? $msg['chat']['type'], 'len' => strlen((string) ($msg['text'] ?? ''))]);
    }
    do_action('sp_tg_update', $u);
}

/* ---------- Admin ---------- */
add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Telegram', 'Telegram', 'manage_options', 'sp-telegram', 'sp_tg_render_admin');
}, 30);

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
                $secret = wp_generate_password(40, false);
                update_option('sp_tg_secret', $secret, false);
                $wh = sp_tg_api('setWebhook', [
                    'url'             => rest_url('sp-tg/v1/hook'),
                    'secret_token'    => $secret,
                    'allowed_updates' => ['message', 'edited_message', 'channel_post', 'my_chat_member', 'chat_member', 'chat_join_request', 'callback_query'],
                    'drop_pending_updates' => true,
                ]);
                $note = !empty($wh['ok'])
                    ? '<div class="notice notice-success"><p>Bot @' . esc_html($me['result']['username']) . ' verbunden ✓</p></div>'
                    : '<div class="notice notice-error"><p>Webhook-Fehler: ' . esc_html($wh['description'] ?? '?') . '</p></div>';
            } else {
                delete_option('sp_tg_token');
                $note = '<div class="notice notice-error"><p>Token ungültig: ' . esc_html($me['description'] ?? '?') . '</p></div>';
            }
        }
    }
    $bot = get_option('sp_tg_bot');
    $chats = (array) get_option('sp_tg_chats', []);
    echo '<div class="wrap"><h1>Telegram</h1>' . $note;
    echo '<form method="post">';
    wp_nonce_field('sp_tg');
    echo '<table class="form-table"><tr><th>Bot-Token</th><td><input type="password" name="sp_tg_token" class="regular-text" autocomplete="off" placeholder="' . (sp_tg_token() ? 'gespeichert – nur zum Ändern neu eingeben' : '123456789:ABC…') . '">'
        . '<p class="description">Von @BotFather. Wird nur hier gespeichert und nie angezeigt.</p></td></tr></table>';
    echo '<p><button class="button button-primary" name="sp_tg_save" value="1">Speichern &amp; verbinden</button></p></form>';
    echo '<h2>Status</h2><ul>';
    echo '<li>Bot: ' . ($bot ? '@' . esc_html($bot['username']) . ' ✓' : '— noch nicht verbunden') . '</li>';
    if (sp_tg_token()) {
        $wi = sp_tg_api('getWebhookInfo');
        $r = $wi['result'] ?? [];
        echo '<li>Webhook: ' . (!empty($r['url']) ? 'aktiv' : 'fehlt') . (!empty($r['last_error_message']) ? ' – letzter Fehler: ' . esc_html($r['last_error_message']) : '') . (isset($r['pending_update_count']) ? ' · wartend: ' . (int) $r['pending_update_count'] : '') . '</li>';
    }
    foreach ($chats as $id => $c) {
        echo '<li>' . esc_html($c['type'] === 'channel' ? 'Kanal' : 'Gruppe') . ': <b>' . esc_html($c['title']) . '</b>' . ($c['admin'] ? ' · Bot ist Admin ✓' : ' · Bot ist KEIN Admin') . ($c['type'] !== 'channel' ? ($c['forum'] ? ' · Themen an ✓' : ' · Themen AUS') : '') . '</li>';
    }
    if (!$chats) echo '<li>Noch keine Gruppe/kein Kanal – Bot als Admin hinzufügen.</li>';
    echo '</ul></div>';
}
