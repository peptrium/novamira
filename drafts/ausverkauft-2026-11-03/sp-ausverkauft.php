<?php
/**
 * Plugin Name: SP Ausverkauft
 * Description: (2026-11-03) Produkte mit Lagerstatus "Nicht vorrätig" (outofstock) sichtbar als AUSVERKAUFT kennzeichnen.
 *   Produktseite: Lagerzeile "Ausverkauft", Mengen/Kaufbutton/Zubehör/Sticky-Leiste ausgeblendet, Hinweisbox mit
 *   Newsletter-Link. Produktkarten (.sp-pc): Badge "Ausverkauft", Button wird zum grauen "Ausverkauft"-Link.
 *   Gilt automatisch für jedes Produkt, das im Backend auf "Nicht vorrätig" steht (nicht für Vorbestellungen).
 *   Wieder lieferbar = im Backend auf "Vorrätig" stellen, sonst nichts.
 */
if (!defined('ABSPATH')) exit;

/** IDs aller sichtbaren, ausverkauften Produkte (ohne Vorbestellungen/Sonderprodukte). */
function sp_av_ids() {
    $ids = get_transient('sp_av_ids_v1');
    if (is_array($ids)) return $ids;
    $skip = [729, 745, 817];
    $pre = function_exists('sp_preorder_product_ids') ? array_map('intval', sp_preorder_product_ids()) : [];
    $ids = [];
    foreach (wc_get_products(['limit' => -1, 'status' => 'publish', 'stock_status' => 'outofstock', 'return' => 'ids']) as $id) {
        $id = (int) $id;
        if (!in_array($id, $skip, true) && !in_array($id, $pre, true)) $ids[] = $id;
    }
    set_transient('sp_av_ids_v1', $ids, HOUR_IN_SECONDS);
    return $ids;
}
foreach (['woocommerce_product_set_stock_status', 'woocommerce_variation_set_stock_status', 'woocommerce_update_product'] as $h) {
    add_action($h, function () { delete_transient('sp_av_ids_v1'); });
}

add_filter('body_class', function ($c) {
    if (function_exists('is_product') && is_product() && in_array((int) get_queried_object_id(), sp_av_ids(), true)) $c[] = 'sp-av-out';
    return $c;
});

add_action('wp_footer', function () {
    if (is_admin()) return;
    $ids = sp_av_ids();
    if (!$ids) return;
    $pid = (function_exists('is_product') && is_product()) ? (int) get_queried_object_id() : 0;
    ?>
<style>
body.sp-av-out #rx-buybox .rx-tiers-head,body.sp-av-out #rx-buybox .rx-tiers,body.sp-av-out #rx-buybox .rx-buysum,
body.sp-av-out #rx-buybox .rx-buycta,body.sp-av-out #rx-buybox #rx-addons,body.sp-av-out #rx-buybox .rx-shiphint,
body.sp-av-out #rx-buybox .sp-bb-trust,body.sp-av-out #sp-pp-bar,body.sp-av-out #sp-hw-bb,body.sp-av-out .rx-addons,body.sp-av-out #sp-abo-toggle,body.sp-av-out .sp-abo-interval-row{display:none!important}
body.sp-av-out #rx-buybox .rx-stock{color:#B42318!important;font-weight:700!important}
body.sp-av-out #rx-buybox .rx-stock .rx-stock-dot{background:#B42318!important;box-shadow:none!important;animation:none!important}
.sp-av-box{background:#0D0F12;color:#fff;border-radius:14px;padding:16px 16px 14px;margin:14px 0 6px;font-family:Sora,sans-serif;text-align:center}
.sp-av-box b{display:block;font-size:17px;font-weight:800;margin-bottom:4px}
.sp-av-box span{display:block;font-size:13px;color:#C7CCD1;line-height:1.45}
.sp-av-box a{display:inline-block;margin-top:11px;background:#fff;color:#0D0F12!important;font-weight:700;font-size:13.5px;padding:10px 16px;border-radius:10px;text-decoration:none}
.sp-pc a.add.sp-av-btn{background:#E4E6E9!important;color:#5B6169!important;text-align:center;pointer-events:auto}
.sp-pc .bd.sp-av-bd{background:#B42318!important;color:#fff!important}
.sp-pc.sp-av .im img{opacity:.55;filter:grayscale(.4)}
</style>
<script>
(function(){
  var IDS=<?php echo wp_json_encode(array_map('strval', $ids)); ?>, PID=<?php echo (int) $pid; ?>;
  function cards(){
    IDS.forEach(function(id){
      document.querySelectorAll('.sp-pc[data-id="'+id+'"]').forEach(function(c){
        if(c.dataset.av)return; c.dataset.av=1; c.classList.add('sp-av');
        var u=(c.querySelector('a.im')||{}).href||'#';
        c.querySelectorAll('button.add,a.add').forEach(function(b){var a=document.createElement('a');a.className='add sp-av-btn';a.href=u;a.textContent='Ausverkauft';b.replaceWith(a);});
        var im=c.querySelector('a.im');
        if(im){var old=im.querySelector('.bd'); if(old)old.remove(); var s=document.createElement('span');s.className='bd sp-av-bd';s.textContent='Ausverkauft';im.appendChild(s);}
        c.querySelectorAll('.vm,.vg,.qd,.pre-n').forEach(function(x){x.style.display='none';});
      });
    });
  }
  function page(){
    if(!PID||IDS.indexOf(String(PID))<0)return;
    var bb=document.getElementById('rx-buybox'); if(!bb)return;
    var st=bb.querySelector('.rx-stock'); if(st&&!st.dataset.av){st.dataset.av=1;st.innerHTML='<span class="rx-stock-dot"></span> Ausverkauft';}
    if(!bb.querySelector('.sp-av-box')){
      var row=bb.querySelector('.rx-price-row')||bb.firstElementChild;
      var g=document.createElement('div');g.className='sp-av-box';
      g.innerHTML='<b>Gerade ausverkauft</b><span>Wir füllen so schnell wie möglich nach. Trag dich in den Newsletter ein – dann erfährst du es zuerst.</span><a href="'+(document.getElementById('sp-nlh')?'#sp-nlh':'/#sp-nlh')+'">Benachrichtigen lassen →</a>';
      if(row&&row.parentNode)row.parentNode.insertBefore(g,row.nextSibling); else bb.appendChild(g);
    }
  }
  function run(){cards();page();}
  run(); document.addEventListener('DOMContentLoaded',run);
  new MutationObserver(function(){cards();page();}).observe(document.documentElement,{childList:true,subtree:true});
})();
</script>
    <?php
}, 125);
