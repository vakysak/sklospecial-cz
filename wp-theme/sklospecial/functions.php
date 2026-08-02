<?php
/**
 * Sklospeciál theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('SKLO_THEME_VER', '1.17.4');

/** Default konfigurátor/API host. Override via option sklo_api_base or env SKLO_API_BASE. */
define(
    'SKLO_API_BASE_DEFAULT',
    'https://api.sklospecial.eu'
);

require_once get_template_directory() . '/inc/katalog-data.php';
require_once get_template_directory() . '/inc/katalog-produkty.php';
require_once get_template_directory() . '/inc/seo-landings-data.php';
require_once get_template_directory() . '/inc/pruvodce-data.php';
require_once get_template_directory() . '/inc/recenze-data.php';
require_once get_template_directory() . '/inc/trust-helpers.php';

/**
 * Production host? sklospecial.eu / sklospecial.cz (with or without www).
 */
function sklo_is_production(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host) ?: '';
    $host = preg_replace('/^www\./', '', $host) ?: '';
    return $host === 'sklospecial.eu' || $host === 'sklospecial.cz';
}

/**
 * Staging host? (not production .eu / .cz).
 */
function sklo_is_staging_host(): bool
{
    return !sklo_is_production();
}

/** @deprecated Use sklo_is_production() */
function sklo_is_production_host(): bool
{
    return sklo_is_production();
}

/**
 * Theme pojistka: noindex on non-production hosts.
 * When host is sklospecial.eu or sklospecial.cz, indexing auto-enables here.
 * Still turn off WP Reading “Discourage” + Rank Math noindex on go-live day.
 */
function sklo_staging_robots_noindex(): void
{
    if (sklo_is_production()) {
        return;
    }
    echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}
add_action('wp_head', 'sklo_staging_robots_noindex', 0);

add_filter('robots_txt', static function ($output, $public) {
    if (!sklo_is_production()) {
        return "User-agent: *\nDisallow: /\n";
    }
    $out = is_string($output) ? $output : '';
    if ($out === '' || !str_contains($out, 'User-agent:')) {
        $out = "User-agent: *\nAllow: /\n";
    }
    $sitemap = home_url('/sitemap_index.xml');
    if (!str_contains($out, 'Sitemap:')) {
        $out = rtrim($out) . "\n\nSitemap: {$sitemap}\n";
    }
    return $out;
}, 10, 2);

/**
 * P1 security headers. HSTS only on production host (not sslip).
 */
add_action('send_headers', static function (): void {
    if (headers_sent()) {
        return;
    }
    header('X-Frame-Options: SAMEORIGIN', false);
    header('X-Content-Type-Options: nosniff', false);
    header('Referrer-Policy: strict-origin-when-cross-origin', false);
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()', false);
    if (sklo_is_production() && is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains', false);
    }
});

/** Disable XML-RPC + drop X-Pingback. */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', static function (): array {
    return [];
});
// Kill XML-RPC as soon as the theme loads (xmlrpc.php defines XMLRPC_REQUEST before theme).
if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
    status_header(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'XML-RPC is disabled.';
    exit;
}
add_filter('wp_headers', static function (array $headers): array {
    unset($headers['X-Pingback']);
    return $headers;
});
// Also remove RSD / WLW manifest discovery links.
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

/**
 * Block public users REST for anonymous clients.
 */
add_filter('rest_authentication_errors', static function ($result) {
    if (!empty($result)) {
        return $result;
    }
    if (is_user_logged_in()) {
        return $result;
    }
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if (preg_match('#/wp-json/wp/v2/users(?:/|\?|$)#', $uri)) {
        return new WP_Error(
            'rest_forbidden',
            'Users endpoint is disabled for anonymous access.',
            ['status' => 401]
        );
    }
    return $result;
});

add_filter('rest_endpoints', static function (array $endpoints): array {
    if (is_user_logged_in()) {
        return $endpoints;
    }
    foreach (array_keys($endpoints) as $route) {
        if (is_string($route) && str_starts_with($route, '/wp/v2/users')) {
            unset($endpoints[$route]);
        }
    }
    return $endpoints;
});

