<?php
/**
 * Template: Realizace — inspirace / ukázky skla (stock bank, not project claims)
 */
get_header();

$uploads = content_url('uploads/2026/07');

$gallery = [
    ['file' => 'sklenene-dvere-v-interieru.webp', 'alt' => 'Ukázka skleněných dveří v interiéru s výhledem.', 'w' => 626, 'h' => 470],
    ['file' => 'moderni-bydleni-sklenene-dvere.webp', 'alt' => 'Ukázka prosklených dveří v obytném interiéru.', 'w' => 626, 'h' => 501],
    ['file' => 'sklene-zabradli-s-masivnim-drevem.webp', 'alt' => 'Ukázka skleněného zábradlí s dřevěným madlem na schodišti.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-s-masivnim-zarubnim.webp', 'alt' => 'Detail skleněných dveří s madlem a zárubní.', 'w' => 417, 'h' => 626],
    ['file' => 'zenova-zena-u-sklenenych-dveri-v-interieru.webp', 'alt' => 'Prosklené dveře v interiéru s výhledem do zahrady.', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-na-zakazku.webp', 'alt' => 'Ilustrační fotografie celoskleněného zábradlí (inspirace).', 'w' => 352, 'h' => 626],
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
      <p class="sklo-realizace__lead">Inspirace — jak může sklo vypadat v interiéru: dveře, zábradlí, skleněné stěny. Fotografie jsou ilustrační (stock / náladové snímky), ne dokumentace konkrétních zakázek zákazníků.</p>
      <p class="sklo-realizace__note"><strong>Fotky z našich montáží doplníme — startujeme.</strong> Vlastní fotodokumentaci přidáme, až budeme mít vhodné snímky z realizací. Mezitím si typ a sklo vybereš podle svých rozměrů a fotek prostoru (WhatsApp nebo poptávka).</p>
    </header>

    <section class="sklo-cases" aria-labelledby="cases-heading">
      <header class="sklo-cases__head">
        <p class="sklo-eyebrow">Jak to řešíme</p>
        <h2 id="cases-heading">Typické scénáře</h2>
        <p class="sklo-cases__lead">Nejsou to příběhy konkrétních zákazníků — jen typický postup, který u podobných zadání opakujeme.</p>
      </header>
      <div class="sklo-cases__grid">
        <article class="sklo-case">
          <p class="sklo-case__label">Typický scénář</p>
          <h3 class="sklo-case__title">Sprcha do malé koupelny</h3>
          <dl class="sklo-case__steps">
            <div>
              <dt>Problém</dt>
              <dd>Klasická zástěna nebo vanička se nevejde; potřebuješ průchod a snadný úklid.</dd>
            </div>
            <div>
              <dt>Řešení</dt>
              <dd>Walk-in stěna na míru podle fotek a rozměrů — bez zbytečných kování, kde překážejí.</dd>
            </div>
            <div>
              <dt>Výsledek</dt>
              <dd>Víc prostoru, čistší linie, nabídka podle tvého otvoru — ne podle stock fotky.</dd>
            </div>
          </dl>
          <p class="sklo-case__cta">
            <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poslat rozměry koupelny</a>
          </p>
        </article>
        <article class="sklo-case">
          <p class="sklo-case__label">Typický scénář</p>
          <h3 class="sklo-case__title">Dveře do pouzdra</h3>
          <dl class="sklo-case__steps">
            <div>
              <dt>Problém</dt>
              <dd>Otevírané křídlo bere místo; pouzdro už je ve zdi, ale typ a šířka nejsou jasné.</dd>
            </div>
            <div>
              <dt>Řešení</dt>
              <dd>Ověříme typ pouzdra (fotky + míry), navrhneme posuvné křídlo a sklo, které sedí do kapsy.</dd>
            </div>
            <div>
              <dt>Výsledek</dt>
              <dd>Průchod bez překážky; výroba startuje až po odsouhlasené nabídce.</dd>
            </div>
          </dl>
          <p class="sklo-case__cta">
            <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poslat poptávku</a>
            ·
            <a class="sklo-link" href="<?php echo esc_url(function_exists('sklo_whatsapp_url') ? sklo_whatsapp_url() : 'https://wa.me/420736134604'); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
          </p>
        </article>
      </div>
    </section>
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
