<?php
/**
 * Template Name: Katalog kování sprchy
 * Description: Listing + detail kování pro sprchové kouty (JSON), v podkategoriích.
 */

declare(strict_types=1);

get_header();

$path = get_template_directory() . '/assets/data/kovani-sprchy.json';
$kovani = [];
if (is_readable($path)) {
    $raw = file_get_contents($path);
    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if (is_array($decoded)) {
        $kovani = $decoded;
    }
}

/** @var array<string, string> $subcat_order slug → Czech label (supplier order) */
$subcat_order = [
    'panty-pro-sprchove-dvere'                    => 'Panty pro sprchové dveře',
    'uchyty-pro-sprchove-zasteny-2'               => 'Upevnění pro sprchové zástěny',
    'kovani-pro-posuvne-dvere-sprchy'             => 'Posuvné kování pro sprchové dveře',
    'stabilizacni-tyce-pro-zasteny-2'             => 'Stabilizační tyče pro zástěny',
    'uchytky-a-madla-pro-sprchove-dvere'          => 'Úchytky a madla pro sprchové dveře',
    'tesnici-profily-a-doplnky-pro-sprchove-kouty' => 'Těsnící profily a doplňky pro sprchové kouty',
];

/** @var array<string, list<array<string, mixed>>> $by_sub */
$by_sub = [];
foreach ($kovani as $produkt) {
    if (!is_array($produkt)) {
        continue;
    }
    $slug = (string) ($produkt['category_slug'] ?? $produkt['subcat'] ?? '');
    if ($slug === '') {
        $slug = 'ostatni';
    }
    if (!isset($by_sub[$slug])) {
        $by_sub[$slug] = [];
    }
    $by_sub[$slug][] = $produkt;
}

$subcats = [];
foreach ($subcat_order as $slug => $label) {
    if (empty($by_sub[$slug])) {
        continue;
    }
    $thumb = '';
    foreach ($by_sub[$slug] as $p) {
        $img = (string) ($p['image'] ?? '');
        if ($img === '' && !empty($p['images']) && is_array($p['images'])) {
            $img = (string) ($p['images'][0] ?? '');
        }
        if ($img !== '') {
            $thumb = $img;
            break;
        }
    }
    $subcats[] = [
        'slug'  => $slug,
        'label' => (string) ($by_sub[$slug][0]['subcategory'] ?? $label),
        'count' => count($by_sub[$slug]),
        'image' => $thumb,
    ];
}
foreach ($by_sub as $slug => $items) {
    if (isset($subcat_order[$slug])) {
        continue;
    }
    $subcats[] = [
        'slug'  => $slug,
        'label' => (string) ($items[0]['subcategory'] ?? $slug),
        'count' => count($items),
        'image' => (string) ($items[0]['image'] ?? ''),
    ];
}

$kategorie = 'Kování pro sprchové kouty';
$total = count($kovani);
$back_url = (string) get_permalink();
$initial = 24;

$kod = isset($_GET['kod']) ? sanitize_text_field(wp_unslash((string) $_GET['kod'])) : '';
$detail_product = null;
if ($kod !== '' && function_exists('sklo_produkt_by_code')) {
    $detail_product = sklo_produkt_by_code($kod);
}
$kod_missing = $kod !== '' && $detail_product === null;
?>

