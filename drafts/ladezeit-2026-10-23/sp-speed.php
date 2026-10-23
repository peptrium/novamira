<?php
/**
 * Ladezeit-Feinschliff (2026-10-23).
 * 1) Schriften: Elementor lud Roboto, Roboto Slab und Sora jeweils mit ALLEN Stärken (3 blockierende
 *    CSS-Anfragen). Genutzt werden nur Sora 400–800 und Roboto 400/600 (wenige Elemente) →
 *    eine einzige Anfrage + preconnect.
 * 2) Gutenberg-Block-CSS (17 KB, blockierend) wird nur geladen, wenn der Inhalt wirklich Blöcke hat
 *    (nirgends im Shop der Fall). Warenkorb/Kasse/Konto bleiben unverändert.
 * 3) Startseite: Bilder weiter unten (3-Schritte, Schwebe-Flasche, Sektion a347f10) erst beim
 *    Scrollen laden; das Desktop-Hero-Bild auf dem Handy gar nicht (ist dort ausgeblendet).
 * 4) Produktseiten (Reta, Reta-Pen, Mots-C-Pen): das Kaufbox-Skript setzte beim Laden src/srcset des
 *    Produktbilds auf das (identische) Variantenbild → Bild lud neu, LCP wurde ~2 s später gewertet.
 *    Tausch nur noch, wenn die Variante wirklich ein anderes Bild hat.
 * Rückgängig: Datei löschen (danach Seiten-Cache leeren).
 */
if (!defined('ABSPATH')) exit;

function sp_speed_skip() {
    return is_admin() || isset($_GET['elementor-preview']) || (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page()));
}

add_action('wp_head', function () {
    if (sp_speed_skip()) return;
    echo "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n";
}, 1);

add_action('wp_enqueue_scripts', function () {
    if (sp_speed_skip()) return;
    $p = is_singular() ? get_post() : null;
    // Elementor-Seiten rendern post_content nicht (Startseite hat dort einen alten Block-Rest)
    if (!$p || get_post_meta($p->ID, '_elementor_edit_mode', true) === 'builder' || !has_blocks($p->post_content)) {
        foreach (['wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles'] as $h) {
            wp_dequeue_style($h);
        }
    }
}, 200);

add_action('wp_print_styles', function () {
    if (sp_speed_skip()) return;
    $had = false;
    foreach (['elementor-gf-roboto', 'elementor-gf-robotoslab', 'elementor-gf-sora'] as $h) {
        if (wp_style_is($h, 'enqueued') || wp_style_is($h, 'registered')) {
            wp_dequeue_style($h);
            $had = true;
        }
    }
    if ($had) {
        wp_enqueue_style('sp-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&family=Sora:wght@400;500;600;700;800&display=swap', [], null);
    }
}, 1);

/* Startseite: Lazy-Loading per Ausgabe-Filter (Werte vorher bestimmen, im Callback nur Strings). */
add_action('template_redirect', function () {
    if (!is_front_page() || sp_speed_skip()) return;
    $mobile = wp_is_mobile();
    ob_start(function ($html) use ($mobile) {
        if (strlen($html) < 5000) return $html;
        $lazy = function ($tag) {
            $tag = str_replace(' fetchpriority="high"', '', $tag);
            return strpos($tag, 'loading=') === false ? preg_replace('/^<img /', '<img loading="lazy" ', $tag) : $tag;
        };
        $html = preg_replace_callback('/<img [^>]*class="(?:sps-bottle-img|sp-hiw-step-img)"[^>]*>/', function ($m) use ($lazy) { return $lazy($m[0]); }, $html);
        if ($mobile) {
            $html = preg_replace_callback('/<img [^>]*class="sp-hb-image"[^>]*>/', function ($m) use ($lazy) { return $lazy($m[0]); }, $html);
        }
        // Bilder in Sektion a347f10 (weit unten): nicht mehr "fetchpriority=high", sondern lazy
        $i = strpos($html, 'data-id="a347f10"');
        if ($i !== false) {
            $j = strpos($html, '</section>', $i);
            $end = $j === false ? ($i + 20000) : $j;
            $part = substr($html, $i, min(20000, $end - $i));
            $new = preg_replace_callback('/<img [^>]*>/', function ($m) use ($lazy) { return $lazy($m[0]); }, $part);
            $html = substr_replace($html, $new, $i, strlen($part));
        }
        return $html;
    });
}, 9);

add_action('template_redirect', function () {
    if (!function_exists('is_product') || !is_product() || sp_speed_skip()) return;
    ob_start(function ($html) {
        return str_replace(
            'if (heroImg && variation.image && variation.image.src) {',
            "if (heroImg && variation.image && variation.image.src && !(variation.image_id && heroImg.classList.contains('wp-image-' + variation.image_id))) {",
            $html
        );
    });
}, 9);
