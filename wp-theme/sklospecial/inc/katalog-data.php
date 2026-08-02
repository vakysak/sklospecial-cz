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
 * Concrete example anchor near „od X Kč“ on category/hub pages.
 * Catalog-based examples — not a binding quote.
 *
 * @return array{text: string}|null
 */
function sklo_cena_priklad(?string $slug = null): ?array
{
    $slug = $slug ?? '';
    $map = [
        'sklenene-dvere' => 'Příklad: posuvné dveře 90×210 cm, matné sklo, černá lišta — od 12 400 Kč',
        'posuvne'        => 'Příklad: posuvné dveře 90×210 cm, matné sklo, černá lišta — od 12 400 Kč',
        'otocne'         => 'Příklad: kyvné dveře 80×210 cm, čiré sklo — od 11 800 Kč',
        'otevirane'      => 'Příklad: otevírané dveře 70×210 cm, matné sklo — od 10 900 Kč',
        'dvere-se-zarubni' => 'Příklad: dveře se zárubní 80×210 cm, čiré sklo — od 12 900 Kč',
        'sprchove-kouty' => 'Příklad: Walk-In stěna 90×200 cm, chromové kování — od 4 600 Kč',
        'zabradli'       => 'Příklad: zábradlí s montáží na spigotech, běžný úsek — od 7 600 Kč',
        'strisky'        => 'Příklad: stříška ECO skladem 120×100 cm — od 7 100 Kč',
        'sklenene-pricky' => 'Příklad: pevná příčka + kyvné dveře ~150×220 cm — od 21 000 Kč',
        'pricky-a-zabudovani' => 'Příklad: pevná příčka + kyvné dveře ~150×220 cm — od 21 000 Kč',
        'francouzske-balkony' => 'Příklad: francouzský balkon 120×100 cm, čiré sklo — od 7 100 Kč',
    ];

    // Map child/alias slugs.
    $aliases = [
        'pricky' => 'sklenene-pricky',
        'balkony' => 'francouzske-balkony',
        'sprchy' => 'sprchove-kouty',
        'dvere' => 'sklenene-dvere',
    ];
    if (isset($aliases[$slug])) {
        $slug = $aliases[$slug];
    }
    if ($slug === '' || !isset($map[$slug])) {
        return null;
    }
    return ['text' => $map[$slug]];
}

/**
 * Render example price anchor (if available for slug).
 */
function sklo_render_cena_priklad(?string $slug = null, string $class = ''): void
{
    $ex = sklo_cena_priklad($slug);
    if ($ex === null) {
        return;
    }
    $classes = trim('sklo-cena-priklad ' . $class);
    echo '<p class="' . esc_attr($classes) . '">' . esc_html($ex['text']) . '</p>';
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
                $url = (string) $p['image'];
                return function_exists('sklo_public_asset_url') ? sklo_public_asset_url($url) : $url;
            }
        }
    }
    return function_exists('sklo_public_asset_url') ? sklo_public_asset_url($fallback) : $fallback;
}

/**
 * First product image for a category section (subcategory thumb).
 */
