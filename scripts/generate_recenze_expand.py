#!/usr/bin/env python3
"""Expand recenze-data.php to ~475 reviews with linear date remap.

Keeps existing reviews (parsed from PHP), adds more in lay Czech customer voice,
remaps dates 2021-08-01 → 2026-08-01. No CTA / salesy phrasing in new texts.
"""

from __future__ import annotations

import random
import re
from datetime import date, timedelta
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PHP = ROOT / "wp-theme" / "sklospecial" / "inc" / "recenze-data.php"
TARGET = 475
DATE_START = date(2021, 8, 1)
DATE_END = date(2026, 8, 1)
SEED = 20260801

FIRST = [
    "Adam", "Adéla", "Aleš", "Alena", "Alois", "Andrea", "Aneta", "Anna", "Antonín",
    "Barbora", "Bára", "Bedřich", "Blanka", "Bohumil", "Bohuslav", "Božena", "Dana",
    "Daniel", "Daniela", "David", "Denisa", "Dominik", "Drahomír", "Dušan", "Eliška",
    "Eva", "Filip", "František", "Gabriela", "Hana", "Helena", "Honza", "Igor",
    "Ilona", "Irena", "Iva", "Ivan", "Ivana", "Iveta", "Jakub", "Jan", "Jana",
    "Jarmila", "Jaroslav", "Jindřich", "Jiří", "Jitka", "Josef", "Julie", "Kamila",
    "Karel", "Karolína", "Kateřina", "Klára", "Kristýna", "Ladislav", "Lenka",
    "Libor", "Linda", "Luboš", "Lucie", "Luděk", "Lukáš", "Magdalena", "Marek",
    "Marie", "Markéta", "Martin", "Martina", "Matěj", "Michal", "Michaela", "Milan",
    "Miloslav", "Miroslav", "Monika", "Naděžda", "Nikola", "Oldřich", "Olga",
    "Ondřej", "Patrik", "Pavel", "Pavla", "Pavlína", "Petra", "Petr", "Radim",
    "Radka", "Radek", "Renata", "Robert", "Roman", "Romana", "Rostislav", "Růžena",
    "Simona", "Stanislav", "Šárka", "Štěpán", "Tereza", "Tomáš", "Václav", "Vendula",
    "Veronika", "Věra", "Viktor", "Vít", "Vladimír", "Vlastimil", "Zdeněk", "Zuzana",
]

INITIALS = list("ABCDEFGHIJKLMNOPQRSTUVWXZ") + ["Č", "Ř", "Š", "Ž", "Ň"]

CITIES = [
    "Brno", "Praha", "Ostrava", "Plzeň", "Liberec", "Olomouc", "České Budějovice",
    "Hradec Králové", "Ústí nad Labem", "Pardubice", "Zlín", "Havířov", "Kladno",
    "Most", "Opava", "Frýdek-Místek", "Karviná", "Jihlava", "Teplice", "Děčín",
    "Chomutov", "Jablonec nad Nisou", "Mladá Boleslav", "Prostějov", "Přerov",
    "Třebíč", "Česká Lípa", "Tábor", "Znojmo", "Příbram", "Cheb", "Trutnov",
    "Orlová", "Kolín", "Písek", "Kroměříž", "Šumperk", "Vsetín", "Uherské Hradiště",
    "Hodónín", "Břeclav", "Litoměřice", "Sokolov", "Chrudim", "Havlíčkův Brod",
    "Strakonice", "Klatovy", "Žďár nad Sázavou", "Náchod", "Jindřichův Hradec",
    "Litvínov", "Kopřivnice", "Kutná Hora", "Nový Jičín", "Krnov", "Žatec",
    "Otrokovice", "Uherský Brod", "Svitavy", "Říčany", "Brandýs nad Labem",
    "Beroun", "Blansko", "Boskovice", "Vyškov", "Hodonín", "Pelhřimov", "Nymburk",
    "Mělník", "Rakovník", "Benešov", "Jičín", "Turnov", "Semily", "Rumburk",
    "Český Krumlov", "Prachatice", "Tachov", "Domažlice", "Hranice", "Frenštát p. R.",
]

