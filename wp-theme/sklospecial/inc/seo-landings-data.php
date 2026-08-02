<?php
/**
 * SEO landings — categories, cities, type pages, copy templates.
 *
 * City pages = honest remote service area (no fake branches).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product pillars that get city landings.
 *
 * @return array<string, array<string, mixed>>
 */
function sklo_seo_categories(): array
{
    return [
        'sklenene-dvere' => [
            'name'          => 'Skleněné dveře',
            'h1_product'    => 'Skleněné dveře',
            'in_phrase'     => 'skleněné dveře',
            'genitive'      => 'skleněných dveří',
            'katalog_slug'  => 'sklenene-dvere',
            'pillar_path'   => '/sklenene-dvere/',
            'short'         => 'dveře',
            'benefits'      => [
                'Posuvné, kyvné i otevírané — podle otvoru a prostoru',
                'Zaměříš sám podle návodu, nebo přijedeme zaměřit',
                'Výroba na míru a montáž po celé ČR',
            ],
            'related'       => [
                ['/sprchove-kouty/', 'Sprchové kouty'],
                ['/sklenene-pricky/', 'Skleněné příčky'],
                ['/francouzske-balkony/', 'Francouzské balkony'],
            ],
        ],
        'sprchove-kouty' => [
            'name'          => 'Sprchové kouty',
            'h1_product'    => 'Sprchové kouty',
            'in_phrase'     => 'sprchové kouty',
            'genitive'      => 'sprchových koutů',
            'katalog_slug'  => 'sprchove-kouty',
            'pillar_path'   => '/sprchove-kouty/',
            'short'         => 'sprchy',
            'benefits'      => [
                'Walk-in stěny i klasické kouty podle koupelny',
                'Rozměry a kování doladíme podle tvého prostoru',
                'Doprava a montáž domluvíme individuálně',
            ],
            'related'       => [
                ['/sklenene-dvere/', 'Skleněné dveře'],
                ['/zabradli/', 'Skleněné zábradlí'],
                ['/strisky/', 'Skleněné stříšky'],
            ],
        ],
        'zabradli' => [
            'name'          => 'Skleněné zábradlí',
            'h1_product'    => 'Skleněné zábradlí',
            'in_phrase'     => 'skleněné zábradlí',
            'genitive'      => 'skleněného zábradlí',
            'katalog_slug'  => 'zabradli',
            'pillar_path'   => '/zabradli/',
            'short'         => 'zábradlí',
            'benefits'      => [
                'Zábradlí na schodiště, terasu i galerii',
                'DIY sestavy i řešení s montáží',
                'Bezpečné sklo a profily podle projektu',
            ],
            'related'       => [
                ['/francouzske-balkony/', 'Francouzské balkony'],
                ['/strisky/', 'Skleněné stříšky'],
                ['/sklenene-dvere/', 'Skleněné dveře'],
            ],
        ],
        'strisky' => [
            'name'          => 'Skleněné stříšky',
            'h1_product'    => 'Skleněné stříšky',
            'in_phrase'     => 'skleněné stříšky',
            'genitive'      => 'skleněných stříšek',
            'katalog_slug'  => 'strisky',
            'pillar_path'   => '/strisky/',
            'short'         => 'stříšky',
            'benefits'      => [
                'Stříšky na konzolách, táhlech i systémové sestavy',
                'Ochraní vstup před deštěm a sněhem',
                'Výroba podle rozměrů a typu fasády',
            ],
            'related'       => [
                ['/zabradli/', 'Skleněné zábradlí'],
                ['/francouzske-balkony/', 'Francouzské balkony'],
                ['/sklenene-dvere/', 'Skleněné dveře'],
            ],
        ],
        'sklenene-pricky' => [
            'name'          => 'Skleněné příčky',
            'h1_product'    => 'Skleněné příčky',
            'in_phrase'     => 'skleněné příčky',
            'genitive'      => 'skleněných příček',
            'katalog_slug'  => 'pricky-a-zabudovani',
            'pillar_path'   => '/sklenene-pricky/',
            'short'         => 'příčky',
            'benefits'      => [
                'Oddělíš místnosti a necháš projít světlo',
                'Pevné výplně i kombinace s dveřním křídlem',
                'Zaměření, výroba a montáž podle dispozice',
            ],
            'related'       => [
                ['/sklenene-dvere/', 'Skleněné dveře'],
                ['/sklenene-dvere/posuvne/', 'Posuvné dveře'],
                ['/sprchove-kouty/', 'Sprchové kouty'],
            ],
        ],
        'francouzske-balkony' => [
            'name'          => 'Francouzské balkony',
            'h1_product'    => 'Francouzské balkony',
            'in_phrase'     => 'francouzské balkony',
            'genitive'      => 'francouzských balkonů',
            'katalog_slug'  => 'francouzske-balkony',
            'pillar_path'   => '/francouzske-balkony/',
            'short'         => 'balkony',
            'benefits'      => [
                'Skleněné výplně francouzských balkonů na míru',
                'Bezpečné kotvení podle otvoru a fasády',
                'Dodávka a montáž po celé ČR',
            ],
            'related'       => [
                ['/zabradli/', 'Skleněné zábradlí'],
                ['/strisky/', 'Skleněné stříšky'],
                ['/sklenene-dvere/', 'Skleněné dveře'],
            ],
        ],
    ];
}

