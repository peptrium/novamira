LOGO='https://peptrium.com/wp-content/uploads/2026/08/IMG_2644-cropped-300x300.png'
HERO='https://peptrium.com/wp-content/uploads/sp-email/nl-hero-vials.jpg'
F="-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif"
MONO="'SFMono-Regular',Consolas,Menlo,monospace"
LEGAL='Mit der Anmeldung erhältst du unseren Newsletter (Bestätigung per E-Mail). Abmeldung jederzeit. <u>Datenschutz</u>'
def page(inner,dim=True):
    return f'''<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=390"><style>
*{{box-sizing:border-box}}body{{margin:0;width:390px;height:844px;overflow:hidden;font-family:{F};background:url(bg.png) top left/390px 844px no-repeat;position:relative}}
.dim{{position:absolute;inset:0;background:rgba(5,6,8,.62);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px)}}
.f{{width:100%;height:54px;border-radius:14px;border:1.5px solid #2C3138;background:#12151A;color:#fff;font-size:16px;padding:0 16px;font-family:{F}}}
.fl{{border-color:#D3D6DA;background:#fff;color:#0D0F12}}
.b{{width:100%;height:56px;border-radius:14px;border:0;font-size:16px;font-weight:800;font-family:{F}}}
.legal{{font-size:11px;line-height:1.5;color:#7D848D;margin-top:12px;text-align:center}}
</style></head><body>{'<div class="dim"></div>' if dim else ''}{inner}</body></html>'''
# A: Bottom-Sheet mit Chrom-Ticket (wie Insider-Bereich der Website)
A=page(f'''<div style="position:absolute;left:0;right:0;bottom:0;background:#0B0D10;border-radius:26px 26px 0 0;padding:12px 20px 26px;box-shadow:0 -10px 40px rgba(0,0,0,.5)">
<div style="width:44px;height:5px;border-radius:3px;background:#3A4048;margin:0 auto 14px"></div>
<div style="position:absolute;right:16px;top:16px;width:34px;height:34px;border-radius:50%;background:#1F242B;color:#A3A9B1;font-size:18px;line-height:34px;text-align:center">✕</div>
<div style="display:flex;border-radius:18px;overflow:hidden;margin:14px 0 18px;box-shadow:0 10px 30px rgba(0,0,0,.4)">
 <div style="flex:0 0 46%;background:linear-gradient(135deg,#F2F4F6 0%,#A9AFB7 40%,#5A616B 70%,#D9DDE2 100%);padding:20px 10px;text-align:center;color:#0D0F12"><div style="font-size:44px;font-weight:900;line-height:1">10&nbsp;%</div><div style="font-size:10px;font-weight:800;letter-spacing:.16em;margin-top:6px">INSIDER-RABATT</div></div>
 <div style="flex:1;background:#181C22;border-left:2px dashed #0B0D10;padding:16px 12px;text-align:center"><div style="font-size:10px;font-weight:700;letter-spacing:.16em;color:#7D848D">DEIN CODE</div><div style="font-family:{MONO};font-size:19px;font-weight:800;color:#D9DDE2;margin-top:8px;letter-spacing:.04em">HALLO-•••••</div><div style="font-size:11px;color:#7D848D;margin-top:6px">kommt per E-Mail</div></div>
</div>
<div style="font-size:28px;line-height:1.1;font-weight:800;color:#fff;letter-spacing:-.02em">10 % auf deine<br>erste Bestellung.</div>
<div style="font-size:14px;line-height:1.5;color:#A3A9B1;margin:10px 0 18px">Werde Peptrium-Insider: dein persönlicher Code, Nachschub-Erinnerungen und Neuheiten zuerst.</div>
<input class="f" placeholder="Deine E-Mail-Adresse"><div style="height:10px"></div>
<button class="b" style="background:#fff;color:#0D0F12">Code sichern →</button>
<div style="text-align:center;margin-top:12px;font-size:13px;color:#7D848D;text-decoration:underline">Nein danke</div>
<div class="legal">{LEGAL}</div></div>''')
# B: zentrierte Karte mit Produktbild
B=page(f'''<div style="position:absolute;left:18px;right:18px;top:96px;background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5)">
<div style="position:relative;line-height:0"><img src="{HERO}" style="width:100%;height:190px;object-fit:cover"><div style="position:absolute;right:12px;top:12px;width:34px;height:34px;border-radius:50%;background:rgba(0,0,0,.55);color:#fff;font-size:18px;line-height:34px;text-align:center">✕</div>
<div style="position:absolute;left:16px;bottom:-22px;background:#0D0F12;color:#fff;border:3px solid #fff;border-radius:16px;padding:10px 14px;line-height:1"><div style="font-size:30px;font-weight:900">10&nbsp;%</div></div></div>
<div style="padding:34px 20px 22px">
<div style="font-size:11px;font-weight:700;letter-spacing:.18em;color:#5B6169">PEPTRIUM NEWSLETTER</div>
<div style="font-size:26px;line-height:1.12;font-weight:800;color:#0D0F12;margin:8px 0 8px;letter-spacing:-.02em">Dein Willkommens-<br>geschenk wartet.</div>
<div style="font-size:14px;line-height:1.5;color:#5B6169;margin-bottom:16px">Melde dich an und spare <b style="color:#0D0F12">10 %</b> auf deine erste Bestellung. Dazu Neuheiten und Nachschub-Erinnerungen.</div>
<input class="f fl" placeholder="Deine E-Mail-Adresse"><div style="height:10px"></div>
<button class="b" style="background:#0D0F12;color:#fff">10 % sichern</button>
<div style="display:flex;justify-content:center;gap:14px;margin-top:14px;font-size:11px;font-weight:700;color:#5B6169"><span>✓ HPLC-geprüft</span><span>✓ Diskret</span><span>✓ 2 Werktage</span></div>
<div class="legal" style="color:#9AA0A8">{LEGAL}</div></div></div>''')
# C1: dezenter Teaser (Pill) – Seite bleibt bedienbar
C1=page(f'''<div style="position:absolute;left:14px;bottom:28px;display:flex;align-items:center;gap:10px;background:#0B0D10;border:1px solid #3A4048;border-radius:999px;padding:8px 16px 8px 8px;box-shadow:0 10px 30px rgba(0,0,0,.35)">
<div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#F2F4F6,#8E959E 45%,#3A4048 70%,#C9CED6);color:#0D0F12;font-weight:900;font-size:13px;line-height:40px;text-align:center">10%</div>
<div><div style="font-size:14px;font-weight:800;color:#fff">10 % Rabatt sichern</div><div style="font-size:11px;color:#A3A9B1">für deine erste Bestellung</div></div>
<div style="margin-left:6px;color:#7D848D;font-size:15px">✕</div></div>''',dim=False)
# C2: aufgeklappt (kompakt)
C2=page(f'''<div style="position:absolute;left:14px;right:14px;bottom:24px;background:#0B0D10;border:1px solid #2C3138;border-radius:22px;padding:18px 18px 16px;box-shadow:0 14px 40px rgba(0,0,0,.5)">
<div style="display:flex;align-items:center;gap:12px;margin-bottom:14px"><div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#F2F4F6,#8E959E 45%,#3A4048 70%,#C9CED6);color:#0D0F12;font-weight:900;font-size:16px;line-height:52px;text-align:center;flex:0 0 52px">10%</div>
<div style="flex:1"><div style="font-size:18px;font-weight:800;color:#fff;line-height:1.2">10 % auf deine erste Bestellung</div><div style="font-size:12px;color:#A3A9B1;margin-top:2px">Code kommt nach kurzer Bestätigung per E-Mail.</div></div><div style="color:#7D848D;font-size:16px;align-self:flex-start">✕</div></div>
<div style="display:flex;gap:8px"><input class="f" style="height:50px;flex:1" placeholder="E-Mail-Adresse"><button class="b" style="width:auto;height:50px;padding:0 18px;background:#fff;color:#0D0F12">Sichern</button></div>
<div class="legal" style="text-align:left">{LEGAL}</div></div>''')
for k,v in {'A':A,'B':B,'C1':C1,'C2':C2}.items(): open(k+'.html','w').write(v)
