/**
 * JulioSM Main — Interactive Features
 */
document.addEventListener('DOMContentLoaded', () => {

  /* ─── Navbar Scroll ─── */
  const navbar = document.getElementById('navbar');
  const handleNavScroll = () => {
    if (window.scrollY > 80) {
      navbar && navbar.classList.add('scrolled');
    } else {
      navbar && navbar.classList.remove('scrolled');
    }
  };
  window.addEventListener('scroll', handleNavScroll, { passive: true });
  handleNavScroll();

  /* ─── Mobile Nav Toggle ─── */
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      navToggle.classList.toggle('active');
    });
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('open');
        navToggle.classList.remove('active');
      });
    });
  }

  /* ─── Smooth Scroll ─── */
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

  /* ─── FAQ Accordion ─── */
  document.querySelectorAll('.faq-item').forEach(item => {
    const question = item.querySelector('.faq-question');
    if (!question) return;
    question.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => {
        i.classList.remove('open');
        const q = i.querySelector('.faq-question');
        if (q) q.setAttribute('aria-expanded', 'false');
      });
      if (!isOpen) {
        item.classList.add('open');
        question.setAttribute('aria-expanded', 'true');
      }
    });
  });

  /* ─── Scroll Animations ─── */
  const animateElements = document.querySelectorAll('[data-animate]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const delay = parseInt(el.dataset.delay || '0', 10);
        setTimeout(() => el.classList.add('animated'), delay);
        observer.unobserve(el);
      }
    });
  }, { rootMargin: '0px 0px -60px 0px', threshold: 0.1 });
  animateElements.forEach(el => observer.observe(el));

  /* ─── Sticky CTA (Mobile) ─── */
  const stickyCta = document.getElementById('stickyCta');
  const hero = document.getElementById('hero');
  if (stickyCta && hero) {
    const stickyObs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting && window.scrollY > 500) {
          stickyCta.classList.add('visible');
        } else {
          stickyCta.classList.remove('visible');
        }
      });
    }, { threshold: 0 });
    stickyObs.observe(hero);
  }

  /* ─── Contact Form AJAX ─── */
  const form = document.getElementById('contactForm');
  const resp = document.getElementById('formResponse');
  if (form && typeof juliosmData !== 'undefined') {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Enviando...';

      const data = new FormData(form);
      data.append('action', 'juliosm_contact');
      data.append('nonce', juliosmData.nonce);

      try {
        const res = await fetch(juliosmData.ajaxUrl, { method: 'POST', body: data });
        const json = await res.json();
        resp.style.display = 'block';
        if (json.success) {
          resp.style.color = '#059669';
          resp.textContent = json.data.message;
          form.reset();
        } else {
          resp.style.color = '#DC2626';
          resp.textContent = json.data.message;
        }
      } catch {
        resp.style.display = 'block';
        resp.style.color = '#DC2626';
        resp.textContent = 'Error de conexión. Intente nuevamente.';
      }
      btn.disabled = false;
      btn.textContent = 'Enviar Mensaje';
    });
  }
});
