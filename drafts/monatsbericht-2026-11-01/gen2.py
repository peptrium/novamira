import json
D = json.load(open('mb2.json')); o = D['okt']; s = D['sep']
OPEN_N, OPEN_REV, CANC = 6, 1445.13, 7          # Oktober-Bestellungen, noch unbezahlt (nach Bestelldatum)
PURCH = 1417.13; MICH_OFFEN = 842.12; FIX = None
def eur(v, sign='', dec=2):
    t = f"{abs(v):,.{dec}f}".replace(',', 'X').replace('.', ',').replace('X', '.')
    return f"{sign}{t} €"
def chg(a, b):
    if not b: return ''
    p = (a - b) / b * 100
    return f"{'▲' if p >= 0 else '▼'} {abs(p):.0f} %"
de=lambda x: f'{x:.1f}'.replace('.',',')
INK='#0D0F12'; MUT='#5B6169'; LINE='#E4E6E9'; GRN='#1F8A4C'; RED='#B42318'; BG='#F5F6F7'; OR='#E07B1F'
F="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif;"
def card(title, inner, sub=''):
    return f'''<tr><td style="padding:0 14px 14px;"><div style="background:#FFF;border:1px solid {LINE};border-radius:16px;padding:16px 16px 12px;">
<div style="font-size:11px;font-weight:800;letter-spacing:.14em;color:{MUT};text-transform:uppercase;">{title}</div>{f'<div style="font-size:12px;color:{MUT};margin-top:2px;">{sub}</div>' if sub else ''}
<div style="height:8px"></div>{inner}</div></td></tr>'''
td = lambda x, a='right', st='': f'<td align="{a}" style="padding:8px 0 8px 6px;border-top:1px solid {LINE};font-size:13.5px;white-space:nowrap;vertical-align:top;{st}">{x}</td>'
def r2(label, a, b, note='', bold=False, col=INK, top=False):
    bt = f'border-top:2px solid {INK};' if top else f'border-top:1px solid {LINE};'
    w = 'font-weight:800;' if bold else 'font-weight:600;'
    return f'''<tr><td style="padding:8px 0;{bt}font-size:13.5px;{w}color:{INK};">{label}{f'<div style="font-size:11px;font-weight:400;color:{MUT};margin-top:1px;">{note}</div>' if note else ''}</td>
<td align="right" style="padding:8px 0 8px 6px;{bt}font-size:14px;{w}color:{col};white-space:nowrap;vertical-align:top;">{a}</td>
<td align="right" style="padding:8px 0 8px 8px;{bt}font-size:12px;color:{MUT};white-space:nowrap;vertical-align:top;">{b}</td></tr>'''
head2 = lambda c1='', c2='Okt.', c3='Sep.': f'<tr><td style="font-size:10.5px;color:{MUT};font-weight:700;padding-bottom:5px;">{c1}</td><td align="right" style="font-size:10.5px;color:{INK};font-weight:800;padding-bottom:5px;">{c2}</td><td align="right" style="font-size:10.5px;color:{MUT};font-weight:700;padding-bottom:5px;">{c3}</td></tr>'
T = lambda rows: f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0">{"".join(rows)}</table>'

# ---------- Kopf ----------
def tile(lbl, val, sub):
    return f'''<td width="50%" style="width:50%;padding:4px;vertical-align:top;"><div style="background:#1A1E23;border-radius:14px;padding:13px;">
<div style="font-size:10.5px;color:#9AA0A8;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">{lbl}</div>
<div style="font-size:22px;font-weight:800;color:#FFF;margin-top:3px;white-space:nowrap;">{val}</div>
<div style="font-size:11.5px;color:#9AA0A8;margin-top:2px;">{sub}</div></div></td>'''
up = lambda a,b: f'<span style="color:#7FD49B;">{chg(a,b)}</span>'
hero = f'''<tr><td style="background:#0B0D10;padding:20px 18px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td><img src="https://peptrium.com/wp-content/uploads/2026/08/IMG_2644-cropped-300x300.png" width="28" height="28" alt="" style="vertical-align:middle;border:0;"><span style="vertical-align:middle;margin-left:8px;font-size:12px;font-weight:800;letter-spacing:.3em;color:#E6E8EB;">PEPTRIUM</span></td>
<td align="right" style="font-size:11px;color:#7D848D;">Vertraulich</td></tr></table>
<div style="font-size:12px;font-weight:700;letter-spacing:.16em;color:#9AA0A8;margin-top:18px;">MONATSBERICHT</div>
<div style="font-size:32px;font-weight:800;color:#FFF;letter-spacing:-.02em;margin:2px 0 12px;">Oktober 2026</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
{tile('Umsatz', eur(o['rev'],dec=0), up(o['rev'],s['rev'])+' ggü. Sep.')}{tile('Gewinn', eur(o['res'],dec=0), up(o['res'],s['res'])+' · vor Fixkosten')}</tr><tr>
{tile('Bestellungen', str(o['orders']), f"Sep.: {s['orders']}")}{tile('Ø Bestellung', eur(o['rev']/o['orders'],dec=0), f"Sep.: {eur(s['rev']/s['orders'],dec=0)}")}</tr></table></td></tr>'''

