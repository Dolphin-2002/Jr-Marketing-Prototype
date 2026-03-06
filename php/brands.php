<?php
$pageTitle = 'Brands';
$activePage = 'brands';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Brands</h1>
        <div class="toolbar-actions">
            <button class="btn btn-primary" onclick="showAdd()"><i class="bi bi-plus-circle"></i> Add Brand</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody id="brand-body"><tr><td colspan="4" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="modal" style="display:none" onclick="if(event.target===this)closeModal()">
        <div class="modal-dialog" style="max-width:450px">
            <div class="modal-header">
                <h3 id="modal-title">Add Brand</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="brand-form" onsubmit="saveBrand(event)">
                    <input type="hidden" id="edit-id">
                    <div class="form-group"><label class="form-label">Name *</label><input type="text" id="b-name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Description</label><textarea id="b-desc" class="form-control" rows="2"></textarea></div>
                    <div style="margin-top:16px;display:flex;gap:8px">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
async function loadBrands() {
    try {
        const res = await API.get('api/brands.php');
        const data = res.data || [];
        const tbody = document.getElementById('brand-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No brands</td></tr>'; return; }
        tbody.innerHTML = data.map((b, i) => `<tr>
            <td>${i+1}</td><td>${sanitize(b.name)}</td><td>${sanitize(b.description||'—')}</td>
            <td class="action-btns">
                <button class="btn btn-sm btn-primary" onclick='editBrand(${JSON.stringify(b)})'>Edit</button>
                <button class="btn btn-sm btn-danger" onclick="delBrand(${b.id})">Del</button>
            </td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load brands'); }
}

function showAdd() {
    document.getElementById('edit-id').value = '';
    document.getElementById('brand-form').reset();
    document.getElementById('modal-title').textContent = 'Add Brand';
    document.getElementById('modal').style.display = 'flex';
}

function editBrand(b) {
    document.getElementById('edit-id').value = b.id;
    document.getElementById('b-name').value = b.name;
    document.getElementById('b-desc').value = b.description || '';
    document.getElementById('modal-title').textContent = 'Edit Brand';
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() { document.getElementById('modal').style.display = 'none'; }

async function saveBrand(ev) {
    ev.preventDefault();
    const id = document.getElementById('edit-id').value;
    const data = { name: document.getElementById('b-name').value.trim(), description: document.getElementById('b-desc').value.trim() };
    try {
        let res;
        if (id) { data.id = id; res = await API.put('api/brands.php', data); }
        else res = await API.post('api/brands.php', data);
        if (res.success) { Toast.success(res.message); closeModal(); loadBrands(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function delBrand(id) {
    if (await confirmDelete('api/brands.php?id=' + id, 'Delete this brand?')) loadBrands();
}

loadBrands();
JS;
include __DIR__ . '/includes/footer.php';
?>
