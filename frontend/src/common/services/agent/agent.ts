import { Agent } from "@/common/models";
import { ServiceIdentifier } from "inversify";
import { plainToInstance } from "class-transformer";

export interface AgentManagerInterface {
  moi(): Promise<Agent>;

  agentsActifs(): Promise<Agent[]>;
  agentsInactifs(): Promise<Agent[]>;
}

export namespace AgentManagerInterface {
  export const $: ServiceIdentifier<AgentManagerInterface> = Symbol(
    "AgentManagerInterface",
  );
}

export class APIAgentManager implements AgentManagerInterface {
  async moi(): Promise<Agent> {
    return plainToInstance(
      Agent,
      await (await fetch("/api/agent/fip6/moi")).json(),
    );
  }

  async agentsActifs(): Promise<Agent[]> {
    const reponse = await fetch("/api/agent/fip6/agents/actifs");

    return plainToInstance(Agent, (await reponse.json()) as any[]);
  }

  agentsInactifs(): Promise<Agent[]> {
    return Promise.resolve([]);
  }
}
