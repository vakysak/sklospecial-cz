#!/usr/bin/env python3
"""Quba Glass (qubaglass.pl / Shoper) → Czech catalog CSV/JSON scraper.

# pip install -r scripts/requirements-scraper.txt
# or: pip install requests beautifulsoup4

Internal prep only — do not upload scraped images to live WP media
or publish product pages with supplier photos until partnership is clear.
"""

from __future__ import annotations

import argparse
import csv
import json
import logging
import random
import re
import time
from collections import Counter
from pathlib import Path
from typing import Any
from urllib.parse import urljoin, urlparse

import requests
from bs4 import BeautifulSoup

# --- pricing: PLN × 5.8 × 1.45 (45% margin) ---
PLN_TO_CZK = 5.8
MARGIN = 1.45

BASE_URL = "https://qubaglass.pl"
DELAY_MIN = 0.8
DELAY_MAX = 1.2
REQUEST_TIMEOUT = 30

# Category path → Czech label. Leaf categories preferred for railings/canopies.
# Order matters for URL dedupe: first category seen wins.
CATEGORIES: list[tuple[str, str]] = [
    # --- Doors (16) ---
    ("/pl/c/Drzwi-przesuwne-DESIGN-LUX/50", "Posuvné dveře Design-Lux"),
    ("/pl/c/Drzwi-przesuwne-Ultra-Slim/51", "Posuvné dveře Ultra Slim"),
    ("/pl/c/Drzwi-przesuwne-LOFT-ART/57", "Posuvné dveře Loft"),
    ("/pl/c/Drzwi-przesuwne-RUROWE/56", "Posuvné dveře – trubkový systém"),
    ("/pl/c/Drzwi-przesuwne-w-KASECIE/55", "Posuvné dveře do pouzdra"),
    ("/pl/c/Drzwi-wahadlowe/33", "Kyvné dveře"),
    ("/pl/c/DRZWI-OTWIERANE/68", "Otevírané dveře"),
    ("/pl/c/Drzwi-z-futryna-stala/37", "Dveře s pevnou zárubní"),
    ("/pl/c/Drzwi-z-futryna-regulowana-TOP/41", "Dveře s nastavitelnou zárubní"),
    ("/pl/c/Drzwi-szklane-z-oscieznica-aluminiowa/79", "Dveře s hliníkovou zárubní"),
    ("/pl/c/Drzwi-szklane-LINIA-LUXE/36", "Linie Luxe"),
    ("/pl/c/Drzwi-szklane-ROCK-GLASS/81", "Rock Glass – industriální linie"),
    ("/pl/c/ZABUDOWY-SZKLANE/87", "Skleněné příčky a zabudování"),
    ("/pl/c/Drzwi-szklane-od-reki/19", "Skladem – rychlá expedice"),
    ("/pl/c/Drzwi-przesuwne-na-wymiar/17", "Posuvné dveře na míru"),
    ("/pl/c/Drzwi-laminowane-kolorowe/20", "Laminované dveře"),
    # --- Door patterns ---
    ("/pl/c/Wzory-kolorowe/23", "Barevné vzory skla"),
    ("/pl/c/Wzory-matowe/24", "Matné vzory skla"),
    # --- Showers ---
    ("/pl/c/Kabiny-szklane/22", "Skleněné sprchové kouty"),
    # --- French balconies ---
    ("/pl/c/Balkony-francuskie/40", "Francouzské balkony"),
    # --- Railings (leaf) ---
    ("/pl/c/Balustrady-do-samodzielnego-montazu/43", "Zábradlí DIY"),
    ("/pl/c/Balustrady-z-montazem/58", "Zábradlí s montáží"),
    ("/pl/c/Profile-do-balustrad/94", "Profily na zábradlí"),
    ("/pl/c/Profile-do-balustrad-FIX-z-przechyleniem-szyby/93", "Profily FIX na zábradlí"),
    # --- Canopies (leaf) ---
    ("/pl/c/DASZKI-DOSTEPNE-OD-REKI/89", "Stříšky skladem"),
    ("/pl/c/DASZKI-SYSTEMOWE/82", "Stříšky systémové"),
    ("/pl/c/Daszki-z-rynna/84", "Stříšky s okapem"),
    ("/pl/c/Daszki-z-czarnymi-okuciami/86", "Stříšky s černým kováním"),
    ("/pl/c/Daszki-na-naciagachpretach/21", "Stříšky na táhlech"),
    ("/pl/c/Daszki-na-wspornikach/54", "Stříšky na konzolách"),
    # --- Other ---
    ("/pl/c/Lustra/52", "Zrcadla"),
    ("/pl/c/Szklo/61", "Sklo"),
]

