<?php
/**
 * Plugin Name: SP Produktseiten-Vorschau
 * Description: Entwurf der neuen Produktseite (zuerst Retatrutide) NUR ueber den geheimen Vorschau-Link (2026-10-10).
 *
 * https://peptrium.com/produkt/retatrutide/?sp_vorschau=TOKEN (gleicher Schluessel wie
 * sp-home-preview.php). Kaufbox bleibt technisch unveraendert; drumherum: Kundenstimmen
 * (dunkel, Laufband + Flasche beim Scrollen), "Passt dazu"-Sets, Lexikon dunkel, FAQ im
 * Startseiten-Stil, "Auch beliebt"-Produktkarten, Hinweis als Zeile, Sticky-Kaufleiste
 * auf dem Handy. Nutzt Daten-Helfer aus sp-home-preview.php (laedt alphabetisch vorher).
 * Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_ppv_active() {
    return defined('SP_HPV_TOKEN') && function_exists('sp_hpv_products') && isset($_GET['sp_vorschau'])
        && hash_equals(SP_HPV_TOKEN, (string) $_GET['sp_vorschau']) && function_exists('is_product') && is_product()
        && in_array((int) get_queried_object_id(), [65], true);
}

function sp_ppv_data() {
    $id = (int) get_queried_object_id();
    $p = wc_get_product($id);
    $map = function_exists('sp_abo_picker_rating_map') ? sp_abo_picker_rating_map() : [];
    $price = $p->is_type('variable') ? (float) $p->get_variation_price('min', true) : (float) wc_get_price_to_display($p);
    $family = array_merge([$id], $p->get_children());
    $sets = array_values(array_filter(sp_hpv_sets(), function ($s) use ($family) {
        foreach ($s['items'] as $it) {
            if (in_array((int) $it['id'], $family, true) || in_array((int) $it['id'], [547, 548, 549], true)) {
                return true;
            }
        }
        return false;
    }));
    $more = [];
    foreach (sp_hpv_products() as $q) {
        if ((int) $q['id'] === $id) {
            continue;
        }
        $q['v'] = count($q['vars']) > 1;
        $q['aid'] = $q['vars'] ? $q['vars'][0]['id'] : $q['id'];
        if (!$q['vol'] && count($q['vars']) === 1) {
            $q['vol'] = $q['vars'][0]['l'];
        }
        unset($q['vars']);
        $more[] = $q;
    }
    $bottle = $id === 65 ? content_url('/uploads/2026/08/retatrutide-tilted-glass-v3.png') : wp_get_attachment_image_url($p->get_image_id(), 'medium_large');
    return [
        'name' => $p->get_name(), 'price' => $price, 'thumb' => wp_get_attachment_image_url($p->get_image_id(), 'thumbnail'),
        'rt' => str_replace('.', ',', (string) ($map[$id]['num'] ?? 4.8)), 'rc' => (int) ($map[$id]['count'] ?? 0),
        'bottle' => $bottle, 'sets' => $sets, 'more' => $more,
    ];
}

add_action('wp_head', function () {
    if (!sp_ppv_active()) {
        return;
    }
    echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    ?>
<style id="sp-ppv-css">
/* ===== Startseite komplett (Entwurf) ===== */
body.home .elementor-211{display:flex;flex-direction:column}
body.home .elementor-211>*{order:50;width:100%}
body.home .elementor-211>[data-id="shbn001"]{order:1}
body.home .elementor-211>#sp-hp-prod{order:2}
body.home .elementor-211>[data-id="mfst001"]{order:3}
body.home .elementor-211>#sp-hp-proof{order:4}
body.home .elementor-211>[data-id="penTeaser1"]{order:5}
body.home .elementor-211>#sp-hp-sets{order:6}
body.home .elementor-211>[data-id="e761cf4"]{order:7}
body.home .elementor-211>[data-id="hiwk001"]{order:8}
body.home .elementor-211>[data-id="77e913a"]{order:9}
body.home .elementor-211>#sp-hp-faq{order:10}
body.home .elementor-211>#sp-nlh{order:11}
body.home .elementor-211>#sp-hp-info{order:12}
body.home .elementor-211>[data-id="045a884"],body.home .elementor-211>[data-id="a4ab146"]{order:13}
body.home .elementor-211>[data-id="a9d4d1b"],body.home .elementor-211>[data-id="d60a05b"],body.home .elementor-211>[data-id="spsocial1"],body.home .elementor-211>[data-id="midcta01"],body.home .elementor-211>[data-id="3ca703b"],body.home .elementor-211>[data-id="5c363d8"]{display:none!important}
body.home .elementor-211>.sp-hp-closed{display:none!important}
body.home .elementor-location-footer .elementor-element-97108f0{display:none!important}

