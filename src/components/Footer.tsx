import Link from "next/link";

export function Footer() {
  return (
    <footer className="border-t border-black/10 dark:border-white/10">
      <div className="mx-auto flex max-w-4xl flex-col gap-3 px-6 py-8 text-sm text-zinc-600 dark:text-zinc-400 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; {new Date().getFullYear()} Novamira Coaching</p>
        <nav className="flex flex-wrap gap-4">
          <Link href="/impressum" className="hover:underline">
            Impressum
          </Link>
          <Link href="/datenschutz" className="hover:underline">
            Datenschutz
          </Link>
          <Link href="/agb" className="hover:underline">
            AGB
          </Link>
          <Link href="/widerruf" className="hover:underline">
            Widerrufsrecht
          </Link>
        </nav>
      </div>
    </footer>
  );
}
