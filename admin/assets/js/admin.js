/* ============================================================
   RUS CAB — admin.js
   Admin portal JS: password toggles, auto-dismiss alerts
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Password show/hide ───────────────────────────────── */
  document.querySelectorAll('.a-eye-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.target);
      const icon  = btn.querySelector('i');
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

  /* ── Auto-dismiss alerts after 6s ────────────────────── */
  document.querySelectorAll('.a-alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.4s ease';
      alert.style.opacity    = '0';
      setTimeout(() => alert.remove(), 400);
    }, 6000);
  });

  /* ── Confirm on data-confirm elements ─────────────────── */
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

});