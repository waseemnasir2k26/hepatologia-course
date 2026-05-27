<?php
/**
 * Custom Post Types: c360_course + c360_lesson.
 * Course: parent. Lesson: child via meta _c360_course_id.
 * Quiz data: stored as JSON in lesson meta _c360_quiz.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_CPT {

    const CPT_COURSE = 'c360_course';
    const CPT_LESSON = 'c360_lesson';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register' ), 5 );
        add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
        add_action( 'save_post_' . self::CPT_COURSE, array( __CLASS__, 'save_course' ), 10, 2 );
        add_action( 'save_post_' . self::CPT_LESSON, array( __CLASS__, 'save_lesson' ), 10, 2 );
        add_filter( 'manage_' . self::CPT_LESSON . '_posts_columns', array( __CLASS__, 'lesson_columns' ) );
        add_action( 'manage_' . self::CPT_LESSON . '_posts_custom_column', array( __CLASS__, 'lesson_column_content' ), 10, 2 );
        add_filter( 'manage_' . self::CPT_COURSE . '_posts_columns', array( __CLASS__, 'course_columns' ) );
        add_action( 'manage_' . self::CPT_COURSE . '_posts_custom_column', array( __CLASS__, 'course_column_content' ), 10, 2 );
    }

    public static function register() {
        register_post_type( self::CPT_COURSE, array(
            'label'         => 'Cursos',
            'labels'        => array(
                'name'          => 'Cursos',
                'singular_name' => 'Curso',
                'add_new_item'  => 'Añadir nuevo Curso',
                'edit_item'     => 'Editar Curso',
                'all_items'     => 'Todos los Cursos',
            ),
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => 'c360-lms',
            'menu_position' => 27,
            'supports'      => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'   => false,
            'rewrite'       => false,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ) );

        register_post_type( self::CPT_LESSON, array(
            'label'         => 'Lecciones',
            'labels'        => array(
                'name'          => 'Lecciones',
                'singular_name' => 'Lección',
                'add_new_item'  => 'Añadir nueva Lección',
                'edit_item'     => 'Editar Lección',
                'all_items'     => 'Todas las Lecciones',
            ),
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => 'c360-lms',
            'supports'      => array( 'title', 'editor', 'page-attributes' ),
            'has_archive'   => false,
            'rewrite'       => false,
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        ) );

        // Register meta with auth + sanitize callbacks.
        $course_meta = array(
            '_c360_price'    => 'sanitize_text_field',
            '_c360_currency' => 'sanitize_text_field',
            '_c360_duration_days' => 'absint',
            '_c360_subtitle' => 'sanitize_text_field',
        );
        foreach ( $course_meta as $k => $cb ) {
            register_post_meta( self::CPT_COURSE, $k, array(
                'show_in_rest'      => false,
                'single'            => true,
                'type'              => 'string',
                'sanitize_callback' => $cb,
                'auth_callback'     => function () { return current_user_can( 'manage_options' ); },
            ) );
        }

        $lesson_meta = array(
            '_c360_course_id'  => 'absint',
            '_c360_vimeo_id'   => 'sanitize_text_field',
            '_c360_pdf_url'    => 'esc_url_raw',
            '_c360_type'       => 'sanitize_key',
            '_c360_subtitle'   => 'sanitize_text_field',
            '_c360_has_quiz'   => 'absint',
            '_c360_pass_score' => 'absint',
            '_c360_quiz'       => array( __CLASS__, 'sanitize_quiz_json' ),
        );
        foreach ( $lesson_meta as $k => $cb ) {
            register_post_meta( self::CPT_LESSON, $k, array(
                'show_in_rest'      => false,
                'single'            => true,
                'type'              => 'string',
                'sanitize_callback' => $cb,
                'auth_callback'     => function () { return current_user_can( 'manage_options' ); },
            ) );
        }
    }

    public static function sanitize_quiz_json( $value ) {
        if ( is_array( $value ) ) $value = wp_json_encode( $value );
        $decoded = json_decode( (string) $value, true );
        if ( ! is_array( $decoded ) ) return '';
        // Strict shape: array of {q, options[], correct}.
        $clean = array();
        foreach ( $decoded as $item ) {
            if ( ! is_array( $item ) ) continue;
            $q = isset( $item['q'] ) ? sanitize_text_field( $item['q'] ) : '';
            $opts = isset( $item['options'] ) && is_array( $item['options'] )
                ? array_map( 'sanitize_text_field', $item['options'] ) : array();
            $correct = isset( $item['correct'] ) ? intval( $item['correct'] ) : 0;
            if ( $q && count( $opts ) >= 2 ) {
                $clean[] = array( 'q' => $q, 'options' => array_values( $opts ), 'correct' => max( 0, min( count( $opts ) - 1, $correct ) ) );
            }
        }
        return wp_json_encode( $clean );
    }

    public static function meta_boxes() {
        add_meta_box( 'c360_course_meta', 'Detalles del Curso', array( __CLASS__, 'mb_course' ), self::CPT_COURSE, 'normal', 'high' );
        add_meta_box( 'c360_lesson_meta', 'Detalles de la Lección', array( __CLASS__, 'mb_lesson' ), self::CPT_LESSON, 'normal', 'high' );
        add_meta_box( 'c360_lesson_quiz', 'Quiz (opcional)', array( __CLASS__, 'mb_quiz' ), self::CPT_LESSON, 'normal', 'default' );
    }

    public static function mb_course( $post ) {
        wp_nonce_field( 'c360_course_save', 'c360_course_nonce' );
        $price    = get_post_meta( $post->ID, '_c360_price', true );
        $currency = get_post_meta( $post->ID, '_c360_currency', true ) ?: 'PEN';
        $days     = (int) get_post_meta( $post->ID, '_c360_duration_days', true ) ?: C360_LMS_EXPIRY_DAYS;
        $subtitle = get_post_meta( $post->ID, '_c360_subtitle', true );
        ?>
        <p><label><strong>Subtítulo</strong><br>
            <input type="text" name="c360_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="widefat"></label></p>
        <p><label><strong>Precio</strong> (texto, ej. "S/ 247")<br>
            <input type="text" name="c360_price" value="<?php echo esc_attr( $price ); ?>" class="regular-text"></label></p>
        <p><label><strong>Moneda (ISO)</strong><br>
            <input type="text" name="c360_currency" value="<?php echo esc_attr( $currency ); ?>" class="small-text" maxlength="3"></label></p>
        <p><label><strong>Duración del acceso</strong> (días)<br>
            <input type="number" name="c360_duration_days" value="<?php echo esc_attr( $days ); ?>" class="small-text" min="1" max="3650"></label></p>
        <?php
    }

    public static function mb_lesson( $post ) {
        wp_nonce_field( 'c360_lesson_save', 'c360_lesson_nonce' );
        $course_id = (int) get_post_meta( $post->ID, '_c360_course_id', true );
        $vimeo     = get_post_meta( $post->ID, '_c360_vimeo_id', true );
        $pdf       = get_post_meta( $post->ID, '_c360_pdf_url', true );
        $type      = get_post_meta( $post->ID, '_c360_type', true ) ?: 'module';
        $subtitle  = get_post_meta( $post->ID, '_c360_subtitle', true );
        $has_quiz  = (int) get_post_meta( $post->ID, '_c360_has_quiz', true );

        $courses = get_posts( array( 'post_type' => self::CPT_COURSE, 'numberposts' => -1, 'post_status' => 'publish' ) );
        ?>
        <p><label><strong>Curso</strong><br>
            <select name="c360_course_id" class="widefat">
                <option value="0">— Seleccionar —</option>
                <?php foreach ( $courses as $c ) : ?>
                    <option value="<?php echo intval( $c->ID ); ?>" <?php selected( $c->ID, $course_id ); ?>>
                        <?php echo esc_html( $c->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select></label></p>
        <p><label><strong>Tipo</strong><br>
            <select name="c360_type">
                <option value="module" <?php selected( $type, 'module' ); ?>>Módulo</option>
                <option value="bonus"  <?php selected( $type, 'bonus' ); ?>>Bono</option>
            </select></label></p>
        <p><label><strong>Subtítulo</strong><br>
            <input type="text" name="c360_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" class="widefat"></label></p>
        <p><label><strong>Vimeo ID</strong> (solo el ID, ej. <code>1180151832</code>)<br>
            <input type="text" name="c360_vimeo_id" value="<?php echo esc_attr( $vimeo ); ?>" class="regular-text"
                   pattern="\d+" inputmode="numeric"></label></p>
        <p><label><strong>URL del PDF</strong><br>
            <input type="url" name="c360_pdf_url" value="<?php echo esc_attr( $pdf ); ?>" class="widefat"
                   placeholder="https://.../wp-content/uploads/.../lesson.pdf"></label></p>
        <p><label><input type="checkbox" name="c360_has_quiz" value="1" <?php checked( $has_quiz, 1 ); ?>>
            <strong>Esta lección tiene quiz</strong></label></p>
        <p><strong>Orden</strong>: usa el campo "Atributos de Página → Orden" en la barra lateral.</p>
        <?php
    }

    public static function mb_quiz( $post ) {
        $raw = get_post_meta( $post->ID, '_c360_quiz', true );
        $data = $raw ? json_decode( $raw, true ) : array();
        if ( ! is_array( $data ) ) $data = array();
        ?>
        <p>Solo se usa si "tiene quiz" está activado. Mínimo: 2 opciones por pregunta. La opción correcta se marca por índice (0 = primera).</p>
        <p><label>Puntaje mínimo (%) para aprobar:
            <input type="number" name="c360_pass_score" min="1" max="100"
                   value="<?php echo esc_attr( get_post_meta( $post->ID, '_c360_pass_score', true ) ?: 70 ); ?>"
                   class="small-text"></label></p>
        <table class="widefat" id="c360-quiz-builder">
            <thead><tr><th style="width:35%">Pregunta</th><th>Opciones (una por línea)</th><th>Índice correcto</th><th>—</th></tr></thead>
            <tbody>
            <?php foreach ( $data as $i => $q ) : ?>
                <tr>
                    <td><textarea name="quiz[<?php echo $i; ?>][q]" rows="3" class="widefat"><?php echo esc_textarea( $q['q'] ?? '' ); ?></textarea></td>
                    <td><textarea name="quiz[<?php echo $i; ?>][options]" rows="4" class="widefat"><?php echo esc_textarea( implode( "\n", $q['options'] ?? array() ) ); ?></textarea></td>
                    <td><input type="number" name="quiz[<?php echo $i; ?>][correct]" value="<?php echo esc_attr( $q['correct'] ?? 0 ); ?>" min="0" class="small-text"></td>
                    <td><button type="button" class="button button-link-delete" onclick="this.closest('tr').remove()">Eliminar</button></td>
                </tr>
            <?php endforeach; ?>
                <tr><td><textarea name="quiz_new[q]" rows="3" class="widefat" placeholder="Pregunta nueva (vacía = ignorada)"></textarea></td>
                    <td><textarea name="quiz_new[options]" rows="4" class="widefat" placeholder="Opción 1&#10;Opción 2&#10;Opción 3"></textarea></td>
                    <td><input type="number" name="quiz_new[correct]" value="0" min="0" class="small-text"></td>
                    <td>—</td></tr>
            </tbody>
        </table>
        <?php
    }

    public static function save_course( $post_id, $post ) {
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
        if ( ! isset( $_POST['c360_course_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['c360_course_nonce'] ), 'c360_course_save' ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        update_post_meta( $post_id, '_c360_subtitle', sanitize_text_field( wp_unslash( $_POST['c360_subtitle'] ?? '' ) ) );
        update_post_meta( $post_id, '_c360_price', sanitize_text_field( wp_unslash( $_POST['c360_price'] ?? '' ) ) );
        update_post_meta( $post_id, '_c360_currency', sanitize_text_field( wp_unslash( $_POST['c360_currency'] ?? 'PEN' ) ) );
        update_post_meta( $post_id, '_c360_duration_days', max( 1, absint( $_POST['c360_duration_days'] ?? C360_LMS_EXPIRY_DAYS ) ) );
    }

    public static function save_lesson( $post_id, $post ) {
        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) return;
        if ( ! isset( $_POST['c360_lesson_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['c360_lesson_nonce'] ), 'c360_lesson_save' ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $course_id = absint( $_POST['c360_course_id'] ?? 0 );
        update_post_meta( $post_id, '_c360_course_id', $course_id );
        update_post_meta( $post_id, '_c360_type', sanitize_key( wp_unslash( $_POST['c360_type'] ?? 'module' ) ) );
        update_post_meta( $post_id, '_c360_subtitle', sanitize_text_field( wp_unslash( $_POST['c360_subtitle'] ?? '' ) ) );
        $vimeo = preg_replace( '/\D+/', '', (string) wp_unslash( $_POST['c360_vimeo_id'] ?? '' ) );
        update_post_meta( $post_id, '_c360_vimeo_id', $vimeo );
        update_post_meta( $post_id, '_c360_pdf_url', esc_url_raw( wp_unslash( $_POST['c360_pdf_url'] ?? '' ) ) );
        update_post_meta( $post_id, '_c360_has_quiz', ! empty( $_POST['c360_has_quiz'] ) ? 1 : 0 );

        // If lesson has no course assigned, force draft + flag.
        if ( ! $course_id && $post->post_status === 'publish' ) {
            remove_action( 'save_post_' . self::CPT_LESSON, array( __CLASS__, 'save_lesson' ), 10 );
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
            add_action( 'save_post_' . self::CPT_LESSON, array( __CLASS__, 'save_lesson' ), 10, 2 );
            set_transient( 'c360_lesson_no_course_' . get_current_user_id(), $post_id, 30 );
        }

        $pass = max( 1, min( 100, absint( $_POST['c360_pass_score'] ?? 70 ) ) );
        update_post_meta( $post_id, '_c360_pass_score', $pass );

        $quiz_in = isset( $_POST['quiz'] ) && is_array( $_POST['quiz'] ) ? wp_unslash( $_POST['quiz'] ) : array();
        if ( ! empty( $_POST['quiz_new'] ) && is_array( $_POST['quiz_new'] ) && ! empty( $_POST['quiz_new']['q'] ) ) {
            $quiz_in[] = wp_unslash( $_POST['quiz_new'] );
        }

        $quiz_clean = array();
        foreach ( $quiz_in as $q ) {
            if ( ! is_array( $q ) ) continue;
            $text = isset( $q['q'] ) ? sanitize_text_field( $q['q'] ) : '';
            if ( ! $text ) continue;
            $opts_raw = isset( $q['options'] ) ? (string) $q['options'] : '';
            $opts = array_values( array_filter( array_map( 'trim', preg_split( "/\r\n|\n|\r/", $opts_raw ) ), 'strlen' ) );
            $opts = array_map( 'sanitize_text_field', $opts );
            if ( count( $opts ) < 2 ) continue;
            $correct = isset( $q['correct'] ) ? intval( $q['correct'] ) : 0;
            $correct = max( 0, min( count( $opts ) - 1, $correct ) );
            $quiz_clean[] = array( 'q' => $text, 'options' => $opts, 'correct' => $correct );
        }
        update_post_meta( $post_id, '_c360_quiz', wp_json_encode( $quiz_clean ) );
    }

    public static function lesson_columns( $cols ) {
        $new = array(
            'cb'        => $cols['cb'] ?? '',
            'title'     => 'Título',
            'course'    => 'Curso',
            'type'      => 'Tipo',
            'vimeo'     => 'Vimeo',
            'has_quiz'  => 'Quiz',
            'order'     => 'Orden',
            'date'      => $cols['date'] ?? '',
        );
        return $new;
    }

    public static function lesson_column_content( $col, $post_id ) {
        switch ( $col ) {
            case 'course':
                $cid = (int) get_post_meta( $post_id, '_c360_course_id', true );
                if ( $cid ) {
                    $c = get_post( $cid );
                    if ( $c ) echo '<a href="' . esc_url( get_edit_post_link( $cid ) ) . '">' . esc_html( $c->post_title ) . '</a>';
                } else echo '<em>—</em>';
                break;
            case 'type':
                $t = get_post_meta( $post_id, '_c360_type', true );
                echo esc_html( $t === 'bonus' ? 'Bono' : 'Módulo' );
                break;
            case 'vimeo':
                $v = get_post_meta( $post_id, '_c360_vimeo_id', true );
                echo $v ? '<code>' . esc_html( $v ) . '</code>' : '—';
                break;
            case 'has_quiz':
                echo (int) get_post_meta( $post_id, '_c360_has_quiz', true ) ? '✓' : '—';
                break;
            case 'order':
                echo (int) get_post_field( 'menu_order', $post_id );
                break;
        }
    }

    public static function course_columns( $cols ) {
        $new = array(
            'cb'      => $cols['cb'] ?? '',
            'title'   => 'Curso',
            'price'   => 'Precio',
            'lessons' => 'Lecciones',
            'enrollments' => 'Inscritos',
            'date'    => $cols['date'] ?? '',
        );
        return $new;
    }

    public static function course_column_content( $col, $post_id ) {
        switch ( $col ) {
            case 'price':
                echo esc_html( get_post_meta( $post_id, '_c360_price', true ) );
                break;
            case 'lessons':
                $count = (int) ( new WP_Query( array(
                    'post_type'      => self::CPT_LESSON,
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'meta_query'     => array( array( 'key' => '_c360_course_id', 'value' => $post_id ) ),
                    'no_found_rows'  => true,
                ) ) )->post_count;
                echo $count;
                break;
            case 'enrollments':
                $users = get_users( array(
                    'meta_query' => array(
                        'relation' => 'AND',
                        array( 'key' => C360_LMS_ENROLL_KEY, 'value' => 1 ),
                        array( 'key' => C360_LMS_Enrollment::META_COURSE, 'value' => $post_id, 'compare' => '=' ),
                    ),
                    'fields' => 'ID',
                ) );
                echo intval( count( $users ) );
                break;
        }
    }
}
