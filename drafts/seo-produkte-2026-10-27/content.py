# SEO texts v2 for further products (same pattern as drafts/seo-retatrutide-2026-10-27/content.py).
# Placeholders: {preis:ID}, {versandfrei}. Research framing only: no effects, no dosing, no human use.

NOTE = '<p class="sp-ab-note"><strong>Nur für Laborforschung.</strong> Nicht zur Anwendung am Menschen oder Tier und nicht für diagnostische oder therapeutische Zwecke bestimmt.</p>'

def versand(name):
    return (f'<h3>Versand & Bezahlung</h3><p>Wir versenden {name} per DHL aus Deutschland, diskret und neutral verpackt. '
            'Nach Zahlungseingang ist dein Paket in der Regel innerhalb von 2 Werktagen bei dir, die Sendungsnummer bekommst du per E-Mail. '
            'Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} Bestellwert gratis. Bezahlen kannst du per Vorkasse (Überweisung) oder mit Krypto.</p>')

def reko(name, extra=''):
    return (f'<h3>Lieferform & Rekonstitution</h3><p>{name} wird als lyophilisiertes (gefriergetrocknetes) Pulver im versiegelten Vial geliefert. '
            'Für die Arbeit im Labor wird es mit <a href="/produkt/bac-water/">bakteriostatischem Wasser (Bac Water)</a> rekonstituiert – '
            'das Wasser langsam an der Glaswand entlang zugeben und das Vial sanft schwenken, nicht schütteln. '
            'Welche Wassermenge welche Konzentration ergibt, berechnest du mit unserem <a href="/peptid-rechner/">Peptid-Rechner</a>; '
            f'eine Schritt-für-Schritt-Anleitung gibt es im Ratgeber <a href="/ratgeber/peptide-rekonstituieren/">Peptide rekonstituieren</a>.{extra}</p>')

def lager():
    return ('<h3>Lagerung & Haltbarkeit</h3><p>Lyophilisiert kühl bei 2–8 °C und lichtgeschützt lagern, für längere Zeit bei −20 °C. '
            'Nach der Rekonstitution gekühlt aufbewahren, nicht wieder einfrieren und zügig verwenden. Details findest du im <a href="/lagerungs-guide/">Lagerungs-Guide</a>.</p>')

def achten(name, menge):
    return (f'<h3>{name} kaufen in Deutschland – worauf du achten solltest</h3><ul>'
            '<li><strong>Geprüfte Reinheit:</strong> jede Charge per HPLC auf Reinheit und per LC-MS auf Identität geprüft – unser Zielwert liegt bei ≥ 99 %. Was die Verfahren bedeuten, erklärt der Ratgeber <a href="/ratgeber/hplc-lc-ms-reinheit/">HPLC & LC-MS</a>.</li>'
            f'<li><strong>Klare Lieferform:</strong> Lyophilisat im versiegelten Vial mit eindeutiger Mengenangabe ({menge}).</li>'
            '<li><strong>Versand aus Deutschland:</strong> kein wochenlanges Warten auf Übersee-Pakete und kein Zoll.</li>'
            '<li><strong>Erreichbarer Support:</strong> Fragen beantworten wir per <a href="https://t.me/peptrium">Telegram</a> oder E-Mail.</li></ul>')

FAQ_VERSAND = lambda name: [f"Wie schnell wird {name} geliefert?", "Wir versenden per DHL aus Deutschland. Nach Zahlungseingang ist dein Paket in der Regel innerhalb von 2 Werktagen bei dir. Die Sendungsnummer bekommst du per E-Mail."]
FAQ_ZAHL = ["Wie kann ich bezahlen?", "Per Vorkasse (Überweisung) oder mit Kryptowährung. Die Bankdaten bekommst du direkt nach der Bestellung auf der Bestätigungsseite und per E-Mail."]
FAQ_LAGER = lambda name: [f"Wie wird {name} geliefert und gelagert?", "Als lyophilisiertes Pulver im versiegelten Vial. Ungeöffnet bei 2–8 °C und lichtgeschützt lagern, für längere Zeit bei −20 °C. Nach der Rekonstitution mit Bac Water gekühlt aufbewahren und zügig verwenden."]
FAQ_ZUL = lambda name, extra='': [f"Ist {name} als Arzneimittel zugelassen?", f"Nein. {name} ist nicht als Arzneimittel zugelassen.{extra} Wir verkaufen es ausschließlich als Forschungspeptid für Laborzwecke – nicht zur Anwendung am Menschen oder Tier."]
RABATT = "Ab 3 Vials gilt zusätzlich unser Mengenrabatt (−10 %, ab 5 Stück −15 %, ab 10 Stück −20 %)."

