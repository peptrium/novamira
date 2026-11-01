import json, re
D = json.load(open('mb.json')); o = D['okt']; s = D['sep']; L = D['lager']
PURCH = 1417.13; MICH_OFFEN = 1096.54 + 845.58 - 1100
def eur(v, sign=''):
    t = f"{abs(v):,.2f}".replace(',', 'X').replace('.', ',').replace('X', '.')
    return f"{sign}{t} €"
def pct(a, b): return f"{(a-b)/b*100:+.0f} %".replace('+', '+ ').replace('-', '− ') if b else '–'
INK='#0D0F12'; MUT='#5B6169'; LINE='#E4E6E9'; GRN='#1F8A4C'; RED='#B42318'; BG='#F5F6F7'
F="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif;"
def card(title, inner, sub=''):
    return f'''<tr><td style="padding:0 16px 16px;"><div style="background:#FFF;border:1px solid {LINE};border-radius:16px;padding:18px 18px 14px;">
<div style="font-size:11px;font-weight:800;letter-spacing:.14em;color:{MUT};text-transform:uppercase;">{title}</div>{f'<div style="font-size:12px;color:{MUT};margin-top:3px;">{sub}</div>' if sub else ''}
<div style="height:10px"></div>{inner}</div></td></tr>'''
def row(label, val, note='', bold=False, color=INK, top=False):
    b = 'font-weight:800;' if bold else 'font-weight:600;'
    bt = f'border-top:2px solid {INK};' if top else f'border-top:1px solid {LINE};'
    return f'''<tr><td style="padding:9px 0;{bt}font-size:14px;color:{INK};{b}">{label}{f'<div style="font-size:11.5px;font-weight:400;color:{MUT};margin-top:2px;">{note}</div>' if note else ''}</td>
<td align="right" style="padding:9px 0;{bt}font-size:15px;{b}color:{color};white-space:nowrap;vertical-align:top;">{val}</td></tr>'''
def tbl(rows): return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0">{"".join(rows)}</table>'

marge = o['res'] / o['rev'] * 100
# KPI tiles
def tile(lbl, val, cmp):
    return f'''<td width="50%" style="width:50%;padding:5px;vertical-align:top;"><div style="background:#1A1E23;border-radius:14px;padding:14px;">
<div style="font-size:11px;color:#9AA0A8;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">{lbl}</div>
<div style="font-size:22px;font-weight:800;color:#FFF;margin-top:4px;white-space:nowrap;">{val}</div>
<div style="font-size:11.5px;color:#7FD49B;margin-top:3px;">{cmp}</div></div></td>'''
kpi = f'''<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
{tile('Umsatz', eur(o['rev']), pct(o['rev'], s['rev'])+' ggü. Sep.')}{tile('Netto-Gewinn', eur(o['res']), pct(o['res'], s['res'])+' ggü. Sep.')}</tr><tr>
{tile('Bestellungen', str(o['orders']), f"Sep.: {s['orders']}")}{tile('Ø Bestellwert', eur(o['rev']/o['orders']), f"Sep.: {eur(s['rev']/s['orders'])}")}</tr></table>'''

hero = f'''<tr><td style="background:#0B0D10;padding:22px 20px 18px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td><img src="https://peptrium.com/wp-content/uploads/2026/08/IMG_2644-cropped-300x300.png" width="30" height="30" alt="" style="vertical-align:middle;border:0;"><span style="vertical-align:middle;margin-left:8px;font-size:12px;font-weight:800;letter-spacing:.3em;color:#E6E8EB;">PEPTRIUM</span></td>
<td align="right" style="font-size:11px;color:#7D848D;">Vertraulich</td></tr></table>
<div style="font-size:12px;font-weight:700;letter-spacing:.16em;color:#9AA0A8;margin-top:22px;">MONATSBERICHT</div>
<div style="font-size:34px;font-weight:800;color:#FFF;letter-spacing:-.02em;margin:4px 0 4px;">Oktober 2026</div>
<div style="font-size:13px;color:#9AA0A8;margin-bottom:14px;">01.10. – 31.10.2026 · bezahlte Bestellungen · erstellt am 01.11.2026</div>
{kpi}</td></tr>'''

