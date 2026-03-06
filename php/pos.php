<?php
$pageTitle = 'Point of Sale';
$activePage = 'pos';
require_once __DIR__ . '/includes/auth.php';
Auth::requireAuth();
$extraCss = <<<'CSS'
.pos-layout{display:grid;grid-template-columns:1fr 380px;gap:0;height:calc(100vh - 60px);overflow:hidden}
.pos-products{padding:16px;overflow-y:auto}
.pos-cart{background:var(--bg-secondary);border-left:1px solid var(--border-color);display:flex;flex-direction:column;height:100%}
.pos-cart-header{padding:12px 16px;border-bottom:1px solid var(--border-color);background:white}
.pos-cart-items{flex:1;overflow-y:auto;padding:8px 16px}
.pos-cart-footer{padding:16px;border-top:1px solid var(--border-color);background:white}
.pos-search{margin-bottom:16px}
.pos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.pos-product-card{background:white;border:1px solid var(--border-color);border-radius:8px;padding:12px;cursor:pointer;transition:all 0.15s;text-align:center}
.pos-product-card:hover{border-color:var(--primary);box-shadow:0 2px 8px rgba(0,0,0,0.1)}
.pos-product-card .name{font-weight:600;font-size:0.85em;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pos-product-card .price{color:var(--primary);font-weight:700;font-size:0.95em}
.pos-product-card .stock{font-size:0.75em;color:var(--text-muted)}
.cart-item{display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-color)}
.cart-item .ci-name{flex:1;font-weight:600;font-size:0.85em}
.cart-item .ci-qty{width:50px;text-align:center;font-size:0.85em}
.cart-item .ci-price{width:90px;text-align:right;font-size:0.85em;font-weight:600}
.cart-item .ci-remove{color:var(--danger);cursor:pointer;font-weight:700}
.cart-total-row{display:flex;justify-content:space-between;padding:4px 0;font-size:0.9em}
.cart-total-row.grand{font-size:1.1em;font-weight:700;border-top:2px solid var(--border-color);padding-top:8px;margin-top:4px}
@media(max-width:768px){.pos-layout{grid-template-columns:1fr}.pos-cart{height:auto;max-height:50vh}}
CSS;
include __DIR__ . '/includes/header.php';
?>
<div class="pos-layout">
    <!-- Products -->
    <div class="pos-products">
        <div class="pos-search">
            <input type="text" id="pos-search" class="form-control" placeholder="Search products by name or SKU..." oninput="renderProducts()">
        </div>
        <div class="pos-grid" id="pos-grid">Loading...</div>
    </div>

    <!-- Cart -->
    <div class="pos-cart">
        <div class="pos-cart-header">
            <select id="pos-customer" class="form-control" style="font-size:0.85em"><option value="">Walk-In Customer</option></select>
        </div>
        <div class="pos-cart-items" id="cart-items">
            <div class="empty-state" style="padding:40px 0">Cart is empty</div>
        </div>
        <div class="pos-cart-footer">
            <div class="cart-total-row"><span>Subtotal</span><strong id="cart-subtotal">LKR 0.00</strong></div>
            <div class="cart-total-row"><span>Discount</span><input type="number" id="pos-discount" class="form-control" style="width:100px;height:28px;font-size:0.85em;text-align:right" value="0" step="0.01" oninput="calcCart()"></div>
            <div class="cart-total-row"><span>Tax</span><input type="number" id="pos-tax" class="form-control" style="width:100px;height:28px;font-size:0.85em;text-align:right" value="0" step="0.01" oninput="calcCart()"></div>
            <div class="cart-total-row grand"><span>Total</span><strong id="cart-total">LKR 0.00</strong></div>
            <div style="margin-top:12px">
                <div class="form-group" style="margin-bottom:8px">
                    <div style="display:flex;gap:8px">
                        <select id="pos-method" class="form-control" style="font-size:0.85em"><option>Cash</option><option>Card</option><option>Bank Transfer</option><option>Cheque</option></select>
                        <input type="number" id="pos-paid" class="form-control" placeholder="Amount Paid" style="font-size:0.85em" step="0.01">
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button class="btn btn-primary" style="flex:1" onclick="processPayment()">Pay & Complete</button>
                    <button class="btn btn-outline" onclick="suspendSale()" title="Suspend">⏸</button>
                    <button class="btn btn-outline" onclick="clearCart()" title="Clear">🗑</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extraJs = <<<'JS'
let allProducts = [], cart = [];

async function init() {
    const [pRes, cRes] = await Promise.all([API.get('api/products.php'), API.get('api/customers.php')]);
    allProducts = pRes.data || [];
    const customers = cRes.data || [];
    document.getElementById('pos-customer').innerHTML = '<option value="">Walk-In Customer</option>' + customers.map(c => `<option value="${c.id}">${sanitize(c.name)}</option>`).join('');
    renderProducts();
}

