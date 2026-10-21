# Dunkle Double-Opt-in-Bestaetigungsmail (2026-10-21, user: "mach die bestaetigungsmail auch dunkel").
# Stil = Willkommen 1 v7 (#050607, Karten #0E1114). {{LINK}} = Bestaetigungslink.
# python3 gen.py -> doi.body (Fragment fuer sp-newsletter.php) + vorschau.html
F = "-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif"
U = 'https://peptrium.com/wp-content/uploads/'
E = U + 'sp-email/'
BG = '#050607'; CARD = '#0E1114'; BD = '#22272E'; GR = '#8E959E'; LT = '#C9CDD2'; MU = '#9AA0A8'

def row(inner, pad, bg=BG, align='left'):
    return f'<tr><td style="padding:{pad};background:{bg};text-align:{align};font-family:{F};">{inner}</td></tr>'

def step(n, t, state):
    # state: done / now / next
    if state == 'done':
        c = f'<div style="width:30px;height:30px;line-height:30px;border-radius:50%;background:#1F7A4A;color:#FFFFFF;font-size:15px;font-weight:800;margin:0 auto;">&#10003;</div>'
        tc = LT
    elif state == 'now':
        c = f'<div style="width:30px;height:30px;line-height:30px;border-radius:50%;background:#FFFFFF;color:#0D0F12;font-size:14px;font-weight:800;margin:0 auto;">{n}</div>'
        tc = '#FFFFFF'
    else:
        c = f'<div style="width:28px;height:28px;line-height:28px;border-radius:50%;border:1px solid #3A4048;color:{MU};font-size:14px;font-weight:800;margin:0 auto;">{n}</div>'
        tc = MU
    return (f'<td width="33%" valign="top" style="width:33%;text-align:center;padding:0 2px;font-family:{F};">{c}'
            f'<div style="font-size:12.5px;line-height:1.3;font-weight:700;color:{tc};margin-top:8px;">{t}</div></td>')

