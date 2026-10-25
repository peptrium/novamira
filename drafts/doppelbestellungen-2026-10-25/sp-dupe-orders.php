<?php
/**
 * Plugin Name: SP Doppel- & Nachbestellungen
 * Description: (2026-10-25) Erkennt doppelte Bestellungen und Nachbestellungen derselben E-Mail innerhalb von 72 Std.
 *   A) Markierung beim Bestellen: gleiche Artikel = "Doppelt?" (keine automatische Zahlungserinnerung fuer die
 *      unbezahlte, Partner sehen "Doppelt bestellt"), andere Artikel = "Nachbestellung" (Hinweis "zusammen versenden").
 *   B) Admin-Box (Vorkasse-Uebersicht, Versand, Bestellungen, Dashboard) + Meta-Box in der Bestellung:
 *      "Als Duplikat stornieren" (ohne Kundenmail, Provision abgelehnt), "Zusammenlegen" (beide unbezahlt:
 *      Artikel in die erste Bestellung, zweite Versandgebuehr weg, Kunde bekommt eine Mail mit neuem Betrag),
 *      "Kein Duplikat". Es wird NIE automatisch storniert.
 *   C) Kasse: Hinweis, wenn der Kunde noch eine offene Bestellung hat.
 */
if (!defined('ABSPATH')) exit;

define('SP_DUP_WINDOW', 72 * HOUR_IN_SECONDS);
define('SP_DUP_SKIP', 'skip-dupe'); // Marker in _sp_vk_auto_sent → automatische Erinnerung entfaellt

/** Artikel-Signatur ohne Geschenke/Guthaben: "pid/vid×qty" sortiert. */
function sp_dup_sig($o) {
    $s = [];
    foreach ($o->get_items() as $it) {
        if ($it->get_meta('Geschenk') || $it->get_meta('_sp_is_gift') || (int) $it->get_product_id() === 729) continue;
        $k = $it->get_product_id() . '/' . $it->get_variation_id();
        $s[$k] = ($s[$k] ?? 0) + (int) $it->get_quantity();
    }
    ksort($s);
    $out = [];
    foreach ($s as $k => $q) $out[] = $k . 'x' . $q;
    return implode(',', $out);
}

function sp_dup_unpaid($o) { return in_array($o->get_status(), ['on-hold', 'pending'], true); }

/** A) Erkennung beim Anlegen der Bestellung. */
function sp_dup_detect($order) {
    $o = is_object($order) ? $order : wc_get_order($order);
    if (!$o || $o->get_meta('_sp_dupe_chk') || $o->get_meta('_sp_is_test')) return;
    $o->update_meta_data('_sp_dupe_chk', 1);
    $o->save();
    $mail = strtolower(trim((string) $o->get_billing_email()));
    $sig = sp_dup_sig($o);
    if ($mail === '' || $sig === '') return;
    $created = $o->get_date_created() ? $o->get_date_created()->getTimestamp() : time();
    $cands = wc_get_orders(['billing_email' => $mail, 'status' => ['on-hold', 'pending', 'processing'], 'limit' => 10,
        'date_created' => '>' . ($created - SP_DUP_WINDOW), 'exclude' => [$o->get_id()], 'orderby' => 'date', 'order' => 'DESC']);
    $other = null; $type = 'nach';
    foreach ($cands as $c) {
        if ($c->get_meta('_sp_is_test') || sp_dup_sig($c) === '') continue;
        if (sp_dup_sig($c) === $sig) { $other = $c; $type = 'dupe'; break; }
        if (!$other) $other = $c;
    }
    if (!$other) return;
    $o->update_meta_data('_sp_dupe', ['t' => $type, 'o' => $other->get_id(), 'at' => time()]);
    $o->save();
    $nr = $o->get_order_number(); $onr = $other->get_order_number();
    if ($type === 'dupe') {
        // Verdaechtig ist die unbezahlte – normalerweise die neue (beim Anlegen noch nicht bezahlt)
        $new_paid = in_array($o->get_status(), ['processing', 'completed'], true);
        sp_dup_set_suspect($new_paid && sp_dup_unpaid($other) ? $other : $o);
        $o->add_order_note('⚠️ Mögliches Duplikat von #' . $onr . ' (gleiche Artikel, gleiche E-Mail). Prüfen: Vorkasse-Übersicht → "Als Duplikat stornieren" oder "Kein Duplikat".');
        $other->add_order_note('⚠️ Kunde hat #' . $nr . ' mit den gleichen Artikeln noch einmal bestellt – mögliches Duplikat.');
    } else {
        $o->add_order_note('📦 Nachbestellung zu #' . $onr . ' (gleiche E-Mail, andere Artikel) – ' . (sp_dup_unpaid($o) && sp_dup_unpaid($other) ? 'zusammenlegen möglich, ' : '') . 'zusammen in ein Paket.');
        $other->add_order_note('📦 Kunde hat nachbestellt: #' . $nr . ' – zusammen in ein Paket.');
    }
}
add_action('woocommerce_store_api_checkout_order_processed', 'sp_dup_detect', 30);
add_action('woocommerce_checkout_order_processed', function ($id) { sp_dup_detect($id); }, 30);

