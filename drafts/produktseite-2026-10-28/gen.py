# Entwuerfe Produktseite (Retatrutide als Beispiel) - 3 komplette Varianten, mobil 390px
U='https://peptrium.com/wp-content/uploads/'
IMG=U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-768x1024.png'
T={'reta':U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png','bac':U+'2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png',
 'spr':U+'2026/08/insulinspritze-10er-pack-wide-300x300.png','pen':U+'2026/08/45D2BF40-474D-46BE-9C9E-885EE2E57738-300x300.png',
 'cagri':U+'2026/10/cagrilintid-peptrium-300x300.png','ghk':U+'2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png',
 'logo':U+'2026/08/IMG_2644-cropped-300x300.png','tilt':U+'2026/08/retatrutide-tilted-glass-v3.png'}
REV=[('Robert N.','Alles gut gelaufen, bin zufrieden. Lieferung kam schnell und diskret.'),('Sandra K.','Top Service, Fragen auf Telegram wurden sofort beantwortet.'),
 ('Marco B.','Saubere Verpackung, Etiketten klar beschriftet. Gerne wieder.'),('Julia W.','Zweite Bestellung, wieder nach 2 Tagen da.')]
FAQ=['Wie schnell wird verschickt?','Wie kann ich bezahlen?','Wie wird verpackt?','10 mg oder 20 mg – was ist der Unterschied?','Wie lagere ich die Ampulle?']
HEAD='''<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=390">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box}body{margin:0;width:390px;font-family:Sora,sans-serif;color:#0D0F12;background:#fff;-webkit-font-smoothing:antialiased}
p{margin:0}img{display:block}
.hd{background:#0D0F12;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;color:#fff;position:relative;z-index:3}
.hd .lg{display:flex;align-items:center;gap:8px;font:700 18px Sora}.hd .lg img{width:30px;height:30px}
.ib{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.08);display:inline-flex;align-items:center;justify-content:center;margin-left:6px}
.ib svg{width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2}
.stars{color:#F5A524;letter-spacing:1px}
.ck{color:#2E9B57;font-weight:800}
.tag{position:absolute;right:12px;top:12px;background:#FF5A1F;color:#fff;font:800 11px Sora;padding:5px 10px;border-radius:999px;z-index:20;box-shadow:0 4px 12px rgba(0,0,0,.25)}
%s</style></head><body>'''
HDR='''<div class="hd"><span class="lg"><img src="%s">Peptrium</span><span><span class="ib"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg></span><span class="ib"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4-6 8-6s7 2 8 6"/></svg></span><span class="ib"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></span></span></div>'''%T['logo']
DIS='Nur für Laborforschungszwecke – nicht für den menschlichen oder veterinärmedizinischen Gebrauch.'
def stars(): return '<span class="stars">★★★★★</span>'

