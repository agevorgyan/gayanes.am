# SEO Migration Validation Report — Gayane's Kitchen

**Staging Tested:** `http://127.0.0.1:8088` (branch `seo/p0-remediation`)  
**Target Domain:** `https://gayanes.am/`  
**Date:** 2026-11-22

---

## 1. Staging SEO Validation Results (Post-Remediation)

| Check | Result | Evidence |
| :--- | :--- | :--- |
| **Single Canonical per Page** | **PASS** | `180/180` multilingual URLs (`99` products, `45` categories, `24` pages, `12` blog posts) + `/shop/` emit exactly `1` self-referencing `<link rel="canonical">` with matching trailing slashes before `?lang=`. `0` duplicate canonicals. |
| **Hreflang Tags** | **PASS** | `180/180` URLs emit `hy-AM`, `en-AM`, `ru-AM`, and `x-default` with 100% self-referencing and reciprocal links. Omitted on `cart`, `checkout`, `my-account`, `search`, and `404`. |
| **Product Sitemap (`/wp-sitemap-posts-product-1.xml`)** | **PASS** | `HTTP 200`, valid XML, `99` URLs (`33 hy`, `33 ru`, `33 en`). |
| **Category Sitemap (`/wp-sitemap-taxonomies-product_cat-1.xml`)** | **PASS** | `HTTP 200`, valid XML, `45` URLs (`15 hy`, `15 ru`, `15 en`). |
| **Page Sitemap (`/wp-sitemap-posts-page-1.xml`)** | **PASS** | `HTTP 200`, valid XML, `30` URLs (`0` transactional pages — `/cart/`, `/checkout/`, `/my-account/` excluded). |
| **User Sitemap & Legacy Sitemap** | **PASS** | `/wp-sitemap-users-1.xml` → `HTTP 404` (disabled). `/sitemap_index.xml` → `HTTP 301` → `/wp-sitemap.xml`. |
| **Robots Directives** | **PASS** | Commercial pages emit `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`. `/cart/`, `/checkout/`, `/my-account/`, `/?s=` emit `noindex, follow`. `404` emits `noindex, nofollow`. |
| **Structured Data (`JSON-LD`)** | **PASS** | `99/99` products emit `Product` schema with `SKU`, `price`, `InStock`, and `image` (`wc_placeholder_img_src('large')` fallback when featured image is missing), plus `gtin` when populated. |
| **No Staging URLs in HTML/Sitemap** | **PASS** | `0` occurrences of `new.gayanes.am` in rendered HTML or sitemaps. |

## 2. Redirect Mapping Prepared (Inactive Until Cutover)
- `https://new.gayanes.am/(.*)` → `https://gayanes.am/$1` (`HTTP 301`, preserving path & `?lang=` query string)
- `https://www.gayanes.am/(.*)` → `https://gayanes.am/$1` (`HTTP 301`, already active)
- `https://khohanots.am/(.*)` → `https://gayanes.am/$1` (`HTTP 301`, already active)
- `/sitemap_index.xml` → `/wp-sitemap.xml` (`HTTP 301`, active in WordPress)
