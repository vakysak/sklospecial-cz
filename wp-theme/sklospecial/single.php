<?php
/**
 * Single post / realizace later
 */
get_header();
?>

<article class="sklo-page">
  <div class="sklo-wrap sklo-page__inner">
    <?php while (have_posts()) : the_post(); ?>
      <header class="sklo-page__head">
        <h1><?php the_title(); ?></h1>
      </header>
      <?php if (has_post_thumbnail()) : ?>
        <div class="sklo-page__thumb"><?php the_post_thumbnail('large'); ?></div>
      <?php endif; ?>
      <div class="sklo-prose">
        <?php the_content(); ?>
      </div>
    <?php endwhile; ?>
  </div>
</article>

<?php get_footer(); ?>
