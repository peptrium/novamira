# Erzeugt Elementor-Import-Dateien für die neuen Produkte aus der Mots-C-Vorlage (351)
import json, re, sys, html as H
sys.path.insert(0, '.')
from texts import P

IDS = {'cagri': (1051, 1052, 'https://peptrium.com/wp-content/uploads/2026/10/cagrilintid-peptrium.png'),
       'mt2': (1053, 1054, 'https://peptrium.com/wp-content/uploads/2026/10/melanotan-2-peptrium.png'),
       'glow': (1055, 1056, 'https://peptrium.com/wp-content/uploads/2026/10/glow-peptrium.png')}
# Karten für "Wird häufig zusammen gekauft" (Name, URL, Bild, Preis)
REL = {
    65: ('Retatrutide', '/produkt/retatrutide/', 'https://peptrium.com/wp-content/uploads/2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png', 'ab 74,90'),
    68: ('GHK-Cu', '/produkt/ghk-cu/', 'https://peptrium.com/wp-content/uploads/2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png', 'ab 39,90'),
    71: ('Mots-C', '/produkt/mots-c/', 'https://peptrium.com/wp-content/uploads/2026/08/7255FFFB-6823-45F9-AC96-88637F1411CB-300x300.png', '34,90'),
    74: ('Bac Water', '/produkt/bac-water/', 'https://peptrium.com/wp-content/uploads/2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png', '9,90'),
    428: ('BPC-157', '/produkt/bpc-157/', 'https://peptrium.com/wp-content/uploads/2026/08/104D478F-BD97-4CA2-A036-29E66BDE6ECF-300x300.png', '39,90'),
    431: ('TB-500', '/produkt/tb-500/', 'https://peptrium.com/wp-content/uploads/2026/08/13C97F4D-C559-40FD-B414-14B8D73FCB56-300x300.png', 'ab 38,17'),
    515: ('KPV', '/produkt/kpv/', 'https://peptrium.com/wp-content/uploads/2026/08/9A5E6104-889F-47CB-B191-D707F7FB77A2-300x300.png', '29,67'),
    'GLOW': ('GLOW', '/produkt/glow/', 'https://peptrium.com/wp-content/uploads/2026/10/glow-peptrium-300x300.png', '139,90'),
}

src = json.load(open('t351.json'))
base = json.loads(src['data'])


def de(n):
    return ('%.2f' % float(n)).replace('.', ',')


def esc(s):
    return H.escape(s, quote=False)


