import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";
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
  moi(): Promise<ContexteUsager> {
    return new Promise((resolve) =>
      resolve({
        usager: {
          id: 1234,
          prenom: "Rick",
          nom: "ERRANT",
          courriel: "rick@courriel.fr",
        },
      }),
    );
  }
}
