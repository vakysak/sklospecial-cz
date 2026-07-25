# Sklospeciál.cz

Fáze 1: skleněné dveře — konfigurátor + AI asistent.

## Live URL

| Služba | URL |
|--------|-----|
| WordPress | https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io |
| API | https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io |
| Health | https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/api/health |
| Konfigurátor | https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/konfigurator.html |
| Coolify | http://46.225.122.108:8000/ |

### WP stránky

- [/sklenene-dvere/](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/sklenene-dvere/)
- [/sklenene-dvere/posuvne/](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/sklenene-dvere/posuvne/)
- [/sklenene-dvere/otocne/](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/sklenene-dvere/otocne/)
- [/sklenene-dvere/celosklenene/](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/sklenene-dvere/celosklenene/)
- [/navod-na-zamereni/](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/navod-na-zamereni/)
- [/realizace/](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/realizace/)
- [/kontakt/](https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/kontakt/)

## Coolify IDs

| Resource | ID |
|----------|-----|
| Project | `f11nd5n7lf5j9mhukibhxvsq` |
| WordPress service | `jzxqv0aq7w5lf4f12nkwgj00` |
| API app | `c93wrq6ujvo02103pn26bxbr` |
| Leads DB | `nwqi2e98unof1x0daqk3bh2c` |

## Repo

https://github.com/vakysak/sklospecial-cz (větev `sklospecial`)

## Co doplnit v Coolify → sklospecial-api → Environment

```
OPENAI_API_KEY=
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
```

Pak redeploy API. Chat a maily z konfigurátoru začnou fungovat.

## Lokálně

```bash
cd app && npm run dev
```

Dokumentace: `docs/FAZE-1-ZADANI.md`
