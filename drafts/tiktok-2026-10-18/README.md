TikTok-Wissensvideos (Test 2026-10-18, MOTS-c)
- overlay.html: animierte Ebene (render(t)), 1080x1920, transparent
- rec.js: Playwright rendert PNG-Frames (node rec.js 24 → fr/0000.png …)
- ffmpeg: Hintergrund = Clip des Users (576x1024, Text oben weggeschnitten: crop 576:640:0:384),
  2,4x verlangsamt, unscharfe Füllung + scharfer Ausschnitt mit Verlaufsmaske (mask.png), darüber Frames.
- Inhalte aus faq_all.json (Forschungsrahmen, keine Wirkversprechen, keine Dosierung).

## Video-Kit v2 (Stimme + Soundeffekte), 2026-10-18
- engine.html: animierter Hintergrund (Partikel + rotierende Peptidkette in Akzentfarbe), Logo oben,
  Fortschrittsleiste, Szenentypen hook/card/receptors/chips/timeline/end, Wort-für-Wort-Untertitel.
- build.py <video.json> <out.mp4>: Piper-TTS (de_DE-thorsten-high, im Scratchpad: tts/ + voices/)
  pro Szene → Szenenlängen aus Sprachdauer; sfx.py erzeugt whoosh/pop/tick/glow (eigene Synthese);
  rec2.js rendert JPG-Frames; ffmpeg + loudnorm -14 LUFS.
- video.json: accent, speed, scenes[{type, say (TTS-Text, Abkürzungen ausschreiben z. B. "G L P eins"),
  sub (Untertitel in Schriftform), …}].
- reta.json = erstes Video (Retatrutide, 34 s). Upload: uploads/sp-video/.
