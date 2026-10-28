# Entwuerfe Peptid-Rechner - 3 Varianten (R1 Alles auf einen Blick, R2 Assistent, R3 Regler + Bild)
U='https://peptrium.com/wp-content/uploads/'
T={'reta':U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png','bac':U+'2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png',
 'spr':U+'2026/08/insulinspritze-10er-pack-wide-300x300.png','ghk':U+'2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png',
 'mots':U+'2026/08/7255FFFB-6823-45F9-AC96-88637F1411CB-300x300.png','cagri':U+'2026/10/cagrilintid-peptrium-300x300.png',
 'logo':U+'2026/08/IMG_2644-cropped-300x300.png'}
DIS='Reine Rechenhilfe für die Vorbereitung im Labor – nicht für den menschlichen oder veterinärmedizinischen Gebrauch.'

def syringe(units,maxu=100,w=358,dark=False,label=True):
    """horizontale Insulinspritze (U-100) als SVG, Fuellung bis units"""
    bx,bw,by,bh=34,w-34-58,22,40   # Zylinder
    ink='#E6E9EC' if dark else '#2A2F35'; mute='#8E959E' if dark else '#9AA0A6'
    fill_w=bw*units/maxu
    s=f'<svg viewBox="0 0 {w} 96" width="{w}" height="96" xmlns="http://www.w3.org/2000/svg" style="display:block">'
    s+='<defs><linearGradient id="fl" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#9BE7B7"/><stop offset="1" stop-color="#2E9B57"/></linearGradient>'
    s+='<linearGradient id="gl" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".55"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs>'
    # Kolbenstange links
    pw=bx+fill_w
    s+=f'<rect x="2" y="{by+8}" width="10" height="{bh-16}" rx="3" fill="{ink}"/><rect x="10" y="{by+bh/2-3}" width="{bx-10+2}" height="6" fill="{ink}" opacity=".7"/>'
    s+=f'<rect x="{bx}" y="{by}" width="{bw}" height="{bh}" rx="7" fill="{"#1A1E23" if dark else "#fff"}" stroke="{ink}" stroke-width="2"/>'
    s+=f'<rect x="{bx+2}" y="{by+2}" width="{max(0,fill_w-2)}" height="{bh-4}" rx="5" fill="url(#fl)"/>'
    s+=f'<rect x="{bx+fill_w-3}" y="{by+2}" width="6" height="{bh-4}" fill="{ink}"/>'  # Kolbengummi
    # Skala
    step=1 if maxu<=30 else (1 if maxu<=50 else 2)
    for u in range(0,maxu+1,step):
        x=bx+bw*u/maxu; big=(u%10==0); mid=(u%5==0)
        h=14 if big else (9 if mid else 5)
        s+=f'<line x1="{x:.1f}" x2="{x:.1f}" y1="{by}" y2="{by+h}" stroke="{ink}" stroke-width="{1.4 if big else 1}" opacity="{.95 if big else .6}"/>'
        if big and label: s+=f'<text x="{x:.1f}" y="{by+bh+16}" font-family="Sora" font-size="10" font-weight="700" text-anchor="middle" fill="{mute}">{u}</text>'
    s+=f'<rect x="{bx}" y="{by}" width="{bw}" height="{bh/2}" rx="7" fill="url(#gl)"/>'
    # Nadel rechts
    nx=bx+bw
    s+=f'<path d="M{nx} {by+10} h12 l6 6 v8 l-6 6 h-12z" fill="{ink}"/><rect x="{nx+18}" y="{by+bh/2-1.2}" width="{w-nx-20}" height="2.4" fill="{mute}"/>'
    # Markierung
    s+=f'<line x1="{bx+fill_w:.1f}" x2="{bx+fill_w:.1f}" y1="6" y2="{by+bh+4}" stroke="#2E9B57" stroke-width="2" stroke-dasharray="3 3"/>'
    s+=f'<rect x="{bx+fill_w-22:.1f}" y="0" width="44" height="17" rx="8.5" fill="#2E9B57"/><text x="{bx+fill_w:.1f}" y="12.5" font-family="Sora" font-size="10.5" font-weight="800" text-anchor="middle" fill="#fff">{units} E</text>'
    return s+'</svg>'

