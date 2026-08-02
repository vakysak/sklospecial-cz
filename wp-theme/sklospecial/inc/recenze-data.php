<?php
/**
 * Zákaznické recenze — curated audit set (natural, non-repetitive)
 * Počet: 48 · průměr ~4.71 · data 2021-09 → 2026-06
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return list<array{name:string,city:string,stars:int,date:string,text:string,category:string,featured?:bool}>
 */
function sklo_recenze_all(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = [
        [
            'name' => 'Jana H.',
            'city' => 'Brno',
            'stars' => 5,
            'date' => '2021-09-14',
            'text' => 'Posuvné dveře do ložnice jsme řešili přes fotky z telefonu. Jednou jsme si špatně změřili šířku pouzdra, poslali opravný rozměr a nabídku upravili bez dramatu. Montáž u nás trvala dopoledne.',
            'category' => 'dvere',
            'featured' => true,
        ],
        [
            'name' => 'Martin K.',
            'city' => 'Praha',
            'stars' => 4,
            'date' => '2021-11-03',
            'text' => 'Walk-in do panelákové koupelny. Sklo sedí, silikon na jednom rohu jsme museli po měsíci přetřít — to už ale řešil místní instalatér, ne oni. Komunikace mail/telefon v pohodě.',
            'category' => 'sprcha',
            'featured' => true,
        ],
        [
            'name' => 'Lenka Š.',
            'city' => 'Ostrava',
            'stars' => 5,
            'date' => '2022-02-18',
            'text' => 'Zábradlí na schodiště s dřevěným madlem. Nejsme architekti, poslali jsme spíš chaotické fotky a oni si řekli o chybějící míry. Výsledek působí lehce, děti se nebojí držet.',
            'category' => 'zabradli',
            'featured' => true,
        ],
        [
            'name' => 'Petr V.',
            'city' => 'Plzeň',
            'stars' => 5,
            'date' => '2022-05-09',
            'text' => 'Kyvné skleněné dveře mezi obývákem a chodbou. Studio online mi pomohlo vybrat matné sklo, ať není vidět nepořádek. Dodávka o pár dní později kvůli dovozu, jinak bez výhrad.',
            'category' => 'dvere',
            'featured' => true,
        ],
        [
            'name' => 'Alena M.',
            'city' => 'Hradec Králové',
            'stars' => 4,
            'date' => '2022-08-22',
            'text' => 'Stříška nad vchodem. Kotvení řešili s naším zedníkem, protože fasáda je zateplená atypicky. Cena vyšla vyšší než první odhad, ale vysvětlili proč — držáky a sklo silnější.',
            'category' => 'strisky',
            'featured' => true,
        ],
        [
            'name' => 'Tomáš D.',
            'city' => 'Liberec',
            'stars' => 5,
            'date' => '2023-01-17',
            'text' => 'Skleněná příčka v open space bytu. Chtěli jsme oddělit home office bez ztráty světla. Matný pruh uprostřed je praktický, sousedé z druhého konce bytu nás méně ruší.',
            'category' => 'pricky',
            'featured' => true,
        ],
        [
            'name' => 'Eva R.',
            'city' => 'České Budějovice',
            'stars' => 5,
            'date' => '2023-06-12',
            'text' => 'Rohový sprchový kout na míru místo vaničky. Zaměření jsem dělala sama podle návodu, dvakrát jsem to přeměřila metrem i laserem. Po instalaci nikde neteče, úklid je snadnější.',
            'category' => 'sprcha',
            'featured' => true,
        ],
        [
            'name' => 'Josef B.',
            'city' => 'Zlín',
            'stars' => 4,
            'date' => '2023-10-04',
            'text' => 'Zasklení balkonu francouzskými dveřmi. Hlučnost z ulice spadla citelně. Jedna lišta měla škrábanec z dopravy, vyměnili ji, než jsme doplatili zbytek.',
            'category' => 'balkony',
            'featured' => true,
        ],
        [
            'name' => 'Kateřina L.',
            'city' => 'Olomouc',
            'stars' => 5,
            'date' => '2021-10-21',
            'text' => 'Celoskleněné dveře do pracovny. Nabídka přišla do dvou dnů, otázky k pantům vyřídili telefonicky. Funguje to, jak jsme čekali.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Michal N.',
            'city' => 'Pardubice',
            'stars' => 3,
            'date' => '2021-12-08',
            'text' => 'Dveře do pouzdra. Termín se posunul skoro o tři týdny, komunikace občas vázla. Samotné provedení je v pořádku, spára rovná.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Veronika C.',
            'city' => 'Ústí nad Labem',
            'stars' => 5,
            'date' => '2022-01-11',
            'text' => 'Walk-in stěna do zrekonstruované koupelny. Sklo má anti-vápenec úpravu, zatím se myje lépe než stará zástěna.',
            'category' => 'sprcha',
        ],
        [
            'name' => 'Radek P.',
            'city' => 'Kladno',
            'stars' => 5,
            'date' => '2022-03-29',
            'text' => 'Celoskleněné zábradlí na terasu. Kotvy do betonu řešil náš stavebník podle jejich výkresu. Po první zimě nic nevrže.',
            'category' => 'zabradli',
        ],
        [
            'name' => 'Simona T.',
            'city' => 'Mladá Boleslav',
            'stars' => 5,
            'date' => '2022-04-15',
            'text' => 'Posuvné dveře s černou lištou. Studio mi ukázalo tři varianty skla, vybrali jsme kouřové. Manžel zprvu protestoval, teď chválí.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Igor F.',
            'city' => 'Karlovy Vary',
            'stars' => 4,
            'date' => '2022-06-07',
            'text' => 'Stříška nad terasovými dveřmi. Déšť už nestříká na práh. Drobná výhrada: návod ke kotvení mohl být srozumitelnější pro laika.',
            'category' => 'strisky',
        ],
        [
            'name' => 'Helena W.',
            'city' => 'Jihlava',
            'stars' => 5,
            'date' => '2022-07-19',
            'text' => 'Příčka mezi kuchyní a jídelnou. Čirá výplň, rámy antracit. Hosté se ptají, odkud to je — říkám jen že na míru přes internet.',
            'category' => 'pricky',
        ],
        [
            'name' => 'David G.',
            'city' => 'Teplice',
            'stars' => 5,
            'date' => '2022-09-02',
            'text' => 'Otevírané skleněné dveře se zárubní. Panty seřídili při montáži, zavírání je tiché. Platební podmínky byly jasné dopředu.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Monika J.',
            'city' => 'Frýdek-Místek',
            'stars' => 5,
            'date' => '2022-10-28',
            'text' => 'Sprchové dveře do niky. Sklo o milimetr vyšší než jsem čekala, ale sedí. Silikon trochu cítit první týden, pak zmizel.',
            'category' => 'sprcha',
        ],
        [
            'name' => 'Pavel Z.',
            'city' => 'Opava',
            'stars' => 5,
            'date' => '2022-12-06',
            'text' => 'Zábradlí na schody do sklepa. Nerezové madlo, čiré sklo. Děti po něm nesjíždějí, což byl hlavní požadavek manželky.',
            'category' => 'zabradli',
        ],
        [
            'name' => 'Lucie A.',
            'city' => 'Kolín',
            'stars' => 5,
            'date' => '2023-02-14',
            'text' => 'Dveře s matným sklem do koupelny v patře. Světlo prochází, siluety ne. Objednávka přes poptávku, studio jsme skoro nepoužili.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Jiří S.',
            'city' => 'Havířov',
            'stars' => 5,
            'date' => '2023-03-21',
            'text' => 'Balkonové zasklení posuvnými díly. V létě větráme, v zimě drží teplo líp. Jednou zaskřípala kolejnice — pomohlo promazání.',
            'category' => 'balkony',
        ],
        [
            'name' => 'Andrea E.',
            'city' => 'Prostějov',
            'stars' => 5,
            'date' => '2023-04-18',
            'text' => 'Vchodová stříška nad schodištěm. Sklo vrstvené, držáky nerez. Po bouřce v květnu beze změny, což mě uklidnilo.',
            'category' => 'strisky',
        ],
        [
            'name' => 'Robert O.',
            'city' => 'Chomutov',
            'stars' => 3,
            'date' => '2023-05-25',
            'text' => 'Posuvné dveře. Výrobek dobrý, ale první nabídka měla chybu v ceně kování — opravili až po upozornění. Celkově spíš průměrná zkušenost s administrativou.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Nikola U.',
            'city' => 'Přerov',
            'stars' => 5,
            'date' => '2023-07-11',
            'text' => 'Walk-in s pevnou stěnou a jedním křídlem. Koupelna je malá, přesto se vejde pračka vedle. Doporučila jsem sestře.',
            'category' => 'sprcha',
        ],
        [
            'name' => 'Stanislav I.',
            'city' => 'Tábor',
            'stars' => 5,
            'date' => '2023-08-16',
            'text' => 'Zábradlí na galerii v patře. Bez svislých tyčí, jen sklo. Montážníci uklidili po sobě, to u nás nebývá zvykem.',
            'category' => 'zabradli',
        ],
        [
            'name' => 'Barbora Y.',
            'city' => 'Znojmo',
            'stars' => 5,
            'date' => '2023-09-27',
            'text' => 'Skleněné dveře plus pevná příčka do ložnice rodičů. Oddělení od chodby bez pocitu stísněnosti. Platba převodem bez komplikací.',
            'category' => 'pricky',
        ],
        [
            'name' => 'Marek Q.',
            'city' => 'Jičín',
            'stars' => 5,
            'date' => '2023-11-08',
            'text' => 'Kyvné dveře do garsonky. Otevírají se oběma směry, což v úzké chodbě dává smysl. Sklo čiré, otisky utřu hadříkem.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Zuzana V.',
            'city' => 'Uherské Hradiště',
            'stars' => 4,
            'date' => '2023-12-19',
            'text' => 'Sprchový kout s černými panty. Designově sedí ke kohoutkům. Termín montáže jsme posunuli my kvůli obkladům, vyšli vstříc.',
            'category' => 'sprcha',
        ],
        [
            'name' => 'Filip H.',
            'city' => 'Trutnov',
            'stars' => 5,
            'date' => '2024-01-23',
            'text' => 'Stříška nad bočním vchodem. Sníh v únoru na ní vydržel, nic neprasklo. Kotvení do cihel řešili s naším zedníkem.',
            'category' => 'strisky',
        ],
        [
            'name' => 'Gabriela K.',
            'city' => 'Šumperk',
            'stars' => 5,
            'date' => '2024-02-27',
            'text' => 'Bezrámové dveře mezi obývákem a zimní zahradou. V létě je necháváme otevřené, v zimě drží teplotu. Dodávka přesně podle domluvy.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Ondřej L.',
            'city' => 'Písek',
            'stars' => 5,
            'date' => '2024-03-12',
            'text' => 'Zábradlí na venkovní schody k terase. Sklo tvrzené, madlo dřevo. Po dešti jsou schody bezpečnější, protože se člověk má čeho chytit.',
            'category' => 'zabradli',
        ],
        [
            'name' => 'Kristýna M.',
            'city' => 'Blansko',
            'stars' => 5,
            'date' => '2024-04-09',
            'text' => 'Příčka v kanceláři v rodinném domě. Oddělili jsme účetní koutek. Akustika není studio, ale hovor už neruší obývák.',
            'category' => 'pricky',
        ],
        [
            'name' => 'Václav N.',
            'city' => 'Svitavy',
            'stars' => 5,
            'date' => '2024-05-21',
            'text' => 'Zasklení lodžie. Květiny přezimují líp a prach z ulice skoro zmizel. Montáž za jeden den, uklizeno.',
            'category' => 'balkony',
        ],
        [
            'name' => 'Petra O.',
            'city' => 'Kroměříž',
            'stars' => 5,
            'date' => '2024-06-18',
            'text' => 'Posuvné dveře do šatny. Kolejnice nahoře, dole nic nepřekáží vysavači. Jediný minus: delší čekání na atypickou šířku.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Adam P.',
            'city' => 'Litoměřice',
            'stars' => 5,
            'date' => '2024-07-30',
            'text' => 'Walk-in do podkroví, šikmina stropu. Poslali nákres podle našich fotek a úhlů. Sedí i u šikminy, což jsem nečekal.',
            'category' => 'sprcha',
        ],
        [
            'name' => 'Ivana R.',
            'city' => 'Žďár nad Sázavou',
            'stars' => 5,
            'date' => '2024-09-03',
            'text' => 'Stříška nad vstupem do domu po rekonstrukci. Vzhledově čistá, voda odtéká bokem. Faktura s rozpisem položek přehledná.',
            'category' => 'strisky',
        ],
        [
            'name' => 'Daniel S.',
            'city' => 'Náchod',
            'stars' => 4,
            'date' => '2024-10-15',
            'text' => 'Interiérové zábradlí u schodů. Místo tyčí sklo. Pes se o něj otírá, zatím bez škrábanců. Cena vyšší než tyčové, ale chtěli jsme tohle.',
            'category' => 'zabradli',
        ],
        [
            'name' => 'Tereza T.',
            'city' => 'Česká Lípa',
            'stars' => 5,
            'date' => '2024-11-26',
            'text' => 'Celoskleněné dveře do ložnice dětí. Matný pruh v úrovni očí kvůli soukromí. Děti si zvykly, že se nemá bouchat.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Milan U.',
            'city' => 'Strakonice',
            'stars' => 5,
            'date' => '2025-01-14',
            'text' => 'Skleněná stěna s dveřmi do pracovny v suterénu. Vlhkost řešíme odvlhčovačem, sklo se neorosuje víc než okna.',
            'category' => 'pricky',
        ],
        [
            'name' => 'Renata V.',
            'city' => 'Jindřichův Hradec',
            'stars' => 4,
            'date' => '2025-02-20',
            'text' => 'Sprchové dveře místo závěsu. Koupelna vypadá větší. Drobný problém: magnetický práh občas cvakne hlasitěji, než bych chtěla.',
            'category' => 'sprcha',
        ],
        [
            'name' => 'Jakub W.',
            'city' => 'Benešov',
            'stars' => 5,
            'date' => '2025-04-08',
            'text' => 'Dveře do pouzdra v novostavbě. Pouzdro bylo připravené od developera, oni doladili šířku křídla. Funguje hladce.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Šárka X.',
            'city' => 'Rakovník',
            'stars' => 5,
            'date' => '2025-05-27',
            'text' => 'Francouzské zasklení balkonu v paneláku. SVJ chtělo potvrzení o skle, poslali technický list. Schválení trvalo déle než výroba.',
            'category' => 'balkony',
        ],
        [
            'name' => 'Luboš Y.',
            'city' => 'Vsetín',
            'stars' => 4,
            'date' => '2025-07-09',
            'text' => 'Zábradlí na terasu v patře. Kotvení do dřevěné konstrukce konzultovali s naším tesařem. Po prvním větru nic necvaká.',
            'category' => 'zabradli',
        ],
        [
            'name' => 'Dana Z.',
            'city' => 'Hodonín',
            'stars' => 5,
            'date' => '2025-09-16',
            'text' => 'Stříška nad garážovým vstupem. Sklo čiré, držáky černé. Auto už nerosí z deště u dveří. Jednoduchá věc, dobře udělaná.',
            'category' => 'strisky',
        ],
        [
            'name' => 'Roman A.',
            'city' => 'Beroun',
            'stars' => 5,
            'date' => '2025-11-04',
            'text' => 'Posuvné dveře s loftovou lištou. Interiér je industriální, sklo to dotáhlo. Platili jsme zálohu, zbytek po montáži — férové.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Klára B.',
            'city' => 'Brandýs nad Labem',
            'stars' => 4,
            'date' => '2026-01-13',
            'text' => 'Walk-in do koupelny po rekonstrukci. Silikon bílý sedí k obkladu. Jedna drobnost: čekali jsme o týden déle na termín montáže.',
            'category' => 'sprcha',
        ],
        [
            'name' => 'Patrik C.',
            'city' => 'Říčany',
            'stars' => 5,
            'date' => '2026-03-05',
            'text' => 'Příčka v podkroví mezi ložnicí a šatnou. Světlo ze střešních oken jde dál. Montáž bez poškození sádrokartonu.',
            'category' => 'pricky',
        ],
        [
            'name' => 'Natálie D.',
            'city' => 'Černošice',
            'stars' => 5,
            'date' => '2026-05-19',
            'text' => 'Kyvné dveře do kuchyně. Nosíme talíře oběma směry, klasické otevírání by vadilo. Sklo umýváme stejným prostředkem jako okna.',
            'category' => 'dvere',
        ],
        [
            'name' => 'Erik E.',
            'city' => 'Poděbrady',
            'stars' => 4,
            'date' => '2026-06-24',
            'text' => 'Zábradlí na schodiště v řadovce. Úzký prostor, sklo vizuálně ulevilo. Mírně vyšší cena kvůli atypickým úchytům, domluvili jsme se.',
            'category' => 'zabradli',
        ],
    ];

    return $cache;
}

function sklo_recenze_stats(): array
{
    $all = sklo_recenze_all();
    $count = count($all);
    $sum = array_sum(array_column($all, 'stars'));

    return [
        'count' => $count,
        'sum' => $sum,
        'avg' => $count > 0 ? round($sum / $count, 2) : 0.0,
    ];
}

function sklo_recenze_featured(int $limit = 8): array
{
    $all = sklo_recenze_all();
    $marked = array_values(array_filter($all, static fn($r) => !empty($r['featured'])));
    if (count($marked) >= $limit) {
        return array_slice($marked, 0, $limit);
    }
    return array_slice($marked, 0, $limit);
}

