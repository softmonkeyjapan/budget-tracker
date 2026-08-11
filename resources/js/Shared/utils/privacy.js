const STORAGE_KEY = 'budget-tracker-privacy';

export function getPreferredPrivacy() {
    try {
        return localStorage.getItem(STORAGE_KEY) === 'hidden';
    } catch {
        // localStorage unavailable (privacy mode, file://, ...) — fall through.
    }

    return false;
}

export function applyPrivacy(hidden, persist = true) {
    if (hidden) {
        document.documentElement.dataset.privacy = 'hidden';
    } else {
        delete document.documentElement.dataset.privacy;
    }

    if (persist) {
        try {
            localStorage.setItem(STORAGE_KEY, hidden ? 'hidden' : 'visible');
        } catch {
            // ignore
        }
    }
}

export function currentPrivacy() {
    return document.documentElement.dataset.privacy === 'hidden';
}
