<?php
/**
 * Plugin Name: SP Ratgeber
 * Description: Ratgeber-Bereich /ratgeber/ (Übersicht + Artikel als normale WP-Seiten, kein Elementor). Hero, Artikel-Layout,
 *              FAQ (Post-Meta _sp_rg_faq), JSON-LD (Article, FAQPage, BreadcrumbList), weitere Artikel, Links in Menü-Drawer und Footer.
 *              Inhalt der Artikel = post_content, Lead = post_excerpt, SEO-Titel/Beschreibung = Post-Meta _sp_seo (sp-seo.php).
 *              Neue Artikel: Unterseite von "ratgeber" anlegen → erscheint automatisch in der Übersicht und bei "Weitere Artikel".
 */
if (!defined('ABSPATH')) {
    exit;
}

function sp_rg_parent_id() {
    static $id = null;
    if ($id === null) {
        $p = get_page_by_path('ratgeber');
        $id = ($p && $p->post_status === 'publish') ? (int) $p->ID : 0;
    }
    return $id;
}

/** 'index' = Übersicht, 'artikel' = Artikel, '' = keine Ratgeber-Seite */
function sp_rg_kind() {
    static $k = null;
    if ($k !== null) {
        return $k;
    }
    $k = '';
    if (is_page() && sp_rg_parent_id()) {
        $id = (int) get_queried_object_id();
        if ($id === sp_rg_parent_id()) {
            $k = 'index';
        } elseif ((int) wp_get_post_parent_id($id) === sp_rg_parent_id()) {
            $k = 'artikel';
        }
    }
    return $k;
}

function sp_rg_articles($exclude = 0) {
    if (!sp_rg_parent_id()) {
        return [];
    }
    return get_posts(['post_type' => 'page', 'post_status' => 'publish', 'post_parent' => sp_rg_parent_id(),
        'orderby' => 'menu_order date', 'order' => 'ASC', 'numberposts' => 50, 'exclude' => $exclude ? [$exclude] : []]);
}

function sp_rg_minutes($post) {
    $w = str_word_count(wp_strip_all_tags($post->post_content . ' ' . $post->post_excerpt), 0, 'äöüÄÖÜß');
    return max(2, (int) ceil($w / 180));
}

add_action('wp', function () {
    if (!sp_rg_kind()) {
        return;
    }
    remove_filter('the_content', 'wpautop');
    add_filter('astra_the_title_enabled', '__return_false');
    add_filter('body_class', function ($c) {
        $c[] = 'sp-rg-page';
        return $c;
    });
});

