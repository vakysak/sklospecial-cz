#!/usr/bin/env python3
"""Scrape Quba Glass product detail pages (options, surcharges, descriptions).

Reads scripts/output/qubaglass_katalog.json, fetches each /pl/p/... page,
parses Shoper product-variants form, translates to Czech, converts PLN
surcharges → CZK (round(pln * 5.8 * 1.45)), and writes
scripts/output/product_details.json keyed by product_url.

Resume-friendly: skips URLs already present in the output file.

Usage:
  scripts/.venv/bin/python scripts/scrape_product_details.py
  scripts/.venv/bin/python scripts/scrape_product_details.py --limit 20
  scripts/.venv/bin/python scripts/scrape_product_details.py --only 1170,1772
"""

from __future__ import annotations

import argparse
import json
import logging
import random
import re
import sys
import time
from pathlib import Path
from typing import Any
from urllib.parse import urljoin, urlparse

import requests
from bs4 import BeautifulSoup, NavigableString, Tag

# Reuse listing scraper helpers
sys.path.insert(0, str(Path(__file__).resolve().parent))
from qubaglass_scraper import (  # noqa: E402
    BASE_URL,
    HEADERS,
    PLN_TO_CZK,
    MARGIN,
    absolute_url,
    parse_price_pln,
    price_czk,
    translate_name,
)

DELAY_MIN = 0.5
DELAY_MAX = 0.8
REQUEST_TIMEOUT = 35
SCRIPT_DIR = Path(__file__).resolve().parent
DEFAULT_KATALOG = SCRIPT_DIR / "output" / "qubaglass_katalog.json"
DEFAULT_OUT = SCRIPT_DIR / "output" / "product_details.json"

log = logging.getLogger("quba-details")

