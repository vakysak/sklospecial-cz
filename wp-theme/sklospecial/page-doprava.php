<?php
/**
 * Template Name: Doprava
 * Shipping / delivery and indicative installation costs for CZ custom glass.
 */

declare(strict_types=1);

get_header();

$poptavka = home_url('/poptavka/');
$realizace = home_url('/doba-realizace/');
?>

<article class="sklo-page sklo-legal-page">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Doručení a montáž</p>
      <h1>Doprava a montáž</h1>
      <p class="sklo-legal-page__intro">Sklo není běžný balíček. Dopravu i montáž po ČR naceníme podle rozměrů, hmotnosti, vzdálenosti a typu výrobku — vždy v nabídce. Na webu ti tykáme; tady zůstáváme u vykání.</p>
    </header>

    <div class="sklo-prose">
      <h2>Jak to u nás funguje</h2>
      <p>Nejsme klasický e-shop s pevnou sazbou u každé položky v košíku. Proces je: <strong>poptávka → nabídka → faktura → výroba → expedice</strong>. Cenu dopravy a případné montáže uvedeme v nabídce spolu s výrobkem.</p>

      <h2>Možnosti dopravy</h2>
      <ul>
        <li><strong>Vlastní doprava (klient)</strong> — vyzvednutí po domluvě na provozovně (Prostřední Bludovice). Termín potvrdíme po dokončení výroby.</li>
        <li><strong>Naše doprava firemním autem</strong> — podle kapacity a trasy; vhodné zejména v dostupném regionu. Cena dle vzdálenosti a rozsahu zakázky.</li>
        <li><strong>Přepravní služba</strong> — paletová / specializovaná přeprava skla. Dopravce vybíráme my podle typu zásilky; nelze vždy volit konkrétní kurýrní značku.</li>
      </ul>

      <h2>Náklady dopravy</h2>
      <ul>
        <li>Orientační údaje „od“ u produktů v katalogu, pokud jsou uvedeny, jsou jen vodítko.</li>
        <li>Finální dopravné = individuální kalkulace (rozměry, počet kusů, adresa, typ přepravy).</li>
        <li>U většího objemu nebo atypických rozměrů naceníme nejvhodnější variantu — ozvěte se v poptávce.</li>
      </ul>

      <h2 id="praha">Výjezdy do Prahy a vzdálenějších regionů</h2>
      <p>Do Prahy (a případně dalších vzdálenějších lokalit) plánujeme montáže dávkově — typicky když se sejdou <strong>3–5 montáží</strong> na jednu trasu. Náklady na výjezd se tak rozdělí mezi zákazníky a vychází to výhodněji.</p>
      <p>Termíny v Praze vypisujeme pravidelně — napište nám a zařadíme vás do nejbližšího výjezdu.</p>
      <ul>
        <li>Orientační doprava na zákazníka při sdíleném výjezdu: <strong>1&nbsp;200–1&nbsp;500&nbsp;Kč</strong> (příklad při cca 4 montážích na jeden výjezd).</li>
        <li>Finální částku uvedeme v nabídce podle konkrétní trasy a rozsahu.</li>
        <li>Pokud nechcete čekat na společný termín, individuální / expresní výjezd je možný — bývá ale dražší.</li>
      </ul>

      <h2 id="montaz">Orientační ceny montáže</h2>
      <p>Montáž nabízíme volitelně — výrobek můžete objednat i bez ní. Částky níže jsou <strong>orientační</strong> (práce); finální cenu včetně dojezdu uvedeme v nabídce po zaměření / podle lokality.</p>
      <?php if (function_exists('sklo_montaz_orientacni_ceny')) : ?>
        <ul>
          <?php foreach (sklo_montaz_orientacni_ceny() as $tier) : ?>
            <li><strong><?php echo esc_html($tier['label']); ?></strong> — <?php echo esc_html($tier['from']); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <ul>
        <li>Nejde o pevný ceník — atypické kotvení, výška, přístup nebo delší dojezd cenu posunou.</li>
        <li>Termín montáže závisí na kalendáři techniků a lokalitě.</li>
        <li>Montáž se typicky hradí na místě po podpisu předávacího protokolu.</li>
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
