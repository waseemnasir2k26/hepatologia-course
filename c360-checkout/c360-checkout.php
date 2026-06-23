<?php
/**
 * Plugin Name: Cirrosis 360 — Checkout Automático (Mercado Pago)
 * Description: Ficha de inscripción (Nombre/Email/WhatsApp) + Checkout Pro de Mercado Pago + creación automática de usuario, inscripción y correo de acceso al confirmarse el pago.
 * Version: 1.0.0
 * Author: SkynetLabs
 * Text Domain: c360-checkout
 *
 * FLUJO: Ficha -> Preference (con payer.email + external_reference) -> Checkout Pro
 *        -> webhook payment.approved -> crear usuario (rol que auto-inscribe) -> correo de acceso.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'C360CO_VER', '1.0.0' );
define( 'C360CO_OPT', 'c360co_settings' );
define( 'C360CO_TABLE', 'c360co_orders' );

/* ──────────────────────────────────────────────────────────────
 * 1. ACTIVATION — create the pending-orders table
 * ────────────────────────────────────────────────────────────── */
register_activation_hook( __FILE__, 'c360co_activate' );
function c360co_activate() {
    global $wpdb;
    $table   = $wpdb->prefix . C360CO_TABLE;
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        order_ref VARCHAR(64) NOT NULL,
        nombre VARCHAR(190) NOT NULL,
        email VARCHAR(190) NOT NULL,
        whatsapp VARCHAR(60) DEFAULT '',
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        payment_id VARCHAR(64) DEFAULT '',
        user_id BIGINT(20) UNSIGNED DEFAULT 0,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY order_ref (order_ref),
        KEY status (status)
    ) $charset;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

function c360co_get( $key, $default = '' ) {
    $o = get_option( C360CO_OPT, array() );
    return isset( $o[ $key ] ) && $o[ $key ] !== '' ? $o[ $key ] : $default;
}

/* ──────────────────────────────────────────────────────────────
 * 2. SETTINGS PAGE  (Ajustes -> Inscripción C360)
 * ────────────────────────────────────────────────────────────── */
add_action( 'admin_menu', function () {
    add_menu_page( 'Cirrosis 360', 'Cirrosis 360', 'manage_options', 'c360co_orders', 'c360co_orders_page', 'dashicons-cart', 56 );
    add_submenu_page( 'c360co_orders', 'Inscripciones', 'Inscripciones', 'manage_options', 'c360co_orders', 'c360co_orders_page' );
    add_submenu_page( 'c360co_orders', 'Ajustes', 'Ajustes', 'manage_options', 'c360co', 'c360co_settings_page' );
} );

add_action( 'admin_init', function () {
    register_setting( 'c360co_group', C360CO_OPT, 'c360co_sanitize' );
} );

function c360co_sanitize( $in ) {
    $out = array();
    $out['access_token']  = isset($in['access_token'])  ? trim( sanitize_text_field( $in['access_token'] ) ) : '';
    $out['webhook_secret']= isset($in['webhook_secret'])? trim( sanitize_text_field( $in['webhook_secret'] ) ) : '';
    $out['course_id']     = isset($in['course_id'])     ? absint( $in['course_id'] ) : 0;
    $out['role_slug']     = isset($in['role_slug'])     ? sanitize_key( $in['role_slug'] ) : 'cirrosis_student';
    $out['price']         = isset($in['price'])         ? floatval( $in['price'] ) : 0;
    $out['currency']      = isset($in['currency'])      ? sanitize_text_field( $in['currency'] ) : 'PEN';
    $out['product_name']  = isset($in['product_name'])  ? sanitize_text_field( $in['product_name'] ) : 'Programa Cirrosis 360';
    $out['portal_url']    = isset($in['portal_url'])    ? esc_url_raw( $in['portal_url'] ) : '';
    $out['success_url']   = isset($in['success_url'])   ? esc_url_raw( $in['success_url'] ) : '';
    $out['failure_url']   = isset($in['failure_url'])   ? esc_url_raw( $in['failure_url'] ) : '';
    $out['from_name']     = isset($in['from_name'])     ? sanitize_text_field( $in['from_name'] ) : 'Dr. Julio Santiago Marcelo';
    $out['direct_enroll'] = ! empty( $in['direct_enroll'] ) ? 1 : 0;
    return $out;
}

