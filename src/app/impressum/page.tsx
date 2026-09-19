export default function ImpressumPage() {
  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      <h1 className="text-2xl font-semibold">Impressum</h1>
      <p className="mt-4 text-sm text-amber-600 dark:text-amber-400">
        Platzhalter &ndash; vor Live-Schaltung mit echten Angaben gemäß § 5
        TMG (DE) bzw. § 5 ECG (AT) / Art. 3 UWG (CH) ersetzen.
      </p>

      <div className="mt-8 flex flex-col gap-6 text-sm text-zinc-700 dark:text-zinc-300">
        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            Angaben gemäß § 5 TMG
          </h2>
          <p className="mt-1">
            [PLATZHALTER: Vor- und Nachname / Firmenname]
            <br />
            [PLATZHALTER: Straße und Hausnummer]
            <br />
            [PLATZHALTER: PLZ und Ort]
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            Kontakt
          </h2>
          <p className="mt-1">
            Telefon: [PLATZHALTER]
            <br />
            E-Mail: [PLATZHALTER]
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            Umsatzsteuer-ID
          </h2>
          <p className="mt-1">
            [PLATZHALTER: USt-IdNr. gemäß § 27a UStG, falls vorhanden]
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV
          </h2>
          <p className="mt-1">[PLATZHALTER: Name und Anschrift]</p>
        </section>
      </div>
    </div>
  );
}
