"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useCart } from "@/lib/cart-context";

export function AddToCartButton({ slug }: { slug: string }) {
  const { addItem } = useCart();
  const [added, setAdded] = useState(false);
  const router = useRouter();

  return (
    <div className="flex flex-col gap-3 sm:flex-row">
      <button
        onClick={() => {
          addItem(slug);
          setAdded(true);
        }}
        className="rounded-full bg-foreground px-6 py-3 text-sm font-medium text-background transition-colors hover:bg-[#383838] dark:hover:bg-[#ccc]"
      >
        {added ? "Zum Warenkorb hinzugefügt ✓" : "In den Warenkorb"}
      </button>
      {added && (
        <button
          onClick={() => router.push("/warenkorb")}
          className="rounded-full border border-black/10 px-6 py-3 text-sm font-medium transition-colors hover:bg-black/[.04] dark:border-white/15 dark:hover:bg-white/[.06]"
        >
          Zum Warenkorb
        </button>
      )}
    </div>
  );
}
