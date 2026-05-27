<?php
/**
 * Plugin Name: Cirrosis 360 — LMS
 * Plugin URI: https://juliosantiagomarcelo.com
 * Description: Custom multi-course LMS with admin-approved registration, MercadoPago webhook auto-enroll, per-user 6-month expiry, quiz scoring, progress tracking.
 * Version: 1.4.3
 * Author: SkynetLabs / Waseem Nasir
 * Author URI: https://www.skynetjoe.com
 * License: GPL-2.0-or-later
 * Text Domain: cirrosis360-lms
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package Cirrosis360_LMS
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'C360_LMS_VERSION', '1.4.3' );
define( 'C360_LMS_FILE', __FILE__ );
define( 'C360_LMS_DIR', plugin_dir_path( __FILE__ ) );
define( 'C360_LMS_URL', plugin_dir_url( __FILE__ ) );
define( 'C360_LMS_ENROLL_KEY', '_c360_enrolled' );
define( 'C360_LMS_ENROLL_DATE_KEY', '_c360_enrolled_date' );
define( 'C360_LMS_EXPIRY_DAYS', 180 );
define( 'C360_LMS_PROGRESS_KEY', '_c360_progress' );
define( 'C360_LMS_QUIZ_KEY_PREFIX', '_c360_quiz_' );

require_once C360_LMS_DIR . 'includes/class-cpt.php';
require_once C360_LMS_DIR . 'includes/class-config.php';
require_once C360_LMS_DIR . 'includes/class-storage.php';
require_once C360_LMS_DIR . 'includes/class-enrollment.php';
require_once C360_LMS_DIR . 'includes/class-approval.php';
require_once C360_LMS_DIR . 'includes/class-registration.php';
require_once C360_LMS_DIR . 'includes/class-router.php';
require_once C360_LMS_DIR . 'includes/class-templates.php';
require_once C360_LMS_DIR . 'includes/class-shortcodes.php';
require_once C360_LMS_DIR . 'includes/class-admin.php';
require_once C360_LMS_DIR . 'includes/class-mercadopago.php';

register_activation_hook( __FILE__, function () {
    C360_LMS_CPT::register();
    C360_LMS_Router::register_rewrites();
    C360_LMS_Storage::create_tables();
    update_option( C360_LMS_Storage::DB_VERSION_OPT, C360_LMS_Storage::DB_VERSION );
    flush_rewrite_rules();
    if ( ! get_role( 'cirrosis_student' ) ) {
        add_role( 'cirrosis_student', 'Estudiante Cirrosis 360', array( 'read' => true ) );
    }
    if ( method_exists( 'C360_LMS_Config', 'maybe_seed_default' ) ) {
        C360_LMS_Config::maybe_seed_default();
    }
    if ( get_option( C360_LMS_Approval::OPT_REQUIRE, null ) === null ) {
        update_option( C360_LMS_Approval::OPT_REQUIRE, 1 );
    }
    // Auto-seed a webhook secret so it's never empty on first install.
    if ( ! get_option( C360_LMS_MercadoPago::SECRET_OPT, '' ) ) {
        update_option( C360_LMS_MercadoPago::SECRET_OPT, wp_generate_password( 40, false ) );
    }
    set_transient( 'c360_lms_activation_notice', 1, MINUTE_IN_SECONDS * 30 );
} );

register_deactivation_hook( __FILE__, function () {
    flush_rewrite_rules();
} );

C360_LMS_Storage::init();

add_action( 'plugins_loaded', function () {
    C360_LMS_CPT::init();
    C360_LMS_Router::init();
    C360_LMS_Templates::init();
    C360_LMS_Shortcodes::init();
    C360_LMS_Admin::init();
    C360_LMS_MercadoPago::init();
    C360_LMS_Approval::init();
    C360_LMS_Registration::init();
} );

// Front-end asset enqueue (only on LMS routes).
add_action( 'wp_enqueue_scripts', function () {
    wp_register_style( 'c360-lms', C360_LMS_URL . 'assets/lms.css', array(), C360_LMS_VERSION );
    wp_register_script( 'c360-lms', C360_LMS_URL . 'assets/lms.js', array(), C360_LMS_VERSION, true );
    if ( class_exists( 'C360_LMS_Router' ) && C360_LMS_Router::is_lms_request() ) {
        wp_enqueue_style( 'c360-lms' );
        wp_enqueue_script( 'c360-lms' );
        wp_localize_script( 'c360-lms', 'c360LMS', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'c360_lms' ),
        ) );
    }
}, 30 );

// Security headers on LMS routes.
add_action( 'send_headers', function () {
    if ( is_admin() ) return;
    if ( ! class_exists( 'C360_LMS_Router' ) ) return;
    // Send strict CSP-friendly headers regardless; LMS pages can be hardened further.
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
} );
