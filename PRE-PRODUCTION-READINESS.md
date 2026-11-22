# Pre-Production Readiness Audit — Gayane's Kitchen

**Project:** Gayane's Kitchen (`https://gayanes.am/`)  
**Staging Audited:** `http://127.0.0.1:8088` (Git branch: `seo/p0-remediation`)  
**Audit Date:** 2026-11-22  
**Audit Mode:** Read-only (no production changes, no redirects activated, no DNS/DB modifications)

---

## Final Decision: **NOT READY FOR PRODUCTION**

While the P0 technical SEO remediation (`seo/p0-remediation`) passed staging validation across all 99 products, 45 category terms, and core sitemaps, **three hard production blockers** prevent immediate cutover:

1. **Payment Gateway in Sandbox Mode & Plugin Code Absent from Git (`CRITICAL`)**
   - `woocommerce_ameria_vpos_settings` in `elab2023_newgayanes.sql` has `sandbox: yes`, `base_live: ""` (empty), `client_id: ""` (empty), and `base_test: https://servicestest.ameriabank.am/VPOS/`.
   - The active payment gateway plugins (`ameriabank/ameriabank.php`, `hk-idram-payment-gateway`, `payment-gateway-for-fastshift`, `payment-gateway-for-telcell`) are referenced in `wp_options.active_plugins` but are **not present in the local repository** (`wp-content/plugins/`), so live callback/webhook/refund behavior cannot be code-verified locally.
2. **No Live Production Database Snapshot for Reconciliation (`CRITICAL`)**
   - The legacy `https://gayanes.am/` production database was not available in this environment (`dist/localhost-backup-before-import.sql` is only a local test snapshot with 2 orders and 1 user). Order/customer delta reconciliation against `elab2023_newgayanes.sql` (152 completed, 11 processing, 6 on-hold, 1 pending, 6 cancelled, 2 failed, 536 trashed orders; 44 users) has not yet been executed.
3. **Hreflang Region Code & Sitemap Cleanup Gaps (`MEDIUM` / `NON-BLOCKING` or `FIX BEFORE LAUNCH`)**
   - `GM_Router::output_hreflang_tags()` currently emits `hy`, `en-US`, `ru-RU`, and `x-default` instead of the target `hy-AM`, `en-AM`, `ru-AM`, and `x-default`.
   - `wp-sitemap-posts-page-1.xml` still includes `/cart/`, `/checkout/`, and `/my-account/` (which emit `noindex, follow` / `302`).
   - All `99/99` products lack `gtin`/`ean` in Product schema, and `63/99` products reported `NO_IMG` in Product schema JSON-LD (plus `12` image `404`s and `3` `0` statuses in local staging due to missing `wp-content/uploads/` files).

---

## Audit Scorecards by Area

| Audit Area | Evidence File | Status | Key Findings |
| :--- | :--- | :--- | :--- |
| **1. Multilingual SEO** | [MULTILINGUAL-VALIDATION.csv](file:///Users/apple/Projects/gayanes.am/MULTILINGUAL-VALIDATION.csv) | **PASS (with note)** | `99/99` products, `45/45` categories, `12/12` posts return `HTTP 200`, valid `canonical`, `self_ref`, `reciprocal`, and `x-default`. Emits `hy`/`en-US`/`ru-RU` instead of `hy-AM`/`en-AM`/`ru-AM`. Page `?lang=` canonicals omit trailing slash before `?lang=`. |
| **2. Product Crawl (99 URLs)** | [PRODUCT-CRAWL-VALIDATION.csv](file:///Users/apple/Projects/gayanes.am/PRODUCT-CRAWL-VALIDATION.csv) | **PASS (with media/GTIN gaps)** | `99/99` `HTTP 200`, `canonical_match=YES`, `has_product_schema=YES`, `SKU` & `price` 100% present, `InStock` 100%, `meta_description` 100%. `99/99` missing GTIN/EAN; `63/99` `NO_IMG` in JSON-LD; `12` image `404`s locally. |
| **3. Website & Sitemap Crawl** | [PRE-PRODUCTION-READINESS.md](file:///Users/apple/Projects/gayanes.am/PRE-PRODUCTION-READINESS.md) | **PASS (with sitemap note)** | `67/68` non-product sitemap URLs return `HTTP 200`; `/checkout/` `302`s to `/cart/` when empty. `cart`, `checkout`, `my-account` are still listed in `wp-sitemap-posts-page-1.xml` despite `noindex, follow`. |
| **4. Database Reconciliation** | [DATABASE-RECONCILIATION-REPORT.md](file:///Users/apple/Projects/gayanes.am/DATABASE-RECONCILIATION-REPORT.md) | **BLOCKED (needs live prod DB)** | `elab2023_newgayanes.sql` metrics captured; live `gayanes.am` DB dump required for order/customer delta check. |
| **5. Payment Gateway (Ameriabank)** | [PAYMENT-GATEWAY-READINESS.md](file:///Users/apple/Projects/gayanes.am/PAYMENT-GATEWAY-READINESS.md) | **NOT READY** | `sandbox: yes`, `base_live` empty, `client_id` empty, and plugin source absent from repo. |
| **6. Domain & Infrastructure** | [DOMAIN-CUTOVER-CHECKLIST.md](file:///Users/apple/Projects/gayanes.am/DOMAIN-CUTOVER-CHECKLIST.md) | **READY FOR SCHEDULED CUTOVER** | DNS (`51.38.26.56`), Let's Encrypt SSL SANs, HTTPS, and `www` → apex `301` verified on all 4 domains. |
