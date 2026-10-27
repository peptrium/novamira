# Freunde werben + Creator-Einladung (2026-10-27, Nutzer: Freund-Code immer nutzbar (nicht vom Werber selbst),
# Werber bekommt 20 % nach bezahlter Bestellung, ab 3 Empfehlungen Partner-Einladung mit Begruendung; nach Halloween Creator-Mail).
# Rahmen/Helfer aus newsletter-mails-C-2026-10-16/more2.py (Variante hybrid).
# python3 gen.py          -> <name>.body (Omnisend-Platzhalter)
# python3 gen.py vorschau -> vorschau-<name>.html mit Beispieldaten
import os, sys, urllib.parse
_own = os.path.dirname(os.path.abspath(__file__))
_c = os.path.join(_own, '..', 'newsletter-mails-C-2026-10-16')
__file__ = os.path.join(_c, 'more2.py')
exec(open(__file__).read().split('\ndef build():')[0])
THEME = 'hybrid'
GOLD = '#C08A2E'; GOLDL = '#FBF4E6'

def tile(big, t, col=INK, bg='#F5F6F7'):
    return (f'<td width="50%" valign="top" style="width:50%;padding:4px;"><div style="background:{bg};border-radius:16px;padding:18px 8px;text-align:center;font-family:{F};">'
            f'<div style="font-size:34px;font-weight:800;color:{col};line-height:1;letter-spacing:-.02em;">{big}</div><div style="font-size:13px;line-height:1.4;color:{LMUT};margin-top:8px;">{t}</div></div></td>')
def tile3(big, t):
    return (f'<td width="33%" valign="top" style="width:33%;padding:4px;"><div style="background:#F5F6F7;border-radius:14px;padding:14px 6px;text-align:center;font-family:{F};">'
            f'<div style="font-size:20px;font-weight:800;color:{INK};line-height:1.1;">{big}</div><div style="font-size:11px;line-height:1.4;color:{LMUT};margin-top:6px;">{t}</div></div></td>')
def step(n, t, d, col=INK):
    return (f'<tr><td width="40" valign="top" style="width:40px;padding:8px 0;"><div style="width:28px;height:28px;line-height:28px;border-radius:50%;background:{col};color:#FFF;font-size:13px;font-weight:800;text-align:center;font-family:{F};">{n}</div></td>'
            f'<td style="padding:8px 0;text-align:left;font-family:{F};"><div style="font-size:16px;font-weight:800;color:{INK};line-height:1.3;">{t}</div><div style="font-size:13px;line-height:1.5;color:{LMUT};margin-top:2px;">{d}</div></td></tr>')
def codebox(label, code, sub='', col=INK, bg='#F5F6F7'):
    return (f'<div style="border:2px dashed {col};background:{bg};border-radius:16px;padding:18px 14px;text-align:center;font-family:{F};">'
            f'<div style="font-size:11px;font-weight:800;letter-spacing:.18em;color:{col};">{label}</div>'
            f'<div style="font-size:28px;font-weight:800;font-family:{MONO};color:{INK};margin-top:8px;letter-spacing:.04em;word-break:break-all;">{code}</div>'
            + (f'<div style="font-size:12px;color:{LMUT};margin-top:6px;">{sub}</div>' if sub else '') + '</div>')
def small(t): return f'<div style="font-size:12px;line-height:1.55;color:{LMUT};">{t}</div>'

def share(code):
    txt = f'Hey, ich hol meine Peps bei Peptrium – kommt schnell und diskret. Mit meinem Code {code} sparst du 10 % 👉 https://peptrium.com'
    wa = 'https://wa.me/?text=' + urllib.parse.quote(txt, safe=':/')
    tg = 'https://t.me/share/url?url=' + urllib.parse.quote('https://peptrium.com', safe='') + '&text=' + urllib.parse.quote(f'Hey, ich hol meine Peps bei Peptrium – kommt schnell und diskret. Mit meinem Code {code} sparst du 10 % 👉', safe='')
    qc = urllib.parse.quote(code, safe='')
    wa, tg = wa.replace(qc, code), tg.replace(qc, code)  # Omnisend-Platzhalter muss unkodiert bleiben
    b = lambda t, u, bg: f'<td width="50%" style="width:50%;padding:4px;"><a href="{u}" style="display:block;background:{bg};color:#FFF;text-align:center;text-decoration:none;font-weight:800;font-size:14px;padding:14px 6px;border-radius:12px;font-family:{F};">{t}</a></td>'
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{b("Per WhatsApp teilen", wa, "#1FA855")}{b("Per Telegram teilen", tg, "#229ED9")}</tr></table>'

