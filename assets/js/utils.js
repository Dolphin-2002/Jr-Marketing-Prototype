/**
 * ============================================
 * UTILITY HELPERS
 * General-purpose utility functions
 * JR Marketing - Sri Lanka
 * ============================================
 */

const Utils = {
    /**
     * Debounce a function
     */
    debounce(fn, delay = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    },

    /**
     * Format date for Sri Lanka locale
     */
    formatDate(date, options = {}) {
        const defaults = {
            year: 'numeric', month: 'long', day: 'numeric'
        };
        return new Intl.DateTimeFormat('en-LK', { ...defaults, ...options })
            .format(date instanceof Date ? date : new Date(date));
    },

    /**
     * Format currency in Sri Lankan Rupees
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-LK', {
            style: 'currency',
            currency: 'LKR',
            minimumFractionDigits: 2
        }).format(amount);
    },

    /**
     * Validate email
     */
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    /**
     * Validate Sri Lanka phone number
     */
    isValidSLPhone(phone) {
        return /^(?:\+94|0)?[0-9]{9}$/.test(phone.replace(/\s/g, ''));
    },

    /**
     * Get query parameter from URL
     */
    getQueryParam(key) {
        return new URLSearchParams(window.location.search).get(key);
    },

    /**
     * Sanitize HTML to prevent XSS
     */
    sanitize(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    },

    /**
     * Show/hide element
     */
    show(el) {
        const elem = typeof el === 'string' ? document.querySelector(el) : el;
        if (elem) elem.style.display = '';
    },
    hide(el) {
        const elem = typeof el === 'string' ? document.querySelector(el) : el;
        if (elem) elem.style.display = 'none';
    },

    /**
     * Toggle class
     */
    toggleClass(el, cls) {
        const elem = typeof el === 'string' ? document.querySelector(el) : el;
        if (elem) elem.classList.toggle(cls);
    },

    /**
     * Simple local storage wrapper
     */
    storage: {
        set(key, value) {
            try { localStorage.setItem(key, JSON.stringify(value)); } catch(e) {}
        },
        get(key) {
            try { return JSON.parse(localStorage.getItem(key)); } catch(e) { return null; }
        },
        remove(key) {
            try { localStorage.removeItem(key); } catch(e) {}
        },
        clear() {
            try { localStorage.clear(); } catch(e) {}
        }
    },

    /**
     * Migrate old prodata_ localStorage keys to jr_ prefix (one-time)
     */
    migrateStorageKeys() {
        if (localStorage.getItem('jr_migrated')) return;
        const map = {
            'prodata_session':      'jr_session',
            'prodata_remember':     'jr_remember',
            'prodata_products':     'jr_products',
            'prodata_payments':     'jr_payments',
            'prodata_drafts':       'jr_drafts',
            'prodata_quotations':   'jr_quotations',
            'prodata_suspended':    'jr_suspended',
            'prodata_credit_sales': 'jr_credit_sales',
            'prodata_expenses':     'jr_expenses'
        };
        Object.entries(map).forEach(([oldKey, newKey]) => {
            const val = localStorage.getItem(oldKey);
            if (val !== null && localStorage.getItem(newKey) === null) {
                localStorage.setItem(newKey, val);
            }
            localStorage.removeItem(oldKey);
        });
        localStorage.setItem('jr_migrated', '1');
    }
};

window.Utils = Utils;

/* Auto-migrate old prodata_ keys on first load */
Utils.migrateStorageKeys();
