#!/usr/bin/env python3
"""Build wp-theme/sklospecial/assets/data/kovani-sprchy.json from luxusnikovani.cz.

Source: /kovani-pro-sprchove-kouty (+ subcategories, pagination).
Applies +35 % margin rounded to tens Kč (same as kování zábradlí / stříšky).
"""

from __future__ import annotations

import html as htmlmod
import json
import math
import re
import time
import unicodedata
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "wp-theme" / "sklospecial" / "assets" / "data" / "kovani-sprchy.json"
UA = "Mozilla/5.0 (compatible; SklospecialBot/1.0; +https://sklospecial.eu)"
BASE = "https://www.luxusnikovani.cz"
SECTION = "kovani-sprchy"
MARKUP = 1.35
CATEGORY_URL = BASE + "/kovani-pro-sprchove-kouty"

ROOT_SUBS = [
    "/panty-pro-sprchove-dvere",
    "/uchyty-pro-sprchove-zasteny-2",
    "/kovani-pro-posuvne-dvere-sprchy",
    "/stabilizacni-tyce-pro-zasteny-2",
    "/uchytky-a-madla-pro-sprchove-dvere",
    "/tesnici-profily-a-doplnky-pro-sprchove-kouty",
]

# Czech labels from luxusnikovani.cz /kovani-pro-sprchove-kouty (slug → label)
SUBCAT_LABELS: dict[str, str] = {
    "panty-pro-sprchove-dvere": "Panty pro sprchové dveře",
    "uchyty-pro-sprchove-zasteny-2": "Upevnění pro sprchové zástěny",
    "kovani-pro-posuvne-dvere-sprchy": "Posuvné kování pro sprchové dveře",
    "stabilizacni-tyce-pro-zasteny-2": "Stabilizační tyče pro zástěny",
    "uchytky-a-madla-pro-sprchove-dvere": "Úchytky a madla pro sprchové dveře",
    "tesnici-profily-a-doplnky-pro-sprchove-kouty": "Těsnící profily a doplňky pro sprchové kouty",
}


def subcat_meta(slug: str) -> tuple[str, str]:
    """Return (category_slug, Czech subcategory label)."""
    slug = (slug or "").strip().strip("/")
    label = SUBCAT_LABELS.get(slug) or slug.replace("-", " ").capitalize()
    return slug, label


def slugify(s: str) -> str:
    s = unicodedata.normalize("NFKD", s)
    s = "".join(c for c in s if not unicodedata.combining(c))
    s = s.lower()
    return re.sub(r"[^a-z0-9]+", "_", s).strip("_") or "opt"


def fetch(url: str) -> str:
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept-Language": "cs"})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read().decode("utf-8", errors="ignore")


def abs_url(path: str) -> str:
    if path.startswith("http"):
        return path
    return BASE + path if path.startswith("/") else BASE + "/" + path


def bump(czk: int) -> int:
    if czk <= 0:
        return 0
    return int(math.floor(czk * MARKUP / 10 + 0.5) * 10)


def parse_price_vat(text: str) -> int | None:
    text = htmlmod.unescape(text).replace("\xa0", " ").replace("\u202f", " ")
    m = re.search(r"([\d][\d\s]*)\s*Kč\s*s\s*DPH", text, re.I)
    if not m:
        return None
    return int(re.sub(r"\s+", "", m.group(1)))


def parse_int_price(text: str) -> int | None:
    text = htmlmod.unescape(text).replace("\xa0", " ").replace("\u202f", " ")
    m = re.search(r"([\d][\d\s]*)", text)
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
    for b in ("luxusní", "exkluzivní", "prémiový", "prémiová", "prémiové"):
        desc = re.sub(re.escape(b), "", desc, flags=re.I)
    desc = re.sub(r" {2,}", " ", desc)
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


def content_region(html: str) -> str:
    i = html.find("snippet--categoryContent")
    if i < 0:
        return html
    j = html.find("content-box-shadow", i)
    return html[i : j if j > 0 else i + 80000]


