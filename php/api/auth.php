<?php
/**
 * Auth API
 * Handles login, logout
 */
require_once __DIR__ . '/../includes/auth.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'logout') {
    Auth::logout();
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    $action = $data['action'] ?? '';

    if ($action === 'login') {
        $result = Auth::login($data['username'] ?? '', $data['password'] ?? '');
        jsonResponse($result, $result['success'] ? 200 : 401);
    }
}

jsonResponse(['success' => false, 'message' => 'Invalid request.'], 400);