HEAD='''<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=390">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box}body{margin:0;width:390px;font-family:Sora,sans-serif;color:#0D0F12;background:#fff;-webkit-font-smoothing:antialiased}p{margin:0}img{display:block}
.hd{background:#0D0F12;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;color:#fff}
.hd .lg{display:flex;align-items:center;gap:8px;font:700 18px Sora}.hd .lg img{width:30px;height:30px}
.ib{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.08);display:inline-flex;align-items:center;justify-content:center;margin-left:6px}.ib svg{width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2}
.tag{position:absolute;right:12px;top:12px;background:#FF5A1F;color:#fff;font:800 11px Sora;padding:5px 10px;border-radius:999px;z-index:20}
.qk{display:flex;gap:7px;overflow:hidden;margin-right:-16px}.qk span{flex:none;display:inline-flex;align-items:center;gap:6px;border:1px solid #E3E6E9;border-radius:999px;padding:5px 12px 5px 5px;font:700 12px Sora;background:#fff}.qk img{width:26px;height:26px;border-radius:50%;background:#0D0F12;object-fit:cover}.qk .on{background:#0D0F12;color:#fff;border-color:#0D0F12}
.acc{display:flex;align-items:center;gap:10px;padding:9px 0;border-top:1px solid #EEF0F2}.acc img{width:44px;height:44px;border-radius:10px;background:#0D0F12;object-fit:cover}.acc .n{flex:1;font:700 13px Sora}.acc .n small{display:block;font:500 11px Sora;color:#5B6169}.acc .b{height:34px;padding:0 13px;border-radius:999px;background:#0D0F12;color:#fff;font:800 12px/34px Sora}
.fq div{display:flex;justify-content:space-between;padding:14px 0;border-bottom:1px solid #EEF0F2;font:700 14px Sora}.fq div span{color:#9AA0A6}
.dis{font:500 11px/1.5 Sora;color:#6B7178;text-align:center;padding:20px 24px 28px}
@@CSS@@</style></head><body style="position:relative">'''
HDR='<div class="hd"><span class="lg"><img src="%s">Peptrium</span><span><span class="ib"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg></span><span class="ib"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></span></span></div>'%T['logo']
QK='<div class="qk"><span class="on"><img src="%s">Retatrutide 10 mg</span><span><img src="%s">Retatrutide 20 mg</span><span><img src="%s">GHK-Cu 50 mg</span><span><img src="%s">Mots-C 10 mg</span><span>Andere</span></div>'%(T['reta'],T['reta'],T['ghk'],T['mots'])
ACC=f'''<div class="acc" style="border:0"><img src="{T['reta']}"><span class="n">Retatrutide 10 mg<small>79,90 €</small></span><span class="b">+ Warenkorb</span></div>
<div class="acc"><img src="{T['bac']}"><span class="n">Bac Water 3 ml<small>9,90 € · zum Anmischen</small></span><span class="b">+ Warenkorb</span></div>
<div class="acc"><img src="{T['spr']}"><span class="n">Insulinspritzen 1 ml (10er)<small>9,90 €</small></span><span class="b">+ Warenkorb</span></div>'''
FQ='<div class="fq"><div>Was bedeutet „Einheiten“ auf der Spritze?<span>+</span></div><div>Wie viel Bac Water soll ich nehmen?<span>+</span></div><div>Welche Spritze passt?<span>+</span></div><div>Wie lange hält die angemischte Lösung?<span>+</span></div></div>'

