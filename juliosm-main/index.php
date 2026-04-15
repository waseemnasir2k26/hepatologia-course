<?php get_header(); ?>

<div class="page-content">
  <?php if ( have_posts() ) : ?>
    <h1>Blog</h1>
    <?php while ( have_posts() ) : the_post(); ?>
      <article style="margin-bottom:3rem;padding-bottom:2rem;border-bottom:1px solid var(--gray-200);">
        <h2><a href="<?php the_permalink(); ?>" style="color:var(--primary);"><?php the_title(); ?></a></h2>
        <p style="font-size:0.82rem;color:var(--gray-400);margin-bottom:0.75rem;"><?php echo get_the_date(); ?></p>
        <?php the_excerpt(); ?>
        <a href="<?php the_permalink(); ?>" class="btn btn-outline" style="margin-top:0.5rem;">Leer más →</a>
      </article>
    <?php endwhile; ?>
    <?php the_posts_pagination(); ?>
  <?php else : ?>
    <h1>No se encontraron publicaciones</h1>
    <p>No hay contenido disponible por el momento.</p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
