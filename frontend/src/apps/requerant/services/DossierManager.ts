import { Dossier } from "@/apps/requerant/models";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { differentiel } from "@/common/services";
import {
  instanceToInstance,
  instanceToPlain,
  plainToClassFromExist,
  plainToInstance,
} from "class-transformer";
import { ServiceIdentifier } from "inversify";

// Source - https://stackoverflow.com/a/61132308
// Posted by Terry, modified by community. See post 'Timeline' for change history
// Retrieved 2026-03-09, License - CC BY-SA 4.0
export type DeepPartial<T> = T extends object
  ? {
      [P in keyof T]?: DeepPartial<T[P]>;
    }
  : T;

export interface DossierManagerInterface {
  aDossier(reference: string): Promise<boolean>;
  getDossier(reference: string): Promise<Dossier | undefined>;

  modifier(reference: string, modifications: DeepPartial<Dossier>): void;

  enregistrer(reference: string): Promise<Dossier>;

  soumettre(reference: string): Promise<void>;

  mesDemandes(): Promise<DossierApercu[]>;
}

export namespace DossierManagerInterface {
  export const $: ServiceIdentifier<DossierManagerInterface> = Symbol(
    "DossierManagerInterface",
  );
}

type EtatSauvegardeDossier = {
  // Le dossier tel qu'il est en temps réel
  original: Dossier;
  modifie: Dossier;
};

/**
 * Synchronise les dossiers du requérant avec l'API
 */
export class ApiDossierManager implements DossierManagerInterface {
  protected dossiers: Map<string, EtatSauvegardeDossier>;
  constructor() {}

  protected async chargerDossiers(rafraichir: boolean = false): Promise<void> {
    if (!this.dossiers || rafraichir) {
      const reponse = await fetch("/api/requerant/mes-demandes", {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
      });

      const data = await reponse.json();
      const dossiers = plainToInstance(Dossier, data as any[]);

      this.dossiers = new Map(
        dossiers.map((dossier) => [
          dossier.reference,
          {
            original: dossier,
            // On clone l'objet source pour ne pas également modifier le dossier original
            modifie: instanceToInstance(dossier),
          },
        ]),
      );
    }
  }

  async aDossier(reference: string): Promise<boolean> {
    await this.chargerDossiers();

    return this.dossiers.has(reference);
  }

  async getDossier(reference: string): Promise<Dossier | undefined> {
    await this.chargerDossiers();

    return this.dossiers.get(reference)?.modifie;
  }

  modifier(reference: string, modifications: DeepPartial<Dossier>): void {
    if (!this.dossiers.has(reference)) {
      throw new Error(`Aucun dossier de référence ${reference}`);
    }

    let { original, modifie } = this.dossiers.get(
      reference,
    ) as EtatSauvegardeDossier;

    this.dossiers.set(reference, {
      original,
      modifie: plainToClassFromExist(modifie, modifications),
    });
  }

  async enregistrer(reference: string): Promise<Dossier> {
    await this.chargerDossiers();

    let { original, modifie } = this.dossiers.get(
      reference,
    ) as EtatSauvegardeDossier;

    const modificationsEnAttente = differentiel(
      instanceToPlain(original),
      instanceToPlain(modifie),
    );

    // On ne déclenche l'enregistrement que si des modifications sont présentes
    if (modificationsEnAttente) {
      const reponse = await fetch(
        // Vers la route `api_requerant_dossier_bris_porte_amender`
        `/api/requerant/dossier/bris-de-porte/${reference}/amender`,
        {
          method: "PATCH",
          headers: {
            Accept: "application/json",
          },
          body: JSON.stringify(modificationsEnAttente),
        },
      );
      const data = await reponse.json();
      const dossier = plainToInstance(Dossier, data);

      this.dossiers.set(reference, {
        original: dossier,
        modifie: instanceToInstance(dossier),
      });
    }

    return this.dossiers.get(reference)?.modifie as Dossier;
  }

  async mesDemandes(): Promise<DossierApercu[]> {
    const reponse = await fetch("/api/requerant/mes-demandes");

    const data = await reponse.json();

    return plainToInstance(DossierApercu, data as any[]);
  }

  async soumettre(reference: string): Promise<void> {
    const reponse = await fetch(
      `/api/requerant/dossier/bris-de-porte/${reference}/publier`,
      {
        method: "POST",
        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!reponse.ok) {
      const data = await reponse.json();
      console.error(data.erreurs);
    } else {
      await this.chargerDossiers(true);
    }
  }
}
