#!/usr/bin/env python3
"""Remap product image URLs in theme data to self-hosted cropped katalog-img.

Updates:
  - wp-theme/sklospecial/assets/data/produkty.json
  - wp-theme/sklospecial/inc/katalog-produkty-data.php  (listing `image` fields)
  - wp-theme/sklospecial/inc/katalog-data.php           (hub hard-coded thumbs)

Base URL default matches Coolify API public static:
  https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/{code}.jpg

Only remaps URLs for product codes that have a cropped file present
(unless --force). Gallery entries: primary product shot(s) matching the
original `image` URL (or same productGfx id) are replaced; accessory /
install gallery URLs stay on qubaglass unless --gallery-main-only replaces
the whole gallery with the single cropped image.
"""

from __future__ import annotations

import argparse
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
JSON_PATH = ROOT / "wp-theme/sklospecial/assets/data/produkty.json"
PHP_LISTING = ROOT / "wp-theme/sklospecial/inc/katalog-produkty-data.php"
PHP_HUB = ROOT / "wp-theme/sklospecial/inc/katalog-data.php"
CROPPED_DIR = ROOT / "scripts/output/images_cropped"
PUBLIC_DIR = ROOT / "app/public/katalog-img"

DEFAULT_BASE = (
    "https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img"
)

GFX_RE = re.compile(r"productGfx_([a-zA-Z0-9]+)_", re.I)


def cropped_url(base: str, code: str) -> str:
    return f"{base.rstrip('/')}/{code}.jpg"


def has_crop(code: str) -> bool:
    return (CROPPED_DIR / f"{code}.jpg").is_file() or (PUBLIC_DIR / f"{code}.jpg").is_file()


def gfx_id(url: str) -> str | None:
    m = GFX_RE.search(url or "")
    return m.group(1) if m else None


def remap_product(prod: dict, base: str, gallery_main_only: bool) -> int:
    code = str(prod.get("code") or "").strip()
    if not code or not has_crop(code):
        return 0
    new_url = cropped_url(base, code)
    changed = 0
    old_image = str(prod.get("image") or "")
    old_gfx = gfx_id(old_image)

    if old_image != new_url:
        prod["image"] = new_url
        changed += 1

    images = list(prod.get("images") or [])
    if gallery_main_only:
        if images != [new_url]:
            prod["images"] = [new_url]
            changed += 1
        return changed

    new_images: list[str] = []
    seen: set[str] = set()
    # Always lead with cropped primary
    new_images.append(new_url)
    seen.add(new_url)

    for u in images:
        u = str(u or "").strip()
        if not u or u in seen:
            continue
        # Same productGfx family as the primary shot → use cropped
        if old_gfx and gfx_id(u) == old_gfx:
            continue  # already have cropped primary
        if u == old_image:
            continue
        new_images.append(u)
        seen.add(u)

    if new_images != images:
        prod["images"] = new_images
        changed += 1
    return changed


def remap_json(path: Path, base: str, gallery_main_only: bool) -> int:
    data = json.loads(path.read_text(encoding="utf-8"))
    products = data.get("products") or {}
    n = 0
    for _code, prod in products.items():
        n += remap_product(prod, base, gallery_main_only)
    data["generated"] = data.get("generated")  # keep
    path.write_text(json.dumps(data, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    return n


def remap_php_listing(path: Path, base: str) -> int:
    """Replace 'image' => 'https://qubaglass...' next to a known code."""
    text = path.read_text(encoding="utf-8")
    changed = 0

    def repl(m: re.Match[str]) -> str:
        nonlocal changed
        code = m.group(1)
        if not has_crop(code):
            return m.group(0)
        url = cropped_url(base, code)
        changed += 1
        return f"'code' => '{code}'{m.group(2)}'image' => '{url}'"

    # Match code then later image within the same array item (non-greedy).
    pattern = re.compile(
        r"'code'\s*=>\s*'(SklS-\d+)'((?:(?!'code'\s*=>).)*?)'image'\s*=>\s*'[^']*'",
        re.S,
    )
    new_text = pattern.sub(repl, text)
    if new_text != text:
        path.write_text(new_text, encoding="utf-8")
    return changed


def remap_php_hub(path: Path, base: str, code_hints: dict[str, str]) -> int:
    """Replace hard-coded hub image URLs when we know the product code.

    code_hints maps old qubaglass URL fragment or full URL → SklS code.
    Also parses trailing // SklS-XXXX comments.
    """
    text = path.read_text(encoding="utf-8")
    changed = 0

    # Comment-annotated: 'image' => 'https://…',  // SklS-0333
    def repl_comment(m: re.Match[str]) -> str:
        nonlocal changed
        code = m.group(1)
        if not has_crop(code):
            return m.group(0)
        changed += 1
        return f"'image' => '{cropped_url(base, code)}',  // {code}"

    text2 = re.sub(
        r"'image'\s*=>\s*'https://qubaglass\.pl[^']*',\s*//\s*(SklS-\d+)",
        repl_comment,
        text,
    )

    # Known primary product URLs from produkty.json image field
    for old_url, code in code_hints.items():
        if not has_crop(code):
            continue
        new = cropped_url(base, code)
        if old_url in text2 and old_url != new:
            text2 = text2.replace(old_url, new)
            changed += 1

    if text2 != text:
        path.write_text(text2, encoding="utf-8")
    return changed


def build_code_hints_from_json(json_path: Path) -> dict[str, str]:
    data = json.loads(json_path.read_text(encoding="utf-8"))
    hints: dict[str, str] = {}
    for code, prod in (data.get("products") or {}).items():
        img = str(prod.get("image") or "")
        if img.startswith("http") and "katalog-img" not in img:
            hints[img] = code
    return hints


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--base", default=DEFAULT_BASE)
    ap.add_argument(
        "--gallery-main-only",
        action="store_true",
        help="Replace each product gallery with only the cropped primary image",
    )
    ap.add_argument("--dry-run", action="store_true")
    args = ap.parse_args()

    if args.dry_run:
        codes = sorted({p.stem for p in CROPPED_DIR.glob("SklS-*.jpg")} | {p.stem for p in PUBLIC_DIR.glob("SklS-*.jpg")})
        print(f"Would remap using {len(codes)} cropped files, base={args.base}")
        return 0

    # Remap JSON first so hub hints still see old URLs… actually build hints before JSON rewrite
    hints = build_code_hints_from_json(JSON_PATH) if JSON_PATH.is_file() else {}
    # But JSON may already have qubaglass — good.

    n_json = remap_json(JSON_PATH, args.base, args.gallery_main_only) if JSON_PATH.is_file() else 0
    n_php = remap_php_listing(PHP_LISTING, args.base) if PHP_LISTING.is_file() else 0
    n_hub = remap_php_hub(PHP_HUB, args.base, hints) if PHP_HUB.is_file() else 0

    print(f"Remapped: json_fields≈{n_json} listing_php={n_php} hub_php={n_hub}")
    print(f"Base: {args.base}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
