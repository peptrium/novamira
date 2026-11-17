import re
a=open('a2.html').read()
scene=re.search(r'(<div class="beam"></div>.*?)<div class="txt">',a,re.S).group(1)
style=re.search(r'<style>(.*?)</style>',a,re.S).group(1)
def eur(x): return f"{x:,.2f} €".replace(',','X').replace('.',',').replace('X','.')
CSS=style+'''
html,body{overflow-x:hidden}body{height:auto;min-height:844px;background:#0A0B0D;overflow-y:visible}
.inbox{background:#fff;color:#111;padding:14px 16px 12px;border-bottom:1px solid #E5E5E5}
.inbox .f{display:flex;justify-content:space-between;font:600 14px/1.2 Inter}.inbox .f span{font:400 12px Inter;color:#8A8F96}
.inbox .s{font:700 15px/1.3 Inter;margin-top:4px}.inbox .p{font:400 13px/1.35 Inter;color:#6B7076;margin-top:2px}
.m{background:#000;position:relative}
.ml{text-align:center;padding:22px 0 0}.ml img{width:40px}
.sc2{position:relative;height:560px;overflow:hidden;background:radial-gradient(70% 45% at 50% 0%,#1A1C20,#000 70%)}
.sc2 .txt{top:22px}.sc2 .vw,.sc2 .beam,.sc2 .pool,.sc2 .floor,.sc2 .hz{transform:translateY(-60px)}
.sc2.sm{height:300px}.sc2.sm .vw,.sc2.sm .beam,.sc2.sm .pool,.sc2.sm .floor,.sc2.sm .hz{display:none}
.sc2 .big2{font:800 48px/1 Sora;letter-spacing:-.05em;margin:14px 0 8px}
.bd{padding:6px 24px 30px;text-align:center}
.bd p{font:400 14.5px/1.6 Inter;color:#B8BDC4;margin:0 0 18px}.bd p b{color:#fff;font-weight:600}
.pl{border-top:1px solid rgba(255,255,255,.1);margin:6px 0 4px}
.pr{display:flex;justify-content:space-between;align-items:baseline;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.07);font:500 14px Inter;color:#fff;text-align:left}
.pr span{color:#8A9098;font-size:12.5px}.pr b{font:700 15px Sora}.pr s{color:#6E747B;font-size:12px;margin-right:6px}
.h2{font:600 10px/1 Inter;letter-spacing:.42em;color:#8A9098;margin:26px 0 14px}
.cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-bottom:22px;text-align:left}
.card{min-width:0;overflow:hidden;background:radial-gradient(90% 70% at 50% 0%,#1C1E22,#0B0C0E 75%);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:14px 12px 12px;display:flex;flex-direction:column;gap:6px}
.card .im{height:118px;overflow:hidden;display:flex;align-items:flex-end;justify-content:center;gap:2px}.card .im img{height:100%;width:auto;filter:drop-shadow(0 8px 10px rgba(0,0,0,.7))}
.card .im img.s{height:72%}.card .im img.penimg{height:auto;width:112%;max-width:none;transform:rotate(-14deg);margin:0 -6% 34px}
.card b{font:600 13.5px/1.25 Inter;color:#fff}.card span{font:400 11.5px/1.35 Inter;color:#8A9098}
.card .pp{font:700 15px Sora;color:#fff;margin-top:auto}.card .pp s{font:400 11.5px Inter;color:#6E747B;margin-left:5px}
.cd{display:flex;justify-content:center;gap:8px;margin:2px 0 22px}
.cd div{width:64px;border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px 0 8px;background:#0C0D0F}
.cd b{display:block;font:700 24px/1 Sora;color:#fff;font-variant-numeric:tabular-nums}.cd span{font:600 8.5px/1 Inter;letter-spacing:.16em;color:#7B8188}
.tl{display:flex;margin:4px 0 24px;text-align:center}
.tl div{flex:1;font:400 11.5px/1.35 Inter;color:#7B8188;position:relative;padding-top:18px}
.tl div:before{content:"";position:absolute;top:0;left:50%;width:9px;height:9px;margin-left:-4.5px;border-radius:50%;background:#33363B}
.tl div:after{content:"";position:absolute;top:4px;left:calc(50% + 8px);right:calc(-50% + 8px);height:1px;background:#2A2D31}
.tl div:last-child:after{display:none}
.tl div.on{color:#fff}.tl div.on:before{background:#fff;box-shadow:0 0 12px #fff}
.tl b{display:block;font:600 12.5px Inter;color:inherit;margin-bottom:2px}
.ck{text-align:left;margin:0 0 22px;display:grid;gap:10px}.ck div{display:flex;gap:10px;font:400 14px/1.45 Inter;color:#C9CDD2}.ck div:before{content:"";flex:0 0 18px;height:18px;border-radius:50%;border:1px solid rgba(255,255,255,.4);background:radial-gradient(circle,#fff 0 3px,transparent 3.5px);margin-top:1px}
.ft{font:400 11px/1.5 Inter;color:#5F656C;text-align:center;padding:18px 24px 30px;border-top:1px solid rgba(255,255,255,.07)}
.ps{font:400 12.5px/1.5 Inter;color:#7B8188;margin-top:18px}
'''
def mail(subj,pre,hero,body,cta,time='18:00'):
    return f'''<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="base.css"><style>{CSS}</style></head><body>
<div class="inbox"><div class="f">Peptrium <span>{time}</span></div><div class="s">{subj}</div><div class="p">{pre}</div></div>
<div class="m"><div class="ml"><img src="logo.png"></div>{hero}
<div class="bd">{body}<div class="cta">{cta}<svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div></div>
<div class="ft">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Abmelden · Impressum</div></div>
<script src="vial.js"></script></body></html>'''
def hero_scene(ey,pct,l1,l2,bright=False):
    sc=scene.replace('rgba(235,240,250,.20)','rgba(240,244,252,.30)') if bright else scene
    return f'<div class="sc2">{sc}<div class="txt"><div class="ey">{ey}</div><div class="big chrome">−{pct}<sup>%</sup></div><div class="l1">{l1}</div><div class="l2">{l2}</div></div><div class="grain"></div></div>'
