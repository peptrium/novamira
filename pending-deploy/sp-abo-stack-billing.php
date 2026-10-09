<?php
/**
 * Plugin Name: SP Abo Stack-Abrechnung
 * Description: Rechnet einen Abo-Stack als EINE Einheit ab (2026-10-09).
 *
 * Vorher (sp-subscriptions.php) wurde jede Abo-Position einzeln abgerechnet:
 * ein 3-Produkte-Stack ergab pro Lieferung 3 Bestellungen/Pakete, und reichte
 * das Guthaben nur fuer einen Teil, wurde nur ein Teil geliefert und der Rest
 * pausiert (Stack zerfiel). Auch Erinnerung, Pausierungs-, Fortsetzungs- und
 * Kuendigungs-Mails kamen pro Position statt pro Stack.
 *
 * Diese Datei ersetzt die betroffenen Cronjob-Callbacks (remove_action) durch
 * stack-weite Versionen. Ein Stack = alle Positionen eines Kunden mit derselben
 * origin_order_id (gleiche Gruppierung wie "Mein Abo"); Positionen ohne
 * origin_order_id sind je ein eigener Stack.
 *
 *  - Abrechnung: alle am selben Tag faelligen Positionen eines Stacks -> eine
 *    Bestellung, eine Guthaben-Abbuchung. Reicht das Guthaben nicht fuer den
 *    ganzen Stack, wird der ganze Stack pausiert (eine Mail).
 *  - Fortsetzen nach Aufladung: sofort (nicht erst am naechsten Morgen) und
 *    nur stack-weise, wenn das Guthaben fuer den ganzen Stack reicht.
 *  - Erinnerung: 7 und 2 Tage vorher, mit der Summe aller bis dahin faelligen
 *    Positionen statt pro Produkt (Vorkasse braucht 1-2 Werktage).
 *  - "Stack fortsetzen" in "Mein Abo" ohne genug Guthaben: klare Meldung statt
 *    stiller Wieder-Pausierung am naechsten Morgen.
 *
 * Bausteine (sp_abo_calculate_price, sp_abo_create_charge_order,
 * sp_wallet_add_entry, E-Mail-Helfer) kommen unveraendert aus
 * sp-subscriptions.php / sp-wallet.php / sp-abo-emails.php. Rueckgaengig:
 * Datei loeschen - dann greifen wieder die alten Einzel-Callbacks.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_ASB_REMINDER_DAYS', '7,2');

/* Alte Einzel-Callbacks durch Stack-Versionen ersetzen (sp-subscriptions.php und
 * sp-abo-emails.php laden alphabetisch vor dieser Datei). */
remove_action('sp_abo_daily_cron', 'sp_abo_maybe_auto_resume_paused', 5);
remove_action('sp_abo_daily_cron', 'sp_abo_process_due_subscriptions', 10);
remove_action('sp_abo_daily_cron', 'sp_abo_maybe_send_cancel_warning', 15);
remove_action('sp_abo_daily_cron', 'sp_abo_maybe_auto_cancel_abandoned', 20);
remove_action('sp_abo_reminder_daily_cron', 'sp_abo_check_upcoming_insufficient_funds', 10);

add_action('sp_abo_daily_cron', 'sp_asb_cron_resume', 5);
add_action('sp_abo_daily_cron', 'sp_asb_cron_process', 10);
add_action('sp_abo_daily_cron', 'sp_asb_cron_cancel_warning', 15);
add_action('sp_abo_daily_cron', 'sp_asb_cron_auto_cancel', 20);
add_action('sp_abo_reminder_daily_cron', 'sp_asb_cron_reminders', 10);

/* ---------------------------------------------------------------------
 * Helfer
 * ------------------------------------------------------------------- */

function sp_asb_stack_key($sub) {
    return $sub->origin_order_id ? 'o' . $sub->origin_order_id : 's' . $sub->id;
}

function sp_asb_group($subs) {
    $groups = array();
    foreach ($subs as $sub) {
        $groups[(int) $sub->user_id . '|' . sp_asb_stack_key($sub)][] = $sub;
    }
    return $groups;
}

function sp_asb_product($sub) {
    return wc_get_product($sub->variation_id ?: $sub->product_id);
}

