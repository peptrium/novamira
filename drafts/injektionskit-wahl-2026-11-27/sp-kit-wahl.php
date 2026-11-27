<?php
/**
 * Plugin Name: Peptrium – Injektionskit: Pen Nadeln oder Spritzen
 * Description: (2026-11-27) Das Gratis-Injektionskit (745, Geschenkstufe 200 €) gibt es als „Spritzen“ (wie bisher:
 *   Spritzen + Tupfer + Bac Water laut Lager-Rezept) oder als „Pen Nadeln“ (nur Pen Nadeln, ohne Tupfer).
 *   Vorauswahl: kein Pen im Warenkorb → Spritzen, keine Auswahl. Nur Pens → Pen Nadeln. Pen + Flaschen → Spritzen.
 *   Mit Pen im Warenkorb kann der Kunde im Drawer wählen (Session `sp_kit_wahl`, admin-ajax `sp_kit`).
 *   Bestellposition: Name „Injektionskit (Pen Nadeln|Spritzen)“ + versteckte Meta `_sp_kit` (nadeln|spritzen) →
 *   Lager (sp-lager.php), EK (sp-ek-marge.php) und Versandliste (sp-versand-liste.php) lesen sie.
 */
if (!defined('ABSPATH')) exit;

define('SP_KIT_ID', 745);
define('SP_KIT_NADELN_ID', 908);

function sp_kit_pen_ids() { return [393, 395, 396]; }

/** Warenkorb-Inhalt: [hat Pen, hat Flasche]. */
function sp_kit_cart_flags($cart = null) {
    $cart = $cart ?: (function_exists('WC') && WC()->cart ? WC()->cart : null);
    $pen = false; $vial = false;
    if (!$cart) return [false, false];
    foreach ($cart->get_cart() as $it) {
        if (!empty($it['sp_is_gift'])) continue;
        $pid = (int) $it['product_id'];
        if (in_array($pid, sp_kit_pen_ids(), true)) { $pen = true; continue; }
        if (in_array($pid, [729, 817, SP_KIT_ID], true)) continue;
        if (has_term(20, 'product_cat', $pid)) continue; // Zubehör
        $vial = true;
    }
    return [$pen, $vial];
}

/** Aktuelle Wahl: 'nadeln' | 'spritzen'. */
function sp_kit_choice($cart = null) {
    [$pen, $vial] = sp_kit_cart_flags($cart);
    if (!$pen) return 'spritzen';
    $s = (function_exists('WC') && WC()->session) ? WC()->session->get('sp_kit_wahl') : '';
    if ($s === 'nadeln' || $s === 'spritzen') return $s;
    return $vial ? 'spritzen' : 'nadeln';
}

function sp_kit_label($c) { return $c === 'nadeln' ? 'Pen Nadeln' : 'Spritzen'; }

function sp_kit_is_kit_line($cart_item) {
    return !empty($cart_item['sp_is_gift']) && (int) $cart_item['product_id'] === SP_KIT_ID;
}

/* Warenkorb / Kasse: sichtbar „Inhalt“, versteckt der Status für den Drawer */
add_filter('woocommerce_get_item_data', function ($data, $cart_item) {
    if (!sp_kit_is_kit_line($cart_item)) return $data;
    [$pen] = sp_kit_cart_flags();
    $c = sp_kit_choice();
    $data[] = ['key' => 'Inhalt', 'value' => sp_kit_label($c)];
    $data[] = ['key' => 'sp_kit', 'value' => ($pen ? 'wahl:' : 'fest:') . $c, 'hidden' => true, '__experimental_woocommerce_blocks_hidden' => true];
    return $data;
}, 20, 2);

/* Bild der Pen Nadeln, wenn gewählt (Drawer + Kasse über die Store API) */
add_filter('woocommerce_store_api_cart_item_images', function ($images, $cart_item, $key) {
    if (!sp_kit_is_kit_line($cart_item) || sp_kit_choice() !== 'nadeln') return $images;
    $p = wc_get_product(SP_KIT_NADELN_ID);
    $aid = $p ? (int) $p->get_image_id() : 0;
    if (!$aid) return $images;
    return [(object) [
        'id' => $aid,
        'src' => (string) wp_get_attachment_url($aid),
        'thumbnail' => (string) wp_get_attachment_image_url($aid, 'woocommerce_thumbnail'),
        'srcset' => (string) wp_get_attachment_image_srcset($aid, 'full'),
        'sizes' => (string) wp_get_attachment_image_sizes($aid, 'full'),
        'name' => get_the_title($aid),
        'alt' => 'Pen Nadeln',
    ]];
}, 10, 3);

/* Bestellposition */
add_action('woocommerce_checkout_create_order_line_item', function ($item, $key, $values) {
    if (!sp_kit_is_kit_line($values)) return;
    $c = sp_kit_choice();
    $item->set_name('Injektionskit (' . sp_kit_label($c) . ')');
    $item->add_meta_data('_sp_kit', $c, true);
}, 20, 3);

/* Wahl speichern */
function sp_kit_ajax() {
    $c = isset($_POST['c']) ? sanitize_key(wp_unslash($_POST['c'])) : '';
    if (!in_array($c, ['nadeln', 'spritzen'], true) || !function_exists('WC') || !WC()->session) wp_send_json_error();
    WC()->session->set('sp_kit_wahl', $c);
    if (WC()->cart) WC()->cart->calculate_totals();
    wp_send_json_success(['c' => sp_kit_choice()]);
}
add_action('wp_ajax_sp_kit', 'sp_kit_ajax');
add_action('wp_ajax_nopriv_sp_kit', 'sp_kit_ajax');

