<?php
/**
 * Transaktions-E-Mails rund um Guthaben und Abo (Phase 6 der Abo-Checkliste):
 * Guthaben-Aufladung, Abo-Start, Erinnerung bei absehbar nicht ausreichendem
 * Guthaben vor der naechsten Faelligkeit, sowie Pausierung/Kuendigung.
 *
 * Die Versandbestaetigung pro Abo-Lieferung braucht hier keinen eigenen Code:
 * jede Abo-Lieferung ist eine ganz normale WC_Order (sp-subscriptions.php),
 * die genau wie jede andere Bestellung ueber sp-order-tracking.php eine
 * Sendungsnummer bekommt und dabei automatisch die bestehende
 * customer-completed-order-E-Mail (Chrome-Mono-Design) ausloest.
 *
 * Stil/Bausteine (sp_email_box_style, WC()->mailer()->wrap_message fuer den
 * bereits gebrandeten Header/Footer) folgen bewusst demselben Muster wie die
 * Bestell-E-Mails in sp-order-emails.php, statt ein zweites Email-Design
 * einzufuehren. Texte sind bewusst kurz gehalten (ein, zwei Saetze pro
 * Abschnitt) statt langer Erklaerungen - die Fakten stehen in der Info-Box,
 * nicht im Fliesstext.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_ABO_REMINDER_DAYS_BEFORE_DUE', 3);

/** Gemeinsamer Versand-Wrapper: nutzt WooCommerce's eigenen (bereits im Chrome-Mono-Design angepassten) Email-Header/Footer. */
/* $accent: 'abo' (Standard, alle Abo-/Guthaben-Mails) = orange Buttons + "Peptrium Abo"
   ueber der Ueberschrift; 'dark' fuer Mails ohne Abo-Bezug (Warenkorb-, Zahlungserinnerung). */
function sp_abo_send_branded_email($to, $subject, $heading, $body_html, $heading_html = null, $accent = 'abo') {
    if (!$to) {
        return;
    }
    /* Neues Mail-Design (sp-email-design.php): ABO-Kennzeichnung als Zeile ueber der
       Ueberschrift statt "Peptrium ABO" als Ueberschrift unter dem PEPTRIUM-Schriftzug. */
    if (function_exists('sp_em_hero') && $accent === 'abo') {
        if ($heading_html !== null) {
            sp_em_hero(['eyebrow' => 'Peptrium Abo', 'accent' => 'abo', 'sub' => 'Ab jetzt kommt deine Lieferung automatisch.']);
            $heading = 'Willkommen im Abo 🎉';
            $heading_html = null;
        } else {
            sp_em_hero(['eyebrow' => 'Peptrium Abo', 'accent' => 'abo']);
        }
        $body_html = sp_em_abo_accent($body_html);
    }
    $mailer = WC()->mailer();
    $message = $mailer->wrap_message($heading, $body_html);
    if ($heading_html !== null) {
        $message = str_replace('<h1>' . esc_html($heading) . '</h1>', '<h1>' . $heading_html . '</h1>', $message);
    }
    $mailer->send($to, $subject, $message, "Content-Type: text/html\r\n");
}

function sp_abo_email_customer_email($user_id) {
    $user = get_userdata($user_id);
    return $user ? $user->user_email : '';
}

function sp_abo_email_product_name($subscription) {
    $product_id = $subscription->variation_id ?: $subscription->product_id;
    $product = wc_get_product($product_id);
    return $product ? $product->get_name() : ('Produkt #' . $subscription->product_id);
}

function sp_abo_email_account_link($label = 'Zu „Mein Abo“') {
    $url = wc_get_account_endpoint_url('abo');
    if (function_exists('sp_em_button')) {
        return sp_em_button(esc_html($label) . ' &rarr;', $url);
    }
    return '<a href="' . esc_url($url) . '" style="display:inline-block;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:9px;">' . esc_html($label) . ' &rarr;</a>';
}

/** Einheitliche, kurze Support-Zeile fuer alle Abo/Guthaben-Mails - gleiche zwei Kanaele wie im Rest des Shops (sp-social-fab.php). */
function sp_abo_email_support_line() {
    /* Kontakt (E-Mail + Telegram) steht jetzt im Footer jeder Mail (sp-email-templates/emails/email-footer.php). */
    if (function_exists('sp_em_button')) {
        return '';
    }
    return '<p style="margin:18px 0 0;font-size:12px;line-height:1.6;color:#8A9099;">Fragen? <a href="mailto:info@peptrium.com" style="color:#8A9099;">info@peptrium.com</a> &middot; <a href="https://t.me/peptrium" style="color:#8A9099;">Telegram</a></p>';
}

