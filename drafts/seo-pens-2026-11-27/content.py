# SEO texts for the Peptrium-Pens (same pattern as drafts/seo-produkte-2026-10-27/content.py).
# Placeholders: {preis:ID} (product or variation id), {versandfrei}. Research framing only: no effects, no dosing, no human use.
import json, sys, os, html

NOTE = '<p class="sp-ab-note"><strong>Nur für Laborforschung.</strong> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</p>'

def was(name, stoff, groessen, vial_url):
    return (f'<h3>Was ist der {name}?</h3><p>Der {name} ist ein <strong>vorgefüllter Pen mit {stoff} in fertig angesetzter Lösung</strong> – erhältlich mit {groessen}. '
            f'Anders als beim klassischen <a href="{vial_url}">{stoff}-Vial</a> entfällt das Rekonstituieren: kein Bac Water abmessen, kein Aufziehen mit der Spritze. '
            'Die Entnahmemenge stellst du über das Klick-Rad am Pen ein, eine Anzeige zeigt die eingestellte Menge. '
            'Wir setzen jeden Pen <strong>erst nach deiner Bestellung frisch an</strong> – er liegt also nicht monatelang im Lager.</p>')

def achten(stoff):
    return (f'<h3>{stoff} Pen kaufen – worauf du achten solltest</h3><ul>'
            f'<li><strong>Geprüfter Wirkstoff:</strong> das {stoff} im Pen stammt aus denselben Chargen wie unsere Vials – per HPLC auf Reinheit und per LC-MS auf Identität geprüft, Zielwert ≥ 99 %. Mehr dazu im Ratgeber <a href="/ratgeber/hplc-lc-ms-reinheit/">HPLC & LC-MS</a>.</li>'
            '<li><strong>Frisch angesetzt:</strong> der Pen wird erst bei Bestelleingang befüllt und dann direkt verschickt.</li>'
            '<li><strong>Versand aus Deutschland:</strong> kein Zoll, kein wochenlanges Warten auf Übersee-Pakete.</li>'
            '<li><strong>Erreichbarer Support:</strong> Fragen zum Pen beantworten wir per <a href="https://t.me/peptrium">Telegram</a> oder E-Mail.</li></ul>')

def zubehoer():
    return ('<h3>Was du zusätzlich brauchst</h3><p>Für den Pen brauchst du <a href="/produkt/pen-nadeln-32g-4mm/">Pen-Nadeln 32G × 4 mm</a> '
            '({preis:908} für 15 Stück) – sterile Einmal-Nadeln, die einfach auf den Pen geschraubt werden. '
            '<strong>Tipp:</strong> Ab 200 € Bestellwert legen wir dir ein Injektionskit gratis dazu – bei einer Pen-Bestellung kannst du im Warenkorb wählen, dass es <strong>Pen-Nadeln statt Spritzen</strong> enthält.</p>')

def versand(name):
    return (f'<h3>Versand & Bezahlung</h3><p>Wir versenden den {name} per DHL aus Deutschland, diskret und neutral verpackt. '
            'Nach Zahlungseingang ist dein Paket in der Regel innerhalb von 2 Werktagen bei dir, die Sendungsnummer bekommst du per E-Mail. '
            'Innerhalb Deutschlands ist der Versand ab {versandfrei} gratis – <strong>jeder Pen liegt darüber</strong>. Bezahlen kannst du per Vorkasse (Überweisung) oder mit Krypto.</p>')

def lager():
    return ('<h3>Lagerung</h3><p>Den Pen gekühlt bei 2–8 °C und lichtgeschützt lagern, <strong>nicht einfrieren</strong>. '
            'Nach dem Gebrauch die Nadel abschrauben und die Schutzkappe aufsetzen. Mehr zur Lagerung von Peptiden findest du im <a href="/lagerungs-guide/">Lagerungs-Guide</a>.</p>')

def pen_vs_vial(stoff, vial_url, extra=''):
    return (f'<h3>Pen oder Vial – was passt besser?</h3><ul>'
            '<li><strong>Pen:</strong> fertig angesetzt, kein Anmischen, Menge per Klick einstellbar – die bequemste Variante, besonders wenn du ohne Spritze und Bac Water arbeiten möchtest.</li>'
            f'<li><strong>Vial:</strong> <a href="{vial_url}">{stoff} als lyophilisiertes Pulver</a> – pro Milligramm deutlich günstiger, muss aber mit <a href="/produkt/bac-water/">Bac Water</a> angesetzt und mit einer <a href="/produkt/insulinspritze-10er-pack/">Spritze</a> aufgezogen werden.</li></ul>{extra}'
            '<p>Alle Pens im Überblick findest du unter <a href="/alle-peptrium-pens/">Peptrium-Pens</a>.</p>')

