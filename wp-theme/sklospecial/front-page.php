<?php
/**
 * Homepage
 */
get_header();

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
      <?php if (function_exists('sklo_render_studio_coming_actions')) : ?>
        <?php sklo_render_studio_coming_actions('sklo-hero__var-c', false, true); ?>
      <?php endif; ?>
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
      <strong>Konfigurátor připravujeme</strong>
      <span>Teď nejjednodušší cesta: WhatsApp, nebo nezávazná poptávka s rozměry a fotkami.</span>
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
      <p>Rodinná firma Sklospeciál. Stavíme na stabilitě a jistotě — od zaměření po montáž víš, s kým jednáš. Skleněné dveře na míru vyřídíš na dálku: zaměříš sám, pošleš fotky a ozvi se přes WhatsApp nebo poptávku.</p>
      <div class="sklo-about__actions">
        <?php if (function_exists('sklo_render_studio_coming_actions')) : ?>
          <?php sklo_render_studio_coming_actions('sklo-about__var-c'); ?>
        <?php endif; ?>
        <a class="sklo-link" href="<?php echo esc_url(home_url('/o-nas/')); ?>">Víc o nás</a>
      </div>
    </div>
    <div class="sklo-about__panel" aria-hidden="true">
      <div class="sklo-about__glass"></div>
    </div>
  </div>
</section>

