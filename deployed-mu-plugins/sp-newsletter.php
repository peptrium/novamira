<?php
/**
 * Plugin Name: SP Newsletter
 * Description: Newsletter-Anmeldung mit FluentCRM (kostenlos, selbst gehostet) - 2026-10-10.
 *
 * - Liste "Newsletter" in FluentCRM, Double-Opt-in (Bestaetigungsmail im Peptrium-Design,
 *   sp-email-design.php), Tags nach Quelle (Kasse / Footer).
 * - Anmeldung 1: optionales Kaestchen in der Kasse (Additional Checkout Field
 *   'sp/newsletter', wie die AGB-/18-Kaestchen in sp-checkout-consent.php).
 * - Anmeldung 2: Feld im Footer jeder Seite (ausser Kasse/Warenkorb), admin-ajax
 *   'sp_nl_subscribe' (ohne Nonce wegen Seiten-Cache; Honeypot + Limit pro IP).
 * - Nach der Bestaetigung: eigener Gutschein (10 %, 1x, 30 Tage, an die E-Mail
 *   gebunden) und Weiterleitung auf eine Danke-Seite mit dem Code (?sp_nl=danke).
 * - Datenschutzerklaerung (Seite 410) wird um einen Newsletter-Abschnitt ergaenzt.
 *
 * WICHTIG: Werbe-Mails (Kampagnen) NICHT ueber SMTP2GO verschicken (Peptide sind dort
 * laut AGB verboten). Hier laeuft nur die Bestaetigungsmail (keine Werbung) ueber den
 * normalen Shop-Versand. Fuer Kampagnen spaeter einen eigenen Versanddienst anbinden.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_NL_LIST_SLUG', 'newsletter');
define('SP_NL_COUPON_PERCENT', 10);
define('SP_NL_COUPON_DAYS', 30);

function sp_nl_ready() {
    return function_exists('FluentCrmApi') && class_exists('\FluentCrm\App\Models\Subscriber');
}

/* Liste + Quellen-Tags (werden beim ersten Bedarf angelegt). */
function sp_nl_list_id() {
    $list = \FluentCrm\App\Models\Lists::where('slug', SP_NL_LIST_SLUG)->first();
    if (!$list) {
        $list = \FluentCrm\App\Models\Lists::create(['title' => 'Newsletter', 'slug' => SP_NL_LIST_SLUG, 'is_public' => 1]);
    }
    return (int) $list->id;
}

function sp_nl_tag_id($source) {
    $slug = 'quelle-' . sanitize_title($source);
    $tag = \FluentCrm\App\Models\Tag::where('slug', $slug)->first();
    if (!$tag) {
        $tag = \FluentCrm\App\Models\Tag::create(['title' => 'Quelle: ' . ucfirst($source), 'slug' => $slug]);
    }
    return (int) $tag->id;
}

/**
 * Anmeldung starten. Rueckgabe: 'pending' (Bestaetigungsmail verschickt), 'already'
 * (schon bestaetigt), 'wait' (Mail gerade erst verschickt) oder 'error'.
 */
function sp_nl_subscribe($email, $first = '', $last = '', $source = 'footer') {
    if (!sp_nl_ready() || !is_email($email)) {
        return 'error';
    }
    $email = strtolower(trim($email));
    $list_id = sp_nl_list_id();
    $tag_id = sp_nl_tag_id($source);
    $contact = FluentCrmApi('contacts')->getContact($email);

    if ($contact && $contact->status === 'subscribed') {
        $contact->attachLists([$list_id]);
        $contact->attachTags([$tag_id]);
        return 'already';
    }
    if (get_transient('sp_nl_doi_' . md5($email))) {
        return 'wait';
    }

    $data = [
        'email' => $email,
        'status' => 'pending',
        'lists' => [$list_id],
        'tags' => [$tag_id],
        'source' => 'peptrium-' . $source,
    ];
    if ($first !== '') {
        $data['first_name'] = $first;
    }
    if ($last !== '') {
        $data['last_name'] = $last;
    }
    if ($contact && in_array($contact->status, ['unsubscribed', 'bounced', 'complained'], true)) {
        /* Ausdruecklicher neuer Anmeldewunsch: wieder auf "ausstehend" setzen, Bestaetigung noetig. */
        $contact->status = 'pending';
        $contact->save();
    }
    $contact = FluentCrmApi('contacts')->createOrUpdate($data);
    if (!$contact) {
        return 'error';
    }
    if ($contact->status !== 'pending') {
        $contact->status = 'pending';
        $contact->save();
    }
    $contact->sendDoubleOptinEmail();
    set_transient('sp_nl_doi_' . md5($email), 1, 10 * MINUTE_IN_SECONDS);
    return 'pending';
}

