<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * "Peptrium Dashboard" - one central admin page meant to replace checking
 * WooCommerce's built-in Analytics for day-to-day numbers. Built because
 * WC Analytics has three properties that confused the shop owner in
 * practice: revenue sync is deferred to a background batch job (can lag
 * hours behind), "products sold" counts on-hold (unpaid Vorkasse) orders
 * by default alongside paid ones, and the date-range/caching behavior
 * isn't obvious from the UI. Every number here is computed live from the
 * real order data on each page load (no lookup-table dependency), so it
 * never has a sync-lag problem - see the full outage/lag diagnosis this
 * page exists to sidestep in the conversation this was built from.
 *
 * "Echter Umsatz" (real revenue) = orders with status processing or
 * completed only. In this shop's own established convention (see
 * sp-vorkasse-overview.php and sp-order-tracking.php): "processing" means
 * payment confirmed but not yet shipped, "completed" additionally means a
 * tracking number has been entered. Both represent money actually
 * received - "on-hold" (Vorkasse awaiting bank transfer) and "pending"
 * (abandoned/incomplete checkout) do not, and are shown separately as
 * "offener Umsatz" rather than mixed into "real" revenue.
 */

define('SP_DASHBOARD_PAID_STATUSES', ['processing', 'completed']);

/**
 * Prioritaet 5 (vor dem WP-Standard 10) und der explizite Index-Untereintrag
 * sind beide noetig, seit "Abo-Verwaltung" und "Guthaben" als eigene
 * Unterseiten zu diesem Menue hinzugekommen sind: sobald ein Menue ueberhaupt
 * Unterseiten hat, biegt WordPress den Klick auf den Hauptmenuepunkt auf den
 * zuerst registrierten Untereintrag um, statt auf den eigenen add_menu_page()
 * -Callback. Ohne diesen expliziten Eintrag - und ohne Garantie, dass er vor
 * den anderen Dateien drankommt - landete der Hauptmenuepunkt auf einer
 * ungueltigen Seite ("Seite scheint nicht zu existieren").
 */
add_action('admin_menu', function () {
    add_menu_page(
        'Peptrium Dashboard',
        'Peptrium Dashboard',
        'manage_woocommerce',
        'peptrium-dashboard',
        'sp_dashboard_render_page',
        'dashicons-chart-area',
        3
    );
    add_submenu_page(
        'peptrium-dashboard',
        'Peptrium Dashboard',
        'Übersicht',
        'manage_woocommerce',
        'peptrium-dashboard',
        'sp_dashboard_render_page'
    );
}, 5);

/* -----------------------------------------------------------------------
 * Date range handling
 * ---------------------------------------------------------------------*/

function sp_dashboard_get_date_range() {
    $today = current_time('Y-m-d');
    $default_start = date('Y-m-d', strtotime('-29 days', current_time('timestamp')));

    $start = isset($_GET['sp_start']) ? sanitize_text_field(wp_unslash($_GET['sp_start'])) : $default_start;
    $end = isset($_GET['sp_end']) ? sanitize_text_field(wp_unslash($_GET['sp_end'])) : $today;

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
        $start = $default_start;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
        $end = $today;
    }
    if ($start > $end) {
        [$start, $end] = [$end, $start];
    }

    return ['start' => $start, 'end' => $end];
}

/* -----------------------------------------------------------------------
 * Core order data
 * ---------------------------------------------------------------------*/

/**
 * Paid orders (processing + completed) whose payment date falls in range.
 * date_paid is what WooCommerce sets automatically the moment an order
 * first reaches a paid status - using it (not date_created) means revenue
 * is attributed to when the money actually arrived, matching the "echter
 * Umsatz" definition above.
 */
function sp_dashboard_get_paid_orders($start, $end) {
    return wc_get_orders([
        'limit'     => -1,
        'status'    => SP_DASHBOARD_PAID_STATUSES,
        'date_paid' => $start . '...' . $end . ' 23:59:59',
        'orderby'   => 'date',
        'order'     => 'ASC',
        'return'    => 'objects',
    ]);
}

/**
 * All-time paid orders, oldest first - needed to determine each
 * customer's very first paid order for the new-vs-returning split, which
 * requires looking further back than the currently selected range.
 */
function sp_dashboard_get_all_paid_orders() {
    return wc_get_orders([
        'limit'   => -1,
        'status'  => SP_DASHBOARD_PAID_STATUSES,
        'orderby' => 'date',
        'order'   => 'ASC',
        'return'  => 'objects',
    ]);
}

function sp_dashboard_order_paid_date($order) {
    $date = $order->get_date_paid() ?: $order->get_date_created();
    return $date ? $date->date('Y-m-d') : null;
}

/**
 * A guest checkout has no customer_id, so billing email is the only
 * consistent identity to track repeat purchases by for this shop.
 */
function sp_dashboard_customer_identity($order) {
    $customer_id = $order->get_customer_id();
    if ($customer_id) {
        return 'u' . $customer_id;
    }
    $email = strtolower(trim($order->get_billing_email()));
    return $email !== '' ? 'e' . $email : null;
}

/**
 * Automatische Abo-Abbuchungen (sp_abo_create_charge_order) werden aus
 * bereits aufgeladenem Guthaben bezahlt - das Geld wurde schon bei der
 * Aufladung als Umsatz gezaehlt. Ohne diesen Ausschluss wuerde jede
 * Abo-Lieferung den "echten Umsatz" ein zweites Mal erhoehen.
 */
function sp_dashboard_is_wallet_funded_order($order) {
    return $order->get_payment_method() === 'sp_wallet';
}

/** Tatsaechlich eingenommener Betrag einer Bestellung: Gesamtbetrag minus bereits erstattete Betraege. */
function sp_dashboard_order_net_total($order) {
    return max(0.0, (float) $order->get_total() - (float) $order->get_total_refunded());
}

/** Anteil einer Bestellung, der Guthaben-Aufladung ist (kein Warenverkauf, sondern Vorauszahlung). */
function sp_dashboard_order_topup_total($order) {
    $sum = 0.0;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_wallet_amount')) {
            $sum += (float) $item->get_total() + (float) $item->get_total_tax();
        }
    }
    return $sum;
}

function sp_dashboard_compute_revenue_stats($orders) {
    $total = 0.0;
    $topup = 0.0;
    $count = 0;
    foreach ($orders as $order) {
        if (sp_dashboard_is_wallet_funded_order($order)) {
            continue;
        }
        $total += sp_dashboard_order_net_total($order);
        $topup += sp_dashboard_order_topup_total($order);
        $count++;
    }
    return [
        'total' => $total,
        'topup' => $topup,
        'count' => $count,
        'aov'   => $count > 0 ? $total / $count : 0.0,
    ];
}

/**
 * Total SliceWP affiliate commission owed for a set of orders (matched by
 * order ID against the commission's `reference` column), excluding
 * "rejected" commissions - a cancelled/refunded order's commission never
 * actually gets paid out, so it shouldn't reduce net revenue. Separate from
 * sp_aff_lifetime_revenue() in sp-affiliate-custom-rates.php, which sums a
 * single affiliate's all-time referred sales for tier progress, not a
 * date-ranged total across all affiliates for the shop's own net-revenue view.
 */
