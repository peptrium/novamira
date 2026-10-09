<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * "Willkommen zurück" login/register experience for /mein-konto/, matching
 * the structure of certapeptides.com/auth (per user-supplied screenshots,
 * 2026-08-18) but in Chrome Mono and WITHOUT a Google sign-in option (the
 * user explicitly excluded it) and WITHOUT a magic-link/passwordless login
 * (the user chose to skip that - real password auth only).
 *
 * WooCommerce's stock registration form only has email + password (and
 * optionally hides password behind an auto-generate setting); it has no
 * first/last name or password-confirmation field. Those are added here as
 * real functional fields (rendered, validated, saved), not just styling.
 * Everything is guarded with is_account_page() so none of this leaks onto
 * the checkout page's mini login prompt, which reuses the same
 * myaccount/form-login.php template.
 */

add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain !== 'woocommerce' || !function_exists('is_account_page') || !is_account_page()) {
        return $translated;
    }

    switch ($text) {
        case 'Username or email address':
            return 'E-Mail';
        case 'Lost your password?':
            return 'Passwort vergessen? Hier zurücksetzen';
        case 'Log in':
            return 'Anmelden';
        case 'Register':
            return 'Konto erstellen';
    }

    return $translated;
}, 10, 3);

add_action('woocommerce_before_customer_login_form', function () {
    if (!is_account_page()) {
        return;
    }
    ?>
    <div class="sp-auth-hero">
      <div class="sp-auth-eyebrow">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Kontozugang
      </div>
      <h1>Willkommen</h1>
      <p>Melden Sie sich an, um Bestellungen zu verfolgen, Adressen zu speichern und schneller zur Kasse zu gehen. Neu hier? Erstellen Sie in Sekunden ein Konto.</p>
    </div>
    <div class="sp-auth-tabs" id="sp-auth-tabs">
      <button type="button" class="sp-auth-tab is-active" data-tab="login">Anmelden</button>
      <button type="button" class="sp-auth-tab" data-tab="register">Konto erstellen</button>
    </div>
    <?php
}, 10);

add_action('woocommerce_register_form_start', function () {
    if (!is_account_page()) {
        return;
    }
    ?>
    <p class="form-row form-row-wide sp-auth-name-row">
      <span class="sp-auth-name-field">
        <label for="sp_reg_first_name">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
          Vorname
        </label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="sp_reg_first_name" value="<?php echo (!empty($_POST['first_name'])) ? esc_attr(wp_unslash($_POST['first_name'])) : ''; ?>" required />
      </span>
      <span class="sp-auth-name-field">
        <label for="sp_reg_last_name">Nachname</label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="sp_reg_last_name" value="<?php echo (!empty($_POST['last_name'])) ? esc_attr(wp_unslash($_POST['last_name'])) : ''; ?>" required />
      </span>
    </p>
    <?php
}, 10);

add_action('woocommerce_register_form', function () {
    if (!is_account_page()) {
        return;
    }
    $terms_url = get_permalink(408) ?: home_url('/agb/');
    $privacy_url = get_permalink(410) ?: home_url('/datenschutz/');
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
      <label for="sp_reg_password_confirm">Passwort bestätigen</label>
      <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_confirm" id="sp_reg_password_confirm" placeholder="Passwort wiederholen" autocomplete="new-password" required />
    </p>
    <p class="sp-auth-legal">
      Mit der Erstellung eines Kontos stimmen Sie unseren <a href="<?php echo esc_url($terms_url); ?>">Nutzungsbedingungen</a> und unserer <a href="<?php echo esc_url($privacy_url); ?>">Datenschutzerklärung</a> zu. Alle Produkte werden ausschließlich für Forschungszwecke verkauft.
    </p>
    <?php
}, 10);

