<?php
/**
 * SEO guide pages under /pruvodce/.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Guide entries for hub + related blocks.
 *
 * @return list<array{slug:string,path:string,title:string,blurb:string,seo_title?:string,seo_desc?:string,external?:bool}>
 */
function sklo_pruvodce_guides(): array
{
    return [
        [
            'slug'      => 'pruvodce',
            'path'      => '/pruvodce/',
            'title'     => 'Průvodce',
            'blurb'     => 'Přehled krátkých průvodců ke skleněným dveřím, sprše a zábradlí.',
            'seo_title' => 'Průvodce skleněnými dveřmi a sprchou | Sklospeciál',
            'seo_desc'  => 'Krátké průvodce: posuvné vs. otevírané, cena dveří, walk-in sprcha, zábradlí a zaměření. Konkrétně a bez omáčky.',
            'hub'       => true,
        ],
        [
            'slug'      => 'posuvne-vs-otevirane',
            'path'      => '/pruvodce/posuvne-vs-otevirane/',
            'title'     => 'Posuvné vs. otevírané dveře',
            'blurb'     => 'Kdy dává smysl posuv, kdy klasické otevírání — a co to znamená pro prostor.',
            'seo_title' => 'Posuvné vs. otevírané skleněné dveře | Sklospeciál',
            'seo_desc'  => 'Kdy zvolit posuvné a kdy otevírané skleněné dveře. Srovnání, tipy k prostoru a odkaz do katalogu i studia.',
        ],
        [
            'slug'      => 'cena-sklenenych-dveri',
            'path'      => '/pruvodce/cena-sklenenych-dveri/',
            'title'     => 'Cena skleněných dveří',
            'blurb'     => 'Co cenu ovlivní a orientační pásma „od“ z katalogu.',
            'seo_title' => 'Cena skleněných dveří — co ji ovlivní | Sklospeciál',
            'seo_desc'  => 'Co ovlivní cenu skleněných dveří a orientační pásma „od“ z katalogu. Pošli rozměry a fotky pro přesnou nabídku.',
        ],
        [
            'slug'      => 'walk-in-sprchovy-kout',
            'path'      => '/pruvodce/walk-in-sprchovy-kout/',
            'title'     => 'Walk-in sprchový kout',
            'blurb'     => 'Proč walk-in, na co myslet při zaměření a kam dál v katalogu.',
            'seo_title' => 'Walk-in sprchový kout — výhody a tipy | Sklospeciál',
            'seo_desc'  => 'Walk-in sprchový kout bez dveří: výhody, na co myslet při zaměření a odkaz do katalogu walk-in stěn.',
        ],
        [
            'slug'      => 'zabradli-montaz',
            'path'      => '/pruvodce/zabradli-montaz/',
            'title'     => 'Skleněné zábradlí a montáž',
            'blurb'     => 'Přehled typů, co řešíme před montáží a jak poslat podklady.',
            'seo_title' => 'Skleněné zábradlí a montáž | Sklospeciál',
            'seo_desc'  => 'Přehled skleněného zábradlí, kotvení a montáže. Co zaměřit a jak poslat podklady k nabídce.',
        ],
        [
            'slug'      => 'navod-na-zamereni',
            'path'      => '/navod-na-zamereni/',
            'title'     => 'Jak zaměřit otvor',
            'blurb'     => 'Krok za krokem: metr, fotky a co poslat k nabídce. Canonical návod.',
            'external'  => true,
        ],
    ];
}

/**
 * Guides listed on the hub (excludes hub itself).
 *
 * @return list<array<string, mixed>>
 */
function sklo_pruvodce_hub_list(): array
{
    $out = [];
    foreach (sklo_pruvodce_guides() as $g) {
        if (!empty($g['hub'])) {
            continue;
        }
        $out[] = $g;
    }
    return $out;
}

/**
 * Resolve guide SEO data for the current (or given) page.
 *
 * @return array<string, mixed>|null
 */
function sklo_pruvodce_for_page(?int $post_id = null): ?array
{
    $post_id = $post_id ?? (int) get_queried_object_id();
    if ($post_id <= 0) {
        return null;
    }
    $slug = (string) get_post_field('post_name', $post_id);
    if ($slug === '') {
        return null;
    }
    $parent = (int) wp_get_post_parent_id($post_id);
    $parent_slug = $parent > 0 ? (string) get_post_field('post_name', $parent) : '';
    if ($slug !== 'pruvodce' && $parent_slug !== 'pruvodce') {
        return null;
    }
    foreach (sklo_pruvodce_guides() as $g) {
        if (($g['slug'] ?? '') === $slug) {
            return $g;
        }
    }
    return null;
}
