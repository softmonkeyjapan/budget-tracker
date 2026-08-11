const STORAGE_KEY = 'budget-tracker-theme';

export function getPreferredTheme() {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);

        if (saved) {
            return saved;
        }
    } catch {
        // localStorage unavailable (privacy mode, file://, ...) — fall through.
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function applyTheme(theme, persist = true) {
    document.documentElement.dataset.theme = theme;

    if (persist) {
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch {
            // ignore
        }
    }
}

export function currentTheme() {
    return document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
}
