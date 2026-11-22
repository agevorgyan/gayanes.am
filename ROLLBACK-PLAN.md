# Rollback Plan — Gayane's Kitchen Domain Migration

**Target:** `https://gayanes.am/`  
**Source:** `https://new.gayanes.am/`  
**Branch:** `seo/p0-remediation`

---

## 1. Staging Code Rollback
To revert the `seo/p0-remediation` SEO changes without touching database or content:
```bash
git checkout <pre-remediation-commit> -- \
  wp-content/themes/GayanesV2/inc/seo.php \
  wp-content/themes/GayanesV2/functions.php \
  wp-content/plugins/gemini-multilingual/includes/class-gm-router.php
```
Local verified backups are stored in `dist/backups-2026-11-22/` (see [BACKUP-VERIFICATION.md](file:///Users/apple/Projects/gayanes.am/BACKUP-VERIFICATION.md)).

## 2. Production Cutover Rollback (If Issues Occur During Cutover)
1. **Disable `new.gayanes.am` → `gayanes.am` `301` redirect** in the LiteSpeed vhost / `.htaccess` so `https://new.gayanes.am/` remains immediately reachable.
2. **Repoint `gayanes.am` vhost** back to the archived legacy `gayanes.am` document root.
3. **Restore the pre-cutover `gayanes.am` database backup** (`mysql < pre_cutover_gayanes.sql`) if the target DB was overwritten in place.
4. **Purge LiteSpeed Cache** and verify `https://gayanes.am/` and `https://new.gayanes.am/` both return `HTTP 200`.
