#!/usr/bin/env python3
"""Download / organize product images by defaultCategory folders.

Reads scripts/output/shoptet_import.csv (code, image URL, defaultCategory),
saves files as:
  scripts/output/images/{category_slug_path}/SklS-XXXX.ext

Category folders are a Czech→ASCII slug hierarchy mirroring defaultCategory
segments split on " > ", e.g.:
  Skleněné dveře > Posuvné dveře Design-Lux
    → sklenene-dvere/posuvne-dvere-design-lux/

Existing flat files (images/SklS-XXXX.ext) are moved into the correct category
folder instead of re-downloaded.

Writes scripts/output/images_map.csv with columns:
  code;category;remote_url;local_path;status

Shoptet import itself should keep remote https URLs in the `image` column —
local paths are not valid for Shoptet import. The local tree is for backup /
re-hosting only.

Usage:
  scripts/.venv/bin/python scripts/download_images.py
  scripts/.venv/bin/python scripts/download_images.py --csv scripts/output/shoptet_import.csv
"""

from __future__ import annotations

import argparse
import csv
import logging
import mimetypes
import random
import re
import shutil
import time
import unicodedata
from pathlib import Path
from urllib.parse import urlparse

import requests

ROOT = Path(__file__).resolve().parent
DEFAULT_CSV = ROOT / "output" / "shoptet_import.csv"
DEFAULT_IMG_DIR = ROOT / "output" / "images"
DEFAULT_MAP = ROOT / "output" / "images_map.csv"
DEFAULT_README = DEFAULT_IMG_DIR / "README.txt"

DELAY_MIN = 0.3
DELAY_MAX = 0.5
REQUEST_TIMEOUT = 30
HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (compatible; SklospecialCatalogPrep/1.0; "
        "+https://sklospecial.cz; internal catalog backup)"
    ),
    "Accept": "image/avif,image/webp,image/apng,image/*,*/*;q=0.8",
}

KNOWN_EXTS = (".jpg", ".jpeg", ".png", ".webp", ".gif")

log = logging.getLogger("download_images")

EXT_BY_MIME = {
    "image/jpeg": ".jpg",
    "image/jpg": ".jpg",
    "image/png": ".png",
    "image/webp": ".webp",
    "image/gif": ".gif",
    "image/svg+xml": ".svg",
}

README_TEXT = """Quba Glass product images — local backup / re-host tree
=======================================================

Layout
------
  images/{category_slug}/.../SklS-XXXX.ext

Category folders mirror the Shoptet `defaultCategory` path (segments split on
" > "), slugified to ASCII (Czech diacritics stripped), e.g.:

  Skleněné dveře > Posuvné dveře Design-Lux
    → sklenene-dvere/posuvne-dvere-design-lux/SklS-0001.jpg

  Zábradlí > Profily na zábradlí
    → zabradli/profily-na-zabradli/SklS-....jpg

  Stříšky > Stříšky na táhlech
    → strisky/strisky-na-tahlech/SklS-....jpg

Shoptet import
--------------
The Shoptet CSV (`shoptet_import.csv`) keeps remote https image URLs.
Local paths here are NOT valid for Shoptet import — this tree is for backup
and optional re-hosting only.

Mapping
-------
See ../images_map.csv (code;category;remote_url;local_path;status).

Regenerate / sync
-----------------
  scripts/.venv/bin/python scripts/download_images.py
"""


def polite_sleep() -> None:
    time.sleep(random.uniform(DELAY_MIN, DELAY_MAX))


def strip_diacritics(text: str) -> str:
    normalized = unicodedata.normalize("NFKD", text)
    return "".join(ch for ch in normalized if not unicodedata.combining(ch))


def slugify(text: str) -> str:
    """ASCII-ish slug for a single category segment."""
    text = strip_diacritics(text).lower().strip()
    # Normalize various dashes to hyphen
    text = text.replace("–", "-").replace("—", "-").replace("−", "-")
    text = re.sub(r"[^a-z0-9]+", "-", text)
    text = re.sub(r"-{2,}", "-", text).strip("-")
    return text or "uncategorized"


