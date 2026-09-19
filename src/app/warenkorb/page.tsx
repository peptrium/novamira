"use client";

import Link from "next/link";
import { useCart } from "@/lib/cart-context";
import { coachings, formatPrice } from "@/lib/coachings";

export default function WarenkorbPage() {
  const { items, setQuantity, removeItem, totalPrice } = useCart();

  if (items.length === 0) {
    return (
      <div className="mx-auto max-w-2xl px-6 py-16 text-center">
        <h1 className="text-2xl font-semibold">Dein Warenkorb ist leer</h1>
        <Link
          href="/#angebote"
          className="mt-6 inline-block rounded-full bg-foreground px-6 py-3 text-sm font-medium text-background hover:bg-[#383838] dark:hover:bg-[#ccc]"
        >
          Angebote ansehen
        </Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      <h1 className="text-2xl font-semibold">Warenkorb</h1>

      <div className="mt-8 flex flex-col gap-6">
        {items.map((item) => {
          const coaching = coachings.find((c) => c.slug === item.slug);
          if (!coaching) return null;
          return (
            <div
              key={item.slug}
              className="flex items-center justify-between gap-4 border-b border-black/10 pb-6 dark:border-white/10"
            >
              <div>
                <p className="font-medium">{coaching.title}</p>
                <p className="text-sm text-zinc-500">
                  {formatPrice(coaching.price)} pro Stück
                </p>
              </div>
              <div className="flex items-center gap-3">
                <input
                  type="number"
                  min={1}
                  value={item.quantity}
                  onChange={(e) =>
                    setQuantity(item.slug, Number(e.target.value))
                  }
                  className="w-16 rounded border border-black/15 px-2 py-1 text-center dark:border-white/15 dark:bg-transparent"
                />
                <button
                  onClick={() => removeItem(item.slug)}
                  className="text-sm text-zinc-500 hover:underline"
                >
                  Entfernen
                </button>
              </div>
            </div>
          );
        })}
      </div>

      <div className="mt-8 flex items-center justify-between text-lg font-semibold">
        <span>Gesamt</span>
        <span>{formatPrice(totalPrice)}</span>
      </div>

      <Link
        href="/checkout"
        className="mt-8 block w-full rounded-full bg-foreground px-6 py-3 text-center text-sm font-medium text-background hover:bg-[#383838] dark:hover:bg-[#ccc]"
      >
        Zur Kasse
      </Link>
    </div>
  );
}
