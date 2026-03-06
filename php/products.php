<?php
$pageTitle = 'Products';
$activePage = 'products';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Products</h1>
        <div class="toolbar-actions">
            <input type="text" id="search" class="form-control" placeholder="Search products..." style="width:200px" oninput="renderList()">
            <select id="filter-cat" class="form-control" style="width:160px" onchange="renderList()"><option value="">All Categories</option></select>
            <button class="btn btn-primary" onclick="showForm()">+ Add Product</button>
        </div>
    </div>

    <div class="card" id="list-view">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>SKU</th><th>Product</th><th>Category</th><th>Brand</th><th>Buy Price</th><th>Sell Price</th><th>Qty</th><th>Actions</th></tr></thead>
                <tbody id="table-body"><tr><td colspan="9" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="card" id="form-view" style="display:none">
        <div class="card-header"><h3 id="form-title">Add Product</h3></div>
        <div class="card-body">
            <form id="prod-form" onsubmit="saveItem(event)">
                <input type="hidden" id="edit-id">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Product Name *</label><input type="text" id="f-name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">SKU *</label><input type="text" id="f-sku" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Category</label><select id="f-category" class="form-control"><option value="">Select</option></select></div>
                    <div class="form-group"><label class="form-label">Brand</label><select id="f-brand" class="form-control"><option value="">Select</option></select></div>
                    <div class="form-group"><label class="form-label">Unit</label><select id="f-unit" class="form-control"><option value="Pieces">Pieces</option></select></div>
                    <div class="form-group"><label class="form-label">Barcode Type</label><select id="f-barcode" class="form-control"><option value="C128">Code 128</option><option value="C39">Code 39</option><option value="EAN13">EAN-13</option><option value="UPC">UPC</option></select></div>
                    <div class="form-group"><label class="form-label">Buying Price *</label><input type="number" id="f-buy" class="form-control" step="0.01" required></div>
                    <div class="form-group"><label class="form-label">Selling Price *</label><input type="number" id="f-sell" class="form-control" step="0.01" required></div>
                    <div class="form-group"><label class="form-label"><input type="checkbox" id="f-manage-stock" onchange="document.getElementById('stock-fields').style.display=this.checked?'contents':'none'"> Manage Stock</label></div>
                    <div id="stock-fields" style="display:none;contents">
                        <div class="form-group"><label class="form-label">Quantity</label><input type="number" id="f-qty" class="form-control" value="0"></div>
                        <div class="form-group"><label class="form-label">Alert Qty</label><input type="number" id="f-alert" class="form-control" value="0"></div>
                    </div>
                    <div class="form-group" style="grid-column:1/-1"><label class="form-label">Description</label><textarea id="f-desc" class="form-control" rows="2"></textarea></div>
                </div>
                <div style="margin-top:16px;display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <button type="button" class="btn btn-outline" onclick="hideForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let allProducts = [], categories = [], brands = [], units = [];

async function loadData() {
    try {
        const [pRes, cRes, bRes, uRes] = await Promise.all([
            API.get('api/products.php'),
            API.get('api/categories.php'),
            API.get('api/brands.php'),
            API.get('api/units.php')
        ]);
        allProducts = pRes.data || [];
        categories = cRes.data || [];
        brands = bRes.data || [];
        units = uRes.data || [];

        // Populate filter and form dropdowns
        const catOpts = '<option value="">All Categories</option>' + categories.map(c => `<option value="${c.id}">${sanitize(c.name)}</option>`).join('');
        document.getElementById('filter-cat').innerHTML = catOpts;
        document.getElementById('f-category').innerHTML = '<option value="">Select</option>' + categories.map(c => `<option value="${c.id}">${sanitize(c.name)}</option>`).join('');
        document.getElementById('f-brand').innerHTML = '<option value="">Select</option>' + brands.map(b => `<option value="${b.id}">${sanitize(b.name)}</option>`).join('');
        document.getElementById('f-unit').innerHTML = units.map(u => `<option value="${sanitize(u.name)}">${sanitize(u.name)}</option>`).join('');

        renderList();
    } catch(e) { Toast.error('Failed to load data'); }
}