wf = tbl([
 row('Umsatz', eur(o['rev']), f"{o['orders']} bezahlte Bestellungen, inkl. {eur(o['ship_in'])} Versandgebühren"),
 row('− Wareneinsatz', eur(o['cogs'],'− '), f"Einkaufspreis der verkauften Ware inkl. Gratis-Geschenke ({eur(o['gift_cogs'])})", color=RED),
 row('− Partner-Provisionen', eur(o['comm'],'− '), f"{o['comm']/o['rev']*100:.1f} % vom Umsatz", color=RED),
 row('− Versand & Verpackung', eur(o['own'],'− '), 'DHL 6,19 € (AT 14,90 €) + 0,20 € Verpackung je Paket', color=RED),
 row('= Netto-Gewinn', eur(o['res']), f"{marge:.0f} % vom Umsatz · ohne sonstige Kosten (Omnisend, Hosting …)", bold=True, color=GRN, top=True),
])
# visual bar
parts=[(o['cogs'],'#8E959E','Ware'),(o['comm'],'#C98A2B','Provision'),(o['own'],'#B9BEC5','Versand'),(o['res'],GRN,'Gewinn')]
bar='<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-radius:8px;overflow:hidden;"><tr>'+''.join(f'<td width="{v/o["rev"]*100:.1f}%" style="height:14px;background:{c};font-size:0;">&nbsp;</td>' for v,c,_ in parts)+'</tr></table>'
leg='<div style="font-size:11.5px;color:'+MUT+';margin-top:6px;">'+' &nbsp; '.join(f'<span style="white-space:nowrap;"><span style="color:{c};">■</span> {n} {v/o["rev"]*100:.0f} %</span>' for v,c,n in parts)+'</div>'
c1 = card('Vom Umsatz zum Gewinn', wf + '<div style="height:12px"></div>' + bar + leg)

geld = o['rev'] - PURCH - o['aff_transfer'] - o['own']
c2 = card('Geldfluss – was wirklich aufs Konto kam', tbl([
 row('Eingenommen', eur(o['rev']), 'Zahlungen der Kunden im Oktober'),
 row('− Einkäufe bezahlt', eur(PURCH,'− '), 'Peptid-Lieferant 1.248,11 € · Bac-Water-Lieferant 169,02 € (18.10.)', color=RED),
 row('− An Partner überwiesen', eur(o['aff_transfer'],'− '), 'Michaela Muche, 17.10.', color=RED),
 row('− Versand & Verpackung', eur(o['own'],'− '), color=RED),
 row('= Geld übrig', eur(geld), bold=True, color=GRN, top=True),
]) + f'<div style="font-size:12px;color:{MUT};margin-top:8px;line-height:1.5;">Mehr als der Gewinn, weil weniger eingekauft als verkauft wurde (Lager wurde kleiner) und ein Teil der Provision noch nicht überwiesen ist.</div>')

c3 = card('Noch offen', tbl([
 row('Unbezahlte Bestellungen (Vorkasse)', eur(o['open_rev']), f"{o['open']} Bestellungen aus Oktober, noch nicht im Umsatz"),
 row('Provision an Michaela, noch nicht überwiesen', eur(MICH_OFFEN), 'verdient aus bezahlten Bestellungen minus Überweisungen (Stand heute)'),
 row('Stornierte Bestellungen', str(o['canc']), 'nicht bezahlt / doppelt'),
]))

