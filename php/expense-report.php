<?php
$pageTitle = 'Expense Report';
$activePage = 'expense-report';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Expense Report</h1>
        <div class="toolbar-actions">
            <button class="btn btn-outline btn-sm" onclick="exportCSV()"><i class="bi bi-download"></i> Export CSV</button>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div class="card-header" style="display:flex;gap:8px;align-items:center">
            <input type="date" id="fl-from" class="form-control" style="width:140px">
            <input type="date" id="fl-to" class="form-control" style="width:140px">
            <select id="fl-cat" class="form-control" style="width:160px"><option value="">All Categories</option></select>
            <button class="btn btn-primary btn-sm" onclick="loadReport()">Generate</button>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:16px" id="stats-cards"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div class="card" id="category-breakdown">
            <div class="card-header"><h3>By Category</h3></div>
            <div class="card-body"><div class="empty-state">Loading...</div></div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Expense Details</h3></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>#</th><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Method</th></tr></thead>
                    <tbody id="report-body"><tr><td colspan="6" class="empty-state">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let reportData = [];
document.getElementById('fl-from').value = new Date().toISOString().slice(0,8) + '01';
document.getElementById('fl-to').value = new Date().toISOString().slice(0,10);

(async function(){
    try {
        const cRes = await API.get('api/expenses.php?action=categories');
        const cats = cRes.data || [];
        document.getElementById('fl-cat').innerHTML = '<option value="">All Categories</option>' + cats.map(c => `<option value="${c.id}">${sanitize(c.name)}</option>`).join('');
    } catch(e) {}
    loadReport();
})();

async function loadReport() {
    const from = document.getElementById('fl-from').value;
    const to = document.getElementById('fl-to').value;
    const cat = document.getElementById('fl-cat').value;
    try {
        const res = await API.get(`api/reports.php?report=expenses&from=${from}&to=${to}&category_id=${cat}`);
        const d = res.data;
        reportData = d.expenses || [];
        const byCategory = d.by_category || [];
        document.getElementById('stats-cards').innerHTML = `
            <div class="card"><div class="card-body" style="text-align:center"><h4>${d.total_count}</h4><small>Total Expenses</small></div></div>
            <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--danger)">${fmtC(d.total_amount)}</h4><small>Total Amount</small></div></div>
        `;
        // Category breakdown
        const catCard = document.getElementById('category-breakdown');
        if (byCategory.length) {
            catCard.querySelector('.card-body').innerHTML = '<table class="table"><thead><tr><th>Category</th><th>Count</th><th>Amount</th></tr></thead><tbody>' +
                byCategory.map(c => `<tr><td>${sanitize(c.category_name)}</td><td>${c.count}</td><td style="font-weight:700;color:var(--danger)">${fmtC(c.total)}</td></tr>`).join('') +
                '</tbody></table>';
        } else {
            catCard.querySelector('.card-body').innerHTML = '<div class="empty-state">No data</div>';
        }
        // Details
        const tbody = document.getElementById('report-body');
        if (!reportData.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No expenses</td></tr>'; return; }
        tbody.innerHTML = reportData.map((e, i) => `<tr>
            <td>${i+1}</td><td>${fmtD(e.expense_date)}</td>
            <td><span class="badge badge-info">${sanitize(e.category_name||'—')}</span></td>
            <td>${sanitize(e.description||'—')}</td>
            <td style="font-weight:700;color:var(--danger)">${fmtC(e.amount)}</td>
            <td>${sanitize(e.payment_method)}</td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load report'); }
}

function exportCSV() {
    if (!reportData.length) return Toast.error('No data to export');
    const headers = ['Date','Category','Description','Amount','Method','Reference','Paid By'];
    const rows = reportData.map(e => [e.expense_date, e.category_name||'', e.description||'', e.amount, e.payment_method, e.reference_no||'', e.paid_by||'']);
    exportToCSV('expense_report.csv', headers, rows);
}
JS;
include __DIR__ . '/includes/footer.php';
?>