def parse_subcats(html: str) -> list[tuple[str, str]]:
    body = content_region(html)
    out: list[tuple[str, str]] = []
    for m in re.finditer(r'<ul class="category-list clear">(.*?)</ul>', body, re.S):
        for a in re.finditer(r'<a[^>]+title="([^"]+)"[^>]+href="(/[^"?#]+)"', m.group(1)):
            out.append((htmlmod.unescape(a.group(1)), a.group(2)))
    return out


def parse_listing_page(html: str, subcat: str) -> list[dict]:
    body = content_region(html)
    out: list[dict] = []
    starts = [m.start() for m in re.finditer(r'<div class="product-box">', body)]
    for i, s in enumerate(starts):
        e = starts[i + 1] if i + 1 < len(starts) else min(len(body), s + 5000)
        block = body[s:e]
        hm = re.search(
            r'<h2>\s*<a[^>]+href="(/[^"#?]+)"[^>]*(?:title="([^"]*)")?',
            block,
            re.I,
        )
        if not hm:
            continue
        href = hm.group(1)
        name = htmlmod.unescape(hm.group(2) or "").strip()
        if not name:
            tm = re.search(r">([^<]+)</a>", block[hm.start() : hm.start() + 400])
            name = htmlmod.unescape(tm.group(1)).strip() if tm else ""
        imgm = re.search(r'<img[^>]+src="([^"]+)"', block)
        img = abs_url(imgm.group(1)) if imgm else ""
        pm = re.search(r"CENA OD\s+([\d\s\xa0]+)\s*Kč", block, re.I)
        if not pm:
            pm = re.search(r'class="price"[^>]*>.*?([\d\s\xa0]+)\s*Kč', block, re.S | re.I)
        list_price = parse_int_price(pm.group(1)) if pm else None
        out.append(
            {
                "name": name,
                "url": abs_url(href),
                "image": img,
                "list_price_bez_dph": list_price,
                "subcat": subcat,
            }
        )
    return out


def next_page_url(html: str, current: str) -> str | None:
    m = re.search(r'href="([^"]+)"[^>]*>\s*NAČÍST DALŠÍ PRODUKTY', html, re.I)
    if not m:
        return None
    nxt = abs_url(htmlmod.unescape(m.group(1)))
    return None if nxt == current else nxt


def make_code(url: str, used: set[str]) -> str:
    slug = url.rstrip("/").split("/")[-1]
    raw = slugify(slug).upper().replace("_", "-")
    raw = re.sub(r"-+", "-", raw).strip("-")
    if len(raw) > 40:
        raw = raw[:40].rstrip("-")
    code = f"KOV-SPR-{raw}" if raw else "KOV-SPR-X"
    base = code
    n = 2
    while code in used:
        code = f"{base}-{n}"
        n += 1
    used.add(code)
    return code


def list_all_products() -> list[dict]:
    products: list[dict] = []
    seen: set[str] = set()
    queue = list(ROOT_SUBS)
    visited: set[str] = set()
    while queue:
        path = queue.pop(0)
        if path in visited:
            continue
        visited.add(path)
        url = abs_url(path)
        page = 0
        while url and page < 40:
            page += 1
            print(f"  list {path} p{page}")
            html = fetch(url)
            kids = parse_subcats(html)
            batch = parse_listing_page(html, path.strip("/"))
            if kids and not batch:
                for _title, href in kids:
                    if href not in visited:
                        queue.append(href)
                break
            added = 0
            for p in batch:
                if p["url"] in seen:
                    continue
                seen.add(p["url"])
                products.append(p)
                added += 1
            print(f"    +{added} (total {len(products)})")
            nxt = next_page_url(html, url)
            if not nxt or added == 0:
                break
            url = nxt
            time.sleep(0.2)
        time.sleep(0.2)
    return products


