# P0 File Change Map — Gayane's Kitchen

**Branch:** `seo/p0-remediation`  
**Date:** 2026-11-22

| File | Symbol / Hook | Change Summary | Objective(s) |
| :--- | :--- | :--- | :--- |
| [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php) | `gv2_seo_init()` | Removed `rel_canonical` when `! defined( 'WPSEO_VERSION' )`; hooked `gv2_filter_wp_robots` on `wp_robots` (priority `20`); hooked `gv2_redirect_legacy_sitemap_index` on `template_redirect` (priority `1`). | SEO-002, SEO-003, SEO-004 |
| [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php) | Top-level `wp_sitemaps_add_provider`, `wp_sitemaps_posts_query_args` | Disabled `'users'` sitemap provider before `init:10` so `/wp-sitemap-users-1.xml` returns `404`; set `gm_skip_lang_filter = true` so `hy`/`ru`/`en` products are all included in `wp-sitemap-posts-product-1.xml`. | SEO-004 |
| [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php) | `gv2_filter_wp_robots()` | Added robots policy: `404` → `noindex, nofollow`; `cart`/`checkout`/`my-account`/`search`/`author`/`?add-to-cart=`/`?orderby=` → `noindex, follow`; commercial pages → `index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1`. | SEO-003 |
| [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php) | `gv2_redirect_legacy_sitemap_index()` | Redirects `/sitemap_index.xml` (`301`) to `home_url( '/wp-sitemap.xml' )` on the active host. | SEO-004 |
| [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php) | `gv2_output_seo_meta_tags()` | Added `is_404()` early return; resolved single canonical URL across front page, shop, taxonomies, and singular posts (with `GM_Router::add_lang_to_url`); removed duplicate hardcoded `<meta name="robots">`. | SEO-002, SEO-003 |
| [seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php) | `gv2_output_json_ld_graph()` | Replaced hardcoded `'https://gayanes.am/'` with `$site_url` (`home_url( '/' )`). | SEO-001 |
| [class-gm-router.php](file:///Users/apple/Projects/gayanes.am/wp-content/plugins/gemini-multilingual/includes/class-gm-router.php) | `GM_Router::output_hreflang_tags()` | Skipped `hreflang` output on `is_404()`, `is_search()`, `is_cart()`, `is_checkout()`, `is_account_page()`; added `x-default` pointing to default language (`hy`). | SEO-002, SEO-003 |
| [functions.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/functions.php) | `GV2_VERSION` | Bumped theme version constant to `2.1.18`. | Staging release tracking |

## Files Inspected (Unmodified)
- [class-seo-schema.php](file:///Users/apple/Projects/gayanes.am/wp-content/plugins/gayanes-core/includes/class-seo-schema.php): Verified already uses `home_url('/')` for JSON-LD `Organization`, `WebSite`, `Product`, `Article`, and `BreadcrumbList` schemas.
