"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { formatPrice } from "@/lib/coachings";
import { getLastOrder, type Order } from "@/lib/order";

export default function BestellungBestaetigtPage() {
  const [order, setOrder] = useState<Order | null>(null);
  const [checked, setChecked] = useState(false);

  useEffect(() => {
    // Bewusst synchron: liest einmalig den externen sessionStorage-Snapshot
    // nach der Hydration ein (SSR kennt sessionStorage nicht).
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setOrder(getLastOrder());
    setChecked(true);
  }, []);

  if (!checked) return null;

  if (!order) {
    return (
      <div className="mx-auto max-w-xl px-6 py-16 text-center">
        <h1 className="text-2xl font-semibold">Keine Bestellung gefunden</h1>
        <Link href="/" className="mt-4 inline-block text-sm hover:underline">
          Zurück zur Startseite
        </Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-xl px-6 py-16">
      <h1 className="text-2xl font-semibold">Vielen Dank für deine Bestellung!</h1>
      <p className="mt-2 text-zinc-600 dark:text-zinc-400">
        Deine Bestellreferenz: <span className="font-mono font-medium">{order.reference}</span>
      </p>

      <div className="mt-8 rounded-xl border border-black/10 p-4 text-sm dark:border-white/10">
        <p className="font-medium">Bestellte Leistungen</p>
        <ul className="mt-2 flex flex-col gap-1 text-zinc-600 dark:text-zinc-400">
          {order.items.map((item) => (
            <li key={item.slug} className="flex justify-between">
              <span>
                {item.title} &times; {item.quantity}
              </span>
              <span>{formatPrice(item.price * item.quantity)}</span>
            </li>
          ))}
        </ul>
        <div className="mt-3 flex justify-between border-t border-black/10 pt-3 font-semibold dark:border-white/10">
          <span>Gesamt</span>
          <span>{formatPrice(order.totalPrice)}</span>
        </div>
      </div>

      <div className="mt-8 rounded-xl border border-black/10 p-4 text-sm dark:border-white/10">
        <p className="font-medium">Bitte überweise den Betrag an:</p>
        <dl className="mt-2 flex flex-col gap-1 text-zinc-600 dark:text-zinc-400">
          <div className="flex justify-between">
            <dt>Empfänger</dt>
            <dd>[PLATZHALTER: Firmenname]</dd>
          </div>
          <div className="flex justify-between">
            <dt>IBAN</dt>
            <dd>[PLATZHALTER: IBAN]</dd>
          </div>
          <div className="flex justify-between">
            <dt>BIC</dt>
            <dd>[PLATZHALTER: BIC]</dd>
          </div>
          <div className="flex justify-between">
            <dt>Verwendungszweck</dt>
            <dd className="font-mono">{order.reference}</dd>
          </div>
        </dl>
        <p className="mt-3 text-zinc-500 dark:text-zinc-500">
          Dein Termin wird nach Zahlungseingang bestätigt. Du erhältst dazu
          eine separate E-Mail von uns.
        </p>
      </div>

      <Link href="/" className="mt-8 inline-block text-sm hover:underline">
        Zurück zur Startseite
      </Link>
    </div>
  );
}
