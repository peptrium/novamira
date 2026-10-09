<?php
/**
 * Plugin Name: SP Gratisversand-Hinweis
 * Description: Zeigt in der Kasse "Nur noch X € bis zum Gratisversand" mit Fortschrittsbalken und 1-2 passenden Zubehör-Vorschlägen zum direkten Hinzufügen (2026-10-09).
 *
 * Hintergrund (Analyse-Tab, 90 Tage): 7 Bestellungen lagen knapp (bis 30 €)
 * unter der 100-€-Grenze, nur 4 knapp darüber.
 *
 * Rechnet wie WooCommerce selbst (free_shipping, ignore_discounts = no):
 * Artikel-Zwischensumme inkl. Steuer minus Gutscheinrabatt, ohne
 * Guthaben-Aufladung (sp-abo-wallet-ux.php nimmt sie ebenfalls heraus).
 * Ob Gratisversand tatsaechlich greift, entscheidet die Versandrate im
 * Warenkorb (free_shipping vorhanden) - der Betrag ist nur die Anzeige.
 * Nur fuer Lieferland Deutschland (bzw. noch kein Land gewaehlt), weil nur die
 * Zone Deutschland Gratisversand hat. Liest die Mindestgrenze aus der
 * Versandzone, damit eine spaetere Aenderung in WooCommerce automatisch passt.
 *
 * Achtung: Das Inline-Skript darf das Wort "c-l-a-r-i-t-y" NICHT enthalten -
 * Real Cookie Banner blockiert sonst das ganze Skript bis zur Zustimmung.
 */
if (!defined('ABSPATH')) {
    exit;
}

/** Mindestbetrag der Gratisversand-Methode in der Zone Deutschland (0 = keine). */
function sp_fsh_threshold() {
    foreach (WC_Shipping_Zones::get_zones() as $zone) {
        $codes = array_map(function ($l) {
            return $l->code;
        }, $zone['zone_locations']);
        if (!in_array('DE', $codes, true)) {
            continue;
        }
        foreach ($zone['shipping_methods'] as $m) {
            if ($m->id === 'free_shipping' && $m->enabled === 'yes' && in_array($m->get_option('requires'), array('min_amount', 'either'), true)) {
                return (float) $m->get_option('min_amount');
            }
        }
    }
    return 0.0;
}

/** Zubehoer-Vorschlaege: Produkt-ID => nur zeigen, wenn eines dieser Produkte im Warenkorb ist (leer = immer). */
function sp_fsh_suggestions() {
    return array(
        74 => array(),                // Bac Water
        80 => array(),                // Insulinspritze 10er Pack
        908 => array(393, 395, 396),  // Pen Nadeln - nur wenn ein Peptrium-Pen im Warenkorb ist
    );
}

