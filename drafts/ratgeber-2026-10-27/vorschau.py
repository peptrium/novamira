import html
from artikel import ARTIKEL, HINWEIS
css='''body{margin:0;background:#F4F5F7;font-family:-apple-system,Inter,Segoe UI,Roboto,sans-serif;color:#1A1D21}
.top{background:#0D0F12;color:#fff;padding:18px 16px}.top b{letter-spacing:.2em;font-size:13px}.top p{margin:6px 0 0;color:#A3AAB3;font-size:13px}
nav{display:flex;gap:8px;overflow-x:auto;padding:12px 16px;background:#fff;position:sticky;top:0;border-bottom:1px solid #E3E5E8}
nav a{white-space:nowrap;padding:8px 12px;border-radius:99px;background:#F0F1F3;color:#0D0F12;text-decoration:none;font-size:13px;font-weight:600}
article{max-width:760px;margin:16px auto;background:#fff;border-radius:18px;padding:22px 18px 26px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.eb{font-size:11px;font-weight:800;letter-spacing:.16em;color:#8E959E}
.seo{font-size:12px;background:#F7F8F9;border:1px dashed #C9CDD2;border-radius:10px;padding:10px;margin:10px 0 16px;color:#555}
h1{font-size:27px;line-height:1.2;margin:8px 0 12px}h2{font-size:20px;margin:28px 0 8px}
p,li{font-size:16px;line-height:1.65}.lead{font-size:17.5px;color:#3A3F45}
a{color:#0D0F12;text-decoration:underline;text-decoration-color:#B0B5BB}
.rg-box{background:#0D0F12;color:#fff;border-radius:14px;padding:14px 16px;line-height:1.7;margin:12px 0}
.rg-tab{width:100%;border-collapse:collapse;font-size:14px;margin:10px 0;display:block;overflow-x:auto}.rg-tab th,.rg-tab td{border-bottom:1px solid #E3E5E8;padding:8px 6px;text-align:left}.rg-tab th{background:#F0F1F3}
.faq{margin-top:26px;border-top:1px solid #E3E5E8}.faq details{border-bottom:1px solid #E3E5E8;padding:12px 0}.faq summary{font-weight:700;cursor:pointer}
.rg-note{font-size:13px;color:#6B7178;background:#F7F8F9;border-radius:10px;padding:12px;margin-top:22px}'''
nav=''.join(f'<a href="#{x["slug"]}">{i+1}. {html.escape(x["title"].split(":")[0])}</a>' for i,x in enumerate(ARTIKEL))
arts=''
for i,x in enumerate(ARTIKEL):
    faq=''.join(f'<details><summary>{html.escape(q)}</summary><p>{html.escape(a)}</p></details>' for q,a in x['faq'])
    arts+=(f'<article id="{x["slug"]}"><div class="eb">RATGEBER · ARTIKEL {i+8}</div>'
           f'<div class="seo"><b>Adresse:</b> peptrium.com/ratgeber/{x["slug"]}/<br><b>Google-Titel:</b> {html.escape(x["seo_title"])} | Peptrium<br><b>Google-Beschreibung:</b> {html.escape(x["meta"])}</div>'
           f'<h1>{html.escape(x["title"])}</h1><p class="lead">{html.escape(x["lead"])}</p>{x["body"]}'
           f'<div class="faq"><h2>Häufige Fragen</h2>{faq}</div>{HINWEIS}</article>')
open('vorschau.html','w').write(f'<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Ratgeber Entwurf</title><style>{css}</style></head><body><div class="top"><b>PEPTRIUM · RATGEBER</b><p>Entwurf – 4 neue Artikel, noch nicht online</p></div><nav>{nav}</nav>{arts}</body></html>')
print('ok')
