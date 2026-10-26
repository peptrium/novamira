# Animierte TikTok-Storys (eigener Account) – 1080x1920, 8 s. Namen nur angedeutet (R*TA …), Flaschen img/x-*.png.
# Render: node vid.js → anim-*.mp4 (Frames per Web-Animations-API, ffmpeg).
# Safe Zone: oben ~160, unten ~420, rechts ~140.
LOGO = 'https://peptrium.com/wp-content/uploads/2026/08/IMG_2644-cropped-300x300.png'
BATP = 'M0 4C3 1 6 1 8 3.5 9 1.5 10.5 1 12 2.6 13.5 1 15 1.5 16 3.5 18 1 21 1 24 4 21.5 4 19.6 5 18.6 7 16.8 6 14.6 6.4 12 9 9.4 6.4 7.2 6 5.4 7 4.4 5 2.5 4 0 4Z'


def bat(y, w, dur, delay, o=.9, rev=False):
    a = 'flyR' if rev else 'flyL'
    return (f'<div class="batw" style="top:{y}px;animation:{a} {dur}s linear {delay}s infinite">'
            f'<svg class="bat" style="width:{w}px;opacity:{o}" viewBox="0 0 24 12"><path d="{BATP}"/></svg></div>')


CSS = '''*{box-sizing:border-box;margin:0;padding:0}
body{width:1080px;height:1920px;overflow:hidden;font-family:Sora,sans-serif;color:#fff;background:#060304;position:relative}
.bg{position:absolute;inset:0;background:radial-gradient(70% 40% at 50% 100%,rgba(255,90,10,.35),transparent 70%),radial-gradient(55% 30% at 0% 10%,rgba(110,30,150,.35),transparent 70%),linear-gradient(180deg,#0a0508,#060304 50%,#120603)}
.moon{position:absolute;border-radius:50%;background:radial-gradient(circle at 40% 35%,#FFE2B0,#FF9A3C 45%,#C2410C 75%);box-shadow:0 0 120px 40px rgba(255,120,30,.45),0 0 300px 120px rgba(255,90,10,.25);animation:glow 3s ease-in-out infinite alternate}
@keyframes glow{to{box-shadow:0 0 170px 60px rgba(255,120,30,.6),0 0 360px 150px rgba(255,90,10,.3)}}
.fog{position:absolute;left:-30%;width:160%;height:420px;background:radial-gradient(40% 50% at 50% 50%,rgba(255,220,200,.11),transparent 70%);filter:blur(10px);animation:fog 8s ease-in-out infinite alternate}
@keyframes fog{to{transform:translateX(12%)}}
.batw{position:absolute;left:0}
.bat{fill:#0b0607;filter:drop-shadow(0 0 6px rgba(255,140,40,.4));animation:flap .28s ease-in-out infinite alternate;display:block}
@keyframes flap{to{transform:scaleY(.45)}}
@keyframes flyL{from{transform:translate(1180px,0)}50%{transform:translate(540px,-60px)}to{transform:translate(-200px,20px)}}
@keyframes flyR{from{transform:translate(-200px,0)}50%{transform:translate(540px,50px)}to{transform:translate(1180px,-30px)}}
.web{position:absolute;right:-30px;top:-30px;width:440px;opacity:.22}
.top{position:absolute;left:80px;top:170px;display:flex;align-items:center;gap:16px}.top img{width:64px}.top b{font-size:26px;letter-spacing:.32em}
.pill{display:inline-block;font-size:30px;font-weight:800;letter-spacing:.16em;color:#FFB547;border:2px solid rgba(255,122,26,.7);background:rgba(20,10,2,.7);border-radius:99px;padding:14px 28px}
h1{font-size:128px;line-height:.98;font-weight:800;letter-spacing:-.035em;text-shadow:0 6px 40px rgba(0,0,0,.6)}
h1 span{display:block}
.o{background:linear-gradient(90deg,#FF6A00,#FFB547);-webkit-background-clip:text;background-clip:text;color:transparent}
.sub{font-size:42px;color:#F0DDCB;line-height:1.35;font-weight:700}
.up{opacity:0;animation:up .6s cubic-bezier(.2,.9,.3,1.2) forwards}
@keyframes up{from{opacity:0;transform:translateY(60px)}to{opacity:1;transform:none}}
.pop{opacity:0;animation:pop .5s cubic-bezier(.2,1.4,.4,1) forwards}
@keyframes pop{from{opacity:0;transform:scale(.4)}to{opacity:1;transform:none}}
.drop{opacity:0;animation:drop .7s cubic-bezier(.25,1.3,.5,1) forwards}
@keyframes drop{from{opacity:0;transform:translateY(-700px) rotate(-14deg)}to{opacity:1;transform:none}}
.vials{position:absolute;left:0;right:60px;display:flex;justify-content:center;align-items:flex-end}
.vials img{display:block;filter:drop-shadow(0 30px 40px rgba(0,0,0,.8))}
.vw{position:relative;opacity:0;display:flex;flex-direction:column;align-items:center}
.dimmer{animation:dim .6s ease forwards}
@keyframes dim{to{filter:brightness(.45) drop-shadow(0 30px 40px rgba(0,0,0,.8))}}
.lit{animation:lit 1.2s ease-in-out infinite alternate}
@keyframes lit{from{filter:drop-shadow(0 0 30px rgba(255,122,26,.5)) drop-shadow(0 30px 40px rgba(0,0,0,.8))}to{filter:drop-shadow(0 0 90px rgba(255,122,26,1)) drop-shadow(0 30px 40px rgba(0,0,0,.8))}}
.stamp{position:absolute;top:30%;left:50%;font-size:56px;font-weight:800;letter-spacing:.08em;color:#140A02;background:linear-gradient(90deg,#FF6A00,#FFB547);padding:12px 30px;border-radius:14px;box-shadow:0 10px 50px rgba(255,122,26,.8);opacity:0;transform:translate(-50%,-50%) rotate(-12deg);animation:slam .45s cubic-bezier(.3,1.6,.5,1) forwards}
@keyframes slam{from{opacity:0;transform:translate(-50%,-50%) rotate(-12deg) scale(3)}to{opacity:1;transform:translate(-50%,-50%) rotate(-12deg) scale(1)}}
.cta{position:absolute;left:80px;right:150px;bottom:440px;text-align:center}
.url{display:block;background:linear-gradient(90deg,#FF6A00,#FFB547);color:#140A02;font-size:78px;font-weight:800;letter-spacing:-.01em;border-radius:28px;padding:28px 20px;box-shadow:0 16px 60px rgba(255,106,0,.55)}
.url small{display:block;font-size:24px;font-weight:700;letter-spacing:.14em;opacity:.75;margin-bottom:2px}
.pulse{animation:pulse 1s ease-in-out infinite alternate}
@keyframes pulse{to{transform:scale(1.05);box-shadow:0 16px 90px rgba(255,106,0,.9)}}
.or{font-size:32px;color:#E7D6C6;margin-top:18px;font-weight:700}
.foot{position:absolute;left:80px;right:150px;bottom:395px;font-size:20px;color:#7d7068;text-align:center}
.date{display:inline-flex;gap:14px;align-items:center;font-size:40px;font-weight:800;background:rgba(255,255,255,.08);border:2px solid rgba(255,255,255,.2);border-radius:99px;padding:16px 30px}
.ticket{display:flex;border-radius:28px;overflow:hidden;box-shadow:0 20px 80px rgba(255,106,0,.35)}
.ticket .a{background:linear-gradient(160deg,#FF6A00,#FFB547);color:#140A02;padding:30px 32px;font-weight:800;font-size:32px;letter-spacing:.1em;display:flex;align-items:center;writing-mode:vertical-rl;transform:rotate(180deg)}
.ticket .b{flex:1;background:#140c08;border:2px solid rgba(255,122,26,.6);border-left:4px dashed rgba(255,181,71,.7);padding:34px 36px}
.ticket .b .t2{font-size:120px;font-weight:800;line-height:1}.ticket .b .t3{font-size:36px;color:#E7D6C6;margin-top:14px;font-weight:600}
.lbl small{font-size:24px;display:block}.lbl{width:210px;margin-top:16px;line-height:1.1;text-align:center;font-size:34px;font-weight:800;color:#FFB547}
.lbl.g{color:#7FD8A6}
.slots{display:flex;gap:26px;justify-content:center}
.slot{width:230px;height:400px;border-radius:30px;border:3px dashed rgba(255,181,71,.45);display:flex;align-items:flex-end;justify-content:center;position:relative;padding-bottom:20px}
.slot .n{position:absolute;top:-30px;left:50%;transform:translateX(-50%);width:64px;height:64px;border-radius:50%;background:#1c120c;border:3px solid rgba(255,181,71,.6);color:#FFB547;font-weight:800;font-size:32px;display:flex;align-items:center;justify-content:center;z-index:2}
.slot img{height:340px}
.fill{animation:fillS .4s ease forwards}
@keyframes fillS{to{border-style:solid;border-color:rgba(255,181,71,.9);background:rgba(255,122,26,.08)}}
.hour{display:inline-block;animation:turn 2s ease-in-out infinite}
@keyframes turn{0%,70%{transform:rotate(0)}100%{transform:rotate(180deg)}}
'''
WEB = '<svg class="web" viewBox="0 0 100 100" fill="none" stroke="#fff" stroke-width=".6"><path d="M100 0 0 100M100 0 30 100M100 0 60 100M100 0 0 70M100 0 0 40"/><path d="M80 0q5 15 20 20M60 0q8 30 40 40M40 0q10 45 60 60M20 0q12 60 80 80"/></svg>'


