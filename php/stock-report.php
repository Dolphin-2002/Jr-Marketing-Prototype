<?php
$pageTitle = 'Stock Report';
$activePage = 'stock-report';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Stock Report</h1>
        <div class="toolbar-actions">
            <button class="btn btn-outline btn-sm" onclick="exportCSV()"><i class="bi bi-download"></i> Export CSV</button>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div class="card-header" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input type="text" id="fl-search" class="form-control" placeholder="Search..." style="width:180px">
            <select id="fl-cat" class="form-control" style="width:160px"><option value="">All Categories</option></select>
            <select id="fl-status" class="form-control" style="width:140px">
                <option value="">All Status</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="loadReport()">Generate</button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:16px" id="stats-cards"></div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>SKU</th><th>Product</th><th>Category</th><th>Cost</th><th>Price</th><th>Qty</th><th>Stock Value</th><th>Status</th></tr></thead>
                <tbody id="report-body"><tr><td colspan="9" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let reportData = [];
(async function(){
    try {
        const res = await API.get('api/categories.php');
        const cats = res.data || [];
        document.getElementById('fl-cat').innerHTML = '<option value="">All Categories</option>' + cats.map(c => `<option value="${c.id}">${sanitize(c.name)}</option>`).join('');
    } catch(e) {}
    loadReport();
})();

async function loadReport() {
    const search = document.getElementById('fl-search').value;
    const cat = document.getElementById('fl-cat').value;
    const status = document.getElementById('fl-status').value;
    const q = new URLSearchParams({ report: 'stock' });
    if (search) q.set('search', search);
    if (cat) q.set('category_id', cat);
    if (status) q.set('status', status);
    try {
        const res = await API.get('api/reports.php?' + q.toString());
        const d = res.data;
        reportData = d.products || [];
        document.getElementById('stats-cards').innerHTML = `
            <div class="card"><div class="card-body" style="text-align:center"><h4>${d.total_products}</h4><small>Total Products</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--success)">${d.in_stock}</h4><small>In Stock</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--warning)">${d.low_stock}</h4><small>Low Stock</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--danger)">${d.out_of_stock}</h4><small>Out of Stock</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4>${fmtC(d.total_stock_value)}</h4><small>Total Stock Value</small></div></div>
        `;
        const tbody = document.getElementById('report-body');
        if (!reportData.length) { tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No products</td></tr>'; return; }
        tbody.innerHTML = reportData.map((p, i) => {
            const qty = parseInt(p.quantity) || 0;
            const alert = parseInt(p.alert_quantity) || 0;
            let badge;
            if (qty <= 0) badge = '<span class="badge badge-danger">Out of Stock</span>';
            else if (qty <= alert) badge = '<span class="badge badge-warning">Low Stock</span>';
            else badge = '<span class="badge badge-success">In Stock</span>';
            return `<tr>
                <td>${i+1}</td><td><code>${sanitize(p.sku)}</code></td><td>${sanitize(p.name)}</td>
                <td>${sanitize(p.category_name||'—')}</td>
                <td>${fmtC(p.cost_price)}</td><td>${fmtC(p.selling_price)}</td>
                <td style="font-weight:700">${qty}</td>
                <td style="font-weight:700">${fmtC(p.stock_value)}</td>
                <td>${badge}</td>
            </tr>`;
        }).join('');
    } catch(e) { Toast.error('Failed to load report'); }
}

function exportCSV() {
    if (!reportData.length) return Toast.error('No data to export');
    const headers = ['SKU','Product','Category','Cost','Price','Qty','Stock Value','Status'];
    const rows = reportData.map(p => {
        const qty = parseInt(p.quantity)||0;
        const alert = parseInt(p.alert_quantity)||0;
        const st = qty <= 0 ? 'Out of Stock' : qty <= alert ? 'Low Stock' : 'In Stock';
        return [p.sku, p.name, p.category_name||'', p.cost_price, p.selling_price, qty, p.stock_value, st];
    });
    exportToCSV('stock_report.csv', headers, rows);
}
JS;
include __DIR__ . '/includes/footer.php';
?>
