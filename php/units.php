<?php
$pageTitle = 'Units';
$activePage = 'units';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Units</h1>
        <div class="toolbar-actions">
            <button class="btn btn-primary" onclick="showAdd()"><i class="bi bi-plus-circle"></i> Add Unit</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Name</th><th>Short Name</th><th>Actions</th></tr></thead>
                <tbody id="unit-body"><tr><td colspan="4" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="modal" style="display:none" onclick="if(event.target===this)closeModal()">
        <div class="modal-dialog" style="max-width:400px">
            <div class="modal-header">
                <h3 id="modal-title">Add Unit</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="unit-form" onsubmit="saveUnit(event)">
                    <input type="hidden" id="edit-id">
                    <div class="form-group"><label class="form-label">Name *</label><input type="text" id="u-name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Short Name *</label><input type="text" id="u-short" class="form-control" required></div>
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
async function loadUnits() {
    try {
        const res = await API.get('api/units.php');
        const data = res.data || [];
        const tbody = document.getElementById('unit-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No units</td></tr>'; return; }
        tbody.innerHTML = data.map((u, i) => `<tr>
            <td>${i+1}</td><td>${sanitize(u.name)}</td><td>${sanitize(u.short_name)}</td>
            <td class="action-btns">
                <button class="btn btn-sm btn-primary" onclick='editUnit(${JSON.stringify(u)})'>Edit</button>
                <button class="btn btn-sm btn-danger" onclick="delUnit(${u.id})">Del</button>
            </td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load units'); }
}

function showAdd() {
    document.getElementById('edit-id').value = '';
    document.getElementById('unit-form').reset();
    document.getElementById('modal-title').textContent = 'Add Unit';
    document.getElementById('modal').style.display = 'flex';
}

function editUnit(u) {
    document.getElementById('edit-id').value = u.id;
    document.getElementById('u-name').value = u.name;
    document.getElementById('u-short').value = u.short_name;
    document.getElementById('modal-title').textContent = 'Edit Unit';
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() { document.getElementById('modal').style.display = 'none'; }

async function saveUnit(ev) {
    ev.preventDefault();
    const id = document.getElementById('edit-id').value;
    const data = { name: document.getElementById('u-name').value.trim(), short_name: document.getElementById('u-short').value.trim() };
    try {
        let res;
        if (id) { data.id = id; res = await API.put('api/units.php', data); }
        else res = await API.post('api/units.php', data);
        if (res.success) { Toast.success(res.message); closeModal(); loadUnits(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function delUnit(id) {
    if (await confirmDelete('api/units.php?id=' + id, 'Delete this unit?')) loadUnits();
}

loadUnits();
JS;
include __DIR__ . '/includes/footer.php';
?>
