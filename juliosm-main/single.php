<?php get_header(); ?>

<div class="page-content">
  <?php while ( have_posts() ) : the_post(); ?>
    <article>
      <p style="font-size:0.82rem;color:var(--gray-400);margin-bottom:0.5rem;"><?php echo get_the_date(); ?></p>
      <h1><?php the_title(); ?></h1>
      <?php if ( has_post_thumbnail() ) : ?>
        <div style="margin:1.5rem 0;">
          <?php the_post_thumbnail( 'large', array( 'style' => 'border-radius:var(--radius-lg);' ) ); ?>
        </div>
      <?php endif; ?>
      <?php the_content(); ?>
    </article>
    <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid var(--gray-200);">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-outline">← Volver al Inicio</a>
    </div>
  <?php endwhile; ?>
</div>

<?php get_footer(); ?>
