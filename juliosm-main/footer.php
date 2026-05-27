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
        <a href="<?php echo esc_url( home_url( '/#programa' ) ); ?>">El Programa</a>
        <a href="<?php echo esc_url( home_url( '/#testimonios' ) ); ?>">Testimonios</a>
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

<?php
$wa_raw   = get_theme_mod( 'juliosm_whatsapp', '51917623134' );
$wa_phone = preg_replace( '/\D+/', '', $wa_raw );
if ( $wa_raw && strpos( $wa_raw, 'http' ) === 0 ) {
    $wa_url = esc_url( $wa_raw );
} elseif ( $wa_phone ) {
    $wa_url = 'https://wa.me/' . $wa_phone . '?text=' . rawurlencode( 'Hola Dr. Julio, me interesa el programa Cirrosis 360.' );
} else {
    $wa_url = '';
}
?>
<?php if ( $wa_url ) : ?>
<a class="wa-float" href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" aria-label="Contactar por WhatsApp">
  <svg viewBox="0 0 24 24" width="32" height="32" fill="currentColor" aria-hidden="true">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.165-.173.198-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.611-.916-2.207-.242-.579-.487-.501-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
    <path d="M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.142 1.595 5.945L0 24l6.335-1.652a11.882 11.882 0 0 0 5.71 1.447h.006c6.583 0 11.941-5.336 11.944-11.896 0-3.18-1.26-6.167-3.475-8.45zM12.05 21.785h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.976 1.003-3.643-.235-.374a9.836 9.836 0 0 1-1.51-5.26c.001-5.45 4.458-9.884 9.942-9.884 2.654 0 5.147 1.032 7.021 2.905 1.874 1.873 2.906 4.362 2.905 7.008-.003 5.45-4.458 9.863-9.985 9.863z"/>
  </svg>
</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
