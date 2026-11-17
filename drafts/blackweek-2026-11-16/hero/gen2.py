import re
exec(open('gen_mails.py').read().split("M={}\nM['1-start']")[0])  # helpers: eur, CSS, mail, vial, stage, sets, plist, cd, scene
VJS='<script src="vial.js"></script>'
BASE='''<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="base.css"><style>
html,body{overflow-x:hidden}body{height:auto;background:#fff;overflow-y:visible}
.inbox{background:#fff;color:#111;padding:14px 16px 12px;border-bottom:1px solid #E5E5E5}
.inbox .f{display:flex;justify-content:space-between;font:600 14px/1.2 Inter}.inbox .f span{font:400 12px Inter;color:#8A8F96}
.inbox .s{font:700 15px/1.3 Inter;margin-top:4px}.inbox .p{font:400 13px/1.35 Inter;color:#6B7076;margin-top:2px}
.m{position:relative;overflow:hidden}
.vw{position:absolute}.vw img{display:block;width:100%;position:relative;z-index:2}
.vw .rim{position:absolute;inset:0;z-index:3;-webkit-mask-size:100% 100%;mask-size:100% 100%;mix-blend-mode:screen}
.vw .shadow{position:absolute;left:-20%;right:-20%;bottom:-3%;height:9%;background:radial-gradient(50% 50% at 50% 50%,rgba(0,0,0,.6),transparent 70%);z-index:1}
.vw .refl{position:absolute;left:0;right:0;top:100%;height:40%;overflow:hidden;z-index:1;opacity:.2;-webkit-mask-image:linear-gradient(180deg,#000,transparent 75%)}
.vw .refl img{transform:scaleY(-1);filter:blur(1.5px)}
.chrome{background:linear-gradient(180deg,#FFFFFF 0%,#F1F3F5 26%,#A3A9B1 48%,#5F656D 53%,#D9DDE1 68%,#FFFFFF 82%,#9AA0A8 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.steel{background:linear-gradient(180deg,#2A2D33 0%,#5A6068 40%,#0E1013 55%,#3B4047 80%,#16181C 100%);-webkit-background-clip:text;background-clip:text;color:transparent}
.btn{width:100%;box-sizing:border-box;display:flex;align-items:center;justify-content:center;gap:8px;height:54px;border-radius:999px;font:700 15.5px/1 Inter}
.btn svg{width:16px;height:16px;fill:none;stroke:currentColor;stroke-width:2.2}
.bw{background:#fff;color:#000}.bb{background:#07080A;color:#fff}
.ft{font:400 11px/1.5 Inter;text-align:center;padding:18px 24px 30px}
.grain{position:absolute;inset:0;z-index:50;pointer-events:none;opacity:.08;mix-blend-mode:overlay;background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='.95' numOctaves='2' stitchTiles='stitch'/></filter><rect width='100%' height='100%' filter='url(%23n)'/></svg>")}
.hd{height:auto}
STYLE
</style></head><body>
<div class="inbox"><div class="f">Peptrium <span>TIME</span></div><div class="s">SUBJ</div><div class="p">PRE</div></div>
<div class="m">BODY</div>VJS</body></html>'''
ARW='<svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
def page(subj,pre,time,style,body):
    return BASE.replace('STYLE',style).replace('TIME',time).replace('SUBJ',subj).replace('PRE',pre).replace('BODY',body).replace('VJS',VJS)
def v(src,l,t,w,extra='',rim='both',col='215,225,240',top=False):
    return f'<div class="vw" data-src="{src}" data-rim="{rim}" data-color="{col}" {"data-top=1" if top else ""} style="left:{l}px;top:{t}px;width:{w}px;{extra}"></div>'