function sp_dashboard_compute_affiliate_commissions($orders) {
    global $wpdb;
    $order_ids = array_map(fn($o) => $o->get_id(), $orders);
    if (empty($order_ids)) {
        return ['total' => 0.0, 'order_count' => 0];
    }

    $placeholders = implode(',', array_fill(0, count($order_ids), '%d'));
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT reference, SUM(CAST(amount AS DECIMAL(15,2))) AS total
         FROM {$wpdb->prefix}slicewp_commissions
         WHERE status != 'rejected' AND reference IN ($placeholders)
         GROUP BY reference",
        $order_ids
    ), ARRAY_A);

    $total = 0.0;
    foreach ($rows as $row) {
        $total += (float) $row['total'];
    }

    return ['total' => $total, 'order_count' => count($rows)];
}

function sp_dashboard_compute_daily_trend($orders, $start, $end) {
    $days = [];
    $cursor = strtotime($start);
    $end_ts = strtotime($end);
    while ($cursor <= $end_ts) {
        $days[date('Y-m-d', $cursor)] = 0.0;
        $cursor = strtotime('+1 day', $cursor);
    }
    foreach ($orders as $order) {
        if (sp_dashboard_is_wallet_funded_order($order)) {
            continue;
        }
        $key = sp_dashboard_order_paid_date($order);
        if ($key && isset($days[$key])) {
            $days[$key] += sp_dashboard_order_net_total($order);
        }
    }
    return $days;
}

function sp_dashboard_compute_payment_breakdown($orders) {
    $out = [];
    foreach ($orders as $order) {
        // Gleiche Basis wie "Echter Umsatz": mit Guthaben bezahlte Bestellungen
        // sind kein neuer Geldeingang (schon bei der Aufladung gezaehlt).
        if (sp_dashboard_is_wallet_funded_order($order)) {
            continue;
        }
        $label = $order->get_payment_method_title() ?: $order->get_payment_method();
        if ($label === '') {
            $label = 'Unbekannt';
        }
        if (!isset($out[$label])) {
            $out[$label] = ['count' => 0, 'total' => 0.0];
        }
        $out[$label]['count']++;
        $out[$label]['total'] += sp_dashboard_order_net_total($order);
    }
    uasort($out, fn($a, $b) => $b['total'] <=> $a['total']);
    return $out;
}

function sp_dashboard_compute_top_products($orders, $limit = 8) {
    $products = [];
    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            if ($item->get_meta('_sp_wallet_amount')) {
                continue; // Guthaben-Aufladung ist kein Produktverkauf
            }
            $pid = $item->get_product_id();
            if (!isset($products[$pid])) {
                $products[$pid] = ['name' => $item->get_name(), 'qty' => 0, 'revenue' => 0.0];
            }
            $products[$pid]['qty'] += $item->get_quantity();
            $products[$pid]['revenue'] += (float) $item->get_total();
        }
    }
    uasort($products, fn($a, $b) => $b['revenue'] <=> $a['revenue']);
    return array_slice($products, 0, $limit, true);
}

/**
 * @return array{new_customers:int, orders_from_new:int, orders_from_returning:int}
 */
function sp_dashboard_compute_customer_stats($all_paid_orders, $start, $end) {
    $first_order_date = [];
    foreach ($all_paid_orders as $order) {
        $identity = sp_dashboard_customer_identity($order);
        $date = sp_dashboard_order_paid_date($order);
        if (!$identity || !$date) {
            continue;
        }
        if (!isset($first_order_date[$identity])) {
            $first_order_date[$identity] = $date; // list is oldest-first, so first hit = earliest
        }
    }

    $new_customer_identities = [];
    $orders_new = 0;
    $orders_returning = 0;

    foreach ($all_paid_orders as $order) {
        $date = sp_dashboard_order_paid_date($order);
        if (!$date || $date < $start || $date > $end) {
            continue;
        }
        $identity = sp_dashboard_customer_identity($order);
        if (!$identity) {
            continue;
        }
        $is_new = ($first_order_date[$identity] ?? '') >= $start;
        if ($is_new) {
            $new_customer_identities[$identity] = true;
            $orders_new++;
        } else {
            $orders_returning++;
        }
    }

    return [
        'new_customers'         => count($new_customer_identities),
        'orders_from_new'       => $orders_new,
        'orders_from_returning' => $orders_returning,
    ];
}

function sp_dashboard_get_open_vorkasse_stats() {
    $orders = wc_get_orders(['limit' => -1, 'status' => 'on-hold', 'payment_method' => 'bacs', 'return' => 'objects']);
    $total = 0.0;
    foreach ($orders as $o) {
        $total += (float) $o->get_total();
    }
    return ['count' => count($orders), 'total' => $total];
}

function sp_dashboard_get_status_breakdown($start, $end) {
    $statuses = ['pending', 'on-hold', 'processing', 'completed', 'cancelled', 'refunded', 'failed'];
    $out = [];
    foreach ($statuses as $status) {
        $orders = wc_get_orders([
            'limit'        => -1,
            'status'       => $status,
            'date_created' => $start . '...' . $end . ' 23:59:59',
            'return'       => 'objects',
        ]);
        $total = 0.0;
        foreach ($orders as $o) {
            $total += (float) $o->get_total();
        }
        $out[$status] = ['count' => count($orders), 'total' => $total];
    }
    return $out;
}

function sp_dashboard_get_unshipped_orders() {
    $orders = wc_get_orders(['limit' => -1, 'status' => 'processing', 'return' => 'objects']);
    $unshipped = [];
    foreach ($orders as $order) {
        // Reine Guthaben-Aufladungen haben nichts zu versenden.
        $needs_shipping = false;
        foreach ($order->get_items() as $item) {
            if (!$item->get_meta('_sp_wallet_amount')) {
                $needs_shipping = true;
                break;
            }
        }
        if (!$needs_shipping) {
            continue;
        }
        $tracking = defined('SP_TRACKING_META_KEY') ? $order->get_meta(SP_TRACKING_META_KEY) : '';
        if (empty($tracking)) {
            $unshipped[] = $order;
        }
    }
    return $unshipped;
}

function sp_dashboard_get_top_coupons($orders, $limit = 8) {
    $out = [];
    foreach ($orders as $order) {
        foreach ($order->get_coupon_codes() as $code) {
            $out[$code] = ($out[$code] ?? 0) + 1;
        }
    }
    arsort($out);
    return array_slice($out, 0, $limit, true);
}

function sp_dashboard_get_backorder_products() {
    $products = wc_get_products(['limit' => -1, 'status' => 'publish']);
    $out = [];
    foreach ($products as $p) {
        if ($p->get_stock_status() === 'onbackorder') {
            $out[] = $p;
        }
    }
    return $out;
}

function sp_dashboard_get_low_revenue_products($orders, $limit = 10) {
    $revenue_by_product = [];
    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            $pid = $item->get_product_id();
            $revenue_by_product[$pid] = ($revenue_by_product[$pid] ?? 0.0) + (float) $item->get_total();
        }
    }
    $products = wc_get_products(['limit' => -1, 'status' => 'publish']);
    $out = [];
    foreach ($products as $p) {
        $out[] = ['id' => $p->get_id(), 'name' => $p->get_name(), 'revenue' => $revenue_by_product[$p->get_id()] ?? 0.0];
    }
    usort($out, fn($a, $b) => $a['revenue'] <=> $b['revenue']);
    return array_slice($out, 0, $limit);
}

/* -----------------------------------------------------------------------
 * Live: carts & sessions
 * ---------------------------------------------------------------------*/

