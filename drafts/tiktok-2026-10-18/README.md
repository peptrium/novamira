TikTok-Wissensvideos (Test 2026-10-18, MOTS-c)
- overlay.html: animierte Ebene (render(t)), 1080x1920, transparent
- rec.js: Playwright rendert PNG-Frames (node rec.js 24 → fr/0000.png …)
- ffmpeg: Hintergrund = Clip des Users (576x1024, Text oben weggeschnitten: crop 576:640:0:384),
  2,4x verlangsamt, unscharfe Füllung + scharfer Ausschnitt mit Verlaufsmaske (mask.png), darüber Frames.
- Inhalte aus faq_all.json (Forschungsrahmen, keine Wirkversprechen, keine Dosierung).
