# Halloween-Newsletter (v2 2026-10-23): teaser (Mi 28.10.), early (Do 29.10. 18 Uhr), letzte (So 1.11. ~11 Uhr)
# v3 2026-10-24: Bilder ohne Schrift (je Mail eigenes), Mails entschlackt (Haekchen-Liste statt Boxen/Schritte), Vorbestellungen zaehlen mit
# v2: keine Streichpreise (PAngV), 'Heute 23:59', Vorkasse-Hinweis, Impressum-Link, Geraete-Hinweis deutlicher, Betreffzeilen in SUBJ
# python3 gen.py <ea-token> -> <name>.body (Omnisend htmlCode, [[unsubscribe_link]]) + vorschau-<name>.html
import sys
F = "-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif"
U = 'https://peptrium.com/wp-content/uploads/'
E = U + 'sp-email/'
BG = '#050607'; CARD = '#0E1114'; BD = '#22272E'; MU = '#9AA0A8'; LT = '#C9CDD2'; OR = '#FF7A1A'; OR2 = '#FFB547'
EA = sys.argv[1] if len(sys.argv) > 1 else 'TOKEN'
def utm(path, c): return f'https://peptrium.com{path}{"&" if "?" in path else "?"}utm_source=newsletter&utm_medium=email&utm_campaign={c}'
def row(inner, pad, bg=BG, align='left'): return f'<tr><td style="padding:{pad};background:{bg};text-align:{align};font-family:{F};">{inner}</td></tr>'
def btn(t, u): return (f'<a href="{u}" style="display:block;background:{OR};background-image:linear-gradient(90deg,{OR},{OR2});color:#140A02;text-align:center;'
                       f'text-decoration:none;font-weight:800;font-size:16px;line-height:1.2;padding:17px 12px;border-radius:12px;font-family:{F};">{t} &rarr;</a>')
def pill(t): return (f'<span style="display:inline-block;font-size:11px;font-weight:800;letter-spacing:.18em;color:{OR2};border:1px solid #7A3A10;border-radius:99px;padding:7px 12px;">{t}</span>')
def h1(a, b, sub): return (f'<div style="font-size:36px;line-height:1.08;font-weight:800;color:#FFFFFF;letter-spacing:-.02em;margin-top:14px;">{a}<br><span style="color:{OR};">{b}</span></div>'
                           f'<div style="font-size:15px;line-height:1.55;color:#B4BAC2;margin-top:12px;">{sub}</div>')
def logo(): return (f'<img src="{U}2026/08/IMG_2644-cropped-300x300.png" width="54" height="54" alt="Peptrium" style="display:block;margin:0 auto;width:54px;height:54px;border:0;">'
                    f'<div style="font-size:12px;font-weight:800;letter-spacing:.34em;padding-left:.34em;color:#E6E8EB;margin-top:6px;">PEPTRIUM</div>')
def hero(link): return (f'<tr><td style="padding:0;line-height:0;background:{BG};"><a href="{link}" style="display:block;line-height:0;"><img src="{E}nl-hw-hero.jpg" width="600" alt="Halloween bei Peptrium" '
                        f'style="display:block;width:100%;max-width:600px;height:auto;border:0;"></a></td></tr>')
def box(inner, bd='#5A2A0C', bg='#140A02'): return f'<div style="background:{bg};border:1px solid {bd};border-radius:16px;padding:18px 18px;">{inner}</div>'
def steps():
    s = lambda n, t, d: (f'<tr><td width="40" valign="top" style="width:40px;padding:8px 0;"><div style="width:30px;height:30px;line-height:30px;border-radius:50%;background:{OR};color:#140A02;text-align:center;font-weight:800;font-size:14px;">{n}</div></td>'
                         f'<td style="padding:8px 0;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#fff;">{t}</div><div style="font-size:13px;color:{MU};margin-top:2px;line-height:1.45;">{d}</div></td></tr>')
    return ('<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' + s(1, 'Leg 3 Flaschen in den Warenkorb', 'Auch gemischt – z. B. 2× Retatrutide + 1× GHK-Cu.')
            + s(2, 'Die günstigste ist gratis', 'Wird automatisch abgezogen, kein Code nötig.') + s(3, 'Bei 6 Flaschen sind 2 gratis', 'Jede dritte Flasche zählt – so oft du willst.') + '</table>')
