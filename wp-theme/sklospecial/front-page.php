<?php
/**
 * Homepage
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
    <p class="sklo-hero__lead">Zaměříš sám, pošleš fotky a ve studiu si složíš dveře — typ, sklo, lištu. My z toho připravíme konkrétní nabídku. Bez showroomu, bez zbytečných kol.</p>
    <div class="sklo-hero__actions">
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
      <a class="sklo-btn sklo-btn--ghost-light" href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Jak zaměřit</a>
    </div>
    <p class="sklo-hero__proof">Pošli rozměry a fotky → dostaneš nabídku od nás</p>
  </div>
</section>

<section class="sklo-strip">
  <div class="sklo-wrap sklo-strip__inner sklo-strip__inner--rich">
    <div>
      <strong>Na dálku — bez návštěvy prodejny</strong>
      <span>Zaměření a fotky z telefonu stačí. Celý proces od výběru po nabídku proběhne online.</span>
    </div>
    <div>
      <strong>Studio online — složíš si dveře sám</strong>
      <span>Vlož fotku prostoru, nastav rozměry a vyber sklo se lištou. Vidíš výsledek ještě před objednávkou.</span>
    </div>
    <div>
      <strong>Nabídka na míru — podle tvých rozměrů</strong>
      <span>Nepracujeme s katalogovými cenami. Každá nabídka vychází z konkrétního otvoru a zvoleného provedení.</span>
    </div>
  </div>
</section>

<section class="sklo-section sklo-about">
  <div class="sklo-wrap sklo-about__grid">
    <div class="sklo-about__copy">
      <p class="sklo-eyebrow">Jak to funguje</p>
      <h2>Od otvoru k nabídce — bez zbytečných kol</h2>
      <p>Nejezdíme hned na zaměření. Ty změříš otvor, pošleš fotky a ve studiu si složíš dveře. My z toho připravíme konkrétní nabídku — a když něco chybí, ozveme se.</p>
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Otevřít studio</a>
    </div>
    <div class="sklo-about__panel" aria-hidden="true">
      <div class="sklo-about__glass"></div>
    </div>
  </div>
</section>

<section class="sklo-section sklo-flow sklo-flow--alt">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">4 kroky</p>
      <h2>Od zaměření po nabídku</h2>
    </header>
    <ol class="sklo-steps sklo-steps--4">
      <li>
        <span class="sklo-steps__n">1</span>
        <h3>Zaměř otvor</h3>
        <p>Šířka a výška na 3 místech — vždy bereme nejmenší hodnotu. Přesný návod máme na webu.</p>
      </li>
      <li>
        <span class="sklo-steps__n">2</span>
        <h3>Pošli fotky</h3>
        <p>Celý otvor, detail stěny a podlahy. Volitelně i fotku místnosti — pomůže s doporučením.</p>
      </li>
      <li>
        <span class="sklo-steps__n">3</span>
        <h3>Navrhni dveře ve studiu</h3>
        <p>Typ, vzor skla, kování, lišta. Vidíš výsledek ještě před tím, než cokoliv objednáš.</p>
      </li>
      <li>
        <span class="sklo-steps__n">4</span>
        <h3>Dostaneš nabídku</h3>
        <p>Po odeslání ti připravíme cenovou nabídku. Bez závazku, bez tlaku.</p>
      </li>
    </ol>
  </div>
</section>

<section class="sklo-section sklo-types">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">Typy dveří</p>
      <h2>Vyber typ, detaily doladíš ve studiu</h2>
      <p>Každý typ má jiné nároky na prostor a jiný vizuální výsledek.</p>
    </header>
    <div class="sklo-type-grid">
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-dvere/otocne/')); ?>">
        <span class="sklo-type__visual sklo-type__visual--swing" aria-hidden="true"></span>
        <h3>Otočné</h3>
        <p>Klasické otevírání do místnosti nebo na chodbu. Vhodné tam, kde je prostor pro křídlo.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-dvere/posuvne/')); ?>">
        <span class="sklo-type__visual sklo-type__visual--slide" aria-hidden="true"></span>
        <h3>Posuvné</h3>
        <p>Po stěně nebo do pouzdra. Ideální tam, kde každý centimetr hraje roli.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-dvere/celosklenene/')); ?>">
        <span class="sklo-type__visual sklo-type__visual--full" aria-hidden="true"></span>
        <h3>Celoskleněné</h3>
        <p>Maximální průchod světla, minimální rám. Opticky propojí místnosti.</p>
      </a>
    </div>
  </div>
</section>

<section class="sklo-section sklo-faq" id="faq">
  <div class="sklo-wrap sklo-faq__grid">
    <header class="sklo-section__head">
      <p class="sklo-eyebrow">FAQ</p>
      <h2>Časté otázky</h2>
      <p>Krátce a věcně — zbytek dořešíme u poptávky nebo v chatu.</p>
    </header>
    <div class="sklo-faq__list">
      <details class="sklo-faq__item" open>
        <summary>Musím mít přesné zaměření?</summary>
        <p>Ano — šířka a výška na 3 místech, vždy bereme nejmenší hodnotu. Postup najdeš v <a href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">návodu na zaměření</a>. Zvládneš to sám s metrem a telefonem.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Stačí fotky z telefonu?</summary>
        <p>Ano. Potřebujeme celý otvor, detail stěny a podlahy. Čím více fotek, tím přesnější nabídka. Do studia můžeš vložit i fotku místnosti.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Děláte i montáž?</summary>
        <p>Ano. V poptávce zvolíš dveře s montáží nebo bez. Montáž domlouváme individuálně podle lokality a typu dveří.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Kdy dostanu nabídku?</summary>
        <p>Obvykle do 2 pracovních dnů od odeslání kompletních podkladů. Pokud něco chybí, ozveme se dříve.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Mohu si dveře nejdřív prohlédnout naživo?</summary>
        <p>Nemáme showroom — pracujeme na dálku. Studio na webu ti ukáže, jak dveře budou vypadat ve tvém prostoru. S výběrem skla poradíme přes chat nebo e-mail.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Jaká je minimální a maximální velikost dveří?</summary>
        <p>Pracujeme s nestandardními rozměry — to je náš základ. Limity závisí na typu dveří a skle. Zadej rozměry do studia a uvidíš, co je možné.</p>
      </details>
    </div>
  </div>
</section>

<section class="sklo-section sklo-cta-band">
  <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
    <div>
      <h2>Máš rozměry? Pojď rovnou do studia.</h2>
      <p>Vlož fotku prostoru, nastav otvor a skládej sklo se lištou. Nabídku připravíme podle toho, co si složíš.</p>
    </div>
    <div class="sklo-cta-band__actions">
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Otevřít studio</a>
      <a class="sklo-link" href="<?php echo esc_url(home_url('/kontakt/')); ?>">Nebo napiš</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
