<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if ( jsma_is_landing() ) : ?>
<meta name="description" content="Cirrosis 360 — Programa práctico para cuidadores familiares de pacientes con cirrosis hepática. Dr. Julio Santiago Marcelo, Gastroenterólogo.">
<meta name="robots" content="index, follow">
<meta property="og:title" content="Cirrosis 360 — Dr. Julio Santiago Marcelo">
<meta property="og:description" content="Programa práctico para cuidadores familiares de pacientes con cirrosis hepática.">
<meta property="og:type" content="website">
<?php else : ?>
<meta name="description" content="Portal de Alumnos — Cirrosis 360. Dr. Julio Santiago Marcelo, Gastroenterólogo.">
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar" id="navbar">
  <div class="nav-container">
    <?php jsma_logo( 'header' ); ?>

    <div class="nav-links" id="navLinks">
      <?php if ( jsma_is_portal() ) : ?>
        <?php if ( is_front_page() ) : ?>
          <a href="#hero">Inicio</a>
          <?php if ( is_user_logged_in() ) : ?>
            <a href="#mi-curso">Mi Curso</a>
          <?php else : ?>
            <a href="#login-card">Iniciar Sesión</a>
          <?php endif; ?>
          <a href="#soporte">Soporte</a>
        <?php else : ?>
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a>
          <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( home_url( '/#mi-curso' ) ); ?>">Mi Curso</a>
          <?php endif; ?>
          <a href="<?php echo esc_url( home_url( '/#soporte' ) ); ?>">Soporte</a>
        <?php endif; ?>
      <?php elseif ( is_front_page() ) : ?>
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

      <?php
      $jsma_portal = esc_url( get_theme_mod( 'jsma_portal_url', 'https://portal.juliosantiagomarcelo.com/dashboard/' ) );
      ?>
      <?php if ( is_user_logged_in() ) : ?>
        <?php if ( jsma_is_tutor_active() ) : ?>
          <a href="<?php echo esc_url( tutor_utils()->tutor_dashboard_url() ); ?>" class="nav-cta">Mi Curso →</a>
        <?php elseif ( jsma_is_landing() ) : ?>
          <a href="<?php echo $jsma_portal; ?>" class="nav-cta" target="_blank" rel="noopener">Mi Curso →</a>
        <?php else : ?>
          <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>" class="nav-cta">Mi Curso →</a>
        <?php endif; ?>
      <?php else : ?>
        <?php if ( jsma_is_portal() ) : ?>
          <a href="<?php echo esc_url( wp_login_url( home_url( '/dashboard/' ) ) ); ?>" class="nav-cta">Iniciar Sesión →</a>
        <?php else : ?>
          <a href="<?php echo $jsma_portal; ?>" target="_blank" rel="noopener" style="color:rgba(255,255,255,0.8);">Acceso Alumnos</a>
          <a href="<?php echo esc_url( get_theme_mod( 'jsma_mercadopago', 'https://mpago.la/1sXKiM3' ) ); ?>" class="nav-cta" target="_blank" rel="noopener">Inscribirme →</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Menú">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
