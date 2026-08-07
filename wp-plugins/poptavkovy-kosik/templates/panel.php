<div id="poptavka-overlay" class="poptavka-overlay" hidden></div>
<aside id="poptavka-panel" class="poptavka-panel" aria-hidden="true" role="dialog" aria-labelledby="poptavka-panel-title">
  <div class="poptavka-panel__header">
    <h2 id="poptavka-panel-title">Vaše poptávka</h2>
    <button type="button" class="poptavka-panel__close" aria-label="Zavřít" data-poptavka-close>&times;</button>
  </div>

  <div class="poptavka-panel__body">
    <div id="poptavka-items" class="poptavka-items"></div>

    <div id="poptavka-empty" class="poptavka-empty" hidden>
      <p>Poptávka je zatím prázdná.</p>
      <p class="poptavka-empty__hint">Přidejte produkty tlačítkem „Přidat do poptávky“.</p>
    </div>

    <form id="poptavka-form" class="poptavka-form" hidden novalidate>
      <h3 class="poptavka-form__title">Kontaktní údaje</h3>
      <label class="poptavka-field">
        <span>Jméno <abbr title="povinné">*</abbr></span>
        <input type="text" name="jmeno" required autocomplete="name">
      </label>
      <label class="poptavka-field">
        <span>E-mail <abbr title="povinné">*</abbr></span>
        <input type="email" name="email" required autocomplete="email">
      </label>
      <label class="poptavka-field">
        <span>Telefon</span>
        <input type="tel" name="telefon" autocomplete="tel">
      </label>
      <label class="poptavka-field">
        <span>Zpráva</span>
        <textarea name="zprava" rows="3"></textarea>
      </label>
      <p id="poptavka-form-error" class="poptavka-form__error" hidden></p>
      <p id="poptavka-form-success" class="poptavka-form__success" hidden></p>
      <button type="submit" class="poptavka-submit" id="poptavka-submit-btn">Odeslat poptávku</button>
    </form>
  </div>

  <div class="poptavka-panel__footer" id="poptavka-footer">
    <span id="poptavka-count" class="poptavka-count">0 položek</span>
    <button type="button" class="poptavka-odeslat" id="poptavka-show-form" disabled>Odeslat poptávku</button>
  </div>
</aside>
