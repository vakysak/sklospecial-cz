'use strict';

const path = require('path');
const dotenv = require('dotenv');

dotenv.config({ path: path.join(__dirname, '../../../.env') });
dotenv.config({ path: path.join(__dirname, '../../.env') });

const { getPool } = require('../services/database');
const {
  CREATE_SKLO_LEADS,
  CREATE_SKLO_KATALOG_TYPY,
  CREATE_SKLO_KATALOG_VZORY,
  CREATE_SKLO_KATALOG_KOVANI,
  ALTER_LEADS,
} = require('../sql/schema');
const { SEED_TYPY, SEED_VZORY, SEED_KOVANI } = require('../sql/seed-katalog');

async function ensureAlters(pool) {
  for (const sql of ALTER_LEADS) {
    try {
      await pool.query(sql);
    } catch (err) {
      // Duplicate column = already migrated
      if (!String(err.message).includes('Duplicate column')) {
        throw err;
      }
    }
  }
}

async function seedIfEmpty(pool, table, rows, columns) {
  const [countRows] = await pool.query(`SELECT COUNT(*) AS c FROM ${table}`);
  if (Number(countRows[0].c) > 0) {
    console.log(`skip seed ${table} (already has data)`);
    return;
  }
  for (const row of rows) {
    const cols = columns.filter((c) => row[c] !== undefined);
    const placeholders = cols.map((c) => `:${c}`).join(', ');
    await pool.execute(
      `INSERT INTO ${table} (${cols.join(', ')}) VALUES (${placeholders})`,
      Object.fromEntries(cols.map((c) => [c, row[c]]))
    );
  }
  console.log(`seeded ${table}: ${rows.length}`);
}

async function main() {
  const pool = getPool();

  await pool.query(CREATE_SKLO_LEADS);
  await pool.query(CREATE_SKLO_KATALOG_TYPY);
  await pool.query(CREATE_SKLO_KATALOG_VZORY);
  await pool.query(CREATE_SKLO_KATALOG_KOVANI);
  await ensureAlters(pool);

  await seedIfEmpty(pool, 'sklo_katalog_typy', SEED_TYPY, [
    'slug', 'nazev', 'popis', 'thumb_class', 'sort_order',
  ]);
  await seedIfEmpty(pool, 'sklo_katalog_vzory', SEED_VZORY, [
    'slug', 'nazev', 'popis', 'css_class', 'sort_order',
  ]);
  await seedIfEmpty(pool, 'sklo_katalog_kovani', SEED_KOVANI, [
    'slug', 'nazev', 'popis', 'color_hex', 'sort_order',
  ]);

  const tables = ['sklo_leads', 'sklo_katalog_typy', 'sklo_katalog_vzory', 'sklo_katalog_kovani'];
  for (const t of tables) {
    const [rows] = await pool.query(`SHOW TABLES LIKE '${t}'`);
    console.log('OK:', t, rows.length ? 'exists' : 'MISSING');
  }

  await pool.end();
}

main().catch((err) => {
  console.error('migrate failed:', err.message);
  process.exit(1);
});
