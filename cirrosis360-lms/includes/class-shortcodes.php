<?php
/**
 * Shortcodes — for embedding LMS components in pages/widgets.
 *  [c360_dashboard]  — student dashboard tile grid
 *  [c360_courses]    — public catalog card
 *  [c360_lesson slug=mod1] — single lesson player (enrollment-gated)
 *  [c360_login_or_dashboard] — smart card: login form or "Ir a Dashboard"
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Shortcodes {

    public static function init() {
        add_shortcode( 'c360_dashboard', array( __CLASS__, 'sc_dashboard' ) );
        add_shortcode( 'c360_courses', array( __CLASS__, 'sc_courses' ) );
        add_shortcode( 'c360_lesson', array( __CLASS__, 'sc_lesson' ) );
        add_shortcode( 'c360_login_or_dashboard', array( __CLASS__, 'sc_smart' ) );
    }

    private static function ensure_assets() {
        if ( ! wp_style_is( 'c360-lms', 'enqueued' ) ) {
            wp_enqueue_style( 'c360-lms' );
        }
        if ( ! wp_script_is( 'c360-lms', 'enqueued' ) ) {
            wp_enqueue_script( 'c360-lms' );
            wp_localize_script( 'c360-lms', 'c360LMS', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'c360_lms' ),
            ) );
        }
    }

    public static function sc_dashboard() {
        self::ensure_assets();
        if ( ! is_user_logged_in() ) return self::login_card();
        if ( ! C360_LMS_Enrollment::is_enrolled() ) return self::not_enrolled();
        ob_start();
        $vars = array();
        include C360_LMS_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }

    public static function sc_courses() {
        self::ensure_assets();
        ob_start();
        include C360_LMS_DIR . 'templates/courses.php';
        return ob_get_clean();
    }

    public static function sc_lesson( $atts ) {
        self::ensure_assets();
        $atts = shortcode_atts( array( 'slug' => '' ), $atts );
        $slug = sanitize_key( $atts['slug'] );
        if ( ! is_user_logged_in() ) return self::login_card();
        $lesson = C360_LMS_Config::lesson( $slug );
        if ( ! $lesson ) return '';
        $course_id = (int) $lesson['course_id'];
        if ( ! $course_id || ! C360_LMS_Enrollment::is_enrolled( null, $course_id ) ) return self::not_enrolled();
        ob_start();
        include C360_LMS_DIR . 'templates/lesson.php';
        return ob_get_clean();
    }

    public static function sc_smart() {
        self::ensure_assets();
        if ( is_user_logged_in() && C360_LMS_Enrollment::is_enrolled() ) {
            return '<a class="c360-btn c360-btn-primary" href="' . esc_url( home_url( '/dashboard/' ) ) . '">Ir a Mi Dashboard →</a>';
        }
        return self::login_card();
    }

    private static function login_card() {
        ob_start();
        ?>
        <div class="c360-login-card">
            <h3>Acceso de Alumnos</h3>
            <p>Inicia sesión para acceder a tus lecciones.</p>
            <?php wp_login_form( array(
                'redirect' => home_url( '/dashboard/' ),
                'remember' => true,
                'label_username' => 'Usuario o Email',
                'label_password' => 'Contraseña',
                'label_log_in'   => 'Entrar',
            ) ); ?>
            <p class="c360-login-meta">
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">¿Olvidaste tu contraseña?</a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function not_enrolled() {
        $mp = esc_url( get_theme_mod( 'jsma_mercadopago', 'https://mpago.la/1sXKiM3' ) );
        return '<div class="c360-notice">No tienes inscripción activa. <a class="c360-btn c360-btn-primary" href="' . $mp . '" target="_blank" rel="noopener">Inscribirme al Programa</a></div>';
    }
}
