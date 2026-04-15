<?php
/**
 * Tutor LMS — Single Course Template Override
 * Branded course page with Vimeo embeds
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$course_id = get_the_ID();
?>

<div class="tutor-single-course-wrap">
  <div class="container" style="padding-top:6rem;">

    <?php while ( have_posts() ) : the_post(); ?>

    <div class="course-header" data-animate="fade-up">
      <div class="section-eyebrow">Programa del Dr. Julio</div>
      <h1 class="section-title"><?php the_title(); ?></h1>
      <div class="course-meta-bar">
        <span>&#128218; <?php echo tutor_utils()->count_completed_contents_by_course( $course_id ); ?> lecciones completadas</span>
        <span>&#128337; 6 meses de acceso</span>
        <span>&#127891; Certificado incluido</span>
      </div>
    </div>

    <div class="course-content-area">
      <?php the_content(); ?>

      <?php if ( function_exists( 'tutor_course_content' ) ) : ?>
        <div class="tutor-course-content-wrap">
          <?php tutor_course_content(); ?>
        </div>
      <?php endif; ?>

      <?php if ( function_exists( 'tutor_course_enrolled_nav' ) ) : ?>
        <?php tutor_course_enrolled_nav(); ?>
      <?php endif; ?>
    </div>

    <?php endwhile; ?>

  </div>
</div>

<?php get_footer(); ?>
