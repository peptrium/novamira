# Produktseite v2-Entwuerfe D/E/F (Retatrutide), mobil 390px
U='https://peptrium.com/wp-content/uploads/'
IMG=U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-768x1024.png'
T={'reta':U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png','bac':U+'2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png',
 'spr':U+'2026/08/insulinspritze-10er-pack-wide-300x300.png','pen':U+'2026/08/45D2BF40-474D-46BE-9C9E-885EE2E57738-300x300.png',
 'cagri':U+'2026/10/cagrilintid-peptrium-300x300.png','ghk':U+'2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png',
 'logo':U+'2026/08/IMG_2644-cropped-300x300.png','tilt':U+'2026/08/retatrutide-tilted-glass-v3.png'}
I={ # Linien-Icons
 'truck':'<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/>',
 'box':'<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>',
 'lock':'<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
 'flask':'<path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 1.7 3h10.6a2 2 0 0 0 1.7-3l-5-9V3"/><path d="M7.5 15h9"/>',
 'scan':'<path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3"/><path d="M7 12h10"/>',
 'snow':'<path d="M12 2v20M4.9 6.5l14.2 11M19.1 6.5 4.9 17.5"/>',
 'chat':'<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/>',
 'calc':'<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8M8 11h2M14 11h2M8 15h2M14 15h2"/>',
 'book':'<path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z"/><path d="M4 19V5"/>',
 'cart':'<path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/><circle cx="10" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/>',
 'check':'<path d="M5 12.5 10 17l9-10"/>','star':'',
}
def ic(n,c='currentColor',s=18,w=1.9): return f'<svg viewBox="0 0 24 24" width="{s}" height="{s}" fill="none" stroke="{c}" stroke-width="{w}" stroke-linecap="round" stroke-linejoin="round">{I[n]}</svg>'
ST='<span class="st">★★★★★</span>'
REV=[('Robert N.','Alles gut gelaufen, bin zufrieden. Lieferung kam schnell und diskret.'),('Sandra K.','Fragen auf Telegram wurden sofort beantwortet. Top Service.'),
 ('Marco B.','Saubere Verpackung, Etiketten klar beschriftet. Gerne wieder.'),('Julia W.','Zweite Bestellung, wieder nach 2 Tagen da.')]
FAQ=['Wie schnell wird verschickt?','Wie kann ich bezahlen?','Wie wird verpackt?','10 mg oder 20 mg – was ist der Unterschied?','Wie lagere ich die Ampulle?']
DIS='Nur für Laborforschungszwecke – nicht für den menschlichen oder veterinärmedizinischen Gebrauch.'
HEAD='''<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=390">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box}body{margin:0;width:390px;font-family:Sora,sans-serif;color:#0D0F12;background:#fff;-webkit-font-smoothing:antialiased;position:relative}p{margin:0}img{display:block}
.hd{background:#0D0F12;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;color:#fff}
.hd .lg{display:flex;align-items:center;gap:8px;font:700 18px Sora}.hd .lg img{width:30px;height:30px}
.ib{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.08);display:inline-flex;align-items:center;justify-content:center;margin-left:6px;color:#fff}
.st{color:#F5A524;letter-spacing:1px}
.tag{position:absolute;right:10px;top:10px;background:#FF5A1F;color:#fff;font:800 11px Sora;padding:5px 10px;border-radius:999px;z-index:30}
.bar{color:#0D0F12;position:absolute;left:0;right:0;top:760px;z-index:25;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);border-top:1px solid #E3E6E9;box-shadow:0 -10px 30px rgba(0,0,0,.08);padding:9px 12px 12px;display:flex;align-items:center;gap:8px}
.bar .nm{flex:1;min-width:0;font:800 13.5px Sora}.bar .nm small{display:block;font:600 11.5px Sora;color:#5B6169}
.bar .chip{border:1.5px solid #E3E6E9;border-radius:999px;padding:6px 10px;font:800 11.5px Sora}.bar .chip.on{background:#0D0F12;color:#fff;border-color:#0D0F12}
.bar .go{height:46px;padding:0 16px;border-radius:14px;background:#0D0F12;color:#fff;display:flex;align-items:center;gap:8px;font:800 13.5px Sora;white-space:nowrap}
.note{position:absolute;left:12px;background:#FF5A1F;color:#fff;font:800 10px Sora;border-radius:999px;padding:3px 8px;z-index:30}
@@CSS@@</style></head><body>'''
HDR=f'<div class="hd"><span class="lg"><img src="{T["logo"]}">Peptrium</span><span><span class="ib">'+ic('scan','#fff',16)+'</span><span class="ib">'+ic('cart','#fff',16)+'</span></span></div>'
def bar(): return f'<div class="bar"><span class="nm">Retatrutide<small>3 Stück · 215,73 €</small></span><span class="chip on">10 mg</span><span class="chip">20 mg</span><span class="go">{ic("cart","#fff",16)}In den Korb</span></div><span class="note" style="top:738px">Kaufleiste beim Scrollen</span>'
def page(css,body): return HEAD.replace('@@CSS@@',css)+HDR+body+'</body></html>'

