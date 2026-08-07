<?php
/**
 * Product catalog helpers (data from katalog-produkty-data.php + produkty.json).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, array{min_price: ?int, count: int, section_mins: array<string, int>, products: list<array<string, mixed>>}>
 */
function sklo_produkty_all(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $path = get_template_directory() . '/inc/katalog-produkty-data.php';
    if (!is_readable($path)) {
        $data = [];
        return $data;
    }
    /** @var array<string, mixed> $loaded */
    $loaded = require $path;
    $data = is_array($loaded) ? $loaded : [];
    return $data;
}

/**
 * Full product map keyed by code (from assets/data/produkty.json).
 *
 * @return array<string, array<string, mixed>>
 */
function sklo_produkty_json_map(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $path = get_template_directory() . '/assets/data/produkty.json';
    if (!is_readable($path)) {
        $map = [];
        return $map;
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        $map = [];
        return $map;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $map = [];
        return $map;
    }
    $products = $decoded['products'] ?? [];
    $map = is_array($products) ? $products : [];
    return $map;
}

/**
 * @return array{min_price: ?int, count: int, section_mins: array<string, int>, products: list<array<string, mixed>>}|null
 */
function sklo_produkty_for_slug(string $slug): ?array
{
    if ($slug === 'sklenene-steny' || $slug === 'sklenene-pricky') {
        $slug = 'pricky-a-zabudovani';
    }
    $all = sklo_produkty_all();
    return isset($all[$slug]) && is_array($all[$slug]) ? $all[$slug] : null;
}

/**
 * Find product by SklS code (JSON first, then PHP index).
 *
 * @return array<string, mixed>|null
 */
function sklo_produkt_by_code(string $code): ?array
{
    $code = trim($code);
    if ($code === '' || !preg_match('/^SklS-\d{4}$/', $code)) {
        return null;
    }
    $json = sklo_produkty_json_map();
    if (isset($json[$code]) && is_array($json[$code])) {
        return $json[$code];
    }
    foreach (sklo_produkty_all() as $bundle) {
        if (!is_array($bundle) || empty($bundle['products']) || !is_array($bundle['products'])) {
            continue;
        }
        foreach ($bundle['products'] as $p) {
            if (!is_array($p)) {
                continue;
            }
            if ((string) ($p['code'] ?? '') === $code) {
                return $p;
            }
        }
    }
    return null;
}

/**
 * Formátuje absolutní cenu (ne „od“).
 */
function sklo_format_cena(int $kc): string
{
    return number_format($kc, 0, ',', "\u{00a0}") . ' Kč';
}

/**
 * Doplatek display: „+ X Kč“ or empty for zero.
 */
function sklo_format_doplatek(int $kc): string
{
    if ($kc <= 0) {
        return '';
    }
    return '+ ' . sklo_format_cena($kc);
}

/**
 * Prefer smaller Quba cache thumbs for grid cards (_700_700 → _400_400).
 */
function sklo_quba_thumb(string $url, string $size = '400_400'): string
{
    if (function_exists('sklo_public_asset_url')) {
        $url = sklo_public_asset_url($url);
    }
    if ($url === '' || !str_contains($url, 'qubaglass.pl')) {
        return $url;
    }
    $out = preg_replace('/_(\d{3,4})_(\d{3,4})\b/', '_' . $size, $url, 1);
    return is_string($out) && $out !== '' ? $out : $url;
}


/**
 * Detail URL on current category page.
 */
function sklo_produkt_detail_url(string $code, ?string $base = null): string
{
    $base = $base ?? (string) get_permalink();
    $sep = str_contains($base, '?') ? '&' : '?';
    return $base . $sep . 'kod=' . rawurlencode($code);
}

/**
 * Sanitize public Czech copy (no Polish leftovers).
 */
