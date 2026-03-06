<?php
/**
 * Login Page
 */
session_start();
require_once __DIR__ . '/config/database.php';

// If already logged in, redirect
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Login</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%230a5c2e'/><text x='50%25' y='55%25' font-size='13' font-weight='900' fill='white' text-anchor='middle' dominant-baseline='middle' font-family='Arial'>JR</text></svg>">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <main class="login-page">
        <div class="login-logo-area">
            <div class="login-logo-circle">
                <img src="assets/images/logo.png" alt="<?= APP_NAME ?> Logo" class="login-logo-img-circle" onerror="this.style.display='none'">
            </div>
            <h2 class="login-brand-name"><?= APP_NAME ?></h2>
        </div>

        <div class="login-card">
            <div class="login-card-header">
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Login to your <?= APP_NAME ?></p>
            </div>

            <div id="login-alert" class="alert" style="display:none;" role="alert"></div>

            <form id="login-form" novalidate autocomplete="off">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-icon-wrap">
                        <input type="text" id="username" class="form-control" placeholder="Username" autocomplete="username" required>
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                    </div>
                    <span class="form-error-msg" id="username-error"></span>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" class="form-control" placeholder="Password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="toggle-password" title="Toggle password">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <span class="form-error-msg" id="password-error"></span>
                </div>
                <div class="login-forgot-row">
                    <a href="forgot-password.php" class="login-forgot-link">Forgot Your Password?</a>
                </div>
                <div class="login-btn-group">
                    <button type="submit" id="login-btn" class="btn btn-primary">
                        <span class="btn-text">Login</span>
                        <div class="spinner"></div>
                    </button>
                </div>
            </form>
        </div>

        <footer class="login-footer">
            <p><?= APP_NAME ?> — V<?= APP_VERSION ?> &nbsp;|&nbsp; Copyright &copy; <?= date('Y') ?> All rights reserved.</p>
            <p>Design and Developed by <?= APP_NAME ?></p>
        </footer>
    </main>

    <script src="assets/js/toast.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('login-form');
        const btn = document.getElementById('login-btn');
        const alertEl = document.getElementById('login-alert');

        // Toggle password
        document.getElementById('toggle-password').addEventListener('click', function() {
            const inp = document.getElementById('password');
            inp.type = inp.type === 'password' ? 'text' : 'password';
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            document.getElementById('username-error').textContent = '';
            document.getElementById('password-error').textContent = '';
            alertEl.style.display = 'none';

            if (!username) { document.getElementById('username-error').textContent = 'Username is required'; return; }
            if (!password) { document.getElementById('password-error').textContent = 'Password is required'; return; }

            btn.classList.add('loading');
            btn.disabled = true;

            try {
                const res = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login', username, password })
                });
                const data = await res.json();

                if (data.success) {
                    Toast.success(data.message);
                    setTimeout(() => location.href = 'dashboard.php', 600);
                } else {
                    alertEl.className = 'alert alert-danger';
                    alertEl.innerHTML = '<span class="alert-icon">!</span><span>' + data.message + '</span>';
                    alertEl.style.display = 'flex';
                }
            } catch (err) {
                alertEl.className = 'alert alert-danger';
                alertEl.innerHTML = '<span class="alert-icon">!</span><span>Connection error. Please try again.</span>';
                alertEl.style.display = 'flex';
            }
            btn.classList.remove('loading');
            btn.disabled = false;
        });
    });
    </script>
</body>
</html>
