export const calculerPrescription = (dateDecision?: Date): Date | undefined => {
  if (!dateDecision || isNaN(dateDecision.getTime())) return undefined;

  return new Date(dateDecision.getFullYear() + 5, 0, 1);
};

export const calculerDateFin = (datePrescription?: Date): Date | undefined => {
  return datePrescription
    ? new Date(datePrescription.getFullYear() - 1, 11, 31)
    : undefined;
};
