<?php
/**
 * Sidebar Navigation
 * Expects: $activePage (e.g., 'dashboard', 'customers', 'products', etc.)
 */
$ap = $activePage ?? '';

function menuOpen(string $group, string $activePage): string {
    $map = [
        'contacts'  => ['customers','suppliers'],
        'products'  => ['products','units','stock','categories','brands'],
        'purchases' => ['purchases'],
        'sell'      => ['sales','add-sale','sale-return','pos'],
        'expenses'  => ['expenses','expense-categories'],
        'reports'   => ['profit-loss','purchase-report','sales-report','expense-report','stock-report','tax-report'],
    ];
    return in_array($activePage, $map[$group] ?? []) ? 'open' : '';
}

function submenuActive(string $page, string $activePage): string {
    return $page === $activePage ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar" role="complementary">
    <div class="sidebar-inner">
        <ul class="sidebar-menu">
            <!-- Home -->
            <li class="menu-item">
                <a href="dashboard.php" class="menu-link <?= $ap === 'dashboard' ? 'active' : '' ?>">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
                        Home
                    </span>
                </a>
            </li>
            <!-- Contacts -->
            <li class="menu-item <?= menuOpen('contacts', $ap) ?>" data-menu="contacts">
                <div class="menu-link <?= in_array($ap, ['customers','suppliers']) ? 'active' : '' ?>" onclick="toggleMenu('contacts')">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                        Contacts
                    </span>
                    <span class="menu-chevron">&#9654;</span>
                </div>
                <ul class="submenu">
                    <li><a class="submenu-link <?= submenuActive('customers', $ap) ?>" href="customers.php">&#8227;&nbsp; Customers</a></li>
                    <li><a class="submenu-link <?= submenuActive('suppliers', $ap) ?>" href="suppliers.php">&#8227;&nbsp; Suppliers</a></li>
                </ul>
            </li>
            <!-- Products -->
            <li class="menu-item <?= menuOpen('products', $ap) ?>" data-menu="products">
                <div class="menu-link <?= in_array($ap, ['products','units','stock','categories','brands']) ? 'active' : '' ?>" onclick="toggleMenu('products')">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
                        Products
                    </span>
                    <span class="menu-chevron">&#9654;</span>
                </div>
                <ul class="submenu">
                    <li><a class="submenu-link <?= submenuActive('products', $ap) ?>" href="products.php">&#8227;&nbsp; Products List</a></li>
                    <li><a class="submenu-link" href="products.php#add">&#8227;&nbsp; Add New Product</a></li>
                    <li><a class="submenu-link <?= submenuActive('categories', $ap) ?>" href="categories.php">&#8227;&nbsp; Categories</a></li>
                    <li><a class="submenu-link <?= submenuActive('brands', $ap) ?>" href="brands.php">&#8227;&nbsp; Brands</a></li>
                    <li><a class="submenu-link <?= submenuActive('units', $ap) ?>" href="units.php">&#8227;&nbsp; Units</a></li>
                    <li><a class="submenu-link <?= submenuActive('stock', $ap) ?>" href="stock.php">&#8227;&nbsp; Stock</a></li>
                </ul>
            </li>
            <!-- Purchases -->
            <li class="menu-item <?= menuOpen('purchases', $ap) ?>" data-menu="purchases">
                <div class="menu-link <?= $ap === 'purchases' ? 'active' : '' ?>" onclick="toggleMenu('purchases')">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/></svg></span>
                        Purchases
                    </span>
                    <span class="menu-chevron">&#9654;</span>
                </div>
                <ul class="submenu">
                    <li><a class="submenu-link <?= submenuActive('purchases', $ap) ?>" href="purchases.php">&#8227;&nbsp; Purchase List</a></li>
                    <li><a class="submenu-link" href="purchases.php#add">&#8227;&nbsp; Add Purchase</a></li>
                    <li><a class="submenu-link" href="purchases.php#return">&#8227;&nbsp; Purchase Return</a></li>
                </ul>
            </li>
            <!-- Sell -->
            <li class="menu-item <?= menuOpen('sell', $ap) ?>" data-menu="sell">
                <div class="menu-link <?= in_array($ap, ['sales','add-sale','sale-return']) ? 'active' : '' ?>" onclick="toggleMenu('sell')">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg></span>
                        Sell
                    </span>
                    <span class="menu-chevron">&#9654;</span>
                </div>
                <ul class="submenu">
                    <li><a class="submenu-link <?= submenuActive('sales', $ap) ?>" href="sales.php">&#8227;&nbsp; All Sales</a></li>
                    <li><a class="submenu-link <?= submenuActive('add-sale', $ap) ?>" href="add-sale.php">&#8227;&nbsp; Add Sale</a></li>
                    <li><a class="submenu-link <?= submenuActive('sale-return', $ap) ?>" href="sale-return.php">&#8227;&nbsp; Sale Return</a></li>
                    <li><a class="submenu-link" href="sales.php#quotations">&#8227;&nbsp; Quotations</a></li>
                    <li><a class="submenu-link" href="sales.php#credit">&#8227;&nbsp; Credit Sales</a></li>
                    <li><a class="submenu-link" href="sales.php#cheques">&#8227;&nbsp; Cheques</a></li>
                </ul>
            </li>
            <!-- Expenses -->
            <li class="menu-item <?= menuOpen('expenses', $ap) ?>" data-menu="expenses">
                <div class="menu-link <?= in_array($ap, ['expenses','expense-categories']) ? 'active' : '' ?>" onclick="toggleMenu('expenses')">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
                        Expenses
                    </span>
                    <span class="menu-chevron">&#9654;</span>
                </div>
                <ul class="submenu">
                    <li><a class="submenu-link <?= submenuActive('expenses', $ap) ?>" href="expenses.php">&#8227;&nbsp; Expense List</a></li>
                    <li><a class="submenu-link" href="expenses.php#add">&#8227;&nbsp; Add Expense</a></li>
                    <li><a class="submenu-link <?= submenuActive('expense-categories', $ap) ?>" href="expense-categories.php">&#8227;&nbsp; Expense Categories</a></li>
                </ul>
            </li>
            <!-- Payments -->
            <li class="menu-item">
                <a href="payments.php" class="menu-link <?= $ap === 'payments' ? 'active' : '' ?>">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/><line x1="7" y1="15" x2="10" y2="15"/><line x1="13" y1="15" x2="17" y2="15"/></svg></span>
                        Payments
                    </span>
                </a>
            </li>
            <!-- Reports -->
            <li class="menu-item <?= menuOpen('reports', $ap) ?>" data-menu="reports">
                <div class="menu-link <?= in_array($ap, ['profit-loss','purchase-report','sales-report','expense-report','stock-report','tax-report']) ? 'active' : '' ?>" onclick="toggleMenu('reports')">
                    <span class="menu-link-left">
                        <span class="menu-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span>
                        Reports
                    </span>
                    <span class="menu-chevron">&#9654;</span>
                </div>
                <ul class="submenu">
                    <li><a class="submenu-link <?= submenuActive('profit-loss', $ap) ?>" href="profit-loss.php">&#8227;&nbsp; Profit / Loss</a></li>
                    <li><a class="submenu-link <?= submenuActive('purchase-report', $ap) ?>" href="purchase-report.php">&#8227;&nbsp; Purchase Report</a></li>
                    <li><a class="submenu-link <?= submenuActive('sales-report', $ap) ?>" href="sales-report.php">&#8227;&nbsp; Sales Report</a></li>
                    <li><a class="submenu-link <?= submenuActive('expense-report', $ap) ?>" href="expense-report.php">&#8227;&nbsp; Expense Report</a></li>
                    <li><a class="submenu-link <?= submenuActive('stock-report', $ap) ?>" href="stock-report.php">&#8227;&nbsp; Stock Report</a></li>
                    <li><a class="submenu-link <?= submenuActive('tax-report', $ap) ?>" href="tax-report.php">&#8227;&nbsp; Tax Report</a></li>
                </ul>
            </li>
        </ul>
    </div>
</aside>
