<?php
/**
 * Plugin Name: SP Start-Seite
 * Description: /start/ = Einsteiger-Seite für Besucher aus den TikTok-Videos (Retatrutide). Normale WP-Seite, Inhalt komplett hier gerendert.
 *              noindex + nicht in der Sitemap, kein Menü-Link. Beim Öffnen wird der Code TIKTOK10 vorgemerkt (Cookie 30 Tage) und beim
 *              nächsten Warenkorb automatisch einmal angewendet (nicht bei Partner-Besuchern). Scroll-Tiefe und Klicks landen als
 *              stscroll/stclick in wp_sp_stats. Entwürfe: drafts/start-seite-2026-10-17/.
 */
if (!defined('ABSPATH')) {
    exit;
}

const SP_ST_CODE = 'tiktok10';

function sp_st_page_id() {
    static $id = null;
    if ($id === null) {
        $p = get_page_by_path('start');
        $id = ($p && $p->post_status === 'publish') ? (int) $p->ID : 0;
    }
    return $id;
}

function sp_st_is() {
    return sp_st_page_id() && is_page(sp_st_page_id());
}

/* noindex, nicht in der Sitemap */
add_filter('wp_robots', function ($r) {
    if (sp_st_is()) {
        $r['noindex'] = true;
        $r['follow'] = true;
    }
    return $r;
});
add_filter('wp_sitemaps_posts_query_args', function ($args, $post_type) {
    if ($post_type === 'page' && sp_st_page_id()) {
        $args['post__not_in'] = array_values(array_unique(array_merge($args['post__not_in'] ?? [], [sp_st_page_id()])));
    }
    return $args;
}, 30, 2);

/* Code vormerken */
add_action('template_redirect', function () {
    if (!sp_st_is()) {
        return;
    }
    if (empty($_COOKIE['sp_st_code'])) {
        setcookie('sp_st_code', '1', ['expires' => time() + 30 * DAY_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax']);
        $_COOKIE['sp_st_code'] = '1';
    }
}, 5);

function sp_st_maybe_apply() {
    if (empty($_COOKIE['sp_st_code']) || !empty($_COOKIE['slicewp_aff'])) {
        return;
    }
    if (!function_exists('WC') || !WC()->cart || !WC()->session || WC()->session->get('sp_st_done')) {
        return;
    }
    if (WC()->cart->is_empty()) {
        return;
    }
    if (WC()->cart->has_discount(SP_ST_CODE) || WC()->cart->get_applied_coupons()) {
        WC()->session->set('sp_st_done', 1); // schon ein Code drin → nicht überschreiben
        return;
    }
    WC()->session->set('sp_st_done', 1);
    WC()->cart->apply_coupon(SP_ST_CODE);
}
add_action('woocommerce_add_to_cart', 'sp_st_maybe_apply', 30);
add_action('wp_loaded', function () {
    if (!is_admin() || wp_doing_ajax()) {
        sp_st_maybe_apply();
    }
}, 30);

/* Seite: kein Astra-Titel, kein Newsletter-Popup (Code gibt es hier schon) */
add_action('wp', function () {
    if (!sp_st_is()) {
        return;
    }
    $_COOKIE['sp_nlp_done'] = '1'; // nur für diesen Aufruf: sp_nlp_should_render() → false
    remove_filter('the_content', 'wpautop');
    add_filter('astra_the_title_enabled', '__return_false');
    add_filter('body_class', function ($c) {
        $c[] = 'sp-start-page';
        return $c;
    });
});

/* Messung */
function sp_st_ajax_event() {
    if (!function_exists('sp_an_should_track') || !function_exists('sp_an_insert') || !sp_an_should_track()) {
        wp_die('', '', ['response' => 204]);
    }
    $t = isset($_POST['t']) && $_POST['t'] === 'click' ? 'stclick' : 'stscroll';
    $v = substr(preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($_POST['v'] ?? ''))), 0, 40);
    if ($v !== '') {
        sp_an_insert(['type' => $t, 'ptype' => 'other', 'path' => '/start/', 'campaign' => $v]);
    }
    wp_die('', '', ['response' => 204]);
}
add_action('wp_ajax_sp_st_ev', 'sp_st_ajax_event');
add_action('wp_ajax_nopriv_sp_st_ev', 'sp_st_ajax_event');

