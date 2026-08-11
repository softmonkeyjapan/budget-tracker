export function parseMonth(month) {
    const [year, m] = month.split('-').map(Number);

    return new Date(year, m - 1, 1);
}

export function formatMonth(month, format = { month: 'long', year: 'numeric' }) {
    return parseMonth(month).toLocaleDateString('fr-FR', format);
}
