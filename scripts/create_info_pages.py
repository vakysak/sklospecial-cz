#!/usr/bin/env python3
"""Create/update Sklospecial legal & info pages + footer menu links via WP REST."""

from __future__ import annotations

import base64
import json
import os
import ssl
import sys
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

PAGES = [
    {
        "slug": "zaruka",
        "title": "Záruka a servis",
        "template": "page-zaruka.php",
        "menu_title": "Záruka",
        "menu_order": 20,
        "seo_title": "Záruka, reklamace a servis | Sklospeciál",
        "seo_desc": "Zákonná odpovědnost za vady, pozáruční servis a volitelná prodloužená záruka +1 rok za 10 % ceny výrobku (bez dopravy a montáže).",
        "content": "<p>Obsah stránky je v šabloně tématu.</p>",
    },
    {
        "slug": "doprava",
        "title": "Doprava a montáž",
        "template": "page-doprava.php",
        "menu_title": "Doprava a montáž",
        "menu_order": 21,
        "seo_title": "Doprava a montáž | Sklospeciál",
        "seo_desc": "Doprava skla po ČR a orientační montáž: dveře od 2 500 Kč, sprcha od 3 500 Kč, zábradlí od 1 500 Kč/bm. Finální cena v nabídce.",
        "content": "<p>Obsah stránky je v šabloně tématu.</p>",
    },
    {
        "slug": "platba",
        "title": "Platební podmínky",
        "template": "page-platba.php",
        "menu_title": "Platba",
        "menu_order": 22,
        "seo_title": "Platební podmínky | Sklospeciál",
        "seo_desc": "Platba převodem podle nabídky a faktury. Výroba obvykle po přijetí sjednané zálohy.",
        "content": "<p>Obsah stránky je v šabloně tématu.</p>",
    },
    {
        "slug": "doba-realizace",
        "title": "Doba realizace",
        "template": "page-doba-realizace.php",
        "menu_title": "Lhůty",
        "menu_order": 23,
        "seo_title": "Doba realizace a lhůty | Sklospeciál",
        "seo_desc": "Nabídka do 1–2 PD, výroba orientačně 5–10 PD, doprava 1–3 PD. Závazný termín je v nabídce.",
        "content": "<p>Obsah stránky je v šabloně tématu.</p>",
    },
]

