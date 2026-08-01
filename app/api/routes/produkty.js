'use strict';

const express = require('express');
const { listProdukty, getProdukt, listTypy, getKatalogMeta } = require('../services/produkty');

const router = express.Router();

/**
 * GET /api/produkty?typ=posuvne&q=matné&section=design-lux
 */
router.get('/', (req, res, next) => {
  try {
    const typ = req.query.typ ? String(req.query.typ) : '';
    const q = req.query.q ? String(req.query.q) : '';
    const section = req.query.section ? String(req.query.section) : '';
    const items = listProdukty({ typ, q, section }).map((p) => ({
      code: p.code,
      name: p.name,
      price: p.price,
      image: p.image,
      section: p.section,
      typ: p.typ,
      description_short: p.description_short,
      options_count: Array.isArray(p.options) ? p.options.length : 0,
    }));
    res.json({
      success: true,
      count: items.length,
      items,
      typy: listTypy(),
      meta: getKatalogMeta(),
    });
  } catch (err) {
    next(err);
  }
});

/**
 * GET /api/produkty/:code
 */
router.get('/:code', (req, res, next) => {
  try {
    const item = getProdukt(req.params.code);
    if (!item) {
      return res.status(404).json({ success: false, error: 'Produkt nenalezen' });
    }
    res.json({ success: true, item });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
