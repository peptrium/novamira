# SEO-Texte v2 für die restlichen Produkte (gleiches Muster wie drafts/seo-produkte-2026-10-27/content.py).
# Platzhalter {preis:ID}, {versandfrei}. Nur Forschungsrahmen: keine Wirkversprechen, keine Dosierung, keine Anwendung am Menschen.
# Semax, Selank, KPV, Tesamorelin, IGF-1 LR3 sind VORBESTELLUNGEN (sp_preorder_product_ids) – Texte anpassen, sobald sie wieder lieferbar sind.
import os, sys, re, json, html
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'seo-produkte-2026-10-27'))
from content import NOTE, versand, reko, lager, achten, FAQ_VERSAND, FAQ_ZAHL, FAQ_LAGER, FAQ_ZUL

def vorbestellung(name, pid, menge):
    return (f'<h3>Preis & Vorbestellung</h3><p>{name} ist derzeit <strong>vorbestellbar</strong>: Das <strong>{menge}-Vial kostet {{preis:{pid}}}</strong> – 15 % weniger als der reguläre Preis. '
            'Du bestellst und bezahlst jetzt, wir versenden, sobald die neue Charge eingetroffen und geprüft ist. '
            'Den Stand deiner Bestellung siehst du jederzeit unter <a href="/bestellstatus/">Bestellstatus</a>.</p>')

FAQ_VORB = lambda name: [f"Wann wird vorbestelltes {name} geliefert?", "Sobald die neue Charge eingetroffen und per HPLC/LC-MS geprüft ist, versenden wir alle Vorbestellungen in der Reihenfolge des Eingangs. Wenn du lieferbare Produkte mitbestellst, kommt alles zusammen in einem Paket."]
FAQ_PREIS = lambda name, pid, menge: [f"Was kostet {name} bei Peptrium?", f"Das {menge}-Vial kostet als Vorbestellung {{preis:{pid}}} – 15 % unter dem regulären Preis. Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {{versandfrei}} gratis."]

P = {}

# ---------------- Semax (434) ----------------
P[434] = dict(
 name="Semax", slug="semax",
 h2="Semax kaufen – alles Wichtige auf einen Blick",
 seo=["Semax kaufen – 10 mg vorbestellen, Versand aus DE",
      "Semax kaufen: Heptapeptid (ACTH-Fragment) als 10-mg-Vial, HPLC- und LC-MS-geprüft. Jetzt mit 15 % Vorbestell-Rabatt, Versand aus Deutschland."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von Semax wissen solltest: was das Peptid ist, wie es mit Selank zusammenhängt, wie die Vorbestellung funktioniert – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist Semax?</h3><p>Semax ist ein synthetisches <strong>Heptapeptid</strong> aus sieben Aminosäuren (Met-Glu-His-Phe-Pro-Gly-Pro). Es leitet sich von einem kurzen Fragment des adrenocorticotropen Hormons ab (ACTH 4–7), ergänzt um die stabilisierende Sequenz Pro-Gly-Pro. Entwickelt wurde Semax in Russland; in der EU ist es nicht als Arzneimittel zugelassen.</p>'
  '<h3>Semax in der Forschung</h3><p>In Zell- und Tiermodellen wird Semax vor allem im Zusammenhang mit <strong>neurotrophen Signalwegen</strong> untersucht – etwa der Expression von BDNF (Brain-Derived Neurotrophic Factor) – sowie bei neurobiologischen Kommunikationsprozessen. Häufig wird es gemeinsam mit <a href="/produkt/selank/">Selank</a> betrachtet, das eine ähnliche Pro-Gly-Pro-Endung trägt.</p>'
  + achten("Semax", "10 mg") + vorbestellung("Semax", 434, "10-mg") + versand("Semax") + reko("Semax") + lager() +
  '<h3>Mehr zu Semax</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/semax/">Peptid-Lexikon: Semax</a> zusammen.</p>' + NOTE),
 faq=[FAQ_PREIS("Semax", 434, "10-mg"),
      ["Gibt es Semax als Nasenspray?", "Bei uns gibt es Semax als lyophilisiertes Pulver im versiegelten Vial (10 mg). Für die Arbeit im Labor wird es mit Bac Water rekonstituiert."],
      ["Was ist der Unterschied zwischen Semax und Selank?", "Beide sind Heptapeptide mit derselben stabilisierenden Pro-Gly-Pro-Endung. Semax leitet sich von einem ACTH-Fragment ab, Selank vom körpereigenen Tetrapeptid Tuftsin."],
      FAQ_VORB("Semax"), FAQ_ZUL("Semax"), FAQ_LAGER("Semax"), FAQ_ZAHL])

