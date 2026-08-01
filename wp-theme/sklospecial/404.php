<?php
/**
 * 404 — stránka nenalezena
 */
get_header();
?>

<section class="sklo-page sklo-404">
  <div class="sklo-wrap sklo-404__inner">
    <p class="sklo-eyebrow">404</p>
    <h1>Stránka nenalezena</h1>
    <p class="sklo-404__lead">Tahle adresa neexistuje nebo byla přesunuta. Podívej se do katalogu dveří, napiš poptávku, nebo se vrať na úvod.</p>
    <div class="sklo-404__actions">
      <a class="sklo-btn sklo-btn--filled" href="<?php echo esc_url(home_url('/sklenene-dvere/')); ?>">Katalog dveří</a>
      <a class="sklo-btn" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a>
      <a class="sklo-link" href="<?php echo esc_url(home_url('/')); ?>">Domů</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
