from gen import page, LOGO, LEGAL, MONO
from gen2 import CH, card, close
head=f'<div style="display:flex;align-items:center;gap:8px"><img src="{LOGO}" style="width:28px;height:28px"><span style="font-size:11px;font-weight:800;letter-spacing:.3em;color:#E6E8EB">PEPTRIUM INSIDER</span></div>'
H=f'<div style="font-size:27px;line-height:1.15;font-weight:800;color:#fff;margin:16px 0 10px;letter-spacing:-.01em">Sichere dir <span style="{CH}">10 % Rabatt</span><br>auf deine erste Bestellung</div>'
silver='background:linear-gradient(135deg,#FFFFFF 0%,#D9DDE2 50%,#AEB4BC 100%);color:#0D0F12'
code=lambda blur:f'<div style="display:flex;align-items:center;justify-content:space-between;border:1.5px dashed #3A4048;border-radius:14px;padding:12px 14px;margin-bottom:14px"><span style="font-size:10px;font-weight:700;letter-spacing:.16em;color:#7D848D">DEIN CODE</span><span style="font-family:{MONO};font-size:17px;font-weight:800;color:#D9DDE2;letter-spacing:.06em;{"filter:blur(4px)" if blur else ""}">HALLO-7KQ2M</span></div>'
S1=page(card(close+head+H+'<div style="font-size:14px;line-height:1.55;color:#A3A9B1;margin-bottom:18px">Werde Peptrium-Insider und erhalte deinen persönlichen Code – dazu Neuheiten und neue Chargen zuerst.</div>'
 +f'<button class="b" style="{silver}">Ja, Code holen</button><div style="text-align:center;margin-top:14px;font-size:13px;color:#7D848D;text-decoration:underline">Nein danke</div>'))
S2=page(card(close+head+H+'<div style="font-size:14px;line-height:1.55;color:#A3A9B1;margin-bottom:14px">Wohin dürfen wir deinen Code schicken?</div>'
 +f'<input class="f" style="border-color:#C9CED6;background:#14171C" placeholder="Deine E-Mail-Adresse" value="">'
 +f'<div style="height:10px"></div><button class="b" style="{silver}">Code anfordern →</button><div class="legal">{LEGAL}</div>'))
S3=page(card(close+head+'<div style="font-size:44px;margin:14px 0 4px">📬</div><div style="font-size:26px;line-height:1.15;font-weight:800;color:#fff;margin-bottom:8px">Fast geschafft!</div>'
 +'<div style="font-size:14px;line-height:1.55;color:#A3A9B1;margin-bottom:14px">Wir haben dir eine Mail an <b style="color:#fff">max@beispiel.de</b> geschickt. Tippe dort auf <b style="color:#fff">„Anmeldung bestätigen“</b> – dann ist dein Code freigeschaltet.</div>'
 +code(True)
 +f'<div style="display:flex;gap:8px"><button class="b" style="flex:1;height:50px;font-size:14px;{silver}">Gmail öffnen</button><button class="b" style="flex:1;height:50px;font-size:14px;background:#14171C;color:#fff;border:1.5px solid #3A4048">Mail-App öffnen</button></div>'
 +'<div style="text-align:center;margin-top:14px;font-size:12px;color:#7D848D">Keine Mail bekommen? Schau im Spam-Ordner nach oder <u style="color:#C9CED6">erneut senden</u>.</div>'))
S4=page(f'''<div style="position:absolute;left:14px;bottom:28px;display:flex;align-items:center;gap:10px;background:#0B0D10;border:1px solid #3A4048;border-radius:999px;padding:8px 16px 8px 8px;box-shadow:0 10px 30px rgba(0,0,0,.35)">
<div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#F2F4F6,#8E959E 45%,#3A4048 70%,#C9CED6);color:#0D0F12;font-weight:900;font-size:13px;line-height:40px;text-align:center">10%</div>
<div><div style="font-size:14px;font-weight:800;color:#fff">10 % Rabatt sichern</div><div style="font-size:11px;color:#A3A9B1">für deine erste Bestellung</div></div>
<div style="margin-left:6px;color:#7D848D;font-size:15px">✕</div></div>''',dim=False)
for k,v in {'S1':S1,'S2':S2,'S3':S3,'S4':S4}.items(): open(k+'.html','w').write(v)