P = {}

# ---------------- CJC-1295 + Ipamorelin (77) ----------------
P[77] = dict(
 name="CJC-1295 + Ipamorelin",
 h2="CJC-1295 + Ipamorelin kaufen – alles Wichtige auf einen Blick",
 seo=["CJC-1295 + Ipamorelin kaufen – 10 mg, Versand aus DE",
      "CJC-1295 (ohne DAC) + Ipamorelin kaufen: 10 mg im Vial, HPLC- und LC-MS-geprüft, ≥ 99 % Reinheit. Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von CJC-1295 + Ipamorelin wissen solltest: was in dem Vial steckt, wie die beiden Peptide zusammenhängen, wie geliefert und gelagert wird – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist CJC-1295 + Ipamorelin?</h3><p>Die Kombination vereint zwei Forschungspeptide in einem Vial: <strong>CJC-1295 ohne DAC</strong> (auch „Modified GRF 1-29“ oder „Mod GRF“ genannt), ein stabilisiertes Analogon des Wachstumshormon-Releasing-Hormons (GHRH), und <strong>Ipamorelin</strong>, ein selektiver Agonist am Ghrelin-Rezeptor (GHS-R). Die Variante <em>mit</em> DAC (Drug Affinity Complex) ist ein anderes Molekül mit deutlich längerer Halbwertszeit – bei uns bekommst du die Variante ohne DAC. Beide Peptide sind nicht als Arzneimittel zugelassen.</p>'
  '<h3>CJC-1295 und Ipamorelin in der Forschung</h3><p>Untersucht werden die beiden unterschiedlichen Signalwege, über die die Hypophyse Wachstumshormon freisetzt – den GHRH-Weg und den Ghrelin-Weg – sowie deren Zusammenspiel. Ipamorelin gilt dabei als besonders rezeptorselektiv im Vergleich zu älteren GHRPs wie GHRP-2 oder GHRP-6. Weil beide Peptide häufig gemeinsam untersucht werden, sparst du mit dem Kombi-Vial einen Ansatz.</p>'
  + achten("CJC-1295", "10 mg") +
  '<h3>Größe & Preis</h3><p>CJC-1295 + Ipamorelin gibt es bei uns als <strong>10-mg-Vial für {preis:77}</strong>. ' + RABATT + ' Für die Rekonstitution brauchst du <a href="/produkt/bac-water/">Bac Water</a>, zum Abmessen eignen sich unsere <a href="/produkt/insulinspritze-10er-pack/">Insulinspritzen</a>.</p>'
  + versand("CJC-1295 + Ipamorelin") + reko("CJC-1295 + Ipamorelin") + lager() +
  '<h3>Verwandte Peptide</h3><p>Ein weiteres GHRH-Analogon in unserem Sortiment ist <a href="/produkt/tesamorelin/">Tesamorelin</a>. Herkunft, Aufbau und Forschungsstand der Kombination fasst das <a href="/peptid-lexikon/cjc-1295-ipamorelin/">Peptid-Lexikon: CJC-1295 + Ipamorelin</a> zusammen.</p>' + NOTE),
 faq=[["Was kostet CJC-1295 + Ipamorelin bei Peptrium?", "Das 10-mg-Vial kostet {preis:77}. Ab 3 Vials gibt es Mengenrabatt (−10 %, ab 5 −15 %, ab 10 −20 %). Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Ist das CJC-1295 mit oder ohne DAC?", "Ohne DAC – also Modified GRF 1-29, kombiniert mit Ipamorelin in einem Vial. CJC-1295 mit DAC ist ein anderes Molekül mit einer zusätzlichen Bindungsgruppe und deutlich längerer Halbwertszeit."],
      ["Wo kann ich CJC-1295 in Deutschland kaufen?", "Direkt hier bei Peptrium: Versand aus Deutschland per DHL, in der Regel in 2 Werktagen, jede Charge HPLC- und LC-MS-geprüft."],
      FAQ_VERSAND("CJC-1295 + Ipamorelin"), FAQ_ZUL("CJC-1295"), FAQ_LAGER("CJC-1295 + Ipamorelin"), FAQ_ZAHL])