/**
 * Verlinkt auf den Abo-Stack-Konfigurator (/abo-stack/) - derselbe Wizard,
 * ueber den Kunden ihr Abo ueberhaupt erst zusammenstellen. Pro Mail an der
 * psychologisch passendsten Stelle platziert statt pauschal ueberall:
 * Aufladung/Start = Erweitern (Kunde ist gerade im "mehr"-Modus), Pausierung
 * = Wiedereinstieg vor dem Fortsetzen, Kuendigung = Winback ("falsche Wahl"
 * statt "kein Interesse" als Rahmen).
 */
/**
 * $group_key (optional): fuehrt direkt in den Bearbeiten-Modus fuer genau
 * diesen Stack (siehe sp-abo-picker.php, ?edit_stack=...) statt zum leeren
 * "neuen Stack zusammenstellen"-Wizard - sinnvoll ueberall dort, wo im
 * E-Mail-Kontext ein konkreter, bereits bestehender Stack gemeint ist (z.B.
 * die Willkommens-Mail nach einer Bestellung).
 */
function sp_abo_email_stack_link($label, $group_key = null) {
    $url = $group_key ? 'https://peptrium.com/abo-stack/?edit_stack=' . rawurlencode($group_key) : 'https://peptrium.com/abo-stack/';
    return '<a href="' . esc_url($url) . '" style="color:#0D0F12;font-weight:700;text-decoration:underline;">' . esc_html($label) . '</a>';
}

/* ---------------------------------------------------------------------
 * 1) Bestaetigung nach Guthaben-Aufladung.
 * ------------------------------------------------------------------- */
add_action('sp_wallet_credited', function ($user_id, $amount, $order_id) {
    $to = sp_abo_email_customer_email($user_id);
    if (!$to) {
        return;
    }
    $balance = sp_wallet_get_balance($user_id);

    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;">Dein Guthaben wurde aufgeladen.</p>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">AUFGELADEN</p>
      <p style="margin:0 0 16px;font-size:22px;font-weight:700;color:#0D0F12;"><?php echo wp_kses_post(wc_price($amount)); ?></p>
      <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">NEUER KONTOSTAND</p>
      <p style="margin:0;font-size:22px;font-weight:700;color:#0D0F12;"><?php echo wp_kses_post(wc_price($balance)); ?></p>
    </div>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Lust auf mehr? <?php echo sp_abo_email_stack_link('Jetzt deinen Stack erweitern →'); ?></p>
    <?php echo sp_abo_email_account_link('Mein Guthaben ansehen'); ?>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    $body = ob_get_clean();

    sp_abo_send_branded_email($to, 'Guthaben-Aufladung bestätigt', 'Guthaben-Aufladung bestätigt', $body);
}, 10, 3);

/* ---------------------------------------------------------------------
 * 2) Bestaetigung bei Abo-Start - EINE Mail fuer alle in der Bestellung
 * gestarteten Positionen (nicht eine separate Mail pro Produkt), damit ein
 * Mehrprodukt-Stack nicht zusaetzlich zur Zahlungsbestaetigung noch eine
 * eigene Mail pro Produkt obendrauf verschickt.
 *
 * $started: Array aus array('sub_id' => int, 'item' => WC_Order_Item_Product),
 * siehe sp_abo_maybe_start_subscriptions_from_order() in sp-abo-buybox.php.
 * ------------------------------------------------------------------- */
