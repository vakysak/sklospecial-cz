'use strict';

const path = require('path');
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const dotenv = require('dotenv');

dotenv.config({ path: path.join(__dirname, '../../.env') });
dotenv.config({ path: path.join(__dirname, '../.env') });

const chatRouter = require('./routes/chat');
const konfiguratorRouter = require('./routes/konfigurator');
const uploadRouter = require('./routes/upload');
const { apiRateLimit } = require('./middleware/rateLimit');

const PORT = Number(process.env.PORT || 3001);
const CORS_ORIGIN = process.env.CORS_ORIGIN || '*';

const app = express();

app.set('trust proxy', 1);

app.use(
  helmet({
    crossOriginResourcePolicy: { policy: 'cross-origin' },
    contentSecurityPolicy: false,
  })
);
app.use(
  cors({
    origin: CORS_ORIGIN === '*' ? true : CORS_ORIGIN.split(',').map((s) => s.trim()),
    methods: ['GET', 'POST', 'OPTIONS'],
  })
);
app.use(morgan(process.env.NODE_ENV === 'production' ? 'combined' : 'dev'));
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));

app.use('/public', express.static(path.join(__dirname, '../public'), { maxAge: '1h' }));

app.get('/api/health', async (_req, res) => {
  const payload = {
    ok: true,
    service: 'sklospecial-api',
    phase: 1,
    time: new Date().toISOString(),
    db: 'skipped',
  };

  if (process.env.DB_USER && process.env.DB_PASSWORD && process.env.DB_NAME) {
    try {
      const { ping } = require('./services/database');
      payload.db = (await ping()) ? 'ok' : 'fail';
    } catch (err) {
      payload.db = 'error';
      payload.db_error = err.message;
    }
  }

  res.status(200).json(payload);
});

app.use('/api', apiRateLimit);
app.use('/api/chat', chatRouter);
app.use('/api/konfigurator', konfiguratorRouter);
app.use('/api/upload', uploadRouter);

app.use((err, _req, res, _next) => {
  console.error('[api]', err);
  const status = err.status || err.statusCode || 500;
  res.status(status).json({
    success: false,
    error: status === 500 ? 'Interní chyba serveru' : err.message,
  });
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`sklospecial-api listening on :${PORT}`);
});
