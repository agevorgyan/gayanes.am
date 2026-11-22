# Backup Verification Report — Gayane's Kitchen

**Backup Directory (Gitignored):** `dist/backups-2026-11-22/`  
**Date:** 2026-11-22  
**Status:** Refreshed after SEO remediation and integrity-verified in staging.

---

## Backup Artifacts & SHA-256 Checksums

| Component | Archive / File | Size | Integrity Check | SHA-256 |
| :--- | :--- | ---: | :--- | :--- |
| **Database (`gayanes_local`)** | `dist/backups-2026-11-22/db-gayanes_local.sql.gz` | `2.5M` | `gzip -t` **PASS** | `cad9b9891412b077c17f7ab4f750faf126befe2b350fa965dffaecd6a6274eb8` |
| **Theme (`GayanesV2`)** | `dist/backups-2026-11-22/theme-GayanesV2.tar.gz` | `2.3M` | `tar -tzf` **PASS** | `0ce5066f319e6a5e902b2f9bb6130f9c2e3b13f3789aea789602eafe50bb76a1` |
| **Custom Plugins** (`gayanes-core`, `gayanes-frontpad`, `gayanes-qty-rules`, `gemini-multilingual`) | `dist/backups-2026-11-22/custom-plugins.tar.gz` | `74K` | `tar -tzf` **PASS** | `b5319f7f9a1562d2f7c8f1b469be91b0b56d638c6ee02a5bbd37769d10ae453e` |
| **Uploads** (`wp-content/uploads`) | `dist/backups-2026-11-22/uploads.tar.gz` | `42M` | `tar -tzf` **PASS** | `54f7ec563f0c8f13c772bd41a2a25da6b1092e89367aad1d33f565846db99816` |
| **Configuration** (`wp-config.php`) | `dist/backups-2026-11-22/wp-config.php.bak` | `3.3K` | File copy verified | `57eb0d56557ca4729b7cfc2299fa4a544d5974c14fc65567e3b028be6b47615a` |

> **Production Note:** Before executing production cutover on `51.38.26.56`, take equivalent server-side backups of `new.gayanes.am` (full `wp-content/plugins/` including payment gateways, full `wp-content/uploads/`, `.htaccess`, and live DB with `--single-transaction --set-gtid-purged=OFF`) and the legacy `gayanes.am` docroot/DB as a safety archive.
