<?php
/**
 * Plugin Name: SP Kasse – einfacher bestellen
 * Description: (2026-10-28) Kasse leichter bedienbar: Land standardmaessig Deutschland, Bundesland nie Pflicht,
 * PLZ -> Ort automatisch, nach "Bestellen" mit Fehlern direkt zum ersten Fehler springen + kurzer Hinweis
 * welche Felder fehlen. Keine Aenderung an Bestell-/Zahlungslogik.
 *
 * Hintergrund (Kassen-Fehler-Protokoll wp_sp_errors, Okt. 2026): haeufigste Abbrueche = "Land/Region: Bitte Land
 * auswaehlen" + "Bundesland fehlt" + "PLZ ungueltig" (Land war leer, weil woocommerce_default_customer_address = ''),
 * und vergessene Pflicht-Haekchen (die Meldung stand ausserhalb des Bildschirms).
 */
if (!defined('ABSPATH')) {
    exit;
}

/* 1) Land vorbelegen: neue Besucher bekommen DE (Laden-Basisland ist fuer Rechnungen US/WY, daher nicht 'base') */
add_filter('woocommerce_customer_default_location_array', function ($loc) {
    if (!is_array($loc) || empty($loc['country'])) {
        return array('country' => 'DE', 'state' => '');
    }
    return $loc;
});

/* Bestehende Sitzungen mit leerem Land (vor dieser Aenderung entstanden) beim Aufruf der Kasse nachziehen */
add_action('template_redirect', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url('order-received') || !WC()->customer) {
        return;
    }
    $c = WC()->customer;
    $changed = false;
    if ($c->get_shipping_country() === '') {
        $c->set_shipping_country('DE');
        $changed = true;
    }
    if ($c->get_billing_country() === '') {
        $c->set_billing_country('DE');
        $changed = true;
    }
    if ($changed) {
        $c->save();
    }
}, 5);

/* 2) Bundesland/Kanton ist in DE/AT/CH nie Pflicht (wird fuer DHL nicht gebraucht) */
add_filter('woocommerce_get_country_locale', function ($loc) {
    foreach (array('DE', 'AT', 'CH') as $cc) {
        if (!isset($loc[$cc]['state']) || !is_array($loc[$cc]['state'])) {
            $loc[$cc]['state'] = array();
        }
        $loc[$cc]['state']['required'] = false;
        $loc[$cc]['state']['hidden'] = true;
    }
    return $loc;
});

/* 3) PLZ -> Ort (Server holt api.zippopotam.us, Ergebnis 60 Tage zwischengespeichert; nur PLZ + Land werden gesendet) */
function sp_kux_plz_places($cc, $plz) {
    $cc = strtoupper($cc);
    $len = array('DE' => 5, 'AT' => 4, 'CH' => 4);
    if (!isset($len[$cc]) || !preg_match('/^\d{' . $len[$cc] . '}$/', $plz)) {
        return null;
    }
    $key = 'sp_plz_' . $cc . $plz;
    $hit = get_transient($key);
    if (is_array($hit)) {
        return $hit;
    }
    $r = wp_remote_get('https://api.zippopotam.us/' . strtolower($cc) . '/' . $plz, array('timeout' => 4));
    $code = wp_remote_retrieve_response_code($r);
    if (is_wp_error($r) || ($code !== 200 && $code !== 404)) {
        return null; /* Dienst nicht erreichbar -> nichts tun */
    }
    $out = array();
    if ($code === 200) {
        $j = json_decode(wp_remote_retrieve_body($r), true);
        foreach ((array) ($j['places'] ?? array()) as $p) {
            $n = trim((string) ($p['place name'] ?? ''));
            if ($n !== '' && !in_array($n, $out, true)) {
                $out[] = $n;
            }
        }
    }
    set_transient($key, $out, 60 * DAY_IN_SECONDS);
    return $out;
}

