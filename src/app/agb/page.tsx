export default function AgbPage() {
  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      <h1 className="text-2xl font-semibold">
        Allgemeine Geschäftsbedingungen (AGB)
      </h1>
      <p className="mt-4 text-sm text-amber-600 dark:text-amber-400">
        Platzhalter &ndash; vor Live-Schaltung juristisch prüfen lassen,
        insbesondere im Hinblick auf Dienstleistungsverträge und
        Fernabsatzrecht.
      </p>

      <div className="mt-8 flex flex-col gap-6 text-sm text-zinc-700 dark:text-zinc-300">
        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            1. Geltungsbereich
          </h2>
          <p className="mt-1">
            Diese AGB gelten für alle Coaching-Leistungen, die über diese
            Website von [PLATZHALTER: Firmenname] angeboten werden.
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            2. Vertragsschluss
          </h2>
          <p className="mt-1">
            Mit Abschluss der Bestellung gibt der Kunde ein verbindliches
            Angebot zum Kauf der ausgewählten Leistung ab. Der Vertrag kommt
            durch Bestätigung von [PLATZHALTER: Firmenname] zustande.
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            3. Preise und Zahlung
          </h2>
          <p className="mt-1">
            Alle Preise verstehen sich in Euro. Die Zahlung erfolgt per
            Vorkasse/Banküberweisung unter Angabe der Bestellreferenz. Der
            Termin wird nach Zahlungseingang vereinbart bzw. bestätigt.
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            4. Terminabsagen und Stornierung
          </h2>
          <p className="mt-1">[PLATZHALTER: Stornobedingungen]</p>
        </section>
      </div>
    </div>
  );
}
