document.addEventListener('DOMContentLoaded',function(){
 var D=window.SP_CAT;if(!D)return;
 var host=document.getElementById('content');if(!host)return;
 document.body.classList.add('sp-cat-on');
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 var cart='<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg>';
 var pre=/[?&]vorbestellung=1/.test(location.search);
 var list=D.products.filter(function(p){return !pre||p.pre;});
 function card(p){
   var first=(p.vars&&p.vars.length)?p.vars[0]:{id:p.id,p:p.p,r:p.r};
   var bd=p.pre?'<span class="bd p">Vorbestellung'+(first.r?' · −'+Math.round((1-first.p/first.r)*100)+' %':'')+'</span>':(first.r?'<span class="bd s">−'+Math.round((1-first.p/first.r)*100)+' %</span>':(p.best?'<span class="bd">★ Bestseller</span>':(p.pen?'<span class="bd n">Neu</span>':'')));
   var vo='';
   if(p.vars&&p.vars.length>1)vo='<div class="vo">'+p.vars.map(function(v,k){return '<button type="button" data-id="'+v.id+'" data-p="'+v.p+'" data-r="'+(v.r||'')+'"'+(k===0?' class="on"':'')+'>'+v.l+'</button>';}).join('')+'</div>';
   else if(p.vars&&p.vars.length===1)vo='<div class="vo"><span>'+p.vars[0].l+'</span></div>';
   else if(p.vol)vo='<div class="vo"><span>'+p.vol+'</span></div>';
   return '<div class="sp-pc'+(p.pen?' pen':'')+(p.acc?' acc':'')+'" data-id="'+first.id+'"><a class="im" href="'+p.u+'"><img loading="lazy" src="'+p.i+'" alt="'+p.n+'">'+bd+(p.acc||p.noq?'':'<span class="coa">✓ HPLC-verifiziert</span>')+'</a><div class="tx"><a href="'+p.u+'"><h3 class="nm">'+p.n+'</h3></a>'
    +(p.rc?'<div class="rt"><b>★★★★★</b>'+String(p.rt).replace('.',',')+' ('+p.rc+')</div>':'')+vo
    +(p.qd?'<div class="qd">ab 3 Stück −10 %</div>':'')
    +'<div class="pr"><strong>'+eur(first.p)+'</strong>'+(first.r?'<s>'+eur(first.r)+'</s>':'')+'</div>'
    +'<button type="button" class="add">'+cart+(p.pre?'Vorbestellen':'In den Warenkorb')+'</button>'+(p.pre?'<div class="pre-n">Lieferung, sobald neue Ware eintrifft</div>':'')+'</div></div>';}
 var chips=D.cats.map(function(c){return '<a class="'+(c.on?'on':'')+(c.pre?' pre':'')+'" href="'+c.u+'">'+c.n+(c.c!=null?' <small>'+c.c+'</small>':'')+'</a>';}).join('');
 var others=D.cats.filter(function(c){return !c.on&&c.tile;});
 var root=document.createElement('div');root.id='sp-cat';
 root.innerHTML='<section class="hero"><div class="in"><div class="bc"><a href="/">Start</a> › <a href="/alle-produkte/">Produkte</a>'+(D.isAll?'':' › '+D.title)+'</div>'
  +'<span class="pill">'+(D.label||'Sortiment')+'</span>'
  +'<h1>'+(pre?'Vorbestellung':D.title)+'</h1><p>'+(pre?'Diese Produkte sind gerade vorbestellbar – mit Preisvorteil. Wir liefern, sobald die neue Ware eintrifft.':D.desc)+'</p>'
  +'<div class="meta"><span>🚚 Lieferung in 2 Werktagen</span><span>Gratisversand ab 100 €</span></div></div></section>'
  +'<div class="body"><div class="chips">'+chips+'</div>'
  +'<div class="bar"><b>'+list.length+' Ergebnisse</b><select aria-label="Sortieren"><option value="pop">Beliebteste</option><option value="pa">Preis aufsteigend</option><option value="pd">Preis absteigend</option><option value="az">Name A–Z</option></select></div>'
  +(list.some(function(p){return p.pre;})&&!pre?'<div class="note">⏳ <span>Mit <b>Vorbestellung</b> markierte Produkte werden geliefert, sobald neue Ware eintrifft.</span></div>':'')
  +'<div class="sp-grid">'+(list.length?list.map(card).join(''):'')+'</div>'+(list.length?'':D.search?'<div class="empty"><b>Keine Treffer</b>Probier z. B. „Retatrutide“, „GHK-Cu“ oder „Pen“.<a href="/alle-produkte/">Alle Produkte ansehen →</a></div>':'<div class="empty"><b>Bald verfügbar</b>Passende Peptide für diesen Bereich folgen in Kürze. Trag dich unten für den Newsletter ein – dann erfährst du es zuerst.<a href="/alle-produkte/">Zum ganzen Sortiment →</a></div>')
  +(others.length?'<div class="more"><h2>Weitere Forschungsbereiche</h2><div class="cats">'+others.map(function(c){return '<a class="sp-cat" href="'+c.u+'">'+(c.i?'<img loading="lazy" src="'+c.i+'" alt="">':'')+'<span>'+c.n+'<small>'+c.c+(c.c===1?' Produkt':' Produkte')+'</small></span></a>';}).join('')+'</div></div>':'')
  +'<div class="dis"><span>ⓘ</span><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div></div>';
 host.insertBefore(root,host.firstChild);
 var grid=root.querySelector('.sp-grid');
 root.querySelector('select').addEventListener('change',function(){var v=this.value,arr=list.slice();
   var pr=function(p){return (p.vars&&p.vars.length)?p.vars[0].p:p.p;};
   if(v==='pa')arr.sort(function(a,b){return pr(a)-pr(b);});if(v==='pd')arr.sort(function(a,b){return pr(b)-pr(a);});if(v==='az')arr.sort(function(a,b){return a.n.localeCompare(b.n,'de');});
   grid.innerHTML=arr.map(card).join('');});
 grid.addEventListener('click',function(e){
   var vb=e.target.closest('.vo button');
   if(vb){var c=vb.closest('.sp-pc');c.querySelectorAll('.vo button').forEach(function(b){b.classList.toggle('on',b===vb);});c.setAttribute('data-id',vb.getAttribute('data-id'));
     c.querySelector('.pr').innerHTML='<strong>'+eur(vb.getAttribute('data-p'))+'</strong>'+(vb.getAttribute('data-r')?'<s>'+eur(vb.getAttribute('data-r'))+'</s>':'');return;}
   var ab=e.target.closest('.add');if(!ab)return;
   var c2=ab.closest('.sp-pc'),old=ab.innerHTML;ab.disabled=true;ab.textContent='…';
   var fd=new FormData();fd.append('product_id',c2.getAttribute('data-id'));fd.append('quantity','1');
   fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){
     if(r&&r.error&&r.product_url){location.href=r.product_url;return;}
     ab.classList.add('ok');ab.innerHTML='✓ Hinzugefügt';
     if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[r.fragments,r.cart_hash,jQuery(ab)]);
     setTimeout(function(){ab.classList.remove('ok');ab.innerHTML=old;ab.disabled=false;},2200);
   }).catch(function(){location.href=c2.querySelector('a.im').href;});
 });
});