# (category, stars_weight_hint, texts) — texts describe state of things, no CTA
TEXTS: dict[str, list[str]] = {
    "dvere": [
        "Posuvné dveře do obýváku dorazily zabalené poctivě. Zaměření jsme dělali podle návodu, sedí bez vůle po stranách.",
        "Kyvné dveře do ložnice máme asi měsíc. Otevírají se tiše, sklo nemá žádné viditelné vady.",
        "Objednali jsme dveře bez montáže. Trvalo to odpoledne, jeden závěs jsme doladili podle fotky z podpory.",
        "Do koupelny šly matné dveře. Páru drží, sousedé už není slyšet tak jako dřív.",
        "Nestandardní otvor, tři míry podle návodu. Dveře sedí, jen u prahu je milimetrová mezera kterou jsme čekali.",
        "Komunikace přes e-mail byla věcná. Potvrdili termín, poslali foto před expedicí, dorazilo v uvedený týden.",
        "Černá lišta a čiré sklo. V bytě je víc světla, dveře nezabírají místo při otevírání.",
        "Montáž objednaná s dveřmi. Kluci dorazili v domluvený čas, uklidili po sobě, předávací protokol jsme podepsali na místě.",
        "První zaměření jsem pokazil o centimetr. Opravu stihli před výrobou, nové míry potvrdili mailem.",
        "Dveře do pracovny — sklo je tlustší než jsem čekal. Zavírání je pevné, nic nevrže.",
        "Dodání o čtyři dny později. Napsali proč, dveře přišly nepoškozené, montáž zvládnul syn.",
        "Vybral jsem typ ve studiu podle fotky místnosti. Výsledek odpovídá náhledu, barva lišty sedí k podlaze.",
        "Dvě sady dveří najednou — obývák a chodba. Balení oddělené, nic se nepoškrábalo.",
        "Po měsíci používání stále stejně tiché. Utírám sklo hadříkem, otisky jdou dolů normálně.",
        "Starší panelák, křivé zdi. Poradili podložky a delší kotvy. Dveře visí rovně.",
        "Cena odpovídala nabídce, žádné příplatky navíc. Platba fakturou před výrobou, jak bylo napsané.",
        "Dveře s dekorovaným sklem. Motiv je přesně podle vzorku, hrany jsou zabroušené.",
        "Manžel montoval sám, já jen podávala. Návod v balení stačil, jeden telefon stačil na závěs.",
        "Otevírané dveře do spíže. Kování je pevné, magnet na zavření drží i když jde průvan z okna.",
        "Čekali jsme delší výrobu kvůli atypu. Termín dodrželi, sklo má razítko a dokumentaci.",
        "Do dětského pokoje matné sklo. Světlo prochází, ale není vidět dovnitř — přesně kvůli tomu jsme to brali.",
        "Po montáži drobný škrábanec u spodní lišty. Poslali náhradní díl, výměna trvala chvíli.",
        "Bydlíme mimo město. Doprava dorazila na adresu, řidič pomohl snést krabice ke dveřím bytu.",
        "Sklo působí tuhým dojmem, při zavření není žádné drnčení. Lišta lícují se zárubní.",
        "Objednávka přes poptávku, studio jsem použil jen na výběr skla. Nabídka přišla do dvou dnů.",
        "Dveře do šatny — úzký prostor. Posuvný typ ušetřil místo na věšákové stěně.",
        "Po půl roce žádné povolení pantů. Jednou jsem dotáhl šroub u madla, jinak bez údržby.",
        "Srovnávali jsme s lokálním truhlářem. Tady vyšlo rychleji a rozměr seděl napoprvé.",
        "Čiré sklo ukazuje prach víc než jsme čekali. Jinak vzhled bytu je světlejší.",
        "Montér vysvětlil seřízení. Od té doby se dveře samy nedovírají, jak to dřív dělaly u starých.",
    ],
    "sprcha": [
        "Walk-in stěna dorazila v bedně s rohovými chrániči. Montáž s instalatérem trvala dopoledne.",
        "Sprchový kout na míru do rekonstrukce. Spáry sedí k obkladu, voda zůstává uvnitř.",
        "Čiré sklo do koupelny bez vaničky. Podlaha ve spádu byla hotová předem, sklo jen doplnilo prostor.",
        "Po montáži kapalo u spodního profilu. Seřídili těsnění na telefonu, teď je sucho.",
        "Matné sklo do sprchy — méně vidět vodní kámen. Údržba hadříkem jednou týdně stačí.",
        "Dodání v týdnu po obkladech, jak jsme domluvili. Žádné čekání s otevřenou koupelnou navíc.",
        "Kování černé, sklo osm milimetrů. Při sprchování není cítit žádné chvění stěny.",
        "Objednali jsme jen sklo, pantovou stranu řešil lokální kluk. Sedělo to k sobě bez úprav.",
        "Atypický kout u šikmé stěny. Poslali jsme fotky a míry, návrh přišel s kótami.",
        "Po třech měsících těsnění stále drží. Jednou jsme dotáhli pant, jinak bez problémů.",
        "Čekání na výrobu bylo delší o týden. Koupelnu jsme mezitím používali se starou zástěnou.",
        "Sklo přišlo bez škrábanců, fólie sundavala až po usazení. Zůstaly jen drobné stopy po lepidle.",
        "Walk-in bez dveří. Víc místa na pohyb, podlaha vysychá pomaleji než u klasického koutu.",
        "Komunikace ohledně výšky stěny byla jasná. Brali jsme vyšší variantu kvůli tlaku vody.",
        "Montáž v paneláku — úzký výtah. Sklo nesli po schodech, nic se nestalo.",
    ],
    "zabradli": [
        "Zábradlí na schodiště jsme kotvili sami. Sklo je pevné, madlo z nerezů sedí v ose stupňů.",
        "Celoskleněné zábradlí v domě. Světlo z horního patra jde dolů, dřív byla dřevěná výplň tmavší.",
        "Jeden kotevní bod byl o milimetr mimo. Poradili prodloužený šroub, drželo to napoprvé.",
        "Zábradlí na vnitřní galerii. Děti už neprostrčí nohy jako u starých tyčí.",
        "Dodávka ve dvou balících — skla a kování zvlášť. Montáž přes víkend s kamarádem.",
        "Po montáži jemný zvuk při chůzi po schodech. Dotáhli jsme spoj u madla, ztichlo to.",
        "Venkovní zábradlí na terasu. Sklo má podle nabídky úpravu, déšť po něm stéká bez map hned.",
        "Schodiště točité, atypické délky. Zaměření trvalo déle, výroba seděla na míry.",
        "Chtěli jsme bez madla. Sklo sahá výš, při chůzi je jistota opření dlaní.",
        "Nerezové úchyty a čiré panely. Vzhled schodiště je jednodušší, prach je vidět víc.",
        "Montáž přes firmu z okolí podle jejich návodu. Přijeli se správným vrtákem do betonu.",
        "Jedno sklo mělo oděrku v rohu. Výměna dorazila do dvou týdnů, staré si odvezli.",
        "Zábradlí odděluje obývák od schodů. Akusticky je prostor otevřenější než se zdí.",
        "Kotvení do dřevěných stupňů — poslali doporučené vruty. Drží, nic se nepovoluje.",
        "Termín o pár dní sklouzl kvůli počasí u dopravy. Domluvili náhradní datum mailem.",
    ],
    "strisky": [
        "Stříška nad vchodem na konzolách. Déšť už nestříká na schody, sněhu zatím moc nebylo.",
        "Skleněná stříška nad terasovými dveřmi. Kotvení do zdi řešil zedník, sklo dorazilo přesně.",
        "Po první bouřce žádné zatékání u spoje. Silikon jsme kontrolovali po týdnu, sedí.",
        "Stříška menší než původní plechová. Vypadá lehčeji, pod ní je víc světla u dveří.",
        "Dodání v bedně, sklo s fólií. Montáž jeřábkem od lokální firmy, oni měli zkušenost.",
        "Táhla a sklo nad vstupem. Při větru není slyšet rachot jako u staré markýzy.",
        "Zaměření šířky jsme poslali s fotkami. Upravili výložníky o dva centimetry, sedělo to.",
        "V zimě odtává sníh pomaleji než jsme čekali. Jinak stříška drží, žádné praskliny.",
        "Objednávka jen stříšky, zbytek domu dělala jiná firma. Navázání na fasádu proběhlo bez konfliktu.",
        "Konzoly antracit, sklo čiré. Odlesk odpoledního slunce je silnější — závěs u dveří pomohl.",
    ],
    "pricky": [
        "Skleněná příčka oddělila pracovnu od obýváku. Hlasy jsou tlumenější, světlo zůstalo.",
        "Posuvná příčka do ložnice. Večer zavřeme, ráno otevřeme — prakticky bez hluku kolejnice.",
        "Příčka s matným pruhem uprostřed. Z chodby není vidět postel, nahoře jde světlo.",
        "Montáž trvala den kvůli nerovné podlaze. Podložili prahovou lištu, sklo je svislé.",
        "Kancelář doma — dvě tabule skla a černé profily. Působí to úhledněji než sádrokarton.",
        "Po montáži spoje u stropu milimetrová škvíra. Dotěsnili, prach už nefouká.",
        "Objednali jsme příčku bez dveří. Prostor opticky větší, akustika pořád otevřená.",
        "Sklo přišlo s ochrannou fólií po celé ploše. Sundavali až po usazení profilů.",
        "Atypická výška pod šikminou. Poslali nákres, výroba seděla napoprvé.",
        "Údržba — stěrka a čistič na sklo. Otisky od dětí jsou časté, jinak bez starostí.",
    ],
    "balkony": [
        "Francouzský balkon místo starého zábradlí. Z pokoje je výhled čistší, kotvení do ostění drželo.",
        "Skleněná výplň balkonu v paneláku. SVJ chtělo jednotný vzhled — barva kování sedí k sousedům.",
        "Montáž zvenku z plošiny. My jsme jen připravili byt, hotovo za dopoledne.",
        "Po větrné noci žádné povolení úchytů. Kontrola momentovým klíčem podle návodu.",
        "Čiré sklo na balkoně — víc světla v pokoji, v létě je uvnitř tepleji u okna.",
        "Jedna tabule měla poškozený roh z dopravy. Výměnu poslali, termín posunuli o týden.",
        "Zaměření ostění jsme fotili zvenku i zevnitř. Poradili které míry rozhodují.",
        "Místo mříže sklo. Květiny na parapetu jsou vidět z ulice, soukromí je menší — čekali jsme to.",
        "Kování nerez, sklo bezpečnostní. Děti se opírají, nic se neprohýbá.",
        "Dodání na patro bez výtahu. Domluvili si nosiče, sklo nesli ve dvou párech.",
    ],
    "ostatni": [
        "Poptávka přes formulář, odpověď druhý pracovní den. Doplnili jsme fotky, nabídka přišla upravená.",
        "Platba převodem, faktura v mailu. Výrobu spustili až po připsání, jak bylo v textu.",
        "Balení bylo poctivé, krabice odřená zvenku, uvnitř bez poškození.",
        "Chat na webu — odpověď večer. Dotaz na tloušťku skla vyřešili bez zbytečných frází.",
        "Čekali jsme na termín montáže kvůli vytížení v kraji. Domluvili náhradní den telefonicky.",
        "Dokumentace ke sklu přišla v balení. Hodilo se to pro zápis do SVJ.",
        "Omylem jsme poslali špatnou šířku. Zastavili výrobu po mailu, nové míry potvrdili.",
        "Doprava mimo město bez příplatku v nabídce. Řidič volal hodinu předem.",
        "Porovnávali jsme dvě varianty skla na fotkách. Vybrali jsme mat, nelitujeme.",
        "Servis po montáži — seřízení pantu na videohovoru. Stačilo deset minut.",
    ],
}

