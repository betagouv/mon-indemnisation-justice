import { Dossier } from "@/apps/requerant/models";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import {
  instanceToPlain,
  plainToClassFromExist,
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

/**
 * Gère une collection de tests statiques et non sauvegardées, utile uniquement
 * pour les tests.
 */
export class InMemoryDossierManager implements DossierManagerInterface {
  protected dossiers: Map<string, Dossier>;
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
        dossiers.map((dossier) => [dossier.reference, dossier]),
      );
    }
  }

  async aDossier(reference: string): Promise<boolean> {
    await this.chargerDossiers();

    return this.dossiers.has(reference);
  }

  async getDossier(reference: string): Promise<Dossier | undefined> {
    await this.chargerDossiers();

    return this.dossiers.get(reference);
  }

  modifier(reference: string, modifications: Partial<Dossier>): void {
    if (!this.dossiers.has(reference)) {
      throw new Error(`Aucun dossier de référence ${reference}`);
    }

    this.dossiers.set(
      reference,
      plainToClassFromExist(
        this.dossiers.get(reference) as Dossier,
        modifications,
      ),
    );
  }

  async enregistrer(
    reference: string,
    modifications: Partial<Dossier>,
  ): Promise<Dossier> {
    const reponse = await fetch(
      `/api/requerant/brouillon/bris-de-porte/${reference}/amender`,
      {
        method: "PATCH",
        headers: {
          Accept: "application/json",
        },
        body: JSON.stringify(instanceToPlain(modifications)),
      },
    );

    const data = await reponse.json();

    this.dossiers.set(reference, plainToInstance(Dossier, data));

    return this.dossiers.get(reference) as Dossier;
  }

  async mesDemandes(): Promise<DossierApercu[]> {
    const reponse = await fetch("/api/requerant/mes-demandes");

    const data = await reponse.json();

    return plainToInstance(DossierApercu, data as any[]);
  }
}
