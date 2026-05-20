import React from "react";
import type { StepProps } from "../types";
import { StepDateDecision } from "./StepDateDecision";
import { StepActionContentieuse } from "./StepActionContentieuse";
import { StepTypeDecision } from "./StepTypeDecision";
import { StepPiecesProc } from "./StepPiecesProc";
import { StepDiligences } from "./StepDiligences";

export { NavButtons } from "./NavButtons";

export type StepDefinition = {
  title: string;
  Component: React.FC<StepProps>;
};

export const STEPS: StepDefinition[] = [
  {
    title: "Date de la décision",
    Component: StepDateDecision,
  },
  {
    title: "État de la procédure",
    Component: StepActionContentieuse,
  },
  {
    title: "État de la procédure",
    Component: StepTypeDecision,
  },
  {
    title: "État de la procédure",
    Component: StepPiecesProc,
  },
  {
    title: "Diligences",
    Component: StepDiligences,
  },
];

export const STEP_COUNT = STEPS.length;
export const TOTAL_STEPS = STEP_COUNT + 1;