/* ---------------------------------------------------------------------
 * 1) Kaestchen in der Kasse
 * ------------------------------------------------------------------- */
add_action('woocommerce_init', function () {
    if (!function_exists('woocommerce_register_additional_checkout_field')) {
        return;
    }
    woocommerce_register_additional_checkout_field([
        'id' => 'sp/newsletter',
        'label' => 'Ja, schick mir Neuheiten & Angebote per E-Mail – mit 10 % Willkommensgutschein (jederzeit abbestellbar).',
        'location' => 'order',
        'type' => 'checkbox',
        'required' => false,
    ]);
});

add_action('woocommerce_store_api_checkout_order_processed', function ($order) {
    if (!$order instanceof WC_Order || !sp_nl_ready()) {
        return;
    }
    $value = '';
    if (class_exists('\Automattic\WooCommerce\Blocks\Package') && class_exists('\Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields')) {
        $fields = \Automattic\WooCommerce\Blocks\Package::container()->get(\Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class);
        $value = $fields->get_field_from_object('sp/newsletter', $order, 'other');
    }
    if (!$value || $order->get_meta('_sp_nl_done')) {
        return;
    }
    $res = sp_nl_subscribe($order->get_billing_email(), $order->get_billing_first_name(), $order->get_billing_last_name(), 'kasse');
    $order->update_meta_data('_sp_nl_done', $res);
    $order->add_order_note('Newsletter-Anmeldung an der Kasse: ' . ($res === 'pending' ? 'Bestätigungsmail verschickt' : $res) . '.');
    $order->save();
});

/* ---------------------------------------------------------------------
 * 2) Anmeldefeld im Footer
 * ------------------------------------------------------------------- */
add_action('wp_ajax_sp_nl_subscribe', 'sp_nl_ajax_subscribe');
add_action('wp_ajax_nopriv_sp_nl_subscribe', 'sp_nl_ajax_subscribe');
function sp_nl_ajax_subscribe() {
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (!empty($_POST['website'])) { // Honeypot
        wp_send_json_success(['msg' => 'Fast geschafft! Bitte bestätige deine Anmeldung über den Link in der E-Mail.']);
    }
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
    $key = 'sp_nl_ip_' . md5($ip . wp_salt());
    $hits = (int) get_transient($key);
    if ($hits >= 6) {
        wp_send_json_error(['msg' => 'Zu viele Versuche – bitte versuch es später noch einmal.']);
    }
    set_transient($key, $hits + 1, HOUR_IN_SECONDS);
    if (!is_email($email)) {
        wp_send_json_error(['msg' => 'Bitte gib eine gültige E-Mail-Adresse ein.']);
    }
    $res = sp_nl_subscribe($email, '', '', 'footer');
    if ($res === 'already') {
        wp_send_json_success(['msg' => 'Du bist schon angemeldet – danke! 💌']);
    }
    if ($res === 'pending' || $res === 'wait') {
        wp_send_json_success(['msg' => 'Fast geschafft! Bitte bestätige deine Anmeldung über den Link in der E-Mail (schau auch im Spam-Ordner).']);
    }
    wp_send_json_error(['msg' => 'Das hat leider nicht geklappt – bitte versuch es noch einmal.']);
}