/** Verdaechtige Bestellung markieren: keine automatische Erinnerung, Partner sehen "Doppelt bestellt". */
function sp_dup_set_suspect($o, $other = null) {
    if ($other && $other->get_meta('_sp_dupe_suspect')) {
        $other->delete_meta_data('_sp_dupe_suspect');
        if ($other->get_meta('_sp_vk_auto_sent') === SP_DUP_SKIP) $other->delete_meta_data('_sp_vk_auto_sent');
        $other->save();
    }
    $o->update_meta_data('_sp_dupe_suspect', 1);
    if (!$o->get_meta('_sp_vk_auto_sent')) $o->update_meta_data('_sp_vk_auto_sent', SP_DUP_SKIP);
    $o->save();
}

/** Wird die verdaechtige bezahlt, waehrend die andere noch offen ist → Markierung wandert zur offenen. */
add_action('woocommerce_order_status_processing', function ($id) {
    $o = wc_get_order($id);
    if (!$o) return;
    $pair = sp_dup_pair_of($o);
    if (!$pair || $pair['t'] !== 'dupe' || !$o->get_meta('_sp_dupe_suspect')) return;
    $other = $pair['other'];
    if (sp_dup_unpaid($other)) {
        sp_dup_set_suspect($other, $o);
        $other->add_order_note('⚠️ #' . $o->get_order_number() . ' (gleiche Artikel) wurde bezahlt – diese Bestellung ist jetzt das mögliche Duplikat.');
    }
});

/** Paar zu einer Bestellung (egal ob neuere oder aeltere). */
function sp_dup_pair_of($o) {
    $m = $o->get_meta('_sp_dupe');
    if (is_array($m) && !empty($m['o'])) {
        $other = wc_get_order((int) $m['o']);
        return $other ? ['t' => $m['t'], 'new' => $o, 'old' => $other, 'other' => $other] : null;
    }
    $r = wc_get_orders(['meta_query' => [['key' => '_sp_dupe', 'value' => '"o";i:' . $o->get_id() . ';', 'compare' => 'LIKE']], 'limit' => 1, 'status' => 'any']);
    if ($r) {
        $m = $r[0]->get_meta('_sp_dupe');
        return ['t' => $m['t'], 'new' => $r[0], 'old' => $o, 'other' => $r[0]];
    }
    return null;
}

/** Offene Faelle der letzten 14 Tage fuer die Admin-Box. */
function sp_dup_cases() {
    $list = wc_get_orders(['meta_query' => [['key' => '_sp_dupe', 'compare' => 'EXISTS']], 'status' => 'any', 'limit' => 30,
        'date_created' => '>' . (time() - 14 * DAY_IN_SECONDS)]);
    $out = [];
    foreach ($list as $new) {
        if ($new->get_meta('_sp_dupe_done')) continue;
        $m = $new->get_meta('_sp_dupe');
        $old = wc_get_order((int) ($m['o'] ?? 0));
        if (!$old) continue;
        $live = ['on-hold', 'pending', 'processing'];
        if (!in_array($new->get_status(), $live, true) || !in_array($old->get_status(), $live, true)) continue;
        $out[] = ['t' => $m['t'], 'new' => $new, 'old' => $old];
    }
    return $out;
}

