<?php
/**
 * Quiz view.
 *
 * @var array $lesson
 * @var array $quiz
 */
if ( ! defined( 'ABSPATH' ) ) exit;
if ( empty( $quiz ) || empty( $lesson ) ) return;
?>
<section class="c360-section c360-quiz">
    <?php if ( ! empty( $admin_preview ) ) : ?>
        <div style="background:#fff8db;border-left:4px solid #c89b3c;padding:12px 18px;margin-bottom:18px;border-radius:6px;font-size:14px">
            🔍 <strong>Modo Vista Previa Admin</strong> — está viendo el quiz como administrador. Los alumnos inscritos verán esta misma página.
        </div>
    <?php endif; ?>
    <p class="c360-breadcrumb">
        <a href="<?php echo esc_url( home_url( '/lesson/' . $lesson['slug'] . '/' ) ); ?>">← Volver a la lección</a>
    </p>
    <h1><?php echo esc_html( $quiz['title'] ); ?></h1>
    <p class="c360-subtitle">Puntaje mínimo para aprobar: <strong><?php echo intval( $quiz['pass_score'] ); ?>%</strong></p>

    <form id="c360-quiz-form" data-slug="<?php echo esc_attr( $lesson['slug'] ); ?>">
        <?php foreach ( $quiz['questions'] as $i => $q ) : ?>
            <fieldset class="c360-q">
                <legend><strong>Pregunta <?php echo $i + 1; ?>.</strong> <?php echo esc_html( $q['q'] ); ?></legend>
                <?php foreach ( $q['options'] as $j => $opt ) : ?>
                    <label class="c360-opt">
                        <input type="radio" name="answers[<?php echo $i; ?>]" value="<?php echo $j; ?>" required>
                        <span><?php echo esc_html( $opt ); ?></span>
                    </label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>

        <button type="submit" class="c360-btn c360-btn-primary">Enviar respuestas</button>
    </form>

    <div id="c360-quiz-result" class="c360-quiz-result" hidden></div>
</section>
