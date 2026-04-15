<!-- ═══════ FOOTER ═══════ -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <?php juliosm_logo( 'footer' ); ?>
        <p><?php echo esc_html( get_theme_mod( 'juliosm_specialty', 'Gastroenterólogo' ) ); ?> &bull; Especialista en enfermedades del hígado y sistema digestivo.</p>
      </div>

      <div class="footer-col">
        <h4>Enlaces</h4>
        <a href="<?php echo esc_url( home_url( '/#sobre' ) ); ?>">Sobre el Doctor</a>
        <a href="<?php echo esc_url( home_url( '/#servicios' ) ); ?>">Servicios</a>
        <a href="<?php echo esc_url( home_url( '/#programa' ) ); ?>">El Programa</a>
        <a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">Contacto</a>
        <a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>">Preguntas Frecuentes</a>
      </div>

      <div class="footer-col">
        <h4>Programa Educativo</h4>
        <a href="<?php echo esc_url( get_theme_mod( 'juliosm_portal_url', 'https://portal.juliosantiagomarcelo.com' ) ); ?>" target="_blank" rel="noopener">Portal de Alumnos</a>
        <a href="<?php echo esc_url( get_theme_mod( 'juliosm_mercadopago', 'https://mpago.la/1sXKiM3' ) ); ?>" target="_blank" rel="noopener">Inscribirme al Programa</a>
        <?php
        $email = get_theme_mod( 'juliosm_email', 'contacto@juliosantiagomarcelo.com' );
        if ( $email ) : ?>
          <a href="mailto:<?php echo esc_attr( $email ); ?>">Soporte: <?php echo esc_html( $email ); ?></a>
        <?php endif; ?>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?php echo date( 'Y' ); ?> Dr. Julio Santiago Marcelo. Todos los derechos reservados.</p>
      <div>
        <a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>">Privacidad</a>
        &nbsp;&bull;&nbsp;
        <a href="<?php echo esc_url( home_url( '/terminos/' ) ); ?>">Términos</a>
        &nbsp;&bull;&nbsp;
        <span>Desarrollado por <a href="https://www.skynetjoe.com" target="_blank" rel="noopener" style="color:var(--gold);">Skynet Labs</a></span>
      </div>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
