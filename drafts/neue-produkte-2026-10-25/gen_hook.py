# Erzeugt sp-neu-produktseiten.php: Produktseiten der neuen Produkte per WooCommerce-Hooks
# (wie sp-pen-nadeln-page.php), Inhalte = HTML-Widgets aus den generierten Vorlagen (gen_tpl.py)
import json, sys
sys.path.insert(0, '.')
from texts import P

SLUG = {'cagri': 'cagrilintid', 'mt2': 'melanotan-2', 'glow': 'glow'}
PID = {'cagri': 1051, 'mt2': 1053, 'glow': 1055}
IMG = {'cagri': ('https://peptrium.com/wp-content/uploads/2026/10/cagrilintid-peptrium.png', 1052),
       'mt2': ('https://peptrium.com/wp-content/uploads/2026/10/melanotan-2-peptrium.png', 1054),
       'glow': ('https://peptrium.com/wp-content/uploads/2026/10/glow-peptrium.png', 1056)}


def widgets(k):
    d = json.load(open('Peptrium-Vorlage-%s.json' % SLUG[k]))
    out = {}

    def walk(l):
        for x in l:
            s = x.get('settings')
            if isinstance(s, dict) and x.get('widgetType') == 'html':
                out[x['id']] = s['html']
            walk(x.get('elements', []))
    walk(d['content'])
    return out


def nd(s):
    assert 'SPHTML' not in s
    return "<<<'SPHTML'\n" + s + "\nSPHTML"


php = ["<?php\n/**\n * Plugin Name: SP Neue Produktseiten\n * Description: (2026-10-25) Produktseiten für Cagrilintid (1051), Melanotan II (1053) und GLOW (1055) – per WooCommerce-Hooks\n *   gebaut wie sp-pen-nadeln-page.php (keine Elementor-Vorlage). Inhalte im selben HTML wie die anderen Peptid-Seiten\n *   (Hero, Kaufbox #rx-buybox, Tabs, Kundenstimmen, FAQ, Zusammen gekauft, Hinweis); das echte WooCommerce-Formular wird per\n *   JS in die Kaufbox umgehängt. Das Redesign (sp-product-preview, pp.js) erkennt die Hook-Seite automatisch (.pp-hook).\n *   Quelle: drafts/neue-produkte-2026-10-25/ (texts.py → gen_tpl.py → gen_hook.py).\n */\nif (!defined('ABSPATH')) exit;\n\nfunction sp_nps_pages() {\n    static $p = null;\n    if ($p !== null) return $p;\n    $p = [];\n"]
for k in ['cagri', 'mt2', 'glow']:
    w = widgets(k)
    img, att = IMG[k]
    php.append("    $p[%d] = [\n" % PID[k])
    php.append("        'img' => %s,\n" % json.dumps(img))
    php.append("        'alt' => %s,\n" % json.dumps(P[k]['name'] + ' – Peptrium'))
    for key, wid in [('hero', '7c87d35'), ('bb', 'mcbb0001'), ('qual', 'e3a55b4'), ('tabs', 'mctab0001'), ('rev', '5946c1e'), ('lex', '9af0125'), ('faq', 'c11f1b1'), ('rel', 'mcrelw001')]:
        php.append("        '%s' => %s,\n" % (key, nd(w[wid])))
    php.append("    ];\n")