# Extra option/description phrases (merged into translate via translate_text)
OPTION_WORD_MAP: dict[str, str] = {
    # option group labels
    "kierunek otwierania": "směr otevírání",
    "kotwa montazowa": "montážní kotva",
    "kotwa montażowa": "montážní kotva",
    "szerokosc wneki okenního": "šířka okenního výklenku",
    "szerokość wnęki okenního": "šířka okenního výklenku",
    "szerokosc wneki (cm)": "šířka výklenku (cm)",
    "szerokość wnęki (cm)": "šířka výklenku (cm)",
    "szerokosc wneki": "šířka výklenku",
    "szerokość wnęki": "šířka výklenku",
    "wysokosc wneki (cm)": "výška výklenku (cm)",
    "wysokość wnęki (cm)": "výška výklenku (cm)",
    "wysokosc wneki": "výška výklenku",
    "wysokość wnęki": "výška výklenku",
    "výška wneki (cm)": "výška výklenku (cm)",
    "výška wneki": "výška výklenku",
    "výška wnęki (cm)": "výška výklenku (cm)",
    "výška wnęki": "výška výklenku",
    "rodzaj zamka": "typ zámku",
    "rodzaj zawiasow": "typ závěsů",
    "rodzaj zawiasów": "typ závěsů",
    "rodzaj wzoru": "typ vzoru",
    "rodzaj folii": "typ fólie",
    "rodzaj kolek w systemie": "typ koleček v systému",
    "rodzaj szkła": "typ skla",
    "rodzaj szkla": "typ skla",
    "kolor futryny": "barva zárubně",
    "kolor wspornikow": "barva konzol",
    "kolor wsporników": "barva konzol",
    "kolor folii": "barva fólie",
    "kolor szkla": "barva skla",
    "kolor szkła": "barva skla",
    "kolor spigotow": "barva spigotů",
    "kolor spigotów": "barva spigotů",
    "opaski szerokosc": "šířka lišt",
    "opaski szerokość": "šířka lišt",
    "podciecie wentylacyjne": "ventilační podříznutí",
    "podcięcie wentylacyjne": "ventilační podříznutí",
    "zakres regulacji futryny": "rozsah regulace zárubně",
    "uszczelka sylikonowa": "silikonové těsnění",
    "uszczelka silikonowa": "silikonové těsnění",
    "opcja skrecenia dveře": "možnost sešroubování dveří",
    "opcja skręcenia drzwi": "možnost sešroubování dveří",
    "opcja skrecenia drzwi": "možnost sešroubování dveří",
    "system przesuwny wariant": "varianta posuvného systému",
    "grubosc ocieplenia": "tloušťka zateplení",
    "grubość ocieplenia": "tloušťka zateplení",
    "rozety maskujace": "krycí rozety",
    "rozety maskujące": "krycí rozety",
    "grubosc skla": "tloušťka skla",
    "grubość szkła": "tloušťka skla",
    "wymiary szkla": "rozměry skla",
    "wymiary szkła": "rozměry skla",
    "funkcja symetric": "funkce Symmetric",
    "pochwyt do drzwi": "madlo",
    "uchwyt do drzwi": "madlo",
    "uchwyty": "úchyty",
    "uchwyt": "úchyt",
    "kolor mocowań": "barva kování",
    "kolor mocowan": "barva kování",
    "stronność": "strana",
    "stronnosc": "strana",
    "strona matná (szorstka)": "strana matná (drsná)",
    "strona matna (szorstka)": "strana matná (drsná)",
    "wymiary wnęki na szerokość": "šířka výklenku",
    "wymiary wneki na szerokosc": "šířka výklenku",
    "wymiary wnęki na wysokość": "výška výklenku",
    "wymiary wneki na wysokosc": "výška výklenku",
    "wymiar drzwi": "rozměr dveří",
    "wymiary drzwi": "rozměry dveří",
    "wymiar": "rozměr",
    "wymiary": "rozměry",
    "powierzchnia": "povrch",
    "zawiasy": "závěsy",
    "samodomyk": "samozavírač / tichý domyk",
    "cichy domyk": "tichý domyk",
    "domykacz": "samozavírač",
    # choice values
    "czarny mat": "černý mat",
    "czarny": "černý",
    "drzwi lewe": "levé dveře",
    "drzwi prawe": "pravé dveře",
    "zawiasy po prawej stronie": "závěsy vpravo",
    "zawiasy po lewej stronie": "závěsy vlevo",
    "po prawej stronie": "vpravo",
    "po lewej stronie": "vlevo",
    "z lewej strony": "zleva",
    "z prawej strony": "zprava",
    "lewej strony": "levé strany",
    "prawej strony": "pravé strany",
    "obustronnie": "oboustranně",
    "obustronny": "oboustranný",
    "tak - obustronnie": "ano – oboustranně",
    "tak / obustronnie": "ano / oboustranně",
    "tak - obustronny": "ano – oboustranný",
    "kotwa montazowa": "montážní kotva",
    "kotwa montażowa": "montážní kotva",
    "kotwa porotherm": "kotva Porotherm",
    "kotwa kotwa": "kotva",
    "lewe": "levé",
    "prawe": "pravé",
    # keep product loanwords idempotent on retranslate
    "madlo antaba": "madlo antaba",
    "pochwyt do drzwi typu antaba": "madlo typu antaba",
    "antaba": "antaba",
    "muszelka": "mušle",
    "satyna": "satin",
    "tak - 1 szt": "ano – 1 ks",
    "tak - 2 szt": "ano – 2 ks",
    "tak - 3 szt": "ano – 3 ks",
    "tak - 4 szt": "ano – 4 ks",
    "tak - 1 szt.": "ano – 1 ks",
    "tak - 2 szt.": "ano – 2 ks",
    "tak - 3 szt.": "ano – 3 ks",
    "tak - 4 szt.": "ano – 4 ks",
    "tak - 4 ks": "ano – 4 ks",
    "tak - 3 ks": "ano – 3 ks",
    "szt.": "ks",
    "szt": "ks",
    "nie": "ne",
    "tak": "ano",
    "bezbarwne": "čiré",
    "bezbarwna": "čirá",
    "bezbarwny": "čirý",
    "matowe": "matné",
    "matowa": "matná",
    "grafitowe": "grafitové",
    "przezroczyste": "čiré",
    "ciemny niebieski mat": "tmavě modrý mat",
    "tyg.": "týd.",
    "uchwyty bokiem, dolem silikon": "úchyty ze strany, zespodu silikon",
    "uchwyty bokiem i dolem": "úchyty ze strany a zespodu",
    "bokiem": "ze strany",
    "dolem": "zespodu",
    # description phrases
    "zabudowa szklana składająca się drzwi wahadłowych oraz z dwóch ścianek stałych":
        "Skleněné zabudování složené z kyvných dveří a dvou pevných příček",
    "zabudowa szklana składająca się z drzwi wahadłowych oraz z dwóch ścianek stałych":
        "Skleněné zabudování složené z kyvných dveří a dvou pevných příček",
    "esg hartowane": "ESG kalené",
    "hartowane": "kalené",
    "do wyboru kolor szkła": "na výběr barva skla",
    "do wyboru również kolor okuć": "na výběr také barva kování",
    "w zestawie": "v sadě",
    "zestaw obejmuje": "sada obsahuje",
    "samozamykacz bez wkuwania": "samozavírač bez zasekání",
    "zawias górny": "horní závěs",
    "trzpień sufitowy": "stropní čep",
    "profil bazowy szklenia stałego": "základní profil pevného zasklení",
    "profil osłony szklenia stałego": "krycí profil pevného zasklení",
    "klemy zaciskowe": "svěrné klemy",
    "zaślepka czołowa": "čelní záslepka",
    "zaślepka maskownicy": "záslepka krytu",
    "produkt wykonywany na zamówienie": "produkt vyráběný na zakázku",
    "nowoczesne drzwi szklane z maskownicą maskującą wózki jezdne":
        "Moderní skleněné dveře s krytem zakrývajícím pojezdové vozíky",
    "drzwi przesuwne design-lux ze szkła": "posuvné dveře Design-Lux ze skla",
    "system ten nie nadaje się do pomieszczeń gdzie występują listwy przypodłogowe":
        "tento systém se nehodí do místností s podlahovými lištami",
    "w przypadku listw - należy wybrać inny system przesuwny - ultra slim":
        "při lištách zvolte jiný posuvný systém – Ultra Slim",
    "w przypadku listw – należy wybrać inny system przesuwny – ultra slim":
        "při lištách zvolte jiný posuvný systém – Ultra Slim",
    "prowadnica": "vodicí lišta",
    "maskownica": "kryt",
    "komplet okuć montażowych": "komplet montážního kování",
    "tafla szklana bezbarwna": "tabule čirého skla",
    "tafla szklana": "tabule skla",
    "wolne, łagodne dociągnięcie skrzydła drzwi": "pomalé, plynulé dotažení křídla dveří",
    "czas wysyłki": "doba expedice",
    "dni roboczych": "pracovních dní",
    "duża ilość": "skladem",
    "jak szyba w oknie": "jako sklo v okně",
    "satynowe": "satinové",
    "trawione": "leptané",
    "parametry": "parametry",
    "klient podaje nam wymiar wnęki": "zákazník uvádí rozměr výklenku",
    "nie przekraczający": "nepřesahující",
    "przyjmujemy, że drzwi będą mieć": "počítáme, že dveře budou mít",
    "ścianki stałe": "pevné příčky",
    "scianki stale": "pevné příčky",
    "w zależności od podanego wymiaru wnęki": "podle uvedeného rozměru výklenku",
    "wysokość": "výška",
    "wysokosc": "výška",
    "szerokość": "šířka",
    "szerokosc": "šířka",
    "wnęki": "výklenku",
    "wneki": "výklenku",
    "maksymalnie": "maximálně",
    "jeśli mają państwo inne wymiary": "pokud máte jiné rozměry",
    "prosimy o maila wtedy dokonamy wyceny": "napište e-mail — připravíme nabídku",
    "opcja pierwsza": "varianta první",
    "opcja druga": "varianta druhá",
    "uwaga": "pozor",
    "instrukcja do pobrania": "návod ke stažení",
    "plikach do pobrania": "souborech ke stažení",
    "pliki do pobrania": "soubory ke stažení",
    "film montażowy": "montážní video",
    "w komplecie nie ma kołków rozporowych": "v sadě nejsou hmoždinky",
    "należy je dostosować do rodzaju swojej ściany": "je potřeba je přizpůsobit typu zdi",
    "wszystkie produkty wykonujemy na zamówienie": "všechny produkty vyrábíme na zakázku",
    "po zakupie nie ma możliwości rezygnacji z zamówienia":
        "po objednání nelze od objednávky odstoupit",
    "inne wymiary wycena mailowa": "jiné rozměry — nabídka e-mailem",
    "listwy przypodłogowe": "podlahové lišty",
    "wózki jezdne": "pojezdové vozíky",
    "uchwytów montażowych": "montážních úchytů",
    "producent qubaglass": "",
    "producent quba glass": "",
    "jakub czyrnek qubaglass": "",
    "qubaglass": "",
}

