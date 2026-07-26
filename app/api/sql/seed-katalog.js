'use strict';

/**
 * Výchozí katalog — později nahraď reálnými fotkami (image_url / preview_url).
 * css_class / color_hex slouží jako vizuální fallback v konfigurátoru.
 */

const SEED_TYPY = [
  { slug: 'otocne', nazev: 'Otočné', popis: 'Klasické otevírání do místnosti', thumb_class: 'thumb-otocne', sort_order: 1 },
  { slug: 'posuvne_stena', nazev: 'Posuvné na stěnu', popis: 'Křídlo pojede podél stěny', thumb_class: 'thumb-posuvne_stena', sort_order: 2 },
  { slug: 'posuvne_pouzdro', nazev: 'Posuvné do pouzdra', popis: 'Dveře zmizí ve stěně', thumb_class: 'thumb-posuvne_pouzdro', sort_order: 3 },
  { slug: 'dvoukridle', nazev: 'Dvoukřídlé', popis: 'Širší otvor, dvě křídla', thumb_class: 'thumb-dvoukridle', sort_order: 4 },
];

const SEED_VZORY = [
  { slug: 'cire', nazev: 'Čiré', popis: 'Maximum světla', css_class: 'p-cire', sort_order: 1 },
  { slug: 'matne', nazev: 'Matné / satén', popis: 'Soukromí bez tmy', css_class: 'p-matne', sort_order: 2 },
  { slug: 'pruhy', nazev: 'Pískované pruhy', popis: 'Dekorativní pruhy', css_class: 'p-pruhy', sort_order: 3 },
  { slug: 'kanafas', nazev: 'Kanárek / fluted', popis: 'Svislé rýhování', css_class: 'p-kanafas', sort_order: 4 },
  { slug: 'ornament', nazev: 'Ornament', popis: 'Dekorativní vzor', css_class: 'p-ornament', sort_order: 5 },
  { slug: 'nevim', nazev: 'Nevím, poraďte', popis: 'Doporučíme podle místa', css_class: 'p-nevim', sort_order: 99 },
];

const SEED_KOVANI = [
  { slug: 'cerne', nazev: 'Černá matná', popis: 'Lišta a madlo', color_hex: '#1a1a1a', sort_order: 1 },
  { slug: 'nerez', nazev: 'Nerez', popis: 'Kartáčovaný kov', color_hex: '#c5ccd1', sort_order: 2 },
  { slug: 'hlinik', nazev: 'Hliník stříbrný', popis: 'Světlá lišta', color_hex: '#aeb6bc', sort_order: 3 },
  { slug: 'zlate', nazev: 'Mosaz / zlatá', popis: 'Teplý detail', color_hex: '#c4a35a', sort_order: 4 },
  { slug: 'bila', nazev: 'Bílá', popis: 'Do světlých interiérů', color_hex: '#f2f2f0', sort_order: 5 },
  { slug: 'nevim', nazev: 'Nevím, poraďte', popis: 'Ladíme ke sklu', color_hex: '#8899a3', sort_order: 99 },
];

module.exports = { SEED_TYPY, SEED_VZORY, SEED_KOVANI };
