<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core order-tracking system: a DHL tracking number field per order (manual,
 * admin-side). Setting it marks the order "Completed" (WC_Order::update_status()),
 * which natively triggers WooCommerce's own "Bestellung abgeschlossen" customer
 * email (customer-completed-order.php, sp-order-emails.php) - that template
 * already shows the tracking number + DHL link automatically when present, so
 * there's exactly one shipped-notice email design instead of two near-duplicate
 * ones. (Previously this fired its own separate hand-rolled "your order has
 * shipped" email; merged per user request since the "abgeschlossen" design was
 * preferred and the content was redundant anyway. Confirmed no conflict with
 * SlicewP: its WooCommerce integration already accepts pending commissions on
 * the earlier "processing" transition, so completing the order later doesn't
 * re-trigger or double anything there.)
 *
 * Also renders a shared 2-step progress box (Bestellung erhalten -> Versandt -
 * no "Verpackt" step, deliberately dropped: WooCommerce has no native "packed"
 * status and the user decided a manual extra click per order isn't worth it),
 * shown both in the WooCommerce My Account order view and on the public
 * /bestellstatus/ lookup page (sp-order-status-page.php). Bulk CSV import
 * lives in sp-order-tracking-bulk.php and calls sp_tracking_set_number() below
 * so both paths share the exact same save + status-change logic.
 *
 * No third-party tracking plugin and no DHL API - the tracking link just
 * deep-links to DHL's own public tracking page with the number pre-filled.
 */

define('SP_TRACKING_META_KEY', '_sp_tracking_number');

/**
 * Sets (or clears) an order's tracking number, marking the order "Completed"
 * (which triggers WooCommerce's own customer-completed-order email) only when
 * the value actually changes to something new and non-empty - re-running the
 * same CSV twice, or re-saving the same number in admin, never re-triggers it.
 * Shared by both the single-order admin field and the bulk CSV importer so
 * there's exactly one place this logic lives.
 *
 * @return string One of "unchanged", "cleared", "set" - lets callers report
 *   a meaningful summary without duplicating the comparison logic.
 */
function sp_tracking_set_number($order, $tracking_number, $send_email = true) {
    $tracking_number = trim((string) $tracking_number);
    $old = trim((string) $order->get_meta(SP_TRACKING_META_KEY));

    if ($tracking_number === '') {
        if ($old !== '') {
            $order->delete_meta_data(SP_TRACKING_META_KEY);
            $order->save();
        }
        return 'cleared';
    }

    if ($tracking_number === $old) {
        return 'unchanged';
    }

    $order->update_meta_data(SP_TRACKING_META_KEY, $tracking_number);
    $order->save();

    if ($send_email) {
        sp_tracking_mark_completed($order);
    }

    return 'set';
}

function sp_tracking_dhl_url($tracking_number) {
    return 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?piececode=' . rawurlencode($tracking_number);
}

/**
 * Marks the order "Completed" - the tracking number meta is already saved by
 * the time this runs, so WooCommerce's own customer-completed-order email
 * (sp-order-emails.php) picks it up and shows the tracking box automatically.
 * A no-op (WC won't re-fire the status email) if the order is already
 * "completed", matching the previous system's "don't re-send" behavior.
 */
function sp_tracking_mark_completed($order) {
    if ($order->get_status() === 'completed') {
        return;
    }
    $order->update_status('completed', 'Sendungsnummer hinterlegt – automatisch als abgeschlossen markiert. ');
}

/**
 * Admin-side tracking number field, right in WooCommerce's own Order Data
 * metabox - works for both HPOS and legacy post-based orders since it's the
 * same shared admin template either way.
 */
add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    $tracking_number = $order->get_meta(SP_TRACKING_META_KEY);
    ?>
    <p class="form-field form-field-wide" style="margin-top:16px;">
      <label for="sp_tracking_number">DHL-Sendungsnummer</label>
      <input
        type="text"
        id="sp_tracking_number"
        name="sp_tracking_number"
        value="<?php echo esc_attr($tracking_number); ?>"
        style="width:100%"
        placeholder="z. B. 00340434123456789012"
      />
      <?php wp_nonce_field('sp_save_tracking_number', 'sp_tracking_number_nonce'); ?>
    </p>
    <?php
});

/**
 * Priority 50 is deliberate: WooCommerce's own order-data metabox save
 * (WC_Meta_Box_Order_Data::save(), hooked here at priority 40) re-applies
 * whatever status was in the admin screen's dropdown at page-load time. At
 * the default priority 10 this callback used to run BEFORE that, so setting
 * the order to "completed" here got silently overwritten back to the old
 * status a moment later in the same save request - the order still received
 * its "completed" email correctly, but the admin-visible status reverted.
 * Running after WC's own save (which is what re-fetching the order below
 * picks up) makes this callback's status change the one that actually sticks.
 */
add_action('woocommerce_process_shop_order_meta', function ($order_id) {
    if (!isset($_POST['sp_tracking_number_nonce']) || !wp_verify_nonce($_POST['sp_tracking_number_nonce'], 'sp_save_tracking_number')) {
        return;
    }
    if (!current_user_can('edit_shop_orders')) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    $submitted = isset($_POST['sp_tracking_number']) ? sanitize_text_field(wp_unslash($_POST['sp_tracking_number'])) : '';
    sp_tracking_set_number($order, $submitted);
}, 50);

/**
 * Shared 2-step progress renderer, used both in My Account order view and
 * on the public /bestellstatus/ lookup page. Deliberately only 2 steps -
 * "Bestellung erhalten" and "Versandt" - since those are the only two
 * moments this system has a real signal for.
 */
