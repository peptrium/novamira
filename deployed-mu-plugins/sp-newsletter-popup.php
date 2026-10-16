<?php
/**
 * Plugin Name: SP Newsletter-Pop-up
 * Description: Pop-up "10 % auf deine erste Bestellung" in 3 Schritten: Frage (Ja/Nein) -> E-Mail -> "Fast geschafft" mit Postfach-Button.
 *
 * (2026-10-16) Anmeldung laeuft ueber die bestehende Double-Opt-in-Anmeldung (admin-ajax sp_nl_subscribe, Quelle "Popup").
 * Der Code wird NICHT im Pop-up gezeigt - erst nach Bestaetigung (Danke-Seite + Willkommensmail).
 * Erscheint: Erstbesucher, nach 12 s oder am Computer beim Verlassen (Maus nach oben),
 * erst nach Altersabfrage und Cookie-Banner, nie in Warenkorb/Kasse/Konto. "Nein danke"/X -> 7 Tage Ruhe,
 * nach Anmeldung nie wieder (Cookie sp_nlp_done). Eingeloggte Kunden mit Bestellung sehen es nicht.
 * LIVE nur mit Option sp_nlp_live = 1. Vorschau: ?sp_popup=vorschau (Cookie 24 h, erscheint nach 3 s, ignoriert die 7-Tage-Ruhe).
 * Kein Postfach-Button (Nutzer-Entscheidung 2026-10-16: lieber keiner als einer, der auf manchen Handys nicht geht).
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    if (isset($_GET['sp_popup']) && $_GET['sp_popup'] === 'vorschau') {
        setcookie('sp_nlp_preview', '1', time() + DAY_IN_SECONDS, '/', '', is_ssl(), true);
        $_COOKIE['sp_nlp_preview'] = '1';
    }
});

function sp_nlp_preview() {
    return !empty($_COOKIE['sp_nlp_preview']);
}

function sp_nlp_should_render() {
    if (is_admin() || wp_doing_ajax()) {
        return false;
    }
    if (!get_option('sp_nlp_live') && !sp_nlp_preview()) {
        return false;
    }
    if (function_exists('is_checkout') && (is_checkout() || is_cart() || is_account_page() || is_wc_endpoint_url('order-received'))) {
        return false;
    }
    if (!empty($_GET['sp_nl']) || !empty($_GET['sp_nl_join'])) {
        return false; // Danke-/Anmeldeseiten
    }
    if (!sp_nlp_preview() && !empty($_COOKIE['sp_nlp_done'])) {
        return false;
    }
    if (!sp_nlp_preview() && is_user_logged_in()) {
        $u = wp_get_current_user();
        if (function_exists('wc_get_customer_order_count') && wc_get_customer_order_count($u->ID) > 0) {
            return false;
        }
        if (class_exists('\FluentCrm\App\Models\Subscriber')) {
            $s = \FluentCrm\App\Models\Subscriber::where('email', strtolower($u->user_email))->first();
            if ($s && in_array($s->status, ['subscribed', 'unsubscribed'], true)) {
                return false;
            }
        }
    }
    return true;
}

add_action('wp_footer', function () {
    if (!sp_nlp_should_render()) {
        return;
    }
    $logo = home_url('/wp-content/uploads/2026/08/IMG_2644-cropped-300x300.png');
    $privacy = get_permalink(410);
    $cfg = [
        'ajax' => admin_url('admin-ajax.php'),
        'preview' => sp_nlp_preview(),
        'delay' => sp_nlp_preview() ? 3000 : 12000, // Nutzer: 12 Sekunden
    ];
    ?>
<style id="sp-nlp-css">
#sp-nlp{position:fixed;inset:0;z-index:2147483600;display:none;align-items:flex-start;justify-content:center;padding:max(110px,18vh) 14px 20px;font-family:inherit}
#sp-nlp.on{display:flex}
#sp-nlp .bd{position:absolute;inset:0;background:rgba(5,6,8,.62);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);opacity:0;transition:opacity .25s}
#sp-nlp.on .bd{opacity:1}
#sp-nlp .fr{position:relative;width:100%;max-width:420px;border-radius:24px;padding:2px;background:linear-gradient(135deg,#F2F4F6 0%,#5A616B 35%,#1A1D21 60%,#C9CED6 100%);box-shadow:0 24px 60px rgba(0,0,0,.6);transform:translateY(16px);opacity:0;transition:transform .3s,opacity .3s}
#sp-nlp.on .fr{transform:none;opacity:1}
#sp-nlp .in{background:#0B0D10;border-radius:22px;padding:22px 20px 18px;color:#fff;text-align:left}
#sp-nlp *{box-sizing:border-box}
#sp-nlp .x{position:absolute;right:14px;top:14px;width:34px;height:34px;border-radius:50%;background:#1A1D21;border:1px solid #2C3138;color:#C9CED6;font-size:16px;line-height:1;display:flex;align-items:center;justify-content:center;cursor:pointer;padding:0;box-shadow:none}
#sp-nlp .hd{display:flex;align-items:center;gap:8px}
#sp-nlp .hd img{width:28px;height:28px;display:block}
#sp-nlp .hd span{font-size:11px;font-weight:800;letter-spacing:.3em;color:#E6E8EB}
#sp-nlp h2{font-size:27px;line-height:1.15;font-weight:800;color:#fff;margin:16px 0 10px;letter-spacing:-.01em;padding-right:10px}
#sp-nlp h2 em{font-style:normal;background:linear-gradient(180deg,#FFFFFF 0%,#E6E8EB 45%,#9AA0A8 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
#sp-nlp p{font-size:14px;line-height:1.55;color:#A3A9B1;margin:0 0 16px}
#sp-nlp p b{color:#fff}
#sp-nlp input[type=email]{width:100%;height:54px;border-radius:14px;border:1.5px solid #3A4048;background:#14171C;color:#fff;font-size:16px;padding:0 16px;outline:none;margin:0}
#sp-nlp input[type=email]:focus{border-color:#C9CED6}
#sp-nlp .b{display:flex;align-items:center;justify-content:center;width:100%;height:56px;border-radius:14px;border:0;font-size:16px;font-weight:800;cursor:pointer;text-decoration:none;background:linear-gradient(135deg,#FFFFFF 0%,#D9DDE2 50%,#AEB4BC 100%)!important;color:#0D0F12!important;box-shadow:none!important;margin:10px 0 0}
#sp-nlp .b:disabled{opacity:.6}
#sp-nlp .no{display:block;margin:14px auto 0;background:none!important;border:0;color:#7D848D!important;font-size:13px;text-decoration:underline;cursor:pointer;box-shadow:none!important;padding:4px}
#sp-nlp .lg{font-size:11px;line-height:1.5;color:#7D848D;margin-top:12px;text-align:center}
#sp-nlp .lg a{color:#7D848D}
#sp-nlp .er{color:#FF8A80;font-size:13px;margin:8px 0 0;display:none}
#sp-nlp .cd{display:flex;align-items:center;justify-content:space-between;border:1.5px dashed #3A4048;border-radius:14px;padding:12px 14px;margin:0 0 14px}
#sp-nlp .cd small{font-size:10px;font-weight:700;letter-spacing:.16em;color:#7D848D}
#sp-nlp .cd b{font-family:'SFMono-Regular',Consolas,Menlo,monospace;font-size:17px;font-weight:800;color:#D9DDE2;letter-spacing:.06em;filter:blur(4px);user-select:none}
#sp-nlp .ic{font-size:42px;margin:12px 0 2px}
#sp-nlp .rs{font-size:12px;color:#7D848D;text-align:center;margin-top:14px}
#sp-nlp .rs button{background:none!important;border:0;color:#C9CED6!important;text-decoration:underline;font-size:12px;cursor:pointer;padding:0;box-shadow:none!important}
#sp-nlp .st{display:none}#sp-nlp .st.on{display:block}
#sp-nlp .hp{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}
</style>
<div id="sp-nlp" role="dialog" aria-modal="true" aria-labelledby="sp-nlp-h" aria-hidden="true">
  <div class="bd" data-close></div>
  <div class="fr"><div class="in">
    <button type="button" class="x" data-close aria-label="Schließen">✕</button>
    <div class="hd"><img src="<?php echo esc_url($logo); ?>" alt=""><span>PEPTRIUM INSIDER</span></div>
    <div class="st on" data-s="1">
      <h2 id="sp-nlp-h">Sichere dir <em>10 % Rabatt</em><br>auf deine erste Bestellung</h2>
      <p>Werde Peptrium-Insider und erhalte deinen persönlichen Code – dazu Neuheiten und neue Chargen zuerst.</p>
      <button type="button" class="b" data-yes>Ja, Code holen</button>
      <button type="button" class="no" data-close>Nein danke</button>
    </div>
    <form class="st" data-s="2" novalidate>
      <h2>Sichere dir <em>10 % Rabatt</em><br>auf deine erste Bestellung</h2>
      <p>Wohin dürfen wir deinen Code schicken?</p>
      <input type="email" name="email" placeholder="Deine E-Mail-Adresse" autocomplete="email" inputmode="email" required>
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="er" data-err></div>
      <button type="submit" class="b">Code anfordern →</button>
      <div class="lg">Mit der Anmeldung erhältst du unseren Newsletter (Bestätigung per E-Mail). Abmeldung jederzeit. <a href="<?php echo esc_url($privacy); ?>" target="_blank" rel="noopener">Datenschutz</a></div>
    </form>
    <div class="st" data-s="3">
      <div class="ic">📬</div>
      <h2 style="margin-top:4px">Fast geschafft!</h2>
      <p>Wir haben dir eine Mail an <b data-mail></b> geschickt. Tippe dort auf <b>„Anmeldung bestätigen“</b> – dann ist dein Code freigeschaltet.</p>
      <div class="cd"><small>DEIN CODE</small><b aria-hidden="true">HALLO-XXXXX</b></div>
      <div class="rs" data-rs>Keine Mail bekommen? Schau im Spam-Ordner nach oder <button type="button" data-resend>erneut senden</button>.</div>
    </div>
    <div class="st" data-s="4">
      <h2>Du bist schon dabei ✓</h2>
      <p>Diese E-Mail-Adresse ist bereits für unseren Newsletter angemeldet. Deinen Code findest du in der Willkommensmail.</p>
      <button type="button" class="b" data-close>Weiter einkaufen</button>
    </div>
  </div></div>
</div>
<script>
(function(){
  var C=<?php echo wp_json_encode($cfg); ?>, M=document.getElementById('sp-nlp'); if(!M) return;
  var KEY='sp_nlp_closed', shown=false, email='';
  function ls(k,v){try{if(v===undefined)return localStorage.getItem(k);localStorage.setItem(k,v);}catch(e){return null;}}
  function cookie(n,v,d){document.cookie=n+'='+v+';path=/;max-age='+(d*86400)+';SameSite=Lax';}
  if(!C.preview){var until=parseInt(ls(KEY)||'0',10); if(until>Date.now()) return; if(/(^|; )sp_nlp_closed=1/.test(document.cookie)) return;}
  function step(n){M.querySelectorAll('.st').forEach(function(s){s.classList.toggle('on',s.getAttribute('data-s')==String(n));});}
  function blocked(){
    var g=document.getElementById('sp-gate'); if(g&&g.offsetParent!==null&&getComputedStyle(g).display!=='none') return true;
    var c=document.querySelector('[consent-skip-blocker]'); if(c&&c.offsetParent!==null) return true;
    return document.body.classList.contains('sp-cd-on'); // Warenkorb-Schublade offen
  }
  function open(){ if(shown) return; if(blocked()){setTimeout(open,800);return;} shown=true; M.style.display='flex'; requestAnimationFrame(function(){M.classList.add('on');}); M.setAttribute('aria-hidden','false'); document.documentElement.style.overflow='hidden'; }
  function close(){ M.classList.remove('on'); M.setAttribute('aria-hidden','true'); document.documentElement.style.overflow=''; setTimeout(function(){M.style.display='none';},250);
    if(!C.preview){ls(KEY,String(Date.now()+7*86400000)); cookie('sp_nlp_closed',1,7);} }
  setTimeout(open,C.delay);
  if(window.matchMedia('(pointer:fine)').matches){document.addEventListener('mouseout',function(e){if(!e.relatedTarget&&e.clientY<=0) open();});}
  M.querySelectorAll('[data-close]').forEach(function(b){b.addEventListener('click',close);});
  document.addEventListener('keydown',function(e){if(e.key==='Escape'&&M.classList.contains('on')) close();});
  M.querySelector('[data-yes]').addEventListener('click',function(){step(2);setTimeout(function(){var i=M.querySelector('input[type=email]');i&&i.focus();},50);});
  function send(btn,cb){
    var f=M.querySelector('form'), fd=new FormData(); fd.append('action','sp_nl_subscribe'); fd.append('email',email); fd.append('website',f.website.value); fd.append('src','popup');
    btn&&(btn.disabled=true);
    fetch(C.ajax,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){btn&&(btn.disabled=false);cb(j);}).catch(function(){btn&&(btn.disabled=false);cb({success:false,data:{msg:'Verbindung fehlgeschlagen – bitte versuch es noch einmal.'}});});
  }
  M.querySelector('form').addEventListener('submit',function(e){
    e.preventDefault(); var f=this, err=M.querySelector('[data-err]'); email=(f.email.value||'').trim(); err.style.display='none';
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){err.textContent='Bitte gib eine gültige E-Mail-Adresse ein.';err.style.display='block';return;}
    send(f.querySelector('.b'),function(j){
      if(!j||!j.success){err.textContent=(j&&j.data&&j.data.msg)||'Das hat nicht geklappt.';err.style.display='block';return;}
      cookie('sp_nlp_done',1,365);
      if(j.data&&j.data.state==='already'){step(4);return;}
      M.querySelector('[data-mail]').textContent=email; step(3);
    });
  });
  M.querySelector('[data-resend]').addEventListener('click',function(){
    var rs=M.querySelector('[data-rs]');
    send(null,function(j){ rs.textContent=(j&&j.data&&j.data.state==='wait')?'Die Mail ist gerade erst rausgegangen – schau in ein paar Minuten nochmal nach, auch im Spam-Ordner.':'Wir haben die Mail erneut gesendet.'; });
  });
})();
</script>
    <?php
}, 60);
