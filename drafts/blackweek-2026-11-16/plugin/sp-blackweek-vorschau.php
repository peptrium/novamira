<?php
/**
 * Plugin Name: SP Black Week – Vorschau
 * Description: (2026-11-17) Nur-Ansicht-Vorschau des Black-Week-/Black-Friday-Looks (Spotlight-Stil) für den Betreiber.
 *   Aktiv nur mit Vorschau-Cookie: /?sp_bw=vorschau&k=<token> (Black Week) bzw. &p=bf (Black Friday); /?sp_bw=aus beendet.
 *   Rein optisch: Preise werden im Browser um 30/35 % reduziert angezeigt, Warenkorb/Bestellung rechnen weiter mit Normalpreisen.
 *   Normale Besucher sehen nichts (Cookie umgeht den Seiten-Cache).
 */
if (!defined('ABSPATH')) exit;

function sp_bwv_token() {
    $t = get_option('sp_bwv_token');
    if (!$t) { $t = strtolower(wp_generate_password(10, false, false)); update_option('sp_bwv_token', $t, false); }
    return $t;
}
function sp_bwv_phase() {
    if (function_exists('sp_bw_phase')) return sp_bw_phase();
    $c = isset($_COOKIE['sp_bw_pv']) ? (string) $_COOKIE['sp_bw_pv'] : '';
    if ($c === 'bw' || $c === 'bf') return $c;
    return '';
}

add_action('init', function () {
    if (empty($_GET['sp_bw'])) return;
    $v = sanitize_key($_GET['sp_bw']);
    if ($v === 'aus') {
        setcookie('sp_bw_pv', '', time() - 3600, '/', '', is_ssl(), true);
        wp_safe_redirect(home_url('/')); exit;
    }
    if ($v === 'vorschau' && isset($_GET['k']) && hash_equals(sp_bwv_token(), (string) $_GET['k'])) {
        $p = (isset($_GET['p']) && $_GET['p'] === 'bf') ? 'bf' : 'bw';
        setcookie('sp_bw_pv', $p, time() + DAY_IN_SECONDS, '/', '', is_ssl(), true);
        $_COOKIE['sp_bw_pv'] = $p;
        nocache_headers();
        wp_safe_redirect(home_url('/')); exit;
    }
}, 1);

