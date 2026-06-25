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
  ActeIntroductif: "acte_introductif",
  Ecritures: "ecritures",
  Convocations: "convocations",
  Echanges: "echanges",
  DocumentsProcedure: "documents_procedure",
  Aucune: "aucune",
} as const;
export type PieceProcedure = (typeof PieceProcedure)[keyof typeof PieceProcedure];

import { TestEligibilite } from "@/apps/public/models/TestEligibilite";

export type StepProps = {
  onPrecedent: () => void;
  onSuivant: () => void | Promise<void>;
  isLastStep?: boolean;
  test?: TestEligibilite;
};
