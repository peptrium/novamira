import subprocess,re,json,os,hashlib,html as H,urllib.parse,concurrent.futures as cf
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124 Safari/537.36"
P=os.environ['https_proxy']
def get(u):
    r=subprocess.run(['curl','-s','-x',P,'-A',UA,'-o','-','-w','\n%{http_code} %{time_total} %{size_download} %{redirect_url}',u],capture_output=True,timeout=90)
    out=r.stdout.decode('utf-8','replace');body,meta=out.rsplit('\n',1);c,t,s,red=(meta.split(' ')+['',''])[:4]
    return body,int(c),float(t),int(s),red
urls=[l.strip() for l in open('urls.txt') if l.strip()]
def one(u):
    b,c,t,s,red=get(u)
    open('html/'+hashlib.md5(u.encode()).hexdigest()+'.html','w').write(b)
    tt=re.search(r'<title>(.*?)</title>',b,re.S);title=H.unescape(tt.group(1).strip()) if tt else ''
    md=re.search(r'<meta name="description" content="([^"]*)"',b);desc=H.unescape(md.group(1)) if md else ''
    h1=re.findall(r'<h1[^>]*>(.*?)</h1>',b,re.S);h1=[re.sub('<[^>]+>','',x).strip() for x in h1]
    can=re.search(r'<link rel="canonical" href="([^"]+)"',b);rob=re.search(r'<meta name=.robots. content="([^"]+)"',b)
    ld=re.findall(r'<script type="application/ld\+json"[^>]*>(.*?)</script>',b,re.S);types=[]
    for x in ld:
        try:
            j=json.loads(x);items=j.get('@graph',[j]) if isinstance(j,dict) else j
            for it in items: types.append(it.get('@type') if isinstance(it,dict) else '?')
        except Exception as e: types.append('JSON-FEHLER')
    imgs=re.findall(r'<img\b[^>]*>',b);noalt=[i for i in imgs if not re.search(r'alt="[^"]+"',i)]
    links=set(re.findall(r'href="(https://peptrium\.com/[^"#?]*)',b))
    words=len(re.sub(r'\s+',' ',re.sub(r'<[^>]+>',' ',re.sub(r'<(script|style)[^>]*>.*?</\1>','',b,flags=re.S))).split())
    return dict(u=u,code=c,t=t,kb=s//1024,title=title,tl=len(title),desc=desc,dl=len(desc),h1=h1,can=can.group(1) if can else '',rob=rob.group(1) if rob else '',ld=types,img=len(imgs),noalt=len(noalt),words=words,links=sorted(links))
with cf.ThreadPoolExecutor(6) as ex: res=list(ex.map(one,urls))
json.dump(res,open('crawl.json','w'),ensure_ascii=False,indent=0)
# Links prüfen
alll=sorted(set(l for r in res for l in r['links'] if not re.search(r'\.(png|jpe?g|webp|svg|css|js|pdf|ico|xml)$',l) and '/wp-' not in l))
print('pages',len(res),'internal links',len(alll))
def st(u):
    r=subprocess.run(['curl','-s','-x',P,'-A',UA,'-o','/dev/null','-w','%{http_code} %{redirect_url}',u],capture_output=True,timeout=60);return u,r.stdout.decode()
with cf.ThreadPoolExecutor(8) as ex: ls=list(ex.map(st,alll))
json.dump(ls,open('links.json','w'))
