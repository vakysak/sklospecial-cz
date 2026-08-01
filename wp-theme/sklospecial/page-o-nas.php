<?php
/**
 * Template Name: O nás
 * Company / workshop page — process, IČO, map; workshop photo if available.
 */

declare(strict_types=1);

get_header();

$poptavka = home_url('/poptavka/');
$cfg = sklo_konfigurator_url();
$about_img = get_template_directory() . '/assets/images/about.jpg';
$about_uri = get_template_directory_uri() . '/assets/images/about.jpg';
$has_about = is_readable($about_img);
?>

<article class="sklo-page sklo-o-nas">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Rodinná firma</p>
      <h1>O nás — Sklospeciál</h1>
      <p class="sklo-o-nas__intro">Už 30&nbsp;let nás najdete na jedné adrese. Pokračuje třetí generace. Provozovatel: Stolařství Aleš s.r.o.</p>
    </header>

    <div class="sklo-o-nas__grid">
      <div class="sklo-prose">
        <h2>Kdo jsme</h2>
        <p>Sklospeciál je rodinná značka pro skleněné dveře, sprchy, zábradlí, stříšky a příčky na míru. Pracujeme na dálku — zaměříš sám, pošleš fotky a nabídku připravíme podle konkrétního otvoru.</p>
        <p>Nemáme showroom. Studio na webu ukáže typ, sklo a lištu ještě před objednávkou. Montáž je volitelná; výrobek můžeš převzít i sám.</p>

        <h2>Jak to u nás probíhá</h2>
        <ol>
          <li>Pošleš rozměry a fotky (nebo poptávku).</li>
          <li>Do 1 pracovního dne se ozveme; nabídka bez závazku.</li>
          <li>Po odsouhlasení vystavíme fakturu — výroba startuje po úhradě.</li>
          <li>Expedice: osobní odběr, naše doprava, nebo přepravní firma.</li>
          <li>Volitelná montáž s předávacím protokolem.</li>
        </ol>
      </div>

      <aside class="sklo-o-nas__aside" aria-label="Provozovna a firma">
        <?php if ($has_about) : ?>
          <figure class="sklo-o-nas__photo">
            <img
              src="<?php echo esc_url($about_uri); ?>"
              alt="Provozovna a dílenské prostředí Sklospeciál"
              width="800"
              height="600"
              loading="lazy"
              decoding="async"
            >
            <figcaption>Provozovna / dílenské prostředí — Horní Bludovice. Nejde o portrét pojmenovaného týmu.</figcaption>
          </figure>
        <?php else : ?>
          <div class="sklo-o-nas__photo-placeholder">
            <p>Prostor pro fotografii provozovny — lze nahrát do tématu (<code>assets/images/about.jpg</code>) nebo do médií WordPress.</p>
          </div>
        <?php endif; ?>

        <div class="sklo-o-nas__card">
          <h2>Provozovna / kontakt</h2>
          <p>Prostřední Bludovice 193<br>739 37 Horní Bludovice</p>
          <p><a href="tel:+420736134604">+420 736 134 604</a><br>
          <a href="mailto:info@sklospecial.cz">info@sklospecial.cz</a></p>
        </div>

        <div class="sklo-o-nas__card">
          <h2>Firma</h2>
          <p>
            Stolařství Aleš s.r.o.<br>
            Sídlo: Horní Bludovice 193<br>
            739 37 Horní Bludovice<br>
            IČO: 29457335<br>
            DIČ: CZ29457335
          </p>
        </div>

        <div class="sklo-o-nas__map">
          <iframe
            title="Mapa — Prostřední Bludovice 193"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            src="https://www.openstreetmap.org/export/embed.html?bbox=18.420%2C49.735%2C18.460%2C49.760&amp;layer=mapnik&amp;marker=49.7475%2C18.4400"
          ></iframe>
          <p><a class="sklo-link" href="https://www.openstreetmap.org/?mlat=49.7475&amp;mlon=18.4400#map=14/49.7475/18.4400" rel="noopener noreferrer" target="_blank">Otevřít mapu</a></p>
        </div>
      </aside>
    </div>

    <p class="sklo-o-nas__cta">
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Otevřít studio</a>
      <a class="sklo-link" href="<?php echo esc_url($poptavka); ?>">Nebo napsat poptávku</a>
      <?php if (function_exists('sklo_render_cta_sla')) : ?>
        <?php sklo_render_cta_sla(); ?>
      <?php endif; ?>
    </p>
  </div>
</article>

<?php
get_footer();