function sklo_public_product_text(string $text): string
{
    $replacements = [
        // Longest phrases first

        // Availability (Quba leftovers)
        '/\bDost[eę]pny\s+na\s+zam[oó]wienie\b/iu' => 'Na objednávku',
        '/\bDost[eę]pny\s+na\s+zamowienie\b/iu' => 'Na objednávku',
        '/\bdost[eę]pnymi\b/iu' => 'dostupnými',
        '/\b[SŚ]rednia\s+ilo[sś][cć]\b/iu' => 'Omezené množství',
        '/\bSrednia\s+ilosc\b/iu' => 'Omezené množství',
        '/\bNa\s+wyczerpaniu\b/iu' => 'Dochází',

        // LOFT-ART marketing leftovers (hybrid PL/CS)
        '/\bpředstavujeme\s+nowe\s+skleněné\s+dveře\s+na\s+systemie\s+posuvném\b/iu' => 'představujeme nové skleněné dveře na posuvném systému',
        '/\bPo\s+czarnym\s+systemie\s+LOFT-ART\s+przyszla\s+kolej\s+na\b/iu' => 'Po černém systému LOFT-ART přišel na řadu',
        '/\bLOFT-ART\s+BIALYM\b/iu' => 'LOFT-ART bílý',
        '/\bjde\s+o\s+systém\s+w\s+minimalistycznym\s+stylu\s+industrialnym,\s+dodávající\s+idealnej\s+eleganckiej\s+lekkosci\s+I\s+poczucia\s+przestrzeni\s+w\s+kazdym\s+wnetrzu\b/iu' => 'jde o systém v minimalistickém industriálním stylu, který dodává lehkost a pocit prostoru v každém interiéru',
        '/\bsystém\s+ten\s+pozwala\s+dzielic\s+przestrzen\s+bez\s+utraty\s+doswietlenia\b/iu' => 'systém umožňuje dělit prostor bez ztráty světla',
        '/\bDveře\s+oferujemy\s+w\s+pieciu\s+rozmiarach\b/iu' => 'Dveře nabízíme v pěti rozměrech',
        '/\bPo\s+skreceniu\s+rámu\s+hliníkové\s+wraz\s+ze\s+szklem\s+uzyskamy\s+rozměry\s+calosciowe\b/iu' => 'Po sešroubování hliníkového rámu se sklem získáte celkové rozměry',
        '/\bdélky\s+systemow\b/iu' => 'délky systémů',
        '/\bdélka\s+systemu\b/iu' => 'délka systému',

        // Aluminum frame leftovers
        '/\bOferujemy\s+skleněné\s+dveře\b/iu' => 'Nabízíme skleněné dveře',
        '/\boferujemy\s+skleněné\s+dveře\b/iu' => 'nabízíme skleněné dveře',
        '/\bideálně\s+se\s+hodí\s+wiec\s+do\s+pomieszczen\s+o\s+duzej\s+wilgotnosci\b/iu' => 'ideálně se hodí do místností s vyšší vlhkostí',
        '/\bDuzym\s+plusem\b/iu' => 'Velkým plusem',
        '/\bduzym\s+plusem\b/iu' => 'velkým plusem',
        '/\bjest\s+rowniez\s+to,\s+ze\s+nadaje\s+sie\s+do\s+bardzo\s+duzych\b/iu' => 'je také to, že se hodí i na velmi velké',
        '/\bJest\s+wiec\s+bardziej\s+wytrzymala\s+na\s+duze\s+obciazenia\b/iu' => 'Je tedy odolnější vůči velkému zatížení',
        '/\bwytrzymuje\s+ciezar\s+szerokich\s+i\s+ciezkich\s+tafli\s+skla\b/iu' => 'unese váhu širokých a těžkých tabulí skla',
        '/\bDzieki\s+zastosowanej\s+uszczelce\b/iu' => 'Díky použitému těsnění',
        '/\btlumienia\s+dzwiekow\b/iu' => 'tlumení zvuků',
        '/\bW\s+sklad\s+zárubně\s+hliníkové\s+wchodzi\b/iu' => 'Součástí hliníkové zárubně je',
        '/\bpionowy\b/iu' => 'svislý',
        '/\bpoziomy\b/iu' => 'vodorovný',
        '/\buszczelka\s+pionowa\b/iu' => 'svislé těsnění',
        '/\buszczelka\s+pozioma\b/iu' => 'vodorovné těsnění',
        '/\blacznik\s+rohový\b/iu' => 'rohová spojka',
        '/\bnaroznik\s+stabilizujacy\b/iu' => 'stabilizační roh',
        '/\bblaszka\s+pod\b/iu' => 'destička pod',
        '/\bw\s+provedení\s+srebrnej\s+anody\b/iu' => 'v provedení stříbrné anody',
        '/\bhartowna\b/iu' => 'kalené',
        '/\budávají\s+panstwo\s+rozměr\s+swojej\b/iu' => 'uvedete rozměr svého',
        '/\bne\s+moga\s+przekraczac\b/iu' => 'nesmí přesáhnout',
        '/\bo\s+wiekszych\s+wymiarach\b/iu' => 've větších rozměrech',
        '/\bwycenimy\s+taka\s+varianty\b/iu' => 'oceníme takovou variantu',
        '/\bvarianty\s+DODATKOWE\b/iu' => 'doplňkové varianty',
        '/\bwycena\s+mailowa\b/iu' => 'cena na e-mail',
        '/\bz\s+funkcja\s+samozamykacza\b/iu' => 's funkcí samozavírače',
        '/\bhydrauliczne\b/iu' => 'hydraulické',
        '/\belektrozaczepem\b/iu' => 'elektrickým dorazem',

        // Common single-word leftovers
        '/\bnowe\b/iu' => 'nové',
        '/\bsystemie\b/iu' => 'systému',
        '/\bminimalistycznym\b/iu' => 'minimalistickém',
        '/\bindustrialnym\b/iu' => 'industriálním',
        '/\beleganckiej\b/iu' => 'elegantní',
        '/\blekkosci\b/iu' => 'lehkosti',
        '/\bpoczucia\b/iu' => 'pocitu',
        '/\bprzestrzeni\b/iu' => 'prostoru',
        '/\bkazdym\b/iu' => 'každém',
        '/\bwnetrzu\b/iu' => 'interiéru',
        '/\bczarnym\b/iu' => 'černém',
        '/\bprzyszla\b/iu' => 'přišla',
        '/\bOferujemy\b/iu' => 'Nabízíme',
        '/\boferujemy\b/iu' => 'nabízíme',
        '/\bprzedstawiamy\b/iu' => 'představujeme',
        '/\bprzedstawiaja\b/iu' => 'představují',
        '/\bpomieszczen\b/iu' => 'místností',
        '/\bwilgotnosci\b/iu' => 'vlhkosti',
        '/\bwiec\b/iu' => 'tedy',
        '/\browniez\b/iu' => 'také',
        '/\bbardzo\b/iu' => 'velmi',
        '/\bduzych\b/iu' => 'velkých',
        '/\bduzej\b/iu' => 'velké',
        '/\bduzym\b/iu' => 'velkým',
        '/\bduze\b/iu' => 'velké',
        '/\bwytrzymala\b/iu' => 'odolná',
        '/\bobciazenia\b/iu' => 'zatížení',
        '/\bciezar\b/iu' => 'váhu',
        '/\bszerokich\b/iu' => 'širokých',
        '/\bciezkich\b/iu' => 'těžkých',
        '/\btafli\b/iu' => 'tabulí',
        '/\buszczelce\b/iu' => 'těsnění',
        '/\buszczelka\b/iu' => 'těsnění',
        '/\bdzwiekow\b/iu' => 'zvuků',
        '/\bwchodzi\b/iu' => 'patří',
        '/\bsrebrnej\b/iu' => 'stříbrné',
        '/\bjest\b/iu' => 'je',

        // Aggressive PL→CS (product copy leftovers)
        '/\bdo\s+wymiar[oó]w\b/iu' => 'na míru',
        '/\bdo\s+wymiaru\b/iu' => 'na míru',
        '/\bwymiar[oó]w\b/iu' => 'rozměrů',
        '/\bwymiaru\b/iu' => 'rozměru',
        '/\bwymiar\b/iu' => 'rozměr',
        '/\bna\s+zam[oó]wienie\b/iu' => 'na objednávku',
        '/\bna\s+zamowienie\b/iu' => 'na objednávku',
        '/\bzam[oó]wienia\b/iu' => 'objednávky',
        '/\bzamowienia\b/iu' => 'objednávky',
        '/\bzam[oó]wienie\b/iu' => 'objednávka',
        '/\bzamowienie\b/iu' => 'objednávka',
        '/\bodp[lł]ywowy\b/iu' => 'odtokový',
        '/\bodplywowy\b/iu' => 'odtokový',
        '/\bbezbarwnego\b/iu' => 'čirého',
        '/\bbezbarwne\b/iu' => 'čiré',
        '/\bwahad[lł]owe\b/iu' => 'kyvné',
        '/\bwahadlowe\b/iu' => 'kyvné',
        '/\bInstrukcja\b/iu' => 'Návod',
        '/\binstrukcja\b/iu' => 'návod',
        '/\bschemat\b/iu' => 'schéma',
        // Matte-side N/A choice (Polish + hybrid leftovers)
        '/\bBrak\s*-?\s*gdy\s+(?:dveře\s+)?(?:čiré|bezbarwne|bezbarvé)\s+(?:nebo|lub)\s+laminat\b/iu' => 'chybí, pokud jsou dveře bezbarvé nebo laminátové',
        '/\bBrak\s*-?\s*gdy\s+drzwi\s+bezbarwne\s+lub\s+laminat\b/iu' => 'chybí, pokud jsou dveře bezbarvé nebo laminátové',
        '/\bVSG folie drží\b/iu' => 'VSG fólie drží',
        '/\bfolie drží\b/iu' => 'fólie drží',
        '/\. prosím seznam\b/iu' => '. Prosím seznam',
        '/\bprosimy o seznámení sie z schematem dotyczacym strony otevírání dveře\b/iu' => 'prosím seznam se se schématem strany otevírání dveří',
        '/\bprosimy o seznámení sie z schematem dotyczacym\b/iu' => 'prosím seznam se se schématem',
        '/\bprosimy o\b/iu' => 'prosím',
        '/\bschematem dotyczacym\b/iu' => 'schématem',
        '/\bdotyczacym\b/iu' => 'týkajícím se',
        '/\bwykonana ze skla\b/iu' => 'vyrobená ze skla',
        '/\bwykonana ze\b/iu' => 'vyrobená ze',
        '/\bkrawedzie\b/iu' => 'hrany',
        '/\bgladkie\b/iu' => 'hladké',
        '/\bidealnie sprawdzajace sie\b/iu' => 'ideálně se hodící',
        '/\bsprawdzajace sie\b/iu' => 'hodící se',
        '/\bdoskonalej jakosci\b/iu' => 'vynikající kvality',
        '/\bw nowoczesnych wnetrzach\b/iu' => 'v moderních interiérech',
        '/\bpelniace funkcje\b/iu' => 'plnící funkci',
        '/\bw przypadku prasknutí folia\b/iu' => 'při prasknutí fólie',
        '/\bfolia ktora polaczyla dwie skla trzyma nadal je ze soba\b/iu' => 'fólie, která spojila dvě skla, je stále drží pohromadě',
        '/\bktora polaczyla\b/iu' => 'která spojila',
        '/\btrzyma nadal\b/iu' => 'stále drží',
        '/\bze soba\b/iu' => 'pohromadě',
        '/\bto sklo bezpieczne\b/iu' => 'jde o bezpečnostní sklo',
        '/\bfolia\b/iu' => 'fólie',
        '/\bwkladka patentowa wc lub klucz\/klucz\b/iu' => 'vložka WC nebo klíč/klíč (dle modelu)',
        '/\bwkładka patentowa wc lub klucz\/klucz\b/iu' => 'vložka WC nebo klíč/klíč (dle modelu)',
        '/\bvložka patentowa wc nebo klucz\/klucz\b/iu' => 'vložka WC nebo klíč/klíč (dle modelu)',
        '/\bvložka patentowa wc lub klucz\/klucz\b/iu' => 'vložka WC nebo klíč/klíč (dle modelu)',
        '/\bwkladka patentowa wc nebo klucz\/klucz\b/iu' => 'vložka WC nebo klíč/klíč (dle modelu)',
        '/\bwkladka patentowa wc\b/iu' => 'vložka WC',
        '/\bwkładka patentowa wc\b/iu' => 'vložka WC',
        '/\bvložka patentowa wc\b/iu' => 'vložka WC',
        '/\bpatentowa wc\b/iu' => 'WC',
        '/\bpatentowa\b/iu' => '',
        '/\bklucz\/klucz\b/iu' => 'klíč/klíč',
        '/\bklucz\b/iu' => 'klíč',
        '/\bSztywna konstrukcja\b/iu' => 'Tuhá konstrukce',
        '/\bsztywna konstrukcja\b/iu' => 'tuhá konstrukce',
        '/\bKonstrukcja zárubně\b/iu' => 'Konstrukce zárubně',
        '/\bkonstrukcja zárubně\b/iu' => 'konstrukce zárubně',
        '/\bKonstrukcja\b/iu' => 'Konstrukce',
        '/\bkonstrukcja\b/iu' => 'konstrukce',
        '/\bwykonana jest z wysokogatunkowej p[lł]yty MDF\b/iu' => 'je z kvalitní MDF desky',
        '/\bwysokogatunkowej\b/iu' => 'kvalitní',
        '/\bp[lł]yty MDF\b/iu' => 'MDF desky',
        '/\boklejona jest\b/iu' => 'opatřená',
        '/\boklejona\b/iu' => 'opatřená',
        '/\bekologiczna,?\b/iu' => 'ekologickou',
        '/\bdrewnopodobna folia dekoracyjna\b/iu' => 'dřevodekorativní fólií',
        '/\bfolia dekoracyjna\b/iu' => 'dekorativní fólie',
        '/\bposiada\b/iu' => 'má',
        '/\bne ma opasek maskujacych\b/iu' => 'bez maskovacích lišt',
        '/\bnie ma opasek maskuj[aą]cych\b/iu' => 'bez maskovacích lišt',
        '/\bopasek maskuj[aą]cych\b/iu' => 'maskovacích lišt',
        '/\bPonizej představujeme schematy futryny\b/iu' => 'Níže jsou schémata zárubně',
        '/\bPoniżej przedstawiamy schematy futryny\b/iu' => 'Níže jsou schémata zárubně',
        '/\bponizej przedstawiamy schematy futryny\b/iu' => 'níže jsou schémata zárubně',
        '/\bponi[zż]ej\b/iu' => 'níže',
        '/\bschematy futryny\b/iu' => 'schémata zárubně',
        '/\bschematy\b/iu' => 'schémata',
        '/\bfutryny\b/iu' => 'zárubně',
        '/\bprosz[eę] o zapoznanie si[eę] ze schematem stronno[sś]ci\b/iu' => 'prosím seznam se se schématem strany otevírání dveří',
        '/\bzapoznanie si[eę] ze schematem stronno[sś]ci\b/iu' => 'seznamte se se schématem strany otevírání dveří',
        '/\bzapoznanie\b/iu' => 'seznámení',
        '/\bstronno[sś]ci\b/iu' => 'strany otevírání',
        '/\bstronność\b/iu' => 'strany otevírání',
        '/\bstronnosc\b/iu' => 'strany otevírání',
        '/\bswiatla przejscia\b/iu' => 'světlá šířka průchodu',
        '/\bświatła przejścia\b/iu' => 'světlá šířka průchodu',
        '/\buzyskanie efektu\b/iu' => 'získání efektu',
        '/\bdemonta[zż]u szyny\b/iu' => 'demontáže kolejnice',
        '/\bSprawd[zź] produkty powi[aą]zane do produktu\b/iu' => 'Související produkty',
        '/\bSprawd[zź] powi[aą]zane produkty\b/iu' => 'Související produkty',
        '/\bprodukty powi[aą]zane do produktu\b/iu' => 'související produkty',
        '/\bpowi[aą]zane produkty\b/iu' => 'související produkty',
        '/\bpowi[aą]zane\b/iu' => 'související',
        '/\bsprawd[zź]\b/iu' => 'zkontrolujte',
        '/\bsatin szlifowana\b/iu' => 'satin broušený',
        '/\bsatin szlifowane\b/iu' => 'satin broušené',
        '/\bszlifowana\b/iu' => 'broušená',
        '/\bmateri[aá][lł]:\s*aluminium\b/iu' => 'materiál: hliník',
        '/\bz aluminium\b/iu' => 'z hliníku',
        '/\baluminium\b/iu' => 'hliník',
        '/\bpevnou šířka\b/iu' => 'pevnou šířku',
        '/\bdo wybranych modeli\b/iu' => 'do vybraných modelů',
        '/\bdo vybraných modeli\b/iu' => 'do vybraných modelů',
        '/\bW lewo\s*\(\s*patrzac od str(?:ony|\.)\s*systemu\s*\)/iu' => 'Doleva (při pohledu od strany systému)',
        '/\bW prawo\s*\(\s*patrzac od str(?:ony|\.)\s*systém?\s*\)/iu' => 'Doprava (při pohledu od strany systému)',
        '/\bOd przodu\s*\(\s*patrzac od str(?:ony|\.)\s*systemu\s*\)/iu' => 'Zepředu (při pohledu od strany systému)',
        '/\bOd tylu\s*\(\s*patrzac od str(?:ony|\.)\s*systemu\s*\)/iu' => 'Zezadu (při pohledu od strany systému)',
        '/\bGrafika od przodu,\s*od tylu\b/iu' => 'Grafika zepředu, zezadu',
        '/\bGrafika od tylu,\s*od przodu\b/iu' => 'Grafika zezadu, zepředu',
        '/\bZrcadlo od przodu,\s*od tylu\b/iu' => 'Zrcadlo zepředu, zezadu',
        '/\bZrcadlo od tylu,\s*od przodu\b/iu' => 'Zrcadlo zezadu, zepředu',
        '/\bSzorstka od strony systemu\b/iu' => 'Drsná od strany systému',
        '/\bG[lł]adka od strony systemu\b/iu' => 'Hladká od strany systému',
        '/\bMat od przodu\b/iu' => 'Mat zepředu',
        '/\bMat od tylu\b/iu' => 'Mat zezadu',
        '/\bOd przodu\b/iu' => 'Zepředu',
        '/\bOd tylu\b/iu' => 'Zezadu',
        '/\bW lewo\b/iu' => 'Doleva',
        '/\bW prawo\b/iu' => 'Doprava',
        '/\bOpcja dodatkowa\.?/iu' => 'Doplňková možnost',
        '/\bSpowolnione zamykanie oraz otwieranie drzwi?\b/iu' => 'Zpomalené zavírání i otevírání dveří',
        '/\bdo ustalonej\b/iu' => 'do stanovené',
        '/\bPozycji spoczynkowej\.?/iu' => 'klidové polohy',
        '/\bjuz od\b/iu' => 'již od',
        '/\bdo dveře juz\b/iu' => 'u dveří již',
        '/\bwozki,\s*hamulce,\s*prowadnik dolny\b/iu' => 'vozíky, dorazy, spodní vodítko',
        '/\bhamulce do wozkow\b/iu' => 'dorazy / brzdy na vozíky',
        '/\búchyty do prowadnicy\b/iu' => 'úchyty na vodicí lištu',
        '/\bprowadnik dolny\b/iu' => 'spodní vodítko',
        '/\bBlaszka zaczepowa kolor satin\b/iu' => 'Protiplech barva satin',
        '/\bBlaszka zaczepowa\b/iu' => 'Protiplech',
        '/\bz zawiasami hydraulicznymi z regulacja zamykania\b/iu' => 's hydraulickými závěsy s regulací zavírání',
        '/\bzawiasami hydraulicznymi\b/iu' => 'hydraulickými závěsy',
        '/\bregulacja zamykania\b/iu' => 'regulace zavírání',
        '/\bZarowno systém jak i úchyt sa w satinovém provedení\b/iu' => 'Jak systém, tak úchyt jsou v satinovém provedení',
        '/\bpozwalaja zsunac dveře do konca\b/iu' => 'umožňují zasunout dveře až na doraz',
        '/\bpozwalaja zsunac\b/iu' => 'umožňují zasunout',
        '/\bwahadłowe\b/iu' => 'kyvné',
        '/\bwahadlowe\b/iu' => 'kyvné',
        '/\bprzesuwne\b/iu' => 'posuvné',
        '/\bprzesuwny\b/iu' => 'posuvný',
        '/\botočné\b/iu' => 'kyvné',
        '/\bQuba\s*Glass\b/iu' => '',
        '/\bQubaglass\b/iu' => '',
        '/\bWybierz wariant produktu\b/iu' => 'Vyberte variantu produktu',
        '/\bWybierz\b/iu' => 'Vyberte',
        '/\bWymiar drzwi\b/iu' => 'Rozměr dveří',
        '/\bWymiary drzwi\b/iu' => 'Rozměry dveří',
        '/\bUchwyt do drzwi\b/iu' => 'Madlo / úchyt',
        '/\buchwyt do drzwi\b/iu' => 'Madlo / úchyt',
        '/\bUchwyt\b/iu' => 'Madlo',
        '/\bKierunek otwierania\b/iu' => 'Směr otevírání',
        '/\bKotwa montazowa\b/iu' => 'Montážní kotva',
        '/\bKotwa montażowa\b/iu' => 'Montážní kotva',
        '/\bkotwa\s+montážní\s+kotva\b/iu' => 'montážní kotva',
        '/\bSzerokosc wneki\b/iu' => 'Šířka výklenku',
        '/\bSzerokość wnęki\b/iu' => 'Šířka výklenku',
        '/\bWysokosc wneki\b/iu' => 'Výška výklenku',
        '/\bWysokość wnęki\b/iu' => 'Výška výklenku',
        '/\bRodzaj zamka\b/iu' => 'Typ zámku',
        '/\bRodzaj zawiasow\b/iu' => 'Typ závěsů',
        '/\bRodzaj zawiasów\b/iu' => 'Typ závěsů',
        '/\bRodzaj szkła\b/iu' => 'Typ skla',
        '/\bRodzaj szkla\b/iu' => 'Typ skla',
        '/\bKolor oku[cć]\b/iu' => 'Barva kování',
        '/\bKolor mocowa[nń]\b/iu' => 'Barva kování',
        '/\bbezbarwne\b/iu' => 'čiré',
        '/\bmatowe\b/iu' => 'matné',
        '/\bgrafitowe\b/iu' => 'grafitové',
        '/\bsatyna\b/iu' => 'satin',
        '/\bczarny mat\b/iu' => 'černý mat',
        '/\bczarny\b/iu' => 'černý',
        '/\bsamodomyk(?:acz)?\b/iu' => 'Samozavírač / tichý dojezd',
        '/\bsamozavírač\s*\/\s*tichý domyk\b/iu' => 'Samozavírač / tichý dojezd',
        '/\btichý domyk\b/iu' => 'tichý dojezd',
        '/\bmuszelka\b/iu' => 'mušle',
        '/\bmadlo antaba\b/iu' => 'madlo táhlo',
        '/\bantaba\b/iu' => 'táhlo',
        '/\boscieznica\b/iu' => 'zárubeň',
        '/\bościeżnica\b/iu' => 'zárubeň',
        '/\bfutryna\b/iu' => 'zárubeň',
        '/\bklamka\b/iu' => 'klika',
        '/\bmocowa[nń]\b/iu' => 'úchytů',
        '/\boku[cć]\b/iu' => 'kování',
        '/\bzawiasy\b/iu' => 'závěsy',
        '/\bzawias\b/iu' => 'závěs',
        '/\bobustronnie\b/iu' => 'oboustranně',
        '/\bwneki\b/iu' => 'výklenku',
        '/\bwnęki\b/iu' => 'výklenku',
        '/\bSystém przesuwny wariant\b/iu' => 'Varianta posuvného systému',
        '/\bstronność\b/iu' => 'strany otevírání',
        '/\bstronnosc\b/iu' => 'strany otevírání',
        '/\blewe\b/iu' => 'levé',
        '/\bprawe\b/iu' => 'pravé',
        '/\bdrzwi\b/iu' => 'dveře',
        '/\bszklane\b/iu' => 'skleněné',
        '/\bdaszek\b/iu' => 'stříška',
        '/\bzadaszenie\b/iu' => 'stříška',
        '/\bbalustrada\b/iu' => 'zábradlí',
        '/\bpowstajacego\b/iu' => 'vzniklého',
        '/\bpowstaje\b/iu' => 'vzniká',
        '/\bsklad zestawu\b/iu' => 'sada obsahuje',
        '/\bzestawu\b/iu' => 'sady',
        '/\bsystemie\b/iu' => 'systému',
        '/\bdwoch\b/iu' => 'dvou',
        '/\bzestaw\b/iu' => 'sada',
        '/\brezygnacji\b/iu' => 'zrušení',
        '/\bpodzialu\b/iu' => 'členění',
        '/\bkomplecie\b/iu' => 'sadě',
        '/tabule skla staje sie latwiejsza w utrzymaniu czystosci/iu' => 'tabule skla se snáze udržuje v čistotě',
        '/system trubkovy charakteryzuje sie cicha praca i komfortem w uzytkowaniu/iu' => 'trubkový systém se vyznačuje tichým chodem a pohodlným používáním',
        '/systém vyrobený z nerezové oceli szczotkowanej, dopracowany, niezawodny/iu' => 'systém z broušené nerezové oceli, propracovaný a spolehlivý',
        '/wszystkie regulacje lze przeprowadzic po zamontowaniu/iu' => 'veškeré seřízení lze provést po montáži',
        '/Nasze dveře z grafikami powstaja dzieki drukarce UV ktora nanosi vzor na jednej z szyb/iu' => 'Naše dveře s grafikou vznikají UV tiskem, který nanáší vzor na jednu ze skleněných tabulí',
        '/Potisk je(?:st)? wewnatrz szyb,?dzieki czemu ne lze go seškrábnout, a sklo pieknie blyszczy z obu stron/iu' => 'Potisk je uvnitř skla, takže jej nelze seškrábnout, a sklo se krásně leskne z obou stran',
        '/Kolor grafiki moze sie roznic o kilka tonow/iu' => 'Barva grafiky se může lišit o několik odstínů',
        '/zalezne od ustawien monitora a druku/iu' => 'v závislosti na nastavení monitoru a tisku',
        '/regulace nahoře \/ dole na wozku/iu' => 'nastavení nahoře / dole na vozíku',
        '/Odpornosc na korozje EN 1670/iu' => 'Odolnost proti korozi EN 1670',
        '/Dostupné komfortowe funkcje/iu' => 'Dostupné komfortní funkce',
        '/drugie skrzydlo otwiera i zamyka sie automatycznie, jesli pierwsze skrzydlo je w ruchu/iu' => 'druhé křídlo se automaticky otevírá a zavírá, pokud je první křídlo v pohybu',
        '/\bwycena mailowa\b/iu' => 'cena na vyžádání e-mailem',
        '/\bwycena\b/iu' => 'cena na vyžádání',
        '/\bprowadznik\b/iu' => 'vodítko',
        '/\bprowadnik\b/iu' => 'vodítko',
        '/Latwa i mozliwa regulace wysokosci/iu' => 'Snadné nastavení výšky',
        '/Systém przetestowany na ponad 200 000 cykli/iu' => 'Systém testovaný na více než 200 000 cyklů',
        '/Minimalistyczne wymiary elementow systemu/iu' => 'Minimalistické rozměry prvků systému',
        '/\bPanstwo\b/iu' => 'vy',
        '/\bszyb\b/iu' => 'skleněných tabulí',
        '/\bjakosci\b/iu' => 'kvality',
        '/\bnowoczesnych\b/iu' => 'moderních',
        '/\bsie\b/iu' => 'se',
        '/\bpiaskowanym\b/iu' => 'pískovaným',
        '/\bwykonywany\b/iu' => 'provedený',
        '/\bszczotkowanej\b/iu' => 'broušené',
        '/\bdopracowany\b/iu' => 'propracovaný',
        '/\bniezawodny\b/iu' => 'spolehlivý',
        '/\bprosimy podac\b/iu' => 'uveďte prosím',
        '/\botrzymuja Panstwo\b/iu' => 'obdržíte',
        // Strip leftover Polish-only diacritics (keep Czech)
        '/[ąęłńśźżćĄĘŁŃŚŹŻĆ]/u' => '',
    ];
    // Map stripped diacritics properly via transliteration-ish replacements instead of delete
    // (handled below after loop with dedicated map)

    foreach ($replacements as $pattern => $replacement) {
        if ($pattern === '/[ąęłńśźżćĄĘŁŃŚŹŻĆ]/u') {
            continue;
        }
        $text = preg_replace($pattern, $replacement, $text) ?? $text;
    }

    $plFold = [
        'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n',
        'ś' => 's', 'ź' => 'z', 'ż' => 'z',
        'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N',
        'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z',
    ];
    $text = strtr($text, $plFold);

    if (preg_match('/^nie\.?$/iu', trim($text))) {
        return 'Ne';
    }
    if (preg_match('/^tak\.?$/iu', trim($text))) {
        return 'Ano';
    }

    return trim(preg_replace('/\s{2,}/u', ' ', $text) ?? $text);
}

