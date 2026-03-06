<?php
/**
 * Authentication Helper
 * JR Marketing (Pvt) Ltd
 */

session_start();

require_once __DIR__ . '/../config/database.php';

class Auth {
    /**
     * Attempt login
     */
    public static function login(string $username, string $password): array {
        $username = trim($username);
        $password = trim($password);

        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Please enter your username and password.'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, username, password, name, role, is_active FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !$user['is_active']) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'name'     => $user['name'],
            'role'     => $user['role'],
        ];
        $_SESSION['login_time'] = date('Y-m-d H:i:s');

        return ['success' => true, 'user' => $_SESSION['user'], 'message' => 'Welcome, ' . $user['name'] . '!'];
    }

    /**
     * Logout and destroy session
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user']);
    }

    /**
     * Get current session user
     */
    public static function getUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Require authentication - redirect to login if not logged in
     */
    public static function requireAuth(): void {
        if (!self::isLoggedIn()) {
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Require auth for API calls - return JSON error
     */
    public static function requireApiAuth(): void {
        if (!self::isLoggedIn()) {
            jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }
}
