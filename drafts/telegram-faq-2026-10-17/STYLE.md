# Stil für die Peptrium-Telegram-FAQ (verbindlich)

Ziel: Einträge für den FAQ-Bot der Telegram-Gruppe "Peptrium Community" (Shop peptrium.com, verkauft Forschungspeptide
ausdrücklich NUR für Laborforschungszwecke). Quelle: Texte einer fremden Gruppe → NIEMALS übernehmen, immer komplett in
EIGENEN Worten neu schreiben (Urheberrecht). Fakten nur, wenn du dir sicher bist; im Zweifel weglassen statt erfinden.

## Absolute Verbote (rechtlich)
- KEINE Dosierung, Mengen pro Tag/Woche, Einnahmezeitpunkt, Zyklusdauer, Verabreichungswege als Anleitung
  ("subkutan spritzen", "täglich", "Zink einnehmen" usw.), keine Titrationsschemata, keine "Protokolle" mit Mengen.
- KEINE Heil- oder Wirkversprechen am Menschen ("hilft bei", "beschleunigt die Heilung", "verbessert die Haut").
  Stattdessen Forschungsrahmen: "In Studien untersucht: …", "In Tierversuchen wurde … beobachtet".
- Keine Empfehlungen zur Anwendung, keine Nebenwirkungs-Ratschläge, keine Kombinationsempfehlungen.
- Keine fremden Shops/Händler/Gruppen nennen. Keine Zertifikate/COA erwähnen (gibt es noch nicht).
- Zugelassene Arzneimittel (z. B. Semaglutid) neutral als zugelassenes Medikament beschreiben, ohne Anwendungstipps.

## Format = Telegram-HTML
Erlaubt NUR: <b>, <i>, <u>, <a href="…">, Emojis. Kein Markdown, keine anderen Tags. Absätze durch eine Leerzeile.
Aufzählungen mit "• ". Sonderzeichen & < > im Text als &amp; &lt; &gt; schreiben.
Länge: ca. 700–1500 Zeichen pro Eintrag (max. 2500). Du-Form, sachlich, gut lesbar auf dem Handy.

## Aufbau Peptid-Steckbrief (Beispiel unten exakt so)
<b>🧪 NAME</b>
<i>Kurzbezeichnung / Klasse</i>

<b>Was ist es?</b> (2–4 Sätze: Herkunft, Aufbau, Klasse)
<b>Wie funktioniert es?</b> (Mechanismus, wissenschaftlich, verständlich) – optional, wenn bekannt
<b>In Studien untersucht</b> (3–5 Bullets, Forschungsthemen, keine Versprechen)
<b>Studienlage</b> (Zell-/Tier-/Humanstudien? zugelassen? Phase?)
<b>Gut zu wissen</b> – optional (Besonderheit, z. B. Farbe der Lösung, Abgrenzung zu ähnlichem Peptid)
<b>Bei Peptrium</b> – NUR wenn das Peptid im Shop ist (siehe shop.json): Lieferform + Größen, "HPLC/LC-MS-geprüft",
  bei Vorbestellung "aktuell vorbestellbar".
Letzte Zeile immer: <i>Alle Angaben zu Forschungszwecken. Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</i>

Bei "Protokollen"/Stacks/Blends (Cinderella, Gut Shield, Mitochondrial Health, Peaceful Nights, GLOW, KLOW, BPC+TB500):
nur erklären, AUS WELCHEN Substanzen die Kombination besteht und was zu den Einzelstoffen erforscht wird – ohne Mengen,
ohne Ablauf, ohne Empfehlung. Titel z. B. "🧪 GLOW (Peptid-Kombination)".

## Beispiel (freigegeben)
<b>🧪 GHK-Cu</b>
<i>Kupfer-Tripeptid · Glycyl-L-Histidyl-L-Lysin</i>

<b>Was ist es?</b>
GHK-Cu ist ein kleines Peptid aus drei Aminosäuren, das ein Kupfer-Ion bindet. Es kommt natürlicherweise im menschlichen Blutplasma, im Speichel und im Urin vor und wurde 1973 von Dr. Loren Pickart entdeckt. Der Plasmaspiegel sinkt mit dem Alter deutlich.

<b>Wie funktioniert es?</b>
GHK hat eine hohe Affinität zu Kupfer(II) und kann es gezielt zu Zellen transportieren. Kupfer ist Cofaktor wichtiger Enzyme – etwa der Lysyloxidase, die Kollagen vernetzt. Zusätzlich beeinflusst GHK in Zellversuchen die Aktivität zahlreicher Gene, die mit Gewebeumbau und Reparatur zusammenhängen.

<b>In Studien untersucht</b>
• Wundheilung und Geweberegeneration
• Kollagen- und Elastinbildung
• Neubildung von Blutgefäßen (Angiogenese)
• Entzündungs- und antioxidative Prozesse

<b>Studienlage</b>
Überwiegend Zell- und Tierstudien sowie Studien zur äußerlichen Anwendung in der Kosmetik – große klinische Studien gibt es bisher nicht.

<b>Gut zu wissen</b>
Gelöstes GHK-Cu ist durch das Kupfer blau gefärbt – das ist normal.

<b>Bei Peptrium</b>
Lyophilisiert als 50 mg und 100 mg, außerdem als Peptrium-Pen. HPLC/LC-MS-geprüft.

<i>Alle Angaben zu Forschungszwecken. Nicht für den menschlichen oder veterinärmedizinischen Gebrauch.</i>

## Aufbau Lagerung/Material-Eintrag
<b>🧊 FRAGE</b> (Material: 🧰) – dann kurze Absätze und Bullets, Laborrahmen ("im Labor", "für die Rekonstitution").
"Wo kaufe ich X?"-Fragen: auf die passenden Peptrium-Produkte verweisen (Bac Water 3 ml, Insulinspritze 10er Pack 1 ml,
Pen Nadeln 32G x 4 mm 15er Pack, Peptrium-Pens) – Fragen, die NUR fremde Shops/Produkte empfehlen und für die es bei
Peptrium nichts gibt (z. B. Etikettendrucker, Thermobehälter-Marken, "welche Shops werden empfohlen", Filter kaufen),
mit "skip": true markieren. Keine Anleitung zum Injizieren am Menschen; Rekonstitution nur als Laborvorgang.
Letzte Zeile bei Lagerung/Material: keine Pflicht, aber kein Anwendungsbezug zum Menschen.

## Ausgabe
JSON-Datei (UTF-8) als Liste von Objekten:
{"key": "ghk-cu", "title": "GHK-Cu", "aliases": ["ghk", "ghk cu", "kupferpeptid"], "cat": "peptide"|"lagerung"|"material",
 "shop_url": "https://peptrium.com/produkt/…/" oder "", "question": "Originalfrage", "skip": false, "text": "<b>…"}
- key: kleingeschrieben, Bindestriche. aliases: übliche Schreibweisen/Kurzformen/Codes (z. B. "reta", "ly3437943").
- shop_url: nur wenn exakt dieses Peptid (oder ein Pen davon) im Shop ist; sonst "".
- Für Lagerung/Material: title = kurze Frage (max. 45 Zeichen, für einen Button), aliases = 1–3 Suchwörter.