function sp_asb_product_ok($product) {
    return $product && $product->is_purchasable() && $product->is_in_stock();
}

/** Abo-Preis einer Position (mit 15 % Rabatt) oder null, falls Produkt fehlt. */
function sp_asb_price($sub) {
    $product = sp_asb_product($sub);
    return $product ? sp_abo_calculate_price($sub, $product) : null;
}

function sp_asb_sum($subs) {
    $sum = 0.0;
    foreach ($subs as $sub) {
        $sum += (float) sp_asb_price($sub);
    }
    return round($sum, 2);
}

function sp_asb_today() {
    return current_time('Y-m-d');
}

/** Gleiche Datumslogik wie sp_abo_process_single_subscription(). */
function sp_asb_next_date($sub) {
    $months = sp_abo_interval_to_months($sub->interval_days);
    if ($months !== null && $sub->preferred_day_of_month) {
        return sp_abo_add_calendar_months($sub->next_payment_date, $months, (int) $sub->preferred_day_of_month);
    }
    return date('Y-m-d', strtotime($sub->next_payment_date . ' +' . (int) $sub->interval_days . ' days'));
}

function sp_asb_topup_url() {
    $id = function_exists('sp_wallet_get_topup_product_id') ? sp_wallet_get_topup_product_id() : 0;
    return $id ? get_permalink($id) : wc_get_account_endpoint_url('guthaben');
}

/** Gemeinsame Stack-Mail: Produktliste, optionale Zahlenbox, optionaler Button. */
function sp_asb_send_stack_mail($subs, $subject, $heading, $intro, $rows = array(), $cta = null, $outro = '') {
    if (empty($subs) || !function_exists('sp_abo_send_branded_email')) {
        return;
    }
    $to = sp_abo_email_customer_email($subs[0]->user_id);
    if (!$to) {
        return;
    }
    ob_start();
    ?>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#0D0F12;"><?php echo wp_kses_post($intro); ?></p>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;"><?php echo count($subs) > 1 ? 'DEIN ABO-STACK' : 'DEIN ABO'; ?></p>
      <?php foreach ($subs as $i => $sub) : $price = sp_asb_price($sub); ?>
        <div style="<?php echo $i < count($subs) - 1 ? 'border-bottom:1px solid #F2F3F4;padding-bottom:8px;margin-bottom:8px;' : ''; ?>">
          <p style="margin:0;font-size:14.5px;font-weight:700;color:#0D0F12;"><?php echo esc_html(sp_abo_email_product_name($sub)); ?> &times; <?php echo (int) $sub->quantity; ?><?php if ($price !== null) : ?> <span style="font-weight:600;color:#4B5157;">&middot; <?php echo wp_kses_post(wc_price($price)); ?></span><?php endif; ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($rows) : ?>
    <div style="<?php echo esc_attr(sp_email_box_style()); ?>">
      <?php $n = 0; foreach ($rows as $label => $value) : $n++; ?>
        <div style="<?php echo $n < count($rows) ? 'border-bottom:1px solid #F2F3F4;padding-bottom:10px;margin-bottom:10px;' : ''; ?>">
          <p style="margin:0 0 3px;font-size:11px;font-weight:700;letter-spacing:.04em;color:#4B5157;"><?php echo esc_html($label); ?></p>
          <p style="margin:0;font-size:17px;font-weight:700;color:#0D0F12;"><?php echo wp_kses_post($value); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($cta === 'topup') : ?>
      <a href="<?php echo esc_url(sp_asb_topup_url()); ?>" style="display:inline-block;background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);color:#FFFFFF;text-decoration:none;font-weight:700;font-size:14px;padding:12px 22px;border-radius:9px;">Jetzt Guthaben aufladen &rarr;</a>
    <?php elseif ($cta === 'account') : ?>
      <?php echo sp_abo_email_account_link(); ?>
    <?php endif; ?>
    <?php if ($outro) : ?>
      <p style="margin:16px 0 0;font-size:13px;line-height:1.6;color:#4B5157;"><?php echo wp_kses_post($outro); ?></p>
    <?php endif; ?>
    <?php echo sp_abo_email_support_line(); ?>
    <?php
    sp_abo_send_branded_email($to, $subject, $heading, ob_get_clean());
}

