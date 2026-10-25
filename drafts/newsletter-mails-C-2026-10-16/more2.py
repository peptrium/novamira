# v2 (2026-10-16, Nutzer: "sehen die 5 Mails nicht zu gleich aus ... alles zu dunkel?")
# Heller Inhalt, jede Mail mit EIGENER Farbwelt; nur Logo-Leiste oben und Fuss unten bleiben dunkel (Markenrahmen).
# python3 more2.py          -> <name>.body (Omnisend-Platzhalter)
# python3 more2.py vorschau -> <name>.html mit Beispieldaten + vorschau-v2.html (alle nebeneinander)
import os, sys
_here = os.path.dirname(os.path.abspath(__file__))
exec(open(os.path.join(_here, 'more.py')).read().split('def build():')[0])

INK = '#0D0F12'; LMUT = '#5B6169'; LBD = '#E4E6E9'
THEME = 'bunt'  # bunt | hybrid | neutral
def pal(accent, tint, keep=False):
    """-> A (Akzent), T (helle Toenung), HB (Kopf-Hintergrund), HT, HS, HP, HE (Kopf-Textfarben)"""
    if THEME == 'bunt':
        return accent, tint, tint, INK, accent, LMUT, accent
    A = accent if keep else INK
    T = tint if keep else '#F5F6F7'
    if THEME == 'hybrid':
        return A, T, '#0D0F12', '#FFFFFF', (accent if keep else '#8E959E'), '#C9CDD2', (accent if keep else '#8E959E')
    return A, T, '#F5F6F7', INK, (accent if keep else '#8E959E'), LMUT, (accent if keep else LMUT)

SAMPLE = None
def S(n): return CP(n) if SAMPLE is None else SAMPLE.get(n, n)

def wrap(rows): return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E9EBED;"><tr><td align="center" style="padding:0;">'
                        f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;">{rows}</table></td></tr></table>')
def lrow(inner, pad='28px 20px', bg='#FFFFFF'): return f'<tr><td style="padding:{pad};text-align:left;background:{bg};font-family:{F};">{inner}</td></tr>'
def eyebrow(t, col): return f'<div style="font-size:12px;font-weight:800;letter-spacing:.16em;color:{col};margin-bottom:10px;">{t}</div>'
def h1(a, b='', col=INK, col2=LMUT, size=32): return f'<div style="font-size:{size}px;line-height:1.08;font-weight:800;color:{col};letter-spacing:-.02em;">{a}' + (f'<br><span style="color:{col2};">{b}</span>' if b else '') + '</div>'
def p(t, m='12px 0 0', col=LMUT): return f'<div style="font-size:15px;line-height:1.55;color:{col};margin:{m};">{t}</div>'
def lbtn(t, u, bg=INK, fg='#FFFFFF'): return f'<a href="{u}" style="display:block;background:{bg};color:{fg};text-align:center;text-decoration:none;font-weight:800;font-size:15px;line-height:1.2;padding:15px 12px;border-radius:12px;font-family:{F};">{t}</a>'
def lfoot(why=None):
    f = nohelp(foot())
    return f if why is None else f.replace('Du bekommst diese Mail, weil du den Peptrium-Newsletter abonniert hast.<br><a href="[[unsubscribe_link]]" style="color:#7D848D;">Abmelden</a>', why)
def card(inner, bd=LBD, bg='#FFFFFF', r=18, pad='16px'): return f'<div style="border:1px solid {bd};background:{bg};border-radius:{r}px;padding:{pad};">{inner}</div>'
def pimg(src, s=78, bg='#F5F6F7'): return f'<img src="{src}" width="{s}" height="{s}" alt="" style="display:block;width:{s}px;height:{s}px;border-radius:14px;border:0;background:{bg};">'