<article class="sklo-katalog sklo-katalog--kovani sklo-katalog--filtered<?php echo $detail_product ? ' sklo-katalog--detail' : ''; ?>">
  <?php if ($detail_product !== null) : ?>
    <?php sklo_render_produkt_detail($detail_product, $back_url); ?>
  <?php else : ?>
  <header class="sklo-katalog__hero">
    <div class="sklo-wrap">
      <?php if ($kod_missing) : ?>
        <div class="sklo-katalog__notice" role="status">
          <p>Produkt <strong><?php echo esc_html($kod); ?></strong> jsme v nabídce kování nenašli. Vyber jiný z nabídky níže, nebo <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">pošli poptávku</a>.</p>
        </div>
      <?php endif; ?>
      <p class="sklo-eyebrow">Příslušenství</p>
      <h1>Kování pro sprchové kouty</h1>
      <p class="sklo-katalog__lead">
        Kování pro sprchové kouty v podkategoriích — panty, úchyty, posuvné systémy, madla a těsnění.
        Otevři detail, zvol variantu a přidej do nezávazné poptávky.
      </p>
      <p class="sklo-katalog__back">
        <a class="sklo-link" href="<?php echo esc_url(home_url('/sprchove-kouty/')); ?>">← Zpět na sprchové kouty</a>
      </p>
    </div>
  </header>

  <?php if ($subcats !== []) : ?>
    <section class="sklo-cat-cards-wrap" aria-label="Podkategorie kování">
      <div class="sklo-wrap">
        <div class="sklo-cat-cards sklo-cat-cards--kovani">
          <?php foreach ($subcats as $sc) : ?>
            <button
              type="button"
              class="sklo-cat-card"
              data-cat-filter="<?php echo esc_attr($sc['slug']); ?>"
              aria-pressed="false"
            >
              <?php if ($sc['image'] !== '') : ?>
                <img
                  class="sklo-cat-card__image"
                  src="<?php echo esc_url($sc['image']); ?>"
                  alt="<?php echo esc_attr($sc['label']); ?>"
                  width="400"
                  height="160"
                  loading="lazy"
                  decoding="async"
                >
              <?php else : ?>
                <span class="sklo-cat-card__image sklo-cat-card__image--empty" aria-hidden="true"></span>
              <?php endif; ?>
              <span class="sklo-cat-card__overlay">
                <span class="sklo-cat-card__title"><?php echo esc_html($sc['label']); ?></span>
                <span class="sklo-cat-card__price"><?php echo esc_html((string) $sc['count']); ?> položek</span>
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
          <?php foreach ($subcats as $sc) : ?>
            <button
              type="button"
              class="sklo-cat-nav__chip"
              data-cat-filter="<?php echo esc_attr($sc['slug']); ?>"
              aria-pressed="false"
            >
              <?php echo esc_html($sc['label']); ?>
              <span class="sklo-cat-nav__count"><?php echo esc_html((string) $sc['count']); ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </nav>
  <?php endif; ?>

  <section
    class="sklo-section sklo-produkty"
    aria-label="Kování pro sprchové kouty"
    data-sklo-produkty
    data-initial="<?php echo esc_attr((string) $initial); ?>"
  >
    <div class="sklo-wrap">
      <header class="sklo-section__head sklo-section__head--center">
        <p class="sklo-eyebrow">Produkty</p>
        <h2>Nabídka kování pro sprchy</h2>
        <p><span data-sklo-produkty-count><?php echo esc_html((string) $total); ?></span> položek · ceny orientační, finální nabídka podle konkrétní sestavy</p>
      </header>

      <?php if ($total === 0) : ?>
        <p class="sklo-katalog__notice">Data kování se nepodařilo načíst. Zkus obnovit stránku, nebo <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">pošli poptávku</a>.</p>
      <?php else : ?>
        <?php
        $shown = 0;
        foreach ($subcats as $sc) :
            $slug = $sc['slug'];
            $items = $by_sub[$slug] ?? [];
            if ($items === []) {
                continue;
            }
            ?>
          <div class="sklo-katalog-sub" data-cat-section="<?php echo esc_attr($slug); ?>" id="<?php echo esc_attr($slug); ?>">
            <h3 class="sklo-katalog-sub__title"><?php echo esc_html($sc['label']); ?></h3>
            <div class="sklo-produkty__grid">
              <?php foreach ($items as $produkt) :
                  $code  = (string) ($produkt['code'] ?? '');
                  $name  = (string) ($produkt['name'] ?? '');
                  $price_raw = $produkt['price'] ?? 0;
                  $price_int = is_numeric($price_raw) ? (int) $price_raw : 0;
                  $image = (string) ($produkt['image'] ?? '');
                  if ($image === '' && !empty($produkt['images']) && is_array($produkt['images'])) {
                      $image = (string) ($produkt['images'][0] ?? '');
                  }
                  if ($code === '' || $name === '') {
                      continue;
                  }
                  $detail_url = function_exists('sklo_produkt_detail_url')
                      ? sklo_produkt_detail_url($code, $back_url)
                      : $back_url . (str_contains($back_url, '?') ? '&' : '?') . 'kod=' . rawurlencode($code);
                  $price_label = $price_int > 0 && function_exists('sklo_format_cena_od')
                      ? sklo_format_cena_od($price_int)
                      : ($price_int > 0 ? 'od ' . number_format($price_int, 0, ',', "\u{00a0}") . ' Kč' : '');
                  $cena_txt = $price_int > 0 && function_exists('sklo_format_cena')
                      ? sklo_format_cena($price_int)
                      : '';
                  $collapsed = $shown >= $initial;
                  $shown++;
                  ?>
                <article
                  class="sklo-produkty__item<?php echo $collapsed ? ' is-collapsed' : ''; ?>"
                  data-category="<?php echo esc_attr($slug); ?>"
                  <?php echo $collapsed ? 'hidden' : ''; ?>
                >
                  <a class="sklo-produkty__link" href="<?php echo esc_url($detail_url); ?>">
                    <?php if ($image !== '') : ?>
                      <span class="sklo-produkty__media sklo-produkty__media--ext">
                        <img
                          src="<?php echo esc_url($image); ?>"
                          alt="<?php echo esc_attr($name); ?>"
                          loading="lazy"
                          decoding="async"
                          width="300"
                          height="200"
                        >
                      </span>
                    <?php else : ?>
                      <div class="sklo-produkty__media sklo-produkty__media--empty" role="img" aria-label="Fotografie není k dispozici"></div>
                    <?php endif; ?>
                    <div class="sklo-produkty__body">
                      <h4 class="sklo-produkty__name"><?php echo esc_html($name); ?></h4>
                      <?php if ($price_label !== '') : ?>
                        <p class="sklo-produkty__price"><?php echo esc_html($price_label); ?></p>
                      <?php endif; ?>
                      <p class="sklo-produkty__more-link">Detail a volby</p>
                    </div>
                  </a>
                  <?php if (shortcode_exists('poptavka_tlacitko')) : ?>
                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode attrs escaped; markup from shortcode.
                    echo do_shortcode(sprintf(
                        '[poptavka_tlacitko id="%s" nazev="%s" kategorie="%s" cena="%s" fotka="%s"]',
                        esc_attr($code),
                        esc_attr($name),
                        esc_attr($kategorie . ' · ' . $sc['label']),
                        esc_attr($cena_txt),
                        esc_attr($image)
                    ));
                    ?>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if ($total > $initial) : ?>
          <div class="sklo-produkty__more">
            <button type="button" class="sklo-btn sklo-btn--ghost" data-sklo-produkty-more>
              Zobrazit další (<?php echo esc_html((string) ($total - $initial)); ?>)
            </button>
          </div>
        <?php endif; ?>

        <p class="sklo-katalog__notice sklo-katalog__notice--muted">
          Orientační ceny včetně DPH; doplatky za varianty uvidíš v detailu.
        </p>
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

<?php
get_footer();
