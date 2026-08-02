#!/usr/bin/env python3
"""Fail if any 5+ word phrase is shared by 2+ reviews across recenze texts."""

from __future__ import annotations

import re
import sys
from collections import Counter
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PHP = ROOT / "wp-theme" / "sklospecial" / "inc" / "recenze-data.php"
MIN_WORDS = 5
MAX_OCCURRENCES = 1


def parse_texts(path: Path) -> list[str]:
    text = path.read_text(encoding="utf-8")
    bodies = re.findall(r"'text'\s*=>\s*'((?:\\'|[^'])*)'", text)
    out = []
    for b in bodies:
        out.append(b.replace("\\'", "'").replace("\\\\", "\\"))
    return out


def normalize(s: str) -> str:
    s = s.lower()
    s = re.sub(r"[^\wáčďéěíňóřšťúůýž\s]+", " ", s, flags=re.I)
    s = re.sub(r"\s+", " ", s).strip()
    return s


def phrases(text: str, n: int = MIN_WORDS) -> list[str]:
    words = normalize(text).split()
    if len(words) < n:
        return []
    return [" ".join(words[i : i + n]) for i in range(len(words) - n + 1)]


def main() -> int:
    texts = parse_texts(PHP)
    if len(texts) < 30:
        print(f"ERROR: too few texts parsed: {len(texts)}", file=sys.stderr)
        return 2

    c: Counter[str] = Counter()
    for t in texts:
        # Deduplicate within a single review
        c.update(set(phrases(t)))

    bad = [(p, n) for p, n in c.items() if n > MAX_OCCURRENCES]
    bad.sort(key=lambda x: (-x[1], x[0]))

    print(f"reviews={len(texts)}")
    print(f"unique_5grams={len(c)}")
    print(f"phrases_over_{MAX_OCCURRENCES}={len(bad)}")
    if bad:
        print("TOP_REPEATED:")
        for p, n in bad[:40]:
            print(f"  {n}×  {p}")
        return 1

    # Also report top 10 by frequency (should all be ≤2)
    top = c.most_common(10)
    print("TOP10_OK:")
    for p, n in top:
        print(f"  {n}×  {p}")
    print("PASS")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