# ---------------- A: Klar & hell ----------------
A_CSS='''
body{background:#F4F5F6}
.gal{background:#fff;padding:12px 16px 6px}
.gal .main{border-radius:22px;overflow:hidden;background:#0D0F12;height:330px;position:relative;display:flex;align-items:center;justify-content:center}
.gal .main img{height:330px}
.gal .bd{position:absolute;left:12px;top:12px;display:flex;gap:6px}.gal .bd span{background:#fff;border-radius:999px;font:700 11px Sora;padding:5px 9px}
.gal .dots{display:flex;gap:6px;justify-content:center;padding:10px 0 4px}.gal .dots i{width:6px;height:6px;border-radius:50%;background:#D5D8DC}.gal .dots i:first-child{width:18px;border-radius:9px;background:#0D0F12}
.bb{background:#fff;padding:6px 16px 18px;border-radius:0 0 26px 26px}
.cat{font:700 11px Sora;letter-spacing:.08em;color:#6B7178;margin:4px 0 4px}
h1{font:800 28px/1.1 Sora;margin:0 0 6px}
.rt{font:600 12.5px Sora;color:#5B6169;display:flex;gap:6px;align-items:center;margin:0 0 12px}
.pr{display:flex;align-items:baseline;gap:8px;margin:0 0 4px}.pr b{font:800 30px Sora}.pr small{font:500 12px Sora;color:#5B6169}
.stock{font:700 12.5px Sora;color:#2E9B57;margin:0 0 14px}
.lb{font:800 11px Sora;letter-spacing:.08em;color:#6B7178;margin:14px 0 8px}
.seg{display:flex;gap:8px}.seg div{flex:1;border:1.5px solid #E3E6E9;border-radius:14px;padding:10px;text-align:center;font:800 16px Sora}.seg div small{display:block;font:600 11.5px Sora;color:#5B6169;margin-top:2px}.seg .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.qty{display:grid;grid-template-columns:repeat(4,1fr);gap:7px}.qty div{border:1.5px solid #E3E6E9;border-radius:12px;padding:8px 4px;text-align:center;font:800 15px Sora;position:relative}.qty div small{display:block;font:700 10.5px Sora;color:#2E9B57;margin-top:1px}.qty div em{display:block;font:500 10px Sora;color:#5B6169;font-style:normal}.qty .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.add{display:flex;align-items:center;gap:10px;border:1px solid #E3E6E9;border-radius:14px;padding:9px 10px;margin:0 0 8px}.add img{width:40px;height:40px;border-radius:9px;object-fit:cover;background:#0D0F12}.add .n{flex:1;font:700 13px Sora}.add .n small{display:block;font:500 11px Sora;color:#5B6169}
.tg{width:44px;height:26px;border-radius:999px;background:#D5D8DC;position:relative}.tg:after{content:'';position:absolute;left:3px;top:3px;width:20px;height:20px;border-radius:50%;background:#fff}.tg.on{background:#0D0F12}.tg.on:after{left:21px}
.tot{display:flex;justify-content:space-between;align-items:baseline;margin:14px 0 10px}.tot span{font:600 13px Sora;color:#5B6169}.tot b{font:800 22px Sora}.tot s{font:600 13px Sora;color:#9AA0A6;margin-right:6px}
.cta{height:56px;border-radius:16px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;gap:10px;font:800 16px Sora}
.trust{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:12px 0 0}.trust div{background:#F4F5F6;border-radius:12px;padding:9px 4px;text-align:center;font:700 11px/1.3 Sora}.trust div i{display:block;font-style:normal;font-size:16px;margin-bottom:3px}
.pay{text-align:center;font:600 11.5px Sora;color:#6B7178;margin:10px 0 0}
.sec{background:#fff;margin:12px 0 0;padding:20px 16px;border-radius:26px}
.sec h2{font:800 21px/1.15 Sora;margin:0 0 12px}
.ey{font:800 10.5px Sora;letter-spacing:.1em;color:#6B7178;margin:0 0 6px}
.acc{border-top:1px solid #EEF0F2}.acc div{display:flex;justify-content:space-between;padding:15px 0;border-bottom:1px solid #EEF0F2;font:700 14.5px Sora}.acc div span{color:#9AA0A6}
.acc .open{display:block}.acc .open p{font:500 13px/1.55 Sora;color:#4B5157;margin-top:8px}
.spec{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:0 0 4px}.spec div{background:#F4F5F6;border-radius:12px;padding:10px}.spec small{display:block;font:600 10.5px Sora;color:#6B7178}.spec b{font:800 13.5px Sora}
.rvh{display:flex;align-items:center;gap:12px;margin:0 0 12px}.rvh b{font:800 34px Sora}.rvh small{font:600 12px Sora;color:#5B6169}
.rvs{display:flex;gap:10px;overflow:hidden;margin-right:-16px}.rv{flex:0 0 250px;border:1px solid #EEF0F2;border-radius:16px;padding:12px}.rv p{font:500 13px/1.5 Sora;margin:6px 0 8px}.rv small{font:700 11.5px Sora}.rv small i{font-style:normal;color:#2E9B57;font-weight:600}
.set{border:1.5px solid #0D0F12;border-radius:18px;padding:12px}.set .row{display:flex;align-items:center;gap:10px;padding:6px 0}.set .row img{width:44px;height:44px;border-radius:10px;object-fit:cover;background:#0D0F12}.set .row .n{flex:1;font:700 13px Sora}.set .row b{font:700 13px Sora}
.set .plus{text-align:center;color:#9AA0A6;font:700 14px Sora;margin:-2px 0}
.set .sum{display:flex;align-items:center;justify-content:space-between;border-top:1px solid #EEF0F2;margin-top:8px;padding-top:10px}.set .sum b{font:800 18px Sora}.set .sum span{background:#0D0F12;color:#fff;border-radius:12px;padding:12px 14px;font:800 13px Sora}
.txt p{font:500 13.5px/1.6 Sora;color:#3A3F45;margin:0 0 10px}.txt .more{font:700 13px Sora;text-decoration:underline}
.rg{display:flex;gap:10px;align-items:center;border:1px solid #EEF0F2;border-radius:14px;padding:12px;margin:0 0 8px}.rg i{font-style:normal;width:36px;height:36px;border-radius:10px;background:#F4F5F6;display:flex;align-items:center;justify-content:center}.rg b{flex:1;font:700 13px Sora}
.more2{display:flex;gap:10px;overflow:hidden;margin-right:-16px}.pc{flex:0 0 160px;border:1px solid #EEF0F2;border-radius:16px;overflow:hidden}.pc img{width:160px;height:150px;object-fit:cover;background:#0D0F12}.pc div{padding:9px}.pc b{display:block;font:800 13.5px Sora}.pc small{font:700 12.5px Sora}
.dis{font:500 11px/1.5 Sora;color:#6B7178;text-align:center;padding:18px 24px 120px}
.bar{position:fixed}
'''
def A():
    h=HEAD%A_CSS+HDR+'<span class="tag">A · Klar & hell</span>'
    h+=f'''<div class="gal"><div class="main"><div class="bd"><span>★ Bestseller</span><span>✓ HPLC ≥ 99 %</span></div><img src="{IMG}"></div><div class="dots"><i></i><i></i><i></i></div></div>
<div class="bb"><p class="cat">FORSCHUNGSPEPTID · STOFFWECHSEL</p><h1>Retatrutide</h1>
<p class="rt">{stars()} 4,8 · <u>378 Bewertungen</u></p>
<div class="pr"><b>79,90 €</b><small>zzgl. Versand</small></div><p class="stock">● Auf Lager · heute bestellt, in 2 Werktagen da</p>
<p class="lb">GRÖSSE</p><div class="seg"><div class="on">10 mg<small>79,90 €</small></div><div>20 mg<small>149,90 €</small></div></div>
<p class="lb">MENGE</p><div class="qty"><div class="on">1<em>Stück</em></div><div>3<small>−10 %</small></div><div>5<small>−15 %</small></div><div>10<small>−20 %</small></div></div>
<p class="lb">DAZU PASSEND</p>
<div class="add"><img src="{T['bac']}"><span class="n">Bac Water 3 ml<small>zum Anmischen · +9,90 €</small></span><span class="tg on"></span></div>
<div class="add"><img src="{T['spr']}"><span class="n">Insulinspritzen 10er<small>+9,90 €</small></span><span class="tg"></span></div>
<div class="tot"><span>Gesamt</span><b>89,80 €</b></div>
<div class="cta">🛒&nbsp; In den Warenkorb</div>
<div class="trust"><div><i>🚚</i>Gratis ab 150 €</div><div><i>📦</i>Neutral verpackt</div><div><i>🔒</i>Sicher bezahlen</div></div>
<p class="pay">Vorkasse · Krypto · Guthaben</p></div>

<div class="sec"><p class="ey">AUF EINEN BLICK</p><div class="spec"><div><small>Reinheit</small><b>≥ 99 % (HPLC)</b></div><div><small>Form</small><b>Lyophilisat</b></div><div><small>Inhalt</small><b>10 mg / Ampulle</b></div><div><small>Lagerung</small><b>−20 °C, dunkel</b></div></div></div>

<div class="sec"><div class="acc"><div class="open"><span style="display:flex;justify-content:space-between;color:#0D0F12">Über Retatrutide <span style="color:#9AA0A6">−</span></span><p>Retatrutide ist ein synthetisches Peptid, das in der Forschung als dreifacher Rezeptor-Agonist (GIP, GLP-1, Glukagon) untersucht wird. Geliefert als gefriergetrocknetes Pulver in einer 10- oder 20-mg-Ampulle … <u>Weiterlesen</u></p></div>
<div>Anmischen & Peptid-Rechner<span>+</span></div><div>Lagerung & Haltbarkeit<span>+</span></div><div>Qualität & Analyse<span>+</span></div><div>Versand & Bezahlung<span>+</span></div></div></div>

<div class="sec"><p class="ey">KUNDENSTIMMEN</p><div class="rvh"><b>4,8</b><span>{stars()}<br><small>378 Bewertungen</small></span></div>
<div class="rvs">'''+''.join(f'<div class="rv">{stars()}<p>„{t}“</p><small>{n}</small></div>' for n,t in REV)+f'''</div></div>

<div class="sec"><p class="ey">KOMPLETT-SET</p><h2>Alles, was du brauchst – in einem Klick</h2><div class="set">
<div class="row"><img src="{T['reta']}"><span class="n">Retatrutide 10 mg</span><b>79,90 €</b></div><p class="plus">+</p>
<div class="row"><img src="{T['bac']}"><span class="n">Bac Water 3 ml</span><b>9,90 €</b></div><p class="plus">+</p>
<div class="row"><img src="{T['spr']}"><span class="n">Insulinspritzen 10er</span><b>9,90 €</b></div>
<div class="sum"><b>99,70 €</b><span>Set in den Warenkorb</span></div></div></div>

<div class="sec"><p class="ey">HÄUFIGE FRAGEN</p><div class="acc">'''+''.join(f'<div>{q}<span>+</span></div>' for q in FAQ)+f'''</div></div>

<div class="sec"><p class="ey">MEHR DAZU IM RATGEBER</p><div class="rg"><i>📘</i><b>Retatrutide 10 mg oder 20 mg?</b>→</div><div class="rg"><i>📘</i><b>Was ist „GLP-3“?</b>→</div><div class="rg"><i>📘</i><b>Peptide richtig rekonstituieren</b>→</div></div>

<div class="sec"><p class="ey">AUCH BELIEBT</p><div class="more2">'''+''.join(f'<div class="pc"><img src="{T[k]}"><div><b>{n}</b><small>{p}</small></div></div>' for k,n,p in [('cagri','Cagrilintid','104,90 €'),('pen','Retatrutide-Pen','ab 264,90 €'),('ghk','GHK-Cu','ab 39,90 €')])+f'''</div></div>
<p class="dis">{DIS}</p>
<div style="position:absolute;left:0;right:0;bottom:0;background:#fff;border-top:1px solid #E3E6E9;box-shadow:0 -10px 30px rgba(0,0,0,.08);padding:10px 14px 14px;display:flex;align-items:center;gap:10px"><span style="flex:1;font:800 14px Sora">Retatrutide 10 mg<br><small style="font:600 12px Sora;color:#5B6169">89,80 € · inkl. Bac Water</small></span><span class="cta" style="height:48px;padding:0 18px;font-size:14px">In den Warenkorb</span></div>
</body></html>'''
    return h

