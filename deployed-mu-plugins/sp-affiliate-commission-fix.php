<?php
/**
 * Plugin Name: SP Partner-Provision Korrektur
 * Description: Provision vom tatsaechlich bezahlten Warenwert berechnen (Empfehlungs-Rabatt abziehen) - 2026-10-10.
 *
 * Problem: Kommt ein Kunde ueber einen Partner-Link, bekommt er 10 % "Empfehlungs-Rabatt"
 * als NEGATIVE GEBUEHR (fee). SliceWP berechnet die Provision aber nur aus den
 * Produktzeilen und ignoriert Gebuehren -> Provision vom Preis VOR dem Rabatt
 * (Beispiel #3218: 20 % von 169,90 = 33,98 statt 20 % von 152,91 = 30,58).
 * Gutscheine (z. B. SCHLANK10) waren nie betroffen, die stecken schon in den Zeilen.
 *
 * Loesung: direkt nachdem SliceWP die Provision angelegt hat (gleicher Hook, spaetere
 * Prioritaet) wird sie mit dem Faktor (Warenwert + negative Gebuehren) / Warenwert
 * skaliert. Einmal pro Bestellung (Order-Meta _sp_acf_done), bereits ausgezahlte
 * Provisionen bleiben unangetastet. Zusaetzlich: Provision ablehnen, wenn eine
 * Bestellung in den Papierkorb geht (SliceWPs eigener Trash-Hook greift bei HPOS nicht,
 * siehe #3225).
 */
if (!defined('ABSPATH')) {
    exit;
}

/** Anteil des Warenwerts, der nach negativen Gebuehren (Empfehlungs-Rabatt) uebrig bleibt. */
function sp_acf_factor($order) {
    $gross = 0.0;
    foreach ($order->get_items() as $item) {
        $gross += (float) $item->get_total();
    }
    $fee = 0.0;
    foreach ($order->get_fees() as $f) {
        $t = (float) $f->get_total();
        if ($t < 0) {
            $fee += $t;
        }
    }
    if ($gross <= 0 || $fee >= 0) {
        return 1.0;
    }
    return max(0.0, ($gross + $fee) / $gross);
}

/** Provisionen einer Bestellung an den Rabatt anpassen. Rueckgabe: [[id, alt, neu], ...]. */
function sp_acf_fix_order($order_id) {
    $order = wc_get_order($order_id);
    if (!$order || !function_exists('slicewp_get_commissions') || $order->get_meta('_sp_acf_done')) {
        return [];
    }
    $factor = sp_acf_factor($order);
    if ($factor >= 1) {
        return [];
    }
    $changed = [];
    foreach (slicewp_get_commissions(['reference' => $order->get_id(), 'origin' => 'woo', 'number' => -1]) as $c) {
        if ($c->get('status') === 'paid') {
            continue;
        }
        $old = (float) $c->get('amount');
        $new = round($old * $factor, 2);
        if ($new === $old) {
            continue;
        }
        slicewp_update_commission($c->get('id'), ['amount' => $new, 'date_modified' => current_time('mysql', true)]);
        $changed[] = [(int) $c->get('id'), $old, $new];
        $order->add_order_note(sprintf('Partner-Provision #%d an den Empfehlungs-Rabatt angepasst: %s → %s', $c->get('id'), wc_format_decimal($old, 2), wc_format_decimal($new, 2)));
    }
    $order->update_meta_data('_sp_acf_done', current_time('mysql'));
    $order->save();
    return $changed;
}

/* Nach SliceWP (Prio 10) laufen. */
add_action('woocommerce_store_api_checkout_order_processed', function ($order) {
    sp_acf_fix_order(is_a($order, 'WC_Order') ? $order->get_id() : $order);
}, 20);
add_action('woocommerce_checkout_update_order_meta', 'sp_acf_fix_order', 20);

/* Bestellung im Papierkorb -> offene Provision ablehnen (HPOS feuert SliceWPs wc-*_to_trash nicht). */
add_action('woocommerce_trash_order', function ($order_id) {
    if (!function_exists('slicewp_get_commissions')) {
        return;
    }
    foreach (slicewp_get_commissions(['reference' => $order_id, 'origin' => 'woo', 'number' => -1]) as $c) {
        if (in_array($c->get('status'), ['pending', 'unpaid'], true)) {
            slicewp_update_commission($c->get('id'), ['status' => 'rejected', 'date_modified' => current_time('mysql', true)]);
        }
    }
});
