<?php
/**
 * Peptrium Dashboard → Monatsbericht (2026-11-01)
 * Monatsauswertung nach Zahlungseingang: Umsatz, Gewinn (vor/nach Fixkosten), Geldfluss, Partner,
 * Produkte, Kunden, Rabatte, Verlauf. Vorschau im Admin, Versand per Mail (wie die Versandliste),
 * optional automatisch am 1. des Monats für den Vormonat.
 * Optionen: sp_mb_fix [Y-m => [amount, note]], sp_mb_last_email, sp_mb_auto {on, to}, sp_mb_sent [Y-m => ts].
 * Braucht: sp_ek_order_figures() (sp-ek-marge.php), sp_lager_purchases() (sp-lager.php, optional).
 */
if (!defined('ABSPATH')) exit;

function sp_mb_tz() { return new DateTimeZone('Europe/Berlin'); }
function sp_mb_range($ym) {
    $s = new DateTime($ym . '-01 00:00', sp_mb_tz());
    $e = (clone $s)->modify('+1 month');
    return [$s->getTimestamp(), $e->getTimestamp(), $s, $e];
}
function sp_mb_prev($ym) { return (new DateTime($ym . '-01', sp_mb_tz()))->modify('-1 month')->format('Y-m'); }
function sp_mb_month_name($ym) {
    $n = ['', 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    [$y, $m] = explode('-', $ym);
    return $n[(int) $m] . ' ' . $y;
}
function sp_mb_short_month($ym) {
    $n = ['', 'Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Juni', 'Juli', 'Aug.', 'Sep.', 'Okt.', 'Nov.', 'Dez.'];
    return $n[(int) substr($ym, 5, 2)];
}

/** Produktname kurz + Größe, z. B. "Retatrutide 10 mg", "Reta-Pen 40 mg". */
function sp_mb_product_label($pid, $vid) {
    if (!$vid && function_exists('sp_bbv_legacy_variation')) { $l = sp_bbv_legacy_variation($pid); if ($l) $vid = is_array($l) ? (int) $l[0] : (int) $l; }
    $p = wc_get_product($vid ?: $pid);
    $parent = wc_get_product($pid);
    $name = $parent ? $parent->get_name() : ($p ? $p->get_name() : 'Produkt ' . $pid);
    $name = preg_replace('/^Peptrium-Pen \(Retatrutide\)$/u', 'Reta-Pen', $name);
    $name = preg_replace('/^Peptrium-Pen \((.+)\)$/u', '$1-Pen', $name);
    $name = preg_replace('/\s*\(\d+er Pack\)|\s*\d+er Pack|\s*32G x 4mm/u', '', $name);
    $name = trim($name);
    $vol = '';
    if ($p && $p->is_type('variation')) {
        $vol = implode(' ', array_filter($p->get_attributes()));
    } elseif ($p) {
        $vol = $p->get_attribute('volumen');
        if (strpos($vol, '|') !== false || strpos($vol, ',') !== false) $vol = '';
    }
    $vol = preg_replace('/(\d)\s*mg/i', '$1 mg', $vol);
    if ($vol && stripos($name, $vol) === false) $name .= ' ' . $vol;
    return $name;
}

function sp_mb_data($ym) {
    global $wpdb;
    [$s, $e] = sp_mb_range($ym);
    $R = ['ym' => $ym, 'orders' => 0, 'rev' => 0.0, 'ship_in' => 0.0, 'cogs' => 0.0, 'comm' => 0.0, 'own' => 0.0, 'res' => 0.0,
          'new' => 0, 'ret' => 0, 'gifts' => 0, 'gift_value' => 0.0, 'gift_cogs' => 0.0, 'prod' => [], 'aff' => [], 'country' => [],
          'days' => [], 'coupons' => [], 'hw_free' => 0, 'hw_save' => 0.0, 'hw_rev' => 0.0, 'max' => 0.0, 'pay' => []];
    $ids = wc_get_orders(['limit' => -1, 'return' => 'ids', 'type' => 'shop_order',
        'status' => ['processing', 'completed', 'refunded'], 'date_paid' => $s . '...' . ($e - 1)]);
    foreach ($ids as $id) {
        $o = wc_get_order($id);
        if (!$o || $o->get_meta('_sp_is_test') || !$o->get_date_paid()) continue;
        $f = sp_ek_order_figures($o);
        if (!$f['goods']) continue;
        $R['orders']++;
        foreach (['rev' => 'revenue', 'ship_in' => 'shipping_in', 'cogs' => 'cogs', 'comm' => 'comm', 'own' => 'own', 'res' => 'result'] as $k => $fk) $R[$k] += $f[$fk];
        $R['max'] = max($R['max'], $f['revenue']);
        $pm = $o->get_payment_method_title() ?: $o->get_payment_method();
        $R['pay'][$pm] = ($R['pay'][$pm] ?? 0) + 1;
        $c = $o->get_shipping_country() ?: $o->get_billing_country();
        $R['country'][$c] = ($R['country'][$c] ?? 0) + 1;
        $d = (int) wp_date('j', $o->get_date_paid()->getTimestamp());
        $R['days'][$d] = ($R['days'][$d] ?? 0) + $f['revenue'];
        foreach ($o->get_items('coupon') as $cc) {
            $k = strtoupper($cc->get_code());
            if (strpos($k, 'HALLO-') === 0) $k = 'HALLO';
            elseif (strpos($k, 'COMEBACK-') === 0) $k = 'COMEBACK';
            elseif (strpos($k, 'DANKE-') === 0) $k = 'DANKE';
            elseif (strpos($k, 'FREUND-') === 0) $k = 'FREUND';
            $R['coupons'][$k] = $R['coupons'][$k] ?? [0, 0.0];
            $R['coupons'][$k][0]++; $R['coupons'][$k][1] += (float) $cc->get_discount();
        }
        foreach ($o->get_fees() as $fe) if ((float) $fe->get_total() < 0) {
            $R['coupons']['EMPF'] = $R['coupons']['EMPF'] ?? [0, 0.0];
            $R['coupons']['EMPF'][0]++; $R['coupons']['EMPF'][1] += -(float) $fe->get_total();
        }
        if ($o->get_meta('_sp_hw_phase') !== '' || (int) $o->get_meta('_sp_hw') > 0) $R['hw_rev'] += $f['revenue'];
        $R['hw_free'] += (int) $o->get_meta('_sp_hw');
        foreach ($o->get_items() as $it) if ($it->get_meta('Halloween-Angebot')) $R['hw_save'] += (float) $it->get_subtotal() - (float) $it->get_total();
        foreach ($f['lines'] as $l) {
            $vid = $l['vid'];
            $p = wc_get_product($vid ?: $l['pid']);
            if ($l['gift']) {
                $R['gifts'] += $l['qty'];
                $R['gift_value'] += ($p ? (float) $p->get_regular_price() : 0) * $l['qty'];
                $R['gift_cogs'] += (float) $l['cogs'];
                continue;
            }
            $key = sp_mb_product_label($l['pid'], $vid);
            $R['prod'][$key] = $R['prod'][$key] ?? ['q' => 0, 'rev' => 0.0, 'cogs' => 0.0];
            $R['prod'][$key]['q'] += $l['qty']; $R['prod'][$key]['rev'] += $l['rev']; $R['prod'][$key]['cogs'] += (float) $l['cogs'];
        }
        $em = strtolower($o->get_billing_email());
        $prev = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders WHERE LOWER(billing_email)=%s AND id<>%d AND date_created_gmt<%s AND status IN ('wc-processing','wc-completed')",
            $em, $id, gmdate('Y-m-d H:i:s', $o->get_date_created()->getTimestamp())));
        if ($prev) $R['ret']++; else $R['new']++;
        $cm = $wpdb->get_results($wpdb->prepare("SELECT affiliate_id a, SUM(amount) s FROM {$wpdb->prefix}slicewp_commissions WHERE reference=%s AND status<>'rejected' GROUP BY affiliate_id", (string) $id));
        if ($cm) foreach ($cm as $x) {
            $R['aff'][$x->a] = $R['aff'][$x->a] ?? ['n' => 0, 'rev' => 0.0, 'comm' => 0.0];
            $R['aff'][$x->a]['n']++; $R['aff'][$x->a]['rev'] += $f['revenue']; $R['aff'][$x->a]['comm'] += (float) $x->s;
        } else {
            $R['aff'][0] = $R['aff'][0] ?? ['n' => 0, 'rev' => 0.0, 'comm' => 0.0];
            $R['aff'][0]['n']++; $R['aff'][0]['rev'] += $f['revenue'];
        }
    }
    // Offen / storniert: im Monat bestellt, (noch) nicht bezahlt
    $R['open_n'] = 0; $R['open_rev'] = 0.0; $R['canc'] = 0;
    foreach (wc_get_orders(['limit' => -1, 'type' => 'shop_order', 'status' => ['on-hold', 'pending', 'cancelled', 'failed'], 'date_created' => $s . '...' . ($e - 1)]) as $o) {
        if ($o->get_meta('_sp_is_test') || $o->get_created_via() === 'sp_abo') continue;
        if (in_array($o->get_status(), ['cancelled', 'failed'], true)) { $R['canc']++; continue; }
        if ($o->get_meta('_sp_dupe_suspect')) continue;
        $R['open_n']++; $R['open_rev'] += (float) $o->get_total();
    }
    // Partner: Namen, Überweisungen im Monat, offener Saldo am Monatsende
    $R['aff_names'] = []; $R['aff_transfer'] = 0.0; $R['aff_transfer_list'] = []; $R['aff_open'] = [];
    $tr = (array) get_option('sp_aff_transfers', []);
    foreach ($tr as $t) {
        $ts = strtotime(($t['date'] ?? '') . ' 12:00');
        if ($ts >= $s && $ts < $e) { $R['aff_transfer'] += (float) $t['amount']; $R['aff_transfer_list'][] = $t; }
    }
    $affs = $wpdb->get_col("SELECT DISTINCT affiliate_id FROM {$wpdb->prefix}slicewp_commissions WHERE status IN ('paid','unpaid')");
    foreach (array_unique(array_merge(array_keys($R['aff']), $affs)) as $a) {
        if (!$a) { $R['aff_names'][0] = 'Ohne Partner'; continue; }
        $af = function_exists('slicewp_get_affiliate') ? slicewp_get_affiliate($a) : null;
        $u = $af ? get_userdata($af->get('user_id')) : null;
        $R['aff_names'][$a] = $u ? (trim($u->first_name . ' ' . $u->last_name) ?: $u->display_name) : 'Partner ' . $a;
        $earned = (float) $wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id=%d AND status IN ('paid','unpaid') AND date_created < %s", $a, gmdate('Y-m-d H:i:s', $e)));
        $paid = 0.0;
        foreach ($tr as $t) if ((int) $t['aff'] === (int) $a && strtotime(($t['date'] ?? '') . ' 12:00') < $e) $paid += (float) $t['amount'];
        if (abs($earned - $paid) > 0.5) $R['aff_open'][$a] = $earned - $paid;
    }
    $R['purch'] = function_exists('sp_lager_purchases') ? sp_lager_purchases(wp_date('Y-m-d', $s), wp_date('Y-m-d', $e - 1)) : ['total' => 0, 'list' => []];
    $R['nl_new'] = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}fc_subscribers WHERE status='subscribed' AND created_at>=%s AND created_at<%s", wp_date('Y-m-d H:i:s', $s), wp_date('Y-m-d H:i:s', $e)));
    $R['visitors'] = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT CONCAT(day,vh)) FROM {$wpdb->prefix}sp_stats WHERE day>=%s AND day<%s AND country<>'US'", wp_date('Y-m-d', $s), wp_date('Y-m-d', $e)));
    $since = (string) get_option('sp_an_since', '');
    $R['visitors_since'] = ($since && strtotime($since) > $s && strtotime($since) < $e) ? wp_date('j.n.', strtotime($since)) : '';
    $fx = (array) get_option('sp_mb_fix', []);
    $R['fix'] = isset($fx[$ym]) ? (float) $fx[$ym]['amount'] : null;
    $R['fix_note'] = isset($fx[$ym]) ? (string) $fx[$ym]['note'] : '';
    $R['net'] = $R['res'] - (float) $R['fix'];
    return $R;
}

