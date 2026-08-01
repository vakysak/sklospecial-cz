</main>

<footer class="sklo-footer">
  <div class="sklo-wrap sklo-footer__inner">
    <div class="sklo-footer__brand">
      <p class="sklo-footer__name">Sklospeciál</p>
      <p class="sklo-footer__tag">Už 30 let nás najdete na jedné adrese — pokračuje třetí generace. Rodinná firma Sklospeciál: stabilita a jistota. Pracujeme na dálku — zaměříš sám, pošleš fotky a ve studiu si složíš dveře.</p>
    </div>
    <nav class="sklo-footer__nav" aria-label="Patička">
      <?php
      wp_nav_menu([
          'theme_location' => 'footer',
          'container'      => false,
          'menu_class'     => 'footer-list',
          'fallback_cb'    => 'sklo_nav_fallback',
          'depth'          => 1,
      ]);
      ?>
    </nav>
    <div class="sklo-footer__cta">
      <a class="sklo-btn" href="<?php echo esc_url(sklo_konfigurator_url()); ?>">Navrhni si dveře</a>
      <a class="sklo-link" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Nebo napiš</a>
    </div>
  </div>
  <div class="sklo-wrap sklo-footer__meta">
    <div>
      <h3>Kontakt</h3>
      <p>Prostřední Bludovice 193<br>739 37 Horní Bludovice</p>
      <p><a href="tel:+420736134604">+420 736 134 604</a></p>
      <p><a href="mailto:info@sklospecial.cz">info@sklospecial.cz</a></p>
      <p><a href="<?php echo esc_url(home_url('/poptavka/')); ?>">Napsat poptávku</a></p>
    </div>
    <div>
      <h3>Studio</h3>
      <p><a href="<?php echo esc_url(sklo_konfigurator_url()); ?>">Navrhni si dveře</a></p>
    </div>
    <div>
      <h3>Zaměření</h3>
      <p><a href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Návod na zaměření</a></p>
    </div>
  </div>
  <div class="sklo-wrap sklo-footer__copy">
    <p>&copy; <?php echo esc_html(gmdate('Y')); ?> Sklospeciál · provozovatel Stolařství Aleš s.r.o. · IČO 29457335 · DIČ CZ29457335</p>
    <nav class="sklo-footer__legal" aria-label="Právní informace">
      <a href="<?php echo esc_url(home_url('/ochrana-osobnich-udaju/')); ?>">Ochrana osobních údajů</a>
      <a href="<?php echo esc_url(home_url('/obchodni-podminky/')); ?>">Obchodní podmínky</a>
      <a href="<?php echo esc_url(home_url('/cookies/')); ?>">Cookies</a>
    </nav>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