/**
 * Reads WooCommerce's own session table directly - every visitor who has
 * interacted with the cart gets a row here, with the cart contents inside
 * a serialized session_value blob. Not-yet-expired rows with a non-empty
 * cart are the closest available proxy for "who has something in their
 * cart right now"; there's no separate "last active" timestamp, only
 * session_expiry (creation/last-write time + WooCommerce's session
 * lifetime, ~47h by default) - so this reads as "still-valid carts", not
 * a live minute-by-minute presence signal.
 */
function sp_dashboard_get_active_carts() {
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT session_key, session_value, session_expiry FROM {$wpdb->prefix}woocommerce_sessions WHERE session_expiry > UNIX_TIMESTAMP()",
        ARRAY_A
    );

    $carts = [];
    $total_sessions = count($rows);
    foreach ($rows as $row) {
        $data = @unserialize($row['session_value']);
        if (!is_array($data) || empty($data['cart'])) {
            continue;
        }
        $cart_data = @maybe_unserialize($data['cart']);
        if (!is_array($cart_data) || empty($cart_data)) {
            continue;
        }
        $item_count = 0;
        $value = 0.0;
        foreach ($cart_data as $cart_item) {
            if (!is_array($cart_item)) {
                continue;
            }
            $qty = $cart_item['quantity'] ?? 1;
            $item_count += $qty;
            $product = isset($cart_item['product_id']) ? wc_get_product($cart_item['product_id']) : null;
            if ($product) {
                $value += (float) $product->get_price() * $qty;
            }
        }
        if ($item_count > 0) {
            $carts[] = ['items' => $item_count, 'value' => $value];
        }
    }

    return ['carts' => $carts, 'total_sessions' => $total_sessions];
}

/**
 * Carts whose WooCommerce session has already expired (WooCommerce's
 * default session length is 48h from the last write) while still holding
 * items - the closest available proxy for "gave up without ordering",
 * since WooCommerce keeps no separate abandonment record and the session
 * row itself is only garbage-collected some time after expiry, not
 * instantly.
 *
 * A session alone can't prove the shopper never bought anything - they may
 * have completed a later order through a different session/device - so
 * every cart with a captured checkout email gets cross-checked against
 * real orders for that email. Only carts where NO order at all exists for
 * that email are counted as "confirmed" abandoned; carts with no email
 * captured (very common - WooCommerce only stores it once checkout starts)
 * can't be checked either way and are listed separately as "unknown",
 * never merged into the confirmed count.
 *
 * @return array{confirmed: array, unknown: array}
 */
function sp_dashboard_get_abandoned_carts() {
    global $wpdb;
    $now = time();
    // WooCommerce gives logged-in customers a 7-day session, guests only 2
    // days (WC_Session_Handler::set_session_expiration()) - using the guest
    // length for everyone would make a logged-in customer's abandoned cart
    // look up to 5 days more recent than it actually was.
    $guest_lifetime = 2 * DAY_IN_SECONDS;
    $logged_in_lifetime = WEEK_IN_SECONDS;

    $rows = $wpdb->get_results(
        "SELECT session_value, session_expiry FROM {$wpdb->prefix}woocommerce_sessions WHERE session_expiry <= UNIX_TIMESTAMP()",
        ARRAY_A
    );

    $confirmed = [];
    $unknown = [];

    foreach ($rows as $row) {
        $data = @unserialize($row['session_value']);
        if (!is_array($data) || empty($data['cart'])) {
            continue;
        }
        $cart_data = @maybe_unserialize($data['cart']);
        if (!is_array($cart_data) || empty($cart_data)) {
            continue;
        }

        $item_count = 0;
        $value = 0.0;
        $item_names = [];
        foreach ($cart_data as $cart_item) {
            if (!is_array($cart_item) || empty($cart_item['product_id'])) {
                continue;
            }
            $qty = $cart_item['quantity'] ?? 1;
            $item_count += $qty;
            $product = wc_get_product($cart_item['product_id']);
            if ($product) {
                $value += (float) $product->get_price() * $qty;
                $item_names[] = $product->get_name() . ' &times; ' . $qty;
            }
        }
        if ($item_count === 0) {
            continue;
        }

        $customer = isset($data['customer']) ? @maybe_unserialize($data['customer']) : null;
        $email = is_array($customer) && !empty($customer['email']) ? sanitize_email($customer['email']) : '';
        $is_logged_in = is_array($customer) && !empty($customer['id']);

        // Which partner's link (if any) this visitor came in through - set
        // by sp-affiliate-cart-attribution.php the moment a "?ref=ID" link
        // is visited, entirely separate from the cart data itself, so it
        // survives regardless of how the cart contents got serialized.
        $referring_affiliate_id = isset($data['sp_referring_affiliate_id']) ? (int) @maybe_unserialize($data['sp_referring_affiliate_id']) : 0;
        $partner_name = ($referring_affiliate_id > 0 && function_exists('sp_aff_display_name')) ? sp_aff_display_name($referring_affiliate_id) : null;

        $cart_row = [
            'email' => $email,
            'items' => implode(', ', $item_names),
            'value' => $value,
            'last_active' => (int) $row['session_expiry'] - ($is_logged_in ? $logged_in_lifetime : $guest_lifetime),
            'partner_name' => $partner_name,
        ];

        if ($email === '') {
            $unknown[] = $cart_row;
            continue;
        }

        $has_any_order = wc_get_orders([
            'billing_email' => $email,
            'limit' => 1,
            'status' => 'any',
            'return' => 'ids',
        ]);

        if (empty($has_any_order)) {
            $confirmed[] = $cart_row;
        }
    }

    usort($confirmed, fn($a, $b) => $b['last_active'] <=> $a['last_active']);
    usort($unknown, fn($a, $b) => $b['last_active'] <=> $a['last_active']);

    return ['confirmed' => $confirmed, 'unknown' => $unknown];
}

/* -----------------------------------------------------------------------
 * Rendering
 * ---------------------------------------------------------------------*/

function sp_dashboard_money($amount) {
    return wc_price($amount, ['currency' => get_woocommerce_currency()]);
}

function sp_dashboard_tabs() {
    return [
        'uebersicht' => 'Übersicht',
        'bestellungen' => 'Bestellungen',
        'kunden' => 'Kunden & Partner',
        'live' => 'Live',
        'produkte' => 'Produkte & Traffic',
        'analyse' => 'Analyse',
    ];
}

function sp_dashboard_tab_url($tab, $range) {
    return add_query_arg([
        'page' => 'peptrium-dashboard',
        'tab' => $tab,
        'sp_start' => $range['start'],
        'sp_end' => $range['end'],
    ], admin_url('admin.php'));
}

