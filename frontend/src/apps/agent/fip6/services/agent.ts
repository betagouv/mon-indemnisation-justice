import { Administration, Agent, Redacteur } from "@/common/models";
import { RoleAgent } from "@/common/models/Agent";
import { queryClient } from "@fdo/query.ts";
import { AgentFIP6 } from "@fip6/modeles/AgentFIP6.ts";
import { AgentFIP6Contexte } from "@fip6/routeur/contexte";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";

const referenceService = "AgentManagerInterface@FIP6";

export type RechercheAgentRequete = {
  page: number;
  taille: number;
  actifs: boolean;
  requete?: string;
  administrations?: Administration[];
};

export type RechercheAgentResultat = {
  resultats: Agent[];
  taille: number;
  total: number;
  page: number;
};

export interface AgentManagerInterface {
  moi(): Promise<AgentFIP6Contexte>;

  redacteurs(): Promise<Redacteur[]>;

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

  rechercher(requete: RechercheAgentRequete): Promise<RechercheAgentResultat>;
}

export namespace AgentManagerInterface {
  export const $: ServiceIdentifier<AgentManagerInterface> =
    Symbol(referenceService);
}

export class APIAgentManager implements AgentManagerInterface {
  async moi(): Promise<AgentFIP6Contexte> {
    return queryClient.fetchQuery({
      queryKey: [referenceService, "moi"],
      queryFn: async (): Promise<AgentFIP6Contexte> => {
        const reponse = await fetch("/api/agent/fip6/moi");

        const data: { agent: any; incarnePar: string; urlDeconnexion: string } =
          await reponse.json();

        return {
          agent: plainToInstance(AgentFIP6, data.agent),
          incarnePar: data.incarnePar,
          urlDeconnexion: data.urlDeconnexion,
        };
      },
      staleTime: 5 * 60 * 1000, // 5 minutes
    });
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

  async rechercher(
    requete: RechercheAgentRequete,
  ): Promise<RechercheAgentResultat> {
    const parametresURL = Object.entries({
      page: requete.page,
      taille: requete.taille,
      actif: requete.actifs,
      ...(requete.administrations
        ? {
            administrations: requete.administrations?.map(
              (administration) => administration.type,
            ),
          }
        : {}),
      ...(requete.requete ? { recherche: requete.requete } : {}),
    })
      .map(([key, value]) => {
        if (Array.isArray(value)) {
          return value.map((v) => `${key}[]=${v}`).join("&");
        }

        return `${key}=${value}`;
      })
      .join("&");

    const reponse = await fetch(
      `/api/agent/fip6/agents/rechercher?${parametresURL}`,
    );

    const data: {
      resultats: any[];
      taille: number;
      total: number;
      page: number;
    } = await reponse.json();

    return {
      ...data,
      resultats: plainToInstance(Agent, data.resultats),
    };
  }
}
