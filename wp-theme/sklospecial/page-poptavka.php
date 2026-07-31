<?php
/**
 * Template Name: Poptávka
 * Guided inquiry form — works with or without a katalog draft in sessionStorage.
 */

declare(strict_types=1);

get_header();

$rest = esc_url_raw(rest_url('sklo/v1/poptavka'));
$katalog = home_url('/sklenene-dvere/');
?>

<article class="sklo-page sklo-poptavka" data-sklo-poptavka data-rest="<?php echo esc_url($rest); ?>">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Provede tě poptávkou</p>
      <h1>Poptávka</h1>
      <p class="sklo-poptavka__intro">Vyplň pár údajů — ozveme se s nabídkou.</p>
    </header>

    <ol class="sklo-poptavka__steps" aria-label="Kroky poptávky">
      <li class="is-active"><span class="sklo-poptavka__step-num">1</span> Co poptáváš</li>
      <li><span class="sklo-poptavka__step-num">2</span> Tvé údaje</li>
      <li><span class="sklo-poptavka__step-num">3</span> Doprava a montáž</li>
      <li><span class="sklo-poptavka__step-num">4</span> Odeslání</li>
    </ol>

    <div class="sklo-poptavka__layout" data-poptavka-content>
      <aside class="sklo-poptavka__summary" data-poptavka-summary aria-label="Shrnutí poptávky">
        <!-- filled by JS: product card or obecná poptávka -->
      </aside>

      <form class="sklo-poptavka__form" data-poptavka-form novalidate>
        <section class="sklo-poptavka__section" data-poptavka-section="1" aria-labelledby="poptavka-s1">
          <h2 id="poptavka-s1"><span class="sklo-poptavka__sec-num">1</span> Co poptáváš</h2>
          <div data-poptavka-product-fields>
            <p class="sklo-poptavka__hint" data-poptavka-empty-hint hidden>
              Zatím nemáš vybraný produkt. Můžeš napsat obecnou poptávku níže, nebo si nejdřív
              <a href="<?php echo esc_url($katalog); ?>">vyber z nabídky</a>.
            </p>
            <label class="sklo-field" data-poptavka-code-field hidden>
              <span class="sklo-field__label">Kód produktu (volitelné)</span>
              <input class="sklo-field__input" type="text" name="kod_produktu" maxlength="80" placeholder="např. SD-120" autocomplete="off">
            </label>
          </div>
        </section>

        <section class="sklo-poptavka__section" data-poptavka-section="2" aria-labelledby="poptavka-s2">
          <h2 id="poptavka-s2"><span class="sklo-poptavka__sec-num">2</span> Tvé údaje</h2>

          <label class="sklo-field">
            <span class="sklo-field__label">Jméno a příjmení <abbr title="povinné">*</abbr></span>
            <input class="sklo-field__input" type="text" name="jmeno" autocomplete="name" required maxlength="120">
          </label>

          <label class="sklo-field">
            <span class="sklo-field__label">E-mail <abbr title="povinné">*</abbr></span>
            <input class="sklo-field__input" type="email" name="email" autocomplete="email" required maxlength="150">
          </label>

          <label class="sklo-field">
            <span class="sklo-field__label">Telefon <abbr title="povinné">*</abbr></span>
            <input class="sklo-field__input" type="tel" name="telefon" autocomplete="tel" required maxlength="30" placeholder="+420 …">
          </label>

          <label class="sklo-field">
            <span class="sklo-field__label">Adresa / PSČ a město</span>
            <input class="sklo-field__input" type="text" name="adresa" autocomplete="street-address" maxlength="200" placeholder="Pro odhad dopravy">
          </label>
        </section>

        <section class="sklo-poptavka__section" data-poptavka-section="3" aria-labelledby="poptavka-s3">
          <h2 id="poptavka-s3"><span class="sklo-poptavka__sec-num">3</span> Doprava a montáž</h2>

          <div class="sklo-poptavka__extras">
            <label class="sklo-field">
              <span class="sklo-field__label">Doprava</span>
              <select class="sklo-field__input" name="doprava">
                <option value="ne">Ne</option>
                <option value="ano">Ano, chci návrh dopravy</option>
              </select>
            </label>
            <label class="sklo-field">
              <span class="sklo-field__label">Montáž</span>
              <select class="sklo-field__input" name="montaz">
                <option value="ne">Ne</option>
                <option value="ano">Ano, chci montáž</option>
                <option value="konzultace">Jen konzultaci</option>
              </select>
            </label>
          </div>
        </section>

        <section class="sklo-poptavka__section" data-poptavka-section="4" aria-labelledby="poptavka-s4">
          <h2 id="poptavka-s4"><span class="sklo-poptavka__sec-num">4</span> Odeslání</h2>

          <label class="sklo-field">
            <span class="sklo-field__label">Poznámka</span>
            <textarea class="sklo-field__input sklo-field__input--area" name="poznamka" rows="4" maxlength="2000" placeholder="Rozměry otvoru, termín, detaily…"></textarea>
          </label>

          <input type="hidden" name="order_json" value="" data-poptavka-order-json>

          <p class="sklo-poptavka__form-err" data-poptavka-err hidden></p>
          <p class="sklo-poptavka__form-ok" data-poptavka-ok hidden>Poptávka odeslána. Ozveme se co nejdřív.</p>

          <button type="submit" class="sklo-btn" data-poptavka-submit>Odeslat poptávku</button>
        </section>
      </form>
    </div>
  </div>
</article>

<?php
get_footer();
