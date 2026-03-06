/**
 * Data Importer Utility for JR Marketing System
 * Supports CSV and XLSX (via SheetJS CDN) data import.
 * Maps old-system column names to new-system localStorage structures.
 */

const Importer = (() => {

    /* ─── SheetJS (xlsx) lazy loader ─── */
    let _xlsxReady = null;
    function ensureXLSX() {
        if (_xlsxReady) return _xlsxReady;
        _xlsxReady = new Promise((resolve, reject) => {
            if (window.XLSX) { resolve(window.XLSX); return; }
            const s = document.createElement('script');
            s.src = 'https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js';
            s.onload = () => resolve(window.XLSX);
            s.onerror = () => reject(new Error('Failed to load SheetJS library'));
            document.head.appendChild(s);
        });
        return _xlsxReady;
    }

    /* ─── Parse CSV text into array of objects ─── */
    function parseCSV(text) {
        const lines = text.split(/\r?\n/).filter(l => l.trim());
        if (lines.length < 2) return [];
        const headers = parseCSVRow(lines[0]);
        const rows = [];
        for (let i = 1; i < lines.length; i++) {
            const vals = parseCSVRow(lines[i]);
            // Skip total/summary rows
            if ((vals[0] || '').startsWith('Total')) continue;
            const obj = {};
            headers.forEach((h, idx) => obj[h.trim()] = (vals[idx] || '').trim());
            rows.push(obj);
        }
        return rows;
    }

    function parseCSVRow(line) {
        const result = [];
        let current = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
            const ch = line[i];
            if (inQuotes) {
                if (ch === '"') {
                    if (line[i + 1] === '"') { current += '"'; i++; }
                    else inQuotes = false;
                } else { current += ch; }
            } else {
                if (ch === '"') inQuotes = true;
                else if (ch === ',') { result.push(current); current = ''; }
                else current += ch;
            }
        }
        result.push(current);
        return result;
    }

    /* ─── Parse XLSX file into array of objects ─── */
    async function parseXLSX(file) {
        const XLSX = await ensureXLSX();
        const data = await file.arrayBuffer();
        const wb = XLSX.read(data, { type: 'array', cellDates: true });
        const ws = wb.Sheets[wb.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(ws, { defval: '' });
        return rows;
    }

    /* ─── Parse currency string like "LKR 109,068.75" to number ─── */
    function parseLKR(str) {
        if (typeof str === 'number') return str;
        if (!str) return 0;
        return parseFloat(String(str).replace(/[^0-9.\-]/g, '')) || 0;
    }

    /* ─── Parse old date formats ─── */
    function parseDate(str) {
        if (!str) return '';
        // Handle Date objects from xlsx
        if (str instanceof Date) return str.toISOString();
        const s = String(str).trim();
        // DD/MM/YYYY HH:MM AM/PM  or  DD-MM-YYYY  or  DD/MM/YYYY
        const m1 = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\s*(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
        if (m1) {
            let [, d, mo, y, h, mi, ap] = m1;
            h = parseInt(h);
            if (ap) {
                if (ap.toUpperCase() === 'PM' && h < 12) h += 12;
                if (ap.toUpperCase() === 'AM' && h === 12) h = 0;
            }
            return new Date(y, mo - 1, d, h, parseInt(mi)).toISOString();
        }
        const m2 = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
        if (m2) {
            const [, d, mo, y] = m2;
            return new Date(y, mo - 1, d).toISOString();
        }
        // Try native parse as fallback
        const d = new Date(s);
        return isNaN(d.getTime()) ? '' : d.toISOString();
    }

    /* ─── Read file (CSV or XLSX) and return array of objects ─── */
    async function readFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (ext === 'csv') {
            const text = await file.text();
            return parseCSV(text);
        } else if (ext === 'xlsx' || ext === 'xls') {
            return await parseXLSX(file);
        } else {
            throw new Error('Unsupported file format. Use .csv or .xlsx');
        }
    }

    /* ════════════════════════════════════════════════
     *  CUSTOMER IMPORT
     *  Old columns: Action, Contact ID, Business Name, Name, Email, Tax number,
     *    Credit Limit, Pay term, Opening Balance, Advance Balance, Added On,
     *    Customer Group, Address, Mobile, Total Sale Due, Total Sell Return Due
     * ════════════════════════════════════════════════ */
    function mapCustomerRow(row) {
        const contactId = (row['Contact ID'] || '').trim();
        const businessName = (row['Business Name'] || '').trim();
        const name = (row['Name'] || '').trim();
        const mobile = (row['Mobile'] || '').trim();
        const email = (row['Email'] || '').trim();
        const address = (row['Address'] || '').trim();
        const creditLimitStr = (row['Credit Limit'] || '').trim();
        const addedOn = (row['Added On'] || '').trim();
        const totalSaleDue = parseLKR(row['Total Sale Due']);
        const totalSellReturnDue = parseLKR(row['Total Sell Return Due']);

        // Determine type: if businessName exists, it's business
        const isBusiness = !!businessName;
        const displayName = isBusiness ? businessName : name;
        if (!displayName) return null; // skip empty rows

        // Parse credit limit
        let creditLimit = null;
        if (creditLimitStr && creditLimitStr !== 'No Limit') {
            creditLimit = parseLKR(creditLimitStr);
        }

        // Parse prefix from name (MR, MRS, MS, MISS, DR)
        let prefix = '', firstName = '', lastName = '';
        if (!isBusiness && name) {
            const prefixMatch = name.match(/^(MR|MRS|MS|MISS|DR)\.?\s+/i);
            if (prefixMatch) {
                const rawPrefix = prefixMatch[1].toUpperCase();
                prefix = rawPrefix === 'MR' ? 'Mr.' : rawPrefix === 'MRS' ? 'Mrs.' : rawPrefix === 'MS' ? 'Ms.' : rawPrefix === 'MISS' ? 'Ms.' : rawPrefix === 'DR' ? 'Dr.' : '';
                const rest = name.substring(prefixMatch[0].length).trim();
                const parts = rest.split(/\s+/);
                firstName = parts[0] || '';
                lastName = parts.slice(1).join(' ');
            } else {
                const parts = name.split(/\s+/);
                firstName = parts[0] || '';
                lastName = parts.slice(1).join(' ');
            }
        }

        return {
            id: contactId || ('C-' + String(Date.now() + Math.random() * 1000).slice(-8)),
            type: isBusiness ? 'business' : 'individual',
            name: displayName,
            prefix: prefix,
            firstName: firstName,
            middleName: '',
            lastName: lastName,
            dob: '',
            businessName: isBusiness ? businessName : '',
            mobile: mobile,
            altNumber: '',
            landline: '',
            email: email,
            assignedTo: '',
            payTermVal: '',
            payTermUnit: '',
            creditLimit: creditLimit,
            addr1: address,
            city: '',
            state: '',
            zip: '',
            totalSaleDue: totalSaleDue,
            totalSellReturnDue: totalSellReturnDue,
            status: 'Active',
            createdAt: parseDate(addedOn) || new Date().toISOString(),
            _imported: true
        };
    }

    async function importCustomers(file, mode = 'merge') {
        const rows = await readFile(file);
        const mapped = rows.map(mapCustomerRow).filter(Boolean);
        if (!mapped.length) throw new Error('No valid customer records found in file.');

        const existing = JSON.parse(localStorage.getItem('jr_customers') || '[]');

        if (mode === 'replace') {
            localStorage.setItem('jr_customers', JSON.stringify(mapped));
            return { total: mapped.length, added: mapped.length, skipped: 0, mode };
        }

        // Merge: skip duplicates by Contact ID or name+mobile
        let added = 0, skipped = 0;
        const existingIds = new Set(existing.map(c => c.id));
        const existingKeys = new Set(existing.map(c => `${(c.name||'').toLowerCase()}|${c.mobile||c.phone||''}`));

        for (const rec of mapped) {
            const key = `${(rec.name||'').toLowerCase()}|${rec.mobile||''}`;
            if (existingIds.has(rec.id) || existingKeys.has(key)) {
                skipped++; continue;
            }
            existing.push(rec);
            existingIds.add(rec.id);
            existingKeys.add(key);
            added++;
        }
        localStorage.setItem('jr_customers', JSON.stringify(existing));
        return { total: mapped.length, added, skipped, mode };
    }


    /* ════════════════════════════════════════════════
     *  PURCHASE IMPORT
     *  Old columns: Action, Date, Reference No, Location, Supplier,
     *    Purchase Status, Payment Status, Grand Total, Payment due, Added By
     * ════════════════════════════════════════════════ */
    function mapPurchaseRow(row) {
        const date = row['Date'] || '';
        const ref = (row['Reference No'] || '').trim();
        const location = (row['Location'] || '').trim();
        const supplier = (row['Supplier'] || '').trim().replace(/,\s*$/, ''); // remove trailing comma
        const purchaseStatus = (row['Purchase Status'] || 'Received').trim();
        const paymentStatus = (row['Payment Status'] || '').trim();
        const grandTotal = parseLKR(row['Grand Total']);
        const paymentDueStr = row['Payment due'] || row['Payment due   '] || '';
        const addedBy = (row['Added By'] || '').trim();

        if (!ref && !supplier) return null; // skip empty rows

        // Parse payment due — format: "Purchase: LKR 109,068.75"
        let paymentDue = 0;
        const dueMatch = String(paymentDueStr).match(/LKR\s*([\d,]+\.?\d*)/);
        if (dueMatch) paymentDue = parseLKR(dueMatch[1]);

        // Calculate paid amount
        const totalPaid = Math.max(0, grandTotal - paymentDue);

        return {
            id: ref || ('PUR-' + Date.now() + '-' + Math.floor(Math.random() * 1000)),
            date: parseDate(date) || new Date().toISOString(),
            ref: ref,
            supplier: supplier,
            address: '',
            location: location,
            items: [],
            total: grandTotal,
            status: purchaseStatus,
            paymentStatus: paymentStatus,
            paymentDue: paymentDue,
            totalPaid: totalPaid,
            payTermUnit: '',
            payTermVal: '',
            addedBy: addedBy,
            created: parseDate(date) || new Date().toISOString(),
            _imported: true
        };
    }

    async function importPurchases(file, mode = 'merge') {
        const rows = await readFile(file);
        const mapped = rows.map(mapPurchaseRow).filter(Boolean);
        if (!mapped.length) throw new Error('No valid purchase records found in file.');

        const existing = JSON.parse(localStorage.getItem('jr_purchases') || '[]');

        if (mode === 'replace') {
            localStorage.setItem('jr_purchases', JSON.stringify(mapped));
            return { total: mapped.length, added: mapped.length, skipped: 0, mode };
        }

        // Merge: skip duplicates by Reference No
        let added = 0, skipped = 0;
        const existingRefs = new Set(existing.map(p => p.ref || p.id));

        for (const rec of mapped) {
            if (existingRefs.has(rec.ref)) {
                skipped++; continue;
            }
            existing.push(rec);
            existingRefs.add(rec.ref);
            added++;
        }
        localStorage.setItem('jr_purchases', JSON.stringify(existing));
        return { total: mapped.length, added, skipped, mode };
    }


    /* ════════════════════════════════════════════════
     *  SALES IMPORT
     *  Expected XLSX columns (from import_sales_template.xlsx):
     *  Flexible mapping — will detect common column names
     * ════════════════════════════════════════════════ */
    function mapSaleRow(row) {
        // Try multiple possible column names for each field
        const getVal = (...keys) => {
            for (const k of keys) {
                if (row[k] !== undefined && row[k] !== '') return row[k];
                // Also try case-insensitive
                const found = Object.keys(row).find(rk => rk.toLowerCase().trim() === k.toLowerCase().trim());
                if (found && row[found] !== undefined && row[found] !== '') return row[found];
            }
            return '';
        };

        const invoiceNo = getVal('Invoice No.', 'Invoice No', 'Invoice', 'Invoice Number', 'Inv No', 'invoice_no', 'Reference No');
        const date = getVal('Date', 'Sale Date', 'date', 'Transaction Date');
        const customer = String(getVal('Customer Name', 'Customer', 'customer', 'customer_name', 'Client')).trim();
        const contactNumber = String(getVal('Contact Number', 'Contact No', 'Phone', 'Mobile', 'contact_number')).trim();
        const location = String(getVal('Location', 'Business Location', 'location', 'Store')).trim();
        const paymentStatusVal = String(getVal('Payment Status', 'payment_status', 'Status', 'Pay Status')).trim();
        const paymentMethodVal = String(getVal('Payment Method', 'payment_method', 'Method', 'Pay Method')).trim();
        const totalAmount = parseLKR(getVal('Total Amount', 'Grand Total', 'Total', 'total_amount', 'Amount'));
        const totalPaid = parseLKR(getVal('Total Paid', 'Paid Amount', 'total_paid', 'Paid'));
        const sellDue = parseLKR(getVal('Sell Due', 'Sale Due', 'Balance Due', 'sell_due', 'Due'));
        const sellReturnDue = parseLKR(getVal('Sell Return Due', 'Return Due', 'sell_return_due'));

        if (!invoiceNo && !customer && totalAmount === 0) return null;

        // Build payments array
        const payments = [];
        const paidAmount = totalPaid > 0 ? totalPaid : (totalAmount - sellDue);
        if (paidAmount > 0) {
            payments.push({
                amount: paidAmount,
                method: paymentMethodVal || 'Cash',
                note: 'Imported from old system'
            });
        }

        return {
            id: String(invoiceNo || ('SALE-' + Date.now() + '-' + Math.floor(Math.random() * 1000))),
            date: parseDate(date) || new Date().toISOString(),
            customer: customer || 'Walk-In Customer',
            contactNumber: contactNumber,
            items: [],
            totalPayable: totalAmount,
            totalPaying: paidAmount,
            balance: Math.max(0, totalAmount - paidAmount),
            payments: payments,
            paymentStatus: paymentStatusVal || 'Paid',
            location: location || 'JR MARKETING (PVT) LTD',
            sellNote: '',
            staffNote: '',
            sellReturnDue: sellReturnDue,
            _imported: true
        };
    }

    async function importSales(file, mode = 'merge') {
        const rows = await readFile(file);
        const mapped = rows.map(mapSaleRow).filter(Boolean);
        if (!mapped.length) throw new Error('No valid sales records found in file.');

        const existing = JSON.parse(localStorage.getItem('jr_payments') || '[]');

        if (mode === 'replace') {
            localStorage.setItem('jr_payments', JSON.stringify(mapped));
            return { total: mapped.length, added: mapped.length, skipped: 0, mode };
        }

        // Merge: skip duplicates by invoice ID
        let added = 0, skipped = 0;
        const existingIds = new Set(existing.map(s => s.id));

        for (const rec of mapped) {
            if (existingIds.has(rec.id)) {
                skipped++; continue;
            }
            existing.push(rec);
            existingIds.add(rec.id);
            added++;
        }
        localStorage.setItem('jr_payments', JSON.stringify(existing));
        return { total: mapped.length, added, skipped, mode };
    }


    /* ════════════════════════════════════════════════
     *  SUPPLIER IMPORT
     *  Old columns: Action, Contact ID, Business Name, Name, Email, Tax number,
     *    Pay term, Opening Balance, Advance Balance, Added On,
     *    Address, Mobile, Total Purchase Due, Total Purchase Return Due, Custom Field 1-10
     * ════════════════════════════════════════════════ */
    function mapSupplierRow(row) {
        const contactId = (row['Contact ID'] || '').trim();
        const businessName = (row['Business Name'] || '').trim();
        const name = (row['Name'] || '').trim();
        const email = (row['Email'] || '').trim();
        const mobile = (row['Mobile'] || '').trim();
        const address = (row['Address'] || '').trim();
        const tax = (row['Tax number'] || '').trim();
        const payTerm = (row['Pay term'] || '').trim();
        const openingBalance = parseLKR(row['Opening Balance']);
        const advanceBalance = parseLKR(row['Advance Balance']);
        const addedOn = (row['Added On'] || '').trim();
        const totalPurchaseDue = parseLKR(row['Total Purchase Due']);
        const totalPurchaseReturnDue = parseLKR(row['Total Purchase Return Due']);

        const company = businessName || name;
        if (!company) return null; // skip empty rows

        return {
            id: contactId || ('SUP-' + Date.now() + '-' + Math.floor(Math.random() * 1000)),
            company: company,
            contact: name && businessName ? name : '',
            phone: mobile,
            email: email,
            tax: tax,
            address: address,
            balance: openingBalance || totalPurchaseDue,
            totalPurchaseDue: totalPurchaseDue,
            totalPurchaseReturnDue: totalPurchaseReturnDue,
            advanceBalance: advanceBalance,
            payTerm: payTerm,
            createdAt: parseDate(addedOn) || new Date().toISOString(),
            _imported: true
        };
    }

    async function importSuppliers(file, mode = 'merge') {
        const rows = await readFile(file);
        const mapped = rows.map(mapSupplierRow).filter(Boolean);
        if (!mapped.length) throw new Error('No valid supplier records found in file.');

        const existing = JSON.parse(localStorage.getItem('jr_suppliers') || '[]');

        if (mode === 'replace') {
            localStorage.setItem('jr_suppliers', JSON.stringify(mapped));
            return { total: mapped.length, added: mapped.length, skipped: 0, mode };
        }

        // Merge: skip duplicates by Contact ID or company+phone
        let added = 0, skipped = 0;
        const existingIds = new Set(existing.map(s => s.id));
        const existingKeys = new Set(existing.map(s => `${(s.company||'').toLowerCase()}|${s.phone||''}`));

        for (const rec of mapped) {
            const key = `${(rec.company||'').toLowerCase()}|${rec.phone||''}`;
            if (existingIds.has(rec.id) || existingKeys.has(key)) {
                skipped++; continue;
            }
            existing.push(rec);
            existingIds.add(rec.id);
            existingKeys.add(key);
            added++;
        }
        localStorage.setItem('jr_suppliers', JSON.stringify(existing));
        return { total: mapped.length, added, skipped, mode };
    }


    /* ════════════════════════════════════════════════
     *  IMPORT MODAL UI — call Importer.showModal(type)
     * ════════════════════════════════════════════════ */
    function showModal(type) {
        // type: 'customers' | 'purchases' | 'sales' | 'suppliers'
        const labels = { customers: 'Customers', purchases: 'Purchases', sales: 'Sales', suppliers: 'Suppliers' };
        const accepts = '.csv,.xlsx,.xls';
        const title = `Import ${labels[type]}`;

        // Create overlay
        const overlay = document.createElement('div');
        overlay.id = 'import-overlay';
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;';

        overlay.innerHTML = `
            <div style="background:#fff;border-radius:14px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.22);animation:imSlide .18s ease">
                <style>@keyframes imSlide{from{opacity:0;transform:translateY(-18px)}to{opacity:1;transform:translateY(0)}}</style>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb">
                    <h2 style="font-size:16px;font-weight:700;color:#111827;margin:0;display:flex;align-items:center;gap:8px">
                        <svg width="20" height="20" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        ${title}
                    </h2>
                    <button id="import-close-btn" style="width:32px;height:32px;border:none;background:#f3f4f6;border-radius:6px;cursor:pointer;font-size:18px;color:#6b7280;display:flex;align-items:center;justify-content:center">✕</button>
                </div>
                <div style="padding:20px">
                    <div style="border:2px dashed #d1d5db;border-radius:10px;padding:30px 20px;text-align:center;margin-bottom:16px;cursor:pointer;transition:border-color .2s;background:#fafbfc" id="import-drop-zone">
                        <svg width="36" height="36" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p style="font-size:14px;color:#374151;font-weight:600;margin:0 0 4px">Click to select file or drag & drop</p>
                        <p style="font-size:12px;color:#9ca3af;margin:0">Accepts .csv and .xlsx files</p>
                        <input type="file" id="import-file-input" accept="${accepts}" style="display:none">
                    </div>
                    <div id="import-file-info" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#166534;display:none">
                        <strong id="import-file-name"></strong> — <span id="import-file-size"></span>
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Import Mode</label>
                        <div style="display:flex;gap:12px">
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;color:#374151">
                                <input type="radio" name="import-mode" value="merge" checked style="accent-color:#16a34a"> Merge (add new, skip duplicates)
                            </label>
                            <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;color:#374151">
                                <input type="radio" name="import-mode" value="replace" style="accent-color:#dc2626"> Replace all existing data
                            </label>
                        </div>
                    </div>
                    <div id="import-status" style="display:none;padding:12px;border-radius:8px;margin-bottom:14px;font-size:13px"></div>
                    <div style="display:flex;gap:8px;justify-content:flex-end">
                        <button id="import-cancel-btn" style="height:38px;padding:0 18px;border:1px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;cursor:pointer">Cancel</button>
                        <button id="import-submit-btn" style="height:38px;padding:0 20px;border:none;border-radius:8px;background:linear-gradient(135deg,#0a5c2e,#16a34a);color:#fff;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;opacity:.5;pointer-events:none">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Import Data
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        // Elements
        const fileInput = document.getElementById('import-file-input');
        const dropZone = document.getElementById('import-drop-zone');
        const fileInfo = document.getElementById('import-file-info');
        const fileName = document.getElementById('import-file-name');
        const fileSize = document.getElementById('import-file-size');
        const submitBtn = document.getElementById('import-submit-btn');
        const statusDiv = document.getElementById('import-status');
        let selectedFile = null;

        // Close handlers
        const close = () => { overlay.remove(); document.body.style.overflow = ''; };
        document.getElementById('import-close-btn').addEventListener('click', close);
        document.getElementById('import-cancel-btn').addEventListener('click', close);
        overlay.addEventListener('click', e => { if (e.target === overlay) close(); });

        // File selection
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#16a34a'; dropZone.style.background = '#f0fdf4'; });
        dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#d1d5db'; dropZone.style.background = '#fafbfc'; });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.style.borderColor = '#d1d5db'; dropZone.style.background = '#fafbfc';
            if (e.dataTransfer.files.length) setFile(e.dataTransfer.files[0]);
        });
        fileInput.addEventListener('change', () => { if (fileInput.files.length) setFile(fileInput.files[0]); });

        function setFile(f) {
            selectedFile = f;
            fileName.textContent = f.name;
            fileSize.textContent = (f.size / 1024).toFixed(1) + ' KB';
            fileInfo.style.display = 'block';
            submitBtn.style.opacity = '1';
            submitBtn.style.pointerEvents = 'auto';
            statusDiv.style.display = 'none';
        }

        // Submit
        submitBtn.addEventListener('click', async () => {
            if (!selectedFile) return;
            const mode = document.querySelector('input[name="import-mode"]:checked').value;

            submitBtn.style.opacity = '.5';
            submitBtn.style.pointerEvents = 'none';
            submitBtn.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><style>@keyframes spin{to{transform:rotate(360deg)}}</style><circle cx="12" cy="12" r="10"/></svg> Importing...';

            try {
                let result;
                if (type === 'customers') result = await importCustomers(selectedFile, mode);
                else if (type === 'purchases') result = await importPurchases(selectedFile, mode);
                else if (type === 'sales') result = await importSales(selectedFile, mode);
                else if (type === 'suppliers') result = await importSuppliers(selectedFile, mode);

                statusDiv.style.display = 'block';
                statusDiv.style.background = '#f0fdf4';
                statusDiv.style.border = '1px solid #bbf7d0';
                statusDiv.style.color = '#166534';
                statusDiv.innerHTML = `
                    <strong>✅ Import Successful!</strong><br>
                    Total records in file: <strong>${result.total}</strong><br>
                    Added: <strong>${result.added}</strong>
                    ${result.skipped > 0 ? ` | Skipped (duplicates): <strong>${result.skipped}</strong>` : ''}
                    ${result.mode === 'replace' ? '<br><em>All previous data replaced.</em>' : ''}
                `;

                // Call page-specific refresh if available
                if (typeof window._importRefresh === 'function') window._importRefresh();
                if (typeof Toast !== 'undefined') Toast.success(`${result.added} ${labels[type].toLowerCase()} imported successfully!`);

                // Reset button
                submitBtn.innerHTML = '✓ Done';
                submitBtn.style.background = '#16a34a';
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'auto';
                submitBtn.addEventListener('click', close, { once: true });

            } catch (err) {
                statusDiv.style.display = 'block';
                statusDiv.style.background = '#fef2f2';
                statusDiv.style.border = '1px solid #fecaca';
                statusDiv.style.color = '#dc2626';
                statusDiv.innerHTML = `<strong>❌ Import Failed:</strong> ${err.message}`;
                submitBtn.innerHTML = 'Import Data';
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'auto';
            }
        });
    }

    /* ─── Public API ─── */
    return {
        importCustomers,
        importPurchases,
        importSales,
        importSuppliers,
        showModal,
        readFile,
        parseCSV,
        parseLKR,
        parseDate
    };

})();
