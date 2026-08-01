'use strict';

const fs = require('fs');
const path = require('path');

const DATA_DIR = path.join(__dirname, '../../data');
const KONFIG_PATH = path.join(DATA_DIR, 'katalog-konfigurator.json');
const FULL_PATH = path.join(DATA_DIR, 'produkty.json');

let konfigCache = null;
let fullCache = null;
let konfigMtime = 0;
let fullMtime = 0;

/** Polish leftovers → Czech (longest phrases first). */
const PUBLIC_TEXT_REPLACEMENTS = [
  [/představujeme\s+nowe\s+skleněné\s+dveře\s+na\s+systemie\s+posuvném/gi, 'představujeme nové skleněné dveře na posuvném systému'],
  [/Po\s+czarnym\s+systemie\s+LOFT-ART\s+przyszla\s+kolej\s+na/gi, 'Po černém systému LOFT-ART přišel na řadu'],
  [/LOFT-ART\s+BIALYM/gi, 'LOFT-ART bílý'],
  [/jde\s+o\s+systém\s+w\s+minimalistycznym\s+stylu\s+industrialnym,\s+dodávající\s+idealnej\s+eleganckiej\s+lekkosci\s+I\s+poczucia\s+przestrzeni\s+w\s+kazdym\s+wnetrzu/gi, 'jde o systém v minimalistickém industriálním stylu, který dodává lehkost a pocit prostoru v každém interiéru'],
  [/systém\s+ten\s+pozwala\s+dzielic\s+przestrzen\s+bez\s+utraty\s+doswietlenia/gi, 'systém umožňuje dělit prostor bez ztráty světla'],
  [/Dveře\s+oferujemy\s+w\s+pieciu\s+rozmiarach/gi, 'Dveře nabízíme v pěti rozměrech'],
  [/Po\s+skreceniu\s+rámu\s+hliníkové\s+wraz\s+ze\s+szklem\s+uzyskamy\s+rozměry\s+calosciowe/gi, 'Po sešroubování hliníkového rámu se sklem získáte celkové rozměry'],
  [/délky\s+systemow/gi, 'délky systémů'],
  [/délka\s+systemu/gi, 'délka systému'],
  [/Oferujemy\s+skleněné\s+dveře/gi, 'Nabízíme skleněné dveře'],
  [/ideálně\s+se\s+hodí\s+wiec\s+do\s+pomieszczen\s+o\s+duzej\s+wilgotnosci/gi, 'ideálně se hodí do místností s vyšší vlhkostí'],
  [/Duzym\s+plusem/gi, 'Velkým plusem'],
  [/jest\s+rowniez\s+to,\s+ze\s+nadaje\s+sie\s+do\s+bardzo\s+duzych/gi, 'je také to, že se hodí i na velmi velké'],
  [/Jest\s+wiec\s+bardziej\s+wytrzymala\s+na\s+duze\s+obciazenia/gi, 'Je tedy odolnější vůči velkému zatížení'],
  [/wytrzymuje\s+ciezar\s+szerokich\s+i\s+ciezkich\s+tafli\s+skla/gi, 'unese váhu širokých a těžkých tabulí skla'],
  [/Dzieki\s+zastosowanej\s+uszczelce/gi, 'Díky použitému těsnění'],
  [/tlumienia\s+dzwiekow/gi, 'tlumení zvuků'],
  [/W\s+sklad\s+zárubně\s+hliníkové\s+wchodzi/gi, 'Součástí hliníkové zárubně je'],
  [/\bnowe\b/gi, 'nové'],
  [/\bsystemie\b/gi, 'systému'],
  [/\bminimalistycznym\b/gi, 'minimalistickém'],
  [/\bindustrialnym\b/gi, 'industriálním'],
  [/\beleganckiej\b/gi, 'elegantní'],
  [/\blekkosci\b/gi, 'lehkosti'],
  [/\bpoczucia\b/gi, 'pocitu'],
  [/\bprzestrzeni\b/gi, 'prostoru'],
  [/\bkazdym\b/gi, 'každém'],
  [/\bwnetrzu\b/gi, 'interiéru'],
  [/\bczarnym\b/gi, 'černém'],
  [/\bprzyszla\b/gi, 'přišla'],
  [/\bOferujemy\b/gi, 'Nabízíme'],
  [/\boferujemy\b/gi, 'nabízíme'],
  [/\bprzedstawiamy\b/gi, 'představujeme'],
  [/\bpomieszczen\b/gi, 'místností'],
  [/\bwilgotnosci\b/gi, 'vlhkosti'],
  [/\bwiec\b/gi, 'tedy'],
  [/\browniez\b/gi, 'také'],
  [/\bbardzo\b/gi, 'velmi'],
  [/\bduzych\b/gi, 'velkých'],
  [/\bduzej\b/gi, 'velké'],
  [/\bduzym\b/gi, 'velkým'],
  [/\bduze\b/gi, 'velké'],
  [/\bwytrzymala\b/gi, 'odolná'],
  [/\bobciazenia\b/gi, 'zatížení'],
  [/\bciezar\b/gi, 'váhu'],
  [/\bszerokich\b/gi, 'širokých'],
  [/\bciezkich\b/gi, 'těžkých'],
  [/\btafli\b/gi, 'tabulí'],
  [/\buszczelce\b/gi, 'těsnění'],
  [/\buszczelka\b/gi, 'těsnění'],
  [/\bdzwiekow\b/gi, 'zvuků'],
  [/\bwchodzi\b/gi, 'patří'],
  [/\bsrebrnej\b/gi, 'stříbrné'],
  [/\bpionowy\b/gi, 'svislý'],
  [/\bpoziomy\b/gi, 'vodorovný'],
  [/\bhartowna\b/gi, 'kalené'],
  [/\bhydrauliczne\b/gi, 'hydraulické'],
  [/\belektrozaczepem\b/gi, 'elektrickým dorazem'],
  [/\bwycena\s+mailowa\b/gi, 'cena na e-mail'],
  [/\bjest\b/gi, 'je'],
  [/tabule skla staje sie latwiejsza w utrzymaniu czystosci/gi, 'tabule skla se snáze udržuje v čistotě'],
  [/system trubkovy charakteryzuje sie cicha praca i komfortem w uzytkowaniu/gi, 'trubkový systém se vyznačuje tichým chodem a pohodlným používáním'],
  [/systém vyrobený z nerezové oceli szczotkowanej, dopracowany, niezawodny/gi, 'systém z broušené nerezové oceli, propracovaný a spolehlivý'],
  [/wszystkie regulacje lze przeprowadzic po zamontowaniu/gi, 'veškeré seřízení lze provést po montáži'],
  [/Nasze dveře z grafikami powstaja dzieki drukarce UV ktora nanosi vzor na jednej z szyb/gi, 'Naše dveře s grafikou vznikají UV tiskem, který nanáší vzor na jednu ze skleněných tabulí'],
  [/Potisk je(?:st)? wewnatrz szyb,?dzieki czemu ne lze go seškrábnout, a sklo pieknie blyszczy z obu stron/gi, 'Potisk je uvnitř skla, takže jej nelze seškrábnout, a sklo se krásně leskne z obou stran'],
  [/Kolor grafiki moze sie roznic o kilka tonow/gi, 'Barva grafiky se může lišit o několik odstínů'],
  [/zalezne od ustawien monitora a druku/gi, 'v závislosti na nastavení monitoru a tisku'],
  [/regulace nahoře \/ dole na wozku/gi, 'nastavení nahoře / dole na vozíku'],
  [/Odpornosc na korozje EN 1670/gi, 'Odolnost proti korozi EN 1670'],
  [/Dostupné komfortowe funkcje/gi, 'Dostupné komfortní funkce'],
  [/drugie skrzydlo otwiera i zamyka sie automatycznie, jesli pierwsze skrzydlo je w ruchu/gi, 'druhé křídlo se automaticky otevírá a zavírá, pokud je první křídlo v pohybu'],
  [/\bwycena mailowa\b/gi, 'cena na vyžádání e-mailem'],
  [/\bwycena\b/gi, 'cena na vyžádání'],
  [/\bprowadznik\b/gi, 'vodítko'],
  [/\bprowadnik\b/gi, 'vodítko'],
  [/Latwa i mozliwa regulace wysokosci/gi, 'Snadné nastavení výšky'],
  [/Systém przetestowany na ponad 200 000 cykli/gi, 'Systém testovaný na více než 200 000 cyklů'],
  [/Minimalistyczne wymiary elementow systemu/gi, 'Minimalistické rozměry prvků systému'],
  [/\bPanstwo\b/gi, 'vy'],
  [/\bszyb\b/gi, 'skleněných tabulí'],
  [/\bjakosci\b/gi, 'kvality'],
  [/\bnowoczesnych\b/gi, 'moderních'],
  [/\bsie\b/gi, 'se'],
  [/\bpiaskowanym\b/gi, 'pískovaným'],
  [/\bwykonywany\b/gi, 'provedený'],
  [/\bszczotkowanej\b/gi, 'broušené'],
  [/\bdopracowany\b/gi, 'propracovaný'],
  [/\bniezawodny\b/gi, 'spolehlivý'],
  [/\bprosimy podac\b/gi, 'uveďte prosím'],
  [/\botrzymuja Panstwo\b/gi, 'obdržíte'],
  [/pokud zalezy panstwu na jakims konkretnym prvku w danej grafice, nalezy to uvést podczas skládání objednávky- w przeciwnym wypadku reklamacje ne beda uwzgledniane/gi, 'pokud vám záleží na konkrétním prvku grafiky, uveďte jej při objednávce; k pozdějším reklamacím tohoto výběru nelze přihlížet'],
  [/Stosowanie nadruku powoduje ozdobe wizualna dveře/gi, 'Potisk vytváří dekorativní vzhled dveří'],
  [/Kolorystyka ram aluminiowych wpisuje se w najnowsze trendy/gi, 'Barevnost hliníkových rámů odpovídá nejnovějším trendům'],
  [/\bdwoch\b/gi, 'dvou'],
  [/\bzestaw\b/gi, 'sada'],
  [/\brezygnacji\b/gi, 'zrušení'],
  [/\bpodzialu\b/gi, 'členění'],
  [/\bkomplecie\b/gi, 'sadě'],
  [/\bszyna\b/gi, 'kolejnice'],
  [
    /[ąęłńśźżćĄĘŁŃŚŹŻĆ]/g,
    (ch) =>
      ({
        ą: 'a',
        ć: 'c',
        ę: 'e',
        ł: 'l',
        ń: 'n',
        ś: 's',
        ź: 'z',
        ż: 'z',
        Ą: 'A',
        Ć: 'C',
        Ę: 'E',
        Ł: 'L',
        Ń: 'N',
        Ś: 'S',
        Ź: 'Z',
        Ż: 'Z',
      }[ch] || ch),
  ],
];

