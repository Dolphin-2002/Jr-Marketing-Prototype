<?php
$pageTitle = 'Add Sale';
$activePage = 'add-sale';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="main-content">
    <div class="page-toolbar">
        <h1 class="page-title">Add Sale</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="sale-form" onsubmit="saveSale(event)">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px">
                    <div class="form-group"><label class="form-label">Customer</label><select id="s-customer" class="form-control"><option value="">Walk-In Customer</option></select></div>
                    <div class="form-group"><label class="form-label">Sale Date</label><input type="date" id="s-date" class="form-control"></div>
                    <div class="form-group"><label class="form-label">Sale Type</label>
                        <select id="s-type" class="form-control"><option value="sale">Sale</option><option value="quotation">Quotation</option><option value="credit">Credit Sale</option><option value="cheque">Cheque</option></select>
                    </div>
                </div>

                <!-- Items -->
                <h4 style="margin-bottom:8px">Sale Items</h4>
                <div style="display:flex;gap:8px;margin-bottom:12px">
                    <select id="s-product" class="form-control" style="flex:2"><option value="">Select Product</option></select>
                    <input type="number" id="s-qty" class="form-control" placeholder="Qty" style="width:100px" value="1" min="1">
                    <input type="number" id="s-price" class="form-control" placeholder="Price" style="width:140px" step="0.01">
                    <button type="button" class="btn btn-success btn-sm" onclick="addItem()">Add</button>
                </div>
                <table class="table">
                    <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody id="items-body"></tbody>
                </table>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-top:16px">
                    <div class="form-group"><label class="form-label">Discount (LKR)</label><input type="number" id="s-discount" class="form-control" value="0" step="0.01" oninput="calcTotals()"></div>
                    <div class="form-group"><label class="form-label">Tax (LKR)</label><input type="number" id="s-tax" class="form-control" value="0" step="0.01" oninput="calcTotals()"></div>
                    <div class="form-group"><label class="form-label">Payment Amount</label><input type="number" id="s-paid" class="form-control" value="0" step="0.01"></div>
                    <div class="form-group"><label class="form-label">Payment Method</label><select id="s-method" class="form-control"><option>Cash</option><option>Card</option><option>Bank Transfer</option><option>Cheque</option></select></div>
                </div>

                <!-- Totals -->
                <div style="background:var(--bg-secondary);border-radius:8px;padding:16px;margin:16px 0">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span>Subtotal:</span><strong id="t-subtotal">LKR 0.00</strong></div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span>Discount:</span><strong id="t-discount">LKR 0.00</strong></div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px"><span>Tax:</span><strong id="t-tax">LKR 0.00</strong></div>
                    <div style="display:flex;justify-content:space-between;font-size:1.2em;border-top:2px solid var(--border-color);padding-top:8px"><span><strong>Total Payable:</strong></span><strong id="t-total" style="color:var(--primary)">LKR 0.00</strong></div>
                </div>

                <div class="form-group"><label class="form-label">Notes</label><textarea id="s-note" class="form-control" rows="2"></textarea></div>

                <div style="display:flex;gap:8px">
                    <button type="submit" class="btn btn-primary">Save Sale</button>
                    <a href="sales.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
$extraJs = <<<'JS'
let saleItems = [], allProducts = [];

document.getElementById('s-date').value = new Date().toISOString().slice(0,10);

async function init() {
    const [pRes, cRes] = await Promise.all([API.get('api/products.php'), API.get('api/customers.php')]);
    allProducts = pRes.data || [];
    const customers = cRes.data || [];
    document.getElementById('s-customer').innerHTML = '<option value="">Walk-In Customer</option>' + customers.map(c => `<option value="${c.id}">${sanitize(c.name)} (${sanitize(c.contact_id)})</option>`).join('');
    document.getElementById('s-product').innerHTML = '<option value="">Select Product</option>' + allProducts.map(p => `<option value="${p.id}" data-price="${p.selling_price}" data-name="${sanitize(p.name)}">${sanitize(p.name)} (${sanitize(p.sku)})</option>`).join('');
    document.getElementById('s-product').addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt.dataset.price) document.getElementById('s-price').value = opt.dataset.price;
    });
}

function addItem() {
    const sel = document.getElementById('s-product');
    const productId = sel.value;
    const opt = sel.options[sel.selectedIndex];
    const productName = opt?.dataset?.name || opt?.text || '';
    const qty = parseFloat(document.getElementById('s-qty').value) || 0;
    const price = parseFloat(document.getElementById('s-price').value) || 0;
    if (!productId || qty <= 0 || price <= 0) { Toast.warning('Select product and enter qty/price'); return; }
    saleItems.push({ product_id: productId, product_name: productName, quantity: qty, unit_price: price, subtotal: qty * price });
    renderItems();
    sel.value = ''; document.getElementById('s-qty').value = 1; document.getElementById('s-price').value = '';
}

function removeItem(i) { saleItems.splice(i, 1); renderItems(); }

function renderItems() {
    document.getElementById('items-body').innerHTML = saleItems.map((item, i) => `<tr>
        <td>${sanitize(item.product_name)}</td><td>${item.quantity}</td><td>${fmtC(item.unit_price)}</td><td>${fmtC(item.subtotal)}</td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${i})">×</button></td>
    </tr>`).join('');
    calcTotals();
}

function calcTotals() {
    const subtotal = saleItems.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat(document.getElementById('s-discount').value) || 0;
    const tax = parseFloat(document.getElementById('s-tax').value) || 0;
    const total = subtotal - discount + tax;
    document.getElementById('t-subtotal').textContent = fmtC(subtotal);
    document.getElementById('t-discount').textContent = fmtC(discount);
    document.getElementById('t-tax').textContent = fmtC(tax);
    document.getElementById('t-total').textContent = fmtC(total);
}

async function saveSale(e) {
    e.preventDefault();
    if (!saleItems.length) { Toast.warning('Add at least one item'); return; }
    const custSel = document.getElementById('s-customer');
    const data = {
        customer_id: custSel.value || null,
        customer_name: custSel.value ? custSel.options[custSel.selectedIndex].text.replace(/\s*\(.*\)/, '') : 'Walk-In Customer',
        sale_date: document.getElementById('s-date').value,
        sale_type: document.getElementById('s-type').value,
        items: saleItems,
        discount: parseFloat(document.getElementById('s-discount').value) || 0,
        tax: parseFloat(document.getElementById('s-tax').value) || 0,
        total_paid: parseFloat(document.getElementById('s-paid').value) || 0,
        payment_method: document.getElementById('s-method').value,
        note: document.getElementById('s-note').value
    };
    try {
        const res = await API.post('api/sales.php', data);
        if (res.success) {
            Toast.success(res.message + ' Invoice: ' + res.invoice_no);
            setTimeout(() => location.href = 'sales.php', 800);
        } else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

init();
JS;
include __DIR__ . '/includes/footer.php';
?>
