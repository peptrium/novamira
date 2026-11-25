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

EA='0iqjngzz21sm'
TH={'dark':dict(bg='#000000',fg='#FFFFFF',mu='#9AA0A8',lt='#C9CDD2',line='#1C1E22',btn='w',ft='#6E747B'),
    'light':dict(bg='#F3F3F1',fg='#0B0C0E',mu='#6B7076',lt='#3A3F46',line='#DADBD8',btn='b',ft='#8A9098')}
def T(base,bg): d=dict(TH[base]);d['bg']=bg;return d
def btn(t,u,th):
    w=th['btn']=='w'
    return f'<a href="{u}" style="display:block;background:{"#FFFFFF" if w else "#07080A"};color:{"#000000" if w else "#FFFFFF"};text-align:center;text-decoration:none;font-family:{F};font-size:16px;font-weight:800;padding:17px 18px;border-radius:999px;">{t} &rarr;</a>'
def R(inner,pad,th,al='left'): return f'<tr><td style="padding:{pad};background:{th["bg"]};text-align:{al};font-family:{F};">{inner}</td></tr>'
def P(t,th,sz=15,al='center',c=None): return f'<div style="font-size:{sz}px;line-height:1.6;color:{c or th["lt"]};text-align:{al};">{t}</div>'
def rows(items,f,th):
    t=''.join(f'<tr><td style="padding:13px 0;border-bottom:1px solid {th["line"]};font-family:{F};font-size:14px;color:{th["fg"]};">{n}<br><span style="font-size:12.5px;color:{th["mu"]};">{v}</span></td><td align="right" style="padding:13px 0;border-bottom:1px solid {th["line"]};font-family:{F};white-space:nowrap;"><span style="font-size:12px;color:{th["mu"]};text-decoration:line-through;">{eur(p)}</span>&nbsp;&nbsp;<span style="font-size:16px;font-weight:800;color:{th["fg"]};">{eur(p*f)}</span></td></tr>' for n,v,p in items)
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid {th["line"]};">{t}</table>'
def checks(items,th): return ''.join(f'<div style="font-size:14px;line-height:1.5;color:{th["lt"]};padding:5px 0;text-align:left;">&#10003;&nbsp; {i}</div>' for i in items)
def grid(items,f,th):
    cells=[f'<td width="50%" valign="top" style="padding:12px;background:#F7F7F8;border:1px solid #D2D4D7;font-family:{F};"><div style="font-size:13px;font-weight:600;color:#07080A;">{n}</div><div style="font-size:11.5px;color:#6B7076;">{v}</div><div style="margin-top:8px;"><span style="font-size:11.5px;color:#9AA0A8;text-decoration:line-through;">{eur(p)}</span>&nbsp;<span style="font-size:17px;font-weight:800;color:#07080A;">{eur(p*f)}</span></div></td>' for n,v,p in items]
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;"><tr>{cells[0]}{cells[1]}</tr><tr>{cells[2]}{cells[3]}</tr></table>'
def foot2(th,extra=''):
    return (f'<div style="height:1px;background:{th["line"]};margin:0 0 22px;"></div>'+logo()
      +f'<div style="font-size:11px;color:{th["ft"]};margin-top:12px;line-height:1.6;">{extra}Alle Produkte ausschließlich für Laborforschungszwecke.<br>Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</div>'
      +f'<div style="font-size:11px;color:{th["ft"]};margin-top:12px;line-height:1.6;">Du bekommst diese Mail, weil du den Peptrium-Newsletter abonniert hast.<br><a href="https://peptrium.com/impressum/" style="color:{th["ft"]};">Impressum</a> · <a href="[[unsubscribe_link]]" style="color:{th["ft"]};">Abmelden</a></div>')
def wrap2(rows_,th,extra=''):
    return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{th["bg"]};"><tr><td align="center" style="padding:0;">'
      f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{th["bg"]};">'+rows_
      +R(foot2(th,extra),'14px 22px 30px',th,'center')+'</table></td></tr></table>')