function sp_dup_url($act, $id, $extra = []) {
    return wp_nonce_url(add_query_arg(array_merge(['action' => 'sp_dup', 'a' => $act, 'o' => $id], $extra), admin_url('admin-post.php')), 'sp_dup_' . $act . '_' . $id);
}

function sp_dup_status_label($o) {
    $s = $o->get_status();
    if ($s === 'on-hold' || $s === 'pending') return '<span style="color:#9A5B00;font-weight:700">unbezahlt</span>';
    if ($s === 'processing') return '<span style="color:#1F5FA8;font-weight:700">bezahlt · nicht versendet</span>';
    return esc_html(wc_get_order_status_name($s));
}

function sp_dup_olink($o) {
    return '<a href="' . esc_url($o->get_edit_order_url()) . '"><b>#' . esc_html($o->get_order_number()) . '</b></a> ' . wp_date('d.m. H:i', $o->get_date_created()->getTimestamp())
        . ' · ' . wp_strip_all_tags(wc_price($o->get_total())) . ' · ' . sp_dup_status_label($o);
}

/** Aktions-Buttons zu einem Fall. */
function sp_dup_actions($c) {
    $new = $c['new']; $old = $c['old'];
    $b = [];
    $btn = function ($url, $label, $confirm, $primary = false) {
        return '<a class="button' . ($primary ? ' button-primary' : '') . '" href="' . esc_url($url) . '" onclick="return confirm(' . esc_attr(wp_json_encode($confirm)) . ')">' . esc_html($label) . '</a>';
    };
    foreach ([$new, $old] as $o) {
        $other = $o === $new ? $old : $new;
        if ($c['t'] === 'dupe' && sp_dup_unpaid($o) && in_array($other->get_status(), ['on-hold', 'pending', 'processing'], true)) {
            $b[] = $btn(sp_dup_url('storno', $o->get_id()), '#' . $o->get_order_number() . ' als Duplikat stornieren',
                'Bestellung #' . $o->get_order_number() . ' stornieren? Keine Mail an den Kunden, Partner-Provision wird abgelehnt.', $o->get_meta('_sp_dupe_suspect'));
        }
    }
    if ($new->get_status() === 'on-hold' && $old->get_status() === 'on-hold' && $new->get_payment_method() === 'bacs' && $old->get_payment_method() === 'bacs') {
        $b[] = $btn(sp_dup_url('merge', $new->get_id()), 'Zusammenlegen in #' . $old->get_order_number(),
            'Artikel aus #' . $new->get_order_number() . ' in #' . $old->get_order_number() . ' übernehmen, zweite Versandgebühr streichen, #' . $new->get_order_number() . ' ohne Mail stornieren und dem Kunden den neuen Betrag mit Bankdaten mailen?', $c['t'] === 'nach');
    }
    $b[] = $btn(sp_dup_url('ok', $new->get_id()), $c['t'] === 'dupe' ? 'Kein Duplikat' : 'Erledigt', 'Hinweis ausblenden?');
    return implode(' ', $b);
}