P={}
# ---------- Mail 2: Pen – HELL, Studio ----------
P['2-pen']=page('Halbzeit: der Pen, jetzt −30 %','Fertig gemischt, kein Anmischen – noch bis Donnerstag.','18:00','''
.m{background:#F3F3F1;color:#0B0C0E}
.hd{display:flex;justify-content:space-between;align-items:center;padding:20px 22px 0;font:600 10px/1 Inter;letter-spacing:.3em;color:#7A8088}.hd img{width:28px;filter:invert(1) brightness(.2)}
.h{padding:30px 22px 0}.h .k{font:600 11px/1 Inter;letter-spacing:.3em;color:#7A8088}
.h h1{font:800 76px/.86 Sora;letter-spacing:-.06em;margin:14px 0 0;color:#0B0C0E}
.h h1 em{font-style:normal;color:#9AA0A8}
.stg{position:relative;height:250px;margin-top:10px}
.stg .sh{position:absolute;left:30px;right:30px;top:178px;height:30px;background:radial-gradient(50% 50% at 50% 50%,rgba(0,0,0,.28),transparent 70%)}
.stg img{position:absolute;left:22px;top:92px;width:346px;transform:rotate(-9deg);filter:drop-shadow(0 26px 24px rgba(0,0,0,.28))}
.tag{position:absolute;right:22px;top:20px;width:92px;height:92px;border-radius:50%;background:#0B0C0E;color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;font:800 26px/1 Sora;letter-spacing:-.04em;transform:rotate(8deg)}
.tag small{font:600 8.5px/1 Inter;letter-spacing:.2em;margin-top:4px;color:#9AA0A8}
.tx{padding:6px 22px 0;font:400 15px/1.6 Inter;color:#3A3F46}.tx b{color:#0B0C0E}
.rows{margin:20px 22px 0;border-top:1px solid #DADBD8}
.r{display:grid;grid-template-columns:1fr auto;padding:14px 0;border-bottom:1px solid #DADBD8;align-items:baseline}
.r b{font:600 15px Inter}.r span{display:block;font:400 12.5px Inter;color:#7A8088}.r s{font:400 12px Inter;color:#9AA0A8;margin-right:8px}.r i{font:800 18px Sora;font-style:normal}
.cta{padding:24px 22px 0}.ft{color:#8A9098}
''','<div class="hd"><img src="logo.png"><span>BLACK WEEK · HALBZEIT</span></div><div class="h"><div class="k">FERTIG GEMISCHT</div><h1>Der<br>Pen.<em> −30%</em></h1></div><div class="stg"><div class="sh"></div><img src="pen-h.png"></div><div class="tx">Kein Bac Water, kein Anmischen: <b>der Peptrium-Pen kommt fertig gemischt</b>. Diese Woche 30 % günstiger.</div><div class="rows">'+''.join(f'<div class="r"><div><b>{n}</b><span>{x}</span></div><div><s>{eur(p)}</s><i>{eur(p*.7)}</i></div></div>' for n,x,p in [('Pen Retatrutide','20 mg',264.90),('Pen Retatrutide','40 mg',339.90),('Pen GHK-Cu','50 mg',169.90),('Pen MOTS-c','20 mg',189.90)])+'</div><div class="cta"><div class="btn bb">Pens ansehen'+ARW+'</div></div><div class="ft">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Abmelden · Impressum</div>')
# ---------- Mail 3: Favoriten – Katalog/Lookbook ----------
def tile(img,n,d,p,f,big=False,pen=False):
    im=f'<img src="{img}" style="{"width:96%;height:auto;align-self:center;transform:rotate(-12deg)" if pen else "height:100%"}">'
    return f'<div class="t{" big" if big else ""}"><div class="im">{im}</div><div class="nf"><span>{n}</span><small>{d}</small></div><div class="pp"><s>{eur(p)}</s><b>{eur(p*f)}</b></div></div>'
