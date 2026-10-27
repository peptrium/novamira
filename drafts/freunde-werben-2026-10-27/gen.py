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
    txt = f'Ich bestelle meine Peptide bei Peptrium. Mit meinem Code {code} sparst du 10 %: https://peptrium.com'
    wa = 'https://wa.me/?text=' + urllib.parse.quote(txt, safe=':/')
    tg = 'https://t.me/share/url?url=' + urllib.parse.quote('https://peptrium.com', safe='') + '&text=' + urllib.parse.quote(f'Mit meinem Code {code} sparst du 10 % bei Peptrium.', safe='')
    b = lambda t, u, bg: f'<td width="50%" style="width:50%;padding:4px;"><a href="{u}" style="display:block;background:{bg};color:#FFF;text-align:center;text-decoration:none;font-weight:800;font-size:14px;padding:14px 6px;border-radius:12px;font-family:{F};">{t}</a></td>'
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{b("Per WhatsApp teilen", wa, "#1FA855")}{b("Per Telegram teilen", tg, "#229ED9")}</tr></table>'

def build():
    M = {}
    E = U + 'sp-email/'
    # ---------- 1. Ankuendigung (Kampagne an Kunden-Abonnenten, persoenlicher Code als Kontakt-Property) ----------
    c = 'freunde-werben'
    code = S('freund_code')
    M['freunde-start'] = wrap(bar('Freunde werben')
        + lrow(eyebrow('NEU · FREUNDE WERBEN', GOLD) + h1('Empfiehl uns weiter.', 'Ihr spart beide.', '#FFFFFF', '#8E959E', 34)
               + p('Dein Freund spart 10 % auf seine Bestellung. Du bekommst dafür 20 % auf deine nächste – für jeden neuen Freund wieder.', col='#C9CDD2'), '34px 20px 26px', '#0D0F12')
        + lrow(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{tile("10 %", "für deinen Freund – auf jede Bestellung mit deinem Code")}{tile("20 %", "für dich – nach jeder neuen Empfehlung", GOLD, GOLDL)}</tr></table>', '24px 16px 8px')
        + lrow(codebox('DEIN PERSÖNLICHER CODE', code, 'Einfach weitergeben – im Warenkorb oder an der Kasse eingeben.', GOLD, GOLDL) + '<div style="height:12px;"></div>' + share(code), '14px 20px 10px')
        + lrow(eyebrow('SO EINFACH GEHT\'S', LMUT) + '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
               + step(1, 'Code teilen', 'Per WhatsApp, Telegram oder persönlich.')
               + step(2, 'Dein Freund bestellt', 'Mit deinem Code spart er 10 %.')
               + step(3, 'Du bekommst 20 %', 'Sobald seine Bestellung bezahlt ist, kommt dein Code per Mail.', GOLD) + '</table>', '18px 20px 6px')
        + lrow(f'<div style="background:#0D0F12;border-radius:18px;padding:18px 16px;text-align:left;font-family:{F};"><div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:{GOLD};">BONUS AB 3 EMPFEHLUNGEN</div>'
               f'<div style="font-size:17px;font-weight:800;color:#FFF;margin-top:6px;line-height:1.3;">Einladung ins Partnerprogramm</div><div style="font-size:13px;line-height:1.5;color:#C9CDD2;margin-top:4px;">Mit eigenem Link und Provision auf jede Bestellung.</div></div>', '14px 20px 8px')
        + lrow(small('Gilt für jede neue Person, die mit deinem Code bestellt. Deinen eigenen Code kannst du nicht selbst einlösen. Dein 20-%-Code gilt 60 Tage, einmal einlösbar, nicht mit anderen Codes kombinierbar.'), '10px 20px 30px')
        + lfoot())

    # ---------- 2. Belohnung (Automation auf Ereignis sp_freund_belohnung) ----------
    c = 'freunde-belohnung'
    pshow = S('partner_show')
    partner = (f'<tr><td style="padding:6px 20px 10px;background:#FFFFFF;"><div style="display:{pshow};">'
               f'<div style="background:#0D0F12;border-radius:20px;padding:22px 18px;text-align:left;font-family:{F};">'
               f'<div style="font-size:11px;font-weight:800;letter-spacing:.16em;color:{GOLD};">WARUM DU DIESE MAIL BEKOMMST</div>'
               f'<div style="font-size:22px;font-weight:800;color:#FFF;margin-top:8px;line-height:1.2;">Du hast schon 3 Leute<br><span style="color:#8E959E;">von uns überzeugt.</span></div>'
               f'<div style="font-size:14px;line-height:1.55;color:#C9CDD2;margin-top:10px;">Das schaffen nur wenige. Deshalb laden wir dich in unser Partnerprogramm ein – statt Rabatt bekommst du dort Provision auf jede Bestellung über deinen Link.</div>'
               f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0 16px;">'
               + ''.join(f'<tr><td width="26" valign="top" style="width:26px;padding:5px 0;color:{GOLD};font-weight:800;font-family:{F};">✓</td><td style="padding:5px 0;font-size:14px;color:#FFF;text-align:left;font-family:{F};">{t}</td></tr>'
                         for t in ('15 % Provision auf jede Bestellung', 'Eigener Link und Code für deine Kontakte', 'Übersicht und Auszahlung in deinem Partnerkonto'))
               + '</table>' + lbtn('Partnerprogramm ansehen', utm('/affiliate-programm/', c), GOLD) + '</div></div></td></tr>')
    M['freunde-belohnung'] = wrap(bar('Danke')
        + lrow(eyebrow('EMPFEHLUNG NR. ' + S('freund_anzahl'), GOLD) + h1('Dein Tipp kam an.', 'Hier sind deine 20 %.', '#FFFFFF', '#8E959E', 34)
               + p('Jemand hat mit deinem Code bestellt und bezahlt. Danke, dass du Peptrium weiterempfiehlst!', col='#C9CDD2'), '34px 20px 26px', '#0D0F12')
        + lrow(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid {LBD};border-radius:18px;overflow:hidden;"><tr>'
               f'<td width="32%" style="width:32%;background:{GOLD};padding:18px 4px;text-align:center;font-family:{F};"><div style="font-size:34px;font-weight:800;color:#FFF;line-height:1;">20 %</div><div style="font-size:11px;color:#FFF;opacity:.85;margin-top:4px;">für dich</div></td>'
               f'<td style="background:{GOLDL};padding:14px 10px;text-align:center;border-left:2px dashed #E3C99A;font-family:{F};"><div style="font-size:20px;font-weight:800;font-family:{MONO};color:{INK};word-break:break-all;">{S("belohnung_code")}</div>'
               f'<div style="font-size:12px;color:{LMUT};margin-top:4px;">einmal einlösbar · gültig bis {S("belohnung_bis")}</div></td></tr></table><div style="height:14px;"></div>'
               + lbtn('Jetzt einlösen', utm('/alle-produkte/', c)), '24px 20px 14px')
        + partner
        + lrow(eyebrow('NOCH JEMAND IM KOPF?', LMUT) + p('Dein Empfehlungscode gilt weiter – für jeden neuen Freund gibt es wieder 20 % für dich.', '0 0 12px')
               + codebox('DEIN EMPFEHLUNGSCODE', S('freund_code'), '', INK) + '<div style="height:12px;"></div>' + share(S('freund_code')), '18px 20px 30px')
        + lfoot('Du bekommst diese Mail, weil jemand deinen Empfehlungscode bei Peptrium genutzt hat.'))

    # ---------- 3. Creator-Einladung (Kampagne nach Halloween an Kunden-Abonnenten) ----------
    c = 'creator'
    M['creator'] = wrap(bar('Partnerprogramm')
        + lrow(eyebrow('WIR WACHSEN', GOLD) + h1('Wir suchen Creator.', 'Vielleicht dich?', '#FFFFFF', '#8E959E', 36)
               + p('Du kennst Peptrium schon als Kunde – die beste Voraussetzung, um anderen davon zu erzählen. Und dafür Geld zu bekommen.', col='#C9CDD2'), '36px 20px 28px', '#0D0F12')
        + lrow(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{tile3("15 %", "Provision auf jede Bestellung")}{tile3("10 %", "Rabatt-Code für deine Follower")}{tile3("🎁", "Gratis-Produkt für dein erstes Video")}</tr></table>', '24px 16px 8px')
        + lrow(eyebrow('SO LÄUFT\'S', LMUT) + '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
               + step(1, 'Bewerben', 'Dauert 2 Minuten – mit deinem Social-Media-Account.')
               + step(2, 'Freigeschaltet werden', 'Du bekommst deinen Link, deinen Code und einen persönlichen Creator Manager.')
               + step(3, 'Erstes Video posten', 'Dein Gratis-Produkt kommt in 2 Werktagen. Ab dann verdienst du an jeder Bestellung.', GOLD) + '</table>', '18px 20px 8px')
        + lrow(card('<div style="font-size:15px;font-weight:800;color:' + INK + ';">Du brauchst keine riesige Reichweite.</div>'
                    + p('Ehrliche Videos von echten Kunden funktionieren oft besser als große Accounts. TikTok, Instagram oder YouTube – Hauptsache, du hast Lust auf Content.', '6px 0 0'), LBD, '#F5F6F7'), '12px 20px 10px')
        + lrow(lbtn('Jetzt als Partner bewerben', utm('/affiliate-programm/', c), INK) + '<div style="height:14px;"></div>'
               + small('Wichtig: Unsere Produkte sind ausschließlich für Laborforschungszwecke. In Videos daher keine Heilversprechen und keine Dosierangaben – dein Creator Manager hilft dir bei passenden Formaten. Es gelten die <a href="' + utm('/affiliate-bedingungen/', c) + '" style="color:' + LMUT + ';">Partner-Bedingungen</a>.'), '14px 20px 30px')
        + lfoot())
    return M

SAMPLE_DATA = {'freund_code': 'FREUND-ANNA7K', 'freund_anzahl': '3', 'belohnung_code': 'DANKE-Q4M8X2', 'belohnung_bis': '26.12.2026', 'partner_show': 'block'}

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
