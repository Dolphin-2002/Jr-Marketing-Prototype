<?php
$pageTitle = 'Tax Report';
$activePage = 'tax-report';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Tax Report</h1>
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

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:16px" id="stats-cards"></div>

    <div class="card">
        <div class="card-header"><h3>Taxable Sales</h3></div>
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Date</th><th>Invoice</th><th>Customer</th><th>Subtotal</th><th>Tax Amount</th><th>Tax Rate</th><th>Total</th></tr></thead>
                <tbody id="report-body"><tr><td colspan="8" class="empty-state">Loading...</td></tr></tbody>
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
        const res = await API.get(`api/reports.php?report=tax&from=${from}&to=${to}`);
        const d = res.data;
        reportData = d.sales || [];
        document.getElementById('stats-cards').innerHTML = `
            <div class="card"><div class="card-body" style="text-align:center"><h4>${d.taxable_sales}</h4><small>Taxable Sales</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--success)">${fmtC(d.total_taxable_amount)}</h4><small>Taxable Amount</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--warning)">${fmtC(d.total_tax_collected)}</h4><small>Total Tax Collected</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4>${d.effective_tax_rate}%</h4><small>Effective Tax Rate</small></div></div>
        `;
        const tbody = document.getElementById('report-body');
        if (!reportData.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No taxable sales</td></tr>'; return; }
        tbody.innerHTML = reportData.map((s, i) => {
            const rate = parseFloat(s.subtotal) > 0 ? ((parseFloat(s.tax_amount) / parseFloat(s.subtotal)) * 100).toFixed(1) : '0.0';
            return `<tr>
                <td>${i+1}</td><td>${fmtD(s.sale_date)}</td><td>${sanitize(s.invoice_no)}</td>
                <td>${sanitize(s.customer_name||'Walk-in')}</td>
                <td>${fmtC(s.subtotal)}</td>
                <td style="font-weight:700;color:var(--warning)">${fmtC(s.tax_amount)}</td>
                <td>${rate}%</td>
                <td style="font-weight:700">${fmtC(s.total_amount)}</td>
            </tr>`;
        }).join('');
    } catch(e) { Toast.error('Failed to load report'); }
}

function exportCSV() {
    if (!reportData.length) return Toast.error('No data to export');
    const headers = ['Date','Invoice','Customer','Subtotal','Tax Amount','Tax Rate','Total'];
    const rows = reportData.map(s => {
        const rate = parseFloat(s.subtotal) > 0 ? ((parseFloat(s.tax_amount) / parseFloat(s.subtotal)) * 100).toFixed(1) : '0.0';
        return [s.sale_date, s.invoice_no, s.customer_name||'Walk-in', s.subtotal, s.tax_amount, rate+'%', s.total_amount];
    });
    exportToCSV('tax_report.csv', headers, rows);
}

loadReport();
JS;
include __DIR__ . '/includes/footer.php';
?>
