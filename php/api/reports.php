<?php
/**
 * Reports API — Dashboard + Report data
 */
require_once __DIR__ . '/../includes/auth.php';
Auth::requireApiAuth();
$db = Database::getConnection();
$report = $_GET['report'] ?? 'dashboard';
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

// ── Dashboard Stats ──
if ($report === 'dashboard') {
    // Sales
    $stmt = $db->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(total_payable),0) as total FROM sales WHERE sale_date BETWEEN ? AND ? AND is_suspended=0");
    $stmt->execute([$from, $to]);
    $sales = $stmt->fetch();

    // Purchases
    $stmt = $db->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(grand_total),0) as total FROM purchases WHERE purchase_date BETWEEN ? AND ?");
    $stmt->execute([$from, $to]);
    $purch = $stmt->fetch();

    // Expenses
    $stmt = $db->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as total FROM expenses WHERE expense_date BETWEEN ? AND ?");
    $stmt->execute([$from, $to]);
    $expenses = $stmt->fetch();

    // Payments received
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE payment_type='sale' AND payment_date BETWEEN ? AND ?");
    $stmt->execute([$from, $to]);
    $payReceived = $stmt->fetch()['total'];

    // Outstanding
    $stmt = $db->query("SELECT COALESCE(SUM(balance),0) as total FROM sales WHERE payment_status!='Paid' AND is_suspended=0");
    $outstanding = $stmt->fetch()['total'];

    // Low stock
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM products WHERE manage_stock=1 AND quantity <= alert_qty AND quantity > 0");
    $lowStock = $stmt->fetch()['cnt'];

    // Out of stock
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM products WHERE manage_stock=1 AND quantity = 0");
    $outStock = $stmt->fetch()['cnt'];

    // Recent sales
    $stmt = $db->prepare("SELECT id, invoice_no, customer_name, sale_date, total_payable, payment_status FROM sales WHERE is_suspended=0 ORDER BY id DESC LIMIT 10");
    $stmt->execute();
    $recentSales = $stmt->fetchAll();

    // Stock alerts
    $stmt = $db->query("SELECT id, name, sku, quantity, alert_qty FROM products WHERE manage_stock=1 AND quantity <= alert_qty ORDER BY quantity ASC LIMIT 10");
    $stockAlerts = $stmt->fetchAll();

    // Daily sales for chart (last 7 days)
    $stmt = $db->prepare("SELECT sale_date, SUM(total_payable) as total FROM sales WHERE sale_date >= DATE_SUB(?, INTERVAL 7 DAY) AND is_suspended=0 GROUP BY sale_date ORDER BY sale_date");
    $stmt->execute([$to]);
    $dailySales = $stmt->fetchAll();

    jsonResponse(['success' => true, 'data' => [
        'total_sales' => $sales['total'], 'sales_count' => $sales['cnt'],
        'total_purchases' => $purch['total'], 'purchases_count' => $purch['cnt'],
        'total_expenses' => $expenses['total'], 'expenses_count' => $expenses['cnt'],
        'payments_received' => $payReceived, 'outstanding' => $outstanding,
        'low_stock' => $lowStock, 'out_of_stock' => $outStock,
        'recent_sales' => $recentSales, 'stock_alerts' => $stockAlerts,
        'daily_sales' => $dailySales,
        'profit' => $sales['total'] - $purch['total'] - $expenses['total']
    ]]);
}

