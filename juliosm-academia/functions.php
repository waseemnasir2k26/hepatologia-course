<?php
/**
 * JulioSM Academia — Theme Functions
 * Course portal with Tutor LMS support for Dr. Julio Santiago Marcelo
 *
 * @package JulioSM_Academia
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'JSMA_VERSION', '1.2.2' );
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

function jsma_is_c360_lms_active() {
    return defined( 'C360_LMS_VERSION' );
}

/**
 * Resolve effective site mode.
 *  - 'landing' = sales page (cirrosis360 subdomain). Indexable, CTA → MercadoPago, "Mi Curso" → portal subdomain.
 *  - 'portal'  = student LMS (portal subdomain). Noindex, CTA → local dashboard.
 * Auto mode infers by Tutor LMS plugin presence.
 */
function jsma_site_mode() {
    $mode = get_theme_mod( 'jsma_site_mode', 'auto' );
    if ( $mode !== 'auto' ) {
        return $mode;
    }
    // Hostname-based detection (more reliable than plugin presence).
    $host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
    if ( $host && ( strpos( $host, 'portal.' ) === 0 || strpos( $host, 'portal-' ) === 0 ) ) {
        return 'portal';
    }
    if ( $host && strpos( $host, 'cirrosis360.' ) === 0 ) {
        return 'landing';
    }
    // Final fallback: plugin presence (c360-lms or Tutor LMS).
    return ( jsma_is_c360_lms_active() || jsma_is_tutor_active() ) ? 'portal' : 'landing';
}

function jsma_is_landing() { return jsma_site_mode() === 'landing'; }
function jsma_is_portal()  { return jsma_site_mode() === 'portal'; }

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

