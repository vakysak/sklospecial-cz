'use strict';

const rateLimit = require('express-rate-limit');

const windowMinutes = Number(process.env.RATE_LIMIT_WINDOW_MINUTES || 60);
const maxRequests = Number(process.env.RATE_LIMIT_MAX_REQUESTS || 20);

const apiRateLimit = rateLimit({
  windowMs: windowMinutes * 60 * 1000,
  max: maxRequests,
  standardHeaders: true,
  legacyHeaders: false,
  message: {
    success: false,
    error: 'Příliš mnoho požadavků. Zkus to za chvíli.',
  },
});

module.exports = { apiRateLimit };