def category_slug_path(default_category: str) -> str:
    """Full relative folder path from defaultCategory (may contain '/')."""
    raw = (default_category or "").strip()
    if not raw:
        return "uncategorized"
    segments = [s.strip() for s in raw.split(">") if s.strip()]
    if not segments:
        return "uncategorized"
    return "/".join(slugify(s) for s in segments)


def extension_from_url(url: str) -> str:
    path = urlparse(url).path
    ext = Path(path).suffix.lower()
    if ext in {".jpg", ".jpeg", ".png", ".webp", ".gif"}:
        return ".jpg" if ext == ".jpeg" else ext
    return ""


def extension_from_response(url: str, content_type: str | None) -> str:
    if content_type:
        mime = content_type.split(";")[0].strip().lower()
        if mime in EXT_BY_MIME:
            return EXT_BY_MIME[mime]
        guessed = mimetypes.guess_extension(mime)
        if guessed:
            return ".jpg" if guessed == ".jpe" else guessed
    return extension_from_url(url) or ".jpg"


def find_existing_image(img_dir: Path, code: str, cat_dir: Path) -> Path | None:
    """Find an existing image for code: prefer category folder, then flat root."""
    for base in (cat_dir, img_dir):
        for ext in KNOWN_EXTS:
            candidate = base / f"{code}{ext}"
            if candidate.is_file() and candidate.stat().st_size > 0:
                return candidate
    # Also search one level of nested category dirs (already organized elsewhere)
    for ext in KNOWN_EXTS:
        matches = list(img_dir.rglob(f"{code}{ext}"))
        for m in matches:
            if m.is_file() and m.stat().st_size > 0:
                return m
    return None


def ensure_in_category(src: Path, dest: Path) -> Path:
    """Move (or keep) file into dest path. Returns final path."""
    if src.resolve() == dest.resolve():
        return dest
    dest.parent.mkdir(parents=True, exist_ok=True)
    if dest.exists():
        # Destination already has a copy — remove stray source if different
        if src.resolve() != dest.resolve() and src.is_file():
            # Prefer keeping dest; drop duplicate source only if same size or src is flat leftover
            src.unlink()
        return dest
    shutil.move(str(src), str(dest))
    return dest


def load_rows(csv_path: Path) -> list[dict[str, str]]:
    with csv_path.open(newline="", encoding="utf-8-sig") as f:
        reader = csv.DictReader(f, delimiter=";")
        return [dict(row) for row in reader]


def download_one(
    session: requests.Session,
    code: str,
    url: str,
    category: str,
    img_dir: Path,
) -> tuple[str, str, bool]:
    """Return (status, local_path, was_cached). status: ok|fail|empty."""
    url = (url or "").strip()
    if not code or not url:
        return "empty", "", False

    cat_rel = category_slug_path(category)
    cat_dir = img_dir / cat_rel
    cat_dir.mkdir(parents=True, exist_ok=True)

    existing = find_existing_image(img_dir, code, cat_dir)
    if existing is not None:
        # Determine target extension from existing file
        dest = cat_dir / existing.name
        final = ensure_in_category(existing, dest)
        return "ok", str(final), True

    polite_sleep()
    resp = session.get(url, headers=HEADERS, timeout=REQUEST_TIMEOUT)
    resp.raise_for_status()

    final_ext = extension_from_response(url, resp.headers.get("Content-Type"))
    dest = cat_dir / f"{code}{final_ext}"
    if dest.exists() and dest.stat().st_size > 0:
        return "ok", str(dest), True

    dest.write_bytes(resp.content)
    return "ok", str(dest), False


