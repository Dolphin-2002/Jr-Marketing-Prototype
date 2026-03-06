<?php
$pageTitle = 'Purchases';
$activePage = 'purchases';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Purchases</h1>
        <div class="toolbar-actions">
            <div class="tab-group">
                <button class="tab active" data-tab="list" onclick="switchTab('list')">All Purchases</button>
                <button class="tab" data-tab="add" onclick="switchTab('add')">Add Purchase</button>
                <button class="tab" data-tab="returns" onclick="switchTab('returns');loadReturns()">Returns</button>
            </div>
        </div>
    </div>

    <!-- List Tab -->
    <div class="tab-content active" id="tab-list">
        <div class="card">
            <div class="card-header" style="display:flex;gap:8px;align-items:center">
                <input type="date" id="fl-from" class="form-control" style="width:140px">
                <input type="date" id="fl-to" class="form-control" style="width:140px">
                <select id="fl-status" class="form-control" style="width:140px"><option value="">All Status</option><option value="Paid">Paid</option><option value="Due">Due</option><option value="Partial">Partial</option></select>
                <button class="btn btn-primary btn-sm" onclick="loadPurchases()">Filter</button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>#</th><th>Ref#</th><th>Supplier</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody id="purchase-body"><tr><td colspan="9" class="empty-state">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Tab -->
    <div class="tab-content" id="tab-add">
        <div class="card">
            <div class="card-header"><h3>New Purchase</h3></div>
            <div class="card-body">
                <form id="purchase-form" onsubmit="savePurchase(event)">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px">
                        <div class="form-group"><label class="form-label">Supplier</label><select id="p-supplier" class="form-control"><option value="">Select Supplier</option></select></div>
                        <div class="form-group"><label class="form-label">Purchase Date</label><input type="date" id="p-date" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Reference No</label><input type="text" id="p-ref" class="form-control" placeholder="Auto-generated"></div>
                        <div class="form-group"><label class="form-label">Status</label><select id="p-status" class="form-control"><option value="Received">Received</option><option value="Pending">Pending</option><option value="Ordered">Ordered</option></select></div>
                    </div>

                    <!-- Add Items -->
                    <div style="margin-bottom:16px">
                        <h4 style="margin-bottom:8px">Purchase Items</h4>
                        <div style="display:flex;gap:8px;margin-bottom:12px">
                            <select id="p-product" class="form-control" style="flex:2"><option value="">Select Product</option></select>
                            <input type="number" id="p-qty" class="form-control" placeholder="Qty" style="width:100px" value="1" min="1">
                            <input type="number" id="p-price" class="form-control" placeholder="Unit Price" style="width:140px" step="0.01">
                            <button type="button" class="btn btn-success btn-sm" onclick="addPurchaseItem()">Add</button>
                        </div>
                        <table class="table">
                            <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th><th></th></tr></thead>
                            <tbody id="p-items-body"></tbody>
                            <tfoot><tr><td colspan="3" style="text-align:right"><strong>Grand Total</strong></td><td id="p-grand-total"><strong>LKR 0.00</strong></td><td></td></tr></tfoot>
                        </table>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                        <div class="form-group"><label class="form-label">Discount</label><input type="number" id="p-discount" class="form-control" value="0" step="0.01" onchange="updatePurchaseTotal()"></div>
                        <div class="form-group"><label class="form-label">Payment Amount</label><input type="number" id="p-paid" class="form-control" value="0" step="0.01"></div>
                        <div class="form-group"><label class="form-label">Payment Method</label><select id="p-method" class="form-control"><option>Cash</option><option>Card</option><option>Bank Transfer</option><option>Cheque</option></select></div>
                    </div>
                    <div class="form-group"><label class="form-label">Notes</label><textarea id="p-note" class="form-control" rows="2"></textarea></div>
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Returns Tab -->
    <div class="tab-content" id="tab-returns">
        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>#</th><th>Ref#</th><th>Purchase Ref</th><th>Date</th><th>Amount</th><th>Reason</th></tr></thead>
                    <tbody id="returns-body"><tr><td colspan="6" class="empty-state">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let purchaseItems = [], allProducts = [], allSuppliers = [];

document.getElementById('p-date').value = new Date().toISOString().slice(0,10);
document.getElementById('fl-from').value = new Date().toISOString().slice(0,8) + '01';
document.getElementById('fl-to').value = new Date().toISOString().slice(0,10);

async function init() {
    const [pRes, sRes] = await Promise.all([API.get('api/products.php'), API.get('api/suppliers.php')]);
    allProducts = pRes.data || [];
    allSuppliers = sRes.data || [];
    document.getElementById('p-supplier').innerHTML = '<option value="">Select Supplier</option>' + allSuppliers.map(s => `<option value="${s.id}">${sanitize(s.company)}</option>`).join('');
    document.getElementById('p-product').innerHTML = '<option value="">Select Product</option>' + allProducts.map(p => `<option value="${p.id}" data-price="${p.buying_price}">${sanitize(p.name)} (${sanitize(p.sku)})</option>`).join('');
    document.getElementById('p-product').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.price) document.getElementById('p-price').value = opt.dataset.price;
    });
    loadPurchases();
}