# gemeinsame Bausteine
def tiers(cls='qt'):
    rows=[(1,'79,90','',''),(3,'71,91','−10 %','Beliebt'),(5,'67,92','−15 %',''),(10,'63,92','−20 %','')]
    h=f'<div class="{cls}">'
    for n,p,d,flag in rows:
        on=' on' if n==3 else ''
        h+=f'<div class="t{on}">{f"<em>{flag}</em>" if flag else ""}<b>{n}</b><small>{"Stück" if n==1 else "Stück"}</small><span>{p} €</span>{f"<i>{d}</i>" if d else "<i class=x>&nbsp;</i>"}</div>'
    return h+'</div>'
def addons():
    return f'''<div class="ad"><img src="{T['bac']}"><span class="n">Bac Water 3 ml<small>Zum Anmischen · +9,90 €</small></span><span class="sw on"></span></div>
<div class="ad"><img src="{T['spr']}"><span class="n">Insulinspritzen 10er<small>1 ml U-100 · +9,90 €</small></span><span class="sw"></span></div>'''
def reviews(dark):
    return ''.join(f'<div class="rv">{ST}<p>„{t}“</p><small>{n}</small></div>' for n,t in REV)
def more():
    return ''.join(f'<div class="pc"><img src="{T[k]}"><div><b>{n}</b><small>{p}</small></div></div>' for k,n,p in [('cagri','Cagrilintid','104,90 €'),('pen','Retatrutide-Pen','ab 264,90 €'),('ghk','GHK-Cu','ab 39,90 €')])
def faqs(): return ''.join(f'<div>{q}<span>+</span></div>' for q in FAQ)
def acc_rows(): return ''.join(f'<div><span class="i">{ic(i,"#0D0F12",17)}</span>{t}<span class="p">+</span></div>' for i,t in [('calc','Anmischen & Peptid-Rechner'),('snow','Lagerung & Haltbarkeit'),('flask','Qualität & Analyse'),('truck','Versand & Bezahlung')])

