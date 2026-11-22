# SEO Optimization Roadmap (Implementation Plan — Awaiting Approval)

**Important:** No code, database, or redirect changes have been made during this audit. Execute only after explicit approval, on a dedicated Git branch with full DB/file backups.

---

## Phase 0 — Emergency & Domain Cutover Prep (Week 1)
1. **Prevent Staging Indexation (`SEO-001`):** On `https://new.gayanes.am/`, enable `blog_public = 0` (`noindex, nofollow`) or HTTP Basic Auth immediately until cutover.
2. **Fix Duplicate Canonicals (`SEO-002`):** In `GayanesV2/inc/seo.php`, call `remove_action( 'wp_head', 'rel_canonical' )` whenever the theme outputs its own canonical tag (or activate and configure Yoast SEO cleanly).
3. **Noindex Transactional & 404 Pages (`SEO-003`, `SEO-004`):**
   - Return `noindex, follow` on `is_cart()`, `is_checkout()`, `is_account_page()`, `is_search()`, and `?orderby=` / `?add-to-cart=` views.
   - Output `noindex, nofollow` and suppress canonical/`hreflang` on `is_404()`.
4. **Disable User Sitemap (`SEO-007`):** Remove `users` provider from `wp_sitemaps_add_provider` so `/author/admin/` is not exposed.

## Phase 1 — Domain Consolidation & Multilingual Sitemaps (Weeks 2–3)
1. **Cutover `new.gayanes.am` → `gayanes.am` (`SEO-001`):**
   - Full backup of `gayanes.am`.
   - Deploy `GayanesV2` + updated DB to `gayanes.am` with serialized-safe `wp search-replace`.
   - Keep `khohanots.am` → `gayanes.am` `301` redirect; add `301` redirect `new.gayanes.am/*` → `https://gayanes.am/$1`.
   - Remove hardcoded `https://gayanes.am/` in `GayanesV2/inc/seo.php` line 148 in favor of `home_url('/')`.
2. **Multilingual URL & Hreflang Upgrade (`SEO-005`, `SEO-006`):**
   - Include RU & EN product/page/category URLs in XML sitemaps.
   - Add `x-default` `hreflang` pointing to the Armenian (`hy`) canonical URL, and standardize `hreflang` codes (`hy-AM`, `ru-AM`, `en-AM` or `hy`, `ru`, `en`).
   - Evaluate migrating `?lang=ru` / `?lang=en` to `/ru/` and `/en/` subdirectories (with `301` redirects from `?lang=`).

## Phase 2 — On-Page, Product Catalog & Structured Data (Weeks 4–6)
1. **Activate & Configure Yoast SEO (or Theme Meta Templates):** Apply the custom SEO titles and meta descriptions from `WOOCOMMERCE-SEO-AUDIT.csv` across all 99 product posts.
2. **Complete Missing Product Assets (`SEO-010`):**
   - Upload real photos for products currently using placeholders.
   - Populate localized `_wp_attachment_image_alt` in `wp_postmeta`.
   - Assign missing SKUs and verify weights requiring business confirmation.
3. **Upgrade JSON-LD Schema (`SEO-008`):**
   - Enhance `Gayanes_Core\SEO_Schema` with `FoodEstablishment` / `LocalBusiness` (Yerevan address, `+374 55 600655`, `openingHoursSpecification`, `areaServed: Yerevan`).
   - Add `weight` (`QuantitativeValue`) to `Product` schema.
4. **Publish Category SEO Content:** Add 150–250 word localized category descriptions + FAQs for all 15 active categories.

## Phase 3 — Performance, Image Optimization & Content Growth (Weeks 7–12)
1. **Performance & CWV (`SEO-009`, `SEO-011`):**
   - Configure LiteSpeed Cache warmer for `/shop/` and all `product_cat` archives (target TTFB `< 0.30s`).
   - Convert `GAA Gayane` `.otf` fonts to `.woff2` subset and preload primary weights.
   - Minify `main.css` and `app.js`; convert product images to WebP/AVIF.
2. **Execute 90-Day Editorial & Local SEO Plan:** Follow `90-DAY-SEO-STRATEGY.md`.
