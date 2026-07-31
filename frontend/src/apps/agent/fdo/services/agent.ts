import { AgentFDO } from "@fdo/modeles/AgentFDO.ts";
import { EtablissementFDO } from "@fdo/modeles/EtablissementFDO.ts";
import { queryClient } from "@fdo/query";
import { AgentFDOContexte } from "@fdo/routeur/contexte.ts";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";
import { URLSearchParams } from "node:url";

const referenceService = "AgentManagerInterface@FDO";
export interface AgentManagerInterface {
  moi(): Promise<AgentFDOContexte>;

  rechercherEtablissements(recherche: string): Promise<EtablissementFDO[]>;

  attribuerEtablissement({
    estExempt,
    etablissement,
    dateAffectation,
  }: {
    estExempt: boolean;
    etablissement?: EtablissementFDO;
    dateAffectation?: Date;
  }): Promise<void>;
}

export namespace AgentManagerInterface {
  export const $: ServiceIdentifier<AgentManagerInterface> =
    Symbol(referenceService);
}

export class APIAgentManager implements AgentManagerInterface {
  async moi(): Promise<AgentFDOContexte> {
    return queryClient.fetchQuery({
      queryKey: [referenceService, "moi"],
      queryFn: async (): Promise<AgentFDOContexte> => {
        const reponse = await fetch("/api/agent/fdo/moi");

        const data: { agent: any; incarnePar: string; urlDeconnexion: string } =
          await reponse.json();

        return {
          agent: plainToInstance(AgentFDO, data.agent),
          incarnePar: data.incarnePar,
          urlDeconnexion: data.urlDeconnexion,
        };
      },
      staleTime: 5 * 60 * 1000, // 5 minutes
    });
  }

  async rechercherEtablissements(
    recherche: string,
  ): Promise<EtablissementFDO[]> {
    const reponse = await fetch(
      "/api/agent/fdo/etablissements/rechercher?" +
        new URLSearchParams({
          r: recherche,
        }).toString(),
    );

    const data: {
      resultats: { id: string; nom: string; identifiant: string }[];
    } = await reponse.json();

    return plainToInstance(EtablissementFDO, data.resultats);
  }

  async attribuerEtablissement({
    estExempt,
    etablissement = undefined,
    dateAffectation = undefined,
  }: {
    estExempt: boolean;
    etablissement?: EtablissementFDO;
    dateAffectation?: Date;
  }): Promise<void> {
    const reponse = await fetch("/api/agent/fdo/affecter", {
      method: "POST",
      headers: {
        "Content-type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        estExempt: estExempt,
        etablissement: etablissement?.id,
        dateAffectation: dateAffectation,
      }),
    });

    const data = await reponse.json();

    queryClient.setQueryData([referenceService, "moi"], {
      ...(await this.moi()),
      agent: plainToInstance(AgentFDO, data),
    });
  }
}