add_action('wp_footer', function () {
    if (is_admin() || (function_exists('is_checkout') && (is_checkout() || is_cart()))) {
        return;
    }
    $privacy = get_permalink(410);
    ?>
    <style>
    #sp-nl{background:#0D0F12;padding:34px 16px 6px;font-family:Sora,sans-serif}
    #sp-nl .in{max-width:640px;margin:0 auto;border:1px solid #2A2E33;border-radius:18px;padding:22px 20px;background:linear-gradient(135deg,#15181C 0%,#1E2226 100%)}
    #sp-nl .ey{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#FF8A5C;margin:0 0 6px}
    #sp-nl h3{color:#fff;font-size:20px;line-height:1.25;margin:0 0 6px;font-weight:700}
    #sp-nl p.sub{color:#B9BEC5;font-size:14px;line-height:1.55;margin:0 0 14px}
    #sp-nl form{display:flex;gap:8px;flex-wrap:wrap}
    #sp-nl input[type=email]{flex:1 1 200px;min-width:0;height:46px;box-sizing:border-box;border:1px solid #3A3F45;border-radius:12px;background:#0D0F12;color:#fff;padding:0 14px;font:500 15px Sora,sans-serif}
    #sp-nl input[type=email]::placeholder{color:#80868E}
    #sp-nl button{flex:0 0 auto;height:46px;border:0;border-radius:12px;padding:0 20px;font:700 15px Sora,sans-serif;color:#0D0F12;background:#fff;cursor:pointer}
    #sp-nl button[disabled]{opacity:.6}
    #sp-nl .hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
    #sp-nl .msg{margin:12px 0 0;font-size:14px;line-height:1.5;color:#fff;display:none}
    #sp-nl .msg.err{color:#FF8A7A}
    #sp-nl .legal{margin:12px 0 0;font-size:11.5px;line-height:1.55;color:#80868E}
    #sp-nl .legal a{color:#B9BEC5}
    @media(max-width:480px){#sp-nl button{flex:1 1 100%}}
    </style>
    <script>
    (function(){
      function mount(){
        if(document.getElementById('sp-nl'))return;
        var anchor=document.querySelector('footer.elementor-location-footer .elementor-element-0421ed2')||document.querySelector('footer.elementor-location-footer');
        if(!anchor)return;
        var box=document.createElement('section');box.id='sp-nl';
        box.innerHTML='<div class="in"><p class="ey">Newsletter</p><h3>10 % auf deine nächste Bestellung</h3>'
          +'<p class="sub">Neuheiten, Aktionen und Forschungs-Updates direkt in dein Postfach. Kein Spam, jederzeit abbestellbar.</p>'
          +'<form novalidate><input type="email" name="email" placeholder="Deine E-Mail-Adresse" autocomplete="email" required>'
          +'<span class="hp"><input type="text" name="website" tabindex="-1" autocomplete="off"></span>'
          +'<button type="submit">Anmelden</button></form><p class="msg"></p>'
          +'<p class="legal">Mit der Anmeldung willigst du ein, dass wir dir Neuheiten &amp; Angebote per E-Mail senden und auswerten, ob du unsere Mails öffnest und anklickst. Du bekommst zuerst eine Bestätigungsmail. Abmeldung jederzeit über den Link in jeder Mail. Mehr in der <a href="<?php echo esc_url($privacy); ?>">Datenschutzerklärung</a>.</p></div>';
        if(anchor.classList.contains('elementor-element-0421ed2')){anchor.parentNode.insertBefore(box,anchor);}else{anchor.insertBefore(box,anchor.firstChild);}
        var f=box.querySelector('form'),m=box.querySelector('.msg'),b=f.querySelector('button');
        f.addEventListener('submit',function(e){
          e.preventDefault();
          var em=f.email.value.trim();
          if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){m.className='msg err';m.style.display='block';m.textContent='Bitte gib eine gültige E-Mail-Adresse ein.';return;}
          b.disabled=true;b.textContent='…';
          var fd=new FormData();fd.append('action','sp_nl_subscribe');fd.append('email',em);fd.append('website',f.website.value);
          fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>',{method:'POST',body:fd,credentials:'same-origin'})
            .then(function(r){return r.json();})
            .then(function(r){
              m.style.display='block';m.textContent=(r&&r.data&&r.data.msg)||'Bitte versuch es noch einmal.';
              m.className=r&&r.success?'msg':'msg err';
              if(r&&r.success){f.style.display='none';}else{b.disabled=false;b.textContent='Anmelden';}
            })
            .catch(function(){m.style.display='block';m.className='msg err';m.textContent='Das hat leider nicht geklappt – bitte versuch es noch einmal.';b.disabled=false;b.textContent='Anmelden';});
        });
      }
      if(document.readyState!=='loading')mount();else document.addEventListener('DOMContentLoaded',mount);
    })();
    </script>
    <?php
}, 30);

/* ---------------------------------------------------------------------
 * 3) Bestaetigungsmail im Peptrium-Design
 * ------------------------------------------------------------------- */
