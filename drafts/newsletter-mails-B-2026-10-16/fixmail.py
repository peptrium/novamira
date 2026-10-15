import json,re,base64
from gen import *
c='code-nachtrag'
def build(code,bis):
    return body(top('Hier ist dein Code.','In unserer Willkommensmail fehlte dein persönlicher Code – sorry! Hier ist er.','Zum Shop',utm('/alle-produkte/',c),'nl-hero-vials.jpg')
     +row(H('Dein persönlicher','Code.'),'30px 20px 18px')+code_card(code,bis)
     +row(H('Beliebt bei','unseren Kunden.')+prods(['reta','ghk','pen','mots'],c),'26px 15px')+row(H('Unser Standard.','Wie immer.')+trust(),'10px 20px 30px')+foot())
def code_card(cd,bis): return code('10 %','auf deine nächste Bestellung',cd,'gültig bis '+bis+' · einmal einlösbar','Code jetzt einlösen',utm('/alle-produkte/',c))
open('fix.body','w').write(build(CP('hallo_code'),CP('hallo_gueltig_bis')))
open('fix.html','w').write(f'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head><body style="margin:0;background:{BG};">'+build('HALLO-7KQ2M','15.11.2026')+'</body></html>')
