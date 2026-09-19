import Link from "next/link";
import { formatPrice, type Coaching } from "@/lib/coachings";

export function CoachingCard({ coaching }: { coaching: Coaching }) {
  return (
    <Link
      href={`/angebote/${coaching.slug}`}
      className="flex flex-col justify-between gap-4 rounded-xl border border-black/10 p-6 transition-colors hover:border-black/30 dark:border-white/10 dark:hover:border-white/30"
    >
      <div>
        <h3 className="text-lg font-semibold">{coaching.title}</h3>
        <p className="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
          {coaching.shortDescription}
        </p>
      </div>
      <div className="flex items-center justify-between text-sm">
        <span className="text-zinc-500 dark:text-zinc-500">
          {coaching.durationMinutes} Min. &middot; {coaching.format}
        </span>
        <span className="font-semibold">{formatPrice(coaching.price)}</span>
      </div>
    </Link>
  );
}
