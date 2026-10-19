<?php
/**
 * Peptrium – Wartungsmodus (2026-10-19)
 *
 * Peptrium Dashboard -> Wartungsmodus: schaltet fuer Besucher eine Wartungsseite
 * im Peptrium-Look ein (HTTP 503 + Retry-After, noindex). Admins (manage_woocommerce)
 * sehen den Shop normal mit rotem Hinweisbalken. Schaltet sich nach der gewaehlten
 * Dauer automatisch wieder aus (Option sp_wartung: on, until, text, by).
 * Nicht betroffen: wp-admin, Login, REST/Store-API, admin-ajax, WP-Cron, wc-api
 * (Zahlungs-Callbacks) und ?sp_health=1.
 *
 * Bei einem echten Server-Ausfall kann WordPress nichts anzeigen – dafuer gibt es
 * dieselbe Seite als Cloudflare Worker (drafts/wartungsseite-2026-10-19/).
 * Header X-SP-Wartung: 1 sagt dem Worker, dass diese 503 gewollt ist.
 */
if (!defined('ABSPATH')) exit;

function sp_wartung_get() {
    $o = get_option('sp_wartung', []);
    return is_array($o) ? $o : [];
}

function sp_wartung_active() {
    $o = sp_wartung_get();
    if (empty($o['on'])) return false;
    if (!empty($o['until']) && time() > (int) $o['until']) {
        $o['on'] = 0;
        update_option('sp_wartung', $o, false);
        return false;
    }
    return true;
}

function sp_wartung_default_text() {
    return 'Wir führen gerade Wartungsarbeiten durch. Der Shop ist in wenigen Minuten wieder erreichbar.';
}