/* ---------------------------------------------------------------------
 * Abrechnung eines Stacks
 * ------------------------------------------------------------------- */

function sp_asb_charge_stack($subs, $today) {
    global $wpdb;
    $table = sp_abo_table_name();

    $items = array();
    foreach ($subs as $sub) {
        $product = sp_asb_product($sub);
        if (!sp_asb_product_ok($product)) {
            sp_abo_set_status($sub->id, 'paused', 'product_unavailable');
            do_action('sp_abo_paused_product_unavailable', $sub);
            continue;
        }
        $items[] = array('sub' => $sub, 'product' => $product, 'price' => sp_abo_calculate_price($sub, $product));
    }
    if (!$items) {
        return;
    }

    $user_id = (int) $items[0]['sub']->user_id;
    $total = round(array_sum(array_column($items, 'price')), 2);
    $balance = sp_wallet_get_balance($user_id);

    if ($balance < $total) {
        $paused = array();
        foreach ($items as $it) {
            sp_abo_set_status($it['sub']->id, 'paused', 'insufficient_funds');
            $paused[] = $it['sub'];
        }
        sp_asb_send_stack_mail(
            $paused,
            count($paused) > 1 ? 'Dein Abo-Stack wurde pausiert' : 'Dein Abo wurde pausiert',
            'Lieferung pausiert',
            'Dein Guthaben hat für die heutige Lieferung nicht gereicht, deshalb haben wir sie pausiert.',
            array(
                'BENÖTIGT' => wc_price($total),
                'DEIN GUTHABEN' => wc_price($balance),
                'ES FEHLEN' => wc_price(max(0, $total - $balance)),
            ),
            'topup',
            'Sobald die Aufladung bei uns eingegangen ist, geht deine Lieferung automatisch raus &ndash; du musst nichts weiter tun.'
        );
        return;
    }

    // Atomarer Claim pro Position (wie im Original) - schuetzt vor Doppel-Abbuchung.
    $claimed = array();
    foreach ($items as $it) {
        $sub = $it['sub'];
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table} SET next_payment_date = %s, charge_count = charge_count + 1, updated_at = %s WHERE id = %d AND status = 'active' AND next_payment_date <= %s",
            sp_asb_next_date($sub), current_time('mysql'), $sub->id, $today
        ));
        if ($wpdb->rows_affected === 1) {
            $claimed[] = $it;
        }
    }
    if (!$claimed) {
        return;
    }
    $total = round(array_sum(array_column($claimed, 'price')), 2);

    $first = $claimed[0];
    $order = sp_abo_create_charge_order($first['sub'], $first['product'], $first['price']);
    if (count($claimed) > 1) {
        $ids = array();
        foreach ($claimed as $i => $it) {
            $ids[] = (int) $it['sub']->id;
            if ($i === 0) {
                continue;
            }
            $item = new WC_Order_Item_Product();
            $item->set_product($it['product']);
            $item->set_quantity((int) $it['sub']->quantity);
            $item->set_subtotal($it['price']);
            $item->set_total($it['price']);
            $order->add_item($item);
        }
        $order->calculate_totals();
        $order->update_meta_data('_sp_abo_subscription_ids', implode(',', $ids));
        $order->add_order_note('Abo-Stack-Lieferung: Positionen #' . implode(', #', $ids) . ' gemeinsam in einer Bestellung.');
        $order->save();
    }

    $already = sp_wallet_order_already_debited($order->get_id());
    $entry_id = $already ? true : sp_wallet_add_entry($user_id, -$total, 'Abo-Abbuchung (Bestellung #' . $order->get_order_number() . ')', $order->get_id());
    if (!$entry_id) {
        $order->update_status('failed', 'Automatische Guthaben-Abbuchung fehlgeschlagen - Bestellung wurde nicht als bezahlt markiert.');
        error_log('sp-abo-stack-billing: Abbuchung fehlgeschlagen, Bestellung #' . $order->get_id());
        return;
    }
    $order->update_status('processing');

    foreach ($claimed as $it) {
        $wpdb->insert(sp_abo_charges_table_name(), array(
            'subscription_id' => $it['sub']->id,
            'order_id' => $order->get_id(),
            'amount' => $it['price'],
            'charged_at' => current_time('mysql'),
        ), array('%d', '%d', '%f', '%s'));
        do_action('sp_abo_charged', $it['sub'], $order, $it['price']);
    }
}