/**
 * Optional Cloudflare Turnstile keys (Simple CAPTCHA plugin or theme options).
 */
function sklo_turnstile_site_key(): string
{
    foreach (['cfturnstile_key', 'turnstile_site_key', 'sklo_turnstile_site_key'] as $opt) {
        $v = (string) get_option($opt, '');
        if ($v !== '') {
            return $v;
        }
    }
    return (string) apply_filters('sklo_turnstile_site_key', '');
}

function sklo_turnstile_secret_key(): string
{
    foreach (['cfturnstile_secret', 'turnstile_secret_key', 'sklo_turnstile_secret'] as $opt) {
        $v = (string) get_option($opt, '');
        if ($v !== '') {
            return $v;
        }
    }
    return (string) apply_filters('sklo_turnstile_secret_key', '');
}

function sklo_turnstile_configured(): bool
{
    return sklo_turnstile_site_key() !== '' && sklo_turnstile_secret_key() !== '';
}

/**
 * Client IP for rate limiting (respects first X-Forwarded-For hop on proxy).
 */
function sklo_client_ip(): string
{
    $xff = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    if ($xff !== '') {
        $parts = array_map('trim', explode(',', $xff));
        if ($parts[0] !== '') {
            return substr($parts[0], 0, 45);
        }
    }
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/**
 * Product from ?kod= on katalog templates.
 *
 * @return array<string, mixed>|null
 */
function sklo_request_produkt(): ?array
{
    if (!isset($_GET['kod'])) {
        return null;
    }
    $kod = sanitize_text_field(wp_unslash((string) $_GET['kod']));
    if ($kod === '' || !function_exists('sklo_produkt_by_code')) {
        return null;
    }
    return sklo_produkt_by_code($kod);
}

function sklo_api_base(): string
{
    $from_env = (string) (getenv('SKLO_API_BASE') ?: '');
    $from_opt = (string) get_option('sklo_api_base', '');
    $base = $from_opt !== '' ? $from_opt : ($from_env !== '' ? $from_env : SKLO_API_BASE_DEFAULT);
    $base = rtrim($base, '/');
    return (string) apply_filters('sklo_api_base', $base);
}

function sklo_konfigurator_url(): string
{
    return rtrim(sklo_api_base(), '/') . '/public/konfigurator.html';
}

/**
 * Absolute URL for API-hosted katalog product image.
 */
function sklo_katalog_img_url(string $code): string
{
    $code = trim($code);
    if ($code === '') {
        return '';
    }
    if (!str_ends_with(strtolower($code), '.jpg') && !str_ends_with(strtolower($code), '.webp')) {
        $code .= '.jpg';
    }
    return rtrim(sklo_api_base(), '/') . '/public/katalog-img/' . ltrim($code, '/');
}

/**
 * Rewrite legacy staging hosts (WP sslip / old API sslip) to current home / API base.
 * Leaves non-staging URLs untouched. Safe for chat/API assets still served from sslip.
 */
function sklo_public_asset_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    $wp_staging = 'https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io';
    $api_staging = 'https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io';
    if (str_starts_with($url, $wp_staging)) {
        $path = substr($url, strlen($wp_staging));
        return home_url($path === '' ? '/' : $path);
    }
    if (str_starts_with($url, $api_staging)) {
        $path = substr($url, strlen($api_staging));
        return rtrim(sklo_api_base(), '/') . ($path === '' ? '' : $path);
    }
    return $url;
}