BASE='''
.qt{display:grid;grid-template-columns:repeat(4,1fr);gap:7px}
.qt .t{position:relative;border:1.5px solid #E3E6E9;border-radius:14px;padding:12px 4px 9px;text-align:center;background:#fff}
.qt .t b{display:block;font:800 19px/1 Sora}.qt .t small{display:block;font:600 10.5px Sora;color:#6B7178;margin:2px 0 5px}
.qt .t span{display:block;font:700 11.5px Sora}.qt .t i{display:block;font:800 10.5px Sora;color:#0B6B3A;font-style:normal;margin-top:2px}
.qt .t em{position:absolute;top:-9px;left:50%;transform:translateX(-50%);background:#0D0F12;color:#fff;font:800 9px Sora;font-style:normal;border-radius:999px;padding:3px 7px;white-space:nowrap}
.qt .t.on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12;background:#F7F8F9}
.ad{display:flex;align-items:center;gap:10px;border:1px solid #E3E6E9;border-radius:14px;padding:9px 10px;margin:0 0 8px;background:#fff}
.ad img{width:42px;height:42px;border-radius:10px;object-fit:cover;background:#0D0F12}.ad .n{flex:1;font:700 13px Sora}.ad .n small{display:block;font:500 11px Sora;color:#5B6169}
.sw{width:44px;height:26px;border-radius:999px;background:#D5D8DC;position:relative;flex:none}.sw:after{content:'';position:absolute;left:3px;top:3px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2)}.sw.on{background:#2E9B57}.sw.on:after{left:21px}
.cta{height:56px;border-radius:16px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;gap:10px;font:800 16px Sora;box-shadow:0 10px 22px rgba(13,15,18,.22)}
.tr3{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:12px}.tr3 div{border-radius:12px;padding:10px 4px;text-align:center;font:700 10.5px/1.3 Sora;background:#F4F5F6;color:#2A2F35}.tr3 svg{display:block;margin:0 auto 5px}
.rvs{display:flex;gap:10px;overflow:hidden;margin-right:-16px}.rv{flex:0 0 250px;border-radius:18px;padding:14px}.rv p{font:500 13px/1.5 Sora;margin:6px 0 10px}.rv small{font:700 11.5px Sora}
.more{display:flex;gap:10px;overflow:hidden;margin-right:-16px}.pc{flex:0 0 160px;border-radius:16px;overflow:hidden;background:#fff;border:1px solid #EEF0F2}.pc img{width:160px;height:150px;object-fit:cover;background:#0D0F12}.pc div{padding:9px}.pc b{display:block;font:800 13.5px Sora}.pc small{font:700 12.5px Sora}
.fq div,.ac div{display:flex;align-items:center;gap:10px;padding:15px 0;border-bottom:1px solid #EEF0F2;font:700 14px Sora}.fq div span,.ac .p{margin-left:auto;color:#9AA0A6}
.ac .i{width:34px;height:34px;border-radius:10px;background:#F4F5F6;display:flex;align-items:center;justify-content:center;flex:none}
.rg{display:flex;gap:8px;flex-wrap:wrap}.rg span{display:inline-flex;align-items:center;gap:6px;border:1px solid #E3E6E9;border-radius:999px;padding:9px 12px;font:700 12px Sora;background:#fff}
.dis{font:500 11px/1.5 Sora;color:#6B7178;text-align:center;padding:18px 24px 30px}
.sumrow{display:flex;justify-content:space-between;align-items:flex-end;margin:16px 0 12px}.sumrow .l{font:600 12.5px Sora;color:#5B6169}.sumrow .l b{display:block;font:800 26px Sora;color:#0D0F12;margin-top:2px}
.sumrow .r{text-align:right;font:700 12px Sora;color:#0B6B3A}.sumrow .r s{color:#9AA0A6;font-weight:600;display:block}
.lb{font:800 10.5px Sora;letter-spacing:.1em;color:#6B7178;margin:16px 0 8px;text-transform:uppercase}
.seg{display:flex;gap:8px}.seg div{flex:1;border:1.5px solid #E3E6E9;border-radius:14px;padding:11px;font:800 16px Sora;text-align:center;background:#fff}.seg small{display:block;font:600 11.5px Sora;color:#6B7178;margin-top:2px}.seg .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.del{display:flex;align-items:center;gap:8px;font:600 12.5px Sora;color:#0B6B3A;margin:8px 0 0}.del i{width:8px;height:8px;border-radius:50%;background:#2E9B57;box-shadow:0 0 0 4px rgba(46,155,87,.15)}
'''
# ================= D: Clean hell =================
D_CSS=BASE+'''
body{background:#F2F4F6}
.g{background:#fff;padding:10px 12px 0}.g .m{position:relative;border-radius:24px;overflow:hidden;background:radial-gradient(80% 70% at 50% 40%,#2B3037,#0D0F12);height:360px;display:flex;justify-content:center}
.g .m img{height:360px}.g .bd{position:absolute;left:12px;top:12px;display:flex;gap:6px}.g .bd span{background:rgba(255,255,255,.95);border-radius:999px;font:700 11px Sora;padding:5px 9px}
.g .cnt{position:absolute;right:12px;bottom:12px;background:rgba(0,0,0,.5);color:#fff;border-radius:999px;font:700 11px Sora;padding:4px 9px}
.g .th{display:flex;gap:8px;padding:10px 4px 0}.g .th span{width:54px;height:54px;border-radius:14px;overflow:hidden;background:#F2F4F5;border:2px solid transparent}.g .th span.on{border-color:#0D0F12}.g .th img{width:100%;height:100%;object-fit:cover}
.bb{background:#fff;padding:14px 16px 20px;border-radius:0 0 28px 28px}
.cat{font:700 10.5px Sora;letter-spacing:.1em;color:#6B7178}
h1{font:800 30px/1.05 Sora;margin:4px 0 6px;letter-spacing:-.02em}
.rt{display:flex;align-items:center;gap:6px;font:600 12.5px Sora;color:#5B6169}.rt u{color:#0D0F12}
.chips{display:flex;gap:6px;flex-wrap:wrap;margin:12px 0 0}.chips span{display:inline-flex;align-items:center;gap:5px;background:#F2F4F5;border-radius:999px;padding:6px 10px;font:700 11.5px Sora}
.sec{background:#fff;margin:12px 0 0;padding:22px 16px;border-radius:28px}
.ey{font:800 10.5px Sora;letter-spacing:.12em;color:#6B7178;margin:0 0 6px;text-transform:uppercase}
.sec h2{font:800 22px/1.15 Sora;margin:0 0 14px;letter-spacing:-.01em}
.hl{display:grid;grid-template-columns:1fr 1fr;gap:8px}.hl div{background:#F4F5F6;border-radius:16px;padding:13px}.hl b{display:block;font:800 14px Sora;margin:8px 0 2px}.hl small{font:500 11.5px/1.4 Sora;color:#5B6169}
.dk{background:#0D0F12;color:#fff;margin:12px 0 0;padding:26px 16px;border-radius:28px;position:relative;overflow:hidden}
.dk h2{font:800 23px/1.12 Sora;margin:0 0 14px}.dk h2 em{font-style:normal;color:#8E959E}
.dk .sc{display:flex;align-items:center;gap:12px;background:#15181C;border:1px solid #23272D;border-radius:18px;padding:14px;margin:0 0 12px}.dk .sc b{font:800 38px Sora}.dk .sc small{font:600 12px Sora;color:#A8B0B9}
.dk .rv{background:#15181C;border:1px solid #23272D}.dk .rv p{color:#D5D9DE}
.dk .tilt{position:absolute;right:-28px;top:10px;width:140px;transform:rotate(16deg)}
.set{border-radius:22px;padding:14px;background:linear-gradient(160deg,#F7F8F9,#EEF1F3);border:1px solid #E3E6E9}
.set .r{display:flex;align-items:center;gap:10px;padding:7px 0}.set .r img{width:46px;height:46px;border-radius:12px;object-fit:cover;background:#0D0F12}.set .r .n{flex:1;font:700 13px Sora}.set .r .n small{display:block;font:500 11px Sora;color:#5B6169}.set .r b{font:700 13px Sora}
.set .plus{height:1px;background:#E3E6E9;margin:2px 0 2px 56px}
.set .f{display:flex;align-items:center;justify-content:space-between;margin-top:10px}.set .f b{font:800 20px Sora}.set .f b small{display:block;font:600 11px Sora;color:#5B6169}.set .f span{background:#0D0F12;color:#fff;border-radius:14px;padding:13px 16px;font:800 13.5px Sora}
'''
def D():
    b='<span class="tag">D · Clean hell</span>'
    b+=f'''<div class="g"><div class="m"><div class="bd"><span>★ Bestseller</span><span>✓ HPLC ≥ 99 %</span></div><img src="{IMG}"><span class="cnt">1 / 4</span></div>
<div class="th"><span class="on"><img src="{T['reta']}"></span><span><img src="{T['tilt']}"></span><span><img src="{T['bac']}"></span><span><img src="{T['pen']}"></span></div></div>
<div class="bb"><p class="cat">FORSCHUNGSPEPTID · STOFFWECHSEL</p><h1>Retatrutide</h1>
<p class="rt">{ST} 4,8 · <u>378 Bewertungen</u></p>
<div class="chips"><span>{ic('flask','#2E9B57',14,2.2)}HPLC ≥ 99 %</span><span>{ic('scan','#2E9B57',14,2.2)}LC-MS geprüft</span><span>{ic('truck','#2E9B57',14,2.2)}Versand aus DE</span></div>
<p class="lb">Größe</p><div class="seg"><div class="on">10 mg<small>79,90 €</small></div><div>20 mg<small>149,90 €</small></div></div>
<p class="lb">Menge – je mehr, desto günstiger</p>{tiers()}
<p class="lb">Dazu passend</p>{addons()}
<div class="sumrow"><span class="l">Gesamt<b>225,63 €</b></span><span class="r"><s>249,60 €</s>Du sparst 23,97 €</span></div>
<div class="cta">{ic('cart','#fff',18,2)}In den Warenkorb</div>
<p class="del" style="justify-content:center"><i></i>Auf Lager · in 2 Werktagen bei dir</p>
<div class="tr3"><div>{ic('truck','#0D0F12')}Gratis ab 150 €</div><div>{ic('box','#0D0F12')}Neutral verpackt</div><div>{ic('lock','#0D0F12')}Vorkasse · Krypto</div></div></div>

<div class="sec"><p class="ey">Auf einen Blick</p><div class="hl">
<div>{ic('flask','#0D0F12',20)}<b>≥ 99 % rein</b><small>HPLC-geprüft, jede Charge</small></div>
<div>{ic('scan','#0D0F12',20)}<b>LC-MS</b><small>Identität bestätigt</small></div>
<div>{ic('snow','#0D0F12',20)}<b>Lyophilisat</b><small>stabil, −20 °C lagern</small></div>
<div>{ic('box','#0D0F12',20)}<b>Diskret</b><small>neutral, mit DHL-Nummer</small></div></div></div>

<div class="dk"><img class="tilt" src="{T['tilt']}"><p class="ey" style="color:#8E959E">Kundenstimmen</p><h2>Das sagen Kunden<br><em>über Retatrutide.</em></h2>
<div class="sc"><b>4,8</b><span>{ST}<br><small>aus 378 Bewertungen</small></span></div><div class="rvs">{reviews(True)}</div></div>

<div class="sec"><p class="ey">Komplett-Set</p><h2>Alles für die Vorbereitung – mit einem Klick</h2><div class="set">
<div class="r"><img src="{T['reta']}"><span class="n">Retatrutide 10 mg<small>Ampulle</small></span><b>79,90 €</b></div><div class="plus"></div>
<div class="r"><img src="{T['bac']}"><span class="n">Bac Water 3 ml<small>zum Anmischen</small></span><b>9,90 €</b></div><div class="plus"></div>
<div class="r"><img src="{T['spr']}"><span class="n">Insulinspritzen 10er<small>1 ml U-100</small></span><b>9,90 €</b></div>
<div class="f"><b>99,70 €<small>3 Artikel</small></b><span>Set in den Warenkorb</span></div></div></div>

<div class="sec"><p class="ey">Produktinfo</p><h2>Über Retatrutide</h2>
<p style="font:500 13.5px/1.6 Sora;color:#3A3F45;margin:0 0 10px">Retatrutide ist ein synthetisches Peptid, das in der Forschung als dreifacher Rezeptor-Agonist (GIP, GLP-1, Glukagon) untersucht wird. Geliefert als gefriergetrocknetes Pulver … <b><u>Weiterlesen</u></b></p>
<div class="ac">{acc_rows()}</div></div>

<div class="sec"><p class="ey">Häufige Fragen</p><div class="fq">{faqs()}</div>
<p class="ey" style="margin-top:22px">Mehr dazu im Ratgeber</p><div class="rg"><span>{ic('book','#0D0F12',14)}10 mg oder 20 mg?</span><span>{ic('book','#0D0F12',14)}Was ist „GLP-3“?</span><span>{ic('calc','#0D0F12',14)}Peptid-Rechner</span></div></div>

<div class="sec"><p class="ey">Auch beliebt</p><div class="more">{more()}</div></div>
<p class="dis">{DIS}</p>{bar()}'''
    return page(D_CSS,b)

