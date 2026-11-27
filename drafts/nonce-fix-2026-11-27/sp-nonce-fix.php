<?php
/**
 * Plugin Name: Peptrium – Abgelaufene Store-API-Nonce automatisch erneuern
 * Description: (2026-11-27) Kassen-Fehler-Protokoll: „Nonce ist ungültig.“ (woocommerce_rest_invalid_nonce) bei 15 Besuchern
 *   seit 9.11., fast nur auf /kasse/ am Handy, oft mehrfach über Stunden (Tab bleibt offen → Nonce läuft ab), nur 1 bestellte.
 *   Die Store API schickt bei 403 bereits eine frische Nonce im Header `Nonce` mit. Dieses Skript (wp_head prio 1, also INNERSTER
 *   fetch-Wrapper) wiederholt eine solche Anfrage einmal mit der frischen Nonce (Header + Batch-Body) und ersetzt die alte Nonce
 *   danach in allen weiteren Store-API-Anfragen. Zähler: localStorage-frei, Erfolge als sp_stats-Zeile type='nonce' (ajax sp_nf).
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
    if (is_admin()) return; ?>
<script id="sp-nonce-fix">
(function(){
  if(!window.fetch||window.fetch.__spNF)return;
  var of=window.fetch,stale=[],fresh=null,logged=0;
  function isStore(u){return typeof u==='string'&&u.indexOf('/wc/store/')>-1;}
  function getH(h,k){if(!h)return null;if(typeof h.get==='function')return h.get(k);for(var x in h){if(x.toLowerCase()===k.toLowerCase())return h[x];}return null;}
  function setH(init,k,v){var h=init.headers;if(!h){init.headers={};h=init.headers;}if(typeof h.set==='function'){h.set(k,v);return;}for(var x in h){if(x.toLowerCase()===k.toLowerCase()){h[x]=v;return;}}h[k]=v;}
  function swap(init){if(!fresh||!init)return init;var n=Object.assign({},init);
    if(init.headers&&typeof init.headers.set!=='function')n.headers=Object.assign({},init.headers);
    var cur=getH(n.headers,'Nonce');if(cur&&cur!==fresh&&stale.indexOf(cur)>-1)setH(n,'Nonce',fresh);
    if(typeof n.body==='string')stale.forEach(function(s){if(s&&s!==fresh)n.body=n.body.split(s).join(fresh);});
    return n;}
  function note(){if(logged++)return;try{var fd=new FormData();fd.append('action','sp_nf');fd.append('p',location.pathname);navigator.sendBeacon&&navigator.sendBeacon('<?php echo esc_js(admin_url('admin-ajax.php')); ?>',fd);}catch(e){}}
  var nf=function(input,init){
    if(!isStore(input))return of.apply(this,arguments);
    var self=this,used=swap(init);
    return of.call(self,input,used).then(function(r){
      if(r.status!==403&&r.status!==207)return r;
      var nn=r.headers.get('Nonce');if(!nn)return r;
      return r.clone().json().then(function(j){
        var codes=[];if(j&&j.code)codes.push(j.code);
        if(j&&j.responses)j.responses.forEach(function(x){if(x&&x.body&&x.body.code)codes.push(x.body.code);});
        if(codes.indexOf('woocommerce_rest_invalid_nonce')<0)return r;
        var old=getH(used&&used.headers,'Nonce');if(old&&stale.indexOf(old)<0)stale.push(old);
        try{var cfg=window.wcBlocksMiddlewareConfig;if(cfg&&cfg.storeApiNonce&&stale.indexOf(cfg.storeApiNonce)<0)stale.push(cfg.storeApiNonce);}catch(e){}
        if(nn===old)return r;fresh=nn;
        try{if(window.wcBlocksMiddlewareConfig){wcBlocksMiddlewareConfig.storeApiNonce=nn;wcBlocksMiddlewareConfig.storeApiNonceTimestamp=parseInt(r.headers.get('Nonce-Timestamp')||'0',10)||Math.floor(Date.now()/1000);}}catch(e){}
        try{if(window.wp&&wp.apiFetch&&typeof wp.apiFetch.setNonce==='function')wp.apiFetch.setNonce(r.headers);}catch(e){}
        note();
        return of.call(self,input,swap(init));
      }).catch(function(){return r;});
    });
  };
  nf.__spNF=1;window.fetch=nf;
})();
</script>
<?php }, 1);

/* Zähler: wie oft wurde eine abgelaufene Nonce gerettet */
function sp_nf_ajax() {
    global $wpdb;
    if (function_exists('sp_an_is_bot') && sp_an_is_bot()) wp_die();
    $vh = function_exists('sp_an_visitor_hash') ? sp_an_visitor_hash() : '';
    $wpdb->insert($wpdb->prefix . 'sp_stats', ['ts' => current_time('mysql'), 'day' => current_time('Y-m-d'), 'vh' => $vh, 'type' => 'nonce', 'path' => substr(sanitize_text_field(wp_unslash($_POST['p'] ?? '')), 0, 190)]);
    wp_die();
}
add_action('wp_ajax_sp_nf', 'sp_nf_ajax');
add_action('wp_ajax_nopriv_sp_nf', 'sp_nf_ajax');