add_filter('document_title_parts', function (array $parts): array {
    $produkt = sklo_request_produkt();
    if ($produkt !== null) {
        $name = function_exists('sklo_public_product_text')
            ? sklo_public_product_text((string) ($produkt['name'] ?? ''))
            : (string) ($produkt['name'] ?? '');
        $code = (string) ($produkt['code'] ?? '');
        if ($name !== '') {
            $parts['title'] = $name . ($code !== '' ? ' (' . $code . ')' : '') . ' | Sklospeciál';
            unset($parts['tagline'], $parts['site']);
            return $parts;
        }
    }
    if (is_front_page()) {
        $parts['title'] = 'Sklospeciál — Skleněné dveře na míru bez showroomu';
        unset($parts['tagline'], $parts['site']);
    } elseif (is_page('realizace')) {
        $parts['title'] = 'Realizace — skleněné dveře v interiérech';
        unset($parts['tagline']);
    } elseif (is_page('recenze')) {
        $stats = function_exists('sklo_recenze_stats') ? sklo_recenze_stats() : ['avg' => 0, 'count' => 0];
        $parts['title'] = 'Recenze zákazníků — průměr ' . number_format_i18n((float) $stats['avg'], 1) . ' z 5';
        unset($parts['tagline']);
    }
    return $parts;
});

add_action('wp_head', function (): void {
    $produkt = sklo_request_produkt();
    if ($produkt !== null) {
        $name = function_exists('sklo_public_product_text')
            ? sklo_public_product_text((string) ($produkt['name'] ?? ''))
            : (string) ($produkt['name'] ?? '');
        $code = (string) ($produkt['code'] ?? '');
        $raw_desc = (string) ($produkt['description'] ?? '');
        $desc = function_exists('sklo_public_product_text')
            ? sklo_public_product_text($raw_desc)
            : $raw_desc;
        $desc = wp_strip_all_tags($desc);
        if (mb_strlen($desc) > 160) {
            $desc = rtrim(mb_substr($desc, 0, 157)) . '…';
        }
        if ($desc === '') {
            $desc = ($name !== '' ? $name . ' — ' : '')
                . 'orientační cena a volby v katalogu Sklospeciál. Zaměření online, výroba na míru.';
            if ($code !== '') {
                $desc = $code . ': ' . $desc;
            }
        }
        echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
        return;
    }
    if (is_front_page()) {
        $desc = 'Zaměř otvor, pošli fotky a navrhni si dveře online. Kyvné, posuvné i celoskleněné. Nabídku připravíme podle tvých rozměrů.';
    } elseif (is_page('realizace')) {
        $desc = 'Galerie hotových skleněných dveří — kyvné, posuvné i celoskleněné v bytech a kancelářích. Podívej se na realizace a navrhni si vlastní.';
    } elseif (is_page('recenze')) {
        $stats = function_exists('sklo_recenze_stats') ? sklo_recenze_stats() : ['avg' => 0, 'count' => 0];
        $desc = 'Recenze zákazníků Sklospeciál — průměr '
            . number_format_i18n((float) $stats['avg'], 1)
            . ' z 5 z '
            . (int) $stats['count']
            . ' hodnocení. Skleněné dveře, sprchy a zábradlí na míru.';
    } else {
        return;
    }
    echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
}, 1);

/**
 * Canonical + Open Graph + Twitter for key public surfaces.
 */
