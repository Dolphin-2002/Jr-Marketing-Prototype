<?php
/**
 * Purchases API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $action = $_GET['action'] ?? '';

    // Get single purchase with items
    if ($id) {
        $stmt = $db->prepare("SELECT p.*, s.company AS supplier_name FROM purchases p LEFT JOIN suppliers s ON p.supplier_id=s.id WHERE p.id=?");
        $stmt->execute([$id]);
        $purchase = $stmt->fetch();
        if (!$purchase) jsonResponse(['success' => false, 'message' => 'Not found'], 404);
        $items = $db->prepare("SELECT pi.*, pr.name AS product_name_ref FROM purchase_items pi LEFT JOIN products pr ON pi.product_id=pr.id WHERE pi.purchase_id=?");
        $items->execute([$id]);
        $purchase['items'] = $items->fetchAll();
        jsonResponse(['success' => true, 'data' => $purchase]);
    }

    // Get purchase returns
    if ($action === 'returns') {
        $stmt = $db->query("SELECT pr.*, p.reference_no AS purchase_ref FROM purchase_returns pr LEFT JOIN purchases p ON pr.purchase_id=p.id ORDER BY pr.return_date DESC");
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
    }

    // List with filters
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $supplier = $_GET['supplier_id'] ?? '';
    $status = $_GET['status'] ?? '';

    $sql = "SELECT p.*, s.company AS supplier_name FROM purchases p LEFT JOIN suppliers s ON p.supplier_id=s.id WHERE 1=1";
    $params = [];
    if ($from) { $sql .= " AND p.purchase_date >= ?"; $params[] = $from; }
    if ($to) { $sql .= " AND p.purchase_date <= ?"; $params[] = $to; }
    if ($supplier) { $sql .= " AND p.supplier_id = ?"; $params[] = $supplier; }
    if ($status) { $sql .= " AND p.payment_status = ?"; $params[] = $status; }
    $sql .= " ORDER BY p.purchase_date DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = $data['action'] ?? 'create';

    // Purchase return
    if ($action === 'return') {
        $purchaseId = $data['purchase_id'] ?? null;
        $items = $data['items'] ?? [];
        $reason = $data['reason'] ?? '';
        $totalAmount = floatval($data['total_amount'] ?? 0);
        if (!$purchaseId || empty($items)) jsonResponse(['success' => false, 'message' => 'Purchase and items required.'], 400);

        $refNo = 'PR-' . date('YmdHis');
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO purchase_returns (purchase_id, reference_no, return_date, total_amount, reason) VALUES (?,?,CURDATE(),?,?)");
            $stmt->execute([$purchaseId, $refNo, $totalAmount, $reason]);
            $returnId = $db->lastInsertId();

            $ins = $db->prepare("INSERT INTO purchase_return_items (return_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?,?,?,?,?,?)");
            foreach ($items as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                $price = floatval($item['unit_price'] ?? 0);
                $ins->execute([$returnId, $item['product_id'] ?? null, $item['product_name'] ?? '', $qty, $price, $qty * $price]);
                // Restore stock
                if (!empty($item['product_id'])) {
                    $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND manage_stock = 1")->execute([$qty, $item['product_id']]);
                }
            }
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Purchase return processed.', 'id' => $returnId]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Create purchase
    $refNo = trim($data['reference_no'] ?? ('PO-' . date('YmdHis')));
    $supplierId = $data['supplier_id'] ?: null;
    $purchaseDate = $data['purchase_date'] ?? date('Y-m-d');
    $items = $data['items'] ?? [];
    if (empty($items)) jsonResponse(['success' => false, 'message' => 'Add at least one item.'], 400);

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item['quantity'] ?? 0) * floatval($item['unit_price'] ?? 0);
    }
    $discount = floatval($data['discount'] ?? 0);
    $tax = floatval($data['tax'] ?? 0);
    $grandTotal = $subtotal - $discount + $tax;
    $totalPaid = floatval($data['total_paid'] ?? 0);
    $paymentDue = $grandTotal - $totalPaid;
    $paymentStatus = $paymentDue <= 0 ? 'Paid' : ($totalPaid > 0 ? 'Partial' : 'Due');

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT INTO purchases (reference_no, supplier_id, purchase_date, status, payment_status, subtotal, discount, tax, grand_total, total_paid, payment_due, note) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$refNo, $supplierId, $purchaseDate, $data['status'] ?? 'Received', $paymentStatus, $subtotal, $discount, $tax, $grandTotal, $totalPaid, $paymentDue, $data['note'] ?? '']);
        $purchaseId = $db->lastInsertId();

        $ins = $db->prepare("INSERT INTO purchase_items (purchase_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?,?,?,?,?,?)");
        foreach ($items as $item) {
            $qty = floatval($item['quantity'] ?? 0);
            $price = floatval($item['unit_price'] ?? 0);
            $productId = $item['product_id'] ?? null;
            $ins->execute([$purchaseId, $productId ?: null, $item['product_name'] ?? '', $qty, $price, $qty * $price]);
            // Update stock
            if ($productId) {
                $db->prepare("UPDATE products SET quantity = quantity + ?, buying_price = ? WHERE id = ? AND manage_stock = 1")->execute([$qty, $price, $productId]);
            }
        }

        // Update supplier totals
        if ($supplierId) {
            $db->prepare("UPDATE suppliers SET total_purchase_amount = total_purchase_amount + ?, total_paid = total_paid + ?, total_purchase_due = total_purchase_due + ? WHERE id = ?")->execute([$grandTotal, $totalPaid, $paymentDue, $supplierId]);
        }

        // Record payment if paid
        if ($totalPaid > 0) {
            $db->prepare("INSERT INTO payments (purchase_id, payment_type, amount, method, payment_date, note) VALUES (?, 'purchase', ?, ?, ?, ?)")->execute([$purchaseId, $totalPaid, $data['payment_method'] ?? 'Cash', $purchaseDate, 'Purchase payment']);
        }

        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Purchase saved.', 'id' => $purchaseId]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);

    // Add payment to existing purchase
    if (($data['action'] ?? '') === 'add_payment') {
        $amount = floatval($data['amount'] ?? 0);
        if ($amount <= 0) jsonResponse(['success' => false, 'message' => 'Amount must be greater than 0.'], 400);
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE purchases SET total_paid = total_paid + ?, payment_due = payment_due - ?, payment_status = CASE WHEN payment_due - ? <= 0 THEN 'Paid' ELSE 'Partial' END WHERE id = ?")->execute([$amount, $amount, $amount, $id]);
            $db->prepare("INSERT INTO payments (purchase_id, payment_type, amount, method, payment_date, note) VALUES (?, 'purchase', ?, ?, CURDATE(), ?)")->execute([$id, $amount, $data['method'] ?? 'Cash', $data['note'] ?? '']);
            // Update supplier
            $purchase = $db->prepare("SELECT supplier_id FROM purchases WHERE id=?");
            $purchase->execute([$id]);
            $p = $purchase->fetch();
            if ($p && $p['supplier_id']) {
                $db->prepare("UPDATE suppliers SET total_paid = total_paid + ?, total_purchase_due = total_purchase_due - ? WHERE id = ?")->execute([$amount, $amount, $p['supplier_id']]);
            }
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Payment added.']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Update purchase status
    $stmt = $db->prepare("UPDATE purchases SET status=?, note=? WHERE id=?");
    $stmt->execute([$data['status'] ?? 'Received', $data['note'] ?? '', $id]);
    jsonResponse(['success' => true, 'message' => 'Purchase updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->beginTransaction();
    try {
        // Reverse stock changes
        $items = $db->prepare("SELECT product_id, quantity FROM purchase_items WHERE purchase_id = ?");
        $items->execute([$id]);
        foreach ($items->fetchAll() as $item) {
            if ($item['product_id']) {
                $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND manage_stock = 1")->execute([$item['quantity'], $item['product_id']]);
            }
        }
        $db->prepare("DELETE FROM purchases WHERE id = ?")->execute([$id]);
        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Purchase deleted.']);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}