# ---------------- GHK-Cu (68) ----------------
P[68] = dict(
 name="GHK-Cu",
 h2="GHK-Cu kaufen – alles Wichtige auf einen Blick",
 seo=["GHK-Cu kaufen – 50 & 100 mg, Versand aus DE",
      "GHK-Cu (Kupferpeptid, Copper Tripeptide-1) kaufen: 50 mg & 100 mg, HPLC- und LC-MS-geprüft, ≥ 99 % Reinheit. Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von GHK-Cu wissen solltest: was das Kupferpeptid ist, welche Größen es gibt, wie es geliefert und gelagert wird – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist GHK-Cu?</h3><p>GHK-Cu ist ein Komplex aus dem Tripeptid Glycyl-L-Histidyl-L-Lysin (GHK) und einem Kupfer(II)-Ion – deshalb auch <strong>Kupferpeptid</strong>, „Copper Peptide“ oder <strong>Kupfer-Tripeptid-1</strong> (Copper Tripeptide-1) genannt. GHK wurde in den 1970er-Jahren im menschlichen Blutplasma entdeckt und gehört zu den am längsten untersuchten kupferbindenden Peptiden. Das lyophilisierte Pulver hat durch das Kupfer eine typische blaue Farbe. GHK-Cu ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>GHK-Cu in der Forschung</h3><p>In Zellkultur- und Tiermodellen wird GHK-Cu vor allem im Zusammenhang mit Fibroblasten, dem Aufbau der extrazellulären Matrix (Kollagen, Elastin, Glykosaminoglykane) und der Regulation von Genexpressionsmustern untersucht. Auch die Rolle von Kupferionen bei enzymatischen Prozessen ist ein Forschungsschwerpunkt.</p>'
  + achten("GHK-Cu", "50 mg oder 100 mg") +
  '<h3>50 mg oder 100 mg?</h3><p>GHK-Cu gibt es bei uns als <strong>50-mg-Vial für {preis:1011}</strong> und als <strong>100-mg-Vial für {preis:1012}</strong>. Welche Größe passt, hängt vom Umfang deiner Versuchsreihe ab – mit dem 100-mg-Vial zahlst du weniger pro Milligramm. ' + RABATT + ' Fertig gelöst gibt es GHK-Cu im <a href="/produkt/peptrium-pen-ghk-cu/">Peptrium-Pen</a> (50 oder 100 mg, ab {preis:597}).</p>'
  + versand("GHK-Cu") + reko("GHK-Cu") + lager() +
  '<h3>GHK-Cu im Blend</h3><p>Zusammen mit BPC-157 und TB-500 steckt GHK-Cu auch im Peptid-Blend <a href="/produkt/glow/">GLOW</a> – was dahinter steckt, erklärt der Ratgeber <a href="/ratgeber/glow-peptid-blend/">GLOW-Peptid-Blend</a>. Herkunft und Forschungsstand im Überblick: <a href="/peptid-lexikon/ghk-cu/">GHK-Cu im Peptid-Lexikon</a>.</p>' + NOTE),
 faq=[["Was kostet GHK-Cu bei Peptrium?", "Das 50-mg-Vial kostet {preis:1011}, das 100-mg-Vial {preis:1012}. Ab 3 Vials gibt es Mengenrabatt (−10 %, ab 5 −15 %, ab 10 −20 %). Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Warum ist GHK-Cu blau?", "Die blaue Farbe kommt vom gebundenen Kupfer(II)-Ion. Sie ist typisch für GHK-Cu und kein Qualitätsmangel – reines GHK ohne Kupfer wäre farblos."],
      ["Ist Kupferpeptid dasselbe wie GHK-Cu?", "Ja. „Kupferpeptid“, „Copper Peptide“ und „Kupfer-Tripeptid-1“ sind gängige Bezeichnungen für GHK-Cu, den Komplex aus dem Tripeptid GHK und einem Kupfer-Ion."],
      ["Gibt es GHK-Cu auch ohne Anmischen?", "Ja, im Peptrium-Pen: GHK-Cu fertig gelöst in 50 oder 100 mg – ohne Rekonstitution."],
      FAQ_VERSAND("GHK-Cu"), FAQ_ZUL("GHK-Cu"), FAQ_ZAHL])

