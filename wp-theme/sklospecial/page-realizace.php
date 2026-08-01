<?php
/**
 * Template: Realizace — inspirace / ukázky skla (stock bank, not project claims)
 */
get_header();

$cfg = sklo_konfigurator_url();
$uploads = content_url('uploads/2026/07');

$gallery = [
    ['file' => 'sklenene-dvere-v-interieru.webp', 'alt' => 'Ukázka skleněných dveří v interiéru s výhledem.', 'w' => 626, 'h' => 470],
    ['file' => 'moderni-bydleni-sklenene-dvere.webp', 'alt' => 'Ukázka prosklených dveří v obytném interiéru.', 'w' => 626, 'h' => 501],
    ['file' => 'sklene-zabradli-s-masivnim-drevem.webp', 'alt' => 'Ukázka skleněného zábradlí s dřevěným madlem na schodišti.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-s-masivnim-zarubnim.webp', 'alt' => 'Detail skleněných dveří s madlem a zárubní.', 'w' => 417, 'h' => 626],
    ['file' => 'zenova-zena-u-sklenenych-dveri-v-interieru.webp', 'alt' => 'Prosklené dveře v interiéru s výhledem do zahrady.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-na-zakazku.webp', 'alt' => 'Ukázka celoskleněného zábradlí na schodišti.', 'w' => 352, 'h' => 626],
    ['file' => 'sklenene-dvere-s-modernim-madlem.webp', 'alt' => 'Detail skleněných dveří s černým madlem.', 'w' => 626, 'h' => 418],
    ['file' => 'sklenene-dvere-moderny-interier.webp', 'alt' => 'Ukázka prosklených dveří v obývacím interiéru.', 'w' => 626, 'h' => 391],
    ['file' => 'sklene-zabradli-s-nerezovym-madlem.webp', 'alt' => 'Ukázka skleněného zábradlí s nerezovým madlem.', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-se-sklenem-a-sochami.webp', 'alt' => 'Ukázka velkých skleněných ploch v interiéru.', 'w' => 626, 'h' => 262],
    ['file' => 'sklenene-dvere-na-miru.webp', 'alt' => 'Prosklené dveře s rámem v bytovém interiéru.', 'w' => 626, 'h' => 417],
    ['file' => 'interier-modernich-sklenenych-dveri.webp', 'alt' => 'Ukázka celoskleněných dveří a dřevěné podlahy.', 'w' => 626, 'h' => 417],
    ['file' => 'posuvne-sklenene-dvere-matne-sklo.webp', 'alt' => 'Posuvné skleněné dveře s matným sklem.', 'w' => 507, 'h' => 626],
    ['file' => 'sklenene-zabradli-schody.webp', 'alt' => 'Ukázka skleněného zábradlí na schodišti.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje.webp', 'alt' => 'Celoskleněné dveře oddělující obývací prostor.', 'w' => 626, 'h' => 428],
    ['file' => 'interier-sklene-dvere.webp', 'alt' => 'Ukázka prosklených dveří s výhledem na terasu.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje-2.webp', 'alt' => 'Celoskleněné dveře se zárubní v interiéru.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-uzavrene-s-handlem.webp', 'alt' => 'Detail skleněných dveří s nerezovým madlem.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-schodiste.webp', 'alt' => 'Ukázka skleněného zábradlí u dřevěného schodiště.', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-sklene-prcky-dreveny-stolek.webp', 'alt' => 'Ukázka skleněných příček v interiéru.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-moderni-interier.webp', 'alt' => 'Ukázka skleněných dveří v obytném interiéru.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-masivnim-drevem-2.webp', 'alt' => 'Ukázka skleněného zábradlí na schodišti.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-detail.webp', 'alt' => 'Detail skleněných dveří s rámem.', 'w' => 626, 'h' => 418],
    ['file' => 'moderni-interier-sklenene-dvere.webp', 'alt' => 'Ukázka prosklených dveří a dřevěné podlahy.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-masivnim-drevem-3.webp', 'alt' => 'Ukázka skleněného zábradlí s dřevěným madlem.', 'w' => 470, 'h' => 626],
    ['file' => 'zeny-u-prosklenych-dveri-v-kancelari.webp', 'alt' => 'Ukázka prosklených dveří v kancelářském interiéru.', 'w' => 626, 'h' => 416],
    ['file' => 'interier-sklene-dvere-masivni-drevo.webp', 'alt' => 'Ukázka posuvných skleněných dveří a dřeva.', 'w' => 418, 'h' => 626],
    ['file' => 'sklenene-zabradli-schody-modernu.webp', 'alt' => 'Ukázka skleněného zábradlí na schodišti.', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-doska-sklenene-dvere.webp', 'alt' => 'Ukázka skleněných dveří u schodiště.', 'w' => 417, 'h' => 626],
    ['file' => 'interier-se-sklenenyma-dverima-a-drevenym-schodiistem.webp', 'alt' => 'Ukázka proskleného oddělení a schodiště.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-masivnim-drevem-4.webp', 'alt' => 'Ukázka skleněného zábradlí s dřevem.', 'w' => 626, 'h' => 471],
    ['file' => 'moderni-interier-se-skletem-a-drevem.webp', 'alt' => 'Ukázka skleněných příček a dřevěného schodiště.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-s-matnym-sklem.webp', 'alt' => 'Posuvné dveře s matným sklem.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-nerezovym-madlem-2.webp', 'alt' => 'Ukázka skleněného zábradlí s nerezovým madlem.', 'w' => 418, 'h' => 626],
    ['file' => 'sklenene-dvere-v-interieru-2.webp', 'alt' => 'Ukázka prosklených dveří ve světlém interiéru.', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-sklene-dvere-s-drevem.webp', 'alt' => 'Ukázka skleněných dveří s výhledem do zahrady.', 'w' => 626, 'h' => 352],
    ['file' => 'sklenene-zabradli-s-nerezovym-madlem-3.webp', 'alt' => 'Ukázka skleněného zábradlí na terase.', 'w' => 626, 'h' => 430],
    ['file' => 'moderni-sklenene-dvere-s-vystavbou.webp', 'alt' => 'Ukázka skleněných dveří v architektuře.', 'w' => 626, 'h' => 351],
    ['file' => 'sklenene-dvere-detail-2.webp', 'alt' => 'Detail skleněných dveří s madly.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-vyhledem-do-zahrady.webp', 'alt' => 'Ukázka skleněného zábradlí s výhledem do zahrady.', 'w' => 626, 'h' => 358],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje-3.webp', 'alt' => 'Celoskleněné dveře s rámem v interiéru.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-v-interieru-3.webp', 'alt' => 'Ukázka prosklených dveří a dřevěné podlahy.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-na-miru-2.webp', 'alt' => 'Ukázka prosklené stěny v architektuře.', 'w' => 626, 'h' => 351],
    ['file' => 'prosklene-dvere-na-miru.webp', 'alt' => 'Ukázka prosklených dveří ve skleněné architektuře.', 'w' => 626, 'h' => 352],
    ['file' => 'sklenene-dvere-zlute-predni-vstup.webp', 'alt' => 'Ukázka skleněných dveří s kovovým madlem.', 'w' => 470, 'h' => 626],
    ['file' => 'zeny-na-schodech-s-laptopem.webp', 'alt' => 'Ukázka skleněného zábradlí na schodech.', 'w' => 417, 'h' => 626],
    ['file' => 'zeny-za-sklenenymi-dvermi-v-interieru.webp', 'alt' => 'Ukázka skleněných dveří v interiéru.', 'w' => 417, 'h' => 626],
];
?>

<article class="sklo-realizace">
  <div class="sklo-wrap sklo-realizace__intro">
    <header class="sklo-page__head">
      <h1>Ukázky skleněných řešení</h1>
      <p class="sklo-realizace__lead">Inspirace — jak může sklo vypadat v interiéru: dveře, zábradlí, příčky. Jde o ilustrační fotografie, ne o katalog našich konkrétních zakázek.</p>
      <p class="sklo-realizace__note">Fotodokumentaci vlastních realizací doplníme. Mezitím si typ a sklo složíš ve studiu podle svých rozměrů.</p>
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
        <h2>Líbí se ti směr? Slož si vlastní.</h2>
        <p>Ve studiu vybereš typ, sklo i lištu. Nabídku připravíme podle tvých rozměrů a fotek — ne podle stock fotek nahoře.</p>
      </div>
      <div class="sklo-cta-band__actions">
        <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
        <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Nebo napiš</a>
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
