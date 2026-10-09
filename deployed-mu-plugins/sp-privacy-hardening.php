<?php
/**
 * Plugin Name: SP Privacy Hardening
 * Description: Verhindert, dass Benutzernamen, Kunden-/Partnernamen und Versionsnummern oeffentlich auslesbar sind (2026-10-09).
 *
 * Vorher oeffentlich sichtbar:
 * - /wp-json/wp/v2/users  -> Admin-Login "host0101" (= E-Mail-Alias) + Gravatar-Hash
 * - /?author=N            -> Name jedes registrierten Kunden/Partners (Autoren-Archiv)
 * - oEmbed-Antwort + RSS-Feed -> author_name "host0101"
 * - <meta name="generator"> -> WordPress-/WooCommerce-Versionen
 * Eingeloggte Admins sind nicht betroffen (Editor, Elementor, SliceWP funktionieren weiter).
 */
if (!defined('ABSPATH')) {
    exit;
}

/* REST: Benutzerliste nur fuer Eingeloggte mit Benutzerverwaltungs-Recht */
add_filter('rest_endpoints', function ($endpoints) {
    if (current_user_can('list_users')) {
        return $endpoints;
    }
    foreach (array_keys($endpoints) as $route) {
        if (preg_match('#^/wp/v2/users(/|$)#', $route) && $route !== '/wp/v2/users/me') {
            unset($endpoints[$route]);
        }
    }
    return $endpoints;
});

/* Autoren-Archive (/author/xyz/ und ?author=N) auf die Startseite umleiten */
add_action('template_redirect', function () {
    if (current_user_can('list_users')) {
        return;
    }
    if (is_author() || isset($_GET['author'])) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}, 0);

/* oEmbed ohne Autor */
add_filter('oembed_response_data', function ($data) {
    unset($data['author_name'], $data['author_url']);
    return $data;
});

/* Feed: Shop-Name statt Benutzername */
add_filter('the_author', function ($name) {
    return is_feed() ? get_bloginfo('name') : $name;
});

/* Versionsnummern aus dem Quelltext entfernen */
remove_action('wp_head', 'wp_generator');
add_filter('the_generator', '__return_empty_string');
add_action('init', function () {
    remove_filter('get_the_generator_html', 'wc_generator_tag', 10);
    remove_filter('get_the_generator_xhtml', 'wc_generator_tag', 10);
}, 20);
