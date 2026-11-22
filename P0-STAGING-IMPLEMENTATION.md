# P0 Staging Implementation — Gayane's Kitchen

**Branch:** `seo/p0-remediation`  
**Date:** 2026-11-22  
**Status:** Implemented and validated on local staging (`http://127.0.0.1:8088`). No production files, database, DNS, or hosting configuration were modified.

---

## Implemented Changes

1. **Single Canonical Generation & Domain-Aware URLs (`SEO-001`, `SEO-002`)**
   - In [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php):
     - Removed WordPress core `rel_canonical` inside `gv2_seo_init()` when `! defined( 'WPSEO_VERSION' )`.
     - Updated `gv2_output_seo_meta_tags()` to return early on `is_404()` and compute a single canonical URL for the front page, WooCommerce shop (`wc_get_page_id( 'shop' )`), taxonomy archives (`get_term_link`), and singular pages (`get_permalink`), applying `GM_Router::add_lang_to_url` when available.
     - Replaced hardcoded `'https://gayanes.am/'` in `gv2_output_json_ld_graph()` with `$site_url` (`home_url( '/' )`).

2. **WooCommerce Transactional & Utility Page `noindex` (`SEO-003`)**
   - In [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php):
     - Removed hardcoded `<meta name="robots">` from `gv2_output_seo_meta_tags()`.
     - Added `gv2_filter_wp_robots()` hooked to `wp_robots` (priority `20`) when `! defined( 'WPSEO_VERSION' )`:
       - `is_404()` → `noindex, nofollow`
       - `is_cart()`, `is_checkout()`, `is_account_page()`, `is_search()`, `is_author()`, `?add-to-cart=`, or `?orderby=` → `noindex, follow`
       - All other pages → `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`

3. **Hreflang Hardening (`SEO-002`, `SEO-003`)**
   - In [class-gm-router.php](file:///Users/apple/Projects/gayanes.am/wp-content/plugins/gemini-multilingual/includes/class-gm-router.php):
     - Updated `GM_Router::output_hreflang_tags()` to skip `is_404()`, `is_search()`, `is_cart()`, `is_checkout()`, and `is_account_page()`.
     - Added `hreflang="x-default"` pointing to the default language (`hy`) URL.

4. **XML Sitemap Hardening & Legacy Endpoint Redirect (`SEO-004`)**
   - In [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php):
     - Registered top-level `wp_sitemaps_add_provider` filter (before `wp_sitemaps_get_server()` runs on `init:10`) to disable the `'users'` sitemap provider.
     - Registered `wp_sitemaps_posts_query_args` filter setting `$args['gm_skip_lang_filter'] = true` so `hy`, `ru`, and `en` products/pages are all included in core sitemaps.
     - Added `gv2_redirect_legacy_sitemap_index()` on `template_redirect` (priority `1`) to `301` redirect `/sitemap_index.xml` to `home_url( '/wp-sitemap.xml' )`.

5. **Theme Version Bump**
   - In [functions.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/functions.php): bumped `GV2_VERSION` to `2.1.18`.
