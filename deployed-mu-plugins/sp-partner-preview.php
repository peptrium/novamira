<?php
/**
 * Plugin Name: SP Partnerkonto-Vorschau
 * Description: Entwurf Partner-Bereich (SliceWP) (2026-10-10), NUR im Vorschau-Modus (sp_hpv_token_ok() aus sp-home-preview.php).
 *
 * 1) Uebersetzungsluecken von SliceWP (Englisch, "Armaturenbrett", Sie-Form) per gettext auf Deutsch/du.
 * 2) Tab-Leiste im Partnerkonto auf dem Handy als Chips mit Beschriftung (SliceWP zeigt dort nur Icons).
 * Beim Go-live: Vorschau-Pruefung entfernen. Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_ptp_active() {
    return function_exists('sp_hpv_token_ok') && sp_hpv_token_ok();
}

function sp_ptp_strings() {
    return [
        'Dashboard' => 'Übersicht',
        'Past 7 days' => 'Letzte 7 Tage',
        'Past 30 days' => 'Letzte 30 Tage',
        'Week to date' => 'Diese Woche',
        'Month to date' => 'Dieser Monat',
        'Year to date' => 'Dieses Jahr',
        'Last week' => 'Letzte Woche',
        'Last month' => 'Letzter Monat',
        'Last year' => 'Letztes Jahr',
        'Custom' => 'Zeitraum wählen',
        'Earnings' => 'Einnahmen',
        'Daily' => 'Täglich',
        'Weekly' => 'Wöchentlich',
        'Monthly' => 'Monatlich',
        'Landing Page' => 'Zielseite',
        'Referrer URL' => 'Herkunft',
        'Generated Referral Link' => 'Dein neuer Partner-Link',
        'Share the affiliate referral link below to earn commissions.' => 'Teile diesen Link – für jede Bestellung darüber bekommst du Provision.',
        'This is your referral URL. Share it with your audience to earn commissions.' => 'Das ist dein Partner-Link. Teile ihn mit deiner Community – für jede Bestellung darüber bekommst du Provision.',
        'Add any URL from this website in the field below to generate a referral link.' => 'Füge unten eine beliebige Seite aus unserem Shop ein, um einen Partner-Link genau dafür zu erzeugen.',
    ];
}

add_filter('gettext_slicewp', function ($translation, $text) {
    if (!sp_ptp_active()) {
        return $translation;
    }
    $m = sp_ptp_strings();
    return $m[$text] ?? $translation;
}, 20, 2);

add_action('wp_footer', function () {
    if (!sp_ptp_active() || !is_page([557, 558, 623])) {
        return;
    }
    ?>
<style id="sp-ptp-css">
@media(max-width:768px){
 #slicewp-affiliate-account-nav-tab{margin:0 -16px 16px!important;border:none!important;position:relative}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab-wrapper{display:flex!important;flex-wrap:nowrap!important;gap:8px!important;overflow-x:auto;scrollbar-width:none;padding:4px 16px 8px!important;margin:0!important;border:none!important}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab-wrapper::-webkit-scrollbar{display:none}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab{flex:0 0 auto!important;margin:0!important;border:none!important;padding:0!important}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab a{display:inline-flex!important;align-items:center;gap:7px;height:40px;padding:0 15px!important;border-radius:999px!important;border:1px solid #E3E6E9!important;background:#fff!important;color:#0D0F12!important;font:700 13.5px/1 Sora,sans-serif!important;white-space:nowrap;text-decoration:none!important}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab a span{display:inline!important;position:static!important;width:auto!important;height:auto!important;clip:auto!important;overflow:visible!important;margin:0!important}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab a svg{width:16px!important;height:16px!important;flex:0 0 16px}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab.slicewp-active a{background:#0D0F12!important;border-color:#0D0F12!important;color:#fff!important}
 #slicewp-affiliate-account-nav-tab .slicewp-nav-tab.slicewp-active a svg *{stroke:#fff!important}
 #slicewp-affiliate-account-nav-tab:after{content:'';position:absolute;right:0;top:0;bottom:8px;width:28px;background:linear-gradient(90deg,rgba(255,255,255,0),#fff);pointer-events:none}
}
</style>
<script id="sp-ptp-js">
(function(){
 var ul=document.querySelector('#slicewp-affiliate-account-nav-tab .slicewp-nav-tab-wrapper');if(!ul)return;
 function center(){var a=ul.querySelector('.slicewp-active');if(a&&window.innerWidth<=768)ul.scrollLeft=Math.max(0,a.offsetLeft-ul.clientWidth/2+a.offsetWidth/2);}
 center();ul.addEventListener('click',function(){setTimeout(center,50);});
})();
</script>
    <?php
}, 100);