def img(name,link,alt,th): return f'<tr><td style="padding:0;line-height:0;background:{th["bg"]};"><a href="{link}" style="display:block;line-height:0;"><img src="{E}{name}" width="600" alt="{alt}" style="display:block;width:100%;max-width:600px;height:auto;border:0;"></a></td></tr>'
PR65=[('Retatrutide','10 mg',79.90),('Retatrutide','20 mg',149.90),('GHK-Cu','50 mg',39.90),('GLOW','70 mg',139.90),('Peptrium-Pen Retatrutide','20 mg',264.90)]
def codeblock(code,th,hint='Kopiere den Code und füge ihn im Warenkorb bei „Rabattcode“ ein.'):
    dk=th['btn']=='w'
    bg='#0E1013' if dk else '#FFFFFF'; bd='rgba(255,255,255,.18)' if dk else '#D2D4D7'; fg='#FFFFFF' if dk else '#07080A'; dash='rgba(255,255,255,.55)' if dk else '#9AA0A8'
    return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;"><tr><td align="center" style="background:{bg};border:1px solid {bd};border-radius:16px;padding:18px 16px;font-family:{F};">'
      f'<div style="font-size:10px;font-weight:700;letter-spacing:.26em;color:{th["mu"]};">DEIN CODE</div>'
      f'<div style="display:inline-block;margin-top:10px;border:2px dashed {dash};border-radius:12px;padding:10px 22px;font-size:32px;font-weight:800;letter-spacing:.08em;color:{fg};">{code}</div>'
      f'<div style="font-size:12.5px;line-height:1.5;color:{th["mu"]};margin-top:10px;">{hint}</div></td></tr></table>')
def m_pen():
    th=T('light','#F3F3F1');L=utm('/alle-peptrium-pens/','blackweek-pen')
    return wrap2(img('nl-bw-2-pen.jpg',L,'Der Pen −30 %',th)
      +R(P('Kein Bac Water, kein Anmischen: <b style="color:#0B0C0E;">der Peptrium-Pen kommt fertig gemischt</b>. Mit Code BLACK30 jetzt 30 % günstiger – nur bis Donnerstag.',th,15,'left'),'4px 22px 6px',th)
      +R(codeblock('BLACK30',th),'14px 22px 0',th)
      +R(rows([('Pen Retatrutide','20 mg',264.90),('Pen Retatrutide','40 mg',339.90),('Pen GHK-Cu','50 mg',169.90),('Pen MOTS-c','20 mg',189.90)],.7,th),'14px 22px 6px',th)
      +R(checks(['Pen Nadeln gleich mitbestellen – auch −30 %','Ab 200 € gibt es das Injektionskit gratis dazu','Versand in 2 Werktagen, diskret verpackt'],th),'12px 22px 4px',th)
      +R(btn('Pens ansehen',L,th),'18px 22px 28px',th),th)
def m_fav():
    th=T('dark','#16181C');L=utm('/alle-produkte/','blackweek-favoriten')
    return wrap2(img('nl-bw-3-fav-v3.jpg',L,'Die Favoriten −30 %',th)+R(codeblock('BLACK30',th),'16px 20px 0',th)+R(btn('Sets ansehen',L,th),'14px 20px 28px',th),th)
def m_teaser():
    th=T('dark','#000000');L=utm('/alle-produkte/','blackweek-teaser')
    return wrap2(img('nl-bw-4-ablauf.jpg',L,'BLACK30 läuft morgen ab',th)
      +R(P('Morgen, Donnerstag um 23:59, endet dein Code. <b style="color:#FFFFFF;">Bis dahin: −30 % auf alles</b> – Peptide, Pens und Zubehör.',th,15),'8px 26px 4px',th)
      +R(codeblock('BLACK30',th),'14px 22px 0',th)
      +R(rows(PR65,.7,th),'14px 22px 6px',th)
      +R(btn('Jetzt noch −30 % sichern',L,th),'18px 22px 28px',th),th)
def m_insider():
    th=T('dark','#050506');L=utm(f'/?sp_bw=frueh&k={EA}','blackweek-insider')
    return wrap2(img('nl-bw-5-insider.jpg',L,'Insider −35 %',th)
      +R(codeblock('BLACK35',th,'Dein Insider-Code – kopieren und im Warenkorb bei „Rabattcode“ einfügen.'),'14px 22px 0',th)
      +R(rows(PR65,.65,th),'14px 22px 6px',th)
      +R(btn('Jetzt −35 % sichern',L,th),'20px 22px 6px',th)
      +R(P('Bestseller sind am Freitagmorgen erfahrungsgemäß zuerst weg.',th,12.5,c=th['mu']),'12px 26px 26px',th),th)
