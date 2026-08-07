<?php
/**
 * Template Name: Katalog kování stříšky
 * Description: Listing + detail kování Süd-Metall pro skleněné stříšky (JSON).
 */

declare(strict_types=1);

get_header();

$path = get_template_directory() . '/assets/data/kovani-strisky.json';
$kovani = [];
if (is_readable($path)) {
    $raw = file_get_contents($path);
    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if (is_array($decoded)) {
        $kovani = $decoded;
    }
}

$kategorie = 'Kování pro skleněné stříšky';
$total = count($kovani);
$back_url = (string) get_permalink();
$hub = function_exists('sklo_kovani_hub') ? sklo_kovani_hub('kovani-strisky') : null;
$img_kw = is_array($hub) ? (string) ($hub['keyword'] ?? 'kování stříšky') : 'kování stříšky';

$kod = isset($_GET['kod']) ? sanitize_text_field(wp_unslash((string) $_GET['kod'])) : '';
$detail_product = null;
if ($kod !== '' && function_exists('sklo_produkt_by_code')) {
    $detail_product = sklo_produkt_by_code($kod);
}
$kod_missing = $kod !== '' && $detail_product === null;
?>

<article class="sklo-katalog sklo-katalog--kovani<?php echo $detail_product ? ' sklo-katalog--detail' : ''; ?>">
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
      <h1><?php echo esc_html(is_array($hub) ? (string) ($hub['h1'] ?? $kategorie) : $kategorie); ?></h1>
      <p class="sklo-katalog__lead">
        Kování Süd-Metall pro montáž skleněných stříšek.
        Otevři detail, zvol variantu a přidej do nezávazné poptávky.
      </p>
      <p class="sklo-katalog__back">
        <a class="sklo-link" href="<?php echo esc_url(home_url('/strisky/')); ?>">← Zpět na stříšky</a>
      </p>
    </div>
  </header>

  <?php if (is_array($hub) && function_exists('sklo_render_katalog_seo_intro')) : ?>
    <?php sklo_render_katalog_seo_intro($hub); ?>
  <?php endif; ?>

  <section class="sklo-section sklo-produkty" aria-label="Kování pro stříšky">
    <div class="sklo-wrap">
      <header class="sklo-section__head sklo-section__head--center">
        <p class="sklo-eyebrow">Produkty</p>
        <h2>Nabídka kování</h2>
        <p><?php echo esc_html((string) $total); ?> položek · ceny orientační, finální nabídka podle konkrétní sestavy</p>
      </header>

      <?php if ($total === 0) : ?>
        <p class="sklo-katalog__notice">Data kování se nepodařilo načíst. Zkus obnovit stránku, nebo <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">pošli poptávku</a>.</p>
      <?php else : ?>
        <div class="sklo-produkty__grid">
          <?php foreach ($kovani as $produkt) :
              if (!is_array($produkt)) {
                  continue;
              }
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
              $alt = function_exists('sklo_kovani_img_alt')
                  ? sklo_kovani_img_alt($name, $img_kw)
                  : $name;
              ?>
            <article class="sklo-produkty__item">
              <a class="sklo-produkty__link" href="<?php echo esc_url($detail_url); ?>">
                <?php if ($image !== '') : ?>
                  <span class="sklo-produkty__media sklo-produkty__media--ext">
                    <img
                      src="<?php echo esc_url($image); ?>"
                      alt="<?php echo esc_attr($alt); ?>"
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
                  <h3 class="sklo-produkty__name"><?php echo esc_html($name); ?></h3>
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
                    esc_attr($kategorie),
                    esc_attr($cena_txt),
                    esc_attr($image)
                ));
                ?>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
        <p class="sklo-katalog__notice sklo-katalog__notice--muted">
          Výrobce kování: Süd-Metall. Orientační ceny včetně DPH; doplatky za varianty uvidíš v detailu.
        </p>
      <?php endif; ?>
    </div>
  </section>
  <?php if (function_exists('sklo_render_kovani_related')) : ?>
    <?php sklo_render_kovani_related('kovani-strisky'); ?>
  <?php endif; ?>
  <?php endif; ?>
</article>

<div class="sklo-lightbox" data-lightbox hidden>
  <button type="button" class="sklo-lightbox__close" data-lightbox-close aria-label="Zavřít">×</button>
  <button type="button" class="sklo-lightbox__nav sklo-lightbox__nav--prev" data-lightbox-prev aria-label="Předchozí">‹</button>
  <figure class="sklo-lightbox__figure">
    <img src="" alt="Náhled produktu" data-lightbox-img>
  </figure>
  <button type="button" class="sklo-lightbox__nav sklo-lightbox__nav--next" data-lightbox-next aria-label="Další">›</button>
</div>

<?php
get_footer();
