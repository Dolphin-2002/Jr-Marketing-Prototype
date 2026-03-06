<?php
$pageTitle = 'Sales';
$activePage = 'sales';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Sales</h1>
        <div class="toolbar-actions">
            <div class="tab-group">
                <button class="tab active" data-tab="all" onclick="switchTab('all');loadSales()">All Sales</button>
                <button class="tab" data-tab="quotations" onclick="switchTab('quotations');loadSales('quotation')">Quotations</button>
                <button class="tab" data-tab="credit" onclick="switchTab('credit');loadSales('credit')">Credit Sales</button>
                <button class="tab" data-tab="cheques" onclick="switchTab('cheques');loadSales('cheque')">Cheques</button>
            </div>
            <a href="add-sale.php" class="btn btn-primary">+ Add Sale</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom:16px">
        <div class="card-body" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <input type="date" id="fl-from" class="form-control" style="width:140px">
            <input type="date" id="fl-to" class="form-control" style="width:140px">
            <select id="fl-status" class="form-control" style="width:140px"><option value="">All Status</option><option value="Paid">Paid</option><option value="Due">Due</option><option value="Partial">Partial</option></select>
            <button class="btn btn-primary btn-sm" onclick="loadSales()">Filter</button>
        </div>
    </div>

    <!-- All Sales -->
    <div class="tab-content active" id="tab-all"><div class="card"><div class="card-body">
        <table class="table">
            <thead><tr><th>#</th><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="sales-body"><tr><td colspan="9" class="empty-state">Loading...</td></tr></tbody>
        </table>
    </div></div></div>

    <!-- Quotations -->
    <div class="tab-content" id="tab-quotations"><div class="card"><div class="card-body">
        <table class="table">
            <thead><tr><th>#</th><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="quot-body"><tr><td colspan="7" class="empty-state">Loading...</td></tr></tbody>
        </table>
    </div></div></div>

    <!-- Credit -->
    <div class="tab-content" id="tab-credit"><div class="card"><div class="card-body">
        <table class="table">
            <thead><tr><th>#</th><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Actions</th></tr></thead>
            <tbody id="credit-body"><tr><td colspan="8" class="empty-state">Loading...</td></tr></tbody>
        </table>
    </div></div></div>

    <!-- Cheques -->
    <div class="tab-content" id="tab-cheques"><div class="card"><div class="card-body">
        <table class="table">
            <thead><tr><th>#</th><th>Invoice</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="cheques-body"><tr><td colspan="7" class="empty-state">Loading...</td></tr></tbody>
        </table>
    </div></div></div>

    <!-- View Modal -->
    <div class="modal-overlay" id="sale-modal" style="display:none" onclick="if(event.target===this)this.style.display='none'">
        <div class="modal" style="max-width:600px">
            <div class="modal-header"><h3>Sale Details</h3><button class="modal-close" onclick="document.getElementById('sale-modal').style.display='none'">&times;</button></div>
            <div class="modal-body" id="sale-modal-body"></div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let currentType = '';
document.getElementById('fl-from').value = new Date().toISOString().slice(0,8) + '01';
document.getElementById('fl-to').value = new Date().toISOString().slice(0,10);

async function loadSales(type) {
    currentType = type || '';
    const from = document.getElementById('fl-from').value;
    const to = document.getElementById('fl-to').value;
    const status = document.getElementById('fl-status').value;
    let url = `api/sales.php?from=${from}&to=${to}`;
    if (status) url += `&payment_status=${status}`;
    if (type) url += `&sale_type=${type}`;
    url += '&suspended=0';

    try {
        const res = await API.get(url);
        const data = res.data || [];

        if (!type || type === 'sale') {
            renderSalesTable('sales-body', data, true);
        }
        if (type === 'quotation') renderSalesTable('quot-body', data, false);
        if (type === 'credit') renderCreditTable(data);
        if (type === 'cheque') renderSalesTable('cheques-body', data, false);
    } catch(e) { Toast.error('Failed to load sales'); }
}

