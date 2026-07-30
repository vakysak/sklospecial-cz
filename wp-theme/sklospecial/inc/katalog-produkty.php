<?php
/**
 * Product catalog helpers (data from katalog-produkty-data.php).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, array{min_price: ?int, count: int, section_mins: array<string, int>, products: list<array<string, mixed>>}>
 */
function sklo_produkty_all(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $path = get_template_directory() . '/inc/katalog-produkty-data.php';
    if (!is_readable($path)) {
        $data = [];
        return $data;
    }
    /** @var array<string, mixed> $loaded */
    $loaded = require $path;
    $data = is_array($loaded) ? $loaded : [];
    return $data;
}

/**
 * @return array{min_price: ?int, count: int, section_mins: array<string, int>, products: list<array<string, mixed>>}|null
 */
function sklo_produkty_for_slug(string $slug): ?array
{
    $all = sklo_produkty_all();
    return isset($all[$slug]) && is_array($all[$slug]) ? $all[$slug] : null;
}

/**
 * Formátuje absolutní cenu (ne „od“).
 */
function sklo_format_cena(int $kc): string
{
    return number_format($kc, 0, ',', "\u{00a0}") . ' Kč';
}

/**
 * Render product grid for a category slug (optional section filter).
 */
function sklo_render_produkty_grid(string $slug, ?string $section = null, int $initial = 12): void
{
    $bundle = sklo_produkty_for_slug($slug);
    if ($bundle === null || empty($bundle['products'])) {
        return;
    }

    $products = $bundle['products'];
    if ($section !== null && $section !== '') {
        $products = array_values(array_filter(
            $products,
            static fn(array $p): bool => (string) ($p['section'] ?? '') === $section
        ));
    }
    if ($products === []) {
        return;
    }

    $total = count($products);
    $note = sklo_cena_note();
    $grid_id = 'sklo-prod-' . preg_replace('/[^a-z0-9\-]+/', '-', $slug . ($section ? '-' . $section : ''));
    ?>
  <section class="sklo-section sklo-produkty" id="<?php echo esc_attr($grid_id); ?>" data-sklo-produkty data-initial="<?php echo esc_attr((string) $initial); ?>">
    <div class="sklo-wrap">
      <header class="sklo-section__head sklo-section__head--center">
        <p class="sklo-eyebrow">Produkty</p>
        <h2><?php echo $section ? 'Vybrané produkty' : 'Nabídka v této kategorii'; ?></h2>
        <p><span data-sklo-produkty-count><?php echo esc_html((string) $total); ?></span> položek · <?php echo esc_html($note); ?></p>
      </header>
      <div class="sklo-produkty__grid" data-gallery>
        <?php foreach ($products as $i => $p) :
            $name  = (string) ($p['name'] ?? '');
            $price = (int) ($p['price'] ?? 0);
            $img   = (string) ($p['image'] ?? '');
            $code  = (string) ($p['code'] ?? '');
            $hidden = $i >= $initial;
            ?>
          <?php
            $psec = (string) ($p['section'] ?? '');
          ?>
          <article
            class="sklo-produkty__item<?php echo $hidden ? ' is-collapsed' : ''; ?>"
            data-category="<?php echo esc_attr($psec); ?>"
            <?php echo $hidden ? 'hidden' : ''; ?>
          >
            <?php if ($img !== '') : ?>
              <button
                type="button"
                class="sklo-produkty__media"
                data-lightbox-open
                data-src="<?php echo esc_url($img); ?>"
                data-alt="<?php echo esc_attr($name); ?>"
              >
                <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async" width="400" height="400">
              </button>
            <?php else : ?>
              <div class="sklo-produkty__media sklo-produkty__media--empty" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="sklo-produkty__body">
              <h3 class="sklo-produkty__name"><?php echo esc_html($name); ?></h3>
              <?php if ($code !== '') : ?>
                <p class="sklo-produkty__code"><?php echo esc_html($code); ?></p>
              <?php endif; ?>
              <?php if ($price > 0) : ?>
                <p class="sklo-produkty__price"><?php echo esc_html(sklo_format_cena($price)); ?></p>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if ($total > $initial) : ?>
        <div class="sklo-produkty__more">
          <button type="button" class="sklo-btn sklo-btn--ghost" data-sklo-produkty-more>
            Zobrazit další (<?php echo esc_html((string) ($total - $initial)); ?>)
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>
    <?php
}
