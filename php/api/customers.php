<?php
/**
 * Customers API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found'], $row ? 200 : 404);
    }
    $search = $_GET['search'] ?? '';
    $sql = "SELECT * FROM customers WHERE 1=1";
    $params = [];
    if ($search) {
        $sql .= " AND (name LIKE ? OR contact_id LIKE ? OR mobile LIKE ? OR business_name LIKE ?)";
        $params = array_fill(0, 4, "%$search%");
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($data['name'] ?? '');
    $mobile = trim($data['mobile'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
    if (!$mobile) jsonResponse(['success' => false, 'message' => 'Mobile is required.'], 400);

    // Auto-generate contact_id
    $stmt = $db->query("SELECT COUNT(*) as c FROM customers");
    $count = $stmt->fetch()['c'] + 1;
    $contactId = 'CO' . str_pad($count, 4, '0', STR_PAD_LEFT);
    // Ensure unique
    while (true) {
        $chk = $db->prepare("SELECT id FROM customers WHERE contact_id = ?");
        $chk->execute([$contactId]);
        if (!$chk->fetch()) break;
        $count++;
        $contactId = 'CO' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    $stmt = $db->prepare("INSERT INTO customers (contact_id, type, name, prefix, first_name, business_name, mobile, address, city, state, zip, pay_term_val, pay_term_unit, credit_limit, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $contactId,
        $data['type'] ?? 'individual',
        $name,
        $data['prefix'] ?? '',
        $data['first_name'] ?? $name,
        $data['business_name'] ?? '',
        $mobile,
        $data['address'] ?? '',
        $data['city'] ?? '',
        $data['state'] ?? '',
        $data['zip'] ?? '',
        $data['pay_term_val'] ?: null,
        $data['pay_term_unit'] ?? '',
        $data['credit_limit'] ?: null,
        $data['status'] ?? 'Active'
    ]);
    jsonResponse(['success' => true, 'message' => 'Customer added.', 'id' => $db->lastInsertId(), 'contact_id' => $contactId]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $name = trim($data['name'] ?? '');
    $mobile = trim($data['mobile'] ?? '');
    if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);

    $stmt = $db->prepare("UPDATE customers SET type=?, name=?, prefix=?, first_name=?, business_name=?, mobile=?, address=?, city=?, state=?, zip=?, pay_term_val=?, pay_term_unit=?, credit_limit=?, status=? WHERE id=?");
    $stmt->execute([
        $data['type'] ?? 'individual',
        $name,
        $data['prefix'] ?? '',
        $data['first_name'] ?? $name,
        $data['business_name'] ?? '',
        $mobile,
        $data['address'] ?? '',
        $data['city'] ?? '',
        $data['state'] ?? '',
        $data['zip'] ?? '',
        $data['pay_term_val'] ?: null,
        $data['pay_term_unit'] ?? '',
        $data['credit_limit'] ?: null,
        $data['status'] ?? 'Active',
        $id
    ]);
    jsonResponse(['success' => true, 'message' => 'Customer updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Customer deleted.']);
}
