# Phase 9 — QA & Automated Verification Report

## 1. Automated Local Checks (Executed in Workspace)

- **PHP Syntax Lint (`php -l`)**: Verified across all PHP files in `wp-content/plugins/gayanes-core/` and `wp-content/themes/gayanes-theme/`.
- **JavaScript Syntax Check (`node --check`)**: Verified on `wp-content/themes/gayanes-theme/assets/js/cart.js`.

## 2. Staging / Production Functional QA Checklist

| Category | Test Case | Expected Result |
| :--- | :--- | :--- |
| **Product Card** | Click `Add to Cart` on simple product | Adds 1 item via `gayanes_add_to_cart`, swaps button to `[ - ] [ 1 ] [ + ]`, updates header & mobile badges |
| **Quantity Controls** | Click `+` and `-` on card | Persists via `WC()->cart`, updates totals immediately, prevents duplicate requests |
| **Zero Removal** | Click `-` when quantity is `1` | Removes item from `WC()->cart`, restores `Add to Cart` button |
| **Variable Product** | Open variable product, select variation | Validates variation ID & stock, adds to cart cleanly |
| **Mini Cart Drawer** | Click cart icon, press `Escape`, test `+`/`-`/Remove | Opens accessible drawer, restores focus on `Escape`, syncs product cards |
| **Checkout & Gateways** | Test Guest & Logged-in checkout with Idram, ArCa, Ameriabank, Telcell | Order created, payment redirect/callback succeeds, coupons & shipping calculated |
| **Multilingual** | Switch `HY` (`/`) ↔ `RU` (`/ru/`) ↔ `EN` (`/en/`) | Preserves equivalent product/category URLs, `hreflang` and canonical tags valid |
| **Performance** | Run Google PageSpeed Insights (Mobile & Desktop) | Mobile 90+, Desktop 95+, LCP <= 2.5s, INP <= 200ms, CLS <= 0.1 |