def build():
    logo = (f'<img src="{U}2026/08/IMG_2644-cropped-300x300.png" width="64" height="64" alt="Peptrium" style="display:block;margin:0 auto;width:64px;height:64px;border:0;">'
            f'<div style="font-size:13px;font-weight:800;letter-spacing:.34em;padding-left:.34em;color:#E6E8EB;margin-top:8px;">PEPTRIUM</div>')
    head = (f'<div style="font-size:11px;font-weight:800;letter-spacing:.2em;color:{LT};">NUR NOCH EIN KLICK</div>'
            f'<div style="font-size:34px;line-height:1.1;font-weight:800;color:#FFFFFF;letter-spacing:-.02em;margin-top:12px;">Bestätige deine<br><span style="color:{GR};">Anmeldung.</span></div>'
            f'<div style="font-size:15px;line-height:1.55;color:#B4BAC2;margin-top:14px;">Danke für dein Interesse am Peptrium-Newsletter. Tippe auf den Button – danach schicken wir dir deinen persönlichen Code.</div>')
    btn = (f'<a href="{{{{LINK}}}}" style="display:block;background:#FFFFFF;color:#0D0F12;text-align:center;text-decoration:none;font-weight:800;'
           f'font-size:16px;line-height:1.2;padding:17px 12px;border-radius:12px;font-family:{F};">Anmeldung bestätigen &rarr;</a>')
    steps = ('<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
             + step(1, 'Angemeldet', 'done') + step(2, 'Bestätigen', 'now') + step(3, 'Code erhalten', 'next') + '</tr></table>')
    code = (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid {BD};border-radius:18px;overflow:hidden;">'
            f'<tr><td background="{E}nl-w1-cardbg3.jpg" bgcolor="{CARD}" valign="middle" style="background:{CARD} url({E}nl-w1-cardbg3.jpg) right center / cover no-repeat;padding:0;">'
            f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td valign="middle" style="padding:22px 6px 22px 18px;text-align:left;font-family:{F};">'
            f'<div style="font-size:11px;font-weight:800;letter-spacing:.18em;color:{LT};">WARTET AUF DICH</div>'
            f'<div style="font-size:46px;line-height:1;font-weight:800;color:#FFFFFF;letter-spacing:-.03em;margin-top:10px;">10&nbsp;%</div>'
            f'<div style="font-size:14px;font-weight:700;color:{LT};margin-top:6px;">auf deine erste Bestellung</div>'
            f'<div style="border:1.5px dashed #4A5058;border-radius:10px;padding:9px 8px;margin-top:12px;text-align:center;font-size:17px;font-weight:800;letter-spacing:.12em;color:#6E757E;">HALLO-&bull;&bull;&bull;&bull;&bull;</div>'
            f'<div style="font-size:12px;color:{MU};margin-top:8px;">Kommt direkt nach der Bestätigung.</div></td>'
            f'<td width="40%" style="width:40%;padding:0;">&nbsp;</td></tr></table></td></tr></table>')
    note = (f'<div style="background:{CARD};border:1px solid {BD};border-radius:14px;padding:14px 16px;font-size:13px;line-height:1.55;color:{MU};">'
            f'Du hast dich nicht angemeldet? Dann ignoriere diese E-Mail einfach – ohne Bestätigung wirst du nicht eingetragen und bekommst keine weiteren E-Mails.</div>')
    ctab = lambda t, u, bg, fg, ic='': (f'<a href="{u}" style="display:block;background:{bg};color:{fg};text-align:center;text-decoration:none;font-weight:700;font-size:14px;'
                                       f'line-height:1.2;padding:13px 10px;border-radius:10px;font-family:{F};{"border:1px solid #2C3138;" if bg == "transparent" else ""}">{ic}{t}</a>')
    contact = (f'<div style="font-size:15px;font-weight:800;color:#FFFFFF;">Fragen? Wir sind für dich da.</div>'
               f'<div style="font-size:13px;color:{MU};margin:6px 0 14px;">Antworte einfach auf diese E-Mail oder schreib uns.</div>'
               '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
               f'<td width="50%" style="width:50%;padding-right:5px;">' + ctab('Telegram', 'https://t.me/peptrium', '#229ED9', '#FFFFFF',
                    f'<img src="{E}telegram.png" width="16" height="16" alt="" style="display:inline-block;vertical-align:-3px;width:16px;height:16px;border:0;margin-right:7px;">') + '</td>'
               f'<td width="50%" style="width:50%;padding-left:5px;">' + ctab('E-Mail', 'mailto:info@peptrium.com', 'transparent', '#FFFFFF') + '</td></tr></table>')
    foot = (f'<div style="height:1px;background:#2C3138;margin:0 0 22px;"></div>'
            f'<img src="{U}2026/08/IMG_2644-cropped-300x300.png" width="44" height="44" alt="Peptrium" style="display:block;margin:0 auto;width:44px;height:44px;border:0;">'
            f'<div style="font-size:12px;font-weight:800;letter-spacing:.34em;padding-left:.34em;color:#E6E8EB;margin-top:6px;">PEPTRIUM</div>'
            f'<div style="font-size:11px;color:#7D848D;margin-top:12px;line-height:1.6;">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</div>')
    body = (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{BG};"><tr><td align="center" style="padding:0;">'
            f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{BG};">'
            + row(logo, '30px 20px 8px', align='center')
            + row(head, '22px 22px 6px')
            + row(btn, '18px 22px 6px')
            + row(steps, '22px 14px 8px')
            + row(code, '18px 16px 8px')
            + row(note, '10px 16px 8px')
            + row(contact, '26px 22px 26px', align='center')
            + row(foot, '0 22px 30px', bg='#12151A', align='center')
            + '</table></td></tr></table>')
    return body

if __name__ == '__main__':
    b = build()
    open('doi.body', 'w').write(b)
    open('vorschau.html', 'w').write('<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Bestätigung</title></head>'
                                     f'<body style="margin:0;background:{BG};">' + b.replace('{{LINK}}', '#') + '</body></html>')
    print(len(b))
