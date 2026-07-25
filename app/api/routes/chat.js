'use strict';

const express = require('express');
const router = express.Router();

/**
 * POST /api/chat
 * Krok 1: stub. Plná OpenAI integrace v pozdějším kroku.
 */
router.post('/', async (req, res) => {
  const { messages, session_id: sessionId } = req.body || {};

  if (!sessionId || typeof sessionId !== 'string') {
    return res.status(400).json({ success: false, error: 'Chybí session_id' });
  }
  if (!Array.isArray(messages) || messages.length === 0) {
    return res.status(400).json({ success: false, error: 'Chybí messages' });
  }

  res.status(501).json({
    success: false,
    error: 'Chat ještě není zapojený — přijde v kroku OpenAI.',
    session_id: sessionId,
  });
});

module.exports = router;
