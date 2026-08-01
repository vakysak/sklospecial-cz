<?php
/**
 * Template: Recenze — zákaznická hodnocení
 */
get_header();

$stats = sklo_recenze_stats();
$all = sklo_recenze_all();
$cfg = sklo_konfigurator_url();

$fmt_date = static function (string $ymd): string {
    $ts = strtotime($ymd . ' 12:00:00');
    if ($ts === false) {
        return $ymd;
    }
    return date_i18n('j. n. Y', $ts);
};

$stars_html = static function (int $n): string {
    $n = max(1, min(5, $n));
    return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
};
?>

<article class="sklo-recenze-page">
  <div class="sklo-wrap">
    <header class="sklo-page__head sklo-recenze-page__head">
      <h1>Recenze</h1>
      <p class="sklo-recenze-page__lead">Hodnocení od zákazníků — dveře, sprchy, zábradlí, stříšky, příčky i balkony. Pracujeme na dálku: zaměření, fotky a nabídka online.</p>
      <p class="sklo-recenze-page__agg" aria-label="Souhrn hodnocení">
        <span class="sklo-recenze-stars" aria-hidden="true"><?php echo esc_html($stars_html(5)); ?></span>
        průměr <strong><?php echo esc_html(number_format_i18n($stats['avg'], 1)); ?></strong> z 5
        <span class="sklo-recenze-page__dot" aria-hidden="true">·</span>
        <?php echo esc_html((string) $stats['count']); ?> hodnocení
      </p>
    </header>

    <div class="sklo-recenze-filters" data-recenze-filters role="group" aria-label="Filtrovat podle typu">
      <button type="button" class="sklo-recenze-chip is-active" data-filter="all">Vše</button>
      <button type="button" class="sklo-recenze-chip" data-filter="dvere">Dveře</button>
      <button type="button" class="sklo-recenze-chip" data-filter="sprcha">Sprcha</button>
      <button type="button" class="sklo-recenze-chip" data-filter="zabradli">Zábradlí</button>
      <button type="button" class="sklo-recenze-chip" data-filter="strisky">Stříšky</button>
      <button type="button" class="sklo-recenze-chip" data-filter="pricky">Příčky</button>
      <button type="button" class="sklo-recenze-chip" data-filter="balkony">Balkony</button>
    </div>

    <div class="sklo-recenze-list" data-recenze-list data-page-size="24">
      <?php foreach ($all as $i => $r) :
          $cat = (string) ($r['category'] ?? 'ostatni');
          ?>
        <blockquote
          class="sklo-recenze-item"
          data-category="<?php echo esc_attr($cat); ?>"
          data-index="<?php echo esc_attr((string) $i); ?>"
          <?php if ($i >= 24) : ?>hidden<?php endif; ?>
        >
          <p class="sklo-recenze-stars" aria-label="<?php echo esc_attr((string) $r['stars'] . ' z 5'); ?>"><?php echo esc_html($stars_html((int) $r['stars'])); ?></p>
          <p class="sklo-recenze-item__text"><?php echo esc_html((string) $r['text']); ?></p>
          <footer class="sklo-recenze-item__meta">
            <cite><?php echo esc_html((string) $r['name']); ?></cite>
            <span><?php echo esc_html((string) $r['city']); ?></span>
            <time datetime="<?php echo esc_attr((string) $r['date']); ?>"><?php echo esc_html($fmt_date((string) $r['date'])); ?></time>
          </footer>
        </blockquote>
      <?php endforeach; ?>
    </div>

    <div class="sklo-recenze-more">
      <button type="button" class="sklo-btn sklo-btn--ghost" data-recenze-more>Zobrazit další</button>
      <p class="sklo-recenze-more__count" data-recenze-count hidden></p>
    </div>

    <div class="sklo-recenze-page__cta">
      <p>Chceš podobný výsledek u sebe?</p>
      <a class="sklo-btn" href="<?php echo esc_url($cfg); ?>">Navrhni si dveře</a>
      <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Nebo napiš</a>
      <?php if (function_exists('sklo_render_cta_sla')) : ?>
        <?php sklo_render_cta_sla(); ?>
      <?php endif; ?>
    </div>
  </div>
</article>

<?php get_footer(); ?>