/* ---- Einheitliche Abschnitts-Labels ---- */
body.home .sp-lbl,body.home .sp-mf-badge,body.home .sp-pt-eyebrow,body.home .sp-hiw-badge,body.home .sp-tools-badge,body.home .sp-abo-promo-eyebrow,body.home #sp-nlh .ey{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:6px 14px!important;border-radius:999px!important;font:700 10.5px/1.2 Sora,sans-serif!important;letter-spacing:.16em!important;text-transform:uppercase!important;border:1px solid rgba(255,255,255,.18)!important;background:rgba(255,255,255,.05)!important;color:#E6E9EC!important;margin:0 0 16px!important;box-shadow:none!important}
body.home .sp-lbl:before,body.home .sp-mf-badge:before,body.home .sp-pt-eyebrow:before,body.home .sp-hiw-badge:before,body.home .sp-tools-badge:before,body.home .sp-abo-promo-eyebrow:before{content:'';width:6px;height:6px;border-radius:50%;background:#C7CCD1;box-shadow:0 0 8px rgba(199,204,209,.7);flex:0 0 6px}
body.home #sp-nlh .ey i{width:6px;height:6px}
body.home .sp-lbl.lt,body.home .sp-hiw-badge{border-color:rgba(13,15,18,.14)!important;background:#fff!important;color:#4A5058!important}
body.home .sp-lbl.lt:before,body.home .sp-hiw-badge:before{background:#0D0F12;box-shadow:none}
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

/* ---- Produkte (Bestseller-Reihe) v2 ---- */
.sp-pc{position:relative;display:flex;flex-direction:column;background:#fff;border:1px solid #E3E6E9;border-radius:20px;overflow:hidden;color:#0D0F12;box-shadow:0 10px 28px rgba(13,15,18,.07);transition:transform .25s,box-shadow .25s}
.sp-pc:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(13,15,18,.13)}
.sp-pc a{text-decoration:none!important;color:inherit}
.sp-pc .im{position:relative;display:block;aspect-ratio:1/1;background:radial-gradient(circle at 50% 30%,#30363D 0%,#14171B 55%,#0B0D10 100%);overflow:hidden}
.sp-pc .im img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
.sp-pc:hover .im img{transform:scale(1.04)}
.sp-pc.pen .im img{object-fit:contain;padding:14px}
.sp-pc .im:after{content:'';position:absolute;left:0;right:0;bottom:0;height:38%;background:linear-gradient(transparent,rgba(11,13,16,.55));pointer-events:none}
.sp-pc .bd{position:absolute;z-index:1;top:12px;left:12px;font:700 10.5px Sora,sans-serif;padding:5px 10px;border-radius:999px;background:#fff;color:#0D0F12;letter-spacing:.02em}
.sp-pc .bd.s{background:#E8452C;color:#fff}
.sp-pc .bd.n{background:linear-gradient(120deg,#C7CCD1,#FFFFFF 50%,#C7CCD1)}
.sp-pc .coa{position:absolute;z-index:1;right:12px;bottom:12px;display:flex;align-items:center;gap:5px;font:600 10.5px Sora,sans-serif;color:#fff;padding:5px 9px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px)}
.sp-pc .tx{padding:14px 14px 14px;display:flex;flex-direction:column;gap:8px;flex:1}
.sp-pc .nm{font:700 16px/1.25 Sora,sans-serif;margin:0}
.sp-pc .rt{font-size:12px;color:#5A6068;display:flex;align-items:center;gap:6px}
.sp-pc .rt b{color:#F5A623;letter-spacing:1px}
.sp-pc .vo{display:flex;flex-wrap:wrap;gap:6px}
.sp-pc .vo button,.sp-pc .vo span{height:28px;padding:0 11px;border-radius:9px;border:1px solid #D5D9DD;background:#fff;font:600 12px Sora,sans-serif;color:#3A4048;cursor:pointer;display:inline-flex;align-items:center}
.sp-pc .vo span{cursor:default;background:#F4F5F6;border-color:#EEF0F2}
.sp-pc .vo button.on{background:#0D0F12;border-color:#0D0F12;color:#fff}
.sp-pc .qd{font-size:11.5px;font-weight:600;color:#2E9B57}
.sp-pc .pr{margin-top:auto;display:flex;align-items:baseline;gap:7px;flex-wrap:wrap}
.sp-pc .pr strong{font:800 19px Sora,sans-serif;letter-spacing:-.01em}
.sp-pc .pr s{font-size:13px;color:#9AA0A8}
.sp-pc .pr small{font-size:10.5px;color:#9AA0A8}
.sp-pc .add{height:44px;border:0;border-radius:13px;background:#0D0F12;color:#fff;font:700 13.5px Sora,sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;transition:background .2s}
.sp-pc .add:hover{background:#2A2F35}
.sp-pc .add.ok{background:#2E9B57}
.sp-pc .add[disabled]{opacity:.7}
.sp-pc.all{background:linear-gradient(160deg,#0D0F12,#23272C);color:#fff;justify-content:center;align-items:center;text-align:center;padding:24px;gap:10px;text-decoration:none!important}
.sp-pc.all .ar{width:56px;height:56px;border-radius:50%;background:linear-gradient(120deg,#C7CCD1,#fff 50%,#C7CCD1);color:#0D0F12;display:flex;align-items:center;justify-content:center;font-size:24px}
.sp-pc.all b{font-size:17px}.sp-pc.all span{font-size:12.5px;color:#9AA0A8}
.sp-trust{display:flex;justify-content:center;gap:10px 22px;flex-wrap:wrap;margin:22px 0 0;padding:0;list-style:none}
.sp-trust li{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:#3A4048}
.sp-trust li:before{content:'';width:16px;height:16px;border-radius:50%;background:#0D0F12 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center/9px no-repeat}
@media(max-width:900px){.sp-pc{flex:0 0 66%}.sp-pc.all{flex-basis:50%}.sp-trust{gap:8px 14px}.sp-trust li{font-size:12px}}
@media(min-width:901px){.sp-pc.all{display:none}}

/* ---- Kundenstimmen: Laufband + Flasche beim Scrollen ---- */
#sp-hp-proof{overflow:hidden;background:#F4F5F6!important}
#sp-hp-proof h2 span{background:linear-gradient(100deg,#0D0F12,#6A727C 50%,#0D0F12);-webkit-background-clip:text;background-clip:text;color:transparent}
.sp-sum{display:flex;align-items:center;justify-content:center;gap:16px;margin:0 auto 10px;padding:14px 20px;max-width:440px;background:#fff;border:1px solid #E3E6E9;border-radius:18px;box-shadow:0 8px 24px rgba(13,15,18,.06)}
.sp-sum .big{font:800 40px/1 Sora,sans-serif;color:#0D0F12}
.sp-sum .st{color:#F5A623;font-size:17px;letter-spacing:2px}
.sp-sum .t{font-size:12.5px;color:#5A6068;line-height:1.45}
.sp-sum .t b{color:#0D0F12}
.sp-stage{position:relative;margin:0 -16px;padding:40px 0 30px}
.sp-mq{position:relative;overflow:hidden;margin:0 0 14px;contain:paint}
.sp-mq:before,.sp-mq:after{content:'';position:absolute;top:0;bottom:0;width:28px;z-index:1;pointer-events:none}
.sp-mq:before{left:0;background:linear-gradient(90deg,#F4F5F6,rgba(244,245,246,0))}
.sp-mq:after{right:0;background:linear-gradient(270deg,#F4F5F6,rgba(244,245,246,0))}
.sp-mq .tr{display:flex;gap:14px;width:max-content;animation:spMq 55s linear infinite;will-change:transform;backface-visibility:hidden;transform:translate3d(0,0,0)}
.sp-mq.r2 .tr{animation-direction:reverse}
.sp-mq:hover .tr,.sp-mq:active .tr{animation-play-state:paused}
@keyframes spMq{from{transform:translate3d(0,0,0)}to{transform:translate3d(-50%,0,0)}}
.sp-rv{flex:0 0 270px;display:flex;flex-direction:column;gap:10px;background:#fff;border:1px solid #E3E6E9;border-radius:18px;padding:16px}
.sp-rv .st{color:#F5A623;font-size:13px;letter-spacing:2px}
.sp-rv p{margin:0;font-size:13.5px;line-height:1.55;color:#2A2F35;flex:1;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden;min-height:4.65em}
.sp-rv .au{display:flex;align-items:center;gap:10px;font-size:12px;color:#5A6068}
.sp-rv .au i{font-style:normal;width:32px;height:32px;border-radius:50%;background:#0D0F12;color:#fff;font-weight:700;font-size:11.5px;display:flex;align-items:center;justify-content:center}
.sp-rv .au b{display:block;color:#0D0F12;font-size:12.5px}
.sp-rv .au em{font-style:normal;color:#2E9B57;font-size:11px;font-weight:600}
.sp-bottle{position:absolute;z-index:2;left:50%;top:50%;width:190px;margin:-150px 0 0 -95px;pointer-events:none;will-change:transform;backface-visibility:hidden;transform:translate3d(0,0,0)}
.sp-bottle img{width:100%;height:auto;display:block}
.sp-glow{position:absolute;z-index:1;left:50%;top:50%;width:300px;height:300px;margin:-150px 0 0 -150px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.95),rgba(255,255,255,0) 65%);pointer-events:none}
.sp-bshadow{position:absolute;z-index:1;left:50%;top:50%;width:150px;height:36px;margin:120px 0 0 -75px;border-radius:50%;background:radial-gradient(ellipse,rgba(13,15,18,.28),rgba(13,15,18,0) 70%);pointer-events:none;will-change:transform,opacity}
@media(min-width:901px){.sp-stage{margin:0;padding:60px 0 40px}.sp-bottle{width:260px;margin:-200px 0 0 -130px}.sp-bshadow{width:200px;margin:160px 0 0 -100px}.sp-glow{width:420px;height:420px;margin:-210px 0 0 -210px}.sp-rv{flex-basis:300px}}
@media (prefers-reduced-motion:reduce){.sp-mq .tr{animation:none}}

/* ---- Abschnitte: Originalfarben fuer Abo (dunkel), Schritte (hell), Tools (dunkel) ---- */
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

/* ---- Forschungsbereiche ---- */
.sp-cats-h{display:flex;align-items:baseline;justify-content:space-between;margin:34px 0 12px}
.sp-cats-h b{font:700 17px Sora,sans-serif;color:#0D0F12}
.sp-cats-h a{font-size:13px;font-weight:600;color:#5A6068!important;text-decoration:none!important}
.sp-cats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
.sp-cat{position:relative;display:flex;flex-direction:column;justify-content:flex-end;aspect-ratio:1/1.08;border-radius:18px;overflow:hidden;text-decoration:none!important;background:radial-gradient(circle at 50% 30%,#30363D,#0B0D10 70%)}
.sp-cat img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85;transition:transform .5s}
.sp-cat.wide img{object-fit:contain;padding:12px 12px 40px}
.sp-cat:hover img{transform:scale(1.05)}
.sp-cat:after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(11,13,16,0) 40%,rgba(11,13,16,.85))}
.sp-cat span{position:relative;z-index:1;padding:0 12px 12px;color:#fff;font:700 13.5px/1.25 Sora,sans-serif}
.sp-cat small{display:block;font-weight:500;font-size:11px;color:#B9BEC5;margin-top:2px}
@media(max-width:900px){.sp-cats{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;scroll-padding:0 16px;margin:0 -16px;padding:2px 16px 10px;scrollbar-width:none}.sp-cats::-webkit-scrollbar{display:none}.sp-cat{flex:0 0 36%;scroll-snap-align:start}}
.sp-trust li.pay:before{background-color:#2A2F35}
/* ---- Kombi-Sets ---- */
.sp-set{display:flex;flex-direction:column;gap:12px;background:#fff;border:1px solid #E3E6E9;border-radius:20px;padding:18px;box-shadow:0 10px 28px rgba(13,15,18,.06)}
.sp-set .thumbs{display:flex;align-items:center}
.sp-set .thumbs span{width:62px;height:62px;border-radius:50%;overflow:hidden;border:3px solid #fff;background:radial-gradient(circle at 50% 30%,#30363D,#0B0D10 70%);box-shadow:0 4px 12px rgba(13,15,18,.18);margin-left:-14px}
.sp-set .thumbs span:first-child{margin-left:0}
.sp-set .thumbs img{width:100%;height:100%;object-fit:cover}
.sp-set .thumbs span.w img{object-fit:contain;padding:6px}
.sp-set .thumbs i{font-style:normal;margin-left:10px;font-size:12px;font-weight:700;color:#5A6068}
.sp-set h3{margin:0;font:700 17px Sora,sans-serif;color:#0D0F12}
.sp-set .why{margin:0;font-size:13px;line-height:1.55;color:#5A6068}
.sp-set ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:6px}
.sp-set li{display:flex;justify-content:space-between;gap:10px;font-size:13px;color:#2A2F35}
.sp-set li span:last-child{color:#5A6068;white-space:nowrap}
.sp-set .sum{display:flex;align-items:baseline;justify-content:space-between;border-top:1px dashed #D5D9DD;padding-top:10px;margin-top:auto}
.sp-set .sum strong{font:800 20px Sora,sans-serif;color:#0D0F12;white-space:nowrap}
.sp-set .sum{gap:10px}
.sp-set .ship{font-size:11.5px;font-weight:600;color:#2E9B57}
.sp-set .ship.no{color:#9AA0A8}
.sp-set .add{height:46px;border:0;border-radius:13px;background:#0D0F12;color:#fff;font:700 13.5px Sora,sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer}
.sp-set .add.ok{background:#2E9B57}
#sp-hp-sets .sp-rail{grid-template-columns:repeat(4,minmax(0,1fr))}
@media(max-width:900px){.sp-set{flex:0 0 80%}}
/* ---- FAQ ---- */
#sp-hp-faq .in{max-width:760px}
.sp-faq{border-top:1px solid #DDE1E5}
.sp-faq details{border-bottom:1px solid #DDE1E5}
.sp-faq summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:14px;padding:18px 2px;font:700 15.5px/1.35 Sora,sans-serif;color:#0D0F12}
.sp-faq summary::-webkit-details-marker{display:none}
.sp-faq summary:after{content:'+';flex:0 0 28px;height:28px;border-radius:50%;border:1px solid #D5D9DD;display:flex;align-items:center;justify-content:center;font-weight:500;font-size:18px;color:#3A4048;transition:transform .25s}
.sp-faq details[open] summary:after{transform:rotate(45deg);background:#0D0F12;color:#fff;border-color:#0D0F12}
.sp-faq p{margin:0;padding:0 40px 18px 2px;font-size:14px;line-height:1.65;color:#4A5058}
.sp-faq a{color:#0D0F12;font-weight:600}

html{overflow-x:clip}
#sp-hp-proof,#sp-hp-prod,#sp-hp-sets,#sp-hp-faq,#sp-nlh,.sp-stage{overflow:hidden}
html,body{overflow-x:hidden}
@supports (overflow:clip){html,body{overflow-x:clip}}
/* ===== Produktseite (Entwurf) ===== */
body.single-product .elementor-340{display:flex;flex-direction:column}
body.single-product .elementor-340>*{order:50;width:100%}
body.single-product .elementor-340>[data-id="5d034e1"]{order:1}
body.single-product .elementor-340>#sp-pp-proof{order:2}
body.single-product .elementor-340>#sp-pp-sets{order:3}
body.single-product .elementor-340>[data-id="4573496"]{order:4}
body.single-product .elementor-340>[data-id="9977f52"]{order:5}
body.single-product .elementor-340>#sp-pp-more{order:6}
body.single-product .elementor-340>#sp-pp-info{order:7}
body.single-product .elementor-340>[data-id="b221b92"],body.single-product .elementor-340>[data-id="rtxrel01"],body.single-product .elementor-340>[data-id="4be25d4"]{display:none!important}
body.single-product .elementor-location-footer .elementor-element-97108f0{display:none!important}
/* Labels einheitlich */
.sp-pp .sp-lbl,body.single-product .sp-sci-badge{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:6px 14px!important;border-radius:999px!important;font:700 10.5px/1.2 Sora,sans-serif!important;letter-spacing:.16em!important;text-transform:uppercase!important;border:1px solid rgba(255,255,255,.18)!important;background:rgba(255,255,255,.05)!important;color:#E6E9EC!important;margin:0 0 16px!important}
.sp-pp .sp-lbl:before,body.single-product .sp-sci-badge:before{content:'';width:6px;height:6px;border-radius:50%;background:#C7CCD1;box-shadow:0 0 8px rgba(199,204,209,.7);flex:0 0 6px}
.sp-pp .sp-lbl.lt{border-color:rgba(13,15,18,.14)!important;background:#fff!important;color:#4A5058!important}
.sp-pp .sp-lbl.lt:before{background:#0D0F12;box-shadow:none}
/* Dunkle Abschnitte */
.sp-hp-dark{background:linear-gradient(160deg,#0D0F12 0%,#1E2226 100%)}
.sp-hp-dark h2{color:#fff!important}
.sp-hp-dark .sub{color:rgba(255,255,255,.62)!important}
/* Kundenstimmen dunkel */
#sp-pp-proof h2 span{background:linear-gradient(100deg,#9AA3AD,#F4F6F8 50%,#B8BFC6);-webkit-background-clip:text;background-clip:text;color:transparent}
#sp-pp-proof .sp-sum{background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12);box-shadow:none}
#sp-pp-proof .sp-sum .big{color:#fff}
#sp-pp-proof .sp-sum .t{color:#B9BEC5}#sp-pp-proof .sp-sum .t b{color:#fff}
#sp-pp-proof .sp-rv{background:#1A1E22;border-color:#2C3137}
#sp-pp-proof .sp-rv p{color:#D5D9DD}
#sp-pp-proof .sp-rv .au{color:#9AA0A8}#sp-pp-proof .sp-rv .au b{color:#fff}
#sp-pp-proof .sp-rv .au i{background:#E6E9EC;color:#0D0F12}
#sp-pp-proof .sp-mq:before{background:linear-gradient(90deg,#15181C,rgba(21,24,28,0))}
#sp-pp-proof .sp-mq:after{background:linear-gradient(270deg,#15181C,rgba(21,24,28,0))}
#sp-pp-proof .sp-glow{background:radial-gradient(circle,rgba(210,215,220,.22),rgba(210,215,220,0) 65%)}
#sp-pp-proof .sp-bshadow{background:radial-gradient(ellipse,rgba(0,0,0,.6),rgba(0,0,0,0) 70%)}
/* Lexikon: Abschnitt dunkel, Karte bleibt */
body.single-product [data-id="4573496"]{background:linear-gradient(160deg,#0D0F12 0%,#1E2226 100%)!important;padding-top:40px!important;padding-bottom:40px!important}
body.single-product [data-id="4573496"] .sp-sci-card{background:linear-gradient(160deg,#1A1E22,#262A2F)!important;border:1px solid #2C3137!important}
/* FAQ im Startseiten-Stil */
body.single-product [data-id="9977f52"]{background:linear-gradient(180deg,#FFFFFF,#EEF0F2)!important;padding-top:44px!important;padding-bottom:44px!important}
body.single-product .sp-reta-faq h3{font:700 clamp(24px,3vw,32px)/1.2 Sora,sans-serif!important;letter-spacing:-.02em;text-align:center;margin:0 0 22px!important;color:#0D0F12!important}
body.single-product .sp-reta-faq details{border:0!important;border-bottom:1px solid #DDE1E5!important;border-radius:0!important;background:transparent!important;margin:0!important;padding:0!important;box-shadow:none!important}
body.single-product .sp-reta-faq details:first-of-type{border-top:1px solid #DDE1E5!important}
body.single-product .sp-reta-faq summary{list-style:none;display:flex!important;justify-content:space-between;align-items:center;gap:14px;padding:18px 2px!important;font:700 15px/1.4 Sora,sans-serif!important;color:#0D0F12!important;cursor:pointer}
body.single-product .sp-reta-faq summary::-webkit-details-marker{display:none}
body.single-product .sp-reta-faq .sp-faq-arrow{display:none!important}
body.single-product .sp-reta-faq summary:after{content:'+';flex:0 0 28px;height:28px;border-radius:50%;border:1px solid #D5D9DD;display:flex;align-items:center;justify-content:center;font-weight:500;font-size:18px;color:#3A4048;transition:transform .25s}
body.single-product .sp-reta-faq details[open] summary:after{transform:rotate(45deg);background:#0D0F12;color:#fff;border-color:#0D0F12}
body.single-product .sp-reta-faq details p{margin:0!important;padding:0 40px 18px 2px!important;font-size:14px!important;line-height:1.65!important;color:#4A5058!important}
/* Weitere Produkte auf dunkel */
#sp-pp-more .sp-pc{border-color:#2C3137;box-shadow:none}
#sp-pp-more .sp-hp-more-a a{background:linear-gradient(120deg,#C7CCD1,#fff 50%,#C7CCD1);color:#0D0F12!important}
/* Hinweis-Zeile */
#sp-pp-info{background:linear-gradient(180deg,#FFFFFF,#EEF0F2);padding:24px 20px;text-align:center;font-family:Sora,sans-serif}
#sp-pp-info .dis{display:inline-flex;align-items:flex-start;gap:8px;max-width:640px;margin:0 auto;font-size:12.5px;line-height:1.55;color:#4A5058;text-align:left}
#sp-pp-info .dis b{color:#0D0F12}
/* Sticky Kaufleiste (Handy) */
#sp-pp-bar{position:fixed;left:10px;right:10px;bottom:10px;z-index:199995;display:flex;align-items:center;gap:10px;padding:8px 8px 8px 10px;border-radius:18px;background:rgba(13,15,18,.92);-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.1);box-shadow:0 14px 34px rgba(0,0,0,.35);font-family:Sora,sans-serif;transform:translate3d(0,140%,0);transition:transform .35s cubic-bezier(.2,.8,.2,1)}
#sp-pp-bar.on{transform:translate3d(0,0,0)}
#sp-pp-bar img{width:42px;height:42px;border-radius:10px;object-fit:cover;background:#0B0D10;flex:0 0 42px}
#sp-pp-bar .t{flex:1;min-width:0;color:#fff;line-height:1.25}
#sp-pp-bar .t b{display:block;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
#sp-pp-bar .t span{font-size:13px;color:#C9CDD2}
#sp-pp-bar button{flex:0 0 auto;height:44px;padding:0 16px;border:0;border-radius:13px;background:linear-gradient(120deg,#C7CCD1,#FFFFFF 45%,#C7CCD1);color:#0D0F12;font:700 13.5px Sora,sans-serif;display:flex;align-items:center;gap:7px;cursor:pointer}
body.sp-bar-on #sp-cart-fab{bottom:86px!important;transition:bottom .35s}
body.sp-bar-on #sp-social-fab{bottom:154px!important;transition:bottom .35s}
@media(min-width:901px){#sp-pp-bar{display:none}}
/* volle Breite fuer die neuen Abschnitte */
body.single-product .elementor-340>#sp-pp-proof,body.single-product .elementor-340>#sp-pp-sets,body.single-product .elementor-340>#sp-pp-more,body.single-product .elementor-340>#sp-pp-info{width:100vw!important;max-width:100vw!important;margin-left:calc(50% - 50vw)!important;margin-right:calc(50% - 50vw)!important;box-sizing:border-box}
body.single-product{overflow-x:hidden}
body.single-product .sp-reta-faq h3{text-align:center!important}
.sp-pc .add,.sp-set .add{white-space:nowrap;font-size:13px}

/* ================= Oberer Teil: Hero + Kaufbox ================= */
/* doppelte/ueberfluessige Elemente */
body.single-product [data-id="d449ce7"],body.single-product #rx-buybox .rx-trustbadges,body.single-product #sp-crosssell-box,body.single-product #rx-buybox .rx-disclaimer{display:none!important}
/* Hero-Text */
body.single-product .sp-reta-hero{font-family:Sora,sans-serif}
body.single-product .sp-reta-title{font:800 34px/1.1 Sora,sans-serif!important;letter-spacing:-.02em!important;margin:6px 0 4px!important}
body.single-product .sp-reta-subtitle-line{font-size:13.5px!important;margin:0 0 16px!important}
body.single-product .sp-reta-checks{display:grid!important;grid-template-columns:1fr 1fr;gap:8px!important}
body.single-product .sp-reta-check-item{display:flex!important;align-items:center!important;gap:8px!important;padding:10px 11px!important;border-radius:13px;border:1px solid #E3E6E9;background:#F6F7F8;font-size:12px!important;line-height:1.3!important;font-weight:600!important;margin:0!important}
body.single-product .sp-reta-check-item svg{flex:0 0 16px;width:16px!important;height:16px!important}
/* Bild-Badges */
body.single-product [data-id="7269057"]{position:relative}
.sp-img-bd{position:absolute;z-index:2;top:16px;left:16px;display:flex;gap:6px;flex-wrap:wrap;pointer-events:none}
.sp-img-bd span{font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;background:#fff;color:#0D0F12;box-shadow:0 4px 14px rgba(0,0,0,.25)}
.sp-img-bd span.g{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.25);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);box-shadow:none}
@media(max-width:900px){
 body.single-product [data-id="5d034e1"]{padding-top:0!important}
 body.single-product [data-id="5d034e1"]>.e-con-inner,body.single-product [data-id="825e5c4"]{gap:0!important}
 body.single-product [data-id="7269057"]{width:100vw!important;max-width:100vw!important;margin-left:calc(50% - 50vw)!important;padding:14px 16px 4px!important;background:radial-gradient(120% 70% at 50% 25%,#2A2F35 0%,#121519 55%,#0B0D10 100%)!important;box-sizing:border-box}
 .sp-img-bd{top:28px;left:30px}
 body.single-product [data-id="279d771"]{width:100vw!important;max-width:100vw!important;margin-left:calc(50% - 50vw)!important;padding:18px 20px 26px!important;background:linear-gradient(180deg,#0B0D10 0%,#15181C 100%)!important;box-sizing:border-box;border-radius:0 0 28px 28px;margin-bottom:18px!important}
 body.single-product .sp-reta-title{color:#fff!important}
 body.single-product .sp-reta-subtitle-line{color:#9AA0A8!important}
 body.single-product .sp-reta-rating,body.single-product .sp-reta-rating *{color:#E6E9EC!important}
 body.single-product .sp-reta-rating .sp-rate-stars-fg{color:#F5A623!important}
 body.single-product .sp-reta-rating .sp-rate-stars-bg{color:rgba(255,255,255,.2)!important}
 body.single-product .sp-reta-check-item{background:rgba(255,255,255,.05)!important;border-color:rgba(255,255,255,.12)!important;color:#E6E9EC!important}
 body.single-product .sp-reta-check-item span{color:#E6E9EC!important}
 body.single-product .sp-reta-check-item svg *{stroke:#7DDBA0!important}
}
/* Pen-Hinweis kompakter */
body.single-product .sp-pen-promo{border-radius:18px!important;border:1px solid #E3E6E9!important;box-shadow:none!important;background:linear-gradient(135deg,#FFFFFF,#F4F5F6)!important}
/* Kaufbox als klare Karte */
body.single-product #rx-buybox{border-radius:24px!important;border:1px solid #E3E6E9!important;box-shadow:0 20px 44px rgba(13,15,18,.09)!important;padding:22px 18px 18px!important;background:#fff!important;font-family:Sora,sans-serif}
body.single-product #rx-buybox .rx-price{font:800 36px/1 Sora,sans-serif!important;letter-spacing:-.02em;color:#0D0F12!important}
body.single-product #rx-buybox .rx-price-note{font-size:12px!important;color:#9AA0A8!important}
body.single-product #rx-buybox .rx-tiers-head{font:700 11px Sora,sans-serif!important;letter-spacing:.14em!important;text-transform:uppercase;color:#5A6068!important;margin:18px 0 9px!important}
body.single-product #rx-buybox .rx-tier{border:1.5px solid #DDE1E5!important;border-radius:15px!important;background:#fff!important;color:#0D0F12!important;box-shadow:none!important;transition:border-color .2s,background .2s,transform .15s}
body.single-product #rx-buybox .rx-tier:active{transform:scale(.97)}
body.single-product #rx-buybox .rx-tier.is-active{background:#0D0F12!important;border-color:#0D0F12!important;color:#fff!important}
body.single-product #rx-buybox .rx-tier.is-active *{color:#fff!important}
body.single-product #rx-buybox .rx-tier__disc{display:inline-block;font:700 10.5px Sora,sans-serif!important;color:#2E9B57!important;background:rgba(46,155,87,.1);padding:2px 6px;border-radius:999px}
body.single-product #rx-buybox .rx-tier.is-active .rx-tier__disc{background:rgba(125,219,160,.18)!important;color:#7DDBA0!important}
body.single-product #rx-buybox .rx-tier__q{font:800 17px Sora,sans-serif!important}
body.single-product #rx-buybox .rx-tier__flag{background:linear-gradient(120deg,#C7CCD1,#FFFFFF 50%,#C7CCD1)!important;color:#0D0F12!important;border-radius:999px!important}
body.single-product #rx-buybox .rx-addon-row{border:1px solid #EEF0F2!important;border-radius:14px!important;padding:8px 10px!important;margin:0 0 8px!important;background:#FAFAFB!important}
body.single-product #rx-buybox .rx-addon-img{border-radius:10px!important}
body.single-product #rx-buybox .rx-addon-btn{border-radius:10px!important}
body.single-product #rx-buybox .sp-abo-switch{border-radius:15px!important;background:#F1F2F4!important;padding:4px!important}
body.single-product #rx-buybox .sp-abo-opt{border-radius:12px!important}
body.single-product #rx-buybox .rx-buysum{border-radius:15px!important;background:#F6F7F8!important;border:0!important}
body.single-product #rx-buybox .single_add_to_cart_button{height:56px!important;border-radius:16px!important;background:linear-gradient(135deg,#0D0F12,#2A2F35)!important;color:#fff!important;font:700 16px Sora,sans-serif!important;letter-spacing:.01em;box-shadow:0 12px 26px rgba(13,15,18,.25)!important;display:flex!important;align-items:center;justify-content:center;gap:10px;width:100%!important}
body.single-product #rx-buybox .single_add_to_cart_button:before{content:'';width:18px;height:18px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='9' cy='21' r='1'/%3E%3Ccircle cx='20' cy='21' r='1'/%3E%3Cpath d='M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6'/%3E%3C/svg%3E") center/contain no-repeat}
/* Vertrauens-Zeile unter dem Button */
.sp-bb-trust{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:14px 0 4px}
.sp-bb-trust div{display:flex;flex-direction:column;align-items:center;text-align:center;gap:5px;padding:10px 4px;border-radius:13px;background:#F6F7F8;font:600 11px/1.3 Sora,sans-serif;color:#3A4048}
.sp-bb-trust div b{font-size:16px;line-height:1}
body.single-product #rx-buybox .rx-pay-in__k{font:700 11px Sora,sans-serif!important;letter-spacing:.14em;text-transform:uppercase;color:#5A6068!important}
/* Tabs als Pillen */
body.single-product .rx-tabnav{gap:8px!important}
body.single-product .rx-tabbtn{border-radius:999px!important;border:1px solid #DDE1E5!important;background:#fff!important;color:#3A4048!important;font:700 13px Sora,sans-serif!important;box-shadow:none!important}
body.single-product .rx-tabbtn.is-active{background:#0D0F12!important;border-color:#0D0F12!important;color:#fff!important}
body.single-product .rx-tabcard{border-radius:22px!important;border:1px solid #E3E6E9!important;box-shadow:none!important}
body.single-product .sp-reta-checks{grid-template-columns:repeat(2,minmax(0,1fr))!important}
body.single-product .sp-reta-check-item span{min-width:0;overflow-wrap:anywhere}
@media(max-width:900px){
 body.single-product [data-id="7269057"]{padding:14px 0 4px!important}
 .sp-img-bd{top:30px;left:30px}
}
@media(max-width:900px){
 body.single-product [data-id="7269057"]{padding:14px 0 14px!important}
 body.single-product [data-id="279d771"]{padding-top:8px!important}
}

/* ===== v3: Hero-Bild randlos mit weichem Uebergang, saubere Abstaende ===== */
@media(max-width:900px){
 body.single-product [data-id="5d034e1"]{background:linear-gradient(180deg,#0B0D10 0,#0B0D10 260px,transparent 260px)!important}
 body.single-product [data-id="825e5c4"]{padding-top:0!important}
 body.single-product [data-id="7269057"]{padding:0!important;background:#0B0D10!important}
 .sp-img-bd{top:16px;left:16px}
 body.single-product [data-id="279d771"]{margin-top:-1px!important;padding:0 20px 24px!important;background:linear-gradient(180deg,#0B0D10 0%,#15181C 100%)!important;border-radius:0 0 26px 26px;margin-bottom:16px!important;position:relative;z-index:1}
 body.single-product .sp-reta-hero{margin-top:-46px;position:relative}
 body.single-product .sp-reta-check-item{white-space:nowrap;font-size:12.5px!important;padding:11px 10px!important}
 body.single-product .sp-reta-check-item span{overflow-wrap:normal}
 /* Breiten vereinheitlichen: alles 16px vom Rand */
 body.single-product .sp-pen-promo,body.single-product #rx-buybox{margin-left:0!important;margin-right:0!important;width:100%!important;max-width:100%!important;box-sizing:border-box}
 body.single-product [data-id="spwpenpromo1"],body.single-product [data-id="de228b8"]{margin:0 0 14px!important;padding:0!important;width:100%!important}
}
/* Pen-Hinweis: eine schlanke Zeile statt Box in Box */
body.single-product .sp-pen-promo{padding:0!important;border:0!important;background:transparent!important;box-shadow:none!important}
body.single-product .sp-pen-promo-eyebrow-row{display:none!important}
body.single-product .sp-pen-promo-row{display:flex!important;align-items:center!important;gap:12px!important;padding:10px 12px!important;border-radius:16px!important;border:1px solid #E3E6E9!important;background:linear-gradient(135deg,#FFFFFF,#F4F5F6)!important;box-shadow:none!important;text-decoration:none!important}
body.single-product .sp-pen-promo-icon{flex:0 0 46px!important;width:46px!important;height:46px!important;border-radius:12px!important;overflow:hidden;background:#0B0D10!important}
body.single-product .sp-pen-promo-icon img{width:100%!important;height:100%!important;object-fit:cover!important}
body.single-product .sp-pen-promo-text{flex:1!important;min-width:0!important}
body.single-product .sp-pen-promo-name{display:block!important;font:700 14px/1.3 Sora,sans-serif!important;color:#0D0F12!important;white-space:normal!important}
body.single-product .sp-pen-promo-blurb{display:block!important;font-size:12px!important;color:#5A6068!important;white-space:normal!important;overflow:visible!important;text-overflow:clip!important}
body.single-product .sp-pen-promo-aside .sp-pen-promo-price{display:none!important}
body.single-product .sp-pen-promo-name em{font-style:normal;font:700 9.5px Sora,sans-serif;letter-spacing:.08em;background:linear-gradient(120deg,#C7CCD1,#fff 50%,#C7CCD1);color:#0D0F12;padding:2px 6px;border-radius:999px;margin-left:6px;vertical-align:2px}
/* Preiszeile: Lager + Versand kompakt */
body.single-product #rx-buybox .rx-stock{font-size:12.5px!important}
body.single-product #rx-buybox .rx-shipbadges{margin-top:8px!important}

@media(max-width:900px){body.single-product [data-id="825e5c4"]{padding-left:0!important;padding-right:0!important}}

/* ===== v4: kompakteres Produktbild ===== */
@media(max-width:900px){
 body.single-product [data-id="7269057"]{padding:10px 0 0!important;background:radial-gradient(60% 55% at 50% 45%,#2A2F35 0%,#14171B 60%,#0B0D10 100%)!important}
 body.single-product .sp-reta-hero{margin-top:-18px}
 body.single-product [data-id="5d034e1"]{background:linear-gradient(180deg,#0B0D10 0,#0B0D10 200px,transparent 200px)!important}
}

/* ===== v4b: Kaufleiste mit Warenkorb-Symbol, Schwebe-Buttons ausblenden ===== */
body.sp-bar-on #sp-cart-fab,body.sp-bar-on #sp-social-fab{opacity:0!important;pointer-events:none!important;transform:scale(.6)!important;transition:opacity .25s,transform .25s!important}
#sp-pp-bar .cb{position:relative;flex:0 0 44px;width:44px;height:44px;border-radius:13px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.06);color:#fff;display:flex;align-items:center;justify-content:center;padding:0;cursor:pointer}
#sp-pp-bar .cb i{position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 5px;border-radius:999px;background:#E8452C;color:#fff;font:700 10.5px/18px Sora,sans-serif;font-style:normal;text-align:center;box-sizing:border-box}
#sp-pp-bar .cb i.z{display:none}
#sp-pp-bar img{width:38px;height:38px;flex-basis:38px}
#sp-pp-bar button.go{padding:0 14px}
/* Tabs: weniger Leerraum */
body.single-product .rx-tabcard{padding-top:22px!important}
body.single-product .rx-tabpanel>*:first-child,body.single-product .sp-reta-desc{margin-top:0!important;padding-top:0!important}
body.single-product .sp-reta-desc h3{margin-top:0!important}
/* FAQ: keine Linie ueber der Ueberschrift, weniger Luft unten */
body.single-product .sp-reta-faq{border:0!important;padding-top:0!important;padding-bottom:0!important}
body.single-product [data-id="9977f52"]{padding-bottom:30px!important}
body.single-product [data-id="9977f52"]>.e-con-inner{padding-bottom:0!important;min-height:0!important}
#sp-pp-bar img{display:none!important}#sp-pp-bar{padding-left:14px}#sp-pp-bar .t b{font-size:14px}

/* ===== v5: Produktbild wie im Original (abgerundete Karte, hell) ===== */
@media(max-width:900px){
 body.single-product [data-id="5d034e1"]{background:linear-gradient(180deg,#F4F5F6 0,#FFFFFF 520px)!important;padding-top:14px!important}
 body.single-product [data-id="7269057"]{width:100%!important;max-width:100%!important;margin-left:0!important;padding:0 16px!important;background:transparent!important;box-sizing:border-box}
 .sp-img-bd{top:14px;left:14px}
 body.single-product [data-id="279d771"]{width:100%!important;max-width:100%!important;margin:0 0 14px!important;padding:18px 16px 0!important;background:transparent!important;border-radius:0!important}
 body.single-product .sp-reta-hero{margin-top:0!important}
 body.single-product .sp-reta-title{color:#0D0F12!important}
 body.single-product .sp-reta-subtitle-line{color:#5A6068!important}
 body.single-product .sp-reta-rating,body.single-product .sp-reta-rating *{color:#3A4048!important}
 body.single-product .sp-reta-rating .sp-rate-stars-fg{color:#F5A623!important}
 body.single-product .sp-reta-rating .sp-rate-stars-bg{color:#DDE1E5!important}
 body.single-product .sp-reta-check-item{background:#F6F7F8!important;border-color:#E3E6E9!important;color:#0D0F12!important}
 body.single-product .sp-reta-check-item span{color:#0D0F12!important}
 body.single-product .sp-reta-check-item svg *{stroke:#2E9B57!important}
 body.single-product [data-id="825e5c4"]{padding-left:16px!important;padding-right:16px!important}
}
@media(max-width:900px){body.single-product [data-id="7269057"]{padding:0!important}body.single-product [data-id="825e5c4"]{padding-left:0!important;padding-right:0!important}body.single-product [data-id="279d771"]{padding:18px 0 0!important}}

/* kein seitliches Ueberstehen (weisser Rand rechts) */
html,body.single-product{overflow-x:clip!important}
.sp-stage{margin-left:-16px!important;margin-right:-16px!important}

/* Kaufbox unten: Kunden + Bezahlung zentriert */
body.single-product #rx-social-proof{justify-content:center!important;text-align:center}
body.single-product #rx-buybox .rx-pay-in{text-align:center!important;align-items:center!important}
body.single-product #rx-buybox .rx-pay-in__k{display:block;text-align:center!important}
body.single-product #rx-buybox .rx-pay-in__row{justify-content:center!important}
@media(max-width:900px){body.single-product [data-id="7269057"] .elementor-widget-image{text-align:center!important}body.single-product [data-id="7269057"] img{display:inline-block!important}.sp-img-bd{left:0;right:0;justify-content:center;top:14px}}

/* ===== v7: Vollbreite ohne 100vw (Safari/iPhone) ===== */
body.single-product .elementor-340>#sp-pp-proof,body.single-product .elementor-340>#sp-pp-sets,body.single-product .elementor-340>#sp-pp-more,body.single-product .elementor-340>#sp-pp-info{width:auto!important;max-width:none!important;margin-left:-20px!important;margin-right:-20px!important;align-self:stretch!important}
#sp-pp-proof,#sp-pp-sets,#sp-pp-more,#sp-pp-info,#sp-nlh,.sp-mq,.sp-stage{overflow:hidden!important}
html,body{overflow-x:hidden!important}
@supports (overflow:clip){html,body{overflow-x:clip!important}}
#sp-hpv-flag{position:fixed;left:12px;top:12px;z-index:200000;background:#FF8A5C;color:#0D0F12;font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.3);pointer-events:none}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    if (!sp_ppv_active()) {
        return;
    }
    ?>
<div id="sp-hpv-flag">ENTWURF-VORSCHAU</div>
<script id="sp-ppv-js">
window.SP_PP=<?php echo wp_json_encode(sp_ppv_data()); ?>;
document.addEventListener('DOMContentLoaded',function(){
 var root=document.querySelector('body.single-product .elementor-340'); if(!root)return;
 var D=window.SP_PP||{};
 function el(h){var d=document.createElement('div');d.innerHTML=h.trim();return d.firstChild;}
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 var cart='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg>';
 function addIds(ids,b,done){var old=b.innerHTML,i=0,last=null;b.disabled=true;b.textContent='…';
   (function next(){if(i>=ids.length){b.classList.add('ok');b.innerHTML=done;if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[last&&last.fragments,last&&last.cart_hash,jQuery(b)]);setTimeout(function(){b.classList.remove('ok');b.innerHTML=old;b.disabled=false;},2400);return;}
     var fd=new FormData();fd.append('product_id',ids[i]);fd.append('quantity','1');
     fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){last=r;i++;next();}).catch(function(){b.innerHTML=old;b.disabled=false;});})();}
 /* 0) Oberer Teil: Bild-Badges + Vertrauens-Zeile in der Kaufbox */
 var imc=document.querySelector('[data-id="7269057"]');
 if(imc&&!imc.querySelector('.sp-img-bd')){imc.appendChild(el('<div class="sp-img-bd"><span>★ Bestseller</span><span class="g">✓ HPLC ≥ 99 %</span></div>'));}
 var short={'99 % Reinheit (HPLC)':'99 % Reinheit','LC-MS Identitätsverifizierung':'LC-MS geprüft','Chargenspezifisches Analysezertifikat (COA)':'Mit Zertifikat','Diskrete Verpackung & schneller Versand':'Diskreter Versand'};
 var pn=document.querySelector('.sp-pen-promo-name'),pb=document.querySelector('.sp-pen-promo-blurb'),pp=document.querySelector('.sp-pen-promo-price');
 if(pn&&!pn.querySelector('em')){pn.innerHTML='Auch als Peptrium-Pen <em>NEU</em>';}
 if(pb&&pp){pb.textContent='Fertig gemischt, kein Anmischen · '+pp.textContent.trim();}
 document.querySelectorAll('.sp-reta-check-item span').forEach(function(s){var t=s.textContent.trim();if(short[t])s.textContent=short[t];});
 var cta=document.querySelector('#rx-buybox .rx-buycta');
 if(cta&&!document.querySelector('.sp-bb-trust')){cta.parentNode.insertBefore(el('<div class="sp-bb-trust"><div><b>🚚</b>Gratisversand ab 100 €</div><div><b>📦</b>Neutral verpackt</div><div><b>🔒</b>Sicher bezahlen</div></div>'),cta.nextSibling);}
 /* 1) Kundenstimmen: dunkel, Laufband + Flasche */
 var rv=[];
 document.querySelectorAll('[data-id="b221b92"] .rv-card').forEach(function(c){var t=c.querySelector('.rv-text'),n=c.querySelector('.rv-name'),a=c.querySelector('.rv-avatar');if(t)rv.push({t:t.textContent.trim(),n:n?n.textContent.trim():'Verifizierter Kunde',a:a?a.textContent.trim():'✓',s:c.querySelectorAll('.rv-stars > svg').length||5});});
 if(rv.length<4)(D.extraReviews||[]).forEach(function(t){rv.push({t:t,n:'Verifizierter Kunde',a:'✓',s:5});});
 function card(r){return '<div class="sp-rv"><div class="st">'+'★★★★★'.slice(0,r.s)+'</div><p>„'+r.t+'“</p><div class="au"><i>'+r.a+'</i><div><b>'+r.n+'</b><em>✓ Verifizierter Kauf</em></div></div></div>';}
 function row(list,cls){var s=list.map(card).join('');return '<div class="sp-mq '+cls+'"><div class="tr">'+s+s+'</div></div>';}
 var h1=rv.filter(function(_,i){return i%2===0;}),h2=rv.filter(function(_,i){return i%2===1;});
 var proof=el('<section id="sp-pp-proof" class="sp-pp sp-hp-sec sp-hp-dark"><div class="in"><div class="hd"><span class="sp-lbl">Kundenstimmen</span><h2>Das sagen Kunden über <span>'+D.name+'</span>.</h2></div>'
  +'<div class="sp-sum"><div class="big">'+D.rt+'</div><div><div class="st">★★★★★</div><div class="t">aus <b>'+D.rc+' Bewertungen</b><br>von verifizierten Käufern</div></div></div>'
  +'<div class="sp-stage">'+row(h1,'r1')+row(h2,'r2')+'<div class="sp-glow"></div><div class="sp-bshadow"></div><div class="sp-bottle"><img src="'+D.bottle+'" alt="'+D.name+'"></div></div></div></section>');
 root.appendChild(proof);
 var stage=proof.querySelector('.sp-stage'),bottle=proof.querySelector('.sp-bottle'),bsh=proof.querySelector('.sp-bshadow'),cur=0,tgt=0,run=false,vis=false;
 function target(){var r=stage.getBoundingClientRect(),vh=window.innerHeight;var p=((r.top+r.height/2)-vh/2)/vh;return Math.max(-1,Math.min(1,p));}
 function paint(p){bottle.style.transform='translate3d(0,'+(p*110).toFixed(1)+'px,0) rotate('+(p*-22+6).toFixed(2)+'deg)';bsh.style.transform='translate3d(0,'+(p*40).toFixed(1)+'px,0) scale('+(1-Math.abs(p)*0.25).toFixed(3)+')';bsh.style.opacity=(1-Math.abs(p)*0.5).toFixed(2);}
 function loop(){cur+=(tgt-cur)*0.12;paint(cur);if(Math.abs(tgt-cur)>0.0005&&vis){requestAnimationFrame(loop);}else{run=false;}}
 function kick(){tgt=target();if(!run&&vis){run=true;requestAnimationFrame(loop);}}
 if('IntersectionObserver' in window){new IntersectionObserver(function(e){vis=e[0].isIntersecting;if(vis)kick();},{rootMargin:'200px 0px'}).observe(stage);}else{vis=true;}
 window.addEventListener('scroll',kick,{passive:true});cur=tgt=target();paint(cur);

 /* Laufband: beide Reihen gleich schnell (Dauer aus der echten Breite, ~28 px/s) */
 function mqSpeed(){document.querySelectorAll('.sp-mq .tr').forEach(function(tr){var w=tr.scrollWidth/2;if(w>0)tr.style.animationDuration=(w/46).toFixed(1)+'s';});}
 mqSpeed();window.addEventListener('load',mqSpeed);window.addEventListener('resize',mqSpeed);
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
  +P.map(function(p){var vo=p.vol?'<div class="vo"><span>'+p.vol+'</span></div>':'';return '<div class="sp-pc'+(p.pen?' pen':'')+'" data-id="'+p.aid+'"><a class="im" href="'+p.u+'"><img loading="lazy" src="'+p.i+'" alt="'+p.n+'">'+(p.r?'<span class="bd s">−'+Math.round((1-p.p/p.r)*100)+' %</span>':'')+'<span class="coa">✓ COA</span></a><div class="tx"><a href="'+p.u+'"><h3 class="nm">'+p.n+'</h3></a><div class="rt"><b>★★★★★</b>'+String(p.rt).replace('.',',')+' ('+p.rc+')</div>'+vo+'<div class="pr"><strong>'+(p.v?'ab ':'')+eur(p.p)+'</strong>'+(p.r?'<s>'+eur(p.r)+'</s>':'')+'</div>'+(p.v?'<a class="add" href="'+p.u+'">Größe wählen</a>':'<button type="button" class="add">'+cart+'In den Warenkorb</button>')+'</div></div>';}).join('')
  +'</div><div class="sp-hp-more-a"><a href="/alle-produkte/">Alle Produkte ansehen →</a></div></div></section>');
  root.appendChild(more);
  more.addEventListener('click',function(e){var b=e.target.closest('button.add');if(!b)return;addIds([b.closest('.sp-pc').getAttribute('data-id')],b,'✓ Hinzugefügt');});}
 /* 4) Hinweis als Zeile */
 root.appendChild(el('<div id="sp-pp-info"><div class="dis"><span>ⓘ</span><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div></div>'));
 /* 5) Sticky Kaufleiste */
 var real=document.querySelector('form.cart .single_add_to_cart_button');
 if(real){
  var bag='<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>';
  var bar=el('<div id="sp-pp-bar"><img src="'+D.thumb+'" alt=""><div class="t"><b>'+D.name+'</b><span class="pr">'+eur(D.price)+'</span></div><button type="button" class="cb" aria-label="Warenkorb öffnen">'+bag+'<i class="z">0</i></button><button type="button" class="go">'+cart+'In den Warenkorb</button></div>');
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
</script>
    <?php
}, 99);
