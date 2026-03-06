<?php
$pageTitle = 'Categories';
$activePage = 'categories';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Categories</h1>
        <div class="toolbar-actions">
            <button class="btn btn-primary" onclick="showAdd()"><i class="bi bi-plus-circle"></i> Add Category</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Name</th><th>Description</th><th>Actions</th></tr></thead>
                <tbody id="cat-body"><tr><td colspan="4" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="modal-overlay" id="modal" style="display:none" onclick="if(event.target===this)closeModal()">
        <div class="modal-dialog" style="max-width:450px">
            <div class="modal-header">
                <h3 id="modal-title">Add Category</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="cat-form" onsubmit="saveCat(event)">
                    <input type="hidden" id="edit-id">
                    <div class="form-group"><label class="form-label">Name *</label><input type="text" id="c-name" class="form-control" required></div>
                    <div class="form-group"><label class="form-label">Description</label><textarea id="c-desc" class="form-control" rows="2"></textarea></div>
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
async function loadCats() {
    try {
        const res = await API.get('api/categories.php');
        const data = res.data || [];
        const tbody = document.getElementById('cat-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No categories</td></tr>'; return; }
        tbody.innerHTML = data.map((c, i) => `<tr>
            <td>${i+1}</td><td>${sanitize(c.name)}</td><td>${sanitize(c.description||'—')}</td>
            <td class="action-btns">
                <button class="btn btn-sm btn-primary" onclick='editCat(${JSON.stringify(c)})'>Edit</button>
                <button class="btn btn-sm btn-danger" onclick="delCat(${c.id})">Del</button>
            </td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load categories'); }
}

function showAdd() {
    document.getElementById('edit-id').value = '';
    document.getElementById('cat-form').reset();
    document.getElementById('modal-title').textContent = 'Add Category';
    document.getElementById('modal').style.display = 'flex';
}

function editCat(c) {
    document.getElementById('edit-id').value = c.id;
    document.getElementById('c-name').value = c.name;
    document.getElementById('c-desc').value = c.description || '';
    document.getElementById('modal-title').textContent = 'Edit Category';
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() { document.getElementById('modal').style.display = 'none'; }

async function saveCat(ev) {
    ev.preventDefault();
    const id = document.getElementById('edit-id').value;
    const data = { name: document.getElementById('c-name').value.trim(), description: document.getElementById('c-desc').value.trim() };
    try {
        let res;
        if (id) { data.id = id; res = await API.put('api/categories.php', data); }
        else res = await API.post('api/categories.php', data);
        if (res.success) { Toast.success(res.message); closeModal(); loadCats(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function delCat(id) {
    if (await confirmDelete('api/categories.php?id=' + id, 'Delete this category?')) loadCats();
}

loadCats();
JS;
include __DIR__ . '/includes/footer.php';
?>
