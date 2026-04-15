<?php
/**
 * Template Name: Landing del Curso
 * Sales funnel landing page for the Cirrhosis Course
 */
get_header();

$mp_link       = esc_url( get_theme_mod( 'jsma_mercadopago', 'https://mpago.la/1sXKiM3' ) );
$vid_mod1      = esc_attr( get_theme_mod( 'jsma_vimeo_mod1', '1180151832' ) );
$vid_mod2      = esc_attr( get_theme_mod( 'jsma_vimeo_mod2', '1147358050' ) );
$vid_mod3      = esc_attr( get_theme_mod( 'jsma_vimeo_mod3', '1163996678' ) );
$vid_bon1      = esc_attr( get_theme_mod( 'jsma_vimeo_bon1', '1181683995' ) );
$vid_bon2      = esc_attr( get_theme_mod( 'jsma_vimeo_bon2', '1181686811' ) );
$vid_bon3      = esc_attr( get_theme_mod( 'jsma_vimeo_bon3', '1181691449' ) );
$vid_test      = esc_attr( get_theme_mod( 'jsma_vimeo_test', '1182155574' ) );
$doctor_photo  = esc_url( get_theme_mod( 'jsma_doctor_photo', '' ) );
$doctor_bio    = wp_kses_post( get_theme_mod( 'jsma_doctor_bio', 'El Dr. Julio Santiago Marcelo es un gastroenterólogo con amplia experiencia en el diagnóstico y tratamiento de enfermedades del sistema digestivo y del hígado.' ) );
$email         = esc_html( get_theme_mod( 'jsma_email', 'contacto@juliosantiagomarcelo.com' ) );
?>

<!-- ═══════ HERO ═══════ -->
<header class="hero" id="hero">
  <div class="hero-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>
  <div class="hero-content">
    <div class="hero-badge" data-animate="fade-down">
      <span class="badge-dot"></span>
      Inscripciones Abiertas — Precio de Lanzamiento
    </div>
    <h1 data-animate="fade-up">
      Entienda y Controle su <span class="text-gradient">Cirrosis</span><br>
      Paso a Paso
    </h1>
    <p class="hero-subtitle" data-animate="fade-up" data-delay="100">
      Un programa claro y confiable, creado por el Dr. Julio Santiago Marcelo, para que usted y su familia aprendan a cuidar su hígado con un lenguaje sencillo y práctico.
    </p>
    <div class="hero-stats" data-animate="fade-up" data-delay="200">
      <div class="hero-stat">
        <span class="hero-stat-num">3</span>
        <span class="hero-stat-label">Módulos Base</span>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <span class="hero-stat-num">3</span>
        <span class="hero-stat-label">Bonos Exclusivos</span>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <span class="hero-stat-num">+</span>
        <span class="hero-stat-label">Clases en Vivo</span>
      </div>
    </div>
  </div>

  <div class="hero-cta-row" data-animate="fade-up" data-delay="300">
    <a href="#inscripcion" class="btn btn-gold btn-lg">
      <span>Quiero Inscribirme</span>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    <a href="#modulos" class="btn btn-ghost btn-lg">
      Conocer Más
    </a>
  </div>

  <div class="hero-trust-bar" data-animate="fade-up" data-delay="400">
    <span>&#128274; Pago Seguro con Mercado Pago</span>
    <span>&#128241; Desde su computadora o celular</span>
    <span>&#9989; Garantía de 7 Días</span>
  </div>
</header>

