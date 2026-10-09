<?php
/**
 * Site-wide add-to-cart slide-in drawer with a gift-progress bar, all pages.
 */
if (!defined('ABSPATH')) {
    exit;
}

// Suppress WooCommerce's default "X was added to your cart" notice everywhere -
// the slide-in cart drawer below already gives that feedback.
add_filter('wc_add_to_cart_message_html', '__return_false');

add_action('wp_footer', function () {
    ?>
    <div id="sp-cd-overlay"></div>
    <div id="sp-cart-drawer" role="dialog" aria-label="Warenkorb">
      <div class="sp-cd-header">
        <span class="sp-cd-title">Warenkorb</span>
        <button type="button" id="sp-cd-close" aria-label="Schließen">&times;</button>
      </div>
      <div id="sp-cd-scroll">
        <div id="sp-rb"></div>
        <div class="sp-cd-items" id="sp-cd-items"></div>
      </div>
      <div class="sp-cd-footer">
        <div class="sp-cd-coupon" id="sp-cd-coupon">
          <button type="button" id="sp-cd-coupon-toggle" class="sp-cd-coupon-toggle">Rabattcode?</button>
          <div class="sp-cd-coupon-form" id="sp-cd-coupon-form" style="display:none">
            <input type="text" id="sp-cd-coupon-input" placeholder="Rabattcode eingeben" autocomplete="off">
            <button type="button" id="sp-cd-coupon-apply">Einl&ouml;sen</button>
          </div>
          <div class="sp-cd-coupon-error" id="sp-cd-coupon-error" style="display:none"></div>
          <div class="sp-cd-coupon-applied" id="sp-cd-coupon-applied" style="display:none"></div>
        </div>
        <div class="sp-cd-subtotal" id="sp-cd-subtotal-row">Zwischensumme <span id="sp-cd-subtotal-val">0,00 €</span></div>
        <div class="sp-cd-total-savings" id="sp-cd-total-savings-row" style="display:none">Du sparst <span id="sp-cd-total-savings-val">0,00 €</span></div>
        <div class="sp-cd-dhl" id="sp-cd-dhl">
          <span>DHL PREMIUM &middot; <span class="sp-cd-dhl-shine">2 Tage Lieferung</span></span>
        </div>
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" id="sp-cd-checkout-btn" class="sp-cd-btn sp-cd-btn-solid">Zur Kasse</a>
        <button type="button" id="sp-cd-continue" class="sp-cd-btn sp-cd-btn-outline">Weiter einkaufen</button>
      </div>
    </div>

    <?php
    /**
     * Den Trigger-Button zusaetzlich zu Warenkorb/Kasse auch auf dem
     * Abo-Stack-Konfigurator und in "Mein Abo" ausblenden (der Drawer selbst
     * bleibt ueberall gerendert, da der Wizard ihn in sp-abo-picker.php
     * weiterhin programmatisch oeffnen koennen muss) - beide Seiten haben
     * eigene fixierte Leisten am unteren Bildschirmrand, auf Mobilgeraeten
     * lag der FAB sonst direkt ueber deren Buttons (u.a. dem finalen
     * "Jetzt Abo bestellen").
     */
    $sp_is_mein_abo = is_account_page() && get_query_var('abo', null) !== null;
    if (!is_cart() && !is_checkout() && !is_page('abo-stack') && !$sp_is_mein_abo): ?>
    <button type="button" id="sp-cart-fab" aria-label="Warenkorb öffnen">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <span id="sp-cart-fab-badge">0</span>
    </button>
    <?php endif; ?>

    <style id="sp-cart-gifts-style">
      #sp-cd-overlay { position: fixed; inset: 0; background: rgba(13, 15, 18, .45); z-index: 199998; opacity: 0; pointer-events: none; transition: opacity .3s ease; }
      #sp-cd-overlay.open { opacity: 1; pointer-events: auto; }
      #sp-cart-drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 400px; max-width: 92vw; background: #FFFFFF; border-left: 1px solid #DCDEE0; box-shadow: -12px 0 40px rgba(13, 15, 18, .18); z-index: 199999; display: flex; flex-direction: column; transform: translateX(100%); transition: transform .35s cubic-bezier(.34, 1, .4, 1); font-family: 'Sora', sans-serif; }
      #sp-cart-drawer.open { transform: translateX(0); }
      .sp-cd-header { display: flex; align-items: center; justify-content: space-between; padding: 20px 22px; border-bottom: 1px solid #DCDEE0; flex-shrink: 0; }
      .sp-cd-title { font-size: 18px; font-weight: 600; color: #0D0F12; }
      #sp-cd-close { background: none; border: none; font-size: 26px; line-height: 1; color: #4B5157; cursor: pointer; padding: 4px; }
      #sp-cd-close:hover { color: #0D0F12; }
      #sp-cd-scroll { flex: 1 1 auto; overflow-y: auto; }
      .sp-cd-items { padding: 8px 22px; }
      .sp-cd-item { display: flex; align-items: flex-start; gap: 12px; padding: 14px 0; border-bottom: 1px solid #F2F3F4; }
      .sp-cd-item img { width: 52px; height: 52px; border-radius: 10px; object-fit: cover; background: #F2F3F4; flex-shrink: 0; }
      .sp-cd-item-bundle { display: inline-block; font-size: 10.5px; font-weight: 700; color: #FFFFFF; background: #0D0F12; border-radius: 999px; padding: 2px 8px; margin: 0 0 5px; letter-spacing: .01em; }
      .sp-cd-item-gift-badge { background: linear-gradient(135deg, #C7920D 0%, #E3B23D 100%); }
      .sp-cd-item-abo-badge { background: linear-gradient(135deg, #FF6B35 0%, #E5342B 100%); margin-right: 5px; }
      .sp-cd-item-qty-inline { font-weight: 700; color: #4B5157; font-size: 12.5px; }
      .sp-cd-item-abo-edit { display: inline-block; margin-top: 4px; font-size: 11.5px; font-weight: 600; color: #4B5157; text-decoration: underline; text-underline-offset: 2px; }
      .sp-cd-item-price-free { color: #2f7d4f; }
      .sp-cd-item-name { font-size: 14px; font-weight: 600; color: #0D0F12; margin: 0 0 4px; }
      .sp-cd-item-vol { font-size: 12.5px; color: #4B5157; margin: 0 0 6px; }
      .sp-cd-item-price { font-size: 14px; font-weight: 700; color: #0D0F12; white-space: nowrap; }
      .sp-cd-item-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
      .sp-cd-item-remove { background: none; border: none; padding: 2px; margin: 0; cursor: pointer; color: #A8B0B9; display: flex; align-items: center; justify-content: center; }
      .sp-cd-item-remove:hover { color: #0D0F12; }
      .sp-cd-suggest { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 11px 12px; margin: 10px 0; border: 1px dashed #DCDEE0; border-radius: 10px; background: #F9FAFA; cursor: pointer; transition: border-color .15s, background .15s; }
      .sp-cd-suggest:hover, .sp-cd-suggest:focus-visible { border-color: #A8B0B9; background: #F2F3F4; }
      .sp-cd-suggest:active { background: #EAEBEC; }
      .sp-cd-items.sp-cd-busy .sp-cd-suggest { pointer-events: none; opacity: .6; }
      .sp-cd-suggest-text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
      .sp-cd-suggest-name { font-size: 13px; font-weight: 600; color: #0D0F12; }
      .sp-cd-suggest-hint { font-size: 11.5px; color: #4B5157; }
      .sp-cd-suggest-btn { flex-shrink: 0; display: flex; flex-direction: column; align-items: center; gap: 1px; font-family: 'Sora', sans-serif; color: #0D0F12; background: #FFFFFF; border: 1px solid #DCDEE0; border-radius: 12px; padding: 6px 13px; white-space: nowrap; pointer-events: none; }
      .sp-cd-suggest-btn-label { font-size: 12px; font-weight: 700; }
      .sp-cd-suggest-btn-price { font-size: 10.5px; color: #4B5157; }
      .sp-cd-item-remove svg { width: 16px; height: 16px; display: block; }
      .sp-cd-item-save { font-size: 11px; font-weight: 700; color: #FFFFFF; background: #2f7d4f; border-radius: 20px; padding: 3px 8px; white-space: nowrap; }
      .sp-cd-items.sp-cd-busy .sp-cd-qty-btn,
      .sp-cd-items.sp-cd-busy .sp-cd-item-remove { pointer-events: none; }
      .sp-cd-empty { text-align: center; color: #4B5157; font-size: 14px; padding: 40px 0; }
      .sp-cd-empty p { margin: 0 0 18px; font-size: 15px; color: #0D0F12; font-weight: 600; }
      .sp-cd-empty .sp-cd-btn { display: inline-block; max-width: 220px; margin: 0 auto; }
      #sp-cd-subtotal-row.is-empty, #sp-cd-checkout-btn.is-hidden, #sp-cd-dhl.is-hidden { display: none; }
      .sp-cd-total-savings { display: flex; align-items: center; justify-content: space-between; font-size: 15px; font-weight: 600; color: #2f7d4f; margin: 0 0 8px; }
      .sp-cd-dhl { display: flex; align-items: center; gap: 7px; margin: 0 0 8px; color: #4B5157; font-family: 'Sora', sans-serif; font-size: 12px; font-weight: 600; }
      .sp-cd-dhl-iconwrap { display: inline-flex; align-items: center; justify-content: center; width: 19px; height: 19px; background: #FFCC00; border-radius: 5px; flex-shrink: 0; }
      .sp-cd-dhl-iconwrap svg { width: 11px; height: 11px; display: block; }
      .sp-cd-dhl-shine { font-weight: 700; }
      #sp-cd-continue { width: 100%; box-sizing: border-box; cursor: pointer; font-family: 'Sora', sans-serif; }
      .sp-cd-qty { display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0; }
      .sp-cd-qty-btn { width: 22px; height: 22px; border-radius: 6px; border: 1px solid #DCDEE0; background: #F2F3F4; color: #0D0F12; font-size: 14px; font-weight: 700; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; flex-shrink: 0; }
      .sp-cd-qty-btn:hover { border-color: #A8B0B9; background: #FFFFFF; }
      .sp-cd-qty-val { min-width: 16px; text-align: center; font-size: 13px; font-weight: 700; color: #0D0F12; }
      .sp-cd-footer { flex-shrink: 0; padding: 14px 22px 16px; border-top: 1px solid #DCDEE0; }
      .sp-cd-subtotal { display: flex; justify-content: space-between; font-size: 15px; font-weight: 600; color: #0D0F12; margin-bottom: 8px; }
      .sp-cd-coupon { margin-bottom: 8px; }
      .sp-cd-coupon-toggle { background: none; border: none; padding: 0; margin: 0 0 4px; font-family: 'Sora', sans-serif; font-size: 12.5px; font-weight: 600; color: #4B5157; text-decoration: underline; text-underline-offset: 2px; cursor: pointer; }
      .sp-cd-coupon-toggle:hover { color: #0D0F12; }
      .sp-cd-coupon-form { display: flex; gap: 8px; margin-bottom: 4px; }
      .sp-cd-coupon-form input { flex: 1; min-width: 0; height: 38px; padding: 0 12px; border: 1px solid #DCDEE0; border-radius: 8px; font-family: 'Sora', sans-serif; font-size: 16px; color: #0D0F12; }
      .sp-cd-coupon-form input:focus { outline: none; border-color: #A8B0B9; }
      .sp-cd-coupon-form button { flex-shrink: 0; height: 38px; padding: 0 14px; border: 1px solid #0D0F12; background: #0D0F12; color: #FFFFFF; border-radius: 8px; font-family: 'Sora', sans-serif; font-size: 12.5px; font-weight: 700; cursor: pointer; }
      .sp-cd-coupon-form button:disabled { opacity: .55; cursor: default; }
      .sp-cd-coupon-error { font-size: 12px; color: #B3261E; margin-bottom: 4px; }
      .sp-cd-coupon-applied { display: flex; flex-direction: column; gap: 6px; }
      .sp-cd-coupon-applied-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; background: #EAF3ED; border: 1px solid #cfe4d6; border-radius: 8px; padding: 8px 10px; font-size: 12.5px; }
      .sp-cd-coupon-applied-code { font-weight: 700; color: #1F7A4D; }
      .sp-cd-coupon-remove { background: none; border: none; padding: 2px; margin: 0; cursor: pointer; color: #4B5157; font-size: 13px; line-height: 1; }
      .sp-cd-coupon-remove:hover { color: #0D0F12; }
      .sp-cd-btn { display: block; text-align: center; text-decoration: none !important; padding: 12px 18px; border-radius: 10px; font-size: 14.5px; font-weight: 600; margin-bottom: 8px; transition: transform .2s; }
      .sp-cd-btn:last-child { margin-bottom: 0; }
      .sp-cd-btn-outline { background: #FFFFFF; color: #0D0F12 !important; border: 1px solid #DCDEE0; }
      .sp-cd-btn-outline:hover { border-color: #A8B0B9; }
      .sp-cd-btn-solid { background: linear-gradient(135deg, #0D0F12 0%, #2A2E33 100%); color: #FFFFFF !important; border: none; position: relative; overflow: hidden; }
      .sp-cd-btn-solid:hover { transform: translateY(-1px); }
      .sp-cd-btn-solid::after { content: ''; position: absolute; top: 0; left: -75%; width: 50%; height: 100%; background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.4) 50%, transparent 70%); transform: skewX(-20deg); animation: sp-cd-shine 3.2s ease-in-out infinite; pointer-events: none; }
      @keyframes sp-cd-shine { 0%, 55% { left: -75%; } 100% { left: 150%; } }

      #sp-rb { padding: 16px 22px; border-bottom: 1px solid #DCDEE0; background: #F9FAFA; }
      .sp-rb-title { font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #4B5157; margin: 0 0 10px; }
      .sp-rb-list { display: flex; flex-direction: column; gap: 8px; }
      .sp-rb-box { position: relative; overflow: hidden; border: 1px solid #DCDEE0; border-radius: 10px; background: #FFFFFF; min-height: 44px; transition: border-color .3s, background .3s, opacity .3s, box-shadow .3s; }
      .sp-rb-fill { position: absolute; top: 0; left: 0; bottom: 0; width: 0; z-index: 1; background: linear-gradient(90deg, rgba(168,176,185,.18), rgba(168,176,185,.32)); transition: width .55s cubic-bezier(.34,1.2,.4,1); }
      .sp-rb-content { position: relative; z-index: 3; display: flex; align-items: center; gap: 9px; padding: 8px 11px; }
      .sp-rb-ic { width: 24px; height: 24px; border-radius: 50%; flex: none; display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 13px; background: #F2F3F4; border: 2px solid #DCDEE0; transition: all .3s; }
      .sp-rb-ic img { width: 100%; height: 100%; object-fit: cover; }
      .sp-rb-amt { font-weight: 800; font-size: 12.5px; min-width: 44px; color: #0D0F12; font-variant-numeric: tabular-nums; }
      .sp-rb-lbl { font-size: 12px; color: #0D0F12; flex: 1; }
      .sp-rb-remain { font-size: 11px; font-weight: 700; color: #0D0F12; white-space: nowrap; background: #F2F3F4; padding: 2px 7px; border-radius: 999px; transition: background .3s, color .3s; }
      .sp-rb-badge { display: inline-block; margin-left: 5px; font-size: 9px; font-weight: 800; color: #0D0F12; background: #F2F3F4; border: 1px solid #DCDEE0; border-radius: 4px; padding: 1px 5px; vertical-align: middle; }
      .sp-rb-box.future { opacity: .55; }
      .sp-rb-box.future .sp-rb-amt, .sp-rb-box.future .sp-rb-lbl { color: #4B5157; }

      .sp-rb-box.active { border-color: #A8B0B9; animation: sp-rb-pulse 1.8s ease-in-out infinite; }
      @keyframes sp-rb-pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(168,176,185,.6); } 50% { box-shadow: 0 0 0 6px rgba(168,176,185,0); } }
      .sp-rb-box.active .sp-rb-fill { background: linear-gradient(90deg, #DCDEE0 0%, #A8B0B9 100%); }
      .sp-rb-box.active .sp-rb-remain { background: #0D0F12; color: #FFFFFF; }
      .sp-rb-box.active::before { content: ''; position: absolute; inset: 0; z-index: 2; background: linear-gradient(115deg, transparent 35%, rgba(255,255,255,.7) 50%, transparent 65%); transform: translateX(-130%) skewX(-20deg); animation: sp-rb-shim 1.6s ease-in-out infinite; pointer-events: none; }
      @keyframes sp-rb-shim { to { transform: translateX(130%) skewX(-20deg); } }
      .sp-rb-box.done { border-color: #8fbfa0; background: #EAF3ED; }
      .sp-rb-box.done .sp-rb-fill { background: linear-gradient(90deg, #d3e8da 0%, #b8dbc4 100%); }
      .sp-rb-box.done .sp-rb-ic { background: #2f7d4f; border-color: #2f7d4f; color: #fff; }
      .sp-rb-box.done .sp-rb-amt { color: #2f7d4f; }
      .sp-rb-box.done .sp-rb-lbl { color: #0D0F12; }
      @keyframes sp-rb-pop { 0% { transform: scale(0); } 55% { transform: scale(1.3); } 100% { transform: scale(1); } }
      @keyframes sp-rb-ring { 0% { box-shadow: 0 0 0 0 rgba(13,15,18,.35); } 100% { box-shadow: 0 0 0 16px rgba(13,15,18,0); } }
      .sp-rb-box.sp-just { animation: sp-rb-ring .7s ease; }
      .sp-rb-box.sp-just .sp-rb-ic { animation: sp-rb-pop .5s ease; }
      @media (prefers-reduced-motion: reduce) { .sp-rb-box.active, .sp-rb-box.active::before { animation: none; } }
      @media (max-width: 480px) { #sp-cart-drawer { width: 100vw; max-width: 100vw; } }

      #sp-cart-fab { position: fixed; right: 20px; bottom: calc(20px + env(safe-area-inset-bottom)); width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #0D0F12 0%, #2A2E33 100%); border: none; color: #FFFFFF; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(13, 15, 18, .35); cursor: pointer; z-index: 199990; transition: transform .2s ease, box-shadow .2s ease; }
      #sp-cart-fab:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13, 15, 18, .4); }
      #sp-cart-fab svg { width: 24px; height: 24px; flex-shrink: 0; }
      #sp-cart-fab-badge { position: absolute; top: -2px; right: -2px; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; background: #FFFFFF; color: #0D0F12; font-family: 'Sora', sans-serif; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(13, 15, 18, .3); }
      @media (max-width: 480px) { #sp-cart-fab { right: 16px; bottom: calc(16px + env(safe-area-inset-bottom)); width: 52px; height: 52px; } }
    </style>
    <?php
}, 20);

add_action('wp_footer', function () {
    $abo_stack_url = home_url('/abo-stack/');
    $gift_img_100 = wp_get_attachment_image_url(wc_get_product(74) ? wc_get_product(74)->get_image_id() : 0, 'thumbnail');
    $gift_img_200 = wp_get_attachment_image_url(wc_get_product(745) ? wc_get_product(745)->get_image_id() : 0, 'thumbnail');
    $gift_img_350 = wp_get_attachment_image_url(wc_get_product(68) ? wc_get_product(68)->get_image_id() : 0, 'thumbnail');
    $gift_img_500 = wp_get_attachment_image_url(wc_get_product(65) ? wc_get_product(65)->get_image_id() : 0, 'thumbnail');
    ?>
    <script id="sp-cart-gifts-js">
    (function () {
      if (window.__spCartGifts) return;
      window.__spCartGifts = true;

      var TIERS = [
        { amount: 100, icon: '🚚', label: 'Gratisversand', giftImg: '' },
        { amount: 200, icon: '💧', label: '<?php echo (defined('SP_GIFT_BAC_WATER_ENABLED') && SP_GIFT_BAC_WATER_ENABLED) ? 'Injektionskit + Bac Water gratis' : 'Injektionskit gratis'; ?>', giftImg: '<?php echo esc_js($gift_img_200 ?: ''); ?>' },
        { amount: 350, icon: '🎁', label: 'GHK-Cu gratis', giftImg: '<?php echo esc_js($gift_img_350 ?: ''); ?>' },
        { amount: 500, icon: '⭐', label: 'Retatrutide gratis', giftImg: '<?php echo esc_js($gift_img_500 ?: ''); ?>' }
      ];

      var TRASH_SVG = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>';
      var ABO_STACK_URL = '<?php echo esc_js($abo_stack_url); ?>';

      function fmtEUR(n) {
        return (Math.round(n * 100) / 100).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
      }
      function fmtRem(n) {
        var r = Math.round(n * 100) / 100;
        var hc = Math.abs(r - Math.round(r)) > 0.001;
        return r.toLocaleString('de-DE', { minimumFractionDigits: hc ? 2 : 0, maximumFractionDigits: 2 }) + ' €';
      }

      var drawer = document.getElementById('sp-cart-drawer');
      var overlay = document.getElementById('sp-cd-overlay');
      var itemsHost = document.getElementById('sp-cd-items');
      var subtotalEl = document.getElementById('sp-cd-subtotal-val');
      var fab = document.getElementById('sp-cart-fab');
      var fabBadge = document.getElementById('sp-cart-fab-badge');
      var rbHost = document.getElementById('sp-rb');
      var rbBoxes = null;
      var rbBuilt = false;
      var prevDone = TIERS.map(function () { return false; });
      var storeNonce = '';
      var cartReadyPromise = null;

      function buildGiftBar() {
        var h = '<div class="sp-rb-title">Deine Geschenke</div><div class="sp-rb-list">';
        TIERS.forEach(function (t) {
          var badge = (t.amount === 500) ? ' <span class="sp-rb-badge">NEU</span>' : '';
          h += '<div class="sp-rb-box future"><div class="sp-rb-fill"></div><div class="sp-rb-content">' +
               '<span class="sp-rb-ic">' + t.icon + '</span>' +
               '<span class="sp-rb-amt">' + t.amount + ' €</span>' +
               '<span class="sp-rb-lbl">' + t.label + badge + '</span>' +
               '<span class="sp-rb-remain" style="display:none"></span>' +
               '</div></div>';
        });
        h += '</div>';
        rbHost.innerHTML = h;
        rbBoxes = [].slice.call(rbHost.querySelectorAll('.sp-rb-box'));
        rbBuilt = true;
      }

      function paintGiftBar(total, animate) {
        if (!rbBuilt) buildGiftBar();
        var nextIdx = -1;
        for (var i = 0; i < TIERS.length; i++) {
          if (total < TIERS[i].amount) { nextIdx = i; break; }
        }
        TIERS.forEach(function (t, i) {
          var box = rbBoxes[i];
          var fill = box.querySelector('.sp-rb-fill');
          var ic = box.querySelector('.sp-rb-ic');
          var remain = box.querySelector('.sp-rb-remain');
          var prev = i > 0 ? TIERS[i - 1].amount : 0;
          var done = total >= t.amount, active = (i === nextIdx);
          var pct = done ? 100 : (active ? Math.max(0, Math.min(100, (total - prev) / (t.amount - prev) * 100)) : 0);
          if (!animate) { fill.style.transition = 'none'; }
          fill.style.width = pct + '%';
          if (!animate) { void fill.offsetWidth; fill.style.transition = ''; }
          box.classList.toggle('done', done);
          box.classList.toggle('active', active && !done);
          box.classList.toggle('future', !done && !active);
          ic.textContent = done ? '✓' : t.icon;
          if (active && !done) {
            remain.style.display = '';
            remain.textContent = 'noch ' + fmtRem(t.amount - total);
          } else {
            remain.style.display = 'none';
          }
          if (animate && done && !prevDone[i]) {
            box.classList.remove('sp-just');
            void box.offsetWidth;
            box.classList.add('sp-just');
            (function (b) { setTimeout(function () { b.classList.remove('sp-just'); }, 760); })(box);
          }
          prevDone[i] = done;
        });
      }

      function computeItemDisplay(it) {
        var mu = (it.totals && it.totals.currency_minor_unit) || 2;
        var lineTotal = (it.totals && it.totals.line_total) ? parseInt(it.totals.line_total) / Math.pow(10, mu) : 0;
        var lineSubtotal = (it.totals && it.totals.line_subtotal) ? parseInt(it.totals.line_subtotal) / Math.pow(10, mu) : lineTotal;
        var savings = lineSubtotal - lineTotal;
        var pct = (savings > 0.004 && lineSubtotal > 0) ? Math.round((savings / lineSubtotal) * 100) : 0;
        var vol = '';
        var isGift = false;
        var aboLabel = '';
        if (it.item_data && it.item_data.length) {
          for (var i = 0; i < it.item_data.length; i++) {
            if (it.item_data[i].key === 'Volumen') { vol = it.item_data[i].value; }
            if (it.item_data[i].key === 'Geschenk') { isGift = true; }
            if (it.item_data[i].key === 'Abo') { aboLabel = it.item_data[i].value; }
          }
        }
        // Variation attributes (e.g. Retatrutide's 10mg/20mg) come back from the
        // Store API in their own "variation" array, not merged into "item_data" -
        // item_data only carries custom attributes on simple products.
        if (!vol && it.variation && it.variation.length) {
          for (var j = 0; j < it.variation.length; j++) {
            if (it.variation[j].attribute === 'Volumen') { vol = it.variation[j].value; break; }
          }
        }
        // Only exactly 3/5/10 units are a real "bundle" - other quantities still get a
        // blended discount on the price, but shouldn't show the Bundle label.
        var isExactBundle = (it.quantity === 3 || it.quantity === 5 || it.quantity === 10);
        return { lineTotal: lineTotal, lineSubtotal: lineSubtotal, savings: savings, pct: pct, vol: vol, isExactBundle: isExactBundle, isGift: isGift, aboLabel: aboLabel };
      }

      function itemRowHTML(it) {
        var d = computeItemDisplay(it);
        var img = (it.images && it.images[0] && it.images[0].thumbnail) ? it.images[0].thumbnail : '';
        if (d.isGift) {
          // Auto-managed by the cart total - no qty stepper, no remove button.
          return (img ? '<img src="' + img + '" alt="">' : '') +
                 '<div style="flex:1;min-width:0">' +
                 '<span class="sp-cd-item-bundle sp-cd-item-gift-badge">🎁 Geschenk</span>' +
                 '<p class="sp-cd-item-name">' + it.name + '</p>' +
                 (d.vol ? '<p class="sp-cd-item-vol">' + d.vol + '</p>' : '') +
                 '</div>' +
                 '<div class="sp-cd-item-right">' +
                 (d.lineSubtotal > 0 ? '<span class="sp-cd-item-save">Spare ' + fmtEUR(d.lineSubtotal) + '</span>' : '') +
                 '<span class="sp-cd-item-price sp-cd-item-price-free">GRATIS</span>' +
                 '</div>';
        }
        return (img ? '<img src="' + img + '" alt="">' : '') +
               '<div style="flex:1;min-width:0">' +
               (d.aboLabel ? '<span class="sp-cd-item-bundle sp-cd-item-abo-badge">&#8635; Abo &middot; ' + d.aboLabel + '</span>' : '') +
               (d.isExactBundle ? '<span class="sp-cd-item-bundle">Bundle &minus;' + d.pct + '%</span>' : '') +
               '<p class="sp-cd-item-name">' + it.name + (d.aboLabel ? ' <span class="sp-cd-qty-val sp-cd-item-qty-inline">&times;' + it.quantity + '</span>' : '') + '</p>' +
               (d.vol ? '<p class="sp-cd-item-vol">' + d.vol + '</p>' : '') +
               (d.aboLabel ?
                 '' :
                 '<div class="sp-cd-qty">' +
                   '<button type="button" class="sp-cd-qty-btn" data-action="dec" aria-label="Menge verringern">-</button>' +
                   '<span class="sp-cd-qty-val">' + it.quantity + '</span>' +
                   '<button type="button" class="sp-cd-qty-btn" data-action="inc" aria-label="Menge erhöhen">+</button>' +
                 '</div>') +
               '</div>' +
               '<div class="sp-cd-item-right">' +
               '<button type="button" class="sp-cd-item-remove" data-action="remove" aria-label="Aus dem Warenkorb entfernen">' + TRASH_SVG + '</button>' +
               (d.pct > 0 ? '<span class="sp-cd-item-save">Spare ' + fmtEUR(d.savings) + '</span>' : '') +
               '<span class="sp-cd-item-price">' + fmtEUR(d.lineTotal) + '</span>' +
               (d.aboLabel ? '<a class="sp-cd-item-abo-edit" href="' + ABO_STACK_URL + '">Abo-Stack bearbeiten</a>' : '') +
               '</div>';
      }

      /* Updates an existing row's text/badges in place instead of destroying and
         recreating its buttons - a full innerHTML rebuild on every +/- tap replaces the
         exact button under the user's finger, which iOS Safari can render as a
         momentarily invisible/stuck button. */
      function updateItemRowInPlace(el, it) {
        var d = computeItemDisplay(it);

        var qtyValEl = el.querySelector('.sp-cd-qty-val');
        if (qtyValEl) qtyValEl.textContent = d.aboLabel ? ('×' + it.quantity) : it.quantity;

        var priceEl = el.querySelector('.sp-cd-item-price');
        if (priceEl) priceEl.textContent = fmtEUR(d.lineTotal);

        var rightEl = el.querySelector('.sp-cd-item-right');
        var saveEl = el.querySelector('.sp-cd-item-save');
        if (d.pct > 0) {
          var saveText = 'Spare ' + fmtEUR(d.savings);
          if (saveEl) {
            saveEl.textContent = saveText;
          } else if (rightEl && priceEl) {
            saveEl = document.createElement('span');
            saveEl.className = 'sp-cd-item-save';
            saveEl.textContent = saveText;
            rightEl.insertBefore(saveEl, priceEl);
          }
        } else if (saveEl) {
          saveEl.parentNode.removeChild(saveEl);
        }

        var nameEl = el.querySelector('.sp-cd-item-name');
        var aboEl = el.querySelector('.sp-cd-item-abo-badge');
        if (d.aboLabel) {
          var aboText = '↻ Abo · ' + d.aboLabel;
          if (aboEl) {
            aboEl.textContent = aboText;
          } else if (nameEl && nameEl.parentNode) {
            aboEl = document.createElement('span');
            aboEl.className = 'sp-cd-item-bundle sp-cd-item-abo-badge';
            aboEl.textContent = aboText;
            nameEl.parentNode.insertBefore(aboEl, nameEl.parentNode.firstChild);
          }
        } else if (aboEl) {
          aboEl.parentNode.removeChild(aboEl);
        }

        var editEl = el.querySelector('.sp-cd-item-abo-edit');
        if (d.aboLabel && !editEl && rightEl) {
          editEl = document.createElement('a');
          editEl.className = 'sp-cd-item-abo-edit';
          editEl.href = ABO_STACK_URL;
          editEl.textContent = 'Abo-Stack bearbeiten';
          rightEl.appendChild(editEl);
        } else if (!d.aboLabel && editEl) {
          editEl.parentNode.removeChild(editEl);
        }

        var bundleEl = el.querySelector('.sp-cd-item-bundle:not(.sp-cd-item-abo-badge)');
        if (d.isExactBundle) {
          var bundleText = 'Bundle −' + d.pct + '%';
          if (bundleEl) {
            bundleEl.textContent = bundleText;
          } else if (nameEl && nameEl.parentNode) {
            bundleEl = document.createElement('span');
            bundleEl.className = 'sp-cd-item-bundle';
            bundleEl.textContent = bundleText;
            nameEl.parentNode.insertBefore(bundleEl, nameEl);
          }
        } else if (bundleEl) {
          bundleEl.parentNode.removeChild(bundleEl);
        }
      }

      // Products that are a lyophilised powder needing reconstitution before use -
      // Peptrium-Pens, Bac Water and the syringes themselves are deliberately excluded.
      // A variable product's cart item reports its *variation* ID in "id", not
      // its own parent product ID, so the parent IDs alone would never match -
      // expand each variable product here into all of its variation IDs too.
      <?php
      $sp_recon_parent_ids = array(65, 68, 71, 77, 428, 431, 434, 437, 512, 515, 521);
      $sp_recon_ids_expanded = $sp_recon_parent_ids;
      foreach ($sp_recon_parent_ids as $sp_recon_id) {
          $sp_recon_product = wc_get_product($sp_recon_id);
          if ($sp_recon_product && $sp_recon_product->is_type('variable')) {
              foreach ($sp_recon_product->get_children() as $sp_recon_variation_id) {
                  $sp_recon_ids_expanded[] = $sp_recon_variation_id;
              }
          }
      }
      ?>
      var RECON_NEEDED_IDS = <?php echo wp_json_encode($sp_recon_ids_expanded); ?>;
      var ADDON_SUGGESTIONS = [
        { id: 74, name: 'Bac Water 3 ml', price: '6,90 €', hint: 'Zum Anmischen deines Pulvers' },
        { id: 80, name: 'Insulinspritze 10er Pack', price: '9,90 €', hint: 'Zum genauen Dosieren' }
      ];

      // Zweite, unabhaengige Vorschlag-Gruppe: sobald ein Peptrium-Pen im
      // Warenkorb liegt, Pen Nadeln als passendes Zubehoer vorschlagen -
      // gleiches Prinzip wie RECON_NEEDED_IDS/ADDON_SUGGESTIONS oben, nur mit
      // eigenem Ausloeser statt "braucht Rekonstitution". Die drei
      // Peptrium-Pen-Produkte sind selbst variable Produkte (Varianten je
      // Pen) - genau wie bei RECON_NEEDED_IDS muss daher auf die Varianten-
      // IDs erweitert werden, sonst matcht der Warenkorb-Eintrag nie.
      <?php
      $sp_pen_parent_ids = array(393, 395, 396);
      $sp_pen_ids_expanded = $sp_pen_parent_ids;
      foreach ($sp_pen_parent_ids as $sp_pen_id) {
          $sp_pen_product = wc_get_product($sp_pen_id);
          if ($sp_pen_product && $sp_pen_product->is_type('variable')) {
              foreach ($sp_pen_product->get_children() as $sp_pen_variation_id) {
                  $sp_pen_ids_expanded[] = $sp_pen_variation_id;
              }
          }
      }
      ?>
      var PEN_PRODUCT_IDS = <?php echo wp_json_encode($sp_pen_ids_expanded); ?>;
      var PEN_ADDON_SUGGESTIONS = [
        { id: 908, name: 'Pen Nadeln 32G x 4mm (15er Pack)', price: '9,90 €', hint: 'Passend für deinen Peptrium-Pen' }
      ];

      function suggestionRowHTML(a) {
        return '<div class="sp-cd-suggest" data-addon-id="' + a.id + '" role="button" tabindex="0">' +
               '<div class="sp-cd-suggest-text"><span class="sp-cd-suggest-name">' + a.name + '</span>' +
               '<span class="sp-cd-suggest-hint">' + a.hint + '</span></div>' +
               '<span class="sp-cd-suggest-btn"><span class="sp-cd-suggest-btn-label">Hinzuf&uuml;gen</span><span class="sp-cd-suggest-btn-price">+&nbsp;' + a.price + '</span></span>' +
               '</div>';
      }

      function addonSuggestionsHTML(items) {
        var presentIds = items.map(function (it) { return it.id; });
        var h = '';
        var needsRecon = presentIds.some(function (id) { return RECON_NEEDED_IDS.indexOf(id) !== -1; });
        if (needsRecon) {
          ADDON_SUGGESTIONS.forEach(function (a) {
            if (presentIds.indexOf(a.id) !== -1) return;
            h += suggestionRowHTML(a);
          });
        }
        var needsPenAddon = presentIds.some(function (id) { return PEN_PRODUCT_IDS.indexOf(id) !== -1; });
        if (needsPenAddon) {
          PEN_ADDON_SUGGESTIONS.forEach(function (a) {
            if (presentIds.indexOf(a.id) !== -1) return;
            h += suggestionRowHTML(a);
          });
        }
        return h;
      }

      function isGiftItem(it) {
        if (it.item_data && it.item_data.length) {
          for (var i = 0; i < it.item_data.length; i++) {
            if (it.item_data[i].key === 'Geschenk') return true;
          }
        }
        return false;
      }

      function renderItems(cart) {
        if (!cart.items || !cart.items.length) {
          itemsHost.innerHTML = '<div class="sp-cd-empty"><p>Der Warenkorb ist leer</p><a href="<?php echo esc_url(home_url('/alle-produkte/')); ?>" id="sp-cd-empty-continue" class="sp-cd-btn sp-cd-btn-solid">Zur&uuml;ck zum Shop</a></div>';
          return;
        }
        // Gift items always sort after items the customer added themselves,
        // so the cart reads as "yours" then "gifts" instead of interleaved.
        var items = cart.items.slice().sort(function (a, b) {
          return (isGiftItem(a) ? 1 : 0) - (isGiftItem(b) ? 1 : 0);
        });
        var h = '';
        items.forEach(function (it) {
          h += '<div class="sp-cd-item" data-key="' + it.key + '">' + itemRowHTML(it) + '</div>';
        });
        h += addonSuggestionsHTML(cart.items);
        itemsHost.innerHTML = h;
      }

      function addAddonToCart(productId) {
        if (cartBusy) return;
        setItemsBusy(true);
        fetch('/wp-json/wc/store/v1/cart/add-item', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json', 'Nonce': storeNonce },
          body: JSON.stringify({ id: productId, quantity: 1 }),
        })
          .then(function (r) {
            var n = r.headers.get('Nonce');
            if (n) storeNonce = n;
            return r.json();
          })
          .then(function (cart) { applyCartData(cart, true, false); })
          .catch(function () { updateFromStoreApi(true, false); })
          .then(function () { setItemsBusy(false); });
      }

      var lastTotal = 0;
      var subtotalRow = document.getElementById('sp-cd-subtotal-row');
      var checkoutBtn = document.getElementById('sp-cd-checkout-btn');
      var dhlBadge = document.getElementById('sp-cd-dhl');
      var checkoutTotalEl = document.getElementById('sp-cd-checkout-total');
      var totalSavingsRow = document.getElementById('sp-cd-total-savings-row');
      var totalSavingsEl = document.getElementById('sp-cd-total-savings-val');

      function applyCartData(cart, animate, openAfter, surgicalKey) {
        if (!cart || !cart.totals) return;
        var mu = cart.totals.currency_minor_unit || 2;
        var total = parseInt(cart.totals.total_items || 0) +
                    parseInt(cart.totals.total_items_tax || 0) -
                    parseInt(cart.totals.total_discount || 0) -
                    parseInt(cart.totals.total_discount_tax || 0);
        lastTotal = total / Math.pow(10, mu);

        var didSurgical = false;
        if (surgicalKey && cart.items && cart.items.length) {
          var existingEls = itemsHost.querySelectorAll('.sp-cd-item[data-key]');
          var existingKeys = [];
          existingEls.forEach(function (el) { existingKeys.push(el.getAttribute('data-key')); });
          var newKeys = cart.items.map(function (it) { return it.key; });
          var sameSet = existingKeys.length === newKeys.length && existingKeys.every(function (k) { return newKeys.indexOf(k) !== -1; });
          if (sameSet) {
            var targetEl = itemsHost.querySelector('.sp-cd-item[data-key="' + surgicalKey + '"]');
            var targetItem = null;
            for (var i = 0; i < cart.items.length; i++) {
              if (cart.items[i].key === surgicalKey) { targetItem = cart.items[i]; break; }
            }
            if (targetEl && targetItem) {
              updateItemRowInPlace(targetEl, targetItem);
              didSurgical = true;
            }
          }
        }
        if (!didSurgical) {
          renderItems(cart);
        }

        if (fabBadge) fabBadge.textContent = cart.items_count != null ? cart.items_count : 0;
        subtotalEl.textContent = fmtEUR(lastTotal);
        if (checkoutTotalEl) checkoutTotalEl.textContent = fmtEUR(lastTotal);
        var isEmpty = !cart.items || !cart.items.length;
        if (subtotalRow) subtotalRow.classList.toggle('is-empty', isEmpty);
        if (checkoutBtn) checkoutBtn.classList.toggle('is-hidden', isEmpty);
        if (dhlBadge) dhlBadge.classList.toggle('is-hidden', isEmpty);

        var totalSavings = 0;
        if (cart.items && cart.items.length) {
          cart.items.forEach(function (it) {
            var d = computeItemDisplay(it);
            totalSavings += d.savings;
          });
        }
        // Note: line_subtotal - line_total per item already nets out coupon
        // discounts (WooCommerce allocates them into line_total), so a coupon's
        // amount must NOT be added again here - that would double-count it.
        if (totalSavingsRow && totalSavingsEl) {
          if (totalSavings > 0.004) {
            totalSavingsEl.textContent = fmtEUR(totalSavings);
            totalSavingsRow.style.display = '';
          } else {
            totalSavingsRow.style.display = 'none';
          }
        }

        renderCoupons(cart.coupons || []);
        paintGiftBar(lastTotal, animate);
        if (openAfter) openDrawer();
      }

      function updateFromStoreApi(animate, openAfter) {
        return fetch('/wp-json/wc/store/v1/cart', { credentials: 'include' })
          .then(function (r) {
            var n = r.headers.get('Nonce');
            if (n) storeNonce = n;
            return r.json();
          })
          .then(function (cart) { applyCartData(cart, animate, openAfter); })
          .catch(function () {});
      }

      function ensureNonce() {
        if (storeNonce) return Promise.resolve();
        return cartReadyPromise || Promise.resolve();
      }

      var couponToggle = document.getElementById('sp-cd-coupon-toggle');
      var couponForm = document.getElementById('sp-cd-coupon-form');
      var couponInput = document.getElementById('sp-cd-coupon-input');
      var couponApplyBtn = document.getElementById('sp-cd-coupon-apply');
      var couponErrorEl = document.getElementById('sp-cd-coupon-error');
      var couponAppliedEl = document.getElementById('sp-cd-coupon-applied');
      var couponBusy = false;

      function renderCoupons(coupons) {
        if (coupons.length) {
          couponToggle.style.display = 'none';
          couponForm.style.display = 'none';
          var h = '';
          coupons.forEach(function (c) {
            h += '<div class="sp-cd-coupon-applied-row"><span class="sp-cd-coupon-applied-code">&#10003; ' + c.code.toUpperCase() + '</span>' +
                 '<button type="button" class="sp-cd-coupon-remove" data-code="' + c.code + '" aria-label="Gutschein entfernen">&times;</button></div>';
          });
          couponAppliedEl.innerHTML = h;
          couponAppliedEl.style.display = '';
        } else {
          couponAppliedEl.style.display = 'none';
          couponAppliedEl.innerHTML = '';
          if (couponForm.style.display === 'none') {
            couponToggle.style.display = '';
          }
        }
      }

      function setCouponError(msg) {
        if (msg) {
          couponErrorEl.textContent = msg;
          couponErrorEl.style.display = '';
        } else {
          couponErrorEl.style.display = 'none';
          couponErrorEl.textContent = '';
        }
      }

      function applyCoupon() {
        var code = couponInput.value.trim();
        if (!code || couponBusy) return;
        couponBusy = true;
        couponApplyBtn.disabled = true;
        setCouponError('');
        ensureNonce().then(function () {
          return fetch('/wp-json/wc/store/v1/cart/apply-coupon', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json', 'Nonce': storeNonce },
            body: JSON.stringify({ code: code }),
          });
        })
          .then(function (r) {
            var n = r.headers.get('Nonce');
            if (n) storeNonce = n;
            return r.json().then(function (data) { return { ok: r.ok, data: data }; });
          })
          .then(function (res) {
            if (res.ok) {
              couponInput.value = '';
              applyCartData(res.data, true, false);
            } else {
              setCouponError(res.data && res.data.message ? res.data.message.replace(/<[^>]*>/g, '') : 'Dieser Rabattcode konnte nicht angewendet werden.');
            }
          })
          .catch(function () { setCouponError('Dieser Rabattcode konnte nicht angewendet werden.'); })
          .then(function () { couponBusy = false; couponApplyBtn.disabled = false; });
      }

      function removeCoupon(code) {
        if (couponBusy) return;
        couponBusy = true;
        fetch('/wp-json/wc/store/v1/cart/remove-coupon', {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json', 'Nonce': storeNonce },
          body: JSON.stringify({ code: code }),
        })
          .then(function (r) {
            var n = r.headers.get('Nonce');
            if (n) storeNonce = n;
            return r.json();
          })
          .then(function (cart) { applyCartData(cart, true, false); })
          .catch(function () { updateFromStoreApi(true, false); })
          .then(function () { couponBusy = false; });
      }

      if (couponToggle) {
        couponToggle.addEventListener('click', function () {
          couponToggle.style.display = 'none';
          couponForm.style.display = 'flex';
        });
      }
      if (couponApplyBtn) couponApplyBtn.addEventListener('click', applyCoupon);
      if (couponInput) {
        couponInput.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') { e.preventDefault(); applyCoupon(); }
        });
      }
      if (couponAppliedEl) {
        couponAppliedEl.addEventListener('click', function (e) {
          var btn = e.target.closest ? e.target.closest('.sp-cd-coupon-remove') : null;
          if (btn) removeCoupon(btn.getAttribute('data-code'));
        });
      }

      var cartBusy = false;
      function setItemsBusy(busy) {
        cartBusy = busy;
        itemsHost.classList.toggle('sp-cd-busy', busy);
      }

      function changeItemQuantity(key, quantity) {
        if (cartBusy) return;
        setItemsBusy(true);
        var isRemoval = quantity <= 0;
        var endpoint = isRemoval ? '/wp-json/wc/store/v1/cart/remove-item' : '/wp-json/wc/store/v1/cart/update-item';
        var body = isRemoval ? { key: key } : { key: key, quantity: quantity };
        fetch(endpoint, {
          method: 'POST',
          credentials: 'include',
          headers: { 'Content-Type': 'application/json', 'Nonce': storeNonce },
          body: JSON.stringify(body),
        })
          .then(function (r) {
            var n = r.headers.get('Nonce');
            if (n) storeNonce = n;
            return r.json();
          })
          .then(function (cart) {
            applyCartData(cart, true, false, isRemoval ? null : key);
          })
          .catch(function () { updateFromStoreApi(true, false); })
          .then(function () { setItemsBusy(false); });
      }

      itemsHost.addEventListener('click', function (e) {
        var suggestRow = e.target.closest ? e.target.closest('.sp-cd-suggest') : null;
        if (suggestRow) {
          addAddonToCart(parseInt(suggestRow.getAttribute('data-addon-id'), 10));
          return;
        }
        var removeBtn = e.target.closest ? e.target.closest('.sp-cd-item-remove') : null;
        if (removeBtn) {
          var removeEl = removeBtn.closest('.sp-cd-item');
          if (removeEl) changeItemQuantity(removeEl.getAttribute('data-key'), 0);
          return;
        }
        var btn = e.target.closest ? e.target.closest('.sp-cd-qty-btn') : null;
        if (!btn) return;
        var itemEl = btn.closest('.sp-cd-item');
        if (!itemEl) return;
        var key = itemEl.getAttribute('data-key');
        var valEl = itemEl.querySelector('.sp-cd-qty-val');
        var current = parseInt(valEl.textContent, 10) || 1;
        var next = btn.getAttribute('data-action') === 'inc' ? current + 1 : current - 1;
        changeItemQuantity(key, next);
      });

      itemsHost.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var suggestRow = e.target.closest ? e.target.closest('.sp-cd-suggest') : null;
        if (!suggestRow) return;
        e.preventDefault();
        addAddonToCart(parseInt(suggestRow.getAttribute('data-addon-id'), 10));
      });

      function openDrawer() {
        drawer.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
      }
      function closeDrawer() {
        drawer.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
      }

      document.getElementById('sp-cd-close').addEventListener('click', closeDrawer);
      overlay.addEventListener('click', closeDrawer);
      var continueBtn = document.getElementById('sp-cd-continue');
      if (continueBtn) continueBtn.addEventListener('click', closeDrawer);
      // "Zurueck zum Shop" (leerer Warenkorb) soll sich wie "Weiter
      // einkaufen" verhalten - Drawer schliessen statt zur Produktliste
      // wegzunavigieren. Delegiert, da der Link bei jedem Cart-Update neu
      // gerendert wird (siehe renderItems oben).
      itemsHost.addEventListener('click', function (e) {
        var emptyBtn = e.target.closest ? e.target.closest('#sp-cd-empty-continue') : null;
        if (!emptyBtn) return;
        e.preventDefault();
        closeDrawer();
      });
      if (fab) fab.addEventListener('click', openDrawer);
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
      });

      if (window.jQuery) {
        jQuery(document.body).on('added_to_cart', function () {
          updateFromStoreApi(true, true);
        });
      }

      document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.classList || !form.classList.contains('cart')) return;
        var addToCartField = form.querySelector('[name="add-to-cart"]');
        if (!addToCartField) return;

        e.preventDefault();
        var submitBtn = form.querySelector('button[type="submit"], .single_add_to_cart_button');
        var formData = new FormData(form);
        if (!formData.get('product_id') && addToCartField.value) {
          formData.set('product_id', addToCartField.value);
        }
        /* WC_AJAX::add_to_cart() does NOT read a separate variation_id field like the
           classic (non-AJAX) form handler does - for variable products it expects
           product_id itself to BE the variation's post ID, and derives the parent
           product + attributes from that. Without this, it silently falls back to
           variation_id 0, WC's validation rejects the "no variant chosen" add, and the
           handler responds with {error:true, product_url}, which the code below then
           navigates to - i.e. the page just reloads instead of opening the drawer. */
        var variationIdField = form.querySelector('[name="variation_id"]');
        var variationId = variationIdField ? parseInt(variationIdField.value, 10) : 0;
        if (variationId > 0) {
          formData.set('product_id', variationId);
        }
        /* WooCommerce's classic WC_Form_Handler::add_to_cart_action() runs on every
           request (hooked on wp_loaded) and adds the item a second time whenever
           add-to-cart is present in $_POST, regardless of this being an AJAX call -
           strip it so only WC_AJAX::add_to_cart() processes this submission. */
        formData.delete('add-to-cart');

        fetch('<?php echo esc_js(\WC_AJAX::get_endpoint('add_to_cart')); ?>', {
          method: 'POST',
          credentials: 'include',
          body: formData,
        })
          .then(function (r) { return r.json(); })
          .then(function (resp) {
            if (resp && resp.error) {
              if (resp.product_url) window.location = resp.product_url;
              return;
            }
            if (window.jQuery) {
              jQuery(document.body).trigger('added_to_cart', [resp.fragments, resp.cart_hash, submitBtn ? jQuery(submitBtn) : undefined]);
            }
            updateFromStoreApi(true, true);
          })
          .catch(function () {
            form.submit();
          });
      });

      cartReadyPromise = updateFromStoreApi(false, false);
    })();
    </script>
    <?php
}, 21);






