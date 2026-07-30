<?php
/**
 * Katalog nabídky — sekce, ceny, copy.
 *
 * Ceny „od“: z katalog-produkty-data.php (45 % již v CSV), zaokrouhleno na 100 Kč.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Formátuje cenu pro zobrazení (mezery mezi tisíci).
 */
function sklo_format_cena_od(int $kc): string
{
    return 'od ' . number_format($kc, 0, ',', "\u{00a0}") . ' Kč';
}

/**
 * Poznámka pod orientačními cenami.
 */
function sklo_cena_note(): string
{
    return 'Orientační cena od — finální nabídka podle rozměrů a provedení.';
}

/**
 * Min cena z product data, nebo fallback.
 */
function sklo_katalog_min_price(string $slug, ?int $fallback = null): ?int
{
    $bundle = function_exists('sklo_produkty_for_slug') ? sklo_produkty_for_slug($slug) : null;
    if ($bundle && isset($bundle['min_price']) && $bundle['min_price'] !== null) {
        return (int) $bundle['min_price'];
    }
    return $fallback;
}

/**
 * Sample product image URL for a slug (first with image).
 */
function sklo_katalog_sample_image(string $slug, string $fallback = ''): string
{
    $bundle = function_exists('sklo_produkty_for_slug') ? sklo_produkty_for_slug($slug) : null;
    if ($bundle && !empty($bundle['products'])) {
        foreach ($bundle['products'] as $p) {
            if (!empty($p['image'])) {
                return (string) $p['image'];
            }
        }
    }
    return $fallback;
}

/**
 * First product image for a category section (subcategory thumb).
 */
function sklo_katalog_section_image(string $slug, string $section, string $fallback = ''): string
{
    if ($section === '') {
        return $fallback;
    }
    $bundle = function_exists('sklo_produkty_for_slug') ? sklo_produkty_for_slug($slug) : null;
    if ($bundle && !empty($bundle['products'])) {
        foreach ($bundle['products'] as $p) {
            if ((string) ($p['section'] ?? '') === $section && !empty($p['image'])) {
                return (string) $p['image'];
            }
        }
    }
    return $fallback;
}

/**
 * @return array<string, array<string, mixed>>
 */
