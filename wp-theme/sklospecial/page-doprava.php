<?php
/**
 * Template Name: Doprava
 * Shipping / delivery costs and options for CZ custom glass.
 */

declare(strict_types=1);

get_header();

$poptavka = home_url('/poptavka/');
$realizace = home_url('/doba-realizace/');
?>

<article class="sklo-page sklo-legal-page">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Doručení</p>
      <h1>Doprava a náklady dopravy</h1>
      <p class="sklo-legal-page__intro">Sklo není běžný balíček. Dopravu po ČR naceníme podle rozměrů, hmotnosti a vzdálenosti — vždy v nabídce. Na webu ti tykáme; tady zůstáváme u vykání.</p>
    </header>

    <div class="sklo-prose">
      <h2>Jak to u nás funguje</h2>
      <p>Nejsme klasický e-shop s pevnou sazbou u každé položky v košíku. Proces je: <strong>poptávka → nabídka → faktura → výroba → expedice</strong>. Cenu dopravy uvedeme v nabídce spolu s výrobkem.</p>

      <h2>Možnosti dopravy</h2>
      <ul>
        <li><strong>Vlastní doprava (klient)</strong> — vyzvednutí po domluvě na provozovně (Prostřední Bludovice). Termín potvrdíme po dokončení výroby.</li>
        <li><strong>Naše doprava firemním autem</strong> — podle kapacity a trasy; vhodné zejména v dostupném regionu. Cena dle vzdálenosti a rozsahu zakázky.</li>
                <li><strong>Přepravní služba</strong> — paletová / specializovaná přeprava skla. Dopravce vybíráme my podle typu zásilky; nelze vždy volit konkrétní kurýrní značku.</li>
      </ul>

      <h2>Náklady</h2>
      <ul>
        <li>Orientační údaje „od“ u produktů v katalogu, pokud jsou uvedeny, jsou jen vodítko.</li>
        <li>Finální dopravné = individuální kalkulace (rozměry, počet kusů, adresa, typ přepravy).</li>
        <li>U většího objemu nebo atypických rozměrů naceníme nejvhodnější variantu — ozvěte se v poptávce.</li>
      </ul>

      <h2>Předání a rizika</h2>
      <p>Při převzetí zásilku zkontrolujte. Zjevné poškození při dopravě reklamujte ihned (fotodokumentace). Riziko škody přechází dle domluvy v nabídce — typicky předáním dopravci, nebo při osobním odběru. Sklo vyžaduje opatrnou manipulaci a vhodné skladování do montáže.</p>

      <h2>Související lhůty</h2>
      <p>Výroba a expedice: viz <a href="<?php echo esc_url($realizace); ?>">doba realizace</a>. Samotná přeprava po ČR obvykle trvá řádově 1–3 pracovní dny podle dopravce a destinace — přesný odhad uvedeme u konkrétní zakázky.</p>

      <p><a class="sklo-btn" href="<?php echo esc_url($poptavka); ?>">Nezávazná poptávka</a></p>
    </div>
  </div>
</article>

<?php
get_footer();
