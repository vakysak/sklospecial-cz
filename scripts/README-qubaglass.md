# Quba Glass → Czech catalog scraper

Internal tool for reviewing the public [qubaglass.pl](https://qubaglass.pl) catalog (Shoper.pl) before a possible reseller partnership. Output is **CSV + JSON only** — nothing is pushed to live WordPress.

Sklospecial live WP is **not** Shoptet. This CSV is for review / import prep; Shoptet-style mapping can be a later step if needed.

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

CSV columns: `Název`, `Kategorie`, `Cena (CZK)`, `Cena původní (PLN)`, `URL obrázku`, `URL produktu (zdroj)`, `Popis krátký`.

**Images are URL-only by default.** Do not upload scraped supplier photos into live WordPress media or publish product pages with them until usage rights / partnership are clear. Use data for internal prep only.

## Legal / usage note

Use this export for internal catalog preparation as a prospective buyer/reseller. Respect qubaglass.pl terms and copyright; do not republish their product photography or copy wholesale without agreement.

## Tone

Czech product names via `WORD_MAP`. Category **Linie Luxe** (not „Prémiová…“). Short descriptions avoid luxusní / exkluzivní / prémiový.