# ================= E: Dunkel Premium v2 =================
E_CSS=BASE+'''
body{background:#0B0D10;color:#fff}
.hero{position:relative;height:440px;overflow:hidden;background:radial-gradient(85% 70% at 50% 38%,#2C3138 0%,#0B0D10 72%)}
.hero img.v{position:absolute;left:50%;top:0;transform:translateX(-50%);height:440px;-webkit-mask-image:linear-gradient(180deg,#000 62%,transparent 96%);mask-image:linear-gradient(180deg,#000 62%,transparent 96%)}
.hero .bd{position:absolute;left:16px;top:16px;display:flex;gap:6px}.hero .bd span{border:1px solid rgba(255,255,255,.22);background:rgba(255,255,255,.08);border-radius:999px;font:700 11px Sora;padding:5px 10px;backdrop-filter:blur(6px)}
.hero .dots{position:absolute;left:0;right:0;bottom:84px;display:flex;justify-content:center;gap:6px}.hero .dots i{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.3)}.hero .dots i:first-child{width:18px;border-radius:9px;background:#fff}
.ttl{padding:0 16px;margin-top:-76px;position:relative}
.ttl h1{font:800 36px/1 Sora;margin:0 0 8px;letter-spacing:-.02em}
.ttl .rt{font:600 12.5px Sora;color:#A8B0B9}
.ttl .chips{display:flex;gap:6px;flex-wrap:wrap;margin:12px 0 0}.ttl .chips span{display:inline-flex;align-items:center;gap:5px;font:700 11.5px Sora;border-radius:999px;padding:6px 10px;background:#15181C;border:1px solid #23272D;color:#D5D9DE}
.card{background:#fff;color:#0D0F12;border-radius:28px;margin:18px 10px 0;padding:18px 14px}
.card .pr{display:flex;align-items:baseline;justify-content:space-between}.card .pr b{font:800 32px Sora}.card .pr small{font:600 12px Sora;color:#6B7178}
.pill{display:flex;background:#F2F4F5;border-radius:16px;padding:4px}.pill div{flex:1;text-align:center;padding:10px 0;border-radius:12px;font:800 15px Sora;color:#5B6169}.pill small{display:block;font:600 11px Sora}.pill .on{background:#0D0F12;color:#fff}
.tl div{display:flex;align-items:center;justify-content:space-between;border:1.5px solid #E3E6E9;border-radius:14px;padding:11px 12px;margin:0 0 7px;font:700 14px Sora}
.tl .r{display:flex;align-items:center;gap:10px}.tl .r i{width:20px;height:20px;border-radius:50%;border:2px solid #CDD1D5;flex:none}.tl .on .r i{border:6px solid #0D0F12}
.tl em{font-style:normal;font:800 10.5px Sora;background:#ECF8F1;color:#0B6B3A;border-radius:999px;padding:3px 8px;margin-left:6px}
.tl .p{text-align:right;font:800 14px Sora}.tl .p small{display:block;font:600 10.5px Sora;color:#6B7178}
.tl .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.mini{display:flex;justify-content:space-between;margin-top:14px;padding-top:12px;border-top:1px solid #EEF0F2}.mini span{display:inline-flex;align-items:center;gap:5px;font:700 11px Sora;color:#4B5157}
.ds{padding:36px 16px 4px;position:relative}
.ds h2{font:800 26px/1.08 Sora;margin:0 0 14px;letter-spacing:-.01em}.ds h2 em{font-style:normal;color:#8E959E}
.ey{display:inline-flex;align-items:center;gap:6px;font:800 10.5px Sora;letter-spacing:.12em;color:#A8B0B9;border:1px solid #2A2F35;border-radius:999px;padding:5px 11px;margin:0 0 12px;text-transform:uppercase}
.ey:before{content:'';width:6px;height:6px;border-radius:50%;background:#7DDBA0}
.big{display:flex;gap:14px;align-items:center;background:#14171B;border:1px solid #23272D;border-radius:20px;padding:16px;margin:0 0 12px}.big b{font:800 42px Sora}.big small{font:600 12px Sora;color:#A8B0B9}
.ds .rv{background:#14171B;border:1px solid #23272D}.ds .rv p{color:#D5D9DE}
.tilt{position:absolute;right:-20px;top:4px;width:150px;transform:rotate(18deg)}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.grid div{background:#14171B;border:1px solid #23272D;border-radius:18px;padding:14px}.grid b{display:block;font:800 14px Sora;margin:10px 0 2px}.grid small{font:500 11.5px/1.4 Sora;color:#A8B0B9}
.setd{background:linear-gradient(160deg,#1B1F24,#101215);border:1px solid #2A2F35;border-radius:24px;padding:16px}
.setd .imgs{display:flex;margin:0 0 12px}.setd .imgs img{width:72px;height:72px;border-radius:18px;object-fit:cover;border:3px solid #101215;margin-right:-16px;background:#0D0F12}
.setd p{font:500 13px/1.5 Sora;color:#C9CED4}.setd .row{display:flex;justify-content:space-between;align-items:center;margin-top:14px}.setd .row b{font:800 22px Sora}.setd .row span{background:#fff;color:#0D0F12;border-radius:14px;padding:13px 16px;font:800 13.5px Sora}
.lt{background:#F4F5F6;color:#0D0F12;border-radius:30px 30px 0 0;margin-top:36px;padding:28px 16px 6px}
.lt h2{font:800 23px/1.15 Sora;margin:0 0 12px}
.lt .ac div,.lt .fq div{border-color:#E3E6E9}.lt .ac .i{background:#fff}
.lt .pc{border-color:#E3E6E9}.lt .dis{padding-bottom:110px}
'''
def E():
    b='<span class="tag">E · Dunkel Premium</span>'
    tl=''.join(f'<div class="{"on" if n==3 else ""}"><span class="r"><i></i>{n} {"Ampulle" if n==1 else "Ampullen"}{f"<em>{d}</em>" if d else ""}</span><span class="p">{t} €<small>{u} € / Stück</small></span></div>' for n,d,t,u in [(1,'','79,90','79,90'),(3,'−10 %','215,73','71,91'),(5,'−15 %','339,58','67,92'),(10,'−20 %','639,20','63,92')])
    b+=f'''<div class="hero"><div class="bd"><span>★ Bestseller</span><span>✓ HPLC ≥ 99 %</span></div><img class="v" src="{IMG}"><div class="dots"><i></i><i></i><i></i><i></i></div></div>
<div class="ttl"><h1>Retatrutide</h1><p class="rt">{ST} 4,8 · 378 Bewertungen · Forschungspeptid</p>
<div class="chips"><span>{ic('flask','#7DDBA0',13,2.2)}99 % Reinheit</span><span>{ic('scan','#7DDBA0',13,2.2)}LC-MS</span><span>{ic('truck','#7DDBA0',13,2.2)}Versand aus DE</span><span>{ic('box','#7DDBA0',13,2.2)}Diskret</span></div></div>
<div class="card"><div class="pr"><b>215,73 €</b><small>3 × 71,91 €</small></div><p class="del"><i></i>Auf Lager · in 2 Werktagen bei dir</p>
<p class="lb">Größe</p><div class="pill"><div class="on">10 mg<small>79,90 €</small></div><div>20 mg<small>149,90 €</small></div></div>
<p class="lb">Menge</p><div class="tl">{tl}</div>
<div class="ad" style="margin-top:10px"><img src="{T['bac']}"><span class="n">+ Bac Water 3 ml<small>zum Anmischen · 9,90 €</small></span><span class="sw"></span></div>
<div class="cta" style="margin-top:6px">{ic('cart','#fff',18,2)}In den Warenkorb</div>
<div class="mini"><span>{ic('truck','#0D0F12',14)}Gratis ab 150 €</span><span>{ic('box','#0D0F12',14)}Neutral</span><span>{ic('lock','#0D0F12',14)}Vorkasse · Krypto</span></div></div>

<div class="ds"><img class="tilt" src="{T['tilt']}"><span class="ey">Kundenstimmen</span><h2>Das sagen Kunden<br><em>über Retatrutide.</em></h2>
<div class="big"><b>4,8</b><span>{ST}<br><small>aus 378 Bewertungen</small></span></div><div class="rvs">{reviews(True)}</div></div>

<div class="ds"><span class="ey">Warum Peptrium</span><h2>Qualität, die du<br><em>nachprüfen kannst.</em></h2>
<div class="grid"><div>{ic('flask','#7DDBA0',22)}<b>HPLC ≥ 99 %</b><small>Jede Charge geprüft</small></div><div>{ic('scan','#7DDBA0',22)}<b>LC-MS</b><small>Identität bestätigt</small></div>
<div>{ic('snow','#7DDBA0',22)}<b>Lyophilisiert</b><small>stabil gelagert</small></div><div>{ic('chat','#7DDBA0',22)}<b>Hilfe auf Telegram</b><small>schnelle Antworten</small></div></div></div>

<div class="ds"><span class="ey">Komplett-Set</span><h2>Startklar<br><em>in einem Paket.</em></h2><div class="setd">
<div class="imgs"><img src="{T['reta']}"><img src="{T['bac']}"><img src="{T['spr']}"></div>
<p>Retatrutide 10 mg + Bac Water 3 ml + Insulinspritzen 10er – alles für die Vorbereitung im Labor.</p>
<div class="row"><b>99,70 €</b><span>Set in den Warenkorb</span></div></div></div>

<div class="lt"><h2>Über Retatrutide</h2><p style="font:500 13.5px/1.6 Sora;color:#3A3F45;margin:0 0 10px">Retatrutide ist ein synthetisches Peptid, das in der Forschung als dreifacher Rezeptor-Agonist (GIP, GLP-1, Glukagon) untersucht wird … <b><u>Weiterlesen</u></b></p>
<div class="ac">{acc_rows()}</div>
<h2 style="margin-top:24px">Häufige Fragen</h2><div class="fq">{faqs()}</div>
<div class="rg" style="margin:18px 0 0"><span>{ic('book','#0D0F12',14)}10 mg oder 20 mg?</span><span>{ic('book','#0D0F12',14)}Was ist „GLP-3“?</span><span>{ic('calc','#0D0F12',14)}Peptid-Rechner</span></div>
<h2 style="margin-top:26px">Auch beliebt</h2><div class="more">{more()}</div><p class="dis">{DIS}</p></div>{bar()}'''
    return page(E_CSS,b)