def ex_row(l, r, bold=False):
    w = '800' if bold else '600'
    return (f'<tr><td style="padding:9px 0;border-bottom:1px solid {LBD};text-align:left;font-size:14px;color:{LMUT};font-family:{F};">{l}</td>'
            f'<td style="padding:9px 0;border-bottom:1px solid {LBD};text-align:right;font-size:15px;font-weight:{w};color:{INK};white-space:nowrap;font-family:{F};">{r}</td></tr>')
def dots(active):
    out = ''
    for i, t in enumerate(('1. Freund', '2. Freund', '3. Freund'), 1):
        on = i <= active
        out += (f'<td width="33%" valign="top" style="width:33%;padding:4px;text-align:center;font-family:{F};"><div style="height:6px;border-radius:3px;background:{GOLD if on else "#3A4048"};"></div>'
                f'<div style="font-size:12px;font-weight:800;color:{"#FFF" if on else "#8E959E"};margin-top:8px;">{t}</div><div style="font-size:11px;color:#C9CDD2;margin-top:2px;">20 % für dich</div></td>')
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{out}</tr></table>'
def bullet(t, col=GOLD, fg=INK):
    return f'<tr><td width="26" valign="top" style="width:26px;padding:5px 0;color:{col};font-weight:800;font-family:{F};">✓</td><td style="padding:5px 0;font-size:14px;line-height:1.45;color:{fg};text-align:left;font-family:{F};">{t}</td></tr>'
def msgbox(code):
    return (f'<div style="background:#E7F7EC;border-radius:16px 16px 16px 4px;padding:14px 16px;text-align:left;font-family:{F};font-size:15px;line-height:1.5;color:{INK};">'
            f'Hey, ich hol meine Peps bei Peptrium – kommt schnell und diskret. Mit meinem Code <b>{code}</b> sparst du 10 % 👉 peptrium.com</div>')

