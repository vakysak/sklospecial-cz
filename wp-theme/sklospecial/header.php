<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="sklo-atmosphere" aria-hidden="true"></div>

<header class="sklo-header">
  <div class="sklo-wrap sklo-header__inner">
    <a class="sklo-brand" href="<?php echo esc_url(home_url('/')); ?>">
      <span class="sklo-brand__mark" aria-hidden="true"></span>
      <span class="sklo-brand__name">Sklospeciál</span>
    </a>

    <button class="sklo-nav-toggle" type="button" aria-expanded="false" aria-controls="sklo-nav" data-nav-toggle>
      <span class="sklo-nav-toggle__bars" aria-hidden="true"></span>
      <span class="screen-reader-text">Menu</span>
    </button>

    <nav id="sklo-nav" class="sklo-nav" data-nav>
      <?php
      wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'nav-list',
          'fallback_cb'    => 'sklo_nav_fallback',
          'depth'          => 2,
      ]);
      ?>
      <a class="sklo-btn sklo-btn--sm" href="<?php echo esc_url(sklo_konfigurator_url()); ?>">Navrhni si dveře</a>
    </nav>
  </div>
</header>

<main id="content" class="sklo-main">
