'use strict';

const express = require('express');
const { chat, resetSession } = require('../services/openai');

const router = express.Router();

router.post('/', async (req, res, next) => {
  try {
    const { messages, session_id: sessionId, reset, page_context: pageContext } = req.body || {};

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

    const ctx =
      pageContext && typeof pageContext === 'object'
        ? {
            kod: pageContext.kod ? String(pageContext.kod).slice(0, 64) : undefined,
            path: pageContext.path ? String(pageContext.path).slice(0, 300) : undefined,
          }
        : undefined;

    const result = await chat(messages, sessionId, ctx);
    res.json({ success: true, ...result });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
