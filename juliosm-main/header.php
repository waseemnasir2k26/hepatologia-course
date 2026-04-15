<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Dr. Julio Santiago Marcelo — Gastroenterólogo. Especialista en enfermedades del hígado y sistema digestivo. Programa educativo para pacientes con cirrosis.">
<meta property="og:title" content="Dr. Julio Santiago Marcelo — Gastroenterólogo">
<meta property="og:description" content="Especialista en enfermedades del hígado y sistema digestivo. Programa educativo para pacientes con cirrosis.">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo esc_url( home_url( '/' ) ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar" id="navbar">
  <div class="nav-container">
    <?php juliosm_logo( 'header' ); ?>

    <div class="nav-links" id="navLinks">
      <?php if ( is_front_page() ) : ?>
        <a href="#sobre">Sobre Mí</a>
        <a href="#servicios">Servicios</a>
        <a href="#programa">El Programa</a>
        <a href="#testimonios">Testimonios</a>
        <a href="#contacto">Contacto</a>
        <a href="#faq">FAQ</a>
      <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a>
        <a href="<?php echo esc_url( home_url( '/#servicios' ) ); ?>">Servicios</a>
        <a href="<?php echo esc_url( home_url( '/#programa' ) ); ?>">El Programa</a>
        <a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">Contacto</a>
      <?php endif; ?>
      <a href="<?php echo esc_url( get_theme_mod( 'juliosm_portal_url', 'https://portal.juliosantiagomarcelo.com' ) ); ?>" class="nav-cta" target="_blank" rel="noopener">
        Acceder al Curso →
      </a>
    </div>

    <button class="nav-toggle" id="navToggle" aria-label="Menú">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
