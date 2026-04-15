<?php get_header(); ?>

<div class="error-404">
  <h1>404</h1>
  <h2 style="font-size:1.5rem;margin-bottom:1rem;">Página no encontrada</h2>
  <p style="color:var(--gray-500);margin-bottom:2rem;">Lo sentimos, la página que busca no existe o ha sido movida.</p>
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-lg">Volver al Inicio</a>
</div>

<?php get_footer(); ?>