add_action('wp_head', static function (): void {
    $url = '';
    $title = wp_get_document_title();
    $desc = '';
    $image = '';
    $type = 'website';
    $emit = false;

    $produkt = sklo_request_produkt();
    if ($produkt !== null) {
        $emit = true;
        $code = (string) ($produkt['code'] ?? '');
        $base = (string) get_permalink();
        $url = $code !== '' && function_exists('sklo_produkt_detail_url')
            ? sklo_produkt_detail_url($code, $base)
            : $base;
        $name = function_exists('sklo_public_product_text')
            ? sklo_public_product_text((string) ($produkt['name'] ?? ''))
            : (string) ($produkt['name'] ?? '');
        $raw_desc = (string) ($produkt['description'] ?? '');
        $desc = function_exists('sklo_public_product_text')
            ? sklo_public_product_text($raw_desc)
            : $raw_desc;
        $desc = wp_strip_all_tags($desc);
        if ($desc === '') {
            $desc = $name !== ''
                ? $name . ' — katalog Sklospeciál, výroba na míru.'
                : 'Produkt z katalogu Sklospeciál.';
        }
        $img = (string) ($produkt['image'] ?? $produkt['img'] ?? $produkt['thumb'] ?? '');
        if ($img !== '') {
            $image = sklo_public_asset_url($img);
        }
        $type = 'product';
    } elseif (is_front_page()) {
        $emit = true;
        $url = home_url('/');
        $desc = 'Zaměř otvor, pošli fotky a navrhni si dveře online. Kyvné, posuvné i celoskleněné.';
    } elseif (is_page()) {
        $landing = sklo_seo_resolve_landing();
        if ($landing && ($landing['kind'] ?? '') === 'city') {
            $emit = true;
            $copy = sklo_seo_city_copy($landing['category'], $landing['city']);
            $url = (string) get_permalink();
            $desc = $copy['seo_desc'];
            $title = $copy['seo_title'];
        } elseif ($landing && ($landing['kind'] ?? '') === 'type') {
            $emit = true;
            $url = (string) get_permalink();
            $desc = (string) ($landing['type']['seo_desc'] ?? '');
            $title = (string) ($landing['type']['seo_title'] ?? $title);
        } else {
            $slug = get_post_field('post_name', get_queried_object_id()) ?: '';
            $cat = sklo_katalog_for_slug($slug);
            if ($cat) {
                $emit = true;
                $url = (string) get_permalink();
                $desc = (string) ($cat['seo_desc'] ?? '');
                if (!empty($cat['seo_title'])) {
                    $title = (string) $cat['seo_title'];
                }
            } else {
                $guide = sklo_pruvodce_for_page();
                if ($guide) {
                    $emit = true;
                    $url = (string) get_permalink();
                    $desc = (string) ($guide['seo_desc'] ?? '');
                    if (!empty($guide['seo_title'])) {
                        $title = (string) $guide['seo_title'];
                    }
                }
            }
        }
    }

    if (!$emit) {
        return;
    }

    if ($url === '') {
        $url = (string) get_permalink();
    }
    if ($desc === '') {
        $desc = (string) get_bloginfo('description');
    }
    if (mb_strlen($desc) > 200) {
        $desc = rtrim(mb_substr($desc, 0, 197)) . '…';
    }
    if ($image === '') {
        $og_file = get_template_directory() . '/assets/img/og-default.jpg';
        if (is_readable($og_file)) {
            $image = get_template_directory_uri() . '/assets/img/og-default.jpg';
        } else {
            $logo_id = (int) get_theme_mod('custom_logo');
            if ($logo_id > 0) {
                $src = wp_get_attachment_image_url($logo_id, 'full');
                if (is_string($src) && $src !== '') {
                    $image = $src;
                }
            }
        }
    }

    // Canonical: let Rank Math own the tag when active; we override via filter below.
    if (!defined('RANK_MATH_VERSION')) {
        echo '<link rel="canonical" href="' . esc_url($url) . '" />' . "\n";
    }
    echo '<meta property="og:locale" content="cs_CZ" />' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($desc) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
    echo '<meta property="og:site_name" content="Sklospeciál" />' . "\n";
    if ($image !== '') {
        echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($desc) . '" />' . "\n";
    if ($image !== '') {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
    }
}, 2);

/**
 * Canonical URL for homepage + product ?kod=.
 */
function sklo_canonical_url(?string $fallback = null): string
{
    $produkt = sklo_request_produkt();
    if ($produkt !== null) {
        $code = (string) ($produkt['code'] ?? '');
        $base = (string) get_permalink();
        if ($code !== '' && function_exists('sklo_produkt_detail_url')) {
            return sklo_produkt_detail_url($code, $base);
        }
        return $base !== '' ? $base : (string) $fallback;
    }
    if (is_front_page()) {
        return home_url('/');
    }
    return $fallback !== null && $fallback !== '' ? $fallback : (string) get_permalink();
}

add_filter('get_canonical_url', static function ($canonical) {
    return sklo_canonical_url(is_string($canonical) ? $canonical : null);
}, 20);

add_filter('rank_math/frontend/canonical', static function ($canonical) {
    return sklo_canonical_url(is_string($canonical) ? $canonical : null);
}, 99);

remove_action('wp_head', 'rel_canonical');

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

    // Chat widget (API-hosted). Version via WP only — do not put ?ver= in the URL
    // (snippet #7 used to double-append ver=1.8.0&ver=1.7.0).
    $api = rtrim(sklo_api_base(), '/');
    wp_enqueue_script(
        'sklo-chat-widget',
        $api . '/public/chat-widget.js',
        [],
        '1.9.0',
        true
    );
    wp_add_inline_script(
        'sklo-chat-widget',
        'window.SKLO_API_BASE=' . wp_json_encode($api) . ';'
        . 'window.SKLO_CONFIGURATOR_URL=' . wp_json_encode($api . '/public/konfigurator.html') . ';',
        'before'
    );
    wp_add_inline_style(
        'sklo-main',
        '.sklo-chat-panel[hidden]{display:none!important}'
        . '.sklo-chat-panel:not(.is-open){display:none!important}'
        . '.sklo-chat-panel.is-open{display:flex!important}'
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
        ['/sklenene-pricky/', 'Příčky'],
        ['/francouzske-balkony/', 'Balkony'],
        ['/realizace/', 'Realizace'],
        ['/recenze/', 'Recenze'],
        ['/navod-na-zamereni/', 'Návod'],
        ['/pruvodce/', 'Průvodce'],
        ['/o-nas/', 'O nás'],
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
 * Document title for katalog + SEO landings.
 */
add_filter('document_title_parts', function (array $parts): array {
    if (!is_page()) {
        return $parts;
    }
    if (sklo_request_produkt() !== null) {
        return $parts;
    }
    $landing = sklo_seo_resolve_landing();
    if ($landing) {
        if (($landing['kind'] ?? '') === 'city') {
            $copy = sklo_seo_city_copy($landing['category'], $landing['city']);
            $parts['title'] = $copy['seo_title'];
            unset($parts['tagline'], $parts['site']);
            return $parts;
        }
        if (($landing['kind'] ?? '') === 'type') {
            $parts['title'] = (string) ($landing['type']['seo_title'] ?? $parts['title']);
            unset($parts['tagline'], $parts['site']);
            return $parts;
        }
    }
    $slug = get_post_field('post_name', get_queried_object_id()) ?: '';
    $cat  = sklo_katalog_for_slug($slug);
    if ($cat && !empty($cat['seo_title'])) {
        $parts['title'] = (string) $cat['seo_title'];
        unset($parts['tagline'], $parts['site']);
        return $parts;
    }
    $guide = sklo_pruvodce_for_page();
    if ($guide && !empty($guide['seo_title'])) {
        $parts['title'] = (string) $guide['seo_title'];
        unset($parts['tagline'], $parts['site']);
    }
    return $parts;
}, 20);

add_action('wp_head', function (): void {
    if (!is_page() || sklo_request_produkt() !== null) {
        return;
    }
    $desc = '';
    $landing = sklo_seo_resolve_landing();
    if ($landing && ($landing['kind'] ?? '') === 'city') {
        $copy = sklo_seo_city_copy($landing['category'], $landing['city']);
        $desc = $copy['seo_desc'];
    } elseif ($landing && ($landing['kind'] ?? '') === 'type') {
        $desc = (string) ($landing['type']['seo_desc'] ?? '');
    } else {
        $slug = get_post_field('post_name', get_queried_object_id()) ?: '';
        $cat  = sklo_katalog_for_slug($slug);
        if ($cat && !empty($cat['seo_desc'])) {
            $desc = (string) $cat['seo_desc'];
        } else {
            $guide = sklo_pruvodce_for_page();
            if ($guide && !empty($guide['seo_desc'])) {
                $desc = (string) $guide['seo_desc'];
            }
        }
    }
    if ($desc === '') {
        return;
    }
    echo '<meta name="description" content="' . esc_attr($desc) . '" />' . "\n";
}, 1);

function sklo_is_current(string $path): bool
{
    $req = trailingslashit(wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/');
    return $req === trailingslashit($path);
}

/**
 * REST: product inquiry from /poptávka/ (FluentSMTP via wp_mail).
 * Anti-spam: honeypot + IP rate limit (5/hour) + optional Turnstile.
 */
add_action('rest_api_init', static function (): void {
    register_rest_route('sklo/v1', '/poptavka', [
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => static function (WP_REST_Request $req) {
            // Honeypot — bots fill hidden "website" / company fields
            $honeypot = trim((string) $req->get_param('website'));
            if ($honeypot === '') {
                $honeypot = trim((string) $req->get_param('company_url'));
            }
            if ($honeypot !== '') {
                return new WP_Error('spam', 'Odeslání odmítnuto.', ['status' => 400]);
            }

            $ip = sklo_client_ip();
            $rl_key = 'sklo_poptavka_rl_' . md5($ip);
            $rl_count = (int) get_transient($rl_key);
            if ($rl_count >= 5) {
                return new WP_Error(
                    'rate_limited',
                    'Příliš mnoho poptávek. Zkus to prosím za hodinu.',
                    ['status' => 429]
                );
            }

            if (sklo_turnstile_configured()) {
                $token = trim((string) $req->get_param('cf_turnstile_response'));
                if ($token === '') {
                    $token = trim((string) $req->get_param('turnstile_token'));
                }
                if ($token === '') {
                    return new WP_Error(
                        'bad_captcha',
                        'Chybí ověření anti-spam (Turnstile).',
                        ['status' => 400]
                    );
                }
                $verify = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'timeout' => 8,
                    'body' => [
                        'secret' => sklo_turnstile_secret_key(),
                        'response' => $token,
                        'remoteip' => $ip,
                    ],
                ]);
                if (is_wp_error($verify)) {
                    return new WP_Error(
                        'captcha_fail',
                        'Ověření anti-spam selhalo. Zkus to znovu.',
                        ['status' => 400]
                    );
                }
                $body = json_decode((string) wp_remote_retrieve_body($verify), true);
                if (!is_array($body) || empty($body['success'])) {
                    return new WP_Error(
                        'captcha_fail',
                        'Ověření anti-spam selhalo. Zkus to znovu.',
                        ['status' => 400]
                    );
                }
            }

            $jmeno = trim((string) $req->get_param('jmeno'));
            $email = sanitize_email((string) $req->get_param('email'));
            $telefon = trim((string) $req->get_param('telefon'));
            $adresa = trim((string) $req->get_param('adresa'));
            $doprava = sanitize_key((string) $req->get_param('doprava'));
            $montaz = sanitize_key((string) $req->get_param('montaz'));
            $zamereni_raw = $req->get_param('zamereni');
            $zamereni = false;
            if (is_bool($zamereni_raw)) {
                $zamereni = $zamereni_raw;
            } elseif (is_string($zamereni_raw)) {
                $zamereni = in_array(strtolower($zamereni_raw), ['1', 'ano', 'true', 'on', 'yes'], true);
            } elseif (is_numeric($zamereni_raw)) {
                $zamereni = ((int) $zamereni_raw) === 1;
            }
            $zaruka_raw = $req->get_param('prodlouzena_zaruka');
            $prodlouzena_zaruka = false;
            if (is_bool($zaruka_raw)) {
                $prodlouzena_zaruka = $zaruka_raw;
            } elseif (is_string($zaruka_raw)) {
                $prodlouzena_zaruka = in_array(strtolower($zaruka_raw), ['1', 'ano', 'true', 'on', 'yes'], true);
            } elseif (is_numeric($zaruka_raw)) {
                $prodlouzena_zaruka = ((int) $zaruka_raw) === 1;
            }
            $gdpr_raw = $req->get_param('gdpr_souhlas');
            $gdpr_souhlas = false;
            if (is_bool($gdpr_raw)) {
                $gdpr_souhlas = $gdpr_raw;
            } elseif (is_string($gdpr_raw)) {
                $gdpr_souhlas = in_array(strtolower($gdpr_raw), ['1', 'ano', 'true', 'on', 'yes'], true);
            } elseif (is_numeric($gdpr_raw)) {
                $gdpr_souhlas = ((int) $gdpr_raw) === 1;
            }
            $poznamka = trim((string) $req->get_param('poznamka'));
            $order = $req->get_param('order');
            if (!is_array($order)) {
                $order = [];
            }

            if (mb_strlen($jmeno) < 2) {
                return new WP_Error('bad_jmeno', 'Chybí jméno', ['status' => 400]);
            }
            if (!is_email($email)) {
                return new WP_Error('bad_email', 'Neplatný e-mail', ['status' => 400]);
            }
            if (mb_strlen($telefon) < 5) {
                return new WP_Error('bad_telefon', 'Neplatný telefon', ['status' => 400]);
            }
            if (!$gdpr_souhlas) {
                return new WP_Error(
                    'bad_gdpr',
                    'Je nutný souhlas se zpracováním osobních údajů',
                    ['status' => 400]
                );
            }

            $allowed_doprava = ['ne', 'ano', 'vlastni', 'nase_auto', 'prepravni'];
            $allowed_montaz = ['ne', 'ano', 'konzultace'];
            if (!in_array($doprava, $allowed_doprava, true)) {
                $doprava = 'ne';
            }
            if (!in_array($montaz, $allowed_montaz, true)) {
                $montaz = 'ne';
            }

            $code = sanitize_text_field((string) ($order['code'] ?? ''));
            $name = sanitize_text_field((string) ($order['name'] ?? ''));
            $qty = max(1, min(99, (int) ($order['qty'] ?? 1)));
            $unit = (int) ($order['unitTotal'] ?? $order['basePrice'] ?? 0);
            $base = (int) ($order['basePrice'] ?? 0);
            $surcharges = (int) ($order['surcharges'] ?? 0);
            $total = $unit * $qty;

            $sel_lines = [];
            if (!empty($order['selections']) && is_array($order['selections'])) {
                foreach ($order['selections'] as $s) {
                    if (!is_array($s)) {
                        continue;
                    }
                    $lab = sanitize_text_field((string) ($s['label'] ?? ''));
                    $val = sanitize_text_field((string) ($s['value'] ?? ''));
                    $sur = (int) ($s['surcharge'] ?? 0);
                    if ($lab === '' || $val === '') {
                        continue;
                    }
                    $line = $lab . ': ' . $val;
                    if ($sur > 0) {
                        $line .= ' (+ ' . number_format($sur, 0, ',', "\u{00a0}") . ' Kč)';
                    }
                    $sel_lines[] = $line;
                }
            }

            $doprava_map = [
                'ne' => 'Zatím nerozhodnuto',
                'ano' => 'Ano, chci návrh dopravy',
                'vlastni' => 'Vlastní doprava (klient)',
                'nase_auto' => 'Naše doprava firemním autem',
                'prepravni' => 'Přepravní služba',
            ];
            $doprava_label = $doprava_map[$doprava] ?? 'Zatím nerozhodnuto';
            $montaz_map = [
                'ne' => 'Ne',
                'ano' => 'Ano, chci montáž',
                'konzultace' => 'Jen konzultaci',
            ];
            $montaz_label = $montaz_map[$montaz] ?? 'Ne';
            $zamereni_label = $zamereni ? 'Ano — volitelné zaměření' : 'Ne';
            $zaruka_amount = 0;
            if ($prodlouzena_zaruka && $base > 0) {
                $zaruka_amount = function_exists('sklo_warranty_surcharge_czk')
                    ? sklo_warranty_surcharge_czk($base)
                    : (int) round($base * 0.10);
            }
            $zaruka_label = $prodlouzena_zaruka
                ? (
                    'Ano — zájem o +1 rok (10 % ceny výrobku bez dopravy/montáže'
                    . ($zaruka_amount > 0
                        ? ', orientačně ' . number_format($zaruka_amount, 0, ',', "\u{00a0}") . ' Kč'
                        : '')
                    . ')'
                )
                : 'Ne';

            $body_lines = [
                'Nová poptávka z katalogu (sklospecial.eu)',
                '',
                'Jméno: ' . $jmeno,
                'E-mail: ' . $email,
                'Telefon: ' . $telefon,
                'Adresa / PSČ město: ' . ($adresa !== '' ? $adresa : '—'),
                'Doprava: ' . $doprava_label,
                'Montáž: ' . $montaz_label,
                'Zaměření: ' . $zamereni_label,
                'Prodloužená záruka: ' . $zaruka_label,
                'GDPR souhlas: Ano',
                '',
                'Produkt: ' . ($name !== '' ? $name : 'Obecná poptávka') . ($code !== '' ? ' (' . $code . ')' : ''),
                'Počet: ' . $qty,
            ];
            if ($code !== '' || $unit > 0 || $sel_lines !== []) {
                $body_lines[] = 'Základ: ' . number_format($base, 0, ',', "\u{00a0}") . ' Kč';
                $body_lines[] = 'Doplatky: ' . number_format($surcharges, 0, ',', "\u{00a0}") . ' Kč';
                $body_lines[] = 'Cena / ks: ' . number_format($unit, 0, ',', "\u{00a0}") . ' Kč';
                $body_lines[] = 'Orientační celkem: ' . number_format($total, 0, ',', "\u{00a0}") . ' Kč';
                $body_lines[] = '';
                $body_lines[] = 'Volby:';
                if ($sel_lines === []) {
                    $body_lines[] = '—';
                } else {
                    foreach ($sel_lines as $ln) {
                        $body_lines[] = '- ' . $ln;
                    }
                }
            }
            $body_lines[] = '';
            $body_lines[] = 'Poznámka:';
            $body_lines[] = $poznamka !== '' ? $poznamka : '—';

            $to = (string) get_option('admin_email');
            $subject = sprintf('Poptávka %s — %s', $code !== '' ? $code : 'obecná', $jmeno);
            $headers = [
                'Content-Type: text/plain; charset=UTF-8',
                'Reply-To: ' . $jmeno . ' <' . $email . '>',
            ];
            $sent = wp_mail($to, $subject, implode("\n", $body_lines), $headers);

            // Confirmation to client (best-effort)
            if ($sent && is_email($email)) {
                $confirm = [
                    'Dobrý den, ' . $jmeno . ',',
                    '',
                    'děkujeme za poptávku' . ($code !== '' ? ' na ' . $code : '') . '.',
                    'Ozveme se obratem, nejpozději do 1 pracovního dne — s konkrétní nabídkou.',
                    '',
                    'Sklospeciál',
                    'https://sklospecial.cz',
                ];
                wp_mail(
                    $email,
                    'Potvrzení poptávky — Sklospeciál',
                    implode("\n", $confirm),
                    ['Content-Type: text/plain; charset=UTF-8']
                );
            }

            if (!$sent) {
                return new WP_Error('mail_fail', 'E-mail se nepodařilo odeslat', ['status' => 500]);
            }

            set_transient($rl_key, $rl_count + 1, HOUR_IN_SECONDS);

            return [
                'success' => true,
            ];
        },
    ]);
});

/** Enqueue Turnstile on poptávka when keys exist. */
add_action('wp_enqueue_scripts', static function (): void {
    if (!is_page('poptavka') || !sklo_turnstile_configured()) {
        return;
    }
    wp_enqueue_script(
        'cf-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        [],
        null,
        true
    );
    wp_add_inline_script(
        'sklo-main',
        'window.SKLO_TURNSTILE_SITEKEY=' . wp_json_encode(sklo_turnstile_site_key()) . ';',
        'before'
    );
}, 20);