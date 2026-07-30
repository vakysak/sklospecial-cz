#!/usr/bin/env python3
"""Quba Glass (qubaglass.pl / Shoper) → Czech catalog CSV/JSON scraper.

# pip install -r scripts/requirements-scraper.txt
# or: pip install requests beautifulsoup4

Internal prep only — do not upload scraped images to live WP media
or publish product pages with supplier photos until partnership is clear.
"""

from __future__ import annotations

import argparse
import csv
import json
import logging
import random
import re
import time
from collections import Counter
from pathlib import Path
from typing import Any
from urllib.parse import urljoin, urlparse

import requests
from bs4 import BeautifulSoup

# --- pricing: PLN × 5.8 × 1.04 (4% margin) ---
PLN_TO_CZK = 5.8
MARGIN = 1.04

BASE_URL = "https://qubaglass.pl"
DELAY_MIN = 0.8
DELAY_MAX = 1.2
REQUEST_TIMEOUT = 30

# Category path → Czech label (16). Use „Linie Luxe“, not „Prémiová…“.
CATEGORIES: list[tuple[str, str]] = [
    ("/pl/c/Drzwi-przesuwne-DESIGN-LUX/50", "Posuvné dveře Design-Lux"),
    ("/pl/c/Drzwi-przesuwne-Ultra-Slim/51", "Posuvné dveře Ultra Slim"),
    ("/pl/c/Drzwi-przesuwne-LOFT-ART/57", "Posuvné dveře Loft"),
    ("/pl/c/Drzwi-przesuwne-RUROWE/56", "Posuvné dveře – trubkový systém"),
    ("/pl/c/Drzwi-przesuwne-w-KASECIE/55", "Posuvné dveře do pouzdra"),
    ("/pl/c/Drzwi-wahadlowe/33", "Otočné dveře"),
    ("/pl/c/DRZWI-OTWIERANE/68", "Otevírané dveře"),
    ("/pl/c/Drzwi-z-futryna-stala/37", "Dveře s pevnou zárubní"),
    ("/pl/c/Drzwi-z-futryna-regulowana-TOP/41", "Dveře s nastavitelnou zárubní"),
    ("/pl/c/Drzwi-szklane-z-oscieznica-aluminiowa/79", "Dveře s hliníkovou zárubní"),
    ("/pl/c/Drzwi-szklane-LINIA-LUXE/36", "Linie Luxe"),
    ("/pl/c/Drzwi-szklane-ROCK-GLASS/81", "Rock Glass – industriální linie"),
    ("/pl/c/ZABUDOWY-SZKLANE/87", "Skleněné příčky a zabudování"),
    ("/pl/c/Drzwi-szklane-od-reki/19", "Skladem – rychlá expedice"),
    ("/pl/c/Drzwi-przesuwne-na-wymiar/17", "Posuvné dveře na míru"),
    ("/pl/c/Drzwi-laminowane-kolorowe/20", "Laminované dveře"),
]

# Longer phrases first so multi-word replacements win.
WORD_MAP: dict[str, str] = {
    "laminowane czarne": "laminované černé",
    "laminowane białe": "laminované bílé",
    "przesuwne przesuwane": "posuvné",
    "z lustrem": "se zrcadlem",
    "system czarny": "černý systém",
    "system biały": "bílý systém",
    "na wymiar": "na míru",
    "od ręki": "skladem",
    "bezbarwne": "čiré",
    "matowe": "matné",
    "trawione": "leptané",
    "grafitowe": "grafitové",
    "brązowe": "hnědé",
    "białe": "bílé",
    "czarne": "černé",
    "przesuwne": "posuvné",
    "przesuwane": "posuvné",
    "wahadłowe": "otočné",
    "otwierane": "otevírané",
    "szklane": "skleněné",
    "drzwi": "dveře",
    "wzór": "vzor",
    "grafika": "grafika",
    "BIAŁY": "bílý",
    "biały": "bílý",
}

CSV_COLUMNS = [
    "Název",
    "Kategorie",
    "Cena (CZK)",
    "Cena původní (PLN)",
    "URL obrázku",
    "URL produktu (zdroj)",
    "Popis krátký",
]

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/128.0.0.0 Safari/537.36"
    ),
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Language": "cs,en;q=0.9,pl;q=0.8",
    "Connection": "keep-alive",
}

