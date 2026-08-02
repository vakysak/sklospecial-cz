<?php
/**
 * Hub: Skleněné dveře (slug sklenene-dvere)
 */

declare(strict_types=1);

get_header();

$cfg = sklo_konfigurator_url();
$cat = sklo_katalog_for_slug('sklenene-dvere');
$uploads = (string) ($cat['uploads'] ?? content_url('uploads/2026/07'));
$children = is_array($cat['children'] ?? null) ? $cat['children'] : [];
?>

<article class="sklo-katalog sklo-katalog--hub">
  <header class="sklo-katalog__hero">
    <div class="sklo-wrap">
      <p class="sklo-eyebrow"><?php echo esc_html((string) ($cat['eyebrow'] ?? 'Katalog')); ?></p>
      <h1><?php echo esc_html((string) ($cat['title'] ?? 'Skleněné dveře na míru')); ?></h1>
      <p class="sklo-katalog__lead"><?php echo esc_html((string) ($cat['lead'] ?? '')); ?></p>
      <?php
      $hub_from = $cat['price_from'] ?? null;
      if (!$hub_from && function_exists('sklo_format_cena_od')) {
          $hub_from = sklo_format_cena_od(10100);
      }
      if ($hub_from) :
          ?>
        <p class="sklo-katalog__from"><?php echo esc_html((string) $hub_from); ?></p>
      <?php endif; ?>
      <?php if (function_exists('sklo_render_cena_priklad')) : ?>
        <?php sklo_render_cena_priklad('sklenene-dvere'); ?>
      <?php endif; ?>
      <?php if (function_exists('sklo_render_recenze_rating_link')) : ?>
        <div class="sklo-katalog__rating">
          <?php sklo_render_recenze_rating_link('sklo-rating-link--inline'); ?>
        </div>
      <?php endif; ?>
      <div class="sklo-katalog__hero-actions">
        <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
        <a class="sklo-btn sklo-btn--ghost-light" href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Jak zaměřit</a>
      </div>
    </div>
  </header>

  <section class="sklo-section sklo-katalog-hub">
    <div class="sklo-wrap">
      <header class="sklo-section__head sklo-section__head--center">
        <p class="sklo-eyebrow">Kategorie</p>
        <h2>Vyber typ dveří</h2>
        <p>Každý typ má jiné nároky na prostor a jiný výsledek. Orientační ceny najdeš u konkrétních kategorií — finální nabídka vždy podle rozměrů.</p>
      </header>

      <div class="sklo-katalog-hub__grid">
        <?php foreach ($children as $child) :
            $slug  = (string) ($child['slug'] ?? '');
            $title = (string) ($child['title'] ?? '');
            $text  = (string) ($child['text'] ?? '');
            $price = $child['price'] ?? null;
            $raw   = (string) ($child['image'] ?? '');
            $img   = $raw === '' ? '' : (str_starts_with($raw, 'http') ? $raw : trailingslashit($uploads) . $raw);
            $url   = home_url('/sklenene-dvere/' . $slug . '/');
            $alt   = $title !== '' ? $title . ' — skleněné dveře Sklospeciál' : 'Skleněné dveře Sklospeciál';
            ?>
          <a class="sklo-katalog-card" href="<?php echo esc_url($url); ?>">
            <?php if ($img) : ?>
              <img
                class="sklo-katalog-card__image"
                src="<?php echo esc_url($img); ?>"
                alt="<?php echo esc_attr($alt); ?>"
                width="626"
                height="417"
                loading="lazy"
                decoding="async"
              >
            <?php endif; ?>
            <div class="sklo-katalog-card__body">
              <h3><?php echo esc_html($title); ?></h3>
              <p><?php echo esc_html($text); ?></p>
              <?php if ($price) : ?>
                <span class="sklo-katalog-card__price"><?php echo esc_html((string) $price); ?></span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="sklo-section sklo-katalog-note">
    <div class="sklo-wrap sklo-katalog-note__inner">
      <p><?php echo esc_html(sklo_cena_note()); ?></p>
      <p>Celoskleněné dveře zůstávají součástí nabídky — najdeš je v katalogu níže i jako samostatnou stránku. Detaily doladíš ve studiu.</p>
    </div>
  </section>

  <?php sklo_render_city_cloud('sklenene-dvere'); ?>

  <section class="sklo-section sklo-cta-band">
    <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
      <div>
        <h2>Nejsi si jistý typem?</h2>
        <p>Pošli fotku otvoru nebo rovnou otevři studio. Poradíme, co sedí do prostoru — a připravíme nabídku.</p>
      </div>
      <div class="sklo-cta-band__actions">
        <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Otevřít studio</a>
        <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Nebo napiš</a>
        <?php if (function_exists('sklo_render_cta_sla')) : ?>
          <?php sklo_render_cta_sla(); ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</article>

<?php get_footer(); ?>
