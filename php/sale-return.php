<?php
$pageTitle = 'Sale Return';
$activePage = 'sale-return';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Sale Returns</h1>
        <div class="toolbar-actions">
            <div class="tab-group">
                <button class="tab active" data-tab="list" onclick="switchTab('list')">All Returns</button>
                <button class="tab" data-tab="add" onclick="switchTab('add')">Process Return</button>
            </div>
        </div>
    </div>

    <!-- Returns List -->
    <div class="tab-content active" id="tab-list">
        <div class="card"><div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Return#</th><th>Sale Invoice</th><th>Date</th><th>Amount</th><th>Reason</th></tr></thead>
                <tbody id="returns-body"><tr><td colspan="6" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div></div>
    </div>

    <!-- Process Return -->
    <div class="tab-content" id="tab-add">
        <div class="card">
            <div class="card-header"><h3>Process Sale Return</h3></div>
            <div class="card-body">
                <div class="form-group" style="max-width:400px">
                    <label class="form-label">Select Sale</label>
                    <select id="r-sale" class="form-control" onchange="loadSaleItems()"><option value="">Select a sale...</option></select>
                </div>
                <div id="return-items" style="display:none">
                    <h4 style="margin:16px 0 8px">Select Items to Return</h4>
                    <table class="table">
                        <thead><tr><th><input type="checkbox" id="r-all" onchange="toggleAll(this.checked)"></th><th>Product</th><th>Sold Qty</th><th>Return Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
                        <tbody id="r-items-body"></tbody>
                    </table>
                    <div class="form-group" style="max-width:400px;margin-top:16px">
                        <label class="form-label">Reason</label>
                        <textarea id="r-reason" class="form-control" rows="2"></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="processReturn()">Process Return</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let saleItems = [];

async function init() {
    // Load sales for dropdown
    const res = await API.get('api/sales.php?suspended=0');
    const sales = res.data || [];
    document.getElementById('r-sale').innerHTML = '<option value="">Select a sale...</option>' + sales.map(s => `<option value="${s.id}">${sanitize(s.invoice_no)} — ${sanitize(s.customer_name)} — ${fmtC(s.total_payable)}</option>`).join('');
    loadReturns();
}

async function loadReturns() {
    try {
        const res = await API.get('api/sales.php?action=returns');
        const data = res.data || [];
        const tbody = document.getElementById('returns-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No returns found</td></tr>'; return; }
        tbody.innerHTML = data.map((r, i) => `<tr>
            <td>${i+1}</td>
            <td><strong>${sanitize(r.invoice_no)}</strong></td>
            <td>${sanitize(r.sale_invoice||'—')}</td>
            <td>${fmtD(r.return_date)}</td>
            <td style="color:var(--danger);font-weight:600">${fmtC(r.total_amount)}</td>
            <td>${sanitize(r.reason||'—')}</td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load returns'); }
}

async function loadSaleItems() {
    const saleId = document.getElementById('r-sale').value;
    if (!saleId) { document.getElementById('return-items').style.display = 'none'; return; }
    try {
        const res = await API.get('api/sales.php?id=' + saleId);
        saleItems = res.data.items || [];
        document.getElementById('return-items').style.display = 'block';
        document.getElementById('r-items-body').innerHTML = saleItems.map((item, i) => `<tr>
            <td><input type="checkbox" class="r-check" data-idx="${i}"></td>
            <td>${sanitize(item.product_name)}</td>
            <td>${item.quantity}</td>
            <td><input type="number" class="form-control r-qty" data-idx="${i}" value="${item.quantity}" min="1" max="${item.quantity}" style="width:80px"></td>
            <td>${fmtC(item.unit_price)}</td>
            <td class="r-sub">${fmtC(item.subtotal)}</td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load sale items'); }
}

function toggleAll(checked) {
    document.querySelectorAll('.r-check').forEach(cb => cb.checked = checked);
}

async function processReturn() {
    const saleId = document.getElementById('r-sale').value;
    if (!saleId) { Toast.warning('Select a sale'); return; }
    const items = [];
    let totalAmount = 0;
    document.querySelectorAll('.r-check:checked').forEach(cb => {
        const idx = cb.dataset.idx;
        const qty = parseFloat(document.querySelectorAll('.r-qty')[idx].value) || 0;
        const item = saleItems[idx];
        if (qty > 0 && item) {
            items.push({ product_id: item.product_id, product_name: item.product_name, quantity: qty, unit_price: parseFloat(item.unit_price) });
            totalAmount += qty * parseFloat(item.unit_price);
        }
    });
    if (!items.length) { Toast.warning('Select items to return'); return; }
    try {
        const res = await API.post('api/sales.php', {
            action: 'return', sale_id: saleId, items, total_amount: totalAmount,
            reason: document.getElementById('r-reason').value
        });
        if (res.success) {
            Toast.success(res.message);
            switchTab('list');
            loadReturns();
            document.getElementById('r-sale').value = '';
            document.getElementById('return-items').style.display = 'none';
        } else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

init();
JS;
include __DIR__ . '/includes/footer.php';
?>