# Longer phrases first so multi-word replacements win (sorted at runtime too).
WORD_MAP: dict[str, str] = {
    "wykonana ze skla": "vyrobená ze skla",
    "folia": "fólie",
    "sprawdzajace sie": "hodící se",
    "wkladka patentowa wc lub klucz/klucz": "vložka WC nebo klíč/klíč (dle modelu)",
    "wkładka patentowa wc": "vložka WC",
    "wkladka patentowa wc": "vložka WC",
    "patentowa wc": "WC",
    "klucz/klucz": "klíč/klíč",
    "klucz": "klíč",
    "sztywna konstrukcja": "tuhá konstrukce",
    "konstrukcja": "konstrukce",
    "oklejona": "opatřená",
    "ekologiczna": "ekologickou",
    "drewnopodobna folia dekoracyjna": "dřevodekorativní fólií",
    "folia dekoracyjna": "dekorativní fólie",
    "schematy futryny": "schémata zárubně",
    "futryny": "zárubně",
    "swiatla przejscia": "světlá šířka průchodu",
    "światła przejścia": "světlá šířka průchodu",
    "satin szlifowana": "satin broušený",
    "szlifowana": "broušená",
    "aluminium": "hliník",
    "powiazane": "související",
    "powiązane": "související",
    # --- long multi-word phrases ---
    "daszek szklany zadaszenie szklane nad drzwi": "skleněná stříška nad dveře",
    "daszek szklany zadaszenie szklane": "skleněná stříška",
    "daszek szklany nad drzwi": "skleněná stříška nad dveře",
    "daszek szklany": "skleněná stříška",
    "daszki szklane": "skleněné stříšky",
    "zadaszenie szklane nad drzwi": "skleněná stříška nad dveře",
    "zadaszenie szklane": "skleněná stříška",
    "drzwi szklane przesuwne przesuwane": "skleněné posuvné dveře",
    "drzwi szklane przesuwne": "skleněné posuvné dveře",
    "drzwi przesuwne w kasecie chowane w ścianę kaseta bezościeżnicowa": "posuvné dveře do pouzdra bez zárubně",
    "drzwi przesuwne w kasecie chowane w ścianę": "posuvné dveře do pouzdra",
    "w kasecie chowane w ścianę": "do pouzdra",
    "kaseta bezościeżnicowa": "bez zárubně",
    "bezościeżnicowa": "bez zárubně",
    "drzwi szklane otwierane": "skleněné otevírané dveře",
    "drzwi szklane wahadłowe": "skleněné kyvné dveře",
    "drzwi szklane": "skleněné dveře",
    "z lustrem drzwi lustro": "se zrcadlem",
    "lustro drzwi w ramie": "zrcadlo v rámu",
    "drzwi lustro": "",
    "lustro drzwi": "zrcadlo",
    "z lustrem": "se zrcadlem",
    "kabina szklana prysznicowa ścianka + drzwi szklane": "skleněný sprchový kout příčka + dveře",
    "kabina szklana prysznicowa ścianka szklana drzwi + ścianka stała": "skleněný sprchový kout dveře + pevná příčka",
    "kabina szklana prysznicowa ścianka szklana": "skleněný sprchový kout",
    "kabina szklana prysznicowa ścianka": "skleněný sprchový kout",
    "kabina szklana prysznicowa": "skleněný sprchový kout",
    "kabina szklana": "skleněný sprchový kout",
    "kabiny szklane": "skleněné sprchové kouty",
    "zabudowa szklana": "skleněné zabudování",
    "balustrada szklana": "skleněné zábradlí",
    "balustrady szklane": "skleněná zábradlí",
    "profile do balustrad": "profily na zábradlí",
    "profil do balustrady": "profil na zábradlí",
    "profil do balustrad": "profil na zábradlí",
    "profil boczny do balustrady narożny": "boční rohový profil na zábradlí",
    "profil boczny do balustrady": "boční profil na zábradlí",
    "profil narożny do balustrady": "rohový profil na zábradlí",
    "zaślepka profilu bocznego": "záslepka bočního profilu",
    "zaślepka do profili": "záslepka profilu",
    "zaślepka do profilu": "záslepka profilu",
    "oklinowanie i uszczelki na szkło": "klinování a těsnění na sklo",
    "zestaw montażowy oklinowanie": "montážní sada klinování",
    "balkon francuski": "francouzský balkon",
    "balkony francuskie": "francouzské balkony",
    "montowany do ramy okiennej": "montovaný na okenní rám",
    "do ramy okiennej": "na okenní rám",
    "montowany na listwach": "montovaný na lištách",
    "na listwach": "na lištách",
    "ze szkła czarnego": "z černého skla",
    "ze ściankami stałymi": "s pevnými příčkami",
    "ścianek stałych": "pevných příček",
    "ścianka stała": "pevná příčka",
    "ścianka szklana": "skleněná příčka",
    "drzwi + ścianka stała": "dveře + pevná příčka",
    "ścianka + drzwi": "příčka + dveře",
    "z drzwiami zamykanymi na zamek": "s dveřmi se zámkem",
    "mocowane góra dół": "kotvené nahoře a dole",
    "mocowane do ściany": "kotvené ke stěně",
    "bez ościeżnicy": "bez zárubně",
    "bez ościeżnic": "bez zárubní",
    "z ościeżnicą aluminiową": "s hliníkovou zárubní",
    "z futryną regulowaną": "s nastavitelnou zárubní",
    "z futryną stałą": "s pevnou zárubní",
    "czarna futryna": "černá zárubeň",
    "na samozamykaczu": "na samozavírači",
    "do samodzielnego montażu": "pro DIY montáž",
    "z czarnymi okuciami": "s černým kováním",
    "czarne okucia": "černé kování",
    "chromowane okucia": "chromované kování",
    "złote okucia": "zlaté kování",
    "okucia chrom": "chromové kování",
    "okucia ral": "kování RAL",
    "na naciągach": "na táhlech",
    "na prętach": "na táhlech",
    "na wspornikach na podporach": "na konzolách a podpěrách",
    "na wspornikach podporach": "na konzolách a podpěrách",
    "na wspornikach": "na konzolách",
    "na podporach": "na podpěrách",
    "na spigotach czarnych": "na černých spigotech",
    "na spigotach": "na spigotech",
    "na rotulach": "na rotulích",
    "na profilu": "na profilu",
    "kup próbkę": "koupit vzorek",
    "szybka wysyłka": "rychlá expedice",
    "od ręki": "skladem",
    "od reki": "skladem",
    "na wymiar": "na míru",
    "z montażem": "s montáží",
    "z rynną": "s okapem",
    "z okapnikiem": "s okapničkou",
    "system czarny": "černý systém",
    "czarny system": "černý systém",
    "system biały": "bílý systém",
    "biały system": "bílý systém",
    "bialy system": "bílý systém",
    "laminowane czarne": "laminované černé",
    "laminowane białe": "laminované bílé",
    "laminowane czarne szkło": "laminované černé sklo",
    "czarne szkło": "černé sklo",
    "przesuwne przesuwane": "posuvné",
    "szkło lodowe": "ledové sklo",
    "szkło bezbarwne": "čiré sklo",
    "szkło matowe": "matné sklo",
    "szkło przezroczyste": "čiré sklo",
    "wzór matowy": "matný vzor",
    "wzór piaskowany": "pískovaný vzor",
    "wzór paski": "vzor proužky",
    "wzór pasy": "vzor pruhy",
    "wzór kwadraty": "vzor čtverce",
    "z wzorem": "se vzorem",
    "do biura": "do kanceláře",
    "do sufitu": "ke stropu",
    "w ramie": "v rámu",
    "sauna przeszklenie sauny": "zasklení sauny",
    "przeszklenie sauny": "zasklení sauny",
    # --- adjectives / colours ---
    "przezroczysty": "čirý",
    "przezroczyste": "čiré",
    "przezroczysta": "čirá",
    "bezbarwne": "čiré",
    "bezbarwny": "čirý",
    "bezbarwna": "čirá",
    "matowy": "matný",
    "matowe": "matné",
    "matowa": "matná",
    "trawione": "leptané",
    "grafitowy": "grafitový",
    "grafitowe": "grafitové",
    "grafitowa": "grafitová",
    "brązowy": "hnědý",
    "brązowe": "hnědé",
    "brązowa": "hnědá",
    "białe": "bílé",
    "biała": "bílá",
    "białej": "bílé",
    "biały": "bílý",
    "bialy": "bílý",
    "czarne": "černé",
    "czarna": "černá",
    "czarny": "černý",
    "czarnego": "černého",
    "czarnych": "černých",
    "kolorowe": "barevné",
    "kolorowy": "barevný",
    "złote": "zlaté",
    "chromowane": "chromované",
    "laminowane": "laminované",
    "laminowana": "laminovaná",
    "klejone": "lepené",
    "szlifowane": "broušené",
    "piaskowany": "pískovaný",
    "fenickie": "fénické",
    # --- door / glass nouns & verbs ---
    "przesuwne": "posuvné",
    "przesuwane": "posuvné",
    "wahadłowe": "kyvné",
    "wahadlowe": "kyvné",
    "otwierane": "otevírané",
    "zamykanymi": "zavíranými",
    "szklane": "skleněné",
    "szklany": "skleněný",
    "szklana": "skleněná",
    "szklanej": "skleněné",
    "drzwi": "dveře",
    "drzwiami": "dveřmi",
    "wzór": "vzor",
    "wzor": "vzor",
    "wzory": "vzory",
    "grafika": "grafika",
    "nadruk": "potisk",
    "kabina": "sprchový kout",
    "kabiny": "sprchové kouty",
    "prysznicowa": "sprchová",
    "daszek": "stříška",
    "daszki": "stříšky",
    "zadaszenie": "stříška",
    "balustrada": "zábradlí",
    "balustrady": "zábradlí",
    "balkon": "balkon",
    "balkony": "balkony",
    "francuskie": "francouzské",
    "francuski": "francouzský",
    "lustro": "zrcadlo",
    "lustra": "zrcadla",
    "profil": "profil",
    "profile": "profily",
    "profilu": "profilu",
    "profili": "profilů",
    "szkło": "sklo",
    "szkla": "skla",
    "rynna": "okap",
    "rynną": "okapem",
    "okapnikiem": "okapničkou",
    "okucia": "kování",
    "okuciami": "kováním",
    "wspornikach": "konzolách",
    "wsporniki": "konzoly",
    "naciągach": "táhlech",
    "prętach": "táhlech",
    "podporach": "podpěrách",
    "systemowe": "systémové",
    "systemowy": "systémový",
    "system": "systém",
    "montaż": "montáž",
    "montażem": "montáží",
    "montażu": "montáži",
    "montażowy": "montážní",
    "montowany": "montovaný",
    "mocowane": "kotvené",
    "samodzielnego": "samostatné",
    "dostępne": "dostupné",
    "dostepne": "dostupné",
    "przechyleniem": "nakloněním",
    "szyby": "skla",
    "szyba": "sklo",
    "rurowe": "trubkové",
    "podwójne": "dvojité",
    "naścienne": "nástěnné",
    "loftowe": "loftové",
    "typowe": "typové",
    "futryną": "zárubní",
    "futryna": "zárubeň",
    "ościeżnicą": "zárubní",
    "ościeżnicy": "zárubně",
    "ościeżnic": "zárubní",
    "aluminiową": "hliníkovou",
    "regulowaną": "nastavitelnou",
    "stałą": "pevnou",
    "stała": "pevná",
    "stałych": "pevných",
    "stałymi": "pevnými",
    "ścianka": "příčka",
    "ścianki": "příčky",
    "ścianek": "příček",
    "ściankami": "příčkami",
    "ściany": "stěny",
    "ścianę": "stěnu",
    "ścianie": "stěně",
    "ścianę": "stěnu",
    "kasecie": "pouzdře",
    "kaseta": "pouzdro",
    "chowane": "zasouvané",
    "zamkiem": "zámkem",
    "zamek": "zámek",
    "oklinowanie": "klinování",
    "uszczelki": "těsnění",
    "boczny": "boční",
    "bocznego": "bočního",
    "zaślepka": "záslepka",
    "szprosy": "příčky",
    "góra": "nahoře",
    "dół": "dole",
    "samozamykacz": "samozavírač",
    "samozamykaczu": "samozavírači",
    "zabudowa": "zabudování",
    "ramie": "rámu",
    "rama": "rám",
    "ramka": "rámeček",
    "ramy": "rámu",
    "okiennej": "okenního",
    "łączony": "spojený",
    "narożny": "rohový",
    "prawa": "pravá",
    "lewa": "levá",
    "nakładka": "nástavec",
    "paski": "proužky",
    "pasy": "pruhy",
    "kwadraty": "čtverce",
    "wysyłka": "expedice",
    "wysylka": "expedice",
    "szybka": "rychlá",
    "sztuka": "ks",
    "szt": "ks",
    "do profilu": "na profil",
    "do balustrady": "na zábradlí",
    "nad drzwi": "nad dveře",
    "w kasecie": "do pouzdra",
    "pouzdro bez zárubně": "bez zárubně",
}