function renderList() {
    const q = document.getElementById('search').value.toLowerCase();
    const cat = document.getElementById('filter-cat').value;
    const filtered = allProducts.filter(p => {
        if (q && !(p.name||'').toLowerCase().includes(q) && !(p.sku||'').toLowerCase().includes(q)) return false;
        if (cat && p.category_id != cat) return false;
        return true;
    });
    const tbody = document.getElementById('table-body');
    if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No products found</td></tr>'; return; }
    tbody.innerHTML = filtered.map((p, i) => {
        const stockBadge = p.manage_stock ? (p.quantity == 0 ? '<span class="badge badge-danger">Out</span>' : p.quantity <= (p.alert_qty||10) ? '<span class="badge badge-warning">' + p.quantity + '</span>' : '<span class="badge badge-success">' + p.quantity + '</span>') : '—';
        return `<tr>
            <td>${i+1}</td>
            <td><strong>${sanitize(p.sku)}</strong></td>
            <td>${sanitize(p.name)}</td>
            <td>${sanitize(p.category_name||'—')}</td>
            <td>${sanitize(p.brand_name||'—')}</td>
            <td>${fmtC(p.buying_price)}</td>
            <td>${fmtC(p.selling_price)}</td>
            <td>${stockBadge}</td>
            <td class="action-btns">
                <button class="btn btn-sm btn-primary" onclick="editItem(${p.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteItem(${p.id})">Del</button>
            </td>
        </tr>`;
    }).join('');
}

function showForm(product) {
    document.getElementById('list-view').style.display = 'none';
    document.getElementById('form-view').style.display = 'block';
    document.getElementById('form-title').textContent = product ? 'Edit Product' : 'Add Product';
    if (product) {
        document.getElementById('edit-id').value = product.id;
        document.getElementById('f-name').value = product.name || '';
        document.getElementById('f-sku').value = product.sku || '';
        document.getElementById('f-category').value = product.category_id || '';
        document.getElementById('f-brand').value = product.brand_id || '';
        document.getElementById('f-unit').value = product.unit || 'Pieces';
        document.getElementById('f-barcode').value = product.barcode_type || 'C128';
        document.getElementById('f-buy').value = product.buying_price || 0;
        document.getElementById('f-sell').value = product.selling_price || 0;
        document.getElementById('f-manage-stock').checked = !!product.manage_stock;
        document.getElementById('stock-fields').style.display = product.manage_stock ? 'contents' : 'none';
        document.getElementById('f-qty').value = product.quantity || 0;
        document.getElementById('f-alert').value = product.alert_qty || 0;
        document.getElementById('f-desc').value = product.description || '';
    } else {
        document.getElementById('prod-form').reset();
        document.getElementById('edit-id').value = '';
        document.getElementById('stock-fields').style.display = 'none';
    }
}

function hideForm() {
    document.getElementById('form-view').style.display = 'none';
    document.getElementById('list-view').style.display = 'block';
}

async function saveItem(e) {
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    const ms = document.getElementById('f-manage-stock').checked;
    const data = {
        name: document.getElementById('f-name').value.trim(),
        sku: document.getElementById('f-sku').value.trim(),
        category_id: document.getElementById('f-category').value || null,
        brand_id: document.getElementById('f-brand').value || null,
        unit: document.getElementById('f-unit').value,
        barcode_type: document.getElementById('f-barcode').value,
        buying_price: parseFloat(document.getElementById('f-buy').value) || 0,
        selling_price: parseFloat(document.getElementById('f-sell').value) || 0,
        manage_stock: ms ? 1 : 0,
        quantity: ms ? parseInt(document.getElementById('f-qty').value) || 0 : 0,
        alert_qty: ms ? parseInt(document.getElementById('f-alert').value) || 0 : 0,
        description: document.getElementById('f-desc').value.trim()
    };
    try {
        let res;
        if (id) { data.id = id; res = await API.put('api/products.php', data); }
        else res = await API.post('api/products.php', data);
        if (res.success) { Toast.success(res.message); hideForm(); loadData(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

function editItem(id) {
    const p = allProducts.find(x => x.id == id);
    if (p) showForm(p);
}

async function deleteItem(id) {
    if (await confirmDelete('api/products.php?id=' + id, 'Delete this product?')) loadData();
}

// Handle hash for #add
if (location.hash === '#add') setTimeout(() => showForm(), 300);

loadData();
JS;
include __DIR__ . '/includes/footer.php';
?>
