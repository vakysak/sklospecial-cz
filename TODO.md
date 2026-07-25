# Checklist

## Blokuje provoz (doplnit údaje)

- [ ] Cloudflare Turnstile — Site Key + Secret Key
- [ ] SMTP — host, port, user, heslo, From (pro FluentSMTP i Node mailer)
- [ ] OpenAI API key (jen do Coolify env / `.env`, ne do gitu)
- [ ] DB přístup pro API (Coolify MariaDB na sdílené síti)

## Fáze 1 — výroba

- [x] Krok 1: Skeleton Node.js API
- [x] Krok 2: DB vrstva + migrate `sklo_leads`
- [x] Krok 3: Upload + validace fotek
- [x] Krok 4: Mailer (šablony + SMTP služba)
- [x] Krok 5: Endpoint konfigurátor `/api/konfigurator/odeslat`
- [ ] Krok 6: Coolify služba `/api/` + migrate na serveru
- [ ] Chat OpenAI (plná integrace)
- [ ] Konfigurátor FE
- [ ] WP theme / CPT / ACF / stránky
- [ ] PDF návod
- [ ] E2E test
- [ ] DNS sklospecial.cz + indexace
