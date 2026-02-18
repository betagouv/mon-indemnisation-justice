const RapportAuLogements = ["PRO", "LOC", "BAI", "AUT"] as const;
type RapportAuLogement = (typeof RapportAuLogements)[number];

const RapportAuLogementLibelles: { [key in RapportAuLogement]: string } = {
  PRO: "Propriétaire occupant",
  BAI: "Propriétaire bailleur",
  LOC: "Locataire",
  AUT: "Autre",
};

const getRapportAuLogementLibelle = (
  rapportAuLogement: RapportAuLogement,
): string => RapportAuLogementLibelles[rapportAuLogement];

export {
  RapportAuLogements,
  getRapportAuLogementLibelle,
  type RapportAuLogement,
};