/** B) Admin-Box oben auf den wichtigsten Seiten. */
add_action('admin_notices', function () {
    if (!current_user_can('manage_woocommerce')) return;
    $page = $_GET['page'] ?? '';
    if (!in_array($page, ['sp-vorkasse-overview', 'sp-versand', 'wc-orders', 'peptrium-dashboard'], true)) return;
    if (!empty($_GET['sp_dup_msg'])) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(wp_unslash($_GET['sp_dup_msg'])) . '</p></div>';
    }
    $cases = sp_dup_cases();
    if (!$cases) return;
    echo '<div class="notice notice-warning" style="padding:10px 14px"><p style="font-size:14px;margin:0 0 8px"><b>Doppel- / Nachbestellungen prüfen (' . count($cases) . ')</b></p>';
    foreach ($cases as $c) {
        $name = trim($c['new']->get_billing_first_name() . ' ' . $c['new']->get_billing_last_name());
        $both_paid = !sp_dup_unpaid($c['new']) && !sp_dup_unpaid($c['old']);
        $head = $c['t'] === 'dupe' ? ($both_paid ? '⚠️ <b>Doppelt bestellt UND beide bezahlt</b> – eine Zahlung erstatten oder mit dem Kunden klären' : '⚠️ <b>Doppelt bestellt?</b> (gleiche Artikel)')
            : ($both_paid ? '📦 <b>Zusammen in ein Paket</b> (Nachbestellung, beide bezahlt)' : '📦 <b>Nachbestellung</b> (andere Artikel) – zusammen versenden');
        echo '<div style="border-top:1px solid #e5e5e5;padding:8px 0">' . $head . ' · ' . esc_html($name) . ' &lt;' . esc_html($c['new']->get_billing_email()) . '&gt;<br>'
            . 'Erste: ' . sp_dup_olink($c['old']) . '<br>Zweite: ' . sp_dup_olink($c['new']) . '<div style="margin-top:6px">' . sp_dup_actions($c) . '</div></div>';
    }
    echo '</div>';
});

/** B) Meta-Box in der Bestellung. */
add_action('add_meta_boxes', function () {
    foreach (['shop_order', 'woocommerce_page_wc-orders'] as $screen) {
        add_meta_box('sp-dup-box', 'Doppel-/Nachbestellung', function ($post_or_order) {
            $o = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order($post_or_order->ID);
            $p = $o ? sp_dup_pair_of($o) : null;
            if (!$p) { echo '<p style="color:#777">Keine andere offene Bestellung dieser E-Mail in 72 Std.</p>'; return; }
            $c = ['t' => $p['t'], 'new' => $p['new'], 'old' => $p['old']];
            echo '<p>' . ($p['t'] === 'dupe' ? '⚠️ <b>Gleiche Artikel</b> wie ' : '📦 <b>Nachbestellung</b> zu ') . sp_dup_olink($p['other']) . '</p>';
            if ($p['new']->get_meta('_sp_dupe_done')) { echo '<p style="color:#777">Erledigt (' . esc_html($p['new']->get_meta('_sp_dupe_done')) . ').</p>'; return; }
            echo '<p style="display:flex;flex-direction:column;gap:6px">' . str_replace('<a class="button', '<a style="text-align:center;white-space:normal;height:auto" class="button', sp_dup_actions($c)) . '</p>';
        }, $screen, 'side', 'high');
    }
});

/** Bestellung ohne Mails stornieren, Provisionen ablehnen. */
function sp_dup_cancel_silent($o, $note) {
    global $wpdb;
    $block = function () { return true; };
    add_filter('pre_wp_mail', $block, 1);
    $o->update_status('cancelled', $note);
    remove_filter('pre_wp_mail', $block, 1);
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}slicewp_commissions SET status = 'rejected' WHERE reference = %s AND status IN ('pending','unpaid')", (string) $o->get_id()));
    $n = (array) get_option('sp_aff_nachtrag', []);
    if (isset($n[$o->get_id()])) { unset($n[$o->get_id()]); update_option('sp_aff_nachtrag', $n, false); }
}

