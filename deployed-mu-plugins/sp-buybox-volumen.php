<?php
/**
 * Plugin Name: SP Buybox Volumen
 * Description: Die Elementor-Kaufboxen (#rx-buybox) haben fest eine einzige Volumen-Kachel
 * (z. B. Retatrutide "10 mg", GHK-Cu "50 mg", rx-tier--static). Hat das variable Produkt mehrere
 * kaufbare Varianten, wird die Kachel beim Ausgeben durch je eine Kachel pro Variante ersetzt
 * (mit Preis). Retatrutide bringt die Umschalt-JS (initVolumeSelector) selbst mit; bei Kaufboxen
 * ohne diese JS (GHK-Cu war früher ein einfaches Produkt) wird sie hier ergänzt.
 * Nichts in Elementor gespeichert wird verändert.
 */

if (!defined('ABSPATH')) exit;

/**
 * Produkte, die früher einfache Produkte waren und jetzt variabel sind: alte Bestellungen /
 * Warenkorb-Snapshots haben keine variation_id. Liefert [variation_id, attribute] der
 * Standard-Variante (die alte Größe) oder null.
 */
function sp_bbv_legacy_variation($product_id) {
    $map = (array) get_option('sp_bbv_legacy', []);
    $vid = isset($map[(int) $product_id]) ? (int) $map[(int) $product_id] : 0;
    if (!$vid) return null;
    $v = wc_get_product($vid);
    if (!$v || !$v->is_type('variation')) return null;
    return [$vid, $v->get_variation_attributes()];
}

/* Alle Werte VOR ob_start berechnen - im Callback nur Stringarbeit (siehe CLAUDE.md). */
add_action('template_redirect', function () {
    if (is_admin() || !function_exists('is_product') || !is_product()) return;
    $p = wc_get_product(get_queried_object_id());
    if (!$p || !$p->is_type('variable')) return;

    $vars = [];
    foreach ($p->get_children() as $vid) {
        $v = wc_get_product($vid);
        if (!$v || get_post_status($vid) !== 'publish' || !$v->is_purchasable() || !$v->is_in_stock()) continue;
        $va   = $v->get_variation_attributes();
        $slug = isset($va['attribute_volumen']) ? (string) $va['attribute_volumen'] : '';
        $attr = $v->get_attribute('volumen');
        if (!preg_match('/^\s*(\d+(?:[.,]\d+)?)\s*([a-zA-Zµ]+)\s*$/u', $attr, $m)) continue;
        $vars[] = [
            'vol'   => $slug !== '' ? $slug : $attr,
            'q'     => $m[1],
            'unit'  => $m[2],
            'price' => (float) wc_get_price_to_display($v),
        ];
    }
    if (count($vars) < 2) return;
    usort($vars, function ($a, $b) { return (float) str_replace(',', '.', $a['q']) <=> (float) str_replace(',', '.', $b['q']); });

    // ?attribute_volumen=20mg (z. B. Link aus einer Mail) wählt diese Größe vor
    $want = isset($_GET['attribute_volumen']) ? sanitize_title(wp_unslash($_GET['attribute_volumen'])) : '';
    $act = 0;
    foreach ($vars as $i => $v) {
        if ($want !== '' && sanitize_title($v['vol']) === $want) $act = $i;
    }
    $tiles = '';
    foreach ($vars as $i => $v) {
        $tiles .= '<button type="button" class="rx-tier' . ($i === $act ? ' is-active' : '') . '" data-vol="' . esc_attr($v['vol']) . '">'
            . '<span class="rx-tier__disc">' . esc_html(number_format($v['price'], 2, ',', '.')) . '&nbsp;&euro;</span>'
            . '<span class="rx-tier__q">' . esc_html($v['q']) . '</span><span class="rx-tier__unit">' . esc_html($v['unit']) . '</span></button>';
    }
    $GLOBALS['sp_bbv_tiles'] = $tiles;
    $GLOBALS['sp_bbv_own']   = false;

    ob_start(function ($html) {
        if (empty($GLOBALS['sp_bbv_tiles']) || strpos($html, 'rx-tier--static') === false) return $html;
        $out = preg_replace(
            '~<div class="rx-tier is-active rx-tier--static"(?: data-vol="[^"]*")?>\s*<span class="rx-tier__q">[^<]*</span><span class="rx-tier__unit">[^<]*</span>\s*</div>~',
            $GLOBALS['sp_bbv_tiles'], $html, 1, $n);
        if (!$n || !is_string($out)) return $html;
        // Kaufbox ohne eigene Varianten-JS: Container-ID setzen und der (geschlossenen)
        // Kaufbox-JS einen Weg geben, den Stückpreis zu ändern.
        if (strpos($out, 'initVolumeSelector') === false) {
            $out = str_replace('<div class="rx-tiers rx-tiers--single">', '<div class="rx-tiers rx-tiers--single" id="rx-vol-tiers">', $out);
            $i = strpos($out, 'var unitPrice');
            $k = $i !== false ? strpos($out, "update(1);", $i) : false;
            if ($k !== false) {
                $out = substr_replace($out, 'update(1); window.spBBSetPrice = function(p){ unitPrice = p; update(parseInt(qtyField.value, 10) || 1); };', $k, strlen('update(1);'));
                $out = str_replace('</body>', '<script>window.spBBOwn = 1;</script></body>', $out);
            }
        }
        return $out;
    });
}, 5);

add_action('wp_head', function () {
    if (empty($GLOBALS['sp_bbv_tiles'])) return;
    echo '<style>#sp-reta-buybox-el .variations,#sp-reta-buybox-el .reset_variations{display:none!important}#sp-reta-buybox-el .single_variation{display:none!important;margin:0!important;padding:0!important}</style>';
}, 99);

add_action('wp_footer', function () {
    if (empty($GLOBALS['sp_bbv_tiles'])) return;
    ?>
<script>
(function(){
  if (!window.jQuery) return;
  jQuery(function($){ setTimeout(function(){
    var form = document.querySelector('#sp-reta-buybox-el form.variations_form') || document.querySelector('form.variations_form');
    if (!form) return;
    function fmt(n){ return Number(n).toLocaleString('de-DE', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' €'; }
    /* Großer Preis oben: die Elementor-JS setzt ihn nur einmal. */
    $(form).on('found_variation', function(e, v){
      if (!v || v.display_price == null) return;
      if (window.spBBOwn && window.spBBSetPrice) window.spBBSetPrice(parseFloat(v.display_price));
      var el = document.getElementById('rx-price');
      if (el) el.textContent = fmt(v.display_price);
    });
    if (!window.spBBOwn) return; /* Retatrutide: Kachel-Logik hat die Kaufbox selbst */
    var select = form.querySelector('select[name="attribute_volumen"]');
    var tiles = document.querySelectorAll('#rx-vol-tiers .rx-tier');
    if (!select || !tiles.length) return;
    function pick(vol){
      select.value = vol;
      $(select).trigger('change');
      tiles.forEach(function(t){ t.classList.toggle('is-active', t.getAttribute('data-vol') === vol); });
    }
    tiles.forEach(function(t){ t.addEventListener('click', function(){ pick(t.getAttribute('data-vol')); }); });
    var a = document.querySelector('#rx-vol-tiers .rx-tier.is-active') || tiles[0];
    pick(a.getAttribute('data-vol'));
  }, 0); });
})();
</script>
    <?php
}, 99);