def hero_text(ey,big,l1,l2):
    return f'<div class="sc2 sm"><div class="beam" style="display:block;transform:translateX(-50%)"></div><div class="txt" style="top:44px"><div class="ey">{ey}</div><div class="big2 chrome">{big}</div><div class="l1">{l1}</div><div class="l2">{l2}</div></div><div class="grain"></div></div>'
def plist(f):
    rows=[('Retatrutide','10 mg',79.90),('Retatrutide','20 mg',149.90),('GHK-Cu','50 mg',39.90),('GLOW','70 mg',139.90),('Peptrium-Pen','Retatrutide 20 mg',264.90)]
    return '<div class="pl"></div>'+''.join(f'<div class="pr"><div>{n}<br><span>{v}</span></div><div><s>{eur(p)}</s><b>{eur(p*f)}</b></div></div>' for n,v,p in rows)
def sets(f):
    S=[('Retatrutide Starter-Set','Retatrutide 10 mg, Bac Water, Spritzen',99.70,'<img src="reta.png">'),
       ('Retatrutide Pen-Set','Pen 20 mg + Pen Nadeln',274.80,'<img class="penimg" src="pen.png">'),
       ('GLOW','GHK-Cu + BPC-157 + TB-500',139.90,'<img src="glow.png">'),
       ('GHK-Cu Komplett','GHK-Cu 50 mg, Bac Water, Spritzen',59.70,'<img src="ghk.png">')]
    return '<div class="cards">'+''.join(f'<div class="card"><div class="im">{im}</div><b>{n}</b><span>{d}</span><div class="pp">{eur(p*f)}<s>{eur(p)}</s></div></div>' for n,d,p,im in S)+'</div>'
