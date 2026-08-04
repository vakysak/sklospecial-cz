<?php
/**
 * Template Name: SEO Landing
 * Description: City and type SEO landings (honest remote model).
 */

declare(strict_types=1);

get_header();

$landing = sklo_seo_resolve_landing();

if ($landing === null) {
    while (have_posts()) {
        the_post();
        echo '<article class="sklo-page"><div class="sklo-wrap sklo-page__inner">';
        echo '<header class="sklo-page__head"><h1>' . esc_html(get_the_title()) . '</h1></header>';
        echo '<div class="sklo-prose">';
        the_content();
        echo '</div></div></article>';
    }
    get_footer();
    return;
}

$kind = (string) $landing['kind'];
$faq = [];
$related = [];
$cross_sell = [];
$process_links = [];
$link_blocks = [];
$bullets = [];
$price_from = null;
$katalog_slug = '';
$h1 = '';
$eyebrow = 'Sklospeciál';
$intro = '';
$service = '';
$local_note = '';
$why_title = '';
$why_local = '';
$sibling_cities = [];
$parent_url = '';
$parent_label = '';
$crumbs = [['/', 'Domů']];

if ($kind === 'city') {
    /** @var array<string, mixed> $category */
    $category = $landing['category'];
    /** @var array<string, mixed> $city */
    $city = $landing['city'];
    $copy = sklo_seo_city_copy($category, $city);
    $h1 = $copy['h1'];
    $intro = $copy['intro'];
    $bullets = $copy['bullets'];
    $service = $copy['service'];
    $local_note = (string) ($copy['local_note'] ?? '');
    $why_title = (string) ($copy['why_title'] ?? '');
    $why_local = (string) ($copy['why_local'] ?? '');
    $sibling_cities = (array) ($copy['sibling_cities'] ?? []);
    $faq = $copy['faq'];
    $price_from = $copy['price_from'];
    $katalog_slug = (string) ($category['katalog_slug'] ?? ($category['slug'] ?? ''));
    $related = (array) ($copy['related_extra'] ?? ($category['related'] ?? []));
    $cross_sell = (array) ($copy['cross_sell'] ?? ($category['cross_sell'] ?? []));
    $process_links = (array) ($copy['process_links'] ?? []);
    $link_blocks = (array) ($copy['link_blocks'] ?? []);
    $parent_url = (string) ($category['pillar_path'] ?? '/');
    $parent_label = (string) ($category['name'] ?? 'Katalog');
    $eyebrow = 'Dodávka · ' . $city['name'];
    $crumbs[] = [$parent_url, $parent_label];
    $crumbs[] = ['', $h1];
} else {
    /** @var array<string, mixed> $type */
    $type = $landing['type'];
    $h1 = (string) $type['h1'];
    $eyebrow = (string) ($type['eyebrow'] ?? 'Katalog');
    $intro = (string) $type['intro'];
    $bullets = (array) ($type['bullets'] ?? []);
    $service = 'Pracujeme online — zaměříš sám, nebo přijedeme zaměřit. Dodáváme a montujeme po celé ČR. Nemáme síť showroomů; výroba jde z dílny k tobě.';
    $local_note = '';
    $why_title = '';
    $why_local = '';
    $sibling_cities = [];
    $faq = (array) ($type['faq'] ?? []);
    $related = (array) ($type['related'] ?? []);
    $parent_url = (string) ($type['parent_url'] ?? '/');
    $parent_label = (string) ($type['parent_label'] ?? 'Katalog');
    $katalog_slug = (string) ($type['katalog_slug'] ?? '');
    if ($katalog_slug !== '' && function_exists('sklo_katalog_for_slug')) {
        $cat = sklo_katalog_for_slug($katalog_slug);
        if ($cat && !empty($cat['price_from'])) {
            $price_from = (string) $cat['price_from'];
        }
    }
    $pillar = (string) ($type['pillar_url'] ?? $parent_url);
    $crumbs[] = [$pillar, $parent_label];
    if ($parent_url !== $pillar) {
        $crumbs[] = [$parent_url, $parent_label];
    }
    $crumbs[] = ['', $h1];
}
?>

