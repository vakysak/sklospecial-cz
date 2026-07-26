'use strict';

const path = require('path');
const express = require('express');
const multer = require('multer');
const { isEmail, isPhoneCzSk, isDimMm } = require('../middleware/validate');
const { insertLead, updateLeadPhotos } = require('../services/database');
const { saveLeadPhotos, maxFileBytes, maxFiles, uploadRoot } = require('../services/storage');
const { sendLeadToFirm, sendConfirmationToClient } = require('../services/mailer');
const { findBySlug } = require('../services/katalog');

const router = express.Router();

const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: maxFileBytes(),
    files: maxFiles(),
  },
});

const ALLOWED_POUZITI = new Set(['byt_dum', 'koupelna', 'kancelar']);
const ALLOWED_MONTAZ = new Set(['s_montazi', 'bez_montaze']);

function numList(raw) {
  if (Array.isArray(raw)) return raw.map(Number);
  if (typeof raw === 'string') {
    try {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed)) return parsed.map(Number);
    } catch {
      return raw.split(',').map((s) => Number(s.trim()));
    }
  }
  return [];
}

async function validatePayload(body) {
  const errors = [];

  const typ = await findBySlug('sklo_katalog_typy', body.typ_dveri);
  const vzor = await findBySlug('sklo_katalog_vzory', body.typ_skla || body.vzor);
  const kovani = await findBySlug('sklo_katalog_kovani', body.kovani);

  if (!typ) errors.push('Neplatný typ dveří');
  if (!ALLOWED_POUZITI.has(body.pouziti)) errors.push('Neplatné použití');
  if (!vzor) errors.push('Neplatný vzor skla');
  if (!kovani) errors.push('Neplatné kování / lišta');
  if (!ALLOWED_MONTAZ.has(body.montaz)) errors.push('Neplatná montáž');

  const sirka = numList(body.sirka || body['sirka[]']);
  const vyska = numList(body.vyska || body['vyska[]']);
  const hloubka = Number(body.hloubka);

  if (sirka.length !== 3 || !sirka.every(isDimMm)) {
    errors.push('Šířka: 3 hodnoty v rozsahu 300–3500 mm');
  }
  if (vyska.length !== 3 || !vyska.every(isDimMm)) {
    errors.push('Výška: 3 hodnoty v rozsahu 300–3500 mm');
  }
  if (!Number.isFinite(hloubka) || hloubka < 20 || hloubka > 800) {
    errors.push('Hloubka stěny musí být číslo (typicky 20–800 mm)');
  }

  if (!body.jmeno || String(body.jmeno).trim().length < 2) errors.push('Chybí jméno');
  if (!isPhoneCzSk(body.telefon)) errors.push('Neplatný telefon (CZ/SK)');
  if (!isEmail(body.email)) errors.push('Neplatný e-mail');
  if (!body.mesto || String(body.mesto).trim().length < 2) errors.push('Chybí město / PSČ');

  if (errors.length) {
    const err = new Error(errors.join('; '));
    err.status = 400;
    throw err;
  }

  return {
    typ_dveri: typ.slug,
    pouziti: body.pouziti,
    sirka_min: Math.min(...sirka),
    sirka_max: Math.max(...sirka),
    vyska_min: Math.min(...vyska),
    vyska_max: Math.max(...vyska),
    hloubka,
    typ_skla: vzor.slug,
    kovani: kovani.slug,
    vzor_id: vzor.id,
    kovani_id: kovani.id,
    montaz: body.montaz,
    jmeno: String(body.jmeno).trim(),
    telefon: String(body.telefon).trim(),
    email: String(body.email).trim().toLowerCase(),
    mesto: String(body.mesto).trim(),
    poznamka: body.poznamka ? String(body.poznamka).trim() : '',
    fotky: [],
    stav: 'novy',
  };
}

router.post('/odeslat', upload.array('fotky', maxFiles()), async (req, res, next) => {
  try {
    const lead = await validatePayload(req.body || {});
    const id = await insertLead(lead);
    lead.id = id;

    const fotky = await saveLeadPhotos(req.files || [], id);
    await updateLeadPhotos(id, fotky);
    lead.fotky = fotky;

    const attachments = fotky.map((rel) => ({
      filename: path.basename(rel),
      path: path.join(uploadRoot(), rel),
    }));

    const mailErrors = [];
    try {
      await sendLeadToFirm(lead, attachments);
    } catch (err) {
      mailErrors.push(`firma: ${err.message}`);
    }
    try {
      await sendConfirmationToClient(lead);
    } catch (err) {
      mailErrors.push(`klient: ${err.message}`);
    }

    res.json({
      success: true,
      id: String(id),
      mail_warnings: mailErrors.length ? mailErrors : undefined,
    });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
