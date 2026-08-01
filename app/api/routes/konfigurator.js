'use strict';

const path = require('path');
const express = require('express');
const multer = require('multer');
const { isEmail, isPhoneCzSk, isDimMm } = require('../middleware/validate');
const { insertLead, updateLeadPhotos } = require('../services/database');
const { saveLeadPhotos, maxFileBytes, maxFiles, uploadRoot } = require('../services/storage');
const { sendLeadToFirm, sendConfirmationToClient } = require('../services/mailer');
const { findTyp, getProdukt, productExists, computePrice } = require('../services/produkty');

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

function parseOptionsSelected(raw) {
  if (Array.isArray(raw)) return raw;
  if (typeof raw === 'string' && raw.trim()) {
    try {
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }
  return [];
}

async function validatePayload(body) {
  const errors = [];

  const typSlug = String(body.typ_dveri || '').trim();
  const typ = findTyp(typSlug);
  if (!typ) errors.push('Neplatný typ dveří');

  const needsAdvice =
    body.needs_advice === true ||
    body.needs_advice === '1' ||
    body.needs_advice === 'true' ||
    typSlug === 'nevim';

  let product = null;
  const productCode = body.product_code ? String(body.product_code).trim() : '';
  if (productCode) {
    if (!productExists(productCode)) {
      errors.push('Neplatný kód produktu');
    } else {
      product = getProdukt(productCode);
    }
  } else if (!needsAdvice) {
    errors.push('Vyber produkt z katalogu, nebo zvol „potřebuju poradit“');
  }

  if (!ALLOWED_POUZITI.has(body.pouziti)) errors.push('Neplatné použití');
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

  const gdprRaw = body.gdpr_souhlas;
  const gdprOk =
    gdprRaw === true ||
    gdprRaw === 1 ||
    gdprRaw === '1' ||
    (typeof gdprRaw === 'string' && ['ano', 'true', 'on', 'yes'].includes(gdprRaw.toLowerCase()));
  if (!gdprOk) errors.push('Je nutný souhlas se zpracováním osobních údajů');

  const optionsRaw = parseOptionsSelected(body.options_selected);
  let priceInfo = { price_base: 0, price_surcharge: 0, price_total: 0, options_selected: [] };
  if (product) {
    priceInfo = computePrice(product, optionsRaw);
    const clientTotal = Number(body.price_total);
    if (Number.isFinite(clientTotal) && Math.abs(clientTotal - priceInfo.price_total) > 2) {
      // Prefer server-side total; ignore client drift beyond rounding
    }
  } else if (Number.isFinite(Number(body.price_total))) {
    priceInfo.price_total = Math.round(Number(body.price_total));
  }

  if (errors.length) {
    const err = new Error(errors.join('; '));
    err.status = 400;
    throw err;
  }

  const optionsSummary = priceInfo.options_selected
    .map((o) => `${o.label}: ${o.choice}${o.surcharge_czk ? ` (+${o.surcharge_czk} Kč)` : ''}`)
    .join(' | ');

  return {
    typ_dveri: typ.slug,
    pouziti: body.pouziti,
    sirka_min: Math.min(...sirka),
    sirka_max: Math.max(...sirka),
    vyska_min: Math.min(...vyska),
    vyska_max: Math.max(...vyska),
    hloubka,
    typ_skla: optionsSummary ? 'katalog' : needsAdvice ? 'nevim' : 'katalog',
    kovani: product ? 'katalog' : 'nevim',
    vzor_id: null,
    kovani_id: null,
    product_code: product ? product.code : null,
    product_name: product
      ? product.name
      : body.product_name
        ? String(body.product_name).trim()
        : needsAdvice
          ? 'Potřebuje poradit'
          : null,
    options_selected: priceInfo.options_selected,
    price_total: priceInfo.price_total || null,
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
      product_code: lead.product_code || null,
      price_total: lead.price_total || null,
      mail_warnings: mailErrors.length ? mailErrors : undefined,
    });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