# ---------- Produkte vorbereiten ----------
NAME = {'Peptrium-Pen (Retatrutide)':'Reta-Pen','Peptrium-Pen (GHK-Cu)':'GHK-Cu-Pen','Pen Nadeln 32G x 4mm (15er Pack)':'Pen Nadeln','Insulinspritze 10er Pack':'Insulinspritzen'}
FIXN = {'GHK-Cu':'GHK-Cu 50 mg','Cagrilintid':'Cagrilintid 10 mg','GLOW':'GLOW 70 mg','KPV':'KPV 10 mg','Mots-C':'MOTS-c 10 mg','Selank':'Selank 10 mg'}
P = {}
for k, v in o['prod'].items():
    for a, b in NAME.items(): k = k.replace(a, b)
    k = FIXN.get(k, k)
    p = P.setdefault(k, {'q':0,'rev':0,'cogs':0})
    for f in p: p[f] += v[f]
for p in P.values(): p['g'] = p['rev'] - p['cogs']
best = sorted(P.items(), key=lambda x: -x[1]['g'])
aff40 = o['aff']['40']; aff40s = s['aff']['40']
share = aff40['n'] / o['orders'] * 100
marge = o['res'] / o['rev'] * 100

# ---------- Auf einen Blick ----------
pts = [
 f"<b>Bester Monat seit Start:</b> {o['orders']} bezahlte Bestellungen und {eur(o['rev'],dec=0)} Umsatz – {de(o['rev']/s['rev'])}× so viel wie im September.",
 f"<b>{marge:.0f} % vom Umsatz bleiben als Gewinn</b> ({eur(o['res'],dec=0)}), Fixkosten noch nicht abgezogen.",
 f"<b>{aff40['n']} von {o['orders']} Bestellungen kamen über Michaela</b> ({share:.0f} %). Ihre Provision: {eur(aff40['comm'],dec=0)}.",
 f"<b>Halloween (29.–31.10.)</b> brachte in 3 Tagen {eur(o['hw_rev'],dec=0)} = {o['hw_rev']/o['rev']*100:.0f} % des Monats. Die Aktion lief bis 1.11. weiter.",
 f"<b>Am meisten verdient an {best[0][0]}</b> ({eur(best[0][1]['g'],dec=0)} Rohertrag aus {best[0][1]['q']} Flaschen).",
 f"<b>Noch offen:</b> {OPEN_N} Oktober-Bestellungen über {eur(OPEN_REV,dec=0)} warten auf Zahlung.",
]
blick = card('Auf einen Blick', ''.join(f'<div style="font-size:14px;line-height:1.5;color:{INK};padding:7px 0;border-top:1px solid {LINE};">{t}</div>' for t in pts))

# ---------- Gewinn ----------
fixrow = r2('− Fixkosten', '–', '–', 'Omnisend, Hosting, Werbung … (noch nicht eingetragen)', col=MUT)
gw = T([head2(),
 r2('Umsatz', eur(o['rev']), eur(s['rev']), f"inkl. {eur(o['ship_in'])} Versandgebühren"),
 r2('− Ware (Einkauf)', eur(o['cogs'],'− '), eur(s['cogs'],'− '), f"inkl. Gratis-Geschenke {eur(o['gift_cogs'])}", col=RED),
 r2('− Partner-Provision', eur(o['comm'],'− '), eur(s['comm'],'− '), f"{de(o['comm']/o['rev']*100)} % vom Umsatz", col=RED),
 r2('− Versand & Verpackung', eur(o['own'],'− '), eur(s['own'],'− '), 'DHL 6,19 € (AT 14,90 €) + 0,20 € je Paket', col=RED),
 fixrow,
 r2('= Gewinn', eur(o['res']), eur(s['res']), f"{marge:.0f} % vom Umsatz (Sep. {s['res']/s['rev']*100:.0f} %)", bold=True, col=GRN, top=True)])
parts=[(o['cogs'],'#8E959E','Ware'),(o['comm'],'#C98A2B','Provision'),(o['own'],'#B9BEC5','Versand'),(o['res'],GRN,'Gewinn')]
bar='<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'+''.join(f'<td width="{de(v/o["rev"]*100)}%" style="height:12px;background:{c};font-size:0;">&nbsp;</td>' for v,c,_ in parts)+'</tr></table>'
leg=f'<div style="font-size:11px;color:{MUT};margin-top:6px;">'+' &nbsp; '.join(f'<span style="white-space:nowrap;"><span style="color:{c};">■</span> {n} {v/o["rev"]*100:.0f} %</span>' for v,c,n in parts)+'</div>'
c_gew = card('Vom Umsatz zum Gewinn', gw + '<div style="height:10px"></div>' + bar + leg)