function c360co_settings_page() {
    $o = get_option( C360CO_OPT, array() );
    $f = function( $k, $d='' ) use ( $o ) { return isset($o[$k]) ? esc_attr($o[$k]) : $d; };
    $webhook = esc_url( rest_url( 'c360/v1/webhook' ) );

    // Pre-flight warnings.
    $warn = array();
    $tok  = $f( 'access_token' );
    $role = $f( 'role_slug', 'cirrosis_student' );
    if ( $tok && stripos( $tok, 'TEST-' ) === 0 ) {
        $warn[] = 'El Access Token parece ser de PRUEBA (empieza con "TEST-"). Para cobrar dinero real use el token de producción (APP_USR-...).';
    }
    if ( $role && ! get_role( $role ) ) {
        $warn[] = 'El rol "' . esc_html( $role ) . '" no existe en este WordPress. Verifique el rol del alumno, o active "Inscripción directa" como respaldo.';
    }
    ?>
    <div class="wrap">
      <h1>Inscripción C360 — Checkout Automático</h1>
      <?php foreach ( $warn as $w ) : ?>
        <div class="notice notice-warning"><p><?php echo wp_kses_post( $w ); ?></p></div>
      <?php endforeach; ?>
      <p><strong>URL del Webhook</strong> (péguela en Mercado Pago → Notificaciones / Webhooks, evento <code>payment</code>):<br>
         <code style="font-size:14px;background:#fff;padding:6px 10px;display:inline-block;margin-top:4px;"><?php echo esc_html( $webhook ); ?></code></p>
      <form method="post" action="options.php">
        <?php settings_fields( 'c360co_group' ); ?>
        <table class="form-table" role="presentation">
          <tr><th><label>Access Token (producción)</label></th>
              <td><input type="text" name="<?php echo esc_attr( C360CO_OPT ); ?>[access_token]" value="<?php echo $f('access_token'); ?>" class="regular-text" autocomplete="off" placeholder="APP_USR-..."></td></tr>
          <tr><th><label>Clave secreta del Webhook</label></th>
              <td><input type="text" name="<?php echo esc_attr( C360CO_OPT ); ?>[webhook_secret]" value="<?php echo $f('webhook_secret'); ?>" class="regular-text" autocomplete="off" placeholder="(de MP → Webhooks)">
              <p class="description">Opcional pero recomendado. Si la pega aquí, se verifica la firma de cada notificación.</p></td></tr>
          <tr><th><label>ID del Curso</label></th>
              <td><input type="number" name="<?php echo esc_attr( C360CO_OPT ); ?>[course_id]" value="<?php echo $f('course_id','10'); ?>" class="small-text">
              <p class="description">En producción el curso "Programa Completo" tiene ID <code>10</code>.</p></td></tr>
          <tr><th><label>Rol del alumno</label></th>
              <td><input type="text" name="<?php echo esc_attr( C360CO_OPT ); ?>[role_slug]" value="<?php echo $f('role_slug','cirrosis_student'); ?>" class="regular-text">
              <p class="description">El rol que el LMS usa para inscribir automáticamente (por defecto <code>cirrosis_student</code>).</p></td></tr>
          <tr><th><label>Precio</label></th>
              <td><input type="number" step="0.01" name="<?php echo esc_attr( C360CO_OPT ); ?>[price]" value="<?php echo $f('price','247'); ?>" class="small-text">
              <input type="text" name="<?php echo esc_attr( C360CO_OPT ); ?>[currency]" value="<?php echo $f('currency','PEN'); ?>" class="small-text" style="width:70px;"> </td></tr>
          <tr><th><label>Nombre del producto</label></th>
              <td><input type="text" name="<?php echo esc_attr( C360CO_OPT ); ?>[product_name]" value="<?php echo $f('product_name','Programa Cirrosis 360'); ?>" class="regular-text"></td></tr>
          <tr><th><label>URL del Portal</label></th>
              <td><input type="url" name="<?php echo esc_attr( C360CO_OPT ); ?>[portal_url]" value="<?php echo $f('portal_url','https://portal.juliosantiagomarcelo.com'); ?>" class="regular-text"></td></tr>
          <tr><th><label>URL de éxito (post-pago)</label></th>
              <td><input type="url" name="<?php echo esc_attr( C360CO_OPT ); ?>[success_url]" value="<?php echo $f('success_url'); ?>" class="regular-text" placeholder="página de gracias"></td></tr>
          <tr><th><label>URL de fallo</label></th>
              <td><input type="url" name="<?php echo esc_attr( C360CO_OPT ); ?>[failure_url]" value="<?php echo $f('failure_url'); ?>" class="regular-text"></td></tr>
          <tr><th><label>Nombre del remitente</label></th>
              <td><input type="text" name="<?php echo esc_attr( C360CO_OPT ); ?>[from_name]" value="<?php echo $f('from_name','Dr. Julio Santiago Marcelo'); ?>" class="regular-text"></td></tr>
          <tr><th><label>Inscripción directa</label></th>
              <td><strong>Siempre activa.</strong> Al confirmarse el pago, el alumno queda inscrito automáticamente (meta <code>_c360_enrolled</code> + curso + 180 días), sin depender de aprobación manual.</td></tr>
        </table>
        <?php submit_button(); ?>
      </form>
      <hr>
      <h2>Cómo publicar la ficha</h2>
      <p>Cree una página (ej. <em>Inscripción</em>) e inserte el shortcode: <code>[c360_inscripcion]</code>. Luego apunte los botones "Inscribirme" de la web a esa página.</p>
    </div>
    <?php
}

