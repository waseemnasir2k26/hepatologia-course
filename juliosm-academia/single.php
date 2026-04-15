<?php get_header(); ?>

<div class="page-content">
  <?php while ( have_posts() ) : the_post(); ?>
    <article>
      <p style="font-size:0.82rem;color:var(--gray-400);"><?php echo get_the_date(); ?></p>
      <h1><?php the_title(); ?></h1>
      <?php if ( has_post_thumbnail() ) : ?>
        <div style="margin:1.5rem 0;">
          <?php the_post_thumbnail( 'large', array( 'style' => 'border-radius:16px;' ) ); ?>
        </div>
      <?php endif; ?>
      <?php the_content(); ?>
    </article>
  <?php endwhile; ?>
</div>

<?php get_footer(); ?>
