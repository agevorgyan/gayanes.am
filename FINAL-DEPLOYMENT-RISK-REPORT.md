# Final Deployment Risk Report — Gayane's Kitchen

**Audit Date:** 2026-11-22  
**Branch:** `seo/p0-remediation`  
**Overall Readiness Classification:** **NOT READY FOR PRODUCTION**

---

## 1. Blocking Risks (Must Resolve Before Production Cutover)

| ID | Risk | Evidence | Required Remediation |
| :--- | :--- | :--- | :--- |
| **RISK-01 (CRITICAL)** | **Ameriabank vPOS is in Sandbox Mode & Payment Plugins Missing from Git** | `woocommerce_ameria_vpos_settings` has `sandbox=yes`, `base_live=""`, `client_id=""`, `base_test=https://servicestest.ameriabank.am/VPOS/`. `ameriabank`, `hk-idram-payment-gateway`, `fastshift`, and `telcell` plugin directories are absent from local `wp-content/plugins/`. | Sync/verify server payment plugin code, configure live Ameriabank `client_id`/`base_live`/`sandbox=no` at cutover, whitelist `https://gayanes.am/` callback URLs, and test 1 live low-value order + refund. |
| **RISK-02 (CRITICAL)** | **Live `gayanes.am` Production DB Not Reconciled** | Only `elab2023_newgayanes.sql` (`714` total order rows, `44` users, `99` products) was available; live `gayanes.am` DB was not accessible here. | Take live `gayanes.am` DB dump right before cutover, diff orders/customers/SKUs/stock, and merge any delta orders before switching vhost. |

---

## 2. Medium / Non-Blocking SEO & Media Risks

| ID | Risk | Evidence | Recommended Action |
| :--- | :--- | :--- | :--- |
| **RISK-03 (MEDIUM)** | **Hreflang Codes Use `hy`/`en-US`/`ru-RU` Instead of `hy-AM`/`en-AM`/`ru-AM`** | All `180` rows in [MULTILINGUAL-VALIDATION.csv](file:///Users/apple/Projects/gayanes.am/MULTILINGUAL-VALIDATION.csv) emit `en-US\|hy\|ru-RU\|x-default`. | Update `GM_Router::output_hreflang_tags()` language map if strict Armenia-targeted `hy-AM`/`en-AM`/`ru-AM` codes are required for launch. |
| **RISK-04 (MEDIUM)** | **Transactional Pages Listed in `wp-sitemap-posts-page-1.xml`** | `/cart/`, `/checkout/`, and `/my-account/` appear in `wp-sitemap-posts-page-1.xml` while emitting `noindex, follow` (and `/checkout/` `302`s when empty). | Exclude `wc_get_page_id('cart')`, `wc_get_page_id('checkout')`, and `wc_get_page_id('myaccount')` in `wp_sitemaps_posts_query_args` (`post__not_in`). |
| **RISK-05 (MEDIUM)** | **Product Schema Missing Images & GTIN/EAN** | In [PRODUCT-CRAWL-VALIDATION.csv](file:///Users/apple/Projects/gayanes.am/PRODUCT-CRAWL-VALIDATION.csv), `63/99` products have `NO_IMG` in JSON-LD (`12` `404`s locally due to missing uploads) and `99/99` lack `gtin`/`ean`. | Ensure full `wp-content/uploads/` sync from `new.gayanes.am` to `gayanes.am` and populate featured images / GTINs in P1. |
| **RISK-06 (LOW)** | **Page `?lang=` Canonicals Omit Trailing Slash Before `?lang=`** | `post_page` canonicals emit `/delivery?lang=en` while links use `/delivery/?lang=en` (`16` `?lang=ru`/`en` pages in `MULTILINGUAL-VALIDATION.csv`). | Normalize trailing slash in `GM_Router::add_lang_to_url()` so canonicals match `/{slug}/?lang={lang}` consistently. |

---

## 3. Go / No-Go Criteria

Production cutover (`new.gayanes.am` → `https://gayanes.am/`) may proceed **only when**:
1. `RISK-01` (Ameriabank vPOS live config & payment plugin verification) is resolved.
2. `RISK-02` (live `gayanes.am` vs `new.gayanes.am` order/customer reconciliation) is completed.
3. Explicit user approval is granted to execute the [DOMAIN-CUTOVER-CHECKLIST.md](file:///Users/apple/Projects/gayanes.am/DOMAIN-CUTOVER-CHECKLIST.md).
