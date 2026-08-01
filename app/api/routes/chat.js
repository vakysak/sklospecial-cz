'use strict';

const express = require('express');
const { chat, chatStream, resetSession } = require('../services/openai');

const router = express.Router();

function wantsStream(req) {
  if (req.body && req.body.stream === true) return true;
  const accept = String(req.headers.accept || '');
  return accept.includes('text/event-stream');
}

function writeSse(res, payload) {
  res.write(`data: ${JSON.stringify(payload)}\n\n`);
}

router.post('/', async (req, res, next) => {
  try {
    const { messages, session_id: sessionId, reset, page_context: pageContext, stream } = req.body || {};

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

    if (wantsStream(req) || stream === true) {
      res.status(200);
      res.setHeader('Content-Type', 'text/event-stream; charset=utf-8');
      res.setHeader('Cache-Control', 'no-cache, no-transform');
      res.setHeader('Connection', 'keep-alive');
      res.setHeader('X-Accel-Buffering', 'no');
      if (typeof res.flushHeaders === 'function') res.flushHeaders();

      try {
        await chatStream(messages, sessionId, ctx, (payload) => writeSse(res, payload));
      } catch (err) {
        writeSse(res, {
          type: 'error',
          success: false,
          error: err.message || String(err),
        });
      }
      return res.end();
    }

    const result = await chat(messages, sessionId, ctx);
    res.json({ success: true, ...result });
  } catch (err) {
    next(err);
  }
});

module.exports = router;
