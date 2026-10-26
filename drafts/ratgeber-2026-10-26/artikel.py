# Ratgeber-Artikel 6 + 7 (Entwurf 2026-10-26, Nutzer: "ja schreib die zwei ratgeber artikel").
# Gleiches Format wie drafts/ratgeber-2026-10-17/artikel.py. Regeln: Forschungsrahmen, keine Dosierung fuer Menschen,
# keine Wirkversprechen, keine Zertifikats-Versprechen, keine Preise (aendern sich).
P = 'https://peptrium.com'


def a(path, text):
    return f'<a href="{P}{path}">{text}</a>'


HINWEIS = ('<p class="rg-note">Alle Produkte von Peptrium sind ausschließlich für die Laborforschung bestimmt – '
           'nicht für die Anwendung am Menschen oder Tier. Dieser Artikel dient der allgemeinen Information und ist keine Anwendungsanleitung.</p>')

ARTIKEL = [
{
 'slug': 'cagrilintid-retatrutide-unterschied',
 'title': 'Cagrilintid vs. Retatrutide: Amylin und GLP-1 im Vergleich',
 'seo_title': 'Cagrilintid vs. Retatrutide – Unterschied einfach erklärt',
 'meta': 'Cagrilintid und Retatrutide im Vergleich: Amylin-Analogon gegen Triple-Agonist, Rezeptoren, Entwicklungsstand und Rolle in der Stoffwechselforschung – sachlich erklärt.',
 'lead': 'Beide Peptide stehen in der Stoffwechselforschung im Mittelpunkt – setzen aber an völlig unterschiedlichen Hormonsystemen an. Retatrutide orientiert sich an den Darmhormonen GLP-1 und GIP sowie an Glukagon, Cagrilintid am Hormon Amylin aus der Bauchspeicheldrüse. Hier der Unterschied in Ruhe erklärt.',
 'body': f'''
<h2>Zwei Hormonsysteme, ein Forschungsfeld</h2>
<p>Wenn wir essen, reagiert der Körper mit einer ganzen Reihe von Hormonen. Aus dem Darm kommen die Inkretine <strong>GLP-1</strong> und <strong>GIP</strong>, aus der Bauchspeicheldrüse unter anderem <strong>Insulin</strong>, <strong>Glukagon</strong> – und <strong>Amylin</strong>. Amylin wird zusammen mit Insulin aus denselben Zellen ausgeschüttet und ist an Sättigungssignalen und an der Geschwindigkeit der Magenentleerung beteiligt.</p>
<p>Retatrutide und Cagrilintid sind synthetische Peptide, die diese natürlichen Signale nachahmen – aber eben unterschiedliche. Genau das macht den Vergleich für die Forschung interessant.</p>

<h2>Der Unterschied auf einen Blick</h2>
<table class="rg-tab">
<tr><th></th><th>Cagrilintid</th><th>Retatrutide</th></tr>
<tr><td>Vorbild</td><td>Amylin</td><td>GLP-1, GIP und Glukagon</td></tr>
<tr><td>Rezeptoren</td><td>Amylin- und Calcitonin-Rezeptoren</td><td>GLP-1-, GIP- und Glukagon-Rezeptor</td></tr>
<tr><td>Typ</td><td>Langwirksames Amylin-Analogon</td><td>Triple-Agonist</td></tr>
<tr><td>Entwickler</td><td>Novo Nordisk</td><td>Eli Lilly</td></tr>
<tr><td>Status</td><td>klinische Entwicklung</td><td>klinische Entwicklung (Phase 3)</td></tr>
</table>

<h2>Cagrilintid: das Amylin-Analogon</h2>
<p>Natürliches Amylin ist im Körper nur sehr kurz stabil und neigt in Lösung dazu, zu verklumpen. Cagrilintid wurde so verändert, dass es beides nicht tut: Eine Fettsäure-Seitenkette (Lipidierung) lässt es an das Bluteiweiß Albumin binden, wodurch es in Studien eine deutlich längere Halbwertszeit zeigt. Es aktiviert Amylin-Rezeptoren und den Calcitonin-Rezeptor. Bekannt wurde Cagrilintid vor allem durch Studien, in denen es <strong>mit dem GLP-1-Agonisten Semaglutid kombiniert</strong> untersucht wurde. Cagrilintid allein ist kein zugelassenes Arzneimittel.</p>

<h2>Retatrutide: der Triple-Agonist</h2>
<p>Retatrutide (Entwicklungsname LY3437943) aktiviert gleich drei Rezeptoren: GLP-1, GIP und Glukagon. Nach einer viel beachteten Phase-2-Studie läuft das Phase-3-Programm. Wie es sich von Semaglutid und Tirzepatide unterscheidet, steht ausführlich in unserem Artikel {a('/ratgeber/retatrutide-tirzepatide-semaglutid/', 'Retatrutide vs. Tirzepatide vs. Semaglutid')}. Auch Retatrutide ist kein zugelassenes Arzneimittel.</p>

<h2>Warum man beide nebeneinander untersucht</h2>
<p>Weil die beiden Peptide an verschiedenen Rezeptor-Familien ansetzen, lassen sich mit ihnen unterschiedliche Signalwege getrennt betrachten: Welchen Anteil hat das Amylin-System an einem Effekt, welchen das Inkretin-System? Die Kombination aus Amylin- und GLP-1-Signal ist derzeit eines der meistdiskutierten Themen der Stoffwechselforschung – entsprechend gefragt sind beide Substanzen als Referenz im Labor, in Zellmodellen und in Rezeptor-Bindungsstudien.</p>

<h2>Praktisch im Labor</h2>
<p>Beide Peptide werden als lyophilisiertes Pulver geliefert und vor dem Einsatz mit {a('/produkt/bac-water/', 'Bac Water')} gelöst. Wie das sauber funktioniert, steht in {a('/ratgeber/peptide-rekonstituieren/', 'Peptide rekonstituieren')}; die Konzentration rechnet dir der {a('/peptid-rechner/', 'Peptid-Rechner')} aus. Ungelöst lagern beide am besten bei 2–8 °C, langfristig bei −20 °C – mehr im {a('/lagerungs-guide/', 'Lagerungs-Guide')}.</p>

<h2>Bei Peptrium erhältlich</h2>
<p>{a('/produkt/cagrilintid/', 'Cagrilintid 10 mg')} ist neu im Sortiment, {a('/produkt/retatrutide/', 'Retatrutide')} gibt es mit 10 und 20 mg sowie fertig gemischt als {a('/produkt/peptrium-pen-retatrutide/', 'Peptrium-Pen')}. Alle Produkte sind HPLC- und LC-MS-geprüft und ausschließlich für die Laborforschung bestimmt.</p>
''',
 'faq': [
  ('Was ist der Unterschied zwischen Cagrilintid und Retatrutide?', 'Cagrilintid ahmt das Hormon Amylin nach und wirkt an Amylin- und Calcitonin-Rezeptoren. Retatrutide aktiviert die Rezeptoren für GLP-1, GIP und Glukagon. Die beiden setzen also an unterschiedlichen Hormonsystemen an.'),
  ('Was ist Amylin?', 'Ein Peptidhormon, das zusammen mit Insulin aus der Bauchspeicheldrüse ausgeschüttet wird. Es ist an Sättigungssignalen und an der Magenentleerung beteiligt.'),
  ('Ist Cagrilintid zugelassen?', 'Nein. Cagrilintid befindet sich in der klinischen Entwicklung und ist kein zugelassenes Arzneimittel. Bei Peptrium ist es ausschließlich als Forschungssubstanz für das Labor erhältlich.'),
  ('Was bedeutet „lipidiert“?', 'An das Peptid ist eine Fettsäure-Kette gekoppelt. Dadurch bindet es an Albumin im Blut und wird langsamer abgebaut – das verlängert die Halbwertszeit.'),
 ],
},
{
 'slug': 'glow-peptid-blend',
 'title': 'GLOW erklärt: GHK-Cu, BPC-157 und TB-500 in einem Vial',
 'seo_title': 'GLOW Peptid-Blend erklärt – GHK-Cu, BPC-157 und TB-500',
 'meta': 'Was steckt im Peptid-Blend GLOW? GHK-Cu, BPC-157 und TB-500 einzeln erklärt, das Mischverhältnis, Rekonstitution des Blends und Lagerung – sachlich für die Forschung.',
 'lead': 'GLOW ist kein eigenes Peptid, sondern eine Mischung aus drei bekannten Forschungspeptiden in einem Fläschchen: GHK-Cu, BPC-157 und TB-500. Hier erfährst du, was die drei Bausteine sind, warum man sie kombiniert und worauf du beim Anmischen eines Blends achten solltest.',
 'body': f'''
<h2>Was steckt drin?</h2>
<table class="rg-tab">
<tr><th>Peptid</th><th>Menge pro Vial</th><th>Kurz erklärt</th></tr>
<tr><td>GHK-Cu</td><td>50 mg</td><td>Kupfer-Peptidkomplex aus drei Aminosäuren</td></tr>
<tr><td>BPC-157</td><td>10 mg</td><td>Peptid aus 15 Aminosäuren</td></tr>
<tr><td>TB-500</td><td>10 mg</td><td>Abgeleitet von Thymosin β4</td></tr>
<tr><td><strong>Gesamt</strong></td><td><strong>70 mg</strong></td><td>Verhältnis 5 : 1 : 1</td></tr>
</table>
<p>„GLOW“ ist dabei nur ein Handelsname für diese Kombination – keine Wirkbeschreibung.</p>

<h2>GHK-Cu</h2>
<p>GHK ist ein sehr kleines Peptid aus den drei Aminosäuren Glycin, Histidin und Lysin. Es wurde in den 1970er-Jahren im menschlichen Blutplasma entdeckt und bindet Kupfer-Ionen sehr fest – daher „-Cu“. In der Forschung wird GHK-Cu vor allem in Haut- und Bindegewebsmodellen untersucht, zum Beispiel zur Bildung von Kollagen. Typisch: Durch das Kupfer ist das Pulver bläulich und die fertige Lösung blau gefärbt. Mehr zum Einzelpeptid auf der Seite {a('/produkt/ghk-cu/', 'GHK-Cu')}.</p>

<h2>BPC-157</h2>
<p>BPC-157 ist ein synthetisches Peptid aus 15 Aminosäuren. Seine Sequenz leitet sich von einem Protein ab, das im Magensaft vorkommt („Body Protection Compound“). Untersucht wurde es bislang überwiegend in Tier- und Zellmodellen, unter anderem zu Sehnen, Bändern und Wundheilung. BPC-157 ist kein zugelassenes Arzneimittel. Als Einzelpeptid: {a('/produkt/bpc-157/', 'BPC-157')}.</p>

<h2>TB-500</h2>
<p>TB-500 ist ein synthetisches Peptid, abgeleitet vom aktiven Bereich des körpereigenen Proteins <strong>Thymosin β4</strong>. Thymosin β4 bindet Aktin – einen Hauptbaustein des Zellskeletts – und spielt bei der Wanderung von Zellen eine Rolle. Deshalb taucht es in Studien zu Regeneration und Gewebereparatur auf. Als Einzelpeptid: {a('/produkt/tb-500/', 'TB-500')}.</p>

<h2>Warum ein Blend?</h2>
<p>Alle drei Peptide werden in ähnlichen Forschungsfeldern untersucht: Haut, Bindegewebe und Regeneration. Ein Blend erlaubt es, die Kombination <strong>in einem einzigen Ansatz</strong> zu betrachten – mit festem Mischverhältnis und nur einem Fläschchen statt dreien. Der Nachteil: Das Verhältnis lässt sich nicht verändern. Wer die Peptide einzeln oder in anderen Verhältnissen untersuchen will, greift zu den Einzelpeptiden.</p>

<h2>Anmischen: auf die Konzentration achten</h2>
<p>Ein Blend wird genauso rekonstituiert wie ein einzelnes Peptid – Schritt für Schritt erklärt in {a('/ratgeber/peptide-rekonstituieren/', 'Peptide rekonstituieren')}. Wichtig ist nur: In der Lösung stecken drei Peptide in unterschiedlicher Menge. Notiere dir deshalb die Konzentration <em>jedes</em> Bestandteils.</p>
<div class="rg-box">Beispiel: 70 mg GLOW + 3 ml {a('/produkt/bac-water/', 'Bac Water')}<br>→ GHK-Cu ≈ 16,7 mg/ml · BPC-157 ≈ 3,3 mg/ml · TB-500 ≈ 3,3 mg/ml</div>
<p>Die Rechnung ist einfach: Menge des Bestandteils geteilt durch die Wassermenge. Für einzelne Peptide hilft der {a('/peptid-rechner/', 'Peptid-Rechner')}.</p>

<h2>Lagerung</h2>
<p>Ungelöst bei 2–8 °C lagern, langfristig bei −20 °C, immer dunkel. Nach dem Anmischen gehört die Lösung in den Kühlschrank; wiederholtes Einfrieren und Auftauen solltest du vermeiden. Details im {a('/lagerungs-guide/', 'Lagerungs-Guide')}.</p>

<h2>Bei Peptrium erhältlich</h2>
<p>{a('/produkt/glow/', 'GLOW 70 mg')} ist neu im Sortiment – HPLC- und LC-MS-geprüft und ausschließlich für die Laborforschung bestimmt. Die drei Bestandteile gibt es auch einzeln.</p>
''',
 'faq': [
  ('Was ist in GLOW enthalten?', 'GHK-Cu (50 mg), BPC-157 (10 mg) und TB-500 (10 mg) – zusammen 70 mg in einem Vial.'),
  ('Warum ist die Lösung blau?', 'Das liegt am Kupfer im GHK-Cu. Eine blaue Färbung ist bei GHK-Cu-haltigen Lösungen normal.'),
  ('Ist ein Blend besser als die Einzelpeptide?', 'Nicht besser, sondern anders: Ein Blend ist praktisch, wenn genau diese Kombination untersucht werden soll. Für andere Mischverhältnisse oder getrennte Versuche sind die Einzelpeptide die bessere Wahl.'),
  ('Wie viel Bac Water brauche ich?', 'Das hängt davon ab, welche Konzentration du brauchst. Mehr Wasser ergibt eine verdünntere Lösung – rechne die Konzentration jedes Bestandteils einzeln aus.'),
 ],
},
]
