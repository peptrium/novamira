export default function WiderrufPage() {
  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      <h1 className="text-2xl font-semibold">Widerrufsrecht</h1>
      <p className="mt-4 text-sm text-amber-600 dark:text-amber-400">
        Platzhalter &ndash; die gesetzliche Musterwiderrufsbelehrung vor
        Live-Schaltung mit den echten Firmendaten ausfüllen und juristisch
        prüfen lassen.
      </p>

      <div className="mt-8 flex flex-col gap-6 text-sm text-zinc-700 dark:text-zinc-300">
        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            Widerrufsrecht
          </h2>
          <p className="mt-1">
            Verbraucher haben das Recht, binnen vierzehn Tagen ohne Angabe
            von Gründen diesen Vertrag zu widerrufen. Die Widerrufsfrist
            beträgt vierzehn Tage ab dem Tag des Vertragsabschlusses.
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            Ausübung des Widerrufs
          </h2>
          <p className="mt-1">
            Um dein Widerrufsrecht auszuüben, musst du uns ([PLATZHALTER:
            Firmenname, Anschrift, E-Mail]) mittels einer eindeutigen
            Erklärung über deinen Entschluss, diesen Vertrag zu widerrufen,
            informieren.
          </p>
        </section>

        <section>
          <h2 className="font-semibold text-zinc-900 dark:text-zinc-100">
            Erlöschen bei bereits erbrachter Leistung
          </h2>
          <p className="mt-1">
            Hast du ausdrücklich zugestimmt, dass mit der Ausführung der
            Coaching-Leistung vor Ablauf der Widerrufsfrist begonnen wird,
            und bestätigst du deine Kenntnis, dass du dein Widerrufsrecht
            bei vollständiger Vertragserfüllung verlierst, erlischt das
            Widerrufsrecht entsprechend.
          </p>
        </section>
      </div>
    </div>
  );
}