php.append("""    return $p;
}

add_action('wp', function () {
    if (!function_exists('is_product') || !is_product()) return;
    $id = (int) get_the_ID();
    if (!isset(sp_nps_pages()[$id])) return;
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5); // H1 kommt aus dem Hero
    add_action('woocommerce_before_single_product_summary', 'sp_nps_render_main', 1);
    add_action('woocommerce_after_single_product_summary', 'sp_nps_render_rest', 1);
});

function sp_nps_render_main() {
    $id = (int) get_the_ID();
    $d = sp_nps_pages()[$id];
    ?>
    <style>
      body.postid-<?php echo $id; ?> .woocommerce-product-gallery{display:none!important}
      body.postid-<?php echo $id; ?> .summary.entry-summary{display:none!important}
      .sp-pen-wrap{max-width:1200px;margin:0 auto;background:#fff;display:flex;flex-direction:row;flex-wrap:wrap;justify-content:space-between;gap:32px 48px;padding:56px 20px 40px;box-sizing:border-box;font-family:'Sora',sans-serif}
      .sp-pen-col-img{width:40%;min-width:280px;box-sizing:border-box}
      .sp-pen-col-img img{width:100%;height:auto;display:block;border-radius:16px}
      .sp-pen-col-text{width:54%;min-width:280px;box-sizing:border-box}
      @media(max-width:900px){.sp-pen-col-img,.sp-pen-col-text{width:100%}}
    </style>
    <div class="sp-pen-wrap">
      <div class="sp-pen-col-img">
        <img fetchpriority="high" src="<?php echo esc_url($d['img']); ?>" alt="<?php echo esc_attr($d['alt']); ?>" width="1086" height="1448" loading="eager">
      </div>
      <div class="sp-pen-col-text">
<?php echo $d['hero'], $d['bb'], $d['qual'], $d['tabs']; ?>
      </div>
    </div>
    <script>
    (function(){
      function moveCartForm(){
        var cartRow = document.getElementById('rx-cartrow');
        var realForm = document.querySelector('.summary.entry-summary form.cart');
        if (cartRow && realForm && !document.getElementById('sp-reta-buybox-el')) {
          var wrap = document.createElement('div');
          wrap.id = 'sp-reta-buybox-el';
          realForm.parentNode.insertBefore(wrap, realForm);
          wrap.appendChild(realForm);
          cartRow.appendChild(wrap);
        }
      }
      if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', moveCartForm); else moveCartForm();
    })();
    </script>
    <?php
}

function sp_nps_render_rest() {
    $d = sp_nps_pages()[(int) get_the_ID()];
    ?>
    <div style="text-align:center;padding:48px 20px;max-width:900px;margin:0 auto;box-sizing:border-box" id="motsc-kundenstimmen">
      <div class="elementor-heading-title elementor-size-default" style="font-family:'Sora',sans-serif;font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:1.5px;color:#8a9099;margin-bottom:8px">KUNDENSTIMMEN</div>
      <h2 class="elementor-heading-title elementor-size-default" style="font-family:'Sora',sans-serif;font-size:34px;font-weight:600;color:#0D0F12;margin:0 0 28px">Was unsere Kunden sagen</h2>
<?php echo $d['rev']; ?>
    </div>
    <div style="max-width:900px;margin:0 auto;padding:0 20px 48px;box-sizing:border-box">
<?php echo $d['lex']; ?>
    </div>
    <div style="max-width:760px;margin:0 auto;padding:0 20px 48px;box-sizing:border-box">
<?php echo $d['faq']; ?>
    </div>
    <div style="max-width:900px;margin:0 auto;padding:0 20px 48px;box-sizing:border-box">
<?php echo $d['rel']; ?>
    </div>
    <div style="max-width:1200px;margin:0 auto 48px;padding:0 20px;box-sizing:border-box">
      <div style="background:linear-gradient(135deg,#0D0F12 0%,#2A2E33 100%);border-radius:20px;padding:40px 32px;text-align:center;font-family:'Sora',sans-serif">
        <h2 style="color:#fff;font-weight:600;font-size:30px;margin:0 0 16px">Wichtiger Hinweis</h2>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:13px;align-items:center;font-size:15px;color:#fff">
          <li>&#9679;&nbsp; Nicht f&uuml;r die Anwendung am Menschen oder Tier bestimmt</li>
          <li>&#9679;&nbsp; F&uuml;r Laborforschungszwecke</li>
          <li>&#9679;&nbsp; Nicht f&uuml;r diagnostische oder therapeutische Zwecke</li>
        </ul>
      </div>
    </div>
    <?php
}
""")
open('sp-neu-produktseiten.php', 'w').write(''.join(php))
print('ok', sum(len(x) for x in php))
