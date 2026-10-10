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
    $src = (isset($_POST['src']) && $_POST['src'] === 'startseite') ? 'startseite' : 'footer';
    $res = sp_nl_subscribe($email, '', '', $src);
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
    $full = is_front_page();
    ?>
    <style id="sp-nlh-css">
#sp-nlh{position:relative;overflow:hidden;background:#0B0D10;padding:80px 24px;font-family:Sora,sans-serif;color:#fff}
#sp-nlh:before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:44px 44px;-webkit-mask-image:radial-gradient(ellipse 70% 70% at 50% 50%,#000 30%,transparent 75%);mask-image:radial-gradient(ellipse 70% 70% at 50% 50%,#000 30%,transparent 75%)}
#sp-nlh:after{content:'';position:absolute;width:620px;height:620px;left:12%;top:50%;transform:translateY(-50%);background:radial-gradient(circle,rgba(210,215,220,.13),transparent 65%);pointer-events:none}
#sp-nlh *{box-sizing:border-box}
#sp-nlh .w{position:relative;z-index:1;max-width:1100px;margin:0 auto;display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:64px;align-items:center}
/* Ticket */
#sp-nlh .tk{position:relative;display:flex;max-width:440px;margin:0 auto;border-radius:20px;color:#0D0F12;background:linear-gradient(125deg,#9EA6AF 0%,#E9ECEF 22%,#FFFFFF 38%,#C3C9CF 58%,#F1F3F5 78%,#A7AFB8 100%);box-shadow:0 30px 60px rgba(0,0,0,.55),0 0 0 1px rgba(255,255,255,.35) inset;transform:rotate(-4deg)}
#sp-nlh .tk .m{flex:1;padding:26px 24px 24px}
#sp-nlh .tk .lg{display:flex;align-items:center;gap:8px;font-size:10.5px;font-weight:800;letter-spacing:.2em;text-transform:uppercase;color:#2A2F35}
#sp-nlh .tk .lg img{width:24px;height:24px;display:block;flex:0 0 24px;filter:drop-shadow(0 1px 2px rgba(0,0,0,.25))}
#sp-nlh .tk .big{font-size:76px;line-height:.95;font-weight:800;letter-spacing:-.04em;margin:18px 0 6px}
#sp-nlh .tk .big small{font-size:.42em;letter-spacing:-.01em;vertical-align:.9em;margin-left:2px}
#sp-nlh .tk .d{font-size:13px;font-weight:600;color:#30363D}
#sp-nlh .tk .st{position:relative;flex:0 0 92px;border-left:2px dashed rgba(13,15,18,.28);display:flex;align-items:center;justify-content:center}
#sp-nlh .tk .st:before,#sp-nlh .tk .st:after{content:'';position:absolute;left:-12px;width:22px;height:22px;border-radius:50%;background:#0B0D10}
#sp-nlh .tk .st:before{top:-11px}#sp-nlh .tk .st:after{bottom:-11px}
#sp-nlh .tk .code{writing-mode:vertical-rl;transform:rotate(180deg);font:800 13px/1 ui-monospace,Menlo,monospace;letter-spacing:.18em;color:#0D0F12}
#sp-nlh .tk .code em{font-style:normal;color:rgba(13,15,18,.35)}
#sp-nlh .tk .seal{position:absolute;right:104px;bottom:18px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#4A5058;border:1px solid rgba(13,15,18,.25);border-radius:999px;padding:4px 9px}
/* Inhalt */
#sp-nlh .ey{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#9AA0A8;margin:0 0 14px}
#sp-nlh .ey i{width:6px;height:6px;border-radius:50%;background:#FF8A5C;box-shadow:0 0 10px rgba(255,138,92,.9)}
#sp-nlh h2{font-size:clamp(28px,3.4vw,40px);line-height:1.12;font-weight:700;letter-spacing:-.02em;margin:0 0 14px;color:#fff}
#sp-nlh h2 span{background:linear-gradient(90deg,#9AA3AD,#F4F6F8 50%,#B8BFC6);-webkit-background-clip:text;background-clip:text;color:transparent}
#sp-nlh .lead{font-size:15.5px;line-height:1.65;color:#A9AFB6;margin:0 0 26px;max-width:520px}
#sp-nlh form{display:flex;align-items:center;gap:6px;max-width:520px;padding:6px;border-radius:16px;background:#14171B;border:1px solid #2C3137;transition:border-color .2s,box-shadow .2s}
#sp-nlh form:focus-within{border-color:#8E969F;box-shadow:0 0 0 4px rgba(199,204,209,.10)}
#sp-nlh input[type=email]{flex:1;min-width:0;height:48px;border:0;background:transparent;color:#fff;padding:0 14px;font:500 15px Sora,sans-serif;outline:none}
#sp-nlh input[type=email]::placeholder{color:#6E747C}
#sp-nlh button{flex:0 0 auto;height:48px;border:0;border-radius:12px;padding:0 22px;font:700 14.5px Sora,sans-serif;color:#0D0F12;background:linear-gradient(120deg,#C7CCD1,#FFFFFF 45%,#C7CCD1);cursor:pointer;white-space:nowrap}
#sp-nlh .steps{display:flex;gap:0;margin:26px 0 0;max-width:520px;padding:0;list-style:none}
#sp-nlh .steps li{flex:1;position:relative;padding-top:34px;font-size:12.5px;line-height:1.4;color:#C9CDD2}
#sp-nlh .steps li b{display:block;color:#fff;font-size:13px;margin-bottom:2px}
#sp-nlh .steps li:before{content:attr(data-n);position:absolute;top:0;left:0;width:24px;height:24px;border-radius:50%;border:1px solid #4A5058;background:#0B0D10;color:#E6E9EC;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;z-index:1}
#sp-nlh .steps li:after{content:'';position:absolute;top:12px;left:30px;right:10px;height:1px;background:linear-gradient(90deg,#4A5058,rgba(74,80,88,.2))}
#sp-nlh .steps li:last-child:after{display:none}
#sp-nlh .steps li:last-child:before{background:#E6E9EC;color:#0D0F12;border-color:#E6E9EC}
#sp-nlh .legal{font-size:11px;line-height:1.55;color:#6A7077;margin:22px 0 0;max-width:520px}
#sp-nlh .legal a{color:#9AA0A8}
@media(max-width:900px){
 #sp-nlh{padding:56px 16px 52px}
 #sp-nlh .w{grid-template-columns:1fr;gap:40px}
 #sp-nlh:after{left:50%;top:150px;transform:translateX(-50%);width:440px;height:440px}
 #sp-nlh .tk{max-width:330px;transform:rotate(-2deg)}
 #sp-nlh .tk .m{padding:20px 18px 18px}
 #sp-nlh .tk .big{font-size:58px;margin:14px 0 4px}
 #sp-nlh .tk .st{flex-basis:72px}
 #sp-nlh .tk .seal{display:none}
 #sp-nlh .tk .code{font-size:11.5px}
 #sp-nlh .c{text-align:left}
 #sp-nlh form{flex-direction:column;align-items:stretch;padding:6px;gap:6px}
 #sp-nlh input[type=email]{height:50px;text-align:center}
 #sp-nlh button{width:100%}
 #sp-nlh .steps li{font-size:11.5px;padding-right:6px}
 #sp-nlh .steps li b{font-size:12px}
}

