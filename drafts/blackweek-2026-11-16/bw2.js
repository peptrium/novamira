(function(){
var BF=window.BWP==='bf',PCT=BF?35:30,F=1-PCT/100;
function fm(n){return n.toFixed(2).replace('.',',')+' €';}
function num(s){return parseFloat(String(s).replace(/[^\d,]/g,'').replace(',','.'))||0;}
var css=`
.bw-dbar{margin:10px 14px 4px;background:#07080A;color:#fff;border-radius:14px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;gap:10px;position:relative;overflow:hidden}
.bw-dbar:before{content:"";position:absolute;left:-30px;top:-50px;width:160px;height:160px;background:radial-gradient(closest-side,rgba(255,45,45,.5),transparent)}
.bw-dbar>*{position:relative}.bw-dbar b{display:block;font:800 14px/1.2 Sora,sans-serif}.bw-dbar span{font:600 11.5px/1.3 Inter,sans-serif;color:#A9AEB5}
.bw-dbar em{font-style:normal;font:800 18px/1 Sora,sans-serif;color:#FF5A5A;white-space:nowrap}
.sp-cd-item-price s,.bw-old{display:block;font-size:11.5px;color:#8A9098;font-weight:500;text-align:right;text-decoration:line-through}
.bw-badge{display:inline-block;background:#FF2D2D!important;color:#fff!important;font:800 10.5px/1 Sora,sans-serif!important;padding:4px 7px!important;border-radius:6px!important;margin-bottom:4px}
.bw-co{display:flex;justify-content:space-between;align-items:center;gap:8px;background:#07080A;color:#fff;border-radius:12px;padding:11px 13px;margin:10px 16px;font:700 14px/1.2 Sora,sans-serif}
.bw-co em{font-style:normal;color:#FF5A5A;font-size:16px}.bw-co small{display:block;font:500 11px/1.3 Inter,sans-serif;color:#A9AEB5;margin-top:3px}
`;
var st=document.createElement('style');st.textContent=css;document.head.appendChild(st);
var reg=0,neu=0;
// Drawer
var items=document.getElementById('sp-cd-items');
if(items){
 items.querySelectorAll('.sp-cd-item').forEach(function(it){var pe=it.querySelector('.sp-cd-item-price:not(.sp-cd-item-price-free)');if(!pe)return;
  var q=+(it.querySelector('.sp-cd-qty-val')||{}).textContent||1;var nm=(it.querySelector('.sp-cd-item-name')||{}).textContent;
  var unit={'Retatrutide':79.9,'GHK-Cu':39.9,'Bac Water':9.9}[nm]||num(pe.textContent)/q;var r=unit*q;reg+=r;neu+=r*F;
  pe.innerHTML=fm(r*F)+'<s>'+fm(r)+'</s>';
  var bd=it.querySelector('.sp-cd-item-bundle:not(.sp-cd-item-gift-badge)');if(bd)bd.remove();
  var n=it.querySelector('.sp-cd-item-name');var b=document.createElement('span');b.className='bw-badge';b.textContent='−'+PCT+' %';n.parentNode.insertBefore(b,n);
 });
 var gift=12.9;
 var d=document.createElement('div');d.className='bw-dbar';d.innerHTML='<div><b>'+(BF?'Black Friday':'Black Week')+' −'+PCT+' %</b><span>Endet in 09 T 14 Std · automatisch abgezogen</span></div><em>−'+fm(reg-neu)+'</em>';
 var sc=document.getElementById('sp-cd-scroll');sc.insertBefore(d,sc.firstChild);
 var sv=document.getElementById('sp-cd-subtotal-val');if(sv)sv.textContent=fm(neu);
 var ts=document.getElementById('sp-cd-total-savings-val');if(ts)ts.textContent=fm(reg-neu+gift);
 var p=document.querySelector('#sp-gx .hd p');if(p)p.innerHTML='Noch <b>'+fm(350-neu)+'</b> bis <b>GHK-Cu gratis</b><small>✓ Gratisversand · Injektionskit gratis</small>';
 var fl=document.querySelector('#sp-gx .fl');if(fl)fl.style.width='42%';
}
})();
