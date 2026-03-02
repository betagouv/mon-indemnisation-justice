const RapportAuLogements = [
  "PROPRIETAIRE",
  "LOCATAIRE",
  "BAILLEUR",
  "AUTRE",
] as const;
type RapportAuLogement = (typeof RapportAuLogements)[number];

const RapportAuLogementLibelles: { [key in RapportAuLogement]: string } = {
  PROPRIETAIRE: "Propriétaire occupant",
  BAILLEUR: "Propriétaire bailleur",
  LOCATAIRE: "Locataire",
  AUTRE: "Autre",
};

const getRapportAuLogementLibelle = (
  rapportAuLogement: RapportAuLogement,
): string => RapportAuLogementLibelles[rapportAuLogement];

export {
  RapportAuLogements,
  getRapportAuLogementLibelle,
  type RapportAuLogement,
};