# ---------------- Selank (437) ----------------
P[437] = dict(
 name="Selank", slug="selank",
 h2="Selank kaufen – alles Wichtige auf einen Blick",
 seo=["Selank kaufen – 10 mg vorbestellen, Versand aus DE",
      "Selank kaufen: Tuftsin-Analogon als 10-mg-Vial, HPLC- und LC-MS-geprüft. Jetzt mit 15 % Vorbestell-Rabatt, Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von Selank wissen solltest: was das Peptid ist, wie es mit Semax zusammenhängt, wie die Vorbestellung funktioniert – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist Selank?</h3><p>Selank ist ein synthetisches <strong>Heptapeptid</strong> (Thr-Lys-Pro-Arg-Pro-Gly-Pro) und ein Analogon des körpereigenen Tetrapeptids <strong>Tuftsin</strong>. Die angehängte Sequenz Pro-Gly-Pro macht es stabiler gegenüber dem Abbau durch Peptidasen. Entwickelt wurde Selank am Institut für Molekulargenetik in Moskau; in der EU ist es nicht als Arzneimittel zugelassen.</p>'
  '<h3>Selank in der Forschung</h3><p>Forschungsarbeiten – überwiegend an Tiermodellen und Zellkulturen – befassen sich mit der Rolle von Selank bei <strong>GABAergen Signalwegen</strong>, der Immunmodulation und neurobiologischen Kommunikationsprozessen. Oft wird es zusammen mit <a href="/produkt/semax/">Semax</a> untersucht.</p>'
  + achten("Selank", "10 mg") + vorbestellung("Selank", 437, "10-mg") + versand("Selank") + reko("Selank") + lager() +
  '<h3>Mehr zu Selank</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/selank/">Peptid-Lexikon: Selank</a> zusammen.</p>' + NOTE),
 faq=[FAQ_PREIS("Selank", 437, "10-mg"),
      ["Was ist Tuftsin?", "Tuftsin ist ein körpereigenes Tetrapeptid (Thr-Lys-Pro-Arg), das aus Immunglobulin G freigesetzt wird. Selank ist ein stabilisiertes Analogon davon."],
      ["Kann ich Selank zusammen mit Semax bestellen?", "Ja. Beide Peptide sind derzeit vorbestellbar und kommen nach dem Eintreffen der Charge zusammen in einem Paket."],
      FAQ_VORB("Selank"), FAQ_ZUL("Selank"), FAQ_LAGER("Selank"), FAQ_ZAHL])

# ---------------- KPV (515) ----------------
P[515] = dict(
 name="KPV", slug="kpv",
 h2="KPV kaufen – alles Wichtige auf einen Blick",
 seo=["KPV kaufen – 10 mg vorbestellen, Versand aus DE",
      "KPV kaufen: Tripeptid aus α-MSH (Lys-Pro-Val) als 10-mg-Vial, HPLC- und LC-MS-geprüft. Jetzt mit 15 % Vorbestell-Rabatt, Versand aus Deutschland."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von KPV wissen solltest: was das Tripeptid ist, wo es herkommt, wie die Vorbestellung funktioniert – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist KPV?</h3><p>KPV ist ein <strong>Tripeptid</strong> aus den Aminosäuren Lysin, Prolin und Valin (Lys-Pro-Val). Es entspricht dem C-terminalen Ende des körpereigenen Hormons <strong>α-MSH</strong> (Alpha-Melanozyten-stimulierendes Hormon, Position 11–13). Mit nur drei Aminosäuren gehört KPV zu den kleinsten Peptiden im Sortiment. Es ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>KPV in der Forschung</h3><p>In Zellkultur- und Tiermodellen wird KPV im Zusammenhang mit <strong>Entzündungssignalwegen</strong> untersucht, unter anderem an Darmepithelzellen und in Verbindung mit dem Peptidtransporter PepT1. Auch <a href="/produkt/melanotan-2/">Melanotan II</a> leitet sich von α-MSH ab – allerdings von einem anderen Abschnitt.</p>'
  + achten("KPV", "10 mg") + vorbestellung("KPV", 515, "10-mg") + versand("KPV") + reko("KPV") + lager() +
  '<h3>Mehr zu KPV</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/kpv/">Peptid-Lexikon: KPV</a> zusammen. Weitere Peptide aus der Regenerationsforschung sind <a href="/produkt/bpc-157/">BPC-157</a> und <a href="/produkt/tb-500/">TB-500</a>.</p>' + NOTE),
 faq=[FAQ_PREIS("KPV", 515, "10-mg"),
      ["Wofür steht KPV?", "Für die Ein-Buchstaben-Codes der drei Aminosäuren: K = Lysin, P = Prolin, V = Valin."],
      ["Ist KPV dasselbe wie α-MSH?", "Nein. KPV ist nur das kurze C-terminale Ende (Position 11–13) von α-MSH, das aus 13 Aminosäuren besteht."],
      FAQ_VORB("KPV"), FAQ_ZUL("KPV"), FAQ_LAGER("KPV"), FAQ_ZAHL])

