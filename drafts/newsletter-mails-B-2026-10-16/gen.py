import json,sys
U='https://peptrium.com/wp-content/uploads/'
LOGO=U+'2026/08/IMG_2644-cropped-300x300.png'
F="-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif"
MONO="'SFMono-Regular',Consolas,Menlo,monospace"
BG='#0B0D10'; IN='#12151A'; CARD='#181C22'; BD='#2C3138'; MUT='#A3A9B1'; DIM='#7D848D'; ACC='#D9DDE2'
CP=lambda n:'[[contact.custom_properties.'+n+']]'
def utm(p,c): return 'https://peptrium.com'+p+'?utm_source=newsletter&utm_medium=email&utm_campaign='+c
P={'reta':('Retatrutide','ab 74,90 €','nl-p-reta.jpg','/produkt/retatrutide/'),
 'ghk':('GHK-Cu','ab 39,90 €','nl-p-ghk.jpg','/produkt/ghk-cu/'),
 'mots':('Mots-C','ab 34,90 €','nl-p-mots.jpg','/produkt/mots-c/'),
 'pen':('Peptrium-Pen','ab 169,90 €','nl-p-pen.jpg','/alle-peptrium-pens/')}
TRUST=[('🧪','HPLC / LC-MS','jede Charge geprüft'),('💎','≥ 99 % Reinheit','unser Mindeststandard'),('📦','Diskret','neutral verpackt'),('🚚','2 Werktage','schneller Versand')]
def row(inner,pad='30px 20px',bg=IN): return f'<tr><td style="padding:{pad};text-align:center;background:{bg};font-family:{F};">{inner}</td></tr>'
def logo(s=44): return f'<img src="{LOGO}" width="{s}" height="{s}" alt="Peptrium" style="display:block;margin:0 auto;width:{s}px;height:{s}px;border:0;"><div style="font-size:13px;font-weight:800;letter-spacing:.34em;padding-left:.34em;color:#E6E8EB;margin-top:6px;">PEPTRIUM</div>'
def btn(t,u,w='100%'): return f'<a href="{u}" style="display:block;width:{w};margin:0 auto;box-sizing:border-box;background:#FFFFFF;color:#0D0F12;text-align:center;text-decoration:none;font-weight:800;font-size:15px;line-height:1.2;padding:15px 12px;border-radius:12px;font-family:{F};">{t}</a>'
def H(a,b,sub=''): return f'<div style="font-size:27px;line-height:1.15;font-weight:800;color:#FFFFFF;">{a}<br><span style="color:#8E959E;">{b}</span></div>'+(f'<div style="font-size:14px;line-height:1.6;color:{MUT};margin:12px auto 0;max-width:430px;">{sub}</div>' if sub else '')
def top(title,sub,cta,url,hero):
    return row(logo()+f'<div style="font-size:32px;line-height:1.1;font-weight:800;color:#FFFFFF;margin:20px 0 10px;letter-spacing:-.01em;">{title}</div><div style="font-size:15px;line-height:1.5;color:{MUT};margin-bottom:22px;">{sub}</div>'+btn(cta,url,'86%'),'30px 20px 24px')+\
      f'<tr><td style="padding:0;line-height:0;background:{IN};"><img src="{U}sp-email/{hero}" width="600" alt="" style="display:block;width:100%;max-width:600px;height:auto;border:0;"></td></tr>'
def code(big,label,c,valid,cta,url):
    return row(f'<div style="border:1px solid {BD};background:{CARD};border-radius:18px;padding:22px 14px;"><div style="font-size:52px;font-weight:800;color:#FFFFFF;line-height:1;">{big}</div><div style="font-size:11px;font-weight:700;letter-spacing:.18em;color:{MUT};margin:10px 0 14px;text-transform:uppercase;">{label}</div><div style="border:1.5px dashed #59606A;border-radius:12px;padding:12px 8px;font-size:22px;font-weight:800;font-family:{MONO};color:{ACC};letter-spacing:.03em;word-break:break-all;">{c}</div><div style="font-size:12px;color:{DIM};margin-top:10px;">{valid}</div></div><div style="height:16px;"></div>'+btn(cta,url),'4px 20px 28px')