/** Alle heute faelligen, aktiven Positionen eines Kunden stack-weise abrechnen. */
function sp_asb_process_user($user_id, $today) {
    global $wpdb;
    $due = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . sp_abo_table_name() . " WHERE user_id = %d AND status = 'active' AND next_payment_date <= %s ORDER BY next_payment_date ASC, id ASC",
        $user_id, $today
    ));
    foreach (sp_asb_group($due) as $subs) {
        try {
            sp_asb_charge_stack($subs, $today);
        } catch (\Throwable $e) {
            error_log('sp-abo-stack-billing: Fehler bei Stack von Kunde #' . $user_id . ': ' . $e->getMessage());
        }
    }
}

/**
 * Wegen Guthabenmangel pausierte Stacks eines Kunden fortsetzen - nur ganze
 * Stacks, und nur wenn das Guthaben (nach Abzug bereits faelliger aktiver
 * Positionen) fuer den kompletten Stack reicht.
 */
function sp_asb_try_resume_user($user_id) {
    global $wpdb;
    $table = sp_abo_table_name();
    $today = sp_asb_today();

    $remaining = sp_wallet_get_balance($user_id);
    $active_due = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE user_id = %d AND status = 'active' AND next_payment_date <= %s",
        $user_id, $today
    ));
    $remaining -= sp_asb_sum($active_due);

    $paused = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE user_id = %d AND status = 'paused' AND paused_reason = 'insufficient_funds' ORDER BY next_payment_date ASC, id ASC",
        $user_id
    ));
    foreach (sp_asb_group($paused) as $subs) {
        $available = array_values(array_filter($subs, function ($sub) {
            return sp_asb_product_ok(sp_asb_product($sub));
        }));
        if (!$available) {
            continue;
        }
        $total = sp_asb_sum($available);
        if ($remaining < $total) {
            continue;
        }
        $remaining -= $total;
        foreach ($available as $sub) {
            sp_abo_set_status($sub->id, 'active', null);
        }
        sp_asb_send_stack_mail(
            $available,
            count($available) > 1 ? 'Dein Abo-Stack läuft wieder' : 'Dein Abo läuft wieder',
            'Dein Abo läuft wieder',
            'Dein Guthaben reicht wieder &ndash; dein Abo läuft automatisch weiter.',
            array(),
            'account'
        );
    }
}

/* ---------------------------------------------------------------------
 * Cronjobs
 * ------------------------------------------------------------------- */

function sp_asb_cron_resume() {
    global $wpdb;
    $users = $wpdb->get_col("SELECT DISTINCT user_id FROM " . sp_abo_table_name() . " WHERE status = 'paused' AND paused_reason = 'insufficient_funds'");
    foreach ($users as $user_id) {
        try {
            sp_asb_try_resume_user((int) $user_id);
        } catch (\Throwable $e) {
            error_log('sp-abo-stack-billing: Fortsetzen fehlgeschlagen fuer Kunde #' . $user_id . ': ' . $e->getMessage());
        }
    }
}

function sp_asb_cron_process() {
    if (get_transient('sp_abo_cron_lock')) {
        return;
    }
    set_transient('sp_abo_cron_lock', 1, 10 * MINUTE_IN_SECONDS);
    try {
        global $wpdb;
        $today = sp_asb_today();
        $users = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM " . sp_abo_table_name() . " WHERE status = 'active' AND next_payment_date <= %s",
            $today
        ));
        foreach ($users as $user_id) {
            sp_asb_process_user((int) $user_id, $today);
        }
    } finally {
        delete_transient('sp_abo_cron_lock');
    }
}