/**
 * Cities for local SEO paths (no fake offices).
 *
 * @return list<array{slug:string,name:string,locative:string,v:string,kraj:string,note:string}>
 */
function sklo_seo_cities(): array
{
    return [
        [
            'slug' => 'praha',
            'name' => 'Praha',
            'locative' => 'Praze',
            'v' => 'v',
            'kraj' => 'Hlavní město Praha',
            'note' => 'V Praze často řešíme panelové byty a posuvné do pouzdra. Montáže dávkujeme do společných výjezdů (typicky 3–5 zakázek) — napište nám a zařadíme vás do nejbližšího termínu. Dopravu plánujeme mimo špičku podle domu a výtahu.',
        ],
        [
            'slug' => 'brno',
            'name' => 'Brno',
            'locative' => 'Brně',
            'v' => 'v',
            'kraj' => 'Jihomoravský kraj',
            'note' => 'Do Brna a okolí jezdíme pravidelně. Typicky zaměření podle fotek, výroba v dílně a montáž v domluveném okně — bez showroomu ve městě.',
        ],
        [
            'slug' => 'ostrava',
            'name' => 'Ostrava',
            'locative' => 'Ostravě',
            'v' => 'v',
            'kraj' => 'Moravskoslezský kraj',
            'note' => 'Ostrava a okolí (včetně Havířova) máme blízko provozovny v Horních Bludovicích — kratší dojezd na montáž i volitelné zaměření.',
        ],
        [
            'slug' => 'plzen',
            'name' => 'Plzeň',
            'locative' => 'Plzni',
            'v' => 'v',
            'kraj' => 'Plzeňský kraj',
            'note' => 'Do Plzně dodáváme po celé ČR modelu: online podklady, výroba na míru, doprava firemním autem nebo přepravní službou podle objemu.',
        ],
        [
            'slug' => 'liberec',
            'name' => 'Liberec',
            'locative' => 'Liberci',
            'v' => 'v',
            'kraj' => 'Liberecký kraj',
            'note' => 'Liberec a podhůří — počítáme s delším dojezdem. Montáž a volitelné zaměření plánujeme s předstihem; nabídku připravíme z fotek a rozměrů.',
        ],
        [
            'slug' => 'olomouc',
            'name' => 'Olomouc',
            'locative' => 'Olomouci',
            'v' => 'v',
            'kraj' => 'Olomoucký kraj',
            'note' => 'Olomoucko je v dosahu z Moravy. Často kombinujeme DIY zaměření s montáží na místě — bez fiktivní pobočky ve městě.',
        ],
        [
            'slug' => 'ceske-budejovice',
            'name' => 'České Budějovice',
            'locative' => 'Českých Budějovicích',
            'v' => 'v',
            'kraj' => 'Jihočeský kraj',
            'note' => 'Jižní Čechy obsluhujeme na dálku. Dopravu skla plánujeme pečlivě (křehký náklad) — termín a cenu dopravy uvidíš v nabídce.',
        ],
        [
            'slug' => 'hradec-kralove',
            'name' => 'Hradec Králové',
            'locative' => 'Hradci Králové',
            'v' => 'v',
            'kraj' => 'Královéhradecký kraj',
            'note' => 'Hradec a okolí — online proces, výroba na míru. Montáž domluvíme podle lokality; volitelné zaměření, pokud nechceš měřit sám.',
        ],
        [
            'slug' => 'usti-nad-labem',
            'name' => 'Ústí nad Labem',
            'locative' => 'Ústí nad Labem',
            'v' => 'v',
            'kraj' => 'Ústecký kraj',
            'note' => 'Ústecko obsluhujeme stejným remote modelem jako zbytek ČR. Pošli rozměry a fotky — nabídku a termín montáže připravíme individuálně.',
        ],
        [
            'slug' => 'pardubice',
            'name' => 'Pardubice',
            'locative' => 'Pardubicích',
            'v' => 'v',
            'kraj' => 'Pardubický kraj',
            'note' => 'Pardubice a východní Čechy — dodávka a montáž po domluvě. Bez showroomu; vše řešíme online a na místě až při montáži nebo zaměření.',
        ],
        [
            'slug' => 'zlin',
            'name' => 'Zlín',
            'locative' => 'Zlíně',
            'v' => 've',
            'kraj' => 'Zlínský kraj',
            'note' => 'Zlínsko máme v rozumném dojezdu z Moravy. Často stačí fotky a rozměry; montáž a doprava se domluví podle konkrétního projektu.',
        ],
        [
            'slug' => 'havirov',
            'name' => 'Havířov',
            'locative' => 'Havířově',
            'v' => 'v',
            'kraj' => 'Moravskoslezský kraj',
            'note' => 'Havířov je blízko naší provozovny v Horních Bludovicích — kratší logistika montáže i volitelného zaměření v regionu.',
        ],
        [
            'slug' => 'kladno',
            'name' => 'Kladno',
            'locative' => 'Kladně',
            'v' => 'v',
            'kraj' => 'Středočeský kraj',
            'note' => 'Kladno a Středočesko často spojujeme s pražskými termíny montáže. Online zaměření, výroba na míru, doprava podle objemu zakázky.',
        ],
        [
            'slug' => 'jihlava',
            'name' => 'Jihlava',
            'locative' => 'Jihlavě',
            'v' => 'v',
            'kraj' => 'Kraj Vysočina',
            'note' => 'Vysočina — delší trasy, proto termín montáže plánujeme s předstihem. Nabídku připravíme z podkladů; showroom ve městě nemáme.',
        ],
        [
            'slug' => 'teplice',
            'name' => 'Teplice',
            'locative' => 'Teplicích',
            'v' => 'v',
            'kraj' => 'Ústecký kraj',
            'note' => 'Teplicko obsluhujeme remote modelem po celé ČR. Dopravu a montáž naceníme podle vzdálenosti a typu skla — bez fiktivní pobočky.',
        ],
    ];
}