def trust():
    r=''.join(f'<tr><td width="48" valign="top" style="width:48px;padding:10px 0;"><div style="width:38px;height:38px;line-height:38px;border-radius:50%;background:#1F242B;border:1px solid {BD};text-align:center;font-size:17px;">{i}</div></td><td style="padding:10px 6px;text-align:left;font-family:{F};"><div style="font-size:14px;font-weight:800;color:#FFFFFF;">{a}</div><div style="font-size:12px;color:{MUT};">{b}</div></td></tr>' for i,a,b in TRUST)
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">{r}</table>'
def prods(keys,c):
    cell=lambda k:f'<td width="50%" valign="top" style="width:50%;padding:5px;"><a href="{utm(P[k][3],c)}" style="display:block;text-decoration:none;background:{CARD};border:1px solid {BD};border-radius:14px;overflow:hidden;text-align:center;"><img src="{U}sp-email/{P[k][2]}" width="150" alt="{P[k][0]}" style="display:block;width:100%;height:auto;border:0;"><div style="padding:10px 8px 12px;font-family:{F};"><div style="font-size:14px;font-weight:800;color:#FFFFFF;">{P[k][0]}</div><div style="font-size:12px;color:{MUT};margin:2px 0 10px;">{P[k][1]}</div><div style="border:1.5px solid #FFFFFF;border-radius:8px;padding:8px 6px;font-size:12px;font-weight:800;color:#FFFFFF;">Ansehen</div></div></a></td>'
    rows=''.join('<tr>'+''.join(cell(k) for k in keys[i:i+2])+'</tr>' for i in range(0,len(keys),2))
    return f'<div style="height:16px;"></div><table role="presentation" width="100%" cellpadding="0" cellspacing="0">{rows}</table>'
def last():
    r=''
    for i in (1,2):
        cp=lambda n:CP(f'nachkauf_{n}_{i}')
        r+=f'<tr><td width="70" style="width:70px;padding:9px 0;border-bottom:1px solid {BD};"><img src="{cp("bild")}" width="58" height="58" alt="" style="display:block;width:58px;height:58px;border-radius:12px;border:0;background:#0D0F12;"></td><td style="padding:9px 4px;border-bottom:1px solid {BD};text-align:left;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#FFFFFF;">{cp("name")}</div><div style="font-size:12px;color:{MUT};">{cp("info")}</div></td></tr>'
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0 18px;">{r}</table>'+btn('Gleiche Bestellung nochmal',CP('nachkauf_link'))
def abo(c): return row(f'<div style="border:1px solid #5A3322;background:{CARD};border-radius:18px;padding:22px 16px;"><div style="font-size:11px;font-weight:700;letter-spacing:.18em;color:#FF8A5C;">PEPTRIUM ABO</div><div style="font-size:22px;font-weight:800;color:#FFFFFF;margin:8px 0 6px;">Nie wieder nachbestellen.</div><div style="font-size:14px;line-height:1.5;color:{MUT};margin-bottom:16px;">Ab der 2. Lieferung −15 % · jederzeit pausierbar</div><a href="{utm("/abo-modell/",c)}" style="display:inline-block;background:#FF6B35;color:#FFFFFF;text-decoration:none;font-weight:800;font-size:14px;padding:12px 20px;border-radius:12px;">Abo ansehen</a></div>','4px 20px 28px')
def foot():
    return row(f'<div style="height:1px;background:{BD};margin:0 0 24px;"></div><div style="font-size:15px;font-weight:700;color:#FFFFFF;margin-bottom:12px;">Fragen? Wir sind für dich da.</div><table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td style="padding:0 5px;"><a href="mailto:info@peptrium.com" style="display:inline-block;background:#FFFFFF;color:#0D0F12;text-decoration:none;font-weight:700;font-size:13px;padding:11px 16px;border-radius:12px;">✉️ E-Mail</a></td><td style="padding:0 5px;"><a href="https://t.me/peptrium" style="display:inline-block;background:#229ED9;color:#FFFFFF;text-decoration:none;font-weight:700;font-size:13px;padding:11px 16px;border-radius:12px;">Telegram</a></td></tr></table><div style="height:28px;"></div>'+logo(54)+f'<div style="font-size:11px;color:{DIM};margin-top:14px;line-height:1.6;">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</div><div style="font-size:11px;color:{DIM};margin-top:14px;line-height:1.6;">Du bekommst diese Mail, weil du den Peptrium-Newsletter abonniert hast.<br><a href="[[unsubscribe_link]]" style="color:{DIM};">Abmelden</a></div>','0 22px 32px')
