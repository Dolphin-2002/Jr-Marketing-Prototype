<?php
/**
 * Sales API
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = $_GET['id'] ?? null;
    $action = $_GET['action'] ?? '';

    if ($id) {
        $stmt = $db->prepare("SELECT * FROM sales WHERE id=?");
        $stmt->execute([$id]);
        $sale = $stmt->fetch();
        if (!$sale) jsonResponse(['success' => false, 'message' => 'Not found'], 404);
        $items = $db->prepare("SELECT si.*, p.sku FROM sale_items si LEFT JOIN products p ON si.product_id=p.id WHERE si.sale_id=?");
        $items->execute([$id]);
        $sale['items'] = $items->fetchAll();
        $pays = $db->prepare("SELECT * FROM payments WHERE sale_id=? AND payment_type='sale'");
        $pays->execute([$id]);
        $sale['payments'] = $pays->fetchAll();
        jsonResponse(['success' => true, 'data' => $sale]);
    }

    // Sale returns
    if ($action === 'returns') {
        $sql = "SELECT sr.*, s.invoice_no AS sale_invoice FROM sale_returns sr LEFT JOIN sales s ON sr.sale_id=s.id ORDER BY sr.return_date DESC";
        $stmt = $db->query($sql);
        $returns = $stmt->fetchAll();
        foreach ($returns as &$ret) {
            $ri = $db->prepare("SELECT * FROM sale_return_items WHERE return_id=?");
            $ri->execute([$ret['id']]);
            $ret['items'] = $ri->fetchAll();
        }
        jsonResponse(['success' => true, 'data' => $returns]);
    }

    // Get next invoice number
    if ($action === 'next_invoice') {
        jsonResponse(['success' => true, 'invoice_no' => getNextInvoiceNo()]);
    }

    // List with filters
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $customer = $_GET['customer_id'] ?? '';
    $status = $_GET['payment_status'] ?? '';
    $type = $_GET['sale_type'] ?? '';
    $suspended = $_GET['suspended'] ?? '';

    $sql = "SELECT * FROM sales WHERE 1=1";
    $params = [];
    if ($from) { $sql .= " AND sale_date >= ?"; $params[] = $from; }
    if ($to) { $sql .= " AND sale_date <= ?"; $params[] = $to; }
    if ($customer) { $sql .= " AND customer_id = ?"; $params[] = $customer; }
    if ($status) { $sql .= " AND payment_status = ?"; $params[] = $status; }
    if ($type) { $sql .= " AND sale_type = ?"; $params[] = $type; }
    if ($suspended !== '') { $sql .= " AND is_suspended = ?"; $params[] = intval($suspended); }
    $sql .= " ORDER BY sale_date DESC, id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = $data['action'] ?? 'create';

    // Process sale return
    if ($action === 'return') {
        $saleId = $data['sale_id'] ?? null;
        $items = $data['items'] ?? [];
        $reason = $data['reason'] ?? '';
        $totalAmount = floatval($data['total_amount'] ?? 0);
        if (!$saleId || empty($items)) jsonResponse(['success' => false, 'message' => 'Sale and items required.'], 400);

        $db->beginTransaction();
        try {
            $invNo = 'SR-' . date('YmdHis');
            $stmt = $db->prepare("INSERT INTO sale_returns (sale_id, invoice_no, return_date, total_amount, reason) VALUES (?,?,CURDATE(),?,?)");
            $stmt->execute([$saleId, $invNo, $totalAmount, $reason]);
            $returnId = $db->lastInsertId();

            $ins = $db->prepare("INSERT INTO sale_return_items (return_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?,?,?,?,?,?)");
            foreach ($items as $item) {
                $qty = floatval($item['quantity'] ?? 0);
                $price = floatval($item['unit_price'] ?? 0);
                $ins->execute([$returnId, $item['product_id'] ?? null, $item['product_name'] ?? '', $qty, $price, $qty * $price]);
                // Restore stock
                if (!empty($item['product_id'])) {
                    $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ? AND manage_stock = 1")->execute([$qty, $item['product_id']]);
                }
            }
            // Update customer return due
            $sale = $db->prepare("SELECT customer_id FROM sales WHERE id=?");
            $sale->execute([$saleId]);
            $s = $sale->fetch();
            if ($s && $s['customer_id']) {
                $db->prepare("UPDATE customers SET total_sell_return_due = total_sell_return_due + ? WHERE id=?")->execute([$totalAmount, $s['customer_id']]);
            }
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Sale return processed.', 'id' => $returnId]);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Create sale
    $items = $data['items'] ?? [];
    if (empty($items)) jsonResponse(['success' => false, 'message' => 'Add at least one item.'], 400);

    $invoiceNo = $data['invoice_no'] ?? getNextInvoiceNo();
    $customerId = $data['customer_id'] ?: null;
    $customerName = $data['customer_name'] ?? 'Walk-In Customer';
    $saleDate = $data['sale_date'] ?? date('Y-m-d');
    $saleType = $data['sale_type'] ?? 'sale';
    $isSuspended = intval($data['is_suspended'] ?? 0);

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item['quantity'] ?? 0) * floatval($item['unit_price'] ?? 0);
    }
    $discount = floatval($data['discount'] ?? 0);
    $tax = floatval($data['tax'] ?? 0);
    $totalPayable = $subtotal - $discount + $tax;

    $payments = $data['payments'] ?? [];
    $totalPaid = 0;
    foreach ($payments as $p) {
        $totalPaid += floatval($p['amount'] ?? 0);
    }
    if (!$payments && isset($data['total_paid'])) {
        $totalPaid = floatval($data['total_paid']);
    }
    $balance = $totalPayable - $totalPaid;
    $changeAmount = $balance < 0 ? abs($balance) : 0;
    if ($balance < 0) $balance = 0;
    $paymentStatus = $balance <= 0 ? 'Paid' : ($totalPaid > 0 ? 'Partial' : 'Due');

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT INTO sales (invoice_no, customer_id, customer_name, sale_date, sale_type, payment_status, subtotal, discount, tax, total_payable, total_paid, balance, change_amount, note, is_suspended) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$invoiceNo, $customerId, $customerName, $saleDate, $saleType, $paymentStatus, $subtotal, $discount, $tax, $totalPayable, $totalPaid, $balance, $changeAmount, $data['note'] ?? '', $isSuspended]);
        $saleId = $db->lastInsertId();

        $ins = $db->prepare("INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?,?,?,?,?,?)");
        foreach ($items as $item) {
            $qty = floatval($item['quantity'] ?? 0);
            $price = floatval($item['unit_price'] ?? 0);
            $productId = $item['product_id'] ?? null;
            $ins->execute([$saleId, $productId ?: null, $item['product_name'] ?? '', $qty, $price, $qty * $price]);
            // Deduct stock
            if ($productId && !$isSuspended) {
                $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND manage_stock = 1")->execute([$qty, $productId]);
            }
        }

        // Record payments
        if (!$isSuspended) {
            if (!empty($payments)) {
                $pIns = $db->prepare("INSERT INTO payments (sale_id, payment_type, amount, method, reference, payment_date, note) VALUES (?, 'sale', ?, ?, ?, ?, ?)");
                foreach ($payments as $p) {
                    $pIns->execute([$saleId, floatval($p['amount'] ?? 0), $p['method'] ?? 'Cash', $p['reference'] ?? '', $saleDate, $p['note'] ?? '']);
                }
            } elseif ($totalPaid > 0) {
                $db->prepare("INSERT INTO payments (sale_id, payment_type, amount, method, payment_date, note) VALUES (?, 'sale', ?, ?, ?, 'Sale payment')")->execute([$saleId, $totalPaid, $data['payment_method'] ?? 'Cash', $saleDate]);
            }
        }

        // Update customer balance
        if ($customerId && !$isSuspended) {
            $db->prepare("UPDATE customers SET total_sale_due = total_sale_due + ? WHERE id=?")->execute([$balance, $customerId]);
        }

        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Sale saved.', 'id' => $saleId, 'invoice_no' => $invoiceNo]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);

    // Add payment to existing sale
    if (($data['action'] ?? '') === 'add_payment') {
        $amount = floatval($data['amount'] ?? 0);
        if ($amount <= 0) jsonResponse(['success' => false, 'message' => 'Amount must be greater than 0.'], 400);
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE sales SET total_paid = total_paid + ?, balance = balance - ?, payment_status = CASE WHEN balance - ? <= 0 THEN 'Paid' ELSE 'Partial' END WHERE id = ?")->execute([$amount, $amount, $amount, $id]);
            $db->prepare("INSERT INTO payments (sale_id, payment_type, amount, method, payment_date, note) VALUES (?, 'sale', ?, ?, CURDATE(), ?)")->execute([$id, $amount, $data['method'] ?? 'Cash', $data['note'] ?? '']);
            $sale = $db->prepare("SELECT customer_id FROM sales WHERE id=?");
            $sale->execute([$id]);
            $s = $sale->fetch();
            if ($s && $s['customer_id']) {
                $db->prepare("UPDATE customers SET total_sale_due = total_sale_due - ? WHERE id=?")->execute([$amount, $s['customer_id']]);
            }
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Payment added.']);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Resume suspended sale
    if (($data['action'] ?? '') === 'resume') {
        $db->prepare("UPDATE sales SET is_suspended = 0 WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Sale resumed.']);
    }

    jsonResponse(['success' => true, 'message' => 'Sale updated.']);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required.'], 400);
    $db->beginTransaction();
    try {
        // Get sale details for reversal
        $sale = $db->prepare("SELECT * FROM sales WHERE id=?");
        $sale->execute([$id]);
        $s = $sale->fetch();
        if ($s) {
            // Restore stock
            $items = $db->prepare("SELECT product_id, quantity FROM sale_items WHERE sale_id=?");
            $items->execute([$id]);
            foreach ($items->fetchAll() as $item) {
                if ($item['product_id']) {
                    $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ? AND manage_stock = 1")->execute([$item['quantity'], $item['product_id']]);
                }
            }
            // Reverse customer balance
            if ($s['customer_id'] && $s['balance'] > 0) {
                $db->prepare("UPDATE customers SET total_sale_due = total_sale_due - ? WHERE id=?")->execute([$s['balance'], $s['customer_id']]);
            }
        }
        $db->prepare("DELETE FROM sales WHERE id = ?")->execute([$id]);
        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Sale deleted.']);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}