/* ──────────────────────────────────────────────────────────────
 * 3. FRONT-END FORM  [c360_inscripcion]
 * ────────────────────────────────────────────────────────────── */
add_shortcode( 'c360_inscripcion', function () {
    $price = c360co_get( 'price', '247' );
    $cur   = c360co_get( 'currency', 'PEN' );
    $err   = isset( $_GET['c360_err'] ) ? sanitize_text_field( wp_unslash( $_GET['c360_err'] ) ) : '';
    ob_start(); ?>
    <div class="c360-checkout" style="max-width:480px;margin:0 auto;font-family:inherit;">
      <?php if ( $err ) : ?>
        <div style="background:#fde8e8;color:#a12;padding:10px 14px;border-radius:8px;margin-bottom:14px;">No se pudo iniciar el pago. Intente de nuevo o escríbanos por WhatsApp.</div>
      <?php endif; ?>
      <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="c360_create_pref">
        <?php wp_nonce_field( 'c360_pref', 'c360_nonce' ); ?>
        <p style="margin:0 0 12px;">
          <label style="display:block;font-weight:600;margin-bottom:4px;">Nombre completo</label>
          <input type="text" name="nombre" required style="width:100%;padding:11px;border:1px solid #ccc;border-radius:8px;">
        </p>
        <p style="margin:0 0 12px;">
          <label style="display:block;font-weight:600;margin-bottom:4px;">Correo electrónico</label>
          <input type="email" name="email" required style="width:100%;padding:11px;border:1px solid #ccc;border-radius:8px;">
          <small style="color:#666;">A este correo enviaremos sus accesos al curso.</small>
        </p>
        <p style="margin:0 0 16px;">
          <label style="display:block;font-weight:600;margin-bottom:4px;">WhatsApp</label>
          <input type="text" name="whatsapp" required style="width:100%;padding:11px;border:1px solid #ccc;border-radius:8px;" placeholder="+51 ...">
        </p>
        <button type="submit" style="width:100%;padding:14px;border:0;border-radius:10px;background:#c9a227;color:#3a2e00;font-weight:700;font-size:1rem;cursor:pointer;">
          Pagar S/ <?php echo esc_html( $price ); ?> con Mercado Pago →
        </button>
        <p style="text-align:center;color:#888;font-size:.8rem;margin-top:10px;">🔒 Pago seguro · Tarjeta, Yape, Plin o transferencia</p>
      </form>
    </div>
    <?php
    return ob_get_clean();
} );

