<?php
/**
 * Template Name: Página Principal
 * Front page template — one-page medical website
 */
get_header();

$portal_url    = esc_url( get_theme_mod( 'juliosm_portal_url', 'https://portal.juliosantiagomarcelo.com' ) );
$mp_link       = esc_url( get_theme_mod( 'juliosm_mercadopago', 'https://mpago.la/1sXKiM3' ) );
$specialty     = esc_html( get_theme_mod( 'juliosm_specialty', 'Gastroenterólogo' ) );
$bio           = wp_kses_post( get_theme_mod( 'juliosm_bio', 'El Dr. Julio Santiago Marcelo es un gastroenterólogo con amplia experiencia en el diagnóstico y tratamiento de enfermedades del sistema digestivo y del hígado. Con años de dedicación a sus pacientes, ha desarrollado un programa educativo único para personas que viven con cirrosis hepática.' ) );
$bio_2         = wp_kses_post( get_theme_mod( 'juliosm_bio_2', 'Su compromiso con la educación del paciente lo llevó a crear este programa, donde explica conceptos médicos complejos en un lenguaje claro y accesible, para que cada familia pueda tomar mejores decisiones sobre su salud.' ) );
$hospital      = esc_html( get_theme_mod( 'juliosm_hospital', 'Hospital / Clínica' ) );
$university    = esc_html( get_theme_mod( 'juliosm_university', 'Universidad' ) );
$years         = esc_html( get_theme_mod( 'juliosm_years', '15+' ) );
$doctor_photo  = esc_url( get_theme_mod( 'juliosm_photo', '' ) );
$email         = esc_html( get_theme_mod( 'juliosm_email', 'contacto@juliosantiagomarcelo.com' ) );
$phone         = esc_html( get_theme_mod( 'juliosm_phone', '' ) );
$address       = esc_html( get_theme_mod( 'juliosm_address', 'Lima, Perú' ) );
$whatsapp      = esc_url( get_theme_mod( 'juliosm_whatsapp', '' ) );
$testimonial_v = esc_attr( get_theme_mod( 'juliosm_testimonial_vimeo', '1182155574' ) );
?>

<!-- ═══════ HERO ═══════ -->
<header class="hero" id="hero">
  <div class="hero-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>
  <div class="hero-content" data-animate="fade-up">
    <div class="hero-badge">
      <span class="badge-dot"></span>
      Programa Educativo Disponible
    </div>
    <h1>
      Dr. Julio Santiago<br>
      <span class="text-gradient">Marcelo</span>
    </h1>
    <p class="hero-subtitle">
      <?php echo $specialty; ?> — Especialista en enfermedades del hígado y sistema digestivo. Programa educativo online para pacientes con cirrosis y sus familias.
    </p>
    <div class="hero-cta-row" data-animate="fade-up" data-delay="200">
      <a href="<?php echo $portal_url; ?>" class="btn btn-gold btn-lg" target="_blank" rel="noopener">
        <span>Conozca el Programa</span>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="#contacto" class="btn btn-ghost btn-lg">
        Agendar Consulta
      </a>
    </div>
    <div class="hero-trust-bar" data-animate="fade-up" data-delay="400">
      <span>&#9877; <?php echo $specialty; ?></span>
      <span>&#127891; <?php echo $years; ?> Años de Experiencia</span>
      <span>&#128218; Programa para Pacientes</span>
    </div>
  </div>
</header>