def parse_args() -> argparse.Namespace:
    p = argparse.ArgumentParser(
        description="Download/organize Shoptet product images by category"
    )
    p.add_argument("--csv", type=Path, default=DEFAULT_CSV, help="Source CSV path")
    p.add_argument("--img-dir", type=Path, default=DEFAULT_IMG_DIR, help="Image output dir")
    p.add_argument("--map", type=Path, default=DEFAULT_MAP, dest="map_path", help="Mapping CSV")
    p.add_argument("-v", "--verbose", action="store_true")
    return p.parse_args()


def relative_to_project(path: str | Path) -> str:
    p = Path(path).resolve()
    try:
        return str(p.relative_to(ROOT.parent))
    except ValueError:
        return str(p)


def main() -> int:
    args = parse_args()
    logging.basicConfig(
        level=logging.DEBUG if args.verbose else logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        datefmt="%H:%M:%S",
    )

    if not args.csv.exists():
        log.error("CSV not found: %s", args.csv)
        return 1

    rows = load_rows(args.csv)
    args.img_dir.mkdir(parents=True, exist_ok=True)

    session = requests.Session()
    map_rows: list[dict[str, str]] = []
    stats = {"downloaded": 0, "moved_or_cached": 0, "fail": 0, "empty": 0}
    folders: set[str] = set()

    for i, row in enumerate(rows, start=1):
        code = (row.get("code") or "").strip()
        url = (row.get("image") or "").strip()
        category = (row.get("defaultCategory") or "").strip()
        cat_rel = category_slug_path(category)
        folders.add(cat_rel)

        local_path = ""
        status = "fail"
        was_cached = False
        try:
            status, local_path, was_cached = download_one(
                session, code, url, category, args.img_dir
            )
        except requests.RequestException as exc:
            status = "fail"
            log.warning("[%d/%d] FAIL %s %s — %s", i, len(rows), code, url, exc)
        else:
            if status == "ok" and was_cached:
                stats["moved_or_cached"] += 1
                log.debug("[%d/%d] Cached/moved %s → %s", i, len(rows), code, cat_rel)
            elif status == "ok":
                stats["downloaded"] += 1
                log.info(
                    "[%d/%d] Downloaded %s → %s/%s",
                    i,
                    len(rows),
                    code,
                    cat_rel,
                    Path(local_path).name,
                )
            elif status == "empty":
                stats["empty"] += 1
                log.warning("[%d/%d] Empty code/url for row", i, len(rows))

        if status == "fail":
            stats["fail"] += 1

        map_rows.append(
            {
                "code": code,
                "category": category,
                "remote_url": url,
                "local_path": relative_to_project(local_path) if local_path else "",
                "status": status,
            }
        )

    with args.map_path.open("w", newline="", encoding="utf-8-sig") as f:
        writer = csv.DictWriter(
            f,
            fieldnames=["code", "category", "remote_url", "local_path", "status"],
            delimiter=";",
        )
        writer.writeheader()
        writer.writerows(map_rows)

    DEFAULT_README.write_text(README_TEXT, encoding="utf-8")

    # Count files under category folders (exclude README)
    image_files = [
        p
        for p in args.img_dir.rglob("*")
        if p.is_file() and p.name != "README.txt" and p.suffix.lower() in KNOWN_EXTS
    ]
    leftover_flat = [
        p for p in args.img_dir.iterdir() if p.is_file() and p.suffix.lower() in KNOWN_EXTS
    ]

    log.info(
        "Done: downloaded=%d moved/cached=%d failed=%d empty=%d",
        stats["downloaded"],
        stats["moved_or_cached"],
        stats["fail"],
        stats["empty"],
    )
    log.info(
        "Images on disk: %d | category folders: %d | leftover flat: %d → %s",
        len(image_files),
        len(folders),
        len(leftover_flat),
        args.img_dir,
    )
    log.info("Mapping → %s", args.map_path)
    log.info("README → %s", DEFAULT_README)
    return 1 if stats["fail"] else 0


if __name__ == "__main__":
    raise SystemExit(main())
