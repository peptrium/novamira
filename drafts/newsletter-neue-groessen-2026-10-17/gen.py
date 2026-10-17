# Kampagne an alle Abonnenten (2026-10-17, Nutzer: "a) die größen reta 20mg und ghk 100mg"):
# neue Größen Retatrutide 20 mg + GHK-Cu 100 mg. v2: als 'Wieder verfügbar' (Nutzer), ohne 4,90-€-Ersparnis-Zeile. Rahmen/Helfer aus newsletter-mails-C-2026-10-16/more2.py (Variante hybrid).
# python3 gen.py -> neue-groessen.body (htmlCode-Body für Omnisend) + vorschau.html
import os, sys
_here = _own = os.path.dirname(os.path.abspath(__file__))
_c = os.path.join(_here, '..', 'newsletter-mails-C-2026-10-16')
__file__ = os.path.join(_c, 'more2.py')
exec(open(__file__).read().split('\ndef build():')[0])
THEME = 'hybrid'

def build():
    c = 'neue-groessen'
    A, AL, HB, HT, HS, HP, HE = pal('#16A34A', '#ECF9F0', True)
    E = U + 'sp-email/'
    def size(l, pr, new=False):
        st = f'border:2px solid {A};background:{AL};' if new else f'border:1px solid {LBD};background:#F5F6F7;'
        tag = f'<div style="font-size:10px;font-weight:800;letter-spacing:.12em;color:{A};margin-bottom:2px;">WIEDER DA</div>' if new else '<div style="font-size:10px;line-height:1;margin-bottom:2px;">&nbsp;</div>'
        return (f'<td width="50%" valign="top" style="width:50%;padding:4px;"><div style="{st}border-radius:14px;padding:10px 6px;text-align:center;font-family:{F};">{tag}'
                f'<div style="font-size:20px;font-weight:800;color:{INK};line-height:1.1;">{l}</div><div style="font-size:13px;color:{LMUT};margin-top:3px;">{pr}</div></div></td>')
    def prod(img, name, big, old, new, save, url):
        return (f'<div style="border:1px solid {LBD};background:#FFF;border-radius:22px;overflow:hidden;box-shadow:0 6px 24px rgba(13,15,18,.08);">'
                f'<a href="{url}" style="display:block;line-height:0;background:#111;"><img src="{E}{img}" width="560" alt="{name} {big}" style="display:block;width:100%;max-width:560px;height:auto;border:0;"></a>'
                f'<div style="padding:16px 16px 18px;text-align:left;font-family:{F};">'
                f'<div style="font-size:22px;font-weight:800;color:{INK};">{name} <span style="color:{A};">{big}</span></div>'
                f'<div style="font-size:13px;color:{LMUT};margin-top:3px;">Wieder auf Lager · sofort lieferbar</div>'
                f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:12px 0 6px;"><tr>{size(*old)}{size(*new, True)}</tr></table>'
                '<div style="height:10px;"></div>'
                + lbtn(f'{name} {big} ansehen', url, INK) + '</div></div>')
    tip = lambda big, t: f'<td width="33%" valign="top" style="width:33%;padding:4px;"><div style="background:#F5F6F7;border-radius:14px;padding:14px 6px;text-align:center;font-family:{F};"><div style="font-size:19px;font-weight:800;color:{INK};line-height:1.1;">{big}</div><div style="font-size:11px;line-height:1.4;color:{LMUT};margin-top:6px;">{t}</div></div></td>'
    return wrap(bar('Wieder verfügbar')
        + lrow(f'<div style="display:inline-block;background:{A};border-radius:999px;padding:7px 14px;font-size:12px;font-weight:800;letter-spacing:.12em;color:#FFF;">● WIEDER VERFÜGBAR</div>'
               + h1('Wieder da:', 'die großen Größen.', HT, HS, 34).replace('line-height:1.08;', 'line-height:1.08;margin-top:16px;')
               + p('Retatrutide 20 mg und GHK-Cu 100 mg sind wieder auf Lager – sofort lieferbar, Versand in 2 Werktagen.', col=HP), '34px 20px 26px', HB)
        + lrow(prod('nl-p-reta20.jpg', 'Retatrutide', '20 mg', ('10 mg', '74,90 €'), ('20 mg', '144,90 €'), '4,90 € günstiger als 2 × 10 mg', utm('/produkt/retatrutide/', c) + '&attribute_volumen=20mg'), '24px 20px 10px')
        + lrow(prod('nl-p-ghk100.jpg', 'GHK-Cu', '100 mg', ('50 mg', '39,90 €'), ('100 mg', '74,90 €'), '4,90 € günstiger als 2 × 50 mg', utm('/produkt/ghk-cu/', c) + '&attribute_volumen=100mg'), '14px 20px 10px')
        + lrow(eyebrow('SO SPARST DU', LMUT) + f'<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>{tip("−10 %", "ab 3 Stück eines Peptids")}{tip("150 €", "ab hier Versand gratis")}{tip("2 Tage", "Lieferung mit DHL")}</tr></table>', '20px 16px 10px')
        + lrow(lbtn('Alle Produkte ansehen', utm('/alle-produkte/', c), '#FFFFFF', INK).replace('border-radius:12px;', f'border-radius:12px;border:1.5px solid {INK};'), '10px 20px 32px')
        + lfoot())

if __name__ == '__main__':
    os.chdir(_own)
    body = build()
    open('neue-groessen.body', 'w').write(body)
    open('vorschau.html', 'w').write('<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Neue Größen</title></head><body style="margin:0;background:#E9EBED;">' + body + '</body></html>')
    print('ok', len(body))
