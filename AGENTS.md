# Sklospeciál — agent context

## Scope

- Workspace **jen** pro sklospecial.cz (Coolify + WordPress + Node API).
- Nestřídej se stolařstvím / SEO landingem, pokud uživatel výslovně neřekne.

## Dokumentace

| Soubor | Obsah |
|--------|--------|
| `docs/FAZE-1-ZADANI.md` | Kompletní výrobní zadání Fáze 1 |
| `docs/MEMORY.md` | Krátký snapshot pro nová vlákna |
| `README.md` | Stav infrastruktury, odkazy, ID |
| `TODO.md` | Checklist (SMTP, Turnstile, …) |

## Infrastruktura

- Staging: sslip.io WordPress na Coolify (HTTPS).
- Ostrá doména `sklospecial.cz` ještě není.
- Indexace vypnutá do ostré domény.
- Secrets: MCP / Keychain / Coolify env — **ne** do gitu.

## Fáze 1 — co stavíme

1. Node API: konfigurátor, chat, upload, mail, `sklo_leads`
2. Frontend: 7krokový konfigurátor + floating chat
3. WP: theme/ACF, stránky dveří, realizace, návod na zaměření

## Brand

Tykat, česky, konkrétně. CTA „pošli rozměry a fotky“. Bez „luxusní / exkluzivní / prémiový“.
