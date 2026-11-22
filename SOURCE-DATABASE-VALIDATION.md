# Source Database Validation — `new.gayanes.am`

**Source Dump:** `dist/elab2023_newgayanes.sql` (loaded into staging DB `gayanes_local`)  
**Policy:** `new.gayanes.am` is the **sole source of truth**. Legacy `gayanes.am` database records will **not** be merged or imported.  
**Validation Date:** 2026-11-22

---

## 1. Database Integrity & Tables
- **Total Tables:** `63`
- **`CHECK TABLE` Result:** `0` errors / `0` non-OK rows across all `63` tables.
- **Custom & Plugin Tables Verified:**
  - `wp_gm_translations` (`194` post rows, `68` term rows — `88` translation groups)
  - `wp_gm_strings`
  - `wp_gk_tg_notification_log`
  - `wp_litespeed_url`, `wp_litespeed_url_file`
  - `wp_yoast_indexable`, `wp_yoast_indexable_hierarchy`, `wp_yoast_migrations`, `wp_yoast_primary_term`, `wp_yoast_seo_links`, `wp_yoast_expiring_store`
  - Full WooCommerce HPOS & lookup tables (`wp_wc_orders`, `wp_wc_orders_meta`, `wp_wc_order_addresses`, `wp_wc_order_operational_data`, `wp_wc_order_stats`, `wp_wc_order_product_lookup`, `wp_wc_customer_lookup`, `wp_wc_product_meta_lookup`, etc.)

## 2. WooCommerce HPOS & Order Storage
- `woocommerce_custom_orders_table_enabled`: **`yes`** (HPOS enabled)
- `woocommerce_custom_orders_table_data_sync_enabled`: **`yes`** (compatibility sync with `wp_posts` enabled)
- **Order Counts (`wp_wc_orders` / `wp_posts` `shop_order`, 100% in sync):**
  - `wc-completed`: `152`
  - `wc-processing`: `11`
  - `wc-on-hold`: `6`
  - `wc-pending`: `1`
  - `wc-cancelled`: `6`
  - `wc-failed`: `2`
  - `trash`: `536`
  - **Total:** `714` (`178` non-trash orders)

## 3. Payment References, Customers, Products & Media
- **Payment Methods (`wp_wc_orders`, non-trash):**
  - `cod`: `68`
  - `cheque`: `59`
  - `ameria_vpos`: `27`
  - `hk-idram-payment-gateway`: `20`
  - `hkd_telcell`: `3`
  - `payment-gateway-for-fastshift`: `1`
  - **Orders with `transaction_id`:** `18`
- **Customers & Users:** `44` rows in `wp_users`; `126` rows in `wp_wc_customer_lookup`.
- **Products & Categories:** `99` published products (`0` variations), `57` trashed products; `46` `product_cat` taxonomy terms.
- **Inventory:** `woocommerce_manage_stock = yes`.
- **Media Library (`wp_posts` `attachment`):** `27` attachment records in DB. *(Note: full `wp-content/uploads/` must be synced from the `new.gayanes.am` server during production cutover.)*
- **Email & Permalinks:** `admin_email = admin@gayanes.am`, `woocommerce_email_from_address = admin@gayanes.am`, `permalink_structure = /%postname%/`.
