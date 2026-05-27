<?php
/**
 * Custom registration handler — replaces wp-login.php?action=register flow.
 * - Enforces strong password.
 * - Honeypot spam field.
 * - Rate-limit per IP (3/hr) via transients.
 * - ToS checkbox.
 * - Optional course_id pre-selection (passes to enrollment later if approved + paid).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Registration {

    const RATE_TRANSIENT = 'c360_reg_rate_';
    const RATE_LIMIT = 3;        // attempts
    const RATE_WINDOW = 3600;    // 1 hour

    public static function init() {
        add_action( 'init', array( __CLASS__, 'maybe_handle_post' ), 20 );
    }

    public static function maybe_handle_post() {
        if ( empty( $_POST['c360_register'] ) ) return;
        if ( ! isset( $_POST['c360_register_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['c360_register_nonce'] ), 'c360_register' ) ) {
            wp_die( 'Solicitud no válida (nonce).', 400 );
        }

        // Honeypot — must be empty. Counts as spam, low rate-limit cost.
        if ( ! empty( $_POST['c360_hp'] ) ) {
            self::redirect_with_error( 'spam' );
        }

        // Rate limit per IP + global. IP is REMOTE_ADDR unless C360_TRUST_PROXY=true.
        $ip = self::client_ip();
        $key = self::RATE_TRANSIENT . md5( $ip );
        $count = (int) get_transient( $key );
        $global_count = (int) get_transient( 'c360_reg_global' );
        if ( $count >= self::RATE_LIMIT || $global_count >= 100 ) {
            self::redirect_with_error( 'rate' );
        }
        set_transient( $key, $count + 1, self::RATE_WINDOW );
        set_transient( 'c360_reg_global', $global_count + 1, self::RATE_WINDOW );

        $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
        $pw    = (string) ( $_POST['password'] ?? '' );
        $pw2   = (string) ( $_POST['password2'] ?? '' );
        $tos   = ! empty( $_POST['tos'] );
        $course_id = absint( $_POST['course_id'] ?? 0 );

        if ( ! $tos ) self::redirect_with_error( 'tos' );
        if ( ! is_email( $email ) ) self::redirect_with_error( 'email' );
        if ( strlen( $pw ) < 8 ) self::redirect_with_error( 'pw_short' );
        if ( $pw !== $pw2 ) self::redirect_with_error( 'pw_match' );
        if ( ! $name || strlen( $name ) < 2 ) self::redirect_with_error( 'name' );

        // Validate course_id (if specified) actually exists.
        if ( $course_id ) {
            $c = C360_LMS_Config::course( $course_id );
            if ( ! $c ) $course_id = 0;
        }

        // Email-already-exists: do NOT leak. Render success and stop. Optionally email user that they have an account.
        if ( email_exists( $email ) ) {
            $existing = get_user_by( 'email', $email );
            if ( $existing ) {
                wp_mail( $email,
                    '[Cirrosis 360] Ya tienes cuenta',
                    "Recibimos un intento de registro con tu email. Si fuiste tú, inicia sesión: " . wp_login_url( home_url( '/dashboard/' ) ) . "\n\nSi no, ignora este mensaje."
                );
            }
            wp_safe_redirect( add_query_arg( 'registered', '1', home_url( '/register/' ) ) );
            exit;
        }

        $username_base = sanitize_user( current( explode( '@', $email ) ), true );
        if ( ! $username_base ) $username_base = 'user';
        $username = $username_base;
        $i = 1;
        while ( username_exists( $username ) ) { $username = $username_base . $i++; }

        $uid = wp_create_user( $username, $pw, $email );
        if ( is_wp_error( $uid ) ) self::redirect_with_error( 'create' );

        wp_update_user( array(
            'ID'           => $uid,
            'display_name' => $name,
            'first_name'   => $name,
        ) );

        if ( $course_id ) {
            update_user_meta( $uid, '_c360_requested_course', $course_id );
        }

        wp_safe_redirect( add_query_arg( 'registered', '1', home_url( '/register/' ) ) );
        exit;
    }

    private static function redirect_with_error( $code ) {
        wp_safe_redirect( add_query_arg( 'err', sanitize_key( $code ), home_url( '/register/' ) ) );
        exit;
    }

    private static function client_ip() {
        if ( defined( 'C360_TRUST_PROXY' ) && C360_TRUST_PROXY ) {
            foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR' ) as $h ) {
                if ( ! empty( $_SERVER[ $h ] ) ) {
                    $ip = trim( current( explode( ',', wp_unslash( $_SERVER[ $h ] ) ) ) );
                    if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) return $ip;
                }
            }
        }
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '0.0.0.0';
        return filter_var( $ip, FILTER_VALIDATE_IP ) ?: '0.0.0.0';
    }

    public static function error_message( $code ) {
        $map = array(
            'spam'      => 'Solicitud rechazada.',
            'rate'      => 'Demasiados intentos. Intenta de nuevo en una hora.',
            'tos'       => 'Debes aceptar los términos para continuar.',
            'email'     => 'Email inválido.',
            'pw_short'  => 'La contraseña debe tener al menos 8 caracteres.',
            'pw_match'  => 'Las contraseñas no coinciden.',
            'name'      => 'Nombre inválido.',
            'create'    => 'No pudimos crear la cuenta. Intenta de nuevo.',
        );
        return $map[ $code ] ?? 'Error desconocido.';
    }
}