<!-- ═══════ ABOUT / BIO ═══════ -->
<section class="section section-light" id="sobre">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Su Doctor</div>
    <h2 class="section-title" data-animate="fade-up">
      Conozca al <span class="text-primary">Dr. Julio Santiago Marcelo</span>
    </h2>

    <div class="doctor-card" data-animate="fade-up" data-delay="100">
      <div class="doctor-photo">
        <?php if ( $doctor_photo ) : ?>
          <img src="<?php echo $doctor_photo; ?>" alt="Dr. Julio Santiago Marcelo" style="width:100%;border-radius:var(--radius-xl);box-shadow:var(--shadow-xl);">
        <?php else : ?>
          <div class="doctor-photo-placeholder">
            <span>&#128104;&#8205;&#9877;&#65039;</span>
            <small>Foto del Dr. Julio</small>
          </div>
        <?php endif; ?>
        <div class="doctor-badge-float">
          <span><?php echo $years; ?></span>
          <small>Años de Experiencia</small>
        </div>
      </div>

      <div class="doctor-info">
        <h3>Dr. Julio Santiago Marcelo</h3>
        <p class="doctor-title"><?php echo $specialty; ?></p>
        <p class="doctor-bio"><?php echo $bio; ?></p>
        <p class="doctor-bio"><?php echo $bio_2; ?></p>

        <div class="doctor-credentials">
          <div class="credential">
            <div class="credential-icon">&#127975;</div>
            <div>
              <strong><?php echo $hospital; ?></strong>
              <span>Consulta de Gastroenterología</span>
            </div>
          </div>
          <div class="credential">
            <div class="credential-icon">&#127891;</div>
            <div>
              <strong><?php echo $university; ?></strong>
              <span>Formación especializada</span>
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
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ SERVICES ═══════ -->
<section class="section section-cream" id="servicios">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Especialidades</div>
    <h2 class="section-title" data-animate="fade-up">
      Servicios <span class="text-primary">Médicos</span>
    </h2>
    <p class="section-subtitle" data-animate="fade-up">
      Atención integral del sistema digestivo con enfoque en enfermedades hepáticas.
    </p>

    <div class="services-grid" data-animate="fade-up" data-delay="100">
      <div class="service-card">
        <div class="service-icon">&#129728;</div>
        <h3>Consulta de Gastroenterología</h3>
        <p>Evaluación completa del sistema digestivo. Diagnóstico y tratamiento de enfermedades gastrointestinales con enfoque personalizado.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">&#128269;</div>
        <h3>Endoscopía Digestiva</h3>
        <p>Procedimientos diagnósticos y terapéuticos del tracto digestivo superior e inferior con tecnología de última generación.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">&#129516;</div>
        <h3>Hepatología</h3>
        <p>Especialista en enfermedades del hígado: cirrosis, hepatitis, hígado graso. Seguimiento integral y planes de manejo personalizados.</p>
      </div>
      <div class="service-card">
        <div class="service-icon">&#128218;</div>
        <h3>Programa Educativo</h3>
        <p>Curso online para pacientes con cirrosis y sus familias. Aprenda a cuidar su hígado con un lenguaje claro y práctico.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ COURSE PROMO ═══════ -->
<section class="course-promo" id="programa">
  <div class="container">
    <div class="section-eyebrow light" data-animate="fade-up">Programa Online</div>
    <h2 class="section-title light" data-animate="fade-up">
      Programa para Pacientes con <span class="text-gold">Cirrosis</span>
    </h2>
    <p class="section-subtitle" data-animate="fade-up" style="color:rgba(255,255,255,0.6);">
      Un programa claro y confiable para que usted y su familia aprendan a cuidar su hígado, paso a paso.
    </p>

    <!-- Modules -->
    <div class="modules-grid" data-animate="fade-up" data-delay="100">
      <div class="module-card">
        <div class="module-number">01</div>
        <h3>Descompensaciones Evitables</h3>
        <p>Errores diarios que ponen en riesgo tu cirrosis. Aprenda a identificarlos y evitarlos para mantener su hígado estable.</p>
        <div class="module-meta">
          <span class="module-tag">Video</span>
          <span class="module-tag">PDFs</span>
          <span class="module-tag">Quiz</span>
        </div>
      </div>
      <div class="module-card">
        <div class="module-number">02</div>
        <h3>Nutrición Clave</h3>
        <p>Por qué la última comida antes de dormir es vital para prevenir descompensaciones. Guía práctica de alimentación.</p>
        <div class="module-meta">
          <span class="module-tag">Video</span>
          <span class="module-tag">PDFs</span>
          <span class="module-tag">Quiz</span>
        </div>
      </div>
      <div class="module-card">
        <div class="module-number">03</div>
        <h3>El Semáforo de la Cirrosis</h3>
        <p>Aprende a identificar cambios clave para que la familia sepa cuándo mantener la calma y cuándo actuar de inmediato.</p>
        <div class="module-meta">
          <span class="module-tag">Video</span>
          <span class="module-tag">PDFs</span>
          <span class="module-tag">Quiz</span>
        </div>
      </div>
    </div>

    <!-- Bonos -->
    <div style="margin-top:3rem;">
      <div class="section-eyebrow light" data-animate="fade-up" style="text-align:center;">Regalos Incluidos</div>
      <h3 style="text-align:center;color:var(--white);font-family:'Playfair Display',serif;font-size:1.75rem;margin-bottom:2rem;" data-animate="fade-up">
        3 Bonos <span class="text-gold">Exclusivos</span>
      </h3>
    </div>

    <div class="bonos-grid" data-animate="fade-up" data-delay="100">
      <div class="bono-card">
        <div class="bono-ribbon">Bono 01</div>
        <h3>El Mapa de Alimentos</h3>
        <p>Qué poner en el plato para nutrir a un paciente con cirrosis. Guía visual y práctica.</p>
      </div>
      <div class="bono-card">
        <div class="bono-ribbon">Bono 02</div>
        <h3>Signos Vitales Paso a Paso</h3>
        <p>Cómo medir y controlar la salud de tu familiar sin ser enfermera. Manual ilustrado.</p>
      </div>
      <div class="bono-card">
        <div class="bono-ribbon">Bono 03</div>
        <h3>El Método Kardex</h3>
        <p>Aprende a crear un "Diario de Control" para nunca más confundirte con medicinas y horarios.</p>
      </div>
    </div>

    <!-- Pricing -->
    <div class="price-card" data-animate="fade-up" data-delay="150">
      <div class="price-card-badge">&#128293; Precio de Lanzamiento</div>
      <h3 style="color:var(--white);font-family:'Inter',sans-serif;font-size:1.1rem;font-weight:600;">Programa Completo para Pacientes con Cirrosis</h3>
      <div class="price-amount">
        <span class="price-currency">S/</span>
        <span class="price-value">247</span>
        <span class="price-period">PEN</span>
      </div>
      <p class="price-subtitle">
        <s style="color:rgba(255,255,255,0.3);">Precio regular S/ 297</s> &nbsp;&bull;&nbsp; Pago único
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
          Acceso a Clases en Vivo con el Doctor
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
          Exámenes con explicación de respuestas
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
          Sección de Casos Reales
        </li>
        <li>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
          Acceso por 6 meses al portal
        </li>
      </ul>

      <a href="<?php echo $mp_link; ?>" class="btn btn-gold btn-xl" target="_blank" rel="noopener" style="width:100%;justify-content:center;">
        <span>Inscribirme con Mercado Pago</span>
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
          Yape / Plin / Tarjeta
        </div>
      </div>
    </div>

    <!-- Guarantee -->
    <div class="guarantee-badge" data-animate="fade-up" data-delay="200">
      <div class="guarantee-seal">
        <span class="big">7</span>
        <span class="small">Días</span>
      </div>
      <div class="guarantee-text">
        <strong>Garantía de Riesgo Cero de 7 Días</strong>
        <span>Si en 7 días el programa no cumple sus expectativas, le devolvemos el 100% de su dinero. Sin preguntas, sin complicaciones.</span>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ TESTIMONIALS ═══════ -->
