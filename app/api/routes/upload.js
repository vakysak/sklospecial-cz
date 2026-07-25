'use strict';

const express = require('express');
const router = express.Router();

/**
 * POST /api/upload
 * Krok 1: stub. Multer + validace v kroku Upload.
 */
router.post('/', async (_req, res) => {
  res.status(501).json({
    success: false,
    error: 'Upload ještě není zapojený.',
  });
});

module.exports = router;
