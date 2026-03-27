import { Dossier, TypesPersonneMorale } from "@/apps/requerant/models";
import { RapportAuLogements } from "@/apps/requerant/models/RapportAuLogement";
import { z } from "zod";

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
    rapportAuLogement: z.enum(RapportAuLogements, {
      error: "Veuillez indiquer votre rapport au logement",
    }),
    descriptionRapportAuLogement: z.string().optional(),
    dateOperation: z
      .date({ error: "Veuillez indiquer la date du bris de porte" })
      .max(new Date(+new Date().setHours(23, 59, 59, 9999)), {
        error: "L'opération ne peut avoir lieu dans le futur",
      }),
    description: z.string().optional(),
    adresse: z.object({
      ligne1: z
        .string({ error: "L'adresse du logement est requise" })
        .trim()
        .min(1, { error: "L'adresse du logement est requise" }),
      ligne2: z.string().optional(),
      codePostal: z
        .string({ error: "Le code postal est requis" })
        .regex(/\d{5}/, { error: "Le code postal doit réunir 5 chiffres" }),
      commune: z
        .string({ error: "La commune est requise" })
        .trim()
        .min(1, { error: "La ville est requise" }),
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

    if (
      donnees.rapportAuLogement === "AUTRE" &&
      !donnees.descriptionRapportAuLogement
    ) {
      contexte.addIssue({
        code: "custom",
        path: ["descriptionRapportAuLogement"],
        message: "Veuillez préciser votre rapport au logement",
      });
    }
  });

export type TypeBrisDePorte = z.infer<typeof SchemaValidationBrisPorte>;

export const extraireDonneesBrisDeporte = (
  dossier: Dossier,
): TypeBrisDePorte => {
  return {
    estPersonneMorale: dossier.estPersonneMorale,
    personneMorale: dossier.personneMorale?.typePersonneMorale
      ? {
          typePersonneMorale: dossier.personneMorale.typePersonneMorale,
        }
      : undefined,
    rapportAuLogement: dossier.rapportAuLogement,
    descriptionRapportAuLogement: dossier.descriptionRapportAuLogement,
    dateOperation: dossier.dateOperation,
    description: dossier.description,
    adresse: {
      ligne1: dossier.adresse?.ligne1,
      ligne2: dossier.adresse?.ligne2,
      codePostal: dossier.adresse?.codePostal,
      commune: dossier.adresse?.commune,
    },
    estPorteBlindee: dossier.estPorteBlindee,
  };
};

export const estDossierOkBrisDePorte = (dossier: Dossier): boolean =>
  SchemaValidationBrisPorte.safeParse(extraireDonneesBrisDeporte(dossier))
    .success;
