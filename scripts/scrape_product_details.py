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
    # Residual inflections found by the final verification scan.
    'dotyczy': 'týká se',
    'osadzic': 'usadit',
    'moga': 'mohou',
    # Last isolated hybrid connectors.
    'sklada': 'skládá',
    'poddane': 'podrobené',
    'w momencie': 'v okamžiku',
    'podana cena dotyczy': 'uvedená cena platí pro',
    'swojej': 'svého',
    'odpowiednim luzem': 'odpovídající vůlí',
    'moga przekraczac': 'mohou překročit',
    'natomiast': 'zatímco',
    'z serii': 'z řady',
    'przeznaczony': 'určeno',
    'stabilnie osadzic': 'stabilně usadit',
    'ustabilizowac ja pod katem': 'stabilizovat ji v úhlu',
    'stopni': 'stupňů',
    'nam': 'nám',
    'w': 'v',
    # Final legacy ROCK GLASS, shower and canopy templates.
    'piekny styl, nowoczesny design - w przystepnej cenie': 'krásný moderní design za dostupnou cenu',
    'systém má piec wersji kolorystycznych': 'systém je dostupný v pěti barevných variantách',
    'barva rdza i korozja - prosím zapytania mailowe': 'barvy rez a koroze — cenu si vyžádejte e-mailem',
    'systém ten má blokady zabezpieczajace vozíky przed vypadnutím a stopery na obu krancach vodicí lišty': 'systém má pojistky vozíků proti vypadnutí a dorazy na obou koncích vodicí lišty',
    'vodicí lišta w systému o wymiarach 50x6 mm dostepna w dlugosciach': 'vodicí lišta systému o rozměru 50 × 6 mm je dostupná v délkách',
    'skleněný sprchový kout- wykonywana pod rozměr klienta, ze skla 8 mm kaleného': 'skleněný sprchový kout vyráběný na míru z 8mm kaleného skla',
    'sklo kalené to sklo poddane obrobce termicznej, která podnosi jego odolnost na uszkodzenia mechanické': 'kalené sklo je tepelně zpracované pro vyšší odolnost proti mechanickému poškození',
    'sklo kalené w momencie prasknutí rozpadá se na tysiace drobnych ale tepych kawalkow': 'kalené sklo se při prasknutí rozpadne na tisíce drobných neostrých kousků',
    'skleněný sprchový kout sklada se z dveře + příčky stalej': 'skleněný sprchový kout se skládá z dveří a pevné příčky',
    'montáž na 4 ks kotwy chemicznej przed ociepleniem': 'montáž na 4 chemické kotvy před zateplením',
    'to idealna propozycja dla osob poszukujacych solidnego stříšky w przystepnej cenie': 'ideální řešení pro ty, kdo hledají pevnou stříšku za dostupnou cenu',
    'montáž wspornikow zalecany je przed ociepleniem budynku - do zelbetu na kotwie chemicznej': 'montáž konzol doporučujeme před zateplením budovy do železobetonu pomocí chemické kotvy',
    'sada sklada se z 15 kompletow plastikowych prvků montazowych a dvou gumowych uszczelek o délce 5 m': 'sada se skládá z 15 kompletů plastových montážních prvků a dvou gumových těsnění délky 5 m',
    'dveře sa bezpečné, kalené': 'dveře jsou bezpečnostní a kalené',
    'system posuvny cechuje se tichou praci i velmi dobrym wykonaniem': 'posuvný systém se vyznačuje tichým chodem a kvalitním zpracováním',
    'efekt stluczonego skla': 'efekt popraskaného skla',
    'przed vypadnutím': 'proti vypadnutí',
    'obu krancach': 'obou koncích',
    'sklada se': 'skládá se',
    'wewnetrzna': 'vnitřní',
    'rozbita': 'narušena',
    'postac': 'podobu',
    'pierwszy': 'první',
    'rzut oka': 'pohled',
    'rozni': 'liší',
    'zwyklego': 'běžného',
    'zachowaniu': 'zachování',
    'nienaruszonej': 'neporušeného',
    'zespolonych': 'spojených',
    'warstw': 'vrstev',
    'trwale': 'trvale',
    'przed': 'před',
    'krancach': 'koncích',
    'piekny': 'krásný',
    'nowoczesny': 'moderní',
    'przystepnej': 'dostupné',
    'cenie': 'ceně',
    'piec': 'pět',
    'wersji': 'variant',
    'kolorystycznych': 'barevných',
    'rdza': 'rez',
    'korozja': 'koroze',
    'zapytania': 'dotazy',
    'mailowe': 'e-mailem',
    'zabezpieczajace': 'zajišťující',
    'dostepna': 'dostupná',
    'dlugosciach': 'délkách',
    'wykonywana': 'vyráběný',
    'obrobce': 'zpracování',
    'termicznej': 'tepelném',
    'podnosi': 'zvyšuje',
    'jego': 'jeho',
    'uszkodzenia': 'poškození',
    'tysiace': 'tisíce',
    'drobnych': 'drobných',
    'tepych': 'neostrých',
    'kawalkow': 'kousků',
    'stalej': 'pevné',
    'kotwy': 'kotvy',
    'chemicznej': 'chemické',
    'ociepleniem': 'zateplením',
    'propozycja': 'řešení',
    'poszukujacych': 'hledající',
    'solidnego': 'pevnou',
    'wspornikow': 'konzol',
    'zalecany': 'doporučená',
    'budynku': 'budovy',
    'zelbetu': 'železobetonu',
    'kompletow': 'kompletů',
    'plastikowych': 'plastových',
    'montazowych': 'montážních',
    'gumowych': 'gumových',
    'uszczelek': 'těsnění',
    'cechuje': 'vyznačuje',
    'dobrym': 'dobrým',
    'wykonaniem': 'zpracováním',
    # Legacy supplier templates cleaned during the second deep pass.
    'pokud zalezy panstwu na jakims konkretnym prvku w danej grafice, nalezy to uvést podczas skládání objednávky- w przeciwnym wypadku reklamacje ne beda uwzgledniane': 'pokud vám záleží na konkrétním prvku grafiky, uveďte jej při objednávce; k pozdějším reklamacím tohoto výběru nelze přihlížet',
    'závěsy hydraulické z funkce samozamykacza prosím promyšlené nákupy, protože všechny produkty vyrábíme na zakázku i po nákupu ne ma možnosti rezygnacji z objednávky': 'hydraulické závěsy s funkcí samozavírání. Nákup si prosím promyslete, všechny produkty vyrábíme na zakázku a objednávku po nákupu nelze zrušit',
    'stosowanie nadruku powoduje ozdobe wizualna dveře': 'potisk vytváří dekorativní vzhled dveří',
    'oznaczenie esg oznacza ze sklo zostala poddana procesowi hartowania czyli zvýšení jej trwalosci natomiast vsg polega na zlaczeniu pohromadě dwoch taflii w piecu do laminowania co finalnie daje nam jedna szybe bezpieczna': 'označení ESG znamená, že sklo prošlo kalením pro zvýšení odolnosti. VSG vzniká spojením dvou tabulí v laminovací peci do jednoho bezpečnostního skla',
    'oznaczenie esg oznacza, ze sklo zostala poddana procesowaniu hartowania': 'označení ESG znamená, že sklo prošlo procesem kalení',
    'oznaczenie esg powoduje podniesienie trwalosci skla w trakcie hartowania a vsg oznacza polaczenie dwoch skleněných tabulí pohromadě w jedna, pod wplywem wysokiej temperatury w piecu do laminacji': 'označení ESG znamená zvýšení odolnosti skla kalením a VSG spojení dvou skleněných tabulí vysokou teplotou v laminovací peci',
    'díky zastosowanej těsnění získáváme efekt tlumení zvuků i ciche zamykanie': 'díky použitému těsnění získáváme tlumení zvuků a tiché zavírání',
    'podzialka ta je zawsze w komplecie, niezaleznie od wybranego podzialu dveře': 'tato dělicí příčka je vždy součástí sady bez ohledu na zvolené členění dveří',
    'kolorystyka ram aluminiowych wpisuje se w najnowsze trendy': 'barevnost hliníkových rámů odpovídá nejnovějším trendům',
    'díky použití černých plaskownikow p2 možné získání efektu podzialu w pionie': 'pomocí černých lišt P2 lze vytvořit svislé členění',
    'díky použití bialych plaskownikow p2 možné získání efektu podzialu w pionie': 'pomocí bílých lišt P2 lze vytvořit svislé členění',
    'przez takie sklo jsou vidět obrysy': 'přes takové sklo jsou vidět pouze obrysy',
    'balkon montujemy na dwoch smuklych listwach po bokach okna': 'balkon montujeme na dvě štíhlé lišty po stranách okna',
    'sklo esg/vsg má zwiekszona odolnost na czynniki mechaniczne, je bardziej wytrzymale na uderzenia a naprezenia zwiazane ze zmianami temperatury': 'sklo ESG/VSG má zvýšenou mechanickou odolnost a lépe odolává nárazům i pnutí při změnách teploty',
    'vsg natomiast to proces zgrzania dwoch skleněných tabulí o tloušťky 6 mm pohromadě w jedna': 'VSG je proces spojení dvou skleněných tabulí tloušťky 6 mm do jednoho celku',
    'uznaje se, ze sklo laminované esg vsg to najbezpieczniejsze sklo na rynku': 'laminované sklo ESG/VSG je považováno za jedno z nejbezpečnějších na trhu',
    'díky použití wysokiej kvality stali nierdzewnej, pomimo malych gabarytow konzoly sa velmi wytrzymale zapewniajac stabilitu calej konstrukcji': 'díky kvalitní nerezové oceli jsou konzoly i přes malé rozměry velmi odolné a zajišťují stabilitu celé konstrukce',
    'to inaczej sklo lepené, jde o typ skla bezpiecznego powstajacego w wyniku polaczenia dwoch skleněných tabulí pomocí specjalnej folie': 'jde o lepené bezpečnostní sklo vzniklé spojením dvou skleněných tabulí speciální fólií',
    'takové sklo se po rozbití nevysype, a w miejscu uderzenia powstaje splot rozchodzacych se pekniec': 'takové sklo se po rozbití nevysype a v místě nárazu vznikne síť prasklin',
    'niweluje to možnost zranienia se': 'tím se snižuje riziko zranění',
    'mechanizm jednoczesnego przesuwu 2 dveře symetryczne, jednoczesne otwieranie i zamykanie obu skrzydel': 'mechanismus současného posuvu dvou dveří; symetrické otevírání a zavírání obou křídel',
    'prosím promyšlené nákupy protože všechny dveře vyrábíme na objednávku tedy ne ma možnosti rezygnacji z objednávky': 'nákup si prosím promyslete, všechny dveře vyrábíme na objednávku a objednávku nelze zrušit',
    'protože všechny produkty vyrábíme na zakázku ne ma tedy možnosti rezygnacji z objednávky': 'všechny produkty vyrábíme na zakázku, proto objednávku nelze zrušit',
    'zastosowane zrcadlo to dokladnie takie jakie stosuje se w lustrach lazienkowych, z ta roznica ze aby zrcadlo bylo z obou stran': 'použité zrcadlo odpovídá koupelnovému zrcadlu, ale je zrcadlové z obou stran',
    'w przypadku budynkow ocieplonych zachecamy do montowania stříšky na kotwie chemicznej, co powoduje stale zespolenie stříšky z budynkiem': 'u zateplených budov doporučujeme montáž stříšky chemickou kotvou, která zajistí pevné spojení s budovou',
    'zabezpieczenie przed vypadnutím z toru': 'zajištění proti vypadnutí z kolejnice',
    'zestaw zawiera': 'sada obsahuje',
    'w komplecie': 'v sadě',
    'zestaw montážní': 'montážní sada',
    'komplet montážního kování': 'kompletní montážní kování',
    'komplet vozíků ze stoperami': 'kompletní sada vozíků s dorazy',
    'szyna 2m': 'kolejnice 2 m',
    'szyna': 'kolejnice',
    'dwoch': 'dvou',
    'zestaw': 'sada',
    'rezygnacji': 'zrušení',
    'podzialu': 'členění',
    'komplecie': 'sadě',
    'laczeniu': 'spoji',
    'zlozeniu': 'zadání',
    'podczas': 'během',
    'uwagi': 'poznámky',
    'przeciwnym': 'opačném',
    'wypadku': 'případě',
    'reklamacje': 'reklamace',
    'uwzgledniane': 'zohledněny',
    'nalezy': 'je třeba',
    'jakims': 'nějakém',
    'konkretnym': 'konkrétním',
    'danej': 'dané',
    'grafice': 'grafice',
    'zalezy': 'záleží',
    'panstwu': 'vám',
    'plaskownikow': 'lišt',
    'pionie': 'svisle',
    'ram': 'rámů',
    'aluminiowych': 'hliníkových',
    'wpisuje': 'zapadá',
    'najnowsze': 'nejnovější',
    'trendy': 'trendy',
    'zastosowanej': 'použitému',
    'ciche': 'tiché',
    'zamykanie': 'zavírání',
    'otwieranie': 'otevírání',
    'skrzydel': 'křídel',
    'jednoczesne': 'současné',
    'mechanizm': 'mechanismus',
    'zawsze': 'vždy',
    'niezaleznie': 'nezávisle',
    'wybranego': 'zvoleného',
    'podzialka': 'dělicí příčka',
    'przez': 'přes',
    'obrysy': 'obrysy',
    'montujemy': 'montujeme',
    'smuklych': 'štíhlých',
    'listwach': 'lištách',
    'bokach': 'stranách',
    'okna': 'okna',
    'maksymalny': 'maximální',
    'wynosi': 'činí',
    'innego': 'jiného',
    'rodzaju': 'druhu',
    'powoduje': 'způsobuje',
    'podniesienie': 'zvýšení',
    'trwalosci': 'odolnosti',
    'wplywem': 'vlivem',
    'wysokiej': 'vysoké',
    'temperatury': 'teploty',
    'piecu': 'peci',
    'zgrzania': 'spojení',
    'uznaje': 'považuje',
    'najbezpieczniejsze': 'nejbezpečnější',
    'rynku': 'trhu',
    'pomimo': 'navzdory',
    'malych': 'malým',
    'gabarytow': 'rozměrům',
    'zapewniajac': 'zajišťující',
    'calej': 'celé',
    'konstrukcji': 'konstrukce',
    'skladajace': 'skládající',
    'polaczenia': 'spojení',
    'specjalnej': 'speciální',
    'miejscu': 'místě',
    'uderzenia': 'nárazy',
    'pekniec': 'prasklin',
    'zranienia': 'zranění',
    'zwiekszona': 'zvýšenou',
    'czynniki': 'vlivy',
    'mechaniczne': 'mechanické',
    'wytrzymale': 'odolné',
    'naprezenia': 'pnutí',
    'zwiazane': 'spojené',
    'zmianami': 'změnami',
    'zostala': 'byla',
    'poddana': 'podrobena',
    'procesowi': 'procesu',
    'hartowania': 'kalení',
    'zlaczeniu': 'spojení',
    'taflii': 'tabulí',
    'laminowania': 'laminování',
    'finalnie': 'výsledně',
    'szybe': 'tabuli',
    'bezpieczna': 'bezpečnou',
    'zastosowane': 'použité',
    'dokladnie': 'přesně',
    'takie': 'takové',
    'jakie': 'jaké',
    'stosuje': 'používá',
    'lustrach': 'zrcadlech',
    'lazienkowych': 'koupelnových',
    'roznica': 'rozdíl',
    'aby': 'aby',
    'bylo': 'bylo',
    'wtedy': 'tehdy',
    'pozbywamy': 'zbavíme se',
    'trzech': 'třech',
    'opcjach': 'variantách',
    'matowym': 'matným',
    'itd': 'atd.',
    # Recurring supplier-copy blocks that survived the first PL→CS pass.
    # Hybrid LOFT/system copy also guarded at the API and theme boundaries.
    "představujeme nowe skleněné dveře na systemie posuvném": "představujeme nové skleněné dveře na posuvném systému",
    "po czarnym systemie loft-art przyszla kolej na": "po černém systému LOFT-ART přišel na řadu",
    "loft-art bialym": "LOFT-ART bílý",
    "jde o systém w minimalistycznym stylu industrialnym, dodávající idealnej eleganckiej lekkosci i poczucia przestrzeni w kazdym wnetrzu": "jde o systém v minimalistickém industriálním stylu, který dodává lehkost a pocit prostoru v každém interiéru",
    "systém ten pozwala dzielic przestrzen bez utraty doswietlenia": "systém umožňuje dělit prostor bez ztráty světla",
    "dveře oferujemy w pieciu rozmiarach": "dveře nabízíme v pěti rozměrech",
    "po skreceniu rámu hliníkové wraz ze szklem uzyskamy rozměry calosciowe": "po sešroubování hliníkového rámu se sklem získáte celkové rozměry",
    "ideálně se hodí wiec do pomieszczen o duzej wilgotnosci": "ideálně se hodí do místností s vyšší vlhkostí",
    "jest rowniez to, ze nadaje sie do bardzo duzych": "je také to, že se hodí i na velmi velké",
    "jest wiec bardziej wytrzymala na duze obciazenia": "je tedy odolnější vůči velkému zatížení",
    "wytrzymuje ciezar szerokich i ciezkich tafli skla": "unese váhu širokých a těžkých tabulí skla",
    "w sklad zárubně hliníkové wchodzi": "součástí hliníkové zárubně je",
    "nowe": "nové",
    "systemie": "systému",
    "systemow": "systémů",
    "systemu": "systému",
    "przedstawiamy": "představujeme",
    "naszej": "naší",
    "ekskluzywnej": "exkluzivní",
    "linii": "řady",
    "uszczelka": "těsnění",
    "pionowa": "svislé",
    "pozioma": "vodorovné",
    "lacznik": "spojka",
    "naroznik": "rohový prvek",
    "stabilizujacy": "stabilizační",
    "duze": "velké",
    "prawy": "pravý",
    "lewy": "levý",
    "galka": "koule",
    "sciana": "stěna",
    "podloga": "podlaha",
    "magnetyczna": "magnetické",
    "profilem": "profilem",
    "kolorze": "barvě",
    "blyszczacy": "lesklý",
    "materialu": "materiálu",
    "przemyslane": "promyšlené",
    "zakupy": "nákupy",
    "gdyz": "protože",
    "minimalistycznym": "minimalistickém",
    "industrialnym": "industriálním",
    "idealnej": "ideální",
    "eleganckiej": "elegantní",
    "lekkosci": "lehkosti",
    "poczucia": "pocitu",
    "przestrzeni": "prostoru",
    "kazdym": "každém",
    "wnetrzu": "interiéru",
    "pozwala": "umožňuje",
    "dzielic": "dělit",
    "utraty": "ztráty",
    "doswietlenia": "osvětlení",
    "oferujemy": "nabízíme",
    "pieciu": "pěti",
    "rozmiarach": "rozměrech",
    "czarnym": "černém",
    "przyszla": "přišla",
    "skreceniu": "sešroubování",
    "wraz": "spolu",
    "szklem": "sklem",
    "uzyskamy": "získáme",
    "calosciowe": "celkové",
    "wiec": "tedy",
    "pomieszczen": "místností",
    "duzej": "velké",
    "wilgotnosci": "vlhkosti",
    "duzym": "velkým",
    "rowniez": "také",
    "nadaje": "hodí se",
    "bardzo": "velmi",
    "duzych": "velkých",
    "wytrzymala": "odolná",
    "obciazenia": "zatížení",
    "ciezar": "váhu",
    "szerokich": "širokých",
    "ciezkich": "těžkých",
    "uszczelce": "těsnění",
    "tlumienia": "tlumení",
    "dzwiekow": "zvuků",
    "wchodzi": "patří",
    "srebrnej": "stříbrné",
    "pionowy": "svislý",
    "poziomy": "vodorovný",
    "hartowna": "kalené",
    "hydrauliczne": "hydraulické",
    "elektrozaczepem": "elektrickým dorazem",
    "jest": "je",
    "tabule skla staje sie latwiejsza w utrzymaniu czystosci": "tabule skla se snáze udržuje v čistotě",
    "system trubkovy charakteryzuje sie cicha praca i komfortem w uzytkowaniu": "trubkový systém se vyznačuje tichým chodem a pohodlným používáním",
    "system vyrobeny z nerezove oceli szczotkowanej, dopracowany, niezawodny": "systém z broušené nerezové oceli, propracovaný a spolehlivý",
    "wszystkie regulacje lze przeprowadzic po zamontowaniu": "veškeré seřízení lze provést po montáži",
    "nasze dveře z grafikami powstaja dzieki drukarce uv ktora nanosi vzor na jednej z szyb": "naše dveře s grafikou vznikají UV tiskem, který nanáší vzor na jednu ze skleněných tabulí",
    "potisk jest wewnatrz szyb dzieki czemu ne lze go seškrábnout, a sklo pieknie blyszczy z obu stron": "potisk je uvnitř skla, takže jej nelze seškrábnout, a sklo se krásně leskne z obou stran",
    "potisk jest wewnatrz szyb,dzieki czemu ne lze go seškrábnout, a sklo pieknie blyszczy z obu stron": "potisk je uvnitř skla, takže jej nelze seškrábnout, a sklo se krásně leskne z obou stran",
    "kolor grafiki moze sie roznic o kilka tonow": "barva grafiky se může lišit o několik odstínů",
    "zalezne od ustawien monitora a druku": "v závislosti na nastavení monitoru a tisku",
    "moznost regulacji glebokosci skrzydla do pouzdra dzieki stoperowi regulovanemu po zamontowaniu dveře": "možnost nastavení hloubky křídla v pouzdře pomocí dorazu nastavitelného po montáži dveří",
    "regulace nahoře / dole na wozku": "nastavení nahoře / dole na vozíku",
    "odpornosc na korozje en 1670": "odolnost proti korozi EN 1670",
    "maksymalna šířka skrzydla dveře": "maximální šířka dveřního křídla",
    "maksymalna výška skrzydla": "maximální výška křídla",
    "wycena mailowa": "cena na vyžádání e-mailem",
    "wycena": "cena na vyžádání",
    "dostupne komfortowe funkcje": "dostupné komfortní funkce",
    "kompletna ramecek docieta na miru wraz z dostosowanymi otworami do jej skrecenia": "kompletní rám nařezaný na míru včetně připravených otvorů pro sešroubování",
    "wysoki komfort dzieki pouziti samodomykacza": "vysoký komfort díky použití samozavírače",
    "drugie skrzydlo otwiera i zamyka sie automatycznie, jesli pierwsze skrzydlo je w ruchu": "druhé křídlo se automaticky otevírá a zavírá, pokud je první křídlo v pohybu",
    "niewielki spodni voditko zapewnia stabilnosc, obejmujac tafle skla z obu stron": "malé spodní vodítko zajišťuje stabilitu a obepíná tabuli skla z obou stran",
    "z jednej strony je hladke jako sklo v okne, z drugiej satinove lekko szorstkie": "z jedné strany je hladké jako okenní sklo, z druhé jemně drsné satinované",
    "sklo taka w momencie prasknuti ne rozpada sie": "takové sklo se při prasknutí nerozpadne",
    "sklo takie ne se sype po rozbiciu": "takové sklo se po rozbití nevysype",
    "hartowanie skla to proces podniesienia odpornosci skla na korozje": "kalení skla zvyšuje jeho odolnost",
    "skleneny sprchovy kout sklada sie z dveře + přičky stalej": "skleněný sprchový kout se skládá z dveří a pevné příčky",
    "prosimy podac": "uveďte prosím",
    "prosimy po zakupie o maila": "po nákupu nám prosím napište e-mail",
    "prosimy o maila": "napište nám prosím e-mail",
    "prosimy": "prosíme",
    "wszystkie": "všechny",
    "regulacje": "seřízení",
    "przeprowadzic": "provést",
    "prowadznik": "vodítko",
    "prowadnik": "vodítko",
    "latwa i mozliwa regulace wysokosci": "snadné nastavení výšky",
    "system przetestowany na ponad 200 000 cykli": "systém testovaný na více než 200 000 cyklů",
    "minimalistyczne wymiary elementow systemu": "minimalistické rozměry prvků systému",
    "panstwo": "vy",
    "szyb": "skleněných tabulí",
    "jakosci": "kvality",
    "nowoczesnych": "moderních",
    "przetestowany": "testovaný",
    "ponad": "více než",
    "cykli": "cyklů",
    "wysokosci": "výšky",
    "elementow": "prvků",
    "minimalistyczne": "minimalistické",
    "mozliwa": "možné",
    "latwa": "snadná",
    "sie": "se",
    "wozku": "vozíku",
    "odpornosc": "odolnost",
    "korozje": "korozi",
    "klasa": "třída",
    "piaskowanym": "pískovaným",
    "wykonywany": "provedený",
    "szczotkowanej": "broušené",
    "dopracowany": "propracovaný",
    "niezawodny": "spolehlivý",
    "charakteryzuje": "vyznačuje",
    "cicha": "tichou",
    "praca": "prací",
    "uzytkowaniu": "používáním",
    "latwiejsza": "snazší",
    "utrzymaniu": "údržbě",
    "czystosci": "čistoty",
    "staje sie": "stává se",
    "powstaja": "vznikají",
    "dzieki": "díky",
    "drukarce": "tiskárně",
    "ktora": "která",
    "nanosi": "nanáší",
    "jednej": "jedné",
    "wewnatrz": "uvnitř",
    "czemu": "čemuž",
    "pieknie": "krásně",
    "blyszczy": "leskne se",
    "obu stron": "obou stran",
    "kolor": "barva",
    "moze sie roznic": "se může lišit",
    "kilka tonow": "několik odstínů",
    "glebokosci": "hloubky",
    "skrzydla": "křídla",
    "stoperowi": "dorazu",
    "zamontowaniu": "montáži",
    "komfortowe": "komfortní",
    "funkcja": "funkce",
    "docieta": "nařezaný",
    "dostosowanymi": "připravenými",
    "otworami": "otvory",
    "skrecenia": "sešroubování",
    "samodomykacza": "samozavírače",
    "drugie": "druhé",
    "otwiera": "otevírá",
    "zamyka": "zavírá",
    "automatycznie": "automaticky",
    "jesli": "pokud",
    "pierwsze": "první",
    "ruchu": "pohybu",
    "zapewnia": "zajišťuje",
    "stabilnosc": "stabilitu",
    "obejmujac": "obepínající",
    "tafla": "tabule",
    "tafli": "tabule",
    "lekko": "mírně",
    "szorstkie": "drsné",
    "rozpada sie": "rozpadá se",
    "rozbiciu": "rozbití",
    "sype": "vysype",
    "podniesienia": "zvýšení",
    "podac": "uvést",
    "zakupie": "nákupu",
    "maila": "e-mail",
    "otrzymuja panstwo": "obdržíte",
    "prosím seznam se se schématem strany otevírání dveří": "Prosím seznam se se schématem strany otevírání dveří",
    "prosím seznam se se schématem": "Prosím seznam se se schématem",
    "při prasknutí skla VSG fólie drží střepy pohromadě": "při prasknutí skla VSG fólie drží střepy pohromadě",
    # Matte-side N/A choice (Polish + hybrid leftovers after word-by-word PL→CS)
    "brak gdy drzwi bezbarwne lub laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak gdy dveře bezbarwne lub laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak gdy dveře čiré nebo laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak gdy dveře čiré lub laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak - gdy dveře čiré nebo laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak - gdy dveře čiré lub laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak - gdy čiré nebo laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak - gdy čiré lub laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak gdy čiré nebo laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "brak gdy čiré lub laminat": "chybí, pokud jsou dveře bezbarvé nebo laminátové",
    "VSG folie drží": "VSG fólie drží",
    " folie ": " fólie ",
    "folie drží": "fólie drží",
    "prosimy o zapoznanie sie ze schematem stronności": "prosím seznam se se schématem strany otevírání dveří",
    "prosimy o zapoznanie sie ze schematem stronnosci": "prosím seznam se se schématem strany otevírání dveří",
    "prosimy o seznámení sie z schematem dotyczacym strany otevírání dveře": "prosím seznam se se schématem strany otevírání dveří",
    "prosimy o seznámení sie z schematem dotyczacym": "prosím seznam se se schématem",
    "prosimy o": "prosím",
    "schematem dotyczacym": "schématem",
    "dotyczacym": "týkajícím se",
    "wykonana ze skla": "vyrobená ze skla",
    "wykonana ze": "vyrobená ze",
    "krawedzie": "hrany",
    "krawędzie": "hrany",
    "gladkie": "hladké",
    "gładkie": "hladké",
    "idealnie sprawdzajace sie": "ideálně se hodící",
    "idealnie sprawdzające się": "ideálně se hodící",
    "sprawdzajace sie": "hodící se",
    "sprawdzające się": "hodící se",
    "doskonalej jakosci": "vynikající kvality",
    "doskonałej jakości": "vynikající kvality",
    "w nowoczesnych wnetrzach": "v moderních interiérech",
    "w nowoczesnych wnętrzach": "v moderních interiérech",
    "pelniace funkcje": "plnící funkci",
    "pełniące funkcje": "plnící funkci",
    "w przypadku prasknutí folia ktora polaczyla dwie skla trzyma nadal je ze soba mimo prasknutí": "při prasknutí fólie, která spojila dvě skla, je stále drží pohromadě",
    "w przypadku prasknutí folia": "při prasknutí fólie",
    "folia ktora polaczyla dwie skla trzyma nadal je ze soba": "fólie, která spojila dvě skla, je stále drží pohromadě",
    "folia która połączyła dwie szkła trzyma nadal je ze sobą": "fólie, která spojila dvě skla, je stále drží pohromadě",
    "ktora polaczyla": "která spojila",
    "która połączyła": "která spojila",
    "trzyma nadal": "stále drží",
    "ze soba": "pohromadě",
    "ze sobą": "pohromadě",
    "to sklo bezpieczne": "jde o bezpečnostní sklo",
    "sklo laminované - to sklo bezpieczne": "laminované sklo — bezpečnostní sklo",
    "barevných folií": "barevných fólií",
    "dřevodekorativní folií": "dřevodekorativní fólií",
    " folia ": " fólie ",
    "folia": "fólie",
    "wkladka patentowa wc lub klucz/klucz": "vložka WC nebo klíč/klíč (dle modelu)",
    "wkładka patentowa wc lub klucz/klucz": "vložka WC nebo klíč/klíč (dle modelu)",
    "vložka patentowa wc nebo klucz/klucz": "vložka WC nebo klíč/klíč (dle modelu)",
    "vložka patentowa wc lub klucz/klucz": "vložka WC nebo klíč/klíč (dle modelu)",
    "wkladka patentowa wc nebo klucz/klucz": "vložka WC nebo klíč/klíč (dle modelu)",
    "wkladka patentowa wc": "vložka WC",
    "wkładka patentowa wc": "vložka WC",
    "vložka patentowa wc": "vložka WC",
    "patentowa wc": "WC",
    "patentowa": "",
    "klucz/klucz": "klíč/klíč",
    "klucz": "klíč",
    "konstrukcja zárubně rovná zárubeň opc wykonana jest z wysokogatunkowej plyty mdf i oklejona jest ekologiczna, drewnopodobna folia dekoracyjna zárubeň posiada pevnou šířka 100 mm, ne ma opasek maskujacych ponizej představujeme schematy futryny": "Konstrukce rovné zárubně OPC je z kvalitní MDF desky, opatřená ekologickou dřevodekorativní fólií. Zárubeň má pevnou šířku 100 mm, bez maskovacích lišt. Níže jsou schémata zárubně",
    "konstrukcja zárubně rovná zárubeň opc": "konstrukce rovné zárubně OPC",
    "sztywna konstrukcja": "Tuhá konstrukce",
    "Sztywna konstrukcja": "Tuhá konstrukce",
    "konstrukcja zárubně": "konstrukce zárubně",
    "Konstrukcja zárubně": "Konstrukce zárubně",
    "Konstrukcja": "Konstrukce",
    "konstrukcja": "konstrukce",
    "wykonana jest z wysokogatunkowej plyty MDF": "je z kvalitní MDF desky",
    "wykonana jest z wysokogatunkowej płyty MDF": "je z kvalitní MDF desky",
    "wykonana jest z wysokogatunkowej plyty mdf": "je z kvalitní MDF desky",
    "wysokogatunkowej": "kvalitní",
    "plyty MDF": "MDF desky",
    "płyty MDF": "MDF desky",
    "plyty mdf": "MDF desky",
    "oklejona jest": "opatřená",
    "oklejona": "opatřená",
    "ekologiczna,": "ekologickou",
    "ekologiczna": "ekologickou",
    "drewnopodobna folia dekoracyjna": "dřevodekorativní fólií",
    "folia dekoracyjna": "dekorativní fólie",
    "posiada": "má",
    "ne ma opasek maskujacych": "bez maskovacích lišt",
    "nie ma opasek maskujących": "bez maskovacích lišt",
    "nie ma opasek maskujacych": "bez maskovacích lišt",
    "opasek maskujacych": "maskovacích lišt",
    "opasek maskujących": "maskovacích lišt",
    "Ponizej představujeme schematy futryny": "Níže jsou schémata zárubně",
    "Poniżej przedstawiamy schematy futryny": "Níže jsou schémata zárubně",
    "ponizej przedstawiamy schematy futryny": "níže jsou schémata zárubně",
    "ponizej představujeme schematy futryny": "níže jsou schémata zárubně",
    "ponizej": "níže",
    "poniżej": "níže",
    "schematy futryny": "schémata zárubně",
    "schematy": "schémata",
    "futryny": "zárubně",
    "proszę o zapoznanie się ze schematem stronności": "prosím seznam se se schématem strany otevírání dveří",
    "prosze o zapoznanie sie ze schematem stronnosci": "prosím seznam se se schématem strany otevírání dveří",
    "zapoznanie się ze schematem stronności": "seznamte se se schématem strany otevírání dveří",
    "zapoznanie sie ze schematem stronnosci": "seznamte se se schématem strany otevírání dveří",
    "zapoznanie": "seznámení",
    "stronności": "strany otevírání",
    "stronnosci": "strany otevírání",
    "maksymalne wykorzystanie przestrzeni (swiatla przejscia)": "maximální využití prostoru (světlá šířka průchodu)",
    "maksymalne wykorzystanie przestrzeni": "maximální využití prostoru",
    "swiatla przejscia": "světlá šířka průchodu",
    "światła przejścia": "světlá šířka průchodu",
    "uzyskanie efektu": "získání efektu",
    "demontazu szyny": "demontáže kolejnice",
    "demontażu szyny": "demontáže kolejnice",
    "Sprawdz produkty powiazane do produktu": "Související produkty",
    "Sprawdź produkty powiązane do produktu": "Související produkty",
    "Sprawdz powiazane produkty": "Související produkty",
    "Sprawdź powiązane produkty": "Související produkty",
    "produkty powiazane do produktu": "související produkty",
    "produkty powiązane do produktu": "související produkty",
    "powiazane produkty": "související produkty",
    "powiązane produkty": "související produkty",
    "powiazane": "související",
    "powiązane": "související",
    "Sprawdz": "Zkontrolujte",
    "Sprawdź": "Zkontrolujte",
    "sprawdz": "zkontrolujte",
    "sprawdź": "zkontrolujte",
    "satin szlifowana": "satin broušený",
    "satin szlifowane": "satin broušené",
    "powierzchnia szlifowana matowa": "povrch broušená matná",
    "powierzchnia szlifowana matná": "povrch broušená matná",
    "szlifowana matowa": "broušená matná",
    "szlifowana matná": "broušená matná",
    "szlifowana": "broušená",
    "materiał: aluminium": "materiál: hliník",
    "material: aluminium": "materiál: hliník",
    "materiál: aluminium": "materiál: hliník",
    "z aluminium": "z hliníku",
    "aluminium": "hliník",
    "pevnou šířka": "pevnou šířku",
    "do wybranych modeli": "do vybraných modelů",
    "do vybraných modeli": "do vybraných modelů",
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
    "stronność": "strany otevírání",
    "stronnosc": "strany otevírání",
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
    "samodomyk": "samozavírač / tichý dojezd",
    "samozavírač / tichý domyk": "samozavírač / tichý dojezd",
    "cichy domyk": "tichý dojezd",
    "tichý domyk": "tichý dojezd",
    "domykacz": "samozavírač",
    "systém przesuwny wariant": "varianta posuvného systému",
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
    "kotwa montážní kotva": "montážní kotva",
    "montážní kotva montážní kotva": "montážní kotva",
    "kotwa porotherm": "kotva Porotherm",
    "kotwa kotwa": "kotva",
    "kotwa": "kotva",
    "lewe": "levé",
    "prawe": "pravé",
    "madlo antaba": "madlo táhlo",
    "madlo typu antaba": "madlo typu táhlo",
    "pochwyt do drzwi typu antaba": "madlo typu táhlo",
    "pochwyty typu madlo antaba": "madla typu táhlo",
    "pochwyty typu": "madla typu",
    "antaba": "táhlo",
    "muszelka": "mušle",
    "satyna": "satin",
    "oscieznica regulowana": "nastavitelná zárubeň",
    "ościeżnica regulowana": "nastavitelná zárubeň",
    "oscieznica prosta": "rovná zárubeň",
    "ościeżnica prosta": "rovná zárubeň",
    "oscieznica aluminiowa": "hliníková zárubeň",
    "ościeżnica aluminiowa": "hliníková zárubeň",
    "oscieznica": "zárubeň",
    "ościeżnica": "zárubeň",
    "futryna regulowana": "nastavitelná zárubeň",
    "futryna stala": "pevná zárubeň",
    "futryna stała": "pevná zárubeň",
    "futryna": "zárubeň",
    "klamka": "klika",
    "zamek z klamka": "zámek s klikou",
    "zámek z klamka": "zámek s klikou",
    "mocowan": "úchytů",
    "mocowań": "úchytů",
    "mocowanie": "kotvení",
    "mocowania": "kotvení",
    "okucia": "kování",
    "okuć": "kování",
    "okuc": "kování",
    "zawias": "závěs",
    "do wyboru": "na výběr",
    "wykonane sa": "jsou vyrobené",
    "wykonane są": "jsou vyrobené",
    "ze stali nierdzewnej": "z nerezové oceli",
    "stal nierdzewna": "nerezová ocel",
    "stal szczotkowana": "broušená ocel",
    "powodzeniem stosowac": "úspěšně použít",
    "powodzeniem stosować": "úspěšně použít",
    "mozna z": "lze",
    "można z": "lze",
    "pneumatyczny silownik": "pneumatický píst",
    "uniwersalny": "univerzální",
    "jest to": "jde o",
    "powoduje wyhamowanie": "způsobuje zpomalení",
    "umozliwiajac": "a umožňuje",
    "umożliwiając": "a umožňuje",
    "ruchu dveře": "pohybu dveří",
    "ruchu drzwi": "pohybu dveří",
    "dystans": "rozteč",
    "szklo": "sklo",
    "szkla": "skla",
    "szkle": "skle",
    "szkła": "skla",
    "trawionym": "leptaném",
    "trawione": "leptané",
    "bezbarwnym": "čirém",
    "piaskowanej": "pískované",
    "piaskowany": "pískovaný",
    "piaskowane": "pískované",
    "duza iloscia": "velkým podílem",
    "dużą ilością": "velkým podílem",
    "powierzchni": "povrchu",
    "specyfikacja wzorow": "specifikace vzorů",
    "specyfikacja wzorów": "specifikace vzorů",
    "wykonany na": "provedený na",
    "malowane proszkowo": "práškově lakované",
    "kolor czarny": "barva černá",
    "w kolorze czarnym": "v černé barvě",
    "wkladka": "vložka",
    "wkładka": "vložka",
    "zaslepki na trzpienie": "záslepky na čepy",
    "zaślepki na trzpienie": "záslepky na čepy",
    "trzpienie": "čepy",
    "lozysko podlogowe": "podlahové ložisko",
    "łożysko podłogowe": "podlahové ložisko",
    "zawias dolny": "spodní závěs",
    "wykonczeniu satyny": "satinovém provedení",
    "wykończeniu satyny": "satinovém provedení",
    "hartowana": "kalené",
    "hartowane": "kalené",
    "przesuwnym": "posuvném",
    "przesuwny": "posuvný",
    "przesuwne": "posuvné",
    "wahadlowe": "kyvné",
    "wahadłowe": "kyvné",
    "rurowym": "trubkovém",
    "rurowy": "trubkový",
    "nadajacy": "dodávající",
    "nadający": "dodávající",
    "prezentujemy panstwu": "představujeme",
    "prezentujemy państwu": "představujeme",
    "nowosc": "novinka",
    "nowość": "novinka",
    "absolutna nowosc": "absolutní novinka",
    "idealnie nadaje sie": "ideálně se hodí",
    "idealnie nadaje się": "ideálně se hodí",
    "wykonana w 100 % z aluminium": "vyrobená ze 100 % hliníku",
    "wykonana z mdf": "vyrobená z MDF",
    "marki dre": "značky DRE",
    "aluminiowy": "hliníkový",
    "aluminiowe": "hliníkové",
    "komorowy": "komorový",
    "balustrad szklanych": "skleněných zábradlí",
    "mocowanie napowierzchniowe": "povrchové kotvení",
    "mocowanie boczne": "boční kotvení",
    "mocowanie z wierzchu": "kotvení shora",
    "dostarczane w odcinkach": "dodávané v délkách",
    "zestaw uszczelek i mocowan": "sada těsnění a úchytů",
    "zestaw uszczelek i mocowań": "sada těsnění a úchytů",
    "na dlugosc profilu": "na délku profilu",
    "na długość profilu": "na délku profilu",
    "tloušťka skla": "tloušťka skla",
    "od skla": "od skla",
    "podlogowym": "podlahovém",
    "podłogowym": "podlahovém",
    "to idealne rozwiazanie": "je ideální řešení",
    "to idealne rozwiązanie": "je ideální řešení",
    "milosnikow": "milovníků",
    "miłośników": "milovníků",
    "nowoczesnego stylu": "moderního stylu",
    "bezposrednio w podlodze": "přímo v podlaze",
    "bezpośrednio w podłodze": "přímo v podlaze",
    "pozwala zachowac": "umožňuje zachovat",
    "pozwala zachować": "umožňuje zachovat",
    "spigoty sa mocowaniami": "spigoty jsou kotvení",
    "spigoty są mocowaniami": "spigoty jsou kotvení",
    "co to sa spigoty": "co jsou spigoty",
    "co to są spigoty": "co jsou spigoty",
    "montowanymi w podloze": "montovanými v podkladu",
    "montowanymi w podłożu": "montovanými v podkladu",
    "antresoli": "mezzaninu",
    "schodow": "schodů",
    "schodów": "schodów",
    "tarasu": "terasy",
    "balkonu": "balkonu",
    "balkony wykonujemy tylko ze": "balkony vyrábíme jen ze",
    "zmniejszaja swiatlo przejscia": "zmenšují světlost průchodu",
    "zmniejszają światło przejścia": "zmenšují světlost průchodu",
    "natomiast pochwyty": "zatímco madla",
    "zloty": "zlatý",
    "złoty": "zlatý",
    "mosiadz blyszczacy": "lesklá mosaz",
    "mosiądz błyszczący": "lesklá mosaz",
    "material okuc": "materiál kování",
    "materiał okuć": "materiál kování",
    "zalety": "výhody",
    "zalety skla laminowanego": "výhody laminovaného skla",
    "latwe w utrzymaniu czystosc": "snadná údržba čistoty",
    "łatwe w utrzymaniu czystości": "snadná údržba čistoty",
    "w komplecie z": "v sadě s",
    "wsparta na": "opřená o",
    "mosieznym stabilizatorze": "mosazném stabilizátoru",
    "czarnym stabilizatorze": "černém stabilizátoru",
    "chromowanym stabilizatorze": "chromovaném stabilizátoru",
    "dwie opcje montowania": "dvě možnosti montáže",
    "u dolu oraz z boku": "dole a ze strany",
    "u dołu oraz z boku": "dole a ze strany",
    "idealnym rozwiazanie": "ideální řešení",
    "idealnym rozwiązaniem": "ideální řešení",
    "dla tej sprchové kouty": "pro tento sprchový kout",
    "eleganckim profilu": "elegantním profilu",
    "mocowana na": "kotvená na",
    "wykonczenie powierzchni": "povrchová úprava",
    "wykończenie powierzchni": "povrchová úprava",
    "przeznaczenie": "určení",
    "do budynkow ocieplonych": "pro zateplené budovy",
    "do budynków ocieplonych": "pro zateplené budovy",
    "lub": "nebo",
    "material": "materiál",
    "wykonanie": "provedení",
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
    "zabudowa szklana składająca się drzwi wahadłowych oraz z dwóch ścianek stałych": "Skleněné zabudování složené z kyvných dveří a dvou pevných příček",
    "zabudowa szklana składająca się z drzwi wahadłowych oraz z dwóch ścianek stałych": "Skleněné zabudování složené z kyvných dveří a dvou pevných příček",
    "esg hartowane": "ESG kalené",
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
    "nowoczesne drzwi szklane z maskownicą maskującą wózki jezdne": "Moderní skleněné dveře s krytem zakrývajícím pojezdové vozíky",
    "drzwi przesuwne design-lux ze szkła": "posuvné dveře Design-Lux ze skla",
    "system ten nie nadaje się do pomieszczeń gdzie występują listwy przypodłogowe": "tento systém se nehodí do místností s podlahovými lištami",
    "w przypadku listw - należy wybrać inny system przesuwny - ultra slim": "při lištách zvolte jiný posuvný systém – Ultra Slim",
    "w przypadku listw – należy wybrać inny system przesuwny – ultra slim": "při lištách zvolte jiný posuvný systém – Ultra Slim",
    "prowadnica": "vodicí lišta",
    "maskownica": "kryt",
    "komplet okuć montażowych": "komplet montážního kování",
    "tafla szklana bezbarwna": "tabule čirého skla",
    "tafla szklana": "tabule skla",
    "wolne, łagodne dociągnięcie skrzydła drzwi": "pomalé, plynulé dotažení křídla dveří",
    "czas wysyłki": "doba expedice",
    "dni roboczych": "pracovních dní",
    "duża ilość": "skladem",
    "dostępny na zamówienie": "na objednávku",
    "dostepny na zamowienie": "na objednávku",
    "dostępny": "dostupný",
    "dostepny": "dostupný",
    "dostepnymi": "dostupnými",
    "dostępnymi": "dostupnými",
    "na zamówienie": "na objednávku",
    "na zamowienie": "na objednávku",
    "zamówienie": "objednávka",
    "zamowienie": "objednávka",
    "zamówienia": "objednávky",
    "zamowienia": "objednávky",
    "średnia ilość": "omezené množství",
    "srednia ilosc": "omezené množství",
    "na wyczerpaniu": "dochází",
    "do wymiarów": "na míru",
    "do wymiarow": "na míru",
    "do wymiaru": "na míru",
    "wymiarów": "rozměrů",
    "wymiarow": "rozměrů",
    "wymiaru": "rozměru",
    "wymiar": "rozměr",
    "odpływowy": "odtokový",
    "odplywowy": "odtokový",
    "bezbarwnego": "čirého",
    "bezbarwne": "čiré",
    "wahadłowe": "kyvné",
    "wahadlowe": "kyvné",
    "instrukcja": "návod",
    "jak szyba w oknie": "jako sklo v okně",
    "satynowe": "satinové",
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
    "po zakupie nie ma możliwości rezygnacji z zamówienia": "po objednání nelze od objednávky odstoupit",
    "inne wymiary wycena mailowa": "jiné rozměry — nabídka e-mailem",
    "listwy przypodłogowe": "podlahové lišty",
    "wózki jezdne": "pojezdové vozíky",
    "uchwytów montażowych": "montážních úchytů",
    "producent qubaglass": "",
    "producent quba glass": "",
    "jakub czyrnek qubaglass": "",
    "oslona przednia": "přední kryt",
    "osłona przednia": "přední kryt",
    "oslony przedniej": "předního krytu",
    "osłony przedniej": "předního krytu",
    "narozniki boczne oslony przedniej": "boční rohy předního krytu",
    "narożniki boczne osłony przedniej": "boční rohy předního krytu",
    "narozniki boczne": "boční rohy",
    "narożniki boczne": "boční rohy",
    "ogranicznik boční": "boční doraz",
    "ogranicznik boczny": "boční doraz",
    "cicha, plynna praca systemu": "tichý, plynulý chod systému",
    "ulepszone vozíky z prosta regulacja": "vylepšené vozíky s jednoduchou regulací",
    "ulepszone wózki z prostą regulacją": "vylepšené vozíky s jednoduchou regulací",
    "delikatny wyglad": "jemný vzhled",
    "delikatny wygląd": "jemný vzhled",
    "wysokiej jakosci aluminium": "hliník vysoké kvality",
    "wysokiej jakości aluminium": "hliník vysoké kvality",
    "szybki i snadný montáž": "rychlá a snadná montáž",
    "szybki i łatwy montaż": "rychlá a snadná montáž",
    "o dlugosci": "o délce",
    "o długości": "o délce",
    "dlugosci": "délky",
    "długości": "délky",
    "sklo, ktore stosujemy przy folii bílé to optiwhite": "sklo, které používáme s bílou fólií, je Optiwhite",
    "ktore stosujemy przy folii": "které používáme s fólií",
    "oddaje idealna biel, bez zielonkawej poswiaty": "podává ideální bílou bez nazelenalého nádechu",
    "zielonkawej poswiaty": "nazelenalého nádechu",
    "idealna biel": "ideální bílou",
    "przy folii bílé": "s bílou fólií",
    "przy folii": "s fólií",
    "stosujemy": "používáme",
    "elemencie": "prvku",
    "matowej": "matné",
    "przyleganie": "přilnutí",
    "aluminiowej": "hliníkové",
    "wykonczeniu": "provedení",
    "nastepnie": "poté",
    "następnie": "poté",
    "zastosowaniu": "použití",
    "zalaminowanego": "zalaminovaného",
    "podaja": "udávají",
    "podają": "udávají",
    "wybranych": "vybraných",
    "wykonac": "vyrobit",
    "wykonać": "vyrobit",
    "wykonane": "vyrobené",
    "mozliwe": "možné",
    "możliwe": "možné",
    "wykonany": "vyrobený",
    "opcje": "varianty",
    "opcja": "varianta",
    "mozliwosci": "možnosti",
    "możliwości": "možnosti",
    "fragmenty": "úlomky",
    "wykorzystanie": "využití",
    "calkowitego": "úplného",
    "całkowitego": "úplného",
    "licowania": "líčování",
    "zamontowania": "montáže",
    "cichego": "tichého",
    "regulowanemu": "regulovanému",
    "uchwytami": "úchyty",
    "zastosowac": "použít",
    "zastosować": "použít",
    "instrukcje": "návody",
    "elektromechaniczny": "elektromechanický",
    "wypadnieciem": "vypadnutím",
    "widocznymi": "viditelnými",
    "zwiazana": "spojená",
    "związana": "spojená",
    "podtrzymuje": "podpírá",
    "skladania": "skládání",
    "składania": "skládání",
    "bezpieczne": "bezpečné",
    "regulacja": "regulace",
    " sie ": " se ",
    " sie.": " se.",
    " sie,": " se,",
    " sie)": " se)",
    " sie-": " se-",