# ---------------- BPC-157 (428) ----------------
P[428] = dict(
 name="BPC-157",
 h2="BPC-157 kaufen – alles Wichtige auf einen Blick",
 seo=["BPC-157 kaufen – 10 mg, auf Lager, Versand aus DE",
      "BPC-157 (Body Protection Compound) kaufen: 10 mg, HPLC- und LC-MS-geprüft, ≥ 99 % Reinheit. Auf Lager – Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von BPC-157 wissen solltest: was das Peptid ist, wie es sich von TB-500 unterscheidet, wie es geliefert und gelagert wird – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist BPC-157?</h3><p>BPC-157 („<strong>Body Protection Compound</strong>“) ist ein synthetisches Peptid aus 15 Aminosäuren (Pentadecapeptid). Seine Sequenz ist von einem Abschnitt eines im Magensaft vorkommenden Proteins abgeleitet. Neben der klassischen Acetat-Form wird in der Literatur auch eine stabilere Arginat-Form diskutiert. BPC-157 ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>BPC-157 in der Forschung</h3><p>BPC-157 wird seit den 1990er-Jahren vor allem in präklinischen Modellen untersucht – unter anderem zu Gewebekommunikation, Angiogenese (Gefäßneubildung) und strukturellen Anpassungsprozessen. Die Datenlage stammt überwiegend aus Zell- und Tiermodellen.</p>'
  + achten("BPC-157", "10 mg") +
  '<h3>Größe & Preis</h3><p>BPC-157 ist <strong>wieder auf Lager</strong> und sofort versandfertig: das <strong>10-mg-Vial für {preis:428}</strong>. ' + RABATT + '</p>'
  '<h3>BPC-157 und TB-500</h3><p>In der Regenerationsforschung wird BPC-157 häufig gemeinsam mit <a href="/produkt/tb-500/">TB-500</a> betrachtet: BPC-157 ist ein kurzes, eigenständiges Peptid, TB-500 orientiert sich an der Aktin-bindenden Region von Thymosin Beta-4. Beide zusammen – plus GHK-Cu – gibt es auch im Blend <a href="/produkt/glow/">GLOW</a>.</p>'
  + versand("BPC-157") + reko("BPC-157") + lager() +
  '<p>Mehr zu Herkunft und Forschungsstand: <a href="/peptid-lexikon/bpc-157/">BPC-157 im Peptid-Lexikon</a>.</p>' + NOTE),
 faq=[["Was kostet BPC-157 bei Peptrium?", "Das 10-mg-Vial kostet {preis:428}. Ab 3 Vials gibt es Mengenrabatt (−10 %, ab 5 −15 %, ab 10 −20 %). Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Ist BPC-157 auf Lager?", "Ja, BPC-157 ist wieder regulär auf Lager und wird in der Regel innerhalb von 2 Werktagen nach Zahlungseingang geliefert."],
      ["Was ist der Unterschied zwischen BPC-157 und TB-500?", "BPC-157 ist ein Peptid aus 15 Aminosäuren, abgeleitet von einem Magensaft-Protein. TB-500 orientiert sich an der Aktin-bindenden Region von Thymosin Beta-4. In der Regenerationsforschung werden beide oft gemeinsam untersucht."],
      FAQ_VERSAND("BPC-157"), FAQ_ZUL("BPC-157"), FAQ_LAGER("BPC-157"), FAQ_ZAHL])