/* ──────────────────────────────────────────────────────────────
 * 4. CREATE PREFERENCE  (form submit)  — public + logged
 * ────────────────────────────────────────────────────────────── */
add_action( 'admin_post_nopriv_c360_create_pref', 'c360co_create_pref' );
add_action( 'admin_post_c360_create_pref',        'c360co_create_pref' );
function c360co_create_pref() {
    if ( ! isset( $_POST['c360_nonce'] ) || ! wp_verify_nonce( $_POST['c360_nonce'], 'c360_pref' ) ) {
        wp_die( 'Sesión expirada. Vuelva a la página e intente de nuevo.' );
    }
    $nombre   = isset($_POST['nombre'])   ? sanitize_text_field( wp_unslash($_POST['nombre']) )   : '';
    $email    = isset($_POST['email'])    ? sanitize_email( wp_unslash($_POST['email']) )         : '';
    $whatsapp = isset($_POST['whatsapp']) ? sanitize_text_field( wp_unslash($_POST['whatsapp']) ) : '';
    $back     = wp_get_referer() ?: home_url();

    if ( ! is_email( $email ) || $nombre === '' ) {
        wp_safe_redirect( add_query_arg( 'c360_err', 'datos', $back ) ); exit;
    }

    $token = c360co_get( 'access_token' );
    if ( ! $token ) { wp_safe_redirect( add_query_arg( 'c360_err', 'config', $back ) ); exit; }

    $order_ref = 'C360-' . wp_generate_password( 12, false, false );

    global $wpdb;
    $wpdb->insert( $wpdb->prefix . C360CO_TABLE, array(
        'order_ref' => $order_ref,
        'nombre'    => $nombre,
        'email'     => $email,
        'whatsapp'  => $whatsapp,
        'status'    => 'pending',
        'created_at'=> current_time( 'mysql' ),
    ) );

    $price   = floatval( c360co_get( 'price', 247 ) );
    $cur     = c360co_get( 'currency', 'PEN' );
    $success = c360co_get( 'success_url' ) ?: home_url( '/?c360=ok' );
    $failure = c360co_get( 'failure_url' ) ?: add_query_arg( 'c360_err', 'pago', $back );

    $body = array(
        'items' => array( array(
            'title'       => c360co_get( 'product_name', 'Programa Cirrosis 360' ),
            'quantity'    => 1,
            'unit_price'  => $price,
            'currency_id' => $cur,
        ) ),
        'payer' => array( 'email' => $email, 'name' => $nombre ),
        'external_reference' => $order_ref,
        'back_urls' => array(
            'success' => $success,
            'pending' => $success,
            'failure' => $failure,
        ),
        'auto_return'      => 'approved',
        'notification_url' => rest_url( 'c360/v1/webhook' ),
        'statement_descriptor' => 'CIRROSIS360',
    );

    $resp = wp_remote_post( 'https://api.mercadopago.com/checkout/preferences', array(
        'timeout' => 25,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ),
        'body' => wp_json_encode( $body ),
    ) );

    if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) >= 300 ) {
        c360co_log( 'preference_error', wp_remote_retrieve_body( $resp ) );
        wp_safe_redirect( add_query_arg( 'c360_err', 'mp', $back ) ); exit;
    }

    $data = json_decode( wp_remote_retrieve_body( $resp ), true );
    $init = is_array( $data ) ? ( ! empty( $data['init_point'] ) ? $data['init_point'] : ( $data['sandbox_init_point'] ?? '' ) ) : '';
    if ( ! $init ) { wp_safe_redirect( add_query_arg( 'c360_err', 'mp', $back ) ); exit; }

    wp_redirect( esc_url_raw( $init ) ); // external (MP) -> wp_redirect not wp_safe_redirect
    exit;
}