function sklo_katalog(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }

    $uploads = content_url('uploads/2026/07');

    $od = static function (string $slug, ?int $fallback = null): ?string {
        $min = sklo_katalog_min_price($slug, $fallback);
        return $min !== null ? sklo_format_cena_od($min) : null;
    };

    $sec_od = static function (string $slug, string $section, ?int $fallback = null): ?string {
        $bundle = sklo_produkty_for_slug($slug);
        $mins = is_array($bundle['section_mins'] ?? null) ? $bundle['section_mins'] : [];
        $min = isset($mins[$section]) ? (int) $mins[$section] : $fallback;
        return $min !== null ? sklo_format_cena_od($min) : null;
    };

    $img = static function (string $slug, string $wp_file = '') use ($uploads): string {
        $remote = sklo_katalog_sample_image($slug);
        if ($remote !== '') {
            return $remote;
        }
        return $wp_file !== '' ? trailingslashit($uploads) . $wp_file : '';
    };

    $data = [
        'sklenene-dvere' => [
            'title'       => 'Skleněné dveře na míru',
            'eyebrow'     => 'Katalog',
            'lead'        => 'Vyrábíme skleněné dveře přesně podle tvého otvoru. Posuvné, kyvné, otevírané i celoskleněné — vyber kategorii a doladíš detaily ve studiu.',
            'seo_title'   => 'Skleněné dveře na míru — katalog | Sklospeciál',
            'seo_desc'    => 'Posuvné, kyvné, otevírané i celoskleněné dveře. Orientační ceny od, finální nabídka podle rozměrů. Navrhni si dveře online.',
            'hub'         => true,
            'price_from'  => $od('posuvne', 10100),
            // Hub card images: Quba catalog product photos (not Realizace gallery webps).
            'children'    => [
                [
                    'slug'  => 'posuvne',
                    'title' => 'Posuvné',
                    'text'  => 'Po stěně nebo do pouzdra. Ideální tam, kde každý centimetr hraje roli.',
                    // SklS-0101 — loftové LOFT-ART čiré
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_6254_700_700/IMG_9735-bezbarwne-ALLEGRO.jpg',
                    'price' => $od('posuvne', 10100),
                ],
                [
                    'slug'  => 'otocne',
                    'title' => 'Kyvné',
                    'text'  => 'Otevírání oběma směry (wahadłowe) — pivot / samozavírač, ne obyčejné křídlo na pantech.',
                    // SklS-0211 — kyvné wahadłowe čiré
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_7256_700_700/drzwi_wahadlowe_szklane_qubaglass.jpg',
                    'price' => $od('otocne', 9800),
                ],
                [
                    'slug'  => 'otevirane',
                    'title' => 'Otevírané',
                    'text'  => 'Křídlo na pantech — jasný pohyb, známé ovládání, široký výběr skel.',
                    // SklS-0241 — otevírané na pantech se zámkem
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_15467_700_700/drzwi-szklane-otwierane-na-zawiasach-z--zamkiem-bezbawrne.png',
                    'price' => $od('otevirane', 9700),
                ],
                [
                    'slug'  => 'dvere-se-zarubni',
                    'title' => 'Dveře se zárubní',
                    'text'  => 'Pevná, nastavitelná nebo hliníková zárubeň — podle stavby a požadovaného vzhledu.',
                    // SklS-0260 — pevná zárubeň čiré
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_6335_700_700/DRZWI_FUTRYNA_BEZBARWNE.jpg',
                    'price' => $od('dvere-se-zarubni', 10900),
                ],
                [
                    'slug'  => 'linie-luxe',
                    'title' => 'Linie Luxe',
                    'text'  => 'Nejvyšší třída v nabídce — jemnější detaily, větší prostor pro individuální návrh.',
                    // SklS-0314 — LUXE trubkové ledové sklo
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_1046_700_700/5627ef74720cc2eadb442f3c2f25c3de.jpg',
                    'price' => $od('linie-luxe'),
                ],
                [
                    'slug'  => 'rock-glass',
                    'title' => 'Rock Glass',
                    'text'  => 'Sklo s výraznou strukturou — světlo hraje, pohled zůstává soukromý.',
                    // SklS-0316 — ROCK GLASS čiré
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_15099_700_700/drzwi-szklane-rock-glass-bezbarwne.jpg',
                    'price' => $od('rock-glass', 11500),
                ],
                [
                    'slug'  => 'pricky-a-zabudovani',
                    'title' => 'Příčky a zabudování',
                    'text'  => 'Skleněné příčky, pevné výplně a zabudování do stěny nebo pouzdra.',
                    // SklS-0325 — zabudování kyvné + 2× pevná příčka
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_15418_700_700/zabudowa-szklana-ze-sciankami-stalymi-qubaglass.png',
                    'price' => $od('pricky-a-zabudovani'),
                ],
                [
                    'slug'  => 'vzory-skla',
                    'title' => 'Vzory skla',
                    'text'  => 'Čiré, matné, barevné i dekorativní vzory — přehledný výběr pro studio.',
                    // SklS-0002 — Design-Lux vzor proužky (jasný náhled vzoru na křídle)
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_15213_700_700/drzwi-szklane-design-lux-paski-pionowe.jpg',
                    'price' => $od('vzory-skla', 2100),
                ],
                [
                    'slug'  => 'laminovane',
                    'title' => 'Laminované',
                    'text'  => 'Vrstvené sklo pro vyšší bezpečnost a klidnější akustiku.',
                    // SklS-0008 — Design-Lux laminované černé
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_15179_700_700/drzwi-szklane-czaene-deisn.jpg',
                    'price' => $od('laminovane', 11700),
                ],
                [
                    'slug'  => 'skladem',
                    'title' => 'Skladem',
                    'text'  => 'Vybrané provedení s rychlou expedicí — když nechceš čekat na výrobu na míru.',
                    // SklS-0333 — Design-Lux matné skladem
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_15182_700_700/drzwi-szklane-przesuwne-matowe-design-lux.jpg',
                    'price' => $od('skladem', 7600),
                ],
                [
                    'slug'  => 'celosklenene',
                    'title' => 'Celoskleněné',
                    'text'  => 'Maximální průchod světla, minimální rám. Opticky propojí místnosti.',
                    // SklS-0228 — celoskleněné otevírané se zámkem (bez zárubně)
                    'image' => 'https://qubaglass.pl/environment/cache/images/productGfx_9689_700_700/drzwi_szklane_z_zamkiem_qubaglass.jpg',
                    'price' => null,
                ],
            ],
            'uploads' => $uploads,
        ],

        'posuvne' => [
            'title'      => 'Posuvné skleněné dveře',
            'eyebrow'    => 'Katalog · Posuvné',
            'lead'       => 'Posuvné dveře šetří místo a nechají prostor působit vzdušněji. Níže najdeš systémy i konkrétní produkty — od Design-Lux po pouzdro.',
            'seo_title'  => 'Posuvné skleněné dveře — systémy a ceny od | Sklospeciál',
            'seo_desc'   => 'Design-Lux, Ultra Slim, Loft, trubkový systém i do pouzdra. Orientační ceny od, finální nabídka podle rozměrů.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('posuvne', 10100),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'design-lux',
                    'title' => 'Design-Lux',
                    'lead'  => 'Nejoblíbenější posuvný systém v nabídce. Čistá linie, spolehlivý posuv po stěně.',
                    'price' => $sec_od('posuvne', 'design-lux'),
                    'image' => 'posuvne-sklenene-dvere-matne-sklo.webp',
                ],
                [
                    'id'    => 'ultra-slim',
                    'title' => 'Ultra Slim',
                    'lead'  => 'Tenký profil, který ustoupí sklu — lehčí vzhled, méně kovu v pohledu.',
                    'price' => $sec_od('posuvne', 'ultra-slim'),
                    'image' => 'sklenene-dvere-s-matnym-sklem.webp',
                ],
                [
                    'id'    => 'loft',
                    'title' => 'Loft',
                    'lead'  => 'Výraznější průmyslový charakter — černé prvky, pevnější linie.',
                    'price' => $sec_od('posuvne', 'loft'),
                    'image' => 'sklenene-dvere-moderny-interier.webp',
                ],
                [
                    'id'    => 'trubkovy-system',
                    'title' => 'Trubkový systém',
                    'lead'  => 'Posuv s trubkovým madlem a robustnějším hardwarem.',
                    'price' => $sec_od('posuvne', 'trubkovy-system'),
                    'image' => 'sklenene-dvere-s-modernim-madlem.webp',
                ],
                [
                    'id'    => 'do-pouzdra',
                    'title' => 'Do pouzdra',
                    'lead'  => 'Křídlo zajíždí do stavebního pouzdra — když jsou dveře otevřené, zůstane čistý průchod.',
                    'price' => $sec_od('posuvne', 'do-pouzdra'),
                    'image' => 'interier-sklene-dvere-masivni-drevo.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'otocne' => [
            'title'      => 'Kyvné dveře (otočné)',
            'eyebrow'    => 'Katalog · Kyvné',
            'lead'       => 'Kyvné (wahadłowe) skleněné dveře — otevírání oběma směry na pivotu nebo samozavírači. Nejsou to obyčejná skleněná křídla na pantech; ty najdeš v kategorii Otevírané.',
            'seo_title'  => 'Kyvné skleněné dveře (otočné) | Sklospeciál',
            'seo_desc'   => 'Kyvné skleněné dveře wahadłowe — otevírání oběma směry. Orientační cena od, finální nabídka podle rozměrů.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('otocne', 9800),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'otocne',
                    'title' => 'Kyvné dveře',
                    'lead'  => 'Systém s pohybem oběma směry (často samozavírač). Vhodné do průchodů, kde potřebuješ volný průchod z obou stran.',
                    'price' => $od('otocne', 9800),
                    'image' => 'prosklene-dvere-na-miru.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'otevirane' => [
            'title'      => 'Otevírané skleněné dveře',
            'eyebrow'    => 'Katalog · Otevírané',
            'lead'       => 'Otevírané dveře na pantech (jedním směrem) — jasný pohyb, známé ovládání. Na rozdíl od kyvných (wahadłowe) se neotvírají oběma směry.',
            'seo_title'  => 'Otevírané skleněné dveře | Sklospeciál',
            'seo_desc'   => 'Otevírané skleněné dveře na míru. Vyber sklo, kování a rozměry.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('otevirane', 9700),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'otevirane',
                    'title' => 'Otevírané dveře',
                    'lead'  => 'Křídlo se otevírá do prostoru. Dobře sedí k zárubním i k celoskleněným řešením.',
                    'price' => $od('otevirane', 9700),
                    'image' => 'sklenene-dvere-do-obyvaciho-pokoje.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'dvere-se-zarubni' => [
            'title'      => 'Dveře se zárubní',
            'eyebrow'    => 'Katalog · Zárubeň',
            'lead'       => 'Skleněné dveře v zárubni — pevné ukotvení do stavebního otvoru. Pevná, nastavitelná nebo hliníková varianta.',
            'seo_title'  => 'Skleněné dveře se zárubní | Sklospeciál',
            'seo_desc'   => 'Pevná, nastavitelná i hliníková zárubeň pro skleněné dveře.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('dvere-se-zarubni', 10900),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'pevna',
                    'title' => 'Pevná zárubeň',
                    'lead'  => 'Stabilní řešení pro přesně připravený otvor.',
                    'price' => $sec_od('dvere-se-zarubni', 'pevna'),
                ],
                [
                    'id'    => 'nastavitelna',
                    'title' => 'Nastavitelná zárubeň',
                    'lead'  => 'Pomůže u tloušťky stěny, která není úplně standardní.',
                    'price' => $sec_od('dvere-se-zarubni', 'nastavitelna'),
                ],
                [
                    'id'    => 'hlinikova',
                    'title' => 'Hliníková zárubeň',
                    'lead'  => 'Lehký, přesný profil — často v černé nebo eloxované úpravě.',
                    'price' => $sec_od('dvere-se-zarubni', 'hlinikova'),
                ],
            ],
            'uploads' => $uploads,
        ],

        'linie-luxe' => [
            'title'      => 'Linie Luxe',
            'eyebrow'    => 'Katalog · Linie Luxe',
            'lead'       => 'Linie Luxe — nejvyšší třída v nabídce. Více prostoru pro individuální návrh a jemnější detaily.',
            'seo_title'  => 'Linie Luxe — skleněné dveře | Sklospeciál',
            'seo_desc'   => 'Linie Luxe: nejvyšší třída skleněných dveří. Nabídka podle projektu.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('linie-luxe'),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'luxe',
                    'title' => 'Linie Luxe',
                    'lead'  => 'Když chceš dveře, které sedí k celému projektu — ne jen k jednomu otvoru.',
                    'price' => $od('linie-luxe'),
                    'image' => 'sklenene-dvere-na-miru.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'rock-glass' => [
            'title'      => 'Rock Glass',
            'eyebrow'    => 'Katalog · Rock Glass',
            'lead'       => 'Sklo se strukturou, která láme světlo a drží soukromí. Sedí do koupelen, šaten i obytných místností.',
            'seo_title'  => 'Rock Glass — strukturované sklo | Sklospeciál',
            'seo_desc'   => 'Rock Glass: skleněné dveře se strukturovaným sklem.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('rock-glass', 11500),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'rock-glass',
                    'title' => 'Rock Glass',
                    'lead'  => 'Výrazná textura, měkký průhled. Kombinuje se s posuvnými i otevíranými systémy.',
                    'price' => $od('rock-glass', 11500),
                    'image' => 'sklenene-dvere-s-matnym-sklem.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'pricky-a-zabudovani' => [
            'title'      => 'Skleněné příčky a zabudování',
            'eyebrow'    => 'Katalog · Příčky',
            'lead'       => 'Odděl místnosti bez těžké stěny. Skleněné příčky a zabudování pustí světlo dál.',
            'seo_title'  => 'Skleněné příčky a zabudování | Sklospeciál',
            'seo_desc'   => 'Skleněné příčky, pevné výplně a zabudování. Nabídka podle rozměrů.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('pricky-a-zabudovani'),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'pricky',
                    'title' => 'Skleněné příčky',
                    'lead'  => 'Pevné nebo s dveřním křídlem. Hodí se do open-space, pracovny i bytu.',
                    'price' => $od('pricky-a-zabudovani'),
                    'image' => 'moderni-interier-sklene-prcky-dreveny-stolek.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'vzory-skla' => [
            'title'      => 'Vzory skla',
            'eyebrow'    => 'Katalog · Sklo',
            'lead'       => 'Od čirého po matné a dekorativní vzory. Níže najdeš barevné i matné vzory z katalogu — ve studiu si je vyzkoušíš na dveřích.',
            'seo_title'  => 'Vzory skla pro skleněné dveře | Sklospeciál',
            'seo_desc'   => 'Přehled vzorů skla: matné, barevné a dekorativní. Vyber ve studiu nebo se zeptej.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('vzory-skla', 2100),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'barevne',
                    'title' => 'Barevné vzory skla',
                    'lead'  => 'Tónované a barevné výplně pro dveře, které mají nést barvu interiéru.',
                    'price' => $sec_od('vzory-skla', 'barevne'),
                ],
                [
                    'id'    => 'matne',
                    'title' => 'Matné vzory skla',
                    'lead'  => 'Soukromí bez úplného zatemnění. Mat propustí světlo a zjemní pohled.',
                    'price' => $sec_od('vzory-skla', 'matne'),
                ],
            ],
            'uploads' => $uploads,
        ],

        'laminovane' => [
            'title'      => 'Laminované dveře',
            'eyebrow'    => 'Katalog · Laminované',
            'lead'       => 'Vrstvené sklo drží pohromadě i při poškození. Hodí se tam, kde chceš vyšší bezpečnost a klidnější akustiku.',
            'seo_title'  => 'Laminované skleněné dveře | Sklospeciál',
            'seo_desc'   => 'Laminované skleněné dveře. Orientační cena od — finální nabídka podle rozměrů.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('laminovane', 11700),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'laminovane',
                    'title' => 'Laminované sklo',
                    'lead'  => 'Dvě tabulě spojené fólií. Při prasknutí zůstane výplň v celku.',
                    'price' => $od('laminovane', 11700),
                    'image' => 'sklenene-dvere-uzavrene-s-handlem.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'skladem' => [
            'title'      => 'Skladem — rychlá expedice',
            'eyebrow'    => 'Katalog · Skladem',
            'lead'       => 'Vybrané provedení připravené k rychlejší expedici. Když nechceš čekat na plnou výrobu na míru.',
            'seo_title'  => 'Skladem — skleněné dveře s rychlou expedicí | Sklospeciál',
            'seo_desc'   => 'Skleněné dveře skladem. Rychlejší expedice vybraných provedení.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('skladem', 7600),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'skladem',
                    'title' => 'Rychlá expedice',
                    'lead'  => 'Skladem držíme vybrané rozměry a skla. Není to celý katalog — spíš rychlá cesta ke standardnějšímu řešení.',
                    'price' => $od('skladem', 7600),
                    'image' => 'interier-sklene-dvere.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'celosklenene' => [
            'title'     => 'Celoskleněné dveře',
            'eyebrow'   => 'Katalog · Celoskleněné',
            'lead'      => 'Maximální průchod světla, minimální rám. Celoskleněné dveře opticky propojí místnosti — od čirého po matné a dekorativní sklo.',
            'seo_title' => 'Celoskleněné dveře na míru | Sklospeciál',
            'seo_desc'  => 'Celoskleněné dveře s minimálním rámem. Navrhni provedení online podle svého otvoru.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'    => 'celosklenene',
                    'title' => 'Celoskleněné řešení',
                    'lead'  => 'Když má být sklo hlavní. Rám ustoupí, světlo jde skrz. Můžeš kombinovat s kyvným i posuvným systémem.',
                    'image' => 'sklenene-dvere-do-obyvaciho-pokoje-3.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        // —— Top-level assortment beyond doors ——

        'sprchove-kouty' => [
            'title'      => 'Skleněné sprchové kouty',
            'eyebrow'    => 'Nabídka · Sprchy',
            'lead'       => 'Walk-in stěny, kouty s dveřmi i řešení na míru. Čiré, matné, grafika i laminát — kování chrom, černé nebo zlaté.',
            'seo_title'  => 'Skleněné sprchové kouty | Sklospeciál',
            'seo_desc'   => 'Skleněné sprchové kouty a walk-in stěny. Orientační ceny od, finální nabídka podle rozměrů.',
            'price_from' => $od('sprchove-kouty', 4600),
            'show_products' => true,
            'hero_image' => $img('sprchove-kouty'),
            'sections'   => [
                [
                    'id'    => 'sprchy',
                    'title' => 'Sprchové kouty a walk-in',
                    'lead'  => 'Od jednoduché walk-in stěny po kout s dveřmi a pevnou stěnou. Rozměry a kování doladíme podle koupelny.',
                    'price' => $od('sprchove-kouty', 4600),
                ],
            ],
            'uploads' => $uploads,
        ],

        'francouzske-balkony' => [
            'title'      => 'Francouzské balkony',
            'eyebrow'    => 'Nabídka · Balkony',
            'lead'       => 'Skleněné francouzské balkony — bezpečné, vzdušné a na míru otvoru. Od čirého skla po matné a dekorativní vzory.',
            'seo_title'  => 'Francouzské balkony ze skla | Sklospeciál',
            'seo_desc'   => 'Skleněné francouzské balkony. Orientační ceny od, nabídka podle rozměrů a skla.',
            'price_from' => $od('francouzske-balkony', 7100),
            'show_products' => true,
            'hero_image' => $img('francouzske-balkony'),
            'sections'   => [
                [
                    'id'    => 'balkony',
                    'title' => 'Francouzské balkony',
                    'lead'  => 'Kotvení, výška a typ skla podle stavby. Hotové sestavy i řešení na míru.',
                    'price' => $od('francouzske-balkony', 7100),
                ],
            ],
            'uploads' => $uploads,
        ],

        'zabradli' => [
            'title'      => 'Skleněné zábradlí',
            'eyebrow'    => 'Nabídka · Zábradlí',
            'lead'       => 'Zábradlí DIY, sestavy s montáží i profily. Sklo drží výhled a světlo — ty zvolíš systém a výšku.',
            'seo_title'  => 'Skleněné zábradlí | Sklospeciál',
            'seo_desc'   => 'Skleněné zábradlí DIY, s montáží a profily. Orientační ceny od.',
            'price_from' => $od('zabradli', 2800),
            'show_products' => true,
            'hero_image' => $img('zabradli'),
            'sections'   => [
                [
                    'id'    => 'diy',
                    'title' => 'Zábradlí DIY',
                    'lead'  => 'Sestavy připravené k montáži svépomocí. Jasný základ, když chceš mít instalaci pod kontrolou.',
                    'price' => $sec_od('zabradli', 'diy'),
                ],
                [
                    'id'    => 's-montazi',
                    'title' => 'Zábradlí s montáží',
                    'lead'  => 'Komplet včetně montáže — domluvíme termín a postup podle lokality.',
                    'price' => $sec_od('zabradli', 's-montazi'),
                ],
                [
                    'id'    => 'profily',
                    'title' => 'Profily na zábradlí',
                    'lead'  => 'Profily, spoje a drobné díly pro skleněné zábradlí.',
                    'price' => $sec_od('zabradli', 'profily'),
                ],
                [
                    'id'    => 'profily-fix',
                    'title' => 'Profily FIX',
                    'lead'  => 'FIX profily a příslušenství pro pevné uchycení skla.',
                    'price' => $sec_od('zabradli', 'profily-fix'),
                ],
            ],
            'uploads' => $uploads,
        ],

        'strisky' => [
            'title'      => 'Skleněné stříšky',
            'eyebrow'    => 'Nabídka · Stříšky',
            'lead'       => 'Stříšky skladem, systémové, s okapem, s černým kováním, na táhlech i konzolách. Chrání vstup a nechá světlo projít.',
            'seo_title'  => 'Skleněné stříšky | Sklospeciál',
            'seo_desc'   => 'Skleněné stříšky na konzolách, táhlech, s okapem i skladem. Orientační ceny od.',
            'price_from' => $od('strisky', 7100),
            'show_products' => true,
            'hero_image' => $img('strisky'),
            'sections'   => [
                [
                    'id'    => 'skladem',
                    'title' => 'Stříšky skladem',
                    'lead'  => 'Vybrané rozměry připravené k rychlejší dodávce.',
                    'price' => $sec_od('strisky', 'skladem'),
                ],
                [
                    'id'    => 'systemove',
                    'title' => 'Stříšky systémové',
                    'lead'  => 'Systémová řešení včetně bočních panelů a napojení.',
                    'price' => $sec_od('strisky', 'systemove'),
                ],
                [
                    'id'    => 's-okapem',
                    'title' => 'Stříšky s okapem',
                    'lead'  => 'Odvedení vody okapem — praktické u vstupů a teras.',
                    'price' => $sec_od('strisky', 's-okapem'),
                ],
                [
                    'id'    => 'cerne-kovani',
                    'title' => 'Stříšky s černým kováním',
                    'lead'  => 'Výrazné černé kování — sedí k soudobým fasádám.',
                    'price' => $sec_od('strisky', 'cerne-kovani'),
                ],
                [
                    'id'    => 'tahla',
                    'title' => 'Stříšky na táhlech',
                    'lead'  => 'Kotvení táhly — lehčí vizuální působení u vyšších vstupů.',
                    'price' => $sec_od('strisky', 'tahla'),
                ],
                [
                    'id'    => 'konzoly',
                    'title' => 'Stříšky na konzolách',
                    'lead'  => 'Klasické konzoly pod sklem — pevné a srozumitelné řešení.',
                    'price' => $sec_od('strisky', 'konzoly'),
                ],
            ],
            'uploads' => $uploads,
        ],

        'zrcadla' => [
            'title'      => 'Zrcadla',
            'eyebrow'    => 'Nabídka · Zrcadla',
            'lead'       => 'Zrcadla na míru — broušené hrany, přesný rozměr. Malá, ale praktická součást nabídky.',
            'seo_title'  => 'Zrcadla na míru | Sklospeciál',
            'seo_desc'   => 'Zrcadla na míru. Orientační cena od, finální nabídka podle rozměrů.',
            'price_from' => $od('zrcadla', 2100),
            'show_products' => true,
            'hero_image' => $img('zrcadla'),
            'sections'   => [
                [
                    'id'    => 'zrcadla',
                    'title' => 'Zrcadla na míru',
                    'lead'  => 'Broušené zrcadlo podle tvých rozměrů. Napiš rozměr a domluvíme nabídku.',
                    'price' => $od('zrcadla', 2100),
                ],
            ],
            'uploads' => $uploads,
        ],
    ];

    return $data;
}

/**
 * Vrátí data kategorie podle slug stránky.
 *
 * @return array<string, mixed>|null
 */
function sklo_katalog_for_slug(string $slug): ?array
{
    $all = sklo_katalog();
    return $all[$slug] ?? null;
}

/**
 * URL zpět na rodiče katalogu.
 */
function sklo_katalog_parent_url(array $cat): string
{
    $parent = (string) ($cat['parent'] ?? '');
    if ($parent === 'sklenene-dvere') {
        return home_url('/sklenene-dvere/');
    }
    if ($parent !== '') {
        return home_url('/' . $parent . '/');
    }
    return home_url('/');
}