# ===== R1: Alles auf einen Blick (Ergebnis oben, Stepper darunter) =====
R1_CSS='''
body{background:#F4F5F6}
.ttl{padding:18px 16px 10px;background:#fff}.ttl h1{font:800 26px/1.1 Sora;margin:0 0 4px}.ttl p{font:500 13px/1.45 Sora;color:#5B6169;margin:0 0 12px}
.res{position:relative;margin:0;background:#0D0F12;color:#fff;padding:16px 16px 14px;border-radius:0 0 26px 26px}
.res .l{font:800 10.5px Sora;letter-spacing:.1em;color:#8E959E}.res .big{font:800 40px/1 Sora;margin:4px 0 2px}.res .big em{font-style:normal;color:#7DDBA0}.res .big small{font:700 16px Sora;color:#C9CED4}
.res .sub{font:600 12.5px Sora;color:#A8B0B9;margin:0 0 10px}
.kp{display:flex;gap:6px;margin-top:10px}.kp div{flex:1;background:#1A1E23;border-radius:12px;padding:8px 4px;text-align:center;font:800 14px Sora}.kp small{display:block;font:600 10px Sora;color:#8E959E;margin-top:1px}
.row{background:#fff;margin:10px 12px 0;border-radius:18px;padding:13px 14px;display:flex;align-items:center;gap:10px}
.row .ic{flex:0 0 38px;height:38px;border-radius:12px;background:#F4F5F6;display:flex;align-items:center;justify-content:center;font-size:18px}
.row .t{flex:1;font:700 13.5px Sora}.row .t small{display:block;font:500 11.5px Sora;color:#6B7178}
.stp{display:flex;align-items:center;border:1.5px solid #E3E6E9;border-radius:14px;height:44px}.stp b{width:38px;text-align:center;font:600 20px Sora;color:#0D0F12}.stp span{min-width:64px;text-align:center;font:800 15px Sora}
.seg{display:flex;background:#F2F4F5;border-radius:12px;padding:3px}.seg span{padding:8px 10px;border-radius:9px;font:800 12.5px Sora;color:#5B6169}.seg .on{background:#fff;color:#0D0F12;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.card{background:#fff;margin:12px 12px 0;border-radius:22px;padding:16px}
.card h2{font:800 18px Sora;margin:0 0 10px}
.how{display:flex;gap:8px}.how div{flex:1;background:#F4F5F6;border-radius:14px;padding:10px 8px;text-align:center}.how b{display:block;font:800 13px Sora;margin:4px 0 2px}.how small{font:500 10.5px/1.35 Sora;color:#5B6169}.how i{font-style:normal;font-size:20px}
.save{display:flex;gap:8px;margin:10px 12px 0}.save span{flex:1;background:#fff;border-radius:14px;padding:12px;text-align:center;font:700 12.5px Sora}
'''
def R1():
    h=HEAD.replace("@@CSS@@",R1_CSS)+HDR+'<span class="tag">R1 · Alles auf einen Blick</span>'
    h+=f'''<div class="ttl"><h1>Peptid-Rechner</h1><p>Wähle dein Produkt – das Ergebnis rechnet sofort mit.</p>{QK}</div>
<div class="res"><p class="l">AUFZIEHEN BIS</p><p class="big"><em>10</em> <small>Einheiten</small></p><p class="sub">= 0,10 ml auf einer 1-ml-Spritze (U-100)</p>
{syringe(10,100,358,True)}
<div class="kp"><div>5 mg/ml<small>Konzentration</small></div><div>20×<small>pro Ampulle</small></div><div>0,10 ml<small>pro Entnahme</small></div></div></div>
<div class="row"><span class="ic">🧪</span><span class="t">Inhalt der Ampulle<small>laut Etikett</small></span><span class="stp"><b>−</b><span>10 mg</span><b>+</b></span></div>
<div class="row"><span class="ic">💧</span><span class="t">Bac Water<small>wie viel du zugibst</small></span><span class="stp"><b>−</b><span>2 ml</span><b>+</b></span></div>
<div class="row"><span class="ic">🎯</span><span class="t">Menge pro Entnahme<small>in mcg oder mg</small></span><span class="stp"><b>−</b><span>500 mcg</span><b>+</b></span></div>
<div class="row"><span class="ic">💉</span><span class="t">Spritze</span><span class="seg"><span>0,3 ml</span><span>0,5 ml</span><span class="on">1 ml</span></span></div>
<div class="save"><span>↗ Ergebnis teilen</span><span>↺ Zurücksetzen</span></div>
<div class="card"><h2>So wird gerechnet</h2><div class="how"><div><i>🧪</i><b>10 mg ÷ 2 ml</b><small>= 5 mg pro ml</small></div><div><i>🎯</i><b>0,5 mg ÷ 5</b><small>= 0,10 ml</small></div><div><i>💉</i><b>0,10 ml</b><small>= 10 Einheiten (U-100)</small></div></div></div>
<div class="card"><h2>Das brauchst du dafür</h2>{ACC}</div>
<div class="card"><h2>Häufige Fragen</h2>{FQ}</div>
<p class="dis">{DIS}</p></body></html>'''
    return h

