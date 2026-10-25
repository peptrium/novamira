<?php
/**
 * Plugin Name: SP Neuheiten-Start
 * Description: (2026-10-25) Neue Produkte (Cagrilintid, MT-2, GLOW) sind schon sichtbar, aber erst ab dem Halloween-Start
 *   kaufbar: Newsletter-Insider mit Early-Access-Cookie ab Do 18:00 (sp_hw['early']), alle ab Fr 00:00 (sp_hw['start']).
 *   Vorher: nicht kaufbar (woocommerce_is_purchasable), Kaufbox zeigt "Erhältlich ab …", Produktkarten verlinken
 *   auf die Seite statt "In den Warenkorb". Danach automatisch normal kaufbar – nichts muss umgestellt werden.
 */
if (!defined('ABSPATH')) exit;

function sp_nl_launch_ids() { return [1051, 1053, 1055]; }

function sp_nl_launch_times() {
    $o = function_exists('sp_hw_opt') ? sp_hw_opt() : [];
    $tz = new DateTimeZone('Europe/Berlin');
    return [
        'early' => (int) ($o['early'] ?? (new DateTime('2026-10-29 18:00:00', $tz))->getTimestamp()),
        'start' => (int) ($o['start'] ?? (new DateTime('2026-10-30 00:00:00', $tz))->getTimestamp()),
    ];
}

/** Darf dieses Produkt jetzt gekauft werden? */
function sp_nl_launch_open($pid) {
    if (!in_array((int) $pid, sp_nl_launch_ids(), true)) return true;
    $t = sp_nl_launch_times();
    if (time() >= $t['start']) return true;
    if (function_exists('sp_hw_is_preview') && sp_hw_is_preview()) return true; // Halloween-Vorschau-Link zum Testen
    return time() >= $t['early'] && function_exists('sp_hw_has_ea') && sp_hw_has_ea();
}

add_filter('woocommerce_is_purchasable', function ($ok, $p) {
    if (!$ok) return $ok;
    $pid = $p->get_parent_id() ?: $p->get_id();
    return sp_nl_launch_open($pid) ? $ok : false;
}, 20, 2);

/** Noch gesperrte Produkte (für das Frontend). */
function sp_nl_launch_closed() {
    $out = [];
    foreach (sp_nl_launch_ids() as $id) {
        if (!sp_nl_launch_open($id)) $out[] = $id;
    }
    return $out;
}

add_action('wp_footer', function () {
    if (is_admin()) return;
    $closed = sp_nl_launch_closed();
    if (!$closed) return;
    $t = sp_nl_launch_times();
    $early_phase = time() >= $t['early'];
    $head = $early_phase ? '🎃 Für alle ab Fr ' . wp_date('j.n.', $t['start']) . ', 0 Uhr' : '🎃 Erhältlich ab Do ' . wp_date('j.n.', $t['early']) . ', 18 Uhr';
    $sub = $early_phase ? 'Newsletter-Insider können schon jetzt bestellen.' : 'Newsletter-Insider bestellen zuerst – für alle ab Fr ' . wp_date('j.n.', $t['start']) . ', 0 Uhr.';
    $card = $early_phase ? 'Ab Fr 0 Uhr' : 'Ab Do 18 Uhr';
    $pid = is_product() ? (int) get_queried_object_id() : 0;
    ?>
<style>
.sp-nlx-gate{background:#0D0F12;color:#fff;border-radius:14px;padding:16px 16px 14px;margin:6px 0 4px;font-family:Sora,sans-serif;text-align:center}
.sp-nlx-gate b{display:block;font-size:16px;font-weight:700;margin-bottom:4px}
.sp-nlx-gate span{display:block;font-size:12.5px;color:#C7CCD1;line-height:1.45}
.sp-nlx-gate a{display:inline-block;margin-top:10px;background:#F97316;color:#fff!important;font-weight:700;font-size:13.5px;padding:10px 16px;border-radius:10px;text-decoration:none}
.sp-pc a.add.sp-nlx-soon{background:#1C1F23!important;color:#fff!important;text-align:center}
.sp-pc .bd.sp-nlx-bd{background:#0D0F12;color:#F5B041}
</style>
<script>
(function(){
  var C=<?php echo wp_json_encode(array_map('strval', $closed)); ?>, PID=<?php echo (int) $pid; ?>;
  var HEAD=<?php echo wp_json_encode($head); ?>, SUB=<?php echo wp_json_encode($sub); ?>, CARD=<?php echo wp_json_encode($card); ?>;
  function cards(){
    C.forEach(function(id){
      document.querySelectorAll('.sp-pc[data-id="'+id+'"]').forEach(function(c){
        if(c.dataset.nlx)return; c.dataset.nlx=1;
        var b=c.querySelector('button.add'), u=(c.querySelector('a.im')||{}).href||'#';
        if(b){var a=document.createElement('a');a.className='add sp-nlx-soon';a.href=u;a.textContent=CARD;b.replaceWith(a);}
        var im=c.querySelector('a.im'); if(im&&!im.querySelector('.bd')){var s=document.createElement('span');s.className='bd sp-nlx-bd';s.textContent='Neu';im.appendChild(s);}
      });
    });
  }
  function page(){
    if(!PID||C.indexOf(String(PID))<0)return;
    var bb=document.getElementById('rx-buybox'); if(!bb)return;
    if(!bb.querySelector('.sp-nlx-gate')){
      var slot=bb.querySelector('.rx-buycta')||bb.querySelector('.rx-buysum');
      if(slot){var g=document.createElement('div');g.className='sp-nlx-gate';g.innerHTML='<b>'+HEAD+'</b><span>'+SUB+'</span><a href="#sp-nlh">Früher dabei sein →</a>';slot.parentNode.insertBefore(g,slot.nextSibling);}
    }
    var st=bb.querySelector('.rx-stock'); if(st)st.innerHTML='<span class="rx-stock-dot"></span> '+HEAD.replace('🎃 ','');
    var ad=bb.querySelector('#rx-addons'); if(ad)ad.style.display='none';
    var gl=document.querySelector('.sp-nlx-gate a'); if(gl&&!document.getElementById('sp-nlh'))gl.href='/#sp-nlh';
  }
  function run(){cards();page();}
  run(); document.addEventListener('DOMContentLoaded',run);
  new MutationObserver(function(){cards();}).observe(document.documentElement,{childList:true,subtree:true});
  setTimeout(page,1500);
})();
</script>
    <?php
}, 120);