<!-- ═══════ PAIN POINTS ═══════ -->
<section class="section section-light" id="problema">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">¿Se identifica?</div>
    <h2 class="section-title" data-animate="fade-up">
      Vivir con cirrosis <span class="text-primary">no tiene por qué ser un misterio</span>
    </h2>

    <div class="pain-grid" data-animate="fade-up" data-delay="100">
      <div class="pain-card">
        <div class="pain-icon">&#128543;</div>
        <h3>Siente miedo y confusión</h3>
        <p>No sabe qué puede comer, qué medicamentos tomar o qué síntomas son señal de alarma. La información que encuentra es contradictoria.</p>
      </div>
      <div class="pain-card">
        <div class="pain-icon">&#127861;</div>
        <h3>No sabe qué alimentos son seguros</h3>
        <p>Cada persona le dice algo diferente sobre su dieta. Necesita una guía clara y práctica hecha por un especialista.</p>
      </div>
      <div class="pain-card">
        <div class="pain-icon">&#128138;</div>
        <h3>Dudas sobre su tratamiento</h3>
        <p>Las consultas son cortas y quedan muchas preguntas sin respuesta. Quisiera entender mejor su condición para cuidarse como corresponde.</p>
      </div>
      <div class="pain-card">
        <div class="pain-icon">&#128106;</div>
        <h3>Su familia también necesita saber</h3>
        <p>Sus seres queridos quieren apoyarlo pero no saben cómo. Este programa también los ayuda a acompañarlo mejor.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ TRANSFORMATION ═══════ -->
<section class="section section-dark">
  <div class="container">
    <div class="section-eyebrow light" data-animate="fade-up">La Solución</div>
    <h2 class="section-title light" data-animate="fade-up">
      Un programa diseñado por un <span class="text-gold">especialista en hígado</span><br>
      para que usted tome el control
    </h2>

    <div class="transform-grid" data-animate="fade-up" data-delay="100">
      <div class="transform-card">
        <div class="transform-before">
          <span class="transform-label">ANTES</span>
          <p>Confusión y miedo por información contradictoria</p>
        </div>
        <div class="transform-arrow">&#8594;</div>
        <div class="transform-after">
          <span class="transform-label">DESPUÉS</span>
          <p>Claridad y tranquilidad con un plan paso a paso</p>
        </div>
      </div>
      <div class="transform-card">
        <div class="transform-before">
          <span class="transform-label">ANTES</span>
          <p>No sabe qué comer ni qué evitar</p>
        </div>
        <div class="transform-arrow">&#8594;</div>
        <div class="transform-after">
          <span class="transform-label">DESPUÉS</span>
          <p>Guía práctica de alimentación para proteger su hígado</p>
        </div>
      </div>
      <div class="transform-card">
        <div class="transform-before">
          <span class="transform-label">ANTES</span>
          <p>Consultas cortas con muchas preguntas sin respuesta</p>
        </div>
        <div class="transform-arrow">&#8594;</div>
        <div class="transform-after">
          <span class="transform-label">DESPUÉS</span>
          <p>Videos y clases en vivo donde puede preguntarlo todo</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ MODULES (3 BASE) ═══════ -->
<section class="section section-light" id="modulos">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Programa del Curso</div>
    <h2 class="section-title" data-animate="fade-up">
      3 Módulos Base de <span class="text-primary">Alto Valor</span>
    </h2>
    <p class="section-subtitle" data-animate="fade-up">Explicados en un lenguaje claro, con videos profesionales y material descargable.</p>

    <div class="modules-grid" data-animate="fade-up" data-delay="100">
      <div class="module-card">
        <div class="module-number">01</div>
        <div class="module-icon">&#128216;</div>
        <h3>Descompensaciones Evitables</h3>
        <p>Errores diarios que ponen en riesgo tu cirrosis. Aprenda qué acciones cotidianas pueden desestabilizar su hígado y cómo evitarlas con cambios simples.</p>
        <div class="module-meta">
          <span class="module-tag">Video</span>
          <span class="module-tag">PDFs</span>
          <span class="module-tag">Quiz</span>
        </div>
      </div>
      <div class="module-card">
        <div class="module-number">02</div>
        <div class="module-icon">&#128215;</div>
        <h3>Nutrición Clave</h3>
        <p>Por qué la última comida antes de dormir es vital para prevenir descompensaciones. Guía de alimentación práctica para proteger su hígado cada día.</p>
        <div class="module-meta">
          <span class="module-tag">Video</span>
          <span class="module-tag">PDFs</span>
          <span class="module-tag">Quiz</span>
        </div>
      </div>
      <div class="module-card">
        <div class="module-number">03</div>
        <div class="module-icon">&#128213;</div>
        <h3>El Semáforo de la Cirrosis</h3>
        <p>Aprende a identificar cambios clave para que la familia sepa cuándo mantener la calma y cuándo actuar de inmediato. Un sistema visual de alerta temprana.</p>
        <div class="module-meta">
          <span class="module-tag">Video</span>
          <span class="module-tag">PDFs</span>
          <span class="module-tag">Quiz</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ BONOS ═══════ -->
