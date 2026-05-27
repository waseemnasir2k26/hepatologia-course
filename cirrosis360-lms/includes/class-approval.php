<?php
/**
 * Pending-approval workflow.
 * New user registers → marked pending → CANNOT login until admin approves.
 *  - Hooks user_register to flag.
 *  - Hooks wp_authenticate_user to block.
 *  - Admin approve → flag cleared + notification email to user.
 *  - Admin reject → user deleted (or kept, marked rejected).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Approval {

    const META_PENDING  = '_c360_pending';
    const META_REJECTED = '_c360_rejected';
    const OPT_REQUIRE   = 'c360_lms_require_approval';

    public static function init() {
        add_action( 'user_register',          array( __CLASS__, 'on_register' ), 10, 1 );
        add_filter( 'wp_authenticate_user',   array( __CLASS__, 'block_pending' ), 10, 2 );
        add_action( 'admin_post_c360_approve',   array( __CLASS__, 'handle_approve' ) );
        add_action( 'admin_post_c360_reject',    array( __CLASS__, 'handle_reject' ) );
        add_action( 'lostpassword_post',      array( __CLASS__, 'block_lostpassword' ), 10, 1 );
        // Auto-enroll when admin assigns student role (manual or via role-change).
        add_action( 'set_user_role',          array( __CLASS__, 'on_role_set' ), 10, 3 );
        // Catch student role added at user_register (race vs set_user_role on new accounts).
        add_action( 'user_register',          array( __CLASS__, 'maybe_auto_enroll_on_register' ), 20, 1 );
    }

    public static function require_approval() {
        return (bool) get_option( self::OPT_REQUIRE, 1 );
    }

    public static function on_register( $user_id ) {
        if ( ! self::require_approval() ) return;
        $user = get_userdata( $user_id );
        if ( ! $user ) return;
        if ( in_array( 'administrator', (array) $user->roles, true ) ) return;
        // Skip approval gate when an admin creates the user via WP Users → Add New
        // or via Cirrosis 360 → Inscripciones (admin-context create_users capability).
        // Public /register/ form is anonymous → still flagged pending.
        if ( is_user_logged_in() && current_user_can( 'create_users' ) ) {
            update_user_meta( $user_id, '_c360_registered_at', current_time( 'mysql' ) );
            update_user_meta( $user_id, '_c360_register_ip', self::client_ip() );
            update_user_meta( $user_id, '_c360_admin_created', 1 );
            return;
        }
        update_user_meta( $user_id, self::META_PENDING, 1 );
        update_user_meta( $user_id, '_c360_registered_at', current_time( 'mysql' ) );
        update_user_meta( $user_id, '_c360_register_ip', self::client_ip() );
        self::notify_admin( $user );
        self::notify_user_pending( $user );
    }

    /**
     * If admin assigns the cirrosis_student role (via WP Users → Add New or Edit User
     * role dropdown), auto-enroll into the default course. Skips when actor is not
     * an admin (e.g. role assigned programmatically during MP webhook — that path
     * has its own enroll call w/ correct course_id).
     */
    public static function on_role_set( $user_id, $new_role, $old_roles ) {
        if ( $new_role !== 'cirrosis_student' ) return;
        if ( ! is_user_logged_in() || ! current_user_can( 'create_users' ) ) return;
        if ( ! class_exists( 'C360_LMS_Enrollment' ) ) return;
        // Already enrolled? Skip (don't reset clock).
        if ( get_user_meta( $user_id, C360_LMS_ENROLL_KEY, true ) ) return;
        C360_LMS_Enrollment::enroll( $user_id, 'admin_manual', 0, true );
    }

    /**
     * Race fallback — on user_register, after WP assigns the role, check if it's a
     * student role and auto-enroll. set_user_role doesn't always fire on the WP
     * Add-New screen when role is assigned during create.
     */
    public static function maybe_auto_enroll_on_register( $user_id ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'create_users' ) ) return;
        if ( ! class_exists( 'C360_LMS_Enrollment' ) ) return;
        $user = get_userdata( $user_id );
        if ( ! $user || ! in_array( 'cirrosis_student', (array) $user->roles, true ) ) return;
        if ( get_user_meta( $user_id, C360_LMS_ENROLL_KEY, true ) ) return;
        C360_LMS_Enrollment::enroll( $user_id, 'admin_manual', 0, true );
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

    public static function block_pending( $user, $password ) {
        if ( is_wp_error( $user ) ) return $user;
        if ( ! ( $user instanceof WP_User ) ) return $user;
        if ( in_array( 'administrator', (array) $user->roles, true ) ) return $user;
        if ( get_user_meta( $user->ID, self::META_REJECTED, true ) ) {
            return new WP_Error( 'c360_rejected', __( 'Tu cuenta no está autorizada para acceder. Contacta soporte.', 'cirrosis360-lms' ) );
        }
        if ( get_user_meta( $user->ID, self::META_PENDING, true ) ) {
            return new WP_Error( 'c360_pending', __( 'Tu cuenta está pendiente de aprobación por el administrador. Recibirás un email cuando sea aprobada.', 'cirrosis360-lms' ) );
        }
        return $user;
    }

    public static function block_lostpassword( &$errors ) {
        if ( ! is_wp_error( $errors ) ) $errors = new WP_Error();
        $login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';
        if ( ! $login ) return;
        $user = is_email( $login ) ? get_user_by( 'email', $login ) : get_user_by( 'login', $login );
        if ( ! $user ) return;
        if ( get_user_meta( $user->ID, self::META_PENDING, true ) || get_user_meta( $user->ID, self::META_REJECTED, true ) ) {
            $errors->add( 'c360_blocked_pw', 'Esta cuenta no está activa. Contacta soporte.' );
        }
    }

    public static function approve( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) return false;
        delete_user_meta( $user_id, self::META_PENDING );
        delete_user_meta( $user_id, self::META_REJECTED );
        $user = get_userdata( $user_id );
        if ( ! $user ) return false;
        self::notify_user_approved( $user );
        do_action( 'c360_lms_user_approved', $user_id );
        return true;
    }

    public static function reject( $user_id, $delete = false ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) return false;
        if ( $delete ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user( $user_id );
        } else {
            delete_user_meta( $user_id, self::META_PENDING );
            update_user_meta( $user_id, self::META_REJECTED, 1 );
            // Also unenroll + destroy active sessions to revoke access.
            if ( class_exists( 'C360_LMS_Enrollment' ) ) C360_LMS_Enrollment::unenroll( $user_id );
            if ( class_exists( 'WP_Session_Tokens' ) ) {
                WP_Session_Tokens::get_instance( $user_id )->destroy_all();
            }
        }
        do_action( 'c360_lms_user_rejected', $user_id );
        return true;
    }

    public static function handle_approve() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $uid = absint( $_REQUEST['user_id'] ?? 0 );
        check_admin_referer( 'c360_approve_' . $uid );
        if ( ! self::is_pending( $uid ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=c360-lms-pending&err=not_pending' ) ); exit;
        }
        self::approve( $uid );
        $course_id = absint( $_REQUEST['course_id'] ?? 0 );
        if ( $course_id && class_exists( 'C360_LMS_Enrollment' ) ) {
            C360_LMS_Enrollment::enroll( $uid, 'admin_approved', $course_id, true );
        }
        wp_safe_redirect( admin_url( 'admin.php?page=c360-lms-pending&approved=1' ) );
        exit;
    }

    public static function handle_reject() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $uid = absint( $_REQUEST['user_id'] ?? 0 );
        check_admin_referer( 'c360_reject_' . $uid );
        $delete = ! empty( $_REQUEST['delete'] );
        self::reject( $uid, $delete );
        wp_safe_redirect( admin_url( 'admin.php?page=c360-lms-pending&rejected=1' ) );
        exit;
    }

    private static function notify_admin( $user ) {
        $admin_email = get_option( 'admin_email' );
        $approve_url = admin_url( 'admin.php?page=c360-lms-pending' );
        // Strip CRLF from anything that lands in the subject line.
        $email_safe = preg_replace( '/[\r\n]+/', ' ', (string) $user->user_email );
        $subj = '[Cirrosis 360] Nuevo registro pendiente: ' . $email_safe;
        $body = "Un nuevo usuario se registró y espera aprobación:\n\n";
        $body .= 'Nombre: ' . preg_replace( '/[\r\n]+/', ' ', (string) $user->display_name ) . "\n";
        $body .= 'Email: ' . $email_safe . "\n";
        $body .= 'Usuario: ' . preg_replace( '/[\r\n]+/', ' ', (string) $user->user_login ) . "\n\n";
        $body .= 'Aprobar/rechazar: ' . $approve_url . "\n";
        wp_mail( $admin_email, $subj, $body );
    }

    private static function notify_user_pending( $user ) {
        $subj = '[Cirrosis 360] Registro recibido — pendiente de aprobación';
        $body = "Hola {$user->display_name},\n\n";
        $body .= "Recibimos tu registro en " . get_bloginfo( 'name' ) . ". Tu cuenta está pendiente de aprobación por el administrador.\n\n";
        $body .= "Recibirás un correo cuando sea aprobada y podrás iniciar sesión.\n\n";
        $body .= "Si pagaste el programa, asegúrate de mencionar tu número de orden a soporte.\n\n";
        $body .= "Soporte: " . get_option( 'admin_email' ) . "\n";
        wp_mail( $user->user_email, $subj, $body );
    }

    private static function notify_user_approved( $user ) {
        $login_url = wp_login_url( home_url( '/dashboard/' ) );
        $subj = '[Cirrosis 360] Cuenta aprobada — ya puedes iniciar sesión';
        $body = "Hola {$user->display_name},\n\n";
        $body .= "Tu cuenta fue aprobada. Ya puedes iniciar sesión:\n{$login_url}\n\n";
        $body .= "Si no recuerdas tu contraseña, usa el enlace 'Olvidé contraseña' en la página de login.\n";
        wp_mail( $user->user_email, $subj, $body );
    }

    public static function is_pending( $user_id ) {
        return (bool) get_user_meta( $user_id, self::META_PENDING, true );
    }

    public static function pending_users() {
        return get_users( array(
            'meta_key'   => self::META_PENDING,
            'meta_value' => 1,
            'orderby'    => 'registered',
            'order'      => 'ASC',
        ) );
    }
}