function sanitizePublicText(text) {
  if (!text) return '';
  let out = String(text);
  for (const [pattern, replacement] of PUBLIC_TEXT_REPLACEMENTS) {
    out = out.replace(pattern, replacement);
  }
  return out.replace(/\s{2,}/g, ' ').trim();
}

function sanitizeProduct(p) {
  if (!p || typeof p !== 'object') return p;
  const out = { ...p };
  if (out.name) out.name = sanitizePublicText(out.name);
  if (out.description_short) out.description_short = sanitizePublicText(out.description_short);
  if (out.description) out.description = sanitizePublicText(out.description);
  if (out.availability) out.availability = sanitizePublicText(out.availability);
  if (Array.isArray(out.includes)) {
    out.includes = out.includes.map((x) => sanitizePublicText(String(x))).filter(Boolean);
  }
  if (Array.isArray(out.options)) {
    out.options = out.options.map((g) => {
      if (!g || typeof g !== 'object') return g;
      return {
        ...g,
        label: sanitizePublicText(g.label || ''),
        choices: Array.isArray(g.choices)
          ? g.choices.map((c) =>
              c && typeof c === 'object' ? { ...c, name: sanitizePublicText(c.name || '') } : c
            )
          : g.choices,
      };
    });
  }
  return out;
}

