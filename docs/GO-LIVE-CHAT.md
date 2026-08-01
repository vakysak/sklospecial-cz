# Go-live: OpenAI chat (položka 5)

**Stav:** audit hotov — **needs your secret** (`OPENAI_API_KEY`).  
Klíč jsme nevymýšleli ani neukládali.

Datum snapshotu: 2026-08-01 · větev `sklospecial`

---

## Audit (co jsme ověřili)

| Kontrola | Výsledek |
|----------|----------|
| Coolify API app `c93wrq6ujvo02103pn26bxbr` → Environment | **`OPENAI_API_KEY` chybí** (není v seznamu env) |
| Live `POST /api/chat` | `{"success":false,"error":"OPENAI_API_KEY není nastavený"}` |
| Lokální `.env` v repu | žádný (správně — secrets jen Coolify) |
| Chat widget launcher | už má **2 řádky**: „Sklo asistent“ / „WhatsApp“ (`app/public/chat-widget.js`, verze na WP `?ver=1.4.0`) |

Kód: `app/api/services/openai.js` → `process.env.OPENAI_API_KEY`.  
Model default: `OPENAI_MODEL` nebo `gpt-4o` (volitelná env).

---

## Kam vložit klíč (Coolify)

1. Coolify: `http://46.225.122.108:8000/`
2. App **sklospecial-api** (`c93wrq6ujvo02103pn26bxbr`)
3. **Environment** → přidej:

```env
OPENAI_API_KEY=sk-...
```

Volitelně:

```env
OPENAI_MODEL=gpt-4o
```

4. **Redeploy** API (Deploy), ať se env načte do kontejneru.
5. Ověření:

```bash
curl -sS -X POST \
  "https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/api/chat" \
  -H "Content-Type: application/json" \
  -d '{"messages":[{"role":"user","content":"Ahoj"}],"session_id":"test-chat"}'
```

Očekáváno: `success: true` + text odpovědi (ne chyba o chybějícím klíči).

Na webu: floating tlačítko → „Napsat do chatu“ → krátká otázka ke skleněným dveřím.

---

## Widget label

Launcher už je dvouřádkový (bez další změny v tomto kroku):

- řádek 1: **Sklo asistent**
- řádek 2: **WhatsApp** (menší, sekundární)

Po změně `chat-widget.js` bumpni `?ver=` ve WP snippetu / theme enqueue, ať prohlížeče neservírují starou cache.

---

## Bezpečnost

- Klíč **jen** v Coolify Environment (secret), nikdy do gitu / theme / `window.*`.
- Rate limit API: `RATE_LIMIT_*` (už částečně nastaveno).
- Po ostré doméně aktualizuj `CORS_ORIGIN` a `SKLO_API_BASE` ve WP (viz `docs/GO-LIVE-DNS.md`).
