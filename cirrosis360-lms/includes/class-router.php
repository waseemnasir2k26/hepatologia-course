<?php
/**
 * Virtual page router. Owns:
 *   /courses/                 — public catalog (all courses)
 *   /courses/{course-slug}/   — single course detail
 *   /dashboard/               — student dashboard
 *   /lesson/{lesson-slug}/    — single lesson (enrollment-gated)
 *   /quiz/{lesson-slug}/      — quiz (enrollment-gated)
 *   /register/                — custom registration page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Router {

    const QV       = 'c360_lms_view';
    const QV_SLUG  = 'c360_lms_slug';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_rewrites' ) );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
        add_action( 'template_redirect', array( __CLASS__, 'route' ) );
        add_action( 'wp_ajax_c360_quiz_submit', array( __CLASS__, 'ajax_quiz_submit' ) );
        add_action( 'wp_ajax_c360_lesson_complete', array( __CLASS__, 'ajax_lesson_complete' ) );
    }

    public static function register_rewrites() {
        add_rewrite_rule( '^courses/?$',                  'index.php?' . self::QV . '=courses', 'top' );
        add_rewrite_rule( '^courses/([^/]+)/?$',          'index.php?' . self::QV . '=course&' . self::QV_SLUG . '=$matches[1]', 'top' );
        add_rewrite_rule( '^dashboard/?$',                'index.php?' . self::QV . '=dashboard', 'top' );
        add_rewrite_rule( '^lesson/([^/]+)/?$',           'index.php?' . self::QV . '=lesson&' . self::QV_SLUG . '=$matches[1]', 'top' );
        add_rewrite_rule( '^quiz/([^/]+)/?$',             'index.php?' . self::QV . '=quiz&' . self::QV_SLUG . '=$matches[1]', 'top' );
        add_rewrite_rule( '^register/?$',                 'index.php?' . self::QV . '=register', 'top' );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = self::QV;
        $vars[] = self::QV_SLUG;
        return $vars;
    }

    public static function current_view() {
        return get_query_var( self::QV );
    }

    public static function current_slug() {
        return sanitize_key( get_query_var( self::QV_SLUG ) );
    }

    public static function is_lms_request() {
        return (bool) self::current_view();
    }

    public static function route() {
        $view = self::current_view();
        if ( ! $view ) return;

        $needs_login = in_array( $view, array( 'dashboard', 'lesson', 'quiz' ), true );
        if ( $needs_login && ! is_user_logged_in() ) {
            $redirect = self::safe_redirect_target();
            wp_safe_redirect( wp_login_url( $redirect ) );
            exit;
        }

        status_header( 200 );
        nocache_headers();

        switch ( $view ) {
            case 'courses':
                C360_LMS_Templates::render( 'courses' );
                break;
            case 'course':
                $course = C360_LMS_Config::course( self::current_slug() );
                if ( ! $course ) { self::not_found(); return; }
                C360_LMS_Templates::render( 'course', array( 'course' => $course ) );
                break;
            case 'dashboard':
                C360_LMS_Templates::render( 'dashboard' );
                break;
            case 'lesson':
                $lesson = C360_LMS_Config::lesson( self::current_slug() );
                if ( ! $lesson ) { self::not_found(); return; }
                $course_id = (int) $lesson['course_id'];
                $is_admin_preview = current_user_can( 'manage_options' );
                if ( ! $is_admin_preview && ( ! $course_id || ! C360_LMS_Enrollment::is_enrolled( null, $course_id ) ) ) {
                    wp_safe_redirect( home_url( '/dashboard/' ) ); exit;
                }
                if ( class_exists( 'C360_LMS_Storage' ) && ! $is_admin_preview ) {
                    C360_LMS_Storage::log_lesson_event( get_current_user_id(), $course_id, self::current_slug(), 'view' );
                }
                C360_LMS_Templates::render( 'lesson', array( 'lesson' => $lesson, 'admin_preview' => $is_admin_preview ) );
                break;
            case 'quiz':
                $lesson = C360_LMS_Config::lesson( self::current_slug() );
                $quiz   = C360_LMS_Config::quiz( self::current_slug() );
                if ( ! $lesson || ! $quiz ) { self::not_found(); return; }
                $course_id = (int) $lesson['course_id'];
                $is_admin_preview = current_user_can( 'manage_options' );
                if ( ! $is_admin_preview && ( ! $course_id || ! C360_LMS_Enrollment::is_enrolled( null, $course_id ) ) ) {
                    wp_safe_redirect( home_url( '/dashboard/' ) ); exit;
                }
                if ( class_exists( 'C360_LMS_Storage' ) && ! $is_admin_preview ) {
                    C360_LMS_Storage::log_lesson_event( get_current_user_id(), $course_id, self::current_slug(), 'quiz_view' );
                }
                C360_LMS_Templates::render( 'quiz', array( 'lesson' => $lesson, 'quiz' => $quiz, 'admin_preview' => $is_admin_preview ) );
                break;
            case 'register':
                C360_LMS_Templates::render( 'register' );
                break;
        }
        exit;
    }

    public static function not_found() {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        nocache_headers();
        get_template_part( '404' );
        exit;
    }

    /** Sanitize REQUEST_URI for use as login redirect target. Same-host only. */
    private static function safe_redirect_target() {
        $req = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
        $req = wp_validate_redirect( home_url( $req ), home_url( '/' ) );
        return $req;
    }

    public static function ajax_quiz_submit() {
        check_ajax_referer( 'c360_lms', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( array( 'msg' => 'No autenticado.' ), 401 );
        $uid = get_current_user_id();
        $slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
        $answers_raw = isset( $_POST['answers'] ) ? (array) wp_unslash( $_POST['answers'] ) : array();
        $answers = array();
        foreach ( $answers_raw as $k => $v ) $answers[ intval( $k ) ] = intval( $v );

        $quiz = C360_LMS_Config::quiz( $slug );
        $lesson = C360_LMS_Config::lesson( $slug );
        if ( ! $quiz || ! $lesson ) wp_send_json_error( array( 'msg' => 'Quiz no encontrado.' ), 404 );
        $course_id = (int) $lesson['course_id'];
        if ( ! $course_id ) wp_send_json_error( array( 'msg' => 'Lección sin curso.' ), 400 );
        if ( ! C360_LMS_Enrollment::is_enrolled( null, $course_id, false ) ) {
            wp_send_json_error( array( 'msg' => 'No inscrito.' ), 403 );
        }

        // Rate-limit attempts per user/quiz: 5/hour.
        $rate_key = 'c360_qz_' . $uid . '_' . md5( $slug );
        $attempts = (int) get_transient( $rate_key );
        if ( $attempts >= 5 ) wp_send_json_error( array( 'msg' => 'Demasiados intentos. Espera 1 hora.' ), 429 );
        set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

        $total = count( $quiz['questions'] );
        if ( count( $answers ) < $total ) wp_send_json_error( array( 'msg' => 'Faltan respuestas.' ), 400 );

        $correct = 0;
        $detail = array();
        foreach ( $quiz['questions'] as $i => $q ) {
            $opts_n = is_array( $q['options'] ) ? count( $q['options'] ) : 0;
            $pick = isset( $answers[ $i ] ) ? $answers[ $i ] : -1;
            if ( $pick < 0 || $pick >= $opts_n ) $pick = -1;
            $is_right = ( $pick === intval( $q['correct'] ) && $pick !== -1 );
            if ( $is_right ) $correct++;
            // Do NOT leak correct answer index.
            $detail[] = array( 'i' => $i, 'right' => $is_right );
        }
        $score = $total ? intval( ( $correct / $total ) * 100 ) : 0;
        $passed = $score >= intval( $quiz['pass_score'] );
        C360_LMS_Enrollment::save_quiz_score( $uid, $slug, $score, $passed );
        if ( class_exists( 'C360_LMS_Storage' ) ) {
            C360_LMS_Storage::log_quiz_attempt( $uid, $course_id, $slug, $score, $passed, $total, $correct, $answers, $detail );
        }
        if ( $passed ) C360_LMS_Enrollment::mark_complete( $uid, $slug );

        wp_send_json_success( array(
            'score'      => $score,
            'passed'     => $passed,
            'pass_score' => intval( $quiz['pass_score'] ),
            'detail'     => $detail,
            'redirect'   => $passed ? home_url( '/dashboard/' ) : '',
        ) );
    }

    public static function ajax_lesson_complete() {
        check_ajax_referer( 'c360_lms', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( array(), 401 );
        $slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
        $lesson = C360_LMS_Config::lesson( $slug );
        if ( ! $lesson ) wp_send_json_error( array( 'msg' => 'Lección no encontrada.' ), 404 );
        $course_id = (int) $lesson['course_id'];
        if ( ! $course_id ) wp_send_json_error( array( 'msg' => 'Lección sin curso.' ), 400 );
        if ( ! C360_LMS_Enrollment::is_enrolled( null, $course_id, false ) ) wp_send_json_error( array( 'msg' => 'No inscrito.' ), 403 );
        $uid = get_current_user_id();
        C360_LMS_Enrollment::mark_complete( $uid, $slug );
        if ( class_exists( 'C360_LMS_Storage' ) ) {
            C360_LMS_Storage::log_lesson_event( $uid, $course_id, $slug, 'complete' );
        }
        wp_send_json_success( array( 'progress' => C360_LMS_Enrollment::progress_percent( $uid, $course_id ) ) );
    }
}