/* ──────────────────────────────────────────────────────────────
 * 5. WEBHOOK  POST /wp-json/c360/v1/webhook
 * ────────────────────────────────────────────────────────────── */
add_action( 'rest_api_init', function () {
    register_rest_route( 'c360/v1', '/webhook', array(
        'methods'             => 'POST',
        'callback'            => 'c360co_webhook',
        'permission_callback' => '__return_true',
    ) );
} );

function c360co_webhook( WP_REST_Request $req ) {
    // Identify the payment id from query or body (MP sends several shapes).
    $payment_id = '';
    if ( $req->get_param( 'type' ) === 'payment' || $req->get_param( 'topic' ) === 'payment' ) {
        $payment_id = $req->get_param( 'id' ) ?: $req->get_param( 'data_id' );
    }
    $json = $req->get_json_params();
    if ( ! $payment_id && is_array( $json ) ) {
        if ( ! empty( $json['data']['id'] ) ) $payment_id = $json['data']['id'];
        elseif ( ! empty( $json['id'] ) && ( ($json['type'] ?? '') === 'payment' ) ) $payment_id = $json['id'];
    }
    if ( ! $payment_id ) return new WP_REST_Response( array( 'ok' => true, 'skip' => 'no payment id' ), 200 );

    // Optional signature verification.
    if ( ! c360co_verify_signature( $req, $payment_id ) ) {
        c360co_log( 'sig_fail', 'payment ' . $payment_id );
        return new WP_REST_Response( array( 'ok' => false ), 401 );
    }

    $token = c360co_get( 'access_token' );
    if ( ! $token ) return new WP_REST_Response( array( 'ok' => false, 'err' => 'no token' ), 200 );

    // Fetch the real payment from MP (source of truth).
    $resp = wp_remote_get( 'https://api.mercadopago.com/v1/payments/' . rawurlencode( $payment_id ), array(
        'timeout' => 25,
        'headers' => array( 'Authorization' => 'Bearer ' . $token ),
    ) );
    if ( is_wp_error( $resp ) || wp_remote_retrieve_response_code( $resp ) >= 300 ) {
        return new WP_REST_Response( array( 'ok' => false, 'err' => 'fetch' ), 200 );
    }
    $pay = json_decode( wp_remote_retrieve_body( $resp ), true );
    if ( ! is_array( $pay ) ) return new WP_REST_Response( array( 'ok' => false, 'err' => 'decode' ), 200 );

    $status    = $pay['status'] ?? '';
    $order_ref = $pay['external_reference'] ?? '';
    if ( $status !== 'approved' || ! $order_ref ) {
        return new WP_REST_Response( array( 'ok' => true, 'status' => $status ), 200 );
    }

    global $wpdb;
    $table = $wpdb->prefix . C360CO_TABLE;
    $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE order_ref = %s", $order_ref ) );
    if ( ! $row ) return new WP_REST_Response( array( 'ok' => true, 'skip' => 'no order' ), 200 );

    // ATOMIC CLAIM — flip pending -> processing only if still pending.
    // MP retries aggressively; this guarantees exactly one worker fulfills (no duplicate emails/users).
    $claimed = $wpdb->query( $wpdb->prepare(
        "UPDATE $table SET status = 'processing' WHERE id = %d AND status = 'pending'",
        $row->id
    ) );
    if ( 1 !== (int) $claimed ) {
        // Already claimed/completed by another delivery -> idempotent no-op.
        return new WP_REST_Response( array( 'ok' => true, 'dup' => true ), 200 );
    }

    $user_id = c360co_fulfill( $row, $payment_id );
    if ( is_wp_error( $user_id ) ) {
        // Release back to pending AND return 5xx so Mercado Pago retries the delivery
        // (transient DB/SMTP/MP hiccups then self-heal on the next attempt).
        $wpdb->update( $table, array( 'status' => 'pending' ), array( 'id' => $row->id ) );
        c360co_log( 'fulfill_error', $user_id->get_error_message() );
        return new WP_REST_Response( array( 'ok' => false, 'retry' => true ), 500 );
    }
    return new WP_REST_Response( array( 'ok' => true, 'user' => $user_id ), 200 );
}

