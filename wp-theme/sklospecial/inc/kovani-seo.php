<?php
/**
 * SEO helpers for kování catalogue hubs (?kod= details share product SEO).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hub metadata keyed by page slug.
 *
 * @return array<string, array<string, mixed>>
 */
function sklo_kovani_hubs(): array
{
    static $hubs = null;
    if (is_array($hubs)) {
        return $hubs;
    }

    $hubs = [
        'kovani-strisky' => [
            'slug'        => 'kovani-strisky',
            'h1'          => 'Kování pro skleněné stříšky',
            'short_title' => 'Kování stříšky',
            'keyword'     => 'kování stříšky',
            'seo_title'   => 'Kování stříšky — držáky a upevnění skla | Sklospeciál',
            'seo_desc'    => 'Kování stříšky Süd-Metall — MOTIVO, SEASONS, SWORD, CANO. Orientační ceny, varianty v detailu. Přidej do poptávky u Sklospeciál.',
            'seo_intro'   => [
                'Kování pro skleněné stříšky je nabídka držáků a upevnění — typicky Süd-Metall systémy jako MOTIVO, SEASONS, SWORD nebo CANO. Vybereš typ podle tloušťky skla a způsobu uchycení, ve detailu doladíš variantu a přidáš položku do nezávazné poptávky.',
                'Samotnou skleněnou stříšku řešíš v katalogu stříšek; tady doplňuješ kování. Orientační ceny včetně DPH — finální nabídka podle sestavy a montáže.',
            ],
            'parent'      => ['/strisky/', 'Skleněné stříšky'],
            'related'     => [
                ['/strisky/', 'Skleněné stříšky'],
                ['/kovani-zabradli/', 'Kování zábradlí'],
                ['/kovani-sprchy/', 'Kování sprchy'],
                ['/poptavka/', 'Nezávazná poptávka'],
            ],
        ],
        'kovani-zabradli' => [
            'slug'        => 'kovani-zabradli',
            'h1'          => 'Kování pro zábradlí',
            'short_title' => 'Kování zábradlí',
            'keyword'     => 'kování zábradlí',
            'seo_title'   => 'Kování zábradlí — držáky, sloupky a trubky | Sklospeciál',
            'seo_desc'    => 'Kování zábradlí — držáky, sloupky, trubky, spojky a výplně pro skleněné i nerezové sestavy. Přidej do poptávky u Sklospeciál.',
            'seo_intro'   => [
                'Kování zábradlí pokrývá prvky pro výrobu sestav — trubky a profily, spojky, držáky skla, sloupky, lankové systémy i upevnění na zeď a podlahu. Filtruj podle podkategorie, otevři detail a přidej vybrané položky do poptávky.',
                'Hotové skleněné zábradlí řešíš v katalogu zábradlí; tady skládáš díly. Ceny jsou orientační včetně DPH — doplatky za varianty uvidíš v detailu.',
            ],
            'parent'      => ['/zabradli/', 'Skleněné zábradlí'],
            'related'     => [
                ['/zabradli/', 'Skleněné zábradlí'],
                ['/francouzske-balkony/', 'Francouzské balkony'],
                ['/kovani-strisky/', 'Kování stříšky'],
                ['/poptavka/', 'Nezávazná poptávka'],
            ],
        ],
        'kovani-sprchy' => [
            'slug'        => 'kovani-sprchy',
            'h1'          => 'Kování pro sprchové kouty',
            'short_title' => 'Kování sprchy',
            'keyword'     => 'kování sprchy',
            'seo_title'   => 'Kování sprchy — panty, úchyty a posuvy | Sklospeciál',
            'seo_desc'    => 'Kování sprchy — panty, úchyty, posuvné systémy, madla a těsnění pro sprchové kouty. Přidej do poptávky u Sklospeciál.',
            'seo_intro'   => [
                'Kování sprchy zahrnuje panty, upevnění zástěn, posuvné systémy, madla, úchytky i těsnící profily. Vybereš podkategorii, v detailu zvolíš variantu a přidáš položku do nezávazné poptávky.',
                'Celý sprchový kout nebo walk-in stěnu řešíš v katalogu sprch; tady doplňuješ kování. Orientační ceny včetně DPH — finální nabídka podle sestavy.',
            ],
            'parent'      => ['/sprchove-kouty/', 'Sprchové kouty'],
            'related'     => [
                ['/sprchove-kouty/', 'Sprchové kouty'],
                ['/kovani-posuvne/', 'Kování posuvné dveře'],
                ['/kovani-otevirane/', 'Kování otevírané dveře'],
                ['/poptavka/', 'Nezávazná poptávka'],
            ],
        ],
        'kovani-posuvne' => [
            'slug'        => 'kovani-posuvne',
            'h1'          => 'Kování pro posuvné skleněné dveře',
            'short_title' => 'Kování posuvné dveře',
            'keyword'     => 'kování posuvné skleněné dveře',
            'seo_title'   => 'Kování posuvné skleněné dveře — posuvy a zámky | Sklospeciál',
            'seo_desc'    => 'Kování posuvné skleněné dveře — posuvy, mušle, úchytky a zámky. Orientační ceny, varianty v detailu. Přidej do poptávky u Sklospeciál.',
            'seo_intro'   => [
                'Kování pro posuvné skleněné dveře pokrývá posuvy, mušle a úchytky i zámky. Otevři detail, zvol variantu a přidej položku do nezávazné poptávky — vhodné jako doplněk k dveřím na míru.',
                'Samotné posuvné dveře řešíš v katalogu; tady vybíráš kování. Orientační ceny včetně DPH, finální nabídka podle sestavy a montáže.',
            ],
            'parent'      => ['/sklenene-dvere/posuvne/', 'Posuvné skleněné dveře'],
            'related'     => [
                ['/sklenene-dvere/posuvne/', 'Posuvné skleněné dveře'],
                ['/kovani-otevirane/', 'Kování otevírané dveře'],
                ['/kovani-sprchy/', 'Kování sprchy'],
                ['/poptavka/', 'Nezávazná poptávka'],
            ],
        ],
        'kovani-otevirane' => [
            'slug'        => 'kovani-otevirane',
            'h1'          => 'Kování pro otevírané skleněné dveře',
            'short_title' => 'Kování otevírané dveře',
            'keyword'     => 'kování otevírané skleněné dveře',
            'seo_title'   => 'Kování otevírané skleněné dveře — kliky a panty | Sklospeciál',
            'seo_desc'    => 'Kování otevírané skleněné dveře — kliky, panty, madla a závěsy. Orientační ceny, varianty v detailu. Přidej do poptávky u Sklospeciál.',
            'seo_intro'   => [
                'Kování pro otevírané skleněné dveře zahrnuje kliky a zámky, panty a závěsy, madla, koule i doplňky. Filtruj podle podkategorie, otevři detail a přidej vybrané položky do poptávky.',
                'Celoskleněné otevírané dveře řešíš v katalogu; tady doplňuješ kování. Ceny jsou orientační včetně DPH — doplatky za varianty uvidíš v detailu.',
            ],
            'parent'      => ['/sklenene-dvere/otevirane/', 'Otevírané skleněné dveře'],
            'related'     => [
                ['/sklenene-dvere/otevirane/', 'Otevírané skleněné dveře'],
                ['/kovani-posuvne/', 'Kování posuvné dveře'],
                ['/kovani-sprchy/', 'Kování sprchy'],
                ['/poptavka/', 'Nezávazná poptávka'],
            ],
        ],
    ];

    return $hubs;
}

