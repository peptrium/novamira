# Neue Mails 2026-10-16: Willkommen 2+3, Abo-Angebot, Wieder da, Passt dazu, Bewertung (+ Warenkorb-Versandzeile).
# Gleicher Rahmen wie Layout B (Logo, Farben, Fuß), jede Mail mit eigenem Aufbau.
# python3 more.py          -> <name>.body mit Omnisend-Platzhaltern
# python3 more.py vorschau -> vorschau.html (Beispieldaten, alle Mails nebeneinander)
import os, sys, re
_here = os.path.dirname(os.path.abspath(__file__))
_g = open(os.path.join(_here, '..', 'newsletter-mails-B-2026-10-16', 'gen.py')).read().split('M={}')[0]
exec(_g)

SAMPLE = None
def S(n): return CP(n) if SAMPLE is None else SAMPLE.get(n, n)
LMUT = '#5B6169'; LBD = '#E4E6E9'; LS = '#F5F6F7'; INK = '#0D0F12'; OR = '#FF6B35'
CHRG = 'background-color:#3A4048;background-image:linear-gradient(135deg,#F2F4F6 0%,#8E959E 35%,#3A4048 60%,#C9CED6 100%);'
IMG = U + '2026/08/'
PI = {'reta': IMG + '8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png', 'bac': IMG + 'F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png',
      'spritze': IMG + 'insulinspritze-10er-pack-wide-300x300.png', 'ghk': IMG + '9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png',
      'mots': IMG + '7255FFFB-6823-45F9-AC96-88637F1411CB-300x300.png'}

def bar(right): return f'<tr><td style="padding:16px 20px;background:{BG};border-bottom:1px solid {BD};font-family:{F};"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="text-align:left;"><img src="{LOGO}" width="30" height="30" alt="" style="display:inline-block;vertical-align:middle;border:0;"><span style="display:inline-block;vertical-align:middle;margin-left:8px;font-size:12px;font-weight:800;letter-spacing:.3em;color:#E6E8EB;">PEPTRIUM</span></td><td style="text-align:right;font-size:11px;color:{DIM};">{right}</td></tr></table></td></tr>'
def label(t, mb=8, col=None): return f'<div style="text-align:left;font-size:12px;font-weight:700;letter-spacing:.16em;color:{col or DIM};margin-bottom:{mb}px;">{t}</div>'
def nohelp(f): return re.sub(r'<div style="font-size:15px;font-weight:700;color:#FFFFFF;margin-bottom:12px;">Fragen\?.*?</table><div style="height:28px;"></div>', '', f, flags=re.S)
def bighead(a, b='', size=34): return f'<div style="text-align:left;font-size:{size}px;line-height:1.08;font-weight:800;color:#FFF;letter-spacing:-.02em;">{a}' + (f'<br><span style="color:#8E959E;">{b}</span>' if b else '') + '</div>'
def sub(t, m='12px 0 0'): return f'<div style="text-align:left;font-size:15px;line-height:1.55;color:{MUT};margin:{m};">{t}</div>'
def mini(keys, c):
    PP = {'ghk': ('GHK-Cu', 'ab 39,90 €', '/produkt/ghk-cu/'), 'mots': ('Mots-C', 'ab 34,90 €', '/produkt/mots-c/'), 'reta': ('Retatrutide', 'ab 74,90 €', '/produkt/retatrutide/')}
    cell = lambda k: f'<td width="50%" valign="top" style="width:50%;padding:5px;"><a href="{utm(PP[k][2], c)}" style="display:block;text-decoration:none;background:{CARD};border:1px solid {BD};border-radius:14px;overflow:hidden;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td width="64" style="width:64px;line-height:0;"><img src="{U}sp-email/nl-p-{k}.jpg" width="64" height="64" alt="" style="display:block;border:0;"></td><td style="padding:8px 10px;text-align:left;font-family:{F};"><div style="font-size:13px;font-weight:800;color:#FFF;">{PP[k][0]}</div><div style="font-size:11px;color:{MUT};">{PP[k][1]}</div></td></tr></table></a></td>'
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>' + ''.join(cell(k) for k in keys) + '</tr></table>'
def foot_service(why): return nohelp(foot()).replace('Du bekommst diese Mail, weil du den Peptrium-Newsletter abonniert hast.<br><a href="[[unsubscribe_link]]" style="color:#7D848D;">Abmelden</a>', why)

