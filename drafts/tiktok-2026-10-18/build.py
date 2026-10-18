"""Baut ein Peptrium-Wissensvideo: Sprecherstimme (Piper) + Soundeffekte + animierte Szenen.
Aufruf: python3 build.py <video.json> <out.mp4>"""
import json, sys, os, subprocess, wave, numpy as np
KIT = os.path.dirname(os.path.abspath(__file__))
SCR = os.path.dirname(os.path.dirname(KIT))
PIPER = os.path.join(SCR, 'tts/bin/piper')
VOICE = os.path.join(SCR, 'voices/de_DE-thorsten-high.onnx')
SR = 44100

def read(p):
    w = wave.open(p); sr = w.getframerate(); x = np.frombuffer(w.readframes(w.getnframes()), '<i2').astype(np.float32) / 32768
    if w.getnchannels() == 2: x = x.reshape(-1, 2).mean(1)
    if sr != SR: x = np.interp(np.arange(int(len(x) * SR / sr)) * sr / SR, np.arange(len(x)), x)
    return x

spec = json.load(open(sys.argv[1])); out = sys.argv[2]
work = out + '.work'; os.makedirs(work + '/fr', exist_ok=True)
t = 0.35; voice = []
for i, s in enumerate(spec['scenes']):
    wav = f'{work}/v{i}.wav'
    subprocess.run([PIPER, '-m', VOICE, '-f', wav, '--length-scale', str(spec.get('speed', 0.92)), '--sentence-silence', '0.1'],
                   input=s['say'].encode(), check=True, capture_output=True)
    x = read(wav); d = len(x) / SR
    s['v0'] = t + 0.25; s['vd'] = d; s['t0'] = t
    s['t1'] = t + 0.25 + d + (1.6 if s['type'] == 'end' else 0.45)
    voice.append((s['v0'], x)); t = s['t1']
total = t
# Tonspur: Stimme + Effekte
mix = np.zeros(int(SR * (total + 0.5)), np.float32)
def put(at, x, g=1.0):
    a = int(at * SR); b = min(len(mix), a + len(x)); mix[a:b] += g * x[:b - a]
for at, x in voice: put(at, x, 0.95)
fx = {n: read(f'{KIT}/{n}.wav') for n in ('whoosh', 'pop', 'tick', 'glow')}
for k, s in enumerate(spec['scenes']):
    if k: put(s['t0'] - 0.15, fx['whoosh'], 0.5)
    if s['type'] == 'receptors':
        for j in range(len(s['labels'])): put(s['t0'] + 0.8 + j * 0.8, fx['pop'], 0.6)
    if s['type'] == 'chips':
        for j in range(len(s['chips'])): put(s['t0'] + 0.6 + j * 0.35, fx['pop'], 0.45)
    if s['type'] == 'timeline':
        for j in range(len(s['steps'])): put(s['t0'] + 0.5 + j * 0.45, fx['tick'], 0.6)
    if s['type'] == 'end': put(s['t0'], fx['glow'], 0.6)
mix /= max(1.0, np.abs(mix).max() / 0.95)
w = wave.open(f'{work}/audio.wav', 'wb'); w.setnchannels(1); w.setsampwidth(2); w.setframerate(SR); w.writeframes((mix * 32767).astype('<i2').tobytes()); w.close()
json.dump(spec, open(f'{work}/timed.json', 'w'), ensure_ascii=False)
# Bilder rendern
subprocess.run(['node', f'{KIT}/rec2.js', f'{work}/timed.json', f'{work}/fr', str(total)], check=True, cwd=KIT)
subprocess.run(['ffmpeg', '-v', 'error', '-y', '-framerate', '30', '-i', f'{work}/fr/%05d.jpg', '-i', f'{work}/audio.wav',
                '-c:v', 'libx264', '-preset', 'medium', '-crf', '19', '-pix_fmt', 'yuv420p', '-af', 'loudnorm=I=-14:TP=-1.5:LRA=11', '-ar', '44100', '-c:a', 'aac', '-b:a', '160k',
                '-shortest', '-movflags', '+faststart', out], check=True)
print(f'{out}: {total:.1f}s')
