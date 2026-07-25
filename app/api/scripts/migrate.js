'use strict';

const path = require('path');
const dotenv = require('dotenv');

dotenv.config({ path: path.join(__dirname, '../../../.env') });
dotenv.config({ path: path.join(__dirname, '../../.env') });

const { getPool } = require('../services/database');
const { CREATE_SKLO_LEADS } = require('../sql/schema');

async function main() {
  const pool = getPool();
  await pool.query(CREATE_SKLO_LEADS);
  const [rows] = await pool.query("SHOW TABLES LIKE 'sklo_leads'");
  console.log('OK: sklo_leads', rows.length ? 'exists' : 'MISSING');
  await pool.end();
}

main().catch((err) => {
  console.error('migrate failed:', err.message);
  process.exit(1);
});
