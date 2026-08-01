#!/usr/bin/env python3
"""Create / update Sklospecial /pruvodce/ SEO guide pages via WP REST."""

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
TEMPLATE = "template-pruvodce.php"
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


def get_page_by_slug(slug: str, parent: int = 0):
    q = urllib.parse.urlencode({"slug": slug, "per_page": 20})
    pages = req("GET", f"/wp-json/wp/v2/pages?{q}")
    if not pages:
        return None
    if parent:
        match = next((p for p in pages if int(p.get("parent") or 0) == parent), None)
        return match or pages[0]
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
    sync = next((s for s in snips if s.get("name") == "Sklospecial temporary post meta REST"), None)
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


def upsert_page(*, title: str, slug: str, parent: int, seo_title: str, seo_desc: str, content: str):
    existing = get_page_by_slug(slug, parent)
    payload = {
        "title": title,
        "slug": slug,
        "status": "publish",
        "parent": parent,
        "template": TEMPLATE,
        "content": content,
    }
    if existing:
        page = req("POST", f"/wp-json/wp/v2/pages/{existing['id']}", payload)
        action = "updated"
    else:
        page = req("POST", "/wp-json/wp/v2/pages", payload)
        action = "created"
    set_seo_meta(int(page["id"]), seo_title, seo_desc)
    link = page.get("link") or f"{WP}/{slug}/"
    print(f"{action} {slug} id={page['id']} {link}")
    return page


HUB_CONTENT = """
<p>Vyber si téma. Každý článek je krátký, konkrétní a odkazuje dál do katalogu nebo studia.</p>
""".strip()

POSUVNE_CONTENT = """
<p>Oba typy dveří řeší stejnou věc — oddělit místnosti sklem. Liší se tím, jak se otevírají a kolik prostoru potřebují.</p>

<h2>Posuvné skleněné dveře</h2>
<ul>
<li>Nezabírají prostor před otvorem — křídlo jezdí podél stěny, nebo do pouzdra.</li>
<li>Hodí se do úzkých chodeb, šaten a místností, kde by otevírané křídlo překáželo.</li>
<li>Počítáš s volnou stěnou vedle otvoru (u klasického posuvu), nebo se stavebním pouzdrem.</li>
</ul>
<p>Prohlédni si <a href="/sklenene-dvere/posuvne/">posuvné dveře v katalogu</a> nebo <a href="/sklenene-dvere/posuvne/do-pouzdra/">posuv do pouzdra</a>.</p>

<h2>Otevírané (a kyvné) dveře</h2>
<ul>
<li>Klasický pohyb křídla — potřebuješ volný prostor před otvorem.</li>
<li>Často s pevnou nebo nastavitelnou zárubní; snadno zapadnou do běžných interiérových návyků.</li>
<li>Kyvné (otočné) dveře se otevírají oběma směry — praktické u průchodů s vyšším provozem.</li>
</ul>
<p>Mrkni na <a href="/sklenene-dvere/otevirane/">otevírané</a> a <a href="/sklenene-dvere/otocne/">kyvné dveře</a>.</p>

<h2>Rychlé srovnání</h2>
<table>
<thead>
<tr><th></th><th>Posuvné</th><th>Otevírané / kyvné</th></tr>
</thead>
<tbody>
<tr><th>Prostor před otvorem</th><td>Nezabírá</td><td>Potřebuješ ho</td></tr>
<tr><th>Stěna vedle otvoru</th><td>Ano (u posuvu podél stěny)</td><td>Ne nutně</td></tr>
<tr><th>Stavební příprava</th><td>Pouzdro = větší zásah</td><td>Obvykle menší</td></tr>
<tr><th>Soukromí a těsnění</th><td>Dobré při správném systému</td><td>Často těsnější u zárubně</td></tr>
</tbody>
</table>

<h2>Jak se rozhodnout</h2>
<ol>
<li>Změř otvor a podívej se, kam by křídlo jelo nebo se otáčelo.</li>
<li>Vyfoť stěny a podlahu — z fotek poznáme, co dává smysl.</li>
<li>Ve studiu si typ vyzkoušíš a pošleš poptávku.</li>
</ol>
<p><a href="/navod-na-zamereni/">Jak zaměřit otvor →</a></p>
""".strip()

