# Entwuerfe: 1 Partner-Landing, 2 GiroCode, 3 Nochmal bestellen
import segno, io, base64
U='https://peptrium.com/wp-content/uploads/'
T={'reta':U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-300x300.png','bac':U+'2026/08/F36AD2AF-7A25-405E-8801-88F12605CC69-300x300.png',
 'spr':U+'2026/08/insulinspritze-10er-pack-wide-300x300.png','pen':U+'2026/08/45D2BF40-474D-46BE-9C9E-885EE2E57738-300x300.png',
 'ghk':U+'2026/08/9FABD238-B0DE-40C2-87DB-B5240E33DEF3-300x300.png','glow':U+'2026/10/glow-peptrium-300x300.png',
 'logo':U+'2026/08/IMG_2644-cropped-300x300.png','hero':U+'2026/08/8792D35C-5DD0-4537-B142-4C882F5F04DC-768x1024.png'}
epc='BCD\n002\n1\nSCT\n\nSTM Prime Holdings LLC\nDE89370400440532013000\nEUR225.63\n\n\nBestellung 3312\n'
q=segno.make(epc,error='m'); b=io.BytesIO(); q.save(b,kind='svg',scale=5,border=0,dark='#0D0F12'); QR='data:image/svg+xml;base64,'+base64.b64encode(b.getvalue()).decode()
I={'cart':'<path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/><circle cx="10" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/>',
 'check':'<path d="M5 12.5 10 17l9-10"/>','copy':'<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a1 1 0 0 1 1-1h10"/>',
 'bolt':'<path d="M13 3 5 14h6l-1 7 8-11h-6z"/>','phone':'<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
 'repeat':'<path d="M4 12a8 8 0 0 1 13.7-5.7L20 8M20 4v4h-4M20 12a8 8 0 0 1-13.7 5.7L4 16M4 20v-4h4"/>','truck':'<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/>',
 'gift':'<rect x="4" y="9" width="16" height="11" rx="1.5"/><path d="M3 9h18M12 9v11M12 9c-2-4-6-4-6-1.5S10 9 12 9c2 0 6 1 6-1.5S14 5 12 9"/>',
 'shield':'<path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6z"/><path d="M9 12l2 2 4-4"/>'}
def ic(n,c='currentColor',s=18,w=1.9): return f'<svg viewBox="0 0 24 24" width="{s}" height="{s}" fill="none" stroke="{c}" stroke-width="{w}" stroke-linecap="round" stroke-linejoin="round" style="flex:none">{I[n]}</svg>'
HEAD='''<!doctype html><html><head><meta charset="utf-8"><link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>*{box-sizing:border-box}body{margin:0;background:#E3E6E9;font-family:Sora,sans-serif;color:#0D0F12;padding:26px;-webkit-font-smoothing:antialiased}p{margin:0}img{display:block}
.wrap{display:flex;gap:26px;align-items:flex-start}.col{width:390px}.cap{font:800 15px Sora;margin:0 0 4px}.cap small{display:block;font:500 12.5px/1.45 Sora;color:#5B6169;margin:2px 0 10px}
.ph{width:390px;height:844px;background:#fff;border-radius:34px;overflow:hidden;position:relative;border:8px solid #0D0F12;box-shadow:0 20px 50px rgba(0,0,0,.2)}
.hd{background:#0D0F12;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;color:#fff}.hd .lg{display:flex;align-items:center;gap:8px;font:700 17px Sora}.hd .lg img{width:28px;height:28px}
.ib{width:32px;height:32px;border-radius:10px;background:rgba(255,255,255,.08);display:inline-flex;align-items:center;justify-content:center;margin-left:6px}
.btn{height:52px;border-radius:15px;background:#0D0F12;color:#fff;display:flex;align-items:center;justify-content:center;gap:9px;font:800 15px Sora}
.st{color:#F5A524;letter-spacing:1px}
@@CSS@@</style></head><body><div class="wrap">'''
HDR=f'<div class="hd"><span class="lg"><img src="{T["logo"]}">Peptrium</span><span><span class="ib">{ic("cart","#fff",15)}</span></span></div>'