FAQ_VERSAND = lambda name: [f"Wie schnell wird der {name} geliefert?", "Wir versenden per DHL aus Deutschland. Nach Zahlungseingang ist dein Paket in der Regel innerhalb von 2 Werktagen bei dir. Der Versand ist bei jedem Pen innerhalb Deutschlands gratis."]
FAQ_ANMISCHEN = lambda stoff: ["Muss ich den Pen selbst anmischen?", f"Nein. Das {stoff} ist im Pen bereits fertig angesetzt. Du brauchst kein Bac Water und keine Spritze – nur Pen-Nadeln, die auf den Pen geschraubt werden."]
FAQ_NADELN = ["Brauche ich Pen-Nadeln?", "Ja, die Nadeln sind nicht im Pen enthalten. Unsere Pen-Nadeln 32G × 4 mm passen auf alle Peptrium-Pens ({preis:908} für 15 Stück). Ab 200 € Bestellwert bekommst du ein Injektionskit gratis dazu und kannst im Warenkorb Pen-Nadeln statt Spritzen wählen."]
FAQ_LAGER = ["Wie lagere ich den Pen?", "Gekühlt bei 2–8 °C und lichtgeschützt, nicht einfrieren. Wir verschicken den Pen frisch angesetzt – am besten legst du ihn nach dem Auspacken direkt in den Kühlschrank."]
FAQ_ZUL = lambda stoff: [f"Ist {stoff} als Arzneimittel zugelassen?", f"Nein. {stoff} ist nicht als Arzneimittel zugelassen. Wir verkaufen den Pen ausschließlich für Laborzwecke – nicht zur Anwendung am Menschen oder Tier."]

P = {}

# ---------------- Retatrutide-Pen (393) ----------------
P[393] = dict(
 name="Peptrium-Pen (Retatrutide)",
 h2="Retatrutide Pen kaufen – alles Wichtige auf einen Blick",
 seo=["Retatrutide Pen kaufen – 20, 30 & 40 mg, Versand aus DE",
      "Retatrutide Pen kaufen: fertig angesetzt, kein Anmischen. 20, 30 oder 40 mg, HPLC- und LC-MS-geprüft. Gratis-Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf des Retatrutide Pens wissen solltest: was im Pen steckt, welche Größe zu dir passt, was du zusätzlich brauchst – und wie er geliefert und gelagert wird.</p>'
  + was("Peptrium-Pen (Retatrutide)", "Retatrutide", "20, 30 oder 40 mg", "/produkt/retatrutide/") +
  '<h3>Was ist Retatrutide?</h3><p>Retatrutide (LY3437943) ist ein Forschungspeptid, das gleichzeitig an drei Rezeptoren ansetzt – GLP-1, GIP und Glukagon. In der Szene wird es deshalb oft „GLP-3“ genannt; was dahintersteckt, erklärt der Ratgeber <a href="/ratgeber/was-ist-glp-3/">Was ist GLP-3?</a>. Retatrutide ist nicht als Arzneimittel zugelassen.</p>'
  + achten("Retatrutide") +
  '<h3>Größen & Preise</h3><ul><li><strong>20 mg:</strong> {preis:547}</li><li><strong>30 mg:</strong> {preis:548}</li><li><strong>40 mg:</strong> {preis:549}</li></ul>'
  '<p>Pro Milligramm ist der 40-mg-Pen am günstigsten und liegt damit nah am Preis der Vials. Wenn du dir bei der Menge unsicher bist, hilft der Ratgeber <a href="/ratgeber/retatrutide-10mg-oder-20mg/">Retatrutide 10 mg oder 20 mg?</a> beim Einordnen der Größen.</p>'
  + pen_vs_vial("Retatrutide", "/produkt/retatrutide/") + zubehoer() + versand("Retatrutide Pen") + lager() +
  '<h3>Mehr zu Retatrutide</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/peptrium-pen-retatrutide/">Peptid-Lexikon: Peptrium-Pen (Retatrutide)</a> zusammen. Wie sich Retatrutide von Tirzepatide und Semaglutid unterscheidet, liest du im Ratgeber <a href="/ratgeber/retatrutide-tirzepatide-semaglutid/">Retatrutide, Tirzepatide & Semaglutid</a>.</p>' + NOTE),
 faq=[["Was kostet der Retatrutide Pen?", "20 mg kosten {preis:547}, 30 mg {preis:548} und 40 mg {preis:549}. Der Versand innerhalb Deutschlands ist bei jedem Pen gratis."],
      ["Welche Größe soll ich nehmen – 20, 30 oder 40 mg?", "Alle drei Pens enthalten dieselbe geprüfte Retatrutide-Lösung, nur in unterschiedlicher Gesamtmenge. Pro Milligramm ist der 40-mg-Pen am günstigsten."],
      FAQ_ANMISCHEN("Retatrutide"), FAQ_NADELN, FAQ_VERSAND("Retatrutide Pen"), FAQ_LAGER, FAQ_ZUL("Retatrutide")])