function sp_dashboard_render_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Keine Berechtigung.');
    }

    $range = sp_dashboard_get_date_range();
    $current_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], sp_dashboard_tabs()) ? $_GET['tab'] : 'uebersicht';

    sp_dashboard_render_styles();
    ?>
    <div class="wrap sp-dash">
      <div class="sp-dash-header">
        <h1>Peptrium Dashboard</h1>
        <form method="get" class="sp-dash-range-form">
          <input type="hidden" name="page" value="peptrium-dashboard">
          <input type="hidden" name="tab" value="<?php echo esc_attr($current_tab); ?>">
          <label>Von <input type="date" name="sp_start" value="<?php echo esc_attr($range['start']); ?>"></label>
          <label>Bis <input type="date" name="sp_end" value="<?php echo esc_attr($range['end']); ?>"></label>
          <button type="submit" class="button button-primary">Anwenden</button>
        </form>
      </div>

      <nav class="sp-dash-tabs">
        <?php foreach (sp_dashboard_tabs() as $slug => $label): ?>
          <a href="<?php echo esc_url(sp_dashboard_tab_url($slug, $range)); ?>" class="sp-dash-tab <?php echo $slug === $current_tab ? 'is-active' : ''; ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="sp-dash-content">
        <?php
        switch ($current_tab) {
            case 'bestellungen':
                sp_dashboard_render_tab_bestellungen($range);
                break;
            case 'kunden':
                sp_dashboard_render_tab_kunden($range);
                break;
            case 'live':
                sp_dashboard_render_tab_live();
                break;
            case 'produkte':
                sp_dashboard_render_tab_produkte($range);
                break;
            case 'analyse':
                if (function_exists('sp_an_render_tab')) {
                    sp_an_render_tab($range);
                }
                break;
            default:
                sp_dashboard_render_tab_uebersicht($range);
        }
        ?>
      </div>
    </div>
    <?php
}

