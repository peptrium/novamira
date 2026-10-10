<?php
/**
 * Plugin Name: SP Startseiten-Vorschau
 * Description: Entwurf der neuen Startseite NUR ueber einen geheimen Vorschau-Link (2026-10-10).
 *
 * https://peptrium.com/?sp_vorschau=TOKEN zeigt die Startseite mit dem Entwurf
 * (neue Reihenfolge, Hell/Dunkel-Wechsel, Bestseller-Reihe mit Direkt-Kauf,
 * Kundenstimmen-Laufband mit Flasche beim Scrollen, ...). Alle anderen Besucher
 * sehen die normale Startseite. Reines CSS/JS-Overlay, Elementor-Daten bleiben
 * unangetastet. Datei loeschen = Vorschau weg.
 * Entwurfsdateien im Repo: drafts/startseite-2026-10-10/.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_HPV_TOKEN', 'v02dqoyjtv');

/** Vorschau-Modus: per Link (?sp_vorschau=TOKEN) einschalten, bleibt per Cookie 24 h beim Weiterklicken aktiv; ?sp_vorschau=aus beendet ihn. */
function sp_hpv_token_ok() {
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    if (isset($_GET['sp_vorschau'])) {
        $v = (string) $_GET['sp_vorschau'];
        if ($v === 'aus') {
            if (!headers_sent()) {
                setcookie('sp_vorschau', '', time() - 3600, '/', '', true, true);
            }
            return $ok = false;
        }
        if (hash_equals(SP_HPV_TOKEN, $v)) {
            if (!headers_sent()) {
                setcookie('sp_vorschau', $v, time() + DAY_IN_SECONDS, '/', '', true, true);
            }
            return $ok = true;
        }
    }
    return $ok = isset($_COOKIE['sp_vorschau']) && hash_equals(SP_HPV_TOKEN, (string) $_COOKIE['sp_vorschau']);
}
add_action('send_headers', 'sp_hpv_token_ok');

function sp_hpv_active() {
    return function_exists('sp_redesign_on') && sp_redesign_on('katalog') && is_front_page();
}

/** Bestseller fuer die Produkt-Reihe (Peptide + Pens, nach Verkaufszahl). */
function sp_hpv_products($ids = null) {
    $ids = $ids ?: [65, 68, 393, 428, 431, 71, 77, 434];
    $map = function_exists('sp_abo_picker_rating_map') ? sp_abo_picker_rating_map() : [];
    $qd = function_exists('sp_quantity_discount_product_ids') ? sp_quantity_discount_product_ids() : [];
    $out = [];
    foreach ($ids as $id) {
        $p = wc_get_product($id);
        if (!$p || $p->get_status() !== 'publish') {
            continue;
        }
        $var = $p->is_type('variable');
        $vars = [];
        if ($var) {
            foreach ($p->get_children() as $vid) {
                $v = wc_get_product($vid);
                if (!$v || !$v->is_purchasable() || !$v->is_in_stock()) {
                    continue;
                }
                $vars[] = [
                    'id' => $vid,
                    'l' => implode(' / ', array_values($v->get_attributes())),
                    'p' => (float) wc_get_price_to_display($v),
                    'r' => $v->is_on_sale() ? (float) wc_get_price_to_display($v, ['price' => $v->get_regular_price()]) : null,
                ];
            }
            usort($vars, function ($a, $b) { return $a['p'] <=> $b['p']; });
        }
        $vol = '';
        if (!$var) {
            $vol = $p->get_attribute('volumen');
        }
        $on_sale = $p->is_on_sale();
        $out[] = [
            'id' => $id, 'n' => $p->get_name(), 'u' => get_permalink($id), 'i' => wp_get_attachment_image_url($p->get_image_id(), 'medium_large'),
            'p' => $var ? ($vars ? $vars[0]['p'] : 0) : (float) wc_get_price_to_display($p),
            'r' => (!$var && $on_sale) ? (float) wc_get_price_to_display($p, ['price' => $p->get_regular_price()]) : null,
            'vars' => $vars, 'vol' => $vol,
            'qd' => in_array($id, $qd, true) && !$on_sale,
            'rt' => isset($map[$id]['num']) ? $map[$id]['num'] : 4.8, 'rc' => isset($map[$id]['count']) ? $map[$id]['count'] : 0,
            'pen' => stripos($p->get_name(), 'Pen') !== false,
            'pre' => function_exists('sp_preorder_product_ids') && in_array($id, sp_preorder_product_ids(), true),
        ];
    }
    return $out;
}

