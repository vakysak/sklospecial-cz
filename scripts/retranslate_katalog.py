#!/usr/bin/env python3
"""Re-apply improved PL→CS translation on existing katalog (no re-scrape).

Reads scripts/output/qubaglass_katalog.json (needs name_pl), rewrites
name_cs + short description, writes CSV/JSON. Then run:
  python3 scripts/export_shoptet.py
  python3 scripts/build_katalog_data.py
"""

from __future__ import annotations

import argparse
import csv
import json
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPT_DIR))

from qubaglass_scraper import (  # noqa: E402
    CSV_COLUMNS,
    short_description,
    translate_name,
)

DEFAULT_JSON = SCRIPT_DIR / "output" / "qubaglass_katalog.json"
DEFAULT_CSV = SCRIPT_DIR / "output" / "qubaglass_katalog.csv"


def _image(p: dict) -> str:
    return (p.get("image_url") or p.get("image") or "").strip()


def _url(p: dict) -> str:
    return (p.get("product_url") or p.get("url") or "").strip()


def to_csv_row(p: dict) -> dict:
    return {
        "Název": p["name_cs"],
        "Kategorie": p["category"],
        "Cena (CZK)": p["price_czk"],
        "Cena původní (PLN)": p["price_pln"],
        "URL obrázku": _image(p),
        "URL produktu (zdroj)": _url(p),
        "Popis krátký": p.get("description_short") or p.get("description") or "",
    }


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--json", type=Path, default=DEFAULT_JSON)
    ap.add_argument("--csv", type=Path, default=DEFAULT_CSV)
    args = ap.parse_args()

    payload = json.loads(args.json.read_text(encoding="utf-8"))
    products = payload.get("products") or []
    if not products:
        print("No products in", args.json, file=sys.stderr)
        return 1

    changed = 0
    examples: list[tuple[str, str]] = []
    for p in products:
        name_pl = (p.get("name_pl") or "").strip()
        old_cs = (p.get("name_cs") or "").strip()
        if not name_pl:
            name_pl = old_cs
        new_cs = translate_name(name_pl)
        cat = p.get("category") or ""
        new_desc = short_description(new_cs, cat)
        if new_cs != old_cs:
            changed += 1
            if len(examples) < 25:
                examples.append((old_cs, new_cs))
        p["name_cs"] = new_cs
        p["description_short"] = new_desc
        p["description"] = new_desc
        # Normalize keys to scraper schema
        p["image_url"] = _image(p)
        p["product_url"] = _url(p)

    payload["count"] = len(products)
    args.json.write_text(
        json.dumps(payload, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )

    args.csv.parent.mkdir(parents=True, exist_ok=True)
    with args.csv.open("w", newline="", encoding="utf-8-sig") as f:
        writer = csv.DictWriter(f, fieldnames=CSV_COLUMNS)
        writer.writeheader()
        for p in products:
            writer.writerow(to_csv_row(p))

    print(f"products={len(products)} names_changed={changed}")
    print("examples (old → new):")
    for old, new in examples:
        print(f"  - {old}")
        print(f"    → {new}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
