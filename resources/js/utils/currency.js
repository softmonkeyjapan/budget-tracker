export function amountLabel(amount) {
    return `${new Intl.NumberFormat('fr-FR').format(amount)} FCFP`;
}
