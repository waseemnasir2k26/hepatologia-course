<?php get_header(); ?>

<div class="error-404" style="min-height:80vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:4rem 2rem;">
  <h1 style="font-size:6rem;color:var(--primary);margin-bottom:0.5rem;">404</h1>
  <h2 style="font-size:1.5rem;margin-bottom:1rem;">Página no encontrada</h2>
  <p style="color:var(--gray-500);margin-bottom:2rem;">La página que busca no existe o ha sido movida.</p>
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-lg">Volver al Inicio</a>
</div>

<?php get_footer(); ?>
