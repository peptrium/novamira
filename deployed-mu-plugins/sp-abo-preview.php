<?php
/**
 * Plugin Name: SP Abo-Seiten-Vorschau
 * Description: Abo-Seiten im Redesign (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * - Abo-Stack (743): "Weiter"/"Zurueck" behalten nach dem Antippen ihre Farbe (Astra faerbt fokussierte
 *   Buttons blau #045CB4 - auf dem iPhone bleibt der Fokus nach dem Tippen haengen -> blauer Weiter-Button).
 * - Abo-Modell (735): Ueberschriften im normalen Schwarz statt Dunkelblau (#1E293B); Kundenstimmen
 *   (#rv-wrap, alte schwarze Karten) ersetzt durch die dunkle Laufband-Sektion #sp-ab-proof wie auf den
 *   Produktseiten (gleiche Texte, Karten-Engine mqStart aus pp.js).
 * Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    if (!function_exists('sp_hpv_token_ok') || !sp_hpv_token_ok() || !is_page([735, 743])) {
        return;
    }
    ?>
<style id="sp-abp-css">
.sp-stack-nav .sp-stack-next,.sp-stack-nav .sp-stack-next:focus,.sp-stack-nav .sp-stack-next:active,.sp-stack-nav .sp-stack-next:focus-visible{background-color:#FF6B35!important;background-image:none!important;color:#fff!important;outline:none;box-shadow:none}
.sp-stack-nav .sp-stack-next:not(:disabled):hover{background-color:#E5342B!important}
.sp-stack-nav .sp-stack-back,.sp-stack-nav .sp-stack-back:focus,.sp-stack-nav .sp-stack-back:active{background-color:#fff!important;background-image:none!important;color:#0D0F12!important;outline:none}
.sp-stack-nav{box-shadow:0 -8px 20px rgba(13,15,18,.06)}
body.page-id-735 .sp-abo h2{color:#0D0F12!important}
body.page-id-735 .sp-closing h2,body.page-id-735 .sp-closing h3{color:#fff!important}
#sp-ab-proof.sp-hp-sec{font-family:Sora,sans-serif;padding:56px 20px;position:relative}
#sp-ab-proof.sp-hp-sec .in{max-width:1140px;margin:0 auto}
#sp-ab-proof.sp-hp-sec .hd{text-align:center;margin:0 0 26px}
#sp-ab-proof.sp-hp-sec h2{font:700 clamp(26px,3.4vw,38px)/1.15 Sora,sans-serif;letter-spacing:-.02em;margin:0 0 10px;color:#0D0F12}
#sp-ab-proof.sp-hp-sec .sub{font-size:15px;line-height:1.6;color:#5A6068;margin:0 auto;max-width:560px}
 #sp-ab-proof.sp-hp-sec{padding:44px 16px}
#sp-ab-proof .sp-sum{display:flex;align-items:center;justify-content:center;gap:16px;margin:0 auto 10px;padding:14px 20px;max-width:440px;background:#fff;border:1px solid #E3E6E9;border-radius:18px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
#sp-ab-proof .sp-sum .big{font:800 40px/1 Sora,sans-serif;color:#0D0F12}
#sp-ab-proof .sp-sum .st{color:#F5A623;font-size:17px;letter-spacing:2px}
#sp-ab-proof .sp-sum .t{font-size:12.5px;color:#5A6068;line-height:1.45}
#sp-ab-proof .sp-sum .t b{color:#0D0F12}
#sp-ab-proof .sp-stage{position:relative;margin:0 -16px;padding:40px 0 30px}
#sp-ab-proof .sp-mq{position:relative;overflow:hidden;margin:0 0 14px;contain:paint}
#sp-ab-proof .sp-mq:before,#sp-ab-proof .sp-mq:after{content:'';position:absolute;top:0;bottom:0;width:28px;z-index:1;pointer-events:none}
#sp-ab-proof .sp-mq:before{left:0;background:linear-gradient(90deg,#F4F5F6,rgba(244,245,246,0))}
#sp-ab-proof .sp-mq:after{right:0;background:linear-gradient(270deg,#F4F5F6,rgba(244,245,246,0))}
#sp-ab-proof .sp-mq .tr{display:flex;gap:14px;width:max-content;animation:spMq 55s linear infinite;will-change:transform;backface-visibility:hidden;transform:translate3d(0,0,0)}
#sp-ab-proof .sp-mq.r2 .tr{animation-direction:reverse}
#sp-ab-proof .sp-mq:hover .tr,#sp-ab-proof .sp-mq:active .tr{animation-play-state:paused}
@keyframes spMq{from{transform:translate3d(0,0,0)}to{transform:translate3d(-50%,0,0)}}
#sp-ab-proof .sp-rv{flex:0 0 270px;display:flex;flex-direction:column;gap:10px;background:#fff;border:1px solid #E3E6E9;border-radius:18px;padding:16px}
#sp-ab-proof .sp-rv .st{color:#F5A623;font-size:13px;letter-spacing:2px}
#sp-ab-proof .sp-rv p{margin:0;font-size:13.5px;line-height:1.55;color:#2A2F35;flex:1;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;min-height:4.65em}
#sp-ab-proof .sp-rv .au{display:flex;align-items:center;gap:10px;font-size:12px;color:#5A6068}
#sp-ab-proof .sp-rv .au i{font-style:normal;width:32px;height:32px;border-radius:50%;background:#0D0F12;color:#fff;font-weight:700;font-size:11.5px;display:flex;align-items:center;justify-content:center}
#sp-ab-proof .sp-rv .au b{display:block;color:#0D0F12;font-size:12.5px}
#sp-ab-proof .sp-rv .au em{font-style:normal;color:#2E9B57;font-size:11px;font-weight:600}
@media (prefers-reduced-motion:reduce){.sp-mq .tr{animation:none}}
#sp-ab-proof .sp-mq{contain:none!important}
#sp-ab-proof .sp-mq .tr{animation-play-state:paused!important;will-change:auto!important}
#sp-ab-proof .sp-mq .tr.go{animation-play-state:running!important}
#sp-ab-proof .sp-mq:hover .tr.go,#sp-ab-proof .sp-mq:active .tr.go{animation-play-state:paused!important}
#sp-ab-proof .sp-mq .sp-rv{transform:translateZ(0);-webkit-transform:translateZ(0);backface-visibility:hidden;-webkit-backface-visibility:hidden}
#sp-ab-proof .sp-mq .tr.js{position:relative;display:block!important;width:auto!important;animation:none!important;transform:none!important}
#sp-ab-proof .sp-mq .tr.js>.sp-rv{position:absolute;top:0;left:0;width:270px;will-change:transform}
@media(min-width:901px){.sp-mq .tr.js>.sp-rv{width:300px}}
#sp-ab-proof .sp-mq .tr.js>.sp-rv{box-sizing:border-box}
#sp-ab-proof .sp-lbl.lt{border-color:rgba(13,15,18,.14)!important;background:#fff!important;color:#4A5058!important}
#sp-ab-proof .sp-lbl.lt:before{background:#0D0F12;box-shadow:none}
#sp-ab-proof.sp-hp-dark{background:linear-gradient(160deg,#0D0F12 0%,#1E2226 100%)}
#sp-ab-proof.sp-hp-dark h2{color:#fff!important}
#sp-ab-proof.sp-hp-dark .sub{color:rgba(255,255,255,.62)!important}
#sp-ab-proof h2 span{background:linear-gradient(100deg,#9AA3AD,#F4F6F8 50%,#B8BFC6);-webkit-background-clip:text;background-clip:text;color:transparent}
#sp-ab-proof .sp-sum{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12);box-shadow:none}
#sp-ab-proof .sp-sum .big{color:#fff}
#sp-ab-proof .sp-sum .t{color:#B9BEC5}#sp-ab-proof .sp-sum .t b{color:#fff}
#sp-ab-proof .sp-rv{background:#1A1E22;border-color:#2C3137}
#sp-ab-proof .sp-rv p{color:#D5D9DD}
#sp-ab-proof .sp-rv .au{color:#9AA0A8}#sp-ab-proof .sp-rv .au b{color:#fff}
#sp-ab-proof .sp-rv .au i{background:#E6E9EC;color:#0D0F12}
#sp-ab-proof .sp-mq:before{background:linear-gradient(90deg,#15181C,rgba(21,24,28,0))}
#sp-ab-proof .sp-mq:after{background:linear-gradient(270deg,#15181C,rgba(21,24,28,0))}
#sp-ab-proof .sp-stage{margin-left:-16px!important;margin-right:-16px!important}
#sp-ab-proof,#sp-ab-proof .sp-mq,#sp-ab-proof .sp-stage{overflow:hidden!important}
#sp-ab-proof{margin:34px calc(50% - 50vw) 30px!important;width:auto!important;border-radius:0}
#sp-ab-proof .hd{text-align:center}
#sp-ab-proof .sp-stage{padding:10px 0 6px}
#sp-ab-proof .sp-rv .au i{background:linear-gradient(135deg,#FF6B35,#E5342B);color:#fff}
html,body{overflow-x:clip}
body.page-id-735 .sp-abo>h2.sp-ab-old,body.page-id-735 .sp-abo>#rv-wrap{display:none!important}

</style>
<script id="sp-abp-js">
document.addEventListener('DOMContentLoaded',function(){(function(){
 var wrap=document.getElementById('rv-wrap');if(!wrap||!document.body.classList.contains('page-id-735'))return;
 var h=wrap.previousElementSibling;while(h&&h.tagName!=='H2')h=h.previousElementSibling;
 var rv=[],seen={};
 [].forEach.call(wrap.querySelectorAll('.rv-card'),function(c){var t=c.querySelector('.rv-text');if(!t)return;var k=t.textContent.trim();if(seen[k])return;seen[k]=1;var n=c.querySelector('.rv-name'),a=c.querySelector('.rv-avatar');rv.push({t:k,n:n?n.textContent.trim():'Verifizierter Kunde',a:a?a.textContent.trim():'✓',s:c.querySelectorAll('.rv-stars > svg').length||5});});
 if(rv.length<2)return;
 function card(r){return '<div class="sp-rv"><div class="st">'+'★★★★★'.slice(0,r.s)+'</div><p>„'+r.t+'“</p><div class="au"><i>'+r.a+'</i><div><b>'+r.n+'</b><em>✓ Verifizierter Kauf</em></div></div></div>';}
 function row(list,cls){return '<div class="sp-mq '+cls+'"><div class="tr">'+list.map(card).join('')+'</div></div>';}
 var h1=rv.filter(function(_,i){return i%2===0;}).slice(0,6),h2=rv.filter(function(_,i){return i%2===1;}).slice(0,6);
 var s=document.createElement('section');s.id='sp-ab-proof';s.className='sp-hp-sec sp-hp-dark';
 s.innerHTML='<div class="in"><div class="hd"><span class="sp-lbl">Kundenstimmen</span><h2>Was unsere Kunden sagen</h2></div><div class="sp-stage">'+row(h1,'r1')+row(h2,'r2')+'</div></div>';
 wrap.parentNode.insertBefore(s,wrap);if(h)h.classList.add('sp-ab-old');
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
})();});
</script>
    <?php
}, 100);