add_action('sp_abo_subscriptions_started', function ($started, $order) {
    $intervals = function_exists('sp_abo_get_intervals') ? sp_abo_get_intervals() : [];
    $rows = array();
    $total_next_price = 0.0;
    $next_date = null;
    $to = '';

    foreach ($started as $entry) {
        $subscription = sp_abo_get_subscription($entry['sub_id']);
        if (!$subscription) {
            continue;
        }
        if (!$to) {
            $to = sp_abo_email_customer_email($subscription->user_id);
        }
        $interval_label = $intervals[$subscription->interval_days] ?? ($subscription->interval_days . ' Tage');
        $product_name = sp_abo_email_product_name($subscription);

        // Naechste Abbuchung zeigt direkt den konkreten, bereits rabattierten
        // Betrag statt nur "15% Rabatt" als Text - charge_count ist hier
        // immer 0 (frisch angelegt), sp_abo_calculate_price() rabattiert aber
        // jede automatische Abbuchung unabhaengig davon (siehe sp-subscriptions.php).
        $next_product = wc_get_product($subscription->variation_id ?: $subscription->product_id);
        $next_price = $next_product ? sp_abo_calculate_price($subscription, $next_product) : null;
        if ($next_price !== null) {
            $total_next_price += $next_price;
        }
        if ($next_date === null || $subscription->next_payment_date < $next_date) {
            $next_date = $subscription->next_payment_date;
        }

        $rows[] = array(
            'name' => $product_name,
            'qty' => (int) $subscription->quantity,
            'interval' => $interval_label,
        );
    }

    if (empty($rows) || !$to) {
        return;
    }

    $next_date_formatted = $next_date ? date_i18n('d.m.Y', strtotime($next_date)) : null;
    $row_count = count($rows);
    $topup_product_id = function_exists('sp_wallet_get_topup_product_id') ? sp_wallet_get_topup_product_id() : 0;
    $topup_url = $topup_product_id ? get_permalink($topup_product_id) : '';

    ob_start();
    ?>
    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#0D0F12;">Willkommen bei <strong>Peptrium Abo</strong>! Dein<?php echo $row_count > 1 ? ' Stack läuft' : 'e Lieferung läuft'; ?> jetzt automatisch, ohne dass du je wieder bestellen musst.</p>
    <div style="<?php echo esc_attr(sp_email_box_style('#FFF1EA', '#FFD9C2')); ?>">
      <p style="margin:0;font-size:14px;line-height:1.5;color:#0D0F12;">🎁 Dein Willkommensgeschenk ist gratis in deiner ersten Lieferung dabei.</p>
    </div>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;"><?php echo $row_count > 1 ? 'DEIN ABO-STACK' : 'DEIN ABO'; ?></p>
      <?php foreach ($rows as $i => $row) : ?>
        <div style="<?php echo $i < $row_count - 1 ? 'border-bottom:1px solid #F2F3F4;padding-bottom:10px;margin-bottom:10px;' : ''; ?>">
          <p style="margin:0;font-size:15px;font-weight:700;color:#0D0F12;"><?php echo esc_html($row['name']); ?> &times; <?php echo (int) $row['qty']; ?></p>
          <p style="margin:2px 0 0;font-size:12.5px;color:#4B5157;">Intervall: <?php echo esc_html($row['interval']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($next_date_formatted) : ?>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">NÄCHSTE ABBUCHUNG</p>
      <p style="margin:0;font-size:18px;font-weight:700;color:#0D0F12;"><?php echo esc_html($next_date_formatted); ?><?php echo $total_next_price > 0 ? ' &ndash; ' . wp_kses_post(wc_price($total_next_price)) . ' (−15%)' : ''; ?></p>
    </div>
    <?php endif; ?>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Ab deiner nächsten Lieferung sicherst du dir dauerhaft −15&nbsp;% &ndash; automatisch, ohne dass du etwas tun musst.</p>
    <div style="<?php echo esc_attr(sp_email_box_style('#FFF1EA', '#FFD9C2')); ?>">
      <p style="margin:0 0 14px;font-size:13px;line-height:1.6;color:#0D0F12;">💡 <strong>Tipp:</strong> Halte dein Guthaben gefüllt, dann geht deine nächste Lieferung ganz automatisch raus &ndash; pünktlich, ohne Unterbrechung.</p>
      <?php if ($topup_url) : ?>
        <a href="<?php echo esc_url($topup_url); ?>" style="display:inline-block;background:linear-gradient(135deg,#FF6B35 0%,#E5342B 100%);color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:9px;">Jetzt Guthaben aufladen &rarr;</a>
      <?php endif; ?>
    </div>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Deinen Stack bearbeiten oder erweitern? <?php echo sp_abo_email_stack_link('Jetzt anpassen →', 'stack-order-' . $order->get_id()); ?></p>
    <?php echo sp_abo_email_account_link(); ?>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    $body = ob_get_clean();

    $subject = $row_count > 1 ? 'Willkommen bei Peptrium Abo – dein Stack läuft jetzt automatisch' : 'Willkommen bei Peptrium Abo – ' . $rows[0]['name'];
    sp_abo_send_branded_email($to, $subject, 'Peptrium ABO', $body, 'Peptrium <span style="color:#FF6B35;">ABO</span>');
}, 10, 2);

/* ---------------------------------------------------------------------
 * 3) Erinnerung bei absehbar nicht ausreichendem Guthaben vor Faelligkeit.
 *
 * Eigener, separater taeglicher Cronjob (unabhaengig vom Abbuchungs-Cronjob
 * in sp-subscriptions.php) - prueft Abos, die in genau
 * SP_ABO_REMINDER_DAYS_BEFORE_DUE Tagen faellig werden, und schlaegt nur dann
 * Alarm, wenn das aktuelle Guthaben dafuer schon jetzt nicht reichen wuerde.
 * Das exakte Datums-Match (statt <=) sorgt dafuer, dass die Erinnerung pro
 * Abbuchungszyklus genau einmal verschickt wird, ohne eine eigene
 * "schon erinnert"-Markierung in der Datenbank zu brauchen.
 * ------------------------------------------------------------------- */
add_action('init', function () {
    if (!wp_next_scheduled('sp_abo_reminder_daily_cron')) {
        wp_schedule_event(strtotime('tomorrow 7:00'), 'daily', 'sp_abo_reminder_daily_cron');
    }
});
add_action('sp_abo_reminder_daily_cron', 'sp_abo_check_upcoming_insufficient_funds');

function sp_abo_check_upcoming_insufficient_funds() {
    global $wpdb;
    $table = sp_abo_table_name();
    $target_date = date('Y-m-d', strtotime('+' . SP_ABO_REMINDER_DAYS_BEFORE_DUE . ' days'));

    $due_soon = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE status = 'active' AND next_payment_date = %s",
        $target_date
    ));

    foreach ($due_soon as $subscription) {
        $product_id = $subscription->variation_id ?: $subscription->product_id;
        $product = wc_get_product($product_id);
        if (!$product) {
            continue;
        }
        $price = sp_abo_calculate_price($subscription, $product);
        $balance = sp_wallet_get_balance($subscription->user_id);
        if ($balance < $price) {
            do_action('sp_abo_insufficient_funds_reminder', $subscription, $price, $balance);
        }
    }
}

