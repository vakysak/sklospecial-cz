<?php
/**
 * Product catalog helpers (data from katalog-produkty-data.php + produkty.json).
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
 * Full product map keyed by code (from assets/data/produkty.json).
 *
 * @return array<string, array<string, mixed>>
 */
function sklo_produkty_json_map(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $path = get_template_directory() . '/assets/data/produkty.json';
    if (!is_readable($path)) {
        $map = [];
        return $map;
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        $map = [];
        return $map;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $map = [];
        return $map;
    }
    $products = $decoded['products'] ?? [];
    $map = is_array($products) ? $products : [];
    return $map;
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
 * Find product by SklS code (JSON first, then PHP index).
 *
 * @return array<string, mixed>|null
 */
function sklo_produkt_by_code(string $code): ?array
{
    $code = trim($code);
    if ($code === '' || !preg_match('/^SklS-\d{4}$/', $code)) {
        return null;
    }
    $json = sklo_produkty_json_map();
    if (isset($json[$code]) && is_array($json[$code])) {
        return $json[$code];
    }
    foreach (sklo_produkty_all() as $bundle) {
        if (!is_array($bundle) || empty($bundle['products']) || !is_array($bundle['products'])) {
            continue;
        }
        foreach ($bundle['products'] as $p) {
            if (!is_array($p)) {
                continue;
            }
            if ((string) ($p['code'] ?? '') === $code) {
                return $p;
            }
        }
    }
    return null;
}

/**
 * Formátuje absolutní cenu (ne „od“).
 */
function sklo_format_cena(int $kc): string
{
    return number_format($kc, 0, ',', "\u{00a0}") . ' Kč';
}

/**
 * Doplatek display: „+ X Kč“ or empty for zero.
 */
function sklo_format_doplatek(int $kc): string
{
    if ($kc <= 0) {
        return '';
    }
    return '+ ' . sklo_format_cena($kc);
}

/**
 * Detail URL on current category page.
 */
function sklo_produkt_detail_url(string $code, ?string $base = null): string
{
    $base = $base ?? (string) get_permalink();
    $sep = str_contains($base, '?') ? '&' : '?';
    return $base . $sep . 'kod=' . rawurlencode($code);
}

/**
 * Sanitize public Czech copy (no Polish leftovers).
 */
function sklo_public_product_text(string $text): string
{
    $replacements = [
        '/\bwahadłowe\b/iu' => 'kyvné',
        '/\bwahadlowe\b/iu' => 'kyvné',
        '/\botočné\b/iu' => 'kyvné',
        '/\bQuba\s*Glass\b/iu' => '',
        '/\bQubaglass\b/iu' => '',
        '/\bWybierz wariant produktu\b/iu' => 'Vyberte variantu produktu',
        '/\bWymiar drzwi\b/iu' => 'Rozměr dveří',
        '/\bWymiary drzwi\b/iu' => 'Rozměry dveří',
        '/\bUchwyt do drzwi\b/iu' => 'Madlo / úchyt',
        '/\buchwyt do drzwi\b/iu' => 'Madlo / úchyt',
        '/\bUchwyt\b/iu' => 'Madlo',
        '/\bKierunek otwierania\b/iu' => 'Směr otevírání',
        '/\bKotwa montazowa\b/iu' => 'Montážní kotva',
        '/\bKotwa montażowa\b/iu' => 'Montážní kotva',
        '/\bSzerokosc wneki\b/iu' => 'Šířka výklenku',
        '/\bSzerokość wnęki\b/iu' => 'Šířka výklenku',
        '/\bRodzaj zamka\b/iu' => 'Typ zámku',
        '/\bRodzaj zawiasow\b/iu' => 'Typ závěsů',
        '/\bRodzaj zawiasów\b/iu' => 'Typ závěsů',
        '/\bRodzaj szkła\b/iu' => 'Typ skla',
        '/\bRodzaj szkla\b/iu' => 'Typ skla',
        '/\bKolor oku[cć]\b/iu' => 'Barva kování',
        '/\bbezbarwne\b/iu' => 'čiré',
        '/\bmatowe\b/iu' => 'matné',
        '/\bgrafitowe\b/iu' => 'grafitové',
        '/\bsatyna\b/iu' => 'satin',
        '/\bczarny\b/iu' => 'černý',
        '/\bsamodomyk(?:acz)?\b/iu' => 'Samozavírač / tichý dojezd',
        '/\bsamozavírač\s*\/\s*tichý domyk\b/iu' => 'Samozavírač / tichý dojezd',
        '/\bmuszelka\b/iu' => 'mušle',
        '/\bobustronnie\b/iu' => 'oboustranně',
        '/\bwneki\b/iu' => 'výklenku',
        '/\bwnęki\b/iu' => 'výklenku',
    ];
    foreach ($replacements as $pattern => $replacement) {
        $text = preg_replace($pattern, $replacement, $text) ?? $text;
    }
    return trim(preg_replace('/\s{2,}/u', ' ', $text) ?? $text);
}

/**
 * Render full product detail card.
 *
 * @param array<string, mixed> $p
 */
function sklo_render_produkt_detail(array $p, string $back_url = ''): void
{
    $name  = sklo_public_product_text((string) ($p['name'] ?? ''));
    $code  = (string) ($p['code'] ?? '');
    $price = (int) ($p['price'] ?? 0);
    $desc  = sklo_public_product_text((string) ($p['description'] ?? ''));
    $part  = (string) ($p['partNumber'] ?? '');
    $ship  = $p['shipping_days'] ?? null;
    $avail = sklo_public_product_text((string) ($p['availability'] ?? ''));

    $images = [];
    if (!empty($p['images']) && is_array($p['images'])) {
        foreach ($p['images'] as $img) {
            $img = (string) $img;
            if ($img !== '') {
                $images[] = $img;
            }
        }
    }
    if ($images === [] && !empty($p['image'])) {
        $images[] = (string) $p['image'];
    }

    $includes = [];
    if (!empty($p['includes']) && is_array($p['includes'])) {
        foreach ($p['includes'] as $item) {
            $t = sklo_public_product_text((string) $item);
            if ($t !== '') {
                $includes[] = $t;
            }
        }
    }

    $options = [];
    if (!empty($p['options']) && is_array($p['options'])) {
        foreach ($p['options'] as $g) {
            if (!is_array($g)) {
                continue;
            }
            $label = sklo_public_product_text((string) ($g['label'] ?? ''));
            $choices = [];
            if (!empty($g['choices']) && is_array($g['choices'])) {
                foreach ($g['choices'] as $c) {
                    if (!is_array($c)) {
                        continue;
                    }
                    $cname = sklo_public_product_text((string) ($c['name'] ?? ''));
                    if ($cname === '') {
                        continue;
                    }
                    $choices[] = [
                        'name' => $cname,
                        'surcharge_czk' => (int) ($c['surcharge_czk'] ?? 0),
                    ];
                }
            }
            $options[] = [
                'label' => $label,
                'type' => (string) ($g['type'] ?? 'select'),
                'choices' => $choices,
            ];
        }
    }

    $kontakt = home_url('/kontakt/');
    $cfg_url = function_exists('sklo_konfigurator_url') ? sklo_konfigurator_url() : home_url('/');
    $options_json = wp_json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($options_json)) {
        $options_json = '[]';
    }

    if ($back_url === '') {
        $back_url = (string) get_permalink();
    }
    ?>
  <section
    class="sklo-section sklo-pdetail"
    id="produkt-detail"
    data-sklo-pdetail
    data-code="<?php echo esc_attr($code); ?>"
    data-name="<?php echo esc_attr($name); ?>"
    data-base-price="<?php echo esc_attr((string) $price); ?>"
    data-kontakt="<?php echo esc_url($kontakt); ?>"
    data-options="<?php echo esc_attr($options_json); ?>"
  >
    <div class="sklo-wrap">
      <p class="sklo-pdetail__back">
        <a class="sklo-link" href="<?php echo esc_url($back_url); ?>">← Zpět na nabídku</a>
      </p>

      <div class="sklo-pdetail__grid">
        <div class="sklo-pdetail__gallery" data-gallery>
          <?php if ($images !== []) : ?>
            <button
              type="button"
              class="sklo-pdetail__main"
              data-lightbox-open
              data-src="<?php echo esc_url($images[0]); ?>"
              data-alt="<?php echo esc_attr($name); ?>"
            >
              <img
                src="<?php echo esc_url($images[0]); ?>"
                alt="<?php echo esc_attr($name); ?>"
                width="800"
                height="800"
                loading="eager"
                decoding="async"
                data-sklo-pdetail-main
              >
            </button>
            <?php if (count($images) > 1) : ?>
              <div class="sklo-pdetail__thumbs">
                <?php foreach ($images as $i => $img) : ?>
                  <button
                    type="button"
                    class="sklo-pdetail__thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"
                    data-sklo-pdetail-thumb
                    data-src="<?php echo esc_url($img); ?>"
                    data-lightbox-open
                    data-alt="<?php echo esc_attr($name); ?>"
                    aria-label="Foto <?php echo esc_attr((string) ($i + 1)); ?>"
                  >
                    <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async" width="120" height="120">
                  </button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php else : ?>
            <div class="sklo-pdetail__main sklo-pdetail__main--empty" aria-hidden="true"></div>
          <?php endif; ?>
        </div>

        <div class="sklo-pdetail__info">
          <p class="sklo-eyebrow">Sklospeciál</p>
          <h1 class="sklo-pdetail__title"><?php echo esc_html($name); ?></h1>
          <p class="sklo-pdetail__meta">
            <span class="sklo-pdetail__code"><?php echo esc_html($code); ?></span>
            <?php if ($part !== '') : ?>
              <span class="sklo-pdetail__part">Dodavatelský kód <?php echo esc_html($part); ?></span>
            <?php endif; ?>
          </p>
          <?php if ($price > 0) : ?>
            <p class="sklo-pdetail__price" data-sklo-base-price="<?php echo esc_attr((string) $price); ?>">
              <span data-sklo-orient-label>Orientační cena:</span>
              <strong data-sklo-orient-price><?php echo esc_html(sklo_format_cena($price)); ?></strong>
            </p>
            <?php if ($options !== []) : ?>
              <p class="sklo-pdetail__price-base">Základ <?php echo esc_html(sklo_format_cena($price)); ?> · doplatky podle výběru níže</p>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($ship !== null && (int) $ship > 0) : ?>
            <p class="sklo-pdetail__ship">Expedice cca <?php echo esc_html((string) (int) $ship); ?> pracovních dní</p>
          <?php endif; ?>
          <?php if ($avail !== '') : ?>
            <p class="sklo-pdetail__avail"><?php echo esc_html($avail); ?></p>
          <?php endif; ?>

          <p class="sklo-pdetail__note">Finální nabídka podle rozměrů a dostupnosti.</p>
        </div>
      </div>

      <?php if ($desc !== '') : ?>
        <div class="sklo-pdetail__block">
          <h2>Popis</h2>
          <div class="sklo-pdetail__prose">
            <?php
            $paras = preg_split('/\n+/', $desc) ?: [];
            foreach ($paras as $para) {
                $para = trim($para);
                if ($para === '') {
                    continue;
                }
                if (str_starts_with($para, '- ')) {
                    echo '<p class="sklo-pdetail__li">' . esc_html($para) . '</p>';
                } else {
                    echo '<p>' . esc_html($para) . '</p>';
                }
            }
            ?>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($includes !== []) : ?>
        <div class="sklo-pdetail__block">
          <h2>V sadě</h2>
          <ul class="sklo-katalog-list">
            <?php foreach ($includes as $item) : ?>
              <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($options !== []) : ?>
        <div class="sklo-pdetail__block sklo-pdetail__options" data-sklo-options>
          <h2>Varianta produktu</h2>
          <p class="sklo-pdetail__options-lead">Vyberte variantu produktu — jednotlivé volby mohou změnit cenu.</p>
          <div class="sklo-pdetail__option-groups">
            <?php foreach ($options as $gi => $g) :
                $label = (string) ($g['label'] ?? '');
                $choices = is_array($g['choices'] ?? null) ? $g['choices'] : [];
                $type = (string) ($g['type'] ?? 'select');
                $field_id = 'sklo-opt-' . (string) $gi;
                $free_default_idx = null;
                foreach ($choices as $ci => $c) {
                    if ((int) ($c['surcharge_czk'] ?? 0) === 0) {
                        $free_default_idx = (int) $ci;
                        break;
                    }
                }
                ?>
              <div class="sklo-pdetail__option" data-sklo-option-group>
                <?php if ($type === 'text' && $choices === []) : ?>
                  <h3><?php echo esc_html($label !== '' ? $label : 'Možnost'); ?></h3>
                  <p class="sklo-pdetail__option-hint">Zadáš při poptávce (rozměry).</p>
                <?php elseif ($choices !== []) : ?>
                  <label class="sklo-pdetail__select-label" for="<?php echo esc_attr($field_id); ?>">
                    <?php echo esc_html($label !== '' ? $label : 'Možnost'); ?>
                  </label>
                  <select
                    class="sklo-produkt-opt sklo-pdetail__select"
                    id="<?php echo esc_attr($field_id); ?>"
                    name="opt_<?php echo esc_attr((string) $gi); ?>"
                    data-sklo-option-select
                    data-opt-label="<?php echo esc_attr($label !== '' ? $label : 'Možnost'); ?>"
                    required
                    aria-label="<?php echo esc_attr($label !== '' ? $label : 'Možnost'); ?>"
                  >
                    <option value="" data-surcharge="0"<?php echo $free_default_idx === null ? ' selected' : ''; ?>>Vyberte…</option>
                    <?php foreach ($choices as $ci => $c) :
                        $cname = (string) ($c['name'] ?? '');
                        $sur = (int) ($c['surcharge_czk'] ?? 0);
                        $opt_label = $cname . ($sur > 0 ? ' (+ ' . number_format($sur, 0, ',', "\u{00a0}") . ' Kč)' : '');
                        $selected = $free_default_idx !== null && (int) $ci === $free_default_idx;
                        ?>
                      <option
                        value="<?php echo esc_attr($cname); ?>"
                        data-surcharge="<?php echo esc_attr((string) $sur); ?>"
                        <?php echo $selected ? ' selected' : ''; ?>
                      ><?php echo esc_html($opt_label); ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="sklo-pdetail__options-err" data-sklo-options-err hidden>Vyber všechny povinné varianty.</p>
        </div>
      <?php endif; ?>

      <div class="sklo-pdetail__cta sklo-pdetail__cta--bottom sklo-pdetail__cta--triple">
        <button type="button" class="sklo-btn" data-sklo-order-selected>Objednat vybrané</button>
        <a class="sklo-btn sklo-btn--ghost" href="<?php echo esc_url($kontakt); ?>">Nenašel jsi co hledáš? Pošli nezávaznou poptávku</a>
        <a class="sklo-link" href="<?php echo esc_url($cfg_url); ?>" target="_blank" rel="noopener">Konfigurátor</a>
      </div>
    </div>
  </section>
    <?php
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
    $base = (string) get_permalink();
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
            $name  = sklo_public_product_text((string) ($p['name'] ?? ''));
            $price = (int) ($p['price'] ?? 0);
            $img   = (string) ($p['image'] ?? '');
            $code  = (string) ($p['code'] ?? '');
            $hidden = $i >= $initial;
            $detail = $code !== '' ? sklo_produkt_detail_url($code, $base) : '';
            $psec = (string) ($p['section'] ?? '');
            ?>
          <article
            class="sklo-produkty__item<?php echo $hidden ? ' is-collapsed' : ''; ?>"
            data-category="<?php echo esc_attr($psec); ?>"
            <?php echo $hidden ? 'hidden' : ''; ?>
          >
            <?php if ($detail !== '') : ?>
              <a class="sklo-produkty__link" href="<?php echo esc_url($detail); ?>">
            <?php endif; ?>
            <?php if ($img !== '') : ?>
              <span class="sklo-produkty__media">
                <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy" decoding="async" width="400" height="400">
              </span>
            <?php else : ?>
              <div class="sklo-produkty__media sklo-produkty__media--empty" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="sklo-produkty__body">
              <h3 class="sklo-produkty__name"><?php echo esc_html($name); ?></h3>
              <?php if ($code !== '') : ?>
                <p class="sklo-produkty__code"><?php echo esc_html($code); ?></p>
              <?php endif; ?>
              <?php if ($price > 0) : ?>
                <p class="sklo-produkty__price"><?php echo esc_html(sklo_format_cena_od($price)); ?></p>
              <?php endif; ?>
              <p class="sklo-produkty__more-link">Detail a doplatky</p>
            </div>
            <?php if ($detail !== '') : ?>
              </a>
            <?php endif; ?>
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
