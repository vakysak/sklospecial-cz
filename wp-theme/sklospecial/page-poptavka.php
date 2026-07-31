<?php
/**
 * Template Name: Poptávka
 * Product inquiry / order draft from katalog (sessionStorage).
 */

declare(strict_types=1);

get_header();

$rest = esc_url_raw(rest_url('sklo/v1/poptavka'));
?>

<article class="sklo-page sklo-poptavka" data-sklo-poptavka data-rest="<?php echo esc_url($rest); ?>">
  <div class="sklo-wrap sklo-page__inner">
    <header class="sklo-page__head">
      <p class="sklo-eyebrow">Objednávka / poptávka</p>
      <h1>Poptávka</h1>
      <p class="sklo-poptavka__intro">Zkontroluj výběr a pošli nám poptávku — ozveme se s konkrétní nabídkou.</p>
    </header>

    <div class="sklo-poptavka__empty" data-poptavka-empty hidden>
      <p>Nemáš zatím vybraný produkt. Vyber variantu v katalogu a klikni na <strong>Objednat vybrané</strong>.</p>
      <p><a class="sklo-btn" href="<?php echo esc_url(home_url('/sklenene-dvere/')); ?>">Zpět do katalogu</a></p>
    </div>

    <div class="sklo-poptavka__layout" data-poptavka-content hidden>
      <aside class="sklo-poptavka__summary" data-poptavka-summary aria-label="Shrnutí objednávky">
        <!-- filled by JS -->
      </aside>

      <form class="sklo-poptavka__form" data-poptavka-form novalidate>
        <h2>Kontaktní údaje</h2>

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

        <label class="sklo-field">
          <span class="sklo-field__label">Poznámka</span>
          <textarea class="sklo-field__input sklo-field__input--area" name="poznamka" rows="4" maxlength="2000" placeholder="Rozměry otvoru, termín, detaily…"></textarea>
        </label>

        <input type="hidden" name="order_json" value="" data-poptavka-order-json>

        <p class="sklo-poptavka__form-err" data-poptavka-err hidden></p>
        <p class="sklo-poptavka__form-ok" data-poptavka-ok hidden>Poptávka odeslána. Ozveme se co nejdřív.</p>

        <button type="submit" class="sklo-btn" data-poptavka-submit>Odeslat poptávku</button>
      </form>
    </div>
  </div>
</article>

<?php
get_footer();
