<?php
/**
 * Default page
 */
get_header();
?>

<article class="sklo-page">
  <div class="sklo-wrap sklo-page__inner">
    <?php while (have_posts()) : the_post(); ?>
      <header class="sklo-page__head">
        <h1><?php the_title(); ?></h1>
      </header>
      <div class="sklo-prose">
        <?php the_content(); ?>
      </div>
    <?php endwhile; ?>
  </div>
</article>

<?php get_footer(); ?>