# ---------------- B: Dunkel Premium ----------------
B_CSS='''
body{background:#0B0D10;color:#fff}
.hero{position:relative;height:430px;overflow:hidden;background:radial-gradient(90% 70% at 50% 40%,#2A2F36 0%,#0B0D10 70%)}
.hero img.v{position:absolute;left:50%;top:10px;transform:translateX(-50%);height:420px;-webkit-mask-image:linear-gradient(180deg,#000 70%,transparent);mask-image:linear-gradient(180deg,#000 70%,transparent)}
.hero .bd{position:absolute;left:16px;top:14px;display:flex;gap:6px}.hero .bd span{border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.08);border-radius:999px;font:700 11px Sora;padding:5px 9px;backdrop-filter:blur(6px)}
.ttl{padding:0 16px;margin-top:-70px;position:relative}
.ttl h1{font:800 34px/1.05 Sora;margin:0 0 8px;letter-spacing:-.02em}
.ttl .rt{font:600 12.5px Sora;color:#A8B0B9}
.chips{display:flex;gap:6px;flex-wrap:wrap;margin:12px 0 0}.chips span{font:700 11.5px Sora;border-radius:999px;padding:6px 10px;background:#1A1E23;color:#D5D9DE}
.card{background:#fff;color:#0D0F12;border-radius:26px;margin:18px 10px 0;padding:18px 16px}
.pr{display:flex;align-items:baseline;justify-content:space-between}.pr b{font:800 32px Sora}.pr span{font:700 12px Sora;color:#2E9B57}
.lb{font:800 11px Sora;letter-spacing:.08em;color:#6B7178;margin:14px 0 8px}
.pill{display:flex;background:#F2F4F5;border-radius:14px;padding:4px}.pill div{flex:1;text-align:center;padding:10px 0;border-radius:11px;font:800 15px Sora;color:#5B6169}.pill div small{display:block;font:600 11px Sora}.pill .on{background:#0D0F12;color:#fff}
.tiers div{display:flex;align-items:center;justify-content:space-between;border:1.5px solid #E3E6E9;border-radius:14px;padding:11px 12px;margin:0 0 7px;font:700 14px Sora}.tiers div small{font:600 12px Sora;color:#5B6169}.tiers div em{font-style:normal;font:800 11px Sora;background:#ECF8F1;color:#0B6B3A;border-radius:999px;padding:3px 8px;margin-left:8px}.tiers .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.tiers .r{display:flex;align-items:center;gap:10px}.tiers .r i{width:20px;height:20px;border-radius:50%;border:2px solid #CDD1D5}.tiers .on .r i{border:6px solid #0D0F12}
.cta{height:58px;border-radius:16px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;font:800 16px Sora;margin-top:14px}
.addl{font:600 12.5px Sora;color:#4B5157;margin-top:12px;display:flex;align-items:center;gap:8px}.addl b{width:20px;height:20px;border-radius:6px;background:#0D0F12;color:#fff;font:800 12px/20px Sora;text-align:center}
.mini{display:flex;justify-content:space-between;font:600 11.5px Sora;color:#6B7178;margin-top:14px;padding-top:12px;border-top:1px solid #EEF0F2}
.dsec{padding:34px 16px 6px}
.dsec h2{font:800 25px/1.1 Sora;margin:0 0 14px;letter-spacing:-.01em}.dsec h2 em{font-style:normal;color:#8E959E}
.ey{display:inline-block;font:800 10.5px Sora;letter-spacing:.1em;color:#A8B0B9;border:1px solid #2A2F35;border-radius:999px;padding:5px 10px;margin:0 0 10px}
.big{display:flex;gap:14px;align-items:center;background:#14171B;border:1px solid #23272D;border-radius:20px;padding:16px;margin:0 0 12px}.big b{font:800 40px Sora}.big small{font:600 12px Sora;color:#A8B0B9}
.rvs{display:flex;gap:10px;overflow:hidden;margin-right:-16px}.rv{flex:0 0 250px;background:#14171B;border:1px solid #23272D;border-radius:18px;padding:14px}.rv p{font:500 13px/1.5 Sora;color:#D5D9DE;margin:6px 0 10px}.rv small{font:700 11.5px Sora;color:#fff}
.tilt{position:relative;height:0}.tilt img{position:absolute;right:-30px;top:-120px;width:150px;transform:rotate(18deg);opacity:.95}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}.grid div{background:#14171B;border:1px solid #23272D;border-radius:16px;padding:14px}.grid i{font-style:normal;font-size:20px}.grid b{display:block;font:800 14px Sora;margin:8px 0 2px}.grid small{font:500 11.5px/1.4 Sora;color:#A8B0B9}
.setd{background:linear-gradient(160deg,#1B1F24,#101215);border:1px solid #2A2F35;border-radius:22px;padding:16px}
.setd .imgs{display:flex;gap:-10px;margin:0 0 12px}.setd .imgs img{width:70px;height:70px;border-radius:16px;object-fit:cover;border:2px solid #101215;margin-right:-14px;background:#0D0F12}
.setd p{font:500 13px/1.5 Sora;color:#C9CED4}.setd .row{display:flex;justify-content:space-between;align-items:center;margin-top:14px}.setd .row b{font:800 22px Sora}.setd .row span{background:#fff;color:#0D0F12;border-radius:14px;padding:13px 16px;font:800 13.5px Sora}
.lt{background:#F4F5F6;color:#0D0F12;border-radius:28px 28px 0 0;margin-top:34px;padding:28px 16px 10px}
.lt h2{font:800 23px/1.15 Sora;margin:0 0 12px}
.lt p.t{font:500 13.5px/1.6 Sora;color:#3A3F45;margin:0 0 10px}
.fq div{display:flex;justify-content:space-between;padding:15px 0;border-bottom:1px solid #E3E6E9;font:700 14px Sora}.fq div span{color:#9AA0A6}
.rg{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0}.rg span{background:#fff;border:1px solid #E3E6E9;border-radius:999px;padding:9px 12px;font:700 12px Sora}
.more2{display:flex;gap:10px;overflow:hidden;margin-right:-16px}.pc{flex:0 0 160px;background:#fff;border-radius:16px;overflow:hidden}.pc img{width:160px;height:150px;object-fit:cover;background:#0D0F12}.pc div{padding:9px}.pc b{display:block;font:800 13.5px Sora}.pc small{font:700 12.5px Sora}
.dis{font:500 11px/1.5 Sora;color:#6B7178;text-align:center;padding:18px 24px 30px;background:#F4F5F6}
'''
def B():
    h=HEAD%B_CSS+HDR+'<span class="tag">B · Dunkel Premium</span>'
    h+=f'''<div class="hero"><div class="bd"><span>★ Bestseller</span><span>✓ HPLC ≥ 99 %</span></div><img class="v" src="{IMG}"></div>
<div class="ttl"><h1>Retatrutide</h1><p class="rt">{stars()} 4,8 · 378 Bewertungen · Forschungspeptid</p>
<div class="chips"><span>✓ 99 % Reinheit</span><span>✓ LC-MS geprüft</span><span>✓ Versand aus DE</span><span>✓ Diskret</span></div></div>
<div class="card"><div class="pr"><b>79,90 €</b><span>● Auf Lager</span></div>
<p class="lb">GRÖSSE</p><div class="pill"><div class="on">10 mg<small>79,90 €</small></div><div>20 mg<small>149,90 €</small></div></div>
<p class="lb">MENGE – JE MEHR, DESTO GÜNSTIGER</p><div class="tiers">
<div class="on"><span class="r"><i></i>1 Ampulle</span><small>79,90 €</small></div>
<div><span class="r"><i></i>3 Ampullen<em>−10 %</em></span><small>215,73 €</small></div>
<div><span class="r"><i></i>5 Ampullen<em>−15 %</em></span><small>339,58 €</small></div>
<div><span class="r"><i></i>10 Ampullen<em>−20 %</em></span><small>639,20 €</small></div></div>
<div class="cta">In den Warenkorb · 79,90 €</div>
<p class="addl"><b>+</b> Bac Water 3 ml dazu (+9,90 €) – zum Anmischen</p>
<div class="mini"><span>🚚 2 Werktage</span><span>📦 Neutral</span><span>🔒 Vorkasse · Krypto</span></div></div>

<div class="dsec"><span class="ey">● KUNDENSTIMMEN</span><div class="tilt"><img src="{T['tilt']}"></div><h2>Das sagen Kunden<br><em>über Retatrutide.</em></h2>
<div class="big"><b>4,8</b><span>{stars()}<br><small>aus 378 Bewertungen</small></span></div>
<div class="rvs">'''+''.join(f'<div class="rv">{stars()}<p>„{t}“</p><small>{n}</small></div>' for n,t in REV)+f'''</div></div>

<div class="dsec"><span class="ey">● WARUM PEPTRIUM</span><h2>Qualität, die du<br><em>nachprüfen kannst.</em></h2>
<div class="grid"><div><i>🧪</i><b>HPLC ≥ 99 %</b><small>Jede Charge geprüft</small></div><div><i>🔬</i><b>LC-MS</b><small>Identität bestätigt</small></div><div><i>❄️</i><b>Lyophilisiert</b><small>Stabil gelagert</small></div><div><i>📦</i><b>Diskret</b><small>Neutral, mit DHL-Nr.</small></div></div></div>

<div class="dsec"><span class="ey">● KOMPLETT-SET</span><h2>Startklar<br><em>in einem Paket.</em></h2><div class="setd">
<div class="imgs"><img src="{T['reta']}"><img src="{T['bac']}"><img src="{T['spr']}"></div>
<p>Retatrutide 10 mg + Bac Water 3 ml + Insulinspritzen 10er – alles, was du für die Vorbereitung brauchst.</p>
<div class="row"><b>99,70 €</b><span>Set in den Warenkorb</span></div></div></div>

<div class="lt"><h2>Über Retatrutide</h2><p class="t">Retatrutide ist ein synthetisches Peptid, das in der Forschung als dreifacher Rezeptor-Agonist (GIP, GLP-1, Glukagon) untersucht wird. Geliefert als gefriergetrocknetes Pulver in 10- oder 20-mg-Ampullen …</p><p class="t"><b><u>Weiterlesen</u></b></p>
<div class="rg"><span>📘 10 mg oder 20 mg?</span><span>📘 Was ist „GLP-3“?</span><span>🧮 Peptid-Rechner</span><span>❄️ Lagerung</span></div>
<h2 style="margin-top:22px">Häufige Fragen</h2><div class="fq">'''+''.join(f'<div>{q}<span>+</span></div>' for q in FAQ)+f'''</div>
<h2 style="margin-top:26px">Auch beliebt</h2><div class="more2">'''+''.join(f'<div class="pc"><img src="{T[k]}"><div><b>{n}</b><small>{p}</small></div></div>' for k,n,p in [('cagri','Cagrilintid','104,90 €'),('pen','Retatrutide-Pen','ab 264,90 €'),('ghk','GHK-Cu','ab 39,90 €')])+f'''</div></div>
<p class="dis">{DIS}</p></body></html>'''
    return h

