# Go-live: DNS a domény sklospecial.cz

**Stav položky 1:** připraveno v dokumentaci — **vyžaduje manuální DNS akci** u registrátora a připojení domén v Coolify.  
Živé DNS jsme v tomto kroku **neměnili** (Coolify MCP nedostupné / bez listingu).

Datum snapshotu: 2026-08-01 · větev `sklospecial`

---

## 0. Současný staging (nemazat hned)

| Služba | Staging URL | Coolify UUID |
|--------|-------------|--------------|
| WordPress | `https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io` | `jzxqv0aq7w5lf4f12nkwgj00` |
| API (Node) | `https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io` | `c93wrq6ujvo02103pn26bxbr` |
| Health | `…/api/health` | — |
| Konfigurátor | `…/public/konfigurator.html` | na API |
| Chat widget JS | `…/public/chat-widget.js` | na API |
| Katalog obrázky | `…/public/katalog-img/SklS-XXXX.jpg` | na API (static) |
| Leads DB | — | `nwqi2e98unof1x0daqk3bh2c` |
| Project | Coolify | `f11nd5n7lf5j9mhukibhxvsq` |

- Server IP (Hetzner cx43): **`46.225.122.108`**
- Coolify UI: `http://46.225.122.108:8000/`
- Staging sslip.io nechte po go-live ještě chvíli jako fallback / debug (neodpojovat Traefik doménu dřív, než ověříte ostrý web).

---

## 1. Cílové produkční hostname

Doporučené rozdělení (2 Coolify služby = 2 sady domén):

| Hostname | Kam | Co běží |
|----------|-----|---------|
| `sklospecial.cz` | **WordPress** | CMS, theme, stránky, REST `/wp-json/sklo/v1/…` |
| `www.sklospecial.cz` | **WordPress** | stejná WP služba (redirect www→apex nebo naopak) |
| `api.sklospecial.cz` | **API** | Express: `/api/*`, `/public/konfigurator.html`, `/public/chat-widget.js`, `/public/katalog-img/*` |

**WP vs API — kam co patří**

| Asset / endpoint | Host |
|------------------|------|
| Veřejný web, menu, katalog stránky | `sklospecial.cz` (WP) |
| Konfigurátor HTML/JS/CSS | `api.sklospecial.cz/public/konfigurator.html` |
| Chat widget skript | `api.sklospecial.cz/public/chat-widget.js` |
| Katalog fotky (`katalog-img`) | `api.sklospecial.cz/public/katalog-img/…` |
| Chat / konfigurátor / upload API | `api.sklospecial.cz/api/…` |

> Alternativa `konfigurator.sklospecial.cz` jen pokud nechceš subdoménu `api` — v Coolify stejně připojíš hostname na API službu. V theme a snippetech musí být **jedna** kanonická API base URL.

---

## 2. DNS záznamy (u registrátora / DNS hostingu)

TTL klidně 300 s během přepnutí, pak zvednout.

```
# Apex — WordPress (Coolify Traefik na stejném serveru)
sklospecial.cz.          A      46.225.122.108

# www — buď A na stejnou IP, nebo CNAME na apex
www.sklospecial.cz.      A      46.225.122.108
# NEBO:
# www.sklospecial.cz.    CNAME  sklospecial.cz.

# API — konfigurátor, chat-widget, katalog-img, /api
api.sklospecial.cz.      A      46.225.122.108
```

**Poznámky**

- Pokud doména už má A/AAAA jinde, nejdřív sniž TTL, pak přepiš na `46.225.122.108`.
- AAAA (IPv6) zatím **nedávej**, pokud Coolify/Traefik nemá IPv6 listener (jinak hrozí broken dual-stack).
- Cloudflare „proxy“ (oranžový cloud): po go-live OK, při prvním LE certifikátu v Coolify často dočasně **DNS only** (šedý cloud), dokud Traefik nevydá cert — pak můžeš zapnout proxy.
- MX / e-mail záznamy (`info@sklospecial.cz`) **neměň** kvůli webu, pokud mail běží jinde.

Ověření po propagaci:

```bash
dig +short A sklospecial.cz
dig +short A www.sklospecial.cz
dig +short A api.sklospecial.cz
# očekávej: 46.225.122.108
```

---

## 3. Coolify — připojení domén

Panel: `http://46.225.122.108:8000/` · Project UUID `f11nd5n7lf5j9mhukibhxvsq`

### 3.1 WordPress (`jzxqv0aq7w5lf4f12nkwgj00`)

1. Otevři službu **sklospecial-wordpress** (UUID výše).
2. **Domains / FQDN** → přidej:
   - `sklospecial.cz`
   - `www.sklospecial.cz`
3. Zapni **HTTPS / Let's Encrypt** (Generate SSL) pro obě.
4. Nastav preferovaný kanonický host (redirect www↔apex) podle volby firmy — doporučení: **apex** `https://sklospecial.cz`, www → 301 na apex (Traefik redirect nebo WP).
5. Staging FQDN `wordpress-jzxqv0aq7w5lf4f12nkwgj00.….sslip.io` **ponech** do ověření.

### 3.2 API (`c93wrq6ujvo02103pn26bxbr`)

1. Otevři službu **sklospecial-api**.
2. Domains → přidej `api.sklospecial.cz`.
3. Zapni **Let's Encrypt**.
4. Po SSL ověř:
   - `https://api.sklospecial.cz/api/health` → `ok` / `db: ok`
   - `https://api.sklospecial.cz/public/konfigurator.html`
   - jeden existující `…/public/katalog-img/SklS-….jpg`
5. Staging sslip FQDN API zatím nech.

### 3.3 Pořadí