/** Erinnerung 7 und 2 Tage vorher: eine Mail pro Kunde, Summe aller bis dahin faelligen Positionen. */
function sp_asb_cron_reminders() {
    global $wpdb;
    $table = sp_abo_table_name();
    $now = current_time('timestamp');
    foreach (array_map('intval', explode(',', SP_ASB_REMINDER_DAYS)) as $days) {
        $target = date('Y-m-d', $now + $days * DAY_IN_SECONDS);
        $users = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM {$table} WHERE status = 'active' AND next_payment_date = %s",
            $target
        ));
        foreach ($users as $user_id) {
            $upcoming = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d AND status = 'active' AND next_payment_date <= %s ORDER BY next_payment_date ASC, id ASC",
                $user_id, $target
            ));
            $needed = sp_asb_sum($upcoming);
            $balance = sp_wallet_get_balance($user_id);
            if ($balance >= $needed) {
                continue;
            }
            $on_target = array_values(array_filter($upcoming, function ($s) use ($target) {
                return $s->next_payment_date === $target;
            }));
            sp_asb_send_stack_mail(
                $on_target,
                'Guthaben für deine nächste Abo-Lieferung aufladen',
                'Bitte Guthaben aufladen',
                'Deine nächste Abo-Lieferung ist am <strong>' . esc_html(date_i18n('d.m.Y', strtotime($target))) . '</strong> fällig &ndash; dein Guthaben reicht dafür noch nicht.',
                array(
                    'BENÖTIGT BIS ' . date_i18n('d.m.Y', strtotime($target)) => wc_price($needed),
                    'DEIN GUTHABEN' => wc_price($balance),
                    'ES FEHLEN' => wc_price(max(0, $needed - $balance)),
                ),
                'topup',
                'Tipp: Eine Überweisung braucht meist 1&ndash;2 Werktage &ndash; lade am besten gleich auf. Passt es gerade nicht? Du kannst deinen Stack in deinem Konto verschieben oder pausieren.'
            );
        }
    }
}

/** Vorab-Warnung vor Auto-Kuendigung - eine Mail pro Stack. */
function sp_asb_cron_cancel_warning() {
    global $wpdb;
    $table = sp_abo_table_name();
    $warn_after = max(1, SP_ABO_AUTO_CANCEL_AFTER_DAYS - SP_ABO_CANCEL_WARNING_DAYS_BEFORE);
    $cutoff = date('Y-m-d H:i:s', current_time('timestamp') - $warn_after * DAY_IN_SECONDS);
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE status = 'paused' AND paused_reason = 'insufficient_funds' AND updated_at <= %s AND cancel_warning_sent_at IS NULL",
        $cutoff
    ));
    foreach (sp_asb_group($rows) as $subs) {
        foreach ($subs as $sub) {
            $wpdb->update($table, array('cancel_warning_sent_at' => current_time('mysql')), array('id' => $sub->id), array('%s'), array('%d'));
        }
        sp_asb_send_stack_mail(
            $subs,
            'Dein Abo wird bald automatisch beendet',
            'Nur noch wenige Tage',
            'Dein Abo ist seit Längerem wegen fehlendem Guthaben pausiert. Lädst du nicht innerhalb der nächsten ' . (int) SP_ABO_CANCEL_WARNING_DAYS_BEFORE . ' Tage auf, wird es automatisch beendet.',
            array('BENÖTIGT' => wc_price(sp_asb_sum($subs)), 'DEIN GUTHABEN' => wc_price(sp_wallet_get_balance($subs[0]->user_id))),
            'topup'
        );
    }
}

/** Auto-Kuendigung nach zu langer Pausierung mangels Guthaben - eine Mail pro Stack. */
function sp_asb_cron_auto_cancel() {
    global $wpdb;
    $table = sp_abo_table_name();
    $cutoff = date('Y-m-d H:i:s', current_time('timestamp') - SP_ABO_AUTO_CANCEL_AFTER_DAYS * DAY_IN_SECONDS);
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE status = 'paused' AND paused_reason = 'insufficient_funds' AND updated_at <= %s",
        $cutoff
    ));
    foreach (sp_asb_group($rows) as $subs) {
        foreach ($subs as $sub) {
            sp_abo_set_status($sub->id, 'cancelled', 'auto_abandoned');
        }
        sp_asb_send_stack_mail(
            $subs,
            'Dein Abo wurde automatisch beendet',
            'Dein Abo wurde beendet',
            'Dein Abo war seit über ' . (int) SP_ABO_AUTO_CANCEL_AFTER_DAYS . ' Tagen pausiert, weil dein Guthaben nicht gereicht hat. Da in dieser Zeit nicht aufgeladen wurde, haben wir es automatisch beendet.',
            array(),
            null,
            'Dein Restguthaben bleibt erhalten und verfällt nicht. ' . sp_abo_email_stack_link('Neuen Stack zusammenstellen →')
        );
    }
}

