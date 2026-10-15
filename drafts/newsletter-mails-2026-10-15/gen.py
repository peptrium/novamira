import json,sys
U='https://peptrium.com/wp-content/uploads/'
INK='#0D0F12';MUT='#5B6169';LINE='#E4E6E9';SOFT='#F5F6F7'
F="-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif"
MONO="'SFMono-Regular',Consolas,Menlo,monospace"
GRAD='background-color:#0D0F12;background-image:linear-gradient(135deg,#0D0F12 0%,#23272C 55%,#3A3F45 100%);'
def utm(path,c): return 'https://peptrium.com'+path+'?utm_source=newsletter&utm_medium=email&utm_campaign='+c
P={ # sample products
 'reta':('Retatrutide','ab 74,90 €',U+'sp-email/nl-p-reta.jpg','/produkt/retatrutide/'),
 'ghk':('GHK-Cu','ab 39,90 €',U+'sp-email/nl-p-ghk.jpg','/produkt/ghk-cu/'),
 'mots':('Mots-C','ab 34,90 €',U+'sp-email/nl-p-mots.jpg','/produkt/mots-c/'),
 'pen':('Peptrium-Pen','ab 169,90 €',U+'sp-email/nl-p-pen.jpg','/alle-peptrium-pens/'),
 'bac':('Bac Water','6,90 €',U+'sp-email/nl-p-bac.jpg','/produkt/bac-water/'),
}
def btn(t,u,dark=True,small=False):
    s=(GRAD+'color:#FFFFFF;border:1.5px solid '+INK+';') if dark else ('background:#FFFFFF;color:'+INK+';border:1.5px solid '+INK+';')
    pad='11px 10px' if small else '16px 12px'; fs='14px' if small else '16px'
    return f'<a href="{u}" style="{s}display:block;text-align:center;text-decoration:none;font-weight:700;font-size:{fs};line-height:1.2;padding:{pad};border-radius:12px;font-family:{F};">{t}</a>'
def head(img,eyebrow,title,sub):
    return (f'<tr><td style="{GRAD}padding:16px 18px;text-align:center;"><img src="{U}2026/08/IMG_2644-cropped-300x300.png" width="34" height="34" alt="" style="display:inline-block;width:34px;height:34px;border:0;vertical-align:middle;"><span style="display:inline-block;vertical-align:middle;margin-left:10px;font-size:15px;font-weight:800;letter-spacing:.3em;color:#E6E8EB;font-family:{F};">PEPTRIUM</span></td></tr>'
     f'<tr><td style="background:#0D0F12;padding:0;line-height:0;"><img src="{U}sp-email/{img}" width="600" alt="Peptrium" style="display:block;width:100%;max-width:600px;height:auto;border:0;"></td></tr>'
     f'<tr><td style="background:#0D0F12;padding:6px 20px 28px;text-align:center;font-family:{F};"><div style="font-size:11px;font-weight:700;letter-spacing:.16em;color:#9AA0A8;text-transform:uppercase;">{eyebrow}</div>'
     f'<div style="font-size:30px;line-height:1.15;font-weight:800;color:#FFFFFF;margin:8px 0 8px;letter-spacing:-.01em;">{title}</div><div style="font-size:15px;line-height:1.5;color:#C9CDD2;">{sub}</div></td></tr>')
def sec(inner,bg='#FFFFFF',pad='24px 16px'):
    return f'<tr><td style="background:{bg};padding:{pad};font-family:{F};color:{INK};">{inner}</td></tr>'
def h2(t,sub=''):
    return f'<div style="font-size:20px;font-weight:800;color:{INK};margin:0 0 4px;">{t}</div>'+(f'<div style="font-size:14px;color:{MUT};margin:0 0 14px;">{sub}</div>' if sub else '<div style="height:12px;"></div>')
