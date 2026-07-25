# MEMORY SNAPSHOT — sklospecial.cz

**PROJEKT:** sklospecial.cz  
**FÁZE:** 1 — Skleněné dveře

## SERVER

- cx43 / Hetzner · IP `46.225.122.108`
- 8 vCPU / 16 GB RAM / 80 GB
- WordPress na Coolify (dočasná sslip.io doména)
- Project UUID: `f11nd5n7lf5j9mhukibhxvsq`
- Service UUID: `jzxqv0aq7w5lf4f12nkwgj00`

## STACK

- WordPress (CMS, obsah, realizace)
- Node.js / Express (API, konfigurátor, chat) — **nová služba**
- MariaDB (WP + `sklo_leads`)
- Coolify Traefik (proxy + SSL)
- OpenAI Responses API (chat)
- Alpine.js / vanilla JS (konfigurátor + chat)
- ACF Pro (custom fields)
- FluentSMTP + Turnstile + LLAR (už na WP)

## MODEL

Prodej na dálku: konfigurace → rozměry + fotky → nabídka od firmy.

## STRÁNKY FÁZE 1

`/` · `/sklenene-dvere/` · `/posuvne/` · `/otocne/` · `/celosklenene/` · `/navod-na-zamereni/` · `/realizace/` · `/kontakt/`

## KONFIGURÁTOR

7 kroků → `POST /api/konfigurator/odeslat` → DB + e-mail firmě + potvrzení klientovi

## CHAT

Floating „Sklo asistent“ · jen skleněné dveře · stream · 20 req/IP/h · klíč jen `.env`

## BRAND

Tykat · konkrétní · bez „luxusní/exkluzivní/prémiový“ · CTA „pošli rozměry a fotky“

## Live (Coolify)

- WP: `https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io`
- API: `https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io` (`db: ok`)
- Konfigurátor: `/public/konfigurator.html` na API
- Chat widget: načtený na WP (snippet)

## DOPORUČENÝ START

Hotovo přes Coolify. Doplň `OPENAI_API_KEY` + SMTP env u API app.
