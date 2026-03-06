import { Dossier } from "@/apps/requerant/models";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { differentiel, fusion } from "@/common/services";
import {
  instanceToInstance,
  instanceToPlain,
  plainToInstance,
} from "class-transformer";
import { ServiceIdentifier } from "inversify";

export interface DossierManagerInterface {
  aDossier(reference: string): Promise<boolean>;
  getDossier(reference: string): Promise<Dossier | undefined>;

  modifier(reference: string, modifications: Partial<Dossier>): void;

  enregistrer(
    reference: string,
    modifications: Partial<Dossier>,
  ): Promise<Dossier>;

  mesDemandes(): Promise<DossierApercu[]>;
}

export namespace DossierManagerInterface {
  export const $: ServiceIdentifier<DossierManagerInterface> = Symbol(
    "DossierManagerInterface",
  );
}

type EtatSauvegardeDossier = {
  original: Dossier;
  modifie: Dossier;
};

/**
 * Synchronise les dossiers du requérant avec l'API
 */
export class ApiDossierManager implements DossierManagerInterface {
  protected dossiers: Map<string, EtatSauvegardeDossier>;
  constructor() {}

  protected async chargerDossiers(): Promise<void> {
    if (!this.dossiers) {
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

  modifier(reference: string, modifications: Partial<Dossier>): void {
    if (!this.dossiers.has(reference)) {
      throw new Error(`Aucun dossier de référence ${reference}`);
    }

    const { original, ...reste } = this.dossiers.get(
      reference,
    ) as EtatSauvegardeDossier;

    this.dossiers.set(reference, {
      original,
      modifie: plainToInstance(
        Dossier,
        fusion(instanceToPlain(original), modifications),
      ),
    });
  }

  async enregistrer(
    reference: string,
    modifications: Partial<Dossier>,
  ): Promise<Dossier> {
    const { original, modifie } = this.dossiers.get(
      reference,
    ) as EtatSauvegardeDossier;

    // On calcule l'écart entre la version originale et la version modifiée
    const modificationsEnAttente = differentiel(
      instanceToPlain(original),
      instanceToPlain(modifie),
    );

    // On ne déclenche l'enregistrement que si des modifications sont présentes
    if (modificationsEnAttente) {
      const reponse = await fetch(
        `/api/requerant/brouillon/bris-de-porte/${reference}/amender`,
        {
          method: "PATCH",
          headers: {
            Accept: "application/json",
          },
          body: JSON.stringify(modificationsEnAttente),
        },
      );
      const data = await reponse.json();

      this.dossiers.set(reference, {
        original: plainToInstance(Dossier, data),
        modifie: plainToInstance(Dossier, data),
      });
    }

    return this.dossiers.get(reference)?.original as Dossier;
  }

  async mesDemandes(): Promise<DossierApercu[]> {
    const reponse = await fetch("/api/requerant/mes-demandes");

    const data = await reponse.json();

    return plainToInstance(DossierApercu, data as any[]);
  }
}
