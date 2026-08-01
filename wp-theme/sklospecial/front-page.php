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
    <h1 class="sklo-hero__title">Skleněné dveře a sklo na míru — online, bez showroomu</h1>
    <p class="sklo-hero__lead">Posuvné dveře, sprchové kouty, zábradlí a stříšky. Zaměříš sám, my vyrobíme — nabídku připravíme podle tvých rozměrů a fotek. Bez showroomu, bez zbytečných kol.</p>
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
      <span>Orientační ceny od v katalogu. Finální nabídka vždy podle konkrétního otvoru a provedení.</span>
    </div>
  </div>
</section>

<section class="sklo-section sklo-about">
  <div class="sklo-wrap sklo-about__grid">
    <div class="sklo-about__copy">
      <p class="sklo-eyebrow">O nás</p>
      <h2>Už 30&nbsp;let nás najdete na jedné adrese. Pokračuje třetí generace.</h2>
      <p>Rodinná firma Sklospeciál. Stavíme na stabilitě a jistotě — od zaměření po montáž víš, s kým jednáš. Skleněné dveře na míru vyřídíš na dálku: zaměříš sám, pošleš fotky a ve studiu si složíš dveře.</p>
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

<section class="sklo-section sklo-trust" id="proc-sklospecial" aria-labelledby="trust-heading">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">Jistota</p>
      <h2 id="trust-heading">Proč Sklospeciál</h2>
      <p>Bez falešných hvězdiček — jen to, na čem stavíme zakázky každý den.</p>
    </header>
    <div class="sklo-trust__grid">
      <div class="sklo-trust__block">
        <h3>Garance a jistota</h3>
        <ul class="sklo-trust__list">
          <li>Rodinná firma — třetí generace na jedné adrese</li>
          <li>30&nbsp;let zkušeností se sklem na míru</li>
          <li>Výroba na míru podle tvých rozměrů a fotek</li>
          <li>Montáž s předávacím protokolem — víš, co přebíráš</li>
        </ul>
      </div>
      <div class="sklo-trust__block">
        <h3>Jak probíhá úhrada</h3>
        <ol class="sklo-trust__pay">
          <li>Nabídka</li>
          <li>Faktura</li>
          <li>Výroba</li>
          <li>Expedice</li>
          <li>Montáž na místě</li>
        </ol>
        <p class="sklo-trust__note">Výroba startuje po úhradě faktury. Montáž se hradí na místě po podepsání předávacího protokolu. Detailní postup je na stránce <a href="<?php echo esc_url(home_url('/kontakt/')); ?>">Kontakt</a>.</p>
      </div>
    </div>
  </div>
</section>

<section class="sklo-section sklo-types">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">Nabídka</p>
      <h2>Sklo do domu — od dveří po stříšky</h2>
      <p>Hlavní směry nabídky. U dveří doladíš detaily ve <a href="<?php echo esc_url($cfg); ?>">studiu</a>.</p>
    </header>
    <div class="sklo-type-grid sklo-type-grid--6">
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-dvere/')); ?>">
        <img class="sklo-type__image" src="https://wordpress-jzxqv0aq7w5lf4f12nkwgj00.46.225.122.108.sslip.io/wp-content/uploads/2026/07/posuvne-sklenene-dvere-matne-sklo.webp" alt="Skleněné dveře" width="507" height="626" loading="lazy" decoding="async">
        <h3>Dveře</h3>
        <p>Posuvné, kyvné, otevírané i vzory skla.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sprchove-kouty/')); ?>">
        <img class="sklo-type__image" src="https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/SklS-0608.jpg" alt="Skleněné sprchové kouty" width="400" height="400" loading="lazy" decoding="async">
        <h3>Sprchy</h3>
        <p>Walk-in stěny a kouty na míru.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/zabradli/')); ?>">
        <img class="sklo-type__image" src="https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/SklS-0734.jpg" alt="Skleněné zábradlí" width="400" height="400" loading="lazy" decoding="async">
        <h3>Zábradlí</h3>
        <p>DIY, s montáží i profily.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/strisky/')); ?>">
        <img class="sklo-type__image" src="https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/SklS-0832.jpg" alt="Skleněné stříšky" width="400" height="400" loading="lazy" decoding="async">
        <h3>Stříšky</h3>
        <p>Na konzolách, táhlech i skladem.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/sklenene-pricky/')); ?>">
        <img class="sklo-type__image" src="https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/SklS-0326.jpg" alt="Skleněné příčky" width="400" height="400" loading="lazy" decoding="async">
        <h3>Příčky</h3>
        <p>Skleněné příčky a zabudování.</p>
      </a>
      <a class="sklo-type" href="<?php echo esc_url(home_url('/francouzske-balkony/')); ?>">
        <img class="sklo-type__image" src="https://c93wrq6ujvo02103pn26bxbr.46.225.122.108.sslip.io/public/katalog-img/SklS-0645.jpg" alt="Francouzské balkony" width="400" height="400" loading="lazy" decoding="async">
        <h3>Balkony</h3>
        <p>Francouzské balkony ze skla.</p>
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
      <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Nebo napiš</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
