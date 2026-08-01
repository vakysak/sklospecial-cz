'use strict';

const fs = require('fs');
const path = require('path');

const KATALOG_PATH = path.join(__dirname, '../../data/katalog-konfigurator.json');
const PRODUKTY_PATH = path.join(__dirname, '../../data/produkty.json');

const TYP_ALIASES = {
  kyvne: 'otocne',
  kyvné: 'otocne',
  oteviraci: 'otevirane',
  otevírací: 'otevirane',
  posuvne: 'posuvne',
  posuvné: 'posuvne',
  'do-pouzdra': 'do-pouzdra',
  dopouzdra: 'do-pouzdra',
  celosklenene: 'celosklenene',
  celoskleněné: 'celosklenene',
  otocne: 'otocne',
  otočné: 'otocne',
  otevirane: 'otevirane',
  otevírané: 'otevirane',
  nevim: 'nevim',
};

function normalizeTyp(typ) {
  if (!typ) return '';
  const raw = String(typ).trim().toLowerCase();
  if (!raw) return '';
  const ascii = raw
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, '-');
  return TYP_ALIASES[raw] || TYP_ALIASES[ascii] || ascii;
}

function optionsSummary(options) {
  if (!Array.isArray(options) || !options.length) return '';
  return options
    .map((o) => {
      const choices = (o.choices || []).map((c) => c.name || c).filter(Boolean).join(', ');
      return choices ? `${o.label}: ${choices}` : o.label;
    })
    .join(' | ');
}

function choiceNames(options) {
  if (!Array.isArray(options)) return [];
  const names = [];
  for (const o of options) {
    for (const c of o.choices || []) {
      if (c && c.name) names.push(String(c.name));
    }
  }
  return names;
}

function loadCatalog() {
  const raw = JSON.parse(fs.readFileSync(KATALOG_PATH, 'utf8'));
  let fullProducts = {};
  try {
    if (fs.existsSync(PRODUKTY_PATH)) {
      const full = JSON.parse(fs.readFileSync(PRODUKTY_PATH, 'utf8'));
      fullProducts = full.products || {};
    }
  } catch {
    // optional enrichment only
  }

  const index = [];
  const byCode = new Map();

  for (const [code, p] of Object.entries(raw.products || {})) {
    const full = fullProducts[code] || {};
    const name = p.name || full.name || code;
    const price = Number(p.price ?? full.price) || 0;
    const image = p.image || full.image || '';
    const typ = p.typ || '';
    const section = p.section || full.section || '';
    const description_short =
      p.description_short ||
      (full.description ? String(full.description).slice(0, 280) : '') ||
      '';
    const options = p.options || full.options || [];
    const options_summary = optionsSummary(options);
    const keywords_blob = [
      name,
      description_short,
      typ,
      section,
      code,
      ...choiceNames(options),
      options_summary,
    ]
      .join(' ')
      .toLowerCase();

    const row = {
      code,
      name,
      price,
      image,
      typ,
      section,
      description_short,
      options_summary,
      keywords_blob,
      options,
    };
    index.push(row);
    byCode.set(code.toLowerCase(), row);
  }

  return { typy: raw.typy || [], index, byCode, generated: raw.generated, count: index.length };
}

const catalog = loadCatalog();

/**
 * WP relative category path; optional ?kod= for product deep-link (supported by theme).
 */
function categoryToUrlPath(typ, code) {
  const t = normalizeTyp(typ);
  let base = '/sklenene-dvere/';
  if (t === 'posuvne') base = '/sklenene-dvere/posuvne/';
  else if (t === 'do-pouzdra') base = '/sklenene-dvere/posuvne/do-pouzdra/';
  else if (t === 'otocne') base = '/sklenene-dvere/otocne/';
  else if (t === 'otevirane') base = '/sklenene-dvere/otevirane/';
  else if (t === 'celosklenene') base = '/sklenene-dvere/celosklenene/';

  if (code) {
    const sep = base.includes('?') ? '&' : '?';
    return `${base}${sep}kod=${encodeURIComponent(code)}`;
  }
  return base;
}

/**
 * Konfigurátor deep-link. Query typ/kod are reserved for future init
 * (konfigurator.js does not yet read them on load). Prefer WP paths for product cards.
 */
function getKonfiguratorLink({ typ, kod } = {}) {
  const params = new URLSearchParams();
  const t = normalizeTyp(typ);
  if (t && t !== 'nevim') params.set('typ', t);
  if (kod) params.set('kod', String(kod));
  const q = params.toString();
  return q ? `/public/konfigurator.html?${q}` : '/public/konfigurator.html';
}