# ---------------- C: Schritt fuer Schritt ----------------
C_CSS='''
body{background:#fff}
.top{display:flex;gap:12px;padding:14px 16px 10px;align-items:center}
.top .im{flex:0 0 132px;height:160px;border-radius:20px;background:#0D0F12;overflow:hidden;display:flex;justify-content:center}.top .im img{height:160px}
.top h1{font:800 25px/1.1 Sora;margin:0 0 5px}.top .rt{font:600 12px Sora;color:#5B6169;margin:0 0 8px}.top .fr{font:500 12px/1.4 Sora;color:#5B6169}
.top .pr{font:800 22px Sora;margin-top:8px}.top .pr small{font:600 11.5px Sora;color:#2E9B57;display:block}
.thumbs{display:flex;gap:8px;padding:0 16px 6px}.thumbs span{width:48px;height:48px;border-radius:12px;background:#F2F4F5;overflow:hidden}.thumbs img{width:48px;height:48px;object-fit:cover}.thumbs span:first-child{outline:2px solid #0D0F12}
.steps{padding:6px 16px 0}
.st{border:1.5px solid #E3E6E9;border-radius:20px;padding:14px;margin:10px 0 0}
.st .h{display:flex;align-items:center;gap:9px;font:800 15px Sora;margin:0 0 11px}.st .h i{font-style:normal;width:24px;height:24px;border-radius:50%;background:#0D0F12;color:#fff;font:800 12px/24px Sora;text-align:center}.st .h small{margin-left:auto;font:600 12px Sora;color:#5B6169}
.st.done{background:#F7F8F9}.st.done .h{margin:0}.st.done .h i{background:#2E9B57}
.big{display:grid;grid-template-columns:1fr 1fr;gap:8px}.big div{border:1.5px solid #E3E6E9;border-radius:16px;padding:12px;font:800 18px Sora;position:relative;background:#fff}.big div small{display:block;font:600 12px Sora;color:#5B6169;margin-top:3px}.big .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}.big .on:after{content:'✓';position:absolute;right:10px;top:10px;width:20px;height:20px;border-radius:50%;background:#0D0F12;color:#fff;font:800 11px/20px Sora;text-align:center}
.q4{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}.q4 div{border:1.5px solid #E3E6E9;border-radius:14px;padding:10px 2px;text-align:center;font:800 17px Sora}.q4 div small{display:block;font:700 10.5px Sora;color:#2E9B57}.q4 div em{display:block;font:500 10px Sora;color:#5B6169;font-style:normal}.q4 .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.hint{font:600 12px Sora;color:#0B6B3A;background:#ECF8F1;border-radius:10px;padding:8px 10px;margin-top:9px}
.ad{display:flex;align-items:center;gap:10px;padding:8px 0;border-top:1px solid #EEF0F2}.ad:first-of-type{border-top:0}.ad img{width:44px;height:44px;border-radius:10px;object-fit:cover;background:#0D0F12}.ad .n{flex:1;font:700 13px Sora}.ad .n small{display:block;font:500 11px Sora;color:#5B6169}
.cb{width:24px;height:24px;border-radius:8px;border:2px solid #CDD1D5}.cb.on{background:#0D0F12;border-color:#0D0F12;color:#fff;font:800 13px/20px Sora;text-align:center}
.sum{margin:12px 16px 0;background:#0D0F12;color:#fff;border-radius:22px;padding:16px}
.sum .l{display:flex;justify-content:space-between;font:500 13px Sora;color:#C9CED4;padding:3px 0}.sum .l b{color:#fff;font-weight:700}
.sum .t{display:flex;justify-content:space-between;align-items:baseline;border-top:1px solid rgba(255,255,255,.12);margin-top:8px;padding-top:10px}.sum .t b{font:800 26px Sora}
.sum .btn{margin-top:12px;height:54px;border-radius:15px;background:#fff;color:#0D0F12;display:flex;align-items:center;justify-content:center;font:800 15.5px Sora}
.sum .fs{font:600 12px Sora;color:#7DDBA0;margin-top:10px;text-align:center}
.sec{padding:26px 16px 0}.sec h2{font:800 21px Sora;margin:0 0 12px}
.ic{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;text-align:center}.ic div{font:700 11px/1.3 Sora}.ic i{display:flex;font-style:normal;width:46px;height:46px;border-radius:14px;background:#F4F5F6;margin:0 auto 6px;align-items:center;justify-content:center;font-size:19px}
.tabs{display:flex;gap:6px;margin:0 0 12px}.tabs span{flex:1;text-align:center;border:1px solid #E3E6E9;border-radius:999px;padding:9px 0;font:700 12px Sora}.tabs span:first-child{background:#0D0F12;color:#fff;border-color:#0D0F12}
.tb{background:#F7F8F9;border-radius:18px;padding:14px}.tb p{font:500 13.5px/1.6 Sora;color:#3A3F45;margin:0 0 8px}
.kv div{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #E7E9EC;font:600 13px Sora}.kv div span{color:#6B7178;font-weight:500}
.rvl .rv{border-bottom:1px solid #EEF0F2;padding:12px 0}.rvl .rv p{font:500 13.5px/1.5 Sora;margin:4px 0}.rvl small{font:700 12px Sora;color:#5B6169}
.bar5 div{display:flex;align-items:center;gap:8px;font:600 12px Sora;margin:3px 0}.bar5 i{flex:1;height:6px;border-radius:9px;background:#EEF0F2;position:relative;overflow:hidden}.bar5 i b{position:absolute;left:0;top:0;bottom:0;background:#F5A524;border-radius:9px}
.fq div{display:flex;justify-content:space-between;padding:14px 0;border-bottom:1px solid #EEF0F2;font:700 14px Sora}.fq div span{color:#9AA0A6}
.more2{display:flex;gap:10px;overflow:hidden;margin-right:-16px}.pc{flex:0 0 160px;border:1px solid #EEF0F2;border-radius:16px;overflow:hidden}.pc img{width:160px;height:150px;object-fit:cover;background:#0D0F12}.pc div{padding:9px}.pc b{display:block;font:800 13.5px Sora}.pc small{font:700 12.5px Sora}
.dis{font:500 11px/1.5 Sora;color:#6B7178;text-align:center;padding:22px 24px 30px}
'''
def C():
    h=HEAD%C_CSS+HDR+'<span class="tag">C · Schritt für Schritt</span>'
    h+=f'''<div class="top"><div class="im"><img src="{IMG}"></div><div><h1>Retatrutide</h1><p class="rt">{stars()} 4,8 (378)</p><p class="fr">Forschungspeptid · HPLC ≥ 99 % · Lyophilisat</p><p class="pr">ab 79,90 €<small>● Auf Lager · in 2 Werktagen da</small></p></div></div>
<div class="thumbs"><span><img src="{T['reta']}"></span><span><img src="{T['bac']}"></span><span><img src="{T['tilt']}"></span><span style="display:flex;align-items:center;justify-content:center;font:700 11px Sora;color:#5B6169">+2</span></div>
<div class="steps">
<div class="st"><p class="h"><i>1</i>Größe wählen</p><div class="big"><div class="on">10 mg<small>79,90 €</small></div><div>20 mg<small>149,90 € · Mehr Inhalt</small></div></div></div>
<div class="st"><p class="h"><i>2</i>Menge wählen</p><div class="q4"><div>1<em>Stück</em></div><div class="on">3<small>−10 %</small></div><div>5<small>−15 %</small></div><div>10<small>−20 %</small></div></div><p class="hint">✓ Du sparst 23,97 € mit 3 Stück</p></div>
<div class="st"><p class="h"><i>3</i>Zubehör dazu?<small>optional</small></p>
<div class="ad"><img src="{T['bac']}"><span class="n">Bac Water 3 ml<small>Zum Anmischen · 9,90 €</small></span><span class="cb on">✓</span></div>
<div class="ad"><img src="{T['spr']}"><span class="n">Insulinspritzen 10er<small>9,90 €</small></span><span class="cb"></span></div></div></div>
<div class="sum"><div class="l"><span>3× Retatrutide 10 mg</span><b>215,73 €</b></div><div class="l"><span>Bac Water 3 ml</span><b>9,90 €</b></div><div class="l"><span>Versand</span><b>gratis</b></div>
<div class="t"><span style="font:600 13px Sora;color:#C9CED4">Gesamt</span><b>225,63 €</b></div><div class="btn">In den Warenkorb</div><p class="fs">✓ Gratisversand · 🎁 Injektionskit gratis ab 200 €</p></div>

<div class="sec"><div class="ic"><div><i>🧪</i>HPLC<br>≥ 99 %</div><div><i>🚚</i>2 Werk-<br>tage</div><div><i>📦</i>Neutral<br>verpackt</div><div><i>💬</i>Hilfe auf<br>Telegram</div></div></div>

<div class="sec"><h2>Produktinfo</h2><div class="tabs"><span>Über</span><span>Daten</span><span>Lagerung</span></div>
<div class="tb"><p>Retatrutide ist ein synthetisches Peptid, das in der Forschung als dreifacher Rezeptor-Agonist (GIP, GLP-1, Glukagon) untersucht wird …</p>
<div class="kv"><div><span>Reinheit</span>≥ 99 % (HPLC)</div><div><span>Identität</span>LC-MS</div><div><span>Form</span>Lyophilisiertes Pulver</div><div style="border:0"><span>Lagerung</span>−20 °C, lichtgeschützt</div></div></div>
<p style="font:700 13px Sora;margin-top:12px">🧮 Zum Peptid-Rechner → &nbsp; 📘 10 oder 20 mg? →</p></div>

<div class="sec"><h2>Bewertungen</h2><div style="display:flex;gap:16px;align-items:center;margin:0 0 6px"><div><b style="font:800 40px Sora">4,8</b><br>{stars()}<br><small style="font:600 11.5px Sora;color:#5B6169">378 Bewertungen</small></div>
</div>
<div class="rvl">'''+''.join(f'<div class="rv">{stars()}<p>„{t}“</p><small>{n}</small></div>' for n,t in REV[:3])+'''<p style="font:700 13px Sora;text-decoration:underline;margin-top:10px">Alle 378 Bewertungen</p></div></div>

<div class="sec"><h2>Häufige Fragen</h2><div class="fq">'''+''.join(f'<div>{q}<span>+</span></div>' for q in FAQ)+f'''</div></div>
<div class="sec"><h2>Passt dazu</h2><div class="more2">'''+''.join(f'<div class="pc"><img src="{T[k]}"><div><b>{n}</b><small>{p}</small></div></div>' for k,n,p in [('cagri','Cagrilintid','104,90 €'),('pen','Retatrutide-Pen','ab 264,90 €'),('ghk','GHK-Cu','ab 39,90 €')])+f'''</div></div>
<p class="dis">{DIS}</p></body></html>'''
    return h

for n,f in [('A',A),('B',B),('C',C)]:
    open(f'/home/user/novamira/drafts/produktseite-2026-10-28/variante-{n}.html','w').write(f())
print('ok')
