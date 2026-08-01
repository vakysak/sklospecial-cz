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

/**
 * Build /poptavka/ URL + sklo_order_draft-compatible payload for the chat widget.
 * @param {{ kod?: string, name?: string, price?: number, selected_summary?: string, image?: string }} opts
 */
function createPoptavka({
  kod,
  name,
  price,
  selected_summary,
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

  const selections = summary
    ? [{ label: 'Shrnutí', value: summary, surcharge: 0 }]
    : [];

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
  if (summary) params.set('summary', summary.slice(0, 200));

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
  categoryToUrlPath,
  normalizeTyp,
  // test helpers
  _catalogMeta: { count: catalog.count, generated: catalog.generated },
};
