#!/usr/bin/env python3
"""Download product images from Shoptet import CSV for local backup / re-hosting.

Reads scripts/output/shoptet_import.csv (code + absolute https image URLs),
saves files as scripts/output/images/{code}.{ext}, and writes
scripts/output/images_map.csv (code, remote_url, local_path).

Shoptet import itself should keep remote https URLs in the `image` column —
local paths are not valid for Shoptet import.

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
import time
from pathlib import Path
from urllib.parse import urlparse

import requests

ROOT = Path(__file__).resolve().parent
DEFAULT_CSV = ROOT / "output" / "shoptet_import.csv"
DEFAULT_IMG_DIR = ROOT / "output" / "images"
DEFAULT_MAP = ROOT / "output" / "images_map.csv"

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

log = logging.getLogger("download_images")

EXT_BY_MIME = {
    "image/jpeg": ".jpg",
    "image/jpg": ".jpg",
    "image/png": ".png",
    "image/webp": ".webp",
    "image/gif": ".gif",
    "image/svg+xml": ".svg",
}


def polite_sleep() -> None:
    time.sleep(random.uniform(DELAY_MIN, DELAY_MAX))


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


def load_rows(csv_path: Path) -> list[dict[str, str]]:
    with csv_path.open(newline="", encoding="utf-8-sig") as f:
        reader = csv.DictReader(f, delimiter=";")
        return [dict(row) for row in reader]


def download_one(
    session: requests.Session,
    code: str,
    url: str,
    img_dir: Path,
) -> tuple[str, str, bool]:
    """Return (status, local_path, was_cached). status: ok|fail|empty."""
    url = (url or "").strip()
    if not code or not url:
        return "empty", "", False

    # Prefer existing file with any known extension (skip re-download).
    for ext in (".jpg", ".jpeg", ".png", ".webp", ".gif"):
        existing = img_dir / f"{code}{ext}"
        if existing.exists() and existing.stat().st_size > 0:
            return "ok", str(existing), True

    polite_sleep()
    resp = session.get(url, headers=HEADERS, timeout=REQUEST_TIMEOUT)
    resp.raise_for_status()

    final_ext = extension_from_response(url, resp.headers.get("Content-Type"))
    dest = img_dir / f"{code}{final_ext}"
    if dest.exists() and dest.stat().st_size > 0:
        return "ok", str(dest), True

    dest.write_bytes(resp.content)
    return "ok", str(dest), False


def parse_args() -> argparse.Namespace:
    p = argparse.ArgumentParser(description="Download Shoptet product images by code")
    p.add_argument("--csv", type=Path, default=DEFAULT_CSV, help="Source CSV path")
    p.add_argument("--img-dir", type=Path, default=DEFAULT_IMG_DIR, help="Image output dir")
    p.add_argument("--map", type=Path, default=DEFAULT_MAP, dest="map_path", help="Mapping CSV")
    p.add_argument("-v", "--verbose", action="store_true")
    return p.parse_args()


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
    stats = {"downloaded": 0, "skipped": 0, "fail": 0, "empty": 0}

    for i, row in enumerate(rows, start=1):
        code = (row.get("code") or "").strip()
        url = (row.get("image") or "").strip()
        local_path = ""
        status = "fail"
        was_cached = False
        try:
            status, local_path, was_cached = download_one(session, code, url, args.img_dir)
        except requests.RequestException as exc:
            status = "fail"
            log.warning("[%d/%d] FAIL %s %s — %s", i, len(rows), code, url, exc)
        else:
            if status == "ok" and was_cached:
                stats["skipped"] += 1
                log.debug("[%d/%d] Skip existing %s", i, len(rows), code)
            elif status == "ok":
                stats["downloaded"] += 1
                log.info("[%d/%d] Downloaded %s → %s", i, len(rows), code, Path(local_path).name)
            elif status == "empty":
                stats["empty"] += 1
                log.warning("[%d/%d] Empty code/url for row", i, len(rows))

        if status == "fail":
            stats["fail"] += 1

        rel_path = ""
        if local_path:
            try:
                rel_path = str(Path(local_path).resolve().relative_to(ROOT.parent))
            except ValueError:
                rel_path = local_path
        map_rows.append(
            {
                "code": code,
                "remote_url": url,
                "local_path": rel_path,
                "status": status,
            }
        )

    with args.map_path.open("w", newline="", encoding="utf-8-sig") as f:
        writer = csv.DictWriter(
            f,
            fieldnames=["code", "remote_url", "local_path", "status"],
            delimiter=";",
        )
        writer.writeheader()
        writer.writerows(map_rows)

    log.info(
        "Done: downloaded=%d skipped=%d failed=%d empty=%d → %s",
        stats["downloaded"],
        stats["skipped"],
        stats["fail"],
        stats["empty"],
        args.img_dir,
    )
    log.info("Mapping → %s", args.map_path)
    return 1 if stats["fail"] else 0


if __name__ == "__main__":
    raise SystemExit(main())
