'use strict';

const express = require('express');
const multer = require('multer');
const { maxFileBytes, maxFiles, saveLeadPhotos } = require('../services/storage');

const router = express.Router();

const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: maxFileBytes(),
    files: maxFiles(),
  },
});

/**
 * POST /api/upload
 * Dočasný endpoint: uloží fotky pod leadId (nebo "draft").
 * Hlavní tok jde přes /api/konfigurator/odeslat.
 */
router.post('/', upload.array('fotky', maxFiles()), async (req, res, next) => {
  try {
    const leadId = String(req.body.lead_id || 'draft');
    const paths = await saveLeadPhotos(req.files || [], leadId);
    res.json({ success: true, fotky: paths });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
