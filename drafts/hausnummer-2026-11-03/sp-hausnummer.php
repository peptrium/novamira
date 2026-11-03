<?php
/**
 * Plugin Name: SP Hausnummer-Pflicht
 * Description: (2026-11-03) Kasse: Straße ohne Hausnummer (keine Ziffer) wird für DE/AT/CH nicht akzeptiert.
 *   Anlass: #3323 ohne Hausnummer bestellt → Paket nicht zustellbar. Browser-Hinweis direkt unter dem Feld
 *   ("Hausnummer vergessen?") + serverseitige Sperre beim Bestellen (Store API), damit es auch ohne JS greift.
 */
if (!defined('ABSPATH')) exit;

function sp_hn_countries() { return ['DE', 'AT', 'CH']; }

/** true, wenn die Straße eine Hausnummer (mind. eine Ziffer) enthält oder das Land nicht geprüft wird. */
function sp_hn_ok($street, $country) {
    if (!in_array(strtoupper((string) $country), sp_hn_countries(), true)) return true;
    $street = trim((string) $street);
    return $street === '' || (bool) preg_match('/\d/', $street);
}

add_filter('woocommerce_default_address_fields', function ($f) {
    if (isset($f['address_1'])) $f['address_1']['label'] = 'Straße und Hausnummer';
    return $f;
}, 20);
add_filter('woocommerce_get_country_locale', function ($l) {
    foreach (sp_hn_countries() as $c) { $l[$c]['address_1']['label'] = 'Straße und Hausnummer'; }
    return $l;
}, 30);

add_action('woocommerce_store_api_checkout_update_order_from_request', function ($order, $request) {
    $has_ship = $order->has_shipping_address();
    $street = $has_ship ? $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2() : $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
    $country = $has_ship ? $order->get_shipping_country() : $order->get_billing_country();
    if (!$order->needs_shipping_address() && !$has_ship) return;
    if (!sp_hn_ok($street, $country) && class_exists('\Automattic\WooCommerce\StoreApi\Exceptions\RouteException')) {
        throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
            'sp_hausnummer',
            'Bitte ergänze deine Hausnummer bei der Adresse (z. B. „Musterstraße 12“) – sonst kann DHL dein Paket nicht zustellen.',
            400
        );
    }
}, 10, 2);

add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page() || is_wc_endpoint_url('order-pay')) return;
    ?>
<style>.sp-hn-hint{margin:6px 2px 0;font-size:13px;line-height:1.4;color:#9A5B00;font-weight:600}.sp-hn-hint b{color:#7A4700}</style>
<script>
(function(){
  var C=<?php echo wp_json_encode(sp_hn_countries()); ?>;
  function country(kind){var s=document.getElementById(kind+'-country');if(s&&s.value)return s.value.toUpperCase();
    try{var d=wp.data.select('wc/store/cart').getCustomerData();var a=kind==='billing'?d.billingAddress:d.shippingAddress;return (a&&a.country||'DE').toUpperCase();}catch(e){return 'DE';}}
  function check(inp){
    if(!inp)return; var kind=inp.id.indexOf('billing')===0?'billing':'shipping';
    var wrap=inp.closest('.wc-block-components-text-input')||inp.parentNode, h=wrap.parentNode.querySelector('.sp-hn-hint[data-for="'+inp.id+'"]');
    var v=(inp.value||'').trim(), bad=v.length>2 && !/\d/.test(v) && C.indexOf(country(kind))>-1 && document.activeElement!==inp;
    if(bad&&!h){h=document.createElement('div');h.className='sp-hn-hint';h.dataset.for=inp.id;h.innerHTML='⚠️ <b>Hausnummer vergessen?</b> Bitte trag sie mit ein, z. B. „Musterstraße 12“.';wrap.parentNode.insertBefore(h,wrap.nextSibling);}
    if(!bad&&h)h.remove();
  }
  function all(){['shipping-address_1','billing-address_1'].forEach(function(id){check(document.getElementById(id));});}
  document.addEventListener('focusout',function(e){if(e.target&&/address_1$/.test(e.target.id||''))setTimeout(function(){check(e.target);},50);},true);
  document.addEventListener('input',function(e){if(e.target&&/address_1$/.test(e.target.id||'')&&/\d/.test(e.target.value)){var h=document.querySelector('.sp-hn-hint[data-for="'+e.target.id+'"]');if(h)h.remove();}},true);
  setInterval(all,2000);
})();
</script>
    <?php
}, 70);