# ---------------- Tesamorelin (512) ----------------
P[512] = dict(
 name="Tesamorelin", slug="tesamorelin",
 h2="Tesamorelin kaufen – alles Wichtige auf einen Blick",
 seo=["Tesamorelin kaufen – 10 mg vorbestellen, Versand aus DE",
      "Tesamorelin kaufen: GHRH-Analogon als 10-mg-Vial, HPLC- und LC-MS-geprüft. Jetzt mit 15 % Vorbestell-Rabatt, Versand aus Deutschland in 2 Werktagen."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von Tesamorelin wissen solltest: was das Peptid ist, wie es sich von CJC-1295 unterscheidet, wie die Vorbestellung funktioniert – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist Tesamorelin?</h3><p>Tesamorelin ist ein synthetisches Analogon des <strong>Wachstumshormon-Releasing-Hormons (GHRH)</strong> aus 44 Aminosäuren, das am N-Terminus chemisch stabilisiert ist. In den USA ist es für eine eng begrenzte Indikation als Arzneimittel zugelassen, in der EU nicht. Bei Peptrium ist es ausschließlich als Forschungssubstanz erhältlich.</p>'
  '<h3>Tesamorelin in der Forschung</h3><p>Tesamorelin dient als Werkzeug, um die Aktivierung des GHRH-Rezeptors und die Regulation der Wachstumshormon-Achse zu untersuchen. Im Unterschied zu <a href="/produkt/cjc-1295/">CJC-1295</a> (einem verkürzten GHRH-Analogon aus 29 Aminosäuren) bildet Tesamorelin die vollständige GHRH-Sequenz ab.</p>'
  + achten("Tesamorelin", "10 mg") + vorbestellung("Tesamorelin", 512, "10-mg") + versand("Tesamorelin") + reko("Tesamorelin") + lager() +
  '<h3>Mehr zu Tesamorelin</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/tesamorelin/">Peptid-Lexikon: Tesamorelin</a> zusammen.</p>' + NOTE),
 faq=[FAQ_PREIS("Tesamorelin", 512, "10-mg"),
      ["Was ist der Unterschied zwischen Tesamorelin und CJC-1295?", "Beide sind GHRH-Analoga. Tesamorelin besteht aus allen 44 Aminosäuren des GHRH und ist am N-Terminus stabilisiert, CJC-1295 (ohne DAC) ist eine verkürzte Variante aus 29 Aminosäuren."],
      ["Ist Tesamorelin in Deutschland zugelassen?", "Nein. In der EU ist Tesamorelin nicht als Arzneimittel zugelassen. Wir verkaufen es ausschließlich als Forschungspeptid für Laborzwecke – nicht zur Anwendung am Menschen oder Tier."],
      FAQ_VORB("Tesamorelin"), FAQ_LAGER("Tesamorelin"), FAQ_VERSAND("Tesamorelin"), FAQ_ZAHL])