P['3-fav']=page('Die Favoriten, jetzt 30 % günstiger','Starter-Set, Pen-Set und GLOW – fertig zusammengestellt.','11:00','''
.m{background:#16181C;color:#fff}
.hd{padding:34px 20px 16px;display:flex;justify-content:space-between;align-items:flex-end;border-bottom:1px solid rgba(255,255,255,.12)}
.hd h1{font:800 46px/.95 Sora;letter-spacing:-.05em;margin:0;padding-top:4px}.hd .r{text-align:right;font:600 10px/1.5 Inter;letter-spacing:.24em;color:#8A9098}
.hd .r b{display:block;font:800 30px/1 Sora;letter-spacing:-.04em;color:#fff}
.in{padding:14px 20px 0;font:400 14px/1.55 Inter;color:#AEB4BB}
.g{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:16px 20px 0}
.t{background:linear-gradient(180deg,#22252B,#101114);border-radius:16px;padding:14px 12px 12px;min-width:0;overflow:hidden;display:flex;flex-direction:column}
.t.big{grid-column:span 2;display:grid;grid-template-columns:44% 1fr;gap:8px;align-items:center}
.t .im{height:150px;display:flex;align-items:flex-end;justify-content:center;overflow:hidden}.t.big .im{height:190px}
.t .im img{filter:drop-shadow(0 10px 12px rgba(0,0,0,.7))}
.nf span{display:block;font:700 14px/1.25 Inter;margin-top:8px}.nf small{display:block;font:400 11.5px/1.35 Inter;color:#8A9098;margin-top:3px}
.t.big .nf span{font:800 22px/1.05 Sora;letter-spacing:-.03em}
.pp{margin-top:auto;padding-top:10px}.pp s{font:400 11.5px Inter;color:#6E747B;margin-right:6px}.pp b{font:800 17px Sora}
.t.big .pp b{font-size:24px}
.nb{display:flex;gap:8px;padding:18px 20px 0}.nb div{flex:1;border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px;font:500 11.5px/1.35 Inter;color:#AEB4BB}.nb b{display:block;color:#fff;font-size:13px}
.cta{padding:20px 20px 0}.ft{color:#5F656C}
''','<div class="hd"><h1>Die<br>Favoriten.</h1><div class="r">BLACK WEEK<b>−30%</b>BIS DO 26.11.</div></div><div class="in">Die meisten bestellen nicht einzeln, sondern alles, was zusammengehört. Hier die beliebtesten Kombinationen – schon reduziert.</div><div class="g">'
 +tile('reta.png','Retatrutide Starter-Set','Retatrutide 10 mg · Bac Water · Spritzen',99.70,.7,True)
 +tile('pen-h.png','Retatrutide Pen-Set','Pen 20 mg + Pen Nadeln',274.80,.7,pen=True)
 +tile('glow.png','GLOW','GHK-Cu · BPC-157 · TB-500',139.90,.7)
 +tile('ghk.png','GHK-Cu Komplett','GHK-Cu 50 mg · Bac Water · Spritzen',59.70,.7)
 +tile('mt2.png','Melanotan II','10 mg',29.90,.7)
 +'</div><div class="nb"><div><b>Ab 150 €</b>versandkostenfrei</div><div><b>Ab 200 €</b>Injektionskit gratis</div></div><div class="cta"><div class="btn bw">Alle Sets ansehen'+ARW+'</div></div><div class="ft">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Abmelden · Impressum</div>')
