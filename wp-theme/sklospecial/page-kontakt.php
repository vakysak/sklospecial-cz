<?php
/**
 * Template Name: Kontakt
 * Contact page — how inquiry works + address / billing details.
 */

declare(strict_types=1);

get_header();

$poptavka = home_url('/poptavka/');
?>

<article class="sklo-page sklo-kontakt">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Ozvěte se nám</p>
      <h1>Kontaktujte nás</h1>
      <p class="sklo-kontakt__intro">Níže najdete, jak probíhá poptávka, a všechny údaje pro spojení i fakturaci.</p>
      <p class="sklo-kontakt__tone-note">Na webu a v nabídce ti tykáme. U právních a procesních textů na této stránce zůstáváme u vykání.</p>
    </header>

    <section class="sklo-kontakt__process" aria-labelledby="kontakt-process">
      <h2 id="kontakt-process">Jak probíhá poptávka</h2>
      <ol class="sklo-kontakt__steps">
        <li>
          <span class="sklo-kontakt__step-num">1</span>
          <div>
            <strong>Odešlete poptávku</strong>
            <p>Obratem, nejpozději následující pracovní den, se vám ozveme s řešením a termínem.</p>
          </div>
        </li>
        <li>
          <span class="sklo-kontakt__step-num">2</span>
          <div>
            <strong>Volitelné zaměření</strong>
            <p>Pokud si nejste jistí, nebo jde o složitější projekt, přijedeme a zaměříme prostor.</p>
          </div>
        </li>
        <li>
          <span class="sklo-kontakt__step-num">3</span>
          <div>
            <strong>Výroba</strong>
            <p>Vystavíme fakturu; po úhradě jde zakázka do výroby (5–10 dní).</p>
          </div>
        </li>
        <li>
          <span class="sklo-kontakt__step-num">4</span>
          <div>
            <strong>Expedice</strong>
            <p>Vlastní doprava klientem, naše doprava firemním autem, nebo přepravní služba.</p>
          </div>
        </li>
        <li>
          <span class="sklo-kontakt__step-num">5</span>
          <div>
            <strong>Montáž</strong>
            <p>Domluvíme termín podle kalendáře našich techniků. Montáž se hradí na místě po podepsání předávacího protokolu.</p>
          </div>
        </li>
      </ol>
    </section>

    <?php if (function_exists('sklo_render_risk_block')) : ?>
      <?php sklo_render_risk_block('vykani', 'sklo-risk--kontakt'); ?>
    <?php endif; ?>

    <?php
    $about_img = get_template_directory() . '/assets/images/about.jpg';
    $about_uri = get_template_directory_uri() . '/assets/images/about.jpg';
    if (is_readable($about_img)) :
        ?>
    <figure class="sklo-kontakt__photo">
      <img
        src="<?php echo esc_url($about_uri); ?>"
        alt="Provozovna Sklospeciál — dílenské prostředí"
        width="800"
        height="600"
        loading="lazy"
        decoding="async"
      >
      <figcaption>Provozovna v Horních Bludovicích — atmosféra dílny, ne portrét týmu. Více na stránce <a href="<?php echo esc_url(home_url('/o-nas/')); ?>">O nás</a>.</figcaption>
    </figure>
    <?php endif; ?>

    <section class="sklo-kontakt__grid" aria-labelledby="kontakt-udaje">
      <div class="sklo-kontakt__card">
        <h2 id="kontakt-udaje">Spojení</h2>
        <dl class="sklo-kontakt__dl">
          <div>
            <dt>Provozovna / kontakt</dt>
            <dd>Prostřední Bludovice 193<br>739 37 Horní Bludovice</dd>
          </div>
          <div>
            <dt>Telefon</dt>
            <dd><a href="tel:+420736134604">+420 736 134 604</a></dd>
          </div>
          <div>
            <dt>E-mail</dt>
            <dd><a href="mailto:info@sklospecial.cz">info@sklospecial.cz</a></dd>
          </div>
        </dl>
      </div>
      <div class="sklo-kontakt__card">
        <h2>Fakturační údaje / sídlo</h2>
        <p class="sklo-kontakt__billing">
          Stolařství Aleš s.r.o.<br>
          <strong>Sídlo:</strong> Horní Bludovice 193<br>
          739 37 Horní Bludovice<br>
          IČO: 29457335<br>
          DIČ: CZ29457335<br>
          <span class="sklo-kontakt__brand-note">Veřejná značka: Sklospeciál</span>
        </p>
      </div>
    </section>

    <p class="sklo-kontakt__cta">
      <a class="sklo-btn" href="<?php echo esc_url($poptavka); ?>">Napsat poptávku</a>
      <?php if (function_exists('sklo_render_cta_sla')) : ?>
        <?php sklo_render_cta_sla(); ?>
      <?php endif; ?>
    </p>
  </div>
</article>

<?php
get_footer();
