<?php
/**
 * Plugin Name: SP Kategorie-Vorschau
 * Description: Entwurf fuer Produktkategorie-Seiten und "Alle Produkte" im neuen Design - nur im Vorschau-Modus (2026-10-10).
 *
 * Aktiv, wenn sp_hpv_token_ok() (Link ?sp_vorschau=TOKEN bzw. Cookie aus sp-home-preview.php).
 * Ersetzt per JS den Inhalt von #content (Astra-Archiv bzw. Elementor-Seite 325) durch:
 * dunkler Kopfbereich (Titel, Beschreibung, Anzahl), Kategorie-Chips, Sortierung,
 * Produkt-Raster (gleiche Karten wie die Startseite, Direkt-Kauf, Vorbestellung markiert),
 * weitere Forschungsbereiche, Hinweis-Zeile. ?vorbestellung=1 auf /alle-produkte/ zeigt nur
 * Vorbestell-Produkte. Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

/** Seiten, die als Produkt-Sammlung dargestellt werden (Ziel-Seiten, Pens, Zubehoer). Preise immer live. */
function sp_cpv_collections() {
    return [
        'gewebe-verletzungsregeneration' => ['Gewebe- & Verletzungsregeneration', 'Forschungspeptide zur Untersuchung von Zellmigration, Kollagenbildung und Geweberegeneration – BPC-157 und TB-500 sind hier die zentralen Werkzeuge.', [428, 431, 68]],
        'entzuendung-darmgesundheit' => ['Entzündung & Darmgesundheit', 'Forschungspeptide zur Untersuchung entzündungsbezogener Signalwege und der Darmgesundheit.', [428, 515]],
        'immunmodulation' => ['Immunmodulation', 'Forschungspeptide zur Untersuchung immunmodulatorischer Mechanismen.', [515]],
        'mitochondrien-energie' => ['Mitochondrien & Energie', 'Forschungspeptide zur Untersuchung mitochondrialer Biogenese und zellulärer Energieproduktion – allen voran Mots-C.', [71, 396]],
        'zellschutz-anti-aging' => ['Zellschutz & Anti-Aging', 'Forschungspeptide zur Untersuchung zellprotektiver und altersbezogener Signalwege.', [68, 395, 71]],
        'konzentration-kognitive-leistung' => ['Konzentration & kognitive Leistung', 'Forschungspeptide zur Untersuchung neurotropher Signalwege und kognitiver Prozesse – Semax und Selank zählen zu den am längsten dokumentierten.', [434, 437]],
        'stress-angstregulation' => ['Stress- & Angstregulation', 'Forschungspeptide zur Untersuchung stress- und angstbezogener neurobiologischer Prozesse.', [437]],
        'schlaf-erholung' => ['Schlaf & Erholung', 'Forschungspeptide zur Untersuchung der Wachstumshormon-Sekretion und von Erholungsprozessen im Schlaf.', [77]],
        'appetitkontrolle-gewichtsmanagement' => ['Appetitkontrolle & Gewichtsmanagement', 'Forschungspeptide zur Untersuchung von Appetitregulation und Sättigungssignalwegen – Retatrutide ist eines der meistuntersuchten.', [65, 393, 518]],
        'fettverbrennung-stoffwechselfunktion' => ['Fettverbrennung & Stoffwechselfunktion', 'Forschungspeptide zur Untersuchung von Lipidstoffwechsel und metabolischer Funktion.', [65, 393, 71, 518]],
        'wachstumshormon-erholung' => ['Wachstumshormon & Erholung', 'Forschungspeptide zur Untersuchung der Wachstumshormon-Sekretion und regenerativer Signalwege.', [77, 431, 428]],
        'haut-kollagen' => ['Haut & Kollagen', 'Forschungspeptide zur Untersuchung von Kollagensynthese und Hautstruktur – GHK-Cu ist hier eines der meistuntersuchten.', [68, 395]],
        'pigmentierung-braeunung' => ['Pigmentierung & Bräunung', 'Forschungspeptide zur Untersuchung von Pigmentierungsprozessen. Dieser Bereich wird gerade aufgebaut – passende Peptide folgen in Kürze.', []],
        'alle-peptrium-pens' => ['Alle Peptrium-Pens', 'Vorgefüllte Pens mit Dosierrad – kein Anmischen, keine Spritzen. Retatrutide, GHK-Cu und Mots-C in mehreren Stärken.', [393, 395, 396], 'Peptrium-Pen'],
        'zubehoer' => ['Zubehör', 'Alles für die Arbeit im Labor: Bac Water zum Anmischen, Spritzen, Pen Nadeln und Injektionskit.', 'cat:zubehoer', 'Zubehör'],
    ];
}

