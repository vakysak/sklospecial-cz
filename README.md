# Sklospeciál.cz

Samostatný workspace pro web **Sklospeciál** (WordPress na Coolify).  
Oddělený od projektu stolařství / SEO landingu.

## Stav (2026-07-25)

| Položka | Hodnota |
|--------|---------|
| Staging URL | https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io |
| Coolify UI | http://46.225.122.108:8000/ |
| WP admin | `/wp-admin` (uživatel `vakysak`) |
| Admin e-mail | `hampl@vakysak.cz` |
| HTTPS | zapnuto (sslip.io) |
| Indexace | vypnutá (`blog_public=0`) do ostré domény |

### Coolify (IDs)

| Resource | UUID / název |
|----------|----------------|
| Project | `sklospecial-cz` · `f11nd5n7lf5j9mhukibhxvsq` |
| Service | `sklospecial-wordpress` · `jzxqv0aq7w5lf4f12nkwgj00` |
| App (WP) | `y8auik6s2jgoy1nrdz3yfqlm` |
| Server | `localhost` · `tnfqwxaxq7mvpkhhq5hngak7` |

### Pluginy (aktivní)

- **Simple CAPTCHA – Cloudflare Turnstile** — login, registrace, reset hesla, komentáře (čeká na Site/Secret key)
- **FluentSMTP** — odesílání mailů (čeká na SMTP údaje)
- **Limit Login Attempts Reloaded** — ochrana přihlášení
- **Code Snippets** — bootstrap + options REST pro setup

## Co zbývá

1. **Cloudflare Turnstile** — Site Key + Secret Key → nastavit v WP
2. **SMTP** — host, port, user, heslo, From → FluentSMTP + test mail
3. Doména **sklospecial.cz** — DNS → `46.225.122.108`, FQDN v Coolify, zapnout indexaci
4. Obsah webu, téma, šablony

## Cursor / MCP

- Coolify MCP: `~/.cursor/mcp.json` → server `coolify`
- WP REST: Basic auth `vakysak` + heslo aplikace (neukládat do gitu)
- Stolařství WP MCP zůstává v druhém projektu (`SEO obrázky`)

## Bezpečnost

Do tohoto repa **nedávej** hesla, API tokeny ani app password.  
Citlivé věci jen do Cursor MCP / Keychain / Coolify env.