/**
 * @return array<string, mixed>|null
 */
function sklo_kovani_hub(?string $slug = null): ?array
{
    if ($slug === null || $slug === '') {
        if (!is_page()) {
            return null;
        }
        $slug = (string) (get_post_field('post_name', get_queried_object_id()) ?: '');
    }
    $hubs = sklo_kovani_hubs();
    return $hubs[$slug] ?? null;
}

/**
 * Resolve hub from product section / code prefix.
 *
 * @param array<string, mixed> $produkt
 * @return array<string, mixed>|null
 */
function sklo_kovani_hub_for_product(array $produkt): ?array
{
    $sec = (string) ($produkt['section'] ?? '');
    if ($sec !== '') {
        $hub = sklo_kovani_hub($sec);
        if ($hub) {
            return $hub;
        }
    }
    $code = (string) ($produkt['code'] ?? '');
    $map = [
        'KOV-ZAB-' => 'kovani-zabradli',
        'KOV-SPR-' => 'kovani-sprchy',
        'KOV-POS-' => 'kovani-posuvne',
        'KOV-OTE-' => 'kovani-otevirane',
        'KOV-'     => 'kovani-strisky',
    ];
    foreach ($map as $prefix => $slug) {
        if (str_starts_with($code, $prefix)) {
            return sklo_kovani_hub($slug);
        }
    }
    return null;
}

/**
 * Meaningful image alt: product/subcat name + category keyword (no empty string).
 */
function sklo_kovani_img_alt(string $name, string $keyword): string
{
    $name = trim($name);
    $keyword = trim($keyword);
    if ($name === '') {
        return $keyword !== '' ? $keyword : 'Kování Sklospeciál';
    }
    if ($keyword === '') {
        return $name;
    }
    $hay = mb_strtolower($name);
    $needles = array_filter([
        mb_strtolower($keyword),
        preg_replace('/^kování\s+/iu', '', mb_strtolower($keyword)) ?: '',
    ]);
    foreach ($needles as $n) {
        if ($n !== '' && mb_strpos($hay, $n) !== false) {
            return $name;
        }
    }
    return $name . ' — ' . $keyword;
}

/**
 * Related internal links under a kování hub listing.
 */
function sklo_render_kovani_related(string $slug): void
{
    $hub = sklo_kovani_hub($slug);
    if (!$hub) {
        return;
    }
    $related = is_array($hub['related'] ?? null) ? $hub['related'] : [];
    if ($related === []) {
        return;
    }
    echo '<section class="sklo-section sklo-related-pillars" aria-label="Související kategorie">';
    echo '<div class="sklo-wrap">';
    echo '<header class="sklo-section__head sklo-section__head--center">';
    echo '<p class="sklo-eyebrow">Tip</p>';
    echo '<h2>Mohlo by tě zajímat</h2>';
    echo '<p>Navázané katalogové stránky a poptávka — stejná dílna, montáž po ČR.</p>';
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
