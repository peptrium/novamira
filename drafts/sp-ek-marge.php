<?php
/**
 * Plugin Name: SP Einkauf & Marge
 * Description: Einkaufspreise (EK) je Produkt/Variante + Peptrium Dashboard → Umsatz & Marge:
 * Umsatz, Wareneinsatz, Partner-Provisionen, eigene Versand-/Verpackungskosten → Ergebnis. 2026-10-18.
 *
 * Optionen: sp_ek_items [post_id => ['usd'=>float|null,'eur'=>float|null,'code'=>'RT10']] (EK je Flasche/Stück),
 * sp_ek_rate (USD→EUR, täglich aktualisiert, sofern sp_ek_rate_manual leer), sp_ek_costs (Zusatzkosten).
 * Beim Bezahlen einer Bestellung wird der EK je Position in die Bestellposition geschrieben (_sp_ek_eur),
 * damit spätere EK-Änderungen alte Monate nicht verändern.
 */

if (!defined('ABSPATH')) exit;

function sp_ek_items() { return (array) get_option('sp_ek_items', []); }
function sp_ek_rate() {
    $m = (float) get_option('sp_ek_rate_manual', 0);
    return $m > 0 ? $m : (float) get_option('sp_ek_rate', 0.89);
}
function sp_ek_costs() {
    return wp_parse_args((array) get_option('sp_ek_costs', []), ['import_pct' => 0, 'ship_order' => 0, 'ship_order_at' => 0, 'pack_order' => 0]);
}

/** EK in € je Stück für eine Produkt- oder Varianten-ID (inkl. Aufschlag für Versand/Zoll beim Einkauf), null = unbekannt. */
function sp_ek_unit_eur($id) {
    $it = sp_ek_items();
    $e = $it[(int) $id] ?? null;
    if (!$e) return null;
    if (isset($e['eur']) && $e['eur'] !== null && $e['eur'] !== '') $v = (float) $e['eur'];
    elseif (isset($e['usd']) && $e['usd'] !== null && $e['usd'] !== '') $v = (float) $e['usd'] * sp_ek_rate();
    else return null;
    // Aufschlag nur auf den Lieferantenpreis; 'add' = feste €-Teile (z. B. Pen + Kartusche)
    return round($v * (1 + (float) sp_ek_costs()['import_pct'] / 100) + (float) ($e['add'] ?? 0), 4);
}

/** EK einer Bestellposition: gespeicherter Wert, sonst aktueller EK (Variante vor Produkt). */
function sp_ek_item_unit($item) {
    $s = $item->get_meta('_sp_ek_eur', true);
    if ($s !== '' && $s !== null && $s !== false) return [(float) $s, true];
    $v = $item->get_variation_id() ? sp_ek_unit_eur($item->get_variation_id()) : null;
    if ($v === null) $v = sp_ek_unit_eur($item->get_product_id());
    // früheres Einfachprodukt (z. B. GHK-Cu 68 → 50-mg-Variante 1011)
    if ($v === null && !$item->get_variation_id() && function_exists('sp_bbv_legacy_variation') && ($lv = sp_bbv_legacy_variation($item->get_product_id())) && !empty($lv[0])) $v = sp_ek_unit_eur((int) $lv[0]);
    return [$v, false];
}

/* EK beim Bezahlen festschreiben */
function sp_ek_snapshot($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;
    $changed = false;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_wallet_amount') || $item->get_meta('_sp_ek_eur', true) !== '') continue;
        [$v] = sp_ek_item_unit($item);
        if ($v === null) continue;
        $item->update_meta_data('_sp_ek_eur', $v);
        $item->save();
        $changed = true;
    }
    if ($changed) $order->save();
}
add_action('woocommerce_order_status_processing', 'sp_ek_snapshot', 30);
add_action('woocommerce_order_status_completed', 'sp_ek_snapshot', 30);

