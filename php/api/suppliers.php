<?php
/**
 * Suppliers API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found'], $row ? 200 : 404);
    }
    $search = $_GET['search'] ?? '';
    $sql = "SELECT * FROM suppliers WHERE 1=1";
    $params = [];
    if ($search) {
        $sql .= " AND (company LIKE ? OR contact_id LIKE ? OR contact_person LIKE ? OR phone LIKE ?)";
        $params = array_fill(0, 4, "%$search%");
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $company = trim($data['company'] ?? '');
    $phone = trim($data['phone'] ?? '');
    if (!$company) jsonResponse(['success' => false, 'message' => 'Company name is required.'], 400);
    if (!$phone) jsonResponse(['success' => false, 'message' => 'Phone is required.'], 400);

    // Auto-generate contact_id
    $stmt = $db->query("SELECT COUNT(*) as c FROM suppliers");
    $count = $stmt->fetch()['c'] + 1;
    $contactId = 'SU' . str_pad($count, 4, '0', STR_PAD_LEFT);
    while (true) {
        $chk = $db->prepare("SELECT id FROM suppliers WHERE contact_id = ?");
        $chk->execute([$contactId]);
        if (!$chk->fetch()) break;
        $count++;
        $contactId = 'SU' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    $stmt = $db->prepare("INSERT INTO suppliers (contact_id, company, contact_person, phone, email) VALUES (?,?,?,?,?)");
    $stmt->execute([$contactId, $company, $data['contact_person'] ?? '', $phone, $data['email'] ?? '']);
    jsonResponse(['success' => true, 'message' => 'Supplier added.', 'id' => $db->lastInsertId(), 'contact_id' => $contactId]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $company = trim($data['company'] ?? '');
    if (!$company) jsonResponse(['success' => false, 'message' => 'Company name is required.'], 400);

    $stmt = $db->prepare("UPDATE suppliers SET company=?, contact_person=?, phone=?, email=? WHERE id=?");
    $stmt->execute([$company, $data['contact_person'] ?? '', $data['phone'] ?? '', $data['email'] ?? '', $id]);
    jsonResponse(['success' => true, 'message' => 'Supplier updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->prepare("DELETE FROM suppliers WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Supplier deleted.']);
}
