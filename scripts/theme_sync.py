#!/usr/bin/env python3
"""Push local wp-theme/sklospecial to staging via Code Snippet #9 theme-sync."""

from __future__ import annotations

import base64
import json
import os
import ssl
import sys
import time
import urllib.error
import urllib.request
from pathlib import Path

WP = os.environ.get(
    "SKLO_WP_URL",
    "https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io",
).rstrip("/")
USER = os.environ.get("SKLO_WP_USER", "vakysak")
PASS = os.environ.get("SKLO_WP_APP_PASSWORD", "")
if not PASS:
    raise SystemExit("Set SKLO_WP_APP_PASSWORD (WP application password)")
AUTH = base64.b64encode(f"{USER}:{PASS}".encode()).decode()
CTX = ssl.create_default_context()
THEME = Path(__file__).resolve().parents[1] / "wp-theme" / "sklospecial"
SNIPPET_NAME = "Sklospecial theme sync"


def req(method: str, path: str, data=None, timeout: int = 180):
    body = None if data is None else json.dumps(data).encode()
    r = urllib.request.Request(
        WP + path,
        data=body,
        method=method,
        headers={
            "Authorization": f"Basic {AUTH}",
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
    )
    try:
        with urllib.request.urlopen(r, context=CTX, timeout=timeout) as resp:
            raw = resp.read().decode()
            return json.loads(raw) if raw else {}
    except urllib.error.HTTPError as e:
        err = e.read().decode(errors="ignore")
        raise RuntimeError(f"{method} {path} -> {e.code}: {err[:800]}") from e


def set_snippet_active(active: bool):
    snips = req("GET", "/wp-json/code-snippets/v1/snippets")
    sync = next(s for s in snips if s.get("name") == SNIPPET_NAME)
    req(
        "PUT",
        f"/wp-json/code-snippets/v1/snippets/{sync['id']}",
        {
            "name": sync["name"],
            "desc": sync.get("desc") or "",
            "code": sync["code"],
            "scope": sync.get("scope") or "global",
            "priority": sync.get("priority") or 5,
            "active": active,
        },
    )
    return sync["id"]


def collect_files() -> dict[str, str]:
    files: dict[str, str] = {}
    for p in THEME.rglob("*"):
        if not p.is_file() or p.name == ".DS_Store":
            continue
        # Skip huge binary-ish if any; json text ok
        rel = str(p.relative_to(THEME)).replace("\\", "/")
        try:
            files[rel] = p.read_text(encoding="utf-8")
        except UnicodeDecodeError:
            print("skip binary", rel)
    return files


def main():
    files = collect_files()
    print(f"files={len(files)}")
    set_snippet_active(True)
    time.sleep(0.5)
    try:
        out = req("POST", "/wp-json/sklo/v1/theme-sync", {"files": files, "activate": True}, timeout=300)
        written = out.get("written") or []
        print(f"written={len(written)} stylesheet={out.get('stylesheet')} template={out.get('template')}")
        if not out.get("ok"):
            print(json.dumps(out, ensure_ascii=False)[:2000])
            sys.exit(1)
    finally:
        set_snippet_active(False)
        print("theme-sync snippet off")


if __name__ == "__main__":
    main()
