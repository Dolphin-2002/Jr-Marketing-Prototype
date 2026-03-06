<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Dashboard</h1>
        <div class="toolbar-actions">
            <input type="date" id="filter-from" class="form-control" style="width:150px">
            <input type="date" id="filter-to" class="form-control" style="width:150px">
            <button class="btn btn-primary btn-sm" onclick="loadDashboard()">Filter</button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" id="stats-grid">
        <div class="stat-card"><div class="stat-label">Total Sales</div><div class="stat-value" id="s-sales">LKR 0.00</div><div class="stat-sub" id="s-sales-count">0 sales</div></div>
        <div class="stat-card" style="border-left:4px solid var(--danger)"><div class="stat-label">Total Purchases</div><div class="stat-value" id="s-purchases">LKR 0.00</div><div class="stat-sub" id="s-purchases-count">0 purchases</div></div>
        <div class="stat-card" style="border-left:4px solid var(--warning)"><div class="stat-label">Total Expenses</div><div class="stat-value" id="s-expenses">LKR 0.00</div><div class="stat-sub" id="s-expenses-count">0 expenses</div></div>
        <div class="stat-card" style="border-left:4px solid var(--success)"><div class="stat-label">Net Profit</div><div class="stat-value" id="s-profit">LKR 0.00</div></div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Payments Received</div><div class="stat-value" id="s-received">LKR 0.00</div></div>
        <div class="stat-card"><div class="stat-label">Outstanding</div><div class="stat-value" id="s-outstanding" style="color:var(--danger)">LKR 0.00</div></div>
        <div class="stat-card"><div class="stat-label">Low Stock Items</div><div class="stat-value" id="s-low" style="color:var(--warning)">0</div></div>
        <div class="stat-card"><div class="stat-label">Out of Stock</div><div class="stat-value" id="s-out" style="color:var(--danger)">0</div></div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions" style="margin:20px 0">
        <a href="pos.php" class="btn btn-primary">Open POS</a>
        <a href="add-sale.php" class="btn btn-success">Add Sale</a>
        <a href="purchases.php#add" class="btn btn-outline">Add Purchase</a>
        <a href="expenses.php#add" class="btn btn-outline">Add Expense</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
        <!-- Recent Sales -->
        <div class="card">
            <div class="card-header"><h3>Recent Sales</h3></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                    <tbody id="recent-sales"><tr><td colspan="5" class="empty-state">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
        <!-- Stock Alerts -->
        <div class="card">
            <div class="card-header"><h3>Stock Alerts</h3></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>SKU</th><th>Product</th><th>Qty</th><th>Alert</th></tr></thead>
                    <tbody id="stock-alerts"><tr><td colspan="4" class="empty-state">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
const fromEl = document.getElementById('filter-from');
const toEl = document.getElementById('filter-to');
const now = new Date();
fromEl.value = now.toISOString().slice(0,8) + '01';
toEl.value = now.toISOString().slice(0,10);

async function loadDashboard() {
    try {
        const res = await API.get(`api/reports.php?report=dashboard&from=${fromEl.value}&to=${toEl.value}`);
        const d = res.data;
        document.getElementById('s-sales').textContent = fmtC(d.total_sales);
        document.getElementById('s-sales-count').textContent = d.sales_count + ' sales';
        document.getElementById('s-purchases').textContent = fmtC(d.total_purchases);
        document.getElementById('s-purchases-count').textContent = d.purchases_count + ' purchases';
        document.getElementById('s-expenses').textContent = fmtC(d.total_expenses);
        document.getElementById('s-expenses-count').textContent = d.expenses_count + ' expenses';
        document.getElementById('s-profit').textContent = fmtC(d.profit);
        document.getElementById('s-profit').style.color = d.profit >= 0 ? 'var(--success)' : 'var(--danger)';
        document.getElementById('s-received').textContent = fmtC(d.payments_received);
        document.getElementById('s-outstanding').textContent = fmtC(d.outstanding);
        document.getElementById('s-low').textContent = d.low_stock;
        document.getElementById('s-out').textContent = d.out_of_stock;

        // Recent sales
        const rs = d.recent_sales || [];
        document.getElementById('recent-sales').innerHTML = rs.length ? rs.map(s => `
            <tr>
                <td><strong>${sanitize(s.invoice_no)}</strong></td>
                <td>${sanitize(s.customer_name)}</td>
                <td>${fmtD(s.sale_date)}</td>
                <td>${fmtC(s.total_payable)}</td>
                <td><span class="badge badge-${s.payment_status==='Paid'?'success':s.payment_status==='Partial'?'warning':'danger'}">${s.payment_status}</span></td>
            </tr>`).join('') : '<tr><td colspan="5" class="empty-state">No recent sales</td></tr>';

        // Stock alerts
        const sa = d.stock_alerts || [];
        document.getElementById('stock-alerts').innerHTML = sa.length ? sa.map(p => `
            <tr>
                <td>${sanitize(p.sku)}</td>
                <td>${sanitize(p.name)}</td>
                <td><strong style="color:${p.quantity==0?'var(--danger)':'var(--warning)'}">${p.quantity}</strong></td>
                <td>${p.alert_qty}</td>
            </tr>`).join('') : '<tr><td colspan="4" class="empty-state">No stock alerts</td></tr>';
    } catch(e) {
        Toast.error('Failed to load dashboard: ' + e.message);
    }
}
loadDashboard();
JS;
include __DIR__ . '/includes/footer.php';
?>
