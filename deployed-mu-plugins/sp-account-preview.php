<?php
/**
 * Plugin Name: SP Kundenkonto-Vorschau
 * Description: Entwurf des neuen Kundenkontos (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * CSS/JS-Overlay ueber /mein-konto/ (+ sp-myaccount-auth.php, sp-wallet.php, Abo-Seiten):
 * Menue auf dem Handy als waagerechte Chip-Leiste statt 7 Zeilen, Bestellungen und Guthaben-Verlauf
 * als Karten, Adressen als saubere Karten mit "Bearbeiten", Bestellansicht wie die neue Danke-Seite,
 * Login-Text in Du-Form. Keine Aenderung an Formularen oder Logik. Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    if (!function_exists('sp_redesign_on') || !sp_redesign_on('basis') || !function_exists('is_account_page') || !is_account_page()) {
        return;
    }
    ?>
<style id="sp-acp-css">
/* ---------- Menue: Chip-Leiste (Handy + Tablet) ---------- */
@media(max-width:921px){
 body.woocommerce-account .woocommerce-MyAccount-navigation{float:none!important;width:auto!important;background:none!important;border:none!important;box-shadow:none!important;padding:0!important;margin:0 -16px 18px!important;position:relative}
 body.woocommerce-account .woocommerce-MyAccount-navigation ul{display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;gap:8px!important;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;padding:4px 16px 8px!important;margin:0!important;border:none!important;background:none!important;box-shadow:none!important;scroll-padding:0 16px}
 body.woocommerce-account .woocommerce-MyAccount-navigation ul::-webkit-scrollbar{display:none}
 body.woocommerce-account .woocommerce-MyAccount-navigation li{flex:0 0 auto!important;margin:0!important;padding:0!important;border:none!important;background:none!important;width:auto!important}
 body.woocommerce-account .woocommerce-MyAccount-navigation li a{display:inline-flex!important;align-items:center!important;gap:7px!important;height:40px!important;padding:0 15px!important;border-radius:999px!important;border:1px solid #E3E6E9!important;background:#fff!important;color:#0D0F12!important;font-size:13.5px!important;font-weight:700!important;white-space:nowrap!important;box-shadow:none!important;margin:0!important;width:auto!important}
 body.woocommerce-account .woocommerce-MyAccount-navigation li.is-active a{background:#0D0F12!important;border-color:#0D0F12!important;color:#fff!important}
 body.woocommerce-account .woocommerce-MyAccount-navigation li.is-active a svg *{stroke:#fff!important}
 body.woocommerce-account .woocommerce-MyAccount-navigation li a svg{width:16px!important;height:16px!important;flex:0 0 16px}
 body.woocommerce-account .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout a{background:#F6F7F8!important;color:#5A6068!important;border-color:#EEF0F2!important}
 body.woocommerce-account .woocommerce-MyAccount-navigation:after{content:'';position:absolute;right:0;top:0;bottom:8px;width:28px;background:linear-gradient(90deg,rgba(255,255,255,0),#fff);pointer-events:none}
 body.woocommerce-account .woocommerce-MyAccount-content{float:none!important;width:auto!important}
}
/* ---------- Bestellungen als Karten ---------- */
body.woocommerce-account table.woocommerce-orders-table{border:none!important;display:block;background:none!important}
body.woocommerce-account table.woocommerce-orders-table thead{display:none!important}
body.woocommerce-account table.woocommerce-orders-table tbody{display:flex;flex-direction:column;gap:12px}
body.woocommerce-account table.woocommerce-orders-table tr.order{display:grid!important;grid-template-columns:1fr auto;grid-template-areas:"num total" "date status" "act act";gap:4px 12px;align-items:center;padding:16px!important;border:1px solid #E3E6E9!important;border-radius:16px!important;background:#fff!important;margin:0!important;box-shadow:0 4px 14px rgba(13,15,18,.04)}
body.woocommerce-account table.woocommerce-orders-table tr.order>*{display:block!important;border:none!important;padding:0!important;background:none!important;text-align:left!important;font-size:13.5px!important}
body.woocommerce-account table.woocommerce-orders-table tr.order>*:before{display:none!important}
body.woocommerce-account .woocommerce-orders-table__cell-order-number{grid-area:num}
body.woocommerce-account .woocommerce-orders-table__cell-order-number a{font-size:17px!important;font-weight:800!important;color:#0D0F12!important;text-decoration:none!important}
body.woocommerce-account .woocommerce-orders-table__cell-order-number a:before{content:'Bestellung ';font-weight:600;color:#80868E;font-size:13px}
body.woocommerce-account .woocommerce-orders-table__cell-order-total{grid-area:total;text-align:right!important;font-size:12px!important;color:#80868E!important}
body.woocommerce-account .woocommerce-orders-table__cell-order-total .amount{display:block;font-size:17px;font-weight:800;color:#0D0F12}
body.woocommerce-account .woocommerce-orders-table__cell-order-date{grid-area:date;color:#5A6068!important}
body.woocommerce-account table.woocommerce-orders-table tr.order>.woocommerce-orders-table__cell-order-status{grid-area:status;justify-self:end;font-size:11.5px!important;font-weight:800!important;padding:4px 10px!important;border-radius:999px!important;background:#F2F3F4!important;color:#4B5157!important}
body.woocommerce-account table.woocommerce-orders-table tr.order.woocommerce-orders-table__row--status-completed .woocommerce-orders-table__cell-order-status{background:#E8F5EC!important;color:#1F7A4D!important}
body.woocommerce-account table.woocommerce-orders-table tr.order.woocommerce-orders-table__row--status-processing .woocommerce-orders-table__cell-order-status{background:#E8F0FB!important;color:#1D4F91!important}
body.woocommerce-account table.woocommerce-orders-table tr.order.woocommerce-orders-table__row--status-on-hold .woocommerce-orders-table__cell-order-status,body.woocommerce-account table.woocommerce-orders-table tr.order.woocommerce-orders-table__row--status-pending .woocommerce-orders-table__cell-order-status{background:#FFF4E0!important;color:#9A5B00!important}
body.woocommerce-account table.woocommerce-orders-table tr.order.woocommerce-orders-table__row--status-cancelled .woocommerce-orders-table__cell-order-status,body.woocommerce-account table.woocommerce-orders-table tr.order.woocommerce-orders-table__row--status-failed .woocommerce-orders-table__cell-order-status{background:#FDECEC!important;color:#B3261E!important}
body.woocommerce-account .woocommerce-orders-table__cell-order-actions{grid-area:act;display:flex!important;gap:8px;margin-top:10px!important}
body.woocommerce-account .woocommerce-orders-table__cell-order-actions a.button{flex:1 1 0;display:flex!important;align-items:center;justify-content:center;height:42px;border-radius:12px!important;margin:0!important;font-size:13.5px!important;font-weight:700!important;background:#F4F5F6!important;color:#0D0F12!important;border:1px solid #E3E6E9!important;box-shadow:none!important;padding:0 10px!important}
body.woocommerce-account .woocommerce-orders-table__cell-order-actions a.button.pay{background:#0D0F12!important;color:#fff!important;border-color:#0D0F12!important}
/* ---------- Guthaben-Verlauf als Liste ---------- */
body.woocommerce-account table.sp-wallet-table{display:block;border:1px solid #E3E6E9!important;border-radius:16px;background:#fff;padding:4px 16px;overflow:hidden}
body.woocommerce-account table.sp-wallet-table thead{display:none!important}
body.woocommerce-account table.sp-wallet-table tbody{display:block}
body.woocommerce-account table.sp-wallet-table tr{display:grid!important;grid-template-columns:1fr auto;grid-template-areas:"why amt" "date amt";gap:2px 12px;align-items:center;padding:12px 0;border-bottom:1px solid #F2F3F4}
body.woocommerce-account table.sp-wallet-table tr:last-child{border-bottom:none}
body.woocommerce-account table.sp-wallet-table td{display:block;border:none!important;padding:0!important;background:none!important}
body.woocommerce-account table.sp-wallet-table td:nth-child(1){grid-area:date;font-size:12px;color:#80868E}
body.woocommerce-account table.sp-wallet-table td:nth-child(2){grid-area:why;font-size:13.5px;font-weight:600;color:#0D0F12;line-height:1.4;overflow-wrap:anywhere}
body.woocommerce-account table.sp-wallet-table td:nth-child(3){grid-area:amt;font-weight:800;font-size:14.5px;white-space:nowrap;text-align:right}
/* ---------- Adressen ---------- */
body.woocommerce-account .woocommerce-Addresses{display:grid!important;grid-template-columns:1fr;gap:12px;margin:0!important}
@media(min-width:768px){body.woocommerce-account .woocommerce-Addresses{grid-template-columns:1fr 1fr}}
body.woocommerce-account .woocommerce-Addresses:before,body.woocommerce-account .woocommerce-Addresses:after{display:none!important}
body.woocommerce-account .woocommerce-Address{width:auto!important;float:none!important;margin:0!important;padding:16px!important;border:1px solid #E3E6E9!important;border-radius:16px!important;background:#fff!important;box-shadow:none!important}
body.woocommerce-account .woocommerce-Address-title{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:10px;margin:0 0 8px!important;padding:0!important;border:none!important;background:none!important}
body.woocommerce-account .woocommerce-Address-title h2{flex:1 1 auto;text-align:left!important;width:auto!important;margin:0!important;font-size:16px!important;font-weight:800!important;padding:0!important;border:none!important;background:none!important}
body.woocommerce-account .woocommerce-Address-title a.edit{float:none!important;font-size:12.5px!important;font-weight:700!important;text-decoration:none!important;color:#0D0F12!important;border:1px solid #DCDEE0;border-radius:999px;padding:6px 12px;white-space:nowrap}
body.woocommerce-account .woocommerce-Address address{border:none!important;padding:0!important;margin:0!important;background:none!important;font-style:normal;font-size:14px;line-height:1.6;color:#4B5157}
body.woocommerce-account .woocommerce-Address-title:before,body.woocommerce-account .woocommerce-Address-title:after{display:none!important}
body.woocommerce-account .woocommerce-MyAccount-content{padding-bottom:28px}
/* ---------- Bestellansicht ---------- */
body.woocommerce-view-order section.woocommerce-order-details,body.woocommerce-view-order section.woocommerce-customer-details{background:#fff!important;border:1px solid #E3E6E9!important;border-radius:18px!important;padding:16px!important;margin:0 0 16px!important;box-shadow:none!important}
body.woocommerce-view-order .woocommerce-order-details__title,body.woocommerce-view-order .woocommerce-column__title{border:none!important;background:none!important;padding:0!important;margin:0 0 6px!important;font-size:16px!important;font-weight:800!important}
body.woocommerce-view-order table.woocommerce-table--order-details{border:none!important;margin:0!important;display:block}
body.woocommerce-view-order table.woocommerce-table--order-details thead{display:none!important}
body.woocommerce-view-order table.woocommerce-table--order-details tbody,body.woocommerce-view-order table.woocommerce-table--order-details tfoot{display:block}
body.woocommerce-view-order table.woocommerce-table--order-details tr{display:flex!important;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid #F2F3F4}
body.woocommerce-view-order table.woocommerce-table--order-details td,body.woocommerce-view-order table.woocommerce-table--order-details th{border:none!important;padding:11px 0!important;background:none!important;font-size:14px!important;text-align:left!important}
body.woocommerce-view-order table.woocommerce-table--order-details td.product-name{flex:1 1 auto;min-width:0;font-weight:700}
body.woocommerce-view-order table.woocommerce-table--order-details td.product-name a{color:#0D0F12!important;text-decoration:none!important}
body.woocommerce-view-order table.woocommerce-table--order-details td.product-name .product-quantity{font-weight:600;color:#80868E;font-size:12.5px}
body.woocommerce-view-order table.woocommerce-table--order-details td.product-total{white-space:nowrap;font-weight:800!important;text-align:right!important}
body.woocommerce-view-order table.woocommerce-table--order-details tfoot tr{border-bottom:none}
body.woocommerce-view-order table.woocommerce-table--order-details tfoot th{font-weight:600!important;color:#5A6068!important;font-size:13.5px!important;padding:6px 0!important}
body.woocommerce-view-order table.woocommerce-table--order-details tfoot td{text-align:right!important;font-size:13.5px!important;padding:6px 0!important}
body.woocommerce-view-order table.woocommerce-table--order-details tfoot tr.sp-ty-total{border-top:1px solid #E3E6E9;margin-top:6px}
body.woocommerce-view-order table.woocommerce-table--order-details tfoot tr.sp-ty-total th,body.woocommerce-view-order table.woocommerce-table--order-details tfoot tr.sp-ty-total td{font-size:17px!important;font-weight:800!important;color:#0D0F12!important;padding:12px 0!important}
body.woocommerce-view-order section.woocommerce-customer-details .col2-set{display:grid!important;grid-template-columns:1fr;gap:14px;margin:0!important;padding:0!important;border:none!important;background:none!important}
body.woocommerce-view-order section.woocommerce-customer-details .col2-set:before,body.woocommerce-view-order section.woocommerce-customer-details .col2-set:after{display:none!important}
body.woocommerce-view-order section.woocommerce-customer-details .woocommerce-column{width:auto!important;float:none!important;padding:0!important;margin:0!important;border:none!important;background:none!important}
body.woocommerce-view-order section.woocommerce-customer-details address{border:none!important;padding:0!important;margin:0!important;font-style:normal;font-size:14px;line-height:1.6;color:#4B5157;background:none!important}
body.woocommerce-view-order section.woocommerce-customer-details.sp-ty-same .woocommerce-column--billing-address{display:none!important}
body.woocommerce-view-order .wc-block-order-confirmation-additional-fields-wrapper{display:none!important}
/* ---------- Kontodetails ---------- */
body.woocommerce-edit-account .woocommerce-EditAccountForm em,body.woocommerce-edit-account #account_display_name_description{display:block;font-style:normal!important;font-size:12px!important;color:#80868E!important;margin-top:4px}
body.woocommerce-edit-account .woocommerce-EditAccountForm fieldset{border:1px solid #E3E6E9!important;border-radius:16px!important;padding:14px 16px!important;margin:18px 0!important}
body.woocommerce-edit-account .woocommerce-EditAccountForm legend{font-weight:800!important;font-size:15px!important;padding:0 6px!important;border:none!important}
body.woocommerce-edit-account .woocommerce-EditAccountForm button[type=submit]{width:100%;height:50px;border-radius:14px!important;font-size:15px!important;font-weight:700!important}
/* ---------- Login ---------- */
body.woocommerce-account .woocommerce-form-login__rememberme{display:inline-flex!important;align-items:center;gap:8px;text-transform:none!important;letter-spacing:0!important;font-size:13.5px!important;font-weight:600!important}
body.woocommerce-account .woocommerce-form-login__rememberme input{margin:0!important;width:18px;height:18px}
</style>
<script id="sp-acp-js">
(function(){
 /* aktiven Menue-Chip ins Bild scrollen */
 var act=document.querySelector('.woocommerce-MyAccount-navigation li.is-active');
 if(act&&window.innerWidth<=921){var ul=act.parentNode;ul.scrollLeft=Math.max(0,act.offsetLeft-ul.clientWidth/2+act.offsetWidth/2);}
 /* Login-Text in Du-Form */
 var hp=document.querySelector('.sp-auth-hero p');
 if(hp&&/Melden Sie sich/.test(hp.textContent)){hp.textContent='Melde dich an, um deine Bestellungen zu verfolgen, Adressen zu speichern und schneller zur Kasse zu gehen. Neu hier? Dein Konto ist in Sekunden erstellt.';}
 /* Registrierung: Sie -> du */
 var rf=document.querySelector('form.woocommerce-form-register');if(rf){var w=document.createTreeWalker(rf,NodeFilter.SHOW_TEXT),n;while(n=w.nextNode()){if(/stimmen Sie/.test(n.nodeValue))n.nodeValue=n.nodeValue.replace('stimmen Sie','stimmst du');}}
 /* Adress-Links kurz */
 [].forEach.call(document.querySelectorAll('.woocommerce-Address-title a.edit'),function(a){a.textContent=/hinzuf/i.test(a.textContent)?'Hinzufügen':'Bearbeiten';});
 /* Bestellansicht: Summenzeile + eine Adresskarte */
 [].forEach.call(document.querySelectorAll('body.woocommerce-view-order table.woocommerce-table--order-details tfoot tr'),function(tr){var t=(tr.querySelector('th')||{}).textContent||'';if(/^\s*Gesamt/.test(t))tr.classList.add('sp-ty-total');});
 var cd=document.querySelector('body.woocommerce-view-order section.woocommerce-customer-details');
 if(cd){var ba=cd.querySelector('.woocommerce-column--billing-address address'),sa=cd.querySelector('.woocommerce-column--shipping-address address');
   if(ba&&sa){var em=ba.querySelector('.woocommerce-customer-details--email,.woocommerce-customer-details--phone');var tx=function(e){return e?e.textContent.replace(/\s+/g,' ').trim():'';};var bt=tx(ba);[].forEach.call(ba.querySelectorAll('p'),function(p){bt=bt.replace(tx(p),'').trim();});
     if(bt===tx(sa)){cd.classList.add('sp-ty-same');[].forEach.call(ba.querySelectorAll('p'),function(p){sa.appendChild(p.cloneNode(true));});}}}
})();
</script>
    <?php
}, 100);