function toSearchHit(row) {
  return {
    code: row.code,
    name: row.name,
    price: row.price,
    url_path: categoryToUrlPath(row.typ, row.code),
    image: row.image,
  };
}

function tokenizeKeywords(keywords) {
  if (!keywords) return [];
  const arr = Array.isArray(keywords) ? keywords : String(keywords).split(/[\s,;]+/);
  return arr
    .map((t) =>
      String(t)
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
    )
    .filter((t) => t.length >= 2);
}

/**
 * @param {{ typ?: string, keywords?: string|string[], max_price?: number, limit?: number }} opts
 */
function searchProducts({ typ, keywords, max_price, limit = 5 } = {}) {
  const lim = Math.min(Math.max(Number(limit) || 5, 1), 10);
  const t = normalizeTyp(typ);
  const tokens = tokenizeKeywords(keywords);
  const maxP = max_price != null && max_price !== '' ? Number(max_price) : null;

  let items = catalog.index;
  if (t && t !== 'nevim') {
    items = items.filter((p) => p.typ === t);
  }
  if (maxP != null && Number.isFinite(maxP)) {
    items = items.filter((p) => p.price > 0 && p.price <= maxP);
  }

  let scored;
  if (tokens.length) {
    scored = [];
    for (const p of items) {
      // normalize blob for diacritic-insensitive match
      const blob = p.keywords_blob
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
      let hits = 0;
      let all = true;
      for (const tok of tokens) {
        if (blob.includes(tok)) hits += 1;
        else all = false;
      }
      if (hits === 0) continue;
      scored.push({ p, hits, all, price: p.price });
    }
    scored.sort((a, b) => {
      if (a.all !== b.all) return a.all ? -1 : 1;
      if (b.hits !== a.hits) return b.hits - a.hits;
      return a.price - b.price;
    });
    return scored.slice(0, lim).map((s) => toSearchHit(s.p));
  }

  return [...items]
    .sort((a, b) => a.price - b.price || a.name.localeCompare(b.name, 'cs'))
    .slice(0, lim)
    .map(toSearchHit);
}

function getProduct(code) {
  if (!code) return null;
  const row = catalog.byCode.get(String(code).trim().toLowerCase());
  if (!row) return null;
  return {
    code: row.code,
    name: row.name,
    price: row.price,
    options_summary: row.options_summary,
    description_short: row.description_short,
    url_path: categoryToUrlPath(row.typ, row.code),
    image: row.image,
    typ: row.typ,
  };
}

function getProductRow(code) {
  if (!code) return null;
  return catalog.byCode.get(String(code).trim().toLowerCase()) || null;
}

const SECTION_KEYWORDS = {
  'design-lux': 'design lux',
  'ultra-slim': 'ultra slim',
  loft: 'loft',
  'trubkovy-system': 'trubkový systém',
  'do-pouzdra': 'pouzdro',
  otocne: 'kyvné otočné',
  otevirane: 'otevírané',
  pevna: 'pevná zárubeň',
  nastavitelna: 'nastavitelná zárubeň',
  hlinikova: 'hliníková',
  luxe: 'luxe',
  'rock-glass': 'rock glass',
};

const KEYWORD_STOP = new Set([
  'sklenene',
  'sklenené',
  'dvere',
  'dveře',
  'na',
  'miru',
  'míru',
  'a',
  's',
  'se',
  'pro',
  'od',
  'do',
  'ze',
  'z',
  'v',
  've',
  'the',
  'ks',
  'mm',
  'cm',
]);

/**
 * Build search keywords from a catalog row (name / section / short desc).
 */