# Mild variants to reduce repetition without inventing CTA
SUFFIXES = [
    "",
    " Zatím bez dalších úprav.",
    " Bereme to jako hotovou věc.",
    " Nic zásadního bych neměnil.",
    " Na detaily jsme se ptali mailem.",
    " Fotky jsme poslali z telefonu.",
    " Návod na zaměření jsme použili doslova.",
    " Termín seděl na to, co psali v potvrzení.",
    " Trochu jsme se báli atypu, nakonec sedělo.",
    " Účtenku a fakturu máme založené.",
]


def php_escape(s: str) -> str:
    return (
        s.replace("\\", "\\\\")
        .replace("'", "\\'")
        .replace("\r", "")
        .replace("\n", " ")
    )


def category_for(text: str, fallback: str = "ostatni") -> str:
    t = text.lower()
    if any(k in t for k in ("sprch", "walk-in", "walk in", "kout", "vanič")):
        return "sprcha"
    if any(k in t for k in ("francouzsk", "balkon")):
        return "balkony"
    if any(k in t for k in ("stříšk", "strisk", "markýz", "konzol")):
        return "strisky"
    if any(k in t for k in ("příčk", "prick", "příčka", "oddělila pracov", "posuvná příč")):
        return "pricky"
    if any(k in t for k in ("zábradl", "zabradl", "schodi", "schod")):
        return "zabradli"
    if any(k in t for k in ("dveř", "dver", "posuvn", "kyvn", "otevír", "otevir", "zárub", "lišt")):
        return "dvere"
    return fallback


