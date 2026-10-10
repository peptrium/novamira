<?php
/**
 * Plugin Name: SP Design-System (Redesign-Vorschau)
 * Description: Gemeinsame Design-Ebene fuer das Shop-Redesign - aktiv im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php) auf allen Seiten (2026-10-10).
 *
 * - Header: Aktiv-/Fokus-Zustand der Kopf-Knoepfe (Suche, Konto, Menue) ohne Astra-Blau.
 * - Menue (Elementor-Header 316): Gruppe "Nach Ziel suchen" -> "Forschungsbereiche" mit den 6 Produktkategorien.
 * - Die 13 Ziel-Seiten (Elementor, mit fest eingetragenen, teils veralteten Preisen) leiten auf die passende
 *   Produktkategorie weiter (Vorschau: 302; beim Livegang 301).
 * - 404-Seite im neuen Design (Suche, Links, Forschungsbereiche).
 * Datei loeschen = alles wieder wie vorher.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_ds_on() {
    return !is_admin() && function_exists('sp_redesign_on') && sp_redesign_on('basis');
}

/** Ziel-Seite -> Produktkategorie. */
function sp_ds_goal_map() {
    return [
        'gewebe-verletzungsregeneration' => 'regeneration-heilung', 'entzuendung-darmgesundheit' => 'regeneration-heilung',
        'immunmodulation' => 'regeneration-heilung', 'regeneration-heilung' => 'regeneration-heilung',
        'mitochondrien-energie' => 'energie', 'energie-zellschutz' => 'energie',
        'zellschutz-anti-aging' => 'aesthetik', 'haut-kollagen' => 'aesthetik', 'aesthetik-mehr' => 'aesthetik', 'pigmentierung-braeunung' => 'aesthetik',
        'konzentration-kognitive-leistung' => 'fokus', 'stress-angstregulation' => 'fokus', 'schlaf-erholung' => 'fokus',
        'wachstumshormon-erholung' => 'fokus', 'fokus-schlaf' => 'fokus',
        'appetitkontrolle-gewichtsmanagement' => 'fettverlust', 'fettverbrennung-stoffwechselfunktion' => 'fettverlust', 'gewicht-metabolismus' => 'fettverlust',
    ];
}

add_action('template_redirect', function () {
    if (!sp_ds_on() || !is_page()) {
        return;
    }
    $slug = get_post_field('post_name', get_queried_object_id());
    $map = sp_ds_goal_map();
    if (isset($map[$slug])) {
        $t = get_term_by('slug', $map[$slug], 'product_cat');
        if ($t) {
            wp_safe_redirect(get_term_link($t), function_exists('sp_redesign_live') && sp_redesign_live('basis') ? 301 : 302);
            exit;
        }
    }
}, 5);

function sp_ds_menu_cats() {
    $ex = ['fettverlust' => 'Retatrutide, Tesamorelin', 'regeneration-heilung' => 'BPC-157, TB-500, KPV', 'fokus' => 'Semax, Selank, CJC-1295', 'energie' => 'Mots-C', 'aesthetik' => 'GHK-Cu'];
    $out = [];
    foreach ($ex as $slug => $e) {
        $t = get_term_by('slug', $slug, 'product_cat');
        if ($t && $t->count) {
            $out[] = ['n' => html_entity_decode($t->name), 'u' => get_term_link($t), 'e' => 'z. B. ' . $e];
        }
    }
    return $out;
}

