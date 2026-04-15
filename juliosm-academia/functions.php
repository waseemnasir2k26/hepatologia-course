<?php
/**
 * JulioSM Academia — Theme Functions
 * Course portal with Tutor LMS support for Dr. Julio Santiago Marcelo
 *
 * @package JulioSM_Academia
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'JSMA_VERSION', '1.0.0' );
define( 'JSMA_DIR', get_template_directory() );
define( 'JSMA_URI', get_template_directory_uri() );

/* ─── Theme Setup ─── */
function jsma_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 280,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    register_nav_menus( array(
        'primary' => __( 'Menú Principal', 'juliosm-academia' ),
        'footer'  => __( 'Menú Pie de Página', 'juliosm-academia' ),
    ) );

    load_theme_textdomain( 'juliosm-academia', JSMA_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'jsma_setup' );

/* ─── Enqueue Styles & Scripts ─── */
function jsma_scripts() {
    wp_enqueue_style( 'jsma-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800&display=swap',
        array(), null
    );
    wp_enqueue_style( 'jsma-style', get_stylesheet_uri(), array(), JSMA_VERSION );
    wp_enqueue_script( 'jsma-main-js', JSMA_URI . '/assets/js/main.js', array(), JSMA_VERSION, true );

    wp_localize_script( 'jsma-main-js', 'jsmaData', array(
        'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'jsma_nonce' ),
        'isLoggedIn' => is_user_logged_in(),
    ) );
}
add_action( 'wp_enqueue_scripts', 'jsma_scripts' );

/* ─── Widget Areas ─── */
function jsma_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Barra Lateral del Curso', 'juliosm-academia' ),
        'id'            => 'course-sidebar',
        'before_widget' => '<div class="widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
    register_sidebar( array(
        'name'          => __( 'Pie de Página', 'juliosm-academia' ),
        'id'            => 'footer-widget',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'jsma_widgets_init' );

/* ─── Tutor LMS Support ─── */
function jsma_is_tutor_active() {
    return defined( 'TUTOR_VERSION' ) || class_exists( '\TUTOR\Tutor' );
}

function jsma_tutor_support() {
    if ( jsma_is_tutor_active() ) {
        add_theme_support( 'tutor' );
    }
}
add_action( 'after_setup_theme', 'jsma_tutor_support', 20 );

/* Hide admin bar for students/subscribers */
function jsma_hide_admin_bar() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        show_admin_bar( false );
    }
}
add_action( 'after_setup_theme', 'jsma_hide_admin_bar' );

/* Add body classes for Tutor pages */
function jsma_body_classes( $classes ) {
    if ( jsma_is_tutor_active() ) {
        if ( is_singular( 'courses' ) ) $classes[] = 'tutor-course-page';
        if ( is_singular( 'lesson' ) ) $classes[] = 'tutor-lesson-page';
    }
    if ( is_user_logged_in() ) $classes[] = 'user-logged-in';
    return $classes;
}
add_filter( 'body_class', 'jsma_body_classes' );