/** Forschungsbereiche mit einem typischen Produktbild. */
function sp_hpv_cats() {
    $pick = ['fettverlust' => 65, 'regeneration-heilung' => 428, 'fokus' => 434, 'energie' => 71, 'aesthetik' => 68, 'zubehoer' => 74];
    $out = [];
    foreach ($pick as $slug => $pid) {
        $t = get_term_by('slug', $slug, 'product_cat');
        $p = wc_get_product($pid);
        if (!$t || !$t->count) {
            continue;
        }
        $out[] = ['n' => html_entity_decode($t->name), 'u' => get_term_link($t), 'c' => (int) $t->count, 'i' => $p ? wp_get_attachment_image_url($p->get_image_id(), 'medium_large') : '', 'w' => false];
    }
    return $out;
}

/** Kombi-Sets (nur lieferbare Produkte; keine Set-Rabatte). Optionale Varianten-Auswahl ('opts'). */
function sp_hpv_set_item($id) {
    $p = wc_get_product($id);
    if (!$p || !$p->is_purchasable() || !$p->is_in_stock()) {
        return null;
    }
    $parent = $p->is_type('variation') ? wc_get_product($p->get_parent_id()) : $p;
    $img = $p->get_image_id() ? $p->get_image_id() : $parent->get_image_id();
    $name = $parent->get_name() . ($p->is_type('variation') ? ' ' . implode(' ', array_values($p->get_attributes())) : '');
    return ['id' => $id, 'n' => $name, 'p' => (float) wc_get_price_to_display($p), 'i' => wp_get_attachment_image_url($img, 'thumbnail'), 'w' => in_array($parent->get_id(), [80, 393, 395, 396, 908, 745], true)];
}

/* Varianten eines Produkts als Set-Optionen: je Variante (lieferbar) eine Option mit Mengen-Label */
function sp_hpv_set_vars($pid, $g, $extra) {
    $p = wc_get_product($pid);
    if (!$p) {
        return [];
    }
    $out = [];
    $kids = $p->is_type('variable') ? $p->get_children() : [$pid];
    foreach ($kids as $vid) {
        $v = wc_get_product($vid);
        if (!$v || !$v->is_purchasable() || !$v->is_in_stock()) {
            continue;
        }
        $l = $v->is_type('variation') ? preg_replace('/(\d)\s*mg$/i', '$1 mg', implode(' ', array_values($v->get_attributes()))) : '';
        $out[] = ['g' => $g, 'l' => $l, 'ids' => array_merge([$vid], $extra)];
    }
    return $out;
}

function sp_hpv_sets() {
    $defs = [
        ['n' => 'Retatrutide Starter', 'd' => 'Alles für den Start: Retatrutide plus Bac Water zum Anmischen und Spritzen zum genauen Dosieren.', 'opts' => sp_hpv_set_vars(65, '', [74, 80])],
        ['n' => 'Pen-Set', 'd' => 'Ein vorgefüllter Peptrium-Pen mit passenden Pen Nadeln – ganz ohne Anmischen. Wähle Pen und Menge:', 'opts' => array_merge(sp_hpv_set_vars(393, 'Retatrutide', [908]), sp_hpv_set_vars(395, 'GHK-Cu', [908]), sp_hpv_set_vars(396, 'Mots-C', [908]))],
        ['n' => 'GHK-Cu Komplett', 'd' => 'GHK-Cu 50 mg mit Bac Water und Spritzen – alles für deine Forschung in einem Paket.', 'opts' => [['l' => '', 'ids' => [68, 74, 80]]]],
        ['n' => 'Energie & Ästhetik', 'd' => 'Das oft zusammen gekaufte Duo: Mots-C und GHK-Cu, dazu Bac Water zum Anmischen.', 'opts' => [['l' => '', 'ids' => [71, 68, 74]]]],
    ];
    $out = [];
    foreach ($defs as $d) {
        $opts = [];
        foreach ($d['opts'] as $o) {
            $items = [];
            $t = 0;
            foreach ($o['ids'] as $id) {
                $it = sp_hpv_set_item($id);
                if (!$it) {
                    continue 2;
                }
                $items[] = $it;
                $t += $it['p'];
            }
            $opts[] = ['g' => $o['g'] ?? '', 'l' => $o['l'], 'items' => $items, 't' => round($t, 2)];
        }
        if ($opts) {
            $out[] = ['n' => $d['n'], 'd' => $d['d'], 'opts' => $opts, 'sel' => 0];
        }
    }
    return $out;
}

