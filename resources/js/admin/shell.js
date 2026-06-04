/*
 * Admin shell — sidebar collapse / mobile drawer + active-link highlighting.
 *
 * Layout state lives on <body> as data attributes so CSS does all the work:
 *   <body data-sidebar-collapsed="false" data-sidebar-mobile-open="false">
 */

const STORAGE_KEY = 'xgp_admin_sidebar_collapsed';
const THEME_KEY = 'xgp_admin_theme';

function setCollapsed(collapsed) {
    document.body.setAttribute('data-sidebar-collapsed', collapsed ? 'true' : 'false');
    try {
        localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
    } catch (e) { /* private mode etc. — fine to ignore */ }
}

function setMobileOpen(open) {
    document.body.setAttribute('data-sidebar-mobile-open', open ? 'true' : 'false');
}

function bindShell() {
    // Restore persisted collapse preference (desktop only).
    let collapsed = false;
    try {
        collapsed = localStorage.getItem(STORAGE_KEY) === '1';
    } catch (e) { /* ignore */ }
    setCollapsed(collapsed);
    setMobileOpen(false);

    document.querySelectorAll('[data-action="adm-toggle-sidebar"]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const isMobile = window.matchMedia('(max-width: 1023px)').matches;
            if (isMobile) {
                const open = document.body.getAttribute('data-sidebar-mobile-open') === 'true';
                setMobileOpen(!open);
            } else {
                const c = document.body.getAttribute('data-sidebar-collapsed') === 'true';
                setCollapsed(!c);
            }
        });
    });

    // Click outside / overlay → close mobile drawer.
    document.querySelectorAll('[data-action="adm-close-sidebar"]').forEach((el) => {
        el.addEventListener('click', () => setMobileOpen(false));
    });
}

function applyTheme(theme) {
    const html = document.documentElement;
    html.classList.remove("theme-light", "theme-dark");
    html.classList.add(theme === "light" ? "theme-light" : "theme-dark");
    try { localStorage.setItem(THEME_KEY, theme); } catch (e) { /* ignore */ }

    // Toggle the icon visibility (sun shows in light mode, moon in dark).
    document.querySelectorAll("[data-theme-icon-dark]").forEach((el) => {
        el.style.display = theme === "dark" ? "" : "none";
    });
    document.querySelectorAll("[data-theme-icon-light]").forEach((el) => {
        el.style.display = theme === "light" ? "" : "none";
    });
}

function bindTheme() {
    let theme = "dark";
    try {
        const stored = localStorage.getItem(THEME_KEY);
        if (stored === "light" || stored === "dark") theme = stored;
    } catch (e) { /* ignore */ }
    applyTheme(theme);

    document.querySelectorAll('[data-action="adm-toggle-theme"]').forEach((btn) => {
        btn.addEventListener("click", () => {
            const current = document.documentElement.classList.contains("theme-light") ? "light" : "dark";
            applyTheme(current === "dark" ? "light" : "dark");
        });
    });
}

function highlightActiveLink() {
    // URL is /admin/<page> — first segment after /admin/. The active state
    // also gets set server-side on the rendered <a>, this is a fallback.
    const segments = window.location.pathname.split('/').filter(Boolean);
    const adminIdx = segments.indexOf('admin');
    const current = adminIdx >= 0 ? (segments[adminIdx + 1] || 'home') : '';

    document.querySelectorAll('.adm-sidebar-link[data-page]').forEach((link) => {
        if (link.getAttribute('data-page') === current) {
            link.classList.add('is-active');
        }
    });
}

export function initAdminShell() {
    bindShell();
    bindTheme();
    highlightActiveLink();
}
