<?php
/**
 * Plugin Name: SP Inhaltsseiten-Vorschau
 * Description: Phase 5 des Redesigns (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * - /peptrium-pen/ (Seite 394, verwaist, veralteter Preis "ab 150,00 €") -> /alle-peptrium-pens/ (302, Go-live 301)
 * - Lagerungs-Guide (322): erstes Peptid vorauswaehlen, damit kein ~750px leerer Bereich erscheint
 * - Cookie-Richtlinie (596, Real Cookie Banner, kein Elementor): Optik wie die anderen Rechtstexte
 * Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_cnp_active() {
    return function_exists('sp_hpv_token_ok') && sp_hpv_token_ok();
}

add_action('template_redirect', function () {
    if (sp_cnp_active() && is_page(394)) {
        wp_safe_redirect(home_url('/alle-peptrium-pens/'), 302);
        exit;
    }
}, 5);

add_action('wp_footer', function () {
    if (!sp_cnp_active() || !is_page([322, 596])) {
        return;
    }
    if (is_page(322)) {
        ?>
<script id="sp-cnp-lg">
(function(){
 function go(){var s=document.getElementById('sp-lg-peptide-select');if(!s||s.options.length<2)return false;
   if(!s.value){var o=[].filter.call(s.options,function(x){return x.value&&/retatrutide/i.test(x.textContent);})[0]||[].filter.call(s.options,function(x){return x.value;})[0];
     if(o){s.value=o.value;s.dispatchEvent(new Event('change',{bubbles:true}));}}return true;}
 if(!go()){var n=0,t=setInterval(function(){if(go()||++n>40)clearInterval(t);},150);}
})();
</script>
        <?php
        return;
    }
    ?>
<style id="sp-cnp-cookie">
body.page-id-596 .entry-content{max-width:720px;margin:0 auto;font-family:Sora,sans-serif;font-size:14.5px;line-height:1.7;color:#3A4048}
body.page-id-596 .entry-header{max-width:720px;margin:0 auto 6px;text-align:left}
body.page-id-596 .entry-title{font:800 28px/1.2 Sora,sans-serif!important;color:#0D0F12!important;letter-spacing:-.01em}
body.page-id-596 .entry-content h2,body.page-id-596 .entry-content h3{font:800 18px/1.3 Sora,sans-serif!important;color:#0D0F12!important;margin:28px 0 8px!important}
body.page-id-596 .entry-content h3{font-size:15.5px!important}
body.page-id-596 .entry-content a{color:#0D0F12!important;text-decoration:underline;text-decoration-color:#A8B0B9;text-underline-offset:3px}
body.page-id-596 .entry-content ul{padding-left:18px}
body.page-id-596 .entry-content table{display:block;overflow-x:auto;border:1px solid #E3E6E9;border-radius:12px;font-size:13px}
body.page-id-596 .entry-content table td,body.page-id-596 .entry-content table th{border-color:#EEF0F2!important;padding:8px 10px!important}
#sp-cnp-pill{display:inline-flex;align-items:center;padding:6px 12px;border-radius:999px;background:#F2F3F4;font:700 10.5px/1 Sora,sans-serif;letter-spacing:.12em;text-transform:uppercase;color:#4B5157;margin:0 0 12px}
</style>
<script>
(function(){var h=document.querySelector('body.page-id-596 .entry-header');if(h&&!document.getElementById('sp-cnp-pill')){var p=document.createElement('div');p.id='sp-cnp-pill';p.textContent='Rechtliches';h.insertBefore(p,h.firstChild);}})();
</script>
    <?php
}, 100);
