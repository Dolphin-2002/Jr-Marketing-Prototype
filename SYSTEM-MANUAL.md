# 📘 JR MARKETING SHOP — System Manual

**Point of Sale, Stock Management & Business Application**  
**Version:** 6.3 | **Location:** Sri Lanka  
**Currency:** LKR (Sri Lankan Rupee)

---

## 📑 Table of Contents

1. [About This System](#1-about-this-system)
2. [How to Start the Application](#2-how-to-start-the-application)
3. [Login & User Accounts](#3-login--user-accounts)
4. [Dashboard (Home Screen)](#4-dashboard-home-screen)
5. [Point of Sale (POS)](#5-point-of-sale-pos)
6. [Products Management](#6-products-management)
7. [Customer Management](#7-customer-management)
8. [Supplier Management](#8-supplier-management)
9. [Purchases](#9-purchases)
10. [Sales Management](#10-sales-management)
11. [Sale Returns](#11-sale-returns)
12. [Expenses](#12-expenses)
13. [Payment Records](#13-payment-records)
14. [Reports](#14-reports)
15. [Data Import (From Old System)](#15-data-import-from-old-system)
16. [Data Flow Diagram (DFD)](#16-data-flow-diagram-dfd)
17. [System Flow Charts](#17-system-flow-charts)
18. [Complete Feature List](#18-complete-feature-list)
19. [Troubleshooting](#19-troubleshooting)

---

## 1. About This System

JR Marketing Shop is a complete business management application designed for retail shops in Sri Lanka. It helps you:

- **Sell products** quickly using the Point of Sale (POS) screen
- **Track your stock** so you know when items are running low
- **Manage customers and suppliers** with their contact details and balances
- **Record purchases** from suppliers and track what you owe them
- **Record expenses** like rent, electricity, transport etc.
- **View reports** to understand your profit, loss, sales, and expenses
- **Import data** from your old system using CSV or Excel files

All data is stored locally in your browser — no internet connection is needed after the first load.

---

## 2. How to Start the Application

### What You Need
- A modern web browser (Google Chrome, Microsoft Edge, or Firefox)
- The application files on your computer

### Steps to Open
1. Open **Visual Studio Code** (or any code editor)
2. Install the **Live Server** extension
3. Right-click on `index.html` → Choose **"Open with Live Server"**
4. The login page will open in your browser

> **Note:** You must use a local server (Live Server). Simply double-clicking the file will not work properly.

---

## 3. Login & User Accounts

When you open the application, you will see the **Login Page**.

### Available User Accounts

| Username   | Password     | Role          | What They Can Do                     |
|------------|-------------|---------------|--------------------------------------|
| `admin`    | `admin123`  | Administrator | Full access to everything            |
| `manager`  | `manager123`| Manager       | Manage sales, purchases, reports     |
| `cashier`  | `cashier123`| Cashier       | Use POS and make sales               |
| `demo`     | `demo`      | Demo User     | View and explore the system          |

### How to Login
1. Type your **Username** in the first box
2. Type your **Password** in the second box
3. (Optional) Tick **"Remember me"** to stay logged in
4. Click the **Login** button
5. You will be taken to the Dashboard

### Forgot Password
- Click **"Forgot Password?"** on the login page
- Enter your username or email to reset (demo feature)

### Logout
- Click your **name/avatar** in the top-right corner of any page
- Confirm "Do you want to logout?"
- You will be returned to the login page

---

## 4. Dashboard (Home Screen)

After logging in, the **Dashboard** is your home screen. It gives you a quick summary of your business at a glance.

### What You See on the Dashboard

#### Summary Cards (Top Section)
Eight cards showing key numbers:

| Card                  | What It Shows                                           |
|-----------------------|---------------------------------------------------------|
| **Total Sales**       | Total value of all sales made                           |
| **Net**               | Sales minus purchases and expenses (your net position)  |
| **Invoice Due**       | Money customers still owe you                           |
| **Total Sell Return** | Value of products returned by customers                 |
| **Total Purchase**    | Total value of goods you bought from suppliers          |
| **Purchase Due**      | Money you still owe to suppliers                        |
| **Purchase Return**   | Value of goods you returned to suppliers                |
| **Expense**           | Total expenses recorded                                 |

#### Date Filter
- Click the **"Filter by date"** button (top-right)
- A dropdown appears with quick options:
  - **Today** — show only today's numbers
  - **Yesterday** — show yesterday's numbers
  - **Last 7 Days** / **Last 30 Days** — recent period
  - **This Month** / **Last Month** — monthly view
  - **This Year** — full year
  - **All Time** — everything ever recorded
- You can also pick **custom dates** using the calendar fields and click **Apply**

#### Stock Alert Products
- Below the summary cards, a table shows **products running low on stock**
- Shows products that are **Out of Stock** (red) or **Low Stock** (yellow warning)
- Columns: Product Name, SKU, Current Stock, Alert Quantity, Selling Price, Status
- A **red badge** shows how many products need attention
- Click **"View Stock Report →"** for the full stock report

#### Quick Actions
Four shortcut buttons at the bottom:
- **Open POS** — go to Point of Sale
- **Add New Product** — add a product
- **Payments** — view payment records
- **Add Expense** — record a new expense

---

## 5. Point of Sale (POS)

The POS screen is where you make sales to customers. It is designed to be fast and easy.

### How to Make a Sale

1. **Open POS** (click "POS" button in the top bar or from Dashboard)
2. **Search for products** by typing the product name or scanning a barcode
3. **Click a product** to add it to the cart
4. **Adjust quantity** using the + and − buttons
5. **Select a customer** or use "Walk-In Customer" (default)
6. Click **"Pay"** when ready
7. Choose **payment method** (Cash, Card, Bank Transfer, Cheque)
8. Enter the **amount received**
9. The system calculates **change** automatically
10. Click **"Complete Sale"** to finish
11. **Print the invoice** or save as PDF

### POS Features
- **Product search** — type name or SKU to find products quickly
- **Customer selection** — choose existing customer or add a new one
- **Multiple payment methods** — Cash, Card, Bank Transfer, Cheque
- **Split payments** — pay partly by cash and partly by card
- **Discount** — apply discounts to the sale
- **Hold/Suspend sale** — pause a sale and come back to it later
- **Quotation** — create a price quote without completing a sale
- **Invoice printing** — print a receipt/invoice for the customer
- **Add new product** — add a product directly from POS if it doesn't exist yet
- **Add new customer** — register a new customer without leaving POS

---

## 6. Products Management

### Viewing Products
- Go to **Products → Products List** from the sidebar
- See all products in a table with: Name, SKU, Barcode Type, Unit, Price, Stock Status
- **Search** products by name or SKU
- **Delete** products using the red delete button

### Adding a New Product
1. Go to **Products → Add New Product** (or click the "Add Product" tab)
2. Fill in the details:
   - **Product Name** (required) — e.g., "Milo 400g"
   - **SKU** — unique code (auto-generated if left blank)
   - **Selling Price** (required) — e.g., 980
   - **Barcode Type** — C128, EAN13, etc.
   - **Unit** — Pieces, Boxes, Kg, etc.
   - **Manage Stock** — turn on to track stock levels
   - **Alert Quantity** — get warned when stock drops to this number
   - **Description** — optional notes about the product
3. Click **"Save Product"**

### Importing Products from Excel
1. Click the **"Import Products"** tab
2. Download the **template** (Excel file with correct column names)
3. Fill in your product data in the template
4. Upload the filled file
5. Preview and confirm the import

### Other Product Pages
- **Categories** — group products by type (e.g., Beverages, Dairy, Groceries)
- **Brands** — manage product brands (e.g., Milo, Anchor, Sunlight)
- **Units** — manage units of measurement (Pieces, Kg, Litres, etc.)
- **Stock** — view and adjust stock levels

---

## 7. Customer Management

### Viewing Customers
- Go to **Contacts → Customers** from the sidebar
- See all customers with: Name, Type, Phone, Email, Total Sale Due
- **Search** by name, phone, or email
- **View details** of any customer by clicking the eye icon
- **Export** customer list to CSV

### Adding a New Customer
1. Click **"+ Add Customer"**
2. Choose **type**: Individual or Business
3. Fill in details:
   - Name, Phone, Email
   - Address (Street, City, State, Zip)
   - Credit Limit (optional)
   - Pay Terms (optional)
4. Click **"Save Customer"**

### Customer Types
- **Individual** — a single person (Mr., Mrs., Ms., Dr.)
- **Business** — a company or organization

### Importing Customers
- Click **"📤 Import Data"** to import customers from CSV or Excel files
- Choose **Merge** (add new, skip duplicates) or **Replace** (replace all)

---

## 8. Supplier Management

### Viewing Suppliers
- Go to **Contacts → Suppliers** from the sidebar
- See all suppliers with: Company, Contact Person, Phone, Email, Total Purchase Due

### Adding a New Supplier
1. Click **"+ Add Supplier"**
2. Fill in: Company Name, Contact Person, Phone, Email, Tax Number, Address
3. Click **"Save"**

### Importing Suppliers
- Click **"📤 Import Data"** to import from CSV or Excel
- Supports Contact ID, Business Name, Phone, Email, Address, Tax Number, Purchase Due balances

---

## 9. Purchases

### Viewing Purchases
- Go to **Purchases → Purchase List** from the sidebar
- See all purchases with: Date, Reference No, Supplier, Status, Grand Total, Payment Due

### Purchase Status Labels
- **Received** — goods received from supplier
- **Pending** — order placed but not received yet
- **Ordered** — order confirmed

### Payment Status Labels
- **Paid** (green) — fully paid
- **Due** (red) — nothing paid yet
- **Partial** (orange) — some amount paid

### Adding a Purchase
1. Go to **Purchases → Add Purchase**
2. Select supplier, date, reference number
3. Add items with quantities and prices
4. Record payment details
5. Save the purchase

### Purchase Returns
- Go to **Purchases → Purchase Return**
- Return goods to suppliers and record the refund

### Importing Purchases
- Click **"📤 Import Data"** on the purchase list page
- Import from CSV with columns: Date, Reference No, Supplier, Grand Total, Payment Due, etc.

---

## 10. Sales Management

### Viewing All Sales
- Go to **Sell → All Sales** from the sidebar
- See all sales with: Date, Invoice No, Customer, Payment Status, Total Amount

### Tabs Available
- **All Sales** — complete list of all sales
- **Quotations** — price estimates given to customers (not final sales)
- **Credit Sales** — sales where the customer will pay later
- **Cheques** — sales paid by cheque

### Adding a Sale
- Use the **POS screen** for quick sales (recommended)
- Or go to **Sell → Add Sale** for a form-based entry

### Importing Sales
- Click **"📤 Import Data"** on the sales page
- Import from CSV or Excel with columns: Invoice No, Date, Customer, Total Amount, etc.

---

## 11. Sale Returns

- Go to **Sell → Sale Return** from the sidebar
- Search for a sale by invoice number
- Select items being returned
- Enter return quantity and reason
- The refund amount is calculated automatically
- Process the return

---

## 12. Expenses

### Viewing Expenses
- Go to **Expenses → Expense List** from the sidebar
- See all expenses with: Date, Reference, Category, Amount, Note, Paid By

### Adding an Expense
1. Go to **Expenses → Add Expense**
2. Fill in: Date, Category, Amount, Note, Payment Method
3. Click **"Save"**

### Expense Categories
- Go to **Expenses → Expense Categories**
- Add categories like: Rent, Electricity, Transport, Salaries, Marketing, etc.
- Each category can have a unique code
- Edit or delete categories as needed

---

## 13. Payment Records

- Go to **Payments** from the sidebar
- View all payment records with: Date, Invoice, Customer, Method, Amount, Status
- Filter by payment method (Cash, Card, Bank Transfer, Cheque)
- Search by invoice number or customer name
- **Print** or **Export** payment records

---

## 14. Reports

The system provides six types of reports to help you understand your business:

### 📊 Profit / Loss Report
- Shows your total **income** (sales) vs total **expenses** (purchases + expenses)
- Calculates your **net profit or loss**
- Filter by date range

### 📦 Purchase Report
- Summary of all purchases over a period
- Total purchases, total paid, total due
- Filter by date, supplier, or payment status

### 🛒 Sales Report
- Summary of all sales over a period
- Total sales, total received, total due
- Filter by date, customer, or payment status

### 💰 Expense Report
- Breakdown of expenses by category
- Total expenses for the period
- Filter by date range or category

### 📦 Stock Report
- Shows all products with their current stock levels
- Summary cards: Total Products, Stock Value, Low Stock Items, Out of Stock
- Status badges: **In Stock** (green), **Low Stock** (yellow), **Out of Stock** (red)
- Filter by stock status or search by product name
- Export to CSV or Print

### 🧾 Tax Report
- Shows tax-related information for your sales and purchases
- Useful for filing tax returns

---

## 15. Data Import (From Old System)

The system allows you to import data from your old system using **CSV or Excel (.xlsx)** files.

### Pages That Support Import
| Page         | Button       | What It Imports                                        |
|-------------|-------------|--------------------------------------------------------|
| Customers   | 📤 Import Data | Customer list with names, phones, balances            |
| Suppliers   | 📤 Import Data | Supplier list with company, contact, purchase dues    |
| Purchases   | 📤 Import Data | Purchase records with dates, amounts, payment status  |
| Sales       | 📤 Import Data | Sales records with invoices, amounts, customers       |
| Products    | Import Products | Product list with names, SKUs, prices, stock settings |

### How to Import
1. Click the **"📤 Import Data"** button on the relevant page
2. **Drag & drop** your file or click to browse
3. Accepted formats: `.csv` and `.xlsx`
4. Choose import mode:
   - **Merge** — adds new records, skips duplicates
   - **Replace** — removes all existing data and replaces with the file
5. Click **"Import Data"**
6. A success message shows how many records were imported

### Import Tips
- Make sure your file has column headers in the first row
- The system automatically maps common column names
- Duplicate records are skipped (based on ID, name, or reference number)
- Products have a downloadable **template** with the correct column format

---

## 16. Data Flow Diagram (DFD)

### Level 0 — Context Diagram (Overall System)

```
                        ┌─────────────────┐
                        │                 │
     ┌──────────┐       │   JR MARKETING  │       ┌──────────┐
     │          │──────▶│   SHOP SYSTEM   │◀──────│          │
     │  Staff   │       │                 │       │ Suppliers │
     │ (Users)  │◀──────│  (POS, Stock,   │──────▶│          │
     │          │       │   Accounting)   │       └──────────┘
     └──────────┘       │                 │
                        │                 │       ┌──────────┐
     ┌──────────┐       │                 │       │          │
     │          │──────▶│                 │──────▶│ Reports  │
     │Customers │◀──────│                 │       │ (Print/  │
     │          │       │                 │       │  Export)  │
     └──────────┘       └─────────────────┘       └──────────┘
```

**How to read this:**
- **Staff** (shop workers) log in and use the system to make sales, add products, record expenses
- **Customers** buy products; the system tracks what they owe
- **Suppliers** provide goods; the system tracks what you owe them
- **Reports** are generated for the shop owner to review business performance

---

### Level 1 — Main Processes

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         JR MARKETING SHOP SYSTEM                        │
│                                                                          │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐                  │
│  │   1. LOGIN   │    │ 2. PRODUCT  │    │ 3. CUSTOMER │                  │
│  │  & SECURITY  │    │ MANAGEMENT  │    │ MANAGEMENT  │                  │
│  │              │    │             │    │             │                  │
│  │ • Login      │    │ • Add/Edit  │    │ • Add/Edit  │                  │
│  │ • Logout     │    │ • Delete    │    │ • View      │                  │
│  │ • Session    │    │ • Import    │    │ • Import    │                  │
│  │ • Remember   │    │ • Stock Set │    │ • Export    │                  │
│  └──────┬───────┘    └──────┬──────┘    └──────┬──────┘                  │
│         │                   │                  │                         │
│         ▼                   ▼                  ▼                         │
│  ┌─────────────────────────────────────────────────────────────────┐     │
│  │                    LOCAL DATA STORAGE                            │     │
│  │  (Browser localStorage — all data saved locally)                │     │
│  └─────────────────────────────────────────────────────────────────┘     │
│         ▲                   ▲                  ▲                         │
│         │                   │                  │                         │
│  ┌──────┴───────┐    ┌──────┴──────┐    ┌──────┴──────┐                  │
│  │ 4. POINT OF  │    │5. PURCHASES │    │ 6. EXPENSES │                  │
│  │    SALE      │    │& SUPPLIERS  │    │& PAYMENTS   │                  │
│  │              │    │             │    │             │                  │
│  │ • Sell items │    │ • Record    │    │ • Add       │                  │
│  │ • Invoice    │    │ • Returns   │    │ • Categorise│                  │
│  │ • Payments   │    │ • Import    │    │ • Track     │                  │
│  │ • Hold sale  │    │ • Dues      │    │ • Report    │                  │
│  └──────┬───────┘    └──────┬──────┘    └──────┬──────┘                  │
│         │                   │                  │                         │
│         └───────────┬───────┘──────────────────┘                         │
│                     ▼                                                    │
│              ┌──────────────┐                                            │
│              │  7. REPORTS  │                                            │
│              │              │                                            │
│              │ • Profit/Loss│                                            │
│              │ • Sales      │                                            │
│              │ • Purchases  │                                            │
│              │ • Expenses   │                                            │
│              │ • Stock      │                                            │
│              │ • Tax        │                                            │
│              └──────────────┘                                            │
└──────────────────────────────────────────────────────────────────────────┘
```

---

### Level 2 — Point of Sale (POS) Process Detail

```
                    ┌─────────────────┐
                    │  Staff Opens    │
                    │  POS Screen     │
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Search/Scan    │
                    │  Product        │◀──── Product Data
                    └────────┬────────┘      (jr_products)
                             │
                             ▼
                    ┌─────────────────┐
                    │  Add to Cart    │
                    │  Set Quantity   │
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Select         │◀──── Customer Data
                    │  Customer       │      (jr_customers)
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Choose Payment │
                    │  Cash/Card/Bank │
                    └────────┬────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │  Complete Sale  │
                    │  Print Invoice  │
                    └────────┬────────┘
                             │
                    ┌────────┴────────┐
                    ▼                 ▼
          ┌──────────────┐  ┌──────────────┐
          │ Save to      │  │ Update Stock │
          │ jr_payments  │  │ jr_products  │
          └──────────────┘  └──────────────┘
```

---

### Level 2 — Data Import Process Detail

```
         ┌──────────────────┐
         │  User Clicks     │
         │  "Import Data"   │
         └────────┬─────────┘
                  │
                  ▼
         ┌──────────────────┐
         │  Upload File     │
         │  (.csv or .xlsx) │
         └────────┬─────────┘
                  │
                  ▼
         ┌──────────────────┐
         │  Parse File      │
         │  Read Rows &     │
         │  Column Headers  │
         └────────┬─────────┘
                  │
                  ▼
         ┌──────────────────┐
         │  Map Old Columns │
         │  to New Format   │
         │  (Auto-detect)   │
         └────────┬─────────┘
                  │
                  ▼
        ┌─────────┴──────────┐
        │  Merge or Replace? │
        └─────────┬──────────┘
                  │
          ┌───────┴───────┐
          ▼               ▼
   ┌────────────┐  ┌────────────┐
   │   MERGE    │  │  REPLACE   │
   │ Add new,   │  │ Delete all │
   │ skip dupes │  │ Import new │
   └─────┬──────┘  └─────┬──────┘
         │               │
         └───────┬────────┘
                 ▼
        ┌──────────────────┐
        │  Save to Local   │
        │  Storage         │
        └──────────────────┘
```

---

## 17. System Flow Charts

### Main Navigation Flow

```
 ┌──────────┐     Login      ┌──────────────┐
 │  LOGIN   │ ──────────────▶│  DASHBOARD   │
 │  PAGE    │                │  (Home)      │
 └──────────┘                └──────┬───────┘
                                    │
              ┌─────────────────────┼─────────────────────┐
              │                     │                     │
              ▼                     ▼                     ▼
     ┌────────────────┐   ┌────────────────┐   ┌────────────────┐
     │   CONTACTS     │   │   PRODUCTS     │   │    SELL        │
     │                │   │                │   │                │
     │ • Customers    │   │ • Product List │   │ • POS          │
     │ • Suppliers    │   │ • Add Product  │   │ • All Sales    │
     │                │   │ • Categories   │   │ • Add Sale     │
     │                │   │ • Brands       │   │ • Sale Return  │
     │                │   │ • Units        │   │ • Quotations   │
     │                │   │ • Stock        │   │ • Credit Sales │
     └────────────────┘   └────────────────┘   └────────────────┘
              │                     │                     │
              ▼                     ▼                     ▼
     ┌────────────────┐   ┌────────────────┐   ┌────────────────┐
     │  PURCHASES     │   │   EXPENSES     │   │   REPORTS      │
     │                │   │                │   │                │
     │ • Purchase List│   │ • Expense List │   │ • Profit/Loss  │
     │ • Add Purchase │   │ • Add Expense  │   │ • Purchase     │
     │ • Purchase Rtn │   │ • Categories   │   │ • Sales        │
     │                │   │                │   │ • Expense      │
     │                │   │                │   │ • Stock        │
     │                │   │ • Payments     │   │ • Tax          │
     └────────────────┘   └────────────────┘   └────────────────┘
```

---

### Sale Process Flow

```
START
  │
  ▼
┌──────────────────┐
│ Open POS Screen  │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐     ┌────────────────────┐
│ Search Product   │────▶│ Product not found? │
└────────┬─────────┘     │ Add New Product    │
         │               └────────────────────┘
         ▼
┌──────────────────┐
│ Add to Cart      │◀─── Repeat for more items
│ Set Qty & Price  │
└────────┬─────────┘
         │
         ▼
┌──────────────────┐     No    ┌────────────────┐
│ Want to Hold?    │─────────▶│ Select Customer │
│ (Suspend Sale)   │          └────────┬────────┘
└────────┬─────────┘                   │
    Yes  │                             ▼
         ▼                    ┌────────────────────┐
┌──────────────────┐          │ Apply Discount?    │
│ Save as Draft    │          │ (Optional)         │
│ Resume Later     │          └────────┬───────────┘
└──────────────────┘                   │
                                       ▼
                              ┌────────────────────┐
                              │ Choose Payment     │
                              │ Cash / Card / Bank │
                              │ / Cheque / Split   │
                              └────────┬───────────┘
                                       │
                                       ▼
                              ┌────────────────────┐
                              │ Enter Amount Paid  │
                              │ System Calculates  │
                              │ Change / Balance   │
                              └────────┬───────────┘
                                       │
                              ┌────────┴───────────┐
                              ▼                    ▼
                    ┌──────────────┐     ┌──────────────┐
                    │ Fully Paid   │     │ Partial Pay  │
                    │ (Completed)  │     │ (Credit Sale)│
                    └──────┬───────┘     └──────┬───────┘
                           │                    │
                           └────────┬───────────┘
                                    ▼
                           ┌────────────────┐
                           │ Print Invoice  │
                           │ Save Record    │
                           └────────────────┘
                                    │
                                   END
```

---

### Purchase Process Flow

```
START
  │
  ▼
┌──────────────────────┐
│ Go to Add Purchase   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Select Supplier      │──── New? → Add Supplier
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Enter Purchase Date  │
│ & Reference Number   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Add Products & Qty   │
│ Enter Buy Prices     │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Set Payment Status   │
│ (Paid / Due / Partial│
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Save Purchase Record │
│ Stock Updated        │
└──────────────────────┘
           │
          END
```

---

### Expense Recording Flow

```
START
  │
  ▼
┌──────────────────────┐
│ Go to Add Expense    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Select Category      │──── No category? → Create one
│ (Rent, Electric, etc)│     in Expense Categories
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Enter Amount (LKR)   │
│ Enter Date & Note    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Choose Payment Method│
│ (Cash, Card, etc.)   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Save Expense         │
│ Shows in Reports     │
└──────────────────────┘
           │
          END
```

---

## 18. Complete Feature List

### 🔐 Security & Access
| Feature | Description |
|---------|-------------|
| User Login | Username and password login |
| Remember Me | Stay logged in across sessions |
| Session Management | Automatic session tracking |
| Role-Based Access | Admin, Manager, Cashier, Demo roles |
| Secure Logout | Confirmation before logging out |

### 🏠 Dashboard
| Feature | Description |
|---------|-------------|
| Sales Summary | Total sales, net, invoice due amounts |
| Purchase Summary | Total purchases, purchase due amounts |
| Returns Summary | Sell return and purchase return totals |
| Expense Summary | Total expenses |
| Date Filter | Filter all numbers by date range |
| Quick Presets | Today, Yesterday, Last 7/30 days, Month, Year |
| Custom Date Range | Pick any start and end date |
| Stock Alerts | Table showing low/out of stock products |
| Quick Actions | Shortcut buttons to common tasks |

### 🛒 Point of Sale (POS)
| Feature | Description |
|---------|-------------|
| Product Search | Search by name or barcode |
| Cart Management | Add, remove, change quantity |
| Customer Selection | Choose or add new customer |
| Multiple Payment Methods | Cash, Card, Bank Transfer, Cheque |
| Split Payments | Pay with multiple methods |
| Change Calculation | Automatic change calculation |
| Hold/Suspend Sale | Pause and resume later |
| Quotation | Create price estimates |
| Invoice Print | Print receipt for customer |
| Quick Product Add | Add products from POS screen |
| Quick Customer Add | Register customers from POS |

### 📦 Products
| Feature | Description |
|---------|-------------|
| Product List | View all products in a table |
| Add Product | Create new products with full details |
| SKU Auto-Generate | Automatic SKU if not provided |
| Barcode Support | C128, C39, EAN13, EAN8, UPC codes |
| Unit Management | Pieces, Kg, Litres, Boxes, etc. |
| Stock Management | Enable/disable stock tracking per product |
| Alert Quantity | Get warned when stock is low |
| Product Search | Find products by name or SKU |
| Delete Product | Remove products from the system |
| Excel Import | Import products from Excel template |
| Template Download | Download ready-made import template |
| Categories | Group products by type |
| Brands | Manage product brands |

### 👥 Contacts
| Feature | Description |
|---------|-------------|
| Customer List | View all customers |
| Customer Types | Individual or Business |
| Customer Details | Name, phone, email, address, credit limit |
| Customer View | Detailed view of customer info |
| Sale Due Tracking | Track money owed by each customer |
| Supplier List | View all suppliers |
| Supplier Details | Company, contact, phone, tax number |
| Purchase Due Tracking | Track money owed to each supplier |
| CSV Import | Import customers/suppliers from files |
| CSV Export | Export lists to CSV files |
| Search & Filter | Find contacts quickly |

### 🛍️ Sales
| Feature | Description |
|---------|-------------|
| All Sales List | View every sale with status |
| Payment Status | Paid, Due, Partial badges |
| Sale Details | Invoice, customer, items, amounts |
| Quotations Tab | View price estimates |
| Credit Sales Tab | View unpaid sales |
| Cheques Tab | View cheque payments |
| Sale Return | Process product returns |
| Refund Calculation | Automatic refund amounts |
| Import Sales | Import from CSV/Excel |
| Search & Filter | Find sales quickly |

### 📋 Purchases
| Feature | Description |
|---------|-------------|
| Purchase List | View all purchase records |
| Purchase Status | Received, Pending, Ordered |
| Payment Tracking | Paid, Due, Partial status |
| Purchase Return | Return goods to supplier |
| Import Purchases | Import from CSV/Excel |
| Search & Filter | Find purchases quickly |

### 💰 Expenses
| Feature | Description |
|---------|-------------|
| Expense List | View all expenses |
| Add Expense | Record new expenses |
| Expense Categories | Rent, Electric, Transport, etc. |
| Category Management | Add, edit, delete categories |
| Payment Method | Track how expense was paid |

### 💳 Payments
| Feature | Description |
|---------|-------------|
| Payment Records | View all payment transactions |
| Filter by Method | Cash, Card, Bank, Cheque |
| Search | Find by invoice or customer |
| Print/Export | Print or export records |

### 📊 Reports
| Feature | Description |
|---------|-------------|
| Profit/Loss | Income vs expenses |
| Sales Report | Sales summary and details |
| Purchase Report | Purchase summary and details |
| Expense Report | Expense breakdown by category |
| Stock Report | Current stock levels, low stock alerts |
| Tax Report | Tax-related summaries |
| Date Filtering | Filter all reports by date range |
| Print | Print any report |
| Export | Export to CSV |

### 📥 Data Import
| Feature | Description |
|---------|-------------|
| CSV Support | Import .csv files |
| Excel Support | Import .xlsx files |
| Auto Column Mapping | Automatically detects column names |
| Merge Mode | Add new records, skip duplicates |
| Replace Mode | Replace all existing data |
| Drag & Drop | Drag files onto the import area |
| Progress Feedback | Shows import results and counts |

---

## 19. Troubleshooting

### Common Problems and Solutions

| Problem | Solution |
|---------|----------|
| **Page shows blank** | Make sure you're using Live Server, not opening the file directly |
| **Login not working** | Check username and password (case-sensitive). Try: admin / admin123 |
| **Data disappeared** | Data is stored in browser. Clearing browser data will remove it. Don't clear site data. |
| **Import failed** | Check that your file has column headers in the first row. Use .csv or .xlsx format. |
| **Dashboard shows LKR 0.00** | Make sure you have sales/purchases/expenses recorded. Try changing the date filter to "All Time". |
| **Stock alerts empty** | Products need "Manage Stock" enabled and an "Alert Quantity" set. |
| **POS products not showing** | Add products first in Products → Add New Product |
| **Invoice not printing** | Allow pop-ups in your browser for the printing to work |
| **Sidebar not showing** | Click the ☰ (hamburger) menu icon in the top-left corner |
| **Forgot password** | Use the demo credentials listed in Section 3 of this manual |

---

## 📌 Data Storage Reference

All information is stored in your browser using these storage names:

| Storage Name | What It Stores |
|-------------|---------------|
| `jr_session` | Current logged-in user |
| `jr_remember` | Remember me preference |
| `jr_products` | All products |
| `jr_customers` | All customers |
| `jr_suppliers` | All suppliers |
| `jr_payments` | All sales/payment records |
| `jr_purchases` | All purchase records |
| `jr_expenses` | All expense records |
| `jr_expense_categories` | Expense category list |
| `jr_sale_returns` | Sale return records |
| `jr_purchase_returns` | Purchase return records |
| `jr_drafts` | Saved/suspended POS sales |
| `jr_quotations` | Quotation records |
| `jr_credit_sales` | Credit sale records |
| `jr_suspended` | Suspended (held) sales |
| `jr_invoice_counter` | Next invoice number |

> ⚠️ **Important:** This data is saved in your browser only. If you clear your browser data or use a different browser/computer, the data will not be there. Always export/backup important data regularly.

---

*JR Marketing Shop — System Manual v6.3*  
*© 2025 JR Marketing, Sri Lanka*  
*Last Updated: March 2026*

Ok i will tell you fixex that customer i mean my client mentioed 

1. whenever JR Marketing comes add JR Marketing (Pvt) Ltd
2. Remove Tax Number and address option for add supplier and for suppliler related 
3.Remove opening balance txt box from all ok 
4.inside add supplier section add Supplier contect ID textbox to all add supplier and supplier related boxes
5. inside product area there's a add product button so replace it with download product data like that do to all add a like export (data) button (xlsx)

6. in product grid view client dont like to view barcode and warrenty remove that collum you can replace that thing with Purchase price and selling price. rencame the addon on into Data also in the action area if clinet clicked the client can edit that product in a pop up 

7.inside that produt add page the barcode and unit should be defult like for bar code (code 128 (c128)) and unit (pieces) add a edit button near that row so if it's differnct he can click and edit it 

8. inside that supplier gride view the clicnt dont want to see all the collums should be company,total amount, total paid, total due and date

9. in side add customer page do give option to type customer id if they left it empty it will auto gen using customer name 

10.also in customer add page for inducvial and bussiness remove (if exists)
 remove 
 middle name, last name , landline , altnative number and email remove the assiged to section 

also move the addresss section to top to 