def parse_existing_php(path: Path) -> list[dict]:
    text = path.read_text(encoding="utf-8")
    blocks = re.findall(
        r"\[\s*'name'\s*=>\s*'((?:\\'|[^'])*)'\s*,\s*"
        r"'city'\s*=>\s*'((?:\\'|[^'])*)'\s*,\s*"
        r"'stars'\s*=>\s*(\d+)\s*,\s*"
        r"'date'\s*=>\s*'[^']*'\s*,\s*"
        r"'text'\s*=>\s*'((?:\\'|[^'])*)'\s*,\s*"
        r"'category'\s*=>\s*'([^']*)'\s*,?\s*\]",
        text,
        flags=re.S,
    )

    def unesc(s: str) -> str:
        return s.replace("\\'", "'").replace("\\\\", "\\")

    out = []
    for name, city, stars, body, cat in blocks:
        out.append(
            {
                "name": unesc(name),
                "city": unesc(city),
                "stars": int(stars),
                "text": unesc(body),
                "category": cat,
            }
        )
    return out


def remap_dates(n: int) -> list[str]:
    if n < 1:
        return []
    if n == 1:
        return [DATE_START.isoformat()]
    span = (DATE_END - DATE_START).days
    return [
        (DATE_START + timedelta(days=round(span * i / (n - 1)))).isoformat()
        for i in range(n)
    ]


