export type PrescriptionResult = {
  rempli: boolean;
  expiration: Date | null;
  detail: string;
};

export const calculerPrescription = (dateDecision?: string): PrescriptionResult => {
  if (!dateDecision) return { rempli: false, expiration: null, detail: "Date non renseignée" };
  const date = new Date(dateDecision);
  if (isNaN(date.getTime())) return { rempli: false, expiration: null, detail: "Date invalide" };
  const expiration = new Date(date.getFullYear() + 5, 0, 1);
  const rempli = new Date() < expiration;
  return {
    rempli,
    expiration,
    detail: rempli
      ? `Dans les délais (recevable jusqu'au ${expiration.toLocaleDateString("fr-FR")})`
      : `Prescrite depuis le ${expiration.toLocaleDateString("fr-FR")}`,
  };
};
