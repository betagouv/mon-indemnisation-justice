import { Agent } from "@common/models";

export interface AgentFIP6Contexte {
  agent: Agent;
  incarnePar?: string;
  urlDeconnexion: string;
}