add_filter('fluent_crm/email-design-template-peptrium', function ($body, $data = []) {
    if (!function_exists('sp_em_hero') || !function_exists('wc_get_template_html')) {
        return $body;
    }
    sp_em_hero(['sub' => 'Nur noch ein Klick, dann bist du dabei.', 'preheader' => 'Bitte bestätige deine Newsletter-Anmeldung.']);
    $html = wc_get_template_html('emails/email-header.php', ['email_heading' => 'Bitte bestätige deine Anmeldung'])
        . $body
        . wc_get_template_html('emails/email-footer.php', ['email' => null]);
    WC()->mailer(); // laedt die WC_Email-Klassen (im AJAX-Aufruf sonst noch nicht geladen)
    $tmp = new WC_Email();
    return $tmp->style_inline($html);
}, 10, 2);

/* Kein FluentCRM-Tracking-Cookie fuer Besucher (wir setzen keine Cookies ohne Einwilligung). */
add_filter('fluent_crm/will_use_cookie', '__return_false');

/* ---------------------------------------------------------------------
 * 4) Nach der Bestaetigung: Gutschein + Danke-Seite
 * ------------------------------------------------------------------- */
function sp_nl_ensure_coupon($subscriber) {
    if (!$subscriber || !function_exists('wc_get_coupon_id_by_code')) {
        return '';
    }
    $code = (string) $subscriber->getMeta('sp_nl_coupon', 'peptrium');
    if ($code && wc_get_coupon_id_by_code($code)) {
        return $code;
    }
    do {
        $code = 'HALLO-' . strtoupper(wp_generate_password(5, false, false));
    } while (wc_get_coupon_id_by_code($code));
    $coupon = new WC_Coupon();
    $coupon->set_code($code);
    $coupon->set_discount_type('percent');
    $coupon->set_amount(SP_NL_COUPON_PERCENT);
    $coupon->set_individual_use(true);
    $coupon->set_usage_limit(1);
    $coupon->set_usage_limit_per_user(1);
    $coupon->set_email_restrictions([$subscriber->email]);
    $coupon->set_date_expires(time() + SP_NL_COUPON_DAYS * DAY_IN_SECONDS);
    $topup = function_exists('sp_wallet_get_topup_product_id') ? (int) sp_wallet_get_topup_product_id() : 0;
    if ($topup) {
        $coupon->set_excluded_product_ids([$topup]);
    }
    $coupon->set_description('Newsletter-Willkommensgutschein (' . $subscriber->email . ')');
    $coupon->save();
    $subscriber->updateMeta('sp_nl_coupon', $code, 'peptrium');
    return $code;
}

add_action('fluentcrm_subscriber_status_to_subscribed', function ($subscriber) {
    if ($subscriber && $subscriber->lists()->where('slug', SP_NL_LIST_SLUG)->exists()) {
        sp_nl_ensure_coupon($subscriber);
    }
}, 20, 1);

add_filter('fluent_crm/double_optin_options', function ($config, $subscriber) {
    if ($subscriber && $subscriber->status === 'subscribed') {
        sp_nl_ensure_coupon($subscriber);
        $config['after_confirmation_type'] = 'redirect';
        $config['after_conf_redirect_url'] = add_query_arg(['sp_nl' => 'danke', 'h' => $subscriber->hash], home_url('/'));
    }
    return $config;
}, 20, 2);