// ── Profit/Loss Report ──
if ($report === 'profit-loss') {
    // Revenue
    $stmt = $db->prepare("SELECT COALESCE(SUM(total_payable),0) as total FROM sales WHERE sale_date BETWEEN ? AND ? AND is_suspended=0");
    $stmt->execute([$from, $to]);
    $revenue = $stmt->fetch()['total'];

    // COGS from sold items
    $stmt = $db->prepare("SELECT COALESCE(SUM(si.quantity * p.buying_price),0) as total FROM sale_items si JOIN sales s ON si.sale_id=s.id LEFT JOIN products p ON si.product_id=p.id WHERE s.sale_date BETWEEN ? AND ? AND s.is_suspended=0");
    $stmt->execute([$from, $to]);
    $cogs = $stmt->fetch()['total'];

    // Expenses
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE expense_date BETWEEN ? AND ?");
    $stmt->execute([$from, $to]);
    $expenses = $stmt->fetch()['total'];

    // Expense breakdown
    $stmt = $db->prepare("SELECT ec.name, COALESCE(SUM(e.amount),0) as total FROM expenses e LEFT JOIN expense_categories ec ON e.category_id=ec.id WHERE e.expense_date BETWEEN ? AND ? GROUP BY ec.name ORDER BY total DESC");
    $stmt->execute([$from, $to]);
    $expenseBreakdown = $stmt->fetchAll();

    // Purchase returns
    $stmt = $db->prepare("SELECT COALESCE(SUM(total_amount),0) as total FROM purchase_returns WHERE return_date BETWEEN ? AND ?");
    $stmt->execute([$from, $to]);
    $purchReturns = $stmt->fetch()['total'];

    // Sale returns
    $stmt = $db->prepare("SELECT COALESCE(SUM(total_amount),0) as total FROM sale_returns WHERE return_date BETWEEN ? AND ?");
    $stmt->execute([$from, $to]);
    $saleReturns = $stmt->fetch()['total'];

    $grossProfit = $revenue - $cogs - $saleReturns;
    $netProfit = $grossProfit - $expenses + $purchReturns;

    jsonResponse(['success' => true, 'data' => [
        'revenue' => $revenue, 'cogs' => $cogs,
        'gross_profit' => $grossProfit,
        'expenses' => $expenses, 'expense_breakdown' => $expenseBreakdown,
        'purchase_returns' => $purchReturns, 'sale_returns' => $saleReturns,
        'net_profit' => $netProfit
    ]]);
}

// ── Sales Report ──
if ($report === 'sales') {
    $customer = $_GET['customer_id'] ?? '';
    $sql = "SELECT s.*, (SELECT COUNT(*) FROM sale_items WHERE sale_id=s.id) as item_count FROM sales s WHERE s.sale_date BETWEEN ? AND ? AND s.is_suspended=0";
    $params = [$from, $to];
    if ($customer) { $sql .= " AND s.customer_id = ?"; $params[] = $customer; }
    $sql .= " ORDER BY s.sale_date DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $sales = $stmt->fetchAll();

    $totalSales = array_sum(array_column($sales, 'total_payable'));
    $totalPaid = array_sum(array_column($sales, 'total_paid'));
    $totalDue = array_sum(array_column($sales, 'balance'));

    jsonResponse(['success' => true, 'data' => [
        'sales' => $sales,
        'total_sales' => $totalSales,
        'total_paid' => $totalPaid,
        'total_due' => $totalDue,
        'count' => count($sales)
    ]]);
}

// ── Purchase Report ──
if ($report === 'purchases') {
    $supplier = $_GET['supplier_id'] ?? '';
    $sql = "SELECT p.*, s.company AS supplier_name FROM purchases p LEFT JOIN suppliers s ON p.supplier_id=s.id WHERE p.purchase_date BETWEEN ? AND ?";
    $params = [$from, $to];
    if ($supplier) { $sql .= " AND p.supplier_id = ?"; $params[] = $supplier; }
    $sql .= " ORDER BY p.purchase_date DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $purchases = $stmt->fetchAll();

    $total = array_sum(array_column($purchases, 'grand_total'));
    $paid = array_sum(array_column($purchases, 'total_paid'));
    $due = array_sum(array_column($purchases, 'payment_due'));

    jsonResponse(['success' => true, 'data' => [
        'purchases' => $purchases,
        'total' => $total, 'total_paid' => $paid, 'total_due' => $due,
        'count' => count($purchases)
    ]]);
}

