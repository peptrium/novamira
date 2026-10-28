<?php
/**
 * Plugin Name: SP Warenkorb-Drawer – Gratisversand-Vorschlag + Vertrauenszeile
 * Description: (2026-10-28) Im Warenkorb-Drawer (sp-cart-gifts.php / sp-cart-preview.php):
 * (1) Box "Noch X € bis zum Gratisversand" mit 1–2 passenden Ein-Klick-Vorschlaegen (gleiche Logik wie der
 *     Hinweis in der Kasse, sp-free-shipping-hint.php: Katalog sp_fsh_catalog(), Schwelle sp_fsh_threshold());
 * (2) die Zeile ueber "Zur Kasse" zeigt statt "DHL PREMIUM" drei kurze Vertrauenspunkte.
 * Katalog wird per admin-ajax nachgeladen (Seiten-Cache-tauglich, nichts Personenbezogenes im HTML).
 * Klicks werden wie in der Kasse gezaehlt (wp_sp_stats type fshview/fshclick, campaign 'drawer-…').
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_dux_ajax_cat() {
    if (!function_exists('sp_fsh_catalog') || !function_exists('sp_fsh_threshold')) {
        wp_send_json(array('ok' => false));
    }
    $hw = array();
    if (function_exists('sp_hw_active') && sp_hw_active() && function_exists('sp_hw_eligible_ids')) {
        $hw = array_map('intval', (array) sp_hw_eligible_ids());
    }
    wp_send_json(array(
        'ok' => true,
        'min' => (float) sp_fsh_threshold(),
        'topup' => function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 729,
        'cat' => sp_fsh_catalog(),
        'hw' => $hw,
    ));
}
add_action('wp_ajax_sp_dux_cat', 'sp_dux_ajax_cat');
add_action('wp_ajax_nopriv_sp_dux_cat', 'sp_dux_ajax_cat');

add_action('wp_footer', function () {
    if (is_admin() || (function_exists('is_checkout') && is_checkout())) {
        return;
    }
    ?>
<style id="sp-dux-css">
#sp-dux{margin:4px 20px 14px;border:1px solid #E3E6E9;border-radius:16px;background:#F7F8F9;padding:14px 14px 6px;font-family:Sora,sans-serif;color:#0D0F12}
#sp-dux[hidden]{display:none}
#sp-dux .h{display:flex;align-items:center;gap:9px;font-size:13.5px;line-height:1.35;margin:0 0 9px}
#sp-dux .h svg{flex:0 0 20px;width:20px;height:20px;color:#0D0F12}
#sp-dux .h b{font-weight:800}
#sp-dux .bar{height:5px;border-radius:99px;background:#E2E5E8;overflow:hidden;margin:0 0 6px}
#sp-dux .bar span{display:block;height:100%;border-radius:99px;background:#0D0F12;transition:width .45s ease}
#sp-dux .it{display:flex;align-items:center;gap:11px;padding:10px 0;border-top:1px solid #E7E9EC}
#sp-dux .it:first-of-type{border-top:0}
#sp-dux .it img{width:44px;height:44px;border-radius:10px;object-fit:cover;background:#fff;flex:none}
#sp-dux .it .n{flex:1;min-width:0;font-size:13px;font-weight:700;line-height:1.3}
#sp-dux .it .n small{display:block;font-weight:500;color:#5B6169;font-size:11.5px;margin-top:2px}
#sp-dux .it .n small b{color:#0B6B3A;font-weight:700}
#sp-dux .it button{all:unset;box-sizing:border-box;flex:none;display:inline-flex!important;align-items:center;gap:4px;height:34px!important;padding:0 12px!important;border-radius:999px!important;background:#0D0F12!important;color:#fff!important;font:700 12.5px/1 Sora,sans-serif!important;cursor:pointer;white-space:nowrap;box-shadow:none!important}
#sp-dux .it button[disabled]{opacity:.5;cursor:default}
#sp-dux .pair{font-size:11.5px;color:#0B6B3A;font-weight:700;margin:0 0 8px}
#sp-cart-drawer #sp-cd-dhl.sp-dux-tr{display:flex;justify-content:center;flex-wrap:wrap;gap:4px 12px;font-size:11.5px;font-weight:600;color:#5A6068;margin:0 0 10px}
#sp-cart-drawer #sp-cd-dhl.sp-dux-tr.is-hidden{display:none}
#sp-cd-dhl.sp-dux-tr i{font-style:normal;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
#sp-cd-dhl.sp-dux-tr svg{width:13px;height:13px;color:#2E9B57;flex:none}
</style>
<script id="sp-dux-js">
(function(){
  var AJAX=<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
  var drawer=document.getElementById('sp-cart-drawer'),items=document.getElementById('sp-cd-items');
  if(!drawer||!items)return;
  /* ---- Vertrauenszeile ueber "Zur Kasse" ---- */
  var dhl=document.getElementById('sp-cd-dhl');
  if(dhl){
    var ck='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
    dhl.classList.add('sp-dux-tr');
    dhl.innerHTML='<i>'+ck+'Vorkasse oder Krypto</i><i>'+ck+'In 2 Werktagen da</i><i>'+ck+'Diskret verpackt</i>';
  }
  /* ---- Gratisversand-Vorschlag ---- */
  var MIN=0,TOPUP=729,CAT=null,P={},V2P={},COMP={},QTY={ids:[],tiers:[]},HW=[],busy=false,seen=false;
  var box=document.createElement('div');box.id='sp-dux';box.hidden=true;
  items.parentNode.insertBefore(box,items.nextSibling);
  var dec=document.createElement('textarea');function txt(h){dec.innerHTML=h||'';return dec.value;}
  function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');}
      function money(v,t){
        var d=t&&t.currency_minor_unit!=null?t.currency_minor_unit:2,s=(Math.round(v*100)/100).toFixed(d).split('.');
        var ts=t&&t.currency_thousand_separator!=null?t.currency_thousand_separator:'.',ds=t&&t.currency_decimal_separator?t.currency_decimal_separator:',';
        s[0]=s[0].replace(/\B(?=(\d{3})+(?!\d))/g,ts);
        return (t&&t.currency_prefix||'')+s.join(ds)+(t&&t.currency_suffix!=null?t.currency_suffix:' €');
      }
      function isAbo(i){return (i.item_data||[]).some(function(d){return /abo|liefer|intervall/i.test((d.name||d.key||'')+' '+(d.value||''));});}
      function state(){
        var st=sel();if(!st||!st.getCartData)return null;
        var c=st.getCartData();if(!c||!c.items)return null;
        var t=c.totals||{},u=Math.pow(10,t.currency_minor_unit||2);
        var basis=(+t.total_items+(+t.total_items_tax||0)-(+t.total_discount||0)-(+t.total_discount_tax||0))/u,goods=0,ids={};
        c.items.forEach(function(i){
          ids[i.id]=1;
          var line=((+i.totals.line_subtotal||0)+(+i.totals.line_subtotal_tax||0))/u;
          if(i.id===TOPUP){basis-=line;}else{goods+=line;}
        });
        var free=false;(c.shippingRates||[]).forEach(function(p){(p.shipping_rates||[]).forEach(function(r){if(r.method_id==='free_shipping')free=true;});});
        return {c:c,u:u,basis:Math.max(0,basis),goods:goods,ids:ids,free:free,country:(c.shippingAddress&&c.shippingAddress.country)||'',totals:t};
      }
      function parentOf(id){return V2P[id]?+V2P[id]:+id;}
      /* Zeilenpreis mit Mengenrabatt (wie sp-quantity-discount.php: nur die Stueck im groessten erreichten Paket rabattiert) */
      function lineTotal(pid,unit,q,itemId){
        if(QTY.ids.indexOf(pid)===-1||(QTY.sale||[]).indexOf(itemId)>-1)return unit*q;
        for(var k=0;k<QTY.tiers.length;k++){var t=QTY.tiers[k];if(q>=t.size){return Math.round(unit*(1-t.percent/100)*100)/100*t.size+unit*(q-t.size);}}
        return unit*q;
      }
      function tierAt(pid,q,itemId){if(QTY.ids.indexOf(pid)===-1||(QTY.sale||[]).indexOf(itemId)>-1)return null;for(var k=0;k<QTY.tiers.length;k++){if(QTY.tiers[k].size===q)return QTY.tiers[k];}return null;}
      function pickVar(prod,rest){var vs=prod.vars.slice().sort(function(a,b){return a.price-b.price;});for(var k=0;k<vs.length;k++){if(vs[k].price>=rest)return vs[k];}return vs[0];}
      function suggestions(s,rest){
        var inCart={},cands={},vial=false,pen=false,names={};
        s.c.items.forEach(function(i){var pp=parentOf(i.id);inCart[pp]=1;names[pp]=txt(i.name);if(P[pp]&&!P[pp].acc){if(P[pp].pen)pen=true;else vial=true;}});
        function put(c){if(c.price>rest+150)return;var k=c.kind+':'+(c.key||c.id);if(!cands[k]||cands[k].rel<c.rel)cands[k]=c;}
        /* 1) Noch 1x vom selben Produkt - mit echtem Mehrpreis inkl. Mengenrabatt */
        s.c.items.forEach(function(i){
          var onSale=(QTY.sale||[]).indexOf(i.id)>-1,unit=((onSale?+i.prices.price:+i.prices.regular_price)||+i.prices.price||0)/s.u,lim=i.quantity_limits||{},pp=parentOf(i.id);
          if((+i.prices.price||0)<=0||i.id===TOPUP||i.sold_individually||lim.editable===false||i.quantity+1>(lim.maximum||9999)||isAbo(i))return;
          var now=((+i.totals.line_subtotal||0)+(+i.totals.line_subtotal_tax||0))/s.u,extra=Math.max(0,lineTotal(pp,unit,i.quantity+1,i.id)-now),t=tierAt(pp,i.quantity+1,i.id);
          /* Rabatt-Hinweis nur, wenn das zusaetzliche Stueck wirklich guenstiger ist als sein normaler Preis
             (sp-quantity-discount.php rechnet vom regulaeren Preis - bei Angebotspreisen kann das teurer werden) */
          if(t&&extra>=(+i.prices.price||0)/s.u-0.005)t=null;
          var v=(i.variation||[]).map(function(x){return x.value;}).join(' ');
          put({kind:'more',pid:pp,key:i.key,qty:i.quantity,price:extra,name:'Noch 1× '+txt(i.name)+(v?' '+v:''),img:(i.images&&i.images[0]&&i.images[0].thumbnail)||'',why:t?('ab '+t.size+' Stück −'+t.percent+' %'):'',rel:t?42:15});
        });
        /* 2) Passende Ergaenzung (gepflegte Kombinationen + gemeinsam gekauft) */
        Object.keys(inCart).forEach(function(pp){
          var m=COMP[pp]||{};
          Object.keys(m).forEach(function(q){
            q=+q;if(inCart[q]||!P[q]||m[q]<2)return; /* Einzelfall (1x zusammen gekauft) ist kein Muster */
            var vr=pickVar(P[q],rest);
            put({kind:'add',pid:q,id:vr.id,price:vr.price,name:P[q].name+(vr.label?' '+vr.label:''),img:P[q].img,why:'Oft zusammen mit '+(names[pp]||'deiner Bestellung')+' gekauft',rel:30+Math.min(30,m[q]*3)});
          });
        });
        /* 3) Zubehoer, das zur Bestellung gehoert */
        var need=[];if(vial){need.push([74,'Zum Anmischen'],[80,'Passendes Zubehör']);}if(pen){need.push([908,'Passend zum Pen']);}
        need.forEach(function(n){if(inCart[n[0]]||!P[n[0]])return;var vr=P[n[0]].vars[0];put({kind:'add',pid:n[0],id:vr.id,price:vr.price,name:P[n[0]].name,img:P[n[0]].img,why:n[1],rel:n[0]===74?40:35}); /* Bac Water ist bei Flaeschchen fast immer dabei */});
        var list=Object.keys(cands).map(function(k){var c=cands[k];c.closes=c.price>=rest-0.005;c.score=c.closes?100+c.rel-(c.price-rest)/4:c.rel+10*c.price/rest;return c;});
        list.sort(function(a,b){return b.score-a.score;});
        var out=list.slice(0,1),pair=false;
        if(out.length&&out[0].closes){
          /* Erster Vorschlag reicht schon - als zweiten den inhaltlich passendsten statt einer zweiten teuren Alternative */
          var restList=list.slice(1).sort(function(a,b){return b.rel-a.rel||a.price-b.price;});
          if(restList.length)out.push(restList[0]);
        }else{out=list.slice(0,2);}
        if(out.length===2&&!out[0].closes&&!out[1].closes&&out[0].price+out[1].price>=rest)pair=true;
        if(!out.length||(!out[0].closes&&!pair)){
          /* Kein Einzelartikel reicht: zwei passende, die zusammen reichen? */
          var nc=list.filter(function(c){return !c.closes;});
          outer:for(var a=0;a<nc.length;a++){for(var b=a+1;b<nc.length;b++){if(nc[a].price+nc[b].price>=rest&&(nc[a].key||nc[a].id)!==(nc[b].key||nc[b].id)){out=[nc[a],nc[b]];pair=true;break outer;}}}
        }
        return {items:out,pair:pair};
      }
      var QS='style="all:unset;box-sizing:border-box;display:inline-flex !important;align-items:center;justify-content:center;width:28px !important;height:28px !important;min-height:0 !important;padding:0 !important;border:1px solid #DCDEE2 !important;border-radius:8px !important;background:#fff !important;color:#0D0F12 !important;font:600 16px/1 Sora,sans-serif !important;box-shadow:none !important;cursor:pointer"';
      var RS='style="all:unset;cursor:pointer;margin-left:8px !important;padding:0 !important;min-height:0 !important;color:#8A9099 !important;background:none !important;border:0 !important;box-shadow:none !important;font:500 12px Sora,sans-serif !important;text-decoration:underline !important;width:auto !important;height:auto !important;white-space:nowrap !important"';
      var PS='style="all:unset;box-sizing:border-box;flex:none;display:inline-flex !important;align-items:center;justify-content:center;width:36px !important;height:36px !important;min-height:0 !important;padding:0 !important;border:0 !important;border-radius:50% !important;background:#0D0F12 !important;color:#fff !important;font:500 22px/1 Sora,sans-serif !important;box-shadow:none !important;cursor:pointer"';
  function state(c){
    if(!c||!c.items)return null;
    var t=c.totals||{},u=Math.pow(10,t.currency_minor_unit||2);
    var basis=(+t.total_items+(+t.total_items_tax||0)-(+t.total_discount||0)-(+t.total_discount_tax||0))/u,ids={};
    c.items.forEach(function(i){ids[i.id]=1;if(i.id===TOPUP){basis-=((+i.totals.line_subtotal||0)+(+i.totals.line_subtotal_tax||0))/u;}});
    var free=false;(c.shipping_rates||[]).forEach(function(p){(p.shipping_rates||[]).forEach(function(r){if(r.method_id==='free_shipping')free=true;});});
    return {c:c,u:u,basis:Math.max(0,basis),ids:ids,free:free,country:(c.shipping_address&&c.shipping_address.country)||'',totals:t};
  }
  function loadCat(){
    if(CAT)return Promise.resolve(CAT);
    try{var s=sessionStorage.getItem('sp_dux_cat');if(s){var j=JSON.parse(s);if(j&&j.t>Date.now()-30*60000)return Promise.resolve(use(j.d));}}catch(e){}
    var f=new FormData();f.append('action','sp_dux_cat');
    return fetch(AJAX,{method:'POST',body:f,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(d){
      if(!d||!d.ok)return null;try{sessionStorage.setItem('sp_dux_cat',JSON.stringify({t:Date.now(),d:d}));}catch(e){}return use(d);
    });
  }
  function use(d){MIN=+d.min||0;TOPUP=+d.topup||729;CAT=d.cat||{};P=CAT.products||{};V2P=CAT.var2parent||{};COMP=CAT.comp||{};QTY=CAT.qty||{ids:[],tiers:[]};HW=d.hw||[];return CAT;}
  var ic='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></svg>';
  function render(c){
    var s=state(c);
    if(!s||!CAT||!MIN||!s.c.items.length||s.free||(s.country&&s.country!=='DE')||s.basis>=MIN){box.hidden=true;box.innerHTML='';return;}
    var rest=MIN-s.basis,sg=suggestions(s,rest);
    sg.items=sg.items.filter(function(x){return !(x.kind==='more'&&HW.indexOf(x.pid)>-1);});
    var h='<p class="h">'+ic+'<span><b>Gratisversand mit einem Klick</b><br>noch '+money(rest,s.totals)+' bis versandkostenfrei</span></p><div class="bar"><span style="width:'+Math.min(100,Math.round(s.basis/MIN*100))+'%"></span></div>';
    if(sg.pair&&sg.items.length===2)h+='<p class="pair">Mit beidem zusammen versandkostenfrei</p>';
    sg.items.forEach(function(x){
      var why=x.closes?'<b>✓ Gratisversand</b>'+(x.why?' · '+esc(x.why):''):esc(x.why||'');
      h+='<div class="it">'+(x.img?'<img src="'+esc(x.img)+'" alt="" loading="lazy">':'')+'<div class="n">'+esc(x.name)+(why?'<small>'+why+'</small>':'')+'</div>'+
        '<button type="button" data-id="'+(x.kind==='more'?(s.c.items.filter(function(i){return i.key===x.key;})[0]||{}).id:x.id)+'" data-pid="'+x.pid+'" data-price="'+(Math.round(x.price*100)/100)+'"'+(x.kind==='more'?' data-more="1"':'')+'>+ '+money(x.price,s.totals)+'</button></div>';
    });
    if(box.getAttribute('data-k')!==h){box.setAttribute('data-k',h);box.innerHTML=h;}
    box.hidden=false;
    /* bestehenden "Passt dazu"-Vorschlag desselben Produkts nicht doppelt zeigen */
    var ids=sg.items.map(function(x){return String(x.id||'');});
    [].forEach.call(items.querySelectorAll('.sp-cd-suggest[data-addon-id]'),function(r){r.style.display=ids.indexOf(r.getAttribute('data-addon-id'))>-1?'none':'';});
    if(!seen&&sg.items.length&&drawer.classList.contains('open')){seen=true;ev('view');}
  }
  function ev(t,b){try{var f=new FormData();f.append('action','sp_fsh_ev');f.append('t',t);if(b){f.append('pid',b.getAttribute('data-pid')||0);f.append('k',b.hasAttribute('data-more')?'more':'add');f.append('p',b.getAttribute('data-price')||0);}navigator.sendBeacon?navigator.sendBeacon(AJAX,f):fetch(AJAX,{method:'POST',body:f});}catch(e){}}
  var tm=null;
  function refresh(){
    if(tm)return;tm=setTimeout(function(){tm=null;
      if(!drawer.classList.contains('open'))return;
      Promise.all([loadCat(),fetch('/wp-json/wc/store/v1/cart',{credentials:'same-origin'}).then(function(r){return r.json();})])
        .then(function(r){if(!busy)render(r[1]);}).catch(function(){});
    },350);
  }
  box.addEventListener('click',function(e){
    var b=e.target.closest('button[data-id]');if(!b||busy)return;
    busy=true;[].forEach.call(box.querySelectorAll('button'),function(x){x.disabled=true;});
    ev('click',b);
    var f=new FormData();f.append('product_id',b.getAttribute('data-id'));f.append('quantity','1');
    fetch('/?wc-ajax=add_to_cart',{method:'POST',body:f,credentials:'same-origin'}).then(function(r){return r.json();})
      .then(function(d){busy=false;if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[d&&d.fragments,d&&d.cart_hash]);refresh();})
      .catch(function(){busy=false;refresh();});
  });
  new MutationObserver(refresh).observe(items,{childList:true,subtree:true});
  new MutationObserver(function(){if(drawer.classList.contains('open'))refresh();else seen=false;}).observe(drawer,{attributes:true,attributeFilter:['class']});
})();
</script>
    <?php
}, 110);