function renderProducts() {
    const q = document.getElementById('pos-search').value.toLowerCase();
    const filtered = allProducts.filter(p => (p.name||'').toLowerCase().includes(q) || (p.sku||'').toLowerCase().includes(q));
    document.getElementById('pos-grid').innerHTML = filtered.length ? filtered.map(p => {
        const stockText = p.manage_stock ? (p.quantity > 0 ? `Stock: ${p.quantity}` : 'Out of stock') : '';
        const disabled = p.manage_stock && p.quantity <= 0;
        return `<div class="pos-product-card${disabled?' disabled':''}" onclick="${disabled?'':'addToCart('+p.id+')'}">
            <div class="name">${sanitize(p.name)}</div>
            <div class="price">${fmtC(p.selling_price)}</div>
            <div class="stock">${stockText}</div>
        </div>`;
    }).join('') : '<div class="empty-state">No products found</div>';
}

function addToCart(productId) {
    const p = allProducts.find(x => x.id == productId);
    if (!p) return;
    const existing = cart.find(x => x.product_id == productId);
    if (existing) {
        if (p.manage_stock && existing.quantity >= p.quantity) { Toast.warning('Not enough stock'); return; }
        existing.quantity++;
        existing.subtotal = existing.quantity * existing.unit_price;
    } else {
        cart.push({ product_id: p.id, product_name: p.name, quantity: 1, unit_price: parseFloat(p.selling_price), subtotal: parseFloat(p.selling_price) });
    }
    renderCart();
}

function updateCartQty(idx, qty) {
    qty = parseInt(qty) || 0;
    if (qty <= 0) { cart.splice(idx, 1); }
    else {
        cart[idx].quantity = qty;
        cart[idx].subtotal = qty * cart[idx].unit_price;
    }
    renderCart();
}

function removeFromCart(idx) { cart.splice(idx, 1); renderCart(); }

function renderCart() {
    const el = document.getElementById('cart-items');
    if (!cart.length) { el.innerHTML = '<div class="empty-state" style="padding:40px 0">Cart is empty</div>'; calcCart(); return; }
    el.innerHTML = cart.map((item, i) => `<div class="cart-item">
        <span class="ci-remove" onclick="removeFromCart(${i})">×</span>
        <span class="ci-name">${sanitize(item.product_name)}</span>
        <input type="number" class="ci-qty form-control" value="${item.quantity}" min="1" style="width:50px;height:28px;text-align:center;font-size:0.85em" onchange="updateCartQty(${i},this.value)">
        <span class="ci-price">${fmtC(item.subtotal)}</span>
    </div>`).join('');
    calcCart();
}

function calcCart() {
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    const discount = parseFloat(document.getElementById('pos-discount').value) || 0;
    const tax = parseFloat(document.getElementById('pos-tax').value) || 0;
    const total = subtotal - discount + tax;
    document.getElementById('cart-subtotal').textContent = fmtC(subtotal);
    document.getElementById('cart-total').textContent = fmtC(total);
}

function getTotal() {
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    return subtotal - (parseFloat(document.getElementById('pos-discount').value)||0) + (parseFloat(document.getElementById('pos-tax').value)||0);
}

async function processPayment() {
    if (!cart.length) { Toast.warning('Cart is empty'); return; }
    const total = getTotal();
    const paid = parseFloat(document.getElementById('pos-paid').value) || 0;
    if (paid < total) {
        if (!confirm(`Amount paid (${fmtC(paid)}) is less than total (${fmtC(total)}). Save as partial payment?`)) return;
    }

    const custSel = document.getElementById('pos-customer');
    const data = {
        customer_id: custSel.value || null,
        customer_name: custSel.value ? custSel.options[custSel.selectedIndex].text : 'Walk-In Customer',
        sale_date: new Date().toISOString().slice(0,10),
        sale_type: 'sale',
        items: cart,
        discount: parseFloat(document.getElementById('pos-discount').value) || 0,
        tax: parseFloat(document.getElementById('pos-tax').value) || 0,
        total_paid: paid,
        payment_method: document.getElementById('pos-method').value,
        payments: [{ amount: paid, method: document.getElementById('pos-method').value }]
    };
    try {
        const res = await API.post('api/sales.php', data);
        if (res.success) {
            const change = paid > total ? paid - total : 0;
            Toast.success(`Sale completed! Invoice: ${res.invoice_no}${change > 0 ? ' | Change: ' + fmtC(change) : ''}`);
            clearCart();
            // Refresh product stock
            const pRes = await API.get('api/products.php');
            allProducts = pRes.data || [];
            renderProducts();
        } else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

async function suspendSale() {
    if (!cart.length) return;
    const custSel = document.getElementById('pos-customer');
    const data = {
        customer_id: custSel.value || null,
        customer_name: custSel.value ? custSel.options[custSel.selectedIndex].text : 'Walk-In Customer',
        sale_date: new Date().toISOString().slice(0,10),
        sale_type: 'sale',
        items: cart,
        discount: parseFloat(document.getElementById('pos-discount').value) || 0,
        tax: parseFloat(document.getElementById('pos-tax').value) || 0,
        is_suspended: 1
    };
    try {
        const res = await API.post('api/sales.php', data);
        if (res.success) { Toast.success('Sale suspended'); clearCart(); }
        else Toast.error(res.message);
    } catch(e) { Toast.error('Error: ' + e.message); }
}

function clearCart() {
    cart = [];
    document.getElementById('pos-discount').value = 0;
    document.getElementById('pos-tax').value = 0;
    document.getElementById('pos-paid').value = '';
    renderCart();
}

init();
JS;
include __DIR__ . '/includes/footer.php';
?>
