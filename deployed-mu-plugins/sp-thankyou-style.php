<?php
/**
 * Chrome-Mono restyle for the native WooCommerce order-received ("Danke")
 * page - the summary page customers land on right after checkout. Reuses
 * the same box language already established in sp-order-emails.php and
 * sp-checkout-style.php, so the page and the confirmation email look like
 * one consistent design instead of a styled email next to a bare WC default.
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Friendlier, on-brand success message with the customer's first name,
 * via WooCommerce's own filter for this exact string.
 */
add_filter('woocommerce_thankyou_order_received_text', function ($text, $order) {
    if (!$order) {
        return $text;
    }
    $first_name = $order->get_billing_first_name();
    return $first_name
        ? sprintf('🎉 Danke, %s! Deine Bestellung ist eingegangen und wird jetzt bearbeitet.', esc_html($first_name))
        : '🎉 Danke! Deine Bestellung ist eingegangen und wird jetzt bearbeitet.';
}, 10, 2);

/**
 * Swap WooCommerce's plain "Unsere Bankverbindung" list (from its
 * checkout/bacs-bank-details.php template) for the same styled box already
 * used in the Vorkasse confirmation email - sp_email_render_bank_box(),
 * defined in sp-order-emails.php (always loaded, mu-plugins load before any
 * page renders) - one design, shown consistently on the thank-you page and
 * in the email instead of two different-looking bank detail blocks.
 */
add_action('woocommerce_thankyou_bacs', function ($order_id) {
    $bacs_gateway = WC()->payment_gateways()->payment_gateways()['bacs'] ?? null;
    if ($bacs_gateway) {
        remove_action('woocommerce_thankyou_bacs', [$bacs_gateway, 'thankyou_page'], 10);
    }
    $order = wc_get_order($order_id);
    if ($order) {
        echo sp_email_render_bank_box($order);
    }
}, 5);

/**
 * Same "So geht es jetzt weiter" Fahrplan shown in the confirmation email
 * (sp_email_render_roadmap(), sp-order-emails.php) - same wording per
 * payment status as the matching email template, so the page a customer
 * lands on right after checkout already tells the same story the email
 * repeats a moment later, instead of a bare order summary with no next
 * steps at all.
 *
 * Vorkasse (bacs, on-hold): runs on the gateway-specific hook at a lower
 * priority than the bank box above, so the roadmap always renders first.
 */
add_action('woocommerce_thankyou_bacs', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || $order->get_status() !== 'on-hold') {
        return;
    }
    echo sp_email_render_roadmap(
        [
            ['Überweisen', 'Betrag <strong>' . wp_kses_post(wc_price($order->get_total(), ['currency' => $order->get_currency()])) . '</strong> auf die unten genannten Bankdaten, Bestellnummer <strong>' . esc_html($order->get_order_number()) . '</strong> <strong>als Verwendungszweck</strong>'],
            ['Zahlungseingang', 'wir prüfen die Eingänge mehrmals täglich &ndash; kein Zahlungsnachweis nötig. Sobald dein Geld da ist, bekommst du eine kurze Bestätigung per E-Mail.'],
            ['Versand', 'diskret per DHL, am selben oder nächsten Werktag nach Zahlungseingang'],
            ['Sendungsnummer', 'bekommst du von uns per E-Mail, sobald dein Paket unterwegs ist.'],
        ],
        '⏱️ <strong>Schneller:</strong> Echtzeitüberweisung (Minuten) oder Krypto (sofortige Bestätigung).'
    );
}, 4);

/**
 * Bereits bezahlt (z. B. Krypto, sofort bestätigt): generic hook, fires for
 * every payment method - guarded by status so it never doubles up with the
 * bacs roadmap above (bacs orders land on "on-hold", never "processing" at
 * this point).
 */
add_action('woocommerce_thankyou', function ($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || $order->get_status() !== 'processing') {
        return;
    }
    echo sp_email_render_roadmap([
        ['Zahlung bestätigt ✓', 'deine Zahlung ist eingegangen'],
        ['Bearbeitung', 'deine Bestellung wird jetzt vorbereitet'],
        ['Versand', 'diskret per DHL, am selben oder nächsten Werktag'],
        ['Sendungsnummer', 'bekommst du von uns per E-Mail, sobald dein Paket unterwegs ist.'],
    ]);
}, 5);

/**
 * Same preorder/backorder note shown in the confirmation email
 * (sp_email_render_preorder_note(), sp-order-emails.php) - was missing here
 * entirely, so a customer who preordered saw the note in their inbox a
 * moment later but never on the page they were just looking at. Hooked the
 * same way sp-order-tracking.php's progress box already is (no
 * is_order_received_page() restriction), so it shows on the My Account
 * order view too, not just right after checkout.
 */
add_action('woocommerce_order_details_after_order_table', function ($order) {
    if (is_admin()) {
        return;
    }
    if (function_exists('sp_order_has_preorder_item') && sp_order_has_preorder_item($order)) {
        echo sp_email_render_preorder_note();
    }
}, 5);

/**
 * Product thumbnail per line item in the "Bestelldetails" table, matching
 * the email's order table (WooCommerce's "email_improvements" feature adds
 * a thumbnail there via wc_get_email_order_items()) - the classic
 * order-received page template never got that treatment, so without this
 * the same order looked illustrated in the inbox and bare on the page it
 * came from a moment earlier.
 */
