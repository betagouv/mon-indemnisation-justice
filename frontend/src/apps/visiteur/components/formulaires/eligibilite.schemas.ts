import { z } from "zod";
import { ActionContentieuse, PieceProcedure, PreuvesDiligences, TypeDecision } from "../types";
import { calculerPrescription } from "@/apps/visiteur/services/prescription";

const valeursTypeDecision = [
  TypeDecision.JugementPremiereInstance,
  TypeDecision.ArretCourAppel,
  TypeDecision.ArretCourCassation,
  TypeDecision.Aucune,
] as const;

const valeursPieceProcedure = [
  PieceProcedure.Assignation,
  PieceProcedure.DecisionsJuge,
  PieceProcedure.Calendrier,
  PieceProcedure.Ecritures,
  PieceProcedure.Convocations,
  PieceProcedure.Renvoi,
  PieceProcedure.Echanges,
  PieceProcedure.Appel,
] as const;

export const SchemaEtapeDateDecision = z
  .object({
    dateDecision: z
      .string({ error: "Veuillez indiquer la date de la décision" })
      .min(1, { error: "Veuillez indiquer la date de la décision" }),
  })
  .superRefine((donnees, contexte) => {
    if (!donnees.dateDecision) return;
    const prescription = calculerPrescription(new Date(donnees.dateDecision));
    if (!prescription.rempli) {
      contexte.addIssue({
        code: "custom",
        path: ["dateDecision"],
        message: prescription.expiration
          ? `Votre demande est prescrite depuis le ${prescription.expiration.toLocaleDateString("fr-FR")}.`
          : "Date invalide. Veuillez vérifier la date saisie.",
      });
    }
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
      return;
    }
    if (donnees.actionContentieuse === ActionContentieuse.Oui) {
      contexte.addIssue({
        code: "custom",
        path: ["actionContentieuse"],
        message:
          "Une procédure contentieuse en cours devant l'AJE rend la démarche précontentieuse irrecevable. Vous pourrez effectuer cette déclaration après la clôture de cette procédure.",
      });
    }
  });

export const SchemaEtapeTypeDecision = z
  .object({
    typeDecision: z.enum(valeursTypeDecision).optional(),
  })
  .superRefine((donnees, contexte) => {
    if (!donnees.typeDecision) {
      contexte.addIssue({
        code: "custom",
        path: ["typeDecision"],
        message: "Veuillez sélectionner le type de décision",
      });
      return;
    }
    if (donnees.typeDecision === TypeDecision.Aucune) {
      contexte.addIssue({
        code: "custom",
        path: ["typeDecision"],
        message:
          "L'absence de décision de justice ne permet pas de qualifier un délai déraisonnable. Une décision rendue dans votre affaire est nécessaire pour engager cette démarche.",
      });
    }
  });

export const SchemaEtapePiecesProc = z.object({
  piecesProc: z
    .array(z.enum(valeursPieceProcedure))
    .min(1, { error: "Veuillez sélectionner au moins une pièce de procédure" }),
});

export const SchemaEtapeDiligences = z
  .object({
    preuvesDiligences: z.enum([PreuvesDiligences.Oui, PreuvesDiligences.Non] as const).optional(),
  })
  .superRefine((donnees, contexte) => {
    if (!donnees.preuvesDiligences) {
      contexte.addIssue({
        code: "custom",
        path: ["preuvesDiligences"],
        message: "Veuillez répondre à cette question",
      });
    }
  });
