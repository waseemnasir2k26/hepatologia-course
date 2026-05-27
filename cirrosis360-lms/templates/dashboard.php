<?php
/**
 * Dashboard view — course-aware student gateway.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! is_user_logged_in() ) {
    echo do_shortcode( '[c360_login_or_dashboard]' );
    return;
}

$uid = get_current_user_id();
$user = wp_get_current_user();

// Pending users shouldn't reach here (block_pending blocks login) — safety check anyway.
if ( class_exists( 'C360_LMS_Approval' ) && C360_LMS_Approval::is_pending( $uid ) ) {
    ?>
    <section class="c360-section">
        <div class="c360-notice c360-notice-warn">
            <h3>Cuenta pendiente</h3>
            <p>Tu cuenta está pendiente de aprobación. Recibirás un email cuando sea aprobada.</p>
        </div>
        <p><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Cerrar sesión</a></p>
    </section>
    <?php
    return;
}

if ( ! C360_LMS_Enrollment::is_enrolled( $uid ) ) {
    $mp = esc_url( get_theme_mod( 'jsma_mercadopago', 'https://mpago.la/1sXKiM3' ) );
    ?>
    <section class="c360-section">
        <h1>Hola, <?php echo esc_html( $user->display_name ); ?></h1>
        <div class="c360-notice c360-notice-warn">
            <p>No tienes inscripción activa.</p>
            <a class="c360-btn c360-btn-primary" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">Ver cursos disponibles</a>
        </div>
        <p style="margin-top:2rem"><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Cerrar sesión</a></p>
    </section>
    <?php
    return;
}

$course_id = (int) C360_LMS_Enrollment::enrolled_course_id( $uid );
$course    = $course_id ? C360_LMS_Config::course( $course_id ) : C360_LMS_Config::default_course();
$lessons   = $course ? $course['lessons'] : array();
$pct       = C360_LMS_Enrollment::progress_percent( $uid, $course ? $course['id'] : 0 );
$exp       = C360_LMS_Enrollment::expiry_date( $uid );
$days      = C360_LMS_Enrollment::days_remaining( $uid );
?>
<section class="c360-section">
    <header class="c360-dash-head">
        <div>
            <h1>Bienvenido, <?php echo esc_html( $user->display_name ); ?></h1>
            <?php if ( $course ) : ?>
                <p class="c360-dash-meta"><?php echo esc_html( $course['title'] ); ?></p>
            <?php endif; ?>
            <p class="c360-dash-meta">Acceso vence el <strong><?php echo esc_html( $exp ); ?></strong> · <?php echo intval( $days ); ?> días restantes</p>
        </div>
        <div class="c360-progress">
            <div class="c360-progress-label">Progreso</div>
            <div class="c360-progress-bar"><span style="width:<?php echo intval( $pct ); ?>%"></span></div>
            <div class="c360-progress-pct"><?php echo intval( $pct ); ?>%</div>
        </div>
    </header>

    <?php
    $modules = array_filter( $lessons, function ( $l ) { return $l['type'] === 'module'; } );
    $bonuses = array_filter( $lessons, function ( $l ) { return $l['type'] === 'bonus'; } );

    if ( $modules ) : ?>
    <h2 class="c360-h2">Módulos</h2>
    <div class="c360-grid">
        <?php $i = 0; foreach ( $modules as $slug => $l ) : $i++;
            $done = C360_LMS_Enrollment::is_complete( $uid, $slug );
            $quiz = $l['has_quiz'] ? C360_LMS_Enrollment::quiz_result( $uid, $slug ) : null;
            ?>
        <a class="c360-card<?php echo $done ? ' c360-card-done' : ''; ?>" href="<?php echo esc_url( home_url( '/lesson/' . $slug . '/' ) ); ?>">
            <span class="c360-card-num">M<?php echo intval( $i ); ?></span>
            <h3><?php echo esc_html( $l['title'] ); ?></h3>
            <?php if ( $l['subtitle'] ) : ?><p><?php echo esc_html( $l['subtitle'] ); ?></p><?php endif; ?>
            <div class="c360-card-tags">
                <span>Video</span><?php if ( $l['pdf'] ) : ?><span>PDF</span><?php endif; ?>
                <?php if ( $l['has_quiz'] ) : ?><span>Quiz</span><?php endif; ?>
                <?php if ( $done ) : ?><span class="c360-tag-done">✓ Completo</span><?php endif; ?>
                <?php if ( $quiz && is_array( $quiz ) ) : ?>
                    <span class="c360-tag-score"><?php echo intval( $quiz['score'] ); ?>%</span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ( $bonuses ) : ?>
    <h2 class="c360-h2">Bonos</h2>
    <div class="c360-grid">
        <?php $i = 0; foreach ( $bonuses as $slug => $l ) : $i++;
            $done = C360_LMS_Enrollment::is_complete( $uid, $slug );
            ?>
        <a class="c360-card c360-card-bonus<?php echo $done ? ' c360-card-done' : ''; ?>" href="<?php echo esc_url( home_url( '/lesson/' . $slug . '/' ) ); ?>">
            <span class="c360-card-num">B<?php echo intval( $i ); ?></span>
            <h3><?php echo esc_html( $l['title'] ); ?></h3>
            <?php if ( $l['subtitle'] ) : ?><p><?php echo esc_html( $l['subtitle'] ); ?></p><?php endif; ?>
            <div class="c360-card-tags">
                <span>Video</span><?php if ( $l['pdf'] ) : ?><span>PDF</span><?php endif; ?>
                <?php if ( $done ) : ?><span class="c360-tag-done">✓ Completo</span><?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <p class="c360-dash-foot">
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Cerrar sesión</a>
    </p>
</section>
