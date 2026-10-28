# Rechner v2: realistische Insulinspritze (0 an der Nadel, Fluessigkeit an der Nadel, Kolben auf der Einheit) + Lupe
U='https://peptrium.com/wp-content/uploads/'
T={'reta':U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png','bac':U+'2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png',
 'spr':U+'2026/08/insulinspritze-10er-pack-wide-300x300.png','ghk':U+'2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png',
 'mots':U+'2026/08/7255FFFB-6823-45F9-AC96-88637F1411CB-300x300.png','logo':U+'2026/08/IMG_2644-cropped-300x300.png'}

SPEC={1:dict(max=100,minor=2,num=10,mid=None,ml='1 ml'),0.5:dict(max=50,minor=1,num=5,mid=None,ml='0,5 ml'),0.3:dict(max=30,minor=1,num=5,mid=None,ml='0,3 ml')}
_id=[0]
def syr(units,size=1,W=358,lens=True,dark=False):
    _id[0]+=1; k=_id[0]; sp=SPEC[size]; mx=sp['max']
    L=230                                   # Laenge Skala
    nd=46                                   # Nadel
    hub=16
    x0=nd+hub+8                             # Nullstrich
    bx=nd+hub                               # Zylinderanfang
    bend=x0+L+10                            # Zylinderende
    y=58; h=34                              # Zylinder oben / Hoehe
    mark=x0+L*units/mx
    rod_len=L+14
    stop_w=12
    rod_end=mark+stop_w+rod_len-(L)         # Stange ragt um den Hub heraus
    total=max(bend+18, mark+stop_w+rod_len*0.0+ (rod_len - (bend-mark-stop_w)) + 18)
    # Stange: vom Stopfen nach rechts, aussen sichtbar bis Daumenplatte
    thumb=mark+stop_w+ (bend-x0) +6
    total=thumb+14
    ink='#2A2F35'; scale_col='#1d2125'
    bg='#14171B' if dark else '#fff'
    s=f'<svg viewBox="0 0 {total:.0f} 136" width="100%" style="display:block;max-width:{W}px" xmlns="http://www.w3.org/2000/svg">'
    s+=f'''<defs>
<linearGradient id="bar{k}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff" stop-opacity=".95"/><stop offset=".45" stop-color="#EEF3F6" stop-opacity=".75"/><stop offset="1" stop-color="#D8E0E6" stop-opacity=".9"/></linearGradient>
<linearGradient id="liq{k}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#D7F5E3"/><stop offset=".5" stop-color="#9FE2BA"/><stop offset="1" stop-color="#6CCB95"/></linearGradient>
<linearGradient id="rub{k}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3A3F45"/><stop offset=".5" stop-color="#15181B"/><stop offset="1" stop-color="#2A2F35"/></linearGradient>
<linearGradient id="rod{k}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FFFFFF"/><stop offset="1" stop-color="#E2E6EA"/></linearGradient>
<linearGradient id="ndl{k}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E9ECEF"/><stop offset=".5" stop-color="#9AA3AC"/><stop offset="1" stop-color="#D5DADF"/></linearGradient>
<clipPath id="cb{k}"><rect x="{bx+1}" y="{y+1}" width="{bend-bx-2}" height="{h-2}" rx="5"/></clipPath>
<filter id="sh{k}" x="-10%" y="-30%" width="120%" height="180%"><feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000" flood-opacity=".18"/></filter>
</defs><g id="sg{k}">'''
    # Nadel + Ansatz (orange Kappe weg, Ansatz transparent orange wie Insulinspritze)
    s+=f'<rect x="2" y="{y+h/2-1.3}" width="{nd}" height="2.6" rx="1.3" fill="url(#ndl{k})"/>'
    s+=f'<path d="M{nd} {y+h/2-5} L{nd+hub} {y+5} L{nd+hub} {y+h-5} L{nd} {y+h/2+5}Z" fill="#F29A4A" opacity=".85"/>'
    # Kolbenstange (aussen) + Daumenplatte
    s+=f'<g filter="url(#sh{k})">'
    s+=f'<rect x="{mark+stop_w}" y="{y+h/2-6}" width="{thumb-mark-stop_w}" height="12" fill="url(#rod{k})" stroke="#C9CED4" stroke-width="1"/>'
    s+=f'<rect x="{mark+stop_w}" y="{y+h/2-1}" width="{thumb-mark-stop_w}" height="2" fill="#D5DADF"/>'
    s+=f'<rect x="{thumb}" y="{y-8}" width="8" height="{h+16}" rx="3" fill="url(#rod{k})" stroke="#C9CED4"/>'
    # Zylinder
    s+=f'<rect x="{bx}" y="{y}" width="{bend-bx}" height="{h}" rx="6" fill="url(#bar{k})" stroke="#AEB6BE" stroke-width="1.4"/>'
    s+='</g>'
    # Fluessigkeit von der Nadel bis zum Stopfen
    s+=f'<g clip-path="url(#cb{k})"><rect x="{bx}" y="{y}" width="{mark-bx}" height="{h}" fill="url(#liq{k})"/>'
    s+=f'<rect x="{bx}" y="{y+3}" width="{mark-bx}" height="5" fill="#fff" opacity=".45"/></g>'
    # Stopfen (Gummi) - Vorderkante = Strich
    s+=f'<g clip-path="url(#cb{k})"><rect x="{mark}" y="{y}" width="{stop_w}" height="{h}" fill="url(#rub{k})"/><rect x="{mark+3.5}" y="{y}" width="1.6" height="{h}" fill="#000" opacity=".35"/><rect x="{mark+7.5}" y="{y}" width="1.6" height="{h}" fill="#000" opacity=".35"/></g>'
    # Fingerflansch
    s+=f'<rect x="{bend-3}" y="{y-14}" width="7" height="{h+28}" rx="3" fill="#E9EDF0" stroke="#AEB6BE"/>'
    # Glanz
    s+=f'<rect x="{bx+3}" y="{y+2}" width="{bend-bx-6}" height="4" rx="2" fill="#fff" opacity=".7"/>'
    # Skala
    u=0
    while u<=mx:
        x=x0+L*u/mx; big=(u%sp['num']==0)
        hh=13 if big else 7
        s+=f'<line x1="{x:.2f}" x2="{x:.2f}" y1="{y+1}" y2="{y+1+hh}" stroke="{scale_col}" stroke-width="{1.3 if big else .8}"/>'
        if big: s+=f'<text x="{x:.2f}" y="{y+h+13}" font-family="Sora" font-size="9.5" font-weight="700" text-anchor="middle" fill="{scale_col}">{u}</text>'
        u+=sp['minor']
    s+='</g>'
    # Markierung oben
    s+=f'<line x1="{mark:.2f}" x2="{mark:.2f}" y1="{y-16}" y2="{y+h+4}" stroke="#2E9B57" stroke-width="1.6"/>'
    s+=f'<rect x="{mark-30:.2f}" y="{y-38}" width="60" height="22" rx="11" fill="#2E9B57"/><text x="{mark:.2f}" y="{y-23}" font-family="Sora" font-size="11.5" font-weight="800" text-anchor="middle" fill="#fff">{units} E</text>'
    s+=f'<text x="{x0-2}" y="{y+h+30}" font-family="Sora" font-size="9" font-weight="600" fill="#8E959E">0 = Nadelseite</text>'
    s+=f'<text x="{bend}" y="{y+h+30}" font-family="Sora" font-size="9" font-weight="600" text-anchor="end" fill="#8E959E">{sp["ml"]} · U-100</text>'
    s+='</svg>'
    if lens:
        # Lupe: 3,2x Vergroesserung um den Strich
        z=2.3; R=58; cx=R+2; cy=R+2
        tx=cx-mark*z; ty=cy-(y+h/2+9)*z
        lz=f'''<svg viewBox="0 0 {2*R+4} {2*R+4}" width="{2*R+4}" height="{2*R+4}" xmlns="http://www.w3.org/2000/svg" style="display:block">
<defs><clipPath id="lz{k}"><circle cx="{cx}" cy="{cy}" r="{R}"/></clipPath></defs>
<circle cx="{cx}" cy="{cy}" r="{R}" fill="#fff"/>
<g clip-path="url(#lz{k})"><g transform="translate({tx:.2f} {ty:.2f}) scale({z})">{s.split('<g id="sg%d">'%k)[1].split('</svg>')[0].rsplit('<line x1',1)[0]}</g>
<line x1="{cx}" x2="{cx}" y1="0" y2="{2*R+4}" stroke="#2E9B57" stroke-width="2"/></g>
<circle cx="{cx}" cy="{cy}" r="{R}" fill="none" stroke="#0D0F12" stroke-width="3"/></svg>'''
        # defs der Hauptgrafik werden im Lupen-SVG gebraucht -> mitkopieren
        defs=s.split('<defs>')[1].split('</defs>')[0]
        lz=lz.replace('<defs>','<defs>'+defs,1)
        return s,lz
    return s,None