function renderSalesTable(tbodyId, data, showDue) {
    const tbody = document.getElementById(tbodyId);
    if (!data.length) { tbody.innerHTML = `<tr><td colspan="9" class="empty-state">No records found</td></tr>`; return; }
    tbody.innerHTML = data.map((s, i) => `<tr>
        <td>${i+1}</td>
        <td><strong>${sanitize(s.invoice_no)}</strong></td>
        <td>${sanitize(s.customer_name)}</td>
        <td>${fmtD(s.sale_date)}</td>
        <td>${fmtC(s.total_payable)}</td>
        ${showDue ? `<td>${fmtC(s.total_paid)}</td><td style="color:var(--danger)">${fmtC(s.balance)}</td>` : ''}
        <td><span class="badge badge-${s.payment_status==='Paid'?'success':s.payment_status==='Partial'?'warning':'danger'}">${s.payment_status}</span></td>
        <td class="action-btns">
            <button class="btn btn-sm btn-outline" onclick="viewSale(${s.id})">View</button>
            <button class="btn btn-sm btn-danger" onclick="deleteSale(${s.id})">Del</button>
        </td>
    </tr>`).join('');
}

function renderCreditTable(data) {
    const tbody = document.getElementById('credit-body');
    if (!data.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No credit sales</td></tr>'; return; }
    tbody.innerHTML = data.map((s, i) => `<tr>
        <td>${i+1}</td>
        <td><strong>${sanitize(s.invoice_no)}</strong></td>
        <td>${sanitize(s.customer_name)}</td>
        <td>${fmtD(s.sale_date)}</td>
        <td>${fmtC(s.total_payable)}</td>
        <td>${fmtC(s.total_paid)}</td>
        <td style="color:var(--danger);font-weight:700">${fmtC(s.balance)}</td>
        <td class="action-btns">
            <button class="btn btn-sm btn-outline" onclick="viewSale(${s.id})">View</button>
            ${parseFloat(s.balance)>0?`<button class="btn btn-sm btn-success" onclick="addPayment(${s.id})">Pay</button>`:''}
        </td>
    </tr>`).join('');
}

async function viewSale(id) {
    try {
        const res = await API.get('api/sales.php?id=' + id);
        const s = res.data;
        const items = (s.items||[]).map((it,i) => `<tr><td>${i+1}</td><td>${sanitize(it.product_name)}</td><td>${it.quantity}</td><td>${fmtC(it.unit_price)}</td><td>${fmtC(it.subtotal)}</td></tr>`).join('');
        document.getElementById('sale-modal-body').innerHTML = `
            <table class="table"><tbody>
                <tr><td><strong>Invoice</strong></td><td>${sanitize(s.invoice_no)}</td></tr>
                <tr><td><strong>Customer</strong></td><td>${sanitize(s.customer_name)}</td></tr>
                <tr><td><strong>Date</strong></td><td>${fmtD(s.sale_date)}</td></tr>
                <tr><td><strong>Status</strong></td><td><span class="badge badge-${s.payment_status==='Paid'?'success':'danger'}">${s.payment_status}</span></td></tr>
            </tbody></table>
            <h4 style="margin:12px 0 8px">Items</h4>
            <table class="table"><thead><tr><th>#</th><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>${items}</tbody></table>
            <table class="table" style="margin-top:12px"><tbody>
                <tr><td>Subtotal</td><td style="text-align:right">${fmtC(s.subtotal)}</td></tr>
                <tr><td>Discount</td><td style="text-align:right">${fmtC(s.discount)}</td></tr>
                <tr><td>Tax</td><td style="text-align:right">${fmtC(s.tax)}</td></tr>
                <tr><td><strong>Total</strong></td><td style="text-align:right"><strong>${fmtC(s.total_payable)}</strong></td></tr>
                <tr><td>Paid</td><td style="text-align:right">${fmtC(s.total_paid)}</td></tr>
                <tr><td>Balance</td><td style="text-align:right;color:var(--danger)">${fmtC(s.balance)}</td></tr>
            </tbody></table>`;
        document.getElementById('sale-modal').style.display = 'flex';
    } catch(e) { Toast.error('Failed to load sale'); }
}

async function addPayment(saleId) {
    const amount = prompt('Enter payment amount:');
    if (!amount || parseFloat(amount) <= 0) return;
    try {
        const res = await API.put('api/sales.php', { id: saleId, action: 'add_payment', amount: parseFloat(amount), method: 'Cash' });
        if (res.success) { Toast.success(res.message); loadSales(currentType); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function deleteSale(id) {
    if (await confirmDelete('api/sales.php?id=' + id, 'Delete this sale? Stock will be restored.')) loadSales(currentType);
}

// Hash navigation
if (location.hash === '#quotations') setTimeout(() => { switchTab('quotations'); loadSales('quotation'); }, 100);
else if (location.hash === '#credit') setTimeout(() => { switchTab('credit'); loadSales('credit'); }, 100);
else if (location.hash === '#cheques') setTimeout(() => { switchTab('cheques'); loadSales('cheque'); }, 100);
else loadSales();
JS;
include __DIR__ . '/includes/footer.php';
?>
