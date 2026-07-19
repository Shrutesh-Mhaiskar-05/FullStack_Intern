/* ========================================
   User Management System - JavaScript
   ======================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ---------- Toast Notifications ----------
    window.showToast = function (message, type) {
        type = type || 'success';
        const icons = {
            success: 'bi-check-circle-fill text-success',
            danger: 'bi-x-circle-fill text-danger',
            warning: 'bi-exclamation-triangle-fill text-warning',
            info: 'bi-info-circle-fill text-info'
        };
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const id = 'toast-' + Date.now();
        const html = `
            <div id="${id}" class="toast align-items-center border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi ${icons[type] || icons.info} me-2"></i>
                        ${escapeHtml(message)}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('show');
                setTimeout(() => el.remove(), 300);
            }
        }, 4000);
    };

    // ---------- Image Preview ----------
    const fileInput = document.getElementById('profilePictureInput');
    const preview = document.getElementById('imagePreview');
    if (fileInput && preview) {
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                showToast('Only JPG, PNG, GIF, WebP allowed.', 'danger');
                this.value = '';
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                showToast('File size must be under 2MB.', 'danger');
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'inline';
            };
            reader.readAsDataURL(file);
        });
    }

    // ---------- Password Show/Hide Toggle ----------
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });

    // ---------- Loading Spinner on Form Submit ----------
    document.querySelectorAll('form[data-spinner]').forEach(function (form) {
        form.addEventListener('submit', function () {
            const spinner = document.getElementById('loadingSpinner');
            if (spinner) spinner.classList.add('show');
        });
    });

    // ---------- Client-side Form Validation ----------
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            const password = document.getElementById('regPassword');
            const confirm = document.getElementById('regConfirm');
            const msg = document.getElementById('passwordMatchMsg');
            if (password.value !== confirm.value) {
                e.preventDefault();
                msg.style.display = 'block';
                confirm.classList.add('is-invalid');
            } else {
                msg.style.display = 'none';
                confirm.classList.remove('is-invalid');
            }
        });
    }

    // ---------- Auto-dismiss Alerts ----------
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // ---------- Escape HTML helper ----------
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