add_action('wp_head', function () {
    if (!sp_ds_on()) {
        return;
    }
    ?>
<style id="sp-ds-css">
/* Footer-Banner "Hochreine Peptide fuer deine Forschung" ueberall weg (Nutzerwunsch) */
.elementor-location-footer .elementor-element-97108f0{display:none!important}
/* Header: kein Astra-Blau bei Fokus/aktiv */
.sp-hb-right button,.sp-hb-right button:hover,.sp-hb-right button:focus,.sp-hb-right button:active,.sp-hb-right button[aria-expanded="true"]{background:rgba(255,255,255,.08)!important;color:#fff!important;outline:none!important;box-shadow:none!important}
.sp-hb-right button[aria-expanded="true"],.sp-hb-right button:active{background:rgba(255,255,255,.18)!important;box-shadow:inset 0 0 0 1px rgba(255,255,255,.25)!important}
.sp-hb-right button:focus-visible{box-shadow:0 0 0 2px rgba(255,255,255,.5)!important}
/* Menue: Forschungsbereiche als direkte Links */
.sp-mm-sub a.sp-ds-cat{display:flex;align-items:center;gap:12px;padding:11px 12px;border-radius:12px;text-decoration:none!important;color:#fff!important}
.sp-mm-sub a.sp-ds-cat:hover{background:rgba(255,255,255,.06)}
.sp-mm-sub a.sp-ds-cat b{display:block;font:600 14px/1.25 Sora,sans-serif}
.sp-mm-sub a.sp-ds-cat small{display:block;font-size:11.5px;color:#8A9099;margin-top:2px}
.sp-mm-sub a.sp-ds-cat i{flex:0 0 8px;width:8px;height:8px;border-radius:50%;background:#C7CCD1;box-shadow:0 0 8px rgba(199,204,209,.6)}
.sp-mm-sub a.sp-ds-cat.all{border-top:1px solid rgba(255,255,255,.08);margin-top:4px;padding-top:13px}
/* 404 */
body.sp-ds-404 #content>.ast-container{display:none!important}
#sp-404{font-family:Sora,sans-serif}
#sp-404 .hero{background:radial-gradient(90% 120% at 85% 0%,rgba(199,204,209,.14),transparent 60%),linear-gradient(160deg,#0D0F12 0%,#1E2226 100%);color:#fff;padding:34px 20px 36px;border-radius:0 0 26px 26px;text-align:center}
#sp-404 .code{font:800 72px/1 Sora,sans-serif;letter-spacing:-.04em;background:linear-gradient(100deg,#8E969F,#F4F6F8 50%,#A9B0B8);-webkit-background-clip:text;background-clip:text;color:transparent;margin:0 0 8px}
#sp-404 h1{font:700 24px/1.25 Sora,sans-serif;margin:0 0 8px;color:#fff}
#sp-404 p{font-size:14px;line-height:1.6;color:#B9BEC5;margin:0 auto 18px;max-width:420px}
#sp-404 form{display:flex;gap:6px;max-width:420px;margin:0 auto;padding:5px;border-radius:14px;background:#14171B;border:1px solid #2C3137}
#sp-404 input{flex:1;min-width:0;height:44px;border:0;background:transparent;color:#fff;padding:0 12px;font:500 15px Sora,sans-serif;outline:none}
#sp-404 form button{height:44px;padding:0 16px;border:0;border-radius:10px;background:linear-gradient(120deg,#C7CCD1,#fff 50%,#C7CCD1);color:#0D0F12;font:700 14px Sora,sans-serif}
#sp-404 .body{max-width:900px;margin:0 auto;padding:22px 16px 40px}
#sp-404 .btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin:0 0 24px}
#sp-404 .btns a{height:44px;display:inline-flex;align-items:center;padding:0 18px;border-radius:999px;border:1px solid #DDE1E5;font:700 13.5px Sora,sans-serif;color:#0D0F12!important;text-decoration:none!important;background:#fff}
#sp-404 .btns a.p{background:#0D0F12;border-color:#0D0F12;color:#fff!important}
#sp-404 h2{font:700 17px Sora,sans-serif;margin:0 0 12px;color:#0D0F12}
#sp-404 .cats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
@media(min-width:700px){#sp-404 .cats{grid-template-columns:repeat(5,minmax(0,1fr))}}
#sp-404 .cats a{padding:14px;border-radius:14px;background:#F6F7F8;border:1px solid #E3E6E9;text-decoration:none!important;color:#0D0F12!important}
#sp-404 .cats b{display:block;font-size:14px}#sp-404 .cats small{font-size:11.5px;color:#5A6068}
</style>
    <?php
}, 98);

add_action('wp_footer', function () {
    if (!sp_ds_on()) {
        return;
    }
    $cats = sp_ds_menu_cats();
    ?>
<script id="sp-ds-js">
(function(){
 var C=<?php echo wp_json_encode($cats); ?>;
 function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
 /* Menue: "Nach Ziel suchen" -> Forschungsbereiche */
 function menu(){document.querySelectorAll('.sp-mm-toggle').forEach(function(t){var l=t.querySelector('.sp-mm-toggle-label');if(!l||!/Nach Ziel suchen/.test(l.textContent))return;
   var ic=l.querySelector('.sp-mm-item-ic');l.innerHTML='';if(ic)l.appendChild(ic);l.appendChild(document.createTextNode('Forschungsbereiche'));
   var sub=t.parentNode.querySelector('.sp-mm-sub');if(!sub)return;
   sub.innerHTML=C.map(function(c){return '<a class="sp-ds-cat" href="'+c.u+'"><i></i><span><b>'+esc(c.n)+'</b><small>'+esc(c.e)+'</small></span></a>';}).join('')+'<a class="sp-ds-cat all" href="/alle-produkte/"><i></i><span><b>Alle Produkte</b><small>Das komplette Sortiment</small></span></a>';});}
 /* 404 */
 function nf(){if(!document.body.classList.contains('error404'))return;var host=document.getElementById('content');if(!host)return;document.body.classList.add('sp-ds-404');
   var d=document.createElement('div');d.id='sp-404';
   d.innerHTML='<section class="hero"><div class="code">404</div><h1>Diese Seite gibt es nicht.</h1><p>Vielleicht hat sich der Link geändert. Such einfach nach einem Produkt oder starte bei unseren Forschungsbereichen.</p><form action="/" method="get"><input type="search" name="s" placeholder="Produkt suchen, z. B. Retatrutide" aria-label="Suche"><button type="submit">Suchen</button></form></section>'
    +'<div class="body"><div class="btns"><a class="p" href="/">Zur Startseite</a><a href="/alle-produkte/">Alle Produkte</a><a href="/kontakt/">Kontakt</a></div><h2>Forschungsbereiche</h2><div class="cats">'+C.map(function(c){return '<a href="'+c.u+'"><b>'+esc(c.n)+'</b><small>'+esc(c.e)+'</small></a>';}).join('')+'</div></div>';
   host.insertBefore(d,host.firstChild);
   d.querySelector('form').addEventListener('submit',function(e){e.preventDefault();var q=this.s.value.trim();var b=document.getElementById('sp-search-btn');if(!b)return;b.click();setTimeout(function(){var i=document.querySelector('#sp-search-panel input');if(i){i.value=q;i.dispatchEvent(new Event('input',{bubbles:true}));i.focus();}},250);});}
 if(document.readyState!=='loading'){menu();nf();}else document.addEventListener('DOMContentLoaded',function(){menu();nf();});
})();
</script>
    <?php
}, 97);

/* Sitemap (WP-Core): sobald "basis" live ist, die weitergeleiteten alten Ziel-Seiten, die alte
   Pen-Seite 394 und reine Funktionsseiten (Warenkorb, Kasse, Konto, Passwort) nicht mehr melden. */
add_filter('wp_sitemaps_posts_query_args', function ($args, $post_type) {
    if ($post_type !== 'page' || !function_exists('sp_redesign_live') || !sp_redesign_live('basis')) {
        return $args;
    }
    $ids = [394, 623];
    foreach (['cart', 'checkout', 'myaccount'] as $p) {
        $ids[] = (int) wc_get_page_id($p);
    }
    foreach (array_keys(sp_ds_goal_map()) as $slug) {
        $pg = get_page_by_path($slug);
        if ($pg) {
            $ids[] = (int) $pg->ID;
        }
    }
    $args['post__not_in'] = array_values(array_unique(array_merge($args['post__not_in'] ?? [], array_filter($ids))));
    return $args;
}, 10, 2);