function sp_cpv_collection() {
    foreach (sp_cpv_collections() as $slug => $c) {
        if (is_page($slug)) {
            return array_merge(['slug' => $slug], ['title' => $c[0], 'desc' => $c[1], 'ids' => $c[2], 'label' => $c[3] ?? 'Forschungsziel']);
        }
    }
    return null;
}

function sp_cpv_page_is_collection() {
    foreach (array_keys(sp_cpv_collections()) as $s) {
        if (is_page($s)) {
            return true;
        }
    }
    return false;
}

function sp_cpv_active() {
    if (!function_exists('sp_hpv_token_ok') || !sp_hpv_token_ok() || !function_exists('is_product_category')) {
        return false;
    }
    return is_product_category() || is_shop() || is_page('alle-produkte') || sp_cpv_collection() || (is_search() && !is_admin());
}

function sp_cpv_desc($slug) {
    $d = [
        'fettverlust' => 'Peptide aus der Stoffwechsel-Forschung – darunter Retatrutide als Triple-Agonist, Tesamorelin und der vorgefüllte Peptrium-Pen.',
        'regeneration-heilung' => 'Forschungspeptide für Studien zu Geweberegeneration und Wundheilung – BPC-157, TB-500, KPV und IGF-1 LR3.',
        'fokus' => 'Peptide aus der neurologischen und kognitiven Forschung – Semax, Selank sowie CJC-1295 + Ipamorelin.',
        'energie' => 'Mots-C, ein mitochondriales Peptid aus der Energie- und Stoffwechselforschung – als Vial oder vorgefüllter Pen.',
        'aesthetik' => 'GHK-Cu, das Kupferpeptid aus der Haut- und Kollagenforschung – als Vial oder vorgefüllter Pen.',
        'zubehoer' => 'Alles für die Arbeit im Labor: Bac Water zum Anmischen, Spritzen, Pen Nadeln und Injektionskit.',
    ];
    return $d[$slug] ?? '';
}

