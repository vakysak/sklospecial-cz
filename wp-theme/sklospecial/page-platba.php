<?php
/**
 * Template Name: Platba
 * Payment terms for CZ B2C custom glass (quote → invoice).
 */

declare(strict_types=1);

get_header();

$poptavka = home_url('/poptavka/');
$op = home_url('/obchodni-podminky/');
?>

<article class="sklo-page sklo-legal-page">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Platby</p>
      <h1>Platební podmínky</h1>
      <p class="sklo-legal-page__intro">Platíte podle nabídky a faktury — ne přes okamžitý košík. Na webu ti tykáme; tady zůstáváme u vykání.</p>
    </header>

    <div class="sklo-prose">
      <h2>Základní model</h2>
      <ol>
        <li>Pošlete poptávku (rozměry, fotky, specifikace).</li>
        <li>Připravíme nezávaznou nabídku.</li>
        <li>Po odsouhlasení vystavíme zálohovou / daňový doklad.</li>
        <li>Výrobu obvykle zahajujeme po přijetí sjednané zálohy.</li>
        <li>Doplatek a případná montáž — dle textu nabídky (montáž často na místě po předání).</li>
      </ol>

      <h2>Formy platby</h2>
      <ul>
        <li><strong>Bankovní převod</strong> — základní a preferovaný způsob. Číslo účtu a variabilní symbol jsou na faktuře. Zakázku předáváme do výroby po připsání platby (u zálohy).</li>
        <li><strong>Hotovost</strong> — jen ve výjimečných případech a v limitech zákona, typicky při osobním odběru / montáži, pokud je to v nabídce výslovně uvedeno.</li>
        <li><strong>Online platební brána / karta</strong> — standardně nenabízíme; pokud to u konkrétní zakázky umožníme, uvedeme to v nabídce.</li>
      </ul>
      <p>Ceny v nabídce jsou v CZK. Zda je částka s DPH nebo bez DPH, je vždy napsáno v textu nabídky / na dokladu. DIČ: CZ29457335 (Stolařství Aleš s.r.o.).</p>

      <h2>Splatnost</h2>
      <p>Splatnost zálohy a doplatku stanoví faktura (obvykle několik dnů). Pokud záloha nedorazí v dohodnutém termínu, výrobu nezahajujeme a termín se posouvá. Detaily smluvního vztahu: <a href="<?php echo esc_url($op); ?>">obchodní podmínky</a>.</p>

      <h2>Co neplatíte „naslepo“</h2>
      <p>Poptávka ani orientační cena v katalogu nejsou závaznou objednávkou. Závazek vzniká až odsouhlasením nabídky a úhradou dle domluvy.</p>

      <p><a class="sklo-btn" href="<?php echo esc_url($poptavka); ?>">Nezávazná poptávka</a></p>
    </div>
  </div>
</article>

<?php
get_footer();
