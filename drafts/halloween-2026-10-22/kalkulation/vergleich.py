# Detail-Vergleich pro Bestellung: normal (mit Rabatten) vs. Halloween
R10=dict(n='Retatrutide 10 mg',p=74.90,ek=8.4579,usd=9.50)
G50=dict(n='GHK-Cu 50 mg',p=39.90,ek=2.938,usd=3.30)
SHIP_IN=4.90;FREE=150;DHL=6.19;PACK=0.20;PROV=0.20;BON=0.05;REF=0.10
GIFTS=[(200,'Injektionskit',4.0346),(350,'GHK-Cu 50 mg',2.938),(500,'Retatrutide 10 mg',8.4579)]
def bundle(q):  # Mengenrabatt: nur die Flaschen der größten erreichten Staffel
    for n,d in ((10,.20),(5,.15),(3,.10)):
        if q>=n: return n,d
    return 0,0
def calc(lines,partner,hw):
    rows=[];reg=sum(l['p']*q for l,q in lines)
    rows.append(('Warenwert regulär',reg,''))
    items=reg
    if not hw:
        md=0
        for l,q in lines:
            n,d=bundle(q);md+=n*l['p']*d
        if md: rows.append(('− Mengenrabatt',-md,'ab 3/5/10 gleichen Flaschen −10/15/20 %'));items-=md
        pay=items
        if partner: pr=items*REF;rows.append(('− Partner-Rabatt 10 %',-pr,''));pay-=pr
    else:
        units=sorted([l['p'] for l,q in lines for _ in range(q)]);free=sum(units[:len(units)//3])
        if free: rows.append((f'− {len(units)//3}× Flasche gratis (die günstigste)',-free,''));items-=free
        pay=items
        if partner: rows.append(('− Partner-Rabatt 10 %','—','gilt in der Aktion nicht auf Aktions-Flaschen'))
    ship=0 if items>=FREE else SHIP_IN
    rows.append(('= Kunde zahlt für die Ware',pay,'b'))
    rows.append(('+ Versand, den der Kunde zahlt',ship,'gratis ab 150 €' if ship==0 else 'unter 150 €'))
    rev=pay+ship;rows.append(('= Umsatz (kommt bei dir an)',rev,'b'))
    ek=sum(l['ek']*q for l,q in lines);desc=' + '.join(f"{q}× {l['ek']:.2f} €".replace('.',',') for l,q in lines)
    rows.append(('− Einkauf der Flaschen (EK)',-ek,desc))
    g=[(t,n,e) for t,n,e in GIFTS if items>=t]
    if g: rows.append(('− Einkauf Geschenk',-sum(e for _,_,e in g),', '.join(n+f' (ab {t} €)' for t,n,_ in g)))
    rate=PROV+(BON if hw else 0)
    if partner: c=pay*rate;rows.append((f'− Partner-Provision {int(round(rate*100))} %',-c,f'von {pay:.2f} €'.replace('.',',')))
    rows.append(('− DHL + Verpackung',-(DHL+PACK),'6,19 € + 0,20 €'))
    res=rev-ek-sum(e for _,_,e in g)-(pay*rate if partner else 0)-DHL-PACK
    rows.append(('= Dein Gewinn',res,'B'))
    return rows,pay+ship,res
S=[('1 Flasche Retatrutide 10 mg – Kunde über Partner-Link',[(R10,1)],True),
   ('2 Flaschen Retatrutide 10 mg – Kunde über Partner-Link',[(R10,2)],True),
   ('3 Flaschen Retatrutide 10 mg – Kunde direkt (ohne Partner)',[(R10,3)],False),
   ('3 Flaschen Retatrutide 10 mg – Kunde über Partner-Link',[(R10,3)],True),
   ('2× Retatrutide 10 mg + 1× GHK-Cu 50 mg – Kunde über Partner-Link',[(R10,2),(G50,1)],True),
   ('6 Flaschen Retatrutide 10 mg – Kunde über Partner-Link',[(R10,6)],True)]
eu=lambda v:(f'{v:,.2f} €'.replace(',','X').replace('.',',').replace('X','.')) if isinstance(v,(int,float)) else v
cards=[];summ=[]
for i,(t,lines,partner) in enumerate(S,1):
    a,ca,ra=calc(lines,partner,False);h,ch,rh=calc(lines,partner,True)
    labels=[];[labels.append(r[0]) for r in a+h if r[0] not in labels]
    order=['Warenwert regulär','− Mengenrabatt','− Partner-Rabatt 10 %','Partner-Rabatt']+[x for x in labels if x.startswith('− ') and 'gratis' in x]+['= Kunde zahlt für die Ware','+ Versand, den der Kunde zahlt','= Umsatz (kommt bei dir an)','− Einkauf der Flaschen (EK)','− Einkauf Geschenk']+[x for x in labels if 'Provision' in x]+['− DHL + Verpackung','= Dein Gewinn']
    da={r[0]:r for r in a};dh={r[0]:r for r in h}
    seen=[];trs=''
    for lab in order:
        if lab in seen: continue
        seen.append(lab)
        ra_=da.get(lab);rh_=dh.get(lab)
        if 'Provision' in lab:
            ra_=next((r for r in a if 'Provision' in r[0]),None);rh_=next((r for r in h if 'Provision' in r[0]),None)
            if 'Provision' in ''.join(seen[:-1]): continue
            lab='− Partner-Provision'
        if not ra_ and not rh_: continue
        cls='tot' if lab.startswith('=') else ''
        if lab=='= Dein Gewinn': cls='win'
        def cell(r):
            if not r: return '<td>—</td>'
            v=r[1];note=r[2] if r[2] not in ('b','B') else ''
            if 'Provision' in r[0]: note=r[0].replace('− Partner-Provision ','')+' '+note
            return f'<td><b>{eu(v)}</b>'+(f'<small>{note}</small>' if note else '')+'</td>'
        trs+=f'<tr class="{cls}"><th>{lab}</th>{cell(ra_)}{cell(rh_)}</tr>'
    d=rh-ra;dk=ch-ca
    verdict=(f'<span class="up">+{eu(d)}</span> mehr Gewinn mit Halloween' if d>0.005 else f'<span class="dn">{eu(d)}</span> weniger Gewinn pro Bestellung – dafür bekommt der Kunde {eu(-dk)} Rabatt' if dk<0 else f'<span class="dn">{eu(d)}</span>')
    if dk>0.005: verdict=f'<span class="up">+{eu(d)}</span> mehr Gewinn – aber der Kunde zahlt {eu(dk)} <u>mehr</u> als sonst (Partner-Rabatt fällt weg)'
    cards.append(f'<div class="c"><h2><span class="no">{i}</span>{t}</h2><table><tr class="hd"><th></th><th>Normal (mit Rabatten)</th><th>🎃 Halloween</th></tr>{trs}</table><p class="v">{verdict}</p></div>')
    summ.append((t,ca,ch,ra,rh,d))
srows=''.join(f'<tr><td>{t}</td><td>{eu(ca)}</td><td>{eu(ch)}</td><td>{eu(ra)}</td><td>{eu(rh)}</td><td class="{"up" if d>0 else "dn"}">{"+" if d>0 else ""}{eu(d)}</td></tr>' for t,ca,ch,ra,rh,d in summ)
html=f'''<!doctype html><html><head><meta charset="utf-8"><style>
body{{margin:0;background:#fff;font-family:Arial,sans-serif;color:#111;width:960px}}.w{{padding:34px 40px}}
h1{{font-size:26px;margin:0 0 6px}}.s{{color:#555;font-size:14px;margin:0 0 6px;line-height:1.5}}
.ek{{background:#f4f4f4;border-radius:10px;padding:12px 16px;font-size:13.5px;line-height:1.7;margin:14px 0 22px}}
.c{{border:1px solid #ddd;border-radius:14px;padding:18px 20px;margin:0 0 18px;page-break-inside:avoid}}
h2{{font-size:17px;margin:0 0 12px;display:flex;align-items:center;gap:10px}}.no{{background:#111;color:#fff;border-radius:50%;width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;font-size:13px}}
table{{border-collapse:collapse;width:100%;font-size:14px;table-layout:fixed}}.sum{{table-layout:auto}}th,td{{padding:7px 10px;border-bottom:1px solid #eee;text-align:right;vertical-align:top}}
th:first-child{{text-align:left;font-weight:normal;color:#333;width:38%}}tr.hd th{{background:#111;color:#fff;font-weight:700;text-align:right}}tr.hd th:last-child{{background:#B4530A}}
td small{{display:block;color:#888;font-size:11.5px;font-weight:normal;margin-top:2px}}
tr.tot th,tr.tot td{{background:#fafafa}}tr.tot th{{font-weight:700;color:#111}}
tr.win th,tr.win td{{background:#FFF3E8;font-size:16px}}tr.win th{{font-weight:700;color:#111}}
.v{{margin:10px 0 0;font-size:14.5px}}.up{{color:#0a7a2f;font-weight:700}}.dn{{color:#B00020;font-weight:700}}
.sum td,.sum th{{font-size:13.5px}}.sum th{{background:#111;color:#fff;text-align:right}}.sum th:first-child{{text-align:left}}.sum td:first-child{{text-align:left}}
.n{{font-size:12.5px;color:#555;line-height:1.6;margin-top:14px}}
</style></head><body><div class="w">
<h1>🎃 Halloween vs. normal – was bleibt pro Bestellung übrig?</h1>
<p class="s">Gleiche Bestellung, einmal zu normalen Konditionen (Mengenrabatt + ggf. Partner-Rabatt) und einmal in der Halloween-Aktion (jede 3. Flasche gratis, keine anderen Rabatte auf Aktions-Flaschen, Partner +5 % Provision).</p>
<div class="ek"><b>Einkaufspreise (EK) pro Flasche:</b> Retatrutide 10 mg 9,50 $ = <b>8,46 €</b> · GHK-Cu 50 mg 3,30 $ = <b>2,94 €</b> (Kurs 0,8903)<br>
<b>Verkaufspreise:</b> Retatrutide 10 mg 74,90 € · GHK-Cu 50 mg 39,90 €<br>
<b>Kosten pro Paket:</b> DHL 6,19 € + Verpackung 0,20 € · <b>Partner-Provision:</b> normal 20 %, Halloween 25 % (vom Betrag, den der Kunde für die Ware zahlt)<br>
<b>Geschenke</b> (nach Warenwert): ab 200 € Injektionskit (EK 4,03 €) · ab 350 € GHK-Cu 50 mg · ab 500 € Retatrutide 10 mg</div>
<div class="c"><h2>Auf einen Blick</h2><table class="sum"><tr><th>Bestellung</th><th>Kunde zahlt normal</th><th>Kunde zahlt Halloween</th><th>Gewinn normal</th><th>Gewinn Halloween</th><th>Unterschied</th></tr>{srows}</table></div>
{''.join(cards)}
<p class="n"><b>So liest man das:</b> „Gewinn“ = was nach Einkauf, Geschenk, Partner-Provision, DHL und Verpackung übrig bleibt – vor Fixkosten und Werbung. Ein Minus pro Bestellung ist der Preis für den Kaufanreiz: Es lohnt sich, sobald dadurch zusätzliche Bestellungen kommen oder Kunden mehr Flaschen nehmen (z. B. Szenario 1 → 3: aus 1 Flasche mit ~46 € Gewinn wird eine Bestellung mit ~85 € Gewinn).</p>
</div></body></html>'''
open('vergleich.html','w').write(html)
for t,ca,ch,ra,rh,d in summ: print(t,round(ca,2),round(ch,2),round(ra,2),round(rh,2),round(d,2))
