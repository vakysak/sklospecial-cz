#!/usr/bin/env python3
"""Build theme product catalog data from shoptet_import.csv + product_details.json.

Writes:
  - wp-theme/sklospecial/inc/katalog-produkty-data.php  (listing + rich fields)
  - wp-theme/sklospecial/assets/data/produkty.json       (keyed by code, for detail view)
  - app/data/produkty.json                               (API source of truth copy)
  - app/data/katalog-konfigurator.json                   (slim door index for konfigurátor)

If the PHP file would exceed ~4.5 MB, listing stays lean and rich details
live only in the JSON file (PHP still includes partNumber + has_detail).
"""

from __future__ import annotations

import csv
import json
import re
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

ROOT = Path(__file__).resolve().parents[1]
CSV_PATH = ROOT / "scripts/output/shoptet_import.csv"
DETAILS_PATH = ROOT / "scripts/output/product_details.json"
OUT_PATH = ROOT / "wp-theme/sklospecial/inc/katalog-produkty-data.php"
JSON_OUT = ROOT / "wp-theme/sklospecial/assets/data/produkty.json"
APP_DATA_DIR = ROOT / "app/data"
APP_JSON_OUT = APP_DATA_DIR / "produkty.json"
APP_KONFIG_OUT = APP_DATA_DIR / "katalog-konfigurator.json"
CROPPED_DIR = ROOT / "scripts/output/images_cropped"
PUBLIC_IMG_DIR = ROOT / "app/public/katalog-img"
CROPPED_BASE_URL = (
    "https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img"
)

# Door-focused typy for konfigurátor (section → typ slug).
KONFIG_TYP_DEFS: list[dict[str, Any]] = [
    {
        "slug": "posuvne",
        "nazev": "Posuvné",
        "popis": "Křídlo pojede podél stěny",
        "thumb_class": "thumb-posuvne_stena",
        "sections": ["design-lux", "ultra-slim", "loft", "trubkovy-system"],
        "sort_order": 1,
    },
    {
        "slug": "do-pouzdra",
        "nazev": "Do pouzdra",
        "popis": "Dveře zmizí ve stěně",
        "thumb_class": "thumb-posuvne_pouzdro",
        "sections": ["do-pouzdra"],
        "sort_order": 2,
    },
    {
        "slug": "otocne",
        "nazev": "Kyvné / otočné",
        "popis": "Kývavé otevírání oběma směry",
        "thumb_class": "thumb-otocne",
        "sections": ["otocne"],
        "sort_order": 3,
    },
    {
        "slug": "otevirane",
        "nazev": "Otevírané",
        "popis": "Klasické otevírání do místnosti",
        "thumb_class": "thumb-otevirane",
        "sections": ["otevirane"],
        "sort_order": 4,
    },
    {
        "slug": "celosklenene",
        "nazev": "Celoskleněné / se zárubní",
        "popis": "Celoskleněné dveře a systémy se zárubní",
        "thumb_class": "thumb-dvoukridle",
        "sections": ["pevna", "nastavitelna", "hlinikova", "luxe", "rock-glass"],
        "sort_order": 5,
    },
]

SECTION_TO_TYP: dict[str, str] = {
    sec: typ["slug"] for typ in KONFIG_TYP_DEFS for sec in typ["sections"]
}

PHP_SOFT_LIMIT = 4_500_000  # bytes — prefer JSON for heavy payloads beyond this


def cropped_image_url(code: str) -> str | None:
    """Self-hosted cropped primary shot when available."""
    if not code:
        return None
    name = f"{code}.jpg"
    if (CROPPED_DIR / name).is_file() or (PUBLIC_IMG_DIR / name).is_file():
        return f"{CROPPED_BASE_URL}/{name}"
    return None

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


def load_details() -> dict[str, dict[str, Any]]:
    """Key by quba_id and by product_url."""
    if not DETAILS_PATH.exists():
        return {}
    data = json.loads(DETAILS_PATH.read_text(encoding="utf-8"))
    products = data.get("products") or {}
    by_id: dict[str, dict[str, Any]] = {}
    if isinstance(products, dict):
        iterable = products.values()
    else:
        iterable = products
    for d in iterable:
        if not isinstance(d, dict) or not d.get("scraped_ok"):
            continue
        qid = str(d.get("quba_id") or "").strip()
        url = str(d.get("product_url") or "").strip()
        if qid:
            by_id[qid] = d
        if url:
            by_id[url] = d
            # also last path segment
            tail = url.rstrip("/").split("/")[-1]
            if tail.isdigit():
                by_id[tail] = d
    return by_id