add_action('sp_abo_insufficient_funds_reminder', function ($subscription, $price, $balance) {
    $to = sp_abo_email_customer_email($subscription->user_id);
    if (!$to) {
        return;
    }
    $product_name = sp_abo_email_product_name($subscription);
    $next_date = date_i18n('d.m.Y', strtotime($subscription->next_payment_date));
    $missing = max(0, $price - $balance);
    $topup_product_id = function_exists('sp_wallet_get_topup_product_id') ? sp_wallet_get_topup_product_id() : 0;
    $topup_url = $topup_product_id ? get_permalink($topup_product_id) : wc_get_account_endpoint_url('guthaben');

    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;">Dein Guthaben reicht noch nicht für die nächste Lieferung von <strong><?php echo esc_html($product_name); ?></strong> am <strong><?php echo esc_html($next_date); ?></strong>.</p>
    <div style="<?php echo esc_attr(sp_email_box_style('#FDECEA', '#F3C9C6')); ?>">
      <div style="border-bottom:1px solid rgba(179,38,30,.15);padding-bottom:12px;margin-bottom:12px;">
        <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#B3261E;">GUTHABEN</p>
        <p style="margin:0;font-size:18px;font-weight:700;color:#0D0F12;"><?php echo wp_kses_post(wc_price($balance)); ?></p>
      </div>
      <div>
        <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#B3261E;">ES FEHLEN</p>
        <p style="margin:0;font-size:18px;font-weight:700;color:#0D0F12;"><?php echo wp_kses_post(wc_price($missing)); ?></p>
      </div>
    </div>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Lade rechtzeitig auf, sonst pausieren wir dein Abo automatisch.</p>
    <a href="<?php echo esc_url($topup_url); ?>" style="display:inline-block;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:9px;">Jetzt aufladen &rarr;</a>
    <p style="margin:16px 0 0;font-size:13px;line-height:1.6;color:#4B5157;">Passt dir die Lieferung gerade nicht? Du kannst dein Abo jederzeit <a href="<?php echo esc_url(wc_get_account_endpoint_url('abo')); ?>" style="color:#0D0F12;font-weight:700;text-decoration:underline;">in deinem Konto pausieren</a>.</p>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    $body = ob_get_clean();

    sp_abo_send_branded_email($to, 'Guthaben für deine nächste Abo-Lieferung prüfen', 'Guthaben für deine nächste Lieferung prüfen', $body);
}, 10, 3);

