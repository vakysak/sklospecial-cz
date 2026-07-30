# Quba Glass → Czech catalog scraper

Internal tool for reviewing the public [qubaglass.pl](https://qubaglass.pl) catalog (Shoper.pl) before a possible reseller partnership. Output is **CSV + JSON only** — nothing is pushed to live WordPress.

Sklospecial live WP is **not** Shoptet. For a Shoptet shop, regenerate the mapped import with `export_shoptet.py` (see below).

## Pricing

```text
price_czk = round(price_pln * 5.8 * 1.45)   # 45 % margin
```

Example: `899 PLN → round(899 × 5.8 × 1.45) = 7560 CZK`.

Live WP curated door sections under `/sklenene-dvere/` use the same **45 %** orientation prices (see theme `inc/katalog-data.php`).

Optional display note „od“ (from) is for WP templates only; scraper stores integer CZK without the prefix.

## Setup

```bash
cd /Users/josefhampl/sklospecial-cz
python3 -m venv scripts/.venv
source scripts/.venv/bin/activate
pip install -r scripts/requirements-scraper.txt
# equivalent: pip install requests beautifulsoup4
```

## Run

```bash
scripts/.venv/bin/python scripts/qubaglass_scraper.py
```

Useful flags:

| Flag | Meaning |
|------|---------|
| `--limit-categories N` | Scrape only the first N categories (smoke test) |
| `--download-images` | Also save images under `scripts/output/images/` (**off** by default) |
| `--out-dir PATH` | Override output directory |
| `-v` | Verbose logging |

## Output

| File | Description |
|------|-------------|
| `scripts/output/qubaglass_katalog.csv` | UTF-8 BOM CSV for spreadsheet review |
| `scripts/output/qubaglass_katalog.json` | Same data, easier for scripts |
| `scripts/output/shoptet_import.csv` | Shoptet product import (UTF-8 BOM, `;`) — `image` = absolute https URLs |
| `scripts/output/images/` | Local image backup by code (`QG-0001.jpg`) — gitignored |
| `scripts/output/images_map.csv` | Mapping code → remote URL → local path |

CSV columns: `Název`, `Kategorie`, `Cena (CZK)`, `Cena původní (PLN)`, `URL obrázku`, `URL produktu (zdroj)`, `Popis krátký`.

**Images are URL-only by default.** Do not upload scraped supplier photos into live WordPress media or publish product pages with them until usage rights / partnership are clear. Use data for internal prep only.

## Shoptet import

After a scrape (or whenever the katalog CSV/JSON changes):

```bash
python3 scripts/export_shoptet.py
# optional: --in scripts/output/qubaglass_katalog.json
```

This writes `scripts/output/shoptet_import.csv` (semicolon-delimited, UTF-8 with BOM). XLSX is written only if `openpyxl` is installed; CSV alone is enough for Shoptet.

**Import in admin:** Produkty → Import → upload `shoptet_import.csv`.

Notes:

- Codes are stable by source row order: `QG-0001` … `QG-0345`.
- `price` = CZK with 45 % margin; `purchasePrice` = `round(PLN × 5.8)` (cost before margin).
- `includingVat` = `1` (B2C prices including VAT). Switch to `0` in the export script / CSV if your Shoptet catalog uses prices without VAT.
- `percentVat` = `21`; `defaultCategory` = `Skleněné dveře|{Kategorie}`; supplier/manufacturer = `Quba Glass`.
- Category label **Linie Luxe** (not „Prémiová linie Luxe“).

### Product images (Shoptet + local backup)

1. **`shoptet_import.csv` already contains absolute `https://` URLs** in the `image` column (qubaglass.pl CDN). Shoptet should fetch those images during product import — keep remote URLs for import; local file paths are **not** valid for Shoptet CSV import.
2. **Local copies** (backup / later re-hosting on your own CDN) live in `scripts/output/images/` as `{code}.jpg` / `.png` (e.g. `QG-0001.jpg`). Download with:

```bash
scripts/.venv/bin/python scripts/download_images.py
```

   Mapping: `scripts/output/images_map.csv` (`code`, `remote_url`, `local_path`, `status`). The folder is gitignored — do not commit binary images.
3. **Legal:** use supplier photos in a public shop only with a clear supplier agreement / usage rights. Until then, treat downloads as internal backup only.

The scraper’s `--download-images` flag also saves files under `scripts/output/images/`, but names them from the remote filename. Prefer `download_images.py` when you need stable names by product code.

## Legal / usage note

Use this export for internal catalog preparation as a prospective buyer/reseller. Respect qubaglass.pl terms and copyright; do not republish their product photography or copy wholesale without agreement. Public shop use of supplier images requires supplier agreement.

## Tone

Czech product names via `WORD_MAP`. Category **Linie Luxe** (not „Prémiová…“). Short descriptions avoid luxusní / exkluzivní / prémiový.
