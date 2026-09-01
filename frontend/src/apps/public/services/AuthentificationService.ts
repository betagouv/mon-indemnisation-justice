import { InscriptionAvocat, InscriptionUsager } from "@/apps/public/components/authentification/authentification.schemas";
import { TypePersonne } from "@/apps/public/components/authentification/etatAuthentification";
import { obtenirJetonCsrf } from "@/apps/public/services/reactArguments";
import { ServiceIdentifier } from "inversify";

export const CSRF_INTENTION_INSCRIPTION = "inscription-dysfonctionnement";

export type ErreursChamps = Record<string, string>;

export class ErreurInscription extends Error {
  constructor(public readonly erreursChamps: ErreursChamps) {
    super("Erreur lors de l'inscription");
  }
}

// Reporte les erreurs de validation du backend sur les champs correspondants du formulaire tanstack, pour que
// chaque `FormInput` affiche son propre message. `formulaire` est typé `any` car cette fonction est partagée entre
// FormulaireInscriptionUsager et FormulaireInscriptionAvocat, qui utilisent chacun leur propre instance de
// formulaire (schémas Zod différents) — même choix que ChampsBaseInscription pour la même raison.
// `renommer` permet de faire correspondre un nom de champ backend à un nom de champ frontend différent (ex :
// le backend valide `barreauId`, mais le champ du formulaire avocat s'appelle `barreau`).
export function appliquerErreursChamps(
  formulaire: any,
  erreursChamps: ErreursChamps,
  renommer: Record<string, string> = {},
): void {
  Object.entries(erreursChamps).forEach(([champBackend, message]) => {
    const champ = renommer[champBackend] ?? champBackend;
    formulaire.setFieldMeta(champ, (meta: any) => ({
      ...meta,
      isTouched: true,
      errorMap: { ...meta.errorMap, onSubmit: [{ message }] },
    }));
  });
}

export interface AuthentificationServiceInterface {
  inscrireUsager(donnees: InscriptionUsager, typePersonne: TypePersonne): Promise<void>;

  inscrireAvocat(donnees: InscriptionAvocat): Promise<void>;
}

export namespace AuthentificationServiceInterface {
  export const $: ServiceIdentifier<AuthentificationServiceInterface> = Symbol(
    "AuthentificationServiceInterface",
  );
}

export class ApiAuthentificationService implements AuthentificationServiceInterface {
  async inscrireUsager(donnees: InscriptionUsager, typePersonne: TypePersonne): Promise<void> {
    // `confirmation` est envoyé tel quel : le backend le revalide indépendamment (Assert\EqualTo côté
    // InscriptionUsagerInput), en défense en profondeur par rapport à la validation Zod du formulaire.
    return this.poster(
      "/api/public/dysfonctionnement/inscription",
      { ...donnees, estPersonneMorale: typePersonne === TypePersonne.Morale },
      obtenirJetonCsrf(CSRF_INTENTION_INSCRIPTION),
    );
  }

  async inscrireAvocat(donnees: InscriptionAvocat): Promise<void> {
    const { barreau, ...reste } = donnees;
    return this.poster(
      "/api/public/dysfonctionnement/inscription-avocat",
      { ...reste, barreauId: barreau.id },
      obtenirJetonCsrf(CSRF_INTENTION_INSCRIPTION),
    );
  }

  private async poster<T extends object>(url: string, donnees: T, jetonCsrf: string): Promise<void> {
    const reponse = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-Csrf-Token": jetonCsrf },
      body: JSON.stringify(donnees),
    });

    if (!reponse.ok) {
      let erreursChamps: ErreursChamps = {};
      try {
        const corps = await reponse.json();
        if (corps && typeof corps.erreurs === "object" && !Array.isArray(corps.erreurs)) {
          erreursChamps = corps.erreurs;
        }
      } catch {
        // pas de corps JSON exploitable
      }
      throw new ErreurInscription(erreursChamps);
    }
  }
}