def cd(d,h,m,s): return '<div class="cd">'+''.join(f'<div><b>{v}</b><span>{l}</span></div>' for v,l in [(d,'TAGE'),(h,'STD'),(m,'MIN'),(s,'SEK')])+'</div>'
def hero_pen(ey,big,l1,l2):
    return f'<div class="sc2" style="height:520px"><div class="beam" style="transform:translateX(-50%)"></div><div class="pool" style="top:420px;transform:translateX(-50%)"></div><div class="hz" style="top:470px"></div><div class="txt"><div class="ey">{ey}</div><div class="big2 chrome">{big}</div><div class="l1">{l1}</div><div class="l2">{l2}</div></div><img src="pen.png" style="position:absolute;left:50%;top:300px;width:300px;margin-left:-150px;transform:rotate(-26deg);filter:drop-shadow(0 26px 22px rgba(0,0,0,.85));z-index:5"><img src="pen.png" style="position:absolute;left:50%;top:452px;width:300px;margin-left:-150px;transform:scaleY(-1) rotate(26deg);opacity:.12;filter:blur(2px);-webkit-mask-image:linear-gradient(0deg,transparent 40%,#000 85%);z-index:4"><div class="grain"></div></div>'
def penlist(f):
    rows=[('Peptrium-Pen Retatrutide','20 mg',264.90),('Peptrium-Pen Retatrutide','30 mg',None),('Peptrium-Pen GHK-Cu','50 mg',None),('Peptrium-Pen MOTS-c','20 mg',None)]
    return ''
M={}
M['1-start']=mail('Black Week: −30 % auf alles','Peptide, Pens und Zubehör – automatisch reduziert.',
 hero_scene('BLACK WEEK',30,'auf das gesamte Sortiment.','Bis Donnerstag, 26. November'),
 '<p>Ab heute ist <b>jedes Produkt 30 % günstiger</b>. Ohne Code, direkt im Preis. Die Geschenke ab 200 € gibt es weiterhin dazu.</p>'+plist(.7)+'<p class="ps">Und Freitag? Wird es noch besser. Du erfährst es zuerst.</p>','Zum Shop','00:05')
M['1b-pen']=mail('Halbzeit: der Pen, jetzt −30 %','Fertig gemischt, kein Anmischen – noch bis Donnerstag.',
 hero_pen('BLACK WEEK · HALBZEIT','Der Pen.','Fertig gemischt. Jetzt −30 %.','Noch bis Donnerstag, 26. November'),
 '<p>Kein Bac Water, kein Anmischen: <b>der Peptrium-Pen kommt fertig gemischt</b>. In der Black Week ist auch er <b>30 % günstiger</b>.</p><div class="pl"></div>'
 +''.join(f'<div class="pr"><div>{n}<br><span>{v}</span></div><div><s>{eur(p)}</s><b>{eur(p*.7)}</b></div></div>' for n,v,p in [('Pen Retatrutide','20 mg',264.90),('Pen Retatrutide','40 mg',339.90),('Pen GHK-Cu','50 mg',169.90),('Pen MOTS-c','20 mg',189.90)])
 +'<div class="ck" style="margin-top:20px"><div>Pen Nadeln gleich mitbestellen – auch −30 %</div><div>Ab 200 € gibt es das Injektionskit gratis dazu</div><div>Versand in 2 Werktagen, diskret verpackt</div></div>','Pens ansehen','18:00')
M['2-favoriten']=mail('Die Favoriten, jetzt 30 % günstiger','Starter-Set, Pen-Set und GLOW – fertig zusammengestellt.',
 hero_text('BLACK WEEK · SAMSTAG','Die Favoriten.','Fertig zusammengestellt, jetzt −30 %.','Noch bis Donnerstag, 26. November'),
 '<p>Die meisten bestellen nicht einzeln, sondern <b>alles, was zusammengehört</b>. Hier die beliebtesten Kombinationen, schon reduziert.</p>'+sets(.7)+'<p>Ab 150 € versandkostenfrei, ab 200 € gibt es das Injektionskit gratis dazu.</p>','Sets ansehen','11:00')
