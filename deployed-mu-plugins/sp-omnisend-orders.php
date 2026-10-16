<?php
/**
 * Plugin Name: SP Omnisend Bestell-Ereignisse
 * Description: Uebertraegt Bestellungen als Omnisend-Standardereignisse ("placed order", "paid for order", "order fulfilled",
 * "order canceled", "order refunded"), damit Omnisend Umsatz pro Mail auswerten und nach Kaeufen segmentieren kann.
 *
 * (2026-10-16, Nutzer: "alle Punkte") Server-seitig ueber /api/events (origin api, eventVersion v2), asynchron per
 * WP-Cron-Einzelereignis (Checkout wird nicht langsamer), bis zu 3 Wiederholungen. Nie: Testbestellungen (_sp_is_test),
 * Testkunden 17/41/90, reine Guthaben-Aufladungen, Kontakte, die sich in Omnisend abgemeldet haben.
 * Geschenk- und Aufladungs-Positionen werden weggelassen. Bestehende Kontakte behalten ihren Anmeldestatus,
 * neue werden als "nonSubscribed" angelegt (bekommen also keine Newsletter).
 * Schalter: Option sp_oso_on (1 = an). Nachtragen alter Bestellungen: sp_oso_backfill().
 * Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

const SP_OSO_TOPUP_ID = 729;

function sp_oso_on() {
    return get_option('sp_oso_on') === '1' && function_exists('sp_os_key') && sp_os_key() !== '';
}

function sp_oso_skip($order) {
    if (!$order instanceof WC_Order || $order instanceof WC_Order_Refund) {
        return 'kein Auftrag';
    }
    if ($order->get_meta('_sp_is_test')) {
        return 'Test';
    }
    if (in_array((int) $order->get_customer_id(), [17, 41, 90], true)) {
        return 'Testkunde';
    }
    if (!is_email($order->get_billing_email())) {
        return 'keine E-Mail';
    }
    return '';
}

function sp_oso_uuid($seed) {
    $h = md5($seed);
    return substr($h, 0, 8) . '-' . substr($h, 8, 4) . '-4' . substr($h, 13, 3) . '-' . dechex(8 + (hexdec($h[16]) & 3)) . substr($h, 17, 3) . '-' . substr($h, 20, 12);
}

function sp_oso_iso($dt) {
    return $dt ? gmdate('Y-m-d\TH:i:s\Z', $dt->getTimestamp()) : gmdate('Y-m-d\TH:i:s\Z');
}

function sp_oso_addr($o, $type) {
    $g = function ($f) use ($o, $type) {
        $m = "get_{$type}_{$f}";
        return method_exists($o, $m) ? (string) $o->$m() : '';
    };
    // Datensparsam: Omnisend braucht fuer Mails/Segmente keine Anschrift – nur das Land.
    $c = $g('country');
    return array_filter([
        'country' => $c && function_exists('WC') ? (WC()->countries->countries[$c] ?? $c) : $c, 'countryCode' => $c,
    ], 'strlen');
}

/** Properties fuer alle Bestell-Ereignisse; null wenn nichts Uebertragbares in der Bestellung ist. */
function sp_oso_props($o) {
    $items = [];
    $topup = 0.0;
    foreach ($o->get_items() as $it) {
        $pid = (int) $it->get_product_id();
        $total = (float) $it->get_total() + (float) $it->get_total_tax();
        if ($pid === SP_OSO_TOPUP_ID) {
            $topup += $total;
            continue;
        }
        if ($it->get_meta('Geschenk') || $it->get_meta('_sp_gift')) {
            continue;
        }
        $qty = max(1, (int) $it->get_quantity());
        $p = $it->get_product();
        $cats = [];
        foreach (wp_get_post_terms($pid, 'product_cat') as $t) {
            $cats[] = ['id' => (string) $t->term_id, 'title' => $t->name];
        }
        $sub = (float) $it->get_subtotal() + (float) $it->get_subtotal_tax();
        $img = $p ? wp_get_attachment_image_url($p->get_image_id() ?: get_post_thumbnail_id($pid), 'woocommerce_thumbnail') : '';
        $li = [
            'productID' => (string) $pid,
            'productTitle' => get_the_title($pid),
            'productPrice' => round($total / $qty, 2),
            'productQuantity' => $qty,
            'productDiscount' => round(max(0, $sub - $total) / $qty, 2),
            'productURL' => get_permalink($pid),
            'productImageURL' => $img ?: '',
            'productCategories' => $cats,
        ];
        if ($it->get_variation_id()) {
            $li['productVariantID'] = (string) $it->get_variation_id();
            $li['productVariantTitle'] = $it->get_name();
        }
        $items[] = $li;
    }
    if (!$items) {
        return null; // reine Aufladung / nur Geschenke
    }
    $disc = [];
    foreach ($o->get_coupon_codes() as $c) {
        $disc[] = ['code' => strtoupper($c), 'type' => 'coupon'];
    }
    $fee_disc = 0.0; // Partner-Rabatt kommt als negative Gebuehr
    foreach ($o->get_fees() as $fee) {
        $ft = (float) $fee->get_total() + (float) $fee->get_total_tax();
        if ($ft < 0) {
            $fee_disc -= $ft;
        }
    }
    $num = $o->get_meta('_sp_order_number') ?: $o->get_order_number();
    $paid = $o->is_paid();
    $st = $o->get_status();
    $props = [
        'orderID' => (string) $o->get_id(),
        'orderNumber' => (int) $num,
        'createdAt' => sp_oso_iso($o->get_date_created()),
        'currency' => $o->get_currency() ?: 'EUR',
        'totalPrice' => round((float) $o->get_total() - $topup, 2),
        'subTotalPrice' => round((float) $o->get_subtotal() - $topup, 2),
        'subTotalTaxIncluded' => true,
        'totalDiscount' => round((float) $o->get_discount_total() + (float) $o->get_discount_tax() + $fee_disc, 2),
        'shippingPrice' => round((float) $o->get_shipping_total() + (float) $o->get_shipping_tax(), 2),
        'totalTax' => round((float) $o->get_total_tax(), 2),
        'shippingMethod' => $o->get_shipping_method(),
        'paymentMethod' => $o->get_payment_method_title() ?: $o->get_payment_method(),
        'paymentStatus' => $st === 'refunded' ? 'refunded' : ($paid ? 'paid' : 'awaitingPayment'),
        'fulfillmentStatus' => $st === 'completed' ? 'fulfilled' : 'unfulfilled',
        'orderStatusURL' => home_url('/bestellstatus/'),
        'billingAddress' => sp_oso_addr($o, 'billing'),
        'shippingAddress' => sp_oso_addr($o, 'shipping') ?: sp_oso_addr($o, 'billing'),
        'lineItems' => $items,
    ];
    if ($disc) {
        $props['discounts'] = $disc;
    }
    $tn = (string) $o->get_meta('_sp_tracking_number');
    if ($tn !== '') {
        $props['tracking'] = ['code' => $tn, 'courierTitle' => 'DHL', 'courierURL' => function_exists('sp_tracking_dhl_url') ? sp_tracking_dhl_url($tn) : ''];
    }
    return $props;
}