# ---------- 1 Partner-Landing ----------
C1='''
.ph1{background:#0D0F12;color:#fff}
.pb{margin:14px 14px 0;background:linear-gradient(135deg,#1B1F24,#121417);border:1px solid #2A2F35;border-radius:22px;padding:14px;display:flex;gap:12px;align-items:center}
.av{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#F5C9A8,#E58E6A);display:flex;align-items:center;justify-content:center;font:800 19px Sora;color:#3A1D0E;flex:none;box-shadow:0 0 0 3px #0D0F12,0 0 0 5px #7DDBA0}
.pb .t{flex:1}.pb .t small{display:block;font:700 10.5px Sora;letter-spacing:.1em;color:#7DDBA0}.pb .t b{display:block;font:800 17px/1.2 Sora;margin:3px 0 2px}.pb .t span{font:500 12px Sora;color:#A8B0B9}
.ok{margin:10px 14px 0;display:flex;align-items:center;gap:8px;background:#11261A;border:1px solid #1E4A30;border-radius:14px;padding:10px 12px;font:700 12.5px Sora;color:#9BE7B7}
.ok b{margin-left:auto;background:#2E9B57;color:#fff;border-radius:999px;padding:3px 9px;font:800 11px Sora}
.h{padding:20px 16px 8px}.h p{font:800 10.5px Sora;letter-spacing:.12em;color:#8E959E}.h h2{font:800 24px/1.12 Sora;margin:6px 0 0}.h h2 em{font-style:normal;color:#8E959E}
.pl{display:grid;grid-template-columns:1fr 1fr;gap:9px;padding:6px 14px 0}
.pc{background:#fff;color:#0D0F12;border-radius:18px;overflow:hidden}.pc img{width:100%;height:150px;object-fit:cover;background:#0D0F12}.pc div{padding:9px 10px 10px}.pc b{display:block;font:800 13.5px Sora}.pc .r{font:600 10.5px Sora;color:#5B6169;margin:2px 0 6px}.pc .p{display:flex;align-items:center;justify-content:space-between}.pc .p span{font:800 13.5px Sora}.pc .p s{font:600 10.5px Sora;color:#9AA0A6;margin-left:4px}.pc .p em.pv{display:block;font:700 10.5px Sora;color:#0B6B3A;font-style:normal;margin-top:1px}.pc .p i{width:30px;height:30px;border-radius:50%;background:#0D0F12;display:flex;align-items:center;justify-content:center}
.pc .tg{position:relative}.pc .tg em{position:absolute;left:8px;top:8px;background:#7DDBA0;color:#0D0F12;font:800 9.5px Sora;font-style:normal;border-radius:999px;padding:3px 7px}
.why{margin:14px 14px 0;display:flex;justify-content:space-between;font:600 11px Sora;color:#A8B0B9}.why span{display:flex;align-items:center;gap:5px}
.dr{position:absolute;left:0;right:0;bottom:0;top:120px;background:#fff;color:#0D0F12;border-radius:26px 26px 0 0;box-shadow:0 -20px 50px rgba(0,0,0,.4)}
.dr .t{display:flex;justify-content:space-between;padding:18px 18px 10px;font:800 18px Sora}
.dr .pv{margin:0 16px;background:#ECF8F1;border:1px solid #CBEBD8;border-radius:14px;padding:11px 12px;display:flex;align-items:center;gap:9px;font:600 12.5px/1.4 Sora;color:#0B6B3A}.dr .pv b{display:block;font-weight:800;color:#0D0F12}
.it{display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #EEF0F2}.it img{width:56px;height:56px;border-radius:12px;object-fit:cover;background:#0D0F12}.it .n{flex:1;font:700 14px Sora}.it .n small{display:block;font:500 12px Sora;color:#6B7178}.it b{font:800 14px Sora}
.tot{position:absolute;left:0;right:0;bottom:0;padding:14px 18px 22px;border-top:1px solid #EEF0F2}
.tot .r{display:flex;justify-content:space-between;font:600 13.5px Sora;margin:0 0 6px}.tot .r.g{color:#0B6B3A}.tot .r.b{font:800 17px Sora;margin:6px 0 12px}
'''
def s1():
    pcs=[('reta','Retatrutide','10 & 20 mg','71,91 €','79,90 €','Meistgekauft bei Lena'),('pen','Retatrutide-Pen','fertig gemischt','238,41 €','264,90 €',''),('ghk','GHK-Cu','50 & 100 mg','35,91 €','39,90 €',''),('glow','GLOW','70 mg Blend','125,91 €','139,90 €','Neu')]
    g=''.join(f'<div class="pc"><div class="tg">{f"<em>{t}</em>" if t else ""}<img src="{T[k]}"></div><div><b>{n}</b><p class="r"><span class="st">★★★★★</span> {sub}</p><div class="p"><span>{o}<em class="pv">mit Lenas Vorteil {p}</em></span><i>{ic("cart","#fff",14)}</i></div></div></div>' for k,n,sub,p,o,t in pcs)
    a=f'''<div class="col"><p class="cap">1 · Partner-Landing (oben auf der Startseite)<small>Nur für Besucher über einen Partner-Link. Name + Foto/Initiale des Partners, Vorteil schon aktiv, darunter die Produkte, die über diesen Partner am meisten gekauft werden. Normaler Preis + grüne Zeile „mit Lenas Vorteil“ (kein Streichpreis). Foto/Satz nur, wenn der Partner es selbst hinterlegt.</small></p>
<div class="ph ph1">{HDR}
<div class="pb"><span class="av">L</span><span class="t"><small>EMPFOHLEN VON</small><b>Lena</b><span>hat dir Peptrium empfohlen</span></span></div>
<div class="ok">{ic('check','#9BE7B7',16,2.6)}Dein Partner-Vorteil ist aktiv<b>−10 %</b></div>
<div class="h"><p>LENAS COMMUNITY KAUFT AM HÄUFIGSTEN</p><h2>Direkt loslegen –<br><em>ohne lange suchen.</em></h2></div>
<div class="pl">{g}</div>
<div class="why"><span>{ic('truck','#7DDBA0',13)}2 Werktage</span><span>{ic('shield','#7DDBA0',13)}HPLC ≥ 99 %</span><span>{ic('gift','#7DDBA0',13)}Gratis ab 150 €</span></div>
</div></div>'''
    b=f'''<div class="col"><p class="cap">… und im Warenkorb<small>Der Vorteil bleibt sichtbar bis zur Kasse – mit Partnername. Das gibt Sicherheit, dass der Rabatt wirklich gilt.</small></p>
<div class="ph ph1">{HDR}<div class="dr"><p class="t">Warenkorb <span style="color:#9AA0A6">✕</span></p>
<div class="pv">{ic('check','#0B6B3A',18,2.6)}<span><b>Empfohlen von Lena – 10 % Rabatt aktiv</b>wird automatisch abgezogen, kein Code nötig</span></div>
<div class="it"><img src="{T['reta']}"><span class="n">Retatrutide<small>10 mg · 2 Stück</small></span><b>159,80 €</b></div>
<div class="it"><img src="{T['bac']}"><span class="n">Bac Water 3 ml<small>1 Stück</small></span><b>9,90 €</b></div>
<div class="tot"><p class="r"><span>Zwischensumme</span><span>169,70 €</span></p><p class="r g"><span>Partner-Vorteil (Lena)</span><span>−16,97 €</span></p><p class="r g"><span>Versand</span><span>gratis</span></p><p class="r b"><span>Gesamt</span><span>152,73 €</span></p><div class="btn">Zur Kasse</div></div></div></div></div>'''
    return a+b

