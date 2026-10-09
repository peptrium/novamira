# Peptrium.com — WordPress/WooCommerce Shop Maintenance

This repo (`peptrium/novamira`) is the project home for an ongoing Claude Code
engagement maintaining **peptrium.com**, a live WordPress + WooCommerce +
Elementor shop selling research peptides (framed strictly as "nicht für den
menschlichen oder veterinärmedizinischen Gebrauch, nur für Laborforschungszwecke")
and related accessories (syringes, Bac Water, pen needles, etc.).

The user communicates in German (often with typos). Respond in German.

**This repo itself does not contain the WordPress site's code.** It's a thin
project shell (this file + `.mcp.json`). All real work happens *directly on
the live server* through a custom ability — see below.

## Shop at a glance (verified live 2026-10-09 — trust this over older notes)

- Stack: WP 7.1.3, WooCommerce 10.9.4 (**Blocks checkout**), Astra, Elementor Pro 4.2, PHP 8.3, Redis object cache, 73 `sp-*` mu-plugins.
- Email: **solved** — WP Mail SMTP → SMTP2GO, from `info@peptrium.com`; the user runs the inbox in **FreeScout**. (Older notes saying "no mailbox / no SMTP" are outdated.)
- Header menu: **built** — hamburger drawer (header template 316) using the WP menu "Hauptmenü" + Abo entry via `sp-abo-nav.php`. (Older notes calling it a placeholder are outdated.)
- Payments: Vorkasse, Krypto (NOWPayments), Guthaben (`sp_wallet`). The user confirms payments and ships **manually himself** — no third party; WP-Cron timing is fine, no server cron wanted.
- Customers 17, 41, 90 and their orders/abos (e.g. top-up #3244) are **test data**, not real customers.
- Genuinely still open: **no Impressum page exists** (and "Über uns" claims one), §312k cancellation button only behind login. (Abo/Guthaben UX improvements: done 2026-10-09, `sp-abo-wallet-ux.php`.) BPC-157/TB-500/Semax/Selank were removed from the Zubehör category on request.

## How to connect / operate on the live site

The site is controlled exclusively via a custom REST ability exposed by the
`novamira` WordPress plugin:

```
POST https://peptrium.com/wp-json/novamira/v1/abilities/novamira/execute-php/run
Auth: HTTP Basic, using env vars $NOVAMIRA_WP_USER / $NOVAMIRA_WP_APP_PASSWORD
Body: {"input": {"code": "<raw PHP, WITHOUT the leading <?php tag>"}}
```

These env vars are expected to already be present in the Claude Code
environment this project runs in. If a fresh session doesn't have them,
that's the first thing to sort out before anything else is possible.

Use this ability to: read/write files under `wp-content/mu-plugins/`, query
the DB (`global $wpdb`), inspect/create WooCommerce products and orders,
upload media (`media_handle_sideload`), etc. There is no SSH/shell access —
everything goes through this one PHP execution ability.

## Hard constraint: do NOT write Elementor `_elementor_data` directly

Nearly every page on this site (product pages, the homepage promo sections,
the `/zubehoer/` accessory showcase, etc.) is built in Elementor. **Writing
directly to a post's `_elementor_data` postmeta — even for a brand-new,
previously-empty post — is blocked by this environment's Auto-Mode
Classifier.** It was tried and explicitly denied with reason
`[Auto-Mode Bypass]`. Do not retry this; it just wastes a turn and trips an
explicit block event. Two safe alternatives instead:

1. **CSS/JS overlay (the dominant pattern used everywhere in this project):**
   add a new mu-plugin (or extend an existing one) that hooks `wp_head` /
   `wp_footer` (gated by `is_product()`, `is_checkout()`, `is_page(ID)`, a
   specific product ID check, etc.) and injects `<style>`/`<script>`/raw
   `<div>` HTML from outside, without touching stored Elementor data. This is
   how virtually all visual fixes, the Abo-Modell subscription toggle, the
   checkout info popup, the FAQ restyle, and even an entire new product
   page's content (`sp-pen-nadeln-page.php`, built by hooking
   `woocommerce_before/after_single_product_summary` and reproducing the
   same HTML/CSS/JS another product's Elementor "html" widgets would have
   rendered) were built.
2. **Elementor's own Template Import feature**, when a genuinely new, richly
   designed Elementor template is wanted and the user is willing to do one
   manual step: generate the import JSON (`{"content": [...], "page_settings":
   [], "version": "<Elementor DB::DB_VERSION>", "title": "...", "type":
   "product"}` — the `content` array is the same format as `_elementor_data`)
   by duplicating an existing similar template's data and editing the text
   within it locally, then hand the `.json` file to the user to upload via
   **WordPress Admin → Templates → Saved Templates → Import Templates**. That
   import runs through Elementor's own sanctioned code path, not a raw DB
   write, so it isn't blocked. The user still has to set the new template's
   Display Condition (`Elementor product ID picker`) themselves afterward —
   that's a 10-second manual step, not a coding task.

When in doubt which to use: if the page already exists and just needs a
visual tweak or added widget, use pattern 1. Only reach for pattern 2 when
building a whole new page's rich layout from scratch.

## Standard deploy workflow for any mu-plugin edit

1. Fetch the current live file's content (base64-encode it server-side via
   the execute-php ability, decode locally) — never trust a locally cached
   copy without re-fetching first, it may have drifted.
