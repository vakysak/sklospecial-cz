#!/bin/sh
set -e

echo "[sklospecial-api] migrate..."
node api/scripts/migrate.js || {
  echo "[sklospecial-api] migrate failed, starting anyway"
}

echo "[sklospecial-api] starting on :${PORT:-3001}"
exec node api/server.js
