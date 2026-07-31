#!/usr/bin/env python3
"""Preferred entry point: crop Quba Glass bottom logo from local product photos.

Live site loads remote qubaglass.pl images — CSS clips ~9% via
`--sklo-quba-logo` on `.sklo-produkty__media` / `.sklo-pdetail__*` (see
wp-theme/sklospecial/assets/css/main.css). This script is for local/
Shoptet assets under scripts/output/images/ → images_cropped/.

Delegates to crop_logos.py (same CLI).
"""

from __future__ import annotations

import runpy
from pathlib import Path

if __name__ == "__main__":
    runpy.run_path(str(Path(__file__).resolve().parent / "crop_logos.py"), run_name="__main__")
