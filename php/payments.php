<?php
$pageTitle = 'Payments';
$activePage = 'payments';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Payments</h1>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <select id="fl-type" class="form-control" style="width:140px">
                <option value="">All Types</option>
                <option value="sale">Sale</option>
                <option value="purchase">Purchase</option>
            </select>
            <select id="fl-method" class="form-control" style="width:140px">
                <option value="">All Methods</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cheque">Cheque</option>
            </select>
            <input type="date" id="fl-from" class="form-control" style="width:140px">
            <input type="date" id="fl-to" class="form-control" style="width:140px">
            <button class="btn btn-primary btn-sm" onclick="loadPayments()">Filter</button>
        </div>
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Date</th><th>Type</th><th>Invoice</th><th>Contact</th><th>Amount</th><th>Method</th><th>Note</th><th>Actions</th></tr></thead>
                <tbody id="pay-body"><tr><td colspan="9" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
document.getElementById('fl-from').value = new Date().toISOString().slice(0,8) + '01';
document.getElementById('fl-to').value = new Date().toISOString().slice(0,10);

async function loadPayments() {
    try {
        const type = document.getElementById('fl-type').value;
        const method = document.getElementById('fl-method').value;
        const from = document.getElementById('fl-from').value;
        const to = document.getElementById('fl-to').value;
        const q = new URLSearchParams({ type, method, from, to }).toString();
        const res = await API.get('api/payments.php?' + q);
        const data = res.data || [];
        const tbody = document.getElementById('pay-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No payments found</td></tr>'; return; }
        let total = 0;
        const rows = data.map((p, i) => {
            total += parseFloat(p.amount);
            const badge = p.type === 'sale' ? 'badge-success' : 'badge-warning';
            return `<tr>
                <td>${i+1}</td>
                <td>${fmtD(p.payment_date)}</td>
                <td><span class="badge ${badge}">${p.type}</span></td>
                <td>${sanitize(p.invoice_no||'—')}</td>
                <td>${sanitize(p.contact_name||'—')}</td>
                <td style="font-weight:700">${fmtC(p.amount)}</td>
                <td>${sanitize(p.payment_method)}</td>
                <td>${sanitize(p.note||'—')}</td>
                <td class="action-btns"><button class="btn btn-sm btn-danger" onclick="delPayment(${p.id})">Del</button></td>
            </tr>`;
        });
        rows.push(`<tr style="font-weight:700;background:var(--bg-light)"><td colspan="5" style="text-align:right">Total</td><td>${fmtC(total)}</td><td colspan="3"></td></tr>`);
        tbody.innerHTML = rows.join('');
    } catch(e) { Toast.error('Failed to load payments'); }
}

async function delPayment(id) {
    if (await confirmDelete('api/payments.php?id=' + id, 'Delete this payment? Related sale/purchase balance will be updated.')) loadPayments();
}

loadPayments();
JS;
include __DIR__ . '/includes/footer.php';
?>