/* ──────────────────────────────────────────────────────────────
 * 6. FULFILL — create user (role auto-enrolls) + email + optional direct enroll
 * ────────────────────────────────────────────────────────────── */
function c360co_fulfill( $row, $payment_id ) {
    $role  = c360co_get( 'role_slug', 'cirrosis_student' );
    $email = $row->email;

    $existing = get_user_by( 'email', $email );
    if ( $existing ) {
        $user_id = $existing->ID;
        // ensure the student role (this re-fires the LMS auto-enroll hook)
        $u = new WP_User( $user_id );
        if ( ! in_array( $role, (array) $u->roles, true ) ) { $u->add_role( $role ); }
        $password = null; // do not reset an existing account's password
    } else {
        $base = sanitize_user( current( explode( '@', $email ) ), true );
        $username = $base; $i = 1;
        while ( username_exists( $username ) ) { $username = $base . $i; $i++; }
        $password = wp_generate_password( 12 );
        $user_id  = wp_insert_user( array(
            'user_login'   => $username,
            'user_pass'    => $password,
            'user_email'   => $email,
            'display_name' => $row->nombre,
            'first_name'   => $row->nombre,
            'role'         => $role,   // <-- LMS auto-enrolls on this role (user_register/set_user_role)
        ) );
        if ( is_wp_error( $user_id ) ) return $user_id;
        update_user_meta( $user_id, 'c360_whatsapp', $row->whatsapp );
    }

    // Direct enrollment (ALWAYS) using the cirrosis360-lms meta scheme.
    // The LMS only auto-enrolls users created by an admin; the webhook has no admin context,
    // so a role-only path could land the buyer in "pending". This guarantees active access.
    c360co_direct_enroll( $user_id );

    // Send access email and record the outcome so a stuck buyer is recoverable from the Orders page.
    $sent = c360co_send_access_email( $row, $user_id, $password );
    if ( ! $sent ) c360co_log( 'mail_failed', 'order ' . $row->order_ref . ' user ' . $user_id );

    global $wpdb;
    $wpdb->update( $wpdb->prefix . C360CO_TABLE,
        array(
            'status'     => $sent ? 'completed' : 'completed_noemail',
            'payment_id' => (string) $payment_id,
            'user_id'    => $user_id,
        ),
        array( 'id' => $row->id )
    );
    return $user_id;
}

/** Enrollment matching the LIVE cirrosis360-lms meta scheme (verified on prod 2026-06-22).
 *  Keys: _c360_enrolled, _c360_enrolled_course, _c360_enrolled_date, _c360_enroll_days, _c360_source. */
function c360co_direct_enroll( $user_id ) {
    $course = absint( c360co_get( 'course_id', 10 ) );
    update_user_meta( $user_id, '_c360_enrolled', 1 );
    if ( $course ) update_user_meta( $user_id, '_c360_enrolled_course', $course );
    if ( ! get_user_meta( $user_id, '_c360_enrolled_date', true ) ) {
        update_user_meta( $user_id, '_c360_enrolled_date', current_time( 'mysql' ) );
    }
    if ( ! get_user_meta( $user_id, '_c360_enroll_days', true ) ) {
        update_user_meta( $user_id, '_c360_enroll_days', absint( c360co_get( 'enroll_days', 180 ) ) );
    }
    update_user_meta( $user_id, '_c360_source', 'checkout' );
    delete_user_meta( $user_id, '_c360_pending' );
}

