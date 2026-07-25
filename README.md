# Sklospeciál.cz

Workspace pro **sklospecial.cz** — Fáze 1: skleněné dveře (konfigurátor + AI asistent).

## Dokumentace (zadávací kontext)

- **[docs/FAZE-1-ZADANI.md](docs/FAZE-1-ZADANI.md)** — kompletní výrobní zadání  
- **[docs/MEMORY.md](docs/MEMORY.md)** — krátký snapshot  
- **[TODO.md](TODO.md)** — checklist  

## Stav infrastruktury

| | |
|--|--|
| Staging | https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io |
| Coolify | http://46.225.122.108:8000/ |
| Server | `46.225.122.108` (cx43) |
| WP admin | uživatel `vakysak` · e-mail `hampl@vakysak.cz` |
| HTTPS | ano (sslip.io) |
| Indexace | vypnutá do ostré domény |

### Coolify IDs

| Resource | ID |
|----------|-----|
| Project `sklospecial-cz` | `f11nd5n7lf5j9mhukibhxvsq` |
| Service `sklospecial-wordpress` | `jzxqv0aq7w5lf4f12nkwgj00` |
| App wordpress | `y8auik6s2jgoy1nrdz3yfqlm` |

### WP pluginy (už aktivní)

FluentSMTP · Cloudflare Turnstile · Limit Login Attempts · Code Snippets  

*(Turnstile klíče + SMTP údaje ještě chybí.)*

## Stack Fáze 1

WordPress (obsah) + **Node/Express API** (konfigurátor, chat, leady) na Coolify Traefik — ne samostatný Nginx compose od nuly.

## Doporučený start

**Node.js API** (endpointy + DB + upload + mail), pak konfigurátor a chat, WP stránky paralelně.

## Bezpečnost

Do gitu nedávej hesla, API tokeny ani app password.
