import { Dossier } from "@/apps/requerant/models";
import { ServiceIdentifier } from "inversify";
import { plainToClassFromExist, plainToInstance } from "class-transformer";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { dateIlYaNJours } from "@/common/services/date.ts";

export interface DossierManagerInterface {
  aDossier(reference: string): Promise<boolean>;
  getDossier(reference: string): Promise<Dossier | undefined>;
  modifierDossier(
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
  constructor() {
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

  modifierDossier(
    reference: string,
    modifications: Partial<Dossier>,
  ): Promise<Dossier> {
    if (!this.dossiers.has(reference)) {
      return Promise.reject(`Aucun dossier de référence ${reference}`);
    }

    console.dir(modifications);

    this.dossiers.set(
      reference,
      plainToClassFromExist(
        this.dossiers.get(reference) as Dossier,
        modifications,
      ),
    );

    console.log(this.dossiers.get(reference));

    return Promise.resolve(this.dossiers.get(reference) as Dossier);
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
