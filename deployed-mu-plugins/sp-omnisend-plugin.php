<?php
/**
 * Plugin Name: SP Omnisend-Plugin zaehmen
 * Description: Das Omnisend-Core-Plugin bleibt aktiv/verbunden, aber (1) sein Tracking-Skript laeuft nur nach Cookie-Zustimmung
 * (ueber den Real-Cookie-Banner-Dienst "Omnisend", Post 593, codeOptIn), (2) es schiebt KEINE WordPress-Benutzer
 * (Admins, Partner, Testkonten) nach Omnisend und setzt beim Login kein Cookie, (3) kein CSS mit time()-Cachebuster auf jeder Seite.
 *
 * (2026-10-16, Nutzer: "warum ist omnisend plugin aus dann mach ihn aktiv")
 * Produktdaten fuer das Omnisend-Ereignis "viewed product" stehen in window.spOsProduct (nur Daten, kein Tracking);
 * das Senden passiert im codeOptIn des Cookie-Dienstes, also erst nach Zustimmung.
 * Zuruecksetzen: Datei loeschen (dann gilt wieder das Standardverhalten des Plugins).
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function () {
    if (!class_exists('Omnisend_Core_Bootstrap')) {
        return;
    }
    remove_action('wp_footer', 'Omnisend\Internal\Snippet::add');
    remove_action('user_register', 'Omnisend\Internal\Sync::identify_user_by_id');
    remove_action('profile_update', 'Omnisend\Internal\Sync::identify_user_by_id');
    remove_action('wp_enqueue_scripts', 'Omnisend_Core_Bootstrap::load_omnisend_site_styles');
    if (defined('OMNISEND_CORE_CRON_SYNC_CONTACT')) {
        remove_all_actions(OMNISEND_CORE_CRON_SYNC_CONTACT);
        if (wp_next_scheduled(OMNISEND_CORE_CRON_SYNC_CONTACT)) {
            wp_clear_scheduled_hook(OMNISEND_CORE_CRON_SYNC_CONTACT);
        }
    }
    // wp_login-Closure des Plugins entfernen (anonym → per Reflection an der Datei erkennen)
    global $wp_filter;
    if (!empty($wp_filter['wp_login'])) {
        foreach ($wp_filter['wp_login']->callbacks as $prio => $cbs) {
            foreach ($cbs as $id => $cb) {
                if ($cb['function'] instanceof Closure) {
                    try {
                        $rf = new ReflectionFunction($cb['function']);
                        if (strpos((string) $rf->getFileName(), '/plugins/omnisend/') !== false) {
                            unset($wp_filter['wp_login']->callbacks[$prio][$id]);
                        }
                    } catch (\Throwable $e) {
                    }
                }
            }
        }
    }
}, 20);

/** Produktdaten fuer "viewed product" (nur Daten; gesendet wird erst nach Zustimmung im Cookie-Dienst). */
add_action('wp_footer', function () {
    if (!function_exists('is_product') || !is_product()) {
        return;
    }
    $p = wc_get_product(get_queried_object_id());
    if (!$p || in_array($p->get_id(), [729, 745, 817], true)) {
        return;
    }
    $cats = [];
    foreach (wp_get_post_terms($p->get_id(), 'product_cat') as $t) {
        $cats[] = ['id' => (string) $t->term_id, 'title' => $t->name];
    }
    $price = (float) ($p->is_type('variable') ? $p->get_variation_price('min') : $p->get_price());
    $reg = (float) ($p->is_type('variable') ? $p->get_variation_regular_price('min') : $p->get_regular_price());
    $img = wp_get_attachment_image_url($p->get_image_id(), 'woocommerce_single');
    $pre = function_exists('sp_preorder_product_ids') && in_array($p->get_id(), (array) sp_preorder_product_ids(), true);
    $d = [
        'id' => (string) $p->get_id(),
        'title' => $p->get_name(),
        'url' => get_permalink($p->get_id()),
        'imageUrl' => $img ?: '',
        'currency' => 'EUR',
        'price' => (int) round($price * 100),
        'status' => $pre ? 'notAvailable' : ($p->is_in_stock() ? 'inStock' : 'outOfStock'),
        'categories' => $cats,
    ];
    if ($reg > $price) {
        $d['oldPrice'] = (int) round($reg * 100);
    }
    echo '<script>window.spOsProduct=' . wp_json_encode($d) . ';</script>';
}, 5);

/** Code fuer den Cookie-Dienst (codeOptIn). */
function sp_osp_optin_code($brand_id) {
    return "<script>\nwindow.omnisend = window.omnisend || [];\nomnisend.push(['accountID', '" . esc_js($brand_id) . "']);\nomnisend.push(['track', '\$pageViewed']);\n"
        . "if (window.spOsProduct) { omnisend.push(['track', 'viewed product', {origin: 'api', eventVersion: 'v4', properties: {product: window.spOsProduct}}]); }\n"
        . "!(function () { var e = document.createElement('script'); e.type = 'text/javascript'; e.async = true; e.src = 'https://omnisnippet1.com/inshop/launcher-v2.js'; var t = document.getElementsByTagName('script')[0]; t.parentNode.insertBefore(e, t); })();\n</script>";
}