function sp_wartung_html() {
    $o = sp_wartung_get();
    $text = !empty($o['text']) ? $o['text'] : sp_wartung_default_text();
    $logo = content_url('uploads/2026/08/IMG_2644-cropped-300x300.png');
    $html = <<<'SPWHTML'
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex">
<title>Gleich wieder da | Peptrium</title>
<style>
:root{--bg:#0B0D10;--card:#12151A;--line:rgba(255,255,255,.08);--tx:#F2F4F6;--mu:#8E959E;--ok:#3DDC84}
*{box-sizing:border-box;margin:0;padding:0}
html{height:100%}
body{background:var(--bg);background-image:radial-gradient(ellipse 90% 60% at 50% -10%,#1C2128 0%,rgba(11,13,16,0) 70%);color:var(--tx);font:16px/1.55 -apple-system,BlinkMacSystemFont,"Segoe UI",Inter,Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;min-height:100%;display:flex;flex-direction:column;padding:28px 16px calc(32px + env(safe-area-inset-bottom))}
.w{width:100%;max-width:460px;text-align:center;margin:auto}
.logo{width:72px;height:auto;display:block;margin:0 auto 10px;animation:fl 5s ease-in-out infinite}
.wm{font-size:13px;letter-spacing:.42em;font-weight:600;color:#C9CED4;margin:0 0 26px;padding-left:.42em}
.pill{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#C9CED4;border:1px solid var(--line);background:rgba(255,255,255,.03);border-radius:99px;padding:7px 14px;margin-bottom:20px}
.dot{width:8px;height:8px;border-radius:50%;background:#F5A524;box-shadow:0 0 0 0 rgba(245,165,36,.6);animation:pu 1.8s infinite}
h1{font-size:34px;line-height:1.15;font-weight:700;letter-spacing:-.02em;margin-bottom:14px}
h1 span{color:var(--mu)}
p.lead{color:#B4BAC2;font-size:16px;margin:0 auto 24px;max-width:400px}
.card{background:var(--card);border:1px solid var(--line);border-radius:20px;padding:20px;text-align:left;margin-bottom:16px}
.row{display:flex;gap:12px;align-items:flex-start;padding:10px 0}
.row+.row{border-top:1px solid var(--line)}
.ic{flex:0 0 32px;height:32px;border-radius:50%;background:rgba(255,255,255,.05);display:flex;align-items:center;justify-content:center}
.ic svg{width:16px;height:16px;stroke:#DDE1E5;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.row b{display:block;font-size:15px;font-weight:600}
.row small{display:block;color:var(--mu);font-size:13.5px;line-height:1.45}
.bar{height:4px;border-radius:4px;background:rgba(255,255,255,.06);overflow:hidden;margin:4px 0 10px}
.bar i{display:block;height:100%;width:0;background:linear-gradient(90deg,#8E959E,#F2F4F6);border-radius:4px}
.cd{font-size:13px;color:var(--mu)}
.btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:52px;border-radius:14px;font-weight:600;font-size:15px;text-decoration:none;cursor:pointer;border:0;font-family:inherit}
.b1{background:#F2F4F6;color:#0B0D10;margin-top:6px}
.b2{background:transparent;color:var(--tx);border:1px solid rgba(255,255,255,.16);margin-top:10px}
.b2 svg{width:18px;height:18px;fill:#2AABEE}
.mail{display:inline-block;margin-top:16px;color:var(--mu);font-size:14px;text-decoration:none;border-bottom:1px solid rgba(142,149,158,.4)}
.ft{margin-top:28px;font-size:12px;color:#5C636B}
@keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
@keyframes pu{0%{box-shadow:0 0 0 0 rgba(245,165,36,.55)}70%{box-shadow:0 0 0 9px rgba(245,165,36,0)}100%{box-shadow:0 0 0 0 rgba(245,165,36,0)}}
@media (prefers-reduced-motion:reduce){.logo,.dot{animation:none}}
@media (max-width:420px){h1{font-size:30px}.card{padding:14px 16px}}
</style>
</head>
<body>
<main class="w">
<img class="logo" src="{{LOGO}}" alt="Peptrium">
<div class="wm">PEPTRIUM</div>
<div class="pill"><span class="dot"></span>Kurze Wartung</div>
<h1>Wir sind gleich<br><span>wieder für dich da.</span></h1>
<p class="lead">{{TEXT}}</p>
<div class="card">
<div class="row"><div class="ic"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg></div><div><b>Deine Daten sind sicher</b><small>Bestellungen, Kundenkonto und Guthaben bleiben unverändert.</small></div></div>
<div class="row"><div class="ic"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg></div><div style="flex:1"><b>Seite lädt automatisch neu</b><div class="bar"><i id="bar"></i></div><span class="cd" id="cd">Nächster Versuch in 60 s</span></div></div>
</div>
<button class="btn b1" type="button" onclick="location.reload()">Jetzt erneut versuchen</button>
<a class="btn b2" href="https://t.me/peptrium"><svg viewBox="0 0 24 24"><path d="M12 0a12 12 0 1 0 0 24 12 12 0 0 0 0-24zm5.6 8.2-1.9 8.9c-.1.6-.5.8-1 .5l-2.9-2.1-1.4 1.3c-.2.2-.3.3-.6.3l.2-3 5.4-4.9c.2-.2 0-.3-.4-.1l-6.7 4.2-2.9-.9c-.6-.2-.6-.6.1-.9l11.3-4.4c.5-.2 1 .1.8 1.1z"/></svg>Fragen? Schreib uns auf Telegram</a>
<a class="mail" href="mailto:info@peptrium.com">info@peptrium.com</a>
<div class="ft">© Peptrium · Alle Produkte ausschließlich für Laborforschungszwecke</div>
</main>
<script>
(function(){var t=60,s=t,b=document.getElementById('bar'),c=document.getElementById('cd');
setInterval(function(){s--;if(s<=0){location.reload();return}b.style.width=((t-s)/t*100)+'%';c.textContent='Nächster Versuch in '+s+' s'},1000);})();
</script>
</body>
</html>

SPWHTML;
    return strtr($html, ['{{LOGO}}' => esc_url($logo), '{{TEXT}}' => esc_html($text)]);
}

function sp_wartung_output() {
    status_header(503);
    nocache_headers();
    header('Retry-After: 600');
    header('X-SP-Wartung: 1');
    header('X-Robots-Tag: noindex');
    header('Content-Type: text/html; charset=utf-8');
    echo sp_wartung_html();
    exit;
}

add_action('template_redirect', function () {
    if (isset($_GET['sp_wartung_vorschau']) && current_user_can('manage_woocommerce')) sp_wartung_output();
    if (isset($_GET['sp_health'])) return;
    if (!sp_wartung_active()) return;
    if (current_user_can('manage_woocommerce')) return;
    sp_wartung_output();
}, -999);

add_action('wp_footer', function () {
    if (!sp_wartung_active() || !current_user_can('manage_woocommerce')) return;
    $o = sp_wartung_get();
    $bis = !empty($o['until']) ? ' bis ' . wp_date('H:i', (int) $o['until']) . ' Uhr' : '';
    echo '<div style="position:fixed;left:0;right:0;bottom:0;z-index:999999;background:#C62828;color:#fff;font:600 13px/1.4 -apple-system,Segoe UI,sans-serif;text-align:center;padding:8px 12px">Wartungsmodus aktiv' . esc_html($bis) . ' – Besucher sehen die Wartungsseite. <a style="color:#fff;text-decoration:underline" href="' . esc_url(admin_url('admin.php?page=sp-wartung')) . '">Ausschalten</a></div>';
});

add_action('admin_bar_menu', function ($bar) {
    if (!sp_wartung_active() || !current_user_can('manage_woocommerce')) return;
    $bar->add_node(['id' => 'sp-wartung', 'title' => '<span style="background:#C62828;padding:2px 8px;border-radius:3px">Wartungsmodus AN</span>', 'href' => admin_url('admin.php?page=sp-wartung')]);
}, 100);

add_action('admin_menu', function () {
    add_submenu_page('peptrium-dashboard', 'Wartungsmodus', 'Wartungsmodus', 'manage_woocommerce', 'sp-wartung', 'sp_wartung_render_page');
}, 60);

function sp_wartung_render_page() {
    if (!current_user_can('manage_woocommerce')) return;
    if (!empty($_POST['sp_wartung_act']) && check_admin_referer('sp_wartung')) {
        $o = sp_wartung_get();
        $o['text'] = sanitize_text_field(wp_unslash($_POST['text'] ?? ''));
        if ($o['text'] === sp_wartung_default_text()) $o['text'] = '';
        if ($_POST['sp_wartung_act'] === 'on') {
            $min = max(0, (int) ($_POST['min'] ?? 60));
            $o['on'] = 1;
            $o['until'] = $min ? time() + $min * 60 : 0;
            $o['by'] = get_current_user_id();
            $o['since'] = time();
        } elseif ($_POST['sp_wartung_act'] === 'off') {
            $o['on'] = 0;
        }
        update_option('sp_wartung', $o, false);
        echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
    }
    $o = sp_wartung_get();
    $on = sp_wartung_active();
    $text = !empty($o['text']) ? $o['text'] : sp_wartung_default_text();
    echo '<div class="wrap" style="max-width:720px"><h1>Wartungsmodus</h1>';
    echo '<div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px;margin:16px 0">';
    if ($on) {
        $bis = !empty($o['until']) ? 'automatisch aus um <b>' . esc_html(wp_date('H:i', (int) $o['until'])) . ' Uhr</b>' : 'bleibt an, bis du ihn ausschaltest';
        echo '<p style="font-size:15px"><span style="display:inline-block;background:#C62828;color:#fff;border-radius:99px;padding:3px 12px;font-weight:600">AN</span> &nbsp;Besucher sehen gerade die Wartungsseite – ' . $bis . '. Du selbst siehst den Shop normal.</p>';
    } else {
        echo '<p style="font-size:15px"><span style="display:inline-block;background:#2E7D32;color:#fff;border-radius:99px;padding:3px 12px;font-weight:600">AUS</span> &nbsp;Der Shop ist normal erreichbar.</p>';
    }
    echo '<form method="post">';
    wp_nonce_field('sp_wartung');
    echo '<p><label><b>Text auf der Seite</b><br><textarea name="text" rows="2" style="width:100%">' . esc_textarea($text) . '</textarea></label></p>';
    if ($on) {
        echo '<p><button class="button button-primary button-hero" name="sp_wartung_act" value="off">Wartungsmodus ausschalten</button> <button class="button" name="sp_wartung_act" value="save">Nur Text speichern</button></p>';
    } else {
        echo '<p><label><b>Automatisch wieder aus nach</b> <select name="min"><option value="30">30 Minuten</option><option value="60" selected>1 Stunde</option><option value="120">2 Stunden</option><option value="240">4 Stunden</option><option value="0">nie (manuell ausschalten)</option></select></label></p>';
        echo '<p><button class="button button-primary button-hero" name="sp_wartung_act" value="on" onclick="return confirm(\'Shop für Besucher jetzt in den Wartungsmodus schalten?\')">Wartungsmodus einschalten</button> <button class="button" name="sp_wartung_act" value="save">Nur Text speichern</button></p>';
    }
    echo '</form>';
    echo '<p><a href="' . esc_url(home_url('/?sp_wartung_vorschau=1')) . '" target="_blank">Vorschau der Wartungsseite ansehen ↗</a></p></div>';
    echo '<div style="background:#f6f7f7;border-radius:10px;padding:16px 20px;color:#50575e"><b>Gut zu wissen</b><ul style="list-style:disc;margin-left:18px">'
        . '<li>Für <b>geplante</b> Arbeiten (Updates, Umbauten). Bezahl-Rückmeldungen (Krypto), Telegram-Bot und Cron laufen weiter.</li>'
        . '<li>Google bekommt „503 – vorübergehend“, das schadet dem Ranking nicht.</li>'
        . '<li>Wenn der <b>Server selbst</b> ausfällt (wie bei der AlexHost-Wartung), kann WordPress nichts anzeigen. Dafür zeigt Cloudflare dieselbe Seite über den Worker „peptrium-wartung“ – falls eingerichtet.</li></ul></div></div>';
}