function sklo_render_produkt_detail(array $p, string $back_url = ''): void
{
    $name  = sklo_public_product_text((string) ($p['name'] ?? ''));
    $code  = (string) ($p['code'] ?? '');
    $price = (int) ($p['price'] ?? 0);
    $desc  = sklo_public_product_text((string) ($p['description'] ?? ''));
    $part  = (string) ($p['partNumber'] ?? '');
    $ship  = $p['shipping_days'] ?? null;
    $avail = sklo_public_product_text((string) ($p['availability'] ?? ''));

    $images = [];
    if (!empty($p['images']) && is_array($p['images'])) {
        foreach ($p['images'] as $img) {
            $img = (string) $img;
            if ($img !== '') {
                $images[] = function_exists('sklo_public_asset_url') ? sklo_public_asset_url($img) : $img;
            }
        }
    }
    if ($images === [] && !empty($p['image'])) {
        $img = (string) $p['image'];
        $images[] = function_exists('sklo_public_asset_url') ? sklo_public_asset_url($img) : $img;
    }

    $includes = [];
    if (!empty($p['includes']) && is_array($p['includes'])) {
        foreach ($p['includes'] as $item) {
            $t = sklo_public_product_text((string) $item);
            if ($t !== '') {
                $includes[] = $t;
            }
        }
    }

    $options = [];
    if (!empty($p['options']) && is_array($p['options'])) {
        foreach ($p['options'] as $g) {
            if (!is_array($g)) {
                continue;
            }
            $label = sklo_public_product_text((string) ($g['label'] ?? ''));
            $choices = [];
            if (!empty($g['choices']) && is_array($g['choices'])) {
                foreach ($g['choices'] as $c) {
                    if (!is_array($c)) {
                        continue;
                    }
                    $cname = sklo_public_product_text((string) ($c['name'] ?? ''));
                    if ($cname === '') {
                        continue;
                    }
                    $choices[] = [
                        'name' => $cname,
                        'surcharge_czk' => (int) ($c['surcharge_czk'] ?? 0),
                    ];
                }
            }
            $options[] = [
                'label' => $label,
                'type' => (string) ($g['type'] ?? 'select'),
                'key' => (string) ($g['key'] ?? ''),
                'choices' => $choices,
            ];
        }
    }

    $warranty_czk = function_exists('sklo_warranty_surcharge_czk')
        ? sklo_warranty_surcharge_czk($price)
        : ($price > 0 ? (int) round($price * 0.10) : 0);
    if ($warranty_czk > 0) {
        $options[] = [
            'label' => 'Prodloužená záruka (+1 rok)',
            'type' => 'select',
            'key' => 'prodlouzena_zaruka',
            'choices' => [
                ['name' => 'Ne', 'surcharge_czk' => 0],
                ['name' => 'Ano', 'surcharge_czk' => $warranty_czk],
            ],
        ];
    }

    $kontakt = home_url('/kontakt/');
    $poptavka = home_url('/poptavka/');
    $wa_url = function_exists('sklo_whatsapp_url') ? sklo_whatsapp_url() : 'https://wa.me/420736134604';
    $thumb = $images[0] ?? '';
    $ship_from = isset($p['shipping_from_czk']) ? (int) $p['shipping_from_czk'] : 0;
    $options_json = wp_json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($options_json)) {
        $options_json = '[]';
    }

    if ($back_url === '') {
        $back_url = (string) get_permalink();
    }
    ?>
  <section
    class="sklo-section sklo-pdetail"
    id="produkt-detail"
    data-sklo-pdetail
    data-code="<?php echo esc_attr($code); ?>"
    data-name="<?php echo esc_attr($name); ?>"
    data-base-price="<?php echo esc_attr((string) $price); ?>"
    data-warranty-price="<?php echo esc_attr((string) $warranty_czk); ?>"
    data-image="<?php echo esc_url($thumb); ?>"
    data-kontakt="<?php echo esc_url($kontakt); ?>"
    data-poptavka="<?php echo esc_url($poptavka); ?>"
    data-options="<?php echo esc_attr($options_json); ?>"
  >
    <div class="sklo-wrap">
      <p class="sklo-pdetail__back">
        <a class="sklo-link" href="<?php echo esc_url($back_url); ?>">← Zpět na nabídku</a>
      </p>

      <div class="sklo-pdetail__grid">
        <div class="sklo-pdetail__gallery" data-gallery>
          <?php if ($images !== []) : ?>
            <button
              type="button"
              class="sklo-pdetail__main"
              data-lightbox-open
              data-src="<?php echo esc_url($images[0]); ?>"
              data-alt="<?php echo esc_attr($name); ?>"
            >
              <img
                src="<?php echo esc_url($images[0]); ?>"
                alt="<?php echo esc_attr($name); ?>"
                width="800"
                height="800"
                loading="eager"
                decoding="async"
                data-sklo-pdetail-main
              >
            </button>
            <?php if (count($images) > 1) : ?>
              <div class="sklo-pdetail__thumbs">
                <?php foreach ($images as $i => $img) : ?>
                  <button
                    type="button"
                    class="sklo-pdetail__thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"
                    data-sklo-pdetail-thumb
                    data-src="<?php echo esc_url($img); ?>"
                    data-lightbox-open
                    data-alt="<?php echo esc_attr($name); ?>"
                    aria-label="Foto <?php echo esc_attr((string) ($i + 1)); ?>"
                  >
                    <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async" width="120" height="120">
                  </button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php else : ?>
            <div class="sklo-pdetail__main sklo-pdetail__main--empty" aria-hidden="true"></div>
          <?php endif; ?>
        </div>

        <div class="sklo-pdetail__info">
          <p class="sklo-eyebrow">Sklospeciál</p>
          <h1 class="sklo-pdetail__title"><?php echo esc_html($name); ?></h1>
          <p class="sklo-pdetail__meta">
            <span class="sklo-pdetail__code"><?php echo esc_html($code); ?></span>
            <?php if ($part !== '') : ?>
              <span class="sklo-pdetail__part">Dodavatelský kód <?php echo esc_html($part); ?></span>
            <?php endif; ?>
          </p>
          <?php if ($price > 0) : ?>
            <p class="sklo-pdetail__price" data-sklo-base-price="<?php echo esc_attr((string) $price); ?>">
              <span data-sklo-orient-label>Orientační cena:</span>
              <strong data-sklo-orient-price><?php echo esc_html(sklo_format_cena($price)); ?></strong>
            </p>
            <?php if ($options !== []) : ?>
              <p class="sklo-pdetail__price-base">Základ <?php echo esc_html(sklo_format_cena($price)); ?> · doplatky podle výběru níže</p>
            <?php endif; ?>
          <?php endif; ?>
          <?php if (function_exists('sklo_render_recenze_rating_link')) : ?>
            <?php sklo_render_recenze_rating_link('sklo-rating-link--pdetail'); ?>
          <?php endif; ?>
          <?php if ($ship !== null && (int) $ship > 0) : ?>
            <p class="sklo-pdetail__ship">Expedice cca <?php echo esc_html((string) (int) $ship); ?> pracovních dní</p>
          <?php endif; ?>
          <?php if ($avail !== '') : ?>
            <p class="sklo-pdetail__avail"><?php echo esc_html($avail); ?></p>
          <?php endif; ?>

          <p class="sklo-pdetail__note">Finální nabídka podle rozměrů a dostupnosti. Volitelně prodloužená záruka +1&nbsp;rok — 10&nbsp;% ceny výrobku (bez dopravy a montáže); částku v&nbsp;Kč zvolíš ve volbách níže. <a href="<?php echo esc_url(home_url('/zaruka/')); ?>">Více o záruce</a>.</p>
        </div>
      </div>

      <?php if ($desc !== '') : ?>
        <div class="sklo-pdetail__block">
          <h2>Popis</h2>
          <div class="sklo-pdetail__prose">
            <?php
            $paras = preg_split('/\n+/', $desc) ?: [];
            foreach ($paras as $para) {
                $para = trim($para);
                if ($para === '') {
                    continue;
                }
                if (str_starts_with($para, '- ')) {
                    echo '<p class="sklo-pdetail__li">' . esc_html($para) . '</p>';
                } else {
                    echo '<p>' . esc_html($para) . '</p>';
                }
            }
            ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($includes !== []) : ?>
        <div class="sklo-pdetail__block">
          <h2>V sadě</h2>
          <ul class="sklo-katalog-list">
            <?php foreach ($includes as $item) : ?>
              <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($options !== []) : ?>
        <div class="sklo-pdetail__block sklo-pdetail__options" data-sklo-options>
          <h2>Volby k produktu</h2>
          <p class="sklo-pdetail__options-lead">Vyber variantu a volitelnou záruku — volby můžou změnit cenu.</p>
          <div class="sklo-pdetail__option-groups">
            <?php foreach ($options as $gi => $g) :
                $label = (string) ($g['label'] ?? '');
                $choices = is_array($g['choices'] ?? null) ? $g['choices'] : [];
                $type = (string) ($g['type'] ?? 'select');
                $opt_key = (string) ($g['key'] ?? '');
                $field_id = 'sklo-opt-' . (string) $gi;
                $free_default_idx = null;
                foreach ($choices as $ci => $c) {
                    if ((int) ($c['surcharge_czk'] ?? 0) === 0) {
                        $free_default_idx = (int) $ci;
                        break;
                    }
                }
                ?>
              <div class="sklo-pdetail__option" data-sklo-option-group>
                <?php if ($type === 'text' && $choices === []) : ?>
                  <h3><?php echo esc_html($label !== '' ? $label : 'Možnost'); ?></h3>
                  <p class="sklo-pdetail__option-hint">Zadáš při poptávce (rozměry).</p>
                <?php elseif ($choices !== []) : ?>
                  <label class="sklo-pdetail__select-label" for="<?php echo esc_attr($field_id); ?>">
                    <?php echo esc_html($label !== '' ? $label : 'Možnost'); ?>
                  </label>
                  <select
                    class="sklo-produkt-opt sklo-pdetail__select"
                    id="<?php echo esc_attr($field_id); ?>"
                    name="opt_<?php echo esc_attr((string) $gi); ?>"
                    data-sklo-option-select
                    data-opt-label="<?php echo esc_attr($label !== '' ? $label : 'Možnost'); ?>"
                    <?php if ($opt_key !== '') : ?>data-opt-key="<?php echo esc_attr($opt_key); ?>"<?php endif; ?>
                    <?php if ($opt_key === 'prodlouzena_zaruka') : ?>data-sklo-warranty="1"<?php endif; ?>
                    required
                    aria-label="<?php echo esc_attr($label !== '' ? $label : 'Možnost'); ?>"
                  >
                    <option value="" data-surcharge="0"<?php echo $free_default_idx === null ? ' selected' : ''; ?>>Vyberte…</option>
                    <?php foreach ($choices as $ci => $c) :
                        $cname = (string) ($c['name'] ?? '');
                        $sur = (int) ($c['surcharge_czk'] ?? 0);
                        $opt_label = $cname . ($sur > 0 ? ' (+ ' . number_format($sur, 0, ',', "\u{00a0}") . ' Kč)' : '');
                        $selected = $free_default_idx !== null && (int) $ci === $free_default_idx;
                        ?>
                      <option
                        value="<?php echo esc_attr($cname); ?>"
                        data-surcharge="<?php echo esc_attr((string) $sur); ?>"
                        <?php echo $selected ? ' selected' : ''; ?>
                      ><?php echo esc_html($opt_label); ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="sklo-pdetail__options-err" data-sklo-options-err hidden>Vyber všechny povinné varianty.</p>
        </div>
      <?php endif; ?>

      <div class="sklo-pdetail__block sklo-pdetail__shipcard">
        <h2>Doprava a montáž</h2>
        <div class="sklo-shipcard">
          <div class="sklo-shipcard__col">
            <h3>Doprava</h3>
            <p>
              Doprava po ČR — individuálně dle rozměrů a vzdálenosti.
              U skla počítej s atypickou přepravou.
              Do Prahy často jezdíme ve společných výjezdech (více montáží najednou).
              <?php if ($ship_from > 0) : ?>
                Orientačně od <strong><?php echo esc_html(sklo_format_cena($ship_from)); ?></strong>.
              <?php endif; ?>
              <?php if ($ship !== null && (int) $ship > 0) : ?>
                Expedice cca <?php echo esc_html((string) (int) $ship); ?> pracovních dní.
              <?php endif; ?>
              Detaily: <a href="<?php echo esc_url(home_url('/doprava/#praha')); ?>">doprava</a>,
              <a href="<?php echo esc_url(home_url('/doba-realizace/')); ?>">lhůty</a>.
            </p>
          </div>
          <div class="sklo-shipcard__col">
            <h3>Montáž</h3>
            <p>
              Orientačně: dveře od 2&nbsp;500&nbsp;Kč/ks, sprcha od 3&nbsp;500&nbsp;Kč, zábradlí od 1&nbsp;500&nbsp;Kč/bm.
              Finální cena včetně dojezdu je v nabídce po zaměření.
              Můžeš objednat výrobek samostatně nebo s montáží.
              Detaily: <a href="<?php echo esc_url(home_url('/doprava/#montaz')); ?>">doprava a montáž</a>.
            </p>
          </div>
        </div>
      </div>

      <?php if ($code !== '') : ?>
        <div
          class="sklo-pdetail__block sklo-pdetail__similar"
          data-sklo-similar
          data-code="<?php echo esc_attr($code); ?>"
          data-api="<?php echo esc_url(function_exists('sklo_api_base') ? sklo_api_base() : ''); ?>"
          hidden
        >
          <h2>Podobné produkty</h2>
          <p class="sklo-pdetail__similar-lead">Další dveře ve stejném stylu z katalogu.</p>
          <div class="sklo-produkty__grid sklo-pdetail__similar-grid" data-sklo-similar-grid></div>
        </div>
      <?php endif; ?>

      <?php if (function_exists('sklo_render_risk_block')) : ?>
        <div class="sklo-pdetail__block">
          <?php sklo_render_risk_block('tykani', 'sklo-risk--compact'); ?>
        </div>
      <?php endif; ?>

      <div class="sklo-pdetail__cta sklo-pdetail__cta--bottom sklo-pdetail__cta--triple">
        <button type="button" class="sklo-btn" data-sklo-order-selected>Odeslat poptávku</button>
        <a class="sklo-btn sklo-btn--ghost" href="<?php echo esc_url($poptavka); ?>">Nenašel jsi co hledáš? Pošli nezávaznou poptávku</a>
        <a class="sklo-link" href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
        <?php if (function_exists('sklo_render_cta_sla')) : ?>
          <?php sklo_render_cta_sla('sklo-cta-sla--pdetail'); ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
    <?php
    // Product JSON-LD (no AggregateRating — reviews on site are not verified product reviews).
    $schema_desc = $desc !== '' ? wp_strip_all_tags($desc) : ($name . ' — katalog Sklospeciál, výroba na míru.');
    // Avoid shipping leftover Polish supplier copy into structured data.
    if (preg_match('/\b(lodow|tluczon|szkla|polaczone|trzy|srodkowa|zewnetrzne|uszkodzeni|folii|kabina|szklan)\b/iu', $schema_desc)) {
        $schema_desc = $name . ' — výroba na míru z katalogu Sklospeciál. Orientační cena podle rozměrů a provedení; finální nabídka po zaměření.';
    }
    if (mb_strlen($schema_desc) > 300) {
        $schema_desc = rtrim(mb_substr($schema_desc, 0, 297)) . '…';
    }
    $schema_url = $code !== '' && function_exists('sklo_produkt_detail_url')
        ? sklo_produkt_detail_url($code, $back_url !== '' ? $back_url : (string) get_permalink())
        : ($back_url !== '' ? $back_url : (string) get_permalink());
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        'name'     => $name,
        'sku'      => $code,
        'brand'    => [
            '@type' => 'Brand',
            'name'  => 'Sklospeciál',
        ],
        'description' => $schema_desc,
        'url'         => $schema_url,
    ];
    if ($images !== []) {
        $schema['image'] = count($images) === 1 ? $images[0] : array_values($images);
    }
    if ($price > 0) {
        $avail_url = 'https://schema.org/InStock';
        $avail_l = mb_strtolower($avail);
        if ($avail_l !== '' && (str_contains($avail_l, 'nedostup') || str_contains($avail_l, 'vyprod'))) {
            $avail_url = 'https://schema.org/OutOfStock';
        } elseif ($avail_l !== '' && (str_contains($avail_l, 'na objedn') || str_contains($avail_l, 'vyrob'))) {
            $avail_url = 'https://schema.org/PreOrder';
        }
        $schema['offers'] = [
            '@type'         => 'Offer',
            'url'           => $schema_url,
            'priceCurrency' => 'CZK',
            'price'         => number_format($price, 2, '.', ''),
            'availability'  => $avail_url,
            'seller'        => [
                '@type' => 'Organization',
                'name'  => 'Sklospeciál',
            ],
        ];
    }
    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
}

