<?php
/**
 * Homepage
 */
get_header();

$cfg = sklo_konfigurator_url();
?>

<section class="sklo-hero">
  <div class="sklo-hero__visual" aria-hidden="true">
    <div class="sklo-hero__glow"></div>
    <div class="sklo-door-stage">
      <div class="sklo-door-frame">
        <div class="sklo-door-glass sklo-door-glass--clear"></div>
        <div class="sklo-door-trim"></div>
      </div>
    </div>
  </div>
  <div class="sklo-wrap sklo-hero__content">
    <p class="sklo-hero__brand">Sklospeciál</p>
    <h1 class="sklo-hero__title">Skleněné dveře,<br><em>které sedí</em> do tvého otvoru</h1>
    <p class="sklo-hero__lead">Zaměř, pošli fotky a navrhni si dveře online. Připravíme nabídku na míru — bez showroomu.</p>
    <div class="sklo-hero__actions">
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
      <a class="sklo-btn sklo-btn--ghost" href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Jak zaměřit</a>
    </div>
  </div>
</section>

<section class="sklo-section sklo-types">
  <div class="sklo-wrap">
    <header class="sklo-section__head">
      <h2>Typy skleněných dveří</h2>
      <p>Vyber směr — detaily skla a kování doladíš ve studiu.</p>
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

<section class="sklo-section sklo-flow">
  <div class="sklo-wrap">
    <header class="sklo-section__head">
      <h2>Jak to funguje</h2>
      <p>Prodej na dálku — od zaměření po nabídku.</p>
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
        <p>Celý otvor, detail stěny a podlahy. Ideálně i fotku prostoru do studia.</p>
      </li>
      <li>
        <span class="sklo-steps__n">3</span>
        <h3>Navrhni dveře</h3>
        <p>Typ, vzor skla, lišta. Dostaneš nabídku od nás.</p>
      </li>
    </ol>
  </div>
</section>

<section class="sklo-section sklo-cta-band">
  <div class="sklo-wrap sklo-cta-band__inner">
    <h2>Máš rozměry? Pojď rovnou do studia.</h2>
    <p>Vlož fotku prostoru, nastav otvor a skládej sklo se lištou.</p>
    <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Otevřít studio</a>
  </div>
</section>

<?php get_footer(); ?>