# ---------- Mail 4: Teaser – Insider-Ticket ----------
P['4-teaser']=page('Morgen 18 Uhr: dein früher Zugang','Freitag wird größer. Du erfährst es zuerst.','18:00','''
.m{background:#000;color:#fff;padding-bottom:4px}
.lg{text-align:center;padding:26px 0 0}.lg img{width:34px}
.k{text-align:center;font:600 10.5px/1 Inter;letter-spacing:.5em;color:#8A9098;margin-top:22px;padding-left:.5em}
.q{text-align:center;font:800 250px/.8 Sora;letter-spacing:-.08em;margin:10px 0 0}
.l1{text-align:center;font:600 20px/1.25 Inter;margin-top:6px}.l2{text-align:center;font:400 13.5px/1.5 Inter;color:#8A9098;margin:6px 30px 0}
.tk{margin:30px 20px 0;display:flex;border-radius:18px;overflow:hidden;background:linear-gradient(135deg,#F4F5F7,#AEB4BB 45%,#E6E8EB 60%,#8D939A);color:#0B0C0E;position:relative}
.tk .a{flex:1;padding:18px 18px 16px}.tk .a small{font:700 9.5px/1 Inter;letter-spacing:.3em;color:#3A3F46}
.tk .a b{display:block;font:800 30px/1 Sora;letter-spacing:-.04em;margin:10px 0 4px}.tk .a span{font:500 12px Inter;color:#3A3F46}
.tk .s{width:96px;border-left:2px dashed rgba(0,0,0,.35);display:flex;flex-direction:column;align-items:center;justify-content:center;font:800 26px/1 Sora;letter-spacing:-.03em}
.tk .s small{font:700 9px/1 Inter;letter-spacing:.2em;margin-top:6px}
.tk:before,.tk:after{content:"";position:absolute;right:88px;width:16px;height:16px;border-radius:50%;background:#000}.tk:before{top:-8px}.tk:after{bottom:-8px}
.tl{display:flex;margin:30px 20px 0;text-align:center}
.tl div{flex:1;font:400 11.5px/1.35 Inter;color:#7B8188;position:relative;padding-top:18px}
.tl div:before{content:"";position:absolute;top:0;left:50%;width:9px;height:9px;margin-left:-4.5px;border-radius:50%;background:#33363B}
.tl div:after{content:"";position:absolute;top:4px;left:calc(50% + 8px);right:calc(-50% + 8px);height:1px;background:#2A2D31}.tl div:last-child:after{display:none}
.tl div.on{color:#fff}.tl div.on:before{background:#fff;box-shadow:0 0 12px #fff}.tl b{display:block;font:600 12.5px Inter;color:inherit}
.tip{margin:26px 20px 0;font:400 14px/1.55 Inter;color:#AEB4BB;text-align:center}.tip b{color:#fff}
.cta{padding:22px 20px 0}.ft{color:#5F656C}
''','<div class="lg"><img src="logo.png"></div><div class="k">NUR FÜR INSIDER</div><div class="q chrome">?</div><div class="l1">Freitag wird größer.</div><div class="l2">Wie viel genau, erfährst du morgen – einen Abend vor allen anderen.</div><div class="tk"><div class="a"><small>INSIDER-ZUGANG</small><b>Do 26.11.</b><span>Dein Link kommt per Mail</span></div><div class="s">18:00<small>UHR</small></div></div><div class="tl"><div class="on"><b>Bis Do</b>−30 % für alle</div><div class="on"><b>Do 18 Uhr</b>du zuerst</div><div><b>Fr–Mo</b>für alle</div></div><div class="tip">Tipp: Leg heute schon in den Warenkorb, was du willst. <b>Dann geht es morgen in einer Minute.</b></div><div class="cta"><div class="btn bw">Warenkorb vorbereiten'+ARW+'</div></div><div class="ft">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Abmelden · Impressum</div>')
# ---------- Mail 5: Insider – Chrom-Karte (VIP) ----------
P['5-insider']=page('Für dich schon heute: −35 % 🖤','Black Friday startet für alle erst morgen.','18:00','''
.m{background:radial-gradient(90% 50% at 50% 18%,#25282E,#050506 70%);color:#fff}
.lg{text-align:center;padding:26px 0 0}.lg img{width:34px}
.card{margin:34px 26px 0;aspect-ratio:1.586;border-radius:20px;background:linear-gradient(125deg,#FBFBFC 0%,#C9CDD3 18%,#7E848C 38%,#EDEFF1 52%,#9AA0A8 70%,#F4F5F7 100%);color:#0B0C0E;padding:18px 20px;position:relative;box-shadow:0 30px 60px rgba(0,0,0,.7),inset 0 1px 0 rgba(255,255,255,.8);transform:rotate(-4deg)}
.card .top{display:flex;justify-content:space-between;align-items:center;font:700 10px/1 Inter;letter-spacing:.3em}.card .top img{width:30px;filter:brightness(.15)}
.card .n{position:absolute;left:20px;bottom:44px;font:800 64px/.9 Sora;letter-spacing:-.06em}
.card .b{position:absolute;left:20px;right:20px;bottom:16px;display:flex;justify-content:space-between;font:600 10px/1 Inter;letter-spacing:.18em;color:#2A2D33}
.chip{position:absolute;right:22px;bottom:50px;width:42px;height:32px;border-radius:6px;background:linear-gradient(135deg,#E8D9A8,#B89C5A 50%,#E8D9A8)}
.h{text-align:center;margin-top:40px}.h h1{font:800 34px/1.05 Sora;letter-spacing:-.04em;margin:0}.h p{font:400 14px/1.55 Inter;color:#AEB4BB;margin:10px 28px 0}.h p b{color:#fff}
.rows{margin:22px 22px 0;border-top:1px solid rgba(255,255,255,.1)}
.r{display:flex;justify-content:space-between;align-items:baseline;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.07)}
.r b{font:500 14px Inter}.r span{display:block;font:400 12px Inter;color:#8A9098}.r s{font:400 12px Inter;color:#6E747B;margin-right:6px}.r i{font:700 15px Sora;font-style:normal}
.cta{padding:22px 22px 0}.ps{text-align:center;font:400 12.5px/1.5 Inter;color:#7B8188;margin:16px 26px 0}.ft{color:#5F656C}
''','<div class="lg"><img src="logo.png"></div><div class="card"><div class="top"><span>PEPTRIUM INSIDER</span><img src="logo.png"></div><div class="chip"></div><div class="n">−35%</div><div class="b"><span>BLACK FRIDAY</span><span>AB DO 18:00</span></div></div><div class="h"><h1>Du bist früher dran.</h1><p>Black Friday startet für alle erst morgen. <b>Für dich gilt −35 % auf alles schon jetzt.</b></p></div><div class="rows">'+''.join(f'<div class="r"><div><b>{n}</b><span>{x}</span></div><div><s>{eur(p)}</s><i>{eur(p*.65)}</i></div></div>' for n,x,p in [('Retatrutide','10 mg',79.90),('Retatrutide','20 mg',149.90),('GHK-Cu','50 mg',39.90),('GLOW','70 mg',139.90),('Peptrium-Pen Retatrutide','20 mg',264.90)])+'</div><div class="cta"><div class="btn bw">Jetzt −35 % sichern'+ARW+'</div></div><div class="ps">Bestseller sind am Freitagmorgen erfahrungsgemäß zuerst weg.</div><div class="ft">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Abmelden · Impressum</div>')
# ---------- Mail 6: Black Friday – Poster hell ----------
P['6-bf']=page('Black Friday: −35 % auf alles','Der größte Rabatt des Jahres. Bis Montag.','08:00','''
.m{background:#E9EAEC;color:#07080A}
.hd{display:flex;justify-content:space-between;align-items:center;padding:18px 20px;font:700 10px/1 Inter;letter-spacing:.3em}.hd img{width:26px;filter:brightness(.1)}
.p1{font:800 132px/.8 Sora;letter-spacing:-.07em;margin:6px 0 0 12px}
.p2{font:800 64px/.9 Sora;letter-spacing:-.05em;margin:0 0 0 14px}
.p2 em{font-style:normal;color:#9AA0A8}
.stg{position:relative;height:340px;margin-top:14px}
.stg .fl{position:absolute;left:0;right:0;top:250px;bottom:0;background:linear-gradient(180deg,#D6D8DB,#E9EAEC)}
.sub{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:2px solid #07080A;border-bottom:2px solid #07080A;margin:0 20px;font:700 12px/1.2 Inter;letter-spacing:.06em}
.sub b{font:800 16px Sora;letter-spacing:-.01em}
.g{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:#C9CBCF;margin:18px 20px 0;border:1px solid #C9CBCF}
.c{background:#F5F5F6;padding:12px}.c span{display:block;font:600 13px/1.25 Inter}.c small{font:400 11.5px Inter;color:#6B7076}
.c div{margin-top:8px}.c s{font:400 11.5px Inter;color:#9AA0A8;margin-right:6px}.c b{font:800 17px Sora}
.ck{margin:18px 20px 0;font:500 13px/1.8 Inter;color:#3A3F46}
.cta{padding:18px 20px 0}.ft{color:#7A8088}
''','<div class="hd"><img src="logo.png"><span>BLACK FRIDAY · 27.–30.11.</span></div><div class="p1 steel">−35%</div><div class="p2">auf alles.<em><br>Bis Montag.</em></div><div class="stg"><div class="fl"></div>'
 +v('bpc.png',8,96,62,'filter:brightness(.92)','left','255,255,255')+v('mt2.png',320,96,62,'filter:brightness(.92)','right','255,255,255')+v('ghk.png',62,64,80,'','left','255,255,255')+v('glow.png',248,64,80,'','right','255,255,255')+v('reta.png',138,20,114,'','both','255,255,255',True)
 +'</div><div class="sub"><span>KEIN CODE NÖTIG</span><b>Alles im Shop</b></div><div class="g">'
 +''.join(f'<div class="c"><span>{n}</span><small>{x}</small><div><s>{eur(p)}</s><b>{eur(p*.65)}</b></div></div>' for n,x,p in [('Retatrutide','10 mg',79.90),('GHK-Cu','50 mg',39.90),('GLOW','70 mg',139.90),('Pen Retatrutide','20 mg',264.90)])
 +'</div><div class="ck">✓ Versand in 2 Werktagen, diskret verpackt<br>✓ Geschenke ab 200 € bleiben<br>✓ Bei Vorkasse zählt das Bestelldatum</div><div class="cta"><div class="btn bb">Zum Black Friday'+ARW+'</div></div><div class="ft">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Abmelden · Impressum</div>')