add_action('template_redirect', function () {
    if (isset($_COOKIE['sp_bw_pv']) && !defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
}, 0);

add_filter('body_class', function ($c) { if (sp_bwv_phase()) $c[] = 'sp-bwv'; return $c; });

add_action('wp_footer', function () {
    $ph = sp_bwv_phase();
    if (!$ph || is_admin()) return;
    $bf = $ph === 'bf';
    $cfg = [
        'bf'    => $bf,
        'pct'   => $bf ? 35 : 30,
        'end'   => $bf ? '2026-11-30T23:59:59+01:00' : '2026-11-26T23:59:59+01:00',
        'scene' => content_url('uploads/sp-bw/scene-' . ($bf ? 'bf' : 'bw') . '.webp'),
        'home'  => is_front_page(),
        'off'   => home_url('/?sp_bw=aus'),
        'live'  => function_exists('sp_bw_active') && sp_bw_active(),
        'pv'    => isset($_COOKIE['sp_bw_pv']),
        'insider' => (function_exists('sp_bw_opt') && time() >= (int) sp_bw_opt()['insider']),
    ];
    ?>
<style id="sp-bwv-css">
#sp-top-banner.bw{background:#050506!important;color:#C9CDD2!important;border-bottom:1px solid rgba(255,255,255,.08)}
#sp-top-banner.bw .sp-tb-mask{display:none!important}
.bw-tb{display:flex;align-items:center;justify-content:center;gap:10px;height:100%;font:600 10.5px/1 Inter,Sora,sans-serif;letter-spacing:.22em;text-transform:uppercase;white-space:nowrap;color:#C9CDD2}
.bw-tb b{color:#fff}.bw-tb i{font-style:normal;color:#8D939A;letter-spacing:.12em;font-variant-numeric:tabular-nums}
.bw-chrome{background:linear-gradient(180deg,#FFFFFF 0%,#F1F3F5 26%,#A3A9B1 48%,#5F656D 53%,#D9DDE1 68%,#FFFFFF 82%,#9AA0A8 100%);-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent}
.bw-ey{font:600 9.5px/1 Inter,Sora,sans-serif;letter-spacing:.42em;color:#9AA0A8}
/* Startseite */
.sp-hero2.bw .sp-hero2-img-wrap>picture,.sp-hero2.bw .sp-hero2-text,.sp-hero2.bw .sp-hero2-cta-wrap{display:none!important}
.sp-hero2.bw .sp-hero2-img-wrap{background:#000!important;height:auto!important}
.bwh{position:relative;height:810px;background:#000 center top/390px 810px no-repeat;text-align:center;overflow:hidden}
.bwh .t{position:absolute;left:0;right:0;top:84px}
.bwh.bwd{height:810px;background-color:#000;background-position:center top;margin-top:0}.bwh.bwd:before,.bwh.bwd:after{content:"";position:absolute;top:0;bottom:0;width:120px;pointer-events:none}.bwh.bwd:before{left:calc(50% - 196px);background:linear-gradient(90deg,#000 15%,transparent)}.bwh.bwd:after{left:calc(50% + 76px);background:linear-gradient(270deg,#000 15%,transparent)}.bwh.bwd .t,.bwh.bwd .b{z-index:2}
.bwh.bwd .b{left:50%;right:auto;width:358px;margin-left:-179px}
.bwh .ey2{font:600 10.5px/1 Inter,Sora,sans-serif;letter-spacing:.55em;color:#AEB4BB;padding-left:.55em}
.bwh .big{font:800 128px/.9 Sora,sans-serif;letter-spacing:-.06em;margin:12px 0 6px;filter:drop-shadow(0 10px 30px rgba(0,0,0,.8))}
.bwh .big .bw-pc{display:inline-block;font-size:.42em;line-height:1;vertical-align:top;margin:.08em 0 0 4px;letter-spacing:-.02em}
.bwh .l1{font:500 17px/1.3 Inter,sans-serif;color:#fff}.bwh .l2{font:400 13px/1.3 Inter,sans-serif;color:#8A9098;margin-top:5px}
.bwh .b{position:absolute;left:16px;right:16px;top:648px}
.bwh .cdn{display:flex;justify-content:center;gap:14px;font:300 12px/1 Inter,sans-serif;color:#8A9098;letter-spacing:.18em;margin-bottom:14px;font-variant-numeric:tabular-nums}
.bwh .cdn b{font:600 13px/1 Inter,sans-serif;color:#fff;letter-spacing:.06em;margin-right:3px}
.bwh .cta,.bwh .cta:hover{text-decoration:none!important}.bwh .cta{display:flex;align-items:center;justify-content:center;gap:8px;height:54px;border-radius:999px;background:#fff;color:#000!important;font:700 15.5px/1 Inter,sans-serif;text-decoration:none}
.bwh .lnk{font:500 12.5px/1 Inter,sans-serif;color:#9BA1A8;margin-top:13px}.bwh .lnk a{color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.4)}
/* Karten */
.sp-pc .bd.bw-bd{background:#fff!important;color:#07080A!important;font:800 11px/1 Sora,sans-serif!important;padding:6px 8px!important;border-radius:999px!important;box-shadow:0 4px 14px rgba(0,0,0,.35)}
.sp-bwv .sp-pc .coa{display:none!important}
.sp-pc .pr.bw-pr{display:flex;align-items:baseline;gap:7px;flex-wrap:wrap}
.sp-pc .pr.bw-pr s{font-size:12.5px;color:#9097A0;font-weight:500}
.sp-pc .qd.bw-qd{background:none!important;color:#6B7178!important;font:600 10px/1 Inter,sans-serif!important;letter-spacing:.2em!important;text-transform:uppercase;padding:0!important}
.bw-strip{margin:0 0 16px;background:radial-gradient(80% 140% at 50% 0%,#1C1E22,#050506 70%);color:#fff;border-radius:18px;padding:18px 18px 16px;border:1px solid rgba(255,255,255,.09);display:grid;gap:6px;text-align:center}
.bw-strip .bg{font:800 40px/.95 Sora,sans-serif;letter-spacing:-.05em}
.bw-strip .s{font:500 13px/1.3 Inter,sans-serif;color:#C9CDD2}
.bw-strip .c{font:500 11.5px/1 Inter,sans-serif;color:#8A9098;letter-spacing:.06em;font-variant-numeric:tabular-nums}.bw-strip .c b{color:#fff;font-weight:600}
/* Kaufbox */
.bw-pricerow{display:inline-flex;align-items:baseline;gap:9px;flex-wrap:wrap}
.bw-pricerow s{font-size:16px;color:#8A9098;font-weight:500}
.bw-tag{display:inline-block;background:#07080A;color:#fff;font:700 11px/1 Inter,sans-serif;padding:6px 9px;border-radius:999px;vertical-align:4px}
.bw-bb{margin:14px 0 6px;background:radial-gradient(90% 120% at 50% 0%,#1C1E22,#050506 72%);color:#fff;border-radius:16px;padding:16px 16px 14px;display:grid;gap:10px}
.bw-bb .h{display:flex;justify-content:space-between;align-items:flex-end;gap:8px}
.bw-bb .h .bg{font:800 34px/.9 Sora,sans-serif;letter-spacing:-.05em}
.bw-bb .h .r{text-align:right;font:600 13.5px/1.25 Inter,sans-serif}.bw-bb .h .r span{display:block;font:400 11.5px/1.3 Inter,sans-serif;color:#8A9098}
.bw-bb .ln{height:1px;background:linear-gradient(90deg,rgba(255,255,255,.3),rgba(255,255,255,.04))}
.bw-bb .c{display:flex;justify-content:space-between;font:500 12px/1 Inter,sans-serif;color:#8A9098}.bw-bb .c b{font:600 13.5px/1 Sora,sans-serif;color:#fff;letter-spacing:.06em;font-variant-numeric:tabular-nums}
.sp-bwv #sp-cd-total-savings-row,.sp-bwv #sp-abo-toggle,.sp-bwv .rx-tier__flag,.sp-bwv #sp-fsh-box,.sp-bwv .sp-fsh-box{display:none!important}
/* Warenkorb + Kasse */
.bw-dbar{margin:10px 14px 6px;background:radial-gradient(90% 140% at 50% 0%,#1E2024,#050506 72%);color:#fff;border-radius:16px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:10px}
.bw-dbar .bw-ey{display:block;margin-bottom:6px}.bw-dbar b{display:block;font:600 14px/1.2 Inter,sans-serif}.bw-dbar .t{font:400 11.5px/1.3 Inter,sans-serif;color:#8A9098}
.bw-dbar em{font-style:normal;font:800 24px/1 Sora,sans-serif;letter-spacing:-.03em;white-space:nowrap}
.bw-old{display:block;font-size:11.5px;color:#9097A0;font-weight:500;text-align:right;text-decoration:line-through}
.bw-badge{display:inline-block;background:#07080A;color:#fff;font:700 10px/1 Inter,sans-serif;padding:4px 7px;border-radius:999px;margin-bottom:5px}
.bw-co{display:flex;justify-content:space-between;align-items:center;gap:10px;background:radial-gradient(90% 140% at 50% 0%,#1E2024,#050506 72%);color:#fff;border-radius:14px;padding:13px 15px;margin:12px 16px 4px}
.bw-co b{font:600 14px/1.2 Inter,sans-serif}.bw-co em{font-style:normal;font:800 20px/1 Sora,sans-serif;white-space:nowrap}.bw-co small{display:block;font:400 11px/1.35 Inter,sans-serif;color:#8A9098;margin-top:4px}
#sp-bwv-pill{position:fixed;left:50%;transform:translateX(-50%);bottom:calc(14px + env(safe-area-inset-bottom,0px));z-index:99999;background:#fff;color:#000;font:700 11px/1 Inter,sans-serif;letter-spacing:.06em;padding:9px 13px;border-radius:999px;box-shadow:0 6px 20px rgba(0,0,0,.35);text-decoration:none!important;white-space:nowrap}
</style>
<script id="sp-bwv-js">
(function(){
var C=<?php echo wp_json_encode($cfg); ?>,BF=C.bf,PCT=C.pct,F=1-PCT/100,NAME=BF?'Black Friday':'Black Week',END=new Date(C.end);
function fm(n){return n.toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.')+' €';}
function num(s){return parseFloat(String(s).replace(/[^\d,]/g,'').replace(',','.'))||0;}
function cd(){var s=Math.max(0,(END-new Date())/1000);return [Math.floor(s/86400),Math.floor(s%86400/3600),Math.floor(s%3600/60),Math.floor(s%60)].map(function(x){return (x<10?'0':'')+x;});}
if(C.pv){var pill=document.createElement('a');pill.id='sp-bwv-pill';pill.href=C.off;pill.textContent='Vorschau beenden ✕';document.body.appendChild(pill);}
function banner(){var tb=document.getElementById('sp-top-banner');if(!tb)return;tb.classList.add('bw');var d=tb.querySelector('.bw-tb');if(!d){d=document.createElement('div');d.className='bw-tb';tb.appendChild(d);}var c=cd();d.innerHTML='<b>'+NAME+'</b><span>·</span>−'+PCT+' % auf alles<span>·</span><i>'+c[0]+' : '+c[1]+' : '+c[2]+'</i>';}
function heroD(){var hb=document.getElementById('sp-hero-banner');if(!hb||innerWidth<768)return;var x=document.querySelector('.bwh.bwd');if(!x){x=document.createElement('div');x.className='bwh bwd';x.style.backgroundImage='url('+C.scene+')';hb.parentNode.insertBefore(x,hb);hb.style.display='none';fill(x);}var c=cd();x.querySelector('.cdn').innerHTML='<span><b>'+c[0]+'</b>T</span><span><b>'+c[1]+'</b>STD</span><span><b>'+c[2]+'</b>MIN</span><span><b>'+c[3]+'</b>SEK</span>';}
function fill(x){x.innerHTML='<div class="t"><div class="ey2">'+NAME.toUpperCase()+'</div><div class="big"><span class="bw-chrome">−'+PCT+'</span><span class="bw-chrome bw-pc">%</span></div><div class="l1">'+(BF?'Der Höhepunkt der Black Week.':'auf das gesamte Sortiment.')+'</div><div class="l2">'+(BF?'Nur bis Cyber Monday, 30. November · 23:59':'Nur bis Donnerstag, 26. November')+'</div></div><div class="b"><div class="cdn"></div><a class="cta" href="/alle-produkte/">'+(BF?'Jetzt −35 % sichern':'Jetzt sparen')+' →</a><div class="lnk">'+(BF?'Solange der Vorrat reicht · Versand in 2 Werktagen':'Black Friday wird größer. <a href="#sp-nlh">Als Insider zuerst erfahren</a>')+'</div></div>';}
function hero(){if(!C.home)return;var h=document.querySelector('.sp-hero2');if(!h||h.offsetParent===null)return;var w=h.querySelector('.sp-hero2-img-wrap');if(!w)return;h.classList.add('bw');var x=w.querySelector('.bwh');
 if(!x){x=document.createElement('div');x.className='bwh';x.style.backgroundImage='url('+C.scene+')';
  x.innerHTML='<div class="t"><div class="ey2">'+NAME.toUpperCase()+'</div><div class="big"><span class="bw-chrome">−'+PCT+'</span><span class="bw-chrome bw-pc">%</span></div><div class="l1">'+(BF?'Der Höhepunkt der Black Week.':'auf das gesamte Sortiment.')+'</div><div class="l2">'+(BF?'Nur bis Cyber Monday, 30. November · 23:59':'Nur bis Donnerstag, 26. November')+'</div></div><div class="b"><div class="cdn"></div><a class="cta" href="/alle-produkte/">'+(BF?'Jetzt −35 % sichern':'Jetzt sparen')+' →</a><div class="lnk">'+(BF?'Solange der Vorrat reicht · Versand in 2 Werktagen':'Black Friday wird größer. <a href="#sp-nlh">Als Insider zuerst erfahren</a>')+'</div></div>';
  w.insertBefore(x,w.firstChild);
  var hd=document.querySelector('.sp-hero2');var top=hd.getBoundingClientRect().top+scrollY;var tb=document.getElementById('sp-top-banner');var tbh=tb?tb.offsetHeight:32;x.style.marginTop=(tbh-top)+'px';}
 var c=cd();x.querySelector('.cdn').innerHTML='<span><b>'+c[0]+'</b>T</span><span><b>'+c[1]+'</b>STD</span><span><b>'+c[2]+'</b>MIN</span><span><b>'+c[3]+'</b>SEK</span>';}
function cards(){document.querySelectorAll('.sp-pc[data-id]').forEach(function(cd){var pr=cd.querySelector('.pr');if(!pr)return;var on=cd.querySelector('.vo button.on')||cd.querySelector('.vo button');
  var p=on?(parseFloat(on.getAttribute('data-r'))||parseFloat(on.getAttribute('data-p'))||0):0;if(!p){if(pr.dataset.bwo)p=+pr.dataset.bwo;else p=num((pr.querySelector('s')||pr.querySelector('strong')||pr).textContent);}if(!p)return;
  if(pr.dataset.bwo==String(p)&&pr.querySelector('s'))return;pr.dataset.bwo=p;pr.classList.add('bw-pr');pr.innerHTML='<strong>'+fm(p*F)+'</strong><s>'+fm(p)+'</s>';
  var bd=cd.querySelector('a.im .bd');if(!bd){bd=document.createElement('span');var im=cd.querySelector('a.im');if(im)im.appendChild(bd);}if(bd&&!/Ausverkauft/.test(bd.textContent)){bd.className='bd bw-bd';bd.textContent='−'+PCT+' %';}
  var q=cd.querySelector('.qd');if(!q){q=document.createElement('div');q.className='qd';pr.parentNode.insertBefore(q,pr);}q.classList.add('bw-qd');q.textContent=NAME;});
 var cat=document.querySelector('#sp-cat .body');if(cat&&!document.querySelector('.bw-strip')){var s=document.createElement('div');s.className='bw-strip';cat.insertBefore(s,cat.firstChild);}
 var st=document.querySelector('.bw-strip');if(st){var c=cd();st.innerHTML='<div class="bw-ey">'+NAME.toUpperCase()+'</div><div class="bg bw-chrome">−'+PCT+' % auf alles</div><div class="s">Preise bereits reduziert. Kein Code nötig.</div><div class="c">Endet in <b>'+c[0]+'T · '+c[1]+':'+c[2]+':'+c[3]+'</b></div>';}}
function setOnce(el,orig,html){if(!el)return;if(el.dataset.bwSet===el.innerHTML)return;el.dataset.bwo=orig;el.innerHTML=html;el.dataset.bwSet=el.innerHTML;}
function buybox(){var rp=document.getElementById('rx-price');if(!rp)return;
 var po=document.querySelector('#rx-buybox .rx-price-old');if(po){po.style.display='none';var dc=document.querySelector('#rx-buybox .rx-discount-chip');if(dc)dc.style.display='none';}
 if(rp.dataset.bwSet!==rp.innerHTML){var p0=po?num(po.textContent):num(rp.textContent);if(!p0)return;rp.dataset.bwo=p0;rp.innerHTML=fm(p0*F)+' <s style="font-size:.5em;color:#8A9098;font-weight:500">'+fm(p0)+'</s> <span class="bw-tag">−'+PCT+' %</span>';rp.dataset.bwSet=rp.innerHTML;}
 var p=+rp.dataset.bwo;
 var tt=document.getElementById('rx-total');if(tt&&tt.dataset.bwSet!==tt.innerHTML){var t0=num(tt.textContent);var q=+((document.getElementById('rx-qtyfield')||{}).value||1);tt.innerHTML=fm(p*F*q);tt.dataset.bwSet=tt.innerHTML;}
 document.querySelectorAll('#rx-vol-tiers .rx-tier__disc').forEach(function(e){if(e.dataset.bwSet===e.innerHTML)return;e.innerHTML=fm(num(e.textContent)*F);e.dataset.bwSet=e.innerHTML;});
 document.querySelectorAll('#rx-tiers .rx-tier').forEach(function(t){var q=+t.getAttribute('data-q');var e=t.querySelector('.rx-tier__disc');if(!e)return;var v=q===1?'Einzeln':fm(p*F*q);if(e.textContent!==v)e.textContent=v;});
 var bb=document.querySelector('#rx-buybox .bw-bb');if(!bb){bb=document.createElement('div');bb.className='bw-bb';var prr=document.querySelector('#rx-buybox .rx-price-row');if(prr)prr.parentNode.insertBefore(bb,prr.nextSibling);}
 var c=cd();bb.innerHTML='<div class="bw-ey">'+NAME.toUpperCase()+'</div><div class="h"><div class="bg bw-chrome">−'+PCT+' %</div><div class="r">Du sparst '+fm(p*(1-F))+'<span>automatisch, ohne Code</span></div></div><div class="ln"></div><div class="c"><span>Endet in</span><b>'+c[0]+'T · '+c[1]+' : '+c[2]+' : '+c[3]+'</b></div>';
 var sh=document.querySelector('#rx-shiphint .rx-amt-shine');if(sh&&sh.dataset.bwSet!==sh.textContent){var v=Math.max(0,150-p*F);sh.textContent=fm(v);sh.dataset.bwSet=sh.textContent;}}
function drawer(){var dr=document.getElementById('sp-cart-drawer');if(!dr)return;var items=document.getElementById('sp-cd-items');if(!items)return;var reg=0;
 items.querySelectorAll('.sp-cd-item').forEach(function(it){var pe=it.querySelector('.sp-cd-item-price:not(.sp-cd-item-price-free)');if(!pe)return;
  if(pe.dataset.bwSet!==pe.innerHTML){var sv0=it.querySelector('.sp-cd-item-save');var o=C.live?Math.round(num(pe.textContent)/F*100)/100:num(pe.textContent)+(sv0?num(sv0.textContent):0);pe.dataset.bwo=o;pe.innerHTML=fm(o*F)+'<span class="bw-old">'+fm(o)+'</span>';pe.dataset.bwSet=pe.innerHTML;}
  reg+=+pe.dataset.bwo;var bd=it.querySelector('.sp-cd-item-bundle:not(.sp-cd-item-gift-badge)');if(bd)bd.remove();var sv=it.querySelector('.sp-cd-item-save');if(sv&&!it.querySelector('.sp-cd-item-price-free'))sv.remove();
  if(!it.querySelector('.bw-badge')){var n=it.querySelector('.sp-cd-item-name');if(n){var b=document.createElement('span');b.className='bw-badge';b.textContent='−'+PCT+' %';n.parentNode.insertBefore(b,n);}}});
 var sc=document.getElementById('sp-cd-scroll');var bar=document.querySelector('.bw-dbar');
 if(reg>0&&sc){if(!bar){bar=document.createElement('div');bar.className='bw-dbar';sc.insertBefore(bar,sc.firstChild);}var c=cd();bar.innerHTML='<div><span class="bw-ey">'+NAME.toUpperCase()+'</span><b>−'+PCT+' % auf alles</b><span class="t">Endet in '+c[0]+' T '+c[1]+' Std</span></div><em class="bw-chrome">−'+fm(reg*(1-F))+'</em>';}else if(bar)bar.remove();
 var sv2=document.getElementById('sp-cd-subtotal-val');if(!C.live&&sv2&&sv2.dataset.bwSet!==sv2.textContent&&reg>0){sv2.textContent=fm(reg*F);sv2.dataset.bwSet=sv2.textContent;}}
function checkoutLive(){var bl=document.querySelectorAll('.wp-block-woocommerce-checkout-order-summary-block');if(!bl.length)return;var s=0;try{var d=wp.data.select('wc/store/cart').getCartData();(d.items||[]).forEach(function(i){var p=i.prices||{},m=Math.pow(10,p.currency_minor_unit||2);var r=(+p.regular_price)/m,c=(+p.price)/m;if(c>0&&r>c)s+=(r-c)*i.quantity;});}catch(e){return;}
 bl.forEach(function(b){var ft=b.querySelector('.wc-block-components-totals-footer-item');if(!ft)return;var w=ft.closest('.wc-block-components-totals-wrapper');var row=b.querySelector('.bw-co');if(!row&&w){row=document.createElement('div');row.className='bw-co';w.parentNode.insertBefore(row,w);}if(!row)return;if(s<=0){row.style.display='none';return;}row.style.display='';var h='<div><b>'+NAME+' −'+PCT+' %</b><small>Schon in den Preisen abgezogen. Bei Vorkasse zählt das Bestelldatum.</small></div><em class="bw-chrome">−'+fm(s)+'</em>';if(row.innerHTML!==h)row.innerHTML=h;});}
function checkout(){if(C.live)return checkoutLive();var blocks=document.querySelectorAll('.wp-block-woocommerce-checkout-order-summary-block');blocks.forEach(function(bl){var reg=0;
  bl.querySelectorAll('.wc-block-components-order-summary-item').forEach(function(it){var tp=it.querySelector('.wc-block-components-order-summary-item__total-price');if(!tp)return;
   if(/Geschenk/.test(it.textContent)){tp.dataset.bwo=0;return;}
   if(tp.dataset.bwSet!==tp.innerHTML){var ru=it.querySelector('.wc-block-components-order-summary-item__regular-individual-price');var qn=it.querySelector('.wc-block-components-order-summary-item__quantity');var qq=qn&&qn.firstChild?parseInt(qn.firstChild.textContent)||1:1;var o=ru?num(ru.textContent)*qq:num(tp.textContent);if(!o){tp.dataset.bwo=0;return;}tp.dataset.bwo=o;tp.innerHTML='<div style="text-align:right;white-space:nowrap"><div style="font-weight:700">'+fm(o*F)+'</div><span class="bw-old">'+fm(o)+'</span></div>';tp.dataset.bwSet=tp.innerHTML;}
   reg+=+tp.dataset.bwo||0;var ip=it.querySelector('.wc-block-components-order-summary-item__individual-prices');if(ip&&+tp.dataset.bwo&&!ip.querySelector('.bw-badge'))ip.innerHTML='<span class="bw-badge" style="margin:0">−'+PCT+' %</span>';});
  var ft=bl.querySelector('.wc-block-components-totals-footer-item');if(!ft||!reg)return;var tv=ft.querySelector('.wc-block-components-totals-item__value');
  if(tv&&tv.dataset.bwSet!==tv.textContent){var t0=num(tv.textContent);tv.textContent=fm(t0-reg*(1-F));tv.dataset.bwSet=tv.textContent;}
  var w=ft.closest('.wc-block-components-totals-wrapper');var row=bl.querySelector('.bw-co');if(!row&&w){row=document.createElement('div');row.className='bw-co';w.parentNode.insertBefore(row,w);}
  if(row)row.innerHTML='<div><b>'+NAME+' −'+PCT+' %</b><small>Vorschau: nur Anzeige, bestellt wird zum Normalpreis.</small></div><em class="bw-chrome">−'+fm(reg*(1-F))+'</em>';});}
var nlpDone=0;function nlp(){if(nlpDone)return;var pp=document.getElementById('sp-nlp');if(!pp)return;nlpDone=1;
 var h=pp.querySelectorAll('.st[data-s="1"] h2,.st[data-s="2"] h2'),p1=pp.querySelector('.st[data-s="1"] p'),b1=pp.querySelector('.st[data-s="1"] [data-yes]');
 if(!BF&&!C.insider){h.forEach(function(e){e.innerHTML='Freitag wird <em>noch größer</em>';});if(p1)p1.textContent='Newsletter-Insider bekommen den Black-Friday-Preis einen Abend früher – Donnerstag ab 18 Uhr. Plus dein 10-%-Code für die Zeit danach.';if(b1)b1.textContent='Ja, Insider werden';}
 else{h.forEach(function(e){e.innerHTML='Sichere dir <em>10 % Rabatt</em><br>für deine nächste Bestellung';});if(p1)p1.textContent='Dein Code gilt nach der Black Week noch 30 Tage – dazu Neuheiten und neue Chargen zuerst.';}
 }
function nlh(){var nl=document.getElementById('sp-nlh');if(!nl||nl.dataset.bw||!nl.querySelector('h2'))return;nl.dataset.bw=1;if(!BF&&!C.insider){var ey=nl.querySelector('.ey'),h2=nl.querySelector('h2'),ld=nl.querySelector('.lead');if(ey)ey.innerHTML='<i></i>Black-Friday-Insider';if(h2)h2.innerHTML='Freitag <span>früher</span>.';if(ld)ld.textContent='Insider bekommen den Black-Friday-Preis schon Donnerstag ab 18 Uhr. Plus dein 10-%-Code für danach.';}}
function run(){try{nlp();nlh();banner();if(C.home)heroD();hero();cards();buybox();drawer();checkout();}catch(e){}}
run();document.addEventListener('DOMContentLoaded',run);setInterval(run,800);
})();
</script>
    <?php
}, 250);