# ---------- 2 GiroCode ----------
C2='''
.ty{background:#0D0F12;color:#fff;padding:18px 16px 70px;text-align:center}
.ty .ck{width:52px;height:52px;border-radius:50%;background:#2E9B57;margin:0 auto 10px;display:flex;align-items:center;justify-content:center}
.ty h1{font:800 24px Sora;margin:0 0 4px}.ty p{font:500 13px Sora;color:#A8B0B9}
.qc{margin:-56px 14px 0;background:#fff;border-radius:24px;padding:16px;box-shadow:0 18px 40px rgba(13,15,18,.16);position:relative}
.qc .lb{font:800 10.5px Sora;letter-spacing:.1em;color:#B26B00;display:flex;align-items:center;gap:6px}
.qc h2{font:800 19px/1.2 Sora;margin:5px 0 12px}
.qr{display:flex;gap:14px;align-items:center}
.qr .box{width:150px;height:150px;flex:none;border-radius:16px;border:1.5px solid #E3E6E9;padding:10px;position:relative;background:#fff}.qr .box img{width:100%;height:100%}
.qr .box:after{content:'GiroCode';position:absolute;left:50%;bottom:-9px;transform:translateX(-50%);background:#0D0F12;color:#fff;font:800 9px Sora;border-radius:999px;padding:3px 8px}
.qr ol{margin:0;padding-left:18px;font:600 12.5px/1.5 Sora;color:#2A2F35}.qr ol li{margin:0 0 5px}
.amt{display:flex;justify-content:space-between;align-items:baseline;margin:18px 0 10px;padding-top:12px;border-top:1px solid #EEF0F2}.amt span{font:600 12.5px Sora;color:#5B6169}.amt b{font:800 26px Sora}
.rw{display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #F1F3F4}.rw span{width:112px;font:600 11.5px Sora;color:#6B7178;flex:none}.rw b{flex:1;font:700 13px Sora;font-variant-numeric:tabular-nums}.rw i{width:32px;height:32px;border-radius:10px;background:#F2F4F5;display:flex;align-items:center;justify-content:center}
.rw.vz b{color:#B26B00}
.fast{margin-top:12px;background:#FFF6E5;border:1px solid #FBE1B0;border-radius:14px;padding:10px 12px;display:flex;gap:9px;font:600 12px/1.45 Sora;color:#6B4400}
.cp{display:flex;align-items:center;gap:10px;border:1px solid #E7E9EC;border-radius:14px;padding:10px 10px 10px 12px;margin:0 0 7px}.cp span{flex:1;min-width:0}.cp small{display:block;font:600 10.5px Sora;color:#6B7178}.cp b{font:800 14px Sora;font-variant-numeric:tabular-nums}.cp i{font-style:normal;display:flex;align-items:center;gap:5px;background:#F2F4F5;border-radius:10px;padding:8px 10px;font:700 11.5px Sora}.cp.vz{border-color:#FBE1B0;background:#FFFBF2}.cp.vz b{color:#B26B00}
.fold{margin-top:10px;border:1.5px dashed #CDD1D5;border-radius:14px;padding:11px 12px;display:flex;align-items:center;gap:10px}.fold span{flex:1;display:flex;align-items:center;gap:10px}.fold b{display:block;font:800 13px Sora}.fold small{display:block;font:500 11px Sora;color:#6B7178}.fold em{font-style:normal;color:#6B7178}
.mob{margin-top:12px;display:flex;gap:8px}.mob span{flex:1;border:1.5px solid #E3E6E9;border-radius:14px;padding:11px 6px;text-align:center;font:700 12px Sora;display:flex;align-items:center;justify-content:center;gap:6px}
.ml{background:#F2F4F6;height:100%;padding:16px}
.ml .bx{background:#fff;border-radius:20px;overflow:hidden}
.ml .top{background:#0D0F12;color:#fff;padding:18px;text-align:center}.ml .top img{width:34px;margin:0 auto 6px}.ml .top b{display:block;font:800 19px Sora}.ml .top small{font:500 12px Sora;color:#A8B0B9}
.ml .in{padding:16px}
'''
def s2():
    rows=lambda vz=True: f'''<div class="rw"><span>Empfänger</span><b>STM Prime Holdings LLC</b><i>{ic('copy','#0D0F12',15)}</i></div>
<div class="rw"><span>IBAN</span><b>DE89 3704 0044 0532 0130 00</b><i>{ic('copy','#0D0F12',15)}</i></div>
<div class="rw vz"><span>Verwendungszweck</span><b>Bestellung 3312</b><i>{ic('copy','#0D0F12',15)}</i></div>'''
    a=f'''<div class="col"><p class="cap">2a · Danke-Seite am Handy<small>Am Handy kann man den eigenen Bildschirm nicht scannen – deshalb zuerst große Kopier-Zeilen (jede einzeln + „Alles kopieren“). Der QR-Code ist darunter einklappbar: als Bild speichern → in vielen Banking-Apps „Foto-Überweisung“ aus der Galerie.</small></p>
<div class="ph">{HDR}<div class="ty" style="padding-bottom:64px"><div class="ck">{ic('check','#fff',26,3)}</div><h1>Danke, Thomas!</h1><p>Bestellung #3312 ist eingegangen.</p></div>
<div class="qc"><p class="lb">⏳ LETZTER SCHRITT: ÜBERWEISEN</p><h2>Überweise 225,63 € –<br>mit 3 Tipps kopiert.</h2>
<div class="cp"><span><small>Betrag</small><b>225,63 €</b></span><i>{ic('copy','#0D0F12',15)}Kopieren</i></div>
<div class="cp"><span><small>IBAN</small><b>DE89 3704 0044 0532 0130 00</b></span><i>{ic('copy','#0D0F12',15)}Kopieren</i></div>
<div class="cp vz"><span><small>Verwendungszweck – bitte genau so</small><b>Bestellung 3312</b></span><i>{ic('copy','#0D0F12',15)}Kopieren</i></div>
<div class="cp"><span><small>Empfänger</small><b>STM Prime Holdings LLC</b></span><i>{ic('copy','#0D0F12',15)}Kopieren</i></div>
<div class="btn" style="margin-top:12px;height:50px">{ic('copy','#fff',17,2)}Alles kopieren</div>
<div class="fold"><span>{ic('phone','#0D0F12',17)}<b>QR-Code für die Banking-App</b><small>am PC scannen oder als Bild speichern</small></span><em>▾</em></div>
<div class="fast">{ic('bolt','#B26B00',18,2.2)}<span><b>Echtzeitüberweisung wählen</b> – dann ist das Geld sofort da und wir versenden schneller.</span></div></div></div></div>'''
    a2=f'''<div class="col"><p class="cap">2b · … QR aufgeklappt / am PC<small>Am PC steht der QR-Code direkt groß da (Handy-Kamera bzw. Banking-App drauf halten). Am Handy: „Als Bild speichern“ → Banking-App → Foto-Überweisung → Bild aus der Galerie.</small></p>
<div class="ph">{HDR}<div class="qc" style="margin:14px">
<p class="lb">GIROCODE</p><h2>Scannen statt tippen.</h2>
<div class="qr"><div class="box"><img src="{QR}"></div><ol><li>Banking-App öffnen</li><li>„Scannen“ / „Foto-Überweisung“</li><li>Code scannen &amp; bestätigen</li></ol></div>
<div class="btn" style="margin-top:20px;height:48px;background:#fff;color:#0D0F12;border:1.5px solid #0D0F12">↓ Als Bild speichern</div>
<p style="font:500 12px/1.5 Sora;color:#5B6169;margin-top:10px">Funktioniert mit vielen deutschen und österreichischen Banking-Apps (GiroCode / EPC-QR-Standard). Klappt es nicht: einfach die Kopier-Zeilen nutzen.</p>
<div class="amt"><span>Betrag</span><b>225,63 €</b></div>
<div class="rw vz"><span>Verwendungszweck</span><b>Bestellung 3312</b><i>{ic('copy','#0D0F12',15)}</i></div></div></div></div>'''
    b=f'''<div class="col"><p class="cap">2c · … in der Bestellmail<small>Wer am Handy bestellt, kann den Code nicht mit demselben Handy scannen – deshalb dort zusätzlich „Kopieren“. Am PC bestellt? QR mit dem Handy scannen. In der Mail steht derselbe Code (als Bild).</small></p>
<div class="ph"><div class="ml"><div class="bx"><div class="top"><img src="{T['logo']}"><b>Danke, Thomas! 🙌</b><small>Bestellung #3312 · noch nicht bezahlt</small></div>
<div class="in"><p style="font:800 15px Sora;margin:0 0 10px">So bezahlst du in 10 Sekunden</p>
<div class="qr"><div class="box" style="width:130px;height:130px"><img src="{QR}"></div><ol><li>Banking-App öffnen</li><li>QR-Code scannen</li><li>Bestätigen – fertig</li></ol></div>
<div class="amt"><span>Betrag</span><b>225,63 €</b></div>
<div class="rw"><span>Empfänger</span><b>STM Prime Holdings LLC</b></div><div class="rw"><span>IBAN</span><b>DE89 3704 0044 …</b></div><div class="rw vz"><span>Verwendungszweck</span><b>Bestellung 3312</b></div>
<div class="fast">{ic('bolt','#B26B00',18,2.2)}<span>Mit <b>Echtzeitüberweisung</b> ist das Geld sofort da – wir versenden schneller.</span></div></div></div></div></div></div>'''
    return a+a2+b

