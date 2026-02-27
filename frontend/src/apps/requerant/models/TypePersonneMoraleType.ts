const TypesPersonneMorale = [
  "ASSUREUR",
  "SCI",
  "PROFESSIONNEL",
  "BAILLEUR_SOCIAL",
  "SYNDIC",
  "ASSOCIATION",
  "COLLECTIVITE",
] as const;

type TypePersonneMoraleType = (typeof TypesPersonneMorale)[number];

const PersonneMoraleTypeLibelles: { [key in TypePersonneMoraleType]: string } =
  {
    ASSOCIATION: "Association",
    ASSUREUR: "Assureur",
    BAILLEUR_SOCIAL: "Bailleur social",
    COLLECTIVITE: "Association territoriale",
    PROFESSIONNEL: "Professionnel du secteur privé",
    SCI: "SCI",
    SYNDIC: "Syndic",
  };

const getLibelleTypePersonneMorale = (type: TypePersonneMoraleType): string =>
  PersonneMoraleTypeLibelles[type];

export {
  TypesPersonneMorale,
  getLibelleTypePersonneMorale,
  type TypePersonneMoraleType,
};