# ---------- Geld & Offen ----------
geld = o['rev'] - PURCH - o['aff_transfer'] - o['own']
r1 = lambda l,v,n='',col=INK,b=False,top=False: r2(l,v,'',n,b,col,top)
c_geld = card('Geld auf dem Konto', T([
 r1('Eingenommen', eur(o['rev'])),
 r1('− Ware eingekauft & bezahlt', eur(PURCH,'− '), 'Peptide 1.248,11 € · Bac Water 169,02 €', RED),
 r1('− An Michaela überwiesen', eur(o['aff_transfer'],'− '), '17.10.', RED),
 r1('− Versand & Verpackung', eur(o['own'],'− '), col=RED),
 r1('= Geld übrig', eur(geld), 'mehr als der Gewinn, weil weniger eingekauft als verkauft wurde', GRN, True, True),
]) + f'''<div style="height:12px"></div><div style="background:#FFF7EA;border:1px solid #F4D7A8;border-radius:12px;padding:10px 12px;font-size:13px;line-height:1.55;color:#5A3A00;">
<b>Noch offen:</b> {OPEN_N} unbezahlte Bestellungen ({eur(OPEN_REV)}) · Provision an Michaela noch nicht überwiesen: {eur(MICH_OFFEN)} · {CANC} storniert</div>''')

# ---------- Partner ----------
c_par = card('Partner', T([head2('', 'Okt.', 'Sep.'),
 r2('Michaela Muche', eur(aff40['comm']), eur(aff40s['comm']), f"{aff40['n']} Bestellungen · {eur(aff40['rev'],dec=0)} Umsatz · {de(aff40['comm']/aff40['rev']*100)} % (20 %, Halloween 25 %)"),
 r2('Ohne Partner', '–', '–', f"{o['aff']['0']['n']} Bestellungen · {eur(o['aff']['0']['rev'],dec=0)} Umsatz (Sep.: {s['aff']['0']['n']})", col=MUT),
]))

# ---------- Produkte ----------
th = lambda t,a='left': f'<td align="{a}" style="font-size:10px;color:{MUT};font-weight:700;padding:0 0 5px 6px;text-transform:uppercase;letter-spacing:.05em;">{t}</td>'
rows = []
top8, rest = best[:8], best[8:]
for k, v in top8:
    rows.append(f'<tr><td style="padding:8px 0;border-top:1px solid {LINE};font-size:13px;font-weight:700;">{k}</td>{td(v["q"])}{td(eur(v["rev"],dec=0))}{td(eur(v["g"],dec=0),st="font-weight:800;color:"+GRN+";")}{td(f"{v["g"]/v["rev"]*100:.0f} %",st="color:"+MUT+";font-size:12px;")}</tr>')
if rest:
    q=sum(v['q'] for _,v in rest); rv=sum(v['rev'] for _,v in rest); g=sum(v['g'] for _,v in rest)
    rows.append(f'<tr><td style="padding:8px 0;border-top:1px solid {LINE};font-size:12.5px;color:{MUT};">{len(rest)} weitere<div style="font-size:11px;">{", ".join(k for k,_ in rest)}</div></td>{td(q,st="color:"+MUT)}{td(eur(rv,dec=0),st="color:"+MUT)}{td(eur(g,dec=0),st="color:"+MUT)}{td("")}</tr>')
q=sum(v['q'] for v in P.values()); rv=sum(v['rev'] for v in P.values()); g=sum(v['g'] for v in P.values())
bt=f'border-top:2px solid {INK};'
rows.append(f'<tr><td style="padding:8px 0;{bt}font-size:13px;font-weight:800;">Gesamt</td>{td(q,st=bt+"font-weight:800;")}{td(eur(rv,dec=0),st=bt+"font-weight:800;")}{td(eur(g,dec=0),st=bt+"font-weight:800;color:"+GRN)}{td(f"{g/rv*100:.0f} %",st=bt+"font-size:12px;color:"+MUT)}</tr>')
c_pro = card('Produkte', f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{th("Produkt")}{th("Stk","right")}{th("Umsatz","right")}{th("Rohertrag","right")}{th("Marge","right")}</tr>{"".join(rows)}</table>'
 + f'<div style="font-size:11.5px;color:{MUT};margin-top:8px;line-height:1.55;">Sortiert nach Rohertrag (Umsatz nach Rabatten minus Einkauf). Ohne Versandgebühren, daher kleiner als der Gesamtumsatz.<br>'
 + f'Verschenkt: {o["gifts"]} Geschenke (Ladenwert {eur(o["gift_value"],dec=0)}, Einkauf {eur(o["gift_cogs"],dec=0)}) · Halloween: {o["hw_free"]} Flaschen gratis (Ladenwert {eur(o["hw_save"],dec=0)})</div>')