# Category → short Czech description stub template key
DOOR_CATEGORIES = {
    "Posuvné dveře Design-Lux",
    "Posuvné dveře Ultra Slim",
    "Posuvné dveře Loft",
    "Posuvné dveře – trubkový systém",
    "Posuvné dveře do pouzdra",
    "Kyvné dveře",
    "Otevírané dveře",
    "Dveře s pevnou zárubní",
    "Dveře s nastavitelnou zárubní",
    "Dveře s hliníkovou zárubní",
    "Linie Luxe",
    "Rock Glass – industriální linie",
    "Skleněné příčky a zabudování",
    "Skladem – rychlá expedice",
    "Posuvné dveře na míru",
    "Laminované dveře",
}
PATTERN_CATEGORIES = {"Barevné vzory skla", "Matné vzory skla"}
SHOWER_CATEGORIES = {"Skleněné sprchové kouty"}
BALCONY_CATEGORIES = {"Francouzské balkony"}
RAILING_CATEGORIES = {
    "Zábradlí DIY",
    "Zábradlí s montáží",
    "Profily na zábradlí",
    "Profily FIX na zábradlí",
}
CANOPY_CATEGORIES = {
    "Stříšky skladem",
    "Stříšky systémové",
    "Stříšky s okapem",
    "Stříšky s černým kováním",
    "Stříšky na táhlech",
    "Stříšky na konzolách",
}
MIRROR_CATEGORIES = {"Zrcadla"}
GLASS_CATEGORIES = {"Sklo"}