def ticket(pct,label,code,valid,cta,url):
    return (f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1.5px solid {INK};border-radius:18px;overflow:hidden;"><tr>'
     f'<td width="34%" style="{GRAD}width:34%;padding:18px 8px;text-align:center;border-right:2px dashed #FFFFFF;"><div style="font-size:30px;font-weight:800;color:#FFFFFF;line-height:1;">{pct}</div><div style="font-size:10px;font-weight:700;letter-spacing:.12em;color:#B9BEC5;text-transform:uppercase;margin-top:6px;">{label}</div></td>'
     f'<td style="background:#FFFFFF;padding:16px 12px;text-align:center;"><div style="font-size:10px;font-weight:700;letter-spacing:.14em;color:{MUT};text-transform:uppercase;">Dein Code</div><div style="font-size:21px;font-weight:800;font-family:{MONO};color:{INK};letter-spacing:.03em;margin:6px 0 4px;word-break:break-all;">{code}</div><div style="font-size:12px;color:{MUT};">{valid}</div></td></tr></table>'
     f'<div style="height:14px;"></div>'+btn(cta,url))
def cards(keys,c,title=None):
    cell=lambda k: (f'<td width="50%" valign="top" style="width:50%;padding:6px;"><a href="{utm(P[k][3],c)}" style="text-decoration:none;color:{INK};display:block;border:1px solid {LINE};border-radius:16px;overflow:hidden;background:#FFFFFF;">'
       f'<div style="background:#0D0F12;text-align:center;line-height:0;"><img src="{P[k][2]}" width="150" alt="{P[k][0]}" style="display:block;width:100%;height:auto;border:0;"></div>'
       f'<div style="padding:10px 10px 12px;font-family:{F};"><div style="font-size:15px;font-weight:800;color:{INK};">{P[k][0]}</div><div style="font-size:13px;color:{MUT};margin:2px 0 10px;">{P[k][1]}</div>'
       f'<div style="{GRAD}color:#FFFFFF;border-radius:10px;padding:9px 6px;text-align:center;font-size:13px;font-weight:700;">Ansehen →</div></div></a></td>')
    rows=''.join('<tr>'+''.join(cell(k) for k in keys[i:i+2])+'</tr>' for i in range(0,len(keys),2))
    return (h2(title) if title else '')+f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 -6px;width:calc(100% + 12px);">{rows}</table>'
def icons():
    it=[('🚚','Versand','in 2 Werktagen'),('📦','Diskret','neutral verpackt'),('🧪','Geprüft','HPLC / LC-MS')]
    c=''.join(f'<td width="33%" align="center" valign="top" style="padding:0 4px;font-family:{F};"><div style="width:48px;height:48px;line-height:48px;border-radius:50%;background:{SOFT};font-size:22px;margin:0 auto;">{a}</div><div style="font-size:13px;font-weight:800;color:{INK};margin-top:8px;">{b}</div><div style="font-size:11px;color:{MUT};margin-top:2px;">{d}</div></td>' for a,b,d in it)
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{c}</tr></table>'
def foot():
    return (f'<tr><td style="background:#FFFFFF;padding:6px 16px 22px;text-align:center;font-family:{F};font-size:12px;line-height:1.6;color:{MUT};">Du erhältst diese E-Mail, weil du den Peptrium-Newsletter abonniert hast.<br><a href="[[unsubscribe_link]]" style="color:{MUT};">Abmelden</a></td></tr>'
     f'<tr><td style="{GRAD}padding:24px 18px;text-align:center;font-family:{F};"><div style="font-size:15px;font-weight:700;color:#FFFFFF;margin-bottom:12px;">Fragen? Wir sind für dich da.</div>'
     f'<table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td style="padding:0 5px;"><a href="mailto:info@peptrium.com" style="display:inline-block;background:#FFFFFF;color:{INK};text-decoration:none;font-weight:700;font-size:13px;padding:11px 14px;border-radius:12px;">✉️ E-Mail</a></td>'
     f'<td style="padding:0 5px;"><a href="https://t.me/peptrium" style="display:inline-block;background:#229ED9;color:#FFFFFF;text-decoration:none;font-weight:700;font-size:13px;padding:11px 14px;border-radius:12px;">Telegram</a></td></tr></table>'
     f'<div style="font-size:11px;line-height:1.6;color:#80868E;margin-top:16px;">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</div></td></tr>')