# ---------------- TB-500 (431, preorder, on sale) ----------------
P[431] = dict(
 name="TB-500",
 h2="TB-500 kaufen – alles Wichtige auf einen Blick",
 seo=["TB-500 kaufen – 10 mg vorbestellen, Versand aus DE",
      "TB-500 (Thymosin Beta-4-Fragment) kaufen: 10 mg, HPLC- und LC-MS-geprüft, ≥ 99 % Reinheit. Jetzt mit 15 % Vorbestell-Rabatt sichern."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von TB-500 wissen solltest: was das Peptid ist, wie es sich von BPC-157 unterscheidet, wie die Vorbestellung funktioniert – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist TB-500?</h3><p>TB-500 ist ein synthetisches Peptid, das sich an der aktiven Region des körpereigenen Proteins <strong>Thymosin Beta-4</strong> (Tβ4) orientiert – dem Abschnitt, der an Aktin bindet. Thymosin Beta-4 ist eines der häufigsten Aktin-bindenden Proteine in Zellen. Häufig liest man auch „TB500“ oder „TB 500“ – gemeint ist dasselbe Peptid. TB-500 ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>TB-500 in der Forschung</h3><p>Im Labor wird TB-500 genutzt, um Zellmigration, die Dynamik des Zytoskeletts und Aktin-Bindungsmechanismen zu untersuchen. In der Regenerationsforschung wird es häufig gemeinsam mit <a href="/produkt/bpc-157/">BPC-157</a> betrachtet.</p>'
  + achten("TB-500", "10 mg") +
  '<h3>Preis & Vorbestellung</h3><p>TB-500 ist derzeit <strong>vorbestellbar</strong>: Das <strong>10-mg-Vial kostet {preis:577}</strong> – 15 % weniger als der reguläre Preis. Du bestellst und bezahlst jetzt, wir versenden, sobald die neue Charge eingetroffen und geprüft ist. Den Stand deiner Bestellung siehst du jederzeit unter <a href="/bestellstatus/">Bestellstatus</a>.</p>'
  + versand("TB-500") + reko("TB-500") + lager() +
  '<h3>TB-500 im Blend</h3><p>Zusammen mit BPC-157 und GHK-Cu steckt TB-500 auch im sofort lieferbaren Blend <a href="/produkt/glow/">GLOW</a>. Herkunft und Forschungsstand im Überblick: <a href="/peptid-lexikon/tb-500/">TB-500 im Peptid-Lexikon</a>.</p>' + NOTE),
 faq=[["Was kostet TB-500 bei Peptrium?", "Das 10-mg-Vial kostet als Vorbestellung {preis:577} – 15 % unter dem regulären Preis. Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Wann wird vorbestelltes TB-500 geliefert?", "Sobald die neue Charge eingetroffen und per HPLC/LC-MS geprüft ist, versenden wir alle Vorbestellungen in der Reihenfolge des Eingangs. Wenn du andere, lieferbare Produkte mitbestellst, kommt alles zusammen."],
      ["Ist TB500 dasselbe wie TB-500?", "Ja. „TB500“, „TB 500“ und „TB-500“ sind Schreibweisen für dasselbe Peptid, das sich an der Aktin-bindenden Region von Thymosin Beta-4 orientiert."],
      ["Was ist der Unterschied zwischen TB-500 und BPC-157?", "TB-500 orientiert sich an Thymosin Beta-4, BPC-157 ist ein Peptid aus 15 Aminosäuren, abgeleitet von einem Magensaft-Protein. In der Regenerationsforschung werden beide oft gemeinsam untersucht."],
      FAQ_ZUL("TB-500"), FAQ_LAGER("TB-500"), FAQ_ZAHL])

