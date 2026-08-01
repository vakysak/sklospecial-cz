# Go-live: noindex / sitemap / Search Console

**Stav položky 11:** dokumentace + theme pojistka pro staging. Ostrá indexace až PO DNS.

## Staging (teď)

- WP Settings → Reading → „Discourage search engines“ = ON
- Rank Math (pokud aktivní): site-wide noindex
- Theme: `sklo_staging_robots` / `sklo_is_production_host()` přidává `noindex, nofollow` meta pokud host není sklospecial.cz
- `robots.txt` filtr: na non-prod `Disallow: /` — staging NESMÍ být indexovatelný

## Po DNS (ostrý sklospecial.cz)

1. Vypnout Discourage search engines
2. Rank Math: remove noindex; enable Sitemap
3. Ověřit robots.txt + sitemap (`/sitemap_index.xml` nebo `/wp-sitemap.xml`)
4. Google Search Console — ověřit property, odeslat sitemap

## Checklist

- [ ] DNS live + HTTPS OK
- [ ] Remove noindex (Reading + Rank Math)
- [ ] Sitemap enabled
- [ ] Search Console sitemap submitted
- [ ] Meta robots = index,follow on production