# ---------------- GHK-Cu-Pen (395) ----------------
P[395] = dict(
 name="Peptrium-Pen (GHK-Cu)",
 h2="GHK-Cu Pen kaufen – alles Wichtige auf einen Blick",
 seo=["GHK-Cu Pen kaufen – 50 & 100 mg, Versand aus DE",
      "GHK-Cu Pen kaufen: Kupferpeptid fertig angesetzt, kein Anmischen. 50 oder 100 mg, HPLC- und LC-MS-geprüft. Gratis-Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf des GHK-Cu Pens wissen solltest: was im Pen steckt, welche Größe zu dir passt, was du zusätzlich brauchst – und wie er geliefert und gelagert wird.</p>'
  + was("Peptrium-Pen (GHK-Cu)", "GHK-Cu", "50 oder 100 mg", "/produkt/ghk-cu/") +
  '<h3>Was ist GHK-Cu?</h3><p>GHK-Cu ist ein Komplex aus dem Tripeptid Glycyl-L-Histidyl-L-Lysin und einem Kupfer(II)-Ion – deshalb auch <strong>Kupferpeptid</strong> oder Copper Tripeptide-1 genannt. Untersucht wird es vor allem in Haut-, Kollagen- und Bindegewebsmodellen. Durch das Kupfer ist die Lösung leicht bläulich – das ist typisch und kein Qualitätsmangel. GHK-Cu ist nicht als Arzneimittel zugelassen.</p>'
  + achten("GHK-Cu") +
  '<h3>Größen & Preise</h3><ul><li><strong>50 mg:</strong> {preis:597}</li><li><strong>100 mg:</strong> {preis:598}</li></ul>'
  '<p>Pro Milligramm ist der 100-mg-Pen deutlich günstiger als der 50-mg-Pen.</p>'
  + pen_vs_vial("GHK-Cu", "/produkt/ghk-cu/", '<p>Wer GHK-Cu zusammen mit BPC-157 und TB-500 untersuchen möchte, findet die Kombination im Blend <a href="/produkt/glow/">GLOW</a> (als Vial).</p>') + zubehoer() + versand("GHK-Cu Pen") + lager() +
  '<h3>Mehr zu GHK-Cu</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/peptrium-pen-ghk-cu/">Peptid-Lexikon: Peptrium-Pen (GHK-Cu)</a> zusammen. Wie GHK-Cu im GLOW-Blend kombiniert wird, erklärt der Ratgeber <a href="/ratgeber/glow-peptid-blend/">GLOW-Peptid-Blend</a>.</p>' + NOTE),
 faq=[["Was kostet der GHK-Cu Pen?", "50 mg kosten {preis:597}, 100 mg {preis:598}. Der Versand innerhalb Deutschlands ist bei jedem Pen gratis."],
      ["Warum ist die Lösung bläulich?", "Die Farbe kommt vom Kupfer(II)-Ion im GHK-Cu. Sie ist typisch für Kupferpeptide und kein Qualitätsmangel."],
      FAQ_ANMISCHEN("GHK-Cu"), FAQ_NADELN, FAQ_VERSAND("GHK-Cu Pen"), FAQ_LAGER, FAQ_ZUL("GHK-Cu")])