# ---------------- Mots-C (71) ----------------
P[71] = dict(
 name="MOTS-c",
 h2="MOTS-c kaufen – alles Wichtige auf einen Blick",
 seo=["MOTS-c kaufen – 10 mg, Versand aus DE",
      "MOTS-c (mitochondriales Peptid) kaufen: 10 mg, HPLC- und LC-MS-geprüft, ≥ 99 % Reinheit. Versand aus Deutschland in 2 Werktagen, auch als Pen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von MOTS-c wissen solltest: was das mitochondriale Peptid ist, welche Formen es gibt, wie es geliefert und gelagert wird – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist MOTS-c?</h3><p>MOTS-c (auch Mots-C, „Mitochondrial Open Reading Frame of the 12S rRNA type-c“) ist ein Peptid aus 16 Aminosäuren, das nicht im Zellkern, sondern im <strong>Erbgut der Mitochondrien</strong> kodiert ist – genauer im 12S-rRNA-Gen. Es wurde 2015 erstmals beschrieben und zählt zur Gruppe der mitochondrial abgeleiteten Peptide (MDPs). MOTS-c ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>MOTS-c in der Forschung</h3><p>Forschungsarbeiten an Zell- und Tiermodellen beschäftigen sich mit der Rolle von MOTS-c in der Kommunikation zwischen Mitochondrien und Zellkern, im zellulären Energiestoffwechsel und im AMPK-Signalweg. Die Datenlage stammt bislang überwiegend aus präklinischen Studien.</p>'
  + achten("MOTS-c", "10 mg") +
  '<h3>Vial oder Pen?</h3><p>MOTS-c gibt es bei uns als <strong>10-mg-Vial für {preis:71}</strong>. ' + RABATT + ' Wer ohne Anmischen arbeiten möchte, bekommt MOTS-c fertig gelöst im <a href="/produkt/peptrium-pen-mots-c/">Peptrium-Pen</a> (20 oder 40 mg, ab {preis:544}).</p>'
  + versand("MOTS-c") + reko("MOTS-c") + lager() +
  '<p>Mehr zu Herkunft und Forschungsstand: <a href="/peptid-lexikon/mots-c/">MOTS-c im Peptid-Lexikon</a>.</p>' + NOTE),
 faq=[["Was kostet MOTS-c bei Peptrium?", "Das 10-mg-Vial kostet {preis:71}. Ab 3 Vials gibt es Mengenrabatt (−10 %, ab 5 −15 %, ab 10 −20 %). Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Was bedeutet MOTS-c?", "MOTS-c steht für „Mitochondrial Open Reading Frame of the 12S rRNA type-c“ – ein Peptid, das im Erbgut der Mitochondrien kodiert ist."],
      ["Gibt es MOTS-c auch ohne Anmischen?", "Ja, im Peptrium-Pen: MOTS-c fertig gelöst in 20 oder 40 mg – ohne Rekonstitution."],
      FAQ_VERSAND("MOTS-c"), FAQ_ZUL("MOTS-c"), FAQ_LAGER("MOTS-c"), FAQ_ZAHL])

