export const ActionContentieuse = {
  Oui: "oui",
  Non: "non",
} as const;
export type ActionContentieuse = (typeof ActionContentieuse)[keyof typeof ActionContentieuse];

export const TypeDecision = {
  JugementPremiereInstance: "jugement_premiere_instance",
  ArretCourAppel: "arret_cour_appel",
  ArretCourCassation: "arret_cour_cassation",
  Aucune: "aucune",
} as const;
export type TypeDecision = (typeof TypeDecision)[keyof typeof TypeDecision];

export const PieceProcedure = {
  Assignation: "assignation",
  DecisionsJuge: "decisions_juge",
  Calendrier: "calendrier",
  Ecritures: "ecritures",
  Convocations: "convocations",
  Renvoi: "renvoi",
  Echanges: "echanges",
  Appel: "appel",
} as const;
export type PieceProcedure = (typeof PieceProcedure)[keyof typeof PieceProcedure];

export type StepProps = {
  onPrecedent: () => void;
  onSuivant: () => void;
  isLastStep?: boolean;
};