<article class="sklo-seo-landing">
  <header class="sklo-katalog__hero sklo-seo-landing__hero">
    <div class="sklo-wrap">
      <nav class="sklo-seo-landing__crumbs" aria-label="Drobečková navigace">
        <?php foreach ($crumbs as $i => [$curl, $clabel]) :
            if ($i > 0) {
                echo ' <span aria-hidden="true">/</span> ';
            }
            if ($curl !== '') {
                printf('<a href="%s">%s</a>', esc_url(home_url($curl)), esc_html($clabel));
            } else {
                echo '<span>' . esc_html($clabel) . '</span>';
            }
        endforeach; ?>
      </nav>
      <p class="sklo-eyebrow"><?php echo esc_html($eyebrow); ?></p>
      <h1><?php echo esc_html($h1); ?></h1>
      <p class="sklo-katalog__lead"><?php echo esc_html($intro); ?></p>
      <?php if ($price_from) : ?>
        <p class="sklo-katalog__from"><?php echo esc_html($price_from); ?></p>
        <?php if (function_exists('sklo_render_cena_priklad')) : ?>
          <?php sklo_render_cena_priklad($katalog_slug !== '' ? $katalog_slug : null); ?>
        <?php endif; ?>
        <p class="sklo-seo-landing__price-note"><?php echo esc_html(sklo_cena_note()); ?></p>
      <?php endif; ?>
      <?php if (function_exists('sklo_render_recenze_rating_link')) : ?>
        <div class="sklo-katalog__rating">
          <?php sklo_render_recenze_rating_link('sklo-rating-link--inline'); ?>
        </div>
      <?php endif; ?>
      <div class="sklo-katalog__hero-actions">
        <?php if (function_exists('sklo_render_studio_coming_actions')) : ?>
          <?php sklo_render_studio_coming_actions('sklo-katalog__var-c', false, true); ?>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <section class="sklo-section">
    <div class="sklo-wrap sklo-seo-landing__grid">
      <div>
        <h2>Co dostaneš</h2>
        <ul class="sklo-seo-landing__bullets">
          <?php foreach ($bullets as $b) : ?>
            <li><?php echo esc_html((string) $b); ?></li>
          <?php endforeach; ?>
        </ul>
        <p class="sklo-seo-landing__service"><?php echo esc_html($service); ?></p>
        <?php if ($local_note !== '' && $kind === 'city') : ?>
          <p class="sklo-seo-landing__local"><?php echo esc_html($local_note); ?></p>
        <?php endif; ?>
        <p>
          <a class="sklo-link" href="<?php echo esc_url(home_url($parent_url)); ?>">← <?php echo esc_html($parent_label); ?></a>
          ·
          <a class="sklo-link" href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Návod na zaměření</a>
          ·
          <a class="sklo-link" href="<?php echo esc_url(home_url('/doprava/')); ?>">Doprava a montáž</a>
        </p>
      </div>
      <aside class="sklo-seo-landing__aside">
        <h2>Související</h2>
        <ul class="sklo-seo-landing__related">
          <?php foreach ($related as $rel) :
              $rurl = is_array($rel) ? (string) ($rel[0] ?? '') : '';
              $rlabel = is_array($rel) ? (string) ($rel[1] ?? '') : '';
              if ($rurl === '' || $rlabel === '') {
                  continue;
              }
              ?>
            <li><a href="<?php echo esc_url(home_url($rurl)); ?>"><?php echo esc_html($rlabel); ?></a></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($kind === 'city' && $cross_sell) : ?>
          <h2 class="sklo-seo-landing__aside-sub">Další sklo</h2>
          <ul class="sklo-seo-landing__related">
            <?php foreach ($cross_sell as $rel) :
                $rurl = is_array($rel) ? (string) ($rel[0] ?? '') : '';
                $rlabel = is_array($rel) ? (string) ($rel[1] ?? '') : '';
                if ($rurl === '' || $rlabel === '') {
                    continue;
                }
                ?>
              <li><a href="<?php echo esc_url(home_url($rurl)); ?>"><?php echo esc_html($rlabel); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <?php if ($kind === 'city' && $process_links) : ?>
          <h2 class="sklo-seo-landing__aside-sub">Postup</h2>
          <ul class="sklo-seo-landing__related">
            <?php foreach ($process_links as $rel) :
                $rurl = is_array($rel) ? (string) ($rel[0] ?? '') : '';
                $rlabel = is_array($rel) ? (string) ($rel[1] ?? '') : '';
                if ($rurl === '' || $rlabel === '') {
                    continue;
                }
                ?>
              <li><a href="<?php echo esc_url(home_url($rurl)); ?>"><?php echo esc_html($rlabel); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <?php if ($kind === 'city' && $sibling_cities) : ?>
          <h2 class="sklo-seo-landing__aside-sub">Další města</h2>
          <ul class="sklo-seo-landing__related">
            <?php foreach ($sibling_cities as $rel) :
                $rurl = is_array($rel) ? (string) ($rel[0] ?? '') : '';
                $rlabel = is_array($rel) ? (string) ($rel[1] ?? '') : '';
                if ($rurl === '' || $rlabel === '') {
                    continue;
                }
                ?>
              <li><a href="<?php echo esc_url(home_url($rurl)); ?>"><?php echo esc_html($rlabel); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </aside>
    </div>
  </section>

  <?php if ($kind === 'city' && function_exists('sklo_render_seo_city_photos')) : ?>
    <?php sklo_render_seo_city_photos($category); ?>
  <?php endif; ?>

  <?php if ($kind === 'city' && function_exists('sklo_render_seo_city_category_showcases')) : ?>
    <?php sklo_render_seo_city_category_showcases($category); ?>
  <?php endif; ?>

  <?php if ($kind === 'city' && $link_blocks) : ?>
  <section class="sklo-section sklo-seo-landing__links" aria-label="Související stránky">
    <div class="sklo-wrap sklo-seo-landing__links-inner">
      <header class="sklo-section__head">
        <p class="sklo-eyebrow">Orientace na webu</p>
        <h2>Co ještě řeší lidé u stejné zakázky</h2>
        <p class="sklo-seo-landing__links-lead">Rychlé odkazy na varianty, montáž a další města — ať nemusíš hledat v menu.</p>
      </header>
      <div class="sklo-seo-landing__link-grid">
      <?php foreach ($link_blocks as $block) :
          $btitle = (string) ($block['title'] ?? '');
          $paras = (array) ($block['paragraphs'] ?? []);
          if ($btitle === '' || $paras === []) {
              continue;
          }
          ?>
        <div class="sklo-seo-landing__link-block">
          <h3><?php echo esc_html($btitle); ?></h3>
          <?php foreach ($paras as $para) : ?>
            <p><?php echo wp_kses((string) $para, [
                'a' => [
                    'href' => true,
                    'class' => true,
                    'rel' => true,
                    'target' => true,
                ],
            ]); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($kind === 'city' && $why_local !== '') : ?>
  <section class="sklo-section sklo-seo-landing__why">
    <div class="sklo-wrap sklo-seo-landing__why-inner">
      <header class="sklo-section__head">
        <p class="sklo-eyebrow">Lokálně a férově · z Havířova</p>
        <h2><?php echo esc_html($why_title !== '' ? $why_title : 'Jak to u vás řešíme'); ?></h2>
      </header>
      <p class="sklo-seo-landing__why-text"><?php echo esc_html($why_local); ?></p>
      <p class="sklo-seo-landing__why-links">
        <a class="sklo-link" href="<?php echo esc_url(home_url('/o-nas/')); ?>">O nás a dílně</a>
        ·
        <a class="sklo-link" href="<?php echo esc_url(home_url('/kontakt/')); ?>">Kontakt</a>
        ·
        <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a>
      </p>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($faq) : ?>
  <section class="sklo-section sklo-faq sklo-seo-landing__faq" id="faq">
    <div class="sklo-wrap sklo-faq__grid">
      <header class="sklo-section__head">
        <p class="sklo-eyebrow">FAQ</p>
        <h2>Časté otázky</h2>
      </header>
      <div class="sklo-faq__list">
        <?php foreach ($faq as $i => $item) :
            $q = (string) ($item['q'] ?? '');
            $a = (string) ($item['a'] ?? '');
            if ($q === '' || $a === '') {
                continue;
            }
            ?>
          <details class="sklo-faq__item"<?php echo $i === 0 ? ' open' : ''; ?>>
            <summary><?php echo esc_html($q); ?></summary>
            <p><?php echo esc_html($a); ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="sklo-section sklo-cta-band">
    <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
      <?php if (function_exists('sklo_render_studio_coming_block')) : ?>
        <?php sklo_render_studio_coming_block('sklo-var-c--band'); ?>
      <?php else : ?>
        <div>
          <h2>Konfigurátor připravujeme.</h2>
          <p>Teď nejjednodušší cesta: WhatsApp, nebo nezávazná poptávka s rozměry a fotkami.</p>
        </div>
        <div class="sklo-cta-band__actions">
          <a class="sklo-btn" href="https://wa.me/420736134604" target="_blank" rel="noopener noreferrer">WhatsApp</a>
          <a class="sklo-btn sklo-btn--ghost" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a>
        </div>
      <?php endif; ?>
    </div>
  </section>
