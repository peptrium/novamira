(function(){
var PH=window.BWP||'bw', BF=PH==='bf', PCT=BF?35:30, F=1-PCT/100;
var END=BF?new Date('2026-11-30T23:59:59+01:00'):new Date('2026-11-26T23:59:59+01:00');
var NOW=window.BWNOW?new Date(window.BWNOW):new Date();
function fm(n){return n.toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.')+' €';}
function num(s){return parseFloat(String(s).replace(/[^\d,]/g,'').replace(',','.'))||0;}
function cd(){var s=Math.max(0,(END-NOW)/1000);return [Math.floor(s/86400),Math.floor(s%86400/3600),Math.floor(s%3600/60),Math.floor(s%60)].map(function(x){return (x<10?'0':'')+x;});}
var c=cd();
var css=`
:root{--bwr:#FF2D2D;--bwr2:#C3001B}
#sp-top-banner.bw{background:${BF?'#fff':'linear-gradient(90deg,#B0001A,#FF2D2D 50%,#B0001A)'}!important;color:${BF?'#07080A':'#fff'}!important}
#sp-top-banner.bw .sp-tb-mask{display:none!important}
.bw-tb{display:flex;align-items:center;justify-content:center;gap:10px;height:100%;font:800 12.5px/1 Sora,Inter,sans-serif;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap}
.bw-tb i{font-style:normal;font-variant-numeric:tabular-nums;background:${BF?'#07080A':'rgba(0,0,0,.28)'};color:#fff;padding:5px 8px;border-radius:6px;letter-spacing:.02em}
.sp-hero2.bw .sp-hero2-img-wrap{background:#000!important;position:relative;overflow:hidden}
.bw-glow{position:absolute;inset:0;pointer-events:none;z-index:1;background:radial-gradient(60% 34% at 50% 66%,${BF?'rgba(255,45,45,.55)':'rgba(255,45,45,.38)'},transparent 70%),radial-gradient(90% 40% at 50% 100%,rgba(255,45,45,.18),transparent 70%);mix-blend-mode:screen}
.bw-lines{position:absolute;left:0;right:0;top:0;height:260px;z-index:1;pointer-events:none;background:repeating-linear-gradient(115deg,rgba(255,255,255,.035) 0 1px,transparent 1px 22px)}
.sp-hero2.bw .sp-hero2-text{top:96px!important;z-index:3!important;text-align:left}
.bw-ey{display:inline-flex;align-items:center;gap:7px;font:800 11px/1 Sora,sans-serif;letter-spacing:.24em;color:#fff;padding:7px 11px;border-radius:999px;background:${BF?'var(--bwr)':'rgba(255,255,255,.08)'};border:1px solid ${BF?'var(--bwr)':'rgba(255,255,255,.22)'};margin-bottom:6px}
.bw-ey b{width:6px;height:6px;border-radius:50%;background:${BF?'#fff':'var(--bwr)'};box-shadow:0 0 10px ${BF?'#fff':'var(--bwr)'}}
.sp-hero2.bw .sp-hero2-title{font:800 86px/.86 Sora,sans-serif!important;letter-spacing:-.05em!important;margin:4px 0 2px!important;
 background:${BF?'linear-gradient(180deg,#FF7373 0%,#FF2D2D 55%,#B3001A 100%)':'linear-gradient(180deg,#FFFFFF 0%,#E3E6EA 38%,#80868E 52%,#F4F5F7 70%,#A6ACB3 100%)'}!important;-webkit-background-clip:text!important;background-clip:text!important;color:transparent!important;-webkit-text-fill-color:transparent!important;filter:drop-shadow(0 6px 26px ${BF?'rgba(255,45,45,.55)':'rgba(255,255,255,.12)'})}
.sp-hero2.bw .sp-hero2-title small{display:block;font:800 26px/1.1 Sora,sans-serif;letter-spacing:-.01em;-webkit-text-fill-color:#fff;color:#fff;margin-top:6px}
.sp-hero2.bw .sp-hero2-sub{color:rgba(255,255,255,.75)!important;font-size:14px!important;margin-top:6px!important}
.sp-hero2.bw .sp-hero2-img-wrap>picture img{transform:translateY(84px) scale(.94);transform-origin:50% 50%}
.sp-hero2-cta-wrap.bw{position:relative!important;inset:auto!important;transform:none!important;display:flex!important;flex-direction:column;align-items:stretch!important;gap:10px;margin:-34px 16px 0!important;z-index:3!important}.sp-hero2.bw .sp-hero2-stats{margin-top:22px!important}
.bw-cd{display:flex;gap:6px;justify-content:center}
.bw-cd div{width:62px;background:rgba(15,16,19,.85);border:1px solid rgba(255,255,255,.14);border-radius:10px;padding:6px 0 5px;text-align:center;backdrop-filter:blur(6px)}
.bw-cd b{display:block;font:800 21px/1.1 Sora,sans-serif;color:#fff;font-variant-numeric:tabular-nums}
.bw-cd span{font:700 9px/1 Inter,sans-serif;letter-spacing:.14em;color:rgba(255,255,255,.55)}
.sp-hero2-cta.bw-cta{justify-content:center;background:${BF?'linear-gradient(90deg,#E0001E,#FF2D2D)':'#fff'}!important;color:${BF?'#fff':'#07080A'}!important;font-weight:800!important;font-size:16px!important;padding:16px 22px!important;box-shadow:0 12px 32px ${BF?'rgba(255,45,45,.5)':'rgba(255,255,255,.18)'}!important}
.bw-ins{font:500 12.5px/1.35 Inter,sans-serif;color:rgba(255,255,255,.7);text-align:center}
.bw-ins u{color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.6);font-weight:700}
/* Karten */
.sp-pc .bd.bw-bd{background:var(--bwr)!important;color:#fff!important;font-weight:800!important;letter-spacing:.02em}
.sp-pc .pr.bw-pr{display:flex;align-items:baseline;gap:6px;flex-wrap:wrap}
.sp-pc .pr.bw-pr strong{color:#07080A}
.sp-pc .pr.bw-pr s{font-size:12px;color:#8A9098;font-weight:500}
.sp-pc .qd.bw-qd{color:#1E7A46!important;background:#E5F4EB!important;font-weight:700!important}
/* Kaufbox */
.bw-pricerow{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
.bw-pricerow s{font-size:16px;color:#8A9098;font-weight:500}
.bw-tag{display:inline-block;background:var(--bwr);color:#fff;font:800 12px/1 Sora,sans-serif;padding:5px 8px;border-radius:7px;vertical-align:4px}
.bw-bb{margin:12px 0 4px;background:#07080A;color:#fff;border-radius:14px;padding:13px 14px;display:grid;gap:8px;position:relative;overflow:hidden}
.bw-bb:before{content:"";position:absolute;right:-40px;top:-60px;width:180px;height:180px;background:radial-gradient(closest-side,rgba(255,45,45,.45),transparent);pointer-events:none}
.bw-bb .h{display:flex;justify-content:space-between;align-items:center;gap:8px;position:relative}
.bw-bb .h b{font:800 14px/1.2 Sora,sans-serif}
.bw-bb .h em{font-style:normal;font:800 15px/1 Sora,sans-serif;color:#FF6B6B;white-space:nowrap}
.bw-bb .t{display:flex;gap:5px;position:relative}
.bw-bb .t span{flex:1;text-align:center;background:#16181D;border:1px solid #2A2D33;border-radius:8px;padding:5px 0;font:800 15px/1.1 Sora,sans-serif;font-variant-numeric:tabular-nums}
.bw-bb .t span small{display:block;font:600 8.5px/1 Inter,sans-serif;letter-spacing:.12em;color:#8E949C;margin-top:2px}
.bw-bb .n{font:500 12px/1.4 Inter,sans-serif;color:#B7BCC3;position:relative}
.bw-bb .n b{color:#fff}
/* Kategorie */
.bw-strip{margin:0 0 14px;background:#07080A;color:#fff;border-radius:14px;padding:12px 14px;display:flex;justify-content:space-between;align-items:center;gap:10px;border:1px solid #23262C}
.bw-strip b{font:800 15px/1.2 Sora,sans-serif}.bw-strip b em{font-style:normal;color:#FF5252}
.bw-strip span{font:600 11.5px/1.3 Inter,sans-serif;color:#9AA0A8;display:block;margin-top:2px}
.bw-strip i{font-style:normal;font:800 12px/1 Sora,sans-serif;background:var(--bwr);padding:7px 9px;border-radius:8px;white-space:nowrap;font-variant-numeric:tabular-nums}
`;
var st=document.createElement('style');st.textContent=css;document.head.appendChild(st);
var cdtxt=c[0]+'T '+c[1]+':'+c[2]+':'+c[3];
// Banner
var tb=document.getElementById('sp-top-banner');
if(tb){tb.classList.add('bw');var d=document.createElement('div');d.className='bw-tb';d.innerHTML=(BF?'Black Friday · −35 % auf alles':'Black Week · −30 % auf alles')+' <i>'+cdtxt+'</i>';tb.appendChild(d);}
// Hero
var h=document.querySelector('.sp-hero2');
if(h){h.classList.add('bw');var w=h.querySelector('.sp-hero2-img-wrap');
 var g=document.createElement('div');g.className='bw-glow';w.appendChild(g);var l=document.createElement('div');l.className='bw-lines';w.appendChild(l);
 var tx=h.querySelector('.sp-hero2-text');
 tx.querySelector('.sp-hero2-title').innerHTML='−'+PCT+'%<small>'+(BF?'Der größte Rabatt des Jahres.':'auf alle Peptide, Pens & Zubehör.')+'</small>';
 tx.querySelector('.sp-hero2-sub').textContent=BF?'Nur bis Cyber Monday, 30.11. um 23:59.':'Black Week bis Donnerstag, 26.11.';
 var ey=document.createElement('div');ey.className='bw-ey';ey.innerHTML='<b></b>'+(BF?'BLACK FRIDAY':'BLACK WEEK');tx.insertBefore(ey,tx.firstChild);
 var cw=h.querySelector('.sp-hero2-cta-wrap');cw.classList.add('bw');
 w.parentNode.insertBefore(cw,w.nextSibling);var a=cw.querySelector('.sp-hero2-cta');a.classList.add('bw-cta');a.firstChild.textContent='Jetzt '+PCT+' % sparen ';
 var k=document.createElement('div');k.className='bw-cd';k.innerHTML=['Tage','Std','Min','Sek'].map(function(x,i){return '<div><b>'+c[i]+'</b><span>'+x.toUpperCase()+'</span></div>';}).join('');cw.insertBefore(k,a);
 var ins=document.createElement('div');ins.className='bw-ins';ins.innerHTML=BF?'Solange der Vorrat reicht · Versand in 2 Werktagen':'Freitag wird’s noch besser. <u>Insider erfahren es zuerst →</u>';cw.appendChild(ins);
}
// Karten
document.querySelectorAll('.sp-pc[data-id]').forEach(function(cd){
 var on=cd.querySelector('.vo button.on')||cd.querySelector('.vo button');var pr=cd.querySelector('.pr');if(!pr)return;
 var p=on?parseFloat(on.getAttribute('data-p')):num(pr.textContent);if(!p)return;
 pr.classList.add('bw-pr');pr.innerHTML='<strong>'+fm(p*F)+'</strong><s>'+fm(p)+'</s>';
 var bd=cd.querySelector('.bd');if(!bd){bd=document.createElement('span');bd.className='bd';var im=cd.querySelector('a.im');if(im)im.appendChild(bd);}bd.className='bd bw-bd';bd.textContent='−'+PCT+' %';
 var q=cd.querySelector('.qd');if(!q){q=document.createElement('div');q.className='qd';pr.parentNode.insertBefore(q,pr);}q.classList.add('bw-qd');q.textContent='Du sparst '+fm(p-p*F);
});
// Kategorie-Streifen
var cat=document.querySelector('#sp-cat .grid, #sp-cat .pcs, #sp-cat [class*="grid"]');
if(cat&&!document.querySelector('.bw-strip')){var s=document.createElement('div');s.className='bw-strip';s.innerHTML='<div><b>'+(BF?'Black Friday':'Black Week')+': <em>−'+PCT+' %</em> auf alles</b><span>Preise schon reduziert · kein Code nötig</span></div><i>'+cdtxt+'</i>';cat.parentNode.insertBefore(s,cat);}
// Kaufbox
var rp=document.getElementById('rx-price');
if(rp){var p0=num(rp.textContent);rp.innerHTML=fm(p0*F);var row=document.createElement('span');row.className='bw-pricerow';rp.parentNode.insertBefore(row,rp);row.appendChild(rp);row.insertAdjacentHTML('beforeend','<s>'+fm(p0)+'</s><span class="bw-tag">−'+PCT+' %</span>');
 document.querySelectorAll('#rx-vol-tiers .rx-tier__disc').forEach(function(e){e.textContent=fm(num(e.textContent)*F);});
 document.querySelectorAll('#rx-tiers .rx-tier').forEach(function(t){var q=+t.getAttribute('data-q');var e=t.querySelector('.rx-tier__disc');if(e)e.textContent=q===1?'Standard':fm(p0*F*q).replace(' €','€');});
 var fl=document.querySelector('.rx-tier__flag');if(fl)fl.style.display='none';
 var bb=document.createElement('div');bb.className='bw-bb';bb.innerHTML='<div class="h"><b>'+(BF?'Black Friday':'Black Week')+' · −'+PCT+' %</b><em>Du sparst '+fm(p0-p0*F)+'</em></div><div class="t">'+['Tage','Std','Min','Sek'].map(function(x,i){return '<span>'+c[i]+'<small>'+x.toUpperCase()+'</small></span>';}).join('')+'</div><div class="n">'+(BF?'Gilt automatisch, kein Code nötig. Geschenke ab 200 € bleiben.':'Gilt automatisch, kein Code nötig. <b>Freitag wird’s noch besser</b> – Insider erfahren es zuerst.')+'</div>';
 var pr=document.querySelector('#rx-buybox .rx-price-row');pr.parentNode.insertBefore(bb,pr.nextSibling);
 var sh=document.querySelector('#rx-shiphint .rx-amt-shine');if(sh)sh.textContent=fm(150-p0*F);
}
})();
