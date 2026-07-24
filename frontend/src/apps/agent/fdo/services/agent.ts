import { queryClient } from "@fdo/query";
import { AgentFDOContexte } from "@fdo/routeur/contexte.ts";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";
import { AgentFDO } from "@fdo/modeles/AgentFDO.ts";

const referenceService = "AgentManagerInterface@FDO";
export interface AgentManagerInterface {
  moi(): Promise<AgentFDOContexte>;
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
}