<section class="section section-light" id="testimonios">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Testimonios</div>
    <h2 class="section-title" data-animate="fade-up">
      Lo que dicen <span class="text-primary">los pacientes</span>
    </h2>
    <p class="section-subtitle" data-animate="fade-up">Personas que ya tomaron las clases y hoy viven mejor con su condición.</p>

    <?php if ( $testimonial_v ) : ?>
    <div class="testimonial-video-wrap" data-animate="fade-up" data-delay="100">
      <div class="video-container">
        <iframe src="https://player.vimeo.com/video/<?php echo $testimonial_v; ?>?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;dnt=1"
                allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                title="Testimonio de Paciente"></iframe>
      </div>
    </div>
    <?php endif; ?>

    <div class="testimonials-grid" data-animate="fade-up" data-delay="200">
      <div class="testimonial-card">
        <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <p class="testimonial-text">"El programa del Dr. Julio me ayudó a entender mi condición como nunca antes. Ahora sé qué comer, qué evitar y cuándo preocuparme. Mi familia también se siente más tranquila."</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar">P1</div>
          <div>
            <strong>Paciente Verificado</strong>
            <span>Perú</span>
          </div>
        </div>
      </div>
      <div class="testimonial-card">
        <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <p class="testimonial-text">"Las explicaciones son claras y prácticas. El Semáforo de la Cirrosis me enseñó cuándo llevar a mi esposo al hospital y cuándo puedo manejar la situación en casa."</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar">P2</div>
          <div>
            <strong>Familiar de Paciente</strong>
            <span>Perú</span>
          </div>
        </div>
      </div>
      <div class="testimonial-card">
        <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <p class="testimonial-text">"El Método Kardex cambió nuestra rutina. Ya no nos confundimos con las medicinas y los horarios. Lo recomiendo a todas las familias que cuidan a un paciente con cirrosis."</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar">P3</div>
          <div>
            <strong>Cuidador Familiar</strong>
            <span>Perú</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ FAQ ═══════ -->
