<?php
/**
 * Partnerkonto → Tab „Bestellungen“ (2026-10-25, User: Partner sollen den Status ihrer Bestellungen sehen).
 * Jeder Partner sieht NUR seine eigenen Bestellungen: Bestellnummer, Datum, Vorname + Initial, Betrag,
 * Provision und Status (wartet auf Zahlung / bezahlt – Provision offen / ausgezahlt / storniert),
 * dazu Verdient / Überwiesen / Offen / Ausstehend (gleiche Rechnung wie Peptrium Dashboard → Partner-Auszahlungen)
 * und die Überweisungen. Keine Kontaktdaten der Kunden. v2 (2026-10-25): dunkle Kopfkarte, Monatszeile, offene Zahlungen zuerst,
 * Monate einklappbar, Stornierte ausgeblendet, Produkte je Bestellung, SliceWP-Tabs Provisionen/Auszahlungen ausgeblendet.
 * Bei offenen Vorkasse-Bestellungen kann der Partner die normale Shop-Zahlungserinnerung auslösen
 * (ab 1 Tag nach Bestellung, höchstens alle 48 Std., zählt mit der automatischen Erinnerung).
 */
if (!defined('ABSPATH')) exit;

const SP_PAO_REMIND_MIN_AGE = DAY_IN_SECONDS;
const SP_PAO_REMIND_GAP = 2 * DAY_IN_SECONDS;

add_filter('slicewp_affiliate_account_tabs', function ($tabs) {
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>';
    $out = [];
    foreach ($tabs as $k => $t) {
        $out[$k] = $t;
        if ($k === 'dashboard') $out['bestellungen'] = ['label' => 'Bestellungen', 'icon' => $icon];
    }
    if (!isset($out['bestellungen'])) $out['bestellungen'] = ['label' => 'Bestellungen', 'icon' => $icon];
    // SliceWPs eigene Tabs "Provisionen"/"Auszahlungen" rechnen anders (Überweisungen fehlen) → ausblenden, damit es nur EINE Übersicht gibt
    unset($out['commissions'], $out['payments']);
    return $out;
});

/** Letzte Zahlungserinnerung (Shop, automatisch oder Partner) als Zeitstempel. */
function sp_pao_last_reminder($o) {
    $ts = array_map('intval', array_filter((array) $o->get_meta('_sp_vorkasse_reminders')));
    $auto = $o->get_meta('_sp_vk_auto_sent');
    if ($auto) $ts[] = (int) strtotime(get_gmt_from_date($auto) . ' UTC');
    return $ts ? max($ts) : 0;
}

/** Darf jetzt erinnert werden? true oder Text, warum nicht. */
function sp_pao_can_remind($o) {
    if ($o->get_status() !== 'on-hold' || $o->get_payment_method() !== 'bacs') return 'nicht möglich';
    if ($o->get_meta('_sp_is_test')) return 'Testbestellung';
    $created = $o->get_date_created() ? $o->get_date_created()->getTimestamp() : time();
    if (time() - $created < SP_PAO_REMIND_MIN_AGE) return 'ab ' . wp_date('d.m. H:i', $created + SP_PAO_REMIND_MIN_AGE) . ' Uhr möglich';
    $last = sp_pao_last_reminder($o);
    if ($last && time() - $last < SP_PAO_REMIND_GAP) return 'wieder ab ' . wp_date('d.m. H:i', $last + SP_PAO_REMIND_GAP) . ' Uhr möglich';
    return true;
}

