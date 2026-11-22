# Production Cutover Checklist — `new.gayanes.am` → `https://gayanes.am/`

**Source of Truth:** `new.gayanes.am` (no legacy `gayanes.am` DB merge)  
**Target Domain:** `https://gayanes.am/`  
**Status:** Prepared & validated in staging; **awaiting explicit approval to execute**.

---

## Step-by-Step Cutover Procedure

1. **Pre-Cutover Backups (Both Environments)**
   - [ ] Enable maintenance mode on `gayanes.am` and `new.gayanes.am`.
   - [ ] Dump `new.gayanes.am` DB (`mysqldump --single-transaction --set-gtid-purged=OFF`) and archive `wp-content/` (including `uploads/` and all payment plugins).
   - [ ] Archive legacy `gayanes.am` DB and docroot as a cold rollback snapshot.

2. **Target Preparation & Code Deployment**
   - [ ] Isolate `gayanes.am` destination docroot and deploy `GayanesV2` + custom plugins from branch `seo/p0-remediation`.
   - [ ] Sync full `wp-content/uploads/` and third-party plugins (`ameriabank`, `hk-idram-payment-gateway`, `payment-gateway-for-fastshift`, `payment-gateway-for-telcell`, `litespeed-cache`, `woocommerce`) from `new.gayanes.am`.
   - [ ] Import the `new.gayanes.am` database into the `gayanes.am` target database (do **not** merge legacy `gayanes.am` records).

3. **Serialized-Safe Domain Replacement**
   - [ ] Run `wp option update home 'https://gayanes.am'` and `wp option update siteurl 'https://gayanes.am'`.
   - [ ] Run `wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables --dry-run`, inspect counts, then run live.
   - [ ] Run `wp search-replace 'http://new.gayanes.am' 'https://gayanes.am' --all-tables`.
   - [ ] Run `wp transient delete --all && wp rewrite flush --hard`.

4. **Payment Gateways Live Activation**
   - [ ] Switch Ameriabank vPOS (`woocommerce_ameria_vpos_settings`) from `sandbox=yes` to `sandbox=no`, enter live `client_id`, `username`, `password`, and `base_live`.
   - [ ] Verify Idram, Fastshift, and Telcell callback URLs point to `https://gayanes.am/`.

5. **Redirects & Cache Activation**
   - [ ] Verify SSL certificates cover `gayanes.am`, `www.gayanes.am`, and `new.gayanes.am` (all verified on `51.38.26.56`).
   - [ ] Enable server `301` redirect: `https://new.gayanes.am/(.*)` → `https://gayanes.am/$1` (preserving query string).
   - [ ] Purge LiteSpeed Cache and disable maintenance mode.

6. **Post-Cutover Smoke Tests**
   - [ ] Verify `https://gayanes.am/` (`hy`/`ru`/`en`), `/shop/`, `/product/...`, `/wp-sitemap.xml` (`99` products), `/sitemap_index.xml` (`301`), `/wp-sitemap-users-1.xml` (`404`), and `/cart/`/`/checkout/` (`noindex, follow`).
   - [ ] Confirm zero `new.gayanes.am` references in HTML source or sitemaps.
   - [ ] Execute 1 live low-value card order + refund test via Ameriabank vPOS.