/** Umsatz aller bisherigen Monate (schnell, ohne EK) – für "Bester Monat". */
function sp_mb_month_totals($until_ym) {
    $out = [];
    $ym = '2026-09';
    while ($ym <= $until_ym) {
        [$s, $e] = sp_mb_range($ym);
        $sum = 0.0;
        foreach (wc_get_orders(['limit' => -1, 'type' => 'shop_order', 'status' => ['processing', 'completed', 'refunded'], 'date_paid' => $s . '...' . ($e - 1)]) as $o) {
            if (!$o->get_meta('_sp_is_test')) $sum += (float) $o->get_total() - (float) $o->get_total_refunded();
        }
        $out[$ym] = $sum;
        $ym = (new DateTime($ym . '-01'))->modify('+1 month')->format('Y-m');
    }
    return $out;
}

function sp_mb_eur($v, $sign = '', $dec = 2) { return $sign . number_format(abs((float) $v), $dec, ',', '.') . ' €'; }
function sp_mb_de($v, $dec = 1) { return number_format((float) $v, $dec, ',', '.'); }

function sp_mb_html($ym) {
    $o = sp_mb_data($ym);
    $pym = sp_mb_prev($ym);
    $p = sp_mb_data($pym);
    $pm = sp_mb_short_month($pym); $om = sp_mb_short_month($ym);
    $hasp = $p['orders'] > 0;
    $INK = '#0D0F12'; $MUT = '#5B6169'; $LINE = '#E4E6E9'; $GRN = '#1F8A4C'; $RED = '#B42318'; $BG = '#F5F6F7'; $OR = '#E07B1F';
    $F = "font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif;";
    $E = 'sp_mb_eur';
    $chg = function ($a, $b) { if (!$b) return ''; $x = ($a - $b) / $b * 100; return ($x >= 0 ? '▲ ' : '▼ ') . sp_mb_de(abs($x), 0) . ' %'; };
    $card = function ($title, $inner, $sub = '') use ($LINE, $MUT) {
        return '<tr><td style="padding:0 14px 14px;"><div style="background:#FFF;border:1px solid ' . $LINE . ';border-radius:16px;padding:16px 16px 12px;">'
            . '<div style="font-size:11px;font-weight:800;letter-spacing:.14em;color:' . $MUT . ';text-transform:uppercase;">' . $title . '</div>'
            . ($sub ? '<div style="font-size:12px;color:' . $MUT . ';margin-top:2px;">' . $sub . '</div>' : '')
            . '<div style="height:8px"></div>' . $inner . '</div></td></tr>';
    };
    $r2 = function ($label, $a, $b, $note = '', $bold = false, $col = null, $top = false) use ($INK, $MUT, $LINE) {
        $col = $col ?: $INK;
        $bt = $top ? "border-top:2px solid $INK;" : "border-top:1px solid $LINE;";
        $w = $bold ? 'font-weight:800;' : 'font-weight:600;';
        return '<tr><td style="padding:8px 0;' . $bt . 'font-size:13.5px;' . $w . 'color:' . $INK . ';">' . $label
            . ($note !== '' ? '<div style="font-size:11px;font-weight:400;color:' . $MUT . ';margin-top:1px;">' . $note . '</div>' : '') . '</td>'
            . '<td align="right" style="padding:8px 0 8px 6px;' . $bt . 'font-size:14px;' . $w . 'color:' . $col . ';white-space:nowrap;vertical-align:top;">' . $a . '</td>'
            . '<td align="right" style="padding:8px 0 8px 8px;' . $bt . 'font-size:12px;color:' . $MUT . ';white-space:nowrap;vertical-align:top;">' . $b . '</td></tr>';
    };
    $head2 = function () use ($INK, $MUT, $om, $pm, $hasp) {
        return '<tr><td></td><td align="right" style="font-size:10.5px;color:' . $INK . ';font-weight:800;padding-bottom:5px;">' . $om . '</td><td align="right" style="font-size:10.5px;color:' . $MUT . ';font-weight:700;padding-bottom:5px;">' . ($hasp ? $pm : '') . '</td></tr>';
    };
    $T = function ($rows) { return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . implode('', $rows) . '</table>'; };
    $pv = function ($v, $sign = '') use ($hasp) { return $hasp ? sp_mb_eur($v, $sign) : ''; };

    if (!$o['orders']) {
        return '<!doctype html><html><head><meta charset="utf-8"></head><body style="' . $F . 'padding:30px;">Keine bezahlten Bestellungen im ' . esc_html(sp_mb_month_name($ym)) . '.</body></html>';
    }
    $marge = $o['res'] / $o['rev'] * 100;
    $hasfix = $o['fix'] !== null;
    $gl = $hasfix ? 'nach Fixkosten' : 'vor Fixkosten';
    $g = $hasfix ? $o['net'] : $o['res'];
    $gp = $hasfix && $p['fix'] !== null ? $p['net'] : $p['res'];

    // Kopf
    $tile = function ($lbl, $val, $sub) {
        return '<td width="50%" style="width:50%;padding:4px;vertical-align:top;"><div style="background:#1A1E23;border-radius:14px;padding:13px;">'
            . '<div style="font-size:10.5px;color:#9AA0A8;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">' . $lbl . '</div>'
            . '<div style="font-size:22px;font-weight:800;color:#FFF;margin-top:3px;white-space:nowrap;">' . $val . '</div>'
            . '<div style="font-size:11.5px;color:#9AA0A8;margin-top:2px;">' . $sub . '</div></div></td>';
    };
    $up = function ($a, $b) use ($chg) { $c = $chg($a, $b); return $c ? '<span style="color:' . (strpos($c, '▲') === 0 ? '#7FD49B' : '#F09A8A') . ';">' . $c . '</span> ' : ''; };
    $logo = 'https://peptrium.com/wp-content/uploads/2026/08/IMG_2644-cropped-300x300.png';
    $hero = '<tr><td style="background:#0B0D10;padding:20px 18px 16px;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td><img src="' . $logo . '" width="28" height="28" alt="" style="vertical-align:middle;border:0;"><span style="vertical-align:middle;margin-left:8px;font-size:12px;font-weight:800;letter-spacing:.3em;color:#E6E8EB;">PEPTRIUM</span></td><td align="right" style="font-size:11px;color:#7D848D;">Vertraulich</td></tr></table>'
        . '<div style="font-size:12px;font-weight:700;letter-spacing:.16em;color:#9AA0A8;margin-top:18px;">MONATSBERICHT</div>'
        . '<div style="font-size:32px;font-weight:800;color:#FFF;letter-spacing:-.02em;margin:2px 0 12px;">' . esc_html(sp_mb_month_name($ym)) . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
        . $tile('Umsatz', $E($o['rev'], '', 0), $hasp ? $up($o['rev'], $p['rev']) . 'ggü. ' . $pm : '&nbsp;')
        . $tile('Gewinn', ($g < 0 ? '− ' : '') . $E($g, '', 0), $up($g, $hasp ? $gp : 0) . $gl) . '</tr><tr>'
        . $tile('Bestellungen', $o['orders'], $hasp ? $pm . ': ' . $p['orders'] : '&nbsp;')
        . $tile('Ø Bestellung', $E($o['rev'] / $o['orders'], '', 0), $hasp ? $pm . ': ' . $E($p['rev'] / $p['orders'], '', 0) : '&nbsp;')
        . '</tr></table></td></tr>';

    // Produkte
    $P = $o['prod'];
    foreach ($P as $k => $v) $P[$k]['g'] = $v['rev'] - $v['cogs'];
    uasort($P, function ($a, $b) { return $b['g'] <=> $a['g']; });
    $best = array_key_first($P);

    // Partner
    $affs = $o['aff']; uasort($affs, function ($a, $b) { return $b['rev'] <=> $a['rev']; });
    $topaff = null; foreach ($affs as $a => $v) if ($a) { $topaff = $a; break; }

    // Auf einen Blick
    $tot = sp_mb_month_totals($ym);
    $isbest = count($tot) > 1 && max($tot) <= ($tot[$ym] ?? 0) + 0.01;
    $pts = [];
    $l1 = $o['orders'] . ' bezahlte Bestellungen und ' . $E($o['rev'], '', 0) . ' Umsatz';
    if ($hasp && $p['rev'] > 0) $l1 .= $o['rev'] >= $p['rev'] ? ' – ' . sp_mb_de($o['rev'] / $p['rev']) . '× so viel wie im Vormonat.' : ' – ' . sp_mb_de((1 - $o['rev'] / $p['rev']) * 100, 0) . ' % weniger als im Vormonat.';
    else $l1 .= '.';
    $pts[] = ($isbest ? '<b>Bester Monat seit Start:</b> ' : '') . $l1;
    $pts[] = '<b>' . sp_mb_de($marge, 0) . ' % vom Umsatz bleiben als Gewinn</b> (' . $E($o['res'], '', 0) . ')' . ($hasfix ? ', nach Fixkosten ' . $E($o['net'], '', 0) . '.' : ', Fixkosten noch nicht abgezogen.');
    if ($topaff) {
        $v = $o['aff'][$topaff];
        $pts[] = '<b>' . $v['n'] . ' von ' . $o['orders'] . ' Bestellungen kamen über ' . esc_html($o['aff_names'][$topaff]) . '</b> (' . sp_mb_de($v['n'] / $o['orders'] * 100, 0) . ' %). Provision: ' . $E($v['comm'], '', 0) . '.';
    }
    if ($o['hw_rev'] > 0) $pts[] = '<b>Halloween-Aktion:</b> ' . $E($o['hw_rev'], '', 0) . ' = ' . sp_mb_de($o['hw_rev'] / $o['rev'] * 100, 0) . ' % des Monats, ' . $o['hw_free'] . ' Flaschen gratis.';
    if ($best) $pts[] = '<b>Am meisten verdient an ' . esc_html($best) . '</b> (' . $E($P[$best]['g'], '', 0) . ' Rohertrag aus ' . $P[$best]['q'] . ' Stück).';
    if ($o['open_n']) $pts[] = '<b>Noch offen:</b> ' . $o['open_n'] . ' Bestellungen aus dem Monat über ' . $E($o['open_rev'], '', 0) . ' warten auf Zahlung.';
    $blick = $card('Auf einen Blick', implode('', array_map(function ($t) use ($INK, $LINE) {
        return '<div style="font-size:14px;line-height:1.5;color:' . $INK . ';padding:7px 0;border-top:1px solid ' . $LINE . ';">' . $t . '</div>';
    }, $pts)));

    // Gewinn
    $rows = [$head2(),
        $r2('Umsatz', $E($o['rev']), $pv($p['rev']), 'inkl. ' . $E($o['ship_in']) . ' Versandgebühren'),
        $r2('− Ware (Einkauf)', $E($o['cogs'], '− '), $pv($p['cogs'], '− '), $o['gift_cogs'] > 0 ? 'inkl. Gratis-Geschenke ' . $E($o['gift_cogs']) : '', false, $RED),
        $r2('− Partner-Provision', $E($o['comm'], '− '), $pv($p['comm'], '− '), sp_mb_de($o['comm'] / $o['rev'] * 100) . ' % vom Umsatz', false, $RED),
        $r2('− Versand & Verpackung', $E($o['own'], '− '), $pv($p['own'], '− '), 'DHL + Verpackung je Paket', false, $RED)];
    if ($hasfix) {
        $rows[] = $r2('= Gewinn vor Fixkosten', $E($o['res']), $pv($p['res']), '', true, $INK, true);
        $rows[] = $r2('− Fixkosten', $E($o['fix'], '− '), $hasp && $p['fix'] !== null ? $E($p['fix'], '− ') : '', esc_html($o['fix_note']), false, $RED);
        $rows[] = $r2('= Gewinn', ($o['net'] < 0 ? '− ' : '') . $E($o['net']), $hasp && $p['fix'] !== null ? $E($p['net']) : '', sp_mb_de($o['net'] / $o['rev'] * 100, 0) . ' % vom Umsatz', true, $o['net'] >= 0 ? $GRN : $RED, true);
    } else {
        $rows[] = $r2('− Fixkosten', '–', '', 'Omnisend, Hosting, Werbung … (noch nicht eingetragen)', false, $MUT);
        $rows[] = $r2('= Gewinn', $E($o['res']), $pv($p['res']), sp_mb_de($marge, 0) . ' % vom Umsatz' . ($hasp ? ' (' . $pm . ' ' . sp_mb_de($p['res'] / max(1, $p['rev']) * 100, 0) . ' %)' : ''), true, $GRN, true);
    }
    $parts = [[$o['cogs'], '#8E959E', 'Ware'], [$o['comm'], '#C98A2B', 'Provision'], [$o['own'], '#B9BEC5', 'Versand']];
    if ($hasfix) $parts[] = [$o['fix'], '#6B7280', 'Fixkosten'];
    $parts[] = [max(0, $g), $GRN, 'Gewinn'];
    $bar = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>';
    $leg = '';
    foreach ($parts as [$v, $c, $n]) {
        if ($v <= 0) continue;
        $bar .= '<td width="' . number_format($v / $o['rev'] * 100, 1, '.', '') . '%" style="height:12px;background:' . $c . ';font-size:0;">&nbsp;</td>';
        $leg .= '<span style="white-space:nowrap;"><span style="color:' . $c . ';">■</span> ' . $n . ' ' . sp_mb_de($v / $o['rev'] * 100, 0) . ' %</span> &nbsp; ';
    }
    $bar .= '</tr></table>';
    $c_gew = $card('Vom Umsatz zum Gewinn', $T($rows) . '<div style="height:10px"></div>' . $bar . '<div style="font-size:11px;color:' . $MUT . ';margin-top:6px;">' . $leg . '</div>');

    // Geld auf dem Konto
    $r1 = function ($l, $v, $n = '', $col = null, $b = false, $top = false) use ($r2) { return $r2($l, $v, '', $n, $b, $col, $top); };
    $pl = [];
    foreach ($o['purch']['list'] as $x) $pl[] = esc_html($x['label'] ?? 'Einkauf') . ' ' . $E($x['paid_eur'] ?? 0);
    $tl = [];
    foreach ($o['aff_transfer_list'] as $t) $tl[] = esc_html($o['aff_names'][$t['aff']] ?? ('Partner ' . $t['aff'])) . ' ' . wp_date('j.n.', strtotime($t['date'] . ' 12:00'));
    $geld = $o['rev'] - $o['purch']['total'] - $o['aff_transfer'] - $o['own'] - (float) $o['fix'];
    $grows = [$r1('Eingenommen', $E($o['rev'])),
        $r1('− Ware eingekauft & bezahlt', $E($o['purch']['total'], '− '), implode(' · ', $pl), $RED),
        $r1('− An Partner überwiesen', $E($o['aff_transfer'], '− '), implode(' · ', $tl), $RED),
        $r1('− Versand & Verpackung', $E($o['own'], '− '), '', $RED)];
    if ($hasfix) $grows[] = $r1('− Fixkosten', $E($o['fix'], '− '), '', $RED);
    $diff = $geld - $g;
    $grows[] = $r1('= Geld übrig', ($geld < 0 ? '− ' : '') . $E($geld), abs($diff) < 1 ? '' : ($diff > 0 ? 'mehr als der Gewinn: weniger eingekauft als verkauft bzw. Provision noch nicht überwiesen' : 'weniger als der Gewinn: Ware auf Vorrat gekauft bzw. ältere Provision überwiesen'), $geld >= 0 ? $GRN : $RED, true, true);
    $open = [];
    if ($o['open_n']) $open[] = $o['open_n'] . ' unbezahlte Bestellungen (' . $E($o['open_rev']) . ')';
    foreach ($o['aff_open'] as $a => $v) if ($v > 0) $open[] = 'Provision an ' . esc_html($o['aff_names'][$a]) . ' noch nicht überwiesen: ' . $E($v);
    if ($o['canc']) $open[] = $o['canc'] . ' storniert';
    $c_geld = $card('Geld auf dem Konto', $T($grows) . ($open ? '<div style="height:12px"></div><div style="background:#FFF7EA;border:1px solid #F4D7A8;border-radius:12px;padding:10px 12px;font-size:13px;line-height:1.55;color:#5A3A00;"><b>Offen (Stand Monatsende):</b> ' . implode(' · ', $open) . '</div>' : ''));

    // Partner
    $prow = [$head2()];
    foreach ($affs as $a => $v) {
        $pvv = $p['aff'][$a] ?? null;
        if ($a) $prow[] = $r2(esc_html($o['aff_names'][$a]), $E($v['comm']), $hasp ? ($pvv ? $E($pvv['comm']) : '–') : '', $v['n'] . ' Bestellungen · ' . $E($v['rev'], '', 0) . ' Umsatz · Ø ' . sp_mb_de($v['comm'] / max(1, $v['rev']) * 100) . ' % Provision');
        else $prow[] = $r2('Ohne Partner', '–', '', $v['n'] . ' Bestellungen · ' . $E($v['rev'], '', 0) . ' Umsatz' . ($pvv ? ' (' . $pm . ': ' . $pvv['n'] . ')' : ''), false, $MUT);
    }
    $c_par = $card('Partner', $T($prow));

    // Produkte
    $td = function ($x, $st = '') use ($LINE) { return '<td align="right" style="padding:8px 0 8px 6px;border-top:1px solid ' . $LINE . ';font-size:13.5px;white-space:nowrap;vertical-align:top;' . $st . '">' . $x . '</td>'; };
    $th = function ($t, $a = 'left') use ($MUT) { return '<td align="' . $a . '" style="font-size:10px;color:' . $MUT . ';font-weight:700;padding:0 0 5px 6px;text-transform:uppercase;letter-spacing:.05em;">' . $t . '</td>'; };
    $prs = []; $i = 0; $rest = [];
    foreach ($P as $k => $v) {
        if ($i++ >= 8) { $rest[$k] = $v; continue; }
        $prs[] = '<tr><td style="padding:8px 0;border-top:1px solid ' . $LINE . ';font-size:13px;font-weight:700;">' . esc_html($k) . '</td>' . $td($v['q']) . $td($E($v['rev'], '', 0))
            . $td($E($v['g'], '', 0), "font-weight:800;color:$GRN;") . $td($v['rev'] > 0 ? sp_mb_de($v['g'] / $v['rev'] * 100, 0) . ' %' : '–', "color:$MUT;font-size:12px;") . '</tr>';
    }
    if ($rest) {
        $q = array_sum(array_column($rest, 'q')); $rv = array_sum(array_column($rest, 'rev')); $gg = array_sum(array_column($rest, 'g'));
        $prs[] = '<tr><td style="padding:8px 0;border-top:1px solid ' . $LINE . ';font-size:12.5px;color:' . $MUT . ';">' . count($rest) . ' weitere<div style="font-size:11px;">' . esc_html(implode(', ', array_keys($rest))) . '</div></td>'
            . $td($q, "color:$MUT;") . $td($E($rv, '', 0), "color:$MUT;") . $td($E($gg, '', 0), "color:$MUT;") . $td('') . '</tr>';
    }
    $q = array_sum(array_column($P, 'q')); $rv = array_sum(array_column($P, 'rev')); $gg = array_sum(array_column($P, 'g'));
    $bt = "border-top:2px solid $INK;";
    $prs[] = '<tr><td style="padding:8px 0;' . $bt . 'font-size:13px;font-weight:800;">Gesamt</td>' . $td($q, $bt . 'font-weight:800;') . $td($E($rv, '', 0), $bt . 'font-weight:800;')
        . $td($E($gg, '', 0), $bt . "font-weight:800;color:$GRN;") . $td($rv > 0 ? sp_mb_de($gg / $rv * 100, 0) . ' %' : '', $bt . "font-size:12px;color:$MUT;") . '</tr>';
    $foot = [];
    if ($o['gifts']) $foot[] = 'Verschenkt: ' . $o['gifts'] . ' Geschenke (Ladenwert ' . $E($o['gift_value'], '', 0) . ', Einkauf ' . $E($o['gift_cogs'], '', 0) . ')';
    if ($o['hw_free']) $foot[] = 'Halloween: ' . $o['hw_free'] . ' Flaschen gratis (Ladenwert ' . $E($o['hw_save'], '', 0) . ')';
    $c_pro = $card('Produkte', '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>' . $th('Produkt') . $th('Stk', 'right') . $th('Umsatz', 'right') . $th('Rohertrag', 'right') . $th('Marge', 'right') . '</tr>' . implode('', $prs) . '</table>'
        . '<div style="font-size:11.5px;color:' . $MUT . ';margin-top:8px;line-height:1.55;">Sortiert nach Rohertrag (Umsatz nach Rabatten minus Einkauf). Ohne Versandgebühren, daher kleiner als der Gesamtumsatz.' . ($foot ? '<br>' . implode(' · ', $foot) : '') . '</div>');

    // Kunden
    $kt = function ($l, $v, $sub = '') use ($BG, $MUT, $INK) {
        return '<td width="33%" style="width:33%;padding:4px;vertical-align:top;"><div style="background:' . $BG . ';border-radius:12px;padding:10px;"><div style="font-size:10.5px;color:' . $MUT . ';font-weight:700;">' . $l . '</div><div style="font-size:19px;font-weight:800;color:' . $INK . ';margin-top:2px;">' . $v . '</div><div style="font-size:10.5px;color:' . $MUT . ';">' . $sub . '</div></div></td>';
    };
    arsort($o['country']);
    $lands = array_slice($o['country'], 0, 3, true);
    $c_kun = $card('Kunden', '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
        . $kt('Neukunden', $o['new'], $hasp ? $pm . ': ' . $p['new'] : '')
        . $kt('Stammkunden', $o['ret'], $hasp ? $pm . ': ' . $p['ret'] : '')
        . $kt('Länder', esc_html(implode(' · ', array_keys($lands))), implode(' · ', $lands)) . '</tr><tr>'
        . $kt('Besucher', number_format($o['visitors'], 0, ',', '.'), $o['visitors_since'] ? 'ab ' . $o['visitors_since'] . ' gemessen' : 'eindeutige pro Tag')
        . $kt('Kaufquote', $o['visitors'] ? sp_mb_de($o['orders'] / $o['visitors'] * 100) . ' %' : '–', 'Besucher → Bestellung')
        . $kt('Newsletter', '+' . $o['nl_new'], 'neue Abonnenten') . '</tr></table>');

    // Rabatte
    $lab = ['EMPF' => 'Empfehlungs-Rabatt (Partner-Link)', 'HALLO' => 'HALLO-Codes (Newsletter)', 'COMEBACK' => 'COMEBACK-Codes', 'DANKE' => 'DANKE-Codes (Freunde werben)', 'FREUND' => 'FREUND-Codes (Freunde werben)'];
    $cps = $o['coupons']; uasort($cps, function ($a, $b) { return $b[1] <=> $a[1]; });
    $cr = []; $ctot = 0.0;
    foreach ($cps as $k => $v) { $ctot += $v[1]; $cr[] = $r1(esc_html($lab[$k] ?? $k) . ' <span style="color:' . $MUT . ';font-weight:400;">· ' . $v[0] . '×</span>', $E($v[1], '− '), '', $RED); }
    $cr[] = $r1('Rabatte gesamt', $E($ctot, '− '), sp_mb_de($ctot / $o['rev'] * 100) . ' % vom Umsatz · ohne Mengenrabatt/Aktionen', $INK, true, true);
    $c_rab = $ctot > 0 ? $card('Rabatte & Codes', $T($cr)) : '';

    // Verlauf
    $days = (int) (new DateTime($ym . '-01'))->format('t');
    $mx = max(1, max($o['days'] ?: [0]));
    $cells = ''; $axis = '';
    $hwday = function ($d) use ($ym) { $o = get_option('sp_hw'); if (empty($o['early'])) return false; $ts = (new DateTime(sprintf('%s-%02d 12:00', $ym, $d), sp_mb_tz()))->getTimestamp(); return $ts >= (int) $o['early'] - 43200 && $ts <= (int) $o['end'] + 43200; };
    for ($d = 1; $d <= $days; $d++) {
        $v = $o['days'][$d] ?? 0; $h = $v ? (int) round($v / $mx * 80) : 0;
        $col = $v ? ($hwday($d) ? $OR : $INK) : $LINE;
        $cells .= '<td valign="bottom" align="center" style="padding:0 1px;"><div style="height:' . (80 - max($h, 2)) . 'px;font-size:0;">&nbsp;</div><div style="height:' . max($h, 2) . 'px;background:' . $col . ';border-radius:3px 3px 0 0;font-size:0;">&nbsp;</div></td>';
        $axis .= '<td align="center" style="font-size:9px;color:' . $MUT . ';padding-top:3px;">' . (in_array($d, [1, 8, 15, 22, 29], true) ? $d : '') . '</td>';
    }
    $wk = '';
    foreach ([[1, 7], [8, 14], [15, 21], [22, 28], [29, $days]] as [$a, $b]) {
        if ($a > $days) continue;
        $sum = 0; for ($d = $a; $d <= $b; $d++) $sum += $o['days'][$d] ?? 0;
        $wk .= '<td align="center" style="padding:6px 2px 0;"><div style="font-size:10px;color:' . $MUT . ';">' . $a . '.–' . $b . '.</div><div style="font-size:12.5px;font-weight:800;color:' . $INK . ';">' . $E($sum, '', 0) . '</div></td>';
    }
    $c_ver = $card('Umsatz im Verlauf', '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="table-layout:fixed;"><tr>' . $cells . '</tr><tr>' . $axis . '</tr></table>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;border-top:1px solid ' . $LINE . ';"><tr>' . $wk . '</tr></table>', 'nach Zahlungseingang' . ($o['hw_rev'] > 0 ? ', orange = Halloween' : ''));

    $footer = '<tr><td style="padding:18px;background:#12151A;text-align:center;font-size:11px;line-height:1.6;color:#7D848D;">Gezählt werden Bestellungen nach <b style="color:#B9BEC5;">Zahlungseingang</b> im Monat (Testbestellungen ausgenommen).<br>Gewinn ' . ($hasfix ? 'nach Fixkosten, ' : 'vor Fixkosten und ') . 'vor Steuern · erstellt am ' . wp_date('d.m.Y') . ' · vertraulich</td></tr>';
    return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Monatsbericht ' . esc_html(sp_mb_month_name($ym)) . '</title></head><body style="margin:0;background:#E9EBED;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E9EBED;"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:' . $BG . ';' . $F . '">'
        . $hero . '<tr><td style="height:14px"></td></tr>' . $blick . $c_gew . $c_geld . $c_par . $c_pro . $c_kun . $c_rab . $c_ver . $footer
        . '</table></td></tr></table></body></html>';
}