CENA_CONTENT = """
<p>Orientační ceny „od“ bereme z katalogu. Finální nabídka vždy sedí na tvé rozměry, sklo, kování a montáž — proto nejprve pošli podklady.</p>

<h2>Co cenu nejvíc ovlivní</h2>
<ul>
<li><strong>Rozměry</strong> — větší plocha skla = vyšší cena.</li>
<li><strong>Typ dveří</strong> — posuv, otevírané, kyvné, zárubeň, pouzdro.</li>
<li><strong>Sklo</strong> — čiré, matné, laminované, dekor / grafika.</li>
<li><strong>Systém a kování</strong> — lišty, panty, madla, pojezdy.</li>
<li><strong>Montáž a doprava</strong> — podle lokality a připravenosti otvoru.</li>
</ul>

<h2>Orientační pásma (katalog „od“)</h2>
<table>
<thead>
<tr><th>Typ</th><th>Orientačně od</th></tr>
</thead>
<tbody>
<tr><td><a href="/sklenene-dvere/otevirane/">Otevírané dveře</a></td><td>od 9&nbsp;700&nbsp;Kč</td></tr>
<tr><td><a href="/sklenene-dvere/otocne/">Kyvné (otočné) dveře</a></td><td>od 9&nbsp;800&nbsp;Kč</td></tr>
<tr><td><a href="/sklenene-dvere/posuvne/">Posuvné dveře</a></td><td>od 10&nbsp;100&nbsp;Kč</td></tr>
<tr><td><a href="/sklenene-dvere/dvere-se-zarubni/">Dveře se zárubní</a></td><td>od 10&nbsp;900&nbsp;Kč</td></tr>
<tr><td><a href="/sprchove-kouty/">Sprchové kouty</a></td><td>od 4&nbsp;600&nbsp;Kč</td></tr>
<tr><td><a href="/zabradli/">Skleněné zábradlí</a></td><td>od 2&nbsp;800&nbsp;Kč</td></tr>
</tbody>
</table>
<p>Orientační cena od — finální nabídka podle rozměrů a provedení.</p>

<h2>Jak získat přesnou nabídku</h2>
<ol>
<li>Zaměř otvor (nebo přijedeme).</li>
<li>Ve studiu si složíš dveře a pošleš návrh.</li>
<li>Nebo napiš rovnou poptávku s rozměry a fotkami.</li>
</ol>
<p><a href="/poptavka/">Poptávka →</a> · <a href="/navod-na-zamereni/">Návod na zaměření →</a></p>
""".strip()

WALKIN_CONTENT = """
<p>Walk-in je sprchová stěna bez klasických dveří — otevřený vstup, čisté linie a snadnější údržba prostoru kolem sprchy.</p>

<h2>Proč walk-in dává smysl</h2>
<ul>
<li>Žádné křídlo, které by se otevíralo do koupelny.</li>
<li>Snadnější přístup — vhodné i tam, kde klasické dveře překážejí.</li>
<li>Sklo na míru sedí přesně na vaničku, žlab nebo spádovanou podlahu.</li>
<li>Můžeš zvolit čiré, matné nebo dekorativní sklo podle soukromí.</li>
</ul>

<h2>Na co myslet předem</h2>
<ul>
<li>Spád podlahy a odvodnění — voda má zůstat ve sprchové zóně.</li>
<li>Šířka vstupu a výška stěny — zaměř na 3 místech, ber nejmenší hodnotu.</li>
<li>Ukotvení do stěny / podlahy / stropu podle dispozice.</li>
</ul>
<p>Detailní nabídku najdeš v katalogu: <a href="/sprchove-kouty/walk-in/">Walk-in sprchové kouty</a>. Širší přehled je u <a href="/sprchove-kouty/">sprchových koutů</a>.</p>

<h2>Další krok</h2>
<p>Pošli rozměry a fotky koupelny — připravíme nabídku. Zaměření zvládneš podle <a href="/navod-na-zamereni/">návodu</a>, nebo přijedeme.</p>
""".strip()

ZABRADLI_CONTENT = """
<p>Skleněné zábradlí drží výhled a bezpečí na schodišti, terase nebo galerii. Montáž sedí na kotvení, tloušťku skla a přesné zaměření.</p>

<h2>Co obvykle řešíme</h2>
<ul>
<li>Typ kotvení — do podlahy, na bok desky, sloupek / madlo.</li>
<li>Sklo — tloušťka a laminace podle normy a výšky pádu.</li>
<li>Výška a délka úseků — včetně rohů a ukončení.</li>
<li>Podklad — beton, dřevo, ocel; stav stavby při montáži.</li>
</ul>
<p>Prohlédni si nabídku: <a href="/zabradli/">Skleněné zábradlí</a> · <a href="/zabradli/schodiste/">Zábradlí na schodiště</a>.</p>

<h2>Zaměření a montáž</h2>
<ol>
<li>Zaměř délky a výšky, vyfoť detail kotvení a okolí.</li>
<li>Napiš, jestli jde o novostavbu, rekonstrukci, interiér nebo exteriér.</li>
<li>Připravíme nabídku; montáž naplánujeme podle připravenosti stavby.</li>
</ol>
<p>Zaměření zvládneš sám podle <a href="/navod-na-zamereni/">návodu</a> — u zábradlí často pomůže i naše zaměření na místě. Stačí napsat <a href="/poptavka/">poptávku</a>.</p>
""".strip()

