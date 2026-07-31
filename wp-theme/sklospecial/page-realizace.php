<?php
/**
 * Template: Realizace — portfolio galerie
 */
get_header();

$cfg = sklo_konfigurator_url();
$uploads = content_url('uploads/2026/07');

$gallery = [
    ['file' => 'sklenene-dvere-v-interieru.webp', 'alt' => 'Moderní skleněné dveře v architektuře s panoramatickým výhledem.', 'w' => 626, 'h' => 470],
    ['file' => 'moderni-bydleni-sklenene-dvere.webp', 'alt' => 'Pohled na moderní byt s prosklenými dveřmi a pohodlným nábytkem.', 'w' => 626, 'h' => 501],
    ['file' => 'sklene-zabradli-s-masivnim-drevem.webp', 'alt' => 'Moderní skleněné zábradlí s dřevěným madlem na schodišti', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-s-masivnim-zarubnim.webp', 'alt' => 'Detail skleněných dveří s nerezovým madlem a masivní zárubní.', 'w' => 417, 'h' => 626],
    ['file' => 'zenova-zena-u-sklenenych-dveri-v-interieru.webp', 'alt' => 'Žena stojící u prosklených dveří v moderním interiéru s výhledem do zahrady.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-na-zakazku.webp', 'alt' => 'Celoskleněné zábradlí na schodišti s masivním dřevem a osvětlením.', 'w' => 352, 'h' => 626],
    ['file' => 'sklenene-dvere-s-modernim-madlem.webp', 'alt' => 'Detail skleněných dveří s černým madlem', 'w' => 626, 'h' => 418],
    ['file' => 'sklenene-dvere-moderny-interier.webp', 'alt' => 'Dva lidé odpočívající v moderním interiéru s prosklenými dveřmi.', 'w' => 626, 'h' => 391],
    ['file' => 'sklene-zabradli-s-nerezovym-madlem.webp', 'alt' => 'Skleněné zábradlí s nerezovým madlem v interiéru', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-se-sklenem-a-sochami.webp', 'alt' => 'Moderní interiér s velkými skleněnými stěnami a abstraktními sochami v zahradě.', 'w' => 626, 'h' => 262],
    ['file' => 'sklenene-dvere-na-miru.webp', 'alt' => 'Prosklené dveře s černým rámem v interiéru moderního bytu.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-masivnim-drevem.webp', 'alt' => 'Skleněné zábradlí s nerezovými madly na dřevěných schodech.', 'w' => 626, 'h' => 417],
    ['file' => 'interier-modernich-sklenenych-dveri.webp', 'alt' => 'Moderní interiér s celoskleněnými dveřmi a dřevěnou podlahou', 'w' => 626, 'h' => 417],
    ['file' => 'posuvne-sklenene-dvere-matne-sklo.webp', 'alt' => 'Posuvné skleněné dveře s matným sklem a černým rámem', 'w' => 507, 'h' => 626],
    ['file' => 'sklenene-zabradli-schody.webp', 'alt' => 'Skleněné zábradlí na schodišti s dřevěnými schody a výhledem na zeleň.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje.webp', 'alt' => 'Celoskleněné dveře s černým rámem oddělují obývací pokoj od jiného prostoru.', 'w' => 626, 'h' => 428],
    ['file' => 'interier-sklene-dvere.webp', 'alt' => 'Moderní interiér s prosklenými dveřmi a terasou.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-nerezovym-madlem.webp', 'alt' => 'Skleněné zábradlí s nerezovým madlem v moderním interiéru.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje-2.webp', 'alt' => 'Celoskleněné dveře s masivní zárubní v moderním interiéru', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-uzavrene-s-handlem.webp', 'alt' => 'Detail skleněných dveří s nerezovým madlem a klíčem.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-schodiste.webp', 'alt' => 'Skleněné zábradlí na schodišti s dřevěným schodištěm a nerezovými prvky.', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-sklene-prcky-dreveny-stolek.webp', 'alt' => 'Moderní interiér s prosklenými příčkami a dřevěným stolkem.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-moderni-interier.webp', 'alt' => 'Moderní interiér s skleněnými dveřmi a pohodlným křeslem.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-masivnim-drevem-2.webp', 'alt' => 'Bezpečné skleněné zábradlí na schodišti s nerezovou madlem', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-detail.webp', 'alt' => 'Detail skleněných dveří s rámem na pozadí prostorné místnosti.', 'w' => 626, 'h' => 418],
    ['file' => 'moderni-interier-sklenene-dvere.webp', 'alt' => 'Moderní interiér s prosklenými dveřmi a dřevěnou podlahou.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-masivnim-drevem-3.webp', 'alt' => 'Skleněné zábradlí s masivním dřevem na schodišti', 'w' => 470, 'h' => 626],
    ['file' => 'zeny-u-prosklenych-dveri-v-kancelari.webp', 'alt' => 'Žena stojící u prosklených dveří v kanceláři a telefonující', 'w' => 626, 'h' => 416],
    ['file' => 'interier-sklene-dvere-masivni-drevo.webp', 'alt' => 'Prostorný interiér s posuvnými skleněnými dveřmi a masivním dřevem', 'w' => 418, 'h' => 626],
    ['file' => 'sklenene-zabradli-schody-modernu.webp', 'alt' => 'Moderní skleněné zábradlí na schodišti s dřevěným madlem.', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-doska-sklenene-dvere.webp', 'alt' => 'Moderní interiér se skleněnými dveřmi a dřevěnými schody.', 'w' => 417, 'h' => 626],
    ['file' => 'interier-se-sklenenyma-dverima-a-drevenym-schodiistem.webp', 'alt' => 'Moderní interiér s proskleným oddělením a dřevěným schodištěm', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-masivnim-drevem-4.webp', 'alt' => 'Moderní skleněné zábradlí s masivním dřevem v interiéru domu.', 'w' => 626, 'h' => 471],
    ['file' => 'moderni-interier-se-skletem-a-drevem.webp', 'alt' => 'Moderní interiér s prosklenými příčkami a dřevěným schodištěm.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-s-matnym-sklem.webp', 'alt' => 'Prosklené posuvné dveře s matným sklem a moderním designem.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-nerezovym-madlem-2.webp', 'alt' => 'Skleněné zábradlí s nerezovým madlem v moderním interiéru.', 'w' => 418, 'h' => 626],
    ['file' => 'sklenene-dvere-v-interieru-2.webp', 'alt' => 'Pohled na prosklené dveře vedoucí do světlého interiéru bytu', 'w' => 626, 'h' => 417],
    ['file' => 'moderni-interier-sklene-dvere-s-drevem.webp', 'alt' => 'Moderní interiér s skleněnými dveřmi a zahradou', 'w' => 626, 'h' => 352],
    ['file' => 'sklenene-zabradli-s-nerezovym-madlem-3.webp', 'alt' => 'Moderní skleněné zábradlí s nerezovým madlem na terase', 'w' => 626, 'h' => 430],
    ['file' => 'moderni-sklenene-dvere-s-vystavbou.webp', 'alt' => 'Moderní skleněné dveře v moderní architektuře s odrazem okolní přírody.', 'w' => 626, 'h' => 351],
    ['file' => 'sklenene-dvere-detail-2.webp', 'alt' => 'Detail skleněných dveří s nerezovými madly na skleněné konstrukci.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-s-vyhledem-do-zahrady.webp', 'alt' => 'Skleněné zábradlí s výhledem do zahrady', 'w' => 626, 'h' => 358],
    ['file' => 'sklenene-dvere-do-obyvaciho-pokoje-3.webp', 'alt' => 'Celoskleněné dveře s černým rámem v moderním interiéru', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-v-interieru-3.webp', 'alt' => 'Interiér s prosklenými dveřmi a dřevěnou podlahou.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-muzikanta.webp', 'alt' => 'Žena stojící za skleněnými dveřmi s černým oblečením a červenými rtěnkami.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-na-miru-2.webp', 'alt' => 'Proskleněná stěna s velkým zakřiveným oknem v moderní budově.', 'w' => 626, 'h' => 351],
    ['file' => 'prosklene-dvere-na-miru.webp', 'alt' => 'Prosklené dveře s moderním designem ve skleněné architektuře.', 'w' => 626, 'h' => 352],
    ['file' => 'sklenene-dvere-zlute-predni-vstup.webp', 'alt' => 'Žluté skleněné dveře s kovovým madlem', 'w' => 470, 'h' => 626],
    ['file' => 'zeny-na-schodech-s-laptopem.webp', 'alt' => 'Žena na schodech se skleněným zábradlím, drží laptop.', 'w' => 417, 'h' => 626],
    ['file' => 'zeny-za-sklenenymi-dvermi-v-interieru.webp', 'alt' => 'Žena otevírá skleněné dveře v moderním interiéru.', 'w' => 417, 'h' => 626],
];
?>

<article class="sklo-realizace">
  <div class="sklo-wrap sklo-realizace__intro">
    <header class="sklo-page__head">
      <h1>Realizace</h1>
      <p class="sklo-realizace__lead">Hotové skleněné dveře i zábradlí v bytech, kancelářích i domech — tak, jak sedí do konkrétního prostoru.</p>
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