CSV_COLUMNS = [
    "Název",
    "Kategorie",
    "Cena (CZK)",
    "Cena původní (PLN)",
    "URL obrázku",
    "URL produktu (zdroj)",
    "Popis krátký",
]

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/128.0.0.0 Safari/537.36"
    ),
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Language": "cs,en;q=0.9,pl;q=0.8",
    "Connection": "keep-alive",
}

SCRIPT_DIR = Path(__file__).resolve().parent
DEFAULT_OUT_DIR = SCRIPT_DIR / "output"

log = logging.getLogger("qubaglass")


def polite_sleep() -> None:
    time.sleep(random.uniform(DELAY_MIN, DELAY_MAX))


def absolute_url(href: str | None) -> str:
    if not href:
        return ""
    return urljoin(BASE_URL + "/", href)


def parse_price_pln(text: str) -> float | None:
    """Parse Polish price strings: '2 900,00 zł', nbsp, spaces, commas."""
    if not text:
        return None
    raw = (
        text.replace("\xa0", " ")
        .replace("\u202f", " ")
        .replace("zł", "")
        .replace("PLN", "")
        .strip()
    )
    raw = re.sub(r"[^\d,.\s]", "", raw)
    raw = re.sub(r"\s+", "", raw)
    if not raw:
        return None
    if "," in raw and "." in raw:
        # 1.200,50 → 1200.50
        raw = raw.replace(".", "").replace(",", ".")
    elif "," in raw:
        raw = raw.replace(",", ".")
    try:
        return float(raw)
    except ValueError:
        return None


