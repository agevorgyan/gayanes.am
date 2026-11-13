# Phase 1 — Audit & Risk Assessment Report (`gayanes.am`)

## 1. Live Site External Audit Summary

| Area | Observed State | Risk / Impact | Mitigation in New Architecture |
| :--- | :--- | :--- | :--- |
| **Platform** | WordPress + WooCommerce (Store API active) | High business data sensitivity (products, orders, customers, coupons) | Zero DB resets; custom theme + `gayanes-core` plugin operate strictly via WooCommerce APIs/hooks |
| **Current Theme** | `flatsome` + `flatsome-child` (~171.8 KB HTML) | Heavy page-builder DOM, slow mobile LCP/INP | Replaced with lightweight custom `gayanes-theme` (zero page builders, zero jQuery) |
| **Multilingual** | Polylang (`hy` at `/`, `ru` at `/ru/...`) | Missing English (`/en/`); risk of broken URLs or lost translations | `Gayanes_Core\Multilingual` integrates with Polylang (+ WPML fallback), adds `/en/`, preserves product/category links |
| **Payment Gateways** | Idram, ArCa, Ameriabank, Telcell | Caching or checkout DOM changes can break callbacks/redirects | Preserve all WooCommerce checkout hooks; enforce `DONOTCACHEPAGE` + `no-store` on `/wc-api/*`, cart, checkout, account |
| **Caching / Server** | LiteSpeed + LiteSpeed Cache + HTTP/3 | Stale cart badge/card quantities on cached pages | `cart.js` hydrates fresh cart state via `gayanes_get_cart_state` on page load; dynamic routes excluded from cache |
| **SEO** | Yoast SEO (`sitemap.xml`, `news-sitemap.xml`) | Risk of duplicate meta/schema or broken indexed URLs | `SEO_Schema` suppresses duplicate meta when Yoast is active; outputs Product, Organization, WebSite, BreadcrumbList, Article JSON-LD |
| **Security** | `all-in-one-wp-security-and-firewall` | AJAX endpoints must resist CSRF/XSS/stock bypass | Nonce verification (`gayanes_cart_nonce`), input sanitization, output escaping, server-side `WC()->cart` validation |

## 2. Preserved Data & Entities

All existing store data remains untouched in the WordPress/WooCommerce database:
- Product IDs, SKUs, prices, sale prices, weights, stock, categories, and images
- Customer accounts, order history, and active orders
- Payment gateways (Idram, ArCa, Ameriabank, Telcell)
- Shipping zones, rates, and discount coupons

