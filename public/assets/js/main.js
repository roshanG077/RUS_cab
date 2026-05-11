/* ============================================================
   RUS CAB — main.js
   Global JS for all public + driver pages
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Navbar: add .scrolled class on scroll ─────────────── */
  const navbar = document.getElementById('navbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // run once on load
  }

  /* ── Hamburger + Mobile Nav toggle ────────────────────── */
  const hamburger = document.getElementById('hamburger');
  const mobileNav = document.getElementById('mobileNav');
  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', () => {
      const isOpen = mobileNav.classList.toggle('open');
      hamburger.classList.toggle('open', isOpen);
      hamburger.setAttribute('aria-expanded', isOpen);
    });

    // Close mobile nav when any link inside it is clicked
    mobileNav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        mobileNav.classList.remove('open');
        hamburger.classList.remove('open');
        hamburger.setAttribute('aria-expanded', false);
      });
    });
  }

  /* ── Active nav link highlight ─────────────────────────── */
  const currentPath = window.location.pathname.split('/').pop();
  document.querySelectorAll('.navbar-links a').forEach(link => {
    const linkPath = link.getAttribute('href').split('/').pop().split('#')[0];
    if (linkPath && linkPath === currentPath) {
      link.classList.add('active');
    }
  });

  /* ── Password show / hide toggle ───────────────────────── */
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.closest('.input-password-wrapper').querySelector('input');
      const icon  = btn.querySelector('i');
      if (input.type === 'password') {
        input.type   = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type   = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    });
  });

  /* ── Scroll reveal (.reveal elements) ──────────────────── */
  const revealElements = document.querySelectorAll('.reveal');
  if (revealElements.length > 0 && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target); // fire once
        }
      });
    }, { threshold: 0.12 });

    revealElements.forEach(el => observer.observe(el));
  }

  /* ── Alert auto-dismiss after 5 seconds ─────────────────── */
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity    = '0';
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });

  /* ── Smooth scroll for anchor links ─────────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = 80; // account for fixed navbar height
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  /* ── OTP input: auto-advance to next field ──────────────── */
  const otpInputs = document.querySelectorAll('.otp-input');
  if (otpInputs.length > 0) {
    otpInputs.forEach((input, index) => {
      input.addEventListener('input', () => {
        if (input.value.length === 1 && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }
      });
      input.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && input.value === '' && index > 0) {
          otpInputs[index - 1].focus();
        }
      });
    });
  }

  /* ── Payment method selection ───────────────────────────── */
  document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', () => {
      document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
      option.classList.add('selected');
      // Set hidden input value if present
      const hiddenInput = document.querySelector('input[name="payment_method"]');
      if (hiddenInput) hiddenInput.value = option.dataset.method ?? '';
    });
  });

});

/* ── Booking Tabs (Ride Now / Schedule) ─────────────────── */
document.querySelectorAll('.booking-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.booking-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    const scheduleFields = document.getElementById('scheduleFields');
    if (scheduleFields) {
      scheduleFields.style.display = tab.dataset.tab === 'schedule' ? 'block' : 'none';
    }
  });
});

/* ── Cab Type Selector ──────────────────────────────────── */
document.querySelectorAll('.cab-type-option').forEach(option => {
  option.addEventListener('click', () => {
    document.querySelectorAll('.cab-type-option').forEach(o => o.classList.remove('active'));
    option.classList.add('active');
    const radio = option.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
  });
});