add_action('wp_head', function () {
    if (!sp_hpv_active()) {
        return;
    }
    if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')) {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
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
body.home .elementor-211>#sp-hp-sets{order:6}
body.home .elementor-211>[data-id="e761cf4"]{order:7}
body.home .elementor-211>[data-id="hiwk001"]{order:8}
body.home .elementor-211>[data-id="77e913a"]{order:9}
body.home .elementor-211>#sp-hp-faq{order:10}
body.home .elementor-211>#sp-nlh{order:11}
body.home .elementor-211>#sp-hp-info{order:12}
body.home .elementor-211>[data-id="045a884"],body.home .elementor-211>[data-id="a4ab146"]{display:none!important}
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

/* Haekchen-Liste: auf dem Handy sauber untereinander */
@media(max-width:900px){.sp-trust{display:flex;flex-direction:column;align-items:flex-start;width:max-content;max-width:100%;margin:22px auto 0;gap:11px}.sp-trust li{font-size:13px}}
/* Mehr ueber Peptrium */
#sp-hp-info .sp-about{max-width:1000px;margin:26px auto 4px;text-align:left}
#sp-hp-info .sp-about h2{font:700 clamp(22px,3vw,28px)/1.25 Sora,sans-serif;letter-spacing:-.01em;color:#0D0F12;margin:0 0 10px;text-align:center}
#sp-hp-info .sp-about .lead{font-size:14.5px;line-height:1.65;color:#4A5058;margin:0 auto 20px;max-width:640px;text-align:center}
#sp-hp-info .sp-about .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
#sp-hp-info .sp-about .grid>div{background:#fff;border:1px solid #E3E6E9;border-radius:16px;padding:16px}
#sp-hp-info .sp-about h3{font:700 15px/1.3 Sora,sans-serif;color:#0D0F12;margin:0 0 6px}
#sp-hp-info .sp-about p{font-size:13.5px;line-height:1.6;color:#4A5058;margin:0}
#sp-hp-info .sp-about a{color:#0D0F12;font-weight:600;text-decoration:underline;text-underline-offset:2px}
@media(max-width:900px){#sp-hp-info .sp-about .grid{grid-template-columns:1fr}}
@media(max-width:900px){#sp-hp-prod .sp-trust{margin-left:auto!important;margin-right:auto!important;padding:0!important;display:flex!important;flex-direction:column!important;align-items:flex-start!important;width:-webkit-fit-content!important;width:fit-content!important}}

.sp-pc .bd.p{background:#F5A623;color:#0D0F12}
.sp-pc .bd.p+.bd{top:42px}
.sp-pc .pre-n{font-size:11px;color:#A86A00;text-align:center;margin-top:-2px}
.sp-set .vo{display:flex;flex-wrap:wrap;gap:6px}
.sp-set .vo button{height:30px;padding:0 12px;border-radius:9px;border:1px solid #D5D9DD;background:#fff;font:600 12.5px Sora,sans-serif;color:#3A4048;cursor:pointer}
.sp-set .vo button.on{background:#0D0F12;border-color:#0D0F12;color:#fff}
.sp-set .ch{display:flex;flex-direction:column;gap:8px}
.sp-set .ch:empty{display:none}
.sp-set .vm button{height:28px;padding:0 11px;border-radius:999px;font-size:12px;background:#F4F5F6;border-color:#E3E6E9}
.sp-set .vm button.on{background:#fff;border:1.5px solid #0D0F12;color:#0D0F12}
.sp-set .body{display:flex;flex-direction:column;gap:12px;flex:1}
/* 3 Schritte: Bilder kompakt auf dem Handy */
@media(max-width:767px){
 body.home .sp-hiw-step-img{display:block!important;width:100%!important;height:120px!important;object-fit:cover!important;border-radius:14px!important;margin:12px 0 0!important}
}

/* Karten-Badges ruhiger: ein Badge oben links, Vorbestellung dunkel mit Bernstein-Text */
.sp-pc .bd.p{background:rgba(13,15,18,.82)!important;color:#F5C26B!important;border:1px solid rgba(245,194,107,.45);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px)}
.sp-pc .pre-n{color:#8A6A2A!important}
.sp-pc .coa{font-size:10px!important;letter-spacing:.01em}

/* Laufband robust (iOS): erst laufen, wenn Tempo gesetzt; keine Zeichen-Beschraenkung, Karten eigene Ebene */
.sp-mq{contain:none!important}
.sp-mq .tr{animation-play-state:paused!important;will-change:auto!important}
.sp-mq .tr.go{animation-play-state:running!important}
.sp-mq:hover .tr.go,.sp-mq:active .tr.go{animation-play-state:paused!important}
.sp-mq .sp-rv{transform:translateZ(0);-webkit-transform:translateZ(0);backface-visibility:hidden;-webkit-backface-visibility:hidden}

/* Laufband v3 (JS, Karten einzeln) */
.sp-mq .tr.js{position:relative;display:block!important;width:auto!important;animation:none!important;transform:none!important}
.sp-mq .tr.js>.sp-rv{position:absolute;top:0;left:0;width:270px;will-change:transform}
@media(min-width:901px){.sp-mq .tr.js>.sp-rv{width:300px}}
.sp-mq .tr.js>.sp-rv{box-sizing:border-box}

/* 3 Schritte: kurzer Satz + groessere Bilder */
@media(max-width:767px){body.home .sp-hiw-step-img{width:72%!important;height:auto!important;aspect-ratio:282/190;object-fit:cover!important;margin-top:10px!important;border-radius:14px!important}body.home .sp-hiw-step-content p{margin:4px 0 0!important}}
.sp-set .vg{flex-wrap:nowrap!important;gap:6px}
.sp-set .vg button{flex:1 1 auto;padding:0 6px;font-size:11.5px;white-space:nowrap}
/* Desktop: 3 Schritte nebeneinander statt schmaler Spalte, Tools-Raster auf Seitenbreite */
@media(min-width:1024px){
body.home .elementor-211>[data-id="hiwk001"] .sp-hiw-steps{flex-direction:row!important;align-items:stretch!important;gap:28px!important;max-width:1100px!important;width:100%!important;margin-left:auto!important;margin-right:auto!important}
body.home .elementor-211>[data-id="hiwk001"] .sp-hiw-steps *{max-width:none}
body.home .elementor-211>[data-id="hiwk001"] .sp-hiw-track{display:none!important}
body.home .elementor-211>[data-id="hiwk001"] .sp-hiw-step{flex:1 1 0!important;flex-direction:column!important;align-items:flex-start!important;gap:14px!important;margin:0!important}
body.home .elementor-211>[data-id="hiwk001"] .sp-hiw-step-content{width:100%!important}
body.home .elementor-211>[data-id="hiwk001"] .sp-hiw-step-img{width:100%!important;height:auto!important;aspect-ratio:282/190;object-fit:cover}
body.home .elementor-211>[data-id="77e913a"] .sp-tools-grid{max-width:1140px!important;margin-left:auto!important;margin-right:auto!important}
body.home .elementor-211>[data-id="77e913a"] .sp-tool-card{display:flex!important;flex-direction:column!important}
body.home .elementor-211>[data-id="77e913a"] .sp-tool-card p{flex:0 0 auto}
body.home .elementor-211>[data-id="77e913a"] .sp-tool-cta{align-self:flex-start;margin-top:auto!important}
body.home .elementor-211>[data-id="77e913a"] .sp-tools-grid{align-items:stretch!important}
}
#sp-hpv-flag{position:fixed;left:12px;top:12px;z-index:99999;background:#FF8A5C;color:#0D0F12;font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.3);text-decoration:none!important}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    if (!sp_hpv_active()) {
        return;
    }
    ?>
<?php if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')): ?><a id="sp-hpv-flag" href="<?php echo esc_url(add_query_arg('sp_vorschau', 'aus', home_url('/'))); ?>">ENTWURF-VORSCHAU ✕</a><?php endif; ?>
<script id="sp-hpv-js">
window.SP_HP_PRODS=<?php echo wp_json_encode(sp_hpv_products()); ?>;
window.SP_HP_CATS=<?php echo wp_json_encode(sp_hpv_cats()); ?>;
window.SP_HP_SETS=<?php echo wp_json_encode(sp_hpv_sets()); ?>;
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
document.addEventListener('DOMContentLoaded',function(){
 var root=document.querySelector('body.home .elementor-211'); if(!root)return;
 function el(h){var d=document.createElement('div');d.innerHTML=h.trim();return d.firstChild;}
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 function stars(n){return '★★★★★'.slice(0,Math.round(n));}
 /* Produkte */
 var P=window.SP_HP_PRODS||[];
 var cart='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg>';
 var cards=P.map(function(p,i){
   var f0=(p.vars&&p.vars.length)?p.vars[0]:{p:p.p,r:p.r},dis=f0.r?Math.round((1-f0.p/f0.r)*100):0;
   var bd=p.pre?'<span class="bd p">Vorbestellung'+(dis?' · −'+dis+' %':'')+'</span>':(dis?'<span class="bd s">−'+dis+' %</span>':(i===0?'<span class="bd">★ Bestseller</span>':(p.pen?'<span class="bd n">Neu</span>':'')));
   var vo='';
   if(p.vars&&p.vars.length>1){vo='<div class="vo">'+p.vars.map(function(v,k){return '<button type="button" data-id="'+v.id+'" data-p="'+v.p+'" data-r="'+(v.r||'')+'"'+(k===0?' class="on"':'')+'>'+v.l+'</button>';}).join('')+'</div>';}
   else if(p.vars&&p.vars.length===1){vo='<div class="vo"><span>'+p.vars[0].l+'</span></div>';}
   else if(p.vol){vo='<div class="vo"><span>'+p.vol+'</span></div>';}
   var first=(p.vars&&p.vars.length)?p.vars[0]:{id:p.id,p:p.p,r:p.r};
   return '<div class="sp-pc'+(p.pen?' pen':'')+'" data-id="'+first.id+'"><a class="im" href="'+p.u+'"><img loading="lazy" src="'+p.i+'" alt="'+p.n+'">'+bd+'<span class="coa">✓ HPLC-verifiziert</span></a><div class="tx"><a href="'+p.u+'"><h3 class="nm">'+p.n+'</h3></a>'
     +'<div class="rt"><b>★★★★★</b>'+String(p.rt).replace('.',',')+' ('+p.rc+')</div>'+vo
     +(p.qd?'<div class="qd">ab 3 Stück −10 % Mengenrabatt</div>':'')
     +'<div class="pr"><strong>'+eur(first.p)+'</strong>'+(first.r?'<s>'+eur(first.r)+'</s>':'')+'<small>inkl. MwSt.</small></div>'
     +'<button type="button" class="add">'+cart+(p.pre?'Vorbestellen':'In den Warenkorb')+'</button>'+(p.pre?'<div class="pre-n">Lieferung, sobald neue Ware eintrifft</div>':'')+'</div></div>';
 }).join('');
 var prod=el('<section id="sp-hp-prod" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Bestseller</span><h2>Peptide in Forschungsqualität</h2><p class="sub">Jede Charge HPLC-geprüft, mit Analysezertifikat – Versand aus Deutschland.</p></div>'
  +'<div class="sp-rail">'+cards+'<a class="sp-pc all" href="/alle-produkte/"><div class="ar">→</div><b>Alle Produkte</b><span>Das ganze Sortiment ansehen</span></a></div>'
  +'<div class="sp-cats-h"><b>Nach Forschungsbereich</b><a href="/alle-produkte/">Alle →</a></div><div class="sp-cats">'+(window.SP_HP_CATS||[]).map(function(c){return '<a class="sp-cat'+(c.w?' wide':'')+'" href="'+c.u+'">'+(c.i?'<img loading="lazy" src="'+c.i+'" alt="">':'')+'<span>'+c.n+'<small>'+c.c+(c.c===1?' Produkt':' Produkte')+'</small></span></a>';}).join('')+'</div>'
  +'<ul class="sp-trust"><li>HPLC ≥ 99 % Reinheit</li><li>Analysezertifikat zu jeder Charge</li><li>Gratisversand ab 100 €</li><li>Neutrale, diskrete Verpackung</li></ul>'
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
 var h1=rv.filter(function(_,i){return i%2===0;}).slice(0,6),h2=rv.filter(function(_,i){return i%2===1;}).slice(0,6);
 function row(list,cls){return '<div class="sp-mq '+cls+'"><div class="tr">'+list.map(card).join('')+'</div></div>';}
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
 /* Kombi-Sets (mit optionaler Auswahl) */
 var S=window.SP_HP_SETS||[];
 if(S.length){
 var sets=el('<section id="sp-hp-sets" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Beliebte Kombinationen</span><h2>Passt zusammen.</h2><p class="sub">Sinnvoll kombiniert – mit einem Klick komplett im Warenkorb.</p></div><div class="sp-rail">'
  +S.map(function(s,i){return SPSETS.card(s,i,cart);}).join('')
  +'</div></div></section>');
 root.appendChild(sets);
 SPSETS.bind(sets,S,function(ids,b){var old=b.innerHTML,i=0,last=null;b.disabled=true;b.textContent='…';
   function next(){if(i>=ids.length){b.classList.add('ok');b.innerHTML='✓ Set hinzugefügt';if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[last&&last.fragments,last&&last.cart_hash,jQuery(b)]);setTimeout(function(){b.classList.remove('ok');b.innerHTML=old;b.disabled=false;},2400);return;}
     var fd=new FormData();fd.append('product_id',ids[i]);fd.append('quantity','1');
     fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){last=r;i++;next();}).catch(function(){b.innerHTML=old;b.disabled=false;});}
   next();});
 }
 /* FAQ */
 var F=[
  ['Wie schnell kommt meine Bestellung?','Nach Zahlungseingang ist dein Paket in der Regel innerhalb von <b>2 Werktagen</b> bei dir. Wir versenden mit DHL, die Sendungsnummer bekommst du per E-Mail. Ab 100 € Bestellwert ist der Versand gratis. Mehr unter <a href="/versand-lieferzeit/">Versand &amp; Lieferzeit</a>.'],
  ['Wie kann ich bezahlen?','Per Vorkasse (Überweisung), mit Kryptowährung oder mit deinem Peptrium-Guthaben. Bei Vorkasse verschicken wir, sobald deine Zahlung eingegangen ist.'],
  ['Ist die Verpackung neutral?','Ja. Wir verschicken neutral und unauffällig – von außen ist nicht zu erkennen, was im Paket ist.'],
  ['Gibt es Mengenrabatt oder Geschenke?','Bei ausgewählten Peptiden sparst du ab 3 Stück 10 %, ab 5 Stück 15 % und ab 10 Stück 20 %. Ab 200 € Bestellwert legen wir ein Injektionskit gratis dazu, ab 350 € GHK-Cu und ab 500 € Retatrutide.'],
  ['Wie funktioniert das Abo?','Du stellst einmal ein, was du regelmäßig brauchst. Ab der 2. Lieferung zahlst du dauerhaft 15 % weniger und kannst jederzeit pausieren oder kündigen. Alles dazu auf der Seite <a href="/abo-modell/">Abo-Modell</a>.'],
  ['Wofür sind die Produkte bestimmt?','Alle Produkte sind ausschließlich für die Laborforschung bestimmt – nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke.']
 ];
 var faq=el('<section id="sp-hp-faq" class="sp-hp-sec sp-hp-light"><div class="in"><div class="hd"><span class="sp-lbl lt">Häufige Fragen</span><h2>Gut zu wissen.</h2></div><div class="sp-faq">'+F.map(function(f,i){return '<details'+(i===0?' open':'')+'><summary>'+f[0]+'</summary><p>'+f[1]+'</p></details>';}).join('')+'</div></div></section>');
 root.appendChild(faq);

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
 /* Newsletter in die Seite holen */
 var nl=document.getElementById('sp-nlh'); if(nl) root.appendChild(nl);
 /* 3 Schritte: je ein kurzer Satz statt Fliesstext, Bild groesser */
 var ST=['Wähle aus unserem Sortiment an geprüften Forschungspeptiden.','Größe und Menge wählen – ab 3 Stück sparst du 10 %.','Per Vorkasse oder Krypto bezahlen – in 2 Werktagen bei dir.'];
 document.querySelectorAll('#sp-how-it-works .sp-hiw-step-content').forEach(function(c,i){var p=c.querySelector('p');if(p&&ST[i]){p.textContent=ST[i];p.style.display='';}});
 setTimeout(function(){window.dispatchEvent(new Event('resize'));window.dispatchEvent(new Event('scroll'));},60);
 /* Hinweis + Mehr ueber Peptrium (eigener, strukturierter Text) */
 var info=el('<div id="sp-hp-info"><div class="dis"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A5058" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div><br><button type="button" aria-expanded="false">Mehr über Peptrium <span>▾</span></button>'
  +'<div class="sp-about" hidden><h2>Peptide kaufen in Deutschland – bei Peptrium</h2>'
  +'<p class="lead">Peptrium ist ein Fachshop für hochreine Forschungspeptide. Wir verbinden geprüfte Qualität mit einfachem Bestellen, schnellem Versand und persönlichem Support.</p>'
  +'<div class="grid">'
  +'<div><h3>Geprüfte Qualität</h3><p>Unsere Peptide haben eine Reinheit von über 99 %, bestimmt per HPLC, die Identität wird per LC-MS bestätigt. Jede Charge ist eindeutig gekennzeichnet.</p></div>'
  +'<div><h3>Schnell &amp; diskret geliefert</h3><p>Versand aus Deutschland mit DHL – in der Regel innerhalb von 2 Werktagen nach Zahlungseingang. Neutral verpackt, ab 100 € versandkostenfrei.</p></div>'
  +'<div><h3>Breites Sortiment</h3><p>Peptide für die Forschungsbereiche <a href="/produktkategorie/fettverlust/">Stoffwechsel</a>, <a href="/produktkategorie/regeneration-heilung/">Regeneration</a>, <a href="/produktkategorie/fokus/">Fokus</a>, <a href="/produktkategorie/energie/">Energie</a> und <a href="/produktkategorie/aesthetik/">Ästhetik</a> – dazu passendes <a href="/produktkategorie/zubehoer/">Zubehör</a> und vorgefüllte Peptrium-Pens.</p></div>'
  +'<div><h3>Einfach bestellen</h3><p>Bezahlen per Vorkasse, Krypto oder Guthaben. Mengenrabatt ab 3 Stück, Geschenke ab 200 € und auf Wunsch ein Abo mit 15 % Ersparnis ab der 2. Lieferung.</p></div>'
  +'<div><h3>Kostenlose Tools</h3><p>Mit dem <a href="/peptid-rechner/">Peptid-Rechner</a>, dem <a href="/lagerungs-guide/">Lagerungs-Guide</a> und dem Peptid-Lexikon planst du deine Forschung genauer.</p></div>'
  +'<div><h3>Persönlicher Support</h3><p>Fragen zu Produkt oder Bestellung? Schreib uns über die <a href="/kontakt/">Kontaktseite</a> oder auf Telegram (<a href="https://t.me/peptrium">@peptrium</a>) – wir antworten schnell und auf Deutsch.</p></div>'
  +'</div></div></div>');
 root.appendChild(info);
 var about=info.querySelector('.sp-about');
 var ib=info.querySelector('button');ib.onclick=function(){var o=ib.classList.toggle('open');about.hidden=!o;ib.setAttribute('aria-expanded',o?'true':'false');};
});
</script>
    <?php
}, 99);