add_filter('woocommerce_registration_errors', function ($errors, $username, $email) {
    /**
     * Vor-/Nachname nur dort selbst pruefen, wo unser eigenes Formular auf
     * /mein-konto/ sie per klassischem $_POST mitschickt (Felder vorhanden).
     *
     * FIX 09.10.2026 (Kundenmeldung: Konto erstellen an der Kasse ging nicht,
     * immer "Bitte gib deinen Vornamen an"): Der Checkout-Block legt das Konto
     * ueber die Store API an und uebergibt den Namen DIREKT an
     * wc_create_new_customer() (billing_address.first_name/last_name) - es gibt
     * dort kein $_POST, und der fruehere Fallback ueber WC()->customer war in
     * der Praxis leer. Die Kasse erzwingt Vor-/Nachname bereits selbst als
     * Pflichtfelder der Adresse, eine zweite Pruefung hier blockierte nur die
     * Kontoerstellung.
     */
    $from_own_form = isset($_POST['first_name']) || isset($_POST['last_name']);
    if ($from_own_form) {
        if (trim((string) wp_unslash($_POST['first_name'] ?? '')) === '') {
            $errors->add('first_name_required', 'Bitte gib deinen Vornamen an.');
        }
        if (trim((string) wp_unslash($_POST['last_name'] ?? '')) === '') {
            $errors->add('last_name_required', 'Bitte gib deinen Nachnamen an.');
        }
    }
    if (isset($_POST['password']) && isset($_POST['password_confirm']) && $_POST['password'] !== $_POST['password_confirm']) {
        $errors->add('password_mismatch', 'Die Passwörter stimmen nicht überein.');
    }

    return $errors;
}, 10, 3);

/**
 * Gleicher "Mind. 6 Zeichen"-Hinweis wie beim eigenen Registrierungsformular
 * oben, jetzt auch am Checkout - damit beide Stellen zur Kontoerstellung
 * konsistent wirken statt nur die eine mit sichtbarem Hinweis dazustehen.
 *
 * Der Checkout laeuft hier NICHT ueber das klassische WooCommerce-Formular
 * (woocommerce_checkout_fields greift hier nicht), sondern ueber den neuen
 * React-basierten Checkout-Block - das Passwortfeld samt "Konto erstellen"-
 * Checkbox wird erst nach dem Laden clientseitig ins DOM gerendert. Deshalb
 * wie schon beim Abo-Umschalter per JS von aussen angehaengt, sobald das
 * Feld erscheint, statt ueber einen PHP-Feld-Filter.
 */
add_action('wp_footer', function () {
    if (!is_checkout()) {
        return;
    }
    ?>
    <script>
    (function(){
      // Beim Ein-/Ausklappen von "Konto erstellen" baut React den
      // Passwort-Wrapper jedes Mal frisch auf (unmount + neues Element).
      // Ein zuvor per insertAdjacentElement angehaengter Hinweis ist React
      // dabei unbekannt und wird nicht mitentfernt - ohne Aufraeumen haeufen
      // sich die Hinweise bei jedem erneuten Haken-Setzen. Deshalb bei jedem
      // Tick: verwaiste Hinweise (ohne noch lebenden Passwort-Wrapper direkt
      // davor) entfernen, dann genau einen frischen Hinweis je aktuell
      // sichtbarem Passwort-Wrapper sicherstellen.
      function tick(){
        var wraps = document.querySelectorAll('.wc-block-components-address-form__password');
        var validHints = [];
        wraps.forEach(function(pwWrap){
          var hint = pwWrap.nextElementSibling;
          if (!hint || !hint.classList.contains('sp-checkout-pw-hint')) {
            hint = document.createElement('span');
            hint.className = 'sp-checkout-pw-hint';
            hint.textContent = 'Mind. 6 Zeichen';
            hint.style.cssText = 'display:block;margin:-10px 0 16px;font-size:12px;color:#8A9099;font-family:Sora,sans-serif';
            pwWrap.insertAdjacentElement('afterend', hint);
          }
          validHints.push(pwWrap.nextElementSibling);
        });
        document.querySelectorAll('.sp-checkout-pw-hint').forEach(function(hint){
          if (validHints.indexOf(hint) === -1) {
            hint.remove();
          }
        });
      }
      var observer = new MutationObserver(tick);
      observer.observe(document.body, {childList:true, subtree:true});
      tick();
    })();
    </script>
    <?php
}, 20);