def build():
    M = {}
    # ---------- Willkommen 2 · bildlastig (2026-10-16, Nutzer: "viel Text, mehr mit Bildern") ----------
    c = 'willkommen-2'; A, AL, HB, HT, HS, HP, HE = pal('#2563EB', '#EAF1FE')
    E = U + 'sp-email/'
    def step(n, img, t, d): return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid {LBD};border-radius:18px;margin-bottom:10px;background:#FFF;"><tr>'
        f'<td width="116" valign="middle" style="width:116px;padding:8px;"><img src="{E}{img}" width="108" height="108" alt="" style="display:block;width:108px;height:108px;border-radius:12px;border:0;"></td>'
        f'<td valign="middle" style="padding:8px 14px 8px 6px;text-align:left;font-family:{F};"><div style="display:inline-block;width:24px;height:24px;line-height:24px;border-radius:50%;background:{A};color:#FFF;font-size:12px;font-weight:800;text-align:center;">{n}</div>'
        f'<div style="font-size:16px;font-weight:800;color:{INK};margin-top:6px;line-height:1.25;">{t}</div><div style="font-size:13px;line-height:1.45;color:{LMUT};margin-top:3px;">{d}</div></td></tr></table>')
    def chip(ic, t, u): return (f'<td width="33%" style="width:33%;padding:4px;"><a href="{u}" style="display:block;text-decoration:none;background:#F5F6F7;border-radius:14px;padding:12px 4px;text-align:center;font-family:{F};">'
        f'<div style="font-size:22px;line-height:1;">{ic}</div><div style="font-size:12px;font-weight:800;color:{INK};margin-top:6px;">{t}</div></a></td>')
    M['willkommen2'] = wrap(bar('So arbeiten wir')
        + lrow(eyebrow('AUS DEM LABOR', HE) + h1('Vom Labor', 'bis zu dir.', HT, HS) + p('Das passiert mit jedem Vial, bevor es bei dir ankommt.', col=HP), '34px 20px 24px', HB)
        + f'<tr><td style="padding:0;line-height:0;background:{HB};"><img src="{E}nl-w2-labor.jpg" width="600" alt="" style="display:block;width:100%;max-width:600px;height:auto;border:0;"></td></tr>'
        + lrow(step(1, 'nl-w2-vial.jpg', 'Gefriergetrocknet', 'Als stabiles Pulver – lange haltbar.')
               + step(2, 'nl-w2-test.jpg', 'HPLC- und LC-MS-geprüft', 'Reinheit ≥ 99 % für jede Charge.')
               + step(3, 'nl-w2-paket.jpg', 'Neutral in 2 Werktagen', 'Diskret verpackt, mit DHL-Sendungsnummer.'), '24px 16px 8px')
        + lrow(eyebrow('PRAKTISCH FÜR DICH', LMUT) + f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{chip("🧮", "Rechner", utm("/peptid-rechner/", c))}{chip("❄️", "Lagerung", utm("/lagerungs-guide/", c))}{chip("📖", "Lexikon", utm("/peptid-lexikon/", c))}</tr></table>', '16px 16px 8px')
        + lrow(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:2px dashed {A};border-radius:14px;background:{AL};"><tr>'
               f'<td style="padding:14px;text-align:left;font-family:{F};"><div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:{A};">DEIN CODE · 10 %</div><div style="font-size:19px;font-weight:800;font-family:{MONO};color:{INK};margin-top:4px;word-break:break-all;">{S("hallo_code")}</div></td>'
               f'<td width="100" style="width:100px;padding:14px 14px 14px 0;text-align:right;"><a href="{utm("/alle-produkte/", c)}" style="display:inline-block;background:{A};color:#FFF;text-decoration:none;font-weight:800;font-size:13px;padding:11px 14px;border-radius:10px;font-family:{F};">Einlösen</a></td></tr></table>', '12px 20px 32px')
        + lfoot())

    # ---------- Willkommen 3 · SAND/WARM (Start-Set) ----------
    c = 'willkommen-3'; A, AL, HB, HT, HS, HP, HE = pal('#B7791F', '#FBF6EE')
    plus = f'<td width="18" style="width:18px;text-align:center;font-size:20px;font-weight:800;color:{A};font-family:{F};">+</td>'
    setrow = f'<table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td>{pimg(PI["reta"], 80, "#FFF")}</td>{plus}<td>{pimg(PI["bac"], 80, "#FFF")}</td>{plus}<td>{pimg(PI["spritze"], 80, "#FFF")}</td></tr></table>'
    tip = lambda big, t, bg: f'<td width="33%" valign="top" style="width:33%;padding:4px;"><div style="background:{bg};border-radius:14px;padding:14px 6px;text-align:center;font-family:{F};"><div style="font-size:20px;font-weight:800;color:{INK};line-height:1.1;">{big}</div><div style="font-size:11px;line-height:1.4;color:{LMUT};margin-top:6px;">{t}</div></div></td>'
    def mini(src, name, info, u): return (f'<td width="50%" valign="top" style="width:50%;padding:4px;"><a href="{u}" style="display:block;text-decoration:none;border:1px solid {LBD};border-radius:16px;padding:12px;background:#FFF;text-align:center;font-family:{F};">'
                                          f'<img src="{src}" width="110" alt="" style="display:block;margin:0 auto;width:110px;height:110px;border-radius:12px;border:0;"><div style="font-size:15px;font-weight:800;color:{INK};margin-top:8px;">{name}</div><div style="font-size:12px;color:{LMUT};">{info}</div></a></td>')
    M['willkommen3'] = wrap(bar('Für den Start')
        + lrow(eyebrow('DIE BELIEBTESTE KOMBI', HE) + h1('Womit die meisten', 'starten.', HT, HS), '34px 20px 26px', HB)
        + f'<tr><td style="padding:0;line-height:0;background:{HB};"><img src="{U}sp-email/nl-w3-start.jpg" width="600" alt="" style="display:block;width:100%;max-width:600px;height:auto;border:0;"></td></tr>'
        + lrow(f'<div style="background:{AL};border:1px solid {"#EFE2CC" if THEME == "bunt" else LBD};border-radius:22px;padding:22px 14px 18px;text-align:center;">{setrow}'
               f'<div style="font-size:21px;font-weight:800;color:{INK};margin-top:16px;">Retatrutide Starter-Set</div><div style="font-size:13px;line-height:1.5;color:{LMUT};margin:4px 0 12px;">Peptid, Bac Water zum Anmischen und Spritzen – alles für den Anfang.</div>'
               f'<div style="font-size:26px;font-weight:800;color:{INK};margin-bottom:14px;">99,70 €</div>' + lbtn('Set ansehen', utm('/produkt/retatrutide/', c)) + '</div>', '22px 20px 22px')
        + lrow(eyebrow('SO SPARST DU', LMUT) + f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{tip("−10 %", "ab 3 Stück eines Peptids", "#EEF7EE" if THEME == "bunt" else "#F5F6F7")}{tip("150 €", "ab hier Versand gratis", "#EAF1FE" if THEME == "bunt" else "#F5F6F7")}{tip("🎁", "ab 200 € ein Geschenk dazu", "#FBF0E6" if THEME == "bunt" else "#F5F6F7")}</tr></table>', '26px 16px 6px')
        + lrow(eyebrow('AUCH OFT IM ERSTEN WARENKORB', LMUT) + f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{mini(PI["ghk"], "GHK-Cu", "50 mg · lyophilisiert", utm("/produkt/ghk-cu/", c))}{mini(PI["mots"], "Mots-C", "10 mg · lyophilisiert", utm("/produkt/mots-c/", c))}</tr></table>', '18px 16px 10px')
        + lrow(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid {"#EFE2CC" if THEME == "bunt" else LBD};border-radius:16px;overflow:hidden;"><tr><td width="30%" style="background:{A};padding:14px 4px;text-align:center;font-family:{F};"><div style="font-size:24px;font-weight:800;color:#FFF;line-height:1;">10 %</div></td>'
               f'<td style="background:{AL};padding:12px 10px;text-align:center;border-left:2px dashed {"#E3C99A" if THEME == "bunt" else "#C9CDD2"};font-family:{F};"><div style="font-size:17px;font-weight:800;font-family:{MONO};color:{INK};word-break:break-all;">{S("hallo_code")}</div><div style="font-size:11px;color:{LMUT};margin-top:3px;">dein Code · gültig bis {S("hallo_gueltig_bis")}</div></td></tr></table>', '10px 20px 32px')
        + lfoot())

    # ---------- Abo-Angebot · ORANGE (Abo-Farbe) ----------
    c = 'abo-angebot'; A, AL, HB, HT, HS, HP, HE = pal('#FF6B35', '#FFF2EC', True)
    chk = lambda t: f'<tr><td width="30" valign="top" style="width:30px;padding:7px 0;"><div style="width:22px;height:22px;line-height:22px;border-radius:50%;background:{A};color:#FFF;font-size:12px;font-weight:800;text-align:center;">✓</div></td><td style="padding:7px 0;text-align:left;font-size:14px;color:{INK};font-family:{F};">{t}</td></tr>'
    M['abo'] = wrap(bar('<span style="color:#FF8A5C;font-weight:700;">PEPTRIUM ABO</span>')
        + lrow(eyebrow('DU BESTELLST REGELMÄSSIG', '#E2551F' if THEME == 'bunt' else HE) + h1(S('abo_name') + ' im Abo:', '15 % günstiger.', HT, HS) + p('Du hast ' + S('abo_name') + ' schon mehrmals bestellt. Im Abo kommt es automatisch – und ab der 2. Lieferung günstiger.', col=HP), '34px 20px 24px', HB)
        + lrow(f'<div style="border:2px solid {A};background:#FFF;border-radius:20px;overflow:hidden;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td width="38%" style="width:38%;padding:14px;"><img src="{S("abo_bild")}" width="110" alt="" style="display:block;width:100%;height:auto;border-radius:14px;border:0;"></td>'
               f'<td style="padding:14px 14px 14px 0;text-align:left;font-family:{F};"><div style="font-size:12px;color:{LMUT};">Einzeln bestellt</div><div style="font-size:17px;font-weight:700;color:#9AA0A8;text-decoration:line-through;">{S("abo_preis")}</div><div style="font-size:12px;color:#E2551F;margin-top:8px;font-weight:800;">Im Abo ab 2. Lieferung</div><div style="font-size:30px;font-weight:800;color:{INK};line-height:1.1;">{S("abo_preis_abo")}</div></td></tr></table>'
               f'<div style="background:{A};padding:11px 14px;text-align:center;font-size:14px;font-weight:800;color:#FFF;font-family:{F};">Du sparst {S("abo_ersparnis")} pro Lieferung</div></div>', '24px 20px 20px')
        + lrow('<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' + chk('Lieferung automatisch in deinem Rhythmus') + chk('Jederzeit pausieren, ändern oder kündigen') + chk('Bequem per Guthaben – kein Überweisen jedes Mal') + '</table><div style="height:18px;"></div>'
               + lbtn('Abo einrichten', utm('/abo-stack/', c), A) + f'<div style="font-size:12px;color:{LMUT};margin-top:10px;text-align:center;">Keine Mindestlaufzeit.</div>', '0 20px 32px')
        + lfoot())

    # ---------- Wieder da · GRUEN (verfuegbar) ----------
    c = 'wieder-da'; A, AL, HB, HT, HS, HP, HE = pal('#16A34A', '#ECF9F0', True)
    M['wiederda'] = wrap(bar('Benachrichtigung')
        + lrow(f'<div style="display:inline-block;background:{A};border-radius:999px;padding:7px 14px;font-size:12px;font-weight:800;letter-spacing:.12em;color:#FFF;">● WIEDER LIEFERBAR</div>'
               + h1(S('wd_name'), 'ist wieder da.', HT, HS, 34).replace('line-height:1.08;', 'line-height:1.08;margin-top:16px;') + p('Du wolltest Bescheid bekommen – hier ist es.', col=HP), '34px 20px 24px', HB)
        + lrow(f'<div style="background:{AL};border-radius:22px;line-height:0;text-align:center;"><img src="{S("wd_bild")}" width="280" alt="" style="display:block;margin:0 auto;width:280px;max-width:100%;height:auto;border:0;"></div>'
               f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0 14px;"><tr><td style="text-align:left;font-family:{F};"><div style="font-size:13px;font-weight:800;color:{A};">✓ Sofort lieferbar</div><div style="font-size:12px;color:{LMUT};margin-top:2px;">Versand in 2 Werktagen</div></td>'
               f'<td align="right" style="font-size:26px;font-weight:800;color:{INK};white-space:nowrap;font-family:{F};">{S("wd_preis")}</td></tr></table>' + lbtn('Jetzt bestellen', S('wd_link'), A), '0 20px 32px')
        + lfoot('Du bekommst diese Mail einmalig, weil du auf der Produktseite um eine Benachrichtigung gebeten hast.<br><a href="[[unsubscribe_link]]" style="color:#7D848D;">Keine E-Mails mehr erhalten</a>'))

    # ---------- Passt dazu · VIOLETT (Empfehlung) ----------
    c = 'passt-dazu'; A, AL, HB, HT, HS, HP, HE = pal('#6D4AFF', '#F2EFFF')
    M['passtdazu'] = wrap(bar('Passt dazu')
        + lrow(eyebrow('ZU DEINER LETZTEN BESTELLUNG', HE) + h1('Passend zu', S('qv_basis') + '.', HT, HS) + p(S('qv_grund'), col=HP), '34px 20px 24px', HB)
        + lrow(f'<div style="border:1px solid {LBD};background:#FFF;border-radius:22px;overflow:hidden;box-shadow:0 6px 24px rgba(13,15,18,.08);"><div style="background:{AL};line-height:0;"><img src="{S("qv_bild")}" width="280" alt="" style="display:block;margin:0 auto;width:280px;max-width:100%;height:auto;border:0;"></div>'
               f'<div style="padding:16px;text-align:left;font-family:{F};"><div style="display:inline-block;background:{AL};color:{A};border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;letter-spacing:.08em;">EMPFEHLUNG</div>'
               f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:8px;"><tr><td style="text-align:left;"><div style="font-size:21px;font-weight:800;color:{INK};">{S("qv_name")}</div><div style="font-size:13px;color:{LMUT};margin-top:2px;">{S("qv_info")}</div></td><td align="right" style="font-size:21px;font-weight:800;color:{INK};white-space:nowrap;">{S("qv_preis")}</td></tr></table><div style="height:14px;"></div>' + lbtn('Ansehen', S('qv_link'), A) + '</div></div>', '24px 20px 18px')
        + lrow(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td width="50%" style="width:50%;padding:4px;"><div style="background:#F5F6F7;border-radius:12px;padding:12px;text-align:center;font-size:13px;font-weight:700;color:{INK};font-family:{F};">🚚 Ab 150 € gratis</div></td>'
               f'<td width="50%" style="width:50%;padding:4px;"><div style="background:#F5F6F7;border-radius:12px;padding:12px;text-align:center;font-size:13px;font-weight:700;color:{INK};font-family:{F};">−10 % ab 3 Stück</div></td></tr></table>', '0 16px 32px')
        + lfoot())
    return M

SAMPLE_DATA = {
    'hallo_code': 'HALLO-7K2QX', 'hallo_gueltig_bis': '15.11.2026',
    'abo_name': 'Retatrutide', 'abo_bild': PI['reta'], 'abo_preis': '79,90 €', 'abo_preis_abo': '67,92 €', 'abo_ersparnis': '11,98 €',
    'wd_name': 'BPC-157', 'wd_bild': IMG + '104D478F-BD97-4CA2-A036-29E66BDE6ECF-300x300.png', 'wd_preis': '33,92 €', 'wd_link': '#',
    'qv_basis': 'deinem Retatrutide', 'qv_grund': 'Wird oft zusammen mit Retatrutide erforscht – und passt gut in deine nächste Bestellung.', 'qv_name': 'GHK-Cu', 'qv_info': 'lyophilisiert · HPLC-geprüft', 'qv_preis': '39,90 €', 'qv_bild': PI['ghk'], 'qv_link': '#',
}

if __name__ == '__main__':
    os.chdir(_here)
    if len(sys.argv) > 2:
        THEME = sys.argv[2]
    if len(sys.argv) > 1 and sys.argv[1] == 'vorschau':
        SAMPLE = SAMPLE_DATA
        M = build()
        names = {'willkommen2': 'Willkommen 2 · Tag 2 · Blau', 'willkommen3': 'Willkommen 3 · Tag 6 · Sand', 'abo': 'Abo-Angebot · Orange', 'wiederda': 'Wieder da · Grün', 'passtdazu': 'Passt dazu · Violett'}
        if THEME != 'bunt':
            names = {k: v.split(' · ')[0] + (' · ' + v.split(' · ')[1] if 'Tag' in v else '') for k, v in names.items()}
        cols = ''.join(f'<div class="c"><div class="n">{names[k]}</div><div class="m">{v}</div></div>' for k, v in M.items())
        open('vorschau-v2' + ('' if THEME == 'bunt' else '-' + THEME) + '.html', 'w').write('<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mail-Entwürfe hell</title>'
            '<style>body{margin:0;background:#D9DCE0;font-family:-apple-system,Segoe UI,Arial,sans-serif}h1{font-size:20px;margin:0;padding:18px 16px 4px}.i{padding:0 16px 14px;color:#444;font-size:14px}'
            '.w{display:flex;gap:18px;overflow-x:auto;padding:0 16px 30px;scroll-snap-type:x mandatory}.c{flex:0 0 min(400px,88vw);scroll-snap-align:start}.n{font-weight:800;font-size:14px;margin:0 0 8px}.m{border-radius:14px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,.15)}</style></head>'
            f'<body><h1>Neue Mails – Variante ' + {'bunt': 'A · bunt', 'hybrid': 'B · dunkler Kopf', 'neutral': 'C · hell neutral'}[THEME] + f'</h1><div class="i">Zum Vergleich seitlich wischen. Beispieldaten.</div><div class="w">{cols}</div></body></html>')
        print('vorschau', list(M))
    else:
        for k, v in build().items():
            open(k + '.body', 'w').write(v)
        print('ok', list(build()))