# ---------- Kunden ----------
def kt(l, v, sub=''):
    return f'<td width="33%" style="width:33%;padding:4px;vertical-align:top;"><div style="background:{BG};border-radius:12px;padding:10px;"><div style="font-size:10.5px;color:{MUT};font-weight:700;">{l}</div><div style="font-size:19px;font-weight:800;color:{INK};margin-top:2px;">{v}</div><div style="font-size:10.5px;color:{MUT};">{sub}</div></div></td>'
c_kun = card('Kunden', f'''<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{kt('Neukunden', o['new'], f"Sep.: {s['new']}")}{kt('Stammkunden', o['ret'], f"Sep.: {s['ret']}")}{kt('Länder', 'DE · AT', f"{o['country']['DE']} · {o['country']['AT']}")}</tr>
<tr>{kt('Besucher', f"{o['visitors']:,}".replace(',','.'), 'ab 9.10. gemessen')}{kt('Kaufquote', f"{de(o['orders']/o['visitors']*100)} %".replace('.',','), 'Besucher → Bestellung')}{kt('Newsletter', f"+{o['nl_new']}", 'inkl. 35 Bestandskunden')}</tr></table>''')

# ---------- Rabatte ----------
lab={"EMPF":"Empfehlungs-Rabatt (Partner-Link)","HALLO":"HALLO-Codes (Newsletter)","SCHLANK10":"SCHLANK10 (Michaela)","PEP10":"PEP10","EUNICE95":"EUNICE95 (Gutschrift)"}
cr=[r1(f'{lab.get(k,k)} <span style="color:{MUT};font-weight:400;">· {v[0]}×</span>', eur(v[1],'− '), col=RED) for k,v in sorted(o['coupons'].items(),key=lambda x:-x[1][1])]
tot=sum(v[1] for v in o['coupons'].values())
cr.append(r1('Rabatte gesamt', eur(tot,'− '), f"{de(tot/o['rev']*100)} % vom Umsatz · ohne Mengenrabatt/Halloween", INK, True, True))
c_rab = card('Rabatte & Codes', T(cr))

# ---------- Verlauf ----------
mx=max(o['days'].values()); cells=''
for d in range(1,32):
    v=o['days'].get(str(d),0); h=int(v/mx*80) if v else 0
    cells+=f'<td valign="bottom" align="center" style="padding:0 1px;"><div style="height:{80-h}px;font-size:0;">&nbsp;</div><div style="height:{max(h,2)}px;background:{OR if d>=29 else INK if v else LINE};border-radius:3px 3px 0 0;font-size:0;">&nbsp;</div></td>'
axis=''.join(f'<td align="center" style="font-size:9px;color:{MUT};padding-top:3px;">{d if d in(1,8,15,22,29) else ""}</td>' for d in range(1,32))
weeks=[(1,7),(8,14),(15,21),(22,28),(29,31)]
wk=''.join(f'<td align="center" style="padding:6px 2px 0;"><div style="font-size:10px;color:{MUT};">{a}.–{b}.</div><div style="font-size:12.5px;font-weight:800;color:{OR if a==29 else INK};">{eur(sum(o["days"].get(str(d),0) for d in range(a,b+1)),dec=0)}</div></td>' for a,b in weeks)
c_ver = card('Umsatz im Verlauf', f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="table-layout:fixed;"><tr>{cells}</tr><tr>{axis}</tr></table><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;border-top:1px solid {LINE};"><tr>{wk}</tr></table>', 'nach Zahlungseingang, orange = Halloween')

foot=f'''<tr><td style="padding:18px;background:#12151A;text-align:center;font-size:11px;line-height:1.6;color:#7D848D;">Gezählt werden Bestellungen nach <b style="color:#B9BEC5;">Zahlungseingang</b> im Monat (Testbestellungen ausgenommen).<br>Gewinn vor Fixkosten und Steuern · erstellt am 01.11.2026 · vertraulich</td></tr>'''
html=f'''<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:#E9EBED;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E9EBED;"><tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:{BG};{F}">
{hero}<tr><td style="height:14px"></td></tr>{blick}{c_gew}{c_geld}{c_par}{c_pro}{c_kun}{c_rab}{c_ver}{foot}</table></td></tr></table></body></html>'''
open('bericht-oktober-v2.html','w').write(html); print(len(html), best[0])
