# Gayane's Kitchen — Full SEO Audit & Optimization Report (Audit-Only Mode)

**Date:** 2026-11-22  
**Audited Live Targets:** `https://new.gayanes.am/`, `https://gayanes.am/`, `https://khohanots.am/`  
**Audited Codebase & Catalog:** WordPress + WooCommerce (`GayanesV2` v2.1.17, `gayanes-core` v1.0.0, `gemini-multilingual` v1.0.0, `gayanes-frontpad` v1.0.0, `gayanes-qty-rules` v1.0.0)  
**Mode:** Audit-Only (zero production changes made)

---

## 1. Executive Summary

Gayane's Kitchen (`Գայանեի Խոհանոց`) has a modern, mobile-first custom WooCommerce storefront (`GayanesV2`) with clean JSON-LD Product/Breadcrumb/FAQ markup, structured multilingual product descriptions across **99 published product posts** (33 products × `hy`, `ru`, `en`), and fast LiteSpeed caching on the homepage (`0.27s` TTFB).

However, **4 P0 (Critical) issues** currently block safe organic growth and risk severe indexation/canonicalization damage:
1. **Split Live Domains (`SEO-001`):** Both `https://gayanes.am/` (legacy site) and `https://new.gayanes.am/` (new site) return `HTTP/2 200` and are simultaneously indexable (`index, follow`).
2. **Duplicate Canonical Tags (`SEO-002`):** `https://new.gayanes.am/` outputs **two** `<link rel="canonical">` tags on every page (`GayanesV2/inc/seo.php` + WordPress core `rel_canonical()`) because Yoast SEO is not active.
3. **Transactional Pages Indexable (`SEO-003`):** `/cart/`, `/checkout/`, and `/my-account/` return `index, follow`.
4. **404 Pages Marked Indexable (`SEO-004`):** Nonexistent URLs (e.g., `/sitemap_index.xml`) return `HTTP/2 404` alongside `<meta name="robots" content="index, follow">`, a self-referencing canonical, and `hreflang` tags.

---

## 2. Weighted SEO Score (62 / 100)

| Category | Weight | Score | Key Evidence & Gaps |
|---|---:|---:|---|
| **Technical SEO** | 20 | **11 / 20** | Fast LiteSpeed HTTP/2 & security headers, but duplicate canonicals, 404 `index, follow`, exposed `/wp-sitemap-users-1.xml`, and split `gayanes.am` / `new.gayanes.am` domains. |
| **On-Page SEO** | 15 | **10 / 15** | Clean H1s and structured 6-part product descriptions, but Yoast SEO is inactive (missing custom meta titles/descriptions per product & category). |
| **WooCommerce SEO** | 15 | **10 / 15** | Clean prices (AMD), `Gayane's Kitchen` brand, grams `_weight`, and `min_qty` rules; missing featured images/SKUs on several imported items and `noindex` on cart/checkout. |
| **Content SEO** | 10 | **5 / 10** | Strong product descriptions, but empty category SEO descriptions and minimal blog/recipe content. |
| **Indexation** | 10 | **4 / 10** | Both `gayanes.am` and `new.gayanes.am` are live & indexable; `/cart/` is indexable; RU/EN products are missing from `wp-sitemap-posts-product-1.xml`. |
| **Performance** | 10 | **7 / 10** | Cached homepage TTFB is `0.27s`, but `/shop/` TTFB is `0.86s`, `main.css` (`152 KB`) and `app.js` (`29 KB`) are unminified, and 12 `.otf` fonts (`~1 MB` total) are used instead of `.woff2`. |
| **Multilingual SEO** | 10 | **6 / 10** | Full 33-product & 15-category translation coverage (`hy`, `ru`, `en`), but uses `?lang=` query parameters, lacks `x-default`, uses `en-US`/`ru-RU` instead of `en-AM`/`ru-AM`, and omits RU/EN from XML sitemaps. |
| **Local SEO** | 5 | **3 / 5** | Footer & FAQ have Yerevan address/phones, but `FoodEstablishment` / `LocalBusiness` JSON-LD is suppressed when `Gayanes_Core\SEO_Schema` is active. |
| **Structured Data** | 5 | **4 / 5** | `Organization`, `WebSite`, `Product` (with `Offer`, `Brand`, `SKU`), `BreadcrumbList`, and `FAQPage` are present; missing `LocalBusiness` and `gtin` (if applicable). |
| **TOTAL** | **100** | **62 / 100** | **Actionable path to 88–92 / 100 after Phase 0–2 fixes.** |

---

## 3. Data Sources & Limitations

- **Verified Data Sources Used:** Live HTTP/HTTPS probes (`curl`) of `khohanots.am`, `gayanes.am`, and `new.gayanes.am`; full codebase inspection; live WooCommerce database export (`99` published products, `16` product categories, `4` tags, `1` brand, `21` pages/posts).
- **Unavailable Data Sources (Explicitly Documented):** Google Search Console (GSC) coverage/queries/clicks, Google Analytics 4 (GA4), Google Business Profile (GBP) insights, and paid third-party backlink/keyword volume tools (Ahrefs/SEMrush) were not connected in this workspace. Search demand tiers in `KEYWORD-RESEARCH.csv` are qualitative estimates based on Armenian/Russian/English food delivery market structure.

---

## 4. Deliverable Index

1. `GAYANES-SEO-AUDIT.md` — Executive summary & weighted scorecard (this document)
2. `DOMAIN-MIGRATION-AUDIT.md` — `khohanots.am` → `gayanes.am` → `new.gayanes.am` investigation & cutover plan
3. `TECHNICAL-SEO-REPORT.md` — Crawl, robots.txt, sitemaps, canonicals, WooCommerce URLs, CWV, and Image SEO
4. `WOOCOMMERCE-SEO-AUDIT.csv` — Product-by-product audit & SEO recommendations for all 99 published products
5. `MULTILINGUAL-SEO-AUDIT.csv` — Multilingual audit across all products, categories, pages, and posts
6. `KEYWORD-RESEARCH.csv` — Independent HY / RU / EN keyword clusters mapped to target URLs
7. `CONTENT-GAP-ANALYSIS.md` — Category SEO, Blog/Recipe clusters, and 5-competitor analysis
8. `SEO-ISSUES.csv` — Prioritized P0–P2 issue register with evidence and validation procedures
9. `SEO-OPTIMIZATION-ROADMAP.md` — Phased technical & on-page implementation plan (awaiting approval)
10. `90-DAY-SEO-STRATEGY.md` — 3-month editorial calendar, Local SEO, and conversion-oriented SEO plan
