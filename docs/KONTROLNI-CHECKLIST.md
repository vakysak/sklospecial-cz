# Kontrolní checklist — spuštění sklospecial.cz

Pro majitele / provozovatele. Odškrtávej položky před a během go-live.

**Staging (aktuální):**
- Web (WP): https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io
- API: https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io

**Produkce (po DNS):**
- Web: https://sklospecial.cz _(placeholder do přepnutí)_
- WWW: https://www.sklospecial.cz _(placeholder)_
- API: https://api.sklospecial.cz _(placeholder)_

Technický denní plán: [`GO-LIVE-CHECKLIST.md`](./GO-LIVE-CHECKLIST.md).

---

### Před spuštěním (ty)

- [ ] DNS `sklospecial.cz` (+ `www`, `api`) → `46.225.122.108` — postup v [`GO-LIVE-DNS.md`](./GO-LIVE-DNS.md)
- [ ] Coolify: SSL (Let’s Encrypt) + domény připojené na WP a API služby
- [ ] SMTP: FluentSMTP ve WP + Coolify `SMTP_*` pro API — [`GO-LIVE-SMTP.md`](./GO-LIVE-SMTP.md)
- [ ] OPENAI: ověřit chat (mělo by už fungovat) — [`GO-LIVE-CHAT.md`](./GO-LIVE-CHAT.md)
- [ ] Zálohy DB + uploads (UpdraftPlus / export) stažené a ověřené
- [ ] Licence fotek Quba / partnership — [`GO-LIVE-FOTKY.md`](./GO-LIVE-FOTKY.md)

---

### Po DNS (technické)

- [ ] Search Replace URL: staging `*.sslip.io` → `https://sklospecial.cz`
- [ ] CORS / `SKLO_API_BASE` → `https://api.sklospecial.cz` (+ Coolify env, redeploy API)
- [ ] Ověřit **noindex vypnuté** na produkci (Reading + Rank Math + theme `sklo_is_production()`) — [`GO-LIVE-SEO.md`](./GO-LIVE-SEO.md)
- [ ] Sitemap + Search Console (ověření property + odeslání sitemap)
- [ ] Test poptávky end-to-end (e-mail dorazí na `info@…`)
- [ ] Test konfigurátor — odeslání bez `mail_warnings`
- [ ] Test chat + WhatsApp odkaz / widget
- [ ] Cookie lišta + právní odkazy (GDPR, cookies, obchodní podmínky)

---

### Kontrola obsahu (projdi web)

- [ ] Homepage: H1, O nás, CTA, sekce **Proč Sklospeciál** (garance + úhrada)
- [ ] Katalog: produkt + doplatky + tlačítko Odeslat poptávku
- [ ] Konfigurátor: SklS produkty, náhled, odeslání
- [ ] Kontakt: IČO/DIČ + postup poptávky / úhrady (nezdvojovat zbytečně — v patičce už je)
- [ ] Poptávka: formulář + GDPR souhlas + CAPTCHA (pokud zapnuto)
- [ ] Průvodci / SEO landings / města (náhodný vzorek 2–3 stránek)
- [ ] Realizace, 404 stránka, patička (odkazy, IČO/DIČ, mapa stránek)

---

### Security quick check

- [ ] `GET /wp-json/wp/v2/users` → **401** (nebo 403) pro anonymní
- [ ] `xmlrpc.php` disabled / odmítá zápis
- [ ] `wp-admin` login pouze přes přihlášení (žádný veřejný bypass)

---

### AI

- [ ] Chat doporučí produkty **SklS** z katalogu
- [ ] Fotka otvoru — vision / kontrola rozměrů
- [ ] Podobné produkty (vizuální search)
- [ ] Poptávka z chatu (tool → odeslání)

---

## Rychlé URL pro kontrolu

| Co | Staging | Produkce |
|----|---------|----------|
| Homepage | [staging home](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/) | `https://sklospecial.cz/` |
| Kontakt | [staging kontakt](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/kontakt/) | `https://sklospecial.cz/kontakt/` |
| Poptávka | [staging poptávka](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/poptavka/) | `https://sklospecial.cz/poptavka/` |
| Katalog | [staging skleněné dveře](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/sklenene-dvere/) | `https://sklospecial.cz/sklenene-dvere/` |
| Konfigurátor | [API studio](https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/konfigurator.html) | `https://api.sklospecial.cz/public/konfigurator.html` |