function sp_dashboard_render_styles() {
    ?>
    <style>
      .sp-dash{font-family:'Sora',sans-serif;color:#0D0F12;max-width:1200px;}
      .sp-dash-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin:20px 0 24px;}
      .sp-dash-header h1{font-family:'Sora',sans-serif;font-size:26px;font-weight:800;margin:0;padding:0;}
      .sp-dash-range-form{display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#fff;border:1px solid #E2E4E8;border-radius:12px;padding:10px 14px;}
      .sp-dash-range-form label{font-size:12px;color:#4B5157;display:flex;align-items:center;gap:6px;}
      .sp-dash-range-form input[type=date]{border:1px solid #DCDEE0;border-radius:8px;padding:4px 8px;}

      .sp-dash-tabs{display:flex;gap:6px;border-bottom:1px solid #E2E4E8;margin-bottom:24px;flex-wrap:wrap;}
      .sp-dash-tab{padding:10px 18px;border-radius:10px 10px 0 0;font-size:14px;font-weight:600;color:#787c81;text-decoration:none;}
      .sp-dash-tab:hover{color:#0D0F12;}
      .sp-dash-tab.is-active{background:#0D0F12;color:#fff;}

      .sp-dash-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;}
      .sp-dash-kpi{border-radius:16px;padding:20px 22px;color:#fff;position:relative;overflow:hidden;}
      .sp-dash-kpi-label{font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;opacity:.85;margin:0 0 8px;}
      .sp-dash-kpi-value{font-size:28px;font-weight:800;margin:0;line-height:1.2;}
      .sp-dash-kpi-sub{font-size:12px;opacity:.85;margin:6px 0 0;}
      .sp-dash-kpi.c-green{background:linear-gradient(135deg,#0E9F6E 0%,#057A55 100%);}
      .sp-dash-kpi.c-blue{background:linear-gradient(135deg,#3B82F6 0%,#1D4ED8 100%);}
      .sp-dash-kpi.c-purple{background:linear-gradient(135deg,#A855F7 0%,#7E22CE 100%);}
      .sp-dash-kpi.c-amber{background:linear-gradient(135deg,#F59E0B 0%,#B45309 100%);}
      .sp-dash-kpi.c-dark{background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);}
      .sp-dash-kpi.c-red{background:linear-gradient(135deg,#F87171 0%,#B91C1C 100%);}

      .sp-dash-card{background:#fff;border:1px solid #E2E4E8;border-radius:16px;padding:22px 24px;margin-bottom:20px;}
      .sp-dash-card h2{font-size:16px;font-weight:700;margin:0 0 16px;}
      .sp-dash-grid-2{display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;}
      @media (max-width:900px){.sp-dash-grid-2{grid-template-columns:1fr;}}

      .sp-dash-chart{display:flex;align-items:flex-end;gap:3px;height:180px;padding-top:10px;}
      .sp-dash-chart-bar{flex:1;background:linear-gradient(180deg,#3B82F6 0%,#1D4ED8 100%);border-radius:4px 4px 0 0;min-height:2px;position:relative;}
      .sp-dash-chart-bar:hover{opacity:.8;}
      .sp-dash-chart-labels{display:flex;justify-content:space-between;font-size:11px;color:#8A9099;margin-top:8px;}

      .sp-dash-table{width:100%;border-collapse:collapse;}
      .sp-dash-table th{text-align:left;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#4B5157;padding:8px 10px;border-bottom:1px solid #E2E4E8;}
      .sp-dash-table td{padding:10px 10px;border-bottom:1px solid #F2F3F4;font-size:14px;}
      .sp-dash-table tr:last-child td{border-bottom:none;}
      .sp-dash-bar-inline{height:6px;border-radius:4px;background:#E2E4E8;overflow:hidden;margin-top:4px;}
      .sp-dash-bar-inline span{display:block;height:100%;background:linear-gradient(90deg,#3B82F6,#1D4ED8);}

      .sp-dash-status-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;}
      .sp-dash-status-tile{border:1px solid #E2E4E8;border-radius:12px;padding:14px 16px;}
      .sp-dash-status-tile .n{font-size:20px;font-weight:800;}
      .sp-dash-status-tile .l{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#787c81;font-weight:700;}
      .sp-dash-status-tile .v{font-size:12px;color:#4B5157;margin-top:2px;}

      .sp-dash-empty{color:#8A9099;font-size:13px;padding:12px 0;}
      .sp-dash-link-btn{display:inline-flex;align-items:center;gap:6px;background:#0D0F12;color:#fff;text-decoration:none;padding:9px 16px;border-radius:9px;font-size:13px;font-weight:600;}
    </style>
    <?php
}

function sp_dashboard_kpi($label, $value_html, $color, $sub = null) {
    ?>
    <div class="sp-dash-kpi c-<?php echo esc_attr($color); ?>">
      <p class="sp-dash-kpi-label"><?php echo esc_html($label); ?></p>
      <p class="sp-dash-kpi-value"><?php echo $value_html; ?></p>
      <?php if ($sub): ?><p class="sp-dash-kpi-sub"><?php echo esc_html($sub); ?></p><?php endif; ?>
    </div>
    <?php
}

function sp_dashboard_render_chart($daily_data) {
    $max = max(array_merge([0.01], array_values($daily_data)));
    $count = count($daily_data);
    $label_every = max(1, (int) ceil($count / 10));
    ?>
    <div class="sp-dash-chart">
      <?php foreach ($daily_data as $day => $value): ?>
        <div class="sp-dash-chart-bar" style="height:<?php echo esc_attr(max(2, round(($value / $max) * 100))); ?>%;" title="<?php echo esc_attr(date_i18n('d.m.Y', strtotime($day)) . ': ' . wp_strip_all_tags(sp_dashboard_money($value))); ?>"></div>
      <?php endforeach; ?>
    </div>
    <div class="sp-dash-chart-labels">
      <?php
      $i = 0;
      foreach ($daily_data as $day => $value):
          if ($i % $label_every === 0 || $i === $count - 1):
              echo '<span>' . esc_html(date_i18n('d.m.', strtotime($day))) . '</span>';
          endif;
          $i++;
      endforeach;
      ?>
    </div>
    <?php
}

/* -----------------------------------------------------------------------
 * Tab: Übersicht
 * ---------------------------------------------------------------------*/

function sp_dashboard_render_tab_uebersicht($range) {
    $paid_orders = sp_dashboard_get_paid_orders($range['start'], $range['end']);
    $revenue = sp_dashboard_compute_revenue_stats($paid_orders);
    $affiliate_commissions = sp_dashboard_compute_affiliate_commissions($paid_orders);
    $net_revenue = $revenue['total'] - $affiliate_commissions['total'];
    $trend = sp_dashboard_compute_daily_trend($paid_orders, $range['start'], $range['end']);
    $payment_breakdown = sp_dashboard_compute_payment_breakdown($paid_orders);
    $top_products = sp_dashboard_compute_top_products($paid_orders, 8);
    $open_vorkasse = sp_dashboard_get_open_vorkasse_stats();
    $all_paid = sp_dashboard_get_all_paid_orders();
    $customer_stats = sp_dashboard_compute_customer_stats($all_paid, $range['start'], $range['end']);

    ?>
    <div class="sp-dash-kpis">
      <?php
      sp_dashboard_kpi('Echter Umsatz', sp_dashboard_money($revenue['total']), 'green', $revenue['count'] . ' bezahlte Bestellung' . ($revenue['count'] === 1 ? '' : 'en') . ($revenue['topup'] > 0 ? ', davon ' . wp_strip_all_tags(sp_dashboard_money($revenue['topup'])) . ' Guthaben-Aufladungen' : ''));
      sp_dashboard_kpi(
          'Netto-Umsatz (nach Provisionen)',
          sp_dashboard_money($net_revenue),
          'dark',
          $affiliate_commissions['order_count'] > 0
              ? wp_strip_all_tags(sp_dashboard_money($affiliate_commissions['total'])) . ' Provisionen aus ' . $affiliate_commissions['order_count'] . ' Bestellung' . ($affiliate_commissions['order_count'] === 1 ? '' : 'en')
              : 'Keine Affiliate-Provisionen im Zeitraum'
      );
      sp_dashboard_kpi('Ø Bestellwert', sp_dashboard_money($revenue['aov']), 'blue');
      sp_dashboard_kpi('Neukunden', (string) $customer_stats['new_customers'], 'purple', $customer_stats['orders_from_returning'] . ' Bestellungen von Stammkunden');
      sp_dashboard_kpi('Offener Umsatz (Vorkasse)', sp_dashboard_money($open_vorkasse['total']), 'amber', $open_vorkasse['count'] . ' noch nicht überwiesen');
      ?>
    </div>

    <div class="sp-dash-grid-2">
      <div class="sp-dash-card">
        <h2>Umsatz-Verlauf (<?php echo esc_html(date_i18n('d.m.Y', strtotime($range['start']))); ?> &ndash; <?php echo esc_html(date_i18n('d.m.Y', strtotime($range['end']))); ?>)</h2>
        <?php if ($revenue['count'] > 0): ?>
          <?php sp_dashboard_render_chart($trend); ?>
        <?php else: ?>
          <p class="sp-dash-empty">Keine bezahlten Bestellungen in diesem Zeitraum.</p>
        <?php endif; ?>
      </div>

      <div class="sp-dash-card">
        <h2>Umsatz nach Zahlart</h2>
        <?php if (empty($payment_breakdown)): ?>
          <p class="sp-dash-empty">Keine Daten.</p>
        <?php else: ?>
          <?php $max_pm = max(array_column($payment_breakdown, 'total')); ?>
          <?php foreach ($payment_breakdown as $label => $data): ?>
            <div style="margin-bottom:12px;">
              <div style="display:flex;justify-content:space-between;font-size:13px;"><strong><?php echo esc_html($label); ?></strong><span><?php echo sp_dashboard_money($data['total']); ?></span></div>
              <div class="sp-dash-bar-inline"><span style="width:<?php echo esc_attr(round(($data['total'] / max(0.01, $max_pm)) * 100)); ?>%;"></span></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="sp-dash-card">
      <h2>Meistverkaufte Produkte</h2>
      <?php if (empty($top_products)): ?>
        <p class="sp-dash-empty">Keine Daten in diesem Zeitraum.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Produkt</th><th>Menge</th><th>Umsatz</th></tr></thead>
          <tbody>
            <?php foreach ($top_products as $p): ?>
              <tr><td><?php echo esc_html($p['name']); ?></td><td><?php echo (int) $p['qty']; ?></td><td><?php echo sp_dashboard_money($p['revenue']); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <?php
}

/* -----------------------------------------------------------------------
 * Tab: Bestellungen
 * ---------------------------------------------------------------------*/

function sp_dashboard_status_label($status) {
    $labels = [
        'pending'    => 'Wartend',
        'on-hold'    => 'In Wartestellung',
        'processing' => 'In Bearbeitung',
        'completed'  => 'Abgeschlossen',
        'cancelled'  => 'Storniert',
        'refunded'   => 'Erstattet',
        'failed'     => 'Fehlgeschlagen',
    ];
    return $labels[$status] ?? ucfirst($status);
}

function sp_dashboard_render_tab_bestellungen($range) {
    $status_breakdown = sp_dashboard_get_status_breakdown($range['start'], $range['end']);
    $open_vorkasse = sp_dashboard_get_open_vorkasse_stats();
    $unshipped = sp_dashboard_get_unshipped_orders();
    $cancelled_total = $status_breakdown['cancelled']['total'] + $status_breakdown['refunded']['total'];
    $cancelled_count = $status_breakdown['cancelled']['count'] + $status_breakdown['refunded']['count'];
    ?>
    <div class="sp-dash-kpis">
      <?php
      sp_dashboard_kpi('Offene Vorkasse-Zahlungen', (string) $open_vorkasse['count'], 'amber', wp_strip_all_tags(sp_dashboard_money($open_vorkasse['total'])) . ' insgesamt');
      sp_dashboard_kpi('Bezahlt, noch nicht versendet', (string) count($unshipped), 'blue');
      sp_dashboard_kpi('Storniert / Erstattet', (string) $cancelled_count, 'red', wp_strip_all_tags(sp_dashboard_money($cancelled_total)) . ' im Zeitraum');
      ?>
    </div>

    <div class="sp-dash-card">
      <h2>Bestellungen nach Status (im gewählten Zeitraum, nach Bestelldatum)</h2>
      <div class="sp-dash-status-grid">
        <?php foreach ($status_breakdown as $status => $data): ?>
          <div class="sp-dash-status-tile">
            <div class="l"><?php echo esc_html(sp_dashboard_status_label($status)); ?></div>
            <div class="n"><?php echo (int) $data['count']; ?></div>
            <div class="v"><?php echo sp_dashboard_money($data['total']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="sp-dash-grid-2">
      <div class="sp-dash-card">
        <h2>Offene Vorkasse-Zahlungen</h2>
        <p>Alle Bestellungen, bei denen noch auf die Überweisung gewartet wird &ndash; mit Bank-Abgleich-Werkzeug (CSV/Excel-Export &amp; -Import).</p>
        <a class="sp-dash-link-btn" href="<?php echo esc_url(admin_url('admin.php?page=sp-vorkasse-overview')); ?>">Zur Vorkasse-Übersicht &rarr;</a>
      </div>
      <div class="sp-dash-card">
        <h2>Vorbestellungen</h2>
        <p>Übersicht, wie viel von den Vorbestellungs-Produkten noch eingekauft werden muss.</p>
        <a class="sp-dash-link-btn" href="<?php echo esc_url(admin_url('admin.php?page=sp-preorder-overview')); ?>">Zur Vorbestellungen-Übersicht &rarr;</a>
      </div>
    </div>

    <div class="sp-dash-card">
      <h2>Bezahlt, aber noch nicht versendet</h2>
      <?php if (empty($unshipped)): ?>
        <p class="sp-dash-empty">Alles versendet &ndash; keine offenen Sendungen.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Bestellung</th><th>Kunde</th><th>Datum</th><th>Betrag</th></tr></thead>
          <tbody>
            <?php foreach ($unshipped as $order): ?>
              <tr>
                <td><a href="<?php echo esc_url($order->get_edit_order_url()); ?>">#<?php echo esc_html($order->get_order_number()); ?></a></td>
                <td><?php echo esc_html(trim($order->get_formatted_billing_full_name()) ?: $order->get_billing_email()); ?></td>
                <td><?php echo esc_html($order->get_date_created() ? $order->get_date_created()->date('d.m.Y') : ''); ?></td>
                <td><?php echo sp_dashboard_money($order->get_total()); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
    <?php
}

/* -----------------------------------------------------------------------
 * Tab: Live
 * ---------------------------------------------------------------------*/

function sp_dashboard_render_tab_live() {
    $cart_data = sp_dashboard_get_active_carts();
    $carts = $cart_data['carts'];
    $cart_value_total = array_sum(array_column($carts, 'value'));

    $abandoned = sp_dashboard_get_abandoned_carts();
    $abandoned_confirmed = $abandoned['confirmed'];
    $abandoned_unknown = $abandoned['unknown'];
    $abandoned_value_total = array_sum(array_column($abandoned_confirmed, 'value'));
    ?>
    <div class="sp-dash-kpis">
      <?php
      sp_dashboard_kpi('Aktive Warenkörbe', (string) count($carts), 'green', wp_strip_all_tags(sp_dashboard_money($cart_value_total)) . ' Gesamtwert');
      sp_dashboard_kpi('Aktive Sitzungen (ca.)', (string) $cart_data['total_sessions'], 'blue', 'inkl. leerer Warenkörbe');
      sp_dashboard_kpi('Abgebrochene Warenkörbe', (string) count($abandoned_confirmed), 'amber', wp_strip_all_tags(sp_dashboard_money($abandoned_value_total)) . ' Gesamtwert');
      ?>
    </div>

    <div class="sp-dash-card">
      <h2>Was gerade in Warenkörben liegt</h2>
      <p style="color:#787c81;font-size:12px;margin-top:-8px;">Basiert auf WooCommerce-Sitzungen, die noch nicht abgelaufen sind &ndash; eine Annäherung an "gerade aktiv", keine exakte Echtzeit-Anwesenheit.</p>
      <?php if (empty($carts)): ?>
        <p class="sp-dash-empty">Aktuell keine gefüllten Warenkörbe.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>#</th><th>Artikel</th><th>Warenkorb-Wert</th></tr></thead>
          <tbody>
            <?php foreach ($carts as $i => $cart): ?>
              <tr><td><?php echo (int) ($i + 1); ?></td><td><?php echo (int) $cart['items']; ?></td><td><?php echo sp_dashboard_money($cart['value']); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="sp-dash-card">
      <h2>Abgebrochene Warenkörbe</h2>
      <p style="color:#787c81;font-size:12px;margin-top:-8px;">Warenkorb-Sitzung ist abgelaufen, ohne dass die hinterlegte E-Mail-Adresse irgendeine Bestellung hat &ndash; also nicht nur eine andere Zahlart oder ein zweiter Versuch. Zeitangabe ist geschätzt (WooCommerce speichert keinen exakten "zuletzt aktiv"-Zeitpunkt, nur wann die Sitzung ausläuft).</p>
      <?php if (empty($abandoned_confirmed)): ?>
        <p class="sp-dash-empty">Aktuell keine bestätigt abgebrochenen Warenkörbe.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>E-Mail</th><th>Artikel</th><th>Wert</th><th>Zuletzt aktiv</th><th>Partner</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($abandoned_confirmed, 0, 30) as $cart): ?>
              <tr>
                <td><?php echo esc_html($cart['email']); ?></td>
                <td><?php echo wp_kses_post($cart['items']); ?></td>
                <td><?php echo sp_dashboard_money($cart['value']); ?></td>
                <td>vor <?php echo esc_html(human_time_diff($cart['last_active'], current_time('timestamp'))); ?></td>
                <td><?php echo $cart['partner_name'] ? esc_html($cart['partner_name']) : '<span style="color:#B0B4B8;">&ndash;</span>'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (count($abandoned_confirmed) > 30): ?>
          <p style="color:#787c81;font-size:12px;margin-top:10px;">Zeigt die 30 aktuellsten von <?php echo (int) count($abandoned_confirmed); ?> insgesamt.</p>
        <?php endif; ?>
      <?php endif; ?>

      <?php
      $unknown_with_partner = array_values(array_filter($abandoned_unknown, fn($c) => !empty($c['partner_name'])));
      $unknown_without_partner = count($abandoned_unknown) - count($unknown_with_partner);
      ?>
      <?php if (!empty($unknown_with_partner)): ?>
        <h3 style="font-size:14px;margin:22px 0 4px;">Über einen Partner-Link, ohne erfasste E-Mail</h3>
        <p style="color:#787c81;font-size:12px;margin-top:0;">Keine E-Mail bekannt, also nicht mit einer Bestellung abgleichbar &ndash; aber die Partner-Zuordnung selbst kommt unabhängig davon aus dem Link-Klick, ist also trotzdem verlässlich.</p>
        <table class="sp-dash-table">
          <thead><tr><th>Artikel</th><th>Wert</th><th>Zuletzt aktiv</th><th>Partner</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($unknown_with_partner, 0, 30) as $cart): ?>
              <tr>
                <td><?php echo wp_kses_post($cart['items']); ?></td>
                <td><?php echo sp_dashboard_money($cart['value']); ?></td>
                <td>vor <?php echo esc_html(human_time_diff($cart['last_active'], current_time('timestamp'))); ?></td>
                <td><?php echo esc_html($cart['partner_name']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <?php if ($unknown_without_partner > 0): ?>
        <p style="color:#787c81;font-size:12px;margin-top:16px;"><strong><?php echo (int) $unknown_without_partner; ?></strong> weitere abgelaufene Warenkörbe ohne erfasste E-Mail-Adresse und ohne Partner-Link &ndash; lassen sich weder mit Bestellungen noch mit einem Partner abgleichen, könnten also zusätzlich abgebrochen sein.</p>
      <?php endif; ?>
    </div>
    <?php
}

/* -----------------------------------------------------------------------
 * Tab: Kunden & Partner
 * ---------------------------------------------------------------------*/

/**
 * Kundenkonten = Nutzer mit der Rolle "customer" (ueber WooCommerce-Konto
 * angelegt, z.B. per "Kundenkonto anlegen" im Abo-Stack oder an der Kasse).
 * Bewusst getrennt von "subscriber" (bei uns ausschliesslich SliceWP-
 * Affiliates/Newsletter, keine echten Shop-Kunden) und von den
 * bestellbasierten Neukunden-Zahlen oben, die auf dem ERSTEN BEZAHLTEN
 * AUFTRAG basieren - ein Kundenkonto kann angelegt sein, ohne dass je
 * bestellt wurde.
 */
function sp_dashboard_get_customer_account_stats($start, $end) {
    $total = count(get_users(array('role' => 'customer', 'fields' => 'ID')));
    $new_query = new WP_User_Query(array(
        'role' => 'customer',
        'date_query' => array(array('after' => $start, 'before' => $end . ' 23:59:59', 'inclusive' => true)),
        'fields' => 'ID',
    ));
    return array('total' => $total, 'new_in_range' => (int) $new_query->get_total());
}

function sp_dashboard_get_recent_customer_accounts($limit = 10) {
    return get_users(array('role' => 'customer', 'orderby' => 'registered', 'order' => 'DESC', 'number' => $limit));
}

function sp_dashboard_render_tab_kunden($range) {
    $paid_orders = sp_dashboard_get_paid_orders($range['start'], $range['end']);
    $all_paid = sp_dashboard_get_all_paid_orders();
    $customer_stats = sp_dashboard_compute_customer_stats($all_paid, $range['start'], $range['end']);
    $top_coupons = sp_dashboard_get_top_coupons($paid_orders, 8);
    $total_orders_in_range = $customer_stats['orders_from_new'] + $customer_stats['orders_from_returning'];
    $account_stats = sp_dashboard_get_customer_account_stats($range['start'], $range['end']);
    $recent_accounts = sp_dashboard_get_recent_customer_accounts(10);
    ?>
    <div class="sp-dash-kpis">
      <?php
      sp_dashboard_kpi('Kundenkonten gesamt', (string) $account_stats['total'], 'dark', 'Alle Zeit');
      sp_dashboard_kpi('Neue Konten im Zeitraum', (string) $account_stats['new_in_range'], 'amber');
      sp_dashboard_kpi('Neukunden (erste Bestellung)', (string) $customer_stats['new_customers'], 'purple');
      sp_dashboard_kpi('Bestellungen von Neukunden', (string) $customer_stats['orders_from_new'], 'blue');
      sp_dashboard_kpi('Bestellungen von Stammkunden', (string) $customer_stats['orders_from_returning'], 'green');
      ?>
    </div>

    <div class="sp-dash-card">
      <h2>Letzte Kundenkonten</h2>
      <?php if (empty($recent_accounts)) : ?>
        <p class="sp-dash-empty">Noch keine Kundenkonten vorhanden.</p>
      <?php else : ?>
        <table class="sp-dash-table">
          <thead><tr><th>Name</th><th>E-Mail</th><th>Registriert am</th></tr></thead>
          <tbody>
            <?php foreach ($recent_accounts as $account) : ?>
              <tr>
                <td><a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $account->ID)); ?>"><?php echo esc_html($account->display_name ?: $account->user_login); ?></a></td>
                <td><?php echo esc_html($account->user_email); ?></td>
                <td><?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($account->user_registered))); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="sp-dash-card">
      <h2>Neu vs. wiederkehrend</h2>
      <?php if ($total_orders_in_range === 0): ?>
        <p class="sp-dash-empty">Keine bezahlten Bestellungen in diesem Zeitraum.</p>
      <?php else: ?>
        <?php
        $new_pct = round(($customer_stats['orders_from_new'] / $total_orders_in_range) * 100);
        $returning_pct = 100 - $new_pct;
        ?>
        <div style="display:flex;height:28px;border-radius:8px;overflow:hidden;">
          <div style="width:<?php echo esc_attr($new_pct); ?>%;background:#3B82F6;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;"><?php echo $new_pct >= 12 ? $new_pct . '%' : ''; ?></div>
          <div style="width:<?php echo esc_attr($returning_pct); ?>%;background:#0E9F6E;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;"><?php echo $returning_pct >= 12 ? $returning_pct . '%' : ''; ?></div>
        </div>
        <p style="font-size:12px;color:#787c81;margin-top:8px;"><span style="color:#3B82F6;font-weight:700;">■</span> Neukunden (<?php echo $new_pct; ?>%) &nbsp; <span style="color:#0E9F6E;font-weight:700;">■</span> Stammkunden (<?php echo $returning_pct; ?>%)</p>
      <?php endif; ?>
    </div>

    <div class="sp-dash-card">
      <h2>Meistgenutzte Rabattcodes</h2>
      <?php if (empty($top_coupons)): ?>
        <p class="sp-dash-empty">Keine Coupons in diesem Zeitraum verwendet.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Code</th><th>Verwendungen</th></tr></thead>
          <tbody>
            <?php foreach ($top_coupons as $code => $count): ?>
              <tr><td><?php echo esc_html($code); ?></td><td><?php echo (int) $count; ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <?php sp_dashboard_render_affiliate_section($range); ?>
    <?php
}

/* -----------------------------------------------------------------------
 * Tab: Produkte & Traffic
 * ---------------------------------------------------------------------*/

function sp_dashboard_render_tab_produkte($range) {
    $paid_orders = sp_dashboard_get_paid_orders($range['start'], $range['end']);
    $backorder_products = sp_dashboard_get_backorder_products();
    $low_revenue = sp_dashboard_get_low_revenue_products($paid_orders, 10);
    ?>
    <div class="sp-dash-grid-2">
      <div class="sp-dash-card">
        <h2>Produkte aktuell auf Vorbestellung</h2>
        <?php if (empty($backorder_products)): ?>
          <p class="sp-dash-empty">Aktuell kein Produkt auf Vorbestellung.</p>
        <?php else: ?>
          <table class="sp-dash-table">
            <thead><tr><th>Produkt</th></tr></thead>
            <tbody>
              <?php foreach ($backorder_products as $p): ?>
                <tr><td><a href="<?php echo esc_url(get_edit_post_link($p->get_id())); ?>"><?php echo esc_html($p->get_name()); ?></a></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="sp-dash-card">
        <h2>Umsatzschwache Produkte im Zeitraum</h2>
        <table class="sp-dash-table">
          <thead><tr><th>Produkt</th><th>Umsatz</th></tr></thead>
          <tbody>
            <?php foreach ($low_revenue as $p): ?>
              <tr><td><?php echo esc_html($p['name']); ?></td><td><?php echo sp_dashboard_money($p['revenue']); ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php sp_dashboard_render_traffic_section($range); ?>
    <?php
}

/* -----------------------------------------------------------------------
 * Affiliate (SliceWP) and traffic (InsightPress) sections
 * ---------------------------------------------------------------------*/

/**
 * "Counts as earned" = unpaid or paid (order went through and wasn't
 * cancelled/refunded); "open" = unpaid specifically (earned but not yet
 * paid out to the affiliate). Values verified against SliceWP's own
 * source (slicewp_get_affiliate_available_statuses(), commission status
 * handling) - amount/reference_amount are stored as text columns, hence
 * the CAST.
 */
function sp_dashboard_get_affiliate_stats($start, $end) {
    global $wpdb;
    $table_commissions = $wpdb->prefix . 'slicewp_commissions';
    $table_affiliates = $wpdb->prefix . 'slicewp_affiliates';

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT affiliate_id,
                COUNT(*) AS referral_count,
                SUM(CAST(amount AS DECIMAL(15,2))) AS commission_earned,
                SUM(CAST(reference_amount AS DECIMAL(15,2))) AS order_revenue
         FROM {$table_commissions}
         WHERE status IN ('unpaid','paid')
           AND date_created BETWEEN %s AND %s
         GROUP BY affiliate_id
         ORDER BY commission_earned DESC",
        $start . ' 00:00:00',
        $end . ' 23:59:59'
    ), ARRAY_A);

    $open_total = $wpdb->get_var("SELECT SUM(CAST(amount AS DECIMAL(15,2))) FROM {$table_commissions} WHERE status = 'unpaid'");

    $top_affiliates = [];
    foreach ((array) $rows as $row) {
        $affiliate_id = (int) $row['affiliate_id'];
        $user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$table_affiliates} WHERE id = %d", $affiliate_id));
        $name = 'Partner #' . $affiliate_id;
        if ($user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $full_name = trim($user->first_name . ' ' . $user->last_name);
                $name = $full_name !== '' ? $full_name : $user->display_name;
            }
        }
        $top_affiliates[] = [
            'affiliate_id'      => $affiliate_id,
            'name'              => $name,
            'referral_count'    => (int) $row['referral_count'],
            'commission_earned' => (float) $row['commission_earned'],
            'order_revenue'     => (float) $row['order_revenue'],
        ];
    }

    return [
        'top_affiliates'         => $top_affiliates,
        'open_commissions_total' => $open_total !== null ? (float) $open_total : 0.0,
    ];
}

function sp_dashboard_render_affiliate_section($range) {
    global $wpdb;
    $table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->prefix . 'slicewp_affiliates'));
    ?>
    <div class="sp-dash-card">
      <h2>Affiliate-Performance</h2>
      <?php if (!$table_exists): ?>
        <p class="sp-dash-empty">SliceWP ist nicht aktiv.</p>
      <?php else:
          $stats = sp_dashboard_get_affiliate_stats($range['start'], $range['end']);
      ?>
        <p style="margin:0 0 16px;font-size:13px;color:#4B5157;">Offene (noch nicht ausgezahlte) Provisionen insgesamt: <strong><?php echo sp_dashboard_money($stats['open_commissions_total']); ?></strong></p>
        <?php if (empty($stats['top_affiliates'])): ?>
          <p class="sp-dash-empty">Keine abgeschlossenen Provisionen in diesem Zeitraum.</p>
        <?php else: ?>
          <table class="sp-dash-table">
            <thead><tr><th>Partner</th><th>Empfehlungen</th><th>Bestellumsatz</th><th>Provision</th></tr></thead>
            <tbody>
              <?php foreach ($stats['top_affiliates'] as $a): ?>
                <tr>
                  <td><?php echo esc_html($a['name']); ?></td>
                  <td><?php echo (int) $a['referral_count']; ?></td>
                  <td><?php echo sp_dashboard_money($a['order_revenue']); ?></td>
                  <td><?php echo sp_dashboard_money($a['commission_earned']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php if ($table_exists && function_exists('sp_dashboard_compute_affiliate_funnel')):
        $funnel = sp_dashboard_compute_affiliate_funnel($range['start'], $range['end']);
        $commission_by_id = [];
        foreach ($stats['top_affiliates'] as $a) {
            $commission_by_id[$a['affiliate_id']] = $a['commission_earned'];
        }
        $funnel_totals = ['visits' => 0, 'carts' => 0, 'orders' => 0];
        foreach ($funnel as $row) {
            $funnel_totals['visits'] += $row['visits'];
            $funnel_totals['carts'] += $row['carts'];
            $funnel_totals['orders'] += $row['orders'];
        }
    ?>
    <div class="sp-dash-card">
      <h2>Warum kommen Besucher, bestellen aber nicht? (Partner-Funnel)</h2>
      <p style="margin:0 0 16px;font-size:13px;color:#4B5157;">
        Zeigt pro Partner, wie viele Besuche über den Link tatsächlich zu einem gefüllten Warenkorb und einer Bestellung führen.
        <strong>Hinweis:</strong> "Warenkorb gestartet" wird erst ab Einführung dieser Funktion (<?php echo esc_html(date_i18n('d.m.Y')); ?>) historisch erfasst &ndash; für Zeiträume davor steht dort 0, auch wenn tatsächlich Warenkörbe gefüllt wurden.
      </p>
      <?php if (empty($funnel)): ?>
        <p class="sp-dash-empty">Keine Partner-Besuche in diesem Zeitraum.</p>
      <?php else: ?>
        <table class="sp-dash-table">
          <thead><tr><th>Partner</th><th>Besuche</th><th>Warenkorb gestartet</th><th>Bestellungen</th><th>Conversion-Rate</th><th>Provision</th></tr></thead>
          <tbody>
            <?php foreach ($funnel as $row): ?>
              <tr>
                <td><?php echo esc_html($row['name']); ?></td>
                <td><?php echo (int) $row['visits']; ?></td>
                <td><?php echo (int) $row['carts']; ?></td>
                <td><?php echo (int) $row['orders']; ?></td>
                <td><?php echo $row['visits'] > 0 ? esc_html(round(($row['orders'] / $row['visits']) * 100, 1)) . '%' : '&ndash;'; ?></td>
                <td><?php echo sp_dashboard_money($commission_by_id[$row['affiliate_id']] ?? 0); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="font-weight:700;">
              <td>Gesamt</td>
              <td><?php echo (int) $funnel_totals['visits']; ?></td>
              <td><?php echo (int) $funnel_totals['carts']; ?></td>
              <td><?php echo (int) $funnel_totals['orders']; ?></td>
              <td><?php echo $funnel_totals['visits'] > 0 ? esc_html(round(($funnel_totals['orders'] / $funnel_totals['visits']) * 100, 1)) . '%' : '&ndash;'; ?></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php
}

/**
 * InsightPress stores UTM data as order meta (no dedicated table), keys
 * confirmed from its class-insightpress-utm-tracker.php:
 * _insightpress_utm_source / _utm_medium / _utm_campaign etc. This site
 * runs HPOS, so that meta lives in {prefix}wc_orders_meta against
 * {prefix}wc_orders, not post meta.
 */
function sp_dashboard_get_traffic_sources($start, $end) {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'wc_orders';
    $meta_table = $wpdb->prefix . 'wc_orders_meta';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT m.meta_value AS utm_source, COUNT(DISTINCT o.id) AS order_count, SUM(o.total_amount) AS revenue
         FROM {$orders_table} o
         JOIN {$meta_table} m ON m.order_id = o.id AND m.meta_key = '_insightpress_utm_source'
         WHERE o.type = 'shop_order'
           AND o.status IN ('wc-completed','wc-processing')
           AND o.date_created_gmt BETWEEN %s AND %s
         GROUP BY m.meta_value
         ORDER BY order_count DESC",
        $start . ' 00:00:00',
        $end . ' 23:59:59'
    ), ARRAY_A);
}

function sp_dashboard_render_traffic_section($range) {
    $hpos_enabled = get_option('woocommerce_custom_orders_table_enabled', 'no') === 'yes';
    ?>
    <div class="sp-dash-card">
      <h2>Traffic-Quellen (InsightPress)</h2>
      <?php if (!$hpos_enabled): ?>
        <p class="sp-dash-empty">Nicht verfügbar.</p>
      <?php else:
          $rows = sp_dashboard_get_traffic_sources($range['start'], $range['end']);
      ?>
        <?php if (empty($rows)): ?>
          <p class="sp-dash-empty">Noch keine Traffic-Quellen-Daten in diesem Zeitraum &ndash; die entstehen erst, sobald Kunden über einen echten Link mit UTM-Parametern (z. B. aus einer Kampagne) auf die Seite kommen.</p>
        <?php else: ?>
          <table class="sp-dash-table">
            <thead><tr><th>Quelle</th><th>Bestellungen</th><th>Umsatz</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr><td><?php echo esc_html($r['utm_source'] ?: '(unbekannt)'); ?></td><td><?php echo (int) $r['order_count']; ?></td><td><?php echo sp_dashboard_money($r['revenue']); ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php
}
