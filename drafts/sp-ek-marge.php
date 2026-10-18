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

    echo '<div class="wrap"><h1>Umsatz &amp; Marge</h1>' . $note;
    echo '<form method="get" style="margin:8px 0 18px"><input type="hidden" name="page" value="sp-umsatz-marge">Von <input type="date" name="sp_start" value="' . esc_attr($range['start']) . '"> bis <input type="date" name="sp_end" value="' . esc_attr($range['end']) . '"> <button class="button">Anzeigen</button> ';
    $m0 = current_time('Y-m-01');
    foreach ([['Dieser Monat', $m0, current_time('Y-m-d')], ['Letzter Monat', date('Y-m-01', strtotime($m0 . ' -1 month')), date('Y-m-t', strtotime($m0 . ' -1 month'))], ['Seit Start', '2026-01-01', current_time('Y-m-d')]] as $q)
        echo '<a class="button-link" style="margin-left:10px" href="' . esc_url(add_query_arg(['page' => 'sp-umsatz-marge', 'sp_start' => $q[1], 'sp_end' => $q[2]], admin_url('admin.php'))) . '">' . esc_html($q[0]) . '</a>';
    echo '</form>';

    $pct = $T['revenue'] > 0 ? $T['result'] / $T['revenue'] * 100 : 0;
    $tile = function ($l, $v, $c = '#1d2327', $s = '') { return '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:14px 16px;min-width:170px"><div style="font-size:12px;color:#646970;text-transform:uppercase;letter-spacing:.04em">' . $l . '</div><div style="font-size:22px;font-weight:700;color:' . $c . ';margin-top:4px">' . $v . '</div>' . ($s ? '<div style="font-size:12px;color:#646970;margin-top:2px">' . $s . '</div>' : '') . '</div>'; };
    echo '<div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:10px">'
        . $tile('Umsatz (bezahlt)', sp_ek_money($T['revenue']), '#1d2327', (int) $T['n'] . ' Bestellungen · davon Versand ' . sp_ek_money($T['shipping_in']) . ($T['refunds'] ? ' · Erstattungen −' . sp_ek_money($T['refunds']) : ''))
        . $tile('− Wareneinsatz (EK)', sp_ek_money($T['cogs']), '#b32d2e', 'inkl. Gratis-Geschenke')
        . $tile('− Partner-Provisionen', sp_ek_money($T['comm']), '#b32d2e')
        . $tile('− Versand & Verpackung', sp_ek_money($T['own']), '#b32d2e', 'eigene Kosten je Paket')
        . $tile('= Ergebnis', sp_ek_money($T['result']), $T['result'] >= 0 ? '#00a32a' : '#b32d2e', number_format($pct, 1, ',', '.') . ' % vom Umsatz')
        . '</div>';
    echo '<p style="color:#646970;max-width:900px">Umsatz = bezahlte Bestellungen (auch mit Guthaben bezahlte Abo-Lieferungen) nach Rabatten, Partner-Rabatt und Erstattungen, ohne Guthaben-Aufladungen. Der Shop berechnet keine Umsatzsteuer, daher gibt es keinen Steuerabzug. Testbestellungen sind ausgenommen.'
        . ($T['missing'] ? ' <strong style="color:#b32d2e">' . (int) $T['missing'] . ' verkaufte Artikel haben noch keinen EK – das Ergebnis ist deshalb zu hoch.</strong>' : '')
        . ($T['estimated'] ? ' ' . (int) $T['estimated'] . ' Artikel mit aktuellem EK gerechnet (Bestellungen von vor der Erfassung).' : '') . '</p>';

    // Geldfluss: was wirklich rein- und rausging (Einkauf zählt beim Bezahlen, nicht erst beim Verkauf)
    $buy = function_exists('sp_lager_purchases') ? sp_lager_purchases($range['start'], $range['end']) : ['total' => 0, 'list' => []];
    $paidAff = 0;
    foreach ((array) get_option('sp_aff_transfers', []) as $t) if ($t['date'] >= $range['start'] && $t['date'] <= $range['end']) $paidAff += (float) $t['amount'];
    $cash = $T['revenue'] - $buy['total'] - $paidAff - $T['own'];
    echo '<h2 style="margin-top:22px">Geldfluss im Zeitraum</h2><div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:6px">'
        . $tile('Eingenommen', sp_ek_money($T['revenue']))
        . $tile('− Einkäufe bezahlt', sp_ek_money($buy['total']), '#b32d2e', count($buy['list']) . ' Lieferung(en), auch wenn noch unterwegs')
        . $tile('− An Partner überwiesen', sp_ek_money($paidAff), '#b32d2e')
        . $tile('− Versand & Verpackung', sp_ek_money($T['own']), '#b32d2e')
        . $tile('= Geld übrig', sp_ek_money($cash), $cash >= 0 ? '#00a32a' : '#b32d2e')
        . '</div><p style="color:#646970;max-width:900px">Oben (Ergebnis) zählt die Ware erst, wenn sie verkauft ist – so siehst du, was jeder Verkauf wirklich verdient. Hier (Geldfluss) zählt jeder Einkauf an dem Tag, an dem du ihn bezahlt hast, und Provisionen erst, wenn du sie überwiesen hast. Einkäufe trägst du unter <a href="' . esc_url(admin_url('admin.php?page=sp-lager')) . '">Lager → Lieferungen</a> ein; „Angekommen“ bucht dort nur den Bestand ein.</p>';

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
