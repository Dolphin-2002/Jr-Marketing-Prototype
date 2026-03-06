<?php
/**
 * Brands API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found'], $row ? 200 : 404);
    }
    $stmt = $db->query("SELECT * FROM brands ORDER BY name");
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($data['name'] ?? '');
    $desc = trim($data['description'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
    $stmt = $db->prepare("INSERT INTO brands (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $desc]);
    jsonResponse(['success' => true, 'message' => 'Brand added.', 'id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $name = trim($data['name'] ?? '');
    $desc = trim($data['description'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
    $stmt = $db->prepare("UPDATE brands SET name=?, description=? WHERE id=?");
    $stmt->execute([$name, $desc, $id]);
    jsonResponse(['success' => true, 'message' => 'Brand updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->prepare("DELETE FROM brands WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Brand deleted.']);
}
