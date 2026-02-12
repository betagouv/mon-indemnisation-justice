import { z } from "zod";
import { Pays, Commune, Civilite, Civilites } from "@/apps/requerant/models";

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

export const SchemaValidationEtatCivil = z.object({
  requerant: z.discriminatedUnion("estPersonneMorale", [
    // Cas de la personne morale
    z.object({
      estPersonneMorale: z.literal(true, {
        error:
          "Veuillez-nous indiquez si vous déposez votre demande au nom du personne morale",
      }),
      raisonSociale: z
        .string()
        .trim()
        .min(1, { error: "La raison sociale de l'entreprise est requise" }),
      // https://annuaire-entreprises.data.gouv.fr/definitions/numero-siren
      siren: z.string().regex(/\d{9}(\d{5})?/, {
        error: "Le numéro de SIREN / SIRET est invalide",
      }),
      adresse: z.object({
        ligne1: z
          .string()
          .trim()
          .min(1, { error: "L'adresse de la société est requise" }),
        ligne2: z.string(),
        codePostal: z
          .string()
          .regex(/\d{5}/, { error: "Le code postal doit réunir 5 chiffres" }),
        commune: z.instanceof(Commune, {
          error: "La commune de la société est requise",
        }),
      }),
      estRepresentantLegal: z.boolean(),
      civiliteRepresentantLegal: z.enum(Civilites),
      nomRepresentantLegal: z.string(),
      nomNaissanceRepresentantLegal: z.string(),
      prenomRepresentantLegal: z.string(),
      courrielRepresentantLegal: z.email(),
      telephoneRepresentantLegal: z.string(),
      civilite: z.any(),
      prenom: z.any(),
      nom: z.any(),
      nomNaissance: z.any(),
      courriel: z.any(),
      telephone: z.any(),
      dateNaissance: z.any(),
      paysNaissance: z.any(),
      communeNaissance: z.any(),
    }),
    // Cas de la personne physique
    z
      .object({
        estPersonneMorale: z.literal(false, {
          error:
            "Veuillez-nous indiquez si vous déposez votre demande au nom du personne morale",
        }),
        raisonSociale: z.any(),
        siren: z.any(),
        estRepresentantLegal: z.any(),
        civiliteRepresentantLegal: z.any(),
        nomRepresentantLegal: z.any(),
        nomNaissanceRepresentantLegal: z.any(),
        prenomRepresentantLegal: z.any(),
        courrielRepresentantLegal: z.any(),
        telephoneRepresentantLegal: z.any(),
        civilite: z.enum(Civilites),
        prenom: z.string().trim().min(1, { error: "Le prénom est requis" }),
        nom: z
          .string({ error: "Le nom est requis" })
          .trim()
          .min(1, { error: "Le nom est requis" }),
        nomNaissance: z.string(),
        courriel: z.email({ error: "Une adresse courriel valide et requise" }),
        telephone: z
          .string({ error: "Le numéro de téléphone est requis" })
          .min(7, { error: "Le numéro de téléphone est requis" }),
        adresse: z.object({
          ligne1: z
            .string()
            .trim()
            .min(1, { error: "L'adresse de la société est requise" }),
          ligne2: z.string(),
          codePostal: z
            .string()
            .regex(/\d{5}/, { error: "Le code postal doit réunir 5 chiffres" }),
          commune: z.instanceof(Commune, {
            error: "La commune de la société est requise",
          }),
        }),
        dateNaissance: z
          .date()
          .max(new Date(+new Date().setHours(23, 59, 59, 9999)), {
            error: "Veuillez saisir une date valide",
          }),
        paysNaissance: z.instanceof(Pays, {
          error: "Le pays de naissance est requis",
        }),
        // Dépend du pays de naissance
        communeNaissance: z.any().optional(),
      })
      // Si né en France, la commune de naissance est requise
      .refine(
        (donnees) =>
          donnees.paysNaissance.nom == "France" &&
          !(donnees.communeNaissance instanceof Commune),
        {
          error: "La commune de naissance est requise",
          path: ["communeNaissance"],
        },
      ),
  ]),
});
