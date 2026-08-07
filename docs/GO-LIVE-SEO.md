# Go-live: noindex / sitemap / Search Console

**Stav:** theme **1.29.0** — `sklo_is_production()` (+ `.eu` i `.cz`), brand titles bez `.cz`, canonicaly utility stránek, GA4 option. Ostrá indexace na živém hostu (teď `sklospecial.eu`). Až přijde `.cz`: `docs/DOMAIN-CZ.md`.

## Staging (sslip / non-production)

- WP Settings → Reading → „Discourage search engines“ = ON
- Rank Math (pokud aktivní): site-wide noindex
- Theme: `sklo_is_production()` = false → meta `noindex, nofollow`
- `robots.txt` filtr: `Disallow: /` — staging NESMÍ být indexovatelný

## Produkce (sklospecial.eu teď; sklospecial.cz později)

Theme automaticky:

- **ne** vypisuje staging noindex meta
- `robots.txt` → Allow + `Sitemap: {home}/wp-sitemap.xml`

Stále ručně:

1. Vypnout Discourage search engines (Reading)
2. Rank Math: remove noindex (pokud používaný)
3. Ověřit robots.txt + sitemap (`/wp-sitemap.xml`)
4. Google Search Console — ověřit property, odeslat sitemap

## SEO hardenings (1.29.0)

- Brand v titles: **Sklospeciál** (filtr `option_blogname` + document titles) — ne „Sklospeciál.cz“
- Homepage canonical → `home_url('/')`
- Canonical + OG: home, pillars, SEO landings, produkt `?kod=`, **realizace / recenze / mapa-stranek / cookies / legal**
- Produkt detail: title/description obsahují název produktu
- Footer: odkaz `/mapa-stranek/`
- GA4: option `sklo_ga4_id` (prázdné = off), load až po cookie consent analytics

## Checklist

- [x] DNS live + HTTPS OK (`.eu`)
- [ ] `.cz` DNS + Coolify host (až bude) — `docs/DOMAIN-CZ.md`
- [ ] Remove noindex (Reading + Rank Math) na produkci
- [ ] Sitemap enabled
- [ ] Search Console sitemap submitted
- [ ] Meta robots = index,follow on production (theme + Rank Math)
- [ ] Canonical / OG smoke na home + utility + 1 city + 1 `?kod=`

Celkový day-of seznam: `docs/GO-LIVE-CHECKLIST.md`.