# ===== R2: Assistent - eine Frage pro Bildschirm (4 Screens) =====
R2_CSS='''
body{width:auto;background:#D9DCDF;padding:0;display:flex;gap:20px}
.ph{width:390px;height:844px;background:#fff;position:relative;overflow:hidden;flex:none}
.pg{height:5px;background:#EEF0F2;margin:0}.pg b{display:block;height:5px;background:#0D0F12;border-radius:0 9px 9px 0}
.top{display:flex;justify-content:space-between;align-items:center;padding:14px 16px 0;font:700 12.5px Sora;color:#6B7178}
.q{padding:22px 16px 0}.q .n{font:800 11px Sora;letter-spacing:.1em;color:#6B7178}.q h2{font:800 27px/1.12 Sora;margin:6px 0 6px}.q p{font:500 13.5px/1.5 Sora;color:#5B6169}
.ch{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:20px 16px 0}.ch div{border:1.5px solid #E3E6E9;border-radius:18px;padding:18px 14px;font:800 22px Sora}.ch div small{display:block;font:600 12px Sora;color:#6B7178;margin-top:3px}.ch .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1.5px #0D0F12;background:#F7F8F9}
.ch .wide{grid-column:1/3;display:flex;align-items:center;gap:12px;font-size:16px}.ch .wide img{width:46px;height:46px;border-radius:12px;background:#0D0F12;object-fit:cover}
.ch .other{grid-column:1/3;font:700 14px Sora;text-align:center;padding:14px;color:#5B6169;border-style:dashed}
.btm{position:absolute;left:16px;right:16px;bottom:24px}.btm .go{height:56px;border-radius:16px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;font:800 16px Sora}
.btm .bk{text-align:center;font:700 13px Sora;color:#6B7178;margin-top:12px}
.vis{margin:18px 16px 0;background:#F4F5F6;border-radius:20px;padding:16px;display:flex;align-items:center;justify-content:center;gap:16px}
.vial{width:60px;height:96px;position:relative}.vial .c{position:absolute;left:14px;top:0;width:32px;height:14px;background:#2A2F35;border-radius:4px}.vial .g{position:absolute;left:4px;top:12px;width:52px;height:84px;border:2px solid #9AA0A6;border-radius:8px 8px 12px 12px;overflow:hidden;background:#fff}.vial .w{position:absolute;left:0;right:0;bottom:0;background:linear-gradient(#BFE6FF,#7CC3F2)}
.big{font:800 54px/1 Sora;text-align:center;margin:28px 0 4px}.big small{font:700 18px Sora;color:#6B7178}
.stp{display:flex;justify-content:center;gap:14px;margin-top:14px}.stp b{width:58px;height:58px;border-radius:18px;border:1.5px solid #E3E6E9;display:flex;align-items:center;justify-content:center;font:600 26px Sora}
.unit{display:flex;justify-content:center;margin-top:14px}.unit span{padding:8px 16px;border:1.5px solid #E3E6E9;font:800 13px Sora}.unit span:first-child{border-radius:999px 0 0 999px;background:#0D0F12;color:#fff;border-color:#0D0F12}.unit span:last-child{border-radius:0 999px 999px 0}
.chips{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:16px}.chips span{border:1px solid #E3E6E9;border-radius:999px;padding:7px 12px;font:700 12.5px Sora}
.rs{background:#0D0F12;color:#fff;height:100%}.rs .top{color:#8E959E}
.rs .ok{width:56px;height:56px;border-radius:50%;background:#2E9B57;margin:26px auto 10px;display:flex;align-items:center;justify-content:center;font:800 26px Sora}
.rs h2{text-align:center;font:800 15px Sora;color:#A8B0B9;margin:0}.rs .big{color:#fff;margin:6px 0 2px}.rs .big em{font-style:normal;color:#7DDBA0}.rs .sub{text-align:center;font:600 13px Sora;color:#A8B0B9;margin:0 0 16px}
.rs .sy{padding:0 16px}
.sumr{margin:16px 16px 0;background:#1A1E23;border-radius:18px;padding:6px 14px}.sumr div{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #2A2F35;font:600 13px Sora;color:#C9CED4}.sumr div:last-child{border:0}.sumr b{color:#fff}
.rs .btm .go{background:#fff;color:#0D0F12}.rs .btm .bk{color:#A8B0B9}
'''
def R2():
    h=HEAD.replace("@@CSS@@",R2_CSS)
    def sc(n,body,pct): return f'<div class="ph">{HDR}<div class="pg"><b style="width:{pct}%"></b></div>{body}</div>'
    s1=f'''<div class="top"><span>Schritt 1 von 3</span><span>✕</span></div><div class="q"><p class="n">WAS HAST DU?</p><h2>Welche Ampulle mischst du an?</h2><p>Tippe dein Produkt an – oder gib den Inhalt selbst ein.</p></div>
<div class="ch"><div class="wide on"><img src="{T['reta']}">Retatrutide 10 mg</div><div class="wide"><img src="{T['reta']}">Retatrutide 20 mg</div><div class="wide"><img src="{T['ghk']}">GHK-Cu 50 mg</div><div class="wide"><img src="{T['mots']}">Mots-C 10 mg</div><div class="other">Anderes Peptid – mg selbst eingeben</div></div>
<div class="btm"><div class="go">Weiter →</div></div><span class="tag" style="top:70px">R2 · Assistent</span>'''
    s2=f'''<div class="top"><span>Schritt 2 von 3</span><span>✕</span></div><div class="q"><p class="n">ANMISCHEN</p><h2>Wie viel Bac Water gibst du dazu?</h2><p>Mehr Wasser = weniger Konzentration, leichter abzulesen.</p></div>
<div class="vis"><div class="vial"><div class="c"></div><div class="g"><div class="w" style="height:50%"></div></div></div><span style="font:800 26px Sora">→</span><div style="font:800 15px Sora;text-align:center">5 mg<br><small style="font:600 12px Sora;color:#6B7178">pro ml</small></div></div>
<div class="ch"><div>1 ml<small>10 mg/ml</small></div><div class="on">2 ml<small>5 mg/ml</small></div><div>3 ml<small>3,3 mg/ml</small></div><div>Andere<small>selbst eingeben</small></div></div>
<div class="btm"><div class="go">Weiter →</div><p class="bk">← Zurück</p></div>'''
    s3=f'''<div class="top"><span>Schritt 3 von 3</span><span>✕</span></div><div class="q"><p class="n">MENGE</p><h2>Wie viel brauchst du pro Entnahme?</h2></div>
<p class="big">500 <small>mcg</small></p><div class="unit"><span>mcg</span><span>mg</span></div>
<div class="stp"><b>−</b><b>+</b></div>
<div class="chips"><span>100</span><span>250</span><span style="background:#0D0F12;color:#fff;border-color:#0D0F12">500</span><span>1000</span><span>2000</span></div>
<p style="text-align:center;font:600 12.5px Sora;color:#6B7178;margin-top:22px">Spritze: <b style="color:#0D0F12">1 ml (U-100)</b> · ändern</p>
<div class="btm"><div class="go">Ergebnis anzeigen</div><p class="bk">← Zurück</p></div>'''
    s4=f'''<div class="rs"><div class="pg" style="background:#1A1E23"><b style="width:100%;background:#7DDBA0"></b></div><div class="top"><span>Ergebnis</span><span>↗ Teilen</span></div>
<div class="ok">✓</div><h2>AUFZIEHEN BIS</h2><p class="big"><em>10</em> <small style="color:#A8B0B9">Einheiten</small></p><p class="sub">= 0,10 ml · 1-ml-Spritze</p>
<div class="sy">{syringe(10,100,358,True)}</div>
<div class="sumr"><div><span>Ampulle</span><b>Retatrutide 10 mg</b></div><div><span>Bac Water</span><b>2 ml → 5 mg/ml</b></div><div><span>Pro Entnahme</span><b>500 mcg</b></div><div><span>Reicht für</span><b>20 Entnahmen</b></div></div>
<div class="btm"><div class="go">Bac Water & Spritzen holen</div><p class="bk">↺ Neu berechnen</p></div></div>'''
    return h+sc(1,s1,33)+sc(2,s2,66)+sc(3,s3,100)+f'<div class="ph">{HDR}{s4}</div></body></html>'

