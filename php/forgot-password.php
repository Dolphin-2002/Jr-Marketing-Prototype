<?php
/**
 * Forgot Password Page
 */
session_start();
require_once __DIR__ . '/config/database.php';
if (isset($_SESSION['user'])) { header('Location: dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Forgot Password</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%230a5c2e'/><text x='50%25' y='55%25' font-size='13' font-weight='900' fill='white' text-anchor='middle' dominant-baseline='middle' font-family='Arial'>JR</text></svg>">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>.back-link{display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,0.85);font-size:var(--font-size-sm);font-weight:600;margin-bottom:var(--spacing-md);text-decoration:none;transition:color var(--transition-fast)}.back-link:hover{color:white}</style>
</head>
<body>
    <main class="login-page">
        <a href="index.php" class="back-link">&#8592; Back to Login</a>
        <div class="login-logo-area">
            <div class="login-logo-circle"><img src="assets/images/logo.png" alt="Logo" class="login-logo-img-circle" onerror="this.style.display='none'"></div>
            <h2 class="login-brand-name"><?= APP_NAME ?></h2>
        </div>
        <div class="login-card">
            <div class="login-card-header">
                <h1 class="login-title">Reset Password</h1>
                <p class="login-subtitle">Enter your username to receive reset instructions</p>
            </div>
            <div id="fp-alert" class="alert" style="display:none;"></div>
            <form id="forgot-form" novalidate>
                <div class="form-group">
                    <label class="form-label" for="fp-username">Username</label>
                    <div class="input-icon-wrap">
                        <input type="text" id="fp-username" class="form-control" placeholder="Enter your username" required>
                        <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                    </div>
                </div>
                <div class="login-btn-group">
                    <button type="submit" id="fp-btn" class="btn btn-primary"><span class="btn-text">Send Reset Link</span><div class="spinner"></div></button>
                    <a href="index.php" class="btn btn-outline">Back to Login</a>
                </div>
            </form>
        </div>
        <footer class="login-footer"><p><?= APP_NAME ?> — V<?= APP_VERSION ?> &nbsp;|&nbsp; Copyright &copy; <?= date('Y') ?></p></footer>
    </main>
    <script src="assets/js/toast.js"></script>
    <script>
    document.getElementById('forgot-form').addEventListener('submit', async (e)=>{
        e.preventDefault();
        const v=document.getElementById('fp-username').value.trim();
        if(!v){document.getElementById('fp-username').classList.add('is-error');return;}
        const btn=document.getElementById('fp-btn');
        btn.classList.add('loading'); btn.disabled=true;
        await new Promise(r=>setTimeout(r,1200));
        btn.classList.remove('loading'); btn.disabled=false;
        const a=document.getElementById('fp-alert');
        a.className='alert alert-success';
        a.innerHTML='<span class="alert-icon">✓</span><span>If this account exists, a reset link has been sent. Please contact your system administrator.</span>';
        a.style.display='flex';
        Toast.success('Reset instructions sent!');
    });
    </script>
</body>
</html>
