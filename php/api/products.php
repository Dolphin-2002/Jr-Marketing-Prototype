<?php
/**
 * Products API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id WHERE p.id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found'], $row ? 200 : 404);
    }
    // Optional filters
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $brand = $_GET['brand'] ?? '';
    $stockStatus = $_GET['stock_status'] ?? '';

    $sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($category) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category;
    }
    if ($brand) {
        $sql .= " AND p.brand_id = ?";
        $params[] = $brand;
    }
    if ($stockStatus === 'in') {
        $sql .= " AND p.quantity > 10";
    } elseif ($stockStatus === 'low') {
        $sql .= " AND p.quantity > 0 AND p.quantity <= 10";
    } elseif ($stockStatus === 'out') {
        $sql .= " AND p.quantity = 0";
    }

    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($data['name'] ?? '');
    $sku = trim($data['sku'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Product name is required.'], 400);
    if (!$sku) jsonResponse(['success' => false, 'message' => 'SKU is required.'], 400);

    // Check duplicate SKU
    $chk = $db->prepare("SELECT id FROM products WHERE sku = ?");
    $chk->execute([$sku]);
    if ($chk->fetch()) jsonResponse(['success' => false, 'message' => 'SKU already exists.'], 400);

    $stmt = $db->prepare("INSERT INTO products (name, sku, barcode_type, unit, buying_price, selling_price, manage_stock, quantity, alert_qty, category_id, brand_id, description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $name, $sku,
        $data['barcode_type'] ?? 'C128',
        $data['unit'] ?? 'Pieces',
        floatval($data['buying_price'] ?? 0),
        floatval($data['selling_price'] ?? 0),
        intval($data['manage_stock'] ?? 0),
        intval($data['quantity'] ?? 0),
        intval($data['alert_qty'] ?? 0),
        $data['category_id'] ?: null,
        $data['brand_id'] ?: null,
        $data['description'] ?? ''
    ]);
    jsonResponse(['success' => true, 'message' => 'Product added.', 'id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $name = trim($data['name'] ?? '');
    $sku = trim($data['sku'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Product name is required.'], 400);
    if (!$sku) jsonResponse(['success' => false, 'message' => 'SKU is required.'], 400);

    // Check duplicate SKU (excluding self)
    $chk = $db->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
    $chk->execute([$sku, $id]);
    if ($chk->fetch()) jsonResponse(['success' => false, 'message' => 'SKU already exists.'], 400);

    $stmt = $db->prepare("UPDATE products SET name=?, sku=?, barcode_type=?, unit=?, buying_price=?, selling_price=?, manage_stock=?, quantity=?, alert_qty=?, category_id=?, brand_id=?, description=? WHERE id=?");
    $stmt->execute([
        $name, $sku,
        $data['barcode_type'] ?? 'C128',
        $data['unit'] ?? 'Pieces',
        floatval($data['buying_price'] ?? 0),
        floatval($data['selling_price'] ?? 0),
        intval($data['manage_stock'] ?? 0),
        intval($data['quantity'] ?? 0),
        intval($data['alert_qty'] ?? 0),
        $data['category_id'] ?: null,
        $data['brand_id'] ?: null,
        $data['description'] ?? '',
        $id
    ]);
    jsonResponse(['success' => true, 'message' => 'Product updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Product deleted.']);
}
