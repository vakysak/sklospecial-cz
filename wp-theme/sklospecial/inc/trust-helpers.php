<?php
/**
 * Trust / conversion microcopy helpers (ratings, SLA, risk, next steps).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WhatsApp deep link (same number as tel: sitewide).
 */
function sklo_whatsapp_url(): string
{
    return 'https://wa.me/420736134604';
}

/**
 * Sitewide CTA response promise.
 */
function sklo_cta_sla_text(): string
{
    return 'Obratem, nejpozději do 1 pracovního dne · nabídka bez závazku';
}

/**
 * Short SLA for tight UI (same promise).
 */
function sklo_cta_sla_text_short(): string
{
    return 'Obratem · nejpozději do 1 PD · nabídka bez závazku';
}

/**
 * Prodloužená záruka: 10 % z katalogové ceny výrobku (bez dopravy/montáže).
 */
function sklo_warranty_surcharge_czk(int $base_price_czk): int
{
    if ($base_price_czk <= 0) {
        return 0;
    }
    return (int) round($base_price_czk * 0.10);
}

/**
 * Formatted aggregate rating label from sklo_recenze_stats().
 * Example: „4,8 ★ · 475 hodnocení“
 */
function sklo_recenze_rating_label(): string
{
    $stats = function_exists('sklo_recenze_stats')
        ? sklo_recenze_stats()
        : ['avg' => 0.0, 'count' => 0];
    $avg = number_format_i18n((float) ($stats['avg'] ?? 0), 1);
    $count = number_format_i18n((int) ($stats['count'] ?? 0), 0);
    return $avg . ' ★ · ' . $count . ' hodnocení';
}

/**
 * Stars + aggregate rating linking to /recenze/.
 *
 * @param string $class Extra BEM modifier classes (space-separated).
 */
function sklo_render_recenze_rating_link(string $class = ''): void
{
    $stats = function_exists('sklo_recenze_stats')
        ? sklo_recenze_stats()
        : ['avg' => 0.0, 'count' => 0];
    $count = (int) ($stats['count'] ?? 0);
    if ($count <= 0) {
        return;
    }
    $label = sklo_recenze_rating_label();
    $classes = trim('sklo-rating-link ' . $class);
    $url = home_url('/recenze/');
    $aria = sprintf(
        'Průměrné hodnocení %s z 5 na základě %s hodnocení — zobrazit recenze',
        number_format_i18n((float) ($stats['avg'] ?? 0), 1),
        number_format_i18n($count, 0)
    );
    ?>
  <a class="<?php echo esc_attr($classes); ?>" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($aria); ?>">
    <span class="sklo-rating-link__stars" aria-hidden="true">★★★★★</span>
    <span class="sklo-rating-link__label"><?php echo esc_html($label); ?></span>
  </a>
    <?php
}

/**
 * SLA microcopy near primary CTAs (+ optional WhatsApp nudge).
 *
 * @param string $class Extra BEM modifier classes (space-separated).
 * @param bool   $short Use compact SLA line.
 * @param bool   $whatsapp Show „Nejrychleji na WhatsApp“ link.
 */
function sklo_render_cta_sla(string $class = '', bool $short = false, bool $whatsapp = true): void
{
    $classes = trim('sklo-cta-sla ' . $class);
    $text = $short ? sklo_cta_sla_text_short() : sklo_cta_sla_text();
    echo '<p class="' . esc_attr($classes) . '">';
    echo '<span class="sklo-cta-sla__text">' . esc_html($text) . '</span>';
    if ($whatsapp) {
        echo ' <a class="sklo-cta-sla__wa" href="' . esc_url(sklo_whatsapp_url()) . '"'
            . ' target="_blank" rel="noopener noreferrer">'
            . esc_html('Nejrychleji na WhatsApp')
            . '</a>';
    }
    echo '</p>';
}

/**
 * Short risk-reduction block (zaměření, záruka, reklamace, předání).
 * Aligns with Kontakt / OP — no invented warranty years.
 *
 * @param 'tykani'|'vykani' $tone
 */