add_action('admin_post_sp_dup', function () {
    $act = sanitize_key($_GET['a'] ?? ''); $id = absint($_GET['o'] ?? 0);
    if (!current_user_can('manage_woocommerce') || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'sp_dup_' . $act . '_' . $id)) wp_die('Keine Berechtigung.');
    $o = wc_get_order($id);
    if (!$o) wp_die('Bestellung nicht gefunden.');
    $p = sp_dup_pair_of($o);
    $msg = '';
    if ($act === 'storno') {
        if (!sp_dup_unpaid($o)) wp_die('Nur unbezahlte Bestellungen können hier storniert werden (bezahlt → Erstattung manuell).');
        $other = $p ? $p['other'] : null;
        sp_dup_cancel_silent($o, 'Als Duplikat' . ($other ? ' von #' . $other->get_order_number() : '') . ' storniert – ohne Kundenmail, Partner-Provision abgelehnt.');
        if ($p) { $p['new']->update_meta_data('_sp_dupe_done', 'storniert ' . wp_date('d.m. H:i')); $p['new']->save(); }
        $msg = '#' . $o->get_order_number() . ' als Duplikat storniert (keine Kundenmail).';
    } elseif ($act === 'merge') {
        if (!$p) wp_die('Kein Paar gefunden.');
        $r = sp_dup_merge($p['old'], $p['new']);
        if (is_wp_error($r)) wp_die(esc_html($r->get_error_message()));
        $msg = $r;
    } elseif ($act === 'ok') {
        if ($p) {
            $p['new']->update_meta_data('_sp_dupe_done', 'ok ' . wp_date('d.m. H:i'));
            $p['new']->save();
            foreach ([$p['new'], $p['old']] as $x) {
                if (!$x->get_meta('_sp_dupe_suspect')) continue;
                $x->delete_meta_data('_sp_dupe_suspect');
                if ($x->get_meta('_sp_vk_auto_sent') === SP_DUP_SKIP) $x->delete_meta_data('_sp_vk_auto_sent');
                $x->save();
            }
            $p['new']->add_order_note('Doppel-/Nachbestellungs-Hinweis als erledigt markiert.');
        }
        $msg = 'Hinweis ausgeblendet.';
    }
    $back = wp_get_referer() ?: admin_url('admin.php?page=sp-vorkasse-overview');
    wp_safe_redirect(add_query_arg('sp_dup_msg', rawurlencode($msg), remove_query_arg('sp_dup_msg', $back)));
    exit;
});

