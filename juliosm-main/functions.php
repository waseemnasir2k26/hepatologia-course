<?php
/**
 * JulioSM Main — Theme Functions
 * Professional medical website for Dr. Julio Santiago Marcelo
 *
 * @package JulioSM_Main
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'JULIOSM_VERSION', '1.0.2' );
define( 'JULIOSM_DIR', get_template_directory() );
define( 'JULIOSM_URI', get_template_directory_uri() );

/* ─── Theme Setup ─── */
function juliosm_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 280,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    add_theme_support( 'custom-background', array(
        'default-color' => 'ffffff',
    ) );

    register_nav_menus( array(
        'primary' => __( 'Menú Principal', 'juliosm-main' ),
        'footer'  => __( 'Menú Pie de Página', 'juliosm-main' ),
    ) );

    load_theme_textdomain( 'juliosm-main', JULIOSM_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'juliosm_setup' );

/* ─── Enqueue Styles & Scripts ─── */
function juliosm_scripts() {
    // Google Fonts
    wp_enqueue_style( 'juliosm-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800&display=swap',
        array(), null
    );

    // Theme CSS
    wp_enqueue_style( 'juliosm-style', get_stylesheet_uri(), array(), JULIOSM_VERSION );

    // Theme JS
    wp_enqueue_script( 'juliosm-main-js', JULIOSM_URI . '/assets/js/main.js', array(), JULIOSM_VERSION, true );

    // Pass data to JS
    wp_localize_script( 'juliosm-main-js', 'juliosmData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'juliosm_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'juliosm_scripts' );

/* ─── Widget Areas ─── */
function juliosm_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Pie de Página', 'juliosm-main' ),
        'id'            => 'footer-widget',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'juliosm_widgets_init' );

