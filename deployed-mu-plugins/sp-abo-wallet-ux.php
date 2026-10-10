<?php
/**
 * Plugin Name: SP Abo/Guthaben Bedienung
 * Description: Vereinfacht Aufladen und Abo-Ablauf fuer Kunden (2026-10-09).
 *
 * 1) Aufladeseite: Betrag per ?betrag=XX vorausfuellbar (alle "Es fehlen X"-
 *    Buttons verlinken so), ehrlicher Text statt "Sofort nutzbar" (Ueberweisung
 *    braucht 1-2 Werktage, gutgeschrieben wird bei Zahlungsbestaetigung), und
 *    fuer Abo-Kunden der Hinweis, wie viel fuer die naechste Lieferung fehlt.
 * 2) "Mein Abo": der Aufladen-Button fuellt den Fehlbetrag automatisch vor.
 * 3) Kasse bei Abo-Erstbestellung: "2. Lieferung gleich mitbezahlen" - legt
 *    die Aufladung fuer die naechste Lieferung (mit 15 % Abo-Rabatt) mit in
 *    dieselbe Bestellung, damit der Kunde nur EINMAL ueberweisen muss.
 * 4) Gratisversand-Grenze (100 EUR) zaehlt nur echte Ware, keine Aufladung.
 * 5) Preiserhoehung eines Abo-Produkts -> Info-Mail an betroffene Abo-Kunden.
 */
if (!defined('ABSPATH')) {
    exit;
}

/** Aufladen-Link, optional mit vorausgefuelltem Betrag (aufgerundet, mind. 5 EUR). */
function sp_awu_topup_url($amount = null) {
    $id = function_exists('sp_wallet_get_topup_product_id') ? sp_wallet_get_topup_product_id() : 0;
    $url = $id ? get_permalink($id) : wc_get_account_endpoint_url('guthaben');
    if ($amount !== null && $amount > 0) {
        $url = add_query_arg('betrag', max(5, (int) ceil($amount)), $url);
    }
    return $url;
}

/** Fehlbetrag fuer die naechste Lieferung (0 wenn ausreichend / kein Abo). */
function sp_awu_missing_for_next($user_id) {
    if (!$user_id || !function_exists('sp_wallet_next_due_summary_for_customer')) {
        return array(0.0, null);
    }
    $next = sp_wallet_next_due_summary_for_customer($user_id);
    if (!$next) {
        return array(0.0, null);
    }
    return array(max(0.0, round($next['amount'] - sp_wallet_get_balance($user_id), 2)), $next);
}

/* ---------------------------------------------------------------------
 * 1) Aufladeseite
 * ------------------------------------------------------------------- */

