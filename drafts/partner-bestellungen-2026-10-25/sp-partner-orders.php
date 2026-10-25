<?php
/**
 * Partnerkonto → Tab „Bestellungen“ (2026-10-25, User: Partner sollen den Status ihrer Bestellungen sehen).
 * Jeder Partner sieht NUR seine eigenen Bestellungen: Bestellnummer, Datum, Vorname + Initial, Betrag,
 * Provision und Status (wartet auf Zahlung / bezahlt – Provision offen / ausgezahlt / storniert),
 * dazu Verdient / Überwiesen / Offen / Ausstehend (gleiche Rechnung wie Peptrium Dashboard → Partner-Auszahlungen)
 * und die Überweisungen. Keine Kontaktdaten der Kunden.
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
        $r['name'] = trim(trim((string) $o->get_billing_first_name()) . ($ln !== '' ? ' ' . mb_strtoupper(mb_substr($ln, 0, 1)) . '.' : ''));
        $r['total'] = (float) $o->get_total();
        $r['ts'] = $o->get_date_created() ? $o->get_date_created()->getTimestamp() : 0;
        $r['shipped'] = $st === 'completed';
        $r['bacs'] = $o->get_payment_method() === 'bacs';
        $r['last'] = sp_pao_last_reminder($o);
        $r['can'] = $k === 'wartet' ? sp_pao_can_remind($o) : false;
        $out[] = $r;
    }
    usort($out, function ($a, $b) { return $b['ts'] <=> $a['ts']; });
    return $out;
}

function sp_pao_money($v) { return number_format((float) $v, 2, ',', '.') . ' €'; }

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
    $lab = ['wartet' => ['⏳ Wartet auf Zahlung', 'w'], 'offen' => ['✓ Bezahlt · Provision offen', 'o'], 'paid' => ['💸 Ausgezahlt', 'p'], 'storno' => ['✕ Storniert', 's']];
    $cnt = array_count_values(array_column($rows, 'k'));
    $nonce = wp_create_nonce('sp_pao');
    ?>
    <div id="sp-pao">
      <style>
        #sp-pao{font-family:'Sora',sans-serif;color:#0D0F12}
        #sp-pao h3{font-size:18px;font-weight:800;margin:0 0 4px}
        #sp-pao .sub{font-size:13px;color:#5B6169;margin:0 0 16px;line-height:1.5}
        #sp-pao .kp{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:0 0 18px}
        #sp-pao .kp div{background:#F9FAFA;border:1px solid #DCDEE0;border-radius:14px;padding:14px}
        #sp-pao .kp small{display:block;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#5B6169;margin-bottom:6px}
        #sp-pao .kp b{font-size:20px;font-weight:800;font-variant-numeric:tabular-nums}
        #sp-pao .kp .hl{background:#0D0F12;border-color:#0D0F12;color:#fff}#sp-pao .kp .hl small{color:#A9AFB6}
        #sp-pao .kp em{display:block;font-style:normal;font-size:11.5px;color:#8A9097;margin-top:4px;line-height:1.35}#sp-pao .kp .hl em{color:#A9AFB6}
        #sp-pao .fl{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 12px}
        #sp-pao .fl button{border:1px solid #DCDEE0!important;background:#fff!important;color:#0D0F12!important;border-radius:99px;padding:7px 12px;font:700 12.5px Sora,sans-serif;cursor:pointer;box-shadow:none!important}
        #sp-pao .fl button.on{background:#0D0F12!important;color:#fff!important;border-color:#0D0F12!important}
        #sp-pao .ls{display:flex;flex-direction:column;gap:8px;margin:0 0 22px}
        #sp-pao .it{border:1px solid #DCDEE0;border-radius:14px;padding:12px 14px;background:#fff}
        #sp-pao .it .r1{display:flex;justify-content:space-between;gap:10px;align-items:baseline}
        #sp-pao .it .nr{font-weight:800;font-size:14.5px}#sp-pao .it .nm{font-weight:600;color:#3A3F45}
        #sp-pao .it .am{font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
        #sp-pao .it .r2{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-top:6px;font-size:12.5px;color:#5B6169;flex-wrap:wrap}
        #sp-pao .pill{display:inline-block;border-radius:99px;padding:4px 10px;font-size:11.5px;font-weight:700;white-space:nowrap}
        #sp-pao .pill.w{background:#FFF4E0;color:#9A5B00}#sp-pao .pill.o{background:#E8F1FB;color:#1F5FA8}#sp-pao .pill.p{background:#E6F4EC;color:#1F7A4D}#sp-pao .pill.s{background:#F0F0F1;color:#7A7F86}
        #sp-pao .rm{margin-top:10px;padding-top:10px;border-top:1px dashed #E4E6E9;display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap;font-size:12px;color:#5B6169}
        #sp-pao .rm button{border:0!important;border-radius:9px;padding:9px 12px;background:#0D0F12!important;color:#fff!important;font:700 12.5px Sora,sans-serif;cursor:pointer;box-shadow:none!important}
        #sp-pao .rm button[disabled]{opacity:.5;cursor:default}
        #sp-pao .tip{background:#F9FAFA;border:1px solid #DCDEE0;border-radius:14px;padding:14px;margin:0 0 18px;font-size:13px;line-height:1.5}
        #sp-pao .tip b{display:block;margin-bottom:4px}
        #sp-pao .tip .tx{background:#fff;border:1px dashed #C9CDD2;border-radius:10px;padding:10px 12px;margin:8px 0;font-size:13px;color:#22262B}
        #sp-pao .tip button{border:1px solid #DCDEE0!important;background:#fff!important;color:#0D0F12!important;border-radius:9px;padding:7px 12px;font:700 12px Sora,sans-serif;cursor:pointer;box-shadow:none!important}
        #sp-pao .tr{width:100%;border-collapse:collapse;font-size:13px}#sp-pao .tr td{padding:8px 4px;border-bottom:1px solid #EEF0F2}#sp-pao .tr td:last-child{text-align:right;font-weight:700}
        #sp-pao .em{font-size:13px;color:#5B6169;padding:14px;border:1px dashed #DCDEE0;border-radius:14px}
        @media (max-width:700px){#sp-pao .kp{grid-template-columns:1fr 1fr}#sp-pao .kp b{font-size:18px}}
      </style>
      <h3>Deine Bestellungen</h3>
      <p class="sub">Alle Bestellungen über deinen Link oder Code – mit Zahlungs- und Provisionsstatus.</p>
      <div class="kp">
        <div><small>Verdient</small><b><?php echo esc_html(sp_pao_money($earned)); ?></b><em>aus bezahlten Bestellungen</em></div>
        <div><small>Überwiesen</small><b><?php echo esc_html(sp_pao_money($sent)); ?></b><em>schon an dich ausgezahlt</em></div>
        <div class="hl"><small><?php echo $open < 0 ? 'Guthaben' : 'Offen'; ?></small><b><?php echo esc_html(sp_pao_money(abs($open))); ?></b><em><?php echo $open < 0 ? 'zu viel überwiesen, wird verrechnet' : 'kommt mit der nächsten Auszahlung'; ?></em></div>
        <div><small>Ausstehend</small><b><?php echo esc_html(sp_pao_money($pend)); ?></b><em>Kunde hat noch nicht bezahlt</em></div>
      </div>
      <?php if (!empty($cnt['wartet'])) : ?>
      <div class="tip"><b>💡 Tipp für offene Zahlungen</b>Ein kurzer Hinweis in deiner Story erreicht alle, die noch nicht bezahlt haben – ohne dass du jemanden einzeln anschreiben musst:
        <div class="tx" id="sp-pao-tx">Du hast über meinen Link bei Peptrium bestellt und per Überweisung bezahlt? Schau in dein Postfach (auch in den Spam-Ordner) – dort stehen die Bankdaten. Fragen? Peptrium hilft dir auf Telegram: t.me/peptrium</div>
        <button type="button" id="sp-pao-copy">Text kopieren</button>
      </div>
      <?php endif; ?>
      <?php if ($rows) : ?>
      <div class="fl">
        <button type="button" class="on" data-f="">Alle (<?php echo count($rows); ?>)</button>
        <?php foreach ($lab as $k => $l) if (!empty($cnt[$k])) echo '<button type="button" data-f="' . esc_attr($k) . '">' . esc_html($l[0]) . ' (' . (int) $cnt[$k] . ')</button>'; ?>
      </div>
      <div class="ls">
        <?php foreach ($rows as $r) :
            $days = $r['ts'] ? (int) floor((time() - $r['ts']) / DAY_IN_SECONDS) : 0; ?>
        <div class="it" data-k="<?php echo esc_attr($r['k']); ?>">
          <div class="r1"><span><span class="nr">#<?php echo esc_html($r['nr']); ?></span> · <span class="nm"><?php echo esc_html($r['name'] ?: 'Kunde'); ?></span></span><span class="am"><?php echo esc_html(sp_pao_money($r['total'])); ?></span></div>
          <div class="r2"><span><?php echo esc_html($r['ts'] ? wp_date('d.m.Y', $r['ts']) : ''); ?> · Provision <b><?php echo esc_html(sp_pao_money($r['prov'])); ?></b><?php if ($r['k'] === 'offen' || $r['k'] === 'paid') echo $r['shipped'] ? ' · versendet' : ' · wird versendet'; ?></span><span class="pill <?php echo esc_attr($lab[$r['k']][1]); ?>"><?php echo esc_html($lab[$r['k']][0]); ?></span></div>
          <?php if ($r['k'] === 'wartet') : ?>
          <div class="rm"><span><?php echo $days > 0 ? 'seit ' . $days . ($days === 1 ? ' Tag' : ' Tagen') : 'heute bestellt'; ?><?php echo $r['last'] ? ' · letzte Erinnerung ' . esc_html(wp_date('d.m. H:i', $r['last'])) : ''; ?><?php if (!$r['bacs']) echo ' · Zahlung per Krypto'; ?></span>
            <?php if ($r['bacs']) : ?><?php if ($r['can'] === true) : ?><button type="button" data-o="<?php echo (int) $r['oid']; ?>">Zahlungserinnerung schicken</button><?php else : ?><span><?php echo esc_html($r['can']); ?></span><?php endif; ?><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else : ?>
      <p class="em">Noch keine Bestellungen über deinen Link oder Code.</p>
      <?php endif; ?>
      <?php if ($transfers) : ?>
      <h3 style="font-size:16px;margin-top:6px">Deine Auszahlungen</h3>
      <table class="tr"><?php foreach (array_reverse($transfers) as $t) echo '<tr><td>' . esc_html(wp_date('d.m.Y', strtotime($t['date']))) . '</td><td>' . esc_html(sp_pao_money($t['amount'])) . '</td></tr>'; ?></table>
      <?php endif; ?>
      <script>
      (function(){var w=document.getElementById('sp-pao');if(!w)return;
       w.querySelectorAll('.fl button').forEach(function(b){b.addEventListener('click',function(){w.querySelectorAll('.fl button').forEach(function(x){x.classList.remove('on')});b.classList.add('on');var f=b.getAttribute('data-f');w.querySelectorAll('.it').forEach(function(i){i.style.display=(!f||i.getAttribute('data-k')===f)?'':'none';});});});
       var cp=document.getElementById('sp-pao-copy');if(cp)cp.addEventListener('click',function(){var t=document.getElementById('sp-pao-tx').textContent;var ok=function(){cp.textContent='✓ Kopiert';setTimeout(function(){cp.textContent='Text kopieren'},1800);};if(navigator.clipboard)navigator.clipboard.writeText(t).then(ok,function(){});else{var a=document.createElement('textarea');a.value=t;document.body.appendChild(a);a.select();document.execCommand('copy');a.remove();ok();}});
       w.querySelectorAll('.rm button').forEach(function(b){b.addEventListener('click',function(){if(!confirm('Dem Kunden jetzt die Zahlungserinnerung mit den Bankdaten schicken?'))return;b.disabled=true;b.textContent='Wird gesendet …';
        var fd=new FormData();fd.append('action','sp_pao_remind');fd.append('o',b.getAttribute('data-o'));fd.append('_n','<?php echo esc_js($nonce); ?>');
        fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){var s=document.createElement('span');s.textContent=d&&d.success?'✓ Erinnerung gesendet':(d&&d.data?d.data:'Nicht möglich');b.replaceWith(s);}).catch(function(){b.disabled=false;b.textContent='Zahlungserinnerung schicken';});});});
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