/** Zeitpunkt eines Ereignisses (fuer das Nachtragen alter Bestellungen). */
function sp_oso_event_time($o, $event) {
    switch ($event) {
        case 'paid for order':
            return sp_oso_iso($o->get_date_paid() ?: $o->get_date_created());
        case 'order fulfilled':
            return sp_oso_iso($o->get_date_completed() ?: $o->get_date_modified());
        case 'order canceled':
        case 'order refunded':
            return sp_oso_iso($o->get_date_modified());
        default:
            return sp_oso_iso($o->get_date_created());
    }
}

/** Sendet ein Ereignis jetzt. true, 'skip:<Grund>' oder Fehlertext. */
function sp_oso_send_now($order_id, $event, $historical = false) {
    $o = wc_get_order($order_id);
    if ($why = sp_oso_skip($o)) {
        return 'skip:' . $why;
    }
    $flag = '_sp_oso_' . str_replace(' ', '_', $event);
    if ($o->get_meta($flag)) {
        return 'skip:schon gesendet';
    }
    $props = sp_oso_props($o);
    if (!$props) {
        return 'skip:nur Aufladung/Geschenke';
    }
    $email = strtolower(trim($o->get_billing_email()));
    $st = sp_os_contact_status($email);
    if ($st === null) {
        return 'Kontaktstatus nicht abrufbar';
    }
    if ($st === 'unsubscribed') {
        $o->update_meta_data($flag, 'unsub');
        $o->save_meta_data();
        return 'skip:abgemeldet';
    }
    if ($st === '') {
        $r = sp_os_send(['identifiers' => [['type' => 'email', 'id' => $email, 'channels' => ['email' => ['status' => 'nonSubscribed']]]],
            'firstName' => $o->get_billing_first_name(), 'lastName' => $o->get_billing_last_name(), 'tags' => ['kunde']]);
        if ($r !== true) {
            return 'Kontakt: ' . $r;
        }
    }
    if ($event === 'order canceled') {
        $props['cancelReason'] = 'Storniert';
    }
    $body = [
        'eventName' => $event,
        'origin' => 'api',
        'eventVersion' => 'v2',
        'eventID' => sp_oso_uuid('sp-oso|' . $o->get_id() . '|' . $event),
        'eventTime' => $historical ? sp_oso_event_time($o, $event) : gmdate('Y-m-d\TH:i:s\Z'),
        'contact' => ['email' => $email],
        'properties' => $props,
    ];
    $res = wp_remote_post('https://api.omnisend.com/api/events', [
        'timeout' => 10,
        'headers' => ['Authorization' => 'Omnisend-API-Key ' . sp_os_key(), 'Omnisend-Version' => '2026-03-15', 'Content-Type' => 'application/json'],
        'body' => wp_json_encode($body),
    ]);
    if (is_wp_error($res)) {
        return $res->get_error_message();
    }
    $code = (int) wp_remote_retrieve_response_code($res);
    if ($code < 200 || $code >= 300) {
        return 'HTTP ' . $code . ': ' . substr((string) wp_remote_retrieve_body($res), 0, 300);
    }
    $o->update_meta_data($flag, time());
    $o->save_meta_data();
    return true;
}

