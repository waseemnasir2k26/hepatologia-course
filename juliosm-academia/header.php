<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Portal de Alumnos — Programa para Pacientes con Cirrosis. Dr. Julio Santiago Marcelo, Gastroenterólogo.">
<meta name="robots" content="noindex, nofollow">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar" id="navbar">
  <div class="nav-container">
    <?php jsma_logo( 'header' ); ?>

    <div class="nav-links" id="navLinks">
      <?php if ( is_front_page() ) : ?>
        <a href="#modulos">Módulos</a>
        <a href="#bonos">Bonos</a>
        <a href="#doctor">El Doctor</a>
        <a href="#inscripcion">Inscripción</a>
        <a href="#faq">FAQ</a>
      <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a>
        <a href="<?php echo esc_url( home_url( '/#modulos' ) ); ?>">Módulos</a>
        <a href="<?php echo esc_url( home_url( '/#inscripcion' ) ); ?>">Inscripción</a>
      <?php endif; ?>

      <?php if ( is_user_logged_in() ) : ?>
        <?php if ( jsma_is_tutor_active() ) : ?>
          <a href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>" class="nav-cta">Mi Curso →</a>
        <?php else : ?>
          <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="nav-cta">Mi Curso →</a>
        <?php endif; ?>
      <?php else : ?>
        <a href="<?php echo esc_url( wp_login_url( home_url() ) ); ?>" style="color:rgba(255,255,255,0.8);">Iniciar Sesión</a>
        <a href="<?php echo esc_url( get_theme_mod( 'jsma_mercadopago', 'https://mpago.la/1sXKiM3' ) ); ?>" class="nav-cta" target="_blank" rel="noopener">Inscribirme →</a>
      <?php endif; ?>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Menú">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
