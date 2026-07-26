'use strict';

const mysql = require('mysql2/promise');

let pool;

function requiredEnv(name) {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Chybí env proměnná ${name}`);
  }
  return value;
}

function getPool() {
  if (pool) return pool;

  pool = mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    port: Number(process.env.DB_PORT || 3306),
    user: requiredEnv('DB_USER'),
    password: requiredEnv('DB_PASSWORD'),
    database: requiredEnv('DB_NAME'),
    waitForConnections: true,
    connectionLimit: 10,
    namedPlaceholders: true,
    timezone: 'Z',
  });

  return pool;
}

async function ping() {
  const [rows] = await getPool().query('SELECT 1 AS ok');
  return rows[0]?.ok === 1;
}

/**
 * @param {object} lead
 * @returns {Promise<number>} insert id
 */
async function insertLead(lead) {
  const sql = `
    INSERT INTO sklo_leads (
      typ_dveri, pouziti,
      sirka_min, sirka_max, vyska_min, vyska_max, hloubka,
      typ_skla, kovani, vzor_id, kovani_id, montaz,
      jmeno, telefon, email, mesto, poznamka,
      fotky, stav
    ) VALUES (
      :typ_dveri, :pouziti,
      :sirka_min, :sirka_max, :vyska_min, :vyska_max, :hloubka,
      :typ_skla, :kovani, :vzor_id, :kovani_id, :montaz,
      :jmeno, :telefon, :email, :mesto, :poznamka,
      :fotky, :stav
    )
  `;

  const [result] = await getPool().execute(sql, {
    typ_dveri: lead.typ_dveri,
    pouziti: lead.pouziti,
    sirka_min: lead.sirka_min,
    sirka_max: lead.sirka_max,
    vyska_min: lead.vyska_min,
    vyska_max: lead.vyska_max,
    hloubka: lead.hloubka,
    typ_skla: lead.typ_skla,
    kovani: lead.kovani,
    vzor_id: lead.vzor_id || null,
    kovani_id: lead.kovani_id || null,
    montaz: lead.montaz,
    jmeno: lead.jmeno,
    telefon: lead.telefon,
    email: lead.email,
    mesto: lead.mesto,
    poznamka: lead.poznamka || null,
    fotky: JSON.stringify(lead.fotky || []),
    stav: lead.stav || 'novy',
  });

  return result.insertId;
}

async function updateLeadPhotos(id, fotky) {
  await getPool().execute(
    'UPDATE sklo_leads SET fotky = :fotky WHERE id = :id',
    { id, fotky: JSON.stringify(fotky) }
  );
}

async function getLeadById(id) {
  const [rows] = await getPool().execute(
    'SELECT * FROM sklo_leads WHERE id = :id LIMIT 1',
    { id }
  );
  return rows[0] || null;
}

module.exports = {
  getPool,
  ping,
  insertLead,
  updateLeadPhotos,
  getLeadById,
};