/**
 * High-intent type landings (nested under pillars).
 *
 * @return array<string, array<string, mixed>>
 */
function sklo_seo_type_landings(): array
{
    return [
        'do-pouzdra' => [
            'key'          => 'do-pouzdra',
            'slug'         => 'do-pouzdra',
            'parent_path'  => 'sklenene-dvere/posuvne',
            'url_path'     => '/sklenene-dvere/posuvne/do-pouzdra/',
            'h1'           => 'Posuvné skleněné dveře do pouzdra',
            'eyebrow'      => 'Posuvné · Do pouzdra',
            'product_name' => 'Posuvné dveře do pouzdra',
            'katalog_slug' => 'posuvne',
            'katalog_section' => 'do-pouzdra',
            'parent_label' => 'Posuvné skleněné dveře',
            'parent_url'   => '/sklenene-dvere/posuvne/',
            'pillar_url'   => '/sklenene-dvere/',
            'intro'        => 'Posuvné skleněné dveře do pouzdra zajíždějí do stavebního pouzdra — když jsou otevřené, zůstane čistý průchod bez křídla u stěny. Zaměříš sám, nebo přijedeme zaměřit; výrobu a montáž řešíme online i po celé ČR.',
            'bullets'      => [
                'Křídlo zmizí do pouzdra — ideální tam, kde každý centimetr hraje roli',
                'Systém doladíme podle typu pouzdra a tloušťky stěny',
                'Orientační cena od v katalogu, finální nabídka podle rozměrů',
            ],
            'faq' => [
                [
                    'q' => 'Hodí se dveře do pouzdra i do staršího bytu?',
                    'a' => 'Ano, pokud máš (nebo plánuješ) stavební pouzdro. Bez pouzdra zvol posuv po stěně. Pošli fotky a rozměry — poradíme, co dává smysl.',
                ],
                [
                    'q' => 'Musím mít pouzdro připravené před objednávkou?',
                    'a' => 'Ideálně ano, nebo aspoň znát typ a šířku pouzdra. Bez toho nejde přesně navrhnout křídlo. Návod na zaměření máme na webu.',
                ],
                [
                    'q' => 'Děláte i montáž dveří do pouzdra?',
                    'a' => 'Ano. V poptávce zvolíš montáž nebo jen dodávku. Orientačně od 2 500 Kč/ks — finální cena včetně dojezdu je v nabídce. Více: doprava a montáž.',
                ],
            ],
            'seo_title' => 'Posuvné skleněné dveře do pouzdra na míru | Sklospeciál',
            'seo_desc'  => 'Posuvné dveře do pouzdra — online zaměření, výroba na míru, montáž. Rodinná firma, 30 let.',
            'related'   => [
                ['/sklenene-dvere/posuvne/', 'Posuvné dveře'],
                ['/sklenene-dvere/', 'Skleněné dveře'],
                ['/sklenene-pricky/', 'Skleněné příčky'],
            ],
        ],
        'walk-in' => [
            'key'          => 'walk-in',
            'slug'         => 'walk-in',
            'parent_path'  => 'sprchove-kouty',
            'url_path'     => '/sprchove-kouty/walk-in/',
            'h1'           => 'Walk-in sprchové kouty',
            'eyebrow'      => 'Sprchové kouty · Walk-in',
            'product_name' => 'Walk-in sprchové kouty',
            'katalog_slug' => 'sprchove-kouty',
            'katalog_section' => '',
            'parent_label' => 'Sprchové kouty',
            'parent_url'   => '/sprchove-kouty/',
            'pillar_url'   => '/sprchove-kouty/',
            'intro'        => 'Walk-in sprchové stěny dávají koupelně vzdušný průchod bez klasických dveří. Sklo a kování vyrábíme na míru podle tvých rozměrů — zaměříš sám, nebo přijedeme zaměřit. Pracujeme online a montujeme po celé ČR.',
            'bullets'      => [
                'Jedna nebo více stěn podle dispozice koupelny',
                'Čiré i matné sklo, kování podle výběru',
                'Montáž orientačně od 3 500 Kč / sestava',
            ],
            'faq' => [
                [
                    'q' => 'Stačí jedna walk-in stěna, nebo potřebuji celý kout?',
                    'a' => 'Záleží na odtoku a dispozici. Často stačí jedna pevná stěna. Pošli půdorys nebo fotky — doporučíme konkrétní sestavu.',
                ],
                [
                    'q' => 'Jak přesně mám zaměřit sprchu?',
                    'a' => 'Šířka a výška na několika místech, plus fotky vaničky nebo odtoku. Postup najdeš v návodu na zaměření — nebo zvol volitelné zaměření na místě.',
                ],
                [
                    'q' => 'Dodáváte walk-in i mimo velké města?',
                    'a' => 'Ano. Pracujeme na dálku a montujeme po celé ČR. Orientační montáž sprchy od 3 500 Kč — finální cena v nabídce. Nemáme pobočky v každém městě.',
                ],
            ],
            'seo_title' => 'Walk-in sprchové kouty na míru | Sklospeciál',
            'seo_desc'  => 'Walk-in sprchové stěny na míru — online zaměření, výroba, montáž. Rodinná firma, 30 let.',
            'related'   => [
                ['/sprchove-kouty/', 'Sprchové kouty'],
                ['/sprchove-kouty/zastena/', 'Sprchové zástěny'],
                ['/sklenene-dvere/', 'Skleněné dveře'],
            ],
        ],
        'zastena' => [
            'key'          => 'zastena',
            'slug'         => 'zastena',
            'parent_path'  => 'sprchove-kouty',
            'url_path'     => '/sprchove-kouty/zastena/',
            'h1'           => 'Sprchové zástěny na míru',
            'eyebrow'      => 'Sprchové kouty · Zástěna',
            'product_name' => 'Sprchové zástěny',
            'katalog_slug' => 'sprchove-kouty',
            'katalog_section' => '',
            'parent_label' => 'Sprchové kouty',
            'parent_url'   => '/sprchove-kouty/',
            'pillar_url'   => '/sprchove-kouty/',
            'intro'        => 'Sprchová zástěna ze skla oddělí sprchu od zbytku koupelny bez těžké zděné příčky. Navrhujeme ji podle rozměrů a typu vaničky — zaměříš sám, my vyrobíme. Online proces, montáž po celé ČR.',
            'bullets'      => [
                'Pevné i otevírané zástěny podle prostoru',
                'Sklo a kování vybereš podle koupelny',
                'Finální nabídka vždy podle konkrétních rozměrů',
            ],
            'faq' => [
                [
                    'q' => 'Jaký je rozdíl mezi zástěnou a walk-in?',
                    'a' => 'Walk-in je typicky otevřený průchod s jednou či více stěnami. Zástěna častěji uzavírá sprchu křídlem nebo pevným panelem. Pošli fotky — doporučíme variantu.',
                ],
                [
                    'q' => 'Umíte zástěnu na vanu i na vaničku?',
                    'a' => 'Ano, podle tvaru a výšky okraje. Potřebujeme přesné rozměry a fotky — nebo přijedeme zaměřit.',
                ],
                [
                    'q' => 'Jak dlouho trvá výroba?',
                    'a' => 'Záleží na skle a kování. Po schválení nabídky ti potvrdíme termín. Orientační ceny najdeš v katalogu.',
                ],
            ],
            'seo_title' => 'Sprchové zástěny na míru | Sklospeciál',
            'seo_desc'  => 'Sprchové zástěny na míru — online zaměření, výroba, montáž. Rodinná firma, 30 let.',
            'related'   => [
                ['/sprchove-kouty/walk-in/', 'Walk-in'],
                ['/sprchove-kouty/', 'Sprchové kouty'],
                ['/sklenene-dvere/', 'Skleněné dveře'],
            ],
        ],
        'schodiste' => [
            'key'          => 'schodiste',
            'slug'         => 'schodiste',
            'parent_path'  => 'zabradli',
            'url_path'     => '/zabradli/schodiste/',
            'h1'           => 'Skleněné zábradlí na schodiště',
            'eyebrow'      => 'Zábradlí · Schodiště',
            'product_name' => 'Skleněné zábradlí na schodiště',
            'katalog_slug' => 'zabradli',
            'katalog_section' => '',
            'parent_label' => 'Skleněné zábradlí',
            'parent_url'   => '/zabradli/',
            'pillar_url'   => '/zabradli/',
            'intro'        => 'Skleněné zábradlí na schodiště nechá prostor působit otevřeně a zároveň drží bezpečnostní funkci. Navrhujeme podle výšky stupňů, kotvení a typu skla — zaměříš sám, nebo přijedeme. Výroba na míru, montáž po celé ČR.',
            'bullets'      => [
                'Kotvení do stupňů, bočnice nebo podlahy podle stavby',
                'Bezpečnostní sklo a profily podle projektu',
                'Montáž orientačně od 1 500 Kč / bm',
            ],
            'faq' => [
                [
                    'q' => 'Potřebuji projekt, nebo stačí rozměry?',
                    'a' => 'U schodiště často pomůže jednoduchý náčrt nebo fotky z více úhlů. Pokud máš projekt, pošli ho — urychlí to nabídku.',
                ],
                [
                    'q' => 'Děláte zábradlí jen interiérové?',
                    'a' => 'Hlavně interiér a kryté prostory. U exteriéru řešíme individuálně podle kotvení a skla.',
                ],
                [
                    'q' => 'Montujete i mimo Prahu a Brno?',
                    'a' => 'Ano. Nemáme pobočku v každém městě — jezdíme za tebou. Orientační montáž od 1 500 Kč/bm, finální cena v nabídce.',
                ],
            ],
            'seo_title' => 'Skleněné zábradlí na schodiště na míru | Sklospeciál',
            'seo_desc'  => 'Skleněné zábradlí na schodiště — online zaměření, výroba na míru, montáž. Rodinná firma, 30 let.',
            'related'   => [
                ['/zabradli/', 'Skleněné zábradlí'],
                ['/francouzske-balkony/', 'Francouzské balkony'],
                ['/strisky/', 'Skleněné stříšky'],
            ],
        ],
    ];
}