/* Add body classes for Tutor pages + site mode */
function jsma_body_classes( $classes ) {
    if ( jsma_is_tutor_active() ) {
        if ( is_singular( 'courses' ) ) $classes[] = 'tutor-course-page';
        if ( is_singular( 'lesson' ) ) $classes[] = 'tutor-lesson-page';
    }
    if ( is_user_logged_in() ) $classes[] = 'user-logged-in';
    $classes[] = jsma_is_portal() ? 'site-mode-portal' : 'site-mode-landing';
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

    // Portal URL (cross-domain — landing site links to portal subdomain dashboard)
    $wp_customize->add_setting( 'jsma_portal_url', array(
        'default'           => 'https://portal.juliosantiagomarcelo.com/dashboard/',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'jsma_portal_url', array(
        'label'       => __( 'URL del Portal (Dashboard de Alumnos)', 'juliosm-academia' ),
        'description' => __( 'En el subdominio de ventas, el botón "Mi Curso" enlaza aquí.', 'juliosm-academia' ),
        'section'     => 'jsma_course',
        'type'        => 'url',
    ) );

    // Site Mode (landing | portal) — controls noindex + WhatsApp + CTA behavior
    $wp_customize->add_setting( 'jsma_site_mode', array(
        'default'           => 'auto',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'jsma_site_mode', array(
        'label'       => __( 'Modo del Sitio', 'juliosm-academia' ),
        'description' => __( 'auto = detecta por plugin Tutor LMS. landing = página de ventas (cirrosis360). portal = portal de alumnos.', 'juliosm-academia' ),
        'section'     => 'jsma_course',
        'type'        => 'select',
        'choices'     => array(
            'auto'    => 'Auto (detectar)',
            'landing' => 'Landing (ventas)',
            'portal'  => 'Portal (alumnos)',
        ),
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

    // Years of Experience
    $wp_customize->add_setting( 'jsma_years', array(
        'default'           => '10+',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'jsma_years', array(
        'label'   => __( 'Años de Experiencia', 'juliosm-academia' ),
        'section' => 'jsma_doctor',
        'type'    => 'text',
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

// [pdf_download url="..." title="Guía Nutrición"]
function jsma_pdf_download( $atts ) {
    $atts = shortcode_atts( array(
        'url'   => '',
        'title' => 'Descargar PDF',
    ), $atts );
    if ( empty( $atts['url'] ) ) return '';
    return sprintf(
        '<a class="pdf-big-btn" href="%s" target="_blank" rel="noopener"><span>%s</span></a>',
        esc_url( $atts['url'] ),
        esc_html( $atts['title'] )
    );
}
add_shortcode( 'pdf_download', 'jsma_pdf_download' );

// [guarantee_badge]
function jsma_guarantee_badge() {
    return '<div class="guarantee-badge"><div class="guarantee-seal"><span class="big">7</span><span class="small">Días</span></div><div class="guarantee-text"><strong>Garantía de Riesgo Cero de 7 Días</strong><span>Si en 7 días el programa no cumple sus expectativas, le devolvemos el 100% de su dinero.</span></div></div>';
}
add_shortcode( 'guarantee_badge', 'jsma_guarantee_badge' );

/* ─── Helper: Logo ─── */
function jsma_logo( $context = 'header' ) {
    $is_footer    = ( $context === 'footer' );
    $portal_mode  = jsma_is_portal();
    $base         = get_template_directory_uri() . '/assets/img/';

    if ( $is_footer ) {
        $override = get_theme_mod( 'jsma_footer_logo', '' );
        $url      = $override ? $override : $base . 'logo-white.png';
    } else {
        $override = get_theme_mod( 'jsma_header_logo', '' );
        if ( $override ) {
            $url = $override;
        } elseif ( $portal_mode ) {
            // Portal forces white logo regardless of WP Site Identity (visual distinction).
            $url = $base . 'logo-white.png';
        } elseif ( has_custom_logo() ) {
            // Landing only: honor WP Site Identity custom logo.
            the_custom_logo();
            return;
        } else {
            $url = $base . 'logo-color.png';
        }
    }

    $cls = $is_footer ? 'nav-logo footer-logo-link' : 'nav-logo header-logo-link';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="' . esc_attr( $cls ) . '">
        <img src="' . esc_url( $url ) . '" alt="Logo Dr. Julio Santiago Marcelo" class="site-logo-img">
    </a>';
}

/* ─── Customizer: Logos (Header / Footer) — independent overrides + heights ─── */
function jsma_logos_customizer( $wp_customize ) {
    $wp_customize->add_section( 'jsma_logos', array(
        'title'       => __( 'Logos (Header / Footer)', 'juliosm-academia' ),
        'priority'    => 26,
        'description' => __( 'Logos independientes header/footer. En modo Portal, el logo morado/color NO aparece (se fuerza blanco para distinguir del landing).', 'juliosm-academia' ),
    ) );

    $wp_customize->add_setting( 'jsma_header_logo', array(
        'default' => '', 'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'jsma_header_logo', array(
        'label'       => __( 'Logo del Header (opcional)', 'juliosm-academia' ),
        'description' => __( 'Vacío = automático según modo (color en landing, blanco en portal).', 'juliosm-academia' ),
        'section'     => 'jsma_logos',
    ) ) );

    $wp_customize->add_setting( 'jsma_header_logo_height', array(
        'default' => 72, 'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'jsma_header_logo_height', array(
        'label' => __( 'Altura del Logo Header (px)', 'juliosm-academia' ),
        'section' => 'jsma_logos', 'type' => 'number',
        'input_attrs' => array( 'min' => 32, 'max' => 160, 'step' => 2 ),
    ) );

    $wp_customize->add_setting( 'jsma_footer_logo', array(
        'default' => '', 'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'jsma_footer_logo', array(
        'label'       => __( 'Logo del Footer (opcional)', 'juliosm-academia' ),
        'description' => __( 'Vacío = logo-white.png. Recomendado: PNG transparente blanco.', 'juliosm-academia' ),
        'section'     => 'jsma_logos',
    ) ) );

    $wp_customize->add_setting( 'jsma_footer_logo_height', array(
        'default' => 64, 'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'jsma_footer_logo_height', array(
        'label' => __( 'Altura del Logo Footer (px)', 'juliosm-academia' ),
        'section' => 'jsma_logos', 'type' => 'number',
        'input_attrs' => array( 'min' => 32, 'max' => 160, 'step' => 2 ),
    ) );

    $wp_customize->add_setting( 'jsma_footer_logo_invert', array(
        'default' => 0, 'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'jsma_footer_logo_invert', array(
        'label'       => __( 'Forzar Logo Footer en Blanco (filter invert)', 'juliosm-academia' ),
        'description' => __( 'Activa solo si tu logo footer es a color y necesitas convertirlo a blanco.', 'juliosm-academia' ),
        'section'     => 'jsma_logos', 'type' => 'number',
        'input_attrs' => array( 'min' => 0, 'max' => 1, 'step' => 1 ),
    ) );
}
add_action( 'customize_register', 'jsma_logos_customizer' );

/* ─── Inline CSS: logo heights + invert (Customizer-driven) ─── */
function jsma_logo_inline_css() {
    $h_h     = absint( get_theme_mod( 'jsma_header_logo_height', 72 ) );
    $h_h_sc  = max( 32, $h_h - 16 );
    $h_h_mob = max( 32, $h_h - 16 );
    $h_f     = absint( get_theme_mod( 'jsma_footer_logo_height', 64 ) );
    $invert  = absint( get_theme_mod( 'jsma_footer_logo_invert', 0 ) ) ? 'brightness(0) invert(1)' : 'none';

    $css = "
    .nav-logo img, .navbar .custom-logo { height: {$h_h}px !important; width: auto !important; }
    .navbar.scrolled .nav-logo img, .navbar.scrolled .custom-logo { height: {$h_h_sc}px !important; }
    @media (max-width:768px) {
        .nav-logo img, .navbar .custom-logo { height: {$h_h_mob}px !important; }
        .navbar.scrolled .nav-logo img, .navbar.scrolled .custom-logo { height: " . max( 32, $h_h_mob - 12 ) . "px !important; }
    }
    .footer-brand .nav-logo img,
    .footer-brand .footer-logo-link img,
    .footer-brand .custom-logo-link img { height: {$h_f}px !important; width: auto !important; filter: {$invert}; }
    ";
    wp_add_inline_style( 'jsma-style', $css );
}
add_action( 'wp_enqueue_scripts', 'jsma_logo_inline_css', 20 );

/* ─── Custom Login Redirect ─── */
function jsma_login_redirect( $redirect_to, $request, $user ) {
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( in_array( 'subscriber', $user->roles ) || in_array( 'student', $user->roles ) ) {
            // Landing site: bounce to portal subdomain. Portal site: local dashboard.
            return jsma_is_landing()
                ? esc_url_raw( get_theme_mod( 'jsma_portal_url', 'https://portal.juliosantiagomarcelo.com/dashboard/' ) )
                : home_url( '/dashboard/' );
        }
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'jsma_login_redirect', 10, 3 );

/* ─── Custom Login Page Styling ─── */
function jsma_login_styles() {
    $logo_url = get_template_directory_uri() . '/assets/img/logo-white.png';
    ?>
    <style>
        body.login {
            background: linear-gradient(160deg, #114050 0%, #1A6170 100%) !important;
            font-family: 'Inter', sans-serif;
        }
        #login h1 a {
            background-image: url('<?php echo esc_url( $logo_url ); ?>') !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            background-position: center !important;
            width: 220px !important;
            height: 90px !important;
            margin: 0 auto 1.5rem !important;
            text-indent: -9999px;
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

function jsma_login_logo_title() {
    return jsma_is_landing()
        ? 'Cirrosis 360 — Acceso de Alumnos'
        : 'Dr. Julio Santiago Marcelo — Portal de Alumnos';
}
add_filter( 'login_headertext', 'jsma_login_logo_title' );

/* ─── Security Headers ─── */
function jsma_security_headers() {
    if ( is_admin() ) return;
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(self)' );
    if ( is_ssl() ) {
        header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
    }
}
add_action( 'send_headers', 'jsma_security_headers' );

/* ─── Harden: disable xmlrpc (DDoS/brute-force vector) ─── */
add_filter( 'xmlrpc_enabled', '__return_false' );
