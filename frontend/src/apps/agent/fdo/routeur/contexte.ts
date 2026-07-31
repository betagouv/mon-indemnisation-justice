import { AgentFDO } from "@fdo/modeles/AgentFDO.ts";

export interface AgentFDOContexte {
  agent: AgentFDO;
  incarnePar?: string;
  urlDeconnexion: string;
}
