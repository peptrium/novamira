from gen import page, LOGO, LEGAL, MONO
CH="background:linear-gradient(180deg,#FFFFFF 0%,#E6E8EB 45%,#9AA0A8 100%);-webkit-background-clip:text;background-clip:text;color:transparent"
card=lambda inner:f'<div style="position:absolute;left:14px;right:14px;top:200px;border-radius:24px;padding:2px;background:linear-gradient(135deg,#F2F4F6 0%,#5A616B 35%,#1A1D21 60%,#C9CED6 100%);box-shadow:0 24px 60px rgba(0,0,0,.6)"><div style="background:#0B0D10;border-radius:22px;padding:22px 20px 18px;position:relative">{inner}</div></div>'
close='<div style="position:absolute;right:14px;top:14px;width:34px;height:34px;border-radius:50%;background:#1A1D21;border:1px solid #2C3138;color:#C9CED6;font-size:16px;line-height:32px;text-align:center">✕</div>'
pill=lambda t:f'<div style="display:inline-block;border:1px solid #3A4048;background:#14171C;border-radius:999px;padding:7px 12px;font-size:11px;font-weight:800;letter-spacing:.14em;color:#C9CED6">{t}</div>'
form='<input class="f" style="border-color:#3A4048;background:#14171C" placeholder="Deine E-Mail-Adresse"><div style="height:10px"></div><button class="b" style="background:linear-gradient(135deg,#FFFFFF 0%,#D9DDE2 50%,#AEB4BC 100%);color:#0D0F12">Code sichern</button>'
legal=f'<div class="legal">{LEGAL}</div>'
# D1: kompakt, nur Text (Richtung europa-peptide)
D1=page(card(close+pill('🎁 WILLKOMMENSGESCHENK')
 +f'<div style="font-size:27px;line-height:1.15;font-weight:800;color:#fff;margin:16px 0 10px;letter-spacing:-.01em">Sichere dir <span style="{CH}">10 % Rabatt</span><br>auf deine erste Bestellung</div>'
 +'<div style="font-size:14px;line-height:1.55;color:#A3A9B1;margin-bottom:18px">Melde dich für unseren Newsletter an – dein persönlicher Code kommt nach kurzer Bestätigung per E-Mail. Dazu Neuheiten und neue Chargen zuerst.</div>'
 +form+legal))
# D2: wie D1, plus Logo-Kopf und Code-Vorschau
D2=page(card(close+f'<div style="display:flex;align-items:center;gap:8px"><img src="{LOGO}" style="width:28px;height:28px"><span style="font-size:11px;font-weight:800;letter-spacing:.3em;color:#E6E8EB">PEPTRIUM INSIDER</span></div>'
 +f'<div style="font-size:27px;line-height:1.15;font-weight:800;color:#fff;margin:16px 0 10px;letter-spacing:-.01em">Sichere dir <span style="{CH}">10 % Rabatt</span><br>auf deine erste Bestellung</div>'
 +'<div style="font-size:14px;line-height:1.55;color:#A3A9B1;margin-bottom:14px">Newsletter abonnieren, kurz bestätigen – dein Code kommt per E-Mail.</div>'
 +f'<div style="display:flex;align-items:center;justify-content:space-between;border:1.5px dashed #3A4048;border-radius:14px;padding:12px 14px;margin-bottom:14px"><span style="font-size:10px;font-weight:700;letter-spacing:.16em;color:#7D848D">DEIN CODE</span><span style="font-family:{MONO};font-size:17px;font-weight:800;color:#D9DDE2;letter-spacing:.06em">HALLO-•••••</span></div>'
 +form+legal))
open('D1.html','w').write(D1); open('D2.html','w').write(D2)
