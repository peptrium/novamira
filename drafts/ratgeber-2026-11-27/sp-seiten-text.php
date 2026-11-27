<?php
/**
 * Plugin Name: Peptrium – SEO-Texte für Sammelseiten
 * Description: (2026-11-27) Eigener Text + FAQ unter Sammelseiten, die sp-category-preview.php per JS rendert (z. B. 397 /alle-peptrium-pens/).
 *   Daten in Option `sp_seiten_text` [page_id => {ey, h2, body, faq:[[q,a],…]}]. Platzhalter {preis:ID} und {versandfrei} wie in sp-product-about.php.
 *   Die Sektion wird serverseitig im Footer ausgegeben (für Google) und per JS in #sp-cat vor „Weitere Forschungsbereiche“ geschoben.
 *   FAQPage-JSON-LD im <head>. Google-Titel/-Beschreibung über post meta `_sp_seo` (sp-seo.php).
 */
if (!defined('ABSPATH')) exit;

function sp_st_data() {
    if (!is_page()) return null;
    $all = (array) get_option('sp_seiten_text', []);
    $id = (int) get_queried_object_id();
    return !empty($all[$id]) ? $all[$id] : null;
}

function sp_st_fill($t) {
    $t = preg_replace_callback('/\{preis:(\d+)\}/', function ($m) {
        $p = wc_get_product((int) $m[1]);
        return $p ? html_entity_decode(wp_strip_all_tags(wc_price($p->get_price())), ENT_QUOTES, 'UTF-8') : '';
    }, (string) $t);
    if (strpos($t, '{versandfrei}') !== false) {
        $thr = function_exists('sp_fsh_threshold') ? (float) sp_fsh_threshold() : 150;
        $t = str_replace('{versandfrei}', html_entity_decode(wp_strip_all_tags(wc_price($thr)), ENT_QUOTES, 'UTF-8'), $t);
    }
    return $t;
}

add_action('wp_head', function () {
    $d = sp_st_data();
    if (!$d || empty($d['faq'])) return;
    $q = [];
    foreach ($d['faq'] as $f) $q[] = ['@type' => 'Question', 'name' => wp_strip_all_tags(sp_st_fill($f[0])), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => wp_strip_all_tags(sp_st_fill($f[1]))]];
    echo '<script type="application/ld+json">' . wp_json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $q], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}, 30);

add_action('wp_footer', function () {
    $d = sp_st_data();
    if (!$d) return;
    $faq = '';
    if (!empty($d['faq'])) {
        $faq = '<div class="fq"><h3>Häufige Fragen</h3>';
        foreach ($d['faq'] as $f) $faq .= '<details class="q"><summary>' . esc_html(sp_st_fill($f[0])) . '</summary><p>' . wp_kses_post(sp_st_fill($f[1])) . '</p></details>';
        $faq .= '</div>';
    }
    ?>
<section id="sp-st-about" aria-labelledby="sp-st-h"><div class="in">
<span class="ey"><?php echo esc_html($d['ey'] ?? ''); ?></span>
<h2 id="sp-st-h"><?php echo esc_html($d['h2'] ?? ''); ?></h2>
<div class="tx"><?php echo wp_kses_post(sp_st_fill($d['body'] ?? '')) . $faq; // phpcs:ignore ?></div>
</div></section>
<style id="sp-st-css">
#sp-st-about{margin:28px 16px;box-sizing:border-box}
#sp-st-about .in{background:#F4F5F6;border:1px solid #E3E6E9;border-radius:24px;padding:28px 20px 22px;max-width:1240px;margin:0 auto;box-sizing:border-box;font-family:Inter,system-ui,sans-serif;color:#2B3036;text-align:left}
#sp-st-about .ey{display:inline-block;font:700 11px/1 Sora,sans-serif;letter-spacing:.12em;text-transform:uppercase;color:#6B727A;margin-bottom:10px}
#sp-st-about h2{font:800 24px/1.2 Sora,sans-serif;color:#0D0F12;margin:0 0 14px;letter-spacing:-.01em}
#sp-st-about h3{font:700 16.5px/1.3 Sora,sans-serif;color:#0D0F12;margin:20px 0 6px}
#sp-st-about p,#sp-st-about li{font-size:15px;line-height:1.7;color:#3A4047}#sp-st-about p{margin:0 0 10px}
#sp-st-about ul{padding-left:20px;margin:0 0 10px}#sp-st-about li{margin:0 0 6px}
#sp-st-about a{color:#0D0F12;font-weight:600;text-decoration:underline;text-underline-offset:2px}
#sp-st-about .sp-ab-lead{font-size:16px;color:#0D0F12}
#sp-st-about .fq h3{font-size:19px;margin-top:26px}
#sp-st-about .q{border-bottom:1px solid #DCDEE0;padding:14px 0}#sp-st-about .q summary{font:700 15px/1.4 Sora,sans-serif;color:#0D0F12;cursor:pointer;list-style:none}#sp-st-about .q summary::-webkit-details-marker{display:none}#sp-st-about .q summary:after{content:'+';float:right;color:#8A9099}#sp-st-about .q[open] summary:after{content:'−'}#sp-st-about .q p{margin:8px 0 0}
#sp-st-about .sp-ab-note{margin-top:16px;padding:12px 14px;border-radius:12px;background:#fff;border:1px solid #E3E6E9;font-size:13px;color:#4B5157}
@media(min-width:1024px){#sp-st-about{margin:40px auto;max-width:1240px}#sp-st-about .in{padding:40px 44px 32px}#sp-st-about .tx{columns:2;column-gap:44px}#sp-st-about .tx h3{break-after:avoid}#sp-st-about .fq{column-span:all}}
</style>
<script>
(function(){var s=document.getElementById('sp-st-about');if(!s)return;var n=0;
 (function t(){var c=document.getElementById('sp-cat');if(c){var m=c.querySelector('.more');if(m)m.parentNode.insertBefore(s,m);else c.appendChild(s);return;}if(n++<60)setTimeout(t,100);})();})();
</script>
<?php
}, 120);
