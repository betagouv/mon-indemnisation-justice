import { Administration, Agent } from "@/common/models";
import { RoleAgent } from "@/common/models/Agent";
import { ServiceIdentifier } from "inversify";
import { plainToInstance } from "class-transformer";

export interface AgentManagerInterface {
  moi(): Promise<Agent>;

  editerAgent({
    id,
    prenom,
    nom,
    courriel,
    administration,
    roles,
  }: {
    id?: number;
    prenom: string;
    nom: string;
    courriel: string;
    administration: Administration;
    roles?: RoleAgent[];
  }): Promise<Agent>;
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
  async editerAgent({
    id,
    prenom,
    nom,
    courriel,
    administration,
    roles,
  }: {
    id?: number;
    prenom: string;
    nom: string;
    courriel: string;
    administration: Administration;
    roles: RoleAgent[];
  }): Promise<Agent> {
    const reponse = await fetch(
      id
        ? `/api/agent/fip6/agents/editer/${id}`
        : "/api/agent/fip6/agents/creer",
      {
        method: id ? "POST" : "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          prenom,
          nom,
          courriel,
          administration: administration.type,
          roles: roles.map((r) => r.type),
        }),
      },
    );

    return plainToInstance(Agent, await reponse.json());
  }

  async agentsActifs(): Promise<Agent[]> {
    const reponse = await fetch("/api/agent/fip6/agents/actifs");

    return plainToInstance(Agent, (await reponse.json()) as any[]);
  }

  agentsInactifs(): Promise<Agent[]> {
    return Promise.resolve([]);
  }
}
