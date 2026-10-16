# Willkommen 1 nach der Vorlage des Nutzers (2026-10-16, "die wilkommensmail 1 soll in dem layout neu gemacht werden").
# Komplett dunkel: Hero-Bild mit Text/Button im Bild (nl-w1-hero.jpg, ganzes Bild verlinkt), Code-Karte mit Box-Foto (nl-w1-box.jpg),
# 4 Merkmale mit Linien-Icons, "Beliebt bei Peptrium" mit 3 Produktkarten (Shop-Produktbilder), Fuss.
# python3 willkommen1-v7.py          -> willkommen1.body (Omnisend-Platzhalter)
# python3 willkommen1-v7.py vorschau -> vorschau-w1.html (Beispieldaten)
import os, sys
_here = os.path.dirname(os.path.abspath(__file__))
exec(open(os.path.join(_here, 'more2.py')).read().split('\ndef build():')[0])

E = U + 'sp-email/'
BGD = '#050607'; CARD2 = '#0E1114'; BD2 = '#22272E'; GR = '#A3AAB3'; LT = '#C9CDD2'
SAMPLE = None
def S(n): return CP(n) if SAMPLE is None else SAMPLE.get(n, n)
def cell(inner, pad, bg=BGD, align='left'): return f'<tr><td style="padding:{pad};background:{bg};text-align:{align};font-family:{F};">{inner}</td></tr>'
def wbtn(t, u, pad='14px 12px', size=15): return (f'<a href="{u}" style="display:block;background:#FFFFFF;color:#0D0F12;text-align:center;text-decoration:none;font-weight:800;'
                                                  f'font-size:{size}px;line-height:1.2;padding:{pad};border-radius:10px;font-family:{F};">{t} &rarr;</a>')

