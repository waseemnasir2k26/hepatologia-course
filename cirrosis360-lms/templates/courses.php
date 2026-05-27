<?php
/**
 * Public catalog — list all published courses.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$courses = C360_LMS_Config::courses();
?>
<section class="c360-section">
    <h1>Programas disponibles</h1>
    <?php if ( ! $courses ) : ?>
        <p><em>Aún no hay cursos publicados.</em></p>
    <?php else : ?>
    <div class="c360-grid">
        <?php foreach ( $courses as $c ) : ?>
            <a class="c360-card" href="<?php echo esc_url( home_url( '/courses/' . $c['slug'] . '/' ) ); ?>">
                <h3><?php echo esc_html( $c['title'] ); ?></h3>
                <?php if ( $c['subtitle'] ) : ?><p><?php echo esc_html( $c['subtitle'] ); ?></p><?php endif; ?>
                <div class="c360-card-tags">
                    <span><?php echo esc_html( $c['price'] ); ?></span>
                    <span><?php echo count( $c['lessons'] ); ?> lecciones</span>
                    <span><?php echo esc_html( $c['duration_label'] ); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
