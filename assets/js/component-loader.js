/**
 * ============================================
 * COMPONENT LOADER
 * Loads HTML components into placeholders
 * JR Marketing - Sri Lanka
 * ============================================
 */

const ComponentLoader = {
    /**
     * Load an HTML component into a target element
     * @param {string} targetSelector - CSS selector of the target element
     * @param {string} componentPath - Path to the component HTML file
     * @param {Function} [callback] - Optional callback after loading
     */
    load(targetSelector, componentPath, callback) {
        const target = document.querySelector(targetSelector);
        if (!target) {
            console.warn(`[ComponentLoader] Target not found: ${targetSelector}`);
            return;
        }

        fetch(componentPath)
            .then(res => {
                if (!res.ok) throw new Error(`Failed to load: ${componentPath}`);
                return res.text();
            })
            .then(html => {
                target.innerHTML = html;
                if (typeof callback === 'function') callback(target);
            })
            .catch(err => {
                console.error(`[ComponentLoader] Error:`, err);
            });
    },

    /**
     * Load multiple components in parallel
     * @param {Array} components - Array of { target, path, callback }
     */
    loadAll(components) {
        components.forEach(({ target, path, callback }) => {
            this.load(target, path, callback);
        });
    }
};

window.ComponentLoader = ComponentLoader;
