<?php
/**
 * Plugin Name: SP Abo-Seiten-Vorschau
 * Description: Abo-Seiten im Redesign (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * - Abo-Stack (743): "Weiter"/"Zurueck" behalten nach dem Antippen ihre Farbe (Astra faerbt fokussierte
 *   Buttons blau #045CB4 - auf dem iPhone bleibt der Fokus nach dem Tippen haengen -> blauer Weiter-Button).
 * - Abo-Modell (735): Ueberschriften im normalen Schwarz statt Dunkelblau (#1E293B).
 * Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    if (!function_exists('sp_hpv_token_ok') || !sp_hpv_token_ok() || !is_page([735, 743])) {
        return;
    }
    ?>
<style id="sp-abp-css">
.sp-stack-nav .sp-stack-next,.sp-stack-nav .sp-stack-next:focus,.sp-stack-nav .sp-stack-next:active,.sp-stack-nav .sp-stack-next:focus-visible{background-color:#FF6B35!important;background-image:none!important;color:#fff!important;outline:none;box-shadow:none}
.sp-stack-nav .sp-stack-next:not(:disabled):hover{background-color:#E5342B!important}
.sp-stack-nav .sp-stack-back,.sp-stack-nav .sp-stack-back:focus,.sp-stack-nav .sp-stack-back:active{background-color:#fff!important;background-image:none!important;color:#0D0F12!important;outline:none}
.sp-stack-nav{box-shadow:0 -8px 20px rgba(13,15,18,.06)}
body.page-id-735 .sp-abo h2{color:#0D0F12!important}
body.page-id-735 .sp-closing h2,body.page-id-735 .sp-closing h3{color:#fff!important}
</style>
    <?php
}, 100);
