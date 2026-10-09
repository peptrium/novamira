<?php
/**
 * Plugin Name: SP Wallet/Abo Safeguards
 * Description: Schliesst Luecken rund um Guthaben-Aufladung und Abo (2026-10-09).
 *
 * 1) Kundenkonto Pflicht, sobald ein Abo-Artikel oder eine Guthaben-Aufladung im
 *    Warenkorb liegt. Die bestehende Sperre in sp-abo-buybox.php haengt an
 *    woocommerce_checkout_process, das im hier genutzten Block-Checkout (Store
 *    API) nie feuert - Gastbestellungen liefen daher durch: Abo wurde nie
 *    angelegt, Aufladung nie gutgeschrieben (sp_wallet_credit_on_payment braucht
 *    eine customer_id). woocommerce_checkout_registration_required wird von
 *    klassischem UND Block-Checkout ausgewertet (Store API:
 *    should_create_customer_account()).
 * 2) Keinerlei Rabatt auf Guthaben-Aufladungen: keine Gutscheincodes, kein
 *    automatischer Empfehlungs-Rabatt (negative Fee aus sp-affiliate-program.php).
 *    Zusaetzliches Sicherheitsnetz bei der Gutschrift: gutgeschrieben wird nie
 *    mehr als tatsaechlich bezahlt wurde.
 * 3) Guthaben -> Gutschein: kein doppelter Gutschein beim Neuladen, Warnung
 *    wenn danach das Guthaben nicht mehr fuers Abo reicht.
 *    (Sofortiges Fortsetzen nach Aufladung: siehe sp-abo-stack-billing.php.)
 *
 * Produkt-Einstellungen der Aufladung (Post 729) wurden separat gesetzt:
 * sold_individually=yes (Menge fest 1) und slicewp_disable_commissions=1
 * (keine Partner-Provision auf Aufladungen - die Provision entsteht erst beim
 * spaeteren, mit Guthaben bezahlten Einkauf).
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_wsg_cart_has_topup() {
    return function_exists('sp_wallet_cart_contains_topup_product') && sp_wallet_cart_contains_topup_product();
}

function sp_wsg_cart_needs_account() {
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }
    if (sp_wsg_cart_has_topup()) {
        return true;
    }
    return function_exists('sp_abo_cart_has_abo_item') && sp_abo_cart_has_abo_item();
}

/* ---------------------------------------------------------------------
 * 1) Kundenkonto Pflicht
 * ------------------------------------------------------------------- */

add_filter('woocommerce_checkout_registration_required', function ($required) {
    if ($required || is_user_logged_in()) {
        return $required;
    }
    return sp_wsg_cart_needs_account();
}, 20);

/** Registrierung an der Kasse muss dafuer erlaubt sein (ist sie aktuell - nur Absicherung gegen spaeteres Abschalten). */
add_filter('woocommerce_checkout_registration_enabled', function ($enabled) {
    if (!$enabled && !is_user_logged_in() && sp_wsg_cart_needs_account()) {
        return true;
    }
    return $enabled;
}, 20);

/** Harte Sperre im Block-Checkout, falls trotzdem ohne Konto bestellt wuerde. */
add_action('woocommerce_store_api_checkout_update_order_from_request', function ($order, $request) {
    if (is_user_logged_in() || !sp_wsg_cart_needs_account()) {
        return;
    }
    if (WC()->checkout()->is_registration_required() && WC()->checkout()->is_registration_enabled()) {
        return; // Store API legt das Konto in diesem Fall selbst an.
    }
    if (filter_var($request['create_account'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
        return;
    }
    throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
        'sp_account_required',
        'Für ein Abo oder eine Guthaben-Aufladung brauchst du ein Kundenkonto. Bitte logge dich ein oder erstelle ein Konto.',
        400
    );
}, 10, 2);

/** Letztes Sicherheitsnetz: bezahlte Aufladung ohne Konto -> deutlich sichtbare Notiz statt stillem Verlust. */
add_action('woocommerce_order_status_processing', 'sp_wsg_note_guest_topup', 5, 1);
add_action('woocommerce_order_status_completed', 'sp_wsg_note_guest_topup', 5, 1);
function sp_wsg_note_guest_topup($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || $order->get_customer_id() || !function_exists('sp_wallet_order_contains_topup_product') || !sp_wallet_order_contains_topup_product($order)) {
        return;
    }
    if ($order->get_meta('_sp_wsg_guest_topup_noted')) {
        return;
    }
    $order->add_order_note('ACHTUNG: Guthaben-Aufladung ohne Kundenkonto - es wurde KEIN Guthaben gutgeschrieben. Bitte Konto fuer ' . $order->get_billing_email() . ' anlegen und den Betrag unter Peptrium > Guthaben manuell gutschreiben.');
    $order->update_meta_data('_sp_wsg_guest_topup_noted', 1);
    $order->save();
}

/* ---------------------------------------------------------------------
 * 2) Kein Rabatt auf Guthaben-Aufladungen
 * ------------------------------------------------------------------- */

add_filter('woocommerce_coupon_is_valid', function ($valid, $coupon) {
    if ($valid && sp_wsg_cart_has_topup()) {
        return false;
    }
    return $valid;
}, 20, 2);

add_filter('woocommerce_coupon_error', function ($err, $err_code, $coupon) {
    if ($err_code === WC_Coupon::E_WC_COUPON_INVALID_FILTERED && sp_wsg_cart_has_topup()) {
        return 'Rabattcodes gelten nicht für Guthaben-Aufladungen.';
    }
    return $err;
}, 20, 3);

