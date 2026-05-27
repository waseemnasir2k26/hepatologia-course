<?php
/**
 * Admin UI — top-level menu Cirrosis 360 LMS.
 *  - Inscripciones (active + manual enroll)
 *  - Pendientes (approval queue)
 *  - Cursos (CPT — handled via show_in_menu)
 *  - Lecciones (CPT)
 *  - MercadoPago (handled in class-mercadopago.php)
 *  - Ajustes (toggle approval, etc.)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class C360_LMS_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
        add_action( 'admin_post_c360_enroll',     array( __CLASS__, 'handle_enroll' ) );
        add_action( 'admin_post_c360_unenroll',   array( __CLASS__, 'handle_unenroll' ) );
        add_action( 'admin_post_c360_extend',     array( __CLASS__, 'handle_extend' ) );
        add_action( 'admin_post_c360_save_settings', array( __CLASS__, 'handle_save_settings' ) );
        add_action( 'admin_post_c360_export_csv',    array( __CLASS__, 'handle_export_csv' ) );
        add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
        add_action( 'admin_notices', array( __CLASS__, 'activation_notice' ) );
        add_action( 'admin_notices', array( __CLASS__, 'lesson_orphan_notice' ) );
    }

    public static function activation_notice() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( ! get_transient( 'c360_lms_activation_notice' ) ) return;
        delete_transient( 'c360_lms_activation_notice' );
        $courses = admin_url( 'edit.php?post_type=' . C360_LMS_CPT::CPT_COURSE );
        $settings = admin_url( 'admin.php?page=c360-lms-settings' );
        $mp = admin_url( 'admin.php?page=c360-lms-mp' );
        ?>
        <div class="notice notice-info is-dismissible">
            <h3 style="margin-top:.5em">¡Cirrosis 360 LMS activado!</h3>
            <p>Próximos pasos recomendados:</p>
            <ol>
                <li><strong><a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>">Settings → Permalinks → Save</a></strong> (necesario una vez para activar las rutas /courses/, /dashboard/, etc).</li>
                <li><a href="<?php echo esc_url( $courses ); ?>">Revisar el curso de muestra "Cirrosis 360"</a> (creado automáticamente).</li>
                <li><a href="<?php echo esc_url( $settings ); ?>">Configurar ajustes</a> (aprobación obligatoria, etc).</li>
                <li><a href="<?php echo esc_url( $mp ); ?>">Configurar webhook de MercadoPago</a> (token + secret).</li>
            </ol>
        </div>
        <?php
    }

    public static function lesson_orphan_notice() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $key = 'c360_lesson_no_course_' . get_current_user_id();
        $post_id = (int) get_transient( $key );
        if ( ! $post_id ) return;
        delete_transient( $key );
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>Lección sin curso:</strong> guardamos la lección como borrador porque no asignaste un Curso. Edítala y selecciona un curso para publicarla.</p>
        </div>
        <?php
    }

    public static function menu() {
        add_menu_page(
            'Cirrosis 360 LMS', 'Cirrosis 360', 'manage_options',
            'c360-lms', array( __CLASS__, 'page_enrollments' ),
            'dashicons-welcome-learn-more', 26
        );
        add_submenu_page( 'c360-lms', 'Inscripciones', 'Inscripciones', 'manage_options', 'c360-lms', array( __CLASS__, 'page_enrollments' ) );
        add_submenu_page( 'c360-lms', 'Pendientes de Aprobación', 'Pendientes', 'manage_options', 'c360-lms-pending', array( __CLASS__, 'page_pending' ) );
        add_submenu_page( 'c360-lms', 'Reportes & Quizzes', 'Reportes', 'manage_options', 'c360-lms-reports', array( __CLASS__, 'page_reports' ) );
        add_submenu_page( 'c360-lms', 'Ajustes', 'Ajustes', 'manage_options', 'c360-lms-settings', array( __CLASS__, 'page_settings' ) );
    }

    public static function page_reports() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $user_filter = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
        $quiz_filter = isset( $_GET['quiz_slug'] ) ? sanitize_key( $_GET['quiz_slug'] ) : '';
        $attempts = $user_filter
            ? C360_LMS_Storage::get_user_attempts( $user_filter, $quiz_filter )
            : C360_LMS_Storage::get_all_attempts( 500 );
        $views = $user_filter
            ? C360_LMS_Storage::get_user_views( $user_filter )
            : C360_LMS_Storage::get_all_views( 500 );
        $total_attempts = C360_LMS_Storage::count_attempts();
        $total_views    = C360_LMS_Storage::count_views();
        $export_url = wp_nonce_url( admin_url( 'admin-post.php?action=c360_export_csv&type=attempts' ), 'c360_export_csv' );
        $export_views_url = wp_nonce_url( admin_url( 'admin-post.php?action=c360_export_csv&type=views' ), 'c360_export_csv' );
        ?>
        <div class="wrap">
            <h1>Cirrosis 360 — Reportes</h1>
            <p>
                <strong>Total intentos de quiz:</strong> <?php echo intval( $total_attempts ); ?> ·
                <strong>Total vistas de lección:</strong> <?php echo intval( $total_views ); ?>
            </p>
            <p>
                <a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">⬇ Descargar CSV — Intentos de Quiz</a>
                <a href="<?php echo esc_url( $export_views_url ); ?>" class="button">⬇ Descargar CSV — Vistas de Lección</a>
            </p>

            <form method="get" style="margin:1em 0">
                <input type="hidden" name="page" value="c360-lms-reports">
                <label>Filtrar por usuario ID: <input type="number" name="user_id" value="<?php echo $user_filter ?: ''; ?>" style="width:100px"></label>
                <label>Quiz slug: <input type="text" name="quiz_slug" value="<?php echo esc_attr( $quiz_filter ); ?>" placeholder="mod1, bono2..." style="width:120px"></label>
                <button class="button">Filtrar</button>
                <?php if ( $user_filter || $quiz_filter ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=c360-lms-reports' ) ); ?>" class="button button-secondary">Limpiar</a>
                <?php endif; ?>
            </form>

            <h2>Intentos de Quiz (<?php echo count( $attempts ); ?>)</h2>
            <table class="widefat striped">
                <thead><tr>
                    <th>Fecha</th><th>Usuario</th><th>Quiz</th><th>Intento #</th>
                    <th>Puntaje</th><th>Resultado</th><th>Correctas</th><th>IP</th><th>Detalle</th>
                </tr></thead>
                <tbody>
                <?php foreach ( $attempts as $a ) :
                    $u = get_userdata( $a['user_id'] );
                    $answers = json_decode( $a['answers_json'], true );
                    $detail = json_decode( $a['detail_json'], true );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $a['submitted_at'] ); ?></td>
                        <td><?php echo $u ? esc_html( $u->user_email ) : '<em>#' . intval( $a['user_id'] ) . '</em>'; ?></td>
                        <td><code><?php echo esc_html( $a['quiz_slug'] ); ?></code></td>
                        <td><?php echo intval( $a['attempt_no'] ); ?></td>
                        <td><strong><?php echo intval( $a['score'] ); ?>%</strong></td>
                        <td><?php echo $a['passed'] ? '<span style="color:#0a0">✓ Aprobado</span>' : '<span style="color:#c00">✗ No</span>'; ?></td>
                        <td><?php echo intval( $a['correct_count'] ); ?> / <?php echo intval( $a['total_questions'] ); ?></td>
                        <td><code><?php echo esc_html( $a['ip'] ); ?></code></td>
                        <td><details><summary>Ver</summary>
                            <strong>Respuestas:</strong> <code><?php echo esc_html( $a['answers_json'] ); ?></code><br>
                            <strong>Por pregunta:</strong> <code><?php echo esc_html( $a['detail_json'] ); ?></code>
                        </details></td>
                    </tr>
                <?php endforeach; if ( ! $attempts ) : ?>
                    <tr><td colspan="9"><em>Sin intentos aún.</em></td></tr>
                <?php endif; ?>
                </tbody>
            </table>

            <h2 style="margin-top:2em">Vistas de Lección (<?php echo count( $views ); ?>)</h2>
            <table class="widefat striped">
                <thead><tr><th>Fecha</th><th>Usuario</th><th>Lección</th><th>Evento</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ( $views as $v ) :
                    $u = get_userdata( $v['user_id'] );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $v['occurred_at'] ); ?></td>
                        <td><?php echo $u ? esc_html( $u->user_email ) : '<em>#' . intval( $v['user_id'] ) . '</em>'; ?></td>
                        <td><code><?php echo esc_html( $v['lesson_slug'] ); ?></code></td>
                        <td><?php echo esc_html( $v['event'] ); ?></td>
                        <td><code><?php echo esc_html( $v['ip'] ); ?></code></td>
                    </tr>
                <?php endforeach; if ( ! $views ) : ?>
                    <tr><td colspan="5"><em>Sin vistas aún.</em></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function handle_export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        check_admin_referer( 'c360_export_csv' );
        $type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : 'attempts';
        $filename = 'cirrosis360-' . $type . '-' . date( 'Y-m-d' ) . '.csv';
        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        $out = fopen( 'php://output', 'w' );
        fputs( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM for Excel
        if ( $type === 'views' ) {
            fputcsv( $out, array( 'id','user_id','email','course_id','lesson_slug','event','ip','user_agent','occurred_at' ) );
            $rows = C360_LMS_Storage::get_all_views( 100000 );
            foreach ( $rows as $r ) {
                $u = get_userdata( $r['user_id'] );
                fputcsv( $out, array(
                    $r['id'], $r['user_id'], $u ? $u->user_email : '', $r['course_id'],
                    $r['lesson_slug'], $r['event'], $r['ip'], $r['user_agent'], $r['occurred_at'],
                ) );
            }
        } else {
            fputcsv( $out, array(
                'id','user_id','email','course_id','quiz_slug','attempt_no','score','passed',
                'correct_count','total_questions','answers_json','detail_json','ip','submitted_at',
            ) );
            $rows = C360_LMS_Storage::get_all_attempts( 100000 );
            foreach ( $rows as $r ) {
                $u = get_userdata( $r['user_id'] );
                fputcsv( $out, array(
                    $r['id'], $r['user_id'], $u ? $u->user_email : '', $r['course_id'],
                    $r['quiz_slug'], $r['attempt_no'], $r['score'], $r['passed'] ? 'yes' : 'no',
                    $r['correct_count'], $r['total_questions'],
                    $r['answers_json'], $r['detail_json'], $r['ip'], $r['submitted_at'],
                ) );
            }
        }
        fclose( $out );
        exit;
    }

    public static function notices() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'c360-lms' ) === false ) return;
        if ( ! empty( $_GET['enrolled'] ) )   echo '<div class="notice notice-success is-dismissible"><p>Alumno inscrito.</p></div>';
        if ( ! empty( $_GET['unenrolled'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Inscripción removida.</p></div>';
        if ( ! empty( $_GET['saved'] ) )      echo '<div class="notice notice-success is-dismissible"><p>Ajustes guardados.</p></div>';
        if ( ! empty( $_GET['approved'] ) )   echo '<div class="notice notice-success is-dismissible"><p>Usuario aprobado. Email enviado.</p></div>';
        if ( ! empty( $_GET['rejected'] ) )   echo '<div class="notice notice-success is-dismissible"><p>Usuario rechazado.</p></div>';
    }

    public static function page_enrollments() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $students = get_users( array(
            'meta_key'   => C360_LMS_ENROLL_KEY,
            'meta_value' => 1,
            'orderby'    => 'registered',
            'order'      => 'DESC',
        ) );
        $courses = C360_LMS_Config::courses();
        ?>
        <div class="wrap">
            <h1>Cirrosis 360 — Inscripciones Activas</h1>

            <h2>Inscribir manualmente</h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'c360_enroll' ); ?>
                <input type="hidden" name="action" value="c360_enroll">
                <table class="form-table">
                    <tr><th><label for="c360_email">Email del alumno</label></th>
                        <td><input type="email" id="c360_email" name="email" required class="regular-text" placeholder="alumno@ejemplo.com"></td></tr>
                    <tr><th>Curso</th>
                        <td><select name="course_id">
                            <?php foreach ( $courses as $c ) : ?>
                                <option value="<?php echo intval( $c['id'] ); ?>"><?php echo esc_html( $c['title'] ); ?></option>
                            <?php endforeach; ?>
                        </select></td></tr>
                    <tr><th>Crear cuenta si no existe</th>
                        <td><label><input type="checkbox" name="create" value="1" checked> Sí, crear con contraseña aleatoria + email</label></td></tr>
                    <tr><th>Saltar aprobación</th>
                        <td><label><input type="checkbox" name="skip_approval" value="1" checked> Sí, ya pagó (saltar pendiente)</label></td></tr>
                </table>
                <p><button class="button button-primary">Inscribir</button></p>
            </form>

            <h2>Alumnos inscritos (<?php echo count( $students ); ?>)</h2>
            <table class="widefat striped">
                <thead><tr>
                    <th>Email</th><th>Nombre</th><th>Curso</th><th>Inscrito</th>
                    <th>Vence</th><th>Días</th><th>Progreso</th><th>Origen</th><th>Acción</th>
                </tr></thead>
                <tbody>
                <?php foreach ( $students as $u ) :
                    $date = get_user_meta( $u->ID, C360_LMS_ENROLL_DATE_KEY, true );
                    $exp  = C360_LMS_Enrollment::expiry_date( $u->ID );
                    $days = C360_LMS_Enrollment::days_remaining( $u->ID );
                    $cid  = (int) C360_LMS_Enrollment::enrolled_course_id( $u->ID );
                    $course = $cid ? C360_LMS_Config::course( $cid ) : null;
                    $pct  = C360_LMS_Enrollment::progress_percent( $u->ID, $cid );
                    $src  = (string) get_user_meta( $u->ID, '_c360_source', true );
                    ?>
                <tr>
                    <td><?php echo esc_html( $u->user_email ); ?></td>
                    <td><?php echo esc_html( $u->display_name ); ?></td>
                    <td><?php echo $course ? esc_html( $course['title'] ) : '<em>—</em>'; ?></td>
                    <td><?php echo esc_html( $date ); ?></td>
                    <td><?php echo esc_html( $exp ); ?></td>
                    <td><?php echo $days > 0 ? intval( $days ) : '<strong style="color:#c00">Vencido</strong>'; ?></td>
                    <td><?php echo intval( $pct ); ?>%</td>
                    <td><code><?php echo esc_html( $src ); ?></code></td>
                    <td>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                            <?php wp_nonce_field( 'c360_extend_' . $u->ID ); ?>
                            <input type="hidden" name="action" value="c360_extend">
                            <input type="hidden" name="user_id" value="<?php echo intval( $u->ID ); ?>">
                            <input type="number" name="days" value="30" min="1" max="365" style="width:60px">
                            <button class="button button-small">+ días</button>
                        </form>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Quitar inscripción?');">
                            <?php wp_nonce_field( 'c360_unenroll_' . $u->ID ); ?>
                            <input type="hidden" name="action" value="c360_unenroll">
                            <input type="hidden" name="user_id" value="<?php echo intval( $u->ID ); ?>">
                            <button class="button button-link-delete">Desinscribir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; if ( ! $students ) : ?>
                <tr><td colspan="9"><em>Sin inscripciones aún.</em></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public static function page_pending() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $pending = C360_LMS_Approval::pending_users();
        $courses = C360_LMS_Config::courses();
        ?>
        <div class="wrap">
            <h1>Pendientes de Aprobación (<?php echo count( $pending ); ?>)</h1>
            <p>Usuarios que se registraron y esperan tu aprobación para iniciar sesión.</p>

            <?php if ( ! $pending ) : ?>
                <p><em>No hay registros pendientes.</em></p>
            <?php else : ?>
            <table class="widefat striped">
                <thead><tr><th>Email</th><th>Nombre</th><th>Registrado</th><th>Curso solicitado</th><th>IP / Origen</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ( $pending as $u ) :
                    $req_course = (int) get_user_meta( $u->ID, '_c360_requested_course', true );
                    $rc = $req_course ? C360_LMS_Config::course( $req_course ) : null;
                    ?>
                <tr>
                    <td><?php echo esc_html( $u->user_email ); ?></td>
                    <td><?php echo esc_html( $u->display_name ); ?></td>
                    <td><?php echo esc_html( $u->user_registered ); ?></td>
                    <td><?php echo $rc ? esc_html( $rc['title'] ) : '<em>—</em>'; ?></td>
                    <td><code><?php echo esc_html( get_user_meta( $u->ID, '_c360_register_ip', true ) ?: '—' ); ?></code></td>
                    <td>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                            <?php wp_nonce_field( 'c360_approve_' . $u->ID ); ?>
                            <input type="hidden" name="action" value="c360_approve">
                            <input type="hidden" name="user_id" value="<?php echo intval( $u->ID ); ?>">
                            <select name="course_id">
                                <option value="0">— No inscribir aún —</option>
                                <?php foreach ( $courses as $c ) : ?>
                                    <option value="<?php echo intval( $c['id'] ); ?>" <?php selected( $req_course, $c['id'] ); ?>>
                                        Aprobar + inscribir en: <?php echo esc_html( $c['title'] ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="button button-primary">Aprobar</button>
                        </form>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline" onsubmit="return confirm('Rechazar y eliminar usuario?');">
                            <?php wp_nonce_field( 'c360_reject_' . $u->ID ); ?>
                            <input type="hidden" name="action" value="c360_reject">
                            <input type="hidden" name="user_id" value="<?php echo intval( $u->ID ); ?>">
                            <input type="hidden" name="delete" value="1">
                            <button class="button button-link-delete">Rechazar (eliminar)</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function page_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $require = (int) get_option( C360_LMS_Approval::OPT_REQUIRE, 1 );
        $register_url = home_url( '/register/' );
        $login_url    = wp_login_url( home_url( '/dashboard/' ) );
        ?>
        <div class="wrap">
            <h1>Cirrosis 360 — Ajustes</h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'c360_save_settings' ); ?>
                <input type="hidden" name="action" value="c360_save_settings">
                <table class="form-table">
                    <tr>
                        <th>Aprobación obligatoria</th>
                        <td>
                            <label><input type="checkbox" name="require_approval" value="1" <?php checked( $require, 1 ); ?>>
                                Los usuarios que se registran requieren aprobación antes de iniciar sesión.</label>
                            <p class="description">Si está desactivado, los registros pueden iniciar sesión inmediatamente (no recomendado salvo prueba).</p>
                        </td>
                    </tr>
                    <tr><th>URL de registro</th><td><code><?php echo esc_url( $register_url ); ?></code></td></tr>
                    <tr><th>URL de login</th><td><code><?php echo esc_url( $login_url ); ?></code></td></tr>
                    <tr><th>Webhook MercadoPago</th><td><code><?php echo esc_url( home_url( '/?c360_mp_webhook=1' ) ); ?></code><br>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=c360-lms-mp' ) ); ?>">Configurar token + secret →</a></td></tr>
                </table>
                <?php submit_button( 'Guardar Ajustes' ); ?>
            </form>
        </div>
        <?php
    }

    public static function handle_enroll() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        check_admin_referer( 'c360_enroll' );

        $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $course_id = absint( $_POST['course_id'] ?? 0 );
        $create = ! empty( $_POST['create'] );
        $skip = ! empty( $_POST['skip_approval'] );
        if ( ! is_email( $email ) ) wp_die( 'Email inválido', 400 );

        $user = get_user_by( 'email', $email );
        if ( ! $user && $create ) {
            $username_base = sanitize_user( current( explode( '@', $email ) ), true );
            if ( ! $username_base ) $username_base = 'user';
            $username = $username_base;
            $i = 1;
            while ( username_exists( $username ) ) { $username = $username_base . $i++; }
            $pw = wp_generate_password( 16, true );
            $uid = wp_create_user( $username, $pw, $email );
            if ( is_wp_error( $uid ) ) wp_die( esc_html( $uid->get_error_message() ), 400 );
            wp_new_user_notification( $uid, null, 'user' );
            $user = get_userdata( $uid );
        }
        if ( ! $user ) wp_die( 'Usuario no encontrado y no se pidió crear.', 404 );

        // Skip pending flag (admin-curated enrollment).
        if ( $skip ) {
            delete_user_meta( $user->ID, C360_LMS_Approval::META_PENDING );
            delete_user_meta( $user->ID, C360_LMS_Approval::META_REJECTED );
        }

        C360_LMS_Enrollment::enroll( $user->ID, 'manual', $course_id );
        wp_safe_redirect( admin_url( 'admin.php?page=c360-lms&enrolled=1' ) );
        exit;
    }

    public static function handle_unenroll() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $uid = absint( $_POST['user_id'] ?? 0 );
        check_admin_referer( 'c360_unenroll_' . $uid );
        C360_LMS_Enrollment::unenroll( $uid );
        wp_safe_redirect( admin_url( 'admin.php?page=c360-lms&unenrolled=1' ) );
        exit;
    }

    public static function handle_extend() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        $uid = absint( $_POST['user_id'] ?? 0 );
        check_admin_referer( 'c360_extend_' . $uid );
        $days = absint( $_POST['days'] ?? 0 );
        C360_LMS_Enrollment::extend( $uid, $days );
        wp_safe_redirect( admin_url( 'admin.php?page=c360-lms&enrolled=1' ) );
        exit;
    }

    public static function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No autorizado', 403 );
        check_admin_referer( 'c360_save_settings' );
        update_option( C360_LMS_Approval::OPT_REQUIRE, ! empty( $_POST['require_approval'] ) ? 1 : 0 );
        wp_safe_redirect( admin_url( 'admin.php?page=c360-lms-settings&saved=1' ) );
        exit;
    }
}
