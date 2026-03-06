<?php
/**
 * Payments API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $type = $_GET['type'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $meth = $_GET['method'] ?? '';

    $sql = "SELECT p.*, s.invoice_no, s.customer_name, pu.reference_no AS purchase_ref FROM payments p LEFT JOIN sales s ON p.sale_id=s.id LEFT JOIN purchases pu ON p.purchase_id=pu.id WHERE 1=1";
    $params = [];
    if ($type) { $sql .= " AND p.payment_type = ?"; $params[] = $type; }
    if ($from) { $sql .= " AND p.payment_date >= ?"; $params[] = $from; }
    if ($to) { $sql .= " AND p.payment_date <= ?"; $params[] = $to; }
    if ($meth) { $sql .= " AND p.method = ?"; $params[] = $meth; }
    $sql .= " ORDER BY p.payment_date DESC, p.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    // Get payment details first for reversal
    $pay = $db->prepare("SELECT * FROM payments WHERE id=?");
    $pay->execute([$id]);
    $p = $pay->fetch();
    if (!$p) jsonResponse(['success' => false, 'message' => 'Payment not found.'], 404);

    $db->beginTransaction();
    try {
        // Reverse payment in related records
        if ($p['sale_id']) {
            $db->prepare("UPDATE sales SET total_paid = total_paid - ?, balance = balance + ?, payment_status = CASE WHEN total_paid - ? <= 0 THEN 'Due' ELSE 'Partial' END WHERE id=?")->execute([$p['amount'], $p['amount'], $p['amount'], $p['sale_id']]);
        }
        if ($p['purchase_id']) {
            $db->prepare("UPDATE purchases SET total_paid = total_paid - ?, payment_due = payment_due + ?, payment_status = CASE WHEN total_paid - ? <= 0 THEN 'Due' ELSE 'Partial' END WHERE id=?")->execute([$p['amount'], $p['amount'], $p['amount'], $p['purchase_id']]);
        }
        $db->prepare("DELETE FROM payments WHERE id=?")->execute([$id]);
        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Payment deleted.']);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}