/**
 * @return array{slug:string,name:string,locative:string,v:string}|null
 */
function sklo_seo_city_by_slug(string $slug): ?array
{
    foreach (sklo_seo_cities() as $city) {
        if ($city['slug'] === $slug) {
            return $city;
        }
    }
    return null;
}

/**
 * @return array<string, mixed>|null
 */
function sklo_seo_category_by_slug(string $slug): ?array
{
    $all = sklo_seo_categories();
    return $all[$slug] ?? null;
}

/**
 * Resolve landing context from current page path.
 *
 * @return array{kind:string,category?:array,city?:array,type?:array}|null
 */
function sklo_seo_resolve_landing(?int $post_id = null): ?array
{
    $post_id = $post_id ?: (int) get_queried_object_id();
    if ($post_id <= 0) {
        return null;
    }

    $page = get_post($post_id);
    if (!$page || $page->post_type !== 'page') {
        return null;
    }

    $path = trim((string) get_page_uri($post_id), '/');
    $parts = $path === '' ? [] : explode('/', $path);

    // Type landing: exact path match
    foreach (sklo_seo_type_landings() as $type) {
        $want = trim((string) $type['url_path'], '/');
        if ($path === $want) {
            return ['kind' => 'type', 'type' => $type];
        }
    }

    // City landing: /{category}/{city}/
    if (count($parts) === 2) {
        $cat = sklo_seo_category_by_slug($parts[0]);
        $city = sklo_seo_city_by_slug($parts[1]);
        if ($cat && $city) {
            return ['kind' => 'city', 'category' => $cat + ['slug' => $parts[0]], 'city' => $city];
        }
    }

    return null;
}