# ---------------- Cagrilintid (1051) ----------------
P[1051] = dict(
 name="Cagrilintid",
 h2="Cagrilintid kaufen – alles Wichtige auf einen Blick",
 seo=["Cagrilintid (Cagrilintide) kaufen – 10 mg, Versand aus DE",
      "Cagrilintid (Cagrilintide, Amylin-Analogon) kaufen: 10 mg, HPLC- und LC-MS-geprüft, ≥ 99 % Reinheit. Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von Cagrilintid wissen solltest: was das Amylin-Analogon ist, wie es sich von Retatrutide unterscheidet, wie es geliefert und gelagert wird – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist Cagrilintid?</h3><p>Cagrilintid (englisch <strong>Cagrilintide</strong>, Forschungsbezeichnung NN9838) ist ein synthetisches, lang wirksames Analogon des körpereigenen Peptidhormons <strong>Amylin</strong>. Durch eine Fettsäure-Seitenkette bindet es an Albumin – dadurch bleibt es in Studien deutlich länger stabil als natürliches Amylin. Cagrilintid befindet sich in der klinischen Erprobung und ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>Cagrilintid in der Forschung</h3><p>Cagrilintid wird als Agonist an Amylin- und Calcitonin-Rezeptoren untersucht. Im Mittelpunkt stehen Studien zu Sättigungssignalen, Energiehaushalt und Stoffwechselregulation. Weil es einen anderen Signalweg anspricht als GLP-1-Analoga, wird es häufig im Vergleich oder in Kombination mit Inkretin-Peptiden betrachtet – in klinischen Studien etwa zusammen mit Semaglutid.</p>'
  + achten("Cagrilintid", "10 mg") +
  '<h3>Größe & Preis</h3><p>Cagrilintid gibt es bei uns als <strong>10-mg-Vial für {preis:1051}</strong>. ' + RABATT + '</p>'
  '<h3>Cagrilintid und Retatrutide</h3><p>Retatrutide spricht die Rezeptoren für GIP, GLP-1 und Glukagon an, Cagrilintid den Amylin-Signalweg – zwei verschiedene Ansätze der Stoffwechselforschung. Die Unterschiede erklärt der Ratgeber <a href="/ratgeber/cagrilintid-retatrutide-unterschied/">Cagrilintid und Retatrutide</a>; <a href="/produkt/retatrutide/">Retatrutide</a> selbst findest du ebenfalls im Shop.</p>'
  + versand("Cagrilintid") + reko("Cagrilintid") + lager() + NOTE),
 faq=[["Was kostet Cagrilintid bei Peptrium?", "Das 10-mg-Vial kostet {preis:1051}. Ab 3 Vials gibt es Mengenrabatt (−10 %, ab 5 −15 %, ab 10 −20 %). Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Ist Cagrilintide dasselbe wie Cagrilintid?", "Ja. „Cagrilintide“ ist die englische Schreibweise, „Cagrilintid“ die deutsche – gemeint ist dasselbe Amylin-Analogon (NN9838)."],
      ["Was ist der Unterschied zwischen Cagrilintid und Retatrutide?", "Cagrilintid ist ein Amylin-Analogon und spricht Amylin- und Calcitonin-Rezeptoren an. Retatrutide ist ein Triple-Agonist an GIP-, GLP-1- und Glukagon-Rezeptoren. Ausführlich erklärt im Ratgeber „Cagrilintid und Retatrutide“."],
      FAQ_VERSAND("Cagrilintid"), FAQ_ZUL("Cagrilintid", " Es befindet sich in der klinischen Erprobung."), FAQ_LAGER("Cagrilintid"), FAQ_ZAHL])

# ---------------- Melanotan II (1053) ----------------
P[1053] = dict(
 name="Melanotan II",
 h2="Melanotan II (MT-2) kaufen – alles Wichtige auf einen Blick",
 seo=["Melanotan 2 (MT-2) kaufen – 10 mg, Versand aus DE",
      "Melanotan II (MT-2) kaufen: 10 mg im Vial, HPLC- und LC-MS-geprüft, ≥ 99 % Reinheit. Versand aus Deutschland in 2 Werktagen – nur für Laborforschung."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von Melanotan II wissen solltest: was das Peptid ist, wie es geliefert und gelagert wird – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist Melanotan II?</h3><p>Melanotan II (kurz <strong>MT-2</strong>, MT2 oder Melanotan 2) ist ein synthetisches, zyklisches Analogon des α-Melanozyten-stimulierenden Hormons (α-MSH). Es wurde in den 1980er-Jahren an der University of Arizona entwickelt und ist bis heute eine gefragte Referenzsubstanz in der Melanocortin-Forschung. Melanotan II ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>Melanotan II in der Forschung</h3><p>MT-2 wird als nicht-selektiver Agonist an den Melanocortin-Rezeptoren MC1R, MC3R, MC4R und MC5R untersucht. Im Mittelpunkt stehen Studien zur Melanogenese und Pigmentierung sowie zu den vielfältigen Funktionen des Melanocortin-Systems. Vom verwandten Melanotan I (Afamelanotid) unterscheidet es sich durch die zyklische Struktur und die breitere Rezeptorbindung.</p>'
  + achten("Melanotan II", "10 mg") +
  '<h3>Größe & Preis</h3><p>Melanotan II gibt es bei uns als <strong>10-mg-Vial für {preis:1053}</strong>. ' + RABATT + '</p>'
  + versand("Melanotan II") + reko("Melanotan II") + lager() + NOTE),
 faq=[["Was kostet Melanotan II bei Peptrium?", "Das 10-mg-Vial kostet {preis:1053}. Ab 3 Vials gibt es Mengenrabatt (−10 %, ab 5 −15 %, ab 10 −20 %). Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Ist MT-2 dasselbe wie Melanotan 2?", "Ja. MT-2, MT2, Melanotan 2 und Melanotan II bezeichnen dasselbe zyklische α-MSH-Analogon."],
      ["Was ist der Unterschied zwischen Melanotan I und II?", "Melanotan I (Afamelanotid) ist linear aufgebaut und bindet vor allem an MC1R. Melanotan II ist zyklisch und bindet an mehrere Melanocortin-Rezeptoren (MC1R, MC3R, MC4R, MC5R)."],
      FAQ_VERSAND("Melanotan II"), FAQ_ZUL("Melanotan II"), FAQ_LAGER("Melanotan II"), FAQ_ZAHL])