# ---------------- MOTS-c-Pen (396) ----------------
P[396] = dict(
 name="Peptrium-Pen (MOTS-c)",
 h2="MOTS-c Pen kaufen – alles Wichtige auf einen Blick",
 seo=["MOTS-c Pen kaufen – 20 & 40 mg, Versand aus DE",
      "MOTS-c Pen kaufen: mitochondriales Peptid fertig angesetzt, kein Anmischen. 20 oder 40 mg, HPLC- und LC-MS-geprüft. Gratis-Versand aus Deutschland."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf des MOTS-c Pens wissen solltest: was im Pen steckt, welche Größe zu dir passt, was du zusätzlich brauchst – und wie er geliefert und gelagert wird.</p>'
  + was("Peptrium-Pen (MOTS-c)", "MOTS-c", "20 oder 40 mg", "/produkt/mots-c/") +
  '<h3>Was ist MOTS-c?</h3><p>MOTS-c ist ein aus 16 Aminosäuren bestehendes Peptid, das nicht im Zellkern, sondern im <strong>Erbgut der Mitochondrien</strong> codiert ist – deshalb zählt es zu den sogenannten mitochondrial abgeleiteten Peptiden. Untersucht wird es vor allem in Modellen zum Energie- und Glukosestoffwechsel. MOTS-c ist nicht als Arzneimittel zugelassen.</p>'
  + achten("MOTS-c") +
  '<h3>Größen & Preise</h3><ul><li><strong>20 mg:</strong> {preis:544}</li><li><strong>40 mg:</strong> {preis:545}</li></ul>'
  '<p>Pro Milligramm ist der 40-mg-Pen deutlich günstiger als der 20-mg-Pen.</p>'
  + pen_vs_vial("MOTS-c", "/produkt/mots-c/") + zubehoer() + versand("MOTS-c Pen") + lager() +
  '<h3>Mehr zu MOTS-c</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/peptrium-pen-mots-c/">Peptid-Lexikon: Peptrium-Pen (MOTS-c)</a> zusammen.</p>' + NOTE),
 faq=[["Was kostet der MOTS-c Pen?", "20 mg kosten {preis:544}, 40 mg {preis:545}. Der Versand innerhalb Deutschlands ist bei jedem Pen gratis."],
      ["Was bedeutet „mitochondriales Peptid“?", "MOTS-c ist im Erbgut der Mitochondrien codiert, nicht im Zellkern. Deshalb wird es zu den mitochondrial abgeleiteten Peptiden (MDPs) gezählt."],
      FAQ_ANMISCHEN("MOTS-c"), FAQ_NADELN, FAQ_VERSAND("MOTS-c Pen"), FAQ_LAGER, FAQ_ZUL("MOTS-c")])

PRICES = {547: '264,90 €', 548: '299,90 €', 549: '339,90 €', 597: '169,90 €', 598: '199,90 €', 544: '189,90 €', 545: '234,90 €', 908: '9,90 €'}
SLUG = {393: 'peptrium-pen-retatrutide', 395: 'peptrium-pen-ghk-cu', 396: 'peptrium-pen-mots-c'}

def fill(s):
    import re
    s = re.sub(r'\{preis:(\d+)\}', lambda m: PRICES[int(m.group(1))], s)
    return s.replace('{versandfrei}', '150,00 €')

if __name__ == '__main__':
    here = os.path.dirname(os.path.abspath(__file__))
    tpl = open(os.path.join(here, '..', 'seo-produkte-2026-10-27', 'v-ghk-cu.html'), encoding='utf-8').read()
    head = tpl[:tpl.index('<body>') + 6]
    for pid, d in P.items():
        faq = ''.join(f'<details class=q open><summary>{html.escape(fill(q))}</summary><p>{fill(a)}</p></details>' for q, a in d['faq'])
        page = (head + f'<div class=serp><div class=u>peptrium.com › produkt › {SLUG[pid]}</div><div class=t>{html.escape(d["seo"][0])} | Peptrium</div><div class=d>{html.escape(d["seo"][1])}</div></div>'
                f'<section id=sp-pp-about><div class=in><span class=ey>Produktinfo</span><h2>{d["h2"]}</h2><div>{fill(d["body"])}</div>'
                f'<div class=fq><h3>Häufige Fragen zum Kauf</h3>{faq}</div></div></section></body></html>')
        open(os.path.join(here, f'v-{SLUG[pid]}.html'), 'w', encoding='utf-8').write(page)
    json.dump({str(k): {'h2': v['h2'], 'seo': v['seo'], 'body': v['body'], 'faq': v['faq']} for k, v in P.items()},
              open(os.path.join(here, 'pens.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    for pid, d in P.items():
        import re
        words = len(re.sub('<[^>]+>', ' ', fill(d['body'])).split())
        print(pid, words, 'Wörter', len(d['seo'][1]), 'Zeichen Beschreibung')
