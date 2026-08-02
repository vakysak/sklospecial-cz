#!/usr/bin/env python3
"""Generate Sklospecial SEO landings (type + city pages) via WP REST."""

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
TEMPLATE = "template-seo-landing.php"
MAPA_TEMPLATE = "template-mapa-stranek.php"
KATALOG_TEMPLATE = "template-katalog.php"

# Keep in sync with wp-theme/sklospecial/inc/seo-landings-data.php
CATEGORIES = {
    "sklenene-dvere": {"name": "Skleněné dveře", "h1": "Skleněné dveře", "parent_id": None},
    "sprchove-kouty": {"name": "Sprchové kouty", "h1": "Sprchové kouty", "parent_id": None},
    "zabradli": {"name": "Skleněné zábradlí", "h1": "Skleněné zábradlí", "parent_id": None},
    "strisky": {"name": "Skleněné stříšky", "h1": "Skleněné stříšky", "parent_id": None},
    "sklenene-steny": {"name": "Skleněné stěny", "h1": "Skleněné stěny", "parent_id": None},
    "francouzske-balkony": {"name": "Francouzské balkony", "h1": "Francouzské balkony", "parent_id": None},
}

CITIES = [
    ("praha", "Praha", "Praze", "v"),
    ("brno", "Brno", "Brně", "v"),
    ("ostrava", "Ostrava", "Ostravě", "v"),
    ("plzen", "Plzeň", "Plzni", "v"),
    ("liberec", "Liberec", "Liberci", "v"),
    ("olomouc", "Olomouc", "Olomouci", "v"),
    ("ceske-budejovice", "České Budějovice", "Českých Budějovicích", "v"),
    ("hradec-kralove", "Hradec Králové", "Hradci Králové", "v"),
    ("usti-nad-labem", "Ústí nad Labem", "Ústí nad Labem", "v"),
    ("pardubice", "Pardubice", "Pardubicích", "v"),
    ("zlin", "Zlín", "Zlíně", "ve"),
    ("havirov", "Havířov", "Havířově", "v"),
    ("kladno", "Kladno", "Kladně", "v"),
    ("jihlava", "Jihlava", "Jihlavě", "v"),
    ("teplice", "Teplice", "Teplicích", "v"),
]

TYPE_LANDINGS = [
    {
        "slug": "do-pouzdra",
        "parent_path": "sklenene-dvere/posuvne",
        "title": "Posuvné skleněné dveře do pouzdra",
        "seo_title": "Posuvné skleněné dveře do pouzdra na míru | Sklospeciál",
        "seo_desc": "Posuvné dveře do pouzdra — online zaměření, výroba na míru, montáž. Rodinná firma, 30 let.",
    },
    {
        "slug": "walk-in",
        "parent_path": "sprchove-kouty",
        "title": "Walk-in sprchové kouty",
        "seo_title": "Walk-in sprchové kouty na míru | Sklospeciál",
        "seo_desc": "Walk-in sprchové stěny na míru — online zaměření, výroba, montáž. Rodinná firma, 30 let.",
    },
    {
        "slug": "zastena",
        "parent_path": "sprchove-kouty",
        "title": "Sprchové zástěny na míru",
        "seo_title": "Sprchové zástěny na míru | Sklospeciál",
        "seo_desc": "Sprchové zástěny na míru — online zaměření, výroba, montáž. Rodinná firma, 30 let.",
    },
    {
        "slug": "schodiste",
        "parent_path": "zabradli",
        "title": "Skleněné zábradlí na schodiště",
        "seo_title": "Skleněné zábradlí na schodiště na míru | Sklospeciál",
        "seo_desc": "Skleněné zábradlí na schodiště — online zaměření, výroba na míru, montáž. Rodinná firma, 30 let.",
    },
]


