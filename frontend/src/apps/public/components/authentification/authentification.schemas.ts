import { z } from "zod";

export type Barreau = { id: string; nom: string };

export type AvocatTrouve = {
  numeroCnbf: string;
  nom: string;
  prenom: string;
  cabinet: string | null;
  civilite: "M" | "MME" | null;
  telephone: string | null;
  email: string | null;
  barreau: Barreau;
};

const champsBaseInscription = {
  civilite: z.enum(["M", "MME"] as const, { error: "La civilité est requise" }),
  prenom: z.string().trim().min(1, { error: "Le prénom est requis" }),
  nom: z.string().trim().min(1, { error: "Le nom est requis" }),
  nomNaissance: z.string().optional(),
  courriel: z.email({ error: "L'adresse email n'est pas valide" }),
  telephone: z.string().trim().min(1, { error: "Le numéro de téléphone est requis" }),
  motDePasse: z
    .string()
    .min(8, { error: "Le mot de passe doit contenir au moins 8 caractères" })
    .regex(/\d/, { error: "Le mot de passe doit contenir au moins 1 chiffre" }),
  confirmation: z.string(),
  cguOk: z.literal(true, { error: "Vous devez accepter les conditions générales d'utilisation" }),
};

function verifierConfirmation(donnees: { motDePasse: string; confirmation: string }, ctx: z.RefinementCtx) {
  if (donnees.motDePasse !== donnees.confirmation) {
    ctx.addIssue({
      code: "custom",
      path: ["confirmation"],
      message: "Les deux mots de passe doivent être identiques",
    });
  }
}

export const SchemaInscriptionUsager = z.object(champsBaseInscription).superRefine(verifierConfirmation);
export type InscriptionUsager = z.infer<typeof SchemaInscriptionUsager>;

export const SchemaInscriptionAvocat = z
  .object({
    ...champsBaseInscription,
    barreau: z.custom<Barreau>((valeur) => !!valeur && typeof valeur === "object" && "id" in valeur, {
      error: "Veuillez sélectionner votre barreau d'appartenance",
    }),
    numeroCnbf: z.string().regex(/^\d{6}$/, { error: "Le numéro CNBF doit contenir 6 chiffres" }),
  })
  .superRefine(verifierConfirmation);
export type InscriptionAvocat = z.infer<typeof SchemaInscriptionAvocat>;
