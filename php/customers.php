<?php
$pageTitle = 'Customers';
$activePage = 'customers';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Customers</h1>
        <div class="toolbar-actions">
            <input type="text" id="search" class="form-control" placeholder="Search customers..." style="width:240px" oninput="renderList()">
            <button class="btn btn-primary" onclick="showForm()">+ Add Customer</button>
        </div>
    </div>

    <!-- List -->
    <div class="card" id="list-view">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Contact ID</th><th>Name</th><th>Mobile</th><th>Type</th><th>Total Due</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="table-body"><tr><td colspan="8" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Form -->
    <div class="card" id="form-view" style="display:none">
        <div class="card-header"><h3 id="form-title">Add Customer</h3></div>
        <div class="card-body">
            <form id="cust-form" onsubmit="saveItem(event)">
                <input type="hidden" id="edit-id">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label class="form-label">Customer Type</label>
                        <select id="f-type" class="form-control"><option value="individual">Individual</option><option value="business">Business</option></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Prefix</label>
                        <select id="f-prefix" class="form-control"><option value="Mr">Mr</option><option value="Mrs">Mrs</option><option value="Ms">Ms</option></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Name *</label>
                        <input type="text" id="f-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Business Name</label>
                        <input type="text" id="f-business" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mobile *</label>
                        <input type="text" id="f-mobile" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" id="f-address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" id="f-city" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <input type="text" id="f-state" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">ZIP</label>
                        <input type="text" id="f-zip" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Credit Limit</label>
                        <input type="number" id="f-credit" class="form-control" step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pay Term</label>
                        <div style="display:flex;gap:8px">
                            <input type="number" id="f-pay-val" class="form-control" placeholder="Value" style="width:100px">
                            <select id="f-pay-unit" class="form-control"><option value="">—</option><option value="Days">Days</option><option value="Months">Months</option></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select id="f-status" class="form-control"><option value="Active">Active</option><option value="Inactive">Inactive</option></select>
                    </div>
                </div>
                <div style="margin-top:16px;display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                    <button type="button" class="btn btn-outline" onclick="hideForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal-overlay" id="view-modal" style="display:none" onclick="if(event.target===this)this.style.display='none'">
        <div class="modal">
            <div class="modal-header"><h3>Customer Details</h3><button class="modal-close" onclick="document.getElementById('view-modal').style.display='none'">&times;</button></div>
            <div class="modal-body" id="view-body"></div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let allCustomers = [];

async function loadCustomers() {
    try {
        const res = await API.get('api/customers.php');
        allCustomers = res.data || [];
        renderList();
    } catch(e) { Toast.error('Failed to load customers'); }
}

function renderList() {
    const q = document.getElementById('search').value.toLowerCase();
    const filtered = allCustomers.filter(c =>
        (c.name||'').toLowerCase().includes(q) ||
        (c.contact_id||'').toLowerCase().includes(q) ||
        (c.mobile||'').includes(q) ||
        (c.business_name||'').toLowerCase().includes(q)
    );
    const tbody = document.getElementById('table-body');
    if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No customers found</td></tr>'; return; }
    tbody.innerHTML = filtered.map((c, i) => `<tr>
        <td>${i+1}</td>
        <td><strong>${sanitize(c.contact_id)}</strong></td>
        <td>${sanitize(c.name)}</td>
        <td>${sanitize(c.mobile)}</td>
        <td><span class="badge badge-${c.type==='business'?'info':'default'}">${c.type}</span></td>
        <td style="color:var(--danger);font-weight:600">${fmtC(c.total_sale_due)}</td>
        <td><span class="badge badge-${c.status==='Active'?'success':'danger'}">${c.status}</span></td>
        <td class="action-btns">
            <button class="btn btn-sm btn-outline" onclick="viewItem(${c.id})">View</button>
            <button class="btn btn-sm btn-primary" onclick="editItem(${c.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteItem(${c.id})">Del</button>
        </td>
    </tr>`).join('');
}

