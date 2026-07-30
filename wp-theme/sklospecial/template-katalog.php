<?php
/**
 * Template Name: Katalog
 * Description: Kategorie katalogu skleněných dveří (sekce + ceny z katalog-data.php).
 */

declare(strict_types=1);

get_header();

$cfg  = sklo_konfigurator_url();
$slug = get_post_field('post_name', get_queried_object_id()) ?: '';
$cat  = sklo_katalog_for_slug($slug);

if ($cat === null) {
    // Fallback: plain page content
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

$uploads = (string) ($cat['uploads'] ?? content_url('uploads/2026/07'));
$note    = sklo_cena_note();
?>

<article class="sklo-katalog">
  <header class="sklo-katalog__hero">
    <div class="sklo-wrap">
      <?php if (!empty($cat['eyebrow'])) : ?>
        <p class="sklo-eyebrow"><?php echo esc_html((string) $cat['eyebrow']); ?></p>
      <?php endif; ?>
      <h1><?php echo esc_html((string) $cat['title']); ?></h1>
      <?php if (!empty($cat['lead'])) : ?>
        <p class="sklo-katalog__lead"><?php echo esc_html((string) $cat['lead']); ?></p>
      <?php endif; ?>
      <?php if (!empty($cat['parent'])) : ?>
        <p class="sklo-katalog__back">
          <a class="sklo-link" href="<?php echo esc_url(home_url('/sklenene-dvere/')); ?>">← Zpět na katalog</a>
        </p>
      <?php endif; ?>
    </div>
  </header>

  <?php if (!empty($cat['sections']) && is_array($cat['sections'])) : ?>
    <nav class="sklo-katalog__toc" aria-label="Sekce">
      <div class="sklo-wrap">
        <ul>
          <?php foreach ($cat['sections'] as $sec) :
              $sid = (string) ($sec['id'] ?? '');
              $st  = (string) ($sec['title'] ?? '');
              if ($sid === '' || $st === '') {
                  continue;
              }
              ?>
            <li><a href="#<?php echo esc_attr($sid); ?>"><?php echo esc_html($st); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </nav>

    <?php foreach ($cat['sections'] as $sec) :
        $sid = (string) ($sec['id'] ?? '');
        $img = !empty($sec['image']) ? trailingslashit($uploads) . $sec['image'] : '';
        ?>
      <section class="sklo-katalog-sec" id="<?php echo esc_attr($sid); ?>">
        <div class="sklo-wrap sklo-katalog-sec__grid<?php echo $img ? '' : ' sklo-katalog-sec__grid--solo'; ?>">
          <div class="sklo-katalog-sec__copy">
            <h2><?php echo esc_html((string) ($sec['title'] ?? '')); ?></h2>
            <?php if (!empty($sec['lead'])) : ?>
              <p><?php echo esc_html((string) $sec['lead']); ?></p>
            <?php endif; ?>

            <?php if (!empty($sec['choosable']) && is_array($sec['choosable'])) : ?>
              <h3 class="sklo-katalog-sec__sub">Co si vybereš</h3>
              <ul class="sklo-katalog-list">
                <?php foreach ($sec['choosable'] as $item) : ?>
                  <li><?php echo esc_html((string) $item); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if (!empty($sec['bestsellers']) && is_array($sec['bestsellers'])) : ?>
              <h3 class="sklo-katalog-sec__sub">Nejčastější volby</h3>
              <ul class="sklo-katalog-list sklo-katalog-list--inline">
                <?php foreach ($sec['bestsellers'] as $item) : ?>
                  <li><?php echo esc_html((string) $item); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if (!empty($sec['prices']) && is_array($sec['prices'])) : ?>
              <div class="sklo-katalog-prices">
                <?php foreach ($sec['prices'] as $price) :
                    $label = (string) ($price['label'] ?? '');
                    if (!empty($price['custom'])) {
                        $display = (string) $price['custom'];
                    } elseif (isset($price['od']) && $price['od'] !== null) {
                        $display = sklo_format_cena_od((int) $price['od']);
                    } else {
                        $display = 'cena na dotaz';
                    }
                    ?>
                  <div class="sklo-katalog-price">
                    <span class="sklo-katalog-price__label"><?php echo esc_html($label); ?></span>
                    <span class="sklo-katalog-price__val"><?php echo esc_html($display); ?></span>
                  </div>
                <?php endforeach; ?>
                <p class="sklo-katalog-prices__note"><?php echo esc_html($note); ?></p>
              </div>
            <?php endif; ?>

            <?php if (!empty($sec['patterns']) && is_array($sec['patterns'])) : ?>
              <dl class="sklo-katalog-patterns">
                <?php foreach ($sec['patterns'] as $pat) : ?>
                  <div class="sklo-katalog-patterns__row">
                    <dt><?php echo esc_html((string) ($pat['code'] ?? '')); ?></dt>
                    <dd><?php echo esc_html((string) ($pat['cs'] ?? '')); ?></dd>
                  </div>
                <?php endforeach; ?>
              </dl>
            <?php endif; ?>
          </div>

          <?php if ($img) : ?>
            <figure class="sklo-katalog-sec__media">
              <img
                src="<?php echo esc_url($img); ?>"
                alt="<?php echo esc_attr((string) ($sec['title'] ?? '')); ?>"
                loading="lazy"
                decoding="async"
                width="626"
                height="417"
              >
            </figure>
          <?php endif; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>

  <section class="sklo-section sklo-cta-band">
    <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
      <div>
        <h2>Chceš konkrétní nabídku?</h2>
        <p>Zaměř otvor, pošli fotky a ve studiu si složíš dveře. My z toho připravíme cenu podle rozměrů a provedení.</p>
      </div>
      <div class="sklo-cta-band__actions">
        <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
        <a class="sklo-link" href="<?php echo esc_url(home_url('/kontakt/')); ?>">Nebo napiš</a>
      </div>
    </div>
  </section>
</article>

<?php get_footer(); ?>
