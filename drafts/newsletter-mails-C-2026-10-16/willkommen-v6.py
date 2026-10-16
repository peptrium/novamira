# Willkommen 2 + 3 nach der Vorlage des Nutzers (2026-10-16, "ich will die mails in der form haben"):
# W2 komplett dunkel mit Hero-Foto + 3 Bild-Schrittkarten, W3 dunkler Kopf mit Set-Foto + heller Inhalt.
# Bilder: uploads/sp-email/nl-w2-*.jpg, nl-w3-set.jpg, Icons nl-ic-<name>-w|d.png (Linien-Icons, 96 px).
# python3 willkommen-v6.py          -> willkommen2.body / willkommen3.body (Omnisend-Platzhalter)
# python3 willkommen-v6.py vorschau -> vorschau-v6.html (Beispieldaten)
import os, sys
_here = os.path.dirname(os.path.abspath(__file__))
exec(open(os.path.join(_here, 'more2.py')).read().split('\ndef build():')[0])

E = U + 'sp-email/'
DK = '#030506'; DK2 = '#11151A'; DBD = '#232830'; GR = '#8E959E'; LT = '#C9CDD2'; ORG = '#FF7A1A'
SAMPLE = None
def S(n): return CP(n) if SAMPLE is None else SAMPLE.get(n, n)
def img(src, alt, r=0): return f'<img src="{src}" width="600" alt="{alt}" style="display:block;width:100%;max-width:600px;height:auto;border:0;{f"border-radius:{r}px;" if r else ""}">'
def head(eyebrow_t, a, b, sub_t, bg):
    return (f'<tr><td style="padding:34px 24px 18px;background:{bg};text-align:left;font-family:{F};">'
            f'<div style="font-size:12px;font-weight:800;letter-spacing:.18em;color:{GR};margin-bottom:12px;">{eyebrow_t}</div>'
            f'<div style="font-size:40px;line-height:1.02;font-weight:800;color:#FFFFFF;letter-spacing:-.025em;">{a}<br><span style="color:{GR};">{b}</span></div>'
            f'<div style="font-size:17px;line-height:1.45;color:{LT};margin-top:16px;">{sub_t}</div></td></tr>')
def cell(inner, pad, bg): return f'<tr><td style="padding:{pad};background:{bg};text-align:left;font-family:{F};">{inner}</td></tr>'
def frame(rows, bg): return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{bg};"><tr><td align="center" style="padding:0;">'
                             f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{bg};">{rows}</table></td></tr></table>')

