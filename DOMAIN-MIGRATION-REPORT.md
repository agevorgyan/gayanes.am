# Domain Migration & Replacement Report — Gayane's Kitchen

**Source:** `https://new.gayanes.am/` (`dist/elab2023_newgayanes.sql`)  
**Target Primary Domain:** `https://gayanes.am/`  
**Legacy Domain:** `https://khohanots.am/`  
**Staging Validation Host:** `http://127.0.0.1:8088` (branch `seo/p0-remediation`)

---

## 1. Domain Reference Scan
- **Source Dump (`dist/elab2023_newgayanes.sql`):** `2,716` occurrences of `new.gayanes.am` across serialized options, posts, and metadata.
- **Staging Database (`gayanes_local`):** After importing to `http://127.0.0.1:8088`, `0` occurrences remain in `wp_posts`, `wp_postmeta`, `wp_termmeta`, `wp_usermeta`, and `wp_wc_orders_meta`. Only `1` cached WooCommerce transient (`_transient_woocommerce_blocks_asset_api_script_data_ssl`) in `wp_options` still references `new.gayanes.am` (cleared automatically by transient flush).
- **Theme & Plugin Code (`seo/p0-remediation`):**
  - Removed hardcoded `'https://gayanes.am/'` in `gv2_output_json_ld_graph()` ([seo.php](file:///Users/apple/Projects/gayanes.am/wp-content/themes/GayanesV2/inc/seo.php)); all canonical, JSON-LD, hreflang, and sitemap URLs now derive dynamically from `home_url()` / `get_permalink()`.
  - Rendered HTML on `/`, `/?lang=ru`, `/?lang=en`, `/shop/`, `/?s=dolma`, `/cart/`, `/my-account/`, and `/checkout/` contains **zero** `new.gayanes.am` URLs.

## 2. Production Serialized-Safe Replacement Plan (Execute at Cutover Only)
1. Update `siteurl` and `home` in `wp_options` to `https://gayanes.am`.
2. Run WP-CLI serialized-safe replacement across all tables (handling PHP serialized widgets, theme mods, and plugin settings without corrupting string byte lengths):
   ```bash
   wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables --dry-run
   wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables
   wp search-replace 'http://new.gayanes.am' 'https://gayanes.am' --all-tables
   ```
3. Flush transients, rewrite rules, and LiteSpeed Cache:
   ```bash
   wp transient delete --all
   wp rewrite flush --hard
   ```
4. Verify zero occurrences of `new.gayanes.am` remain in rendered HTML, JSON-LD `@id`/`url`, canonicals, `hreflang`, and `/wp-sitemap.xml`.