/** Alle Bestellungen eines Partners (Provisionen + nachgetragene Codes). */
function sp_pao_rows($aff) {
    global $wpdb;
    $aff = (int) $aff;
    $rows = [];
    $cs = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id = %d AND reference <> '' ORDER BY id DESC LIMIT 300", $aff));
    foreach ($cs as $c) {
        $oid = (int) $c->reference;
        if (isset($rows[$oid])) { // mehrere Provisionen zu einer Bestellung zusammenfassen
            $rows[$oid]['prov'] += $c->status === 'rejected' ? 0 : (float) $c->amount;
            continue;
        }
        $rows[$oid] = ['oid' => $oid, 'cstatus' => $c->status, 'prov' => $c->status === 'rejected' ? 0 : (float) $c->amount, 'date' => $c->date_created];
    }
    foreach ((array) get_option('sp_aff_nachtrag', []) as $oid => $e) {
        if ((int) ($e['aff'] ?? 0) !== $aff || isset($rows[(int) $oid])) continue;
        $o = wc_get_order($oid);
        if (!$o) continue;
        $sum = 0;
        foreach ($o->get_items() as $it) $sum += (float) $it->get_total();
        $rows[(int) $oid] = ['oid' => (int) $oid, 'cstatus' => 'pending', 'prov' => round($sum * (float) ($e['rate'] ?? 0), 2), 'date' => ''];
    }
    $out = [];
    foreach ($rows as $r) {
        $o = wc_get_order($r['oid']);
        if (!$o || $o->get_meta('_sp_is_test')) continue;
        $st = $o->get_status();
        if (in_array($r['cstatus'], ['rejected'], true) || in_array($st, ['cancelled', 'failed', 'refunded', 'trash'], true)) $k = 'storno';
        elseif ($r['cstatus'] === 'paid') $k = 'paid';
        elseif ($r['cstatus'] === 'unpaid' || in_array($st, ['processing', 'completed'], true)) $k = 'offen';
        else $k = 'wartet';
        $ln = trim((string) $o->get_billing_last_name());
        $r['k'] = $k;
        $r['nr'] = $o->get_order_number();
        $r['name'] = trim(mb_convert_case(trim((string) $o->get_billing_first_name()), MB_CASE_TITLE, 'UTF-8') . ($ln !== '' ? ' ' . mb_strtoupper(mb_substr($ln, 0, 1)) . '.' : ''));
        $r['total'] = (float) $o->get_total();
        $r['ts'] = $o->get_date_created() ? $o->get_date_created()->getTimestamp() : 0;
        $r['shipped'] = $st === 'completed';
        $r['bacs'] = $o->get_payment_method() === 'bacs';
        $r['last'] = sp_pao_last_reminder($o);
        $r['can'] = $k === 'wartet' ? sp_pao_can_remind($o) : false;
        $it = [];
        foreach ($o->get_items() as $li) {
            if ($li->get_meta('Geschenk') || $li->get_meta('_sp_is_gift')) continue;
            $nm = preg_replace('/\s+-\s+/', ' ', $li->get_name());
            $it[] = ($li->get_quantity() > 1 ? $li->get_quantity() . '× ' : '') . $nm;
        }
        $r['items'] = implode(', ', $it);
        $out[] = $r;
    }
    usort($out, function ($a, $b) { return $b['ts'] <=> $a['ts']; });
    return $out;
}

function sp_pao_money($v) { return number_format((float) $v, 2, ',', '.') . ' €'; }

/** Halloween-Bonus gerade aktiv? (Option, unabhängig vom Besucher-Cookie) */
function sp_pao_hw_bonus() {
    $o = get_option('sp_hw', []);
    if (!is_array($o) || empty($o['on']) || empty($o['early']) || empty($o['end'])) return 0;
    $now = time();
    return ($now >= (int) $o['early'] && $now < (int) $o['end']) ? (float) ($o['bonus'] ?? 0) : 0;
}

function sp_pao_initials($name) {
    $p = preg_split('/\s+/', trim($name));
    $i = mb_strtoupper(mb_substr($p[0] ?? '', 0, 1)) . mb_strtoupper(mb_substr($p[1] ?? '', 0, 1));
    return $i !== '' ? $i : '•';
}