/* Wechselkurs täglich aktualisieren */
add_action('init', function () {
    if (!wp_next_scheduled('sp_ek_rate_update')) wp_schedule_event(time() + 300, 'daily', 'sp_ek_rate_update');
});
add_action('sp_ek_rate_update', 'sp_ek_fetch_rate');
function sp_ek_fetch_rate() {
    $r = wp_remote_get('https://open.er-api.com/v6/latest/USD', ['timeout' => 10]);
    if (is_wp_error($r)) return false;
    $j = json_decode(wp_remote_retrieve_body($r), true);
    $v = (float) ($j['rates']['EUR'] ?? 0);
    if ($v > 0.5 && $v < 1.5) { update_option('sp_ek_rate', round($v, 4), false); update_option('sp_ek_rate_date', current_time('Y-m-d'), false); return $v; }
    return false;
}

/* ---------- Auswertung ---------- */

/** Zahlen je Bestellung: Umsatz (ohne Guthaben-Aufladungen, minus Erstattungen), Wareneinsatz, Provision, eigene Kosten. */
function sp_ek_order_figures($order) {
    global $wpdb;
    $c = sp_ek_costs();
    $topup = 0.0; $goods = 0; $cogs = 0.0; $missing = 0; $estimated = 0; $lines = [];
    $items_total = 0.0;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_wallet_amount')) { $topup += (float) $item->get_total() + (float) $item->get_total_tax(); continue; }
        $items_total += (float) $item->get_total();
    }
    $fees = 0.0;
    foreach ($order->get_fees() as $f) $fees += (float) $f->get_total();
    $factor = $items_total > 0 ? max(0, ($items_total + min(0, $fees)) / $items_total) : 1;
    foreach ($order->get_items() as $item) {
        if ($item->get_meta('_sp_wallet_amount')) continue;
        $q = (int) $item->get_quantity();
        $goods += $q;
        [$u, $snap] = sp_ek_item_unit($item);
        if ($u === null) $missing += $q; else { $cogs += $u * $q; if (!$snap) $estimated += $q; }
        $lines[] = ['pid' => $item->get_product_id(), 'vid' => $item->get_variation_id(), 'name' => $item->get_name(), 'qty' => $q,
                    'rev' => (float) $item->get_total() * $factor, 'cogs' => $u === null ? null : $u * $q, 'gift' => (float) $item->get_total() == 0.0];
    }
    $revenue = max(0.0, (float) $order->get_total() - (float) $order->get_total_refunded() - $topup);
    $comm = (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$wpdb->prefix}slicewp_commissions WHERE reference=%s AND status<>'rejected'", (string) $order->get_id()));
    $ship = $order->get_shipping_country() === 'AT' && (float) $c['ship_order_at'] > 0 ? (float) $c['ship_order_at'] : (float) $c['ship_order'];
    $own = $goods > 0 && $order->get_items('shipping') ? $ship + (float) $c['pack_order'] : ($goods > 0 ? (float) $c['pack_order'] : 0.0);
    return ['revenue' => $revenue, 'shipping_in' => (float) $order->get_shipping_total(), 'cogs' => $cogs, 'comm' => $comm, 'own' => $own,
            'result' => $revenue - $cogs - $comm - $own, 'missing' => $missing, 'estimated' => $estimated, 'goods' => $goods, 'lines' => $lines, 'topup' => $topup];
}

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Umsatz & Marge', 'Umsatz & Marge', 'manage_woocommerce', 'sp-umsatz-marge', 'sp_ek_render_page');
}, 25);

function sp_ek_money($v) { return number_format((float) $v, 2, ',', '.') . ' €'; }

