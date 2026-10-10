hp=open('hp/full.css').read()
# nur die gemeinsam genutzten Bausteine (Labels, Karten, Kategorie-Kacheln) uebernehmen
import re
def block(start,end):
    a=hp.index(start); b=hp.index(end,a); return hp[a:b]
shared=block('/* ---- Produkte (Bestseller-Reihe) v2 ---- */','/* ---- Kundenstimmen')
shared+=block('/* ---- Forschungsbereiche ---- */','.sp-trust li.pay')
shared+=".sp-pc .bd.p{background:rgba(13,15,18,.82);color:#F5C26B;border:1px solid rgba(245,194,107,.45)}\n.sp-pc .pre-n{font-size:11px;color:#8A6A2A;text-align:center;margin-top:-2px}\n"
css=shared+open('cat/cat.css').read()
js=open('cat/cat.js').read().strip()
php=r"""<?php
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
        'alle-peptrium-pens' => ['Alle Peptrium-Pens', 'Vorgefüllte Pens mit Dosierrad – kein Anmischen, keine Spritzen, in mehreren Stärken.', [393, 395, 396], 'Peptrium-Pen'],
        'zubehoer' => ['Zubehör', 'Alles für die Arbeit im Labor – vom Anmischen bis zum genauen Dosieren.', 'cat:zubehoer', 'Zubehör'],
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
    if (!function_exists('sp_redesign_on') || !sp_redesign_on('katalog') || !function_exists('is_product_category')) {
        return false;
    }
    return is_product_category() || is_shop() || is_page('alle-produkte') || sp_cpv_collection() || (is_search() && !is_admin());
}

function sp_cpv_desc($slug) {
    $d = [
        'fettverlust' => 'Forschungspeptide für Studien zu Stoffwechsel, Appetitregulation und Fettstoffwechsel – als Vial oder vorgefüllter Pen.',
        'regeneration-heilung' => 'Forschungspeptide für Studien zu Geweberegeneration, Wundheilung und Entzündungsprozessen.',
        'fokus' => 'Forschungspeptide für Studien zu kognitiven Prozessen, Stressregulation und Wachstumshormon-Sekretion.',
        'energie' => 'Forschungspeptide für Studien zu Mitochondrien, Zellenergie und Stoffwechsel.',
        'aesthetik' => 'Forschungspeptide für Studien zu Haut, Kollagen und Zellschutz.',
        'zubehoer' => 'Alles für die Arbeit im Labor – vom Anmischen bis zum genauen Dosieren.',
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
    $hide = [729, 817, 745];
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
            'acc' => in_array($id, [80, 908, 745], true),
            'noq' => in_array('zubehoer', $cats, true),
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
    if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')) {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
    ?>
<style id="sp-cpv-css">
""" + css + r"""
#sp-hpv-flag{position:fixed;left:12px;top:12px;z-index:200000;background:#FF8A5C;color:#0D0F12;font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.3);text-decoration:none!important}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    if (!sp_cpv_active()) {
        return;
    }
    ?>
<?php if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')): ?><a id="sp-hpv-flag" href="<?php echo esc_url(add_query_arg('sp_vorschau', 'aus', home_url('/'))); ?>">ENTWURF-VORSCHAU ✕</a><?php endif; ?>
<script id="sp-cpv-js">
window.SP_CAT=<?php echo wp_json_encode(sp_cpv_data()); ?>;
""" + js + r"""
</script>
    <?php
}, 99);
"""
open('sp-category-preview.php','w').write(php)
