(function(){
 var root=document.querySelector('body.home .elementor-211'); if(!root)return;
 function el(h){var d=document.createElement('div');d.innerHTML=h.trim();return d.firstChild;}
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 function stars(n){return '★★★★★'.slice(0,Math.round(n));}
 /* Produkte */
 var P=window.SP_HP_PRODS||[];
 var cart='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg>';
 var cards=P.map(function(p,i){
   var bd=p.r?'<span class="bd s">−'+Math.round((1-p.p/p.r)*100)+' %</span>':(i===0?'<span class="bd">★ Bestseller</span>':(p.pen?'<span class="bd n">Neu</span>':''));
   var vo='';
   if(p.vars&&p.vars.length>1){vo='<div class="vo">'+p.vars.map(function(v,k){return '<button type="button" data-id="'+v.id+'" data-p="'+v.p+'" data-r="'+(v.r||'')+'"'+(k===0?' class="on"':'')+'>'+v.l+'</button>';}).join('')+'</div>';}
   else if(p.vars&&p.vars.length===1){vo='<div class="vo"><span>'+p.vars[0].l+'</span></div>';}
   else if(p.vol){vo='<div class="vo"><span>'+p.vol+'</span></div>';}
   var first=(p.vars&&p.vars.length)?p.vars[0]:{id:p.id,p:p.p,r:p.r};
   return '<div class="sp-pc'+(p.pen?' pen':'')+'" data-id="'+first.id+'"><a class="im" href="'+p.u+'"><img loading="lazy" src="'+p.i+'" alt="'+p.n+'">'+bd+'<span class="coa">✓ COA</span></a><div class="tx"><a href="'+p.u+'"><h3 class="nm">'+p.n+'</h3></a>'
     +'<div class="rt"><b>★★★★★</b>'+String(p.rt).replace('.',',')+' ('+p.rc+')</div>'+vo
     +(p.qd?'<div class="qd">ab 3 Stück −10 % Mengenrabatt</div>':'')
     +'<div class="pr"><strong>'+eur(first.p)+'</strong>'+(first.r?'<s>'+eur(first.r)+'</s>':'')+'<small>inkl. MwSt.</small></div>'
     +'<button type="button" class="add">'+cart+'In den Warenkorb</button></div></div>';
 }).join('');
 var prod=el('<section id="sp-hp-prod" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Bestseller</span><h2>Peptide in Forschungsqualität</h2><p class="sub">Jede Charge HPLC-geprüft, mit Analysezertifikat – Versand aus Deutschland.</p></div>'
  +'<div class="sp-rail">'+cards+'<a class="sp-pc all" href="/alle-produkte/"><div class="ar">→</div><b>Alle Produkte</b><span>Das ganze Sortiment ansehen</span></a></div>'
  +'<ul class="sp-trust"><li>HPLC ≥ 99 % Reinheit</li><li>Analysezertifikat zu jeder Charge</li><li>Gratisversand ab 100 €</li></ul>'
  +'<div class="sp-hp-more-a"><a href="/alle-produkte/">Alle Produkte ansehen →</a></div></div></section>');
 root.appendChild(prod);
 prod.addEventListener('click',function(e){
   var vb=e.target.closest('.vo button');
   if(vb){var c=vb.closest('.sp-pc');c.querySelectorAll('.vo button').forEach(function(b){b.classList.toggle('on',b===vb);});c.setAttribute('data-id',vb.getAttribute('data-id'));
     c.querySelector('.pr').innerHTML='<strong>'+eur(vb.getAttribute('data-p'))+'</strong>'+(vb.getAttribute('data-r')?'<s>'+eur(vb.getAttribute('data-r'))+'</s>':'')+'<small>inkl. MwSt.</small>';return;}
   var ab=e.target.closest('.add'); if(!ab)return;
   var c=ab.closest('.sp-pc'),id=c.getAttribute('data-id'),old=ab.innerHTML;
   ab.disabled=true;ab.textContent='…';
   var fd=new FormData();fd.append('product_id',id);fd.append('quantity','1');
   fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){
     if(r&&r.error&&r.product_url){location.href=r.product_url;return;}
     ab.classList.add('ok');ab.innerHTML='✓ Hinzugefügt';
     if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[r.fragments,r.cart_hash,jQuery(ab)]);
     setTimeout(function(){ab.classList.remove('ok');ab.innerHTML=old;ab.disabled=false;},2200);
   }).catch(function(){location.href=c.querySelector('a.im').href;});
 });
 /* Kundenstimmen: Texte aus den bisherigen Abschnitten uebernehmen */
 var rv=[];
 document.querySelectorAll('[data-id="d60a05b"] .rv-card').forEach(function(c){var t=c.querySelector('.rv-text'),n=c.querySelector('.rv-name'),a=c.querySelector('.rv-avatar');if(t)rv.push({t:t.textContent.trim(),n:n?n.textContent.trim():'Verifizierter Kunde',a:a?a.textContent.trim():'P',s:c.querySelectorAll('.rv-stars > svg').length||5});});
 document.querySelectorAll('[data-id="spsocial1"] .sps-quote-text').forEach(function(q){rv.push({t:q.textContent.trim(),n:'Verifizierter Kunde',a:'✓',s:5});});
 var seen={};rv=rv.filter(function(r){if(seen[r.t])return false;seen[r.t]=1;return true;});
 var bimg=(document.querySelector('[data-id="spsocial1"] .sps-bottle-img')||{}).src||'/wp-content/uploads/2026/08/retatrutide-tilted-glass-v3.png';
 function card(r){return '<div class="sp-rv"><div class="st">'+'★★★★★'.slice(0,r.s)+'</div><p>„'+r.t+'“</p><div class="au"><i>'+r.a+'</i><div><b>'+r.n+'</b><em>✓ Verifizierter Kauf</em></div></div></div>';}
 var h1=rv.filter(function(_,i){return i%2===0;}),h2=rv.filter(function(_,i){return i%2===1;});
 function row(list,cls){var s=list.map(card).join('');return '<div class="sp-mq '+cls+'"><div class="tr">'+s+s+'</div></div>';}
 var proof=el('<section id="sp-hp-proof" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Kundenstimmen</span><h2>Was andere nicht liefern, <span>liefert Peptrium.</span></h2></div>'
  +'<div class="sp-sum"><div class="big">4,8</div><div><div class="st">★★★★★</div><div class="t">aus <b>2.500+ Bestellungen</b><br>von verifizierten Käufern</div></div></div>'
  +'<div class="sp-stage">'+row(h1,'r1')+row(h2,'r2')+'<div class="sp-glow"></div><div class="sp-bottle"><img src="'+bimg+'" alt="Peptrium Fläschchen"></div></div></div></section>');
 root.appendChild(proof);
 /* Flasche bewegt sich beim Scrollen (wie im alten Abschnitt) */
 var stage=proof.querySelector('.sp-stage'),bottle=proof.querySelector('.sp-bottle'),tick=false;
 function upd(){tick=false;var r=stage.getBoundingClientRect(),vh=window.innerHeight;var p=((r.top+r.height/2)-vh/2)/vh;p=Math.max(-1,Math.min(1,p));
   bottle.style.transform='translateY('+(p*110)+'px) rotate('+(p*-22+6)+'deg) scale('+(1-Math.abs(p)*0.08)+')';}
 window.addEventListener('scroll',function(){if(!tick){tick=true;requestAnimationFrame(upd);}},{passive:true});upd();
 /* Newsletter in die Seite holen */
 var nl=document.getElementById('sp-nlh'); if(nl) root.appendChild(nl);
 /* Hinweis + Mehr ueber Peptrium */
 var info=el('<div id="sp-hp-info"><div class="dis"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5058" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div><br><button type="button">Mehr über Peptrium <span>▾</span></button></div>');
 root.appendChild(info);
 var seo=['045a884','a4ab146'].map(function(id){return root.querySelector('[data-id="'+id+'"]');}).filter(Boolean);
 seo.forEach(function(e){e.classList.add('sp-hp-closed');});
 var ib=info.querySelector('button');ib.onclick=function(){var o=ib.classList.toggle('open');seo.forEach(function(e){e.classList.toggle('sp-hp-closed',!o);});};
})();
