export type PrescriptionResult = {
  rempli: boolean;
  expiration: Date | null;
  detail: string;
};

export const formatDateFr = (date: Date): string => {
  const s = date.toLocaleDateString("fr-FR", { day: "numeric", month: "long", year: "numeric" });
  return s.replace(/^1 /, "1er ");
};

export const calculerPrescription = (dateDecision?: Date): PrescriptionResult => {
  if (!dateDecision) return { rempli: false, expiration: null, detail: "Date non renseignée" };
  if (isNaN(dateDecision.getTime())) return { rempli: false, expiration: null, detail: "Date invalide" };
  const expiration = new Date(dateDecision.getFullYear() + 5, 0, 1);
  const rempli = new Date() < expiration;
  const fin = new Date(expiration.getFullYear() - 1, 11, 31);
  return {
    rempli,
    expiration,
    detail: rempli
      ? `Dans les délais (recevable jusqu'au ${formatDateFr(fin)} inclus)`
      : `Prescrite depuis le ${formatDateFr(expiration)}`,
  };
};
