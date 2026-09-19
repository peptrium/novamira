"use client";

import Link from "next/link";
import { useCart } from "@/lib/cart-context";

export function Header() {
  const { totalCount } = useCart();

  return (
    <header className="border-b border-black/10 dark:border-white/10">
      <div className="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
        <Link href="/" className="text-lg font-semibold tracking-tight">
          Novamira Coaching
        </Link>
        <nav className="flex items-center gap-6 text-sm">
          <Link href="/#angebote" className="hover:underline">
            Angebote
          </Link>
          <Link href="/kontakt" className="hover:underline">
            Kontakt
          </Link>
          <Link href="/warenkorb" className="hover:underline">
            Warenkorb{totalCount > 0 ? ` (${totalCount})` : ""}
          </Link>
        </nav>
      </div>
    </header>
  );
}