def scrape_detail(p: dict, code: str) -> dict:
    url = str(p["url"])
    html = fetch(url)
    cat_slug, cat_label = subcat_meta(str(p.get("subcat") or ""))
    out: dict = {
        "code": code,
        "name": p["name"],
        "section": SECTION,
        "subcat": cat_slug,
        "category_slug": cat_slug,
        "subcategory": cat_label,
        "manufacturer": "",
        "source_url": url,
        "url": url,
        "image": p.get("image") or "",
        "images": [p["image"]] if p.get("image") else [],
    }

    man = re.search(r"<h3>\s*<a[^>]*>([^<]+)</a>\s*</h3>", html, re.I)
    if man:
        mname = strip_tags(man.group(1))
        if mname:
            out["manufacturer"] = mname

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
            if any(
                k in low
                for k in [
                    "sklo",
                    "nerez",
                    "sprch",
                    "panty",
                    "pant",
                    "úchyt",
                    "uchyt",
                    "madl",
                    "těsn",
                    "tesn",
                    "zástěn",
                    "zasten",
                    "stabiliz",
                    "posuv",
                    "profil",
                    "kován",
                ]
            ):
                paras.append(t)
        desc = "\n".join(paras[:4])

    if variations:
        base = min(v["price_czk"] for v in variations)
        out["price"] = bump(base)
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
                choices.append({"name": name, "surcharge_czk": bump(v["price_czk"]) - out["price"]})
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
                    choices2.append(
                        {"name": c["name"], "surcharge_czk": bump(match["price_czk"]) - out["price"]}
                    )
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
                    expected = bump(v["price_czk"]) - out["price"]
                    if sum(sur_map.get(a, 0) for a in v["attrs"]) != expected:
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
                                "surcharge_czk": bump(v["price_czk"]) - out["price"],
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
                            "surcharge_czk": bump(v["price_czk"]) - out["price"],
                        }
                        for i, v in enumerate(variations)
                    ],
                }
            ]
    else:
        prices = [parse_price_vat(m.group(0)) for m in re.finditer(r"[\d\s\xa0]+Kč\s*s\s*DPH", html)]
        prices = [x for x in prices if x]
        if prices:
            out["price"] = bump(min(prices))
        elif p.get("list_price_bez_dph"):
            vat = int(round(int(p["list_price_bez_dph"]) * 1.21))
            out["price"] = bump(vat)
        else:
            # single price without VAT label
            pm = re.search(r'class="price"[^>]*>.*?([\d\s\xa0]+)\s*Kč', html, re.S | re.I)
            if pm:
                raw = parse_int_price(pm.group(1)) or 0
                # Prefer s DPH if page shows both; else assume listed is s DPH-ish
                out["price"] = bump(raw)
            else:
                out["price"] = 0
        out["options"] = []

    for g in out.get("options") or []:
        for ch in g.get("choices") or []:
            if int(ch.get("surcharge_czk") or 0) < 0:
                ch["surcharge_czk"] = 0

    out["description"] = clean_desc(desc)[:1800]
    return out


def main() -> None:
    print(f"Source {CATEGORY_URL}")
    listed = list_all_products()
    print(f"Found {len(listed)} products — scraping details…")
    used: set[str] = set()
    enriched = []
    for i, p in enumerate(listed, 1):
        code = make_code(p["url"], used)
        print(f"… [{i}/{len(listed)}] {code}")
        try:
            enriched.append(scrape_detail(p, code))
        except Exception as e:
            print(f"  FAIL {p['url']}: {e}")
            price = 0
            if p.get("list_price_bez_dph"):
                price = bump(int(round(int(p["list_price_bez_dph"]) * 1.21)))
            cat_slug, cat_label = subcat_meta(str(p.get("subcat") or ""))
            enriched.append(
                {
                    "code": code,
                    "name": p["name"],
                    "section": SECTION,
                    "subcat": cat_slug,
                    "category_slug": cat_slug,
                    "subcategory": cat_label,
                    "manufacturer": "",
                    "source_url": p["url"],
                    "url": p["url"],
                    "price": price,
                    "options": [],
                    "description": "",
                    "image": p.get("image") or "",
                    "images": [p["image"]] if p.get("image") else [],
                }
            )
        time.sleep(0.25)
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(enriched, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    with_price = sum(1 for p in enriched if int(p.get("price") or 0) > 0)
    with_opts = sum(1 for p in enriched if p.get("options"))
    print(f"Wrote {OUT} ({len(enriched)} products, {with_price} priced, {with_opts} with options)")


if __name__ == "__main__":
    main()
