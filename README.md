# JR Marketing — Prototype Website
**POS, ERP, Stock Management & Invoicing Application**  
Sri Lanka | JR Marketing

---

## 📁 Project Structure

```
Jr-Marketing-Prototype/
│
├── index.html               ← Login Page (main entry)
├── dashboard.html           ← Dashboard (post-login)
├── forgot-password.html     ← Forgot Password page
│
├── components/              ← Reusable HTML components
│   ├── login-logo.html      ← Logo + tagline block
│   ├── login-form.html      ← Login form markup
│   └── login-footer.html    ← Copyright footer
│
└── assets/
    ├── css/
    │   ├── variables.css    ← CSS custom properties / design tokens
    │   ├── base.css         ← Reset + utility classes
    │   ├── components.css   ← Buttons, inputs, cards, alerts, badges
    │   └── login.css        ← Login page-specific styles
    │
    └── js/
        ├── utils.js         ← General utilities (date, currency LKR, storage)
        ├── auth.js          ← Auth module (login, session, guard)
        ├── toast.js         ← Toast notification component
        ├── component-loader.js ← Loads HTML components dynamically
        └── login.js         ← Login page controller
```

---

## 🚀 How to Run

> **Important:** Because components are loaded via `fetch()`, you need a local HTTP server.

### Option 1 — VS Code Live Server (recommended)
1. Install the **Live Server** extension in VS Code.
2. Right-click `index.html` → **Open with Live Server**.

### Option 2 — Python
```bash
python -m http.server 5500
```
Then open `http://localhost:5500`

### Option 3 — Node.js
```bash
npx serve .
```

---

## 🔑 Demo Login Credentials

| Username  | Password    | Role          |
|-----------|-------------|---------------|
| admin     | admin123    | Administrator |
| manager   | manager123  | Manager       |
| cashier   | cashier123  | Cashier       |
| demo      | demo        | Demo User     |

---

## 🧩 Component System

Components live in `/components/` as standalone HTML fragments.  
They are loaded dynamically by `ComponentLoader.load()`:

```js
ComponentLoader.load('#target-selector', 'components/my-component.html', callback);
```

This keeps pages clean and components reusable across the site.

---

## 🎨 Design System

All colors, fonts, spacing, and radii are defined as CSS variables in `variables.css`.  
Modify them once to update the entire site's appearance.

---

## 🇱🇰 Sri Lanka Localisation

- Currency formatted as **LKR (Rs.)** via `Utils.formatCurrency()`
- Date formatted in `en-LK` locale via `Utils.formatDate()`
- Phone validation for Sri Lanka numbers: `Utils.isValidSLPhone()`

---

*JR Marketing — V6.3 | © 2025 JR Marketing*