add_filter('woocommerce_order_item_name', function ($name, $item, $is_visible) {
    if (!function_exists('is_order_received_page') || !is_order_received_page()) {
        return $name;
    }
    $product = $item->get_product();
    if (!$product) {
        return $name;
    }
    $image = $product->get_image([48, 48], ['class' => 'sp-oi-thumb']);
    return '<span class="sp-oi-row">' . $image . '<span class="sp-oi-name">' . $name . '</span></span>';
}, 10, 3);

add_action('wp_footer', function () {
    if (!function_exists('is_order_received_page') || !is_order_received_page()) {
        return;
    }
    ?>
    <style id="sp-thankyou-style">
      body.woocommerce-order-received .entry-content,
      body.woocommerce-order-received .ast-container {
        padding-left: 16px !important;
        padding-right: 16px !important;
        box-sizing: border-box !important;
      }
      @media (min-width: 769px) {
        body.woocommerce-order-received .ast-container {
          padding-left: 20px !important;
          padding-right: 20px !important;
        }
      }

      .woocommerce-order, .woocommerce-order * {
        font-family: 'Sora', sans-serif !important;
      }
      .woocommerce-order {
        color: #0D0F12 !important;
        max-width: 640px !important;
        margin: 0 auto !important;
      }

      /* Success notice */
      p.woocommerce-notice.woocommerce-thankyou-order-received {
        background: #EAF7EF !important;
        border: 1px solid #BEE6CC !important;
        color: #14532D !important;
        border-radius: 12px !important;
        padding: 16px 20px !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        margin: 0 0 22px !important;
        line-height: 1.5 !important;
      }

      /* Order overview: Bestellnummer / Datum / Gesamt / Zahlungsart as mini
         cards - a fixed 2-column grid (not flex-wrap) so an odd 4th item
         never stretches alone across a whole row; minmax(0,1fr) keeps a
         long value (e.g. a long payment method name) from pushing a column
         wider than its share instead of wrapping inside it. */
      ul.woocommerce-order-overview {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
        gap: 10px !important;
        list-style: none !important;
        margin: 0 0 22px !important;
        padding: 0 !important;
      }
      ul.woocommerce-order-overview li {
        background: #F9FAFA !important;
        border: 1px solid #DCDEE0 !important;
        border-radius: 10px !important;
        padding: 12px 14px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: .04em !important;
        text-transform: uppercase !important;
        color: #4B5157 !important;
        margin: 0 !important;
        min-width: 0 !important;
      }
      ul.woocommerce-order-overview li strong {
        display: block !important;
        font-size: 16px !important;
        font-weight: 700 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        color: #0D0F12 !important;
        margin-top: 4px !important;
      }

      /* Section headings ("Bestelldetails", "Rechnungsadresse", ...) */
      .woocommerce-order-details__title,
      .woocommerce-column__title {
        font-size: 17px !important;
        font-weight: 700 !important;
        color: #0D0F12 !important;
        margin: 0 0 12px !important;
      }

      /* Order items table */
      table.woocommerce-table--order-details {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1px solid #DCDEE0 !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        margin: 0 0 22px !important;
      }
      table.woocommerce-table--order-details th,
      table.woocommerce-table--order-details td {
        padding: 12px 16px !important;
        border-bottom: 1px solid #F2F3F4 !important;
        font-size: 14px !important;
        color: #0D0F12 !important;
        text-align: left !important;
      }
      table.woocommerce-table--order-details thead th {
        background: #F9FAFA !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: .04em !important;
        color: #4B5157 !important;
      }
      table.woocommerce-table--order-details tfoot th,
      table.woocommerce-table--order-details tfoot td {
        font-weight: 600 !important;
      }
      table.woocommerce-table--order-details tfoot tr:last-child th,
      table.woocommerce-table--order-details tfoot tr:last-child td {
        border-bottom: none !important;
        font-size: 16px !important;
        font-weight: 700 !important;
      }
      table.woocommerce-table--order-details a {
        color: #0D0F12 !important;
        text-decoration: underline !important;
        text-decoration-color: #A8B0B9 !important;
      }
      .sp-oi-row {
        display: flex !important;
        align-items: center !important;
        gap: 12px !important;
      }
      .sp-oi-thumb {
        width: 48px !important;
        height: 48px !important;
        object-fit: cover !important;
        border-radius: 8px !important;
        border: 1px solid #DCDEE0 !important;
        flex-shrink: 0 !important;
      }
      .sp-oi-name {
        min-width: 0 !important;
      }

      /* Billing/shipping address card(s) */
      .woocommerce-customer-details {
        background: #F9FAFA !important;
        border: 1px solid #DCDEE0 !important;
        border-radius: 12px !important;
        padding: 18px 20px !important;
        margin-top: 22px !important;
      }
      .woocommerce-customer-details .woocommerce-columns {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 20px !important;
      }
      .woocommerce-customer-details .woocommerce-column {
        flex: 1 1 220px !important;
      }
      .woocommerce-customer-details .woocommerce-column + .woocommerce-column {
        margin-top: 0 !important;
      }
      .woocommerce-customer-details address {
        font-style: normal !important;
        font-size: 14px !important;
        line-height: 1.7 !important;
        color: #4B5157 !important;
      }
      .woocommerce-customer-details p,
      .woocommerce-customer-details a {
        color: #0D0F12 !important;
      }
    </style>
    <?php
}, 999);