# Polish-only letters → ASCII (Czech diacritics kept elsewhere)
_PL_DIACRITICS = str.maketrans({
    "ą": "a", "ć": "c", "ę": "e", "ł": "l", "ń": "n",
    "ó": "o", "ś": "s", "ź": "z", "ż": "z",
    "Ą": "A", "Ć": "C", "Ę": "E", "Ł": "L", "Ń": "N",
    "Ó": "O", "Ś": "S", "Ź": "Z", "Ż": "Z",
})


_SURCHARGE_RE = re.compile(
    r"\(\+\s*([\d\s\u00a0\u202f]+[,.]?\d*)\s*zł\)",
    re.IGNORECASE,
)
_MODIFIER_RE = re.compile(
    r"\+\s*([\d\s\u00a0\u202f]+[,.]?\d*)\s*zł",
    re.IGNORECASE,
)
_SHIPPING_RE = re.compile(
    r"Czas\s+wysyłki\s*:?\s*(\d+)\s*dni\s*roboczych",
    re.IGNORECASE,
)


def polite_sleep() -> None:
    time.sleep(random.uniform(DELAY_MIN, DELAY_MAX))


def fold_pl(text: str) -> str:
    """Fold Polish diacritics for matching (Czech diacritics left intact)."""
    return text.translate(_PL_DIACRITICS)