SCRIPT_DIR = Path(__file__).resolve().parent
DEFAULT_OUT_DIR = SCRIPT_DIR / "output"

log = logging.getLogger("qubaglass")


def polite_sleep() -> None:
    time.sleep(random.uniform(DELAY_MIN, DELAY_MAX))


def absolute_url(href: str | None) -> str:
    if not href:
        return ""
    return urljoin(BASE_URL + "/", href)


def parse_price_pln(text: str) -> float | None:
    """Parse Polish price strings: '2 900,00 zł', nbsp, spaces, commas."""
    if not text:
        return None
    raw = (
        text.replace("\xa0", " ")
        .replace("\u202f", " ")
        .replace("zł", "")
        .replace("PLN", "")
        .strip()
    )
    raw = re.sub(r"[^\d,.\s]", "", raw)
    raw = re.sub(r"\s+", "", raw)
    if not raw:
        return None
    if "," in raw and "." in raw:
        # 1.200,50 → 1200.50
        raw = raw.replace(".", "").replace(",", ".")
    elif "," in raw:
        raw = raw.replace(",", ".")
    try:
        return float(raw)
    except ValueError:
        return None


def price_czk(price_pln: float) -> int:
    return round(price_pln * PLN_TO_CZK * MARGIN)


def translate_name(name: str) -> str:
    """Polish product name → Czech via WORD_MAP (longer keys first)."""
    result = name
    items = sorted(WORD_MAP.items(), key=lambda kv: len(kv[0]), reverse=True)
    # Case-insensitive replace while preserving surrounding text
    for pl, cs in items:
        pattern = re.compile(re.escape(pl), re.IGNORECASE)
        result = pattern.sub(cs, result)
    result = re.sub(r"\s+", " ", result).strip()
    if not result:
        return name
    return result[0].upper() + result[1:]


def short_description(name_cs: str) -> str:
    # Avoid luxusní / exkluzivní / prémiový
    base = name_cs if "dveře" in name_cs.lower() else f"Skleněné dveře {name_cs}"
    return f"{base}. Cena zahrnuje systém a kování."


def fetch(session: requests.Session, url: str) -> BeautifulSoup | None:
    try:
        resp = session.get(url, headers=HEADERS, timeout=REQUEST_TIMEOUT)
        resp.raise_for_status()
        return BeautifulSoup(resp.text, "html.parser")
    except requests.RequestException as exc:
        log.warning("Request failed %s: %s", url, exc)
        return None


def detect_max_page(soup: BeautifulSoup) -> int:
    """Shoper pagination: <pagination-page-number> + 'z N' text / page links."""
    max_page = 1
    tag = soup.select_one("pagination-page-number")
    if tag is not None:
        # sibling text "z 3" lives in parent
        parent = tag.parent
        if parent:
            m = re.search(r"\bz\s+(\d+)\b", parent.get_text(" ", strip=True), re.I)
            if m:
                max_page = max(max_page, int(m.group(1)))
    for a in soup.select(".pagination a[href]"):
        href = a.get("href") or ""
        m = re.search(r"/(\d+)/?(?:\?|$)", href.rstrip("/"))
        # last numeric path segment that looks like page number
        parts = [p for p in href.strip("/").split("/") if p.isdigit()]
        if parts:
            # category id is also numeric; page is typically the last segment
            # e.g. /pl/c/Name/50/2 → last=2; /pl/c/Name/50 → last=50 (category)
            # Prefer links that end with /N after category id path with length>…
            m2 = re.search(r"/c/[^/]+/(\d+)/(\d+)/?$", href)
            if m2:
                max_page = max(max_page, int(m2.group(2)))
            m3 = re.search(r"/c/[^/]+/\d+/1/default/(\d+)/?$", href)
            if m3:
                max_page = max(max_page, int(m3.group(1)))
    return max(1, max_page)


def category_page_url(cat_path: str, page: int) -> str:
    path = cat_path.rstrip("/")
    if page <= 1:
        return absolute_url(path)
    return absolute_url(f"{path}/{page}")