add_action('wp_footer', function () {
    if (!function_exists('is_product') || !is_product() || !function_exists('sp_wallet_get_topup_product_id')) {
        return;
    }
    if (get_queried_object_id() !== sp_wallet_get_topup_product_id()) {
        return;
    }
    $prefill = isset($_GET['betrag']) ? max(5, (int) ceil((float) $_GET['betrag'])) : 0;
    list($missing, $next) = sp_awu_missing_for_next(get_current_user_id());
    $hint = null;
    if ($next) {
        $hint = array(
            'date' => date_i18n('d.m.Y', strtotime($next['date'])),
            'amount' => wp_strip_all_tags(wc_price($next['amount'])),
            'missing' => $missing,
            'missingLabel' => wp_strip_all_tags(wc_price($missing)),
        );
    }
    ?>
    <style>
      .sp-awu-hint{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.22);border-radius:10px;padding:12px 14px;margin:0 0 16px;font-size:12.5px;line-height:1.5;color:rgba(255,255,255,.9)}
      .sp-awu-hint strong{color:#fff}
      .sp-awu-hint button{margin-top:8px;background:#fff;color:#0D0F12;border:none;border-radius:8px;padding:7px 12px;font-family:'Sora',sans-serif;font-weight:700;font-size:12.5px;cursor:pointer}
      .sp-awu-timing{font-size:12px;color:rgba(255,255,255,.7);line-height:1.5;margin:-10px 0 18px;text-align:center}
    </style>
    <script>
    (function(){
      var input = document.getElementById('sp-wallet-amount');
      if (!input) { return; }
      var presets = document.querySelectorAll('.sp-wallet-preset');
      function setAmount(v){
        input.value = v;
        presets.forEach(function(x){ x.classList.toggle('is-active', x.getAttribute('data-amount') === String(v)); });
      }
      // Ehrlicher 3. Schritt: Gutschrift erst nach Zahlungseingang.
      var steps = document.querySelectorAll('.sp-wallet-step');
      if (steps[2]) { steps[2].lastChild.textContent = 'Gutschrift nach Zahlungseingang'; }
      var stepsBox = document.querySelector('.sp-wallet-steps');
      if (stepsBox) {
        var t = document.createElement('p');
        t.className = 'sp-awu-timing';
        t.textContent = 'Krypto: meist innerhalb weniger Minuten · Überweisung: sobald deine Zahlung bei uns eingegangen ist. Du bekommst eine Mail, sobald dein Guthaben gutgeschrieben ist.';
        stepsBox.parentNode.insertBefore(t, stepsBox.nextSibling);
      }
      var desc = document.querySelector('.sp-wallet-card-desc');
      if (desc) { desc.textContent = 'Lade Guthaben auf dein Konto, das du für dein Abo und zukünftige Bestellungen nutzen kannst. Bezahlung per Vorkasse oder Krypto.'; }

      var hint = <?php echo wp_json_encode($hint); ?>;
      var promo = document.querySelector('.sp-wallet-promo-banner') || document.querySelector('.sp-wallet-presets');
      if (hint && promo) {
        var box = document.createElement('div');
        box.className = 'sp-awu-hint';
        if (hint.missing > 0) {
          box.innerHTML = 'Deine nächste Abo-Lieferung am <strong>' + hint.date + '</strong> kostet <strong>' + hint.amount + '</strong> – dir fehlen noch <strong>' + hint.missingLabel + '</strong>.<br>';
          var b = document.createElement('button');
          b.type = 'button';
          b.textContent = 'Fehlbetrag übernehmen (' + Math.max(5, Math.ceil(hint.missing)) + ' €)';
          b.addEventListener('click', function(){ setAmount(Math.max(5, Math.ceil(hint.missing))); });
          box.appendChild(b);
        } else {
          box.innerHTML = '✓ Dein Guthaben reicht bereits für deine nächste Abo-Lieferung am <strong>' + hint.date + '</strong> (' + hint.amount + ').';
        }
        promo.parentNode.insertBefore(box, promo);
      }
      <?php if ($prefill) : ?>setAmount(<?php echo (int) $prefill; ?>);<?php endif; ?>
    })();
    </script>
    <?php
}, 60);

/* ---------------------------------------------------------------------
 * 2) "Mein Abo" / "Mein Guthaben": Aufladen-Buttons mit Fehlbetrag
 * ------------------------------------------------------------------- */

add_action('wp_footer', function () {
    if (!is_user_logged_in() || !function_exists('is_wc_endpoint_url') || !(is_wc_endpoint_url('abo') || is_wc_endpoint_url('guthaben'))) {
        return;
    }
    list($missing) = sp_awu_missing_for_next(get_current_user_id());
    if ($missing <= 0) {
        return;
    }
    $url = sp_awu_topup_url($missing);
    $label = 'Fehlbetrag aufladen (' . max(5, (int) ceil($missing)) . ' €) →';
    ?>
    <script>
    (function(){
      var url = <?php echo wp_json_encode($url); ?>;
      document.querySelectorAll('.sp-abo-dashboard-btn, .sp-wallet-topup-btn').forEach(function(a){
        a.href = url;
        a.textContent = <?php echo wp_json_encode($label); ?>;
      });
    })();
    </script>
    <?php
}, 60);

/* ---------------------------------------------------------------------
 * 3) Kasse: 2. Abo-Lieferung gleich mitbezahlen
 * ------------------------------------------------------------------- */

/** Preis der naechsten Abo-Lieferung fuer die Abo-Artikel im Warenkorb (mit Abo-Rabatt). */
function sp_awu_cart_next_delivery_amount() {
    if (!WC()->cart) {
        return 0.0;
    }
    $pct = defined('SP_ABO_RECURRING_DISCOUNT_PERCENT') ? SP_ABO_RECURRING_DISCOUNT_PERCENT : 15.0;
    $sum = 0.0;
    foreach (WC()->cart->get_cart() as $item) {
        if (empty($item['sp_abo_interval_days']) || empty($item['data'])) {
            continue;
        }
        $product = wc_get_product($item['variation_id'] ?: $item['product_id']);
        if ($product) {
            $sum += (float) $product->get_price() * (int) $item['quantity'] * (1 - $pct / 100);
        }
    }
    return round($sum, 2);
}

add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url('order-received') || !WC()->cart) {
        return;
    }
    if (!function_exists('sp_abo_cart_has_abo_item') || !sp_abo_cart_has_abo_item()) {
        return;
    }
    if (function_exists('sp_wallet_cart_contains_topup_product') && sp_wallet_cart_contains_topup_product()) {
        return;
    }
    $amount = sp_awu_cart_next_delivery_amount();
    if ($amount <= 0) {
        return;
    }
    $topup = max(5, (int) ceil($amount));
    if (is_user_logged_in()) {
        $topup = max(0, (int) ceil($amount - sp_wallet_get_balance(get_current_user_id())));
        if ($topup <= 0) {
            return; // vorhandenes Guthaben deckt die 2. Lieferung bereits
        }
        $topup = max(5, $topup);
    }
    ?>
    <style>
      .sp-awu-co{display:flex;gap:12px;align-items:flex-start;background:#FFF8F4;border:1px solid #FFD9C2;border-radius:14px;padding:16px 18px;margin:0 0 20px;font-family:'Sora',sans-serif}
      .sp-awu-co svg{flex-shrink:0;width:20px;height:20px;color:#E5342B;margin-top:2px}
      .sp-awu-co-t{font-size:14px;font-weight:800;color:#0D0F12;margin-bottom:3px}
      .sp-awu-co-x{font-size:12.5px;color:#4B5157;line-height:1.5}
      .sp-awu-co button{margin-top:10px;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#fff;border:none;border-radius:999px;padding:10px 18px;font-family:'Sora',sans-serif;font-weight:700;font-size:13px;cursor:pointer}
      .sp-awu-co button:disabled{opacity:.6;cursor:default}
    </style>
    <script>
    (function(){
      var placed = false;
      function place(){
        if (placed) { return; }
        var target = document.querySelector('.wp-block-woocommerce-checkout') || document.querySelector('form.woocommerce-checkout');
        if (!target) { return; }
        placed = true;
        var box = document.createElement('div');
        box.className = 'sp-awu-co';
        box.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>'
          + '<div><div class="sp-awu-co-t">Tipp: 2. Lieferung gleich mitbezahlen</div>'
          + '<div class="sp-awu-co-x">Ab der 2. Lieferung wird automatisch von deinem Guthaben abgebucht (15&nbsp;% günstiger). Lade jetzt <strong><?php echo (int) $topup; ?>&nbsp;€</strong> mit auf – dann reicht <strong>eine</strong> Überweisung und deine nächste Lieferung ist schon gedeckt.</div>'
          + '<button type="button">+ <?php echo (int) $topup; ?> € Guthaben mit aufladen</button></div>';
        target.parentNode.insertBefore(box, target);
        var btn = box.querySelector('button');
        btn.addEventListener('click', function(){
          btn.disabled = true; btn.textContent = 'Wird hinzugefügt…';
          var xhr = new XMLHttpRequest();
          xhr.open('POST', <?php echo wp_json_encode(\WC_AJAX::get_endpoint('add_to_cart')); ?>, true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          xhr.onload = function(){ window.location.reload(); };
          xhr.onerror = function(){ btn.disabled = false; btn.textContent = 'Erneut versuchen'; };
          xhr.send('product_id=<?php echo (int) sp_wallet_get_topup_product_id(); ?>&quantity=1&sp_wallet_amount=<?php echo (int) $topup; ?>.00');
        });
      }
      place();
      if (!placed) { var n = 0; var iv = setInterval(function(){ place(); if (placed || ++n > 40) { clearInterval(iv); } }, 250); }
    })();
    </script>
    <?php
}, 60);

/* ---------------------------------------------------------------------
 * 4) Gratisversand-Grenze ohne Guthaben-Aufladung
 * ------------------------------------------------------------------- */

add_filter('woocommerce_shipping_free_shipping_is_available', function ($is_available, $package, $method) {
    if (!$is_available || !function_exists('sp_wallet_get_topup_product_id') || !WC()->cart) {
        return $is_available;
    }
    $topup_id = (int) sp_wallet_get_topup_product_id();
    $topup_total = 0.0;
    foreach (WC()->cart->get_cart() as $item) {
        if ((int) $item['product_id'] === $topup_id) {
            $topup_total += (float) $item['line_subtotal'] + (float) $item['line_subtotal_tax'];
        }
    }
    if ($topup_total <= 0) {
        return $is_available;
    }
    $min = (float) $method->get_option('min_amount');
    if ($min <= 0) {
        return $is_available;
    }
    $goods = (float) WC()->cart->get_displayed_subtotal() - $topup_total;
    return $goods >= $min;
}, 20, 3);

/* ---------------------------------------------------------------------
 * 5) Preiserhoehung eines Abo-Produkts -> Info an betroffene Abo-Kunden
 * ------------------------------------------------------------------- */

add_action('woocommerce_before_product_object_save', function ($product) {
    $changes = $product->get_changes();
    if (!$product->get_id() || !array_key_exists('price', $changes)) {
        return;
    }
    $old = wc_get_product($product->get_id());
    if ($old) {
        $GLOBALS['sp_awu_old_prices'][$product->get_id()] = (float) $old->get_price();
    }
}, 10, 1);

add_action('woocommerce_after_product_object_save', function ($product) {
    $id = $product->get_id();
    if (!isset($GLOBALS['sp_awu_old_prices'][$id]) || !function_exists('sp_abo_table_name')) {
        return;
    }
    $old_price = $GLOBALS['sp_awu_old_prices'][$id];
    unset($GLOBALS['sp_awu_old_prices'][$id]);
    $new_price = (float) $product->get_price();
    if ($new_price <= $old_price + 0.009) {
        return; // nur bei Erhoehung informieren
    }
    global $wpdb;
    $subs = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . sp_abo_table_name() . " WHERE status IN ('active','paused') AND (variation_id = %d OR (variation_id = 0 AND product_id = %d))",
        $id, $id
    ));
    if (!$subs || !function_exists('sp_abo_send_branded_email')) {
        return;
    }
    $pct = defined('SP_ABO_RECURRING_DISCOUNT_PERCENT') ? SP_ABO_RECURRING_DISCOUNT_PERCENT : 15.0;
    $by_user = array();
    foreach ($subs as $sub) {
        $by_user[(int) $sub->user_id][] = $sub;
    }
    foreach ($by_user as $user_id => $user_subs) {
        $to = sp_abo_email_customer_email($user_id);
        if (!$to) {
            continue;
        }
        ob_start();
        ?>
        <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;">Kurze Info: Der Preis eines Produkts in deinem Abo hat sich geändert. Ab deiner nächsten Lieferung gilt der neue Abo-Preis (weiterhin mit 15&nbsp;% Abo-Rabatt).</p>
        <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
          <?php foreach ($user_subs as $i => $sub) : $q = (int) $sub->quantity; ?>
            <div style="<?php echo $i < count($user_subs) - 1 ? 'border-bottom:1px solid #F2F3F4;padding-bottom:10px;margin-bottom:10px;' : ''; ?>">
              <p style="margin:0 0 4px;font-size:15px;font-weight:700;color:#0D0F12;"><?php echo esc_html($product->get_name()); ?> &times; <?php echo $q; ?></p>
              <p style="margin:0;font-size:13px;color:#4B5157;">Bisher <?php echo wp_kses_post(wc_price(round($old_price * $q * (1 - $pct / 100), 2))); ?> &rarr; neu <strong style="color:#0D0F12;"><?php echo wp_kses_post(wc_price(round($new_price * $q * (1 - $pct / 100), 2))); ?></strong> pro Lieferung</p>
            </div>
          <?php endforeach; ?>
        </div>
        <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Du musst nichts tun. Wenn du nicht weitermachen möchtest, kannst du dein Abo jederzeit in deinem Konto pausieren oder kündigen.</p>
        <?php echo sp_abo_email_account_link(); ?>
        <?php echo sp_abo_email_support_line(); ?>
        <?php
        sp_abo_send_branded_email($to, 'Preisänderung in deinem Abo', 'Preisänderung in deinem Abo', ob_get_clean());
    }
}, 10, 1);