def d(t):
    return f'style="animation-delay:{t}s"'


def page(body, moon=(540, 1010, 560), cta_t=3.0, sub='Einfach im Browser eintippen'):
    mx, my, ms = moon
    deco = f'<div class="moon" style="left:{mx - ms // 2}px;top:{my - ms // 2}px;width:{ms}px;height:{ms}px"></div>' if ms else ''
    deco += WEB + '<div class="fog" style="top:1260px"></div>'
    deco += bat(820, 130, 6.5, -1) + bat(1150, 80, 8, -4, .8, True) + bat(700, 70, 9, -6, .7)
    c = (f'<div class="cta"><div class="up" {d(cta_t)}><div class="url pulse" style="animation-delay:{cta_t + .6}s">'
         f'<small>JETZT AUF</small>peptrium.com</div></div><div class="or up" {d(cta_t + .25)}>{sub}</div></div>')
    return ('<!doctype html><html lang="de"><head><meta charset="utf-8"><link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet"><style>'
            + CSS + '</style></head><body><div class="bg"></div>' + deco
            + f'<div class="top"><img src="{LOGO}"><b>PEPTRIUM</b></div>' + body + c
            + '<div class="foot">Nur für Laborforschung · nicht für den menschlichen Gebrauch</div></body></html>')


