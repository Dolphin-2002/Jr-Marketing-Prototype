$file = 'w:\Web Application\Visual Studio Code\Jr-Marketing-Prototype\pos.html'
$lines = [System.IO.File]::ReadAllLines($file)
$keep = $lines[0..3592]

$newBlock = @"

    <!-- ===== ALL PRICES POPUP ===== -->
    <div class="ap-overlay" id="ap-overlay">
        <div class="ap-modal">
            <div class="ap-header">
                <div>
                    <h3>Cart Prices</h3>
                    <div class="ap-header-sub" id="ap-header-sub">0 items in cart</div>
                </div>
                <button class="ap-close" onclick="closeAllPrices()">&#x2715;</button>
            </div>
            <div class="ap-body" id="ap-body">
                <div class="ap-empty">No items in cart yet.</div>
            </div>
            <div class="ap-footer">
                <span class="ap-footer-total">Total Payable: &nbsp;<strong id="ap-total">LKR 0.00</strong></span>
            </div>
        </div>
    </div>

    <script>
    /* ===== ALL PRICES POPUP ===== */
    window.openAllPrices = function() {
        const items = (typeof cartItems !== 'undefined') ? cartItems : [];
        const body  = document.getElementById('ap-body');
        const sub   = document.getElementById('ap-header-sub');
        const tot   = document.getElementById('ap-total');

        if (items.length === 0) {
            body.innerHTML = '<div class="ap-empty">No items in cart yet.</div>';
            if (sub) sub.textContent = '0 items in cart';
            if (tot) tot.textContent = 'LKR 0.00';
        } else {
            const totalPay = items.reduce((s, i) => s + i.price * i.qty, 0);
            if (sub) sub.textContent = items.length + ' item' + (items.length > 1 ? 's' : '') + ' in cart';
            if (tot) tot.textContent = 'LKR ' + totalPay.toLocaleString('en-LK', {minimumFractionDigits:2});
            const rows = items.map(function(item, idx) {
                const lineTotal = item.price * item.qty;
                return '<tr>' +
                    '<td style="width:28px;text-align:center;color:#9ca3af;font-size:12px;">' + (idx + 1) + '</td>' +
                    '<td><div class="ap-prod-name">' + item.name + '</div><div class="ap-prod-sku">' + item.sku + '</div></td>' +
                    '<td style="text-align:center;color:#374151;">' + item.qty + '</td>' +
                    '<td>LKR ' + item.price.toLocaleString('en-LK', {minimumFractionDigits:2}) + '</td>' +
                    '<td>LKR ' + lineTotal.toLocaleString('en-LK', {minimumFractionDigits:2}) + '</td>' +
                    '</tr>';
            }).join('');
            body.innerHTML = '<table class="ap-table">' +
                '<thead><tr>' +
                '<th>#</th><th>Product</th><th style="text-align:center;">Qty</th><th>Unit Price</th><th>Total</th>' +
                '</tr></thead>' +
                '<tbody>' + rows + '</tbody>' +
                '</table>';
        }

        document.getElementById('ap-overlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    };

    window.closeAllPrices = function() {
        document.getElementById('ap-overlay').classList.remove('open');
        document.body.style.overflow = '';
    };

    document.getElementById('ap-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeAllPrices();
    });
    </script>
</body>
</html>
"@

$finalLines = $keep + $newBlock.Split("`n")
[System.IO.File]::WriteAllLines($file, $finalLines, [System.Text.UTF8Encoding]::new($false))
Write-Host "Done. Total lines: $($finalLines.Length)"
