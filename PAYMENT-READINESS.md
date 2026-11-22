# Payment Readiness Report — Gayane's Kitchen

**Date:** 2026-11-22  
**Mode:** Read-only inspection (no credentials exposed; no real payments initiated)

---

## 1. Payment Gateways Configured (`new.gayanes.am` DB)
- `ameriabank/ameriabank.php` (`woocommerce_ameria_vpos_settings`) — `27` historical orders
- `hk-idram-payment-gateway/wc-hkdigital-idram-gateway.php` — `20` historical orders
- `payment-gateway-for-telcell/wc-hkdigital-telcell-gateway.php` — `3` historical orders
- `payment-gateway-for-fastshift/wc-hkdigital-fastshift-gateway.php` — `1` historical order
- `cod` (`68` orders) & `cheque` (`59` orders)
- `18` orders have `transaction_id` recorded in `wp_wc_orders`.

## 2. Ameriabank vPOS Status (`CRITICAL BLOCKER FOR LIVE CUTOVER`)
Observed in `woocommerce_ameria_vpos_settings`:
- `enabled`: `yes`
- `sandbox`: **`yes`** (test mode active)
- `base_test`: `https://servicestest.ameriabank.am/VPOS/`
- `base_live`: **`""` (empty)**
- `client_id`: **`""` (empty)**
- `logging`: `yes`, `twostage`: `no`

## 3. Callback Protection & Required Production Steps
- `Gayanes_Performance` ([class-performance.php](file:///Users/apple/Projects/gayanes.am/wp-content/plugins/gayanes-core/includes/class-performance.php#L83-L92)) disables LiteSpeed/page caching (`DONOTCACHEPAGE`, `Cache-Control: no-store`, `X-LiteSpeed-Cache-Control: no-cache`) on `/cart/`, `/checkout/`, `/my-account/`, and all `?wc-api=` / `/wc-api/` callbacks.
- **Before going live on `https://gayanes.am/`:**
  1. Copy/preserve the `wp-content/plugins/ameriabank`, `hk-idram-payment-gateway`, `payment-gateway-for-fastshift`, and `payment-gateway-for-telcell` directories from the `new.gayanes.am` server.
  2. Switch Ameriabank vPOS from `sandbox=yes` to `sandbox=no` on `https://gayanes.am/`, populate live `client_id`, `username`, `password`, and `base_live`, and whitelist `https://gayanes.am/` return/callback URLs with Ameriabank.
  3. Verify 1 controlled live low-amount transaction and refund on `https://gayanes.am/` after cutover.
