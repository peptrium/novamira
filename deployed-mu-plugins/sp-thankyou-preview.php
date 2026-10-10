<?php
/**
 * Plugin Name: SP Danke-Seite-Vorschau
 * Description: Entwurf der neuen Danke-Seite (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * CSS/JS-Overlay ueber die klassische order-received-Seite (+ sp-thankyou-style.php): dunkler Kopf
 * (Danke + Bestellnummer + Datum/Gesamt/Zahlungsart), bei Vorkasse die Bankdaten zuerst und mit
 * Kopieren-Knoepfen, dann Fahrplan, Artikel als Liste, EINE Adresskarte wenn Rechnung = Lieferung,
 * ohne "Zusaetzliche Informationen" (AGB/18: Ja) und ohne doppelte Versand-Fortschrittsbox.
 * Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    if (!function_exists('sp_hpv_token_ok') || !sp_hpv_token_ok() || !function_exists('is_order_received_page') || !is_order_received_page()) {
        return;
    }
    ?>
<style id="sp-typ-css">
body.woocommerce-order-received .woocommerce-order{max-width:560px!important}
body.woocommerce-order-received .woocommerce-order>p.woocommerce-thankyou-order-received,body.woocommerce-order-received ul.woocommerce-order-overview{display:none!important}
#sp-ty-hero{position:relative;margin:4px 0 18px;padding:26px 20px 20px;border-radius:22px;background:linear-gradient(160deg,#0D0F12 0%,#1F2328 100%);color:#fff;text-align:center;overflow:hidden;font-family:Sora,sans-serif}
#sp-ty-hero:before{content:'';position:absolute;left:50%;top:-80px;width:260px;height:260px;margin-left:-130px;border-radius:50%;background:radial-gradient(circle,rgba(125,219,160,.22),rgba(125,219,160,0) 65%);pointer-events:none}
#sp-ty-hero .ok{position:relative;width:58px;height:58px;margin:0 auto 14px;border-radius:50%;background:#2E9B57;display:flex;align-items:center;justify-content:center;box-shadow:0 0 0 8px rgba(46,155,87,.18);animation:spTyPop .5s cubic-bezier(.34,1.6,.5,1) both}
#sp-ty-hero .ok svg{width:28px;height:28px;color:#fff}
@keyframes spTyPop{0%{transform:scale(.3);opacity:0}100%{transform:scale(1);opacity:1}}
#sp-ty-hero h1{position:relative;margin:0 0 6px!important;font:800 24px/1.2 Sora,sans-serif!important;color:#fff!important;letter-spacing:-.01em}
#sp-ty-hero p{position:relative;margin:0 0 18px!important;font-size:14px!important;line-height:1.5;color:#C9CDD2!important}
#sp-ty-hero p b{color:#fff}
#sp-ty-hero .meta{position:relative;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
#sp-ty-hero .meta div{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px 6px}
#sp-ty-hero .meta span{display:block;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#80868E;margin-bottom:3px}
#sp-ty-hero .meta b{display:block;font-size:12.5px;font-weight:800;color:#fff;white-space:nowrap;letter-spacing:-.01em}
/* Bankdaten-Karte */
.sp-ty-bank{border-radius:18px!important;border-color:#E3E6E9!important;box-shadow:0 10px 26px rgba(13,15,18,.07)}
.sp-ty-bank .sp-ty-row{display:flex;align-items:center;justify-content:space-between;gap:10px}
.sp-ty-bank .sp-ty-row>p{min-width:0;overflow-wrap:anywhere}
.sp-ty-cp{flex:0 0 auto;height:32px;padding:0 12px;border-radius:999px;border:1px solid #DCDEE0!important;background:#fff!important;color:#0D0F12!important;font:700 12px/1 Sora,sans-serif!important;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:none!important}
.sp-ty-cp svg{width:13px;height:13px}
.sp-ty-cp.done{background:#2E9B57!important;border-color:#2E9B57!important;color:#fff!important}
.sp-ty-bank .sp-ty-tip{display:none!important}
.sp-ty-plan{border-radius:18px!important}
/* Artikel als Liste */
body.woocommerce-order-received section.woocommerce-order-details{background:#fff;border:1px solid #E3E6E9;border-radius:18px;padding:16px 16px 6px;margin:0 0 18px}
body.woocommerce-order-received table.woocommerce-table--order-details{border:none!important;margin:0!important;display:block}
body.woocommerce-order-received table.woocommerce-table--order-details thead{display:none!important}
body.woocommerce-order-received table.woocommerce-table--order-details tbody,body.woocommerce-order-received table.woocommerce-table--order-details tfoot{display:block}
body.woocommerce-order-received table.woocommerce-table--order-details tr{display:flex!important;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #F2F3F4}
body.woocommerce-order-received table.woocommerce-table--order-details td,body.woocommerce-order-received table.woocommerce-table--order-details th{border:none!important;padding:12px 0!important;background:none!important}
body.woocommerce-order-received table.woocommerce-table--order-details td.product-name{flex:1 1 auto;min-width:0;display:flex;flex-wrap:wrap;align-items:center;column-gap:8px}
body.woocommerce-order-received .sp-oi-row{display:flex!important;align-items:center;gap:12px;min-width:0}
body.woocommerce-order-received .sp-oi-row img{width:52px!important;height:52px!important;border-radius:12px!important;flex:0 0 52px;margin:0!important}
body.woocommerce-order-received .sp-oi-name,body.woocommerce-order-received .sp-oi-name a{font-size:14px!important;font-weight:700!important;text-decoration:none!important;color:#0D0F12!important}
body.woocommerce-order-received td.product-name .product-quantity{font-size:12.5px!important;font-weight:600!important;color:#80868E!important}
body.woocommerce-order-received td.product-name .wc-item-meta{flex:0 0 100%;margin:4px 0 0 64px!important;padding:0!important;list-style:none;font-size:12px;color:#5A6068}
body.woocommerce-order-received td.product-name .wc-item-meta li,body.woocommerce-order-received td.product-name .wc-item-meta p{margin:0!important;display:inline;font-size:12px!important}
body.woocommerce-order-received table.woocommerce-table--order-details td.product-total{flex:0 0 auto;white-space:nowrap;font-weight:800!important;font-size:14px!important;text-align:right!important}
body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr{border-bottom:none}
body.woocommerce-order-received table.woocommerce-table--order-details tfoot th,body.woocommerce-order-received table.woocommerce-table--order-details tfoot td{padding:6px 0!important;font-size:13.5px!important;font-weight:600!important;color:#5A6068!important}
body.woocommerce-order-received table.woocommerce-table--order-details tfoot td{text-align:right!important;color:#0D0F12!important}
body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr.sp-ty-total{border-top:1px solid #E3E6E9;margin-top:6px}
body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr.sp-ty-total th,body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr.sp-ty-total td{padding:12px 0!important;font-size:17px!important;font-weight:800!important;color:#0D0F12!important}
body.woocommerce-order-received table.woocommerce-table--order-details tfoot tr.sp-ty-pay,body.woocommerce-order-received .shipped_via,body.woocommerce-order-received section.woocommerce-order-details .sp-track-box{display:none!important}
body.woocommerce-order-received section.woocommerce-order-details .woocommerce-order-details__title{border:none!important;background:none!important;padding:0!important;margin:0 0 6px!important;box-shadow:none!important;font-size:16px!important}
/* Adresse */
body.woocommerce-order-received .wc-block-order-confirmation-additional-fields-wrapper{display:none!important}
body.woocommerce-order-received section.woocommerce-customer-details{background:#fff!important;border:1px solid #E3E6E9!important;border-radius:18px!important;padding:16px!important;margin:0 0 18px!important;box-shadow:none!important}
body.woocommerce-order-received section.woocommerce-customer-details .col2-set{display:grid!important;grid-template-columns:1fr;gap:14px;margin:0!important;padding:0!important;border:none!important;background:none!important}
body.woocommerce-order-received section.woocommerce-customer-details .woocommerce-column{width:auto!important;float:none!important;padding:0!important;margin:0!important;border:none!important;background:none!important;box-shadow:none!important}
body.woocommerce-order-received section.woocommerce-customer-details>*:empty{display:none!important}
body.woocommerce-order-received section.woocommerce-customer-details .col2-set:before,body.woocommerce-order-received section.woocommerce-customer-details .col2-set:after{display:none!important}
body.woocommerce-order-received section.woocommerce-customer-details .woocommerce-column__title{font-size:16px!important;margin:0 0 6px!important;margin-top:0!important;padding:0!important;border:none!important;background:none!important}
body.woocommerce-order-received section.woocommerce-customer-details address{border:none!important;padding:0!important;margin:0!important;font-style:normal;font-size:14px;line-height:1.6;color:#4B5157;background:none!important}
body.woocommerce-order-received section.woocommerce-customer-details.sp-ty-same .woocommerce-column--billing-address{display:none!important}
/* Abschluss */
#sp-ty-end{text-align:center;margin:6px 0 30px;font-family:Sora,sans-serif}
#sp-ty-end a.btn{display:flex;align-items:center;justify-content:center;height:52px;border-radius:14px;background:#0D0F12;color:#fff!important;font-weight:700;font-size:15px;text-decoration:none!important;margin:0 0 14px}
#sp-ty-end p{margin:0;font-size:13px;color:#5A6068;line-height:1.6}
#sp-ty-end p a{color:#0D0F12!important;font-weight:700}
</style>
<script id="sp-typ-js">
(function(){
 var o=document.querySelector('.woocommerce-order');if(!o||document.getElementById('sp-ty-hero'))return;
 function txt(e){return e?e.textContent.replace(/\s+/g,' ').trim():'';}
 function find(re,sel){return [].filter.call(o.querySelectorAll(sel||'div'),function(d){return re.test(txt(d.firstElementChild||d));});}
 var note=o.querySelector('.woocommerce-thankyou-order-received'),ov=o.querySelector('.woocommerce-order-overview');
 var name=(txt(note).match(/Danke,\s*([^!]+)!/)||[])[1]||'';
 var g=function(c){var e=ov&&ov.querySelector('.woocommerce-order-overview__'+c+' strong');return txt(e);};
 var nr=g('order'),pay=g('payment-method');
 var MON=['januar','februar','märz','april','mai','juni','juli','august','september','oktober','november','dezember'];
 function shortDate(d){var m=d.match(/^(\d{1,2})\.\s*([A-Za-zäÄ]+)\s+(\d{4})$/);if(!m)return d;var i=MON.indexOf(m[2].toLowerCase());return i<0?d:m[1]+'.'+(i+1)+'.'+m[3];}
 var bank=null;[].forEach.call(o.children,function(d){if(!bank&&/Unsere Bankverbindung/.test(txt(d))&&d.tagName==='DIV')bank=d;});
 var plan=null;[].forEach.call(o.children,function(d){if(!plan&&/Fahrplan|So geht es jetzt weiter/i.test(txt(d))&&d.tagName==='DIV')plan=d;});
 var sub=bank?'Bitte überweise jetzt den Betrag – sobald er da ist, versenden wir.':'Wir bereiten alles vor – die Bestätigung kommt per E-Mail.';
 var CHECK='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>';
 var h=document.createElement('div');h.id='sp-ty-hero';
 h.innerHTML='<div class="ok">'+CHECK+'</div><h1>Danke'+(name?', '+name:'')+'!</h1><p>Deine Bestellung <b>#'+nr+'</b> ist eingegangen.<br>'+sub+'</p><div class="meta"><div><span>Datum</span><b>'+shortDate(g('date'))+'</b></div><div><span>Gesamt</span><b>'+g('total')+'</b></div><div><span>Zahlung</span><b>'+pay+'</b></div></div>';
 o.insertBefore(h,o.firstChild);
 /* Bankdaten direkt unter den Kopf, mit Kopieren-Knoepfen */
 if(bank){bank.classList.add('sp-ty-bank');o.insertBefore(bank,h.nextSibling);
   var CP='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 012-2h10"/></svg>';
   [].forEach.call(bank.querySelectorAll('p'),function(lab){var t=txt(lab);if(!/^(KONTOINHABER|VERWENDUNGSZWECK|IBAN|BIC|BETRAG)$/.test(t))return;var val=lab.nextElementSibling;if(!val)return;
     var row=document.createElement('div');row.className='sp-ty-row';val.parentNode.insertBefore(row,val);row.appendChild(val);
     var b=document.createElement('button');b.type='button';b.className='sp-ty-cp';b.innerHTML=CP+'Kopieren';row.appendChild(b);
     b.addEventListener('click',function(){var v=txt(val);if(t==='IBAN')v=v.replace(/\s+/g,'');if(t==='BETRAG')v=v.replace(/[^0-9,]/g,'');
       var ok=function(){b.classList.add('done');b.innerHTML=CHECK+'Kopiert';setTimeout(function(){b.classList.remove('done');b.innerHTML=CP+'Kopieren';},1800);};
       if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(v).then(ok,function(){fb(v);ok();});}else{fb(v);ok();}});});
   [].forEach.call(bank.querySelectorAll('p'),function(p){if(/^Tipp:/.test(txt(p)))p.classList.add('sp-ty-tip');});}
 function fb(v){var ta=document.createElement('textarea');ta.value=v;ta.style.position='fixed';ta.style.opacity='0';document.body.appendChild(ta);ta.select();try{document.execCommand('copy');}catch(e){}ta.remove();}
 if(plan){plan.classList.add('sp-ty-plan');if(bank){var w=document.createTreeWalker(plan,NodeFilter.SHOW_TEXT),n;while(n=w.nextNode()){if(/unten genannten/.test(n.nodeValue))n.nodeValue=n.nodeValue.replace('unten genannten','oben genannten');}}}
 /* Summenzeilen markieren */
 [].forEach.call(o.querySelectorAll('table.woocommerce-table--order-details tfoot tr'),function(tr){var t=txt(tr.querySelector('th'));if(/^Gesamt/.test(t))tr.classList.add('sp-ty-total');if(/^Zahlungsart/.test(t))tr.classList.add('sp-ty-pay');});
 /* Eine Adresskarte, wenn Rechnung = Lieferung */
 var cd=o.querySelector('section.woocommerce-customer-details'),ba=cd&&cd.querySelector('.woocommerce-column--billing-address address'),sa=cd&&cd.querySelector('.woocommerce-column--shipping-address address');
 if(ba&&sa){var em=ba.querySelector('.woocommerce-customer-details--email');var bt=txt(ba).replace(txt(em),'').trim();
   if(bt===txt(sa)){cd.classList.add('sp-ty-same');if(em)sa.appendChild(em.cloneNode(true));}}
 /* Abschluss */
 var end=document.createElement('div');end.id='sp-ty-end';
 end.innerHTML='<a class="btn" href="<?php echo esc_url(home_url('/alle-produkte/')); ?>">Weiter einkaufen</a><p>Fragen zu deiner Bestellung? Schreib uns an <a href="mailto:info@peptrium.com">info@peptrium.com</a><br>oder auf <a href="https://t.me/peptrium" target="_blank" rel="noopener">Telegram</a>.</p>';
 o.appendChild(end);
})();
</script>
    <?php
}, 100);