function sp_oso_queue($order_id, $event, $delay = 10) {
    $args = [(int) $order_id, $event, 0];
    if (!wp_next_scheduled('sp_oso_send', $args)) {
        wp_schedule_single_event(time() + $delay, 'sp_oso_send', $args);
    }
}

add_action('sp_oso_send', function ($order_id, $event, $try = 0) {
    if (!sp_oso_on()) {
        return;
    }
    if ($event !== 'placed order') {
        $o = wc_get_order($order_id);
        if ($o && !$o->get_meta('_sp_oso_placed_order') && !sp_oso_skip($o)) {
            sp_oso_send_now($order_id, 'placed order'); // Reihenfolge sichern
        }
    }
    $r = sp_oso_send_now($order_id, $event);
    if ($r !== true && strpos((string) $r, 'skip:') !== 0) {
        update_option('sp_oso_last_error', current_time('mysql') . ' #' . $order_id . ' ' . $event . ': ' . $r, false);
        if ($try < 3) {
            wp_schedule_single_event(time() + 600 * ($try + 1), 'sp_oso_send', [(int) $order_id, $event, $try + 1]);
        }
    }
}, 10, 3);

add_action('woocommerce_order_status_changed', function ($id, $from, $to) {
    if (!sp_oso_on()) {
        return;
    }
    if (in_array($to, ['on-hold', 'processing', 'completed'], true)) {
        sp_oso_queue($id, 'placed order', 5);
    }
    if (in_array($to, ['processing', 'completed'], true)) {
        sp_oso_queue($id, 'paid for order', 20);
    }
    if ($to === 'completed') {
        sp_oso_queue($id, 'order fulfilled', 35);
    }
    if ($to === 'cancelled') {
        $o = wc_get_order($id);
        if ($o && $o->get_meta('_sp_oso_placed_order')) {
            sp_oso_queue($id, 'order canceled', 5);
        }
    }
    if ($to === 'refunded') {
        sp_oso_queue($id, 'order refunded', 5);
    }
}, 30, 3);

/** Alte Bestellungen nachtragen (historische Zeitstempel). Gibt eine Zusammenfassung zurueck. */
function sp_oso_backfill($limit = 200) {
    $out = ['ok' => 0, 'skip' => [], 'err' => []];
    $ids = wc_get_orders(['limit' => $limit, 'status' => ['on-hold', 'processing', 'completed', 'cancelled', 'refunded'], 'type' => 'shop_order', 'return' => 'ids', 'orderby' => 'date', 'order' => 'ASC']);
    foreach ($ids as $id) {
        $o = wc_get_order($id);
        $st = $o->get_status();
        $evs = ['placed order'];
        if ($o->is_paid() || in_array($st, ['processing', 'completed', 'refunded'], true)) {
            $evs[] = 'paid for order';
        }
        if ($st === 'completed') {
            $evs[] = 'order fulfilled';
        }
        if ($st === 'cancelled') {
            $evs[] = 'order canceled';
        }
        if ($st === 'refunded') {
            $evs[] = 'order refunded';
        }
        foreach ($evs as $ev) {
            $r = sp_oso_send_now($id, $ev, true);
            if ($r === true) {
                $out['ok']++;
            } elseif (strpos((string) $r, 'skip:') === 0) {
                $out['skip'][$r] = ($out['skip'][$r] ?? 0) + 1;
                if ($r === 'skip:abgemeldet' || $r === 'skip:nur Aufladung/Geschenke' || strpos($r, 'Test') !== false) {
                    break;
                }
            } else {
                $out['err'][] = "#$id $ev: $r";
                break;
            }
        }
    }
    return $out;
}