M['3-teaser']=mail('Morgen 18 Uhr: dein früher Zugang','Black Friday startet für dich einen Abend vor allen anderen.',
 hero_text('NUR FÜR INSIDER','Morgen, 18 Uhr.','Black Friday – für dich zuerst.','Donnerstag, 26. November'),
 '<p>Freitag steigt der Rabatt. <b>Du bekommst ihn schon Donnerstag um 18 Uhr</b> – per Mail, mit deinem persönlichen Link.</p><div class="tl"><div class="on"><b>Bis Do</b>−30 % für alle</div><div class="on"><b>Do 18 Uhr</b>du zuerst</div><div><b>Fr–Mo</b>für alle</div></div><p>Tipp: Leg dir heute schon in den Warenkorb, was du willst. Dann geht es morgen in einer Minute.</p>','Warenkorb vorbereiten','18:00')
M['4-insider']=mail('Für dich schon heute: −35 % 🖤','Black Friday startet für alle erst morgen.',
 hero_scene('NUR FÜR INSIDER',35,'Black Friday – einen Abend früher.','Ab heute 18 Uhr · bis Cyber Monday, 30.11.',True),
 '<p>Du bist im Newsletter, also bist du <b>einen Abend früher dran</b>. Ab sofort gilt für dich <b>−35 % auf alles</b>.</p>'+plist(.65)+'<p class="ps">Bestseller sind am Freitagmorgen erfahrungsgemäß zuerst weg.</p>','Jetzt −35 % sichern','18:00')
M['5-blackfriday']=mail('Black Friday: −35 % auf alles','Der größte Rabatt des Jahres. Bis Montag.',
 hero_scene('BLACK FRIDAY',35,'Der Höhepunkt der Black Week.','Nur bis Cyber Monday, 30. November · 23:59',True),
 '<p>Ab jetzt <b>−35 % auf das gesamte Sortiment</b> – Peptide, Pens und Zubehör. Ohne Code, direkt im Preis.</p>'+sets(.65)+'<div class="ck"><div>Versand in 2 Werktagen, diskret verpackt</div><div>Geschenke ab 200 € bleiben</div><div>Bei Vorkasse zählt das Bestelldatum</div></div>','Zum Black Friday','08:00')
M['6-sonntag']=mail('Noch bis Montag: −35 %','Falls du noch überlegst – hier das Wichtigste auf einen Blick.',
 hero_text('BLACK FRIDAY · SONNTAG','Noch 1 Tag.','−35 % auf alles, bis Montag 23:59.','Danach gelten wieder die normalen Preise.'),
 cd('01','12','47','08')+'<p>Ein kurzer Überblick, was du <b>im Vergleich zu sonst</b> sparst:</p>'+plist(.65)+'<p class="ps">Fragen vor der Bestellung? Schreib uns auf Telegram, wir antworten schnell.</p>','Jetzt bestellen','11:00')
M['7-letzte']=mail('Heute 23:59 ist Schluss','Letzter Tag: −35 % auf alles.',
 hero_text('CYBER MONDAY · LETZTER TAG','Letzter Tag.','−35 % nur noch bis 23:59.','Danach gelten wieder die normalen Preise.'),
 cd('00','13','59','12')+'<p>Das war die Black Week 2026. Wenn du noch etwas brauchst: <b>heute ist der letzte Tag</b>.</p>'+sets(.65)+'<div class="ck"><div>Es zählt deine Bestellung, nicht der Zahlungseingang</div><div>Versand in 2 Werktagen</div></div>','Letzte Chance nutzen','10:00')
for k,v in M.items(): open(f'mail-{k}.html','w').write(v)
print(list(M))