NAVOD_RELATED = """
<hr />
<h2>Související průvodce</h2>
<p>Další krátké články k výběru a ceně najdeš v <a href="/pruvodce/">průvodci</a> — například <a href="/pruvodce/posuvne-vs-otevirane/">posuvné vs. otevírané</a> nebo <a href="/pruvodce/cena-sklenenych-dveri/">cena skleněných dveří</a>.</p>
""".strip()


def soft_link_navod():
    page = get_page_by_slug("navod-na-zamereni")
    if not page:
        print("navod page missing")
        return
    full = req("GET", f"/wp-json/wp/v2/pages/{page['id']}?context=edit")
    content = full.get("content", {}).get("raw") or ""
    if "/pruvodce/" in content and "Související průvodce" in content:
        print("navod already linked")
        return
    # strip previous soft block if partial
    if "Související průvodce" in content:
        content = content.split("<hr />")[0].rstrip()
    new_content = content.rstrip() + "\n\n" + NAVOD_RELATED + "\n"
    req("POST", f"/wp-json/wp/v2/pages/{page['id']}", {"content": new_content})
    print(f"updated navod soft-link id={page['id']}")


def ensure_menu_item(page_id: int, title: str, menu_order: int = 15):
    items = req("GET", f"/wp-json/wp/v2/menu-items?menus={MENU_ID}&per_page=100")
    existing = next(
        (
            it
            for it in items
            if int(it.get("object_id") or 0) == page_id
            and (it.get("title", {}).get("rendered") == title or it.get("title") == title)
        ),
        None,
    )
    # also match by URL path
    if not existing:
        existing = next((it for it in items if "/pruvodce/" in (it.get("url") or "") and it.get("parent") in (0, "0", None)), None)
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
        print(f"menu updated id={existing['id']} {title}")
    else:
        created = req("POST", "/wp-json/wp/v2/menu-items", payload)
        print(f"menu created id={created.get('id')} {title}")


def main():
    ensure_post_meta_endpoint()
    try:
        hub = upsert_page(
            title="Průvodce",
            slug="pruvodce",
            parent=0,
            seo_title="Průvodce skleněnými dveřmi a sprchou | Sklospeciál",
            seo_desc="Krátké průvodce: posuvné vs. otevírané, cena dveří, walk-in sprcha, zábradlí a zaměření. Konkrétně a bez omáčky.",
            content=HUB_CONTENT,
        )
        hub_id = int(hub["id"])

        upsert_page(
            title="Posuvné vs. otevírané skleněné dveře",
            slug="posuvne-vs-otevirane",
            parent=hub_id,
            seo_title="Posuvné vs. otevírané skleněné dveře | Sklospeciál",
            seo_desc="Kdy zvolit posuvné a kdy otevírané skleněné dveře. Srovnání, tipy k prostoru a odkaz do katalogu i studia.",
            content=POSUVNE_CONTENT,
        )
        upsert_page(
            title="Cena skleněných dveří",
            slug="cena-sklenenych-dveri",
            parent=hub_id,
            seo_title="Cena skleněných dveří — co ji ovlivní | Sklospeciál",
            seo_desc="Co ovlivní cenu skleněných dveří a orientační pásma „od“ z katalogu. Pošli rozměry a fotky pro přesnou nabídku.",
            content=CENA_CONTENT,
        )
        upsert_page(
            title="Walk-in sprchový kout",
            slug="walk-in-sprchovy-kout",
            parent=hub_id,
            seo_title="Walk-in sprchový kout — výhody a tipy | Sklospeciál",
            seo_desc="Walk-in sprchový kout bez dveří: výhody, na co myslet při zaměření a odkaz do katalogu walk-in stěn.",
            content=WALKIN_CONTENT,
        )
        upsert_page(
            title="Skleněné zábradlí a montáž",
            slug="zabradli-montaz",
            parent=hub_id,
            seo_title="Skleněné zábradlí a montáž | Sklospeciál",
            seo_desc="Přehled skleněného zábradlí, kotvení a montáže. Co zaměřit a jak poslat podklady k nabídce.",
            content=ZABRADLI_CONTENT,
        )

        soft_link_navod()
        # Near Návod (order 15) — Průvodce as 14.5-ish → use 14.5 not int; WP uses int → 14 before Návod? Realizace=14, Návod=15 → put Průvodce at 15 and bump? Keep next to Návod as 15.5 → use 15 and leave Návod.
        ensure_menu_item(hub_id, "Průvodce", menu_order=15)
    finally:
        deactivate_post_meta_endpoint()


if __name__ == "__main__":
    main()
