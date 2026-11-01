<?php
/**
 * Krypto-Bestellungen (NOWPayments) nicht nach dem allgemeinen Lager-Zeitlimit
 * (woocommerce_hold_stock_minutes = 60) stornieren, sondern erst nach 24 Stunden.
 * Krypto-Zahlungen dauern oft länger (Coins kaufen, Netzwerk-Bestätigung). (2026-11-01)
 */
if (!defined('ABSPATH')) exit;

if (!defined('SP_KRYPTO_HOLD_HOURS')) define('SP_KRYPTO_HOLD_HOURS', 24);

add_filter('woocommerce_cancel_unpaid_order', function ($cancel, $order) {
    if (!$cancel || !$order || $order->get_payment_method() !== 'nowpayments') return $cancel;
    $created = $order->get_date_created();
    if ($created && (time() - $created->getTimestamp()) < SP_KRYPTO_HOLD_HOURS * HOUR_IN_SECONDS) return false;
    return $cancel;
}, 10, 2);
