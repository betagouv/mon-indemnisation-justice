const Civilites = ["M", "MME"] as const;
type Civilite = (typeof Civilites)[number];

const CiviliteLibelles: {
  [key in Civilite]: string;
} = {
  M: "Monsieur",
  MME: "Madame",
};

const getCiviliteLibelle = (civilite: Civilite): string =>
  CiviliteLibelles[civilite];

const getCivilite = (libelle: string): Civilite | undefined => {
  return Civilites.includes(libelle) ? (libelle as Civilite) : undefined;
};

export { Civilites, type Civilite, getCivilite, getCiviliteLibelle };
