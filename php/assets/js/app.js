/**
 * APP.JS — JR Marketing (Pvt) Ltd
 * Core JavaScript for the PHP version
 * Handles AJAX calls, sidebar, common UI
 */

// ── Sidebar Toggle ──
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebar-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const sb = document.getElementById('sidebar');
            if (!sb) return;
            if (window.innerWidth <= 768) sb.classList.toggle('mobile-open');
            else sb.classList.toggle('collapsed');
        });
    }
});

// ── Sidebar Menu Toggle ──
function toggleMenu(id) {
    const item = document.querySelector(`[data-menu="${id}"]`);
    if (!item) return;
    const wasOpen = item.classList.contains('open');
    document.querySelectorAll('.menu-item.open').forEach(el => el.classList.remove('open'));
    if (!wasOpen) item.classList.add('open');
}

// ── AJAX Helper ──
const API = {
    async get(url) {
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    },

    async post(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    },

    async postForm(url, formData) {
        const res = await fetch(url, {
            method: 'POST',
            body: formData
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    },

    async delete(url) {
        const res = await fetch(url, { method: 'DELETE' });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    },

    async put(url, data) {
        const res = await fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
    }
};

// ── Format Currency ──
function fmtC(amount) {
    return 'LKR ' + parseFloat(amount || 0).toLocaleString('en-LK', { minimumFractionDigits: 2 });
}

// ── Format Date ──
function fmtD(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB');
}

// ── Sanitize HTML ──
function sanitize(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str || ''));
    return div.innerHTML;
}

// ── Tab switching helper ──
function switchTab(name, tabClass = '.tab', contentPrefix = 'tab-') {
    document.querySelectorAll(tabClass).forEach((t, i) => {
        t.classList.toggle('active', t.dataset.tab === name);
    });
    document.querySelectorAll('[id^="' + contentPrefix + '"]').forEach(el => {
        el.classList.toggle('active', el.id === contentPrefix + name);
    });
}

// ── Export to CSV ──
function exportToCSV(data, headers, filename) {
    if (!data.length) { Toast.warning('No data to export.'); return; }
    const csv = headers.join(',') + '\n' + data.map(row =>
        headers.map(h => `"${(row[h] ?? '').toString().replace(/"/g, '""')}"`).join(',')
    ).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename;
    a.click();
    Toast.success('CSV exported!');
}

// ── Confirm Delete ──
async function confirmDelete(url, message = 'Delete this record?') {
    if (!confirm(message)) return false;
    try {
        const result = await API.delete(url);
        if (result.success) {
            Toast.success(result.message || 'Deleted successfully.');
            return true;
        } else {
            Toast.error(result.message || 'Delete failed.');
            return false;
        }
    } catch (e) {
        Toast.error('Error: ' + e.message);
        return false;
    }
}
