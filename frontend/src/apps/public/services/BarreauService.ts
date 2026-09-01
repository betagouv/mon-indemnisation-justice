import { Barreau } from "@/apps/public/components/authentification/authentification.schemas";
import { queryClient } from "@/apps/public/query";
import { ServiceIdentifier } from "inversify";

export interface BarreauServiceInterface {
  listerBarreaux(): Promise<Barreau[]>;
}

export namespace BarreauServiceInterface {
  export const $: ServiceIdentifier<BarreauServiceInterface> = Symbol("BarreauServiceInterface");
}

export class ApiBarreauService implements BarreauServiceInterface {
  async listerBarreaux(): Promise<Barreau[]> {
    return queryClient.fetchQuery({
      queryKey: ["dysfonctionnement", "barreaux"],
      queryFn: async (): Promise<Barreau[]> => {
        const reponse = await fetch("/api/public/dysfonctionnement/barreaux");

        return await reponse.json();
      },
      staleTime: 60 * 60 * 1000, // 1 heure
    });
  }
}
