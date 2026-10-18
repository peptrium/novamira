# erzeugt dezente Soundeffekte (eigene Synthese, rechtefrei)
import numpy as np, wave
SR=44100
def save(n,x):
    x=np.clip(x,-1,1); w=wave.open(n,'wb'); w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes((x*32767).astype('<i2').tobytes()); w.close()
rng=np.random.default_rng(1)
# whoosh: gefiltertes Rauschen mit Frequenz-Sweep
t=np.arange(int(SR*0.55))/SR; n=rng.standard_normal(len(t))
env=np.sin(np.pi*np.clip(t/0.55,0,1))**2
f=np.linspace(300,2400,len(t)); ph=np.cumsum(2*np.pi*f/SR)
y=np.zeros_like(n); a=0
for i in range(len(n)):  # einfacher Tiefpass mit wanderndem Koeffizient
    k=min(0.9,f[i]/SR*6); a+= k*(n[i]-a); y[i]=a
save('whoosh.wav', 0.55*y/np.abs(y).max()*env)
# pop: kurzer weicher Sinus-Blip
t=np.arange(int(SR*0.12))/SR; save('pop.wav', 0.5*np.sin(2*np.pi*(900-2500*t)*t)*np.exp(-t*35))
# tick: sehr kurzer Klick
t=np.arange(int(SR*0.03))/SR; save('tick.wav', 0.35*np.sin(2*np.pi*2200*t)*np.exp(-t*220))
# riser/glow für den Abschluss
t=np.arange(int(SR*1.2))/SR; save('glow.wav', 0.25*(np.sin(2*np.pi*220*t)+0.5*np.sin(2*np.pi*330*t)+0.3*np.sin(2*np.pi*440*t))*np.minimum(1,t/0.15)*np.exp(-t*2.2))
