from PIL import Image, ImageDraw, ImageFont, ImageFilter
import numpy as np
A=34.5
orig=Image.open('penimg/peptrium-pen-reta-teaser-nobg.png').convert('RGBA')
rot=orig.rotate(A,resample=Image.BICUBIC,expand=True)
font=ImageFont.truetype('/usr/share/fonts/opentype/inter/Inter-Medium.otf',26)
for slug,txt in [('ghk','GHK-CU 50 mg'),('motsc','MOTS-C 20 mg')]:
    r=np.array(rot).astype(float)
    y0,y1,x0,x1=690,724,732,992
    for y in range(y0,y1):
        L=r[y,720:730].mean(0);R=r[y,992:1002].mean(0)
        for x in range(x0,x1):
            t=(x-x0)/(x1-x0);r[y,x]=L*(1-t)+R*t
    im=Image.fromarray(r.clip(0,255).astype('uint8'),'RGBA')
    d=ImageDraw.Draw(im)
    # baseline: caps 695..714
    bb=font.getbbox('H');d.text((739,714-bb[3]),txt,font=font,fill=(28,28,32,255))
    mask=Image.new('L',rot.size,0);ImageDraw.Draw(mask).rectangle((x0-2,y0-2,x1+2,y1+2),fill=255)
    back=im.rotate(-A,resample=Image.BICUBIC,expand=True);mb=mask.rotate(-A,resample=Image.BICUBIC,expand=True)
    W,H=orig.size;l=(back.width-W)//2;t=(back.height-H)//2
    back=back.crop((l,t,l+W,t+H));mb=mb.crop((l,t,l+W,t+H)).filter(ImageFilter.GaussianBlur(1))
    out=orig.copy();out.paste(back,(0,0),mb)
    out.save('penimg/pen-%s.png'%slug,optimize=True)
    c=out.crop((450,300,1000,620));bg=Image.new('RGBA',c.size,(30,30,30,255));bg.alpha_composite(c);bg.convert('RGB').save('penimg/chk-%s.jpg'%slug)
