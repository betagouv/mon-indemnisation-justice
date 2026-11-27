import { type EtablissementBancaire } from "@/apps/requerant/models/ReferenceBancaire.ts";
import { ServiceIdentifier } from "inversify";

export interface EtablissementBancaireManagerInterface {
  lister(): Promise<EtablissementBancaire[]>;
}

export namespace EtablissementBancaireManagerInterface {
  export const $: ServiceIdentifier<EtablissementBancaireManagerInterface> =
    Symbol("EtablissementBancaireManagerInterface");
}

export class APIEtablissementBancaireManager implements EtablissementBancaireManagerInterface {
  async lister(): Promise<EtablissementBancaire[]> {
    // Route 'api_requerant_etablissements_bancaires_lister'
    const reponse = await fetch("/api/requerant/etablissements-bancaires");

    if (!reponse.ok) {
      throw new Error(
        `Impossible de charger les établissements bancaires (${reponse.status})`,
      );
    }

    return (await reponse.json()) as EtablissementBancaire[];
  }
}
