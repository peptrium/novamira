<?php
/**
 * Plugin Name: SP Analyse – Wochenbericht
 * Description: Schickt jeden Montag früh einen kompakten Wochenbericht (letzte Woche Mo–So vs. Vorwoche) per E-Mail; Einstellungen + Testversand im Analyse-Tab (2026-10-09).
 *
 * Nutzt die Auswertungen aus sp-analyse.php / sp-analyse-insights.php (nur zur
 * Laufzeit aufgerufen - Ladereihenfolge egal). Versand per WP-Cron als
 * Einzel-Event, das sich nach jedem Lauf fuer den naechsten Montag 07:00
 * (Europe/Berlin) neu einplant; Option sp_an_weekly_last verhindert
 * Doppelversand derselben Woche. Testbestellungen/-kunden sind wie im
 * Analyse-Tab ausgenommen.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_an_weekly_settings() {
    $s = get_option('sp_an_weekly');
    if (!is_array($s)) {
        $s = array('enabled' => 1, 'to' => get_option('admin_email'));
    }
    return $s;
}

function sp_an_weekly_next_run() {
    $tz = new DateTimeZone('Europe/Berlin');
    $next = new DateTime('next monday 07:00', $tz);
    return $next->getTimestamp();
}

function sp_an_weekly_schedule() {
    $s = sp_an_weekly_settings();
    $ts = wp_next_scheduled('sp_an_weekly_report');
    if (empty($s['enabled'])) {
        if ($ts) {
            wp_unschedule_event($ts, 'sp_an_weekly_report');
        }
        return;
    }
    if (!$ts) {
        wp_schedule_single_event(sp_an_weekly_next_run(), 'sp_an_weekly_report');
    }
}
add_action('init', 'sp_an_weekly_schedule');

add_action('sp_an_weekly_report', function () {
    $week = (new DateTime('last monday', new DateTimeZone('Europe/Berlin')))->format('o-W');
    if (get_option('sp_an_weekly_last') !== $week) {
        if (sp_an_weekly_send()) {
            update_option('sp_an_weekly_last', $week, false);
        }
    }
    wp_schedule_single_event(sp_an_weekly_next_run(), 'sp_an_weekly_report');
});

/** Zeitraum: letzte volle Woche Mo-So (Shop-Datum) und die Woche davor. */
function sp_an_weekly_range() {
    $tz = wp_timezone();
    $mon = new DateTime('monday last week', $tz);
    $sun = (clone $mon)->modify('+6 days');
    $pmon = (clone $mon)->modify('-7 days');
    $psun = (clone $mon)->modify('-1 day');
    return array($mon->format('Y-m-d'), $sun->format('Y-m-d'), $pmon->format('Y-m-d'), $psun->format('Y-m-d'));
}

function sp_an_weekly_money($v) {
    return number_format((float) $v, 2, ',', '.') . ' €';
}

function sp_an_weekly_delta($cur, $prev, $money = false) {
    if ($cur === null || $prev === null) {
        return '';
    }
    if ((float) $prev == 0.0) {
        return $cur > 0 ? '<span style="color:#057A55">neu</span>' : '';
    }
    $d = ($cur - $prev) / $prev * 100;
    $c = $d >= 0 ? '#057A55' : '#B91C1C';
    return '<span style="color:' . $c . ';font-weight:600">' . ($d >= 0 ? '▲ +' : '▼ −') . number_format(abs($d), 0, ',', '.') . ' %</span>';
}

