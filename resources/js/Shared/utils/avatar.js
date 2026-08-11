const PALETTE = ['#2F80ED', '#23C48E', '#FF5B62', '#8A5CF6', '#FF8A66', '#FFB315'];

export function initials(name) {
    const parts = name.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) {
        return '?';
    }

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

export function colorForName(name) {
    let hash = 0;

    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }

    return PALETTE[Math.abs(hash) % PALETTE.length];
}