def build():
    M = {}
    # ---------- 1. Ankuendigung v2 ----------
    c = 'freunde-werben'
    code = S('freund_code')
    M['freunde-start'] = wrap(bar('Freunde werben')
        + lrow(eyebrow('DEIN PERSÖNLICHER CODE IST FERTIG', GOLD) + h1('Teil Peptrium.', 'Spar 20 %.', '#FFFFFF', '#8E959E', 38)
               + p('Jeder Freund, der mit deinem Code bestellt, spart 10 %. Und du bekommst dafür jedes Mal 20 % auf deine nächste Bestellung.', col='#C9CDD2')
               + '<div style="height:20px;"></div>'
               + f'<div style="border:2px dashed {GOLD};border-radius:16px;padding:16px 14px;text-align:center;font-family:{F};"><div style="font-size:11px;font-weight:800;letter-spacing:.18em;color:{GOLD};">DEIN CODE</div>'
               + f'<div style="font-size:30px;font-weight:800;font-family:{MONO};color:#FFF;margin-top:6px;letter-spacing:.04em;word-break:break-all;">{code}</div></div>'
               + '<div style="height:12px;"></div>' + share(code), '34px 20px 28px', '#0D0F12')
        + lrow(eyebrow('SO EINFACH GEHT\'S', LMUT) + '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
               + step(1, 'Code schicken', 'Ein Klick oben – die Nachricht ist schon fertig.')
               + step(2, 'Dein Freund spart 10 %', 'Er gibt den Code im Warenkorb oder an der Kasse ein.')
               + step(3, 'Du bekommst 20 %', 'Sobald er bezahlt hat, kommt dein Code automatisch per Mail.', GOLD) + '</table>', '26px 20px 8px')
        + lrow(eyebrow('DAS BRINGT ES DIR KONKRET', LMUT) + '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
               + ex_row('Dein Freund bestellt für 150 €', 'spart 15 €') + ex_row('Deine nächste Bestellung: 190 €', 'du sparst 38 €', True)
               + '</table>' + small('<div style="margin-top:8px;">Beispielrechnung.</div>'), '18px 20px 8px')
        + lrow(eyebrow('DIE NACHRICHT FÜR DEINE FREUNDE', LMUT) + msgbox(code)
               + small('<div style="margin-top:8px;">Gedrückt halten zum Kopieren – oder einfach oben auf WhatsApp/Telegram tippen.</div>'), '18px 20px 8px')
        + lrow(card('<div style="font-size:15px;font-weight:800;color:' + INK + ';">Wem du ihn schicken kannst</div><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">'
                    + bullet('Freunden, die sich auch für Peps interessieren') + bullet('Leuten, die dich schon mal gefragt haben, wo du bestellst') + bullet('deinen Telegram- oder WhatsApp-Gruppen') + '</table>', LBD, '#F5F6F7'), '14px 20px 8px')
        + lrow(f'<div style="background:#0D0F12;border-radius:20px;padding:20px 16px;font-family:{F};text-align:left;"><div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:{GOLD};">KEIN LIMIT</div>'
               f'<div style="font-size:19px;font-weight:800;color:#FFF;margin-top:6px;line-height:1.3;">Jeder neue Freund = wieder 20 % für dich.</div>'
               f'<div style="font-size:13px;line-height:1.5;color:#C9CDD2;margin:4px 0 14px;">Und ab 3 Freunden laden wir dich ins Partnerprogramm ein – dann gibt es Geld statt Rabatt.</div>' + dots(0) + '</div>', '14px 20px 8px')
        + lrow(small('Gilt für jede neue Person, die mit deinem Code bestellt und bezahlt. Deinen eigenen Code kannst du nicht selbst einlösen. Dein 20-%-Code gilt 60 Tage, einmal einlösbar, nicht mit anderen Codes kombinierbar. Deinen Code findest du jederzeit in deinem Kundenkonto.'), '12px 20px 30px')
        + lfoot())

    # ---------- 2. Belohnung v2 ----------
    c = 'freunde-belohnung'
    pshow = S('partner_show')
    partner = (f'<tr><td style="padding:6px 20px 10px;background:#FFFFFF;"><div style="display:{pshow};">'
               f'<div style="background:#0D0F12;border-radius:20px;padding:22px 18px;text-align:left;font-family:{F};">'
               f'<div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:{GOLD};">WARUM WIR DICH EINLADEN</div>'
               f'<div style="font-size:23px;font-weight:800;color:#FFF;margin-top:8px;line-height:1.2;">3 Leute haben auf<br><span style="color:#8E959E;">deine Empfehlung bestellt.</span></div>'
               f'<div style="font-size:14px;line-height:1.55;color:#C9CDD2;margin-top:10px;">Das heißt: Man vertraut deinem Urteil. Genau das ist alles, was ein Partner braucht. Du machst einfach weiter wie bisher – nur bekommst du dafür keinen Rabatt mehr, sondern <b style="color:#FFF;">Geld</b>.</div>'
               f'<div style="background:#1A1E24;border-radius:14px;padding:14px;margin:16px 0;text-align:center;"><div style="font-size:12px;color:#C9CDD2;">Deine 3 Empfehlungen hätten dir als Partner schon gebracht:</div>'
               f'<div style="font-size:30px;font-weight:800;color:{GOLD};margin-top:4px;">{S("partner_beispiel")}</div><div style="font-size:11px;color:#8E959E;margin-top:2px;">bei 20 % Provision auf ihre Bestellungen</div></div>'
               '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;">'
               + bullet('Bis zu 25 % Provision auf jede Bestellung über deinen Link', GOLD, '#FFF')
               + bullet('Eigener Code: deine Kontakte sparen weiter 10 %', GOLD, '#FFF')
               + bullet('Persönlicher Ansprechpartner + Partnerkonto mit allen Bestellungen', GOLD, '#FFF')
               + bullet('Kostenlos, kein Mindestumsatz, jederzeit beenden', GOLD, '#FFF')
               + '</table>' + lbtn('In 2 Minuten bewerben', utm('/affiliate-programm/', c), GOLD) + '</div></div></td></tr>')
    M['freunde-belohnung'] = wrap(bar('Danke')
        + lrow(eyebrow('EMPFEHLUNG NR. ' + S('freund_anzahl'), GOLD) + h1('Dein Tipp kam an.', 'Hier sind deine 20 %.', '#FFFFFF', '#8E959E', 34)
               + p('Jemand hat mit deinem Code bestellt und bezahlt. Danke, dass du Peptrium weiterempfiehlst!', col='#C9CDD2'), '34px 20px 26px', '#0D0F12')
        + lrow(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid {LBD};border-radius:18px;overflow:hidden;"><tr>'
               f'<td width="32%" style="width:32%;background:{GOLD};padding:18px 4px;text-align:center;font-family:{F};"><div style="font-size:34px;font-weight:800;color:#FFF;line-height:1;">20 %</div><div style="font-size:11px;color:#FFF;opacity:.85;margin-top:4px;">für dich</div></td>'
               f'<td style="background:{GOLDL};padding:14px 10px;text-align:center;border-left:2px dashed #E3C99A;font-family:{F};"><div style="font-size:20px;font-weight:800;font-family:{MONO};color:{INK};word-break:break-all;">{S("belohnung_code")}</div>'
               f'<div style="font-size:12px;color:{LMUT};margin-top:4px;">einmal einlösbar · gültig bis {S("belohnung_bis")}</div></td></tr></table><div style="height:14px;"></div>'
               + lbtn('Jetzt einlösen', utm('/alle-produkte/', c)), '24px 20px 14px')
        + partner
        + lrow(eyebrow('NOCH JEMAND IM KOPF?', LMUT) + p('Dein Empfehlungscode gilt weiter – jeder neue Freund bringt dir wieder 20 %.', '0 0 12px')
               + codebox('DEIN EMPFEHLUNGSCODE', S('freund_code'), '', INK) + '<div style="height:12px;"></div>' + share(S('freund_code')), '18px 20px 30px')
        + lfoot('Du bekommst diese Mail, weil jemand deinen Empfehlungscode bei Peptrium genutzt hat.'))

    # ---------- 3. Creator v2 ----------
    c = 'creator'
    calc = ''.join(ex_row(f'{n} Bestellungen im Monat', v, n >= 70) for n, v in ((10, '≈ 380 €'), (30, '≈ 1.430 €'), (70, '≈ 3.330 €'), (170, '≈ 8.080 €')))
    M['creator'] = wrap(bar('Partnerprogramm')
        + lrow(eyebrow('NEBENBEI VERDIENEN', GOLD) + h1('Teil deine Erfahrung.', 'Verdien daran mit.', '#FFFFFF', '#8E959E', 36)
               + p('Du kennst Peptrium schon als Kunde. Als Partner empfiehlst du uns weiter – und bekommst bis zu 25 % von jeder Bestellung, die über dich kommt.', col='#C9CDD2')
               + f'<div style="background:#1A1E24;border-radius:16px;padding:16px;margin-top:20px;text-align:left;font-family:{F};"><div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:{GOLD};">ECHTES BEISPIEL</div>'
               f'<div style="font-size:16px;line-height:1.45;color:#FFF;margin-top:6px;">Einer unserer Partner verdient im Schnitt <b style="color:{GOLD};">rund 5.000 € Provision im Monat</b> – nur durchs Teilen und Empfehlen.</div></div>', '36px 20px 28px', '#0D0F12')
        + lrow(eyebrow('WAS DRIN IST', LMUT) + '<div style="font-size:15px;line-height:1.5;color:' + LMUT + ';margin-bottom:10px;">Unsere Bestellungen liegen im Schnitt bei rund 190 €. So viel bleibt bei dir hängen:</div>'
               + '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' + calc + '</table>'
               + small('<div style="margin-top:8px;">Rechenbeispiel mit 190 € pro Bestellung, 20 % Provision bis 25 Bestellungen im Monat, darüber 25 %. Wie viel du verdienst, hängt von deiner Reichweite ab.</div>'), '26px 20px 8px')
        + lrow(card('<div style="font-size:15px;font-weight:800;color:' + INK + ';">Du kümmerst dich nur ums Teilen.</div><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">'
                    + bullet('Kein Lager, kein Versand, kein Kundenservice – das machen wir')
                    + bullet('Deine Follower sparen 10 % mit deinem Code – das macht das Empfehlen leicht')
                    + bullet('Gratis-Produkt für dein erstes Video')
                    + bullet('Partnerkonto: jede Bestellung und Provision live im Blick')
                    + bullet('Kostenlos, kein Mindestumsatz, jederzeit beenden') + '</table>', LBD, '#F5F6F7'), '14px 20px 8px')
        + lrow(eyebrow('SO STARTEST DU', LMUT) + '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
               + step(1, 'Bewerben', '2 Minuten, mit deinem TikTok-, Instagram- oder YouTube-Account.')
               + step(2, 'Link, Code und Creator Manager', 'Dein Ansprechpartner hilft dir mit Ideen und Formaten.')
               + step(3, 'Posten und verdienen', 'Jede Bestellung über deinen Link oder Code bringt dir Provision.', GOLD) + '</table>', '18px 20px 8px')
        + lrow(f'<div style="border:2px solid {GOLD};background:{GOLDL};border-radius:18px;padding:16px;text-align:left;font-family:{F};"><div style="font-size:15px;font-weight:800;color:{INK};">⏰ Bester Zeitpunkt: jetzt.</div>'
               f'<div style="font-size:14px;line-height:1.5;color:{LMUT};margin-top:4px;">Am 27.11. ist Black Friday – die stärkste Kaufwoche des Jahres. Wer jetzt startet, ist bis dahin eingerichtet.</div></div>', '14px 20px 8px')
        + lrow(lbtn('Jetzt kostenlos bewerben', utm('/affiliate-programm/', c), INK) + '<div style="height:14px;"></div>'
               + small('Wichtig: Unsere Produkte sind ausschließlich für Laborforschungszwecke. In deinen Inhalten daher keine Heilversprechen und keine Dosierangaben – erzähl von deiner Erfahrung mit Peptrium (Bestellung, Versand, Qualität). Es gelten die <a href="' + utm('/affiliate-bedingungen/', c) + '" style="color:' + LMUT + ';">Partner-Bedingungen</a>.'), '14px 20px 30px')
        + lfoot())
    return M

SAMPLE_DATA = {'freund_code': 'FREUND-ANNA7K', 'freund_anzahl': '3', 'belohnung_code': 'DANKE-Q4M8X2', 'belohnung_bis': '26.12.2026', 'partner_show': 'block', 'partner_beispiel': '115 €'}

if __name__ == '__main__':
    os.chdir(_own)
    if len(sys.argv) > 1 and sys.argv[1] == 'vorschau':
        for pv, extra in (('', {}), ('-nr1', {'freund_anzahl': '1', 'partner_show': 'none'})):
            SAMPLE = dict(SAMPLE_DATA, **extra)
            for k, v in build().items():
                if pv and k != 'freunde-belohnung': continue
                open(f'vorschau-{k}{pv}.html', 'w').write('<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' + k + '</title></head><body style="margin:0;background:#E9EBED;">' + v + '</body></html>')
        print('vorschau ok')
    else:
        for k, v in build().items():
            open(k + '.body', 'w').write(v)
        print('ok')
