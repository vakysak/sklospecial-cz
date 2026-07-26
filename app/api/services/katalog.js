'use strict';

const { getPool } = require('./database');

async function listActive(table) {
  const [rows] = await getPool().query(
    `SELECT * FROM ${table} WHERE aktivni = 1 ORDER BY sort_order ASC, id ASC`
  );
  return rows;
}

async function getKatalog() {
  const [typy, vzory, kovani] = await Promise.all([
    listActive('sklo_katalog_typy'),
    listActive('sklo_katalog_vzory'),
    listActive('sklo_katalog_kovani'),
  ]);
  return { typy, vzory, kovani };
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
};
