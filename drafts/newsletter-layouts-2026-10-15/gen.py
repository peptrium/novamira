U='https://peptrium.com/wp-content/uploads/'
LOGO=U+'2026/08/IMG_2644-cropped-300x300.png'
F="-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,Helvetica,Arial,sans-serif"
MONO="'SFMono-Regular',Consolas,Menlo,monospace"
P={'reta':('Retatrutide','Stoffwechsel-Forschung','ab 74,90 €',U+'sp-email/nl-p-reta.jpg'),
 'ghk':('GHK-Cu','Kupferpeptid-Forschung','ab 39,90 €',U+'sp-email/nl-p-ghk.jpg'),
 'mots':('Mots-C','Mitochondrien-Forschung','ab 34,90 €',U+'sp-email/nl-p-mots.jpg'),
 'pen':('Peptrium-Pen','Fertig gemischt','ab 169,90 €',U+'sp-email/nl-p-pen.jpg'),
 'bac':('Bac Water','Zum Anmischen','6,90 €',U+'sp-email/nl-p-bac.jpg')}
TRUST=[('🧪','HPLC / LC-MS','jede Charge geprüft'),('💎','≥ 99 % Reinheit','unser Mindeststandard'),('📦','Diskret','neutral verpackt'),('🚚','2 Werktage','schneller Versand')]
CATS=['Alle Produkte','Peptrium-Pens','Zubehör']
def T(rows,bg,inner): # outer wrapper
    return (f'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:{bg};">'
     f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{bg};"><tr><td align="center" style="padding:0;">'
     f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:{inner};">{rows}</table></td></tr></table></body></html>')
def row(inner,style=''): return f'<tr><td style="font-family:{F};{style}">{inner}</td></tr>'
def grid(cells): # list of html cells, 2 per row
    r=''.join('<tr>'+''.join(f'<td width="50%" valign="top" style="width:50%;padding:5px;">{c}</td>' for c in cells[i:i+2])+'</tr>' for i in range(0,len(cells),2))
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0">{r}</table>'
def logo(dark,size=40,word=True):
    col='#E6E8EB' if dark else '#0D0F12'
    return f'<img src="{LOGO}" width="{size}" height="{size}" alt="Peptrium" style="display:block;margin:0 auto;width:{size}px;height:{size}px;border:0;">'+(f'<div style="font-size:13px;font-weight:800;letter-spacing:.34em;color:{col};margin-top:6px;padding-left:.34em;">PEPTRIUM</div>' if word else '')
def btn(t,bg,fg,w='100%',r=12,pad='15px 12px',fs=15,border=''):
    return f'<a href="#" style="display:block;width:{w};margin:0 auto;box-sizing:border-box;background:{bg};color:{fg};text-align:center;text-decoration:none;font-weight:800;font-size:{fs}px;padding:{pad};border-radius:{r}px;{border}">{t}</a>'
def unsub(c): return f'<div style="font-size:11px;color:{c};margin-top:14px;line-height:1.6;">Du bekommst diese Mail, weil du den Newsletter abonniert hast.<br><a href="#" style="color:{c};">Abmelden</a></div>'
LAB='Alle Produkte ausschließlich für Laborforschungszwecke. Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.'
CODE='HALLO-7KQ2M'
LAST=[('reta','Retatrutide – 10 mg','Menge: 2'),('bac','Bac Water – 10 ml','Menge: 1')]

# ---------- A: HELL & KLAR ----------
A_BG='#EEF1F4'; A_INK='#0D0F12'; A_MUT='#5B6169'; A_BTN='#0D0F12'
def A_top(title,sub,cta,hero):
    return row(logo(False,38)+f'<div style="font-size:28px;line-height:1.15;font-weight:800;color:{A_INK};margin:18px 0 8px;">{title}</div><div style="font-size:14px;line-height:1.5;color:{A_MUT};margin:0 auto 18px;max-width:420px;">{sub}</div>'+btn(cta,A_BTN,'#FFFFFF','86%'),
      'background-color:#FFFFFF;background-image:linear-gradient(180deg,#FFFFFF 0%,#E8ECF1 100%);padding:26px 18px 22px;text-align:center;')+\
      row(f'<img src="{U}sp-email/{hero}" width="600" style="display:block;width:100%;height:auto;border:0;">','padding:0;line-height:0;background:#E8ECF1;')
def A_trust():
    c=[f'<div style="background:#F4F7FA;border:1px solid #E1E6EC;border-radius:14px;padding:16px 8px;text-align:center;"><div style="width:40px;height:40px;line-height:40px;border-radius:50%;background:#FFFFFF;font-size:19px;margin:0 auto 8px;">{i}</div><div style="font-size:13px;font-weight:800;color:{A_INK};">{a}</div><div style="font-size:11px;color:{A_MUT};margin-top:2px;">{b}</div></div>' for i,a,b in TRUST]
    return row(grid(c),'padding:20px 11px 8px;background:#FFFFFF;')
def A_prod(keys,title):
    c=[f'<div style="border:1px solid #E1E6EC;border-radius:14px;overflow:hidden;background:#FFFFFF;text-align:center;"><img src="{P[k][3]}" width="150" style="display:block;width:100%;height:auto;border:0;"><div style="padding:10px 8px 12px;"><div style="font-size:14px;font-weight:800;color:{A_INK};">{P[k][0]}</div><div style="font-size:11px;color:{A_MUT};margin:2px 0 10px;">{P[k][1]} · {P[k][2]}</div>{btn("Ansehen",A_BTN,"#FFF",r=8,pad="9px 6px",fs=12)}</div></div>' for k in keys]
    return row(f'<div style="font-size:20px;font-weight:800;color:{A_INK};text-align:center;margin:0 0 12px;">{title}</div>'+grid(c),'padding:22px 11px 12px;background:#FFFFFF;')
def A_cats(): return row(f'<div style="font-size:17px;font-weight:800;color:{A_INK};margin-bottom:12px;">Nach Kategorie stöbern</div>'+''.join(btn(c,'#FFFFFF',A_INK,'86%',10,'12px',13,f'border:1.5px solid {A_INK};margin-bottom:8px;') for c in CATS),'padding:14px 18px 10px;text-align:center;background:#FFFFFF;')
def A_foot(): return row(logo(False,46)+f'<div style="font-size:11px;color:{A_MUT};margin-top:12px;line-height:1.6;">{LAB}</div>'+unsub(A_MUT),'padding:26px 22px 30px;text-align:center;background:#F4F7FA;border-top:1px solid #E1E6EC;')
def A_code(): return row(f'<div style="border:2px dashed {A_INK};border-radius:16px;padding:18px 12px;"><div style="font-size:11px;font-weight:700;letter-spacing:.16em;color:{A_MUT};">DEIN CODE · 10 %</div><div style="font-size:26px;font-weight:800;font-family:{MONO};color:{A_INK};margin:6px 0 4px;">{CODE}</div><div style="font-size:12px;color:{A_MUT};">gültig bis 14.11.2026 · einmal einlösbar</div></div>','padding:22px 18px 4px;text-align:center;background:#FFFFFF;')
def A_last():
    r=''.join(f'<tr><td width="70" style="padding:8px 0;border-bottom:1px solid #E1E6EC;"><img src="{P[k][3]}" width="58" height="58" style="display:block;border-radius:12px;"></td><td style="padding:8px 4px;border-bottom:1px solid #E1E6EC;font-family:{F};"><div style="font-size:15px;font-weight:800;color:{A_INK};">{n}</div><div style="font-size:12px;color:{A_MUT};">{m}</div></td></tr>' for k,n,m in LAST)
    return row(f'<div style="font-size:17px;font-weight:800;color:{A_INK};margin-bottom:6px;">Deine letzte Bestellung</div><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:14px;">{r}</table>'+btn('Gleiche Bestellung nochmal →',A_BTN,'#FFF'),'padding:22px 18px 6px;background:#FFFFFF;')
def A_text(t): return row(f'<div style="font-size:14px;line-height:1.6;color:{A_MUT};">{t}</div>','padding:20px 26px 4px;text-align:center;background:#FFFFFF;')

# ---------- B: DUNKEL PREMIUM ----------
B_BG='#0B0D10'; B_IN='#12151A'; B_MUT='#A3A9B1'; B_ACC='#D9DDE2'
def Btn(t,w='86%'): return btn(t,'#FFFFFF','#0D0F12',w,12,'15px 12px',15)
def B_top(title,sub,cta,hero):
    return row(logo(True,44)+f'<div style="font-size:32px;line-height:1.1;font-weight:800;color:#FFFFFF;margin:20px 0 10px;letter-spacing:-.01em;">{title}</div><div style="font-size:15px;line-height:1.5;color:{B_MUT};margin-bottom:22px;">{sub}</div>'+Btn(cta),'padding:30px 20px 24px;text-align:center;background:'+B_IN+';')+\
      row(f'<img src="{U}sp-email/{hero}" width="600" style="display:block;width:100%;height:auto;border:0;">','padding:0;line-height:0;background:'+B_IN+';')
def B_h(a,b,sub=''): return f'<div style="font-size:27px;line-height:1.15;font-weight:800;color:#FFFFFF;">{a}<br><span style="color:#8E959E;">{b}</span></div>'+(f'<div style="font-size:14px;line-height:1.6;color:{B_MUT};margin:12px auto 0;max-width:430px;">{sub}</div>' if sub else '')
def B_sec(inner,pad='30px 20px'): return row(inner,f'padding:{pad};text-align:center;background:{B_IN};')
def B_code(): return B_sec(f'<div style="border:1px solid #2C3138;background:#181C22;border-radius:18px;padding:22px 14px;"><div style="font-size:52px;font-weight:800;color:#FFFFFF;line-height:1;">10 %</div><div style="font-size:11px;font-weight:700;letter-spacing:.18em;color:{B_MUT};margin:10px 0 14px;">AUF DEINE ERSTE BESTELLUNG</div><div style="border:1.5px dashed #59606A;border-radius:12px;padding:12px;font-size:24px;font-weight:800;font-family:{MONO};color:{B_ACC};letter-spacing:.04em;">{CODE}</div><div style="font-size:12px;color:#7D848D;margin-top:10px;">gültig bis 14.11.2026</div></div><div style="height:16px;"></div>'+Btn('Code jetzt einlösen','100%'),'4px 20px 26px')
def B_prod(keys):
    c=[f'<div style="background:#181C22;border:1px solid #2C3138;border-radius:14px;overflow:hidden;text-align:center;"><img src="{P[k][3]}" width="150" style="display:block;width:100%;height:auto;border:0;"><div style="padding:10px 8px 12px;"><div style="font-size:14px;font-weight:800;color:#FFFFFF;">{P[k][0]}</div><div style="font-size:12px;color:{B_MUT};margin:2px 0 10px;">{P[k][2]}</div>{btn("Ansehen","transparent","#FFFFFF",r=8,pad="8px 6px",fs=12,border="border:1.5px solid #FFFFFF;")}</div></div>' for k in keys]
    return grid(c)
def B_trust():
    r=''.join(f'<tr><td width="48" valign="top" style="padding:10px 0;"><div style="width:38px;height:38px;line-height:38px;border-radius:50%;background:#1F242B;border:1px solid #2C3138;text-align:center;font-size:17px;">{i}</div></td><td style="padding:10px 6px;text-align:left;font-family:{F};"><div style="font-size:14px;font-weight:800;color:#FFFFFF;">{a}</div><div style="font-size:12px;color:{B_MUT};">{b}</div></td></tr>' for i,a,b in TRUST)
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">{r}</table>'
def B_last():
    r=''.join(f'<tr><td width="70" style="padding:9px 0;border-bottom:1px solid #2C3138;"><img src="{P[k][3]}" width="58" height="58" style="display:block;border-radius:12px;"></td><td style="padding:9px 4px;border-bottom:1px solid #2C3138;text-align:left;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#FFFFFF;">{n}</div><div style="font-size:12px;color:{B_MUT};">{m}</div></td></tr>' for k,n,m in LAST)
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:14px 0 18px;">{r}</table>'+Btn('Gleiche Bestellung nochmal','100%')
def B_foot(): return row('<div style="height:1px;background:#2C3138;margin:0 0 26px;"></div>'+logo(True,54)+f'<div style="font-size:11px;color:#7D848D;margin-top:14px;line-height:1.6;">{LAB}</div>'+unsub('#7D848D'),'padding:0 22px 32px;text-align:center;background:'+B_IN+';')

# ---------- C: DUNKEL MIT AKZENT ----------
C_IN='#101317'; C_ACC='#FF6B35'; C_MUT='#A3A9B1'
def Cbtn(t,w='78%'): return btn(t,C_ACC,'#FFFFFF',w,12,'14px 12px',15)
def C_top(a,b,sub,cta):
    return row(logo(True,40)+f'<div style="font-size:28px;line-height:1.15;font-weight:800;color:#FFFFFF;margin:18px 0 8px;">{a} <span style="color:{C_ACC};">{b}</span></div><div style="font-size:14px;line-height:1.5;color:{C_MUT};margin-bottom:6px;">{sub}</div>',f'padding:26px 20px 10px;text-align:center;background:{C_IN};')
def C_spot(k,cta):
    return row(f'<table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td style="width:260px;height:260px;border-radius:50%;background-color:#2A2F36;background-image:radial-gradient(circle at 50% 40%,#5A616B 0%,#23272D 60%,#101317 100%);text-align:center;vertical-align:middle;"><img src="{P[k][3]}" width="210" style="display:block;margin:0 auto;width:210px;height:210px;border-radius:50%;border:0;"></td></tr></table><div style="height:20px;"></div>'+Cbtn(cta),f'padding:14px 20px 26px;text-align:center;background:{C_IN};')
def C_zig():
    out=''
    for n,(i,a,b) in enumerate(TRUST):
        card=f'<div style="background:#181C22;border:1px solid {C_ACC};border-radius:14px;padding:14px 14px 14px 16px;text-align:left;box-shadow:4px 4px 0 #3A2418;"><div style="font-size:18px;margin-bottom:4px;">{i}</div><div style="font-size:14px;font-weight:800;color:{C_ACC};">{a}</div><div style="font-size:12px;color:{C_MUT};margin-top:2px;">{b}</div></div>'
        sp='<td width="34%"></td>'
        out+=f'<tr><td style="padding:6px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{"" if n%2==0 else sp}<td width="66%">{card}</td>{sp if n%2==0 else ""}</tr></table></td></tr>'
    return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0">{out}</table>'
def C_h(a,b,sub=''): return f'<div style="font-size:24px;line-height:1.2;font-weight:800;color:#FFFFFF;">{a}<br><span style="color:{C_ACC};">{b}</span></div>'+(f'<div style="font-size:13px;color:{C_MUT};margin:8px 0 16px;">{sub}</div>' if sub else '<div style="height:14px;"></div>')
def C_sec(inner,pad='26px 18px'): return row(inner,f'padding:{pad};text-align:center;background:{C_IN};')
def C_code(): return f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border-radius:16px;overflow:hidden;"><tr><td width="36%" style="background:{C_ACC};padding:18px 6px;text-align:center;font-family:{F};"><div style="font-size:30px;font-weight:800;color:#FFF;line-height:1;">10 %</div><div style="font-size:10px;font-weight:700;letter-spacing:.14em;color:#FFE3D6;margin-top:6px;">WILLKOMMEN</div></td><td style="background:#181C22;border:1px solid #2C3138;border-left:2px dashed {C_IN};padding:14px 10px;text-align:center;font-family:{F};"><div style="font-size:10px;font-weight:700;letter-spacing:.14em;color:{C_MUT};">DEIN CODE</div><div style="font-size:21px;font-weight:800;font-family:{MONO};color:#FFF;margin:6px 0 3px;">{CODE}</div><div style="font-size:11px;color:#7D848D;">gültig bis 14.11.2026</div></td></tr></table>'
def C_last():
    r=''.join(f'<tr><td width="70" style="padding:9px 0;border-bottom:1px solid #2C3138;"><img src="{P[k][3]}" width="58" height="58" style="display:block;border-radius:12px;"></td><td style="padding:9px 4px;border-bottom:1px solid #2C3138;text-align:left;font-family:{F};"><div style="font-size:15px;font-weight:800;color:#FFF;">{n}</div><div style="font-size:12px;color:{C_MUT};">{m}</div></td></tr>' for k,n,m in LAST)
    return f'<div style="border:1px solid #2C3138;background:#181C22;border-radius:16px;padding:6px 14px 14px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:14px;">{r}</table>'+Cbtn('Gleiche Bestellung nochmal','100%')+'</div>'
def C_foot(): return row('<div style="height:1px;background:#2C3138;margin:0 0 24px;"></div>'+logo(True,50)+f'<div style="font-size:11px;color:#7D848D;margin-top:14px;line-height:1.6;">{LAB}</div>'+unsub('#7D848D'),f'padding:0 22px 30px;text-align:center;background:{C_IN};')

M={}
M['A-willkommen']=T(A_top('Willkommen bei Peptrium.','Schön, dass du dabei bist. Hier ist dein Code für die erste Bestellung.','10 % sichern →','nl-hero-vials.jpg')+A_code()+A_trust()+A_prod(['reta','ghk','pen','mots'],'Beliebt bei Insidern')+A_cats()+A_foot(),A_BG,'#FFFFFF')
M['A-nachkauf']=T(A_top('Zeit für Nachschub?','Deine letzte Bestellung ist rund 4 Wochen her – mit einem Klick ist alles wieder im Warenkorb.','Jetzt nachbestellen →','nl-hero-pen.jpg')+A_last()+A_text('An unserem Standard hat sich nichts geändert:')+A_trust()+A_prod(['ghk','mots','pen','reta'],'Das könnte auch passen')+A_foot(),A_BG,'#FFFFFF')
M['B-willkommen']=T(B_top('Willkommen.','Schön, dass du dabei bist. Dein Geschenk wartet unten.','Zum Shop','nl-hero-vials.jpg')+B_sec(B_h('Dein Willkommens-','geschenk.'),'30px 20px 18px')+B_code()+B_sec(B_h('Was jede Bestellung','auszeichnet.')+B_trust())+B_sec(B_h('Beliebt bei','Insidern.')+'<div style="height:16px;"></div>'+B_prod(['reta','ghk','pen','mots']),'26px 15px')+B_foot(),B_BG,B_IN)
M['B-nachkauf']=T(B_top('Zeit für Nachschub?','Deine letzte Bestellung ist rund 4 Wochen her.','Jetzt nachbestellen','nl-hero-pen.jpg')+B_sec(B_h('Deine letzte','Bestellung.','Mit einem Klick wieder im Warenkorb.')+B_last())+B_sec(B_h('Unser Standard.','Wie immer.')+B_trust())+B_sec(B_h('Das könnte auch','passen.')+'<div style="height:16px;"></div>'+B_prod(['ghk','mots']),'26px 15px')+B_foot(),B_BG,B_IN)
M['C-willkommen']=T(C_top('Willkommen bei','Peptrium.','Dein Code für die erste Bestellung:','')+C_sec(C_code()+'<div style="height:16px;"></div>'+Cbtn('Code einlösen','100%'),'10px 18px 26px')+C_sec(C_h('Was du von uns','erwarten kannst.')+C_zig())+C_sec(C_h('Unser','Bestseller.'),'26px 18px 0')+C_spot('reta','Retatrutide ansehen')+C_foot(),'#08090B',C_IN)
M['C-nachkauf']=T(C_top('Zeit für','Nachschub?','Deine letzte Bestellung ist rund 4 Wochen her.','')+C_sec(C_last(),'10px 18px 26px')+C_sec(C_h('Unser Standard.','Wie immer.')+C_zig())+C_sec(C_h('Passt','dazu.'),'26px 18px 0')+C_spot('ghk','GHK-Cu ansehen')+C_foot(),'#08090B',C_IN)
for k,v in M.items(): open(k+'.html','w').write(v)
print(list(M))
