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
 * Unique fields (housing / why / logistics / faq) keep landings from being near-duplicates.
 * Base: jsme z Havířova (dílna v okolí — Horní Bludovice) — not a showroom network.
 *
 * @return list<array<string, mixed>>
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
            'profile' => 'far_batch',
            'distance' => 'zhruba 360 km z Havířova',
            'areas' => 'Vinohrady, Karlín, panelová sídliště i novostavby',
            'housing' => 'Pražské byty — od paneláků po činžáky s nestandardními otvory — často potřebují sklo na míru, ne katalogový rozměr z hobbymarketu.',
            'why' => 'Do Prahy je z Havířova zhruba 360 km. Montáže proto dávkujeme do společných výjezdů (typicky 3–5 zakázek) a dopravu plánujeme mimo špičku podle domu a výtahu. Showroom v Praze nemáme — nabídku připravíme z fotek a rozměrů, na místě jsme až při zaměření nebo montáži.',
            'logistics' => 'Napište nám a zařadíme vás do nejbližšího pražského okna. Volitelné zaměření na místě je možné; většina zákazníků změří podle návodu a pošle fotky.',
            'note' => 'V Praze často řešíme panelové byty a posuvné do pouzdra. Montáže dávkujeme do společných výjezdů (typicky 3–5 zakázek).',
            'faq' => [
                [
                    'q' => 'Dojíždíte na zaměření do Prahy?',
                    'a' => 'Ano — volitelné zaměření domluvíme, často v rámci společného pražského výjezdu. Jinak stačí návod na zaměření a fotky; nabídku připravíme na dálku.',
                ],
                [
                    'q' => 'Jak řešíte montáž v pražském paneláku s výtahem?',
                    'a' => 'Před výjezdem se domluvíme na přístupu, patře a rozměrech výtahu. Sklo vozíme připravené z dílny — na místě montujeme, neřežeme ani nelakujeme.',
                ],
            ],
        ],
        [
            'slug' => 'brno',
            'name' => 'Brno',
            'locative' => 'Brně',
            'v' => 'v',
            'kraj' => 'Jihomoravský kraj',
            'profile' => 'mid',
            'distance' => 'zhruba 180 km z Havířova',
            'areas' => 'Líšeň, Pisárky, Královo Pole, novostavby na okraji',
            'housing' => 'Brno spojuje starší cihlové byty, panelovou zástavbu i novostavby — otvory a koupelny málokdy sedí na jeden katalogový rozměr.',
            'why' => 'Do Brna je z Havířova zhruba 180 km a jezdíme sem pravidelně. Nemáme showroom ve městě: zaměření podle fotek (nebo na místě), výroba v dílně a montáž v domluveném okně. Zákazníci z Lísně, Pisárek i Králova Pole oceňují, že nemusí čekat na lokální pobočku — výroba jde přímo k nim.',
            'logistics' => 'Termín montáže domluvíme podle kapacity výjezdu na jižní Moravu. Dopravu skla naceníme v nabídce podle objemu.',
            'note' => 'Do Brna a okolí jezdíme pravidelně. Typicky zaměření podle fotek, výroba v dílně a montáž v domluveném okně — bez showroomu ve městě.',
            'faq' => [
                [
                    'q' => 'Dojíždíte na zaměření do Brna?',
                    'a' => 'Ano, volitelné zaměření v Brně a okolí domluvíme. Často ale stačí fotky a rozměry — nabídku připravíme ještě před výjezdem.',
                ],
                [
                    'q' => 'Jak rychle zvládnete montáž na jižní Moravě?',
                    'a' => 'Záleží na výrobě skla a naplánovaném výjezdu. Po schválení nabídky potvrdíme reálný termín — bez fiktivní „skladem zítra“.',
                ],
            ],
        ],
        [
            'slug' => 'ostrava',
            'name' => 'Ostrava',
            'locative' => 'Ostravě',
            'v' => 'v',
            'kraj' => 'Moravskoslezský kraj',
            'profile' => 'near',
            'distance' => 'krátký dojezd z Havířova',
            'areas' => 'Poruba, Ostrava-Jih, centrum, okolní obce',
            'housing' => 'Ostravsko známe zblízka — panelové byty, zděné domy i rekonstrukce, kde skleněné prvky musí sedět na skutečný otvor.',
            'why' => 'Jsme z Havířova, takže Ostrava a okolí má kratší logistiku než zbytek ČR. Pořád platí remote model: nabídku připravíme online, showroom ve městě nemáme. Montáž i volitelné zaměření ale plánujeme snáz díky vzdálenosti.',
            'logistics' => 'Dojezd je krátký — termíny montáže bývají pružnější než u vzdálenějších krajů. Sklo vyrábíme na míru v dílně a dovezeme.',
            'note' => 'Jsme z Havířova — Ostrava a okolí máme v běžném dojezdu, kratší cesta na montáž i volitelné zaměření.',
            'faq' => [
                [
                    'q' => 'Jste z Ostravy, nebo jen sem jezdíte?',
                    'a' => 'Jsme z Havířova — Ostrava je v našem běžném dojezdu. Pobočku ani showroom v Ostravě nemáme.',
                ],
                [
                    'q' => 'Umíte zaměřit i v okolních obcích?',
                    'a' => 'Ano. Moravskoslezský kraj obsluhujeme běžně — pošli lokalitu v poptávce a domluvíme zaměření nebo montáž.',
                ],
            ],
        ],
        [
            'slug' => 'plzen',
            'name' => 'Plzeň',
            'locative' => 'Plzni',
            'v' => 'v',
            'kraj' => 'Plzeňský kraj',
            'profile' => 'far',
            'distance' => 'západ Čech — delší trasa z Havířova',
            'areas' => 'město Plzeň i okolní obce Plzeňského kraje',
            'housing' => 'Plzeňské byty a domy sahají od starší zástavby po novostavby — u skla rozhoduje přesný rozměr otvoru a přístup při montáži.',
            'why' => 'Do Plzně dodáváme stejným modelem jako po celé ČR: online podklady, výroba na míru z Havířova, doprava firemním autem nebo přepravní službou podle objemu. Showroom v Plzni nemáme — jezdíme za tebou až když je co montovat (nebo zaměřit).',
            'logistics' => 'Delší trasu plánujeme s předstihem. Cenu dopravy a montáže uvidíš v nabídce podle vzdálenosti a typu skla.',
            'note' => 'Do Plzně dodáváme po celé ČR modelu: online podklady, výroba na míru, doprava firemním autem nebo přepravní službou podle objemu.',
            'faq' => [
                [
                    'q' => 'Vyplatí se montáž až z Moravy do Plzně?',
                    'a' => 'Ano, u zakázkového skla je výroba v dílně stejně nutná. Dopravu a montáž naceníme dopředu — bez skrytých příplatků na místě.',
                ],
                [
                    'q' => 'Stačí poslat fotky z Plzně, nebo musíte přijet?',
                    'a' => 'U většiny zakázek stačí fotky a rozměry podle návodu. Volitelné zaměření domluvíme, když si nejsi jistý měřením.',
                ],
            ],
        ],
        [
            'slug' => 'liberec',
            'name' => 'Liberec',
            'locative' => 'Liberci',
            'v' => 'v',
            'kraj' => 'Liberecký kraj',
            'profile' => 'far',
            'distance' => 'Liberecko a podhůří — delší dojezd',
            'areas' => 'Liberec, Jablonec a podhorské obce',
            'housing' => 'Liberec a podhůří znamená byty i domy, kde hraje roli přístup, terén a často i vyšší vlhkost u vstupů a balkonů.',
            'why' => 'Liberecko je od Havířova dál — montáž a volitelné zaměření proto plánujeme s předstihem. Nabídku připravíme z fotek a rozměrů; showroom v Liberci nemáme. Sklo vyrobíme v dílně a dovezeme, až je termín jistý.',
            'logistics' => 'Počítej s delším dojezdem v ceně dopravy. Termín potvrdíme po schválení nabídky, ať sedí na tvoji stavbu nebo rekonstrukci.',
            'note' => 'Liberec a podhůří — počítáme s delším dojezdem. Montáž a volitelné zaměření plánujeme s předstihem; nabídku připravíme z fotek a rozměrů.',
            'faq' => [
                [
                    'q' => 'Montujete i v Jablonci nebo okolních obcích?',
                    'a' => 'Ano, Liberecký kraj bereme jako jednu servisní oblast. Napiš obec do poptávky — naceníme dopravu individuálně.',
                ],
                [
                    'q' => 'Jak řešíte dopravu skla do podhůří?',
                    'a' => 'Sklo balíme a vozíme jako křehký náklad. Termín volíme podle počasí a přístupu k domu — detaily probereme před výjezdem.',
                ],
            ],
        ],
        [
            'slug' => 'olomouc',
            'name' => 'Olomouc',
            'locative' => 'Olomouci',
            'v' => 'v',
            'kraj' => 'Olomoucký kraj',
            'profile' => 'mid',
            'distance' => 'Olomoucko v dosahu z Moravy',
            'areas' => 'Olomouc, okolní obce a širší Haná',
            'housing' => 'Olomouc míchá historické centrum, sídliště i satelity — u skleněných prvků často řešíme atypické výšky a rekonstrukce starších otvorů.',
            'why' => 'Olomoucko je z Havířova v rozumném dosahu. Často kombinujeme DIY zaměření s montáží na místě — bez fiktivní pobočky ve městě. Výroba probíhá v dílně, k tobě jedeme s hotovým sklem.',
            'logistics' => 'Termíny domlouváme podle výjezdů na střední Moravu. Dopravu a montáž vidíš v nabídce předem.',
            'note' => 'Olomoucko je v dosahu z Moravy. Často kombinujeme DIY zaměření s montáží na místě — bez fiktivní pobočky ve městě.',
            'faq' => [
                [
                    'q' => 'Dojíždíte i mimo Olomouc — třeba do Přerova?',
                    'a' => 'Ano, Olomoucký kraj obsluhujeme. Pošli adresu nebo obec — naplánujeme montáž a dopravu podle vzdálenosti.',
                ],
                [
                    'q' => 'Musím mít showroom návštěvu před objednávkou?',
                    'a' => 'Ne. Showroom v Olomouci nemáme. Stačí fotky, rozměry a domluva přes WhatsApp nebo poptávku.',
                ],
            ],
        ],
        [
            'slug' => 'ceske-budejovice',
            'name' => 'České Budějovice',
            'locative' => 'Českých Budějovicích',
            'v' => 'v',
            'kraj' => 'Jihočeský kraj',
            'profile' => 'far',
            'distance' => 'jižní Čechy — delší trasa, pečlivá doprava skla',
            'areas' => 'České Budějovice a Jihočeský kraj',
            'housing' => 'Jižní Čechy — od městských bytů po domy na okraji — u skla vždy řešíme bezpečnou dopravu křehkého nákladu na delší trasu.',
            'why' => 'Jižní Čechy obsluhujeme na dálku z Havířova. Dopravu skla plánujeme pečlivě; termín a cenu dopravy uvidíš v nabídce. Showroom v Budějovicích nemáme — online podklady, výroba na míru, montáž u tebe.',
            'logistics' => 'Delší trasu spojujeme s pevným termínem. Pokud chceš jen dodávku bez montáže, domluvíme předání individuálně.',
            'note' => 'Jižní Čechy obsluhujeme na dálku. Dopravu skla plánujeme pečlivě (křehký náklad) — termín a cenu dopravy uvidíš v nabídce.',
            'faq' => [
                [
                    'q' => 'Dovážíte sklo i na jih Čech bez montáže?',
                    'a' => 'Ano — v poptávce zvolíš jen dodávku, nebo dodávku s montáží. Cenu dopravy naceníme podle objemu a vzdálenosti.',
                ],
                [
                    'q' => 'Jak dlouho dopředu plánovat montáž v Budějovicích?',
                    'a' => 'Ideálně hned po schválení nabídky. U delších tras rezervujeme výjezd s předstihem, ať sedí na tvoji rekonstrukci.',
                ],
            ],
        ],
        [
            'slug' => 'hradec-kralove',
            'name' => 'Hradec Králové',
            'locative' => 'Hradci Králové',
            'v' => 'v',
            'kraj' => 'Královéhradecký kraj',
            'profile' => 'far',
            'distance' => 'východní Čechy — výjezd po domluvě',
            'areas' => 'Hradec Králové a okolí',
            'housing' => 'Hradec a okolí — sídliště, rodinné domy i rekonstrukce — typicky začínáme online: fotky, rozměry, nabídka, pak výroba.',
            'why' => 'Hradecko obsluhujeme remote modelem z Havířova. Montáž domluvíme podle lokality; volitelné zaměření, pokud nechceš měřit sám. Žádná fiktivní pobočka ve městě — jen dílna a výjezd k tobě.',
            'logistics' => 'Doprava a montáž po domluvě; cenu uvidíš v nabídce. Termín potvrdíme po výrobě skla.',
            'note' => 'Hradec a okolí — online proces, výroba na míru. Montáž domluvíme podle lokality; volitelné zaměření, pokud nechceš měřit sám.',
            'faq' => [
                [
                    'q' => 'Obsluhujete i obce kolem Hradce?',
                    'a' => 'Ano. Napiš obec do poptávky — naceníme dojezd a navrhneme termín montáže.',
                ],
                [
                    'q' => 'Jak přesně mám zaměřit, když nepřijedete hned?',
                    'a' => 'Postup je v návodu na zaměření. Pošli i fotky otvoru z více úhlů — případné nejasnosti doladíme po WhatsAppu.',
                ],
            ],
        ],
        [
            'slug' => 'usti-nad-labem',
            'name' => 'Ústí nad Labem',
            'locative' => 'Ústí nad Labem',
            'v' => 'v',
            'kraj' => 'Ústecký kraj',
            'profile' => 'far',
            'distance' => 'severní Čechy — remote servis z Havířova',
            'areas' => 'Ústí nad Labem a Ústecký kraj',
            'housing' => 'Ústecko — svažitý terén, sídliště i rodinné domy — u montáže skla řešíme přístup a bezpečné vynášení stejně pečlivě jako samotný rozměr.',
            'why' => 'Ústecko obsluhujeme stejným remote modelem jako zbytek ČR. Pošli rozměry a fotky — nabídku a termín montáže připravíme individuálně. Showroom v Ústí nemáme; jsme z Havířova a jezdíme za tebou.',
            'logistics' => 'Dopravu a montáž naceníme podle vzdálenosti. Termín výjezdu potvrdíme po schválení nabídky.',
            'note' => 'Ústecko obsluhujeme stejným remote modelem jako zbytek ČR. Pošli rozměry a fotky — nabídku a termín montáže připravíme individuálně.',
            'faq' => [
                [
                    'q' => 'Jezdíte i do okolí Ústí — Děčín, Litoměřice?',
                    'a' => 'Ano, Ústecký kraj řešíme individuálně. Obec napiš do poptávky, ať správně naceníme dopravu.',
                ],
                [
                    'q' => 'Je možné jen dodávka bez montáže?',
                    'a' => 'Ano. V poptávce zvolíš dodávku, nebo dodávku s montáží. Sklo připravíme na míru v obou případech.',
                ],
            ],
        ],
        [
            'slug' => 'pardubice',
            'name' => 'Pardubice',
            'locative' => 'Pardubicích',
            'v' => 'v',
            'kraj' => 'Pardubický kraj',
            'profile' => 'far',
            'distance' => 'východní Čechy — dodávka a montáž po domluvě',
            'areas' => 'Pardubice a Pardubický kraj',
            'housing' => 'Pardubice a východní Čechy — byty i domy po rekonstrukci — skleněné prvky ladíme podle skutečného otvoru, ne podle „univerzální“ tabulky.',
            'why' => 'Pardubicko bereme jako běžnou servisní oblast po domluvě. Bez showroomu; vše řešíme online a na místě až při montáži nebo zaměření. Výroba probíhá u nás v Havířově / okolí, k tobě dovezeme hotové sklo.',
            'logistics' => 'Termín a cenu dopravy uvidíš v nabídce. Často stačí DIY zaměření — ušetříš výjezd navíc.',
            'note' => 'Pardubice a východní Čechy — dodávka a montáž po domluvě. Bez showroomu; vše řešíme online a na místě až při montáži nebo zaměření.',
            'faq' => [
                [
                    'q' => 'Dá se spojit montáž v Pardubicích s okolními zakázkami?',
                    'a' => 'Když to kapacita dovolí, výjezdy sdružujeme. Napiš termínové preference — ozveme se s reálným oknem.',
                ],
                [
                    'q' => 'Potřebujete projekt od architekta?',
                    'a' => 'Ne. Stačí rozměry, fotky a představa. Když máš výkres, pošli ho — urychlí to nabídku.',
                ],
            ],
        ],
        [
            'slug' => 'zlin',
            'name' => 'Zlín',
            'locative' => 'Zlíně',
            'v' => 've',
            'kraj' => 'Zlínský kraj',
            'profile' => 'mid',
            'distance' => 'Zlínsko v rozumném dojezdu z Moravy',
            'areas' => 'Zlín, Otrokovice a okolní obce',
            'housing' => 'Zlínsko — baťovská zástavba, sídliště i nové domy — u skla často řešíme přesné výšky a atypické otvory po rekonstrukci.',
            'why' => 'Zlínsko máme v rozumném dojezdu z Havířova. Často stačí fotky a rozměry; montáž a doprava se domluví podle projektu. Showroom ve Zlíně nemáme — jezdíme za tebou s hotovou výrobou.',
            'logistics' => 'Dojezd je kratší než do Čech, termíny bývají pružnější. Cenu montáže uvidíš v nabídce.',
            'note' => 'Zlínsko máme v rozumném dojezdu z Moravy. Často stačí fotky a rozměry; montáž a doprava se domluví podle konkrétního projektu.',
            'faq' => [
                [
                    'q' => 'Montujete i v Otrokovicích nebo Uherském Hradišti?',
                    'a' => 'Ano, Zlínský kraj obsluhujeme. Obec napiš do poptávky — naplánujeme výjezd.',
                ],
                [
                    'q' => 'Jak probíhá první kontakt?',
                    'a' => 'Nejjednodušší je WhatsApp nebo poptávka s fotkami. Ozveme se s dotazy k rozměrům a připravíme nabídku.',
                ],
            ],
        ],
        [
            'slug' => 'havirov',
            'name' => 'Havířov',
            'locative' => 'Havířově',
            'v' => 'v',
            'kraj' => 'Moravskoslezský kraj',
            'profile' => 'near',
            'distance' => 'naše domácí město — jsme z Havířova',
            'areas' => 'Havířov a okolní obce včetně Horních Bludovic',
            'housing' => 'Havířov je panelové město s častými rekonstrukcemi koupelen a interiérů — sklo na míru tady dává smysl kvůli skutečným otvorům, ne katalogu.',
            'why' => 'Jsme z Havířova — tady máme domácí zázemí a dílnu v okolí. Kratší logistika montáže i volitelného zaměření, pořád ale bez městského showroomu: nabídku připravíme online, výroba jde z dílny.',
            'logistics' => 'Dojezd je nejkratší z našich městských landings — termíny montáže a zaměření domlouváme pružně v regionu.',
            'note' => 'Jsme z Havířova — kratší logistika montáže i volitelného zaměření v regionu, bez městského showroomu.',
            'faq' => [
                [
                    'q' => 'Můžu si sklo vyzvednout u vás v Havířově?',
                    'a' => 'Po domluvě ano — záleží na typu a balení. Častěji ale dovezeme a montujeme na místě.',
                ],
                [
                    'q' => 'Děláte zaměření i o víkendu v Havířově?',
                    'a' => 'Termíny domlouváme individuálně. Napiš preference do poptávky — ozveme se s reálnými okny.',
                ],
            ],
        ],
        [
            'slug' => 'kladno',
            'name' => 'Kladno',
            'locative' => 'Kladně',
            'v' => 'v',
            'kraj' => 'Středočeský kraj',
            'profile' => 'far_batch',
            'distance' => 'Středočesko — často ve stejném okně jako Praha',
            'areas' => 'Kladno a Středočeský kraj',
            'housing' => 'Kladno a okolí — panelové byty i rodinné domy — často ladíme stejný remote proces jako u Prahy, jen s kratší odbočkou ze středočeské trasy.',
            'why' => 'Kladno a Středočesko často spojujeme s pražskými termíny montáže. Online zaměření, výroba na míru z Havířova, doprava podle objemu. Showroom na Kladně nemáme — na místě jsme při zaměření nebo montáži.',
            'logistics' => 'Když vychází pražský výjezd, umíme Kladno zařadit do stejného okna. Napište termínové preference do poptávky.',
            'note' => 'Kladno a Středočesko často spojujeme s pražskými termíny montáže. Online zaměření, výroba na míru, doprava podle objemu zakázky.',
            'faq' => [
                [
                    'q' => 'Jste schopni montáž na Kladně spojit s Prahou?',
                    'a' => 'Často ano — výjezdy do středních Čech sdružujeme. Ozveme se s nejbližším společným termínem.',
                ],
                [
                    'q' => 'Stačí zaměření podle návodu?',
                    'a' => 'Ano, většina zakázek takhle běží. Když si nejsi jistý, domluvíme volitelné zaměření na místě.',
                ],
            ],
        ],
        [
            'slug' => 'jihlava',
            'name' => 'Jihlava',
            'locative' => 'Jihlavě',
            'v' => 'v',
            'kraj' => 'Kraj Vysočina',
            'profile' => 'far',
            'distance' => 'Vysočina — delší trasa, plánování s předstihem',
            'areas' => 'Jihlava a Kraj Vysočina',
            'housing' => 'Vysočina — rodinné domy, rekonstrukce i městské byty — u skla počítáme s delší trasou, proto termín montáže plánujeme dopředu.',
            'why' => 'Na Vysočinu jezdíme s předstihem: nabídku připravíme z podkladů, sklo vyrobíme v Havířově / okolí a dovezeme v domluveném okně. Showroom v Jihlavě nemáme — žádná fiktivní pobočka, jen výroba a montáž k tobě.',
            'logistics' => 'Delší trasy = pevnější plánování. Cenu dopravy uvidíš v nabídce; volitelné zaměření domluvíme podle potřeby.',
            'note' => 'Vysočina — delší trasy, proto termín montáže plánujeme s předstihem. Nabídku připravíme z podkladů; showroom ve městě nemáme.',
            'faq' => [
                [
                    'q' => 'Obsluhujete celou Vysočinu, nebo jen Jihlavu?',
                    'a' => 'Celý kraj podle domluvy. Obec napiš do poptávky — naceníme dojezd férově podle vzdálenosti.',
                ],
                [
                    'q' => 'Co když nestíhám přesné zaměření?',
                    'a' => 'Domluvíme volitelné zaměření na místě, nebo ti přes WhatsApp pomůžeme doladit postup podle fotek.',
                ],
            ],
        ],
        [
            'slug' => 'teplice',
            'name' => 'Teplice',
            'locative' => 'Teplicích',
            'v' => 'v',
            'kraj' => 'Ústecký kraj',
            'profile' => 'far',
            'distance' => 'Teplicko — remote model po celé ČR',
            'areas' => 'Teplice a okolí v Ústeckém kraji',
            'housing' => 'Teplicko — lázeňské domy, sídliště i okolní obce — skleněné prvky řešíme na míru a dopravu naceníme podle vzdálenosti z Havířova.',
            'why' => 'Teplicko obsluhujeme remote modelem: fotky a rozměry → nabídka → výroba → montáž. Bez fiktivní pobočky. Jsme z Havířova; k tobě jedeme s hotovým sklem.',
            'logistics' => 'Dopravu a montáž naceníme podle vzdálenosti a typu skla. Termín výjezdu potvrdíme po schválení nabídky.',
            'note' => 'Teplicko obsluhujeme remote modelem po celé ČR. Dopravu a montáž naceníme podle vzdálenosti a typu skla — bez fiktivní pobočky.',
            'faq' => [
                [
                    'q' => 'Dojíždíte na zaměření do Teplic?',
                    'a' => 'Volitelné zaměření domluvíme. Často stačí návod a fotky — ušetříš výjezd a nabídku máš rychleji.',
                ],
                [
                    'q' => 'Montujete i o víkendu?',
                    'a' => 'Termíny jsou individuální podle výjezdu. Preference napiš do poptávky, ozveme se s možnostmi.',
                ],
            ],
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
 * Uppercase first letter of preposition v/ve for sentence starts.
 */
function sklo_seo_ucfirst_v(string $v): string
{
    if ($v === '') {
        return 'V';
    }
    return mb_strtoupper(mb_substr($v, 0, 1)) . mb_substr($v, 1);
}

/**
 * Product-specific housing hook (unique opener per pillar × city).
 *
 * @param array<string, mixed> $category
 * @param array<string, mixed> $city
 */
function sklo_seo_city_product_housing(array $category, array $city): string
{
    $slug_cat = (string) ($category['slug'] ?? '');
    $loc = (string) $city['locative'];
    $v = (string) $city['v'];
    $name = (string) $city['name'];
    $areas = trim((string) ($city['areas'] ?? ''));
    $housing = trim((string) ($city['housing'] ?? ''));
    $var = sklo_seo_variation($slug_cat, (string) $city['slug'], 3);
    $area_bit = $areas !== '' ? ' (' . $areas . ')' : '';

    $by_cat = [
        'sklenene-dvere' => [
            sprintf('Hledáš skleněné dveře %s %s%s? Místní byty a domy málokdy sedí na katalogový rozměr — ostění, pouzdro i přístup při montáži rozhodují.', $v, $loc, $area_bit),
            $housing !== ''
                ? $housing . ' U dveří to znamená: přesný otvor, typ křídla a fotky ostění ještě před výrobou.'
                : sprintf('%s %s u skleněných dveří řešíme nestandardní otvory — panelák, činžák i dům.', sklo_seo_ucfirst_v($v), $loc),
            sprintf('Skleněné dveře %s %s dávají světlo mezi místnostmi, ale jen když sedí na skutečný otvor — ne na „univerzální“ tabulku.', $v, $loc),
        ],
        'sprchove-kouty' => [
            sprintf('Rekonstrukce koupelny %s %s%s skoro nikdy nesedí na hotový sprchový set z hobbymarketu — vanička, odtok i výška stropu jsou jiné.', $v, $loc, $area_bit),
            $housing !== ''
                ? $housing . ' U sprch počítáme s nerovnými stěnami, atypickou vaničkou a kováním, které drží v reálném prostoru.'
                : sprintf('Sprchové kouty %s %s vyrábíme podle koupelny, ne podle katalogové „univerzální“ sestavy.', $v, $loc),
            sprintf('V koupelnách %s %s rozhoduje rozměr na místě: walk-in stěna, rohový kout nebo zástěna — podle odtoku a dispozice.', $v, $loc),
        ],
        'zabradli' => [
            sprintf('Skleněné zábradlí %s %s%s ladíme podle schodiště, galerie nebo terasy — kotvení a výška stupňů jsou vždy jiné.', $v, $loc, $area_bit),
            $housing !== ''
                ? $housing . ' U zábradlí navíc řešíme kotvení do stupňů, bočnice nebo podlahy a bezpečný přístup při montáži.'
                : sprintf('Zábradlí %s %s navrhujeme podle stavby — ne jako katalogový „bm od oka“.', $v, $loc),
            sprintf('%s %s u skleněného zábradlí často řešíme otevřená schodiště v domech i mezonety — stačí fotky z více úhlů.', sklo_seo_ucfirst_v($v), $loc),
        ],
        'strisky' => [
            sprintf('Skleněná stříška %s %s%s chrání vstup — šířka dveří, fasáda a typ kotvení (konzoly / táhla) rozhodují o návrhu.', $v, $loc, $area_bit),
            $housing !== ''
                ? $housing . ' U stříšek potřebujeme fotky fasády a šířku vstupu, ať sedí nosnost i vzhled.'
                : sprintf('Stříšky %s %s vyrábíme podle fasády a rozměrů vstupu — ne jako hotový „univerzální“ dílec.', $v, $loc),
            sprintf('%s %s u stříšek řešíme i odtok vody a přístup k fasádě při montáži — pošli fotky vchodu.', sklo_seo_ucfirst_v($v), $loc),
        ],
        'sklenene-pricky' => [
            sprintf('Skleněná příčka %s %s%s oddělí pracovnu, open-space nebo chodbu a nechá projít světlo — bez těžké zděné stěny.', $v, $loc, $area_bit),
            $housing !== ''
                ? $housing . ' U příček ladíme výšku podhledu, kotvení do stropu/podlahy a typ skla (čiré, matné).'
                : sprintf('Příčky %s %s navrhujeme podle dispozice — pevná výplň i kombinace s dveřním křídlem.', $v, $loc),
            sprintf('%s %s u skleněných příček často řešíme atypickou výšku a napojení na stávající podhled — pošli půdorys nebo fotky.', sklo_seo_ucfirst_v($v), $loc),
        ],
        'francouzske-balkony' => [
            sprintf('Francouzský balkon %s %s%s je skleněná výplň u francouzského okna — výška, šířka a kotvení k fasádě musí sedět na míru.', $v, $loc, $area_bit),
            $housing !== ''
                ? $housing . ' U francouzských balkonů navíc řešíme parapet, fasádu a přístup při montáži z bytu nebo zvenku.'
                : sprintf('Francouzské balkony %s %s vyrábíme podle otvoru — fotky zvenku i zevnitř urychlí nabídku.', $v, $loc),
            sprintf('%s %s u francouzských balkonů počítáme s bezpečným kotvením a domluvou přístupu před výjezdem.', sklo_seo_ucfirst_v($v), $loc),
        ],
    ];

    $list = $by_cat[$slug_cat] ?? [
        $housing !== '' ? $housing : sprintf('Hledáš %s %s %s?', (string) ($category['in_phrase'] ?? ''), $v, $loc),
    ];

    return $list[$var] ?? $list[0];
}

/**
 * Product-specific angle for a city (unique sentence per pillar × city seed).
 *
 * @param array<string, mixed> $category
 * @param array<string, mixed> $city
 */
function sklo_seo_city_product_angle(array $category, array $city): string
{
    $slug_cat = (string) ($category['slug'] ?? '');
    $loc = (string) $city['locative'];
    $v = (string) $city['v'];
    $areas = trim((string) ($city['areas'] ?? ''));
    $var = sklo_seo_variation($slug_cat, (string) $city['slug'], 3);

    $by_cat = [
        'sklenene-dvere' => [
            sprintf(
                'U dveří %s %s nejčastěji ladíme posuvné do pouzdra, posuv po stěně nebo kyvné křídlo — podle šířky otvoru a toho, jestli řešíš panelák, činžák nebo dům%s.',
                $v,
                $loc,
                $areas !== '' ? ' (' . $areas . ')' : ''
            ),
            sprintf(
                'Skleněné dveře %s %s dávají smysl tam, kde chceš světlo mezi místnostmi, ale katalogový rozměr nesedí. Pošli šířku, výšku a fotky ostění — navrhujeme na míru z Havířova.',
                $v,
                $loc
            ),
            sprintf(
                '%s %s u dveří řešíme i přístup při montáži (schody, výtah, úzká chodba). Sklo přijede hotové z naší dílny u Havířova — na místě usadíme a seřídíme.',
                sklo_seo_ucfirst_v($v),
                $loc
            ),
        ],
        'sprchove-kouty' => [
            sprintf(
                'U sprch %s %s typicky ladíme walk-in stěny a kouty podle vaničky, výšky stropu a typu kování — rekonstrukce koupelny málokdy sedí na „univerzální“ sestavu.',
                $v,
                $loc
            ),
            sprintf(
                'Sprchový kout %s %s vyrábíme podle tvých rozměrů: jedna stěna, rohová sestava i atyp. Zaměříš sám, nebo přijedeme; sklo jde z dílny u Havířova, montáž domluvíme individuálně.',
                $v,
                $loc
            ),
            sprintf(
                'V koupelnách %s %s počítáme s nerovnými stěnami a odtokem mimo osu — proto nejdřív fotky a míry, pak výroba skla na míru.',
                $v,
                $loc
            ),
        ],
        'zabradli' => [
            sprintf(
                'U zábradlí %s %s počítáme s kotvením do stupňů, bočnice nebo podlahy — pošli fotky schodiště nebo galerie z více úhlů.',
                $v,
                $loc
            ),
            sprintf(
                'Skleněné zábradlí %s %s navrhujeme podle výšky stupňů a typu stavby. Bezpečnostní sklo a profily doladíme v nabídce — ne „od oka“. Výroba z Havířova, montáž u tebe.',
                $v,
                $loc
            ),
            sprintf(
                '%s %s u zábradlí často řešíme otevřená schodiště v domech i mezonety. Stačí náčrt nebo fotky; projekt od architekta pomůže, ale není nutný.',
                sklo_seo_ucfirst_v($v),
                $loc
            ),
        ],
        'strisky' => [
            sprintf(
                'U stříšek %s %s potřebujeme šířku vstupu a fotky fasády kvůli konzolám nebo táhlům — od toho se odvíjí nosnost i vzhled.',
                $v,
                $loc
            ),
            sprintf(
                'Skleněná stříška %s %s ochrání vstup před deštěm a sněhem. Výroba podle rozměrů v Havířově / okolí; montáž naplánujeme s ohledem na výšku a přístup k fasádě.',
                $v,
                $loc
            ),
            sprintf(
                '%s %s u stříšek řešíme i orientaci ke světovým stranám a odtok vody — pošli fotky dveří/vchodu a šířku otvoru.',
                sklo_seo_ucfirst_v($v),
                $loc
            ),
        ],
        'sklenene-pricky' => [
            sprintf(
                'U příček %s %s často oddělujeme open-space, pracovnu nebo chodbu — světlo zůstane, plná zeď ne.',
                $v,
                $loc
            ),
            sprintf(
                'Skleněná příčka %s %s může být pevná výplň i kombinace s dveřním křídlem. Zaměření podle dispozice; výroba na míru u nás v Havířově / okolí.',
                $v,
                $loc
            ),
            sprintf(
                '%s %s u příček ladíme výšku podhledu, kotvení do stropu/podlahy a typ skla (čiré, matné). Pošli půdorys nebo fotky.',
                sklo_seo_ucfirst_v($v),
                $loc
            ),
        ],
        'francouzske-balkony' => [
            sprintf(
                'U francouzských balkonů %s %s řešíme hlavně výšku, šířku a kotvení u francouzského okna — fasáda a parapet rozhodují.',
                $v,
                $loc
            ),
            sprintf(
                'Francouzský balkon %s %s vyrábíme jako skleněnou výplň na míru z Havířova. Potřebujeme fotky otvoru zvenku i zevnitř a přesné míry.',
                $v,
                $loc
            ),
            sprintf(
                '%s %s u francouzských balkonů počítáme s bezpečným kotvením a přístupem při montáži z bytu nebo lešením — domluvíme před výjezdem.',
                sklo_seo_ucfirst_v($v),
                $loc
            ),
        ],
    ];

    $list = $by_cat[$slug_cat] ?? [
        sprintf('%s %s %s dodáváme na míru — online nabídka, výroba z Havířova, montáž u tebe.', (string) $category['h1_product'], $v, $loc),
    ];

    return $list[$var] ?? $list[0];
}

/**
 * Product-specific why sentence (unique per pillar × city).
 *
 * @param array<string, mixed> $category
 * @param array<string, mixed> $city
 */
function sklo_seo_city_product_why(array $category, array $city): string
{
    $slug_cat = (string) ($category['slug'] ?? '');
    $loc = (string) $city['locative'];
    $v = (string) $city['v'];
    $var = sklo_seo_variation($slug_cat . '|why', (string) $city['slug'], 3);

    $by_cat = [
        'sklenene-dvere' => [
            sprintf('Konkrétně u skleněných dveří %s %s: nejdřív typ křídla a ostění, pak výroba v Havířově / okolí a termín montáže u tebe.', $v, $loc),
            sprintf('U dveří %s %s platí: zaměříš sám podle návodu (nebo přijedeme), sklo vyrobíme na míru a dovezeme hotové — na místě usadíme a seřídíme.', $v, $loc),
            sprintf('Pro dveře %s %s nehraje roli „pobočka ve městě“ — hraje přesný rozměr a přístup při montáži. Jsme z Havířova a jezdíme za tebou.', $v, $loc),
        ],
        'sprchove-kouty' => [
            sprintf('Konkrétně u sprch %s %s: vanička/odtok + výška + typ sestavy (walk-in / kout / zástěna), pak výroba skla z Havířova a montáž v domluveném okně.', $v, $loc),
            sprintf('U sprchových koutů %s %s platí: fotky koupelny a míry na více místech — sklo řežeme až když sedí podklady, ne odhad z katalogu.', $v, $loc),
            sprintf('Sprchy %s %s montujeme remote modelem: nabídka online, výroba u nás v Havířově / okolí, na místě až hotová sestava.', $v, $loc),
        ],
        'zabradli' => [
            sprintf('Konkrétně u zábradlí %s %s: kotvení, výška a délka podle schodiště — sklo a profily doladíme v nabídce, vyrobíme z Havířova a namontujeme u tebe.', $v, $loc),
            sprintf('U skleněného zábradlí %s %s potřebujeme fotky z více úhlů (nebo náčrt). Bezpečnostní sklo nejde „od oka“ — proto nejdřív podklady, pak výroba.', $v, $loc),
            sprintf('Zábradlí %s %s řešíme bez fiktivní pobočky: jsme z Havířova, nabídku připravíme na dálku a výjezd naplánujeme podle stavby.', $v, $loc),
        ],
        'strisky' => [
            sprintf('Konkrétně u stříšek %s %s: šířka vstupu, fasáda a typ kotvení — od toho se odvíjí výroba v Havířově / okolí i způsob montáže.', $v, $loc),
            sprintf('U skleněných stříšek %s %s řešíme nosnost a odtok vody ještě před výrobou. Pošli fotky vchodu; termín výjezdu potvrdíme po nabídce.', $v, $loc),
            sprintf('Stříšky %s %s dodáváme remote modelem z Havířova — online podklady, výroba na míru, montáž u fasády v domluveném okně.', $v, $loc),
        ],
        'sklenene-pricky' => [
            sprintf('Konkrétně u příček %s %s: výška podhledu, kotvení a typ skla — vyrobíme na míru z Havířova a usadíme podle dispozice.', $v, $loc),
            sprintf('U skleněných příček %s %s často kombinujeme pevnou výplň s dveřním křídlem. Stačí půdorys nebo fotky — nabídku připravíme online.', $v, $loc),
            sprintf('Příčky %s %s řešíme bez showroomu ve městě: jsme z Havířova, sklo vyrobíme a dovezeme hotové k montáži.', $v, $loc),
        ],
        'francouzske-balkony' => [
            sprintf('Konkrétně u francouzských balkonů %s %s: míry otvoru + fotky fasády — kotvení a sklo doladíme, vyrobíme z Havířova a namontujeme u tebe.', $v, $loc),
            sprintf('U francouzských balkonů %s %s je klíčový přístup při montáži (z bytu / zvenku). Domluvíme ho před výjezdem — žádné překvapení na místě.', $v, $loc),
            sprintf('Francouzské balkony %s %s dodáváme remote modelem: jsme z Havířova, nabídka z podkladů, výroba na míru, montáž v termínu výjezdu.', $v, $loc),
        ],
    ];

    $list = $by_cat[$slug_cat] ?? [
        sprintf('Konkrétně u %s %s %s: zaměříš sám, nebo přijedeme — vyrobíme z Havířova a domluvíme montáž.', (string) ($category['genitive'] ?? ''), $v, $loc),
    ];

    return $list[$var] ?? $list[0];
}

/**
 * Product-specific FAQ items for a city landing.
 *
 * @param array<string, mixed> $category
 * @param array<string, mixed> $city
 * @return list<array{q:string,a:string}>
 */
function sklo_seo_city_product_faq(array $category, array $city): array
{
    $slug_cat = (string) ($category['slug'] ?? '');
    $loc = (string) $city['locative'];
    $v = (string) $city['v'];
    $name = (string) $city['name'];
    $var = sklo_seo_variation($slug_cat . '|faq', (string) $city['slug'], 2);

    $by_cat = [
        'sklenene-dvere' => [
            [
                'q' => sprintf('Jaký typ skleněných dveří se %s %s hodí nejčastěji?', $v, $loc),
                'a' => sprintf('Záleží na šířce otvoru a prostoru vedle: posuv do pouzdra, posuv po stěně nebo kyvné/otočné křídlo. Pošli fotky a míry — doporučíme variantu. Výroba jde z Havířova, montáž %s %s.', $v, $loc),
            ],
            [
                'q' => sprintf('Musím mít stavební pouzdro, když chci posuvné dveře %s %s?', $v, $loc),
                'a' => 'Do pouzdra ano. Bez pouzdra zvol posuv po stěně. Pošli fotky ostění — řekneme, co dává smysl ještě před výrobou.',
            ],
        ],
        'sprchove-kouty' => [
            [
                'q' => sprintf('Walk-in, nebo klasický sprchový kout %s %s?', $v, $loc),
                'a' => sprintf('Záleží na odtoku a dispozici koupelny. Často stačí jedna walk-in stěna; jindy rohová sestava. Pošli fotky vaničky/odtoku — navrhujeme na míru z Havířova, montáž %s %s.', $v, $loc),
            ],
            [
                'q' => sprintf('Jak mám zaměřit sprchu %s %s, když nepřijedete hned?', $v, $loc),
                'a' => 'Šířka a výška na více místech + fotky. Postup je v návodu na zaměření; nejasnosti doladíme po WhatsAppu. Volitelné zaměření domluvíme.',
            ],
        ],
        'zabradli' => [
            [
                'q' => sprintf('Co potřebujete k nabídce na zábradlí %s %s?', $v, $loc),
                'a' => sprintf('Fotky schodiště/galerie z více úhlů, výšku stupňů a představu kotvení. Projekt pomůže, ale není nutný. Sklo vyrobíme v Havířově / okolí a dovezeme k montáži %s %s.', $v, $loc),
            ],
            [
                'q' => sprintf('Děláte skleněné zábradlí %s %s i na mezonet nebo galerii?', $v, $loc),
                'a' => 'Ano — interiér a kryté prostory řešíme běžně. Pošli fotky a míry; exteriéry posuzujeme individuálně podle kotvení.',
            ],
        ],
        'strisky' => [
            [
                'q' => sprintf('Jaký typ stříšky %s %s dává smysl — konzoly, nebo táhla?', $v, $loc),
                'a' => sprintf('Záleží na fasádě, šířce vstupu a nosnosti. Pošli fotky vchodu — navrhujeme kotvení i sklo na míru z Havířova, montáž %s %s.', $v, $loc),
            ],
            [
                'q' => sprintf('Stačí šířka dveří pro nabídku stříšky %s %s?', $v, $loc),
                'a' => 'Šířka je základ, ale potřebujeme i fotky fasády a výšku nad vstupem. Bez toho nejde doladit konzoly/táhla férově.',
            ],
        ],
        'sklenene-pricky' => [
            [
                'q' => sprintf('Umíte skleněnou příčku %s %s i s dveřním křídlem?', $v, $loc),
                'a' => sprintf('Ano — pevná výplň, nebo kombinace s posuvnými/kyvnými dveřmi. Pošli půdorys nebo fotky; výroba z Havířova, montáž %s %s.', $v, $loc),
            ],
            [
                'q' => sprintf('Jak řešíte kotvení příčky %s %s do stropu s podhledem?', $v, $loc),
                'a' => 'Potřebujeme výšku a typ podhledu (sádrokarton / beton). Doladíme v nabídce — proto fotky a míry ještě před výrobou.',
            ],
        ],
        'francouzske-balkony' => [
            [
                'q' => sprintf('Co poslat pro nabídku francouzského balkonu %s %s?', $v, $loc),
                'a' => sprintf('Fotky otvoru zvenku i zevnitř, výšku a šířku, případně detail parapetu. Sklo a kotvení vyrobíme z Havířova; montáž %s %s domluvíme před výjezdem.', $v, $loc),
            ],
            [
                'q' => sprintf('Montujete francouzský balkon %s %s z bytu, nebo zvenku?', $v, $loc),
                'a' => sprintf('Záleží na domu a přístupu. Domluvíme před výjezdem — v %s často řešíme výtah, lešení nebo montáž z interiéru.', $name),
            ],
        ],
    ];

    $list = $by_cat[$slug_cat] ?? [];
    if ($list === []) {
        return [];
    }

    $item = $list[$var] ?? $list[0];
    return [$item];
}

/**
 * Build unique city landing copy (intro + local angle + FAQ + meta).
 *
 * @param array<string, mixed> $category
 * @param array<string, mixed> $city
 * @return array<string, mixed>
 */
function sklo_seo_city_copy(array $category, array $city): array
{
    $product = (string) $category['h1_product'];
    $name = (string) $city['name'];
    $loc = (string) $city['locative'];
    $v = (string) $city['v'];
    $in = (string) $category['in_phrase'];
    $slug_cat = (string) ($category['slug'] ?? '');
    $slug_city = (string) ($city['slug'] ?? '');
    $var = sklo_seo_variation($slug_cat, $slug_city, 3);
    $kraj = trim((string) ($city['kraj'] ?? ''));
    $why = trim((string) ($city['why'] ?? ''));
    $logistics = trim((string) ($city['logistics'] ?? ''));
    $local_note = trim((string) ($city['note'] ?? ''));
    $distance = trim((string) ($city['distance'] ?? ''));
    $areas = trim((string) ($city['areas'] ?? ''));
    $profile = (string) ($city['profile'] ?? 'far');
    $product_housing = sklo_seo_city_product_housing($category, $city);
    $product_angle = sklo_seo_city_product_angle($category, $city);
    $product_why = sklo_seo_city_product_why($category, $city);

    // Intro: product×city housing hook + product angle + honest remote line (Havířov)
    $intro_parts = [];
    $intro_parts[] = $product_housing;
    $intro_parts[] = $product_angle;
    $intro_parts[] = sprintf(
        'Jsme rodinná firma Sklospeciál z Havířova — vyrábíme na míru a dodáváme a montujeme i %s %s. Showroom %s %s nemáme; nabídku připravíme z fotek a rozměrů.',
        $v,
        $loc,
        $v,
        $loc
    );
    if ($kraj !== '') {
        $intro_parts[] = sprintf('Servisní oblast: %s.', $kraj);
    }
    $intro = implode(' ', $intro_parts);

    // Why-local block (H2 body)
    $why_title = sprintf('Proč %s právě %s %s', mb_strtolower($product), $v, $loc);
    $why_local = $why !== '' ? $why : sprintf(
        '%s %s %s dodáváme z Havířova. Online nabídka, výroba na míru, montáž u tebe — bez fiktivní pobočky.',
        $product,
        $v,
        $loc
    );
    if ($logistics !== '') {
        $why_local .= ' ' . $logistics;
    }
    $why_local .= ' ' . $product_why;

    // Bullets: mix city profile + product benefits (unique per city×product seed)
    $profile_bullets = [
        'near' => [
            sprintf('Kratší dojezd z Havířova — montáž %s %s plánujeme pružněji', $v, $loc),
            'Volitelné zaměření v regionu bez týdnů čekání na „pobočku“',
            'Výroba na míru v dílně, na místě jen montáž a seřízení',
        ],
        'mid' => [
            sprintf('Pravidelné výjezdy z Havířova směrem na %s a okolí', $name),
            'Nabídka z fotek a rozměrů — bez nutnosti navštívit showroom',
            sprintf('Doprava a montáž %s %s naceníme dopředu v nabídce', $v, $loc),
        ],
        'far' => [
            sprintf('Delší trasu z Havířova k %s plánujeme s předstihem — termín potvrdíme po nabídce', $name),
            'Sklo jako křehký náklad — doprava firemním autem nebo přepravní službou podle objemu',
            'DIY zaměření podle návodu ušetří výjezd; volitelné zaměření po domluvě',
        ],
        'far_batch' => [
            sprintf('Montáže %s %s dávkujeme do společných výjezdů z Havířova (typicky 3–5 zakázek)', $v, $loc),
            'Dopravu plánujeme mimo špičku podle domu a výtahu',
            'Napiš preferovaný termín — zařadíme tě do nejbližšího okna',
        ],
    ];
    $base_bullets = $profile_bullets[$profile] ?? $profile_bullets['far'];
    $product_benefits = array_values(array_filter((array) ($category['benefits'] ?? [])));
    $extra_sets = [
        [
            $base_bullets[0] ?? '',
            $product_benefits[0] ?? sprintf('%s na míru podle otvoru', $product),
            $base_bullets[1] ?? 'Online nabídka z fotek a rozměrů',
            $base_bullets[2] ?? 'Montáž po domluvě po celé ČR',
        ],
        [
            $product_benefits[0] ?? sprintf('%s na míru', $product),
            $product_benefits[1] ?? 'Zaměříš sám, nebo přijedeme',
            sprintf('Servis %s %s%s', $v, $loc, $areas !== '' ? ' — ' . $areas : ''),
            '30 let zkušeností, třetí generace rodinné firmy z Havířova',
        ],
        [
            $base_bullets[0] ?? '',
            $product_benefits[2] ?? ($product_benefits[0] ?? 'Výroba na míru'),
            'Konkrétní cena podle rozměrů — ne katalog „od oka“',
            'WhatsApp nebo poptávka — bez zbytečných kol',
        ],
    ];
    $bullets = array_values(array_filter(array_map('strval', $extra_sets[$var] ?? $extra_sets[0])));

    // FAQ: product-specific + city-specific + shared honest ones
    $faq = [];
    foreach (sklo_seo_city_product_faq($category, $city) as $item) {
        $faq[] = $item;
    }
    foreach ((array) ($city['faq'] ?? []) as $item) {
        if (!is_array($item)) {
            continue;
        }
        $q = trim((string) ($item['q'] ?? ''));
        $a = trim((string) ($item['a'] ?? ''));
        if ($q !== '' && $a !== '') {
            $faq[] = ['q' => $q, 'a' => $a];
        }
    }

    $shared_faq = [
        [
            'q' => sprintf('Máte showroom nebo pobočku %s %s?', $v, $loc),
            'a' => sprintf(
                'Ne. Jsme z Havířova a pracujeme remote modelem. %s %s dodáváme a montujeme bez fiktivní městské pobočky — nabídku připravíme z fotek a rozměrů.',
                $product,
                $name
            ),
        ],
        [
            'q' => sprintf('Kolik stojí %s %s %s?', $in, $v, $loc),
            'a' => sprintf(
                'Orientační ceny „od“ najdeš v katalogu %s. Finální nabídka vždy podle rozměrů, skla a kování%s — pošli podklady a připravíme konkrétní cenu včetně dopravy.',
                mb_strtolower($product),
                $distance !== '' ? ' (' . $distance . ')' : ''
            ),
        ],
        [
            'q' => sprintf('Jak dlouho trvá dodání %s %s %s?', (string) $category['genitive'], $v, $loc),
            'a' => 'Záleží na typu skla, kování a naplánovaném výjezdu z Havířova. Po schválení nabídky potvrdíme reálný termín výroby i montáže — bez slibů „skladem zítra“.',
        ],
    ];

    // Merge without duplicate questions; cap at 6
    $seen_q = [];
    $merged = [];
    foreach (array_merge($faq, $shared_faq) as $item) {
        $key = mb_strtolower($item['q']);
        if (isset($seen_q[$key])) {
            continue;
        }
        $seen_q[$key] = true;
        $merged[] = $item;
        if (count($merged) >= 6) {
            break;
        }
    }
    if ($var === 1 && count($merged) >= 3) {
        $merged = [$merged[1], $merged[0], ...array_slice($merged, 2)];
    } elseif ($var === 2 && count($merged) >= 3) {
        $merged = [$merged[0], $merged[2], $merged[1], ...array_slice($merged, 3)];
    }
    $faq = $merged;

    $katalog_slug = (string) ($category['katalog_slug'] ?? $slug_cat);
    $price = null;
    if (function_exists('sklo_katalog_for_slug')) {
        $cat = sklo_katalog_for_slug($katalog_slug);
        if ($cat && !empty($cat['price_from'])) {
            $price = (string) $cat['price_from'];
        }
    }

    $service = sprintf(
        'Dodáváme a montujeme i %s %s / po celé ČR. Jsme z Havířova — pracujeme online: zaměříš sám, nebo přijedeme zaměřit. Žádná fiktivní „pobočka %s“.',
        $v,
        $loc,
        $name
    );

    // Internal links: pillar types + sibling cities
    $related = (array) ($category['related'] ?? []);
    $sibling_cities = [];
    $all_cities = sklo_seo_cities();
    $city_count = count($all_cities);
    if ($city_count > 1) {
        $start = (int) (crc32($slug_cat . '|' . $slug_city) % $city_count);
        for ($i = 0; $i < $city_count && count($sibling_cities) < 4; $i++) {
            $c = $all_cities[($start + $i) % $city_count];
            if (($c['slug'] ?? '') === $slug_city) {
                continue;
            }
            $sibling_cities[] = [
                '/' . $slug_cat . '/' . $c['slug'] . '/',
                $product . ' ' . $c['name'],
            ];
        }
    }

    $seo_title = sprintf('%s %s na míru | Sklospeciál', $product, $name);
    $seo_desc_bits = [
        sprintf('%s %s %s', $product, $v, $loc),
    ];
    if ($kraj !== '') {
        $seo_desc_bits[] = $kraj;
    }
    if ($profile === 'far_batch') {
        $seo_desc_bits[] = 'společné výjezdy z Havířova';
    } elseif ($profile === 'near') {
        $seo_desc_bits[] = 'jsme z Havířova, blízko';
    } else {
        $seo_desc_bits[] = 'výroba z Havířova + montáž';
    }
    $seo_desc = implode(' — ', $seo_desc_bits) . '. Online zaměření, rodinná firma, 30 let. Bez fiktivní pobočky.';
    if (mb_strlen($seo_desc) > 160) {
        $seo_desc = sprintf(
            '%s %s %s — na míru z Havířova, montáž po domluvě. Online nabídka, 30 let zkušeností.',
            $product,
            $v,
            $loc
        );
    }

    return [
        'h1'             => $product . ' ' . $name,
        'intro'          => $intro,
        'bullets'        => $bullets,
        'service'        => $service,
        'local_note'     => $local_note,
        'why_title'      => $why_title,
        'why_local'      => $why_local,
        'faq'            => $faq,
        'seo_title'      => $seo_title,
        'seo_desc'       => $seo_desc,
        'price_from'     => $price,
        'sibling_cities' => $sibling_cities,
        'related_extra'  => $related,
        'distance'       => $distance,
        'areas'          => $areas,
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