def parse_product_tile(tile, category: str) -> dict[str, Any] | None:
    plink = tile.select_one("product-link")
    name_pl = ""
    price_pln: float | None = None

    if plink is not None:
        name_pl = (plink.get("name") or "").strip()
        raw_price = plink.get("price")
        if raw_price is not None and str(raw_price).strip() != "":
            try:
                price_pln = float(str(raw_price).replace(",", "."))
            except ValueError:
                price_pln = None

    if not name_pl:
        name_el = tile.select_one(".product-tile__name a, .product-tile__name, h2 a, h3 a")
        if name_el:
            name_pl = name_el.get_text(" ", strip=True)

    if price_pln is None:
        price_el = tile.select_one(".price__value, .js__price-value, .product-tile__price .price")
        if price_el:
            price_pln = parse_price_pln(price_el.get_text(" ", strip=True))

    link_el = tile.select_one("a[href*='/pl/p/']")
    product_url = absolute_url(link_el.get("href") if link_el else None)

    img_el = tile.select_one(
        ".product-tile__image_primary img, picture.product-tile__image_primary img, "
        ".product-tile__image img, img"
    )
    img_url = ""
    if img_el is not None:
        img_url = absolute_url(
            img_el.get("src") or img_el.get("data-src") or img_el.get("data-original")
        )

    if not name_pl or not product_url:
        return None
    if price_pln is None:
        log.warning("Missing price for %s", product_url)
        price_pln = 0.0

    name_cs = translate_name(name_pl)
    return {
        "name_pl": name_pl,
        "name_cs": name_cs,
        "category": category,
        "price_pln": price_pln,
        "price_czk": price_czk(price_pln),
        "url": product_url,
        "image": img_url,
        "description": short_description(name_cs),
    }


def get_products_from_page(soup: BeautifulSoup, category: str) -> list[dict[str, Any]]:
    # Scope to category product list — avoid menu / products-of-the-day tiles.
    tiles = soup.select(".product-list .product-tile")
    if not tiles:
        tiles = soup.select("main .tile-grid .product-tile")
    if not tiles:
        tiles = soup.select(".tile-grid .product-tile")

    products: list[dict[str, Any]] = []
    seen_on_page: set[str] = set()
    for tile in tiles:
        item = parse_product_tile(tile, category)
        if not item:
            continue
        if item["url"] in seen_on_page:
            continue
        seen_on_page.add(item["url"])
        products.append(item)
    return products


def scrape_category(
    session: requests.Session, cat_path: str, cat_name: str
) -> list[dict[str, Any]]:
    all_products: list[dict[str, Any]] = []
    first_url = category_page_url(cat_path, 1)
    log.info("  Page 1: %s", first_url)
    soup = fetch(session, first_url)
    if soup is None:
        return all_products

    max_page = detect_max_page(soup)
    page_products = get_products_from_page(soup, cat_name)
    all_products.extend(page_products)
    log.info("  Page 1: %d products (max page ~%d)", len(page_products), max_page)

    if not page_products and max_page <= 1:
        return all_products

    for page in range(2, max_page + 1):
        polite_sleep()
        url = category_page_url(cat_path, page)
        log.info("  Page %d: %s", page, url)
        soup = fetch(session, url)
        if soup is None:
            break
        page_products = get_products_from_page(soup, cat_name)
        log.info("  Page %d: %d products", page, len(page_products))
        if not page_products:
            break
        # Stop if Shoper returned the same set (bad page URL)
        existing = {p["url"] for p in all_products}
        new_items = [p for p in page_products if p["url"] not in existing]
        if not new_items:
            log.info("  No new products on page %d — stopping pagination", page)
            break
        all_products.extend(new_items)

    return all_products


def download_images(session: requests.Session, products: list[dict[str, Any]], img_dir: Path) -> int:
    img_dir.mkdir(parents=True, exist_ok=True)
    saved = 0
    for p in products:
        url = p.get("image") or ""
        if not url:
            continue
        path_part = urlparse(url).path
        name = Path(path_part).name or f"{urlparse(p['url']).path.rstrip('/').split('/')[-1]}.jpg"
        dest = img_dir / name
        if dest.exists():
            continue
        try:
            polite_sleep()
            resp = session.get(url, headers=HEADERS, timeout=REQUEST_TIMEOUT)
            resp.raise_for_status()
            dest.write_bytes(resp.content)
            saved += 1
            log.info("Downloaded image %s", dest.name)
        except requests.RequestException as exc:
            log.warning("Image download failed %s: %s", url, exc)
    return saved


