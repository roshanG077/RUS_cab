/* ============================================================
   RUS CAB — driver.js
   Driver portal JavaScript: password toggles, UX helpers
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Password show/hide ───────────────────────────────── */
  document.querySelectorAll('.d-eye-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.dataset.target;
      const input    = document.getElementById(targetId);
      const icon     = btn.querySelector('i');
      if (!input) return;

      if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'fas fa-eye-slash';
      } else {
        input.type     = 'password';
        icon.className = 'fas fa-eye';
      }
    });
  });

  /* ── OTP input: digits only, auto-advance ─────────────── */
  document.querySelectorAll('.d-otp-input').forEach(input => {
    input.addEventListener('input', e => {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
    });
  });

  /* ── Confirm dialogs for cancel buttons ───────────────── */
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  /* ── Auto-dismiss alerts after 5s ────────────────────── */
  document.querySelectorAll('.d-alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s';
      alert.style.opacity    = '0';
      setTimeout(() => alert.remove(), 400);
    }, 5000);
  });

});