/** Zusammenlegen: Artikel/Gebuehren von $drop in $keep, zweite Versandgebuehr weg, $drop still stornieren, Kunde bekommt Mail. */
function sp_dup_merge($keep, $drop) {
    global $wpdb;
    foreach ([$keep, $drop] as $x) {
        if ($x->get_status() !== 'on-hold' || $x->get_payment_method() !== 'bacs') return new WP_Error('st', 'Zusammenlegen geht nur, wenn beide Bestellungen unbezahlte Vorkasse-Bestellungen sind.');
    }
    if (strtolower($keep->get_billing_email()) !== strtolower($drop->get_billing_email())) return new WP_Error('em', 'Unterschiedliche E-Mail-Adressen.');
    $old_total = (float) $keep->get_total();
    $moved = [];
    foreach ($drop->get_items() as $it) {
        $n = new WC_Order_Item_Product();
        $n->set_props(['product_id' => $it->get_product_id(), 'variation_id' => $it->get_variation_id(), 'quantity' => $it->get_quantity(),
            'subtotal' => $it->get_subtotal(), 'total' => $it->get_total(), 'subtotal_tax' => $it->get_subtotal_tax(), 'total_tax' => $it->get_total_tax(),
            'taxes' => $it->get_taxes(), 'name' => $it->get_name(), 'tax_class' => $it->get_tax_class()]);
        foreach ($it->get_meta_data() as $m) {
            if ($m->key === '_reduced_stock') continue;
            $n->add_meta_data($m->key, $m->value);
        }
        // Bestand: die stornierte Bestellung bucht ihren Bestand zurueck → hier neu abziehen
        $prod = $it->get_product();
        if ($prod && $prod->managing_stock() && $it->get_meta('_reduced_stock')) {
            wc_update_product_stock($prod, (int) $it->get_quantity(), 'decrease');
            $n->add_meta_data('_reduced_stock', (int) $it->get_quantity(), true);
        }
        $keep->add_item($n);
        $moved[] = $it->get_quantity() . '× ' . $it->get_name();
    }
    foreach ($drop->get_fees() as $f) {
        $n = new WC_Order_Item_Fee();
        $n->set_props(['name' => $f->get_name(), 'total' => $f->get_total(), 'total_tax' => $f->get_total_tax(), 'taxes' => $f->get_taxes(), 'tax_class' => $f->get_tax_class(), 'tax_status' => $f->get_tax_status()]);
        $keep->add_item($n);
    }
    $coupons = $drop->get_coupon_codes();
    $keep->calculate_totals(false);
    // Versand: nur einmal; ab Gratisversand-Schwelle (DE) kostenlos
    $items_sum = 0;
    foreach ($keep->get_items() as $it) $items_sum += (float) $it->get_total() + (float) $it->get_total_tax();
    foreach ($keep->get_fees() as $f) $items_sum += min(0, (float) $f->get_total());
    $thr = function_exists('sp_fsh_threshold') ? (float) sp_fsh_threshold() : 150;
    $free = false;
    if ($keep->get_shipping_country() === 'DE' && $thr > 0 && $items_sum >= $thr) {
        foreach ($keep->get_shipping_methods() as $s) {
            if ((float) $s->get_total() > 0) { $s->set_total(0); $s->set_taxes(['total' => []]); $s->set_name('Kostenloser Versand'); $s->save(); $free = true; }
        }
    }
    $keep->calculate_totals(false);
    $drop_ship = 0;
    foreach ($drop->get_shipping_methods() as $s) $drop_ship += (float) $s->get_total();
    $nr = $keep->get_order_number(); $dnr = $drop->get_order_number();
    // Provisionen + nachgetragene Codes auf die zusammengelegte Bestellung umhaengen
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}slicewp_commissions SET reference = %s WHERE reference = %s AND status IN ('pending','unpaid')", (string) $keep->get_id(), (string) $drop->get_id()));
    $na = (array) get_option('sp_aff_nachtrag', []);
    if (isset($na[$drop->get_id()])) {
        if (!isset($na[$keep->get_id()])) $na[$keep->get_id()] = $na[$drop->get_id()];
        unset($na[$drop->get_id()]);
        update_option('sp_aff_nachtrag', $na, false);
    }
    $keep->add_order_note('Mit #' . $dnr . ' zusammengelegt: ' . implode(', ', $moved) . '. Neuer Betrag ' . wc_format_localized_price(wc_format_decimal($keep->get_total(), 2)) . ' € (vorher ' . wc_format_localized_price(wc_format_decimal($old_total, 2)) . ' €)'
        . ($drop_ship > 0 ? ', zweite Versandgebühr ' . wc_format_localized_price(wc_format_decimal($drop_ship, 2)) . ' € entfällt' : '') . ($free ? ', jetzt Gratisversand' : '')
        . ($coupons ? '. Gutschein der zweiten Bestellung: ' . implode(', ', $coupons) . ' (Rabatt in den Artikelpreisen enthalten)' : '') . '. Hinweis: Geschenkstufen/Aktionen wurden nicht neu berechnet.');
    $drop->update_meta_data('_sp_dupe_done', 'zusammengelegt ' . wp_date('d.m. H:i'));
    $drop->save();
    // Partner-Provision der zweiten Bestellung NICHT ablehnen (ist umgehaengt) → nur still stornieren
    $block = function () { return true; };
    add_filter('pre_wp_mail', $block, 1);
    $drop->update_status('cancelled', 'In #' . $nr . ' zusammengelegt – storniert ohne Kundenmail.');
    remove_filter('pre_wp_mail', $block, 1);
    $keep = wc_get_order($keep->get_id());
    $sent = sp_dup_merge_mail($keep, $dnr, $drop_ship > 0);
    return 'Zusammengelegt: #' . $dnr . ' → #' . $nr . ', neuer Betrag ' . wp_strip_all_tags(wc_price($keep->get_total())) . '. ' . ($sent ? 'Kunde hat eine Mail mit dem neuen Betrag bekommen.' : 'ACHTUNG: Mail an den Kunden konnte nicht gesendet werden.');
}