# ---------------- GLOW (1055) ----------------
P[1055] = dict(
 name="GLOW",
 h2="GLOW Peptid-Blend kaufen – alles Wichtige auf einen Blick",
 seo=["GLOW Peptid kaufen – GHK-Cu, BPC-157 & TB-500 (70 mg)",
      "GLOW Peptid-Blend kaufen: GHK-Cu 50 mg + BPC-157 10 mg + TB-500 10 mg in einem Vial, HPLC- und LC-MS-geprüft. Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von GLOW wissen solltest: was in dem Blend steckt, wie er geliefert und gelagert wird – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist GLOW?</h3><p>GLOW ist ein <strong>Peptid-Blend</strong> aus drei bekannten Forschungspeptiden in einem Vial: <strong>GHK-Cu (50 mg)</strong>, <strong>BPC-157 (10 mg)</strong> und <strong>TB-500 (10 mg)</strong> – zusammen 70 mg. Statt drei einzelne Vials anzusetzen, lässt sich die Kombination so in einem einzigen Ansatz untersuchen. Keiner der Bestandteile ist als Arzneimittel zugelassen.</p>'
  '<h3>Die drei Bestandteile in der Forschung</h3><p><a href="/produkt/ghk-cu/">GHK-Cu</a> wird als Kupfer-Peptidkomplex in Haut-, Kollagen- und Bindegewebsmodellen untersucht, <a href="/produkt/bpc-157/">BPC-157</a> und <a href="/produkt/tb-500/">TB-500</a> (orientiert an Thymosin Beta-4) in Regenerations- und Wundheilungsmodellen. Wie der Blend zusammengesetzt ist und was ihn von den Einzelpeptiden unterscheidet, erklärt der Ratgeber <a href="/ratgeber/glow-peptid-blend/">GLOW-Peptid-Blend</a>.</p>'
  + achten("GLOW", "70 mg gesamt") +
  '<h3>Größe & Preis</h3><p>GLOW gibt es bei uns als <strong>70-mg-Vial für {preis:1055}</strong>. ' + RABATT + '</p>'
  + versand("GLOW") +
  '<h3>Lieferform & Rekonstitution</h3><p>GLOW wird als lyophilisiertes Pulver im versiegelten Vial geliefert und – wie die Einzelpeptide – mit <a href="/produkt/bac-water/">Bac Water</a> rekonstituiert: Wasser langsam an der Glaswand entlang zugeben, sanft schwenken, nicht schütteln. Die Konzentration der einzelnen Bestandteile berechnest du mit unserem <a href="/peptid-rechner/">Peptid-Rechner</a>. Die leicht bläuliche Farbe kommt vom Kupfer im GHK-Cu.</p>'
  + lager() + NOTE),
 faq=[["Was kostet GLOW bei Peptrium?", "Das 70-mg-Vial kostet {preis:1055}. Ab 3 Vials gibt es Mengenrabatt (−10 %, ab 5 −15 %, ab 10 −20 %). Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Was ist im GLOW-Blend enthalten?", "GHK-Cu (50 mg), BPC-157 (10 mg) und TB-500 (10 mg) – zusammen 70 mg in einem Vial."],
      ["Warum ist GLOW bläulich?", "Die Farbe kommt vom Kupfer(II)-Ion im GHK-Cu. Sie ist typisch für Kupferpeptide und kein Qualitätsmangel."],
      FAQ_VERSAND("GLOW"), FAQ_ZUL("GLOW", " Das gilt für alle drei Bestandteile."), FAQ_LAGER("GLOW"), FAQ_ZAHL])
