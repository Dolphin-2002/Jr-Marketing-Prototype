<?php
/**
 * Categories API
 * CRUD for product categories
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// GET — list or single
if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found'], $row ? 200 : 404);
    }
    $stmt = $db->query("SELECT * FROM categories ORDER BY name");
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

// POST — create
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($data['name'] ?? '');
    $code = trim($data['code'] ?? '');
    $desc = trim($data['description'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
    $stmt = $db->prepare("INSERT INTO categories (name, code, description) VALUES (?, ?, ?)");
    $stmt->execute([$name, $code, $desc]);
    jsonResponse(['success' => true, 'message' => 'Category added.', 'id' => $db->lastInsertId()]);
}

// PUT — update
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $name = trim($data['name'] ?? '');
    $code = trim($data['code'] ?? '');
    $desc = trim($data['description'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
    $stmt = $db->prepare("UPDATE categories SET name=?, code=?, description=? WHERE id=?");
    $stmt->execute([$name, $code, $desc, $id]);
    jsonResponse(['success' => true, 'message' => 'Category updated.']);
}

// DELETE
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Category deleted.']);
}