/** Mail "Bestellungen zusammengelegt" im Stil der Zahlungserinnerung. */
function sp_dup_merge_mail_html($keep, $dnr, $ship_saved) {
    if (!function_exists('sp_vorkasse_reminder_html')) return '';
    $html = sp_vorkasse_reminder_html($keep);
    $nr = esc_html($keep->get_order_number());
    $amount = wp_strip_all_tags(wc_price($keep->get_total()));
    $html = str_replace('⏳ ZAHLUNG OFFEN', '📦 ZUSAMMENGELEGT', $html);
    $html = str_replace('Deine Zahlung<br>fehlt noch.', 'Ein Paket,<br>ein Betrag.', $html);
    $html = preg_replace('/Deine Bestellung vom [^<]*? ist für dich reserviert\.[^<]*/u',
        'Wir haben deine Bestellungen #' . $nr . ' und #' . esc_html($dnr) . ' zu einer zusammengelegt – du bekommst alles in einem Paket.'
        . ($ship_saved ? ' Die zweite Versandgebühr entfällt.' : '') . ' Bitte überweise nur noch den neuen Betrag von <strong>' . esc_html($amount) . '</strong> mit dem Verwendungszweck <strong>' . $nr . '</strong>.', $html, 1);
    $html = preg_replace('/<strong([^>]*)>Schon überwiesen\?<\/strong>.*?(<\/div>)/us',
        '<strong$1>Schon etwas überwiesen?</strong> Kein Problem – wir verrechnen es. Melde dich einfach kurz bei uns, falls du schon einen Betrag für eine der beiden Bestellungen überwiesen hast.$2', $html, 1);
    return $html;
}
function sp_dup_merge_mail($keep, $dnr, $ship_saved) {
    $html = sp_dup_merge_mail_html($keep, $dnr, $ship_saved);
    if (!$html) return false;
    $ok = wp_mail($keep->get_billing_email(), 'Deine Bestellungen #' . $keep->get_order_number() . ' und #' . $dnr . ' wurden zusammengelegt', $html, ['Content-Type: text/html; charset=UTF-8']);
    if ($ok) {
        $r = (array) $keep->get_meta('_sp_vorkasse_reminders');
        $r[] = time(); // zaehlt als Erinnerung (Bankdaten sind drin)
        $keep->update_meta_data('_sp_vorkasse_reminders', array_values(array_filter($r)));
        $keep->add_order_note('Mail "Bestellungen zusammengelegt" mit neuem Betrag und Bankdaten an den Kunden gesendet.');
        $keep->save();
    }
    return $ok;
}

/* ---------- C) Hinweis in der Kasse ---------- */

/** Offene Bestellungen (unbezahlt oder bezahlt-unversendet, 7 Tage) zu einer E-Mail / einem Kunden. */
function sp_dup_open_orders($email, $uid = 0) {
    $email = strtolower(trim((string) $email));
    if (!$uid && !is_email($email)) return [];
    $args = ['status' => ['on-hold', 'pending', 'processing'], 'limit' => 3, 'date_created' => '>' . (time() - 7 * DAY_IN_SECONDS), 'orderby' => 'date', 'order' => 'DESC'];
    $res = $uid ? wc_get_orders($args + ['customer_id' => $uid]) : [];
    if ($email) $res = array_merge($res, wc_get_orders($args + ['billing_email' => $email]));
    $out = [];
    foreach ($res as $o) {
        if ($o->get_meta('_sp_is_test') || $o->get_created_via() === 'sp_abo' || sp_dup_sig($o) === '') continue;
        if ($o->get_status() === 'pending' && $o->get_payment_method() !== 'bacs') continue; // abgebrochene Krypto-Zahlung
        $out[$o->get_id()] = $o;
    }
    return array_values($out);
}

