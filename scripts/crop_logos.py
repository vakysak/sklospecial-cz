#!/usr/bin/env python3
"""Crop Quba Glass bottom logo banner from local product images.

Detects the contiguous bottom band that is mostly near-white and contains
QUBA blue logo pixels. Falls back to cropping the bottom 13% when detection
fails. Writes flat JPEG files to scripts/output/images_cropped/SklS-XXXX.jpg
(and optionally mirrors into --also-dst, e.g. app/public/katalog-img/).

Usage:
  scripts/.venv/bin/python scripts/crop_logos.py
  scripts/.venv/bin/python scripts/crop_logos.py --fallback 0.13
  scripts/.venv/bin/python scripts/crop_logos.py --fixed 0.14
  scripts/.venv/bin/python scripts/crop_logos.py --only SklS-0001 SklS-0004
  scripts/.venv/bin/python scripts/crop_logos.py --also-dst ../app/public/katalog-img
"""

from __future__ import annotations

import argparse
import csv
import logging
import shutil
import sys
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent
DEFAULT_SRC = ROOT / "output" / "images"
DEFAULT_DST = ROOT / "output" / "images_cropped"
DEFAULT_MAP = ROOT / "output" / "images_map.csv"

IMAGE_EXTS = {".jpg", ".jpeg", ".png", ".webp", ".gif"}

# Tuned on SklS-0004 (logo+white pad ≈ 13–15%) and a 50-image sample.
DEFAULT_FALLBACK = 0.13
DEFAULT_MIN_CROP = 0.10
DEFAULT_MAX_CROP = 0.16
DEFAULT_PAD = 0.015
DEFAULT_QUALITY = 85

log = logging.getLogger("crop_logos")


def crop_bottom(img: Image.Image, bottom: float) -> Image.Image:
    """Remove the bottom `bottom` fraction of the image height."""
    if bottom <= 0:
        return img
    if bottom >= 1:
        raise ValueError("bottom crop fraction must be in (0, 1)")
    w, h = img.size
    keep_h = max(1, int(round(h * (1.0 - bottom))))
    return img.crop((0, 0, w, keep_h))


