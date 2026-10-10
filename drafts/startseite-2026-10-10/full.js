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
  +'<div class="sp-cats-h"><b>Nach Forschungsbereich</b><a href="/alle-produkte/">Alle →</a></div><div class="sp-cats">'+(window.SP_HP_CATS||[]).map(function(c){return '<a class="sp-cat'+(c.w?' wide':'')+'" href="'+c.u+'">'+(c.i?'<img loading="lazy" src="'+c.i+'" alt="">':'')+'<span>'+c.n+'<small>'+c.c+(c.c===1?' Produkt':' Produkte')+'</small></span></a>';}).join('')+'</div>'
  +'<ul class="sp-trust"><li>HPLC ≥ 99 % Reinheit</li><li>Analysezertifikat zu jeder Charge</li><li>Gratisversand ab 100 €</li><li>Neutrale, diskrete Verpackung</li><li class="pay">Vorkasse · Krypto · Guthaben</li></ul>'
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
  +'<div class="sp-stage">'+row(h1,'r1')+row(h2,'r2')+'<div class="sp-glow"></div><div class="sp-bshadow"></div><div class="sp-bottle"><img src="'+bimg+'" alt="Peptrium Fläschchen"></div></div></div></section>');
 root.appendChild(proof);
 /* Flasche: gleitet weich hinter dem Scrollen her (nur solange sichtbar) */
 var stage=proof.querySelector('.sp-stage'),bottle=proof.querySelector('.sp-bottle'),bsh=proof.querySelector('.sp-bshadow');
 var cur=0,tgt=0,run=false,vis=false;
 function target(){var r=stage.getBoundingClientRect(),vh=window.innerHeight;var p=((r.top+r.height/2)-vh/2)/vh;return Math.max(-1,Math.min(1,p));}
 function paint(p){bottle.style.transform='translate3d(0,'+(p*110).toFixed(1)+'px,0) rotate('+(p*-22+6).toFixed(2)+'deg)';bsh.style.transform='translate3d(0,'+(p*40).toFixed(1)+'px,0) scale('+(1-Math.abs(p)*0.25).toFixed(3)+')';bsh.style.opacity=(1-Math.abs(p)*0.5).toFixed(2);}
 function loop(){cur+=(tgt-cur)*0.12;paint(cur);if(Math.abs(tgt-cur)>0.0005&&vis){requestAnimationFrame(loop);}else{run=false;}}
 function kick(){tgt=target();if(!run&&vis){run=true;requestAnimationFrame(loop);}}
 if('IntersectionObserver' in window){new IntersectionObserver(function(e){vis=e[0].isIntersecting;if(vis)kick();},{rootMargin:'200px 0px'}).observe(stage);}else{vis=true;}
 window.addEventListener('scroll',kick,{passive:true});cur=tgt=target();paint(cur);
 if(window.matchMedia('(prefers-reduced-motion: reduce)').matches){window.removeEventListener('scroll',kick);}
 /* Kombi-Sets */
 var S=window.SP_HP_SETS||[];
 if(S.length){
 var sets=el('<section id="sp-hp-sets" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Beliebte Kombinationen</span><h2>Passt zusammen.</h2><p class="sub">Sinnvoll kombiniert – mit einem Klick komplett im Warenkorb.</p></div><div class="sp-rail">'
  +S.map(function(s,i){var t=s.items.map(function(it){return '<span class="'+(it.w?'w':'')+'"><img loading="lazy" src="'+it.i+'" alt=""></span>';}).join('');
    return '<div class="sp-set" data-set="'+i+'"><div class="thumbs">'+t+'<i>'+s.items.length+' Artikel</i></div><h3>'+s.n+'</h3><p class="why">'+s.d+'</p><ul>'+s.items.map(function(it){return '<li><span>'+it.n+'</span><span>'+eur(it.p)+'</span></li>';}).join('')+'</ul>'
     +'<div class="sum"><span class="ship'+(s.t>=100?'':' no')+'">'+(s.t>=100?'✓ Gratisversand':'noch '+eur(100-s.t)+' bis Gratisversand')+'</span><strong>'+eur(s.t)+'</strong></div><button type="button" class="add">'+cart+'Set in den Warenkorb</button></div>';}).join('')
  +'</div></div></section>');
 root.appendChild(sets);
 sets.addEventListener('click',function(e){var b=e.target.closest('.add');if(!b)return;var s=S[+b.closest('.sp-set').getAttribute('data-set')],old=b.innerHTML,i=0,last=null;b.disabled=true;b.textContent='…';
   function next(){if(i>=s.items.length){b.classList.add('ok');b.innerHTML='✓ Set hinzugefügt';if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[last&&last.fragments,last&&last.cart_hash,jQuery(b)]);setTimeout(function(){b.classList.remove('ok');b.innerHTML=old;b.disabled=false;},2400);return;}
     var fd=new FormData();fd.append('product_id',s.items[i].id);fd.append('quantity','1');
     fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){last=r;i++;next();}).catch(function(){b.innerHTML=old;b.disabled=false;});}
   next();});
 }
 /* FAQ */
 var F=[
  ['Wie schnell kommt meine Bestellung?','Wir versenden mit DHL. Sobald dein Paket unterwegs ist, bekommst du die Sendungsnummer per E-Mail. Ab 100 € Bestellwert ist der Versand gratis. Mehr unter <a href="/versand-lieferzeit/">Versand &amp; Lieferzeit</a>.'],
  ['Wie kann ich bezahlen?','Per Vorkasse (Überweisung), mit Kryptowährung oder mit deinem Peptrium-Guthaben. Bei Vorkasse verschicken wir, sobald deine Zahlung eingegangen ist.'],
  ['Ist die Verpackung neutral?','Ja. Wir verschicken neutral und unauffällig – von außen ist nicht zu erkennen, was im Paket ist.'],
  ['Gibt es Mengenrabatt oder Geschenke?','Bei ausgewählten Peptiden sparst du ab 3 Stück 10 %, ab 5 Stück 15 % und ab 10 Stück 20 %. Ab 200 € Bestellwert legen wir ein Injektionskit gratis dazu, ab 350 € GHK-Cu und ab 500 € Retatrutide.'],
  ['Wie funktioniert das Abo?','Du stellst einmal ein, was du regelmäßig brauchst. Ab der 2. Lieferung zahlst du dauerhaft 15 % weniger und kannst jederzeit pausieren oder kündigen. Alles dazu auf der Seite <a href="/abo-modell/">Abo-Modell</a>.'],
  ['Wofür sind die Produkte bestimmt?','Alle Produkte sind ausschließlich für die Laborforschung bestimmt – nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke.']
 ];
 var faq=el('<section id="sp-hp-faq" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Häufige Fragen</span><h2>Gut zu wissen.</h2></div><div class="sp-faq">'+F.map(function(f,i){return '<details'+(i===0?' open':'')+'><summary>'+f[0]+'</summary><p>'+f[1]+'</p></details>';}).join('')+'</div></div></section>');
 root.appendChild(faq);

 /* Laufband: beide Reihen gleich schnell (Dauer aus der echten Breite, ~28 px/s) */
 function mqSpeed(){document.querySelectorAll('.sp-mq .tr').forEach(function(tr){var w=tr.scrollWidth/2;if(w>0)tr.style.animationDuration=(w/28).toFixed(1)+'s';});}
 mqSpeed();window.addEventListener('load',mqSpeed);window.addEventListener('resize',mqSpeed);
 /* Newsletter in die Seite holen */
 var nl=document.getElementById('sp-nlh'); if(nl) root.appendChild(nl);
 /* Hinweis + Mehr ueber Peptrium */
 var info=el('<div id="sp-hp-info"><div class="dis"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5058" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div><br><button type="button">Mehr über Peptrium <span>▾</span></button></div>');
 root.appendChild(info);
 var seo=['045a884','a4ab146'].map(function(id){return root.querySelector('[data-id="'+id+'"]');}).filter(Boolean);
 seo.forEach(function(e){e.classList.add('sp-hp-closed');});
 var ib=info.querySelector('button');ib.onclick=function(){var o=ib.classList.toggle('open');seo.forEach(function(e){e.classList.toggle('sp-hp-closed',!o);});};
})();