def build(k):
    d = P[k]
    pid, att, url = IDS[k]
    els = json.loads(json.dumps(base))
    name = d['name']
    price = de(d['price'])
    vol = d['vol'].replace('mg', '')
    rnum, rcount = d['rating']

    def fix_html(wid, h):
        if wid == '7c87d35':  # Hero
            h = h.replace('<h1 class="sp-reta-title">Mots-C</h1>', '<h1 class="sp-reta-title">%s</h1>' % esc(name))
            h = h.replace('<span class="sp-reta-rating-num">4,7</span>', '<span class="sp-reta-rating-num">%s</span>' % str(rnum).replace('.', ','))
            h = h.replace('(213 Bewertungen)', '(%d Bewertungen)' % rcount)
            h = h.replace('style="width:94%"', 'style="width:%d%%"' % round(rnum / 5 * 100))
        elif wid == 'mcbb0001':  # Kaufbox
            h = h.replace('<span class="rx-price" id="rx-price">34,90&nbsp;&euro;</span>', '<span class="rx-price" id="rx-price">%s&nbsp;&euro;</span>' % price)
            h = h.replace('<b id="rx-total">34,90&nbsp;&euro;</b>', '<b id="rx-total">%s&nbsp;&euro;</b>' % price)
            h = h.replace('var unitPrice = 34.90;', 'var unitPrice = %s;' % d['price'])
            h = h.replace('var freeShipThreshold = 100;', 'var freeShipThreshold = 150;')
            h = h.replace('<span class="rx-tier__q">10</span><span class="rx-tier__unit">mg</span>', '<span class="rx-tier__q">%s</span><span class="rx-tier__unit">mg</span>' % vol)
            h = h.replace('data-price="6.90"', 'data-price="9.90"').replace('+6,90&nbsp;&euro;', '+9,90&nbsp;&euro;')
        elif wid == 'mctab0001':  # Tabs
            h = re.sub(r'(<div class="sp-reta-desc">\s*<h3>Wirkung &amp; Forschung</h3>\s*<p>).*?(</p>)', lambda m: m.group(1) + d['research'] + m.group(2), h, count=1, flags=re.S)
            rows = ''.join('<tr><td>%s</td><td>%s</td></tr>\n' % (esc(a), esc(b)) for a, b in d['specs'])
            h = re.sub(r'(<div class="sp-reta-specs">\s*<h3>Technische Spezifikationen</h3>\s*<table>\n).*?(</table>)', lambda m: m.group(1) + rows + m.group(2), h, count=1, flags=re.S)
            h = h.replace('href="/peptid-lexikon/mots-c/"', 'href="/peptid-lexikon/"')
            h = h.replace('Steckbrief, Wirkmechanismus und Forschungsgebiete zu Mots-C im Lexikon nachlesen.', 'Steckbriefe und Forschungsgebiete vieler Peptide im Lexikon nachlesen.')
        elif wid == '9af0125':  # Lexikon-Karte → Ratgeber
            if k == 'cagri':
                h = h.replace('Mehr &uuml;ber Mots-C im Peptid-Lexikon', 'Stoffwechsel-Peptide im Vergleich')
                h = re.sub(r'Steckbrief, Wirkmechanismus.*?Peptid-Lexikon\.', 'Retatrutide, Tirzepatide, Semaglutid und Amylin-Analoga – Unterschiede und Forschungsstand im Überblick.', h, flags=re.S)
                h = h.replace('href="/peptid-lexikon/mots-c/"', 'href="/ratgeber/retatrutide-tirzepatide-semaglutid/"').replace('Zum Lexikon-Eintrag', 'Zum Ratgeber').replace('>Peptid-Lexikon</span>', '>Ratgeber</span>')
            else:
                h = h.replace('Mehr &uuml;ber Mots-C im Peptid-Lexikon', 'Peptide richtig rekonstituieren')
                h = re.sub(r'Steckbrief, Wirkmechanismus.*?Peptid-Lexikon\.', 'Schritt für Schritt: Bac Water, Konzentration berechnen und Lagerung nach dem Anmischen.', h, flags=re.S)
                h = h.replace('href="/peptid-lexikon/mots-c/"', 'href="/ratgeber/peptide-rekonstituieren/"').replace('Zum Lexikon-Eintrag', 'Zum Ratgeber').replace('>Peptid-Lexikon</span>', '>Ratgeber</span>')
        elif wid == 'c11f1b1':  # FAQ
            h = h.replace('Häufig gestellte Fragen zu Mots-C', 'Häufig gestellte Fragen zu ' + esc(d['short']))
            qs = iter(d['faq'])

            def rep(m):
                q, a = next(qs)
                return m.group(1) + esc(q) + m.group(2) + esc(a) + m.group(3)
            h = re.sub(r'(<summary>)[^<]*(<span class="sp-faq-arrow">▼</span></summary>\s*<p>)[^<]*(</p>)', rep, h)
        elif wid == 'mcrelw001':  # Zusammen gekauft
            cards = ''
            for r in d['related']:
                n, u, i, p = REL[r]
                cards += ('    <a href="https://peptrium.com%s" class="sp-rel-card">\n      <div class="sp-rel-card-img"><img src="%s" alt="%s" loading="lazy"></div>\n'
                          '      <div class="sp-rel-card-body">\n        <h3>%s</h3>\n        <div class="sp-rel-card-price">%s&nbsp;&euro;</div>\n      </div>\n    </a>\n') % (u, i, esc(n), esc(n), p)
            h = re.sub(r'(<div class="sp-rel-scroll">\n).*?(  </div>\n  <div class="sp-rel-dots")', lambda m: m.group(1) + cards + m.group(2), h, count=1, flags=re.S)
        elif wid == 'hwcap351x':
            h = h.replace('.elementor-element-abb8338', '.elementor-widget-icon-list').replace('{max-width', '{max-width')
        return h

    def walk(lst):
        out = []
        for x in lst:
            if x.get('id') == 'spwpenpromomc':
                continue  # kein Pen zu diesem Produkt
            s = x.get('settings')
            if isinstance(s, dict):
                if x.get('widgetType') == 'html':
                    s['html'] = fix_html(x['id'], s['html'])
                if x.get('widgetType') == 'image' and isinstance(s.get('image'), dict):
                    s['image'] = {'id': att, 'url': url}
            x['elements'] = walk(x.get('elements', []))
            out.append(x)
        return out
    els = walk(els)
    blob = json.dumps(els, ensure_ascii=False)
    left = [m for m in re.findall(r'.{30}(?:Mots-C|Mots-c|MOTS-c|mots-c).{30}', blob)]
    return {'content': els, 'page_settings': [], 'version': src['ver'], 'title': 'Einzelprodukt: ' + name, 'type': 'product'}, left


for k in P:
    tpl, left = build(k)
    fn = 'Peptrium-Vorlage-%s.json' % P[k]['slug']
    json.dump(tpl, open(fn, 'w'), ensure_ascii=False)
    print(fn, len(json.dumps(tpl)), 'Rest Mots-C:', left[:5])
