<?php
$pageTitle = 'Expense Categories';
$activePage = 'expense-categories';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Expense Categories</h1>
        <div class="toolbar-actions">
            <button class="btn btn-primary" onclick="showAdd()"><i class="bi bi-plus-circle"></i> Add Category</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>#</th><th>Name</th><th>Actions</th></tr></thead>
                <tbody id="cat-body"><tr><td colspan="3" class="empty-state">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modal" style="display:none" onclick="if(event.target===this)closeModal()">
        <div class="modal-dialog" style="max-width:400px">
            <div class="modal-header">
                <h3 id="modal-title">Add Category</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="cat-form" onsubmit="saveCat(event)">
                    <input type="hidden" id="edit-id">
                    <div class="form-group"><label class="form-label">Name *</label><input type="text" id="c-name" class="form-control" required></div>
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
        const res = await API.get('api/expenses.php?action=categories');
        const data = res.data || [];
        const tbody = document.getElementById('cat-body');
        if (!data.length) { tbody.innerHTML = '<tr><td colspan="3" class="empty-state">No categories</td></tr>'; return; }
        tbody.innerHTML = data.map((c, i) => `<tr>
            <td>${i+1}</td><td>${sanitize(c.name)}</td>
            <td class="action-btns">
                <button class="btn btn-sm btn-primary" onclick="editCat(${c.id}, '${sanitize(c.name).replace(/'/g,"\\'")}')">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="delCat(${c.id})">Del</button>
            </td>
        </tr>`).join('');
    } catch(e) { Toast.error('Failed to load categories'); }
}

function showAdd() {
    document.getElementById('edit-id').value = '';
    document.getElementById('c-name').value = '';
    document.getElementById('modal-title').textContent = 'Add Category';
    document.getElementById('modal').style.display = 'flex';
}

function editCat(id, name) {
    document.getElementById('edit-id').value = id;
    document.getElementById('c-name').value = name;
    document.getElementById('modal-title').textContent = 'Edit Category';
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() { document.getElementById('modal').style.display = 'none'; }

async function saveCat(ev) {
    ev.preventDefault();
    const id = document.getElementById('edit-id').value;
    const name = document.getElementById('c-name').value.trim();
    if (!name) return Toast.error('Name is required');
    try {
        let res;
        if (id) res = await API.post('api/expenses.php', { action: 'update_category', id, name });
        else res = await API.post('api/expenses.php', { action: 'add_category', name });
        if (res.success) { Toast.success(res.message); closeModal(); loadCats(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function delCat(id) {
    if (!confirm('Delete this category?')) return;
    try {
        const res = await API.delete('api/expenses.php?action=delete_category&id=' + id);
        if (res.success) { Toast.success(res.message); loadCats(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

loadCats();
JS;
include __DIR__ . '/includes/footer.php';
?>
