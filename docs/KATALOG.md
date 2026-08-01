# Katalog produktů (konfigurátor)

Konfigurátor bere **produkty SklS** z JSON (ne ze seed DB tabulek typů).

## Source of truth

| Soubor | Účel |
|--------|------|
| `wp-theme/sklospecial/assets/data/produkty.json` | WP detail + listing |
| `app/data/produkty.json` | API kopie (full) |
| `app/data/katalog-konfigurator.json` | Slim index dveří (~300 produktů) |

Generuj: `python3 scripts/build_katalog_data.py` (zapíše i `app/data/*`).

## API

| Endpoint | Popis |
|----------|--------|
| `GET /api/katalog` | `typy` z door katalogu + legacy `vzory`/`kovani` |
| `GET /api/produkty?typ=posuvne&q=` | filtrovaný listing |
| `GET /api/produkty/:code` | detail + options |
| `POST /api/konfigurator/odeslat` | lead s `product_code`, `options_selected`, `price_total` |

## Typy (dveře)

- `posuvne` — design-lux, ultra-slim, loft, trubkovy-system
- `do-pouzdra` — do-pouzdra (+ name obsahuje pouzdro)
- `otocne` — kyvné / otočné
- `otevirane` — otevírané
- `celosklenene` — zárubně / luxe / rock-glass
- `nevim` — soft advise bez SKU

## Legacy DB tabulky

`sklo_katalog_typy|vzory|kovani` zůstávají pro zpětnou kompatibilitu / soft advise fallback.
Lead tabulka má navíc `product_code`, `product_name`, `options_selected`, `price_total`.

## Fotka prostoru

Klient může vložit fotku prostoru a posunout rámeček — poměr stran z nejmenší zaměřené šířky × výšky.
