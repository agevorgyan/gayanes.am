# P0 Root Cause Analysis — Gayane's Kitchen

**Date:** 2026-11-22  
**Branch:** `seo/p0-remediation`  
**Scope:** Staging investigation & preparation for `https://new.gayanes.am/` → `https://gayanes.am/` (no production changes applied).

---

## SEO-001: Split Live Domains (`khohanots.am`, `gayanes.am`, `new.gayanes.am`)

### Root Cause
- Three domains exist in the ecosystem:
  - `khohanots.am`: `HTTP 301` → `https://gayanes.am/`
  - `gayanes.am`: `HTTP 200` (legacy WordPress installation)
  - `new.gayanes.am`: `HTTP 200` (new `GayanesV2` WordPress + WooCommerce installation, indexable in parallel)
- Theme and plugin code contained hardcoded production domain references (`'https://gayanes.am/'` inside `gv2_output_json_ld_graph()` in `wp-content/themes/GayanesV2/inc/seo.php`), while `new.gayanes.am` was running as a separate live WordPress instance with its own `siteurl`/`home` options.
- Running both `gayanes.am` and `new.gayanes.am` as indexable `HTTP 200` sites splits link equity, creates duplicate content, and risks Google indexing `new.gayanes.am` as the primary domain.

### Exact Code Location & Dependencies
- `wp-content/themes/GayanesV2/inc/seo.php`: `gv2_output_seo_meta_tags()` and `gv2_output_json_ld_graph()`.
- `wp-content/plugins/gayanes-core/includes/class-seo-schema.php`: uses `home_url('/')` (already domain-aware).
- `wp-content/plugins/gemini-multilingual/includes/class-gm-router.php`: `add_lang_to_url()` and `output_hreflang_tags()` rely on `home_url()` / `get_permalink()`.
- WordPress `wp_options` (`siteurl`, `home`) and serialized URLs in `wp_posts` / `wp_postmeta`.

### Proposed Fix
- Make all theme/plugin SEO URL generation strictly domain-aware via `home_url()` / `get_permalink()` (removed hardcoded `'https://gayanes.am/'` in `gv2_output_json_ld_graph()`).
- Keep domain redirects inactive during staging preparation; execute cutover via the migration runbook (`P0-MIGRATION-RISK-REPORT.md`) only after explicit approval.

### Regression Risks
- Prematurely enabling 301 redirects in staging or `.htaccess` before DNS/SSL/DB cutover would break staging QA and risk redirect loops.

### Test Cases
- Verify JSON-LD `@id` / `url` / `canonical` / `hreflang` outputs use `home_url()` (`http://127.0.0.1:8088` locally, `https://gayanes.am` after production cutover).
- Verify zero hardcoded `gayanes.am` or `khohanots.am` `href` links in rendered `/` and `/shop/` HTML.

---

## SEO-002: Duplicate Canonical Tags & Hreflang Gaps

### Root Cause
- Yoast SEO (`wordpress-seo`) is **not** active in the database (`elab2023_newgayanes.sql`); SEO is handled by `GayanesV2/inc/seo.php`, `gayanes-core/includes/class-seo-schema.php`, and `gemini-multilingual/includes/class-gm-router.php`.
- `gv2_output_seo_meta_tags()` in `wp-content/themes/GayanesV2/inc/seo.php` output `<link rel="canonical">`, while WordPress core `rel_canonical()` remained hooked on `wp_head` (priority `10`), producing **two** `<link rel="canonical">` tags on singular pages.
- `gv2_output_seo_meta_tags()` also lacked `is_404()` handling and canonical resolution for WooCommerce shop/taxonomy archives.
- `GM_Router::output_hreflang_tags()` in `wp-content/plugins/gemini-multilingual/includes/class-gm-router.php` output `hreflang` on 404/search/transactional pages and omitted `x-default`.

### Exact Code Location & Dependencies
- `wp-content/themes/GayanesV2/inc/seo.php`: `gv2_seo_init()`, `gv2_output_seo_meta_tags()`.
- `wp-content/plugins/gemini-multilingual/includes/class-gm-router.php`: `GM_Router::output_hreflang_tags()`.

### Proposed Fix
- Remove core `rel_canonical` when `! defined( 'WPSEO_VERSION' )` inside `gv2_seo_init()`.
- Resolve a single canonical URL in `gv2_output_seo_meta_tags()` for front page, shop, taxonomies, and singular pages (with `GM_Router::add_lang_to_url`), and return early on `is_404()`.
- Skip `output_hreflang_tags()` on `is_404()`, `is_search()`, `is_cart()`, `is_checkout()`, `is_account_page()`, and add `x-default` pointing to the default language (`hy`).

### Regression Risks
- If Yoast SEO is later activated, `! defined( 'WPSEO_VERSION' )` preserves Yoast's canonical/robots handling without conflict.

