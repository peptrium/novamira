"use client";

import { useState, type FormEvent } from "react";
import { useRouter } from "next/navigation";
import { useCart } from "@/lib/cart-context";
import { coachings, formatPrice } from "@/lib/coachings";
import { generateOrderReference, saveLastOrder } from "@/lib/order";

export default function CheckoutPage() {
  const { items, totalPrice, clear } = useCart();
  const router = useRouter();
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");

  if (items.length === 0) {
    return (
      <div className="mx-auto max-w-xl px-6 py-16 text-center">
        <h1 className="text-2xl font-semibold">Dein Warenkorb ist leer</h1>
      </div>
    );
  }

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();

    const orderItems = items.map((item) => {
      const coaching = coachings.find((c) => c.slug === item.slug)!;
      return {
        slug: item.slug,
        title: coaching.title,
        quantity: item.quantity,
        price: coaching.price,
      };
    });

    saveLastOrder({
      reference: generateOrderReference(),
      createdAt: new Date().toISOString(),
      customer: { name, email, message: message || undefined },
      items: orderItems,
      totalPrice,
    });

    clear();
    router.push("/bestellung-bestaetigt");
  };

  return (
    <div className="mx-auto max-w-xl px-6 py-16">
      <h1 className="text-2xl font-semibold">Kasse</h1>

      <div className="mt-6 rounded-xl border border-black/10 p-4 text-sm dark:border-white/10">
        <p className="font-medium">Zusammenfassung</p>
        <ul className="mt-2 flex flex-col gap-1 text-zinc-600 dark:text-zinc-400">
          {items.map((item) => {
            const coaching = coachings.find((c) => c.slug === item.slug);
            if (!coaching) return null;
            return (
              <li key={item.slug} className="flex justify-between">
                <span>
                  {coaching.title} &times; {item.quantity}
                </span>
                <span>{formatPrice(coaching.price * item.quantity)}</span>
              </li>
            );
          })}
        </ul>
        <div className="mt-3 flex justify-between border-t border-black/10 pt-3 font-semibold dark:border-white/10">
          <span>Gesamt</span>
          <span>{formatPrice(totalPrice)}</span>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="mt-8 flex flex-col gap-4">
        <label className="flex flex-col gap-1 text-sm">
          Name
          <input
            required
            value={name}
            onChange={(e) => setName(e.target.value)}
            className="rounded border border-black/15 px-3 py-2 dark:border-white/15 dark:bg-transparent"
          />
        </label>
        <label className="flex flex-col gap-1 text-sm">
          E-Mail
          <input
            required
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="rounded border border-black/15 px-3 py-2 dark:border-white/15 dark:bg-transparent"
          />
        </label>
        <label className="flex flex-col gap-1 text-sm">
          Nachricht (optional)
          <textarea
            value={message}
            onChange={(e) => setMessage(e.target.value)}
            rows={3}
            className="rounded border border-black/15 px-3 py-2 dark:border-white/15 dark:bg-transparent"
          />
        </label>

        <p className="text-sm text-zinc-500 dark:text-zinc-400">
          Nach der Bestellung erhältst du unsere Bankdaten und eine
          Bestellreferenz. Bitte überweise den Betrag unter Angabe der
          Referenz &ndash; dein Termin wird nach Zahlungseingang bestätigt.
        </p>

        <button
          type="submit"
          className="mt-2 rounded-full bg-foreground px-6 py-3 text-sm font-medium text-background hover:bg-[#383838] dark:hover:bg-[#ccc]"
        >
          Kostenpflichtig bestellen
        </button>
      </form>
    </div>
  );
}
