<?php
/**
 * Plugin Name: Cirrosis 360 — Inspector (solo lectura)
 * Description: Endpoint de diagnóstico SOLO-LECTURA, protegido por token, para verificar el estado del sitio (roles, cursos, ajustes, inscripciones, logs) de forma remota. NO escribe nada. NO expone secretos. Desinstale tras el diagnóstico.
 * Version: 1.0.0
 * Author: SkynetLabs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'C360INS_OPT_KEY', 'c360ins_token' );

/* Generate a random token on activation (or allow a wp-config constant override). */
register_activation_hook( __FILE__, function () {
    if ( ! get_option( C360INS_OPT_KEY ) ) {
        update_option( C360INS_OPT_KEY, wp_generate_password( 40, false, false ), false );
    }
} );

function c360ins_token() {
    if ( defined( 'C360INS_TOKEN' ) ) return C360INS_TOKEN;
    return (string) get_option( C360INS_OPT_KEY, '' );
}

/* Mask an email like ju***@gmail.com */
function c360ins_mask_email( $e ) {
    if ( ! is_string( $e ) || strpos( $e, '@' ) === false ) return '';
    list( $u, $d ) = explode( '@', $e, 2 );
    $u = strlen( $u ) <= 2 ? substr( $u, 0, 1 ) . '*' : substr( $u, 0, 2 ) . str_repeat( '*', max( 1, strlen( $u ) - 2 ) );
    return $u . '@' . $d;
}

/* Mask a secret -> show only length + last 4 chars, never the value. */
function c360ins_mask_secret( $s ) {
    $s = (string) $s;
    if ( $s === '' ) return '(vacío)';
    return 'len=' . strlen( $s ) . ' …' . substr( $s, -4 );
}

/* ── Admin notice + tiny page showing the inspect URL ── */
add_action( 'admin_menu', function () {
    add_management_page( 'C360 Inspector', 'C360 Inspector', 'manage_options', 'c360ins', function () {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $url = add_query_arg( 'key', c360ins_token(), rest_url( 'c360-inspect/v1/state' ) );
        echo '<div class="wrap"><h1>Cirrosis 360 — Inspector (solo lectura)</h1>';
        echo '<p>Copie esta URL completa y envíela a su desarrollador. Es solo lectura, no expone contraseñas ni el token de Mercado Pago, y puede desinstalar el plugin después.</p>';
        echo '<p><textarea readonly rows="4" style="width:100%;font-family:monospace;font-size:13px;" onclick="this.select()">' . esc_textarea( $url ) . '</textarea></p>';
        echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '" target="_blank">Abrir vista JSON</a></p>';
        echo '</div>';
    } );
} );

/* ── The read-only REST endpoint ── */
add_action( 'rest_api_init', function () {
    register_rest_route( 'c360-inspect/v1', '/state', array(
        'methods'             => 'GET',
        'callback'            => 'c360ins_state',
        'permission_callback' => '__return_true',
    ) );
} );

