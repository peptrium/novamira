<?php
/**
 * Layout-Verschiebungen beim Laden (CLS) vermeiden – 2026-10-22/23.
 * 1) Menüleiste: sp-top-banner.php setzt per JS `top` des fixierten Headers und das
 *    body-padding erst nach dem Laden → Header sprang von 0 auf 32 px. Hier dieselben Werte
 *    schon per CSS (gemessen: Handy Banner 32 / Header 85 → 117 px; Desktop 38 + 20 Abstand /
 *    Header 116 → 174 px). Das JS überschreibt weiterhin mit den exakten Werten.
 *    Startseite nicht (dort eigenes Verhalten mit transparentem Header).
 * 2) Kategorie-/Sammelseiten: #sp-cat wird per JS gebaut; bis dahin stand der Footer im Bild
 *    und wurde dann weggeschoben → #content bekommt vorab Mindesthöhe.
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
    if (is_admin()) return;
    ?>
<style id="sp-cls-fixes">
@media (max-width:767px){body:not(.home) .elementor-location-header{top:32px}body:not(.home){padding-top:117px}}
@media (min-width:1025px){body:not(.home) .elementor-location-header{top:58px}body:not(.home){padding-top:174px}}
/* Startseite: sp-home-preview sortiert die Sektionen erst bei DOMContentLoaded um (Bestseller vor
   Transparenz/Pen) → bis dahin alles unter dem Hero unsichtbar halten (Platz bleibt, kein Springen
   im Bild). Sicherheitsnetz: nach 5 s wird es auf jeden Fall sichtbar. */
html:not(.sp-hp-ready) body.home .elementor-211>*:not([data-id="shbn001"]){visibility:hidden;animation:sp-hp-show 0s 5s forwards}
@keyframes sp-hp-show{to{visibility:visible}}
/* Produktseite: pp.js gibt dem Bild-Container erst spät .pp-img (padding 0) → das Produktbild
   wuchs nachträglich (LCP erst bei ~7,7 s). Gleiches Padding schon vorab per CSS. */
/* Struktur-Selektor statt :has(#rx-buybox): die Kaufbox steht im HTML NACH dem Bild, beim ersten Zeichnen griff die Regel noch nicht. */
body.single-product [data-elementor-type="product"]>.e-con:first-child>.e-con-inner>.e-con:first-child:has(>.elementor-widget-image){padding:0!important}
/* pp.js setzt .pp-main (14 px Abstand oben, Hintergrund, gap 0) erst nach ~4 s → Produktbild rutschte
   nach unten und wurde als LCP neu gewertet. Gleiche Werte vorab (Handy). */
@media (max-width:900px){body.single-product [data-elementor-type="product"]>.e-con:first-child{padding-top:14px!important;background:linear-gradient(180deg,#F4F5F6 0,#FFFFFF 520px)!important}body.single-product [data-elementor-type="product"]>.e-con:first-child>.e-con-inner{gap:0!important}}
body.tax-product_cat #content,body.post-type-archive-product #content,body.page-id-325 #content,body.page-id-397 #content,body.page-id-529 #content{min-height:100vh}
</style>
    <?php
}, 1);

/* Startseite: Klasse setzen, NACHDEM sp-home-preview umsortiert hat (sein DOMContentLoaded-Listener
 * ist früher registriert und läuft daher zuerst). */
add_action('wp_footer', function () {
    if (!is_front_page()) return;
    echo "<script id=\"sp-hp-ready\">document.addEventListener('DOMContentLoaded',function(){requestAnimationFrame(function(){document.documentElement.classList.add('sp-hp-ready');});});if(document.readyState!=='loading'){document.documentElement.classList.add('sp-hp-ready');}</script>\n";
}, 120);
