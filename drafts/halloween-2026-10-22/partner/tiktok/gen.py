# TikTok-Story-Vorlagen für Partner (1080x1920). Safe Zone: oben ~160, unten ~420 (Caption/Name), rechts ~140 (Buttons).
LOGO='https://peptrium.com/wp-content/uploads/2026/08/IMG_2644-cropped-300x300.png'
BAT='<svg class="bat" style="{s}" viewBox="0 0 24 12"><path d="M0 4C3 1 6 1 8 3.5 9 1.5 10.5 1 12 2.6 13.5 1 15 1.5 16 3.5 18 1 21 1 24 4 21.5 4 19.6 5 18.6 7 16.8 6 14.6 6.4 12 9 9.4 6.4 7.2 6 5.4 7 4.4 5 2.5 4 0 4Z"/></svg>'
CSS='''*{box-sizing:border-box;margin:0;padding:0}
body{width:1080px;height:1920px;overflow:hidden;font-family:Sora,sans-serif;color:#fff;background:#050403;position:relative}
.glow{position:absolute;inset:0;background:radial-gradient(60% 30% at 50% 58%,rgba(255,110,20,.42),transparent 70%),radial-gradient(50% 25% at 15% 20%,rgba(120,40,160,.25),transparent 70%),linear-gradient(180deg,#050403,#0b0705 60%,#050403)}
.web{position:absolute;right:-40px;top:-40px;width:420px;opacity:.18}
.bat{position:absolute;fill:#FFB547;opacity:.35}
.top{position:absolute;left:80px;top:170px;display:flex;align-items:center;gap:16px}.top img{width:64px}.top b{font-size:26px;letter-spacing:.32em}
.pill{display:inline-block;font-size:26px;font-weight:800;letter-spacing:.16em;color:#FFB547;border:2px solid rgba(255,122,26,.6);background:rgba(20,10,2,.6);border-radius:99px;padding:14px 26px}
h1{font-size:104px;line-height:1;font-weight:800;letter-spacing:-.03em}
.o{background:linear-gradient(90deg,#FF7A1A,#FFB547);-webkit-background-clip:text;background-clip:text;color:transparent}
.sub{font-size:34px;color:#E7D6C6;line-height:1.4}
.vials{position:absolute;left:0;right:0;display:flex;justify-content:center;align-items:flex-end}
.vials img{display:block;filter:drop-shadow(0 30px 40px rgba(0,0,0,.7))}
.dim{filter:brightness(.55) drop-shadow(0 30px 40px rgba(0,0,0,.7))!important}
.free{position:relative}
.free img{filter:drop-shadow(0 0 60px rgba(255,122,26,.75)) drop-shadow(0 30px 40px rgba(0,0,0,.7))!important}
.stamp{position:absolute;top:38%;left:50%;transform:translate(-50%,-50%) rotate(-12deg);font-size:46px;font-weight:800;letter-spacing:.08em;color:#140A02;background:linear-gradient(90deg,#FF7A1A,#FFB547);padding:12px 26px;border-radius:14px;box-shadow:0 10px 40px rgba(255,122,26,.6)}
.cta{position:absolute;left:80px;right:160px;bottom:430px;background:rgba(14,10,8,.88);border:2px solid rgba(255,122,26,.55);border-radius:30px;padding:30px 34px}
.cta .l1{font-size:40px;font-weight:800}
.cta .l2{font-size:28px;color:#E7D6C6;margin-top:10px}
.code{margin-top:20px;border:3px dashed rgba(255,181,71,.8);border-radius:18px;padding:18px 22px;font-size:30px;font-weight:700;color:#FFD9B0}
.foot{position:absolute;left:80px;right:160px;bottom:380px;font-size:20px;color:#8A7D72;text-align:left}
.steps{display:flex;flex-direction:column;gap:22px}
.st{display:flex;gap:22px;align-items:center;font-size:36px;font-weight:700}
.st i{font-style:normal;flex:0 0 72px;height:72px;border-radius:50%;background:linear-gradient(90deg,#FF7A1A,#FFB547);color:#140A02;font-weight:800;display:flex;align-items:center;justify-content:center;font-size:34px}
.chips{display:flex;flex-wrap:wrap;gap:14px}.chips span{font-size:28px;font-weight:700;border:2px solid rgba(255,255,255,.25);border-radius:99px;padding:10px 22px;background:rgba(255,255,255,.06)}
.chips span.n{border-color:rgba(255,181,71,.8);color:#FFB547}
'''
WEB='<svg class="web" viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".6"><path d="M100 0 0 100M100 0 30 100M100 0 60 100M100 0 0 70M100 0 0 40"/><path d="M80 0q5 15 20 20M60 0q8 30 40 40M40 0q10 45 60 60M20 0q12 60 80 80"/></svg>'
FOOT='<div class="foot">Nur für Laborforschung · nicht für den menschlichen Gebrauch</div>'
def page(body):
    return ('<!doctype html><html lang="de"><head><meta charset="utf-8"><link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet"><style>'
            + CSS + '</style></head><body><div class="glow"></div>' + WEB
            + BAT.format(s='width:130px;left:110px;top:1000px') + BAT.format(s='width:80px;left:820px;top:430px;opacity:.25')
            + f'<div class="top"><img src="{LOGO}"><b>PEPTRIUM</b></div>' + body + FOOT + '</body></html>')