# ---------------- IGF-1 LR3 (521) ----------------
P[521] = dict(
 name="IGF-1 LR3", slug="igf-1lr3",
 h2="IGF-1 LR3 kaufen – alles Wichtige auf einen Blick",
 seo=["IGF-1 LR3 kaufen – 1 mg vorbestellen, Versand aus DE",
      "IGF-1 LR3 kaufen: IGF-1-Analogon aus 83 Aminosäuren als 1-mg-Vial, HPLC- und LC-MS-geprüft. Jetzt mit 15 % Vorbestell-Rabatt, Versand aus Deutschland."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von IGF-1 LR3 wissen solltest: was das Peptid ist, worin es sich von IGF-1 unterscheidet, wie die Vorbestellung funktioniert – und woran du gute Qualität erkennst.</p>'
  '<h3>Was ist IGF-1 LR3?</h3><p>IGF-1 LR3 (Long R3 IGF-1) ist ein synthetisches Analogon des <strong>insulinähnlichen Wachstumsfaktors 1 (IGF-1)</strong> aus 83 Aminosäuren. Zwei Änderungen unterscheiden es vom natürlichen IGF-1: An Position 3 steht Arginin statt Glutaminsäure (das „R3“), und am N-Terminus ist es um 13 Aminosäuren verlängert (das „Long“). Dadurch bindet es deutlich schwächer an IGF-Bindungsproteine und bleibt in Versuchsansätzen länger aktiv. IGF-1 LR3 ist nicht als Arzneimittel zugelassen.</p>'
  '<h3>IGF-1 LR3 in der Forschung</h3><p>IGF-1 LR3 wird vor allem in der <strong>Zellkultur</strong> eingesetzt – etwa als Zusatz in Kulturmedien – und zur Untersuchung von IGF-1-Rezeptor-Signalwegen, Zellwachstum und Zellteilung.</p>'
  + achten("IGF-1 LR3", "1 mg") + vorbestellung("IGF-1 LR3", 521, "1-mg") + versand("IGF-1 LR3") + reko("IGF-1 LR3") + lager() +
  '<h3>Mehr zu IGF-1 LR3</h3><p>Herkunft, Aufbau und Forschungsstand fasst das <a href="/peptid-lexikon/igf-1lr3/">Peptid-Lexikon: IGF-1 LR3</a> zusammen.</p>' + NOTE),
 faq=[FAQ_PREIS("IGF-1 LR3", 521, "1-mg"),
      ["Was bedeutet „LR3“?", "„Long“ steht für die Verlängerung um 13 Aminosäuren am N-Terminus, „R3“ für Arginin an Position 3 statt Glutaminsäure wie im natürlichen IGF-1."],
      ["Warum ist das Vial nur 1 mg?", "IGF-1 LR3 wird in der Forschung in sehr kleinen Mengen eingesetzt, etwa als Zusatz in Zellkulturmedien. 1 mg ist deshalb die übliche Vial-Größe."],
      FAQ_VORB("IGF-1 LR3"), FAQ_ZUL("IGF-1 LR3"), FAQ_LAGER("IGF-1 LR3"), FAQ_ZAHL])

