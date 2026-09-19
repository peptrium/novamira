export type OrderItem = {
  slug: string;
  title: string;
  quantity: number;
  price: number;
};

export type Order = {
  reference: string;
  createdAt: string;
  customer: {
    name: string;
    email: string;
    message?: string;
  };
  items: OrderItem[];
  totalPrice: number;
};

const STORAGE_KEY = "novamira-last-order";

export function generateOrderReference(): string {
  const now = new Date();
  const datePart = now.toISOString().slice(0, 10).replace(/-/g, "");
  const randomPart = Math.random().toString(36).slice(2, 6).toUpperCase();
  return `NM-${datePart}-${randomPart}`;
}

export function saveLastOrder(order: Order) {
  try {
    window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(order));
  } catch {
    // sessionStorage kann in manchen Umgebungen nicht verfügbar sein
  }
}

export function getLastOrder(): Order | null {
  try {
    const raw = window.sessionStorage.getItem(STORAGE_KEY);
    return raw ? (JSON.parse(raw) as Order) : null;
  } catch {
    return null;
  }
}
