# Black Week 2026 – Omnisend htmlCode bodies (mailtauglich: Tabellen, Inline-Styles, Bilder statt CSS-Effekte)
F="-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif"
U='https://peptrium.com/wp-content/uploads/'; E=U+'sp-email/'
BG='#000000'; MU='#9AA0A8'; LT='#C9CDD2'
def eur(x): return f"{x:,.2f} €".replace(',','X').replace('.',',').replace('X','.')
def utm(path,c): return f'https://peptrium.com{path}{"&" if "?" in path else "?"}utm_source=newsletter&utm_medium=email&utm_campaign={c}'
def row(inner,pad,bg=BG,align='left'): return f'<tr><td style="padding:{pad};background:{bg};text-align:{align};font-family:{F};">{inner}</td></tr>'
def btnw(t,u): return f'<a href="{u}" style="display:block;background:#FFFFFF;color:#000000;text-align:center;text-decoration:none;font-family:{F};font-size:16px;font-weight:800;padding:17px 18px;border-radius:999px;">{t} &rarr;</a>'
def logo(): return f'<img src="{U}2026/08/IMG_2644-cropped-300x300.png" width="44" height="44" alt="Peptrium" style="display:block;margin:0 auto;width:44px;height:44px;border:0;">'
def foot(extra=''):
    return (f'<div style="height:1px;background:#1E2024;margin:0 0 22px;"></div>'+logo()
      +f'<div style="font-size:11px;color:#6E747B;margin-top:12px;line-height:1.6;">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</div>'
      +f'<div style="font-size:11px;color:#6E747B;margin-top:12px;line-height:1.6;">{extra}Du bekommst diese Mail, weil du den Peptrium-Newsletter abonniert hast.<br><a href="https://peptrium.com/impressum/" style="color:#6E747B;">Impressum</a> · <a href="[[unsubscribe_link]]" style="color:#6E747B;">Abmelden</a></div>')
def wrap(rows,extra=''):
    return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{BG};"><tr><td align="center" style="padding:0;">'
      f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{BG};">'+rows
      +row(foot(extra),'14px 22px 30px',align='center')+'</table></td></tr></table>')
def hero(img,link,alt): return f'<tr><td style="padding:0;line-height:0;background:{BG};"><a href="{link}" style="display:block;line-height:0;"><img src="{E}{img}" width="600" alt="{alt}" style="display:block;width:100%;max-width:600px;height:auto;border:0;"></a></td></tr>'
def plist(rows,f):
    t=''.join(f'<tr><td style="padding:13px 0;border-bottom:1px solid #1C1E22;font-family:{F};font-size:14px;color:#FFFFFF;">{n}<br><span style="font-size:12.5px;color:{MU};">{v}</span></td><td align="right" style="padding:13px 0;border-bottom:1px solid #1C1E22;font-family:{F};white-space:nowrap;"><span style="font-size:12px;color:#6E747B;text-decoration:line-through;">{eur(p)}</span>&nbsp;&nbsp;<span style="font-size:16px;font-weight:800;color:#FFFFFF;">{eur(p*f)}</span></td></tr>' for n,v,p in rows)
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #1C1E22;">{t}</table>'
def p(t,c=LT,sz=15,al='center'): return f'<div style="font-size:{sz}px;line-height:1.6;color:{c};text-align:{al};">{t}</div>'
PR=[('Retatrutide','10 mg',79.90),('Retatrutide','20 mg',149.90),('GHK-Cu','50 mg',39.90),('GLOW','70 mg',139.90),('Peptrium-Pen Retatrutide','20 mg',264.90)]
def start():
    L=utm('/alle-produkte/','blackweek-start')
    return wrap(hero('nl-bw1-hero.jpg',L,'Black Week −30 % auf alles')
      +row(p('Ab sofort ist <b style="color:#FFFFFF;">jedes Produkt 30 % günstiger</b> – bis Donnerstag, 26. November. Ohne Code, direkt im Preis. Die Geschenke ab 200 € gibt es weiterhin dazu.'),'26px 26px 6px')
      +row(plist(PR,.7),'16px 22px 6px')
      +row(p('Und Freitag? Wird es noch besser. Du erfährst es zuerst.',MU,13),'16px 26px 4px')
      +row(btnw('Zum Shop',L),'18px 22px 28px'))
SUBJ={'start':('Black Week: −30 % auf alles 🖤','Ab jetzt: Peptide, Pens und Zubehör – automatisch reduziert.')}
if __name__=='__main__':
    open('start.body','w').write(start())
    open('vorschau-start.html','w').write('<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width"><body style="margin:0;background:#000">'+start().replace(E+'nl-bw1-hero.jpg','../hero/nl-bw1-hero.jpg')+'</body>')
    print('ok')