<section class="section section-cream" id="bonos">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Regalos Incluidos</div>
    <h2 class="section-title" data-animate="fade-up">
      3 Bonos <span class="text-primary">Exclusivos</span>
    </h2>
    <p class="section-subtitle" data-animate="fade-up">Además de los 3 módulos base, usted recibe estos bonos de regalo con su inscripción.</p>

    <div class="bonos-grid" data-animate="fade-up" data-delay="100">
      <div class="bono-card">
        <div class="bono-ribbon">Bono 01</div>
        <div class="bono-icon">&#127857;</div>
        <h3>El Mapa de Alimentos</h3>
        <p>Qué poner en el plato para nutrir a un paciente con cirrosis. Una guía visual y práctica que simplifica las decisiones diarias de alimentación.</p>
      </div>
      <div class="bono-card">
        <div class="bono-ribbon">Bono 02</div>
        <div class="bono-icon">&#129657;</div>
        <h3>Signos Vitales Paso a Paso</h3>
        <p>Cómo medir y controlar la salud de tu familiar sin ser enfermera. Manual ilustrado con instrucciones claras para cada medición.</p>
      </div>
      <div class="bono-card">
        <div class="bono-ribbon">Bono 03</div>
        <div class="bono-icon">&#128221;</div>
        <h3>El Método Kardex</h3>
        <p>Aprende a crear un "Diario de Control" para nunca más confundirte con medicinas y horarios. Sistema probado por cuidadores reales.</p>
      </div>
    </div>

    <!-- Live Classes Card -->
    <div class="live-classes-card" data-animate="fade-up" data-delay="200">
      <div class="live-icon">&#128250;</div>
      <div class="live-info">
        <h3><span class="live-dot"></span>Clases en Vivo con el Dr. Julio</h3>
        <p>Acceso a sesiones en vivo donde puede hacer preguntas directamente al Dr. Julio Santiago Marcelo y recibir respuestas personalizadas sobre su caso.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ DOCTOR BIO ═══════ -->
<section class="section section-light" id="doctor">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Su Doctor</div>
    <h2 class="section-title" data-animate="fade-up">
      Conozca al <span class="text-primary">Dr. Julio Santiago Marcelo</span>
    </h2>

    <div class="doctor-card" data-animate="fade-up" data-delay="100">
      <div class="doctor-photo">
        <?php if ( $doctor_photo ) : ?>
          <img src="<?php echo $doctor_photo; ?>" alt="Dr. Julio Santiago Marcelo" class="doctor-photo-img">
        <?php else : ?>
          <div class="doctor-photo-placeholder">
            <span>&#128104;&#8205;&#9877;&#65039;</span>
            <small>Foto del Dr. Julio</small>
          </div>
        <?php endif; ?>
        <div class="doctor-badge-float">
          <span>15+</span>
          <small>Años de Experiencia</small>
        </div>
      </div>
      <div class="doctor-info">
        <h3>Dr. Julio Santiago Marcelo</h3>
        <p class="doctor-title">Gastroenterólogo</p>
        <p class="doctor-bio"><?php echo $doctor_bio; ?></p>
        <div class="doctor-credentials">
          <div class="credential">
            <div class="credential-icon">&#127975;</div>
            <div>
              <strong>Gastroenterólogo</strong>
              <span>Especialista en hígado</span>
            </div>
          </div>
          <div class="credential">
            <div class="credential-icon">&#128101;</div>
            <div>
              <strong>Cientos de Pacientes</strong>
              <span>Atendidos con cirrosis</span>
            </div>
          </div>
          <div class="credential">
            <div class="credential-icon">&#127760;</div>
            <div>
              <strong>juliosantiagomarcelo.com</strong>
              <span>Su espacio profesional</span>
            </div>
          </div>
          <div class="credential">
            <div class="credential-icon">&#128218;</div>
            <div>
              <strong>Programa Online</strong>
              <span>Para pacientes y familias</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ TESTIMONIALS ═══════ -->
