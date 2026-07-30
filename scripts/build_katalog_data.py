#!/usr/bin/env python3
"""Build theme product catalog data from shoptet_import.csv.

Writes wp-theme/sklospecial/inc/katalog-produkty-data.php with all products
grouped by website page slug (+ optional section id).
"""

from __future__ import annotations

import csv
import json
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
CSV_PATH = ROOT / "scripts/output/shoptet_import.csv"
OUT_PATH = ROOT / "wp-theme/sklospecial/inc/katalog-produkty-data.php"

# Exact CSV defaultCategory → (page_slug, section_id)
CSV_TO_SLUG: dict[str, tuple[str, str]] = {
    "Skleněné dveře > Posuvné dveře Design-Lux": ("posuvne", "design-lux"),
    "Skleněné dveře > Posuvné dveře Ultra Slim": ("posuvne", "ultra-slim"),
    "Skleněné dveře > Posuvné dveře Loft": ("posuvne", "loft"),
    "Skleněné dveře > Posuvné dveře – trubkový systém": ("posuvne", "trubkovy-system"),
    "Skleněné dveře > Posuvné dveře do pouzdra": ("posuvne", "do-pouzdra"),
    "Skleněné dveře > Kyvné dveře": ("otocne", "otocne"),
    "Skleněné dveře > Otočné dveře": ("otocne", "otocne"),  # legacy export alias
    "Skleněné dveře > Otevírané dveře": ("otevirane", "otevirane"),
    "Skleněné dveře > Dveře s pevnou zárubní": ("dvere-se-zarubni", "pevna"),
    "Skleněné dveře > Dveře s nastavitelnou zárubní": ("dvere-se-zarubni", "nastavitelna"),
    "Skleněné dveře > Dveře s hliníkovou zárubní": ("dvere-se-zarubni", "hlinikova"),
    "Skleněné dveře > Linie Luxe": ("linie-luxe", "luxe"),
    "Skleněné dveře > Rock Glass – industriální linie": ("rock-glass", "rock-glass"),
    "Skleněné dveře > Skleněné příčky a zabudování": ("pricky-a-zabudovani", "pricky"),
    "Skleněné dveře > Vzory skla > Barevné vzory skla": ("vzory-skla", "barevne"),
    "Skleněné dveře > Vzory skla > Matné vzory skla": ("vzory-skla", "matne"),
    "Skleněné dveře > Skladem – rychlá expedice": ("skladem", "skladem"),
    "Sprchové kouty > Skleněné sprchové kouty": ("sprchove-kouty", "sprchy"),
    "Balkony > Francouzské balkony": ("francouzske-balkony", "balkony"),
    "Zábradlí > Zábradlí DIY": ("zabradli", "diy"),
    "Zábradlí > Zábradlí s montáží": ("zabradli", "s-montazi"),
    "Zábradlí > Profily na zábradlí": ("zabradli", "profily"),
    "Zábradlí > Profily FIX na zábradlí": ("zabradli", "profily-fix"),
    "Stříšky > Stříšky skladem": ("strisky", "skladem"),
    "Stříšky > Stříšky systémové": ("strisky", "systemove"),
    "Stříšky > Stříšky s okapem": ("strisky", "s-okapem"),
    "Stříšky > Stříšky s černým kováním": ("strisky", "cerne-kovani"),
    "Stříšky > Stříšky na táhlech": ("strisky", "tahla"),
    "Stříšky > Stříšky na konzolách": ("strisky", "konzoly"),
    "Zrcadla": ("zrcadla", "zrcadla"),
}

MIN_PRICE_FLOOR = 500  # ignore broken/placeholder prices for „od“

# For hub „od“ price, only count these sections (skip cheap accessories).
HUB_MIN_SECTIONS: dict[str, set[str]] = {
    "zabradli": {"diy", "s-montazi"},
}


def php_escape(s: str) -> str:
    return (
        s.replace("\\", "\\\\")
        .replace("'", "\\'")
        .replace("\r", "")
        .replace("\n", "\\n")
    )


def round_od(price: float) -> int:
    return int(round(price / 100.0) * 100)


def load_rows() -> list[dict]:
    with CSV_PATH.open(encoding="utf-8-sig", newline="") as f:
        return list(csv.DictReader(f, delimiter=";"))


