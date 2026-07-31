#!/usr/bin/env python3
"""Crop Quba Glass bottom logo banner from local product images.

Reads images under scripts/output/images/ (or paths from images_map.csv)
and writes cropped copies to scripts/output/images_cropped/, mirroring
the category folder structure.

Default crop removes the bottom 9% (measured ~5–12% thin logo strip (median ~11%) on
typical 400×400 / 700×700 Design-Lux / trubkový shots). Override with --bottom.

Usage:
  scripts/.venv/bin/python scripts/crop_logos.py
  scripts/.venv/bin/python scripts/crop_logos.py --bottom 0.09
  scripts/.venv/bin/python scripts/crop_logos.py --dry-run
  scripts/.venv/bin/python scripts/crop_logos.py --only SklS-0001 SklS-0110
"""

from __future__ import annotations

import argparse
import csv
import logging
import sys
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent
DEFAULT_SRC = ROOT / "output" / "images"
DEFAULT_DST = ROOT / "output" / "images_cropped"
DEFAULT_MAP = ROOT / "output" / "images_map.csv"

IMAGE_EXTS = {".jpg", ".jpeg", ".png", ".webp", ".gif"}

log = logging.getLogger("crop_logos")


def crop_bottom(img: Image.Image, bottom: float) -> Image.Image:
    """Remove the bottom `bottom` fraction of the image height."""
    if bottom <= 0:
        return img
    if bottom >= 1:
        raise ValueError("--bottom must be in (0, 1)")
    w, h = img.size
    keep_h = max(1, int(round(h * (1.0 - bottom))))
    return img.crop((0, 0, w, keep_h))


def iter_sources(src_dir: Path, map_csv: Path | None) -> list[tuple[Path, Path]]:
    """Return (absolute_src, relative_path) pairs to process."""
    pairs: list[tuple[Path, Path]] = []
    if map_csv and map_csv.is_file():
        with map_csv.open(newline="", encoding="utf-8") as fh:
            reader = csv.DictReader(fh, delimiter=";")
            for row in reader:
                status = (row.get("status") or "").strip().lower()
                if status and status not in {"ok", "moved", "exists"}:
                    continue
                local = (row.get("local_path") or "").strip()
                if not local:
                    continue
                p = Path(local)
                if not p.is_absolute():
                    # paths in map are often relative to scripts/output or repo
                    candidates = [
                        ROOT / local,
                        ROOT / "output" / local,
                        src_dir.parent / local,
                        Path(local),
                    ]
                    p = next((c for c in candidates if c.is_file()), None)  # type: ignore[assignment]
                    if p is None:
                        continue
                if not p.is_file():
                    continue
                try:
                    rel = p.relative_to(src_dir)
                except ValueError:
                    rel = Path(p.name)
                pairs.append((p, rel))
        if pairs:
            return pairs

    for p in sorted(src_dir.rglob("*")):
        if p.is_file() and p.suffix.lower() in IMAGE_EXTS:
            pairs.append((p, p.relative_to(src_dir)))
    return pairs


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument(
        "--src",
        type=Path,
        default=DEFAULT_SRC,
        help=f"Source images dir (default: {DEFAULT_SRC})",
    )
    ap.add_argument(
        "--dst",
        type=Path,
        default=DEFAULT_DST,
        help=f"Output cropped dir (default: {DEFAULT_DST})",
    )
    ap.add_argument(
        "--map",
        type=Path,
        default=DEFAULT_MAP,
        help=f"images_map.csv (default: {DEFAULT_MAP})",
    )
    ap.add_argument(
        "--bottom",
        type=float,
        default=0.09,
        help="Fraction of height to crop from bottom (default: 0.09)",
    )
    ap.add_argument(
        "--only",
        nargs="*",
        default=None,
        help="Optional product codes to limit processing (e.g. SklS-0001)",
    )
    ap.add_argument("--dry-run", action="store_true", help="List files only")
    ap.add_argument("-v", "--verbose", action="store_true")
    args = ap.parse_args()

    logging.basicConfig(
        level=logging.DEBUG if args.verbose else logging.INFO,
        format="%(levelname)s %(message)s",
    )

    if not (0 < args.bottom < 1):
        log.error("--bottom must be between 0 and 1 (got %s)", args.bottom)
        return 2

    src_dir = args.src.resolve()
    dst_dir = args.dst.resolve()
    if not src_dir.is_dir():
        log.error("Source dir not found: %s", src_dir)
        return 1

    pairs = iter_sources(src_dir, args.map if args.map.is_file() else None)
    only = {c.strip() for c in (args.only or []) if c.strip()}
    if only:
        pairs = [
            (src, rel)
            for src, rel in pairs
            if any(code in src.stem or code in str(rel) for code in only)
        ]

    log.info(
        "Cropping bottom %.1f%% from %d images → %s",
        args.bottom * 100,
        len(pairs),
        dst_dir,
    )

    ok = 0
    skipped = 0
    errors = 0
    for src, rel in pairs:
        out = dst_dir / rel
        if args.dry_run:
            log.info("DRY %s → %s", src, out)
            ok += 1
            continue
        try:
            out.parent.mkdir(parents=True, exist_ok=True)
            with Image.open(src) as im:
                im.load()
                cropped = crop_bottom(im, args.bottom)
                save_kwargs: dict = {}
                fmt = (im.format or "").upper()
                if fmt in {"JPEG", "JPG"} or out.suffix.lower() in {".jpg", ".jpeg"}:
                    if cropped.mode in ("RGBA", "P"):
                        cropped = cropped.convert("RGB")
                    save_kwargs = {"quality": 90, "optimize": True}
                    cropped.save(out, format="JPEG", **save_kwargs)
                elif fmt == "PNG" or out.suffix.lower() == ".png":
                    cropped.save(out, format="PNG", optimize=True)
                elif fmt == "WEBP" or out.suffix.lower() == ".webp":
                    cropped.save(out, format="WEBP", quality=90)
                else:
                    cropped.save(out)
            ok += 1
            if args.verbose:
                log.debug("OK %s", rel)
        except Exception as exc:  # noqa: BLE001
            errors += 1
            log.error("FAIL %s: %s", src, exc)

    log.info("Done: ok=%d skipped=%d errors=%d", ok, skipped, errors)
    return 0 if errors == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
