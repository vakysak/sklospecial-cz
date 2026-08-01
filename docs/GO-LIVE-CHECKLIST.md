# Go-live checklist — sklospecial.cz

Day-of DNS / production flip. Staging: `*.sslip.io`. Theme `sklo_is_production()` auto-drops noindex when host contains `sklospecial.cz`.

**Pro majitele (české odškrtávací body):** [`KONTROLNI-CHECKLIST.md`](./KONTROLNI-CHECKLIST.md).

**Last code ship:** theme **1.8.3** (homepage trust / Proč Sklospeciál + kontrolní checklist).

---

## Human secrets (blocking)

| Item | Status | Where |
|------|--------|--------|
| **SMTP** (FluentSMTP + Coolify `SMTP_*`) | **BROKEN / needs you** | See `docs/GO-LIVE-SMTP.md`. WP poptávka → `mail_fail`. FluentSMTP **not** in active plugins list (reinstall/configure). API konfigurátor mail needs Coolify `SMTP_HOST/USER/PASS`. |
| **OPENAI_API_KEY** | **Missing** | Coolify API env → redeploy. See `docs/GO-LIVE-CHAT.md`. Live chat returns `OPENAI_API_KEY není nastavený`. |
| **Turnstile Site + Secret** | **Wired** (site key present on `/poptavka/`) | Theme verifies token when configured. Curl without token → `bad_captcha`. |
| **DNS** | Manual | `docs/GO-LIVE-DNS.md` |

---

## Day-of steps (order)

### 1. Backup verify
- [ ] UpdraftPlus / AIO migration snapshot fresh
- [ ] DB + uploads downloadable

### 2. DNS
- [ ] `sklospecial.cz` + `www` → `46.225.122.108` (WP)
- [ ] `api.sklospecial.cz` → same IP (API)
- [ ] Coolify: attach domains to WP + API services
- [ ] SSL (Let’s Encrypt) green on both
- Details: `docs/GO-LIVE-DNS.md`

### 3. Search Replace (URLs)
- [ ] WP: staging sslip → `https://sklospecial.cz` (Better Search Replace / WP-CLI / Rank Math)
- [ ] Theme filter `sklo_api_base` / Coolify: API base → `https://api.sklospecial.cz`
- [ ] Chat widget + konfigurátor URLs on production host

### 4. CORS
- [ ] API `CORS_ORIGIN` includes `https://sklospecial.cz` (+ www if used)
- [ ] Redeploy API after env change

### 5. noindex OFF / sitemap ON
- [ ] Settings → Reading → **uncheck** “Discourage search engines”
- [ ] Rank Math: remove site-wide noindex; enable Sitemap
- [ ] Theme: on `sklospecial.cz`, `sklo_is_production()` = true → **no** staging noindex meta; `robots.txt` Allow + Sitemap
- [ ] Verify `https://sklospecial.cz/robots.txt` and `/sitemap_index.xml`
- Details: `docs/GO-LIVE-SEO.md`

### 6. SMTP verify
- [ ] FluentSMTP connection + test mail
- [ ] Coolify API `SMTP_*` + redeploy
- [ ] `POST /wp-json/sklo/v1/poptavka` → `{"success":true}` (not `mail_fail`)
- [ ] Konfigurátor odeslat bez `mail_warnings`

### 7. OpenAI verify
- [ ] `OPENAI_API_KEY` in Coolify → redeploy
- [ ] `POST /api/chat` → `success: true`

### 8. Security verify (already in theme 1.7.1)
- [ ] `GET /wp-json/wp/v2/users` → **401/403** (anonymous)
- [ ] `POST /xmlrpc.php` disabled / rejected
- [ ] Response headers: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`
- [ ] Production only: `Strict-Transport-Security`
- [ ] Poptávka: honeypot + rate limit; Turnstile if keys set; GDPR still required

### 9. SEO smoke
- [ ] Homepage canonical → home URL
- [ ] OG/Twitter on home, pillars, SEO landings, product `?kod=`
- [ ] Product detail title includes product name
- [ ] Footer link `/mapa-stranek/`
- [ ] City pages not byte-identical (kraj + local note)

### 10. Search Console
- [ ] Verify property `sklospecial.cz`
- [ ] Submit sitemap

---

## Agent cannot do without you

- DNS at registrar
- SMTP passwords / FluentSMTP UI
- OpenAI API key in Coolify
- Turnstile keys in plugin
- Confirm production SSL after DNS
