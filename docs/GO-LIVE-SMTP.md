# Go-live: SMTP (položka 4)

**Stav:** částečně připraveno — **chybí Webglobe schránka + heslo** (`SMTP_USER` / `SMTP_PASS`).  
Žádné heslo jsme nevymýšleli ani neukládali.

Datum snapshotu: 2026-08-02 · produkce `sklospecial.eu` · větev `sklospecial`

---

## Audit (co jsme ověřili)

| Kontrola | Výsledek |
|----------|----------|
| Coolify API `c93wrq6ujvo02103pn26bxbr` | **Chybí** `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS` (2026-08-02 REST) |
| Coolify API `MAIL_FROM` / `MAIL_TO` | Nastaveno na **`info@sklospecial.eu`** (dříve `hampl@vakysak.cz`) — **platí až po redeploy** |
| DNS `sklospecial.eu` | MX → `email*.webglobe.cz`, SPF `include:_spf.webglobe.cz`, DKIM `default._domainkey` OK |
| DNS `sklospecial.cz` | MX → **Active24**, SPF `include:_spf.websupport.cz` — **ne Webglobe** |
| Live kontakt na webu | `info@sklospecial.eu` (mailto v tématu) |
| FluentSMTP na WP | Namespace `fluent-smtp` v REST **je** (plugin přítomen); konfigurace jen v WP adminu |
| `POST /wp-json/sklo/v1/poptavka` | Bez Turnstile → `bad_captcha`; s mailerem bez SMTP → historicky `mail_fail` |
| Dedicated API SMTP test | **Není** — jen `/api/health` (db); mail se ověří konfigurátorem / FluentSMTP testem |
| Lokální `.env` / transcripts | **Žádné** SMTP heslo |

**Proč ne `info@sklospecial.cz` jako From při Webglobe SMTP:**  
odesílání přes Webglobe musí mít From na doméně se SPF Webglobe (= **`sklospecial.eu`**). From `@sklospecial.cz` by při Webglobe SMTP padal na SPF fail (`.cz` = Websupport/Active24).

Poptávka z katalogu (`/poptavka/` → `sklo/v1/poptavka`) → **`wp_mail()`** → FluentSMTP.  
Konfigurátor (`POST /api/konfigurator/odeslat`) → **Node nodemailer** → Coolify `SMTP_*`.

---

## Webglobe SMTP — přesné hodnoty

