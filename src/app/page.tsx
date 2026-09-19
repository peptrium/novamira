import { coachings } from "@/lib/coachings";
import { CoachingCard } from "@/components/CoachingCard";

export default function Home() {
  return (
    <div className="mx-auto max-w-4xl px-6 py-16">
      <section className="flex flex-col gap-4 py-8 text-center sm:text-left">
        <h1 className="text-3xl font-semibold tracking-tight sm:text-4xl">
          Coaching, das dich wirklich weiterbringt
        </h1>
        <p className="max-w-2xl text-lg text-zinc-600 dark:text-zinc-400 sm:mx-0 mx-auto">
          Wähle das passende Angebot und buche direkt online. Bezahlung
          bequem per Banküberweisung.
        </p>
      </section>

      <section id="angebote" className="py-8">
        <h2 className="mb-6 text-xl font-semibold">Unsere Angebote</h2>
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
          {coachings.map((coaching) => (
            <CoachingCard key={coaching.slug} coaching={coaching} />
          ))}
        </div>
      </section>
    </div>
  );
}
