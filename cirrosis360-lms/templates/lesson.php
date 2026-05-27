<?php
/**
 * Lesson view — LMS layout: main column + sticky sidebar with full lesson list, prev/next nav.
 *
 * @var array $lesson
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( empty( $lesson ) ) return;

$uid       = get_current_user_id();
$slug      = $lesson['slug'];
$course_id = (int) $lesson['course_id'];
$course    = $course_id ? C360_LMS_Config::course( $course_id ) : null;
$all       = $course ? $course['lessons'] : array();
$pdf       = (string) $lesson['pdf'];
$done      = C360_LMS_Enrollment::is_complete( $uid, $slug );
$quiz_r    = $lesson['has_quiz'] ? C360_LMS_Enrollment::quiz_result( $uid, $slug ) : null;
$progress  = C360_LMS_Enrollment::progress_percent( $uid, $course_id );
$exp_date  = C360_LMS_Enrollment::expiry_date( $uid );
$days_left = C360_LMS_Enrollment::days_remaining( $uid );

// Build prev/next from full lesson list (already ordered by menu_order).
$slugs = array_keys( $all );
$idx = array_search( $slug, $slugs, true );
$prev_slug = ( $idx !== false && $idx > 0 ) ? $slugs[ $idx - 1 ] : null;
$next_slug = ( $idx !== false && $idx < count( $slugs ) - 1 ) ? $slugs[ $idx + 1 ] : null;
$prev = $prev_slug ? $all[ $prev_slug ] : null;
$next = $next_slug ? $all[ $next_slug ] : null;

$modules = array_filter( $all, function ( $l ) { return $l['type'] === 'module'; } );
$bonuses = array_filter( $all, function ( $l ) { return $l['type'] === 'bonus'; } );
?>
<div class="c360-lesson-layout" data-current="<?php echo esc_attr( $slug ); ?>">

    <!-- ═══════ MAIN ═══════ -->
    <main class="c360-lesson-main">
        <p class="c360-breadcrumb">
            <a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">← Dashboard</a>
            <?php if ( $course ) : ?> · <span><?php echo esc_html( $course['title'] ); ?></span><?php endif; ?>
        </p>

        <button class="c360-sidebar-toggle" type="button" aria-label="Mostrar lecciones" aria-expanded="false">
            <span></span><span></span><span></span> Lecciones del curso
        </button>

        <h1 class="c360-lesson-title"><?php echo esc_html( $lesson['title'] ); ?></h1>
        <?php if ( $lesson['subtitle'] ) : ?>
            <p class="c360-subtitle"><?php echo esc_html( $lesson['subtitle'] ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $lesson['vimeo'] ) ) : ?>
            <div class="c360-video">
                <iframe src="https://player.vimeo.com/video/<?php echo esc_attr( $lesson['vimeo'] ); ?>?title=0&byline=0&portrait=0"
                        frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
            </div>
        <?php else : ?>
            <div class="c360-notice c360-notice-warn"><p>El video estará disponible pronto.</p></div>
        <?php endif; ?>

        <div class="c360-actions">
            <?php if ( $pdf ) : ?>
                <a class="c360-btn c360-btn-secondary" href="<?php echo esc_url( $pdf ); ?>" target="_blank" rel="noopener">⬇ Descargar PDF</a>
            <?php else : ?>
                <span class="c360-pdf-pending">PDF próximamente.</span>
            <?php endif; ?>

            <?php if ( $lesson['has_quiz'] ) : ?>
                <a class="c360-btn c360-btn-primary" href="<?php echo esc_url( home_url( '/quiz/' . $slug . '/' ) ); ?>">
                    <?php echo $quiz_r ? 'Repetir Quiz' : 'Tomar Quiz'; ?>
                </a>
                <?php if ( $quiz_r && is_array( $quiz_r ) ) : ?>
                    <span class="c360-quiz-score">Tu puntaje: <strong><?php echo intval( $quiz_r['score'] ); ?>%</strong></span>
                <?php endif; ?>
            <?php else : ?>
                <button class="c360-btn c360-btn-primary" data-c360-complete="<?php echo esc_attr( $slug ); ?>" <?php echo $done ? 'disabled' : ''; ?>>
                    <?php echo $done ? '✓ Completada' : 'Marcar como completa'; ?>
                </button>
            <?php endif; ?>
        </div>

        <!-- Prev/Next navigation -->
        <nav class="c360-prev-next" aria-label="Navegación entre lecciones">
            <?php if ( $prev ) : ?>
                <a class="c360-nav-link c360-nav-prev" href="<?php echo esc_url( home_url( '/lesson/' . $prev['slug'] . '/' ) ); ?>">
                    <span class="c360-nav-label">← Anterior</span>
                    <span class="c360-nav-title"><?php echo esc_html( $prev['title'] ); ?></span>
                </a>
            <?php else : ?><span></span><?php endif; ?>

            <?php if ( $next ) : ?>
                <a class="c360-nav-link c360-nav-next" href="<?php echo esc_url( home_url( '/lesson/' . $next['slug'] . '/' ) ); ?>">
                    <span class="c360-nav-label">Siguiente →</span>
                    <span class="c360-nav-title"><?php echo esc_html( $next['title'] ); ?></span>
                </a>
            <?php else : ?>
                <a class="c360-nav-link c360-nav-next" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">
                    <span class="c360-nav-label">Volver →</span>
                    <span class="c360-nav-title">Dashboard</span>
                </a>
            <?php endif; ?>
        </nav>
    </main>

    <!-- ═══════ SIDEBAR ═══════ -->
    <aside class="c360-lesson-sidebar" id="c360-sidebar" aria-label="Lista de lecciones">
        <div class="c360-sidebar-inner">
            <button type="button" class="c360-sidebar-close" aria-label="Cerrar">&times;</button>

            <?php if ( $course ) : ?>
                <div class="c360-side-course">
                    <div class="c360-side-course-eyebrow">Curso</div>
                    <h2 class="c360-side-course-title"><?php echo esc_html( $course['title'] ); ?></h2>
                </div>
            <?php endif; ?>

            <div class="c360-side-progress">
                <div class="c360-side-progress-row">
                    <span>Progreso</span>
                    <strong><?php echo intval( $progress ); ?>%</strong>
                </div>
                <div class="c360-progress-bar"><span style="width:<?php echo intval( $progress ); ?>%"></span></div>
                <div class="c360-side-expiry">
                    Acceso: <strong><?php echo intval( $days_left ); ?> días</strong>
                    <span> (vence <?php echo esc_html( $exp_date ); ?>)</span>
                </div>
            </div>

            <?php if ( $modules ) : ?>
                <div class="c360-side-section">
                    <h3>Módulos</h3>
                    <ul class="c360-side-list">
                        <?php $i = 0; foreach ( $modules as $sl => $l ) : $i++;
                            $is_current = ( $sl === $slug );
                            $is_done = C360_LMS_Enrollment::is_complete( $uid, $sl );
                            ?>
                        <li class="c360-side-item<?php echo $is_current ? ' is-current' : ''; ?><?php echo $is_done ? ' is-done' : ''; ?>">
                            <a href="<?php echo esc_url( home_url( '/lesson/' . $sl . '/' ) ); ?>">
                                <span class="c360-side-num"><?php echo $is_done ? '✓' : intval( $i ); ?></span>
                                <span class="c360-side-text">
                                    <span class="c360-side-title"><?php echo esc_html( $l['title'] ); ?></span>
                                    <span class="c360-side-tags">
                                        <?php if ( ! empty( $l['vimeo'] ) ) : ?><span>Video</span><?php endif; ?>
                                        <?php if ( $l['pdf'] ) : ?><span>PDF</span><?php endif; ?>
                                        <?php if ( $l['has_quiz'] ) : ?><span>Quiz</span><?php endif; ?>
                                    </span>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( $bonuses ) : ?>
                <div class="c360-side-section">
                    <h3>Bonos</h3>
                    <ul class="c360-side-list c360-side-list-bonus">
                        <?php $i = 0; foreach ( $bonuses as $sl => $l ) : $i++;
                            $is_current = ( $sl === $slug );
                            $is_done = C360_LMS_Enrollment::is_complete( $uid, $sl );
                            ?>
                        <li class="c360-side-item<?php echo $is_current ? ' is-current' : ''; ?><?php echo $is_done ? ' is-done' : ''; ?>">
                            <a href="<?php echo esc_url( home_url( '/lesson/' . $sl . '/' ) ); ?>">
                                <span class="c360-side-num c360-side-num-bonus"><?php echo $is_done ? '✓' : 'B' . intval( $i ); ?></span>
                                <span class="c360-side-text">
                                    <span class="c360-side-title"><?php echo esc_html( $l['title'] ); ?></span>
                                    <span class="c360-side-tags">
                                        <?php if ( ! empty( $l['vimeo'] ) ) : ?><span>Video</span><?php endif; ?>
                                        <?php if ( $l['pdf'] ) : ?><span>PDF</span><?php endif; ?>
                                    </span>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="c360-side-foot">
                <a class="c360-btn c360-btn-secondary c360-btn-block" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">Dashboard completo</a>
                <a class="c360-side-logout" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Cerrar sesión</a>
            </div>
        </div>
    </aside>

    <div class="c360-sidebar-backdrop" hidden></div>
</div>
