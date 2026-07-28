<?php
/**
 * Homepage — layout inspirovaný Framex (vlastní provedení)
 */
get_header();

$cfg = sklo_konfigurator_url();
?>

<section class="sklo-hero sklo-hero--photo">
  <div class="sklo-hero__bg" aria-hidden="true"></div>
  <div class="sklo-hero__shade" aria-hidden="true"></div>
  <div class="sklo-wrap sklo-hero__content">
    <p class="sklo-pill">Skleněné dveře na míru</p>
    <p class="sklo-hero__brand">Sklospeciál</p>
    <h1 class="sklo-hero__title">Dveře, které sedí<br>do <em>tvého</em> otvoru</h1>
    <p class="sklo-hero__lead">Zaměř, pošli fotky a navrhni si sklo online. Připravíme nabídku — bez showroomu.</p>
    <div class="sklo-hero__actions">
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
      <a class="sklo-btn sklo-btn--ghost-light" href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Jak zaměřit</a>
    </div>
    <p class="sklo-hero__proof">Pošli rozměry a fotky → dostaneš nabídku od nás</p>
  </div>
</section>

<section class="sklo-strip">
  <div class="sklo-wrap sklo-strip__inner">
    <div>
      <strong>Na dálku</strong>
      <span>Zaměření + fotky stačí</span>
    </div>
    <div>
      <strong>Studio online</strong>
      <span>Typ, sklo, lišta</span>
    </div>
    <div>
      <strong>Nabídka</strong>
      <span>Podle tvých rozměrů</span>
    </div>
  </div>
</section>

<section class="sklo-section sklo-about">
  <div class="sklo-wrap sklo-about__grid">
    <div class="sklo-about__copy">
      <p class="sklo-eyebrow">Jak to u nás probíhá</p>
      <h2>Od otvoru k nabídce — bez zbytečných kol</h2>
      <p>Nejezdíme hned na zaměření. Ty změříš otvor, pošleš fotky a ve studiu si složíš dveře. My z toho připravíme konkrétní nabídku.</p>
      <ul class="sklo-check">
        <li>Měření na 3 místech — bereme nejmenší hodnotu</li>
        <li>Fotka prostoru do studia (volitelně)</li>
        <li>Otočné, posuvné i celoskleněné</li>
      </ul>
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Otevřít studio</a>
    </div>
    <div class="sklo-about__panel" aria-hidden="true">
      <div class="sklo-about__glass"></div>
    </div>
  </div>
</section>

<section class="sklo-section sklo-types">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">Typy dveří</p>
      <h2>Vyber směr, detaily doladíš ve studiu</h2>
      <p>Sklo a kování nastavíš až u konkrétního návrhu.</p>
    </header>
    <div class="sklo-type-grid">
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-dvere/otocne/')); ?>">
        <span class="sklo-type__visual sklo-type__visual--swing" aria-hidden="true"></span>
        <h3>Otočné</h3>
        <p>Klasické otevírání do místnosti.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-dvere/posuvne/')); ?>">
        <span class="sklo-type__visual sklo-type__visual--slide" aria-hidden="true"></span>
        <h3>Posuvné</h3>
        <p>Po stěně nebo do pouzdra — šetří prostor.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-dvere/celosklenene/')); ?>">
        <span class="sklo-type__visual sklo-type__visual--full" aria-hidden="true"></span>
        <h3>Celoskleněné</h3>
        <p>Maximum světla, čistý průhled.</p>
      </a>
    </div>
  </div>
</section>

<section class="sklo-section sklo-flow sklo-flow--alt">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">3 kroky</p>
      <h2>Jak to funguje</h2>
    </header>
    <ol class="sklo-steps">
      <li>
        <span class="sklo-steps__n">1</span>
        <h3>Zaměř otvor</h3>
        <p>Šířka a výška na 3 místech. Bereme nejmenší hodnotu.</p>
      </li>
      <li>
        <span class="sklo-steps__n">2</span>
        <h3>Pošli fotky</h3>
        <p>Celý otvor, detail stěny a podlahy. Ideálně i fotku prostoru.</p>
      </li>
      <li>
        <span class="sklo-steps__n">3</span>
        <h3>Navrhni dveře</h3>
        <p>Typ, vzor skla, lišta. Dostaneš nabídku od nás.</p>
      </li>
    </ol>
  </div>
</section>

<section class="sklo-section sklo-faq" id="faq">
  <div class="sklo-wrap sklo-faq__grid">
    <header class="sklo-section__head">
      <p class="sklo-eyebrow">FAQ</p>
      <h2>Časté otázky</h2>
      <p>Krátce a věcně — zbytek dořešíme u poptávky.</p>
    </header>
    <div class="sklo-faq__list">
      <details class="sklo-faq__item" open>
        <summary>Musím mít přesné zaměření?</summary>
        <p>Ano — šířka a výška na 3 místech. Do nabídky bereme nejmenší hodnotu. Návod máme na webu.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Stačí fotky z telefonu?</summary>
        <p>Ano. Celý otvor, detail stěny a podlahy. Do studia můžeš vložit i fotku místnosti.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Děláte i montáž?</summary>
        <p>V poptávce zvolíš s montáží nebo bez. Domluvíme podle lokality a typu dveří.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Kdy dostanu nabídku?</summary>
        <p>Po odeslání rozměrů a fotek ti připravíme nabídku. Když něco chybí, ozveme se.</p>
      </details>
    </div>
  </div>
</section>

<section class="sklo-section sklo-cta-band">
  <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
    <div>
      <h2>Máš rozměry? Pojď rovnou do studia.</h2>
      <p>Vlož fotku prostoru, nastav otvor a skládej sklo se lištou.</p>
    </div>
    <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Otevřít studio</a>
  </div>
</section>

<?php get_footer(); ?>
