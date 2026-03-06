<?php
/**
 * Expenses API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $action = $_GET['action'] ?? '';

    // Expense categories
    if ($action === 'categories') {
        $stmt = $db->query("SELECT * FROM expense_categories ORDER BY name");
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    if ($id) {
        $stmt = $db->prepare("SELECT e.*, ec.name AS category_name FROM expenses e LEFT JOIN expense_categories ec ON e.category_id=ec.id WHERE e.id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found'], $row ? 200 : 404);
    }

    // List
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $category = $_GET['category_id'] ?? '';

    $sql = "SELECT e.*, ec.name AS category_name FROM expenses e LEFT JOIN expense_categories ec ON e.category_id=ec.id WHERE 1=1";
    $params = [];
    if ($from) { $sql .= " AND e.expense_date >= ?"; $params[] = $from; }
    if ($to) { $sql .= " AND e.expense_date <= ?"; $params[] = $to; }
    if ($category) { $sql .= " AND e.category_id = ?"; $params[] = $category; }
    $sql .= " ORDER BY e.expense_date DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    // Manage expense categories
    if (($data['action'] ?? '') === 'add_category') {
        $name = trim($data['name'] ?? '');
        $code = trim($data['code'] ?? '');
        if (!$name) jsonResponse(['success' => false, 'message' => 'Name is required.'], 400);
        $stmt = $db->prepare("INSERT INTO expense_categories (name, code) VALUES (?, ?)");
        $stmt->execute([$name, $code]);
        jsonResponse(['success' => true, 'message' => 'Category added.', 'id' => $db->lastInsertId()]);
    }

    if (($data['action'] ?? '') === 'update_category') {
        $id = $data['id'] ?? null;
        $name = trim($data['name'] ?? '');
        if (!$id || !$name) jsonResponse(['success' => false, 'message' => 'ID and name required.'], 400);
        $db->prepare("UPDATE expense_categories SET name=?, code=? WHERE id=?")->execute([$name, $data['code'] ?? '', $id]);
        jsonResponse(['success' => true, 'message' => 'Category updated.']);
    }

    if (($data['action'] ?? '') === 'delete_category') {
        $id = $data['id'] ?? null;
        if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
        $db->prepare("DELETE FROM expense_categories WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Category deleted.']);
    }

    // Create expense
    $amount = floatval($data['amount'] ?? 0);
    if ($amount <= 0) jsonResponse(['success' => false, 'message' => 'Amount is required.'], 400);
    $expenseDate = $data['expense_date'] ?? date('Y-m-d');

    $stmt = $db->prepare("INSERT INTO expenses (category_id, expense_date, reference_no, amount, payment_method, description, paid_by) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([
        $data['category_id'] ?: null,
        $expenseDate,
        $data['reference_no'] ?? '',
        $amount,
        $data['payment_method'] ?? 'Cash',
        $data['description'] ?? '',
        $data['paid_by'] ?? ''
    ]);
    jsonResponse(['success' => true, 'message' => 'Expense added.', 'id' => $db->lastInsertId()]);
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);

    $stmt = $db->prepare("UPDATE expenses SET category_id=?, expense_date=?, reference_no=?, amount=?, payment_method=?, description=?, paid_by=? WHERE id=?");
    $stmt->execute([
        $data['category_id'] ?: null,
        $data['expense_date'] ?? date('Y-m-d'),
        $data['reference_no'] ?? '',
        floatval($data['amount'] ?? 0),
        $data['payment_method'] ?? 'Cash',
        $data['description'] ?? '',
        $data['paid_by'] ?? '',
        $id
    ]);
    jsonResponse(['success' => true, 'message' => 'Expense updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->prepare("DELETE FROM expenses WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Expense deleted.']);
}