/**
 * Variation seed 0..n-1 from category+city.
 */
function sklo_seo_variation(string $a, string $b, int $n): int
{
    if ($n <= 1) {
        return 0;
    }
    return (int) (crc32($a . '|' . $b) % $n);
}

/**
 * Build unique-ish city landing copy.
 *
 * @param array<string, mixed> $category
 * @param array{slug:string,name:string,locative:string,v:string,kraj?:string,note?:string} $city
 * @return array{h1:string,intro:string,bullets:list<string>,service:string,local_note:string,faq:list<array{q:string,a:string}>,seo_title:string,seo_desc:string,price_from:?string}
 */
function sklo_seo_city_copy(array $category, array $city): array
{
    $product = (string) $category['h1_product'];
    $name = $city['name'];
    $loc = $city['locative'];
    $v = $city['v'];
    $in = (string) $category['in_phrase'];
    $gen = (string) $category['genitive'];
    $slug_cat = (string) ($category['slug'] ?? '');
    $var = sklo_seo_variation($slug_cat, $city['slug'], 3);
    $kraj = trim((string) ($city['kraj'] ?? ''));
    $local_note = trim((string) ($city['note'] ?? ''));

    $intros = [
        sprintf(
            '%s %s vyrábíme na míru — bez showroomu, online. Dodáváme a montujeme i %s %s. Zaměříš sám podle návodu, nebo přijedeme zaměřit; my připravíme nabídku a vyrobíme.',
            $product,
            $name,
            $v,
            $loc
        ),
        sprintf(
            'Hledáš %s %s %s? Jsme rodinná firma Sklospeciál — pracujeme na dálku po celé ČR. Pošleš rozměry a fotky, ve studiu si návrh složíš, my vyrobíme a domluvíme montáž.',
            $in,
            $v,
            $loc
        ),
        sprintf(
            '%s %s %s dodáváme jako zakázkovou výrobu. Nemáme pobočku %s %s — jezdíme za tebou. Online zaměření, výroba na míru, doprava i montáž.',
            $product,
            $v,
            $loc,
            $v,
            $loc
        ),
    ];

    $intro = $intros[$var];
    if ($kraj !== '') {
        $intro .= sprintf(' Působíme remote i v oblasti %s.', $kraj);
    }

    // Product-specific city sentence (keeps landings from reading as pure duplicates).
    $city_extras = [
        'sklenene-dvere' => sprintf(
            ' U dveří %s %s často řešíme posuvné do pouzdra nebo kyvné — podle šířky otvoru a dispozice bytu.',
            $v,
            $loc
        ),
        'sprchove-kouty' => sprintf(
            ' U sprch %s %s typicky ladíme walk-in stěny a kouty podle vaničky, výšky stropu a typu kování.',
            $v,
            $loc
        ),
        'zabradli' => sprintf(
            ' U zábradlí %s %s počítáme s kotvením do stupňů nebo podlahy — pošli fotky schodiště nebo galerie.',
            $v,
            $loc
        ),
        'strisky' => sprintf(
            ' U stříšek %s %s potřebujeme šířku vstupu a fotky fasády kvůli konzolám nebo táhlům.',
            $v,
            $loc
        ),
        'sklenene-pricky' => sprintf(
            ' U příček %s %s často oddělujeme open-space nebo pracovnu — světlo zůstane, zeď ne.',
            $v,
            $loc
        ),
        'francouzske-balkony' => sprintf(
            ' U francouzských balkonů %s %s řešíme hlavně výšku a kotvení u francouzského okna.',
            $v,
            $loc
        ),
    ];
    if (isset($city_extras[$slug_cat])) {
        $intro .= $city_extras[$slug_cat];
    }

    $bullet_sets = [
        (array) ($category['benefits'] ?? []),
        [
            sprintf('Konkrétní nabídka podle tvých rozměrů — ne katalog „od oka“'),
            sprintf('Doprava a montáž %s %s domluvíme individuálně', $v, $loc),
            'Volitelné zaměření na místě, pokud nechceš měřit sám',
        ],
        [
            '30 let zkušeností, třetí generace rodinné firmy',
            sprintf('Servisní oblast zahrnuje i %s — i okolí', $name),
            'CTA přes studio nebo poptávku — bez zbytečných kol',
        ],
    ];

    $faq = [
        [
            'q' => sprintf('Máte pobočku %s %s?', $v, $loc),
            'a' => sprintf(
                'Ne — nemáme showroom ani pobočku %s %s. Pracujeme online a dodáváme a montujeme i %s %s / po celé ČR. Zaměříš sám, nebo přijedeme zaměřit.',
                $v,
                $loc,
                $v,
                $loc
            ),
        ],
        [
            'q' => sprintf('Jak probíhá zaměření %s %s?', $v, $loc),
            'a' => 'Nejjednodušší je zaměřit podle našeho návodu a poslat fotky. Pokud chceš, domluvíme volitelné zaměření na místě — termín podle lokality.',
        ],
        [
            'q' => sprintf('Kolik stojí %s %s %s?', $in, $v, $loc),
            'a' => 'Orientační ceny „od“ najdeš v katalogu. Finální nabídka vždy podle rozměrů, skla a kování — pošli podklady a připravíme konkrétní cenu.',
        ],
    ];

    // Rotate FAQ order slightly
    if ($var === 1) {
        $faq = [$faq[1], $faq[0], $faq[2]];
    } elseif ($var === 2) {
        $faq = [$faq[2], $faq[0], $faq[1]];
    }

    $katalog_slug = (string) ($category['katalog_slug'] ?? $slug_cat);
    $price = null;
    if (function_exists('sklo_katalog_for_slug')) {
        $cat = sklo_katalog_for_slug($katalog_slug);
        if ($cat && !empty($cat['price_from'])) {
            $price = (string) $cat['price_from'];
        }
    }

    $service = sprintf(
        'Dodáváme a montujeme i %s %s / po celé ČR. Pracujeme online — zaměříš sám, nebo přijedeme zaměřit. Žádná fiktivní „pobočka %s“ — jen výroba na míru a montáž k tobě.',
        $v,
        $loc,
        $name
    );

    return [
        'h1'         => $product . ' ' . $name,
        'intro'      => $intro,
        'bullets'    => array_values(array_filter($bullet_sets[$var] ?: $bullet_sets[0])),
        'service'    => $service,
        'local_note' => $local_note,
        'faq'        => $faq,
        'seo_title'  => sprintf('%s %s na míru | Sklospeciál', $product, $name),
        'seo_desc'   => sprintf(
            '%s %s %s (%s) — online zaměření, výroba na míru, montáž. Rodinná firma, 30 let.',
            $product,
            $v,
            $loc,
            $kraj !== '' ? $kraj : 'ČR'
        ),
        'price_from' => $price,
    ];
}