function sp_dup_hint_text($orders, $detail) {
    if (!$orders) return '';
    $o = $orders[0];
    $unpaid = sp_dup_unpaid($o);
    if ($detail) {
        $what = 'Bestellung #' . $o->get_order_number() . ' vom ' . wp_date('d.m.', $o->get_date_created()->getTimestamp()) . ' (' . wp_strip_all_tags(wc_price($o->get_total())) . ', ' . ($unpaid ? 'noch nicht bezahlt' : 'noch nicht versendet') . ')';
    } else {
        $what = 'eine offene Bestellung';
    }
    return '<b>Du hast noch ' . esc_html($what) . '.</b> Bitte nicht doppelt bestellen – bestell einfach nur die neuen Artikel, wir versenden alles zusammen.';
}

add_action('wp_ajax_sp_dupe_check', 'sp_dup_ajax');
add_action('wp_ajax_nopriv_sp_dupe_check', 'sp_dup_ajax');
function sp_dup_ajax() {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
    $k = 'sp_dupck_' . md5($ip);
    $n = (int) get_transient($k);
    if ($n >= 20) wp_send_json(['h' => '']);
    set_transient($k, $n + 1, HOUR_IN_SECONDS);
    $uid = get_current_user_id();
    $orders = sp_dup_open_orders(sanitize_email(wp_unslash($_POST['e'] ?? '')), $uid);
    // Gaeste: keine Nummer/Betraege (sonst koennte jeder fremde Bestellungen abfragen)
    wp_send_json(['h' => sp_dup_hint_text($orders, $uid > 0)]);
}

add_action('wp_footer', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page() || is_wc_endpoint_url('order-pay')) return;
    $pre = '';
    if (is_user_logged_in()) {
        $u = wp_get_current_user();
        $pre = sp_dup_hint_text(sp_dup_open_orders($u->user_email, $u->ID), true);
    }
    ?>
<style>#sp-dup-hint{display:flex;gap:10px;align-items:flex-start;background:#FFF7EA;border:1px solid #F4D7A8;border-radius:14px;padding:12px 14px;margin:0 0 14px;font-size:13.5px;line-height:1.5;color:#5A3A00}#sp-dup-hint .i{font-size:18px;line-height:1.2}#sp-dup-hint b{color:#3D2700}</style>
<script>
(function(){
  var html = <?php echo wp_json_encode($pre); ?>, last = null, busy = false, t = null;
  var ajax = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
  function place(){
    var ex = document.getElementById('sp-dup-hint');
    if (!html) { if (ex) ex.remove(); return; }
    var anchor = document.querySelector('.wc-block-checkout__actions');
    if (!anchor) return;
    if (!ex) { ex = document.createElement('div'); ex.id = 'sp-dup-hint'; }
    if (ex.dataset.h !== html) { ex.innerHTML = '<span class="i">📦</span><div>' + html + '</div>'; ex.dataset.h = html; }
    if (ex.nextElementSibling !== anchor) anchor.parentNode.insertBefore(ex, anchor);
  }
  function check(email){
    if (busy || email === last) return;
    last = email; busy = true;
    var fd = new FormData(); fd.append('action', 'sp_dupe_check'); fd.append('e', email);
    fetch(ajax, {method: 'POST', body: fd, credentials: 'same-origin'}).then(function(r){ return r.json(); }).then(function(d){
      if (d && typeof d.h === 'string') html = d.h;
      place();
    }).catch(function(){}).finally(function(){ busy = false; });
  }
  function email(){
    try { var c = wp.data.select('wc/store/cart').getCustomerData(); return ((c.billingAddress && c.billingAddress.email) || '').trim().toLowerCase(); } catch (e) { return ''; }
  }
  function tick(){
    var e = email();
    if (e && /^[^@\s]+@[^@\s]+\.[a-z]{2,}$/.test(e)) check(e);
    place();
  }
  function init(){
    if (window.wp && wp.data && wp.data.subscribe) {
      wp.data.subscribe(function(){ if (t) return; t = setTimeout(function(){ t = null; tick(); }, 800); });
    }
    setInterval(place, 1500);
    tick();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
    <?php
}, 60);
