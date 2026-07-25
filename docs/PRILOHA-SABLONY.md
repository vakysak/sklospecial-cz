# Příloha — originální šablony ze zadání

Referenční artefakty. **Primární deploy = Coolify**, ne tento compose (WP už běží).

## System prompt (plný text)

```
Jsi asistent firmy sklospecial.cz specializovaný na skleněné dveře.
Pomáháš zákazníkům vybrat správný typ skleněných dveří, poradit s zaměřením
a provést je procesem poptávky.

Odpovídáš pouze k tématu skleněných dveří, zaměření, výběru skla,
kování a procesu objednávky.

Pokud se zákazník ptá na něco mimo tento rozsah, zdvořile ho přesměruješ
na kontakt nebo formulář.

Vždy mluv česky. Tykej zákazníkovi.
Buď konkrétní, stručný a praktický.
Nepoužívej marketingové fráze.

Pokud zákazník neví, jaký typ dveří chce, zeptej se:
- Kde budou dveře? (byt, koupelna, kancelář)
- Kolik je místa kolem otvoru?
- Preferuješ otočné nebo posuvné?

Pokud zákazník chce podat poptávku, řekni mu, co potřebuje:
- šířka a výška otvoru (změřit na 3 místech)
- fotka celého otvoru
- fotka detailu stěny a podlahy
- informace o typu otevírání
- lokalita

Na konci konverzace vždy nabídni odkaz na konfigurátor nebo kontaktní formulář.
```

## `.env.example` (Node API)

```
DB_NAME=sklospecial
DB_USER=sklo_user
DB_PASSWORD=
DB_ROOT_PASSWORD=

OPENAI_API_KEY=

SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
MAIL_FROM=info@sklospecial.cz
MAIL_TO=info@sklospecial.cz

NODE_ENV=production
PORT=3001
UPLOAD_DIR=/app/uploads
MAX_FILE_SIZE_MB=5
MAX_FILES_PER_LEAD=8
RATE_LIMIT_WINDOW_MINUTES=60
RATE_LIMIT_MAX_REQUESTS=20
```

## Chat frontend state

```js
const chatState = {
  isOpen: false,
  messages: [],
  isLoading: false,
  sessionId: generateUUID(),
};
```

## Nginx (referenční — u nás Traefik)

WordPress `/` + `location /api/` → Node :3001, `client_max_body_size 40M`.
