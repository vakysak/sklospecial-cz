FROM node:22-alpine

WORKDIR /app

RUN apk add --no-cache wget

COPY package.json package-lock.json ./
RUN npm ci --omit=dev

COPY api ./api
COPY public ./public
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN mkdir -p /app/uploads/poptavky && chmod +x /docker-entrypoint.sh

ENV NODE_ENV=production
ENV PORT=3001
ENV UPLOAD_DIR=/app/uploads

EXPOSE 3001

HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=6 \
  CMD wget -qO- http://127.0.0.1:3001/api/health || exit 1

ENTRYPOINT ["/docker-entrypoint.sh"]