/**
 * Render city link cloud for a pillar page.
 */
function sklo_render_city_cloud(string $category_slug): void
{
    $cat = sklo_seo_category_by_slug($category_slug);
    if (!$cat) {
        return;
    }
    $cities = sklo_seo_cities();
    $label = (string) $cat['name'];
    echo '<section class="sklo-section sklo-city-cloud">';
    echo '<div class="sklo-wrap">';
    echo '<header class="sklo-section__head sklo-section__head--center">';
    echo '<p class="sklo-eyebrow">Dodávka a montáž</p>';
    echo '<h2>Dodáváme i do…</h2>';
    echo '<p>Pracujeme online po celé ČR — níže města, kde lidé často hledají ' . esc_html(mb_strtolower($label)) . '. Bez fiktivních poboček.</p>';
    echo '</header>';
    echo '<ul class="sklo-city-cloud__list">';
    foreach ($cities as $city) {
        $url = home_url('/' . $category_slug . '/' . $city['slug'] . '/');
        printf(
            '<li><a href="%s">%s %s</a></li>',
            esc_url($url),
            esc_html((string) $cat['h1_product']),
            esc_html($city['name'])
        );
    }
    echo '</ul>';
    echo '<p class="sklo-city-cloud__note"><a class="sklo-link" href="' . esc_url(home_url('/mapa-stranek/')) . '">Kompletní mapa stránek</a></p>';
    echo '</div></section>';
}