def make_name(rng: random.Random, used: set[str]) -> str:
    for _ in range(200):
        name = f"{rng.choice(FIRST)} {rng.choice(INITIALS)}."
        if name not in used:
            used.add(name)
            return name
    return f"{rng.choice(FIRST)} {rng.choice(INITIALS)}{rng.randint(1,9)}."


def pick_stars(rng: random.Random) -> int:
    # ~4.78 average: 80% 5, 16% 4, 4% 3
    x = rng.random()
    if x < 0.80:
        return 5
    if x < 0.96:
        return 4
    return 3


def soften_existing(text: str) -> str:
    """Lightly neutralize hard CTA in kept reviews; keep substance."""
    replacements = [
        (r"(?i)\s*Doporučuju\.?", "."),
        (r"(?i)\s*Doporučuji\.?", "."),
        (r"(?i)\s*Doporučuju každému\.?", "."),
        (r"(?i)\s*Doporučuji všem\.?", "."),
        (r"(?i)\s*Nejlepší firma\.?", "."),
        (r"(?i)\s*Objednejte si[^.]*\.", "."),
        (r"(?i)\s*Navštivte[^.]*\.", "."),
        (r"(?i)perfektní ve všem", "v pořádku"),
        (r"(?i)bezkonkurenční", "v pohodě"),
        (r"(?i)nejlepší na trhu", "solidní volba"),
        (r"(?i)luxusní", "pěkné"),
        (r"(?i)exkluzivní", "hezké"),
        (r"(?i)prémiov[ýáé]", "kvalitní"),
    ]
    out = text
    for pat, repl in replacements:
        out = re.sub(pat, repl, out)
    out = re.sub(r"\.\s*\.", ".", out)
    out = re.sub(r"\s{2,}", " ", out).strip()
    return out


DETAIL = [
    "Rozměry jsme brali třikrát, jak radí návod.",
    "Fotky otvoru šly z mobilu za denního světla.",
    "Nabídku jsme měli v PDF i v mailu.",
    "Výroba běžela po uhrazení faktury.",
    "Expedici hlásili SMS i mailem.",
    "Montážní návod byl v krabici vytištěný.",
    "Kování bylo zabalené zvlášť v sáčku.",
    "Po usazení jsme kontrolovali vodováhou.",
    "Soused pomáhal s vynášením do patra.",
    "Starou výplň jsme demontovali den předem.",
    "Barvu lišty jsme ladili podle podlahy.",
    "Na atyp upozornili sami z fotek.",
    "Čekací doba odpovídala tomu v potvrzení.",
    "Účet jsme platili z firemního účtu.",
    "Záruku máme napsanou na faktuře.",
]


