<?php
/**
 * Enrollment + access checks + progress + quiz scores.
 * Multi-course aware via per-user meta _c360_enrolled_course (course post ID).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Enrollment {

    const META_COURSE = '_c360_enrolled_course';

    /**
     * Enroll user. Idempotent by default — if already enrolled (and not expired),
     * this only updates source/course; clock is NOT reset. Pass $reset=true to
     * force-reset the clock (used by manual admin re-enroll + approved MP payment).
     */
    public static function enroll( $user_id, $source = 'manual', $course_id = 0, $reset = false ) {
        $user_id = absint( $user_id );
        $course_id = absint( $course_id );
        if ( ! $user_id ) return false;

        if ( ! $course_id ) {
            $first = C360_LMS_Config::default_course();
            $course_id = $first ? (int) $first['id'] : 0;
        }

        $days = C360_LMS_EXPIRY_DAYS;
        if ( $course_id ) {
            $course = C360_LMS_Config::course( $course_id );
            if ( $course && ! empty( $course['duration_days'] ) ) $days = max( 1, (int) $course['duration_days'] );
        }

        $existing_date = get_user_meta( $user_id, C360_LMS_ENROLL_DATE_KEY, true );
        $expired = self::is_expired( $user_id );

        if ( $reset || ! $existing_date || $expired ) {
            update_user_meta( $user_id, C360_LMS_ENROLL_DATE_KEY, current_time( 'mysql' ) );
            update_user_meta( $user_id, '_c360_enroll_days', $days );
        }
        update_user_meta( $user_id, C360_LMS_ENROLL_KEY, 1 );
        update_user_meta( $user_id, '_c360_source', sanitize_text_field( $source ) );
        if ( $course_id ) update_user_meta( $user_id, self::META_COURSE, $course_id );

        $user = get_userdata( $user_id );
        if ( $user && ! in_array( 'cirrosis_student', (array) $user->roles, true ) && ! in_array( 'administrator', (array) $user->roles, true ) ) {
            $user->add_role( 'cirrosis_student' );
        }
        do_action( 'c360_lms_enrolled', $user_id, $source, $course_id );
        return true;
    }

    public static function extend( $user_id, $extra_days ) {
        $user_id = absint( $user_id );
        $extra_days = max( 0, absint( $extra_days ) );
        if ( ! $user_id || ! $extra_days ) return false;
        $current_days = (int) get_user_meta( $user_id, '_c360_enroll_days', true ) ?: C360_LMS_EXPIRY_DAYS;
        update_user_meta( $user_id, '_c360_enroll_days', $current_days + $extra_days );
        return true;
    }

    public static function unenroll( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) return false;
        delete_user_meta( $user_id, C360_LMS_ENROLL_KEY );
        delete_user_meta( $user_id, C360_LMS_ENROLL_DATE_KEY );
        delete_user_meta( $user_id, '_c360_enroll_days' );
        delete_user_meta( $user_id, self::META_COURSE );
        $user = get_userdata( $user_id );
        if ( $user ) $user->remove_role( 'cirrosis_student' );
        do_action( 'c360_lms_unenrolled', $user_id );
        return true;
    }

    public static function is_enrolled( $user_id = null, $course_id = 0, $bypass_admin = true ) {
        if ( null === $user_id ) $user_id = get_current_user_id();
        if ( ! $user_id ) return false;
        if ( $bypass_admin && user_can( $user_id, 'manage_options' ) ) return true;
        if ( ! get_user_meta( $user_id, C360_LMS_ENROLL_KEY, true ) ) return false;
        if ( self::is_expired( $user_id ) ) return false;
        if ( $course_id ) {
            $enrolled_course = (int) get_user_meta( $user_id, self::META_COURSE, true );
            if ( $enrolled_course && $enrolled_course !== (int) $course_id ) return false;
        }
        return true;
    }

    public static function enrolled_course_id( $user_id ) {
        return (int) get_user_meta( $user_id, self::META_COURSE, true );
    }

    public static function is_expired( $user_id ) {
        $date = get_user_meta( $user_id, C360_LMS_ENROLL_DATE_KEY, true );
        if ( ! $date ) return true;
        $started = strtotime( $date );
        if ( ! $started ) return true;
        $days = (int) get_user_meta( $user_id, '_c360_enroll_days', true ) ?: C360_LMS_EXPIRY_DAYS;
        return ( time() - $started ) > ( $days * DAY_IN_SECONDS );
    }

    public static function expiry_date( $user_id ) {
        $date = get_user_meta( $user_id, C360_LMS_ENROLL_DATE_KEY, true );
        if ( ! $date ) return '';
        $started = strtotime( $date );
        if ( ! $started ) return '';
        $days = (int) get_user_meta( $user_id, '_c360_enroll_days', true ) ?: C360_LMS_EXPIRY_DAYS;
        return date_i18n( get_option( 'date_format' ), $started + ( $days * DAY_IN_SECONDS ) );
    }

    public static function days_remaining( $user_id ) {
        $date = get_user_meta( $user_id, C360_LMS_ENROLL_DATE_KEY, true );
        if ( ! $date ) return 0;
        $started = strtotime( $date );
        if ( ! $started ) return 0;
        $days = (int) get_user_meta( $user_id, '_c360_enroll_days', true ) ?: C360_LMS_EXPIRY_DAYS;
        $remaining = ( $started + ( $days * DAY_IN_SECONDS ) ) - time();
        return max( 0, intval( $remaining / DAY_IN_SECONDS ) );
    }

    public static function mark_complete( $user_id, $lesson_slug ) {
        $user_id = absint( $user_id );
        $slug = sanitize_key( $lesson_slug );
        if ( ! $user_id || ! $slug ) return;
        $progress = self::progress( $user_id );
        $progress[ $slug ] = current_time( 'mysql' );
        update_user_meta( $user_id, C360_LMS_PROGRESS_KEY, $progress );
    }

    public static function is_complete( $user_id, $lesson_slug ) {
        $progress = self::progress( $user_id );
        return isset( $progress[ sanitize_key( $lesson_slug ) ] );
    }

    public static function progress( $user_id ) {
        $p = get_user_meta( $user_id, C360_LMS_PROGRESS_KEY, true );
        return is_array( $p ) ? $p : array();
    }

    public static function progress_percent( $user_id, $course_id = 0 ) {
        if ( ! $course_id ) $course_id = self::enrolled_course_id( $user_id );
        $lessons = $course_id ? C360_LMS_Config::lessons( $course_id ) : C360_LMS_Config::lessons();
        $total = count( $lessons );
        if ( ! $total ) return 0;
        $progress = self::progress( $user_id );
        $done = 0;
        foreach ( $lessons as $slug => $_l ) {
            if ( isset( $progress[ $slug ] ) ) $done++;
        }
        return min( 100, intval( ( $done / $total ) * 100 ) );
    }

    public static function save_quiz_score( $user_id, $slug, $score, $passed ) {
        update_user_meta( $user_id, C360_LMS_QUIZ_KEY_PREFIX . sanitize_key( $slug ), array(
            'score'  => intval( $score ),
            'passed' => (bool) $passed,
            'date'   => current_time( 'mysql' ),
        ) );
    }

    public static function quiz_result( $user_id, $slug ) {
        return get_user_meta( $user_id, C360_LMS_QUIZ_KEY_PREFIX . sanitize_key( $slug ), true );
    }
}