/** Baut die Mail. Gibt array(subject, html) zurueck. */
function sp_an_weekly_build() {
    list($start, $end, $pstart, $pend) = sp_an_weekly_range();
    $since = (string) get_option('sp_an_since', current_time('Y-m-d'));
    $cur = sp_an_kpis($start, $end, $since);
    $prev = sp_an_kpis($pstart, $pend, $since);
    $orders = sp_an_get_orders($start, $end);
    $visits = $end >= $since ? sp_an_get_visits(max($start, $since), $end) : array();

    // Herkunft
    $src = array();
    foreach ($orders as $o) {
        $k = $o['src'] === 'partner' ? 'Partner' : sp_an_source_label($o['src']);
        if (!isset($src[$k])) {
            $src[$k] = array('n' => 0, 'rev' => 0.0);
        }
        $src[$k]['n']++;
        if ($o['paid']) {
            $src[$k]['rev'] += $o['goods'];
        }
    }
    uasort($src, function ($a, $b) {
        return $b['rev'] <=> $a['rev'];
    });

    // Partner
    $partners = array();
    foreach ($orders as $o) {
        if ($o['src'] !== 'partner' || strpos($o['campaign'], 'aff-') !== 0) {
            continue;
        }
        $id = (int) substr($o['campaign'], 4);
        if (!isset($partners[$id])) {
            $partners[$id] = array('n' => 0, 'rev' => 0.0, 'com' => 0.0);
        }
        $partners[$id]['n']++;
        if ($o['paid']) {
            $partners[$id]['rev'] += $o['goods'];
        }
        $partners[$id]['com'] += $o['commission'] ?? 0;
    }

    // Top-Produkte (bezahlt)
    $prod = array();
    if (function_exists('sp_an_paid_order_details')) {
        foreach (sp_an_paid_order_details($start, $end) as $d) {
            foreach ($d['items'] as $pid => $val) {
                $prod[$pid] = ($prod[$pid] ?? 0) + $val;
            }
        }
    }
    arsort($prod);
    $prod = array_slice($prod, 0, 5, true);

    // Trichter
    $f = array('pv' => 0, 'product' => 0, 'cart' => 0, 'checkout' => 0, 'order' => 0);
    foreach ($visits as $v) {
        foreach ($f as $k => $n) {
            if ($v[$k]) {
                $f[$k]++;
            }
        }
    }

    // Kassen-Fehler
    global $wpdb;
    $errs = array();
    if (function_exists('sp_an_err_table')) {
        $errs = $wpdb->get_results($wpdb->prepare('SELECT msg, area, COUNT(DISTINCT CONCAT(vh, day)) v FROM ' . sp_an_err_table() . ' WHERE day BETWEEN %s AND %s GROUP BY area, msg ORDER BY v DESC LIMIT 4', $start, $end));
    }

    // To-dos jetzt
    $now = current_time('timestamp');
    $vk_open = 0;
    $vk_old = 0;
    $vk_sum = 0.0;
    foreach (wc_get_orders(array('limit' => -1, 'status' => 'on-hold', 'payment_method' => 'bacs')) as $o) {
        if (function_exists('sp_an_is_test_order') && sp_an_is_test_order($o)) {
            continue;
        }
        $vk_open++;
        $vk_sum += (float) $o->get_total();
        if ($o->get_date_created() && $now - $o->get_date_created()->getTimestamp() > 3 * DAY_IN_SECONDS) {
            $vk_old++;
        }
    }
    $ship = function_exists('sp_versand_get_orders') ? count(sp_versand_get_orders()) : null;
    $abo = function_exists('sp_an_abo_stats') ? sp_an_abo_stats($start, $end) : null;

    // Gratisversand-Grenze
    $near_below = 0;
    $near_above = 0;
    if (function_exists('sp_an_paid_order_details')) {
        foreach (sp_an_paid_order_details($start, $end) as $d) {
            if ($d['basis'] >= 70 && $d['basis'] < 100) {
                $near_below++;
            } elseif ($d['basis'] >= 100 && $d['basis'] < 130) {
                $near_above++;
            }
        }
    }

    $fmt_d = function ($d) {
        return date_i18n('d.m.', strtotime($d));
    };
    $period = $fmt_d($start) . ' – ' . date_i18n('d.m.Y', strtotime($end));
    $subject = 'Peptrium Wochenbericht ' . $period . ': ' . $cur['orders'] . ' Bestellungen, ' . sp_an_weekly_money($cur['revenue']);

    $td = 'padding:7px 0;border-bottom:1px solid #EEF0F2;font-size:14px';
    $h2 = 'font-size:15px;margin:26px 0 8px;color:#0D0F12';
    $tile = function ($label, $value, $delta) {
        return '<td style="width:33%;padding:10px 12px;background:#F4F5F6;border-radius:10px;vertical-align:top"><div style="font-size:11px;color:#5B6169;text-transform:uppercase;letter-spacing:.04em;font-weight:700">' . esc_html($label) . '</div><div style="font-size:20px;font-weight:800;margin-top:3px">' . $value . '</div><div style="font-size:12px;margin-top:2px">' . $delta . '</div></td>';
    };

    ob_start();
    ?>
    <div style="font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#0D0F12;max-width:620px;margin:0 auto;padding:8px">
      <h1 style="font-size:20px;margin:0 0 2px">Peptrium Wochenbericht</h1>
      <div style="color:#5B6169;font-size:13px;margin-bottom:16px"><?php echo esc_html($period); ?> · Vergleich zur Vorwoche</div>

      <table role="presentation" style="width:100%;border-collapse:separate;border-spacing:6px"><tr>
        <?php echo $tile('Bestellungen', esc_html($cur['orders']), sp_an_weekly_delta($cur['orders'], $prev['orders'])); ?>
        <?php echo $tile('Umsatz bezahlt', esc_html(sp_an_weekly_money($cur['revenue'])), sp_an_weekly_delta($cur['revenue'], $prev['revenue'])); ?>
        <?php echo $tile('Ø Bestellwert', esc_html(sp_an_weekly_money($cur['aov'])), sp_an_weekly_delta($cur['aov'], $prev['aov'])); ?>
      </tr><tr>
        <?php echo $tile('Besucher', $cur['visitors'] === null ? '–' : esc_html($cur['visitors']), sp_an_weekly_delta($cur['visitors'], $prev['visitors'])); ?>
        <?php echo $tile('Kaufquote', $cur['cr'] === null ? '–' : esc_html(number_format($cur['cr'], 1, ',', '.') . ' %'), $prev['cr'] !== null && $cur['cr'] !== null ? sp_an_weekly_delta($cur['cr'], $prev['cr']) : ''); ?>
        <?php echo $tile('Offene Vorkasse', esc_html($vk_open), $vk_open ? esc_html(sp_an_weekly_money($vk_sum)) : ''); ?>
      </tr></table>

      <h2 style="<?php echo $h2; ?>">📋 Jetzt zu tun</h2>
      <table role="presentation" style="width:100%;border-collapse:collapse">
        <?php if ($ship !== null): ?><tr><td style="<?php echo $td; ?>">📦 Bezahlt, noch nicht versendet</td><td style="<?php echo $td; ?>;text-align:right;font-weight:700"><?php echo esc_html($ship); ?></td></tr><?php endif; ?>
        <tr><td style="<?php echo $td; ?>">💶 Vorkasse offen seit über 3 Tagen</td><td style="<?php echo $td; ?>;text-align:right;font-weight:700"><?php echo esc_html($vk_old); ?></td></tr>
        <?php if ($abo): ?><tr><td style="<?php echo $td; ?>">🔁 Abos pausiert (Guthaben fehlt)</td><td style="<?php echo $td; ?>;text-align:right;font-weight:700"><?php echo esc_html($abo['paused_funds']); ?></td></tr><?php endif; ?>
      </table>

      <h2 style="<?php echo $h2; ?>">Woher kamen die Bestellungen?</h2>
      <table role="presentation" style="width:100%;border-collapse:collapse">
        <?php if (!$src): ?><tr><td style="<?php echo $td; ?>;color:#8A9099">Keine Bestellungen</td></tr><?php endif; ?>
        <?php foreach ($src as $k => $x): ?>
          <tr><td style="<?php echo $td; ?>"><?php echo esc_html($k); ?></td><td style="<?php echo $td; ?>;text-align:right"><?php echo esc_html($x['n']); ?> Best.</td><td style="<?php echo $td; ?>;text-align:right;font-weight:700"><?php echo esc_html(sp_an_weekly_money($x['rev'])); ?></td></tr>
        <?php endforeach; ?>
      </table>
      <?php if ($partners): ?>
        <div style="font-size:13px;color:#5B6169;margin-top:8px">
          <?php foreach ($partners as $id => $x) {
              echo esc_html(sp_an_affiliate_name($id) . ': ' . $x['n'] . ' Bestellungen, ' . sp_an_weekly_money($x['rev']) . ' Umsatz, ' . sp_an_weekly_money($x['com']) . ' Provision') . '<br>';
          } ?>
        </div>
      <?php endif; ?>

      <h2 style="<?php echo $h2; ?>">Top-Produkte</h2>
      <table role="presentation" style="width:100%;border-collapse:collapse">
        <?php if (!$prod): ?><tr><td style="<?php echo $td; ?>;color:#8A9099">Keine Verkäufe</td></tr><?php endif; ?>
        <?php foreach ($prod as $pid => $val): $p = wc_get_product($pid); ?>
          <tr><td style="<?php echo $td; ?>"><?php echo esc_html($p ? $p->get_name() : '#' . $pid); ?></td><td style="<?php echo $td; ?>;text-align:right;font-weight:700"><?php echo esc_html(sp_an_weekly_money($val)); ?></td></tr>
        <?php endforeach; ?>
      </table>

      <?php if ($f['pv']): ?>
        <h2 style="<?php echo $h2; ?>">Kauf-Trichter</h2>
        <div style="font-size:14px;line-height:1.7">
          <?php
          $steps = array('pv' => 'Besucher', 'product' => 'Produkt angesehen', 'cart' => 'Warenkorb', 'checkout' => 'Kasse', 'order' => 'Bestellt');
          $parts = array();
          foreach ($steps as $k => $l) {
              $parts[] = esc_html($l) . ' <b>' . esc_html($f[$k]) . '</b>';
          }
          echo implode(' → ', $parts);
          ?>
        </div>
      <?php endif; ?>

      <h2 style="<?php echo $h2; ?>">Gratisversand-Grenze (100 €)</h2>
      <div style="font-size:14px">Knapp darunter (70–99 €): <b><?php echo esc_html($near_below); ?></b> · knapp darüber (100–129 €): <b><?php echo esc_html($near_above); ?></b></div>
      <?php if (function_exists('sp_an_fsh_stats')): $fx = sp_an_fsh_stats($start, $end); if ($fx['viewers']): ?>
        <div style="font-size:14px;margin-top:6px">Hinweis in der Kasse: <b><?php echo esc_html($fx['viewers']); ?></b> sahen Vorschläge, <b><?php echo esc_html($fx['clickers']); ?></b> tippten auf „+“ (<?php echo esc_html(sp_an_weekly_money($fx['value'])); ?> hinzugefügt), davon <b><?php echo esc_html($fx['clickers_ordered']); ?></b> bestellt.</div>
      <?php endif; endif; ?>

      <?php if ($errs): ?>
        <h2 style="<?php echo $h2; ?>">⚠️ Fehler, die Kunden gesehen haben</h2>
        <table role="presentation" style="width:100%;border-collapse:collapse">
          <?php foreach ($errs as $e): ?>
            <tr><td style="<?php echo $td; ?>"><?php echo esc_html(mb_strimwidth($e->msg, 0, 110, '…')); ?></td><td style="<?php echo $td; ?>;text-align:right;white-space:nowrap"><?php echo esc_html($e->v); ?> Besucher</td></tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <p style="margin:28px 0 6px"><a href="<?php echo esc_url(admin_url('admin.php?page=peptrium-dashboard&tab=analyse&sp_start=' . $start . '&sp_end=' . $end)); ?>" style="display:inline-block;background:#0D0F12;color:#fff;text-decoration:none;padding:11px 18px;border-radius:9px;font-weight:600;font-size:14px">Details im Analyse-Tab</a></p>
      <p style="font-size:11px;color:#8A9099;margin-top:18px">Automatischer Bericht · abbestellen oder Empfänger ändern: Peptrium Dashboard → Analyse → Wochenbericht.</p>
    </div>
    <?php
    return array($subject, ob_get_clean());
}

