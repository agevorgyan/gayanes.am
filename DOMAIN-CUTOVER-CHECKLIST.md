# Domain & Infrastructure Cutover Checklist — Gayane's Kitchen

**Audit Date:** 2026-11-22  
**Target Primary Domain:** `https://gayanes.am/`  
**Current Staging/New Site:** `https://new.gayanes.am/`  
**Status:** Infrastructure verified (read-only). Cutover **not** executed.

---

## 1. Verified DNS, SSL & HTTP Behavior (Observed 2026-11-22)

| Hostname | DNS A / CNAME | HTTP (`80`) Behavior | HTTPS (`443`) Behavior | SSL Certificate & SAN Coverage |
| :--- | :--- | :--- | :--- | :--- |
| `gayanes.am` | `51.38.26.56` | `301` → HTTPS | `HTTP/2 200` (LiteSpeed) | Let's Encrypt `CN=www.khohanoc.am.elab.am`, SAN matches `gayanes.am` (valid Oct 3, 2026 – Jan 1, 2027) |
| `www.gayanes.am` | CNAME `gayanes.am.` → `51.38.26.56` | `301` → HTTPS | `HTTP/2 301` → `https://gayanes.am/` | Same cert, SAN matches `*.gayanes.am` |
| `new.gayanes.am` | `51.38.26.56` | `301` → HTTPS | `HTTP/2 200` | Let's Encrypt `CN=www.new.gayanes.am`, SAN matches `new.gayanes.am` (valid Nov 9, 2026 – Feb 7, 2027) |
| `khohanots.am` | `51.38.26.56` | `301` → HTTPS | `HTTP/2 301` → `https://gayanes.am/` | Let's Encrypt `CN=khohanots.elab.am`, SAN matches `khohanots.am` (valid Oct 27, 2026 – Jan 25, 2027) |

---

## 2. Pre-Cutover Checklist (Do Not Execute Until Approved)

### DNS & SSL
- [ ] Lower DNS TTL on `gayanes.am`, `www.gayanes.am`, and `new.gayanes.am` to `300s` at least 24 hours before cutover (already co-located on `51.38.26.56`, so vhost switch is near-instant).
- [ ] Keep Let's Encrypt SSL certificates active on **both** `gayanes.am` (`*.gayanes.am`) and `new.gayanes.am` so `https://new.gayanes.am/*` → `https://gayanes.am/*` 301 redirects complete TLS cleanly.

### WordPress & Database Cutover
- [ ] Put both `gayanes.am` and `new.gayanes.am` into maintenance mode and back up both databases + `wp-content/uploads/`.
- [ ] Reconcile any delta orders/customers from live `gayanes.am` (see [DATABASE-RECONCILIATION-REPORT.md](file:///Users/apple/Projects/gayanes.am/DATABASE-RECONCILIATION-REPORT.md)).
- [ ] Point `gayanes.am` LiteSpeed vhost document root to the `GayanesV2` deployment containing branch `seo/p0-remediation`.
- [ ] Run serialized-safe URL replacement:
  ```bash
  wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables --dry-run
  wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables
  ```
- [ ] Confirm `wp_options` `siteurl` and `home` both equal `https://gayanes.am`.
- [ ] Flush WordPress permalinks (`wp rewrite flush --hard`) and LiteSpeed Cache.

### Redirect Mapping (Activate Only at Cutover)
- [ ] Configure LiteSpeed / `.htaccess` `301` redirect on `new.gayanes.am` vhost:
  - `https://new.gayanes.am/(.*)` → `https://gayanes.am/$1` (preserving query string `?lang=ru`, `?lang=en`).
- [ ] Keep `www.gayanes.am/*` → `https://gayanes.am/$1` (`301`).
- [ ] Keep `khohanots.am/*` → `https://gayanes.am/$1` (`301`).
- [ ] Keep WordPress internal `301` redirect `/sitemap_index.xml` → `https://gayanes.am/wp-sitemap.xml`.