HEAD='''<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=390">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box}body{margin:0;width:390px;font-family:Sora,sans-serif;color:#0D0F12;background:#F2F4F6;-webkit-font-smoothing:antialiased;position:relative}p{margin:0}img{display:block}
.hd{background:#0D0F12;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;color:#fff}
.hd .lg{display:flex;align-items:center;gap:8px;font:700 18px Sora}.hd .lg img{width:30px;height:30px}
.ib{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.08);display:inline-flex;align-items:center;justify-content:center;margin-left:6px}.ib svg{width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2}
@@CSS@@</style></head><body>'''
HDR='<div class="hd"><span class="lg"><img src="%s">Peptrium</span><span><span class="ib"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg></span><span class="ib"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg></span></span></div>'%T['logo']

CSS='''
.intro{background:#0D0F12;color:#fff;padding:18px 16px 70px}
.intro .ey{font:800 10.5px Sora;letter-spacing:.12em;color:#7DDBA0}
.intro h1{font:800 28px/1.08 Sora;margin:6px 0 6px;letter-spacing:-.01em}
.intro p{font:500 13px/1.5 Sora;color:#A8B0B9}
.qk{display:flex;gap:7px;overflow:hidden;margin:14px -16px 0 0}.qk span{flex:none;display:inline-flex;align-items:center;gap:7px;border:1px solid #2A2F35;border-radius:999px;padding:5px 12px 5px 5px;font:700 12px Sora;background:#15181C;color:#E6E9EC}.qk img{width:26px;height:26px;border-radius:50%;object-fit:cover}.qk .on{background:#fff;color:#0D0F12;border-color:#fff}
.res{background:#fff;margin:-56px 12px 0;border-radius:26px;padding:16px 14px 14px;box-shadow:0 18px 40px rgba(13,15,18,.14);position:relative}
.res .top{display:flex;justify-content:space-between;align-items:flex-start}
.res .l{font:800 10.5px Sora;letter-spacing:.1em;color:#6B7178}
.res .big{font:800 46px/1 Sora;letter-spacing:-.02em;margin:4px 0 2px}.res .big small{font:700 17px Sora;color:#5B6169;letter-spacing:0}
.res .sub{font:600 12.5px Sora;color:#5B6169}
.res .lens{position:absolute;right:12px;top:10px;filter:drop-shadow(0 6px 14px rgba(0,0,0,.18))}
.res .lens:after{content:'Lupe';position:absolute;left:50%;bottom:-4px;transform:translateX(-50%);background:#0D0F12;color:#fff;font:800 9px Sora;letter-spacing:.06em;border-radius:999px;padding:3px 7px}
.res .sy{margin:16px -10px 0}
.hint{background:#ECF8F1;color:#0B6B3A;border-radius:12px;padding:9px 11px;font:500 12px/1.45 Sora;margin:4px 0 0}.hint b{font-weight:800}
.facts{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:8px}.facts div{background:#F4F5F6;border-radius:14px;padding:9px 4px;text-align:center;font:800 14.5px Sora}.facts small{display:block;font:600 10px Sora;color:#6B7178;margin-top:1px}
.sec{margin:16px 12px 0}.sec .h{font:800 11px Sora;letter-spacing:.1em;color:#6B7178;margin:0 4px 8px}
.in{background:#fff;border-radius:20px;padding:13px 14px;margin:0 0 8px}
.in .r{display:flex;align-items:center;gap:10px}
.in .n{width:26px;height:26px;border-radius:50%;background:#0D0F12;color:#fff;font:800 12px/26px Sora;text-align:center;flex:none}
.in .t{flex:1;font:700 14px Sora}.in .t small{display:block;font:500 11.5px Sora;color:#6B7178}
.stp{display:flex;align-items:center;gap:4px}.stp b{width:36px;height:36px;border-radius:12px;background:#F2F4F5;display:flex;align-items:center;justify-content:center;font:600 20px Sora}.stp span{min-width:70px;text-align:center;font:800 17px Sora}
.ch{display:flex;gap:6px;margin:10px 0 0 36px}.ch span{flex:1;text-align:center;border:1px solid #E3E6E9;border-radius:10px;padding:7px 0;font:700 12px Sora;color:#4B5157}.ch .on{background:#0D0F12;color:#fff;border-color:#0D0F12}
.sz{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin:10px 0 0 36px}.sz div{border:1.5px solid #E3E6E9;border-radius:12px;padding:8px 4px;text-align:center;font:800 13px Sora}.sz small{display:block;font:600 10px Sora;color:#6B7178}.sz .on{border-color:#0D0F12;box-shadow:inset 0 0 0 1px #0D0F12}
.sz i{display:block;height:6px;margin:0 auto 6px;border-radius:3px;background:#D5DADF;position:relative}.sz i:before{content:'';position:absolute;left:-8px;top:2px;width:8px;height:2px;background:#9AA3AC}
.calc{background:#0D0F12;color:#fff;border-radius:20px;padding:14px}
.calc .row{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #22262B}.calc .row:last-child{border:0}
.calc .row i{font-style:normal;width:30px;height:30px;border-radius:10px;background:#1C2025;display:flex;align-items:center;justify-content:center;font:800 12px Sora;color:#7DDBA0;flex:none}
.calc .row span{flex:1;font:500 12.5px Sora;color:#A8B0B9}.calc .row b{font:800 13.5px Sora}
.acc{background:#fff;border-radius:20px;padding:6px 14px}
.acc .a{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #EEF0F2}.acc .a:last-child{border:0}.acc img{width:46px;height:46px;border-radius:12px;background:#0D0F12;object-fit:cover}.acc .an{flex:1;font:700 13px Sora}.acc .an small{display:block;font:500 11px Sora;color:#5B6169}.acc .b{width:36px;height:36px;border-radius:50%;background:#0D0F12;color:#fff;font:600 20px/36px Sora;text-align:center}
.allb{margin-top:8px;height:50px;border-radius:15px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;font:800 14px Sora}
.fq{background:#fff;border-radius:20px;padding:2px 14px}.fq div{display:flex;justify-content:space-between;padding:14px 0;border-bottom:1px solid #EEF0F2;font:700 13.5px Sora}.fq div:last-child{border:0}.fq span{color:#9AA0A6}
.share{display:flex;gap:8px;margin:10px 12px 0}.share span{flex:1;background:#fff;border-radius:14px;padding:12px;text-align:center;font:700 12.5px Sora}
.dis{font:500 11px/1.5 Sora;color:#6B7178;text-align:center;padding:20px 24px 30px}
.tag{position:absolute;right:12px;top:12px;background:#FF5A1F;color:#fff;font:800 11px Sora;padding:5px 10px;border-radius:999px;z-index:20}
'''
def page():
    main,lens=syr(10,1)
    h=HEAD.replace('@@CSS@@',CSS)+HDR+'<span class="tag">Rechner v2</span>'
    h+=f'''<div class="intro"><p class="ey">● PEPTID-RECHNER</p><h1>Wie viele Einheiten<br>aufziehen?</h1><p>Produkt antippen – alles andere ist schon eingetragen.</p>
<div class="qk"><span class="on"><img src="{T['reta']}">Retatrutide 10 mg</span><span><img src="{T['reta']}">Retatrutide 20 mg</span><span><img src="{T['ghk']}">GHK-Cu 50 mg</span><span><img src="{T['mots']}">Mots-C</span><span>Andere</span></div></div>
<div class="res"><p class="l">AUFZIEHEN BIS</p><p class="big">10 <small>Einheiten</small></p><p class="sub">= 0,10 ml · 1-ml-Spritze (U-100)</p>
<div class="sy">{main}</div><p class="hint"><b>So ablesen:</b> Die vordere Kante des schwarzen Kolbens steht genau auf dem Strich <b>10</b>.</p>
<div class="facts"><div>5 mg/ml<small>Konzentration</small></div><div>0,10 ml<small>pro Entnahme</small></div><div>20×<small>pro Ampulle</small></div></div></div>
<div class="sec"><p class="h">DEINE ANGABEN</p>
<div class="in"><div class="r"><span class="n">1</span><span class="t">Inhalt der Ampulle<small>steht auf dem Etikett</small></span><span class="stp"><b>−</b><span>10 mg</span><b>+</b></span></div><div class="ch"><span>5</span><span class="on">10</span><span>20</span><span>30</span><span>50</span></div></div>
<div class="in"><div class="r"><span class="n">2</span><span class="t">Bac Water dazu<small>Menge zum Anmischen</small></span><span class="stp"><b>−</b><span>2 ml</span><b>+</b></span></div><div class="ch"><span>1</span><span class="on">2</span><span>3</span><span>5</span></div></div>
<div class="in"><div class="r"><span class="n">3</span><span class="t">Menge pro Entnahme<small>mcg ⇄ mg</small></span><span class="stp"><b>−</b><span>500 mcg</span><b>+</b></span></div><div class="ch"><span>100</span><span>250</span><span class="on">500</span><span>1000</span><span>2000</span></div></div>
<div class="in"><div class="r"><span class="n">4</span><span class="t">Deine Spritze<small>Skala passt sich an</small></span></div><div class="sz"><div><i style="width:34px"></i>0,3 ml<small>30 Einheiten</small></div><div><i style="width:44px"></i>0,5 ml<small>50 Einheiten</small></div><div class="on"><i style="width:58px"></i>1 ml<small>100 Einheiten</small></div></div></div></div>
<div class="share"><span>↗ Ergebnis teilen</span><span>↺ Zurücksetzen</span></div>
<div class="sec"><p class="h">SO WIRD GERECHNET</p><div class="calc">
<div class="row"><i>1</i><span>10 mg Pulver ÷ 2 ml Bac Water</span><b>= 5 mg/ml</b></div>
<div class="row"><i>2</i><span>0,5 mg (500 mcg) ÷ 5 mg/ml</span><b>= 0,10 ml</b></div>
<div class="row"><i>3</i><span>0,10 ml × 100 (U-100-Skala)</span><b>= 10 Einheiten</b></div>
<div class="row"><i>4</i><span>10 mg ÷ 0,5 mg</span><b>= 20× pro Ampulle</b></div></div></div>
<div class="sec"><p class="h">DAS BRAUCHST DU DAFÜR</p><div class="acc">
<div class="a"><img src="{T['reta']}"><span class="an">Retatrutide 10 mg<small>79,90 €</small></span><span class="b">+</span></div>
<div class="a"><img src="{T['bac']}"><span class="an">Bac Water 3 ml<small>9,90 € · reicht für 2 ml</small></span><span class="b">+</span></div>
<div class="a"><img src="{T['spr']}"><span class="an">Insulinspritzen 1 ml (10er)<small>9,90 €</small></span><span class="b">+</span></div></div>
<div class="allb">Alle 3 in den Warenkorb · 99,70 €</div></div>
<div class="sec"><p class="h">FRAGEN</p><div class="fq"><div>Was bedeuten die „Einheiten“?<span>+</span></div><div>Warum steht die 0 an der Nadel?<span>+</span></div><div>Welche Spritze ist am genauesten?<span>+</span></div><div>Wie lange hält die angemischte Lösung?<span>+</span></div></div></div>
<p class="dis">Reine Rechenhilfe für die Vorbereitung im Labor – nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</p></body></html>'''
    return h

def compare():
    h=HEAD.replace('@@CSS@@','body{width:auto;background:#fff;padding:20px} .c{display:flex;flex-direction:column;gap:18px} .c h3{font:800 14px Sora;margin:0 0 4px}')
    h+='<div class="c">'
    for u,sz,t in [(10,1,'1 ml (100 Einheiten) · 10 E'),(10,0.5,'0,5 ml (50 Einheiten) · 10 E'),(10,0.3,'0,3 ml (30 Einheiten) · 10 E'),(25,0.3,'0,3 ml · 25 E'),(60,1,'1 ml · 60 E')]:
        m,_=syr(u,sz,W=520,lens=False)
        h+=f'<div><h3>{t}</h3><div style="width:520px">{m}</div></div>'
    return h+'</div></body></html>'

open('/home/user/novamira/drafts/rechner-2026-10-28/v2.html','w').write(page())
open('/home/user/novamira/drafts/rechner-2026-10-28/v2-spritzen.html','w').write(compare())
print('ok')
