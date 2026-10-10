<?php
/**
 * Plugin Name: SP Startseiten-Vorschau
 * Description: Entwurf der neuen Startseite NUR ueber einen geheimen Vorschau-Link (2026-10-10).
 *
 * https://peptrium.com/?sp_vorschau=TOKEN zeigt die Startseite mit dem Entwurf
 * (neue Reihenfolge, Hell/Dunkel-Wechsel, Bestseller-Reihe, Kundenstimmen, ...).
 * Alle anderen Besucher sehen die normale Startseite. Reines CSS/JS-Overlay,
 * Elementor-Daten bleiben unangetastet. Datei loeschen = Vorschau weg.
 * Entwurfsdateien im Repo: drafts/startseite-2026-10-10/.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_HPV_TOKEN', 'v02dqoyjtv');

function sp_hpv_active() {
    return isset($_GET['sp_vorschau']) && hash_equals(SP_HPV_TOKEN, (string) $_GET['sp_vorschau']) && is_front_page();
}

/** Bestseller fuer die Produkt-Reihe (Peptide + Pens, nach Verkaufszahl). */
function sp_hpv_products() {
    $ids = [65, 68, 393, 428, 431, 71, 77, 434];
    $map = function_exists('sp_abo_picker_rating_map') ? sp_abo_picker_rating_map() : [];
    $out = [];
    foreach ($ids as $id) {
        $p = wc_get_product($id);
        if (!$p || $p->get_status() !== 'publish') {
            continue;
        }
        $var = $p->is_type('variable');
        $min = $var ? (float) $p->get_variation_price('min', true) : (float) wc_get_price_to_display($p);
        $reg = $p->is_on_sale() ? (float) ($var ? $p->get_variation_regular_price('min', true) : $p->get_regular_price()) : null;
        $out[] = [
            'n' => $p->get_name(), 'u' => get_permalink($id), 'i' => wp_get_attachment_image_url($p->get_image_id(), 'medium_large'),
            'p' => $min, 'r' => $reg, 'v' => $var,
            'rt' => isset($map[$id]['num']) ? $map[$id]['num'] : 4.8, 'rc' => isset($map[$id]['count']) ? $map[$id]['count'] : 0,
            'pen' => stripos($p->get_name(), 'Pen') !== false,
        ];
    }
    return $out;
}

