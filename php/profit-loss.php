<?php
$pageTitle = 'Profit & Loss Report';
$activePage = 'profit-loss';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Profit & Loss Report</h1>
    </div>

    <div class="card" style="margin-bottom:16px">
        <div class="card-header" style="display:flex;gap:8px;align-items:center">
            <input type="date" id="fl-from" class="form-control" style="width:140px">
            <input type="date" id="fl-to" class="form-control" style="width:140px">
            <button class="btn btn-primary btn-sm" onclick="loadReport()">Generate</button>
        </div>
    </div>

    <div id="report-content">
        <div class="empty-state">Select date range and click Generate</div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
document.getElementById('fl-from').value = new Date().toISOString().slice(0,8) + '01';
document.getElementById('fl-to').value = new Date().toISOString().slice(0,10);

async function loadReport() {
    const from = document.getElementById('fl-from').value;
    const to = document.getElementById('fl-to').value;
    try {
        const res = await API.get(`api/reports.php?report=profit-loss&from=${from}&to=${to}`);
        const d = res.data;
        const net = d.net_profit;
        const cls = net >= 0 ? 'color:var(--success)' : 'color:var(--danger)';
        document.getElementById('report-content').innerHTML = `
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
                <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--success)">${fmtC(d.total_revenue)}</h4><small>Total Revenue</small></div></div>
                <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--warning)">${fmtC(d.cost_of_goods)}</h4><small>Cost of Goods</small></div></div>
                <div class="card"><div class="card-body" style="text-align:center"><h4>${fmtC(d.gross_profit)}</h4><small>Gross Profit</small></div></div>
                <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--danger)">${fmtC(d.total_expenses)}</h4><small>Total Expenses</small></div></div>
                <div class="card"><div class="card-body" style="text-align:center"><h4 style="color:var(--danger)">${fmtC(d.sale_returns)}</h4><small>Sale Returns</small></div></div>
                <div class="card"><div class="card-body" style="text-align:center"><h4 style="${cls};font-size:1.5rem">${fmtC(net)}</h4><small>Net Profit</small></div></div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Summary</h3></div>
                <div class="card-body">
                    <table class="table">
                        <tr><td>Total Revenue (Sales)</td><td style="text-align:right;font-weight:700;color:var(--success)">${fmtC(d.total_revenue)}</td></tr>
                        <tr><td>Less: Cost of Goods Sold</td><td style="text-align:right;color:var(--danger)">- ${fmtC(d.cost_of_goods)}</td></tr>
                        <tr style="font-weight:700;background:var(--bg-light)"><td>Gross Profit</td><td style="text-align:right">${fmtC(d.gross_profit)}</td></tr>
                        <tr><td>Less: Operating Expenses</td><td style="text-align:right;color:var(--danger)">- ${fmtC(d.total_expenses)}</td></tr>
                        <tr><td>Less: Sale Returns</td><td style="text-align:right;color:var(--danger)">- ${fmtC(d.sale_returns)}</td></tr>
                        <tr style="font-weight:700;font-size:1.1rem;background:var(--bg-light)"><td>Net Profit</td><td style="text-align:right;${cls}">${fmtC(net)}</td></tr>
                    </table>
                </div>
            </div>
        `;
    } catch(e) { Toast.error('Failed to load report'); }
}

loadReport();
JS;
include __DIR__ . '/includes/footer.php';
?>
