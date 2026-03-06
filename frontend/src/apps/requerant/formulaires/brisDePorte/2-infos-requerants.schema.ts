import { Civilites, Commune, Dossier, Pays, PersonneMorale, PersonnePhysique } from "@/apps/requerant/models";
import { z } from "zod";

// Schema for a "personne morale" (company)
const SchemaPersonneMorale = z.object({
  raisonSociale: z
    .string({ error: "La raison sociale de l'entreprise est requise" })
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
    ligne2: z.string().optional(),
    codePostal: z
      .string()
      .regex(/\d{5}/, { error: "Le code postal doit réunir 5 chiffres" }),
    commune: z
      .string()
      .trim()
      .min(1, { error: "La commune de la société est requise" }),
  }),
  representantLegal: z.object({
    civilite: z.enum(Civilites),
    nom: z.string(),
    nomNaissance: z.string(),
    prenom: z.string(),
    courriel: z.email(),
    telephone: z.string(),
  }),
});

// Schema for a "personne physique" (individual)
const SchemaPersonnePhysique = z
  .object({
    personne: z.object({
      civilite: z.enum(Civilites, { error: "La civilité est requise" }),
      prenom: z.string().trim().min(1, { error: "Les prénoms sont requis" }),
      nom: z
        .string({ error: "Le nom est requis" })
        .trim()
        .min(1, { error: "Le nom est requis" }),
      nomNaissance: z.string({ error: "Le nom de naissance est requis" }),
      courriel: z.email({
        error: "Une adresse courriel valide est requise",
      }),
      telephone: z
        .string({ error: "Le numéro de téléphone est requis" })
        .min(7, { error: "Le numéro de téléphone est requis" }),
    }),
    prenom2: z.string().optional(),
    prenom3: z.string().optional(),
    adresse: z.object({
      ligne1: z
        .string({ error: "L'adresse de résidence est requise" })
        .trim()
        .min(1, { error: "L'adresse de résidence est requise" }),
      ligne2: z.string().optional(),
      codePostal: z
        .string({ error: "Le code postal est requis" })
        .regex(/\d{5}/, {
          error: "Le code postal doit réunir 5 chiffres",
        }),
      commune: z
        .string({ error: "La ville est requise" })
        .trim()
        .min(1, { error: "La ville est requise" }),
    }),
    dateNaissance: z
      .date({ error: "Veuillez saisir une date valide" })
      .max(new Date(+new Date().setHours(23, 59, 59, 9999)), {
        error: "Veuillez saisir une date valide",
      }),
    paysNaissance: z.instanceof(Pays, {
      error: "Le pays de naissance est requis",
    }),
    // Dépend du pays de naissance
    communeNaissance: z.any().optional(),
    villeNaissance: z.string().optional(),
  })
  .refine(
    // Si né en France, la commune de naissance est requise
    (donnees) => {
      console.log("Né en France", [,]);
      return donnees.paysNaissance && donnees.paysNaissance.nom == "France"
        ? donnees.communeNaissance instanceof Commune
        : true;
    },
    {
      message: "La commune de naissance est requise",
      path: ["communeNaissance"],
      when: () => true,
    },
  )
  .refine(
    // Si né hors de France, le nom de la ville de naissance doit être saisie
    (donnees) => {
      return donnees.paysNaissance && donnees.paysNaissance.nom != "France"
        ? !!donnees.villeNaissance
        : true;
    },
    {
      path: ["villeNaissance"],
      message: "La ville de naissance est requise",
      when: () => true,
    },
  );
export const SchemaValidationInfosRequerants = z.discriminatedUnion(
  "estPersonneMorale",
  [
    z.object({
      estPersonneMorale: z.literal(true),
      personneMorale: SchemaPersonneMorale,
      personnePhysique: z.undefined(),
    }),
    z.object({
      estPersonneMorale: z.literal(false),
      personneMorale: z.undefined(),
      personnePhysique: SchemaPersonnePhysique,
    }),
  ],
);

export type TypeInfosRequerants = z.infer<
  typeof SchemaValidationInfosRequerants
>;

export const extraireDonneesInfosRequerant = (
  dossier: Dossier,
): TypeInfosRequerants =>
  dossier.estPersonneMorale
    ? {
        estPersonneMorale: true,
        personneMorale: { ...(dossier.personneMorale as PersonneMorale) },
        personnePhysique: undefined,
      }
    : {
        estPersonneMorale: false,
        personneMorale: undefined,
        personnePhysique: { ...(dossier.personnePhysique as PersonnePhysique) },
      };

export const estDossierOkInfosRequerant = (dossier: Dossier): boolean =>
  SchemaValidationInfosRequerants.safeParse(
    extraireDonneesInfosRequerant(dossier),
  ).success;
