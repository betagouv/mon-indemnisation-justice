const TypesPersonneMorale = [
  "ASSUREUR",
  "SCI",
  "ENTREPRISE_PRIVEE",
  "BAILLEUR_SOCIAL",
  "SYNDIC",
  "ASSOCIATION",
  "COLLECTIVITE",
  "ETABLISSEMENT_PUBLIC",
] as const;

type TypePersonneMoraleType = (typeof TypesPersonneMorale)[number];

const PersonneMoraleTypeLibelles: { [key in TypePersonneMoraleType]: string } =
  {
    ASSOCIATION: "Association",
    ASSUREUR: "Assureur",
    BAILLEUR_SOCIAL: "Bailleur social",
    COLLECTIVITE: "Collectivité territoriale",
    ENTREPRISE_PRIVEE: "Entreprise du secteur privé",
    ETABLISSEMENT_PUBLIC: "Etablissement public",
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