OP_CONTENT = """
<p><em>Platné od 1.&nbsp;8.&nbsp;2026. Vztahují se na poptávkový prodej na míru značky Sklospeciál.</em></p>

<h2>1. Prodávající</h2>
<ul>
<li>Stolařství Aleš s.r.o., IČO 29457335, DIČ CZ29457335</li>
<li>Sídlo: Horní Bludovice 193, 739 37 Horní Bludovice</li>
<li>Kontakt / provozovna: Prostřední Bludovice 193, 739 37 Horní Bludovice</li>
<li>E-mail: <a href="mailto:info@sklospecial.eu">info@sklospecial.eu</a> · tel. <a href="tel:+420736134604">+420 736 134 604</a></li>
</ul>
<p>Web a značka: Sklospeciál.</p>

<h2>2. Co nabízíme (a co ne)</h2>
<p>Sklospeciál není klasický e-shop s okamžitým nákupem „do košíku“ a automatickou expedicí katalogového zboží. Nabízíme zakázkovou výrobu na míru (zejména skleněné dveře a související výrobky) podle individuálních rozměrů a specifikace.</p>
<p>Ceny a specifikace na webu / v konfigurátoru jsou orientační. Závazná je až písemná nabídka (e-mail) potvrzená objednávkou / úhradou dle domluvy.</p>

<h2>3. Průběh objednávky</h2>
<ol>
<li><strong>Poptávka</strong> — formulář, e-mail, telefon nebo konfigurátor (rozměry, fotky, popis).</li>
<li><strong>Nabídka</strong> — individuální nabídka (cena, specifikace, termín, doprava / předání).</li>
<li><strong>Potvrzení a faktura</strong> — po odsouhlasení vystavíme zálohovou / daňový doklad dle domluvy.</li>
<li><strong>Výroba na míru</strong> — podle odsouhlasené specifikace; změny po zahájení výroby mohou znamenat vícenáklady nebo nový termín.</li>
<li><strong>Předání / doprava</strong> — dle nabídky (osobní odběr, doprava, případně montáž).</li>
</ol>
<p>Orientační lhůty: stránka <a href="/doba-realizace/">Doba realizace</a>.</p>

<h2>4. Cena, platba, DPH</h2>
<p>Cena je uvedena v nabídce včetně / bez DPH dle textu nabídky. Platba probíhá převodem na účet uvedený na faktuře, není-li domluveno jinak. Výrobu obvykle zahajujeme po přijetí sjednané zálohy. Podrobnosti: <a href="/platba/">Platební podmínky</a>.</p>

<h2>5. Termíny</h2>
<p>Termín výroby a dodání je vždy uveden v nabídce a závisí na dostupnosti materiálů, vytížení výroby a včasnosti vašich podkladů (rozměry, fotky, schválení). Orientační lhůty na webu nejsou závazné.</p>

<h2>6. Zaměření a odpovědnost za rozměry</h2>
<p>U modelu „pracujeme na dálku“ zákazník obvykle zaměřuje sám podle návodu. Odpovědnost za správnost zaslaných rozměrů nese zákazník, pokud není výslovně sjednáno zaměření naší firmou. Výrobek vyrobený dle vámi potvrzených rozměrů nelze reklamovat jen proto, že se později ukáže chyba v zaměření.</p>

<h2>7. Odstoupení od smlouvy (spotřebitel) — zakázková výroba</h2>
<p>Nejde o standardní nákup hotového zboží s lhůtou 14 dnů pro odstoupení jako u běžného e-shopu.</p>
<p>U smlouvy o dodání zboží vyrobeného podle požadavků spotřebitele nebo přizpůsobeného jeho osobním potřebám nelze odstoupit podle §&nbsp;1837 písm.&nbsp;d) občanského zákoníku (zákon č.&nbsp;89/2012&nbsp;Sb.), pokud již byla zahájena výroba podle odsouhlasené specifikace / po přijetí zálohy určené na výrobu.</p>
<p>Do okamžiku zahájení výroby můžete poptávku nebo objednávku zrušit — případné již vynaložené náklady (např. individuální zpracování podkladů) si můžeme dohodnout v nabídce.</p>
<p>Podnikatelům (B2B) se ustanovení o spotřebitelském odstoupení neuplatní; řídíme se nabídkou a občanským zákoníkem v rozsahu smluv mezi podnikateli.</p>

<h2>8. Reklamace, vady a záruka</h2>
<p>Zboží při převzetí zkontrolujte. Zjevné vady (poškození při dopravě, zjevná odchylka od specifikace) reklamujte bez zbytečného odkladu, ideálně písemně e-mailem, s fotodokumentací.</p>
<p>U spotřebitelů odpovídáme za vady podle právních předpisů (typicky 24 měsíců od převzetí). Na skle se mohou vyskytovat běžné optické jevy a tolerance dle norem / praxe sklářské výroby; drobné odchylky v mezích tolerance nejsou vadou.</p>
<p>Volitelně lze sjednat <strong>prodlouženou smluvní záruku (+1&nbsp;rok)</strong> za poplatek <strong>10&nbsp;% ceny výrobku</strong> (bez dopravy a montáže). Kryje výrobní vady; typicky <strong>nekryje</strong> rozbití skla nárazem či neodbornou manipulací. Podrobnosti: <a href="/zaruka/">Záruka a servis</a>. Prodloužená záruka nenahrazuje ani neomezuje zákonná práva z vadného plnění.</p>

<h2>9. Doprava a předání</h2>
<p>Riziko škody na věci přechází dle domluvy v nabídce (typicky předáním dopravci nebo při osobním odběru). Sklo vyžaduje opatrnou manipulaci — dodržujte pokyny k dopravě a skladování. Možnosti, náklady a orientační montáž: <a href="/doprava/">Doprava a montáž</a>.</p>

<h2>10. Duševní vlastnictví</h2>
<p>Podklady konfigurátoru, texty a fotografie na webu jsou chráněny. Nepoužívejte je komerčně bez souhlasu.</p>

<h2>11. Ochrana údajů</h2>
<p>Zpracování osobních údajů popisuje stránka <a href="/ochrana-osobnich-udaju/">Ochrana osobních údajů</a>.</p>

<h2>12. Rozhodné právo</h2>
<p>Tyto podmínky se řídí právem České republiky. Spory se snažíme řešit dohodou. Spotřebitel může využít i mimosoudní řešení spotřebitelských sporů (ČOI).</p>
<p>Otázky k podmínkám: <a href="mailto:info@sklospecial.eu">info@sklospecial.eu</a>.</p>
<p><em>Tento text je informativní shrnutí obchodní praxe; doporučujeme právní kontrolu před ostrým provozem.</em></p>
""".strip()


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
    q = urllib.parse.urlencode({"slug": slug, "per_page": 20, "status": "publish,draft,private"})
    pages = req("GET", f"/wp-json/wp/v2/pages?{q}")
    if not pages:
        q2 = urllib.parse.urlencode({"slug": slug, "per_page": 20, "context": "edit", "status": "any"})
        try:
            pages = req("GET", f"/wp-json/wp/v2/pages?{q2}")
        except Exception:
            pages = []
    if not pages:
        return None
    return next((p for p in pages if int(p.get("parent") or 0) == 0), pages[0])