function showForm(customer) {
    document.getElementById('list-view').style.display = 'none';
    document.getElementById('form-view').style.display = 'block';
    document.getElementById('form-title').textContent = customer ? 'Edit Customer' : 'Add Customer';
    if (customer) {
        document.getElementById('edit-id').value = customer.id;
        document.getElementById('f-type').value = customer.type || 'individual';
        document.getElementById('f-prefix').value = customer.prefix || 'Mr';
        document.getElementById('f-name').value = customer.name || '';
        document.getElementById('f-business').value = customer.business_name || '';
        document.getElementById('f-mobile').value = customer.mobile || '';
        document.getElementById('f-address').value = customer.address || '';
        document.getElementById('f-city').value = customer.city || '';
        document.getElementById('f-state').value = customer.state || '';
        document.getElementById('f-zip').value = customer.zip || '';
        document.getElementById('f-credit').value = customer.credit_limit || '';
        document.getElementById('f-pay-val').value = customer.pay_term_val || '';
        document.getElementById('f-pay-unit').value = customer.pay_term_unit || '';
        document.getElementById('f-status').value = customer.status || 'Active';
    } else {
        document.getElementById('cust-form').reset();
        document.getElementById('edit-id').value = '';
    }
}

function hideForm() {
    document.getElementById('form-view').style.display = 'none';
    document.getElementById('list-view').style.display = 'block';
}

async function saveItem(e) {
    e.preventDefault();
    const id = document.getElementById('edit-id').value;
    const data = {
        type: document.getElementById('f-type').value,
        prefix: document.getElementById('f-prefix').value,
        name: document.getElementById('f-name').value.trim(),
        first_name: document.getElementById('f-name').value.trim(),
        business_name: document.getElementById('f-business').value.trim(),
        mobile: document.getElementById('f-mobile').value.trim(),
        address: document.getElementById('f-address').value.trim(),
        city: document.getElementById('f-city').value.trim(),
        state: document.getElementById('f-state').value.trim(),
        zip: document.getElementById('f-zip').value.trim(),
        credit_limit: document.getElementById('f-credit').value || null,
        pay_term_val: document.getElementById('f-pay-val').value || null,
        pay_term_unit: document.getElementById('f-pay-unit').value,
        status: document.getElementById('f-status').value
    };
    try {
        let res;
        if (id) {
            data.id = id;
            res = await API.put('api/customers.php', data);
        } else {
            res = await API.post('api/customers.php', data);
        }
        if (res.success) {
            Toast.success(res.message);
            hideForm();
            loadCustomers();
        } else {
            Toast.error(res.message);
        }
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function editItem(id) {
    const c = allCustomers.find(x => x.id == id);
    if (c) showForm(c);
}

async function viewItem(id) {
    const c = allCustomers.find(x => x.id == id);
    if (!c) return;
    document.getElementById('view-body').innerHTML = `
        <table class="table"><tbody>
        <tr><td><strong>Contact ID</strong></td><td>${sanitize(c.contact_id)}</td></tr>
        <tr><td><strong>Name</strong></td><td>${sanitize(c.name)}</td></tr>
        <tr><td><strong>Type</strong></td><td>${c.type}</td></tr>
        <tr><td><strong>Business</strong></td><td>${sanitize(c.business_name||'—')}</td></tr>
        <tr><td><strong>Mobile</strong></td><td>${sanitize(c.mobile)}</td></tr>
        <tr><td><strong>Address</strong></td><td>${sanitize([c.address,c.city,c.state,c.zip].filter(Boolean).join(', ')||'—')}</td></tr>
        <tr><td><strong>Credit Limit</strong></td><td>${c.credit_limit ? fmtC(c.credit_limit) : '—'}</td></tr>
        <tr><td><strong>Total Sale Due</strong></td><td style="color:var(--danger);font-weight:700">${fmtC(c.total_sale_due)}</td></tr>
        <tr><td><strong>Status</strong></td><td><span class="badge badge-${c.status==='Active'?'success':'danger'}">${c.status}</span></td></tr>
        <tr><td><strong>Created</strong></td><td>${fmtD(c.created_at)}</td></tr>
        </tbody></table>`;
    document.getElementById('view-modal').style.display = 'flex';
}

async function deleteItem(id) {
    if (await confirmDelete('api/customers.php?id=' + id, 'Delete this customer?')) loadCustomers();
}

loadCustomers();
JS;
include __DIR__ . '/includes/footer.php';
?>