T = '<div style="position:absolute;left:80px;right:150px;top:280px">'


def vials(names, free, top, h, gap, t0, step=.3, stamp_t=None, labels=None):
    out = f'<div class="vials" style="top:{top}px;gap:{gap}px{";align-items:flex-start" if labels else ""}">'
    for i, n in enumerate(names):
        hh = h if i == free else int(h * .88)
        cls = ''
        if free is not None:
            cls = 'lit' if i == free else 'dimmer'
        st = f'animation-delay:{stamp_t}s' if (free is not None and i != free) else ''
        out += f'<div class="vw drop" {d(t0 + i * step)}><img class="{cls}" style="height:{hh}px;{st}" src="img/x-{n}.png">'
        if i == free and stamp_t:
            out += f'<div class="stamp" {d(stamp_t)}>GRATIS</div>'
        if labels:
            lb, g = labels[i]
            out += f'<div class="lbl{" g" if g else ""}">{lb}</div>'
        out += '</div>'
    return out + '</div>'


P = {}
P['1-hook'] = page(T + f'<div class="pill up" {d(.1)}>🎃 HALLOWEEN-AKTION</div>'
                   f'<h1 style="margin-top:28px"><span class="up" {d(.3)}>Jede 3.</span><span class="up" {d(.5)}>Flasche</span><span class="up o" {d(.7)}>gratis.</span></h1>'
                   f'<div class="date pop" style="margin-top:30px;animation-delay:2.6s">📅 Fr 30.10. – So 1.11.</div></div>'
                   + vials(['reta', 'ghk', 'mots'], 2, 875, 335, -10, 1.0, stamp_t=2.1), moon=(560, 1060, 500), cta_t=3.0)
