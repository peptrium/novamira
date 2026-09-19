export type Coaching = {
  slug: string;
  title: string;
  shortDescription: string;
  description: string[];
  price: number;
  durationMinutes: number;
  format: string;
};

export const coachings: Coaching[] = [
  {
    slug: "einzelcoaching-basis",
    title: "Einzelcoaching Basis",
    shortDescription: "Ein Einstiegsgespräch, um deine Ziele zu klären.",
    description: [
      "In dieser Einzelsession klären wir gemeinsam deine aktuelle Situation und definieren konkrete, erreichbare Ziele.",
      "Du erhältst im Anschluss eine schriftliche Zusammenfassung mit den nächsten Schritten.",
    ],
    price: 89,
    durationMinutes: 60,
    format: "Online (Video-Call)",
  },
  {
    slug: "coaching-paket-3er",
    title: "Coaching-Paket (3 Sessions)",
    shortDescription: "Drei aufeinander aufbauende Sessions für nachhaltige Veränderung.",
    description: [
      "Drei Sessions à 60 Minuten, die inhaltlich aufeinander aufbauen.",
      "Ideal, wenn du an einem konkreten Thema über mehrere Wochen dranbleiben möchtest.",
    ],
    price: 240,
    durationMinutes: 60,
    format: "Online (Video-Call)",
  },
  {
    slug: "intensiv-coaching",
    title: "Intensiv-Coaching (Halbtag)",
    shortDescription: "Ein fokussierter Deep-Dive-Termin für ein einzelnes großes Thema.",
    description: [
      "Ein 3-stündiger Termin, um ein komplexes Thema in der Tiefe zu bearbeiten.",
      "Inklusive Vor- und Nachbereitung per E-Mail.",
    ],
    price: 320,
    durationMinutes: 180,
    format: "Online oder vor Ort nach Absprache",
  },
];

export function getCoachingBySlug(slug: string): Coaching | undefined {
  return coachings.find((c) => c.slug === slug);
}

export function formatPrice(price: number): string {
  return new Intl.NumberFormat("de-DE", {
    style: "currency",
    currency: "EUR",
  }).format(price);
}
