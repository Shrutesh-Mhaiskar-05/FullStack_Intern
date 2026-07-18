document.addEventListener('DOMContentLoaded', () => {
  initPasswordToggles();
  initLoginForm();
  initRememberMe();
  initForgotPassword();
});

function initPasswordToggles() {
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function () {
      const input = this.closest('.input-group').querySelector('input');
      const icon = this.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye';
      } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-slash';
      }
    });
  });
}

function initLoginForm() {
  const form = document.getElementById('loginForm');
  if (!form) return;

  const emailInput = document.getElementById('loginEmail');
  const passwordInput = document.getElementById('loginPassword');
  const alert = document.getElementById('loginAlert');
  const btn = document.getElementById('loginBtn');

  emailInput.addEventListener('blur', function () {
    validateEmailInput(this);
  });

  emailInput.addEventListener('input', function () {
    if (this.classList.contains('is-invalid')) {
      validateEmailInput(this);
    }
  });

  passwordInput.addEventListener('blur', function () {
    validatePasswordInput(this);
  });

  passwordInput.addEventListener('input', function () {
    if (this.classList.contains('is-invalid')) {
      validatePasswordInput(this);
    }
  });

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideAlert(alert);

    const isEmailValid = validateEmailInput(emailInput);
    const isPasswordValid = validatePasswordInput(passwordInput);

    if (!isEmailValid || !isPasswordValid) {
      return;
    }

    setLoadingState(btn, true);

    const result = await simulateLogin(emailInput.value.trim(), passwordInput.value);

    setLoadingState(btn, false);

    if (result.success) {
      showAlert(alert, result.message, 'success');

      const rememberMe = document.getElementById('rememberMe');
      if (rememberMe?.checked) {
        localStorage.setItem('authflow_remember', JSON.stringify({
          email: emailInput.value.trim(),
          timestamp: new Date().getTime()
        }));
      } else {
        localStorage.removeItem('authflow_remember');
      }

      sessionStorage.setItem('authflow_user', JSON.stringify(result.user));
      sessionStorage.setItem('authflow_token', result.token);

      showToast(result.message, 'success');

      setTimeout(() => {
        window.location.href = 'index.html';
      }, 1500);
    } else {
      showAlert(alert, result.message, 'danger');
      showToast(result.message, 'error');
      emailInput.classList.add('is-invalid');
      passwordInput.classList.add('is-invalid');
    }
  });
}

function validateEmailInput(input) {
  const email = input.value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!email) {
    setInvalid(input, 'Email is required.');
    return false;
  }

  if (!emailRegex.test(email)) {
    setInvalid(input, 'Please enter a valid email address.');
    return false;
  }

  setValid(input);
  return true;
}

function validatePasswordInput(input) {
  const password = input.value;

  if (!password) {
    setInvalid(input, 'Password is required.');
    return false;
  }

  if (password.length < 6) {
    setInvalid(input, 'Password must be at least 6 characters.');
    return false;
  }

  setValid(input);
  return true;
}

function setInvalid(input, message) {
  input.classList.add('is-invalid');
  input.classList.remove('is-valid');
  const feedback = input.closest('.mb-3')?.querySelector('.invalid-feedback');
  if (feedback) feedback.textContent = message;
}

function setValid(input) {
  input.classList.remove('is-invalid');
  input.classList.add('is-valid');
}

function setLoadingState(btn, loading) {
  const text = btn.querySelector('.btn-text');
  const loader = btn.querySelector('.btn-loading');

  if (loading) {
    text.classList.add('d-none');
    loader.classList.remove('d-none');
    btn.disabled = true;
  } else {
    text.classList.remove('d-none');
    loader.classList.add('d-none');
    btn.disabled = false;
  }
}

function initRememberMe() {
  const saved = localStorage.getItem('authflow_remember');
  const emailInput = document.getElementById('loginEmail');
  const rememberCheck = document.getElementById('rememberMe');

  if (saved && emailInput && rememberCheck) {
    try {
      const data = JSON.parse(saved);
      emailInput.value = data.email || '';
      rememberCheck.checked = true;
    } catch {
      localStorage.removeItem('authflow_remember');
    }
  }
}

function initForgotPassword() {
  const resetBtn = document.getElementById('resetBtn');
  const resetEmail = document.getElementById('resetEmail');

  if (!resetBtn || !resetEmail) return;

  resetBtn.addEventListener('click', function () {
    const email = resetEmail.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!email || !emailRegex.test(email)) {
      resetEmail.classList.add('is-invalid');
      return;
    }

    resetEmail.classList.remove('is-invalid');

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

    setTimeout(() => {
      showToast('Password reset link sent to your email!', 'success');

      const modalEl = document.getElementById('forgotPasswordModal');
      const modal = bootstrap.Modal.getInstance(modalEl);
      modal?.hide();

      this.disabled = false;
      this.innerHTML = 'Send Reset Link';
      resetEmail.value = '';
    }, 1500);
  });

  resetEmail.addEventListener('input', function () {
    this.classList.remove('is-invalid');
  });
}
