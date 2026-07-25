'use strict';

const path = require('path');
const express = require('express');
const multer = require('multer');
const { isEmail, isPhoneCzSk, isDimMm } = require('../middleware/validate');
const { insertLead, updateLeadPhotos } = require('../services/database');
const { saveLeadPhotos, maxFileBytes, maxFiles, uploadRoot } = require('../services/storage');
const { sendLeadToFirm, sendConfirmationToClient } = require('../services/mailer');

const router = express.Router();

const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: maxFileBytes(),
    files: maxFiles(),
  },
});

const ALLOWED_TYP = new Set(['otocne', 'posuvne_stena', 'posuvne_pouzdro', 'dvoukridle']);
const ALLOWED_POUZITI = new Set(['byt_dum', 'koupelna', 'kancelar']);
const ALLOWED_SKLO = new Set(['cire', 'matne', 'dekor', 'nevim']);
const ALLOWED_KOVANI = new Set(['cerne', 'nerez', 'zlate', 'nevim']);
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

function validatePayload(body) {
  const errors = [];

  if (!ALLOWED_TYP.has(body.typ_dveri)) errors.push('Neplatný typ dveří');
  if (!ALLOWED_POUZITI.has(body.pouziti)) errors.push('Neplatné použití');
  if (!ALLOWED_SKLO.has(body.typ_skla)) errors.push('Neplatný typ skla');
  if (!ALLOWED_KOVANI.has(body.kovani)) errors.push('Neplatné kování');
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
    typ_dveri: body.typ_dveri,
    pouziti: body.pouziti,
    sirka_min: Math.min(...sirka),
    sirka_max: Math.max(...sirka),
    vyska_min: Math.min(...vyska),
    vyska_max: Math.max(...vyska),
    hloubka,
    typ_skla: body.typ_skla,
    kovani: body.kovani,
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
    const lead = validatePayload(req.body || {});
    const id = await insertLead(lead);
    lead.id = id;

    const fotky = await saveLeadPhotos(req.files || [], id);
    await updateLeadPhotos(id, fotky);
    lead.fotky = fotky;

    const attachments = fotky.map((rel) => ({
      filename: path.basename(rel),
      path: path.join(uploadRoot(), rel),
    }));

    // Mail je best-effort — lead už je uložený
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
