css=open('hp/full.css').read().strip()+"\n"+open('pp/pp.css').read().strip()
js=open('common/sets.js').read().strip()+"\n"+open('pp/pp.js').read().strip()
php=r"""<?php
/**
 * Plugin Name: SP Produktseiten-Vorschau
 * Description: Entwurf der neuen Produktseite (zuerst Retatrutide) NUR ueber den geheimen Vorschau-Link (2026-10-10).
 *
 * https://peptrium.com/produkt/retatrutide/?sp_vorschau=TOKEN (gleicher Schluessel wie
 * sp-home-preview.php). Kaufbox bleibt technisch unveraendert; drumherum: Kundenstimmen
 * (dunkel, Laufband + Flasche beim Scrollen), "Passt dazu"-Sets, Lexikon dunkel, FAQ im
 * Startseiten-Stil, "Auch beliebt"-Produktkarten, Hinweis als Zeile, Sticky-Kaufleiste
 * auf dem Handy. Nutzt Daten-Helfer aus sp-home-preview.php (laedt alphabetisch vorher).
 * Datei loeschen = Vorschau weg.
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_ppv_active() {
    return function_exists('sp_redesign_on') && sp_redesign_on('katalog') && function_exists('is_product') && is_product()
        && !in_array((int) get_queried_object_id(), [729, 817, 745], true);
}

function sp_ppv_data() {
    $id = (int) get_queried_object_id();
    $p = wc_get_product($id);
    $map = function_exists('sp_abo_picker_rating_map') ? sp_abo_picker_rating_map() : [];
    $price = $p->is_type('variable') ? (float) $p->get_variation_price('min', true) : (float) wc_get_price_to_display($p);
    $family = array_merge([$id], $p->get_children());
    $sets = [];
    foreach (sp_hpv_sets() as $s) {
        foreach ($s['opts'] as $k => $o) {
            foreach ($o['items'] as $it) {
                if (in_array((int) $it['id'], $family, true)) {
                    /* passende Option vorauswaehlen */
                    $s['sel'] = $k;
                    $sets[] = $s;
                    continue 3;
                }
            }
        }
    }
    if (!$sets && !in_array($id, [74, 80, 908], true)) {
        /* Fallback: Produkt + Bac Water + Spritzen (bei Vials) bzw. + Pen Nadeln (bei Pens) */
        $extra = in_array($id, [393, 395, 396], true) ? [908] : [74, 80];
        $opts = [];
        foreach (sp_hpv_set_vars($id, '', $extra) as $o) {
            $items = [];
            $t = 0;
            foreach ($o['ids'] as $iid) {
                $it = sp_hpv_set_item($iid);
                if (!$it) {
                    continue 2;
                }
                $items[] = $it;
                $t += $it['p'];
            }
            $opts[] = ['g' => '', 'l' => $o['l'], 'items' => $items, 't' => round($t, 2)];
        }
        if ($opts) {
            $sets[] = ['n' => $p->get_name() . ' komplett', 'd' => in_array($id, [393, 395, 396], true) ? 'Der Pen mit passenden Pen Nadeln.' : 'Mit Bac Water zum Anmischen und Spritzen zum genauen Dosieren.', 'opts' => $opts, 'sel' => 0];
        }
    }
    $more = [];
    /* Pen Nadeln: zuerst die Pens, dann Bestseller */
    foreach (sp_hpv_products($id === 908 ? [393, 395, 396, 65, 68, 71] : null) as $q) {
        if ((int) $q['id'] === $id) {
            continue;
        }
        $q['v'] = count($q['vars']) > 1;
        $q['aid'] = $q['vars'] ? $q['vars'][0]['id'] : $q['id'];
        if (!$q['vol'] && count($q['vars']) === 1) {
            $q['vol'] = $q['vars'][0]['l'];
        }
        unset($q['vars']);
        $more[] = $q;
    }
    $cats = wp_get_post_terms($id, 'product_cat', ['fields' => 'slugs']);
    $is_acc = in_array('zubehoer', $cats, true);
    $is_pen = in_array($id, [393, 395, 396], true);
    /* Schraeges Freisteller-Glas (ohne Produktnamen) nur bei Peptid-Vials */
    $bottle = $is_acc ? '' : content_url('/uploads/2026/08/retatrutide-tilted-glass-v3.png');
    /* Pens: schwebender Pen mit passender Aufschrift (uploads/sp-redesign/pen-float-*.webp) */
    if ($is_pen) $bottle = content_url('/uploads/sp-redesign/pen-float-' . [393 => 'reta', 395 => 'ghk', 396 => 'motsc'][$id] . '.webp');
    return [
        'name' => $p->get_name(), 'short' => [908 => 'Pen Nadeln'][$id] ?? '', 'price' => $price, 'thumb' => wp_get_attachment_image_url($p->get_image_id(), 'thumbnail'),
        'rt' => str_replace('.', ',', (string) ($map[$id]['num'] ?? 4.8)), 'rc' => (int) ($map[$id]['count'] ?? 0),
        'bottle' => $bottle, 'sets' => $sets, 'more' => $more, 'acc' => $is_acc, 'pen' => $is_pen, 'best' => $id === 65,
        'pre' => function_exists('sp_preorder_product_ids') && in_array($id, sp_preorder_product_ids(), true),
    ];
}

add_action('wp_head', function () {
    if (!sp_ppv_active()) {
        return;
    }
    if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')) {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
    ?>
<style id="sp-ppv-css">
""" + css + r"""
#sp-hpv-flag{position:fixed;left:12px;top:12px;z-index:200000;background:#FF8A5C;color:#0D0F12;font:700 11px Sora,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 4px 14px rgba(0,0,0,.3);text-decoration:none!important}
</style>
    <?php
}, 99);

add_action('wp_footer', function () {
    if (!sp_ppv_active()) {
        return;
    }
    ?>
<?php if (function_exists('sp_redesign_is_draft') && sp_redesign_is_draft('katalog')): ?><a id="sp-hpv-flag" href="<?php echo esc_url(add_query_arg('sp_vorschau', 'aus', home_url('/'))); ?>">ENTWURF-VORSCHAU ✕</a><?php endif; ?>
<script id="sp-ppv-js">
window.SP_PP=<?php echo wp_json_encode(sp_ppv_data()); ?>;
""" + js + r"""
</script>
    <?php
}, 99);
"""
open('sp-product-preview.php','w').write(php)
