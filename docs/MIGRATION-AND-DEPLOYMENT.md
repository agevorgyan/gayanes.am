# Phase 8 & 10 — Safe Migration, Caching & Deployment Playbook

## 1. Pre-Deployment Backup (Mandatory)

Before any staging-to-production cutover:
1. **Database Backup**:
   ```bash
   wp db export backup-gayanes-$(date +%F-%H%M).sql --add-drop-table
   ```
2. **Files Backup**:
   Archive `wp-content/themes/flatsome-child/`, `wp-content/plugins/`, and `wp-content/uploads/`.
3. **Verify Backup**: Confirm non-zero SQL dump and archive integrity before proceeding.

## 2. Staging Environment Deployment Steps

1. Clone production database and `wp-content/uploads` to staging (`staging.gayanes.am`).
2. Update `siteurl` and `home` in staging via `wp search-replace 'https://gayanes.am' 'https://staging.gayanes.am' --skip-columns=guid`.
3. Copy `wp-content/themes/gayanes-theme` and `wp-content/plugins/gayanes-core` to staging.
4. Activate `gayanes-core` plugin first, then switch theme to `gayanes-theme`.
5. Re-assign navigation menus (`primary`, `footer`) under **Appearance → Menus**.
6. Configure Polylang:
   - Default language: Armenian (`hy`) at `/`
   - Secondary languages: Russian (`ru`) at `/ru/`, English (`en`) at `/en/`
   - Translate theme strings under **Languages → Translations** (`Gayanes Theme` group).

## 3. LiteSpeed Cache & Cloudflare Rules

Configure LiteSpeed Cache **Do Not Cache URIs**:
- `/cart/` (and `/ru/cart/`, `/en/cart/`, `/զամբյուղ/`, `/корзина/`)
- `/checkout/` (and localized equivalents)
- `/my-account/` (and localized equivalents)
- `/wc-api/`
- Query strings: `wc-ajax`, `wc-api`, `add-to-cart`

Enable:
- WebP / AVIF image replacement in LiteSpeed Cache or Cloudflare Polish
- Brotli compression
- Browser cache TTL for static CSS/JS/WOFF2/images (`1 year` immutable)

## 4. 301 Redirects & URL Preservation

- Keep all existing WooCommerce product and category permalinks unchanged.
- For any discontinued or renamed URLs (e.g., `/under-construction/`, `/wheel/`), configure 301 redirects in `.htaccess` or Yoast Redirects / Redirection plugin:
  ```apache
  Redirect 301 /under-construction/ /
  Redirect 301 /wheel/ /shop/
  ```

## 5. Production Cutover & Rollback

1. Put store briefly in maintenance mode (or deploy during low-traffic window).
2. Upload `gayanes-core` and `gayanes-theme` to production `wp-content/`.
3. Activate `gayanes-core`, activate `gayanes-theme`, assign menus, purge LiteSpeed Cache & Cloudflare.
4. Run live smoke tests (Add to Cart, `[ - ] [ 1 ] [ + ]`, Side Cart, Checkout with Idram/ArCa/Ameriabank/Telcell).
5. **Rollback**: Switch theme back to `flatsome-child` and deactivate `gayanes-core` if any critical issue occurs (zero database changes were made).

