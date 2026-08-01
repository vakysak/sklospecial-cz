'use strict';

const { getPool } = require('./database');
const {
  listTypy,
  listProdukty,
  getKatalogMeta,
} = require('./produkty');

async function listActive(table) {
  const [rows] = await getPool().query(
    `SELECT * FROM ${table} WHERE aktivni = 1 ORDER BY sort_order ASC, id ASC`
  );
  return rows;
}

/**
 * GET /api/katalog — typy from real SklS door catalog + optional legacy vzory/kovani.
 */
async function getKatalog() {
  const typy = listTypy();
  let vzory = [];
  let kovani = [];
  try {
    [vzory, kovani] = await Promise.all([
      listActive('sklo_katalog_vzory'),
      listActive('sklo_katalog_kovani'),
    ]);
  } catch {
    // DB optional for typy — produkty.json is source of truth for product flow
  }
  return {
    typy,
    vzory,
    kovani,
    meta: getKatalogMeta(),
  };
}

async function findBySlug(table, slug) {
  const [rows] = await getPool().execute(
    `SELECT * FROM ${table} WHERE slug = :slug AND aktivni = 1 LIMIT 1`,
    { slug }
  );
  return rows[0] || null;
}

module.exports = {
  getKatalog,
  findBySlug,
  listTypy,
  listProdukty,
};
