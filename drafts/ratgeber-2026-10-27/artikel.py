# Ratgeber-Artikel 8–11 (Entwurf 2026-10-27, Nutzer: "ja schick entwürfe für die ratgeber artikel").
# Gleiches Format wie drafts/ratgeber-2026-10-26/artikel.py. Regeln: Forschungsrahmen, keine Dosierung für Menschen,
# keine Wirkversprechen, keine Zertifikats-Versprechen, keine Preise (ändern sich).
P = 'https://peptrium.com'


def a(path, text):
    return f'<a href="{P}{path}">{text}</a>'


HINWEIS = ('<p class="rg-note">Alle Produkte von Peptrium sind ausschließlich für die Laborforschung bestimmt – '
           'nicht für die Anwendung am Menschen oder Tier. Dieser Artikel dient der allgemeinen Information und ist keine Anwendungsanleitung.</p>')

ARTIKEL = [
{
 'slug': 'was-ist-glp-3',
 'title': 'Was ist GLP-3? Der Begriff einfach erklärt',
 'seo_title': 'Was ist GLP-3? Bedeutung & Zusammenhang mit Retatrutide',
 'meta': '„GLP-3“ ist kein offizielles Hormon, sondern ein Szenebegriff für Retatrutide. Was dahintersteckt und wie es sich von GLP-1 unterscheidet – einfach erklärt.',
 'lead': 'Wer sich mit Stoffwechsel-Peptiden beschäftigt, stößt früher oder später auf den Begriff „GLP-3“. Klingt nach einem neuen Hormon – ist es aber nicht. Hier erfährst du, was hinter dem Namen steckt und warum er fast immer Retatrutide meint.',
 'body': f'''
<h2>Kurz gesagt</h2>
<div class="rg-box">„GLP-3“ ist <strong>kein offizieller Wirkstoff und kein körpereigenes Hormon</strong>. Der Begriff ist in der Research-Community entstanden und wird fast immer für <strong>Retatrutide</strong> (LY3437943) verwendet – einen Triple-Agonisten an den Rezeptoren für GLP-1, GIP und Glukagon.</div>

<h2>Woher kommt der Name?</h2>
<p>Die bekannten Inkretin-Peptide werden oft danach eingeordnet, wie viele Rezeptoren sie ansprechen. Semaglutid aktiviert einen Rezeptor (GLP-1), Tirzepatide zwei (GIP und GLP-1). Retatrutide spricht gleich <strong>drei</strong> Rezeptoren an. Aus „der dritte Rezeptor“ bzw. „die dritte Generation“ wurde umgangssprachlich „GLP-3“ – ein griffiger, aber fachlich ungenauer Name. Andere Spitznamen sind „Reta“ oder „Triple G“.</p>

<h2>GLP-1, GLP-2 – und warum es kein GLP-3 gibt</h2>
<p>Im Körper gibt es tatsächlich <strong>GLP-1</strong> und <strong>GLP-2</strong> (Glucagon-like Peptide 1 und 2). Beide entstehen aus demselben Vorläuferprotein, dem Proglucagon, und werden im Darm gebildet. GLP-1 ist an der Regulation von Insulin und Sättigung beteiligt, GLP-2 vor allem an der Darmschleimhaut. Ein natürliches „GLP-3“ ist dagegen nicht beschrieben. Der Name sagt also nichts über ein neues Hormon aus – er ist nur eine Abkürzung für ein Peptid, das mehrere bekannte Signalwege gleichzeitig anspricht.</p>

<h2>Die Einordnung auf einen Blick</h2>
<table class="rg-tab">
<tr><th>Peptid</th><th>Rezeptoren</th><th>Spitzname</th></tr>
<tr><td>Semaglutid</td><td>GLP-1</td><td>–</td></tr>
<tr><td>Tirzepatide</td><td>GLP-1 + GIP</td><td>„Twincretin“</td></tr>
<tr><td>Retatrutide</td><td>GLP-1 + GIP + Glukagon</td><td>„GLP-3“, „Reta“, „Triple G“</td></tr>
</table>
<p>Ausführlicher verglichen werden die drei im Artikel {a('/ratgeber/retatrutide-tirzepatide-semaglutid/', 'Retatrutide vs. Tirzepatide vs. Semaglutid')}.</p>

<h2>Was über Retatrutide bekannt ist</h2>
<p>Retatrutide wurde von Eli Lilly entwickelt und befindet sich in der klinischen Erprobung (Phase-3-Programm). Es ist <strong>kein zugelassenes Arzneimittel</strong>. In der Forschung interessiert vor allem, wie sich die gleichzeitige Aktivierung der drei Rezeptoren auf Signalwege und Stoffwechselregulation auswirkt – im Vergleich zu Peptiden, die nur einen oder zwei davon ansprechen. Einen Überblick über Herkunft und Forschungsstand gibt das {a('/peptid-lexikon/retatrutide/', 'Peptid-Lexikon')}.</p>

<h2>Worauf du achten solltest, wenn „GLP-3“ angeboten wird</h2>
<ul>
<li><strong>Klarer Name:</strong> Seriöse Anbieter nennen den Wirkstoff (Retatrutide) und nicht nur den Spitznamen.</li>
<li><strong>Mengenangabe:</strong> Auf dem Vial sollte die Menge in mg eindeutig stehen, z. B. 10 mg oder 20 mg.</li>
<li><strong>Geprüfte Reinheit:</strong> Reinheit per HPLC, Identität per LC-MS – was das bedeutet, erklärt der Artikel {a('/ratgeber/hplc-lc-ms-reinheit/', 'HPLC & LC-MS')}.</li>
<li><strong>Forschungszweck:</strong> Retatrutide ist nicht zugelassen und nur für die Laborforschung bestimmt.</li>
</ul>
<p>Bei Peptrium findest du {a('/produkt/retatrutide/', 'Retatrutide als 10- und 20-mg-Vial')} sowie fertig gelöst im {a('/produkt/peptrium-pen-retatrutide/', 'Peptrium-Pen')}.</p>
''',
 'faq': [
  ('Ist GLP-3 dasselbe wie Retatrutide?', 'In der Praxis ja: „GLP-3“ ist ein Szenebegriff, der fast immer Retatrutide (LY3437943) meint – den Triple-Agonisten an GLP-1-, GIP- und Glukagon-Rezeptoren.'),
  ('Gibt es ein körpereigenes GLP-3?', 'Nein. Im Körper gibt es GLP-1 und GLP-2, beide aus dem Vorläufer Proglucagon. Ein natürliches GLP-3 ist nicht beschrieben.'),
  ('Ist Retatrutide zugelassen?', 'Nein. Retatrutide befindet sich in der klinischen Erprobung und ist kein zugelassenes Arzneimittel. Bei Peptrium ist es ausschließlich als Forschungspeptid erhältlich.'),
  ('Was bedeutet „Triple-Agonist“?', 'Ein Molekül, das drei verschiedene Rezeptoren aktiviert – bei Retatrutide die Rezeptoren für GLP-1, GIP und Glukagon.'),
 ],
},
{
 'slug': 'retatrutide-10mg-oder-20mg',
 'title': 'Retatrutide 10 mg oder 20 mg: Welches Vial passt?',
 'seo_title': 'Retatrutide 10 mg oder 20 mg? Unterschied & Auswahl',
 'meta': 'Retatrutide 10 mg oder 20 mg: Was unterscheidet die Vials, was bedeutet das für Konzentration, Haltbarkeit und Preis pro mg? Ein sachlicher Überblick.',
 'lead': 'Retatrutide gibt es als 10-mg- und als 20-mg-Vial. Der Inhalt ist derselbe – nur die Menge unterscheidet sich. Welche Größe sinnvoll ist, hängt vor allem davon ab, wie viel Material deine Versuchsreihe braucht und wie schnell du es verbrauchst.',
 'body': f'''
<h2>Das Wichtigste vorweg</h2>
<div class="rg-box">Beide Vials enthalten <strong>dasselbe Peptid in derselben Qualität</strong>. Der Unterschied liegt allein in der Menge: 10 mg oder 20 mg lyophilisiertes Retatrutide pro Vial.</div>

<h2>Der Vergleich auf einen Blick</h2>
<table class="rg-tab">
<tr><th></th><th>10 mg</th><th>20 mg</th></tr>
<tr><td>Inhalt</td><td>10 mg Lyophilisat</td><td>20 mg Lyophilisat</td></tr>
<tr><td>Preis pro mg</td><td>höher</td><td>niedriger</td></tr>
<tr><td>Konzentration bei gleicher Wassermenge</td><td>halb so hoch</td><td>doppelt so hoch</td></tr>
<tr><td>Sinnvoll, wenn …</td><td>kleinere oder erste Versuchsreihen</td><td>größere Versuchsreihen, Vorrat</td></tr>
</table>

<h2>1. Menge und Verbrauch</h2>
<p>Die naheliegende Frage: Wie viel Material brauchst du insgesamt? Für kleinere oder erste Versuchsreihen reicht oft das 10-mg-Vial. Wer größere Versuchsreihen plant, ist mit dem 20-mg-Vial meist besser bedient – und muss seltener nachbestellen.</p>

<h2>2. Haltbarkeit nach dem Anmischen</h2>
<p>Als Pulver (lyophilisiert) ist Retatrutide gekühlt lange stabil. Sobald es aber mit {a('/produkt/bac-water/', 'Bac Water')} rekonstituiert ist, sollte die Lösung gekühlt gelagert und zügig verbraucht werden. Ein großes Vial, das nur teilweise genutzt wird, steht entsprechend länger im Kühlschrank. Wenn du nur wenig auf einmal brauchst, können zwei 10-mg-Vials praktischer sein als ein 20-mg-Vial – du mischst dann nur an, was du gerade brauchst. Details zur Lagerung findest du im {a('/lagerungs-guide/', 'Lagerungs-Guide')}.</p>

<h2>3. Konzentration</h2>
<p>Bei gleicher Wassermenge ist die Lösung aus dem 20-mg-Vial doppelt so konzentriert. Ein Beispiel für die Rechnung im Labor: 10 mg in 2 ml ergeben 5 mg/ml, 20 mg in 2 ml ergeben 10 mg/ml. Wer beim 20-mg-Vial dieselbe Konzentration wie beim 10-mg-Vial möchte, nimmt entsprechend mehr Wasser. Für deine eigenen Werte nutzt du am besten den {a('/peptid-rechner/', 'Peptid-Rechner')}; wie das Anmischen Schritt für Schritt funktioniert, erklärt der Artikel {a('/ratgeber/peptide-rekonstituieren/', 'Peptide rekonstituieren')}.</p>

<h2>4. Preis pro Milligramm</h2>
<p>Das 20-mg-Vial ist pro Milligramm günstiger als das 10-mg-Vial. Zusätzlich gilt bei Peptrium ab 3 Vials ein Mengenrabatt. Die aktuellen Preise siehst du direkt auf der {a('/produkt/retatrutide/', 'Retatrutide-Produktseite')}.</p>

<h2>Und ohne Anmischen?</h2>
<p>Wer gar nicht rekonstituieren möchte, findet Retatrutide fertig gelöst im {a('/produkt/peptrium-pen-retatrutide/', 'Peptrium-Pen')} in 20, 30 oder 40 mg. Was Retatrutide überhaupt ist und warum es oft „GLP-3“ genannt wird, liest du im Artikel {a('/ratgeber/was-ist-glp-3/', 'Was ist GLP-3?')}.</p>
''',
 'faq': [
  ('Ist im 20-mg-Vial ein anderes Retatrutide?', 'Nein. Beide Vials enthalten dasselbe Peptid in derselben Qualität – nur die Menge ist unterschiedlich.'),
  ('Welches Vial ist günstiger?', 'Pro Milligramm ist das 20-mg-Vial günstiger. Ab 3 Vials gibt es zusätzlich Mengenrabatt.'),
  ('Wie lange hält Retatrutide nach dem Anmischen?', 'Rekonstituiert sollte die Lösung gekühlt gelagert und zügig verwendet werden. Als Pulver ist es gekühlt deutlich länger stabil – deshalb nur so viel anmischen, wie gerade gebraucht wird.'),
  ('Wie viel Bac Water brauche ich?', 'Das hängt von der gewünschten Konzentration ab. Mehr Wasser ergibt eine verdünntere Lösung. Die genauen Werte berechnest du mit dem Peptid-Rechner.'),
 ],
},
{
 'slug': 'tb-500-vs-bpc-157',
 'title': 'TB-500 vs. BPC-157: Die Unterschiede einfach erklärt',
 'seo_title': 'TB-500 vs. BPC-157 – Unterschied einfach erklärt',
 'meta': 'TB-500 und BPC-157 im Vergleich: Herkunft, Aufbau, Forschungsschwerpunkte und warum beide oft zusammen untersucht werden – sachlich und verständlich erklärt.',
 'lead': 'TB-500 und BPC-157 werden oft in einem Atemzug genannt – beide stehen im Mittelpunkt der Regenerationsforschung. Trotzdem sind es zwei völlig unterschiedliche Peptide mit eigener Herkunft und eigenem Forschungsschwerpunkt.',
 'body': f'''
<h2>Kurz gesagt</h2>
<div class="rg-box"><strong>BPC-157</strong> ist ein kurzes Peptid aus 15 Aminosäuren, abgeleitet von einem Protein aus dem Magensaft. <strong>TB-500</strong> orientiert sich an der Aktin-bindenden Region des körpereigenen Proteins Thymosin Beta-4. Beide sind nicht als Arzneimittel zugelassen.</div>

<h2>Der Vergleich auf einen Blick</h2>
<table class="rg-tab">
<tr><th></th><th>BPC-157</th><th>TB-500</th></tr>
<tr><td>Vorbild</td><td>Abschnitt eines Magensaft-Proteins</td><td>Aktive Region von Thymosin Beta-4</td></tr>
<tr><td>Aufbau</td><td>15 Aminosäuren (Pentadecapeptid)</td><td>synthetisches Peptid, orientiert an Tβ4</td></tr>
<tr><td>Forschungsschwerpunkt</td><td>Gewebekommunikation, Angiogenese</td><td>Zellmigration, Zytoskelett, Aktin-Bindung</td></tr>
<tr><td>Datenlage</td><td>überwiegend präklinisch</td><td>überwiegend präklinisch</td></tr>
<tr><td>Status</td><td>nicht zugelassen</td><td>nicht zugelassen</td></tr>
</table>

<h2>BPC-157: das „Body Protection Compound“</h2>
<p>BPC-157 steht für „Body Protection Compound“. Die Sequenz aus 15 Aminosäuren ist von einem Abschnitt eines Proteins abgeleitet, das im menschlichen Magensaft vorkommt. Seit den 1990er-Jahren wird BPC-157 vor allem in Zell- und Tiermodellen untersucht – unter anderem zu Gewebekommunikation, zur Bildung neuer Blutgefäße (Angiogenese) und zu strukturellen Anpassungsprozessen. Mehr dazu im {a('/peptid-lexikon/bpc-157/', 'Peptid-Lexikon: BPC-157')}.</p>

<h2>TB-500: abgeleitet von Thymosin Beta-4</h2>
<p>Thymosin Beta-4 (Tβ4) ist eines der häufigsten Proteine in menschlichen Zellen und bindet an Aktin – einen zentralen Baustein des Zellgerüsts (Zytoskelett). TB-500 ist ein synthetisches Peptid, das sich an genau dieser Aktin-bindenden Region orientiert. Im Labor wird es genutzt, um Zellmigration, die Dynamik des Zytoskeletts und Aktin-Bindungsmechanismen zu untersuchen. Mehr dazu im {a('/peptid-lexikon/tb-500/', 'Peptid-Lexikon: TB-500')}.</p>

<h2>Warum werden beide oft zusammen untersucht?</h2>
<p>Weil sie an unterschiedlichen Stellen ansetzen: BPC-157 wird eher im Zusammenhang mit Signalen zwischen Geweben und Gefäßbildung betrachtet, TB-500 eher im Zusammenhang mit Zellbewegung und Zellgerüst. In der Regenerationsforschung ist deshalb interessant, wie sich beide Ansätze in einem Modell gegenseitig ergänzen. Wichtig: Fast alle Daten stammen bisher aus präklinischen Studien, belastbare klinische Daten am Menschen fehlen für beide Peptide.</p>

<h2>Einzeln oder als Blend?</h2>
<p>Für getrennte Versuche oder eigene Mischverhältnisse sind die Einzelpeptide die richtige Wahl – {a('/produkt/bpc-157/', 'BPC-157')} und {a('/produkt/tb-500/', 'TB-500')}. Wer genau die Kombination untersuchen möchte, findet beide zusammen mit GHK-Cu im Blend {a('/produkt/glow/', 'GLOW')}; was dahintersteckt, erklärt der Artikel {a('/ratgeber/glow-peptid-blend/', 'GLOW-Peptid-Blend')}.</p>

<h2>Lieferform und Anmischen</h2>
<p>Beide Peptide werden als lyophilisiertes Pulver im Vial geliefert und mit {a('/produkt/bac-water/', 'Bac Water')} rekonstituiert. Wie das funktioniert, liest du im Artikel {a('/ratgeber/peptide-rekonstituieren/', 'Peptide rekonstituieren')}; die Konzentration berechnest du mit dem {a('/peptid-rechner/', 'Peptid-Rechner')}.</p>
''',
 'faq': [
  ('Was ist der Hauptunterschied zwischen TB-500 und BPC-157?', 'Die Herkunft: BPC-157 ist von einem Magensaft-Protein abgeleitet, TB-500 von der Aktin-bindenden Region des Proteins Thymosin Beta-4. Entsprechend unterscheiden sich auch die Forschungsschwerpunkte.'),
  ('Kann man TB-500 und BPC-157 zusammen untersuchen?', 'In der Regenerationsforschung werden beide häufig gemeinsam betrachtet. Für genau diese Kombination gibt es den Blend GLOW (zusammen mit GHK-Cu); für eigene Mischverhältnisse eignen sich die Einzelpeptide.'),
  ('Sind TB-500 oder BPC-157 zugelassen?', 'Nein. Beide sind keine zugelassenen Arzneimittel und bei Peptrium ausschließlich für die Laborforschung erhältlich.'),
  ('Ist TB500 dasselbe wie TB-500?', 'Ja. „TB500“, „TB 500“ und „TB-500“ sind nur unterschiedliche Schreibweisen für dasselbe Peptid.'),
 ],
},
{
 'slug': 'bac-water-bakteriostatisches-wasser',
 'title': 'Bac Water: Wofür man bakteriostatisches Wasser braucht',
 'seo_title': 'Bac Water: bakteriostatisches Wasser erklärt',
 'meta': 'Was ist Bac Water (bakteriostatisches Wasser), wie unterscheidet es sich von sterilem Wasser und wie wird es gelagert? Alles Wichtige für die Laborpraxis.',
 'lead': 'Fast alle Peptide werden als gefriergetrocknetes Pulver geliefert. Damit man im Labor mit ihnen arbeiten kann, müssen sie zuerst gelöst werden – und dafür wird meist Bac Water verwendet. Was das genau ist und worauf es bei der Lagerung ankommt, erfährst du hier.',
 'body': f'''
<h2>Was ist Bac Water?</h2>
<div class="rg-box"><strong>Bac Water</strong> (bakteriostatisches Wasser) ist steriles Wasser, dem <strong>0,9 % Benzylalkohol</strong> zugesetzt ist. Der Benzylalkohol hemmt das Wachstum von Bakterien – deshalb „bakteriostatisch“. So kann ein Vial mehrfach angestochen werden, ohne dass sich darin leicht Keime vermehren.</div>

<h2>Wofür wird Bac Water im Labor verwendet?</h2>
<p>Peptide werden meist <strong>lyophilisiert</strong> geliefert, also gefriergetrocknet als Pulver. In dieser Form sind sie stabil und gut lagerbar. Für die Arbeit im Labor müssen sie aber gelöst werden – dieser Schritt heißt <strong>Rekonstitution</strong>. Bac Water ist dafür das Standard-Lösungsmittel, weil eine angemischte Lösung oft über mehrere Tage verwendet und dafür mehrfach entnommen wird. Wie das Anmischen Schritt für Schritt funktioniert, erklärt der Artikel {a('/ratgeber/peptide-rekonstituieren/', 'Peptide rekonstituieren')}.</p>

<h2>Bac Water vs. steriles Wasser</h2>
<table class="rg-tab">
<tr><th></th><th>Bac Water</th><th>Steriles Wasser</th></tr>
<tr><td>Zusatz</td><td>0,9 % Benzylalkohol</td><td>keiner</td></tr>
<tr><td>Mehrfache Entnahme</td><td>dafür gedacht</td><td>eher für einmalige Verwendung</td></tr>
<tr><td>Nach dem Anbrechen</td><td>in der Regel bis zu 28 Tage verwendbar</td><td>möglichst sofort verbrauchen</td></tr>
</table>
<p>Für Lösungen, die über mehrere Tage genutzt werden, ist Bac Water deshalb meist die praktischere Wahl.</p>

<h2>Lagerung und Haltbarkeit</h2>
<ul>
<li><strong>Ungeöffnet:</strong> bei Raumtemperatur oder kühl, lichtgeschützt und trocken – bis zum Ablaufdatum.</li>
<li><strong>Angebrochen:</strong> kühl lagern und in der Regel innerhalb von 28 Tagen aufbrauchen.</li>
<li><strong>Sauber arbeiten:</strong> den Gummistopfen vor jeder Entnahme mit einem Alkoholtupfer abwischen und für jede Entnahme eine frische Spritze verwenden.</li>
<li><strong>Sichtprüfung:</strong> Trübungen, Schwebeteilchen oder Verfärbungen sind ein Zeichen, dass das Wasser nicht mehr verwendet werden sollte.</li>
</ul>
<p>Für die angemischten Peptide selbst gelten eigene Regeln – die findest du im {a('/lagerungs-guide/', 'Lagerungs-Guide')}.</p>

<h2>Wie viel Bac Water brauche ich?</h2>
<p>Das hängt davon ab, welche Konzentration du erreichen möchtest: Mehr Wasser ergibt eine verdünntere, weniger Wasser eine konzentriertere Lösung. Ein Rechenbeispiel: 10 mg Peptid in 2 ml Bac Water ergeben 5 mg/ml. Deine eigenen Werte berechnest du am einfachsten mit dem {a('/peptid-rechner/', 'Peptid-Rechner')}.</p>

<h2>Bac Water bei Peptrium</h2>
<p>Unser {a('/produkt/bac-water/', 'Bac Water')} ist steril abgefüllt und passt zu allen Peptiden im Sortiment. Zum Abmessen eignen sich unsere {a('/produkt/insulinspritze-10er-pack/', 'Insulinspritzen')}. Wer gar nicht anmischen möchte, findet Retatrutide, GHK-Cu und MOTS-c auch fertig gelöst in den {a('/alle-peptrium-pens/', 'Peptrium-Pens')}.</p>
''',
 'faq': [
  ('Was ist der Unterschied zwischen Bac Water und sterilem Wasser?', 'Bac Water enthält 0,9 % Benzylalkohol, der das Wachstum von Bakterien hemmt. Dadurch kann ein Vial mehrfach angestochen werden. Steriles Wasser enthält keinen Zusatz und ist eher für die einmalige Verwendung gedacht.'),
  ('Wie lange ist Bac Water nach dem Öffnen haltbar?', 'Angebrochen in der Regel bis zu 28 Tage – kühl gelagert und bei sauberer Entnahme.'),
  ('Muss Bac Water in den Kühlschrank?', 'Ungeöffnet nicht zwingend: Raumtemperatur oder kühl, lichtgeschützt und trocken reicht. Nach dem Anbrechen sollte es kühl gelagert werden.'),
  ('Kann ich Leitungswasser oder Kochsalzlösung verwenden?', 'Leitungswasser nicht – es ist nicht steril. Für die Rekonstitution von Peptiden im Labor ist Bac Water der Standard.'),
 ],
},
]