add_action('woocommerce_created_customer', function ($customer_id) {
    $first_name = !empty($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
    $last_name = !empty($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';

    if ($first_name === '' && $last_name === '') {
        return;
    }

    $user_data = ['ID' => $customer_id];
    if ($first_name !== '') {
        $user_data['first_name'] = $first_name;
    }
    if ($last_name !== '') {
        $user_data['last_name'] = $last_name;
    }
    $user_data['display_name'] = trim($first_name . ' ' . $last_name) ?: null;
    $user_data = array_filter($user_data, function ($v) {
        return $v !== null;
    });

    wp_update_user($user_data);
});

add_action('wp_footer', function () {
    if (!function_exists('is_account_page') || !is_account_page() || is_user_logged_in()) {
        return;
    }
    ?>
    <style id="sp-auth-style">
      body.woocommerce-account .entry-content{padding-bottom:80px}
      .sp-auth-hero{max-width:640px;margin:40px auto 32px;text-align:center}
      .sp-auth-eyebrow{display:inline-flex;align-items:center;gap:7px;background:#F2F3F4;color:#4B5157;padding:6px 13px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:16px}
      .sp-auth-eyebrow svg{width:13px;height:13px;color:#0D0F12}
      .sp-auth-hero h1{font-family:'Sora',sans-serif;font-size:32px;font-weight:800;color:#0D0F12;margin:0 0 12px}
      .sp-auth-hero p{font-size:14.5px;color:#4B5157;line-height:1.65;margin:0 auto;max-width:480px}

      .sp-auth-tabs{display:flex;background:#F2F3F4;border-radius:12px;padding:5px;gap:4px;max-width:420px;margin:0 auto 28px}
      .sp-auth-tab{flex:1;padding:12px 16px;border:none;background:transparent;border-radius:9px;font-family:'Sora',sans-serif;font-weight:700;font-size:13.5px;color:#4B5157;cursor:pointer;transition:background .15s ease,color .15s ease,box-shadow .15s ease}
      .sp-auth-tab.is-active{background:#FFFFFF;color:#0D0F12;box-shadow:0 1px 3px rgba(13,15,18,.12)}

      #customer_login .u-column1 > h2,
      #customer_login .u-column2 > h2{display:none}
      #customer_login .required{display:none}
      #customer_login.sp-auth-ready{display:block}
      #customer_login.sp-auth-ready .u-column2{display:none}
      #customer_login.sp-auth-ready.sp-auth-show-register .u-column1{display:none}
      #customer_login.sp-auth-ready.sp-auth-show-register .u-column2{display:block}

      .sp-auth-name-row{display:flex;gap:16px}
      .sp-auth-name-field{flex:1;display:flex;flex-direction:column}
      .sp-auth-name-field label{display:block;font-size:12.5px;font-weight:700;color:#4B5157;text-transform:uppercase;letter-spacing:.02em;margin-bottom:6px}
      .sp-auth-name-field label svg{width:15px;height:15px;color:#0D0F12;margin-right:6px;vertical-align:-3px}
      .sp-auth-name-field input{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid #DCDEE0;border-radius:9px;background:#FFFFFF;color:#0D0F12;font-family:'Sora',sans-serif;font-size:14px}
      .sp-auth-name-field input:focus{outline:none;border-color:#0D0F12}
      @media(max-width:480px){.sp-auth-name-row{flex-direction:column;gap:16px}}

      .sp-auth-legal{font-size:12px;color:#9CA3AF;line-height:1.6;text-align:center;margin:14px 0 0}
      .sp-auth-legal a{color:#4B5157;text-decoration:underline;text-decoration-color:#A8B0B9}

      .sp-auth-field-icon{display:inline-flex;margin-right:6px;vertical-align:-3px}
      .sp-auth-field-icon svg{width:15px;height:15px;color:#0D0F12}

      .sp-auth-btn-arrow{display:inline-flex;margin-left:2px}
      .sp-auth-btn-arrow svg{width:15px;height:15px}
    </style>
    <script>
    (function(){
      var wrap = document.getElementById('customer_login');
      var tabs = document.getElementById('sp-auth-tabs');
      if(!wrap || !tabs){ return; }
      wrap.classList.add('sp-auth-ready');

      tabs.querySelectorAll('.sp-auth-tab').forEach(function(tab){
        tab.addEventListener('click', function(){
          tabs.querySelectorAll('.sp-auth-tab').forEach(function(t){ t.classList.remove('is-active'); });
          tab.classList.add('is-active');
          wrap.classList.toggle('sp-auth-show-register', tab.getAttribute('data-tab') === 'register');
        });
      });

      if(window.location.hash === '#register'){
        var regTab = tabs.querySelector('[data-tab="register"]');
        if(regTab){ regTab.click(); }
      }

      var ICON_MAIL = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>';
      var ICON_LOCK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
      var ICON_ARROW = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';

      function addFieldIcon(selector, svg){
        document.querySelectorAll(selector).forEach(function(label){
          if(label.querySelector('.sp-auth-field-icon')){ return; }
          var span = document.createElement('span');
          span.className = 'sp-auth-field-icon';
          span.innerHTML = svg;
          label.insertBefore(span, label.firstChild);
        });
      }
      addFieldIcon('label[for="username"]', ICON_MAIL);
      addFieldIcon('label[for="password"]', ICON_LOCK);
      addFieldIcon('label[for="reg_email"]', ICON_MAIL);
      addFieldIcon('label[for="reg_password"]', ICON_LOCK);

      var regPassword = document.getElementById('reg_password');
      if(regPassword && !regPassword.getAttribute('placeholder')){
        regPassword.setAttribute('placeholder', 'Mind. 6 Zeichen');
      }

      document.querySelectorAll('.woocommerce-form-login__submit, .woocommerce-form-register__submit').forEach(function(btn){
        if(btn.querySelector('.sp-auth-btn-arrow')){ return; }
        var span = document.createElement('span');
        span.className = 'sp-auth-btn-arrow';
        span.innerHTML = ICON_ARROW;
        btn.appendChild(span);
      });
    })();
    </script>
    <?php
}, 30);

/**
 * Gezielte Rueckkehr nach Login/Registrierung ueber ?redirect=<url>, z.B. von
 * /abo-stack/ aus (sp-abo-picker.php) - WooCommerce bietet das in diesem
 * individuell gestalteten Login/Register-Formular nicht automatisch, nur
 * die Filter-Hooks dafuer sind vorhanden. Das eigentliche Formular traegt
 * keinen eigenen "redirect"-Hidden-Feld, aber "_wp_http_referer" (von
 * WordPress automatisch gesetzt) enthaelt die volle Ausgangs-URL inkl.
 * Query-String und damit auch unseren redirect-Parameter.
 */
function sp_myaccount_safe_redirect_target() {
    $raw = null;
    if (!empty($_REQUEST['redirect'])) {
        $raw = $_REQUEST['redirect'];
    } elseif (!empty($_REQUEST['_wp_http_referer'])) {
        $referer = wp_unslash($_REQUEST['_wp_http_referer']);
        $query = wp_parse_url($referer, PHP_URL_QUERY);
        if ($query) {
            parse_str($query, $params);
            if (!empty($params['redirect'])) {
                $raw = $params['redirect'];
            }
        }
    }
    if (!$raw) {
        return null;
    }
    $url = wp_validate_redirect(esc_url_raw(wp_unslash($raw)), false);
    return $url ?: null;
}
add_filter('woocommerce_login_redirect', function ($redirect) {
    $target = sp_myaccount_safe_redirect_target();
    return $target ?: $redirect;
});
add_filter('woocommerce_registration_redirect', function ($redirect) {
    $target = sp_myaccount_safe_redirect_target();
    return $target ?: $redirect;
});

/**
 * Fallback fuer den Fall, dass jemand nicht ueber einen redirect-Parameter
 * einloggt/registriert (z.B. ueber das Konto-Icon im Header von irgendeiner
 * Seite aus), sondern danach ganz regulaer im Dashboard landet: dort
 * pruefen, ob entweder ein nicht abgeschlossener Stack-Fortschritt (siehe
 * sp-abo-picker.php) oder generell eine zuletzt besuchte Seite (siehe
 * sp-account-header-icon.php) im localStorage liegt, und falls ja, einen
 * Weitermachen-Hinweis zeigen - unabhaengig davon, auf welcher Seite man war.
 * Die spezifische Abo-Stack-Variante hat Vorrang, da sie echte Auswahl
 * (Intervall/Produkte) mitbringt statt nur eine URL.
 */
add_action('woocommerce_account_dashboard', function () {
    ?>
    <style>
      /* Neutral/dunkel als Standard - nur der echte Abo-Stack-Wiederaufnahme-Fall
         bekommt via .is-abo die Orange-Akzentfarbe, da ausschliesslich dieser
         Fall tatsaechlich mit dem Abo zu tun hat. Der generische "Zurueck zur
         vorherigen Seite"-Hinweis ist reine Navigation und bleibt neutral. */
      #sp-stack-resume-banner{background:#F9FAFA;border:1px solid #DCDEE0}
      #sp-stack-resume-banner.is-abo{background:#FFF1EA;border-color:#FFD9C2}
      #sp-resume-link{background-color:#0D0F12}
      #sp-resume-link.is-abo{background-color:#FF6B35}
    </style>
    <div id="sp-stack-resume-banner" style="display:none;border-radius:12px;padding:16px 18px;margin-top:20px;font-family:'Sora',sans-serif;">
      <p id="sp-resume-text" style="margin:0 0 12px;font-size:14px;font-weight:700;color:#0D0F12;"></p>
      <a id="sp-resume-link" href="#" style="display:inline-flex;align-items:center;height:38px;padding:0 18px;border-radius:999px;color:#fff;text-decoration:none;font-size:13px;font-weight:700;">Jetzt weitermachen &rarr;</a>
    </div>
    <script>
    (function(){
      var banner = document.getElementById('sp-stack-resume-banner');
      var textEl = document.getElementById('sp-resume-text');
      var linkEl = document.getElementById('sp-resume-link');

      function show(text, url, isAbo){
        textEl.textContent = text;
        linkEl.setAttribute('href', url);
        banner.classList.toggle('is-abo', !!isAbo);
        linkEl.classList.toggle('is-abo', !!isAbo);
        banner.style.display = 'block';
      }

      // Beide Signale sammeln und das tatsaechlich AKTUELLERE gewinnen lassen
      // (statt den Abo-Stack-Fortschritt immer fest zu bevorzugen) - sonst
      // zeigt der Hinweis z.B. weiterhin "Du hattest einen Abo-Stack
      // angefangen", obwohl man seitdem laengst auf einer ganz anderen Seite
      // war und DIESER Besuch der eigentlich juengere/relevantere ist.
      var candidates = [];

      try {
        var rawStack = localStorage.getItem('sp_stack_resume_v1');
        if (rawStack) {
          var savedStack = JSON.parse(rawStack);
          var freshStack = savedStack && (Date.now() - (savedStack.ts || 0)) <= 24 * 60 * 60 * 1000;
          var hasSelection = freshStack && (!!savedStack.interval || (savedStack.items && Object.keys(savedStack.items).length > 0));
          if (hasSelection) {
            candidates.push({ ts: savedStack.ts || 0, text: 'Du hattest einen Abo-Stack angefangen.', url: '<?php echo esc_js(home_url('/abo-stack/')); ?>', isAbo: true });
          }
        }
      } catch (e) {}

      try {
        var rawPage = localStorage.getItem('sp_last_page');
        if (rawPage) {
          var savedPage = JSON.parse(rawPage);
          var freshPage = savedPage && savedPage.url && (Date.now() - (savedPage.ts || 0)) <= 24 * 60 * 60 * 1000;
          if (freshPage) {
            candidates.push({ ts: savedPage.ts || 0, text: 'Zurück zur vorherigen Seite', url: savedPage.url, isAbo: false });
          }
        }
      } catch (e) {}

      if (candidates.length) {
        candidates.sort(function(a, b){ return b.ts - a.ts; });
        var winner = candidates[0];
        show(winner.text, winner.url, winner.isAbo);
      }
    })();
    </script>
    <?php
}, 30);

