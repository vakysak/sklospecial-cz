#!/usr/bin/env python3
"""Parse recenze_raw.txt → wp-theme/sklospecial/inc/recenze-data.php.

Dates are remapped linearly across [DATE_START, DATE_END] preserving order
(first review = oldest, last = newest). Original years 2027–2029 are discarded.
"""

from __future__ import annotations

import re
from datetime import date, timedelta
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
RAW = ROOT / "scripts" / "output" / "recenze_raw.txt"
OUT = ROOT / "wp-theme" / "sklospecial" / "inc" / "recenze-data.php"

DATE_START = date(2021, 8, 1)
DATE_END = date(2026, 8, 1)

PAT = re.compile(
    r"^(.+?),\s*(.+?)\s*(⭐+)\s*[—\-–]\s*"
    r"(\d{1,2}\.\s*\d{1,2}\.\s*\d{4})\s*(.+)$",
    re.S,
)


def php_escape(s: str) -> str:
    return (
        s.replace("\\", "\\\\")
        .replace("'", "\\'")
        .replace("\r", "")
        .replace("\n", " ")
    )


def category_for(text: str) -> str:
    t = text.lower()
    if any(k in t for k in ("sprch", "walk-in", "walk in", "kout")):
        return "sprcha"
    if any(k in t for k in ("zábradl", "zabradl", "schodi")):
        return "zabradli"
    if any(k in t for k in ("dveř", "dver", "posuvn", "kyvn", "otevír", "otevir")):
        return "dvere"
    return "ostatni"


def parse_raw(text: str) -> list[dict]:
    end = text.find("Všech 200 hotovo")
    if end > 0:
        text = text[:end]
    blocks = [b.strip() for b in re.split(r"\n\s*\n", text) if b.strip()]
    reviews: list[dict] = []
    for b in blocks:
        line = " ".join(b.split())
        m = PAT.match(line)
        if not m:
            raise SystemExit(f"Unparsed block: {line[:160]!r}")
        name, city, stars, _orig_date, body = m.groups()
        reviews.append(
            {
                "name": name.strip(),
                "city": city.strip(),
                "stars": len(stars),
                "text": body.strip(),
            }
        )
    return reviews


def remap_dates(n: int) -> list[str]:
    if n < 1:
        return []
    if n == 1:
        return [DATE_START.isoformat()]
    span = (DATE_END - DATE_START).days
    out: list[str] = []
    for i in range(n):
        d = DATE_START + timedelta(days=round(span * i / (n - 1)))
        out.append(d.isoformat())
    return out


def write_php(reviews: list[dict]) -> None:
    dates = remap_dates(len(reviews))
    lines = [
        "<?php",
        "/**",
        " * Zákaznické recenze — generováno scripts/import_recenze.py",
        f" * Počet: {len(reviews)} · data {dates[0]} → {dates[-1]}",
        " */",
        "",
        "declare(strict_types=1);",
        "",
        "if (!defined('ABSPATH')) {",
        "    exit;",
        "}",
        "",
        "/**",
        " * @return list<array{name:string,city:string,stars:int,date:string,text:string,category:string}>",
        " */",
        "function sklo_recenze_all(): array",
        "{",
        "    static $cache = null;",
        "    if ($cache !== null) {",
        "        return $cache;",
        "    }",
        "",
        "    $cache = [",
    ]
    for i, r in enumerate(reviews):
        cat = category_for(r["text"])
        lines.append("        [")
        lines.append(f"            'name' => '{php_escape(r['name'])}',")
        lines.append(f"            'city' => '{php_escape(r['city'])}',")
        lines.append(f"            'stars' => {int(r['stars'])},")
        lines.append(f"            'date' => '{dates[i]}',")
        lines.append(f"            'text' => '{php_escape(r['text'])}',")
        lines.append(f"            'category' => '{cat}',")
        lines.append("        ],")
    lines += [
        "    ];",
        "",
        "    return $cache;",
        "}",
        "",
        "/** @return array{count:int,avg:float,sum:int} */",
        "function sklo_recenze_stats(): array",
        "{",
        "    $all = sklo_recenze_all();",
        "    $sum = 0;",
        "    foreach ($all as $r) {",
        "        $sum += (int) $r['stars'];",
        "    }",
        "    $count = count($all);",
        "    $avg = $count > 0 ? round($sum / $count, 1) : 0.0;",
        "    return ['count' => $count, 'avg' => $avg, 'sum' => $sum];",
        "}",
        "",
        "/**",
        " * @return list<array{name:string,city:string,stars:int,date:string,text:string,category:string}>",
        " */",
        "function sklo_recenze_featured(int $limit = 8): array",
        "{",
        "    $all = sklo_recenze_all();",
        "    $five = array_values(array_filter($all, static fn($r) => (int) $r['stars'] === 5));",
        "    // Spread featured picks across the timeline for variety.",
        "    $n = count($five);",
        "    if ($n === 0) {",
        "        return array_slice($all, 0, $limit);",
        "    }",
        "    $picks = [];",
        "    for ($i = 0; $i < $limit; $i++) {",
        "        $idx = (int) round($i * ($n - 1) / max(1, $limit - 1));",
        "        $picks[] = $five[$idx];",
        "    }",
        "    return $picks;",
        "}",
        "",
    ]
    OUT.write_text("\n".join(lines), encoding="utf-8")


def main() -> None:
    if not RAW.exists():
        raise SystemExit(f"Missing {RAW}")
    reviews = parse_raw(RAW.read_text(encoding="utf-8"))
    write_php(reviews)
    dates = remap_dates(len(reviews))
    avg = sum(r["stars"] for r in reviews) / len(reviews)
    print(f"imported={len(reviews)} avg={avg:.2f} min={dates[0]} max={dates[-1]} → {OUT}")


if __name__ == "__main__":
    main()
