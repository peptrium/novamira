<?php
/**
 * Plugin Name: SP Warenkorb-Vorschau
 * Description: Entwurf des neuen Warenkorb-Drawers (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * Reines CSS/JS-Overlay ueber den Drawer aus sp-cart-gifts.php - Logik (Store API, Geschenke,
 * Gutscheine, Vorschlaege) bleibt unveraendert. Geschenkstufen als kompakte Fortschrittsleiste
 * (#sp-gx) statt 4 grosser Kaesten; die Originalleiste #sp-rb wird nur versteckt und weiter
 * als Datenquelle gelesen. Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_cxp_active() {
    return function_exists('sp_hpv_token_ok') && sp_hpv_token_ok() && !is_admin();
}

add_action('wp_footer', function () {
    if (!sp_cxp_active()) {
        return;
    }
    $imgs = [];
    foreach (function_exists('sp_gift_tiers') ? sp_gift_tiers() : [] as $t) {
        $gp = wc_get_product($t['variation_id'] ?: $t['product_id']);
        $iid = $gp ? ($gp->get_image_id() ?: (wc_get_product($t['product_id']) ? wc_get_product($t['product_id'])->get_image_id() : 0)) : 0;
        if ($iid && empty($imgs[(int) $t['threshold']])) {
            $imgs[(int) $t['threshold']] = wp_get_attachment_image_url($iid, 'thumbnail');
        }
    }
    ?>
<style id="sp-cxp-css">
body.sp-cd-on #sp-hpv-flag{display:none!important}
#sp-cart-drawer{width:420px}
#sp-cart-drawer .sp-cd-header{padding:14px 18px 14px 20px;border-bottom:1px solid #EEF0F2}
#sp-cart-drawer .sp-cd-title{font:700 19px/1.2 Sora,sans-serif;color:#0D0F12;display:flex;align-items:baseline;gap:8px}
#sp-cart-drawer .sp-cd-title i{font-style:normal;font-size:13px;font-weight:600;color:#80868E}
#sp-cd-close{width:38px;height:38px;border-radius:50%!important;background:#F2F3F4!important;display:flex!important;align-items:center;justify-content:center;font-size:22px!important;color:#0D0F12!important;padding:0!important}
#sp-rb{display:none!important}
#sp-gx{background:linear-gradient(180deg,#0D0F12 0%,#1B1F24 100%);color:#fff;padding:16px 20px 14px;font-family:Sora,sans-serif}
#sp-gx .hd{display:flex;align-items:center;gap:10px;margin:0 0 14px}
#sp-gx .hd .ic{flex:0 0 40px;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);display:flex;align-items:center;justify-content:center;overflow:hidden;color:#E6E9EC}
#sp-gx .hd .ic img{width:100%;height:100%;object-fit:cover}
#sp-gx .hd .ic svg{width:19px;height:19px}
#sp-gx .hd .ic.ok{background:#2E9B57;border-color:#7DDBA0;color:#fff}
#sp-gx .hd p{margin:0;font-size:13.5px;line-height:1.4;color:#C9CDD2}
#sp-gx .hd p b{color:#fff;font-weight:700}
#sp-gx .hd p small{display:block;font-size:11.5px;color:#7DDBA0;font-weight:600;margin-top:2px}
#sp-gx .tr{position:relative;height:6px;border-radius:99px;background:rgba(255,255,255,.12);margin:0 10px 0 4px}
#sp-gx .fl{position:absolute;left:0;top:0;bottom:0;border-radius:99px;background:linear-gradient(90deg,#8E96A0,#F2F3F4);transition:width .6s cubic-bezier(.34,1.2,.4,1)}
#sp-gx .ms{position:absolute;top:50%;width:30px;height:30px;margin:-15px 0 0 -15px;border-radius:50%;background:#2A2F35;border:2px solid #4B5157;display:flex;align-items:center;justify-content:center;font:800 10px/1 Sora,sans-serif;color:#fff;transition:all .3s}
#sp-gx .ms.done{background:#2E9B57;border-color:#7DDBA0}
#sp-gx .ms.next{border-color:#F2F3F4;box-shadow:0 0 0 4px rgba(242,243,244,.12)}
#sp-gx .lb{position:relative;height:30px;margin:12px 10px 0 4px}
#sp-gx .ms img{width:100%;height:100%;border-radius:50%;object-fit:cover;filter:grayscale(1);opacity:.55;transition:all .3s}
#sp-gx .ms svg{width:13px;height:13px;color:#C9CDD2}
#sp-gx .ms.done img{filter:none;opacity:1}
#sp-gx .ms.next img{opacity:.9;filter:grayscale(.3)}
#sp-gx .ms.done svg{color:#fff}
#sp-gx .ms.done.im:after{content:'✓';position:absolute;right:-5px;bottom:-5px;width:14px;height:14px;border-radius:50%;background:#2E9B57;border:1.5px solid #15181C;font:800 8px/14px Sora,sans-serif;text-align:center;color:#fff}
#sp-gx .ms.im{background:#15181C;padding:0}
#sp-gx .lb span{position:absolute;top:0;transform:translateX(-50%);text-align:center;font-size:10.5px;line-height:1.25;color:#80868E;white-space:nowrap;font-weight:600}
#sp-gx .lb span:last-child{transform:translateX(calc(-100% + 10px));text-align:right}
#sp-gx .lb span:first-child{transform:translateX(-50%)}
#sp-gx .lb span em{display:block;font-style:normal;font-weight:700;color:#C9CDD2}
#sp-gx .lb span.done,#sp-gx .lb span.done em{color:#7DDBA0}
#sp-gx .lb span.next em{color:#fff}
#sp-cart-drawer .sp-cd-items{padding:2px 20px 8px}
#sp-cart-drawer .sp-cd-item{gap:14px;padding:16px 0;border-bottom:1px solid #EEF0F2;align-items:center}
#sp-cart-drawer .sp-cd-item img{width:68px;height:68px;border-radius:14px;box-shadow:0 4px 12px rgba(13,15,18,.12)}
#sp-cart-drawer .sp-cd-item-name{font-size:14.5px;font-weight:700;line-height:1.3;margin:0 0 2px}
#sp-cart-drawer .sp-cd-item-vol{font-size:12.5px;color:#80868E;margin:0 0 8px}
#sp-cart-drawer .sp-cd-item-right{align-self:stretch;justify-content:space-between}
#sp-cart-drawer .sp-cd-item-price{font-size:15px;font-weight:800}
#sp-cart-drawer .sp-cd-item-remove{background:transparent!important;box-shadow:none!important;color:#A8B0B9;width:30px;height:30px;border-radius:50%;margin:-6px -6px 0 0}
#sp-cart-drawer .sp-cd-item-remove:hover{background:#F2F3F4;color:#0D0F12}
#sp-cart-drawer .sp-cd-qty{gap:0;height:34px;border:1px solid #DCDEE0;border-radius:999px;padding:0 3px;background:#fff}
#sp-cart-drawer .sp-cd-qty-btn{width:28px;height:28px;border:none!important;border-radius:50%!important;background:transparent!important;font-size:17px;font-weight:600;color:#0D0F12!important;padding:0!important;box-shadow:none!important}
#sp-cart-drawer .sp-cd-qty-btn:hover{background:#F2F3F4!important}
#sp-cart-drawer .sp-cd-qty-val{min-width:24px;font-size:14px}
#sp-cart-drawer .sp-cd-item-save{background:#E8F5EC;color:#1F7A4D}
#sp-cart-drawer .sp-cd-suggest{border:1px solid #E3E6E9;border-radius:14px;background:#F6F7F8;padding:12px 12px 12px 14px;margin:14px 0 6px;position:relative}
#sp-cart-drawer .sp-cd-suggest:before{content:'Passt dazu';position:absolute;top:-9px;left:12px;background:#fff;border:1px solid #E3E6E9;border-radius:99px;padding:1px 8px;font:700 9.5px/1.5 Sora,sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#4B5157}
#sp-cart-drawer .sp-cd-suggest-name{font-size:13.5px;font-weight:700}
#sp-cart-drawer .sp-cd-suggest-hint{font-size:12px;color:#80868E}
#sp-cart-drawer .sp-cd-suggest-btn{flex-direction:row!important;align-items:center;gap:6px;background:#0D0F12!important;border:none!important;border-radius:999px!important;padding:9px 14px!important;color:#fff!important}
#sp-cart-drawer .sp-cd-suggest-btn-label{display:none}
#sp-cart-drawer .sp-cd-suggest-btn-price{font-size:13px!important;font-weight:700;color:#fff!important}
#sp-cart-drawer .sp-cd-footer{padding:12px 20px calc(12px + env(safe-area-inset-bottom));border-top:1px solid #EEF0F2;box-shadow:0 -10px 24px rgba(13,15,18,.05)}
#sp-cart-drawer .sp-cd-subtotal{font-size:16px;font-weight:700;margin:2px 0 6px}
#sp-cart-drawer .sp-cd-subtotal span{font-size:18px;font-weight:800}
#sp-cart-drawer .sp-cd-dhl{font-size:12px;color:#5A6068;margin:0 0 12px}
#sp-cart-drawer .sp-cx-btns{display:grid;grid-template-columns:1fr 1fr;gap:10px}
#sp-cart-drawer #sp-cd-checkout-btn{height:54px;margin:0!important;display:flex;align-items:center;justify-content:center;gap:10px;border-radius:14px;font-size:16px;font-weight:700;padding:0 18px;margin-bottom:4px;box-shadow:0 6px 14px rgba(13,15,18,.16)}
#sp-cart-drawer #sp-cd-checkout-btn:before{content:'';width:16px;height:16px;flex:0 0 16px;background:no-repeat center/contain url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='4' y='11' width='16' height='10' rx='2'/%3E%3Cpath d='M8 11V7a4 4 0 018 0v4'/%3E%3C/svg%3E")}
#sp-cart-drawer #sp-cd-continue{height:54px;margin:0!important;background:#fff!important;border:1.5px solid #DCDEE0!important;border-radius:14px!important;box-shadow:none!important;padding:0 10px!important;font-size:14px!important;font-weight:700!important;color:#0D0F12!important;line-height:1.2}
#sp-cart-drawer #sp-cd-continue:hover{border-color:#0D0F12!important}
#sp-cart-drawer .sp-cd-coupon-toggle{font-size:12.5px;display:inline-flex;align-items:center;gap:6px}
#sp-cart-drawer .sp-cd-empty{padding:56px 10px}
#sp-cart-drawer .sp-cd-empty:before{content:'🛒';display:block;font-size:40px;margin:0 0 12px;opacity:.8}
#sp-cart-drawer .sp-cd-empty p{font-size:16px;font-weight:700;margin:0 0 6px}
#sp-cart-drawer .sp-cd-empty p:after{content:'Entdecke unsere Forschungspeptide – ab 100 € versandkostenfrei.';display:block;font-size:13px;font-weight:400;color:#80868E;margin-top:6px;line-height:1.5}
#sp-cart-drawer .sp-cd-empty .sp-cd-btn{margin-top:14px;max-width:240px;border-radius:14px}
#sp-cart-drawer #sp-cd-checkout-btn.is-hidden,#sp-cart-drawer.sp-cx-empty #sp-gx,#sp-cart-drawer.sp-cx-empty .sp-cd-footer{display:none!important}
@media(max-width:480px){#sp-cart-drawer{width:100vw}}
</style>
<script id="sp-cxp-js">
(function(){
 var dr=document.getElementById('sp-cart-drawer'),rb=document.getElementById('sp-rb');if(!dr||!rb)return;
 /* Body-Klasse, solange der Drawer offen ist (Vorschau-Pille ausblenden) */
 function syncOpen(){document.body.classList.toggle('sp-cd-on',dr.classList.contains('open'));}
 new MutationObserver(syncOpen).observe(dr,{attributes:true,attributeFilter:['class']});syncOpen();
 /* Anzahl Artikel im Titel */
 var title=dr.querySelector('.sp-cd-title'),cnt=document.createElement('i');if(title)title.appendChild(cnt);
 var items=document.getElementById('sp-cd-items');
 function syncCount(){var n=0;[].forEach.call(items.querySelectorAll('.sp-cd-item'),function(it){var q=it.querySelector('.sp-cd-qty-val');n+=q?(parseInt(q.textContent,10)||0):1;});cnt.textContent=n?n+' Artikel':'';dr.classList.toggle('sp-cx-empty',!!items.querySelector('.sp-cd-empty'));}
 if(items){new MutationObserver(syncCount).observe(items,{childList:true,subtree:true,characterData:true});syncCount();}
 /* Kompakte Geschenk-Leiste aus den Daten der Original-Leiste */
 var gx=document.createElement('div');gx.id='sp-gx';rb.parentNode.insertBefore(gx,rb);
 function num(t){return parseFloat(String(t).replace(/[^0-9,.]/g,'').replace(/\./g,'').replace(',','.'))||0;}
 function eur(v){var r=Math.round(v*100)/100,hc=Math.abs(r-Math.round(r))>.001;return r.toLocaleString('de-DE',{minimumFractionDigits:hc?2:0,maximumFractionDigits:2})+' €';}
 function shortL(l){l=l.replace(/\s*NEU\s*$/,'').replace(/\s+gratis$/i,'').trim();if(/^gratisversand$/i.test(l))return 'Versand';return l.split(/\s*\+\s*/)[0];}
 var IMG=<?php echo wp_json_encode((object) $imgs); ?>;
 var TRUCK='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4h14v12H1zM15 9h4l3 3v4h-7"/><circle cx="5.5" cy="18.5" r="2"/><circle cx="18.5" cy="18.5" r="2"/></svg>',GIFT='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v9h14v-9M7.5 8a2.5 2.5 0 010-5C11 3 12 8 12 8s1-5 4.5-5a2.5 2.5 0 010 5"/></svg>',CHECK='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>';
 function icon(t){var u=IMG[Math.round(t.a)];return u?'<img src="'+u+'" alt="">':(/versand/i.test(t.l)?TRUCK:GIFT);}
 var raf=0;
 function paint(){raf=0;var boxes=[].slice.call(rb.querySelectorAll('.sp-rb-box'));if(!boxes.length){gx.style.display='none';return;}gx.style.display='';
   var T=boxes.map(function(b){var lbl=b.querySelector('.sp-rb-lbl');var l=lbl?lbl.childNodes[0].textContent.trim():'';var ic=b.querySelector('.sp-rb-ic img');
     return {a:num(b.querySelector('.sp-rb-amt').textContent),l:l,ic:ic?ic.getAttribute('alt'):'🎁',done:b.classList.contains('done'),act:b.classList.contains('active'),rem:num((b.querySelector('.sp-rb-remain')||{}).textContent||'')};});
   var max=T[T.length-1].a,total=max,nx=-1;
   T.forEach(function(t,i){if(nx<0&&t.act){nx=i;total=t.a-t.rem;}});
   if(nx<0){T.forEach(function(t,i){if(nx<0&&!t.done){nx=i;total=0;}});}
   var done=T.filter(function(t){return t.done;});
   var msg;
   if(nx<0){msg='<span class="ic ok">'+CHECK+'</span><p><b>Alle Geschenke freigeschaltet!</b><small>Sie liegen automatisch in deinem Warenkorb.</small></p>';}
   else{var t=T[nx];msg='<span class="ic">'+icon(t)+'</span><p>Noch <b>'+eur(t.a-total)+'</b> bis <b>'+t.l+'</b>'+(done.length?'<small>✓ '+done.map(function(d){return d.l;}).join(' · ')+'</small>':'')+'</p>';}
   /* Stufen gleichmaessig verteilt, Fuellung stueckweise zwischen den Schwellen */
   var n=T.length,pos=function(i){return (i+1)/n*100;},pct=100;
   for(var i=0;i<n;i++){if(total<T[i].a){var pa=i?T[i-1].a:0,pp=i?pos(i-1):0;pct=pp+Math.max(0,total-pa)/(T[i].a-pa)*(pos(i)-pp);break;}}
   var ms='',lb='';
   T.forEach(function(t,i){var p=pos(i).toFixed(2),c=t.done?'done':(i===nx?'next':'');var im=!!IMG[Math.round(t.a)];ms+='<span class="ms '+c+(im?' im':'')+'" style="left:'+p+'%">'+(im?icon(t):(t.done?CHECK:icon(t)))+'</span>';lb+='<span class="'+c+'" style="left:'+p+'%">'+shortL(t.l)+'<em>'+eur(t.a)+'</em></span>';});
   gx.innerHTML='<div class="hd">'+msg+'</div><div class="tr"><div class="fl" style="width:'+pct.toFixed(1)+'%"></div>'+ms+'</div><div class="lb">'+lb+'</div>';
 }
 function queue(){if(!raf)raf=requestAnimationFrame(paint);}
 /* Weiter einkaufen + Zur Kasse nebeneinander */
 var co=document.getElementById('sp-cd-checkout-btn'),cn=document.getElementById('sp-cd-continue');
 if(co&&cn&&co.parentNode===cn.parentNode){var w=document.createElement('div');w.className='sp-cx-btns';co.parentNode.insertBefore(w,co);w.appendChild(cn);w.appendChild(co);}
 new MutationObserver(queue).observe(rb,{childList:true,subtree:true,attributes:true,characterData:true});queue();
})();
</script>
    <?php
}, 100);