# partners
prow=[]
for a,v in sorted(o['aff'].items(), key=lambda x:-x[1]['rev']):
    nm=o['aff_names'][a]
    note=f"{v['n']} Bestellungen · {v['rev']/o['rev']*100:.0f} % vom Umsatz" + (f" · Ø Provision {v['comm']/v['rev']*100:.1f} %" if v['comm'] else '')
    prow.append(f'''<tr><td style="padding:9px 0;border-top:1px solid {LINE};font-size:14px;font-weight:700;color:{INK};">{nm}<div style="font-size:11.5px;font-weight:400;color:{MUT};margin-top:2px;">{note}</div></td>
<td align="right" style="padding:9px 0;border-top:1px solid {LINE};font-size:13px;color:{MUT};white-space:nowrap;vertical-align:top;">{eur(v['rev'])}<div style="font-size:14px;font-weight:800;color:{INK if v['comm'] else MUT};">{eur(v['comm']) if v['comm'] else '–'}</div></td></tr>''')
c4 = card('Partner (Affiliates)', f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="font-size:11px;color:{MUT};padding-bottom:4px;">Partner</td><td align="right" style="font-size:11px;color:{MUT};padding-bottom:4px;">Umsatz / Provision</td></tr>{"".join(prow)}</table>')

# products
P={}
for k,v in o['prod'].items():
    k2=k.replace('Peptrium-Pen (Retatrutide)','Reta-Pen').replace('Peptrium-Pen (GHK-Cu)','GHK-Cu-Pen').replace('Pen Nadeln 32G x 4mm (15er Pack)','Pen Nadeln (15er)').replace('Insulinspritze 10er Pack','Insulinspritzen (10er)')
    if k2=='GHK-Cu': k2='GHK-Cu 50 mg'
    if k2 in('Cagrilintid','GLOW','KPV','Mots-C','Selank'): k2={'Cagrilintid':'Cagrilintid 10 mg','GLOW':'GLOW 70 mg','KPV':'KPV 10 mg','Mots-C':'MOTS-c 10 mg','Selank':'Selank 10 mg'}[k2]
    p=P.setdefault(k2,{'q':0,'rev':0,'cogs':0}); 
    for f in p: p[f]+=v[f]
th=lambda t,a='left': f'<td align="{a}" style="font-size:10.5px;color:{MUT};font-weight:700;padding:0 0 6px;text-transform:uppercase;letter-spacing:.06em;">{t}</td>'
pr=[]
for k,v in sorted(P.items(), key=lambda x:-x[1]['rev']):
    g=v['rev']-v['cogs']
    pr.append(f'''<tr><td style="padding:8px 4px 8px 0;border-top:1px solid {LINE};font-size:13px;font-weight:700;color:{INK};">{k}</td>
<td align="right" style="padding:8px 4px;border-top:1px solid {LINE};font-size:13px;color:{INK};">{v['q']}</td>
<td align="right" style="padding:8px 4px;border-top:1px solid {LINE};font-size:13px;color:{INK};white-space:nowrap;">{eur(v['rev'])}</td>
<td align="right" style="padding:8px 0 8px 4px;border-top:1px solid {LINE};font-size:13px;font-weight:700;color:{GRN};white-space:nowrap;">{eur(g)}</td></tr>''')
tq=sum(v['q'] for v in P.values()); tr=sum(v['rev'] for v in P.values()); tg=sum(v['rev']-v['cogs'] for v in P.values())
pr.append(f'''<tr><td style="padding:9px 0;border-top:2px solid {INK};font-size:13px;font-weight:800;">Gesamt</td><td align="right" style="padding:9px 4px;border-top:2px solid {INK};font-size:13px;font-weight:800;">{tq}</td><td align="right" style="padding:9px 4px;border-top:2px solid {INK};font-size:13px;font-weight:800;white-space:nowrap;">{eur(tr)}</td><td align="right" style="padding:9px 0;border-top:2px solid {INK};font-size:13px;font-weight:800;color:{GRN};white-space:nowrap;">{eur(tg)}</td></tr>''')
c5 = card('Produkte', f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{th("Produkt")}{th("Stück","right")}{th("Umsatz","right")}{th("Rohertrag","right")}</tr>{"".join(pr)}</table>'
 + f'<div style="font-size:12px;color:{MUT};margin-top:10px;line-height:1.55;">Rohertrag = Umsatz minus Einkaufspreis (vor Provision/Versand). Umsatz je Produkt nach Rabatten.<br>'
 + f'<b style="color:{INK};">Gratis-Geschenke:</b> {o["gifts"]} Stück (Ladenwert {eur(o["gift_value"])}, Einkauf {eur(o["gift_cogs"])})<br>'
 + f'<b style="color:{INK};">Halloween (29.–31.10.):</b> {o["hw_free"]} Flaschen gratis (Ladenwert {eur(o["hw_save"])})</div>', 'sortiert nach Umsatz')

# customers
conv = o['orders']/o['visitors']*100
c6 = card('Kunden & Shop', tbl([
 row('Neukunden', str(o['new']), f"Sep.: {s['new']}"),
 row('Stammkunden (2. Bestellung oder mehr)', str(o['ret']), f"Sep.: {s['ret']}"),
 row('Länder', ' · '.join(f'{k} {v}' for k,v in o['country'].items())),
 row('Besucher', f"{o['visitors']:,}".replace(',','.'), f"seit 09.10. gemessen · {conv:.1f} % haben bestellt"),
 row('Neue Newsletter-Abonnenten', f"+{o['nl_new']}", 'inkl. 35 übernommene Bestandskunden'),
 row('Größte Bestellung', eur(o['max'])),
 row('Zahlungsart', 'Vorkasse 100 %'),
]))
CP={"HALLO":[7,93.25],"EMPF":[32,580.92],"SCHLANK10":[6,115.43],"EUNICE95":[1,95.41],"PEP10":[2,67.43]}
lab={"EMPF":("10 % Empfehlungs-Rabatt","automatisch über Partner-Links"),"HALLO":("HALLO-Codes","Newsletter-Willkommenscode"),"SCHLANK10":("SCHLANK10","Partner-Code Michaela"),"PEP10":("PEP10",""),"EUNICE95":("EUNICE95","Gutschrift für stornierte Vorbestellung")}
rr=[row(lab[k][0]+f' <span style="color:{MUT};font-weight:400;">· {v[0]}×</span>', eur(v[1],'− '), lab[k][1], color=RED) for k,v in sorted(CP.items(),key=lambda x:-x[1][1])]
tot=sum(v[1] for v in CP.values())
rr.append(row('Rabatte gesamt', eur(tot,'− '), 'ohne Mengenrabatt, Halloween und Gratis-Geschenke (stehen bei Produkte)', bold=True, top=True))
c7 = card('Rabatte & Codes', tbl(rr))

# daily chart
mx=max(o['days'].values()); cells=''
for d in range(1,32):
    v=o['days'].get(str(d),0); h=int(v/mx*90) if v else 0
    cells+=f'<td valign="bottom" align="center" style="padding:0 1px;"><div style="height:{90-h}px;font-size:0;">&nbsp;</div><div style="height:{max(h,2)}px;background:{"#E07B1F" if d>=29 else INK if v else LINE};border-radius:3px 3px 0 0;font-size:0;">&nbsp;</div></td>'
axis=''.join(f'<td align="center" style="font-size:9px;color:{MUT};padding-top:3px;">{d if d in(1,8,15,22,29) else ""}</td>' for d in range(1,32))
c8 = card('Umsatz pro Tag', f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="table-layout:fixed;"><tr>{cells}</tr><tr>{axis}</tr></table><div style="font-size:11.5px;color:{MUT};margin-top:6px;"><span style="color:#E07B1F;">■</span> Halloween ab 29.10. · stärkster Tag {eur(mx)}</div>')

c9 = card('Lager (Stand heute)', tbl([
 row('Einkaufswert im Lager', eur(L['val']), f"+ unterwegs: {eur(L['val_in']-L['val'])}"),
 row('Verkaufswert im Lager', eur(L['vk']), f"inkl. unterwegs: {eur(L['vk_in'])} · zu Listenpreisen"),
]) + f'<div style="font-size:12px;color:{MUT};margin-top:8px;">Zubehör (Spritzen, Nadeln, Pens) wird noch nicht gezählt.</div>')

foot=f'''<tr><td style="padding:20px;background:#12151A;text-align:center;font-size:11px;line-height:1.6;color:#7D848D;">Peptrium · interner Monatsbericht · vertraulich<br>Zahlen aus dem Shop-System, ohne Steuerberatung. Gewinn ohne sonstige Fixkosten.</td></tr>'''
html=f'''<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:#E9EBED;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E9EBED;"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:{BG};{F}">
{hero}<tr><td style="height:16px"></td></tr>{c1}{c2}{c3}{c4}{c5}{c6}{c7}{c8}{c9}{foot}</table></td></tr></table></body></html>'''
open('bericht-oktober.html','w').write(html)
print(len(html))