def prods(c):
    p = lambda img, n, was, now, u: (f'<tr><td style="padding:6px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;background:{CARD};border:1px solid {BD};border-radius:14px;overflow:hidden;"><tr>'
        f'<td width="92" style="width:92px;padding:0;line-height:0;"><a href="{u}"><img src="{img}" width="92" alt="" style="display:block;width:92px;height:92px;border:0;"></a></td>'
        f'<td style="padding:10px 12px;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#fff;">{n}</div>'
        f'<div style="font-size:13px;color:{MU};margin-top:3px;">3 Flaschen – du zahlst nur 2</div><div style="font-size:16px;font-weight:800;color:{OR2};margin-top:2px;white-space:nowrap;">{now}</div></td>'
        f'<td width="44" style="width:44px;padding-right:12px;"><a href="{u}" style="display:block;width:36px;height:36px;line-height:36px;border-radius:50%;background:#fff;color:#0D0F12;text-align:center;font-weight:800;text-decoration:none;font-size:18px;">&rarr;</a></td>'
        '</tr></table></td></tr>')
    return ('<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
            + p(E + 'nl-hw-p-reta10.jpg', 'Retatrutide 10 mg', '239,70 €', '159,80 €', utm('/produkt/retatrutide/?attribute_volumen=10mg', c))
            + p(E + 'nl-hw-p-reta20.jpg', 'Retatrutide 20 mg', '449,70 €', '299,80 €', utm('/produkt/retatrutide/?attribute_volumen=20mg', c))
            + p(E + 'nl-hw-p-ghk100.jpg', 'GHK-Cu 100 mg', '224,70 €', '149,80 €', utm('/produkt/ghk-cu/?attribute_volumen=100mg', c)) + '</table>')
def foot():
    return (f'<div style="height:1px;background:#2C3138;margin:0 0 22px;"></div>' + logo()
            + f'<div style="font-size:11px;color:#7D848D;margin-top:12px;line-height:1.6;">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</div>'
            + f'<div style="font-size:11px;color:#7D848D;margin-top:12px;line-height:1.6;">Angebot gilt solange der Vorrat reicht, nicht für Pens und Zubehör.<br>Du bekommst diese Mail, weil du den Peptrium-Newsletter abonniert hast. <br><a href="https://peptrium.com/impressum/" style="color:#7D848D;">Impressum</a> · <a href="[[unsubscribe_link]]" style="color:#7D848D;">Abmelden</a></div>')
def wrap(rows):
    return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{BG};"><tr><td align="center" style="padding:0;">'
            f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{BG};">' + rows
            + row(foot(), '8px 22px 30px', bg='#12151A', align='center') + '</table></td></tr></table>')
def vk(): return box(f'<div style="font-size:14px;color:#E6E8EB;line-height:1.5;">💳 <b>Per Vorkasse?</b> <span style="color:{MU};">Kein Stress – es zählt deine Bestellung, nicht der Zahlungseingang. Der Aktionspreis bleibt dir.</span></div>', bd=BD, bg=CARD)
def timeline(active):
    items = [('Do 29.10. · 18 Uhr', 'Early Access – nur für dich'), ('Fr 30.10. · 0 Uhr', 'Start für alle'), ('So 1.11. · 23:59', 'Ende – solange der Vorrat reicht')]
    out = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
    for i, (d, t) in enumerate(items):
        on = i == active
        out += (f'<tr><td width="18" valign="top" style="width:18px;padding:10px 0;"><div style="width:10px;height:10px;border-radius:50%;margin-top:4px;background:{OR if on else "#3A3F45"};"></div></td>'
                f'<td style="padding:8px 0;font-family:{F};{"" if i == len(items)-1 else f"border-bottom:1px solid {BD};"}"><div style="font-size:14px;font-weight:800;color:{OR2 if on else "#fff"};">{d}</div><div style="font-size:13px;color:{MU};margin-top:2px;">{t}</div></td></tr>')
    return out + '</table>'