def price_czk(price_pln: float) -> int:
    return round(price_pln * PLN_TO_CZK * MARGIN)


# Polish-specific letters → ASCII fallback (after phrase replace). Keep Czech diacritics.
_PL_DIACRITICS = str.maketrans(
    {
        "ą": "a",
        "ć": "c",
        "ę": "e",
        "ł": "l",
        "ń": "n",
        # ó shared with Czech — keep
        "ś": "s",
        "ź": "z",
        "ż": "z",
        "Ą": "A",
        "Ć": "C",
        "Ę": "E",
        "Ł": "L",
        "Ń": "N",
        "Ś": "S",
        "Ź": "Z",
        "Ż": "Z",
    }
)

# Cleanup fragments left after partial phrase matches.
_CLEANUP_PATTERNS: list[tuple[re.Pattern[str], str]] = [
    (re.compile(r"\bse zrcadlem\s+dveře\s+zrcadlo\b", re.I), "se zrcadlem"),
    (re.compile(r"\bzrcadlo\s+dveře\b", re.I), "zrcadlo"),
    (re.compile(r"\bdveře\s+zrcadlo\b", re.I), "se zrcadlem"),
    (re.compile(r"\bstříška\s+stříška\b", re.I), "stříška"),
    (re.compile(r"\bskleněná\s+stříška\s+skleněné\b", re.I), "skleněná stříška"),
    (re.compile(r"\bskleněné\s+posuvné\s+dveře\s+posuvné\b", re.I), "skleněné posuvné dveře"),
    (re.compile(r"\bloft\s+loftové\b", re.I), "loftové"),
    (re.compile(r"\bdo pouzdra\s+bez zárubně\s+bez zárubně\b", re.I), "do pouzdra bez zárubně"),
    (re.compile(r"\bdo pouzdra\s+pouzdro\b", re.I), "do pouzdra"),
    (re.compile(r"\bstříška\s+skleněný\b", re.I), "skleněná stříška"),
    (re.compile(r"\bzabudování\s+skleněná\b", re.I), "skleněné zabudování"),
    (re.compile(r"\s{2,}"), " "),
]


