# P0 Validation Results — Gayane's Kitchen

**Environment:** Local staging server `http://127.0.0.1:8088` (`elab2023_newgayanes.sql` database)  
**Branch:** `seo/p0-remediation`  
**Date:** 2026-11-22

---

## 1. PHP Syntax Lint (`php -l`)
- `wp-content/themes/GayanesV2/inc/seo.php`: **PASS** (`No syntax errors detected`)
- `wp-content/plugins/gemini-multilingual/includes/class-gm-router.php`: **PASS** (`No syntax errors detected`)
- `wp-content/themes/GayanesV2/functions.php`: **PASS** (`No syntax errors detected`)

## 2. Sitemap Validation
| Endpoint | Expected | Observed Result | Status |
| :--- | :--- | :--- | :--- |
| `/sitemap_index.xml` | `HTTP 301` → `/wp-sitemap.xml` | `HTTP 301` → `http://127.0.0.1:8088/wp-sitemap.xml` | **PASS** |
| `/wp-sitemap-users-1.xml` | `HTTP 404` (disabled) | `HTTP 404` | **PASS** |
| `/wp-sitemap.xml` | `HTTP 200`, valid XML, no users sitemap | `HTTP 200`, valid XML, 7 sitemaps (`posts-post-1`, `posts-page-1`, `posts-product-1`, `taxonomies-category-1`, `taxonomies-product_brand-1`, `taxonomies-product_cat-1`, `taxonomies-product_tag-1`) | **PASS** |
| `/wp-sitemap-posts-product-1.xml` | `HTTP 200`, valid XML, all `hy`/`ru`/`en` products | `HTTP 200`, valid XML, 99 URLs (`hy=33`, `ru=33`, `en=33`) | **PASS** |
| `/wp-sitemap-taxonomies-product_cat-1.xml` | `HTTP 200`, valid XML | `HTTP 200`, valid XML, 15 URLs (`hy=15`, `ru=0`, `en=0`) | **PASS** (note: RU/EN category query-param sitemaps are a P1 enhancement) |

## 3. Page Canonical, Robots & Hreflang Validation
| Page Case | URL Tested | HTTP | Canonical Count | Robots Directive | Hreflang | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `home_hy` | `/` | `200` | `1` (`http://127.0.0.1:8088/`) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `home_en` | `/?lang=en` | `200` | `1` (`http://127.0.0.1:8088/?lang=en`) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `home_ru` | `/?lang=ru` | `200` | `1` (`http://127.0.0.1:8088/?lang=ru`) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `shop` | `/shop/` | `200` | `1` (`http://127.0.0.1:8088/shop/`) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `product_hy` | `/product/%d5%ad%d5%ab%d5%b6%d5%af%d5%a1%d5%ac%d5%ab-%d5%bf%d5%b6%d5%a1%d5%af%d5%a1%d5%b6-900%d5%a3/` | `200` | `1` (self-referencing) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `product_ru` | `/product/%d1%85%d0%b8%d0%bd%d0%ba%d0%b0%d0%bb%d0%b8-%d0%b4%d0%be%d0%bc%d0%b0%d1%88%d0%bd%d0%b8%d0%b5-900%d0%b3/?lang=ru` | `200` | `1` (self-referencing) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `product_en` | `/product/%d5%bd%d5%ba%d6%80%d5%ab%d5%b6%d5%a3-%d5%bc%d5%b8%d5%ac%d5%ac-%d5%a2%d5%a1%d5%b6%d5%bb%d5%a1%d6%80%d5%a5%d5%b2%d5%a5%d5%b6%d5%b8%d5%be/?lang=en` | `200` | `1` (self-referencing) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `cat_hy` | `/product-category/khinkali/` | `200` | `1` (self-referencing) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `cat_ru` | `/product-category/%d0%b1%d0%bb%d0%b8%d0%bd%d1%87%d0%b8%d0%ba%d0%b8-ru/?lang=ru` | `200` | `1` (self-referencing) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `cat_en` | `/product-category/crepes-en/?lang=en` | `200` | `1` (self-referencing) | `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1` | `en-US,hy,ru-RU,x-default` | **PASS** |
| `cart` | `/cart/` | `200` | `1` (`http://127.0.0.1:8088/cart/`) | `noindex, follow` | omitted | **PASS** |
| `checkout` (empty cart) | `/checkout/` | `302` → `/cart/` | — | — | — | **PASS** (WooCommerce default) |
| `checkout` (with `pid=134` in cart) | `/checkout/` | `200` | `1` (`http://127.0.0.1:8088/checkout/`) | `noindex, follow` | omitted | **PASS** |
| `myaccount` | `/my-account/` | `200` | `1` (`http://127.0.0.1:8088/my-account/`) | `noindex, follow` | omitted | **PASS** |
| `search` | `/?s=dolma` | `200` | `1` (`http://127.0.0.1:8088`) | `noindex, follow` | omitted | **PASS** |
| `notfound` | `/non-existent-seo-test-404/` | `404` | `0` | `noindex, nofollow` | omitted | **PASS** |

## 4. Internal Link & Domain Leak Check (`/` and `/shop/`)
- **Internal links checked:** `72`
- **Broken navigation/product/category links:** `0` (the 4 `404` responses flagged by the raw crawler were 1 protocol-relative `//fonts.googleapis.com` string and 3 missing local `wp-content/uploads/2026/11/cropped-favicon-*.png` assets not stored in git).
- **Hardcoded `gayanes.am` or `khohanots.am` `href` links:** `0`
