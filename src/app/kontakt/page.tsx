export default function KontaktPage() {
  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      <h1 className="text-2xl font-semibold">Kontakt</h1>
      <p className="mt-4 text-zinc-600 dark:text-zinc-400">
        Fragen zu einem Angebot oder deiner Bestellung? Melde dich gerne:
      </p>
      <dl className="mt-6 flex flex-col gap-2 text-sm text-zinc-700 dark:text-zinc-300">
        <div className="flex gap-2">
          <dt className="font-medium">E-Mail:</dt>
          <dd>[PLATZHALTER: E-Mail-Adresse]</dd>
        </div>
        <div className="flex gap-2">
          <dt className="font-medium">Telefon:</dt>
          <dd>[PLATZHALTER: Telefonnummer]</dd>
        </div>
      </dl>
    </div>
  );
}
