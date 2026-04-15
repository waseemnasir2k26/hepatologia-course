<?php
/**
 * Tutor LMS — Dashboard Template Override
 * Branded student dashboard
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<div class="tutor-dashboard-wrap">
  <div class="container" style="padding-top:6rem;padding-bottom:4rem;">

    <div class="dashboard-header" data-animate="fade-up">
      <h1 class="section-title">
        Bienvenido, <span class="text-primary"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
      </h1>
      <p style="color:var(--gray-500);font-size:1rem;">
        Aquí encontrará su progreso, cursos activos y recursos del programa.
      </p>
    </div>

    <!-- Expiration Notice -->
    <div class="expiration-notice" style="margin:2rem 0;padding:1.25rem 1.5rem;background:var(--primary-light);border:1px solid var(--primary);border-radius:12px;display:flex;align-items:center;gap:1rem;">
      <span style="font-size:1.5rem;">&#128337;</span>
      <div>
        <strong style="color:var(--primary-dark);">Su acceso al programa</strong>
        <p style="font-size:0.85rem;color:var(--gray-600);margin:0;">Recuerde que tiene 6 meses de acceso desde su fecha de inscripción. Aproveche todo el contenido disponible.</p>
      </div>
    </div>

    <?php if ( function_exists( 'tutor_load_template' ) ) : ?>
      <?php tutor_load_template( 'dashboard.index' ); ?>
    <?php else : ?>
      <div style="text-align:center;padding:4rem 0;">
        <p style="color:var(--gray-500);">El dashboard de Tutor LMS se cargará aquí cuando el plugin esté activo.</p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary" style="margin-top:1rem;">Volver al Inicio</a>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php get_footer(); ?>
