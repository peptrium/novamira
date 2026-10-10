(function(){
 var root=document.querySelector('body.home .elementor-211'); if(!root)return;
 function el(h){var d=document.createElement('div');d.innerHTML=h.trim();return d.firstChild;}
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 function stars(n){return '★★★★★'.slice(0,Math.round(n));}
 /* Produkte */
 var P=window.SP_HP_PRODS||[];
 var cards=P.map(function(p,i){
   var bd=p.r?'<span class="bd s">−'+Math.round((1-p.p/p.r)*100)+' %</span>':(i===0?'<span class="bd">★ Bestseller</span>':(p.pen?'<span class="bd n">Neu</span>':''));
   return '<a class="sp-pc'+(p.pen?' pen':'')+'" href="'+p.u+'"><div class="im"><img loading="lazy" src="'+p.i+'" alt="'+p.n+'">'+bd+'</div><div class="tx"><h3 class="nm">'+p.n+'</h3>'
     +'<div class="rt"><b>'+stars(p.rt)+'</b>'+String(p.rt).replace('.',',')+' ('+p.rc+')</div>'
     +'<div class="pr"><strong>'+(p.v?'ab ':'')+eur(p.p)+'</strong>'+(p.r?'<s>'+eur(p.r)+'</s>':'')+'</div><div class="bt">Ansehen</div></div></a>';
 }).join('');
 var prod=el('<section id="sp-hp-prod" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Bestseller</span><h2>Peptide in Forschungsqualität</h2><p class="sub">Jede Charge HPLC-geprüft, mit Analysezertifikat – Versand aus Deutschland.</p></div>'
  +'<div class="sp-rail">'+cards+'<a class="sp-pc all" href="/alle-produkte/"><div class="ar">→</div><b>Alle Produkte</b><span>Das ganze Sortiment ansehen</span></a></div>'
  +'<ul class="sp-trust"><li>HPLC ≥ 99 % Reinheit</li><li>Analysezertifikat zu jeder Charge</li><li>Gratisversand ab 100 €</li></ul>'
  +'<div class="sp-hp-more-a"><a href="/alle-produkte/">Alle Produkte ansehen →</a></div></div></section>');
 root.appendChild(prod);
 /* Kundenstimmen: Texte aus den bisherigen Abschnitten uebernehmen */
 var rv=[];
 document.querySelectorAll('[data-id="d60a05b"] .rv-card').forEach(function(c){var t=c.querySelector('.rv-text'),n=c.querySelector('.rv-name'),a=c.querySelector('.rv-avatar');if(t&&t.textContent.trim().length>25)rv.push({t:t.textContent.trim(),n:n?n.textContent.trim():'Verifizierter Kunde',a:a?a.textContent.trim():'P',s:c.querySelectorAll('.rv-stars > svg').length||5});});
 document.querySelectorAll('[data-id="spsocial1"] .sps-quote-text').forEach(function(q){rv.push({t:q.textContent.trim(),n:'Verifizierter Kunde',a:'✓',s:5});});
 var seen={};rv=rv.filter(function(r){if(seen[r.t])return false;seen[r.t]=1;return true;}).slice(0,9);
 var proof=el('<section id="sp-hp-proof" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Kundenstimmen</span><h2>Was andere nicht liefern, liefert Peptrium.</h2></div>'
  +'<div class="sp-sum"><div class="big">4,8</div><div><div class="st">★★★★★</div><div class="t">aus <b>2.500+ Bestellungen</b><br>von verifizierten Käufern</div></div></div>'
  +'<div class="sp-rail">'+rv.map(function(r){return '<div class="sp-rv"><div class="st">'+'★★★★★'.slice(0,r.s)+'</div><p>„'+r.t+'“</p><div class="au"><i>'+r.a+'</i><div><b>'+r.n+'</b><em>✓ Verifizierter Kauf</em></div></div></div>';}).join('')+'</div>'
  +'<div class="sp-hint">← wischen für mehr Bewertungen →</div></div></section>');
 root.appendChild(proof);
 /* Newsletter in die Seite holen */
 var nl=document.getElementById('sp-nlh'); if(nl) root.appendChild(nl);
 /* Hinweis + Mehr ueber Peptrium */
 var info=el('<div id="sp-hp-info"><div class="dis"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5058" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div><br><button type="button">Mehr über Peptrium <span>▾</span></button></div>');
 root.appendChild(info);
 var seo=['045a884','a4ab146'].map(function(id){return root.querySelector('[data-id="'+id+'"]');}).filter(Boolean);
 seo.forEach(function(e){e.classList.add('sp-hp-closed');});
 var ib=info.querySelector('button');ib.onclick=function(){var o=ib.classList.toggle('open');seo.forEach(function(e){e.classList.toggle('sp-hp-closed',!o);});};
})();
