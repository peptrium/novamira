<?php
/**
 * Plugin Name: SP Testbestellungen
 * Description: Testbestellungen markieren und aus Dashboard/Vorkasse-Uebersicht heraushalten (2026-10-09).
 *
 * Bestellungen mit Meta _sp_is_test = 1 werden im "Peptrium Dashboard" (alle
 * Tabs) und in der Vorkasse-Uebersicht (inkl. Excel-Export) ausgeblendet,
 * damit Umsatz, offene Zahlungen und Neukunden nur echte Bestellungen zeigen.
 * Markieren/Entfernen: Checkbox "Testbestellung" in der Seitenleiste jeder
 * Bestellung. In der Bestellliste zeigt die Spalte "Abo" zusaetzlich ein
 * "TEST"-Badge. Auf beiden Uebersichten blendet ?sp_show_tests=1 die
 * Testbestellungen wieder ein.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_test_order_is_test($order) {
    return $order && (bool) $order->get_meta('_sp_is_test');
}

/** Ob die aktuelle Admin-Anfrage eine der Auswertungsseiten ist. */
function sp_test_orders_filter_active() {
    if (!is_admin() || !empty($_GET['sp_show_tests'])) {
        return false;
    }
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if (in_array($page, array('peptrium-dashboard', 'sp-vorkasse-overview'), true)) {
        return true;
    }
    $action = isset($_REQUEST['action']) ? sanitize_key($_REQUEST['action']) : '';
    return $action === 'sp_vorkasse_export';
}

add_filter('woocommerce_order_query_args', function ($args) {
    if (!sp_test_orders_filter_active()) {
        return $args;
    }
    $clause = array('key' => '_sp_is_test', 'compare' => 'NOT EXISTS');
    if (empty($args['meta_query'])) {
        $args['meta_query'] = array($clause);
    } else {
        $args['meta_query'] = array('relation' => 'AND', $clause, $args['meta_query']);
    }
    return $args;
});

/** Hinweis oben auf den Auswertungsseiten, wie viele Testbestellungen ausgeblendet sind. */
add_action('admin_notices', function () {
    if (!sp_test_orders_filter_active()) {
        return;
    }
    // Direkt in der DB zaehlen - eine wc_get_orders()-Abfrage wuerde hier selbst gefiltert.
    global $wpdb;
    $count = 0;
    if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
        $count = (int) $wpdb->get_var("SELECT COUNT(DISTINCT order_id) FROM {$wpdb->prefix}wc_orders_meta WHERE meta_key = '_sp_is_test' AND meta_value = '1'");
    } else {
        $count = (int) $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_sp_is_test' AND meta_value = '1'");
    }
    if ($count > 0) {
        $url = add_query_arg('sp_show_tests', 1);
        echo '<div class="notice notice-info"><p>' . (int) $count . ' Testbestellung(en) sind hier ausgeblendet und zählen nicht in den Zahlen mit. <a href="' . esc_url($url) . '">Trotzdem anzeigen</a></p></div>';
    }
});

/* Checkbox in der Bestellung (HPOS- und klassische Bestellansicht). */
add_action('add_meta_boxes', function () {
    foreach (array('woocommerce_page_wc-orders', 'shop_order') as $screen) {
        add_meta_box('sp-test-order', 'Testbestellung', function ($post_or_order) {
            $order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order($post_or_order->ID);
            wp_nonce_field('sp_test_order_save', 'sp_test_order_nonce');
            echo '<label><input type="checkbox" name="sp_is_test" value="1" ' . checked(sp_test_order_is_test($order), true, false) . '> Testbestellung (zählt nicht in Dashboard &amp; Vorkasse-Übersicht)</label>';
        }, $screen, 'side', 'default');
    }
});

add_action('woocommerce_process_shop_order_meta', function ($order_id) {
    if (empty($_POST['sp_test_order_nonce']) || !wp_verify_nonce($_POST['sp_test_order_nonce'], 'sp_test_order_save')) {
        return;
    }
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    if (!empty($_POST['sp_is_test'])) {
        $order->update_meta_data('_sp_is_test', '1');
    } else {
        $order->delete_meta_data('_sp_is_test');
    }
    $order->save();
}, 20);

/* "TEST"-Badge in der Bestellliste (Spalte "Abo" aus sp-abo-buybox.php). */
function sp_test_orders_badge($order) {
    if (sp_test_order_is_test($order)) {
        echo ' <span style="display:inline-block;background:#F0F0F1;color:#50575e;border:1px solid #c3c4c7;border-radius:999px;padding:2px 8px;font-size:11px;font-weight:800;">TEST</span>';
    }
}
add_action('manage_woocommerce_page_wc-orders_custom_column', function ($column, $order) {
    if ($column === 'sp_abo_marker') {
        sp_test_orders_badge($order);
    }
}, 20, 2);
add_action('manage_shop_order_posts_custom_column', function ($column, $post_id) {
    if ($column === 'sp_abo_marker') {
        sp_test_orders_badge(wc_get_order($post_id));
    }
}, 20, 2);
