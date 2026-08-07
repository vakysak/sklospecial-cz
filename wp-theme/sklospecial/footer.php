</main>

<footer class="sklo-footer">
  <div class="sklo-wrap sklo-footer__inner">
    <div class="sklo-footer__brand">
      <p class="sklo-footer__name">Sklospeciál</p>
      <p class="sklo-footer__tag">Už 30 let nás najdete na jedné adrese — pokračuje třetí generace. Rodinná firma Sklospeciál: stabilita a jistota. Pracujeme na dálku — zaměříš sám, pošleš fotky a ozvi se přes WhatsApp nebo poptávku.</p>
      <div class="sklo-footer__cta">
        <?php if (function_exists('sklo_render_studio_coming_actions')) : ?>
          <?php sklo_render_studio_coming_actions('sklo-footer__var-c'); ?>
        <?php else : ?>
          <a class="sklo-btn" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a>
        <?php endif; ?>
        <?php if (function_exists('sklo_render_cta_sla')) : ?>
          <?php sklo_render_cta_sla('sklo-cta-sla--footer', false, false); ?>
        <?php endif; ?>
      </div>
    </div>

    <nav class="sklo-footer__cols" aria-label="Patička">
      <div class="sklo-footer__col">
        <h3>Produkty</h3>
        <ul class="sklo-footer__list">
          <li><a href="<?php echo esc_url(home_url('/sklenene-dvere/')); ?>">Skleněné dveře</a></li>
          <li><a href="<?php echo esc_url(home_url('/sprchove-kouty/')); ?>">Sprchové kouty</a></li>
          <li><a href="<?php echo esc_url(home_url('/zabradli/')); ?>">Zábradlí</a></li>
          <li><a href="<?php echo esc_url(home_url('/kovani-zabradli/')); ?>">Kování zábradlí</a></li>
          <li><a href="<?php echo esc_url(home_url('/strisky/')); ?>">Stříšky</a></li>
          <li><a href="<?php echo esc_url(home_url('/kovani-strisky/')); ?>">Kování stříšky</a></li>
          <li><a href="<?php echo esc_url(home_url('/sklenene-steny/')); ?>">Skleněné stěny</a></li>
          <li><a href="<?php echo esc_url(home_url('/francouzske-balkony/')); ?>">Francouzské balkony</a></li>
        </ul>
      </div>
      <div class="sklo-footer__col">
        <h3>Jak to funguje</h3>
        <ul class="sklo-footer__list">
          <li><a href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Návod na zaměření</a></li>
          <li><a href="<?php echo esc_url(home_url('/pruvodce/')); ?>">Průvodce</a></li>
          <li><a href="<?php echo esc_url(home_url('/doprava/')); ?>">Doprava a montáž</a></li>
          <li><a href="<?php echo esc_url(home_url('/platba/')); ?>">Platba</a></li>
          <li><a href="<?php echo esc_url(home_url('/doba-realizace/')); ?>">Doba realizace</a></li>
          <li><a href="<?php echo esc_url(home_url('/zaruka/')); ?>">Záruka a servis</a></li>
          <li><a href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a></li>
          <li><a href="<?php echo esc_url(function_exists('sklo_whatsapp_url') ? sklo_whatsapp_url() : 'https://wa.me/420736134604'); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
        </ul>
      </div>
      <div class="sklo-footer__col">
        <h3>O firmě</h3>
        <ul class="sklo-footer__list">
          <li><a href="<?php echo esc_url(home_url('/o-nas/')); ?>">O nás</a></li>
          <li><a href="https://stolarstviales.cz/" rel="noopener noreferrer">Stolařství Aleš</a></li>
          <li><a href="<?php echo esc_url(home_url('/realizace/')); ?>">Ukázky řešení</a></li>
          <li><a href="<?php echo esc_url(home_url('/recenze/')); ?>">Recenze</a></li>
          <li><a href="<?php echo esc_url(home_url('/kontakt/')); ?>">Kontakt</a></li>
          <li><a href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a></li>
        </ul>
        <p class="sklo-footer__addr">
          Prostřední Bludovice 193, 739 37 Horní Bludovice<br>
          <a href="tel:+420736134604">+420 736 134 604</a><br>
          <a href="mailto:info@sklospecial.eu">info@sklospecial.eu</a>
        </p>
      </div>
    </nav>
  </div>

  <div class="sklo-wrap sklo-footer__copy">
    <p>&copy; <?php echo esc_html(gmdate('Y')); ?> Sklospeciál · provozovatel Stolařství Aleš s.r.o. · IČO 29457335 · DIČ CZ29457335</p>
    <nav class="sklo-footer__legal" aria-label="Právní informace">
      <a href="<?php echo esc_url(home_url('/ochrana-osobnich-udaju/')); ?>">Ochrana osobních údajů</a>
      <a href="<?php echo esc_url(home_url('/obchodni-podminky/')); ?>">Obchodní podmínky</a>
      <a href="<?php echo esc_url(home_url('/cookies/')); ?>">Cookies</a>
      <a href="<?php echo esc_url(home_url('/mapa-stranek/')); ?>">Mapa stránek</a>
    </nav>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
