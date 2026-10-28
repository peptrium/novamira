<?php
/**
 * Plugin Name: SP Peptid-Rechner v2
 * Description: (2026-10-28, user: "passt, bau den rechner so ein") Neuer Peptid-Rechner auf /peptid-rechner/ (Seite 320).
 * Ersetzt beim Ausgeben das alte Elementor-HTML-Widget 1001014 (Rechner + Text, Daten bleiben unveraendert) durch
 * #sp-rx: Produkt-Chips (live aus WooCommerce), Ergebnis-Karte mit realistischer U-100-Spritze (0 an der Nadel,
 * Fluessigkeit an der Nadel, Vorderkante des Kolbens auf dem Strich), 4 Eingaben (mg, Bac Water, Menge pro Entnahme,
 * Spritze 0,3/0,5/1 ml), Rechenweg, Zubehoer mit Warenkorb, Erklaertext + FAQ (FAQPage JSON-LD).
 * Bewusst KEINE Dosierung nach Koerpergewicht (Forschungs-Rahmen). Teilen-Link: ?mg=&ml=&mcg=&sp=&p=
 * Zuruecksetzen = Datei loeschen + Seiten-Cache leeren.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_RX_PAGE', 320);

function sp_rx_presets() {
    $c = get_transient('sp_rx_presets_v1');
    if (is_array($c)) {
        return $c;
    }
    $skip = array(729, 745, 817, 908, 74, 80);
    $out = array();
    foreach (wc_get_products(array('status' => 'publish', 'limit' => -1, 'visibility' => 'catalog')) as $p) {
        $pid = $p->get_id();
        if (in_array($pid, $skip, true) || stripos($p->get_name(), 'pen') !== false || has_term(20, 'product_cat', $pid)) {
            continue;
        }
        $img = wp_get_attachment_image_url($p->get_image_id(), 'thumbnail') ?: '';
        $rows = array();
        if ($p->is_type('variable')) {
            foreach ($p->get_children() as $vid) {
                $v = wc_get_product($vid);
                if (!$v || $v->get_status() !== 'publish') {
                    continue;
                }
                $rows[] = array($vid, implode(' ', $v->get_variation_attributes()), $v);
            }
        } else {
            $rows[] = array($pid, $p->get_attribute('volumen'), $p);
        }
        foreach ($rows as $r) {
            if (!preg_match('/(\d+(?:[.,]\d+)?)\s*mg/i', $r[1], $m)) {
                continue;
            }
            $mg = (float) str_replace(',', '.', $m[1]);
            $out[] = array(
                'id' => (int) $r[0],
                'pid' => $pid,
                'name' => html_entity_decode($p->get_name(), ENT_QUOTES, 'UTF-8'),
                'mg' => $mg,
                'price' => (float) wc_get_price_to_display($r[2]),
                'buy' => $r[2]->is_purchasable() && $r[2]->is_in_stock(),
                'img' => $img,
                'url' => get_permalink($pid),
            );
        }
    }
    // Reihenfolge: Retatrutide zuerst, dann nach Name / mg
    usort($out, function ($a, $b) {
        $ra = stripos($a['name'], 'retatrutide') === 0 ? 0 : 1;
        $rb = stripos($b['name'], 'retatrutide') === 0 ? 0 : 1;
        return array($ra, $a['name'], $a['mg']) <=> array($rb, $b['name'], $b['mg']);
    });
    set_transient('sp_rx_presets_v1', $out, HOUR_IN_SECONDS);
    return $out;
}
add_action('woocommerce_update_product', function () { delete_transient('sp_rx_presets_v1'); });
add_action('woocommerce_product_set_stock_status', function () { delete_transient('sp_rx_presets_v1'); });

function sp_rx_acc() {
    $o = array();
    foreach (array(74 => 'zum Anmischen', 80 => '1 ml, U-100') as $id => $sub) {
        $p = wc_get_product($id);
        if ($p) {
            $o[] = array('id' => $id, 'name' => html_entity_decode($p->get_name(), ENT_QUOTES, 'UTF-8'), 'sub' => $sub, 'price' => (float) wc_get_price_to_display($p), 'buy' => $p->is_purchasable() && $p->is_in_stock(), 'img' => wp_get_attachment_image_url($p->get_image_id(), 'thumbnail') ?: '');
        }
    }
    return $o;
}

function sp_rx_faq() {
    return array(
        array('Was bedeuten die „Einheiten“ auf der Spritze?', 'Einheiten sind Volumen-Markierungen. Auf einer U-100-Insulinspritze entspricht 1 Einheit genau 0,01 ml – 100 Einheiten sind 1 ml. Die Einheiten sagen also, wie viel Flüssigkeit aufgezogen wird, nicht wie viel Wirkstoff darin ist. Das rechnet der Rechner aus der Konzentration um.'),
        array('Warum steht die 0 an der Nadel?', 'Bei Insulinspritzen beginnt die Skala an der Nadelseite. Die Flüssigkeit sitzt vorne an der Nadel, abgelesen wird an der vorderen Kante des schwarzen Gummikolbens.'),
        array('Ändert sich die Zahl der Einheiten mit der Spritzengröße?', 'Nein. 0,3-, 0,5- und 1-ml-Spritzen (U-100) haben dieselbe Einheit: 10 Einheiten sind immer 0,10 ml. Kleinere Spritzen haben nur feinere Striche und lassen sich bei kleinen Mengen genauer ablesen.'),
        array('Wie viel Bac Water soll ich nehmen?', 'Das bestimmt die Konzentration: mehr Wasser = niedrigere Konzentration = mehr Einheiten pro Entnahme. Mehr Wasser macht kleine Mengen leichter ablesbar. Im Rechner siehst du sofort, wie sich die Wahl auswirkt.'),
        array('Was ist der Unterschied zwischen mg und mcg?', '1 mg = 1000 mcg (Mikrogramm). Im Rechner kannst du die Menge pro Entnahme in mcg oder mg eingeben.'),
        array('Wie lange hält die angemischte Lösung?', 'Das hängt vom Peptid ab. Allgemeine Hinweise zu Temperatur, Licht und Haltbarkeit findest du im Lagerungs-Guide.'),
    );
}

function sp_rx_html() {
    $D = array('presets' => sp_rx_presets(), 'acc' => sp_rx_acc(), 'cur' => html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8'));
    $faq = sp_rx_faq();
    ob_start();
    ?>
<div id="sp-rx">
<style>
#sp-rx{--k:#0D0F12;--g:#2E9B57;--mut:#6B7178;font-family:Sora,sans-serif;color:var(--k);width:100vw;margin-left:calc(50% - 50vw);background:#F2F4F6;padding-bottom:10px}
body.page-id-320 .elementor-element-2f61532>.e-con-inner{padding-top:0!important;padding-bottom:0!important}
#sp-rx *{box-sizing:border-box}#sp-rx p{margin:0}#sp-rx h1,#sp-rx h2,#sp-rx h3{font-family:Sora,sans-serif;color:inherit}
#sp-rx button{font-family:Sora,sans-serif;cursor:pointer;-webkit-tap-highlight-color:transparent}
#sp-rx .rx-intro{background:var(--k);color:#fff;padding:22px 16px 74px}
#sp-rx .rx-in{max-width:1080px;margin:0 auto}
#sp-rx .rx-ey{display:block;font:800 10.5px Sora;letter-spacing:.12em;color:#7DDBA0;text-transform:uppercase;margin:0 0 6px}
#sp-rx h1{font:800 29px/1.08 Sora;margin:6px 0 6px;letter-spacing:-.01em;color:#fff}
#sp-rx .rx-intro p.s{font:500 13.5px/1.5 Sora;color:#A8B0B9;max-width:560px}
#sp-rx .rx-qk{display:flex;gap:7px;overflow-x:auto;margin:16px -16px 0;padding:0 16px 4px;scrollbar-width:none}#sp-rx .rx-qk::-webkit-scrollbar{display:none}
#sp-rx .rx-qk button{all:unset;flex:none;display:inline-flex;align-items:center;gap:7px;border:1px solid #2A2F35;border-radius:999px;padding:5px 13px 5px 5px;font:700 12.5px Sora;background:#15181C;color:#E6E9EC;cursor:pointer;white-space:nowrap}
#sp-rx .rx-qk img{width:28px;height:28px;border-radius:50%;object-fit:cover;background:#000}
#sp-rx .rx-qk button.on{background:#fff;color:var(--k);border-color:#fff}
#sp-rx .rx-qk .o{padding:5px 14px}
#sp-rx .rx-grid{max-width:1080px;margin:-58px auto 0;padding:0 12px;display:grid;gap:14px}
#sp-rx .rx-res{background:#fff;border-radius:26px;padding:18px 14px 14px;box-shadow:0 18px 40px rgba(13,15,18,.14);align-self:start}
#sp-rx .rx-l{font:800 10.5px Sora;letter-spacing:.1em;color:var(--mut);text-transform:uppercase}
#sp-rx .rx-big{font:800 50px/1 Sora;letter-spacing:-.02em;margin:4px 0 3px;font-variant-numeric:tabular-nums}#sp-rx .rx-big small{font:700 17px Sora;color:#5B6169;letter-spacing:0}
#sp-rx .rx-sub{font:600 12.5px Sora;color:#5B6169}
#sp-rx .rx-sy{margin:18px -6px 0;min-height:110px}#sp-rx .rx-sy svg{width:100%;height:auto;display:block;overflow:visible}
#sp-rx .rx-hint{background:#ECF8F1;color:#0B6B3A;border-radius:12px;padding:9px 11px;font:500 12.5px/1.45 Sora;margin:6px 0 0}#sp-rx .rx-hint b{font-weight:800}
#sp-rx .rx-hint.w{background:#FDECEC;color:#A12A2A}
#sp-rx .rx-facts{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:9px}#sp-rx .rx-facts div{background:#F4F5F6;border-radius:14px;padding:9px 4px;text-align:center;font:800 14.5px Sora;font-variant-numeric:tabular-nums}#sp-rx .rx-facts small{display:block;font:600 10px Sora;color:var(--mut);margin-top:1px}
#sp-rx .rx-h{font:800 11px Sora;letter-spacing:.1em;color:var(--mut);margin:0 4px 8px;text-transform:uppercase}
#sp-rx .rx-card{background:#fff;border-radius:20px;padding:13px 14px;margin:0 0 8px}
#sp-rx .rx-r{display:flex;align-items:center;gap:10px}
#sp-rx .rx-n{width:26px;height:26px;border-radius:50%;background:var(--k);color:#fff;font:800 12px/26px Sora;text-align:center;flex:none}
#sp-rx .rx-t{flex:1;font:700 14px Sora;min-width:0}#sp-rx .rx-t small{display:block;font:500 11.5px Sora;color:var(--mut)}
#sp-rx .rx-stp{display:flex;align-items:center;gap:4px;flex:none}
#sp-rx .rx-stp button{all:unset;width:38px;height:38px;border-radius:12px;background:#F2F4F5;display:flex;align-items:center;justify-content:center;font:600 21px Sora;color:var(--k);cursor:pointer}
#sp-rx .rx-stp button:active{background:#E3E6E9}
#sp-rx .rx-v{display:flex;align-items:baseline;justify-content:center;min-width:82px}
#sp-rx .rx-v input{all:unset;width:56px;text-align:right;font:800 18px Sora;color:var(--k);font-variant-numeric:tabular-nums;border-bottom:1.5px dashed transparent}
#sp-rx .rx-v input:focus{border-bottom-color:#9AA0A6}
#sp-rx .rx-v span{font:700 13px Sora;color:#5B6169;margin-left:4px}
#sp-rx .rx-ch{display:flex;gap:6px;margin:10px 0 0 36px}
#sp-rx .rx-ch button{all:unset;flex:1;text-align:center;border:1px solid #E3E6E9;border-radius:10px;padding:8px 0;font:700 12.5px Sora;color:#4B5157;cursor:pointer}
#sp-rx .rx-ch button.on{background:var(--k);color:#fff;border-color:var(--k)}
#sp-rx .rx-unit{display:inline-flex;background:#F2F4F5;border-radius:999px;padding:2px;margin-left:6px;vertical-align:middle}
#sp-rx .rx-unit button{all:unset;padding:2px 8px;border-radius:999px;font:700 10.5px Sora;color:#5B6169;cursor:pointer}#sp-rx .rx-unit button.on{background:#fff;color:var(--k);box-shadow:0 1px 3px rgba(0,0,0,.12)}
#sp-rx .rx-sz{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:10px 0 0 36px}
#sp-rx .rx-sz button{all:unset;border:1.5px solid #E3E6E9;border-radius:12px;padding:9px 4px;text-align:center;font:800 13px Sora;cursor:pointer}
#sp-rx .rx-sz small{display:block;font:600 10px Sora;color:var(--mut)}
#sp-rx .rx-sz i{display:block;height:6px;margin:0 auto 7px;border-radius:3px;background:#D5DADF;position:relative}#sp-rx .rx-sz i:before{content:'';position:absolute;left:-9px;top:2px;width:9px;height:2px;background:#9AA3AC}
#sp-rx .rx-sz button.on{border-color:var(--k);box-shadow:inset 0 0 0 1px var(--k)}
#sp-rx .rx-act{display:flex;gap:8px;margin:2px 0 0}
#sp-rx .rx-act button{all:unset;flex:1;background:#fff;border-radius:14px;padding:12px;text-align:center;font:700 12.5px Sora;cursor:pointer}
#sp-rx .rx-calc{background:var(--k);color:#fff;border-radius:20px;padding:6px 14px}
#sp-rx .rx-calc div{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #22262B}#sp-rx .rx-calc div:last-child{border:0}
#sp-rx .rx-calc i{font-style:normal;width:28px;height:28px;border-radius:9px;background:#1C2025;display:flex;align-items:center;justify-content:center;font:800 12px Sora;color:#7DDBA0;flex:none}
#sp-rx .rx-calc span{flex:1;font:500 12.5px Sora;color:#A8B0B9}#sp-rx .rx-calc b{font:800 13.5px Sora;white-space:nowrap}
#sp-rx .rx-acc{background:#fff;border-radius:20px;padding:4px 14px}
#sp-rx .rx-a{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #EEF0F2}#sp-rx .rx-a:last-child{border:0}
#sp-rx .rx-a img{width:46px;height:46px;border-radius:12px;background:var(--k);object-fit:cover}
#sp-rx .rx-a .an{flex:1;font:700 13px Sora;min-width:0}#sp-rx .rx-a .an small{display:block;font:500 11px Sora;color:#5B6169}
#sp-rx .rx-a a{color:inherit;text-decoration:none}
#sp-rx .rx-a button{all:unset;width:38px;height:38px;border-radius:50%;background:var(--k);color:#fff;font:600 21px/38px Sora;text-align:center;cursor:pointer;flex:none}
#sp-rx .rx-a button.ok{background:var(--g)}
#sp-rx .rx-all{all:unset;box-sizing:border-box;margin-top:8px;width:100%;height:52px;border-radius:15px;background:var(--k);color:#fff;display:flex;align-items:center;justify-content:center;font:800 14px Sora;cursor:pointer}
#sp-rx .rx-all[disabled]{opacity:.6}
#sp-rx .rx-txt{background:#fff;border-radius:20px;padding:16px}
#sp-rx .rx-txt h2{font:800 19px/1.25 Sora;margin:0 0 8px}#sp-rx .rx-txt h3{font:800 15px Sora;margin:14px 0 4px}
#sp-rx .rx-txt p{font:500 13.5px/1.6 Sora;color:#3A3F45;margin:0 0 6px}
#sp-rx .rx-txt a{color:var(--k);font-weight:700}
#sp-rx .rx-fq{background:#fff;border-radius:20px;padding:2px 14px}
#sp-rx .rx-fq details{border-bottom:1px solid #EEF0F2}#sp-rx .rx-fq details:last-child{border:0}
#sp-rx .rx-fq summary{list-style:none;display:flex;justify-content:space-between;gap:10px;padding:14px 0;font:700 13.5px Sora;cursor:pointer}#sp-rx .rx-fq summary::-webkit-details-marker{display:none}
#sp-rx .rx-fq summary:after{content:'+';color:#9AA0A6;font-weight:600}#sp-rx .rx-fq details[open] summary:after{content:'−'}
#sp-rx .rx-fq details p{font:500 13px/1.6 Sora;color:#3A3F45;padding:0 0 14px}
#sp-rx .rx-lk{display:flex;gap:8px;flex-wrap:wrap}#sp-rx .rx-lk a{background:#fff;border-radius:999px;padding:10px 14px;font:700 12.5px Sora;color:var(--k);text-decoration:none}
#sp-rx .rx-dis{font:500 11px/1.5 Sora;color:var(--mut);text-align:center;padding:18px 12px 10px}
#sp-rx .rx-toast{position:fixed;left:50%;bottom:calc(20px + env(safe-area-inset-bottom));transform:translate(-50%,140%);background:var(--k);color:#fff;border-radius:999px;padding:11px 18px;font:700 13px Sora;z-index:100000;transition:transform .3s;white-space:nowrap}
#sp-rx .rx-toast.on{transform:translate(-50%,0)}
@media(min-width:900px){
 #sp-rx .rx-intro{padding:34px 24px 90px}#sp-rx h1{font-size:38px}
 #sp-rx .rx-qk{margin:18px 0 0;padding:0;flex-wrap:wrap}
 #sp-rx .rx-grid{grid-template-columns:minmax(0,1.05fr) minmax(0,1fr);padding:0 24px;margin-top:-70px;align-items:start}
 #sp-rx .rx-res{position:sticky;top:120px;grid-column:2;grid-row:1/span 6;padding:24px 22px 20px}
 #sp-rx .rx-left{grid-column:1}
}
</style>
<div class="rx-intro"><div class="rx-in">
<h1><span class="rx-ey">● Peptid-Rechner</span>Wie viele Einheiten aufziehen?</h1>
<p class="s">Produkt antippen – alles andere ist schon eingetragen. Das Ergebnis rechnet bei jeder Änderung sofort mit.</p>
<div class="rx-qk" id="rx-qk"></div>
</div></div>
<div class="rx-grid">
<div class="rx-res" id="rx-res" aria-live="polite">
<p class="rx-l">Aufziehen bis</p>
<p class="rx-big"><span id="rx-u">10</span> <small>Einheiten</small></p>
<p class="rx-sub" id="rx-usub"></p>
<div class="rx-sy" id="rx-sy"></div>
<p class="rx-hint" id="rx-hint"></p>
<div class="rx-facts"><div><span id="rx-c"></span><small>Konzentration</small></div><div><span id="rx-vol"></span><small>pro Entnahme</small></div><div><span id="rx-n"></span><small>pro Ampulle</small></div></div>
</div>
<div class="rx-left">
<p class="rx-h">Deine Angaben</p>
<div class="rx-card"><div class="rx-r"><span class="rx-n">1</span><span class="rx-t">Inhalt der Ampulle<small>steht auf dem Etikett</small></span>
<span class="rx-stp"><button type="button" data-k="mg" data-d="-1" aria-label="weniger">−</button><span class="rx-v"><input id="rx-mg" inputmode="decimal" aria-label="mg in der Ampulle"><span>mg</span></span><button type="button" data-k="mg" data-d="1" aria-label="mehr">+</button></span></div>
<div class="rx-ch" data-k="mg"></div></div>
<div class="rx-card"><div class="rx-r"><span class="rx-n">2</span><span class="rx-t">Bac Water dazu<small>Menge zum Anmischen</small></span>
<span class="rx-stp"><button type="button" data-k="ml" data-d="-1" aria-label="weniger">−</button><span class="rx-v"><input id="rx-ml" inputmode="decimal" aria-label="ml Bac Water"><span>ml</span></span><button type="button" data-k="ml" data-d="1" aria-label="mehr">+</button></span></div>
<div class="rx-ch" data-k="ml"></div></div>
<div class="rx-card"><div class="rx-r"><span class="rx-n">3</span><span class="rx-t">Menge pro Entnahme<small><span class="rx-unit" id="rx-unit"><button type="button" data-u="mcg" class="on">mcg</button><button type="button" data-u="mg">mg</button></span></small></span>
<span class="rx-stp"><button type="button" data-k="amt" data-d="-1" aria-label="weniger">−</button><span class="rx-v"><input id="rx-amt" inputmode="decimal" aria-label="Menge pro Entnahme"><span id="rx-amtu">mcg</span></span><button type="button" data-k="amt" data-d="1" aria-label="mehr">+</button></span></div>
<div class="rx-ch" data-k="amt"></div></div>
<div class="rx-card"><div class="rx-r"><span class="rx-n">4</span><span class="rx-t">Deine Spritze<small>U-100 · Skala passt sich an</small></span></div>
<div class="rx-sz" id="rx-sz"><button type="button" data-s="0.3"><i style="width:34px"></i>0,3 ml<small>30 Einheiten</small></button><button type="button" data-s="0.5"><i style="width:44px"></i>0,5 ml<small>50 Einheiten</small></button><button type="button" data-s="1"><i style="width:58px"></i>1 ml<small>100 Einheiten</small></button></div></div>
<div class="rx-act"><button type="button" id="rx-share">↗ Ergebnis teilen</button><button type="button" id="rx-reset">↺ Zurücksetzen</button></div>
<p class="rx-h" style="margin-top:18px">So wird gerechnet</p>
<div class="rx-calc" id="rx-calc"></div>
<p class="rx-h" style="margin-top:18px">Das brauchst du dafür</p>
<div class="rx-acc" id="rx-acc"></div>
<button type="button" class="rx-all" id="rx-all"></button>
<div class="rx-txt" style="margin-top:18px">
<h2>Peptid-Rechner – so funktioniert er</h2>
<p>Forschungspeptide werden als gefriergetrocknetes Pulver (Lyophilisat) geliefert. Für die Vorbereitung wird das Pulver mit bakteriostatischem Wasser (<a href="/produkt/bac-water/">Bac Water</a>) gelöst. Wie viel Wasser du zugibst, bestimmt die Konzentration der Lösung – und damit, wie viel Flüssigkeit du für eine bestimmte Menge aufziehen musst.</p>
<h3>Die Rechnung in drei Schritten</h3>
<p><b>1.</b> Konzentration = Inhalt der Ampulle (mg) ÷ Bac Water (ml).<br><b>2.</b> Volumen = gewünschte Menge (mg) ÷ Konzentration.<br><b>3.</b> Einheiten = Volumen (ml) × 100 – auf einer U-100-Spritze ist 1 Einheit = 0,01 ml.</p>
<h3>Richtig ablesen</h3>
<p>Auf Insulinspritzen steht die 0 an der Nadelseite. Die Flüssigkeit sitzt vorne an der Nadel, abgelesen wird an der vorderen Kante des schwarzen Kolbens. 0,3-, 0,5- und 1-ml-Spritzen nutzen dieselbe Einheit – kleinere Spritzen haben nur feinere Striche.</p>
<p>Mehr dazu im Ratgeber: <a href="/ratgeber/peptide-rekonstituieren/">Peptide richtig rekonstituieren</a> · <a href="/ratgeber/bac-water-bakteriostatisches-wasser/">Bac Water erklärt</a></p>
</div>
<p class="rx-h" style="margin-top:18px">Häufige Fragen</p>
<div class="rx-fq"><?php foreach ($faq as $f) : ?><details><summary><?php echo esc_html($f[0]); ?></summary><p><?php echo esc_html($f[1]); ?></p></details><?php endforeach; ?></div>
<div class="rx-lk" style="margin-top:14px"><a href="/retatrutide-dosierung-rechner/">Retatrutide-Rechner →</a><a href="/lagerungs-guide/">Lagerungs-Guide →</a><a href="/alle-produkte/">Alle Produkte →</a></div>
<p class="rx-dis">Reine Rechenhilfe für die Vorbereitung im Labor – nicht für den menschlichen oder veterinärmedizinischen Gebrauch. Alle Produkte sind ausschließlich für Forschungszwecke bestimmt.</p>
</div></div>
<div class="rx-toast" id="rx-toast"></div>
<script type="application/ld+json"><?php
    $ld = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array());
    foreach ($faq as $f) {
        $ld['mainEntity'][] = array('@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $f[1]));
    }
    echo wp_json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?></script>
<script>
(function(){
var D=<?php echo wp_json_encode($D, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
var $=function(i){return document.getElementById(i);};
var LIST={mg:[1,2,5,10,15,20,30,40,50,60,70,80,100],ml:[0.5,1,1.5,2,2.5,3,4,5,6,8,10],amt:[25,50,75,100,150,200,250,300,400,500,600,750,1000,1250,1500,2000,2500,3000,4000,5000,7500,10000]};
var CH={mg:[5,10,20,50],ml:[1,2,3,5],amt:[100,250,500,1000,2000]};
var DEF={mg:10,ml:2,amt:500,sp:1,p:0};
var S={mg:10,ml:2,amt:500,sp:1,p:0,unit:'mcg'};
function de(n,d){return n.toLocaleString('de-DE',{minimumFractionDigits:0,maximumFractionDigits:d});}
function num(v){v=String(v).replace(/\s/g,'').replace(',','.');var n=parseFloat(v);return isFinite(n)?n:NaN;}
function money(v){return v.toLocaleString('de-DE',{minimumFractionDigits:2,maximumFractionDigits:2})+' '+D.cur;}
/* ---- Spritze (SVG) ---- */
var SP={1:{max:100,minor:2,num:10,ml:'1 ml'},0.5:{max:50,minor:1,num:5,ml:'0,5 ml'},0.3:{max:30,minor:1,num:5,ml:'0,3 ml'}};
var uid=0;
function syr(units,size){
 uid++;var k='rx'+uid,sp=SP[size],mx=sp.max,over=units>mx,u=Math.max(0,Math.min(units,mx));
 var L=230,nd=46,hub=16,x0=nd+hub+8,bx=nd+hub,bend=x0+L+10,y=58,h=34,mark=x0+L*u/mx,sw=12,thumb=mark+sw+(bend-x0)+6,tot=thumb+14;
 var s='<svg viewBox="0 0 '+Math.round(Math.max(tot,bend+40))+' 140" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Spritze bis '+de(units,1)+' Einheiten">';
 s+='<defs><linearGradient id="b'+k+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".95"/><stop offset=".45" stop-color="#EEF3F6" stop-opacity=".75"/><stop offset="1" stop-color="#D8E0E6" stop-opacity=".9"/></linearGradient>'
 +'<linearGradient id="l'+k+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'+(over?'#FAD4D4':'#D7F5E3')+'"/><stop offset=".5" stop-color="'+(over?'#F09A9A':'#9FE2BA')+'"/><stop offset="1" stop-color="'+(over?'#D95C5C':'#6CCB95')+'"/></linearGradient>'
 +'<linearGradient id="r'+k+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3A3F45"/><stop offset=".5" stop-color="#15181B"/><stop offset="1" stop-color="#2A2F35"/></linearGradient>'
 +'<linearGradient id="p'+k+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#E2E6EA"/></linearGradient>'
 +'<linearGradient id="n'+k+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E9ECEF"/><stop offset=".5" stop-color="#9AA3AC"/><stop offset="1" stop-color="#D5DADF"/></linearGradient>'
 +'<clipPath id="c'+k+'"><rect x="'+(bx+1)+'" y="'+(y+1)+'" width="'+(bend-bx-2)+'" height="'+(h-2)+'" rx="5"/></clipPath>'
 +'<filter id="f'+k+'" x="-10%" y="-30%" width="120%" height="180%"><feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000" flood-opacity=".16"/></filter></defs>';
 s+='<rect x="2" y="'+(y+h/2-1.3)+'" width="'+nd+'" height="2.6" rx="1.3" fill="url(#n'+k+')"/>';
 s+='<path d="M'+nd+' '+(y+h/2-5)+' L'+(nd+hub)+' '+(y+5)+' L'+(nd+hub)+' '+(y+h-5)+' L'+nd+' '+(y+h/2+5)+'Z" fill="#F29A4A" opacity=".85"/>';
 s+='<g filter="url(#f'+k+')"><rect x="'+(mark+sw)+'" y="'+(y+h/2-6)+'" width="'+(thumb-mark-sw)+'" height="12" fill="url(#p'+k+')" stroke="#C9CED4"/>';
 s+='<rect x="'+thumb+'" y="'+(y-8)+'" width="8" height="'+(h+16)+'" rx="3" fill="url(#p'+k+')" stroke="#C9CED4"/>';
 s+='<rect x="'+bx+'" y="'+y+'" width="'+(bend-bx)+'" height="'+h+'" rx="6" fill="url(#b'+k+')" stroke="#AEB6BE" stroke-width="1.4"/></g>';
 s+='<g clip-path="url(#c'+k+')"><rect x="'+bx+'" y="'+y+'" width="'+(mark-bx)+'" height="'+h+'" fill="url(#l'+k+')"/><rect x="'+bx+'" y="'+(y+3)+'" width="'+(mark-bx)+'" height="5" fill="#fff" opacity=".45"/>';
 s+='<rect x="'+mark+'" y="'+y+'" width="'+sw+'" height="'+h+'" fill="url(#r'+k+')"/><rect x="'+(mark+3.5)+'" y="'+y+'" width="1.6" height="'+h+'" fill="#000" opacity=".35"/><rect x="'+(mark+7.5)+'" y="'+y+'" width="1.6" height="'+h+'" fill="#000" opacity=".35"/></g>';
 s+='<rect x="'+(bend-3)+'" y="'+(y-14)+'" width="7" height="'+(h+28)+'" rx="3" fill="#E9EDF0" stroke="#AEB6BE"/>';
 for(var t=0;t<=mx;t+=sp.minor){var x=x0+L*t/mx,big=t%sp.num===0;
  s+='<line x1="'+x.toFixed(2)+'" x2="'+x.toFixed(2)+'" y1="'+(y+1)+'" y2="'+(y+1+(big?13:7))+'" stroke="#1d2125" stroke-width="'+(big?1.3:.8)+'"/>';
  if(big)s+='<text x="'+x.toFixed(2)+'" y="'+(y+h+13)+'" font-family="Sora,sans-serif" font-size="9.5" font-weight="700" text-anchor="middle" fill="#1d2125">'+t+'</text>';}
 s+='<rect x="'+(bx+3)+'" y="'+(y+2)+'" width="'+(bend-bx-6)+'" height="4" rx="2" fill="#fff" opacity=".7"/>';
 var col=over?'#D93636':'#2E9B57',lbl=over?'> '+mx+' E':de(units,1)+' E',lw=Math.max(54,lbl.length*8+16);
 s+='<line x1="'+mark.toFixed(2)+'" x2="'+mark.toFixed(2)+'" y1="'+(y-16)+'" y2="'+(y+h+4)+'" stroke="'+col+'" stroke-width="1.6"/>';
 s+='<rect x="'+(mark-lw/2).toFixed(2)+'" y="'+(y-38)+'" width="'+lw+'" height="22" rx="11" fill="'+col+'"/><text x="'+mark.toFixed(2)+'" y="'+(y-23)+'" font-family="Sora,sans-serif" font-size="11.5" font-weight="800" text-anchor="middle" fill="#fff">'+lbl+'</text>';
 s+='<text x="'+(x0-2)+'" y="'+(y+h+30)+'" font-family="Sora,sans-serif" font-size="9" font-weight="600" fill="#8E959E">0 = Nadelseite</text>';
 s+='<text x="'+bend+'" y="'+(y+h+30)+'" font-family="Sora,sans-serif" font-size="9" font-weight="600" text-anchor="end" fill="#8E959E">'+sp.ml+' · U-100</text>';
 return s+'</svg>';
}
/* ---- Aufbau ---- */
var qk=$('rx-qk'),h='';
D.presets.forEach(function(p,i){h+='<button type="button" data-i="'+i+'">'+(p.img?'<img src="'+p.img+'" alt="" loading="lazy">':'')+p.name+' '+de(p.mg,1)+' mg</button>';});
h+='<button type="button" class="o" data-i="-1">Anderes Peptid</button>';qk.innerHTML=h;
['mg','ml','amt'].forEach(function(k){var c=document.querySelector('#sp-rx .rx-ch[data-k="'+k+'"]');c.innerHTML=CH[k].map(function(v){return '<button type="button" data-v="'+v+'">'+(k==='amt'?de(v,0):de(v,1))+'</button>';}).join('');});
function step(k,d){var l=LIST[k],v=S[k],i;
 if(d>0){for(i=0;i<l.length;i++)if(l[i]>v+1e-9){S[k]=l[i];return;}S[k]=l[l.length-1];}
 else{for(i=l.length-1;i>=0;i--)if(l[i]<v-1e-9){S[k]=l[i];return;}S[k]=l[0];}}
