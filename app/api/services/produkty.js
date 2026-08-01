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
  return items;
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
    return {
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
    };
  }

  return {
    ...slim,
    description: rich?.description || slim.description_short || '',
    images: rich?.images || (slim.image ? [slim.image] : []),
    includes: rich?.includes || [],
    shipping_days: rich?.shipping_days ?? null,
    availability: rich?.availability || '',
  };
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
};