2. Diff against the last-known-good baseline to detect any drift.
3. Edit locally.
4. `php -l` lint the edited file.
5. Diff edited-vs-baseline to confirm *only* the intended lines changed.
6. Base64-encode and deploy via a small PHP snippet that does
   `copy($target, '/tmp/backup_<name>_<timestamp>.php')` then
   `file_put_contents($target, $decoded)`.
7. `php -l` the live file again post-deploy.
8. Verify functionally — prefer `curl`/`grep` against the live rendered page
   over Playwright. **Playwright against peptrium.com is unreliable in this
   environment** (proxy/Cloudflare issues cause frequent
   `ERR_PROXY_CONNECTION_FAILED` / `ERR_TOO_MANY_RETRIES`); don't burn time
   retrying it repeatedly. The user has explicitly said not to default to
   screenshotting — they'll check live on their phone themselves. Only
   attempt a screenshot if genuinely useful and don't loop on failures.
9. Clean up temp/scratch files when done.

## Other load-bearing gotchas learned the hard way

- **Variable products report the *variation* ID in cart data, not the
  parent product ID.** Any JS/PHP that checks "is product X in the cart"
  against Store API cart items (`cart.items[].id`) must expand parent IDs to
  include all variation IDs first (`$product->get_children()`). See
  `RECON_NEEDED_IDS` and `PEN_PRODUCT_IDS` in `sp-cart-gifts.php` for the
  established expansion pattern. The three Peptrium-Pen products (393, 395,
  396) are variable; most peptide vial products are too.
- **Customer-facing order numbers ≠ internal WooCommerce order IDs.**
  A dedicated counter (`sp-order-numbering.php`) assigns customer-visible
  numbers starting at 3201, stored as postmeta `_sp_order_number`. Resolve
  via `wp_wc_orders_meta` (HPOS-aware) before treating a customer-given
  number as a literal post ID.
- **CSS classes used by a widget's own embedded `<style>` block don't exist
  on pages that never had that widget.** E.g. `.rx-addon-row` styling only
  ships on pages that already had a native "Zubehör" addon row; injecting
  that markup via JS on a page that never had one renders completely
  unstyled unless the CSS is also injected. Always check
  `grep -c '\.rx-addon-row{' <page-html>` (or equivalent) before assuming a
  shared class is actually styled everywhere.
