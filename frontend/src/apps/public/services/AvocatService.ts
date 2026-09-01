import { AvocatTrouve } from "@/apps/public/components/authentification/authentification.schemas";
import { ServiceIdentifier } from "inversify";

export interface AvocatServiceInterface {
  rechercherAvocats(recherche: string): Promise<AvocatTrouve[]>;
}

export namespace AvocatServiceInterface {
  export const $: ServiceIdentifier<AvocatServiceInterface> = Symbol("AvocatServiceInterface");
}

export class ApiAvocatService implements AvocatServiceInterface {
  async rechercherAvocats(recherche: string): Promise<AvocatTrouve[]> {
    const reponse = await fetch(`/api/public/dysfonctionnement/avocats?${new URLSearchParams({ r: recherche })}`);
    const donnees: { resultats: AvocatTrouve[] } = await reponse.json();

    return donnees.resultats;
  }
}
