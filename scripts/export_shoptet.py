#!/usr/bin/env python3
"""Convert Quba Glass Czech catalog → Shoptet product import CSV.

Reads scripts/output/qubaglass_katalog.csv (or .json) and writes:
  - scripts/output/shoptet_import.csv  (UTF-8 BOM, semicolon)
  - scripts/output/shoptet_import.xlsx (if openpyxl is installed)

Usage:
  python3 scripts/export_shoptet.py
  python3 scripts/export_shoptet.py --in scripts/output/qubaglass_katalog.csv
"""

from __future__ import annotations

import argparse
import csv
import json
import re
from pathlib import Path
from urllib.parse import urljoin, urlparse

PLN_TO_CZK = 5.8
BASE_URL = "https://qubaglass.pl"
ROOT_CATEGORY = "Skleněné dveře"
SUPPLIER = "Quba Glass"

BANNED_WORDS = re.compile(
    r"\b(luxusní|exkluzivní|prémiový|prémiová|prémiové)\b",
    re.IGNORECASE,
)

# Shoptet column order (first three are required left columns).
COLUMNS = [
    "code",
    "pairCode",
    "name",
    "price",
    "purchasePrice",
    "currency",
    "includingVat",
    "percentVat",
    "shortDescription",
    "description",
    "image",
    "defaultCategory",
    "supplier",
    "manufacturer",
    "visibility",
    "unit",
    "itemType",
    "seoTitle",
    "metaDescription",
    "externalId",
]


def clean_text(text: str) -> str:
    text = (text or "").strip()
    text = BANNED_WORDS.sub("", text)
    text = re.sub(r"\s{2,}", " ", text)
    text = re.sub(r"\s+([.,;:])", r"\1", text)
    return text.strip(" ,.;")


def clean_category(category: str) -> str:
    category = (category or "").strip()
    if category.lower() in {
        "prémiová linie luxe",
        "premiova linie luxe",
        "prémiová linie luxe",
    } or "prémiová linie luxe" in category.lower():
        return "Linie Luxe"
    category = re.sub(
        r"(?i)prémiová\s+linie\s+luxe",
        "Linie Luxe",
        category,
    )
    return category.strip() or "Ostatní"


def absolutize_image(url: str) -> str:
    url = (url or "").strip()
    if not url:
        return ""
    if url.startswith("//"):
        return "https:" + url
    if url.startswith("/"):
        return urljoin(BASE_URL + "/", url.lstrip("/"))
    if not urlparse(url).scheme:
        return urljoin(BASE_URL + "/", url)
    return url


def external_id_from_url(source_url: str, fallback: str) -> str:
    """A-Z0-9 only tracking id from product URL slug, else QG####."""
    path = urlparse(source_url or "").path
    slug = path.rstrip("/").split("/")[-1] if path else ""
    # Drop trailing numeric id segment if present as separate path part.
    if slug.isdigit() and "/" in path.rstrip("/"):
        parts = path.rstrip("/").split("/")
        slug = parts[-2] if len(parts) >= 2 else slug
    cleaned = re.sub(r"[^A-Za-z0-9]", "", slug).upper()
    return cleaned[:64] if cleaned else re.sub(r"[^A-Z0-9]", "", fallback.upper())


def load_rows(path: Path) -> list[dict[str, str]]:
    if path.suffix.lower() == ".json":
        data = json.loads(path.read_text(encoding="utf-8"))
        if isinstance(data, dict) and "products" in data:
            data = data["products"]
        rows: list[dict[str, str]] = []
        for item in data:
            rows.append(
                {
                    "Název": str(item.get("Název") or item.get("name") or ""),
                    "Kategorie": str(item.get("Kategorie") or item.get("category") or ""),
                    "Cena (CZK)": str(item.get("Cena (CZK)") or item.get("price_czk") or ""),
                    "Cena původní (PLN)": str(
                        item.get("Cena původní (PLN)") or item.get("price_pln") or ""
                    ),
                    "URL obrázku": str(item.get("URL obrázku") or item.get("image") or ""),
                    "URL produktu (zdroj)": str(
                        item.get("URL produktu (zdroj)") or item.get("url") or ""
                    ),
                    "Popis krátký": str(
                        item.get("Popis krátký") or item.get("short_description") or ""
                    ),
                }
            )
        return rows

    with path.open(encoding="utf-8-sig", newline="") as fh:
        return list(csv.DictReader(fh))


