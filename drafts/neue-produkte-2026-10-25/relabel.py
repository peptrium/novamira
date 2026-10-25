import sys
from PIL import Image, ImageDraw, ImageFont, ImageFilter
import numpy as np
SRC='104D478F-BD97-4CA2-A036-29E66BDE6ECF.png'
im=Image.open(SRC).convert('RGB'); A0=np.asarray(im).astype(float)
NAME=dict(x0=401,x1=690,top=869,base=934, ex=(392,858,700,946), font='Inter-Bold.otf', maxw=300, maxs=71)
MG=dict(x0=484,x1=612,top=993,base=1026, ex=(474,986,624,1040), font='Inter-SemiBold.otf', maxw=150)
rng=np.random.default_rng(1)
def erase(a,ex):
    x0,y0,x1,y1=ex
    top=np.median(a[y0-7:y0-1,x0:x1+1],axis=0); bot=np.median(a[y1+1:y1+4,x0:x1+1],axis=0) if ex[3]<1000 else top
    h=y1-y0+1
    for i in range(h):
        t=i/(h-1); a[y0+i,x0:x1+1]=top*(1-t)+bot*t
    a[y0:y1+1,x0:x1+1]+=rng.normal(0,2.0,(h,x1-x0+1,1))
def draw(a,text,S,ref='H'):
    capH=S['base']-S['top']; fp='/usr/share/fonts/opentype/inter/'+S['font']
    s=10
    while True:
        f=ImageFont.truetype(fp,s+1); b=f.getbbox(ref,anchor='ls')
        if -b[1]>capH: break
        s+=1
    s=min(s,S.get('maxs',999)); f=ImageFont.truetype(fp,s)
    while f.getlength(text)>S['maxw']: s-=1; f=ImageFont.truetype(fp,s)
    w=f.getlength(text); cx=(S['x0']+S['x1'])/2
    base=S['base'] if -f.getbbox(ref,anchor='ls')[1]>=capH-2 else S['base']-(capH-(-f.getbbox(ref,anchor='ls')[1]))/2
    L=Image.new('L',im.size,0); ImageDraw.Draw(L).text((cx-w/2,base),text,font=f,fill=255,anchor='ls')
    L=L.filter(ImageFilter.GaussianBlur(0.45)); m=np.asarray(L).astype(float)[...,None]/255
    a[:]=a*(1-m)+np.array([18,18,20])*m
    return s
def make(name,mg,out):
    a=A0.copy(); erase(a,NAME['ex']); print(name,draw(a,name,NAME,'H'))
    if mg: erase(a,MG['ex']); print(mg,draw(a,mg,MG,'1'))
    Image.fromarray(np.clip(a,0,255).astype('uint8')).save(out)
make(sys.argv[1], sys.argv[2], sys.argv[3])
