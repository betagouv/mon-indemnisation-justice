import { Agent } from "@/common/models";

export interface AgentContext {
  agent: Promise<Agent>;
  incarnePar?: string;
}
