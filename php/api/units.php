<?php
/**
 * Units API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM units WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found'], $row ? 200 : 404);
    }
    $stmt = $db->query("SELECT * FROM units ORDER BY name");
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($data['name'] ?? '');
    $short = trim($data['short_name'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
    $stmt = $db->prepare("INSERT INTO units (name, short_name) VALUES (?, ?)");
    $stmt->execute([$name, $short]);
    jsonResponse(['success' => true, 'message' => 'Unit added.', 'id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $name = trim($data['name'] ?? '');
    $short = trim($data['short_name'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
    $stmt = $db->prepare("UPDATE units SET name=?, short_name=? WHERE id=?");
    $stmt->execute([$name, $short, $id]);
    jsonResponse(['success' => true, 'message' => 'Unit updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->prepare("DELETE FROM units WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Unit deleted.']);
}
