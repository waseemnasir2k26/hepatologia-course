<?php
/**
 * Persistent audit-trail storage for Cirrosis 360 LMS.
 *
 * Tables:
 *   - {prefix}c360_quiz_attempts  — every quiz submission (full answers + score)
 *   - {prefix}c360_lesson_views   — lesson opens + completions
 *
 * Idempotent dbDelta on activation + plugins_loaded version check.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Storage {

    const DB_VERSION_OPT = 'c360_lms_db_version';
    const DB_VERSION     = '1.0.0';

    public static function init() {
        add_action( 'plugins_loaded', array( __CLASS__, 'maybe_upgrade' ), 5 );
    }

    public static function table_attempts() {
        global $wpdb;
        return $wpdb->prefix . 'c360_quiz_attempts';
    }

    public static function table_views() {
        global $wpdb;
        return $wpdb->prefix . 'c360_lesson_views';
    }

    public static function maybe_upgrade() {
        if ( get_option( self::DB_VERSION_OPT ) === self::DB_VERSION ) return;
        self::create_tables();
        update_option( self::DB_VERSION_OPT, self::DB_VERSION );
    }

    public static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        $attempts = self::table_attempts();
        $views    = self::table_views();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_attempts = "CREATE TABLE $attempts (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            course_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            quiz_slug VARCHAR(64) NOT NULL,
            score TINYINT UNSIGNED NOT NULL DEFAULT 0,
            passed TINYINT(1) NOT NULL DEFAULT 0,
            total_questions SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            correct_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            answers_json LONGTEXT NOT NULL,
            detail_json LONGTEXT NOT NULL,
            ip VARCHAR(45) NOT NULL DEFAULT '',
            user_agent VARCHAR(255) NOT NULL DEFAULT '',
            attempt_no SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            submitted_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY quiz_slug (quiz_slug),
            KEY user_quiz (user_id, quiz_slug),
            KEY submitted_at (submitted_at)
        ) $charset;";

        $sql_views = "CREATE TABLE $views (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            course_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            lesson_slug VARCHAR(64) NOT NULL,
            event VARCHAR(20) NOT NULL DEFAULT 'view',
            ip VARCHAR(45) NOT NULL DEFAULT '',
            user_agent VARCHAR(255) NOT NULL DEFAULT '',
            occurred_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY lesson_slug (lesson_slug),
            KEY user_lesson (user_id, lesson_slug),
            KEY occurred_at (occurred_at)
        ) $charset;";

        dbDelta( $sql_attempts );
        dbDelta( $sql_views );
    }

    private static function client_ip() {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
        if ( defined( 'C360_TRUST_PROXY' ) && C360_TRUST_PROXY && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $fwd = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            $ip = trim( $fwd[0] );
        }
        return substr( (string) $ip, 0, 45 );
    }

    private static function client_ua() {
        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return substr( (string) $ua, 0, 255 );
    }

    public static function log_quiz_attempt( $user_id, $course_id, $quiz_slug, $score, $passed, $total, $correct, $answers, $detail ) {
        global $wpdb;
        $table = self::table_attempts();
        $prior = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id=%d AND quiz_slug=%s",
            $user_id, $quiz_slug
        ) );
        $wpdb->insert( $table, array(
            'user_id'         => absint( $user_id ),
            'course_id'       => absint( $course_id ),
            'quiz_slug'       => sanitize_key( $quiz_slug ),
            'score'           => max( 0, min( 100, intval( $score ) ) ),
            'passed'          => $passed ? 1 : 0,
            'total_questions' => absint( $total ),
            'correct_count'   => absint( $correct ),
            'answers_json'    => wp_json_encode( $answers, JSON_UNESCAPED_UNICODE ),
            'detail_json'     => wp_json_encode( $detail, JSON_UNESCAPED_UNICODE ),
            'ip'              => self::client_ip(),
            'user_agent'      => self::client_ua(),
            'attempt_no'      => $prior + 1,
            'submitted_at'    => current_time( 'mysql' ),
        ), array( '%d','%d','%s','%d','%d','%d','%d','%s','%s','%s','%s','%d','%s' ) );
        return $wpdb->insert_id;
    }

    public static function log_lesson_event( $user_id, $course_id, $lesson_slug, $event = 'view' ) {
        global $wpdb;
        $wpdb->insert( self::table_views(), array(
            'user_id'     => absint( $user_id ),
            'course_id'   => absint( $course_id ),
            'lesson_slug' => sanitize_key( $lesson_slug ),
            'event'       => sanitize_key( $event ),
            'ip'          => self::client_ip(),
            'user_agent'  => self::client_ua(),
            'occurred_at' => current_time( 'mysql' ),
        ), array( '%d','%d','%s','%s','%s','%s','%s' ) );
        return $wpdb->insert_id;
    }

    public static function get_user_attempts( $user_id, $quiz_slug = '' ) {
        global $wpdb;
        $table = self::table_attempts();
        if ( $quiz_slug ) {
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table WHERE user_id=%d AND quiz_slug=%s ORDER BY submitted_at DESC",
                $user_id, $quiz_slug
            ), ARRAY_A );
        }
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE user_id=%d ORDER BY submitted_at DESC",
            $user_id
        ), ARRAY_A );
    }

    public static function get_user_views( $user_id ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::table_views() . " WHERE user_id=%d ORDER BY occurred_at DESC",
            $user_id
        ), ARRAY_A );
    }

    public static function get_all_attempts( $limit = 1000, $offset = 0 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::table_attempts() . " ORDER BY submitted_at DESC LIMIT %d OFFSET %d",
            $limit, $offset
        ), ARRAY_A );
    }

    public static function get_all_views( $limit = 1000, $offset = 0 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . self::table_views() . " ORDER BY occurred_at DESC LIMIT %d OFFSET %d",
            $limit, $offset
        ), ARRAY_A );
    }

    public static function count_attempts() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::table_attempts() );
    }

    public static function count_views() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::table_views() );
    }

    public static function last_attempt( $user_id, $quiz_slug ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::table_attempts() . " WHERE user_id=%d AND quiz_slug=%s ORDER BY submitted_at DESC LIMIT 1",
            $user_id, $quiz_slug
        ), ARRAY_A );
    }

    public static function best_attempt( $user_id, $quiz_slug ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . self::table_attempts() . " WHERE user_id=%d AND quiz_slug=%s ORDER BY score DESC, submitted_at DESC LIMIT 1",
            $user_id, $quiz_slug
        ), ARRAY_A );
    }
}