def req(method: str, path: str, data=None, timeout: int = 90):
    body = None if data is None else json.dumps(data).encode()
    url = WP + path if path.startswith("/") else path
    r = urllib.request.Request(
        url,
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
        raise RuntimeError(f"{method} {path} -> {e.code}: {err[:500]}") from e


def get_page_by_path(path: str):
    """Resolve page by nested path using successive parent lookups."""
    parts = [p for p in path.strip("/").split("/") if p]
    if not parts:
        return None
    parent = 0
    page = None
    for slug in parts:
        q = urllib.parse.urlencode({"slug": slug, "parent": parent, "per_page": 20})
        pages = req("GET", f"/wp-json/wp/v2/pages?{q}")
        if not pages:
            # fallback search without parent (WP may ignore parent filter)
            q2 = urllib.parse.urlencode({"slug": slug, "per_page": 50})
            pages = req("GET", f"/wp-json/wp/v2/pages?{q2}")
            pages = [p for p in pages if int(p.get("parent") or 0) == parent] or pages
        if not pages:
            return None
        # Prefer exact parent match
        match = next((p for p in pages if int(p.get("parent") or 0) == parent), pages[0])
        page = match
        parent = int(page["id"])
    return page


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


def upsert_page(
    *,
    title: str,
    slug: str,
    parent: int,
    template: str,
    seo_title: str,
    seo_desc: str,
    content: str = "",
    status: str = "publish",
):
    existing = None
    if parent:
        # find child with slug under parent
        q = urllib.parse.urlencode({"parent": parent, "per_page": 100})
        kids = req("GET", f"/wp-json/wp/v2/pages?{q}")
        existing = next((p for p in kids if p.get("slug") == slug), None)
    else:
        existing = get_page_by_path(slug)

    payload = {
        "title": title,
        "slug": slug,
        "status": status,
        "parent": parent,
        "template": template,
        "content": content,
    }
    if existing:
        page = req("POST", f"/wp-json/wp/v2/pages/{existing['id']}", payload)
        action = "updated"
    else:
        page = req("POST", "/wp-json/wp/v2/pages", payload)
        action = "created"
    set_seo_meta(int(page["id"]), seo_title, seo_desc)
    return action, page


def ensure_post_meta_endpoint():
    snips = req("GET", "/wp-json/code-snippets/v1/snippets")
    sync = next((s for s in snips if s.get("name") == "Sklospecial temporary post meta REST"), None)
    if not sync:
        print("post-meta snippet missing — SEO meta skipped")
        return
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


def deactivate_post_meta_endpoint():
    snips = req("GET", "/wp-json/code-snippets/v1/snippets")
    sync = next((s for s in snips if s.get("name") == "Sklospecial temporary post meta REST"), None)
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


def main():
    stats = {"created": 0, "updated": 0, "errors": 0}
    ensure_post_meta_endpoint()

    # Resolve / create pillar parents
    parent_ids: dict[str, int] = {}
    for slug, meta in CATEGORIES.items():
        page = get_page_by_path(slug)
        if slug == "sklenene-steny" and not page:
            action, page = upsert_page(
                title="Skleněné stěny na míru",
                slug="sklenene-steny",
                parent=0,
                template=KATALOG_TEMPLATE,
                seo_title="Skleněné stěny na míru | Sklospeciál",
                seo_desc="Skleněné stěny a zabudování na míru. Online zaměření, výroba, montáž. Rodinná firma, 30 let.",
                content="<!-- pillar: sklenene-steny -->",
            )
            stats[action if action in stats else "created"] += 1
            print(action, page.get("link"))
        if not page:
            print(f"ERROR missing pillar {slug}")
            stats["errors"] += 1
            continue
        parent_ids[slug] = int(page["id"])
        CATEGORIES[slug]["parent_id"] = int(page["id"])

    # Type landings
    for t in TYPE_LANDINGS:
        parent = get_page_by_path(t["parent_path"])
        if not parent:
            print(f"ERROR missing parent for type {t['slug']}: {t['parent_path']}")
            stats["errors"] += 1
            continue
        try:
            action, page = upsert_page(
                title=t["title"],
                slug=t["slug"],
                parent=int(parent["id"]),
                template=TEMPLATE,
                seo_title=t["seo_title"],
                seo_desc=t["seo_desc"],
                content=f"<!-- seo-type:{t['slug']} -->",
            )
            stats[action] += 1
            print(action, page.get("link"))
        except Exception as e:
            stats["errors"] += 1
            print("ERROR type", t["slug"], e)

    # City landings
    for cat_slug, cat in CATEGORIES.items():
        pid = cat.get("parent_id") or parent_ids.get(cat_slug)
        if not pid:
            continue
        for city_slug, city_name, locative, v in CITIES:
            title = f"{cat['h1']} {city_name}"
            seo_title = f"{cat['h1']} {city_name} na míru | Sklospeciál"
            seo_desc = (
                f"{cat['h1']} {v} {locative} — online zaměření, výroba na míru, montáž. "
                f"Rodinná firma, 30 let."
            )
            try:
                action, page = upsert_page(
                    title=title,
                    slug=city_slug,
                    parent=int(pid),
                    template=TEMPLATE,
                    seo_title=seo_title,
                    seo_desc=seo_desc,
                    content=f"<!-- seo-city:{cat_slug}:{city_slug} -->",
                )
                stats[action] += 1
                if stats["created"] + stats["updated"] <= 5 or (stats["created"] + stats["updated"]) % 15 == 0:
                    print(action, page.get("link"))
            except Exception as e:
                stats["errors"] += 1
                print("ERROR city", cat_slug, city_slug, e)
            time.sleep(0.05)

    # HTML sitemap
    try:
        action, page = upsert_page(
            title="Mapa stránek",
            slug="mapa-stranek",
            parent=0,
            template=MAPA_TEMPLATE,
            seo_title="Mapa stránek | Sklospeciál",
            seo_desc="Přehled kategorií, typů a městských landings Sklospeciál.",
            content="<!-- sitemap -->",
        )
        stats[action] += 1
        print(action, page.get("link"))
    except Exception as e:
        stats["errors"] += 1
        print("ERROR mapa", e)

    # Add sklenene-steny to menu if missing (no city dump)
    try:
        items = req("GET", "/wp-json/wp/v2/menu-items?menus=3&per_page=100")
        steny = get_page_by_path("sklenene-steny")
        has = any(
            ("sklenene-steny" in (i.get("url") or "")) or ("sklenene-pricky" in (i.get("url") or ""))
            for i in items
        )
        if steny and not has:
            req(
                "POST",
                "/wp-json/wp/v2/menu-items",
                {
                    "title": "Skleněné stěny",
                    "status": "publish",
                    "menus": 3,
                    "object": "page",
                    "object_id": int(steny["id"]),
                    "type": "post_type",
                    "menu_order": 25,
                },
            )
            print("menu: added Skleněné stěny")
    except Exception as e:
        print("warn menu", e)

    deactivate_post_meta_endpoint()

    expected_cities = len(CATEGORIES) * len(CITIES)
    print(
        json.dumps(
            {
                "stats": stats,
                "expected_city_pages": expected_cities,
                "categories": len(CATEGORIES),
                "cities": len(CITIES),
                "type_landings": len(TYPE_LANDINGS),
            },
            ensure_ascii=False,
            indent=2,
        )
    )


if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        print("FATAL", e, file=sys.stderr)
        sys.exit(1)