add_action('template_redirect', function () {
    if (empty($_GET['sp_nl']) || $_GET['sp_nl'] !== 'danke' || !sp_nl_ready()) {
        return;
    }
    $hash = preg_replace('/[^a-f0-9]/', '', (string) ($_GET['h'] ?? ''));
    $sub = $hash ? \FluentCrm\App\Models\Subscriber::where('hash', $hash)->first() : null;
    $code = ($sub && $sub->status === 'subscribed') ? sp_nl_ensure_coupon($sub) : '';
    nocache_headers();
    $logo = get_option('woocommerce_email_header_image');
    ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Anmeldung bestätigt – Peptrium</title>
    <style>
    body{margin:0;background:#0D0F12;font-family:Sora,-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;color:#fff}
    .w{max-width:520px;margin:0 auto;padding:40px 18px 50px;text-align:center}
    .logo{width:96px;height:auto}
    .wm{font-size:18px;font-weight:800;letter-spacing:.32em;color:#E6E8EB;margin:10px 0 26px;padding-left:.32em}
    h1{font-size:28px;line-height:1.2;margin:0 0 10px}
    p{color:#C9CDD2;font-size:15px;line-height:1.6;margin:0 0 22px}
    .cp{background:#fff;color:#0D0F12;border-radius:18px;padding:24px 18px;margin:0 0 22px}
    .cp small{display:block;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#5B6169}
    .cp .c{font:900 30px 'SFMono-Regular',Consolas,Menlo,monospace;letter-spacing:.06em;margin:8px 0 6px;word-break:break-all}
    .cp .h{font-size:13px;color:#5B6169;margin:0 0 16px}
    .bt{display:inline-block;border:0;border-radius:12px;padding:14px 24px;font-weight:700;font-size:15px;text-decoration:none;cursor:pointer;font-family:inherit}
    .b1{background:#0D0F12;color:#fff}.b2{background:#fff;color:#0D0F12;margin-top:6px}
    </style></head><body><div class="w">
    <?php if ($logo) : ?><img class="logo" src="<?php echo esc_url($logo); ?>" alt="Peptrium"><?php endif; ?>
    <div class="wm">PEPTRIUM</div>
    <?php if ($code) : ?>
      <h1>Danke, du bist dabei! 🎉</h1>
      <p>Deine Newsletter-Anmeldung ist bestätigt. Hier ist dein Willkommensgeschenk:</p>
      <div class="cp"><small>Dein Gutscheincode</small><div class="c" id="c"><?php echo esc_html($code); ?></div>
        <div class="h"><?php echo (int) SP_NL_COUPON_PERCENT; ?> % auf deine nächste Bestellung · <?php echo (int) SP_NL_COUPON_DAYS; ?> Tage gültig · gilt mit deiner E-Mail-Adresse</div>
        <button class="bt b1" onclick="navigator.clipboard&&navigator.clipboard.writeText('<?php echo esc_js($code); ?>');this.textContent='Kopiert ✓'">Code kopieren</button></div>
      <p>Gib den Code im Warenkorb unter „Rabattcode eingeben“ ein.</p>
      <a class="bt b2" href="<?php echo esc_url(home_url('/alle-produkte/')); ?>">Zum Shop &rarr;</a>
    <?php else : ?>
      <h1>Anmeldung bestätigt</h1>
      <p>Danke! Falls du deinen Gutscheincode nicht siehst, öffne bitte den Link aus der Bestätigungsmail noch einmal oder schreib uns an info@peptrium.com.</p>
      <a class="bt b2" href="<?php echo esc_url(home_url('/')); ?>">Zum Shop &rarr;</a>
    <?php endif; ?>
    </div></body></html><?php
    exit;
});

/* ---------------------------------------------------------------------
 * 5) Datenschutzerklaerung (Seite 410) ergaenzen
 * ------------------------------------------------------------------- */
function sp_nl_privacy_filter($html) {
    if (!is_page(410) || strpos($html, 'Daten von Kindern') === false || strpos($html, 'sp-nl-privacy') !== false) {
        return $html;
    }
    $section = '<h2 class="sp-nl-privacy">Newsletter</h2>'
        . '<p>Wenn du dich für unseren Newsletter anmeldest (im Footer unserer Website oder per Häkchen an der Kasse), verwenden wir deine E-Mail-Adresse und – falls angegeben – deinen Namen, um dir Neuheiten, Angebote und Informationen zu unseren Produkten per E-Mail zu senden. Die Anmeldung erfolgt im Double-Opt-in-Verfahren: Du erhältst zunächst eine E-Mail mit einem Bestätigungslink; erst nach deiner Bestätigung wirst du in den Verteiler aufgenommen. Zum Nachweis deiner Einwilligung speichern wir den Zeitpunkt der Anmeldung und Bestätigung sowie die dabei verwendete IP-Adresse.</p>'
        . '<p>In unseren Newsletter-Mails werten wir aus, ob eine E-Mail geöffnet und welche Links angeklickt wurden, um unsere Inhalte zu verbessern. Rechtsgrundlage ist deine Einwilligung (Art. 6 Abs. 1 lit. a DSGVO). Du kannst sie jederzeit mit Wirkung für die Zukunft widerrufen – über den Abmelde-Link in jeder E-Mail oder per Nachricht an info@peptrium.com. Die Verwaltung der Abonnenten erfolgt mit der Software FluentCRM direkt auf unserem eigenen Server; deine Daten werden dafür nicht an den Hersteller übermittelt. Nach einer Abmeldung speichern wir deine E-Mail-Adresse nur noch, um sicherzustellen, dass du keine weiteren Newsletter erhältst.</p>';
    return str_replace('<h2>Daten von Kindern</h2>', $section . '<h2>Daten von Kindern</h2>', $html);
}
add_filter('elementor/frontend/the_content', 'sp_nl_privacy_filter', 98);
add_filter('the_content', 'sp_nl_privacy_filter', 98);
