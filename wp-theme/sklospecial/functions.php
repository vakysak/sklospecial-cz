<?php
/**
 * Sklospeciál theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('SKLO_THEME_VER', '1.6.1');

require_once get_template_directory() . '/inc/katalog-data.php';
require_once get_template_directory() . '/inc/katalog-produkty.php';

function sklo_api_base(): string
{
    return (string) apply_filters(
        'sklo_api_base',
        'https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io'
    );
}

function sklo_konfigurator_url(): string
{
    return rtrim(sklo_api_base(), '/') . '/public/konfigurator.html';
}

add_filter('document_title_parts', function (array $parts): array {
    if (is_front_page()) {
        $parts['title'] = 'Sklospeciál — Skleněné dveře na míru bez showroomu';
        unset($parts['tagline'], $parts['site']);
    } elseif (is_page('realizace')) {
        $parts['title'] = 'Realizace — skleněné dveře v interiérech';
        unset($parts['tagline']);
    }
    return $parts;
});

add_action('wp_head', function (): void {
    if (is_front_page()) {
        $desc = 'Zaměř otvor, pošli fotky a navrhni si dveře online. Kyvné, posuvné i celoskleněné. Nabídku připravíme podle tvých rozměrů.';
    } elseif (is_page('realizace')) {
        $desc = 'Galerie hotových skleněných dveří — kyvné, posuvné i celoskleněné v bytech a kancelářích. Podívej se na realizace a navrhni si vlastní.';
    } else {
        return;
    }
    echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
}, 1);

add_action('after_setup_theme', function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary' => 'Hlavní menu',
        'footer'  => 'Patička',
    ]);
});

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style(
        'sklo-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Inter:wght@300;400&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'sklo-main',
        get_template_directory_uri() . '/assets/css/main.css',
        ['sklo-fonts'],
        SKLO_THEME_VER
    );
    wp_enqueue_script(
        'sklo-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        SKLO_THEME_VER,
        true
    );
});

/** Odstraní výchozí WP blokové styly, které bijí do našeho layoutu. */
add_action('wp_enqueue_scripts', function (): void {
    wp_dequeue_style('wp-block-library-theme');
}, 100);

function sklo_nav_fallback(): void
{
    $items = [
        [
            '/sklenene-dvere/',
            'Skleněné dveře',
            [
                ['/sklenene-dvere/posuvne/', 'Posuvné'],
                ['/sklenene-dvere/otocne/', 'Kyvné'],
                ['/sklenene-dvere/otevirane/', 'Otevírané'],
                ['/sklenene-dvere/vzory-skla/', 'Vzory'],
                ['/sklenene-dvere/skladem/', 'Skladem'],
            ],
        ],
        ['/sprchove-kouty/', 'Sprchové kouty'],
        ['/zabradli/', 'Zábradlí'],
        ['/strisky/', 'Stříšky'],
        ['/francouzske-balkony/', 'Balkony'],
        ['/realizace/', 'Realizace'],
        ['/navod-na-zamereni/', 'Návod'],
        ['/kontakt/', 'Kontakt'],
    ];
    echo '<ul class="nav-list">';
    foreach ($items as $item) {
        $path  = $item[0];
        $label = $item[1];
        $kids  = $item[2] ?? [];
        $url   = home_url($path);
        echo '<li' . ($kids ? ' class="menu-item-has-children"' : '') . '>';
        printf(
            '<a href="%s"%s>%s</a>',
            esc_url($url),
            sklo_is_current($path) ? ' aria-current="page"' : '',
            esc_html($label)
        );
        if ($kids) {
            echo '<ul class="sub-menu">';
            foreach ($kids as [$cpath, $clabel]) {
                printf(
                    '<li><a href="%s"%s>%s</a></li>',
                    esc_url(home_url($cpath)),
                    sklo_is_current($cpath) ? ' aria-current="page"' : '',
                    esc_html($clabel)
                );
            }
            echo '</ul>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

/**
 * Rank Math / document title for katalog pages.
 */
add_filter('document_title_parts', function (array $parts): array {
    if (!is_page()) {
        return $parts;
    }
    $slug = get_post_field('post_name', get_queried_object_id()) ?: '';
    $cat  = sklo_katalog_for_slug($slug);
    if ($cat && !empty($cat['seo_title'])) {
        $parts['title'] = (string) $cat['seo_title'];
        unset($parts['tagline'], $parts['site']);
    }
    return $parts;
}, 20);

add_action('wp_head', function (): void {
    if (!is_page()) {
        return;
    }
    $slug = get_post_field('post_name', get_queried_object_id()) ?: '';
    $cat  = sklo_katalog_for_slug($slug);
    if (!$cat || empty($cat['seo_desc'])) {
        return;
    }
    echo '<meta name="description" content="' . esc_attr((string) $cat['seo_desc']) . '" />' . "\n";
}, 1);

function sklo_is_current(string $path): bool
{
    $req = trailingslashit(wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/');
    return $req === trailingslashit($path);
}
