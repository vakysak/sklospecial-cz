# Výrobní zadání: sklospecial.cz — Fáze 1

## Kontext projektu

| | |
|--|--|
| Klient | sklospecial.cz |
| Server | cx43 / `46.225.122.108` / WordPress na dočasné doméně |
| Cíl | První prodejní sekce: **skleněné dveře** + konfigurátor + OpenAI chat |
| Model | Prodej na dálku — zákazník pošle rozměry a fotky, firma připraví nabídku |

### Adaptace na stávající infrastrukturu (důležité)

WordPress už běží na **Coolify** (Traefik + SSL + MariaDB).  
**Neděláme** od nuly docker-compose s Nginx + WordPress, pokud to výslovně nepřehodíme.

| Zadání (ideál) | Realita teď |
|----------------|-------------|
| Nginx + LE | Coolify Traefik + SSL (sslip.io → později sklospecial.cz) |
| WP v Compose | Coolify služba `sklospecial-wordpress` |
| WP Mail SMTP | **FluentSMTP** (už nainstalováno) |
| Contact Form 7 | vlastní formulář / konfigurátor API |
| Node API | **nová Coolify služba** vedle WP, path `/api/` |

---

## Technický stack

- **CMS:** WordPress (nainstalován)
- **Theme:** vlastní WP theme nebo ACF + Timber (Twig); alternativa headless WP + Next.js
- **Pluginy:** ACF Pro, FluentSMTP, Turnstile, Limit Login Attempts
- **Backend:** Node.js / Express — konfigurátor, OpenAI asistent, leady, e-maily
- **DB:** WordPress MySQL/MariaDB + tabulka `sklo_leads`
- **Upload:** WP media nebo oddělený upload endpoint API
- **Proxy / SSL:** Coolify Traefik (ekvivalent Nginx + LE)

---

## Struktura webu — Fáze 1

| URL | Účel |
|-----|------|
| `/` | Homepage |
| `/sklenene-dvere/` | Hlavní sekce Skleněné dveře |
| `/sklenene-dvere/posuvne/` | Posuvné |
| `/sklenene-dvere/otocne/` | Otočné |
| `/sklenene-dvere/celosklenene/` | Celoskleněné |
| `/navod-na-zamereni/` | Návod na zaměření |
| `/realizace/` | Galerie realizací |
| `/kontakt/` | Kontakt + poptávka |

---

## WordPress setup

### CPT: Realizace

```php
register_post_type('realizace', [
    'label'       => 'Realizace',
    'public'      => true,
    'has_archive' => true,
    'supports'    => ['title', 'thumbnail', 'editor'],
    'rewrite'     => ['slug' => 'realizace'],
    'menu_icon'   => 'dashicons-images-alt2',
]);
```

### CPT: Produkty

```php
register_post_type('produkt', [
    'label'       => 'Produkty',
    'public'      => true,
    'has_archive' => false,
    'supports'    => ['title', 'thumbnail', 'editor', 'excerpt'],
    'rewrite'     => ['slug' => 'produkty'],
    'menu_icon'   => 'dashicons-admin-page',
]);
```

### ACF — Realizace detail

- `typ_dveri` — Select (otočné, posuvné, celoskleněné, příčka, zástěna)
- `typ_skla` — Select (čiré, matné, dekor, bezpečnostní)
- `lokalita` — Text
- `popis_realizace` — Textarea
- `fotogalerie` — Gallery

### ACF — stránka Skleněné dveře

- `uvodni_text` — Wysiwyg
- `vyhody` — Repeater (ikona, nadpis, popis)
- `typy_dveri` — Repeater (nazev, popis, obrazek, odkaz)
- `faq` — Repeater (otazka, odpoved)

---

## Konfigurátor skleněných dveří

Umístění: sekce na `/sklenene-dvere/` nebo samostatná stránka — vícekrokový formulář.

### Kroky

1. **Typ dveří** — Otočné / Posuvné na stěnu / Posuvné do pouzdra / Dvoukřídlé  
2. **Použití** — Byt·dům / Koupelna / Kancelář·komerční  
3. **Rozměry** — šířka 3×, výška 3×, hloubka stěny (mm)  
4. **Typ skla** — Čiré / Matné / Dekorativní / Nevím  
5. **Kování** — Černé matné / Nerez / Zlaté / Nevím  
6. **Montáž** — S montáží / Bez montáže  
7. **Fotky + kontakt** — 2–8 fotek (max 5 MB, jpg/png/webp), jméno, telefon, e-mail, město/PSČ, poznámka

### Frontend state

```js
const config = {
  krok: 1,
  typ_dveri: null,
  pouziti: null,
  rozmery: { sirka: [null, null, null], vyska: [null, null, null], hloubka: null },
  typ_skla: null,
  kovani: null,
  montaz: null,
  fotky: [],
  kontakt: { jmeno: '', telefon: '', email: '', mesto: '', poznamka: '' },
};
```