def to_shoptet_row(index: int, raw: dict[str, str]) -> dict[str, str]:
    code = f"QG-{index:04d}"
    name = clean_text(raw.get("Název") or "")
    category = clean_category(raw.get("Kategorie") or "")
    short = clean_text(raw.get("Popis krátký") or "")
    if not short and name:
        short = f"{name}. Cena zahrnuje systém a kování."

    try:
        price = int(float(str(raw.get("Cena (CZK)") or "0").replace(",", ".").replace(" ", "")))
    except ValueError:
        price = 0

    try:
        pln = float(str(raw.get("Cena původní (PLN)") or "0").replace(",", ".").replace(" ", ""))
    except ValueError:
        pln = 0.0
    purchase = int(round(pln * PLN_TO_CZK))

    description = (
        f"{name}. Kategorie: {category}. "
        "Orientační cena — finální nabídka podle rozměrů a provedení."
    )
    meta = short[:150].rstrip()
    if len(short) > 150:
        meta = meta[:147].rstrip() + "…"

    return {
        "code": code,
        "pairCode": "",
        "name": name,
        "price": str(price),
        "purchasePrice": str(purchase),
        "currency": "CZK",
        "includingVat": "1",
        "percentVat": "21",
        "shortDescription": short,
        "description": description,
        "image": absolutize_image(raw.get("URL obrázku") or ""),
        "defaultCategory": f"{ROOT_CATEGORY}|{category}",
        "supplier": SUPPLIER,
        "manufacturer": SUPPLIER,
        "visibility": "visible",
        "unit": "ks",
        "itemType": "product",
        "seoTitle": name,
        "metaDescription": meta,
        "externalId": external_id_from_url(raw.get("URL produktu (zdroj)") or "", code),
    }


def write_csv(rows: list[dict[str, str]], path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8-sig", newline="") as fh:
        writer = csv.DictWriter(
            fh,
            fieldnames=COLUMNS,
            delimiter=";",
            quoting=csv.QUOTE_MINIMAL,
            lineterminator="\n",
        )
        writer.writeheader()
        writer.writerows(rows)


def write_xlsx(rows: list[dict[str, str]], path: Path) -> bool:
    try:
        from openpyxl import Workbook
    except ImportError:
        return False

    wb = Workbook()
    ws = wb.active
    ws.title = "Produkty"
    ws.append(COLUMNS)
    for row in rows:
        ws.append([row.get(col, "") for col in COLUMNS])
    path.parent.mkdir(parents=True, exist_ok=True)
    wb.save(path)
    return True


def main() -> None:
    parser = argparse.ArgumentParser(description="Export Quba Glass catalog for Shoptet import")
    parser.add_argument(
        "--in",
        dest="input_path",
        type=Path,
        default=Path("scripts/output/qubaglass_katalog.csv"),
        help="Source CSV or JSON (default: scripts/output/qubaglass_katalog.csv)",
    )
    parser.add_argument(
        "--out-dir",
        type=Path,
        default=Path("scripts/output"),
        help="Output directory (default: scripts/output)",
    )
    args = parser.parse_args()

    source = args.input_path
    if not source.is_file():
        json_fallback = source.with_suffix(".json")
        if json_fallback.is_file():
            source = json_fallback
        else:
            raise SystemExit(f"Source not found: {args.input_path}")

    raw_rows = load_rows(source)
    products = [to_shoptet_row(i, row) for i, row in enumerate(raw_rows, start=1)]

    csv_path = args.out_dir / "shoptet_import.csv"
    xlsx_path = args.out_dir / "shoptet_import.xlsx"
    write_csv(products, csv_path)
    xlsx_ok = write_xlsx(products, xlsx_path)

    print(f"Wrote {len(products)} products → {csv_path}")
    if xlsx_ok:
        print(f"Wrote XLSX → {xlsx_path}")
    else:
        print("Skipped XLSX (install openpyxl to enable: pip install openpyxl)")


if __name__ == "__main__":
    main()
