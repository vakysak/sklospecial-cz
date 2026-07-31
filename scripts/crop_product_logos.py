#!/usr/bin/env python3
"""Preferred entry point: crop Quba Glass bottom logo from local product photos.

Smart-detects the white+blue logo band (fallback: bottom 13%). Writes flat
JPEGs to scripts/output/images_cropped/SklS-XXXX.jpg. Use --also-dst to copy
into app/public/katalog-img/ for the API static host.

Delegates to crop_logos.py (same CLI).
"""

from __future__ import annotations

import runpy
from pathlib import Path

if __name__ == "__main__":
    runpy.run_path(str(Path(__file__).resolve().parent / "crop_logos.py"), run_name="__main__")