function sp_st_price($ids) {
    $sum = 0.0;
    foreach ($ids as $id) {
        $p = wc_get_product($id);
        if ($p) {
            $sum += (float) $p->get_price();
        }
    }
    return number_format($sum, 2, ',', '.') . ' €';
}

/** Sterne wie auf den Produktseiten (feste Werte aus sp_abo_picker_rating_map()). */
function sp_st_stars($pid) {
    $m = function_exists('sp_abo_picker_rating_map') ? sp_abo_picker_rating_map() : [];
    if (empty($m[$pid])) {
        return '';
    }
    return '<div class="st-stars"><span>★★★★★</span> <b>' . esc_html(number_format((float) $m[$pid]['num'], 1, ',', '')) . '</b> <small>(' . (int) $m[$pid]['count'] . ' Bewertungen)</small></div>';
}

/** Kundenstimmen = dieselben Texte wie in den Kundenstimmen der Retatrutide-Produktseiten. */
function sp_st_reviews() {
    return [
        ['Vanessa B.', 'Ich bin jetzt schon bei der dritten Bestellung und bin nach wie vor begeistert von der Qualität, der Verpackung und dem schnellen Versand. Absolute Kaufempfehlung, komme auf jeden Fall wieder.', 5],
        ['Jasmin T.', 'Wirklich zufrieden mit der Qualität und dem ganzen Ablauf. Werde definitiv wieder bestellen.', 5],
        ['Nadine S.', 'Bestelle hier jetzt regelmäßig weil einfach alles passt Qualität Verpackung und der Support', 5],
        ['Tobias W.', 'Sehr zufrieden. Zweite Bestellung war genauso gut wie die erste.', 5],
        ['Marco P.', 'Gute Erfahrung gemacht auch wenn der Versand einen Tag länger gedauert hat als angegeben aber inhaltlich alles top', 4],
        ['Katharina D.', 'Hat alles gepasst, bin sehr zufrieden und werde wieder bestellen.', 5],
    ];
}

function sp_st_tg_icon() {
    return '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M21.9 4.3 18.6 19.9c-.2 1.1-.9 1.4-1.8.9l-5-3.7-2.4 2.3c-.3.3-.5.5-1 .5l.4-5.1 9.3-8.4c.4-.4-.1-.6-.6-.2L5.9 13.4l-4.9-1.5c-1.1-.3-1.1-1.1.2-1.6L20.4 2.9c.9-.3 1.7.2 1.5 1.4Z"/></svg>';
}