def upsert_page(spec: dict) -> dict:
    existing = get_page_by_slug(spec["slug"])
    payload = {
        "title": spec["title"],
        "slug": spec["slug"],
        "status": "publish",
        "template": spec["template"],
        "content": spec.get("content") or "",
    }
    if existing:
        page = req("POST", f"/wp-json/wp/v2/pages/{existing['id']}", payload)
        action = "update"
    else:
        page = req("POST", "/wp-json/wp/v2/pages", payload)
        action = "create"
    print(f"{action} /{spec['slug']}/ id={page['id']}")
    set_seo_meta(page["id"], spec["seo_title"], spec["seo_desc"])
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


def list_menu_items():
    q = urllib.parse.urlencode({"menus": MENU_ID, "per_page": 100})
    return req("GET", f"/wp-json/wp/v2/menu-items?{q}")


def ensure_menu_item(title: str, page_id: int, url: str, menu_order: int):
    items = list_menu_items()
    existing = None
    for it in items:
        obj = it.get("object_id")
        if str(obj) == str(page_id):
            existing = it
            break
        if it.get("url") and url.rstrip("/") in str(it.get("url")):
            existing = it
            break
        t = it.get("title")
        if isinstance(t, dict):
            t = t.get("rendered")
        if t == title:
            existing = it
            break

    payload = {
        "title": title,
        "type": "post_type",
        "object": "page",
        "object_id": page_id,
        "parent": 0,
        "menu_order": menu_order,
        "menus": MENU_ID,
        "status": "publish",
        "url": url,
    }
    if existing:
        item = req("POST", f"/wp-json/wp/v2/menu-items/{existing['id']}", payload)
        print(f"  menu update {title} id={item['id']}")
    else:
        item = req("POST", "/wp-json/wp/v2/menu-items", payload)
        print(f"  menu create {title} id={item['id']}")
    return item


def update_op():
    page = get_page_by_slug("obchodni-podminky")
    if not page:
        raise RuntimeError("obchodni-podminky page missing")
    payload = {
        "title": "Obchodní podmínky",
        "content": OP_CONTENT,
        "status": "publish",
    }
    updated = req("POST", f"/wp-json/wp/v2/pages/{page['id']}", payload)
    print(f"update /obchodni-podminky/ id={updated['id']}")
    set_seo_meta(
        updated["id"],
        "Obchodní podmínky | Sklospeciál",
        "Obchodní podmínky zakázkové výroby Sklospeciál / Stolařství Aleš s.r.o. — poptávka, platba, doprava, reklamace.",
    )


def main():
    created = []
    for spec in PAGES:
        page = upsert_page(spec)
        created.append((spec, page))
    update_op()
    for spec, page in created:
        link = page.get("link") or f"{WP}/{spec['slug']}/"
        ensure_menu_item(spec["menu_title"], page["id"], link, spec["menu_order"])
    print("done")


if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        print("ERROR:", e, file=sys.stderr)
        sys.exit(1)