def body(rows): return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{BG};"><tr><td align="center" style="padding:0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{IN};">{rows}</table></td></tr></table>'
M={}
c='willkommen'
M[c]=body(top('Willkommen.','Schön, dass du dabei bist. Dein Geschenk wartet unten.','Zum Shop',utm('/alle-produkte/',c),'nl-hero-vials.jpg')
 +row(H('Dein Willkommens-','geschenk.'),'30px 20px 18px')+code('10 %','auf deine erste Bestellung',CP('hallo_code'),'gültig bis '+CP('hallo_gueltig_bis')+' · einmal einlösbar','Code jetzt einlösen',utm('/alle-produkte/',c))
 +row(H('Was jede Bestellung','auszeichnet.')+trust())+row(H('Beliebt bei','Insidern.')+prods(['reta','ghk','pen','mots'],c),'26px 15px')+foot())
c='code-erinnerung'
M['erinnerung']=body(top('Dein Code läuft bald ab.','Nur noch '+CP('erinnerung_tage')+' gültig – danach verfällt er.','Code jetzt nutzen',utm('/alle-produkte/',c),'nl-hero-vials.jpg')
 +row(H('Noch nicht','eingelöst.'),'30px 20px 18px')+code(CP('erinnerung_prozent'),'Rabatt auf deine Bestellung',CP('erinnerung_code'),'nur noch '+CP('erinnerung_tage')+' gültig','Code jetzt einlösen',utm('/alle-produkte/',c))
 +row(H('Am meisten','gekauft.')+prods(['reta','ghk'],c),'26px 15px')+foot())
c='nachkauf'
M['nachkauf']=body(top('Zeit für Nachschub?','Deine letzte Bestellung ist rund 4 Wochen her.','Jetzt nachbestellen',CP('nachkauf_link'),'nl-hero-pen.jpg')
 +row(H('Deine letzte','Bestellung.','Mit einem Klick wieder im Warenkorb.')+last())+abo(c)
 +row(H('Unser Standard.','Wie immer.')+trust(),'10px 20px 30px')+foot())
c='nachkauf-2'
M['nachkauf2']=body(top('Noch alles da?','Falls es knapp wird: Deine Bestellung ist mit einem Klick wieder im Warenkorb.','Jetzt nachbestellen',CP('nachkauf_link'),'nl-hero-pen.jpg')
 +row(H('Deine letzte','Bestellung.')+last())+row(H('Das könnte auch','passen.')+prods(['ghk','mots'],c),'26px 15px')+foot())
c='rueckgewinnung'
M['rueckgewinnung']=body(top('Wir vermissen dich.','Lange nicht gesehen – hier ist ein kleines Dankeschön für dein Comeback.','Zum Shop',utm('/alle-produkte/',c),'nl-hero-vials.jpg')
 +row(H('Dein Comeback-','Code.'),'30px 20px 18px')+code(CP('comeback_prozent'),'auf deine nächste Bestellung',CP('comeback_code'),'gültig bis '+CP('comeback_bis'),'Code jetzt einlösen',utm('/alle-produkte/',c))
 +row(H('Neu und','beliebt.')+prods(['reta','pen','ghk','mots'],c),'26px 15px')+row(H('Unser Standard.','Wie immer.')+trust(),'10px 20px 30px')+foot())
for k,v in M.items():
    open(k+'.body','w').write(v)
    open(k+'.html','w').write(f'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:{BG};">{v}</body></html>')
print(list(M))