def strip_pl_diacritics(text: str) -> str:
    """Remove leftover Polish-only diacritics from output text."""
    # Keep Czech letters; fold Polish-only: ąęłńśźżć (ó shared — leave if already Czech context)
    table = str.maketrans({
        "ą": "a", "ć": "c", "ę": "e", "ł": "l", "ń": "n",
        "ś": "s", "ź": "z", "ż": "z",
        "Ą": "A", "Ć": "C", "Ę": "E", "Ł": "L", "Ń": "N",
        "Ś": "S", "Ź": "Z", "Ż": "Z",
    })
    return text.translate(table)


def translate_text(text: str) -> str:
    """Translate option labels / description fragments to Czech."""
    if not text:
        return ""
    result = text.strip()
    # Longer keys first — option map then listing WORD_MAP via translate_name
    items = sorted(OPTION_WORD_MAP.items(), key=lambda kv: len(kv[0]), reverse=True)
    for pl, cs in items:
        if not pl:
            continue
        # Match with and without Polish diacritics
        variants = {pl, fold_pl(pl)}
        for variant in sorted(variants, key=len, reverse=True):
            pattern = re.compile(rf"(?<!\w){re.escape(variant)}(?!\w)", re.IGNORECASE)
            result = pattern.sub(cs, result)
    result = translate_name(result)
    # Cleanup leftover Polish manufacturer / empty bits
    result = re.sub(r"\bQuba\s*Glass\b", "", result, flags=re.I)
    result = re.sub(r"\bQubaglass\b", "", result, flags=re.I)
    result = strip_pl_diacritics(result)
    result = re.sub(r"\s{2,}", " ", result).strip(" ,;/-:")
    result = re.sub(r"(?i)\botočné\b", "kyvné", result)
    result = re.sub(r"(?i)\bwahadłowe\b", "kyvné", result)
    result = re.sub(r"(?i)\bwahadlowe\b", "kyvné", result)
    result = re.sub(r"(?i)\bmadlo madlo\b", "madlo", result)
    if result:
        result = result[0].upper() + result[1:] if result[0].islower() else result
    return result


def retranslate_product(detail: dict[str, Any]) -> dict[str, Any]:
    """Re-apply Czech translation on an already-scraped product (no HTTP)."""
    out = dict(detail)
    if out.get("description"):
        out["description"] = translate_text(str(out["description"]))
    out["includes"] = [
        translate_text(str(x)) for x in (out.get("includes") or []) if str(x).strip()
    ]
    options: list[dict[str, Any]] = []
    for g in out.get("options") or []:
        if not isinstance(g, dict):
            continue
        choices = []
        for c in g.get("choices") or []:
            if not isinstance(c, dict):
                continue
            name = translate_text(str(c.get("name") or ""))
            if not name:
                continue
            choices.append({**c, "name": name})
        options.append(
            {
                **g,
                "label": translate_text(str(g.get("label") or "")),
                "choices": choices,
            }
        )
    out["options"] = options
    if out.get("availability"):
        out["availability"] = translate_text(str(out["availability"]))
    files = []
    for f in out.get("files") or []:
        if isinstance(f, dict) and f.get("title"):
            files.append({**f, "title": translate_text(str(f["title"]))})
        else:
            files.append(f)
    out["files"] = files
    return out


