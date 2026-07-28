<?php
/**
 * Sklospeciál theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('SKLO_THEME_VER', '1.2.3');

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
    }
    return $parts;
});

add_action('wp_head', function (): void {
    if (!is_front_page()) {
        return;
    }
    $desc = 'Zaměř otvor, pošli fotky a navrhni si dveře online. Otočné, posuvné i celoskleněné. Nabídku připravíme podle tvých rozměrů.';
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
        'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap',
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
        ['/sklenene-dvere/', 'Skleněné dveře'],
        ['/realizace/', 'Realizace'],
        ['/navod-na-zamereni/', 'Návod na zaměření'],
        ['/kontakt/', 'Kontakt'],
    ];
    echo '<ul class="nav-list">';
    foreach ($items as [$path, $label]) {
        $url = home_url($path);
        printf(
            '<li><a href="%s"%s>%s</a></li>',
            esc_url($url),
            sklo_is_current($path) ? ' aria-current="page"' : '',
            esc_html($label)
        );
    }
    echo '</ul>';
}

function sklo_is_current(string $path): bool
{
    $req = trailingslashit(wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/');
    return $req === trailingslashit($path);
}