add_filter('the_content', function ($html) {
    if (!sp_st_is() || !in_the_loop() || !is_main_query()) {
        return $html;
    }
    $u = function ($p) {
        return esc_url(home_url($p));
    };
    $up = content_url('uploads/');
    $tg = 'https://t.me/peptrium';
    $ic = sp_st_tg_icon();
    $pen_img = wp_get_attachment_image_url(get_post_thumbnail_id(393), 'woocommerce_thumbnail') ?: '';
    $set_img = $up . 'sp-email/nl-w3-set.jpg';
    $ask = '<a class="ask" data-ev="tg-ask" href="' . $tg . '" target="_blank" rel="noopener">' . $ic . ' Frage dazu? Schreib uns auf Telegram</a>';
    $faq = [
        ['Ich habe noch nie mit Peptiden gearbeitet. Wo fange ich an?', 'Lies dir oben kurz „Welche Variante passt zu dir?“ durch. Wenn du nichts anmischen möchtest, ist der <b>Pen</b> die einfachste Wahl. Wenn du unsicher bist, schreib uns einfach auf <a href="' . $tg . '" data-ev="tg-faq" target="_blank" rel="noopener">Telegram</a> – wir helfen dir bei der Auswahl.'],
        ['Pen oder Fläschchen – was ist der Unterschied?', 'Der <b>Pen</b> ist bereits fertig gemischt. Du brauchst nur die Pen Nadeln. Das <b>Fläschchen</b> enthält das Peptid als Pulver, ist pro mg günstiger und muss vorher mit Bac Water angemischt werden.'],
        ['Was brauche ich zum Anmischen?', 'Bac Water und Spritzen mit Skala. Beides ist im <b>Starter-Set</b> schon dabei. Die <a href="' . $u('/ratgeber/peptide-rekonstituieren/') . '">Anleitung</a> findest du im Ratgeber, die Konzentration rechnet der <a href="' . $u('/peptid-rechner/') . '">Peptid-Rechner</a> aus.'],
        ['Welche Größe soll ich nehmen?', 'Das Fläschchen gibt es mit 10 mg, den Pen mit 20, 30 oder 40 mg. Welche Menge sinnvoll ist, hängt von deinem Vorhaben ab. Bei der Produktauswahl helfen wir dir gern auf Telegram.'],
        ['Wie viel davon soll ich verwenden?', 'Schreib uns dazu einfach auf <a href="' . $tg . '" data-ev="tg-faq" target="_blank" rel="noopener">Telegram</a>.'],
        ['Wie lagere ich das?', 'Als Pulver kühl und dunkel. Nach dem Anmischen im Kühlschrank bei 2–8 °C. Details im <a href="' . $u('/lagerungs-guide/') . '">Lagerungs-Guide</a>.'],
        ['Wie bezahle ich – und ist das sicher?', 'Per Überweisung (Vorkasse) oder Krypto. Die Bankdaten kommen sofort per E-Mail. Sobald deine Zahlung da ist, bekommst du eine Bestätigung und danach die DHL-Sendungsnummer.'],
        ['Was, wenn etwas mit meinem Paket nicht stimmt?', 'Schreib uns auf <a href="' . $tg . '" data-ev="tg-faq" target="_blank" rel="noopener">Telegram</a> oder per E-Mail an <!--email_off-->info@peptrium.com<!--/email_off-->, am besten mit Bestellnummer und einem Foto. Wir kümmern uns darum.'],
    ];
    $faq_html = '';
    foreach ($faq as $i => $f) {
        $faq_html .= '<details data-ev="faq-' . ($i + 1) . '"><summary>' . esc_html($f[0]) . '</summary><p>' . $f[1] . '</p></details>';
    }
    ob_start();
    ?>
<div id="sp-start">
 <header class="st-hero"><div class="st-in">
  <img class="st-hero-img" src="<?php echo esc_url($set_img); ?>" alt="Retatrutide-Fläschchen mit Bac Water und Spritzen" width="1200" height="895">
  <div class="st-eb">DU KOMMST AUS DEM VIDEO?</div>
  <h1>Retatrutide – einfach erklärt, sicher bestellt.</h1>
  <p><b>Retatrutide</b> ist ein Forschungspeptid aus der Stoffwechselforschung. Hier siehst du in 1 Minute, welche Variante zu dir passt und wie die Bestellung abläuft.</p>
  <div class="st-facts"><span>HPLC- &amp; LC-MS-geprüft</span><span>Versand aus Deutschland</span><span>2 Werktage</span><span>Hilfe auf Telegram</span></div>
 </div></header>
 <div class="st-in">
  <section class="st-sec" data-sec="auswahl"><h2>Welche Variante passt zu dir?</h2><p class="st-sub">Das Peptid ist dasselbe. Der Unterschied liegt nur in der Vorbereitung.</p>
   <div class="st-choose">
    <div class="st-opt"><div class="st-row"><img src="<?php echo esc_url($pen_img); ?>" alt="Peptrium-Pen Retatrutide" loading="lazy"><div><div class="st-who">Am einfachsten · ohne Vorkenntnisse</div><h3>Peptrium-Pen</h3><?php echo sp_st_stars(393); ?><p>Fertig gemischt. Kein Anmischen, kein Abmessen.</p></div></div>
     <div class="st-inset"><b>Im Set:</b> Pen mit Retatrutide 20 mg · Pen Nadeln (15 Stück)</div>
     <div class="st-ft"><b><?php echo esc_html(sp_st_price([547, 908])); ?><small>auch mit <a href="<?php echo $u('/produkt/peptrium-pen-retatrutide/'); ?>" data-ev="pen-groessen">30 oder 40 mg</a></small></b><a class="st-btn" data-ev="set-pen" data-items="547,908" href="<?php echo $u('/?sp_set=reta-pen'); ?>">In den Warenkorb</a></div></div>
    <div class="st-opt"><div class="st-row"><img src="<?php echo esc_url($set_img); ?>" alt="Retatrutide Starter-Set" loading="lazy"><div><div class="st-who">Günstiger · mit Anmischen</div><h3>Starter-Set Fläschchen</h3><?php echo sp_st_stars(65); ?><p>Wird vor der Verwendung mit Bac Water angemischt.</p></div></div>
     <div class="st-inset"><b>Im Set:</b> Retatrutide 10 mg (Pulver) · Bac Water · 10 Spritzen mit Skala</div>
     <div class="st-ft"><b><?php echo esc_html(sp_st_price([555, 74, 80])); ?><small>komplett, alles dabei</small></b><a class="st-btn" data-ev="set-starter" data-items="555,74,80" href="<?php echo $u('/?sp_set=reta-starter'); ?>">In den Warenkorb</a></div></div>
   </div>
   <a class="st-shop" data-ev="shop" href="<?php echo $u('/alle-produkte/'); ?>">Lieber erst stöbern? Alle Produkte ansehen →</a>
   <?php echo $ask; ?>
  </section>
  <section class="st-sec" data-sec="ablauf"><h2>Was nach deiner Bestellung passiert</h2><div class="st-tl">
   <div class="st-st"><span>1</span><div><b>Bestellung abschicken</b>Ohne Kundenkonto möglich.<i>✉ Du bekommst sofort eine E-Mail mit den Bankdaten.</i></div></div>
   <div class="st-st"><span>2</span><div><b>Bezahlen</b>Per Überweisung oder Krypto.<i>✉ Sobald das Geld da ist: Zahlungsbestätigung.</i></div></div>
   <div class="st-st"><span>3</span><div><b>Versand</b>In 2 Werktagen per DHL, neutral verpackt.<i>✉ Mail mit deiner DHL-Sendungsnummer.</i></div></div>
   <div class="st-st"><span>4</span><div><b>Ankommen</b>Etwas unklar oder beschädigt?<i><?php echo $ic; ?> Kurz auf Telegram melden, wir kümmern uns.</i></div></div>
  </div></section>
  <section class="st-sec st-rev" data-sec="stimmen"><h2>Das sagen unsere Kunden</h2><div class="st-revs"><?php
   foreach (sp_st_reviews() as $r) {
       echo '<figure class="st-rc"><div class="st-rs" aria-label="' . (int) $r[2] . ' von 5 Sternen">' . str_repeat('★', (int) $r[2]) . '<span>' . str_repeat('★', 5 - (int) $r[2]) . '</span></div><blockquote>' . esc_html($r[1]) . '</blockquote><figcaption><i>' . esc_html(implode('', array_map(function ($w) { return mb_substr($w, 0, 1); }, explode(' ', $r[0])))) . '</i>' . esc_html($r[0]) . '</figcaption></figure>';
   } ?></div></section>
  <section class="st-help" data-sec="hilfe"><b>Fragen? Schreib uns einfach.</b><p>Auf Telegram bekommst du eine persönliche Antwort. Wir helfen dir zum Beispiel bei:</p>
   <ul><li>der Wahl zwischen Pen und Fläschchen</li><li>Fragen zur Bestellung und Bezahlung</li><li>deiner Sendung</li></ul>
   <a class="st-tg" data-ev="tg-box" href="<?php echo $tg; ?>" target="_blank" rel="noopener"><?php echo $ic; ?> Auf Telegram schreiben (@peptrium)</a>
   <p class="st-mail">Lieber per E-Mail? <!--email_off-->info@peptrium.com<!--/email_off--></p></section>
  <section class="st-sec st-faq" data-sec="fragen"><h2>Häufige Fragen</h2><?php echo $faq_html; // phpcs:ignore ?></section>
  <section class="st-sec" data-sec="wissen"><h2>Wissen für Einsteiger</h2><p class="st-sub">Zum Aufklappen, wenn du es genauer wissen willst.</p><div class="st-faq">
   <details data-ev="w-begriffe"><summary>Die wichtigsten Begriffe kurz erklärt</summary><div class="st-gl">
    <div><b>mg (Milligramm)</b>Wie viel Peptid im Fläschchen oder Pen enthalten ist.</div>
    <div><b>Lyophilisiert</b>Gefriergetrocknet, also als Pulver. So bleibt das Peptid beim Versand stabil.</div>
    <div><b>Bac Water</b>Steriles Wasser zum Auflösen des Pulvers.</div>
    <div><b>Rekonstituieren</b>Das Fachwort für „Pulver mit Bac Water auflösen“.</div>
    <div><b>Pen Nadeln</b>Aufsteckbare Einwegnadeln für den Pen.</div></div></details>
   <details data-ev="w-anmischen"><summary>Anmischen: Anleitung, Rechner, Lagerung</summary><div class="st-how">
    <a href="<?php echo $u('/ratgeber/peptide-rekonstituieren/'); ?>">Schritt-für-Schritt-Anleitung <span>→</span></a>
    <a href="<?php echo $u('/peptid-rechner/'); ?>">Peptid-Rechner: Konzentration ausrechnen <span>→</span></a>
    <a href="<?php echo $u('/lagerungs-guide/'); ?>">Lagerungs-Guide <span>→</span></a></div></details>
   <details data-ev="w-qualitaet"><summary>Unsere Qualität: HPLC &amp; LC-MS</summary><div class="st-qual">
    <div><b>HPLC-Reinheit</b>Mindeststandard ≥ 99 %</div><div><b>LC-MS-Identität</b>Richtiges Peptid bestätigt</div>
    <div><b>Lyophilisiert</b>Stabil für den Versand</div><div><b>Diskret</b>Neutraler Karton, kein Aufdruck</div></div>
    <p class="st-sub" style="margin-top:10px">Was die Prüfverfahren bedeuten: <a href="<?php echo $u('/ratgeber/hplc-lc-ms-reinheit/'); ?>">HPLC und LC-MS erklärt</a></p></details>
   <details data-ev="w-hintergrund"><summary>Hintergrund zu Retatrutide</summary><div class="st-how">
    <a href="<?php echo $u('/peptid-lexikon/retatrutide/'); ?>">Retatrutide im Peptid-Lexikon <span>→</span></a>
    <a href="<?php echo $u('/ratgeber/retatrutide-tirzepatide-semaglutid/'); ?>">Retatrutide vs. Tirzepatide vs. Semaglutid <span>→</span></a></div></details>
  </div></section>
  <div class="st-code">Erste Bestellung? Mit dem Code <b>TIKTOK10</b> sparst du 10 %. Er wird automatisch abgezogen, sobald du etwas in den Warenkorb legst.</div>
  <p class="st-dis">Alle Produkte sind ausschließlich für die Laborforschung bestimmt. Sie sind keine Arzneimittel und nicht zur Anwendung am Menschen oder Tier zugelassen. Zur Anwendung geben wir keine Auskunft.</p>
  <div data-sec="ende"></div>
 </div>
 <div class="st-bar"><span><b>Fragen?</b>Wir helfen dir persönlich.</span><a data-ev="tg-bar" href="<?php echo $tg; ?>" target="_blank" rel="noopener"><?php echo $ic; ?> Telegram</a></div>
</div>
    <?php
    return ob_get_clean();
}, 20);

