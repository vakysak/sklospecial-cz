# Quba Glass → Czech catalog scraper

Internal tool for reviewing the public [qubaglass.pl](https://qubaglass.pl) catalog (Shoper.pl) before a possible reseller partnership. Output is **CSV + JSON only** — nothing is pushed to live WordPress.

Sklospecial live WP is **not** Shoptet. For a Shoptet shop, regenerate the mapped import with `export_shoptet.py` (see below).

## Coverage

Full assortment scrape (~900–1010 unique products after URL dedupe):

| Group | Categories |
|-------|------------|
| Skleněné dveře | 16 door / partition lines |
| Vzory skla | Barevné vzory, Matné vzory |
| Sprchové kouty | Skleněné sprchové kouty |
| Balkony | Francouzské balkony |
| Zábradlí | DIY, s montáží, Profily, Profily FIX |
| Stříšky | skladem, systémové, s okapem, černé kování, na táhlech, na konzolách |
| Ostatní | Zrcadla, Sklo |

Products appearing in multiple categories are deduplicated by product URL (first category seen wins; leaf categories are listed intentionally).

## Pricing

```text
price_czk     = round(price_pln * 5.8 * 1.45)   # sell price, 45 % margin
purchasePrice = round(price_pln * 5.8)          # cost in CZK (Shoptet)
```

Example: `899 PLN → sell 7560 CZK`, purchase `5214 CZK`.

Live WP curated door sections under `/sklenene-dvere/` use the same **45 %** orientation prices (see theme `inc/katalog-data.php`).

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

Full scrape takes several minutes (polite ~1 s delay between pages).

## Output

| File | Description |
|------|-------------|
| `scripts/output/qubaglass_katalog.csv` | UTF-8 BOM CSV for spreadsheet review |
| `scripts/output/qubaglass_katalog.json` | Same data, easier for scripts |
| `scripts/output/shoptet_import.csv` | Shoptet product import (UTF-8 BOM, `;`) |
| `scripts/output/shoptet_categories.txt` | Unique `defaultCategory` paths for manual category setup |
| `scripts/output/images/` | Local image backup by code (`SklS-0001.jpg`) — gitignored |
| `scripts/output/images_map.csv` | Mapping code → remote URL → local path |

Katalog CSV columns: `Název`, `Kategorie`, `Cena (CZK)`, `Cena původní (PLN)`, `URL obrázku`, `URL produktu (zdroj)`, `Popis krátký`.

**Images are URL-only by default.** Do not upload scraped supplier photos into live WordPress media or publish product pages with them until usage rights / partnership are clear.

## Shoptet import

### 1) Regenerate export

```bash
python3 scripts/export_shoptet.py
# optional: --in scripts/output/qubaglass_katalog.json
```

### 2) Create categories (if needed)

Open `scripts/output/shoptet_categories.txt`. Shoptet often auto-creates categories from `defaultCategory`, but many shops prefer creating the tree first under **Kategorie**. Hierarchy uses ` > ` (not `|`):

- `Skleněné dveře > {line}`
- `Skleněné dveře > Vzory skla > {pattern}`
- `Sprchové kouty > Skleněné sprchové kouty`
- `Balkony > Francouzské balkony`
- `Zábradlí > {line}`
- `Stříšky > {line}`
- `Zrcadla` / `Sklo`

Also create availability name **Na objednávku** if it does not exist yet.

### 3) Import products

**Produkty → Import** → upload `shoptet_import.csv`.

### Column meanings (e-shop settings)

| Column | Value / meaning |
|--------|-----------------|
| `code` | `SklS-0001` … by row order |
| `pairCode` | empty |
| `name` | Czech product name |
| `price` | sell CZK (`PLN × 5.8 × 1.45`) |
| `purchasePrice` | cost CZK (`PLN × 5.8`) |
| `currency` | `CZK` |
| `includingVat` | `1` (B2C incl. VAT) |
| `percentVat` | `21` |
| `priceRatio` | `1` |
| `shortDescription` / `description` | Czech stubs by category type |
| `image` | absolute `https://` URL (Shoptet fetches on import) |
| `defaultCategory` | hierarchy with ` > ` |
| `categoryText` | parent breadcrumb when useful |
| `supplier` | `Quba Glass` (order / supplier mapping) |
| `manufacturer` | `Sklospeciál` (public brand) |
| `partNumber` | Quba numeric product id from source URL |
| `productVisibility` | `visible` |
| `unit` | `ks` |
| `itemType` | `product` |
| `stock` | empty (made-to-order; avoid false stock) |
| `availability` | `Na objednávku` |
| `negativeAmount` | `1` (orders allowed without stock) |
| `atypicalShipping` | `1` (glass) |
| `freeShipping` | `0` |
| `seoTitle` | = name |
| `metaDescription` | short desc truncated |
| `externalId` | alphanumeric from source URL |
| `weight` | empty (unknown) |

### Product images (Shoptet + local backup)

1. **`shoptet_import.csv` already contains absolute `https://` URLs** in the `image` column. Keep remote URLs for import; local paths are **not** valid for Shoptet CSV import.
2. **Local copies** (backup) via:

```bash
scripts/.venv/bin/python scripts/download_images.py
```

   Mapping: `scripts/output/images_map.csv`. Folder is gitignored — do not commit binaries.
3. **Legal:** public use of supplier photos requires agreement.

## Cropping supplier logo from product photos

Quba product thumbs often include a white + blue QUBAGLASS logo strip at the bottom.

**Live site:** primary shots are cropped locally and served from the API static host:

`https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/SklS-XXXX.jpg`

```bash
# Detect white+blue logo band (fallback bottom 13%), JPEG q85 → flat files
scripts/.venv/bin/python scripts/crop_product_logos.py \
  --also-dst ../app/public/katalog-img

# Point theme JSON/PHP image URLs at katalog-img
scripts/.venv/bin/python scripts/remap_images_to_cropped.py
```

Cropped binaries (`app/public/katalog-img/*.jpg`, ~47 MB) are gitignored — rsync/scp
them onto the Coolify API container’s `/app/public/katalog-img/` after deploy.
CSS `--sklo-quba-logo` remains as a harmless backup clip.

## Legal / usage note

Use this export for internal catalog preparation as a prospective buyer/reseller. Respect qubaglass.pl terms and copyright; do not republish their product photography or copy wholesale without agreement.

## Tone

Czech product names via `WORD_MAP`. Category **Linie Luxe** (not „Prémiová…“). Short descriptions avoid luxusní / exkluzivní / prémiový.