def build():
    M = {}
    # ---------- Willkommen 2: komplett dunkel ----------
    c = 'willkommen-2'
    def chip(ic, t, u): return (f'<td width="33%" style="width:33%;padding:4px;"><a href="{u}" style="display:block;text-decoration:none;background:{DK2};border:1px solid {DBD};border-radius:14px;padding:14px 4px 12px;text-align:center;font-family:{F};">'
                                f'<img src="{E}nl-ic-{ic}-w.png" width="28" height="28" alt="" style="display:block;margin:0 auto;width:28px;height:28px;border:0;"><div style="font-size:13px;font-weight:800;color:#FFF;margin-top:8px;">{t}</div></a></td>')
    steps = ''.join(f'<div style="margin-bottom:10px;line-height:0;">{img(E + f"nl-w2-schritt{i}.jpg", alt)}</div>' for i, alt in
                    [(1, 'Schritt 1: Gefriergetrocknet – als stabiles Pulver, lange haltbar.'), (2, 'Schritt 2: HPLC- und LC-MS-geprüft – Reinheit ≥ 99 % für jede Charge.'), (3, 'Schritt 3: Neutral in 2 Werktagen – diskret verpackt, mit DHL-Sendungsnummer.')])
    M['willkommen2'] = frame(bar('So arbeiten wir')
        + head('AUS DEM LABOR', 'Vom Labor', 'bis zu dir.', 'Das passiert mit jedem Vial, bevor es bei dir ankommt.', DK)
        + f'<tr><td style="padding:0;line-height:0;background:{DK};">{img(E + "nl-w2-hero.jpg", "Peptrium Vials im Labor")}</td></tr>'
        + cell(steps, '6px 12px 6px', DK)
        + cell(f'<div style="font-size:12px;font-weight:800;letter-spacing:.18em;color:{GR};margin:0 4px 10px;">PRAKTISCH FÜR DICH</div><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
               + chip('rechner', 'Rechner', utm('/peptid-rechner/', c)) + chip('lagerung', 'Lagerung', utm('/lagerungs-guide/', c)) + chip('lexikon', 'Lexikon', utm('/peptid-lexikon/', c)) + '</tr></table>', '18px 12px 8px', DK)
        + cell(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:2px dashed #3A4048;border-radius:14px;background:{DK2};"><tr>'
               f'<td style="padding:14px 16px;text-align:left;font-family:{F};"><div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:{LT};">DEIN CODE · 10 %</div><div style="font-size:21px;font-weight:800;color:#FFF;margin-top:4px;word-break:break-all;">{S("hallo_code")}</div></td>'
               f'<td width="120" style="width:120px;padding:14px 14px 14px 0;text-align:right;"><a href="{utm("/alle-produkte/", c)}" style="display:inline-block;background:{ORG};color:#0D0F12;text-decoration:none;font-weight:800;font-size:15px;padding:12px 20px;border-radius:12px;font-family:{F};">Einlösen</a></td></tr></table>', '14px 16px 30px', DK)
        + lfoot(), DK)

    # ---------- Willkommen 3: dunkler Kopf + Set-Foto, heller Inhalt ----------
    c = 'willkommen-3'; LB = '#F4F5F6'
    plus = f'<td width="22" style="width:22px;text-align:center;font-size:22px;font-weight:800;color:{INK};font-family:{F};">+</td>'
    th = lambda k: f'<td><img src="{PI[k]}" width="84" height="84" alt="" style="display:block;width:84px;height:84px;border-radius:14px;border:0;background:#0D0F12;"></td>'
    tile = lambda ic, big, t: (f'<td width="33%" valign="top" style="width:33%;padding:4px;"><div style="background:{LB};border-radius:14px;padding:14px 6px;text-align:center;font-family:{F};">'
                               f'<img src="{E}nl-ic-{ic}-d.png" width="26" height="26" alt="" style="display:block;margin:0 auto 8px;width:26px;height:26px;border:0;">'
                               + (f'<div style="font-size:19px;font-weight:800;color:{INK};line-height:1.1;">{big}</div>' if big else '')
                               + f'<div style="font-size:12px;line-height:1.4;color:{LMUT};margin-top:4px;">{t}</div></div></td>')
    prod = lambda k, n, i, u: (f'<td width="50%" valign="top" style="width:50%;padding:4px;"><a href="{u}" style="display:block;text-decoration:none;border:1px solid {LBD};border-radius:16px;padding:12px;background:#FFF;text-align:center;font-family:{F};">'
                               f'<img src="{PI[k]}" width="110" alt="" style="display:block;margin:0 auto;width:110px;height:110px;border-radius:12px;border:0;background:#0D0F12;"><div style="font-size:16px;font-weight:800;color:{INK};margin-top:8px;">{n}</div><div style="font-size:12px;color:{LMUT};">{i}</div></a></td>')
    M['willkommen3'] = frame(bar('Für den Start')
        + head('DIE BELIEBTESTE KOMBI', 'Womit die meisten', 'starten.', 'Alles, was du für den Anfang brauchst – in einem Set.', DK).replace('Womit die meisten<br>', 'Womit die<br>meisten ')
        + f'<tr><td style="padding:0;line-height:0;background:{DK};">{img(E + "nl-w3-set.jpg", "Retatrutide Starter-Set mit Bac Water und Spritzen")}</td></tr>'
        + cell(f'<div style="background:#FFFFFF;border:1px solid {LBD};border-radius:22px;padding:20px 14px 16px;text-align:center;">'
               f'<table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr>{th("reta")}{plus}{th("bac")}{plus}{th("spritze")}</tr></table>'
               f'<div style="font-size:21px;font-weight:800;color:{INK};margin-top:14px;">Retatrutide Starter-Set</div><div style="font-size:14px;line-height:1.5;color:{LMUT};margin:4px 0 10px;">Vial, Bac Water zum Anmischen und Spritzen – alles für den Anfang.</div>'
               f'<div style="font-size:28px;font-weight:800;color:{INK};margin-bottom:14px;">94,70 €</div>' + lbtn('Set ansehen', utm('/produkt/retatrutide/', c)) + '</div>', '18px 16px 8px', '#FFFFFF')
        + cell(f'<div style="font-size:12px;font-weight:800;letter-spacing:.18em;color:{LMUT};margin:0 4px 10px;">SO SPARST DU</div><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
               + tile('tag', '−10 %', 'ab 3 Stück eines Peptids') + tile('truck', '150 €', 'ab hier Versand gratis') + tile('gift', '', 'ab 200 € ein Geschenk dazu') + '</tr></table>', '18px 12px 8px', '#FFFFFF')
        + cell(f'<div style="font-size:12px;font-weight:800;letter-spacing:.18em;color:{LMUT};margin:0 4px 10px;">AUCH OFT IM ERSTEN WARENKORB</div><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
               + prod('ghk', 'GHK-Cu', '50 mg · lyophilisiert', utm('/produkt/ghk-cu/', c)) + prod('mots', 'MOTS-C', '10 mg · lyophilisiert', utm('/produkt/mots-c/', c)) + '</tr></table>', '18px 12px 8px', '#FFFFFF')
        + cell(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid {LBD};border-radius:16px;overflow:hidden;"><tr><td width="30%" style="background:{INK};padding:16px 4px;text-align:center;font-family:{F};"><div style="font-size:26px;font-weight:800;color:#FFF;line-height:1;">10 %</div></td>'
               f'<td style="background:{LB};padding:12px 10px;text-align:center;border-left:2px dashed #C9CDD2;font-family:{F};"><div style="font-size:19px;font-weight:800;color:{INK};word-break:break-all;">{S("hallo_code")}</div><div style="font-size:12px;color:{LMUT};margin-top:3px;">dein Code · gültig bis {S("hallo_gueltig_bis")}</div></td></tr></table>', '14px 16px 30px', '#FFFFFF')
        + lfoot(), DK)
    return M

if __name__ == '__main__':
    os.chdir(_here)
    if len(sys.argv) > 1 and sys.argv[1] == 'vorschau':
        SAMPLE = {'hallo_code': 'HALLO-7K2QX', 'hallo_gueltig_bis': '15.11.2026'}
        M = build()
        cols = ''.join(f'<div class="c"><div class="m">{v}</div></div>' for v in M.values())
        open('vorschau-v6.html', 'w').write('<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Willkommen v6</title>'
            '<style>body{margin:0;background:#fff}.w{display:block}.c{width:100%}</style></head>'
            f'<body><div class="w">{cols}</div></body></html>')
        print('vorschau', list(M))
    else:
        for k, v in build().items():
            open(k + '.body', 'w').write(v)
        print('ok', list(build()))
