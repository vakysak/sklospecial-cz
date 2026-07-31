<?php
/**
 * Sklospeciál theme
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('SKLO_THEME_VER', '1.6.3');

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

/**
 * REST: product inquiry from /poptavka/ (FluentSMTP via wp_mail).
 */
add_action('rest_api_init', static function (): void {
    register_rest_route('sklo/v1', '/poptavka', [
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => static function (WP_REST_Request $req) {
            $jmeno = trim((string) $req->get_param('jmeno'));
            $email = sanitize_email((string) $req->get_param('email'));
            $telefon = trim((string) $req->get_param('telefon'));
            $adresa = trim((string) $req->get_param('adresa'));
            $doprava = sanitize_key((string) $req->get_param('doprava'));
            $montaz = sanitize_key((string) $req->get_param('montaz'));
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

            $allowed_doprava = ['ne', 'ano'];
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

            $doprava_label = $doprava === 'ano' ? 'Ano, chci návrh dopravy' : 'Ne';
            $montaz_map = [
                'ne' => 'Ne',
                'ano' => 'Ano, chci montáž',
                'konzultace' => 'Jen konzultaci',
            ];
            $montaz_label = $montaz_map[$montaz] ?? 'Ne';

            $body_lines = [
                'Nová poptávka z katalogu (sklospecial.cz)',
                '',
                'Jméno: ' . $jmeno,
                'E-mail: ' . $email,
                'Telefon: ' . $telefon,
                'Adresa / PSČ město: ' . ($adresa !== '' ? $adresa : '—'),
                'Doprava: ' . $doprava_label,
                'Montáž: ' . $montaz_label,
                '',
                'Produkt: ' . $name . ' (' . $code . ')',
                'Počet: ' . $qty,
                'Základ: ' . number_format($base, 0, ',', "\u{00a0}") . ' Kč',
                'Doplatky: ' . number_format($surcharges, 0, ',', "\u{00a0}") . ' Kč',
                'Cena / ks: ' . number_format($unit, 0, ',', "\u{00a0}") . ' Kč',
                'Orientační celkem: ' . number_format($total, 0, ',', "\u{00a0}") . ' Kč',
                '',
                'Volby:',
            ];
            if ($sel_lines === []) {
                $body_lines[] = '—';
            } else {
                foreach ($sel_lines as $ln) {
                    $body_lines[] = '- ' . $ln;
                }
            }
            $body_lines[] = '';
            $body_lines[] = 'Poznámka:';
            $body_lines[] = $poznamka !== '' ? $poznamka : '—';

            $to = (string) get_option('admin_email');
            $subject = sprintf('Poptávka %s — %s', $code !== '' ? $code : 'katalog', $jmeno);
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
                    'Ozveme se s konkrétní nabídkou.',
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

            return [
                'success' => true,
            ];
        },
    ]);
});
