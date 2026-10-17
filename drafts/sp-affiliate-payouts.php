<?php
/**
 * Plugin Name: SP Partner-Auszahlungen
 * Description: Peptrium Dashboard → Partner-Auszahlungen. Überweisungen an Partner eintragen
 * (Betrag + Datum), Kontostand je Partner: verdient – überwiesen = offen (oder Guthaben).
 * Die ältesten auszahlbaren Provisionen werden automatisch als "bezahlt" markiert, soweit die
 * Überweisungen sie decken (SliceWP-Auszahlung + Zahlung werden mit angelegt). 2026-10-17.
 *
 * Ledger: Option sp_aff_transfers = [ ['id','aff','amount','date'(Y-m-d),'note','payment','payout','by','t'] ]
 */

if (!defined('ABSPATH')) exit;

function sp_afp_transfers($aff = 0) {
    $all = (array) get_option('sp_aff_transfers', []);
    if (!$aff) return $all;
    return array_values(array_filter($all, function ($t) use ($aff) { return (int) $t['aff'] === (int) $aff; }));
}

/** Summen je Partner aus den SliceWP-Provisionen. */
function sp_afp_commission_sums() {
    global $wpdb;
    $out = [];
    $rows = $wpdb->get_results("SELECT affiliate_id, status, SUM(amount) s, COUNT(*) n FROM {$wpdb->prefix}slicewp_commissions WHERE status IN ('paid','unpaid','pending') GROUP BY affiliate_id, status");
    foreach ($rows as $r) {
        $out[(int) $r->affiliate_id][$r->status] = ['s' => (float) $r->s, 'n' => (int) $r->n];
    }
    return $out;
}

function sp_afp_affiliate_name($aff) {
    if (!function_exists('slicewp_get_affiliate')) return 'Partner ' . $aff;
    $a = slicewp_get_affiliate($aff);
    $u = $a ? get_userdata($a->get('user_id')) : null;
    return $u ? $u->display_name : 'Partner ' . $aff;
}

function sp_afp_money($v) { return number_format((float) $v, 2, ',', '.') . ' €'; }

/**
 * Überweisung eintragen und die ältesten auszahlbaren Provisionen als bezahlt markieren,
 * solange alle Überweisungen an diesen Partner sie decken.
 */
function sp_afp_record_transfer($aff, $amount, $date, $note = '', $mail = false) {
    global $wpdb;
    $aff = (int) $aff;
    $amount = round((float) $amount, 2);
    if ($aff <= 0 || $amount <= 0) return new WP_Error('sp_afp', 'Partner und Betrag angeben.');
    $transfers = (array) get_option('sp_aff_transfers', []);
    $entry = ['id' => (int) (max(array_merge([0], wp_list_pluck($transfers, 'id'))) + 1), 'aff' => $aff, 'amount' => $amount, 'date' => $date, 'note' => $note, 'payment' => 0, 'payout' => 0, 'by' => get_current_user_id(), 't' => time()];

    $total_transferred = $amount;
    foreach ($transfers as $t) if ((int) $t['aff'] === $aff) $total_transferred += (float) $t['amount'];
    $paid = (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id=%d AND status='paid'", $aff));
    $open = $wpdb->get_results($wpdb->prepare("SELECT id, amount FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id=%d AND status='unpaid' ORDER BY date_created ASC, id ASC", $aff));
    $ids = [];
    $sum = 0;
    foreach ($open as $c) {
        if ($paid + $sum + (float) $c->amount > $total_transferred + 0.005) break;
        $sum += (float) $c->amount;
        $ids[] = (int) $c->id;
    }

    if (!$mail) add_filter('pre_wp_mail', '__return_true', 999);
    if ($ids) {
        $dt = get_gmt_from_date($date . ' 12:00:00');
        $by = get_current_user_id() ?: 1;
        $entry['payout'] = (int) slicewp_insert_payout(['originator_user_id' => $by, 'amount' => round($sum, 2), 'date_created' => $dt, 'date_modified' => $dt]);
        $entry['payment'] = (int) slicewp_insert_payment(['affiliate_id' => $aff, 'commission_ids' => implode(',', $ids), 'amount' => round($sum, 2), 'currency' => 'EUR', 'originator_user_id' => $by, 'payout_method' => 'manual', 'status' => 'paid', 'payout_id' => $entry['payout'], 'date_created' => $dt, 'date_modified' => $dt]);
        if ($entry['payment']) {
            slicewp_add_payment_meta($entry['payment'], 'sp_note', 'Überweisung ' . sp_afp_money($amount) . ' am ' . date_i18n('d.m.Y', strtotime($date)) . ($note !== '' ? ' – ' . $note : ''));
            foreach ($ids as $id) slicewp_update_commission($id, ['status' => 'paid', 'payment_id' => $entry['payment'], 'date_modified' => current_time('mysql', 1)]);
        }
    }
    if (!$mail) remove_filter('pre_wp_mail', '__return_true', 999);

    $transfers[] = $entry;
    update_option('sp_aff_transfers', $transfers, false);
    return $entry + ['covered' => count($ids), 'covered_sum' => round($sum, 2)];
}

/** Letzte Überweisung eines Partners rückgängig machen (z. B. Tippfehler beim Betrag). */
function sp_afp_undo_transfer($id) {
    global $wpdb;
    $transfers = (array) get_option('sp_aff_transfers', []);
    $idx = null;
    foreach ($transfers as $i => $t) if ((int) $t['id'] === (int) $id) $idx = $i;
    if ($idx === null) return new WP_Error('sp_afp', 'Eintrag nicht gefunden.');
    $t = $transfers[$idx];
    foreach ($transfers as $o) {
        if ((int) $o['aff'] === (int) $t['aff'] && (int) $o['id'] > (int) $t['id']) return new WP_Error('sp_afp', 'Nur die letzte Überweisung eines Partners kann rückgängig gemacht werden.');
    }
    if (!empty($t['payment'])) {
        $wpdb->update($wpdb->prefix . 'slicewp_commissions', ['status' => 'unpaid', 'payment_id' => 0], ['payment_id' => (int) $t['payment'], 'affiliate_id' => (int) $t['aff']]);
        $wpdb->delete($wpdb->prefix . 'slicewp_payments', ['id' => (int) $t['payment']]);
        $wpdb->delete($wpdb->prefix . 'slicewp_payment_meta', ['slicewp_payment_id' => (int) $t['payment']]);
    }
    if (!empty($t['payout'])) $wpdb->delete($wpdb->prefix . 'slicewp_payouts', ['id' => (int) $t['payout']]);
    array_splice($transfers, $idx, 1);
    update_option('sp_aff_transfers', $transfers, false);
    return true;
}

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Partner-Auszahlungen', 'Partner-Auszahlungen', 'manage_woocommerce', 'sp-partner-auszahlungen', 'sp_afp_render_page');
}, 30);

