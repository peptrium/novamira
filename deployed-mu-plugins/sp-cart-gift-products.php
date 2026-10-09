<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Free gift products, added to the cart automatically once the (non-gift)
 * subtotal crosses a threshold, and removed again if it drops back below -
 * cumulative across tiers, so a customer keeps every gift they've unlocked
 * as the cart grows. Deliberately real cart/order line items (price 0), not
 * just a visual progress bar (the drawer's existing gift-progress bar in
 * sp-cart-gifts.php is unchanged/still purely visual) - so the gift shows
 * up in the order confirmation email, invoice, and admin order screen for
 * fulfillment/packing. The 100€ tier's free shipping is handled separately
 * by WooCommerce's own Free Shipping method (min_amount=100, see
 * sp-free-shipping.php); this file only handles the physical gift items.
 */

define('SP_GIFT_ITEM_META', 'sp_is_gift');

/**
 * Bac Water als Geschenk (09.10.2026, Nutzerwunsch): vorerst AUS. 100 EUR =
 * nur noch Gratisversand. Sobald der Nutzer Bescheid gibt, auf true setzen -
 * dann gibt es Bac Water ZUSAMMEN mit dem Injektionskit ab 200 EUR (und bei
 * Abo-Erstbestellungen wieder das gemeinsame "Willkommenspaket").
 * Die Anzeige im Warenkorb-Drawer (sp-cart-gifts.php) richtet sich nach
 * derselben Konstante.
 */
define('SP_GIFT_BAC_WATER_ENABLED', false);

/**
 * Bac Water (74) + Injektionskit (745) sind die beiden Geschenke, die eine
 * Abo-Erstbestellung unten garantiert bekommt (Schwelle wird auf 200
 * hochgesetzt). Bei einer Abo-Bestellung werden sie nicht als zwei einzelne
 * Zeilen mit echtem Produktnamen/-bild gelegt, sondern durch eine einzige
 * Zeile des eigenen Produkts "Willkommenspaket" (ID 817, eigenes Bild)
 * ersetzt - bei einer normalen (Nicht-Abo-)Bestellung, die diese Schwellen
 * organisch per Warenkorbwert erreicht, bleiben die echten Produkte/Namen
 * unveraendert (zwei separate Zeilen wie bisher).
 */
function sp_gift_willkommenspaket_product_ids() {
    // Nur wenn beide Geschenke aktiv sind, werden sie zum Willkommenspaket
    // zusammengefasst - sonst bekommt die Abo-Erstbestellung das Injektionskit
    // als normale Geschenk-Zeile.
    return SP_GIFT_BAC_WATER_ENABLED ? [74, 745] : [];
}

function sp_gift_willkommenspaket_product_id() {
    return 817;
}

/** Liste statt Schwelle=>Geschenk, damit mehrere Geschenke dieselbe Schwelle haben koennen. */
function sp_gift_tiers() {
    $tiers = [
        ['threshold' => 200, 'product_id' => 745, 'variation_id' => 0, 'variation' => [], 'label' => 'Injektionskit'],
    ];
    if (SP_GIFT_BAC_WATER_ENABLED) {
        $tiers[] = ['threshold' => 200, 'product_id' => 74, 'variation_id' => 0, 'variation' => [], 'label' => 'Bac Water'];
    }
    $tiers[] = ['threshold' => 350, 'product_id' => 68, 'variation_id' => 0, 'variation' => [], 'label' => 'GHK-Cu'];
    $tiers[] = ['threshold' => 500, 'product_id' => 65, 'variation_id' => 555, 'variation' => ['attribute_volumen' => '10mg'], 'label' => 'Retatrutide 10mg'];
    return $tiers;
}

function sp_gift_key($product_id, $variation_id) {
    return $product_id . ':' . $variation_id;
}

function sp_gift_non_gift_subtotal($cart) {
    $wallet_product_id = function_exists('sp_wallet_get_topup_product_id') ? sp_wallet_get_topup_product_id() : 0;
    $total = 0.0;
    foreach ($cart->get_cart() as $item) {
        if (!empty($item[SP_GIFT_ITEM_META]) || !$item['data']) {
            continue;
        }
        if ($wallet_product_id && $item['product_id'] === $wallet_product_id) {
            continue; // Guthaben-Aufladung ist kein Produktkauf - zaehlt nicht fuer Warenkorbwert-Geschenke
        }
        $total += (float) $item['data']->get_price() * $item['quantity'];
    }
    return $total;
}

/** Ob der Warenkorb eine Abo-Erstbestellung enthaelt (Produkt mit gewaehltem Lieferintervall). */
function sp_gift_cart_has_abo_signup($cart) {
    foreach ($cart->get_cart() as $item) {
        if (!empty($item['sp_abo_interval_days'])) {
            return true;
        }
    }
    return false;
}

/**
 * Runs after sp-quantity-discount.php's own woocommerce_before_calculate_totals
 * (priority 20) so gift thresholds are evaluated against the customer's real,
 * already-discounted subtotal - not the pre-discount list price.
 */