function sp_mb_send($ym, $to) {
    $list = array_values(array_filter(array_map('trim', preg_split('/[,;\s]+/', (string) $to)), 'is_email'));
    if (!$list) return false;
    $html = sp_mb_html($ym);
    $ok = wp_mail($list, 'Peptrium Monatsbericht ' . sp_mb_month_name($ym), $html, ['Content-Type: text/html; charset=UTF-8']);
    if ($ok) {
        $s = (array) get_option('sp_mb_sent', []);
        $s[$ym][] = ['t' => time(), 'to' => implode(', ', $list)];
        update_option('sp_mb_sent', $s, false);
    }
    return $ok;
}

/* ---------- Automatisch am 1. ---------- */
add_action('init', function () {
    if (!wp_next_scheduled('sp_mb_daily')) {
        $t = new DateTime('tomorrow 08:00', sp_mb_tz());
        wp_schedule_event($t->getTimestamp(), 'daily', 'sp_mb_daily');
    }
});
add_action('sp_mb_daily', function () {
    $a = (array) get_option('sp_mb_auto', []);
    if (empty($a['on']) || empty($a['to'])) return;
    if ((int) wp_date('j') > 3) return;                          // 1.–3. des Monats (falls Cron verpasst)
    $ym = sp_mb_prev(wp_date('Y-m'));
    $done = (array) get_option('sp_mb_auto_done', []);
    if (!empty($done[$ym])) return;
    if (sp_mb_send($ym, $a['to'])) { $done[$ym] = time(); update_option('sp_mb_auto_done', $done, false); }
});

