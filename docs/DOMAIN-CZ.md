# Doména sklospecial.cz (příprava)

**Stav:** živý web = **sklospecial.eu**. Doména `.cz` přijde později — theme už `.cz` v produkčních hostech má.

## `sklo_is_production()`

V `wp-theme/sklospecial/functions.php` jsou produkční hosty:

- `sklospecial.eu` (+ `www`)
- `sklospecial.cz` (+ `www`)

Dokud DNS `.cz` neukazuje na stejný WP, nic se nemění. Až `.cz` míří sem, theme automaticky vypne staging `noindex` / povolí robots (stejně jako na `.eu`).

## Až DNS `.cz` přijde

1. Coolify: připoj `sklospecial.cz` (+ `www`) na stejnou WP službu jako `.eu` (LE cert).
2. Zvol **jeden kanonický host** (doporučení: nejdřív drž `.eu` jako primary, nebo přepni na `.cz` až bude brand ready).
3. Redirect druhého hostu → kanonický (Traefik / Rank Math / mu-plugin).
4. Canonicaly a `home_url()` musí sedět s WP Address / Site Address (Settings → General).
5. Search Console: property pro nový host + sitemap.
6. Aktualizuj e-maily / OG URL jen pokud měníš kanonickou doménu (mail From zůstává `@sklospecial.eu` kvůli SPF Webglobe — viz `GO-LIVE-SMTP.md`).

## Co nerušit

- `.eu` musí dál fungovat (redirect nebo alias), dokud owner neřekne jinak.
- API: `api.sklospecial.eu` (případně později `api.sklospecial.cz` + CORS).

## GA4

Option `sklo_ga4_id` (Nastavení → Sklospeciál) nebo `define('SKLO_GA4_ID', 'G-…')`. Prázdné = bez skriptu. Gtag se načte až po souhlasu s analytickými cookies.
