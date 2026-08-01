<?php
/**
 * Template Name: Záruka a servis
 * Statutory warranty, post-warranty service, optional +1 year extension.
 */

declare(strict_types=1);

get_header();

$poptavka = home_url('/poptavka/');
$op = home_url('/obchodni-podminky/');
?>

<article class="sklo-page sklo-legal-page">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Servis</p>
      <h1>Záruka, reklamace a servis</h1>
      <p class="sklo-legal-page__intro">Tři odlišné věci: zákonná práva z vad, pozáruční servis za úhradu a volitelná prodloužená záruka (+1 rok). Na webu ti tykáme; tady zůstáváme u vykání.</p>
    </header>

    <div class="sklo-prose">
      <h2>1. Zákonná odpovědnost za vady (reklamace)</h2>
      <p>U spotřebitelů odpovídáme za vady podle občanského zákoníku a zákona o ochraně spotřebitele — typicky <strong>24 měsíců</strong> od převzetí. Nejde o „smluvní dárek“, ale o zákonná práva. Podrobnosti jsou v <a href="<?php echo esc_url($op); ?>">obchodních podmínkách</a>.</p>
      <ul>
        <li>Reklamujte bez zbytečného odkladu — e-mailem na <a href="mailto:info@sklospecial.cz">info@sklospecial.cz</a> nebo telefonicky, ideálně s fotkami a číslem faktury / zakázky.</li>
        <li>Zjevné poškození při dopravě hlaste ihned při převzetí (nebo hned po rozbalení).</li>
        <li>Na skle mohou být běžné optické jevy a výrobní tolerance dle norem; drobné odchylky v mezích praxe nejsou vadou.</li>
        <li>Výrobek vyrobený podle vámi potvrzených rozměrů nelze reklamovat jen proto, že se později ukáže chyba ve vlastním zaměření (pokud jsme nezaměřovali my).</li>
      </ul>

      <h2>2. Pozáruční servis</h2>
      <p>Po skončení zákonné lhůty (nebo mimo uznanou reklamaci) nabízíme <strong>pozáruční servis za úhradu</strong> — seřízení, výměna kování, opravy či výměna dílů podle dostupnosti. Cenu a termín domluvíme individuálně.</p>

      <h2>3. Prodloužená záruka (+1 rok) za poplatek</h2>
      <p>Volitelná <strong>smluvní záruka za jakost</strong> nad rámec zákona. Prodlouží pokrytí výrobních vad o <strong>+1 rok</strong> po uplynutí zákonné lhůty.</p>
      <p><strong>Cena:</strong> <strong>10&nbsp;% z ceny výrobku</strong> (bez dopravy a montáže). Konkrétní částku v&nbsp;Kč uvádíme u produktu v katalogu — můžete ji rovnou zvolit jako volbu „Prodloužená záruka (+1 rok)“.</p>

      <h3>Co kryje</h3>
      <ul>
        <li>Výrobní vady skla a konstrukce (např. vada materiálu, výrobní chyba).</li>
        <li>Funkční vady kování / pojezdů / pantů dodaných s výrobkem, pokud vznikly vadou materiálu nebo výroby.</li>
        <li>U montáže provedené námi: vady montáže v rozsahu sjednaném v nabídce / předávacím protokolu.</li>
      </ul>

      <h3>Co nekryje (buďme upřímní)</h3>
      <ul>
        <li><strong>Rozbití skla nárazem, pádem, neodbornou manipulací</strong> nebo jiným vnějším zásahem — to není výrobní vada; na to se vztahuje pojištění domácnosti / odpovědnosti, ne naše prodloužená záruka.</li>
        <li>Běžné opotřebení, škrábance z užívání, usazeniny, nevhodná údržba, chemické poškození.</li>
        <li>Škody z chybného vlastního zaměření, neodborné montáže třetí osobou nebo změn po předání.</li>
        <li>Doprava zajištěná zákazníkem — škoda při takové přepravě se řeší s dopravcem / pojištěním.</li>
      </ul>
      <p>Prodloužená záruka <strong>nenahrazuje</strong> ani neomezuje zákonná práva z vadného plnění. Podmínky upřesníme písemně v nabídce a na faktuře.</p>

      <h2>Jak to objednat</h2>
      <p>U produktu v katalogu zvolíte „Prodloužená záruka (+1 rok)“ s uvedenou cenou, nebo v <a href="<?php echo esc_url($poptavka); ?>">poptávce</a> zaškrtnete zájem — částku v&nbsp;Kč uvidíte, pokud máte vybraný produkt.</p>

      <p><a class="sklo-btn" href="<?php echo esc_url($poptavka); ?>">Napsat poptávku</a></p>
    </div>
  </div>
</article>

<?php
get_footer();