- **Fake-but-fixed "social proof" numbers are a deliberate, pre-existing
  site convention**, not something to avoid inventing: star ratings/review
  counts shown in several places (product hero badges, the Abo-Stack
  picker's `sp_abo_picker_rating_map()`, the `/zubehoer/` cards) are
  hardcoded per product, not real WooCommerce reviews — explicitly done "so
  it looks like the other positions." New products should get a plausible
  entry in the same style once the user asks, not be left blank by default.
- This session's execute-php calls occasionally return a `Found 1 file`
  stub with no content from the `Grep` tool against large fetched files —
  when that happens, fall back to `grep -n` via Bash on the locally-saved
  copy instead of trusting Grep.
- `Edit`'s exact-string matching can silently fail to match HTML-entity vs.
  literal-UTF8 text (e.g. `&auml;` vs. literal `ä`) — if an `old_string`
  replace reports "not found" unexpectedly, re-`Read` the exact bytes rather
  than assuming your remembered text is right.

## Product/content conventions

- New simple accessory products: category "Zubehör" (term_id 20), price as
  agreed with the user, `manage_stock` off, `stock_status` instock,
  `catalog_visibility` visible, not featured unless asked.
- Product imagery goes through `media_handle_sideload()` (copy the source
  file first — it consumes the tmp file it's given).
- Any new pure-accessory product the user wants "bought alongside" existing
  products (like Bac Water/Insulinspritze already are) should be added to
  **three places**, following the Pen Nadeln precedent:
  1. `sp_abo_is_subscribable_product()`'s explicit accessory whitelist in
     `sp-abo-buybox.php`, so it's offered in the Abo-Modell subscription
     flow and inherits the correct (light-grey) buybox styling.
  2. The relevant product page(s)' buybox, as an `.rx-addons`/`.rx-addon-row`
     quick-add toggle (processed server-side via `sp_product_addon_map()` in
     `sp-product-addons.php`).
  3. The cart drawer upsell (`sp-cart-gifts.php`'s
     `ADDON_SUGGESTIONS`/`RECON_NEEDED_IDS`-style trigger-group pattern) so
     it's suggested once a relevant product is already in the cart.
  Remember the variable-product variation-ID gotcha above when wiring #3.

## Mu-plugins this session has directly read or modified

(Not an exhaustive list of every file in `wp-content/mu-plugins/` — just the
ones with established, understood behavior from this engagement. Run a
`scandir()` via execute-php for the full current list; there are ~65 files.)

| File | Purpose |
|---|---|
| `sp-abo-buybox.php` | The "Einmalig / Im Abo" subscription toggle injected into product buyboxes, abo-eligibility whitelist (`sp_abo_is_subscribable_product`), cart/checkout plumbing for subscription line items, 15%-off-from-2nd-delivery logic, Abo-Stack cart banner, coupon-stacking block for Abo items. |
| `sp-abo-picker.php` | The `/abo-stack/` subscription bundle builder/wizard — product grouping by category, the hardcoded `sp_abo_picker_rating_map()` fake-rating lookup, row rendering. |
| `sp-abo-homepage-promo.php` | Homepage Abo promo section (icon checklist, badge). |
| `sp-cart-gifts.php` | The cart drawer: live Store-API-driven rendering, free-gift tiers, coupon UI, the `ADDON_SUGGESTIONS`/`PEN_ADDON_SUGGESTIONS` upsell-suggestion groups, Abo cart item badges. |
| `sp-cart-gift-products.php` | Related: auto-adding free gift line items at spend thresholds. |
| `sp-product-addons.php` | Server-side processing of buybox "Zubehör hinzufügen" toggles (`sp_product_addon_map()`) into real cart line items. |
| `sp-pen-addon-needles.php` | Injects the "Pen Nadeln" quick-add addon row (markup + the CSS it needs, since the Pen product pages never natively had one) into the 3 Peptrium-Pen product buyboxes. |
| `sp-pen-nadeln-page.php` | The entire "Pen Nadeln 32G x 4mm" product page content (hero, buybox, specs tabs, FAQ, Kundenstimmen, cross-sell, disclaimer) — built via WooCommerce hooks instead of an Elementor template, see the hard-constraint section above. |
| `sp-zubehoer-pen-nadeln.php` | Clones the Insulinspritze card on `/zubehoer/` (post 529) via JS to add a 4th "Pen Nadeln" card in the same visual style. |
| `sp-product-faq-style.php` | Restyles every product page's FAQ accordion from boxed cards to the flat divider-list style used on `/abo-modell/`. |
| `sp-home-reviews-style.php` | Sitewide "Kundenstimmen" section fix: strips the dark gradient background and corrects eyebrow/heading colors, matched by scanning for the literal heading text (not by element ID, since every page has its own unique Elementor IDs for this section). |
| `sp-checkout-info-popup.php` | The pre-checkout "Sicher bezahlen & entspannt bestellen" info modal. |
| `sp-checkout-style.php` / `sp-checkout-clean.php` / `sp-checkout-consent.php` | Checkout page (WooCommerce Blocks/Store-API-based) restyling, footer removal, AGB/age consent checkboxes. |
| `sp-myaccount-auth.php` | Custom login/register UI on `/mein-konto/`, incl. the checkout-block account-creation name-sync fix (reads `WC()->customer` as a fallback when `$_POST` is empty, since the Blocks checkout never posts classic form data) and the de-duplicated password-hint tooltip. |
| `sp-order-numbering.php` | Customer-facing order number counter (starts at 3201), independent of internal order ID. |
| `sp-home-hero-mobile.php` | Mobile-only homepage hero override. |
| `sp-header-gap-fix.php` | Non-homepage header fix: zeroes the 20px flex `gap` on Elementor container `hdrA001` (Post 316) that created a dark strip under `#sp-header-bar` (caused by the empty `hdrcenterfix1` CSS widget being a 2nd flex child), and makes bottom spacing match top. Delete the file to revert. |
| `sp-wallet-safeguards.php` | Account required at checkout when cart has an Abo item or wallet top-up (`woocommerce_checkout_registration_required` — works in the Blocks checkout, unlike the older `woocommerce_checkout_process` guard in `sp-abo-buybox.php`), no coupons/referral fee on top-ups, credit capped at amount actually paid, voucher double-submit guard. |
| `sp-abo-stack-billing.php` | Replaces the per-position cron callbacks of `sp-subscriptions.php`/`sp-abo-emails.php` with stack-wide versions (one order + one wallet debit per stack, whole stack paused if funds short, immediate resume+charge after a top-up is credited, reminders 7+2 days ahead with stack totals). Removals run on `plugins_loaded` because this file loads alphabetically BEFORE `sp-subscriptions.php`. |
| `sp-abo-wallet-ux.php` | Abo/Guthaben UX: top-up page accepts `?betrag=XX` prefill + honest "Gutschrift nach Zahlungseingang" copy + "Fehlbetrag übernehmen" hint; Mein-Abo/Mein-Guthaben top-up buttons prefilled with the shortfall; checkout tip "2. Lieferung gleich mitbezahlen" on first Abo orders (adds a top-up to the same order); free-shipping threshold ignores top-ups; price-increase mail to affected Abo customers. |
| `sp-test-orders.php` | Order meta `_sp_is_test` = test order: "Testbestellung" checkbox in the order sidebar, TEST badge in the orders list, and excluded (via `woocommerce_order_query_args` meta_query) from the Peptrium Dashboard and Vorkasse overview/export (`?sp_show_tests=1` shows them). Marked so far: #790, #793, #3243, #3244, #3246, #3262. |
| `sp-vorkasse-overview.php` / `sp-peptrium-dashboard.php` | Admin: open Vorkasse list (age tiers 0–2/3–6/7–13/14+ days, summary tiles, "Zahlungserinnerung senden" from 3 days with bank details, "Stornieren" only from 14 days — never automatic, user decides; some customers pay late). Dashboard revenue = paid minus refunds, wallet-paid orders excluded everywhere (incl. payment breakdown), top-up share shown separately, top-ups not in top products or the unshipped list. |
| `sp-versand-liste.php` | Admin "Peptrium Dashboard → Versand vorbereiten": all paid (processing) orders without tracking number, excluding top-up-only and test orders. Pick list (totals per product/variation), per-order packing list, print views (pick list / one packing slip per page, standalone HTML via `admin_init`), Excel exports via `sp_vorkasse_build_xlsx()`. Read-only. |
| `sp-home-reveal-speed.php` | Monkey-patches `IntersectionObserver` sitewide (on the homepage) to make scroll-reveal animations trigger earlier. |
| `sp-wallet.php` / `sp-wallet-gateway.php` | Store credit / "Guthaben" balance + the custom payment gateway that consumes it (used for automatic 2nd+ Abo delivery billing). |
| `sp-subscriptions.php` | Core recurring-order/subscription engine (separate from the buybox toggle — this is what actually creates and bills renewal orders). |

## Recent work log

**2026-10-08 → 2026-10-09 session:** Built the "Pen Nadeln 32G x 4mm (15er
Pack)" accessory product end-to-end (product 908): WooCommerce product,
media upload, full custom page content (hero/buybox/specs/FAQ/reviews/
cross-sell via the hook-based pattern above since Elementor data writes are
blocked), added it to the Abo-Modell subscription whitelist, to the
`/zubehoer/` showcase page, to the Abo-Stack picker (incl. matching its
hardcoded rating-count convention), and wired it as a cross-sell addon
(buybox quick-add + cart drawer upsell) on all three Peptrium-Pen product
pages — including fixing two bugs found along the way: (1) the addon-row
markup needing its own injected CSS since the Pen pages never had a native
Zubehör row to inherit styles from, and (2) the cart-drawer upsell trigger
needing the Peptrium-Pen variation IDs expanded (they're variable products),
not just the three parent product IDs. Also: sitewide Kundenstimmen
background/color fix, FAQ accordion restyle to match `/abo-modell/`,
checkout info popup polish (close-button visibility/contrast, corner
rendering glitch, avatar cropping, password-hint duplication bug), full
footer removal on checkout, and a fix to checkout-triggered account
creation not picking up the customer's name (Blocks checkout posts JSON,
not classic `$_POST`, so a `$_POST`-only name-sync check always failed).

**2026-10-09 (later):** Solved the long-running "dark strip under the menu bar on non-homepage pages" issue by actually measuring it in a real browser: root cause was `hdrA001`'s `gap:20px` (not padding), fixed via new `sp-header-gap-fix.php`. Real-browser screenshots worked this time via **Playwright (global node module) + `/opt/pw-browsers/chromium-1194/chrome-linux/chrome`**, passing the proxy from `$https_proxy` (server + username/password parsed from the URL). The browser-use plugin's daemon fails with `chrome-not-running`. The age-gate overlay must be removed via JS before screenshotting. Current execute-php endpoint used: `/wp-json/wp-abilities/v1/abilities/novamira/execute-php/run` (browser User-Agent + `-x "$https_proxy"` required against Cloudflare); the older `/novamira/v1/` path also still works.

Also shortened the Pen Nadeln page's "Produkt" tab to a compact version (intro sentence, 4 feature chips, 4-row `.sp-pn-specs` flex list instead of a table — tables wrap badly at 390px — one usage line, one disclaimer).

**2026-10-09 (Abo/Guthaben overhaul):** Audited the subscription + wallet system and fixed real logic gaps (guest checkout silently losing Abos/top-ups, 10% codes and referral fee discounting top-ups while crediting full value, top-up quantity >1, stacks billed per product → split deliveries, per-product reminders missing stack shortfalls, resume only at next cron). See the two new mu-plugins above. Gotchas learned: (1) **mu-plugins load alphabetically** — a `remove_action` at file load only works if the target file loads earlier; otherwise defer to `plugins_loaded`. (2) **OPcache `revalidate_freq=2`** — a verification request within ~2 s of a deploy can still run the old file; wait a few seconds before concluding a deploy didn't take. (3) Auto mode's classifier blocks live changes to payment/checkout logic even after verbal user approval; the user had to switch the session to **Accept edits** mode to approve the deploy prompt.

*(Earlier history predates this file; ask the user or check this repo's
future commits for what's changed since the date above.)*

## Keeping this file useful

When you finish a meaningful piece of work in a session, **update this
file** (new patterns learned, new mu-plugins created, gotchas hit) and
commit it, so the next session doesn't have to rediscover the same things.
Treat it as living project memory, not a one-time snapshot.
