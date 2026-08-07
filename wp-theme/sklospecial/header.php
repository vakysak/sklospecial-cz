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

<div class="sklo-topbar">
  <div class="sklo-wrap sklo-topbar__inner">
    <?php if (function_exists('sklo_render_studio_coming_sticky')) : ?>
      <?php sklo_render_studio_coming_sticky('sklo-topbar__msg'); ?>
    <?php else : ?>
      <p class="sklo-topbar__msg">Skleněné dveře na míru — pošli rozměry a fotky</p>
    <?php endif; ?>
    <div class="sklo-topbar__links">
      <a href="<?php echo esc_url(home_url('/navod-na-zamereni/')); ?>">Návod na zaměření</a>
      <a href="<?php echo esc_url(home_url('/kontakt/')); ?>">Kontakt</a>
    </div>
  </div>
</div>

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
      <?php if (shortcode_exists('poptavka_ikona')) : ?>
        <span class="sklo-header__poptavka"><?php echo do_shortcode('[poptavka_ikona]'); ?></span>
      <?php endif; ?>
      <?php if (function_exists('sklo_render_studio_coming_actions')) : ?>
        <?php sklo_render_studio_coming_actions('sklo-header__cta', true, false); ?>
      <?php else : ?>
        <a class="sklo-btn sklo-btn--sm" href="<?php echo esc_url(home_url('/poptavka/')); ?>">Poptávka</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main id="content" class="sklo-main">