def parse_surcharge_pln(raw: str | None) -> float | None:
    if not raw:
        return None
    m = _MODIFIER_RE.search(raw.replace("\xa0", " "))
    if not m:
        return None
    return parse_price_pln(m.group(1) + " zł")


def surcharge_czk(pln: float | None) -> int:
    if pln is None or pln <= 0:
        return 0
    return price_czk(pln)


def strip_surcharge_from_name(name: str) -> str:
    return _SURCHARGE_RE.sub("", name).strip()


def clean_choice_name(raw: str) -> str:
    name = strip_surcharge_from_name(raw or "")
    name = re.sub(r"Wybranie tej opcji.*", "", name, flags=re.I).strip()
    return translate_text(name)


def parse_options(soup: BeautifulSoup) -> list[dict[str, Any]]:
    root = soup.select_one("product-variants") or soup.select_one(
        '[data-module-name="product_variants"]'
    )
    if not root:
        return []

    groups: list[dict[str, Any]] = []

    for el in root.find_all(
        ["text-variant-option", "radio-variant-option", "select-variant-option"]
    ):
        label_raw = (el.get("validation-name-label") or "").strip().rstrip(":")
        if not label_raw:
            lab = el.select_one(".control__label label, .control__label .label, .control__label p")
            if lab:
                label_raw = lab.get_text(" ", strip=True)
                label_raw = label_raw.lstrip("*").strip().rstrip(":")
        label = translate_text(label_raw)
        opt_type = (el.get("type") or el.name or "").replace("-variant-option", "")
        if "text" in (el.name or "") or opt_type == "text":
            groups.append(
                {
                    "label": label or translate_text(label_raw) or "Text",
                    "type": "text",
                    "choices": [],
                }
            )
            continue

        choices: list[dict[str, Any]] = []
        # radios
        for inp in el.select('input[type="radio"]'):
            user_val = (inp.get("data-user-value") or "").strip()
            if not user_val:
                lab = el.find("label", attrs={"for": inp.get("id")})
                user_val = lab.get_text(" ", strip=True) if lab else ""
            pln = parse_surcharge_pln(inp.get("data-price-modifier"))
            if pln is None:
                pln = parse_surcharge_pln(user_val)
            name = clean_choice_name(user_val)
            if not name:
                continue
            choices.append({"name": name, "surcharge_czk": surcharge_czk(pln), "surcharge_pln": pln or 0})

        # h-option selects
        for hop in el.select("h-option"):
            user_val = (hop.get("data-user-value") or "").strip()
            if not user_val:
                span = hop.select_one("h-option-content > span:first-child, span")
                user_val = span.get_text(" ", strip=True) if span else hop.get_text(" ", strip=True)
            pln = parse_surcharge_pln(hop.get("data-price-modifier"))
            if pln is None:
                extra = hop.select_one(".select-option__additional-value")
                if extra:
                    pln = parse_surcharge_pln(extra.get_text(" ", strip=True))
            name = clean_choice_name(user_val)
            if not name:
                continue
            choices.append({"name": name, "surcharge_czk": surcharge_czk(pln), "surcharge_pln": pln or 0})

        # native select fallback
        for opt in el.select("select option"):
            val = (opt.get_text(" ", strip=True) or "").strip()
            if not val or val.lower() in {"wybierz", "select", "—", "-"}:
                continue
            pln = parse_surcharge_pln(opt.get("data-price-modifier") or val)
            name = clean_choice_name(val)
            if name:
                choices.append(
                    {"name": name, "surcharge_czk": surcharge_czk(pln), "surcharge_pln": pln or 0}
                )

        # dedupe by name keeping first
        seen: set[str] = set()
        uniq: list[dict[str, Any]] = []
        for c in choices:
            key = c["name"].lower()
            if key in seen:
                continue
            seen.add(key)
            uniq.append(c)

        groups.append(
            {
                "label": label or "Možnost",
                "type": "select" if uniq else "text",
                "choices": uniq,
            }
        )

    return groups


