import { notFound } from "next/navigation";
import { coachings, getCoachingBySlug, formatPrice } from "@/lib/coachings";
import { AddToCartButton } from "@/components/AddToCartButton";

export function generateStaticParams() {
  return coachings.map((c) => ({ slug: c.slug }));
}

export default async function CoachingDetailPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const coaching = getCoachingBySlug(slug);

  if (!coaching) {
    notFound();
  }

  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      <h1 className="text-3xl font-semibold tracking-tight">
        {coaching.title}
      </h1>
      <p className="mt-2 text-sm text-zinc-500 dark:text-zinc-500">
        {coaching.durationMinutes} Min. &middot; {coaching.format}
      </p>

      <div className="mt-6 flex flex-col gap-4 text-base text-zinc-700 dark:text-zinc-300">
        {coaching.description.map((paragraph, i) => (
          <p key={i}>{paragraph}</p>
        ))}
      </div>

      <div className="mt-8 flex items-center gap-4">
        <span className="text-2xl font-semibold">
          {formatPrice(coaching.price)}
        </span>
      </div>

      <div className="mt-6">
        <AddToCartButton slug={coaching.slug} />
      </div>
    </div>
  );
}