function sp_kux_ajax_plz() {
    $ip = preg_replace('/[^0-9a-f:.]/i', '', $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''));
    $lim = 'sp_plzl_' . md5($ip);
    $n = (int) get_transient($lim);
    if ($n > 60) {
        wp_send_json(array('ok' => false));
    }
    set_transient($lim, $n + 1, HOUR_IN_SECONDS);
    $places = sp_kux_plz_places(sanitize_text_field(wp_unslash($_POST['c'] ?? '')), sanitize_text_field(wp_unslash($_POST['p'] ?? '')));
    wp_send_json(array('ok' => is_array($places), 'places' => is_array($places) ? $places : array()));
}
add_action('wp_ajax_sp_kux_plz', 'sp_kux_ajax_plz');
add_action('wp_ajax_nopriv_sp_kux_plz', 'sp_kux_ajax_plz');

add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url('order-received') || is_wc_endpoint_url('order-pay')) {
        return;
    }
    ?>
<style id="sp-kux-css">
#sp-kux-toast{position:fixed;left:12px;right:12px;bottom:calc(14px + env(safe-area-inset-bottom));z-index:100000;max-width:460px;margin:0 auto;background:#0D0F12;color:#fff;border-radius:14px;padding:13px 16px 13px 46px;font:600 13.5px/1.45 Sora,sans-serif;box-shadow:0 12px 30px rgba(0,0,0,.28);transform:translateY(140%);opacity:0;transition:transform .32s ease,opacity .32s ease;pointer-events:none}
#sp-kux-toast.on{transform:none;opacity:1}
#sp-kux-toast:before{content:'!';position:absolute;left:14px;top:50%;margin-top:-12px;width:24px;height:24px;border-radius:50%;background:#E5484D;color:#fff;font:800 14px/24px Sora,sans-serif;text-align:center}
#sp-kux-toast small{display:block;font-weight:500;color:#C9CED4;font-size:12.5px;margin-top:2px}
.sp-kux-flash{animation:spKuxFlash 1.4s ease 1}
@keyframes spKuxFlash{0%,100%{box-shadow:0 0 0 0 rgba(229,72,77,0)}25%,60%{box-shadow:0 0 0 4px rgba(229,72,77,.28)}}
</style>
<datalist id="sp-kux-orte"></datalist>
<script id="sp-kux-js">
(function(){
  var AJAX=<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,cache={},LEN={DE:5,AT:4,CH:4};
  function cart(){return window.wp&&wp.data&&wp.data.select&&wp.data.select('wc/store/cart');}
  function act(){return wp.data.dispatch('wc/store/cart');}

  /* ---------- PLZ -> Ort ---------- */
  function lookup(cc,plz){
    var k=cc+plz;if(cache[k])return cache[k];
    var f=new FormData();f.append('action','sp_kux_plz');f.append('c',cc);f.append('p',plz);
    cache[k]=fetch(AJAX,{method:'POST',body:f,credentials:'same-origin'}).then(function(r){return r.json();}).catch(function(){return {ok:false};});
    return cache[k];
  }
  function onPlz(inp){
    var form=/^billing/.test(inp.id)?'billing':'shipping',st=cart();if(!st)return;
    var addr=form==='billing'?st.getCustomerData().billingAddress:st.getCustomerData().shippingAddress;
    var cc=(addr&&addr.country)||'DE',plz=(inp.value||'').replace(/\s/g,'');
    if(!LEN[cc]||plz.length!==LEN[cc]||!/^\d+$/.test(plz))return;
    lookup(cc,plz).then(function(res){
      if(!res||!res.ok||!res.places||!res.places.length)return;
      var city=document.getElementById(form+'-city'),dl=document.getElementById('sp-kux-orte');
      if(dl){dl.innerHTML=res.places.map(function(p){return '<option value="'+String(p).replace(/"/g,'&quot;')+'">';}).join('');}
      if(city){city.setAttribute('list','sp-kux-orte');}
      var cur=st.getCustomerData()[form+'Address'],have=(cur&&cur.city||'').trim();
      /* nur ausfuellen, wenn Ort leer ist oder offensichtlich nicht zur PLZ passt; bei mehreren Orten den ersten - per Liste aenderbar */
      if(have&&res.places.some(function(p){return p.toLowerCase()===have.toLowerCase();}))return;
      if(have&&res.places.length>1)return;
      var o={};o.city=res.places[0];
      try{form==='billing'?act().setBillingAddress(o):act().setShippingAddress(o);}catch(e){}
      if(city)setTimeout(function(){city.classList.add('sp-kux-flash');setTimeout(function(){city.classList.remove('sp-kux-flash');},1500);},60);
    });
  }
  document.addEventListener('input',function(e){var t=e.target;if(t&&t.id&&/^(shipping|billing)-postcode$/.test(t.id))onPlz(t);},true);
  document.addEventListener('change',function(e){var t=e.target;if(t&&t.id&&/^(shipping|billing)-postcode$/.test(t.id))onPlz(t);},true);

  /* ---------- Nach "Bestellen": zum ersten Fehler + Hinweis ---------- */
  var toast=null,tt=null;
  function showToast(h,s){
    if(!toast){toast=document.createElement('div');toast.id='sp-kux-toast';toast.setAttribute('role','alert');document.body.appendChild(toast);}
    toast.innerHTML=h+(s?'<small>'+s+'</small>':'');toast.classList.add('on');
    clearTimeout(tt);tt=setTimeout(function(){toast.classList.remove('on');},5200);
  }
  function label(el){
    var w=el.closest('.wc-block-components-text-input,.wc-block-components-combobox,.wc-block-components-country-input,.wc-block-components-state-input,.wc-block-components-select-input,.wc-block-components-checkbox,.wc-block-components-address-form__country,.wc-block-components-address-form__state');
    var l=w&&w.querySelector('label');var t=l?l.textContent.replace(/\(optional\)|\*/gi,'').trim():'';
    if(/mindestens 18|AGB/i.test(t))return 'Häkchen bei 18+/AGB';
    if(/Land|Region/i.test(t))return 'Land';
    return t.length>28?'':t;
  }
  function check(){
    var errs=[].slice.call(document.querySelectorAll('.wc-block-checkout .has-error, .wc-block-checkout .wc-block-components-validation-error'))
      .filter(function(e){return e.offsetParent!==null;});
    if(!errs.length)return;
    var first=errs[0],field=first.classList.contains('has-error')?first:(first.closest('.has-error')||first.parentNode);
    var names=[];errs.forEach(function(e){var f=e.classList.contains('has-error')?e:e.closest('.has-error');var n=f?label(f):'';if(n&&names.indexOf(n)<0)names.push(n);});
    var r=field.getBoundingClientRect();
    if(r.top<70||r.bottom>window.innerHeight-90){
      var y=window.scrollY+r.top-Math.max(90,(window.innerHeight-r.height)/2.6);window.scrollTo({top:Math.max(0,y),behavior:'smooth'});
    }
    var inp=field.querySelector('input:not([type=hidden]),select');
    if(inp&&inp.type!=='checkbox'){setTimeout(function(){try{inp.focus({preventScroll:true});}catch(e){}},450);}
    field.classList.add('sp-kux-flash');setTimeout(function(){field.classList.remove('sp-kux-flash');},1500);
    var n=names.length;
    showToast(n>1?'Es fehlen noch '+n+' Angaben':'Fast geschafft – eine Angabe fehlt noch',names.length?names.slice(0,4).join(' · '):'Bitte die rot markierten Felder prüfen');
  }
  document.addEventListener('click',function(e){
    var b=e.target.closest&&e.target.closest('.wc-block-components-checkout-place-order-button');
    if(!b)return;setTimeout(check,450);setTimeout(function(){if(!toast||!toast.classList.contains('on'))check();},1300);
  },true);
})();
</script>
    <?php
}, 120);