/* ─── Customizer Settings ─── */
function juliosm_customizer( $wp_customize ) {

    // Section: Info del Doctor
    $wp_customize->add_section( 'juliosm_doctor', array(
        'title'    => __( 'Información del Doctor', 'juliosm-main' ),
        'priority' => 30,
    ) );

    // Doctor Specialty
    $wp_customize->add_setting( 'juliosm_specialty', array(
        'default'           => 'Gastroenterólogo',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'juliosm_specialty', array(
        'label'   => __( 'Especialidad', 'juliosm-main' ),
        'section' => 'juliosm_doctor',
        'type'    => 'text',
    ) );

    // Doctor Bio
    $wp_customize->add_setting( 'juliosm_bio', array(
        'default'           => 'El Dr. Julio Santiago Marcelo es un gastroenterólogo con amplia experiencia en el diagnóstico y tratamiento de enfermedades del sistema digestivo y del hígado. Con años de dedicación a sus pacientes, ha desarrollado un programa educativo único para personas que viven con cirrosis hepática.',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'juliosm_bio', array(
        'label'   => __( 'Biografía', 'juliosm-main' ),
        'section' => 'juliosm_doctor',
        'type'    => 'textarea',
    ) );

    // Doctor Bio Paragraph 2
    $wp_customize->add_setting( 'juliosm_bio_2', array(
        'default'           => 'Su compromiso con la educación del paciente lo llevó a crear este programa, donde explica conceptos médicos complejos en un lenguaje claro y accesible, para que cada familia pueda tomar mejores decisiones sobre su salud.',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'juliosm_bio_2', array(
        'label'   => __( 'Biografía (párrafo 2)', 'juliosm-main' ),
        'section' => 'juliosm_doctor',
        'type'    => 'textarea',
    ) );

    // Hospital
    $wp_customize->add_setting( 'juliosm_hospital', array(
        'default'           => 'Hospital / Clínica',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'juliosm_hospital', array(
        'label'   => __( 'Hospital o Clínica', 'juliosm-main' ),
        'section' => 'juliosm_doctor',
        'type'    => 'text',
    ) );

    // University
    $wp_customize->add_setting( 'juliosm_university', array(
        'default'           => 'Universidad',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'juliosm_university', array(
        'label'   => __( 'Universidad', 'juliosm-main' ),
        'section' => 'juliosm_doctor',
        'type'    => 'text',
    ) );

    // Years of Experience
    $wp_customize->add_setting( 'juliosm_years', array(
        'default'           => '10+',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'juliosm_years', array(
        'label'   => __( 'Años de Experiencia', 'juliosm-main' ),
        'section' => 'juliosm_doctor',
        'type'    => 'text',
    ) );

    // Doctor Photo
    $wp_customize->add_setting( 'juliosm_photo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'juliosm_photo', array(
        'label'   => __( 'Foto del Doctor', 'juliosm-main' ),
        'section' => 'juliosm_doctor',
    ) ) );

    // Section: Logos (header + footer override)
    $wp_customize->add_section( 'juliosm_logos', array(
        'title'    => __( 'Logos (Header / Footer)', 'juliosm-main' ),
        'priority' => 25,
        'description' => __( 'El logo del header se controla desde "Identidad del Sitio". El logo del footer es independiente y puede ser distinto (versión blanca recomendada sobre fondo oscuro).', 'juliosm-main' ),
    ) );

    // Header logo override (optional — bypasses Site Identity custom logo if set)
    $wp_customize->add_setting( 'juliosm_header_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'juliosm_header_logo', array(
        'label'       => __( 'Logo del Header (opcional)', 'juliosm-main' ),
        'description' => __( 'Si lo dejas vacío, se usa el logo de Identidad del Sitio o el logo color por defecto.', 'juliosm-main' ),
        'section'     => 'juliosm_logos',
    ) ) );

    $wp_customize->add_setting( 'juliosm_header_logo_height', array(
        'default'           => 72,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'juliosm_header_logo_height', array(
        'label'       => __( 'Altura del Logo Header (px)', 'juliosm-main' ),
        'description' => __( 'Predeterminado: 72. Móvil ajusta a -16. Al hacer scroll: -16.', 'juliosm-main' ),
        'section'     => 'juliosm_logos',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 32, 'max' => 160, 'step' => 2 ),
    ) );

    // Footer logo override (independent of header)
    $wp_customize->add_setting( 'juliosm_footer_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'juliosm_footer_logo', array(
        'label'       => __( 'Logo del Footer (opcional)', 'juliosm-main' ),
        'description' => __( 'Si lo dejas vacío, se usa logo-white.png. Recomendado: PNG transparente blanco.', 'juliosm-main' ),
        'section'     => 'juliosm_logos',
    ) ) );

    $wp_customize->add_setting( 'juliosm_footer_logo_height', array(
        'default'           => 64,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'juliosm_footer_logo_height', array(
        'label'       => __( 'Altura del Logo Footer (px)', 'juliosm-main' ),
        'description' => __( 'Predeterminado: 64.', 'juliosm-main' ),
        'section'     => 'juliosm_logos',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 32, 'max' => 160, 'step' => 2 ),
    ) );

    $wp_customize->add_setting( 'juliosm_footer_logo_invert', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ) );
    $wp_customize->add_control( 'juliosm_footer_logo_invert', array(
        'label'       => __( 'Forzar Logo Footer en Blanco (filter invert)', 'juliosm-main' ),
        'description' => __( 'Activa solo si tu logo del footer es de color y necesitas convertirlo a blanco automáticamente. Si subiste un PNG ya blanco, déjalo en 0.', 'juliosm-main' ),
        'section'     => 'juliosm_logos',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 0, 'max' => 1, 'step' => 1 ),
    ) );

    // Section: Contact Info
    $wp_customize->add_section( 'juliosm_contact', array(
        'title'    => __( 'Información de Contacto', 'juliosm-main' ),
        'priority' => 35,
    ) );

    $contact_fields = array(
        'email'     => array( 'Email', 'contacto@juliosantiagomarcelo.com' ),
        'phone'     => array( 'Teléfono / WhatsApp', '' ),
        'address'   => array( 'Dirección / Ciudad', 'Lima, Perú' ),
        'whatsapp'  => array( 'Enlace WhatsApp', '' ),
    );

    foreach ( $contact_fields as $key => $data ) {
        $wp_customize->add_setting( "juliosm_{$key}", array(
            'default'           => $data[1],
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( "juliosm_{$key}", array(
            'label'   => $data[0],
            'section' => 'juliosm_contact',
            'type'    => 'text',
        ) );
    }

    // Section: Course / Portal
    $wp_customize->add_section( 'juliosm_course', array(
        'title'    => __( 'Curso / Portal', 'juliosm-main' ),
        'priority' => 40,
    ) );

    $wp_customize->add_setting( 'juliosm_portal_url', array(
        'default'           => 'https://portal.juliosantiagomarcelo.com',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'juliosm_portal_url', array(
        'label'   => __( 'URL del Portal de Cursos', 'juliosm-main' ),
        'section' => 'juliosm_course',
        'type'    => 'url',
    ) );

    $wp_customize->add_setting( 'juliosm_mercadopago', array(
        'default'           => 'https://mpago.la/1sXKiM3',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'juliosm_mercadopago', array(
        'label'   => __( 'Enlace Mercado Pago', 'juliosm-main' ),
        'section' => 'juliosm_course',
        'type'    => 'url',
    ) );

    $wp_customize->add_setting( 'juliosm_testimonial_vimeo', array(
        'default'           => '1182155574',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'juliosm_testimonial_vimeo', array(
        'label'   => __( 'Vimeo ID del Testimonio', 'juliosm-main' ),
        'section' => 'juliosm_course',
        'type'    => 'text',
    ) );

    // Section: Social
    $wp_customize->add_section( 'juliosm_social', array(
        'title'    => __( 'Redes Sociales', 'juliosm-main' ),
        'priority' => 45,
    ) );

    $social = array( 'facebook', 'instagram', 'youtube', 'tiktok', 'linkedin' );
    foreach ( $social as $network ) {
        $wp_customize->add_setting( "juliosm_social_{$network}", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );
        $wp_customize->add_control( "juliosm_social_{$network}", array(
            'label'   => ucfirst( $network ),
            'section' => 'juliosm_social',
            'type'    => 'url',
        ) );
    }
}
add_action( 'customize_register', 'juliosm_customizer' );

/* ─── Contact Form AJAX Handler ─── */
function juliosm_contact_form() {
    check_ajax_referer( 'juliosm_nonce', 'nonce' );

    $name    = sanitize_text_field( $_POST['name'] ?? '' );
    $email   = sanitize_email( $_POST['email'] ?? '' );
    $phone   = sanitize_text_field( $_POST['phone'] ?? '' );
    $message = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
        wp_send_json_error( array( 'message' => 'Complete todos los campos requeridos.' ) );
    }

    $to      = get_theme_mod( 'juliosm_email', get_option( 'admin_email' ) );
    $subject = sprintf( 'Nuevo mensaje de %s — juliosantiagomarcelo.com', $name );
    $body    = "Nombre: {$name}\nEmail: {$email}\nTeléfono: {$phone}\n\nMensaje:\n{$message}";
    $headers = array( "From: {$name} <{$email}>", 'Content-Type: text/plain; charset=UTF-8' );

    $sent = wp_mail( $to, $subject, $body, $headers );

    if ( $sent ) {
        wp_send_json_success( array( 'message' => '¡Mensaje enviado! Le responderemos pronto.' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Error al enviar. Intente por WhatsApp.' ) );
    }
}
add_action( 'wp_ajax_juliosm_contact', 'juliosm_contact_form' );
add_action( 'wp_ajax_nopriv_juliosm_contact', 'juliosm_contact_form' );

/* ─── Helper: Get Logo HTML ─── */
function juliosm_logo( $context = 'header' ) {
    $is_footer = ( $context === 'footer' );

    if ( $is_footer ) {
        $override = get_theme_mod( 'juliosm_footer_logo', '' );
        $url      = $override ? $override : get_template_directory_uri() . '/assets/img/logo-white.png';
    } else {
        $override = get_theme_mod( 'juliosm_header_logo', '' );
        if ( $override ) {
            $url = $override;
        } elseif ( has_custom_logo() ) {
            // WP Site Identity custom logo (header only)
            the_custom_logo();
            return;
        } else {
            $url = get_template_directory_uri() . '/assets/img/logo-color.png';
        }
    }

    $cls = $is_footer ? 'nav-logo footer-logo-link' : 'nav-logo header-logo-link';
    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="' . esc_attr( $cls ) . '">
        <img src="' . esc_url( $url ) . '" alt="Logo Dr. Julio Santiago Marcelo" class="site-logo-img">
    </a>';
}

/* ─── Inline CSS: logo heights + footer invert (Customizer-driven) ─── */
function juliosm_logo_inline_css() {
    $h_h     = absint( get_theme_mod( 'juliosm_header_logo_height', 72 ) );
    $h_h_sc  = max( 32, $h_h - 16 );
    $h_h_mob = max( 32, $h_h - 16 );
    $h_f     = absint( get_theme_mod( 'juliosm_footer_logo_height', 64 ) );
    $invert  = absint( get_theme_mod( 'juliosm_footer_logo_invert', 0 ) ) ? 'brightness(0) invert(1)' : 'none';

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
    wp_add_inline_style( 'juliosm-style', $css );
}
add_action( 'wp_enqueue_scripts', 'juliosm_logo_inline_css', 20 );

/* ─── Security Headers ─── */
function juliosm_security_headers() {
    if ( is_admin() ) return;
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(self)' );
    if ( is_ssl() ) {
        header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
    }
}
add_action( 'send_headers', 'juliosm_security_headers' );

/* ─── Harden: disable xmlrpc ─── */
add_filter( 'xmlrpc_enabled', '__return_false' );