/**
 * Render product grid for a category slug (optional section filter).
 */
function sklo_render_produkty_grid(string $slug, ?string $section = null, int $initial = 24): void
{
    $bundle = sklo_produkty_for_slug($slug);
    if ($bundle === null || empty($bundle['products'])) {
        return;
    }

    $products = $bundle['products'];
    if ($section !== null && $section !== '') {
        $products = array_values(array_filter(
            $products,
            static fn(array $p): bool => (string) ($p['section'] ?? '') === $section
        ));
    }
    if ($products === []) {
        return;
    }

    $total = count($products);
    $note = sklo_cena_note();
    $grid_id = 'sklo-prod-' . preg_replace('/[^a-z0-9\-]+/', '-', $slug . ($section ? '-' . $section : ''));
    $base = (string) get_permalink();
    $section_labels = [];
    $cat_meta = function_exists('sklo_katalog_for_slug') ? sklo_katalog_for_slug($slug) : null;
    if (is_array($cat_meta) && !empty($cat_meta['sections']) && is_array($cat_meta['sections'])) {
        foreach ($cat_meta['sections'] as $sec) {
            if (!is_array($sec)) {
                continue;
            }
            $sid = (string) ($sec['id'] ?? '');
            if ($sid === '') {
                continue;
            }
            $section_labels[$sid] = (string) ($sec['title'] ?? $sid);
        }
    }
    $cat_title = is_array($cat_meta) ? (string) ($cat_meta['title'] ?? '') : '';
    $has_poptavka_btn = shortcode_exists('poptavka_tlacitko');
    ?>
  <section class="sklo-section sklo-produkty" id="<?php echo esc_attr($grid_id); ?>" data-sklo-produkty data-initial="<?php echo esc_attr((string) $initial); ?>">
    <div class="sklo-wrap">
      <header class="sklo-section__head sklo-section__head--center">
        <p class="sklo-eyebrow">Produkty</p>
        <h2><?php echo $section ? 'Vybrané produkty' : 'Nabídka v této kategorii'; ?></h2>
        <p><span data-sklo-produkty-count><?php echo esc_html((string) $total); ?></span> položek · <?php echo esc_html($note); ?></p>
        <?php if (function_exists('sklo_render_recenze_rating_link')) : ?>
          <p class="sklo-produkty__rating"><?php sklo_render_recenze_rating_link('sklo-rating-link--inline'); ?></p>
        <?php endif; ?>
      </header>
      <div class="sklo-produkty__grid" data-gallery>
        <?php foreach ($products as $i => $p) :
            $name  = sklo_public_product_text((string) ($p['name'] ?? ''));
            $price = (int) ($p['price'] ?? 0);
            $img   = (string) ($p['image'] ?? '');
            $code  = (string) ($p['code'] ?? '');
            $hidden = $i >= $initial;
            $detail = $code !== '' ? sklo_produkt_detail_url($code, $base) : '';
            $psec = (string) ($p['section'] ?? '');
            $kat = $section_labels[$psec] ?? ($psec !== '' ? $psec : $cat_title);
            ?>
          <article
            class="sklo-produkty__item<?php echo $hidden ? ' is-collapsed' : ''; ?>"
            data-category="<?php echo esc_attr($psec); ?>"
            <?php echo $hidden ? 'hidden' : ''; ?>
          >
            <?php if ($detail !== '') : ?>
              <a class="sklo-produkty__link" href="<?php echo esc_url($detail); ?>">
            <?php endif; ?>
            <?php if ($img !== '') :
                // Always set src (never empty). Collapsed cards stay hidden; lazy loading
                // defers offscreen bytes. Empty src + data-src broke crawlers / audits.
                $thumb = sklo_quba_thumb($img, '400_400');
                ?>
              <span class="sklo-produkty__media">
                <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async" width="400" height="400">
              </span>
            <?php else : ?>
              <div class="sklo-produkty__media sklo-produkty__media--empty" role="img" aria-label="Fotografie není k dispozici"></div>
            <?php endif; ?>
            <div class="sklo-produkty__body">
              <h3 class="sklo-produkty__name"><?php echo esc_html($name); ?></h3>
              <?php if ($code !== '') : ?>
                <p class="sklo-produkty__code"><?php echo esc_html($code); ?></p>
              <?php endif; ?>
              <?php if ($price > 0) : ?>
                <p class="sklo-produkty__price"><?php echo esc_html(sklo_format_cena_od($price)); ?></p>
              <?php endif; ?>
              <p class="sklo-produkty__more-link">Detail a doplatky</p>
            </div>
            <?php if ($detail !== '') : ?>
              </a>
            <?php endif; ?>
            <?php
            if ($has_poptavka_btn && $code !== '' && $name !== '') {
                $cena_txt = $price > 0 ? sklo_format_cena($price) : '';
                $img_url = $img;
                if ($img_url !== '' && function_exists('sklo_public_asset_url')) {
                    $img_url = sklo_public_asset_url($img_url);
                }
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode attrs escaped; markup from shortcode.
                echo do_shortcode(sprintf(
                    '[poptavka_tlacitko id="%s" nazev="%s" kategorie="%s" cena="%s" fotka="%s"]',
                    esc_attr($code),
                    esc_attr($name),
                    esc_attr($kat),
                    esc_attr($cena_txt),
                    esc_attr($img_url)
                ));
            }
            ?>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($total > $initial) : ?>
        <div class="sklo-produkty__more">
          <button type="button" class="sklo-btn sklo-btn--ghost" data-sklo-produkty-more>
            Zobrazit další (<?php echo esc_html((string) ($total - $initial)); ?>)
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>
    <?php
}
