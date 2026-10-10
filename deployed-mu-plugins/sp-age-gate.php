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
    $logo_url = wp_get_attachment_url(374);
    ?>
    <div id="sp-gate" role="dialog" aria-modal="true" aria-labelledby="sp-gate-title">
      <div class="sp-gate-backdrop"></div>
      <div class="sp-gate-card">
        <div class="sp-gate-banner">
          <?php if ($logo_url): ?><span class="sp-gate-logo-chip"><img src="<?php echo esc_url($logo_url); ?>" alt="Peptrium" class="sp-gate-logo" /></span><?php endif; ?>
          <span class="sp-gate-brandname">Peptrium</span>
        </div>
        <div class="sp-gate-body">

        <h2 id="sp-gate-title" class="sp-gate-title">Nur für <span class="sp-gate-accent">Forschungszwecke.</span></h2>
        <p class="sp-gate-sub">Die Produkte auf dieser Website werden ausschließlich für die wissenschaftliche Laborforschung verkauft. Sie sind nicht für den menschlichen Verzehr, medizinische, tierärztliche oder sonstige In-vivo-Anwendung bestimmt.</p>

        <label class="sp-gate-check">
          <input type="checkbox" class="sp-gate-check-input" data-gate-req />
          <span class="sp-gate-check-box"></span>
          <span class="sp-gate-check-label">Ich bin mindestens <strong>18 Jahre alt</strong>.</span>
        </label>

        <label class="sp-gate-check">
          <input type="checkbox" class="sp-gate-check-input" data-gate-req />
          <span class="sp-gate-check-box"></span>
          <span class="sp-gate-check-label">Ich bestätige, dass ich als <strong>Forscher:in</strong> ausschließlich für <strong>In-vitro-/Laborforschung</strong> kaufe &ndash; nicht für den menschlichen oder tierärztlichen Gebrauch.</span>
        </label>

        <button type="button" class="sp-gate-enter" id="sp-gate-enter" disabled>Website betreten</button>

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
      #sp-gate{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;padding:20px;font-family:'Sora',sans-serif;}
      #sp-gate.is-visible{display:flex;}
      body.sp-gate-locked{overflow:hidden!important;}
      .sp-gate-backdrop{position:absolute;inset:0;background:rgba(13,15,18,.72);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px);}
      .sp-gate-card{position:relative;width:100%;max-width:440px;background:#FFFFFF;border:1px solid #DCDEE0;border-radius:20px;box-shadow:0 30px 70px -20px rgba(13,15,18,.45);text-align:center;max-height:calc(100vh - 40px);overflow-y:auto;box-sizing:border-box;}
      .sp-gate-banner{display:flex;align-items:center;justify-content:center;gap:6px;padding:22px 30px;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);border-radius:20px 20px 0 0;}
      .sp-gate-body{padding:26px 30px 28px;}
      .sp-gate-logo-chip{display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;flex-shrink:0;}
      .sp-gate-logo{width:100%;height:100%;object-fit:contain;flex-shrink:0;filter:brightness(1.35) contrast(0.9);}
      .sp-gate-brandname{font-family:'Sora',sans-serif;font-weight:700;font-size:20px;background:linear-gradient(135deg,#8B929B 0%,#F2F3F4 25%,#C7CCD1 50%,#F2F3F4 75%,#8B929B 100%);-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;white-space:nowrap;letter-spacing:-.01em;}
      .sp-gate-title{font-size:clamp(22px,5vw,27px);font-weight:800;color:#0D0F12;margin:0 0 14px;line-height:1.25;text-wrap:balance;}
      .sp-gate-accent{background-image:linear-gradient(100deg,#0D0F12 0%,#4B5157 35%,#0D0F12 50%,#4B5157 65%,#0D0F12 100%);background-size:220% auto;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent;animation:sp-gate-shine 3.2s linear infinite;}
      @keyframes sp-gate-shine{0%{background-position:200% center}100%{background-position:0% center}}
      @media (prefers-reduced-motion:reduce){.sp-gate-accent{animation:none;background-position:0 center;}}
      .sp-gate-sub{font-size:13.5px;line-height:1.65;color:#4B5157;margin:0 0 22px;}
      .sp-gate-check{display:flex;align-items:flex-start;gap:11px;text-align:left;border:1px solid #DCDEE0;border-radius:12px;padding:13px 14px;margin-bottom:12px;cursor:pointer;transition:border-color .15s ease,background-color .15s ease;}
      .sp-gate-check:hover{border-color:#A8B0B9;background:#F9FAFA;}
      .sp-gate-check-input{position:absolute;opacity:0;width:1px;height:1px;}
      .sp-gate-check-box{flex-shrink:0;width:19px;height:19px;border-radius:5px;border:1.5px solid #DCDEE0;background:#FFFFFF;position:relative;margin-top:1px;transition:border-color .15s ease,background .15s ease;}
      .sp-gate-check-box::after{content:'';position:absolute;left:5px;top:1px;width:6px;height:10px;border:solid #FFFFFF;border-width:0 2px 2px 0;transform:rotate(45deg);opacity:0;}
      .sp-gate-check-input:checked ~ .sp-gate-check-box{background:#0D0F12;border-color:#0D0F12;}
      .sp-gate-check-input:checked ~ .sp-gate-check-box::after{opacity:1;}
      .sp-gate-check-input:focus-visible ~ .sp-gate-check-box{outline:2px solid #0D0F12;outline-offset:2px;}
      .sp-gate-check-label{font-size:13.5px;line-height:1.5;color:#0D0F12;}
      .sp-gate-enter{width:100%;margin-top:8px;padding:14px 20px;border:none;border-radius:11px;background:#DCDEE0;color:#8a9099;font-family:'Sora',sans-serif;font-weight:700;font-size:15px;cursor:not-allowed;transition:background .2s ease,color .2s ease,transform .15s ease;}
      .sp-gate-enter:not(:disabled){background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;cursor:pointer;}
      .sp-gate-enter:not(:disabled):hover{transform:translateY(-1px);}
      .sp-gate-links{margin-top:16px;font-size:12px;color:#8a9099;}
      .sp-gate-links a{color:#8a9099;text-decoration:underline;text-underline-offset:2px;}
      .sp-gate-links a:hover{color:#0D0F12;}
      .sp-gate-links-sep{margin:0 8px;}
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