def build():
    M = {}
    # ---------- Willkommen 2 (Tag 2): So arbeiten wir ----------
    c = 'willkommen-2'
    def tl(n, t, d, last=False):
        line = '' if last else f'<div style="width:2px;height:100%;min-height:34px;background:{BD};margin:6px auto 0;"></div>'
        return (f'<tr><td width="52" valign="top" style="width:52px;padding:0;"><div style="{CHRG}width:38px;height:38px;border-radius:50%;padding:2px;margin:0 auto;"><div style="width:38px;height:38px;line-height:38px;border-radius:50%;background:{BG};color:#FFF;font-weight:800;font-size:15px;text-align:center;font-family:{F};">{n}</div></div>{line}</td>'
                f'<td valign="top" style="padding:6px 0 {0 if last else 18}px 8px;text-align:left;font-family:{F};"><div style="font-size:16px;font-weight:800;color:#FFF;">{t}</div><div style="font-size:13px;line-height:1.55;color:{MUT};margin-top:3px;">{d}</div></td></tr>')
    def tool(ic, t, d, u): return f'<a href="{u}" style="display:block;text-decoration:none;border:1px solid {BD};background:{CARD};border-radius:14px;padding:12px;margin-bottom:8px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td width="44" style="width:44px;"><div style="width:36px;height:36px;line-height:36px;border-radius:10px;background:#1F242B;text-align:center;font-size:17px;">{ic}</div></td><td style="text-align:left;font-family:{F};"><div style="font-size:14px;font-weight:800;color:#FFF;">{t}</div><div style="font-size:12px;color:{MUT};">{d}</div></td><td width="20" style="width:20px;text-align:right;color:{DIM};font-size:18px;font-family:{F};">›</td></tr></table></a>'
    M['willkommen2'] = body(bar('So arbeiten wir')
        + row(label('AUS DEM LABOR') + bighead('Vom Labor', 'bis zu dir.') + sub('Was mit jedem Vial passiert, bevor es bei dir ankommt – in 4 Schritten.', '12px 0 24px')
              + '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
              + tl(1, 'Hergestellt & gefriergetrocknet', 'Jedes Peptid kommt als lyophilisiertes Pulver – stabil und lange haltbar.')
              + tl(2, 'Geprüft per HPLC & LC-MS', 'Reinheit (≥ 99 %) und Identität werden für jede Charge geprüft.')
              + tl(3, 'Neutral verpackt', 'Ohne Hinweis auf den Inhalt, sicher gepolstert.')
              + tl(4, 'In 2 Werktagen bei dir', 'Versand mit DHL, die Sendungsnummer bekommst du per E-Mail.', True) + '</table>', '32px 20px 28px')
        + row(label('DEINE WERKZEUGE', 12) + tool('🧮', 'Peptid-Rechner', 'Die richtige Menge Bac Water berechnen', utm('/peptid-rechner/', c))
              + tool('❄️', 'Lagerungs-Guide', 'So bleiben deine Peptide stabil', utm('/lagerungs-guide/', c))
              + tool('📖', 'Peptid-Lexikon', 'Was über jedes Peptid erforscht wird', utm('/peptid-lexikon/', c)), '0 20px 22px')
        + row(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px dashed #59606A;border-radius:14px;"><tr><td style="padding:14px;text-align:left;font-family:{F};"><div style="font-size:11px;font-weight:700;letter-spacing:.16em;color:{DIM};">DEIN CODE · 10 %</div><div style="font-size:18px;font-weight:800;font-family:{MONO};color:{ACC};margin-top:4px;word-break:break-all;">{S("hallo_code")}</div></td><td width="96" style="width:96px;padding:14px 14px 14px 0;text-align:right;"><a href="{utm("/alle-produkte/", c)}" style="display:inline-block;background:#FFF;color:#0D0F12;text-decoration:none;font-weight:800;font-size:13px;padding:10px 12px;border-radius:10px;font-family:{F};">Einlösen</a></td></tr></table>', '0 20px 30px')
        + foot())

    # ---------- Willkommen 3 (Tag 6): Womit die meisten starten ----------
    c = 'willkommen-3'
    def pimg(k, s=78): return f'<img src="{PI[k]}" width="{s}" height="{s}" alt="" style="display:block;width:{s}px;height:{s}px;border-radius:14px;border:0;background:#0D0F12;">'
    plus = f'<td width="18" style="width:18px;text-align:center;font-size:20px;font-weight:800;color:{DIM};font-family:{F};">+</td>'
    setrow = f'<table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td>{pimg("reta")}</td>{plus}<td>{pimg("bac")}</td>{plus}<td>{pimg("spritze")}</td></tr></table>'
    tip = lambda big, t: f'<td width="33%" valign="top" style="width:33%;padding:4px;"><div style="border:1px solid {BD};background:{CARD};border-radius:14px;padding:14px 6px;text-align:center;font-family:{F};"><div style="font-size:19px;font-weight:800;color:#FFF;line-height:1.1;">{big}</div><div style="font-size:11px;line-height:1.4;color:{MUT};margin-top:6px;">{t}</div></div></td>'
    M['willkommen3'] = body(bar('Für den Start')
        + row(label('DIE BELIEBTESTE KOMBI') + bighead('Womit die meisten', 'starten.'), '32px 20px 18px')
        + row(f'<div style="{CHRG}border-radius:22px;padding:2px;"><div style="background:{CARD};border-radius:20px;padding:22px 14px 18px;">{setrow}'
              f'<div style="font-size:21px;font-weight:800;color:#FFF;margin-top:16px;">Retatrutide Starter-Set</div><div style="font-size:13px;line-height:1.5;color:{MUT};margin:4px 0 12px;">Peptid, Bac Water zum Anmischen und Spritzen – alles für den Anfang.</div>'
              f'<div style="font-size:26px;font-weight:800;color:#FFF;margin-bottom:14px;">94,70 €</div>' + btn('Set ansehen', utm('/produkt/retatrutide/', c)) + '</div></div>', '0 20px 24px')
        + row(label('SO SPARST DU', 10) + f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{tip("−10 %", "ab 3 Stück eines Peptids")}{tip("150 €", "ab hier Versand gratis")}{tip("🎁", "ab 200 € ein Geschenk dazu")}</tr></table>', '0 16px 24px')
        + row(label('AUCH OFT IM ERSTEN WARENKORB', 8) + mini(['ghk', 'mots'], c), '0 15px 24px')
        + row(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid {BD};border-radius:16px;overflow:hidden;"><tr><td width="30%" style="background:#FFFFFF;padding:14px 4px;text-align:center;font-family:{F};"><div style="font-size:24px;font-weight:800;color:#0D0F12;line-height:1;">10 %</div></td><td style="background:{CARD};padding:12px 10px;text-align:center;border-left:2px dashed {IN};font-family:{F};"><div style="font-size:17px;font-weight:800;font-family:{MONO};color:#FFF;word-break:break-all;">{S("hallo_code")}</div><div style="font-size:11px;color:{DIM};margin-top:3px;">dein Code · gültig bis {S("hallo_gueltig_bis")}</div></td></tr></table>', '0 20px 30px')
        + foot())

    # ---------- Abo-Angebot (Wiederkaeufer) ----------
    c = 'abo-angebot'
    chk = lambda t: f'<tr><td width="30" valign="top" style="width:30px;padding:7px 0;"><div style="width:22px;height:22px;line-height:22px;border-radius:50%;background:{OR};color:#FFF;font-size:12px;font-weight:800;text-align:center;">✓</div></td><td style="padding:7px 0;text-align:left;font-size:14px;color:#D9DDE2;font-family:{F};">{t}</td></tr>'
    M['abo'] = body(bar('<span style="color:#FF8A5C;font-weight:700;">PEPTRIUM ABO</span>')
        + row(label('DU BESTELLST REGELMÄSSIG', 8, '#FF8A5C') + bighead(S('abo_name') + ' im Abo:', '15 % günstiger.') + sub('Du hast ' + S('abo_name') + ' schon mehrmals bestellt. Im Abo kommt es automatisch – und ab der 2. Lieferung günstiger.'), '32px 20px 22px')
        + row(f'<div style="border:1px solid #5A3322;background:{CARD};border-radius:20px;overflow:hidden;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td width="38%" style="width:38%;padding:14px;"><img src="{S("abo_bild")}" width="110" alt="" style="display:block;width:100%;height:auto;border-radius:14px;border:0;background:#0D0F12;"></td>'
              f'<td style="padding:14px 14px 14px 0;text-align:left;font-family:{F};"><div style="font-size:12px;color:{MUT};">Einzeln bestellt</div><div style="font-size:17px;font-weight:700;color:{DIM};text-decoration:line-through;">{S("abo_preis")}</div><div style="font-size:12px;color:#FF8A5C;margin-top:8px;font-weight:700;">Im Abo ab 2. Lieferung</div><div style="font-size:28px;font-weight:800;color:#FFF;line-height:1.1;">{S("abo_preis_abo")}</div></td></tr></table>'
              f'<div style="background:#2A1A12;padding:11px 14px;text-align:center;font-size:13px;font-weight:700;color:#FFB08F;font-family:{F};">Du sparst {S("abo_ersparnis")} pro Lieferung</div></div>', '0 20px 22px')
        + row('<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' + chk('Lieferung automatisch in deinem Rhythmus') + chk('Jederzeit pausieren, ändern oder kündigen') + chk('Bequem per Guthaben – kein Überweisen jedes Mal') + '</table><div style="height:18px;"></div>'
              + f'<a href="{utm("/abo-stack/", c)}" style="display:block;background:{OR};color:#FFFFFF;text-align:center;text-decoration:none;font-weight:800;font-size:15px;padding:15px 12px;border-radius:12px;font-family:{F};">Abo einrichten</a><div style="font-size:12px;color:{DIM};margin-top:10px;text-align:center;">Keine Mindestlaufzeit.</div>', '0 20px 30px')
        + foot())

    # ---------- Wieder da ----------
    c = 'wieder-da'
    M['wiederda'] = body(bar('Benachrichtigung')
        + row(f'<div style="display:inline-block;border:1px solid #1F5C3A;background:#0F2A1C;border-radius:999px;padding:7px 14px;font-size:12px;font-weight:800;letter-spacing:.12em;color:#4ADE80;">● WIEDER LIEFERBAR</div>'
              f'<div style="font-size:34px;line-height:1.08;font-weight:800;color:#FFF;margin:18px 0 8px;letter-spacing:-.02em;">{S("wd_name")}<br><span style="color:#8E959E;">ist wieder da.</span></div><div style="font-size:15px;line-height:1.55;color:{MUT};">Du wolltest Bescheid bekommen – hier ist es.</div>', '34px 20px 20px')
        + row(f'<div style="background:#0D0F12;border:1px solid {BD};border-radius:20px;overflow:hidden;line-height:0;"><img src="{S("wd_bild")}" width="300" alt="" style="display:block;margin:0 auto;width:300px;max-width:100%;height:auto;border:0;"></div>'
              f'<div style="font-size:24px;font-weight:800;color:#FFF;margin:16px 0 4px;">{S("wd_preis")}</div><div style="font-size:12px;color:{DIM};margin-bottom:16px;">Sofort lieferbar · Versand in 2 Werktagen</div>' + btn('Jetzt bestellen', S('wd_link')), '0 20px 30px')
        + foot_service('Du bekommst diese Mail einmalig, weil du auf der Produktseite um eine Benachrichtigung gebeten hast.'))

    # ---------- Passt dazu (Querverkauf ~10 Tage nach Lieferung) ----------
    c = 'passt-dazu'
    M['passtdazu'] = body(bar('Passt dazu')
        + row(label('ZU DEINER LETZTEN BESTELLUNG') + bighead('Passend zu', S('qv_basis') + '.') + sub(S('qv_grund'), '12px 0 22px')
              + f'<div style="border:1px solid {BD};background:{CARD};border-radius:20px;overflow:hidden;"><div style="background:#0D0F12;line-height:0;"><img src="{S("qv_bild")}" width="300" alt="" style="display:block;margin:0 auto;width:300px;max-width:100%;height:auto;border:0;"></div>'
              f'<div style="padding:16px;text-align:left;font-family:{F};"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="text-align:left;"><div style="font-size:20px;font-weight:800;color:#FFF;">{S("qv_name")}</div><div style="font-size:13px;color:{MUT};margin-top:2px;">{S("qv_info")}</div></td><td align="right" style="font-size:20px;font-weight:800;color:#FFF;white-space:nowrap;">{S("qv_preis")}</td></tr></table><div style="height:14px;"></div>' + btn('Ansehen', S('qv_link')) + '</div></div>', '32px 20px 22px')
        + row(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="text-align:left;font-size:13px;line-height:1.6;color:{MUT};font-family:{F};">🚚 Ab 150 € versandkostenfrei &nbsp;·&nbsp; −10 % ab 3 Stück</td></tr></table>', '0 20px 30px')
        + foot())

    # ---------- Bewertung (hell, wie "Nach Lieferung") ----------
    c = 'bewertung'
    star = lambda n: f'<td width="20%" style="width:20%;padding:3px;"><a href="{S("bw_link")}&amp;r={n}" style="display:block;text-decoration:none;border:1px solid {LBD};border-radius:12px;padding:10px 0 8px;text-align:center;background:#FFFFFF;font-family:{F};"><div style="font-size:24px;line-height:1;color:#F5A524;">★</div><div style="font-size:13px;font-weight:800;color:{INK};margin-top:4px;">{n}</div></a></td>'
    M['bewertung'] = ('<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E9EBED;"><tr><td align="center" style="padding:0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;">'
        + bar('Bestellung #' + S('bw_nr'))
        + f'<tr><td style="padding:32px 20px 8px;text-align:left;font-family:{F};"><div style="font-size:12px;font-weight:700;letter-spacing:.16em;color:{LMUT};">20 SEKUNDEN</div><div style="font-size:32px;line-height:1.1;font-weight:800;color:{INK};margin:8px 0 10px;letter-spacing:-.02em;">Wie zufrieden<br>bist du?</div><div style="font-size:15px;line-height:1.55;color:{LMUT};">Deine ehrliche Meinung zu {S("bw_produkt")} hilft uns – und anderen, die forschen. Tipp einfach auf eine Zahl.</div></td></tr>'
        + f'<tr><td style="padding:18px 17px 6px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{"".join(star(n) for n in (1, 2, 3, 4, 5))}</tr></table></td></tr>'
        + f'<tr><td style="padding:0 20px 26px;font-family:{F};"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="text-align:left;font-size:11px;color:{LMUT};">1 = gar nicht</td><td style="text-align:right;font-size:11px;color:{LMUT};">5 = sehr zufrieden</td></tr></table></td></tr>'
        + f'<tr><td style="padding:20px;background:{LS};text-align:left;font-family:{F};"><div style="font-size:14px;font-weight:800;color:{INK};">Etwas nicht in Ordnung?</div><div style="font-size:13px;line-height:1.55;color:{LMUT};margin-top:4px;">Schreib uns lieber direkt – wir kümmern uns: <a href="https://t.me/peptrium" style="color:{INK};font-weight:700;">Telegram</a> oder <a href="mailto:info@peptrium.com" style="color:{INK};font-weight:700;">info@peptrium.com</a></div></td></tr>'
        + nohelp(foot()).replace('weil du den Peptrium-Newsletter abonniert hast', 'weil du bei Peptrium bestellt hast und den Newsletter abonniert hast') + '</table></td></tr></table>')
    return M

SAMPLE_DATA = {
    'hallo_code': 'HALLO-7K2QX', 'hallo_gueltig_bis': '15.11.2026',
    'abo_name': 'Retatrutide', 'abo_bild': PI['reta'], 'abo_preis': '74,90 €', 'abo_preis_abo': '63,67 €', 'abo_ersparnis': '11,23 €',
    'wd_name': 'BPC-157', 'wd_bild': IMG + '104D478F-BD97-4CA2-A036-29E66BDE6ECF-300x300.png', 'wd_preis': '39,90 €', 'wd_link': '#',
    'qv_basis': 'deinem GHK-Cu', 'qv_grund': 'Wird oft zusammen mit GHK-Cu erforscht – und passt gut in deine nächste Bestellung.', 'qv_name': 'Mots-C', 'qv_info': '10 mg · lyophilisiert', 'qv_preis': '34,90 €', 'qv_bild': PI['mots'], 'qv_link': '#',
    'bw_link': 'https://peptrium.com/?sp_bewertung=abc', 'bw_nr': '3285', 'bw_produkt': 'deiner Bestellung',
}

if __name__ == '__main__':
    os.chdir(_here)
    if len(sys.argv) > 1 and sys.argv[1] == 'vorschau':
        SAMPLE = SAMPLE_DATA
        M = build()
        names = {'willkommen2': 'Willkommen 2 · Tag 2', 'willkommen3': 'Willkommen 3 · Tag 6', 'abo': 'Abo-Angebot', 'wiederda': 'Wieder da', 'passtdazu': 'Passt dazu', 'bewertung': 'Bewertung'}
        for k, v in M.items():
            open(k + '.html', 'w').write(f'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:{BG};">{v}</body></html>')
        print('vorschau', list(M))
    else:
        for k, v in build().items():
            open(k + '.body', 'w').write(v)
        print('ok', list(build()))
