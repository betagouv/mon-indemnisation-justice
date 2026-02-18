const PersonneMoraleTypes = [
  "ASSUREUR",
  "SCI",
  "PROFESSIONNEL",
  "BAILLEUR_SOCIAL",
  "SYNDIC",
  "ASSOCIATION",
  "COLLECTIVITE",
] as const;

type PersonneMoraleType = (typeof PersonneMoraleTypes)[number];

const PersonneMoraleTypeLibelles: { [key in PersonneMoraleType]: string } = {
  ASSOCIATION: "Association",
  ASSUREUR: "Assureur",
  BAILLEUR_SOCIAL: "Bailleur social",
  COLLECTIVITE: "Association territoriale",
  PROFESSIONNEL: "Professionnel du secteur privé",
  SCI: "SCI",
  SYNDIC: "Syndic",
};

const getLibelleTypePersonneMorale = (type: PersonneMoraleType): string =>
  PersonneMoraleTypeLibelles[type];

export {
  PersonneMoraleTypes,
  getLibelleTypePersonneMorale,
  type PersonneMoraleType,
};
