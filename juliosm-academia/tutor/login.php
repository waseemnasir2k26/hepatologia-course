<?php
/**
 * Tutor LMS — Login Template Override
 * Branded login form
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<div class="login-page-wrap" style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(160deg,#0D3B3B 0%,#155E5E 100%);padding:2rem;">

  <div class="login-card" style="background:var(--white);border-radius:20px;padding:3rem 2.5rem;max-width:420px;width:100%;box-shadow:0 25px 60px rgba(0,0,0,0.3);">

    <div style="text-align:center;margin-bottom:2rem;">
      <span style="font-size:2.5rem;">&#9877;</span>
      <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-top:0.5rem;">
        Dr. <span style="color:var(--gold);">Julio Santiago</span>
      </h1>
      <p style="color:var(--gray-500);font-size:0.85rem;margin-top:0.25rem;">Portal de Alumnos</p>
    </div>

    <div style="height:1px;background:var(--gray-200);margin-bottom:2rem;"></div>

    <?php if ( function_exists( 'tutor_load_template' ) ) : ?>
      <?php tutor_load_template( 'login' ); ?>
    <?php else : ?>
      <?php wp_login_form( array(
        'redirect'       => home_url( '/dashboard/' ),
        'label_username' => 'Usuario o Email',
        'label_password' => 'Contraseña',
        'label_log_in'   => 'Acceder al Programa',
      ) ); ?>
    <?php endif; ?>

    <p style="text-align:center;font-size:0.8rem;color:var(--gray-400);margin-top:1.5rem;">
      ¿Problemas para ingresar?
      <a href="mailto:<?php echo esc_attr( get_theme_mod( 'jsma_email', 'contacto@juliosantiagomarcelo.com' ) ); ?>" style="color:var(--primary);">Contáctenos</a>
    </p>
  </div>

</div>

<?php get_footer(); ?>
