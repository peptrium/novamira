# Shop-Redesign – Fortschritt

Alles wird zuerst als **Entwurf im Vorschau-Modus** gebaut (Link `https://peptrium.com/?sp_vorschau=<SP_HPV_TOKEN>`,
Cookie 24 h, beenden über die orange Pille „ENTWURF-VORSCHAU ✕“). Normale Besucher sehen nichts davon.
Livegang in Wellen, jede Welle per Schalter zurückdrehbar.

Vorschau-Plugins (mu-plugins): `sp-ds.php` (gemeinsame Ebene), `sp-home-preview.php`, `sp-product-preview.php`,
`sp-category-preview.php`. Quellen in `drafts/`.

## Phase 0 – Fundament
- [x] Vorschau-Modus per Cookie, durchklickbar
- [x] `sp-ds.php` als gemeinsame Ebene (Header-Fix, Menü, 404, Ziel-Weiterleitungen)
- [ ] CSS der Vorschauen in eine zentrale Datei zusammenführen (vor dem Livegang)

## Phase 1 – Header, Menü, Suche, Footer, 404
- [x] Header: kein Astra-Blau bei aktiven Knöpfen
- [x] Menü: „Nach Ziel suchen“ → „Forschungsbereiche“ (6 Kategorien + Alle Produkte)
- [x] 13 Ziel-Seiten (veraltete feste Preise!) → Weiterleitung auf passende Kategorie (beim Livegang 301)
- [x] 404-Seite neu (Suche öffnet Header-Suche, Links, Forschungsbereiche)
- [x] Footer-Spalten (live), Newsletter-Box (live)
- [x] Suche: Live-Suche im Header bleibt; `?s=` leitet weiter auf Alle Produkte (bestehend)
- [ ] Altersabfrage: optisch an neues Design angleichen (optional)

## Phase 2 – Shop-Seiten
- [x] Startseite (Entwurf)
- [x] Kategorie-Seiten, Alle Produkte, Vorbestellung, Pens-Seite, Zubehör-Seite (Entwurf, Live-Preise)
- [x] Produktseite Retatrutide (Entwurf)
- [x] Alle übrigen Produktseiten (Peptide, Pens, Zubehör) – Entwurf; Pen-Nadeln-Seite (eigener Aufbau) noch offen

## Phase 3 – Kauf
- [x] Warenkorb-Drawer inkl. Geschenkstufen (Entwurf, sp-cart-preview.php)
- [x] Kasse (Entwurf, sp-checkout-preview.php, nur Optik)
- [x] Danke-Seite (Entwurf, sp-thankyou-preview.php)
- [ ] Warenkorb-Seite (falls genutzt)

## Phase 4 – Kundenkonto
- [ ] Login/Registrierung, Dashboard, Bestellungen, Abo, Guthaben, Adressen, Kontodaten, Partner-Bereich, Bestellstatus

## Phase 5 – Inhaltsseiten
- [ ] Abo-Modell, Abo-Stack, Guthaben aufladen, Peptid-Rechner, Reta-Dosierung, Lagerungs-Guide, Peptid-Lexikon
- [ ] Über uns, Kontakt, FAQ, Versand (Text live korrigiert), AGB, Datenschutz, Widerruf, Forschungsnutzung, Partner-Programm

## Phase 6 – Test & Livegang
- [ ] Klick-Test aller Buttons/Links je Seite
- [ ] Testbestellungen (Test-Konto, als Test markiert): normal, Abo, Guthaben, Gutschein, Geschenke
- [ ] Livegang Welle 1 (Shop-Seiten), Welle 2 (Kauf), Welle 3 (Konto + Inhalte)

## Offene Inhalts-Punkte
- Analysezertifikate: kommen später – bis dahin bleiben COA-Hinweise wie sie sind (Entscheidung Nutzer)
- Text „Wirkung & Forschung“ (Abnehm-/24 %-Aussagen) – neutral umschreiben vorgeschlagen, noch offen
- Startseite live: Schritt 3 „Versand innerhalb von 48 Stunden“ → wird mit Livegang korrigiert
