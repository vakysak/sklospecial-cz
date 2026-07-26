# Checklist

## Blokuje ostrý provoz (doplnit)

- [ ] Cloudflare Turnstile — Site + Secret key
- [ ] SMTP — host/port/user/heslo (FluentSMTP + Node `SMTP_*`)
- [ ] OpenAI API key → Coolify env `OPENAI_API_KEY` u `sklospecial-api`

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
