<?php
/**
 * Plugin Name: SP Produktseite – Abschnitt "Über das Produkt"
 * Description: Zeigt die WooCommerce-Produktbeschreibung (Feld "Beschreibung") als eigenen Abschnitt auf jeder Produktseite.
 *
 * Hintergrund (2026-10-11): Die Elementor-Produktvorlagen zeigen die WooCommerce-Beschreibung nirgends an –
 * Google sah sie nie. Die Texte wurden neu geschrieben (Forschungsstil, du-Form, keine Wirkungs-/Dosierangaben,
 * keine COA-Versprechen; alte Texte in Postmeta _sp_desc_backup_20261011) und erscheinen jetzt hier.
 * Neue Produkte: einfach das Feld "Beschreibung" im Produkt ausfuellen – der Abschnitt erscheint automatisch.
 * Server-seitig im HTML (fuer Google), per JS in die Produktseite (.pp-root, CSS-order 3 (nur ganze Zahlen gueltig! wie #sp-pp-sets, steht im DOM danach) = nach "Passt dazu",
 * vor dem Lexikon) verschoben. Laengere Texte sind eingeklappt ("Weiterlesen"), bleiben aber im HTML.
 * Gate: sp_redesign_on('katalog'). Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    if (!function_exists('is_product') || !is_product() || !function_exists('sp_redesign_on') || !sp_redesign_on('katalog')) {
        return;
    }
    $p = wc_get_product(get_queried_object_id());
    if (!$p || $p->get_catalog_visibility() === 'hidden') {
        return;
    }
    $html = trim((string) get_post_field('post_content', $p->get_id()));
    if ($html === '' || mb_strlen(wp_strip_all_tags($html)) < 80) {
        return;
    }
    $html = wpautop(wp_kses_post($html));
    $name = $p->get_name();
    ?>
<section id="sp-pp-about" aria-labelledby="sp-pp-about-h">
  <div class="in">
    <span class="ey">Produktinfo</span>
    <h2 id="sp-pp-about-h">Über <?php echo esc_html($name); ?></h2>
    <div class="tx" id="sp-pp-about-tx"><?php echo $html; // phpcs:ignore ?></div>
    <button type="button" class="more" id="sp-pp-about-more" aria-expanded="false" aria-controls="sp-pp-about-tx">Weiterlesen</button>
  </div>
</section>
<style id="sp-pp-about-css">
body.single-product .pp-root>#sp-pp-about,#sp-pp-about{order:3!important;margin:28px 0;box-sizing:border-box}
#sp-pp-about .in{background:#F4F5F6;border:1px solid #E3E6E9;border-radius:24px;padding:28px 20px 22px;max-width:1240px;margin:0 auto;box-sizing:border-box;font-family:Inter,system-ui,sans-serif;color:#2B3036}
#sp-pp-about .ey{display:inline-block;font:700 11px/1 Sora,sans-serif;letter-spacing:.12em;text-transform:uppercase;color:#6B727A;margin-bottom:10px}
#sp-pp-about h2{font:800 24px/1.2 Sora,sans-serif;color:#0D0F12;margin:0 0 14px;letter-spacing:-.01em}
#sp-pp-about h3{font:700 16.5px/1.3 Sora,sans-serif;color:#0D0F12;margin:20px 0 6px}
#sp-pp-about p{font-size:15px;line-height:1.7;margin:0 0 10px;color:#3A4047}
#sp-pp-about a{color:#0D0F12;font-weight:600;text-decoration:underline;text-underline-offset:2px}
#sp-pp-about .sp-ab-note{margin-top:16px;padding:12px 14px;border-radius:12px;background:#fff;border:1px solid #E3E6E9;font-size:13px;color:#4B5157}
#sp-pp-about .tx{position:relative}
#sp-pp-about.clip .tx{max-height:300px;overflow:hidden}
#sp-pp-about.clip .tx:after{content:'';position:absolute;left:0;right:0;bottom:0;height:90px;background:linear-gradient(rgba(244,245,246,0),#F4F5F6)}
#sp-pp-about .more{display:none;margin-top:10px;background:#0D0F12!important;color:#fff!important;border:0!important;border-radius:999px!important;padding:11px 20px!important;font:700 14px/1 Sora,sans-serif!important;box-shadow:none!important;cursor:pointer}
#sp-pp-about.clip .more,#sp-pp-about.open .more{display:inline-block}
#sp-pp-about .more:focus,#sp-pp-about .more:active{background:#0D0F12!important;color:#fff!important;outline:none}
@media(min-width:1024px){#sp-pp-about .in{padding:44px 56px 36px}#sp-pp-about .tx{columns:2;column-gap:48px}#sp-pp-about.clip .tx{columns:1}#sp-pp-about h3{break-after:avoid}#sp-pp-about h2{font-size:30px}}
</style>
<script id="sp-pp-about-js">
(function(){
 var s=document.getElementById('sp-pp-about');if(!s)return;
 var tx=document.getElementById('sp-pp-about-tx'),b=document.getElementById('sp-pp-about-more');
 function place(){var root=document.querySelector('.pp-root');if(root){root.appendChild(s);return true;}return false;}
 function clip(){if(tx.scrollHeight>380&&!s.classList.contains('open'))s.classList.add('clip');}
 b.addEventListener('click',function(){var o=s.classList.toggle('open');s.classList.toggle('clip',!o);b.textContent=o?'Weniger anzeigen':'Weiterlesen';b.setAttribute('aria-expanded',o?'true':'false');if(!o)s.scrollIntoView({block:'start',behavior:'smooth'});});
 var n=0;(function t(){if(place()||n++>40){clip();return;}setTimeout(t,100);})();
})();
</script>
    <?php
}, 60);