function sp_pao_card($r, $lab) {
    $days = $r['ts'] ? (int) floor((time() - $r['ts']) / DAY_IN_SECONDS) : 0;
    $sub = $r['k'] === 'offen' ? ($r['shipped'] ? 'Bezahlt · versendet' : 'Bezahlt · wird versendet') : $lab[$r['k']][0];
    ob_start(); ?>
    <div class="it k-<?php echo esc_attr($r['k']); ?>">
      <div class="av"><?php echo esc_html(sp_pao_initials($r['name'])); ?></div>
      <div class="mid">
        <div class="t"><b><?php echo esc_html($r['name'] ?: 'Kunde'); ?></b><span>#<?php echo esc_html($r['nr']); ?> · <?php echo esc_html($r['ts'] ? wp_date('d.m.', $r['ts']) : ''); ?></span></div>
        <?php if ($r['items']) : ?><div class="pr"><?php echo esc_html($r['items']); ?></div><?php endif; ?>
        <div class="st"><i></i><?php echo esc_html($sub); ?></div>
      </div>
      <div class="rt"><b><?php echo esc_html(sp_pao_money($r['prov'])); ?></b><small>von <?php echo esc_html(sp_pao_money($r['total'])); ?></small></div>
      <?php if ($r['k'] === 'wartet') : ?>
      <div class="rm"><span><?php echo $days > 0 ? 'Offen seit ' . $days . ($days === 1 ? ' Tag' : ' Tagen') : 'Heute bestellt'; ?><?php echo $r['last'] ? ' · erinnert am ' . esc_html(wp_date('d.m.', $r['last'])) : ''; ?><?php if (!$r['bacs']) echo ' · Krypto'; ?></span>
        <?php if ($r['bacs']) : ?><?php if ($r['can'] === true) : ?><button type="button" data-o="<?php echo (int) $r['oid']; ?>">Erinnerung schicken</button><?php else : ?><em><?php echo esc_html(ucfirst($r['can'])); ?></em><?php endif; ?><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}