async function loadPurchases() {
    try {
        const from = document.getElementById('fl-from').value;
        const to = document.getElementById('fl-to').value;
        const status = document.getElementById('fl-status').value;
        const res = await API.get(`api/purchases.php?from=${from}&to=${to}&status=${status}`);
        const data = res.data || [];
        const tbody = document.getElementById('purchase-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No purchases found</td></tr>'; return; }
        tbody.innerHTML = data.map((p, i) => `<tr>
            <td>${i+1}</td>
            <td><strong>${sanitize(p.reference_no)}</strong></td>
            <td>${sanitize(p.supplier_name||'—')}</td>
            <td>${fmtD(p.purchase_date)}</td>
            <td>${fmtC(p.grand_total)}</td>
            <td>${fmtC(p.total_paid)}</td>
            <td style="color:var(--danger)">${fmtC(p.payment_due)}</td>
            <td><span class="badge badge-${p.payment_status==='Paid'?'success':p.payment_status==='Partial'?'warning':'danger'}">${p.payment_status}</span></td>
            <td class="action-btns">
                <button class="btn btn-sm btn-outline" onclick="viewPurchase(${p.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="deletePurchase(${p.id})">Del</button>
            </td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load purchases'); }
}

function addPurchaseItem() {
    const sel = document.getElementById('p-product');
    const productId = sel.value;
    const productName = sel.options[sel.selectedIndex]?.text || '';
    const qty = parseFloat(document.getElementById('p-qty').value) || 0;
    const price = parseFloat(document.getElementById('p-price').value) || 0;
    if (!productId || qty <= 0 || price <= 0) { Toast.warning('Select product and enter qty/price'); return; }
    purchaseItems.push({ product_id: productId, product_name: productName, quantity: qty, unit_price: price, subtotal: qty * price });
    renderPurchaseItems();
    sel.value = '';
    document.getElementById('p-qty').value = 1;
    document.getElementById('p-price').value = '';
}

function removePurchaseItem(i) { purchaseItems.splice(i, 1); renderPurchaseItems(); }

function renderPurchaseItems() {
    document.getElementById('p-items-body').innerHTML = purchaseItems.map((item, i) => `<tr>
        <td>${sanitize(item.product_name)}</td><td>${item.quantity}</td><td>${fmtC(item.unit_price)}</td><td>${fmtC(item.subtotal)}</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removePurchaseItem(${i})">×</button></td>
    </tr>`).join('');
    updatePurchaseTotal();
}

function updatePurchaseTotal() {
    const subtotal = purchaseItems.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat(document.getElementById('p-discount').value) || 0;
    document.getElementById('p-grand-total').innerHTML = '<strong>' + fmtC(subtotal - discount) + '</strong>';
}

async function savePurchase(e) {
    e.preventDefault();
    if (!purchaseItems.length) { Toast.warning('Add at least one item'); return; }
    const data = {
        supplier_id: document.getElementById('p-supplier').value || null,
        purchase_date: document.getElementById('p-date').value,
        reference_no: document.getElementById('p-ref').value || '',
        status: document.getElementById('p-status').value,
        items: purchaseItems,
        discount: parseFloat(document.getElementById('p-discount').value) || 0,
        total_paid: parseFloat(document.getElementById('p-paid').value) || 0,
        payment_method: document.getElementById('p-method').value,
        note: document.getElementById('p-note').value
    };
    try {
        const res = await API.post('api/purchases.php', data);
        if (res.success) {
            Toast.success(res.message);
            purchaseItems = [];
            document.getElementById('purchase-form').reset();
            document.getElementById('p-date').value = new Date().toISOString().slice(0,10);
            renderPurchaseItems();
            switchTab('list');
            loadPurchases();
        } else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function viewPurchase(id) {
    try {
        const res = await API.get('api/purchases.php?id=' + id);
        const p = res.data;
        const items = (p.items||[]).map((it,i) => `<tr><td>${i+1}</td><td>${sanitize(it.product_name)}</td><td>${it.quantity}</td><td>${fmtC(it.unit_price)}</td><td>${fmtC(it.subtotal)}</td></tr>`).join('');
        alert(`Purchase: ${p.reference_no}\nSupplier: ${p.supplier_name||'—'}\nDate: ${p.purchase_date}\nTotal: ${fmtC(p.grand_total)}\nPaid: ${fmtC(p.total_paid)}\nDue: ${fmtC(p.payment_due)}\nStatus: ${p.payment_status}`);
    } catch(e) { Toast.error('Failed to load purchase'); }
}

async function deletePurchase(id) {
    if (await confirmDelete('api/purchases.php?id=' + id, 'Delete this purchase? Stock will be reversed.')) loadPurchases();
}

async function loadReturns() {
    try {
        const res = await API.get('api/purchases.php?action=returns');
        const data = res.data || [];
        const tbody = document.getElementById('returns-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No returns found</td></tr>'; return; }
        tbody.innerHTML = data.map((r, i) => `<tr>
            <td>${i+1}</td><td>${sanitize(r.reference_no)}</td><td>${sanitize(r.purchase_ref||'—')}</td>
            <td>${fmtD(r.return_date)}</td><td style="color:var(--danger)">${fmtC(r.total_amount)}</td><td>${sanitize(r.reason||'—')}</td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load returns'); }
}

if (location.hash === '#add') setTimeout(() => switchTab('add'), 100);
if (location.hash === '#return') setTimeout(() => { switchTab('returns'); loadReturns(); }, 100);
init();
JS;
include __DIR__ . '/includes/footer.php';
?>
