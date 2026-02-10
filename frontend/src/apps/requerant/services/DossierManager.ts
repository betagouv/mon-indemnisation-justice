import { Dossier } from "@/apps/requerant/models";
import { ServiceIdentifier } from "inversify";
import { plainToInstance } from "class-transformer";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { EtatDossierTypeCodes } from "@/apps/requerant/models/EtatDossier.ts";
import { dateIlYaNJours } from "@/common/services/date.ts";

export interface DossierManagerInterface {
  aDossier(reference: string): Promise<boolean>;
  getDossier(reference: string): Promise<Dossier | undefined>;
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
  constructor() {
    console.log(dateIlYaNJours(3));

    this.dossiers = new Map(
      [
        plainToInstance(Dossier, {
          reference: self.crypto.randomUUID(),
          etatActuel: {
            date: dateIlYaNJours(3),
            etat: "A_COMPLETER",
            requerant: { id: 123, nom: "Jean MICHON" },
          },
        }),
      ].map((d) => [d.reference, d]),
    );
  }

  aDossier(reference: string): Promise<boolean> {
    return Promise.resolve(this.dossiers.has(reference));
  }

  getDossier(reference: string): Promise<Dossier | undefined> {
    return Promise.resolve(this.dossiers.get(reference));
  }

  mesDemandes(): Promise<DossierApercu[]> {
    return Promise.resolve(
      this.dossiers
        .values()
        .map((d) => {
          const apercu = new DossierApercu();

          apercu.etatActuel = d.etatActuel;
          apercu.reference = d.reference;

          return apercu;
        })
        .toArray(),
    );
  }
}