"grubosci": "tloušťky",
    "grubości": "tloušťky",
    "mozemy": "můžeme",
    "możemy": "můžeme",
    "piekna biel": "krásnou bílou",
    "piękna biel": "krásnou bílou",
    "piekna": "krásná",
    "piękna": "krásná",
    "uzyskujemy": "získáváme",
    "folii": "fólie",
    "zielonkawej": "nazelenalé",
    "poswiaty": "nádechu",
    "poświaty": "nádechu",
    "qubaglass": "",
    "w lewo (patrzac od strony systemu)": "doleva (při pohledu od strany systému)",
    "w prawo (patrzac od strony systemu)": "doprava (při pohledu od strany systému)",
    "w lewo ( patrzac od strony systemu )": "doleva (při pohledu od strany systému)",
    "w prawo ( patrzac od strony systemu )": "doprava (při pohledu od strany systému)",
    "w lewo (patrzac od str. systemu )": "doleva (při pohledu od strany systému)",
    "w lewo (patrzac od str. systemu)": "doleva (při pohledu od strany systému)",
    "w prawo (patrzac od str. systemu)": "doprava (při pohledu od strany systému)",
    "w prawo (patrzac od strony systém )": "doprava (při pohledu od strany systému)",
    "w prawo (patrzac od strony systém)": "doprava (při pohledu od strany systému)",
    "od przodu (patrzac od strony systemu)": "zepředu (při pohledu od strany systému)",
    "od tylu (patrzac od strony systemu)": "zezadu (při pohledu od strany systému)",
    "od przodu ( patrzac od str.systemu)": "zepředu (při pohledu od strany systému)",
    "od tylu ( patrzac od str.systemu)": "zezadu (při pohledu od strany systému)",
    "patrzac od strony systemu": "při pohledu od strany systému",
    "patrzac od strony systém": "při pohledu od strany systému",
    "patrzac od str. systemu": "při pohledu od strany systému",
    "patrzac od str.systemu": "při pohledu od strany systému",
    "patrząc od strony systemu": "při pohledu od strany systému",
    "w lewo": "doleva",
    "w prawo": "doprava",
    "grafika od przodu, od tylu černé sklo": "grafika zepředu, zezadu černé sklo",
    "grafika od tylu, od przodu černé sklo": "grafika zezadu, zepředu černé sklo",
    "zrcadlo od przodu, od tylu černé sklo": "zrcadlo zepředu, zezadu černé sklo",
    "zrcadlo od tylu, od przodu černé sklo": "zrcadlo zezadu, zepředu černé sklo",
    "od przodu zrcadlo, od tylu černé sklo": "zepředu zrcadlo, zezadu černé sklo",
    "od tylu zrcadlo, od przodu černé sklo": "zezadu zrcadlo, zepředu černé sklo",
    "szorstka od strony systemu": "drsná od strany systému",
    "gladka od strony systemu": "hladká od strany systému",
    "gładka od strony systemu": "hladká od strany systému",
    "mat od przodu": "mat zepředu",
    "mat od tylu": "mat zezadu",
    "od przodu": "zepředu",
    "od tylu": "zezadu",
    "od tyłu": "zezadu",
    "opcja dodatkowa.": "Doplňková možnost.",
    "opcja dodatkowa": "doplňková možnost",
    "spowolnione zamykanie oraz otwieranie dveře.": "Zpomalené zavírání i otevírání dveří.",
    "spowolnione zamykanie oraz otwieranie drzwi.": "Zpomalené zavírání i otevírání dveří.",
    "spowolnione zamykanie oraz otwieranie dveře": "zpomalené zavírání i otevírání dveří",
    "spowolnione zamykanie oraz otwieranie drzwi": "zpomalené zavírání i otevírání dveří",
    "pomalé, plynulé dotažení křídla dveří do ustalonej pozycji spoczynkowej.": "Pomalé, plynulé dotažení křídla dveří do stanovené klidové polohy.",
    "pomalé, plynulé dotažení křídla dveří do ustalonej": "Pomalé, plynulé dotažení křídla dveří do stanovené",
    "pozycji spoczynkowej.": "klidové polohy.",
    "pozycji spoczynkowej": "klidové polohy",
    "ustalonej pozycji spoczynkowej": "stanovené klidové polohy",
    "ustalonej": "stanovené",
    "tichý dojezd lze úspěšně použít do dveře juz od 60 cm.": "Tichý dojezd lze úspěšně použít u dveří již od 60 cm.",
    "tichý dojezd lze úspěšně použít do dveře juz od 60 cm": "Tichý dojezd lze úspěšně použít u dveří již od 60 cm",
    "jde o univerzální pneumatický píst. tichý dojezd lze úspěšně použít do dveře juz od 60 cm.": "Jde o univerzální pneumatický píst. Tichý dojezd lze úspěšně použít u dveří již od 60 cm.",
    "jde o univerzální pneumatický píst.tichý dojezd lze úspěšně použít do dveře juz od 60 cm.": "Jde o univerzální pneumatický píst. Tichý dojezd lze úspěšně použít u dveří již od 60 cm.",
    "juz od": "již od",
    "do dveře juz": "u dveří již",
    "4 úchyty do prowadnicy": "4 úchyty na vodicí lištu",
    "8 úchyty do prowadnicy": "8 úchytů na vodicí lištu",
    "úchyty do prowadnicy": "úchyty na vodicí lištu",
    "2 hamulce do wozkow": "2 dorazy / brzdy na vozíky",
    "4 hamulce do wozkow": "4 dorazy / brzdy na vozíky",
    "hamulce do wozkow": "dorazy / brzdy na vozíky",
    "2x prowadnik dolny": "2× spodní vodítko",
    "prowadnik dolny": "spodní vodítko",
    "wozki, hamulce, prowadnik dolny": "vozíky, dorazy, spodní vodítko",
    "wozki": "vozíky",
    "wozkow": "vozíků",
    "wózków": "vozíků",
    "hamulce": "dorazy",
    "prowadnicy": "vodicí lišty",
    "typu mušle pozwalaja zsunac dveře praktycznie calkowicie.": "typu mušle umožňují zasunout dveře prakticky úplně.",
    "pozwalaja zsunac dveře do konca": "umožňují zasunout dveře až na doraz",
    "pozwalają zsunąć drzwi do końca": "umožňují zasunout dveře až na doraz",
    "pozwalaja zsunac": "umožňují zasunout",
    "zsunac dveře": "zasunout dveře",
    "do konca": "až na doraz",
    "praktycznie calkowicie": "prakticky úplně",
    "calkowicie": "úplně",
    "blaszka zaczepowa kolor satin": "protiplech barva satin",
    "blaszka zaczepowa": "protiplech",
    "kolor satin": "barva satin",
    "z zawiasami hydraulicznymi z regulacja zamykania": "s hydraulickými závěsy s regulací zavírání",
    "zawiasami hydraulicznymi": "hydraulickými závěsy",
    "zawias hydrauliczny z funkcja samozamykania": "hydraulický závěs s funkcí samozavírání",
    "z funkcja samozamykania": "s funkcí samozavírání",
    "regulacja zamykania": "regulace zavírání",
    "samozamykanie przy 85 st.": "samozavírání při 85°",
    "maksymalny kat dveře 95 st.": "maximální úhel dveří 95°",
    "maksymalny kąt drzwi 95 st.": "maximální úhel dveří 95°",
    "funkcja stop 90/0 st.": "funkce stop 90/0°",
    "kování wystepuja w kolorze srebrnym - satin, oraz czarnym.": "Kování je ve stříbrné barvě – satin a černé.",
    "wystepuja w kolorze srebrnym": "je ve stříbrné barvě",
    "oraz czarnym": "a černé",
    "wykonujemy inne rozměry - napisz do nas !": "jiné rozměry — nabídka e-mailem",
    "wykonujemy inne rozměry": "jiné rozměry vyrábíme",
    "napisz do nas": "napište nám",
    "w przypadku pekniecia skla vsg, zwiazana z nim folia podtrzymuje": "při prasknutí skla VSG fólie drží střepy pohromadě",
    "w przypadku pęknięcia szkła vsg, związana z nim folia podtrzymuje": "při prasknutí skla VSG fólie drží střepy pohromadě",
    "fragmenty skla (sklo ne rozsypuje sie), pozostaje w jednym kawalku z widocznymi peknieciami tzw. pajaczkiem": "střepy skla se nesypou — tabule zůstane v jednom kusu s viditelnými prasklinami (pavoučí síť)",
    "dveře sa calkowicie nieprzezierne": "dveře jsou zcela neprůhledné",
    "mozliwosc stosowania folii kolorowych, zdjec, wzorow w laminacie": "možnost použití barevných fólií, fotek a vzorů v laminátu",
    "możliwość stosowania folii kolorowych, zdjęć, wzorów w laminacie": "možnost použití barevných fólií, fotek a vzorů v laminátu",
    "zarowno systém jak i úchyt sa w satinovém provedení": "jak systém, tak úchyt jsou v satinovém provedení",
    "zarówno system jak i uchwyt są w satynowym wykończeniu": "jak systém, tak úchyt jsou v satinovém provedení",
    "latwy montáž bez koniecznosci wykonywania otworow pod pojezdové vozíky": "snadná montáž bez nutnosti vrtání otvorů pod pojezdové vozíky",
    "łatwy montaż bez konieczności wykonywania otworów pod wózki jezdne": "snadná montáž bez nutnosti vrtání otvorů pod pojezdové vozíky",
    "pewne i bezpieczne kotvení skla za pomoca jednoczesciowych zaciskow mocujacych": "jisté a bezpečné kotvení skla pomocí celistvých upínacích svěrek",
    "ozdobna - praktyczno- uzyteczna": "dekorativní a praktická",
    "ozdobna - praktyczno-uzyteczna": "dekorativní a praktická",
    "ozdobna": "dekorativní",
    "praktyczno- uzyteczna": "praktická",
    "praktyczno-uzyteczna": "praktická",
    "wykonywane pod rozměr klienta z grafika": "vyráběné na rozměr klienta s grafikou",
    "wykonywane pod rozměr klienta": "vyráběné na rozměr klienta",
    "z jednej strony sklo takie ma powierzchnie gladka jako sklo v okně": "Z jedné strany má sklo hladký povrch jako sklo v okně",
    "z drugiej strony powierzchnie szorstka. na odleglosc do 1 m widac zarysy": "Z druhé strany je povrch drsný. Na vzdálenost do 1 m jsou vidět obrysy",
    "oddalajac sie dalej ne widac juz nic.": "Při větším odstupu už není nic vidět.",
    "sie potisk szprosow - dzieki temu ne mozna go zdrapac - sklo jest gladka z dwoch stron.": "Potisk příček je vlepený — nelze ho seškrábnout; sklo je hladké z obou stran.",
    "pochylenie skla 2 stopnie - mozliwosc regulacji": "Sklon skla 2° — možnost regulace",
    "3x wspornik do daszku o wymiarze 180x120 cm oraz 200x120 cm": "3× konzola na stříšku o rozměru 180×120 cm a 200×120 cm",
    "wspornik do daszku": "konzola na stříšku",
    "o wymiarze": "o rozměru",
    "oraz": "a",
    "mozliwosc": "možnost",
    "możliwość": "možnost",
    "zdjec": "fotek",
    "zdjęć": "fotek",
    "wzorow": "vzorů",
    "wzorów": "vzorů",
    "laminacie": "laminátu",
    "laminacie !": "laminátu",
    "pekniecia": "prasknutí",
    "pęknięcia": "prasknutí",
    "peknieciami": "prasklinami",
    "kawalku": "kusu",
    "kawałku": "kusu",
    "rozsypuje sie": "se sype",
    "rozsypuje": "sype",
    "pozostaje": "zůstává",
    "pajaczkiem": "pavoučí sítí",
    "nieprzezierne": "neprůhledné",
    "całkowicie": "úplně",
    "dlugosc": "délka",
    "długość": "délka",
    "dostosowujemy": "přizpůsobujeme",
    "koniecznosci": "nutnosti",
    "konieczności": "nutnosti",
    "otworow": "otvorů",
    "otworów": "otvorů",
    "wykonania": "provedení",
    "wykonujemy": "vyrábíme",
    "wykonywane": "vyráběné",
    "maskownicy": "krytu",
    "zarowno": "jak",
    "zarówno": "jak",
    "latwy": "snadný",
    "łatwy": "snadný",
    "sa w": "jsou v",
    "są w": "jsou v",
    "za pomoca": "pomocí",
    "za pomocą": "pomocí",
    "jednoczesciowych": "celistvých",
    "zaciskow": "upínacích svěrek",
    "mocujacych": "upínacích",
    "folii kolorowych": "barevných fólií",
    "stosowania": "použití",
    "drzwiach": "dveřích",
    "szklanych": "skleněných",
    "hartowanego": "kaleného",
    "laminowanego": "laminovaného",
    "powierzchnie gladka": "hladký povrch",
    "powierzchnie szorstka": "drsný povrch",
    "gladka": "hladká",
    "gładka": "hladká",
    "szorstka": "drsná",
    "odleglosc": "vzdálenost",
    "widac": "jsou vidět",
    "widać": "jsou vidět",
    "zarysy": "obrysy",
    "oddalajac sie": "při větším odstupu",
    "dzieki temu": "díky tomu",
    "dzięki temu": "díky tomu",
    "mozna": "lze",
    "można": "lze",
    "zdrapac": "seškrábnout",
    "z dwoch stron": "z obou stran",
    "z dwóch stron": "z obou stran",
    "stopnie": "stupně",
    "daszku": "stříšky",
    "daszek": "stříška",
    "zadaszenie": "stříška",
    "balustrada": "zábradlí",
    "drzwi": "dveře",
    "szklane": "skleněné",
}
_PL_DIACRITICS = str.maketrans({
    "ą": "a", "ć": "c", "ę": "e", "ł": "l", "ń": "n",
    "ś": "s", "ź": "z", "ż": "z",  # ó shared with Czech — keep
    "Ą": "A", "Ć": "C", "Ę": "E", "Ł": "L", "Ń": "N",
    "Ś": "S", "Ź": "Z", "Ż": "Z",
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


_OPTION_PATTERNS: list[tuple[re.Pattern[str], str]] | None = None


def _option_patterns() -> list[tuple[re.Pattern[str], str]]:
    """Compile OPTION_WORD_MAP once (longest keys first, with folded variants)."""
    global _OPTION_PATTERNS
    if _OPTION_PATTERNS is not None:
        return _OPTION_PATTERNS
    items = sorted(OPTION_WORD_MAP.items(), key=lambda kv: len(kv[0]), reverse=True)
    compiled: list[tuple[re.Pattern[str], str]] = []
    seen: set[str] = set()
    for pl, cs in items:
        if not pl:
            continue
        for variant in (pl, fold_pl(pl)):
            key = variant.lower()
            if not variant or key in seen:
                continue
            seen.add(key)
            compiled.append(
                (re.compile(rf"(?<!\w){re.escape(variant)}(?!\w)", re.IGNORECASE), cs)
            )
    _OPTION_PATTERNS = compiled
    return compiled


def translate_text(text: str) -> str:
    """Translate option labels / description fragments to Czech."""
    if not text:
        return ""
    result = text.strip()
    for pattern, cs in _option_patterns():
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
    result = re.sub(r"\.\s+prosím\b", ". Prosím", result)
    # Exact-only yes/no (never replace mid-sentence Czech „tak“)
    low = result.strip().lower().rstrip(".")
    if low == "nie":
        return "Ne"
    if low == "tak":
        return "Ano"
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