function sp_an_weekly_send($to = null) {
    if (!function_exists('sp_an_kpis')) {
        return false;
    }
    $s = sp_an_weekly_settings();
    $to = $to ?: $s['to'];
    if (!is_email($to)) {
        return false;
    }
    list($subject, $html) = sp_an_weekly_build();
    return wp_mail($to, $subject, $html, array('Content-Type: text/html; charset=UTF-8'));
}

/* Einstellungen + Testversand als Karte im Analyse-Tab */
add_action('sp_an_render_insights', function () {
    if (!current_user_can('manage_woocommerce')) {
        return;
    }
    $msg = '';
    if (!empty($_POST['sp_an_weekly_action']) && check_admin_referer('sp_an_weekly')) {
        $s = array(
            'enabled' => empty($_POST['enabled']) ? 0 : 1,
            'to' => sanitize_email(wp_unslash($_POST['to'] ?? '')),
        );
        if (!is_email($s['to'])) {
            $msg = 'Bitte eine gültige E-Mail-Adresse eingeben.';
        } else {
            update_option('sp_an_weekly', $s, false);
            $ts = wp_next_scheduled('sp_an_weekly_report');
            if ($ts) {
                wp_unschedule_event($ts, 'sp_an_weekly_report');
            }
            sp_an_weekly_schedule();
            $msg = 'Gespeichert.';
            if ($_POST['sp_an_weekly_action'] === 'test') {
                $msg = sp_an_weekly_send($s['to']) ? 'Testbericht an ' . $s['to'] . ' gesendet (letzte Woche).' : 'Senden fehlgeschlagen.';
            }
        }
    }
    $s = sp_an_weekly_settings();
    $next = wp_next_scheduled('sp_an_weekly_report');
    ?>
    <div class="sp-dash-card">
      <h2>📬 Wochenbericht per E-Mail</h2>
      <?php if ($msg): ?><div class="sp-an-note sp-an-ok"><?php echo esc_html($msg); ?></div><?php endif; ?>
      <form method="post" class="sp-an-form" style="margin-top:0">
        <?php wp_nonce_field('sp_an_weekly'); ?>
        <label style="flex-direction:row;align-items:center;gap:6px"><input type="checkbox" name="enabled" value="1" <?php checked(!empty($s['enabled'])); ?>> jeden Montag ca. 7 Uhr senden</label>
        <label>Empfänger <input type="email" name="to" value="<?php echo esc_attr($s['to']); ?>" style="min-width:260px"></label>
        <button class="button button-primary" name="sp_an_weekly_action" value="save">Speichern</button>
        <button class="button" name="sp_an_weekly_action" value="test">Speichern &amp; Testbericht senden</button>
      </form>
      <p class="sp-an-muted">Inhalt: Bestellungen, Umsatz, Ø Bestellwert, Besucher, Kaufquote (jeweils vs. Vorwoche), was zu tun ist (Versand, offene Vorkasse, pausierte Abos), Herkunft & Partner, Top-Produkte, Kauf-Trichter, Gratisversand-Grenze und Kassen-Fehler.
        <?php if (!empty($s['enabled']) && $next): ?>Nächster Versand: <?php echo esc_html(wp_date('D d.m.Y H:i', $next, new DateTimeZone('Europe/Berlin'))); ?> Uhr.<?php endif; ?></p>
    </div>
    <?php
}, 30);
