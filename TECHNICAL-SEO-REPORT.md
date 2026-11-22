# Technical SEO Audit Report — Gayane's Kitchen

**Target:** `https://new.gayanes.am/` (and codebase in `/Users/apple/Projects/gayanes.am`)  
**Date:** 2026-11-22

---

## 1. Crawlability, `robots.txt` & XML Sitemaps

### `robots.txt` (`https://new.gayanes.am/robots.txt`)
- Currently served dynamically by WordPress/WooCommerce:
  - Disallows `/wp-content/uploads/wc-logs/`, `/woocommerce_transient_files/`, `/woocommerce_uploads/`, `/*?add-to-cart=`, `/*?*add-to-cart=`, `/wp-admin/` (allows `admin-ajax.php`).
  - Declares `Sitemap: https://new.gayanes.am/wp-sitemap.xml`.
- **Gaps:** Should also disallow `/*?s=` / `/search/` and WooCommerce filter parameters if faceted URLs are introduced.

### XML Sitemaps
- `https://new.gayanes.am/sitemap_index.xml` returns **`HTTP/2 404`** (Yoast SEO is not active on the live site).
- `https://new.gayanes.am/wp-sitemap.xml` is active (`HTTP 200`) and lists:
  - `wp-sitemap-posts-post-1.xml`
  - `wp-sitemap-posts-page-1.xml`
  - `wp-sitemap-posts-product-1.xml` (**33 HY products only — RU and EN products are missing!**)
  - `wp-sitemap-taxonomies-category-1.xml`, `product_brand-1.xml`, `product_cat-1.xml`, `product_tag-1.xml`
  - **`wp-sitemap-users-1.xml`** (Exposes `/author/admin/` — should be disabled!)

---

## 2. Canonicals, Indexability & HTTP Status Codes

| Check | Observed State | Status |
|---|---|---|
| HTTPS & non-www redirect | `http://new.gayanes.am` → `301` HTTPS | Pass |
| Duplicate Canonical Tags | Two `<link rel="canonical">` tags in `<head>` (`gv2_output_seo_meta_tags` + WP core `rel_canonical`) | **P0 Fail** |
| 404 Page Robots & Canonical | `HTTP/2 404` on `/sitemap_index.xml` outputs `index, follow`, self-canonical, and `hreflang` | **P0 Fail** |
| Transactional WooCommerce Pages | `/cart/`, `/checkout/`, `/my-account/` output `index, follow` | **P0 Fail** |
| Security Headers | `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` present | Pass |

---

## 3. WooCommerce URL & Parameter Hygiene

- `/product/{slug}/` (HY): Clean, but many Armenian slugs are percent-encoded UTF-8 (`%d5%ad%d5%ab%d5%b6%d5%af%d5%a1%d5%ac%d5%ab-...`). **Do not change existing HY URLs without a 301 redirect map.**
- `/product-category/{slug}/`: Indexable; needs unique category descriptions and `noindex` on `?orderby=` sort parameters.
- `/cart/`, `/checkout/`, `/my-account/`, `/?s=`: Must be `noindex, follow`.
- `?add-to-cart=`: Disallowed in `robots.txt`; also ensure `rel="nofollow"` on any non-AJAX add-to-cart links.

---

## 4. Structured Data (JSON-LD)

- **Active Plugins/Theme:** `Gayanes_Core\SEO_Schema` outputs `Organization`, `WebSite` (with `SearchAction`), `Product` (with `Offer`, `Brand`, `SKU`, `AggregateRating` only when `rating_count > 0`), `Article`, and `BreadcrumbList`. `GayanesV2` also outputs `FAQPage` on homepage/FAQ.
- **Gaps Found:**
  1. `FoodEstablishment` / `LocalBusiness` schema is skipped in `GayanesV2/inc/seo.php` when `Gayanes_Core\SEO_Schema` is active, and `SEO_Schema` only outputs generic `Organization` without Yerevan `PostalAddress`, `telephone`, `openingHoursSpecification`, or `areaServed`.
  2. `Product` schema lacks `weight` (`QuantitativeValue` in grams) and `gtin` (if applicable).

---

## 5. Performance & Core Web Vitals

| Metric / Asset | Measured Value | Target | Notes |
|---|---:|---:|---|
| Homepage TTFB (`https://new.gayanes.am/`) | `0.27s` | `< 0.50s` | Good (LiteSpeed Cache hit) |
| Shop Archive TTFB (`https://new.gayanes.am/shop/`) | `0.86s` | `< 0.50s` | **Needs LiteSpeed Cache warming** |
| Theme CSS (`main.css`) | `152 KB` | `< 60 KB` gzipped | Unminified source CSS |
| Theme JS (`app.js`) | `29 KB` | `< 20 KB` | Deferred (`defer`), vanilla JS (good) |
| Custom Fonts (`assets/fonts/*.otf`) | `12 files (~1 MB)` | WOFF2 subset | OTF format is larger than WOFF2 |

*Note: Real-user CrUX / Lighthouse field LCP, INP, and CLS data require Google Search Console / PageSpeed Insights API access.*

---

## 6. Image SEO

- **Runtime Fallback:** Both `GayanesV2/inc/seo.php` and `Gayanes_Core\SEO_Schema` filter `wp_get_attachment_image_attributes` to inject parent post title if `alt` is empty.
- **Database Gaps:** Several imported products still lack `_wp_attachment_image_alt` in `wp_postmeta` and/or lack a featured product image (`_thumbnail_id`), and filenames are mixed (`cat_pelmeni_...jpg`, `springroll-snkov.jpg`).
- **Recommendation:** Standardize new filenames as `{brand}-{dish-slug}-{lang}.webp` (e.g., `gayanes-khinkali-homemade.webp`), convert uploads to WebP/AVIF via LiteSpeed/Imagify, and populate descriptive localized ALT attributes in `wp_postmeta`.