# ---------- Mail 7: Sonntag – Flip-Countdown ----------
P['7-so']=page('Noch bis Montag: −35 %','Falls du noch überlegst – hier das Wichtigste auf einen Blick.','11:00','''
.m{background:#0B0C0E;color:#fff}
.lg{text-align:center;padding:26px 0 0}.lg img{width:34px}
.k{text-align:center;font:600 10.5px/1 Inter;letter-spacing:.5em;color:#8A9098;margin-top:24px;padding-left:.5em}
.fl{display:flex;justify-content:center;align-items:center;gap:8px;margin-top:18px}
.d{width:76px;height:104px;border-radius:12px;background:linear-gradient(180deg,#26292F 0 49.6%,#0F1013 50.4% 100%);display:flex;align-items:center;justify-content:center;font:800 64px/1 Sora;letter-spacing:-.04em;box-shadow:0 14px 30px rgba(0,0,0,.6),inset 0 1px 0 rgba(255,255,255,.08);position:relative}
.d:after{content:"";position:absolute;left:0;right:0;top:50%;height:1px;background:#000}
.col{font:800 48px/1 Sora;color:#4A4E55}
.lb{display:flex;justify-content:center;gap:110px;font:600 9.5px/1 Inter;letter-spacing:.24em;color:#7B8188;margin-top:10px}
.h{text-align:center;margin-top:30px}.h h1{font:800 32px/1.05 Sora;letter-spacing:-.04em;margin:0}.h p{font:400 14px/1.55 Inter;color:#AEB4BB;margin:10px 28px 0}
.cmp{margin:24px 20px 0;border-radius:16px;background:#15171B;padding:6px 16px}
.cr{display:grid;grid-template-columns:1fr auto auto;gap:12px;align-items:baseline;padding:11px 0;border-bottom:1px solid rgba(255,255,255,.06)}.cr:last-child{border:0}
.cr span{font:500 13.5px Inter}.cr s{font:400 12px Inter;color:#6E747B}.cr b{font:700 15px Sora}
.cta{padding:22px 20px 0}.ps{text-align:center;font:400 12.5px/1.5 Inter;color:#7B8188;margin:16px 26px 0}.ft{color:#5F656C}
''','<div class="lg"><img src="logo.png"></div><div class="k">BLACK FRIDAY ENDET IN</div><div class="fl"><div class="d">36</div><div class="col">:</div><div class="d">59</div></div><div class="lb"><span>STUNDEN</span><span>MINUTEN</span></div><div class="h"><h1>Noch bis Montag.</h1><p>−35 % auf alles, danach gelten wieder die normalen Preise. Das sparst du im Vergleich zu sonst:</p></div><div class="cmp">'
 +''.join(f'<div class="cr"><span>{n}</span><s>{eur(p)}</s><b>{eur(p*.65)}</b></div>' for n,p in [('Retatrutide 10 mg',79.90),('Retatrutide 20 mg',149.90),('GHK-Cu 50 mg',39.90),('GLOW 70 mg',139.90),('Pen Retatrutide 20 mg',264.90)])
 +'</div><div class="cta"><div class="btn bw">Jetzt bestellen'+ARW+'</div></div><div class="ps">Fragen vor der Bestellung? Schreib uns auf Telegram, wir antworten schnell.</div><div class="ft">Alle Produkte ausschließlich für Laborforschungszwecke.<br>Abmelden · Impressum</div>')