add_action('slicewp_affiliate_account_tab_bestellungen_top', function () {
    $aff = function_exists('slicewp_get_current_affiliate_id') ? (int) slicewp_get_current_affiliate_id() : 0;
    if (!$aff) return;
    global $wpdb;
    $sums = ['paid' => 0, 'unpaid' => 0, 'pending' => 0];
    foreach ($wpdb->get_results($wpdb->prepare("SELECT status, SUM(amount) s FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id = %d AND status IN ('paid','unpaid','pending') GROUP BY status", $aff)) as $s) {
        $sums[$s->status] = (float) $s->s;
    }
    $transfers = function_exists('sp_afp_transfers') ? sp_afp_transfers($aff) : [];
    $sent = array_sum(array_map(function ($t) { return (float) $t['amount']; }, $transfers));
    $earned = $sums['paid'] + $sums['unpaid'];
    $open = round($earned - $sent, 2);
    $rows = sp_pao_rows($aff);
    $pend = $sums['pending'];
    foreach ($rows as $r) if ($r['k'] === 'wartet' && $r['date'] === '') $pend += $r['prov']; // nachgetragene Codes (noch keine Provision angelegt)
    $lab = ['wartet' => ['Wartet auf Zahlung', 'w'], 'offen' => ['Bezahlt · Provision offen', 'o'], 'paid' => ['Provision ausgezahlt', 'p'], 'storno' => ['Storniert', 's']];
    $wait = array_values(array_filter($rows, function ($r) { return $r['k'] === 'wartet'; }));
    $rest = array_values(array_filter($rows, function ($r) { return $r['k'] !== 'wartet'; }));
    $months = [];
    foreach ($rest as $r) { $m = $r['ts'] ? wp_date('Y-m', $r['ts']) : '0000-00'; $months[$m][] = $r; }
    krsort($months);
    $mon = wp_date('Y-m');
    $mN = 0; $mP = 0;
    foreach ($rows as $r) if ($r['ts'] && wp_date('Y-m', $r['ts']) === $mon && $r['k'] !== 'storno') { $mN++; $mP += $r['prov']; }
    $mnames = ['01' => 'Januar', '02' => 'Februar', '03' => 'März', '04' => 'April', '05' => 'Mai', '06' => 'Juni', '07' => 'Juli', '08' => 'August', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember'];
    $bonus = sp_pao_hw_bonus();
    $nonce = wp_create_nonce('sp_pao');
    $last_tr = $transfers ? end($transfers) : null;
    ?>
    <div id="sp-pao">
      <style>
        #sp-pao{font-family:'Sora',sans-serif;color:#0D0F12;--ink:#0D0F12;--mut:#6B7178;--line:#E6E8EB;--soft:#F5F6F7}
        #sp-pao *{box-sizing:border-box}
        #sp-pao .hero{position:relative;overflow:hidden;border-radius:22px;padding:22px 20px 18px;color:#fff;background:radial-gradient(120% 90% at 100% 0%,rgba(199,204,209,.22),transparent 55%),linear-gradient(155deg,#16191D 0%,#0B0D10 100%);box-shadow:0 18px 40px rgba(13,15,18,.18)}
        #sp-pao .hero:after{content:'';position:absolute;right:-60px;top:-60px;width:200px;height:200px;border-radius:50%;border:1px solid rgba(255,255,255,.08)}
        #sp-pao .hero .ey{font-size:11px;font-weight:700;letter-spacing:.16em;color:#9AA1A9;text-transform:uppercase}
        #sp-pao .hero .big{font-size:38px;font-weight:800;letter-spacing:-.02em;margin:6px 0 2px;font-variant-numeric:tabular-nums;background:linear-gradient(180deg,#FFFFFF 0%,#C9CED4 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
        #sp-pao .hero .hint{font-size:12.5px;color:#A9AFB6}
        #sp-pao .hero .row{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:18px}
        #sp-pao .hero .row div{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:10px 10px 9px}
        #sp-pao .hero .row small{display:block;font-size:10.5px;color:#9AA1A9;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
        #sp-pao .hero .row b{display:block;font-size:15px;font-weight:800;margin-top:3px;font-variant-numeric:tabular-nums;white-space:nowrap}
        #sp-pao .goal{display:flex;align-items:center;gap:12px;margin:12px 0 0;padding:12px 14px;border-radius:16px;background:var(--soft);border:1px solid var(--line);font-size:13px}
        #sp-pao .goal .ic{width:36px;height:36px;border-radius:12px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex:0 0 36px}
        #sp-pao .goal b{font-weight:800}#sp-pao .goal .bn{margin-left:auto;background:linear-gradient(90deg,#FF7A1A,#FFB547);color:#140A02;font-weight:800;font-size:11.5px;border-radius:99px;padding:5px 9px;white-space:nowrap}
        #sp-pao h4{font-size:15px;font-weight:800;margin:26px 0 10px;display:flex;align-items:center;gap:8px}
        #sp-pao h4 .n{background:#FFF1DC;color:#9A5B00;font-size:12px;border-radius:99px;padding:2px 8px}
        #sp-pao .it{display:grid;grid-template-columns:42px 1fr auto;gap:12px;align-items:center;padding:13px 14px;border:1px solid var(--line);border-radius:16px;background:#fff;margin-bottom:8px}
        #sp-pao .av{width:42px;height:42px;border-radius:50%;background:linear-gradient(145deg,#E9ECEF,#D3D7DC);color:#30353B;font-weight:800;font-size:13.5px;display:flex;align-items:center;justify-content:center}
        #sp-pao .k-wartet{border-color:#F4D7A8;background:linear-gradient(180deg,#FFFBF4,#fff)}
        #sp-pao .k-wartet .av{background:linear-gradient(145deg,#FFE7C2,#FFD08A);color:#7A4600}
        #sp-pao .k-storno{opacity:.6}
        #sp-pao .mid{min-width:0}
        #sp-pao .mid .t{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}#sp-pao .mid .t b{font-size:14.5px;font-weight:800}#sp-pao .mid .t span{font-size:12px;color:var(--mut)}
        #sp-pao .pr{font-size:12.5px;color:#3E444B;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        #sp-pao .st{display:flex;align-items:center;gap:6px;font-size:11.5px;font-weight:700;margin-top:5px;color:var(--mut)}
        #sp-pao .st i{width:7px;height:7px;border-radius:50%;background:#B9BEC4}
        #sp-pao .k-wartet .st{color:#9A5B00}#sp-pao .k-wartet .st i{background:#F59E0B;box-shadow:0 0 0 3px rgba(245,158,11,.18)}
        #sp-pao .k-offen .st{color:#1F5FA8}#sp-pao .k-offen .st i{background:#3B82F6}
        #sp-pao .k-paid .st{color:#1F7A4D}#sp-pao .k-paid .st i{background:#22A06B}
        #sp-pao .rt{text-align:right}#sp-pao .rt b{display:block;font-size:15px;font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}#sp-pao .rt small{font-size:11px;color:var(--mut);white-space:nowrap}
        #sp-pao .rm{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding-top:10px;border-top:1px dashed #F0DDBB;font-size:12px;color:#7A5A2A}
        #sp-pao .rm button{border:0!important;border-radius:11px;padding:10px 14px;background:#0D0F12!important;color:#fff!important;font:700 12.5px Sora,sans-serif;cursor:pointer;box-shadow:none!important}
        #sp-pao .rm em{font-style:normal;font-size:11.5px;color:#9A8463}
        #sp-pao details.mo{border:1px solid var(--line);border-radius:18px;background:var(--soft);margin-bottom:10px;overflow:hidden}
        #sp-pao details.mo summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:10px;padding:14px 16px;font-size:14px}
        #sp-pao details.mo summary::-webkit-details-marker{display:none}
        #sp-pao details.mo summary b{font-weight:800}#sp-pao details.mo summary span{color:var(--mut);font-size:12.5px}
        #sp-pao details.mo summary .sum{margin-left:auto;font-weight:800;font-variant-numeric:tabular-nums;color:var(--ink)}
        #sp-pao details.mo summary:after{content:'';width:8px;height:8px;border-right:2px solid #8A9097;border-bottom:2px solid #8A9097;transform:rotate(45deg);margin:-4px 2px 0 6px;transition:transform .2s}
        #sp-pao details.mo[open] summary:after{transform:rotate(-135deg);margin-top:4px}
        #sp-pao details.mo .in{padding:0 10px 4px}
        #sp-pao .tgl{display:block;margin:4px auto 10px;background:none!important;border:0!important;color:var(--mut)!important;font:600 12px Sora,sans-serif;cursor:pointer;box-shadow:none!important;text-decoration:underline}
        #sp-pao details.tip{border:1px dashed #D9DCE0;border-radius:14px;padding:0 14px;margin:0 0 10px;font-size:13px;background:#fff}
        #sp-pao details.tip summary{cursor:pointer;padding:11px 0;font-weight:700;list-style:none}#sp-pao details.tip summary::-webkit-details-marker{display:none}
        #sp-pao details.tip .tx{background:var(--soft);border-radius:10px;padding:10px 12px;margin:0 0 8px;color:#22262B;line-height:1.5}
        #sp-pao details.tip button{margin:0 0 12px;border:1px solid var(--line)!important;background:#fff!important;color:var(--ink)!important;border-radius:10px;padding:8px 12px;font:700 12px Sora,sans-serif;cursor:pointer;box-shadow:none!important}
        #sp-pao .tr{display:flex;flex-direction:column;gap:6px}
        #sp-pao .tr div{display:flex;justify-content:space-between;padding:11px 14px;border:1px solid var(--line);border-radius:14px;font-size:13px;background:#fff}#sp-pao .tr div b{font-variant-numeric:tabular-nums}
        #sp-pao .em{font-size:13px;color:var(--mut);padding:18px;border:1px dashed var(--line);border-radius:16px;text-align:center}
        @media (max-width:420px){#sp-pao .hero .big{font-size:34px}#sp-pao .hero .row b{font-size:13.5px}#sp-pao .it{grid-template-columns:38px 1fr auto;gap:10px;padding:12px}#sp-pao .av{width:38px;height:38px;font-size:12.5px}}
      </style>

      <div class="hero">
        <div class="ey"><?php echo $open < 0 ? 'Dein Guthaben' : 'Offen für dich'; ?></div>
        <div class="big"><?php echo esc_html(sp_pao_money(abs($open))); ?></div>
        <div class="hint"><?php echo $open < 0 ? 'Zu viel überwiesen – wird mit der nächsten Provision verrechnet.' : 'Kommt mit der nächsten Auszahlung' . ($last_tr ? ' · letzte am ' . esc_html(wp_date('j. F', strtotime($last_tr['date']))) : '.'); ?></div>
        <div class="row">
          <div><small>Verdient</small><b><?php echo esc_html(sp_pao_money($earned)); ?></b></div>
          <div><small>Ausgezahlt</small><b><?php echo esc_html(sp_pao_money($sent)); ?></b></div>
          <div><small>Ausstehend</small><b><?php echo esc_html(sp_pao_money($pend)); ?></b></div>
        </div>
      </div>
      <div class="goal"><div class="ic">📈</div><div><?php echo esc_html($mnames[wp_date('m')]); ?>: <b><?php echo (int) $mN; ?> Bestellung<?php echo $mN === 1 ? '' : 'en'; ?></b> · <b><?php echo esc_html(sp_pao_money($mP)); ?></b> Provision</div><?php if ($bonus) echo '<span class="bn">🎃 +' . esc_html(rtrim(rtrim(number_format($bonus, 1, ',', ''), '0'), ',')) . ' % Bonus</span>'; ?></div>

      <?php if ($wait) : ?>
      <h4>Wartet auf Zahlung <span class="n"><?php echo count($wait); ?></span></h4>
      <details class="tip"><summary>💡 Story-Text für offene Zahlungen</summary>
        <div class="tx" id="sp-pao-tx">Du hast über meinen Link bei Peptrium bestellt und per Überweisung bezahlt? Schau in dein Postfach (auch in den Spam-Ordner) – dort stehen die Bankdaten. Fragen? Peptrium hilft dir auf Telegram: t.me/peptrium</div>
        <button type="button" id="sp-pao-copy">Text kopieren</button>
      </details>
      <?php foreach ($wait as $r) echo sp_pao_card($r, $lab); ?>
      <?php endif; ?>

      <h4>Alle Bestellungen</h4>
      <?php if ($months) : $first = true; foreach ($months as $m => $list) :
          $vis = array_filter($list, function ($r) { return $r['k'] !== 'storno'; });
          $msum = array_sum(array_column($vis, 'prov'));
          $label = $m === '0000-00' ? 'Ohne Datum' : $mnames[substr($m, 5, 2)] . ' ' . substr($m, 0, 4); ?>
      <details class="mo"<?php echo $first ? ' open' : ''; ?>>
        <summary><b><?php echo esc_html($label); ?></b><span><?php echo count($vis); ?> Bestellung<?php echo count($vis) === 1 ? '' : 'en'; ?></span><span class="sum"><?php echo esc_html(sp_pao_money($msum)); ?></span></summary>
        <div class="in"><?php foreach ($list as $r) echo str_replace('class="it ', 'class="it' . ($r['k'] === 'storno' ? ' sx' : '') . ' ', sp_pao_card($r, $lab)); ?>
          <?php if (count($vis) < count($list)) : ?><button type="button" class="tgl">Stornierte anzeigen (<?php echo count($list) - count($vis); ?>)</button><?php endif; ?>
        </div>
      </details>
      <?php $first = false; endforeach; elseif (!$wait) : ?>
      <p class="em">Noch keine Bestellungen über deinen Link oder Code.</p>
      <?php endif; ?>

      <?php if ($transfers) : ?>
      <h4>Deine Auszahlungen</h4>
      <div class="tr"><?php foreach (array_reverse($transfers) as $t) echo '<div><span>' . esc_html(wp_date('d.m.Y', strtotime($t['date']))) . '</span><b>' . esc_html(sp_pao_money($t['amount'])) . '</b></div>'; ?></div>
      <?php endif; ?>
      <style>#sp-pao .it.sx{display:none}#sp-pao .in.show-sx .it.sx{display:grid}</style>
      <script>
      (function(){var w=document.getElementById('sp-pao');if(!w)return;
       w.querySelectorAll('.tgl').forEach(function(b){b.addEventListener('click',function(){var c=b.parentNode;c.classList.toggle('show-sx');b.textContent=c.classList.contains('show-sx')?'Stornierte ausblenden':b.textContent.replace('ausblenden','anzeigen');});});
       var cp=document.getElementById('sp-pao-copy');if(cp)cp.addEventListener('click',function(){var t=document.getElementById('sp-pao-tx').textContent;var ok=function(){cp.textContent='✓ Kopiert';setTimeout(function(){cp.textContent='Text kopieren'},1800);};if(navigator.clipboard)navigator.clipboard.writeText(t).then(ok,function(){});else{var a=document.createElement('textarea');a.value=t;document.body.appendChild(a);a.select();document.execCommand('copy');a.remove();ok();}});
       w.querySelectorAll('.rm button').forEach(function(b){b.addEventListener('click',function(){if(!confirm('Dem Kunden jetzt die Zahlungserinnerung mit den Bankdaten schicken?'))return;b.disabled=true;b.textContent='Wird gesendet …';
        var fd=new FormData();fd.append('action','sp_pao_remind');fd.append('o',b.getAttribute('data-o'));fd.append('_n','<?php echo esc_js($nonce); ?>');
        fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){var s=document.createElement('em');s.textContent=d&&d.success?'✓ Erinnerung gesendet':(d&&d.data?d.data:'Nicht möglich');b.replaceWith(s);}).catch(function(){b.disabled=false;b.textContent='Erinnerung schicken';});});});
      })();
      </script>
    </div>
    <?php
});

