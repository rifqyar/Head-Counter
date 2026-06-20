export function parseScannerPayload(value, expectedOrigin = window.location.origin) {
    const payload = String(value || '').trim();

    if (!payload || /<\s*script/i.test(payload) || /^javascript:/i.test(payload)) {
        return null;
    }

    if (/^[A-Za-z0-9_-]{32,160}$/.test(payload)) {
        return payload;
    }

    try {
        const url = new URL(payload);
        if (url.origin !== expectedOrigin) {
            return null;
        }

        const match = url.pathname.match(/^\/scan\/participant\/([A-Za-z0-9_-]{32,160})$/);

        return match ? match[1] : null;
    } catch (error) {
        return null;
    }
}

export function shouldAcceptDecode(state, token, now = Date.now()) {
    if (!token || state.pending) {
        return false;
    }

    if (state.lastToken === token && now - state.lastAt < state.windowMs) {
        return false;
    }

    state.lastToken = token;
    state.lastAt = now;

    return true;
}
