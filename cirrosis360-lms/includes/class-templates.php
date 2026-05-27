<?php
/**
 * Template loader — wraps theme header/footer around plugin views.
 * Theme override path: theme/cirrosis360-lms/{view}.php → falls back to plugin templates/.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Templates {

    public static function init() {
        add_filter( 'document_title_parts', array( __CLASS__, 'title' ) );
        add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
    }

    public static function title( $parts ) {
        if ( ! C360_LMS_Router::is_lms_request() ) return $parts;
        $view = C360_LMS_Router::current_view();
        $map = array(
            'courses'   => 'Programa Cirrosis 360',
            'dashboard' => 'Mi Dashboard',
            'lesson'    => 'Lección',
            'quiz'      => 'Quiz',
        );
        $parts['title'] = isset( $map[ $view ] ) ? $map[ $view ] : 'Cirrosis 360';
        return $parts;
    }

    public static function body_class( $classes ) {
        if ( ! C360_LMS_Router::is_lms_request() ) return $classes;
        $classes[] = 'c360-lms';
        $classes[] = 'c360-lms-' . C360_LMS_Router::current_view();
        return $classes;
    }

    public static function render( $view, $vars = array() ) {
        $theme_path = locate_template( "cirrosis360-lms/{$view}.php" );
        $plugin_path = C360_LMS_DIR . "templates/{$view}.php";
        $template = $theme_path ? $theme_path : $plugin_path;
        if ( ! file_exists( $template ) ) return;

        get_header();
        echo '<main class="c360-main"><div class="container">';
        extract( $vars, EXTR_SKIP );
        include $template;
        echo '</div></main>';
        get_footer();
    }
}