# ---------- Mail 8: Letzter Tag – weiß, große Typo ----------
P['8-last']=page('Heute 23:59 ist Schluss','Letzter Tag: −35 % auf alles.','10:00','''
.m{background:#fff;color:#07080A}
.hd{display:flex;justify-content:space-between;align-items:center;padding:20px 22px 0;font:700 10px/1 Inter;letter-spacing:.3em;color:#7A8088}.hd img{width:26px;filter:brightness(.1)}
.big{font:800 128px/.82 Sora;letter-spacing:-.07em;margin:26px 0 0 16px}
.big span{display:block;font-size:46px;letter-spacing:-.04em;line-height:1;margin:14px 0 0 4px;color:#7A8088}
.bar{margin:26px 22px 0;height:10px;border-radius:5px;background:#EDEEF0;overflow:hidden}.bar i{display:block;height:100%;width:94%;background:#07080A;border-radius:5px}
.bl{display:flex;justify-content:space-between;margin:8px 22px 0;font:600 10px/1 Inter;letter-spacing:.2em;color:#7A8088}
.tx{margin:26px 22px 0;font:400 15px/1.6 Inter;color:#3A3F46}.tx b{color:#07080A}
.row{display:flex;gap:10px;padding:20px 22px 0}
.t{flex:1;border:1px solid #E2E3E5;border-radius:14px;padding:12px 10px;text-align:center;position:relative;overflow:hidden}
.t .im{height:132px;position:relative}.t .vw{left:50%!important;transform:translateX(-50%)}
.t span{display:block;font:600 12.5px Inter;margin-top:6px}.t s{font:400 11px Inter;color:#9AA0A8;display:block;margin-top:4px}.t b{font:800 15px Sora}
.cta{padding:22px 22px 0}.ft{color:#8A9098}
''','<div class="hd"><img src="logo.png"><span>CYBER MONDAY</span></div><div class="big">23:59<span>Heute ist Schluss.</span></div><div class="bar"><i></i></div><div class="bl"><span>BLACK WEEK</span><span>NOCH WENIGE STUNDEN</span></div><div class="tx">Das war die Black Week 2026. <b>−35 % auf alles gilt nur noch heute.</b> Danach gelten wieder die normalen Preise.</div><div class="row">'
 +''.join(f'<div class="t"><div class="im">{v(img,0,0,46)}</div><span>{n}</span><s>{eur(p)}</s><b>{eur(p*.65)}</b></div>' for img,n,p in [('reta.png','Retatrutide',79.90),('ghk.png','GHK-Cu',39.90),('glow.png','GLOW',139.90)])
 +'</div><div class="cta"><div class="btn bb">Letzte Chance nutzen'+ARW+'</div></div><div class="ft">Es zählt deine Bestellung, nicht der Zahlungseingang.<br>Alle Produkte ausschließlich für Laborforschungszwecke. · Abmelden · Impressum</div>')
for k,h in P.items(): open(f'm2-{k}.html','w').write(h)
print(list(P))