def main() -> None:
    rows = load_rows()
    by_slug: dict[str, dict] = {}
    unmatched = 0

    for r in rows:
        cat = (r.get("defaultCategory") or "").strip()
        mapping = CSV_TO_SLUG.get(cat)
        if not mapping:
            unmatched += 1
            continue
        slug, section = mapping
        try:
            price = float(str(r.get("price") or "0").replace(",", "."))
        except ValueError:
            price = 0.0
        # Skip obvious broken placeholders
        if price > 0 and price < 50:
            continue

        name = (r.get("name") or "").strip()
        code = (r.get("code") or "").strip()
        image = (r.get("image") or "").strip()
        if not code or not name:
            continue

        bucket = by_slug.setdefault(
            slug,
            {
                "min_price": None,
                "count": 0,
                "products": [],
                "sections": defaultdict(list),
                "section_mins": {},
            },
        )
        product = {
            "code": code,
            "name": name,
            "price": int(round(price)),
            "image": image,
            "section": section,
        }
        bucket["products"].append(product)
        bucket["sections"][section].append(product)
        bucket["count"] += 1
        if price >= MIN_PRICE_FLOOR:
            sm = bucket["section_mins"].get(section)
            bucket["section_mins"][section] = price if sm is None else min(sm, price)
            allowed = HUB_MIN_SECTIONS.get(slug)
            if allowed is None or section in allowed:
                mp = bucket["min_price"]
                bucket["min_price"] = price if mp is None else min(mp, price)

        # Extra: laminované across door posuv/otocne names
        if "laminov" in name.lower() and slug in {
            "posuvne",
            "otocne",
            "otevirane",
            "dvere-se-zarubni",
            "skladem",
        }:
            lb = by_slug.setdefault(
                "laminovane",
                {
                    "min_price": None,
                    "count": 0,
                    "products": [],
                    "sections": defaultdict(list),
                    "section_mins": {},
                },
            )
            lb["products"].append({**product, "section": "laminovane"})
            lb["sections"]["laminovane"].append(lb["products"][-1])
            lb["count"] += 1
            if price >= MIN_PRICE_FLOOR:
                sm = lb["section_mins"].get("laminovane")
                lb["section_mins"]["laminovane"] = price if sm is None else min(sm, price)
                mp = lb["min_price"]
                lb["min_price"] = price if mp is None else min(mp, price)

    # Sort products by price asc within each slug
    for slug, data in by_slug.items():
        data["products"].sort(key=lambda p: (p["price"], p["name"]))
        for sec_list in data["sections"].values():
            sec_list.sort(key=lambda p: (p["price"], p["name"]))
        if data["min_price"] is not None:
            data["min_price"] = round_od(data["min_price"])
        data["section_mins"] = {
            k: round_od(v) for k, v in sorted(data["section_mins"].items())
        }

    # Emit PHP
    lines: list[str] = [
        "<?php",
        "/**",
        " * Auto-generated product catalog from shoptet_import.csv.",
        f" * Generated: {datetime.now(timezone.utc).strftime('%Y-%m-%d %H:%M:%S')} UTC",
        f" * Products: {sum(d['count'] for d in by_slug.values())} (incl. laminovane overlap)",
        " * Do not edit by hand — run: python3 scripts/build_katalog_data.py",
        " */",
        "",
        "declare(strict_types=1);",
        "",
        "if (!defined('ABSPATH')) {",
        "    exit;",
        "}",
        "",
        "return [",
    ]

    for slug in sorted(by_slug.keys()):
        data = by_slug[slug]
        mp = data["min_price"]
        mp_php = "null" if mp is None else str(int(mp))
        lines.append(f"    '{php_escape(slug)}' => [")
        lines.append(f"        'min_price' => {mp_php},")
        lines.append(f"        'count' => {int(data['count'])},")
        lines.append("        'section_mins' => [")
        for sec, sm in data["section_mins"].items():
            lines.append(f"            '{php_escape(sec)}' => {int(sm)},")
        lines.append("        ],")
        lines.append("        'products' => [")
        for p in data["products"]:
            lines.append(
                "            ["
                f"'code' => '{php_escape(p['code'])}', "
                f"'name' => '{php_escape(p['name'])}', "
                f"'price' => {int(p['price'])}, "
                f"'image' => '{php_escape(p['image'])}', "
                f"'section' => '{php_escape(p['section'])}'"
                "],"
            )
        lines.append("        ],")
        lines.append("    ],")

    lines.append("];")
    lines.append("")

    OUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    OUT_PATH.write_text("\n".join(lines), encoding="utf-8")

    summary = {
        "slugs": {s: {"count": d["count"], "min_price": d["min_price"]} for s, d in sorted(by_slug.items())},
        "unmatched_rows": unmatched,
        "out": str(OUT_PATH.relative_to(ROOT)),
        "bytes": OUT_PATH.stat().st_size,
    }
    print(json.dumps(summary, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