P['2-insider'] = page(T + f'<div class="pill up" {d(.1)}>🔑 NUR FÜR INSIDER</div>'
                      f'<h1 style="margin-top:28px;font-size:118px"><span class="up" {d(.3)}>6 Stunden</span><span class="up o" {d(.5)}>früher rein.</span></h1>'
                      f'<div class="ticket pop" style="margin-top:56px;animation-delay:1.2s"><div class="a">EARLY ACCESS</div><div class="b"><div class="t2">Do 18:00</div><div class="t3">alle anderen erst Fr 0:00</div></div></div>'
                      f'<div class="sub up" style="margin-top:50px;animation-delay:2.0s">Zugang gibt’s nur per Newsletter.</div></div>',
                      moon=(900, 640, 260), cta_t=2.7, sub='Newsletter eintragen · kostenlos')
P['3-neu'] = page(T + f'<div class="pill up" {d(.1)}>✨ NEU ZU HALLOWEEN</div>'
                  f'<h1 style="margin-top:28px;font-size:120px"><span class="up" {d(.3)}>3 neue.</span><span class="up o" {d(.5)}>+1 ist zurück.</span></h1></div>'
                  + vials(['cagri', 'mt2', 'glow', 'bpc'], None, 740, 380, 0, 1.0, step=.35,
                          labels=[('C*gri', 0), ('M*-2', 0), ('GL*W', 0), ('B*C<br><small>wieder da</small>', 1)]),
                  moon=(540, 980, 520), cta_t=2.8, sub='Alle zählen bei „jede 3. gratis“')
slot = ''
for i, n in enumerate(['reta', 'reta', 'ghk']):
    t = 1.2 + i * .7
    last = i == 2
    img = f'<img class="drop{" lit" if last else ""}" style="animation:drop .7s cubic-bezier(.25,1.3,.5,1) {t}s forwards{",lit 1.2s ease-in-out " + str(t + .7) + "s infinite alternate" if last else ""}" src="img/x-{n}.png">'
    slot += (f'<div class="slot fill" style="animation-delay:{t + .5}s"><div class="n">{i + 1}</div>{img}'
             + (f'<div class="stamp" style="top:40%;animation-delay:{t + .9}s">GRATIS</div>' if last else '') + '</div>')
P['4-so-gehts'] = page(T + f'<div class="pill up" {d(.1)}>🎃 SO EINFACH GEHT’S</div>'
                       f'<h1 style="margin-top:28px;font-size:120px"><span class="up" {d(.3)}>Leg 3 rein.</span><span class="up o" {d(.5)}>Zahl nur 2.</span></h1></div>'
                       f'<div style="position:absolute;left:60px;right:120px;top:800px"><div class="slots">{slot}</div>'
                       f'<div class="sub pop" style="text-align:center;margin-top:34px;animation-delay:3.4s">Automatisch · kein Code nötig</div></div>',
                       moon=(0, 0, 0), cta_t=3.8, sub='Nur Fr 30.10. – So 1.11.')
P['5-letzte-chance'] = page(T + f'<div class="pill up" {d(.1)}><span class="hour">⏳</span> LETZTE CHANCE</div>'
                            f'<h1 style="margin-top:28px"><span class="up" {d(.3)}>Letzter Tag.</span><span class="up o" {d(.5)}>Bis 23:59.</span></h1>'
                            f'<div class="sub up" style="margin-top:26px;animation-delay:.9s">Ab Montag zahlst du wieder alle 3.</div></div>',
                            moon=(540, 980, 480), cta_t=2.8)
P['5-letzte-chance'] = P['5-letzte-chance'].replace('<div class="cta">', vials(['bpc', 'reta', 'glow'], 1, 790, 380, -10, 1.2, stamp_t=2.2) + '<div class="cta">', 1)
for k, v in P.items():
    open('anim-' + k + '.html', 'w').write(v)
    print(k)