function sp_cpv_data() {
    $map = function_exists('sp_abo_picker_rating_map') ? sp_abo_picker_rating_map() : [];
    $qd = function_exists('sp_quantity_discount_product_ids') ? sp_quantity_discount_product_ids() : [];
    $pre = function_exists('sp_preorder_product_ids') ? sp_preorder_product_ids() : [];
    $col = sp_cpv_collection();
    $term = is_product_category() ? get_queried_object() : null;
    if ($col && is_string($col['ids']) && strpos($col['ids'], 'cat:') === 0) {
        $term = get_term_by('slug', substr($col['ids'], 4), 'product_cat');
        $col = null;
    }
    $args = ['status' => 'publish', 'limit' => -1, 'visibility' => 'catalog', 'orderby' => 'meta_value_num', 'meta_key' => 'total_sales', 'order' => 'DESC'];
    if ($term) {
        $args['category'] = [$term->slug];
    }
    $hide = [729, 817];
    $items = [];
    $rank = 0;
    if (is_search()) {
        $q = get_search_query();
        $ids = $q === '' ? [] : wc_get_products(['status' => 'publish', 'limit' => 40, 'visibility' => 'search', 's' => $q, 'return' => 'ids']);
        $col = ['slug' => '', 'title' => 'Suche: „' . $q . '“', 'desc' => $ids ? 'Diese Produkte passen zu deiner Suche.' : 'Zu deiner Suche haben wir leider nichts gefunden. Probier einen anderen Begriff oder stöbere in den Forschungsbereichen.', 'ids' => $ids, 'label' => 'Suche'];
    }
    if ($col) {
        $plist = [];
        foreach ($col['ids'] as $cid) {
            $cp = wc_get_product($cid);
            if ($cp && $cp->get_status() === 'publish') {
                $plist[] = $cp;
            }
        }
    } else {
        $plist = wc_get_products($args);
    }
    foreach ($plist as $p) {
        $id = $p->get_id();
        if (in_array($id, $hide, true)) {
            continue;
        }
        $vars = [];
        if ($p->is_type('variable')) {
            foreach ($p->get_children() as $vid) {
                $v = wc_get_product($vid);
                if (!$v || !$v->is_purchasable()) {
                    continue;
                }
                $vars[] = ['id' => $vid, 'l' => implode(' / ', array_values($v->get_attributes())), 'p' => (float) wc_get_price_to_display($v), 'r' => $v->is_on_sale() ? (float) wc_get_price_to_display($v, ['price' => $v->get_regular_price()]) : null];
            }
            usort($vars, function ($a, $b) { return $a['p'] <=> $b['p']; });
        }
        $cats = wp_get_post_terms($id, 'product_cat', ['fields' => 'slugs']);
        $items[] = [
            'id' => $id, 'n' => $p->get_name(), 'u' => get_permalink($id),
            'i' => wp_get_attachment_image_url($p->get_image_id(), 'medium_large'),
            'p' => $p->is_type('variable') ? ($vars ? $vars[0]['p'] : 0) : (float) wc_get_price_to_display($p),
            'r' => (!$p->is_type('variable') && $p->is_on_sale()) ? (float) wc_get_price_to_display($p, ['price' => $p->get_regular_price()]) : null,
            'vars' => $vars, 'vol' => $p->is_type('variable') ? '' : $p->get_attribute('volumen'),
            'rt' => $map[$id]['num'] ?? null, 'rc' => (int) ($map[$id]['count'] ?? 0),
            'qd' => in_array($id, $qd, true) && !$p->is_on_sale(),
            'pre' => in_array($id, $pre, true),
            'pen' => stripos($p->get_name(), 'Pen') !== false && !in_array('zubehoer', $cats, true),
            'acc' => in_array('zubehoer', $cats, true),
            'best' => $rank++ === 0 && !in_array('zubehoer', $cats, true),
        ];
    }
    $tiles = ['fettverlust' => 65, 'regeneration-heilung' => 428, 'fokus' => 434, 'energie' => 71, 'aesthetik' => 68, 'zubehoer' => 74];
    $all = wc_get_products(['status' => 'publish', 'limit' => -1, 'visibility' => 'catalog', 'return' => 'ids']);
    $all = array_diff($all, $hide);
    $cats = [['n' => 'Alle', 'u' => home_url('/alle-produkte/'), 'c' => count($all), 'on' => !$term && !$col && !is_search() && empty($_GET['vorbestellung']) && !sp_cpv_page_is_collection()]];
    foreach ($tiles as $slug => $pid) {
        $t = get_term_by('slug', $slug, 'product_cat');
        if (!$t || !$t->count) {
            continue;
        }
        $pp = wc_get_product($pid);
        $cnt = count(array_diff(wc_get_products(['status' => 'publish', 'limit' => -1, 'visibility' => 'catalog', 'category' => [$slug], 'return' => 'ids']), $hide));
        if (!$cnt) {
            continue;
        }
        $cats[] = ['n' => html_entity_decode($t->name), 'u' => get_term_link($t), 'c' => $cnt, 'on' => $term && $term->term_id === $t->term_id, 'tile' => true, 'i' => $pp ? wp_get_attachment_image_url($pp->get_image_id(), 'medium') : ''];
    }
    $npre = count(array_intersect($pre, $all));
    if ($npre) {
        $cats[] = ['n' => 'Vorbestellung', 'u' => add_query_arg('vorbestellung', '1', home_url('/alle-produkte/')), 'c' => $npre, 'on' => !$term && !empty($_GET['vorbestellung']), 'pre' => true];
    }
    if ($col && $col['label'] === 'Forschungsziel') {
        $cats = [['n' => 'Alle Produkte', 'u' => home_url('/alle-produkte/'), 'c' => null]];
        foreach (sp_cpv_collections() as $slug => $c) {
            if (($c[3] ?? '') || $c[2] === []) {
                if ($slug !== $col['slug']) {
                    continue;
                }
            }
            $cats[] = ['n' => $c[0], 'u' => home_url('/' . $slug . '/'), 'c' => null, 'on' => $slug === $col['slug']];
        }
    }
    if ($col) {
        return [
            'isAll' => false, 'label' => $col['label'], 'title' => $col['title'], 'desc' => $col['desc'],
            'products' => $items, 'cats' => $cats, 'soon' => !$items, 'search' => is_search(),
        ];
    }
    return [
        'label' => $term ? 'Forschungsbereich' : 'Sortiment',
        'isAll' => !$term,
        'title' => $term ? html_entity_decode($term->name) : 'Alle Produkte',
        'desc' => $term ? sp_cpv_desc($term->slug) : 'Unser komplettes Sortiment: hochreine Forschungspeptide, vorgefüllte Peptrium-Pens und passendes Zubehör – geprüft und schnell aus Deutschland geliefert.',
        'products' => $items, 'cats' => $cats,
    ];
}

