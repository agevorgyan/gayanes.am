# P0 Migration Risk Report & Production Cutover Checklists — Gayane's Kitchen

**Date:** 2026-11-22  
**Target Architecture:** Primary production domain `https://gayanes.am/` (replacing legacy `gayanes.am` with the `GayanesV2` codebase from `new.gayanes.am`, and `301` redirecting `new.gayanes.am` → `gayanes.am` at cutover).  
**Status:** Preparation only — **do not execute domain cutover without explicit approval**.

---

## 1. Risk Assessment Matrix

| Risk | Severity | Mitigation |
| :--- | :--- | :--- |
| **Split indexing / duplicate content** between `gayanes.am` and `new.gayanes.am` | High | At cutover, configure server-level `301` redirect from `new.gayanes.am/*` → `https://gayanes.am/$1`, and keep `wp_options` `siteurl`/`home` set to `https://gayanes.am`. |
| **WooCommerce order & customer loss** during DB cutover | Critical | Never overwrite live `gayanes.am` orders if any were placed after the `new.gayanes.am` DB snapshot; freeze checkout or export/reconcile delta orders/users before switching DNS/docroot. |
| **Payment gateway (Idram / ArCa / Ameriabank / etc.) callback failures** | Critical | Callback URLs and merchant whitelist settings must be updated/verified for `https://gayanes.am/` before opening live checkout. |
| **Broken translated URLs (`?lang=ru`, `?lang=en`)** | Medium | Preserve all `post_id`s, `_gm_lang` / `_gm_trid` postmeta, and `product_cat` slugs (`-ru`, `-en`). |
| **Taxonomy sitemap language coverage** | Low | `wp-sitemap-taxonomies-product_cat-1.xml` currently lists 15 `hy` category URLs (`ru=0, en=0`) because WP core term sitemaps do not append `?lang=`. Non-blocking for P0; can be enhanced in P1 if desired. |

---

## 2. Old-to-New URL Mapping (Pre-Cutover Reference)

| Resource Type | Legacy / Staging Pattern | Target Production Pattern (`https://gayanes.am`) | Action at Cutover |
| :--- | :--- | :--- | :--- |
| Homepage (`hy`) | `https://new.gayanes.am/` | `https://gayanes.am/` | 301 `new.gayanes.am` → `gayanes.am` |
| Homepage (`ru` / `en`) | `https://new.gayanes.am/?lang={ru\|en}` | `https://gayanes.am/?lang={ru\|en}` | 301 preserving query string |
| Shop | `https://new.gayanes.am/shop/` | `https://gayanes.am/shop/` | 301 preserving path |
| Products (`hy`/`ru`/`en`) | `https://new.gayanes.am/product/<slug>/[?lang={ru\|en}]` | `https://gayanes.am/product/<slug>/[?lang={ru\|en}]` | 301 preserving path & query |
| Categories | `https://new.gayanes.am/product-category/<slug>/[?lang={ru\|en}]` | `https://gayanes.am/product-category/<slug>/[?lang={ru\|en}]` | 301 preserving path & query |
| Legacy Sitemap | `https://new.gayanes.am/sitemap_index.xml` | `https://gayanes.am/wp-sitemap.xml` | 301 handled in WordPress (`gv2_redirect_legacy_sitemap_index`) |
| `khohanots.am` | `http(s)://khohanots.am/*` | `https://gayanes.am/*` | Keep existing 301 |

---

## 3. Database Migration Checklist
- [ ] Take a full backup (`mysqldump`) of **both** `gayanes.am` and `new.gayanes.am` databases immediately prior to maintenance window.
- [ ] Export/verify all orders, customers, and `wc_orders` / `woocommerce_order_items` placed on legacy `gayanes.am` since the `new.gayanes.am` snapshot.
- [ ] Run serialized-safe search-replace (`wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables --dry-run`, then live).
- [ ] Verify `wp_options` (`siteurl` and `home`) both equal `https://gayanes.am`.
- [ ] Flush WordPress rewrite rules (`wp rewrite flush --hard`) and object/page caches.

## 4. DNS & SSL Checklist
- [ ] Lower DNS TTL for `gayanes.am`, `www.gayanes.am`, and `new.gayanes.am` to `300s` at least 24 hours before cutover.
- [ ] Confirm valid SSL certificates (SAN / Let's Encrypt) cover `gayanes.am`, `www.gayanes.am`, and `new.gayanes.am` so HTTPS redirects do not fail TLS handshake.
- [ ] Point `gayanes.am` vhost / docroot to the `GayanesV2` codebase.
- [ ] Configure server-level `301` redirect: `www.gayanes.am` → `https://gayanes.am` and `new.gayanes.am` → `https://gayanes.am` (preserving URI & query string).

## 5. WooCommerce Order Preservation Checklist
- [ ] Enable brief maintenance mode on both sites during final delta sync.
- [ ] Verify total published products count (99: 33 `hy`, 33 `ru`, 33 `en`), SKUs, prices, and stock statuses match pre-cutover counts.
- [ ] Verify recent order IDs, customer accounts, and FrontPad sync (`gayanes-frontpad`) keys remain intact.

## 6. Payment Integration Testing Checklist
- [ ] Verify merchant callback / return / webhook URLs are configured for `https://gayanes.am/` (not `new.gayanes.am`).
- [ ] Run one live low-value end-to-end checkout test on `https://gayanes.am/checkout/` after cutover and confirm order status transitions cleanly.
- [ ] Verify `/cart/`, `/checkout/`, and `/my-account/` still emit `noindex, follow` on production.