function sp_ek_render_page() {
    if (!current_user_can('manage_woocommerce')) return;
    $note = '';
    if (!empty($_POST['sp_ek_save']) && check_admin_referer('sp_ek')) {
        $items = sp_ek_items();
        foreach ((array) ($_POST['ek'] ?? []) as $id => $row) {
            $id = (int) $id;
            $usd = trim(str_replace(',', '.', (string) wp_unslash($row['usd'] ?? '')));
            $eur = trim(str_replace(',', '.', (string) wp_unslash($row['eur'] ?? '')));
            $add = trim(str_replace(',', '.', (string) wp_unslash($row['add'] ?? '')));
            $items[$id] = ['usd' => $usd === '' ? null : (float) $usd, 'eur' => $eur === '' ? null : (float) $eur, 'add' => $add === '' ? 0 : (float) $add, 'code' => sanitize_text_field(wp_unslash($row['code'] ?? ''))];
        }
        update_option('sp_ek_items', $items, false);
        $c = [];
        foreach (['import_pct', 'ship_order', 'ship_order_at', 'pack_order'] as $k) $c[$k] = (float) str_replace(',', '.', (string) wp_unslash($_POST['costs'][$k] ?? 0));
        update_option('sp_ek_costs', $c, false);
        $m = trim(str_replace(',', '.', (string) wp_unslash($_POST['rate_manual'] ?? '')));
        update_option('sp_ek_rate_manual', $m === '' ? '' : (float) $m, false);
        $note = '<div class="notice notice-success"><p>Gespeichert ✓</p></div>';
    }
    if (!empty($_POST['sp_ek_rate_now']) && check_admin_referer('sp_ek')) {
        $v = sp_ek_fetch_rate();
        $note = $v ? '<div class="notice notice-success"><p>Kurs aktualisiert: 1 $ = ' . esc_html(number_format($v, 4, ',', '.')) . ' €</p></div>' : '<div class="notice notice-error"><p>Kurs konnte nicht geladen werden.</p></div>';
    }

    $range = function_exists('sp_dashboard_get_date_range') ? sp_dashboard_get_date_range() : ['start' => current_time('Y-m-01'), 'end' => current_time('Y-m-d')];
    if (empty($_GET['sp_start'])) $range = ['start' => current_time('Y-m-01'), 'end' => current_time('Y-m-d')];
    $orders = wc_get_orders(['limit' => -1, 'status' => ['processing', 'completed', 'refunded'], 'date_paid' => $range['start'] . '...' . $range['end'] . ' 23:59:59', 'orderby' => 'date', 'order' => 'ASC']);

    $T = ['revenue' => 0, 'shipping_in' => 0, 'cogs' => 0, 'comm' => 0, 'own' => 0, 'result' => 0, 'missing' => 0, 'estimated' => 0, 'n' => 0, 'refunds' => 0];
    $P = []; $rows = [];
    foreach ($orders as $o) {
        if ($o->get_meta('_sp_is_test')) continue;
        $f = sp_ek_order_figures($o);
        if ($f['goods'] === 0) continue; // reine Guthaben-Aufladung = Vorauszahlung, kein Warenverkauf
        foreach (['revenue', 'shipping_in', 'cogs', 'comm', 'own', 'result', 'missing', 'estimated'] as $k) $T[$k] += $f[$k];
        $T['refunds'] += (float) $o->get_total_refunded();
        $T['n']++;
        $rows[] = [$o, $f];
        foreach ($f['lines'] as $l) {
            $k = $l['vid'] ?: $l['pid'];
            if (!isset($P[$k])) $P[$k] = ['name' => $l['name'], 'qty' => 0, 'gift' => 0, 'rev' => 0, 'cogs' => 0, 'miss' => 0];
            $P[$k]['qty'] += $l['qty']; if ($l['gift']) $P[$k]['gift'] += $l['qty'];
            $P[$k]['rev'] += $l['rev'];
            if ($l['cogs'] === null) $P[$k]['miss'] += $l['qty']; else $P[$k]['cogs'] += $l['cogs'];
        }
    }
    uasort($P, function ($a, $b) { return ($b['rev'] - $b['cogs']) <=> ($a['rev'] - $a['cogs']); });

    // ---------- Übersicht: vom Umsatz Schritt für Schritt zum Gewinn ----------
    $buy = function_exists('sp_lager_purchases') ? sp_lager_purchases($range['start'], $range['end']) : ['total' => 0, 'list' => []];
    $paidAff = 0;
    foreach ((array) get_option('sp_aff_transfers', []) as $t) if ($t['date'] >= $range['start'] && $t['date'] <= $range['end']) $paidAff += (float) $t['amount'];
    $cash = $T['revenue'] - $buy['total'] - $paidAff - $T['own'];
    $rev = max(0.01, $T['revenue']);
    $pct = $T['revenue'] > 0 ? $T['result'] / $T['revenue'] * 100 : 0;
    $eu = function ($v) { return esc_html(number_format((float) $v, 2, ',', '.')) . ' €'; };
    $pc = function ($v) use ($rev) { return number_format($v / $rev * 100, 0, ',', '.') . ' %'; };
    // eine Zeile im Wasserfall: Vorzeichen, Titel, Erklärung, Betrag, Balken
    $line = function ($sign, $title, $why, $amount, $kind) use ($eu, $pc, $rev) {
        $w = max(0, min(100, abs($amount) / $rev * 100));
        return '<div class="spm-row spm-' . $kind . '"><div class="spm-sign">' . $sign . '</div><div class="spm-txt"><div class="spm-t">' . $title . '</div><div class="spm-w">' . $why . '</div><div class="spm-bar"><i style="width:' . round($w, 1) . '%"></i></div></div><div class="spm-amt">' . $eu($amount) . '<small>' . ($kind === 'top' ? '100 %' : $pc(abs($amount))) . '</small></div></div>';
    };

    echo '<style>
.spm{max-width:980px;font-size:14px;color:#1d2327}
.spm-head{display:flex;flex-wrap:wrap;align-items:center;gap:10px 18px;margin:6px 0 18px}
.spm-head h1{margin:0 8px 0 0;font-size:24px}
.spm-chips a{display:inline-block;padding:6px 12px;border-radius:999px;background:#f0f0f1;color:#1d2327;text-decoration:none;font-weight:600;font-size:13px;margin-right:6px}
.spm-chips a.on{background:#0d0f12;color:#fff}
.spm-head form{display:flex;gap:6px;align-items:center;font-size:13px;color:#646970}
.spm-card{background:#fff;border:1px solid #e3e5e8;border-radius:16px;padding:22px 24px;margin-bottom:18px;box-shadow:0 2px 10px rgba(13,15,18,.04)}
.spm-card h2{margin:0 0 4px;font-size:18px}
.spm-sub{color:#646970;margin:0 0 16px}
.spm-row{display:grid;grid-template-columns:26px 1fr auto;gap:12px;align-items:center;padding:12px 0;border-top:1px solid #f0f0f1}
.spm-row:first-of-type{border-top:0}
.spm-sign{font-size:20px;font-weight:700;color:#8c8f94;text-align:center}
.spm-t{font-weight:600;font-size:15px}
.spm-w{color:#646970;font-size:12.5px;margin-top:2px}
.spm-bar{height:6px;background:#f0f0f1;border-radius:6px;margin-top:8px;overflow:hidden}
.spm-bar i{display:block;height:100%;border-radius:6px;background:#c3c4c7}
.spm-amt{text-align:right;font-weight:700;font-size:16px;white-space:nowrap}
.spm-amt small{display:block;font-weight:500;font-size:12px;color:#8c8f94}
.spm-top .spm-bar i{background:#0d0f12}.spm-top .spm-amt{font-size:18px}
.spm-minus .spm-amt{color:#b32d2e}.spm-minus .spm-bar i{background:#e8a3a3}
.spm-total{background:#f2faf4;border:1px solid #cce8d4;border-radius:12px;padding:14px 12px;margin-top:10px}
.spm-total .spm-t{font-size:17px}.spm-total .spm-amt{color:#00a32a;font-size:22px}.spm-total .spm-bar i{background:#00a32a}.spm-total .spm-sign{color:#00a32a}
.spm-total.neg{background:#fcf0f1;border-color:#f0c2c4}.spm-total.neg .spm-amt,.spm-total.neg .spm-sign{color:#b32d2e}
.spm-note{background:#f6f7f7;border-radius:10px;padding:12px 14px;color:#50575e;font-size:13px;margin-top:14px}
.spm-note b{color:#1d2327}
.spm-warn{background:#fcf0f1;color:#8a2424;border-radius:10px;padding:10px 14px;margin:0 0 14px;font-size:13px}
.spm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
.spm-box{background:#f6f7f7;border-radius:12px;padding:14px 16px}
.spm-box .l{font-size:12px;color:#646970;text-transform:uppercase;letter-spacing:.04em}
.spm-box .v{font-size:20px;font-weight:700;margin-top:4px}
.spm-box .s{font-size:12px;color:#646970;margin-top:2px}
.spm-box.hl{background:#0d0f12;color:#fff}.spm-box.hl .l,.spm-box.hl .s{color:#a7aaad}
</style>';

    echo '<div class="wrap spm">' . $note;
    // Kopf: Titel, Zeitraum-Chips, eigener Zeitraum
    $m0 = current_time('Y-m-01');
    $chips = [['Dieser Monat', $m0, current_time('Y-m-d')], ['Letzter Monat', date('Y-m-01', strtotime($m0 . ' -1 month')), date('Y-m-t', strtotime($m0 . ' -1 month'))], ['Seit Start', '2026-01-01', current_time('Y-m-d')]];
    echo '<div class="spm-head"><h1>Umsatz &amp; Gewinn</h1><div class="spm-chips">';
    foreach ($chips as $q) echo '<a class="' . ($range['start'] === $q[1] && $range['end'] === $q[2] ? 'on' : '') . '" href="' . esc_url(add_query_arg(['page' => 'sp-umsatz-marge', 'sp_start' => $q[1], 'sp_end' => $q[2]], admin_url('admin.php'))) . '">' . esc_html($q[0]) . '</a>';
    echo '</div><form method="get"><input type="hidden" name="page" value="sp-umsatz-marge"><input type="date" name="sp_start" value="' . esc_attr($range['start']) . '"> – <input type="date" name="sp_end" value="' . esc_attr($range['end']) . '"> <button class="button">Anzeigen</button></form></div>';

    // 1) Gewinn
    echo '<div class="spm-card"><h2>1. Was du verdient hast</h2><p class="spm-sub">' . esc_html(date_i18n('d.m.Y', strtotime($range['start'])) . ' – ' . date_i18n('d.m.Y', strtotime($range['end']))) . ' · ' . (int) $T['n'] . ' bezahlte Bestellungen</p>';
    if ($T['missing']) echo '<div class="spm-warn">' . (int) $T['missing'] . ' verkaufte Artikel haben noch keinen Einkaufspreis – der Gewinn ist deshalb zu hoch. Einkaufspreise unten eintragen.</div>';
    echo $line('', 'Umsatz', 'Was deine Kunden wirklich bezahlt haben – nach Rabatten, Codes und Partner-Rabatt' . ($T['shipping_in'] > 0 ? ', inkl. ' . $eu($T['shipping_in']) . ' Versandgebühren' : '') . ($T['refunds'] ? ', abzüglich ' . $eu($T['refunds']) . ' Erstattungen' : '') . '.', $T['revenue'], 'top')
        . $line('−', 'Ware (Einkaufspreis)', 'Was dich die verkauften Flaschen, Pens und das Zubehör im Einkauf gekostet haben – auch die Gratis-Geschenke.', $T['cogs'], 'minus')
        . $line('−', 'Partner-Provisionen', 'Anteil, den Partner an Bestellungen über ihren Link oder Code verdient haben (egal ob schon überwiesen).', $T['comm'], 'minus')
        . $line('−', 'Versand &amp; Verpackung', 'DHL-Porto (DE 6,19 € / AT 14,90 €) und Karton je Paket.', $T['own'], 'minus')
        . '<div class="spm-row spm-total' . ($T['result'] < 0 ? ' neg' : '') . '"><div class="spm-sign">=</div><div class="spm-txt"><div class="spm-t">Gewinn</div><div class="spm-w">Von jedem Euro Umsatz bleiben dir ' . number_format($pct / 100, 2, ',', '.') . ' € übrig.</div><div class="spm-bar"><i style="width:' . round(max(0, min(100, $pct)), 1) . '%"></i></div></div><div class="spm-amt">' . $eu($T['result']) . '<small>' . number_format($pct, 0, ',', '.') . ' %</small></div></div>'
        . '</div>';

    // 2) Geld auf dem Konto
    $diffStock = $buy['total'] - $T['cogs'];   // mehr eingekauft als verkauft → liegt im Lager
    $diffAff = $T['comm'] - $paidAff;           // Provision verdient, aber noch nicht überwiesen
    echo '<div class="spm-card"><h2>2. Was davon wirklich auf dem Konto ist</h2><p class="spm-sub">Hier zählt jede Zahlung an dem Tag, an dem das Geld geflossen ist.</p>'
        . $line('', 'Eingenommen', 'Bezahlte Bestellungen im Zeitraum (wie oben).', $T['revenue'], 'top')
        . $line('−', 'Einkäufe bezahlt', count($buy['list']) . ' Lieferung(en) an Lieferanten, auch wenn die Ware noch unterwegs ist (eintragen unter Lager → Lieferungen).', $buy['total'], 'minus')
        . $line('−', 'An Partner überwiesen', 'Provisionen, die du im Zeitraum wirklich überwiesen hast.', $paidAff, 'minus')
        . $line('−', 'Versand &amp; Verpackung', 'DHL-Porto und Karton.', $T['own'], 'minus')
        . '<div class="spm-row spm-total' . ($cash < 0 ? ' neg' : '') . '"><div class="spm-sign">=</div><div class="spm-txt"><div class="spm-t">Geld übrig</div><div class="spm-w">So viel mehr Geld hast du am Ende des Zeitraums als am Anfang.</div></div><div class="spm-amt">' . $eu($cash) . '</div></div>'
        . '<div class="spm-note"><b>Warum ist das anders als der Gewinn?</b><br>'
        . ($diffStock >= 0 ? '• Du hast <b>' . $eu($diffStock) . '</b> mehr Ware eingekauft als verkauft – die liegt jetzt im Lager und bringt später Umsatz.<br>' : '• Du hast Ware für <b>' . $eu(-$diffStock) . '</b> verkauft, die schon früher bezahlt war.<br>')
        . ($diffAff >= 0 ? '• <b>' . $eu($diffAff) . '</b> Provision ist verdient, aber noch nicht überwiesen.' : '• Du hast <b>' . $eu(-$diffAff) . '</b> mehr an Partner überwiesen als im Zeitraum verdient wurde (alte Provisionen).')
        . '</div></div>';

    // 3) Lager
    if (function_exists('sp_lager_summary')) {
        $S = sp_lager_summary();
        $lst = 0; $real = 0; $rev90 = 0; $com90 = 0;
        foreach (wc_get_orders(['limit' => -1, 'status' => ['processing', 'completed'], 'date_paid' => '>' . (time() - 90 * DAY_IN_SECONDS)]) as $o) {
            if ($o->get_meta('_sp_is_test')) continue;
            $f = sp_ek_order_figures($o);
            if (!$f['goods']) continue;
            foreach ($f['lines'] as $l) { $pp = wc_get_product($l['vid'] ?: $l['pid']); if (!$pp) continue; $lst += (float) $pp->get_regular_price() * $l['qty']; $real += $l['rev']; }
            $rev90 += $f['revenue']; $com90 += $f['comm'];
        }
        $q = $lst > 0 ? min(1, $real / $lst) : 1;   // Anteil vom Listenpreis, der wirklich ankommt
        $cq = $rev90 > 0 ? $com90 / $rev90 : 0;     // Provisionsanteil am Umsatz
        $in1 = $S['vk'] * $q * (1 - $cq);
        $in2 = ($S['vk'] + $S['vk_in']) * $q * (1 - $cq);
        echo '<div class="spm-card"><h2>3. Wenn das Lager verkauft wird</h2><p class="spm-sub">Die Ware ist schon bezahlt – hier steht nur, was beim Verkauf noch reinkommt.</p><div class="spm-grid">'
            . '<div class="spm-box"><div class="l">Im Lager</div><div class="v">' . $eu($in1) . '</div><div class="s">kommt noch rein · Einkaufswert ' . $eu($S['val']) . '</div></div>'
            . '<div class="spm-box"><div class="l">+ Lieferungen unterwegs</div><div class="v">' . $eu($in2) . '</div><div class="s">kommt noch rein · Einkaufswert ' . $eu($S['val'] + $S['val_in']) . '</div></div>'
            . '<div class="spm-box hl"><div class="l">Geld übrig danach</div><div class="v">' . $eu($cash + $in2) . '</div><div class="s">Geld übrig (Punkt 2) + alles verkauft</div></div>'
            . '</div><div class="spm-note">Gerechnet mit deinen Erfahrungswerten der letzten 90 Tage: Von den Shop-Preisen kommen im Schnitt <b>' . number_format($q * 100, 0, ',', '.') . ' %</b> an (Mengenrabatt, Codes, Geschenke), davon gehen <b>' . number_format($cq * 100, 0, ',', '.') . ' %</b> als Provision weg. Versandkosten sind noch nicht abgezogen.'
            . ($S['no_vk'] ? ' Ohne Verkaufspreis nicht mitgerechnet: ' . esc_html(implode(', ', $S['no_vk'])) . '.' : '') . '</div></div>';
    }

    // Produkte
    echo '<h2>Nach Produkt</h2><table class="widefat striped" style="max-width:1100px"><thead><tr><th>Produkt</th><th style="text-align:right">Stück</th><th style="text-align:right">Umsatz</th><th style="text-align:right">Wareneinsatz</th><th style="text-align:right">Rohertrag</th><th style="text-align:right">Marge</th></tr></thead><tbody>';
    foreach ($P as $p) {
        $ro = $p['rev'] - $p['cogs'];
        echo '<tr><td>' . esc_html($p['name']) . ($p['gift'] ? ' <span style="color:#646970">(' . (int) $p['gift'] . '× Geschenk)</span>' : '') . ($p['miss'] ? ' <span style="color:#b32d2e">EK fehlt</span>' : '') . '</td><td style="text-align:right">' . (int) $p['qty'] . '</td><td style="text-align:right">' . sp_ek_money($p['rev']) . '</td><td style="text-align:right">' . sp_ek_money($p['cogs']) . '</td><td style="text-align:right"><strong>' . sp_ek_money($ro) . '</strong></td><td style="text-align:right">' . ($p['rev'] > 0 ? number_format($ro / $p['rev'] * 100, 0, ',', '.') . ' %' : '—') . '</td></tr>';
    }
    echo '</tbody></table>';

    // Bestellungen
    echo '<details style="margin-top:18px"><summary style="cursor:pointer;font-size:15px;font-weight:600">Einzelne Bestellungen (' . count($rows) . ')</summary><table class="widefat striped" style="max-width:1100px;margin-top:10px"><thead><tr><th>Bezahlt</th><th>Bestellung</th><th style="text-align:right">Umsatz</th><th style="text-align:right">EK</th><th style="text-align:right">Provision</th><th style="text-align:right">Versand/Verp.</th><th style="text-align:right">Ergebnis</th></tr></thead><tbody>';
    foreach (array_reverse($rows) as [$o, $f]) {
        $nr = $o->get_meta('_sp_order_number') ?: $o->get_id();
        echo '<tr><td>' . esc_html(($o->get_date_paid() ?: $o->get_date_created())->date_i18n('d.m.Y')) . '</td><td><a href="' . esc_url($o->get_edit_order_url()) . '">#' . esc_html($nr) . '</a>' . ($o->get_payment_method() === 'sp_wallet' ? ' <span style="color:#646970">Abo</span>' : '') . '</td><td style="text-align:right">' . sp_ek_money($f['revenue']) . '</td><td style="text-align:right">' . sp_ek_money($f['cogs']) . ($f['missing'] ? ' <span style="color:#b32d2e">*</span>' : '') . '</td><td style="text-align:right">' . sp_ek_money($f['comm']) . '</td><td style="text-align:right">' . sp_ek_money($f['own']) . '</td><td style="text-align:right"><strong>' . sp_ek_money($f['result']) . '</strong></td></tr>';
    }
    echo '</tbody></table></details>';

    // Einkaufspreise pflegen
    $items = sp_ek_items();
    echo '<h2 style="margin-top:28px">Einkaufspreise &amp; Kosten</h2><form method="post">';
    wp_nonce_field('sp_ek');
    echo '<p>Wechselkurs: 1 $ = <strong>' . esc_html(number_format(sp_ek_rate(), 4, ',', '.')) . ' €</strong> ' . (get_option('sp_ek_rate_manual') ? '(fest eingetragen)' : '(automatisch, Stand ' . esc_html(get_option('sp_ek_rate_date', '–')) . ')') . ' &nbsp; <label>Fester Kurs (leer = automatisch): <input name="rate_manual" value="' . esc_attr((string) get_option('sp_ek_rate_manual', '')) . '" style="width:90px"></label> <button class="button" name="sp_ek_rate_now" value="1">Kurs jetzt laden</button></p>';
    $c = sp_ek_costs();
    echo '<p><label>Aufschlag auf EK für Versand/Zoll beim Einkauf: <input name="costs[import_pct]" value="' . esc_attr($c['import_pct']) . '" style="width:70px"> %</label> &nbsp; <label>DHL je Paket Deutschland: <input name="costs[ship_order]" value="' . esc_attr($c['ship_order']) . '" style="width:70px"> €</label> &nbsp; <label>Österreich: <input name="costs[ship_order_at]" value="' . esc_attr($c['ship_order_at']) . '" style="width:70px"> €</label> &nbsp; <label>Verpackung je Paket: <input name="costs[pack_order]" value="' . esc_attr($c['pack_order']) . '" style="width:70px"> €</label></p>';
    echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>Produkt</th><th>Code</th><th>EK $ / Stück</th><th>oder EK € / Stück</th><th>+ € fest</th><th style="text-align:right">EK € (gerechnet)</th><th style="text-align:right">Preis</th><th style="text-align:right">Marge</th></tr></thead><tbody>';
    foreach (wc_get_products(['limit' => -1, 'status' => ['publish', 'private'], 'orderby' => 'title', 'order' => 'ASC']) as $p) {
        $list = $p->is_type('variable') ? array_map('wc_get_product', $p->get_children()) : [$p];
        foreach ($list as $v) {
            if (!$v || (int) $v->get_id() === 729) continue;
            $id = $v->get_id(); $e = $items[$id] ?? [];
            $name = $p->get_name() . ($v->get_id() !== $p->get_id() ? ' – ' . $v->get_attribute('volumen') : '');
            $eu = sp_ek_unit_eur($id); $pr = (float) $v->get_price();
            echo '<tr><td>' . esc_html($name) . (get_post_status($id) === 'private' ? ' <span style="color:#646970">(privat)</span>' : '') . '</td>'
                . '<td><input name="ek[' . $id . '][code]" value="' . esc_attr($e['code'] ?? '') . '" style="width:80px"></td>'
                . '<td><input name="ek[' . $id . '][usd]" value="' . esc_attr($e['usd'] ?? '') . '" style="width:80px" placeholder="$"></td>'
                . '<td><input name="ek[' . $id . '][eur]" value="' . esc_attr($e['eur'] ?? '') . '" style="width:80px" placeholder="€"></td>'
                . '<td><input name="ek[' . $id . '][add]" value="' . esc_attr(!empty($e['add']) ? $e['add'] : '') . '" style="width:70px" placeholder="€" title="Feste Zusatzteile, z. B. Pen + Kartusche"></td>'
                . '<td style="text-align:right">' . ($eu === null ? '<span style="color:#b32d2e">fehlt</span>' : sp_ek_money($eu)) . '</td>'
                . '<td style="text-align:right">' . sp_ek_money($pr) . '</td>'
                . '<td style="text-align:right">' . ($eu !== null && $pr > 0 ? number_format(($pr - $eu) / $pr * 100, 0, ',', '.') . ' %' : '—') . '</td></tr>';
        }
    }
    echo '</tbody></table><p><button class="button button-primary" name="sp_ek_save" value="1">Speichern</button></p></form></div>';
}