function fmtAmt(){return S.unit==='mg'?de(S.amt/1000,3):de(S.amt,0);}
function render(){
 var conc=S.mg/S.ml, amg=S.amt/1000, vol=amg/conc, units=vol*100, per=S.mg/amg, sp=SP[S.sp];
 var ok=isFinite(units)&&units>0;
 $('rx-mg').value=de(S.mg,2);$('rx-ml').value=de(S.ml,2);$('rx-amt').value=fmtAmt();$('rx-amtu').textContent=S.unit;
 $('rx-u').textContent=ok?de(units,units<10?2:1):'–';
 $('rx-usub').textContent=ok?('= '+de(vol,3)+' ml · '+sp.ml.replace(' ','-')+'-Spritze (U-100)'):'';
 $('rx-sy').innerHTML=ok?syr(Math.round(units*100)/100,S.sp):'';
 var hn=$('rx-hint');hn.className='rx-hint';
 if(!ok){hn.textContent='Bitte alle Werte eingeben.';hn.className='rx-hint w';}
 else if(units>sp.max){var fit=null;[0.3,0.5,1].forEach(function(z){if(!fit&&units<=SP[z].max)fit=z;});
  hn.innerHTML='<b>Passt nicht in die '+sp.ml.replace(' ','-')+'-Spritze.</b> '+(fit?'Wähle die '+SP[fit].ml.replace(' ','-')+'-Spritze oder gib weniger Bac Water dazu.':'Gib weniger Bac Water dazu oder teile die Menge auf zwei Entnahmen auf.');hn.className='rx-hint w';}
 else if(units<2){hn.innerHTML='<b>Sehr kleine Menge</b> – schwer genau abzulesen. Mit mehr Bac Water wird die Zahl größer und genauer.';hn.className='rx-hint w';}
 else{var lo=Math.floor(units/sp.minor+1e-9)*sp.minor,hi=lo+sp.minor,exact=Math.abs(units-Math.round(units/sp.minor)*sp.minor)<0.05;
  hn.innerHTML='<b>So ablesen:</b> Die vordere Kante des schwarzen Kolbens steht '+(exact?'genau auf dem Strich <b>'+de(Math.round(units/sp.minor)*sp.minor,0)+'</b>.':'bei <b>'+de(units,1)+'</b> – zwischen den Strichen '+de(lo,0)+' und '+de(hi,0)+'.');}
 $('rx-c').textContent=ok?de(conc,2)+' mg/ml':'–';$('rx-vol').textContent=ok?de(vol,3)+' ml':'–';$('rx-n').textContent=ok?de(Math.floor(per+1e-9),0)+'×':'–';
 $('rx-calc').innerHTML=ok?[
  [de(S.mg,2)+' mg Pulver ÷ '+de(S.ml,2)+' ml Bac Water','= '+de(conc,2)+' mg/ml'],
  [de(amg,3)+' mg ('+de(S.amt,0)+' mcg) ÷ '+de(conc,2)+' mg/ml','= '+de(vol,3)+' ml'],
  [de(vol,3)+' ml × 100 (U-100-Skala)','= '+de(units,1)+' Einheiten'],
  [de(S.mg,2)+' mg ÷ '+de(amg,3)+' mg','= '+de(Math.floor(per+1e-9),0)+'× pro Ampulle']].map(function(r,i){return '<div><i>'+(i+1)+'</i><span>'+r[0]+'</span><b>'+r[1]+'</b></div>';}).join(''):'';
 [].forEach.call(qk.children,function(b){var i=+b.getAttribute('data-i');b.classList.toggle('on',i===-1?S.p===-1:(D.presets[i]&&D.presets[i].id===S.p));});
 ['mg','ml','amt'].forEach(function(k){[].forEach.call(document.querySelectorAll('#sp-rx .rx-ch[data-k="'+k+'"] button'),function(b){b.classList.toggle('on',Math.abs(+b.getAttribute('data-v')-S[k])<1e-9);});});
 [].forEach.call($('rx-sz').children,function(b){b.classList.toggle('on',+b.getAttribute('data-s')===S.sp);});
 [].forEach.call($('rx-unit').children,function(b){b.classList.toggle('on',b.getAttribute('data-u')===S.unit);});
 acc();
}
/* ---- Zubehoer ---- */
function cur(){for(var i=0;i<D.presets.length;i++)if(D.presets[i].id===S.p)return D.presets[i];return null;}
function acc(){
 var p=cur(),items=[];if(p)items.push({id:p.id,name:p.name+' '+de(p.mg,1)+' mg',sub:money(p.price),img:p.img,buy:p.buy,url:p.url});
 D.acc.forEach(function(a){items.push({id:a.id,name:a.name,sub:money(a.price)+' · '+a.sub,img:a.img,buy:a.buy});});
 var k=items.map(function(x){return x.id;}).join(',');if($('rx-acc').getAttribute('data-k')===k)return;$('rx-acc').setAttribute('data-k',k);
 $('rx-acc').innerHTML=items.map(function(x){return '<div class="rx-a">'+(x.img?'<img src="'+x.img+'" alt="" loading="lazy">':'')+'<span class="an">'+(x.url?'<a href="'+x.url+'">'+x.name+'</a>':x.name)+'<small>'+x.sub+'</small></span>'+(x.buy?'<button type="button" data-add="'+x.id+'" aria-label="In den Warenkorb">+</button>':'<small style="font:600 11px Sora;color:#6B7178">bald</small>')+'</div>';}).join('');
 var buy=items.filter(function(x){return x.buy;}),sum=0;buy.forEach(function(x){var pr=x.id===(p&&p.id)?p.price:(D.acc.filter(function(a){return a.id===x.id;})[0]||{}).price||0;sum+=pr;});
 var all=$('rx-all');all.style.display=buy.length>1?'':'none';all.textContent='Alle '+buy.length+' in den Warenkorb · '+money(sum);all.setAttribute('data-ids',buy.map(function(x){return x.id;}).join(','));
}
function add(ids,btns){
 var i=0;btns.forEach(function(b){b.disabled=true;});
 (function next(){if(i>=ids.length){btns.forEach(function(b){b.disabled=false;});if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[]);toast('Im Warenkorb ✓');return;}
  var f=new FormData();f.append('product_id',ids[i]);f.append('quantity','1');
  fetch('/?wc-ajax=add_to_cart',{method:'POST',body:f,credentials:'same-origin'}).then(function(r){return r.json();}).catch(function(){}).then(function(){var b=document.querySelector('#rx-acc button[data-add="'+ids[i]+'"]');if(b){b.textContent='✓';b.classList.add('ok');}i++;next();});
 })();
}
function toast(t){var e=$('rx-toast');e.textContent=t;e.classList.add('on');clearTimeout(toast.t);toast.t=setTimeout(function(){e.classList.remove('on');},2200);}
/* ---- Ereignisse ---- */
document.getElementById('sp-rx').addEventListener('click',function(e){
 var b=e.target.closest('button');if(!b)return;
 if(b.hasAttribute('data-i')){var i=+b.getAttribute('data-i');if(i<0){S.p=-1;$('rx-mg').focus();}else{var p=D.presets[i];S.p=p.id;S.mg=p.mg;}render();return;}
 if(b.hasAttribute('data-d')){step(b.getAttribute('data-k'),+b.getAttribute('data-d'));if(b.getAttribute('data-k')==='mg')matchP();render();return;}
 if(b.hasAttribute('data-v')){var k=b.parentNode.getAttribute('data-k');S[k]=+b.getAttribute('data-v');if(k==='mg')matchP();render();return;}
 if(b.hasAttribute('data-s')){S.sp=+b.getAttribute('data-s');render();return;}
 if(b.hasAttribute('data-u')){S.unit=b.getAttribute('data-u');render();return;}
 if(b.hasAttribute('data-add')){add([+b.getAttribute('data-add')],[b]);return;}
 if(b.id==='rx-all'){add(b.getAttribute('data-ids').split(',').map(Number),[b]);return;}
 if(b.id==='rx-reset'){S.mg=DEF.mg;S.ml=DEF.ml;S.amt=DEF.amt;S.sp=DEF.sp;S.unit='mcg';S.p=D.presets[0]?D.presets[0].id:0;if(D.presets[0])S.mg=D.presets[0].mg;render();return;}
 if(b.id==='rx-share'){var u=location.origin+location.pathname+'?mg='+S.mg+'&ml='+S.ml+'&mcg='+S.amt+'&sp='+S.sp+(S.p>0?'&p='+S.p:'');
  if(navigator.share){navigator.share({title:'Peptid-Rechner',url:u}).catch(function(){});}else if(navigator.clipboard){navigator.clipboard.writeText(u).then(function(){toast('Link kopiert ✓');});}else{prompt('Link:',u);}return;}
});
function matchP(){var p=cur();if(p&&Math.abs(p.mg-S.mg)>1e-9){var m=D.presets.filter(function(x){return x.pid===p.pid&&Math.abs(x.mg-S.mg)<1e-9;})[0];S.p=m?m.id:-1;}}
[['rx-mg','mg'],['rx-ml','ml'],['rx-amt','amt']].forEach(function(a){var el=$(a[0]);
 el.addEventListener('change',function(){var v=num(el.value);if(v>0){S[a[1]]=a[1]==='amt'&&S.unit==='mg'?v*1000:v;if(a[1]==='mg')matchP();}render();});
 el.addEventListener('keydown',function(e){if(e.key==='Enter')el.blur();});
 el.addEventListener('focus',function(){setTimeout(function(){el.select();},0);});
});
/* ---- Start: Link-Parameter ---- */
var q=new URLSearchParams(location.search);
if(D.presets[0]){S.p=D.presets[0].id;S.mg=D.presets[0].mg;}
if(q.get('p')){var pp=D.presets.filter(function(x){return x.id===+q.get('p')||x.pid===+q.get('p');})[0];if(pp){S.p=pp.id;S.mg=pp.mg;}}
if(num(q.get('mg'))>0){S.mg=num(q.get('mg'));matchP();}
if(num(q.get('ml'))>0)S.ml=num(q.get('ml'));
if(num(q.get('mcg'))>0)S.amt=num(q.get('mcg'));
if(SP[q.get('sp')])S.sp=+q.get('sp');
render();
})();
</script>
</div>
    <?php
    return ob_get_clean();
}

add_action('template_redirect', function () {
    if (is_admin() || !is_page(SP_RX_PAGE)) {
        return;
    }
    // Werte VOR ob_start berechnen (WooCommerce-Funktionen im OB-Callback sind tabu)
    $html = sp_rx_html();
    $d = json_decode((string) get_post_meta(SP_RX_PAGE, '_elementor_data', true), true);
    $old = $d[0]['elements'][0]['settings']['html'] ?? '';
    ob_start(function ($buf) use ($html, $old) {
        if ($old !== '' && strpos($buf, $old) !== false) {
            return str_replace($old, $html, $buf);
        }
        // Fallback: alten Rechner per CSS ausblenden, neuen davor einsetzen
        $p = strpos($buf, '<section class="sp-pc-section"');
        if ($p === false) {
            return $buf;
        }
        return substr($buf, 0, $p) . '<style>.sp-pc-section{display:none!important}</style>' . $html . substr($buf, $p);
    });
}, 9);