- Validace před dalším krokem (rozměry 300–3500 mm, e-mail, CZ/SK telefon)
- Progress bar, Zpět / Pokračovat / Odeslat poptávku
- Tech: Alpine.js nebo vanilla JS

### Backend

`POST /api/konfigurator/odeslat` — JSON + multipart fotky:

1. Validace  
2. Uložení fotek do `/uploads/poptavky/{datum}/{id}/`  
3. Lead do DB  
4. E-mail firmě (souhrn + přílohy)  
5. Potvrzení klientovi  
6. `{ success: true, id: 'XXXXX' }`

### Tabulka `sklo_leads`

```sql
CREATE TABLE sklo_leads (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    typ_dveri     VARCHAR(50),
    pouziti       VARCHAR(50),
    sirka_min     INT,
    sirka_max     INT,
    vyska_min     INT,
    vyska_max     INT,
    hloubka       INT,
    typ_skla      VARCHAR(50),
    kovani        VARCHAR(50),
    montaz        VARCHAR(20),
    jmeno         VARCHAR(100),
    telefon       VARCHAR(30),
    email         VARCHAR(150),
    mesto         VARCHAR(100),
    poznamka      TEXT,
    fotky         TEXT,
    stav          VARCHAR(30) DEFAULT 'novy',
    poznamka_int  TEXT
);
```

---

## OpenAI chat asistent

- Jméno: **Sklo asistent**
- Floating widget na stránkách sekce Skleněné dveře (jinde volitelně)
- Jen téma: skleněné dveře, zaměření, výběr, objednávka; mimo → kontakt / konfigurátor
- Tykat, česky, konkrétně, bez marketingových frází
- `POST /api/chat` — messages + session_id, stream, rate limit 20/IP/h, max 20 zpráv/session
- Model: gpt-4.1 nebo gpt-4o; klíč jen v `.env`
- CTA na konci: konfigurátor nebo kontakt

### System prompt (zkráceně)

Jsi asistent sklospecial.cz pro skleněné dveře. Pomáháš s typem dveří, zaměřením a poptávkou. Mimo téma → kontakt/formulář. Tykej, stručně, prakticky. Neví-li typ: kde dveře, místo kolem otvoru, otočné vs posuvné. Pro poptávku: 3× šířka/výška, fotky otvoru/stěny/podlahy, otevírání, lokalita.

---

## Návod na zaměření (`/navod-na-zamereni/`)

1. Šířka — 3 místa, bereme nejmenší  
2. Výška — 3 místa, bereme nejmenší  
3. Hloubka stěny  
4. Fotky (čelo, podlaha, strop, bok, prostor vedle)  
5. Co poslat + CTA: konfigurátor / PDF / e-mail  

PDF: A4, logo, kontakt, QR na konfigurátor.

---

## Node.js struktura

```
/app
  /api
    server.js
    /routes      chat.js, konfigurator.js, upload.js
    /services    openai.js, mailer.js, database.js, storage.js
    /middleware  rateLimit.js, validate.js, auth.js
    /templates   email-firma.html, email-klient.html
  /uploads/poptavky
  .env / .env.example
  package.json
  Dockerfile
```

Port API: **3001**. V Coolify: veřejná cesta `/api/` → Traefik na API službu.

---

## Brand voice

- Lidský, konkrétní, technicky jistý  
- Tykání  
- Bez marketingových frází  
- CTA: „pošli rozměry a fotky“  
- Zakázáno: luxusní, exkluzivní, prémiový  

### Inspirace (PL)

Realizace: Lustro i Szkło, MojeSzklo.pl, Super Szklarz  
Dveře: Quba Glass, Drzwi Globus, Interdoor  
Systémy: Mantion/SLID'UP, CDA Bufab, Morad, Vitrintec  

---

## Pořadí výroby (upravené pro Coolify)

1. ~~Nginx + SSL~~ → už Coolify Traefik; později ostrá doména  
2. Coolify: Node API služba (+ DB tabulka / stejná MariaDB)  
3. Node.js API — server + endpointy  
4. DB — `sklo_leads`  
5. Upload endpoint  
6. Mailer (nodemailer / stejné SMTP jako FluentSMTP)  
7. WP theme — základní struktura  
8. ACF — fields  
9–11. Stránky dveří  
12. Návod + PDF  
13–14. Konfigurátor FE + BE  
15–16. Chat widget FE + BE  
17. E2E test toku  
18. Bezpečnostní checklist  
19. Zálohy  
20. Ostrá doména sklospecial.cz  

---

## Bezpečnost — checklist

- [ ] SSH jen klíč, root login off, fail2ban, UFW 22/80/443  
- [ ] `.env` mimo git  
- [ ] OpenAI klíč jen server  
- [ ] Upload: typ, velikost, počet, UUID názvy  
- [ ] Rate limit chat + konfigurátor  
- [ ] CORS jen vlastní doména  
- [ ] Zálohy DB + uploads denně mimo server  
- [ ] SSL + WP updates, silná hesla, limit loginů (LLAR už je)
