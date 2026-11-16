(function(){
var F=0.7;function fm(n){return n.toFixed(2).replace('.',',')+' €';}
var P={'Retatrutide':79.9,'GHK-Cu':39.9,'Bac Water':9.9};var reg=0,neu=0;
var st=document.createElement('style');st.textContent=`
.bw-co{display:flex;justify-content:space-between;align-items:center;gap:8px;background:#07080A;color:#fff;border-radius:12px;padding:12px 14px;margin:12px 16px 4px;font:800 14px/1.2 Sora,sans-serif;position:relative;overflow:hidden}
.bw-co:before{content:"";position:absolute;right:-30px;top:-50px;width:150px;height:150px;background:radial-gradient(closest-side,rgba(255,45,45,.5),transparent)}
.bw-co>*{position:relative}.bw-co em{font-style:normal;color:#FF5A5A;font-size:17px;white-space:nowrap}.bw-co small{display:block;font:500 11px/1.35 Inter,sans-serif;color:#A9AEB5;margin-top:3px}
.bw-cob{display:inline-block;background:#FF2D2D;color:#fff;font:800 10.5px/1 Sora,sans-serif;padding:4px 7px;border-radius:6px;margin-top:4px}
#sp-fsh-box,.sp-fsh-box{display:none!important}`;document.head.appendChild(st);
document.querySelectorAll('.wc-block-components-order-summary-item').forEach(function(it){
 var nm=(it.querySelector('.wc-block-components-product-name')||{}).textContent||'';nm=nm.trim();var u=P[nm];if(!u)return;
 var q=+(it.querySelector('.wc-block-components-order-summary-item__quantity')||{}).textContent||1;
 var tp=it.querySelector('.wc-block-components-order-summary-item__total-price');var r=u*q;reg+=r;neu+=r*F;
 tp.innerHTML='<div style="text-align:right"><div style="font-weight:700">'+fm(r*F)+'</div><div><span class="bw-cob">Spare '+fm(r-r*F)+'</span></div></div>';
 var ip=it.querySelector('.wc-block-components-order-summary-item__individual-prices');if(ip)ip.innerHTML='<del style="color:#8A9098">'+fm(u)+'</del> <ins style="text-decoration:none;font-weight:700">'+fm(u*F)+'</ins>';
});
var sub=document.querySelector('.wp-block-woocommerce-checkout-order-summary-subtotal-block .wc-block-components-totals-item__value');if(sub)sub.textContent=fm(neu);
var tot=document.querySelector('.wc-block-components-totals-footer-item .wc-block-components-totals-item__value');if(tot)tot.textContent=fm(neu);
var ft=document.querySelector('.wc-block-components-totals-footer-item');var w=ft&&ft.closest('.wc-block-components-totals-wrapper');
var row=document.createElement('div');row.className='bw-co';row.innerHTML='<div>Black Week: du sparst<small>Gilt für Bestellungen bis Do 26.11., 23:59 · bei Vorkasse zählt das Bestelldatum</small></div><em>−'+fm(reg-neu)+'</em>';
if(w)w.parentNode.insertBefore(row,w);
})();