function sp_tracking_render_progress($order) {
    $tracking_number = $order->get_meta(SP_TRACKING_META_KEY);
    $shipped = !empty($tracking_number);
    ob_start();
    ?>
    <div class="sp-track-box">
      <div class="sp-track-steps">
        <div class="sp-track-step is-done">
          <span class="sp-track-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
          <span class="sp-track-label">Bestellung erhalten</span>
        </div>
        <div class="sp-track-line <?php echo $shipped ? 'is-done' : ''; ?>"></div>
        <div class="sp-track-step <?php echo $shipped ? 'is-done' : 'is-pending'; ?>">
          <span class="sp-track-dot"><?php if ($shipped): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><?php else: ?><span class="sp-track-dot-pulse"></span><?php endif; ?></span>
          <span class="sp-track-label">Versandt</span>
        </div>
      </div>
      <?php if ($shipped): ?>
        <div class="sp-track-shipped">
          <div class="sp-track-shipped-num">
            <span class="sp-track-shipped-k">Sendungsnummer</span>
            <span class="sp-track-shipped-v"><?php echo esc_html($tracking_number); ?></span>
          </div>
          <a class="sp-track-btn" href="<?php echo esc_url(sp_tracking_dhl_url($tracking_number)); ?>" target="_blank" rel="noopener">
            Bei DHL verfolgen
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
      <?php else: ?>
        <?php $st = $order->get_status(); ?>
        <?php if (in_array($st, ['on-hold', 'pending'], true)): ?>
        <p class="sp-track-pending-note">Wir warten noch auf deine Zahlung. Sobald sie eingegangen ist, bereiten wir alles vor – die Sendungsnummer bekommst du dann per E-Mail.</p>
        <?php elseif (in_array($st, ['cancelled', 'failed', 'refunded'], true)): ?>
        <p class="sp-track-pending-note">Diese Bestellung wurde storniert und wird nicht versendet.</p>
        <?php else: ?>
        <p class="sp-track-pending-note">Deine Bestellung wird vorbereitet. Sobald sie versandt ist, bekommst du eine E-Mail mit der Sendungsnummer.</p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_action('wp_head', function () {
    ?>
    <style id="sp-track-style">
      .sp-track-box{font-family:'Sora',sans-serif;border:1px solid #DCDEE0;border-radius:14px;padding:20px 22px;background:#F9FAFA;margin:16px 0}
      .sp-track-steps{display:flex;align-items:center;margin-bottom:16px}
      .sp-track-step{display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0}
      .sp-track-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#DCDEE0;color:#FFFFFF;flex-shrink:0}
      .sp-track-step.is-done .sp-track-dot{background:#1F7A4D}
      .sp-track-step.is-pending .sp-track-dot{background:#FFFFFF;border:2px solid #DCDEE0}
      .sp-track-dot svg{width:14px;height:14px}
      .sp-track-dot-pulse{width:8px;height:8px;border-radius:50%;background:#A8B0B9}
      .sp-track-label{font-size:11.5px;font-weight:700;color:#4B5157;text-align:center;white-space:nowrap}
      .sp-track-step.is-done .sp-track-label{color:#0D0F12}
      .sp-track-line{flex:1;height:2px;background:#DCDEE0;margin:0 8px 24px}
      .sp-track-line.is-done{background:#1F7A4D}
      .sp-track-shipped{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;padding-top:14px;border-top:1px solid #DCDEE0}
      .sp-track-shipped-k{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#4B5157;margin-bottom:3px}
      .sp-track-shipped-v{display:block;font-size:15px;font-weight:700;color:#0D0F12;letter-spacing:.01em}
      .sp-track-btn{display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF!important;padding:11px 18px;border-radius:9px;font-size:13px;font-weight:700;text-decoration:none;flex-shrink:0}
      .sp-track-btn svg{width:14px;height:14px}
      .sp-track-pending-note{margin:0;padding-top:14px;border-top:1px solid #DCDEE0;font-size:13px;color:#4B5157;line-height:1.6}
    </style>
    <?php
}, 25);

/**
 * My Account: show the tracking box under each order's line-items table.
 */
add_action('woocommerce_order_details_after_order_table', function ($order) {
    if (is_admin()) {
        return;
    }
    echo sp_tracking_render_progress($order);
});

/**
 * Admin safety net: on the Orders list screen, flag orders that have been
 * "Processing" for more than 2 days without a tracking number yet - easy to
 * lose track of a single order when packing/shipping is done by hand.
 */
add_action('admin_notices', function () {
    $screen = get_current_screen();
    if (!$screen || (strpos($screen->id, 'woocommerce_page_wc-orders') === false && $screen->id !== 'edit-shop_order')) {
        return;
    }

    $orders = wc_get_orders([
        'status' => 'processing',
        'date_created' => '<' . (time() - 2 * DAY_IN_SECONDS),
        'limit' => -1,
        'return' => 'ids',
    ]);

    $overdue = [];
    foreach ($orders as $order_id) {
        $order = wc_get_order($order_id);
        if ($order && empty($order->get_meta(SP_TRACKING_META_KEY))) {
            $overdue[] = $order;
        }
    }

    if (empty($overdue)) {
        return;
    }

    $count = count($overdue);
    ?>
    <div class="notice notice-warning">
      <p>
        <strong>⚠️ <?php echo (int) $count; ?> Bestellung<?php echo $count === 1 ? '' : 'en'; ?></strong>
        seit über 2 Tagen "In Bearbeitung" ohne Sendungsnummer:
        <?php foreach ($overdue as $i => $order) : ?>
          <a href="<?php echo esc_url($order->get_edit_order_url()); ?>">#<?php echo esc_html($order->get_order_number()); ?></a><?php echo $i < count($overdue) - 1 ? ', ' : ''; ?>
        <?php endforeach; ?>
      </p>
    </div>
    <?php
});