/** Bereits angewendete Gutscheine entfernen, sobald eine Aufladung in den Warenkorb kommt. */
add_action('woocommerce_add_to_cart', function ($cart_item_key, $product_id) {
    if (!function_exists('sp_wallet_get_topup_product_id') || (int) $product_id !== (int) sp_wallet_get_topup_product_id()) {
        return;
    }
    if (!WC()->cart || !WC()->cart->get_applied_coupons()) {
        return;
    }
    foreach (WC()->cart->get_applied_coupons() as $code) {
        WC()->cart->remove_coupon($code);
    }
}, 10, 2);

/** Automatischen Empfehlungs-Rabatt (negative Fee, sp-affiliate-program.php Prio 20) bei Aufladungen wieder entfernen. */
add_action('woocommerce_cart_calculate_fees', function ($cart) {
    if (!sp_wsg_cart_has_topup()) {
        return;
    }
    $fees = $cart->fees_api()->get_fees();
    $changed = false;
    foreach ($fees as $key => $fee) {
        if ((float) $fee->amount < 0) {
            unset($fees[$key]);
            $changed = true;
        }
    }
    if ($changed) {
        $cart->fees_api()->set_fees($fees);
    }
}, 99);

/**
 * Sicherheitsnetz vor der Gutschrift (sp_wallet_credit_on_payment, Prio 20):
 * gutgeschrieben wird nie mehr als bezahlt. Pro Position max. der bezahlte
 * Positionsbetrag; besteht die Bestellung nur aus Aufladungen, zusaetzlich max.
 * der Bestell-Gesamtbetrag (faengt negative Gebuehren/Rabatte ab).
 */
add_action('woocommerce_order_status_processing', 'sp_wsg_cap_topup_credit', 15, 1);
add_action('woocommerce_order_status_completed', 'sp_wsg_cap_topup_credit', 15, 1);
function sp_wsg_cap_topup_credit($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || (function_exists('sp_wallet_order_already_credited') && sp_wallet_order_already_credited($order_id))) {
        return;
    }
    $topup_items = array();
    $all_topup = true;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_wallet_amount')) {
            $topup_items[] = $item;
        } else {
            $all_topup = false;
        }
    }
    if (!$topup_items) {
        return;
    }

    $credits = array();
    $sum = 0.0;
    foreach ($topup_items as $item) {
        $paid = (float) $item->get_total() + (float) $item->get_total_tax();
        $credit = min((float) $item->get_meta('_sp_wallet_amount'), round($paid, 2));
        $credits[$item->get_id()] = $credit;
        $sum += $credit;
    }
    $order_total = (float) $order->get_total();
    if ($all_topup && $sum > 0 && $order_total < $sum - 0.009) {
        $ratio = max(0, $order_total) / $sum;
        foreach ($credits as $id => $credit) {
            $credits[$id] = round($credit * $ratio, 2);
        }
    }

    foreach ($topup_items as $item) {
        $original = (float) $item->get_meta('_sp_wallet_amount');
        $credit = $credits[$item->get_id()];
        if ($credit < $original - 0.009) {
            $item->update_meta_data('_sp_wallet_amount', $credit);
            $item->update_meta_data('_sp_wallet_amount_requested', $original);
            $item->save();
            $order->add_order_note(sprintf('Guthaben-Gutschrift auf den tatsaechlich bezahlten Betrag begrenzt: %s statt %s (Rabatt war auf die Aufladung angewendet).', wc_price($credit), wc_price($original)));
        }
    }
}

/* ---------------------------------------------------------------------
 * 3) Guthaben -> Gutschein ("Mein Guthaben")
 *    - Neuladen der Seite nach dem Erstellen darf keinen zweiten Gutschein
 *      erzeugen (Formular-POST aus der Browser-Historie entfernen).
 *    - Warnung im Bestaetigungs-Dialog, wenn danach das Guthaben nicht mehr
 *      fuer die naechste Abo-Lieferung reicht.
 * ------------------------------------------------------------------- */

add_action('wp_footer', function () {
    if (!is_user_logged_in() || !function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('guthaben')) {
        return;
    }
    $user_id = get_current_user_id();
    $balance = function_exists('sp_wallet_get_balance') ? sp_wallet_get_balance($user_id) : 0;
    $next = function_exists('sp_wallet_next_due_summary_for_customer') ? sp_wallet_next_due_summary_for_customer($user_id) : null;
    ?>
    <script>
    (function(){
      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') : ?>
      if (window.history && window.history.replaceState) { window.history.replaceState(null, '', window.location.href); }
      <?php endif; ?>
      var balance = <?php echo wp_json_encode(round((float) $balance, 2)); ?>;
      var next = <?php echo wp_json_encode($next ? array('amount' => round((float) $next['amount'], 2), 'date' => date_i18n('d.m.Y', strtotime($next['date']))) : null); ?>;
      var form = document.getElementById('sp-voucher-form');
      var modalText = document.querySelector('#sp-voucher-confirm-modal p');
      if (!form || !modalText || !next) { return; }
      var warn = document.createElement('p');
      warn.style.cssText = 'margin:-10px 0 18px;font-size:13px;color:#B3261E;font-weight:600;line-height:1.5;display:none';
      modalText.parentNode.insertBefore(warn, modalText.nextSibling);
      form.addEventListener('submit', function(){
        var checked = form.querySelector('input[name="sp_wallet_voucher_amount"]:checked');
        var amount = checked ? parseFloat(checked.value) : 0;
        if (balance - amount < next.amount) {
          warn.textContent = '⚠ Danach reicht dein Guthaben nicht mehr für deine nächste Abo-Lieferung am ' + next.date + ' (' + next.amount.toFixed(2).replace('.', ',') + ' €) – sie würde dann pausiert.';
          warn.style.display = 'block';
        } else {
          warn.style.display = 'none';
        }
      }, true);
    })();
    </script>
    <?php
}, 50);
