# Domain Migration Audit: `khohanots.am` → `gayanes.am` → `new.gayanes.am`

**Status:** Audit-Only (No redirects or canonical changes implemented)

---

## 1. Live HTTP & Redirect Evidence (Measured 2026-11-22)

| Domain / Host | HTTP | HTTPS | Final Target / Behavior | Server / Cache |
|---|---|---|---|---|
| `khohanots.am` | `301` | `301` | `https://gayanes.am/` | LiteSpeed |
| `www.khohanots.am` | `301` | `301` | `https://gayanes.am/` | LiteSpeed |
| `gayanes.am` | `301` | `200` | Live old WordPress site (`Po.91`) | LiteSpeed + LSCache |
| `www.gayanes.am` | `301` | `301` | `https://gayanes.am/` | LiteSpeed + WordPress |
| `new.gayanes.am` | `301` | `200` | Live new `GayanesV2` site (`Po.10`) | LiteSpeed + LSCache |

---

## 2. Ten-Point Domain Migration Investigation

1. **Primary Production Domain:** `https://gayanes.am/` MUST be the permanent primary production domain. It already receives the `301` equity from `khohanots.am` and `www.khohanots.am`, is shorter and brand-aligned, and avoids splitting domain authority onto a `new.` staging subdomain.
2. **Subdomain Indexability (`new.gayanes.am`):** `https://new.gayanes.am/` currently returns `HTTP/2 200` with `<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />` and an open `wp-sitemap.xml`. Google can index it alongside `gayanes.am`. *(Direct `site:` GSC data is unavailable in this environment.)*
3. **Duplicate Content Across Domains:** Both `https://gayanes.am/` and `https://new.gayanes.am/` serve the same brand (`Gayane's Kitchen | Գայանեի Խոհանոց`) and overlapping product catalog concurrently.
4. **Canonical URLs:** `new.gayanes.am` self-canonicalizes to `https://new.gayanes.am/...` (and outputs duplicate `<link rel="canonical">` tags), while `gayanes.am` canonicalizes to `https://gayanes.am/...`.
5. **301 Redirects:** `khohanots.am` → `https://gayanes.am/` is properly configured (`301`). However, there is no redirect between `gayanes.am` and `new.gayanes.am` because both are live.
6. **XML Sitemaps:** `gayanes.am/robots.txt` references `https://gayanes.am/sitemap.xml` and `news-sitemap.xml`. `new.gayanes.am/robots.txt` references `https://new.gayanes.am/wp-sitemap.xml`. Neither mixes domains inside a single sitemap, which is good, but having two competing sitemaps live is harmful.
7. **Hreflang References:** `new.gayanes.am` outputs `hreflang` pointing to `https://new.gayanes.am/?lang=en`, `https://new.gayanes.am/`, `https://new.gayanes.am/?lang=ru`.
8. **Hardcoded Old Domain References in Codebase:** `wp-content/themes/GayanesV2/inc/seo.php` line 148 hardcodes `'url' => 'https://gayanes.am/'` inside the Organization schema fallback even when running on `new.gayanes.am`.
9. **Images & Media:** Media on `new.gayanes.am` is served from `https://new.gayanes.am/wp-content/uploads/...`. During cutover to `gayanes.am`, a serialized-safe `wp search-replace` is required so all media URLs point to `https://gayanes.am/`.
10. **Indexing & Ranking Risk Assessment:** **High Risk (P0).** Leaving `new.gayanes.am` open and indexable while `gayanes.am` is live causes duplicate content, keyword cannibalization, and diluted link equity from `khohanots.am`.

---

## 3. Recommended Domain Architecture & Safe Migration Strategy

**Recommended Architecture:**
- **Primary Domain:** `https://gayanes.am/` (non-www, HTTPS)
- **Legacy Domain (`khohanots.am` & `www.khohanots.am`):** Keep permanent `301` → `https://gayanes.am/` (ideally path-preserving if old URLs had traffic).
- **Staging Subdomain (`new.gayanes.am`):**
  - **Pre-Cutover (Immediate):** Enable WordPress *"Discourage search engines from indexing this site"* (`blog_public = 0`) or HTTP Basic Auth on `new.gayanes.am` so Google does not index the staging subdomain.
  - **At Cutover:**
    1. Take full DB + file backups of `gayanes.am`.
    2. Deploy `GayanesV2` codebase + `elab2023_newgayanes` database to `gayanes.am` with serialized-safe `wp search-replace 'https://new.gayanes.am' 'https://gayanes.am' --all-tables`.
    3. Map any legacy `gayanes.am` URLs whose slugs changed to `301` redirects in `.htaccess` / Redirection.
    4. Configure `301` redirect from `new.gayanes.am/*` → `https://gayanes.am/$1` (or decommission `new.gayanes.am`).
    5. Verify `https://gayanes.am/wp-sitemap.xml` (or Yoast `/sitemap_index.xml`) and submit in Google Search Console.
