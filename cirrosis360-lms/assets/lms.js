(function () {
    'use strict';

    // Sidebar drawer (mobile)
    var sidebar = document.getElementById('c360-sidebar');
    var toggle = document.querySelector('.c360-sidebar-toggle');
    var closeBtn = document.querySelector('.c360-sidebar-close');
    var backdrop = document.querySelector('.c360-sidebar-backdrop');
    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('is-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
        if (backdrop) backdrop.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('is-open');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        if (backdrop) backdrop.hidden = true;
        document.body.style.overflow = '';
    }
    if (toggle) toggle.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSidebar(); });


    function ajax(action, data) {
        var fd = new FormData();
        fd.append('action', action);
        fd.append('nonce', c360LMS.nonce);
        Object.keys(data || {}).forEach(function (k) {
            if (Array.isArray(data[k])) {
                data[k].forEach(function (v, i) { fd.append(k + '[' + i + ']', v); });
            } else { fd.append(k, data[k]); }
        });
        return fetch(c360LMS.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function (r) { return r.json(); });
    }

    // Mark lesson complete (bono)
    document.querySelectorAll('[data-c360-complete]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var slug = btn.getAttribute('data-c360-complete');
            btn.disabled = true;
            btn.textContent = 'Guardando…';
            ajax('c360_lesson_complete', { slug: slug }).then(function (r) {
                if (r && r.success) {
                    btn.textContent = '✓ Marcada como completa';
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Reintentar';
                }
            });
        });
    });

    // Quiz submission
    var qf = document.getElementById('c360-quiz-form');
    if (qf) {
        qf.addEventListener('submit', function (e) {
            e.preventDefault();
            var slug = qf.getAttribute('data-slug');
            var fd = new FormData(qf);
            var answers = [];
            qf.querySelectorAll('fieldset.c360-q').forEach(function (fs, i) {
                var v = fd.get('answers[' + i + ']');
                answers[i] = v == null ? -1 : parseInt(v, 10);
            });

            var btn = qf.querySelector('button[type=submit]');
            btn.disabled = true;
            btn.textContent = 'Calificando…';

            ajax('c360_quiz_submit', { slug: slug, answers: answers }).then(function (r) {
                btn.disabled = false;
                btn.textContent = 'Enviar respuestas';
                var box = document.getElementById('c360-quiz-result');
                if (!r || !r.success) {
                    box.hidden = false;
                    box.className = 'c360-quiz-result fail';
                    box.innerHTML = '<h2>Error</h2><p>' + ((r && r.data && r.data.msg) || 'No se pudo enviar.') + '</p>';
                    return;
                }
                var d = r.data;
                box.hidden = false;
                box.className = 'c360-quiz-result ' + (d.passed ? 'pass' : 'fail');
                box.innerHTML =
                    '<h2>' + (d.passed ? '¡Aprobado!' : 'Sigue practicando') + '</h2>' +
                    '<div class="score-big">' + d.score + '%</div>' +
                    '<p>Mínimo para aprobar: ' + d.pass_score + '%</p>' +
                    (d.passed
                        ? '<p><a class="c360-btn c360-btn-primary" href="' + d.redirect + '">Volver al Dashboard</a></p>'
                        : '<p><button class="c360-btn c360-btn-secondary" onclick="location.reload()">Intentar de nuevo</button></p>');
                window.scrollTo({ top: box.offsetTop - 80, behavior: 'smooth' });
            });
        });
    }
})();