Oficiální parametry ([Webglobe poradna](https://www.webglobe.cz/poradna/odesilani-emailu-z-webu)):

| Pole | Hodnota |
|------|---------|
| SMTP host | **`mail.webglobe.cz`** (aliasy: `smtp.webglobe.cz`, `mail.sklospecial.eu` → stejný cluster) |
| Port | **`587`** (doporučeno) |
| Encryption | **STARTTLS** / TLS |
| Alternativa | Port **`465`** + SSL/TLS (`secure: true` — API to nastaví automaticky při `SMTP_PORT=465`) |
| Username | celá adresa schránky, např. `info@sklospecial.eu` |
| Password | heslo schránky z Webglobe (jen ty) |
| From Email | stejná schránka (nebo alias na stejné doméně Webglobe) |
| From Name | `Sklospeciál` |

**SPF neměň** — už je `v=spf1 a mx include:_spf.webglobe.cz -all`. Při odesílání přes Webglobe SMTP zůstává SPF v pořádku.

---

## Co musíš udělat ve Webglobe (povinné)

1. Přihlas se do Webglobe → doména **`sklospecial.eu`** → E-mailové schránky.
2. **Vytvoř schránku** (pokud ještě neexistuje):
   - doporučeno: **`info@sklospecial.eu`**
   - nebo `poptavky@sklospecial.eu` / `noreply@sklospecial.eu` (pak From = tato adresa)
3. Nastav **silné heslo** a ulož si ho (do gitu nepatří).
4. Volitelně: v Roundcube ověř, že schránka přijímá poštu.
5. Volitelně na webu: kontakt `mailto:` je sjednocený na `info@sklospecial.eu` (téma + právní stránky).

Bez této schránky + hesla **nelze** doplnit `SMTP_USER` / `SMTP_PASS` ani dokončit FluentSMTP.

---

## A) Node API — Coolify env (konfigurátor)

1. Coolify: `http://46.225.122.108:8000/`
2. App **sklospecial-api** (`c93wrq6ujvo02103pn26bxbr`) → **Environment**
3. Doplň (MAIL_* už má být `info@sklospecial.eu`):

```env
SMTP_HOST=mail.webglobe.cz
SMTP_PORT=587
SMTP_USER=info@sklospecial.eu
SMTP_PASS=<heslo ze Webglobe — jen ty>
MAIL_FROM=info@sklospecial.eu
MAIL_TO=info@sklospecial.eu
```

| Proměnná | Poznámka |
|----------|----------|
| `SMTP_HOST` | `mail.webglobe.cz` |
| `SMTP_PORT` | `587` (STARTTLS) nebo `465` (SSL) |
| `SMTP_USER` | celá adresa Webglobe schránky |
| `SMTP_PASS` | heslo schránky — **jen ty** |
| `MAIL_FROM` | musí být `@sklospecial.eu` (Webglobe SPF) |
| `MAIL_TO` | schránka firmy pro leady (stejná nebo jiná `@sklospecial.eu`) |

4. **Redeploy** API (Deploy / restart se znovunačtením env).
5. Ověření: konfigurátor s ≥2 fotkami → odpověď **bez** `mail_warnings` typu `Chybí SMTP_HOST` / auth error; doručení na `MAIL_TO` + potvrzení klientovi.

Kód: `app/api/services/mailer.js`. Dedikovaný SMTP health endpoint **není**.

REST (až budeš mít heslo — agent / ty):

```http
PATCH /api/v1/applications/c93wrq6ujvo02103pn26bxbr/envs
{"key":"SMTP_HOST","value":"mail.webglobe.cz","is_literal":true}
```

Stejně pro `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS` (POST pokud klíč ještě neexistuje).

---

## B) WordPress — FluentSMTP (poptávka `/poptávka/`)

Click-path:

1. WP admin → **FluentSMTP** (nebo **Settings → FluentSMTP** / `wp-admin/admin.php?page=fluent-mail`)
2. **Add Connection** / Other SMTP:
   - From Email: `info@sklospecial.eu`
   - From Name: `Sklospeciál`
   - Host: `mail.webglobe.cz`
   - Port: `587`
   - Encryption: **TLS** (STARTTLS)
   - Username: `info@sklospecial.eu`
   - Password: *(heslo Webglobe)*
3. Save → **Send Test Email**
4. Settings → General: **admin_email** ideálně `info@sklospecial.eu` (příjemce poptávek z `functions.php`).

Ověření poptávky (s platným Turnstile tokenem z prohlížeče, nebo dočasně z WP admin testu):

```bash
# Očekáváno po SMTP: {"success":true} — ne mail_fail
# Bez Turnstile tokenu: bad_captcha
curl -sS -X POST "https://sklospecial.eu/wp-json/sklo/v1/poptavka" \
  -H "Content-Type: application/json" \
  -d '{"jmeno":"Test SMTP","email":"tvoje@schranka.cz","telefon":"+420736134604","adresa":"Praha","doprava":"ne","montaz":"ne","gdpr_souhlas":true,"poznamka":"Test FluentSMTP","order":{}}'
```

---

## Doporučený postup (po vytvoření schránky)

1. Webglobe: vytvoř `info@sklospecial.eu` + heslo.
2. FluentSMTP test v WP.
3. Stejné údaje do Coolify `SMTP_*` + ověř `MAIL_FROM`/`MAIL_TO` = `@sklospecial.eu`.
4. Redeploy API → test konfigurátoru.
5. MX / SPF / DKIM **neměň**.

---

## Co agent neudělá bez tebe

- Nevymyslí SMTP heslo.
- Nenastaví FluentSMTP UI bez credentials.
- Neuloží secrets do gitu.
- Netešuje ostrý mail bez `SMTP_PASS`.
