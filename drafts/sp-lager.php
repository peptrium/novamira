<?php
/**
 * Plugin Name: SP Lager
 * Description: Lagerbestand auf Material-Ebene (Flaschen, Bac Water, Pen-Teile, Spritzen …) – Peptrium Dashboard → Lager.
 * Bestand wird beim Versand (Status "Abgeschlossen") automatisch abgebucht, bei Storno/Erstattung zurückgebucht.
 * Bezahlte, noch nicht versendete Bestellungen = "reserviert". Lieferungen werden als "unterwegs" geführt und per Klick eingebucht.
 * 2026-10-18.
 *
 * Optionen: sp_lager_mats [code => name, usd|eur, qty (null = nicht erfasst)], sp_lager_in (Lieferungen),
 * sp_lager_since (erst Bestellungen, die danach versendet werden, buchen ab), sp_lager_log.
 */

if (!defined('ABSPATH')) exit;

function sp_lager_mats() { return (array) get_option('sp_lager_mats', []); }

/** Was ein verkauftes Stück an Material verbraucht (Produkt- oder Varianten-ID). */
function sp_lager_recipes() {
    $pen = ['BAC' => 1, 'PEN' => 1, 'KART' => 1];
    return [
        555 => ['RT10' => 1], 556 => ['RT20' => 1],
        1011 => ['CU50' => 1], 1012 => ['CU100' => 1],
        71 => ['MS10' => 1], 77 => ['CP10' => 1], 428 => ['BC10' => 1],
        577 => ['BT10' => 1], 578 => ['BT5' => 1], 434 => ['XA10' => 1], 437 => ['SK10' => 1],
        515 => ['KP10' => 1], 512 => ['TSM10' => 1], 521 => ['IG1' => 1],
        74 => ['BAC' => 1], 80 => ['SPR' => 10], 908 => ['NAD' => 15],
        745 => ['SPR' => 10, 'BAC' => 1, 'TUPF' => 10],
        547 => ['RT20' => 1] + $pen, 548 => ['RT30' => 1] + $pen, 549 => ['RT40' => 1] + $pen,
        597 => ['CU50' => 1] + $pen, 598 => ['CU100' => 1] + $pen,
        544 => ['MS20' => 1] + $pen, 545 => ['MS40' => 1] + $pen,
    ];
}

/** Materialverbrauch einer Bestellposition [code => Menge]. */
function sp_lager_item_use($item) {
    if ($item->get_meta('_sp_wallet_amount')) return [];
    $r = sp_lager_recipes();
    $id = $item->get_variation_id() ?: $item->get_product_id();
    if (!isset($r[$id]) && !$item->get_variation_id() && function_exists('sp_bbv_legacy_variation') && ($lv = sp_bbv_legacy_variation($id)) && !empty($lv[0])) $id = (int) $lv[0];
    if (!isset($r[$id])) return [];
    $out = [];
    foreach ($r[$id] as $c => $n) $out[$c] = $n * (int) $item->get_quantity();
    return $out;
}

function sp_lager_order_use($order) {
    $out = [];
    foreach ($order->get_items() as $item) foreach (sp_lager_item_use($item) as $c => $n) $out[$c] = ($out[$c] ?? 0) + $n;
    return $out;
}

function sp_lager_log($text) {
    $log = (array) get_option('sp_lager_log', []);
    array_unshift($log, [current_time('mysql'), $text]);
    update_option('sp_lager_log', array_slice($log, 0, 200), false);
}

/** Bestand ändern (nur erfasste Materialien). $sign = -1 abbuchen, +1 zubuchen. */
function sp_lager_apply($use, $sign) {
    $m = sp_lager_mats(); $done = [];
    foreach ($use as $c => $n) {
        if (!isset($m[$c]) || $m[$c]['qty'] === null) continue;
        $m[$c]['qty'] += $sign * $n;
        $done[] = $c . ' ' . ($sign > 0 ? '+' : '−') . $n;
    }
    if ($done) update_option('sp_lager_mats', $m, false);
    return $done;
}

