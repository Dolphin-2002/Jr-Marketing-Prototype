-- ============================================
-- JR MARKETING (PVT) LTD — MySQL Database
-- Complete schema for POS/ERP System
-- ============================================

CREATE DATABASE IF NOT EXISTS jr_marketing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE jr_marketing;

-- ── USERS ──
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    name        VARCHAR(100) NOT NULL,
    role        ENUM('Administrator','Manager','Cashier','Demo') NOT NULL DEFAULT 'Cashier',
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default users (passwords hashed with password_hash)
INSERT INTO users (username, password, name, role) VALUES
('admin',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin',    'Administrator'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Branch Manager',  'Manager'),
('cashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cash Counter 1',  'Cashier'),
('demo',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User',       'Demo');
-- NOTE: Default password for all users is 'password'. Change after first login.
-- To use old passwords: admin123, manager123, cashier123, demo — update via the app or run:
-- UPDATE users SET password = '$2y$10$...' WHERE username = 'admin';

-- ── CATEGORIES ──
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    code        VARCHAR(20)  DEFAULT NULL,
    description TEXT         DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── BRANDS ──
CREATE TABLE brands (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT         DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── UNITS ──
CREATE TABLE units (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    short_name  VARCHAR(10) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO units (name, short_name) VALUES
('Pieces', 'pcs'),
('Kilograms', 'kg'),
('Grams', 'g'),
('Litres', 'L'),
('Millilitres', 'ml'),
('Boxes', 'box'),
('Packets', 'pkt'),
('Bottles', 'btl'),
('Bags', 'bag'),
('Metres', 'm');

-- ── PRODUCTS ──
CREATE TABLE products (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    sku             VARCHAR(50)  NOT NULL UNIQUE,
    barcode_type    VARCHAR(20)  NOT NULL DEFAULT 'C128',
    unit            VARCHAR(50)  NOT NULL DEFAULT 'Pieces',
    buying_price    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    selling_price   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    manage_stock    TINYINT(1) NOT NULL DEFAULT 0,
    quantity        INT NOT NULL DEFAULT 0,
    alert_qty       INT NOT NULL DEFAULT 0,
    category_id     INT DEFAULT NULL,
    brand_id        INT DEFAULT NULL,
    description     TEXT DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id)    REFERENCES brands(id)     ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── CUSTOMERS ──
CREATE TABLE customers (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    contact_id          VARCHAR(50) NOT NULL UNIQUE,
    type                ENUM('individual','business') NOT NULL DEFAULT 'individual',
    name                VARCHAR(200) NOT NULL,
    prefix              VARCHAR(10)  DEFAULT '',
    first_name          VARCHAR(100) DEFAULT '',
    business_name       VARCHAR(200) DEFAULT '',
    mobile              VARCHAR(20)  NOT NULL,
    address             VARCHAR(255) DEFAULT '',
    city                VARCHAR(100) DEFAULT '',
    state               VARCHAR(100) DEFAULT '',
    zip                 VARCHAR(20)  DEFAULT '',
    pay_term_val        INT          DEFAULT NULL,
    pay_term_unit       VARCHAR(20)  DEFAULT '',
    credit_limit        DECIMAL(12,2) DEFAULT NULL,
    total_sale_due      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_sell_return_due DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status              VARCHAR(20)  NOT NULL DEFAULT 'Active',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── SUPPLIERS ──
CREATE TABLE suppliers (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    contact_id            VARCHAR(50) NOT NULL UNIQUE,
    company               VARCHAR(200) NOT NULL,
    contact_person        VARCHAR(100) DEFAULT '',
    phone                 VARCHAR(20)  NOT NULL,
    email                 VARCHAR(100) DEFAULT '',
    total_purchase_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_paid            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_purchase_due    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── PURCHASES ──
CREATE TABLE purchases (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    reference_no    VARCHAR(50)  NOT NULL,
    supplier_id     INT          DEFAULT NULL,
    purchase_date   DATE         NOT NULL,
    status          ENUM('Received','Pending','Ordered') NOT NULL DEFAULT 'Received',
    payment_status  ENUM('Paid','Due','Partial') NOT NULL DEFAULT 'Due',
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    grand_total     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_paid      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_due     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    note            TEXT DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE purchase_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id     INT NOT NULL,
    product_id      INT DEFAULT NULL,
    product_name    VARCHAR(255) NOT NULL,
    quantity        DECIMAL(12,2) NOT NULL DEFAULT 0,
    unit_price      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── SALES ──
CREATE TABLE sales (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no      VARCHAR(50)  NOT NULL UNIQUE,
    customer_id     INT          DEFAULT NULL,
    customer_name   VARCHAR(200) NOT NULL DEFAULT 'Walk-In Customer',
    sale_date       DATE         NOT NULL,
    sale_type       ENUM('sale','quotation','credit','cheque') NOT NULL DEFAULT 'sale',
    payment_status  ENUM('Paid','Due','Partial') NOT NULL DEFAULT 'Due',
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_payable   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_paid      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    balance         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    change_amount   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    note            TEXT DEFAULT NULL,
    is_suspended    TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE sale_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sale_id         INT NOT NULL,
    product_id      INT DEFAULT NULL,
    product_name    VARCHAR(255) NOT NULL,
    quantity        DECIMAL(12,2) NOT NULL DEFAULT 0,
    unit_price      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (sale_id)    REFERENCES sales(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── PAYMENTS ──
CREATE TABLE payments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sale_id         INT DEFAULT NULL,
    purchase_id     INT DEFAULT NULL,
    payment_type    ENUM('sale','purchase') NOT NULL DEFAULT 'sale',
    amount          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    method          VARCHAR(30) NOT NULL DEFAULT 'Cash',
    reference       VARCHAR(100) DEFAULT '',
    payment_date    DATE NOT NULL,
    note            TEXT DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id)     REFERENCES sales(id)     ON DELETE SET NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── SALE RETURNS ──
CREATE TABLE sale_returns (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    sale_id         INT DEFAULT NULL,
    invoice_no      VARCHAR(50)  NOT NULL,
    return_date     DATE         NOT NULL,
    total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    reason          TEXT DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE sale_return_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    return_id       INT NOT NULL,
    product_id      INT DEFAULT NULL,
    product_name    VARCHAR(255) NOT NULL,
    quantity        DECIMAL(12,2) NOT NULL DEFAULT 0,
    unit_price      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (return_id)  REFERENCES sale_returns(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)     ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── PURCHASE RETURNS ──
CREATE TABLE purchase_returns (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id     INT DEFAULT NULL,
    reference_no    VARCHAR(50)  NOT NULL,
    return_date     DATE         NOT NULL,
    total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    reason          TEXT DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE purchase_return_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    return_id       INT NOT NULL,
    product_id      INT DEFAULT NULL,
    product_name    VARCHAR(255) NOT NULL,
    quantity        DECIMAL(12,2) NOT NULL DEFAULT 0,
    unit_price      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (return_id)  REFERENCES purchase_returns(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)         ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── EXPENSE CATEGORIES ──
CREATE TABLE expense_categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    code        VARCHAR(20)  DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO expense_categories (name, code) VALUES
('Rent', 'RENT'),
('Electricity', 'ELEC'),
('Transport', 'TRANS'),
('Salaries', 'SAL'),
('Marketing', 'MKT'),
('Maintenance', 'MAINT'),
('Other', 'OTHER');

-- ── EXPENSES ──
CREATE TABLE expenses (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    category_id     INT DEFAULT NULL,
    expense_date    DATE NOT NULL,
    reference_no    VARCHAR(50) DEFAULT '',
    amount          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_method  VARCHAR(30) NOT NULL DEFAULT 'Cash',
    description     TEXT DEFAULT NULL,
    paid_by         VARCHAR(100) DEFAULT '',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── INVOICE COUNTER ──
CREATE TABLE settings (
    setting_key   VARCHAR(50)  PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value) VALUES
('invoice_counter', '1000'),
('company_name', 'JR MARKETING (PVT) LTD'),
('currency', 'LKR'),
('locale', 'en-LK');

-- ── INDEXES FOR PERFORMANCE ──
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_customers_contact_id ON customers(contact_id);
CREATE INDEX idx_customers_name ON customers(name);
CREATE INDEX idx_customers_mobile ON customers(mobile);
CREATE INDEX idx_suppliers_company ON suppliers(company);
CREATE INDEX idx_sales_invoice ON sales(invoice_no);
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sales_customer ON sales(customer_id);
CREATE INDEX idx_purchases_date ON purchases(purchase_date);
CREATE INDEX idx_purchases_supplier ON purchases(supplier_id);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_payments_sale ON payments(sale_id);
CREATE INDEX idx_expenses_date ON expenses(expense_date);
CREATE INDEX idx_expenses_category ON expenses(category_id);