add_action('wp_head', function () {
    if (!sp_hpv_active()) {
        return;
    }
    echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    ?>
<style id="sp-hpv-css">
/* ===== Startseite komplett (Entwurf) ===== */
body.home .elementor-211{display:flex;flex-direction:column}
body.home .elementor-211>*{order:50;width:100%}
body.home .elementor-211>[data-id="shbn001"]{order:1}
body.home .elementor-211>#sp-hp-prod{order:2}
body.home .elementor-211>[data-id="mfst001"]{order:3}
body.home .elementor-211>#sp-hp-proof{order:4}
body.home .elementor-211>[data-id="penTeaser1"]{order:5}
body.home .elementor-211>[data-id="e761cf4"]{order:6}
body.home .elementor-211>[data-id="hiwk001"]{order:7}
body.home .elementor-211>[data-id="77e913a"]{order:8}
body.home .elementor-211>#sp-nlh{order:9}
body.home .elementor-211>#sp-hp-info{order:10}
body.home .elementor-211>[data-id="045a884"],body.home .elementor-211>[data-id="a4ab146"]{order:11}
body.home .elementor-211>[data-id="a9d4d1b"],body.home .elementor-211>[data-id="d60a05b"],body.home .elementor-211>[data-id="spsocial1"],body.home .elementor-211>[data-id="midcta01"],body.home .elementor-211>[data-id="3ca703b"],body.home .elementor-211>[data-id="5c363d8"]{display:none!important}
body.home .elementor-211>.sp-hp-closed{display:none!important}

/* ---- Einheitliche Abschnitts-Labels ---- */
body.home .sp-lbl,body.home .sp-mf-badge,body.home .sp-pt-eyebrow,body.home .sp-hiw-badge,body.home .sp-tools-badge,body.home .sp-abo-promo-eyebrow,body.home #sp-nlh .ey{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:6px 14px!important;border-radius:999px!important;font:700 10.5px/1.2 Sora,sans-serif!important;letter-spacing:.16em!important;text-transform:uppercase!important;border:1px solid rgba(255,255,255,.18)!important;background:rgba(255,255,255,.05)!important;color:#E6E9EC!important;margin:0 0 16px!important;box-shadow:none!important}
body.home .sp-lbl:before,body.home .sp-mf-badge:before,body.home .sp-pt-eyebrow:before,body.home .sp-hiw-badge:before,body.home .sp-tools-badge:before,body.home .sp-abo-promo-eyebrow:before{content:'';width:6px;height:6px;border-radius:50%;background:#C7CCD1;box-shadow:0 0 8px rgba(199,204,209,.7);flex:0 0 6px}
body.home #sp-nlh .ey i{width:6px;height:6px}
body.home .sp-lbl.lt,body.home .sp-tools-badge{border-color:rgba(13,15,18,.14)!important;background:#fff!important;color:#4A5058!important}
body.home .sp-lbl.lt:before,body.home .sp-tools-badge:before{background:#0D0F12;box-shadow:none}
body.home .sp-abo-promo-eyebrow{color:#FF8A5C!important;border-color:rgba(255,138,92,.35)!important;background:rgba(255,138,92,.08)!important}
body.home .sp-abo-promo-eyebrow:before{background:#FF8A5C;box-shadow:0 0 8px rgba(255,138,92,.8)}

/* ---- Gemeinsam ---- */
.sp-hp-sec{font-family:Sora,sans-serif;padding:56px 20px;position:relative}
.sp-hp-sec .in{max-width:1140px;margin:0 auto}
.sp-hp-sec .hd{text-align:center;margin:0 0 26px}
.sp-hp-sec h2{font:700 clamp(26px,3.4vw,38px)/1.15 Sora,sans-serif;letter-spacing:-.02em;margin:0 0 10px;color:#0D0F12}
.sp-hp-sec .sub{font-size:15px;line-height:1.6;color:#5A6068;margin:0 auto;max-width:560px}
.sp-hp-light{background:linear-gradient(180deg,#FFFFFF,#EEF0F2)}
.sp-rail{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.sp-hp-more-a{display:flex;justify-content:center;margin-top:26px}
.sp-hp-more-a a{display:inline-flex;align-items:center;gap:8px;height:48px;padding:0 26px;border-radius:999px;background:#0D0F12;color:#fff!important;font:700 14.5px Sora,sans-serif;text-decoration:none!important}
@media(max-width:900px){
 .sp-hp-sec{padding:44px 16px}
 .sp-rail{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;scroll-padding:0 16px;gap:12px;margin:0 -16px;padding:4px 16px 14px;scrollbar-width:none}
 .sp-rail::-webkit-scrollbar{display:none}
 .sp-rail>*{scroll-snap-align:start}
}

/* ---- Produkte (Bestseller-Reihe) ---- */
.sp-pc{display:flex;flex-direction:column;background:#fff;border:1px solid #E3E6E9;border-radius:18px;overflow:hidden;text-decoration:none!important;color:#0D0F12;box-shadow:0 8px 24px rgba(13,15,18,.06);transition:transform .2s,box-shadow .2s}
.sp-pc:hover{transform:translateY(-3px);box-shadow:0 16px 34px rgba(13,15,18,.12)}
.sp-pc .im{position:relative;aspect-ratio:4/4.3;background:radial-gradient(circle at 50% 35%,#2A2F35,#0D0F12 70%);display:flex;align-items:center;justify-content:center;overflow:hidden}
.sp-pc .im img{width:100%;height:100%;object-fit:cover}
.sp-pc.pen .im img{object-fit:contain;padding:10px}
.sp-pc .bd{position:absolute;top:10px;left:10px;font:700 10.5px Sora,sans-serif;padding:5px 9px;border-radius:999px;background:#fff;color:#0D0F12}
.sp-pc .bd.s{background:#E8452C;color:#fff}
.sp-pc .bd.n{background:linear-gradient(120deg,#C7CCD1,#FFFFFF 50%,#C7CCD1)}
.sp-pc .tx{padding:13px 14px 14px;display:flex;flex-direction:column;gap:5px;flex:1}
.sp-pc .nm{font:700 15px/1.25 Sora,sans-serif;margin:0}
.sp-pc .rt{font-size:12px;color:#5A6068;display:flex;align-items:center;gap:5px}
.sp-pc .rt b{color:#F5A623;letter-spacing:1px;font-size:12px}
.sp-pc .pr{margin-top:6px;padding-top:0;display:flex;align-items:baseline;gap:7px;flex-wrap:wrap}
.sp-pc .pr strong{font:800 17px Sora,sans-serif}
.sp-pc .pr s{font-size:12.5px;color:#9AA0A8}
.sp-pc .pr small{font-size:11px;color:#9AA0A8;width:100%}
.sp-pc .bt{margin-top:10px;height:40px;border-radius:12px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;font:700 13px Sora,sans-serif}
.sp-pc.all{background:linear-gradient(160deg,#0D0F12,#23272C);color:#fff;justify-content:center;align-items:center;text-align:center;padding:24px;gap:10px}
.sp-pc.all .ar{width:52px;height:52px;border-radius:50%;background:linear-gradient(120deg,#C7CCD1,#fff 50%,#C7CCD1);color:#0D0F12;display:flex;align-items:center;justify-content:center;font-size:22px}
.sp-pc.all b{font-size:16px}.sp-pc.all span{font-size:12.5px;color:#9AA0A8}
.sp-trust{display:flex;justify-content:center;gap:10px 22px;flex-wrap:wrap;margin:22px 0 0;padding:0;list-style:none}
.sp-trust li{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:#3A4048}
.sp-trust li:before{content:'';width:16px;height:16px;border-radius:50%;background:#0D0F12 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center/9px no-repeat}
@media(max-width:900px){.sp-pc{flex:0 0 46%}.sp-pc.all{flex-basis:46%}.sp-pc .nm{font-size:14px}.sp-trust{gap:8px 14px}.sp-trust li{font-size:12px}}
@media(min-width:901px){.sp-pc.all{display:none}}

/* ---- Kundenstimmen ---- */
.sp-sum{display:flex;align-items:center;justify-content:center;gap:16px;margin:0 auto 26px;padding:14px 20px;max-width:440px;background:#fff;border:1px solid #E3E6E9;border-radius:18px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.sp-sum .big{font:800 40px/1 Sora,sans-serif;color:#0D0F12}
.sp-sum .st{color:#F5A623;font-size:17px;letter-spacing:2px}
.sp-sum .t{font-size:12.5px;color:#5A6068;line-height:1.45}
.sp-sum .t b{color:#0D0F12}
.sp-rv{display:flex;flex-direction:column;gap:12px;background:#fff;border:1px solid #E3E6E9;border-radius:18px;padding:18px;box-shadow:0 8px 24px rgba(13,15,18,.05)}
.sp-rv .st{color:#F5A623;font-size:14px;letter-spacing:2px}
.sp-rv p{margin:0;font-size:14px;line-height:1.6;color:#2A2F35;flex:1}
.sp-rv .au{display:flex;align-items:center;gap:10px;font-size:12.5px;color:#5A6068}
.sp-rv .au i{font-style:normal;width:34px;height:34px;border-radius:50%;background:#0D0F12;color:#fff;font-weight:700;font-size:12px;display:flex;align-items:center;justify-content:center}
.sp-rv .au b{display:block;color:#0D0F12;font-size:13px}
.sp-rv .au em{font-style:normal;display:inline-flex;align-items:center;gap:4px;color:#2E9B57;font-size:11.5px;font-weight:600}
#sp-hp-proof .sp-rail{grid-template-columns:repeat(3,minmax(0,1fr))}
#sp-hp-proof .sp-rail .sp-rv:nth-child(n+7){display:none}
@media(max-width:900px){.sp-rv{flex:0 0 82%}#sp-hp-proof .sp-rail .sp-rv:nth-child(n+7){display:flex}}
.sp-dots{display:none}
@media(max-width:900px){.sp-hint{display:block;text-align:center;font-size:11.5px;color:#9AA0A8;margin-top:2px}}
.sp-hint{display:none}

/* ---- Abschnitte neu einfaerben ---- */
body.home .elementor-211>[data-id="e761cf4"],body.home [data-id="e761cf4"] .sp-abo-promo,body.home [data-id="e761cf4"] .sp-promo-section{background:linear-gradient(180deg,#FFFFFF,#EEF0F2)!important}
body.home [data-id="e761cf4"] .sp-abo-promo-card{box-shadow:0 24px 50px rgba(13,15,18,.25)!important}
body.home #sp-how-it-works{background:linear-gradient(160deg,#0D0F12 0%,#1E2226 100%)!important}
body.home #sp-how-it-works h2,body.home #sp-how-it-works h3{color:#fff!important}
body.home #sp-how-it-works .sp-hiw-accent{background:linear-gradient(100deg,#9AA3AD,#F4F6F8 50%,#B8BFC6)!important;-webkit-background-clip:text!important;background-clip:text!important;color:transparent!important}
body.home #sp-how-it-works p{color:rgba(255,255,255,.65)!important}
body.home #sp-how-it-works .sp-hiw-cta{background:linear-gradient(120deg,#C7CCD1,#FFFFFF 45%,#C7CCD1)!important;color:#0D0F12!important}
body.home #sp-how-it-works .sp-hiw-cta *{color:#0D0F12!important;stroke:#0D0F12}
body.home .elementor-211>[data-id="77e913a"],body.home .sp-tools-section{background:linear-gradient(180deg,#FFFFFF,#EEF0F2)!important}
body.home .sp-tools-title{color:#0D0F12!important}
body.home .sp-tools-subtitle{color:rgba(13,15,18,.6)!important}
body.home .sp-tool-card{background:linear-gradient(160deg,#15181C,#23272C)!important;box-shadow:0 14px 30px rgba(13,15,18,.18)!important}
body.home .elementor-211>[data-id="045a884"],body.home .elementor-211>[data-id="a4ab146"]{background:linear-gradient(180deg,#F6F7F8,#EEF0F2)!important}
body.home [data-id="045a884"] .e-con,body.home [data-id="045a884"]>.e-con-inner{background:transparent!important}

/* ---- Handy: kompakter ---- */
@media(max-width:767px){
 body.home .sp-pt-features{grid-template-columns:1fr 1fr!important;gap:8px!important}
 body.home .sp-pt-feature{min-height:0!important;padding:10px!important;font-size:12.5px!important}
 body.home .sp-tools-grid{display:flex!important;overflow-x:auto;scroll-snap-type:x mandatory;scroll-padding:0 14px;gap:12px!important;margin:0 -14px;padding:4px 14px 12px;scrollbar-width:none}
 body.home .sp-tools-grid::-webkit-scrollbar{display:none}
 body.home .sp-tool-card{flex:0 0 78%;scroll-snap-align:start}
 body.home .sp-mf-grid{display:flex!important;overflow-x:auto;scroll-snap-type:x mandatory;scroll-padding:0 20px;gap:12px!important;margin:0 -20px;padding:4px 20px 12px;scrollbar-width:none}
 body.home .sp-mf-grid::-webkit-scrollbar{display:none}
 body.home .sp-mf-card{flex:0 0 84%;scroll-snap-align:start}
 body.home .sp-hiw-step-img,body.home .sp-hiw-track{display:none!important}
 body.home .sp-hiw-steps{gap:14px!important}
 body.home .sp-hiw-step{min-height:0!important;height:auto!important;padding:0!important}
}

/* ---- Hinweis + Mehr ueber Peptrium ---- */
#sp-hp-info{background:linear-gradient(180deg,#FFFFFF,#EEF0F2);padding:26px 20px 24px;text-align:center;font-family:Sora,sans-serif}
#sp-hp-info .dis{display:inline-flex;align-items:flex-start;gap:8px;max-width:640px;margin:0 auto;font-size:12.5px;line-height:1.55;color:#4A5058;text-align:left}
#sp-hp-info .dis b{color:#0D0F12}
#sp-hp-info .dis svg{flex:0 0 16px;margin-top:2px}
#sp-hp-info button{display:inline-flex;align-items:center;gap:6px;margin:16px auto 0;border:1px solid rgba(13,15,18,.15);background:#fff;border-radius:999px;padding:8px 16px;font:600 12.5px Sora,sans-serif;color:#4A5058;cursor:pointer}
#sp-hp-info button.open span{display:inline-block;transform:rotate(180deg)}
#sp-hpv-flag{position:fixed;left:12px;top:12px;z-index:99999;background:#FF8A5C;color:#0D0F12;font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.3);pointer-events:none}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    if (!sp_hpv_active()) {
        return;
    }
    ?>
<div id="sp-hpv-flag">ENTWURF-VORSCHAU</div>
<script id="sp-hpv-js">
window.SP_HP_PRODS=<?php echo wp_json_encode(sp_hpv_products()); ?>;
document.addEventListener('DOMContentLoaded',function(){
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
});
</script>
    <?php
}, 99);