function keywordsFromProduct(row) {
  if (!row) return '';
  const parts = [];
  if (row.section) {
    parts.push(SECTION_KEYWORDS[row.section] || String(row.section).replace(/-/g, ' '));
  }
  const nameTokens = String(row.name || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .split(/[^a-z0-9]+/)
    .filter((t) => t.length >= 3 && !KEYWORD_STOP.has(t));
  // Keep distinctive tokens (skip generic "sklenene/dvere" already filtered)
  for (const t of nameTokens.slice(0, 6)) {
    if (!parts.some((p) => p.includes(t))) parts.push(t);
  }
  const glassHints = ['matne', 'matné', 'leptane', 'leptané', 'cire', 'čiré', 'zrcadlo', 'satin'];
  const blob = `${row.name || ''} ${row.description_short || ''}`.toLowerCase();
  for (const h of glassHints) {
    if (blob.includes(h)) parts.push(h);
  }
  return parts.join(' ').trim();
}

/**
 * Find similar SklS products by reference code and/or typ+keywords.
 * No vector DB — keyword/typ search with self excluded.
 * @param {{ code?: string, typ?: string, keywords?: string|string[], exclude_code?: string, limit?: number }} opts
 */
function findSimilarProducts({ code, typ, keywords, exclude_code, limit = 5 } = {}) {
  const lim = Math.min(Math.max(Number(limit) || 5, 1), 5);
  const refCode = code ? String(code).trim() : '';
  const exclude = String(exclude_code || refCode || '')
    .trim()
    .toLowerCase();

  let refTyp = typ ? normalizeTyp(typ) : '';
  let refKeywords = keywords;

  if (refCode) {
    const row = getProductRow(refCode);
    if (!row) {
      return {
        ok: false,
        error: `Produkt ${refCode} nenalezen`,
        items: [],
        query: { code: refCode, typ: refTyp, keywords: '' },
      };
    }
    if (!refTyp || refTyp === 'nevim') refTyp = row.typ || '';
    if (!refKeywords) refKeywords = keywordsFromProduct(row);
  }

  const kwStr = Array.isArray(refKeywords)
    ? refKeywords.join(' ')
    : refKeywords
      ? String(refKeywords)
      : '';

  const seen = new Set();
  const out = [];

  const pushHits = (hits) => {
    for (const h of hits) {
      if (!h || !h.code) continue;
      const key = h.code.toLowerCase();
      if (exclude && key === exclude) continue;
      if (seen.has(key)) continue;
      seen.add(key);
      out.push(h);
      if (out.length >= lim) return true;
    }
    return false;
  };

  // 1) typ + keywords
  if (!pushHits(searchProducts({ typ: refTyp, keywords: kwStr, limit: 10 }))) {
    // 2) keywords only (relax typ)
    if (kwStr) pushHits(searchProducts({ keywords: kwStr, limit: 10 }));
  }
  // 3) typ only if still short
  if (out.length < lim && refTyp && refTyp !== 'nevim') {
    pushHits(searchProducts({ typ: refTyp, limit: 10 }));
  }

  return {
    ok: true,
    items: out.slice(0, lim),
    query: {
      code: refCode || undefined,
      typ: refTyp || undefined,
      keywords: kwStr || undefined,
      exclude: exclude || undefined,
    },
  };
}

const ADDON_LABEL_HINTS = [
  { keys: ['madlo', 'uchyt', 'úchyt', 'musle', 'mušle'], kind: 'madlo' },
  {
    keys: ['samozavirac', 'samozavírač', 'tichy', 'tichý', 'dojezd'],
    kind: 'samozavirac',
  },
  { keys: ['barva', 'kovani', 'kování', 'povrch'], kind: 'kovani' },
];

const SERVICE_ADDONS = [
  {
    kind: 'service',
    label: 'Doprava',
    note: 'Doprava na adresu — cenu dopočítáme podle lokality',
    surcharge_czk: null,
    sku: false,
  },
  {
    kind: 'service',
    label: 'Montáž',
    note: 'Montáž na místě — podle typu dveří a dostupnosti',
    surcharge_czk: null,
    sku: false,
  },
  {
    kind: 'service',
    label: 'Zaměření',
    note: 'Profesionální zaměření otvoru (doporučeno u atypů / nerovností)',
    surcharge_czk: null,
    sku: false,
  },
];

function normalizeLabel(s) {
  return String(s || '')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

function matchAddonKind(label) {
  const n = normalizeLabel(label);
  for (const hint of ADDON_LABEL_HINTS) {
    if (hint.keys.some((k) => n.includes(normalizeLabel(k)))) return hint.kind;
  }
  return null;
}

/**
 * Suggest 1–3 paid/upgradable options + always service addons (doprava/montáž/zaměření).
 * @param {string} code
 */
function recommendAddons(code) {
  const row = getProductRow(code);
  if (!row) {
    return {
      ok: false,
      error: `Produkt ${code} nenalezen`,
      suggestions: [],
      services: SERVICE_ADDONS,
    };
  }

  const suggestions = [];
  const options = Array.isArray(row.options) ? row.options : [];

  for (const group of options) {
    if (!group || group.type === 'text') continue;
    const kind = matchAddonKind(group.label);
    if (!kind) continue;
    const choices = (group.choices || []).filter((c) => c && c.name);
    if (!choices.length) continue;

    const paid = choices
      .filter((c) => Number(c.surcharge_czk) > 0)
      .sort((a, b) => Number(a.surcharge_czk) - Number(b.surcharge_czk));
    const free = choices.filter((c) => !Number(c.surcharge_czk));

    let pick = null;
    if (paid.length) {
      pick = paid[0];
    } else if (free.length > 1) {
      // offer a non-default variant when all free
      pick = free[1] || free[0];
    } else if (free.length === 1) {
      pick = free[0];
    }
    if (!pick) continue;

    const surcharge = Math.round(Number(pick.surcharge_czk) || 0);
    suggestions.push({
      kind,
      option_label: group.label,
      choice: pick.name,
      surcharge_czk: surcharge,
      price_note: surcharge > 0 ? `+ ${surcharge.toLocaleString('cs-CZ')} Kč` : 'bez příplatku',
      alternatives: choices.slice(0, 5).map((c) => ({
        name: c.name,
        surcharge_czk: Math.round(Number(c.surcharge_czk) || 0),
      })),
    });
  }

  // Prefer madlo / samozavírač / kování — max 3
  const priority = ['madlo', 'samozavirac', 'kovani'];
  suggestions.sort((a, b) => {
    const ia = priority.indexOf(a.kind);
    const ib = priority.indexOf(b.kind);
    return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
  });
  const top = suggestions.slice(0, 3);

  const text_lines = [
    ...top.map(
      (s) =>
        `${s.option_label}: ${s.choice}${s.surcharge_czk > 0 ? ` (${s.price_note})` : ''}`
    ),
    ...SERVICE_ADDONS.map((s) => `${s.label}: ${s.note}`),
  ];

  return {
    ok: true,
    code: row.code,
    name: row.name,
    base_price: row.price,
    suggestions: top,
    services: SERVICE_ADDONS,
    summary_cs: text_lines.join(' · '),
    text_for_poptavka: text_lines.join('; '),
  };
}

/**
 * Build /poptavka/ URL + sklo_order_draft-compatible payload for the chat widget.
 * @param {{ kod?: string, name?: string, price?: number, selected_summary?: string, addons_summary?: string, image?: string }} opts
 */
function createPoptavka({
  kod,
  name,
  price,
  selected_summary,
  addons_summary,
  image,
} = {}) {
  const code = kod ? String(kod).trim() : '';
  const fromCatalog = code ? getProduct(code) : null;
  const resolvedName =
    (name && String(name).trim()) ||
    (fromCatalog && fromCatalog.name) ||
    (code || 'Obecná poptávka');
  const resolvedPrice = (() => {
    if (price != null && price !== '' && Number.isFinite(Number(price))) {
      return Math.round(Number(price));
    }
    return fromCatalog ? Math.round(Number(fromCatalog.price) || 0) : 0;
  })();
  const resolvedImage =
    (image && String(image).trim()) || (fromCatalog && fromCatalog.image) || '';
  const summary = selected_summary ? String(selected_summary).trim().slice(0, 800) : '';
  const addons = addons_summary ? String(addons_summary).trim().slice(0, 600) : '';

  const selections = [];
  if (summary) selections.push({ label: 'Shrnutí', value: summary, surcharge: 0 });
  if (addons) selections.push({ label: 'Doplňky', value: addons, surcharge: 0 });

  const draft = {
    v: 1,
    code: code || (fromCatalog && fromCatalog.code) || '',
    name: resolvedName,
    image: resolvedImage,
    basePrice: resolvedPrice,
    surcharges: 0,
    unitTotal: resolvedPrice,
    qty: 1,
    selections,
    createdAt: new Date().toISOString(),
    source: 'chat',
  };
  if (!draft.code) {
    draft.general = true;
  }

  const params = new URLSearchParams();
  if (draft.code) params.set('kod', draft.code);
  if (draft.name) params.set('name', draft.name.slice(0, 120));
  if (resolvedPrice > 0) params.set('price', String(resolvedPrice));
  const summaryQ = [summary, addons].filter(Boolean).join(' | ').slice(0, 200);
  if (summaryQ) params.set('summary', summaryQ);

  const qs = params.toString();
  const relative = qs ? `/poptavka/?${qs}` : '/poptavka/';
  const base = (process.env.WP_PUBLIC_URL || '').replace(/\/$/, '');
  const url = base ? `${base}${relative}` : relative;

  return { ok: true, url, relative, poptavka_draft: draft, poptavka_url: url };
}

module.exports = {
  searchProducts,
  getProduct,
  getKonfiguratorLink,
  createPoptavka,
  recommendAddons,
  findSimilarProducts,
  keywordsFromProduct,
  categoryToUrlPath,
  normalizeTyp,
  // test helpers
  _catalogMeta: { count: catalog.count, generated: catalog.generated },
};