def to_csv_row(p: dict[str, Any]) -> dict[str, Any]:
    return {
        "Název": p["name_cs"],
        "Kategorie": p["category"],
        "Cena (CZK)": p["price_czk"],
        "Cena původní (PLN)": p["price_pln"],
        "URL obrázku": p["image"],
        "URL produktu (zdroj)": p["url"],
        "Popis krátký": p["description"],
    }


def save_outputs(products: list[dict[str, Any]], out_dir: Path) -> tuple[Path, Path]:
    out_dir.mkdir(parents=True, exist_ok=True)
    csv_path = out_dir / "qubaglass_katalog.csv"
    json_path = out_dir / "qubaglass_katalog.json"

    with csv_path.open("w", newline="", encoding="utf-8-sig") as f:
        writer = csv.DictWriter(f, fieldnames=CSV_COLUMNS)
        writer.writeheader()
        for p in products:
            writer.writerow(to_csv_row(p))

    payload = {
        "pricing": {
            "formula": "price_czk = round(price_pln * 5.8 * 1.04)",
            "pln_to_czk": PLN_TO_CZK,
            "margin": MARGIN,
        },
        "count": len(products),
        "products": [
            {
                "name_cs": p["name_cs"],
                "name_pl": p["name_pl"],
                "category": p["category"],
                "price_czk": p["price_czk"],
                "price_pln": p["price_pln"],
                "image_url": p["image"],
                "product_url": p["url"],
                "description_short": p["description"],
            }
            for p in products
        ],
    }
    json_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return csv_path, json_path


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Scrape Quba Glass catalog to Czech CSV/JSON")
    parser.add_argument(
        "--out-dir",
        type=Path,
        default=DEFAULT_OUT_DIR,
        help="Output directory (default: scripts/output)",
    )
    parser.add_argument(
        "--download-images",
        action="store_true",
        help="Also download images to scripts/output/images/ (OFF by default)",
    )
    parser.add_argument(
        "--limit-categories",
        type=int,
        default=0,
        help="Only scrape first N categories (0 = all)",
    )
    parser.add_argument(
        "-v",
        "--verbose",
        action="store_true",
        help="Debug logging",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    logging.basicConfig(
        level=logging.DEBUG if args.verbose else logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        datefmt="%H:%M:%S",
    )

    cats = CATEGORIES
    if args.limit_categories and args.limit_categories > 0:
        cats = CATEGORIES[: args.limit_categories]

    session = requests.Session()
    all_products: list[dict[str, Any]] = []
    seen_urls: set[str] = set()
    per_category: Counter[str] = Counter()
    duplicates = 0

    for i, (cat_path, cat_name) in enumerate(cats, start=1):
        log.info("[%d/%d] Category: %s", i, len(cats), cat_name)
        products = scrape_category(session, cat_path, cat_name)
        kept = 0
        for p in products:
            if p["url"] in seen_urls:
                duplicates += 1
                continue
            seen_urls.add(p["url"])
            all_products.append(p)
            kept += 1
        per_category[cat_name] = kept
        log.info("  Kept %d unique (raw %d)", kept, len(products))
        if i < len(cats):
            polite_sleep()

    csv_path, json_path = save_outputs(all_products, args.out_dir)
    log.info("Wrote %d products → %s", len(all_products), csv_path)
    log.info("Wrote JSON → %s", json_path)
    log.info("--- Summary per category (unique first-seen) ---")
    for name, count in per_category.items():
        log.info("  %s: %d", name, count)
    log.info("Total unique: %d | skipped duplicates: %d", len(all_products), duplicates)

    if args.download_images:
        img_dir = args.out_dir / "images"
        n = download_images(session, all_products, img_dir)
        log.info("Downloaded %d images to %s", n, img_dir)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
