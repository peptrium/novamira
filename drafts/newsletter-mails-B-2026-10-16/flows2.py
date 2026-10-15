from gen import *
def items(rows,total=None):
    r=''
    for i,(img,name,info,price) in enumerate(rows):
        bd=f'border-bottom:1px solid {BD};' if i<len(rows)-1 or total else ''
        r+=f'<tr><td width="74" style="width:74px;padding:10px 0;{bd}"><img src="{img}" width="62" height="62" alt="" style="display:block;width:62px;height:62px;border-radius:12px;border:0;background:#0D0F12;"></td><td style="padding:10px 6px;{bd}text-align:left;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#FFFFFF;">{name}</div><div style="font-size:12px;color:{MUT};margin-top:2px;">{info}</div></td><td align="right" style="padding:10px 0;{bd}font-size:15px;font-weight:800;color:#FFFFFF;white-space:nowrap;font-family:{F};">{price}</td></tr>'
    if total: r+=f'<tr><td colspan="2" style="padding:12px 0 0;text-align:left;font-size:14px;color:{MUT};font-family:{F};">Summe</td><td align="right" style="padding:12px 0 0;font-size:18px;font-weight:800;color:#FFFFFF;white-space:nowrap;font-family:{F};">{total}</td></tr>'
    return f'<div style="border:1px solid {BD};background:{CARD};border-radius:18px;padding:8px 16px 16px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0">{r}</table></div>'
def head(title,sub,cta,url):
    return row(logo()+f'<div style="font-size:32px;line-height:1.1;font-weight:800;color:#FFFFFF;margin:20px 0 10px;">{title}</div><div style="font-size:15px;line-height:1.5;color:{MUT};margin-bottom:22px;">{sub}</div>'+btn(cta,url,'86%'),'30px 20px 26px')
def tip(icon,t,d,link,lt):
    return f'<tr><td width="52" valign="top" style="width:52px;padding:12px 0;"><div style="width:42px;height:42px;line-height:42px;border-radius:50%;background:#1F242B;border:1px solid {BD};text-align:center;font-size:19px;">{icon}</div></td><td style="padding:12px 6px;text-align:left;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#FFFFFF;">{t}</div><div style="font-size:13px;line-height:1.5;color:{MUT};margin:3px 0 6px;">{d}</div><a href="{link}" style="font-size:13px;font-weight:700;color:#FFFFFF;">{lt} →</a></td></tr>'
def faq(q,a): return f'<div style="border-top:1px solid {BD};padding:14px 0;text-align:left;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#FFFFFF;">{q}</div><div style="font-size:13px;line-height:1.55;color:{MUT};margin-top:4px;">{a}</div></div>'
def cfoot(): return foot().replace('Du bekommst diese Mail, weil du den Peptrium-Newsletter abonniert hast.','Du bekommst diese Mail, weil du an der Kasse deine E-Mail-Adresse angegeben hast.')
def optout(): return row(f'<div style="font-size:11px;color:{DIM};line-height:1.6;"><a href="[[contact.custom_properties.wk_abmelden]]" style="color:{DIM};">Keine Warenkorb-Erinnerungen mehr</a></div>','0 22px 10px')
U2=U+'sp-email/'
CART=[(U2+'nl-p-reta.jpg','Retatrutide – 10 mg','Menge: 2','149,80 €'),(U2+'nl-p-bac.jpg','Bac Water – 10 ml','Menge: 1','6,90 €')]
M={}
M['warenkorb1']=body(head('Noch etwas vergessen?','Dein Warenkorb ist für dich gespeichert – mit einem Klick geht es genau dort weiter.','Zurück zum Warenkorb','#')
 +row(H('Dein','Warenkorb.')+'<div style="height:16px;"></div>'+items(CART,'156,70 €')+'<div style="height:18px;"></div>'+btn('Bestellung abschließen','#'),'26px 20px 30px')
 +row(H('Darum','Peptrium.')+trust(),'10px 20px 30px')+optout()+cfoot())
M['warenkorb2']=body(head('Noch Fragen zur Bestellung?','Dein Warenkorb ist weiterhin gespeichert. Hier die häufigsten Fragen – kurz beantwortet.','Zum Warenkorb','#')
 +row(items(CART,'156,70 €'),'0 20px 26px')
 +row(H('Kurz','beantwortet.')+'<div style="height:12px;"></div>'+faq('Wie kann ich bezahlen?','Per Vorkasse (Überweisung) oder Krypto. Nach Zahlungseingang geht dein Paket raus.')+faq('Wie schnell ist die Lieferung?','In der Regel innerhalb von 2 Werktagen per DHL – ab 100 € versandkostenfrei.')+faq('Wie wird verpackt?','Neutral und diskret, ohne Hinweis auf den Inhalt.')+faq('Noch etwas unklar?','Schreib uns per E-Mail oder Telegram – wir antworten schnell.')+'<div style="height:10px;"></div>'+btn('Bestellung abschließen','#'),'26px 20px 30px')
 +optout()+cfoot())
M['nachlieferung']=body(head('Ist alles gut angekommen?','Deine Bestellung #3243 sollte inzwischen bei dir sein. Hier ein paar Tipps für deine Forschung.','Hilfe & Kontakt','#')
 +row(H('Deine','Bestellung.')+'<div style="height:16px;"></div>'+items(CART),'26px 20px 30px')
 +row(H('Gut zu','wissen.')+'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:10px;">'
   +tip('❄️','Richtig lagern','Lyophilisierte Peptide kühl und dunkel lagern, gelöste im Kühlschrank.','https://peptrium.com/lagerungs-guide/','Zum Lagerungs-Guide')
   +tip('🧮','Exakt rekonstituieren','Unser Peptid-Rechner zeigt dir die passende Menge Bac Water.','https://peptrium.com/peptid-rechner/','Zum Peptid-Rechner')
   +tip('💬','Fragen?','Wir helfen dir gern weiter – per E-Mail oder Telegram.','mailto:info@peptrium.com','Schreib uns')+'</table>','10px 20px 30px')
 +row(H('Nachschub','gebraucht?','Deine Bestellung ist jederzeit mit einem Klick wieder im Warenkorb.')+'<div style="height:16px;"></div>'+btn('Erneut bestellen','#'),'10px 20px 30px')+foot())
for k,v in M.items(): open(k+'.html','w').write(f'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:{BG};">{v}</body></html>')
print(list(M))