/* ---------------------------------------------------------------------
 * 5) Bestaetigung bei Pausierung/Kuendigung - ein gemeinsamer Renderer fuer
 * alle vier Ausloeser (zwei bereits bestehende System-Pausierungen, zwei
 * neue kundenseitige Aktionen aus "Mein Abo").
 * ------------------------------------------------------------------- */
function sp_abo_send_status_email($subscription, $title, $heading, $explanation, $show_resume_hint, $topup_cta = false, $cancellation_receipt = false) {
    $to = sp_abo_email_customer_email($subscription->user_id);
    if (!$to) {
        return;
    }
    $product_name = sp_abo_email_product_name($subscription);
    $topup_product_id = function_exists('sp_wallet_get_topup_product_id') ? sp_wallet_get_topup_product_id() : 0;
    $topup_url = $topup_product_id ? get_permalink($topup_product_id) : wc_get_account_endpoint_url('guthaben');

    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;"><?php echo wp_kses_post($explanation); ?></p>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">PRODUKT</p>
      <p style="margin:0;font-size:16px;font-weight:700;color:#0D0F12;"><?php echo esc_html($product_name); ?></p>
    </div>
    <?php if ($cancellation_receipt): ?>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <div style="border-bottom:1px solid #F2F3F4;padding-bottom:12px;margin-bottom:12px;">
        <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">KÜNDIGUNG EINGEGANGEN AM</p>
        <p style="margin:0;font-size:16px;font-weight:700;color:#0D0F12;"><?php echo esc_html(date_i18n('d.m.Y \u\m H:i', current_time('timestamp'))); ?> Uhr</p>
      </div>
      <div>
        <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">VERTRAGSENDE</p>
        <p style="margin:0;font-size:16px;font-weight:700;color:#0D0F12;">Sofort, <?php echo esc_html(date_i18n('d.m.Y', current_time('timestamp'))); ?></p>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($topup_cta): ?>
    <a href="<?php echo esc_url($topup_url); ?>" style="display:inline-block;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:9px;">Jetzt Guthaben aufladen &rarr;</a>
    <p style="margin:16px 0 0;font-size:13px;line-height:1.6;color:#4B5157;">Sobald genug Guthaben da ist, läuft dein Abo beim nächsten Check automatisch wieder an &ndash; ohne dass du extra etwas tun musst.</p>
    <?php elseif ($show_resume_hint): ?>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Du kannst dein Abo jederzeit in deinem Konto fortsetzen.</p>
    <?php echo sp_abo_email_account_link(); ?>
    <p style="margin:16px 0 0;font-size:13px;line-height:1.6;color:#4B5157;">Bevor du fortsetzt: <?php echo sp_abo_email_stack_link('Schau dir deinen Stack an →'); ?></p>
    <?php else: ?>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Dein Restguthaben bleibt erhalten und verfällt nicht.</p>
    <div style="<?php echo esc_attr(sp_email_box_style('#FFF4EC', '#F5CBA7')); ?>">
      <p style="margin:0;font-size:13px;line-height:1.6;color:#0D0F12;">Hat Produkt oder Menge nicht gepasst? <?php echo sp_abo_email_stack_link('Stelle dir einen neuen Stack zusammen →'); ?></p>
    </div>
    <?php endif; ?>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    $body = ob_get_clean();

    sp_abo_send_branded_email($to, $title, $heading, $body);
}

add_action('sp_abo_paused_insufficient_funds', function ($subscription, $price, $balance) {
    sp_abo_send_status_email(
        $subscription,
        'Dein Abo wurde pausiert',
        'Dein Abo wurde pausiert',
        'Dein Guthaben hat nicht gereicht, deshalb haben wir dein Abo automatisch pausiert.',
        true,
        true
    );
}, 10, 3);

add_action('sp_abo_paused_product_unavailable', function ($subscription) {
    sp_abo_send_status_email(
        $subscription,
        'Dein Abo wurde pausiert',
        'Dein Abo wurde pausiert',
        'Das Produkt ist aktuell nicht verfügbar, deshalb haben wir dein Abo automatisch pausiert.',
        true
    );
}, 10, 1);