</article>

<?php
// FAQPage + BreadcrumbList (not fake LocalBusiness per city)
$schema_faq = [];
foreach ($faq as $item) {
    $q = (string) ($item['q'] ?? '');
    $a = (string) ($item['a'] ?? '');
    if ($q === '' || $a === '') {
        continue;
    }
    $schema_faq[] = [
        '@type' => 'Question',
        'name' => $q,
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $a,
        ],
    ];
}
$schema_crumbs = [];
$pos = 1;
foreach ($crumbs as [$curl, $clabel]) {
    $schema_crumbs[] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'name' => $clabel,
        'item' => $curl === '' ? get_permalink() : home_url($curl),
    ];
}
$graph = [
    [
        '@type' => 'WebPage',
        'name' => $h1,
        'description' => $intro,
        'url' => get_permalink(),
        'isPartOf' => [
            '@type' => 'WebSite',
            'name' => 'Sklospeciál',
            'url' => home_url('/'),
        ],
    ],
    [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $schema_crumbs,
    ],
];
if ($schema_faq) {
    $graph[] = [
        '@type' => 'FAQPage',
        'mainEntity' => $schema_faq,
    ];
}
$graph[] = [
    '@type' => 'Organization',
    'name' => 'Sklospeciál',
    'url' => home_url('/'),
    'areaServed' => [
        '@type' => 'Country',
        'name' => 'Česko',
    ],
];
echo '<script type="application/ld+json">' . wp_json_encode([
    '@context' => 'https://schema.org',
    '@graph' => $graph,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";

get_footer();
