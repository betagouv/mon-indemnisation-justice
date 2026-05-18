import React from "react";
import { ActionContentieuse, TypeDecision, PieceProcedure } from "../types";
import type { ReponsesEligibilite, StepProps } from "../types";
import { calculerPrescription } from "../eligibilite.utils";
import { StepDateDecision } from "./StepDateDecision";
import { StepActionContentieuse } from "./StepActionContentieuse";
import { StepTypeDecision } from "./StepTypeDecision";
import { StepPiecesProc } from "./StepPiecesProc";
import { StepDiligences } from "./StepDiligences";

export { NavButtons } from "./NavButtons";

export type StepDefinition = {
  title: string;
  canContinue: (r: ReponsesEligibilite) => boolean;
  Component: React.FC<StepProps>;
};

export const STEPS: StepDefinition[] = [
  {
    title: "Date de la décision",
    canContinue: (r) => calculerPrescription(r.dateDecision).rempli,
    Component: StepDateDecision,
  },
  {
    title: "État de la procédure",
    canContinue: (r) => r.actionContentieuse === ActionContentieuse.Non,
    Component: StepActionContentieuse,
  },
  {
    title: "État de la procédure",
    canContinue: (r) => r.typeDecision !== undefined && r.typeDecision !== TypeDecision.Aucune,
    Component: StepTypeDecision,
  },
  {
    title: "État de la procédure",
    canContinue: (r) => r.piecesProc.length > 0,
    Component: StepPiecesProc,
  },
  {
    title: "Diligences",
    canContinue: (r) => r.preuvesDiligences !== undefined,
    Component: StepDiligences,
  },
];
