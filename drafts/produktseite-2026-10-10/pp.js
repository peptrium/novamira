document.addEventListener('DOMContentLoaded',function(){
 /* Abschnitte am Inhalt erkennen (jede Produktvorlage hat eigene Elementor-IDs) */
 var root=document.querySelector('body.single-product [data-elementor-type="product"]'); if(!root)return;
 root.classList.add('pp-root');
 [].slice.call(root.children).forEach(function(t){
   if(t.querySelector('#rx-buybox'))t.classList.add('pp-main');
   else if(t.querySelector('.rv-card')||/kundenstimmen/i.test(t.id||''))t.classList.add('pp-rev');
   else if(t.querySelector('.sp-sci-card'))t.classList.add('pp-lex');
   else if(t.querySelector('.sp-faq-item'))t.classList.add('pp-faq');
   else if(t.querySelector('.sp-rel-section'))t.classList.add('pp-rel');
   else if(/^vsvcont/.test(t.getAttribute('data-id')||''))t.classList.add('pp-vid');
   else if(/Wichtiger Hinweis/.test(t.textContent)&&t.textContent.length<600)t.classList.add('pp-dis');
 });
 function wid(sel){var e=root.querySelector(sel);return e?e.closest('.elementor-widget'):null;}
 var main=root.querySelector('.pp-main');
 if(main){var iw=main.querySelector('.elementor-widget-image');if(iw&&iw.parentElement)iw.parentElement.classList.add('pp-img');
   var bb=main.querySelector('#rx-buybox');var col=bb;while(col&&col.parentElement&&!col.parentElement.classList.contains('e-con-inner')&&col.parentElement!==main)col=col.parentElement;if(col)col.classList.add('pp-col');
   [['.sp-reta-hero','pp-herow'],['.sp-reta-qualitybox','pp-qual'],['#rx-buybox','pp-bbw'],['.sp-pen-promo','pp-penw']].forEach(function(x){var w=wid(x[0]);if(w)w.classList.add(x[1]);});}
 document.body.classList.add('pp-ready');
 var D=window.SP_PP||{};
 function el(h){var d=document.createElement('div');d.innerHTML=h.trim();return d.firstChild;}
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 var cart='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg>';
 function addIds(ids,b,done){var old=b.innerHTML,i=0,last=null;b.disabled=true;b.textContent='…';
   (function next(){if(i>=ids.length){b.classList.add('ok');b.innerHTML=done;if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[last&&last.fragments,last&&last.cart_hash,jQuery(b)]);setTimeout(function(){b.classList.remove('ok');b.innerHTML=old;b.disabled=false;},2400);return;}
     var fd=new FormData();fd.append('product_id',ids[i]);fd.append('quantity','1');
     fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){last=r;i++;next();}).catch(function(){b.innerHTML=old;b.disabled=false;});})();}
 /* 0) Oberer Teil: Bild-Badges + Vertrauens-Zeile in der Kaufbox */
 var imc=document.querySelector('.pp-img');
 var bdh=(D.pre?'<span class="p">Vorbestellung</span>':(D.best?'<span>★ Bestseller</span>':(D.pen?'<span>Neu</span>':'')))+(D.acc?'':'<span class="g">✓ HPLC ≥ 99 %</span>');
 if(imc&&bdh&&!imc.querySelector('.sp-img-bd')){imc.appendChild(el('<div class="sp-img-bd">'+bdh+'</div>'));}
 var short={'99 % Reinheit (HPLC)':'99 % Reinheit','LC-MS Identitätsverifizierung':'LC-MS geprüft','Chargenspezifisches Analysezertifikat (COA)':'Versand aus DE','Diskrete Verpackung & schneller Versand':'Diskret verpackt','Dosiszähler – exakt per Klick':'Exakter Dosiszähler','Kein Rekonstituieren nötig':'Fertig gemischt','Dosis frei einstellbar':'Dosis einstellbar','Sauber & hygienisch':'Hygienisch','CE-zertifiziert (EN ISO 13485:2016)':'CE-zertifiziert','Tri-Bevel-Spitze, doppelte Silikonbeschichtung':'Tri-Bevel-Spitze'};
 var pn=document.querySelector('.sp-pen-promo-name'),pb=document.querySelector('.sp-pen-promo-blurb'),pp=document.querySelector('.sp-pen-promo-price');
 if(pn&&!pn.querySelector('em')){pn.innerHTML='Auch als Peptrium-Pen <em>NEU</em>';}
 if(pb&&pp){pb.textContent='Fertig gemischt, kein Anmischen · '+pp.textContent.trim();}
 document.querySelectorAll('.sp-reta-check-item span').forEach(function(s){var t=s.textContent.trim();if(short[t])s.textContent=short[t];});
 document.querySelectorAll('.sp-reta-check-item').forEach(function(i){if(i.scrollWidth>i.clientWidth+1)i.classList.add('wrap');});
 var cta=document.querySelector('#rx-buybox .rx-buycta');
 if(cta&&!document.querySelector('.sp-bb-trust')){cta.parentNode.insertBefore(el('<div class="sp-bb-trust"><div><b>🚚</b>Gratisversand ab 100 €</div><div><b>📦</b>Neutral verpackt</div><div><b>🔒</b>Sicher bezahlen</div></div>'),cta.nextSibling);}
 /* Vorbestellung: Hinweis + Button-Text */
 if(D.pre){var cta2=document.querySelector('#rx-buybox .rx-buycta');if(cta2&&!document.querySelector('.sp-pre-note')&&!/vorbestellbar/i.test(document.getElementById('rx-buybox').textContent))cta2.parentNode.insertBefore(el('<div class="sp-pre-note"><i>Vorbestellung</i><span><b>Jetzt mit Preisvorteil sichern.</b> Wir liefern, sobald die neue Ware eintrifft.</span></div>'),cta2);
   var rb=document.querySelector('form.cart .single_add_to_cart_button');if(rb)rb.textContent='Vorbestellen';}
 /* 1) Kundenstimmen: dunkel, Laufband + Flasche */
 var rv=[];
 document.querySelectorAll('.pp-rev .rv-card').forEach(function(c){var t=c.querySelector('.rv-text'),n=c.querySelector('.rv-name'),a=c.querySelector('.rv-avatar');if(t)rv.push({t:t.textContent.trim(),n:n?n.textContent.trim():'Verifizierter Kunde',a:a?a.textContent.trim():'✓',s:c.querySelectorAll('.rv-stars > svg').length||5});});
 if(rv.length<4)(D.extraReviews||[]).forEach(function(t){rv.push({t:t,n:'Verifizierter Kunde',a:'✓',s:5});});
 function card(r){return '<div class="sp-rv"><div class="st">'+'★★★★★'.slice(0,r.s)+'</div><p>„'+r.t+'“</p><div class="au"><i>'+r.a+'</i><div><b>'+r.n+'</b><em>✓ Verifizierter Kauf</em></div></div></div>';}
 function row(list,cls){return '<div class="sp-mq '+cls+'"><div class="tr">'+list.map(card).join('')+'</div></div>';}
 var h1=rv.filter(function(_,i){return i%2===0;}).slice(0,6),h2=rv.filter(function(_,i){return i%2===1;}).slice(0,6);
 var proof=el('<section id="sp-pp-proof" class="sp-pp sp-hp-sec sp-hp-dark'+(D.bottle?'':' nob')+(D.pen&&D.bottle?' pen':'')+'"><div class="in"><div class="hd"><span class="sp-lbl">Kundenstimmen</span><h2>Das sagen Kunden über <span>'+D.name+'</span>.</h2></div>'
  +'<div class="sp-sum"><div class="big">'+D.rt+'</div><div><div class="st">★★★★★</div><div class="t">aus <b>'+D.rc+' Bewertungen</b><br>von verifizierten Käufern</div></div></div>'
  +'<div class="sp-stage">'+row(h1,'r1')+row(h2,'r2')+(D.bottle?'<div class="sp-glow"></div><div class="sp-bshadow"></div><div class="sp-bottle"><img src="'+D.bottle+'" alt="'+D.name+'"></div>':'')+'</div></div></section>');
 root.appendChild(proof);
 if(D.bottle){var stage=proof.querySelector('.sp-stage'),bottle=proof.querySelector('.sp-bottle'),bsh=proof.querySelector('.sp-bshadow'),cur=0,tgt=0,run=false,vis=false;
 function target(){var r=stage.getBoundingClientRect(),vh=window.innerHeight;var p=((r.top+r.height/2)-vh/2)/vh;return Math.max(-1,Math.min(1,p));}
 function paint(p){bottle.style.transform='translate3d(0,'+(p*110).toFixed(1)+'px,0) rotate('+(D.pen?p*-12:p*-22+6).toFixed(2)+'deg)';bsh.style.transform='translate3d(0,'+(p*40).toFixed(1)+'px,0) scale('+(1-Math.abs(p)*0.25).toFixed(3)+')';bsh.style.opacity=(1-Math.abs(p)*0.5).toFixed(2);}
 function loop(){cur+=(tgt-cur)*0.12;paint(cur);if(Math.abs(tgt-cur)>0.0005&&vis){requestAnimationFrame(loop);}else{run=false;}}
 function kick(){tgt=target();if(!run&&vis){run=true;requestAnimationFrame(loop);}}
 if('IntersectionObserver' in window){new IntersectionObserver(function(e){vis=e[0].isIntersecting;if(vis)kick();},{rootMargin:'200px 0px'}).observe(stage);}else{vis=true;}
 window.addEventListener('scroll',kick,{passive:true});cur=tgt=target();paint(cur);}

 /* Laufband: beide Reihen gleich schnell (Dauer aus der echten Breite, ~28 px/s) */
 /* Laufband: jede Karte bewegt sich einzeln (kein breiter Streifen -> sofort sichtbar, auch in iOS Safari) */
 function mqStart(){document.querySelectorAll('.sp-mq').forEach(function(m){if(m.getAttribute('data-go'))return;m.setAttribute('data-go','1');
   var tr=m.querySelector('.tr');var cards=[].slice.call(tr.children);if(!cards.length)return;
   var dir=m.classList.contains('r2')?1:-1,speed=46,gap=14,off=0,last=0,vis=true,hold=false,step,total,H;
   function layout(){var w=cards[0].getBoundingClientRect().width||270;step=w+gap;
     var need=m.clientWidth+2*step;while(cards.length*step<need){var c=cards.slice(0,Math.max(1,cards.length/2|0)).map(function(x){var y=x.cloneNode(true);tr.appendChild(y);return y;});cards=cards.concat(c);}
     total=cards.length*step;H=0;cards.forEach(function(c){c.style.height='';H=Math.max(H,c.offsetHeight);});cards.forEach(function(c){c.style.height=H+'px';});tr.style.height=H+'px';}
   function paint(){cards.forEach(function(c,i){var x=((i*step+dir*off)%total+total)%total-step;c.style.transform='translate3d('+x.toFixed(1)+'px,0,0)';});}
   function tick(t){if(!vis){last=0;return;}if(last&&!hold)off+=speed*Math.min(.05,(t-last)/1000);last=t;paint();requestAnimationFrame(tick);}
   tr.classList.add('js');layout();off=dir>0?step*0.45:0;paint();
   if('IntersectionObserver' in window){new IntersectionObserver(function(e){var v=e[0].isIntersecting;if(v&&!vis){vis=true;requestAnimationFrame(tick);}vis=v;},{rootMargin:'150px 0px'}).observe(m);}
   m.addEventListener('touchstart',function(){hold=true;},{passive:true});m.addEventListener('touchend',function(){hold=false;},{passive:true});
   m.addEventListener('mouseenter',function(){hold=true;});m.addEventListener('mouseleave',function(){hold=false;});
   window.addEventListener('resize',function(){layout();paint();});
   if(window.matchMedia('(prefers-reduced-motion: reduce)').matches){paint();return;}
   requestAnimationFrame(tick);});}
 mqStart();
 /* 2) Passt dazu: Sets */
 var S=D.sets||[];
 if(S.length){var sets=el('<section id="sp-pp-sets" class="sp-pp sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Passt dazu</span><h2>Komplett in einem Klick.</h2><p class="sub">Alles, was du rund um '+D.name+' brauchst – zusammen in den Warenkorb.</p></div><div class="sp-rail">'
  +S.map(function(s,i){var t=s.items.map(function(it){return '<span class="'+(it.w?'w':'')+'"><img loading="lazy" src="'+it.i+'" alt=""></span>';}).join('');
    return '<div class="sp-set" data-set="'+i+'"><div class="thumbs">'+t+'<i>'+s.items.length+' Artikel</i></div><h3>'+s.n+'</h3><p class="why">'+s.d+'</p><ul>'+s.items.map(function(it){return '<li><span>'+it.n+'</span><span>'+eur(it.p)+'</span></li>';}).join('')+'</ul>'
     +'<div class="sum"><span class="ship'+(s.t>=100?'':' no')+'">'+(s.t>=100?'✓ Gratisversand':'noch '+eur(100-s.t)+' bis Gratisversand')+'</span><strong>'+eur(s.t)+'</strong></div><button type="button" class="add">'+cart+'Set in den Warenkorb</button></div>';}).join('')
  +'</div></div></section>');
  root.appendChild(sets);
  sets.addEventListener('click',function(e){var b=e.target.closest('.add');if(!b)return;var s=S[+b.closest('.sp-set').getAttribute('data-set')];addIds(s.items.map(function(x){return x.id;}),b,'✓ Set hinzugefügt');});}
 /* 3) Weitere Produkte (statt "Wird haeufig zusammen gekauft") */
 var P=D.more||[];
 if(P.length){var more=el('<section id="sp-pp-more" class="sp-pp sp-hp-sec sp-hp-dark"><div class="in"><div class="hd"><span class="sp-lbl">Auch beliebt</span><h2>Das könnte dich auch interessieren.</h2></div><div class="sp-rail">'
  +P.map(function(p){var vo=p.vol?'<div class="vo"><span>'+p.vol+'</span></div>':'';return '<div class="sp-pc'+(p.pen?' pen':'')+'" data-id="'+p.aid+'"><a class="im" href="'+p.u+'"><img loading="lazy" src="'+p.i+'" alt="'+p.n+'">'+(p.pre?'<span class="bd p">Vorbestellung'+(p.r?' · −'+Math.round((1-p.p/p.r)*100)+' %':'')+'</span>':(p.r?'<span class="bd s">−'+Math.round((1-p.p/p.r)*100)+' %</span>':''))+'<span class="coa">✓ HPLC-verifiziert</span></a><div class="tx"><a href="'+p.u+'"><h3 class="nm">'+p.n+'</h3></a><div class="rt"><b>★★★★★</b>'+String(p.rt).replace('.',',')+' ('+p.rc+')</div>'+vo+'<div class="pr"><strong>'+(p.v?'ab ':'')+eur(p.p)+'</strong>'+(p.r?'<s>'+eur(p.r)+'</s>':'')+'</div>'+(p.v?'<a class="add" href="'+p.u+'">Größe wählen</a>':'<button type="button" class="add">'+cart+(p.pre?'Vorbestellen':'In den Warenkorb')+'</button>')+'</div></div>';}).join('')
  +'</div><div class="sp-hp-more-a"><a href="/alle-produkte/">Alle Produkte ansehen →</a></div></div></section>');
  root.appendChild(more);
  more.addEventListener('click',function(e){var b=e.target.closest('button.add');if(!b)return;addIds([b.closest('.sp-pc').getAttribute('data-id')],b,'✓ Hinzugefügt');});}
 /* 4) Hinweis als Zeile */
 root.appendChild(el('<div id="sp-pp-info"><div class="dis"><span>ⓘ</span><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div></div>'));
 /* 5) Sticky Kaufleiste */
 var real=document.querySelector('form.cart .single_add_to_cart_button');
 if(real){
  var bag='<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
  var bar=el('<div id="sp-pp-bar"><img src="'+D.thumb+'" alt=""><div class="t"><b>'+D.name+'</b><span class="pr">'+eur(D.price)+'</span></div><button type="button" class="cb" aria-label="Warenkorb öffnen">'+bag+'<i class="z">0</i></button><button type="button" class="go">'+cart+(D.pre?'Vorbestellen':'In den Warenkorb')+'</button></div>');
  document.body.appendChild(bar);
  var prEl=bar.querySelector('.pr');
  function total(){var c=null;document.querySelectorAll('form.cart *, .sp-bb *').forEach(function(e){if(!c&&e.children.length===0&&/^gesamt$/i.test(e.textContent.trim()))c=e;});
    if(c){var m=(c.parentElement.textContent||'').match(/(\d{1,3}(?:\.\d{3})*,\d{2})\s*€/);if(m)return m[1]+' €';}return null;}
  function sync(){var t=total();if(t)prEl.textContent=t;}
  function vis(){var r=real.getBoundingClientRect();var on=r.bottom<0;bar.classList.toggle('on',on);document.body.classList.toggle('sp-bar-on',on);}
  window.addEventListener('scroll',function(){vis();},{passive:true});
  document.addEventListener('change',sync);document.addEventListener('click',function(){setTimeout(sync,150);});
  bar.querySelector('button.go').addEventListener('click',function(){sync();real.click();});
  var fab=document.getElementById('sp-cart-fab'),fb=document.getElementById('sp-cart-fab-badge'),cbi=bar.querySelector('.cb i');
  bar.querySelector('.cb').addEventListener('click',function(){if(fab)fab.click();});
  function cnt(){if(!fb)return;var n=parseInt(fb.textContent,10)||0;cbi.textContent=n;cbi.classList.toggle('z',n<1);}
  if(fb){cnt();new MutationObserver(cnt).observe(fb,{childList:true,characterData:true,subtree:true});}
  sync();vis();
 }
});
