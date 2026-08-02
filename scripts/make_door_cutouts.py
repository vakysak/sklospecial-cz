#!/usr/bin/env python3
"""
Generate transparent PNG door cutouts for the konfigurator overlay.

Phase 1: process a small allow-list (default 10). To batch the rest later:

  .venv-cutouts/bin/python scripts/make_door_cutouts.py --all
  # or a custom list:
  .venv-cutouts/bin/python scripts/make_door_cutouts.py --codes SklS-0001,SklS-0002

Requires: rembg + pillow (+ onnxruntime) in .venv-cutouts
  python3 -m venv .venv-cutouts
  .venv-cutouts/bin/pip install rembg pillow onnxruntime

Output: app/public/katalog-img/cutouts/SklS-XXXX.png
Also writes cutouts/manifest.json listing available codes.
"""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

from PIL import Image
from rembg import new_session, remove

ROOT = Path(__file__).resolve().parents[1]
SRC_DIR = ROOT / "app" / "public" / "katalog-img"
OUT_DIR = SRC_DIR / "cutouts"
MANIFEST = OUT_DIR / "manifest.json"

# Phase-1 popular / representative doors (otevírané + posuvné + do pouzdra).
DEFAULT_CODES = [
    "SklS-0243",  # otevírané grafitové — screenshot from studio
    "SklS-0240",  # otevírané bez zárubní
    "SklS-0241",  # otevírané bez zárubní
    "SklS-0228",  # otevírané kotvené nahoře/dole
    "SklS-0244",  # otevírané
    "SklS-0001",  # posuvné Design-Lux se zrcadlem
    "SklS-0002",  # posuvné Design-Lux proužky
    "SklS-0101",  # posuvné LOFT-ART čiré
    "SklS-0102",  # posuvné LOFT-ART matné
    "SklS-0152",  # do pouzdra Estima čiré
]


def crop_to_alpha(im: Image.Image, pad: int = 4) -> Image.Image:
    if im.mode != "RGBA":
        im = im.convert("RGBA")
    bbox = im.getbbox()
    if not bbox:
        return im
    l, t, r, b = bbox
    l = max(0, l - pad)
    t = max(0, t - pad)
    r = min(im.width, r + pad)
    b = min(im.height, b + pad)
    return im.crop((l, t, r, b))


def process_one(code: str, session) -> Path | None:
    src = SRC_DIR / f"{code}.jpg"
    if not src.exists():
        src = SRC_DIR / f"{code}.jpeg"
    if not src.exists():
        src = SRC_DIR / f"{code}.webp"
    if not src.exists():
        print(f"  SKIP missing source: {code}", file=sys.stderr)
        return None

    out = OUT_DIR / f"{code}.png"
    print(f"  {code} ← {src.name}")
    img = Image.open(src).convert("RGB")
    cut = remove(img, session=session)
    cut = crop_to_alpha(cut)
    cut.save(out, "PNG", optimize=True)
    return out


def write_manifest() -> list[str]:
    codes = sorted(p.stem for p in OUT_DIR.glob("SklS-*.png"))
    MANIFEST.write_text(
        json.dumps(
            {
                "generated_by": "scripts/make_door_cutouts.py",
                "count": len(codes),
                "codes": codes,
                "path_pattern": "katalog-img/cutouts/{code}.png",
            },
            ensure_ascii=False,
            indent=2,
        )
        + "\n",
        encoding="utf-8",
    )
    return codes


def main() -> int:
    ap = argparse.ArgumentParser(description="Door PNG cutouts for konfigurator")
    ap.add_argument("--codes", help="Comma-separated SklS codes")
    ap.add_argument("--all", action="store_true", help="Process every SklS-*.jpg in katalog-img")
    ap.add_argument("--model", default="u2net", help="rembg model (u2net, u2netp, isnet-general-use…)")
    args = ap.parse_args()

    OUT_DIR.mkdir(parents=True, exist_ok=True)

    if args.all:
        codes = sorted({p.stem for p in SRC_DIR.glob("SklS-*.jpg")})
    elif args.codes:
        codes = [c.strip() for c in args.codes.split(",") if c.strip()]
    else:
        codes = list(DEFAULT_CODES)

    print(f"Processing {len(codes)} door(s) with rembg/{args.model} → {OUT_DIR}")
    session = new_session(args.model)
    ok = 0
    for code in codes:
        if process_one(code, session):
            ok += 1

    listed = write_manifest()
    print(f"Done: {ok}/{len(codes)} cutouts. Manifest has {len(listed)} PNG(s).")
    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())
