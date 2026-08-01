'use strict';

const express = require('express');
const { checkDimensions } = require('../services/rozmery');

const router = express.Router();

/**
 * POST /api/rozmery/check
 * Body: { widths:[3], heights:[3] } or { sirka, vyska } or { pairs:[{w,h},…] }
 */
router.post('/check', (req, res) => {
  const result = checkDimensions(req.body || {});
  res.json({ success: true, ...result });
});

module.exports = router;
