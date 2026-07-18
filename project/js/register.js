document.addEventListener('DOMContentLoaded', () => {
  initPasswordToggles();
  initRegisterForm();
  initPasswordStrength();
  initUsernameCheck();
  initEmailCheck();
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


function initRegisterForm() {
  const form = document.getElementById('registerForm');
  if (!form) return;

  const inputs = {
    fullName: document.getElementById('fullName'),
    username: document.getElementById('username'),
    email: document.getElementById('registerEmail'),
    phone: document.getElementById('phoneNumber'),
    password: document.getElementById('registerPassword'),
    confirm: document.getElementById('confirmPassword'),
    agree: document.getElementById('agreeTerms')
  };

  const alert = document.getElementById('registerAlert');
  const btn = document.getElementById('registerBtn');

  // Real-time validation on blur
  Object.entries(inputs).forEach(([key, input]) => {
    if (!input || input.type === 'checkbox') return;

    input.addEventListener('blur', function () {
      validateField(key, this, inputs);
    });

    input.addEventListener('input', function () {
      if (this.classList.contains('is-invalid')) {
        validateField(key, this, inputs);
      }
      // Real-time confirm password check
      if (key === 'confirm' || key === 'password') {
        if (inputs.confirm.value) {
          validateField('confirm', inputs.confirm, inputs);
        }
      }
    });
  });

  // Form submit
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    hideAlert(alert);

    const validations = [
      validateField('fullName', inputs.fullName, inputs),
      validateField('username', inputs.username, inputs),
      validateField('email', inputs.email, inputs),
      validateField('phone', inputs.phone, inputs),
      validateField('password', inputs.password, inputs),
      validateField('confirm', inputs.confirm, inputs)
    ];

    if (!inputs.agree.checked) {
      inputs.agree.classList.add('is-invalid');
      validations.push(false);
    } else {
      inputs.agree.classList.remove('is-invalid');
    }

    if (validations.includes(false)) {
      showToast('Please fix the errors in the form.', 'error');
      return;
    }

    // Check username availability via AJAX
    const usernameCheck = await checkUsernameAvailable(inputs.username.value.trim());
    if (!usernameCheck.available) {
      inputs.username.classList.add('is-invalid');
      const feedback = inputs.username.closest('.col-md-6')?.querySelector('.invalid-feedback');
      if (feedback) feedback.textContent = usernameCheck.message;
      showToast(usernameCheck.message, 'error');
      return;
    }

    // Check email availability via AJAX
    const emailCheck = await checkEmailExists(inputs.email.value.trim());
    if (emailCheck.exists) {
      inputs.email.classList.add('is-invalid');
      showToast(emailCheck.message, 'error');
      return;
    }

    // Show loading
    setLoadingState(btn, true);

    // Simulate registration via AJAX
    const result = await simulateRegistration({
      fullName: inputs.fullName.value.trim(),
      username: inputs.username.value.trim(),
      email: inputs.email.value.trim(),
      phone: inputs.phone.value.trim(),
      password: inputs.password.value
    });

    setLoadingState(btn, false);

    if (result.success) {
      showToast(result.message, 'success');
      showSuccessModal(result.user.name);
      form.reset();
      document.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
      resetStrengthMeter();
    } else {
      showAlert(alert, result.message, 'danger');
      showToast(result.message, 'error');
    }
  });
}


function validateField(field, input, inputs) {
  const value = input.value.trim();

  switch (field) {
    case 'fullName':
      if (!value) return setInvalid(input, 'Full name is required.');
      if (value.length < 3) return setInvalid(input, 'Name must be at least 3 characters.');
      return setValid(input);

    case 'username':
      if (!value) return setInvalid(input, 'Username is required.');
      if (value.length < 3) return setInvalid(input, 'Username must be at least 3 characters.');
      if (value.length > 20) return setInvalid(input, 'Username must be less than 20 characters.');
      if (!/^[a-zA-Z0-9_]+$/.test(value)) return setInvalid(input, 'Only letters, numbers, and underscores allowed.');
      return setValid(input);

    case 'email':
      if (!value) return setInvalid(input, 'Email is required.');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return setInvalid(input, 'Please enter a valid email address.');
      return setValid(input);

    case 'phone':
      if (!value) return setInvalid(input, 'Phone number is required.');
      if (!/^[\+\d\s\-\(\)]{10,20}$/.test(value)) return setInvalid(input, 'Please enter a valid phone number.');
      return setValid(input);

    case 'password':
      if (!value) return setInvalid(input, 'Password is required.');
      if (value.length < 6) return setInvalid(input, 'Password must be at least 6 characters.');
      return setValid(input);

    case 'confirm':
      if (!value) return setInvalid(input, 'Please confirm your password.');
      if (value !== inputs.password.value) return setInvalid(input, 'Passwords do not match.');
      return setValid(input);

    default:
      return true;
  }
}