<section class="section section-cream" id="faq">
  <div class="container container-narrow">
    <div class="section-eyebrow" data-animate="fade-up">Preguntas Frecuentes</div>
    <h2 class="section-title" data-animate="fade-up">
      Resolvemos sus <span class="text-primary">dudas</span>
    </h2>

    <div class="faq-list" data-animate="fade-up" data-delay="100">
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Para quién es el programa educativo?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <p>Está diseñado para personas con cirrosis hepática y sus familiares. No necesita conocimientos médicos: el Dr. Julio explica todo en un lenguaje sencillo y claro.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Cuánto tiempo tengo acceso al contenido?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <p>Tiene acceso al portal de estudiantes durante 6 meses desde la fecha de inscripción. Durante ese tiempo puede ver los videos las veces que necesite.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Cómo funciona la Garantía de 7 Días?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <p>Si dentro de los primeros 7 días siente que el programa no cumple con sus expectativas, escríbanos y le devolvemos el 100% de su dinero. Sin preguntas y sin complicaciones.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Puedo pagar en cuotas?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <p>El programa se adquiere con un único pago. Por Mercado Pago puede pagar con tarjeta de crédito, débito, Yape, Plin o transferencia bancaria.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Cómo accedo al curso después de pagar?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <p>Después de confirmar su pago recibirá un email con su usuario, contraseña y el enlace al portal de estudiantes. Puede empezar de inmediato.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Puedo ver los videos desde mi celular?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <p>Sí. El portal funciona perfectamente en computadora, tablet y celular. Los videos están alojados en Vimeo con la máxima calidad y seguridad.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" aria-expanded="false">
          <span>¿Este programa reemplaza a mi médico?</span>
          <svg class="faq-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="faq-answer">
          <p>No. Este programa es una guía educativa para ayudarle a entender mejor su condición. Siempre debe continuar con los controles y el tratamiento que le indique su médico tratante.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ CONTACT ═══════ -->
<section class="section section-light" id="contacto">
  <div class="container">
    <div class="section-eyebrow" data-animate="fade-up">Contacto</div>
    <h2 class="section-title" data-animate="fade-up">
      Comuníquese con el <span class="text-primary">Doctor</span>
    </h2>

    <div class="contact-grid" data-animate="fade-up" data-delay="100">
      <div class="contact-info-list">
        <?php if ( $email ) : ?>
        <div class="contact-item">
          <div class="contact-icon">&#9993;</div>
          <div>
            <strong>Email</strong>
            <span><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></span>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $phone ) : ?>
        <div class="contact-item">
          <div class="contact-icon">&#128222;</div>
          <div>
            <strong>Teléfono</strong>
            <span><?php echo $phone; ?></span>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $address ) : ?>
        <div class="contact-item">
          <div class="contact-icon">&#128205;</div>
          <div>
            <strong>Ubicación</strong>
            <span><?php echo $address; ?></span>
          </div>
        </div>
        <?php endif; ?>

        <?php if ( $whatsapp ) : ?>
        <div class="contact-item">
          <div class="contact-icon">&#128172;</div>
          <div>
            <strong>WhatsApp</strong>
            <span><a href="<?php echo $whatsapp; ?>" target="_blank" rel="noopener">Enviar mensaje directo</a></span>
          </div>
        </div>
        <?php endif; ?>

        <div class="contact-item" style="margin-top:1rem;">
          <a href="<?php echo $portal_url; ?>" class="btn btn-primary" target="_blank" rel="noopener">
            Ver el Programa Educativo →
          </a>
        </div>
      </div>

      <div class="contact-form">
        <h3 style="font-family:'Inter',sans-serif;font-size:1.15rem;font-weight:700;margin-bottom:1.5rem;">Envíe un Mensaje</h3>
        <form id="contactForm">
          <div class="form-group">
            <label for="cf-name">Nombre Completo *</label>
            <input type="text" id="cf-name" name="name" required placeholder="Su nombre">
          </div>
          <div class="form-group">
            <label for="cf-email">Email *</label>
            <input type="email" id="cf-email" name="email" required placeholder="Su correo electrónico">
          </div>
          <div class="form-group">
            <label for="cf-phone">Teléfono</label>
            <input type="tel" id="cf-phone" name="phone" placeholder="Opcional">
          </div>
          <div class="form-group">
            <label for="cf-message">Mensaje *</label>
            <textarea id="cf-message" name="message" required placeholder="¿En qué podemos ayudarle?"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;">
            Enviar Mensaje
          </button>
          <div id="formResponse" style="margin-top:1rem;font-size:0.9rem;display:none;"></div>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ FINAL CTA ═══════ -->
<section class="section-cta">
  <div class="container">
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
