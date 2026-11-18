<?php
/**
 * Plugin Name: SP Black Week – Vorschau
 * Description: (2026-11-17) Nur-Ansicht-Vorschau des Black-Week-/Black-Friday-Looks (Spotlight-Stil) für den Betreiber.
 *   Aktiv nur mit Vorschau-Cookie: /?sp_bw=vorschau&k=<token> (Black Week) bzw. &p=bf (Black Friday); /?sp_bw=aus beendet.
 *   Seit 2026-11-18 auch die LIVE-Anzeige der Aktion (Code BLACK30/BLACK35): Leiste, Hero, Karten, Kaufbox, Pop-up, Warenkorb-/Kassen-Hinweis.
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
        'code'  => function_exists('sp_bw_current_code') ? sp_bw_current_code() : ($bf ? 'BLACK35' : 'BLACK30'),
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
/* Code-Variante (2026-11-18) */
.bw-tb .bw-c{border:1px dashed rgba(255,255,255,.5);border-radius:6px;padding:4px 7px;letter-spacing:.12em;color:#fff;font-weight:800}
.bw-tb .bw-k{background:#fff!important;color:#000!important;border:0!important;border-radius:999px!important;padding:4px 9px!important;font:800 10px/1 Inter,sans-serif!important;letter-spacing:.04em!important;text-transform:none!important;box-shadow:none!important;cursor:pointer;min-height:0!important}
.bw-tk{display:inline-flex;align-items:center;gap:8px;border:1.5px dashed rgba(255,255,255,.55);border-radius:12px;padding:6px 6px 6px 12px;background:rgba(0,0,0,.35)}
.bw-tk b{font:800 18px/1 Sora,sans-serif;letter-spacing:.08em;color:#fff}
.bw-tk .bw-k{background:#fff!important;color:#000!important;border:0!important;border-radius:999px!important;padding:7px 11px!important;font:800 11.5px/1 Inter,sans-serif!important;text-transform:none!important;letter-spacing:0!important;box-shadow:none!important;cursor:pointer;min-height:0!important}
.bwh .l2{margin-top:12px}
.sp-pc .qd.bw-qd{letter-spacing:.02em!important;text-transform:none!important;font:600 11px/1.3 Inter,sans-serif!important;margin-top:4px}
.sp-pc .qd.bw-qd b{border:1px dashed #9AA0A8;border-radius:5px;padding:1px 5px;letter-spacing:.06em;color:#07080A}
.bw-strip .s .bw-tk{margin-left:6px;vertical-align:middle}
.bw-bb .bw-bbc{display:flex}.bw-bb .r b.sv{display:block;font:600 13.5px/1.25 Inter,sans-serif;color:#fff}
.bw-cbox{margin:0 0 12px}
.bw-cb{border-radius:14px;padding:12px 13px;display:flex;align-items:center;gap:11px}
.bw-cb.ok{background:#0D0F12;color:#fff}.bw-cb .ck{width:28px;height:28px;flex:0 0 28px;border-radius:50%;background:#3DDC84;color:#000;display:flex;align-items:center;justify-content:center;font:900 14px/1 Inter,sans-serif}
.bw-cb .tx{flex:1;font:700 13.5px/1.35 Inter,sans-serif}.bw-cb .tx small{display:block;font:500 12px/1.35 Inter,sans-serif;color:#9AA0A8}
.bw-cb.miss{background:#FFF6E0;border:1px solid #F2C94C;color:#3A2E00}.bw-cb.miss .tx small{color:#7A6418}
.bw-cb .ap,.bw-co .ap{background:#0D0F12!important;color:#fff!important;border:0!important;font:800 12.5px/1 Inter,sans-serif!important;padding:10px 13px!important;border-radius:999px!important;white-space:nowrap;cursor:pointer;box-shadow:none!important;text-transform:none!important;letter-spacing:0!important;min-height:0!important}
.bw-co{background:#FFF6E0!important;color:#3A2E00!important;border:1px solid #F2C94C}.bw-co small{color:#7A6418!important}
#bw-toast{position:fixed;left:50%;bottom:calc(90px + env(safe-area-inset-bottom,0px));transform:translate(-50%,20px);opacity:0;z-index:2147483000;background:#fff;color:#000;font:700 13px/1 Inter,sans-serif;padding:12px 16px;border-radius:999px;box-shadow:0 10px 30px rgba(0,0,0,.35);transition:.25s;pointer-events:none;white-space:nowrap}#bw-toast.on{opacity:1;transform:translate(-50%,0)}
#sp-nlp.bw-on .st[data-s="1"]>h2,#sp-nlp.bw-on .st[data-s="1"]>p,#sp-nlp.bw-on .st[data-s="1"]>.no{display:none!important}
#sp-nlp .bwp{text-align:center;color:#fff}
#sp-nlp .bwp .h{font:800 40px/1 Sora,sans-serif;letter-spacing:-.045em;margin-top:4px}
#sp-nlp .bwp .sb{font:500 14px/1.45 Inter,sans-serif;color:#C9CDD2;margin-top:10px}
#sp-nlp .bwp .tk2{margin-top:18px;display:flex;align-items:stretch;border-radius:16px;overflow:hidden;background:#fff;color:#000;text-align:left}
#sp-nlp .bwp .tk2 .l{flex:1;padding:12px 16px;border-right:2px dashed #C9CDD2}#sp-nlp .bwp .tk2 small{display:block;font:700 9.5px/1 Inter,sans-serif;letter-spacing:.24em;color:#6B7076}
#sp-nlp .bwp .tk2 b{display:block;font:800 28px/1.15 Sora,sans-serif;letter-spacing:.06em;margin-top:4px;color:#000}
#sp-nlp .bwp .tk2 .r{width:104px;display:flex!important;flex-direction:column;align-items:center;justify-content:center;gap:4px;font:800 13px/1 Inter,sans-serif!important;background:#F1F2F4!important;color:#000!important;border:0!important;border-radius:0!important;padding:0!important;box-shadow:none!important;text-transform:none!important;letter-spacing:0!important;cursor:pointer}
#sp-nlp .bwp .tk2 .r svg{width:20px;height:20px;stroke:#000;fill:none;stroke-width:2}
#sp-nlp .bwp .go{display:block;margin-top:12px;background:#fff;color:#000!important;border-radius:999px;font:800 15.5px/1 Inter,sans-serif;padding:17px;text-decoration:none!important}
#sp-nlp .bwp .nt{font:500 12px/1.5 Inter,sans-serif;color:#8D939A;margin-top:12px}
#sp-nlp .bwp .cnt{display:flex;justify-content:center;gap:8px;margin-top:6px}#sp-nlp .bwp .cnt div{width:56px;padding:8px 0;border-radius:12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);font:800 19px/1 Sora,sans-serif;color:#fff}#sp-nlp .bwp .cnt small{display:block;font:600 8.5px/1 Inter,sans-serif;letter-spacing:.18em;color:#9AA0A8;margin-top:5px}
#sp-nlp .bwp .cnt+.nt{margin-top:6px}
#sp-nlp.bw-on [data-yes].bw-alt{background:transparent!important;color:#C9CDD2!important;border:1px solid rgba(255,255,255,.25)!important;box-shadow:none!important;margin-top:12px;font-size:13.5px!important}
#sp-bwv-pill{position:fixed;left:50%;transform:translateX(-50%);bottom:calc(14px + env(safe-area-inset-bottom,0px));z-index:99999;background:#fff;color:#000;font:700 11px/1 Inter,sans-serif;letter-spacing:.06em;padding:9px 13px;border-radius:999px;box-shadow:0 6px 20px rgba(0,0,0,.35);text-decoration:none!important;white-space:nowrap}
</style>
<script id="sp-bwv-js">
(function(){
var C=<?php echo wp_json_encode($cfg); ?>,BF=C.bf,PCT=C.pct,F=1-PCT/100,NAME=BF?'Black Friday':'Black Week',END=new Date(C.end),CODE=C.code;
function fm(n){return n.toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g,'.')+' €';}
function num(s){return parseFloat(String(s).replace(/[^\d,]/g,'').replace(',','.'))||0;}
function cd(){var s=Math.max(0,(END-new Date())/1000);return [Math.floor(s/86400),Math.floor(s%86400/3600),Math.floor(s%3600/60),Math.floor(s%60)].map(function(x){return (x<10?'0':'')+x;});}
if(C.pv){var pill=document.createElement('a');pill.id='sp-bwv-pill';pill.href=C.off;pill.textContent='Vorschau beenden ✕';document.body.appendChild(pill);}
/* --- Code: kopieren, merken, einlösen --- */
function toast(m){var t=document.getElementById('bw-toast');if(!t){t=document.createElement('div');t.id='bw-toast';document.body.appendChild(t);}t.textContent=m;t.classList.add('on');clearTimeout(t._t);t._t=setTimeout(function(){t.classList.remove('on');},2200);}
function copyCode(){function ok(){toast('✓ '+CODE+' kopiert – im Warenkorb einfügen');}function fb(){var a=document.createElement('textarea');a.value=CODE;a.style.cssText='position:fixed;opacity:0;top:0';document.body.appendChild(a);a.select();try{document.execCommand('copy');ok();}catch(e){}a.remove();}
 if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(CODE).then(ok,fb);}else fb();}
function setCk(){document.cookie='sp_bw_code='+CODE+';path=/;max-age=1209600;SameSite=Lax;Secure';}
var CART=null,NONCE='';
(function(){var of=window.fetch;if(!of||of._bw)return;var nf=function(u){var p=of.apply(this,arguments);return p.then(function(r){try{var s=typeof u==='string'?u:(u&&u.url)||'';if(/\/wc\/store\/v1\/cart/.test(s)&&!/\/batch/.test(s)){var n=r.headers.get('Nonce');if(n)NONCE=n;r.clone().json().then(function(d){if(d&&d.items&&d.totals)CART=d;}).catch(function(){});}}catch(e){}return r;});};nf._bw=1;window.fetch=nf;})();
function isCode(c){return /^black3[05]$/i.test(c||'');}
function codeIn(cart){var r='';((cart&&cart.coupons)||[]).forEach(function(c){if(isCode(c.code))r=String(c.code).toUpperCase();});return r;}
function applyCode(){setCk();function go(){return fetch('/wp-json/wc/store/v1/cart/apply-coupon',{method:'POST',credentials:'include',headers:{'Content-Type':'application/json','Nonce':NONCE},body:JSON.stringify({code:CODE})}).then(function(r){return r.json();}).then(function(){toast('✓ '+CODE+' ist aktiv');if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[null,null,null]);}).catch(function(){});}
 if(NONCE)go();else fetch('/wp-json/wc/store/v1/cart',{credentials:'include'}).then(go);}
document.addEventListener('click',function(e){var k=e.target.closest&&e.target.closest('.bw-k');if(k){e.preventDefault();e.stopPropagation();copyCode();}
 var g=e.target.closest&&e.target.closest('.bw-go');if(g){copyCode();if(!C.home&&g.hasAttribute('data-stay')){e.preventDefault();var cl=document.querySelector('#sp-nlp [data-close]');if(cl)cl.click();}else{e.preventDefault();var h=g.getAttribute('href');setTimeout(function(){location.href=h;},700);}}},true);
function openCouponField(){var t=document.getElementById('sp-cd-coupon-toggle');if(t&&t.offsetParent!==null)t.click();setTimeout(function(){var i=document.getElementById('sp-cd-coupon-input');if(i){i.focus();i.scrollIntoView({block:'center'});}},250);}
function tk(){return '<span class="bw-tk"><b>'+CODE+'</b><button type="button" class="bw-k">Kopieren</button></span>';}
/* --- Leiste oben --- */
function banner(){var tb=document.getElementById('sp-top-banner');if(!tb)return;tb.classList.add('bw');var d=tb.querySelector('.bw-tb');if(!d){d=document.createElement('div');d.className='bw-tb';tb.appendChild(d);}
 var h='<b>'+NAME+'</b><span>·</span>−'+PCT+' %<span>·</span><span class="bw-c">'+CODE+'</span><button type="button" class="bw-k">Kopieren</button>';if(d.dataset.h!==h){d.innerHTML=h;d.dataset.h=h;}}
/* --- Startseite --- */
function heroHtml(){return '<div class="t"><div class="ey2">'+NAME.toUpperCase()+'</div><div class="big"><span class="bw-chrome">−'+PCT+'</span><span class="bw-chrome bw-pc">%</span></div><div class="l1">auf alles – mit Code</div><div class="l2">'+tk()+'</div></div><div class="b"><div class="cdn"></div><a class="cta bw-go" href="/alle-produkte/">Code kopieren &amp; shoppen →</a><div class="lnk">'+(BF?'Nur bis Cyber Monday, 30.11. · 23:59':'Code gilt nur bis Donnerstag, 26.11. · 23:59')+'</div></div>';}
function cdnHtml(){var c=cd();return '<span><b>'+c[0]+'</b>T</span><span><b>'+c[1]+'</b>STD</span><span><b>'+c[2]+'</b>MIN</span><span><b>'+c[3]+'</b>SEK</span>';}
function heroD(){var hb=document.getElementById('sp-hero-banner');if(!hb||innerWidth<768)return;var x=document.querySelector('.bwh.bwd');if(!x){x=document.createElement('div');x.className='bwh bwd';x.style.backgroundImage='url('+C.scene+')';hb.parentNode.insertBefore(x,hb);hb.style.display='none';x.innerHTML=heroHtml();}x.querySelector('.cdn').innerHTML=cdnHtml();}
function hero(){if(!C.home)return;var h=document.querySelector('.sp-hero2');if(!h||h.offsetParent===null)return;var w=h.querySelector('.sp-hero2-img-wrap');if(!w)return;h.classList.add('bw');var x=w.querySelector('.bwh');
 if(!x){x=document.createElement('div');x.className='bwh';x.style.backgroundImage='url('+C.scene+')';x.innerHTML=heroHtml();w.insertBefore(x,w.firstChild);
  var top=h.getBoundingClientRect().top+scrollY;var tb=document.getElementById('sp-top-banner');var tbh=tb?tb.offsetHeight:32;x.style.marginTop=(tbh-top)+'px';}
 x.querySelector('.cdn').innerHTML=cdnHtml();}
/* --- Produktkarten + Kategorie --- */
function cards(){document.querySelectorAll('.sp-pc[data-id]').forEach(function(cd){var pr=cd.querySelector('.pr');if(!pr)return;var on=cd.querySelector('.vo button.on')||cd.querySelector('.vo button');
  var p=on?(parseFloat(on.getAttribute('data-r'))||parseFloat(on.getAttribute('data-p'))||0):0;if(!p){if(pr.dataset.bwo)p=+pr.dataset.bwo;else p=num((pr.querySelector('s')||pr.querySelector('strong')||pr).textContent);}if(!p)return;
  if(!(pr.dataset.bwo==String(p)&&pr.querySelector('s'))){pr.dataset.bwo=p;pr.classList.add('bw-pr');pr.innerHTML='<strong>'+fm(p*F)+'</strong><s>'+fm(p)+'</s>';}
  var bd=cd.querySelector('a.im .bd');if(!bd){bd=document.createElement('span');var im=cd.querySelector('a.im');if(im)im.appendChild(bd);}if(bd&&!/Ausverkauft/.test(bd.textContent)){bd.className='bd bw-bd';bd.textContent='−'+PCT+' % mit Code';}
  var q=cd.querySelector('.qd');if(!q){q=document.createElement('div');q.className='qd';pr.parentNode.insertBefore(q,pr.nextSibling);}q.classList.add('bw-qd');var qh='mit Code <b>'+CODE+'</b>';if(q.innerHTML!==qh)q.innerHTML=qh;});
 var cat=document.querySelector('#sp-cat .body');if(cat&&!document.querySelector('.bw-strip')){var s=document.createElement('div');s.className='bw-strip';cat.insertBefore(s,cat.firstChild);}
 var st=document.querySelector('.bw-strip');if(st){var c=cd();if(!st.querySelector('.bw-tk'))st.innerHTML='<div class="bw-ey">'+NAME.toUpperCase()+'</div><div class="bg bw-chrome">−'+PCT+' % auf alles</div><div class="s">Mit Code im Warenkorb: '+tk()+'</div><div class="c"></div>';st.querySelector('.c').innerHTML=(BF?'Endet in ':'Code gilt noch ')+'<b>'+c[0]+'T · '+c[1]+':'+c[2]+':'+c[3]+'</b>';}}
/* --- Kaufbox --- */
function buybox(){var rp=document.getElementById('rx-price');if(!rp)return;
 var po=document.querySelector('#rx-buybox .rx-price-old');if(po){po.style.display='none';var dc=document.querySelector('#rx-buybox .rx-discount-chip');if(dc)dc.style.display='none';}
 if(rp.dataset.bwSet!==rp.innerHTML){var p0=po?num(po.textContent):num(rp.textContent);if(!p0)return;rp.dataset.bwo=p0;rp.innerHTML=fm(p0*F)+' <s style="font-size:.5em;color:#8A9098;font-weight:500">'+fm(p0)+'</s> <span class="bw-tag">−'+PCT+' %</span>';rp.dataset.bwSet=rp.innerHTML;}
 var p=+rp.dataset.bwo;
 var tt=document.getElementById('rx-total');if(tt&&tt.dataset.bwSet!==tt.innerHTML){var q=+((document.getElementById('rx-qtyfield')||{}).value||1);tt.innerHTML=fm(p*F*q);tt.dataset.bwSet=tt.innerHTML;}
 document.querySelectorAll('#rx-vol-tiers .rx-tier__disc').forEach(function(e){if(e.dataset.bwSet===e.innerHTML)return;e.innerHTML=fm(num(e.textContent)*F);e.dataset.bwSet=e.innerHTML;});
 document.querySelectorAll('#rx-tiers .rx-tier').forEach(function(t){var q=+t.getAttribute('data-q');var e=t.querySelector('.rx-tier__disc');if(!e)return;var v=q===1?'Einzeln':fm(p*F*q);if(e.textContent!==v)e.textContent=v;});
 var bb=document.querySelector('#rx-buybox .bw-bb');if(!bb){bb=document.createElement('div');bb.className='bw-bb';var prr=document.querySelector('#rx-buybox .rx-price-row');if(prr)prr.parentNode.insertBefore(bb,prr.nextSibling);}
 if(!bb.querySelector('.bw-tk'))bb.innerHTML='<div class="bw-ey">'+NAME.toUpperCase()+'</div><div class="h"><div class="bg bw-chrome">−'+PCT+' %</div><div class="r"><b class="sv"></b><span>mit Code im Warenkorb</span></div></div><div class="bw-bbc">'+tk()+'</div><div class="ln"></div><div class="c"><span>'+(BF?'Endet in':'Code gilt noch')+'</span><b class="cc"></b></div>';
 var c=cd();bb.querySelector('.sv').textContent='Du sparst '+fm(p*(1-F));bb.querySelector('.cc').textContent=c[0]+'T · '+c[1]+' : '+c[2]+' : '+c[3];
 var sh=document.querySelector('#rx-shiphint .rx-amt-shine');if(sh&&sh.dataset.bwSet!==sh.textContent){var v=Math.max(0,150-p*F);sh.textContent=fm(v);sh.dataset.bwSet=sh.textContent;}}
/* --- Warenkorb: Code aktiv / vergessen --- */
function drawer(){var dr=document.getElementById('sp-cart-drawer');if(!dr)return;var it=document.getElementById('sp-cd-items');if(!it)return;var box=dr.querySelector('.bw-cbox');
 if(!CART){if(document.body.classList.contains('sp-cd-on')&&!drawer._f){drawer._f=1;fetch('/wp-json/wc/store/v1/cart',{credentials:'include'});}return;}
 var m=Math.pow(10,(CART.totals||{}).currency_minor_unit||2),r=0,n=0;(CART.items||[]).forEach(function(i){if(+i.prices.price>0){r+=(+i.prices.regular_price)/m*i.quantity;n++;}});
 if(!n){if(box)box.remove();return;}
 var sub=document.getElementById('sp-cd-subtotal-row');if(box&&sub&&box.nextElementSibling!==sub){box.remove();box=null;}if(!box){box=document.createElement('div');box.className='bw-cbox';if(sub)sub.parentNode.insertBefore(box,sub);else it.parentNode.insertBefore(box,it.nextSibling);}
 var h,code=codeIn(CART);
 if(code){var d=(+CART.totals.total_discount+(+CART.totals.total_discount_tax||0))/m;h='<div class="bw-cb ok"><span class="ck">✓</span><div class="tx">'+code+' ist aktiv<small>−'+PCT+' % auf alles'+(d>0?' · du sparst '+fm(d):'')+'</small></div></div>';}
 else{var cur=(+CART.totals.total_items+(+CART.totals.total_items_tax||0)-(+CART.totals.total_discount||0)-(+CART.totals.total_discount_tax||0))/m;h='<div class="bw-cb miss"><div class="tx">Mit Code '+CODE+': '+fm(r*F)+'<small>statt '+fm(cur)+' – Code kopieren und oben bei „Rabattcode“ einfügen</small></div><button type="button" class="ap">Kopieren</button></div>';}
 if(box.dataset.h!==h){box.innerHTML=h;box.dataset.h=h;var b=box.querySelector('.ap');if(b)b.onclick=function(){copyCode();openCouponField();};}}
/* --- Kasse: Hinweis, falls kein Code --- */
function checkout(){var bl=document.querySelectorAll('.wp-block-woocommerce-checkout-order-summary-block');if(!bl.length||!window.wp||!wp.data)return;var d;try{d=wp.data.select('wc/store/cart').getCartData();}catch(e){return;}if(!d||!d.items)return;
 var has=!!codeIn(d),m=Math.pow(10,(d.totals||{}).currency_minor_unit||2),r=0;(d.items||[]).forEach(function(i){if(+i.prices.price>0)r+=(+i.prices.regular_price)/m*i.quantity;});
 bl.forEach(function(b){var ft=b.querySelector('.wc-block-components-totals-footer-item');if(!ft)return;var w=ft.closest('.wc-block-components-totals-wrapper');var row=b.querySelector('.bw-co');if(!row&&w){row=document.createElement('div');row.className='bw-co';w.parentNode.insertBefore(row,w);}if(!row)return;
  if(has||r<=0){row.style.display='none';return;}row.style.display='';
  var h='<div><b>Mit Code '+CODE+' nur '+fm(r*F)+'</b><small>für die Artikel statt '+fm(r)+' – Code kopieren und bei „Gutschein hinzufügen“ einfügen.</small></div><button type="button" class="ap">Kopieren</button>';
  if(row.dataset.h!==h){row.innerHTML=h;row.dataset.h=h;row.querySelector('.ap').onclick=function(){copyCode();var t=document.querySelector('.wc-block-components-totals-coupon-link,.wc-block-components-totals-coupon .wc-block-components-panel__button');if(t&&t.getAttribute('aria-expanded')!=='true')t.click();setTimeout(function(){var i=document.querySelector('#wc-block-components-totals-coupon__input-coupon,.wc-block-components-totals-coupon__input input');if(i)i.focus();},300);};}});}
/* --- Pop-up: zeigt während der Aktion den Code (Newsletter bleibt als 2. Option) --- */
var nlpDone=0;function nlp(){var pp=document.getElementById('sp-nlp');if(!pp)return;var s1=pp.querySelector('.st[data-s="1"]');if(!s1)return;
 if(!nlpDone){nlpDone=1;pp.classList.add('bw-on');var hd=pp.querySelector('.hd span');if(hd)hd.textContent=NAME.toUpperCase();
  var y=s1.querySelector('[data-yes]');if(y){y.textContent='Lieber 10 % für später per Newsletter';y.classList.add('bw-alt');}
  var bx=document.createElement('div');bx.className='bwp';bx.innerHTML='<div class="h bw-chrome">−'+PCT+' % auf alles.</div><div class="sb">Peptide, Pens und Zubehör.<br>Code kopieren und im Warenkorb bei „Rabattcode“ einfügen.</div><div class="tk2"><div class="l"><small>DEIN CODE</small><b>'+CODE+'</b></div><button type="button" class="r bw-k"><svg viewBox="0 0 24 24"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>Kopieren</button></div><a class="go bw-go" data-stay href="/alle-produkte/">Code kopieren &amp; shoppen →</a><div class="nt">'+(BF?'Nur noch':CODE+' gilt nur noch')+'</div><div class="cnt"></div><div class="nt">'+(BF?'bis Cyber Monday, 30.11. · 23:59 Uhr':'bis Donnerstag, 26.11. · 23:59 Uhr')+'</div>';
  s1.insertBefore(bx,s1.firstChild);
  pp.querySelectorAll('.st[data-s="2"] h2').forEach(function(e){e.innerHTML='Sichere dir <em>10 % Rabatt</em><br>für deine nächste Bestellung';});
  var p2=pp.querySelector('.st[data-s="2"] p');if(p2)p2.textContent='Dein Code gilt nach der Black Week noch 30 Tage. Wohin dürfen wir ihn schicken?';}
 var cn=s1.querySelector('.bwp .cnt');if(cn){var c=cd();var h='<div>'+c[0]+'<small>TAGE</small></div><div>'+c[1]+'<small>STD</small></div><div>'+c[2]+'<small>MIN</small></div>';if(cn.innerHTML!==h)cn.innerHTML=h;}}
function run(){try{nlp();banner();if(C.home)heroD();hero();cards();buybox();drawer();checkout();}catch(e){}}
run();document.addEventListener('DOMContentLoaded',run);setInterval(run,800);
})();
</script>
    <?php
}, 250);
