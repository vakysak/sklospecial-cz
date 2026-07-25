# Checklist

## Blokuje provoz (doplnit údaje)

- [ ] Cloudflare Turnstile — Site Key + Secret Key
- [ ] SMTP — host, port, user, heslo, From (pro FluentSMTP i Node mailer)
- [ ] OpenAI API key (jen do Coolify env / `.env`, ne do gitu)

## Fáze 1 — výroba

- [x] Krok 1: Skeleton Node.js API (lokálně běží `/api/health`)
- [ ] Krok 2: DB + tabulka `sklo_leads`
- [ ] Node.js API služba na Coolify (`/api/`)
- [ ] Tabulka `sklo_leads` (na serveru)
- [ ] Upload + mailer
- [ ] Konfigurátor FE + napojení
- [ ] Chat widget FE + napojení
- [ ] WP theme / CPT / ACF
- [ ] Stránky: skleněné dveře (+ podstránky), návod, realizace, kontakt
- [ ] PDF návod
- [ ] E2E test: konfigurátor → e-mail
- [ ] Bezpečnostní checklist
- [ ] DNS sklospecial.cz + indexace