#sp-nlh .tkw{position:relative;perspective:900px}
#sp-nlh .tk{overflow:hidden;animation:spTkFloat 6s ease-in-out infinite}
#sp-nlh .tk .sh{position:absolute;inset:0;background:linear-gradient(105deg,transparent 35%,rgba(255,255,255,.75) 48%,transparent 60%);transform:translateX(-120%);animation:spTkShine 4.5s ease-in-out infinite;pointer-events:none;mix-blend-mode:soft-light}
@keyframes spTkShine{0%,55%{transform:translateX(-120%)}85%,100%{transform:translateX(120%)}}
@keyframes spTkFloat{0%,100%{transform:rotate(-4deg) translateY(0)}50%{transform:rotate(-3deg) translateY(-8px)}}
#sp-nlh .tkw:after{content:'';position:absolute;left:12%;right:12%;bottom:-38px;height:26px;border-radius:50%;background:radial-gradient(ellipse,rgba(0,0,0,.6),transparent 70%);filter:blur(4px)}
#sp-nlh .tk .st:before,#sp-nlh .tk .st:after{z-index:2}
#sp-nlh .tk .code{font-size:14px}
#sp-nlh .tk.ok .code em{color:#0D0F12}
#sp-nlh .perks{display:flex;flex-wrap:wrap;gap:8px 18px;margin:0 0 24px;padding:0;list-style:none}
#sp-nlh .perks li{display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:#D5D9DD}
#sp-nlh .perks li:before{content:'';width:16px;height:16px;border-radius:50%;background:rgba(230,233,236,.12) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23E6E9EC' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center/9px no-repeat}
#sp-nlh .legal{color:#7A8087}
@media (prefers-reduced-motion:reduce){#sp-nlh .tk,#sp-nlh .tk .sh{animation:none}}
@media(max-width:900px){
 #sp-nlh{padding:48px 16px 44px}
 #sp-nlh .w{gap:34px}
 #sp-nlh .tk{max-width:320px}
 @keyframes spTkFloat{0%,100%{transform:rotate(-2deg) translateY(0)}50%{transform:rotate(-1.5deg) translateY(-6px)}}
 #sp-nlh .tk .big{font-size:54px}
 #sp-nlh form{background:transparent;border:0;padding:0;gap:10px;box-shadow:none!important}
 #sp-nlh input[type=email]{flex:none;width:100%;height:52px;text-align:left;padding:0 16px;background:#14171B;border:1px solid #2C3137;border-radius:14px}
 #sp-nlh input[type=email]:focus{border-color:#8E969F}
 #sp-nlh button{height:52px;border-radius:14px}
 #sp-nlh .perks{gap:8px 14px;margin-bottom:20px}
 #sp-nlh .perks li{font-size:12.5px}
 #sp-nlh .steps{margin-top:22px}
}
/* Schmale Variante (alle Seiten ausser Startseite) */
#sp-nlh.mini{padding:40px 16px 8px;background:#0D0F12}
#sp-nlh.mini:before,#sp-nlh.mini:after{display:none}
#sp-nlh.mini .w{max-width:980px;grid-template-columns:230px minmax(0,1fr);gap:36px;border:1px solid #23272C;border-radius:22px;padding:28px 32px;background:radial-gradient(420px 220px at 15% 50%,rgba(210,215,220,.09),transparent 70%),linear-gradient(135deg,#13161A,#1A1E22)}
#sp-nlh.mini .tk{max-width:230px;border-radius:14px}
#sp-nlh.mini .tk .m{padding:16px 14px 14px}
#sp-nlh.mini .tk .lg{font-size:8px;gap:6px}
#sp-nlh.mini .tk .lg img{width:17px;height:17px;flex-basis:17px}
#sp-nlh.mini .tk .big{font-size:44px;margin:10px 0 4px}
#sp-nlh.mini .tk .d{font-size:10px}
#sp-nlh.mini .tk .st{flex-basis:46px}
#sp-nlh.mini .tk .st:before,#sp-nlh.mini .tk .st:after{width:16px;height:16px;left:-9px;background:#15181C}
#sp-nlh.mini .tk .st:before{top:-8px}#sp-nlh.mini .tk .st:after{bottom:-8px}
#sp-nlh.mini .tk .code{font-size:9.5px}
#sp-nlh.mini .tk .seal,#sp-nlh.mini .tkw:after,#sp-nlh.mini .steps{display:none}
#sp-nlh.mini .ey{margin-bottom:8px}
#sp-nlh.mini h2{font-size:24px;margin:0 0 6px}
#sp-nlh.mini .lead{font-size:14px;margin:0 0 16px}
#sp-nlh.mini .legal{margin-top:12px}
@media(max-width:900px){
 #sp-nlh.mini{padding:32px 16px 8px}
 #sp-nlh.mini .w{grid-template-columns:1fr;gap:22px;padding:22px 18px}
 #sp-nlh.mini .tk{max-width:220px;margin:0}
 #sp-nlh.mini h2{font-size:22px}
}
#sp-nlh .msg{display:none;margin:14px 0 0;font-size:14px;line-height:1.5;color:#fff;max-width:520px}
#sp-nlh .msg.err{color:#FF8A7A}
#sp-nlh .msg.ok{padding:12px 14px;border-radius:12px;background:rgba(230,233,236,.08);border:1px solid rgba(230,233,236,.18)}
#sp-nlh .hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
#sp-nlh button[disabled]{opacity:.6}
    </style>
    <script>
    (function(){
      var FULL=<?php echo $full ? 'true' : 'false'; ?>;
      function mount(){
        if(document.getElementById('sp-nlh'))return;
        var footer=document.querySelector('footer.elementor-location-footer');
        var anchor=FULL?footer:(document.querySelector('footer.elementor-location-footer .elementor-element-0421ed2')||footer);
        if(!anchor)return;
        var s=document.createElement('section');s.id='sp-nlh';if(!FULL)s.className='mini';
        s.innerHTML='<div class="w">'
          +'<div class="tkw"><div class="tk"><div class="sh"></div><div class="m"><div class="lg"><img src="<?php echo esc_url(content_url('/uploads/2026/08/IMG_2644-cropped-300x300.png')); ?>" alt="" width="24" height="24">Peptrium Insider</div><div class="big">10<small>%</small></div><div class="d">auf deine nächste Bestellung</div></div>'
          +'<div class="seal">Persönlich</div><div class="st"><div class="code">HALLO-<em>•••••</em></div></div></div></div>'
          +'<div class="c"><div class="ey"><i></i>Newsletter</div>'
          +'<h2>10 % für <span>Insider</span>.</h2>'
          +(FULL?'<p class="lead">Trag dich ein und erfahre neue Peptide und Aktionen vor allen anderen – plus dein persönlicher Code für die nächste Bestellung.</p>'
             +'<ul class="perks"><li>Neues zuerst</li><li>Nur wenn es sich lohnt</li><li>1 Klick abmelden</li></ul>'
            :'<p class="lead">Neue Peptide und Aktionen zuerst – plus dein persönlicher Code für die nächste Bestellung. Kein Spam.</p>')
          +'<form novalidate><input type="email" name="email" placeholder="Deine E-Mail-Adresse" aria-label="E-Mail-Adresse" autocomplete="email" required>'
          +'<span class="hp"><input type="text" name="website" tabindex="-1" autocomplete="off"></span>'
          +'<button type="submit">Code sichern →</button></form><p class="msg"></p>'
          +'<ol class="steps"><li data-n="1"><b>Anmelden</b>E-Mail eintragen</li><li data-n="2"><b>Bestätigen</b>Link in der Mail</li><li data-n="3"><b>Code nutzen</b>10 % an der Kasse</li></ol>'
          +'<p class="legal">Mit der Anmeldung willigst du ein, dass wir dir Neuheiten &amp; Angebote per E-Mail senden und auswerten, ob du unsere Mails öffnest und anklickst. Du bekommst zuerst eine Bestätigungsmail. Abmeldung jederzeit über den Link in jeder Mail. Mehr in der <a href="<?php echo esc_url($privacy); ?>">Datenschutzerklärung</a>.</p>'
          +'</div></div>';
        anchor.parentNode.insertBefore(s,anchor);
        var f=s.querySelector('form'),m=s.querySelector('.msg'),b=f.querySelector('button'),tk=s.querySelector('.tk'),code=s.querySelector('.code');
        function show(t,cls){m.style.display='block';m.className='msg'+(cls?' '+cls:'');m.textContent=t;}
        f.addEventListener('submit',function(e){
          e.preventDefault();
          var em=f.email.value.trim();
          if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){show('Bitte gib eine gültige E-Mail-Adresse ein.','err');return;}
          b.disabled=true;b.textContent='…';
          var fd=new FormData();fd.append('action','sp_nl_subscribe');fd.append('email',em);fd.append('website',f.website.value);fd.append('src',FULL?'startseite':'footer');
          fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>',{method:'POST',body:fd,credentials:'same-origin'})
            .then(function(r){return r.json();})
            .then(function(r){
              var t=(r&&r.data&&r.data.msg)||'Bitte versuch es noch einmal.';
              if(r&&r.success){f.style.display='none';show(t,'ok');tk.classList.add('ok');code.textContent='POSTFACH ✓';}
              else{show(t,'err');b.disabled=false;b.textContent='Code sichern →';}
            })
            .catch(function(){show('Das hat leider nicht geklappt – bitte versuch es noch einmal.','err');b.disabled=false;b.textContent='Code sichern →';});
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