def translate_name(name: str) -> str:
    """Polish product name → Czech via WORD_MAP (longer keys first) + diacritic fallback."""
    result = name
    items = sorted(WORD_MAP.items(), key=lambda kv: len(kv[0]), reverse=True)
    for pl, cs in items:
        if not pl:
            continue
        # Word-boundary replace so e.g. "białe" does not eat "białej" → "bíléj".
        pattern = re.compile(rf"(?<!\w){re.escape(pl)}(?!\w)", re.IGNORECASE)
        result = pattern.sub(cs, result)

    result = result.translate(_PL_DIACRITICS)

    for pat, repl in _CLEANUP_PATTERNS:
        result = pat.sub(repl, result)

    result = re.sub(r"\s+", " ", result).strip(" ,;/-")
    result = re.sub(r"\s+,", ",", result)
    if not result:
        return name
    # Sentence-case first letter; keep rest as produced (brand codes stay uppercase).
    return result[0].upper() + result[1:]


def short_description(name_cs: str, category: str) -> str:
    # Avoid luxusní / exkluzivní / prémiový
    if category in PATTERN_CATEGORIES:
        return f"{name_cs}. Vzor skla pro skleněné dveře — výběr podle designu."
    if category in SHOWER_CATEGORIES:
        return f"{name_cs}. Skleněný sprchový kout — cena orientační dle rozměrů."
    if category in BALCONY_CATEGORIES:
        return f"{name_cs}. Francouzský balkon ze skla — na míru dle zaměření."
    if category in RAILING_CATEGORIES:
        return f"{name_cs}. Skleněné zábradlí / profily — na objednávku."
    if category in CANOPY_CATEGORIES:
        return f"{name_cs}. Skleněná stříška — atypická doprava, na objednávku."
    if category in MIRROR_CATEGORIES:
        return f"{name_cs}. Zrcadlo — na objednávku dle rozměrů."
    if category in GLASS_CATEGORIES:
        return f"{name_cs}. Sklo — na objednávku dle rozměrů a typu."
    # Doors / partitions default
    base = name_cs if "dveře" in name_cs.lower() or "příčk" in name_cs.lower() else f"Skleněné dveře {name_cs}"
    return f"{base}. Cena zahrnuje systém a kování."


def fetch(session: requests.Session, url: str) -> BeautifulSoup | None:
    try:
        resp = session.get(url, headers=HEADERS, timeout=REQUEST_TIMEOUT)
        resp.raise_for_status()
        return BeautifulSoup(resp.text, "html.parser")
    except requests.RequestException as exc:
        log.warning("Request failed %s: %s", url, exc)
        return None


def detect_max_page(soup: BeautifulSoup) -> int:
    """Shoper pagination: <pagination-page-number> + 'z N' text / page links."""
    max_page = 1
    tag = soup.select_one("pagination-page-number")
    if tag is not None:
        # sibling text "z 3" lives in parent
        parent = tag.parent
        if parent:
            m = re.search(r"\bz\s+(\d+)\b", parent.get_text(" ", strip=True), re.I)
            if m:
                max_page = max(max_page, int(m.group(1)))
    for a in soup.select(".pagination a[href]"):
        href = a.get("href") or ""
        m = re.search(r"/(\d+)/?(?:\?|$)", href.rstrip("/"))
        # last numeric path segment that looks like page number
        parts = [p for p in href.strip("/").split("/") if p.isdigit()]
        if parts:
            # category id is also numeric; page is typically the last segment
            # e.g. /pl/c/Name/50/2 → last=2; /pl/c/Name/50 → last=50 (category)
            # Prefer links that end with /N after category id path with length>…
            m2 = re.search(r"/c/[^/]+/(\d+)/(\d+)/?$", href)
            if m2:
                max_page = max(max_page, int(m2.group(2)))
            m3 = re.search(r"/c/[^/]+/\d+/1/default/(\d+)/?$", href)
            if m3:
                max_page = max(max_page, int(m3.group(1)))
    return max(1, max_page)