add_filter('the_content', function ($html) {
    if (!sp_rg_kind() || !in_the_loop() || !is_main_query()) {
        return $html;
    }
    $post = get_post();
    $home = home_url('/');
    $idx = get_permalink(sp_rg_parent_id());
    $chev = '<span aria-hidden="true">›</span>';
    if (sp_rg_kind() === 'index') {
        $cards = '';
        foreach (sp_rg_articles() as $a) {
            $cards .= '<a class="rg-card" href="' . esc_url(get_permalink($a)) . '"><span class="rg-card-m">' . sp_rg_minutes($a) . ' Min. Lesezeit</span>'
                . '<span class="rg-card-t">' . esc_html($a->post_title) . '</span><span class="rg-card-d">' . esc_html($a->post_excerpt) . '</span>'
                . '<span class="rg-card-l">Artikel lesen →</span></a>';
        }
        return '<div class="sp-rg"><header class="rg-hero"><div class="rg-in"><nav class="rg-bc"><a href="' . esc_url($home) . '">Startseite</a>' . $chev . '<span>Ratgeber</span></nav>'
            . '<div class="rg-eb">PEPTRIUM RATGEBER</div><h1>' . esc_html($post->post_title) . '</h1><p class="rg-lead">' . esc_html($post->post_excerpt) . '</p></div></header>'
            . '<div class="rg-in rg-main"><div class="rg-grid">' . $cards . '</div>'
            . '<div class="rg-more-box"><div><b>Du suchst ein bestimmtes Peptid?</b><span>Herkunft, Wirkmechanismus und Forschungsstand findest du im Peptid-Lexikon.</span></div>'
            . '<a class="rg-btn" href="' . esc_url(home_url('/peptid-lexikon/')) . '">Zum Lexikon →</a></div></div></div>';
    }
    $faq = get_post_meta($post->ID, '_sp_rg_faq', true);
    $faq_html = '';
    if (is_array($faq) && $faq) {
        foreach ($faq as $f) {
            $faq_html .= '<details><summary>' . esc_html($f[0]) . '</summary><p>' . esc_html($f[1]) . '</p></details>';
        }
        $faq_html = '<section class="rg-faq"><h2>Häufige Fragen</h2>' . $faq_html . '</section>';
    }
    $more = '';
    foreach (array_slice(sp_rg_articles($post->ID), 0, 3) as $a) {
        $more .= '<a class="rg-card" href="' . esc_url(get_permalink($a)) . '"><span class="rg-card-m">' . sp_rg_minutes($a) . ' Min. Lesezeit</span><span class="rg-card-t">' . esc_html($a->post_title) . '</span><span class="rg-card-l">Lesen →</span></a>';
    }
    $date = get_the_modified_date('j. F Y', $post);
    return '<div class="sp-rg"><header class="rg-hero"><div class="rg-in"><nav class="rg-bc"><a href="' . esc_url($home) . '">Startseite</a>' . $chev
        . '<a href="' . esc_url($idx) . '">Ratgeber</a>' . $chev . '<span>' . esc_html($post->post_title) . '</span></nav>'
        . '<div class="rg-eb">RATGEBER · ' . sp_rg_minutes($post) . ' MIN. LESEZEIT</div><h1>' . esc_html($post->post_title) . '</h1>'
        . '<p class="rg-lead">' . esc_html($post->post_excerpt) . '</p><div class="rg-date">Aktualisiert am ' . esc_html($date) . ' · Peptrium-Redaktion</div></div></header>'
        . '<div class="rg-in rg-main"><article class="rg-art">' . $html . $faq_html
        . '<p class="rg-note">Alle Produkte von Peptrium sind ausschließlich für die Laborforschung bestimmt – nicht für die Anwendung am Menschen oder Tier. Dieser Artikel dient der allgemeinen Information und ist keine Anwendungsanleitung.</p></article>'
        . ($more ? '<section class="rg-rel"><h2>Weitere Artikel</h2><div class="rg-grid">' . $more . '</div><a class="rg-all" href="' . esc_url($idx) . '">Alle Ratgeber-Artikel →</a></section>' : '')
        . '</div></div>';
}, 20);