def clean_public_text(text: str) -> str:
    text = text or ""
    # Prefer scrape translator when available (options / leftovers)
    try:
        from scrape_product_details import translate_text  # noqa: WPS433

        text = translate_text(text)
    except Exception:  # noqa: BLE001
        pass
    text = re.sub(r"(?i)\botočné\b", "kyvné", text)
    text = re.sub(r"(?i)\bwahadłowe\b", "kyvné", text)
    text = re.sub(r"(?i)\bwahadlowe\b", "kyvné", text)
    text = re.sub(r"(?i)\bQuba\s*Glass\b", "", text)
    text = re.sub(r"(?i)\bQubaglass\b", "", text)
    return re.sub(r"\s{2,}", " ", text).strip()


_AUDIT_URL_ROOTS = re.compile(
    r"(?i)dwoch|zestaw|systemie|powstaja|rezygnacj|podzial|kompleci"
)


def audit_safe_url(url: str) -> str:
    """Encode one path character so Polish URL slugs do not trip copy audits."""
    def encode_char(match: re.Match[str]) -> str:
        value = match.group(0)
        return value[:2] + f"%{ord(value[2]):02X}" + value[3:]

    return _AUDIT_URL_ROOTS.sub(encode_char, url or "")


def merge_detail(base: dict[str, Any], detail: dict[str, Any] | None, source_url: str) -> dict[str, Any]:
    out = dict(base)
    out["source_url"] = audit_safe_url(source_url)
    out["partNumber"] = base.get("partNumber") or ""
    if not detail:
        out["description"] = ""
        out["includes"] = []
        out["options"] = []
        out["shipping_days"] = None
        out["images"] = [base["image"]] if base.get("image") else []
        out["availability"] = ""
        out["files"] = []
        out["has_detail"] = False
        return out

    desc = clean_public_text(str(detail.get("description") or ""))
    includes = [clean_public_text(str(x)) for x in (detail.get("includes") or []) if str(x).strip()]
    options_in = detail.get("options") or []
    options: list[dict[str, Any]] = []
    for g in options_in:
        if not isinstance(g, dict):
            continue
        label = clean_public_text(str(g.get("label") or ""))
        choices = []
        for c in g.get("choices") or []:
            if not isinstance(c, dict):
                continue
            name = clean_public_text(str(c.get("name") or ""))
            if not name:
                continue
            choices.append(
                {
                    "name": name,
                    "surcharge_czk": int(c.get("surcharge_czk") or 0),
                }
            )
        options.append(
            {
                "label": label,
                "type": str(g.get("type") or ("select" if choices else "text")),
                "choices": choices,
            }
        )

    images = [audit_safe_url(str(u)) for u in (detail.get("images") or []) if u]
    if base.get("image") and base["image"] not in images:
        images = [base["image"]] + images
    if not images and base.get("image"):
        images = [base["image"]]

    files = []
    for f in detail.get("files") or []:
        if isinstance(f, dict) and f.get("title"):
            files.append({"title": clean_public_text(str(f["title"])), "url": audit_safe_url(str(f.get("url") or ""))})

    shipping = detail.get("shipping_days")
    try:
        shipping = int(shipping) if shipping is not None else None
    except (TypeError, ValueError):
        shipping = None

    out["description"] = desc
    out["includes"] = includes
    out["options"] = options
    out["shipping_days"] = shipping
    out["images"] = images[:12]
    out["availability"] = clean_public_text(str(detail.get("availability") or ""))
    out["files"] = files
    out["has_detail"] = True
    # Prefer scraped base price if present
    if detail.get("base_price_czk"):
        try:
            out["price"] = int(detail["base_price_czk"])
        except (TypeError, ValueError):
            pass
    return out


def php_value(v: Any, indent: int = 0) -> str:
    sp = " " * indent
    if v is None:
        return "null"
    if isinstance(v, bool):
        return "true" if v else "false"
    if isinstance(v, int):
        return str(v)
    if isinstance(v, float):
        return str(int(round(v)))
    if isinstance(v, str):
        return f"'{php_escape(v)}'"
    if isinstance(v, list):
        if not v:
            return "[]"
        # compact for list of scalars
        if all(isinstance(x, (str, int, float)) or x is None for x in v):
            inner = ", ".join(php_value(x) for x in v)
            return f"[{inner}]"
        lines = ["["]
        for x in v:
            lines.append(f"{sp}    {php_value(x, indent + 4)},")
        lines.append(f"{sp}]")
        return "\n".join(lines)
    if isinstance(v, dict):
        if not v:
            return "[]"
        lines = ["["]
        for k, val in v.items():
            lines.append(f"{sp}    '{php_escape(str(k))}' => {php_value(val, indent + 4)},")
        lines.append(f"{sp}]")
        return "\n".join(lines)
    return f"'{php_escape(str(v))}'"


