<?php
$pageTitle = 'Expenses';
$activePage = 'expenses';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Expenses</h1>
        <div class="toolbar-actions">
            <div class="tab-group">
                <button class="tab active" data-tab="list" onclick="switchTab('list')">All Expenses</button>
                <button class="tab" data-tab="add" onclick="switchTab('add')">Add Expense</button>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="tab-content active" id="tab-list">
        <div class="card">
            <div class="card-header" style="display:flex;gap:8px;align-items:center">
                <input type="date" id="fl-from" class="form-control" style="width:140px">
                <input type="date" id="fl-to" class="form-control" style="width:140px">
                <select id="fl-cat" class="form-control" style="width:160px"><option value="">All Categories</option></select>
                <button class="btn btn-primary btn-sm" onclick="loadExpenses()">Filter</button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>#</th><th>Date</th><th>Category</th><th>Ref#</th><th>Description</th><th>Amount</th><th>Method</th><th>Actions</th></tr></thead>
                    <tbody id="expense-body"><tr><td colspan="8" class="empty-state">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add -->
    <div class="tab-content" id="tab-add">
        <div class="card">
            <div class="card-header"><h3 id="exp-form-title">Add Expense</h3></div>
            <div class="card-body">
                <form id="exp-form" onsubmit="saveExpense(event)">
                    <input type="hidden" id="edit-id">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div class="form-group"><label class="form-label">Date *</label><input type="date" id="e-date" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Category *</label><select id="e-category" class="form-control" required><option value="">Select</option></select></div>
                        <div class="form-group"><label class="form-label">Amount *</label><input type="number" id="e-amount" class="form-control" step="0.01" required></div>
                        <div class="form-group"><label class="form-label">Payment Method</label><select id="e-method" class="form-control"><option>Cash</option><option>Card</option><option>Bank Transfer</option><option>Cheque</option></select></div>
                        <div class="form-group"><label class="form-label">Reference No</label><input type="text" id="e-ref" class="form-control"></div>
                        <div class="form-group"><label class="form-label">Paid By</label><input type="text" id="e-paid-by" class="form-control"></div>
                        <div class="form-group" style="grid-column:1/-1"><label class="form-label">Description</label><textarea id="e-desc" class="form-control" rows="2"></textarea></div>
                    </div>
                    <div style="margin-top:16px;display:flex;gap:8px">
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                        <button type="button" class="btn btn-outline" onclick="resetForm();switchTab('list')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let categories = [];
document.getElementById('fl-from').value = new Date().toISOString().slice(0,8) + '01';
document.getElementById('fl-to').value = new Date().toISOString().slice(0,10);
document.getElementById('e-date').value = new Date().toISOString().slice(0,10);

async function init() {
    try {
        const cRes = await API.get('api/expenses.php?action=categories');
        categories = cRes.data || [];
        const opts = categories.map(c => `<option value="${c.id}">${sanitize(c.name)}</option>`).join('');
        document.getElementById('fl-cat').innerHTML = '<option value="">All Categories</option>' + opts;
        document.getElementById('e-category').innerHTML = '<option value="">Select</option>' + opts;
        loadExpenses();
    } catch(e) { Toast.error('Failed to load categories'); }
}

async function loadExpenses() {
    try {
        const from = document.getElementById('fl-from').value;
        const to = document.getElementById('fl-to').value;
        const cat = document.getElementById('fl-cat').value;
        const res = await API.get(`api/expenses.php?from=${from}&to=${to}&category_id=${cat}`);
        const data = res.data || [];
        const tbody = document.getElementById('expense-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No expenses found</td></tr>'; return; }
        tbody.innerHTML = data.map((e, i) => `<tr>
            <td>${i+1}</td>
            <td>${fmtD(e.expense_date)}</td>
            <td><span class="badge badge-info">${sanitize(e.category_name||'—')}</span></td>
            <td>${sanitize(e.reference_no||'—')}</td>
            <td>${sanitize(e.description||'—')}</td>
            <td style="color:var(--danger);font-weight:700">${fmtC(e.amount)}</td>
            <td>${sanitize(e.payment_method)}</td>
            <td class="action-btns">
                <button class="btn btn-sm btn-primary" onclick="editExpense(${e.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteExpense(${e.id})">Del</button>
            </td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load expenses'); }
}

async function saveExpense(ev) {
    ev.preventDefault();
    const id = document.getElementById('edit-id').value;
    const data = {
        expense_date: document.getElementById('e-date').value,
        category_id: document.getElementById('e-category').value || null,
        amount: parseFloat(document.getElementById('e-amount').value) || 0,
        payment_method: document.getElementById('e-method').value,
        reference_no: document.getElementById('e-ref').value.trim(),
        paid_by: document.getElementById('e-paid-by').value.trim(),
        description: document.getElementById('e-desc').value.trim()
    };
    try {
        let res;
        if (id) { data.id = id; res = await API.put('api/expenses.php', data); }
        else res = await API.post('api/expenses.php', data);
        if (res.success) { Toast.success(res.message); resetForm(); switchTab('list'); loadExpenses(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function editExpense(id) {
    try {
        const res = await API.get('api/expenses.php?id=' + id);
        const e = res.data;
        document.getElementById('edit-id').value = e.id;
        document.getElementById('e-date').value = e.expense_date;
        document.getElementById('e-category').value = e.category_id || '';
        document.getElementById('e-amount').value = e.amount;
        document.getElementById('e-method').value = e.payment_method;
        document.getElementById('e-ref').value = e.reference_no || '';
        document.getElementById('e-paid-by').value = e.paid_by || '';
        document.getElementById('e-desc').value = e.description || '';
        document.getElementById('exp-form-title').textContent = 'Edit Expense';
        switchTab('add');
    } catch(e) { Toast.error('Failed to load expense'); }
}

function resetForm() {
    document.getElementById('exp-form').reset();
    document.getElementById('edit-id').value = '';
    document.getElementById('e-date').value = new Date().toISOString().slice(0,10);
    document.getElementById('exp-form-title').textContent = 'Add Expense';
}

async function deleteExpense(id) {
    if (await confirmDelete('api/expenses.php?id=' + id, 'Delete this expense?')) loadExpenses();
}

if (location.hash === '#add') setTimeout(() => switchTab('add'), 100);
init();
JS;
include __DIR__ . '/includes/footer.php';
?>