/* ---------- Admin ---------- */
add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Monatsbericht', 'Monatsbericht', 'manage_woocommerce', 'sp-monatsbericht', 'sp_mb_render_page');
}, 26);

function sp_mb_render_page() {
    if (!current_user_can('manage_woocommerce')) return;
    $ym = isset($_REQUEST['ym']) && preg_match('/^\d{4}-\d{2}$/', $_REQUEST['ym']) ? $_REQUEST['ym'] : sp_mb_prev(wp_date('Y-m'));
    $msg = '';
    if (!empty($_POST['sp_mb_action']) && check_admin_referer('sp_mb')) {
        $act = sanitize_key($_POST['sp_mb_action']);
        if ($act === 'fix') {
            $fx = (array) get_option('sp_mb_fix', []);
            $raw = trim((string) wp_unslash($_POST['fix_amount'] ?? ''));
            if ($raw === '') unset($fx[$ym]);
            else $fx[$ym] = ['amount' => (float) str_replace(',', '.', str_replace('.', '', preg_replace('/[^\d,.\-]/', '', $raw))), 'note' => sanitize_text_field(wp_unslash($_POST['fix_note'] ?? ''))];
            update_option('sp_mb_fix', $fx, false);
            $msg = 'Fixkosten gespeichert.';
        } elseif ($act === 'send') {
            $to = sanitize_text_field(wp_unslash($_POST['to'] ?? ''));
            update_option('sp_mb_last_email', $to, false);
            $msg = sp_mb_send($ym, $to) ? 'Bericht an ' . esc_html($to) . ' gesendet.' : 'Senden fehlgeschlagen – E-Mail-Adresse prüfen.';
        } elseif ($act === 'auto') {
            update_option('sp_mb_auto', ['on' => !empty($_POST['auto_on']) ? 1 : 0, 'to' => sanitize_text_field(wp_unslash($_POST['auto_to'] ?? ''))], false);
            $msg = 'Einstellung gespeichert.';
        }
    }
    $fx = (array) get_option('sp_mb_fix', []);
    $auto = wp_parse_args((array) get_option('sp_mb_auto', []), ['on' => 0, 'to' => '']);
    $last = (string) get_option('sp_mb_last_email', '');
    $sent = (array) get_option('sp_mb_sent', []);
    $months = []; $m = '2026-09';
    while ($m <= wp_date('Y-m')) { $months[] = $m; $m = (new DateTime($m . '-01'))->modify('+1 month')->format('Y-m'); }
    $months = array_reverse($months);
    $html = sp_mb_html($ym);
    $url = admin_url('admin.php?page=sp-monatsbericht');
    ?>
    <div class="wrap" style="max-width:1100px;">
      <h1>Monatsbericht</h1>
      <?php if ($msg) echo '<div class="notice notice-success"><p>' . $msg . '</p></div>'; ?>
      <p>
        <?php foreach ($months as $mm) {
            $cur = $mm === $ym;
            echo '<a href="' . esc_url($url . '&ym=' . $mm) . '" class="button' . ($cur ? ' button-primary' : '') . '" style="margin:0 4px 4px 0;">' . esc_html(sp_mb_month_name($mm)) . ($mm === wp_date('Y-m') ? ' (läuft)' : '') . '</a>';
        } ?>
      </p>
      <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:flex-start;">
        <div style="flex:1 1 640px;max-width:680px;">
          <iframe srcdoc="<?php echo esc_attr($html); ?>" style="width:100%;height:1500px;border:1px solid #dcdcde;border-radius:10px;background:#E9EBED;"></iframe>
        </div>
        <div style="flex:0 1 340px;">
          <div class="card" style="max-width:none;margin-top:0;">
            <h2 style="margin-top:0;">Per E-Mail senden</h2>
            <form method="post">
              <?php wp_nonce_field('sp_mb'); ?>
              <input type="hidden" name="sp_mb_action" value="send"><input type="hidden" name="ym" value="<?php echo esc_attr($ym); ?>">
              <p><label>Empfänger (mehrere mit Komma)<br><input type="text" name="to" value="<?php echo esc_attr($last); ?>" class="widefat" placeholder="name@beispiel.de"></label></p>
              <p><button class="button button-primary" onclick="return confirm('Bericht <?php echo esc_js(sp_mb_month_name($ym)); ?> jetzt senden?');">Bericht <?php echo esc_html(sp_mb_month_name($ym)); ?> senden</button></p>
            </form>
            <?php if (!empty($sent[$ym])) { echo '<p style="color:#5B6169;font-size:12px;">Gesendet: '; foreach ($sent[$ym] as $x) echo esc_html(wp_date('d.m. H:i', $x['t']) . ' → ' . $x['to']) . '<br>'; echo '</p>'; } ?>
          </div>
          <div class="card" style="max-width:none;">
            <h2 style="margin-top:0;">Fixkosten <?php echo esc_html(sp_mb_month_name($ym)); ?></h2>
            <form method="post">
              <?php wp_nonce_field('sp_mb'); ?>
              <input type="hidden" name="sp_mb_action" value="fix"><input type="hidden" name="ym" value="<?php echo esc_attr($ym); ?>">
              <p><label>Betrag in €<br><input type="text" name="fix_amount" value="<?php echo isset($fx[$ym]) ? esc_attr(number_format($fx[$ym]['amount'], 2, ',', '.')) : ''; ?>" class="widefat" placeholder="z. B. 120,00"></label></p>
              <p><label>Wofür (erscheint im Bericht)<br><input type="text" name="fix_note" value="<?php echo esc_attr($fx[$ym]['note'] ?? ''); ?>" class="widefat" placeholder="Omnisend 50 € · Hosting 30 € · …"></label></p>
              <p><button class="button">Speichern</button> <span style="color:#5B6169;font-size:12px;">Leer lassen = nicht eingetragen</span></p>
            </form>
          </div>
          <div class="card" style="max-width:none;">
            <h2 style="margin-top:0;">Automatisch senden</h2>
            <form method="post">
              <?php wp_nonce_field('sp_mb'); ?>
              <input type="hidden" name="sp_mb_action" value="auto"><input type="hidden" name="ym" value="<?php echo esc_attr($ym); ?>">
              <p><label><input type="checkbox" name="auto_on" value="1" <?php checked($auto['on']); ?>> Am 1. jedes Monats den Bericht für den Vormonat senden (ca. 8 Uhr)</label></p>
              <p><label>An<br><input type="text" name="auto_to" value="<?php echo esc_attr($auto['to'] ?: $last); ?>" class="widefat"></label></p>
              <p><button class="button">Speichern</button></p>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php
}