<?php
$proof_uploads = content_url('uploads/2026/07');
$proof_items = [
    ['file' => 'posuvne-sklenene-dvere-matne-sklo.webp', 'caption' => 'Posuvné dveře — matné sklo', 'w' => 507, 'h' => 626],
    ['file' => 'sklene-zabradli-s-masivnim-drevem.webp', 'caption' => 'Zábradlí se dřevěným madlem', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-dvere-v-interieru.webp', 'caption' => 'Skleněné dveře v interiéru', 'w' => 626, 'h' => 470],
    ['file' => 'moderni-interier-sklene-prcky-dreveny-stolek.webp', 'caption' => 'Skleněné stěny', 'w' => 626, 'h' => 417],
    ['file' => 'sklenene-zabradli-na-zakazku.webp', 'caption' => 'Celoskleněné zábradlí', 'w' => 352, 'h' => 626],
    ['file' => 'sklenene-dvere-s-matnym-sklem.webp', 'caption' => 'Posuvné dveře s matným sklem', 'w' => 626, 'h' => 417],
];
?>
<section class="sklo-section sklo-proof" id="ukazky" aria-labelledby="proof-heading">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">Ukázky</p>
      <h2 id="proof-heading">Ukázky skleněných řešení</h2>
      <p>Ilustrační fotografie typů řešení (inspirace) — nejde o dokumentaci konkrétních zakázek. Další ukázky na stránce Realizace.</p>
    </header>
    <div class="sklo-proof__grid">
      <?php foreach ($proof_items as $i => $item) :
          $src = trailingslashit($proof_uploads) . $item['file'];
          ?>
        <figure class="sklo-proof__item" style="--i: <?php echo esc_attr((string) $i); ?>">
          <img
            src="<?php echo esc_url($src); ?>"
            alt="<?php echo esc_attr($item['caption']); ?>"
            width="<?php echo esc_attr((string) $item['w']); ?>"
            height="<?php echo esc_attr((string) $item['h']); ?>"
            loading="lazy"
            decoding="async"
          >
          <figcaption><?php echo esc_html($item['caption']); ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
    <p class="sklo-proof__more">
      <a class="sklo-link" href="<?php echo esc_url(home_url('/realizace/')); ?>">Více ukázek</a>
    </p>
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
        <h3>Pošli poptávku</h3>
        <p>Typ, vzor skla, kování, lišta — napiš to do WhatsAppu nebo poptávky. Nabídku připravíme podle tvých podkladů.</p>
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

<?php
$recenze_stats = sklo_recenze_stats();
$recenze_featured = sklo_recenze_featured(8);
$recenze_stars = static function (int $n): string {
    $n = max(1, min(5, $n));
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
};
?>
<section class="sklo-section sklo-reviews" id="recenze" aria-labelledby="reviews-heading">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">Důvěra</p>
      <h2 id="reviews-heading">Co říkají zákazníci</h2>
      <p class="sklo-reviews__agg">
        <span class="sklo-recenze-stars" aria-hidden="true"><?php echo esc_html($recenze_stars(5)); ?></span>
        průměr <?php echo esc_html(number_format_i18n($recenze_stats['avg'], 1)); ?> z 5
        · <?php echo esc_html((string) $recenze_stats['count']); ?> hodnocení
      </p>
    </header>
    <div class="sklo-reviews__track" data-reviews-track>
      <?php foreach ($recenze_featured as $i => $r) : ?>
        <blockquote class="sklo-reviews__quote<?php echo $i === 0 ? ' is-active' : ''; ?>" data-reviews-slide<?php echo $i === 0 ? '' : ' hidden'; ?>>
          <p class="sklo-recenze-stars" aria-label="<?php echo esc_attr((string) $r['stars'] . ' z 5'); ?>"><?php echo esc_html($recenze_stars((int) $r['stars'])); ?></p>
          <p class="sklo-reviews__text">„<?php echo esc_html((string) $r['text']); ?>“</p>
          <footer class="sklo-reviews__meta">
            <cite><?php echo esc_html((string) $r['name']); ?></cite>
            <span><?php echo esc_html((string) $r['city']); ?></span>
          </footer>
        </blockquote>
      <?php endforeach; ?>
    </div>
    <div class="sklo-reviews__controls" data-reviews-controls>
      <button type="button" class="sklo-reviews__dot is-active" data-reviews-goto="0" aria-label="Recenze 1"></button>
      <?php for ($i = 1, $n = count($recenze_featured); $i < $n; $i++) : ?>
        <button type="button" class="sklo-reviews__dot" data-reviews-goto="<?php echo esc_attr((string) $i); ?>" aria-label="Recenze <?php echo esc_attr((string) ($i + 1)); ?>"></button>
      <?php endfor; ?>
    </div>
    <p class="sklo-reviews__more">
      <a class="sklo-link" href="<?php echo esc_url(home_url('/recenze/')); ?>">Všechny recenze</a>
    </p>
  </div>
</section>

<?php
$katalog = function_exists('sklo_katalog') ? sklo_katalog() : [];
$home_types = [
    [
        'slug' => 'sklenene-dvere',
        'href' => home_url('/sklenene-dvere/'),
        'img'  => content_url('uploads/2026/07/posuvne-sklenene-dvere-matne-sklo.webp'),
        'alt'  => 'Skleněné dveře',
        'w'    => 507,
        'h'    => 626,
        'title'=> 'Dveře',
        'text' => 'Posuvné, kyvné, otevírané i vzory skla.',
        'fallback_price' => 10100,
    ],
    [
        'slug' => 'sprchove-kouty',
        'href' => home_url('/sprchove-kouty/'),
        'img'  => sklo_katalog_img_url('SklS-0608'),
        'alt'  => 'Skleněné sprchové kouty',
        'w'    => 400,
        'h'    => 400,
        'title'=> 'Sprchy',
        'text' => 'Walk-in stěny a kouty na míru.',
        'fallback_price' => 4600,
    ],
    [
        'slug' => 'zabradli',
        'href' => home_url('/zabradli/'),
        'img'  => sklo_katalog_img_url('SklS-0734'),
        'alt'  => 'Skleněné zábradlí',
        'w'    => 400,
        'h'    => 400,
        'title'=> 'Zábradlí',
        'text' => 'DIY, s montáží i profily.',
        'fallback_price' => 2800,
    ],
    [
        'slug' => 'strisky',
        'href' => home_url('/strisky/'),
        'img'  => sklo_katalog_img_url('SklS-0832'),
        'alt'  => 'Skleněné stříšky',
        'w'    => 400,
        'h'    => 400,
        'title'=> 'Stříšky',
        'text' => 'Na konzolách, táhlech i skladem.',
        'fallback_price' => 7100,
    ],
    [
        'slug' => 'sklenene-steny',
        'href' => home_url('/sklenene-steny/'),
        'img'  => sklo_katalog_img_url('SklS-0326'),
        'alt'  => 'Skleněné stěny',
        'w'    => 400,
        'h'    => 400,
        'title'=> 'Skleněné stěny',
        'text' => 'Skleněné stěny a zabudování.',
        'fallback_price' => 21000,
    ],
    [
        'slug' => 'francouzske-balkony',
        'href' => home_url('/francouzske-balkony/'),
        'img'  => sklo_katalog_img_url('SklS-0645'),
        'alt'  => 'Francouzské balkony',
        'w'    => 400,
        'h'    => 400,
        'title'=> 'Balkony',
        'text' => 'Francouzské balkony ze skla.',
        'fallback_price' => 7100,
    ],
];
?>
<section class="sklo-section sklo-types">
  <div class="sklo-wrap">
    <header class="sklo-section__head sklo-section__head--center">
      <p class="sklo-eyebrow">Nabídka</p>
      <h2>Sklo do domu — od dveří po stříšky</h2>
      <p>Hlavní směry nabídky. Orientační ceny „od“ — finální nabídka podle rozměrů. Detaily doladíme přes WhatsApp nebo poptávku.</p>
    </header>
    <div class="sklo-type-grid sklo-type-grid--6">
      <?php foreach ($home_types as $type) :
          $cat = $katalog[$type['slug']] ?? null;
          $price = is_array($cat) && !empty($cat['price_from'])
              ? (string) $cat['price_from']
              : (function_exists('sklo_format_cena_od')
                  ? sklo_format_cena_od((int) $type['fallback_price'])
                  : '');
          ?>
        <a class="sklo-type" href="<?php echo esc_url($type['href']); ?>">
          <img class="sklo-type__image" src="<?php echo esc_url($type['img']); ?>" alt="<?php echo esc_attr($type['alt']); ?>" width="<?php echo esc_attr((string) $type['w']); ?>" height="<?php echo esc_attr((string) $type['h']); ?>" loading="lazy" decoding="async">
          <h3><?php echo esc_html($type['title']); ?></h3>
          <p><?php echo esc_html($type['text']); ?></p>
          <?php if ($price !== '') : ?>
            <p class="sklo-type__from"><?php echo esc_html($price); ?></p>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
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
        <p>Ano. Potřebujeme celý otvor, detail stěny a podlahy. Čím více fotek, tím přesnější nabídka. Fotku místnosti můžeš poslat přes WhatsApp nebo poptávku.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Děláte i montáž?</summary>
        <p>Ano. V poptávce zvolíš výrobek s montáží nebo bez. Orientačně: dveře od 2&nbsp;500&nbsp;Kč/ks, sprcha od 3&nbsp;500&nbsp;Kč, zábradlí od 1&nbsp;500&nbsp;Kč/bm — finální cena v nabídce. Více na stránce <a href="<?php echo esc_url(home_url('/doprava/#montaz')); ?>">doprava a montáž</a>.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Kdy dostanu nabídku?</summary>
        <p>Obvykle do 2 pracovních dnů od odeslání kompletních podkladů. Pokud něco chybí, ozveme se dříve.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Mohu si dveře nejdřív prohlédnout naživo?</summary>
        <p>Nemáme showroom — pracujeme na dálku. S výběrem skla a typu poradíme přes WhatsApp, chat nebo e-mail. Konfigurátor připravujeme.</p>
      </details>
      <details class="sklo-faq__item">
        <summary>Jaká je minimální a maximální velikost dveří?</summary>
        <p>Pracujeme s nestandardními rozměry — to je náš základ. Limity závisí na typu dveří a skle. Pošli rozměry přes WhatsApp nebo poptávku a ověříme, co je možné.</p>
      </details>
    </div>
  </div>
</section>

<section class="sklo-section sklo-cta-band">
  <div class="sklo-wrap sklo-cta-band__inner sklo-cta-band__inner--wide">
    <?php if (function_exists('sklo_render_studio_coming_block')) : ?>
      <?php sklo_render_studio_coming_block('sklo-var-c--band'); ?>
    <?php else : ?>
      <div>
        <h2>Konfigurátor připravujeme.</h2>
        <p>Teď nejjednodušší cesta: WhatsApp, nebo nezávazná poptávka s rozměry a fotkami.</p>
      </div>
      <div class="sklo-cta-band__actions">
        <a class="sklo-btn" href="https://wa.me/420736134604" target="_blank" rel="noopener noreferrer">WhatsApp</a>
        <a class="sklo-btn sklo-btn--ghost" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