/* ──────────────────────────────────────────────────────────────
 * 7. ACCESS EMAIL
 * ────────────────────────────────────────────────────────────── */
function c360co_send_access_email( $row, $user_id, $password ) {
    $portal = c360co_get( 'portal_url', home_url() );
    $from   = c360co_get( 'from_name', 'Dr. Julio Santiago Marcelo' );
    $user   = get_user_by( 'id', $user_id );
    $login  = $user ? $user->user_login : $row->email;

    $cred = $password
        ? "Usuario: {$login}\nContraseña: {$password}"
        : "Usuario: {$login}\n(Ya tenía una cuenta — use su contraseña habitual. Puede restablecerla en " . wp_lostpassword_url() . ")";

    $subject = 'Sus accesos al Programa Cirrosis 360';
    $body =
"Hola {$row->nombre},

¡Su pago fue confirmado y su inscripción está activa! 🎉

Acceda al portal del curso aquí:
{$portal}

Sus datos de acceso:
{$cred}

Su acceso es válido por 6 meses. Si tiene cualquier duda, responda a este correo.

Un saludo,
{$from}";

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
    return wp_mail( $row->email, $subject, $body, $headers );
}

/* ──────────────────────────────────────────────────────────────
 * 8. SIGNATURE VERIFY (MP x-signature)  — skipped if no secret set
 * ────────────────────────────────────────────────────────────── */
function c360co_verify_signature( WP_REST_Request $req, $payment_id ) {
    $secret = c360co_get( 'webhook_secret' );
    if ( ! $secret ) return true; // not configured -> allow (payment is re-fetched from MP anyway)

    $sig = $req->get_header( 'x_signature' );
    $rid = $req->get_header( 'x_request_id' );
    if ( ! $sig ) return false;

    $ts = ''; $v1 = '';
    foreach ( explode( ',', $sig ) as $part ) {
        $kv = explode( '=', trim( $part ), 2 );
        if ( count( $kv ) === 2 ) {
            if ( $kv[0] === 'ts' ) $ts = $kv[1];
            if ( $kv[0] === 'v1' ) $v1 = $kv[1];
        }
    }
    if ( ! $ts || ! $v1 ) return false;

    $manifest = "id:{$payment_id};request-id:{$rid};ts:{$ts};";
    $calc = hash_hmac( 'sha256', $manifest, $secret );
    return hash_equals( $calc, $v1 );
}

/* ──────────────────────────────────────────────────────────────
 * 9. LIGHT LOGGER (option ring buffer, last 30)
 * ────────────────────────────────────────────────────────────── */
function c360co_log( $tag, $msg ) {
    $log = get_option( 'c360co_log', array() );
    $log[] = array( 't' => current_time( 'mysql' ), 'tag' => $tag, 'msg' => substr( (string) $msg, 0, 500 ) );
    if ( count( $log ) > 30 ) $log = array_slice( $log, -30 );
    update_option( 'c360co_log', $log, false );
}

/* ──────────────────────────────────────────────────────────────
 * 10. ORDERS ADMIN PAGE  (operator visibility + manual recovery)
 * ────────────────────────────────────────────────────────────── */
