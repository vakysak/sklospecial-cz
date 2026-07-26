'use strict';

/**
 * Schema Fáze 1 — leads + katalog (vzory skla, kování/lišty, typy dveří)
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
  vzor_id       INT NULL,
  kovani_id     INT NULL,
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

const CREATE_SKLO_KATALOG_TYPY = `
CREATE TABLE IF NOT EXISTS sklo_katalog_typy (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(50) NOT NULL UNIQUE,
  nazev       VARCHAR(100) NOT NULL,
  popis       VARCHAR(255) NULL,
  image_url   VARCHAR(500) NULL,
  thumb_class VARCHAR(50) NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  aktivni     TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
`;

const CREATE_SKLO_KATALOG_VZORY = `
CREATE TABLE IF NOT EXISTS sklo_katalog_vzory (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  slug         VARCHAR(50) NOT NULL UNIQUE,
  nazev        VARCHAR(100) NOT NULL,
  popis        VARCHAR(255) NULL,
  image_url    VARCHAR(500) NULL,
  preview_url  VARCHAR(500) NULL,
  css_class    VARCHAR(50) NULL,
  sort_order   INT NOT NULL DEFAULT 0,
  aktivni      TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
`;

const CREATE_SKLO_KATALOG_KOVANI = `
CREATE TABLE IF NOT EXISTS sklo_katalog_kovani (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  slug         VARCHAR(50) NOT NULL UNIQUE,
  nazev        VARCHAR(100) NOT NULL,
  popis        VARCHAR(255) NULL,
  color_hex    VARCHAR(7) NULL,
  image_url    VARCHAR(500) NULL,
  preview_url  VARCHAR(500) NULL,
  sort_order   INT NOT NULL DEFAULT 0,
  aktivni      TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
`;

/** Add columns if upgrading older DB */
const ALTER_LEADS = [
  `ALTER TABLE sklo_leads ADD COLUMN vzor_id INT NULL`,
  `ALTER TABLE sklo_leads ADD COLUMN kovani_id INT NULL`,
];

module.exports = {
  CREATE_SKLO_LEADS,
  CREATE_SKLO_KATALOG_TYPY,
  CREATE_SKLO_KATALOG_VZORY,
  CREATE_SKLO_KATALOG_KOVANI,
  ALTER_LEADS,
};