add_action('woocommerce_before_calculate_totals', function ($cart) {
    static $running = false;
    if ($running) {
        return;
    }
    $running = true;

    $subtotal = sp_gift_non_gift_subtotal($cart);
    $is_abo_order = sp_gift_cart_has_abo_signup($cart);

    // Abo-Erstbestellung: garantiert Bac Water + Injektionskit (die 100/200-Euro-
    // Stufen), unabhaengig vom tatsaechlichen Bestellwert - "fester Bonus, ersetzt
    // die normale Schwellenregel fuer Abo-Erstbestellungen" (siehe Checkliste Phase 3).
    // Hoehere Stufen (350/500) bleiben an den echten Warenkorbwert gebunden, damit
    // niemand allein durchs Abonnieren zusaetzlich die teureren Geschenke bekommt.
    if ($is_abo_order) {
        $subtotal = max($subtotal, 200);
    }

    $willkommenspaket_ids = sp_gift_willkommenspaket_product_ids();
    $qualifying = [];
    foreach (sp_gift_tiers() as $tier) {
        if ($subtotal < $tier['threshold']) {
            continue;
        }
        if ($is_abo_order && in_array($tier['product_id'], $willkommenspaket_ids, true)) {
            // Bac Water + Injektionskit zu einer einzigen "Willkommenspaket"-Zeile
            // zusammenfassen statt zwei einzelne Geschenk-Zeilen zu vergeben.
            $wp_id = sp_gift_willkommenspaket_product_id();
            $qualifying[sp_gift_key($wp_id, 0)] = ['product_id' => $wp_id, 'variation_id' => 0, 'variation' => []];
            continue;
        }
        $qualifying[sp_gift_key($tier['product_id'], $tier['variation_id'])] = $tier;
    }

    // Remove gift lines that no longer qualify (cart shrank back below their tier).
    foreach ($cart->get_cart() as $key => $item) {
        if (empty($item[SP_GIFT_ITEM_META])) {
            continue;
        }
        $gkey = sp_gift_key($item['product_id'], $item['variation_id']);
        if (!isset($qualifying[$gkey])) {
            $cart->remove_cart_item($key);
        }
    }

    $present = [];
    foreach ($cart->get_cart() as $item) {
        if (!empty($item[SP_GIFT_ITEM_META])) {
            $present[sp_gift_key($item['product_id'], $item['variation_id'])] = true;
        }
    }

    foreach ($qualifying as $gkey => $tier) {
        if (!empty($present[$gkey])) {
            continue;
        }
        $cart->add_to_cart(
            $tier['product_id'],
            1,
            $tier['variation_id'],
            $tier['variation'],
            [SP_GIFT_ITEM_META => true]
        );
    }

    foreach ($cart->get_cart() as $item) {
        if (!empty($item[SP_GIFT_ITEM_META]) && $item['data']) {
            $item['data']->set_price(0);
        }
    }

    $running = false;
}, 30, 1);

/**
 * WC_Cart_Totals derives line_subtotal from the same (already zeroed) price
 * used for line_total, so on its own the override above leaves subtotal ==
 * total with no "you saved X" gap for the cart drawer to show. Restore
 * line_subtotal to the item's real regular price after totals are
 * calculated - same pattern already used for the quantity-discount bundles
 * in sp-quantity-discount.php.
 */
add_action('woocommerce_after_calculate_totals', function ($cart) {
    foreach ($cart->get_cart() as $key => $item) {
        if (empty($item[SP_GIFT_ITEM_META]) || !$item['data']) {
            continue;
        }
        $regular_price = (float) $item['data']->get_regular_price();
        if ($regular_price <= 0) {
            continue;
        }
        $cart->cart_contents[$key]['line_subtotal'] = round($regular_price * $item['quantity'], 2);
    }
}, 25, 1);

/**
 * Visible cart-item meta so the frontend (cart drawer, classic cart page,
 * order emails/admin) can tell a gift line apart from a purchased one.
 */
add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    if (!empty($cart_item[SP_GIFT_ITEM_META])) {
        $item_data[] = ['key' => 'Geschenk', 'value' => '🎁 Gratis-Geschenk'];
    }
    return $item_data;
}, 10, 2);

/**
 * Persist the gift flag onto the actual order line item too, so it's still
 * visible after checkout - in the admin order screen, packing/fulfillment
 * view, invoices, and order confirmation emails, not just in the cart.
 */
add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values) {
    if (!empty($values[SP_GIFT_ITEM_META])) {
        $item->add_meta_data('Geschenk', '🎁 Gratis-Geschenk', true);
    }
}, 10, 3);

/**
 * Gift lines are auto-managed by the cart total - block manual quantity
 * changes and removal via the classic WooCommerce cart page (the custom
 * cart drawer already skips rendering those controls for gift lines).
 */
add_filter('woocommerce_cart_item_quantity', function ($quantity_html, $cart_item_key, $cart_item) {
    if (!empty($cart_item[SP_GIFT_ITEM_META])) {
        return '1';
    }
    return $quantity_html;
}, 10, 3);

add_filter('woocommerce_cart_item_remove_link', function ($link, $cart_item_key) {
    $cart_item = WC()->cart->get_cart_item($cart_item_key);
    if (!empty($cart_item[SP_GIFT_ITEM_META])) {
        return '';
    }
    return $link;
}, 10, 2);


