<?php
/**
 * Template: Realizace — portfolio galerie
 */
get_header();

$cfg = sklo_konfigurator_url();
$uploads = content_url('uploads/2026/07');

$gallery = [
    ['file' => 'posuvne-sklenene-dvere-matne-sklo.webp', 'alt' => 'Posuvné skleněné dveře s matným sklem a černým rámem', 'w' => 507, 'h' => 626],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje.webp', 'alt' => 'Celoskleněné dveře s černým rámem v obývacím pokoji', 'w' => 626, 'h' => 428],
    ['file' => 'zenova-zena-u-sklenenych-dveri-v-interieru.webp', 'alt' => 'Žena u prosklených dveří v moderním interiéru', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-s-masivnim-zarubnim.webp', 'alt' => 'Detail skleněných dveří s madlem a masivní zárubní', 'w' => 417, 'h' => 626],
    ['file' => 'moderni-bydleni-sklenene-dvere.webp', 'alt' => 'Moderní byt s prosklenými dveřmi', 'w' => 626, 'h' => 501],
    ['file' => 'prosklene-dvere-na-miru.webp', 'alt' => 'Prosklené dveře na míru v interiéru', 'w' => 626, 'h' => 352],
    ['file' => 'interier-sklene-dvere-masivni-drevo.webp', 'alt' => 'Posuvné skleněné dveře a masivní dřevo', 'w' => 418, 'h' => 626],
    ['file' => 'sklenene-dvere-na-miru.webp', 'alt' => 'Prosklené dveře s černým rámem v bytu', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-zlute-predni-vstup.webp', 'alt' => 'Žluté skleněné dveře s kovovým madlem', 'w' => 470, 'h' => 626],
    ['file' => 'interier-modernich-sklenenych-dveri.webp', 'alt' => 'Celoskleněné dveře a dřevěná podlaha', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-doska-sklenene-dvere.webp', 'alt' => 'Skleněné dveře u dřevěných schodů', 'w' => 417, 'h' => 626],
    ['file' => 'sklenene-dvere-moderny-interier.webp', 'alt' => 'Moderní interiér s prosklenými dveřmi', 'w' => 626, 'h' => 391],
    ['file' => 'sklenene-dvere-s-modernim-madlem.webp', 'alt' => 'Detail skleněných dveří s černým madlem', 'w' => 626, 'h' => 418],
    ['file' => 'zeny-za-sklenenymi-dvermi-v-interieru.webp', 'alt' => 'Žena otevírá skleněné dveře v interiéru', 'w' => 417, 'h' => 626],
    ['file' => 'sklenene-dvere-v-interieru.webp', 'alt' => 'Skleněné dveře s výhledem do prostoru', 'w' => 626, 'h' => 470],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje-2.webp', 'alt' => 'Celoskleněné dveře s masivní zárubní', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-uzavrene-s-handlem.webp', 'alt' => 'Detail skleněných dveří s nerezovým madlem', 'w' => 626, 'h' => 417],
    ['file' => 'zeny-u-prosklenych-dveri-v-kancelari.webp', 'alt' => 'Prosklené dveře v kanceláři', 'w' => 626, 'h' => 416],
    ['file' => 'sklenene-dvere-moderni-interier.webp', 'alt' => 'Skleněné dveře v moderním interiéru', 'w' => 626, 'h' => 417],
    ['file' => 'interier-se-sklenenyma-dverima-a-drevenym-schodiistem.webp', 'alt' => 'Prosklené oddělení a dřevěné schodiště', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-detail.webp', 'alt' => 'Detail skleněných dveří s rámem', 'w' => 626, 'h' => 418],
    ['file' => 'moderni-interier-sklenene-dvere.webp', 'alt' => 'Prosklené dveře a dřevěná podlaha', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-s-matnym-sklem.webp', 'alt' => 'Posuvné dveře s matným sklem', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-sklene-dvere-s-drevem.webp', 'alt' => 'Skleněné dveře s výhledem do zahrady', 'w' => 626, 'h' => 352],
    ['file' => 'sklenene-dvere-v-interieru-2.webp', 'alt' => 'Prosklené dveře do světlého bytu', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-sklenene-dvere-s-vystavbou.webp', 'alt' => 'Skleněné dveře v moderní architektuře', 'w' => 626, 'h' => 351],
    ['file' => 'sklenene-dvere-detail-2.webp', 'alt' => 'Detail skleněných dveří s nerezovými madly', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje-3.webp', 'alt' => 'Celoskleněné dveře s černým rámem', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-se-skletem-a-drevem.webp', 'alt' => 'Prosklené příčky a dřevěné schodiště', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-muzikanta.webp', 'alt' => 'Skleněné dveře v obytném interiéru', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-na-miru-2.webp', 'alt' => 'Prosklená stěna na míru', 'w' => 626, 'h' => 351],
    ['file' => 'sklenene-dvere-v-interieru-3.webp', 'alt' => 'Prosklené dveře a dřevěná podlaha', 'w' => 626, 'h' => 417],
    ['file' => 'interier-sklene-dvere.webp', 'alt' => 'Prosklené dveře s výhledem na terasu', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-sklene-prcky-dreveny-stolek.webp', 'alt' => 'Prosklené příčky a dřevěný stolek', 'w' => 626, 'h' => 417],
    ['file' => 'zeny-na-schodech-s-laptopem.webp', 'alt' => 'Schodiště se sklem v moderním interiéru', 'w' => 417, 'h' => 626],
];
?>

<article class="sklo-realizace">
  <div class="sklo-wrap sklo-realizace__intro">
    <header class="sklo-page__head">
      <h1>Realizace</h1>
      <p class="sklo-realizace__lead">Hotové skleněné dveře v bytech, kancelářích i domech — tak, jak sedí do konkrétního otvoru.</p>
    </header>
  </div>

  <div class="sklo-gallery" data-gallery>
    <?php foreach ($gallery as $i => $item) :
        $src = trailingslashit($uploads) . $item['file'];
        $alt = $item['alt'];
        ?>
      <button
        type="button"
        class="sklo-gallery__item"
        data-lightbox-open
        data-src="<?php echo esc_url($src); ?>"
        data-alt="<?php echo esc_attr($alt); ?>"
        aria-label="<?php echo esc_attr('Zvětšit: ' . $alt); ?>"
        style="--i: <?php echo (int) $i; ?>"
      >
        <img
          src="<?php echo esc_url($src); ?>"
          alt="<?php echo esc_attr($alt); ?>"
          width="<?php echo (int) $item['w']; ?>"
          height="<?php echo (int) $item['h']; ?>"
          loading="<?php echo $i < 6 ? 'eager' : 'lazy'; ?>"
          decoding="async"
          <?php if ($i < 2) : ?>fetchpriority="high"<?php endif; ?>
        >
      </button>
    <?php endforeach; ?>
  </div>

  <section class="sklo-section sklo-cta-band">
    <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
      <div>
        <h2>Líbí se ti směr? Navrhni si vlastní.</h2>
        <p>Ve studiu složíš typ, sklo i lištu. My z toho připravíme nabídku podle tvých rozměrů.</p>
      </div>
      <div class="sklo-cta-band__actions">
        <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
        <a class="sklo-link" href="<?php echo esc_url(home_url('/kontakt/')); ?>">Nebo napiš</a>
      </div>
    </div>
  </section>
</article>

<div class="sklo-lightbox" data-lightbox hidden>
  <button type="button" class="sklo-lightbox__close" data-lightbox-close aria-label="Zavřít">
    <span aria-hidden="true">&times;</span>
  </button>
  <button type="button" class="sklo-lightbox__nav sklo-lightbox__nav--prev" data-lightbox-prev aria-label="Předchozí">
    <span aria-hidden="true">‹</span>
  </button>
  <figure class="sklo-lightbox__figure">
    <img src="" alt="" data-lightbox-img>
  </figure>
  <button type="button" class="sklo-lightbox__nav sklo-lightbox__nav--next" data-lightbox-next aria-label="Další">
    <span aria-hidden="true">›</span>
  </button>
</div>

<?php get_footer(); ?>
