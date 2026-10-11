<?php
/**
 * Plugin Name: SP IndexNow
 * Description: Meldet neue/geaenderte Seiten sofort an Bing & Co. (IndexNow: Bing, Yandex, Seznam, Naver; DuckDuckGo/Ecosia/ChatGPT-Suche nutzen Bing).
 *
 * - Schluessel: Option sp_indexnow_key (einmalig erzeugt), ausgeliefert unter /<key>.txt (kein Rewrite noetig).
 * - Automatisch: beim Veroeffentlichen/Aktualisieren von Produkten und Seiten (nur oeffentlich + sichtbar),
 *   max. 1 Meldung pro URL alle 10 Minuten. Letztes Ergebnis in Option sp_indexnow_last.
 * - Alles melden: sp_indexnow_submit_all() (alle Sitemap-URLs), z. B. per execute-php.
 * Google nutzt IndexNow NICHT (dort: Sitemap + Search Console).
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_indexnow_key() {
    $k = get_option('sp_indexnow_key');
    if (!$k) {
        $k = bin2hex(random_bytes(16));
        update_option('sp_indexnow_key', $k, false);
    }
    return $k;
}

add_action('init', function () {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $k = get_option('sp_indexnow_key');
    if ($k && $path === '/' . $k . '.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        echo $k;
        exit;
    }
}, 0);

function sp_indexnow_submit(array $urls) {
    $home = home_url('/');
    $host = wp_parse_url($home, PHP_URL_HOST);
    $urls = array_values(array_unique(array_filter($urls, function ($u) use ($host) {
        return is_string($u) && wp_parse_url($u, PHP_URL_HOST) === $host;
    })));
    if (!$urls) {
        return null;
    }
    $key = sp_indexnow_key();
    $res = wp_remote_post('https://api.indexnow.org/indexnow', [
        'timeout' => 10,
        'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
        'body' => wp_json_encode(['host' => $host, 'key' => $key, 'keyLocation' => $home . $key . '.txt', 'urlList' => array_slice($urls, 0, 10000)]),
    ]);
    $code = is_wp_error($res) ? $res->get_error_message() : wp_remote_retrieve_response_code($res);
    update_option('sp_indexnow_last', ['t' => current_time('mysql'), 'n' => count($urls), 'code' => $code, 'first' => $urls[0]], false);
    return $code;
}

/** Alle URLs aus der WP-Sitemap (Seiten, Produkte, Kategorien) – mit denselben Filtern wie die Sitemap. */
function sp_indexnow_all_urls() {
    $urls = [home_url('/')];
    $server = wp_sitemaps_get_server();
    foreach ($server->registry->get_providers() as $name => $provider) {
        foreach (array_keys($provider->get_object_subtypes() ?: ['' => 1]) as $sub) {
            $max = $provider->get_max_num_pages($sub);
            for ($p = 1; $p <= $max; $p++) {
                foreach ($provider->get_url_list($p, $sub) as $e) {
                    if (!empty($e['loc'])) {
                        $urls[] = $e['loc'];
                    }
                }
            }
        }
    }
    return array_values(array_unique($urls));
}

function sp_indexnow_submit_all() {
    return sp_indexnow_submit(sp_indexnow_all_urls());
}

add_action('save_post', function ($post_id, $post) {
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || $post->post_status !== 'publish' || !in_array($post->post_type, ['product', 'page'], true)) {
        return;
    }
    if (!empty($post->post_password)) {
        return;
    }
    if ($post->post_type === 'product' && function_exists('wc_get_product')) {
        $p = wc_get_product($post_id);
        if (!$p || $p->get_catalog_visibility() === 'hidden') {
            return;
        }
    }
    $url = get_permalink($post_id);
    if (!$url || get_transient('sp_inx_' . md5($url))) {
        return;
    }
    set_transient('sp_inx_' . md5($url), 1, 10 * MINUTE_IN_SECONDS);
    // erst nach dem Request senden, damit das Speichern im Admin nicht wartet
    add_action('shutdown', function () use ($url) {
        sp_indexnow_submit([$url]);
    });
}, 20, 2);