def m_bf():
    th=T('light','#E9EAEC');L=utm('/alle-produkte/','blackweek-blackfriday')
    return wrap2(img('nl-bw-6-bf-v3.jpg',L,'Black Friday −35 %',th)
      +R(codeblock('BLACK35',th),'16px 20px 0',th)
      +R(grid([('Retatrutide','10 mg',79.90),('GHK-Cu','50 mg',39.90),('GLOW','70 mg',139.90),('Pen Retatrutide','20 mg',264.90)],.65,th),'16px 20px 4px',th)
      +R(checks(['Versand in 2 Werktagen, diskret verpackt','Geschenke ab 200 € bleiben','Bei Vorkasse zählt das Bestelldatum'],th),'12px 20px 4px',th)
      +R(btn('Zum Black Friday',L,th),'18px 20px 28px',th),th)
def m_so():
    th=T('dark','#0B0C0E');L=utm('/alle-produkte/','blackweek-sonntag')
    return wrap2(img('nl-bw-7-so-v2.jpg',L,'Noch bis Montag −35 %',th)
      +R(codeblock('BLACK35',th),'10px 22px 0',th)
      +R(rows(PR65,.65,th),'10px 22px 6px',th)
      +R(btn('Jetzt bestellen',L,th),'20px 22px 6px',th)
      +R(P('Fragen vor der Bestellung? Schreib uns auf Telegram, wir antworten schnell.',th,12.5,c=th['mu']),'12px 26px 26px',th),th)
def m_last():
    th=T('light','#FFFFFF');L=utm('/alle-produkte/','blackweek-letzte')
    return wrap2(img('nl-bw-8-last-v2.jpg',L,'Heute ist Schluss',th)
      +R(codeblock('BLACK35',th),'16px 22px 0',th)
      +R(btn('Letzte Chance nutzen',L,th),'18px 22px 10px',th)
      +R(P('Es zählt deine Bestellung, nicht der Zahlungseingang.',th,12.5,c=th['mu']),'4px 26px 26px',th),th)
MAILS={'pen':(m_pen,'Halbzeit: der Pen, jetzt −30 %','Fertig gemischt, kein Anmischen – noch bis Donnerstag.','2026-11-19T17:00:00Z','nb','Black Week 2 – Pen (Do 19.11. 18 Uhr)'),
 'favoriten':(m_fav,'Die Favoriten, jetzt 30 % günstiger','Starter-Set, Pen-Set und GLOW – fertig zusammengestellt.','2026-11-21T10:00:00Z','nb','Black Week 3 – Favoriten (Sa 21.11. 11 Uhr)'),
 'teaser':(m_teaser,'Morgen läuft BLACK30 ab','Nur noch bis Donnerstag, 23:59: −30 % auf alles.','2026-11-25T17:00:00Z','all','Black Week 4 – BLACK30 läuft ab (Mi 25.11. 18 Uhr)'),
 'insider':(m_insider,'Für dich schon heute: −35 % 🖤','Black Friday startet für alle erst morgen.','2026-11-26T17:00:00Z','all','Black Week 5 – Insider −35 % (Do 26.11. 18 Uhr)'),
 'blackfriday':(m_bf,'Black Friday: −35 % auf alles','Der größte Rabatt des Jahres. Bis Montag.','2026-11-27T07:00:00Z','all','Black Week 6 – Black Friday (Fr 27.11. 8 Uhr)'),
 'sonntag':(m_so,'Noch bis Montag: −35 %','Falls du noch überlegst – hier das Wichtigste auf einen Blick.','2026-11-29T10:00:00Z','nb','Black Week 7 – Erinnerung (So 29.11. 11 Uhr)'),
 'letzte':(m_last,'Heute 23:59 ist Schluss','Letzter Tag: −35 % auf alles.','2026-11-30T09:00:00Z','all','Black Week 8 – Letzte Chance (Mo 30.11. 10 Uhr)')}

SUBJ={'start':('Black Week: −30 % auf alles 🖤','Ab jetzt: Peptide, Pens und Zubehör – automatisch reduziert.')}
if __name__=='__main__':
    import json
    meta={}
    for k,(fn,s,ph,at,aud,nm) in MAILS.items():
        b=fn();open(k+'.body','w').write(b);meta[k]=dict(subject=s,preheader=ph,at=at,aud=aud,name=nm)
        open('vorschau-'+k+'.html','w').write('<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width"><body style="margin:0">'+b.replace(E,'')+'</body>')
    json.dump(meta,open('meta.json','w'),ensure_ascii=False,indent=1)
    open('start.body','w').write(start())
    open('vorschau-start.html','w').write('<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width"><body style="margin:0;background:#000">'+start().replace(E+'nl-bw1-hero.jpg','../hero/nl-bw1-hero.jpg')+'</body>')
    print('ok')
