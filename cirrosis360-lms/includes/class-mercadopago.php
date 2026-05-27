<?php
/**
 * MercadoPago webhook → auto-enroll on payment.
 *
 * Webhook URL: https://portal.juliosantiagomarcelo.com/?c360_mp_webhook=1
 * Configure in MP dashboard pointing to that URL with secret in plugin settings.
 *
 * Manual fallback: client emails order ID → admin uses /wp-admin/admin.php?page=c360-lms manual enroll.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_MercadoPago {

    const SECRET_OPT = 'c360_mp_webhook_secret';
    const TOKEN_OPT  = 'c360_mp_access_token';

    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_menu', array( __CLASS__, 'menu' ), 30 );
        add_action( 'init', array( __CLASS__, 'maybe_handle_webhook' ) );
    }

    public static function register_settings() {
        register_setting( 'c360_mp', self::SECRET_OPT );
        register_setting( 'c360_mp', self::TOKEN_OPT );
    }

    public static function menu() {
        add_submenu_page( 'c360-lms', 'MercadoPago', 'MercadoPago', 'manage_options', 'c360-lms-mp', array( __CLASS__, 'page' ) );
    }

    public static function page() {
        ?>
        <div class="wrap">
            <h1>MercadoPago — Webhook</h1>
            <p><strong>URL del webhook (copiar a MP):</strong>
               <code><?php echo esc_url( home_url( '/?c360_mp_webhook=1' ) ); ?></code></p>
            <form method="post" action="options.php">
                <?php settings_fields( 'c360_mp' ); ?>
                <table class="form-table">
                    <tr><th>Access Token (MP)</th>
                        <td><input type="password" name="<?php echo esc_attr( self::TOKEN_OPT ); ?>"
                                   value="<?php echo esc_attr( get_option( self::TOKEN_OPT, '' ) ); ?>"
                                   class="regular-text"></td></tr>
                    <tr><th>Webhook Secret (compartido) <span style="color:#c00">*obligatorio</span></th>
                        <td><input type="password" name="<?php echo esc_attr( self::SECRET_OPT ); ?>"
                                   value="<?php echo esc_attr( get_option( self::SECRET_OPT, '' ) ); ?>"
                                   class="regular-text" required>
                            <p class="description">Configura este secreto y envíalo a MP en el header <code>X-C360-Secret</code>. Sin él, el webhook devuelve 503.</p>
                        </td></tr>
                </table>
                <?php submit_button( 'Guardar' ); ?>
            </form>
        </div>
        <?php
    }

    public static function maybe_handle_webhook() {
        if ( empty( $_GET['c360_mp_webhook'] ) ) return;

        // Mandatory secret. Block all webhooks if not configured.
        $secret = (string) get_option( self::SECRET_OPT, '' );
        if ( ! $secret ) { status_header( 503 ); echo 'webhook secret not configured'; exit; }
        $got = isset( $_SERVER['HTTP_X_C360_SECRET'] ) ? wp_unslash( $_SERVER['HTTP_X_C360_SECRET'] ) : '';
        if ( ! hash_equals( $secret, (string) $got ) ) { status_header( 403 ); exit; }

        $body = file_get_contents( 'php://input' );
        $data = json_decode( $body, true );
        if ( ! is_array( $data ) ) { status_header( 400 ); exit; }

        $type = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : ( isset( $data['action'] ) ? sanitize_text_field( $data['action'] ) : '' );
        $payment_id = isset( $data['data']['id'] ) ? sanitize_text_field( $data['data']['id'] ) : '';

        // Strict event allow-list.
        $allowed = array( 'payment', 'payment.created', 'payment.updated' );
        if ( ! in_array( $type, $allowed, true ) || ! $payment_id ) {
            status_header( 200 ); echo 'ignored'; exit;
        }
        // Validate payment_id is purely numeric (MP IDs are integers).
        if ( ! preg_match( '/^[0-9]+$/', $payment_id ) ) {
            status_header( 400 ); echo 'invalid id'; exit;
        }

        $payment = self::fetch_payment( $payment_id );
        if ( ! $payment || empty( $payment['status'] ) ) { status_header( 200 ); echo 'no payment'; exit; }

        $status = $payment['status'];
        $email  = isset( $payment['payer']['email'] ) ? sanitize_email( $payment['payer']['email'] ) : '';
        if ( ! is_email( $email ) ) { status_header( 200 ); echo 'no email'; exit; }

        // Refund / chargeback / cancel → unenroll.
        if ( in_array( $status, array( 'refunded', 'charged_back', 'cancelled' ), true ) ) {
            $existing = get_user_by( 'email', $email );
            if ( $existing ) {
                C360_LMS_Enrollment::unenroll( $existing->ID );
                update_user_meta( $existing->ID, '_c360_mp_last_status', sanitize_text_field( $status ) );
            }
            status_header( 200 ); echo 'revoked'; exit;
        }
        if ( $status !== 'approved' ) { status_header( 200 ); echo 'not approved'; exit; }

        // Approved.
        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            $base = sanitize_user( current( explode( '@', $email ) ), true );
            if ( ! $base ) $base = 'user';
            $username = $base;
            $i = 1;
            while ( username_exists( $username ) ) { $username = $base . $i++; }
            $pw = wp_generate_password( 16, true );
            $uid = wp_create_user( $username, $pw, $email );
            if ( is_wp_error( $uid ) ) { status_header( 500 ); exit; }
            $first = isset( $payment['payer']['first_name'] ) ? sanitize_text_field( $payment['payer']['first_name'] ) : '';
            $last  = isset( $payment['payer']['last_name'] ) ? sanitize_text_field( $payment['payer']['last_name'] ) : '';
            $display = trim( $first . ' ' . $last );
            if ( $display ) {
                wp_update_user( array( 'ID' => $uid, 'display_name' => $display, 'first_name' => $first, 'last_name' => $last ) );
            }
            wp_new_user_notification( $uid, null, 'user' );
            $user = get_userdata( $uid );
        }

        // Idempotency — same payment_id replayed = no-op.
        $stored_pid = (string) get_user_meta( $user->ID, '_c360_mp_payment_id', true );
        if ( $stored_pid === $payment_id && C360_LMS_Enrollment::is_enrolled( $user->ID ) ) {
            status_header( 200 ); echo 'duplicate'; exit;
        }
        update_user_meta( $user->ID, '_c360_mp_payment_id', $payment_id );

        // Paid customer: clear pending/rejected flags + enroll.
        delete_user_meta( $user->ID, C360_LMS_Approval::META_PENDING );
        delete_user_meta( $user->ID, C360_LMS_Approval::META_REJECTED );

        // Optional: course routing via MP "external_reference" (admin sets in MP checkout).
        $course_id = 0;
        if ( ! empty( $payment['external_reference'] ) ) {
            $ref = sanitize_text_field( $payment['external_reference'] );
            // Format: "course:<slug>" or numeric ID.
            if ( strpos( $ref, 'course:' ) === 0 ) {
                $c = C360_LMS_Config::course( substr( $ref, 7 ) );
                if ( $c ) $course_id = (int) $c['id'];
            } elseif ( is_numeric( $ref ) ) {
                $course_id = (int) $ref;
            }
        }

        C360_LMS_Enrollment::enroll( $user->ID, 'mercadopago:' . $payment_id, $course_id, true );
        update_user_meta( $user->ID, '_c360_mp_last_status', 'approved' );

        status_header( 200 );
        echo 'enrolled';
        exit;
    }

    private static function fetch_payment( $id ) {
        $token = get_option( self::TOKEN_OPT, '' );
        if ( ! $token ) return null;
        $res = wp_remote_get( 'https://api.mercadopago.com/v1/payments/' . urlencode( $id ), array(
            'headers' => array( 'Authorization' => 'Bearer ' . $token ),
            'timeout' => 15,
        ) );
        if ( is_wp_error( $res ) ) return null;
        $code = wp_remote_retrieve_response_code( $res );
        if ( $code !== 200 ) return null;
        return json_decode( wp_remote_retrieve_body( $res ), true );
    }
}
