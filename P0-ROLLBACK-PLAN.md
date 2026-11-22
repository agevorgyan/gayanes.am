# P0 Rollback Plan — Gayane's Kitchen

**Branch:** `seo/p0-remediation`  
**Date:** 2026-11-22

---

## 1. Code Rollback (Theme & Plugin Fixes)
If any regression is observed after deploying `seo/p0-remediation` to staging or production:
1. Revert the 3 modified code files to the pre-remediation commit:
   ```bash
   git checkout <pre-remediation-commit> -- \
     wp-content/themes/GayanesV2/inc/seo.php \
     wp-content/themes/GayanesV2/functions.php \
     wp-content/plugins/gemini-multilingual/includes/class-gm-router.php
   ```
2. Clear WordPress object/page cache and PHP OPcache (`wp cache flush`).

## 2. Production Cutover Rollback (If Executed Later)
If domain cutover (`new.gayanes.am` → `gayanes.am`) experiences issues after approval and execution:
1. **Disable `301` redirect** on `new.gayanes.am` → `gayanes.am` in server vhost / `.htaccess` so `new.gayanes.am` remains accessible if needed, OR point `gayanes.am` vhost back to the previous legacy docroot.
2. **Restore pre-cutover database backup** (`mysql < gayanes_pre_cutover_backup.sql`) if database search-replace or order sync caused data corruption.
3. **Flush caches & verify:** confirm `https://gayanes.am/` returns `HTTP 200`, checkout completes, and `wp_options` (`siteurl`, `home`) match the active domain.
