<?php
/**
 * Template Name: Katalog kování stříšky
 * Description: Statický listing kování Süd-Metall pro skleněné stříšky (JSON).
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
$has_poptavka = shortcode_exists('poptavka_tlacitko');
$total = count($kovani);
?>

<article class="sklo-katalog sklo-katalog--kovani">
  <header class="sklo-katalog__hero">
    <div class="sklo-wrap">
      <p class="sklo-eyebrow">Příslušenství</p>
      <h1>Kování pro skleněné stříšky</h1>
      <p class="sklo-katalog__lead">
        Profesionální kování Süd-Metall pro montáž skleněných stříšek.
        Vyber produkt a přidej ho do nezávazné poptávky.
      </p>
      <p class="sklo-katalog__back">
        <a class="sklo-link" href="<?php echo esc_url(home_url('/strisky/')); ?>">← Zpět na stříšky</a>
      </p>
    </div>
  </header>

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
              $price = (string) ($produkt['price'] ?? '');
              $image = (string) ($produkt['image'] ?? '');
              $url   = (string) ($produkt['url'] ?? '');
              if ($code === '' || $name === '') {
                  continue;
              }
              ?>
            <article class="sklo-produkty__item">
              <?php if ($url !== '') : ?>
                <a class="sklo-produkty__link" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">
              <?php endif; ?>
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
                <h3 class="sklo-produkty__name"><?php echo esc_html($name); ?></h3>
                <?php if ($price !== '') : ?>
                  <p class="sklo-produkty__price"><?php echo esc_html($price); ?></p>
                <?php endif; ?>
                <?php if ($url !== '') : ?>
                  <p class="sklo-produkty__more-link">Detail u dodavatele</p>
                <?php endif; ?>
              </div>
              <?php if ($url !== '') : ?>
                </a>
              <?php endif; ?>
              <?php
              if ($has_poptavka) {
                  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode attrs escaped; markup from shortcode.
                  echo do_shortcode(sprintf(
                      '[poptavka_tlacitko id="%s" nazev="%s" kategorie="%s" cena="%s" fotka="%s"]',
                      esc_attr($code),
                      esc_attr($name),
                      esc_attr($kategorie),
                      esc_attr($price),
                      esc_attr($image)
                  ));
              }
              ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</article>

<?php
get_footer();
