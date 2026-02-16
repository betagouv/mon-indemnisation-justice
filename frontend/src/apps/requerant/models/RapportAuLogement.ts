const RapportAuLogements = [
  "PRO",
  "LOC",
  "BAI",
  "AUT",
  "ASS",
  "SCI",
  "BSO",
  "SYN",
  "ETP",
] as const;
type RapportAuLogement = (typeof RapportAuLogements)[number];

const RapportAuLogementLibelles: { [key in RapportAuLogement]: string } = {
  PRO: "Propriétaire",
  LOC: "Locataire",
  BAI: "Bailleur",
  AUT: "Autre",
  ASS: "Assureur",
  SCI: "Gestionnaire de la SCI",
  BSO: "Bailleur social",
  SYN: "Syndic",
  ETP: "Établissement public",
};

const getListeRapportAuLogement = (
  estPersonneMorale: boolean,
): RapportAuLogement[] => {
  return estPersonneMorale
    ? RapportAuLogements.values().toArray()
    : ["PRO", "BAI", "LOC", "AUT"];
};

const getRapportAuLogementLibelle = (
  rapportAuLogement: RapportAuLogement,
): string => RapportAuLogementLibelles[rapportAuLogement];

export {
  type RapportAuLogement,
  getListeRapportAuLogement,
  getRapportAuLogementLibelle,
};
