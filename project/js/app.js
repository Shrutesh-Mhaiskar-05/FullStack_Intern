document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initNavbar();
  initFooter();
  initThemeToggle();
  initBackToTop();
  initAOSFallback();
  initTypingEffect();
  initCounters();
  initScrollSpy();
});

function initTheme() {
  const saved = localStorage.getItem('authflow-theme');
  const html = document.documentElement;
  html.setAttribute('data-bs-theme', saved || 'dark');
}

function initNavbar() {
  const placeholder = document.getElementById('navbar-placeholder');
  if (!placeholder) return;

  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  const isActive = (page) => currentPage === page ? 'active' : '';

  placeholder.innerHTML = `
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
      <div class="container">
        <a class="navbar-brand" href="index.html">
          <i class="bi bi-shield-check"></i>AuthFlow
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="color: var(--text-primary);">
          <i class="bi bi-list fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
            <li class="nav-item">
              <a class="nav-link ${isActive('index.html')}" href="index.html"><i class="bi bi-house"></i>Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link ${isActive('login.html')}" href="login.html"><i class="bi bi-box-arrow-in-right"></i>Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link ${isActive('register.html')}" href="register.html"><i class="bi bi-person-plus"></i>Register</a>
            </li>
            <li class="nav-item ms-lg-2">
              <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                <i class="bi bi-sun-fill"></i>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  `;

  document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);

  window.addEventListener('scroll', () => {
    const nav = document.getElementById('mainNavbar');
    if (nav) {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    }
  });
}

function initFooter() {
  const placeholder = document.getElementById('footer-placeholder');
  if (!placeholder) return;

  const year = new Date().getFullYear();

  placeholder.innerHTML = `
    <footer class="footer py-4">
      <div class="container">
        <div class="row g-4 align-items-center">
          <div class="col-md-4 text-center text-md-start">
            <a class="navbar-brand mb-0 d-inline-block" href="index.html">
              <i class="bi bi-shield-check"></i>AuthFlow
            </a>
            <p class="small mb-0 mt-1" style="color: var(--text-muted);">Modern Authentication System</p>
          </div>
          <div class="col-md-4 text-center">
            <div class="d-flex justify-content-center gap-2">
              <a href="#" class="social-link"><i class="bi bi-github"></i></a>
              <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
              <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
              <a href="#" class="social-link"><i class="bi bi-youtube"></i></a>
            </div>
          </div>
          <div class="col-md-4 text-center text-md-end">
            <p class="small mb-0" style="color: var(--text-muted);">&copy; ${year} AuthFlow. All rights reserved.</p>
          </div>
        </div>
      </div>
    </footer>
  `;
}

function showToast(message, type = 'info', duration = 4000) {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const icons = {
    success: 'bi-check-circle-fill',
    error: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill'
  };

  const colorMap = {
    success: 'var(--success)',
    error: 'var(--danger)',
    warning: 'var(--warning)',
    info: 'var(--primary)'
  };

  const toast = document.createElement('div');
  toast.className = `custom-toast toast-${type} toast align-items-center border-0`;
  toast.setAttribute('role', 'alert');
  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-2">
        <i class="bi ${icons[type] || icons.info} fs-5" style="color: ${colorMap[type] || colorMap.info}"></i>
        <span class="fw-medium small">${message}</span>
      </div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;

  container.appendChild(toast);
  const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: duration });
  bsToast.show();

  toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function showAlert(container, message, type = 'info') {
  if (!container) return;
  container.className = `alert alert-${type} d-flex align-items-center gap-2`;
  container.innerHTML = `<i class="bi bi-${type === 'danger' ? 'exclamation' : type === 'success' ? 'check' : 'info'}-circle-fill"></i> ${message}`;
  container.classList.remove('d-none');
}

function hideAlert(container) {
  if (!container) return;
  container.classList.add('d-none');
  container.innerHTML = '';
}

function showModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
  }
}

function hideModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    const bsModal = bootstrap.Modal.getInstance(modal);
    bsModal?.hide();
  }
}

function toggleTheme() {
  const html = document.documentElement;
  const icon = document.querySelector('#themeToggle i');
  const current = html.getAttribute('data-bs-theme');

  if (current === 'dark') {
    html.setAttribute('data-bs-theme', 'light');
    icon.className = 'bi bi-moon-fill';
    localStorage.setItem('authflow-theme', 'light');
  } else {
    html.setAttribute('data-bs-theme', 'dark');
    icon.className = 'bi bi-sun-fill';
    localStorage.setItem('authflow-theme', 'dark');
  }
}

function initThemeToggle() {
  const saved = localStorage.getItem('authflow-theme') || 'dark';
  setTimeout(() => {
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
      icon.className = saved === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    }
  }, 100);
}

function initBackToTop() {
  const btn = document.getElementById('backToTop');
  if (!btn) return;

  window.addEventListener('scroll', () => {
    btn.classList.toggle('show', window.scrollY > 400);
  });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

function initAOSFallback() {
  const elements = document.querySelectorAll('[data-aos]');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('aos-animate');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  elements.forEach(el => observer.observe(el));
}

function initTypingEffect() {
  const el = document.getElementById('typing-text');
  if (!el) return;

  const phrases = [
    'Secure Authentication.',
    'Modern Login System.',
    'User Friendly Portal.',
    'Zero Compromise.'
  ];

  let phraseIndex = 0;
  let charIndex = 0;
  let isDeleting = false;
  let currentText = '';

  function type() {
    const currentPhrase = phrases[phraseIndex];

    if (!isDeleting) {
      currentText = currentPhrase.substring(0, charIndex + 1);
      charIndex++;
    } else {
      currentText = currentPhrase.substring(0, charIndex - 1);
      charIndex--;
    }

    el.textContent = currentText;

    if (!isDeleting && charIndex === currentPhrase.length) {
      isDeleting = true;
      setTimeout(type, 2000);
      return;
    }

    if (isDeleting && charIndex === 0) {
      isDeleting = false;
      phraseIndex = (phraseIndex + 1) % phrases.length;
      setTimeout(type, 500);
      return;
    }

    setTimeout(type, isDeleting ? 50 : 100);
  }

  type();
}

function initCounters() {
  const counters = document.querySelectorAll('.counter');

  if (!counters.length) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const counter = entry.target;
        const target = parseFloat(counter.getAttribute('data-target'));
        animateCounter(counter, target);
        observer.unobserve(counter);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(c => observer.observe(c));
}

function animateCounter(element, target) {
  const duration = 2000;
  const steps = 60;
  const stepDuration = duration / steps;
  const increment = target / steps;
  let current = 0;
  let step = 0;

  const timer = setInterval(() => {
    step++;
    current += increment;

    if (step >= steps) {
      element.textContent = target;
      clearInterval(timer);
      return;
    }

    if (target % 1 === 0) {
      element.textContent = Math.round(current);
    } else {
      element.textContent = current.toFixed(1);
    }
  }, stepDuration);
}

function initScrollSpy() {
  const navLinks = document.querySelectorAll('.nav-link');
  if (!navLinks.length || window.location.pathname.includes('login') || window.location.pathname.includes('register')) return;

  const sections = document.querySelectorAll('section[id]');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const top = section.offsetTop - 150;
      if (window.scrollY >= top) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href')?.includes(current)) {
        link.classList.add('active');
      }
    });
  });
}