add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page()) {
        return;
    }
    $min = sp_fsh_threshold();
    if ($min <= 0) {
        return;
    }
    $products = array();
    foreach (sp_fsh_suggestions() as $pid => $requires) {
        $p = wc_get_product($pid);
        if (!$p || $p->get_status() !== 'publish' || !$p->is_purchasable() || !$p->is_in_stock()) {
            continue;
        }
        $req = array();
        foreach ($requires as $rid) {
            $req[] = $rid;
            $rp = wc_get_product($rid);
            if ($rp) {
                $req = array_merge($req, $rp->get_children());
            }
        }
        $img = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail');
        $products[] = array(
            'id' => $pid,
            'name' => $p->get_name(),
            'price' => (float) wc_get_price_to_display($p),
            'img' => $img ?: '',
            'requires' => array_map('intval', $req),
        );
    }
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 729;
    ?>
    <style>
      #sp-fsh{background:#F4F5F6;border:1px solid #E2E4E8;border-radius:14px;padding:14px 16px;margin:0 0 20px;font-family:Sora,sans-serif;color:#0D0F12}
      #sp-fsh .t{font-size:14px;line-height:1.45;margin:0 0 9px}
      #sp-fsh .t b{font-weight:700}
      #sp-fsh .bar{height:7px;border-radius:99px;background:#DCDEE2;overflow:hidden}
      #sp-fsh .bar span{display:block;height:100%;border-radius:99px;background:#0D0F12;transition:width .4s ease}
      #sp-fsh .sug{margin-top:12px;display:flex;flex-direction:column;gap:8px}
      #sp-fsh .sug-h{font-size:12px;color:#5B6169;margin:2px 0 0}
      #sp-fsh .it{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #E2E4E8;border-radius:11px;padding:8px 10px}
      #sp-fsh .it img{width:40px;height:40px;object-fit:cover;border-radius:8px;flex:none;background:#F2F3F4}
      #sp-fsh .it .n{flex:1;min-width:0;font-size:13px;font-weight:600;line-height:1.3;word-break:normal;overflow-wrap:normal;hyphens:auto}
      #sp-fsh .it .n em{display:inline-block;font-style:normal;font-size:11px;font-weight:700;color:#0B6B3A;background:#E3F5EA;border-radius:99px;padding:1px 7px;margin-top:3px}
      #sp-fsh .it .n small{display:block;font-weight:500;color:#5B6169;font-size:12px;margin-top:1px}
      #sp-fsh .it button{flex:none;border:0;border-radius:9px;background:#0D0F12;color:#fff;font:600 12px Sora,sans-serif;padding:8px 10px;cursor:pointer;white-space:nowrap}
      #sp-fsh .it button[disabled]{opacity:.55;cursor:default}
      #sp-fsh.ok{background:#ECF8F1;border-color:#B9E4CC}
      #sp-fsh.ok .t{margin:0;color:#0B6B3A}
    </style>
    <script>
    (function(){
      var MIN=<?php echo wp_json_encode($min); ?>,TOPUP=<?php echo (int) $topup; ?>,PRODUCTS=<?php echo wp_json_encode($products); ?>,busy=false,last='';
      function store(){return window.wp&&wp.data&&wp.data.select&&wp.data.select('wc/store/cart');}
      function money(v,t){
        var d=t&&t.currency_minor_unit!=null?t.currency_minor_unit:2,s=(Math.round(v*100)/100).toFixed(d).split('.');
        var ts=t&&t.currency_thousand_separator!=null?t.currency_thousand_separator:'.',ds=t&&t.currency_decimal_separator?t.currency_decimal_separator:',';
        s[0]=s[0].replace(/\B(?=(\d{3})+(?!\d))/g,ts);
        return (t&&t.currency_prefix||'')+s.join(ds)+(t&&t.currency_suffix!=null?t.currency_suffix:' €');
      }
      function state(){
        var st=store();if(!st||!st.getCartData)return null;
        var c=st.getCartData();if(!c||!c.items||!c.items.length)return null;
        var t=c.totals||{},u=Math.pow(10,t.currency_minor_unit||2);
        var country=(c.shippingAddress&&c.shippingAddress.country)||'';
        var basis=(+t.total_items+(+t.total_items_tax||0)-(+t.total_discount||0)-(+t.total_discount_tax||0))/u,goods=0,ids={};
        c.items.forEach(function(i){
          ids[i.id]=1;
          var line=((+i.totals.line_subtotal||0)+(+i.totals.line_subtotal_tax||0))/u;
          if(i.id===TOPUP){basis-=line;}else{goods+=line;}
        });
        var free=false;(c.shippingRates||[]).forEach(function(p){(p.shipping_rates||[]).forEach(function(r){if(r.method_id==='free_shipping')free=true;});});
        return {basis:Math.max(0,basis),goods:goods,ids:ids,free:free,country:country,totals:t};
      }
      function render(){
        var main=document.querySelector('.wc-block-checkout__main');
        var s=state(),box=document.getElementById('sp-fsh');
        if(!main||!s||s.goods<=0||(s.country&&s.country!=='DE')){if(box)box.remove();last='';return;}
        var reached=s.free||s.basis>=MIN;
        var sug=reached?[]:PRODUCTS.filter(function(p){
          if(s.ids[p.id])return false;
          if(p.requires.length&&!p.requires.some(function(r){return s.ids[r];}))return false;
          return true;
        });
        var rest=Math.max(0,MIN-s.basis);
        // Am liebsten zuerst das Zubehoer, das die Luecke allein schliesst.
        sug.sort(function(a,b){return (b.price>=rest)-(a.price>=rest)||a.price-b.price;});
        sug=sug.slice(0,2);
        var key=[reached,rest.toFixed(2),sug.map(function(p){return p.id;}).join(','),busy].join('|');
        if(box&&box.parentNode===main&&key===last)return;
        last=key;
        if(!box){box=document.createElement('div');box.id='sp-fsh';box.lang='de';}
        if(box.parentNode!==main){main.insertBefore(box,main.firstChild);}
        if(reached){
          box.className='ok';
          box.innerHTML='<p class="t">✓ <b>Gratisversand</b> – deine Bestellung wird kostenlos versendet.</p>';
          return;
        }
        box.className='';
        var pct=Math.min(100,Math.round(s.basis/MIN*100));
        var h='<p class="t">🚚 Nur noch <b>'+money(rest,s.totals)+'</b> bis zum <b>Gratisversand</b> (ab '+money(MIN,s.totals)+').</p><div class="bar"><span style="width:'+pct+'%"></span></div>';
        if(sug.length){
          h+='<div class="sug"><p class="sug-h">Passt dazu:</p>';
          sug.forEach(function(p){
            h+='<div class="it">'+(p.img?'<img src="'+p.img+'" alt="">':'')+'<div class="n">'+p.name.replace(/</g,'&lt;')+'<small>'+money(p.price,s.totals)+'</small>'+(p.price>=rest?'<em>damit Gratisversand</em>':'')+'</div><button type="button" data-id="'+p.id+'"'+(busy?' disabled':'')+'>+ Dazu</button></div>';
          });
          h+='</div>';
        }
        box.innerHTML=h;
      }
      document.addEventListener('click',function(e){
        var b=e.target.closest&&e.target.closest('#sp-fsh button[data-id]');if(!b||busy)return;
        e.preventDefault();busy=true;b.textContent='Wird hinzugefügt …';b.disabled=true;
        var id=+b.getAttribute('data-id'),d=wp.data.dispatch('wc/store/cart');
        var p=d&&d.addItemToCart?d.addItemToCart(id,1):Promise.reject();
        Promise.resolve(p).catch(function(){location.reload();}).then(function(){busy=false;last='';render();});
      });
      // Drosseln statt entprellen: der Warenkorb-Store meldet staendig Aenderungen, ein
      // Entprell-Timer wuerde dadurch nie ablaufen.
      var t=null;function soon(){if(t)return;t=setTimeout(function(){t=null;safe();},200);}
      function safe(){try{render();}catch(e){}}
      (function wait(n){
        if(window.wp&&wp.data&&wp.data.subscribe&&store()){wp.data.subscribe(soon);new MutationObserver(soon).observe(document.body,{childList:true,subtree:true});safe();return;}
        if(n<60)setTimeout(function(){wait(n+1);},250);
      })(0);
    })();
    </script>
    <?php
}, 40);
