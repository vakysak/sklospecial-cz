<?php
/**
 * Template Name: Katalog
 * Description: Kategorie katalogu (sekce + produkty z katalog-data / CSV).
 */

declare(strict_types=1);

get_header();

$slug = get_post_field('post_name', get_queried_object_id()) ?: '';
$cat  = sklo_katalog_for_slug($slug);

if ($cat === null) {
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
$is_hub  = !empty($cat['hub']) && !empty($cat['children']);
$children = is_array($cat['children'] ?? null) ? $cat['children'] : [];
$parent   = (string) ($cat['parent'] ?? '');
$price_from = $cat['price_from'] ?? null;

$raw_sections = is_array($cat['sections'] ?? null) ? $cat['sections'] : [];
$sections     = [];
foreach ($raw_sections as $sec) {
    if (!is_array($sec)) {
        continue;
    }
    $sid = (string) ($sec['id'] ?? '');
    $st  = (string) ($sec['title'] ?? '');
    if ($sid === '' || $st === '') {
        continue;
    }
    $sections[] = $sec;
}
$has_section_nav = count($sections) > 1;

$kod = isset($_GET['kod']) ? sanitize_text_field(wp_unslash((string) $_GET['kod'])) : '';
$detail_product = $kod !== '' ? sklo_produkt_by_code($kod) : null;
$kod_missing = $kod !== '' && $detail_product === null;
$back_url = (string) get_permalink();
?>

<article class="sklo-katalog<?php echo $is_hub ? ' sklo-katalog--hub' : ''; ?><?php echo $has_section_nav ? ' sklo-katalog--filtered' : ''; ?><?php echo $detail_product ? ' sklo-katalog--detail' : ''; ?>">
  <?php if ($detail_product !== null) : ?>
    <?php sklo_render_produkt_detail($detail_product, $back_url); ?>
  <?php else : ?>
  <header class="sklo-katalog__hero">
    <div class="sklo-wrap">
      <?php if ($kod_missing) : ?>
        <div class="sklo-katalog__notice" role="status">
          <p>Produkt <strong><?php echo esc_html($kod); ?></strong> jsme v katalogu nenašli. Vyber jiný z nabídky níže, nebo <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">pošli poptávku</a>.</p>
        </div>
      <?php endif; ?>
      <?php if (!empty($cat['eyebrow'])) : ?>
        <p class="sklo-eyebrow"><?php echo esc_html((string) $cat['eyebrow']); ?></p>
      <?php endif; ?>
      <h1><?php echo esc_html((string) $cat['title']); ?></h1>
      <?php if (!empty($cat['lead'])) : ?>
        <p class="sklo-katalog__lead"><?php echo esc_html((string) $cat['lead']); ?></p>
      <?php endif; ?>
      <?php if ($price_from) : ?>
        <p class="sklo-katalog__from"><?php echo esc_html((string) $price_from); ?></p>
        <?php if (function_exists('sklo_render_cena_priklad')) : ?>
          <?php sklo_render_cena_priklad($slug); ?>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (function_exists('sklo_render_recenze_rating_link')) : ?>
        <div class="sklo-katalog__rating">
          <?php sklo_render_recenze_rating_link('sklo-rating-link--inline'); ?>
        </div>
      <?php endif; ?>
      <?php if ($parent !== '') : ?>
        <p class="sklo-katalog__back">
          <a class="sklo-link" href="<?php echo esc_url(sklo_katalog_parent_url($cat)); ?>">← Zpět na katalog</a>
        </p>
      <?php endif; ?>
    </div>
  </header>

  <?php sklo_render_katalog_seo_intro($cat); ?>

  <?php if ($slug === 'strisky') : ?>
    <section class="sklo-section sklo-kovani-cross" aria-label="Kování pro skleněné stříšky">
      <div class="sklo-wrap">
        <div class="sklo-kovani-cross__inner">
          <div class="sklo-kovani-cross__copy">
            <p class="sklo-eyebrow">Samostatná kategorie</p>
            <h2>Kování pro skleněné stříšky</h2>
            <p>Kování Süd-Metall pro montáž — MOTIVO, SEASONS, SWORD, CANO a další. Oddělená nabídka od hotových stříšek.</p>
          </div>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/kovani-strisky/')); ?>">Prohlédnout kování</a>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($slug === 'zabradli') : ?>
    <section class="sklo-section sklo-kovani-cross" aria-label="Kování pro zábradlí">
      <div class="sklo-wrap">
        <div class="sklo-kovani-cross__inner">
          <div class="sklo-kovani-cross__copy">
            <p class="sklo-eyebrow">Samostatná kategorie</p>
            <h2>Kování pro zábradlí</h2>
            <p>Držáky, sloupky, trubky, spojky a další prvky pro výrobu zábradlí v podkategoriích. Oddělená nabídka od hotových sestav.</p>
          </div>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/kovani-zabradli/')); ?>">Prohlédnout kování</a>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($slug === 'sprchove-kouty') : ?>
    <section class="sklo-section sklo-kovani-cross" aria-label="Kování pro sprchové kouty">
      <div class="sklo-wrap">
        <div class="sklo-kovani-cross__inner">
          <div class="sklo-kovani-cross__copy">
            <p class="sklo-eyebrow">Samostatná kategorie</p>
            <h2>Kování pro sprchové kouty</h2>
            <p>Panty, úchyty, posuvné systémy, madla a těsnící profily pro sprchové zástěny. Oddělená nabídka od hotových koutů.</p>
          </div>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/kovani-sprchy/')); ?>">Prohlédnout kování</a>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($slug === 'posuvne') : ?>
    <section class="sklo-section sklo-kovani-cross" aria-label="Kování pro posuvné skleněné dveře">
      <div class="sklo-wrap">
        <div class="sklo-kovani-cross__inner">
          <div class="sklo-kovani-cross__copy">
            <p class="sklo-eyebrow">Samostatná kategorie</p>
            <h2>Kování pro posuvné skleněné dveře</h2>
            <p>Posuvy, mušle, úchytky a zámky pro skleněné posuvné dveře. Oddělená nabídka od hotových dveřních sestav.</p>
          </div>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/kovani-posuvne/')); ?>">Prohlédnout kování</a>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($slug === 'otevirane') : ?>
    <section class="sklo-section sklo-kovani-cross" aria-label="Kování pro otevírané skleněné dveře">
      <div class="sklo-wrap">
        <div class="sklo-kovani-cross__inner">
          <div class="sklo-kovani-cross__copy">
            <p class="sklo-eyebrow">Samostatná kategorie</p>
            <h2>Kování pro otevírané skleněné dveře</h2>
            <p>Kliky, panty, madla, závěsy a doplňky pro otevírané skleněné dveře. Oddělená nabídka od hotových dveřních sestav.</p>
          </div>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/kovani-otevirane/')); ?>">Prohlédnout kování</a>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($slug === 'sklenene-dvere') : ?>
    <section class="sklo-section sklo-kovani-cross" aria-label="Kování pro posuvné skleněné dveře">
      <div class="sklo-wrap">
        <div class="sklo-kovani-cross__inner">
          <div class="sklo-kovani-cross__copy">
            <p class="sklo-eyebrow">Samostatná kategorie</p>
            <h2>Kování pro posuvné skleněné dveře</h2>
            <p>Posuvy, mušle, úchytky a zámky pro skleněné posuvné dveře. Oddělená nabídka od hotových dveřních sestav.</p>
          </div>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/kovani-posuvne/')); ?>">Prohlédnout kování</a>
        </div>
      </div>
    </section>
    <section class="sklo-section sklo-kovani-cross" aria-label="Kování pro otevírané skleněné dveře">
      <div class="sklo-wrap">
        <div class="sklo-kovani-cross__inner">
          <div class="sklo-kovani-cross__copy">
            <p class="sklo-eyebrow">Samostatná kategorie</p>
            <h2>Kování pro otevírané skleněné dveře</h2>
            <p>Kliky, panty, madla, závěsy a doplňky pro otevírané skleněné dveře. Oddělená nabídka od hotových dveřních sestav.</p>
          </div>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/kovani-otevirane/')); ?>">Prohlédnout kování</a>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($is_hub) : ?>
    <section class="sklo-section sklo-katalog-hub">
      <div class="sklo-wrap">
        <header class="sklo-section__head sklo-section__head--center">
          <p class="sklo-eyebrow">Kategorie</p>
          <h2>Vyber typ</h2>
          <p>Orientační ceny najdeš u konkrétních kategorií — finální nabídka vždy podle rozměrů.</p>
        </header>
        <div class="sklo-katalog-hub__grid">
          <?php foreach ($children as $child) :
              $cslug  = (string) ($child['slug'] ?? '');
              $ctitle = (string) ($child['title'] ?? '');
              $ctext  = (string) ($child['text'] ?? '');
              $cprice = $child['price'] ?? null;
              $craw   = (string) ($child['image'] ?? '');
              $cimg   = $craw === '' ? '' : (str_starts_with($craw, 'http') ? $craw : trailingslashit($uploads) . $craw);
              $calt   = $ctitle !== '' ? $ctitle . ' — skleněné dveře Sklospeciál' : 'Skleněné dveře Sklospeciál';
              $curl   = $parent !== '' || $slug === 'sklenene-dvere'
                  ? home_url('/' . $slug . '/' . $cslug . '/')
                  : home_url('/' . $cslug . '/');
              if ($slug === 'sklenene-dvere') {
                  $curl = home_url('/sklenene-dvere/' . $cslug . '/');
              }
              ?>
            <a class="sklo-katalog-card" href="<?php echo esc_url($curl); ?>">
              <?php if ($cimg) : ?>
                <img class="sklo-katalog-card__image" src="<?php echo esc_url($cimg); ?>" alt="<?php echo esc_attr($calt); ?>" width="626" height="417" loading="lazy" decoding="async">
              <?php endif; ?>
              <div class="sklo-katalog-card__body">
                <h3><?php echo esc_html($ctitle); ?></h3>
                <p><?php echo esc_html($ctext); ?></p>
                <?php if ($cprice) : ?>
                  <span class="sklo-katalog-card__price"><?php echo esc_html((string) $cprice); ?></span>
                <?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($has_section_nav) : ?>
    <section class="sklo-cat-cards-wrap" aria-label="Podkategorie">
      <div class="sklo-wrap">
        <div class="sklo-cat-cards">
          <?php foreach ($sections as $sec) :
              $sid = (string) ($sec['id'] ?? '');
              $st  = (string) ($sec['title'] ?? '');
              $sec_price = $sec['price'] ?? null;

              $sec_img = '';
              if (!empty($sec['image'])) {
                  $raw = (string) $sec['image'];
                  $sec_img = str_starts_with($raw, 'http') ? $raw : trailingslashit($uploads) . $raw;
              }
              if ($sec_img === '') {
                  $sec_img = sklo_katalog_section_image($slug, $sid);
              }
              ?>
            <button
              type="button"
              class="sklo-cat-card"
              data-cat-filter="<?php echo esc_attr($sid); ?>"
              aria-pressed="false"
            >
              <?php if ($sec_img !== '') : ?>
                <img
                  class="sklo-cat-card__image"
                  src="<?php echo esc_url($sec_img); ?>"
                  alt="<?php echo esc_attr($st . ' — Sklospeciál'); ?>"
                  width="400"
                  height="160"
                  loading="eager"
                  decoding="async"
                  fetchpriority="high"
                >
              <?php else : ?>
                <span class="sklo-cat-card__image sklo-cat-card__image--empty" aria-hidden="true"></span>
              <?php endif; ?>
              <span class="sklo-cat-card__overlay">
                <span class="sklo-cat-card__title"><?php echo esc_html($st); ?></span>
                <?php if ($sec_price) : ?>
                  <span class="sklo-cat-card__price"><?php echo esc_html((string) $sec_price); ?></span>
                <?php endif; ?>
              </span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <nav class="sklo-cat-nav" data-cat-nav aria-label="Filtr podkategorií">
      <div class="sklo-wrap">
        <div class="sklo-cat-nav__track" role="tablist">
          <button type="button" class="sklo-cat-nav__chip is-active" data-cat-filter="" aria-pressed="true">
            Vše
          </button>
          <?php foreach ($sections as $sec) :
              $sid = (string) ($sec['id'] ?? '');
              $st  = (string) ($sec['title'] ?? '');
              ?>
            <button
              type="button"
              class="sklo-cat-nav__chip"
              data-cat-filter="<?php echo esc_attr($sid); ?>"
              aria-pressed="false"
            >
              <?php echo esc_html($st); ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </nav>

    <?php
    // Compact detail panels only when a section has richer content.
    $rich_sections = [];
    foreach ($sections as $sec) {
        if (
            !empty($sec['choosable'])
            || !empty($sec['bestsellers'])
            || !empty($sec['prices'])
            || !empty($sec['patterns'])
        ) {
            $rich_sections[] = $sec;
        }
    }
    ?>
    <?php if ($rich_sections !== []) : ?>
      <?php foreach ($rich_sections as $sec) :
          $sid = (string) ($sec['id'] ?? '');
          $sec_price = $sec['price'] ?? null;
          ?>
        <section class="sklo-katalog-sec sklo-katalog-sec--compact" id="<?php echo esc_attr($sid); ?>" data-cat-panel="<?php echo esc_attr($sid); ?>" hidden>
          <div class="sklo-wrap sklo-katalog-sec__grid sklo-katalog-sec__grid--solo">
            <div class="sklo-katalog-sec__copy">
              <h2><?php echo esc_html((string) ($sec['title'] ?? '')); ?></h2>
              <?php if (!empty($sec['lead'])) : ?>
                <p><?php echo esc_html((string) $sec['lead']); ?></p>
              <?php endif; ?>
              <?php if ($sec_price) : ?>
                <p class="sklo-katalog-sec__price"><?php echo esc_html((string) $sec_price); ?></p>
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
          </div>
        </section>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php elseif (count($sections) === 1) : ?>
    <?php
    $sec = $sections[0];
    $sid = (string) ($sec['id'] ?? '');
    $sec_price = $sec['price'] ?? null;
    $has_rich = !empty($sec['choosable']) || !empty($sec['bestsellers']) || !empty($sec['prices']) || !empty($sec['patterns']);
    ?>
    <?php if ($has_rich) : ?>
      <section class="sklo-katalog-sec sklo-katalog-sec--compact" id="<?php echo esc_attr($sid); ?>">
        <div class="sklo-wrap sklo-katalog-sec__grid sklo-katalog-sec__grid--solo">
          <div class="sklo-katalog-sec__copy">
            <h2><?php echo esc_html((string) ($sec['title'] ?? '')); ?></h2>
            <?php if (!empty($sec['lead'])) : ?>
              <p><?php echo esc_html((string) $sec['lead']); ?></p>
            <?php endif; ?>
            <?php if ($sec_price) : ?>
              <p class="sklo-katalog-sec__price"><?php echo esc_html((string) $sec_price); ?></p>
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
        </div>
      </section>
    <?php endif; ?>
  <?php endif; ?>

  <?php
  if (!empty($cat['show_products'])) {
      sklo_render_produkty_grid($slug, null, 24);
  }

  sklo_render_related_pillars($slug);

  if (sklo_seo_category_by_slug($slug)) {
      sklo_render_city_cloud($slug);
  }
  ?>

  <section class="sklo-section sklo-cta-band">
    <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
      <div>
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
  <?php endif; ?>
</article>

<div class="sklo-lightbox" data-lightbox hidden>
  <button type="button" class="sklo-lightbox__close" data-lightbox-close aria-label="Zavřít">×</button>
  <button type="button" class="sklo-lightbox__nav sklo-lightbox__nav--prev" data-lightbox-prev aria-label="Předchozí">‹</button>
  <figure class="sklo-lightbox__figure">
    <img src="" alt="" data-lightbox-img>
  </figure>
  <button type="button" class="sklo-lightbox__nav sklo-lightbox__nav--next" data-lightbox-next aria-label="Další">›</button>
</div>

<?php get_footer(); ?>
