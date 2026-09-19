export default function DatenschutzPage() {
  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      <h1 className="text-2xl font-semibold">Datenschutzerklärung</h1>
      <p className="mt-4 text-sm text-amber-600 dark:text-amber-400">
        Platzhalter &ndash; vor Live-Schaltung von einer sachkundigen Stelle
        (z.B. Datenschutzgenerator, Anwalt) auf Grundlage der DSGVO prüfen
        und ergänzen.
      </p>

      <div className="mt-8 flex flex-col gap-6 text-sm text-zinc-700 dark:text-zinc-300">
        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            1. Verantwortlicher
          </h2>
          <p className="mt-1">[PLATZHALTER: Name, Anschrift, Kontakt]</p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            2. Erhebung und Verarbeitung von Daten
          </h2>
          <p className="mt-1">
            Bei einer Bestellung erheben wir Name, E-Mail-Adresse und die
            von dir angegebenen Bestelldetails, um deine Bestellung
            abzuwickeln und dich zu kontaktieren. Rechtsgrundlage ist Art. 6
            Abs. 1 lit. b DSGVO (Vertragserfüllung).
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            3. Speicherdauer
          </h2>
          <p className="mt-1">[PLATZHALTER: Aufbewahrungsfristen]</p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            4. Deine Rechte
          </h2>
          <p className="mt-1">
            Du hast das Recht auf Auskunft, Berichtigung, Löschung,
            Einschränkung der Verarbeitung, Datenübertragbarkeit und
            Widerspruch. Wende dich dazu an [PLATZHALTER: Kontakt].
          </p>
        </section>
      </div>
    </div>
  );
}
