<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <?php jsma_logo( 'footer' ); ?>
        <p>Programa educativo para pacientes con cirrosis hepática y sus familias.</p>
      </div>

      <div class="footer-col">
        <h4>El Programa</h4>
        <a href="<?php echo esc_url( home_url( '/#modulos' ) ); ?>">Módulos</a>
        <a href="<?php echo esc_url( home_url( '/#bonos' ) ); ?>">Bonos</a>
        <a href="<?php echo esc_url( home_url( '/#inscripcion' ) ); ?>">Inscripción</a>
        <a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>">Preguntas Frecuentes</a>
      </div>

      <div class="footer-col">
        <h4>Soporte</h4>
        <?php
        $email = get_theme_mod( 'jsma_email', 'contacto@juliosantiagomarcelo.com' );
        $main  = get_theme_mod( 'jsma_main_site', 'https://juliosantiagomarcelo.com' );
        ?>
        <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
        <a href="<?php echo esc_url( $main ); ?>" target="_blank" rel="noopener">Sitio Principal del Doctor</a>
        <?php if ( is_user_logged_in() ) : ?>
          <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Cerrar Sesión</a>
        <?php else : ?>
          <a href="<?php echo esc_url( wp_login_url( home_url() ) ); ?>">Iniciar Sesión</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?php echo date( 'Y' ); ?> Dr. Julio Santiago Marcelo. Todos los derechos reservados.</p>
      <div>
        <a href="<?php echo esc_url( $main ); ?>" target="_blank" rel="noopener">juliosantiagomarcelo.com</a>
        &nbsp;&bull;&nbsp;
        <span>Desarrollado por <a href="https://www.skynetjoe.com" target="_blank" rel="noopener" style="color:var(--gold);">Skynet Labs</a></span>
      </div>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
