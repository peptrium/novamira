<?php
/**
 * Plugin Name: SP Buybox Volumen
 * Description: Die Elementor-Kaufboxen (#rx-buybox) haben fest eine einzige Volumen-Kachel
 * (z. B. Retatrutide "10 mg", rx-tier--static). Hat das variable Produkt mehrere kaufbare
 * Varianten, wird die Kachel beim Ausgeben durch je eine Kachel pro Variante ersetzt
 * (mit Preis). Die vorhandene Kaufbox-JS (initVolumeSelector) bindet die Kacheln selbst.
 * Nichts in Elementor gespeichert wird verändert.
 */

if (!defined('ABSPATH')) exit;

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

    $tiles = '';
    foreach ($vars as $i => $v) {
        $tiles .= '<button type="button" class="rx-tier' . ($i === 0 ? ' is-active' : '') . '" data-vol="' . esc_attr($v['vol']) . '">'
            . '<span class="rx-tier__disc">' . esc_html(number_format($v['price'], 2, ',', '.')) . '&nbsp;&euro;</span>'
            . '<span class="rx-tier__q">' . esc_html($v['q']) . '</span><span class="rx-tier__unit">' . esc_html($v['unit']) . '</span></button>';
    }
    $GLOBALS['sp_bbv_tiles'] = $tiles;

    ob_start(function ($html) {
        if (empty($GLOBALS['sp_bbv_tiles']) || strpos($html, 'rx-tier--static') === false) return $html;
        $out = preg_replace(
            '~<div class="rx-tier is-active rx-tier--static" data-vol="[^"]*">\s*<span class="rx-tier__q">[^<]*</span><span class="rx-tier__unit">[^<]*</span>\s*</div>~',
            $GLOBALS['sp_bbv_tiles'], $html, 1, $n);
        return ($n && is_string($out)) ? $out : $html;
    });
}, 5);

/* Der große Preis oben in der Kaufbox wird von der Elementor-JS nur einmal gesetzt -
   beim Wechsel der Variante mitziehen. */
add_action('wp_footer', function () {
    if (empty($GLOBALS['sp_bbv_tiles'])) return;
    ?>
<script>
(function(){
  if (!window.jQuery) return;
  jQuery(function($){
    $('#rx-buybox').closest('body').find('form.variations_form').on('found_variation', function(e, v){
      var el = document.getElementById('rx-price');
      if (el && v && v.display_price != null) {
        el.textContent = Number(v.display_price).toLocaleString('de-DE', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' €';
      }
    });
  });
})();
</script>
    <?php
}, 99);