/* Versand = abbuchen, Storno/Erstattung nach Versand = zurückbuchen */
add_action('woocommerce_order_status_changed', function ($id, $from, $to) {
    $order = wc_get_order($id);
    if (!$order || $order->get_meta('_sp_is_test')) return;
    if ($to === 'completed' && !$order->get_meta('_sp_lager_out')) {
        if (time() < (int) get_option('sp_lager_since', PHP_INT_MAX)) return;
        $done = sp_lager_apply(sp_lager_order_use($order), -1);
        $order->update_meta_data('_sp_lager_out', 1); $order->save();
        if ($done) sp_lager_log('Versand #' . ($order->get_meta('_sp_order_number') ?: $id) . ': ' . implode(', ', $done));
    } elseif (in_array($to, ['cancelled', 'refunded', 'failed'], true) && $order->get_meta('_sp_lager_out')) {
        $done = sp_lager_apply(sp_lager_order_use($order), 1);
        $order->delete_meta_data('_sp_lager_out'); $order->save();
        if ($done) sp_lager_log('Rückbuchung #' . ($order->get_meta('_sp_order_number') ?: $id) . ' (' . $to . '): ' . implode(', ', $done));
    }
}, 30, 3);

/** EK eines Materials in € je Stück. */
function sp_lager_unit_eur($m) {
    if (isset($m['eur']) && $m['eur'] !== null && $m['eur'] !== '') return (float) $m['eur'];
    if (isset($m['usd']) && $m['usd'] !== null && $m['usd'] !== '') return (float) $m['usd'] * (function_exists('sp_ek_rate') ? sp_ek_rate() : 0.89);
    return 0.0;
}

/** Summen über Bestellungen [code => Menge]. */
function sp_lager_sum_orders($args) {
    $out = [];
    foreach (wc_get_orders($args + ['limit' => -1]) as $o) {
        if ($o->get_meta('_sp_is_test')) continue;
        foreach (sp_lager_order_use($o) as $c => $n) $out[$c] = ($out[$c] ?? 0) + $n;
    }
    return $out;
}

/** Bezahlter Betrag einer Lieferung in € (eingetragen, sonst aus den EK-Preisen gerechnet). */
function sp_lager_paid($d, $m = null) {
    if (isset($d['paid']) && $d['paid'] !== null && $d['paid'] !== '') return (float) $d['paid'];
    $m = $m ?: sp_lager_mats(); $sum = 0;
    foreach ($d['items'] as $c => $n) $sum += sp_lager_unit_eur($m[$c] ?? []) * $n;
    return round($sum, 2);
}

/** Einkäufe, die im Zeitraum bezahlt wurden (Bestelldatum = Zahlungsdatum): ['total' => €, 'list' => [...]]. */
function sp_lager_purchases($start, $end) {
    $m = sp_lager_mats(); $tot = 0; $list = [];
    foreach ((array) get_option('sp_lager_in', []) as $d) {
        if ($d['date'] < $start || $d['date'] > $end) continue;
        $v = sp_lager_paid($d, $m); $tot += $v; $list[] = $d + ['paid_eur' => $v];
    }
    return ['total' => $tot, 'list' => $list];
}

/** Verkaufspreis je Material: eingetragener VK, sonst Shop-Preis des Produkts, das genau dieses Material als Einzelstück verkauft. */
function sp_lager_vk($code, $m = null) {
    $m = $m ?: sp_lager_mats();
    if (isset($m[$code]['vk']) && $m[$code]['vk'] !== null && $m[$code]['vk'] !== '') return (float) $m[$code]['vk'];
    foreach (sp_lager_recipes() as $pid => $rec) {
        if ($rec !== [$code => 1]) continue;
        $p = wc_get_product($pid);
        if ($p && get_post_status($pid) === 'publish' && (float) $p->get_price() > 0) return (float) $p->get_price();
    }
    return null;
}

/** Lagerwert und möglicher Umsatz (Listenpreis) für Bestand und offene Lieferungen. */
function sp_lager_summary() {
    $m = sp_lager_mats(); $coming = [];
    foreach ((array) get_option('sp_lager_in', []) as $d) if (empty($d['done'])) foreach ($d['items'] as $c => $n) $coming[$c] = ($coming[$c] ?? 0) + $n;
    $o = ['val' => 0, 'val_in' => 0, 'vk' => 0, 'vk_in' => 0, 'no_vk' => []];
    foreach ($m as $c => $x) {
        $u = sp_lager_unit_eur($x); $q = ($x['qty'] !== null && $x['qty'] > 0) ? $x['qty'] : 0; $qi = $coming[$c] ?? 0;
        $o['val'] += $u * $q; $o['val_in'] += $u * $qi;
        if (!$q && !$qi) continue;
        $vk = sp_lager_vk($c, $m);
        if ($vk === null) { $o['no_vk'][] = $c; continue; }
        $o['vk'] += $vk * $q; $o['vk_in'] += $vk * $qi;
    }
    return $o;
}

