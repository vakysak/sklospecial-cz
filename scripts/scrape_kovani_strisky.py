#!/usr/bin/env python3
"""Refresh wp-theme/sklospecial/assets/data/kovani-strisky.json from luxusnikovani.cz.

Scrapes variant prices (with VAT), options/surcharges, and descriptions.
Keeps known-good card image URLs (hotlink) unless --images is passed later.
"""

from __future__ import annotations

import html as htmlmod
import json
import re
import time
import unicodedata
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "wp-theme" / "sklospecial" / "assets" / "data" / "kovani-strisky.json"
UA = "Mozilla/5.0 (compatible; SklospecialBot/1.0; +https://sklospecial.eu)"


def slugify(s: str) -> str:
    s = unicodedata.normalize("NFKD", s)
    s = "".join(c for c in s if not unicodedata.combining(c))
    s = s.lower()
    return re.sub(r"[^a-z0-9]+", "_", s).strip("_") or "opt"


def fetch(url: str) -> str:
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Language": "cs"})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read().decode("utf-8", errors="ignore")


def parse_price_vat(text: str) -> int | None:
    text = htmlmod.unescape(text).replace("\xa0", " ").replace("\u202f", " ")
    m = re.search(r"([\d][\d\s]*)\s*Kč\s*s\s*DPH", text, re.I)
    if not m:
        return None
    return int(re.sub(r"\s+", "", m.group(1)))


def strip_tags(s: str) -> str:
    s = re.sub(r"<br\s*/?>", "\n", s, flags=re.I)
    s = re.sub(r"</p\s*>", "\n", s, flags=re.I)
    s = re.sub(r"<li[^>]*>", "- ", s, flags=re.I)
    s = re.sub(r"<[^>]+>", "", s)
    s = htmlmod.unescape(s).replace("\xa0", " ")
    s = re.sub(r"[ \t]+", " ", s)
    s = re.sub(r" *\n *", "\n", s)
    return re.sub(r"\n{3,}", "\n\n", s).strip()


def clean_desc(desc: str) -> str:
    desc = (
        desc.replace("stříškuse", "stříšku se")
        .replace("serie SEASONS", "série SEASONS")
        .replace("sko o tloušťce", "sklo o tloušťce")
        .replace("ma zeď", "na zeď")
        .replace(" . ", ". ")
    )
    parts = re.split(r"(?<=[.!?])\s+", desc.strip())
    lines: list[str] = []
    buf = ""
    for part in parts:
        part = part.strip()
        if not part:
            continue
        if buf and len(buf) + len(part) > 160:
            lines.append(buf)
            buf = part
        else:
            buf = (buf + " " + part).strip()
    if buf:
        lines.append(buf)
    return "\n".join(lines)


def sort_mm_choices(choices: list[dict]) -> list[dict]:
    def key(c: dict):
        m = re.search(r"([\d]+(?:[.,]\d+)?)", c["name"])
        if not m:
            return (1, c["name"])
        return (0, float(m.group(1).replace(",", ".")))

    return sorted(choices, key=key)