<section class="section section-cream" id="testimonios">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Testimonios</div>
    <h2 class="section-title" data-animate="fade-up">
      Lo que dicen <span class="text-primary">sus estudiantes</span>
    </h2>
    <p class="section-subtitle" data-animate="fade-up">Personas que ya tomaron las clases y hoy viven mejor con su condición.</p>

    <?php if ( $vid_test ) : ?>
    <div class="testimonial-video-wrap" data-animate="fade-up" data-delay="100">
      <div class="video-container">
        <iframe src="https://player.vimeo.com/video/<?php echo $vid_test; ?>?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;dnt=1"
                allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                title="Testimonio de Paciente"></iframe>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════ PRICING / INSCRIPCIÓN ═══════ -->
<section class="section section-dark" id="inscripcion">
  <div class="container">
    <div class="section-eyebrow light" data-animate="fade-up">Inscripción</div>
    <h2 class="section-title light" data-animate="fade-up">
      Comience a cuidar su hígado <span class="text-gold">hoy mismo</span>
    </h2>

    <div class="pricing-section" data-animate="fade-up" data-delay="100">
      <div class="price-card">
        <div class="price-card-badge">&#128293; Precio de Lanzamiento</div>

        <div class="guarantee-badge-floating">
          <strong>7 DÍAS</strong>
          <span>Riesgo Cero</span>
        </div>

        <div class="price-card-content">
          <h3>Programa Completo para Pacientes con Cirrosis</h3>
          <div class="price-amount">
            <span class="price-currency">S/</span>
            <span class="price-value">247</span>
            <span class="price-period">PEN</span>
          </div>
          <p class="price-subtitle">
            <s style="color:rgba(255,255,255,0.3);">Precio regular S/ 297</s> &nbsp;&bull;&nbsp; Pago único — sin cuotas
          </p>

          <ul class="price-features">
            <li>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
              3 Módulos Base con videos y PDFs
            </li>
            <li>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
              3 Bonos Exclusivos de regalo
            </li>
            <li>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
              Acceso a Clases en Vivo con el Dr. Julio
            </li>
            <li>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
              Exámenes con explicación de las respuestas
            </li>
            <li>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
              Sección de Casos Reales (videos + PDFs)
            </li>
            <li>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
              Acceso por 6 meses al portal de estudiantes
            </li>
          </ul>

          <a href="<?php echo $mp_link; ?>" class="btn btn-gold btn-xl" id="mercadoPagoBtn" target="_blank" rel="noopener" style="width:100%;justify-content:center;">
            <span>Pagar con Mercado Pago</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>

          <div class="price-trust">
            <div class="trust-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
              Pago 100% seguro
            </div>
            <div class="trust-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              Datos protegidos
            </div>
            <div class="trust-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              Tarjeta o transferencia
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Guarantee -->
    <div class="guarantee-badge" data-animate="fade-up" data-delay="150">
      <div class="guarantee-seal">
        <span class="big">7</span>
        <span class="small">Días</span>
      </div>
      <div class="guarantee-text">
        <strong>Garantía de Riesgo Cero de 7 Días</strong>
        <span>Si en 7 días el programa no cumple sus expectativas, le devolvemos el 100% de su dinero. Sin preguntas, sin complicaciones.</span>
      </div>
    </div>

    <!-- Payment Methods -->
    <div class="payment-methods" data-animate="fade-up" data-delay="200">
      <span class="payment-label">Métodos de pago aceptados:</span>
      <div class="payment-icons">
        <div class="payment-icon-card">Visa</div>
        <div class="payment-icon-card">Mastercard</div>
        <div class="payment-icon-card">Mercado Pago</div>
        <div class="payment-icon-card">Transferencia</div>
        <div class="payment-icon-card">Yape / Plin</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ FAQ ═══════ -->