# ---------------- Bac Water (74) ----------------
P[74] = dict(
 name="Bac Water", slug="bac-water",
 h2="Bac Water kaufen – alles Wichtige auf einen Blick",
 seo=["Bac Water kaufen – bakteriostatisches Wasser 3 ml",
      "Bac Water (bakteriostatisches Wasser) kaufen: steril, mit 0,9 % Benzylalkohol, 3 ml im Vial. Zum Rekonstituieren von Peptiden – Versand aus Deutschland."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf von Bac Water wissen solltest: was drin ist, wofür es im Labor gebraucht wird, wie viel du brauchst – und wie du es lagerst.</p>'
  '<h3>Was ist Bac Water?</h3><p>Bac Water – kurz für <strong>bakteriostatisches Wasser</strong> – ist steriles Wasser mit <strong>0,9 % Benzylalkohol</strong> als Konservierungsmittel. Der Benzylalkohol hemmt das Wachstum von Bakterien. Deshalb kann ein angestochenes Vial über mehrere Entnahmen hinweg verwendet werden – anders als steriles Wasser ohne Zusatz, das nur für den einmaligen Gebrauch gedacht ist.</p>'
  '<h3>Wofür wird Bac Water verwendet?</h3><p>Im Labor ist Bac Water das Standard-Lösungsmittel, um <strong>lyophilisierte Peptide zu rekonstituieren</strong> – also das gefriergetrocknete Pulver wieder in Lösung zu bringen. Das Wasser wird langsam an der Glaswand des Peptid-Vials entlang zugegeben, danach wird das Vial sanft geschwenkt, nicht geschüttelt. Welche Wassermenge welche Konzentration ergibt, berechnest du mit dem <a href="/peptid-rechner/">Peptid-Rechner</a>. Alles Weitere erklärt der Ratgeber <a href="/ratgeber/bac-water-bakteriostatisches-wasser/">Bac Water – bakteriostatisches Wasser</a>.</p>'
  '<h3>Bac Water kaufen – worauf du achten solltest</h3><ul>'
  '<li><strong>Richtiger Zusatz:</strong> 0,9 % Benzylalkohol – das unterscheidet Bac Water von reinem sterilem Wasser.</li>'
  '<li><strong>Sterile Abfüllung:</strong> versiegeltes Vial mit Gummistopfen, damit du sauber entnehmen kannst.</li>'
  '<li><strong>Passende Menge:</strong> ein 3-ml-Vial reicht in der Regel für mehrere Peptid-Vials.</li></ul>'
  '<h3>Größe & Preis</h3><p>Bac Water gibt es bei uns im <strong>3-ml-Vial für {preis:74}</strong>. Zum Abmessen und Aufziehen eignen sich unsere <a href="/produkt/insulinspritze-10er-pack/">Insulinspritzen</a>. Wer gar nicht anmischen möchte, findet Retatrutide, GHK-Cu und MOTS-c auch fertig angesetzt in den <a href="/alle-peptrium-pens/">Peptrium-Pens</a>.</p>'
  + versand("Bac Water") +
  '<h3>Lagerung</h3><p>Ungeöffnet trocken, lichtgeschützt und bei Raumtemperatur oder kühl lagern. Nach dem ersten Anstechen kühl aufbewahren und in der Regel innerhalb von 28 Tagen verwenden.</p>' + NOTE),
 faq=[["Was kostet Bac Water bei Peptrium?", "Das 3-ml-Vial kostet {preis:74}. Der Versand innerhalb Deutschlands kostet 4,90 € und ist ab {versandfrei} gratis."],
      ["Was ist der Unterschied zwischen Bac Water und sterilem Wasser?", "Bac Water enthält 0,9 % Benzylalkohol, der das Wachstum von Bakterien hemmt. Dadurch kann ein Vial mehrfach angestochen werden. Steriles Wasser ohne Zusatz ist für die einmalige Verwendung gedacht."],
      ["Wie viel Bac Water brauche ich?", "Das hängt davon ab, welche Konzentration du ansetzen möchtest. Ein 3-ml-Vial reicht in der Regel für mehrere Peptid-Vials. Die passende Menge berechnest du mit dem Peptid-Rechner."],
      ["Wie lange ist Bac Water nach dem Öffnen haltbar?", "Angebrochen in der Regel bis zu 28 Tage – kühl gelagert und bei sauberer Entnahme."],
      FAQ_VERSAND("Bac Water"), FAQ_ZAHL])