def category_page_url(cat_path: str, page: int) -> str:
    path = cat_path.rstrip("/")
    if page <= 1:
        return absolute_url(path)
    return absolute_url(f"{path}/{page}")


def parse_product_tile(tile, category: str) -> dict[str, Any] | None:
    plink = tile.select_one("product-link")
    name_pl = ""
    price_pln: float | None = None

    if plink is not None:
        name_pl = (plink.get("name") or "").strip()
        raw_price = plink.get("price")
        if raw_price is not None and str(raw_price).strip() != "":
            try:
                price_pln = float(str(raw_price).replace(",", "."))
            except ValueError:
                price_pln = None

    if not name_pl:
        name_el = tile.select_one(".product-tile__name a, .product-tile__name, h2 a, h3 a")
        if name_el:
            name_pl = name_el.get_text(" ", strip=True)

    if price_pln is None:
        price_el = tile.select_one(".price__value, .js__price-value, .product-tile__price .price")
        if price_el:
            price_pln = parse_price_pln(price_el.get_text(" ", strip=True))

    link_el = tile.select_one("a[href*='/pl/p/']")
    product_url = absolute_url(link_el.get("href") if link_el else None)

    img_el = tile.select_one(
        ".product-tile__image_primary img, picture.product-tile__image_primary img, "
        ".product-tile__image img, img"
    )
    img_url = ""
    if img_el is not None:
        img_url = absolute_url(
            img_el.get("src") or img_el.get("data-src") or img_el.get("data-original")
        )

    if not name_pl or not product_url:
        return None
    if price_pln is None:
        log.warning("Missing price for %s", product_url)
        price_pln = 0.0

    name_cs = translate_name(name_pl)
    return {
        "name_pl": name_pl,
        "name_cs": name_cs,
        "category": category,
        "price_pln": price_pln,
        "price_czk": price_czk(price_pln),
        "url": product_url,
        "image": img_url,
        "description": short_description(name_cs, category),
    }


def get_products_from_page(soup: BeautifulSoup, category: str) -> list[dict[str, Any]]:
    # Scope to category product list — avoid menu / products-of-the-day tiles.
    tiles = soup.select(".product-list .product-tile")
    if not tiles:
        tiles = soup.select("main .tile-grid .product-tile")
    if not tiles:
        tiles = soup.select(".tile-grid .product-tile")

    products: list[dict[str, Any]] = []
    seen_on_page: set[str] = set()
    for tile in tiles:
        item = parse_product_tile(tile, category)
        if not item:
            continue
        if item["url"] in seen_on_page:
            continue
        seen_on_page.add(item["url"])
        products.append(item)
    return products


def scrape_category(
    session: requests.Session, cat_path: str, cat_name: str
) -> tuple[list[dict[str, Any]], bool]:
    """Return (products, ok). ok=False when the first page request failed."""
    all_products: list[dict[str, Any]] = []
    first_url = category_page_url(cat_path, 1)
    log.info("  Page 1: %s", first_url)
    soup = fetch(session, first_url)
    if soup is None:
        log.error("  FAILED to fetch category: %s (%s)", cat_name, first_url)
        return all_products, False

    max_page = detect_max_page(soup)
    page_products = get_products_from_page(soup, cat_name)
    all_products.extend(page_products)
    log.info("  Page 1: %d products (max page ~%d)", len(page_products), max_page)

    if not page_products and max_page <= 1:
        return all_products, True

    for page in range(2, max_page + 1):
        polite_sleep()
        url = category_page_url(cat_path, page)
        log.info("  Page %d: %s", page, url)
        soup = fetch(session, url)
        if soup is None:
            break
        page_products = get_products_from_page(soup, cat_name)
        log.info("  Page %d: %d products", page, len(page_products))
        if not page_products:
            break
        # Stop if Shoper returned the same set (bad page URL)
        existing = {p["url"] for p in all_products}
        new_items = [p for p in page_products if p["url"] not in existing]
        if not new_items:
            log.info("  No new products on page %d — stopping pagination", page)
            break
        all_products.extend(new_items)

    return all_products, True


