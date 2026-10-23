import subprocess,json,os,urllib.parse,concurrent.futures as cf,sys
P=os.environ['https_proxy']
def sug(q):
    u='https://suggestqueries.google.com/complete/search?client=firefox&hl=de&gl=de&q='+urllib.parse.quote(q)
    r=subprocess.run(['curl','-s','-x',P,'-A','Mozilla/5.0',u],capture_output=True,timeout=30)
    try: return q,json.loads(r.stdout.decode('utf-8','replace'))[1]
    except Exception as e: return q,['ERR '+r.stdout.decode()[:80]]
seeds=sys.argv[1:]
qs=[]
for s in seeds:
    qs+= [s, s+' ', 'was ist '+s, s+' kaufen', s+' erfahrungen', s+' dosierung', s+' wirkung', s+' vs']+[s+' '+c for c in 'abcdefghijklmnoprstuvwz']
with cf.ThreadPoolExecutor(10) as ex: res=list(ex.map(sug,qs))
out={}
for q,l in res:
    for x in l: out[x]=out.get(x,0)+1
json.dump(res,open('ac-'+seeds[0].replace(' ','_')+'.json','w'),ensure_ascii=False)
print(len(out));print(' | '.join(sorted(out,key=lambda k:-out[k])[:150]))