add_action('wp_head', function () {
    if (!sp_st_is()) {
        return;
    }
    ?>
<style id="sp-start-css">
body.sp-start-page{background:#F4F5F7}
body.sp-start-page .site-content>.ast-container{max-width:100%!important;padding:0!important;display:block}
body.sp-start-page #primary{margin:0!important;padding:0!important;width:100%}
body.sp-start-page .entry-header,body.sp-start-page .post-thumb{display:none!important}
body.sp-start-page article.page{margin:0!important;padding:0!important;background:none!important;box-shadow:none!important}
body.sp-start-page .entry-content{margin:0!important}
body.sp-start-page #sp-social-fab{display:none!important}
body.sp-start-page #sp-cart-fab{transform:translateY(-64px)}
#sp-start{--fg:#15181C;--mu:#5A6169;--ln:#E1E4E8;--d:#0D0F12;color:var(--fg);font-size:16px;line-height:1.55;padding-bottom:80px}
#sp-start .st-in{max-width:640px;margin:0 auto;padding:0 16px}
#sp-start a{color:var(--d)}
#sp-start .st-hero{background:#0D0F12;color:#fff;padding:0 0 26px}
#sp-start .st-hero .st-in{padding:0}
#sp-start .st-hero-img{display:block;width:100%;height:auto;aspect-ratio:1200/895;-webkit-mask-image:linear-gradient(#000 78%,transparent);mask-image:linear-gradient(#000 78%,transparent)}
@media(min-width:700px){#sp-start .st-hero-img{border-radius:0 0 22px 22px;-webkit-mask-image:none;mask-image:none}}
#sp-start .st-hero .st-eb,#sp-start .st-hero h1,#sp-start .st-hero p,#sp-start .st-facts{margin-left:16px;margin-right:16px}
#sp-start .st-eb{font-size:11.5px;font-weight:800;letter-spacing:.16em;color:#9AA1A9;margin-top:18px}
#sp-start .st-hero h1{color:#fff;font-family:Sora,sans-serif;font-size:clamp(26px,6.5vw,36px);line-height:1.15;margin-top:6px;margin-bottom:10px;font-weight:700;text-wrap:balance}
#sp-start .st-hero p{color:#C9CDD2;margin-top:0;margin-bottom:0}#sp-start .st-hero p b{color:#fff}
#sp-start .st-facts{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px}
#sp-start .st-facts span{border:1px solid #2E333A;border-radius:99px;padding:6px 11px;font-size:12.5px;color:#DDE0E3}
#sp-start .st-sec{padding:26px 0 0}
#sp-start h2{font-family:Sora,sans-serif;font-size:21px;line-height:1.3;margin:0 0 4px;color:var(--d);font-weight:700}
#sp-start .st-sub{color:var(--mu);margin:0 0 14px;font-size:14.5px;line-height:1.5}
#sp-start .st-choose{display:grid;gap:12px}
@media(min-width:620px){#sp-start .st-choose{grid-template-columns:1fr 1fr}}
#sp-start .st-opt{background:#fff;border:1px solid var(--ln);border-radius:18px;overflow:hidden;display:flex;flex-direction:column}
#sp-start .st-row{display:flex;gap:12px;padding:14px}
#sp-start .st-row img{width:84px;height:84px;object-fit:cover;border-radius:12px;flex:0 0 84px;background:#0D0F12}
#sp-start .st-who{font-size:11.5px;font-weight:800;letter-spacing:.05em;color:var(--mu);text-transform:uppercase}
#sp-start h3{margin:2px 0 4px;font-size:17px;font-family:Sora,sans-serif;color:var(--d)}
#sp-start .st-stars{display:flex;align-items:baseline;gap:4px;font-size:13px;margin:0 0 4px;color:var(--mu)}#sp-start .st-stars span{color:#F5A623;letter-spacing:1px}#sp-start .st-stars b{color:var(--fg)}
#sp-start .st-revs{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x mandatory;margin:0 -16px;padding:2px 16px 8px;scrollbar-width:none}#sp-start .st-revs::-webkit-scrollbar{display:none}
#sp-start .st-rc{flex:0 0 78%;max-width:300px;scroll-snap-align:start;margin:0;background:#fff;border:1px solid var(--ln);border-radius:16px;padding:16px;display:flex;flex-direction:column;gap:10px}
#sp-start .st-rs{color:#F5A623;letter-spacing:2px;font-size:15px}#sp-start .st-rs span{color:#D5D9DE}
#sp-start .st-rc blockquote{margin:0;padding:0;border:0;font-size:14.5px;line-height:1.55;color:#2B3036;font-style:normal;flex:1}
#sp-start .st-rc figcaption{display:flex;align-items:center;gap:8px;font-size:13.5px;font-weight:700;color:var(--fg)}#sp-start .st-rc figcaption i{width:30px;height:30px;border-radius:50%;background:#0D0F12;color:#fff;font-style:normal;font-size:11.5px;display:flex;align-items:center;justify-content:center}
@media(min-width:700px){#sp-start .st-rc{flex-basis:46%}}
#sp-start .st-row p{margin:0;color:var(--mu);font-size:14px;line-height:1.45}
#sp-start .st-inset{margin:0 14px 12px;background:#F4F5F7;border-radius:12px;padding:10px 12px;font-size:13.5px;color:#3A4047;line-height:1.55}
#sp-start .st-ft{margin-top:auto;display:flex;align-items:center;justify-content:space-between;gap:10px;border-top:1px solid var(--ln);padding:12px 14px}
#sp-start .st-ft b{font-size:19px}#sp-start .st-ft small{display:block;color:var(--mu);font-size:12.5px;font-weight:400}
#sp-start .added_to_cart{display:none!important}
#sp-start .st-btn.is-busy{opacity:.6;pointer-events:none}#sp-start .st-btn.is-done{background:#2FA36B!important}
#sp-start .st-btn{background:#0D0F12!important;color:#fff!important;border-radius:12px;padding:12px 15px;font-weight:700;text-decoration:none!important;font-size:14.5px;white-space:nowrap}
#sp-start .st-shop{display:block;text-align:center;margin:14px 0 0;font-weight:700;font-size:14.5px}
#sp-start .ask{display:flex;align-items:center;justify-content:center;gap:8px;margin:10px 0 0;color:#2A7BC0!important;font-weight:700;font-size:14.5px;text-decoration:none!important}
#sp-start .st-tl{background:#fff;border:1px solid var(--ln);border-radius:18px;padding:4px 16px}
#sp-start .st-st{display:flex;gap:12px;padding:13px 0;border-top:1px solid var(--ln)}#sp-start .st-st:first-child{border-top:0}
#sp-start .st-st>span{flex:0 0 28px;height:28px;border-radius:50%;background:#0D0F12;color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center;font-size:13px}
#sp-start .st-st div{font-size:14.5px;line-height:1.5;color:var(--mu)}#sp-start .st-st b{color:var(--fg);display:block}
#sp-start .st-st i{display:flex;align-items:center;gap:5px;font-style:normal;font-size:13px;color:#2FA36B;margin-top:3px}
#sp-start .st-help{margin:26px 0 0;background:#E8F4FC;border:1px solid #BFE0F5;border-radius:18px;padding:18px}
#sp-start .st-help>b{display:block;font-size:18px;margin-bottom:6px;font-family:Sora,sans-serif}
#sp-start .st-help p{margin:0 0 10px;color:#3A4047;font-size:14.5px}
#sp-start .st-help ul{margin:0 0 14px;padding-left:20px;color:#3A4047;font-size:14px;line-height:1.7}
#sp-start .st-tg{display:flex;align-items:center;justify-content:center;gap:8px;background:#2AABEE!important;color:#fff!important;border-radius:12px;padding:14px 16px;font-weight:800;text-decoration:none!important;font-size:15.5px}
#sp-start .st-mail{margin:10px 0 0!important;font-size:13px!important}
#sp-start .st-faq details{background:#fff;border:1px solid var(--ln);border-radius:14px;padding:14px 16px;margin-bottom:8px}
#sp-start .st-faq summary{font-weight:700;cursor:pointer;font-size:15px;list-style:none;display:flex;justify-content:space-between;gap:12px}
#sp-start .st-faq summary::-webkit-details-marker{display:none}
#sp-start .st-faq summary::after{content:'+';font-size:20px;line-height:1;color:var(--mu)}#sp-start .st-faq details[open] summary::after{content:'–'}
#sp-start .st-faq details p{margin:8px 0 0;color:#3A4047;font-size:14.5px;line-height:1.6}
#sp-start .st-gl{display:grid;gap:8px;margin-top:10px}#sp-start .st-gl div{background:#F4F5F7;border-radius:12px;padding:11px 13px;font-size:14px;line-height:1.5;color:var(--mu)}#sp-start .st-gl b{color:var(--fg);display:block}
#sp-start .st-how{margin-top:6px}#sp-start .st-how a{display:flex;justify-content:space-between;gap:10px;padding:12px 0;border-top:1px solid var(--ln);text-decoration:none;font-weight:700;font-size:14.5px}#sp-start .st-how a:first-child{border-top:0}
#sp-start .st-qual{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}#sp-start .st-qual div{background:#F4F5F7;border-radius:12px;padding:12px;font-size:13.5px;line-height:1.45;color:var(--mu)}#sp-start .st-qual b{display:block;color:var(--fg)}
#sp-start .st-code{margin:22px 0 0;background:#fff;border:1px dashed #9AA1A9;border-radius:14px;padding:14px 16px;font-size:14.5px;line-height:1.5;color:var(--mu)}#sp-start .st-code b{color:var(--fg)}
#sp-start .st-dis{color:#7C838B;font-size:12.5px;line-height:1.5;margin:18px 0 0}
#sp-start .st-bar{position:fixed;left:0;right:0;bottom:0;z-index:999;background:rgba(244,245,247,.97);border-top:1px solid var(--ln);padding:10px max(16px,env(safe-area-inset-right)) calc(10px + env(safe-area-inset-bottom)) max(16px,env(safe-area-inset-left));display:flex;align-items:center;gap:10px;box-shadow:0 -6px 20px rgba(0,0,0,.06)}
#sp-start .st-bar span{flex:1;font-size:13px;color:var(--mu);line-height:1.35}#sp-start .st-bar span b{color:var(--fg);display:block;font-size:14.5px}
#sp-start .st-bar a{display:flex;align-items:center;gap:6px;background:#2AABEE!important;color:#fff!important;border-radius:11px;padding:11px 14px;font-weight:800;text-decoration:none!important;font-size:14.5px;white-space:nowrap}
@media(min-width:900px){#sp-start .st-bar{left:auto;right:24px;bottom:24px;border-radius:16px;border:1px solid var(--ln);max-width:360px}}
</style>
    <?php
}, 30);

add_action('wp_footer', function () {
    if (!sp_st_is()) {
        return;
    }
    ?>
<script id="sp-start-js">
(function(){
 var url=<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,sent={};
 function ev(t,v){var k=t+':'+v;if(sent[k])return;sent[k]=1;try{var f=new FormData();f.append('action','sp_st_ev');f.append('t',t);f.append('v',v);navigator.sendBeacon?navigator.sendBeacon(url,f):fetch(url,{method:'POST',body:f,keepalive:true});}catch(e){}}
 document.addEventListener('click',function(e){var a=e.target.closest('#sp-start [data-ev]');if(!a)return;if(a.tagName==='DETAILS'){if(e.target.closest('summary'))ev('click',a.getAttribute('data-ev'));return;}ev('click',a.getAttribute('data-ev'));});
 /* Set-Buttons: alle Teile nacheinander in den Warenkorb legen, dann öffnet sich der Warenkorb (wie bei den Sets auf der Startseite).
    Ohne JS bleibt der Link (/?sp_set=… → direkt zur Kasse). */
 var wca=<?php echo wp_json_encode(home_url('/?wc-ajax=add_to_cart')); ?>;
 document.querySelectorAll('#sp-start .st-btn[data-items]').forEach(function(b){b.addEventListener('click',function(e){
  if(!window.fetch)return;e.preventDefault();if(b.classList.contains('is-busy'))return;
  var ids=b.getAttribute('data-items').split(','),txt=b.textContent;b.classList.add('is-busy');b.textContent='Wird hinzugefügt …';
  ids.reduce(function(p,id){return p.then(function(){var f=new FormData();f.append('product_id',id);f.append('quantity','1');return fetch(wca,{method:'POST',body:f,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(j){if(j&&j.error)throw new Error('add');});});},Promise.resolve())
  .then(function(){b.classList.remove('is-busy');b.classList.add('is-done');b.textContent='✓ Im Warenkorb';
    if(window.jQuery){jQuery(document.body).trigger('wc_fragment_refresh');jQuery(document.body).trigger('added_to_cart',[{},'',jQuery()]);}
    setTimeout(function(){b.classList.remove('is-done');b.textContent=txt;},4000);})
  .catch(function(){location.href=b.href;});
 });});
 if('IntersectionObserver' in window){var io=new IntersectionObserver(function(es){es.forEach(function(x){if(x.isIntersecting){ev('scroll',x.target.getAttribute('data-sec'));io.unobserve(x.target);}});},{threshold:.4});
  document.querySelectorAll('#sp-start [data-sec]').forEach(function(s){io.observe(s);});}
})();
</script>
    <?php
}, 50);