/* ─── Customizer Settings ─── */
function jsma_customizer( $wp_customize ) {

    // Section: Course Settings
    $wp_customize->add_section( 'jsma_course', array(
        'title'    => __( 'Configuración del Curso', 'juliosm-academia' ),
        'priority' => 30,
    ) );

    // Mercado Pago Link
    $wp_customize->add_setting( 'jsma_mercadopago', array(
        'default'           => 'https://mpago.la/1sXKiM3',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'jsma_mercadopago', array(
        'label'   => __( 'Enlace Mercado Pago', 'juliosm-academia' ),
        'section' => 'jsma_course',
        'type'    => 'url',
    ) );

    // Main Site URL
    $wp_customize->add_setting( 'jsma_main_site', array(
        'default'           => 'https://juliosantiagomarcelo.com',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'jsma_main_site', array(
        'label'   => __( 'URL del Sitio Principal', 'juliosm-academia' ),
        'section' => 'jsma_course',
        'type'    => 'url',
    ) );

    // Vimeo IDs for each module
    $vimeos = array(
        'mod1' => array( 'Vimeo ID — Módulo 1', '1180151832' ),
        'mod2' => array( 'Vimeo ID — Módulo 2', '1147358050' ),
        'mod3' => array( 'Vimeo ID — Módulo 3', '1163996678' ),
        'bon1' => array( 'Vimeo ID — Bono 1', '1181683995' ),
        'bon2' => array( 'Vimeo ID — Bono 2', '1181686811' ),
        'bon3' => array( 'Vimeo ID — Bono 3', '1181691449' ),
        'test' => array( 'Vimeo ID — Testimonio', '1182155574' ),
    );

    foreach ( $vimeos as $key => $data ) {
        $wp_customize->add_setting( "jsma_vimeo_{$key}", array(
            'default'           => $data[1],
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "jsma_vimeo_{$key}", array(
            'label'   => $data[0],
            'section' => 'jsma_course',
            'type'    => 'text',
        ) );
    }

    // Section: Contact
    $wp_customize->add_section( 'jsma_contact', array(
        'title'    => __( 'Contacto', 'juliosm-academia' ),
        'priority' => 35,
    ) );

    $wp_customize->add_setting( 'jsma_email', array(
        'default'           => 'contacto@juliosantiagomarcelo.com',
        'sanitize_callback' => 'sanitize_email',
    ) );
    $wp_customize->add_control( 'jsma_email', array(
        'label'   => __( 'Email de Soporte', 'juliosm-academia' ),
        'section' => 'jsma_contact',
        'type'    => 'email',
    ) );

    $wp_customize->add_setting( 'jsma_whatsapp', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'jsma_whatsapp', array(
        'label'   => __( 'Enlace WhatsApp', 'juliosm-academia' ),
        'section' => 'jsma_contact',
        'type'    => 'url',
    ) );

    // Doctor Info
    $wp_customize->add_section( 'jsma_doctor', array(
        'title'    => __( 'Información del Doctor', 'juliosm-academia' ),
        'priority' => 40,
    ) );

    $wp_customize->add_setting( 'jsma_doctor_photo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'jsma_doctor_photo', array(
        'label'   => __( 'Foto del Doctor', 'juliosm-academia' ),
        'section' => 'jsma_doctor',
    ) ) );

    $wp_customize->add_setting( 'jsma_doctor_bio', array(
        'default'           => 'El Dr. Julio Santiago Marcelo es un gastroenterólogo con amplia experiencia en el diagnóstico y tratamiento de enfermedades del sistema digestivo y del hígado.',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'jsma_doctor_bio', array(
        'label'   => __( 'Biografía del Doctor', 'juliosm-academia' ),
        'section' => 'jsma_doctor',
        'type'    => 'textarea',
    ) );
}
add_action( 'customize_register', 'jsma_customizer' );

/* ─── Shortcodes ─── */

// [mercadopago_btn text="Inscribirme"]
function jsma_mercadopago_btn( $atts ) {
    $atts = shortcode_atts( array(
        'text'  => 'Inscribirme con Mercado Pago',
        'class' => 'btn btn-gold btn-xl',
    ), $atts );
    $url = esc_url( get_theme_mod( 'jsma_mercadopago', 'https://mpago.la/1sXKiM3' ) );
    return sprintf(
        '<a href="%s" class="%s" target="_blank" rel="noopener"><span>%s</span></a>',
        $url, esc_attr( $atts['class'] ), esc_html( $atts['text'] )
    );
}
add_shortcode( 'mercadopago_btn', 'jsma_mercadopago_btn' );

// [vimeo_embed id="1180151832"]
function jsma_vimeo_embed( $atts ) {
    $atts = shortcode_atts( array( 'id' => '' ), $atts );
    if ( empty( $atts['id'] ) ) return '';
    return sprintf(
        '<div class="video-container"><iframe src="https://player.vimeo.com/video/%s?badge=0&amp;autopause=0&amp;dnt=1" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="Video del curso"></iframe></div>',
        esc_attr( $atts['id'] )
    );
}
add_shortcode( 'vimeo_embed', 'jsma_vimeo_embed' );

// [guarantee_badge]
function jsma_guarantee_badge() {
    return '<div class="guarantee-badge"><div class="guarantee-seal"><span class="big">7</span><span class="small">Días</span></div><div class="guarantee-text"><strong>Garantía de Riesgo Cero de 7 Días</strong><span>Si en 7 días el programa no cumple sus expectativas, le devolvemos el 100% de su dinero.</span></div></div>';
}
add_shortcode( 'guarantee_badge', 'jsma_guarantee_badge' );

/* ─── Helper: Logo ─── */
function jsma_logo( $context = 'header' ) {
    if ( has_custom_logo() ) {
        the_custom_logo();
    } else {
        echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="nav-logo">
            <span class="logo-icon">&#9877;</span>
            <span class="logo-text">Dr. <span class="gold">Julio Santiago</span></span>
        </a>';
    }
}

/* ─── Custom Login Redirect ─── */
function jsma_login_redirect( $redirect_to, $request, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( in_array( 'subscriber', $user->roles ) || in_array( 'student', $user->roles ) ) {
            return home_url( '/dashboard/' );
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'jsma_login_redirect', 10, 3 );

/* ─── Custom Login Page Styling ─── */
function jsma_login_styles() {
    ?>
    <style>
        body.login {
            background: linear-gradient(160deg, #0D3B3B 0%, #155E5E 100%) !important;
            font-family: 'Inter', sans-serif;
        }
        #login h1 a {
            background-image: none !important;
            font-size: 1.5rem;
            color: #fff;
            text-indent: 0;
            width: auto;
            height: auto;
        }
        #login h1 a::after {
            content: 'Dr. Julio Santiago Marcelo';
            display: block;
            font-family: 'Playfair Display', serif;
        }
        #loginform {
            border: none !important;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        #loginform .button-primary {
            background: #D4A84D !important;
            border: none !important;
            border-radius: 10px;
            font-weight: 600;
        }
        #loginform .button-primary:hover {
            background: #C9963C !important;
        }
        .login #nav, .login #backtoblog { text-align: center; }
        .login #nav a, .login #backtoblog a { color: rgba(255,255,255,0.6) !important; }
        .login #nav a:hover, .login #backtoblog a:hover { color: #D4A84D !important; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <?php
}
add_action( 'login_enqueue_scripts', 'jsma_login_styles' );

function jsma_login_logo_url() {
    return home_url( '/' );
}
add_filter( 'login_headerurl', 'jsma_login_logo_url' );