def vials(names, free_idx, top, h=560, gap=-30):
    out=f'<div class="vials" style="top:{top}px;gap:{gap}px">'
    for i,n in enumerate(names):
        hh = h if i==free_idx else int(h*0.86)
        if i==free_idx: out+=f'<div class="free"><img src="img/{n}.png" style="height:{hh}px"><div class="stamp">GRATIS</div></div>'
        else: out+=f'<img class="{"dim" if free_idx is not None else ""}" src="img/{n}.png" style="height:{hh}px">'
    return out+'</div>'
CODE='<div class="code">Mein Code: ____________</div>'
P={}
P['1-drei-fuer-zwei']=page(
 '<div style="position:absolute;left:80px;right:160px;top:290px"><div class="pill">🎃 HALLOWEEN BEI PEPTRIUM</div>'
 '<h1 style="margin-top:30px">Die 3. Flasche<br><span class="o">geht aufs Haus.</span></h1></div>'
 + vials(['reta','ghk','mots'],2,700,h=480)
 + '<div class="cta"><div class="l1">👉 Link in meiner Bio</div><div class="l2">Fr 30.10. – So 1.11. · solange der Vorrat reicht</div>'+CODE+'</div>')
P['2-insider-zuerst']=page(
 '<div style="position:absolute;left:80px;right:160px;top:290px"><div class="pill">🎃 DONNERSTAG 18 UHR</div>'
 '<h1 style="margin-top:30px;font-size:96px">Sei <span class="o">früher</span><br>dabei als alle.</h1>'
 '<div class="sub" style="margin-top:26px">Jede 3. Flasche gratis – Newsletter-Insider kaufen schon Do 18 Uhr.</div>'
 '<div class="steps" style="margin-top:56px"><div class="st"><i>1</i>Link in meiner Bio öffnen</div><div class="st"><i>2</i>Für den Newsletter eintragen</div><div class="st"><i>3</i>Do 18 Uhr zuerst bestellen</div></div></div>'
 + vials(['cagri','reta','glow'],None,1010,h=320,gap=0)
 + '<div class="cta"><div class="l1">👉 Jetzt über meinen Link eintragen</div><div class="l2">Für alle anderen erst ab Fr 0 Uhr</div></div>')
P['3-neu-im-shop']=page(
 '<div style="position:absolute;left:80px;right:160px;top:290px"><div class="pill">✨ NEU IM SHOP</div>'
 '<h1 style="margin-top:30px;font-size:96px">3 neue Peptide<br><span class="o">+ BPC-157 ist zurück.</span></h1>'
 '<div class="chips" style="margin-top:34px"><span class="n">Cagrilintid</span><span class="n">MT-2</span><span class="n">GLOW</span><span>BPC-157</span></div></div>'
 + vials(['cagri','glow','mt2'],None,800,h=430,gap=20)
 + '<div class="cta"><div class="l1">👉 Ab Do 18 Uhr · Link in Bio</div><div class="l2">Alle zählen bei „jede 3. Flasche gratis“ 🎃</div>'+CODE+'</div>')
P['4-letzte-chance']=page(
 '<div style="position:absolute;left:80px;right:160px;top:290px"><div class="pill">⏳ LETZTE CHANCE</div>'
 '<h1 style="margin-top:30px">Nur noch<br><span class="o">bis heute<br>23:59 Uhr.</span></h1>'
 '<div class="sub" style="margin-top:26px">Danach kostet jede Flasche wieder den normalen Preis.</div></div>'
 + vials(['bpc','reta','glow'],1,850,h=400)
 + '<div class="cta"><div class="l1">👉 Link in meiner Bio</div><div class="l2">Jede 3. Flasche gratis · solange der Vorrat reicht</div>'+CODE+'</div>')
for k,v in P.items(): open(k+'.html','w').write(v); print(k)
