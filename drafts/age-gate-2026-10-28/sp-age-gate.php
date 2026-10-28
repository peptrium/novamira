<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Site-wide entry gate: on first visit (any page), visitors must confirm
 * they're 18+ and buying for lab/research use before they can interact with
 * the site. Modeled 1:1 on ascendbiolabs.com's entry gate, translated to
 * German and restyled to Chrome Mono, per explicit user request.
 *
 * Pure client-side overlay (not a server-side redirect): the real page
 * content is still fully present in the DOM underneath, so this doesn't
 * block search engine indexing - it only visually/interactively blocks
 * human visitors until they confirm, via a 365-day cookie.
 */

add_action('wp_body_open', function () {
    if (is_admin()) {
        return;
    }
    $logo_url = wp_get_attachment_image_url(374, 'medium') ?: wp_get_attachment_url(374);
    ?>
    <div id="sp-gate" role="dialog" aria-modal="true" aria-labelledby="sp-gate-title">
      <div class="sp-gate-backdrop"></div>
      <div class="sp-gate-wrap">
        <div class="sp-gate-brand">
          <?php if ($logo_url): ?><img src="<?php echo esc_url($logo_url); ?>" alt="Peptrium" class="sp-gate-logo" width="84" height="84" /><?php endif; ?>
          <span class="sp-gate-brandname">PEPTRIUM</span>
        </div>
        <div class="sp-gate-card">
          <div class="sp-gate-eyebrow">Nur für Laborforschung</div>
          <h2 id="sp-gate-title" class="sp-gate-title">Bevor es losgeht</h2>
          <p class="sp-gate-sub">Peptrium verkauft ausschließlich Forschungspeptide für die Laborforschung &ndash; nicht für den Gebrauch an Mensch oder Tier.</p>

          <label class="sp-gate-check">
            <span class="sp-gate-check-label">Ich bin <strong>18 Jahre</strong> oder älter</span>
            <input type="checkbox" class="sp-gate-check-input" data-gate-req />
            <span class="sp-gate-switch" aria-hidden="true"></span>
          </label>

          <label class="sp-gate-check">
            <span class="sp-gate-check-label">Ich kaufe nur für <strong>In-vitro-Forschung</strong></span>
            <input type="checkbox" class="sp-gate-check-input" data-gate-req />
            <span class="sp-gate-switch" aria-hidden="true"></span>
          </label>

          <button type="button" class="sp-gate-enter" id="sp-gate-enter" disabled>Weiter zum Shop</button>

          <div class="sp-gate-links">
            <a href="<?php echo esc_url(home_url('/agb/')); ?>">AGB</a>
            <span class="sp-gate-links-sep">&middot;</span>
            <a href="<?php echo esc_url(home_url('/forschungsnutzung/')); ?>">Forschungsnutzung</a>
          </div>
        </div>
      </div>
    </div>
    <?php
}, 6);

