<?php
/**
 * Template Name: Průvodce
 * Description: SEO guide hub and article pages under /pruvodce/.
 */

declare(strict_types=1);

get_header();

$is_hub = is_page('pruvodce');
$current_slug = get_post_field('post_name', get_queried_object_id()) ?: '';

$crumbs = [['/', 'Domů']];
if ($is_hub) {
    $crumbs[] = ['', 'Průvodce'];
} else {
    $crumbs[] = ['/pruvodce/', 'Průvodce'];
    $crumbs[] = ['', get_the_title()];
}

$related = [];
foreach (sklo_pruvodce_hub_list() as $g) {
    if (($g['slug'] ?? '') === $current_slug) {
        continue;
    }
    $related[] = $g;
}
?>

<article class="sklo-page sklo-pruvodce<?php echo $is_hub ? ' sklo-pruvodce--hub' : ''; ?>">
  <div class="sklo-wrap sklo-page__inner">
    <?php while (have_posts()) : the_post(); ?>
      <nav class="sklo-pruvodce__crumbs" aria-label="Drobečková navigace">
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

      <header class="sklo-page__head">
        <p class="sklo-eyebrow">Průvodce</p>
        <h1><?php the_title(); ?></h1>
        <?php if ($is_hub) : ?>
          <p class="sklo-pruvodce__lead">Krátké články, které ti pomůžou vybrat typ, odhadnout cenu a připravit zaměření — bez omáčky.</p>
        <?php endif; ?>
      </header>

      <?php if ($is_hub) : ?>
        <ul class="sklo-pruvodce__list">
          <?php foreach (sklo_pruvodce_hub_list() as $g) : ?>
            <li>
              <a class="sklo-pruvodce__card" href="<?php echo esc_url(home_url((string) $g['path'])); ?>">
                <strong><?php echo esc_html((string) $g['title']); ?></strong>
                <span><?php echo esc_html((string) $g['blurb']); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php if (trim((string) get_the_content()) !== '') : ?>
        <div class="sklo-prose sklo-pruvodce__body">
          <?php the_content(); ?>
        </div>
      <?php endif; ?>

      <?php if (!$is_hub && $related) : ?>
        <aside class="sklo-pruvodce__related" aria-label="Další průvodce">
          <h2>Další průvodce</h2>
          <ul>
            <?php foreach (array_slice($related, 0, 4) as $g) : ?>
              <li>
                <a href="<?php echo esc_url(home_url((string) $g['path'])); ?>">
                  <?php echo esc_html((string) $g['title']); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </aside>
      <?php endif; ?>
    <?php endwhile; ?>
  </div>

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
echo '<script type="application/ld+json">' . wp_json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebPage',
            'name' => get_the_title(),
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
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";

get_footer();
