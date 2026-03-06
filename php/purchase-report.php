<?php
$pageTitle = 'Purchase Report';
$activePage = 'purchase-report';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Purchase Report</h1>
        <div class="toolbar-actions">
            <button class="btn btn-outline btn-sm" onclick="exportCSV()"><i class="bi bi-download"></i> Export CSV</button>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div class="card-header" style="display:flex;gap:8px;align-items:center">
            <input type="date" id="fl-from" class="form-control" style="width:140px">
            <input type="date" id="fl-to" class="form-control" style="width:140px">
            <button class="btn btn-primary btn-sm" onclick="loadReport()">Generate</button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:16px" id="stats-cards"></div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Date</th><th>Reference</th><th>Supplier</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th></tr></thead>
                <tbody id="report-body"><tr><td colspan="11" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let reportData = [];
document.getElementById('fl-from').value = new Date().toISOString().slice(0,8) + '01';
document.getElementById('fl-to').value = new Date().toISOString().slice(0,10);

async function loadReport() {
    const from = document.getElementById('fl-from').value;
    const to = document.getElementById('fl-to').value;
    try {
        const res = await API.get(`api/reports.php?report=purchases&from=${from}&to=${to}`);
        const d = res.data;
        reportData = d.purchases || [];
        document.getElementById('stats-cards').innerHTML = `
            <div class="card"><div class="card-body" style="text-align:center"><h4>${d.total_purchases}</h4><small>Total Purchases</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--warning)">${fmtC(d.total_amount)}</h4><small>Total Amount</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--success)">${fmtC(d.total_paid)}</h4><small>Total Paid</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--danger)">${fmtC(d.total_due)}</h4><small>Total Due</small></div></div>
        `;
        const tbody = document.getElementById('report-body');
        if (!reportData.length) { tbody.innerHTML = '<tr><td colspan="11" class="empty-state">No purchases</td></tr>'; return; }
        tbody.innerHTML = reportData.map((p, i) => {
            const due = parseFloat(p.total_amount) - parseFloat(p.paid_amount);
            const badge = p.payment_status === 'paid' ? 'badge-success' : p.payment_status === 'partial' ? 'badge-warning' : 'badge-danger';
            return `<tr>
                <td>${i+1}</td><td>${fmtD(p.purchase_date)}</td><td>${sanitize(p.reference_no||'—')}</td>
                <td>${sanitize(p.supplier_name||'—')}</td><td>${p.item_count}</td>
                <td>${fmtC(p.subtotal)}</td><td>${fmtC(p.discount_amount)}</td>
                <td style="font-weight:700">${fmtC(p.total_amount)}</td><td>${fmtC(p.paid_amount)}</td>
                <td style="color:var(--danger)">${fmtC(due)}</td>
                <td><span class="badge ${badge}">${p.payment_status}</span></td>
            </tr>`;
        }).join('');
    } catch(e) { Toast.error('Failed to load report'); }
}

function exportCSV() {
    if (!reportData.length) return Toast.error('No data to export');
    const headers = ['Date','Reference','Supplier','Items','Subtotal','Discount','Total','Paid','Due','Status'];
    const rows = reportData.map(p => [p.purchase_date, p.reference_no||'', p.supplier_name||'', p.item_count, p.subtotal, p.discount_amount, p.total_amount, p.paid_amount, (parseFloat(p.total_amount)-parseFloat(p.paid_amount)).toFixed(2), p.payment_status]);
    exportToCSV('purchase_report.csv', headers, rows);
}

loadReport();
JS;
include __DIR__ . '/includes/footer.php';
?>
