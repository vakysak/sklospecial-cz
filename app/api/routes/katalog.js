'use strict';

const express = require('express');
const { getKatalog, findBySlug } = require('../services/katalog');
const { listTypy, getKatalogMeta } = require('../services/produkty');

const router = express.Router();

/**
 * GET /api/katalog
 * Typy from SklS door catalog + legacy vzory/kovani (soft advise / fallback).
 */
router.get('/', async (_req, res, next) => {
  try {
    const data = await getKatalog();
    res.json({
      success: true,
      ...data,
      meta: {
        ...(data.meta || {}),
        note: 'typy = skleněné dveře z katalogu SklS; produkty přes GET /api/produkty?typ=',
      },
    });
  } catch (err) {
    next(err);
  }
});

router.get('/vzory', async (_req, res, next) => {
  try {
    const { vzory } = await getKatalog();
    res.json({ success: true, items: vzory });
  } catch (err) {
    next(err);
  }
});

router.get('/kovani', async (_req, res, next) => {
  try {
    const { kovani } = await getKatalog();
    res.json({ success: true, items: kovani });
  } catch (err) {
    next(err);
  }
});

router.get('/typy', async (_req, res, next) => {
  try {
    res.json({ success: true, items: listTypy(), meta: getKatalogMeta() });
  } catch (err) {
    next(err);
  }
});

router.get('/vzory/:slug', async (req, res, next) => {
  try {
    const item = await findBySlug('sklo_katalog_vzory', req.params.slug);
    if (!item) return res.status(404).json({ success: false, error: 'Vzor nenalezen' });
    res.json({ success: true, item });
  } catch (err) {
    next(err);
  }
});

router.get('/kovani/:slug', async (req, res, next) => {
  try {
    const item = await findBySlug('sklo_katalog_kovani', req.params.slug);
    if (!item) return res.status(404).json({ success: false, error: 'Kování nenalezeno' });
    res.json({ success: true, item });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