### Test Cases
- Assert `count(<link rel="canonical">) === 1` on `home_hy`, `home_en`, `home_ru`, `shop`, `product_hy`, `product_ru`, `product_en`, `cat_hy`, `cat_ru`, `cat_en`.
- Assert `count(<link rel="canonical">) === 0` on `404`.
- Assert `hreflang` includes `en-US`, `hy`, `ru-RU`, `x-default` on indexable pages and is absent on `cart`, `checkout`, `my-account`, `search`, and `404`.

---

## SEO-003: Indexable WooCommerce Transactional & Utility Pages

### Root Cause
- `gv2_output_seo_meta_tags()` hardcoded `<meta name="robots" content="index, follow, ...">` directly in `wp_head`, without checking WooCommerce transactional pages (`is_cart()`, `is_checkout()`, `is_account_page()`), `is_search()`, `is_404()`, `is_author()`, or query parameters (`add-to-cart`, `orderby`).
- WordPress core `wp_robots` filter also emitted its own `<meta name="robots">` tag, risking conflicting or missing `noindex` directives.

### Exact Code Location & Dependencies
- `wp-content/themes/GayanesV2/inc/seo.php`: `gv2_seo_init()`, `gv2_filter_wp_robots()`, `gv2_output_seo_meta_tags()`.

### Proposed Fix
- Remove the hardcoded `<meta name="robots">` from `gv2_output_seo_meta_tags()`.
- Hook `gv2_filter_wp_robots` onto WordPress core `wp_robots` (priority `20`) when `! defined( 'WPSEO_VERSION' )`:
  - `is_404()` → `noindex, nofollow`
  - `is_cart()`, `is_checkout()`, `is_account_page()`, `is_search()`, `is_author()`, or `isset( $_GET['add-to-cart'] )` / `isset( $_GET['orderby'] )` → `noindex, follow`
  - All other commercial/indexable pages → `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`

### Regression Risks
- Ensure WooCommerce `is_shop()` and `is_product_category()` are never matched as transactional; only `is_cart()`, `is_checkout()`, `is_account_page()` are noindexed.

### Test Cases
- `/cart/`, `/checkout/` (with item in cart), `/my-account/`, `/?s=dolma` → `noindex, follow`.
- `/non-existent-seo-test-404/` → `noindex, nofollow`.
- `/`, `/shop/`, `/product/...`, `/product-category/...` → `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`.

---

## SEO-004: Broken `/sitemap_index.xml`, User Sitemap Exposure, and Missing Languages

### Root Cause
- Yoast SEO is inactive, so `/sitemap_index.xml` returned `404`, while WordPress core serves `/wp-sitemap.xml`.
- WordPress core exposed `/wp-sitemap-users-1.xml` (author/user enumeration).
- `GM_Router::filter_frontend_query()` filtered `WP_Query` by default language (`hy`), causing `/wp-sitemap-posts-product-1.xml` to omit Russian (`ru`) and English (`en`) products unless `gm_skip_lang_filter` was set.
- Note from staging validation: registering `wp_sitemaps_add_provider` inside `gv2_seo_init()` (at `init:10`) ran too late because `wp_sitemaps_get_server()` also initializes at `init:10`. Moving `wp_sitemaps_add_provider` to top-level (before `init:10`) resolved this so `/wp-sitemap-users-1.xml` returns `404`.

### Exact Code Location & Dependencies
- `wp-content/themes/GayanesV2/inc/seo.php`: top-level `wp_sitemaps_add_provider` and `wp_sitemaps_posts_query_args` filters, plus `gv2_redirect_legacy_sitemap_index()` on `template_redirect` (priority `1`).
- `wp-content/plugins/gemini-multilingual/includes/class-gm-router.php`: respects `$query->get('gm_skip_lang_filter')`.

### Proposed Fix
- Disable `'users'` sitemap provider via top-level `add_filter( 'wp_sitemaps_add_provider', ... )`.
- Set `$args['gm_skip_lang_filter'] = true` via `wp_sitemaps_posts_query_args` so all published `hy`, `ru`, and `en` products appear in `wp-sitemap-posts-product-1.xml`.
- Redirect `/sitemap_index.xml` → `home_url( '/wp-sitemap.xml' )` with `301` on `template_redirect`.

### Regression Risks
- `gm_skip_lang_filter` is scoped strictly to `wp_sitemaps_posts_query_args`, so frontend shop/category language filtering is unaffected.

### Test Cases
- `/sitemap_index.xml` → `HTTP 301` to `/wp-sitemap.xml`.
- `/wp-sitemap-users-1.xml` → `HTTP 404`.
- `/wp-sitemap.xml` → valid XML with 7 sitemaps (no users sitemap).
- `/wp-sitemap-posts-product-1.xml` → valid XML containing all 99 published products (`33 hy`, `33 ru`, `33 en`).
