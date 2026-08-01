# MEMORY SNAPSHOT — sklospecial.cz

**PROJEKT:** sklospecial.cz  
**FÁZE:** 1 — Skleněné dveře · AI Phase **C1** (podobné dveře)

## SERVER

- cx43 / Hetzner · IP `46.225.122.108`
- 8 vCPU / 16 GB RAM / 80 GB
- WordPress na Coolify (dočasná sslip.io doména)
- Project UUID: `f11nd5n7lf5j9mhukibhxvsq`
- Service UUID: `jzxqv0aq7w5lf4f12nkwgj00`

## STACK

- WP theme: `wp-theme/sklospecial` (glass UI, front-page) **1.13.0**
- Prodloužená záruka (+1 rok): zákaznicky **10 %** ceny výrobku (bez dopravy/montáže). Interně může kalkulace kolísat cca 8–12 % podle typu — na webu neuvádět.
- Orientační montáž (web): dveře od 2 500 Kč/ks · sprcha od 3 500 Kč/sestava · zábradlí/příčky od 1 500 Kč/bm — finální v nabídce.
- WordPress (CMS, obsah, realizace)
- Node.js / Express (API, konfigurátor, chat)
- MariaDB (WP + `sklo_leads`)
- Coolify Traefik (proxy + SSL)
- OpenAI chat (gpt-4o) + tool calling
- Alpine.js / vanilla JS (konfigurátor + chat)
- ACF Pro (custom fields)
- FluentSMTP + Turnstile + LLAR (už na WP)

## AI FÁZE

- **B** — vision upload v chatu (fotka otvoru) — hotovo
- **C1** — `find_similar_products` + widget „Najít podobné v katalogu“ + `GET /api/produkty/:code/similar` — hotovo
- **C2** — AR / DoorVision — **odloženo** (později)
- **C3** — WhatsApp Business AI — **odloženo** (později)

## MODEL

Prodej na dálku: konfigurace → rozměry + fotky → nabídka od firmy.

## STRÁNKY FÁZE 1

`/` · `/sklenene-dvere/` · `/posuvne/` · `/otocne/` · `/celosklenene/` · `/navod-na-zamereni/` · `/pruvodce/` · `/realizace/` · `/kontakt/`

## KONFIGURÁTOR

7 kroků → `POST /api/konfigurator/odeslat` → DB + e-mail firmě + potvrzení klientovi

## CHAT

Floating „Sklo asistent“ · jen skleněné dveře · stream · 20 req/IP/h · klíč jen `.env`  
Widget **v1.8.2** — fotka + podobné v katalogu (product cards).

## BRAND

Tykat · konkrétní · bez „luxusní/exkluzivní/prémiový“ · CTA „pošli rozměry a fotky“

## Live (Coolify)

- WP: `https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io`
- API: `https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io` (`db: ok`)
- Konfigurátor: `/public/konfigurator.html` na API
- Chat widget: načtený na WP (snippet #7, `?ver=1.8.2`)

## DOPORUČENÝ START

Hotovo přes Coolify. **Secrets chybí** — viz `docs/GO-LIVE-SMTP.md` + `docs/GO-LIVE-CHAT.md` (SMTP + FluentSMTP + `OPENAI_API_KEY`).