# ---------------- Insulinspritze (80) ----------------
P[80] = dict(
 name="Insulinspritze 10er Pack", slug="insulinspritze-10er-pack",
 h2="Insulinspritzen kaufen – alles Wichtige auf einen Blick",
 seo=["Insulinspritzen kaufen – 1 ml, 30G, 10er Pack",
      "Insulinspritzen kaufen: 1 ml U-100, 30G × 8 mm, steril und einzeln verpackt, 10er Pack. Zum Abmessen rekonstituierter Peptide – Versand aus Deutschland."],
 body=('<p class="sp-ab-lead">Hier findest du alles, was du vor dem Kauf unserer Insulinspritzen wissen solltest: was sie können, wofür sie im Labor gebraucht werden und wie du die Skala liest.</p>'
  '<h3>Die Insulinspritze 1 ml, 30G × 8 mm</h3><p>Unsere Insulinspritzen sind <strong>sterile Einwegspritzen mit 1 ml Volumen</strong> und fest verbundener, feiner 30G-Kanüle (8 mm). Jede Spritze ist einzeln steril verpackt und CE-zertifiziert, ein Pack enthält 10 Stück. Die feine Skalierung erlaubt ein genaues Abmessen kleiner Volumina – wichtig für reproduzierbare Ergebnisse im Labor.</p>'
  '<h3>Wofür im Labor?</h3><p>Die Spritzen werden zum <strong>Aufziehen und Abmessen rekonstituierter Peptidlösungen</strong> verwendet, zum Beispiel nach dem Ansetzen mit <a href="/produkt/bac-water/">Bac Water</a>. Außerdem eignen sie sich, um das Bac Water selbst genau abzumessen. Jede Spritze ist nur für den einmaligen Gebrauch bestimmt und danach sicher zu entsorgen.</p>'
  '<h3>Die Skala: U-100</h3><p>Die Spritzen haben eine <strong>U-100-Skala</strong>: 100 Einheiten entsprechen 1 ml, ein Teilstrich also 0,01 ml. Welche Menge Lösung welcher Peptidmenge entspricht, rechnet dir der <a href="/peptid-rechner/">Peptid-Rechner</a> aus – inklusive einer Abbildung der Spritze mit dem passenden Teilstrich.</p>'
  '<h3>Größe & Preis</h3><p>Der <strong>10er Pack kostet {preis:80}</strong>. Für unsere <a href="/alle-peptrium-pens/">Peptrium-Pens</a> brauchst du keine Spritzen, sondern <a href="/produkt/pen-nadeln-32g-4mm/">Pen-Nadeln</a>. <strong>Tipp:</strong> Ab 200 € Bestellwert legen wir dir ein Injektionskit mit Spritzen gratis dazu.</p>'
  + versand("Insulinspritze 10er Pack") +
  '<h3>Lagerung</h3><p>Trocken, bei Raumtemperatur und in der Originalverpackung lagern. Nur Spritzen aus unbeschädigter Sterilverpackung verwenden.</p>' + NOTE),
 faq=[["Was kostet der 10er Pack Insulinspritzen?", "Der Pack mit 10 Spritzen kostet {preis:80}. Ab 200 € Bestellwert gibt es ein Injektionskit mit Spritzen gratis dazu."],
      ["Was bedeutet U-100?", "Die Skala ist in Einheiten eingeteilt: 100 Einheiten entsprechen 1 ml, ein Teilstrich also 0,01 ml. Die Umrechnung in Peptidmengen übernimmt der Peptid-Rechner."],
      ["Sind die Spritzen steril?", "Ja. Jede Spritze ist einzeln steril verpackt und CE-zertifiziert. Sie ist nur für den einmaligen Gebrauch bestimmt."],
      ["Brauche ich für den Peptrium-Pen Spritzen?", "Nein. Für die Pens brauchst du Pen-Nadeln 32G × 4 mm. Spritzen brauchst du nur für Peptide im Vial."],
      FAQ_VERSAND("Insulinspritze 10er Pack"), FAQ_ZAHL])

PRICES = {434: '29,67 €', 437: '29,67 €', 515: '29,67 €', 512: '63,67 €', 521: '76,42 €', 74: '9,90 €', 80: '9,90 €'}

def fill(s):
    s = re.sub(r'\{preis:(\d+)\}', lambda m: PRICES[int(m.group(1))], s)
    return s.replace('{versandfrei}', '150,00 €')

if __name__ == '__main__':
    here = os.path.dirname(os.path.abspath(__file__))
    tpl = open(os.path.join(here, '..', 'seo-produkte-2026-10-27', 'v-ghk-cu.html'), encoding='utf-8').read()
    head = tpl[:tpl.index('<body>') + 6]
    for pid, d in P.items():
        faq = ''.join(f'<details class=q open><summary>{html.escape(fill(q))}</summary><p>{fill(a)}</p></details>' for q, a in d['faq'])
        page = (head + f'<div class=serp><div class=u>peptrium.com › produkt › {d["slug"]}</div><div class=t>{html.escape(d["seo"][0])} | Peptrium</div><div class=d>{html.escape(d["seo"][1])}</div></div>'
                f'<section id=sp-pp-about><div class=in><span class=ey>Produktinfo</span><h2>{d["h2"]}</h2><div>{fill(d["body"])}</div>'
                f'<div class=fq><h3>Häufige Fragen zum Kauf</h3>{faq}</div></div></section></body></html>')
        open(os.path.join(here, f'v-{d["slug"]}.html'), 'w', encoding='utf-8').write(page)
        print(pid, len(re.sub('<[^>]+>', ' ', fill(d['body'])).split()), 'Wörter', len(d['seo'][0]), len(d['seo'][1]))
    json.dump({str(k): {'h2': v['h2'], 'seo': v['seo'], 'body': v['body'], 'faq': v['faq']} for k, v in P.items()},
              open(os.path.join(here, 'rest.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
