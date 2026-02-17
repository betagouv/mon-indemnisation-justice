import { Commune } from "@/apps/requerant/models";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";

export interface AdresseManagerInterface {
  listerCommunes(codePostal: string): Promise<Commune[]>;
}

export namespace AdresseManagerInterface {
  export const $: ServiceIdentifier<AdresseManagerInterface> = Symbol(
    "AdresseManagerInterface",
  );
}

export class APIAdresseManager implements AdresseManagerInterface {
  async listerCommunes(codePostal: string): Promise<Commune[]> {
    const response = await fetch(`/api/requerant/communes/${codePostal}`);

    const data = await response.json();

    return plainToInstance(
      Commune,
      data as { id: number; code: string; nom: string }[],
    );
  }
}