/* Drawer: Auswahl unter dem Injektionskit */
add_action('wp_footer', function () {
    if (is_admin()) return;
    $p = wc_get_product(SP_KIT_NADELN_ID);
    $nad = $p ? wp_get_attachment_image_url($p->get_image_id(), 'woocommerce_thumbnail') : '';
    $s = wc_get_product(80);
    $spr = $s ? wp_get_attachment_image_url($s->get_image_id(), 'woocommerce_thumbnail') : '';
    ?>
<style id="sp-kit-css">
#sp-cart-drawer .sp-cd-item.sp-kit-row{flex-wrap:wrap}
#sp-cart-drawer .sp-kit{flex:0 0 100%;order:9;margin-top:2px}
#sp-cart-drawer .sp-kit-l{font-size:12px;color:#80868E;margin:0 0 6px}
#sp-cart-drawer .sp-kit-ch{display:grid;grid-template-columns:1fr 1fr;gap:8px}
#sp-cart-drawer .sp-kit-op{all:unset;box-sizing:border-box;cursor:pointer;display:flex;align-items:center;gap:7px;border:1.5px solid #DADDE1;border-radius:11px;padding:8px 10px;background:#fff;font:700 13.5px/1.2 Inter,sans-serif;color:#0D0F12;white-space:nowrap}
#sp-cart-drawer .sp-kit-op img{width:32px!important;height:32px!important;border-radius:7px!important;object-fit:cover;box-shadow:none!important;flex:none}
#sp-cart-drawer .sp-kit-op i{margin-left:auto;width:15px;height:15px;border-radius:50%;border:1.5px solid #C5C9CE;flex:none}
#sp-cart-drawer .sp-kit-op.on{border-color:#0D0F12;box-shadow:0 0 0 1px #0D0F12}
#sp-cart-drawer .sp-kit-op.on i{background:#0D0F12;border-color:#0D0F12;box-shadow:inset 0 0 0 3px #fff}
#sp-cart-drawer .sp-kit.busy{opacity:.5;pointer-events:none}
</style>
<script id="sp-kit-js">
(function(){
  var NAD=<?php echo wp_json_encode($nad); ?>,SPR=<?php echo wp_json_encode($spr); ?>,AJ=<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
  var state={};
  // Kit-Status aus den Store-API-Antworten mitlesen (versteckte item_data 'sp_kit')
  var of=window.fetch;
  if(of&&!of.__spKit){window.fetch=function(u,o){var p=of.apply(this,arguments);try{var s=typeof u==='string'?u:(u&&u.url)||'';if(s.indexOf('/wc/store/')>-1){p.then(function(r){if(!r||!r.clone)return;r.clone().json().then(read).catch(function(){});}).catch(function(){});}}catch(e){}return p;};window.fetch.__spKit=1;}
  function read(j){var c=j&&j.items?j:(j&&j.responses?null:null);if(j&&j.responses){j.responses.forEach(function(x){if(x&&x.body&&x.body.items)read(x.body);});return;}if(!c)return;
    c.items.forEach(function(it){(it.item_data||[]).forEach(function(d){if(d.key==='sp_kit'){state[it.key]=d.value;}});});setTimeout(paint,30);}
  function paint(){
    var host=document.getElementById('sp-cd-items');if(!host)return;
    host.querySelectorAll('.sp-cd-item[data-key]').forEach(function(row){
      var v=state[row.getAttribute('data-key')];if(!v)return;
      var c=v.split(':')[1],wahl=v.indexOf('wahl:')===0;
      var vol=row.querySelector('.sp-cd-item-vol');if(vol&&/Inhalt|Spritzen|Pen Nadeln/.test(vol.textContent))vol.remove();
      var img=row.querySelector(':scope>img');if(img){var want=c==='nadeln'?NAD:SPR;if(want&&img.getAttribute('src')!==want)img.setAttribute('src',want);}
      var box=row.querySelector('.sp-kit');
      if(!wahl){if(box)box.remove();row.classList.remove('sp-kit-row');return;}
      if(box&&box.getAttribute('data-c')===c)return;
      if(!box){box=document.createElement('div');box.className='sp-kit';row.appendChild(box);row.classList.add('sp-kit-row');}
      box.setAttribute('data-c',c);
      box.innerHTML='<p class="sp-kit-l">Wähle, was du brauchst:</p><div class="sp-kit-ch">'+
        '<button type="button" class="sp-kit-op'+(c==='nadeln'?' on':'')+'" data-c="nadeln"><img src="'+NAD+'" alt="">Pen Nadeln<i></i></button>'+
        '<button type="button" class="sp-kit-op'+(c==='spritzen'?' on':'')+'" data-c="spritzen"><img src="'+SPR+'" alt="">Spritzen<i></i></button></div>';
    });
  }
  document.addEventListener('click',function(e){
    var b=e.target.closest&&e.target.closest('#sp-cart-drawer .sp-kit-op');if(!b)return;
    e.preventDefault();e.stopPropagation();
    var box=b.closest('.sp-kit');if(b.classList.contains('on'))return;
    box.classList.add('busy');
    var fd=new FormData();fd.append('action','sp_kit');fd.append('c',b.getAttribute('data-c'));
    of.call(window,AJ,{method:'POST',body:fd,credentials:'same-origin'}).then(function(){if(window.jQuery)jQuery(document.body).trigger('added_to_cart');}).catch(function(){}).then(function(){setTimeout(function(){box.classList.remove('busy');},800);});
  },true);
  var mo=new MutationObserver(function(){paint();});
  function start(){var h=document.getElementById('sp-cd-items');if(h)mo.observe(h,{childList:true,subtree:false});else setTimeout(start,500);}
  if(document.readyState!=='loading')start();else document.addEventListener('DOMContentLoaded',start);
})();
</script>
    <?php
}, 120);
