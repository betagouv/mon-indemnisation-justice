import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";

export interface AgentFIP6Contexte {
  agent: AgentFIP6;
  incarnePar?: string;
  urlDeconnexion: string;
}
