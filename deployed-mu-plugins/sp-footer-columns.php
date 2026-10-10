<?php
/**
 * Plugin Name: SP Footer-Spalten
 * Description: Footer-Linkspalten (Shop / Unternehmen / Rechtliches) als zentrierte 3er-Reihe mit feiner Linie unter der Ueberschrift, auch auf dem Handy (2026-10-10).
 *
 * Reines CSS/JS-Overlay ueber dem Elementor-Footer (Template 317, Container 242bf27,
 * Spalten-Markup .sp-ftcol-h / .sp-ftcol-links). Auf dem Handy (<=767px) werden lange
 * Linknamen per JS durch Kurzformen ersetzt (beide Texte im Link, CSS schaltet um),
 * damit jede Zeile einzeilig bleibt; Desktop zeigt die vollen Namen.
 * Unten extra Abstand, damit Chat-/Warenkorb-Button nichts verdecken.
 * Datei loeschen = alter Footer.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }
    ?>
<style id="sp-footer-columns">
/* Footer-Spalten: 3er-Reihe mit eleganter Linie unter der Ueberschrift */
.elementor-location-footer .elementor-element-242bf27{display:grid!important;grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr) minmax(0,1.05fr);column-gap:clamp(14px,4vw,64px);row-gap:0;width:100%;align-items:start;}
.elementor-location-footer .elementor-element-242bf27>.e-con{width:auto!important;max-width:none!important;min-width:0;padding:0!important;margin:0!important;}
.elementor-location-footer .elementor-element-ftrowfix1{display:none!important;}
.elementor-location-footer .sp-ftcol-h{display:block;font-size:15px;font-weight:600;letter-spacing:.01em;margin:0 0 16px;padding-bottom:12px;}
.elementor-location-footer .sp-ftcol-links{gap:11px;}
.elementor-location-footer .sp-ftcol-links a{line-height:1.35;transition:color .2s;}
@media (max-width:767px){
 .elementor-location-footer .elementor-element-242bf27{column-gap:12px;padding-bottom:64px;}
 .elementor-location-footer .sp-ftcol-h{font-size:13px;margin-bottom:14px;padding-bottom:10px;}
 .elementor-location-footer .sp-ftcol-links{gap:10px;}
 .elementor-location-footer .sp-ftcol-links a{font-size:12.5px;white-space:nowrap;}
}

.sp-ftcol-links .sp-ft-s{display:none;}
@media (max-width:767px){.sp-ftcol-links .sp-ft-l{display:none;}.sp-ftcol-links .sp-ft-s{display:inline;}
 .elementor-location-footer .sp-ftcol-links a{font-size:13px;}
 .elementor-location-footer .sp-ftcol-h{font-size:14px;}}
.elementor-location-footer .sp-ftcol-h::after{width:100%;height:1px;border-radius:0;background:linear-gradient(90deg,rgba(230,233,236,0),rgba(230,233,236,.85) 50%,rgba(230,233,236,0));}

.elementor-location-footer .elementor-element-242bf27>.e-con,.elementor-location-footer .elementor-element-242bf27 .elementor-widget-html{text-align:center;}
.elementor-location-footer .sp-ftcol-h{text-align:center;}
.elementor-location-footer .sp-ftcol-links{align-items:center;text-align:center;}
.elementor-location-footer .elementor-element-cp001,.elementor-location-footer .elementor-element-cp001 p{text-align:center!important;}
.elementor-location-footer .elementor-element-90fdc8b{align-items:center!important;justify-content:center!important;}
.elementor-location-footer .elementor-element-cp001{width:100%!important;max-width:100%!important;}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    ?>
<script id="sp-footer-columns-js">
(function(){var m={'Alle Peptrium-Pens':'Peptrium-Pens','Retatrutide-Dosierung':'Reta-Dosierung','Partner-Programm':'Partner werden','Versand & Lieferzeit':'Versand','Forschungsnutzung':'Forschung','Rückgabe & Widerruf':'Widerruf'};
document.querySelectorAll('.sp-ftcol-links a').forEach(function(a){var t=a.textContent.trim();if(m[t]){a.innerHTML='<span class="sp-ft-l"></span><span class="sp-ft-s"></span>';a.firstChild.textContent=t;a.lastChild.textContent=m[t];}});})();
</script>
    <?php
}, 99);
