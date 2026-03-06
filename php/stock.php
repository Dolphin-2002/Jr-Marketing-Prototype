<?php
$pageTitle = 'Stock Management';
$activePage = 'stock';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Stock Management</h1>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input type="text" id="fl-search" class="form-control" placeholder="Search product..." style="width:200px">
            <select id="fl-cat" class="form-control" style="width:160px"><option value="">All Categories</option></select>
            <select id="fl-status" class="form-control" style="width:140px">
                <option value="">All Status</option>
                <option value="in_stock">In Stock</option>
                <option value="low_stock">Low Stock</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="loadStock()">Filter</button>
        </div>
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>SKU</th><th>Product</th><th>Category</th><th>Cost</th><th>Price</th><th>Qty</th><th>Alert Qty</th><th>Status</th></tr></thead>
                <tbody id="stock-body"><tr><td colspan="9" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
async function init() {
    try {
        const res = await API.get('api/categories.php');
        const cats = res.data || [];
        document.getElementById('fl-cat').innerHTML = '<option value="">All Categories</option>' + cats.map(c => `<option value="${c.id}">${sanitize(c.name)}</option>`).join('');
        loadStock();
    } catch(e) { loadStock(); }
}

async function loadStock() {
    try {
        const search = document.getElementById('fl-search').value;
        const cat = document.getElementById('fl-cat').value;
        const status = document.getElementById('fl-status').value;
        const q = new URLSearchParams();
        if (search) q.set('search', search);
        if (cat) q.set('category_id', cat);
        if (status) q.set('stockStatus', status);
        q.set('manage_stock', '1');
        const res = await API.get('api/products.php?' + q.toString());
        const data = res.data || [];
        const tbody = document.getElementById('stock-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No products found</td></tr>'; return; }
        tbody.innerHTML = data.map((p, i) => {
            let statusBadge;
            const qty = parseInt(p.quantity) || 0;
            const alert = parseInt(p.alert_quantity) || 0;
            if (qty <= 0) statusBadge = '<span class="badge badge-danger">Out of Stock</span>';
            else if (qty <= alert) statusBadge = '<span class="badge badge-warning">Low Stock</span>';
            else statusBadge = '<span class="badge badge-success">In Stock</span>';
            return `<tr>
                <td>${i+1}</td>
                <td><code>${sanitize(p.sku)}</code></td>
                <td>${sanitize(p.name)}</td>
                <td>${sanitize(p.category_name||'—')}</td>
                <td>${fmtC(p.cost_price)}</td>
                <td>${fmtC(p.selling_price)}</td>
                <td style="font-weight:700">${qty}</td>
                <td>${alert}</td>
                <td>${statusBadge}</td>
            </tr>`;
        }).join('');
    } catch(e) { Toast.error('Failed to load stock'); }
}

document.getElementById('fl-search').addEventListener('keyup', function(e) { if (e.key === 'Enter') loadStock(); });
init();
JS;
include __DIR__ . '/includes/footer.php';
?>
