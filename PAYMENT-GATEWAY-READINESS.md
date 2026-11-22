# Payment Gateway Readiness Report — Ameriabank vPOS & Local Gateways

**Audit Date:** 2026-11-22  
**Mode:** Read-only inspection (no credentials exposed; no real payments initiated)  
**Verdict:** **NOT READY FOR PRODUCTION**

---

## 1. Active Payment Gateways Configured in Database (`wp_options.active_plugins`)
The `elab2023_newgayanes.sql` database lists the following payment plugins as active:
- `ameriabank/ameriabank.php` (`woocommerce_ameria_vpos_settings`)
- `hk-idram-payment-gateway/wc-hkdigital-idram-gateway.php` (`woocommerce_hk-idram-payment-gateway_settings`)
- `payment-gateway-for-fastshift/wc-hkdigital-fastshift-gateway.php` (`woocommerce_payment-gateway-for-fastshift_settings`)
- `payment-gateway-for-telcell/wc-hkdigital-telcell-gateway.php`
- WooCommerce built-in `cod` (`woocommerce_cod_settings`)

**Critical Repository Gap:** None of the 4 third-party payment plugin directories (`ameriabank`, `hk-idram-payment-gateway`, `payment-gateway-for-fastshift`, `payment-gateway-for-telcell`) exist in the local workspace `wp-content/plugins/`! They reside only on the `new.gayanes.am` hosting server.

---

## 2. Ameriabank vPOS Configuration Audit (`woocommerce_ameria_vpos_settings`)

Non-secret configuration values observed in `gayanes_local` (`elab2023_newgayanes.sql`):

| Setting Key | Observed Value | Production Readiness |
| :--- | :--- | :--- |
| `enabled` | `yes` | Enabled |
| `title` | `Card` | OK |
| `sandbox` | **`yes`** | **BLOCKER — Sandbox mode is ON** |
| `base_test` | `https://servicestest.ameriabank.am/VPOS/` | Test endpoint active |
| `base_live` | **`""` (empty)** | **BLOCKER — Live endpoint missing** |
| `client_id` | **`[REDACTED len=0]` (empty)** | **BLOCKER — Live Client ID missing** |
| `username` | `[REDACTED len=12]` | Present (likely sandbox credential) |
| `password` | `[REDACTED len=18]` | Present (likely sandbox credential) |
| `twostage` | `no` | Single-stage capture |
| `logging` | `yes` | Enabled |
| `enforce_test_rules` | `no` | Disabled |

---

## 3. Callback, Return, Webhook & Order Lifecycle Audit

- **Cache Protection Verified:** In [class-performance.php](file:///Users/apple/Projects/gayanes.am/wp-content/plugins/gayanes-core/includes/class-performance.php#L83-L92), `Gayanes_Performance` explicitly sets `DONOTCACHEPAGE`, `Cache-Control: no-store, no-cache, must-revalidate, max-age=0`, and `X-LiteSpeed-Cache-Control: no-cache` for `is_cart()`, `is_checkout()`, `is_account_page()`, and any `?wc-api=` or `/wc-api/` callback URL (covering Idram, ArCa, Ameriabank, Telcell).
- **Unverified Locally (Server-Side Inspection Required):** Because `wp-content/plugins/ameriabank/ameriabank.php` is not in Git, the exact `WC_API` callback slug, `BackURL` return handler, `GetPaymentDetails` status synchronization, failed-payment order status transition, and `process_refund()` implementation **must be inspected on the `new.gayanes.am` server** before cutover.

---

## 4. Mandatory Pre-Production Actions for Ameriabank vPOS
1. Commit/sync the production `ameriabank` (and Idram/Fastshift/Telcell) plugin directories or verify their exact files on `new.gayanes.am`.
2. Obtain live Ameriabank vPOS credentials (`client_id`, `username`, `password`, live base URL `https://services.ameriabank.am/VPOS/`) from the merchant portal.
3. Disable `sandbox` (`sandbox = no`) and populate `base_live` and `client_id` **only on production** after cutover.
4. Register/whitelist `https://gayanes.am/` return and `/wc-api/` callback URLs with Ameriabank merchant support.
5. Execute one controlled live low-amount transaction and refund test on `https://gayanes.am/` immediately after cutover.
