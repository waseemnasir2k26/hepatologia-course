<?php
/**
 * Registration page — front-end form posting to init handler.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$err = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : '';
$registered = ! empty( $_GET['registered'] );
$courses = C360_LMS_Config::courses();
$selected_course = isset( $_GET['course'] ) ? sanitize_title( wp_unslash( $_GET['course'] ) ) : '';
$selected_id = 0;
if ( $selected_course ) {
    $c = C360_LMS_Config::course( $selected_course );
    if ( $c ) $selected_id = (int) $c['id'];
}
?>
<section class="c360-section c360-register">
    <h1>Crear Cuenta</h1>
    <p class="c360-subtitle">Tu cuenta debe ser aprobada por el administrador antes de poder iniciar sesión.</p>

    <?php if ( $registered ) : ?>
        <div class="c360-notice c360-notice-success">
            <h3>✓ Registro recibido</h3>
            <p>Recibimos tu registro. El administrador lo revisará y recibirás un email cuando esté aprobado.</p>
            <p>Si ya pagaste el programa, menciona tu número de orden a soporte para acelerar la aprobación.</p>
        </div>
        <p><a class="c360-btn c360-btn-secondary" href="<?php echo esc_url( home_url() ); ?>">Volver al inicio</a></p>
    <?php else : ?>

    <?php if ( $err ) : ?>
        <div class="c360-notice c360-notice-warn"><p><?php echo esc_html( C360_LMS_Registration::error_message( $err ) ); ?></p></div>
    <?php endif; ?>

    <form method="post" class="c360-form" action="<?php echo esc_url( home_url( '/register/' ) ); ?>" autocomplete="on">
        <?php wp_nonce_field( 'c360_register', 'c360_register_nonce' ); ?>
        <input type="hidden" name="c360_register" value="1">
        <!-- Honeypot: must stay empty -->
        <p style="position:absolute;left:-9999px" aria-hidden="true">
            <label>No completar este campo:
                <input type="text" name="c360_hp" tabindex="-1" autocomplete="off"></label>
        </p>

        <div class="c360-form-row">
            <label for="c360-name">Nombre completo</label>
            <input id="c360-name" type="text" name="name" required minlength="2" maxlength="80" autocomplete="name">
        </div>

        <div class="c360-form-row">
            <label for="c360-email">Email</label>
            <input id="c360-email" type="email" name="email" required autocomplete="email">
        </div>

        <div class="c360-form-row">
            <label for="c360-pw">Contraseña <small>(mínimo 8 caracteres)</small></label>
            <input id="c360-pw" type="password" name="password" required minlength="8" autocomplete="new-password">
        </div>

        <div class="c360-form-row">
            <label for="c360-pw2">Confirmar contraseña</label>
            <input id="c360-pw2" type="password" name="password2" required minlength="8" autocomplete="new-password">
        </div>

        <?php if ( count( $courses ) > 1 ) : ?>
        <div class="c360-form-row">
            <label for="c360-course">Curso al que deseas inscribirte</label>
            <select id="c360-course" name="course_id">
                <option value="0">— Sin preferencia —</option>
                <?php foreach ( $courses as $c ) : ?>
                    <option value="<?php echo intval( $c['id'] ); ?>" <?php selected( $selected_id, $c['id'] ); ?>>
                        <?php echo esc_html( $c['title'] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php elseif ( $selected_id ) : ?>
            <input type="hidden" name="course_id" value="<?php echo intval( $selected_id ); ?>">
        <?php endif; ?>

        <div class="c360-form-row">
            <label class="c360-tos">
                <input type="checkbox" name="tos" value="1" required>
                Acepto los <a href="<?php echo esc_url( home_url( '/terminos/' ) ); ?>" target="_blank" rel="noopener">términos y condiciones</a> y la <a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>" target="_blank" rel="noopener">política de privacidad</a>.
            </label>
        </div>

        <p><button type="submit" class="c360-btn c360-btn-primary">Crear Cuenta</button></p>

        <p class="c360-form-foot">
            ¿Ya tienes cuenta? <a href="<?php echo esc_url( wp_login_url( home_url( '/dashboard/' ) ) ); ?>">Iniciar sesión</a>
        </p>
    </form>
    <?php endif; ?>
</section>
