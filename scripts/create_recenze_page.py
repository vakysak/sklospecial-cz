#!/usr/bin/env python3
"""Create /recenze/ page and add menu item near Realizace."""

from __future__ import annotations

import base64
import json
import os
import ssl
import sys
import time
import urllib.error
import urllib.parse
import urllib.request

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
MENU_ID = 3


def req(method: str, path: str, data=None, timeout: int = 120):
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


def get_page_by_slug(slug: str):
    q = urllib.parse.urlencode({"slug": slug, "per_page": 20})
    pages = req("GET", f"/wp-json/wp/v2/pages?{q}")
    if not pages:
        return None
    return next((p for p in pages if int(p.get("parent") or 0) == 0), pages[0])


def set_seo_meta(post_id: int, title: str, desc: str):
    try:
        req(
            "POST",
            "/wp-json/sklo/v1/post-meta",
            {
                "post_id": post_id,
                "meta": {
                    "rank_math_title": title,
                    "rank_math_description": desc,
                },
            },
        )
    except Exception as e:
        print(f"  warn seo meta {post_id}: {e}")


def ensure_post_meta_endpoint():
    snips = req("GET", "/wp-json/code-snippets/v1/snippets")
    sync = next(
        (s for s in snips if s.get("name") == "Sklospecial temporary post meta REST"),
        None,
    )
    if not sync:
        print("post-meta snippet missing — SEO meta skipped")
        return False
    if not sync.get("active"):
        req(
            "PUT",
            f"/wp-json/code-snippets/v1/snippets/{sync['id']}",
            {
                "name": sync["name"],
                "desc": sync.get("desc") or "",
                "code": sync["code"],
                "scope": sync.get("scope") or "global",
                "priority": sync.get("priority") or 10,
                "active": True,
            },
        )
        print("activated post-meta snippet")
        time.sleep(1)
    return True


def deactivate_post_meta_endpoint():
    snips = req("GET", "/wp-json/code-snippets/v1/snippets")
    sync = next(
        (s for s in snips if s.get("name") == "Sklospecial temporary post meta REST"),
        None,
    )
    if sync and sync.get("active"):
        req(
            "PUT",
            f"/wp-json/code-snippets/v1/snippets/{sync['id']}",
            {
                "name": sync["name"],
                "desc": sync.get("desc") or "",
                "code": sync["code"],
                "scope": sync.get("scope") or "global",
                "priority": sync.get("priority") or 10,
                "active": False,
            },
        )
        print("deactivated post-meta snippet")


def upsert_page():
    existing = get_page_by_slug("recenze")
    payload = {
        "title": "Recenze",
        "slug": "recenze",
        "status": "publish",
        "parent": 0,
        "content": "<!-- theme template page-recenze.php -->",
    }
    if existing:
        page = req("POST", f"/wp-json/wp/v2/pages/{existing['id']}", payload)
        action = "updated"
    else:
        page = req("POST", "/wp-json/wp/v2/pages", payload)
        action = "created"
    set_seo_meta(
        int(page["id"]),
        "Recenze zákazníků | Sklospeciál",
        "Hodnocení zákazníků Sklospeciál — skleněné dveře, sprchy a zábradlí na míru. Průměrné hodnocení a zkušenosti z reálných zakázek.",
    )
    link = page.get("link") or f"{WP}/recenze/"
    print(f"{action} recenze id={page['id']} {link}")
    return page


def ensure_menu_item(page_id: int, title: str = "Recenze", menu_order: int = 14):
    items = req("GET", f"/wp-json/wp/v2/menu-items?menus={MENU_ID}&per_page=100")
    existing = next(
        (
            it
            for it in items
            if "/recenze/" in (it.get("url") or "")
            or int(it.get("object_id") or 0) == page_id
        ),
        None,
    )
    # Place right after Realizace when possible
    realizace = next((it for it in items if "/realizace/" in (it.get("url") or "")), None)
    if realizace is not None:
        try:
            menu_order = int(realizace.get("menu_order") or 14) + 1
        except (TypeError, ValueError):
            menu_order = 14

    payload = {
        "title": title,
        "status": "publish",
        "type": "post_type",
        "object": "page",
        "object_id": page_id,
        "parent": 0,
        "menus": MENU_ID,
        "menu_order": menu_order,
    }
    if existing:
        req("POST", f"/wp-json/wp/v2/menu-items/{existing['id']}", payload)
        print(f"menu updated id={existing['id']} {title} order={menu_order}")
    else:
        created = req("POST", "/wp-json/wp/v2/menu-items", payload)
        print(f"menu created id={created.get('id')} {title} order={menu_order}")


def main():
    ensure_post_meta_endpoint()
    try:
        page = upsert_page()
        ensure_menu_item(int(page["id"]))
    finally:
        deactivate_post_meta_endpoint()


if __name__ == "__main__":
    main()
