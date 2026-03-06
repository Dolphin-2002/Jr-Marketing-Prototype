<?php
/**
 * Database Configuration
 * JR Marketing (Pvt) Ltd
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'jr_marketing');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'JR MARKETING (PVT) LTD');
define('APP_CURRENCY', 'LKR');
define('APP_LOCALE', 'en-LK');
define('APP_VERSION', '6.3');

// Base URL - adjust if in subdirectory
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseDir = dirname($scriptDir);
if ($baseDir === '/' || $baseDir === '\\') $baseDir = '';
define('BASE_URL', $protocol . '://' . $host . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        return self::$instance;
    }
}

/**
 * Format currency for display
 */
function formatCurrency(float $amount): string {
    return APP_CURRENCY . ' ' . number_format($amount, 2);
}

/**
 * Format date for display
 */
function formatDate(string $date): string {
    return date('d/m/Y', strtotime($date));
}

/**
 * Get next invoice number
 */
function getNextInvoiceNo(): string {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'invoice_counter'");
    $stmt->execute();
    $row = $stmt->fetch();
    $counter = intval($row['setting_value'] ?? 1000);
    $next = $counter + 1;
    $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'invoice_counter'")->execute([$next]);
    return 'INV-' . str_pad($next, 6, '0', STR_PAD_LEFT);
}

/**
 * Sanitize output for HTML
 */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON response helper for API
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
