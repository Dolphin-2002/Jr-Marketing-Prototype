<?php
$pageTitle = 'Sales Report';
$activePage = 'sales-report';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Sales Report</h1>
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
            <table class="table" id="report-table">
                <thead><tr><th>#</th><th>Date</th><th>Invoice</th><th>Customer</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Tax</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th></tr></thead>
                <tbody id="report-body"><tr><td colspan="12" class="empty-state">Loading...</td></tr></tbody>
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
        const res = await API.get(`api/reports.php?report=sales&from=${from}&to=${to}`);
        const d = res.data;
        reportData = d.sales || [];
        document.getElementById('stats-cards').innerHTML = `
            <div class="card"><div class="card-body" style="text-align:center"><h4>${d.total_sales}</h4><small>Total Sales</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--success)">${fmtC(d.total_amount)}</h4><small>Total Amount</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--success)">${fmtC(d.total_paid)}</h4><small>Total Paid</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--danger)">${fmtC(d.total_due)}</h4><small>Total Due</small></div></div>
        `;
        const tbody = document.getElementById('report-body');
        if (!reportData.length) { tbody.innerHTML = '<tr><td colspan="12" class="empty-state">No sales</td></tr>'; return; }
        tbody.innerHTML = reportData.map((s, i) => {
            const due = parseFloat(s.total_amount) - parseFloat(s.paid_amount);
            const badge = s.payment_status === 'paid' ? 'badge-success' : s.payment_status === 'partial' ? 'badge-warning' : 'badge-danger';
            return `<tr>
                <td>${i+1}</td><td>${fmtD(s.sale_date)}</td><td>${sanitize(s.invoice_no)}</td>
                <td>${sanitize(s.customer_name||'Walk-in')}</td><td>${s.item_count}</td>
                <td>${fmtC(s.subtotal)}</td><td>${fmtC(s.discount_amount)}</td><td>${fmtC(s.tax_amount)}</td>
                <td style="font-weight:700">${fmtC(s.total_amount)}</td><td>${fmtC(s.paid_amount)}</td>
                <td style="color:var(--danger)">${fmtC(due)}</td>
                <td><span class="badge ${badge}">${s.payment_status}</span></td>
            </tr>`;
        }).join('');
    } catch(e) { Toast.error('Failed to load report'); }
}

function exportCSV() {
    if (!reportData.length) return Toast.error('No data to export');
    const headers = ['Date','Invoice','Customer','Items','Subtotal','Discount','Tax','Total','Paid','Due','Status'];
    const rows = reportData.map(s => [s.sale_date, s.invoice_no, s.customer_name||'Walk-in', s.item_count, s.subtotal, s.discount_amount, s.tax_amount, s.total_amount, s.paid_amount, (parseFloat(s.total_amount)-parseFloat(s.paid_amount)).toFixed(2), s.payment_status]);
    exportToCSV('sales_report.csv', headers, rows);
}

loadReport();
JS;
include __DIR__ . '/includes/footer.php';
?>