function loadJson(filePath, getCache, setCache) {
  const stat = fs.statSync(filePath);
  const cached = getCache();
  if (cached.data && cached.mtime === stat.mtimeMs) return cached.data;
  const data = JSON.parse(fs.readFileSync(filePath, 'utf8'));
  setCache(data, stat.mtimeMs);
  return data;
}

function loadKonfig() {
  return loadJson(
    KONFIG_PATH,
    () => ({ data: konfigCache, mtime: konfigMtime }),
    (data, mtime) => {
      konfigCache = data;
      konfigMtime = mtime;
    }
  );
}

function loadFull() {
  if (!fs.existsSync(FULL_PATH)) return loadKonfig();
  return loadJson(
    FULL_PATH,
    () => ({ data: fullCache, mtime: fullMtime }),
    (data, mtime) => {
      fullCache = data;
      fullMtime = mtime;
    }
  );
}

function listTypy() {
  return loadKonfig().typy || [];
}

function findTyp(slug) {
  return listTypy().find((t) => t.slug === slug) || null;
}

function allSlimProducts() {
  return Object.values(loadKonfig().products || {});
}

/**
 * @param {{ typ?: string, q?: string, section?: string }} filters
 */
function listProdukty(filters = {}) {
  let items = allSlimProducts();
  const typ = filters.typ ? String(filters.typ).trim() : '';
  const section = filters.section ? String(filters.section).trim() : '';
  const q = filters.q ? String(filters.q).trim().toLowerCase() : '';

  if (typ && typ !== 'nevim') items = items.filter((p) => p.typ === typ);
  if (section) items = items.filter((p) => p.section === section);
  if (q) {
    items = items.filter((p) => {
      const hay = `${p.code} ${p.name} ${p.description_short || ''} ${p.section || ''}`.toLowerCase();
      return hay.includes(q);
    });
  }

  items.sort((a, b) => a.price - b.price || String(a.name).localeCompare(String(b.name), 'cs'));
  return items.map(sanitizeProduct);
}

