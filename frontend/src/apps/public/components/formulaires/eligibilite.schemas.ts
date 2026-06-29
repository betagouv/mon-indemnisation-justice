

import { z } from "zod";
import { ActionContentieuse, PieceProcedure, TypeDecision } from "../types";

const valeursTypeDecision = [
  TypeDecision.JugementPremiereInstance,
  TypeDecision.ArretCourAppel,
  TypeDecision.ArretCourCassation,
] as const;

const valeursPieceProcedure = [
  PieceProcedure.ActeIntroductif,
  PieceProcedure.Ecritures,
  PieceProcedure.Convocations,
  PieceProcedure.Echanges,
  PieceProcedure.DocumentsProcedure,
] as const;

export const SchemaEtapeDateDecision = z.object({
  dateDecision: z
    .string({ error: "Veuillez indiquer la date de la décision" })
    .min(1, { error: "Veuillez indiquer la date de la décision" }),
});

export const SchemaEtapeActionContentieuse = z
  .object({
    actionContentieuse: z.enum([ActionContentieuse.Non, ActionContentieuse.Oui] as const).optional(),
  })
  .superRefine((donnees, contexte) => {
    if (!donnees.actionContentieuse) {
      contexte.addIssue({
        code: "custom",
        path: ["actionContentieuse"],
        message: "Veuillez répondre à cette question",
      });
    }
  });

export const SchemaEtapeTypeDecision = z.object({
  typeDecision: z
    .array(z.enum(valeursTypeDecision))
    .min(1, { error: "Veuillez sélectionner au moins une décision" }),
});

export const SchemaEtapePiecesProc = z.object({
  piecesProc: z
    .array(z.enum(valeursPieceProcedure))
    .min(1, { error: "Veuillez sélectionner au moins une pièce de procédure" }),
});

export const SchemaEtapeDiligences = z
  .object({
    preuvesDiligences: z.boolean().optional(),
  })
  .superRefine((donnees, contexte) => {
    if (donnees.preuvesDiligences === undefined) {
      contexte.addIssue({
        code: "custom",
        path: ["preuvesDiligences"],
        message: "Veuillez répondre à cette question",
      });
    }
  });