def extract_includes(text: str) -> list[str]:
    """Pull bullet list after 'W zestawie' / 'Zestaw obejmuje'."""
    lines = [ln.strip() for ln in text.splitlines() if ln.strip()]
    includes: list[str] = []
    capturing = False
    for ln in lines:
        low = ln.lower()
        if re.search(r"w zestawie|zestaw obejmuje", low):
            capturing = True
            # same-line content after colon
            rest = re.split(r":", ln, maxsplit=1)
            if len(rest) > 1 and rest[1].strip().startswith("-"):
                pass
            continue
        if capturing:
            if re.match(r"^(parametry|uwaga|wymiary|do wyboru|opcja|grubość|produkt|prosimy|jeśli|nowoczesne|system|film|pliki|certyfikaty|producent)\b", low):
                break
            if ln.startswith("-") or ln.startswith("•"):
                item = ln.lstrip("-• ").strip()
                if item:
                    includes.append(translate_text(item))
            elif includes and len(ln) < 120 and not ln.endswith(":"):
                # continuation sometimes without dash — stop on new section headers
                if ln[0].isupper() and len(ln.split()) <= 3:
                    break
                includes.append(translate_text(ln))
            else:
                if ln.endswith(":") or len(ln) > 140:
                    break
    return includes[:30]


def html_to_structured_text(desc_el: Tag | None) -> str:
    if not desc_el:
        return ""
    # Remove manufacturer / safety blocks
    for bad in desc_el.select(
        ".product-safety, [class*='safety'], .producer, .manufacturer"
    ):
        bad.decompose()

    parts: list[str] = []
    for child in desc_el.descendants:
        if isinstance(child, NavigableString):
            continue
        if not isinstance(child, Tag):
            continue
        if child.name in {"script", "style", "iframe"}:
            continue

    # Prefer block-wise extraction
    blocks: list[str] = []
    for el in desc_el.find_all(["p", "li", "h2", "h3", "h4", "br"]):
        if el.name == "br":
            continue
        t = el.get_text(" ", strip=True)
        if not t:
            continue
        if el.name == "li":
            blocks.append("- " + t)
        else:
            blocks.append(t)

    if not blocks:
        blocks = [desc_el.get_text("\n", strip=True)]

    # Deduplicate consecutive
    out: list[str] = []
    for b in blocks:
        b = re.sub(r"\s+", " ", b).strip()
        if not b:
            continue
        if out and out[-1] == b:
            continue
        # Skip manufacturer block
        if re.search(r"(?i)jakub\s+czyrnek|orkana|rabka-zdrój|producent", b):
            continue
        if re.search(r"(?i)certyfikaty i ostrzeżenie|product safety|oznaczenie ce", b):
            continue
        out.append(translate_text(b) if not b.startswith("- ") else "- " + translate_text(b[2:]))

    text = "\n".join(out)
    text = re.sub(r"\n{3,}", "\n\n", text).strip()
    return text


def parse_gallery(soup: BeautifulSoup) -> list[str]:
    urls: list[str] = []
    seen: set[str] = set()
    selectors = [
        'img[src*="productGfx"]',
        'img[data-src*="productGfx"]',
        'a[href*="productGfx"]',
        ".product-gallery img",
        "[data-gallery] img",
        "picture source",
    ]
    candidates: list[str] = []
    for sel in selectors:
        for el in soup.select(sel):
            for attr in ("src", "data-src", "href", "srcset"):
                val = el.get(attr)
                if not val:
                    continue
                # srcset: take first URL
                if attr == "srcset":
                    val = val.split(",")[0].strip().split(" ")[0]
                if "productGfx" in val or "userdata" in val:
                    candidates.append(val)

    for raw in candidates:
        url = absolute_url(raw)
        # Prefer larger images
        url = re.sub(r"/\d+_\d+/", "/800_800/", url)
        url = re.sub(r"_0_0/", "_800_800/", url)
        if url in seen:
            continue
        if any(x in url for x in ("storefrontImages", "logo", "icon", "flag")):
            continue
        seen.add(url)
        urls.append(url)
    return urls[:12]