add_action('wp_head', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <style id="sp-gate-style">
      /* Redesign 2026-10-28 (Entwurf G1): Logo frei ueber dem verschwommenen Shop, darunter Milchglas-Karte, beides mittig. */
      #sp-gate{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;padding:24px 14px;font-family:'Sora',sans-serif;}
      #sp-gate.is-visible{display:flex;}
      body.sp-gate-locked{overflow:hidden!important;}
      .sp-gate-backdrop{position:absolute;inset:0;background:linear-gradient(180deg,rgba(8,9,11,.40),rgba(8,9,11,.68));-webkit-backdrop-filter:blur(14px);backdrop-filter:blur(14px);}
      .sp-gate-wrap{position:relative;width:100%;max-width:420px;max-height:100%;overflow-y:auto;display:flex;flex-direction:column;align-items:center;box-sizing:border-box;}
      .sp-gate-brand{display:flex;flex-direction:column;align-items:center;margin:0 0 22px;}
      .sp-gate-logo{width:84px;height:84px;object-fit:contain;filter:drop-shadow(0 10px 30px rgba(0,0,0,.6));}
      .sp-gate-brandname{margin-top:10px;font-weight:800;font-size:15px;letter-spacing:.32em;padding-left:.32em;background:linear-gradient(135deg,#8B929B 0%,#F2F3F4 25%,#C7CCD1 50%,#F2F3F4 75%,#8B929B 100%);-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;}
      .sp-gate-card{width:100%;box-sizing:border-box;text-align:center;color:#fff;border-radius:28px;padding:24px 20px 20px;background:rgba(22,25,29,.58);-webkit-backdrop-filter:blur(22px) saturate(140%);backdrop-filter:blur(22px) saturate(140%);border:1px solid rgba(255,255,255,.14);box-shadow:0 30px 60px rgba(0,0,0,.45);}
      .sp-gate-eyebrow{font-size:10.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#B8BEC5;margin-bottom:8px;}
      #sp-gate .sp-gate-title{font-family:'Sora',sans-serif;font-size:26px;line-height:1.15;font-weight:800;color:#fff;margin:0 0 8px;letter-spacing:-.01em;}
      .sp-gate-sub{font-size:13.5px;line-height:1.55;color:#C9CED3;margin:0 0 14px;}
      .sp-gate-check{position:relative;display:flex;align-items:center;gap:12px;text-align:left;padding:12px 0;border-top:1px solid rgba(255,255,255,.12);cursor:pointer;-webkit-tap-highlight-color:transparent;}
      .sp-gate-check-label{flex:1;font-size:13.5px;line-height:1.45;color:#E6E8EA;}
      .sp-gate-check-label strong{color:#fff;font-weight:700;}
      .sp-gate-check-input{position:absolute;opacity:0;width:1px;height:1px;pointer-events:none;}
      .sp-gate-switch{position:relative;flex:0 0 46px;height:28px;border-radius:99px;background:#2A2E33;border:1px solid #3A3F45;box-sizing:border-box;transition:background .2s ease,border-color .2s ease;}
      .sp-gate-switch::after{content:'';position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#8E959E;transition:left .2s ease,background .2s ease;}
      .sp-gate-check-input:checked ~ .sp-gate-switch{background:#F2F3F4;border-color:#F2F3F4;}
      .sp-gate-check-input:checked ~ .sp-gate-switch::after{left:21px;background:#0D0F12;}
      .sp-gate-check-input:focus-visible ~ .sp-gate-switch{outline:2px solid #fff;outline-offset:2px;}
      #sp-gate .sp-gate-enter{display:block;width:100%;margin-top:14px;padding:16px 20px;border:0!important;border-radius:16px!important;font-family:'Sora',sans-serif;font-weight:700;font-size:15.5px;line-height:1.2;box-shadow:none!important;background:rgba(255,255,255,.14)!important;color:rgba(255,255,255,.45)!important;cursor:not-allowed;transition:background .2s ease,color .2s ease,transform .15s ease;}
      #sp-gate .sp-gate-enter:not(:disabled){background:#fff!important;color:#0D0F12!important;cursor:pointer;}
      #sp-gate .sp-gate-enter:not(:disabled):active{transform:scale(.98);}
      #sp-gate .sp-gate-enter:focus,#sp-gate .sp-gate-enter:hover{outline:none;}
      .sp-gate-links{margin-top:12px;font-size:11.5px;color:#9AA1A9;}
      .sp-gate-links a{color:#9AA1A9;text-decoration:none;}
      .sp-gate-links a:hover{color:#fff;text-decoration:underline;}
      .sp-gate-links-sep{margin:0 6px;}
      @media (max-height:640px){.sp-gate-logo{width:60px;height:60px}.sp-gate-brand{margin-bottom:14px}#sp-gate .sp-gate-title{font-size:22px}}
    </style>
    <?php
}, 6);

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <script>
    (function () {
      var COOKIE_NAME = 'sp_age_gate';
      var gate = document.getElementById('sp-gate');
      if (!gate) { return; }

      function hasCookie(name) {
        return document.cookie.split(';').some(function (c) {
          return c.trim().indexOf(name + '=') === 0;
        });
      }

      if (hasCookie(COOKIE_NAME)) {
        return;
      }

      gate.classList.add('is-visible');
      document.body.classList.add('sp-gate-locked');

      /*
       * Das Cookie-Banner (Real Cookie Banner) ist ein natives <dialog>, das
       * der Browser automatisch in eine spezielle "Top Layer" hebt, die
       * IMMER ueber jedem normalen z-index liegt - landet es dort waehrend
       * unser Gate noch offen ist, blockiert es Klicks auf der kompletten
       * Seite (nicht nur auf seinem sichtbaren Kasten), auch auf unser Gate.
       *
       * Einmaliges, zeitlich begrenztes Abfragen (max. 5s, 200ms-Takt; danach
       * stoppt das Intervall in jedem Fall von selbst) statt eines
       * dauerhaften Beobachters: sobald das Banner-Dialog auftaucht, wird es
       * genau einmal geschlossen (nicht wiederholt!) und die Referenz
       * gemerkt, um es nach Gate-Bestaetigung genau einmal wieder zu oeffnen.
       * Gegen das echte Plugin getestet: es "wehrt sich" nicht und bleibt
       * nach dem Wiederoeffnen voll funktionsfaehig.
       */
      var rcbDialog = null;
      var rcbPollAttempts = 0;
      var rcbPollTimer = setInterval(function () {
        rcbPollAttempts++;
        var dialog = document.querySelector('[consent-skip-blocker] dialog[open]');
        if (dialog) {
          rcbDialog = dialog;
          try { dialog.close(); } catch (e) {}
          clearInterval(rcbPollTimer);
        } else if (rcbPollAttempts >= 25) {
          clearInterval(rcbPollTimer);
        }
      }, 200);

      var checks = gate.querySelectorAll('[data-gate-req]');
      var enterBtn = document.getElementById('sp-gate-enter');

      function updateState() {
        var allChecked = Array.prototype.every.call(checks, function (c) { return c.checked; });
        enterBtn.disabled = !allChecked;
      }

      checks.forEach(function (c) {
        c.addEventListener('change', updateState);
      });

      enterBtn.addEventListener('click', function () {
        if (enterBtn.disabled) { return; }
        var maxAge = 60 * 60 * 24 * 365;
        document.cookie = COOKIE_NAME + '=1; max-age=' + maxAge + '; path=/; SameSite=Lax';
        gate.classList.remove('is-visible');
        document.body.classList.remove('sp-gate-locked');
        // Cookie-Banner (falls zwischenzeitlich einmalig geschlossen, siehe
        // oben) jetzt wieder oeffnen - genau einmal, keine Wiederholung.
        if (rcbDialog) {
          try { rcbDialog.showModal(); } catch (e) {}
        }
      });
    })();
    </script>
    <?php
}, 6);