# ---------- 3 Nochmal bestellen ----------
C3='''
.acc{background:#F2F4F6;height:100%}
.acc .t{padding:16px 16px 6px;font:800 22px Sora}
.chips{display:flex;gap:6px;padding:4px 16px 10px;overflow:hidden}.chips span{flex:none;border:1px solid #E3E6E9;border-radius:999px;padding:7px 12px;font:700 12px Sora;background:#fff}.chips span.on{background:#0D0F12;color:#fff;border-color:#0D0F12}
.oc{background:#fff;margin:0 12px 10px;border-radius:20px;padding:14px}
.oc .h{display:flex;justify-content:space-between;align-items:center;margin:0 0 10px}.oc .h b{font:800 14px Sora}.oc .h small{display:block;font:500 11.5px Sora;color:#6B7178}
.pill{font:800 10.5px Sora;border-radius:999px;padding:4px 9px;background:#ECF8F1;color:#0B6B3A}
.th{display:flex;gap:6px;margin:0 0 12px}.th img{width:46px;height:46px;border-radius:11px;object-fit:cover;background:#0D0F12}.th span{width:46px;height:46px;border-radius:11px;background:#F2F4F5;display:flex;align-items:center;justify-content:center;font:700 11px Sora;color:#5B6169}
.oc .b2{display:flex;gap:8px}.oc .b2 .btn{flex:1;height:46px;font-size:13.5px}.oc .b2 .ghost{flex:0 0 auto;height:46px;padding:0 14px;border-radius:15px;border:1.5px solid #E3E6E9;display:flex;align-items:center;font:700 12.5px Sora}
.hint{margin:0 12px 10px;background:#0D0F12;color:#fff;border-radius:20px;padding:14px;display:flex;gap:12px;align-items:center}
.hint .i{width:42px;height:42px;border-radius:12px;background:#1C2025;display:flex;align-items:center;justify-content:center;flex:none}
.hint b{display:block;font:800 14px Sora}.hint small{font:500 12px/1.4 Sora;color:#A8B0B9}
.rb{background:#fff;height:100%}
.rb .bar{background:#0D0F12;color:#fff;padding:14px 16px;display:flex;align-items:center;gap:10px}.rb .bar b{font:800 15px Sora}.rb .bar small{display:block;font:500 11.5px Sora;color:#A8B0B9}
.rb .tt{padding:16px 16px 8px;font:800 20px Sora}
.rb .it{display:flex;align-items:center;gap:12px;padding:11px 16px;border-bottom:1px solid #EEF0F2}.rb .it img{width:56px;height:56px;border-radius:12px;object-fit:cover;background:#0D0F12}.rb .it .n{flex:1;font:700 14px Sora}.rb .it .n small{display:block;font:500 12px Sora;color:#6B7178}
.qty{display:flex;align-items:center;border:1.5px solid #E3E6E9;border-radius:999px;height:34px}.qty span{width:30px;text-align:center;font:700 16px Sora}.qty b{font:800 13px Sora;width:18px;text-align:center}
.rb .add{margin:12px 16px 0;border:1.5px dashed #CDD1D5;border-radius:14px;padding:11px 12px;display:flex;align-items:center;gap:10px;font:700 12.5px Sora}.rb .add img{width:38px;height:38px;border-radius:10px;object-fit:cover}.rb .add span{flex:1}.rb .add small{display:block;font:500 11px Sora;color:#6B7178}.rb .add i{font-style:normal;background:#0D0F12;color:#fff;border-radius:999px;padding:7px 11px;font:800 12px Sora}
.rb .ft{position:absolute;left:0;right:0;bottom:0;padding:14px 16px 22px;border-top:1px solid #EEF0F2}
.rb .ft p{display:flex;justify-content:space-between;font:600 13.5px Sora;margin:0 0 6px}.rb .ft p.g{color:#0B6B3A}.rb .ft p.b{font:800 17px Sora;margin:6px 0 12px}
.ml2{background:#F2F4F6;height:100%;padding:16px}.ml2 .bx{background:#fff;border-radius:20px;overflow:hidden}.ml2 .top{background:#0D0F12;color:#fff;padding:18px;text-align:center}.ml2 .top b{display:block;font:800 18px Sora}.ml2 .top small{font:500 12px Sora;color:#A8B0B9}.ml2 .in{padding:16px}
'''
def s3():
    a=f'''<div class="col"><p class="cap">3a · Kundenkonto: „Nochmal bestellen“<small>Bei jeder Bestellung ein großer Knopf. Ein Tipp legt dieselben Artikel (aktuelle Preise, nur was lieferbar ist) in den Warenkorb – Geschenke/Gutschriften nicht.</small></p>
<div class="ph"><div class="acc">{HDR}<p class="t">Meine Bestellungen</p><div class="chips"><span>Übersicht</span><span class="on">Bestellungen</span><span>Abo</span><span>Guthaben</span><span>Adressen</span></div>
<div class="hint"><span class="i">{ic('repeat','#7DDBA0',20,2)}</span><span><b>Zeit für Nachschub?</b><small>Deine letzte Bestellung ist 24 Tage her – mit einem Tipp nochmal bestellen.</small></span></div>
<div class="oc"><div class="h"><span><b>#3271 · 4. Okt.</b><small>3 Artikel · 169,70 €</small></span><span class="pill">✓ Zugestellt</span></div>
<div class="th"><img src="{T['reta']}"><img src="{T['bac']}"><img src="{T['spr']}"></div>
<div class="b2"><span class="btn">{ic('repeat','#fff',16,2.2)}Nochmal bestellen</span><span class="ghost">Details</span></div></div>
<div class="oc"><div class="h"><span><b>#3240 · 12. Sep.</b><small>2 Artikel · 114,80 €</small></span><span class="pill">✓ Zugestellt</span></div>
<div class="th"><img src="{T['ghk']}"><img src="{T['bac']}"></div>
<div class="b2"><span class="btn">{ic('repeat','#fff',16,2.2)}Nochmal bestellen</span><span class="ghost">Details</span></div></div></div></div></div>'''
    b=f'''<div class="col"><p class="cap">3b · … öffnet den Warenkorb fertig gefüllt<small>Mengen direkt anpassbar, passender Vorschlag dazu, Gratisversand-/Geschenk-Stand wie gewohnt. Funktioniert auch aus der Nachkauf-Mail und der Versandmail (Link mit Sicherheits-Schlüssel, ohne Login).</small></p>
<div class="ph"><div class="rb">{HDR}<div class="bar">{ic('check','#7DDBA0',20,2.6)}<span><b>Bestellung #3271 übernommen</b><small>3 Artikel liegen im Warenkorb – alles lieferbar</small></span></div>
<p class="tt">Warenkorb</p>
<div class="it"><img src="{T['reta']}"><span class="n">Retatrutide<small>10 mg · 79,90 €</small></span><span class="qty"><span>−</span><b>2</b><span>+</span></span></div>
<div class="it"><img src="{T['bac']}"><span class="n">Bac Water 3 ml<small>9,90 €</small></span><span class="qty"><span>−</span><b>1</b><span>+</span></span></div>
<div class="it"><img src="{T['spr']}"><span class="n">Insulinspritzen 10er<small>9,90 €</small></span><span class="qty"><span>−</span><b>1</b><span>+</span></span></div>
<div class="add"><img src="{T['reta']}"><span>Noch 1× Retatrutide<small>ab 3 Stück −10 % auf alle</small></span><i>+ 63,92 €</i></div>
<div class="ft"><p><span>Zwischensumme</span><span>179,60 €</span></p><p class="g"><span>Versand</span><span>gratis</span></p><p class="b"><span>Gesamt</span><span>179,60 €</span></p><div class="btn">Zur Kasse</div></div></div></div></div>'''
    c=f'''<div class="col"><p class="cap">3c · … und in der Versandmail<small>Die Versandmail wird sowieso von allen geöffnet – dort steht unter der Sendungsnummer ein dezenter „Später nachbestellen“-Kasten.</small></p>
<div class="ph"><div class="ml2"><div class="bx"><div class="top"><b>Dein Paket ist unterwegs 📦</b><small>Bestellung #3271 · DHL 00340434…</small></div>
<div class="in"><div class="btn" style="height:46px;font-size:14px">Sendung verfolgen</div>
<div style="margin-top:16px;border:1px solid #E3E6E9;border-radius:16px;padding:14px">
<p style="font:800 14px Sora;margin:0 0 4px">Nachschub mit einem Klick</p><p style="font:500 12.5px/1.5 Sora;color:#5B6169;margin:0 0 10px">Speichere dir diesen Knopf – er legt genau diese Bestellung wieder in den Warenkorb.</p>
<div class="th"><img src="{T['reta']}"><img src="{T['bac']}"><img src="{T['spr']}"></div>
<div class="btn" style="height:44px;font-size:13.5px;background:#fff;color:#0D0F12;border:1.5px solid #0D0F12">{ic('repeat','#0D0F12',16,2.2)}Nochmal bestellen</div></div></div></div></div></div></div>'''
    return a+b+c

for n,css,fn in [('1-partner-landing',C1,s1),('2-girocode',C2,s2),('3-nochmal-bestellen',C3,s3)]:
    open(n+'.html','w').write(HEAD.replace('@@CSS@@',css)+fn()+'</div></body></html>')
print('ok')
