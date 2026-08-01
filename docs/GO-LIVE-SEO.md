# Go-live: noindex / sitemap / Search Console

**Stav:** theme **1.7.1** — `sklo_is_production()` + robots filter. Ostrá indexace až PO DNS (host obsahuje `sklospecial.cz`).

## Staging (teď — sslip)

- WP Settings → Reading → „Discourage search engines“ = ON
- Rank Math (pokud aktivní): site-wide noindex
- Theme: `sklo_is_production()` = false → meta `noindex, nofollow`
- `robots.txt` filtr: `Disallow: /` — staging NESMÍ být indexovatelný

## Po DNS (ostrý sklospecial.cz)

Theme automaticky:

- **ne** vypisuje staging noindex meta
- `robots.txt` → Allow + `Sitemap: https://sklospecial.cz/sitemap_index.xml`

Stále ručně:

1. Vypnout Discourage search engines (Reading)
2. Rank Math: remove noindex; enable Sitemap
3. Ověřit robots.txt + sitemap (`/sitemap_index.xml` nebo `/wp-sitemap.xml`)
4. Google Search Console — ověřit property, odeslat sitemap

## SEO hardenings (1.7.1)

- Homepage canonical → `home_url('/')`
- Open Graph + Twitter: home, pillars (katalog), SEO landings, produkt `?kod=`
- Produkt detail: title/description obsahují název produktu
- City pages: `kraj` + `note` v `sklo_seo_cities()` — unikátní věty (remote model)
- Footer: odkaz `/mapa-stranek/`

## Checklist

- [ ] DNS live + HTTPS OK
- [ ] Remove noindex (Reading + Rank Math)
- [ ] Sitemap enabled
- [ ] Search Console sitemap submitted
- [ ] Meta robots = index,follow on production (theme + Rank Math)
- [ ] Canonical / OG smoke na home + 1 city + 1 `?kod=`

Celkový day-of seznam: `docs/GO-LIVE-CHECKLIST.md`.
