import { Usager } from "@/apps/requerant/models";
import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";

export interface UsagerManagerInterface {
  moi(): Promise<ContexteUsager>;
}

export namespace UsagerManagerInterface {
  export const $: ServiceIdentifier<UsagerManagerInterface> = Symbol(
    "RequerantManagerInterface",
  );
}

export class APIUsagerManager implements UsagerManagerInterface {
  async moi(): Promise<ContexteUsager> {
    const reponse = await fetch("/api/requerant/moi");

    const data = await reponse.json();

    console.dir({
      incarnePar: data,
      usager: plainToInstance(Usager, data.usager),
    });

    return {
      incarnePar: data,
      usager: plainToInstance(Usager, data.usager),
    };
  }
}
