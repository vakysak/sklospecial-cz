'use strict';

const express = require('express');
const { chat, resetSession } = require('../services/openai');

const router = express.Router();

router.post('/', async (req, res, next) => {
  try {
    const { messages, session_id: sessionId, reset } = req.body || {};

    if (!sessionId || typeof sessionId !== 'string') {
      return res.status(400).json({ success: false, error: 'Chybí session_id' });
    }

    if (reset) {
      resetSession(sessionId);
      return res.json({ success: true, reset: true, session_id: sessionId });
    }

    if (!Array.isArray(messages) || messages.length === 0) {
      return res.status(400).json({ success: false, error: 'Chybí messages' });
    }

    const result = await chat(messages, sessionId);
    res.json({ success: true, ...result });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
