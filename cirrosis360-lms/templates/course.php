<?php
/**
 * Single course detail page.
 *
 * @var array $course
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( empty( $course ) ) return;

$mp = esc_url( get_theme_mod( 'jsma_mercadopago', 'https://mpago.la/1sXKiM3' ) );
$logged   = is_user_logged_in();
$enrolled = $logged && C360_LMS_Enrollment::is_enrolled( null, $course['id'] );
?>
<section class="c360-section">
    <p class="c360-breadcrumb"><a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">← Todos los cursos</a></p>
    <h1><?php echo esc_html( $course['title'] ); ?></h1>
    <?php if ( $course['subtitle'] ) : ?>
        <p class="c360-subtitle"><?php echo esc_html( $course['subtitle'] ); ?></p>
    <?php endif; ?>

    <div class="c360-course-card">
        <div class="c360-course-meta">
            <p><strong>Precio:</strong> <?php echo esc_html( $course['price'] ); ?> ·
               <strong>Acceso:</strong> <?php echo esc_html( $course['duration_label'] ); ?></p>
        </div>
        <?php if ( $course['description'] ) : ?>
            <div class="c360-course-desc"><?php echo wp_kses_post( wpautop( $course['description'] ) ); ?></div>
        <?php endif; ?>
        <h3>Contenido del curso</h3>
        <ul class="c360-lesson-list">
            <?php foreach ( $course['lessons'] as $l ) : ?>
                <li>
                    <span class="c360-pill <?php echo $l['type'] === 'module' ? 'c360-pill-mod' : 'c360-pill-bono'; ?>">
                        <?php echo $l['type'] === 'module' ? 'Módulo' : 'Bono'; ?>
                    </span>
                    <?php echo esc_html( $l['title'] ); ?>
                    <?php if ( $l['has_quiz'] ) : ?> <small>+ Quiz</small><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="c360-actions">
            <?php if ( $enrolled ) : ?>
                <a class="c360-btn c360-btn-primary" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">Ir a Mi Dashboard →</a>
            <?php else : ?>
                <a class="c360-btn c360-btn-primary" href="<?php echo $mp; ?>" target="_blank" rel="noopener">
                    Inscribirme — <?php echo esc_html( $course['price'] ); ?>
                </a>
                <?php if ( ! $logged ) : ?>
                    <a class="c360-btn c360-btn-secondary" href="<?php echo esc_url( add_query_arg( 'course', $course['slug'], home_url( '/register/' ) ) ); ?>">Crear cuenta</a>
                    <a class="c360-btn c360-btn-secondary" href="<?php echo esc_url( wp_login_url( home_url( '/dashboard/' ) ) ); ?>">Ya pagué — Iniciar sesión</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