add_action('wp_ajax_sp_pao_remind', function () {
    if (!check_ajax_referer('sp_pao', '_n', false)) wp_send_json_error('Bitte Seite neu laden.');
    $aff = function_exists('slicewp_get_current_affiliate_id') ? (int) slicewp_get_current_affiliate_id() : 0;
    $oid = absint($_POST['o'] ?? 0);
    if (!$aff || !$oid) wp_send_json_error('Nicht möglich.');
    global $wpdb;
    $own = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id = %d AND reference = %s AND status <> 'rejected'", $aff, (string) $oid));
    $nt = (array) get_option('sp_aff_nachtrag', []);
    if (!$own && (int) ($nt[$oid]['aff'] ?? 0) !== $aff) wp_send_json_error('Nicht möglich.');
    $o = wc_get_order($oid);
    if (!$o) wp_send_json_error('Nicht möglich.');
    $can = sp_pao_can_remind($o);
    if ($can !== true) wp_send_json_error(ucfirst($can));
    if (!function_exists('sp_vorkasse_send_payment_reminder') || !sp_vorkasse_send_payment_reminder($o)) wp_send_json_error('Senden fehlgeschlagen – bitte später nochmal.');
    $name = function_exists('sp_afp_affiliate_name') ? sp_afp_affiliate_name($aff) : 'Partner ' . $aff;
    $o->add_order_note('Zahlungserinnerung ausgelöst von Partner ' . $name . ' (Partnerkonto).');
    $log = (array) get_option('sp_pao_log', []);
    $log[] = [time(), $aff, $oid];
    update_option('sp_pao_log', array_slice($log, -200), false);
    wp_send_json_success();
});