// ── Expense Report ──
if ($report === 'expenses') {
    $category = $_GET['category_id'] ?? '';
    $sql = "SELECT e.*, ec.name AS category_name FROM expenses e LEFT JOIN expense_categories ec ON e.category_id=ec.id WHERE e.expense_date BETWEEN ? AND ?";
    $params = [$from, $to];
    if ($category) { $sql .= " AND e.category_id = ?"; $params[] = $category; }
    $sql .= " ORDER BY e.expense_date DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $expenses = $stmt->fetchAll();

    // Category breakdown
    $stmt2 = $db->prepare("SELECT ec.name, COALESCE(SUM(e.amount),0) as total, COUNT(*) as cnt FROM expenses e LEFT JOIN expense_categories ec ON e.category_id=ec.id WHERE e.expense_date BETWEEN ? AND ? GROUP BY ec.name ORDER BY total DESC");
    $params2 = [$from, $to];
    $stmt2->execute($params2);
    $breakdown = $stmt2->fetchAll();

    $total = array_sum(array_column($expenses, 'amount'));

    jsonResponse(['success' => true, 'data' => [
        'expenses' => $expenses, 'breakdown' => $breakdown,
        'total' => $total, 'count' => count($expenses),
        'average' => count($expenses) ? $total / count($expenses) : 0
    ]]);
}

// ── Stock Report ──
if ($report === 'stock') {
    $search = $_GET['search'] ?? '';
    $stockStatus = $_GET['stock_status'] ?? '';
    $category = $_GET['category_id'] ?? '';

    $sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p LEFT JOIN categories c ON p.category_id=c.id LEFT JOIN brands b ON p.brand_id=b.id WHERE 1=1";
    $params = [];
    if ($search) { $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    if ($category) { $sql .= " AND p.category_id = ?"; $params[] = $category; }
    if ($stockStatus === 'in') $sql .= " AND p.quantity > 10";
    elseif ($stockStatus === 'low') $sql .= " AND p.quantity > 0 AND p.quantity <= 10";
    elseif ($stockStatus === 'out') $sql .= " AND p.quantity = 0";
    $sql .= " ORDER BY p.name";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $totalQty = array_sum(array_column($products, 'quantity'));
    $totalValue = 0;
    $lowCount = 0; $outCount = 0;
    foreach ($products as &$p) {
        $p['stock_value'] = $p['quantity'] * $p['buying_price'];
        $totalValue += $p['stock_value'];
        if ($p['quantity'] == 0) $outCount++;
        elseif ($p['quantity'] <= 10) $lowCount++;
    }

    jsonResponse(['success' => true, 'data' => [
        'products' => $products,
        'total_qty' => $totalQty, 'total_value' => $totalValue,
        'low_stock' => $lowCount, 'out_of_stock' => $outCount,
        'count' => count($products)
    ]]);
}

// ── Tax Report ──
if ($report === 'tax') {
    $stmt = $db->prepare("SELECT * FROM sales WHERE sale_date BETWEEN ? AND ? AND tax > 0 AND is_suspended=0 ORDER BY sale_date DESC");
    $stmt->execute([$from, $to]);
    $sales = $stmt->fetchAll();

    $totalSubtotal = array_sum(array_column($sales, 'subtotal'));
    $totalTax = array_sum(array_column($sales, 'tax'));
    $totalRevenue = $totalSubtotal + $totalTax;
    $effectiveRate = $totalSubtotal > 0 ? ($totalTax / $totalSubtotal) * 100 : 0;

    jsonResponse(['success' => true, 'data' => [
        'sales' => $sales,
        'total_subtotal' => $totalSubtotal, 'total_tax' => $totalTax,
        'total_revenue' => $totalRevenue, 'effective_rate' => $effectiveRate,
        'count' => count($sales)
    ]]);
}
