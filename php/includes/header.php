<?php
/**
 * Common Header - Navbar
 * Expects: $pageTitle, $activePage (set before including)
 */
$user = Auth::getUser();
$userName = h($user['name'] ?? $user['username'] ?? 'User');
$userInitial = strtoupper(substr($userName, 0, 1));
$today = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%230a5c2e'/><text x='50%25' y='55%25' font-size='13' font-weight='900' fill='white' text-anchor='middle' dominant-baseline='middle' font-family='Arial'>JR</text></svg>">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <?php if (!empty($extraCss)): ?>
    <style><?= $extraCss ?></style>
    <?php endif; ?>
</head>
<body>
    <nav class="navbar" role="navigation" aria-label="Top Navigation">
        <div class="navbar-left">
            <div class="nav-sidebar-toggle" id="sidebar-toggle" role="button" tabindex="0" aria-label="Toggle sidebar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </div>
            <div class="navbar-brand"><?= APP_NAME ?> <span class="brand-dot"></span></div>
        </div>
        <div class="navbar-center"></div>
        <div class="navbar-right">
            <button class="nav-pos-btn" title="Point of Sale" onclick="location.href='pos.php'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                POS
            </button>
            <div class="nav-divider"></div>
            <span class="nav-date"><?= $today ?></span>
            <div class="nav-divider"></div>
            <button class="nav-icon-btn nav-bell" title="Notifications">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="nav-bell-dot"></span>
            </button>
            <div class="nav-user" id="nav-user" role="button" tabindex="0" onclick="if(confirm('Logout?'))location.href='api/auth.php?action=logout'">
                <div class="nav-user-avatar"><?= $userInitial ?></div>
                <span><?= $userName ?></span>
            </div>
        </div>
    </nav>
    <div class="layout">
