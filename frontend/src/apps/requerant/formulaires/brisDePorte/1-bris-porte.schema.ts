import { Civilite, TypesPersonneMorale } from "@/apps/requerant/models";
import { RapportAuLogements } from "@/apps/requerant/models/RapportAuLogement";
import { z } from "zod";

export type TypeEtatCivil = {
  requerant: {
    estPersonneMorale?: boolean;
    raisonSociale: string;
    siren: string;
    adresse?: {
      ligne1: string;
      ligne2: string;
      commune?: {
        nom: string;
        codePostal: string;
      };
    };
    estRepresentantLegal: boolean;
    civiliteRepresentantLegal?: Civilite;
    nomRepresentantLegal?: boolean;
    nomNaissanceRepresentantLegal;
    prenomRepresentantLegal;
    courrielRepresentantLegal;
    telephoneRepresentantLegal;
  };
};

export const SchemaValidationBrisPorte = z
  .object({
    estPersonneMorale: z.boolean(),
    personneMorale: z
      .object({
        typePersonneMorale: z.enum(TypesPersonneMorale, {
          error: "Veuillez choisir un type de personne morale valide",
        }),
      })
      .optional(),
    rapportAuLogement: z.enum(RapportAuLogements),
    descriptionRapportAuLogement: z.string().optional(),
    dateOperation: z
      .date()
      .max(new Date(+new Date().setHours(23, 59, 59, 9999)), {
        error: "L'opération ne peut avoir lieu dans le futur",
      }),
    description: z.string().optional(),
    adresse: z.object({
      ligne1: z
        .string()
        .trim()
        .min(1, { error: "L'adresse du logement est requise" }),
      ligne2: z.string().optional(),
      codePostal: z
        .string()
        .regex(/\d{5}/, { error: "Le code postal doit réunir 5 chiffres" }),
      commune: z.string().trim().min(1, { error: "La ville est requise" }),
    }),
    estPorteBlindee: z.boolean(),
  })
  .superRefine((donnees, contexte) => {
    if (
      donnees.estPersonneMorale &&
      !donnees.personneMorale?.typePersonneMorale
    ) {
      contexte.addIssue({
        code: "custom",
        path: ["personneMorale", "typePersonneMorale"],
        message:
          "Veuillez nous indiquer le type de personne morale que vous représentez",
      });
    }

    if (donnees.rapportAuLogement && !donnees.descriptionRapportAuLogement) {
      contexte.addIssue({
        code: "custom",
        path: ["descriptionRapportAuLogement"],
        message: "Veuillez préciser votre rapport au logement",
      });
    }
  });
