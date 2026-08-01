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
 * @return list<array{slug:string,path:string,title:string,blurb:string,external?:bool}>
 */
function sklo_pruvodce_guides(): array
{
    return [
        [
            'slug'  => 'posuvne-vs-otevirane',
            'path'  => '/pruvodce/posuvne-vs-otevirane/',
            'title' => 'Posuvné vs. otevírané dveře',
            'blurb' => 'Kdy dává smysl posuv, kdy klasické otevírání — a co to znamená pro prostor.',
        ],
        [
            'slug'  => 'cena-sklenenych-dveri',
            'path'  => '/pruvodce/cena-sklenenych-dveri/',
            'title' => 'Cena skleněných dveří',
            'blurb' => 'Co cenu ovlivní a orientační pásma „od“ z katalogu.',
        ],
        [
            'slug'  => 'walk-in-sprchovy-kout',
            'path'  => '/pruvodce/walk-in-sprchovy-kout/',
            'title' => 'Walk-in sprchový kout',
            'blurb' => 'Proč walk-in, na co myslet při zaměření a kam dál v katalogu.',
        ],
        [
            'slug'  => 'zabradli-montaz',
            'path'  => '/pruvodce/zabradli-montaz/',
            'title' => 'Skleněné zábradlí a montáž',
            'blurb' => 'Přehled typů, co řešíme před montáží a jak poslat podklady.',
        ],
        [
            'slug'     => 'navod-na-zamereni',
            'path'     => '/navod-na-zamereni/',
            'title'    => 'Jak zaměřit otvor',
            'blurb'    => 'Krok za krokem: metr, fotky a co poslat k nabídce. Canonical návod.',
            'external' => true,
        ],
    ];
}