add_action('wp_head', function () {
    if (!sp_cpv_active()) {
        return;
    }
    echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    ?>
<style id="sp-cpv-css">
/* ---- Produkte (Bestseller-Reihe) v2 ---- */
.sp-pc{position:relative;display:flex;flex-direction:column;background:#fff;border:1px solid #E3E6E9;border-radius:20px;overflow:hidden;color:#0D0F12;box-shadow:0 10px 28px rgba(13,15,18,.07);transition:transform .25s,box-shadow .25s}
.sp-pc:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(13,15,18,.13)}
.sp-pc a{text-decoration:none!important;color:inherit}
.sp-pc .im{position:relative;display:block;aspect-ratio:1/1;background:radial-gradient(circle at 50% 30%,#30363D 0%,#14171B 55%,#0B0D10 100%);overflow:hidden}
.sp-pc .im img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
.sp-pc:hover .im img{transform:scale(1.04)}
.sp-pc.pen .im img{object-fit:contain;padding:14px}
.sp-pc .im:after{content:'';position:absolute;left:0;right:0;bottom:0;height:38%;background:linear-gradient(transparent,rgba(11,13,16,.55));pointer-events:none}
.sp-pc .bd{position:absolute;z-index:1;top:12px;left:12px;font:700 10.5px Sora,sans-serif;padding:5px 10px;border-radius:999px;background:#fff;color:#0D0F12;letter-spacing:.02em}
.sp-pc .bd.s{background:#E8452C;color:#fff}
.sp-pc .bd.n{background:linear-gradient(120deg,#C7CCD1,#FFFFFF 50%,#C7CCD1)}
.sp-pc .coa{position:absolute;z-index:1;right:12px;bottom:12px;display:flex;align-items:center;gap:5px;font:600 10.5px Sora,sans-serif;color:#fff;padding:5px 9px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);-webkit-backdrop-filter:blur(6px);backdrop-filter:blur(6px)}
.sp-pc .tx{padding:14px 14px 14px;display:flex;flex-direction:column;gap:8px;flex:1}
.sp-pc .nm{font:700 16px/1.25 Sora,sans-serif;margin:0}
.sp-pc .rt{font-size:12px;color:#5A6068;display:flex;align-items:center;gap:6px}
.sp-pc .rt b{color:#F5A623;letter-spacing:1px}
.sp-pc .vo{display:flex;flex-wrap:wrap;gap:6px}
.sp-pc .vo button,.sp-pc .vo span{height:28px;padding:0 11px;border-radius:9px;border:1px solid #D5D9DD;background:#fff;font:600 12px Sora,sans-serif;color:#3A4048;cursor:pointer;display:inline-flex;align-items:center}
.sp-pc .vo span{cursor:default;background:#F4F5F6;border-color:#EEF0F2}
.sp-pc .vo button.on{background:#0D0F12;border-color:#0D0F12;color:#fff}
.sp-pc .qd{font-size:11.5px;font-weight:600;color:#2E9B57}
.sp-pc .pr{margin-top:auto;display:flex;align-items:baseline;gap:7px;flex-wrap:wrap}
.sp-pc .pr strong{font:800 19px Sora,sans-serif;letter-spacing:-.01em}
.sp-pc .pr s{font-size:13px;color:#9AA0A8}
.sp-pc .pr small{font-size:10.5px;color:#9AA0A8}
.sp-pc .add{height:44px;border:0;border-radius:13px;background:#0D0F12;color:#fff;font:700 13.5px Sora,sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;transition:background .2s}
.sp-pc .add:hover{background:#2A2F35}
.sp-pc .add.ok{background:#2E9B57}
.sp-pc .add[disabled]{opacity:.7}
.sp-pc.all{background:linear-gradient(160deg,#0D0F12,#23272C);color:#fff;justify-content:center;align-items:center;text-align:center;padding:24px;gap:10px;text-decoration:none!important}
.sp-pc.all .ar{width:56px;height:56px;border-radius:50%;background:linear-gradient(120deg,#C7CCD1,#fff 50%,#C7CCD1);color:#0D0F12;display:flex;align-items:center;justify-content:center;font-size:24px}
.sp-pc.all b{font-size:17px}.sp-pc.all span{font-size:12.5px;color:#9AA0A8}
.sp-trust{display:flex;justify-content:center;gap:10px 22px;flex-wrap:wrap;margin:22px 0 0;padding:0;list-style:none}
.sp-trust li{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:#3A4048}
.sp-trust li:before{content:'';width:16px;height:16px;border-radius:50%;background:#0D0F12 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23fff' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center/9px no-repeat}
@media(max-width:900px){.sp-pc{flex:0 0 66%}.sp-pc.all{flex-basis:50%}.sp-trust{gap:8px 14px}.sp-trust li{font-size:12px}}
@media(min-width:901px){.sp-pc.all{display:none}}

/* ---- Forschungsbereiche ---- */
.sp-cats-h{display:flex;align-items:baseline;justify-content:space-between;margin:34px 0 12px}
.sp-cats-h b{font:700 17px Sora,sans-serif;color:#0D0F12}
.sp-cats-h a{font-size:13px;font-weight:600;color:#5A6068!important;text-decoration:none!important}
.sp-cats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
.sp-cat{position:relative;display:flex;flex-direction:column;justify-content:flex-end;aspect-ratio:1/1.08;border-radius:18px;overflow:hidden;text-decoration:none!important;background:radial-gradient(circle at 50% 30%,#30363D,#0B0D10 70%)}
.sp-cat img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85;transition:transform .5s}
.sp-cat.wide img{object-fit:contain;padding:12px 12px 40px}
.sp-cat:hover img{transform:scale(1.05)}
.sp-cat:after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(11,13,16,0) 40%,rgba(11,13,16,.85))}
.sp-cat span{position:relative;z-index:1;padding:0 12px 12px;color:#fff;font:700 13.5px/1.25 Sora,sans-serif}
.sp-cat small{display:block;font-weight:500;font-size:11px;color:#B9BEC5;margin-top:2px}
@media(max-width:900px){.sp-cats{display:flex;overflow-x:auto;scroll-snap-type:x mandatory;scroll-padding:0 16px;margin:0 -16px;padding:2px 16px 10px;scrollbar-width:none}.sp-cats::-webkit-scrollbar{display:none}.sp-cat{flex:0 0 36%;scroll-snap-align:start}}
.sp-pc .bd.p{background:#F5A623;color:#0D0F12}
.sp-pc .bd.p+.bd{top:42px}
.sp-pc .pre-n{font-size:11px;color:#A86A00;text-align:center;margin-top:-2px}
/* ===== Kategorie / Alle Produkte (Entwurf) ===== */
body.sp-cat-on #content>.ast-container{display:none!important}
body.sp-cat-on .elementor-location-footer .elementor-element-97108f0{display:none!important}
#sp-cat{font-family:Sora,sans-serif;color:#0D0F12}
#sp-cat .hero{background:radial-gradient(90% 120% at 85% 0%,rgba(199,204,209,.14),transparent 60%),linear-gradient(160deg,#0D0F12 0%,#1E2226 100%);color:#fff;padding:26px 20px 30px;border-radius:0 0 28px 28px}
#sp-cat .hero .in{max-width:1140px;margin:0 auto}
#sp-cat .bc{font-size:12px;color:#8A9099;margin:0 0 16px}
#sp-cat .bc a{color:#B9BEC5!important;text-decoration:none!important}
#sp-cat .hero h1{font:800 clamp(30px,5vw,46px)/1.08 Sora,sans-serif;letter-spacing:-.02em;margin:0 0 10px;color:#fff}
#sp-cat .hero p{font-size:14.5px;line-height:1.6;color:#B9BEC5;margin:0 0 16px;max-width:620px}
#sp-cat .meta{display:flex;flex-wrap:wrap;gap:8px}
#sp-cat .meta span{font-size:12px;font-weight:600;color:#E6E9EC;padding:6px 11px;border-radius:999px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12)}
#sp-cat .body{max-width:1140px;margin:0 auto;padding:20px 16px 40px}
#sp-cat .chips{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;margin:0 -16px 16px;padding:2px 16px 4px}
#sp-cat .chips::-webkit-scrollbar{display:none}
#sp-cat .chips a{flex:0 0 auto;height:36px;display:inline-flex;align-items:center;gap:6px;padding:0 14px;border-radius:999px;border:1px solid #DDE1E5;background:#fff;font:600 13px Sora,sans-serif;color:#3A4048!important;text-decoration:none!important;white-space:nowrap}
#sp-cat .chips a small{font-size:11px;color:#9AA0A8}
#sp-cat .chips a.on{background:#0D0F12;border-color:#0D0F12;color:#fff!important}
#sp-cat .chips a.on small{color:#9AA0A8}
#sp-cat .chips a.pre{border-color:rgba(245,166,35,.5);color:#A86A00!important}
#sp-cat .chips a.pre.on{background:#F5A623;border-color:#F5A623;color:#0D0F12!important}
#sp-cat .bar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 0 14px}
#sp-cat .bar b{font-size:13px;color:#5A6068;font-weight:600}
#sp-cat .bar select{height:38px;border-radius:11px;border:1px solid #DDE1E5;background:#fff;font:600 13px Sora,sans-serif;color:#0D0F12;padding:0 34px 0 12px;-webkit-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230D0F12' stroke-width='2.4'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:14px}
#sp-cat .note{display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:14px;background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3);color:#7A5200;font-size:13px;line-height:1.5;margin:0 0 16px}
.sp-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
@media(max-width:900px){.sp-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
 .sp-grid .sp-pc{border-radius:18px}
 .sp-grid .sp-pc .tx{padding:11px 11px 12px;gap:6px}
 .sp-grid .sp-pc .nm{font-size:14px}
 .sp-grid .sp-pc .rt{font-size:11px;gap:4px}.sp-grid .sp-pc .rt b{font-size:10.5px;letter-spacing:0}
 .sp-grid .sp-pc .vo button,.sp-grid .sp-pc .vo span{height:26px;padding:0 8px;font-size:11.5px;border-radius:8px}
 .sp-grid .sp-pc .pr strong{font-size:16.5px}.sp-grid .sp-pc .pr s{font-size:11.5px}
 .sp-grid .sp-pc .add{height:40px;font-size:12.5px;border-radius:12px;gap:6px}
 .sp-grid .sp-pc .bd{top:9px;left:9px;font-size:10px;padding:4px 8px}
 .sp-grid .sp-pc .bd.p+.bd{top:36px}
 .sp-grid .sp-pc .coa{right:9px;bottom:9px;font-size:9.5px;padding:4px 7px}
 .sp-grid .sp-pc .qd{font-size:10.5px}
 .sp-grid .sp-pc .pre-n{font-size:10px}
}
.sp-grid .sp-pc{flex:none!important}
.sp-grid .sp-pc .im{aspect-ratio:1/1}
.sp-grid .sp-pc.pen .im img,.sp-grid .sp-pc.acc .im img{object-fit:contain;padding:10px}
.sp-grid .sp-pc.acc .im{background:radial-gradient(circle at 50% 35%,#FFFFFF,#EEF0F2 75%)}
.sp-grid .sp-pc.acc .im:after{display:none}
#sp-cat .empty{text-align:center;color:#5A6068;padding:30px 0}
#sp-cat .more{margin:30px 0 0;padding:20px;border-radius:20px;background:linear-gradient(180deg,#FFFFFF,#F4F5F6);border:1px solid #E3E6E9}
#sp-cat .more h2{font:700 18px Sora,sans-serif;margin:0 0 12px}
#sp-cat .more .cats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
@media(max-width:900px){#sp-cat .more .cats{grid-template-columns:repeat(2,minmax(0,1fr))}}
#sp-cat .dis{display:flex;gap:8px;align-items:flex-start;max-width:640px;margin:26px auto 0;font-size:12.5px;line-height:1.55;color:#4A5058}
#sp-cat .dis b{color:#0D0F12}
#sp-cat .bar b{white-space:nowrap}
@media(max-width:900px){.sp-grid .sp-pc .add svg{display:none}.sp-grid .sp-pc .add{white-space:nowrap;font-size:12.5px;padding:0 6px}}

/* v2: Kopfbereich kompakter */
#sp-cat .hero{padding:20px 18px 22px;border-radius:0 0 24px 24px}
#sp-cat .bc{margin:0 0 12px}
#sp-cat .pill{display:inline-flex;align-items:center;gap:8px;padding:5px 12px;border-radius:999px;font:700 10px/1.2 Sora,sans-serif;letter-spacing:.16em;text-transform:uppercase;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.05);color:#E6E9EC;margin:0 0 10px}
#sp-cat .pill:before{content:'';width:6px;height:6px;border-radius:50%;background:#C7CCD1;box-shadow:0 0 8px rgba(199,204,209,.7)}
#sp-cat .hero h1{font-size:clamp(28px,4.4vw,42px);margin:0 0 8px}
#sp-cat .hero p{font-size:14px;margin:0 0 14px}
#sp-cat .meta{flex-wrap:nowrap;overflow-x:auto;scrollbar-width:none;margin:0 -18px;padding:0 18px}
#sp-cat .meta::-webkit-scrollbar{display:none}
#sp-cat .meta span{flex:0 0 auto;white-space:nowrap;font-size:11.5px;padding:5px 10px}
#sp-cat .body{padding-top:16px}
#sp-cat .empty{display:flex;flex-direction:column;align-items:center;gap:8px;padding:34px 20px;border-radius:20px;background:#F6F7F8;border:1px dashed #D5D9DD;color:#4A5058;font-size:14px;line-height:1.55}
#sp-cat .empty b{font-size:17px;color:#0D0F12}
#sp-cat .empty a{margin-top:6px;height:42px;display:inline-flex;align-items:center;padding:0 20px;border-radius:999px;background:#0D0F12;color:#fff!important;font-weight:700;text-decoration:none!important}

#sp-hpv-flag{position:fixed;left:12px;top:12px;z-index:200000;background:#FF8A5C;color:#0D0F12;font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.3);text-decoration:none!important}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    if (!sp_cpv_active()) {
        return;
    }
    ?>
<a id="sp-hpv-flag" href="<?php echo esc_url(add_query_arg('sp_vorschau', 'aus', home_url('/'))); ?>">ENTWURF-VORSCHAU ✕</a>
<script id="sp-cpv-js">
window.SP_CAT=<?php echo wp_json_encode(sp_cpv_data()); ?>;
document.addEventListener('DOMContentLoaded',function(){
 var D=window.SP_CAT;if(!D)return;
 var host=document.getElementById('content');if(!host)return;
 document.body.classList.add('sp-cat-on');
 function eur(v){return String(Number(v).toFixed(2)).replace('.',',')+' €';}
 var cart='<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg>';
 var pre=/[?&]vorbestellung=1/.test(location.search);
 var list=D.products.filter(function(p){return !pre||p.pre;});
 function card(p){
   var first=(p.vars&&p.vars.length)?p.vars[0]:{id:p.id,p:p.p,r:p.r};
   var bd=(p.pre?'<span class="bd p">Vorbestellung</span>':'')+(first.r?'<span class="bd s">−'+Math.round((1-first.p/first.r)*100)+' %</span>':(p.best?'<span class="bd">★ Bestseller</span>':(p.pen?'<span class="bd n">Neu</span>':'')));
   var vo='';
   if(p.vars&&p.vars.length>1)vo='<div class="vo">'+p.vars.map(function(v,k){return '<button type="button" data-id="'+v.id+'" data-p="'+v.p+'" data-r="'+(v.r||'')+'"'+(k===0?' class="on"':'')+'>'+v.l+'</button>';}).join('')+'</div>';
   else if(p.vars&&p.vars.length===1)vo='<div class="vo"><span>'+p.vars[0].l+'</span></div>';
   else if(p.vol)vo='<div class="vo"><span>'+p.vol+'</span></div>';
   return '<div class="sp-pc'+(p.pen?' pen':'')+(p.acc?' acc':'')+'" data-id="'+first.id+'"><a class="im" href="'+p.u+'"><img loading="lazy" src="'+p.i+'" alt="'+p.n+'">'+bd+(p.acc?'':'<span class="coa">✓ COA</span>')+'</a><div class="tx"><a href="'+p.u+'"><h3 class="nm">'+p.n+'</h3></a>'
    +(p.rc?'<div class="rt"><b>★★★★★</b>'+String(p.rt).replace('.',',')+' ('+p.rc+')</div>':'')+vo
    +(p.qd?'<div class="qd">ab 3 Stück −10 %</div>':'')
    +'<div class="pr"><strong>'+eur(first.p)+'</strong>'+(first.r?'<s>'+eur(first.r)+'</s>':'')+'</div>'
    +'<button type="button" class="add">'+cart+(p.pre?'Vorbestellen':'In den Warenkorb')+'</button>'+(p.pre?'<div class="pre-n">Lieferung, sobald neue Ware eintrifft</div>':'')+'</div></div>';}
 var chips=D.cats.map(function(c){return '<a class="'+(c.on?'on':'')+(c.pre?' pre':'')+'" href="'+c.u+'">'+c.n+(c.c!=null?' <small>'+c.c+'</small>':'')+'</a>';}).join('');
 var others=D.cats.filter(function(c){return !c.on&&c.tile;});
 var root=document.createElement('div');root.id='sp-cat';
 root.innerHTML='<section class="hero"><div class="in"><div class="bc"><a href="/">Start</a> › <a href="/alle-produkte/">Produkte</a>'+(D.isAll?'':' › '+D.title)+'</div>'
  +'<span class="pill">'+(D.label||'Sortiment')+'</span>'
  +'<h1>'+(pre?'Vorbestellung':D.title)+'</h1><p>'+(pre?'Diese Produkte sind gerade vorbestellbar – mit Preisvorteil. Wir liefern, sobald die neue Ware eintrifft.':D.desc)+'</p>'
  +'<div class="meta"><span>'+list.length+(list.length===1?' Produkt':' Produkte')+'</span><span>🚚 Lieferung in 2 Werktagen</span><span>Gratisversand ab 100 €</span></div></div></section>'
  +'<div class="body"><div class="chips">'+chips+'</div>'
  +'<div class="bar"><b>'+list.length+' Ergebnisse</b><select aria-label="Sortieren"><option value="pop">Beliebteste</option><option value="pa">Preis aufsteigend</option><option value="pd">Preis absteigend</option><option value="az">Name A–Z</option></select></div>'
  +(list.some(function(p){return p.pre;})&&!pre?'<div class="note">⏳ <span>Mit <b>Vorbestellung</b> markierte Produkte werden geliefert, sobald neue Ware eintrifft.</span></div>':'')
  +'<div class="sp-grid">'+(list.length?list.map(card).join(''):'')+'</div>'+(list.length?'':D.search?'<div class="empty"><b>Keine Treffer</b>Probier z. B. „Retatrutide“, „GHK-Cu“ oder „Pen“.<a href="/alle-produkte/">Alle Produkte ansehen →</a></div>':'<div class="empty"><b>Bald verfügbar</b>Passende Peptide für diesen Bereich folgen in Kürze. Trag dich unten für den Newsletter ein – dann erfährst du es zuerst.<a href="/alle-produkte/">Zum ganzen Sortiment →</a></div>')
  +(others.length?'<div class="more"><h2>Weitere Forschungsbereiche</h2><div class="cats">'+others.map(function(c){return '<a class="sp-cat" href="'+c.u+'">'+(c.i?'<img loading="lazy" src="'+c.i+'" alt="">':'')+'<span>'+c.n+'<small>'+c.c+(c.c===1?' Produkt':' Produkte')+'</small></span></a>';}).join('')+'</div></div>':'')
  +'<div class="dis"><span>ⓘ</span><span><b>Nur für Laborforschung.</b> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</span></div></div>';
 host.insertBefore(root,host.firstChild);
 var grid=root.querySelector('.sp-grid');
 root.querySelector('select').addEventListener('change',function(){var v=this.value,arr=list.slice();
   var pr=function(p){return (p.vars&&p.vars.length)?p.vars[0].p:p.p;};
   if(v==='pa')arr.sort(function(a,b){return pr(a)-pr(b);});if(v==='pd')arr.sort(function(a,b){return pr(b)-pr(a);});if(v==='az')arr.sort(function(a,b){return a.n.localeCompare(b.n,'de');});
   grid.innerHTML=arr.map(card).join('');});
 grid.addEventListener('click',function(e){
   var vb=e.target.closest('.vo button');
   if(vb){var c=vb.closest('.sp-pc');c.querySelectorAll('.vo button').forEach(function(b){b.classList.toggle('on',b===vb);});c.setAttribute('data-id',vb.getAttribute('data-id'));
     c.querySelector('.pr').innerHTML='<strong>'+eur(vb.getAttribute('data-p'))+'</strong>'+(vb.getAttribute('data-r')?'<s>'+eur(vb.getAttribute('data-r'))+'</s>':'');return;}
   var ab=e.target.closest('.add');if(!ab)return;
   var c2=ab.closest('.sp-pc'),old=ab.innerHTML;ab.disabled=true;ab.textContent='…';
   var fd=new FormData();fd.append('product_id',c2.getAttribute('data-id'));fd.append('quantity','1');
   fetch('/?wc-ajax=add_to_cart',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();}).then(function(r){
     if(r&&r.error&&r.product_url){location.href=r.product_url;return;}
     ab.classList.add('ok');ab.innerHTML='✓ Hinzugefügt';
     if(window.jQuery)jQuery(document.body).trigger('added_to_cart',[r.fragments,r.cart_hash,jQuery(ab)]);
     setTimeout(function(){ab.classList.remove('ok');ab.innerHTML=old;ab.disabled=false;},2200);
   }).catch(function(){location.href=c2.querySelector('a.im').href;});
 });
});
</script>
    <?php
}, 99);
