<?php
/**
 * Template Name: Mapa stránek
 * Description: HTML sitemap — pillars, type landings, city landings.
 */

declare(strict_types=1);

get_header();

$categories = sklo_seo_categories();
$cities = sklo_seo_cities();
$types = sklo_seo_type_landings();
?>

<article class="sklo-page sklo-sitemap">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Orientace</p>
      <h1>Mapa stránek</h1>
      <p>Přehled kategorií, typů a městských landings. City stránky popisují dodávku a montáž — ne fiktivní pobočky.</p>
    </header>

    <section class="sklo-sitemap__block">
      <h2>Kategorie</h2>
      <ul class="sklo-sitemap__list">
        <?php foreach ($categories as $slug => $cat) : ?>
          <li><a href="<?php echo esc_url(home_url((string) $cat['pillar_path'])); ?>"><?php echo esc_html((string) $cat['name']); ?></a></li>
          <?php if ($slug === 'strisky') : ?>
            <li><a href="<?php echo esc_url(home_url('/kovani-strisky/')); ?>">Kování stříšky</a></li>
          <?php endif; ?>
        <?php endforeach; ?>
        <li><a href="<?php echo esc_url(home_url('/realizace/')); ?>">Realizace</a></li>
        <li><a href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Návod na zaměření</a></li>
        <li><a href="<?php echo esc_url(home_url('/pruvodce/')); ?>">Průvodce</a></li>
        <li><a href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a></li>
        <li><a href="<?php echo esc_url(home_url('/kontakt/')); ?>">Kontakt</a></li>
        <li><a href="<?php echo esc_url(home_url('/doprava/')); ?>">Doprava a montáž</a></li>
        <li><a href="<?php echo esc_url(home_url('/platba/')); ?>">Platba</a></li>
        <li><a href="<?php echo esc_url(home_url('/doba-realizace/')); ?>">Doba realizace</a></li>
        <li><a href="<?php echo esc_url(home_url('/zaruka/')); ?>">Záruka a servis</a></li>
        <li><a href="<?php echo esc_url(home_url('/obchodni-podminky/')); ?>">Obchodní podmínky</a></li>
      </ul>
    </section>

    <section class="sklo-sitemap__block">
      <h2>Typy (vysoký záměr)</h2>
      <ul class="sklo-sitemap__list">
        <?php foreach ($types as $type) : ?>
          <li>
            <a href="<?php echo esc_url(home_url((string) $type['url_path'])); ?>">
              <?php echo esc_html((string) $type['h1']); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <?php foreach ($categories as $slug => $cat) : ?>
      <section class="sklo-sitemap__block">
        <h2><?php echo esc_html((string) $cat['name']); ?> — města</h2>
        <ul class="sklo-sitemap__list sklo-sitemap__list--cities">
          <?php foreach ($cities as $city) : ?>
            <li>
              <a href="<?php echo esc_url(home_url('/' . $slug . '/' . $city['slug'] . '/')); ?>">
                <?php echo esc_html((string) $cat['h1_product'] . ' ' . $city['name']); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  </div>
</article>

<?php get_footer(); ?>
