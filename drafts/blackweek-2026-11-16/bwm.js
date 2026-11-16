(function(){
var BF=window.BWP==='bf',PCT=BF?35:30,F=1-PCT/100,NAME=BF?'Black Friday':'Black Week';
function fm(n){return n.toFixed(2).replace('.',',')+' €';}
function num(s){return parseFloat(String(s).replace(/[^\d,]/g,'').replace(',','.'))||0;}
var C=BF?['03','11','12','51']:['09','14','47','26'];
var css=`
#sp-top-banner.bw{background:#050506!important;color:#C9CDD2!important;border-bottom:1px solid rgba(255,255,255,.08)}
#sp-top-banner.bw .sp-tb-mask{display:none!important}
.bw-tb{display:flex;align-items:center;justify-content:center;gap:10px;height:100%;font:600 10.5px/1 Inter,sans-serif;letter-spacing:.22em;text-transform:uppercase;white-space:nowrap;color:#C9CDD2}
.bw-tb b{color:#fff}.bw-tb i{font-style:normal;color:#8D939A;letter-spacing:.12em;font-variant-numeric:tabular-nums}
.bw-chrome{background:linear-gradient(180deg,#FFFFFF 0%,#F1F3F5 26%,#A3A9B1 48%,#5F656D 53%,#D9DDE1 68%,#FFFFFF 82%,#9AA0A8 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.bw-ey{font:600 9.5px/1 Inter,sans-serif;letter-spacing:.42em;color:#9AA0A8}
/* Karten */
.sp-pc .bd.bw-bd{background:#fff!important;color:#07080A!important;font:800 11px/1 Sora,sans-serif!important;letter-spacing:.02em;padding:6px 8px!important;border-radius:999px!important;box-shadow:0 4px 14px rgba(0,0,0,.35)}
.sp-pc .coa{display:none!important}
.sp-pc .pr.bw-pr{display:flex;align-items:baseline;gap:7px;flex-wrap:wrap}
.sp-pc .pr.bw-pr s{font-size:12.5px;color:#9097A0;font-weight:500}
.sp-pc .qd.bw-qd{background:none!important;color:#6B7178!important;font:600 10px/1 Inter,sans-serif!important;letter-spacing:.2em!important;text-transform:uppercase;padding:0!important}
/* Kategorie */
.bw-strip{margin:0 0 16px;background:radial-gradient(80% 140% at 50% 0%,#1C1E22,#050506 70%);color:#fff;border-radius:18px;padding:18px 18px 16px;border:1px solid rgba(255,255,255,.09);display:grid;gap:6px;text-align:center}
.bw-strip .bg{font:800 44px/.95 Sora,sans-serif;letter-spacing:-.05em}
.bw-strip .s{font:500 13px/1.3 Inter,sans-serif;color:#C9CDD2}
.bw-strip .c{font:500 11.5px/1 Inter,sans-serif;color:#8A9098;letter-spacing:.06em;font-variant-numeric:tabular-nums}.bw-strip .c b{color:#fff;font-weight:600}
/* Kaufbox */
.bw-pricerow{display:flex;align-items:baseline;gap:9px;flex-wrap:wrap}
.bw-pricerow s{font-size:16px;color:#8A9098;font-weight:500}
.bw-tag{display:inline-block;background:#07080A;color:#fff;font:700 11px/1 Inter,sans-serif;letter-spacing:.04em;padding:6px 9px;border-radius:999px;vertical-align:4px}
.bw-bb{margin:14px 0 6px;background:radial-gradient(90% 120% at 50% 0%,#1C1E22,#050506 72%);color:#fff;border-radius:16px;padding:16px 16px 14px;display:grid;gap:10px;border:1px solid rgba(255,255,255,.06)}
.bw-bb .h{display:flex;justify-content:space-between;align-items:flex-end;gap:8px}
.bw-bb .h .bg{font:800 34px/.9 Sora,sans-serif;letter-spacing:-.05em}
.bw-bb .h .r{text-align:right;font:600 13.5px/1.25 Inter,sans-serif}.bw-bb .h .r span{display:block;font:400 11.5px/1.3 Inter,sans-serif;color:#8A9098}
.bw-bb .ln{height:1px;background:linear-gradient(90deg,rgba(255,255,255,.3),rgba(255,255,255,.04))}
.bw-bb .c{display:flex;justify-content:space-between;font:500 12px/1 Inter,sans-serif;color:#8A9098}.bw-bb .c b{font:600 13.5px/1 Sora,sans-serif;color:#fff;letter-spacing:.06em;font-variant-numeric:tabular-nums}
/* Drawer */
.bw-dbar{margin:10px 14px 6px;background:radial-gradient(90% 140% at 50% 0%,#1E2024,#050506 72%);color:#fff;border-radius:16px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px;border:1px solid rgba(255,255,255,.06)}
.bw-dbar .bw-ey{display:block;margin-bottom:6px}.bw-dbar b{display:block;font:600 14px/1.2 Inter,sans-serif}.bw-dbar span.t{font:400 11.5px/1.3 Inter,sans-serif;color:#8A9098}
.bw-dbar em{font-style:normal;font:800 24px/1 Sora,sans-serif;letter-spacing:-.03em;white-space:nowrap}
.sp-cd-item-price s{display:block;font-size:11.5px;color:#9097A0;font-weight:500;text-align:right}
.bw-badge{display:inline-block;background:#07080A;color:#fff;font:700 10px/1 Inter,sans-serif;letter-spacing:.06em;padding:4px 7px;border-radius:999px;margin-bottom:5px}
/* Kasse */
.bw-co{display:flex;justify-content:space-between;align-items:center;gap:10px;background:radial-gradient(90% 140% at 50% 0%,#1E2024,#050506 72%);color:#fff;border-radius:14px;padding:13px 15px;margin:12px 16px 4px}
.bw-co b{font:600 14px/1.2 Inter,sans-serif}.bw-co em{font-style:normal;font:800 20px/1 Sora,sans-serif;letter-spacing:-.02em;white-space:nowrap}.bw-co small{display:block;font:400 11px/1.35 Inter,sans-serif;color:#8A9098;margin-top:4px}
#sp-fsh-box,.sp-fsh-box,#sp-abo-toggle{display:none!important}
`;
var st=document.createElement('style');st.textContent=css;document.head.appendChild(st);
var tb=document.getElementById('sp-top-banner');
if(tb){tb.classList.add('bw');var d=document.createElement('div');d.className='bw-tb';d.innerHTML='<b>'+NAME+'</b><span>·</span>−'+PCT+' % auf alles<span>·</span><i>'+C[0]+' : '+C[1]+' : '+C[2]+'</i>';tb.appendChild(d);}
document.querySelectorAll('.sp-pc[data-id]').forEach(function(cd){
 var on=cd.querySelector('.vo button.on')||cd.querySelector('.vo button');var pr=cd.querySelector('.pr');if(!pr)return;
 var p=on?parseFloat(on.getAttribute('data-p')):num(pr.textContent);if(!p)return;
 pr.classList.add('bw-pr');pr.innerHTML='<strong>'+fm(p*F)+'</strong><s>'+fm(p)+'</s>';
 var bd=cd.querySelector('.bd');if(!bd){bd=document.createElement('span');var im=cd.querySelector('a.im');if(im)im.appendChild(bd);}bd.className='bd bw-bd';bd.textContent='−'+PCT+' %';
 var q=cd.querySelector('.qd');if(!q){q=document.createElement('div');q.className='qd';pr.parentNode.insertBefore(q,pr);}q.classList.add('bw-qd');q.textContent=NAME;
});
var cat=document.querySelector('#sp-cat .grid, #sp-cat [class*="grid"]');
if(cat&&!document.querySelector('.bw-strip')){var s=document.createElement('div');s.className='bw-strip';s.innerHTML='<div class="bw-ey">'+NAME.toUpperCase()+'</div><div class="bg bw-chrome">−'+PCT+' % auf alles</div><div class="s">Preise bereits reduziert. Kein Code nötig.</div><div class="c">Endet in <b>'+C[0]+'T · '+C[1]+':'+C[2]+':'+C[3]+'</b></div>';cat.parentNode.insertBefore(s,cat);}
var rp=document.getElementById('rx-price');
if(rp){var p0=num(rp.textContent);rp.innerHTML=fm(p0*F);var row=document.createElement('span');row.className='bw-pricerow';rp.parentNode.insertBefore(row,rp);row.appendChild(rp);row.insertAdjacentHTML('beforeend','<s>'+fm(p0)+'</s><span class="bw-tag">−'+PCT+' %</span>');
 document.querySelectorAll('#rx-vol-tiers .rx-tier__disc').forEach(function(e){e.textContent=fm(num(e.textContent)*F);});
 document.querySelectorAll('#rx-tiers .rx-tier').forEach(function(t){var q=+t.getAttribute('data-q');var e=t.querySelector('.rx-tier__disc');if(e)e.textContent=q===1?'Einzeln':fm(p0*F*q).replace(' €',' €');});
 var fl=document.querySelector('.rx-tier__flag');if(fl)fl.style.display='none';
 var bb=document.createElement('div');bb.className='bw-bb';bb.innerHTML='<div class="bw-ey">'+NAME.toUpperCase()+'</div><div class="h"><div class="bg bw-chrome">−'+PCT+' %</div><div class="r">Du sparst '+fm(p0-p0*F)+'<span>automatisch, ohne Code</span></div></div><div class="ln"></div><div class="c"><span>Endet in</span><b>'+C[0]+'T · '+C[1]+' : '+C[2]+' : '+C[3]+'</b></div>';
 var prr=document.querySelector('#rx-buybox .rx-price-row');prr.parentNode.insertBefore(bb,prr.nextSibling);
 var tt=document.getElementById('rx-total');if(tt)tt.textContent=fm(p0*F);var sh=document.querySelector('#rx-shiphint .rx-amt-shine');if(sh)sh.textContent=fm(150-p0*F);
}
// Drawer
var P={'Retatrutide':79.9,'GHK-Cu':39.9,'Bac Water':9.9};
var items=document.getElementById('sp-cd-items');
if(items&&document.getElementById('sp-cart-drawer').classList.contains('open')){var reg=0,neu=0;
 items.querySelectorAll('.sp-cd-item').forEach(function(it){var pe=it.querySelector('.sp-cd-item-price:not(.sp-cd-item-price-free)');if(!pe)return;
  var q=+(it.querySelector('.sp-cd-qty-val')||{}).textContent||1;var nm=(it.querySelector('.sp-cd-item-name')||{}).textContent;var r=(P[nm]||num(pe.textContent)/q)*q;reg+=r;neu+=r*F;
  pe.innerHTML=fm(r*F)+'<s>'+fm(r)+'</s>';var bd=it.querySelector('.sp-cd-item-bundle:not(.sp-cd-item-gift-badge)');if(bd)bd.remove();var sv=it.querySelector('.sp-cd-item-save');if(sv&&!it.querySelector('.sp-cd-item-price-free'))sv.remove();
  var n=it.querySelector('.sp-cd-item-name');var b=document.createElement('span');b.className='bw-badge';b.textContent='−'+PCT+' %';n.parentNode.insertBefore(b,n);});
 var d2=document.createElement('div');d2.className='bw-dbar';d2.innerHTML='<div><span class="bw-ey">'+NAME.toUpperCase()+'</span><b>−'+PCT+' % auf alles</b><span class="t">Endet in '+C[0]+' T '+C[1]+' Std</span></div><em class="bw-chrome">−'+fm(reg-neu)+'</em>';
 var sc=document.getElementById('sp-cd-scroll');sc.insertBefore(d2,sc.firstChild);
 document.getElementById('sp-cd-subtotal-val').textContent=fm(neu);var ts=document.getElementById('sp-cd-total-savings-val');if(ts)ts.textContent=fm(reg-neu+12.9);
 var gp=document.querySelector('#sp-gx .hd p');if(gp)gp.innerHTML='Noch <b>'+fm(350-neu)+'</b> bis <b>GHK-Cu gratis</b><small>✓ Gratisversand · Injektionskit gratis</small>';var fl2=document.querySelector('#sp-gx .fl');if(fl2)fl2.style.width='42%';
}
// Kasse
if(document.querySelector('.wc-block-components-order-summary-item')){var r2=0,n2=0,seen={};
 document.querySelectorAll('.wc-block-components-order-summary-item').forEach(function(it){var nm=((it.querySelector('.wc-block-components-product-name')||{}).textContent||'').trim();var u=P[nm];if(!u)return;
  var qe=it.querySelector('.wc-block-components-quantity-selector__input');var qe=it.querySelector('.wc-block-components-order-summary-item__quantity');var q=qe&&qe.firstChild?parseInt(qe.firstChild.textContent)||1:1;var r=u*q;if(!seen[nm]){seen[nm]=1;r2+=r;n2+=r*F;}
  it.querySelector('.wc-block-components-order-summary-item__total-price').innerHTML='<div style="text-align:right;white-space:nowrap"><div style="font-weight:700">'+fm(r*F)+'</div><div style="color:#9097A0;font-size:12px;text-decoration:line-through">'+fm(r)+'</div></div>';
  var ip=it.querySelector('.wc-block-components-order-summary-item__individual-prices');if(ip)ip.innerHTML='<span class="bw-badge" style="margin:0">−'+PCT+' %</span>';});
 var sub=document.querySelector('.wp-block-woocommerce-checkout-order-summary-subtotal-block .wc-block-components-totals-item__value');if(sub)sub.textContent=fm(r2);
 var tot=document.querySelector('.wc-block-components-totals-footer-item .wc-block-components-totals-item__value');if(tot)tot.textContent=fm(n2);
 var ft=document.querySelector('.wc-block-components-totals-footer-item');var w=ft&&ft.closest('.wc-block-components-totals-wrapper');
 var row=document.createElement('div');row.className='bw-co';row.innerHTML='<div><b>'+NAME+' −'+PCT+' %</b><small>Bei Vorkasse zählt das Bestelldatum.</small></div><em class="bw-chrome">−'+fm(r2-n2)+'</em>';if(w)w.parentNode.insertBefore(row,w);}
})();
