const QualiteRequerants = {
  PRO: "Propriétaire",
  LOC: "Locataire",
  BAI: "Bailleur",
  AUT: "Autre",
};

type QualiteRequerant = keyof typeof QualiteRequerants;

export { QualiteRequerants, QualiteRequerant };
