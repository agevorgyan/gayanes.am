# WooCommerce Functional Validation Report — Staging (`http://127.0.0.1:8088`)

**Date:** 2026-11-22  
**Branch:** `seo/p0-remediation`

---

## Functional Checks Observed

| Flow / Endpoint | Tested Path | Result | Notes |
| :--- | :--- | :--- | :--- |
| **Homepage (`hy`, `ru`, `en`)** | `/`, `/?lang=ru`, `/?lang=en` | **PASS (`HTTP 200`)** | `0` `new.gayanes.am` URLs; `hy-AM`/`ru-AM`/`en-AM`/`x-default` hreflang. |
| **Shop & Product Search** | `/shop/`, `/?s=dolma` | **PASS (`HTTP 200`)** | Search results render cleanly; `noindex, follow` on `/?s=`. |
| **Products (`99` URLs)** | All `99` URLs in `wp-sitemap-posts-product-1.xml` | **PASS (`99/99 HTTP 200`)** | Valid `Product` schema (`99/99` with `image`), `SKU`, `price`, `InStock` availability. |
| **Categories (`45` terms)** | `15` `hy` + `15` `ru` + `15` `en` | **PASS (`45/45 HTTP 200`)** | All `45` represented in `wp-sitemap-taxonomies-product_cat-1.xml`. |
| **Blog Posts (`12` URLs)** | `4` `hy` + `4` `ru` + `4` `en` | **PASS (`12/12 HTTP 200`)** | `Article` JSON-LD and editorial template render cleanly. |
| **Add to Cart & Cart** | `/?add-to-cart=134`, `/cart/` | **PASS (`HTTP 200`)** | Item added cleanly; `/cart/` emits `noindex, follow` and is excluded from sitemaps. |
| **Checkout** | `/checkout/` (with item `134`) | **PASS (`HTTP 200`)** | Checkout form renders; `Cache-Control: no-cache, must-revalidate, max-age=0, private`; `noindex, follow`. |
| **Customer Login / Account** | `/my-account/` | **PASS (`HTTP 200`)** | Account/login form renders; `noindex, follow`. |
| **Payment Callback (`/wc-api/`)** | `/?wc-api=ameria_vpos` | **EXPECTED `HTTP 400` locally** | Returns `400` locally because third-party `ameriabank` plugin files reside on the `new.gayanes.am` server, not in Git. Cache bypass rules verified in [class-performance.php](file:///Users/apple/Projects/gayanes.am/wp-content/plugins/gayanes-core/includes/class-performance.php#L83-L92). |
| **Email & Inventory Config** | `wp_options` | **PASS** | `woocommerce_manage_stock = yes`; `woocommerce_email_from_address = admin@gayanes.am`. |
