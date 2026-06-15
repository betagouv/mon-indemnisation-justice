import { queryClient } from "@/apps/agent/fip6/query.ts";
import { Agent, BaseDossier, Document, DocumentType, DossierDetail } from "@/common/models";
import { RoleAgent } from "@/common/models/Agent.ts";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";

export type ListeDossier =
  | "a-categoriser"
  | "a-attribuer"
  | "a-instruire"
  | "en-instruction"
  | "rejet-a-signer"
  | "proposition-a-signer"
  | "a-verifier"
  | "arrete-a-signer"
  | "a-transmettre"
  | "en-attente-indemnisation";

export type CompteurDossiers = Record<ListeDossier, number>;

export interface DossierManagerInterface {
  compteursDossiers(agent: Agent): Promise<CompteurDossiers>;

  consulter(id: number): Promise<DossierDetail>;

  ajouterPieceJointe(
    dossier: BaseDossier,
    type: DocumentType,
    fichier: File,
  ): Promise<Document>;
}

export namespace DossierManagerInterface {
  export const $: ServiceIdentifier<DossierManagerInterface> = Symbol(
    "DossierManagerInterface",
  );
}

export class APIDossierManager implements DossierManagerInterface {
  compteursDossiers(agent: Agent): Promise<CompteurDossiers> {
    return queryClient.fetchQuery({
      queryKey: ["DossierManagerInterface", "compteursDossiers"],
      queryFn: async (): Promise<CompteurDossiers> => {
        // Si l'agent n'a pas le rôle DOSSIER, évitons un appel inutile à l'API
        if (!agent.aRole(RoleAgent.DOSSIER)) {
          return {} as CompteurDossiers;
        }
        const reponse = await fetch("/api/agent/fip6/decompter-dossiers");
        if (!reponse.ok) {
          throw new Error(
            `Failed to fetch compteurs dossiers: ${reponse.status}`,
          );
        }
        return (await reponse.json()) as CompteurDossiers;
      },
      staleTime: 5 * 60 * 1000, // 5 minutes
    });
  }

  async consulter(id: number): Promise<DossierDetail> {
    const reponse = await fetch(`/api/agent/fip6/dossier/${id}`);

    if (!reponse.ok) {
      throw new Error(`Failed to fetch dossier: ${reponse.status}`);
    }

    const data = await reponse.json();

    return plainToInstance(DossierDetail, data);
  }

  async ajouterPieceJointe(
    dossier: BaseDossier,
    type: DocumentType,
    fichier: File,
  ): Promise<Document> {
    const payload = new FormData();
    payload.append("pieceJointe", fichier);

    const response = await fetch(
      `/api/agent/fip6/dossier/${dossier.id}/ajouter-piece-jointe/${type.type}`,
      {
        method: "POST",
        body: payload,
      },
    );

    const data = await response.json();

    if (response.ok) {
      return plainToInstance(Document, data);
    }

    throw new Error(
      data?.erreur ??
        "Une erreur est survenue lors de l'envoi de la pièce jointe",
    );
  }
}
