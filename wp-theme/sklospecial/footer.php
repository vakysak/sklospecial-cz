</main>

<footer class="sklo-footer">
  <div class="sklo-wrap sklo-footer__inner">
    <div class="sklo-footer__brand">
      <p class="sklo-footer__name">Sklospeciál</p>
      <p class="sklo-footer__tag">Skleněné dveře na míru — pošli rozměry a fotky, připravíme nabídku.</p>
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
      <a class="sklo-link" href="<?php echo esc_url(home_url('/kontakt/')); ?>">Nebo napiš</a>
    </div>
  </div>
  <div class="sklo-wrap sklo-footer__meta">
    <div>
      <h3>Kontakt</h3>
      <p><a href="<?php echo esc_url(home_url('/kontakt/')); ?>">Napsat poptávku</a></p>
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
    <p>&copy; <?php echo esc_html(gmdate('Y')); ?> Sklospeciál</p>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