/* Automatisches Wiederaufnehmen nach Guthaben-Pausierung - Bestaetigung, dass die Lieferung jetzt rausgeht. */
add_action('sp_abo_auto_resumed', function ($subscription) {
    $to = sp_abo_email_customer_email($subscription->user_id);
    if (!$to) {
        return;
    }
    $product_name = sp_abo_email_product_name($subscription);

    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;">Dein Guthaben reicht wieder &ndash; dein Abo läuft automatisch weiter, <strong><?php echo esc_html($product_name); ?></strong> geht jetzt raus.</p>
    <?php echo sp_abo_email_account_link(); ?>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    $body = ob_get_clean();

    sp_abo_send_branded_email($to, 'Dein Abo läuft wieder – ' . $product_name, 'Dein Abo läuft wieder', $body);
}, 10, 1);

add_action('sp_abo_paused_customer_requested', function ($subscription) {
    sp_abo_send_status_email(
        $subscription,
        'Dein Abo wurde pausiert',
        'Dein Abo wurde pausiert',
        'Wie gewünscht haben wir dein Abo pausiert.',
        true
    );
}, 10, 1);

add_action('sp_abo_cancelled_customer_requested', function ($subscription) {
    sp_abo_send_status_email(
        $subscription,
        'Dein Abo wurde gekündigt',
        'Dein Abo wurde gekündigt',
        'Wie gewünscht haben wir dein Abo gekündigt.',
        false,
        false,
        true
    );
}, 10, 1);

/**
 * Stack-weite Pausierung/Kuendigung aus "Mein Abo" (sp-abo-myaccount.php):
 * der Stack wird dort als EINE Einheit bedient, daher auch hier bewusst nur
 * EINE Mail fuer die ganze Gruppe statt einer Mail pro enthaltenem Produkt -
 * gleiches Prinzip wie schon bei der Abo-Start-Mail (sp-abo-buybox.php).
 */
function sp_abo_send_stack_status_email($subscriptions, $title, $heading, $explanation, $cancellation_receipt = false) {
    if (empty($subscriptions)) {
        return;
    }
    $to = sp_abo_email_customer_email($subscriptions[0]->user_id);
    if (!$to) {
        return;
    }
    $intervals = function_exists('sp_abo_get_intervals') ? sp_abo_get_intervals() : [];

    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;"><?php echo wp_kses_post($explanation); ?></p>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;"><?php echo count($subscriptions) > 1 ? 'DEIN ABO-STACK' : 'DEIN ABO'; ?></p>
      <?php foreach ($subscriptions as $i => $subscription) :
          $product_name = sp_abo_email_product_name($subscription);
          $interval_label = $intervals[$subscription->interval_days] ?? ($subscription->interval_days . ' Tage');
      ?>
        <div style="<?php echo $i < count($subscriptions) - 1 ? 'border-bottom:1px solid #F2F3F4;padding-bottom:10px;margin-bottom:10px;' : ''; ?>">
          <p style="margin:0;font-size:15px;font-weight:700;color:#0D0F12;"><?php echo esc_html($product_name); ?> &times; <?php echo (int) $subscription->quantity; ?></p>
          <p style="margin:2px 0 0;font-size:12.5px;color:#4B5157;">Intervall: <?php echo esc_html($interval_label); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($cancellation_receipt) : ?>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <div style="border-bottom:1px solid #F2F3F4;padding-bottom:12px;margin-bottom:12px;">
        <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">KÜNDIGUNG EINGEGANGEN AM</p>
        <p style="margin:0;font-size:16px;font-weight:700;color:#0D0F12;"><?php echo esc_html(date_i18n('d.m.Y \u\m H:i', current_time('timestamp'))); ?> Uhr</p>
      </div>
      <div>
        <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;">VERTRAGSENDE</p>
        <p style="margin:0;font-size:16px;font-weight:700;color:#0D0F12;">Sofort, <?php echo esc_html(date_i18n('d.m.Y', current_time('timestamp'))); ?></p>
      </div>
    </div>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Dein Restguthaben bleibt erhalten und verfällt nicht.</p>
    <div style="<?php echo esc_attr(sp_email_box_style('#FFF4EC', '#F5CBA7')); ?>">
      <p style="margin:0;font-size:13px;line-height:1.6;color:#0D0F12;">Hat Produkt oder Menge nicht gepasst? <?php echo sp_abo_email_stack_link('Stelle dir einen neuen Stack zusammen →'); ?></p>
    </div>
    <?php else : ?>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Du kannst dein Abo jederzeit in deinem Konto fortsetzen.</p>
    <?php echo sp_abo_email_account_link(); ?>
    <?php endif; ?>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    $body = ob_get_clean();

    sp_abo_send_branded_email($to, $title, $heading, $body);
}