def download_images(session: requests.Session, products: list[dict[str, Any]], img_dir: Path) -> int:
    img_dir.mkdir(parents=True, exist_ok=True)
    saved = 0
    for p in products:
        url = p.get("image") or ""
        if not url:
            continue
        path_part = urlparse(url).path
        name = Path(path_part).name or f"{urlparse(p['url']).path.rstrip('/').split('/')[-1]}.jpg"
        dest = img_dir / name
        if dest.exists():
            continue
        try:
            polite_sleep()
            resp = session.get(url, headers=HEADERS, timeout=REQUEST_TIMEOUT)
            resp.raise_for_status()
            dest.write_bytes(resp.content)
            saved += 1
            log.info("Downloaded image %s", dest.name)
        except requests.RequestException as exc:
            log.warning("Image download failed %s: %s", url, exc)
    return saved


def to_csv_row(p: dict[str, Any]) -> dict[str, Any]:
    return {
        "Název": p["name_cs"],
        "Kategorie": p["category"],
        "Cena (CZK)": p["price_czk"],
        "Cena původní (PLN)": p["price_pln"],
        "URL obrázku": p["image"],
        "URL produktu (zdroj)": p["url"],
        "Popis krátký": p["description"],
    }


def save_outputs(products: list[dict[str, Any]], out_dir: Path) -> tuple[Path, Path]:
    out_dir.mkdir(parents=True, exist_ok=True)
    csv_path = out_dir / "qubaglass_katalog.csv"
    json_path = out_dir / "qubaglass_katalog.json"

    with csv_path.open("w", newline="", encoding="utf-8-sig") as f:
        writer = csv.DictWriter(f, fieldnames=CSV_COLUMNS)
        writer.writeheader()
        for p in products:
            writer.writerow(to_csv_row(p))

    payload = {
        "pricing": {
            "formula": "price_czk = round(price_pln * 5.8 * 1.45)",
            "pln_to_czk": PLN_TO_CZK,
            "margin": MARGIN,
        },
        "count": len(products),
        "products": [
            {
                "name_cs": p["name_cs"],
                "name_pl": p["name_pl"],
                "category": p["category"],
                "price_czk": p["price_czk"],
                "price_pln": p["price_pln"],
                "image_url": p["image"],
                "product_url": p["url"],
                "description_short": p["description"],
            }
            for p in products
        ],
    }
    json_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    return csv_path, json_path


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Scrape Quba Glass catalog to Czech CSV/JSON")
    parser.add_argument(
        "--out-dir",
        type=Path,
        default=DEFAULT_OUT_DIR,
        help="Output directory (default: scripts/output)",
    )
    parser.add_argument(
        "--download-images",
        action="store_true",
        help="Also download images to scripts/output/images/ (OFF by default)",
    )
    parser.add_argument(
        "--limit-categories",
        type=int,
        default=0,
        help="Only scrape first N categories (0 = all)",
    )
    parser.add_argument(
        "-v",
        "--verbose",
        action="store_true",
        help="Debug logging",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    logging.basicConfig(
        level=logging.DEBUG if args.verbose else logging.INFO,
        format="%(asctime)s %(levelname)s %(message)s",
        datefmt="%H:%M:%S",
    )

    cats = CATEGORIES
    if args.limit_categories and args.limit_categories > 0:
        cats = CATEGORIES[: args.limit_categories]

    session = requests.Session()
    all_products: list[dict[str, Any]] = []
    seen_urls: set[str] = set()
    per_category: Counter[str] = Counter()
    failed_categories: list[str] = []
    duplicates = 0

    for i, (cat_path, cat_name) in enumerate(cats, start=1):
        log.info("[%d/%d] Category: %s", i, len(cats), cat_name)
        products, ok = scrape_category(session, cat_path, cat_name)
        if not ok:
            failed_categories.append(cat_name)
        kept = 0
        for p in products:
            if p["url"] in seen_urls:
                duplicates += 1
                continue
            seen_urls.add(p["url"])
            all_products.append(p)
            kept += 1
        per_category[cat_name] = kept
        log.info("  Kept %d unique (raw %d)", kept, len(products))
        if i < len(cats):
            polite_sleep()

    csv_path, json_path = save_outputs(all_products, args.out_dir)
    log.info("Wrote %d products → %s", len(all_products), csv_path)
    log.info("Wrote JSON → %s", json_path)
    log.info("--- Summary per category (unique first-seen) ---")
    for name, count in per_category.items():
        log.info("  %s: %d", name, count)
    log.info("Total unique: %d | skipped duplicates: %d", len(all_products), duplicates)
    if failed_categories:
        log.warning("Failed categories: %s", ", ".join(failed_categories))

    if args.download_images:
        img_dir = args.out_dir / "images"
        n = download_images(session, all_products, img_dir)
        log.info("Downloaded %d images to %s", n, img_dir)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
