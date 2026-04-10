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

const libellerNomTypePersonneMorale = (
  type: TypePersonneMoraleType,
  options: { defini: boolean },
): string => {
  switch (type) {
    case "ASSOCIATION":
      return `${defini ? "l'" : "une "}association`;
    case "ASSUREUR":
      return `${defini ? "l'" : "un "}assureur`;
    case "BAILLEUR_SOCIAL":
      return `${defini ? "le " : "un "}bailleur social`;
    case "COLLECTIVITE":
      return `${defini ? "la " : "une "}collectivité territoriale`;
    case "ENTREPRISE_PRIVEE":
      return `${defini ? "l'" : "une "}entreprise du secteur privé`;
    case "ETABLISSEMENT_PUBLIC":
      return `${defini ? "l'" : "un "}établissement public`;
    case "SCI":
      return `${defini ? "la " : "une "}SCI`;
    case "SYNDIC":
      return `${defini ? "le " : "un "}syndic`;
  }
};

export {
  TypesPersonneMorale,
  getLibelleTypePersonneMorale,
  libellerNomTypePersonneMorale,
  type TypePersonneMoraleType,
};