def scrape(p: dict) -> dict:
    url = str(p["url"])
    html = fetch(url)
    out = {
        "code": p["code"],
        "name": p["name"],
        "section": "kovani-strisky",
        "manufacturer": "Süd-Metall",
        "source_url": url,
        "url": url,
        "image": p.get("image") or "",
        "images": [p["image"]] if p.get("image") else [],
    }

    selects = []
    for m in re.finditer(
        r'<label>([^<]+)</label>\s*</td>\s*<td>\s*<div class="select-fancy">.*?<select name="(variant_attribute_\d+)">(.*?)</select>',
        html,
        re.S | re.I,
    ):
        label = strip_tags(m.group(1))
        opts = [
            {"id": om.group(1), "name": strip_tags(om.group(2))}
            for om in re.finditer(r'<option value="(attribute_item_\d+)">([^<]+)</option>', m.group(3))
        ]
        if opts:
            selects.append({"label": label, "choices": opts})

    variations = []
    for m in re.finditer(r'<tr class="((?:attribute_item_\d+\s*)+)">(.*?)</tr>', html, re.S | re.I):
        price = parse_price_vat(m.group(2))
        if price is None:
            continue
        small = re.search(r"<small>(.*?)</small>", m.group(2), re.S | re.I)
        variations.append(
            {
                "attrs": m.group(1).split(),
                "label": strip_tags(small.group(1)) if small else "",
                "price_czk": price,
            }
        )

    desc = ""
    for pat in [
        r'<div class="product-desc[^"]*"[^>]*>(.*?)</div>',
        r'<div class="desc"[^>]*>(.*?)</div>',
        r'<meta\s+name="description"\s+content="([^"]+)"',
    ]:
        dm = re.search(pat, html, re.S | re.I)
        if not dm:
            continue
        desc = strip_tags(dm.group(1))
        if len(desc) > 40:
            break
    if len(desc) < 40:
        paras = []
        for pm in re.finditer(r"<p[^>]*>(.*?)</p>", html, re.S | re.I):
            t = strip_tags(pm.group(1))
            if len(t) < 40:
                continue
            low = t.lower()
            if "cookie" in low or "přihlašte" in low:
                continue
            if any(k in low for k in ["sklo", "nerez", "sada", "držák", "upevnění", "tloušť"]):
                paras.append(t)
        desc = "\n".join(paras[:4])

    if variations:
        base = min(v["price_czk"] for v in variations)
        out["price"] = base
        if len(selects) == 1:
            seen: set[str] = set()
            choices = []
            for v in variations:
                name = v["label"]
                for c in selects[0]["choices"]:
                    if c["id"] in v["attrs"]:
                        name = c["name"]
                        break
                if name in seen:
                    continue
                seen.add(name)
                choices.append({"name": name, "surcharge_czk": v["price_czk"] - base})
            out["options"] = [
                {
                    "label": selects[0]["label"],
                    "type": "select",
                    "key": slugify(selects[0]["label"]),
                    "choices": sort_mm_choices(choices),
                }
            ]
        elif len(selects) > 1:
            cheapest = min(variations, key=lambda v: v["price_czk"])
            groups = []
            ok = True
            for s in selects:
                choices2 = []
                for c in s["choices"]:
                    target = set()
                    for aid in cheapest["attrs"]:
                        belongs = None
                        for ss in selects:
                            if any(cc["id"] == aid for cc in ss["choices"]):
                                belongs = ss["label"]
                                break
                        if belongs == s["label"]:
                            continue
                        target.add(aid)
                    target.add(c["id"])
                    match = next((v for v in variations if set(v["attrs"]) == target), None)
                    if match is None:
                        ok = False
                        break
                    choices2.append({"name": c["name"], "surcharge_czk": match["price_czk"] - base})
                if not ok:
                    break
                groups.append(
                    {
                        "label": s["label"],
                        "type": "select",
                        "key": slugify(s["label"]),
                        "choices": sort_mm_choices(choices2),
                    }
                )
            if ok and groups:
                sur_map: dict[str, int] = {}
                for g in groups:
                    for s in selects:
                        if s["label"] != g["label"]:
                            continue
                        for ch in g["choices"]:
                            for c in s["choices"]:
                                if c["name"] == ch["name"]:
                                    sur_map[c["id"]] = ch["surcharge_czk"]
                for v in variations:
                    if sum(sur_map.get(a, 0) for a in v["attrs"]) != v["price_czk"] - base:
                        ok = False
                        break
            if ok and groups:
                out["options"] = groups
            else:
                out["options"] = [
                    {
                        "label": "Varianta",
                        "type": "select",
                        "key": "varianta",
                        "choices": [
                            {
                                "name": v["label"] or f"Varianta {i + 1}",
                                "surcharge_czk": v["price_czk"] - base,
                            }
                            for i, v in enumerate(variations)
                        ],
                    }
                ]
        else:
            out["options"] = [
                {
                    "label": "Varianta",
                    "type": "select",
                    "key": "varianta",
                    "choices": [
                        {
                            "name": v["label"] or f"Varianta {i + 1}",
                            "surcharge_czk": v["price_czk"] - base,
                        }
                        for i, v in enumerate(variations)
                    ],
                }
            ]
    else:
        prices = [parse_price_vat(m.group(0)) for m in re.finditer(r"[\d\s\xa0]+Kč\s*s\s*DPH", html)]
        prices = [x for x in prices if x]
        if prices:
            out["price"] = min(prices)
        else:
            m = re.search(r"od\s+([\d\s]+)\s*Kč", str(p.get("price", "")))
            out["price"] = int(re.sub(r"\s+", "", m.group(1))) if m else int(p.get("price") or 0)
        out["options"] = []

    out["description"] = clean_desc(desc)[:1800]
    return out


def main() -> None:
    current = json.loads(OUT.read_text(encoding="utf-8"))
    enriched = []
    for p in current:
        print("…", p["code"])
        enriched.append(scrape(p))
        time.sleep(0.35)
    OUT.write_text(json.dumps(enriched, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Wrote {OUT} ({len(enriched)} products)")


if __name__ == "__main__":
    main()