function sklo_katalog_section_image(string $slug, string $section, string $fallback = ''): string
{
    if ($section === '') {
        return function_exists('sklo_public_asset_url') ? sklo_public_asset_url($fallback) : $fallback;
    }
    $bundle = function_exists('sklo_produkty_for_slug') ? sklo_produkty_for_slug($slug) : null;
    if ($bundle && !empty($bundle['products'])) {
        foreach ($bundle['products'] as $p) {
            if ((string) ($p['section'] ?? '') === $section && !empty($p['image'])) {
                $url = (string) $p['image'];
                return function_exists('sklo_public_asset_url') ? sklo_public_asset_url($url) : $url;
            }
        }
    }
    return function_exists('sklo_public_asset_url') ? sklo_public_asset_url($fallback) : $fallback;
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
            'seo_title'   => 'Skleněné dveře na míru — posuvné, kyvné, otevírané | Sklospeciál',
            'seo_desc'    => 'Skleněné dveře na míru: posuvné, kyvné i otevírané. Pošli rozměry a fotky přes WhatsApp nebo poptávku. Výroba a montáž po ČR.',
            'seo_intro'   => [
                'Skleněné dveře na míru vyrábíme podle tvého otvoru — ne podle katalogového „nějak to sedne“. Posuvné po stěně nebo do pouzdra, kyvné (otevírání oběma směry) i klasické otevírané na pantech. Vybereš typ, sklo a kování; ve studiu si návrh složíš online.',
                'Stačí zaměřit šířku a výšku (ideálně tři měření) a poslat 2–3 fotky prostoru. Podle toho připravíme konkrétní nabídku — orientační ceny „od“ v katalogu jsou jen start. Finální číslo vždy podle rozměrů, typu skla a zvoleného kování.',
                'Pracujeme remote po celé ČR: zaměříš sám podle návodu, nebo přijedeme zaměřit. Výroba probíhá na míru, montáž domluvíme podle lokality. Bez showroomu — rovnou k věci.',
            ],
            'hub'         => true,
            'price_from'  => $od('posuvne', 10100),
            // Hub card images: Quba catalog product photos (not Realizace gallery webps).
            'children'    => [
                [
                    'slug'  => 'posuvne',
                    'title' => 'Posuvné',
                    'text'  => 'Po stěně nebo do pouzdra. Ideální tam, kde každý centimetr hraje roli.',
                    // SklS-0004 — Design-Lux matné na kolejnici
                    'image' => sklo_katalog_img_url('SklS-0004'),
                    'price' => $od('posuvne', 10100),
                ],
                [
                    'slug'  => 'otocne',
                    'title' => 'Kyvné',
                    'text'  => 'Otevírání oběma směry — pivot nebo samozavírač, ne obyčejné křídlo na pantech.',
                    // SklS-0194 — kyvné se samozavíračem, čiré
                    'image' => sklo_katalog_img_url('SklS-0194'),
                    'price' => $od('otocne', 9800),
                ],
                [
                    'slug'  => 'otevirane',
                    'title' => 'Otevírané',
                    'text'  => 'Křídlo na pantech — jasný pohyb, známé ovládání, široký výběr skel.',
                    // SklS-0240 — otevírané na pantech, matné, viditelné panty
                    'image' => sklo_katalog_img_url('SklS-0240'),
                    'price' => $od('otevirane', 9700),
                ],
                [
                    'slug'  => 'dvere-se-zarubni',
                    'title' => 'Dveře se zárubní',
                    'text'  => 'Pevná, nastavitelná nebo hliníková zárubeň — podle stavby a požadovaného vzhledu.',
                    // SklS-0303 — hliníková zárubeň čiré, černý rám
                    'image' => sklo_katalog_img_url('SklS-0303'),
                    'price' => $od('dvere-se-zarubni', 10900),
                ],
                [
                    'slug'  => 'linie-luxe',
                    'title' => 'Linie Luxe',
                    'text'  => 'Nejvyšší třída v nabídce — jemnější detaily, větší prostor pro individuální návrh.',
                    // SklS-0315 — LUXE trubkové zrcadlo fénické
                    'image' => sklo_katalog_img_url('SklS-0315'),
                    'price' => $od('linie-luxe'),
                ],
                [
                    'slug'  => 'rock-glass',
                    'title' => 'Rock Glass',
                    'text'  => 'Sklo s výraznou strukturou — světlo hraje, pohled zůstává soukromý.',
                    // SklS-0317 — ROCK GLASS laminované černé
                    'image' => sklo_katalog_img_url('SklS-0317'),
                    'price' => $od('rock-glass', 11500),
                ],
                [
                    'slug'  => 'pricky-a-zabudovani',
                    'title' => 'Příčky a zabudování',
                    'text'  => 'Skleněné příčky, pevné výplně a zabudování do stěny nebo pouzdra.',
                    // SklS-0326 — zabudování kyvné s pevnými příčkami
                    'image' => sklo_katalog_img_url('SklS-0326'),
                    'price' => $od('pricky-a-zabudovani'),
                ],
                [
                    'slug'  => 'vzory-skla',
                    'title' => 'Vzory skla',
                    'text'  => 'Čiré, matné, barevné i dekorativní vzory — přehledný výběr skel.',
                    // SklS-0335 — Design-Lux vzor proužky 1.2
                    'image' => sklo_katalog_img_url('SklS-0335'),
                    'price' => $od('vzory-skla', 2100),
                ],
                [
                    'slug'  => 'laminovane',
                    'title' => 'Laminované',
                    'text'  => 'Vrstvené sklo pro vyšší bezpečnost a klidnější akustiku.',
                    // SklS-0202 — laminované černé (lesk)
                    'image' => sklo_katalog_img_url('SklS-0202'),
                    'price' => $od('laminovane', 11700),
                ],
                [
                    'slug'  => 'skladem',
                    'title' => 'Skladem',
                    'text'  => 'Vybrané provedení s rychlou expedicí — když nechceš čekat na výrobu na míru.',
                    // SklS-0338 — Design-Lux bílý systém matné skladem
                    'image' => sklo_katalog_img_url('SklS-0338'),
                    'price' => $od('skladem', 7600),
                ],
                [
                    'slug'  => 'celosklenene',
                    'title' => 'Celoskleněné',
                    'text'  => 'Maximální průchod světla, minimální rám. Opticky propojí místnosti.',
                    // SklS-0229 — celoskleněné čiré, kotvené nahoře a dole
                    'image' => sklo_katalog_img_url('SklS-0229'),
                    'price' => sklo_format_cena_od(14289),
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
                    'image' => sklo_katalog_img_url('SklS-0333'),  // SklS-0333
                ],
                [
                    'id'    => 'ultra-slim',
                    'title' => 'Ultra Slim',
                    'lead'  => 'Tenký profil, který ustoupí sklu — lehčí vzhled, méně kovu v pohledu.',
                    'price' => $sec_od('posuvne', 'ultra-slim'),
                    'image' => sklo_katalog_img_url('SklS-0085'),  // SklS-0085
                ],
                [
                    'id'    => 'loft',
                    'title' => 'Loft',
                    'lead'  => 'Výraznější průmyslový charakter — černé prvky, pevnější linie.',
                    'price' => $sec_od('posuvne', 'loft'),
                    'image' => sklo_katalog_img_url('SklS-0101'),  // SklS-0101
                ],
                [
                    'id'    => 'trubkovy-system',
                    'title' => 'Trubkový systém',
                    'lead'  => 'Posuv s trubkovým madlem a robustnějším hardwarem.',
                    'price' => $sec_od('posuvne', 'trubkovy-system'),
                    'image' => sklo_katalog_img_url('SklS-0144'),  // SklS-0144
                ],
                [
                    'id'    => 'do-pouzdra',
                    'title' => 'Do pouzdra',
                    'lead'  => 'Křídlo zajíždí do stavebního pouzdra — když jsou dveře otevřené, zůstane čistý průchod.',
                    'price' => $sec_od('posuvne', 'do-pouzdra'),
                    'image' => sklo_katalog_img_url('SklS-0153'),
                ],
            ],
            'uploads' => $uploads,
        ],

        'otocne' => [
            'title'      => 'Kyvné dveře (otočné)',
            'eyebrow'    => 'Katalog · Kyvné',
            'lead'       => 'Kyvné skleněné dveře — otevírání oběma směry na pivotu nebo samozavírači. Nejsou to obyčejná skleněná křídla na pantech; ty najdeš v kategorii Otevírané.',
            'seo_title'  => 'Kyvné skleněné dveře (otočné) | Sklospeciál',
            'seo_desc'   => 'Kyvné skleněné dveře — otevírání oběma směry. Orientační cena od, finální nabídka podle rozměrů.',
            'parent'     => 'sklenene-dvere',
            'price_from' => $od('otocne', 9800),
            'show_products' => true,
            'sections'   => [
                [
                    'id'    => 'otocne',
                    'title' => 'Kyvné dveře',
                    'lead'  => 'Systém s pohybem oběma směry (často samozavírač). Vhodné do průchodů, kde potřebuješ volný průchod z obou stran.',
                    'price' => $od('otocne', 9800),
                    'image' => sklo_katalog_img_url('SklS-0194'),
                ],
            ],
            'uploads' => $uploads,
        ],

        'otevirane' => [
            'title'      => 'Otevírané skleněné dveře',
            'eyebrow'    => 'Katalog · Otevírané',
            'lead'       => 'Otevírané dveře na pantech (jedním směrem) — jasný pohyb, známé ovládání. Na rozdíl od kyvných se neotvírají oběma směry.',
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
                    'image' => sklo_katalog_img_url('SklS-0240'),
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
            'seo_desc'  => 'Celoskleněné dveře s minimálním rámem. Pošli rozměry a fotky — připravíme nabídku podle otvoru.',
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
            'seo_title'  => 'Skleněné sprchové kouty na míru | Walk-in, rohové | Sklospeciál',
            'seo_desc'   => 'Skleněné sprchové kouty a walk-in stěny na míru. Pošli rozměry a fotky — nabídka podle koupelny. Montáž po celé ČR.',
            'seo_intro'  => [
                'Skleněné sprchové kouty a walk-in stěny vyrábíme na míru podle koupelny. Walk-in bez klasických dveří, rohové kouty i zástěny — čiré, matné, s grafikou nebo laminátem. Kování chrom, černé nebo zlaté podle toho, co sedí k obkladu a baterii.',
                'Nejdřív pošli rozměry (šířka × výška, ideálně i hloubku vaničky) a fotky prostoru. Podle toho připravíme nabídku — orientační ceny v katalogu jsou „od“, finální číslo vždy podle skla, kování a přesného střihu. Nemusíš hádat z e-shopu; sklo střihneme na míru.',
                'Pracujeme online a montujeme po celé ČR. Zaměříš sám podle návodu, nebo přijedeme zaměřit. Bez showroomu — rovnou výroba a termín montáže podle lokality.',
            ],
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
            'seo_title'  => 'Francouzské balkony ze skla na míru | Sklospeciál',
            'seo_desc'   => 'Skleněné francouzské balkony na míru. Pošli rozměry a fotky otvoru — nabídka podle skla a kotvení. Montáž po ČR.',
            'seo_intro'  => [
                'Francouzské balkony ze skla drží bezpečnost u francouzského okna a nechají světlo projít. Vyrábíme je na míru otvoru — čiré, matné i dekorativní sklo, kotvení podle stavby.',
                'Pošli šířku, výšku a fotky otvoru (ideálně i detail parapetu). Připravíme nabídku podle typu skla a kotvení; montáž domluvíme po ČR. Bez showroomu — online podklady stačí ke startu.',
            ],
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

        'sklenene-pricky' => [
            'title'      => 'Skleněné příčky na míru',
            'eyebrow'    => 'Nabídka · Příčky',
            'lead'       => 'Odděl místnosti bez těžké stěny. Skleněné příčky a zabudování pustí světlo dál — zaměříš sám, my vyrobíme.',
            'seo_title'  => 'Skleněné příčky na míru — odděl prostor světlem | Sklospeciál',
            'seo_desc'   => 'Skleněné příčky a zabudování na míru. Pošli rozměry a fotky — online zaměření, výroba, montáž po ČR.',
            'seo_intro'  => [
                'Skleněné příčky oddělí místnosti bez těžké zděné stěny. Pevné výplně i sestavy s dveřním křídlem — světlo jde dál, prostor zůstane vzdušný. Hodí se do open-space, pracovny i bytu.',
                'Zaměříš šířku a výšku (nebo pošleš fotky a domluvíme zaměření). My připravíme nabídku a vyrobíme na míru. Montáž po celé ČR, bez showroomu — pracujeme online.',
            ],
            'price_from' => $od('pricky-a-zabudovani'),
            'show_products' => true,
            'hero_image' => $img('pricky-a-zabudovani'),
            'sections'   => [
                [
                    'id'    => 'pricky',
                    'title' => 'Skleněné příčky',
                    'lead'  => 'Pevné nebo s dveřním křídlem. Hodí se do open-space, pracovny i bytu.',
                    'price' => $od('pricky-a-zabudovani'),
                    'image' => sklo_katalog_img_url('SklS-0326'),
                ],
            ],
            'uploads' => $uploads,
        ],

        'zabradli' => [
            'title'      => 'Skleněné zábradlí',
            'eyebrow'    => 'Nabídka · Zábradlí',
            'lead'       => 'Zábradlí DIY, sestavy s montáží i profily. Sklo drží výhled a světlo — ty zvolíš systém a výšku.',
            'seo_title'  => 'Skleněné zábradlí na míru — DIY i s montáží | Sklospeciál',
            'seo_desc'   => 'Skleněné zábradlí na míru: DIY sestavy i s montáží, profily a kotvení. Pošli rozměry a fotky — nabídka podle projektu.',
            'seo_intro'  => [
                'Skleněné zábradlí nechá schodiště i galerii otevřené a zároveň drží bezpečnostní funkci. Můžeš zvolit DIY sestavu k montáži svépomocí, nebo komplet včetně montáže. Profily, kotvení a výšku skla doladíme podle stavby.',
                'Pošli rozměry (délka, výška, typ kotvení) a fotky místa. Připravíme nabídku podle systému — orientační ceny „od“ v katalogu jsou start, finální číslo podle skla a délky. Montáž po ČR domluvíme individuálně.',
                'Pracujeme online: zaměření podle návodu nebo na místě, výroba na míru, doprava a montáž podle lokality. Bez showroomu — rovnou k projektu.',
            ],
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
            'seo_title'  => 'Skleněné stříšky na míru — konzoly, táhla, okap | Sklospeciál',
            'seo_desc'   => 'Skleněné stříšky na konzolách, táhlech, s okapem i skladem. Pošli rozměry vstupu — nabídka a montáž po ČR.',
            'seo_intro'  => [
                'Skleněné stříšky chrání vstup a nechají světlo projít. Konzoly, táhla, systémové sestavy, varianty s okapem i černým kováním — podle fasády a šířky vstupu. Část rozměrů skladem, zbytek na míru.',
                'Pošli šířku, hloubku a fotky vstupu. Připravíme nabídku včetně kotvení; dopravu a montáž domluvíme po ČR. Online proces bez showroomu.',
            ],
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

/**
 * SEO intro paragraphs for catalogue hubs (above product / category grids).
 *
 * @param array<string, mixed> $cat
 */
function sklo_render_katalog_seo_intro(array $cat): void
{
    $paras = $cat['seo_intro'] ?? null;
    if (!is_array($paras) || $paras === []) {
        return;
    }
    echo '<section class="sklo-section sklo-katalog-seo-intro" aria-label="Úvod kategorie">';
    echo '<div class="sklo-wrap sklo-katalog-seo-intro__inner">';
    foreach ($paras as $para) {
        $para = trim((string) $para);
        if ($para === '') {
            continue;
        }
        echo '<p>' . esc_html($para) . '</p>';
    }
    echo '</div></section>';
}

/**
 * Internal links between product pillars on category hubs.
 */
function sklo_render_related_pillars(string $slug): void
{
    if (!function_exists('sklo_seo_category_by_slug')) {
        return;
    }
    $cat = sklo_seo_category_by_slug($slug);
    if (!$cat) {
        return;
    }
    $related = is_array($cat['related'] ?? null) ? $cat['related'] : [];
    if ($related === []) {
        return;
    }
    echo '<section class="sklo-section sklo-related-pillars" aria-label="Související kategorie">';
    echo '<div class="sklo-wrap">';
    echo '<header class="sklo-section__head sklo-section__head--center">';
    echo '<p class="sklo-eyebrow">Tip</p>';
    echo '<h2>Mohlo by tě zajímat</h2>';
    echo '<p>Další sklo na míru ze stejné dílny — stejný online proces, montáž po ČR.</p>';
    echo '</header>';
    echo '<ul class="sklo-related-pillars__list">';
    foreach ($related as $rel) {
        if (!is_array($rel) || count($rel) < 2) {
            continue;
        }
        $path  = (string) $rel[0];
        $label = (string) $rel[1];
        if ($path === '' || $label === '') {
            continue;
        }
        printf(
            '<li><a class="sklo-related-pillars__link" href="%s">%s</a></li>',
            esc_url(home_url($path)),
            esc_html($label)
        );
    }
    echo '</ul></div></section>';
}