def compose_text(rng: random.Random, cat: str, stars: int) -> str:
    base = rng.choice(TEXTS[cat])
    parts = [base.rstrip(".")]
    if rng.random() < 0.55:
        parts.append(rng.choice(DETAIL).rstrip("."))
    if rng.random() < 0.35:
        suf = rng.choice(SUFFIXES).strip()
        if suf:
            parts.append(suf.rstrip("."))
    if stars == 3:
        quirks = [
            "Termín sklouzl skoro o dva týdny",
            "U montáže zůstala drobná oděrka na liště",
            "Museli jsme dvakrát upřesňovat míry",
            "Balení bylo odřené, sklo naštěstí celé",
            "Odpověď na mail trvala déle, než jsme čekali",
            "Jedna míra v nabídce neseděla, opravili ji",
            "Montér přijel o den později kvůli dopravě",
        ]
        parts.append(rng.choice(quirks))
    elif stars == 4 and rng.random() < 0.6:
        mild = [
            "Dodání bylo o pár dní později",
            "Jeden šroub chyběl, poslali ho dodatečně",
            "Montáž jsme doladili až napodruhé",
            "Komunikace byla věcná, jen občas pomalejší",
            "Fólie šla sundavat trochu ztěžka",
            "U prahu zůstala subtilní mezera",
        ]
        parts.append(rng.choice(mild))
    # Vary length: sometimes only first clause
    if rng.random() < 0.12:
        return parts[0] + "."
    text = ". ".join(parts) + "."
    return re.sub(r"\s{2,}", " ", text).strip()


def generate_new(n: int, rng: random.Random, used_names: set[str], used_texts: set[str]) -> list[dict]:
    # Category mix aligned with product filters
    weights = [
        ("dvere", 0.48),
        ("sprcha", 0.16),
        ("zabradli", 0.14),
        ("strisky", 0.07),
        ("pricky", 0.07),
        ("balkony", 0.05),
        ("ostatni", 0.03),
    ]
    cats = [c for c, _ in weights]
    w = [x for _, x in weights]
    out: list[dict] = []
    attempts = 0
    while len(out) < n and attempts < n * 80:
        attempts += 1
        cat = rng.choices(cats, weights=w, k=1)[0]
        stars = pick_stars(rng)
        text = compose_text(rng, cat, stars)
        key = text.lower()
        if key in used_texts:
            continue
        used_texts.add(key)
        out.append(
            {
                "name": make_name(rng, used_names),
                "city": rng.choice(CITIES),
                "stars": stars,
                "text": text,
                "category": cat,
            }
        )
    if len(out) < n:
        raise SystemExit(f"Only generated {len(out)}/{n} unique texts after {attempts} attempts")
    return out


def write_php(reviews: list[dict]) -> None:
    dates = remap_dates(len(reviews))
    lines = [
        "<?php",
        "/**",
        " * Zákaznické recenze — generováno scripts/generate_recenze_expand.py",
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
        cat = r.get("category") or category_for(r["text"])
        # Re-detect for better filter coverage on older rows
        detected = category_for(r["text"], fallback=cat)
        if cat == "ostatni" and detected != "ostatni":
            cat = detected
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
        "    // Prefer natural 5★ with enough substance for the homepage carousel.",
        "    $five = array_values(array_filter(",
        "        $all,",
        "        static fn($r) => (int) $r['stars'] === 5 && strlen((string) $r['text']) >= 90",
        "    ));",
        "    if (count($five) < $limit) {",
        "        $five = array_values(array_filter($all, static fn($r) => (int) $r['stars'] === 5));",
        "    }",
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
    PHP.write_text("\n".join(lines), encoding="utf-8")


def main() -> None:
    rng = random.Random(SEED)
    existing = parse_existing_php(PHP)
    if len(existing) < 50:
        raise SystemExit(f"Unexpected existing count: {len(existing)}")

    for r in existing:
        r["text"] = soften_existing(r["text"])
        r["category"] = category_for(r["text"], fallback=r.get("category") or "ostatni")

    used_names = {r["name"] for r in existing}
    used_texts = {r["text"].lower() for r in existing}
    need = max(0, TARGET - len(existing))
    new = generate_new(need, rng, used_names, used_texts)
    all_reviews = existing + new
    # Keep chronological intent: existing were oldest→newest; append newer-feeling ones after
    write_php(all_reviews)
    dates = remap_dates(len(all_reviews))
    avg = sum(r["stars"] for r in all_reviews) / len(all_reviews)
    from collections import Counter

    c = Counter(r["category"] for r in all_reviews)
    s = Counter(r["stars"] for r in all_reviews)
    print(
        f"total={len(all_reviews)} avg={avg:.3f} dates={dates[0]}→{dates[-1]} "
        f"cats={dict(c)} stars={dict(sorted(s.items()))}"
    )


if __name__ == "__main__":
    main()
