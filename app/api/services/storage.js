'use strict';

const fs = require('fs/promises');
const path = require('path');
const crypto = require('crypto');

const ALLOWED_MIME = new Set(['image/jpeg', 'image/png', 'image/webp']);
const ALLOWED_EXT = new Set(['.jpg', '.jpeg', '.png', '.webp']);

function uploadRoot() {
  return process.env.UPLOAD_DIR || path.join(__dirname, '../../../uploads');
}

function maxFileBytes() {
  return Number(process.env.MAX_FILE_SIZE_MB || 5) * 1024 * 1024;
}

function maxFiles() {
  return Number(process.env.MAX_FILES_PER_LEAD || 8);
}

function assertImageFile(file) {
  const ext = path.extname(file.originalname || '').toLowerCase();
  const mime = (file.mimetype || '').toLowerCase();

  if (!ALLOWED_MIME.has(mime)) {
    const err = new Error(`Nepovolený typ souboru: ${mime || 'unknown'}`);
    err.status = 400;
    throw err;
  }
  if (!ALLOWED_EXT.has(ext) && ext !== '') {
    // allow missing ext if mime is ok; still rename with mime map
  }
  if (file.size > maxFileBytes()) {
    const err = new Error(`Soubor je větší než ${process.env.MAX_FILE_SIZE_MB || 5} MB`);
    err.status = 400;
    throw err;
  }
}

function extFromMime(mime) {
  if (mime === 'image/png') return '.png';
  if (mime === 'image/webp') return '.webp';
  return '.jpg';
}

/**
 * Save lead photos under uploads/poptavky/YYYY-MM-DD/{leadId}/
 * @returns {Promise<string[]>} relative paths from upload root
 */
async function saveLeadPhotos(files, leadId) {
  if (!Array.isArray(files) || files.length === 0) {
    const err = new Error('Nahraj aspoň 2 fotky');
    err.status = 400;
    throw err;
  }
  if (files.length < 2) {
    const err = new Error('Nahraj aspoň 2 fotky');
    err.status = 400;
    throw err;
  }
  if (files.length > maxFiles()) {
    const err = new Error(`Maximálně ${maxFiles()} fotek`);
    err.status = 400;
    throw err;
  }

  for (const file of files) assertImageFile(file);

  const day = new Date().toISOString().slice(0, 10);
  const relDir = path.join('poptavky', day, String(leadId));
  const absDir = path.join(uploadRoot(), relDir);
  await fs.mkdir(absDir, { recursive: true });

  const saved = [];
  for (const file of files) {
    const ext = extFromMime(file.mimetype);
    const name = `${crypto.randomUUID()}${ext}`;
    const absPath = path.join(absDir, name);
    await fs.writeFile(absPath, file.buffer);
    saved.push(path.join(relDir, name).split(path.sep).join('/'));
  }

  return saved;
}

module.exports = {
  uploadRoot,
  maxFileBytes,
  maxFiles,
  assertImageFile,
  saveLeadPhotos,
  ALLOWED_MIME,
};