function setInvalid(input, message) {
  input.classList.add('is-invalid');
  input.classList.remove('is-valid');
  const feedback = input.closest('.col-md-6')?.querySelector('.invalid-feedback') ||
                    input.closest('.mb-3')?.querySelector('.invalid-feedback') ||
                    input.closest('.col-12')?.querySelector('.invalid-feedback');
  if (feedback) feedback.textContent = message;
  return false;
}

function setValid(input) {
  input.classList.remove('is-invalid');
  input.classList.add('is-valid');
  return true;
}

function setLoadingState(btn, loading) {
  const text = btn.querySelector('.btn-text');
  const loader = btn.querySelector('.btn-loading');

  if (loading) {
    text?.classList.add('d-none');
    loader?.classList.remove('d-none');
    btn.disabled = true;
  } else {
    text?.classList.remove('d-none');
    loader?.classList.add('d-none');
    btn.disabled = false;
  }
}
function initPasswordStrength() {
  const passwordInput = document.getElementById('registerPassword');
  if (!passwordInput) return;

  passwordInput.addEventListener('input', function () {
    updateStrengthMeter(this.value);
  });
}

function updateStrengthMeter(password) {
  const bar = document.getElementById('strengthBar');
  const text = document.getElementById('strengthText');
  if (!bar || !text) return;

  let strength = 0;
  let level = 'None';
  let color = 'transparent';
  let width = '0%';

  if (password.length >= 6) strength += 20;
  if (password.length >= 10) strength += 10;
  if (/[a-z]/.test(password)) strength += 15;
  if (/[A-Z]/.test(password)) strength += 15;
  if (/\d/.test(password)) strength += 15;
  if (/[^a-zA-Z0-9]/.test(password)) strength += 15;
  if (password.length >= 12) strength += 10;

  if (strength < 30) {
    level = 'Weak';
    color = '#e17055';
    width = '25%';
  } else if (strength < 50) {
    level = 'Fair';
    color = '#fdcb6e';
    width = '50%';
  } else if (strength < 70) {
    level = 'Good';
    color = '#00b894';
    width = '75%';
  } else if (strength >= 70) {
    level = 'Strong';
    color = '#6c5ce7';
    width = '100%';
  }

  bar.style.width = width;
  bar.style.background = color;
  bar.style.backgroundColor = color;
  text.textContent = level;
  text.style.color = color;
}

function resetStrengthMeter() {
  const bar = document.getElementById('strengthBar');
  const text = document.getElementById('strengthText');
  if (bar) {
    bar.style.width = '0%';
    bar.style.background = 'transparent';
  }
  if (text) {
    text.textContent = 'None';
    text.style.color = '';
  }
}
function initUsernameCheck() {
  const usernameInput = document.getElementById('username');
  if (!usernameInput) return;

  let debounceTimer;

  usernameInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);

    const feedback = this.closest('.col-md-6')?.querySelector('.invalid-feedback');
    const availableMsg = document.getElementById('usernameAvailable');

    if (this.value.trim().length < 3) {
      if (availableMsg) availableMsg.classList.add('d-none');
      return;
    }

    debounceTimer = setTimeout(async () => {
      const result = await checkUsernameAvailable(this.value.trim());

      if (availableMsg) {
        if (result.available) {
          availableMsg.classList.remove('d-none');
          availableMsg.classList.add('valid-feedback');
        } else {
          availableMsg.classList.add('d-none');
          if (feedback) feedback.textContent = result.message;
        }
      }

      if (!result.available) {
        this.classList.add('is-invalid');
      }
    }, 500);
  });
}
function initEmailCheck() {
  const emailInput = document.getElementById('registerEmail');
  if (!emailInput) return;

  let debounceTimer;

  emailInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);

    if (!this.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value.trim())) {
      return;
    }

    debounceTimer = setTimeout(async () => {
      const result = await checkEmailExists(this.value.trim());

      if (result.exists) {
        const feedback = this.closest('.col-md-6')?.querySelector('.invalid-feedback');
        if (feedback) feedback.textContent = result.message;
        this.classList.add('is-invalid');
      }
    }, 500);
  });
}
function showSuccessModal(userName) {
  const existing = document.getElementById('successModal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.className = 'modal fade';
  modal.id = 'successModal';
  modal.setAttribute('tabindex', '-1');
  modal.innerHTML = `
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow text-center p-4">
        <div class="modal-body">
          <div class="success-icon mb-3">
            <i class="bi bi-check-lg fs-1 text-success"></i>
          </div>
          <h4 class="fw-bold mb-2">Welcome, ${userName}!</h4>
          <p class="text-muted small mb-4">Your account has been created successfully.</p>
          <a href="login.html" class="btn btn-primary w-100 rounded-pill hover-lift">
            <i class="bi bi-box-arrow-in-right me-2"></i>Proceed to Login
          </a>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();

  modal.addEventListener('hidden.bs.modal', () => modal.remove());
}
