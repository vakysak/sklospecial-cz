# Go-live: SMTP (položka 4)

**Stav:** audit hotov — **needs your secret** (SMTP credentials).  
Žádné heslo ani host jsme nevymýšleli ani neukládali.

Datum snapshotu: 2026-08-01 · větev `sklospecial`

---

## Audit (co jsme ověřili)

| Kontrola | Výsledek |
|----------|----------|
| Coolify API app `c93wrq6ujvo02103pn26bxbr` → Environment | **Chybí** `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS` |
| Coolify API env už má | `MAIL_FROM`, `MAIL_TO` (nastavené), DB_*, `CORS_ORIGIN`, … |
| Live mailer | Node `mailer.js` bez `SMTP_HOST` spadne na „Chybí SMTP_HOST“ |
| FluentSMTP na WP | Plugin **není** v aktivních pluginech (2026-08-01 recheck) — dříve `fluent-smtp` REST 404. **Nainstalovat + nakonfigurovat** se SMTP credentials |
| `POST /wp-json/sklo/v1/poptavka` | Odpověď `mail_fail` — „E-mail se nepodařilo odeslat“ (bez fungujícího maileru) |
| WP Coolify env | Žádné `SMTP_*` (FluentSMTP se konfiguruje v WP adminu, ne v Coolify env) |
| Coolify MCP | Nedostupný; env čteno přes Coolify HTTP API (jen názvy klíčů) |

Poptávka z katalogu (`/poptavka/` → `sklo/v1/poptavka`) jde přes **`wp_mail()`** → FluentSMTP.  
Konfigurátor (`POST /api/konfigurator/odeslat`) jde přes **Node nodemailer** → `SMTP_*` v Coolify u API.

Obě cesty teď **neodesílají** bez tvých SMTP credentials.

---

## A) Node API — Coolify env (konfigurátor maily)

1. Otevři Coolify: `http://46.225.122.108:8000/`
2. Project → app **sklospecial-api** (`c93wrq6ujvo02103pn26bxbr`)
3. **Environment** → přidej / doplň:

```env
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
MAIL_FROM=info@sklospecial.cz
MAIL_TO=info@sklospecial.cz
```

| Proměnná | Poznámka |
|----------|----------|
| `SMTP_HOST` | např. `smtp.seznam.cz`, `smtp.gmail.com`, `email-smtp.eu-west-1.amazonaws.com`, … |
| `SMTP_PORT` | obvykle `587` (STARTTLS) nebo `465` (SSL) — API nastaví `secure` podle 465 |
| `SMTP_USER` | login schránky / SMTP user |
| `SMTP_PASS` | heslo nebo app password — **jen ty** |
| `MAIL_FROM` | odesílatel (musí být povolený u providera) |
| `MAIL_TO` | schránka firmy pro leady z konfigurátoru |

4. **Redeploy** API (Deploy / restart se znovunačtením env).
5. Ověření:
   - Pošli testovací konfigurátor (s ≥2 fotkami) → v odpovědi **nesmí** být `mail_warnings` s `Chybí SMTP_HOST` / auth chybou.
   - Zkontroluj doručení na `MAIL_TO` a potvrzení klientovi.

Kód: `app/api/services/mailer.js` (`sendLeadToFirm`, `sendConfirmationToClient`).

---

## B) WordPress — FluentSMTP (poptávka `/poptavka/`)

1. WP admin → **FluentSMTP** (Settings / Connections)
2. Přidej connection se **stejným** (nebo firemním) SMTP jako u API:
   - From Email / From Name
   - Host, Port, Encryption, Username, Password
3. Save → **Send Test Email** v FluentSMTP
4. Ověření poptávky:

```bash
curl -sS -X POST \
  "https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/wp-json/sklo/v1/poptavka" \
  -H "Content-Type: application/json" \
  -d '{
    "jmeno":"Test SMTP",
    "email":"tvoje@schranka.cz",
    "telefon":"+420736134604",
    "adresa":"Praha",
    "doprava":"ne",
    "montaz":"ne",
    "gdpr_souhlas":true,
    "poznamka":"Test po nastavení FluentSMTP",
    "order":{}
  }'
```

Očekáváno: `{"success":true}` (ne `mail_fail`).

Příjemce firemního mailu = `admin_email` ve WP (`get_option('admin_email')` v `functions.php`). Po go-live nastav na `info@sklospecial.cz` (nebo jinou ostrý adresu).

---

## Doporučený postup

1. Zvol SMTP providera (Seznam / Google Workspace / Amazon SES / …) a vytvoř **app password** nebo SMTP credentials.
2. Nejdřív FluentSMTP test v WP (poptávka).
3. Stejné údaje do Coolify `SMTP_*` u API (konfigurátor).
4. MX / DNS e-mailové záznamy **neměň** kvůli webu, pokud mail hostuješ jinde — jen SMTP odesílání.

---

## Co agent neudělá bez tebe

- Nevymyslí SMTP heslo ani host.
- Nenastaví FluentSMTP v WP adminu bez credentials.
- Neuloží secrets do gitu (`.env` je v `.gitignore`).