def wrap(rows):
    return (f'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:#E9EBED;">'
     f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E9EBED;"><tr><td align="center" style="padding:10px 6px;">'
     f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border-radius:20px;overflow:hidden;">{rows}</table></td></tr></table></body></html>')
S=json.loads(sys.argv[1]) if len(sys.argv)>1 else {}
v=lambda k,d: S.get(k,d)
mails={}
mails['willkommen']=wrap(head('nl-hero-vials.jpg','Dein Willkommensgeschenk','10 % für dich.','Schön, dass du dabei bist.')
 +sec(ticket('10 %','Willkommen','[[contact.custom_properties.hallo_code]]','gültig bis [[contact.custom_properties.hallo_gueltig_bis]]','Jetzt einlösen →',utm('/alle-produkte/','willkommen')))
 +sec(cards(['reta','ghk','pen','mots'],'willkommen','Beliebt bei Insidern'),SOFT)
 +sec(icons(),'#FFFFFF','24px 12px 18px')+foot())
mails['erinnerung']=wrap(head('nl-hero-vials.jpg','Kurze Erinnerung','Nur noch [[contact.custom_properties.erinnerung_tage]].','Dein Code ist noch nicht eingelöst.')
 +sec(ticket('[[contact.custom_properties.erinnerung_prozent]]','läuft ab','[[contact.custom_properties.erinnerung_code]]','nur noch [[contact.custom_properties.erinnerung_tage]] gültig','Code jetzt nutzen →',utm('/alle-produkte/','code-erinnerung')))
 +sec(cards(['reta','ghk'],'code-erinnerung','Am meisten gekauft'),SOFT)+foot())
def lastorder():
    r=''
    for i in (1,2):
        cp='[[contact.custom_properties.nachkauf_%s_'+str(i)+']]'
        b='border-bottom:1px solid '+LINE+';'
        r+=(f'<tr><td width="76" style="width:76px;padding:10px 0;{b}"><img src="{cp%"bild"}" width="64" height="64" alt="" style="display:block;width:64px;height:64px;border-radius:12px;background:#0D0F12;border:0;"></td>'
            f'<td style="padding:10px 6px;{b}font-family:{F};"><div style="font-size:15px;font-weight:800;color:{INK};">{cp%"name"}</div><div style="font-size:13px;color:{MUT};">{cp%"info"}</div></td></tr>')
    return h2('Deine letzte Bestellung')+f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">{r}</table>'+btn('Gleiche Bestellung nochmal →','[[contact.custom_properties.nachkauf_link]]')
abo=(f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-radius:16px;overflow:hidden;"><tr><td style="background-color:#FF6B35;background-image:linear-gradient(135deg,#FF6B35 0%,#E5342B 100%);padding:18px 16px;font-family:{F};color:#FFFFFF;">'
     f'<div style="font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;opacity:.85;">Peptrium Abo</div><div style="font-size:20px;font-weight:800;margin:4px 0 4px;">Nie wieder nachbestellen</div>'
     f'<div style="font-size:14px;line-height:1.5;margin-bottom:14px;">Ab der 2. Lieferung −15 % · jederzeit pausierbar</div><a href="{utm("/abo-modell/","nachkauf")}" style="display:inline-block;background:#FFFFFF;color:#E5342B;text-decoration:none;font-weight:800;font-size:14px;padding:11px 18px;border-radius:12px;">Abo ansehen →</a></td></tr></table>')
mails['nachkauf']=wrap(head('nl-hero-pen.jpg','Nachbestellen','Zeit für Nachschub?','Deine letzte Bestellung ist rund 4 Wochen her.')
 +sec(lastorder())+sec(abo,SOFT)+sec(icons(),'#FFFFFF','24px 12px 18px')+foot())
mails['nachkauf2']=wrap(head('nl-hero-pen.jpg','Kurzer Hinweis','Noch alles da?','Mit einem Klick wieder im Warenkorb.')
 +sec(lastorder())+sec(cards(['ghk','mots'],'nachkauf-2','Passt dazu'),SOFT)+foot())
mails['rueckgewinnung']=wrap(head('nl-hero-vials.jpg','Wir vermissen dich','Komm zurück – mit 10 %.','Nur für kurze Zeit.')
 +sec(ticket('[[contact.custom_properties.comeback_prozent]]','Comeback','[[contact.custom_properties.comeback_code]]','gültig bis [[contact.custom_properties.comeback_bis]]','Jetzt einlösen →',utm('/alle-produkte/','rueckgewinnung')))
 +sec(cards(['reta','pen','ghk','mots'],'rueckgewinnung','Neu & beliebt'),SOFT)+sec(icons(),'#FFFFFF','24px 12px 18px')+foot())
for k,h in mails.items(): open(k+'.html','w',encoding='utf-8').write(h)
print('ok')