# ================= F: Kauf-Assistent =================
F_CSS=BASE+'''
body{background:#F2F4F6}
.top{background:#fff;padding:14px 16px 16px;display:flex;gap:12px}
.top .im{flex:0 0 140px;height:176px;border-radius:22px;background:radial-gradient(80% 70% at 50% 40%,#2B3037,#0D0F12);overflow:hidden;display:flex;justify-content:center;position:relative}.top .im img{height:176px}
.top .im span{position:absolute;left:8px;top:8px;background:#fff;border-radius:999px;font:700 10px Sora;padding:3px 7px}
.top h1{font:800 26px/1.05 Sora;margin:2px 0 5px}.top .rt{font:600 12px Sora;color:#5B6169;margin:0 0 8px}
.top .ch{display:flex;flex-direction:column;gap:5px}.top .ch span{display:inline-flex;align-items:center;gap:6px;font:700 11.5px Sora;color:#2A2F35}
.top .pr{font:800 21px Sora;margin-top:9px}.top .pr small{font:600 11.5px Sora;color:#6B7178}
.stp{background:#fff;margin:10px 12px 0;border-radius:22px;padding:15px 14px}
.stp .h{display:flex;align-items:center;gap:9px;font:800 15px Sora;margin:0 0 12px}.stp .h i{font-style:normal;width:26px;height:26px;border-radius:50%;background:#0D0F12;color:#fff;font:800 12px/26px Sora;text-align:center}.stp .h small{margin-left:auto;font:600 12px Sora;color:#6B7178}
.bg{display:grid;grid-template-columns:1fr 1fr;gap:8px}.bg div{position:relative;border:1.5px solid #E3E6E9;border-radius:16px;padding:13px;font:800 19px Sora;background:#fff}.bg small{display:block;font:600 12px Sora;color:#6B7178;margin-top:2px}.bg .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.bg .on:after{content:'✓';position:absolute;right:10px;top:10px;width:22px;height:22px;border-radius:50%;background:#0D0F12;color:#fff;font:800 12px/22px Sora;text-align:center}
.hint{font:600 12.5px Sora;color:#0B6B3A;background:#ECF8F1;border-radius:12px;padding:9px 11px;margin-top:10px;display:flex;align-items:center;gap:7px}
.sum{margin:12px 12px 0;background:#0D0F12;color:#fff;border-radius:24px;padding:16px}
.sum .l{display:flex;justify-content:space-between;font:500 13px Sora;color:#C9CED4;padding:4px 0}.sum .l b{color:#fff;font-weight:700}
.sum .t{display:flex;justify-content:space-between;align-items:baseline;border-top:1px solid rgba(255,255,255,.12);margin-top:8px;padding-top:12px}.sum .t b{font:800 28px Sora}
.sum .go{margin-top:12px;height:56px;border-radius:16px;background:#fff;color:#0D0F12;display:flex;align-items:center;justify-content:center;gap:9px;font:800 16px Sora}
.sum .gx{margin-top:12px;background:#15181C;border-radius:14px;padding:10px 12px}.sum .gx p{display:flex;justify-content:space-between;font:600 11.5px Sora;color:#A8B0B9;margin:0 0 7px}.sum .gx p b{color:#7DDBA0}
.sum .gx .tr{height:6px;border-radius:9px;background:#2A2F35;position:relative}.sum .gx .tr i{position:absolute;left:0;top:0;bottom:0;width:72%;border-radius:9px;background:linear-gradient(90deg,#7DDBA0,#2E9B57)}
.sum .del{color:#7DDBA0;justify-content:center;margin-top:12px}
.ic4{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin:14px 12px 0}.ic4 div{background:#fff;border-radius:16px;padding:11px 4px;text-align:center;font:700 10.5px/1.3 Sora}.ic4 svg{display:block;margin:0 auto 6px}
.sec{background:#fff;margin:12px 12px 0;border-radius:24px;padding:18px 14px}
.sec h2{font:800 20px Sora;margin:0 0 12px}
.tabs{display:flex;gap:6px;margin:0 0 12px}.tabs span{flex:1;text-align:center;border:1px solid #E3E6E9;border-radius:999px;padding:9px 0;font:700 12px Sora}.tabs span:first-child{background:#0D0F12;color:#fff;border-color:#0D0F12}
.kv div{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #EEF0F2;font:600 13px Sora}.kv div span{color:#6B7178;font-weight:500}
.rvl .rv{border-bottom:1px solid #EEF0F2;border-radius:0;padding:12px 0;flex:none}.rvl .rv p{margin:4px 0}.rvl small{color:#5B6169}
'''
def F():
    b='<span class="tag">F · Kauf-Assistent</span>'
    b+=f'''<div class="top"><div class="im"><span>★ Bestseller</span><img src="{IMG}"></div><div><h1>Retatrutide</h1><p class="rt">{ST} 4,8 (378)</p>
<div class="ch"><span>{ic('flask','#2E9B57',14,2.2)}HPLC ≥ 99 %</span><span>{ic('snow','#2E9B57',14,2.2)}Lyophilisat</span><span>{ic('truck','#2E9B57',14,2.2)}2 Werktage</span></div>
<p class="pr">ab 79,90 € <small>/ Stück</small></p></div></div>
<div class="stp"><p class="h"><i>1</i>Größe wählen</p><div class="bg"><div class="on">10 mg<small>79,90 €</small></div><div>20 mg<small>149,90 € · doppelter Inhalt</small></div></div></div>
<div class="stp"><p class="h"><i>2</i>Menge wählen</p>{tiers()}<p class="hint">{ic('check','#0B6B3A',15,2.6)}Mit 3 Stück sparst du 23,97 €</p></div>
<div class="stp"><p class="h"><i>3</i>Zubehör dazu?<small>optional</small></p>{addons()}</div>
<div class="sum"><div class="l"><span>3× Retatrutide 10 mg</span><b>215,73 €</b></div><div class="l"><span>Bac Water 3 ml</span><b>9,90 €</b></div><div class="l"><span>Versand</span><b style="color:#7DDBA0">gratis</b></div>
<div class="t"><span style="font:600 13px Sora;color:#C9CED4">Gesamt</span><b>225,63 €</b></div>
<div class="gx"><p><span>Geschenk ab 200 €: Injektionskit</span><b>✓ freigeschaltet</b></p><div class="tr"><i></i></div></div>
<div class="go">{ic('cart','#0D0F12',18,2)}In den Warenkorb</div><p class="del"><i></i>Auf Lager · in 2 Werktagen bei dir</p></div>
<div class="ic4"><div>{ic('flask','#0D0F12',20)}HPLC<br>≥ 99 %</div><div>{ic('box','#0D0F12',20)}Neutral<br>verpackt</div><div>{ic('lock','#0D0F12',20)}Vorkasse<br>· Krypto</div><div>{ic('chat','#0D0F12',20)}Hilfe auf<br>Telegram</div></div>
<div class="sec"><h2>Produktinfo</h2><div class="tabs"><span>Über</span><span>Daten</span><span>Lagerung</span></div>
<p style="font:500 13.5px/1.6 Sora;color:#3A3F45;margin:0 0 8px">Retatrutide ist ein synthetisches Peptid, das in der Forschung als dreifacher Rezeptor-Agonist (GIP, GLP-1, Glukagon) untersucht wird …</p>
<div class="kv"><div><span>Reinheit</span>≥ 99 % (HPLC)</div><div><span>Identität</span>LC-MS</div><div><span>Form</span>Lyophilisiertes Pulver</div><div style="border:0"><span>Lagerung</span>−20 °C, lichtgeschützt</div></div>
<div class="rg" style="margin-top:12px"><span>{ic('calc','#0D0F12',14)}Zum Peptid-Rechner</span><span>{ic('book','#0D0F12',14)}10 oder 20 mg?</span></div></div>
<div class="sec"><h2>Bewertungen</h2><div style="display:flex;align-items:center;gap:12px;margin:0 0 4px"><b style="font:800 40px Sora">4,8</b><span>{ST}<br><small style="font:600 12px Sora;color:#5B6169">378 Bewertungen</small></span></div>
<div class="rvl">'''+''.join(f'<div class="rv">{ST}<p>„{t}“</p><small>{n}</small></div>' for n,t in REV[:3])+f'''</div></div>
<div class="sec"><h2>Häufige Fragen</h2><div class="fq">{faqs()}</div></div>
<div class="sec"><h2>Passt dazu</h2><div class="more">{more()}</div></div>
<p class="dis">{DIS}</p>{bar()}'''
    return page(F_CSS,b)

import os
d=os.path.dirname(os.path.abspath(__file__))
for n,f in [('D',D),('E',E),('F',F)]: open(f'{d}/variante-{n}.html','w').write(f())
print('ok')
