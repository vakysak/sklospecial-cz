'use strict';

/**
 * CREATE TABLE sklo_leads — Fáze 1
 * Spusť: npm run db:migrate (z složky app/, s nastaveným .env)
 */

const CREATE_SKLO_LEADS = `
CREATE TABLE IF NOT EXISTS sklo_leads (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  typ_dveri     VARCHAR(50),
  pouziti       VARCHAR(50),
  sirka_min     INT,
  sirka_max     INT,
  vyska_min     INT,
  vyska_max     INT,
  hloubka       INT,
  typ_skla      VARCHAR(50),
  kovani        VARCHAR(50),
  montaz        VARCHAR(20),
  jmeno         VARCHAR(100),
  telefon       VARCHAR(30),
  email         VARCHAR(150),
  mesto         VARCHAR(100),
  poznamka      TEXT,
  fotky         TEXT,
  stav          VARCHAR(30) DEFAULT 'novy',
  poznamka_int  TEXT,
  INDEX idx_created_at (created_at),
  INDEX idx_stav (stav),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
`;

module.exports = { CREATE_SKLO_LEADS };