function c360co_orders_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    global $wpdb;
    $table  = $wpdb->prefix . C360CO_TABLE;
    $rows   = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC LIMIT 200" );
    $notice = isset( $_GET['c360_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['c360_msg'] ) ) : '';
    $labels = array(
        'completed'        => '✅ Completado',
        'completed_noemail'=> '⚠️ Pagado — correo NO enviado',
        'processing'       => '⏳ Procesando',
        'pending'          => '🕓 Pendiente (sin pago)',
    );
    ?>
    <div class="wrap">
      <h1>Inscripciones — Cirrosis 360</h1>
      <?php if ( $notice === 'resent' ) : ?><div class="notice notice-success"><p>Correo de acceso reenviado.</p></div><?php endif; ?>
      <?php if ( $notice === 'resent_fail' ) : ?><div class="notice notice-error"><p>No se pudo reenviar el correo (revise la configuración SMTP).</p></div><?php endif; ?>
      <table class="wp-list-table widefat fixed striped">
        <thead><tr>
          <th>Fecha</th><th>Nombre</th><th>Email</th><th>WhatsApp</th><th>Estado</th><th>Pago MP</th><th>Acción</th>
        </tr></thead>
        <tbody>
        <?php if ( ! $rows ) : ?>
          <tr><td colspan="7">Aún no hay inscripciones.</td></tr>
        <?php else : foreach ( $rows as $r ) :
          $resend = wp_nonce_url(
            admin_url( 'admin-post.php?action=c360_resend&order=' . (int) $r->id ),
            'c360_resend_' . (int) $r->id
          );
          $can_resend = in_array( $r->status, array( 'completed', 'completed_noemail' ), true ) && $r->user_id;
        ?>
          <tr>
            <td><?php echo esc_html( $r->created_at ); ?></td>
            <td><?php echo esc_html( $r->nombre ); ?></td>
            <td><?php echo esc_html( $r->email ); ?></td>
            <td><?php echo esc_html( $r->whatsapp ); ?></td>
            <td><?php echo esc_html( isset( $labels[ $r->status ] ) ? $labels[ $r->status ] : $r->status ); ?></td>
            <td><?php echo esc_html( $r->payment_id ); ?></td>
            <td><?php if ( $can_resend ) : ?><a class="button button-small" href="<?php echo esc_url( $resend ); ?>">Reenviar acceso</a><?php else : ?>—<?php endif; ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
      <p class="description">Muestra las últimas 200 inscripciones. "Reenviar acceso" genera una nueva contraseña para el alumno y le reenvía el correo.</p>
    </div>
    <?php
}

/** Admin action: regenerate the student's password and re-send the access email. */
add_action( 'admin_post_c360_resend', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado.' );
    $order = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
    check_admin_referer( 'c360_resend_' . $order );

    global $wpdb;
    $table = $wpdb->prefix . C360CO_TABLE;
    $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $order ) );
    $back  = admin_url( 'admin.php?page=c360co_orders' );

    if ( ! $row || ! $row->user_id ) { wp_safe_redirect( add_query_arg( 'c360_msg', 'resent_fail', $back ) ); exit; }

    $password = wp_generate_password( 12 );
    wp_set_password( $password, $row->user_id );          // reset so the emailed credential is valid
    $sent = c360co_send_access_email( $row, $row->user_id, $password );

    if ( $sent && $row->status === 'completed_noemail' ) {
        $wpdb->update( $table, array( 'status' => 'completed' ), array( 'id' => $row->id ) );
    }
    wp_safe_redirect( add_query_arg( 'c360_msg', $sent ? 'resent' : 'resent_fail', $back ) );
    exit;
} );

/* ──────────────────────────────────────────────────────────────
 * 11. THANK-YOU NOTICE on the success return (?c360=ok)
 * ────────────────────────────────────────────────────────────── */
add_filter( 'the_content', function ( $content ) {
    if ( isset( $_GET['c360'] ) && $_GET['c360'] === 'ok' && in_the_loop() && is_main_query() ) {
        $box = '<div style="background:#e9f6ef;border:1px solid #1f6b54;color:#16503f;padding:16px 20px;border-radius:10px;margin-bottom:18px;">'
             . '<strong>¡Pago recibido!</strong> En unos minutos recibirá un correo con sus accesos al curso. Si no lo ve, revise su carpeta de spam o escríbanos por WhatsApp.'
             . '</div>';
        return $box . $content;
    }
    return $content;
} );