def detect_logo_fraction(
    img: Image.Image,
    *,
    fallback: float,
    min_crop: float,
    max_crop: float,
    pad: float,
) -> tuple[float, str]:
    """Return (fraction, mode) where mode is 'detect' or 'fallback'.

    Scans the bottom of the image for a contiguous near-white band that
    contains QUBA-blue logo pixels, then extends upward through white padding.
    """
    w, h = img.size
    if h < 32:
        return fallback, "fallback"

    rgb = img.convert("RGB")
    pix = rgb.load()
    stride = max(1, w // 350)

    def row_info(y: int) -> tuple[float, float]:
        near = blue = n = 0
        for x in range(0, w, stride):
            r, g, b = pix[x, y]
            n += 1
            if r >= 235 and g >= 235 and b >= 235:
                near += 1
            # Light/mid cyan-blue logo ink on white
            if b >= r + 8 and b >= g - 5 and 90 <= b <= 230 and r <= 200 and g <= 210:
                blue += 1
        return near / n, blue / n

    y_min = max(0, int(h * (1.0 - max_crop) - 2))
    cutoff: int | None = None
    blue_seen = False
    gap = 0

    for y in range(h - 1, y_min - 1, -1):
        near, blue = row_info(y)
        in_band = near >= 0.85 or blue >= 0.012 or (near >= 0.65 and blue >= 0.005)
        if in_band:
            if blue >= 0.01:
                blue_seen = True
            cutoff = y
            gap = 0
        elif cutoff is not None:
            gap += 1
            if gap >= 3:
                break

    if cutoff is None or not blue_seen:
        return fallback, "fallback"

    # Extend through white padding sitting above the logo text.
    y = cutoff - 1
    while y >= y_min:
        near, blue = row_info(y)
        if near >= 0.92 or (blue >= 0.008 and near >= 0.70):
            cutoff = y
            y -= 1
        else:
            break

    frac = (h - cutoff) / h
    frac = min(max_crop, max(min_crop, frac + pad))
    return frac, "detect"


def iter_sources(src_dir: Path, map_csv: Path | None) -> list[tuple[Path, str]]:
    """Return (absolute_src, product_code) pairs to process."""
    pairs: list[tuple[Path, str]] = []
    seen: set[str] = set()

    def add(path: Path) -> None:
        stem = path.stem
        if not stem.startswith("SklS-"):
            # Prefer SklS code from filename when present in path parts
            for part in path.parts:
                if part.startswith("SklS-") and Path(part).stem.startswith("SklS-"):
                    stem = Path(part).stem
                    break
            else:
                stem = path.stem
        if stem in seen:
            return
        if path.suffix.lower() not in IMAGE_EXTS or not path.is_file():
            return
        seen.add(stem)
        pairs.append((path, stem))

    if map_csv and map_csv.is_file():
        with map_csv.open(newline="", encoding="utf-8-sig") as fh:
            reader = csv.DictReader(fh, delimiter=";")
            for row in reader:
                status = (row.get("status") or "").strip().lower()
                if status and status not in {"ok", "moved", "exists"}:
                    continue
                code = (row.get("code") or "").strip()
                local = (row.get("local_path") or "").strip()
                if not local:
                    continue
                p = Path(local)
                if not p.is_absolute():
                    candidates = [
                        ROOT.parent / local,
                        ROOT / local,
                        ROOT / "output" / local,
                        src_dir.parent / local,
                        Path(local),
                    ]
                    p = next((c for c in candidates if c.is_file()), Path())
                if not p.is_file():
                    continue
                stem = code if code.startswith("SklS-") else p.stem
                if stem in seen:
                    continue
                seen.add(stem)
                pairs.append((p, stem))
        if pairs:
            # Also pick up any local files missing from the map
            for p in sorted(src_dir.rglob("*")):
                if p.is_file() and p.suffix.lower() in IMAGE_EXTS:
                    add(p)
            return pairs

    for p in sorted(src_dir.rglob("*")):
        if p.is_file() and p.suffix.lower() in IMAGE_EXTS:
            add(p)
    return pairs


def save_jpeg(img: Image.Image, out: Path, quality: int) -> None:
    out.parent.mkdir(parents=True, exist_ok=True)
    rgb = img.convert("RGB") if img.mode != "RGB" else img
    rgb.save(out, format="JPEG", quality=quality, optimize=True)


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--src", type=Path, default=DEFAULT_SRC)
    ap.add_argument("--dst", type=Path, default=DEFAULT_DST)
    ap.add_argument(
        "--also-dst",
        type=Path,
        default=None,
        help="Optional second output dir (flat SklS-XXXX.jpg), e.g. app/public/katalog-img",
    )
    ap.add_argument("--map", type=Path, default=DEFAULT_MAP)
    ap.add_argument(
        "--fallback",
        type=float,
        default=DEFAULT_FALLBACK,
        help=f"Fallback bottom fraction when detection fails (default: {DEFAULT_FALLBACK})",
    )
    ap.add_argument(
        "--fixed",
        type=float,
        default=None,
        help="If set, skip detection and always crop this bottom fraction",
    )
    ap.add_argument("--min-crop", type=float, default=DEFAULT_MIN_CROP)
    ap.add_argument("--max-crop", type=float, default=DEFAULT_MAX_CROP)
    ap.add_argument("--pad", type=float, default=DEFAULT_PAD)
    ap.add_argument("--quality", type=int, default=DEFAULT_QUALITY)
    ap.add_argument("--only", nargs="*", default=None)
    ap.add_argument("--dry-run", action="store_true")
    ap.add_argument("-v", "--verbose", action="store_true")
    args = ap.parse_args()

    logging.basicConfig(
        level=logging.DEBUG if args.verbose else logging.INFO,
        format="%(levelname)s %(message)s",
    )

    if not (0 < args.fallback < 1):
        log.error("--fallback must be in (0, 1)")
        return 2
    if args.fixed is not None and not (0 < args.fixed < 1):
        log.error("--fixed must be in (0, 1)")
        return 2

    src_dir = args.src.resolve()
    dst_dir = args.dst.resolve()
    also_dst = args.also_dst.resolve() if args.also_dst else None
    if not src_dir.is_dir():
        log.error("Source dir not found: %s", src_dir)
        return 1

    pairs = iter_sources(src_dir, args.map if args.map.is_file() else None)
    only = {c.strip() for c in (args.only or []) if c.strip()}
    if only:
        pairs = [(src, code) for src, code in pairs if code in only or any(c in code for c in only)]

    log.info(
        "Cropping %d images → %s (fallback=%.0f%%, detect %.0f–%.0f%%)",
        len(pairs),
        dst_dir,
        args.fallback * 100,
        args.min_crop * 100,
        args.max_crop * 100,
    )

    ok = 0
    errors = 0
    detect_n = 0
    fallback_n = 0

    for src, code in pairs:
        out_name = f"{code}.jpg"
        out = dst_dir / out_name
        if args.dry_run:
            log.info("DRY %s → %s", src, out)
            ok += 1
            continue
        try:
            with Image.open(src) as im:
                im.load()
                if args.fixed is not None:
                    frac, mode = args.fixed, "fixed"
                else:
                    frac, mode = detect_logo_fraction(
                        im,
                        fallback=args.fallback,
                        min_crop=args.min_crop,
                        max_crop=args.max_crop,
                        pad=args.pad,
                    )
                if mode == "detect":
                    detect_n += 1
                elif mode == "fallback":
                    fallback_n += 1
                cropped = crop_bottom(im, frac)
                save_jpeg(cropped, out, args.quality)
                if also_dst is not None:
                    also_path = also_dst / out_name
                    also_path.parent.mkdir(parents=True, exist_ok=True)
                    shutil.copy2(out, also_path)
                if args.verbose:
                    log.debug("OK %s %.1f%% (%s)", code, frac * 100, mode)
            ok += 1
        except Exception as exc:  # noqa: BLE001
            errors += 1
            log.error("FAIL %s: %s", src, exc)

    log.info(
        "Done: ok=%d errors=%d detect=%d fallback=%d fixed_or_other=%d",
        ok,
        errors,
        detect_n,
        fallback_n,
        ok - detect_n - fallback_n,
    )
    return 0 if errors == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