/**
 * $payload['subscriptions'] statt eines direkten Array-Arguments - siehe die
 * ausfuehrliche Erklaerung an den do_action()-Aufrufen in sp-abo-myaccount.php
 * (WordPress-Altlast-Bug: ein Array mit genau einem Objekt-Element waere dort
 * sonst von WordPress automatisch "ausgepackt" worden).
 */
add_action('sp_abo_stack_paused_customer_requested', function ($payload) {
    $subscriptions = $payload['subscriptions'];
    sp_abo_send_stack_status_email(
        $subscriptions,
        count($subscriptions) > 1 ? 'Dein Abo-Stack wurde pausiert' : 'Dein Abo wurde pausiert',
        count($subscriptions) > 1 ? 'Dein Abo-Stack wurde pausiert' : 'Dein Abo wurde pausiert',
        'Wie gewünscht haben wir deinen Stack pausiert.'
    );
}, 10, 1);

add_action('sp_abo_stack_cancelled_customer_requested', function ($payload) {
    $subscriptions = $payload['subscriptions'];
    sp_abo_send_stack_status_email(
        $subscriptions,
        count($subscriptions) > 1 ? 'Dein Abo-Stack wurde gekündigt' : 'Dein Abo wurde gekündigt',
        count($subscriptions) > 1 ? 'Dein Abo-Stack wurde gekündigt' : 'Dein Abo wurde gekündigt',
        'Wie gewünscht haben wir deinen Stack gekündigt.',
        true
    );
}, 10, 1);

/**
 * Vorab-Warnung ca. 7 Tage vor der automatischen Kuendigung wegen zu langer
 * Pausierung mangels Guthaben (siehe sp_abo_maybe_send_cancel_warning() in
 * sp-subscriptions.php) - nutzt denselben "Jetzt aufladen"-Baustein wie die
 * urspruengliche Pausierungs-Mail (sp_abo_paused_insufficient_funds unten),
 * damit der Kunde nicht von der endgueltigen Kuendigung ueberrascht wird.
 */
add_action('sp_abo_cancel_warning', function ($subscription) {
    $days_left = defined('SP_ABO_CANCEL_WARNING_DAYS_BEFORE') ? SP_ABO_CANCEL_WARNING_DAYS_BEFORE : 7;
    sp_abo_send_status_email(
        $subscription,
        'Dein Abo wird bald automatisch beendet',
        'Nur noch wenige Tage',
        'Dein Abo ist seit Längerem wegen fehlendem Guthaben pausiert. Lädst du nicht innerhalb der nächsten ' . (int) $days_left . ' Tage auf, wird es automatisch beendet.',
        false,
        true
    );
}, 10, 1);

/* Automatische Kuendigung nach zu langer Pausierung mangels Guthaben (siehe sp-subscriptions.php). */
add_action('sp_abo_cancelled_auto_abandoned', function ($subscription) {
    $to = sp_abo_email_customer_email($subscription->user_id);
    if (!$to) {
        return;
    }
    $product_name = sp_abo_email_product_name($subscription);
    $days = defined('SP_ABO_AUTO_CANCEL_AFTER_DAYS') ? SP_ABO_AUTO_CANCEL_AFTER_DAYS : 90;

    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;">Dein Abo für <strong><?php echo esc_html($product_name); ?></strong> war seit über <?php echo (int) $days; ?> Tagen pausiert, weil dein Guthaben nicht gereicht hat. Da in dieser Zeit nicht aufgeladen wurde, haben wir das Abo automatisch beendet.</p>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;">Dein Restguthaben bleibt erhalten und verfällt nicht. Du kannst jederzeit ein neues Abo starten.</p>
    <p style="margin:0 0 16px;font-size:13px;line-height:1.6;color:#4B5157;"><?php echo sp_abo_email_stack_link('Neuen Stack zusammenstellen &rarr;'); ?></p>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    $body = ob_get_clean();

    sp_abo_send_branded_email($to, 'Dein Abo wurde automatisch beendet', 'Dein Abo wurde beendet', $body);
}, 10, 1);