add_action('wp_head', function () {
    if (!sp_rg_kind()) {
        return;
    }
    ?>
<style id="sp-rg-css">
body.sp-rg-page{background:#F4F5F7}
body.sp-rg-page .site-content>.ast-container{max-width:100%!important;padding:0!important;display:block}
body.sp-rg-page #primary{margin:0!important;padding:0!important;width:100%}
body.sp-rg-page .entry-header,body.sp-rg-page .post-thumb{display:none!important}
body.sp-rg-page article.page{margin:0!important;padding:0!important;background:none!important;box-shadow:none!important}
body.sp-rg-page .entry-content{margin:0!important}
.sp-rg{--d:#0D0F12;--fg:#15181C;--mu:#5F666E;--ln:#E1E4E8;--ch:#ECEEF1;color:var(--fg);font-size:17px;line-height:1.7}
.sp-rg .rg-in{max-width:780px;margin:0 auto;padding:0 20px}
.sp-rg .rg-hero{background:radial-gradient(120% 140% at 85% 0%,#22272E 0%,#0D0F12 60%);color:#fff;padding:34px 0 38px}
.sp-rg .rg-bc{font-size:13px;color:#9AA1A9;display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px}
.sp-rg .rg-bc a{color:#C9CDD2;text-decoration:none}.sp-rg .rg-bc span:last-child{color:#9AA1A9}
.sp-rg .rg-eb{font-size:11.5px;font-weight:800;letter-spacing:.18em;color:#A3AAB3;margin-bottom:10px}
.sp-rg .rg-hero h1{color:#fff;font-family:'Sora',sans-serif;font-size:clamp(28px,6vw,42px);line-height:1.15;margin:0 0 14px;text-wrap:balance;font-weight:700}
.sp-rg .rg-lead{color:#C9CDD2;font-size:17.5px;line-height:1.6;margin:0}
.sp-rg .rg-date{font-size:13px;color:#8E959E;margin-top:16px}
.sp-rg .rg-main{padding-top:22px;padding-bottom:48px}
.sp-rg .rg-art{background:#fff;border:1px solid var(--ln);border-radius:20px;padding:10px 22px 26px}
.sp-rg .rg-art h2{font-family:'Sora',sans-serif;font-size:22px;line-height:1.3;margin:30px 0 10px;color:var(--d);text-wrap:balance;font-weight:700}
.sp-rg .rg-art p,.sp-rg .rg-art li{font-size:17px;line-height:1.7;color:#2A2E33}
.sp-rg .rg-art p{margin:0 0 14px}.sp-rg .rg-art ul,.sp-rg .rg-art ol{margin:0 0 16px;padding-left:22px}.sp-rg .rg-art li{margin-bottom:8px}
.sp-rg .rg-art a{color:var(--d);text-decoration:underline;text-decoration-color:#A3AAB3;text-underline-offset:3px;font-weight:600}
.sp-rg .rg-box{background:var(--d);color:#F4F5F7;border-radius:14px;padding:16px 18px;margin:14px 0 18px;line-height:1.75;font-size:16px}
.sp-rg .rg-box strong,.sp-rg .rg-box em{color:#fff}
.sp-rg .tw{overflow-x:auto;margin:12px 0 18px;-webkit-overflow-scrolling:touch}
.sp-rg .rg-tab{width:100%;border-collapse:collapse;font-size:15px}
@media(max-width:600px){.sp-rg .rg-tab{font-size:13.5px}.sp-rg .rg-tab th,.sp-rg .rg-tab td{padding:9px 4px!important;hyphens:auto}.sp-rg .rg-tab th{font-size:12.5px}.sp-rg .rg-art{padding-left:14px;padding-right:14px}}
.sp-rg .rg-tab th,.sp-rg .rg-tab td{border:0;border-bottom:1px solid var(--ln);padding:10px 8px;text-align:left;vertical-align:top}
.sp-rg .rg-tab th{background:var(--ch);font-weight:700}.sp-rg .rg-tab tr td:first-child{font-weight:600}
.sp-rg .rg-faq{margin-top:30px;border-top:1px solid var(--ln)}
.sp-rg .rg-faq details{border-bottom:1px solid var(--ln);padding:14px 0}
.sp-rg .rg-faq summary{font-weight:700;cursor:pointer;list-style:none;display:flex;justify-content:space-between;gap:12px;font-size:16.5px}
.sp-rg .rg-faq summary::-webkit-details-marker{display:none}
.sp-rg .rg-faq summary::after{content:'+';font-size:22px;line-height:1;color:var(--mu)}
.sp-rg .rg-faq details[open] summary::after{content:'–'}
.sp-rg .rg-faq details p{margin:10px 0 0;font-size:16px}
.sp-rg .rg-note{font-size:13.5px!important;line-height:1.55!important;color:var(--mu)!important;background:#F4F5F7;border-radius:12px;padding:12px 14px;margin:24px 0 0!important}
.sp-rg .rg-grid{display:grid;grid-template-columns:1fr;gap:12px}
@media(min-width:700px){.sp-rg .rg-grid{grid-template-columns:1fr 1fr}}
.sp-rg .rg-card{display:flex;flex-direction:column;gap:6px;background:#fff;border:1px solid var(--ln);border-radius:16px;padding:18px;text-decoration:none!important;color:var(--fg);transition:border-color .2s,transform .2s}
.sp-rg .rg-card *{text-decoration:none!important}
.sp-rg .rg-card:hover{border-color:#A3AAB3;transform:translateY(-2px)}
.sp-rg .rg-card-m{font-size:12px;font-weight:700;letter-spacing:.06em;color:var(--mu);text-transform:uppercase}
.sp-rg .rg-card-t{font-family:'Sora',sans-serif;font-size:18px;font-weight:700;line-height:1.3;color:var(--d)}
.sp-rg .rg-card-d{font-size:15px;line-height:1.55;color:#4A5057}
.sp-rg .rg-card-l{font-size:14px;font-weight:700;color:var(--d);margin-top:4px}
.sp-rg .rg-rel{margin-top:34px}.sp-rg .rg-rel h2{font-family:'Sora',sans-serif;font-size:21px;margin:0 0 14px;color:var(--d)}
.sp-rg .rg-all{display:inline-block;margin-top:14px;font-weight:700;color:var(--d)}
.sp-rg .rg-more-box{margin-top:22px;background:var(--d);color:#fff;border-radius:18px;padding:20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px}
.sp-rg .rg-more-box b{display:block;font-size:17px}.sp-rg .rg-more-box span{display:block;color:#C9CDD2;font-size:15px;line-height:1.5}
.sp-rg .rg-btn{background:#fff;color:var(--d)!important;text-decoration:none;font-weight:800;padding:12px 18px;border-radius:12px;white-space:nowrap}
@media(max-width:600px){.sp-rg .rg-in{padding:0 16px}.sp-rg .rg-art{padding:6px 16px 22px;border-radius:16px}.sp-rg .rg-hero{padding:26px 0 30px}}
</style>
    <?php
}, 30);

add_action('wp_head', function () {
    $k = sp_rg_kind();
    if (!$k) {
        return;
    }
    $post = get_queried_object();
    $logo = function_exists('get_site_icon_url') ? get_site_icon_url(512) : '';
    $og = (int) get_option('sp_seo_og_default');
    $img = $og ? wp_get_attachment_image_url($og, 'full') : '';
    $crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Startseite', 'item' => home_url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Ratgeber', 'item' => get_permalink(sp_rg_parent_id())]];
    $graph = [];
    if ($k === 'artikel') {
        $crumbs[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $post->post_title, 'item' => get_permalink($post)];
        $org = ['@type' => 'Organization', 'name' => 'Peptrium', 'url' => home_url('/')];
        if ($logo) {
            $org['logo'] = ['@type' => 'ImageObject', 'url' => $logo];
        }
        $art = ['@type' => 'Article', 'headline' => $post->post_title, 'description' => $post->post_excerpt,
            'datePublished' => get_post_time('c', true, $post), 'dateModified' => get_post_modified_time('c', true, $post),
            'author' => $org, 'publisher' => $org, 'mainEntityOfPage' => get_permalink($post), 'inLanguage' => 'de-DE'];
        if ($img) {
            $art['image'] = $img;
        }
        $graph[] = $art;
        $faq = get_post_meta($post->ID, '_sp_rg_faq', true);
        if (is_array($faq) && $faq) {
            $graph[] = ['@type' => 'FAQPage', 'mainEntity' => array_map(function ($f) {
                return ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]];
            }, $faq)];
        }
    }
    $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs];
    echo '<script type="application/ld+json">' . wp_json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
}, 40);

/* Links im Menü-Drawer (nach "Peptid-Lexikon") und im Footer (Spalte Shop, nach "Lagerungs-Guide").
   Werte vor ob_start berechnen, im Callback nur Strings ersetzen. */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || is_feed() || !sp_rg_parent_id()) {
        return;
    }
    $url = get_permalink(sp_rg_parent_id());
    $ic = '<span class="sp-mm-sub-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>';
    $drawer = '<a href="' . esc_url($url) . '">' . $ic . 'Ratgeber</a>';
    $footer = '<a href="/ratgeber/">Ratgeber</a>';
    ob_start(function ($html) use ($drawer, $footer) {
        if (strpos($html, '/ratgeber/">' . "<span class=\"sp-mm-sub-ic\"") === false) {
            $html = preg_replace('~(<a href="[^"]*/peptid-lexikon/"><span class="sp-mm-sub-ic">.*?</span>Peptid-Lexikon</a>)~s', '$1 ' . str_replace(['\\', '$'], ['\\\\', '\$'], $drawer), $html, 1);
        }
        if (strpos($html, $footer) === false) {
            $html = preg_replace('~(<a href="/lagerungs-guide/">Lagerungs-Guide</a>)~', '$1 ' . $footer, $html, 1);
        }
        return $html;
    });
}, 4);

/* Lexikon: Astras versteckte Titel-H1 entfernen (die Seite hat ihre eigene H1 → sonst zwei H1). */
add_action('wp', function () {
    if (!is_page()) {
        return;
    }
    $id = (int) get_queried_object_id();
    $lex = get_page_by_path('peptid-lexikon');
    if ($lex && ($id === (int) $lex->ID || (int) wp_get_post_parent_id($id) === (int) $lex->ID)) {
        add_filter('astra_the_title_enabled', '__return_false');
    }
});

/** Ratgeber-Slugs passend zur aktuellen Produkt- bzw. Lexikon-Seite (max. 3). */
function sp_rg_related_slugs() {
    $R = 'peptide-rekonstituieren';
    $V = 'retatrutide-tirzepatide-semaglutid';
    $H = 'hplc-lc-ms-reinheit';
    $K = 'peptide-kaufen-deutschland';
    $C = 'cagrilintid-retatrutide-unterschied';
    $G = 'glow-peptid-blend';
    $L = 'was-ist-glp-3';
    $M = 'retatrutide-10mg-oder-20mg';
    $T = 'tb-500-vs-bpc-157';
    $B = 'bac-water-bakteriostatisches-wasser';
    if (function_exists('is_product') && is_product()) {
        $id = (int) get_queried_object_id();
        $slug = get_post_field('post_name', $id);
        if (in_array($id, [729, 745, 817], true)) {
            return [];
        }
        if ($id === 908) {
            return [$K];
        }
        if (has_term(20, 'product_cat', $id)) {
            return $slug === 'bac-water' ? [$B, $R, $K] : [$R, $B, $K];
        }
        if (strpos($slug, 'peptrium-pen') === 0) {
            return $slug === 'peptrium-pen-retatrutide' ? [$L, $M, $V] : [$H, $K];
        }
        if ($slug === 'cagrilintid') {
            return [$C, $V, $R];
        }
        if ($slug === 'retatrutide') {
            return [$L, $M, $V];
        }
        if (in_array($slug, ['bpc-157', 'tb-500'], true)) {
            return [$T, $G, $R];
        }
        if (in_array($slug, ['glow', 'ghk-cu'], true)) {
            return [$G, $T, $R];
        }
        return $slug === 'tirzepatide' ? [$V, $L, $R] : [$R, $B, $H];
    }
    if (is_page()) {
        $id = (int) get_queried_object_id();
        $lex = get_page_by_path('peptid-lexikon');
        if (!$lex) {
            return [];
        }
        if ($id === (int) $lex->ID) {
            return [$R, $V, $H];
        }
        if ((int) wp_get_post_parent_id($id) !== (int) $lex->ID) {
            return [];
        }
        $slug = get_post_field('post_name', $id);
        if (in_array($slug, ['retatrutide', 'tirzepatide'], true)) {
            return [$L, $V, $H];
        }
        if ($slug === 'bac-water') {
            return [$B, $R];
        }
        if (in_array($slug, ['bpc-157', 'tb-500'], true)) {
            return [$T, $R];
        }
        if (strpos($slug, 'peptrium-pen') === 0) {
            return [$H, $K];
        }
        return [$R, $H];
    }
    return [];
}

add_action('wp_footer', function () {
    if (!sp_rg_parent_id()) {
        return;
    }
    $links = '';
    foreach (sp_rg_related_slugs() as $s) {
        $p = get_page_by_path('ratgeber/' . $s);
        if ($p && $p->post_status === 'publish') {
            $links .= '<a class="rgl-a" href="' . esc_url(get_permalink($p)) . '"><span>' . esc_html($p->post_title) . '</span><i aria-hidden="true">→</i></a>';
        }
    }
    if (!$links) {
        return;
    }
    $prod = function_exists('is_product') && is_product();
    ?>
<section id="sp-rg-links" class="<?php echo $prod ? 'is-prod' : 'is-lex'; ?>"><div class="rgl-in"><div class="rgl-eb">Ratgeber</div><div class="rgl-h">Mehr dazu</div><div class="rgl-list"><?php echo $links; // phpcs:ignore ?></div><a class="rgl-all" href="<?php echo esc_url(get_permalink(sp_rg_parent_id())); ?>">Alle Ratgeber-Artikel</a></div></section>
<style id="sp-rg-links-css">
#sp-rg-links{box-sizing:border-box;font-family:Inter,system-ui,sans-serif}
#sp-rg-links .rgl-eb{font:700 11px/1 Sora,sans-serif;letter-spacing:.12em;text-transform:uppercase;color:#6B727A;margin-bottom:8px}
#sp-rg-links .rgl-h{font:800 20px/1.25 Sora,sans-serif;color:#0D0F12;margin:0 0 12px}
#sp-rg-links .rgl-list{display:flex;flex-direction:column;gap:8px}
#sp-rg-links .rgl-a{display:flex;align-items:center;justify-content:space-between;gap:12px;background:#fff;border:1px solid #E3E6E9;border-radius:14px;padding:14px 16px;color:#0D0F12!important;text-decoration:none!important;font-weight:700!important;font-size:15px;line-height:1.35;transition:border-color .2s}
#sp-rg-links .rgl-a:hover{border-color:#A3AAB3}
#sp-rg-links .rgl-a i{font-style:normal;color:#6B727A;flex-shrink:0}
#sp-rg-links .rgl-all{display:inline-block;margin-top:12px;font-size:14px;font-weight:700;color:#0D0F12!important;text-decoration:underline!important;text-underline-offset:2px}
#sp-rg-links.is-prod{margin-top:24px;padding-top:20px;border-top:1px solid #E3E6E9}
#sp-rg-links.is-lex{margin:34px 0 10px}
#sp-rg-links.is-lex .rgl-in{background:#F4F5F6;border:1px solid #E3E6E9;border-radius:24px;padding:24px 20px}
@media(min-width:1024px){#sp-rg-links .rgl-list{display:grid;grid-template-columns:repeat(3,1fr)}}
</style>
<script id="sp-rg-links-js">
(function(){var s=document.getElementById('sp-rg-links');if(!s)return;var n=0;
 function place(){var a=document.querySelector('#sp-pp-about .in'),l=document.getElementById('sp-lexd')||document.getElementById('sp-lex');
  if(s.classList.contains('is-prod')){if(a){a.appendChild(s);return true;}var r=document.querySelector('.pp-root');if(n>20&&r){s.style.order='3';s.classList.remove('is-prod');s.classList.add('is-lex');r.appendChild(s);return true;}return false;}
  if(l){l.appendChild(s);return true;}return n>20;}
 (function t(){if(!place()&&n++<40)setTimeout(t,150);})();
})();
</script>
    <?php
}, 60);
