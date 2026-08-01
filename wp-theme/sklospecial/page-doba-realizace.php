<?php
/**
 * Template Name: Doba realizace
 * Lead times aligned with site FAQ / kontakt process.
 */

declare(strict_types=1);

get_header();

$poptavka = home_url('/poptavka/');
$doprava = home_url('/doprava/');
$navod = home_url('/navod-na-zamereni/');
?>

<article class="sklo-page sklo-legal-page">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Lhůty</p>
      <h1>Doba realizace</h1>
      <p class="sklo-legal-page__intro">Orientační lhůty. Závazný termín je vždy v nabídce. Na webu ti tykáme; tady zůstáváme u vykání.</p>
    </header>

    <div class="sklo-prose">
      <h2>Od poptávky k nabídce</h2>
      <ul>
        <li><strong>Ozveme se</strong> obvykle do <strong>1 pracovního dne</strong> (reakce na poptávku).</li>
        <li><strong>Nabídka</strong> obvykle do <strong>1–2 pracovních dnů</strong> od kompletních podkladů (rozměry, fotky, specifikace). Pokud něco chybí, ozveme se dříve s dotazy.</li>
      </ul>
      <p>Jak zaměřit: <a href="<?php echo esc_url($navod); ?>">návod na zaměření</a>.</p>

      <h2>Výroba</h2>
      <p>Po odsouhlasení nabídky a přijetí sjednané zálohy jde zakázka do výroby. Orientačně <strong>5–10 pracovních dnů</strong> — podle typu výrobku, skla, kování a vytížení. U atypů nebo delších dodacích lhůt materiálu to může trvat déle; napíšeme to do nabídky.</p>
      <p>U některých katalogových položek uvádíme orientační „expedice cca X pracovních dní“ — bere se od potvrzení / úhrady, ne od první zprávy v chatu.</p>

      <h2>Expedice a doprava</h2>
      <p>Po dokončení výroby domluvíme předání: osobní odběr, naše auto, nebo přepravní služba. Samotná doprava po ČR obvykle <strong>1–3 pracovní dny</strong> podle dopravce. Více: <a href="<?php echo esc_url($doprava); ?>">doprava</a>.</p>

      <h2>Montáž</h2>
      <p>Termín montáže závisí na kalendáři techniků a lokalitě. Domlouváme individuálně po výrobě (nebo souběžně, pokud to dává smysl). Montáž se typicky hradí na místě po podpisu předávacího protokolu.</p>

      <h2>Co lhůty prodlužuje</h2>
      <ul>
        <li>Neúplné nebo nepřesné podklady (rozměry, fotky).</li>
        <li>Změny specifikace po zahájení výroby.</li>
        <li>Čekání na úhradu zálohy.</li>
        <li>Sezónní vytížení a dostupnost atypických materiálů.</li>
      </ul>

      <p><a class="sklo-btn" href="<?php echo esc_url($poptavka); ?>">Nezávazná poptávka</a></p>
    </div>
  </div>
</article>

<?php
get_footer();
