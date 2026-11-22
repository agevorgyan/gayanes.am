# Database Reconciliation Report — Gayane's Kitchen

**Audit Date:** 2026-11-22  
**Mode:** Read-only inspection (no database records modified)

---

## 1. Available Databases Inspected

| Source | Origin / Role | Status |
| :--- | :--- | :--- |
| `gayanes_local` (`dist/elab2023_newgayanes.sql`) | Staging snapshot from `new.gayanes.am` | **Inspected in full via MySQL** |
| `dist/localhost-backup-before-import.sql` | Local `gayanes_local` pre-import backup | **Inspected (not live `gayanes.am` prod DB: contains only 2 `wc_orders`, 1 user)** |
| Live `gayanes.am` production database | Legacy production (`https://gayanes.am/`) | **NOT AVAILABLE in this environment** |

---

## 2. `new.gayanes.am` (`elab2023_newgayanes.sql`) Baseline Metrics

### Orders (`wp_posts` `shop_order` & HPOS `wp_wc_orders` — in sync)
| Status | Count |
| :--- | ---: |
| `wc-completed` | 152 |
| `wc-processing` | 11 |
| `wc-on-hold` | 6 |
| `wc-pending` | 1 |
| `wc-cancelled` | 6 |
| `wc-failed` | 2 |
| `trash` | 536 |
| **Total rows** | **714** |

### Customers, Products & Multilingual Groups
- **Registered Users (`wp_users`):** `44`
- **Published Products (`wp_posts` `post_type='product'`):** `99`
- **Published Variations (`post_type='product_variation'`):** `0`
- **Multilingual Translation Groups (`wp_gm_translations`):** `88` total (`65` post groups, `23` term groups), covering:
  - `33` product groups (`99` products: `33 hy`, `33 ru`, `33 en`)
  - `15` product category groups (`45` category terms)
  - `8` page groups (`24` pages)
  - `4` blog post groups (`12` posts)
- **WordPress `siteurl` / `home` (local):** `http://127.0.0.1:8088` (must become `https://gayanes.am` on production).

---

## 3. Gaps Requiring Pre-Cutover Reconciliation

Because the live `gayanes.am` production database was not accessible during this audit, the following comparisons **must** be executed on the server right before cutover:
1. **Order Count & Recent Order Diff:** Compare `MAX(ID)` and `date_created_gmt` in `wp_wc_orders` / `wp_posts (shop_order)` between live `gayanes.am` and `new.gayanes.am`.
2. **Customer Count Diff:** Compare `wp_users` and `wp_wc_customer_lookup` so no new customer accounts created on `gayanes.am` are lost.
3. **Product ID, SKU, Stock & Variation Diff:** Verify SKUs, prices, and stock levels between legacy `gayanes.am` and `new.gayanes.am` (`99` simple products, `0` variations in `new.gayanes.am`).
4. **Payment Status Diff:** Confirm no `pending` or `on-hold` orders on legacy `gayanes.am` are awaiting gateway callbacks.

---

## 4. Safe Final Synchronization Strategy
1. Enable maintenance mode on **both** `gayanes.am` and `new.gayanes.am` for a 15–30 minute cutover window.
2. Take full `mysqldump` backups of both databases.
3. Export any delta orders/customers created on `gayanes.am` after the `new.gayanes.am` snapshot timestamp (using WooCommerce order/customer export or SQL delta import) and reconcile them into the target database.
4. Run serialized-safe URL replacement:
   ```bash
   wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables --dry-run
   wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables
   ```
5. Verify `siteurl` and `home` in `wp_options` both equal `https://gayanes.am`.
