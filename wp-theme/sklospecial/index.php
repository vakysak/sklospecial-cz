<?php
/**
 * Fallback index
 */
get_header();
?>

<section class="sklo-page">
  <div class="sklo-wrap sklo-page__inner">
    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('sklo-card-post'); ?>>
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <div class="sklo-prose"><?php the_excerpt(); ?></div>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <p>Zatím tu nic není.</p>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