def parse_files(soup: BeautifulSoup) -> list[dict[str, str]]:
    files: list[dict[str, str]] = []
    seen: set[str] = set()
    for a in soup.select('a[href*="/p/file/"], a[href*=".pdf"]'):
        href = a.get("href") or ""
        if "cookie" in href.lower() or "polityce" in (a.get_text() or "").lower():
            continue
        if "/p/file/" not in href and not href.lower().endswith(".pdf"):
            continue
        title = a.get_text(" ", strip=True) or Path(urlparse(href).path).name
        title = re.sub(r"\s+\d+[.,]\d+\s*kB$", "", title, flags=re.I).strip()
        title = translate_text(title) if title else title
        url = absolute_url(href)
        if url in seen:
            continue
        seen.add(url)
        files.append({"title": title, "url": url})
    return files[:20]


def parse_shipping_days(soup: BeautifulSoup, page_text: str) -> int | None:
    el = soup.select_one(".product-shipping-time__time, .shipping-time, [class*='shipping-time']")
    blob = (el.get_text(" ", strip=True) if el else "") + "\n" + page_text
    m = _SHIPPING_RE.search(blob)
    if m:
        return int(m.group(1))
    m2 = re.search(r"(\d+)\s*dni\s*roboczych", blob, re.I)
    if m2:
        return int(m2.group(1))
    return None


def parse_availability(soup: BeautifulSoup, page_text: str) -> str:
    for el in soup.select("[class*='availability'], .product-availability"):
        t = el.get_text(" ", strip=True)
        if t and "dostęp" in t.lower():
            # take value after label
            t = re.sub(r"(?i)dostępność\s*:?\s*", "", t).strip()
            return translate_text(t) if t else ""
    m = re.search(r"Dostępność\s*:\s*([^\n]+)", page_text, re.I)
    if m:
        return translate_text(m.group(1).strip())
    return ""


def parse_base_price_pln(soup: BeautifulSoup) -> float | None:
    info = soup.select_one("#product-info")
    if info and info.string:
        try:
            data = json.loads(info.string)
            if isinstance(data, dict) and "price" in data:
                return float(data["price"])
        except (json.JSONDecodeError, TypeError, ValueError):
            pass
    for sel in ['[itemprop="price"]', ".price", ".product-price"]:
        el = soup.select_one(sel)
        if not el:
            continue
        content = el.get("content") or el.get_text(" ", strip=True)
        pln = parse_price_pln(content)
        if pln:
            return pln
    return None


def scrape_product(session: requests.Session, product_url: str) -> dict[str, Any]:
    r = session.get(product_url, timeout=REQUEST_TIMEOUT)
    r.raise_for_status()
    soup = BeautifulSoup(r.text, "html.parser")
    page_text = soup.get_text("\n", strip=True)

    desc_el = (
        soup.select_one(".resetcss")
        or soup.select_one("[itemprop=description]")
        or soup.select_one("#box_description, .product_description")
    )
    description = html_to_structured_text(desc_el)
    includes = extract_includes(desc_el.get_text("\n", strip=True) if desc_el else page_text)
    options = parse_options(soup)
    images = parse_gallery(soup)
    files = parse_files(soup)
    shipping = parse_shipping_days(soup, page_text)
    availability = parse_availability(soup, page_text)
    base_pln = parse_base_price_pln(soup)

    quba_id = ""
    path = urlparse(product_url).path.rstrip("/")
    tail = path.split("/")[-1]
    if tail.isdigit():
        quba_id = tail

    return {
        "product_url": product_url,
        "quba_id": quba_id,
        "base_price_pln": base_pln,
        "base_price_czk": price_czk(base_pln) if base_pln else None,
        "description": description,
        "includes": includes,
        "options": options,
        "shipping_days": shipping,
        "availability": availability,
        "images": images,
        "files": files,
        "scraped_ok": True,
    }


def load_katalog(path: Path) -> list[dict[str, Any]]:
    data = json.loads(path.read_text(encoding="utf-8"))
    if isinstance(data, dict):
        return list(data.get("products") or [])
    return list(data)


def load_existing(path: Path) -> dict[str, Any]:
    if not path.exists():
        return {"products": {}, "count": 0}
    data = json.loads(path.read_text(encoding="utf-8"))
    if isinstance(data.get("products"), list):
        # convert list → dict by url
        keyed = {}
        for p in data["products"]:
            url = p.get("product_url") or ""
            if url:
                keyed[url] = p
        data["products"] = keyed
    elif not isinstance(data.get("products"), dict):
        data["products"] = {}
    return data