def build():
    c = 'willkommen'
    hero = (f'<tr><td style="padding:0;line-height:0;background:{BGD};"><a href="{utm("/alle-produkte/", c)}" style="display:block;line-height:0;">'
            f'<img src="{E}nl-w1-hero2.jpg" width="600" alt="Willkommen bei Peptrium. Du hast den ersten Schritt gemacht – zu mehr Wissen, besseren Entscheidungen und höchster Qualität. Zum Shop" '
            f'style="display:block;width:100%;max-width:600px;height:auto;border:0;"></a></td></tr>')
    code = (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;background:{CARD2};border:1px solid {BD2};border-radius:18px;overflow:hidden;"><tr>'
            f'<td valign="middle" style="padding:20px 10px 20px 18px;text-align:left;font-family:{F};">'
            f'<div style="font-size:11px;font-weight:800;letter-spacing:.18em;color:{LT};">DEIN WILLKOMMENSVORTEIL</div>'
            f'<div style="font-size:54px;line-height:1;font-weight:800;color:#FFFFFF;letter-spacing:-.03em;margin-top:10px;">10&nbsp;%</div>'
            f'<div style="font-size:15px;font-weight:700;color:{LT};margin-top:6px;">auf deine erste Bestellung</div>'
            f'<div style="border:1.5px dashed #4A5058;border-radius:10px;padding:10px 8px;margin-top:14px;text-align:center;font-size:18px;font-weight:800;letter-spacing:.06em;color:#FFFFFF;word-break:break-all;">{S("hallo_code")}</div>'
            f'<div style="font-size:12px;color:#9AA0A8;margin-top:8px;">Gültig bis {S("hallo_gueltig_bis")} · einmal einlösbar</div>'
            f'<div style="margin-top:14px;">' + wbtn('Code jetzt einlösen', utm('/alle-produkte/', c), '12px 8px', 14) + '</div></td>'
            f'<td width="45%" valign="middle" bgcolor="{CARD2}" style="width:45%;padding:0;line-height:0;background:{CARD2};"><img src="{E}nl-w1-box3.jpg" width="200" alt="" style="display:block;width:100%;height:auto;border:0;"></td>'
            '</tr></table>')
    feat = lambda ic, t, d, last=False: (f'<td width="25%" valign="top" style="width:25%;padding:6px 4px;text-align:center;font-family:{F};{"" if last else f"border-right:1px solid {BD2};"}">'
                                         f'<img src="{E}nl-ic-{ic}-w.png" width="34" height="34" alt="" style="display:block;margin:0 auto 10px;width:34px;height:34px;border:0;">'
                                         f'<div style="font-size:13px;line-height:1.25;font-weight:800;color:#FFFFFF;">{t}</div><div style="font-size:11.5px;line-height:1.4;color:#9AA0A8;margin-top:6px;">{d}</div></td>')
    feats = ('<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
             + feat('flask', 'HPLC / LC-MS geprüft', 'Jede Charge laboranalytisch getestet.') + feat('diamond', '≥ 99 % Reinheit', 'Unser Mindeststandard.')
             + feat('box', 'Diskret verpackt', 'Neutral und sicher.') + feat('truck', 'Schneller Versand', 'In der Regel in 2 Werktagen bei dir.', True) + '</tr></table>')
    prod = lambda img, n, i, u: (f'<td width="33%" valign="top" style="width:33%;padding:3px;">'
                                 f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;background:{CARD2};border:1px solid {BD2};border-radius:14px;overflow:hidden;"><tr><td style="padding:0;line-height:0;">'
                                 f'<a href="{u}"><img src="{img}" width="190" alt="{n}" style="display:block;width:100%;height:auto;border:0;"></a></td></tr>'
                                 f'<tr><td style="padding:10px 8px 12px;text-align:center;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#FFFFFF;">{n}</div>'
                                 f'<div style="font-size:11px;color:#9AA0A8;margin:2px 0 10px;white-space:nowrap;">{i}</div>' + wbtn('Ansehen', u, '10px 4px', 13) + '</td></tr></table></td>')
    prods = ('<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
             + prod(E + 'nl-w1-p-ghk.jpg', 'GHK-Cu', '50 mg · lyophilisiert', utm('/produkt/ghk-cu/', c))
             + prod(E + 'nl-w1-p-mots.jpg', 'MOTS-C', '10 mg · lyophilisiert', utm('/produkt/mots-c/', c))
             + prod(E + 'nl-w1-p-pen.jpg', 'Peptrium Pen', 'Retatrutide 40 mg', utm('/produkt/peptrium-pen-retatrutide/', c)) + '</tr></table>')
    rows = (hero
            + cell(code, '16px 16px 8px')
            + cell(f'<div style="font-size:14px;font-weight:800;letter-spacing:.16em;color:{GR};">WAS JEDE BESTELLUNG</div><div style="font-size:24px;font-weight:800;letter-spacing:.02em;color:#FFFFFF;margin:2px 0 18px;">AUSZEICHNET.</div>' + feats, '26px 16px 10px')
            + cell(f'<div style="font-size:24px;font-weight:800;color:#FFFFFF;">BELIEBT BEI PEPTRIUM.</div><div style="font-size:14px;color:{LT};margin:4px 0 14px;">Entdecke ausgewählte Produkte für deine Forschungsziele.</div>' + prods, '28px 13px 30px')
            + lfoot())
    return {'willkommen1': f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{BGD};"><tr><td align="center" style="padding:0;">'
                           f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{BGD};">{rows}</table></td></tr></table>'}

if __name__ == '__main__':
    os.chdir(_here)
    if len(sys.argv) > 1 and sys.argv[1] == 'vorschau':
        SAMPLE = {'hallo_code': 'HALLO-7K2QX', 'hallo_gueltig_bis': '15.11.2026'}
        M = build()
        open('vorschau-w1.html', 'w').write('<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Willkommen 1</title></head>'
                                            f'<body style="margin:0;background:{BGD};"><div class="w"><div class="c">{M["willkommen1"]}</div></div></body></html>')
        print('vorschau')
    else:
        for k, v in build().items():
            open(k + '.body', 'w').write(v)
        print('ok')
