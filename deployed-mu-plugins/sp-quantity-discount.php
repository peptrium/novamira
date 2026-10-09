<?php
/**
 * Applies the same quantity-bundle discount already advertised on the product pages
 * (3 => -10%, 5 => -15%, 10 => -20%) to the actual cart price, for the specific
 * products whose product page shows that tier selector. Without this, the "-10%, du
 * sparst X" text on the product page never actually reduced what customers paid at
 * checkout.
 *
 * This is a bundle-pack model, not a whole-line threshold discount: only the units
 * matching the largest bundle size reached get the discount, any extra units above
 * that get charged full price. E.g. at quantity 4, only 3 units get -10% and the 4th
 * is full price; at quantity 6, only 5 units get -15% and the 6th is full price.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_quantity_discount_product_ids() {
    return array(65, 68, 71, 77, 428, 431, 434, 437);
}

/**
 * The ID to match against sp_quantity_discount_product_ids(): for a variation
 * cart item (e.g. Retatrutide 10mg/20mg), $product->get_id() returns the
 * variation's own post ID, not the parent product's - so eligibility checks
 * against the parent ID (65) always missed variations, silently skipping the
 * bundle discount for the one product on the site that's variable.
 */
function sp_quantity_discount_match_id($product) {
    $parent_id = $product->get_parent_id();
    return $parent_id ? $parent_id : $product->get_id();
}

/**
 * Reduzierte Produkte (Angebotspreis) bekommen KEINEN zusaetzlichen Mengenrabatt -
 * genau wie es ihre Produktseiten zeigen (Staffel-Buttons dort mit data-disc="0").
 * Vorher rechnete der Rabatt hier vom regulaeren Preis: 3x BPC-157 kostete 107,73 €
 * statt der angezeigten 3 x 33,92 € = 101,76 € (gefunden 2026-10-09). 'edit'-Kontext,
 * damit das von set_price() ueberschriebene Laufzeit-Preisfeld keine Rolle spielt.
 */
function sp_quantity_discount_is_on_sale($product) {
    $sale = $product->get_sale_price('edit');
    return $sale !== '' && (float) $sale < (float) $product->get_regular_price('edit') && $product->is_on_sale('edit');
}

function sp_quantity_discount_tiers() {
    return array(
        array('size' => 10, 'percent' => 20),
        array('size' => 5, 'percent' => 15),
        array('size' => 3, 'percent' => 10),
    );
}

function sp_quantity_discount_bundle_for_qty($qty) {
    foreach (sp_quantity_discount_tiers() as $tier) {
        if ($qty >= $tier['size']) {
            return $tier;
        }
    }
    return null;
}

/**
 * Blended per-unit price so that (blended_price * qty) equals
 * (bundle_size discounted units) + (remaining units at full price).
 */
function sp_quantity_discount_blended_unit_price($regular_price, $qty) {
    $tier = sp_quantity_discount_bundle_for_qty($qty);
    if (!$tier) {
        return null;
    }

    $bundle_size = $tier['size'];
    $discounted_unit = round($regular_price * (1 - $tier['percent'] / 100), 2);
    $remainder = $qty - $bundle_size;
    $line_total = ($discounted_unit * $bundle_size) + ($regular_price * $remainder);

    return $line_total / $qty;
}

add_action('woocommerce_before_calculate_totals', function ($cart) {
    // Reentrancy guard only - NOT a "run once per request" guard. Store API quantity
    // updates can legitimately trigger calculate_totals() more than once per request
    // (e.g. once right after the quantity change, once when building the response);
    // skipping every pass after the first left the discount computed against a stale,
    // pre-update quantity while the displayed quantity itself was already current.
    static $running = false;
    if ($running) {
        return;
    }
    $running = true;

    $eligible = sp_quantity_discount_product_ids();

    foreach ($cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (!$product || !in_array(sp_quantity_discount_match_id($product), $eligible, true)) {
            continue;
        }

        // Abo-Artikel bekommen nur den 15%-Abo-Rabatt (ab der 2. Lieferung) - keinen
        // zusaetzlichen Mengenrabatt obendrauf, selbst wenn die Menge zufaellig eine
        // Bundle-Staffel (3/5/10) trifft. Gleiche Regel wie bei Gutscheinen/Affiliate-
        // Rabatten, siehe sp-abo-buybox.php.
        if (!empty($cart_item['sp_abo_interval_days'])) {
            continue;
        }

        if (sp_quantity_discount_is_on_sale($product)) {
            continue;
        }

        $regular_price = (float) $product->get_regular_price();
        if ($regular_price <= 0) {
            continue;
        }

        $blended = sp_quantity_discount_blended_unit_price($regular_price, $cart_item['quantity']);
        if ($blended === null) {
            continue;
        }

        $product->set_price($blended);
    }

    $running = false;
}, 20, 1);

/**
 * WooCommerce's WC_Cart_Totals computes line_subtotal from the same (already
 * discounted) product price used for line_total, so on its own the price override
 * above leaves subtotal == total with no visible "you saved X" gap. Restore
 * line_subtotal to the regular, undiscounted amount after totals are calculated so
 * the Store API (and therefore the cart drawer) can show both the original price and
 * the savings.
 */
add_action('woocommerce_after_calculate_totals', function ($cart) {
    $eligible = sp_quantity_discount_product_ids();

    foreach ($cart->get_cart() as $key => $cart_item) {
        $product = $cart_item['data'];
        if (!$product || !in_array(sp_quantity_discount_match_id($product), $eligible, true)) {
            continue;
        }

        if (!empty($cart_item['sp_abo_interval_days'])) {
            continue;
        }

        if (sp_quantity_discount_is_on_sale($product)) {
            continue;
        }

        $regular_price = (float) $product->get_regular_price();
        if ($regular_price <= 0) {
            continue;
        }

        if (sp_quantity_discount_bundle_for_qty($cart_item['quantity']) === null) {
            continue;
        }

        $cart->cart_contents[$key]['line_subtotal'] = round($regular_price * $cart_item['quantity'], 2);
    }
}, 20, 1);

