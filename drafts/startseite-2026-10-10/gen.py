import sys,re
tok=open('hp/token.txt').read().strip()
css=open('hp/full.css').read().strip()
js=open('common/sets.js').read().strip()+"\n"+open('hp/full.js').read().strip()
js=js.replace("(function(){\n var root","document.addEventListener('DOMContentLoaded',function(){\n var root",1)
assert js.endswith("})();"); js=js[:-5]+"});"
php=r"""<?php
/**
 * Plugin Name: SP Startseiten-Vorschau
 * Description: Entwurf der neuen Startseite NUR ueber einen geheimen Vorschau-Link (2026-10-10).
 *
 * https://peptrium.com/?sp_vorschau=TOKEN zeigt die Startseite mit dem Entwurf
 * (neue Reihenfolge, Hell/Dunkel-Wechsel, Bestseller-Reihe mit Direkt-Kauf,
 * Kundenstimmen-Laufband mit Flasche beim Scrollen, ...). Alle anderen Besucher
 * sehen die normale Startseite. Reines CSS/JS-Overlay, Elementor-Daten bleiben
 * unangetastet. Datei loeschen = Vorschau weg.
 * Entwurfsdateien im Repo: drafts/startseite-2026-10-10/.
 */
if (!defined('ABSPATH')) {
    exit;
}

define('SP_HPV_TOKEN', '""" + tok + r"""');

/** Vorschau-Modus: per Link (?sp_vorschau=TOKEN) einschalten, bleibt per Cookie 24 h beim Weiterklicken aktiv; ?sp_vorschau=aus beendet ihn. */
function sp_hpv_token_ok() {
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    if (isset($_GET['sp_vorschau'])) {
        $v = (string) $_GET['sp_vorschau'];
        if ($v === 'aus') {
            if (!headers_sent()) {
                setcookie('sp_vorschau', '', time() - 3600, '/', '', true, true);
            }
            return $ok = false;
        }
        if (hash_equals(SP_HPV_TOKEN, $v)) {
            if (!headers_sent()) {
                setcookie('sp_vorschau', $v, time() + DAY_IN_SECONDS, '/', '', true, true);
            }
            return $ok = true;
        }
    }
    return $ok = isset($_COOKIE['sp_vorschau']) && hash_equals(SP_HPV_TOKEN, (string) $_COOKIE['sp_vorschau']);
}
add_action('send_headers', 'sp_hpv_token_ok');

function sp_hpv_active() {
    return function_exists('sp_redesign_on') && sp_redesign_on('katalog') && is_front_page();
}

/** Bestseller fuer die Produkt-Reihe (Peptide + Pens, nach Verkaufszahl). */
function sp_hpv_products($ids = null) {
    $ids = $ids ?: [65, 68, 393, 428, 431, 71, 77, 434];
    $map = function_exists('sp_abo_picker_rating_map') ? sp_abo_picker_rating_map() : [];
    $qd = function_exists('sp_quantity_discount_product_ids') ? sp_quantity_discount_product_ids() : [];
    $out = [];
    foreach ($ids as $id) {
        $p = wc_get_product($id);
        if (!$p || $p->get_status() !== 'publish') {
            continue;
        }
        $var = $p->is_type('variable');
        $vars = [];
        if ($var) {
            foreach ($p->get_children() as $vid) {
                $v = wc_get_product($vid);
                if (!$v || !$v->is_purchasable() || !$v->is_in_stock()) {
                    continue;
                }
                $vars[] = [
                    'id' => $vid,
                    'l' => implode(' / ', array_values($v->get_attributes())),
                    'p' => (float) wc_get_price_to_display($v),
                    'r' => $v->is_on_sale() ? (float) wc_get_price_to_display($v, ['price' => $v->get_regular_price()]) : null,
                ];
            }
            usort($vars, function ($a, $b) { return $a['p'] <=> $b['p']; });
        }
        $vol = '';
        if (!$var) {
            $vol = $p->get_attribute('volumen');
        }
        $on_sale = $p->is_on_sale();
        $out[] = [
            'id' => $id, 'n' => $p->get_name(), 'u' => get_permalink($id), 'i' => wp_get_attachment_image_url($p->get_image_id(), 'medium_large'),
            'p' => $var ? ($vars ? $vars[0]['p'] : 0) : (float) wc_get_price_to_display($p),
            'r' => (!$var && $on_sale) ? (float) wc_get_price_to_display($p, ['price' => $p->get_regular_price()]) : null,
            'vars' => $vars, 'vol' => $vol,
            'qd' => in_array($id, $qd, true) && !$on_sale,
            'rt' => isset($map[$id]['num']) ? $map[$id]['num'] : 4.8, 'rc' => isset($map[$id]['count']) ? $map[$id]['count'] : 0,
            'pen' => stripos($p->get_name(), 'Pen') !== false,
            'pre' => function_exists('sp_preorder_product_ids') && in_array($id, sp_preorder_product_ids(), true),
        ];
    }
    return $out;
}

/** Forschungsbereiche mit einem typischen Produktbild. */
function sp_hpv_cats() {
    $pick = ['fettverlust' => 65, 'regeneration-heilung' => 428, 'fokus' => 434, 'energie' => 71, 'aesthetik' => 68, 'zubehoer' => 74];
    $out = [];
    foreach ($pick as $slug => $pid) {
        $t = get_term_by('slug', $slug, 'product_cat');
        $p = wc_get_product($pid);
        if (!$t || !$t->count) {
            continue;
        }
        $out[] = ['n' => html_entity_decode($t->name), 'u' => get_term_link($t), 'c' => (int) $t->count, 'i' => $p ? wp_get_attachment_image_url($p->get_image_id(), 'medium_large') : '', 'w' => false];
    }
    return $out;
}

/** Kombi-Sets (nur lieferbare Produkte; keine Set-Rabatte). Optionale Varianten-Auswahl ('opts'). */
function sp_hpv_set_item($id) {
    $p = wc_get_product($id);
    if (!$p || !$p->is_purchasable() || !$p->is_in_stock()) {
        return null;
    }
    $parent = $p->is_type('variation') ? wc_get_product($p->get_parent_id()) : $p;
    $img = $p->get_image_id() ? $p->get_image_id() : $parent->get_image_id();
    $name = $parent->get_name() . ($p->is_type('variation') ? ' ' . implode(' ', array_values($p->get_attributes())) : '');
    return ['id' => $id, 'n' => $name, 'p' => (float) wc_get_price_to_display($p), 'i' => wp_get_attachment_image_url($img, 'thumbnail'), 'w' => in_array($parent->get_id(), [80, 393, 395, 396, 908, 745], true)];
}

/* Varianten eines Produkts als Set-Optionen: je Variante (lieferbar) eine Option mit Mengen-Label */
function sp_hpv_set_vars($pid, $g, $extra) {
    $p = wc_get_product($pid);
    if (!$p) {
        return [];
    }
    $out = [];
    $kids = $p->is_type('variable') ? $p->get_children() : [$pid];
    foreach ($kids as $vid) {
        $v = wc_get_product($vid);
        if (!$v || !$v->is_purchasable() || !$v->is_in_stock()) {
            continue;
        }
        $l = $v->is_type('variation') ? preg_replace('/(\d)\s*mg$/i', '$1 mg', implode(' ', array_values($v->get_attributes()))) : '';
        $out[] = ['g' => $g, 'l' => $l, 'ids' => array_merge([$vid], $extra)];
    }
    return $out;
}

function sp_hpv_sets() {
    $defs = [
        ['n' => 'Retatrutide Starter', 'd' => 'Alles für den Start: Retatrutide plus Bac Water zum Anmischen und Spritzen zum genauen Dosieren.', 'opts' => sp_hpv_set_vars(65, '', [74, 80])],
        ['n' => 'Pen-Set', 'd' => 'Ein vorgefüllter Peptrium-Pen mit passenden Pen Nadeln – ganz ohne Anmischen. Wähle Pen und Menge:', 'opts' => array_merge(sp_hpv_set_vars(393, 'Retatrutide', [908]), sp_hpv_set_vars(395, 'GHK-Cu', [908]), sp_hpv_set_vars(396, 'Mots-C', [908]))],
        ['n' => 'GHK-Cu Komplett', 'd' => 'GHK-Cu 50 mg mit Bac Water und Spritzen – alles für deine Forschung in einem Paket.', 'opts' => [['l' => '', 'ids' => [68, 74, 80]]]],
        ['n' => 'Energie & Ästhetik', 'd' => 'Das oft zusammen gekaufte Duo: Mots-C und GHK-Cu, dazu Bac Water zum Anmischen.', 'opts' => [['l' => '', 'ids' => [71, 68, 74]]]],
    ];
    $out = [];
    foreach ($defs as $d) {
        $opts = [];
        foreach ($d['opts'] as $o) {
            $items = [];
            $t = 0;
            foreach ($o['ids'] as $id) {
                $it = sp_hpv_set_item($id);
                if (!$it) {
                    continue 2;
                }
                $items[] = $it;
                $t += $it['p'];
            }
            $opts[] = ['g' => $o['g'] ?? '', 'l' => $o['l'], 'items' => $items, 't' => round($t, 2)];
        }
        if ($opts) {
            $out[] = ['n' => $d['n'], 'd' => $d['d'], 'opts' => $opts, 'sel' => 0];
        }
    }
    return $out;
}

add_action('wp_head', function () {
    if (!sp_hpv_active()) {
        return;
    }
    if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')) {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
    if (function_exists('sp_redesign_live') && sp_redesign_live('katalog')) {
        echo '<script type="application/ld+json">' . base64_decode('__FAQ_B64__') . '</script>' . "\n";
    }
    ?>
<style id="sp-hpv-css">
""" + css + r"""
#sp-hpv-flag{position:fixed;left:12px;top:12px;z-index:99999;background:#FF8A5C;color:#0D0F12;font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.3);text-decoration:none!important}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    if (!sp_hpv_active()) {
        return;
    }
    ?>
<?php if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')): ?><a id="sp-hpv-flag" href="<?php echo esc_url(add_query_arg('sp_vorschau', 'aus', home_url('/'))); ?>">ENTWURF-VORSCHAU ✕</a><?php endif; ?>
<script id="sp-hpv-js">
window.SP_HP_PRODS=<?php echo wp_json_encode(sp_hpv_products()); ?>;
window.SP_HP_CATS=<?php echo wp_json_encode(sp_hpv_cats()); ?>;
window.SP_HP_SETS=<?php echo wp_json_encode(sp_hpv_sets()); ?>;
""" + js + r"""
</script>
    <?php
}, 99);
"""
import base64 as _b
php=php.replace('__FAQ_B64__',_b.b64encode(open('hp/faq.json','rb').read()).decode())
open('sp-home-preview.php','w').write(php)
