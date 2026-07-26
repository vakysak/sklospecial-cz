# Katalog vzorů a kování

Konfigurátor bere data z DB přes `GET /api/katalog`.

## Tabulky

| Tabulka | Účel |
|---------|------|
| `sklo_katalog_typy` | otočné / posuvné / … |
| `sklo_katalog_vzory` | vzory skla |
| `sklo_katalog_kovani` | lišty + kování (barva) |

## Pole pro fotky

- `image_url` — náhled ve výběru (čtverec / swatch)
- `preview_url` — volitelně velký náhled do studia
- dokud jsou prázdné, FE použije `css_class` (vzory) nebo `color_hex` (lišty)

## Jak doplnit reálné fotky

1. Nahraj JPG/WebP na WP media nebo do `app/public/katalog/`
2. UPDATE v DB, např.:

```sql
UPDATE sklo_katalog_vzory
SET image_url = 'https://…/vzory/matne.jpg',
    preview_url = 'https://…/vzory/matne-large.jpg'
WHERE slug = 'matne';

UPDATE sklo_katalog_kovani
SET image_url = 'https://…/kovani/cerne.jpg',
    color_hex = '#1a1a1a'
WHERE slug = 'cerne';
```

3. Nový vzor:

```sql
INSERT INTO sklo_katalog_vzory (slug, nazev, popis, image_url, css_class, sort_order, aktivni)
VALUES ('satinato', 'Satinato', 'Jemně matné', 'https://…/satinato.jpg', 'p-matne', 6, 1);
```

## Fotka prostoru + otvor podle zaměření

Ano — klient může:

1. **Vložit fotku prostoru** do náhledu  
2. **Posunout rámeček** na otvor ve fotce  
3. **Škálovat rohy** — poměr stran rámečku se bere z **nejmenší zaměřené šířky × výšky**  

Fotka na telefonu má jiný úhel než „pravý“ otvor — proto se **nečeká**, že fotka = přesné mm.  
Zaměření řídí **poměr** (a nabídku), rámeček na fotce jen **umístění vizualizace**.

Tohle není plné AR s perspektivou (zkreslení stěn). Pro Fázi 1 stačí osový rámeček + poměr z mm. Perspektivní 4-bodový warp lze doplnit později.