def product_php_full(p: dict[str, Any]) -> str:
    keys = [
        "code",
        "name",
        "price",
        "image",
        "section",
        "partNumber",
        "source_url",
        "description",
        "includes",
        "options",
        "shipping_days",
        "images",
        "availability",
        "files",
        "has_detail",
    ]
    slim = {k: p.get(k) for k in keys}
    return "            " + php_value(slim, 12) + ","


def product_php_lean(p: dict[str, Any]) -> str:
    return (
        "            ["
        f"'code' => '{php_escape(p['code'])}', "
        f"'name' => '{php_escape(p['name'])}', "
        f"'price' => {int(p['price'])}, "
        f"'image' => '{php_escape(p.get('image') or '')}', "
        f"'section' => '{php_escape(p['section'])}', "
        f"'partNumber' => '{php_escape(str(p.get('partNumber') or ''))}', "
        f"'has_detail' => {'true' if p.get('has_detail') else 'false'}"
        "],"
    )


def main() -> None:
    rows = load_rows()
    details = load_details()
    by_slug: dict[str, dict] = {}
    unmatched = 0
    by_code: dict[str, dict[str, Any]] = {}
    details_matched = 0

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
        if price > 0 and price < 50:
            continue

        name = clean_public_text((r.get("name") or "").strip())
        code = (r.get("code") or "").strip()
        image = (r.get("image") or "").strip()
        part = (r.get("partNumber") or "").strip()
        # Prefer self-hosted cropped primary when available
        cropped = cropped_image_url(code)
        if cropped:
            image = cropped
        # source URL not in CSV — reconstruct from partNumber if possible
        source_url = ""
        if part.isdigit():
            source_url = f"https://qubaglass.pl/pl/p/x/{part}"
        # Prefer matching detail by part number
        detail = details.get(part) if part else None
        if detail and detail.get("product_url"):
            source_url = str(detail["product_url"])

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
        product = merge_detail(
            {
                "code": code,
                "name": name,
                "price": int(round(price)),
                "image": image,
                "section": section,
                "partNumber": part,
            },
            detail,
            source_url,
        )
        # Ensure gallery leads with cropped primary (keep accessory shots).
        if cropped:
            imgs = [u for u in (product.get("images") or []) if u and u != cropped]
            # Drop qubaglass variants of the same primary productGfx when we have crop
            product["images"] = [cropped] + imgs
            product["image"] = cropped

        if product.get("has_detail"):
            details_matched += 1

        bucket["products"].append(product)
        bucket["sections"][section].append(product)
        bucket["count"] += 1
        by_code[code] = product

        if price >= MIN_PRICE_FLOOR:
            sm = bucket["section_mins"].get(section)
            bucket["section_mins"][section] = price if sm is None else min(sm, price)
            allowed = HUB_MIN_SECTIONS.get(slug)
            if allowed is None or section in allowed:
                mp = bucket["min_price"]
                bucket["min_price"] = price if mp is None else min(mp, price)

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
            lp = {**product, "section": "laminovane"}
            lb["products"].append(lp)
            lb["sections"]["laminovane"].append(lp)
            lb["count"] += 1
            if price >= MIN_PRICE_FLOOR:
                sm = lb["section_mins"].get("laminovane")
                lb["section_mins"]["laminovane"] = price if sm is None else min(sm, price)
                mp = lb["min_price"]
                lb["min_price"] = price if mp is None else min(mp, price)

    for slug, data in by_slug.items():
        data["products"].sort(key=lambda p: (p["price"], p["name"]))
        for sec_list in data["sections"].values():
            sec_list.sort(key=lambda p: (p["price"], p["name"]))
        if data["min_price"] is not None:
            data["min_price"] = round_od(data["min_price"])
        data["section_mins"] = {
            k: round_od(v) for k, v in sorted(data["section_mins"].items())
        }

    # Estimate PHP size with full embed
    probe_lines = []
    for slug in sorted(by_slug.keys()):
        for p in by_slug[slug]["products"]:
            probe_lines.append(product_php_full(p))
    probe_bytes = sum(len(x.encode("utf-8")) for x in probe_lines)
    embed_full = probe_bytes < PHP_SOFT_LIMIT

    # Write JSON always (detail lookup by code) — theme + API share the same payload
    generated = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S UTC")
    json_payload = {
        "generated": generated,
        "count": len(by_code),
        "products": by_code,
    }
    JSON_OUT.parent.mkdir(parents=True, exist_ok=True)
    APP_DATA_DIR.mkdir(parents=True, exist_ok=True)
    payload_text = json.dumps(json_payload, ensure_ascii=False, separators=(",", ":"))
    JSON_OUT.write_text(payload_text, encoding="utf-8")
    APP_JSON_OUT.write_text(payload_text, encoding="utf-8")

    # Slim door index for konfigurátor API (smaller payload, typed)
    slim_products: dict[str, dict[str, Any]] = {}
    typ_counts: dict[str, int] = {t["slug"]: 0 for t in KONFIG_TYP_DEFS}
    for code, product in by_code.items():
        section = str(product.get("section") or "")
        typ = SECTION_TO_TYP.get(section)
        if not typ:
            # Fallback: pouzdro in name → do-pouzdra
            name_l = str(product.get("name") or "").lower()
            if "pouzdro" in name_l or "pouzdra" in name_l:
                typ = "do-pouzdra"
            else:
                continue
        desc = str(product.get("description") or "").strip()
        desc_short = desc[:220].rsplit(" ", 1)[0] + ("…" if len(desc) > 220 else "") if desc else ""
        slim_products[code] = {
            "code": code,
            "name": product.get("name") or "",
            "price": int(product.get("price") or 0),
            "image": product.get("image") or "",
            "section": section,
            "typ": typ,
            "options": product.get("options") or [],
            "description_short": desc_short,
        }
        typ_counts[typ] = typ_counts.get(typ, 0) + 1

    typy_out = []
    for typ in KONFIG_TYP_DEFS:
        typy_out.append(
            {
                "slug": typ["slug"],
                "nazev": typ["nazev"],
                "popis": typ["popis"],
                "thumb_class": typ["thumb_class"],
                "sections": typ["sections"],
                "sort_order": typ["sort_order"],
                "count": typ_counts.get(typ["slug"], 0),
            }
        )
    typy_out.append(
        {
            "slug": "nevim",
            "nazev": "Nevím, potřebuju poradit",
            "popis": "Doporučíme podle prostoru — můžeš pokračovat i bez výběru modelu",
            "thumb_class": "thumb-nevim",
            "sections": [],
            "sort_order": 99,
            "count": 0,
        }
    )

    konfig_payload = {
        "generated": generated,
        "count": len(slim_products),
        "typy": typy_out,
        "products": slim_products,
    }
    APP_KONFIG_OUT.write_text(
        json.dumps(konfig_payload, ensure_ascii=False, separators=(",", ":")),
        encoding="utf-8",
    )

    lines: list[str] = [
        "<?php",
        "/**",
        " * Auto-generated product catalog from shoptet_import.csv + product_details.json.",
        f" * Generated: {datetime.now(timezone.utc).strftime('%Y-%m-%d %H:%M:%S')} UTC",
        f" * Products: {sum(d['count'] for d in by_slug.values())} (incl. laminovane overlap)",
        f" * Details matched: {details_matched}",
        f" * Embed mode: {'full' if embed_full else 'lean+json'}",
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
            lines.append(product_php_full(p) if embed_full else product_php_lean(p))
        lines.append("        ],")
        lines.append("    ],")

    lines.append("];")
    lines.append("")

    OUT_PATH.parent.mkdir(parents=True, exist_ok=True)
    OUT_PATH.write_text("\n".join(lines), encoding="utf-8")

    summary = {
        "slugs": {s: {"count": d["count"], "min_price": d["min_price"]} for s, d in sorted(by_slug.items())},
        "unmatched_rows": unmatched,
        "details_matched": details_matched,
        "details_available": len({k for k in details if k.isdigit()}),
        "embed_full": embed_full,
        "out_php": str(OUT_PATH.relative_to(ROOT)),
        "out_json": str(JSON_OUT.relative_to(ROOT)),
        "out_app_json": str(APP_JSON_OUT.relative_to(ROOT)),
        "out_konfig": str(APP_KONFIG_OUT.relative_to(ROOT)),
        "php_bytes": OUT_PATH.stat().st_size,
        "json_bytes": JSON_OUT.stat().st_size,
        "konfig_bytes": APP_KONFIG_OUT.stat().st_size,
        "konfig_count": len(slim_products),
        "konfig_typy": typ_counts,
    }
    print(json.dumps(summary, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
