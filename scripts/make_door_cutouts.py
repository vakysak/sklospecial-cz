#!/usr/bin/env python3
"""
Generate transparent PNG door cutouts for the konfigurator overlay.

Phase 1: process a small allow-list (default 10). To batch the rest later:

  .venv-cutouts/bin/python scripts/make_door_cutouts.py --all
  # or a custom list:
  .venv-cutouts/bin/python scripts/make_door_cutouts.py --codes SklS-0001,SklS-0002

Requires: rembg + pillow + numpy + scipy (+ onnxruntime) in .venv-cutouts
  python3 -m venv .venv-cutouts
  .venv-cutouts/bin/pip install rembg pillow numpy scipy onnxruntime

Output: app/public/katalog-img/cutouts/SklS-XXXX.png
Also writes cutouts/manifest.json listing available codes.

Glass doors: rembg keeps the room *through* the pane nearly opaque. We post-process
so glass becomes a graphite tint with real alpha (client photo shows through) while
hinges/handles stay opaque. Disconnected leftovers (side tables) are dropped.
"""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

import numpy as np
from PIL import Image
from rembg import new_session, remove
from scipy.ndimage import (
    binary_erosion,
    binary_opening,
    label,
    maximum_filter,
    uniform_filter,
)

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

# Graphite / clear glass tint + alpha (room photo shows through).
GLASS_RGB = (48, 48, 52)
GLASS_ALPHA = 118
# Keep a faint mix of original reflections so the pane is not flat.
GLASS_REFLECTION_MIX = 0.12


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


def _largest_component(mask: np.ndarray) -> np.ndarray:
    labeled, n = label(mask)
    if n <= 1:
        return mask
    counts = np.bincount(labeled.ravel())
    counts[0] = 0
    keep = int(counts.argmax())
    return labeled == keep


def _trim_floor_fringe(mask: np.ndarray, rgb: np.ndarray) -> np.ndarray:
    """Drop dark floor strip under the door when rembg includes it."""
    ys, xs = np.where(mask)
    if len(ys) == 0:
        return mask
    y0, y1 = int(ys.min()), int(ys.max())
    x0, x1 = int(xs.min()), int(xs.max())
    h = y1 - y0 + 1
    if h < 80:
        return mask
    lum = rgb.mean(axis=2)
    cut_y = y1
    band_h = max(8, h // 40)
    floor_run = 0
    for y in range(y1, y0 + int(h * 0.55), -1):
        row = lum[y, x0 : x1 + 1]
        row_m = mask[y, x0 : x1 + 1]
        if not row_m.any():
            continue
        vals = row[row_m]
        if vals.mean() < 70 and vals.std() < 18:
            floor_run += 1
            cut_y = y - 1
        else:
            if floor_run >= band_h:
                break
            floor_run = 0
            cut_y = y1
    if floor_run >= band_h and cut_y < y1:
        out = mask.copy()
        out[cut_y + 1 :, :] = False
        return out
    return mask


def glassify_cutout(rgba: Image.Image) -> Image.Image:
    """Turn rembg output into door-only cutout with transparent glass.

    rembg often keeps the room *seen through* glass at near-opaque alpha.
    Strategy: fill the door slab with a graphite tint + real alpha; keep only
    small metallic blobs (hinges/handles) and a thin silhouette edge opaque.
    """
    arr = np.asarray(rgba.convert("RGBA")).copy()
    h, w = arr.shape[:2]
    rgb = arr[:, :, :3].astype(np.float32)
    alpha = arr[:, :, 3].astype(np.float32)

    region = alpha > 28
    region = binary_opening(region, iterations=1)
    region = _largest_component(region)
    if not region.any():
        return rgba

    region = _trim_floor_fringe(region, rgb)

    lum = rgb.mean(axis=2)
    local_mean = uniform_filter(lum, size=7)

    # Thin silhouette (1–2 px) — keeps a slight frame edge without opaque slab
    core = binary_erosion(region, iterations=2)
    if not core.any():
        core = binary_erosion(region, iterations=1)
    ring = region & ~core

    # Hardware = small bright peaks (hinges, handle), not the whole bright pane
    peak = region & (lum > 155) & ((lum - local_mean) > 28)
    peak |= region & (lum > 175)
    labeled, n = label(peak)
    hardware = np.zeros_like(region)
    if n:
        counts = np.bincount(labeled.ravel())
        area = int(region.sum())
        max_blob = max(80, area // 25)  # reject huge "bright room" blobs
        for i in range(1, n + 1):
            if counts[i] <= max_blob:
                hardware |= labeled == i

    # Edge highlight: only the bright parts of the thin ring
    edge_hi = ring & (maximum_filter(lum, size=3) > 110) & ((lum - local_mean) > 8)

    keep = hardware | edge_hi
    glass = region & ~keep

    out = np.zeros((h, w, 4), dtype=np.uint8)
    if glass.any():
        mix = GLASS_REFLECTION_MIX
        tint = np.array(GLASS_RGB, dtype=np.float32)
        blended = (1.0 - mix) * tint + mix * rgb[glass]
        out[glass, :3] = np.clip(blended, 0, 255).astype(np.uint8)
        out[glass, 3] = GLASS_ALPHA

    if keep.any():
        out[keep, :3] = np.clip(rgb[keep], 0, 255).astype(np.uint8)
        out[keep, 3] = 255

    # Soft outer fringe
    soft = binary_erosion(region, iterations=1) ^ region
    soft = soft & (out[:, :, 3] > 0)
    if soft.any():
        out[soft, 3] = np.minimum(out[soft, 3], 150)

    return Image.fromarray(out, "RGBA")


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
    cut = glassify_cutout(cut)
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
