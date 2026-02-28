/**
 * ============================================
 * TOAST NOTIFICATION COMPONENT
 * Lightweight toast messages
 * JR Marketing - Sri Lanka
 * ============================================
 */

const Toast = {
    _container: null,

    _getContainer() {
        if (!this._container) {
            this._container = document.createElement('div');
            this._container.id = 'toast-container';
            this._container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(this._container);
        }
        return this._container;
    },

    show(message, type = 'info', duration = 3500) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        const colors = {
            success: { bg: '#f0fff4', border: '#c3f0c3', text: '#1a7a1a', icon: '#2d9e2d' },
            error:   { bg: '#fff0f1', border: '#ffd6d8', text: '#c0392b', icon: '#e63946' },
            warning: { bg: '#fffbf0', border: '#ffe8a1', text: '#9a6700', icon: '#f59e0b' },
            info:    { bg: '#f0f6ff', border: '#c3d5ff', text: '#1a4aae', icon: '#4361ee' },
        };
        const c = colors[type] || colors.info;

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${c.bg};
            border: 1px solid ${c.border};
            color: ${c.text};
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 320px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            gap: 10px;
            pointer-events: all;
            animation: toastIn 0.3s ease;
            transition: opacity 0.3s ease, transform 0.3s ease;
        `;

        toast.innerHTML = `
            <span style="font-size:15px;color:${c.icon};font-weight:700;">${icons[type]}</span>
            <span>${message}</span>
        `;

        const container = this._getContainer();
        container.appendChild(toast);

        // Inject keyframe if not already
        if (!document.getElementById('toast-style')) {
            const style = document.createElement('style');
            style.id = 'toast-style';
            style.textContent = `
                @keyframes toastIn {
                    from { opacity: 0; transform: translateX(30px); }
                    to   { opacity: 1; transform: translateX(0); }
                }
            `;
            document.head.appendChild(style);
        }

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(30px)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(msg, duration) { this.show(msg, 'success', duration); },
    error(msg, duration)   { this.show(msg, 'error', duration); },
    warning(msg, duration) { this.show(msg, 'warning', duration); },
    info(msg, duration)    { this.show(msg, 'info', duration); },
};

window.Toast = Toast;