function getProdukt(code) {
  if (!code) return null;
  const key = String(code).trim();
  const konfig = loadKonfig();
  const slim = konfig.products?.[key];
  const full = fs.existsSync(FULL_PATH) ? loadFull() : null;
  const rich = full?.products?.[key];

  if (!slim && !rich) return null;

  if (!slim && rich) {
    return sanitizeProduct({
      code: rich.code || key,
      name: rich.name || '',
      price: Number(rich.price) || 0,
      image: rich.image || '',
      section: rich.section || '',
      typ: null,
      options: rich.options || [],
      description_short: String(rich.description || '').slice(0, 220),
      description: rich.description || '',
      images: rich.images || [],
    });
  }

  return sanitizeProduct({
    ...slim,
    description: rich?.description || slim.description_short || '',
    images: rich?.images || (slim.image ? [slim.image] : []),
    includes: rich?.includes || [],
    shipping_days: rich?.shipping_days ?? null,
    availability: rich?.availability || '',
  });
}

function productExists(code) {
  if (!code) return false;
  const key = String(code).trim();
  if (loadKonfig().products?.[key]) return true;
  if (!fs.existsSync(FULL_PATH)) return false;
  return Boolean(loadFull().products?.[key]);
}

/**
 * @param {object} product
 * @param {Array<{ label: string, choice?: string, name?: string, surcharge_czk?: number }>} selected
 */
function computePrice(product, selected = []) {
  const base = Number(product?.price) || 0;
  let surcharge = 0;
  const normalized = [];
  for (const sel of selected || []) {
    const label = String(sel.label || '').trim();
    const choice = String(sel.choice || sel.name || '').trim();
    if (!label || !choice) continue;
    const group = (product.options || []).find((g) => g.label === label);
    const opt = group?.choices?.find((c) => c.name === choice);
    const amount = Number(opt?.surcharge_czk) || Number(sel.surcharge_czk) || 0;
    surcharge += amount;
    normalized.push({ label, choice, surcharge_czk: amount });
  }
  return {
    price_base: base,
    price_surcharge: surcharge,
    price_total: base + surcharge,
    options_selected: normalized,
  };
}

function getKatalogMeta() {
  const data = loadKonfig();
  return {
    generated: data.generated || null,
    count: data.count || 0,
    source: 'app/data/katalog-konfigurator.json',
  };
}

module.exports = {
  listTypy,
  findTyp,
  listProdukty,
  getProdukt,
  productExists,
  computePrice,
  getKatalogMeta,
  loadKonfig,
  sanitizePublicText,
};
