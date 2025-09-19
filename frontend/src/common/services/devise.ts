const formateurMontantEuro = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "EUR",
    trailingZeroDisplay: "auto",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

export const convertirEnEuros = function (montant: number): string {
    return formateurMontantEuro.format(montant);
}