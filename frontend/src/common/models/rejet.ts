export const MotifsRejetBrisPorte = [
  "MIS_EN_CAUSE",
  "MIS_EN_CAUSE_CHIEN",
  "LOCATAIRE",
  "LOCATAIRE_CHIEN",
  "LOCATAIRE_AIRBNB",
  "LOCATAIRE_HEBERGEANT",
  "LOCATAIRE_PARTIES_COMMUNES",
] as const;

export type MotifRejetBrisPorte = (typeof MotifsRejetBrisPorte)[number];

const libellesMotifsRejetBrisPorte = {
  MIS_EN_CAUSE: "Le requérant est mis en cause",
  MIS_EN_CAUSE_CHIEN:
    "Le requérant est mis en cause par l'intervention d'un chien renifleur",
  LOCATAIRE: "Le locataire est mis en cause",
  LOCATAIRE_CHIEN:
    "Le locataire est mis en cause par l'intervention d'un chien renifleur",
  LOCATAIRE_AIRBNB:
    "Le locataire, via une plateforme type Airbnb, est mis en cause",
  LOCATAIRE_HEBERGEANT: "Le locataire hébergeait la personne mise en cause",
  LOCATAIRE_PARTIES_COMMUNES:
    "Le locataire est mis en cause et l'intervention a entrainé des dégradations dans les parties communes",
};

export const getLibelleMotifRejetBrisPorte = (
  motifRejetBrisPorte: MotifRejetBrisPorte | string,
): string => {
  return libellesMotifsRejetBrisPorte[motifRejetBrisPorte];
};
