<?php
/**
 * Plugin Name: SP Kassen-Texte
 * Description: Korrigiert fehlerhafte deutsche WooCommerce-Übersetzungen in Kasse/Warenkorb (2026-10-09).
 *
 * WooCommerce Blocks baut Pflichtfeld-Meldungen aus "Please enter a valid %s"
 * + Feldname. Die offizielle deutsche Uebersetzung "Bitte gib ein gültiges %s
 * ein" ergibt "Bitte gib ein gültiges E-Mail-Adresse ein" / "... gültiges
 * Postleitzahl ein". Ersetzt durch "%s fehlt oder ist ungültig" - fuer jeden
 * Feldnamen grammatisch korrekt. Die Meldungen entstehen im Browser (wp.i18n),
 * daher ein JS-Filter; der PHP-Filter deckt dieselbe Zeichenkette serverseitig ab.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_ct_fixes() {
    return array(
        'Please enter a valid %s' => '%s fehlt oder ist ungültig',
    );
}

add_filter('gettext_woocommerce', function ($translation, $text) {
    $fixes = sp_ct_fixes();
    return isset($fixes[$text]) ? $fixes[$text] : $translation;
}, 20, 2);

add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !(is_checkout() || is_cart())) {
        return;
    }
    ?>
    <script>
    (function(){
      var F=<?php echo wp_json_encode(sp_ct_fixes()); ?>;
      function add(){
        if(!(window.wp&&wp.hooks&&wp.hooks.addFilter))return false;
        wp.hooks.addFilter('i18n.gettext_woocommerce','sp/kassen-texte',function(tr,text){return Object.prototype.hasOwnProperty.call(F,text)?F[text]:tr;});
        return true;
      }
      if(!add()){var n=0,iv=setInterval(function(){if(add()||++n>40)clearInterval(iv);},250);}
    })();
    </script>
    <?php
}, 5);