/* ---------------------------------------------------------------------
 * Sofort weiter nach Aufladung (statt erst am naechsten Morgen um 6 Uhr)
 * ------------------------------------------------------------------- */

function sp_asb_queue_user($user_id) {
    $GLOBALS['sp_asb_queued_users'][(int) $user_id] = true;
    if (empty($GLOBALS['sp_asb_shutdown_registered'])) {
        $GLOBALS['sp_asb_shutdown_registered'] = true;
        add_action('shutdown', 'sp_asb_run_queue', 5);
    }
}

add_action('sp_wallet_credited', function ($user_id) {
    sp_asb_queue_user($user_id);
}, 20, 1);

function sp_asb_run_queue() {
    if (empty($GLOBALS['sp_asb_queued_users']) || get_transient('sp_abo_cron_lock')) {
        return;
    }
    $today = sp_asb_today();
    foreach (array_keys($GLOBALS['sp_asb_queued_users']) as $user_id) {
        try {
            sp_asb_try_resume_user($user_id);
            sp_asb_process_user($user_id, $today);
        } catch (\Throwable $e) {
            error_log('sp-abo-stack-billing: Sofort-Verarbeitung fehlgeschlagen fuer Kunde #' . $user_id . ': ' . $e->getMessage());
        }
    }
    $GLOBALS['sp_asb_queued_users'] = array();
}

/* ---------------------------------------------------------------------
 * "Stack fortsetzen" in "Mein Abo" (laeuft vor dem Original-Handler, Prio 9)
 * ------------------------------------------------------------------- */

add_action('template_redirect', function () {
    if (($_POST['sp_abo_bulk_action'] ?? '') !== 'resume_all' || empty($_POST['sp_abo_bulk_ids']) || !is_user_logged_in()) {
        return;
    }
    $group = sanitize_text_field(wp_unslash($_POST['sp_abo_bulk_group'] ?? ''));
    if (!$group || !wp_verify_nonce($_POST['sp_abo_bulk_nonce'] ?? '', 'sp_abo_bulk_action_' . $group)) {
        return; // Original-Handler behandelt ungueltige Anfragen wie bisher.
    }
    $user_id = get_current_user_id();
    $today = sp_asb_today();

    $subs = array();
    foreach (array_map('intval', (array) $_POST['sp_abo_bulk_ids']) as $id) {
        $sub = sp_abo_get_subscription($id);
        if ($sub && (int) $sub->user_id === $user_id && $sub->status === 'paused') {
            $subs[] = $sub;
        }
    }
    if (!$subs) {
        return;
    }

    $due_now = false;
    foreach ($subs as $sub) {
        if ($sub->next_payment_date <= $today) {
            $due_now = true;
        }
    }
    global $wpdb;
    $active_due = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . sp_abo_table_name() . " WHERE user_id = %d AND status = 'active' AND next_payment_date <= %s",
        $user_id, $today
    ));
    $remaining = sp_wallet_get_balance($user_id) - sp_asb_sum($active_due);
    $total = sp_asb_sum($subs);

    if ($due_now && $remaining < $total) {
        // Nicht still fortsetzen und morgen frueh wieder pausieren: als "wartet auf
        // Guthaben" markieren - laeuft automatisch an, sobald aufgeladen ist.
        foreach ($subs as $sub) {
            sp_abo_set_status($sub->id, 'paused', 'insufficient_funds');
        }
        wc_add_notice(
            sprintf(
                'Dein Guthaben reicht noch nicht für die nächste Lieferung (%s, es fehlen %s). <a href="%s">Jetzt aufladen</a> – sobald die Aufladung eingegangen ist, läuft dein Stack automatisch weiter.',
                wc_price($total),
                wc_price(max(0, $total - max(0, $remaining))),
                esc_url(sp_asb_topup_url())
            ),
            'notice'
        );
        wp_safe_redirect(wc_get_account_endpoint_url('abo') . '#' . $group);
        exit;
    }

    // Genug Guthaben: Original-Handler setzt fort, faellige Lieferung geht sofort raus.
    sp_asb_queue_user($user_id);
}, 8);
