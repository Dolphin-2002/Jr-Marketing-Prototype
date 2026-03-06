<?php
$pageTitle = 'Suppliers';
$activePage = 'suppliers';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Suppliers</h1>
        <div class="toolbar-actions">
            <input type="text" id="search" class="form-control" placeholder="Search suppliers..." style="width:240px" oninput="renderList()">
            <button class="btn btn-primary" onclick="showForm()">+ Add Supplier</button>
        </div>
    </div>

    <div class="card" id="list-view">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Contact ID</th><th>Company</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Purchase Due</th><th>Actions</th></tr></thead>
                <tbody id="table-body"><tr><td colspan="8" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="card" id="form-view" style="display:none">
        <div class="card-header"><h3 id="form-title">Add Supplier</h3></div>
        <div class="card-body">
            <form id="supp-form" onsubmit="saveItem(event)">
                <input type="hidden" id="edit-id">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group"><label class="form-label">Company Name *</label><input type="text" id="f-company" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Contact Person</label><input type="text" id="f-contact" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Phone *</label><input type="text" id="f-phone" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Email</label><input type="email" id="f-email" class="form-control"></div>
                </div>
                <div style="margin-top:16px;display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                    <button type="button" class="btn btn-outline" onclick="hideForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let allSuppliers = [];

async function loadSuppliers() {
    try {
        const res = await API.get('api/suppliers.php');
        allSuppliers = res.data || [];
        renderList();
    } catch(e) { Toast.error('Failed to load suppliers'); }
}

function renderList() {
    const q = document.getElementById('search').value.toLowerCase();
    const filtered = allSuppliers.filter(s =>
        (s.company||'').toLowerCase().includes(q) ||
        (s.contact_id||'').toLowerCase().includes(q) ||
        (s.contact_person||'').toLowerCase().includes(q) ||
        (s.phone||'').includes(q)
    );
    const tbody = document.getElementById('table-body');
    if (!filtered.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No suppliers found</td></tr>'; return; }
    tbody.innerHTML = filtered.map((s, i) => `<tr>
        <td>${i+1}</td>
        <td><strong>${sanitize(s.contact_id)}</strong></td>
        <td>${sanitize(s.company)}</td>
        <td>${sanitize(s.contact_person||'—')}</td>
        <td>${sanitize(s.phone)}</td>
        <td>${sanitize(s.email||'—')}</td>
        <td style="color:var(--danger);font-weight:600">${fmtC(s.total_purchase_due)}</td>
        <td class="action-btns">
            <button class="btn btn-sm btn-primary" onclick="editItem(${s.id})">Edit</button>
            <button class="btn btn-sm btn-danger" onclick="deleteItem(${s.id})">Del</button>
        </td>
    </tr>`).join('');
}

function showForm(supplier) {
    document.getElementById('list-view').style.display = 'none';
    document.getElementById('form-view').style.display = 'block';
    document.getElementById('form-title').textContent = supplier ? 'Edit Supplier' : 'Add Supplier';
    if (supplier) {
        document.getElementById('edit-id').value = supplier.id;
        document.getElementById('f-company').value = supplier.company || '';
        document.getElementById('f-contact').value = supplier.contact_person || '';
        document.getElementById('f-phone').value = supplier.phone || '';
        document.getElementById('f-email').value = supplier.email || '';
    } else {
        document.getElementById('supp-form').reset();
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
        company: document.getElementById('f-company').value.trim(),
        contact_person: document.getElementById('f-contact').value.trim(),
        phone: document.getElementById('f-phone').value.trim(),
        email: document.getElementById('f-email').value.trim()
    };
    try {
        let res;
        if (id) { data.id = id; res = await API.put('api/suppliers.php', data); }
        else { res = await API.post('api/suppliers.php', data); }
        if (res.success) { Toast.success(res.message); hideForm(); loadSuppliers(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

function editItem(id) {
    const s = allSuppliers.find(x => x.id == id);
    if (s) showForm(s);
}

async function deleteItem(id) {
    if (await confirmDelete('api/suppliers.php?id=' + id, 'Delete this supplier?')) loadSuppliers();
}

loadSuppliers();
JS;
include __DIR__ . '/includes/footer.php';
?>