function sklo_render_risk_block(string $tone = 'tykani', string $class = ''): void
{
    $formal = $tone === 'vykani';
    $op = home_url('/obchodni-podminky/');
    $zaruka = home_url('/zaruka/');
    $navod = home_url('/navod-na-zamereni/');
    $classes = trim('sklo-risk ' . $class);

    $items = $formal
        ? [
            [
                'title' => 'Zaměření',
                'text'  => 'Rozměry zvládnete podle <a href="' . esc_url($navod) . '">návodu</a>. Pokud si nejste jistí, domluvíme volitelné zaměření na místě — ideálně před výrobou.',
            ],
            [
                'title' => 'Záruka',
                'text'  => 'Zákonná práva z vad + volitelná prodloužená záruka (+1 rok, 10&nbsp;% ceny výrobku bez dopravy a montáže — částka v&nbsp;Kč je u produktu). Detail: <a href="' . esc_url($zaruka) . '">záruka a servis</a> a <a href="' . esc_url($op) . '">obchodní podmínky</a>.',
            ],
            [
                'title' => 'Reklamace',
                'text'  => 'Vadné zboží nebo montáž řešíme podle obchodních podmínek — napište na <a href="mailto:info@sklospecial.eu">info@sklospecial.eu</a> nebo volejte.',
            ],
            [
                'title' => 'Předání',
                'text'  => 'Montáž končí předávacím protokolem. Víte, co přebíráte; montáž se hradí na místě po podpisu.',
            ],
        ]
        : [
            [
                'title' => 'Zaměření',
                'text'  => 'Rozměry zvládneš podle <a href="' . esc_url($navod) . '">návodu</a>. Pokud si nejsi jistý, domluvíme volitelné zaměření na místě — ideálně před výrobou.',
            ],
            [
                'title' => 'Záruka',
                'text'  => 'Zákonná práva z vad + volitelná prodloužená záruka (+1 rok, 10&nbsp;% ceny výrobku bez dopravy a montáže — částku v&nbsp;Kč vidíš u produktu). Detail: <a href="' . esc_url($zaruka) . '">záruka a servis</a> a <a href="' . esc_url($op) . '">obchodní podmínky</a>.',
            ],
            [
                'title' => 'Reklamace',
                'text'  => 'Vadné zboží nebo montáž řešíme podle obchodních podmínek — napiš na <a href="mailto:info@sklospecial.eu">info@sklospecial.eu</a> nebo zavolej.',
            ],
            [
                'title' => 'Předání',
                'text'  => 'Montáž končí předávacím protokolem. Víš, co přebíráš; montáž se hradí na místě po podpisu.',
            ],
        ];

    $heading = $formal ? 'Co když něco nesedí' : 'Co když něco nesedí';
    $lead = $formal
        ? 'Krátce a věcně — bez překvapení po cestě.'
        : 'Krátce a věcně — bez překvapení po cestě.';
    ?>
  <aside class="<?php echo esc_attr($classes); ?>" aria-labelledby="sklo-risk-heading">
    <header class="sklo-risk__head">
      <h2 id="sklo-risk-heading" class="sklo-risk__title"><?php echo esc_html($heading); ?></h2>
      <p class="sklo-risk__lead"><?php echo esc_html($lead); ?></p>
    </header>
    <ul class="sklo-risk__list">
      <?php foreach ($items as $item) : ?>
        <li class="sklo-risk__item">
          <strong class="sklo-risk__item-title"><?php echo esc_html($item['title']); ?></strong>
          <p class="sklo-risk__item-text"><?php echo wp_kses($item['text'], [
              'a' => ['href' => true],
          ]); ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  </aside>
    <?php
}

/**
 * Orientační ceny montáže (customer-facing tiers).
 *
 * @return list<array{label: string, from: string}>
 */
function sklo_montaz_orientacni_ceny(): array
{
    return [
        [
            'label' => 'Skleněné dveře (otočné / posuvné)',
            'from'  => 'od 2 500 Kč / ks',
        ],
        [
            'label' => 'Sprchové kouty a stěny',
            'from'  => 'od 3 500 Kč / sestava',
        ],
        [
            'label' => 'Zábradlí a příčky',
            'from'  => 'od 1 500 Kč / bm',
        ],
    ];
}

/**
 * Short montáž pricing blurb + optional tier list.
 */
function sklo_render_montaz_orientacni(bool $with_list = true, string $class = ''): void
{
    $doprava = home_url('/doprava/#montaz');
    $classes = trim('sklo-montaz-orient ' . $class);
    ?>
  <div class="<?php echo esc_attr($classes); ?>">
    <p>
      Orientační ceny montáže (práce). Finální částku včetně dojezdu uvedeme v nabídce po zaměření / podle lokality.
      <?php if (!$with_list) : ?>
        Přehled: <a href="<?php echo esc_url($doprava); ?>">doprava a montáž</a>.
      <?php endif; ?>
    </p>
    <?php if ($with_list) : ?>
      <ul>
        <?php foreach (sklo_montaz_orientacni_ceny() as $tier) : ?>
          <li>
            <strong><?php echo esc_html($tier['label']); ?></strong>
            — <?php echo esc_html($tier['from']); ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p>Nejde o pevný ceník — atypické kotvení, výška, přístup nebo delší dojezd cenu posunou. Můžeš objednat výrobek i bez montáže.</p>
    <?php endif; ?>
  </div>
    <?php
}

/**
 * „Co se stane dál“ after successful poptávka submit.
 */
function sklo_render_poptavka_next_steps(): void
{
    ?>
  <div class="sklo-next-steps" data-poptavka-ok hidden>
    <p class="sklo-next-steps__ok">Poptávka odeslána. Díky.</p>
    <h2 class="sklo-next-steps__title">Co se stane dál</h2>
    <ol class="sklo-next-steps__list">
      <li><strong>Obratem, nejpozději do 1 pracovního dne</strong> se ozveme na telefon nebo e-mail.</li>
      <li>Pokud chybí rozměry nebo fotky, upřesníme je — nabídka bez závazku.</li>
      <li>Připravíš si ideálně šířku a výšku otvoru (3 měření) a 2–3 fotky prostoru.</li>
    </ol>
    <p class="sklo-next-steps__hint">Nejrychleji nám napiš na <a href="<?php echo esc_url(sklo_whatsapp_url()); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>. Mezitím můžeš projít <a href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">návod na zaměření</a>.</p>
  </div>
    <?php
}