function sp_afp_render_page() {
    if (!current_user_can('manage_woocommerce')) return;
    $note = '';
    if (!empty($_POST['sp_afp_add']) && check_admin_referer('sp_afp')) {
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date'] ?? '') ? $_POST['date'] : current_time('Y-m-d');
        $raw = trim((string) wp_unslash($_POST['amount'] ?? '0'));
        $amount = strpos($raw, ',') !== false ? (float) str_replace(',', '.', str_replace('.', '', $raw)) : (float) $raw; // "1.100,50" oder "1100.50"
        $r = sp_afp_record_transfer((int) ($_POST['aff'] ?? 0), $amount, $date, sanitize_text_field(wp_unslash($_POST['note'] ?? '')), !empty($_POST['mail']));
        $note = is_wp_error($r)
            ? '<div class="notice notice-error"><p>' . esc_html($r->get_error_message()) . '</p></div>'
            : '<div class="notice notice-success"><p>Überweisung über ' . esc_html(sp_afp_money($r['amount'])) . ' an ' . esc_html(sp_afp_affiliate_name($r['aff'])) . ' eingetragen ✓ – ' . (int) $r['covered'] . ' Provisionen (' . esc_html(sp_afp_money($r['covered_sum'])) . ') als bezahlt markiert.</p></div>';
    }
    if (!empty($_POST['sp_afp_undo']) && check_admin_referer('sp_afp')) {
        $r = sp_afp_undo_transfer((int) $_POST['sp_afp_undo']);
        $note = is_wp_error($r) ? '<div class="notice notice-error"><p>' . esc_html($r->get_error_message()) . '</p></div>' : '<div class="notice notice-success"><p>Überweisung entfernt, Provisionen wieder offen.</p></div>';
    }

    $sums = sp_afp_commission_sums();
    $transfers = sp_afp_transfers();
    $affs = array_unique(array_merge(array_keys($sums), array_map('intval', wp_list_pluck($transfers, 'aff'))));
    $rows = [];
    foreach ($affs as $aff) {
        $s = $sums[$aff] ?? [];
        $earned = ($s['paid']['s'] ?? 0) + ($s['unpaid']['s'] ?? 0);
        $sent = 0;
        $last = null;
        foreach ($transfers as $t) if ((int) $t['aff'] === $aff) { $sent += (float) $t['amount']; if (!$last || $t['date'] >= $last['date']) $last = $t; }
        $rows[] = ['aff' => $aff, 'name' => sp_afp_affiliate_name($aff), 'earned' => $earned, 'sent' => $sent, 'open' => round($earned - $sent, 2), 'pending' => $s['pending']['s'] ?? 0, 'pending_n' => $s['pending']['n'] ?? 0, 'last' => $last];
    }
    usort($rows, function ($a, $b) { return $b['earned'] <=> $a['earned']; });

    echo '<div class="wrap"><h1>Partner-Auszahlungen</h1>' . $note;
    echo '<p style="max-width:820px">So liest du die Tabelle: <b>Verdient</b> = Provision aus bezahlten Bestellungen. <b>Überwiesen</b> = alles, was du hier eingetragen hast. <b>Offen</b> = Verdient minus Überwiesen – das steht dem Partner noch zu (negativ = du hast mehr überwiesen, wird verrechnet). <b>Ausstehend</b> = Provision aus Bestellungen, die noch nicht bezahlt sind; sie kommt erst nach Zahlungseingang zu „Verdient“ dazu.</p>';
    echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>Partner</th><th style="text-align:right">Verdient</th><th style="text-align:right">Überwiesen</th><th style="text-align:right">Offen</th><th style="text-align:right">Ausstehend</th><th>Letzte Überweisung</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        if ($r['earned'] <= 0 && $r['sent'] <= 0 && $r['pending'] <= 0) continue;
        $open = $r['open'] >= 0.005
            ? '<b style="color:#B45309">' . esc_html(sp_afp_money($r['open'])) . '</b>'
            : ($r['open'] <= -0.005 ? '<span style="color:#15803D">' . esc_html(sp_afp_money(-$r['open'])) . ' Guthaben</span>' : '0,00 €');
        echo '<tr><td><b>' . esc_html($r['name']) . '</b> <span style="color:#888">#' . (int) $r['aff'] . '</span></td>'
            . '<td style="text-align:right">' . esc_html(sp_afp_money($r['earned'])) . '</td>'
            . '<td style="text-align:right">' . esc_html(sp_afp_money($r['sent'])) . '</td>'
            . '<td style="text-align:right">' . $open . '</td>'
            . '<td style="text-align:right;color:#666">' . esc_html(sp_afp_money($r['pending'])) . ($r['pending_n'] ? ' (' . (int) $r['pending_n'] . ')' : '') . '</td>'
            . '<td>' . ($r['last'] ? esc_html(date_i18n('d.m.Y', strtotime($r['last']['date'])) . ' · ' . sp_afp_money($r['last']['amount'])) : '—') . '</td></tr>';
    }
    echo '</tbody></table>';

    echo '<h2 style="margin-top:28px">Überweisung eintragen</h2><form method="post" style="background:#fff;border:1px solid #dcdcde;padding:16px 18px;max-width:640px">';
    wp_nonce_field('sp_afp');
    echo '<p><label>Partner<br><select name="aff" required>';
    foreach ($rows as $r) echo '<option value="' . (int) $r['aff'] . '">' . esc_html($r['name']) . ' – offen ' . esc_html(sp_afp_money(max(0, $r['open']))) . '</option>';
    echo '</select></label></p>';
    echo '<p><label>Betrag (€)<br><input name="amount" required inputmode="decimal" placeholder="z. B. 250 oder 250,50" style="width:180px"></label> &nbsp; <label>Datum der Überweisung<br><input type="date" name="date" value="' . esc_attr(current_time('Y-m-d')) . '"></label></p>';
    echo '<p><label>Notiz (optional)<br><input name="note" class="regular-text" placeholder="z. B. per PayPal, Teilbetrag auf Wunsch"></label></p>';
    echo '<p><label><input type="checkbox" name="mail" value="1"> Partner per E-Mail über die Auszahlung informieren</label></p>';
    echo '<p><button class="button button-primary" name="sp_afp_add" value="1">Überweisung speichern</button></p>';
    echo '<p class="description">Die ältesten auszahlbaren Provisionen werden automatisch als „bezahlt“ markiert, soweit deine Überweisungen sie decken. Teilbeträge sind kein Problem: der Rest bleibt als „Offen“ stehen.</p></form>';

    echo '<h2 style="margin-top:28px">Bisherige Überweisungen</h2>';
    if (!$transfers) echo '<p>Noch keine.</p>';
    else {
        $last_per_aff = [];
        foreach ($transfers as $t) $last_per_aff[(int) $t['aff']] = (int) $t['id'];
        echo '<form method="post">';
        wp_nonce_field('sp_afp');
        echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>Datum</th><th>Partner</th><th style="text-align:right">Betrag</th><th>Notiz</th><th></th></tr></thead><tbody>';
        foreach (array_reverse($transfers) as $t) {
            echo '<tr><td>' . esc_html(date_i18n('d.m.Y', strtotime($t['date']))) . '</td><td>' . esc_html(sp_afp_affiliate_name($t['aff'])) . '</td><td style="text-align:right">' . esc_html(sp_afp_money($t['amount'])) . '</td><td>' . esc_html($t['note']) . '</td><td>'
                . ($last_per_aff[(int) $t['aff']] === (int) $t['id'] ? '<button class="button-link-delete" name="sp_afp_undo" value="' . (int) $t['id'] . '" onclick="return confirm(\'Diese Überweisung wirklich entfernen? Die Provisionen werden wieder offen.\')">Rückgängig</button>' : '') . '</td></tr>';
        }
        echo '</tbody></table></form>';
    }
    echo '</div>';
}
