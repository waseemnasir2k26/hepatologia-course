/* ═══════════════════════════════════════════════════════
   HEPATOLOGÍA COURSE — INTERACTIVE FEATURES
   ═══════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  // ─── NAVBAR SCROLL ───
  const navbar = document.getElementById('navbar');
  const hero = document.getElementById('hero');

  const handleNavScroll = () => {
    if (window.scrollY > 80) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  };
  window.addEventListener('scroll', handleNavScroll, { passive: true });
  handleNavScroll();

  // ─── MOBILE NAV TOGGLE ───
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      navToggle.classList.toggle('active');
    });

    // Close mobile nav on link click
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('open');
        navToggle.classList.remove('active');
      });
    });
  }

  // ─── SMOOTH SCROLL ───
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = 80;
        const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  // ─── FAQ ACCORDION ───
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');
    question.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');

      // Close all
      faqItems.forEach(i => i.classList.remove('open'));
      faqItems.forEach(i => i.querySelector('.faq-question').setAttribute('aria-expanded', 'false'));

      // Open clicked (if wasn't already open)
      if (!isOpen) {
        item.classList.add('open');
        question.setAttribute('aria-expanded', 'true');
      }
    });
  });

  // ─── SCROLL ANIMATIONS (Intersection Observer) ───
  const animateElements = document.querySelectorAll('[data-animate]');

  const observerOptions = {
    root: null,
    rootMargin: '0px 0px -60px 0px',
    threshold: 0.1
  };

  const animateObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const delay = parseInt(el.dataset.delay || '0', 10);
        setTimeout(() => {
          el.classList.add('animated');
        }, delay);
        animateObserver.unobserve(el);
      }
    });
  }, observerOptions);

  animateElements.forEach(el => animateObserver.observe(el));

  // ─── STICKY CTA (Mobile) ───
  const stickyCta = document.getElementById('stickyCta');
  const inscripcionSection = document.getElementById('inscripcion');

  if (stickyCta && inscripcionSection) {
    const stickyObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        // Show sticky when hero is out of view, hide when pricing is visible
        if (!entry.isIntersecting && window.scrollY > 600) {
          stickyCta.classList.add('visible');
        }
      });
    }, { threshold: 0 });

    stickyObserver.observe(hero);

    // Hide sticky when pricing section is visible
    const pricingObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          stickyCta.classList.remove('visible');
        } else if (window.scrollY > 600) {
          stickyCta.classList.add('visible');
        }
      });
    }, { threshold: 0.3 });

    pricingObserver.observe(inscripcionSection);
  }

  // ─── VIDEO PLAY BUTTON ───
  const playBtn = document.getElementById('playBtn');
  const vslVideo = document.getElementById('vslVideo');

  if (playBtn && vslVideo) {
    playBtn.addEventListener('click', () => {
      // Replace placeholder with Vimeo embed
      // For demo, show a message. In production, replace with actual Vimeo ID:
      const placeholder = vslVideo.querySelector('.video-placeholder');
      if (placeholder) {
        placeholder.innerHTML = `
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:#0d1b2a;color:rgba(255,255,255,0.5);font-family:'Montserrat',sans-serif;font-size:0.85rem;text-align:center;padding:2rem;">
            <div>
              <div style="font-size:2.5rem;margin-bottom:1rem;">▶</div>
              <p>Vimeo video se cargará aquí</p>
              <p style="font-size:0.7rem;margin-top:0.5rem;color:rgba(255,255,255,0.3);">Reemplace con: &lt;iframe src="https://player.vimeo.com/video/VIDEO_ID"&gt;</p>
            </div>
          </div>
        `;
      }
      /* Production code:
      placeholder.outerHTML = `
        <iframe src="https://player.vimeo.com/video/VIDEO_ID?autoplay=1&badge=0&autopause=0"
                frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen
                style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>
      `;
      */
    });
  }

  // ─── MERCADO PAGO BUTTON ───
  const mpBtn = document.getElementById('mercadoPagoBtn');
  if (mpBtn) {
    mpBtn.addEventListener('click', (e) => {
      e.preventDefault();
      // In production, redirect to Mercado Pago checkout URL:
      // window.location.href = 'https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=PREFERENCE_ID';
      alert('Botón de Mercado Pago — Conectar con pasarela de pago real en producción.');
    });
  }

  // ─── MODULE CARDS STAGGER ───
  const moduleCards = document.querySelectorAll('.module-card');
  moduleCards.forEach((card, i) => {
    card.style.transitionDelay = `${i * 80}ms`;
  });

  // ─── COUNTER ANIMATION ───
  const counters = document.querySelectorAll('.summary-num');
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const text = el.textContent.trim();
        const num = parseInt(text, 10);

        if (!isNaN(num) && num > 0) {
          let current = 0;
          const step = Math.ceil(num / 40);
          const suffix = text.replace(/[0-9]/g, '');

          const timer = setInterval(() => {
            current += step;
            if (current >= num) {
              current = num;
              clearInterval(timer);
            }
            el.textContent = current + suffix;
          }, 30);
        }
        counterObserver.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(c => counterObserver.observe(c));

});