<section class="section section-light" id="faq">
  <div class="container container-narrow">
    <div class="section-eyebrow" data-animate="fade-up">Preguntas Frecuentes</div>
    <h2 class="section-title" data-animate="fade-up">
      Resolvemos sus <span class="text-primary">dudas</span>
    </h2>

    <div class="faq-list" data-animate="fade-up" data-delay="100">
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Para quién es este programa?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer"><p>Está diseñado para personas con cirrosis hepática y sus familiares. No necesita tener conocimientos médicos: el Dr. Julio explica todo en un lenguaje sencillo y claro.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Cuánto tiempo tengo acceso al contenido?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer"><p>Tiene acceso al portal de estudiantes durante 6 meses desde la fecha de inscripción. Durante ese tiempo puede ver y re-ver los videos las veces que necesite.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Cómo funciona la Garantía de 7 Días?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer"><p>Si dentro de los primeros 7 días siente que el programa no cumple con sus expectativas, simplemente escríbanos y le devolvemos el 100% de su dinero. Sin preguntas.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Puedo pagar en cuotas?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer"><p>Este programa se adquiere con un único pago. Por Mercado Pago puede pagar con tarjeta de crédito, débito, Yape, Plin o transferencia bancaria.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Cómo accedo al curso después de pagar?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer"><p>Inmediatamente después de confirmar su pago recibirá un email con su usuario, contraseña y el enlace al portal de estudiantes. Puede empezar de inmediato.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Puedo ver los videos desde mi celular?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer"><p>Sí. El portal funciona perfectamente en computadora, tablet y celular. Los videos están alojados en Vimeo con la máxima calidad y seguridad.</p></div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Este programa reemplaza a mi médico?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer"><p>No. Este programa es una guía educativa para ayudarle a entender mejor su condición. Siempre debe continuar con los controles y el tratamiento que le indique su médico tratante.</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ FINAL CTA ═══════ -->
<section class="section-cta">
  <div class="container" style="text-align:center;">
    <div class="cta-icon" data-animate="fade-up">&#9877;</div>
    <h2 class="section-title light" data-animate="fade-up">
      Tome el control de su salud<br>
      <span class="text-gold">hoy mismo</span>
    </h2>
    <p style="color:rgba(255,255,255,0.6);max-width:550px;margin:0 auto 2rem;font-size:1.05rem;" data-animate="fade-up" data-delay="100">
      Únase al programa del Dr. Julio Santiago Marcelo y comience a cuidar su hígado con la guía de un especialista.
    </p>
    <a href="<?php echo $mp_link; ?>" class="btn btn-gold btn-xl" data-animate="fade-up" data-delay="200" target="_blank" rel="noopener">
      <span>Inscribirme por S/ 247</span>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>
</section>

<!-- ═══════ STICKY MOBILE CTA ═══════ -->
<div class="sticky-cta" id="stickyCta">
  <a href="<?php echo $mp_link; ?>" class="btn btn-gold btn-sticky" target="_blank" rel="noopener">
    Inscribirme — S/ 247 →
  </a>
</div>

<?php get_footer(); ?>