function c360ins_state( WP_REST_Request $req ) {
    $key = (string) $req->get_param( 'key' );
    $tok = c360ins_token();
    if ( ! $tok || ! hash_equals( $tok, $key ) ) {
        return new WP_REST_Response( array( 'error' => 'unauthorized' ), 401 );
    }

    global $wpdb;
    $out = array();

    // Core
    $out['site'] = array(
        'wp_version'   => get_bloginfo( 'version' ),
        'site_url'     => site_url(),
        'home_url'     => home_url(),
        'active_theme' => ( wp_get_theme() )->get( 'Name' ),
        'php'          => PHP_VERSION,
        'timezone'     => wp_timezone_string(),
        'server_time'  => current_time( 'mysql' ),
    );

    // Active plugins
    $out['active_plugins'] = array_values( (array) get_option( 'active_plugins', array() ) );
    if ( is_multisite() ) $out['network_plugins'] = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) );

    // Roles (slug => display name) — confirms cirrosis_student exists
    $roles = wp_roles()->roles;
    $out['roles'] = array();
    foreach ( $roles as $slug => $r ) $out['roles'][ $slug ] = isset( $r['name'] ) ? $r['name'] : $slug;

    // Public post types + counts (helps locate the "course/curso" type)
    $out['post_types'] = array();
    foreach ( get_post_types( array(), 'objects' ) as $pt ) {
        $cnt = wp_count_posts( $pt->name );
        $out['post_types'][ $pt->name ] = array(
            'label'   => $pt->label,
            'public'  => (bool) $pt->public,
            'publish' => isset( $cnt->publish ) ? (int) $cnt->publish : 0,
        );
    }

    // Likely course posts (any post type whose name/label contains course/curso/lesson/leccion)
    $out['courses'] = array();
    foreach ( get_post_types( array(), 'names' ) as $ptn ) {
        if ( preg_match( '/course|curso|lesson|leccion|lms|tutor/i', $ptn ) ) {
            $posts = get_posts( array( 'post_type' => $ptn, 'numberposts' => 25, 'post_status' => 'any' ) );
            foreach ( $posts as $p ) {
                $out['courses'][] = array( 'type' => $ptn, 'ID' => $p->ID, 'title' => $p->post_title, 'status' => $p->post_status );
            }
        }
    }

    // Sample students: users per role that looks like a student, with their c360/enroll meta keys
    $out['sample_students'] = array();
    foreach ( array_keys( $out['roles'] ) as $slug ) {
        if ( ! preg_match( '/student|alumno|cirrosis|subscriber|customer/i', $slug ) ) continue;
        $users = get_users( array( 'role' => $slug, 'number' => 3, 'orderby' => 'ID', 'order' => 'ASC' ) );
        foreach ( $users as $u ) {
            $meta = get_user_meta( $u->ID );
            $c360meta = array();
            foreach ( $meta as $k => $v ) {
                if ( preg_match( '/c360|enroll|inscri|course|curso|expire|expira|tutor/i', $k ) ) {
                    $c360meta[ $k ] = is_array( $v ) ? array_map( 'strval', $v ) : (string) $v;
                }
            }
            $out['sample_students'][] = array(
                'role'      => $slug,
                'user_id'   => $u->ID,
                'login'     => $u->user_login,
                'email'     => c360ins_mask_email( $u->user_email ),
                'enroll_meta' => $c360meta,
            );
        }
    }

    // Checkout plugin settings (secrets MASKED)
    $co = get_option( 'c360co_settings', array() );
    if ( $co ) {
        // ALLOWLIST — only known non-sensitive keys are ever returned; secrets are masked explicitly.
        $safe_keys = array( 'course_id', 'role_slug', 'price', 'currency', 'product_name', 'portal_url', 'success_url', 'failure_url', 'from_name', 'direct_enroll' );
        $safe = array();
        foreach ( $safe_keys as $k ) if ( isset( $co[ $k ] ) ) $safe[ $k ] = $co[ $k ];
        $safe['access_token']   = c360ins_mask_secret( isset( $co['access_token'] ) ? $co['access_token'] : '' );
        $safe['webhook_secret'] = c360ins_mask_secret( isset( $co['webhook_secret'] ) ? $co['webhook_secret'] : '' );
        $out['c360_checkout_settings'] = $safe;
        $out['c360_webhook_url'] = rest_url( 'c360/v1/webhook' );
    } else {
        $out['c360_checkout_settings'] = '(plugin c360-checkout no configurado / no instalado)';
    }

    // Orders table (emails masked)
    $table = $wpdb->prefix . 'c360co_orders';
    $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
    $out['c360_orders'] = array();
    if ( $exists ) {
        $rows = $wpdb->get_results( "SELECT id, order_ref, nombre, email, whatsapp, status, payment_id, user_id, created_at FROM $table ORDER BY id DESC LIMIT 25" );
        foreach ( (array) $rows as $r ) {
            $r->email = c360ins_mask_email( $r->email );
            $out['c360_orders'][] = $r;
        }
    } else {
        $out['c360_orders'] = '(tabla de inscripciones no existe aún)';
    }

    // Plugin log
    $out['c360_log'] = get_option( 'c360co_log', array() );

    // Mailer detection
    $mailers = array();
    foreach ( $out['active_plugins'] as $pl ) {
        if ( preg_match( '/smtp|mailer|sendgrid|mailgun|postmark|ses|brevo|sendinblue|fluentsmtp|wp-mail/i', $pl ) ) $mailers[] = $pl;
    }
    $out['smtp_plugins'] = $mailers ?: '(ninguno detectado — wp_mail usará PHP mail())';

    // Theme mods relevant to checkout/price (both themes)
    $out['theme_mods'] = array(
        'juliosm_mercadopago' => get_theme_mod( 'juliosm_mercadopago' ),
        'jsma_mercadopago'    => get_theme_mod( 'jsma_mercadopago' ),
        'juliosm_precio_lanz' => get_theme_mod( 'juliosm_precio_lanz' ),
        'jsma_precio_lanz'    => get_theme_mod( 'jsma_precio_lanz' ),
    );

    return new WP_REST_Response( $out, 200 );
}
