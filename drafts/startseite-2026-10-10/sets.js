/* Kombi-Sets: Auswahl Sorte (g) + Menge (l), gemeinsam fuer Startseite und Produktseiten */
window.SPSETS=(function(){
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 function q(t){return String(t).replace(/&/g,'&amp;').replace(/"/g,'&quot;');}
 function groups(s){var g=[];s.opts.forEach(function(o){var k=o.g||'';if(g.indexOf(k)<0)g.push(k);});return g;}
 function body(o){var t=o.items.map(function(it){return '<span class="'+(it.w?'w':'')+'"><img loading="lazy" src="'+it.i+'" alt=""></span>';}).join('');
  return '<div class="thumbs">'+t+'<i>'+o.items.length+' Artikel</i></div><ul>'+o.items.map(function(it){return '<li><span>'+it.n+'</span><span>'+eur(it.p)+'</span></li>';}).join('')+'</ul>'
   +'<div class="sum"><span class="ship'+(o.t>=100?'':' no')+'">'+(o.t>=100?'✓ Gratisversand':'noch '+eur(100-o.t)+' bis Gratisversand')+'</span><strong>'+eur(o.t)+'</strong></div>';}
 function chips(s,k){var o=s.opts[k],G=groups(s),h='',V=[];
  if(G.length>1)h+='<div class="vo vg">'+G.map(function(g){return '<button type="button" data-g="'+q(g)+'"'+((o.g||'')===g?' class="on"':'')+'>'+g+'</button>';}).join('')+'</div>';
  s.opts.forEach(function(x,j){if((x.g||'')===(o.g||'')&&x.l)V.push(j);});
  if(V.length>1)h+='<div class="vo vm">'+V.map(function(j){return '<button type="button" data-o="'+j+'"'+(j===k?' class="on"':'')+'>'+s.opts[j].l+'</button>';}).join('')+'</div>';
  return h;}
 function card(s,i,cart){var k=s.sel||0;return '<div class="sp-set" data-set="'+i+'" data-o="'+k+'"><h3>'+s.n+'</h3><p class="why">'+s.d+'</p><div class="ch">'+chips(s,k)+'</div><div class="body">'+body(s.opts[k])+'</div><button type="button" class="add">'+cart+'Set in den Warenkorb</button></div>';}
 function bind(sec,S,add){sec.addEventListener('click',function(e){var c=e.target.closest('.sp-set');if(!c)return;var s=S[+c.getAttribute('data-set')],cur=s.opts[+c.getAttribute('data-o')],k=-1;
  var gb=e.target.closest('.vg button'),vb=e.target.closest('.vm button');
  if(gb){var g=gb.getAttribute('data-g');s.opts.forEach(function(o,j){if(k<0&&(o.g||'')===g&&o.l===cur.l)k=j;});if(k<0)s.opts.forEach(function(o,j){if(k<0&&(o.g||'')===g)k=j;});}
  else if(vb)k=+vb.getAttribute('data-o');
  if(k>=0){c.setAttribute('data-o',k);c.querySelector('.ch').innerHTML=chips(s,k);c.querySelector('.body').innerHTML=body(s.opts[k]);return;}
  var b=e.target.closest('.add');if(b)add(cur.items.map(function(x){return x.id;}),b);});}
 return {card:card,bind:bind};
})();
