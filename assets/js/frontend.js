/**
 * Before After My Care – Frontend JS
 * Handles show/hide password toggle on registration form and survey modal.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    // ── Password visibility toggle ────────────────────────────
    document.querySelectorAll('.bam-toggle-pass').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetId = btn.getAttribute('data-target');
        var input = document.getElementById(targetId);
        if (!input) return;

        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.setAttribute('aria-label', isPassword ? 'Ocultar contraseña' : 'Mostrar contraseña');
      });
    });

    // ── Inline password strength hint ────────────────────────
    var passwordInput = document.getElementById('bam_password');
    if (passwordInput) {
      var hint = document.createElement('div');
      hint.className = 'bam-strength-hint';
      hint.setAttribute('aria-live', 'polite');
      passwordInput.closest('.bam-field-half').appendChild(hint);

      passwordInput.addEventListener('input', function () {
        var val = passwordInput.value;
        var strength = 0;
        if (val.length >= 8) strength++;
        if (/[A-Z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;

        var labels = ['', 'Débil', 'Regular', 'Buena', 'Fuerte'];
        var colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#16a34a'];

        hint.textContent = val.length > 0 ? 'Seguridad: ' + (labels[strength] || labels[1]) : '';
        hint.style.color = colors[strength] || colors[1];
        hint.style.fontSize = '0.78rem';
        hint.style.marginTop = '4px';
      });
    }

    // ── Survey modal ──────────────────────────────────────────
    var openBtn    = document.getElementById('bam-survey-open');
    var modal      = document.getElementById('bam-survey-modal');
    var closeBtn   = document.getElementById('bam-survey-close');
    var cancelBtn  = document.getElementById('bam-survey-cancel');
    var backdrop   = document.getElementById('bam-survey-backdrop');

    if (!openBtn || !modal) return;

    function openModal() {
      modal.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
      var firstInput = modal.querySelector('input, textarea, select, button');
      if (firstInput) firstInput.focus();
    }

    function closeModal() {
      modal.setAttribute('hidden', '');
      document.body.style.overflow = '';
      openBtn.focus();
    }

    openBtn.addEventListener('click', openModal);

    if (closeBtn)  closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop)  backdrop.addEventListener('click', closeModal);

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hasAttribute('hidden')) {
        closeModal();
      }
    });

    // If there are validation errors, re-open the modal on page load
    var hasErrors = modal && modal.querySelector('.bam-notice-error, .bam-field-error');
    if (hasErrors) {
      openModal();
    }
  });
})();