1. DNS A záznamy  
2. Attach domén v Coolify + LE  
3. Až HTTPS zelené → Search-Replace ve WP + CORS + snippet URL  
4. Teprve potom vypnout „Discourage search engines“ / zapnout indexaci (samostatná položka checklistu)

---

## 4. WordPress Search-Replace (staging → produkce)

**From:**  
`https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io`

**To:**  
`https://sklospecial.cz`  
(pokud kanonický je www, použij `https://www.sklospecial.cz` konzistentně všude)

### Varianta A — plugin Better Search Replace

1. WP admin → Tools → Better Search Replace  
2. Replace above From → To  
3. Všechny tabulky (nebo aspoň `wp_options`, `wp_posts`, `wp_postmeta`)  
4. Nejdřív **Dry run**, pak ostrý běh  
5. Zaškrtni serialized data (BSR to umí)

### Varianta B — WP-CLI (v kontejneru Coolify WP)

```bash
wp search-replace \
  'https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io' \
  'https://sklospecial.cz' \
  --all-tables --precise --recurse-objects --skip-columns=guid
```

Pak:

```bash
wp option update home 'https://sklospecial.cz'
wp option update siteurl 'https://sklospecial.cz'
wp rewrite flush
wp cache flush
```

**Pozor:** GUID sloupec u postů typicky nepřepisuj (`--skip-columns=guid`). Media URL v obsahu a ACF ano.

---

## 5. SKLO_API_BASE / CONFIGURATOR_URL

### 5.1 Theme default (deploy theme po změně)

V `wp-theme/sklospecial/functions.php` je default:

```php
'https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io'
```

Po go-live změň na:

```php
'https://api.sklospecial.cz'
```

(`sklo_konfigurator_url()` z toho složí `/public/konfigurator.html`.)

Filtr `sklo_api_base` může přepsat URL bez deploy theme, pokud ho někde napojíš (mu-plugin / snippet).

### 5.2 Chat widget snippet ve WP (Custom HTML / WPCode / footer)

Widget **není** v theme enqueue — načítá se snippetem (viz MEMORY). Po DNS:

```html
<script>
  window.SKLO_API_BASE = 'https://api.sklospecial.cz';
  window.SKLO_CONFIGURATOR_URL = 'https://api.sklospecial.cz/public/konfigurator.html';
</script>
<script src="https://api.sklospecial.cz/public/chat-widget.js" defer></script>
```

### 5.3 Hardcoded katalog obrázky

V theme datech (`katalog-data.php`, `katalog-produkty-data.php`) jsou URL na staging API `/public/katalog-img/…`.  
Po go-live buď:

- Search-Replace v DB **a** v theme souborech:  
  `https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io` → `https://api.sklospecial.cz`  
- nebo znovu vygenerovat product data se správnou base URL (`scripts/remap_images_to_cropped.py`).

Binárky `app/public/katalog-img/*.jpg` musí zůstat na API volume / v kontejneru (gitignored) — DNS je nepřesune.

---

## 6. SSL (Let's Encrypt)

- Vydává **Coolify Traefik** po připojení FQDN + správném DNS A.
- Ověř v prohlížeči: zámek na `sklospecial.cz`, `www`, `api`.
- Mixed content: po Search-Replace nesmí zůstat `http://` ani staré sslip odkazy v HTML.

---

## 7. CORS allowlist (API env)

V Coolify → **sklospecial-api** → Environment:

```
CORS_ORIGIN=https://sklospecial.cz,https://www.sklospecial.cz
```

- Default v kódu bez env je `*` (`app/api/server.js`) — na produkci **omez**.
- Po změně env → Redeploy / Restart API.
- Smoke: chat z WP origin + `POST` konfigurátoru z prohlížeče (Network → bez CORS error).

Ostatní env (nemění DNS, ale go-live často naráží): `OPENAI_API_KEY`, `SMTP_*`, `MAIL_FROM` / `MAIL_TO` — viz `README.md` / `.env.example`.

---

## 8. Staging poznámka (ponechat)

| Co | Akce |
|----|------|
| sslip.io FQDN u WP i API | Nechat v Coolify Domains jako sekundární, dokud ostrý web stabilně běží |
| Indexace | Do skončení DNS + Search-Replace nechat vypnutou (`AGENTS.md` / `TODO.md`) |
| Starý bookmark sslip | Funguje paralelně; po jistotě odebrat z Coolify, ať LE a kanonické URL nepletou |
| Tento dokument | Po dokončení DNS zaškrtni níže |

Checklist položky 1:

- [ ] DNS A/CNAME u registrátora
- [ ] Coolify domains + LE (WP + API)
- [ ] WP Search-Replace + home/siteurl
- [ ] `sklo_api_base` / snippet `SKLO_API_*`
- [ ] Replace sslip v katalog URL
- [ ] `CORS_ORIGIN` + redeploy API
- [ ] Staging sslip stále dostupný jako fallback

---

## 9. Co dělá člověk vs co je připraveno

| Připraveno v repu | Musíš udělat ručně |
|-------------------|-------------------|
| Tento runbook (`docs/GO-LIVE-DNS.md`) | DNS záznamy u registrátora |
| Coolify UUID a staging URL | Připojení FQDN v Coolify UI |
| Přesné From/To pro Search-Replace | Spuštění BSR / WP-CLI |
| Snippety `SKLO_API_BASE` / CORS | Vložení do WP + Coolify env + redeploy |
| Mapování WP vs API vs katalog-img | Ověření v prohlížeči po LE |

**Coolify MCP:** při tvorbě tohoto dokumentu nebyl spolehlivě dostupný — domény jsme přes MCP nečetli ani neměnili.
