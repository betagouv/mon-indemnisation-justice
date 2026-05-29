import { AgentContext } from "@/apps/agent/_commun/contexts";
import { Administration, Agent, Redacteur } from "@/common/models";
import { RoleAgent } from "@/common/models/Agent";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";

export interface AgentManagerInterface {
  moi(): Promise<AgentContext>;

  redacteurs(): Promise<Redacteur[]>;

  // TODO: changer ça pour pouvoir patcher (utiliser Partial<{}>)
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
  protected _contexteNavigation?: AgentContext;
  async moi(): Promise<AgentContext> {
    if (!this._contexteNavigation) {
      const reponse = await fetch("/api/agent/fip6/moi");

      const data: { agent: any; incarnePar: string } = await reponse.json();

      this._contexteNavigation = {
        agent: plainToInstance(Agent, data.agent),
        incarnePar: data.incarnePar,
      };
    }

    return this._contexteNavigation;
  }

  async redacteurs(): Promise<Redacteur[]> {
    const reponse = await fetch("/api/agent/fip6/agents/redacteurs");

    const data = await reponse.json();

    return data as Redacteur[];
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