# ===== R3: Regler + Bild (Ampulle -> Spritze) =====
R3_CSS='''
.ttl{padding:18px 16px 4px}.ttl h1{font:800 26px/1.1 Sora;margin:0 0 4px}.ttl p{font:500 13px/1.45 Sora;color:#5B6169;margin:0 0 12px}
.stage{margin:12px 12px 0;border-radius:24px;background:linear-gradient(160deg,#F4F6F8,#E8ECEF);padding:16px 12px 12px;position:relative}
.flow{display:flex;align-items:flex-end;justify-content:space-between;padding:0 6px}
.it{text-align:center;font:700 11.5px Sora;color:#4B5157}.it b{display:block;font:800 15px Sora;color:#0D0F12;margin-top:6px}
.vial{width:58px;height:92px;position:relative;margin:0 auto}.vial .c{position:absolute;left:13px;top:0;width:32px;height:14px;background:#2A2F35;border-radius:4px}.vial .g{position:absolute;left:3px;top:12px;width:52px;height:80px;border:2px solid #9AA0A6;border-radius:8px 8px 12px 12px;overflow:hidden;background:#fff}.vial .w{position:absolute;left:0;right:0;bottom:0}
.arrow{font:800 20px Sora;color:#9AA0A6;padding-bottom:40px}
.res{margin-top:14px;background:#0D0F12;color:#fff;border-radius:18px;padding:12px 12px 8px}
.res .l{display:flex;justify-content:space-between;align-items:baseline}.res .l span{font:800 10.5px Sora;letter-spacing:.1em;color:#8E959E}.res .l b{font:800 26px Sora;color:#7DDBA0}.res .l b small{font:700 13px Sora;color:#C9CED4}
.sl{margin:16px 16px 0}
.sl .h{display:flex;justify-content:space-between;align-items:baseline;font:700 13.5px Sora;margin:0 0 10px}.sl .h b{font:800 18px Sora;background:#F4F5F6;border-radius:10px;padding:4px 10px}
.track{position:relative;height:8px;border-radius:9px;background:#E7E9EC}.track i{position:absolute;left:0;top:0;bottom:0;border-radius:9px;background:#0D0F12}.track u{position:absolute;top:50%;width:28px;height:28px;margin:-14px 0 0 -14px;border-radius:50%;background:#fff;border:2px solid #0D0F12;box-shadow:0 3px 8px rgba(0,0,0,.18)}
.tk{display:flex;justify-content:space-between;font:600 10.5px Sora;color:#9AA0A6;margin-top:8px}
.syr{display:flex;gap:8px;margin:18px 16px 0}.syr div{flex:1;border:1.5px solid #E3E6E9;border-radius:14px;padding:10px 6px;text-align:center;font:800 14px Sora}.syr div small{display:block;font:600 10.5px Sora;color:#6B7178}.syr .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.card{margin:22px 16px 0}.card h2{font:800 19px Sora;margin:0 0 10px}
.tip{margin:18px 16px 0;background:#ECF8F1;border-radius:14px;padding:12px;font:600 12.5px/1.5 Sora;color:#0B6B3A}
'''
def R3():
    h=HEAD.replace("@@CSS@@",R3_CSS)+HDR+'<span class="tag">R3 · Regler + Bild</span>'
    h+=f'''<div class="ttl"><h1>Peptid-Rechner</h1><p>Zieh an den Reglern – Ampulle und Spritze zeigen sofort, was passiert.</p>{QK}</div>
<div class="stage"><div class="flow">
<div class="it"><div class="vial"><div class="c"></div><div class="g"><div class="w" style="height:12%;background:#E3E6E9"></div></div></div><b>10 mg</b>Pulver</div>
<span class="arrow">+</span>
<div class="it"><div class="vial"><div class="c" style="background:#5BA8DB"></div><div class="g"><div class="w" style="height:55%;background:linear-gradient(#BFE6FF,#7CC3F2)"></div></div></div><b>2 ml</b>Bac Water</div>
<span class="arrow">=</span>
<div class="it"><div class="vial"><div class="c"></div><div class="g"><div class="w" style="height:55%;background:linear-gradient(#C9F0D7,#7DDBA0)"></div></div></div><b>5 mg/ml</b>Lösung</div></div>
<div class="res"><div class="l"><span>AUFZIEHEN BIS</span><b>10 <small>Einheiten · 0,10 ml</small></b></div>{syringe(10,100,342,True)}</div></div>
<div class="sl"><p class="h">Inhalt der Ampulle<b>10 mg</b></p><div class="track"><i style="width:25%"></i><u style="left:25%"></u></div><div class="tk"><span>2</span><span>10</span><span>20</span><span>30</span><span>50 mg</span></div></div>
<div class="sl"><p class="h">Bac Water<b>2 ml</b></p><div class="track"><i style="width:50%"></i><u style="left:50%"></u></div><div class="tk"><span>0,5</span><span>1</span><span>2</span><span>3</span><span>5 ml</span></div></div>
<div class="sl"><p class="h">Menge pro Entnahme<b>500 mcg</b></p><div class="track"><i style="width:25%"></i><u style="left:25%"></u></div><div class="tk"><span>50</span><span>500</span><span>1000</span><span>1500</span><span>2000 mcg</span></div></div>
<div class="syr"><div>0,3 ml<small>30 E</small></div><div>0,5 ml<small>50 E</small></div><div class="on">1 ml<small>100 E</small></div></div>
<div class="tip">✓ Reicht für <b>20 Entnahmen</b> pro Ampulle.</div>
<div class="card"><h2>Das brauchst du dafür</h2>{ACC}</div>
<div class="card"><h2>Häufige Fragen</h2>{FQ}</div>
<p class="dis">{DIS}</p></body></html>'''
    return h

for n,f in [('R1',R1),('R2',R2),('R3',R3)]:
    open(f'/home/user/novamira/drafts/rechner-2026-10-28/{n}.html','w').write(f())
print('ok')
