/**
 * ============================================
 * LOGIN PAGE CONTROLLER
 * Handles the login form behaviour
 * JR Marketing - Sri Lanka
 * ============================================
 */

document.addEventListener('DOMContentLoaded', () => {

    // ---- Element references ----
    const form          = document.getElementById('login-form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const rememberCheck = document.getElementById('remember-me');
    const loginBtn      = document.getElementById('login-btn');
    const alertBox      = document.getElementById('login-alert');
    const togglePwdBtn  = document.getElementById('toggle-password');

    // ---- Pre-fill remembered username ----
    const remembered = Auth.getRemembered();
    if (remembered && remembered.username) {
        usernameInput.value = remembered.username;
        rememberCheck.checked = true;
        passwordInput.focus();
    }

    // ---- Password Toggle ----
    if (togglePwdBtn) {
        togglePwdBtn.addEventListener('click', () => {
            const isText = passwordInput.type === 'text';
            passwordInput.type = isText ? 'password' : 'text';
            togglePwdBtn.innerHTML = isText
                ? `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
            togglePwdBtn.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
        });
    }

    // ---- Inline validation helpers ----
    function setError(input, msg) {
        input.classList.add('is-error');
        const errEl = input.closest('.form-group')?.querySelector('.form-error-msg');
        if (errEl) errEl.textContent = msg;
    }

    function clearError(input) {
        input.classList.remove('is-error');
    }

    usernameInput.addEventListener('input', () => clearError(usernameInput));
    passwordInput.addEventListener('input', () => clearError(passwordInput));

    // ---- Show alert box ----
    function showAlert(message, type = 'error') {
        if (!alertBox) return;
        alertBox.className = `alert alert-${type}`;
        alertBox.innerHTML = `
            <span class="alert-icon">${type === 'error' ? '⚠' : type === 'success' ? '✓' : 'ℹ'}</span>
            <span>${Utils.sanitize(message)}</span>
        `;
        alertBox.style.display = 'flex';
    }

    function hideAlert() {
        if (alertBox) alertBox.style.display = 'none';
    }

    // ---- Set button loading state ----
    function setLoading(btn, isLoading) {
        if (isLoading) {
            btn.classList.add('loading');
            btn.disabled = true;
        } else {
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    }

    // ---- Handle Login Form Submit ----
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();

        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();
        let hasError = false;

        if (!username) {
            setError(usernameInput, 'Username is required.');
            hasError = true;
        }
        if (!password) {
            setError(passwordInput, 'Password is required.');
            hasError = true;
        }
        if (hasError) return;

        setLoading(loginBtn, true);

        // Simulate network delay
        await new Promise(r => setTimeout(r, 900));

        const result = Auth.login(username, password);
        setLoading(loginBtn, false);

        if (result.success) {
            // Handle "Remember Me"
            if (rememberCheck.checked) {
                Auth.saveRemember(username);
            } else {
                Auth.clearRemember();
            }

            Toast.success(`Welcome back, ${result.user.name}!`);
            showAlert(`Login successful! Redirecting...`, 'success');

            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1200);
        } else {
            showAlert(result.message);
            passwordInput.value = '';
            passwordInput.focus();
            setError(passwordInput, ' ');
        }
    });

    // ---- Redirect if already logged in ----
    if (Auth.isLoggedIn()) {
        window.location.href = 'dashboard.html';
    }
});