function sp_lager_parse_lines($txt) {
    $items = [];
    foreach (preg_split('~[\r\n,;]+~', (string) $txt) as $l) {
        if (preg_match('~^\s*([A-Za-z0-9]+)\s*[x×:= ]\s*(-?\d+)\s*$~u', $l, $mm)) $items[strtoupper($mm[1])] = ($items[strtoupper($mm[1])] ?? 0) + (int) $mm[2];
    }
    return $items;
}

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Lager', 'Lager', 'manage_woocommerce', 'sp-lager', 'sp_lager_render_page');
}, 26);

function sp_lager_render_page() {
    if (!current_user_can('manage_woocommerce')) return;
    $note = '';
    $m = sp_lager_mats();
    $in = (array) get_option('sp_lager_in', []);
    if (!empty($_POST['sp_lager_act']) && check_admin_referer('sp_lager')) {
        $act = sanitize_key($_POST['sp_lager_act']);
        if ($act === 'inventur') {
            $ch = [];
            foreach ((array) ($_POST['qty'] ?? []) as $c => $v) {
                $c = strtoupper(sanitize_key($c)); if (!isset($m[$c])) continue;
                $v = trim((string) wp_unslash($v));
                $new = $v === '' ? null : (int) $v;
                if ($new !== $m[$c]['qty']) { $ch[] = $c . ' ' . ($m[$c]['qty'] === null ? '–' : $m[$c]['qty']) . '→' . ($new === null ? '–' : $new); $m[$c]['qty'] = $new; }
                $usd = trim(str_replace(',', '.', (string) wp_unslash($_POST['usd'][$c] ?? '')));
                $eur = trim(str_replace(',', '.', (string) wp_unslash($_POST['eur'][$c] ?? '')));
                $m[$c]['usd'] = $usd === '' ? null : (float) $usd;
                $m[$c]['eur'] = $eur === '' ? null : (float) $eur;
                $vk = trim(str_replace(',', '.', (string) wp_unslash($_POST['vk'][$c] ?? '')));
                $m[$c]['vk'] = $vk === '' ? null : (float) $vk;
            }
            update_option('sp_lager_mats', $m, false);
            if ($ch) sp_lager_log('Inventur: ' . implode(', ', $ch));
            $note = 'Bestand gespeichert ✓';
        } elseif ($act === 'neu') {
            $items = sp_lager_parse_lines(wp_unslash($_POST['items'] ?? ''));
            $unknown = array_diff(array_keys($items), array_keys($m));
            if (!$items) $note = 'Keine Positionen erkannt (Format: CODE Menge, eine pro Zeile).';
            elseif ($unknown) $note = 'Unbekannte Codes: ' . implode(', ', $unknown);
            else {
                $pd = trim(str_replace(['.', ','], ['', '.'], (string) wp_unslash($_POST['paid'] ?? '')));
                $new = ['id' => uniqid(), 'date' => sanitize_text_field(wp_unslash($_POST['date'] ?? current_time('Y-m-d'))), 'label' => sanitize_text_field(wp_unslash($_POST['label'] ?? '')), 'items' => $items, 'done' => ''];
                $new['paid'] = $pd === '' ? sp_lager_paid($new, $m) : (float) $pd; // Betrag beim Bestellen festschreiben
                $in[] = $new;
                update_option('sp_lager_in', $in, false);
                $note = 'Lieferung eingetragen ✓';
            }
        } elseif ($act === 'bezahlt') {
            $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
            $pd = trim(str_replace(['.', ','], ['', '.'], (string) wp_unslash($_POST['paid'] ?? '')));
            foreach ($in as $k => $d) if ($d['id'] === $id && $pd !== '') { $in[$k]['paid'] = (float) $pd; $note = 'Betrag gespeichert ✓'; sp_lager_log('Einkauf „' . $d['label'] . '“: bezahlt ' . $pd . ' €'); }
            update_option('sp_lager_in', $in, false);
        } elseif ($act === 'eingang' || $act === 'loeschen') {
            $id = sanitize_text_field(wp_unslash($_POST['id'] ?? ''));
            foreach ($in as $k => $d) {
                if ($d['id'] !== $id || $d['done']) continue;
                if ($act === 'eingang') {
                    foreach ($d['items'] as $c => $n) if (isset($m[$c])) $m[$c]['qty'] = (int) $m[$c]['qty'] + $n;
                    update_option('sp_lager_mats', $m, false);
                    $in[$k]['done'] = current_time('Y-m-d');
                    sp_lager_log('Eingang „' . $d['label'] . '“: ' . implode(', ', array_map(function ($c, $n) { return "$c +$n"; }, array_keys($d['items']), $d['items'])));
                    $note = 'Lieferung eingebucht ✓';
                } else { unset($in[$k]); $note = 'Lieferung gelöscht'; }
                update_option('sp_lager_in', array_values($in), false);
            }
        }
    }

    // Kennzahlen
    $reserved = sp_lager_sum_orders(['status' => ['processing']]);
    $sold30 = sp_lager_sum_orders(['status' => ['processing', 'completed'], 'date_paid' => '>' . (time() - 30 * DAY_IN_SECONDS)]);
    $coming = [];
    foreach ($in as $d) if (!$d['done']) foreach ($d['items'] as $c => $n) $coming[$c] = ($coming[$c] ?? 0) + $n;

    // Warnungen: im Shop lieferbar, aber Material leer
    $warn = [];
    foreach (sp_lager_recipes() as $pid => $rec) {
        $p = wc_get_product($pid);
        if (!$p || get_post_status($pid) !== 'publish' || !$p->is_in_stock() || in_array($pid, function_exists('sp_preorder_product_ids') ? array_merge(...array_map(function ($x) { $q = wc_get_product($x); return $q ? array_merge([$x], $q->get_children()) : [$x]; }, sp_preorder_product_ids())) : [], true)) continue;
        $miss = [];
        foreach ($rec as $c => $n) {
            if (!isset($m[$c]) || $m[$c]['qty'] === null) continue;
            if ($m[$c]['qty'] - ($reserved[$c] ?? 0) < $n) $miss[] = $c . ($coming[$c] ?? 0 ? ' (' . $coming[$c] . ' unterwegs)' : '');
        }
        if ($miss) $warn[] = esc_html($p->get_name()) . ' – fehlt: ' . esc_html(implode(', ', $miss));
    }

    echo '<div class="wrap"><h1>Lager</h1>';
    if ($note) echo '<div class="notice notice-info"><p>' . esc_html($note) . '</p></div>';
    if ($warn) echo '<div class="notice notice-warning"><p><strong>Im Shop als lieferbar angezeigt, aber kein Bestand:</strong><br>' . implode('<br>', $warn) . '</p></div>';

    $S = sp_lager_summary();
    $val = $S['val']; $valIn = $S['val_in']; $vkL = $S['vk']; $vkIn = $S['vk_in']; $noVk = $S['no_vk'];
    $eu = function ($v) { return esc_html(number_format($v, 2, ',', '.')) . ' €'; };
    $tile = function ($l, $v, $sub = '', $col = '#1d2327') { return '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:12px 16px;min-width:190px"><div style="font-size:12px;color:#646970;text-transform:uppercase;letter-spacing:.04em">' . $l . '</div><div style="font-size:21px;font-weight:700;color:' . $col . ';margin-top:3px">' . $v . '</div>' . ($sub ? '<div style="font-size:12px;color:#646970;margin-top:2px">' . $sub . '</div>' : '') . '</div>'; };
    echo '<div style="display:flex;flex-wrap:wrap;gap:12px;margin:10px 0 6px">'
        . $tile('Lagerwert (EK)', $eu($val), 'unterwegs: ' . $eu($valIn))
        . $tile('Umsatz bei Abverkauf – Lager', $eu($vkL), 'Rohertrag ' . $eu($vkL - $val), '#00a32a')
        . $tile('… inkl. unterwegs', $eu($vkL + $vkIn), 'Rohertrag ' . $eu($vkL + $vkIn - $val - $valIn), '#00a32a')
        . '</div>';
    echo '<p style="color:#646970;max-width:1000px">„Umsatz bei Abverkauf“ = jede Flasche zum normalen Shop-Preis verkauft (Spalte VK; Reta 20 mg als Flasche, nicht als Pen). Ohne Mengenrabatt, Partner-Rabatt, Codes und Geschenke – Bac Water geht teils gratis raus, der echte Umsatz liegt also darunter. Provisionen und Versand sind nicht abgezogen.'
        . ($noVk ? ' <strong>Ohne VK (nicht mitgerechnet): ' . esc_html(implode(', ', $noVk)) . '</strong> – Preis in der Spalte VK eintragen.' : '')
        . ' Bestand gezählt am ' . esc_html(date_i18n('d.m.Y H:i', (int) get_option('sp_lager_since'))) . '; versendete Bestellungen werden automatisch abgebucht.</p>';

    echo '<form method="post">'; wp_nonce_field('sp_lager');
    echo '<input type="hidden" name="sp_lager_act" value="inventur"><table class="widefat striped" style="max-width:1150px"><thead><tr><th>Code</th><th>Material</th><th style="text-align:right">Bestand</th><th style="text-align:right">reserviert</th><th style="text-align:right">verfügbar</th><th style="text-align:right">unterwegs</th><th style="text-align:right">Verkauf 30 Tage</th><th style="text-align:right">reicht noch</th><th>EK $</th><th>EK €</th><th>VK €</th></tr></thead><tbody>';
    foreach ($m as $c => $x) {
        $tr = $x['qty'] !== null;
        $av = $tr ? $x['qty'] - ($reserved[$c] ?? 0) : null;
        $per = ($sold30[$c] ?? 0) / 30;
        $days = $tr && $per > 0 ? floor(max(0, $av) / $per) : null;
        $col = !$tr ? '#646970' : ($av <= 0 ? '#b32d2e' : ($days !== null && $days < 21 ? '#996800' : '#00a32a'));
        $reach = !$tr ? 'nicht erfasst' : ($av <= 0 ? 'leer' : ($days === null ? '–' : ($days > 365 ? '> 1 Jahr' : $days . ' Tage')));
        echo '<tr><td><code>' . esc_html($c) . '</code></td><td>' . esc_html($x['name']) . '</td>'
            . '<td style="text-align:right"><input name="qty[' . esc_attr($c) . ']" value="' . esc_attr($tr ? $x['qty'] : '') . '" style="width:70px;text-align:right" placeholder="–"></td>'
            . '<td style="text-align:right">' . (int) ($reserved[$c] ?? 0) . '</td>'
            . '<td style="text-align:right;font-weight:600;color:' . $col . '">' . ($tr ? (int) $av : '–') . '</td>'
            . '<td style="text-align:right">' . (($coming[$c] ?? 0) ? '+' . (int) $coming[$c] : '') . '</td>'
            . '<td style="text-align:right">' . (int) ($sold30[$c] ?? 0) . '</td>'
            . '<td style="text-align:right;color:' . $col . '">' . esc_html($reach) . '</td>'
            . '<td><input name="usd[' . esc_attr($c) . ']" value="' . esc_attr($x['usd'] ?? '') . '" style="width:70px"></td>'
            . '<td><input name="eur[' . esc_attr($c) . ']" value="' . esc_attr($x['eur'] ?? '') . '" style="width:70px"></td>'
            . '<td><input name="vk[' . esc_attr($c) . ']" value="' . esc_attr($x['vk'] ?? '') . '" style="width:70px" placeholder="' . esc_attr(($v = sp_lager_vk($c, array_merge($m, [$c => array_merge($x, ['vk' => null])]))) !== null ? number_format($v, 2, ',', '') : '–') . '" title="leer = Shop-Preis"></td></tr>';
    }
    echo '</tbody></table><p style="color:#646970">Bestand leer lassen = nicht erfasst. „reserviert“ = bezahlt, noch nicht versendet. „reicht noch“ = verfügbar ÷ Verkauf der letzten 30 Tage (rot = leer, gelb = unter 3 Wochen). Pens verbrauchen je 1 Flasche + 1 Bac Water + Pen + Kartusche.</p>'
        . '<p><button class="button button-primary">Bestand speichern</button></p></form>';

    // Lieferungen
    echo '<h2 style="margin-top:26px">Lieferungen unterwegs</h2>';
    $open = array_filter($in, function ($d) { return !$d['done']; });
    if (!$open) echo '<p>Keine offenen Lieferungen.</p>';
    foreach ($open as $d) {
        echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:12px 14px;margin:8px 0;max-width:1120px"><strong>' . esc_html($d['label'] ?: 'Lieferung') . '</strong> <span style="color:#646970">bestellt &amp; bezahlt am ' . esc_html(date_i18n('d.m.Y', strtotime($d['date']))) . '</span><br>'
            . esc_html(implode(' · ', array_map(function ($c, $n) use ($m) { return $n . '× ' . ($m[$c]['name'] ?? $c); }, array_keys($d['items']), $d['items'])))
            . '<form method="post" style="margin-top:8px">' . wp_nonce_field('sp_lager', '_wpnonce', true, false) . '<input type="hidden" name="id" value="' . esc_attr($d['id']) . '">'
            . '<span style="margin-right:14px">Bezahlt: <input name="paid" value="' . esc_attr(number_format(sp_lager_paid($d, $m), 2, ',', '')) . '" style="width:90px;text-align:right"> € <button class="button" name="sp_lager_act" value="bezahlt">Betrag speichern</button></span> '
            . '<button class="button button-primary" name="sp_lager_act" value="eingang" onclick="return confirm(\'Ware angekommen? Mengen werden zum Bestand addiert.\')">✓ Angekommen – einbuchen</button> '
            . '<button class="button-link" style="color:#b32d2e;margin-left:10px" name="sp_lager_act" value="loeschen" onclick="return confirm(\'Lieferung löschen?\')">Löschen</button></form></div>';
    }
    $done = array_filter($in, function ($d) { return $d['done']; });
    if ($done) {
        echo '<details style="margin-top:10px"><summary style="cursor:pointer;font-weight:600">Angekommene Einkäufe (' . count($done) . ')</summary><table class="widefat striped" style="max-width:1120px;margin-top:8px"><thead><tr><th>Bezahlt</th><th>Lieferung</th><th>Inhalt</th><th style="text-align:right">Betrag</th><th>Angekommen</th></tr></thead><tbody>';
        foreach (array_reverse($done) as $d) echo '<tr><td>' . esc_html(date_i18n('d.m.Y', strtotime($d['date']))) . '</td><td>' . esc_html($d['label']) . '</td><td>' . esc_html(implode(', ', array_map(function ($c, $n) { return "$n× $c"; }, array_keys($d['items']), $d['items']))) . '</td><td style="text-align:right">' . esc_html(number_format(sp_lager_paid($d, $m), 2, ',', '.')) . ' €</td><td>' . esc_html(date_i18n('d.m.Y', strtotime($d['done']))) . '</td></tr>';
        echo '</tbody></table></details>';
    }
    echo '<details style="margin-top:10px"><summary style="cursor:pointer;font-weight:600">+ Neue Lieferung eintragen</summary><form method="post" style="margin-top:8px">' . wp_nonce_field('sp_lager', '_wpnonce', true, false)
        . '<input type="hidden" name="sp_lager_act" value="neu"><p><label>Bezeichnung <input name="label" style="width:260px" placeholder="z. B. Peptid-Lieferant"></label> <label>bestellt &amp; bezahlt am <input type="date" name="date" value="' . esc_attr(current_time('Y-m-d')) . '"></label> <label>bezahlt <input name="paid" style="width:90px" placeholder="leer = berechnet"> €</label></p>'
        . '<p><textarea name="items" rows="6" style="width:360px" placeholder="RT10 40&#10;RT20 30&#10;BAC 20"></textarea><br><span style="color:#646970">Eine Zeile pro Position: Code + Menge (Codes siehe Tabelle oben).</span></p><p><button class="button">Lieferung speichern</button></p></form></details>';

    // Protokoll
    $log = (array) get_option('sp_lager_log', []);
    echo '<details style="margin-top:22px"><summary style="cursor:pointer;font-weight:600">Protokoll (' . count($log) . ')</summary><table class="widefat striped" style="max-width:1120px;margin-top:8px"><tbody>';
    foreach (array_slice($log, 0, 100) as $l) echo '<tr><td style="width:150px">' . esc_html(date_i18n('d.m.Y H:i', strtotime($l[0]))) . '</td><td>' . esc_html($l[1]) . '</td></tr>';
    echo '</tbody></table></details></div>';
}
