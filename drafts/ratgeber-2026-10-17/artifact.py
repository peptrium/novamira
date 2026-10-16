import html
from artikel import ARTIKEL, HINWEIS
css='''/* Lesefassung: schmale Textspalte, Artikel als Karten, Chips zum Springen */
:root{--bg:#F2F3F5;--card:#FFFFFF;--fg:#15181C;--muted:#5F666E;--line:#E1E4E8;--chip:#E9EBEE;--box:#0D0F12;--boxfg:#F4F5F7;--meta:#F7F8F9;
--sans:"Inter",-apple-system,"Segoe UI",Roboto,sans-serif}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){--bg:#0B0D10;--card:#14171B;--fg:#ECEEF0;--muted:#9AA1A9;--line:#262B31;--chip:#1F2328;--box:#22272E;--boxfg:#F4F5F7;--meta:#1A1E23;color-scheme:dark}}
:root[data-theme="dark"]{--bg:#0B0D10;--card:#14171B;--fg:#ECEEF0;--muted:#9AA1A9;--line:#262B31;--chip:#1F2328;--box:#22272E;--boxfg:#F4F5F7;--meta:#1A1E23;color-scheme:dark}
body{background:var(--bg);color:var(--fg);font-family:var(--sans)}
.wrap{padding-left:max(16px,env(safe-area-inset-left,0px));padding-right:max(16px,env(safe-area-inset-right,0px));padding-block:0 32px;max-width:760px;margin:0 auto}
header{padding-block:20px 8px}header .k{font-size:12px;font-weight:800;letter-spacing:.18em;color:var(--muted)}header h1{font-size:24px;margin:6px 0 4px;text-wrap:balance}header p{margin:0;color:var(--muted);font-size:14px}
nav{display:flex;flex-wrap:wrap;gap:8px;padding-block:12px}nav a{padding:8px 12px;border-radius:99px;background:var(--chip);color:var(--fg);text-decoration:none;font-size:13px;font-weight:600}
article{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:20px 18px 24px;margin-top:16px;min-width:0}
.eb{font-size:11px;font-weight:800;letter-spacing:.16em;color:var(--muted)}
.seo{font-size:12.5px;line-height:1.5;background:var(--meta);border:1px dashed var(--line);border-radius:10px;padding:10px 12px;margin:10px 0 14px;color:var(--muted);overflow-wrap:anywhere}
article h1{font-size:25px;line-height:1.25;margin:6px 0 10px;text-wrap:balance}article h2{font-size:19px;margin:26px 0 6px;text-wrap:balance}
article p,article li{font-size:16px;line-height:1.65}.lead{font-size:17px;color:var(--muted)}
article a{color:var(--fg);text-decoration:underline;text-decoration-color:var(--muted)}
.rg-box{background:var(--box);color:var(--boxfg);border-radius:12px;padding:14px 16px;line-height:1.7;margin:12px 0}
.tw{overflow-x:auto}.rg-tab{width:100%;border-collapse:collapse;font-size:14px;margin:10px 0;font-variant-numeric:tabular-nums}.rg-tab th,.rg-tab td{border-bottom:1px solid var(--line);padding:8px 6px;text-align:left;vertical-align:top}.rg-tab th{background:var(--chip)}
.faq{margin-top:24px;border-top:1px solid var(--line)}.faq details{border-bottom:1px solid var(--line);padding:12px 0}.faq summary{font-weight:700;cursor:pointer}
.rg-note{font-size:13px;color:var(--muted);background:var(--meta);border-radius:10px;padding:12px;margin-top:20px}'''
nav=''.join(f'<a href="#{x["slug"]}">{i+1}. {html.escape(x["title"].split(":")[0])}</a>' for i,x in enumerate(ARTIKEL))
arts=''
for i,x in enumerate(ARTIKEL):
    faq=''.join(f'<details><summary>{html.escape(q)}</summary><p>{html.escape(a)}</p></details>' for q,a in x['faq'])
    body=x['body'].replace('<table class="rg-tab">','<div class="tw"><table class="rg-tab">').replace('</table>','</table></div>')
    arts+=(f'<article id="{x["slug"]}"><div class="eb">ARTIKEL {i+1} VON 5</div>'
           f'<div class="seo"><b>Adresse:</b> peptrium.com/ratgeber/{x["slug"]}/<br><b>Titel bei Google:</b> {html.escape(x["seo_title"])} | Peptrium<br><b>Beschreibung bei Google:</b> {html.escape(x["meta"])}</div>'
           f'<h1>{html.escape(x["title"])}</h1><p class="lead">{html.escape(x["lead"])}</p>{body}'
           f'<div class="faq"><h2>Häufige Fragen</h2>{faq}</div>{HINWEIS}</article>')
out=(f'<title>Peptrium Ratgeber-Entwurf</title><meta name="claude:viewport-fit-sides" content="cover"><style>{css}</style>'
     f'<div class="wrap"><header><div class="k">PEPTRIUM · RATGEBER</div><h1>5 Artikel zum Prüfen</h1><p>Entwurf, noch nicht online. Tippe oben auf ein Thema, um hinzuspringen.</p></header><nav>{nav}</nav>{arts}</div>')
import sys; open(sys.argv[1],'w').write(out); print('ok')