def ticks(items):
    r = ''.join(f'<tr><td width="30" valign="top" style="width:30px;padding:7px 0;"><div style="width:20px;height:20px;line-height:20px;border-radius:50%;background:#2A1404;border:1px solid #7A3A10;color:{OR2};text-align:center;font-size:12px;font-weight:800;">&#10003;</div></td>'
                f'<td style="padding:7px 0;font-family:{F};font-size:14px;line-height:1.45;color:{LT};">{t}</td></tr>' for t in items)
    return box(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0">{r}</table>', bd=BD, bg=CARD)
def label(t): return f'<div style="font-size:11px;font-weight:800;letter-spacing:.18em;color:{LT};margin-bottom:6px;">{t}</div>'
def heroimg(name, link): return hero(link).replace('nl-hw-hero.jpg', f'nl-hw3-{name}.jpg')
TK_MIX = '<b style="color:#fff;">Auch gemischt</b> – auch Vorbestellungen. Die günstigste Flasche ist gratis.'
TK_CODE = '<b style="color:#fff;">Kein Code nötig</b> – wird automatisch abgezogen.'
TK_VK = '<b style="color:#fff;">Per Vorkasse?</b> Es zählt deine Bestellung, nicht der Zahlungseingang.'
TK_VORRAT = '<b style="color:#fff;">Solange der Vorrat reicht.</b>'

NEW = [('nl-hw-p-cagri.jpg', 'Cagrilintid', '10 mg', 'NEU', '104,90 €', '209,80 €', '/produkt/cagrilintid/'),
       ('nl-hw-p-mt2.jpg', 'MT-2', '10 mg', 'NEU', '29,90 €', '59,80 €', '/produkt/melanotan-2/'),
       ('nl-hw-p-glow.jpg', 'GLOW', '70 mg', 'NEU', '139,90 €', '279,80 €', '/produkt/glow/'),
       ('nl-hw-p-bpc.jpg', 'BPC-157', '10 mg', 'WIEDER DA', '39,90 €', '79,80 €', '/produkt/bpc-157/')]
def newgrid(c, deal):
    def card(t):
        img, n, mg, tag, price, three, path = t
        u = utm(path, c)
        sub = (f'<div style="font-size:12px;color:{MU};margin-top:4px;">3 Flaschen</div><div style="font-size:16px;font-weight:800;color:{OR2};white-space:nowrap;">{three}</div>' if deal
               else f'<div style="font-size:15px;font-weight:800;color:#fff;margin-top:4px;white-space:nowrap;">{price}</div>')
        tc = OR2 if tag == 'NEU' else '#7FD8A6'
        return (f'<td width="50%" valign="top" style="width:50%;padding:5px;"><a href="{u}" style="text-decoration:none;display:block;background:{CARD};border:1px solid {BD};border-radius:14px;overflow:hidden;">'
                f'<img src="{E}{img}" width="270" alt="{n}" style="display:block;width:100%;height:auto;border:0;">'
                f'<div style="padding:10px 12px 12px;font-family:{F};"><div style="font-size:10px;font-weight:800;letter-spacing:.16em;color:{tc};">{tag}</div>'
                f'<div style="font-size:16px;font-weight:800;color:#fff;margin-top:3px;">{n} <span style="font-size:12px;font-weight:600;color:{MU};">{mg}</span></div>{sub}</div></a></td>')
    return ('<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>' + card(NEW[0]) + card(NEW[1]) + '</tr><tr>' + card(NEW[2]) + card(NEW[3]) + '</tr></table>')

def teaser():
    c = 'halloween-teaser'
    return wrap(row(logo(), '26px 20px 6px', align='center') + heroimg('teaser', utm('/alle-produkte/', c))
        + row(h1('Morgen um 18 Uhr', 'bist du früher dran.', 'Unser Halloween-Special: <b style="color:#fff;">jede 3. Flasche gratis</b>. Für dich startet es sechs Stunden vor allen anderen – den Zugang schicken wir dir morgen per Mail.'), '22px 22px 6px')
        + row(box(label('DER ABLAUF') + timeline(0)), '18px 16px 4px')
        + row(ticks([TK_MIX, '<b style="color:#fff;">Bei 6 Flaschen sind 2 gratis</b> – so oft du willst.', TK_VORRAT]), '12px 16px 4px')
        + row(label('NEU IM SHOP') + f'<div style="font-size:14px;color:{LT};line-height:1.5;margin-bottom:8px;">Drei neue Produkte ab Donnerstag – und BPC-157 ist schon wieder da. <b style="color:#fff;">Als Insider bestellst du die Neuen als Erste.</b> Alle zählen bei „jede 3. gratis“ mit.</div>' + newgrid(c, False), '20px 16px 4px')
        + row(btn('Schon mal stöbern', utm('/alle-produkte/', c)), '18px 22px 26px'))

def early():
    c = 'halloween-early'
    link = f'https://peptrium.com/?sp_hw=frueh&k={EA}&utm_source=newsletter&utm_medium=email&utm_campaign={c}'
    return wrap(row(logo(), '26px 20px 6px', align='center') + heroimg('early', link)
        + row(h1('Du bist drin.', 'Jede 3. Flasche gratis.', 'Für alle anderen startet es erst um Mitternacht.'), '22px 22px 6px')
        + row(btn('Early Access öffnen', link), '18px 22px 4px')
        + row(f'<div style="font-size:13px;color:{OR2};text-align:center;line-height:1.5;">📱 Öffne den Link auf dem Gerät, mit dem du bestellst.</div>', '8px 22px 6px', align='center')
        + row(label('DEIN PREIS FÜR 3 FLASCHEN') + prods(c), '20px 16px 4px')
        + row(label('JETZT NEU IM SHOP') + f'<div style="font-size:14px;color:{LT};line-height:1.5;margin-bottom:8px;">Ab sofort für dich bestellbar – für alle anderen erst ab Mitternacht. Auch gemischt: <b style="color:#fff;">2× GLOW + 1× MT-2 → MT-2 gratis.</b></div>' + newgrid(c, True), '20px 16px 4px')
        + row(ticks([TK_MIX, TK_CODE, TK_VK, TK_VORRAT]), '12px 16px 4px')
        + row(btn('Jetzt Flaschen wählen', link), '18px 22px 26px'))

def letzte():
    c = 'halloween-letzte'
    return wrap(row(logo(), '26px 20px 6px', align='center') + heroimg('letzte', utm('/alle-produkte/', c))
        + row(h1('Heute um 23:59', 'ist Schluss.', 'Danach kostet jede Flasche wieder den normalen Preis.'), '22px 22px 6px')
        + row(btn('Jetzt Flaschen wählen', utm('/alle-produkte/', c)), '18px 22px 6px')
        + row(label('DIE BELIEBTESTEN') + prods(c), '20px 16px 4px')
        + row(box(f'<div style="font-size:14px;color:#E6E8EB;line-height:1.5;">✨ <b>Neu dabei:</b> <span style="color:{MU};">Cagrilintid, MT-2 und GLOW – und BPC-157 ist wieder da. Alle zählen mit.</span> <a href="{utm("/alle-produkte/", c)}" style="color:{OR2};font-weight:700;text-decoration:none;">Ansehen →</a></div>', bd=BD, bg=CARD), '12px 16px 4px')
        + row(ticks([TK_MIX, TK_CODE, TK_VK]), '12px 16px 26px'))

SUBJ = {
    'teaser': ('Morgen 18 Uhr: du bist früher dran 🎃', 'Jede 3. Flasche gratis – plus 3 neue Produkte.'),
    'early': ('Du bist drin 🎃', 'Jede 3. Flasche gratis – und die Neuheiten sind schon da.'),
    'letzte': ('Heute um Mitternacht ist Schluss', 'Danach wieder volle Preise.'),
}

if __name__ == '__main__':
    for name, fn in [('teaser', teaser), ('early', early), ('letzte', letzte)]:
        b = fn()
        open(name + '.body', 'w').write(b)
        v = b.replace(E + 'nl-hw', 'nl-hw').replace('[[unsubscribe_link]]', '#')
        open('vorschau-' + name + '.html', 'w').write('<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:#050607;">' + v + '</body></html>')
        print(name, len(b))
