# Checklist

## Blokuje ostrý provoz (doplnit)

- [ ] Cloudflare Turnstile — Site + Secret key
- [ ] SMTP — host/port/user/heslo (FluentSMTP + Node `SMTP_*`) — viz `docs/GO-LIVE-SMTP.md`
- [ ] OpenAI API key → Coolify env `OPENAI_API_KEY` u `sklospecial-api` — viz `docs/GO-LIVE-CHAT.md`

## Hotovo

- [x] Node API na Coolify (`running:healthy`, DB `sklo_leads`)
- [x] Upload + konfigurátor endpoint
- [x] Mailer šablony (čeká SMTP)
- [x] Chat API (čeká OpenAI klíč)
- [x] Konfigurátor FE + chat widget
- [x] WP stránky Fáze 1

## Ještě

- [x] WP theme sklospecial + homepage layout
- [ ] Homepage obsah / menu (základ hotov)
- [ ] CPT Realizace + ACF (až bude ACF Pro)
- [ ] PDF návod
- [ ] DNS sklospecial.cz + indexace
