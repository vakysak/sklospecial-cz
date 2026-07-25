'use strict';

const express = require('express');
const router = express.Router();

/**
 * POST /api/konfigurator/odeslat
 * Krok 1: stub. Plná logika (DB + mail + fotky) v dalších krocích.
 */
router.post('/odeslat', async (_req, res) => {
  res.status(501).json({
    success: false,
    error: 'Konfigurátor ještě není zapojený — přijde po DB, uploadu a maileru.',
  });
});

module.exports = router;
