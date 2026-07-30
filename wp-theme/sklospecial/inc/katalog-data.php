<?php
/**
 * Katalog skleněných dveří — sekce, ceny, copy.
 *
 * Ceny: Cost CZK = PLN × 5.8; Sale = Cost × 1.45; display „od X Kč“ zaokrouhleno na 100 Kč.
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
 * @return array<string, array<string, mixed>>
 */
function sklo_katalog(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }

    $uploads = content_url('uploads/2026/07');

    $data = [
        'sklenene-dvere' => [
            'title'       => 'Skleněné dveře na míru',
            'eyebrow'     => 'Katalog',
            'lead'        => 'Vyrábíme skleněné dveře přesně podle tvého otvoru. Posuvné, otočné, otevírané i celoskleněné — vyber kategorii a doladíš detaily ve studiu.',
            'seo_title'   => 'Skleněné dveře na míru — katalog | Sklospeciál',
            'seo_desc'    => 'Posuvné, otočné, otevírané i celoskleněné dveře. Orientační ceny od, finální nabídka podle rozměrů. Navrhni si dveře online.',
            'hub'         => true,
            'children'    => [
                [
                    'slug'  => 'posuvne',
                    'title' => 'Posuvné',
                    'text'  => 'Po stěně nebo do pouzdra. Ideální tam, kde každý centimetr hraje roli.',
                    'image' => 'posuvne-sklenene-dvere-matne-sklo.webp',
                    'price' => sklo_format_cena_od(7600),
                ],
                [
                    'slug'  => 'otocne',
                    'title' => 'Otočné',
                    'text'  => 'Klasické otevírání do místnosti nebo na chodbu. Spolehlivý chod, čistý detail.',
                    'image' => 'prosklene-dvere-na-miru.webp',
                    'price' => sklo_format_cena_od(8900),
                ],
                [
                    'slug'  => 'otevirane',
                    'title' => 'Otevírané',
                    'text'  => 'Křídlo na pantech — jasný pohyb, známé ovládání, široký výběr skel.',
                    'image' => 'sklenene-dvere-do-obyvaciho-pokoje-3.webp',
                    'price' => null,
                ],
                [
                    'slug'  => 'dvere-se-zarubni',
                    'title' => 'Dveře se zárubní',
                    'text'  => 'Pevná, nastavitelná nebo hliníková zárubeň — podle stavby a požadovaného vzhledu.',
                    'image' => 'sklenene-dvere-s-masivnim-zarubnim.webp',
                    'price' => null,
                ],
                [
                    'slug'  => 'linie-luxe',
                    'title' => 'Linie Luxe',
                    'text'  => 'Nejvyšší třída v nabídce — jemnější detaily, větší prostor pro individuální návrh.',
                    'image' => 'sklenene-dvere-na-miru.webp',
                    'price' => 'cena na dotaz',
                ],
                [
                    'slug'  => 'rock-glass',
                    'title' => 'Rock Glass',
                    'text'  => 'Sklo s výraznou strukturou — světlo hraje, pohled zůstává soukromý.',
                    'image' => 'sklenene-dvere-s-matnym-sklem.webp',
                    'price' => null,
                ],
                [
                    'slug'  => 'pricky-a-zabudovani',
                    'title' => 'Příčky a zabudování',
                    'text'  => 'Skleněné příčky, pevné výplně a zabudování do stěny nebo pouzdra.',
                    'image' => 'moderni-interier-sklene-prcky-dreveny-stolek.webp',
                    'price' => null,
                ],
                [
                    'slug'  => 'vzory-skla',
                    'title' => 'Vzory skla',
                    'text'  => 'Čiré, matné, barevné i dekorativní vzory — přehledný výběr pro studio.',
                    'image' => 'sklenene-dvere-detail.webp',
                    'price' => null,
                ],
                [
                    'slug'  => 'laminovane',
                    'title' => 'Laminované',
                    'text'  => 'Vrstvené sklo pro vyšší bezpečnost a klidnější akustiku.',
                    'image' => 'sklenene-dvere-uzavrene-s-handlem.webp',
                    'price' => sklo_format_cena_od(11700),
                ],
                [
                    'slug'  => 'skladem',
                    'title' => 'Skladem',
                    'text'  => 'Vybrané provedení s rychlou expedicí — když nechceš čekat na výrobu na míru.',
                    'image' => 'interier-sklene-dvere.webp',
                    'price' => null,
                ],
                [
                    'slug'  => 'celosklenene',
                    'title' => 'Celoskleněné',
                    'text'  => 'Maximální průchod světla, minimální rám. Opticky propojí místnosti.',
                    'image' => 'sklenene-dvere-do-obyvaciho-pokoje-3.webp',
                    'price' => null,
                ],
            ],
            'uploads' => $uploads,
        ],

        'posuvne' => [
            'title'     => 'Posuvné skleněné dveře',
            'eyebrow'   => 'Katalog · Posuvné',
            'lead'      => 'Posuvné dveře šetří místo a nechají prostor působit vzdušněji. Níže najdeš hlavní systémy — od Design-Lux po pouzdro a výrobu na míru.',
            'seo_title' => 'Posuvné skleněné dveře — systémy a ceny od | Sklospeciál',
            'seo_desc'  => 'Design-Lux, Ultra Slim, Loft, trubkový systém, do pouzdra i na míru. Orientační ceny od 7 600 Kč.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'design-lux',
                    'title'   => 'Design-Lux',
                    'lead'    => 'Nejoblíbenější posuvný systém v nabídce. Čistá linie, spolehlivý posuv po stěně a dostatek možností, jak dveře doladit k interiéru.',
                    'choosable' => [
                        'Typ skla (čiré, matné, zrcadlové, grafika…)',
                        'Barva systému (např. černá, nerez, bílá)',
                        'Rozměry na míru podle otvoru',
                        'Úchyt a detaily kování',
                    ],
                    'bestsellers' => [
                        'Čiré sklo',
                        'Matné sklo',
                        'Zrcadlové sklo',
                        'GRAF / 009b',
                        'Laminované černé',
                    ],
                    'prices' => [
                        ['label' => 'Posuvné čiré', 'pln' => 899, 'od' => 7600],
                        ['label' => 'Posuvné matné', 'pln' => 1100, 'od' => 9300],
                        ['label' => 'Posuvné s grafikou', 'pln' => 1475, 'od' => 12400],
                        ['label' => 'Posuvné laminované černé', 'pln' => 1390, 'od' => 11700],
                        ['label' => 'Posuvné se zrcadlem', 'pln' => 2900, 'od' => 24400],
                    ],
                    'image' => 'posuvne-sklenene-dvere-matne-sklo.webp',
                ],
                [
                    'id'      => 'ultra-slim',
                    'title'   => 'Ultra Slim',
                    'lead'    => 'Tenký profil, který ustoupí sklu. Hodí se tam, kde chceš posuv téměř bez rámu — lehčí vzhled, méně kovu v pohledu.',
                    'choosable' => [
                        'Štíhlý systém a barva kolejnice',
                        'Typ a tloušťka skla',
                        'Rozměry podle otvoru',
                    ],
                    'prices' => [
                        ['label' => 'Orientačně od (čiré)', 'pln' => 899, 'od' => 7600],
                    ],
                    'image' => 'sklenene-dvere-s-matnym-sklem.webp',
                ],
                [
                    'id'      => 'loft',
                    'title'   => 'Loft',
                    'lead'    => 'Výraznější průmyslový charakter — černé prvky, pevnější linie. Funguje v loftu, ateliéru i v bytě, kde chceš dveře jako součást architektury.',
                    'choosable' => [
                        'Loftové rámování a dělení',
                        'Sklo čiré / matné / tónované',
                        'Rozměry na míru',
                    ],
                    'prices' => [
                        ['label' => 'Orientačně od', 'pln' => 1100, 'od' => 9300],
                    ],
                    'image' => 'sklenene-dvere-moderny-interier.webp',
                ],
                [
                    'id'      => 'trubkovy-system',
                    'title'   => 'Trubkový systém',
                    'lead'    => 'Posuv s trubkovým madlem a robustnějším hardwarem. Dobře sedí k industriálním i soudobým interiérům, kde má kování být vidět.',
                    'choosable' => [
                        'Průměr a povrch trubky',
                        'Typ skla',
                        'Délka pojezdu a rozměr křídla',
                    ],
                    'prices' => [
                        ['label' => 'Orientačně od', 'pln' => 1100, 'od' => 9300],
                    ],
                    'image' => 'sklenene-dvere-s-modernim-madlem.webp',
                ],
                [
                    'id'      => 'do-pouzdra',
                    'title'   => 'Do pouzdra',
                    'lead'    => 'Křídlo zajíždí do stavebního pouzdra — když jsou dveře otevřené, zůstane jen čistý průchod. Vyžaduje připravenou stavební kapsu.',
                    'choosable' => [
                        'Jednokřídlé nebo vícekřídlé řešení',
                        'Typ skla a tloušťka',
                        'Kompatibilita s pouzdrem / stavební připraveností',
                    ],
                    'prices' => [
                        ['label' => 'Orientačně od (čiré)', 'pln' => 899, 'od' => 7600],
                    ],
                    'image' => 'interier-sklene-dvere-masivni-drevo.webp',
                ],
                [
                    'id'      => 'na-miru',
                    'title'   => 'Na míru',
                    'lead'    => 'Nestandardní šířka, výška nebo překrytí? Posuvné dveře připravíme podle tvého otvoru — včetně čirého skla jako základního provedení.',
                    'choosable' => [
                        'Přesné rozměry podle zaměření',
                        'Typ skla a systém',
                        'Montáž nebo dodávka bez montáže',
                    ],
                    'prices' => [
                        ['label' => 'Posuvné na míru čiré', 'pln' => 1400, 'od' => 11800],
                    ],
                    'image' => 'sklenene-dvere-na-miru.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'otocne' => [
            'title'     => 'Otočné skleněné dveře',
            'eyebrow'   => 'Katalog · Otočné',
            'lead'      => 'Klasické otevírání na pantech nebo otočném mechanismu. Křídlo se otevírá do místnosti nebo na chodbu — jednoduchý pohyb, který každý zná.',
            'seo_title' => 'Otočné skleněné dveře — cena od 8 900 Kč | Sklospeciál',
            'seo_desc'  => 'Otočné skleněné dveře na míru. Orientační cena od 8 900 Kč — finální nabídka podle rozměrů a provedení.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'otocne',
                    'title'   => 'Otočné dveře',
                    'lead'    => 'Hodí se tam, kde máš prostor pro pohyb křídla. Můžeš volit čiré i matné sklo, různé madla a rámování — od jemného po výraznější.',
                    'choosable' => [
                        'Směr otevírání (dovnitř / ven, levá / pravá)',
                        'Typ skla a míra průhlednosti',
                        'Kování a madlo',
                        'Rozměry podle otvoru',
                    ],
                    'prices' => [
                        ['label' => 'Otočné dveře', 'pln' => null, 'od' => 8900],
                    ],
                    'image' => 'prosklene-dvere-na-miru.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'otevirane' => [
            'title'     => 'Otevírané skleněné dveře',
            'eyebrow'   => 'Katalog · Otevírané',
            'lead'      => 'Otevírané dveře na pantech — jasný pohyb, známé ovládání. Fungují v klasických i soudobých interiérech, kde nepotřebuješ šetřit každý centimetr posuvem.',
            'seo_title' => 'Otevírané skleněné dveře | Sklospeciál',
            'seo_desc'  => 'Otevírané skleněné dveře na míru. Vyber sklo, kování a rozměry — nabídku připravíme podle otvoru.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'otevirane',
                    'title'   => 'Otevírané dveře',
                    'lead'    => 'Křídlo se otevírá do prostoru. Dobře sedí k zárubním i k celoskleněným řešením. Ve studiu zvolíš sklo, madlo a rozměr.',
                    'choosable' => [
                        'Jednokřídlé / dvoukřídlé',
                        'Typ skla',
                        'Panty, zámek, madlo',
                        'S zárubní nebo bez',
                    ],
                    'image' => 'sklenene-dvere-do-obyvaciho-pokoje.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'dvere-se-zarubni' => [
            'title'     => 'Dveře se zárubní',
            'eyebrow'   => 'Katalog · Zárubeň',
            'lead'      => 'Skleněné dveře v zárubni — pevné ukotvení do stavebního otvoru. Podle stavby zvolíš pevnou, nastavitelnou nebo hliníkovou variantu.',
            'seo_title' => 'Skleněné dveře se zárubní | Sklospeciál',
            'seo_desc'  => 'Pevná, nastavitelná i hliníková zárubeň pro skleněné dveře. Nabídka podle otvoru a provedení.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'pevna',
                    'title'   => 'Pevná zárubeň',
                    'lead'    => 'Stabilní řešení pro přesně připravený otvor. Sedí tam, kde máš jistotu rozměrů a chceš pevný, čistý rám.',
                    'choosable' => ['Rozměr otvoru', 'Barva / materiál zárubně', 'Typ skla a křídla'],
                ],
                [
                    'id'      => 'nastavitelna',
                    'title'   => 'Nastavitelná zárubeň',
                    'lead'    => 'Pomůže u tloušťky stěny, která není úplně standardní. Doladí se k zdivu bez zbytečných kompromisů ve vzhledu.',
                    'choosable' => ['Rozsah nastavení podle tloušťky stěny', 'Povrch zárubně', 'Typ dveří'],
                ],
                [
                    'id'      => 'hlinikova',
                    'title'   => 'Hliníková zárubeň',
                    'lead'    => 'Lehký, přesný profil — často v černé nebo eloxované úpravě. Hodí se k soudobým interiérům a celoskleněným výplním.',
                    'choosable' => ['Barva profilu', 'Sklo a tloušťka', 'Kování'],
                ],
            ],
            'uploads' => $uploads,
        ],

        'linie-luxe' => [
            'title'     => 'Linie Luxe',
            'eyebrow'   => 'Katalog · Linie Luxe',
            'lead'      => 'Linie Luxe — nejvyšší třída v nabídce. Více prostoru pro individuální návrh, jemnější detaily a řešení, která nestavíme z katalogové šablony.',
            'seo_title' => 'Linie Luxe — skleněné dveře | Sklospeciál',
            'seo_desc'  => 'Linie Luxe: nejvyšší třída skleněných dveří v nabídce Sklospeciál. Cena na dotaz podle projektu.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'luxe',
                    'title'   => 'Linie Luxe',
                    'lead'    => 'Když chceš dveře, které sedí k celému projektu — ne jen k jednomu otvoru. Domluvíme sklo, systém i detaily podle dispozice a materiálů v interiéru.',
                    'choosable' => [
                        'Individuální skladba skla a systému',
                        'Atypické rozměry a detaily',
                        'Koordinace s příčkami a zabudováním',
                    ],
                    'prices' => [
                        ['label' => 'Linie Luxe', 'pln' => null, 'od' => null, 'custom' => 'cena na dotaz'],
                    ],
                    'image' => 'sklenene-dvere-na-miru.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'rock-glass' => [
            'title'     => 'Rock Glass',
            'eyebrow'   => 'Katalog · Rock Glass',
            'lead'      => 'Sklo se strukturou, která láme světlo a drží soukromí. Rock Glass sedí do koupelen, šaten i obytných místností, kde nechceš úplně průhledné křídlo.',
            'seo_title' => 'Rock Glass — strukturované sklo | Sklospeciál',
            'seo_desc'  => 'Rock Glass: skleněné dveře se strukturovaným sklem. Navrhni provedení online nebo nám napiš.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'rock-glass',
                    'title'   => 'Rock Glass',
                    'lead'    => 'Výrazná textura, měkký průhled. Můžeš ji kombinovat s posuvnými i otevíranými systémy — podle toho, co ti sedí do prostoru.',
                    'choosable' => [
                        'Typ struktury / vzoru',
                        'Posuvné nebo otevírané provedení',
                        'Rozměry na míru',
                    ],
                    'image' => 'sklenene-dvere-s-matnym-sklem.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'pricky-a-zabudovani' => [
            'title'     => 'Skleněné příčky a zabudování',
            'eyebrow'   => 'Katalog · Příčky',
            'lead'      => 'Odděl místnosti bez těžké stěny. Skleněné příčky a zabudování pustí světlo dál a nechají dispozici působit otevřeněji.',
            'seo_title' => 'Skleněné příčky a zabudování | Sklospeciál',
            'seo_desc'  => 'Skleněné příčky, pevné výplně a zabudování. Nabídka podle rozměrů a provedení.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'pricky',
                    'title'   => 'Skleněné příčky',
                    'lead'    => 'Pevné nebo s dveřním křídlem. Hodí se do open-space, pracovny i bytu, kde chceš oddělit zónu a zároveň zachovat světlo.',
                    'choosable' => [
                        'Pevná / posuvná / otevíraná část',
                        'Typ skla',
                        'Profily a kotvení',
                    ],
                    'image' => 'moderni-interier-sklene-prcky-dreveny-stolek.webp',
                ],
                [
                    'id'      => 'zabudovani',
                    'title'   => 'Zabudování',
                    'lead'    => 'Výplně do stěny, pouzdra nebo stávající konstrukce. Doladíme napojení na omítku, SDK i dřevěné prvky.',
                    'choosable' => [
                        'Způsob kotvení',
                        'Sklo a rámování',
                        'Soulad s okolními povrchy',
                    ],
                ],
            ],
            'uploads' => $uploads,
        ],

        'vzory-skla' => [
            'title'     => 'Vzory skla',
            'eyebrow'   => 'Katalog · Sklo',
            'lead'      => 'Od čirého po matné a dekorativní vzory. Níže najdeš přehled barevných i matných možností — ve studiu si je rovnou vyzkoušíš na dveřích.',
            'seo_title' => 'Vzory skla pro skleněné dveře | Sklospeciál',
            'seo_desc'  => 'Přehled vzorů skla: čiré, matné, barevné a dekorativní. Vyber ve studiu nebo se zeptej.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'barevne',
                    'title'   => 'Barevné sklo',
                    'lead'    => 'Tónované a barevné výplně pro dveře, které mají nést barvu interiéru — ne jen průhled.',
                    'choosable' => ['Stupeň zabarvení', 'Kombinace s matováním', 'Soulad s kováním'],
                ],
                [
                    'id'      => 'matne',
                    'title'   => 'Matné sklo',
                    'lead'    => 'Soukromí bez úplného zatemnění. Mat propustí světlo a zjemní pohled z druhé strany.',
                    'choosable' => ['Celomat / pásové matování', 'Kombinace s čirým sklem', 'Grafické vzory'],
                ],
                [
                    'id'      => 'prehled-vzoru',
                    'title'   => 'Přehled vzorů',
                    'lead'    => 'Základní názvy vzorů, se kterými pracujeme. Konkrétní dostupnost ověříme u poptávky.',
                    'patterns' => [
                        ['code' => 'Clear / Float', 'cs' => 'Čiré sklo'],
                        ['code' => 'Matt / Satinato', 'cs' => 'Matné (satinované) sklo'],
                        ['code' => 'Mirror', 'cs' => 'Zrcadlové sklo'],
                        ['code' => 'Graf / 009b', 'cs' => 'Grafický vzor GRAF / 009b'],
                        ['code' => 'Stopsol', 'cs' => 'Reflexní / tónované sklo'],
                        ['code' => 'Bronze', 'cs' => 'Bronzové tónování'],
                        ['code' => 'Grey / Graphite', 'cs' => 'Šedé / grafitové tónování'],
                        ['code' => 'Lacobel / painted', 'cs' => 'Lakované sklo'],
                        ['code' => 'Flutes / fluted', 'cs' => 'Žebrované sklo'],
                        ['code' => 'Chinchilla', 'cs' => 'Strukturované sklo Chinchilla'],
                        ['code' => 'Masterpoint', 'cs' => 'Dekorativní bodový vzor'],
                        ['code' => 'Kura / wired look', 'cs' => 'Drátěný / industriální vzhled'],
                        ['code' => 'Laminated black', 'cs' => 'Laminované černé'],
                        ['code' => 'Rock Glass', 'cs' => 'Strukturované Rock Glass'],
                        ['code' => 'Omnitone / colored', 'cs' => 'Barevné dekorativní sklo'],
                    ],
                ],
            ],
            'uploads' => $uploads,
        ],

        'laminovane' => [
            'title'     => 'Laminované dveře',
            'eyebrow'   => 'Katalog · Laminované',
            'lead'      => 'Vrstvené sklo drží pohromadě i při poškození. Hodí se tam, kde chceš vyšší bezpečnost a klidnější akustiku — včetně tmavších laminovaných provedení.',
            'seo_title' => 'Laminované skleněné dveře — od 11 700 Kč | Sklospeciál',
            'seo_desc'  => 'Laminované skleněné dveře. Orientační cena od 11 700 Kč u laminovaného černého posuvu.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'laminovane',
                    'title'   => 'Laminované sklo',
                    'lead'    => 'Dvě tabulě spojené fólií. Při prasknutí zůstane výplň v celku. Můžeš zvolit čiré i tónované vrstvy — včetně laminovaného černého posuvu.',
                    'choosable' => [
                        'Tloušťka a skladba laminátu',
                        'Čiré / tónované / černé',
                        'Posuvný nebo otevíraný systém',
                    ],
                    'prices' => [
                        ['label' => 'Posuvné laminované černé', 'pln' => 1390, 'od' => 11700],
                    ],
                    'image' => 'sklenene-dvere-uzavrene-s-handlem.webp',
                ],
            ],
            'uploads' => $uploads,
        ],

        'skladem' => [
            'title'     => 'Skladem — rychlá expedice',
            'eyebrow'   => 'Katalog · Skladem',
            'lead'      => 'Vybrané provedení připravené k rychlejší expedici. Když nechceš čekat na plnou výrobu na míru, mrkni sem — a ověř dostupnost u nás.',
            'seo_title' => 'Skladem — skleněné dveře s rychlou expedicí | Sklospeciál',
            'seo_desc'  => 'Skleněné dveře skladem. Rychlejší expedice vybraných provedení — ověř dostupnost a pošli rozměry.',
            'parent'    => 'sklenene-dvere',
            'sections'  => [
                [
                    'id'      => 'skladem',
                    'title'   => 'Rychlá expedice',
                    'lead'    => 'Skladem držíme vybrané rozměry a skla. Není to celý katalog — spíš rychlá cesta, když ti sedí standardnější řešení. Napiš, co hledáš, a řekneme, co jde hned.',
                    'choosable' => [
                        'Dostupné rozměry a skla',
                        'Typ systému (posuv / otočné)',
                        'Termín expedice',
                    ],
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
                    'id'      => 'celosklenene',
                    'title'   => 'Celoskleněné řešení',
                    'lead'    => 'Když má být sklo hlavní. Rám ustoupí, světlo jde skrz. Můžeš kombinovat s otočným i posuvným systémem a doladit míru soukromí matováním.',
                    'choosable' => [
                        'Čiré / matné / dekorativní sklo',
                        'Typ otevírání',
                        'Kování s minimálním vizuálním zásahem',
                    ],
                    'image' => 'sklenene-dvere-do-obyvaciho-pokoje-3.webp',
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
