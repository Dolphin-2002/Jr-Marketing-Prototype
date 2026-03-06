/**
 * ============================================
 * AUTH MODULE
 * Handles login, session, and validation
 * JR Marketing - Sri Lanka
 * ============================================
 */

const Auth = {
    SESSION_KEY: 'jr_session',
    REMEMBER_KEY: 'jr_remember',

    /**
     * Demo credentials (replace with real API in production)
     */
    _demoUsers: [
        { username: 'admin',    password: 'admin123',  role: 'Administrator', name: 'System Admin' },
        { username: 'manager',  password: 'manager123', role: 'Manager',      name: 'Branch Manager' },
        { username: 'cashier',  password: 'cashier123', role: 'Cashier',      name: 'Cash Counter 1' },
        { username: 'demo',     password: 'demo',       role: 'Demo',         name: 'Demo User' },
    ],

    /**
     * Attempt login with username/password
     * Returns { success, user, message }
     */
    login(username, password) {
        const trimUser = username.trim();
        const trimPass = password.trim();

        if (!trimUser || !trimPass) {
            return { success: false, message: 'Please enter your username and password.' };
        }

        const user = this._demoUsers.find(
            u => u.username === trimUser && u.password === trimPass
        );

        if (!user) {
            return { success: false, message: 'Invalid username or password. Please try again.' };
        }

        const session = {
            user: { username: user.username, name: user.name, role: user.role },
            loginTime: new Date().toISOString()
        };

        Utils.storage.set(this.SESSION_KEY, session);
        return { success: true, user: session.user, message: `Welcome, ${user.name}!` };
    },

    /**
     * Log out and clear session
     */
    logout() {
        Utils.storage.remove(this.SESSION_KEY);
        window.location.href = 'index.html';
    },

    /**
     * Check if user is logged in
     */
    isLoggedIn() {
        return !!Utils.storage.get(this.SESSION_KEY);
    },

    /**
     * Get current user session
     */
    getSession() {
        return Utils.storage.get(this.SESSION_KEY);
    },

    /**
     * Save "Remember Me" username
     */
    saveRemember(username) {
        Utils.storage.set(this.REMEMBER_KEY, { username });
    },

    /**
     * Get saved username
     */
    getRemembered() {
        return Utils.storage.get(this.REMEMBER_KEY);
    },

    /**
     * Clear remembered username
     */
    clearRemember() {
        Utils.storage.remove(this.REMEMBER_KEY);
    },

    /**
     * Guard: redirect to login if not authenticated
     */
    requireAuth() {
        if (!this.isLoggedIn()) {
            window.location.href = 'index.html';
        }
    }
};

window.Auth = Auth;
