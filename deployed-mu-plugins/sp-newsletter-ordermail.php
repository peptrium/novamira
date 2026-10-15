<?php
/**
 * Plugin Name: SP Newsletter-Einladung in Bestellmails
 * Description: Kasten "10 % auf deine naechste Bestellung" in den Kundenmails (Bestellung erhalten / Zahlung erhalten / Versendet) fuer Kunden, die noch nicht im Newsletter sind.
 *
 * (2026-10-16) Der Button fuehrt auf /?sp_nl_join=<Bestell-ID>&k=<Signatur>. Dort bestaetigt der Kunde die Anmeldung
 * mit einem Klick (POST, damit Link-Vorschauen von Mailprogrammen nichts ausloesen); danach laeuft der normale
 * Double-Opt-in aus sp-newsletter.php (Tag "Quelle: Bestellmail"). Zuruecksetzen: Datei loeschen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_nlo_sig($order_id) {
    return substr(hash_hmac('sha256', 'sp_nl_join|' . (int) $order_id, wp_salt('auth')), 0, 20);
}

function sp_nlo_status($email) {
    if (!class_exists('\FluentCrm\App\Models\Subscriber') || !is_email($email)) {
        return '';
    }
    $s = \FluentCrm\App\Models\Subscriber::where('email', strtolower(trim($email)))->first();
    return $s ? (string) $s->status : '';
}

add_action('woocommerce_email_customer_details', function ($order, $sent_to_admin = false, $plain_text = false, $email = null) {
    if ($sent_to_admin || $plain_text || !$order instanceof WC_Order || !function_exists('sp_nl_subscribe') || !function_exists('sp_em_card')) {
        return;
    }
    $ids = ['customer_on_hold_order', 'customer_processing_order', 'customer_completed_order'];
    if (!$email || !in_array($email->id, $ids, true)) {
        return;
    }
    if (in_array(sp_nlo_status($order->get_billing_email()), ['subscribed', 'unsubscribed', 'bounced', 'complained'], true)) {
        return; // schon dabei oder bewusst abgemeldet
    }
    $url = add_query_arg(['sp_nl_join' => $order->get_id(), 'k' => sp_nlo_sig($order->get_id())], home_url('/'));
    echo sp_em_card(
        '<p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#9AA0A8;">Peptrium Newsletter</p>'
        . '<p style="margin:0 0 6px;font-size:20px;font-weight:800;color:#FFFFFF;">10&nbsp;% auf deine nächste Bestellung</p>'
        . '<p style="margin:0 0 16px;font-size:14px;line-height:1.55;color:#C9CDD2;">Melde dich an und bekomm deinen persönlichen Code – dazu Nachschub-Erinnerungen und Neuheiten. Abmeldung jederzeit mit einem Klick.</p>'
        . '<a href="' . esc_url($url) . '" style="display:inline-block;background:#FFFFFF;color:#0D0F12;text-decoration:none;font-weight:800;font-size:15px;line-height:1.2;padding:14px 26px;border-radius:12px;">Code sichern</a>',
        '#12151A',
        '#2C3138'
    );
}, 50, 4);

add_action('template_redirect', function () {
    if (empty($_GET['sp_nl_join'])) {
        return;
    }
    $oid = (int) $_GET['sp_nl_join'];
    $ok = $oid && hash_equals(sp_nlo_sig($oid), (string) ($_GET['k'] ?? ''));
    $order = $ok ? wc_get_order($oid) : null;
    nocache_headers();
    $state = 'ask';
    if (!$order) {
        $state = 'invalid';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && function_exists('sp_nl_subscribe')) {
        $r = sp_nl_subscribe($order->get_billing_email(), trim($order->get_billing_first_name()), trim($order->get_billing_last_name()), 'bestellmail');
        $state = $r === 'already' ? 'already' : ($r === 'error' ? 'error' : 'sent');
    } elseif (sp_nlo_status($order->get_billing_email()) === 'subscribed') {
        $state = 'already';
    }
    $logo = get_option('woocommerce_email_header_image');
    $mail = $order ? $order->get_billing_email() : '';
    $texts = [
        'ask' => ['10 % auf deine nächste Bestellung', 'Melde dich mit <!--email_off--><strong>' . esc_html($mail) . '</strong><!--/email_off--> für den Peptrium-Newsletter an. Du bekommst gleich eine Mail zum Bestätigen – danach sofort deinen persönlichen Code.'],
        'sent' => ['Fast geschafft! 📬', 'Wir haben dir eine Mail an <!--email_off--><strong>' . esc_html($mail) . '</strong><!--/email_off--> geschickt. Klick dort auf „Anmeldung bestätigen“ – danach bekommst du deinen Code.'],
        'already' => ['Du bist schon dabei ✓', 'Diese E-Mail-Adresse ist bereits für den Newsletter angemeldet.'],
        'invalid' => ['Link ungültig', 'Dieser Link funktioniert leider nicht. Du kannst dich auch unten auf jeder Seite des Shops anmelden.'],
        'error' => ['Das hat nicht geklappt', 'Bitte versuch es später noch einmal oder melde dich unten auf der Startseite an.'],
    ];
    [$h, $p] = $texts[$state];
    ?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Newsletter – Peptrium</title>
    <style>
    body{margin:0;background:#0D0F12;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;color:#fff}
    .w{max-width:520px;margin:0 auto;padding:40px 18px 50px;text-align:center}
    .logo{width:96px;height:auto}.wm{font-size:18px;font-weight:800;letter-spacing:.32em;color:#E6E8EB;margin:10px 0 26px;padding-left:.32em}
    h1{font-size:27px;line-height:1.2;margin:0 0 12px}p{color:#C9CDD2;font-size:15px;line-height:1.6;margin:0 0 24px}
    .bt{display:block;width:100%;box-sizing:border-box;border:0;border-radius:12px;padding:16px;font-weight:800;font-size:16px;text-decoration:none;cursor:pointer;font-family:inherit;background:#fff;color:#0D0F12}
    .b2{background:transparent;color:#fff;border:1.5px solid #fff;margin-top:10px}.s{font-size:12px;color:#7D848D;margin-top:18px}
    </style></head><body><div class="w">
    <?php if ($logo) : ?><img class="logo" src="<?php echo esc_url($logo); ?>" alt="Peptrium"><?php endif; ?>
    <div class="wm">PEPTRIUM</div>
    <h1><?php echo esc_html($h); ?></h1><p><?php echo $p; // Texte fest, E-Mail per esc_html ?></p>
    <?php if ($state === 'ask') : ?>
      <form method="post"><button class="bt" type="submit">Jetzt anmelden</button></form>
      <div class="s">Abmeldung jederzeit über den Link in jeder Mail. Infos in der <a href="<?php echo esc_url(home_url('/datenschutz/')); ?>" style="color:#9AA0A8;">Datenschutzerklärung</a>.</div>
    <?php else : ?>
      <a class="bt b2" href="<?php echo esc_url(home_url('/alle-produkte/')); ?>">Zum Shop &rarr;</a>
    <?php endif; ?>
    </div></body></html><?php
    exit;
});