def save_out(path: Path, data: dict[str, Any]) -> None:
    data["count"] = len(data.get("products") or {})
    path.parent.mkdir(parents=True, exist_ok=True)
    tmp = path.with_suffix(".tmp.json")
    tmp.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")
    tmp.replace(path)


def main() -> None:
    parser = argparse.ArgumentParser(description="Scrape Quba product detail pages")
    parser.add_argument("--katalog", type=Path, default=DEFAULT_KATALOG)
    parser.add_argument("--out", type=Path, default=DEFAULT_OUT)
    parser.add_argument("--limit", type=int, default=0, help="Max new products to scrape")
    parser.add_argument("--only", type=str, default="", help="Comma-separated quba ids")
    parser.add_argument("--force", action="store_true", help="Re-scrape even if present")
    parser.add_argument(
        "--retranslate",
        action="store_true",
        help="Re-apply Czech option/description translation on existing product_details.json (no HTTP)",
    )
    parser.add_argument("--save-every", type=int, default=25)
    args = parser.parse_args()

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        datefmt="%H:%M:%S",
    )

    if args.retranslate:
        existing = load_existing(args.out)
        store: dict[str, Any] = existing.setdefault("products", {})
        only_ids = {x.strip() for x in args.only.split(",") if x.strip()} if args.only else set()
        changed = 0
        for url, prod in list(store.items()):
            if not isinstance(prod, dict) or not prod.get("scraped_ok"):
                continue
            qid = str(prod.get("quba_id") or url.rstrip("/").split("/")[-1])
            if only_ids and qid not in only_ids:
                continue
            updated = retranslate_product(prod)
            if updated != prod:
                changed += 1
            store[url] = updated
        save_out(args.out, existing)
        log.info("Retranslated %d / %d products → %s", changed, len(store), args.out)
        return

    products = load_katalog(args.katalog)
    existing = load_existing(args.out)
    store = existing.setdefault("products", {})

    only_ids = {x.strip() for x in args.only.split(",") if x.strip()} if args.only else set()

    todo: list[dict[str, Any]] = []
    for p in products:
        url = (p.get("product_url") or "").strip()
        if not url:
            continue
        quba_id = url.rstrip("/").split("/")[-1]
        if only_ids and quba_id not in only_ids:
            continue
        if not args.force and url in store and store[url].get("scraped_ok"):
            continue
        todo.append(p)

    if args.limit and args.limit > 0:
        todo = todo[: args.limit]

    log.info(
        "Katalog %d · already scraped %d · todo %d",
        len(products),
        len(store),
        len(todo),
    )

    session = requests.Session()
    session.headers.update(HEADERS)

    ok = 0
    fail = 0
    for i, p in enumerate(todo, 1):
        url = p["product_url"]
        try:
            detail = scrape_product(session, url)
            # keep listing name hints
            detail["name_cs"] = p.get("name_cs") or ""
            detail["name_pl"] = p.get("name_pl") or ""
            detail["category"] = p.get("category") or ""
            if detail.get("base_price_czk") is None and p.get("price_czk"):
                detail["base_price_czk"] = int(p["price_czk"])
            if detail.get("base_price_pln") is None and p.get("price_pln") is not None:
                detail["base_price_pln"] = float(p["price_pln"])
            if not detail.get("images") and p.get("image_url"):
                detail["images"] = [p["image_url"]]
            store[url] = detail
            ok += 1
            n_opts = len(detail.get("options") or [])
            n_choices = sum(len(g.get("choices") or []) for g in detail.get("options") or [])
            log.info(
                "[%d/%d] OK %s · options=%d choices=%d ship=%s",
                i,
                len(todo),
                detail.get("quba_id") or url,
                n_opts,
                n_choices,
                detail.get("shipping_days"),
            )
        except Exception as exc:  # noqa: BLE001
            fail += 1
            store[url] = {
                "product_url": url,
                "quba_id": url.rstrip("/").split("/")[-1],
                "scraped_ok": False,
                "error": str(exc),
            }
            log.warning("[%d/%d] FAIL %s · %s", i, len(todo), url, exc)

        if i % args.save_every == 0 or i == len(todo):
            save_out(args.out, existing)
            log.info("Saved checkpoint · %d products in %s", len(store), args.out)

        if i < len(todo):
            polite_sleep()

    save_out(args.out, existing)
    log.info("Done. ok=%d fail=%d total_in_file=%d", ok, fail, len(store))


if __name__ == "__main__":
    main()
