<?php
/**
 * Portal home — student gateway view.
 * Rendered only when jsma_is_portal() === true. Included from front-page.php between get_header() / get_footer().
 *
 * Goals:
 *  - Logged-out students: branded login card → wp_login_form (no sales copy, no Mercado Pago).
 *  - Logged-in students: dashboard CTA → Tutor LMS, module quick-links, support, expiry reminder.
 *  - No pricing, no testimonials, no purchase funnel — that lives on the landing subdomain.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$current_user  = wp_get_current_user();
$display_name  = $current_user && $current_user->ID ? $current_user->display_name : '';
$dashboard_url = jsma_is_tutor_active() && function_exists( 'tutor_utils' )
    ? tutor_utils()->tutor_dashboard_url()
    : home_url( '/dashboard/' );
$login_url     = wp_login_url( $dashboard_url );
$logout_url    = wp_logout_url( home_url( '/' ) );
$support_email = esc_html( get_theme_mod( 'jsma_email', 'contacto@juliosantiagomarcelo.com' ) );
$whatsapp      = esc_url( get_theme_mod( 'jsma_whatsapp', '' ) );
$courses_url   = home_url( '/courses/' );
?>

<!-- ═══════ PORTAL HERO ═══════ -->
<header class="hero portal-hero" id="hero">
  <div class="hero-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>

  <div class="hero-content">
    <div class="hero-badge" data-animate="fade-down">
      <span class="badge-dot"></span>
      Portal de Alumnos — Cirrosis 360
    </div>

    <?php if ( is_user_logged_in() ) : ?>

      <h1 data-animate="fade-up">
        Bienvenido, <span class="text-gradient"><?php echo esc_html( $display_name ); ?></span>
      </h1>
      <p class="hero-subtitle" data-animate="fade-up" data-delay="100">
        Acceda a sus módulos, bonos exclusivos y clases en vivo. Su cuidado del hígado, paso a paso.
      </p>

      <div class="hero-cta-row" data-animate="fade-up" data-delay="200">
        <a href="<?php echo esc_url( $dashboard_url ); ?>" class="btn btn-gold btn-lg">
          <span>Ir a Mi Curso</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="<?php echo esc_url( $logout_url ); ?>" class="btn btn-ghost btn-lg">
          Cerrar Sesión
        </a>
      </div>

    <?php else : ?>

      <h1 data-animate="fade-up">
        Acceso de <span class="text-gradient">Alumnos</span>
      </h1>
      <p class="hero-subtitle" data-animate="fade-up" data-delay="100">
        Inicie sesión con las credenciales que recibió por email después de su inscripción.
      </p>

      <div class="hero-cta-row" data-animate="fade-up" data-delay="200">
        <a href="<?php echo esc_url( $login_url ); ?>" class="btn btn-gold btn-lg">
          <span>Iniciar Sesión</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="#login-card" class="btn btn-ghost btn-lg">
          Acceso Rápido
        </a>
      </div>

    <?php endif; ?>

    <div class="hero-trust-bar" data-animate="fade-up" data-delay="400">
      <span>&#128274; Acceso Seguro</span>
      <span>&#127909; Videos en Vimeo HD</span>
      <span>&#128218; 6 Meses de Acceso</span>
    </div>
  </div>
</header>

<?php if ( is_user_logged_in() ) : ?>

  <!-- ═══════ STUDENT DASHBOARD ═══════ -->
  <section class="section section-light" id="mi-curso">
    <div class="container">
      <div class="section-eyebrow" data-animate="fade-up">Mi Programa</div>
      <h2 class="section-title" data-animate="fade-up">
        Continúe donde <span class="text-primary">lo dejó</span>
      </h2>

      <!-- Expiry / progress notice -->
      <div class="expiration-notice" data-animate="fade-up" data-delay="100" style="margin:2rem 0;padding:1.25rem 1.5rem;background:var(--primary-light);border:1px solid var(--primary);border-radius:12px;display:flex;align-items:center;gap:1rem;">
        <span style="font-size:1.5rem;">&#128337;</span>
        <div>
          <strong style="color:var(--primary-dark);">Su acceso al programa</strong>
          <p style="font-size:0.85rem;color:var(--gray-600);margin:0;">Recuerde que tiene 6 meses de acceso desde su fecha de inscripción. Aproveche todo el contenido disponible.</p>
        </div>
      </div>

      <!-- LMS dashboard embed: c360-lms (custom) > Tutor LMS > static grid fallback -->
      <?php if ( defined( 'C360_LMS_VERSION' ) ) : ?>
        <div class="c360-dashboard-embed" data-animate="fade-up" data-delay="200">
          <?php echo do_shortcode( '[c360_dashboard]' ); ?>
        </div>
      <?php elseif ( jsma_is_tutor_active() && function_exists( 'tutor_load_template' ) ) : ?>
        <div class="tutor-dashboard-embed" data-animate="fade-up" data-delay="200">
          <?php tutor_load_template( 'dashboard.index' ); ?>
        </div>
      <?php else : ?>
        <div class="modules-grid" data-animate="fade-up" data-delay="200">
          <a href="<?php echo esc_url( $courses_url ); ?>" class="module-card portal-card">
            <div class="module-number">01</div>
            <div class="module-icon">&#128216;</div>
            <h3>Descompensaciones Evitables</h3>
            <p>Errores diarios que ponen en riesgo tu cirrosis. Acceda al módulo completo.</p>
            <span class="module-link">Abrir Módulo →</span>
          </a>
          <a href="<?php echo esc_url( $courses_url ); ?>" class="module-card portal-card">
            <div class="module-number">02</div>
            <div class="module-icon">&#128215;</div>
            <h3>Nutrición Clave</h3>
            <p>Por qué la última comida antes de dormir es vital. Guía práctica de alimentación.</p>
            <span class="module-link">Abrir Módulo →</span>
          </a>
          <a href="<?php echo esc_url( $courses_url ); ?>" class="module-card portal-card">
            <div class="module-number">03</div>
            <div class="module-icon">&#128213;</div>
            <h3>Semáforo de la Cirrosis</h3>
            <p>Identifique cambios clave para saber cuándo actuar. Sistema visual de alerta.</p>
            <span class="module-link">Abrir Módulo →</span>
          </a>
        </div>

        <div class="bonos-grid" data-animate="fade-up" data-delay="300" style="margin-top:2rem;">
          <a href="<?php echo esc_url( $courses_url ); ?>" class="bono-card portal-card">
            <div class="bono-ribbon">Bono 01</div>
            <div class="bono-icon">&#127857;</div>
            <h3>El Mapa de Alimentos</h3>
            <p>Qué poner en el plato para nutrir a un paciente con cirrosis.</p>
          </a>
          <a href="<?php echo esc_url( $courses_url ); ?>" class="bono-card portal-card">
            <div class="bono-ribbon">Bono 02</div>
            <div class="bono-icon">&#129657;</div>
            <h3>Signos Vitales Paso a Paso</h3>
            <p>Cómo medir y controlar la salud de su familiar.</p>
          </a>
          <a href="<?php echo esc_url( $courses_url ); ?>" class="bono-card portal-card">
            <div class="bono-ribbon">Bono 03</div>
            <div class="bono-icon">&#128221;</div>
            <h3>El Método Kardex</h3>
            <p>Cree un Diario de Control y nunca más confunda medicinas.</p>
          </a>
        </div>

        <div class="live-classes-card" data-animate="fade-up" data-delay="400" style="margin-top:2rem;">
          <div class="live-icon">&#128250;</div>
          <div class="live-info">
            <h3><span class="live-dot"></span>Clases en Vivo con el Dr. Julio</h3>
            <p>Próximas sesiones publicadas dentro de su curso. Acceda al cronograma desde Mi Curso.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

<?php else : ?>

  <!-- ═══════ LOGIN CARD ═══════ -->
  <section class="section section-light" id="login-card">
    <div class="container container-narrow">
      <div class="section-eyebrow" data-animate="fade-up">Iniciar Sesión</div>
      <h2 class="section-title" data-animate="fade-up">
        Ingrese a su <span class="text-primary">portal de alumno</span>
      </h2>

      <div class="login-card-wrap" data-animate="fade-up" data-delay="100" style="max-width:440px;margin:2rem auto 0;background:var(--white);border-radius:20px;padding:2.5rem 2rem;box-shadow:var(--shadow-lg);border:1px solid var(--gray-200);">
        <?php
        wp_login_form( array(
            'redirect'       => $dashboard_url,
            'label_username' => 'Usuario o Email',
            'label_password' => 'Contraseña',
            'label_log_in'   => 'Acceder al Programa',
            'remember'       => true,
            'label_remember' => 'Recordarme',
        ) );
        ?>
        <p style="text-align:center;font-size:0.85rem;color:var(--gray-500);margin-top:1.25rem;">
          <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" style="color:var(--primary);">¿Olvidó su contraseña?</a>
        </p>
        <div style="height:1px;background:var(--gray-200);margin:1.5rem 0;"></div>
        <p style="text-align:center;font-size:0.85rem;color:var(--gray-500);margin:0;">
          ¿Aún no se ha inscrito?<br>
          <a href="https://cirrosis360.juliosantiagomarcelo.com/" style="color:var(--gold);font-weight:600;" target="_blank" rel="noopener">Conozca el programa Cirrosis 360 →</a>
        </p>
      </div>
    </div>
  </section>

<?php endif; ?>

<!-- ═══════ SUPPORT ═══════ -->
<section class="section section-cream" id="soporte">
  <div class="container container-narrow">
    <div class="section-eyebrow" data-animate="fade-up">¿Necesita Ayuda?</div>
    <h2 class="section-title" data-animate="fade-up">
      Estamos para <span class="text-primary">acompañarle</span>
    </h2>
    <p class="section-subtitle" data-animate="fade-up">Si tiene problemas técnicos o preguntas sobre su acceso, contáctenos directamente.</p>

    <div class="support-grid" data-animate="fade-up" data-delay="100" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;margin-top:2rem;">
      <a href="mailto:<?php echo esc_attr( $support_email ); ?>" class="support-card" style="background:var(--white);padding:1.5rem;border-radius:14px;border:1px solid var(--gray-200);text-decoration:none;color:inherit;display:flex;align-items:center;gap:1rem;">
        <span style="font-size:1.75rem;">&#9993;&#65039;</span>
        <div>
          <strong style="display:block;color:var(--primary-dark);">Email de Soporte</strong>
          <span style="font-size:0.85rem;color:var(--gray-600);"><?php echo $support_email; ?></span>
        </div>
      </a>
      <?php if ( $whatsapp ) : ?>
      <a href="<?php echo $whatsapp; ?>" class="support-card" target="_blank" rel="noopener" style="background:var(--white);padding:1.5rem;border-radius:14px;border:1px solid var(--gray-200);text-decoration:none;color:inherit;display:flex;align-items:center;gap:1rem;">
        <span style="font-size:1.75rem;">&#128241;</span>
        <div>
          <strong style="display:block;color:var(--primary-dark);">WhatsApp</strong>
          <span style="font-size:0.85rem;color:var(--gray-600);">Soporte directo</span>
        </div>
      </a>
      <?php endif; ?>
      <a href="https://cirrosis360.juliosantiagomarcelo.com/#faq" class="support-card" target="_blank" rel="noopener" style="background:var(--white);padding:1.5rem;border-radius:14px;border:1px solid var(--gray-200);text-decoration:none;color:inherit;display:flex;align-items:center;gap:1rem;">
        <span style="font-size:1.75rem;">&#10067;</span>
        <div>
          <strong style="display:block;color:var(--primary-dark);">Preguntas Frecuentes</strong>
          <span style="font-size:0.85rem;color:var(--gray-600);">Respuestas rápidas</span>
        </div>
      </a>
    </div>
  </div>
</section>
